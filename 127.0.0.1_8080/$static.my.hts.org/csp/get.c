// ============================================================================
// C servlet sample for the G-WAN Web Application Server (http://trustleap.ch/)
// ----------------------------------------------------------------------------
// get.c: issuing a GET request with the standard BSD socket calls
//
// The heavy tracing (printf()'s output goes to the parent terminal) allows
// you to play with diverse situations, checking what happens at each step.
// ----------------------------------------------------------------------------
// The connect() / write() / read() *blocking* system calls used here work
// *asynchronously* - thanks to the 'magic' of G-WAN's C continuations.
// ============================================================================
// imported functions:
//   get_reply(): get a pointer on the 'reply' dynamic buffer from the server
//     get_env(): get connection's 'environment' variables from the server
//    xbuf_cat(): like strcat(), but it works in the specified dynamic buffer 
// ----------------------------------------------------------------------------
#include "gwan.h" // G-WAN exported functions

#include <sys/socket.h>
#include <sys/ioctl.h>
#include <arpa/inet.h>
#include <netinet/in.h>
#include <netdb.h>
#include <fcntl.h>
#include <unistd.h>
#include <errno.h>

// ----------------------------------------------------------------------------
#define HOST "192.168.200.88"    // available local host
#define PORT "8080"
//#define HOST   "gwan.ch"        // available Internet host
//#define HOST   "www.gwan.ch"        // available Internet host
//#define HOST   "gwan-is-fun.com" // UNavailable domain: gethostname() fails
//#define PORT "80"
//#define PORT "65535"             // UNavailable host: connect() fails
// ----------------------------------------------------------------------------

int main(int argc, char *argv[])
{
   xbuf_t *reply = get_reply(argv);
   char buf[1024] = {0}; // adjust as needed
   int s;
   do 
   {  // ----------------------------------------------------------------------
      // resolve the host
      // ----------------------------------------------------------------------
#ifdef USE_GET_ADDRINFO      
      struct addrinfo *host = 0,
             hints = {.ai_family   = AF_INET, // <- default (can be AF_INET6)
                      .ai_socktype = SOCK_STREAM, //  |
                      .ai_protocol = IPPROTO_TCP, //  V
                      .ai_addrlen  = sizeof(struct sockaddr_in),
                      .ai_canonname = 0,
                      .ai_next     = 0,
                      .ai_addr     = 0,
                      .ai_flags    = NI_NUMERICSERV }, *h = &hints;
/*
      // if HOST is IPv4, we don't use the slow getaddrinfo()
      s = inet_addr(HOST); // "0.0.0.0" => 0x00000000
      if(s != INADDR_NONE) // valid IPv4 address, use it!
      {
         hints.ai_addr = (struct sockaddr*)buf; // provide storage
         struct sockaddr_in *ptr = (struct sockaddr_in*)hints.ai_addr;
         ptr->sin_family      = AF_INET;
         ptr->sin_port        = htons(atoi(PORT));
         ptr->sin_addr.s_addr = s;
      }
      else // invalid IPv4 address, can be IPv6 (or an invalid hostname)
      {
         // here also we should check for an IPV6 address before triggering
         // a DNS lookup - but we don't use IPv6 yet...
         printf("resolving: " HOST "...\n");         
         s = getaddrinfo(HOST, PORT, &hints, &host);
         if(s)
         {
            printf("Can't resolve (%d:%s)\n", s, gai_strerror(s));
            xbuf_xcat(reply, "Can't resolve (%d:%s)", s, gai_strerror(s));
            break;
         }
         h = host; // host was allocated by getaddrinfo(), free it later!
      } */

      // just for the record, this is the normal way of doing things:
      if(inet_addr(HOST) != INADDR_NONE)  // HOST is an IPv4 address?
         hints.ai_flags |= AI_NUMERICHOST; // save a DNS lookup
      
      printf("resolving: " HOST "...\n");         
      s = getaddrinfo(HOST, PORT, &hints, &host); // let's "resolve"...
      if(s)
      {
         printf("Can't resolve (%d:%s)\n", s, gai_strerror(s));
         xbuf_xcat(reply, "Can't resolve (%d:%s)", s, gai_strerror(s));
         s = -1;
         break;
      }
      h = host; // host was allocated by getaddrinfo(), free it later!

      {
         u8 *p = &((struct sockaddr_in*)h->ai_addr)->sin_addr;
         printf("Resolved IP address:%u.%u.%u.%u\n", p[0], p[1], p[2], p[3]);
      }

      // ----------------------------------------------------------------------
      // try all the IP addresses we got, until one lets us connect()
      // ----------------------------------------------------------------------
      do
      {  s = socket(h->ai_family, h->ai_socktype, h->ai_protocol);
         if(s < 0)
            continue;

        printf("trying to connect to " HOST ":" PORT "...\n");
        if(connect(s, h->ai_addr, h->ai_addrlen) == 0) // 0:OK
           break;
        
        close(s); 
        s = -1;
      } while((h = h->ai_next) != 0);

      if(host)
         freeaddrinfo(host);
      if(s < 0)
      {
         printf("Can't connect to " HOST ":" PORT " (%d:%m)\n", errno);
         xbuf_xcat(reply, "Can't connect to " HOST ":" PORT " (%d:%m)", errno);
         break;
      }
#else
      int addr = inet_addr(HOST); // convert string into an integer HOST address
      if(addr == INADDR_NONE)   // is it a valid HOST address?
      {
         // get the HOST address of the specified host name
         struct hostent pHost, *hp;
         char tmp[1024] = {0};
         int err = 0;
         if(gethostbyname_r(HOST, &pHost, tmp, sizeof(tmp), &hp, &err) == 0
         && hp) 
	        addr = *((u32*)pHost.h_addr_list[0]);
         else
	        addr = INADDR_NONE; // failed to resolve hostname
      }
      if(addr == INADDR_NONE)
      {
         printf("Can't resolve " HOST " (%d:%m)\n", errno);
         xbuf_xcat(reply, "Can't resolve " HOST " (%d:%m)", errno);
         break;
      }

      printf("creating a socket...\n");
      s = socket(AF_INET, SOCK_STREAM, 0);
      if(s < 0)
      {
         printf("Can't create socket (%d:%m)\n", errno);
         xbuf_xcat(reply, "Can't create socket (%d:%m)", errno);
         break;
      }
      
      printf("connecting to " HOST ":" PORT "...\n");
      struct sockaddr_in host;
      bzero(&host, sizeof(host));
      host.sin_family = AF_INET;
      host.sin_addr.s_addr = addr;
      host.sin_port = htons((u16)atoi(PORT));
      int ret = connect(s, (struct sockaddr*)&host, sizeof(host));
      if(ret && errno == EINTR) // 0:OK 
      {
         printf("Can't connect to " HOST ":" PORT " (%d:%m)\n", errno);
         xbuf_xcat(reply, "Can't connect to " HOST ":" PORT " (%d:%m)", errno);
         break;
      }
#endif
   
      // ----------------------------------------------------------------------
      // send a request
      // ----------------------------------------------------------------------
      char req[] = "GET / HTTP/1.1\r\n"
                   "Host: " HOST ":" PORT "\r\n"
                   "User-Agent: G-WAN C script\r\n"
                   "Connection: close\r\n"
                   "\r\n";
                   
      printf("writting...\n");
      int len = sizeof(req) - 1;
      if(write(s, req, len) != len)
      {
         printf("Can't write() (%d:%m)\n", errno);
         xbuf_xcat(reply, "Can't write() (%d:%m)", errno);
         break;
      }

      // ----------------------------------------------------------------------
      // read the reply
      // ----------------------------------------------------------------------
      printf("reading...\n");
      *buf = 0;
      for(;;)
      {
         len = read(s, buf, sizeof(buf) - 1);
         if(len <= 0)
            break;
         xbuf_ncat(reply, buf, len);
      }
      
      if(len < 0)
      {
         printf("Can't read() (%d:%m)\n", errno);
         xbuf_xcat(reply, "Can't read() (%d:%m)", errno);
         break;
      }

      // ----------------------------------------------------------------------
      // close client connection
      // ----------------------------------------------------------------------
      printf("closing...\n");
      shutdown(s, SHUT_WR); // clients MUST tell when they close
      close(s); // free memory used by fd
      
      // ----------------------------------------------------------------------
      // send G-WAN reply
      // ----------------------------------------------------------------------
      printf("replying...\n");
      return 200; // return an HTTP code (200:'OK')

   } while(0);

   // -------------------------------------------------------------------------
   // on errors, breaks jump here
   // -------------------------------------------------------------------------
   xbuf_xcat(reply, "<br>An error occured (%d:%m)<br>", errno);
   if(s >= 0)
   {
      shutdown(s, SHUT_WR); // clients MUST tell when they close
      close(s);   // free memory used by fd
   }
   return 200; // return an HTTP code (200:'OK')
}
// ============================================================================
// End of Source Code
// ============================================================================
/* they say this old method is deprecated... *//*    
      int addr = inet_addr(HOST); // convert string into an integer HOST address
      if(addr == INADDR_NONE)   // is it a valid HOST address?
      {
         // get the HOST address of the specified host name
         struct hostent pHost, *hp;
         char tmp[1024] = {0};
         int err = 0;
         if(gethostbyname_r(HOST, &pHost, tmp, sizeof(tmp), &hp, &err) == 0
         && hp) 
	        addr = *((u32*)pHost.h_addr_list[0]);
         else
	        addr = INADDR_NONE; // failed to resolve hostname
      }
      if(addr == INADDR_NONE)
      {
         printf("Can't resolve " HOST " (%d:%m)\n", errno);
         xbuf_xcat(reply, "Can't resolve " HOST " (%d:%m)", errno);
         break;
      }

      printf("creating a socket...\n");
      s = socket(AF_INET, SOCK_STREAM, 0);
      if(s < 0)
      {
         printf("Can't create socket (%d:%m)\n", errno);
         xbuf_xcat(reply, "Can't create socket (%d:%m)", errno);
         break;
      }
      
      printf("connecting to " HOST ":" PORT "...\n");
      struct sockaddr_in host;
      bzero(&host, sizeof(host));
      host.sin_family = AF_INET;
      host.sin_addr.s_addr = addr;
      host.sin_port = htons((u16)atoi(PORT));
      if(connect(s, (struct sockaddr*)&host, sizeof(host))) // 0:OK 
      
*/
      
      

