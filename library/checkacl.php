<?php
class CheckAcl {
    
    private static $populated = false;
    private static $acl;
    
    private static function _populate() {
        self::$acl = new acl(ConnectionFactory::get('redis'));
        self::$populated = true;
    }
    
    public static function can($name) {
		return true;
        if (!self::$populated) self::_populate();
        $group = Session::getVar('group');
        
        if (empty($group))
            $group = 'guest';
        
        $result = self::$acl->can($group, $name);
        return $result;
    }
}
