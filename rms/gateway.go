package main

import (
    "time"
    "strings"
    "strconv"
    
    "github.com/simonz05/godis"
)

type CacheEntry struct {
    Value int
    TTL time.Time
}

type Request struct {
    Name string
    Param []string
    Reply chan interface{}
}

var (
    cache = map[string] CacheEntry {}
    queue = make(chan Request, 10)
    
    redis = godis.New("tcp:localhost:6379", 0, "")
)

func gateway() {
    for true {
        request := <- queue
        
        cacheReturn := handleCache(request)
        if cacheReturn != -1 {
            request.Reply <- cacheReturn
            continue
        }
        
        redisReturn := handleRedis(request)
        addToCache(request, redisReturn)
        
        request.Reply <- redisReturn
    }
}

func handleCache(request Request) int {
    key := request.Name + ":" + strings.Join(request.Param, ",")
    value, ok := cache[key]
    if !ok { return -1 }
    
    if (value.TTL.Unix() - time.Now().Unix()) <= 0 { delete(cache, key);return -1 }
    if value.Value == 1 { return 1 }
    return 0
}

func addToCache(request Request, toReturn int) {
    num := time.Unix(time.Now().Unix() + 10, 0)
    cache[request.Name + ":" + strings.Join(request.Param, ",")] = 
        CacheEntry{Value: toReturn, TTL: num}
}

func handleRedis(request Request) int {
    toReturn := false
    var err error
    
    switch {
        case request.Name == "sIsMember":
            toReturn, err = redis.Sismember(request.Param[0], request.Param[1])
            handleErr(err)
        
        case request.Name == "exists":
            toReturn, err = redis.Exists(request.Param[0])
            handleErr(err)
        
        case request.Name == "expire":
            num, err := strconv.ParseInt(request.Param[1], 10, 64);handleErr(err)
            toReturn, err = redis.Expire(request.Param[0], num)
            handleErr(err)
        
        case request.Name == "publish":
            _, err = redis.Publish(request.Param[0], request.Param[1])
            handleErr(err)
    }
    
    if toReturn { return 1 }
    return 0
}
