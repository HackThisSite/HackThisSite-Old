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
            $key = Cache::PREFIX . 'user_' . self::$data['username'];
            apc_store($key, session_id(), 300);
        }
    }
    
    /**
     * Destroy the current session (logout).
     */
    public static function destroy() {
        if (self::isLoggedIn()) apc_delete(Cache::PREFIX . 'user_' . self::$data['username']);
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
    
    public static function setExternalVars($sid, $data) {
        apc_add(Cache::PREFIX . 'sessionReq_' . $sid, 
            $data, 1860);
        // The logic behind 1860:  If they have a heartbeat enabled, this 
        // should be cleared within 5 minutes if they're online.  If they 
        // don't have a heartbeat enabled, and they're inactive for 30 
        // minutes, they're not going to be logged in anyways.  (31m for 
        // error margins)
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
        
        apc_delete(Cache::PREFIX . 'user_' . $username);
    }
    
    public static function getId() {
        return session_id();
    }
}
