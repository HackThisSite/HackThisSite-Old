<?php
class Forums {
	
	var $data;
	
	public function loginData() {
		$cookieName = Config::get("forums:cookie");
		
		if (empty($_COOKIE[$cookieName . '_sid'])) return array('loggedIn' => false, 'group' => 'guests');
		
		if (apc_exists(((string) $_COOKIE[$cookieName . '_sid']) . '_session')) 
			return apc_fetch(((string) $_COOKIE[$cookieName . '_sid']) . '_session');
			
		$loginData = $this->getLoginData();
		apc_store(((string) $_COOKIE[$cookieName . '_sid']) . '_session', $loginData, 10);
		
		return $loginData;
	}
	
	public function getUsername($id) {
		if (apc_exists('username_' . $id)) return apc_fetch('username_' . $id);
		
		$username = $this->realGetUsername($id);
		apc_store('username_' . $id, $username, 3600);
		return $username;
	}
	
	private function getLoginData() {
		if (empty($this->data)) $this->data = Data::singleton();
		$cookieName = Config::get("forums:cookie");

		$sessionTable = Config::get("forums:prefix") . 'sessions';
		$userTable = Config::get("forums:prefix") . 'users';
		$groupTable = Config::get("forums:prefix") . 'groups';
		
		$sess = $this->data->escape($_COOKIE[$cookieName . '_sid']);
		$u = $this->data->escape($_COOKIE[$cookieName . '_u']);
		
		$query = $this->data->query('SELECT user_id, ' . $userTable . '.username, ' . $groupTable . '.group_name FROM ' . $sessionTable . ',' . $userTable . ',' . $groupTable . ' WHERE session_user_id != 1 AND session_id = ' . $sess . ' AND session_user_id = ' . $u . ' AND session_ip = \'' . $_SERVER['REMOTE_ADDR'] . '\' AND user_id = ' . $u . ' AND ' . $userTable . '.group_id = ' . $groupTable . '.group_id');
		
		if ($query['count'] == 0) 
			return array('loggedIn' => false, 'group' => 'guests');
		
		return array('loggedIn' => true, 'id' => $query['rows'][0]['user_id'], 'username' => $query['rows'][0]['username'], 'group' => strtolower($query['rows'][0]['group_name']));
	}
	
	private function realGetUsername($id) {
		if (empty($this->data)) $this->data = Data::singleton();
		$username = $this->data->query('SELECT username FROM ' . Config::get("forums:prefix") . 'users WHERE user_id = ' . intval($id) . ' LIMIT 1');
		return (!empty($username['rows'][0]['username']) ? $username['rows'][0]['username'] : 'Anonymous');
	}
	
}
