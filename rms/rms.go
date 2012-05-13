package main

import (
	"fmt"
	"os"
	"big"
	"http"
	"net"
)

var hostname string
var hts = "http://v3dev.hackthissite.org/"

func main() {
	listener, err := net.Listen("tcp", ":51064")
	handleErr(err)
	
	for true {
		conn, err := listener.Accept();handleErr(err)
		
		fmt.Println("New connection from", conn.RemoteAddr().String())
		go client(conn)
	}
}

func client(conn net.Conn) {
	err := conn.SetTimeout(1000000000);handleErr(err)
	
	bytes := make([]byte, 20)
	_, err = conn.Read(bytes)
	if err != nil {
		fmt.Println("Error: Connection failed with", conn.RemoteAddr().String())
		fmt.Println("Error:", err)
		return
	}
	
	serverId := compliment(bytes[0:4])
	userId := compliment(bytes[4:8])
	missionId := compliment(bytes[8:12])
	
	req := hts+"rms.php?ip="+conn.RemoteAddr().String()
	req = req+"&serverId="+serverId
	req = req+"&userId="+userId
	req = req+"&missionId="+missionId
	
	response, err := http.Get(req)
	if err != nil {
		fmt.Println("Error:", err)
		return
	}
	
	buffer := make([]byte, 1024)
	n, err := response.Body.Read(buffer)
	if err != nil {
		fmt.Println("Error:", err)
		return
	}
	
	output := buffer[0:n]
	if string(output) == "true" {
		fmt.Print("Notice: Successfully marked mission.  ")
	} else {
		fmt.Print("Error:  Access denied to mark mission.  ")
	}
	
	fmt.Println(serverId, "-", userId, "-", missionId)
	conn.Close()
}

func compliment(bytes []byte) string {
	top := len(bytes) - 1
	sum := big.NewInt(0)
	pos := 0
	
	for top >= 0 {
		temp1 := big.NewInt(0)
		temp2 := big.NewInt(0)
		
		temp1.Exp(big.NewInt(256), big.NewInt(int64(top)), nil)
		temp2.Mul(big.NewInt(int64(bytes[pos])), temp1)
		sum.Add(sum, temp2)
		top = top - 1
		pos = pos + 1
	}
	
	return sum.String()
}

func handleErr(err os.Error) {
	if err != nil {
		fmt.Println("Fatal: ", err)
		os.Exit(1)
	}
}
