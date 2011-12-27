<?php
class Error {
    
    private static $errors = array();
    private static $notices = array();
    
    public static function set($error, $notice = false) {
        if ($notice) {
            array_push(self::$notices, $error);
        } else {
            array_push(self::$errors, $error);
        }
        
        return false;
    }
    
    public static function has() {
        return !empty(self::$errors) || !empty(self::$notices);
    }
    
    public static function getAllErrors() {
        return self::$errors;
    }
    
    public static function getAllNotices() {
        return self::$notices;
    }
    
}