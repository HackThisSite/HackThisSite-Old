<?php

class events_request_received_layout
{
    const CONFIG_LAYOUT = "display:layout";

    static public function handler($data = null)
    {
        // hard code the layout for now, pull
        // later we'll pull the default layout from the config and then check
        // it against user preference.
        Layout::selectLayout(Config::get(self::CONFIG_LAYOUT));
    }
}