<?php
class acl extends mongoBase {

    const KEY_DB     = "mongo:db";
    const CACHE_PREFIX  = "aclGroupPerms_";
    const CACHE_LIFE    = 60;
    private $db;
    
    public function __construct($connection) {
        $this->db = $connection->{Config::get(self::KEY_DB)};
    }
    
    public function aclForGroup($group) {
        $apc = apc_fetch(self::CACHE_PREFIX . $group);
        if (!empty($apc))
            return $apc;
            
        $perms = explode(',', $this->dbAclForGroup($group));
        apc_add(self::CACHE_PREFIX . $group, $perms, self::CACHE_LIFE);
        return $perms;
    }
    
    public function dbAclForGroup($group) {
        $permissions = $this->db->acl->findOne(array("group" => $this->clean($group)));
        return $permissions['permissions'];
    }
    
}
