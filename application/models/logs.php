<?php
class logs extends mongoBase {
    
    private $mongo;
    private $redis;
    
    public function __construct($mongo, $redis) {
        $this->mongo = $mongo->{Config::get('mongo:db')};
        $this->redis = $redis;
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
    
    public function activity($message, $reference) {
        if (($id = Session::getVar('_id')) == false)
            $id = null;
        
        $entry = array(
            'userId' => $id,
            'message' => $this->clean($message),
            'reference' => $this->clean($reference),
            'time' => time()
        );
        
        $this->mongo->activity->insert($entry);
    }
    
    public function general() {
        $start = microtime(true);
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
            'request' => $this->clean(Log::$request),
            'arguments' => $arguments,
            'input' => array(
                'GET' => $get,
                'POST' => $post
                ),
            'time' => microtime(true),
            'loadTime' => (microtime(true) - Log::$start)
        );

        $this->mongo->logs->insert($entry);
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
