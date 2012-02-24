<?php

class events_request_ended_layout
{
    static public function handler($data = null)
    {
        $quotes = new quotes(ConnectionFactory::get('mongo'));
        Layout::set("randomQuote", $quotes->getRandom());
        Layout::set("leftNav", Config::get("display:leftnav"));
        
        echo Layout::render();
    }
}
