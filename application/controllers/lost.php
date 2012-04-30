<?php
class controller_lost extends Controller {
    
    const ERR_LOGGED_IN = 'Why are you logged in...?';
    const ERR_NO_USERNAME = 'No username was found.';
    const ERR_INVALID_USERNAME = 'Invalid username.';
    const ERR_EMPTY_AUTHSET = 'You have no means of authentication.  Please report this to a developer.';
    const ERR_NO_LOST_ID = 'No id was found.';
    const ERR_INVALID_MODE = 'Invalid mode.';
    
    public function index() {
        if (Session::isLoggedIn()) return Error::set(self::ERR_LOGGED_IN);
        $this->view['valid'] = true;
    }
    
    public function access() {
        if (Session::isLoggedIn()) return Error::set(self::ERR_LOGGED_IN);
        if (empty($_POST['username'])) return Error::set(self::ERR_NO_USERNAME);
        
        $users = new users(ConnectionFactory::get('mongo'));
        $user = $users->get($_POST['username']);
        
        if ($user == null) return Error::set(self::ERR_INVALID_USERNAME);
        
        $auths = $user['auths'];
        
        if (in_array('password', $auths)) { // Password auth
            $passReset = new passwordReset(ConnectionFactory::get('redis'));
            $id = $passReset->reset($user['_id'], $user['email']);
            
            $this->view['id'] = $id;
            $this->view['mail'] = false;
            if (Config::get('system:mail')) $this->view['mail'] = true;
            
            $this->setView('lost/passwordReset');
        } else if (!in_array('password', $auths)) { // Certificate auths only
            $status = $this->checkCerts($user);
            
            if ($status == false) { // No valid certificates, set auth to password.
                $users->changeAuth($user['_id'], true, false, false, false);
                $this->setView('lost/authSetToPassword');
            } else { // Send email to change user's auth
                $passReset = new passwordReset(ConnectionFactory::get('redis'));
                $id = $passReset->auth($user['_id'], $user['email']);
                
                $this->view['id'] = $id;
                $this->view['mail'] = false;
                if (Config::get('system:mail')) $this->view['mail'] = true;
            
                $this->setView('lost/authReset');
            }
        } else { // Somehow the user got an empty auth set.
            return Error::set(self::ERR_EMPTY_AUTHSET);
        }
    }
    
    public function confirm($arguments) {
        if (Session::isLoggedIn()) return Error::set(self::ERR_LOGGED_IN);
        if (empty($arguments[0])) return Error::set(self::ERR_NO_LOST_ID);
        if (empty($arguments[1]) || ($arguments[1] != 'auth' && $arguments[1] != 'password')) 
            return Error::set(self::ERR_INIVALID_MODE);
        
        $passReset = new passwordReset(ConnectionFactory::get('redis'));
        $info = $passReset->get($arguments[0], ($arguments[1] == 'auth' ? true : false));
        
        if (is_string($info)) return Error::set($info);
        
        $users = new users(ConnectionFactory::get('mongo'));
        
        if ($arguments[1] == 'auth') {
            $users->changeAuth($info[1], true, false, false, false);
            $this->view['password'] = false;
        } else {
            $password = $users->resetPassword($info[1]);
            $this->view['password'] = $password;
        }
    }
    
    // Returns true if the user has valid certificates, false if the 
    // user does not.
    private function checkCerts($user) {
        if (empty($user['certs'])) return false;
        
        $certs = new certs(ConnectionFactory::get('redis'));
        
        foreach ($user['certs'] as $cert) {
            if (time() > $cert['validFrom_time_t'] && time() < $cert['validTo_time_t'])
                return true;
        }
        
        return false;
    }   
}
