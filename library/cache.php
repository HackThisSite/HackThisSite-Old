<?php
class Cache {

    // Caching
    // In order to have a function cached, set it to protected and prefix 
    // the name with "get"
    public function __call($name, $arguments) {
        if (apc_exists($key = self::genCacheKey($name, $arguments)))
            return apc_fetch($key);
        
        $return = call_user_func_array(array($this, $name), $arguments);
        apc_store($key, $return, 5);
        return $return;
    }
    
    public static function __callStatic($name, $arguments) {
        if (apc_exists($key = self::genCacheKey($name, $arguments)))
            return apc_fetch($key);

        $return = call_user_func_array('static::' . $name, $arguments);
        apc_store($key, $return, 5);
        return $return;
    }
    
    private static function genCacheKey($name, $arguments) {
        return 'data_' . md5($name . serialize($arguments));
    }
    
}
