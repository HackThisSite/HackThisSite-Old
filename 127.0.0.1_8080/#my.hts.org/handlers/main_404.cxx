// ============================================================================
// Handler C script for the G-WAN Web Application Server (http://trustleap.ch/)
// ----------------------------------------------------------------------------
// main.c: handling HTTP 404 Errors (3 methods are provided)
//
//         You could use custom HTTP Error pages, or serve JSON replies cached
//         in your own G-WAN KV store instead of using "/index.html" for all
//         the requests which trigger a 404 error (in that latter case, you 
//         would use HDL_AFTER_PARSE instead of HDL_HTTP_ERRORS, or act from
//         within a C servlet instead of from a handler).
// ============================================================================
#include "gwan.h"    // G-WAN exported functions
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
   *states = 1 << HDL_HTTP_ERRORS;
   return 0;
}
// ----------------------------------------------------------------------------
// clean() will free any allocated memory and possibly log summarized stats
// ----------------------------------------------------------------------------
void clean(int argc, char *argv[])
{}
// ----------------------------------------------------------------------------
// main() does the job for all the connection states below:
// (see 'HTTP_Env' in gwan.h for all the values you can fetch with get_env())
// ----------------------------------------------------------------------------
int main(int argc, char *argv[])
{
   // HDL_HTTP_ERRORS return values:
   //   0: Close the client connection
   //   2: Send a server reply based on a custom reply buffer
   // 255: Continue (send a reply based on the request HTTP code)
   if((long)argv[0] != HDL_HTTP_ERRORS)
      return 255;
      
   // get the HTTP reply code
   int *pHTTP_status = get_env(argv, HTTP_CODE, &pHTTP_status);
   if(pHTTP_status && *pHTTP_status == 404) // is it a 404 error?
   {
      // ----------------------------------------------------------------------
      // option 1: we trigger a redirection (at the cost of a new request)
      /* ----------------------------------------------------------------------
      *pHTTP_status = 301; // setup 301:'moved permanently'
      http_t *http = get_env(argv, HTTP_HEADERS, 0);
      int len = 0;         
      static const char buf[] = 
         "HTTP/1.1 301 Moved Permanently\r\n"
         "Location: http://%s:%u/\r\n" // hostname:port
         "Content-Length: %n%d\r\n" // HTML body length
         "Connection: keep-alive\r\n\r\n"
         "<html><head><title>Redirect</title></head>"
         "<body>Click <a href=\"/index.html\">here</a>.</body></html>"
         "     "; // extra padding to compensate for HTML body length
      xbuf_xcat(get_reply(argv), buf,
                http->h_host, http->h_port,
                &len, sizeof(buf) - (len + 1)); 
      return 2; // 2: Send a server reply          */
      // ----------------------------------------------------------------------
      // option 2: we serve a cached entry (at the cost of a data copy)
      /* ----------------------------------------------------------------------
      u32 mod = 0, len = 0;         
      char *c = cacheget(argv, "index.html", &len, pHTTP_status, &mod, 0);
      if(c)
      {   
         char *date = get_env(argv, SERVER_DATE, 0);
         char szmodified[32];
         static const char buf[] = 
            "HTTP/1.1 %s\r\n"
            "Date: %s\r\n"
            "Last-Modified: %s\r\n"
            "Content-type: text/html\r\n"
            "Content-Length: %u\r\n" // HTML body length
            "Connection: keep-alive\r\n\r\n"
            "%.*s"; // entry is not zero-terminated, just copy 'len' bytes
         xbuf_xcat(get_reply(argv), buf,
                   http_status(*pHTTP_status), // "200 OK" here
                   date,                       // current HTTP time
                   time2rfc(mod, szmodified),  // file HTTP time
                   len,                        // file length
                   len, c);                    // file body ('len' bytes)
         return 2; // 2: Send a server reply
      }*/
      // ----------------------------------------------------------------------
      // option 3: we serve a cached entry (without any data copy)
      // ----------------------------------------------------------------------
      u32 mod = 0, len = 0;         
      char *c = cacheget(argv, "index.html", &len, pHTTP_status, &mod, 0);
      if(c)
      {   
         char *date = get_env(argv, SERVER_DATE, 0);
         char szmodified[32];
         static const char buf[] = 
            "HTTP/1.1 %s\r\n"
            "Date: %s\r\n"
            "Last-Modified: %s\r\n"
            "Content-type: text/html\r\n"
            "Content-Length: %u\r\n" // HTML body length
            "Connection: keep-alive\r\n\r\n";
         build_headers(argv, buf,
                   http_status(*pHTTP_status), // "200 OK" here
                   date,                       // current HTTP time
                   time2rfc(mod, szmodified),  // file HTTP time
                   len);                       // file length
                   
         set_reply(argv, c, len, *pHTTP_status); // no copy
         return 2; // 2: Send a server reply
      }
   }
   return 255; // continue G-WAN's default execution path
}
// ============================================================================
// End of Source Code
// ============================================================================

