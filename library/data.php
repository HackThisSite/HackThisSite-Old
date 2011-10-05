<?php
// Raw data interface
class Data {
	
	private static $instance;
	var $redis;
	var $mysql;
	
	private function __construct() {}
	
	public static function singleton() {
		if (!isset(self::$instance)) {
			$className = __CLASS__;
			self::$instance = new $className;
		}
		return self::$instance;
	}
    
	public function __clone() {
		trigger_error('Clone is not allowed.', E_USER_ERROR);
	}

	public function __wakeup() {
		trigger_error('Unserializing is not allowed.', E_USER_ERROR);
	}
	
	public function get($key, $ttl = 10) {
		if (apc_exists($key)) return apc_fetch($key);
		if (empty($this->redis)) $this->connectRedis();
		$return = $this->redis->get($key);
		
		if ($return != false) apc_store($key, $return, $ttl);
		return $return;
	}
	
	public function zRangeGet($key, $start, $end, $withscores = false, $ttl = 30) {
		$apcKey = hash('adler32', serialize(array('key' => $key, 'start' => $start, 'end' => $end, 'withscores' => $withscores)));
		
		if (apc_exists($apcKey)) return apc_fetch($apcKey);
		if (empty($this->redis)) $this->connectRedis();
		$return = $this->redis->zRange($key, $start, $end, $withscores);
		
		if ($return != false) apc_store($apcKey, $return, $ttl);
		return $return;
	}
	
	public function hGet($key, $field, $ttl = 3600) {
		$apcKey = 'hash_' . $key . '_' . $field;
		
		if (apc_exists($apcKey)) return apc_fetch($apcKey);
		if (empty($this->redis)) $this->connectRedis();
		$return = $this->redis->hGet($key, $field);
		
		if ($return != false) apc_store($apcKey, $return, $ttl);
		return $return;
	}
	
	public function hGetAll($key, $ttl = 3600) {
		$apcKey = 'hash_' . $key;
		
		if (apc_exists($apcKey)) return apc_fetch($apcKey);
		if (empty($this->redis)) $this->connectRedis();
		$return = $this->redis->hGetAll($key);
		
		if ($return != false) apc_store($apcKey, $return, $ttl);
		return $return;
	}
	
	public function hSet($key, $hashKey, $value) {
		if (empty($this->redis)) $this->connectRedis();
		return $this->redis->hSet($key, $hashKey, $value);
	}
	
	public function sRandMember($key) {
		if (empty($this->redis)) $this->connectRedis();
		return $this->redis->sRandMember($key);
	}
	
	public function dbSize() {
		if (empty($this->redis)) $this->connectRedis();
		return $this->redis->dbSize();
	}
	
	public function info() {
		if (empty($this->redis)) $this->connectRedis();
		return $this->redis->info();
	}
	
	public function bgSave() {
		if (empty($this->redis)) $this->connectRedis();
		return $this->redis->bgSave();
	}
	
	public function zAdd($key, $score, $value) {
		if (empty($this->redis)) $this->connectRedis();
		return $this->redis->zAdd($key, $score, $value);
	}
	
	public function zRem($key, $value) {
		if (empty($this->redis)) $this->connectRedis();
		return $this->redis->zRem($key, $value);
	}
	
	private function connectRedis() {
		$this->redis = new Redis();
		$this->redis->connect($GLOBALS['config']['redis']);
	}
	
	
	public function query($query, $cache = true, $ttl = 3600) {
		if ($cache && apc_exists($key = 'query_' . hash('adler32', $query))) 
			return apc_fetch($key);
		if (empty($this->mysql)) $this->connectMysql();
		$result = mysqli_query($this->mysql, $query);
		$data = array();
		
		$data['count'] = mysqli_num_rows($result);
		$data['rows'] = array();
		$data['error'] = mysqli_error($this->mysql);
		
		while ($array = mysqli_fetch_assoc($result)) 
			array_push($data['rows'], $array);
		
		if ($cache) apc_add($key, $data, $ttl);
		return $data;
	}
	
	public function escape($var, $cache = true, $ttl = 3600) {
		if ($cache && apc_exists($key = 'escape_' . hash('adler32', $var))) 
			return apc_fetch($key);
		if (empty($this->mysql)) $this->connectMysql();
		$return = '"' . mysqli_real_escape_string($this->mysql, $var) . '"';
		
		if ($cache) apc_add($key, $return, $ttl);
		return $return;
	}
	
	private function connectMysql() {
		extract($GLOBALS['config']['db']);
		$this->mysql = mysqli_connect($host, $user, $pass, $db);
	}
	
}
