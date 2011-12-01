<?php
class controller_user extends Controller {
    
    public function view($arguments) {
        if (!empty($arguments[0]) && !Session::isLoggedIn()) 
            return Error::set('Username is required.');
        
        if (empty($arguments[0])):
            $username = Session::getVar('username');
        else:
            $username = $arguments[0];
        endif;
        
        $users = new users(ConnectionFactory::get('mongo'));
        $userInfo = $users->getUserByUsername($username);
        $this->view['username'] = $username;
        if ($userInfo == null)
            return Error::set('User not found.');
        
        $this->view['valid'] = true;
        $this->view['user'] = $userInfo;
    }
    
    public function settings($arguments) {
        if (!Session::isLoggedIn())
            return Error::set('You are not logged in!');
        
        $this->view['valid'] = true;
        $user = new users(ConnectionFactory::get('mongo'));
        
        if (!empty($arguments[0]) && $arguments[0] == 'save') {
            if (empty($_POST['email']))
                Error::set('Email required.');

            $user->update(Session::getVar('username'), array('email' => trim($_POST['email'])));
            $this->view['user'] = $user->getUserByUsername(Session::getVar('username'));
            Error::set('User profile saved.', true);
        } else {
            $this->view['user'] = $user->getUserByUsername(Session::getVar('username'));
        }
    }
    
    public function login() {
        if (empty($_POST['username']) || empty($_POST['password'])) 
            return Error::set('Username and password are required.');
        
        $users = new users(ConnectionFactory::get('mongo'));
        
        if (!$data = $users->authenticate($_POST['username'], $_POST['password']))
            return Error::set($users->getError());
            
        Session::init();
        Session::setBatchVars($data);
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
            if (empty($_POST['username']) || empty($_POST['password']) || empty($_POST['email']))
                return Error::set('All forms are required.');
            
            $users = new users(ConnectionFactory::get('mongo'));
            $return = $users->create($_POST['username'], $_POST['password'], $_POST['email']);
            
            if ($return) {
                $this->view['valid'] = false;
                Error::set('You have been registered!', true);
                
                // Log user in
                $data = $users->getUserByUsername($_POST['username']);
                Session::init();
                Session::setBatchVars($data);
            } else {
                Error::set($users->getError());
            }
        }
    }
    
}
