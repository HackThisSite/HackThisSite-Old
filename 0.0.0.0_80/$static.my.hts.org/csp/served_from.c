// ============================================================================
// C servlet sample for the G-WAN Web Application Server (http://trustleap.ch/)
// ----------------------------------------------------------------------------
// servedfrom.c: return "Served from 192.168.142.16:8080" (with your details)
//
//               Useful to check Virtual Host settings.
//
// Based on an idea coded in Python (see below) and describbed in:
// http://www.linuxjournal.com/magazine/nginx-high-performance-web-server-
//                                              and-reverse-proxy?page=0,2
//
//   import sys,BaseHTTPServer as B
//   class Handler(B.BaseHTTPRequestHandler):
//     def do_GET(self):
//       self.wfile.write("Served from port %s" % port)
//     def log_message(self, *args):
//       pass
//   if __name__ == '__main__':
//     host,port = sys.argv[1:3]
//     server = B.HTTPServer((host,int(port)), Handler)
//     server.serve_forever()//
// Python does not look like a simpler (or clearer) language than ANSI C, and 
// G-WAN certainly makes it easier to serve dynamic content than Nginx+Python
// (see the configuration detailed in the 'Linux Journal' article above).
// ============================================================================
// imported functions:
//     get_env(): get the specified 'environment' variable from the server
//   get_reply(): get a pointer on the 'reply' dynamic buffer from the server
//   xbuf_xcat(): like sprintf(), but it works in the specified dynamic buffer 
// ----------------------------------------------------------------------------
#include "gwan.h" // G-WAN exported functions

// ----------------------------------------------------------------------------
/* see enum HTTP_Method {} and char *szHTTP_Method[] in gwan.h
static char *szHTTP_Method[] =
{
   [HTTP_ANY] = "?",
   [HTTP_GET] = "GET",
   [HTTP_HEAD] = "HEAD",
   [HTTP_POST] = "POST",
   [HTTP_PUT] = "PUT", ...
}; */
// ----------------------------------------------------------------------------
int main(int argc, char *argv[])
{
   xbuf_t *reply = get_reply(argv);

   char *srv_host = get_env(argv, SERVER_NAME, 0);
   char *srv_ip   = get_env(argv, SERVER_ADDR, 0);
   u32 srv_port   = get_env(argv, SERVER_PORT, 0);
   char *cli_ip   = get_env(argv, REMOTE_ADDR, 0);
   u32 cli_port   = get_env(argv, REMOTE_PORT, 0);
   u32   method   = get_env(argv, REQUEST_METHOD, 0);
   char *cli_id   = get_env(argv, USER_AGENT, 0);

   xbuf_xcat(reply, 
             "This page was processed...<br><br>"
             "<b>Using get_env()</b>:<br><br>"
             "by the Server: &nbsp; &nbsp; %s:%u (hostname: %s)<br>"
             "HTTP method:&nbsp; &nbsp; %s<br>"
             "for the Client: &nbsp; &nbsp; %s:%u<br>"
             " &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp;" 
             " &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; %s<br>",
             srv_ip, srv_port, srv_host, 
             szHTTP_Method[method],
             cli_ip, cli_port, cli_id);
             
   // an alternate, faster method to get the same information:
   // (see typedef struct http_t in gwan.h)
   http_t *http = get_env(argv, HTTP_HEADERS, 0);

   xbuf_xcat(reply,
             "<br><br>" 
             "<b>Using HTTP Headers to get the same info</b>:"
             "<br><br>"
             "by the Server: &nbsp; &nbsp; %s:%u (hostname: %s)<br>"
             "HTTP method:&nbsp; &nbsp; %s<br>"
             "for the Client: &nbsp; &nbsp; %s:%u<br>"
             " &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp;" 
             " &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; %s<br>",
             http->h_host, http->h_port, http->h_host, 
             szHTTP_Method[http->h_method],
             cli_ip, cli_port, http->h_useragent);
   
   return 200;
}
// ============================================================================
// End of Source Code
// ============================================================================
