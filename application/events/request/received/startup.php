<?php

class events_request_received_startup
{
    const CONFIG_LAYOUT = "display:layout";

    private static $errors = array(
        'Fatal error: Exception thrown without a stack frame in Unknown on line 0',
        'Guru Meditation:  0xDEADBEEF',
        'Load Balancer Error:  503 Service Not Found',
        'It appears Page Cache URL rewriting is not working. If using apache, verify that the server configuration allows .htaccess or if using nginx verify all configuration files are included in the configuration.',
        'mod_deflate: Not detected (required for Page Cache (enhanced mode) and Browser Cache)<br />
mod_env: Not detected (required for Page Cache (enhanced mode) and Browser Cache)<br />
mod_expires: Not detected (required for Page Cache (enhanced mode) and Browser Cache)<br />
mod_headers: Not detected (required for Page Cache (enhanced mode) and Browser Cache)<br />
mod_mime: Not detected (required for Page Cache (enhanced mode) and Browser Cache)<br />
mod_rewrite: Not detected (required for Page Cache (enhanced mode) and Browser Cache)<br />
mod_setenvif: Not detected (required for Page Cache (enhanced mode) and Browser Cache)',
        'Fatal error: Browser type not detected in Unknown on line 0',
        'Fatal error: Cookies not enabled on client in Unknown on line 0',
        'Proxy detected.',
        'Brute forcers not allowed!',
        '<center><div style="width:80%">Your authentication hash has been invalidated.  This means your ip address has changed in the middle of a session.<br />Our solution is to please clear your browser\'s cache/cookies, <a href="Fatal error: Url not found in /var/www/sys/dispatcher.php on line 1047">logout</a>, and relogin.</center></div>'
    );
    
    static public function handler($data = null)
    {
        Session::init();
        
        $key = Cache::PREFIX . 'sessionReq_' . Session::getId();
        if (apc_exists($key)) {
            Session::setBatchVars(apc_fetch($key));
            apc_delete($key);
        }
        
        // hard code the layout for now, pull
        // later we'll pull the default layout from the config and then check
        // it against user preference.
        Layout::selectLayout(Config::get(self::CONFIG_LAYOUT));
        
        self::slowBan();
        self::errorBan();
    }
    
    // Slow Banning
    static public function slowBan() {
        $bans = Session::getVar('bans');
        if (empty($bans)) return;
        if ($bans['slowBan']) goto ban;
        
        return;
        ban:
        sleep(rand(2, 10));
    }
    
    // Error Banning
    static public function errorBan() {
        $bans = Session::getVar('bans');
        if (empty($bans)) return;
        if ($bans['errorBan']) goto ban;
        
        return;
        ban:
        $rand = rand(0, 1);
        
        if ($rand) {
            die(self::$errors[array_rand(self::$errors)]);
        }
    }
}
