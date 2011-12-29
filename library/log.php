<?php
class Log {
    
    private static $opened = false;
    
    public static function write($priority, $message) {
        if (!self::$opened) {
            openlog('hts', LOG_ODELAY, LOG_USER);
            self::$opened = true;
        }
        
        $logHeader = (!Session::isLoggedIn() ? 'Guest' : 'User ' . Session::getVar('username')) . 
            ' (' . microtime() . '):  ';
        return syslog($priority, $logHeader . $message);
    }
    
    public function __destruct() {
        closelog();
    }
    
}
