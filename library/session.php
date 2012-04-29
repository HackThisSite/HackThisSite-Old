<?php
class Session {
    
    private static $data = array();
    
    public static function init($id = null) {
        session_start();
        if ($id !== null) session_id($id);
        self::$data = $_SESSION;
    }
    
    public static function isLoggedIn() {
        return !empty(self::$data['username']);
    }
    
    public static function setVar($name, $value) {
        return self::$data[$name] = $value;
    }
    
    public static function setBatchVars($values) {
        return self::$data = array_merge(self::$data, $values);
    }
    
    public static function getVar($name) {
        return (!empty(self::$data[$name]) ? self::$data[$name] : false);
    }
    
    public static function getAll() {
        return self::$data;
    }
    
    public static function write() {
		$_SESSION = array_merge($_SESSION, self::$data);
		
		if (self::isLoggedIn()) {
			apc_store('user_' . self::$data['username'], session_id(), 300);
		}
    }
    
    public static function destroy() {
		if (!empty(self::$data['username'])) apc_delete('user_' . self::$data['username']);
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
    
    public static function getId() {
		return session_id();
	}
    
    public static function forceLogout($username, $sid) {
		if ($sid == self::getId()) {
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
		
		apc_delete('user_' . $username);
	}
}
