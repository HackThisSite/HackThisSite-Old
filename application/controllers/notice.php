<?php
class controller_notice extends Content {
    
    var $name = 'notice';
    var $model = 'notices';
    var $db = 'redis';
    var $permission = 'Notice';
    var $createForms = array('text');
    var $location = 'notice';
    var $hasRevisions = false;
    
    public function index() {
        if (!CheckAcl::can('manageNotice')) 
            return Error::set('You are not allowed to manage notices.');
        $notices = new notices(ConnectionFactory::get('redis'));
        $this->view['valid'] = true;
        $this->view['notices'] = $notices->getAll();
    }
    
}
