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

    /*
     * Fetches a config parmeter from shared memory (cache) or if the
     * applications config hasn't been loaded into cache yet, it will 
     * preload all parameters in cache in the propper cascading order.
     */
    static public function get($key)
    {
        self::_preloadConfigIfNotLoaded();
        return apc_fetch(self::PREFIX . $key);
    }

    /*
     * Overides a config parameter until the next time the configs get relaoded.
     * NOTE: this should only really be used for debugging purposes in from
     *       some sort of admin interface.
     */
    static public function set($key, $value)
    {
        self::_preloadConfigIfNotLoaded();
        apc_store(self::PREFIX . $key, $value);
    }

    static private function _preloadConfigIfNotLoaded()
    {
        $key = "_" . self::PREFIX . "is_loaded";
        if (apc_fetch($key) !== false)
        {
            // yay, we already loaded the config, return to what we were doing.
            return;
        }

        // load the local configs for this server based on hostname
        $configBase = dirname(dirname(__FILE__)) . "/application/configs";
        $serverConf = self::_loadConfigsRecursively($configBase . "/servers/" . gethostname());

        if (!isset($serverConf[self::ENVIRONMENT_KEY]))
        {
            throw new Exception("You need to have the '".self::ENVIRONMENT_KEY."' set to either dev, stage or prod in this servers config");
        }

        // Next we overide config parameters in a cascading order, `common` 
        // being the first config parameters to be overidden, followed by the
        // `environment` parameters, with the server specific parameters having
        // the final say in the matter.
        $finalConf = array_merge(
            self::_loadConfigsRecursively($configBase . "/common"),
            self::_loadConfigsRecursively($configBase . "/environment/" . $serverConf[self::ENVIRONMENT_KEY]),
            $serverConf
        );

        // populate the shared memory cache with the final config state.
        foreach($finalConf as $key => $value)
        {
            apc_store(self::PREFIX . $key, $value);
        }

        // set the flag indicating that the shared memory cache has been
        // populated with the config state for this machine
        apc_store($key, true);
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
