<?php
/**
 * IRC
 * 
 * @package Model
 */
class irc extends mongoBase {
    
    private $redis;
    
    /**
     * Create a new instance.
     * 
     * @param resource $connection A Redis connection.
     */
    public function __construct($connection) {
        $this->redis = $connection;
    }
    
    /**
     * Get pending nicks.
     * 
     * @param string $username The username to use.
     * 
     * @return array The nicks pending.
     */
    public function getPending($username) {
        return $this->redis->sMembers('linkReqs_' . $username);
    }
    
    /**
     * Add a nick.
     * 
     * @param string $username The username to use.
     * @param string $nick The nick to use.
     */
    public function addNick($username, $nick) {
        $this->redis->sAdd('userNicks_' . $username, $nick);
        $this->redis->set('nick2User_' . $nick, $username);
        $this->delNick($username, $nick);
    }
    
    /**
     * Delete a nick.
     * 
     * @param string $username The username to use.
     * @param string $nick The nick to use.
     */
    public function delNick($username, $nick) {
        $this->redis->sRem('linkReqs_' . $username, $nick);
    }
    
    /**
     * Get the accepted nicks for a user.
     * 
     * @param string $username The username to use.
     * 
     * @return array An array of nicks.
     */
    public function getNicks($username) {
        return $this->redis->sMembers('userNicks_' . $username);
    }
    
    /**
     * Delete an accepted nick.
     * 
     * @param string $username The username to use.
     * @param string $nick The nick to use.
     */
    public function delAcceptedNick($username, $nick) {
        $this->redis->sRem('userNicks_' . $username, $nick);
    }
    
    /**
     * Get online users.
     * 
     * @param bool $resolve If IRC nicks should be resolved into usernames.
     * 
     * @return array An array of usernames/nicks and the number of unknown.
     */
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
    
    /**
     * Check if a user is online.
     * 
     * @param string $username The username to use.
     * 
     * @return array The nicks the user is online as.
     */
    public function isOnline($username) {
        return $this->redis->sInter('usersOnline', 'userNicks_' . $username);
    }
    
}
