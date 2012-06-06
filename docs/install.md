HackThisSite v5 Installation Guide
==================================

Prerequisites
-------------
- php5
- git
- nginx
- php-apc
- php5-fpm
- php-pear
- libpcre-dev
- mongodb
- redis-server
- redis-doc
- [ElasticSearch](http://www.elasticsearch.org/download/)
- [phpredis](http://www.github.com/nicolasff/phpredis)
- PECL extensions (apc, bbcode, mongo)

#### PHP ####

##### php.ini: #####
```
    extension=apc.so
    extension=bbcode.so
    extension=mongo.so
    extension=redis.so
    apc.slam_defense=0
    session.save_handler=redis
    session.save_path = "tcp://localhost:6379?weight=1&timeout=0.5"
```

#### Nginx ####

##### /etc/nginx/sites-available/htsv5: #####
```
    server {

	listen  80; ## listen for ipv4
	server_name  localhost;

	root /var/www/htsdev/;

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
	        fastcgi_param  SCRIPT_FILENAME    /var/www/htsdev/sys/dispatcher.php;
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
 
	        fastcgi_pass 127.0.0.1:9000;
	}
    }
```

```
    $ rm /etc/nginx/sites-enabled/default
    $ ln -s /etc/nginx/sites-available/HTSv5 /etc/nginx/sites-enabled/
```

Installing the Code
-------------------
Generate an OpenSSH public/private keypair: `$ ssh-keygen -t rsa`

##### ~/.ssh/config: ######
```
    Host git.hackthissite.org
         HostName git.hackthissite.org
         User git
         IdentityFile /home/<username>/.ssh/<privkey>\
```

```
    $ cd /var/www
    $ git clone git@git.hackthissite.org:htsdev.git
    $ cd /var/www/html/application/configs/servers
    $ mkdir *host.example.org*
    $ touch *host.example.org*/override.php
```

##### *host.example.org*/override.php: #####
```
    return array("system:environment" => "dev",
        "other:baseUrl" => "http://192.168.101.65/",
        "other:staticUrl" => "http://192.168.101.65/static/");
```
Note: Do not forget the trailing slash.