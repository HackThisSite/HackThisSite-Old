<?php
class driver_blank_view
{
    
    static public function render($view, $data, $finalCut)
    {
        if (!$finalCut) return '';
        Log::error('Blank hit');
        return 'herp';
    }
}
