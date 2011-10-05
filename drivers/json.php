<?php
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

class json_view_driver
{
	public function parse($view, $data)
	{
        $data = objectToArray($data);
		return json_encode($data);
	}
}
