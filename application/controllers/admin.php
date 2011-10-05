<?php
class admin_controller extends Controller {
	
	var $bad = false;
	
	public function firstRun() {
		if (!$GLOBALS['permissions']->check('viewAdminPanel')) $this->bad = true;
		if ($this->bad) { 
			$this->setError('You are not allowed here!');
			$this->view['bad'] = true;
		}
		
	}
	
	public function index() {
		if ($this->bad) return false;
	}
	
	public function data($arguments) {
		if ($this->bad) return false;
		
		$data = Data::singleton();
		if (!empty($arguments[0]) && $arguments[0] == 'clear' && $GLOBALS['permissions']->check('apcClear'))
			apc_clear_cache('user');
		if (!empty($arguments[0]) && $arguments[0] == 'bgsave' && $GLOBALS['permissions']->check('redisBgSave'))
			$data->bgSave();
			
		
		$apc = new APCIterator('user');
		$info = apc_cache_info();
		$this->view['count'] = $apc->getTotalCount();
		$this->view['hits'] = $apc->getTotalHits();
		$this->view['misses'] = $info['num_misses'];
		$this->view['size'] = $apc->getTotalSize();
		
		$dates = new Date;
		$info = $data->info();
		
		$this->view['redisVersion'] = $info['redis_version'];
		$this->view['arch_bits'] = $info['arch_bits'];
		$this->view['uptime'] = $info['uptime_in_days'];
		$this->view['numClients'] = $info['connected_clients'];
		$this->view['changes'] = $info['changes_since_last_save'];
		$this->view['bgSave'] = ($info['bgsave_in_progress'] == 1 ? 'Yes' : 'No');
		$this->view['totalConnecRecv'] = $info['total_connections_received'];
		$this->view['totalCmdsPrcsd'] = $info['total_commands_processed'];
		$this->view['redisSize'] = $data->dbSize();
		$this->view['lastSave'] = $dates->minuteFormat($info['last_save_time']);
	}
	
	public function navigation($arguments) {
		if ($this->bad) return false;
		$data = Data::singleton();
		$this->view['navigation'] = $data->zRangeGet('navigation', 0, -1, true);
		$this->view['mode'] = 'list';
		$access = '';
		$location = '';
		
		if (!$GLOBALS['permissions']->check('navigationEdit') || empty($arguments[0]))
			return; // This is required for everything below.
		
		// Shows form for adding a new entry.
		if ($arguments[0] == 'new') {
			$this->view['mode'] = 'new';
			return;
		}
		
		// Shows edit form.
		if (($arguments[0] == 'edit' || $arguments[0] == 'save') && isset($arguments[1]) && $this->view['info'] = $this->findHashed($arguments[1], $this->view['navigation'], true)) {
			$this->view['mode'] = 'edit';
			if ($arguments[0] == 'edit') return;
		}
		
		// Removes entry.
		if ($arguments[0] == 'delete' && isset($arguments[1]) && $serialized = $this->findHashed($arguments[1], $this->view['navigation'])) {
			$this->view['removed'] = true;
			$data->zRem('navigation', $serialized);
			apc_clear_cache('user');
			$this->view['navigation'] = $data->zRangeGet('navigation', 0, -1, true);
			return;
		}
		
		// Saves edited/new entry.
		if ($arguments[0] == 'save' && isset($arguments[1]) && ($arguments[1] == 'new' || $serialized = $this->findHashed($arguments[1], $this->view['navigation']))) {
			($arguments[1] == 'new' ? $this->view['mode'] = 'new' : null);

			$good = $this->insertChecks($access, $location);
			if ($good != 'win') return;
			 
			$newEntry = array('type' => (int) $_POST['type'], 'name' => (string) $_POST['name'], 'location' => $location, 'access' => $access);
			if ($_POST['type'] == 0) unset($newEntry['location']);
			
			// Got this far, save.
			if ($arguments[1] != 'new') $data->zRem('navigation', $serialized);
			$data->zAdd('navigation', $_POST['score'], serialize($newEntry));
			apc_clear_cache('user');
			$this->view['navigation'] = $data->zRangeGet('navigation', 0, -1, true);
			return;
		}
	}
	
	public function access($arguments) {
		if ($this->bad) return false;
		$data = Data::singleton();
		$this->view['permissions'] = $data->hGetAll('permissions');
		$this->view['mode'] = 'list';
		
		if (!$GLOBALS['permissions']->check('accessEdit') || empty($arguments[0]) || empty($arguments[1]))
			return;
			
		if ($arguments[0] == 'edit' && isset($this->view['permissions'][(string) $arguments[1]])) {
			$this->view['mode'] = 'edit';
			$this->view['id'] = (string) $arguments[1];
		}
		
		if ($arguments[0] == 'save' && isset($this->view['permissions'][(string) $arguments[1]])) {
			$this->view['saved'] = true;
			
			if (!empty($_POST['access'])) {
				$access = implode(',', (array) $_POST['access']);
			} else {
				$access = '';
			}
			
			$data->hSet('permissions', (string) $arguments[1], $access);
			apc_clear_cache('user');
			$this->view['permissions'] = $data->hGetAll('permissions');
		}
	}
	
	public function post_news() {
		if ($this->bad) return false;
		if (!$GLOBALS['permissions']->check('postNews'))
			return $this->setError('You are not allowed to post news.');
			
		$this->view['good'] = true;
	}
	
	
	private function findHashed($hash, $navigation, $full = false) {
		foreach ($navigation as $serialized => $score) {
			if (hash('adler32', $serialized) == $hash) return ($full ? array('score' => $score, 'serialized' => $serialized) : $serialized);
		}
		
		return false;
	}
	
	private function insertChecks(&$access, &$location) {
		// Check that type is correct
		if (!isset($_POST['type']) || (isset($_POST['type']) && $_POST['type'] != 1 && $_POST['type'] != 0))
			return $this->setError('Invalid type.');
		
		// Check that name is not empty
		if (empty($_POST['name']))
			return $this->setError('Needs name.');
		
		// Check that score exists
		if (empty($_POST['score']) || (int) $_POST['score'] != $_POST['score'])
			return $this->setError('Needs score.');
		
		$this->view['mode'] = 'list';
		$this->view['saved'] = true;
		
		$data = Data::singleton();
		$info = $data->query('SELECT group_name FROM ' . $GLOBALS['config']['forums']['prefix'] . 'groups WHERE 1 = 1');
		$count = $info['count'];
		
		if (!empty($_POST['access'])) {
			if ($count == count($_POST['access'])) {
				$access = 'all';
			} else {
				$access = (array) $_POST['access'];
			}
		 } else {
				$access = array();
		 }
		 
		 if (!empty($_POST['location'])) {
			 $location = (string) $_POST['location'];
		 } else {
			 $location = '';
		 }
		
		return 'win';
	}
	
}
