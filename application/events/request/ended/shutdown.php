<?php

class events_request_ended_shutdown
{
    static public function handler($data = null)
    {
        Layout::set("pageExecutionTime", round(microtime(true) - $GLOBALS['start'], 5));
        
        $quotes = new quotes(ConnectionFactory::get('mongo'));
        Layout::set("randomQuote", $quotes->getRandom());
        Layout::set("leftNav", Config::get("display:leftnav"));
        
        //$start = microtime(true);
        echo Layout::render();
        //$end = microtime(true);
        //echo $end - $start;
        //die;
        Session::write();
    }
}
