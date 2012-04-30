<?php
class irc extends mongoBase {
    
    private $redis;
    
    public function __construct($connection) {
        $this->redis = $connection;
    }
    
    public function getPending($username) {
        return $this->redis->sMembers('linkReqs_' . $username);
    }
    
    public function addNick($username, $nick) {
        $this->redis->sAdd('userNicks_' . $username, $nick);
        $this->redis->set('nick2User_' . $nick, $username);
        $this->delNick($username, $nick);
    }
    
    public function delNick($username, $nick) {
        $this->redis->sRem('linkReqs_' . $username, $nick);
    }
    
    public function getNicks($username) {
        return $this->redis->sMembers('userNicks_' . $username);
    }
    
    public function delAcceptedNick($username, $nick) {
        $this->redis->sRem('userNicks_' . $username, $nick);
    }
    
    public function getOnline($resolve = true) {
        $online = $this->redis->sMembers('usersOnline');
        if (!$resolve) return $online;
        
        $keys = array();
        
        foreach ($online as $nick) {
            $keys[] = 'nick2User_' . $nick;
        }
        
        $results = $this->redis->mGet($keys);
        if (empty($results)) return array('usernames' => array(), 'unknown' => 0);
        
        $usernames = array_filter($results);
        $unknown = count($keys) - count($usernames);
        
        return array('usernames' => $usernames, 'unknown' => $unknown);
    }
    
    public function isOnline($username) {
        return $this->redis->sInter('usersOnline', 'userNicks_' . $username);
    }
    
}
