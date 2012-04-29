<?php
class redisSession {
	
	const PREFIX = 'PHPREDIS_SESSION:';
	private $redis;
	
	public function __construct($connection) {
		$this->redis = $connection;
	}
	
	public function get($id) {
		$realSession = $_SESSION;
		session_decode($this->redis->get(self::PREFIX . $id));
		$userSession = $_SESSION;
		$_SESSION = $realSession;
		
		return $userSession;
	}
	
	public function destroy($id) {
		$this->redis->del(self::PREFIX . $id);
	}
}
