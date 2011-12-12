<?php
class controller_lecture extends Controller {
    
    public function index() {
        $lectures = new lectures(ConnectionFactory::get('mongo'));
        $this->view['lectures'] = $lectures->getNew();
        
        if (is_string($this->view['lectures']))
            return Error::set($this->view['lectures']);
        
        $this->view['valid'] = true;
    }
    
    public function post($arguments) {
        if (!CheckAcl::can('postLectures'))
            return Error::set('You can not post lectures!');
        
        $this->view['valid'] = true;
        if (!empty($arguments[0]) && $arguments[0] == 'save') {
            if (empty($_POST['title']) || empty($_POST['lecturer']) || empty($_POST['description']) ||
                empty($_POST['date']) || empty($_POST['duration']))
                return Error::set('Please fill out all of the required forms.');
            
            $lectures = new lectures(ConnectionFactory::get('mongo'));
            $return = $lectures->add($_POST['title'], $_POST['lecturer'], 
                $_POST['description'], $_POST['date'], $_POST['duration']);
            
            if (is_string($return))
                return Error::set($return);
            
            $this->view['valid'] = false;
            return Error::set('Lecture posted!', true);
        }
    }
    
    public function edit($arguments) {
        if (!CheckAcl::can('editLectures'))
            return Error::set('You can not edit lectures.');
        if (empty($arguments[0]))
            return Error::set('No lecture id found.');
        
        $lectures = new lectures(ConnectionFactory::get('mongo'));
        $entry = $lectures->get($arguments[0], false, false);
        
        if (is_string($entry))
            return Error::set($entry);
        
        $this->view['valid'] = true;
        $this->view['post'] = $entry;
        
        if (!empty($arguments[1]) && $arguments[1] == 'save') {
            if (empty($_POST['title']) || empty($_POST['lecturer']) || empty($_POST['description']) ||
                empty($_POST['date']) || empty($_POST['duration']))
                return Error::set('All forms need to be filled out');
            
            $return = $lectures->edit($arguments[0], $_POST['title'], $_POST['lecturer'], 
                $_POST['description'], $_POST['date'], $_POST['duration']);
            
            if (is_string($return))
                return Error::set($return);
            
            $this->view['post'] = $lectures->get($arguments[0], false, false);
            Error::set('Entry edited!', true);
        }
    }
    
    public function delete($arguments) {
        if (!CheckAcl::can('deleteLectures'))
            return Error::set('You can not delete lectures.');
        if (empty($arguments[0]))
            return Error::set('No lecture id found.');
            
        $lectures = new lectures(ConnectionFactory::get('mongo'));
        $return = $lectures->delete($arguments[0]);

        if (is_string($return))
            return Error::set($return);
        
        Error::set('Lecture deleted', true);
        header('Location: ' . Url::format('lecture'));
    }
}
