<?php

class events_request_ended_layout
{
    static public function handler($data = null)
    {
        echo Layout::render();
    }
}