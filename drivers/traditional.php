<?php
class driver_traditional_view
{

    static public function render($view, $data, $finalCut)
    {
        Layout::set("leftNav", Config::get("display:leftnav"));
        
        // localize all the view variables.
        extract($data);
        
        // Start capturing a new output buffer, load the view
        // and apply all the display logic to the localized data
        // and save the results in $parsed.
        ob_start();
        require $view;
        $parsed = ob_get_clean();
        if (!$finalCut) return $parsed;
        
        Layout::set("content", $parsed);
        
        ob_start();
        echo Layout::render();
        $full = ob_get_clean();
        
        return $full;
    }
}
