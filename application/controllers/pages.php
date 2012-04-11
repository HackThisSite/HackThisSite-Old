<?php
class controller_pages extends Controller {
    
    public static $cache = array(
        'info' => array('type' => 'v', 'key' => 'pages/info_{REQ}', 'ttl' => 1000)
        );
    
    public function info($arguments) {
        if (empty($arguments[0]))
            return $this->setView('nil');
        
        $top = dirname(dirname(__FILE__)) . '/application/views/';
        $place = '/pages/info_' . basename($arguments[0]) . '.php';

        if (!file_exists($top . Layout::getLayout() . $place) && 
            !file_exists($top . 'main' . $place))
            $this->setView('nil');
        
        $this->setView('pages/info_' . basename($arguments[0]));
    }
    
}
