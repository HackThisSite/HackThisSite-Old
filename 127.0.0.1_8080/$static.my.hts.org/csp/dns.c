// ============================================================================
// C servlet sample for the G-WAN Web Application Server (http://trustleap.ch/)
// ----------------------------------------------------------------------------
// dns.c: DNS lookup request using the getaddrinfo() and gethostbyname_r()
//        Linux system calls
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

// ----------------------------------------------------------------------------
#define HOST "not-gwan.com"   // UNavailable domain: gethostname() fails
//#define HOST   "gwan.ch"       // available Internet host
#define PORT   "80"
// ----------------------------------------------------------------------------

int main(int argc, char *argv[])
{
   xbuf_t *reply = get_reply(argv);
   int err = 0;
   
   // -------------------------------------------------------------------------
   /*/ getaddrinfo()
   // -------------------------------------------------------------------------
   printf("\ngetaddrinfo() resolving: " HOST "...\n");         
   xbuf_cat(reply, "<br>getaddrinfo() resolving: " HOST "...<br>");
   struct addrinfo *host = 0,
          hints = {.ai_family   = AF_INET, // <- default (can be AF_INET6)
                   .ai_socktype = SOCK_STREAM, //  |
                   .ai_protocol = IPPROTO_TCP, //  V
                   .ai_addrlen  = sizeof(struct sockaddr_in),
                   .ai_next     = 0,
                   .ai_addr     = 0,
                   .ai_flags    = NI_NUMERICSERV };

   if(inet_addr(HOST) != INADDR_NONE)  // HOST is an IPv4 address?
      hints.ai_flags |= AI_NUMERICHOST; // save a DNS lookup
   
   err = getaddrinfo(HOST, PORT, &hints, &host); // let's "resolve"...
   if(err) // failure
   {
      printf("Can't resolve " HOST " (%d:%s)\n", err, gai_strerror(err));
      xbuf_xcat(reply, "Can't resolve " HOST " (%d:%s)", err, gai_strerror(err));
   }
   else
   {
      printf("IP:%x\n", ((struct sockaddr_in*)host->ai_addr)->sin_addr.s_addr);
      xbuf_xcat(reply, "IP:%x, error (%d:%m)<br><br>", 
               ((struct sockaddr_in*)host->ai_addr)->sin_addr.s_addr, errno);
   }
   freeaddrinfo(host); */
   
   // -------------------------------------------------------------------------
   // gethostbyname_r()
   // -------------------------------------------------------------------------
   printf("\ngethostbyname_r() resolving: '" HOST "'...\n");         
   xbuf_cat(reply, "<br><br>gethostbyname_r() resolving: '" HOST "'...<br>");
   struct hostent pHost, *hp;
   char tmp[1024] = {0};
   err = 0;
   gethostbyname_r(HOST, &pHost, tmp, sizeof(tmp), &hp, &err);
   if(hp) // success
   {
      printf("IP:%x\n", *((u32*)pHost.h_addr_list[0]));
      xbuf_xcat(reply, "IP:%x, error (%d:%m)<br><br>", 
               *((u32*)pHost.h_addr_list[0]), errno);
   }
   else
   {
      printf("Can't resolve " HOST " (%d:%m)\n", errno);
      xbuf_xcat(reply, "Can't resolve " HOST " (%d:%m)", errno);
   }
   return 200; // return an HTTP code (200:'OK')
}
// ============================================================================
// End of Source Code
// ============================================================================

