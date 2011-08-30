// ============================================================================
// Handler C script for the G-WAN Web Application Server (http://trustleap.ch/)
// ----------------------------------------------------------------------------
// main.c: bypass HTTP parsing
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
   *states = 1 << HDL_AFTER_READ; // we assume "GET /hello" sent in one shot
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
   // AFTER_READ return values:
   //   0: Close the client connection
   //   1: Read more data from client
   //   2: Send a server reply based on a reply buffer/HTTP status code
   // 255: Continue (read more if request not complete or build reply based
   //                on the client request -or your altered version)
   if((long)argv[0] == HDL_AFTER_READ)
   {
      // that's like hello.c - just without the slow HTTP Headers parsing
      //
      // we could as well use HDL_BEFORE_PARSE which would have the HTTP
      // request be parsed (HTTP verb, request string) in order to query the
      // request string (see below) and reply "Hello World" only when asked:
      // char *szRequest = get_env(argv, REQUEST, &szRequest);
      //
      static const char buf[] = 
         "HTTP/1.1 200 OK\r\n"
         "Content-type: text/html\r\n"
         "Content-Length: 11\r\n" // HTML body length
         "Connection: keep-alive\r\n\r\n"
         "Hello World"; // HTML body
      xbuf_ncat(get_reply(argv), buf, sizeof(buf) - 1);
      
      // set the HTTP reply code
      int *pHTTP_status = get_env(argv, HTTP_CODE, &pHTTP_status);
      if(pHTTP_status)
         *pHTTP_status = 200; // 200:OK
      
      return 2; // 2: Send a server reply
   }
   return(255); // continue G-WAN's default execution path
}
// ============================================================================
// End of Source Code
// ============================================================================
