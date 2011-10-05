<?php
class Forums {
	
	var $data;
	var $cookieName;
	
	public function loginData() {
		$this->data = Data::singleton();
		$this->cookieName = $this->data->get('cookie_name');
		$cookieName = $this->cookieName;
		
		if (empty($_COOKIE[$cookieName . '_sid'])) return array('loggedIn' => false, 'group' => 'guests');
		
		if (apc_exists(((string) $_COOKIE[$cookieName . '_sid']) . '_session')) 
			return apc_fetch(((string) $_COOKIE[$cookieName . '_sid']) . '_session');
			
		$loginData = $this->getLoginData();
		apc_store(((string) $_COOKIE[$cookieName . '_sid']) . '_session', $loginData, 10);
		
		return $loginData;
	}
	
	private function getLoginData() {
		$cookieName = $this->cookieName;

		$sessionTable = $GLOBALS['config']['forums']['prefix'] . 'sessions';
		$userTable = $GLOBALS['config']['forums']['prefix'] . 'users';
		$groupTable = $GLOBALS['config']['forums']['prefix'] . 'groups';
		
		$sess = $this->data->escape($_COOKIE[$cookieName . '_sid']);
		$u = $this->data->escape($_COOKIE[$cookieName . '_u']);
		
		$query = $this->data->query('SELECT ' . $userTable . '.username, ' . $groupTable . '.group_name FROM ' . $sessionTable . ',' . $userTable . ',' . $groupTable . ' WHERE session_user_id != 1 AND session_id = ' . $sess . ' AND session_user_id = ' . $u . ' AND session_ip = \'' . $_SERVER['REMOTE_ADDR'] . '\' AND user_id = ' . $u . ' AND ' . $userTable . '.group_id = ' . $groupTable . '.group_id');
		
		if ($query['count'] == 0) 
			return array('loggedIn' => false, 'group' => 'guests');
		
		return array('loggedIn' => true, 'username' => $query['rows'][0]['username'], 'group' => strtolower($query['rows'][0]['group_name']));
	}
	
}
