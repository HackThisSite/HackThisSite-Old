<?php
/**
 * Forces the lazy loading of a MongoDB connection.
 * 
 * @package Library
 */
class LazyMongo {
    
    private $conn;
    private $connString;
    private $options;
    private $connected = false;
    
    /**
     * Creates a new LazyMongo instance.
     * 
     * @param string $connString MongoDB connection information.
     * @param array $options MongoDB connection options.
     */
    public function __construct($connString, $options) {
        $this->connString = $connString;
        $this->options = $options;
    }
    
    private function connect() {
        $this->connected = true;
        $this->conn = new Mongo($this->connString, $this->options);
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
