<?php
class Content extends Controller {
	
	private function validatePost($arguments) {
		$return = array();
		$skippable = false;
		$argPos = 1;
		
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
			
			if (empty($medium[$keyfield]) && !$skippable) {
				$return = false;
				break;
			} else if (empty($medium[$keyfield]) && $skippable) {
				$medium[$field] = false;
			}
			
			$return[$field] = $medium[$keyfield];
			$skippable = false;
		}

		return $return;
	}
	
	public function pluralize($name) {
		$last = $name[strlen($name) - 1];
		
		if ($last != 's')
			$name .= 's';
		
		return $name;
	}
	
	public function post($arguments) {
		if (!CheckAcl::can('post' . $this->permission))
			return Error::set('You are not allowed to post ' . $this->pluralize($this->name) . '!');

		$this->view['valid'] = true;
		
		if (!empty($arguments[0]) && $arguments[0] == 'save') {
			if (($forms = $this->validatePost($arguments)) == false)
				return Error::set('All forms need to be filled out.');
			
			$model = new $this->model(ConnectionFactory::get($this->db));
			$info = call_user_func_array(array($model, 'create'), $forms);

			if (is_string($info))
				return Error::set($info);
			
			$this->view['valid'] = false;
			Error::set(ucwords($this->name) . ' posted!', true);
			
			if (in_array('post', $this->redirect)) die(Url::format($this->location));
		}
	}
	
	public function edit($arguments) {
		if (!CheckAcl::can('edit' . $this->permission))
			return Error::set('You are not allowed to edit ' . $this->pluralize($this->name) . '!');
		if (empty($arguments[0]))
			return Error::set('No ' . $this->name . ' id was found!');
		
		$model = new $this->model(ConnectionFactory::get($this->db));
		$entry = $model->get($arguments[0], false, false);
		
		if (is_string($entry))
			return Error::set($entry);
		
		$this->view['valid'] = true;
		$this->view['post'] = $entry;
		
		if (!empty($arguments[1]) && $arguments[1] == 'save') {
			if (($forms = $this->validatePost($arguments)) == false)
				return Error::set('All forms need to be filled out.');
			
			$args = array_unshift($forms, $arguments[0]);
			$return = call_user_func_array(array($model, 'edit'), $forms);
			
			if (is_string($return))
				return Error::set($return);
			
			$this->view['post'] = $model->get($arguments[0], false, false);
			Error::set('Entry edited!', true);
		}
	}
	
	public function delete($arguments) {
		if (!CheckAcl::can('delete' . $this->permission))
			return Error::set('You are not allowed to delete ' . $this->pluralize($this->name) . '!');
		if (empty($arguments[0]))
			return Error::set('No ' . $this->name . ' id was found!');
		
		$model = new $this->model(ConnectionFactory::get($this->db));
		$return = call_user_func_array(array($model, 'delete'), array($arguments[0]));
		
		if (is_string($return))
			return Error::set($return);
		
		Error::set(ucwords($this->name) . ' deleted!', true);
		header('Location: ' . Url::format($this->location));
	}
}
