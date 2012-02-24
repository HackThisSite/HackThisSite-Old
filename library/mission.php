<?php
class Mission {
    
    static $missions;
    
    static public function generatePassword($handle) {
        if (($var = Session::getVar('mission-' . $handle . '_password')) != false)
            return $var;
        
        $password = substr(md5(rand()), 0, 15);
        Session::setVar('mission-' . $handle . '_password', $password);
        return $password;
    }
    
    static public function finishMission($handle, $id) {
        self::destroyPassword($handle);
        
        if (Session::isLoggedIn()) {
            $missions = self::getModel();
            $missions->done(Session::getVar('_id'), $id);
        }
    }
    
    static public function destroyPassword($handle) {
        Session::setVar('mission-' . $handle . '_password', false);
    }
    
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
