<?php
/**
 * Functions to assist in the execution of missions.
 * 
 * @package Library
 */
class Mission {
    
    static $missions;
    
    /**
     * Generate a new password for a mission or return the current one.
     * 
     * @param string $handle The name the mission goes by.
     * 
     * @return string Password for the mission.
     */
    static public function generatePassword($handle) {
        if (($var = Session::getVar('mission-' . $handle . '_password')) != false)
            return $var;
        
        $password = substr(md5(rand()), 0, 15);
        Session::setVar('mission-' . $handle . '_password', $password);
        return $password;
    }
    
    /**
     * Mark a mission as finished and destroy the old password.
     * 
     * @param string $handle The name the mission goes by.
     * @param string $id MongoDB id of the mission.
     */
    static public function finishMission($handle, $id) {
        self::destroyPassword($handle);
        
        if (Session::isLoggedIn()) {
            $missions = self::getModel();
            $missions->done(Session::getVar('_id'), $id);
        }
    }
    
    /**
     * Destroy the password for a mission.
     * 
     * @param string $handle The name the mission goes by.
     */
    static public function destroyPassword($handle) {
        Session::setVar('mission-' . $handle . '_password', false);
    }
    
    /**
     * Determine if a user has finished a mission.
     * 
     * @param string $id Mission id.
     * 
     * @return bool True if the user has completed the mission before.
     */
    static public function hasDone($id) {
        if (!Session::isLoggedIn())
            return false;
        
        $missions = self::getModel();
        return (bool) $missions->getTimesDone(Session::getVar('_id'), $id);
    }
    
    static private function getModel() {
        if (empty(self::$missions))
            self::$missions = new missions(ConnectionFactory::get('mongo'));
        
        return self::$missions;
    }
    
}
