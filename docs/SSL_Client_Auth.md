SSL Client Authentication
=========================

Setting Up the CA
-----------------
Create the CA certificate pair for signing client certs:
```
$ openssl genrsa -out ca.key 4096
$ openssl req -new -x509 -days 365 -key ca.key -out ca.crt
```

The location of the certificate and key files will be in the 
ssl:certificate and ssl:key configuration directives.  These two files 
will need to be somewhere accessible by the web server and php-fpm.  For 
example, /var/www/, if you keep your repository in /var/www/html/.


Setting Up SSL on Nginx
-----------------------
In order to allow SSL connections on Nginx, you need a server 
certificate and key.  You can generate them by running:
```
$ openssl genrsa -out server.key 1024
$ openssl req -new -x509 -days 365 -key server.key -out server.crt
```

Put server.key and server.crt in the same place you put the CA's key 
pair in the section above.  Then copy your server block that you use 
for normal site visits and change the listen directive to accept 
connections on port 443.  Also, add the following lines in the server 
block (anywhere is fine as long as it is not in a location block!):
```
ssl on;
ssl_certificate /var/www/server.crt;
ssl_certificate_key /var/www/server.key;
```

Then, where you have your list of fastcgi_params, add the directives:
```
fastcgi_param SSL_CLIENT_CERT $ssl_client_cert;
fastcgi_param SSL_CLIENT_RAW_CERT $ssl_client_raw_cert;
```

Just make sure that they are in the appropriate location block and above 
the fastcgi_pass line.  You can verify that SSL works by saving your 
config, restarting nginx, and going to where you have the repository 
set up with https:// as the protocol (https://localhost/).


Setting Up Nginx
----------------
Once you have SSL enabled on Nginx , add the following lines below your 
SSL directives:
```
ssl_verify_client optional; # Ask for a client certificate
ssl_client_certificate /var/www/ca.crt; # Location of the CA's certificate
```

Make sure to restart Nginx if it's currently running.  Also, if you want 
to keep your keys safe from other users on your box, you can chown them 
to your web user (root or www-data are fine), and chmod them to 600.


Other Information
-----------------
Certificate authentication is only available through an SSL connection 
to the webserver.  In order to utilize this feature you can read the 
article at /pages/info/keyauthentication on your installation of v5.  
Your key pair will only work on the server you set them up on, unless 
two servers are specifically meant to allow the same authentication 
certificates.


More Reading
------------
http://wiki.nginx.org/HttpSslModule
http://blog.nategood.com/client-side-certificate-authentication-in-ngi
https://github.com/nategood/sleep-tight/blob/master/scripts/create-certs.sh


Example Configuration
---------------------
```
server {

	listen  443;
	server_name localhost;
    keepalive_timeout 70;
    
	root /var/www/html/;

	ssl on;
	ssl_certificate /var/www/server.crt;
	ssl_certificate_key /var/www/server.key;
	ssl_verify_client optional;
	ssl_client_certificate /var/www/ca.crt;
        
	location / {
		index  sys/dispatcher.php;

		if (-e $request_filename) {
			break;
		}

		if (!-e $request_filename) {
			rewrite ^(.+)$ sys/dispatcher.php last;
			break;
		}
	}

	location ~ (.+)\.php$ {
        	fastcgi_param  QUERY_STRING       $query_string;
        	fastcgi_param  REQUEST_METHOD     $request_method;
	        fastcgi_param  CONTENT_TYPE       $content_type;
	        fastcgi_param  CONTENT_LENGTH     $content_length;
 
	        fastcgi_param  SCRIPT_NAME        sys/dispatcher.php;
	        fastcgi_param  SCRIPT_FILENAME    /var/www/html/sys/dispatcher.php;
	        fastcgi_param  REQUEST_URI        $request_uri;
	        fastcgi_param  DOCUMENT_URI       $document_uri;
	        fastcgi_param  DOCUMENT_ROOT      $document_root;
	        fastcgi_param  SERVER_PROTOCOL    $server_protocol;
 
        	fastcgi_param  GATEWAY_INTERFACE  CGI/1.1;
	        fastcgi_param  SERVER_SOFTWARE    nginx;
 
        	fastcgi_param  REMOTE_ADDR        $remote_addr;
	        fastcgi_param  REMOTE_PORT        $remote_port;
	        fastcgi_param  SERVER_ADDR        $server_addr;
	        fastcgi_param  SERVER_PORT        $server_port;
	        fastcgi_param  SERVER_NAME        $server_name;
	        
            fastcgi_param SSL_CLIENT_CERT $ssl_client_cert;
            fastcgi_param SSL_CLIENT_RAW_CERT $ssl_client_raw_cert;
 
	        fastcgi_pass 127.0.0.1:9000;
	}
}
```