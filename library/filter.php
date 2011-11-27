<?php
class Filter
{
    static protected $input = null;

    static protected function import($input)
    {
        if (self::$input === null)
        {
            self::$input = $input;
        }
    }

    static public function int($key)
    {
        static::import();
        if (!isset(self::$input[$key]) || self::$input[$key] === null)
        {
            return null;
        }

        return (int) self::$input[$key];
    }

    static public function string($key)
    {
        static::import();
        if (!isset(self::$input[$key]) || self::$input[$key] === null)
        {
            return null;
        }

        return htmlentities(self::$input[$key], ENT_QUOTES);
    }

    static public function raw($key)
    {
        static::import();
        if (!isset(self::$input[$key]) || self::$input[$key] === null)
        {
            return null;
        }

        return self::$input[$key];
    }
}