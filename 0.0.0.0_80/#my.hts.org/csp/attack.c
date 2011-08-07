// ============================================================================
// C servlet sample for the G-WAN Web Application Server (http://trustleap.ch/)
// ----------------------------------------------------------------------------
// attack.c: send malicious URIs (from 'attack.txt' file) to a Web Server
//
//           It's always better to do it yourself rather than waiting others
//           to do it for you. 
//
//           Now you have an easy way to test your servlets (by just editing
//           the "attack.txt" file).
// ----------------------------------------------------------------------------
// attack.c should be executed by G-WAN to target ANOTHER running Web server 
// instance (G-WAN, Apache, Nginx, Lighttpd, etc.)
// ----------------------------------------------------------------------------
// Note that xbuf_frurl() is using asynchronous client calls, just like the 
// connect() / send() / recv() system calls that you can use directly: G-WAN
// transparently transforms blocking system calls into asynchronous calls, for
// C scripts (servlets, handlers) as well as for (shared or static) libraries 
// linked with C scripts by "#pragma link".
// ============================================================================
#include "gwan.h" // G-WAN exported functions

// Title of our HTML page
static char title[]="Attacking your own web server";

// Top of our HTML page
static char top[]="<!DOCTYPE HTML>"
     "<html lang=\"en\"><head><title>%s</title><meta http-equiv"
     "=\"Content-Type\" content=\"text/html; charset=utf-8\">"
//   "<link href=\"/imgs/style.css\" rel=\"stylesheet\" type=\"text/css\">"
     "</head><body><h1>%s</h1>The web server replied:<br><br>";

// ----------------------------------------------------------------------------
// imported functions:
//   get_reply(): get a pointer on the 'reply' dynamic buffer from the server
//   xbuf_init(): called after xbuf_t has been declared, to initialize struct
// xbuf_frfile(): load a file, and store it in a dynamic buffer
//  xbuf_frurl(): make an Http request, and store results in a dynamic buffer
//   xbuf_ncat(): like strncat(), but in the specified dynamic buffer 
//   xbuf_xcat(): formatted strcat() (a la printf) in a given dynamic buffer 
//   xbuf_free(): release the memory allocated for a dynamic buffer
// ----------------------------------------------------------------------------
// like atoi() -but here you can *filter* input strings ("%9d" here)
// ----------------------------------------------------------------------------
int atoint(char *p)
{
	u32 sign = 0;
   while(*p == ' ') p++; // pass space characters
   switch(*p)
   {
      case '-': sign = 1;
      case '+': p++;
   }
   
   int d = 0;
   u32 v, i = 9; // i:the maximum integral part we want to scan
   while(i && (v = *p) && v >= '0' && v <= '9') // integer part
      p++, d = 10 * d + (v - '0'), i--;
	
	return sign ? -d : d;   
}
// ----------------------------------------------------------------------------
int main(int argc, char *argv[])
{
   xbuf_t *reply = get_reply(argv);
   
   u64 start = getus(); // elapsed micro-seconds (1 us = 1,000 milliseconds)
   
   // -------------------------------------------------------------------------
   // format the top of our HTML page with a title
   // -------------------------------------------------------------------------
   xbuf_xcat(reply, top, title, title);

   // -------------------------------------------------------------------------
   // get query parameters, if any
   // -------------------------------------------------------------------------
   char *pHost = "192.168.200.88";
   int port = 80;
   if(argc > 0)
   {
      // http://10.10.2.4:8080/csp?attack&host=127.0.0.1:80
      get_arg("host=", &pHost, argc, argv);
      if(pHost && *pHost)
      {
         char *p = (char*)strchr(pHost, ':');
         if(p)
         {
            *p++ = 0;         // close pHost string
            port = atoint(p); // get port number
         }
      }
   }
   //printf("argc:%d, pHost:%s\n", argc, pHost);

   // -------------------------------------------------------------------------
   // load our bad URIs list
   // -------------------------------------------------------------------------
   char *csp_root = (char*)get_env(argv, CSP_ROOT, 0); // the ".../csp/" path
   char szfile[512];
   s_snprintf(szfile, sizeof(szfile)-1, "%s/attack.txt", csp_root);
   xbuf_ctx list;
   xbuf_init(&list);
   xbuf_frfile(&list, szfile);
   if(list.len)
   {
      int code = 0, codcut = 0, cod2xx = 0, cod3xx = 0, cod4xx = 0, cod5xx = 0;
      u32 i = 1;
      xbuf_ctx buf; xbuf_init(&buf);
      char uri[512]; // loop to send all URIs
      while(xbuf_getln(&list, uri, sizeof(uri) - 1) > 0 && i < 100)
      {
         // send the Http request (with a timeout in milliseconds)
         xbuf_empty(&buf);
         code = xbuf_frurl(&buf, pHost, port, HTTP_HEAD, uri, 1000, 0);
         if(code ==  0) codcut++; else
         if(code < 300) cod2xx++; else
         if(code < 400) cod3xx++; else
         if(code < 500) cod4xx++; else
         if(code < 600) cod5xx++;
         if(code)
         {
            if(code < 300) // server replied with normal HTML contents
            {
               while(xbuf_repl(&buf, "\r", "<br>"))
                  ;
               xbuf_xcat(reply, 
                         "%03u] Reply: <b style=\"color:#2020f0\">%03d</b>"
                         " to &quot;%s&quot;<br>"
                         "<span style=\"color:#777\">%s</span>", 
                         i, code, uri, buf.ptr);
            }
            else // server returned an HTTP error
               xbuf_xcat(reply, 
                         "%03u] Reply: <b%s>%03d</b> to &quot;%s&quot;<br>", 
                         i, 
                         code == 400 ? " style=\"color:#f02020\"" : "",
                         code, 
                         uri);
         }
         else // could not connect, or connection hard-closed by server
            xbuf_xcat(reply, "%03u] Reply: <b style=\"color:#2020f0\">000</b>"
                      " to &quot;%s&quot;<br>", 
                      i, uri);
         i++;
         
      }
      xbuf_free(&buf);
      xbuf_free(&list);

      // ---- display results and close our HTML page
      xbuf_xcat(reply, "<br>"
                "hard-close : %u %s<br>"
                "2xx replies: %u<br>"
                "3xx replies: %u<br>"
                "4xx replies: %u<br>"
                "5xx replies: %u<br><br>"
                "Test done in %.2F ms."
                "</body></html>",
                codcut, 
                (codcut == --i) ? "(<b>Could not reach server)</b>" : "",
                cod2xx, cod3xx, cod4xx, cod5xx,
                (double)(getus() - start)/1000.);
   }
   else // no URI list file
   {
      xbuf_xcat(reply, "The 'attack.txt' URI file was not found<br>"
                        "</body></html>");
   }

   return 200; // return an HTTP code (200:'OK')
}
// ============================================================================
// End of Source Code
// ============================================================================
