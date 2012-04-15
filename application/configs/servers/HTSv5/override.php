<?php

return array("system:environment" => "dev",
	"other:baseUrl" => "http://192.168.101.65/",
	"other:staticUrl" => "http://192.168.101.65/static/",
	"ssl:certificate" => "file:///var/www/keys/ca.crt",
	"ssl:key" => "file:///var/www/keys/ca.key"); //if key has a passwd use an array consisting of the key and the password
