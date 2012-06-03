<?php
class controller_reclaim extends Controller {
    
    public function index() {
        if (Session::isLoggedIn()) return Error::set('You\'re logged in!');
        $this->view['valid'] = true;
        $this->view['publicKey'] = Config::get('recaptcha:publicKey');
    }
    
    public function check() {
        $this->setView('reclaim/index');
        
        if (Session::isLoggedIn()) return Error::set('You\'re logged in!');
        $this->view['valid'] = true;
        $this->view['publicKey'] = Config::get('recaptcha:publicKey');
        
        if (empty($_POST['recaptcha_challenge_field']) || empty($_POST['recaptcha_response_field']))
            return Error::set('We could not find the captcha validation fields!');
        
        $recaptcha = Recaptcha::check($_POST['recaptcha_challenge_field'], $_POST['recaptcha_response_field']);
        
        if (is_string($recaptcha)) return Error::set(Recaptcha::$errors[$recaptcha]);
        if (empty($_POST['username']) || empty($_POST['password'])) 
            return Error::set('All forms are required.');
        
        $reclaims = new reclaims(ConnectionFactory::get('mongo'));
        $good = $reclaims->authenticate($_POST['username'], $_POST['password']);
        
        if (!$good) return Error::set('Invalid username/password.');
        
        $reclaims->import($_POST['username'], $_POST['password']);
        
        $users = new users(ConnectionFactory::get('mongo'));
        $users->authenticate($_POST['username'], $_POST['password']);
        
        header('Location: ' . Url::format('/'));
    }
}
