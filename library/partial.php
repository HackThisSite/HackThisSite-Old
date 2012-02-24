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
        
        if (!file_exists(dirname(dirname(__FILE__)) . View::VIEW_PATH . 
            Layout::getLayout() . '/' . $path . $partial . View::VIEW_EXT)) 
            $path = self::PARTIAL_BASE . 'main/';
        
        $partial = new View($path . $partial, $data);
        return $partial->render();
    }
}
