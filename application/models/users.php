<?php
class users extends baseModel {
	
	const ACCT_OPEN = 1;
	const ACCT_LOCKED = 2;
	
	const DEFAULT_GROUP = 'admin';
	
    var $hasSearch = false;
    var $hasRevisions = false;
    
    public function __construct($mongo) {
        $db       = Config::get(self::KEY_DB);
        $this->mongo = $mongo->$db;
        $this->db = $mongo->$db->users;
    }
    
	public function get($id, $idlib = true, $justOne = true) {
        if ($idlib) {
            $idLib = new Id;
            $keys = $idLib->dissectKeys($id, 'user');

            $query = array('username' => $this->clean($keys['username']));
        } else {
            $query = array('_id' => $this->_toMongoId($id));
        }
        
        if ($justOne) return $this->resolveDeps($this->db->findOne($query));
        $users = iterator_to_array($this->db->find($query));
        
        foreach ($users as $key => $user) { 
			$users[$key] = $this->resolveCerts($user);
		}
		
		return $users;
	}
	
	private function resolveDeps($user) {
		$user = $this->resolveCerts($user);
		$user = $this->resolveNotes($user);
		return $user;
	}
	
	private function resolveNotes($user) {
		if (empty($user['notes'])) return $user;
		
		foreach ($user['notes'] as $k => $note) {
			$user['notes'][$k]['user'] = MongoDBRef::get($this->mongo, $user['notes'][$k]['user']);
		}
		
		return $user;
	}
	
	private function resolveCerts($user) {
        if (!empty($user['certs'])) {
			$certs = new certs(ConnectionFactory::get('redis'));
			
			foreach ($user['certs'] as $key => $certKey) {
				$user['certs'][$key] = openssl_x509_parse($certs->get($certKey), false);
				$user['certs'][$key]['certKey'] = $certKey;
				$user['certs'][$key]['subject']['organizationName'] = $this->clean($user['certs'][$key]['subject']['organizationName']);
			}
		}
		return $user;
	}
	
	public function validate($username, $password, $email, $hideEmail, $group, $creating = true) {
		if (strpos($username, '\'') || strpos($username, '"')) return 'Invalid username.';
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
		if (!preg_match('/^[A-Za-z0-9 _-]+$/', $username)) return 'Invalid username';
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
			'group' => ($group == null ? self::DEFAULT_GROUP : $group),
			'auths' => array('password')
		);
		if (!$creating && !CheckAcl::can('changeUsername')) unset($entry['username']);
		if (!$creating && !CheckAcl::can('changeAcctStatus')) unset($entry['status']);
		if (!$creating && !CheckAcl::can('editAcl')) unset($entry['group']);
		if (!$creating && $passEmpty) unset($entry['password']);
		
		if (!$creating && isset($entry['username'])) Session::setVar('username', $entry['username']);
		if (!$creating && isset($entry['group'])) Session::setVar('group', $entry['group']);
		if (!$creating) {
			Session::setVar('email', $entry['email']);
			unset($entry['auth']);
		}
		
		return $entry;
	}
	
	public function authenticate($username, $password) {
		$auths = array('Password', 'Certificate', 'CAP');
		$applicable = array();
		
		foreach ($auths as $auth) {
			$good = call_user_func(array($this, 'qualify' . $auth), 
				$username, $password);
			if ($good) $applicable[] = $auth;
		}
		
		foreach ($applicable as $auth) {
			$good = call_user_func(array($this, 'check' . $auth),
				$username, $password);
			if ($good != false) {
				if ($good['status'] == self::ACCT_LOCKED) return 'User banned.';
				
				Session::setBatchVars($good);
				return true;
			}
		}
		
		return 'Invalid username/password';
	}
	
	public function preAdd($userId, $certKey) {
		$user = $this->db->findOne(array('_id' => $this->_toMongoId($userId)));
		if ($user == null) return 'Invalid user id.';
		
		if (!empty($user['certs']) && count($user['certs']) >= 5) return 'You are only allowed 5 certificates.';
		return true;
	}
	
	public function addCert($userId, $certKey) {
		$this->db->update(array('_id' => $this->_toMongoId($userId)), 
			array('$push' => array('certs' => $certKey)));
		return true;
	}
	
	public function removeCert($userId, $certKey) {
		$this->db->update(array('_id' => $this->_toMongoId($userId)),
			array('$pull' => array('certs' => $certKey)));
		return true;
	}
	
	public function changeAuth($userId, $password, $certificate, $certAndPass, $autoauth) {
		$auths = array();
		if ($password) array_push($auths, 'password');
		if ($certificate) array_push($auths, 'certificate');
		if ($certAndPass) array_push($auths, 'cert+pass');
		if ($autoauth) array_push($auths, 'autoauth');
		
		if (empty($auths)) return 'You need some method of authentication!';
		$this->db->update(array('_id' => $this->_toMongoId($userId)),
			array('$set' => array('auths' => $auths)));
	}
	
	public function hash($password, $username) {
		return crypt($password, $username);
	}
	
	public function resetPassword($userId) {
		$password = hash('crc32', rand());

		$userInfo = $this->db->findOne(array('_id' => $this->_toMongoId($userId)));
		$this->db->update(array('_id' => $this->_toMongoId($userId)),
			array('$set' => array('password' => $this->hash($password, $userInfo['username']))));
		
		return $password;
	}
	
	public function addNote($userId, $note) {
		$user = $this->db->findOne(array('_id' => $this->_toMongoId($userId)));
		
		if (!$user) return 'Invalid user id.';
		
		$note = $this->clean($note);
		
		if (empty($note)) return 'Invalid note.';
		
		$this->db->update(array('_id' => $this->_toMongoId($userId)), 
		array('$push' => array(
			'notes' => array(
				'user' => MongoDBRef::create('users', Session::getVar('_id')),
				'date' => time(),
				'text' => substr($note, 0, 160)
				))));
	}
	
	
	// AUTHENTICATION MECHANISMS
	private function qualifyPassword($username, $password) {
		if (!empty($username) && !empty($password)) return true;
		return false;
	}
	
	private function checkPassword($username, $password) {
		$user = $this->get($username);
		if (empty($user)) return false;
		if (!in_array('password', $user['auths'])) return false;
		
		if ($user['password'] == $this->hash($password, $username))
			return $user;
		
		return false;
	}
	
	
	private function qualifyCertificate($username, $password) {
		if (!empty($username) || !empty($password)) return false;
		if (empty($_SERVER['SSL_CLIENT_RAW_CERT'])) return false;
		return true;
	}
	
	private function checkCertificate() {
		$certs = new certs(ConnectionFactory::get('redis'));
		$userId = $certs->check($_SERVER['SSL_CLIENT_RAW_CERT']);
		
		if ($userId == null) return false;
		
		$user = $this->get($userId, false);
		if (!in_array('certificate', $user['auths'])) return false;
		
		return $user;
	}
	
	
	private function qualifyCAP($username, $password) {
		if (empty($username) || empty($password)) return false;
		if (empty($_SERVER['SSL_CLIENT_RAW_CERT'])) return false;
		return true;
	}
	
	private function checkCAP($username, $password) {
		$user = $this->get($username);
		
		// Check password authentication
		if (empty($user)) return false;
		if (!in_array('cert+pass', $user['auths'])) return false;
		
		if ($user['password'] != $this->hash($password, $username))
			return false;
		
		// Check certificate authentication
		$certs = new certs(ConnectionFactory::get('redis'));
		$userId = $certs->check($_SERVER['SSL_CLIENT_RAW_CERT']);
		
		if ($userId == null) return false;
		if ($userId != $user['_id']) return false;
		
		return $user;
	}
	
}
