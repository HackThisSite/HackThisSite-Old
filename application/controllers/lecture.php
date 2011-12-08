<?php
class controller_lecture extends Controller {
    
    public function index() {
        $lectures = new lectures(ConnectionFactory::get('mongo'));
        $this->view['lectures'] = $lectures->getNew();
        
        if (is_string($this->view['lectures']))
            return Error::set($this->view['lectures']);
        
        $this->view['valid'] = true;
    }
    
    public function post($arguments[0]) {
        if (!CheckAcl::can('postLectures'))
            return Error::set('You can not post lectures!');
    }
}
