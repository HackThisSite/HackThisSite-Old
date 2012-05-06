<?php

class events_request_ended_shutdown
{
    static public function handler($data = null)
    {
        Layout::set("leftNav", Config::get("display:leftnav"));
        
        echo Layout::render();
        Session::write();
    }
}
