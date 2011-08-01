// ============================================================================
// C servlet sample for the G-WAN Web Application Server (http://trustleap.ch/)
// ----------------------------------------------------------------------------
// hello.c: just used with ab (ApacheBench) to benchmark a minimalist servlet
// ============================================================================
// imported functions:
//   get_reply(): get a pointer on the 'reply' dynamic buffer from the server
//    xbuf_cat(): like strcat(), but it works in the specified dynamic buffer 
// ----------------------------------------------------------------------------
#include "gwan.h"    // G-WAN exported functions

int main(int argc, char *argv[])
{
   xbuf_t *reply = get_reply(argv);

   // encode string
   char buf[80], user[80] = "pierreg@example.com";
   int ret = s_snprintf(buf, 255, "%B", user);
   xbuf_xcat(reply, "<p>plain text %s to base64:<br> &nbsp; %s (len: %d)</p>",
             user, buf, ret);

   // decode string
   ret = s_snprintf(user, 255, "%-B", buf);
   xbuf_xcat(reply, "<p>base64 %s to plain text:<br> &nbsp; %s (len: %d)</p>",
             buf, user, ret);

   // encode binary data
   memset(user, 0, 8);
   ret = s_snprintf(buf, 255, "%8B", user);
   xbuf_xcat(reply, "<p>8 null bytes to base64:<br> &nbsp; %s (len: %d)</p>",
             buf, ret);

   // decode binary data
   memset(user, 'A', 16);
   ret = s_snprintf(user, 255, "%-B", buf);
   xbuf_xcat(reply, "<p>base64 %s to binary:<br> &nbsp; %s (len: %d)</p>",
             buf, 
             memcmp(user, "\0\0\0\0\0\0\0", 8) ? "mismatch" : "OK, match", 
             ret);

   // dump binary data            
   int i = 0;          
   while(i < 16)
      xbuf_xcat(reply, "user[%d] = %d<br>", i, user[i]), i++;

   return 200; // return an HTTP code (200:'OK')
}
// ============================================================================
// End of Source Code
// ============================================================================
