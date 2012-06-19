<?php
/**
 * Forces the lazy loading of a Redis connection.
 * 
 * @package Library
 */
class LazyRedis {
    
    private $conn;
    private $isArray;
    private $host;
    private $connected = false;
    
    /**
     * Creates a new LazyRedis instance.
     * 
     * @param string $ip I.P. address to connect to.
     * @param int $port Port of the Redis server.
     */
    public function __construct($isArray = false, $host = '127.0.0.1') {
        $this->isArray = $isArray;
        $this->host = $host;
    }
    
    private function connect() {
        $this->connected = true;
        
        if ($this->isArray) {
            $this->conn = new RedisArray($this->host['hosts'], array('previous' => $this->host['previous']));
        } else {
            $this->conn = new Redis();
            
            list($ip, $port) = explode(':', $this->host);
            $this->conn->pconnect($ip, $port);
        }
        
        
        $file = Config::get('redis:passwordFile');
        if (!empty($file)) {
            $this->conn->auth(trim(file_get_contents($file)));
        }
    }
    
    public function __call($method, $arguments) {
        if (!$this->connected) $this->connect();
        return call_user_func_array(array($this->conn, $method), $arguments);
    }
    
    public function __get($name) {
        if (!$this->connected) $this->connect();
        return $this->conn->$name;
    }
    
}
