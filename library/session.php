<?php
/**
 * Session management
 * 
 * @package Library
 */
class Session extends Cache {
    
    private static $data = array();
    
    /**
     * Start a new session.
     * 
     * @param string $id Session id to use.
     */
    public static function init($id = null) {
        session_start();
        if ($id !== null) session_id($id);
        self::$data = $_SESSION;
    }
    
    /**
     * Check if the user is logged in.
     * 
     * @return bool True if user is logged in.
     */
    public static function isLoggedIn() {
        return !empty(self::$data['username']);
    }
    
    /**
     * Set a variable in the session.
     * 
     * @param string $name Key to save under.
     * @param mixed $value Value of the variable.
     */
    public static function setVar($name, $value) {
        return self::$data[$name] = $value;
    }
    
    /**
     * Set an array of variables.
     * 
     * @param array $values Associative array of session variables.
     */
    public static function setBatchVars($values) {
        return self::$data = array_merge(self::$data, $values);
    }
    
    /**
     * Get a variable from the session.
     * 
     * @param string $name Name of the variable.
     * 
     * @return mixed The variable.
     */
    public static function getVar($name) {
        return (!empty(self::$data[$name]) ? self::$data[$name] : false);
    }
    
    /**
     * Get all of the variables in the session.
     * 
     * @return array Associative array.
     */
    public static function getAll() {
        return self::$data;
    }
    
    /**
     * Write session data
     */
    public static function write() {
        $_SESSION = array_merge($_SESSION, self::$data);
        
        if (self::isLoggedIn()) {
            $key = 'user_' . self::$data['username'];
            self::ApcAdd($key, session_id(), 300, $key);
        }
    }
    
    /**
     * Destroy the current session (logout).
     */
    public static function destroy() {
        if (!empty(self::$data['username'])) self::ApcPurge('user_' . self::$data['username']);
        self::$data = array();
        
        if (ini_get("session.use_cookies")) {
            $params = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000,
                $params["path"], $params["domain"],
                $params["secure"], $params["httponly"]
            );
        }
        session_destroy();
    }
    
    /**
     * Force logout another user.
     * 
     * @param string $username Username of user.
     * @param string $sid Session id of user.
     */
    public static function forceLogout($username, $sid) {
        if ($sid == session_id()) {
            $current = true;
            $data = self::$data;
        } else {
            $current = false;
            $session = new redisSession(ConnectionFactory::get('redis'));
            $data = $session->get($sid);
        }
        
        if (!empty($data['username'])) { // User is logged in
            if ($current) { // Current user
                self::destroy();
            } else { // Not current user
                $session->destroy($sid);
            }
        }
        
        self::ApcPurge('user_' . $username);
    }
}
