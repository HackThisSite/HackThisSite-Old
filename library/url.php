<?php
class Url {
    
    public static function format($url) {
        $base = Config::get('other:baseUrl');
        return ($base[strlen($base) - 1] == '/' && $url[0] == '/' ? substr($base, 0, -1) : $base) . $url;
    }
}
