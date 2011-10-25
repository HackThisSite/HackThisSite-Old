<?php
class driver_json_view
{
    static public function render($view, $data)
    {
        return json_encode(self::objectToArray($data));
    }

    function objectToArray($data)
    {
        $result = array();
        $references = array();

        foreach ($data as $key => $value)
        {
            if (is_object($value) || is_array($value))
            {
                if (!in_array($value, $references))
                {
                    $result[$key] = objectToArray($value);
                    $references[] = $value;
                }
                continue;
            }
            $result[$key] = $value;
        }
        return $result;
    }
}
