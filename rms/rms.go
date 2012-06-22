package main

import (
    "fmt"
    "io"
    "crypto/tls"
    "crypto/md5"
    "encoding/hex"
    "encoding/pem"
    "encoding/json"
    "net/http"
    "net/url"
    "strings"
    "log"
)

var (
    // These are the same ones used with NginX.
    certificate = "/var/www/server.crt"
    key = "/var/www/server.key"
    
    ttl = 2 * 60 * 60 // 2hrs
)

func main() {
    go gateway()
    http.HandleFunc("/", index) // Index page
    http.HandleFunc("/complete", complete) // Mark a mission as completed.
    http.HandleFunc("/check", check) // Check if a user id is valid.
    http.HandleFunc("/refresh", refresh) // Refresh a user's session for another TTL.
    
    var err error
    tlsConfig := &tls.Config{}
    tlsConfig.Certificates = make([]tls.Certificate, 1)
    tlsConfig.Certificates[0], err = tls.LoadX509KeyPair(certificate, key);handleErr(err)
    tlsConfig.ClientAuth = tls.RequestClientCert
    
    server := &http.Server{Addr: ":8080", Handler: nil, TLSConfig: tlsConfig}
    log.Fatal(server.ListenAndServeTLS(certificate, key))
}

func validate(w http.ResponseWriter, req *http.Request) (string, bool) {
    // Validate the query.
    if req.Method != "GET" { return "Invalid method.", false }
    
    query, err := url.ParseQuery(req.URL.RawQuery);handleErr(err)
    missionId := query.Get("missionId")
    userId := query.Get("userId")
    
    if missionId == "" { return "No mission id found.", false }
    if userId == "" { return "No user id found.", false }
    
    // Validate certificate.
    if len(req.TLS.PeerCertificates) == 0 { return "No certificates.", false }
    cert := req.TLS.PeerCertificates[0]
    block := pem.Block{Type: "CERTIFICATE", Bytes: cert.Raw}
    encoded := string(pem.EncodeToMemory(&block))
    
    h := md5.New()
    io.WriteString(h, encoded)
    certHash := hex.EncodeToString(h.Sum(nil))
    
    // Validate given values with DB.
    toReply := make(chan interface{}, 10)
    queue <- Request{
        Name: "exists", 
        Param: []string{"rms_server_"+certHash+"_ips"},
        Reply: toReply}
    
    output := <- toReply
    if output == 0 { return "Invalid certificate.", false }
    
    raa := strings.SplitN(req.RemoteAddr, ":", 2)
    ipAddr := raa[0] // Isolate IP address of user.
    
    queue <- Request{
        Name: "sIsMember",
        Param: []string{"rms_server_"+certHash+"_ips", ipAddr},
        Reply: toReply}
    queue <- Request{
        Name: "sIsMember",
        Param: []string{"rms_server_"+certHash+"_missions", missionId},
        Reply: toReply}
    queue <- Request{
        Name: "exists",
        Param: []string{"rms_user_"+userId},
        Reply: toReply}
    
    ipGood := <- toReply
    midGood := <- toReply
    uidGood := <- toReply
    
    if ipGood == 0 { return "Invalid IP.", false }
    if midGood == 0 { return "Invalid mission id.", false }
    if uidGood == 0 { return "Invalid user id.", false }
    
    return "", true
}

func output(w http.ResponseWriter, v interface{}) {
    v, err := json.Marshal(v);handleErr(err)
    fmt.Fprintf(w, "%s", v)
}

func handleErr(err error) {
    if err != nil { log.Fatal(err) }
}
