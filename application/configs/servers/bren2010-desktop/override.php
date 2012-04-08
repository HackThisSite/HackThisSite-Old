<?php
// Bren2010's Config.
// (Sorry for the generalized hostname.)

return array(
    "system:environment" => "dev",
    "ssl:certificate" => "file:///var/www/ca.crt",
    "ssl:key" => array("file:///var/www/ca.key", "test"),
    'other:baseUrl' => '/',
//    "mongo:port" => "41795",
);
