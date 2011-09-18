<?php
class Data {
	
	var $redis;
	
	public function get($key, $ttl = 10) {
		if (apc_exists($key)) return apc_fetch($key);
		if (empty($this->redis)) $this->connect();
		$return = $this->redis->get($key);
		
		if ($return != false) apc_store($key, $return, $ttl);
		return $return;
	}
	
	public function zRangeGet($key, $start, $end, $ttl = 30) {
		if (apc_exists($key)) return apc_fetch($key);
		if (empty($this->redis)) $this->connect();
		$return = $this->redis->zRange($key, $start, $end);
		
		if ($return != false) apc_store($key, $return, $ttl);
		return $return;
	}
	
	private function connect() {
		$CI =& get_instance();
		$ip = $CI->config->item('redis');
		
		$this->redis = new Redis();
		$this->redis->connect($ip);
	}
	
}
