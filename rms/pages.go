package main

import (
    "strconv"
    "net/http"
    "net/url"
)

type Reply map[string] interface{}

// Index page
func index(w http.ResponseWriter, req *http.Request) {
    err, ok := validate(w, req)
    rep := Reply{"ok": ok, "error": err}
    output(w, rep)
}

// Mark a mission as completed.
func complete(w http.ResponseWriter, req *http.Request) {
    err, ok := validate(w, req)
    rep := Reply{"ok": ok, "error": err}
    
    if ok {
        toReply := make(chan interface{}, 10)
        query, err := url.ParseQuery(req.URL.RawQuery);handleErr(err)
        missionId := query.Get("missionId")
        userId := query.Get("userId")
        
        queue <- Request{
            Name: "publish",
            Param: []string{"log_rms", missionId+":"+userId},
            Reply: toReply}
        _ = <- toReply
    }
    output(w, rep)
}

// Check if a user id is valid.
func check(w http.ResponseWriter, req *http.Request) {
    err, ok := validate(w, req)
    rep := Reply{"valid": false, "ok": ok, "error": err}
    if ok { rep["valid"] = true }
    output(w, rep)
}

// Refresh a user's session for another TTL.
func refresh(w http.ResponseWriter, req *http.Request) {
    err, ok := validate(w, req)
    rep := Reply{"ok": ok, "error": err}
    
    if ok {
        toReply := make(chan interface{}, 10)
        query, err := url.ParseQuery(req.URL.RawQuery);handleErr(err)
        userId := query.Get("userId")
        
        queue <- Request{
            Name: "expire",
            Param: []string{"rms_user_"+userId, strconv.Itoa(ttl)},
            Reply: toReply}
        success := <- toReply
        
        if success == false {
            rep["ok"] = false
            rep["error"] = "User does not exist."
        }
    }
    output(w, rep)
}
