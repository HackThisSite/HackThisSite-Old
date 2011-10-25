<?php

class event_request_received_dispatch
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
        $request = array_filter(explode('/', $uri), function($n)
        {
            return ($var !== null && $var !== '');
        });

        // if the uri is empty default it to index
        if (!count($request)) {
            $request = array(self::DEFAULT_CONTROLLER, self::DEFAULT_METHOD);
        }

    }

    static private function dispatch($request)
    {
        $controller = self::CONTROLLER_PREFIX . array_shift($request);

        // if no route is set then default to index
        if (!count($request))
        {
            $request = array(0 => "index");
        }

        // if the supplied method doesn't exist then default it to the index
        // handler method
        if (!method_exists($controller, $request[0]))
        {
            $request = array_merge(array(0 => 'index'), $request);
        }

        // pass the request to the controller and return the result
        echo new $controller($request);
    }
}