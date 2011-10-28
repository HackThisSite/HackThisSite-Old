<?php
/**
* Authors:
*   Thetan ( Joseph Moniz )
**/

class Partial
{
    const PARTIAL_BASE = '../../partials/';

    public static function render($partial, $data = array())
    {
        $path = self::PARTIAL_BASE . Layout::getLayout() . '/';
        $partial = new View($path . $partial, $data);
        return $partial->render();
    }
}
