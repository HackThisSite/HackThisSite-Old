<?php
class driver_traditional_view
{
    const KEY_LAYOUT = "display:layout";

    static public function render($view, $data)
    {
        // localize all the view variables.
        extract($data);

        // Start capturing a new output buffer, load the view
        // and apply all the display logic to the localized data
        // and save the results in $parsed.
        ob_start();
        require $view;
        $parsed = ob_get_clean();
        return $parsed;
    }
}
