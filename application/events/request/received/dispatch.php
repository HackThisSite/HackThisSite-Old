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
        $request = array_filter(explode('/', $uri), function($n)
        {
            return ($n !== null && $n !== '');
        });

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
            $request = array(0 => self::DEFAULT_CONTROLLER);
        }

        // if the supplied method doesn't exist then default it to the index
        // handler method
        if (!method_exists($controller, $request[0]))
        {
            $request = array_merge(array(0 => self::DEFAULT_METHOD), $request);
        }

        // pass the request to the controller and return the result
        Layout::set("content", new $controller($request));
    }
}