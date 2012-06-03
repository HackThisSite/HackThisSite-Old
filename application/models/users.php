<?php
/**
 * Users
 * 
 * @package Model
 */
class users extends baseModel {
    
    const ACCT_OPEN = 1;
    const ACCT_LOCKED = 2;
    
    const DEFAULT_GROUP = 'admin';
    
    var $hasSearch = false;
    var $hasRevisions = false;
    
    /**
     * Creates a new instance.
     * 
     * @param resource $mongo A MongoDB connection.
     */
    public function __construct($mongo) {
        $db       = Config::get(self::KEY_DB);
        $this->mongo = $mongo->$db;
        $this->db = $mongo->$db->users;
    }
    
    /**
     * Get a user by id.
     * 
     * @param string $id The user id.
     * @param bool $idlib If the id library should be used.
     * @param bool $justOne If only one entry should be returned.
     * 
     * @return array The user found as an array.
     */
    protected function get($id, $idlib = true, $justOne = true) {
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
    
    
    // Content management magic.
    public function validate($username, $password, $email, $hideEmail, $group, $lockToIp, $creating = true) {
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
            
            $user = $this->mongo->unimportedUsers->findOne(array(
                'username' => $this->clean($username)
                ));
            
            if (!empty($user)) 
                return 'Username is reserved.  If this is you, try to 
reclaim your account instead.';
        }
        
        $entry = array(
            'username' => $username,
            'password' => $password,
            'email' => $email,
            'status' => self::ACCT_OPEN,
            'hideEmail' => $hideEmail,
            'lockToIP' => (bool) $lockToIp,
            'auths' => array('password'),
            'notes' => array(),
            'certs' => array(),
            'bans' => array()
        );
        
        if ($creating) $entry['group'] = ($group == null ? self::DEFAULT_GROUP : $group);
        if (!$creating && !CheckAcl::can('changeUsername')) unset($entry['username']);
        if (!$creating && !CheckAcl::can('changeAcctStatus')) unset($entry['status']);
        if (!$creating && !CheckAcl::can('editAcl')) unset($entry['group']);
        if (!$creating && $passEmpty) unset($entry['password']);
        
        if (!$creating) {
            unset($entry['auth']);
        }
        
        return $entry;
    }
    
    /**
     * Preliminary checks for adding a certificate.
     * 
     * @param string $userId The user's id.
     * @param string $certKey The key of the user's certificate.
     * 
     * @return mixed True on success, or an error string.
     */
    public function preAdd($userId, $certKey) {
        $user = $this->db->findOne(array('_id' => $this->_toMongoId($userId)));
        if ($user == null) return 'Invalid user id.';
        
        if (!empty($user['certs']) && count($user['certs']) >= 5) return 'You are only allowed 5 certificates.';
        return true;
    }
    
    /**
     * Add a certificate.
     * 
     * @param string $userId The user's id.
     * @param string $certKey The key of the user's certificate.
     */
    public function addCert($userId, $certKey) {
        self::ApcPurge($userId);
        $this->db->update(array('_id' => $this->_toMongoId($userId)), 
            array('$push' => array('certs' => $certKey)));
        return true;
    }
    
    /**
     * Remove a certificate.
     * 
     * @param string $userId The user's id.
     * @param string $certKey The key of the user's certificate.
     */
    public function removeCert($userId, $certKey) {
        self::ApcPurge($userId);
        $this->db->update(array('_id' => $this->_toMongoId($userId)),
            array('$pull' => array('certs' => $certKey)));
        return true;
    }
    
    /**
     * Change a user's authentication
     * 
     * @param string $userId The user's id.
     * @param bool $password True for password auth.
     * @param bool $certificate True for certificate auth.
     * @param bool $certAndPass True for certificate and password auth.
     * @param bool $autoauth True to enable AutoAuth.
     */
    public function changeAuth($userId, $password, $certificate, $certAndPass, $autoauth) {
        self::ApcPurge($userId);
        
        $auths = array();
        if ($password) array_push($auths, 'password');
        if ($certificate) array_push($auths, 'certificate');
        if ($certAndPass) array_push($auths, 'cert+pass');
        if ($autoauth) array_push($auths, 'autoauth');
        
        if (empty($auths)) return 'You need some method of authentication!';
        $this->db->update(array('_id' => $this->_toMongoId($userId)),
            array('$set' => array('auths' => $auths)));
    }
    
    /**
     * Reset a user's password.
     * 
     * @param string $userId The user's id.
     * 
     * @return string The user's new password.
     */
    public function resetPassword($userId) {
        self::ApcPurge($userId);
        $password = hash('crc32', rand());

        $userInfo = $this->db->findOne(array('_id' => $this->_toMongoId($userId)));
        $this->db->update(array('_id' => $this->_toMongoId($userId)),
            array('$set' => array('password' => $this->hash($password, $userInfo['username']))));
        
        return $password;
    }
    
    /**
     * Add a note to a user's profile.
     * 
     * @param string $userId The user's id.
     * @param string $note The note.
     * 
     * @return mixed Null on success, or an error string.
     */
    public function addNote($userId, $note) {
        self::ApcPurge($userId);
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
    
    /**
     * Set a user's status.
     * 
     * @param string $userId The user's id.
     * @param int $status The user's new status.
     */
    public function setStatus($userId, $status) {
        self::ApcPurge($userId);
        $this->db->update(array('_id' => $this->_toMongoId($userId)),
            array('$set' => array('status' => (int) $status)));
    }
    
    /**
     * Set a user's group.
     * 
     * @param string $userId The user's id.
     * @param string $group The user's new group.
     */
    public function setGroup($userId, $group) {
        self::ApcPurge($userId);
        $this->db->update(array('_id' => $this->_toMongoId($userId)),
            array('$set' => array('group' => (string) $group)));
    }
    
    /**
     * Special Ban a user.
     */
    public function banUser($userId, $bans) {
        $user = $this->get($userId, false);
        if (empty($user)) return 'Invalid user id.';
        
        $this->db->update(array('_id' => $this->_toMongoId($userId)),
            array('$set' => array('bans' => $bans)));
        self::ApcPurge($userId);
        
        // We need to permeate an active session!
        $key = 'hts_Session_user_' . $user['username'];
        if (apc_exists($key)) {
            Session::setExternalVars(apc_fetch($key), array('bans' => $bans));
        }
        return $user;
    }
    
    
    /**
     * Authenticate a user.
     * 
     * @param string $username The username to use.
     * @param string $password The password to use.
     * 
     * @return mixed User data on success, or error string.
     */
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
                
                $key = Cache::PREFIX . 'Session_user_' . $good['username'];
                if (apc_exists($key)) {
                    Session::forceLogout($good['username'], apc_fetch($key));
                }
                
                Session::setBatchVars($good);
                return $good;
            }
        }
        
        return 'Invalid username/password';
    }
    
    /**
     * Hash a user's password.
     * 
     * @param string $password The password to use.
     * @param string $username The username to use.
     * 
     * @return string The hashed password.
     */
    public function hash($password, $username) {
        return crypt($password, $username);
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
        if (empty($user['auths'])) return false;
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
