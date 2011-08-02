// ============================================================================
// C servlet sample for the G-WAN Web Application Server (http://trustleap.ch/)
// ----------------------------------------------------------------------------
// argv.c: how to list *all* URL parameters (may be useful in RESTFUL services)
//         without calling get_arg("name=", &pName, argc, argv) like in loan.c.
// ============================================================================
// imported functions:
//   get_reply(): get a pointer on the 'reply' dynamic buffer from the server
//   xbuf_xcat(): like sprintf(), but it works in the specified dynamic buffer 
// ----------------------------------------------------------------------------
#include "gwan.h" // G-WAN exported functions

int main(int argc, char *argv[])
{
   xbuf_t *reply = get_reply(argv);

   xbuf_xcat(reply, "<h3>main()'s argv[0-%d] listed:</h3>", 
             argc ? argc - 1 : 0);

   u32 i = 0;
   while(i < argc)
   {
      xbuf_xcat(reply, "argv[%u] '%s'<br>", i, argv[i]);
      i++;
   }
   
   return 200;
}
// ============================================================================
// End of Source Code
// ============================================================================
