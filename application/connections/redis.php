<?php
class ConnectionRedis {
    
    const KEY = 'redis';
    const KEY_HOST = 'server';
    const KEY_PORT = 'port';
    
    static private $connections = array();
    
    static public function builder($data = array()) {
        $key = self::dataToKey($data);
        
        if (isset(self::$connections[$key]))
            return self::$connections[$key];
        
        $info = array(
            'host' => Config::get(self::KEY . ':' . self::KEY_HOST),
            'port' => Config::get(self::KEY . ':' . self::KEY_PORT)
            );
        $info = array_merge($info, $data);
        
        self::$connections[$key] = new Redis();
        
        if (empty($info['port'])) { // Allows connection to the local socket
            self::$connections[$key]->connect($info['host']);
        } else {
            self::$connections[$key]->connect($info['host'], $info['port']);
        }
        
        return self::$connections[$key];
    }
    
    static private function dataToKey($data)
    {
        // normalize the data by sorting on keys
        ksort($data);
        return implode(':', $data);
    }
    
}
