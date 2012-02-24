<?php
class controller_lecture extends Content {
    
    var $name = 'lecture';
    var $model = 'lectures';
    var $db = 'mongo';
    var $permission = 'Lectures';
    var $createForms = array('title', 'lecturer', 'description', 'date', 'duration');
    var $location = 'lecture';
    
    public function index() {
        $lectures = new lectures(ConnectionFactory::get('mongo'));
        $this->view['lectures'] = $lectures->getNew();
        
        if (is_string($this->view['lectures']))
            return Error::set($this->view['lectures']);
        
        $this->view['valid'] = true;
    }
    
}
