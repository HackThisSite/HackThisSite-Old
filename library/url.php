<?php
class Url {
    
    public static function format($url) {
		$info = parse_url($url);
		if (isset($info['host']))
			return $url;
		
        $base = Config::get('other:baseUrl');
        return ($base[strlen($base) - 1] == '/' && $url[0] == '/' ? substr($base, 0, -1) : $base) . $url;
    }
}
