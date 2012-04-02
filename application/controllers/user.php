<?php
class controller_user extends Controller {
    
    public function view($arguments) {
        if (!empty($arguments[0]) && !Session::isLoggedIn()) 
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
    }
    
    public function settings($arguments) {
        if (!Session::isLoggedIn())
            return Error::set('You are not logged in!');
        
        $this->view['valid'] = true;
        $user = new users(ConnectionFactory::get('mongo'));
        $this->view['user'] = $user->get(Session::getVar('username'));
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
    }
    
    public function login() {
        if (empty($_POST['username']) || empty($_POST['password'])) 
            return Error::set('Username and password are required.');
        
        $users = new users(ConnectionFactory::get('mongo'));
        $good = $users->authenticate($_POST['username'], $_POST['password']);
        
        if (!$good)
            return Error::set('Invalid username/password');
        
        header('Location: ' . Config::get('other:baseUrl'));
    }
    
    public function logout() {
        Session::destroy();
        header('Location: ' . Config::get('other:baseUrl'));
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
			header('Location: ' . Config::get('other:baseUrl'));
        }
    }
    
}
