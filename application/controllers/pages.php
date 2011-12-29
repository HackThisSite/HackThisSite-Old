<?php
class controller_pages extends Controller {
    
    public function info($arguments) {
        if (empty($arguments[0]))
            return $this->setView('nil');
        
        $top = $GLOBALS['maind'] . '/application/views/';
        $place = '/pages/info_' . basename($arguments[0]) . '.php';

        if (!file_exists($top . Layout::getLayout() . $place) && 
            !file_exists($top . 'main' . $place))
            $this->setView('nil');
        
        $this->setView('pages/info_' . basename($arguments[0]));
    }
    
}
