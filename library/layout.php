<?php

class Layout
{
    private $layoutPath = '';

    public function __construct($layoutPath)
    {
        $this->layoutPath = $layoutPath;
    }

    // Make the magic happen babeeh.
    public function parse()
    {
        // Import all global variables
        // NOTE: We should make this more explicit
        //       later on.
        extract($GLOBALS);

        // Store the contents of the current output
        // buffer in $page_content so it can be accessed
        // from within the layout view as such. Then load
        // and parse the selected layout, returning the
        // results.
        $page_content = ob_get_clean();
        ob_start();
        require_once $this->layoutPath;
        return ob_get_clean();
    }

    // Any use of this object as a string type
    // (passing to echo and such) will result in
    // this function being returned.
    public function __toString()
    {
        return $this->parse();
    }
}
