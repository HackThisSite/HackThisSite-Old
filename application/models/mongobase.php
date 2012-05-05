<?php
/**
 * MongoDB Base
 * 
 * @package Model
 */
class mongoBase {

    /**
     * Cast a value to a MongoId.
     * 
     * Todo:  Implement proper type checking and type casting to MongoId.
     * 
     * @param mixed Normal form of a MongoId (Number, string?)
     */
    protected function _toMongoId($id) {
        return new MongoId($id);
    }
    
    /**
     * Clean a string, so it's safe to put in MongoDB.
     * 
     * @param string $string The string to be cleaned.
     * 
     * @return string The cleaned string.
     */
    public function clean($string) {
        return utf8_encode(htmlentities(trim((string) $string), ENT_QUOTES, '', false));
    }
    
    
    // Caching
    // In order to have a function cached, set it to protected and prefix 
    // the name with "get"
    public function __call($name, $arguments) {
        if (substr($name, 0, 3) != 'get') return false;
        if (apc_exists($key = $this->genCacheKey($name, $arguments)))
            return apc_fetch($key);
        
        $return = call_user_func_array(array($this, $name), $arguments);
        apc_store($key, $return, 5);
        return $return;
    }
    
    private function genCacheKey($name, $arguments) {
        return 'data_' . md5($name . serialize($arguments));
    }
    
}
