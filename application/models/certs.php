<?php
/**
 * Certificates
 * 
 * @package Model
 */
class certs extends mongoBase {
    
    const HASH      = 'md5';
    const PREFIX    = 'cert_';
    const KEY_DB    = 'mongo:db';
    
    private $grid;
    private $redis;
    
    /**
     * Creates a new instance.
     * 
     * @param resource $connection A MongoDB connection.
     */
    public function __construct($connection, $redis) {
        $this->grid = $connection->{Config::get(self::KEY_DB)}->getGridFS();
        $this->redis = $redis;
    }
    
    /**
     * Preliminary checks for adding a certificate.
     * 
     * @param string $cert The public certificate to check.
     * 
     * @return mixed True on success, or an error string.
     */
    public function preAdd($cert) {
        $certificate = $this->grid->findOne(array('key' => $this->getKey($cert)));
        
        if (!empty($certificate)) return 'Duplicate certificate';
        return true;
    }
    
    /**
     * Add a new certificate.
     * 
     * @param string $cert The public certificate to add.
     */
    public function add($cert) {
        $this->redis->incr('cert_serial');
        
        $this->grid->storeBytes(Session::getVar('_id') . ':' . trim($cert),
            array('key' => $this->getKey($cert)));
        return true;
    }
    
    /**
     * Create a new public certificate.
     * 
     * @param string $csr The CSR to create a public key off of.
     * 
     * @return string The new public key.
     */
    public function create($csr) {
        $cert = openssl_csr_sign($csr, Config::get('ssl:certificate'), 
            Config::get('ssl:key'), 365, Config::get('sslConf'), $this->getSerial());
        openssl_x509_export($cert, $output);
        
        return $output;
    }
    
    /**
     * Get a certificate.
     * 
     * @param string $certKey The certificate key to search by.
     * @param bool $cut True to cut off the user id.
     * 
     * @return string The certificate.
     */
    public function get($certKey, $cut = true) {
        $cert = $this->grid->findOne(array('key' => $certKey));
        if ($cert == null) return null;
        $cert = $cert->getBytes();
        return ($cut ? substr($cert, strpos($cert, ':') + 1) : $cert);
    }
    
    /**
     * Remove a certificate.
     * 
     * @param string $certKey The certificate key to search by.
     */
    public function removeCert($certKey) {
        $this->grid->remove(array('key' => $certKey));
    }
    
    /**
     * Check if the current user's certificate exists on disk.
     * 
     * @param string $cert The user's certificate.
     * 
     * @return string The user id that corresponds to this certificate.
     */
    protected function check($cert) {
        $cert = $this->grid->findOne(array('key' => $this->getKey($cert)));
        if ($cert == null) return null;
        $info = $cert->getBytes();

        return substr($info, 0, strpos($info, ':'));
    }
    
    /**
     * Generate a key for a certificate.
     * 
     * @param string $cert A public key.
     * 
     * @return string The certificate key.
     */
    public static function getKey($cert) {
        return self::PREFIX . hash(self::HASH, trim($cert));
    }
    
    /**
     * Get the serial number for the next certificate.
     * 
     * @return int The serial number for the next certificate.
     */
    public function getSerial() {
        return (int) $this->redis->get('cert_serial');
    }
    
}
