<?php
class controller_platform extends Controller {
    
    /***** START SSO *****/
    public function sso() {
        Layout::cut();
        
        if (empty($_GET['client_id'])) 
            return $this->error('request', 'The client_id parameter is missing.');
        
        $clients = Config::get('sso:clients');
        if (empty($clients[$_GET['client_id']]))
            return $this->error('cilent', 'Unknown client {' . $_GET['client_id'] . '}.');
        if (empty($_GET['timestamp']) && empty($_GET['signature']))
            return $this->info('public');
        if (empty($_GET['timestamp']) || !is_numeric($_GET['timestamp']))
            return $this->error('request', 'The timestamp parameter is missing or invalid.');
        if (empty($_GET['signature']))
            return $this->error('request', 'Missing  signature parameter.');
        
        $secure = Config::get('sso:secure');
        $signature = $this->hash($_GET['timestamp'] . $clients[$_GET['client_id']], $secure);
        if ($signature != $_GET['signature'])
            return $this->error('access_denied', 'Signature invalid.', false);
        
        $this->info('full');
        if ($secure != null && $this->view['data']['name'] != '') {
            $this->view['data'] = array_change_key_case($this->view['data']);
            ksort($this->view['data']);
            
            foreach ($this->view['data'] as $k => $v) {
                if ($v == null) $this->view['data'][$k] = '';
            }
            
            $string = http_build_query($this->view['data'], null, '&');
            $signature = $this->hash($string . $clients[$_GET['client_id']], $secure);
            $this->view['data']['client_id'] = $_GET['client_id'];
            $this->view['data']['signature'] = $signature;
        }
        
        return true;
    }
    
    private function info($level) {
        $default = array('name' => '', 'photourl' => '');
        if (!Session::isLoggedIn()) {
            $this->view['data'] = $default;
            goto infoReturn;
        }
        
        $photoUrl = 'https://secure.gravatar.com/avatar/' . md5(strtolower(trim(Session::getVar('email')))) . '?d=identicon&r=pg';
        
        if ($level == 'public') {
            $this->view['data'] = array('name' => Session::getVar('username'),
                'photourl' => $photoUrl);
        } elseif ($level == 'full') {
            $this->view['data'] = array('uniqueid' => (string) Session::getVar('_id'),
                'name' => Session::getVar('username'), 'email' => Session::getVar('email'),
                'photourl' => $photoUrl);
        } else {
            $this->view['data'] = $default;
        }
            
        infoReturn:
        return true;
    }
    
    private function error($where, $message, $prefix = true) {
        $this->view['data'] = array('error' => ($prefix ? 'invalid_' : '') . $where, 
            'message' => $message);
        
        return false;
    }
    
    private function hash($string, $secure) {
        switch ($secure) {
            case 'md5':
            case TRUE:
            case FALSE:
                return md5($string);
                break;
            
            case 'sha1':
                return sha1($string);
                break;
            
            default:
                return hash($secure, $string).$secure;
        }
    }
    /***** END SSO *****/
    
    /***** START API *****/
    public function api($arguments) {
        Layout::cut();
        $GLOBALS['api'] = true;
        $this->view['data'] = array();
        
        if (empty($_SERVER['SSL_CLIENT_RAW_CERT']) || !in_array(md5($_SERVER['SSL_CLIENT_RAW_CERT']), Config::get('api:clients')))
            return Error::set('You are not allowed to use the API.');
        if (count($arguments) != 2) 
            return Error::set('Too many or too few arguments.');
        if (!in_array($arguments[0] . '/' . $arguments[1], Config::get('api:whitelist')))
            return Error::set('Invalid reference.');
        
        $class = new $arguments[0](ConnectionFactory::get('mongo'));
        
        $params = (empty($_POST['params']) ? array() : json_decode(base64_decode($_POST['params']), true));
        $this->view['data'] = call_user_func_array(array($class, $arguments[1]),
            $params);
        
        Log::error('API call made ' . $arguments[0] . '::' . $arguments[1] .
            ' with parameters ' . json_encode($params));
    }
    
}
