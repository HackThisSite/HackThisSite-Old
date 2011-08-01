// ============================================================================
// C servlet sample for the G-WAN Web Application Server (http://trustleap.ch/)
// ----------------------------------------------------------------------------
// redirect.c: redirect a client to another URL
// ============================================================================
// imported functions:
//   get_reply(): get a pointer on the 'reply' dynamic buffer from the server
//   xbuf_xcat(): like sprintf(), but it works in the specified dynamic buffer 
// ----------------------------------------------------------------------------
#include "gwan.h" // G-WAN exported functions

int main(int argc, char *argv[])
{
   xbuf_t *reply = get_reply(argv);

   xbuf_cat(reply,
            "<html><head><title>Redirect</title></head>"
            "<body>Click <a href=\"new.html\">here</a>.</body></html>");

   return 301; // return an HTTP code (301:'Moved')
}
// ============================================================================
// End of Source Code
// ============================================================================
