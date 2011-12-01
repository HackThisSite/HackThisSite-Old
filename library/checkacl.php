<?php
class CheckAcl {
    
    private static $populated = false;
    private static $acl;
    
    private static function _populate() {
        self::$acl = new acl(ConnectionFactory::get('mongo'));
        self::$populated = true;
    }
    
    public static function can($name) {
        if (!self::$populated) self::_populate();
        $group = Session::getVar('group');
        
        if (empty($group))
            $group = 'guest';
        
        $perms = self::$acl->aclForGroup($group);
        return in_array($name, $perms);
    }
}
