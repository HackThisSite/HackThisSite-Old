<?php

class events_request_ended_shutdown
{
    static public function handler($data = null)
    {
        Layout::set("leftNav", Config::get("display:leftnav"));
        
        $twitter = new twitter(ConnectionFactory::get('redis'));
        Layout::set('tweets', $twitter->getOfficialTweets());
        
        echo Layout::render();
        Session::write();
    }
}
