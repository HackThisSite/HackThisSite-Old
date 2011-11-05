<?php

class ConnectionMongo
{
    const KEY_HOST     = "server";
    const KEY_PORT     = "port";
    const KEY_USERNAME = "username";
    const KEY_PASSWORD = "password";

    static private $connections = array();
    static private $indexKeys = array(
        self::KEY_HOST     => "",
        self::KEY_PORT     => "",
        self::KEY_USERNAME => "",
        self::KEY_PASSWORD => ""
    );
    static private $keyDefaults = array();
    
    static private function populateDefaults() {
		foreach (self::$indexKeys as $key => $nil) {
			self::$keyDefaults[$key] = Config::get('mongo:' . $key);
		}
	}

    static public function builder($data = array())
    {
		if (empty(self::$keyDefaults))
			self::populateDefaults();
		
        // set the defaults
        $data = array_merge(self::$keyDefaults, $data);

        // build the connection key
        $key = self::dataToKey($data);

        // if the connection is already made return it
        if (isset(self::$connections[$key]))
        {
            return self::$connections[$key];
        }

        // filter out auth keys from mongo options
        $options = array_diff_key($data, self::$keyDefaults);
		print_r($data);
        // create a new connection and store it for later reuse
        return self::$connections[$key] = new Mongo(
            "mongodb://{$data[self::KEY_HOST]}:{$data[self::KEY_PORT]}",
            $options
        );
    }

    static private function dataToKey($data)
    {
        // normalize the data by sorting on keys
        ksort($data);
        return implode(':', $data);
    }
}
