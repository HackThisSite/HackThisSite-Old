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
        
        self::EzAddPurge($key, $return);
        
        return $return;
    }
    
    public static function __callStatic($name, $arguments) {
        if (apc_exists($key = self::genCacheKey($name, $arguments)))
            return apc_fetch($key);

        $return = call_user_func_array('static::' . $name, $arguments);
        apc_store($key, $return, self::TTL);
        
        self::EzAddPurge($key, $return);
        
        return $return;
    }
    
    private static function genCacheKey($name, $arguments) {
        return self::PREFIX . 'data_' . md5($name . '--' . serialize($arguments));
    }
    
    /** DATA CACHING **/
    
    public static function ApcAdd($key, $value, $ttl = self::TTL, $purgeOn = null) {
        apc_store(self::PREFIX . $key, $value, $ttl);
        
        if ($purgeOn == null) return;
        self::AddPurge(self::PREFIX . $key, $purgeOn);
    }
    
    public static function ApcPurge($id) {
        $keyName = self::PREFIX . 'purgeOn_' . get_called_class() . '_' . $id;
        
        $keys = apc_fetch($keyName);
        if (empty($keys)) return null;
        
        foreach ($keys as $key) {
            apc_delete($key);
        }
        
        apc_delete($keyName);
    }
    
    public static function EzAddPurge($key, $return) {
        if (!is_array($return)) return;
        if (!empty($return['_id'])) // Normal one entry bit of content.
            return self::AddPurge($key, get_called_class() . '_' . $return['_id']);
        
        $first = reset($return);
        if (!empty($first['_id'])) { // Array of multiple pieces of content.
            foreach ($return as $entry) {
                if (!empty($entry['_id']))
                    self::AddPurge($key, get_called_class() . '_' . $entry['_id']);
            }
        }
    }
    
    public static function AddPurge($key, $purgeOn) {
        $purgeKey = self::PREFIX . 'purgeOn_' . $purgeOn;
        
        if (!apc_exists($purgeKey)) 
            return apc_store($purgeKey, array($key));
        
        $keys = apc_fetch($purgeKey);
        array_push($keys, $key);
        apc_store($purgeKey, array_unique($keys));
    }
    
}
