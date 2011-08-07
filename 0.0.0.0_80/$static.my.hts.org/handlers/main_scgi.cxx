// ============================================================================
// Handler C script for the G-WAN Web Application Server (http://trustleap.ch/)
// ----------------------------------------------------------------------------
// main_scgi.c: this Handler implements the SCGI protocol (client side only*) 
//              to let G-WAN to delegate requests to other application servers.
//
// As a client: G-WAN redirects specific HTTP requests to another server (such
//         Apache+PHP, GlassFish+Java, IIS+C#/.Net, etc.) and then G-WAN sends
//         back the SCGI server HTTP reply to the client.
//         This is done by the 'HDL_AFTER_PARSE' G-WAN Handler state.
//         The 'SCGI_EXT' string defines which HTTP requests are redirected.
//
// [*]: supporting the SCGI server side is far less efficient than redirecting
//      HTTP requests from another instance of the G-WAN server (used as a 
//      reverse HTTP proxy) to the G-WAN instance which processes the requests.
//      See the 'main_rproxy.cxx' Handler C script sample to do this.
// ----------------------------------------------------------------------------
//  "SCGI: A Simple Common Gateway Interface alternative"
//  Neil Schemenauer <nas@python.ca>, 2008-06-23 [...]
//  http://python.ca/scgi.protocol.txt
// ----------------------------------------------------------------------------
//  The web server (a SCGI client) opens a connection and sends the
//  concatenation of the following strings:
//
//      "70:"
//          "CONTENT_LENGTH" <00> "27" <00>
//          "SCGI" <00> "1" <00>
//          "REQUEST_METHOD" <00> "POST" <00>
//          "REQUEST_URI" <00> "/deepthought" <00>
//      ","
//      "What is the answer to life?"
//
//  The SCGI server sends the following response:
//
//      "Status: 200 OK" <0d 0a>
//      "Content-Type: text/plain" <0d 0a>
//      "" <0d 0a>
//      "42"
//
//  The SCGI server closes the connection. 
// ============================================================================
#include "gwan.h" // G-WAN exported functions
// ----------------------------------------------------------------------------
// here we could use more than one single SCGI server, and balance the load 
// among SCGI servers by using hashed IP address values (round-robin, etc.)
// ----------------------------------------------------------------------------
#define SCGI_HOST       "127.0.0.1"    // adjust as needed (the SCGI server)
#define SCGI_PORT       80
#define SCGI_EXT        ".php"         // the contents we redirect to CSGI
#define SCGI_TIMEOUT    (10*1000)      // 10 seconds
#define SCGI_MAX_SIZE   (10*1024*1024) // 10 MB
// ----------------------------------------------------------------------------
// init() will initialize your data structures, load your files, etc.
// ----------------------------------------------------------------------------
// init() should return -1 if failure (to allocate memory for example)
int init(int argc, char *argv[]) { return 0; }
// ----------------------------------------------------------------------------
// clean() will free any allocated memory and possibly log summarized stats
// ----------------------------------------------------------------------------
void clean(int argc, char *argv[]) { }
// ============================================================================
// main() does the job for all the connection states below:
// ----------------------------------------------------------------------------
// Tip: see HTTP_Env in gwan.h for all the values you can fetch with get_env()

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
   if(!*str2) return str1;
   while(*str1)
   {
      char *s1 = str1;
      char *s2 = str2;
      while(*s1 && !(to_lower(*s1) - to_lower(*s2)))
         s1++, s2++;

      if(!*s2) return str1;
      str1++;
   }
   return 0;
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
   // -------------------------------------------------------------------------
   // HDL_AFTER_PARSE return values:
   //   0: Close the client connection
   //   2: Send a server reply based on a reply buffer/HTTP status code
   // 255: Continue (build a reply based on the client request)
   // -------------------------------------------------------------------------
   if((long)argv[0] != HDL_AFTER_PARSE) // the state we want to process
      return 255;                      // continue G-WAN's default execution
   
   // -------------------------------------------------------------------------
   // before anything else, check if this request must be redirected
   // -------------------------------------------------------------------------
   char *request = get_env(argv, REQUEST,      &request); // "GET / HTTP/1.1\r\n..."
   char *query   = get_env(argv, QUERY_STRING, &query);   // part after '?'
   if(!request || !*request)
      return 255; // continue G-WAN's default execution path

   // check if the request contains the SCGI_EXT (".php") sub-string
   if(query && *query)
   {
      if(!striradix(query - sizeof(SCGI_EXT) - 1, SCGI_EXT))
         return 255; // continue G-WAN's default execution path
   }
   else // no '?' in the request, search the whole request...
   {
      if(!stristr(request, SCGI_EXT))
         return 255; // continue G-WAN's default execution path
   }
   
   // -------------------------------------------------------------------------
   // get more useful 'environment' variables for the SCGI server
   // -------------------------------------------------------------------------
   // (the whole list of available values is in gwan/include/gwan.h)
   
   // NOTE: this would be faster by using the new http_t structure
   char *www_root    = get_env(argv, WWW_ROOT,      &www_root);
   char *remote_addr = get_env(argv, REMOTE_ADDR,   &remote_addr);
   char *entity      = get_env(argv, ENTITY,        &entity);
   
   u32 requ_method = get_env(argv, REQUEST_METHOD, 0);
   u32 remote_port = get_env(argv, REMOTE_PORT, 0);
   u32 content_len = get_env(argv, CONTENT_LENGTH, 0);
   u32 contenttype = get_env(argv, CONTENT_TYPE, 0);

   // -------------------------------------------------------------------------
   // build the SCGI 'netstring' request
   // -------------------------------------------------------------------------
   xbuf_t request; 
   xbuf_reset(&request);
   xbuf_xcat (&request,
             "         :" // leave room for the 'netstring' length 
             "CONTENT_LENGTH\n%u\n"
             "SCGI\n1\n"
             "REMOTE_ADDR\n%s\n"
             "REMOTE_PORT\n%u\n"
             "REQUEST_METHOD\n%s\n"
             "REQUEST_URI\n%s\n"
             "QUERY_STRING\n%s\n"
             "CONTENT_TYPE\n%u\n"
             "DOCUMENT_ROOT\n%s\n",
             content_len,
             remote_addr,
             remote_port,
             HTTP_methods[requ_method],
             request,
             query,
             HTTP_types[contenttype],
             www_root);
             
   // now we know it, insert the 'netstring' length (we will send from 'p')
   char *p = u32toa(request.ptr + 9, request.len - 10);

   // append the ending ",[entity]" (if any...)
   xbuf_xcat(&request, ",%s", entity);

   // now all formatting is done, replace '\n' characters by '\0'
   {
      char *q = request.ptr;      int   i = request.len;
      while(i--)
         if(q[i] == '\n') q[i] = '\0';
   }
   
   // -------------------------------------------------------------------------
   // connect to the SCGI server
   // -------------------------------------------------------------------------
   // the buffer where we will store any SCGI answer
   xbuf_t reply; get_reply(argv, &reply);
do
{  char     line[1024];
   s32      sock, len, code = 0;
   u32      cl = 0;

   sock = socket(AF_INET, SOCK_STREAM, 0);
   if(sock < 0)
      break;
   if(mstimeout)
      timeout(sock, mstimeout!=INFINITE?mstimeout<<1:INFINITE);
   if(!connect(sock, SCGI_HOST, SCGI_PORT, SCGI_TIMEOUT))
      break;

   // -------------------------------------------------------------------------
   // send the (non-HTTP) query 'p' to the SCGI backend server
   // -------------------------------------------------------------------------
   write (sock, p, request.len - (request.ptr - p));
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
   if(cl && (cl < SCGI_MAX_SIZE) && (req_method != HTTP_HEAD))
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

   close(sock); // the specs say: "The SCGI server closes the connection"

   // -------------------------------------------------------------------------
   // set the HTTP reply code according to the SCGI server reply
   // -------------------------------------------------------------------------
   int *pHTTP_status = 0; get_env(argv, HTTP_CODE, &pHTTP_status);
   if(pHTTP_status)
      *pHTTP_status = code;

   set_reply(argv, &reply); 
   // -------------------------------------------------------------------------
   // HDL_AFTER_PARSE return values:
   //   0: Close the client connection
   //   2: Send a server reply based on a reply buffer/HTTP status code
   // 255: Continue (build a reply based on the client request)
   // -------------------------------------------------------------------------
   return 2; // that's NOT the HTTP status code here (we are a Handler)
}
// ============================================================================
// End of Source Code
// ============================================================================
