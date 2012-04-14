<?php
return array(
	'config' => '/etc/ssl/openssl.cnf',
	'digest_alg' => 'md5',
	'x509_extensions' => 'v3_ca',
	'req_extensions'   => 'v3_req',
	'private_key_bits' => 666,
	'private_key_type' => OPENSSL_KEYTYPE_RSA,
	'encrypt_key' => false,
);
