<?php
class Filter
{
    private $input;

    protected function __construct($input)
    {
        $this->input = $input;
    }

    public function int($key)
    {
        if (!isset($input[$key]) || $input[$key] === null)
        {
            return null;
        }

        return (int) $input[$key];
    }

    public function string($key)
    {
        if (!isset($input[$key]) || $input[$key] === null)
        {
            return null;
        }

        return htmlentities($input[$key], ENT_QUOTES);
    }

    public function raw($key)
    {
        if (!isset($input[$key]) || $input[$key] === null)
        {
            return null;
        }

        return $input[$key];
    }
}