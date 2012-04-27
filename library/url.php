<?php
class Url {
    
    static $populated = false;
    static $base;
    static $static;
    
    public static function format($url, $static = false) {
		if (!self::$populated) self::populate();
		if (substr($url, 0, 4) == 'http')
			return $url;
		
        $base = ($static ? self::$static : self::$base);
        $base = (!empty($_SERVER['SSL_CLIENT_RAW_CERT']) ? str_replace('http://', 'https://', $base) : $base);

        return ($base[strlen($base) - 1] == '/' && $url[0] == '/' ? substr($base, 0, -1) : $base) . $url;
    }
    
    private static function populate() {
		self::$populated = true;
		self::$base = Config::get('other:baseUrl');
		self::$static = Config::get('other:staticUrl');
	}
}
