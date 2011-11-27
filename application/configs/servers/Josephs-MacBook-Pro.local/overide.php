<?php
/**
 * personal config file for Thetan's laptop, feel free to make a folder in
 * application/configs/servers with your hostname to overide any config param
 * you want safely in a place where it won't effect anyone else - Joseph Moniz
 */

return array(
    "system:environment" => "dev",

    /**
     * i have mutliple dev environments on this system so i need to run
     * this dev env on a special local vhost
     */
    'other:baseUrl' => 'http://local.hackthissite.org/'
);