<?php
class Id {
	
	static public function create($data, $type) {
		switch ($type) {
			case 'news':
				$string = trim(strtolower($data['title']));
				$string = preg_replace('{(-)\1+}', '-', preg_replace('/[^\w\d_ -]/si', '-', $string));
				$string = trim(str_replace(' ', '_', $string), '-_');
				
				return date('Y/m/dHi_', $data['date']) . $string;
                break;
                
            case 'bug':
                $time = $data['_id']->getTimestamp();
                $number = self::gmp_convert((string) $data['_id'], 16, 10);
                $bytes = self::uncompliment($number, 12);
                $id = self::gmp_convert($time, 10, 62) . str_pad(self::gmp_convert((int) $bytes[11], 10, 62), 2, '0', STR_PAD_LEFT);
                
                return $id;
                break;
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
                
            case 'bug':
                $toReturn = array();
                $toReturn['time'] = self::gmp_convert(substr($hash, 0, -2), 62, 10);
                
                return $toReturn;
				
		}
		
		return false;
	}
	
	static public function validateHash($hash, $data, $type) {
		switch ($type) {
			case 'news':
				$realHash = self::create($data, $type);
				return ($realHash == $hash || ($data['date'] >= $data['reportedDate'] && $data['date'] <= $data['reportedDate'] + $data['ambiguity']));
				
            case 'bugs':
                $time = self::gmp_convert(substr($hash, 0, -2), 62, 10);
                $last = self::gmp_convert(substr($hash, -2), 62, 10);
                
                $number = self::gmp_convert((string) $data['_id'], 16, 10);
                $bytes = self::uncompliment($number, 12);
                
                return ($time == $data['created'] && $last == $bytes[11]);
		}
		
		return false;
	}
    
    
    private function gmp_convert($num, $base_a, $base_b) 
    {
        return gmp_strval ( gmp_init($num, $base_a), $base_b );
    }
    
    private function uncompliment($number, $length) {
        $length = $length - 1;
        $array = array();
        
        for ($pos = 0; $pos <= $length;++$pos) {
            $pow = bcpow(256, ($length - $pos));
            $var = floor(bcdiv($number, $pow));
            
            array_push($array, $var);
            $number = bcsub($number, bcmul($var, $pow));
        }
        
        return $array;
    }

    private function compliment($bytes) {
        $bytes = array_reverse($bytes);
        $sum = 0;
        
        foreach ($bytes as $pow => $value) {
            $sum = bcadd($sum, bcmul($value, bcpow(256, $pow)));
        }
        
        return $sum;
    }

	
}
