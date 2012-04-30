<?php
/**
 * Syslog operations
 * 
 * @package Library
 */
class Log {
    
    private static $opened = false;
    
    /**
     * Write a new message to log.
     * 
     * @param int $priority One of the PHP Syslog priority constants.
     * @param string $message Message to log.
     * 
     * @return bool True on success.
     */
    public static function write($priority, $message) {
        if (!self::$opened) {
            openlog('hts', LOG_ODELAY, LOG_USER);
            self::$opened = true;
        }
        
        $logHeader = (!Session::isLoggedIn() ? 'Guest' : 'User ' . Session::getVar('username')) . 
            ' (' . microtime() . '):  ';
        return syslog($priority, $logHeader . $message);
    }
    
    /**
     * Closes the syslog connection at the close of the script.
     */
    public function __destruct() {
        closelog();
    }
    
}
