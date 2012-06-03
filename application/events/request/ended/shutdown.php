<?php

class events_request_ended_shutdown
{
    static public function handler($data = null)
    {
        Session::write();
    }
}
