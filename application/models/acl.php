<?php
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
        
    public function __construct($connection) {
        $this->redis = $connection;
    }
    
    public function can($group, $name, $cache = true) {
        $key = 'acl_' . $group . '_' . $name;
        if ($cache && apc_exists($key)) return apc_fetch($key);
        
        $result = $this->redis->sIsMember('acl_' . $group, $name);
        
        if ($cache) apc_add($key, $result, 60);
        return $result;
    }
    
}
