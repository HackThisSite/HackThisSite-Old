<?php
class controller_reclaim extends Controller {
    
    public function index() {
        if (Session::isLoggedIn()) return Error::set('You\'re logged in!');
        $this->view['valid'] = true;
    }
    
    public function check() {
        $this->setView('reclaim/index');
        $this->view['valid'] = true;
        
        if (Session::isLoggedIn()) return Error::set('You\'re logged in!');
        if (empty($_POST['username']) || empty($_POST['password'])) 
            return Error::set('All forms are required.');
        
        $reclaims = new reclaims(ConnectionFactory::get('mongo'));
    }
}
