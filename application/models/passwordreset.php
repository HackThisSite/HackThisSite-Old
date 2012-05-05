<?php
/**
 * Password Reset
 * 
 * @package Model
 */
class passwordReset extends mongoBase {
    
    const ERR_INVALID = 'Invalid id.';
    const TTL = 3600;
    
    private $redis;
    
    /**
     * Create a new instance.
     * 
     * @param resource $connection A Redis connection.
     */
    public function __construct($connection) {
        $this->redis = $connection;
    }
    
    /**
     * Reset a user's password.
     * 
     * @param string $userId The user id.
     * @param string $email The email to send to.
     * 
     * @return string The id to reference in the future.
     */
    public function reset($userId, $email) {
        $id = $this->genHash();
        $this->redis->set('pr_' . $id, $_SERVER['REMOTE_ADDR'] . ';' . $userId, self::TTL);
        
        if (Config::get('system:mail')) $this->mail($id, $email);
        return $id;
    }
    
    /**
     * Reset a user's auth.
     * 
     * @param string $userId The user id.
     * @param string $email The email to send it.
     * 
     * @return string The id to reference in future.
     */
    public function auth($userId, $email) {
        $id = $this->genHash();
        $this->redis->set('ar_' . $id, $_SERVER['REMOTE_ADDR'] . ';' . $userId, self::TTL);
        
        if (Config::get('system:mail')) $this->mail($id, $email);
        return $id;
    }
    
    private function genHash() {
        return hash('md5', rand());
    }
    
    private function mail($id, $email) {
        // Need to do
    }
    
    /**
     * Get information on a password reset.
     * 
     * @param string $id The reference id.
     * @param bool $auth True if this is a auth reset.
     * 
     * @return mixed Reset information, or an error string.
     */
    public function get($id, $auth = false) {
        $id = $this->clean($id);
        $info = $this->redis->get(($auth ? 'ar' : 'pr') . '_' . $id);
        
        if (empty($info)) return self::ERR_INVALID;
        $array = explode(';', $info);
        
        if ($array[0] != $_SERVER['REMOTE_ADDR'])
            return self::ERR_INVALID;
        
        return $array;
    }
    
}
