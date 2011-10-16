<?php
class Id {
	
	public function create($data, $type) {
		switch ($type) {
			case 'news':
				return date('Y/m/dHi_', $data['date']) . str_replace(' ', '_', strtolower(preg_replace('/[^\w\d_ -]/si', '', $data['title'])));
		}
		
		return false;
	}
	
	public function dissectKeys($hash, $type) {
		switch ($type) {
			case 'news':
				$sections = explode('/', $hash);
				$toReturn = array();
				$ambiguity = 60; // Minimum of 60 seconds.
				
				$toReturn['year'] = $sections[0];
				
				if (empty($sections[2])) { // No unique address
					$ambiguity = 2678400;  // A month in seconds.
					$toReturn['day'] = 1;
					$toReturn['hour'] = 0;
					$toReturn['minute'] = 0;
				} else {
					$toReturn['day'] = substr($sections[2], 0, 2);
					$toReturn['hour'] = substr($sections[2], 2, 2);
					$toReturn['minute'] = substr($sections[2], 4, 2);
				}
				
				if (empty($sections[1])) { // Months
					$ambiguity = 31556926; // A year in seconds.
					$toReturn['month'] = 1;
				} else {
					$toReturn['month'] = $sections[1];
				}
				
				$toReturn['ambiguity'] = $ambiguity;
				$toReturn['date'] = mktime($toReturn['hour'], $toReturn['minute'],
					0, $toReturn['month'], $toReturn['day'], $toReturn['year']);
				
				return $toReturn;
				
		}
		
		return false;
	}
	
	public function validateHash($hash, $data, $type) {
		switch ($type) {
			case 'news':
				$realHash = $this->create($data, $type);
				return ($realHash == $hash || ($data['date'] >= $data['reportedDate'] && $data['date'] <= $data['reportedDate'] + $data['ambiguity']));
				
		}
		
		return false;
	}
	
}
