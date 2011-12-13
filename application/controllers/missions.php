<?php
class controller_missions extends Controller {
    
    public function index() {
        if (!Session::isLoggedIn())
            return Error::set('You need to log in!');
        
        $this->view['valid'] = true;
        $missions = new missions(ConnectionFactory::get('mongo'));
        $this->view['missions'] = $missions->getTypes();
    }
    
    
    private function baseMission($type, $arguments) {
        $this->setView('missions/index');
        if (!Session::isLoggedIn())
            return Error::set('You need to log in!');
        
        $missions = new missions(ConnectionFactory::get('mongo'));

        if (!empty($arguments[0])) { // A specific mission has been requested.
            $arguments[0] = intval($arguments[0]);
            
            // Determine if mission exists
            $mission = $missions->get($type, $arguments[0]);
            
            if (empty($mission))
                return Error::set('Mission does not exist.');
            
            // Generate a uri.
            $uri = $arguments;
            unset($uri[0], $uri[0]);
            $this->view['uri'] = implode('/', $uri);
            
            $this->view['id'] = $mission['_id'];
            $this->setView('missions/' . $type . '/' . intval($arguments[0]));
        } else { // Just show a listing of possible missions.
            $this->view['valid'] = true;
            $this->view['missions'] = $missions->getMissionsByType($type);
            $this->setView('missions/base');
        }
    }
    
    public function basic($arguments) {
        $this->baseMission('basic', $arguments);
    }
}
