<?php
class Id {
	
	static public function create($data, $type) {
		switch ($type) {
			case 'news':
				$string = trim(strtolower($data['title']));
				$string = preg_replace('{(-)\1+}', '-', preg_replace('/[^\w\d_ -]/si', '-', $string));
				$string = trim(str_replace(' ', '_', $string), '-_');
				
				return date('Y/m/dHi_', $data['date']) . $string;
		}
		
		return false;
	}
	
	static public function dissectKeys($hash, $type) {
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
	
	static public function validateHash($hash, $data, $type) {
		switch ($type) {
			case 'news':
				$realHash = self::create($data, $type);
				return ($realHash == $hash || ($data['date'] >= $data['reportedDate'] && $data['date'] <= $data['reportedDate'] + $data['ambiguity']));
				
		}
		
		return false;
	}
	
}
