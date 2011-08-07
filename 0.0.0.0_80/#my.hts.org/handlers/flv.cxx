// ============================================================================
// "Content-Type" Handler sample for the G-WAN Web Server http://trustleap.ch/
// ----------------------------------------------------------------------------
// flv.c: FLV movies Pseudo-Streaming (invoked when a *.flv file is queried)
//
//   Adobe Flash Player can start playing from any part of a FLV movie
//   by sending the HTTP request below ('123' is the bytes offset):
//
//      GET /movie.flv?start=123
//
//   HTTP servers that support Flash Player requests must send the binary 
//   FLV Header ("FLV\x1\x1\0\0\0\x9\0\0\0\x9") before the requested data.
//
//   This "Content-Type" G-WAN Handler just does all this. FLV requests 
//   without a query are served normally (sending the whole file).
//
//   The Apache, Lighttpd or Nginx modules that do the SAME task require 
//   from 150 to 300 lines of code - and are much slower.
// ============================================================================
#include "gwan.h" // G-WAN exported functions

#define FLV_HEAD "FLV\x1\x1\0\0\0\x9\0\0\0\x9" // "innovation" anyone?

int main(int argc, char *argv[])
{
   char *query = get_env(argv, QUERY_STRING, 0); // query: "start=200000"

   if(!query || query[0] != 's' || query[1] != 't' // not a query?
   || query[2] != 'a' || query[3] != 'r' || query[4] != 't' || query[5] != '=')
      return 200; // 200:OK (HTTP return code)

   http_t *head = get_env(argv, HTTP_HEADERS, 0); // set HTTP bytes range
   head->h_range_from = atol(query + sizeof("start=") - 1); // checked later

   http_header(HEAD_ADD | HEAD_AFTER, FLV_HEAD, sizeof(FLV_HEAD) - 1, argv);
   return 206; // 206:Partial Content (HTTP return code)
}
// ============================================================================
// End of Source Code
// ============================================================================
