// ============================================================================
// C servlet sample for the G-WAN Web Application Server (http://trustleap.ch/)
// ----------------------------------------------------------------------------
// post.c: processing a binary POST request
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
#include <arpa/inet.h>
#include <netdb.h>
#include <netinet/in.h>
#include <sys/socket.h>
#define IP         "192.168.200.88" // change as needed
#define PORT       "8080"

#define ENTITY     "blah-blah-blah-blah"        // ASCII POST Entity
#define ENTITY_LEN "19"
/*
#define ENTITY     "[\x01\x10\x20\x30\x40\x50]" // BINARY POST Entity
#define ENTITY_LEN "8"
*/

int main(int argc, char *argv[])
{
   // -------------------------------------------------------------------------
   // GET request? Send a POST to this same servlet
   // -------------------------------------------------------------------------
   // see 'enum HTTP_Method' in gwan.h
   if(get_env(argv, REQUEST_METHOD, 0) == HTTP_GET)
   {
      int s = -1;
      do 
      {  struct hostent *hp = gethostbyname(IP);
         if(!hp)
            break;

         struct sockaddr_in host;
         memset((char*)&host,0, sizeof(host));
         memmove((char*)&host.sin_addr, hp->h_addr, hp->h_length);
         host.sin_family = hp->h_addrtype;
         host.sin_port   = htons((u16)atoi(PORT));

         s = socket(AF_INET, SOCK_STREAM, 0);
         if(s < 0)
           break;
           
         if(connect(s, (struct sockaddr*)&host, sizeof(host)) < 0) 
           break;
      
         char req[] = "POST /csp?post HTTP/1.1\r\n"
                      "Host: " IP ":" PORT "\r\n"
                      "User-Agent: G-WAN C script\r\n"
                      "Content-Length: " ENTITY_LEN "\r\n"
                      "Content-Type: application/octet-stream\r\n"
                      "Connection: close\r\n"
                      "\r\n";
         int len = sizeof(req) - 1;
         if(write(s, req, len) != len)
            break;

         len = sizeof(ENTITY) - 1;
         if(write(s, ENTITY, len) != len)
            break;

         char buf[4070] = {0};
         len = read(s, buf, sizeof(buf) - 1);
         if(len <= 0)
           break;

         shutdown(s, SHUT_WR); // clients MUST tell when they close
         close(s); // free memory used by fd
         
         xbuf_t *reply = get_reply(argv);
         xbuf_ncat(reply, buf, len);
         return 200; // return an HTTP code (200:'OK')

      } while(0);

      // breaks jump here
      if(s >= 0)
      {
         shutdown(s, SHUT_WR); // clients MUST tell when they close
         close(s); // free memory used by fd
      }
      return 503; // return an HTTP code (503:'Service Unavailable')
   }
   
   // -------------------------------------------------------------------------
   // POST request, process the entity sent by the client above
   // -------------------------------------------------------------------------
   // see 'enum HTTP_Type' in gwan.h:
   // TYPE_URLENCODED=1, TYPE_MULTIPART, TYPE_OCTETSTREAM
   static char *http_type[] = {
   "0", "URL-ENCODED", "MULTI-PART", "OCTET-STREAM", "4", "5", "6", "7" };

   // see 'enum ENC_Type' in gwan.h:
   // ENC_IDENTITY=0, ENC_GZIP=1, ENC_DEFLATE=2, ENC_COMPRESS=4, ENC_CHUNKED=8
   static char *enc_type[] = {
   "IDENTIY", "GZIP", "DEFLATE", "3", "COMPRESS", "4", "5", "6", "CHUNCKED" };

   // get the information about the POST entity
   char *entity = get_env(argv, REQ_ENTITY,       0);
   u32   length = get_env(argv, CONTENT_LENGTH,   0);
   u32   type   = get_env(argv, CONTENT_TYPE,     0);
   u32   coding = get_env(argv, CONTENT_ENCODING, 0);
   
   // for convenience, the POST Entity is also passed as the first argument:
   printf("argc:%d, argv[0]=%s, entity:%s\n", argc, argv[0], entity);

   xbuf_t *reply = get_reply(argv);
   xbuf_xcat(reply, 
             "<h1>The client has posted:</h1><br>"
             "<b>Length:</b> %U bytes<br>"
             "<b>Type :</b> %s<br>"
             "<b>Encoding:</b> %s<br>"
             "<b>Entity:</b> ",
             length,
             http_type[type & 7],
             enc_type[coding & 7]);
             
   if(entity[1] >= ' ') // crude test: ASCII entity?
   {
      xbuf_ncat(reply, entity, length);
      return 200; // return an HTTP code (200:'OK')
   }
   
   // convert binary data into ASCII characters
   static const u8 to_ascii[] = "0123456789abcdef";
   u8 tmp[80], *d = tmp, *s = entity;
   int len = length;
   while(len--)
   {
      *d++ = 'x';
      *d++ = to_ascii[(*s >> 4) & 15];
      *d++ = to_ascii[(*s++   ) & 15];
   }
   *d = 0; // close the string (useless here)
        
   xbuf_ncat(reply, tmp, length * 3);
   return 200; // return an HTTP code (200:'OK')
}
// ============================================================================
// End of Source Code
// ============================================================================
