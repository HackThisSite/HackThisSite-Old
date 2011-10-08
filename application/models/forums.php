<?php
class Forums {
	
	var $data;
	
	public function loginData() {
		$cookieName = $GLOBALS['config']['forums']['cookie'];
		
		if (empty($_COOKIE[$cookieName . '_sid'])) return array('loggedIn' => false, 'group' => 'guests');
		
		if (apc_exists(((string) $_COOKIE[$cookieName . '_sid']) . '_session')) 
			return apc_fetch(((string) $_COOKIE[$cookieName . '_sid']) . '_session');
			
		$loginData = $this->getLoginData();
		apc_store(((string) $_COOKIE[$cookieName . '_sid']) . '_session', $loginData, 10);
		
		return $loginData;
	}
	
	private function getLoginData() {
		if (empty($this->data)) $this->data = Data::singleton();
		$cookieName = $GLOBALS['config']['forums']['cookie'];

		$sessionTable = $GLOBALS['config']['forums']['prefix'] . 'sessions';
		$userTable = $GLOBALS['config']['forums']['prefix'] . 'users';
		$groupTable = $GLOBALS['config']['forums']['prefix'] . 'groups';
		
		$sess = $this->data->escape($_COOKIE[$cookieName . '_sid']);
		$u = $this->data->escape($_COOKIE[$cookieName . '_u']);
		
		$query = $this->data->query('SELECT user_id, ' . $userTable . '.username, ' . $groupTable . '.group_name FROM ' . $sessionTable . ',' . $userTable . ',' . $groupTable . ' WHERE session_user_id != 1 AND session_id = ' . $sess . ' AND session_user_id = ' . $u . ' AND session_ip = \'' . $_SERVER['REMOTE_ADDR'] . '\' AND user_id = ' . $u . ' AND ' . $userTable . '.group_id = ' . $groupTable . '.group_id');
		
		if ($query['count'] == 0) 
			return array('loggedIn' => false, 'group' => 'guests');
		
		return array('loggedIn' => true, 'id' => $query['rows'][0]['user_id'], 'username' => $query['rows'][0]['username'], 'group' => strtolower($query['rows'][0]['group_name']));
	}
	
}
