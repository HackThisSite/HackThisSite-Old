<?php
class passwordReset extends mongoBase {
    
    const ERR_INVALID = 'Invalid id.';
    const TTL = 3600;
    
    private $redis;
    
    public function __construct($connection) {
        $this->redis = $connection;
    }
    
    public function reset($userId, $email) {
        $id = $this->genHash();
        $this->redis->set('pr_' . $id, $_SERVER['REMOTE_ADDR'] . ';' . $userId, self::TTL);
        
        if (Config::get('system:mail')) $this->mail($id, $email);
        return $id;
    }
    
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
