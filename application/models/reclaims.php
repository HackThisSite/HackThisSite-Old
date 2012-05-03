<?php
class reclaims extends mongoBase {
    
    var $groups = array(
        3 => 'user', 
        4 => 'admin', 
        5 => 'developer', 
        6 => 'mod'
    );
    private $mongo;
    private $db;
    
    public function __construct($connection) {
        $db = Config::get('mongo:db');
        $this->mongo = $connection->$db;
        $this->db = $connection->$db->unimportedUsers;
    }
    
    public function get($username) {
        return $this->db->findOne(array('username' => $this->clean($username)));
    }
    
    public function authenticate($username, $password) {
        $data = $this->get($username);
        if (empty($data)) return false;
        
        return $this->checkPassword($username, $password, $data['password']);
    }
    
    function checkPassword($username, $password, $hash) {
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
    
    public function import($username, $password) {
        $data = $this->get($username);
        $this->db->remove(array('username' => $this->clean($username)));
        
        $users = new users(ConnectionFactory::get('mongo'));
        $id = $users->create($username, $password, $data['email'], 
            $data['hideEmail'], $this->groups[$data['mgroup']]);
        
        $newRef = MongoDBRef::create('users', $id);
        $oldRef = MongoDBRef::create('unimportedUsers', $data['_id']);
        
        $this->mongo->news->update(array('user' => $oldRef), array('$set' => array('user' => $newRef)));
        $this->mongo->articles->update(array('user' => $oldRef), array('$set' => array('user' => $newRef)));
    }
}
