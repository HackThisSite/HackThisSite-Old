// ============================================================================
// This is a Servlet sample for the G-WAN Web Server (http://www.trustleap.com)
// ----------------------------------------------------------------------------
// email.c: How to send an email
//
// When your ISP is blocking port 25, use "smtp.trustleap.com:587" to define
// another port number to use.
// ============================================================================
#include "gwan.h" // G-WAN exported functions
// ----------------------------------------------------------------------------
int main(int argc, char *argv[])
{
   char dst_address[] ="john@doe.com", subject[] ="test", text[] ="Just a test.";
   xbuf_t *reply = get_reply(argv);

   // Check the email address syntax
   if(!isvalidemailaddr(dst_address))
   {                    
      xbuf_cat(reply, "<p>Invalid Email address.<br>"
                       "Please try again.</p>");
      return 200; // return an HTTP code (200:'OK')
   }

   char *error = (char*)malloc(2048 + sizeof(text));
   if(!error)
      return 500; // return an HTTP code (500:'Internal error')

// if(sendemail("smtp.domain.com:587", // smtp server with custom port
   if(sendemail("smtp.domain.com",     // smtp server
                "pierre@domain.com",   // src. email address
                dst_address,           // dst. email address
                subject, text,         // email title / text
                "pierre@domain.com",   // username for login
                "secret",              // password for login
                error))
   {
      printf("sendemail() error:%s\n", error);
      xbuf_xcat(reply, "<p>Your Email could not be sent: %s.</p>", error);
      free(error);
      return 200; // return an HTTP code (200:'OK')
   }

   free(error);
   xbuf_cat(reply, "<p>Email sent.</p>");
   return 200; // return an HTTP code (200:'OK')
}
// ============================================================================
// End of Source Code
// ============================================================================
