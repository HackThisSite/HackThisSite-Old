<?php
class users extends baseModel {
	
	const ACCT_OPEN = 1;
	const ACCT_LOCKED = 2;
	
	const WARN_UNWARNED = 1;
	
	const DEFAULT_GROUP = 'admin';
	
    var $hasSearch = false;
    var $hasRevisions = false;
    
    public function __construct(Mongo $mongo) {
        $db       = Config::get(self::KEY_DB);
        $this->mongo = $mongo->$db;
        $this->db = $mongo->$db->users;
    }
    
	public function get($id, $idlib = true, $justOne = true) {
        if ($idlib) {
            $idLib = new Id;
            $keys = $idLib->dissectKeys($id, 'user');

            $query = array('username' => $keys['username']);
        } else {
            $query = array('_id' => $this->_toMongoId($id));
        }
        
        if ($justOne) return $this->db->findOne($query);
        return iterator_to_array($this->db->find($query));
	}
	
	public function validate($username, $password, $email, $hideEmail, $group, $creating = true) {
		$passEmpty = false;
		if (empty($password) && $creating) return 'Invalid password';
		if (empty($password)) $passEmpty = true;
		
		// Cleaning
		$username = $this->clean($username);
		$password = $this->hash($password, $username);
		$email = filter_var($email, FILTER_SANITIZE_EMAIL);
		$hideEmail = (bool) $hideEmail;
		
		// Error checking
		if (empty($username) && $creating) return 'Invalid username.';
		if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) 
			return 'Invalid email.';
		if ($group != null && !in_array($group, acl::$acls)) return 'Invalid group.';
		
		if ($creating) {
			$user = $this->get($username);
			if (!empty($user)) return 'Username taken.';
		}
		
		$entry = array(
			'username' => $username,
			'password' => $password,
			'email' => $email,
			'status' => self::ACCT_OPEN,
			'hideEmail' => $hideEmail,
			'warnLevel' => self::WARN_UNWARNED,
			'group' => ($group == null ? self::DEFAULT_GROUP : $group)
		);
		if (!$creating && !CheckAcl::can('changeUsername')) unset($entry['username']);
		if (!$creating && !CheckAcl::can('changeAcctStatus')) unset($entry['status']);
		if (!$creating && !CheckAcl::can('changeWarnLevel')) unset($entry['warnLevel']);
		if (!$creating && !CheckAcl::can('editAcl')) unset($entry['group']);
		if (!$creating && $passEmpty) unset($entry['password']);
		
		if (!$creating && isset($entry['username'])) Session::setVar('username', $entry['username']);
		if (!$creating && isset($entry['group'])) Session::setVar('group', $entry['group']);
		if (!$creating) {
			Session::setVar('email', $entry['email']);
		}
		
		return $entry;
	}
	
	public function authenticate($username, $password) {
		$user = $this->get($username);
		if (empty($user)) return false;
		
		if ($user['password'] == $this->hash($password, $username)) {
		    Session::init();
			Session::setBatchVars($user);
			return true;
		}
		return false;
	}
	
	public function hash($password, $username) {
		return crypt($password, $username);
	}
}
