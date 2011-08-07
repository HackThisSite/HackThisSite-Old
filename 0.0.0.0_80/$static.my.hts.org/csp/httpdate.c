// ============================================================================
// C servlet sample for the G-WAN Web Application Server (http://trustleap.ch/)
// ----------------------------------------------------------------------------
// httpdate.c: benchmark HTTP-compliant dates formatting
//
// In comparison to the Windows system Wininet InternetTimeFromSystemTime() API
// call, Linux clib calls are twice as fast (see the Windows 'bench.c' servlet)
// but G-WAN's code is ALSO faster under Linux: 30x faster than Linux libc.
//
// That's why G-WAN offers function calls that are 'redundant' with the system.
// ============================================================================
// imported functions:
//   get_reply(): get a pointer on the 'reply' dynamic buffer from the server
//    xbuf_cat(): like strcat(), but it works in the specified dynamic buffer 
// ----------------------------------------------------------------------------
#include "gwan.h" // G-WAN exported functions

// ----------------------------------------------------------------------------
// using Linux clib calls to format an HTTP date, just like G-WAN's time2rfc()
// ----------------------------------------------------------------------------
static char *httptime(size_t t, char *date)
{
   size_t tt;
   if(t) tt = t; else tt = time(0);
   strftime(date, 31, "%a, %d %b %Y %H:%M:%S GMT", gmtime(&tt));
   return date;
}
// ----------------------------------------------------------------------------
// Title of our HTML page
static u8 title[] = "Benchmarking function calls (with CPU clock cycles)";

// Top of our HTML page
static u8 top[]=
     "<!DOCTYPE HTML>"
     "<html lang=\"en\"><head><title>%s</title><meta http-equiv"
     "=\"Content-Type\" content=\"text/html; charset=utf-8\">"
     "<link href=\"/imgs/style.css\" rel=\"stylesheet\" type=\"text/css\">"
     "</head><body style=\"margin:0 16px;\"><br><h2>%s</h2>";
     
int main(int argc, char *argv[])
{
   u8  date[32], *p;
   u64 start, overh;
   u32 i, rounds=10,
       ms_min = 0xffffffff, ms_ave = 0, ms_max = 0,
       sv_min = 0xffffffff, sv_max = 0, sv_ave = 0,
       t = time(0), z = 1;

   // create a dynamic buffer and get a pointer on the server response buffer
   xbuf_t *reply = get_reply(argv);

   // ---- format the top of our HTML page with a title
   xbuf_xcat(reply, top, title, title);

   // ---- find the CPU clock cycles count overhead so we can remove it later
   i=rounds; while(i--) start=cycles64(), overh=cycles64()-(start+z), overh--;

   // -------------------------------------------------------------------------
   // benchmark the Linux clib function calls
   // -------------------------------------------------------------------------
   // check that all function calls provide similar results
   memset(date, 0, sizeof(date));
   httptime(t, date);
   xbuf_xcat(reply, "Linux clib calls: %s<br>", date); 

   xbuf_cat(reply, "<br><table class=\"clean\" width=180px>"
                    "<tr><th>function</th><th>time</th></tr>");

   // warm the cpu cache to get consistent values
   i = rounds * 2; while(i--) start = cycles64(), p = httptime(t, date);
   i = rounds; // now time it
   while(i--)
   {
      start = cycles64();
      httptime(t, date);
      start = cycles64() - (start + overh);
      if(ms_min > start) ms_min = start;
      if(ms_max < start) ms_max = start;
      ms_ave += start;
      xbuf_xcat(reply, 
                "<tr class=\"d%u\"><td>Linux clib calls</td><td>%u</td></tr>",
                !(i & 1), start);
   }
   ms_ave /= rounds; xbuf_cat(reply, "</table>");

   // -------------------------------------------------------------------------
   // benchmark G-WAN's "time2rfc(t, date);" function call
   // -------------------------------------------------------------------------
   // check that all function calls provide similar results
   memset(date, 0, sizeof(date));
   xbuf_xcat(reply, "<br>G-WAN time2rfc(): %s<br>", time2rfc(t, date)); 

   xbuf_cat(reply, "<br><table class=\"clean\" width=180px>"
                    "<tr><th>function</th><th>time</th></tr>");

   // warm the cpu cache to get consistent values
   i = rounds * 2; while(i--) start = cycles64(), p = time2rfc(t, date);
   i = rounds; // now time it
   while(i--)
   {
      start = cycles64();
      time2rfc(t, date);
      start = cycles64() - (start + overh);
      if(sv_min > start) sv_min = start;
      if(sv_max < start) sv_max = start;
      sv_ave += start;
      xbuf_xcat(reply, 
                "<tr class=\"d%u\"><td>G-WAN time2rfc()</td><td>%u</td></tr>",
                !(i & 1), start);
   }
   sv_ave /= rounds; xbuf_cat(reply, "</table>");

   // -------------------------------------------------------------------------
   // write how many CPU cycles these operations took
   // -------------------------------------------------------------------------
   xbuf_xcat(reply, "<br><table class=\"clean\" width=440px>"
   "<tr><th>function</th><th>max</th><th>average</th><th>min</th></tr>"
   "<tr class=\"d1\"><td>Linux clib calls</td><td>%u</td><td>%u</td><td>%u</td>"
   "<tr class=\"d0\"><td>G-WAN time2rfc()</td><td>%u</td><td>%u</td><td>%u</td></tr>"
   "</tr></table><table class=\"clean\" width=440px>"
   "<tr><th>G-WAN's code scaled <font color=#f0f000>%.02f</font> times "
   "better</th></tr></table>",
   ms_max, ms_ave, ms_min, 
   sv_max, sv_ave, sv_min, (double)ms_ave / (double)sv_ave);

   // ---- close our HTML page
   xbuf_cat(reply, "<br></body></html>");

   return 200; // return an HTTP code (200:'OK')
}
// ============================================================================
// End of Source Code
// ============================================================================
