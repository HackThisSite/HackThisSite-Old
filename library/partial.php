<?php
/**
* Authors:
*   Thetan ( Joseph Moniz )
**/

class Partial
{
    const PARTIAL_PATH = '../widgets/';

    public static function render($partial, $data = array())
    {
        $partial = new View(self::PARTIAL_PATH . $viewPath, $data);
        return $partial->render();
    }
}
