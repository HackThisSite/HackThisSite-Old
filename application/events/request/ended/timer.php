<?php

class events_request_ended_timer
{
    static public function handler($data = null)
    {
        Layout::set(
            "pageExecutionTime",
            substr(
                (string) (microtime(true) - Layout::get("pageExecutionTime")),
                0,
                6
            )
        );
    }
}