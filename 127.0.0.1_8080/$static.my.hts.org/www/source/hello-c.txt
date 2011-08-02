// ============================================================================
// C servlet sample for the G-WAN Web Application Server (http://trustleap.ch/)
// ----------------------------------------------------------------------------
// hello.c: just used with ab (ApacheBench) to benchmark a minimalist servlet
// ============================================================================
// imported functions:
//   get_reply(): get a pointer on the 'reply' dynamic buffer from the server
//    xbuf_cat(): like strcat(), but it works in the specified dynamic buffer 
// ----------------------------------------------------------------------------
#include "gwan.h" // G-WAN exported functions

int main(int argc, char *argv[])
{
   xbuf_t *reply = get_reply(argv);

   xbuf_cat(reply, "<h1>HELLO WORLD</h1>");

   return 200; // return an HTTP code (200:'OK')
}
// ============================================================================
// End of Source Code
// ============================================================================
