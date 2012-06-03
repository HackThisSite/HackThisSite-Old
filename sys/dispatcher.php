<?php
/**
Copyright (c) 2010, HackThisSite.org
All rights reserved.

Redistribution and use in source and binary forms, with or without
modification, are permitted provided that the following conditions are met:
    * Redistributions of source code must retain the above copyright
      notice, this list of conditions and the following disclaimer.
    * Redistributions in binary form must reproduce the above copyright
      notice, this list of conditions and the following disclaimer in the
      documentation and/or other materials provided with the distribution.
    * Neither the name of the HackThisSite.org nor the
      names of its contributors may be used to endorse or promote products
      derived from this software without specific prior written permission.

THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS ``AS IS'' AND ANY
EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE IMPLIED
WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE ARE
DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT HOLDER OR CONTRIBUTORS BE LIABLE FOR ANY
DIRECT, INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES
(INCLUDING, BUT NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES;
LOSS OF USE, DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND
ON ANY THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT
(INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE OF THIS
SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.
*/

/**
* Authors:
*   Thetan ( Joseph Moniz )
**/

// Set error handlers
error_reporting(E_ALL);
function errorHandler($errno, $errstr, $errfile, $errline) {
    $error = 'Error #' . $errno . ':  ' . $errstr . ' in ' . $errfile . 
        ' on line ' . $errline;
    Log::error($error);
    
    if (Config::get('system:environment') == 'dev') {
        echo $error;
    } else {
        echo 'Sorry, an error has occured.';
    }
    die;
}
set_error_handler("errorHandler");

// add our library path to the include path
set_include_path(
    get_include_path() .
    PATH_SEPARATOR .
    dirname(dirname(__FILE__)) .
    'library'
);

class lazyLoader
{
    const PREFIX            = "lazyLoaderStat:";
    const PREFIX_MODEL      = "model:";
    const PREFIX_CONTROLLER = "controller:";
    const PREFIX_LIBRARY    = "library:";
    const PREFIX_EVENT      = "events:";
    const PREFIX_DRIVER     = "drivers:";

    private $root;
    private $cache = array();
    private static $instance;

    private function __construct()
    {
        $this->root = dirname(dirname(__FILE__)) . '/';

        spl_autoload_register(null, false);
        spl_autoload_extensions('.php');
        spl_autoload_register(array($this, 'cached'));
        spl_autoload_register(array($this, 'library'));
        spl_autoload_register(array($this, 'model'));
        spl_autoload_register(array($this, 'event'));
        spl_autoload_register(array($this, 'controller'));
        spl_autoload_register(array($this, 'driver'));
        
        $this->cache = apc_fetch('lazyLoader_cache');
    }
	
    public function cached($name)
    {
		if (!isset($this->cache[$name])) return false;
        require $this->cache[$name];
        return true;
    }

    public function model($name)
    {
        if ($name[0] == strtoupper($name[0]))
            return false;

        $newName = strtolower($name);
        $file = "{$this->root}application/models/{$newName}.php";
        if (!file_exists($file))
            return false;

        $this->cache[$name] = $file;
        require $file;
    }

    public function controller($name)
    {
        if (strncmp($name, "controller_", 11) !== 0)
            return false;
		
        $newName = substr($name, 11);
        $file = "{$this->root}application/controllers/{$newName}.php";
        
        if (!file_exists($file)) return false;
        
        $this->cache[$name] = $file;
        require $file;
    }

    public function library($name)
    {
        if ($name[0] != strtoupper($name[0]))
            return false;
		
        $newName = strtolower($name);
        $file = "{$this->root}library/{$newName}.php";
        
        if (!file_exists($file))
            return false;

		$this->cache[$name] = $file;
        require $file;
    }

    public function event($name)
    {
        if (strncmp($name, 'events_', 7) !== 0)
            return false;

        $newName = str_replace("_", "/", $name);
        $file = "{$this->root}application/{$newName}.php";

		$this->cache[$name] = $file;
        require $file;
    }

    public function driver($name)
    {
        if (strncmp($name, "driver_", 7) !== 0)
            return false;

        $newName = substr($name, 7, -5);
        $file = "{$this->root}drivers/{$newName}.php";

		$this->cache[$name] = $file;
        
        if (!file_exists($file)) die('Invalid driver.');
        require $file;
    }

    public static function initialize($hooks = false)
    {
        if (!isset(self::$instance))
        {
            $thisClass = __CLASS__;
            self::$instance = new $thisClass($hooks);
        }
        return self::$instance;
    }

    public function __clone()
    {
        die('Error: Can not be cloned.');
    }
    
    public function __destruct() {
		if (!empty($this->cache) && $this->cache != apc_fetch('lazyLoader_cache'))
			apc_add('lazyLoader_cache', $this->cache);
	}
}

lazyLoader::initialize();
Log::$start = microtime(true);

$observer = Observer::singleton(
    array(
        'request/received' => array(
            'startup',
            'autoauth',
            'dispatch'
        ),
        'request/ended' => array(
            'shutdown'
        )
    )
);


$observer->trigger("request/received");

$observer->trigger("request/ended");
//Log::general();

