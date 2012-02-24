<?php

class ConnectionFactory
{
    const PATH_TO_CONNECTIONS = "/application/connections/";
    const EXTENSION           = ".php";
    const CLASS_PREFIX        = "Connection";

    const CACHE_PREFIX = "confactory:";

    static private $_localized = array();

    public static function get($type, $data = array())
    {
        if (isset($_localized[$type]))
        {
            $path = $_localized[$type];
        }
        else
        {
            $cacheKey = self::CACHE_PREFIX . $type;
            $path     = apc_fetch($cacheKey);
            if ($path === null) {
                return false;
            }

            if ($path === false)
            {
                $path = dirname(dirname(__FILE__))
                . self::PATH_TO_CONNECTIONS
                . $type
                . self::EXTENSION;
                if (!file_exists($path))
                {
                    apc_store($cacheKey, null);
                    return false;
                }
                self::$_localized[$type] = $path;
                apc_store($cacheKey, $path);
            }
        }
		
        require_once $path;

        $class = self::CLASS_PREFIX . ucfirst($type);
        return $class::builder($data);
    }
}
