<?php
class Date {
	
	// Day-precision dates
	static public function dayFormat($date) {
		return date('j M, Y', (int) $date);
	}
	
	// Minute-precision dates
	static public function minuteFormat($date) {
		return date('j M, Y H:i T', (int) $date);
	}
	
}
