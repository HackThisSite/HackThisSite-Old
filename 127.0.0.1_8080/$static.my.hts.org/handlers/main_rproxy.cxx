// ============================================================================
// Handler C script for the G-WAN Web Application Server (http://trustleap.ch/)
// ----------------------------------------------------------------------------
// main_rproxy.c: this Handler sample uses G-WAN as a front-end which forwards
//                *some* HTTP requests to another application server.
//
// As a client: G-WAN redirects specific HTTP requests to another server (such
//         Apache+PHP, GlassFish+Java, IIS+C#/.Net, etc.) and it then forwards
//         the backend server reply to the client.
//         This is done by the HDL_BEFORE_PARSE G-WAN Handler state.
//         The 'WORKER_EXT' string defines which HTTP requests are redirected.
//
// In a real-life handler, you probably would use a permanent connection
// to the backend server rather than a new connection for each request.
//
// Also we could use more than one single backend server, and balance the load 
// among backend servers by using hashed IP address values (round-robin, etc.)
//
// This sample is just validating the concept.
// ============================================================================
#include "gwan.h" // G-WAN exported functions
// ----------------------------------------------------------------------------
#define WORKER_HOST       "127.0.0.1"    // adjust as needed (the Proxy server)
#define WORKER_PORT       80
#define WORKER_EXT        ".php"         // the contents we redirect to CSGI
#define WORKER_TIMEOUT    (10*1000)      // 10 seconds
#define WORKER_MAX_SIZE   (10*1024*1024) // 10 MB
// ----------------------------------------------------------------------------
// init() will initialize your data structures, load your files, etc.
// ----------------------------------------------------------------------------
// init() should return -1 if failure (to allocate memory for example)
int init(int argc, char *argv[])
{
   // define which handler states we want to be notified in main():
   // enum HANDLER_ACT { 
   //  HDL_INIT = 0, 
   //  HDL_AFTER_ACCEPT, // just after accept (only client IP address setup)
   //  HDL_AFTER_READ,   // each time a read was done until HTTP request OK
   //  HDL_BEFORE_PARSE, // HTTP verb/URI validated but HTTP headers are not 
   //  HDL_AFTER_PARSE,  // HTTP headers validated, ready to build reply
   //  HDL_BEFORE_WRITE, // after a reply was built, but before it is sent
   //  HDL_HTTP_ERRORS,  // when G-WAN is going to reply with an HTTP error
   //  HDL_CLEANUP };
   u32 *states = get_env(argv, US_HANDLER_STATES, &states);
   *states = 1 << HDL_AFTER_PARSE;
   return 0;
}
// ----------------------------------------------------------------------------
// clean() will free any allocated memory and possibly log summarized stats
// ----------------------------------------------------------------------------
void clean(int argc, char *argv[])
{}
// ============================================================================
// main() does the job for all the connection states
// (see 'HTTP_Env' in gwan.h for all the values you can fetch with get_env())
// ----------------------------------------------------------------------------
static const char *HTTP_Methods[] = {
   "BAD", "GET", "HEAD", "POST", "PUT", "DELETE", "OPTIONS" };

static const char *HTTP_Types[] = {
   "BAD", "URLENCODED", "MULTIPART", "OCTETSTREAM" };

// ----------------------------------------------------------------------------
// a stripped-down* version of itoa() - [*]: no checks, no ending-null
// ----------------------------------------------------------------------------
inline char *p u32toa(u8 *p, u32 v)
{
   do *p-- = '0' + (v % 10), v /= 10; while(v);
   return p;
}
// ----------------------------------------------------------------------------
// convert character 'c' into a lowercase character
// ----------------------------------------------------------------------------
inline u32 to_lower(u32 c) 
{
   return((u32)(c - 'A') < 26u) ? c + ('a' - 'A') : c;
}
// ----------------------------------------------------------------------------
// case-insensitive sub-string search 
// ----------------------------------------------------------------------------
char *stristr(char *str1, char *str2)
{
   if(!*str2) return(str1);
   while(*str1)
   {
      char *s1 = str1;
      char *s2 = str2;
      while(*s1 && !(to_lower(*s1) - to_lower(*s2)))
         s1++, s2++;

      if(!*s2) return(str1);
      str1++;
   }
   return(0);
}
// ----------------------------------------------------------------------------
// check that string begins with the given substr (case-insensitive comparison)
// return ptr of end-of-match if a match is found, 0 otherwise
// ----------------------------------------------------------------------------
char *striradix(char *str1, char *str2)
{
   if(str1)
   while(*str2)
   {
      if(!*str1 || (to_lower(*str1++) - to_lower(*str2++)))
	      return 0;
   }
   return str1;
}
// ----------------------------------------------------------------------------
// main
// ----------------------------------------------------------------------------
int main(int argc, char *argv[])
{
   // HDL_HTTP_ERRORS return values:
   //   0: Close the client connection
   //   2: Send a server reply based on a custom reply buffer
   // 255: Continue (send a reply based on the request HTTP code)
   if((long)argv[0] != HDL_AFTER_PARSE)
      return 255;
      
   // -------------------------------------------------------------------------
   // check if this request must be forwarded
   // -------------------------------------------------------------------------
   char *request = get_env(argv, REQUEST, &request); // "GET / HTTP/1.1\r\n..."
   if(!request)
      return 255; // continue G-WAN's default execution path

   if(!stristr(request, WORKER_EXT))
      return 255; // continue G-WAN's default execution path
   
   // -------------------------------------------------------------------------
   // connect to the backend server
   // -------------------------------------------------------------------------
   // the buffer where we will store any answer sent by the backend server
   xbuf_t *reply = get_reply(argv);
do
{  char     line[1024];
   s32      sock, len, code = 0;
   u32      cl = 0;

   sock = socket(AF_INET, SOCK_STREAM, 0);
   if(sock < 0)
      break;
   if(mstimeout)
      timeout(sock, mstimeout!=INFINITE?mstimeout<<1:INFINITE);
   if(!connect(sock, WORKER_HOST, WORKER_PORT, WORKER_TIMEOUT))
      break;

   // -------------------------------------------------------------------------
   // send the (non-HTTP) query 'p' to the backend server
   // -------------------------------------------------------------------------
   write (sock, request, strlen(request));
   xbuf_free(&request);

   // -------------------------------------------------------------------------
   // get the server reply, something like: "HTTP/1.1 200 OK\r\n"
   // -------------------------------------------------------------------------
   len = readline(sock, '\n', line, sizeof(line)-1);
   if(len > 0)
      xbuf_ncat(&reply, line, len);
   else
      break; // no reply! (connection closed)

   // -------------------------------------------------------------------------
   // get the HTTP status code
   // -------------------------------------------------------------------------
   code = atoi(line + sizeof("HTTP/1.1 ") - 1); // "HTTP/1.1 200 OK\r\n"

   // -------------------------------------------------------------------------
   // The rest of the HTTP headers should follow:
   // -------------------------------------------------------------------------
   //   "HTTP/1.1 200 OK\r\n" < just fetched
   //   "Date: %s\r\n"
   //   "Last-Modified: %s\r\n"
   //   "Content-type: text/html\r\n"
   //   "Content-Length: %u\r\n"   
   while((len = readline(sock, '\n', line, sizeof(line) - 1)) > 0)
   {
      // save the reply in our buffer
      xbuf_ncat(&reply, line, len);
      
      // test for end of headers, entity (if any) should follow
      // (some servers are sending "\r\n", others "\n", others " \n"...)
      if((len <= 4)
      &&(((line[0] == '\r') || (line[0] == '\n'))
      || ((line[1] == '\r') || (line[1] == '\n'))))
         break;

      // check if there is a "Content-Length:"
      if((p = striradix(line, "Content-Length:")))
      {
         line[len - 1] = 0;
         cl = atoi(p);
      }
   }

   // -------------------------------------------------------------------------
   // read any following HTTP entity that makes sense (not too big)
   // -------------------------------------------------------------------------
   if(cl && (cl < WORKER_MAX_SIZE) && (req_method != HTTP_HEAD))
   {
      // check if we have enough room in our buffer
      if(cl >= (reply.allocated - 1 - reply.len))
         xbuf_growto(&reply, cl); // grow our buffer (real size will be higher)

      // we don't use xbuf_ncat() to avoid data copies
      char *pos = reply.ptr + reply.len; // append data to buffer
      while((len = read(sock, pos, cl)) > 0)
      {
         pos += len;
         *pos = 0;
         reply.len += len;
      }
   }
}while(0);

   close(sock); // adjust as needed

   // -------------------------------------------------------------------------
   // set the HTTP reply code according to the backend server reply
   // -------------------------------------------------------------------------
   int *pHTTP_status = 0; get_env(argv, HTTP_CODE, &pHTTP_status);
   if(pHTTP_status)
      *pHTTP_status = code;

   // -------------------------------------------------------------------------
   // HDL_BEFORE_PARSE return values:
   //   0: Close the client connection
   //   2: Send a server reply based on a reply buffer/HTTP status code
   // 255: Continue (build a reply based on the client request)
   // -------------------------------------------------------------------------
   return(2); // that's NOT the HTTP status code here (we are a Handler)
}
// ============================================================================
// End of Source Code
// ============================================================================
