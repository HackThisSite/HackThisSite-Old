<?php
/**
 * Redis Session Data
 * 
 * @package Model
 */
class redisSession {
    
    const PREFIX = 'PHPREDIS_SESSION:';
    private $redis;
    
    /**
     * Creates a new instance.
     * 
     * @param resource $connection A Redis connection.
     */
    public function __construct($connection) {
        $this->redis = $connection;
    }
    
    /**
     * Get information on a session.
     * 
     * @param string $id A session id.
     * 
     * @return array The session variables.
     */
    public function get($id) {
        $realSession = $_SESSION;
        session_decode($this->redis->get(self::PREFIX . $id));
        $userSession = $_SESSION;
        $_SESSION = $realSession;
        
        return $userSession;
    }
    
    /**
     * Destroy a session.
     * 
     * @param string $id A session id.
     */
    public function destroy($id) {
        $this->redis->del(self::PREFIX . $id);
    }
}
