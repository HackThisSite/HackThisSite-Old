<?php
class Content extends Controller {
    
    private function validatePost($arguments) {
        $return = array();
        $skippable = false;
        $argPos = 0;
        
        foreach ($this->createForms as $field) {
            $medium = $_POST;
            $keyfield = $field;
            
            if ($field[0] == '?') {
                $keyfield = $field = substr($field, 1);
                $skippable = true;
            } else if ($field[0] == '@') {
                $keyfield = $field = substr($field, 1);
                $medium = $arguments;
                $keyfield = $argPos;
                $argPos++;
            }
            
            if (empty($medium[$keyfield]) && (isset($medium[$keyfield]) && $medium[$keyfield] != 0) && !$skippable) {
                $return = false;
                break;
            } else if (empty($medium[$keyfield]) && !isset($medium[$keyfield]) && $skippable) {
                $medium[$keyfield] = false;
            }
            
            $return[$field] = $medium[$keyfield];
            $skippable = false;
        }

        return $return;
    }
    
    private function pluralize($name) {
        $last = $name[strlen($name) - 1];
        
        if ($last != 's')
            $name .= 's';
        
        return $name;
    }
    
    public function post($arguments) {
        if (!CheckAcl::can('post' . $this->permission))
            return Error::set('You are not allowed to post ' . $this->pluralize($this->name) . '!');

        $this->view['valid'] = true;
        Layout::set('title', 'Post ' . ucwords($this->name));
        
        if (!empty($arguments[0]) && $arguments[0] == 'save') {
            if (($forms = $this->validatePost($arguments)) == false)
                return Error::set('All forms need to be filled out.');
            
            $model = new $this->model(ConnectionFactory::get($this->db));
            $info = call_user_func_array(array($model, 'create'), $forms);

            if (is_string($info))
                return Error::set($info);
            
            $this->view['valid'] = false;
            Error::set(ucwords($this->name) . ' posted!', true);
            Log::write(LOG_INFO, 'Posted a ' . $this->name);
        } else {
            Log::write(LOG_INFO, 'Composing a ' . $this->name);
        }
    }
    
    public function edit($arguments) {
        $model = new $this->model(ConnectionFactory::get($this->db));
        
        if (empty($arguments[0]))
            return Error::set('No ' . $this->name . ' id was found!');
        if (!method_exists($model, 'authChange') && !CheckAcl::can('edit' . $this->permission))
            return Error::set('You are not allowed to edit ' . $this->pluralize($this->name) . '!');
        
        $entry = $model->get($arguments[0], false, true);
        
        if (empty($entry))
            return Error::set('Invalid id.');
        if (is_string($entry))
            return Error::set($entry);      
        if (method_exists($model, 'authChange') && !$model->authChange('edit', $entry))
            return Error::set('You are not allowed to edit this ' . $this->name . '!');
        
        $this->view['valid'] = true;
        $this->view['post'] = $entry;
        Layout::set('title', 'Edit ' . ucwords($this->name));
        
        if (!empty($arguments[1]) && $arguments[1] == 'save') {
            if (($forms = $this->validatePost($arguments)) == false)
                return Error::set('All forms need to be filled out.');

            $this->view['forms'] = $forms;
            array_unshift($forms, $arguments[0]);
            $return = call_user_func_array(array($model, 'edit'), $forms);
            
            if (is_string($return))
                return Error::set($return);
            
            $this->view['post'] = $model->get($arguments[0], false, true);
            Error::set('Entry edited!', true);
            Log::write(LOG_INFO, 'Successfully edited ' . $this->name . ' ' . $arguments[0]);
        } else {
            Log::write(LOG_INFO, 'Editing ' . $this->name . ' ' . $arguments[0]);
        }
    }
    
    public function delete($arguments) {
        $model = new $this->model(ConnectionFactory::get($this->db));
        
        if (empty($arguments[0]))
            return Error::set('No ' . $this->name . ' id was found!');
        if (!method_exists($model, 'authChange') && !CheckAcl::can('delete' . $this->permission))
            return Error::set('You are not allowed to delete ' . $this->pluralize($this->name) . '!');
        
        Log::write(LOG_INFO, 'Attempting to delete ' . $this->name . ' ' . $arguments[0]);
        
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
            
        Log::write(LOG_INFO, 'Successfully deleted ' . $this->name . ' ' . $arguments[0]);
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
        }
        return true;
    }
}
