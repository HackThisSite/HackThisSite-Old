<?php
/**
 * Access Control List
 * 
 * @package Model
 */
class acl extends mongoBase {

    const KEY_DB     = "mongo:db";
    const CACHE_PREFIX  = "aclGroupPerms_";
    const CACHE_LIFE    = 60;
    private $redis;
    
    static $acls = array(
        'user',
        'developer',
        'admin'
        );
    
    /**
     * Initializes a new acl model.
     * 
     * @param resource $connection Redis connection.
     */
    public function __construct($connection) {
        $this->redis = $connection;
    }
    
    /**
     * Checks if a user can do something.
     * 
     * @param string $group The user's group.
     * @param string $name Permission name.
     * @param bool $cache True if cache is allowed.
     * 
     * @return bool True if the user is allowed.
     */
    protected function can($group, $name) {
        $result = $this->redis->sIsMember('acl_' . $group, $name);
        return $result;
    }
    
}
