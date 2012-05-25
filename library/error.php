<?php
/**
 * Controller-to-View error management.
 * 
 * @package Library
 */
class Error {
    
    private static $errors = array();
    private static $notices = array();
    
    /**
     * Add a new error
     * 
     * @param string $error Error string.
     * @param bool $notice If the error is actually a notice.
     * 
     * @return bool Returns false so that controllers can return this 
     * function to show they died prematurely.
     */
    public static function set($error, $notice = false) {
        if ($notice) {
            Log::error($error);
            array_push(self::$notices, $error);
        } else {
            Log::error($error);
            array_push(self::$errors, $error);
        }
        
        return false;
    }
    
    /**
     * Check if there are any errors.
     * 
     * @return bool True if errors are present.
     */
    public static function has() {
        return !empty(self::$errors) || !empty(self::$notices);
    }
    
    /**
     * Returns all errors.
     * 
     * @return string[] Array of errors.
     */
    public static function getAllErrors() {
        return self::$errors;
    }
    
    /**
     * Returns all notices.
     * 
     * @return string[] Array of notices.
     */
    public static function getAllNotices() {
        return self::$notices;
    }
    
}
