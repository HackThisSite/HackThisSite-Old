<?php

class events_controller_ended_getquote
{
    const CONNECTION_TYPE = 'mongo';

    static public function handler($data = null)
    {
        $quotes = new quotes(ConnectionFactory::get(self::CONNECTION_TYPE));
        Layout::set("randomQuote", $quotes->getRandom());
    }
}