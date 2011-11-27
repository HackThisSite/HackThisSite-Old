<?php
/*
 * Site wide cascading configuration system that's highly cached
 *
 * @author    Joseph Moniz <joseph.moniz@gmail.com>
 */

class Config
{
    const PREFIX          = "_config:";
    const ENVIRONMENT_KEY = "system:environment";

    const DIR_CONF_BASE   = "/application/configs";
    const DIR_SERVER_BASE = "/servers/";
    const DIR_COMMON_BASE = "/common";
    const DIR_ENV_BASE    = "/environment/";
    const DIR_LOCAL_BASE  = "/local";

    const MISSING_ENV = "You need to have the %s set to either dev, stage or prod in this servers config";

    static private $_localized = array();
    static private $_isLoaded = false;

    /*
     * Fetches a config parmeter from shared memory (cache) or if the
     * applications config hasn't been loaded into cache yet, it will
     * preload all parameters in cache in the propper cascading order.
     */
    static public function get($key)
    {
        self::_preloadConfigIfNotLoaded();
        if (isset(self::$_localized[$key])) { return self::$_localized[$key]; }
        return self::$_localized[$key] = apc_fetch(self::PREFIX . $key);
    }

    /*
     * Overides a config parameter until the next time the configs get relaoded.
     * NOTE: this should only really be used for debugging purposes in from
     *       some sort of admin interface.
     */
    static public function set($key, $value)
    {
        self::_preloadConfigIfNotLoaded();
        $this->_localized[$key] = $value;
        apc_store(self::PREFIX . $key, $value);
    }

    static private function _preloadConfigIfNotLoaded()
    {
        // avoid doing string operations and a call to APC if we can
        if (self::$_isLoaded) { return; }

        $isLoadedKey = "_" . self::PREFIX . "is_loaded";
        if (apc_fetch($isLoadedKey) !== false)
        {
            // yay, we already loaded the config, return to what we were doing.
            self::$_isLoaded = true;
            return;
        }

        self::forceReload($isLoadedKey);
    }

    static public function forceReload($isLoadedkey = null)
    {
        // load the local configs for this server based on hostname
        $configBase = dirname(dirname(__FILE__)) . self::DIR_CONF_BASE;
        $serverConf = self::_loadConfigsRecursively($configBase . self::DIR_SERVER_BASE . gethostname());
        $initialConf = array_merge(
            (is_array($serverConf)) ? $serverConf : array(),
            self::_loadConfigsRecursively($configBase . self::DIR_LOCAL_BASE)
        );

        // Next we overide config parameters in a cascading order, `common`
        // being the first config parameters to be overidden, followed by the
        // `environment` parameters, with the server specific parameters having
        // the final say in the matter.


        if (!isset($initialConf[self::ENVIRONMENT_KEY]))
        {
            throw new Exception(sprintf(self::MISSING_ENV, self::ENVIRONMENT_KEY));
        }

        $finalConf = array_merge(
            self::_loadConfigsRecursively($configBase . self::DIR_COMMON_BASE),
            self::_loadConfigsRecursively($configBase . self::DIR_ENV_BASE . $initialConf[self::ENVIRONMENT_KEY]),
            $initialConf
        );

        // populate the shared memory cache with the final config state.
        foreach($finalConf as $key => $value)
        {
            apc_store(self::PREFIX . $key, $value);
        }

        // set the flag indicating that the shared memory cache has been
        // populated with the config state for this machine
        if ($isLoadedKey === null)
        {
            $isLoadedKey = "_" . self::PREFIX . "is_loaded";
        }
        apc_store($isLoadedKey, true);
    }

    /*
     * This function just recursively loads all the config files in the
     * path supplied path
     */
    static private function _loadConfigsRecursively($path)
    {
        $configs = array();
        $files   = array_diff(
            scandir($path),
            array(
                ".",
                ".."
            )
        );

        foreach($files as $file)
        {
            $filePath = $path . '/' . $file;
            if (is_dir($filePath))
            {
                $configs = array_merge(
                    $configs,
                    self::_loadConfigsRecursively($filePath)
                );
                continue;
            }

            $configs = array_merge(
                $configs,
                eval(str_replace("<?php", "", file_get_contents($filePath)))
            );
        }

        return $configs;
    }
}
