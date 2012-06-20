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
    public static function set($error, $notice = false, $supplementary = array()) {
        $extra = '';
        foreach ($supplementary as $name => $link) {
            $extra .= '<a href="' . $link . '">' . $name . '</a>, ';
        }
        if ($extra != '') { $extra = '&nbsp;&nbsp;' . substr($extra, 0, -2); }
        
        if ($notice) {
            array_push(self::$notices, $error . $extra);
        } else {
            Log::error($error);
            array_push(self::$errors, $error . $extra);
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
