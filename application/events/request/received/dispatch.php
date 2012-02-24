<?php

class events_request_received_dispatch
{
    const CONTROLLER_PREFIX  = "controller_";
    const DEFAULT_CONTROLLER = "index";
    const DEFAULT_METHOD     = "index";

    static public function handler($data = null)
    {
        self::dispatch(self::getRequestFromUri());
    }

    static private function getRequestFromUri()
    {
        // crop out the get request of the uri
        list($uri) = explode("?", $_SERVER['REQUEST_URI']);

        // filter out all blank segments of the uri
        $request = array_values(array_filter(explode('/', $uri), function($n)
        {
            return ($n !== null && $n !== '');
        }));

        // if the uri is empty default it to index
        if (!count($request)) {
            $request = array(self::DEFAULT_CONTROLLER, self::DEFAULT_METHOD);
        }
		
        return $request;
    }

    static private function dispatch($request)
    {	
        $controller = self::CONTROLLER_PREFIX . array_shift($request);
		
        // if no route is set then default to index
        if (!count($request))
        {
            $request = array(0 => self::DEFAULT_METHOD);
        }
		
        if (!class_exists($controller))
            $controller = "controller_nil";
        
		if (self::dispatchCache($controller, $request)) 
			return;
        
		$class = new $controller($request);
        
        // pass the request to the controller and return the result
        Layout::set("content", $class);
        
    }
    
    static private function dispatchCache($controller, $request) {
        $params = $request;array_shift($params);
		if (!$data = $controller::getCache($request[0], $params)) return false;
		if (!apc_exists($data['key'])) return false;
		$content = apc_fetch($data['key']);
        
		if ($data['type'] == 'v') {
			Layout::set('content', $content);
		} else if ($data['type'] == 'c') {
			
		}
        Layout::$data = array_merge(Layout::$data, apc_fetch($data['key'] . '_layout'));
		return true;
	}
}
