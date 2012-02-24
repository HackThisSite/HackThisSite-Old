<?php

class events_request_ended_timer
{
    static public function handler($data = null)
    {
        Layout::set("pageExecutionTime", round(microtime(true) - $GLOBALS['start'], 4));
    }
}
