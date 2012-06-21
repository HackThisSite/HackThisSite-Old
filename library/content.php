<?php
class Content extends Controller {
    
    private function normalize() {
        $return = array();
        
        foreach ($this->createForms as $field) {
            $skippable = false;
            $medium = $_POST;
            $keyfield = $field;
            
            if ($field[0] == '?') {
                $keyfield = $field = substr($field, 1);
                $skippable = true;
            } else if ($field[0] == '@') {
                $keyfield = $field = substr($field, 1);
            }
            
            if (empty($medium[$keyfield])) {
                $medium[$keyfield] = false;
            }
            
            $return[$field] = $medium[$keyfield];
        }

        return $return;
    }
    
    private function pluralize($name) {
        $last = $name[strlen($name) - 1];
        
        if ($last != 's')
            $name .= 's';
        
        return $name;
    }
    
    private function handle($method) {
        $this->view['valid'] = true;
        $this->view['method'] = $method;
        
        if (empty($_POST['post']) && empty($_POST['preview'])) {
            return false;
        }
        
        // Error checks.
        $errors = false;
        if (!empty($_POST['post']) || !empty($_POST['preview'])) {
            $forms = $this->normalize();
            array_push($forms, true, true);
            
            $model = new $this->model(ConnectionFactory::get($this->db));
            $info = call_user_func_array(array($model, 'validate'), $forms);
            
            if (is_string($info)) {
                Error::set($info);
                $errors = true;
            } else {
                $info['preview'] = true;
                $model->resolveUTF8($info);
                $model->resolveUser($info['user']);
            }
        }
        
        // Display form with old data.
        if ($errors || (!$errors && !empty($_POST['preview']))) {
            $this->view['post'] = $forms;
            $this->setView($this->name . '/edit');
        }
        
        if (!$errors && !empty($_POST['preview'])) {
            $this->view['preview'] = true;
            $this->view['info'] = $info;
        } else if (!$errors && !empty($_POST['post'])) {
            $this->view['valid'] = false;
            $this->view['info'] = $info;
            return array(&$model, $info, array_slice($forms, 0, -2));
        }
        
        return false;
    }
    
    public function post($arguments) {
        Layout::set('title', 'Post ' . ucwords($this->name));
        if (!CheckAcl::can('post' . $this->permission))
            return Error::set('You are not allowed to post ' . $this->pluralize($this->name) . '!');
        
        $return = $this->handle('post');
        if ($return == false) return;
        
        list($model, $info, $forms) = $return;
        call_user_func_array(array($model, 'create'), $forms);
        
        if ($this->name != 'article') {
            Error::set(ucwords($this->name) . ' posted!', true,
                array('View' => Url::format('/' . $this->name . '/view/' . Id::create($info, $this->name)))
            );
        } else {
            Error::set(ucwords($this->name) . ' posted!', true);
        }
        Log::activity('Created:  ' . $this->name, 
            '/' . substr(get_called_class(), 11) . '/view/' . Id::create($info, $this->name));
    }
    
    public function edit($arguments) {
        Layout::set('title', 'Edit ' . ucwords($this->name));
        
        // START CHECKS
        if (empty($arguments[0]))
            return Error::set('No ' . $this->name . ' id was found!');
        
        $model = new $this->model(ConnectionFactory::get($this->db));
        $entry = $this->view['post'] = $model->get($arguments[0], false, true);
        $this->view['_id'] = $entry['_id'];
        
        if (empty($entry)) return Error::set('Invalid id.');
        if (is_string($entry)) return Error::set($entry); 
        if (!method_exists($model, 'authChange') && !CheckAcl::can('edit' . $this->permission))
            return Error::set('You are not allowed to edit ' . $this->pluralize($this->name) . '!');
        if (method_exists($model, 'authChange') && !$model->authChange('edit', $entry))
            return Error::set('You are not allowed to edit this ' . $this->name . '!');
        // END CHECKS

        
        $return = $this->handle('edit');
        if ($return == false) return;
        
        
        // START AFTERWARDS
        list($model, $info, $forms) = $return;
        array_unshift($forms, $this->view['_id']);
        call_user_func_array(array($model, 'edit'), $forms);
        Error::set('Entry edited!', true, 
            array('View' => Url::format('/' . $this->name . '/view/' . Id::create($entry, $this->name)))
        );
        Log::activity('Edited:  ' . $this->name,
            '/' . substr(get_called_class(), 11) . '/view/' . Id::create($entry, $this->name));
    }
    
    public function delete($arguments) {
        $model = new $this->model(ConnectionFactory::get($this->db));
        
        if (empty($arguments[0]))
            return Error::set('No ' . $this->name . ' id was found!');
        if (!method_exists($model, 'authChange') && !CheckAcl::can('delete' . $this->permission))
            return Error::set('You are not allowed to delete ' . $this->pluralize($this->name) . '!');
        
        if (method_exists($model, 'authChange')) {
            $entry = $model->get($arguments[0], false, true);
            
            if (!(method_exists($model, 'authChange') && $model->authChange('delete', $entry)))
                return Error::set('You are not allowed to delete this ' . $this->name . '!');
        }
        
        $return = call_user_func_array(array($model, 'delete'), array($arguments[0]));
        
        if (is_string($return))
            return Error::set($return);
        
        Error::set(ucwords($this->name) . ' deleted!', true);
        if (!isset($this->dnr) || (isset($this->dnr) && !$this->dnr))
            header('Location: ' . Url::format($this->location));
            
        Log::activity('Deleted:  ' . $this->name . ' (' . $arguments[0] . ')', null);
    }
    
    public function revisions($arguments) {
        if (!$this->hasRevisions) 
            return Error::set('Revisions are not enabled for ' . $this->name . '.');
        if (!CheckAcl::can('view' . $this->permission . 'Revisions'))
            return Error::set('You are not allowed to view ' . $this->name . ' revisions.');
        if (empty($arguments[0])) return Error::set('No ' . $this->name . ' id found.');
        
        $model = new $this->model(ConnectionFactory::get($this->db));
        $current = $model->get($arguments[0], false, true);
        $this->view['current'] = $current;
        
        if (empty($current))
            return Error::set('Invalid id.');
        if (is_string($current))
            return Error::set($current);
        
        Layout::set('title', ucwords($this->name) . ' Revisions');
        
        $revisions = new revisions(ConnectionFactory::get('mongo'));
        
        // Start excerpt soley for reverting
        $revert = $this->revert($arguments, $model, $revisions, $current);
        // End excerpt
        
        $revisions = $revisions->getForId($arguments[0]);
        $this->view['revisions'] = array();
        
        if (empty($revisions))
            return Error::set('This entry has no revisions.');
        
        $this->view['revisions'] = revisions::resolve($current, $revisions, $this->diffdFields);
            
    }
    
    private function revert(&$arguments, &$model, &$revisions, &$current) {
        if (!empty($arguments[1]) && $arguments[1] == 'revert' && !empty($arguments[2])) {
            $revision = $revisions->getById($arguments[2], $current, $this->diffdFields);
            if (is_string($revision)) return Error::set($revision);

            unset($revision['_id'], $revision['contentId']);
            array_unshift($revision, $current['_id']);
            $return = call_user_func_array(array($model, 'edit'), $revision);
            
            if (is_string($return)) { return Error::set($return);}
            
            $current = $model->get($arguments[0], false, true);
            $this->view['current'] = $current;
            
            Log::activity('Reverted:  ' . $this->name,
                '/' . substr(get_called_class(), 11) . '/view/' . Id::create($current, $this->name));
        }
        return true;
    }
}
