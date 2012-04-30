<?php
/**
 * Methods for representing time stamps.
 * 
 * This class contains the methods for representing date and time 
 * stamps.  You should always call these for textual representations of 
 * dates, so that time is representing uniformly and it will be easier 
 * to add localization in the future.
 * 
 * @package Library
 */
class Date {
	
	/**
	 * Day-precision dates
	 * 
	 * @param int $date Timestamp
	 * 
	 * @return string Textual representation of $date.
	 */
	static public function dayFormat($date) {
		return date('j M, Y', (int) $date);
	}
	
	/**
	 * Minute-precision dates
	 * 
	 * @param int $date Timestamp
	 * 
	 * @return string Textual representation of $date.
	 */
	static public function minuteFormat($date) {
		return date('j M, Y H:i T', (int) $date);
	}
    
    /**
     * RFC 2822 formatted dates
     * 
     * @param int $date Timestamp
     * 
     * @return string Textual representation of $date.
     */
    static public function computerFormat($date) {
        return date('r', (int) $date);
    }
    
    /**
     * Duration formated dates
     * 
     * @param int $duration Duration in seconds.
     * 
     * @return string Textual representation of $duration.
     */
    static public function durationFormat($duration) {
        $granularity = 4;
        $difference = $duration;
        $retval = null;
        $periods = array(
            'year' => 31536000,
            'month' => 2628000,
            'week' => 604800, 
            'day' => 86400,
            'hour' => 3600,
            'min' => 60,
            'sec' => 1);

        foreach ($periods as $key => $value) {
            if ($difference >= $value) {
                $time = floor($difference/$value);
                $difference %= $value;
                $retval .= ($retval ? ' ' : '').$time.' ';
                $retval .= (($time > 1) ? $key.'s' : $key);
                $granularity--;
            }
            if ($granularity == '0') { break; }
        }
        return $retval;
    }
	
}
