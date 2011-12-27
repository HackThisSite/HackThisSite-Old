<?php
class Session {
    
    private static $data = array();
    
    public static function init($id = null) {
        session_start();
        if ($id !== null && $id != session_id()) session_id($id);
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
        foreach (self::$data as $key => $entry) {
            $_SESSION[$key] = $entry;
        }
    }
    
    public static function destroy() {
        self::$data = array();
        session_destroy();
    }
}
