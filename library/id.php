<?php
/**
 * Id creation, parsing, and validation.
 * 
 * @package Library
 */
class Id {
    
    /**
     * Id creation
     * 
     * @param array $data The array to create an id out of.
     * @param string $type Type of id being created.
     * 
     * @return string The content's id.
     */
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
                $number = $data['_id']->getInc();
                
                $id = self::gmp_convert($time, 10, 62) . '-' . self::gmp_convert($number, 10, 62);
                
                return $id;
                break;
            
            case 'user':
                return $data['username'];
                break;
        }
        
        return false;
    }
    
    /**
     * Dissect information from an id.
     * 
     * Used to dissect keys from an id that can be used to find it in a 
     * database.
     * 
     * @param string $hash The id being used.
     * @param string $type The type of id being used.
     * 
     * @return array Array of data that can be used in a query for the 
     * original content.
     */
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
                $toReturn['date'] = mktime((int) $toReturn['hour'], 
                    (int) $toReturn['minute'],
                    0, 
                    (int) $toReturn['month'], 
                    (int) $toReturn['day'], 
                    (int) $toReturn['year']);
                //echo $toReturn['date'];
                //die;
                return $toReturn;
                break;
                
            case 'bug':
                $toReturn = array();
                $data = explode('-', $hash);
                $toReturn['time'] = self::gmp_convert($data[0], 62, 10);
                
                return $toReturn;
                break;
            
            case 'user':
                return array('username' => $hash);
                break;
                
        }
        
        return false;
    }
    
    /**
     * Id validation
     * 
     * Used for validating a piece of content directly maps to a given id.
     * 
     * @param string $hash Id to test against.
     * @param array $data Content to test with.
     * @param string $type Type of content/id being used.
     * 
     * @return bool True if the given content directly maps to the given id.
     */
    static public function validateHash($hash, $data, $type) {
        switch ($type) {
            case 'news':
                $realHash = self::create($data, $type);
                return ($realHash == $hash || ($data['date'] >= $data['reportedDate'] && $data['date'] <= $data['reportedDate'] + $data['ambiguity']));
                break;
                
            case 'bugs':
                $hash = explode('-', $hash);
                $time = self::gmp_convert($hash[0], 62, 10);
                $last = self::gmp_convert($hash[1], 62, 10);
                
                return ($time == $data['_id']->getTimestamp() && $last == $data['_id']->getInc());
                break;
                
            case 'user':
                return ($hash == $data['username']);
                break;
        }
        
        return false;
    }
    
    
    private static function gmp_convert($num, $baseA, $baseB) {
        return gmp_strval(gmp_init($num, $baseA), $baseB);
    }
    
}
