<?php
class logs extends mongoBase {
    
    private $redis;
    
    private $error = array();
    private $activity = array();
    private $general = array();
    
    public function __construct($redis, $mongo = null) {
        $this->redis = $redis;
        $this->mongo = $mongo;
        
        if ($this->mongo != null) 
            $this->mongo = $this->mongo->{Config::get('mongo:db')};
    }
    
    public function __destruct() {
        if (Config::get('redis:array')) {
            $host = $this->redis->_target('{log}');
            $multi = $this->redis->multi($host);
        } else {
            $multi = $this->redis->multi();
        }
        
        foreach ($this->error as $errorLog) {
            $multi->publish('{log}log_error', $errorLog);
        }
        
        foreach ($this->activity as $activityLog) {
            $multi->publish('{log}log_activity', $activityLog);
        }
        
        foreach ($this->general as $generalLog) {
            $multi->publish('{log}log_general', $generalLog);
        }
        
        $multi->exec();
    }
    
    public function login($userId) {
        $key = 'logins_' . $userId;
        $entry = array(
            'ipAddress' => $_SERVER['REMOTE_ADDR'],
            'userAgent' => $this->clean($this->findBrowser()),
            'time' => time()
        );
        $this->redis->lPush($key, serialize($entry));
        $this->redis->lTrim($key, 0, 4);
    }
    
    public function error($message) {
        array_push($this->error, $message);
    }
    
    public function activity($message, $reference) {
        if (($id = Session::getVar('_id')) == false)
            $id = null;
        
        $entry = array(
            'userId' => $id,
            'message' => $this->clean($message),
            'reference' => $this->clean($reference),
            'time' => time()
        );
        
        array_push($this->activity, serialize($entry));
    }
    
    public function general() {
        if (($id = Session::getVar('_id')) == false)
            $id = null;
        
        $get = array_map(array($this, 'clean'), $_GET);
        $post = array_map(array($this, 'clean'), $_POST);
        $arguments = array_map(array($this, 'clean'), Log::$arguments);
        
        $entry = array(
            'userId' => $id,
            'ids' => array(
                'ipAddress' => $_SERVER['REMOTE_ADDR'], 
                'sid' => session_id()
                ),
            'uri' => $this->clean(Log::$uri),
            'request' => $this->clean(Log::$request),
            'arguments' => $arguments,
            'input' => array(
                'GET' => $get,
                'POST' => $post
                ),
            'time' => microtime(true),
            'loadTime' => (microtime(true) - Log::$start)
        );

        array_push($this->general, serialize($entry));
    }
    
    protected function getLogins($userId) {
        $key = 'logins_' . $userId;
        $data = $this->redis->lRange($key, 0, 4);
        
        foreach ($data as $key => $nil) {
            $data[$key] = unserialize($data[$key]);
        }
        
        return $data;
    }
    
    protected function getActivity($userId) {
        return iterator_to_array($this->mongo->activity
            ->find(array('userId' => $this->_toMongoId($userId)))
            ->sort(array('time' => -1))
            ->limit(20));
    }
    
    private function findBrowser() {
        if (empty($_SERVER['HTTP_USER_AGENT'])) return 'Unknown';
        
        $data = get_browser($_SERVER['HTTP_USER_AGENT'], true);
        
        if (empty($data['parent'])) return 'Unknown';
        if (empty($data['platform'])) return 'Unknown';
        
        return $data['parent'] . ' on ' . $data['platform'];
    }
    
}
