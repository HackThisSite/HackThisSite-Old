<?php
class controller_notice extends Controller {
    
    public function index() {
        if (!CheckAcl::can('manageNotice')) 
            return Error::set('You are not allowed to manage notices.');
        $notices = new notices(ConnectionFactory::get('redis'));
        $this->view['valid'] = true;
        $this->view['notices'] = $notices->getAll();
        Layout::set('title', 'Manage Notices');
    }
    
    public function post() {
        if (!CheckAcl::can('postNotices'))
            return Error::set('You are not allowed to post notices!');
        $this->view['valid'] = true;
        if (empty($_POST['text'])) return Error::set('No text found.');
        
        $notices = new notices(ConnectionFactory::get('redis'));
        $return = $notices->create($_POST['text']);
        if (is_string($return)) return Error::set($return);
        
        $this->view['valid'] = false;
        header('Location: ' . Url::format('/notice/'));
    }
    
    public function edit($arguments) {
        if (!CheckAcl::can('editNotices'))
            return Error::set('You are not allowed to edit notices!');
        if (empty($arguments[0]))
            return Error::set('No notice id was found!');
        
        $notices = new notices(ConnectionFactory::get('redis'));
        $entry = $notices->get($arguments[0]);
        
        $this->view['id'] = $arguments[0];
        $this->view['post'] = $entry;
        
        if (empty($entry)) return Error::set('Invalid id.');
        if (is_string($entry)) return Error::set($entry); 
        
        $this->view['valid'] = true;
        
        if (!empty($arguments[1]) && $arguments[1] == 'save') {
            if (empty($_POST['text'])) return Error::set('No text found.');
            $return = $notices->edit($arguments[0], $_POST['text']);
            if (is_string($return)) return Error::set($return);
            
            $this->view['valid'] = false;
            header('Location: ' . Url::format('/notice/'));
        }
    }
    
    public function delete($arguments) {
        if (!CheckAcl::can('deleteNotices'))
            return Error::set('You are not allowed to delete notices!');
        if (empty($arguments[0]))
            return Error::set('No notice id was found!');
        
        $notices = new notices(ConnectionFactory::get('redis'));
        $return = $notices->delete($arguments[0]);
        if (is_string($return)) return Error::set($return);
        
        header('Location: ' . Url::format('/notice/'));
    }
    
}
