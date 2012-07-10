Notes on v5
===========

* If finding content seems to be slow, use ensureIndex on important 
     fields in a collection.  Some are as follows:
```
news:  date
articles: date
...
comments: contentId
```
* A good firewall strategy in production would be to drastically limit
  the number of maximum concurrent connections for a user.  v5 uses 
  sleep()'s and this could be used to overload memory.
* If user activity isn't showing up in their settings, it means logging
  daemon probably isn't started.
