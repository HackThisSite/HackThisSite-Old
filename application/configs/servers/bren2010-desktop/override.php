<?php
// Bren2010's Config.
// (Sorry for the generalized hostname.)

return array(
	"system:mail" => false,
    "system:environment" => "dev",
    "ssl:certificate" => "file:///var/www/ca.crt",
    "ssl:key" => array("file:///var/www/ca.key", "test"),
    'other:baseUrl' => 'http://10.0.0.10/',
    'other:staticUrl' => 'http://10.0.0.10/static/'
//    "mongo:port" => "41795",
);
