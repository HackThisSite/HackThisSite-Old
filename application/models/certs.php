<?php
class certs extends mongoBase {
    
    const HASH      = 'md5';
    const PREFIX    = 'cert_';
    private $redis;
    
    public function __construct($connection) {
        $this->redis = $connection;
    }
    
    public function preAdd($cert) {
        $exists = file_exists(Config::get('certs:location') . $this->getKey($cert) . Config::get('certs:extension'));
        
        if ($exists) return 'Duplicate certificate';
        return true;
    }
     
    public function add($cert) {
        $this->redis->incr('cert_serial');
        
        $fh = fopen(Config::get('certs:location') . $this->getKey($cert) . Config::get('certs:extension'), 'c');
        fwrite($fh, Session::getVar('_id') . ':' . trim($cert));
        fclose($fh);
        
        return true;
    }
    
    public function create($csr) {
        $cert = openssl_csr_sign($csr, Config::get('ssl:certificate'), 
            Config::get('ssl:key'), 365, Config::get('sslConf'), $this->getSerial());
        openssl_x509_export($cert, $output);
        
        return $output
    }
    
    public function get($certKey, $cut = true) {
        $cert = file_get_contents(Config::get('certs:location') . $certKey . Config::get('certs:extension'));
        return ($cut ? substr($cert, strpos($cert, ':') + 1) : $cert);
    }
    
    public function removeCert($certKey) {
        unlink(Config::get('certs:location') . $certKey . Config::get('certs:extension'));
    }
    
    public function check($cert) {
        $file = Config::get('certs:location') . $this->getKey($cert) . Config::get('certs:extension');
        if (!file_exists($file)) return null;
        $info = file_get_contents($file);

        return substr($info, 0, strpos($info, ':'));
    }
    
    public static function getKey($cert) {
        return self::PREFIX . hash(self::HASH, trim($cert));
    }
    
    public function getSerial() {
        return (int) $this->redis->get('cert_serial');
    }
    
}
