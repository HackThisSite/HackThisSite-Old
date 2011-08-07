// ============================================================================
// C servlet sample for the G-WAN Web Application Server (http://trustleap.ch/)
// ----------------------------------------------------------------------------
// dns.c: Blocking DNS lookup requests made asynchronous by G-WAN
//        (using the getaddrinfo() and gethostbyname_r() system calls)
//
// Output:
//
// gethostbyname_r() resolving: gwan.ch...
// IP address:192.168.200.88
//
// gethostbyname_r() resolving: gwan-is-fun.com...
// Can't resolve gwan-is-fun.com (1:Connection timed out)
//
// ============================================================================
// imported functions:
//   get_reply(): get a pointer on the 'reply' dynamic buffer from the server
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

//#define USE_GET_ADDRINFO // you can also test both
#define USE_GET_HOSTBYNAME

// ----------------------------------------------------------------------------
//#define HOST "192.168.200.88"    // available local host
#define PORT "8080"
#define HOST   "gwan.ch"         // available Internet host
#define HOST   "gwan-is-fun.com" // UNavailable domain: gethostname() fails
//#define PORT "80"
//#define PORT "65535"             // UNavailable host: connect() fails
// ----------------------------------------------------------------------------

int main(int argc, char *argv[])
{
   xbuf_t *reply = get_reply(argv);
   
#ifdef USE_GET_ADDRINFO   
   // -------------------------------------------------------------------------
   // getaddrinfo()
   // -------------------------------------------------------------------------
   {
      xbuf_cat(reply, "<br><br>getaddrinfo() resolving: " HOST "...<br>");
      int err = 0;
      struct addrinfo *host = 0,
             hints = {.ai_family   = AF_INET, // <- default (can be AF_INET6)
                      .ai_socktype = SOCK_STREAM, //  |
                      .ai_protocol = IPPROTO_TCP, //  V
                      .ai_addrlen  = sizeof(struct sockaddr_in),
                      .ai_canonname = 0,
                      .ai_next     = 0,
                      .ai_addr     = 0,
                      .ai_flags    = NI_NUMERICSERV };

      if(inet_addr(HOST) != INADDR_NONE)  // HOST is an IPv4 address?
         hints.ai_flags |= AI_NUMERICHOST; // save a DNS lookup
      
      err = getaddrinfo(HOST, PORT, &hints, &host); // let's "resolve"...
      if(err) // failure
      {
         xbuf_xcat(reply, "Can't resolve " HOST " (%d:%s)<br>", 
                   err, gai_strerror(err));
      }
      else
      {  
         u8 *p = &((struct sockaddr_in*)host->ai_addr)->sin_addr;
         xbuf_xcat(reply, "Resolved IP address:%u.%u.%u.%u<br>", 
                   p[0], p[1], p[2], p[3]);
      }
      freeaddrinfo(host); // hmm... this may cost quite a few cycles...
   }
#endif
   
#ifdef USE_GET_HOSTBYNAME   
   // -------------------------------------------------------------------------
   // gethostbyname_r()
   // -------------------------------------------------------------------------
   xbuf_cat(reply, "<br><br>gethostbyname_r() resolving: " HOST "...<br>");
   struct hostent host, *res = 0;
   char tmp[1024] = {0};
   int err = 0;
   if(gethostbyname_r(HOST, &host, tmp, sizeof(tmp), &res, &err) == 0
   && res) // success
   {
      const u8 *p = host.h_addr_list[0];
      xbuf_xcat(reply, "Resolved IP address:%u.%u.%u.%u<br>", 
                p[0], p[1], p[2], p[3]);
   }
   else
      xbuf_xcat(reply, "Can't resolve " HOST " (%d:%m)<br>", err);
#endif

   return 200; // return an HTTP code (200:'OK')
}
// ============================================================================
// End of Source Code
// ============================================================================

