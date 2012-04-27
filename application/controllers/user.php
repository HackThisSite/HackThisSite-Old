<?php
class controller_user extends Controller {
    
    public function view($arguments) {
        if (empty($arguments[0])) 
            return Error::set('Username is required.');
        
        if (empty($arguments[0])) {
            $username = Session::getVar('username');
        } else {
            $username = $arguments[0];
        }
        
        $users = new users(ConnectionFactory::get('mongo'));
        $userInfo = $users->get($username);
        
        if (empty($userInfo))
            return Error::set('User not found.');
        
        $this->view['valid'] = true;
        $this->view['username'] = $username;
        $this->view['user'] = $userInfo;
        
        $irc = new irc(ConnectionFactory::get('redis'));
        $this->view['onIrc'] = $irc->isOnline($username);
        $this->view['onSite'] = apc_exists('user_' . $username);
    }
    
    public function settings($arguments) {
        if (!Session::isLoggedIn())
            return Error::set('You are not logged in!');
        
        $this->view['valid'] = true;
        $user = new users(ConnectionFactory::get('mongo'));
        $this->view['user'] = $user->get(Session::getVar('username'));
		$this->view['secure'] = (!empty($_SERVER['SSL_CLIENT_RAW_CERT']) ? true : false);
		if ($this->view['secure']) $this->view['clientSSLKey'] = certs::getKey($_SERVER['SSL_CLIENT_RAW_CERT']);
		
        if (!empty($arguments[0]) && $arguments[0] == 'save') {
			if (!empty($_POST['oldpassword']) && !empty($_POST['password'])) {
				$old = $user->hash($_POST['oldpassword'], $this->view['user']['username']);
				
				if ($old != $this->view['user']['password'])
					return Error::set('Previous password is invalid.');
			}
			
			$params = array(
				Session::getVar('_id'),
				(!empty($_POST['username']) ? $_POST['username'] : null),
				(!empty($_POST['password']) ? $_POST['password'] : null),
				(!empty($_POST['email']) ? $_POST['email'] : null),
				(!empty($_POST['hideEmail']) ? true : false),
				(!empty($_POST['group']) ? $_POST['group'] : null)
			);
			
			$error = call_user_func_array(array($user, 'edit'), $params);
            if (is_string($error)) return Error::set($error);
            
            $this->view['user'] = $user->get(Session::getVar('username'));
            Error::set('User profile saved.', true);
        }
        
		if (!empty($arguments[0]) && $arguments[0] == 'saveAuth') {
			$password = (!empty($_POST['passwordAuth']) ? true : false);
			$certificate = (!empty($_POST['certificateAuth']) ? true : false);
			$certAndPass = (!empty($_POST['certAndPassAuth']) ? true : false);
			$autoauth = (!empty($_POST['autoAuth']) ? true : false);
			
			$return = $user->changeAuth(Session::getVar('_id'), $password, 
				$certificate, $certAndPass, $autoauth);
			
			if (is_string($return)) return Error::set($return);
			$this->view['user'] = $user->get(Session::getVar('username'));
		}
    }
    
    public function rmCert() {
        if (!Session::isLoggedIn())
            return Error::set('You are not logged in!');
        if (empty($_POST['hash']))
			return Error::set('No certificate hash was found.');
		
		$certs = new certs(ConnectionFactory::get('redis'));
		$cert = $certs->get($_POST['hash'], false);
		
		if ($cert == null)
			return Error::set('Invalid certificate hash.');
		
		if (substr($cert, 0, strpos($cert, ':')) != Session::getVar('_id'))
			return Error::set('You are not allowed to remove this certificate.');
		
		$users = new users(ConnectionFactory::get('mongo'));
		$users->removeCert(Session::getVar('_id'), $_POST['hash']);
		$certs->removeCert($_POST['hash']);
		
		header('Location: ' . Url::format('/user/settings'));
	}
	
	public function viewCert() {
        if (!Session::isLoggedIn())
            return Error::set('You are not logged in!');
        if (empty($_POST['hash']))
			return Error::set('No certificate hash was found.');
		
		$certs = new certs(ConnectionFactory::get('redis'));
		$cert = $certs->get($_POST['hash'], false);
		
		if ($cert == null)
			return Error::set('Invalid certificate hash.');
		
		if (substr($cert, 0, strpos($cert, ':')) != Session::getVar('_id'))
			return Error::set('You are not allowed to view this certificate.');
		
		$this->view['cert'] = substr($cert, strpos($cert, ':') + 1);
	}
    
    public function login() {
		$username = (empty($_POST['username']) ? null : $_POST['username']);
		$password = (empty($_POST['password']) ? null : $_POST['password']);
        
        $users = new users(ConnectionFactory::get('mongo'));
        $good = $users->authenticate($_POST['username'], $_POST['password']);
        
        if (!$good)
            return Error::set('Invalid username/password');
        
        header('Location: ' . Url::format('/'));
    }
    
    public function logout() {
        Session::destroy();
        header('Location: ' . Url::format('/'));
    }
    
    public function register($arguments) {
        if (Session::isLoggedIn())
            return Error::set('You can\'t register if you\'re logged in!');
        
        $this->view['valid'] = true;
        
        if (!empty($arguments) && $arguments[0] == 'save') {
            if (empty($_POST['username']) || empty($_POST['password']) || 
                empty($_POST['email']))
                return Error::set('All forms are required.');
			
			$users = new users(ConnectionFactory::get('mongo'));
			$hideEmail = (empty($_POST['hideEmail']) ? false : true);
			$created = $users->create($_POST['username'], $_POST['password'], 
				$_POST['email'], $hideEmail, null);
			if (is_string($created)) return Error::set($created);
			
			$users->authenticate($_POST['username'], $_POST['password']);
			header('Location: ' . Url::format('/'));
        }
    }
    
    public function addkey($arguments) {
        if (!Session::isLoggedIn())
            return Error::set('Please login to add keys.');
		if (empty($_POST['csr']))
			return Error::set('No CSR found.');
		
		$certs = new certs(ConnectionFactory::get('redis'));
		
		$cert = openssl_csr_sign($_POST['csr'], Config::get('ssl:certificate'), 
			Config::get('ssl:key'), 365, Config::get('sslConf'), $certs->getSerial());
		$return = openssl_x509_export($cert, $output);

		if (!$return)
			return Error::set('Invalid CSR.');
		
		$users = new users(ConnectionFactory::get('mongo'));
		
		$check = $certs->preAdd($output);
		if (is_string($check))
			return Error::set($check);
		
		$check = $users->preAdd(Session::getVar('_id'), $certs->getKey($output));
		if (is_string($check))
			return Error::set($check);
		
		$certs->add($output);
		$users->addCert(Session::getVar('_id'), $certs->getKey($output));
		
		$this->view['valid'] = true;
		$this->view['certificate'] = $output;
	}
	
	public function link($arguments) {
		if (!Session::isLoggedIn()) return Error::set('Please login.');
		
		$irc = new irc(ConnectionFactory::get('redis'));
		$username = Session::getVar('username');
		
		$this->view['valid'] = true;
		$this->view['pending'] = $nicks = $irc->getPending($username);
		$this->view['nicks'] = $goodNicks = $irc->getNicks($username);
		
		if (!empty($arguments[0]) && $arguments[0] == 'add') {
			if (!isset($nicks[$arguments[1]])) return Error::set('Invalid nick id.');
			
			$irc->addNick($username, $nicks[$arguments[1]]);
			$this->view['pending'] = $irc->getPending($username);
			$this->view['nicks'] = $irc->getNicks($username);
		} else if (!empty($arguments[0]) && $arguments[0] == 'delP') {
			if (!isset($nicks[$arguments[1]])) return Error::set('Invalid nick id.');
			
			$irc->delNick($username, $nicks[$arguments[1]]);
			$this->view['pending'] = $irc->getPending($username);
		} else if (!empty($arguments[0]) && $arguments[0] == 'delA') {
			if (!isset($goodNicks[$arguments[1]])) return Error::set('Invalid nick id.');
			
			$irc->delAcceptedNick($username, $goodNicks[$arguments[1]]);
			$this->view['nicks'] = $irc->getPending($username);
		}
	}
    
}
