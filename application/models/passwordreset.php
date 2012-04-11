<?php
class passwordReset extends mongoBase {
	
	const ERR_INVALID = 'Invalid id.';
	
	private $redis;
	
	public function __construct($connection) {
		$this->redis = $connection;
	}
	
	public function reset($userId, $email) {
		$id = hash('md5', rand());
		$this->redis->set('pr_' . $id, $_SERVER['REMOTE_ADDR'] . ';' . $userId);
		
		if (Config::get('system:mail')) {
			// Need to do
		}
		
		return $id;
	}
	
	public function get($id) {
		$id = $this->clean($id);
		$info = $this->redis->get('pr_' . $id);
		
		if (empty($info)) return self::ERR_INVALID;
		$array = explode(';', $info);
		
		if ($array[0] != $_SERVER['REMOTE_ADDR'])
			return self::ERR_INVALID;
		
		return $array;
	}
	
}
