<?php

class events_controller_ended_buildnav
{
    static public function handler($data = null)
    {
        Layout::set("leftNav", Config::get("display:leftnav"));
    }
}
