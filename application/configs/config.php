<?php
$config = array(
    'production' => array(
        'db' => array(
            'host' => 'localhost',
            'username' => 'username',
            'password' => 'password',
            'db' => 'blue'
        )
    ),
    'dev' => array(
        'db' => array(
            'host' => 'localhost',
            'port' => '',
            'user' => 'htsdb',
            'pass' => 'htsdb',
            'db' => 'hackthissite'
        ),
        'mongo' => array(
            'ip' => '127.0.0.1',
            'db' => 'hackthissite'
        ),
        'redis' => '127.0.0.1',
        'recaptcha' => array(
            'publicKey' => 'wat',
            'privateKey' => 'ok'
        ),
        'debug' => array(
            'on' => true,
            'backtrace' => true,
            'globals' => true,
            'exception' => true
        ),
        'layout' => 'main',
        'security' => array(
            'token' => 'site-wide-secret-salt'
        ),
        'forums' => array(
            'prefix' => 'phpbb_',
            'session_length' => 3600,
        ),
        'baseUrl' => 'http://localhost/',
        'dataServer' => 'http://'.rand(0,3).'.static.htscdn.org',
    )
);
?>
