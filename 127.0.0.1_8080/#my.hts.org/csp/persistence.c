// ============================================================================
// C servlet sample for the G-WAN Web Application Server (http://trustleap.ch/)
// ----------------------------------------------------------------------------
// persistence.c: using G-WAN's persistence pointers to attach data structures
//                that can be shared between G-WAN scripts, handlers, etc.
// ============================================================================
#include "gwan.h" // G-WAN exported functions
// ----------------------------------------------------------------------------
// Handler and VirtualHost persistence pointers (see gwan.h):
// US_HANDLER_DATA=200, // Listener-wide pointer 
// US_VHOST_DATA,       // Virtual-Host-wide pointer
// US_SERVER_DATA,      // G-WAN-wide global pointer (for maintenance script)
// ----------------------------------------------------------------------------
typedef struct 
{ 
   kv_t *kv;   // a Key-Value store
   char *blah; // a string
   int   val;  // a counter
} data_t;
// ----------------------------------------------------------------------------
int main(int argc, char *argv[])
{
   xbuf_t *reply = get_reply(argv);
   xbuf_cat(reply, "<h2>Using G-WAN's persistence Pointers</h2>");

   // get the Handler persistent pointer to attach anything you need
   // (that's safre to call it this way if its allocation can change,
   //  otherwise, you can assign its value to a static pointer)
 //data_t **data = get_env(argv, US_HANDLER_DATA, 0);
 //data_t **data = get_env(argv, US_VHOST_DATA, 0);
   data_t **data = get_env(argv, US_SERVER_DATA, 0);

   if(!*data) // first time: persistent pointer is uninitialized
   {
      *data = (data_t*)calloc(1, sizeof(data_t));
      if(!*data)
         return 500; // out of memory

      xbuf_cat(reply, "initialized data<br>");
   }
   
   // not thread-safe, just an example
   (*data)->val++;
   
   xbuf_xcat(reply, "Value: %d", (*data)->val);
   return 200; // return an HTTP code (200:'OK')
}
// ============================================================================
// End of Source Code
// ============================================================================
