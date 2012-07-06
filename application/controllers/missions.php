<?php
class controller_missions extends Controller {
    
    public function index() {
        if (!Session::isLoggedIn())
            return Error::set('You need to log in!');
        
        $this->view['valid'] = true;
        $missions = new missions(ConnectionFactory::get('mongo'));
        $this->view['missions'] = $missions->getTypes();
    }
    
    /*
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
            
            if (substr($this->view['uri'], -4) == '.php') {
                $uri = substr($this->view['uri'], 0, -4);
                $path = dirname(dirname(__FILE__)) . '/views/main/missions/' . 
                    $type . '/' . intval($arguments[0]) . '/' . 
                    basename($this->view['uri']);
                if (file_exists($path)) {
                    
                }
            }
            
            try {
                if (substr($this->view['uri'], -4) != '.php')
                    throw new Exception();
                
                $uri = substr($this->view['uri'], 0, -4);
                $path = dirname(dirname(__FILE__)) . '/views/main/missions/' . 
                    $type . '/' . intval($arguments[0]) . '/' . basename($this->view['uri']);
                
                if (!file_exists($path))
                    throw new Exception();
                
                $this->setView('missions/' . $type . '/' . 
                    intval($arguments[0]) . '/' . $uri);
                    
            } catch(Exception $e) {
                $this->setView('missions/' . $type . '/' . 
                    intval($arguments[0]) . '/index');
            }
        } else { // Just show a listing of possible missions.
            $this->view['valid'] = true;
            $this->view['missions'] = $missions->getMissionsByType($type);
            $this->setView('missions/base');
        }
    }
    */
    /*
    public function basic($arguments) {
        $this->baseMission('basic', $arguments);
        
        if (empty($arguments[0])) return;
        if (($arguments[0] == '3' && $arguments[1] == 'password.php') ||
        ($arguments[0] == '4' && $arguments[1] == 'level4.php') || 
        ($arguments[0] == '5' && $arguments[1] == 'level5.php'))
            Layout::cut();
    }
    */
    
    public function basic($arguments) {
        $missions = new missions(ConnectionFactory::get('mongo'));

        if (!empty($arguments[0])) { // A specific mission has been requested.
            $mission = $missions->get('basic', intval($arguments[0]));
            if (empty($mission)) return Error::set('Mission does not exist.');
            
            $this->view['valid'] = true;
            $this->view['num'] = $arguments[0];
            $this->view['basic'] = new BasicMissions;
            $this->view['name'] = $mission['name'];
            
            if (isset($_POST['password'])) {
                $good = call_user_func(array($this->view['basic'], 
                    'validateMission' . $this->view['num']), $_POST['password']);
                if ($good) {
                    Error::set('Correct!', true);
                } else {
                    Error::set('Invalid password!');
                }
            }
        } else { // Just show a listing of possible missions.
            $this->view['valid'] = true;
            $this->view['missions'] = $missions->getMissionsByType('basic');
            $this->setView('missions/base');
        }
    }
    
    public function legacy($arguments) {
        Error::set('Not yet added!', true);
    }
}
