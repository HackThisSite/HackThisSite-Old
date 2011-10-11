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

$maind = getcwd().'/../';

set_include_path(get_include_path() . PATH_SEPARATOR . $maind.'library');

class lazyLoader
{
    const PREFIX            = "lazyLoaderStat:";
    const PREFIX_MODEL      = "model:";
    const PREFIX_CONTROLLER = "controller:";
    const PREFIX_LIBRARY    = "library:";
    const PREFIX_HOOK       = "hook:";
    const PREFIX_DRIVER     = "drivers:";

    private static $instance;
    
    private function __construct($hooks = false)
    {
        spl_autoload_register(null, false);
        spl_autoload_extensions('.php');
        spl_autoload_register(array($this, 'router'));
        spl_autoload_register(array($this, 'model'));
        // view loading is handled automatically by the view class
        spl_autoload_register(array($this, 'library'));
    }
    
    public function router($name) {
        if (substr($name, -11) == '_controller') 
            return $this->controller(substr($name, 0, -11));
        if (substr($name, -5) == '_hook')
            return $this->hook(substr($name, 0, -5));
        if (substr($name, -7) == '_driver')
            return $this->driver(strstr($name, '_', true));
            
        return false;
    }

    public function model($name)
    {
        $key    = self::PREFIX . self::PREFIX_MODEL . $name;
        $cached = apc_fetch($key);

        if ($cached === null) { return false; }
        if ($cached !== false)
        {
            include $cached;
            return true;
        }

        if ($name[0] != strtoupper($name[0]))
        {
            apc_store($key, null);
            return false;
        }

        $name = strtolower($name);
        $main = $GLOBALS['maind'];
        $file = "{$main}application/models/{$name}.php";
        if (!file_exists($file))
        {
            apc_store($key, null);
            return false;
        }

        apc_store($key, $file);
        include $file;
    }

    public function controller($name)
    {
        $key    = self::PREFIX . self::PREFIX_CONTROLLER . $name;
        $cached = apc_fetch($key);

        if ($cached === null) { return false; }
        if ($cached !== false)
        {
            include $cached;
            return true;
        }

        $main = $GLOBALS['maind'];
        $file = "{$main}application/controllers/{$name}.php";
        if (!file_exists($file))
        {
            apc_store($key, null);
            return false;
        }

        apc_store($key, $file);
        include $file;
    }

    public function library($name)
    {
        $key    = self::PREFIX . self::PREFIX_LIBRARY . $name;
        $cached = apc_fetch($key);

        if ($cached === null) { return false; }
        if ($cached !== false)
        {
            include $cached;
            return true;
        }

        if ($name[0] != strtoupper($name[0]))
        {
            apc_store($key, null);
            return false;
        }
        
        $name = strtolower($name);
        $main = $GLOBALS['maind'];
        $file = "{$main}library/{$name}.php";
        if (!file_exists($file))
        {
            apc_store($key, null);
            return false;
        }

        apc_store($key, $file);
        include $file;
    }
    
    public function hook($name)
    {
        $key    = self::PREFIX . self::PREFIX_HOOK . $name;
        $cached = apc_fetch($key);

        if ($cached === null) {
            return false;
        }
        if ($cached !== false)
        {
            include $cached;
            return true;
        }

        $main = $GLOBALS['maind'];
        $file = "{$main}application/hooks/{$name}.php";
        if (!file_exists($file))
        {
            apc_store($key, null);
            return false;
        }

        apc_store($key, $file);
        include $file;
    }

    public function driver($name)
    {
        $key    = self::PREFIX . self::PREFIX_DRIVER . $name;
        $cached = apc_fetch($key);

        if ($cached === null) {
            return false;
        }
        if ($cached !== false)
        {
            include $cached;
            return true;
        }

        $main = $GLOBALS['maind'];
        $file = "{$main}drivers/{$name}.php";
        if (!file_exists($file))
        {
            apc_store($key, null);
            return false;
        }

        apc_store($key, $file);
        include $file;
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
}

function genKey($sensitivity) {
    if ($sensitivity == 'all') {
        $data = serialize($_GET) . serialize($_POST);
    } else if ($sensitivity == 'unique') {
        $data = serialize($_GET) . serialize($_POST) .serialize($_COOKIE) . serialize($_SERVER);
    } else {
        return false;
    }
    
    $hash = hash('adler32', $data);
    return $hash;
}

function dispatch($controller, $request = false, $viewData = false, $standAlone = false)
{
    $controller .= '_controller';
    if (class_exists($controller))
    {
        $GLOBALS['errors'] = array();
        
        if (empty($request[0])) $request = array(0 => "index");
        if (!method_exists($controller, $request[0])) $request = array_merge(array(0 => 'index'), $request);
        
        if (apc_exists(genKey('all')) || apc_exists(genKey('unique'))) {
            $data = apc_fetch(genKey('all'));
            if ($data == false) $data = apc_fetch(genKey('unique'));
            
            if ($data['what'] == 'c') {
                $state = new Controller($request, $data['data'], true);
                $state->callStatic(substr($controller, 0, -11), $request[0]);
            } else if ($data['what'] == 'v') {
                $extension = explode('.', end($request));
                if (count($extension) == 2) {
                    $driver = $extension[1];
                } else {
                    $driver = 'traditional';
                }
                
                if ($driver != 'traditional') {
                    $state = new $controller($request);
                } else {
                    require $GLOBALS['maind'].'application/layouts/'.$GLOBALS['config']['layout'] . '.php';
                    $template = new $GLOBALS['config']['layout']();
                    echo $template->template($data['data']);
                    return;
                }
            } else {
                $state = new $controller($request);
            }
        } else {
            $state = new $controller($request);
        }
        
        // Cache
        if (empty($data) && !empty($GLOBALS['cache']) && $GLOBALS['cacheData']['what'] == 'c')
            $GLOBALS['cacheData']['data'] = $state->view;
        
        $GLOBALS['errors'] = $state->getErrors();
        
        if (!$standAlone)
           return $state->getResult();
        
        echo $state->getResult()->parse();
    }
    else
    {
        include_once("./../application/errors/404.php");
    }
}

lazyLoader::initialize();

$hooks = HookHandler::singleton(
    array(
        'ini' => array(
            'startup',
        ),
        'end' => array(
        )
    )
);

// proccess request string
function cleanArray($var) {
    if ($var !== null && $var !== '') return true;
    return false;
}

if (empty($_GET['r'])) $_GET['r'] = 'index/index';
$request = array_filter(explode('/', $_GET['r']), 'cleanArray');
$controller = array_shift($request);

dispatch($controller, $request, false, true);

$hooks->runHooks('end');

// Cache
if (!empty($GLOBALS['cache'])) 
    apc_store($GLOBALS['cacheKey'], $GLOBALS['cacheData'], $GLOBALS['cacheTtl']);
