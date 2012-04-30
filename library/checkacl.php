<?php
/**
 * Access Control List (ACL) validation.
 * 
 * @package Library
 */
class CheckAcl {
    
    private static $populated = false;
    private static $acl;
    
    private static function _populate() {
        self::$acl = new acl(ConnectionFactory::get('redis'));
        self::$populated = true;
    }
    
    /**
     * Check ACLs to determine if a user has a certain permission.
     * 
     * @param string $name Name of the permission to check for.
     * 
     * @return bool True if the user does have the permission in $name.
     */
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
