<?php

class Layout
{
    const FOLDER_LAYOUTS   = "/application/layouts/";
    const LAYOUT_EXTENSION = ".php";

    private static $layout  = "";
    private static $data = array();

    /**
     * Used to set layout parameters
     * @param key $key
     * @param mixed $value
     */
    public static function set($key, $value)
    {
        self::$data[$key] = $value;
    }

    /**
     * Used to fetch layout parameters
     * @param string $key
     */
    public static function get($key)
    {
        return (isset(self::$data[$key])) ? self::$data[$key] : false;
    }

    /**
     * This function is used to select the layout set to use
     * @param string $layout
     */
    public static function selectLayout($layout)
    {
        self::$layout = $layout;
    }

    public static function getLayout()
    {
        return self::$layout;
    }

    /**
     * Renders the templage
     */
    public static function render()
    {
        // Import all template variables
        extract(self::$data);

        // Store the contents of the current output
        // buffer in $page_content so it can be accessed
        // from within the layout view as such. Then load
        // and parse the selected layout, returning the
        // results.
        $page_content = ob_get_clean();
        ob_start();
        require_once dirname(dirname(__FILE__))
                   . self::FOLDER_LAYOUTS
                   . self::$layout
                   . self::LAYOUT_EXTENSION
                   ;

        return ob_get_clean();
    }

    /**
     * Typical string conversion
     */
    public function __toString()
    {
        return $this->parse();
    }
}
