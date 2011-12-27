<?php
class events_request_received_session {
    
    public static function handler() {
        Session::init();
    }
    
}
