// ============================================================================
// C servlet sample for the G-WAN Web Application Server (http://trustleap.ch/)
// ----------------------------------------------------------------------------
// cache?.c: demonstrates how to add a servlet output into G-WAN's cache,
//           using an user-defined URI (not "/csp?cache")
// ============================================================================
// imported functions:
//   get_reply(): get a pointer on the 'reply' dynamic buffer from the server
//    xbuf_cat(): like strcat(), but it works in the specified dynamic buffer 
//    cacheadd(): puts the specified contents into G-WAN's cache
// ----------------------------------------------------------------------------
#include "gwan.h"    // G-WAN exported functions

int main(int argc, char *argv[])
{
   xbuf_t *reply = get_reply(argv);

   xbuf_cat(reply, "<h1>HELLO WORLD-0</h1>");

   static char szpath[] = "cache0.html";
   int never_expire = 0;
	if(cacheadd(argv, szpath, reply->ptr, reply->len, 200, never_expire) < 0)
   {
      printf("*** error\n");
      return 503;
   }   

   return 200; // return an HTTP code (200:'OK')
}
// ============================================================================
// End of Source Code
// ============================================================================
