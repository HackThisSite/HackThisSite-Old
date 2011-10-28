<?php
class permissions {

	var $data;
	var $forums;

	public function check($perm) {
		if (empty($this->data)) $this->data = Data::singleton();
		if (empty($this->forums)) $this->forums = new Forums;

		$info = $this->forums->loginData();

		$permList = $this->data->hGet('permissions', $perm);
		if ($permList == false) return false;

		$groups = explode(',', $permList);

		if (in_array($info['group'], $groups)) return true;
		return false;
	}

	public function getGroup() {
		$info = $this->forums->loginData();
		return $info['group'];
	}

}
