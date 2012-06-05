<?php
class Cache {
    
    const PREFIX = 'hts_';
    const TTL = 10;
    /** FUNCTION CACHING **/
    
    // Caching
    // In order to have a function cached, set it to protected and prefix 
    // the name with "get"
    public function __call($name, $arguments) {
        if (apc_exists($key = self::genCacheKey($name, $arguments)))
            return apc_fetch($key);
        
        $return = call_user_func_array(array($this, $name), $arguments);
        apc_store($key, $return, self::TTL);
        
        return $return;
    }
    
    public static function __callStatic($name, $arguments) {
        if (apc_exists($key = self::genCacheKey($name, $arguments)))
            return apc_fetch($key);
        
        $return = call_user_func_array('static::' . $name, $arguments);
        apc_store($key, $return, self::TTL);
        
        return $return;
    }
    
    static $hasArray = false;
    private static function toString($value) { 
        (is_array($value) && gettype($value) != 'MongoId' ? self::$hasArray = true : false);
        return (string) $value;
    }
    
    private static function genCacheKey($name, $arguments) {
        $argumentAStr = array_map('self::toString', $arguments);
        $argumentSStr = implode('-', $argumentAStr);
        
        $toReturn = self::PREFIX . 'data_' . 
            get_called_class() . '_' . $name . 
            '-' . 
            (strlen($argumentSStr) > 100 || self::$hasArray ? 
                md5(serialize($arguments)) : $argumentSStr);
        self::$hasArray = false;
        
        return $toReturn;
    }
    
    public static function ApcPurge($func, $id) {
        $apc = new APCIterator('user', '/' . Cache::PREFIX . 'data_' . 
            get_called_class() . '_' . $func . '-' . $id . '.*/');
        
        while ($apc->valid()) {
            $current = $apc->current();
            apc_delete($current['key']);
            $apc->next();
        }
    }
    
}
