<?php
class Recaptcha {
    
    public static $errors = array(
        'invalid-site-private-key' => 'Sorry, we made an error with our recaptcha keys.',
        'invalid-request-cookie' => 'Sorry, but recaptcha said it couldn\'t find the challenge id you gave.',
        'incorrect-captcha-sol' => 'Invalid captcha.',
        'recaptcha-not-reachable' => 'We could not reach our captcha provider.'
    );
    
    const CONFIG_PRIVATEKEY = 'recaptcha:privateKey';
    const RECAPTCHA_API = 'http://www.google.com/recaptcha/api/verify';
    
    public static function check($challenge, $response) {
        $return = self::hit($challenge, $response);
        
        if (empty($return)) return 'recaptcha-not-reachable';
        if ($return[0] == 'true') return true;
        return $return[1];
    }
    
    private static function hit($challenge, $response) {
        $post = array(
            'privatekey' => Config::get(self::CONFIG_PRIVATEKEY),
            'remoteip' => $_SERVER['REMOTE_ADDR'],
            'challenge' => $challenge,
            'response' => $response
        );
        
        $ch = curl_init(self::RECAPTCHA_API);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $post);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $response = curl_exec($ch);
        
        return explode("\n", $response);
    }
    
}
