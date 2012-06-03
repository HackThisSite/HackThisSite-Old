<?php
/**
 * Reclaims
 * 
 * @package Model
 */
class reclaims extends mongoBase {
    
    var $groups = array(
        3 => 'user', 
        4 => 'admin', 
        5 => 'developer', 
        6 => 'mod'
    );
    private $mongo;
    private $db;
    
    /**
     * Create a new instance.
     * 
     * @param resource $connection A MongoDB connection.
     */
    public function __construct($connection) {
        $db = Config::get('mongo:db');
        $this->mongo = $connection->$db;
        $this->db = $connection->$db->unimportedUsers;
    }
    
    /**
     * Get data on an unimported user.
     * 
     * @param string $username The username to look for.
     * 
     * @return array The user information as an array.
     */
    protected function get($username) {
        return $this->db->findOne(array('username' => $this->clean($username)));
    }
    
    /**
     * Authenticate a user with v3 mechanisms.
     * 
     * @param string $username The username to use.
     * @param string $password The password to use.
     * 
     * @return bool True if the user is authed.
     */
    public function authenticate($username, $password) {
        $data = $this->get($username);
        if (empty($data)) return false;
        
        return $this->checkPassword($username, $password, $data['password']);
    }
    
    private function checkPassword($username, $password, $hash) {
        $hashtype = substr($hash, 0, strpos($hash, '}')+1);
        $hashclean = (strpos($hash, '}') ? substr($hash, strpos($hash, '}')+1) : $hash);

        switch ($hashtype) {
            case '{SSHA}':
                $salt = substr(base64_decode($hashclean), -10);
                return ($hashclean == base64_encode(pack("H*",sha1($password.$salt)).$salt));
                break;
                
            case '{SHA}':
                return ($hashclean == base64_encode(pack("H*",sha1($password))));
                break;
                
            case '{HTS}':
                default:
                return ($hashclean == encodeString($username, $password));
                break;
        }
    }
    
    /**
     * Import an account.
     * 
     * @param string $username The username to use.
     * @param string $password The password to use.
     */
    public function import($username, $password) {
        $data = $this->get($username);
        $this->db->remove(array('username' => $this->clean($username)));
        
        $users = new users(ConnectionFactory::get('mongo'));
        $id = $users->create($username, $password, $data['email'], 
            $data['hideEmail'], $this->groups[$data['mgroup']], true);
        
        $newRef = MongoDBRef::create('users', $id);
        $oldRef = MongoDBRef::create('unimportedUsers', $data['_id']);
        
        $this->mongo->news->update(array('user' => $oldRef), array('$set' => array('user' => $newRef)));
        $this->mongo->articles->update(array('user' => $oldRef), array('$set' => array('user' => $newRef)));
        self::ApcPurge($data['_id']);
    }
}
