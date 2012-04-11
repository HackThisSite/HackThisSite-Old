<?php
class LazyRedis {
	
	private $conn;
	private $ip;
	private $port;
	private $connected = false;
	
	public function __construct($ip = '127.0.0.1', $port = 6379) {
		$this->ip = $ip;
		$this->port = $port;
	}
	
	private function connect() {
		$this->connected = true;
		$this->conn = new Redis();
		$this->conn->pconnect($this->ip, $this->port);
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
