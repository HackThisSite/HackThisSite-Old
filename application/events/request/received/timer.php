<?php

class events_request_received_timer
{
    static public function handler($data = null)
    {
        Layout::set("pageExecutionTime", microtime(true));
    }
}