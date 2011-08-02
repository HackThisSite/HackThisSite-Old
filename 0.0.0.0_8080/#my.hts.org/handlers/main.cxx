// ============================================================================
// Handler C script for the G-WAN Web Application Server (http://trustleap.ch/)
// ----------------------------------------------------------------------------
// main.c: filter IP addresses, rewrite URLs, log custom messages, etc.
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
   // struct {FILE *log;} my_data;
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
int main(int argc, char *argv[])
{
   // get the Handler persistent pointer if we attached anything to it
   //struct *my_data = 0; get_env(argv, US_HANDLER_DATA, &my_data);

   // just helping you to know where you are:
   char *states[] = {"", "after_accept", "after_read", "before_write", ""};
   u32   state = (u32)argv[0];
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
         char *szIP = 0; get_env(argv, REMOTE_ADDR, &szIP);
         
         // we don't want this user to touch our server
         if(!szIP || !strcmp(szIP, "1.2.3.4"))
            return 0; // 0: Close the client connection

         // we want this user to be redirected to another server
         if(!strcmp(szIP, "5.6.7.8"))
         {
            char szURI[]="http://another-place.org";
            xbuf_ctx reply; 
            get_reply (argv, &reply);
            xbuf_empty(&reply);
            xbuf_xcat(&reply,
                      "<html><head><title>Redirect</title></head>"
                      "<body>Click <a href=\"%s\">here</a>.</body></html>",
     					    szURI);

            set_reply(argv, &reply);

            // set the HTTP reply code accordingly
            int *pHTTP_status = 0; get_env(argv, HTTP_CODE, &pHTTP_status);
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
         //char *szRequest = 0; get_env(argv, REQUEST, &szRequest);
         // do something with the request (re-write URL? do it in-place)
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
   }
   return(255); // continue G-WAN's default execution path
}
// ============================================================================
// End of Source Code
// ============================================================================
