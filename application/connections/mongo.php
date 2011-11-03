<?php

class ConnectionMongo
{
    const KEY_HOST     = "host";
    const KEY_PORT     = "port";
    const KEY_USERNAME = "username";
    const KEY_PASSWORD = "password";

    static private $connections = array();
    static private $keyDefaults = array(
    );

    static public function builder($data = array())
    {
		if (count(self::$keyDefaults) == 0) self::populateDefaults();
		
        // set the defaults
        $data = array_merge(self::$keyDefaults, $data);

        // build the connection key
        $key = self::dataToKey($data);

        // if the connection is already made return it
        if (isset(self::$connections[$key]))
        {
            return self::$connections[$key];
        }
		print_r($data);
        // filter out auth keys from mongo options
        $options = array_diff_key($data, self::$keyDefaults);

        // create a new connection and store it for later reuse
        return self::$connections[$key] = new Mongo(
            "mongodb://{$data[self::KEY_HOST]}:{$data[self::KEY_PORT]}",
            $options
        );
    }
    
    static private function populateDefaults() {
		self::$keyDefaults[self::KEY_HOST] = Config::get('mongo:server');
		self::$keyDefaults[self::KEY_PORT] = Config::get('mongo:port');
		self::$keyDefaults[self::KEY_USERNAME] = Config::get('mongo:user');
		self::$keyDefaults[self::KEY_PASSWORD] = Config::get('mongo:pass');
	}

    static private function dataToKey($data)
    {
        // normalize the data by sorting on keys
        ksort($data);
        return implode(':', $data);
    }
}
