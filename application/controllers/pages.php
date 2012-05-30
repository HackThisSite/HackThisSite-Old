<?php
class controller_pages extends Controller {
    
    public function info($arguments) {
        if (empty($arguments[0]))
            return $this->setView('nil');
        
        $top = dirname(dirname(__FILE__)) . '/views/';
        $place = '/pages/info/' . basename($arguments[0]) . '.php';
        
        if (!file_exists($top . Layout::getLayout() . $place) && 
            !file_exists($top . 'main' . $place))
            return $this->nil();
        
        $this->setView('pages/info/' . basename($arguments[0]));
    }
    
}
