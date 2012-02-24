<?php
class events_request_ended_session {
    
    public static function handler() {
        Session::write();
    }
    
}
