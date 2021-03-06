<?php
class controller_bugs extends Content {
    /*
    bugReports
        * title         string
        * reporter      string
        * category      int
        * status        int
        * description   string
        * reproduction  string
        * created       int
        * lastUpdate    int
        * public        bool
        * flagged       bool  
        * ghosted       bool  
    */
    
    var $name = 'bugs';
    var $model = 'bugs';
    var $db = 'mongo';
    var $permission = 'Bugs';
    var $createForms = array('title', 'category', 'description', 'reproduction', '?public');
    var $location = 'bugs';
    
    public function index($arguments) {
        $bugs = new bugs(ConnectionFactory::get('mongo'));
        
        $filter = 'open';
        $page = 1;
        
        if (!empty($arguments[0])) $filter = $arguments[0];
        if (!empty($arguments[1])) $page = $arguments[1];

        $bug = $bugs->getNew($filter, $page);
        if (is_string($bug)) return Error::set($bug);
        
        $this->view = $bug;
        $this->view['page'] = $page;
        $this->view['filter'] = $filter;
        
        Layout::set('title', 'Bugs');
        if (empty($this->view['bugs'])) Error::set('No bugs were found!');
    }
    
    public function view($arguments) {
        if (empty($arguments[0])) return Error::set('No bug ID found.');
        if (!empty($arguments[1])) {
            $page = (int) array_pop($arguments);
            if ($page < 1) {
                $this->view['commentPage'] = 1;
            } else {
                $this->view['commentPage'] = $page;
            }
        } else {
            $this->view['commentPage'] = 1;
        }
        $this->view['commentPageLoc'] = 'bugs/view/' . $arguments[0] . '/';
        
        $bugs = new bugs(ConnectionFactory::get('mongo'));
        $this->view['bug'] = $bugs->get($arguments[0], true, true);
        
        if (is_string($this->view['bug']))
            return Error::set($this->view['bug']);
        if (!bugs::canView($this->view['bug']))
            return Error::set('You are not allowed to view this bug.');
                
        $this->view['valid'] = true;
        Layout::set('title', $this->view['bug']['title']);
        if ($this->view['bug']['flagged'] == true && CheckAcl::can('unflagBug')) 
            $bugs->alter($this->view['bug']['_id'], array('flagged' => false));
    }
    
    public function changeStatus($arguments) {
        if (!CheckAcl::can('editBugStatus'))
            return Error::set('You are not allowed to change bug statuses.');
        if (empty($_POST['id'])) return Error::set('Invalid id.');
        
        $bugs = new bugs(ConnectionFactory::get('mongo'));
        $bug = $bugs->get($_POST['id'], false);
        
        if (empty($bug)) return Error::set('Invalid id.');
        
        $extra = array('public', 'private', 'delete');
        $acceptable = array_merge(bugs::$status, $extra);
        
        if (empty($_POST['status']) || !in_array($_POST['status'], $acceptable))
            return Error::set('Invalid status.');
        
        if (in_array($_POST['status'], $extra)) { // Altering
            switch ($_POST['status']) {
                case 'public':
                    $diff = array('public' => true);
                    break;
                
                case 'private':
                    $diff = array('public' => false);
                    break;
                
                case 'delete':
                    $diff = array('ghosted' => true);
                    break;
                
                default:
                    $diff = array();
                    break;
            }
        } else { // Standard status change.
            $diff = array('status' => array_search($_POST['status'], bugs::$status));
        }
        
        $bugs->alter($_POST['id'], $diff);
        $this->view['valid'] = true;
        Error::set('Status changed.', true);
        
        apc_delete('bugs_' . Id::create(current($bug), 'bugs'));
    }
    
}
