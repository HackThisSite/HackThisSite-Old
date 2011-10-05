<?php
class Id {
	
	public function create($data, $type) {
		switch ($type) {
			case 'news':
				return base_convert(hash('adler32', $data['id']), 16, 36) . '~' . base_convert($data['date'], 10, 36);
		}
		
		return false;
	}
	
	public function dissectKeys($hash, $type) {
		switch ($type) {
			case 'news':
				$sections = explode('~', $hash);
				
				return array('date' => (int) base_convert($sections[1], 36, 10));
		}
		
		return false;
	}
	
	public function validateHash($hash, $data, $type) {
		switch ($type) {
			case 'news':
				$realHash = $this->create($data, $type);
				return ($realHash == $hash);
				
		}
		
		return false;
	}
	
}
