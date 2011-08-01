// ============================================================================
// This is a Servlet sample for the G-WAN Web Server (http://www.trustleap.com)
// ----------------------------------------------------------------------------
// noheaders.c: build a complete HTTP reply -without any response header
//              (this useful to send a raw JSON reply for example)
//
//              Returning an INVALID HTTP status code in the 1-99 range 
//              (inclusive) will prevent G-WAN from injecting the missing
//              response HTTP Headers.
// ----------------------------------------------------------------------------
#include "gwan.h" // G-WAN exported functions

// ----------------------------------------------------------------------------
// imported functions:
//   get_reply(): get a pointer on the 'reply' dynamic buffer from the server
//    xbuf_cat(): formatted strcat() (a la printf) in a given dynamic buffer 
// ----------------------------------------------------------------------------
int main(int argc, char *argv[])
{
   xbuf_t *reply = get_reply(argv);

   // -------------------------------------------------------------------------
   // append your data to the reply buffer
   // -------------------------------------------------------------------------
   char szJSON[] = "{ \"user\" : \"Pierre\" }";
   xbuf_cat(reply, szJSON);

   // -------------------------------------------------------------------------
   // return an *INVALID* HTTP code (1:'unknown')
   // -------------------------------------------------------------------------
   return 1;
}
// ============================================================================
// End of Source Code
// ============================================================================
