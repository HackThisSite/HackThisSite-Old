<?php
class LazyMongo {
	
	private $conn;
	private $connString;
	private $options;
	private $connected = false;
	
	public function __construct($connString, $options) {
		$this->connString = $connString;
		$this->options = $options;
	}
	
	private function connect() {
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
