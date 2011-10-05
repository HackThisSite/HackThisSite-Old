<?php
class Date {
	
	// Day-precision dates
	public function dayFormat($date) {
		return date('j M, Y', $date);
	}
	
	// Minute-precision dates
	public function minuteFormat($date) {
		return date('j M, Y H:i T', $date);
	}
	
}
