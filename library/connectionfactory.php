<?php

class ConnectionFactory
{
    const PATH_TO_CONNECTIONS = "/application/connections/";
    const EXTENSION           = ".php";
    const CLASS_PREFIX        = "Connection";

    const CACHE_PREFIX = "confactory:";

    public static function get($type, $data = array())
    {
        $path = apc_fetch(self::CACHE_PREFIX . $type);
        if ($path === null) { return false; }

        if ($path === false)
        {
            $path = dirname(dirname(__FILE__))
                  . self::PATH_TO_CONNECTIONS
                  . $type
                  . self::EXTENSION;
            if (!file_exists($path)) { return false; }
        }

        require_once $path;

        $class = self::CLASS_PREFIX . ucfirst($type);
        return $class::builder($data);
    }
}