<?php
class reclaims extends mongoBase {
    
    private $db;
    
    public function __construct($connection) {
        $this->db = $connection->unimportedUsers;
    }
    
    public function authenticate($username, $password) {
        
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
}
