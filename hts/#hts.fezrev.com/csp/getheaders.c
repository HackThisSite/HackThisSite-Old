// ============================================================================
// C servlet sample for the G-WAN Web Application Server (http://trustleap.ch/)
// ----------------------------------------------------------------------------
// getheaders.c: dump the HTTP headers from a given URL and Web Server
//
//            May be handy to see what a web server is practicing. Here is the
//            output we got from apache.org:
//
//            HTTP/1.1 200 OK
//            Date: Wed, 13 May 2009 14:20:58 GMT
//            Server: Apache/2.2.9 (Unix)
//            Last-Modified: Thu, 16 Apr 2009 04:33:51 GMT
//            ETag: "17a484c-5686-467a4922469c0"
//            Accept-Ranges: bytes
//            Content-Length: 22150
//            Cache-Control: max-age=86400
//            Expires: Thu, 14 May 2009 14:20:58 GMT
//            Vary: Accept-Encoding
//            Connection: close
//            Content-Type: text/html
// ----------------------------------------------------------------------------
// getheaders.c should be run by G-WAN to target ANOTHER running Web server 
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
static char title[] = "Getting HTTP Headers";

// Top of our HTML page
static char top[] = "<!DOCTYPE HTML PUBLIC \"-//IETF//DTD HTML 2.0//EN\">"
     "<html lang=\"en\"><head><title>%s</title><meta http-equiv"
     "=\"Content-Type\" content=\"text/html; charset=utf-8\">"
//   "<link href=\"imgs/style.css\" rel=\"stylesheet\" type=\"text/css\">"
     "</head><body><h1>%s</h1>";

// ----------------------------------------------------------------------------
// imported functions:
//   get_reply(): get a pointer on the 'reply' dynamic buffer from the server
//  xbuf_reset(): (re)initiatize a dynamic buffer object
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

   // -------------------------------------------------------------------------
   // format the top of our HTML page with a title
   // -------------------------------------------------------------------------
   xbuf_xcat(reply, top, title, title);

   // -------------------------------------------------------------------------
   // define default host and URI, and parse parameters, if any
   // -------------------------------------------------------------------------
   char *pHost = "192.168.200.88:80", *pURL = "/index.html";
   int port = 80;
   if(argc > 0)
   {
      // http://10.10.2.4:8080/csp?getheaders&host=127.0.0.1:80&url=/index.html
      get_arg("host=", &pHost, argc, argv);
      get_arg("url=",  &pURL,  argc, argv);
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
   
   // -------------------------------------------------------------------------
   // send the Http request (1,000 ms timeout)
   // -------------------------------------------------------------------------
   xbuf_t buf;
   xbuf_reset(&buf);
   int code = xbuf_frurl(&buf, pHost, port, HTTP_HEAD, pURL, 1000, 0);

   // -------------------------------------------------------------------------
   // build the HTML page
   // -------------------------------------------------------------------------
   xbuf_xcat(reply, 
             "The HEAD request: <b>http://%s:%d%s</b> returned:<br><br>",
             pHost, port, pURL ? pURL : "/");

   // -------------------------------------------------------------------------
   // success (we can now use this data to write into our html page)
   // -------------------------------------------------------------------------
   if(code == 200)
   {
      // Apache.org sends "\r" instead of "\r\n"
      while(xbuf_repl(&buf, "\r", "<br>"))
         ;
      xbuf_ncat(reply, buf.ptr, buf.len);
   }
   else // failure, if(!code) then no connection took place
   if(!code)
   {
      xbuf_cat(reply, "could not reach server<br>");
   }
   else // HTTP error
   {
      // Apache.org sends "\r" instead of "\r\n"
      while(xbuf_repl(&buf, "\r", "<br>"))
         ;
      xbuf_xcat(reply, 
                "The request failed and returned: %u<br><br>%s", 
                code, buf.ptr);
   }

   // -------------------------------------------------------------------------
   // release the memory used by our HTTP request buffer
   // -------------------------------------------------------------------------
   xbuf_free(&buf);

   // -------------------------------------------------------------------------
   // close our HTML page
   // -------------------------------------------------------------------------
   xbuf_xcat(reply, "</body></html>");

   return 200; // return an HTTP code (200:'OK')
}
// ============================================================================
// End of Source Code
// ============================================================================
