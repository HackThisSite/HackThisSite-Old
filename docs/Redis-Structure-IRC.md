Redis Structure of IRC Content
==============================

Required:
* Redis connection (via phpredis found at https://github.com/nicolasff/phpredis)

Users Online
------------
(Managed by bot, viewed on site)

### Structure ###
```
Key:  usersOnline
Type: set
Value: List of users in public channels, with all user mode symbols 
    stripped.  This needs to be deleted or cleared and updated 
    frequently.  Every ping (90 seconds) is acceptable.
```

### Implementation ###
```
$namesReply = $irc->names($channel);

$redis->del('usersOnline');
foreach ($namesReply as $nick) {
	$redis->sAdd('usersOnline', $nick);
}
```

Linking Requests
----------------
(Added by bot, viewed and managed on site)

### Structure ###
```
Key: linkReqs_{username}
Type: set
Value: List of linkable IRC nicks.
```


### Implementation ###
```
$redis->sAdd('linkReqs_' . $username, $nick);
```

Linked Nicks
------------
(Managed on site)

### Requirements ###
* Quick to find *all nicks* from *username*.
* Quick to find *username* from *one nick*.


### Structure ###
```
Key: userNicks_(username)
Type: set
Value: List of IRC nicks

Key: nick2User_(nick)
Type: string
Value: (username);(registration time as unix timestamp)
```