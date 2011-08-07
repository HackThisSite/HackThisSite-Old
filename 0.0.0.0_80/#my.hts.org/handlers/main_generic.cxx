// ============================================================================
// Handler C script for the G-WAN Web Application Server (http://trustleap.ch/)
// ----------------------------------------------------------------------------
// main.c: filter IP addresses, rewrite URLs, log custom messages, etc.
// ----------------------------------------------------------------------------
// The so_xxxxxx() BSD socket calls, like xbuf_frurl(), are provided by G-WAN 
// for convenience and are using asynchronous client calls, just like the 
// connnect() / send() / recv() system calls that you can use directly: G-WAN
// transparently transforms blocking system calls into asynchronous calls, for
// C scripts (servlets, handlers) as well as for (shared or static) libraries 
// linked with C scripts by "#pragma link".
// ============================================================================
#include "gwan.h"    // G-WAN exported functions
// ----------------------------------------------------------------------------
// init() will initialize your data structures, load your files, etc.
// ----------------------------------------------------------------------------
// init() should return -1 if failure (to allocate memory for example)
int init(int argc, char *argv[])
{
   // get the Handler persistent pointer to attach anything you need
   //char *data = 0; get_env(argv, US_HANDLER_DATA, &data);
   
   // attach structures, lists, sockets with a back-end/database server, 
   // file descriptiors for custom log files, etc.
   // struct 
   // { 
   //    kv_t *kv;
   //    FILE *log;
   //    char *blah;
   // } my_data;
   // data = (void*)calloc(1, sizeof(my_data));
   // if(!data)
   //    return -1;
   
   // get the Path for this Listener/VirtualHost
   //char *szPath = 0; get_env(argv, LOG_ROOT, &szPath);
   //if(szPath) // open a file to log custom data from main()
   //{
   //   char file[512];
   //   s_snprintf(file, sizeof(file)-1, "%s/my_log.txt", szPath);
   //   my_data->log = fopen(file, "ab");
   //}
   
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
   *states = (1L << HDL_AFTER_ACCEPT) 
           | (1L << HDL_AFTER_READ)
           | (1L << HDL_BEFORE_WRITE);
   
   return 0;
}
// ----------------------------------------------------------------------------
// clean() will free any allocated memory and possibly log summarized stats
// ----------------------------------------------------------------------------
void clean(int argc, char *argv[])
{
   // free any data attached to your persistence pointer
   //char *data = 0; get_env(argv, US_HANDLER_DATA, &data);

   // we could close our my_data->log custom file
   // fclose(my_data->log);

   //if(data)
   //   free(data);
}
// ----------------------------------------------------------------------------
// main() does the job for all the connection states below:
// (see 'HTTP_Env' in gwan.h for all the values you can fetch with get_env())
// ----------------------------------------------------------------------------

// those defines summarise the return codes for each state
#define RET_CLOSE_CONN   0 // all states
#define RET_BUILD_REPLY  1 // HDL_AFTER_ACCEPT only
#define RET_READ_MORE    1 // HDL_AFTER_READ only
#define RET_SEND_REPLY   2 // all states
#define RET_CONTINUE   255 // all states

int main(int argc, char *argv[])
{
   // get the Handler persistent pointer if we attached anything to it
   //struct *my_data = 0; get_env(argv, US_HANDLER_DATA, &my_data);

   // just helping you to know where you are:
   static char *states[] =
   {
      [HDL_INIT]         = "0:init()",      // not seen here: init()
      [HDL_AFTER_ACCEPT] = "1:AfterAccept",
      [HDL_AFTER_READ]   = "2:AfterRead",
      [HDL_BEFORE_PARSE] = "3:BeforeParse",
      [HDL_AFTER_PARSE]  = "4:AfterParse",
      [HDL_BEFORE_WRITE] = "5:BeforeWrite",
      [HDL_AFTER_WRITE]  = "6:AfterWrite",
      [HDL_HTTP_ERRORS]  = "7:HTTP_Errors",
      [HDL_CLEANUP]      = "8:clean()",   // not seen here: clean()
      ""
   };
   long state = (long)argv[0];
   printf("Handler state:%u:%s\n", state, states[state]);
   
   switch(state)
   {
      // ----------------------------------------------------------------------
      // AFTER_ACCEPT return values:
      //   0: Close the client connection
      //   1: Build a reply based on a custom request buffer/HTTP status code
      //   2: Send a server reply based on a reply buffer/HTTP status code
      // 255: Continue (wait for read() to fetch data sent by client)
      case HDL_AFTER_ACCEPT:
      {
         char *szIP = get_env(argv, REMOTE_ADDR, &szIP);
         
         // we don't want this user to touch our server
         if(!szIP || !strcmp(szIP, "1.2.3.4"))
            return 0; // 0: Close the client connection

         // we want this other user to be redirected to another server
         if(!strcmp(szIP, "5.6.7.8"))
         {
            char szURI[] = "http://another-place.org";
            xbuf_t *reply = get_reply(argv);
            xbuf_xcat(&reply,
                      "<html><head><title>Redirect</title></head>"
                      "<body>Click <a href=\"%s\">here</a>.</body></html>",
     					    szURI);

            // set the HTTP reply code accordingly
            int *pHTTP_status = get_env(argv, HTTP_CODE, &pHTTP_status);
            if(pHTTP_status)
               *pHTTP_status = 301; // 301:'moved permanently'
               
            // 2: Send a server reply based on a reply buffer/HTTP status code
            return 2;
         }
      }
      break;
      // ----------------------------------------------------------------------
      // AFTER_READ return values:
      //   0: Close the client connection
      //   1: Read more data from client
      //   2: Send a server reply based on a reply buffer/HTTP status code
      // 255: Continue (read more if request not complete or build reply based
      //                on the client request -or your altered version)
      case HDL_AFTER_READ:
      {
         //char *szRequest = get_env(argv, REQUEST, &szRequest);
         // do something with the request (re-write URL? do it in-place)
      }
      break;
      // ----------------------------------------------------------------------
      // BEFORE_PARSE return values:
      //   0: Close the client connection
      //   2: Send a server reply based on a reply buffer/HTTP status code
      // 255: Continue (parse the request and its HTTP Headers)
      case HDL_BEFORE_PARSE:
      {
         // here we could bypass HTTP and do something else
      }
      break;
      // ----------------------------------------------------------------------
      // AFTER_PARSE return values:
      //   0: Close the client connection
      //   2: Send a server reply based on a reply buffer/HTTP status code
      // 255: Continue (build a reply)
      case HDL_AFTER_PARSE:
      {
         // here we can take action based on HTTP Headers, to alter the 
         // request or bypass G-WAN's default behavior
      }
      break;
      // ----------------------------------------------------------------------
      // HTTP_ERRORS return values:
      //   0: Close the client connection
      //   2: Send a server reply based on a custom reply buffer
      // 255: Continue (send a reply based on the request HTTP code)
      case HDL_HTTP_ERRORS:
      {
         // here we could use our my_data->log file to log custom events
         // char string[256];
         // s_snprintf(string, sizeof(string)-1, "whatever", x,y,z);
         // fputs(string, my_data->log);
      }
      break;
      // ----------------------------------------------------------------------
      // BEFORE_WRITE return values:
      //   0: Close the client connection
      // 255: Continue (send a server reply based on a reply buffer/HTTP code)
      case HDL_BEFORE_WRITE:
      {
         // here we could use our my_data->log file to log custom events
         // char string[256];
         // s_snprintf(string, sizeof(string)-1, "whatever", x,y,z);
         // fputs(string, my_data->log);
      }
      break;
      // ----------------------------------------------------------------------
      // AFTER_WRITE return values:
      //   0: Close the client connection
      // 255: Continue (close or keep-alive or continue sending a long reply)
      case HDL_AFTER_WRITE:
      {
         // added per the request of someone
      }
      break;
   }
   return(255); // continue G-WAN's default execution path
}
// ============================================================================
// End of Source Code
// ============================================================================
