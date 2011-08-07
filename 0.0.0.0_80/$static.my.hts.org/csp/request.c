// ============================================================================
// C servlet sample for the G-WAN Web Application Server (http://trustleap.ch/)
// ----------------------------------------------------------------------------
// request.c: get exchange rates from the European Central Bank (updated daily)
//            to convert to/from 34 currencies (including the Euro).
//
//            We cache rates during one day after the date they were generated.
//            Doing so saves CPU and network resources but also preserves the 
//            availability of a (rare) *free* online source of exchange rates.
//
//            Theorically, we should check the ETag: "c9c3f7-6ab-4655096678b80"
//            header to verify if the rates we already have are the latest.
//            We assume that the ECB is updating rates at 24-hour intervals and
//            that using unsynchronized rates for a small lapse of time doesn't
//            really matter for this sample mainly dedicated to e-commerce.
// ----------------------------------------------------------------------------
// Note that xbuf_frurl() is using asynchronous client calls, just like the 
// connect() / send() / recv() system calls that you can use directly: G-WAN
// transparently transforms blocking system calls into asynchronous calls, for
// C scripts (servlets, handlers) as well as for (shared or static) libraries 
// linked with C scripts by "#pragma link".
// ============================================================================
#include "gwan.h" // G-WAN exported functions

// Title of our HTML page
static char title[] = "Currency conversions";

// Top of our HTML page
static char top[] = "<!DOCTYPE HTML>"
     "<html lang=\"en\"><head><title>%s</title><meta http-equiv"
     "=\"Content-Type\" content=\"text/html; charset=utf-8\">"
     "<link href=\"imgs/style.css\" rel=\"stylesheet\" type=\"text/css\">"
     "</head><body><h1>%s</h1>";

// ----------------------------------------------------------------------------
// imported functions:
//   get_reply(): get a pointer on the 'reply' dynamic buffer from the server
//   xbuf_init(): called after xbuf_t has been declared, to initialize struct
// xbuf_frfile(): load a file, and store its contents in a dynamic buffer
//  xbuf_frurl(): make an Http request, and store results in a dynamic buffer
//   xbuf_ncat(): like strncat(), but in the specified dynamic buffer 
//   xbuf_xcat(): formatted strcat() (a la printf) in a given dynamic buffer 
//   xbuf_free(): release the memory allocated for a dynamic buffer
//    rfc2time(): convert an Http time string into a time_t value
//     get_env(): get connection's 'environment' variables from the server:
// ----------------------------------------------------------------------------
// find the requested rate (ie: "USD") in the buffer and return it as a double
// ----------------------------------------------------------------------------
double get_rate(char *buf, char *currency)
{
   double rate = 0.;
   static char *szvalue = "'???' rate='";
   szvalue[1] = currency[0];
   szvalue[2] = currency[1];
   szvalue[3] = currency[2];
   char *p = (char*)strstr(buf, szvalue);
   if(p)
   {  
      int rateM = 0, ratem = 0, scale = 1;
      p    += 12;      // "...currency='USD' rate='[1].2942'..."
      while(*p != '.') // convert the rate's integral part (from chars to int)
            rateM = rateM * 10 + (*p++ - '0');
      p++;
      while(*p != '\'') // convert the rate's decimal part (from chars to int)
            ratem = ratem * 10 + (*p++ - '0'), scale *= 10;
      // convert the Major and minor integers into a double
      rate = (double)rateM + ((double)ratem / (double)(scale));
   }
   return rate;
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
   // build the HTML page
   // -------------------------------------------------------------------------
   xbuf_cat(reply, "<br>Using the most recent exchange rates:<br><br>");

   // -------------------------------------------------------------------------
   // check and load the cached file, or make the HTTP request
   // -------------------------------------------------------------------------
   xbuf_ctx buf;
   long i, code = 0; // assume cached file is not there, or not recent enough
   char *csp_root = get_env(argv, CSP_ROOT, 0); // get the ".../csp/" path
   char szfile[1024];
   s_snprintf (szfile, sizeof(szfile)-1, "%s/rates.xml", csp_root);
   xbuf_xcat  (reply, "Trying to load %s<br>", szfile);
   xbuf_init (&buf); 
   xbuf_frfile(&buf, szfile);

   // -------------------------------------------------------------------------
   // check the data creation date (we use the "Last-Modified:" header)
   // -------------------------------------------------------------------------
   if(buf.len) 
   {
      static char sztag[] = "Last-Modified:";
      xbuf_cat(reply, "Found cached data<br>");
      char *p = strstr(buf.ptr, sztag);
      if(p)
      {
         // convert the Http time string into a time_t (nbr seconds since 1970)
         if((time(0) - rfc2time(p + sizeof(sztag))) < (3600 * 24)) // < 1 day
         {
            xbuf_cat(reply, "Cached data up-to-date<br>");
            code = 200; // found cached file, and it is up-to-date
         }
         else
         {
            xbuf_cat(reply, "Cached data too old,"
                             " trying to get fresh data...<br>");
            code = 20; // let this code work when we are offline
         }
      }
      else
         xbuf_cat(reply, "Could not find time-stamp<br>");
   }
   else
      xbuf_xcat(reply, "Could not find cached data file '%s'<br>", szfile);

   if(!code || code == 20) // cached file not found (or not up-to-date)
   {
      xbuf_ctx buf2;
      xbuf_init(&buf2);
      code = xbuf_frurl(&buf2, "www.ecb.int", 80, HTTP_GET, 
                               "/stats/eurofxref/eurofxref-daily.xml", 500, 0);
      
      // cache this valuable data for later use
      if(code == 200 && get_rate(buf2.ptr, "USD"))
      {
         xbuf_cat   (reply, "The ECB server replied to our query<br>");
         xbuf_tofile(&buf2, szfile);
         xbuf_init (&buf);
         xbuf_ncat  (buf.ptr, buf2.ptr, buf2.len);
      }
      else
      {
         xbuf_cat(reply, "Could not reach the ECB server<br>"
                          "Using cached data, if any:<br><br>");
         code = 20; // use cached data, if any
      }
      xbuf_free(&buf2);
   }

   // -------------------------------------------------------------------------
   // success (we can now use this data to write into our HTML page)
   // -------------------------------------------------------------------------
   if(buf.len)
   {
      // add any currency you may need (see at the bottom of the code, a whole
      // list) but keep in mind that Payment Gateways will not support them all
      double rEUR, rUSD, rCHF, rGPB;
      
      // get the currency rates from the (cached or downloaded) xml file
      rEUR = 1.;
      rUSD = get_rate(buf.ptr, "USD");
      rCHF = get_rate(buf.ptr, "CHF");
      rGPB = get_rate(buf.ptr, "GPB");

      // use the rates to convert currencies
      xbuf_xcat(reply, "100 EUR = USD %.02f <br>", 100. * rUSD);
      xbuf_xcat(reply, "100 EUR = CHF %.02f <br>", 100. * rCHF);
      xbuf_xcat(reply, "100 USD = CHF %.02f <br>", 100. * rCHF / rUSD);
   }
   else
      xbuf_xcat(reply, "No '%s' cached data file found.<br>", szfile);

   // -------------------------------------------------------------------------
   // release memory
   // -------------------------------------------------------------------------
   xbuf_free(&buf);

   // -------------------------------------------------------------------------
   // close our HTML page
   // -------------------------------------------------------------------------
   xbuf_xcat(reply, "</body></html>");

   return 200; // return an HTTP code (200:'OK')
}
// ----------------------------------------------------------------------------
// The European Central Bank's server reply
// ----------------------------------------------------------------------------
// "HTTP/1.1 200 OK\r\n"
// "Date: Wed, 18 Mar 2009 11:07:02 GMT\r\n"
// "Server: Apache/2.2.3 (Linux/SUSE)\r\n"
// "Last-Modified: Tue, 17 Mar 2009 13:31:42 GMT\r\n"
// "ETag: "c9c3f7-6ab-4655096678b80"\r\n"
// "Accept-Ranges: bytes\r\n"
// "Content-Length: 1707\r\n"
// "Connection: close\r\n"
// "Content-Type: text/xml\r\n"
// "X-Pad: avoid browser bug\r\n"
// "\r\n"
// <?xml version="1.0" encoding="UTF-8"?>
// <gesmes:Envelope xmlns:gesmes="http://www.gesmes.org/xml/2002-08-01" xmlns="http://www.ecb.int/vocabulary/2002-08-01/eurofxref">
//         <gesmes:subject>Reference rates</gesmes:subject>
//         <gesmes:Sender>
// 	             <gesmes:name>European Central Bank</gesmes:name>
//         </gesmes:Sender>
//         <Cube>
// 	             <Cube time='2009-03-17'>
// 		                  <Cube currency='USD' rate='1.2942'/>
// 		                  <Cube currency='JPY' rate='128.10'/>
// 		                  <Cube currency='BGN' rate='1.9558'/>
// 		                  <Cube currency='CZK' rate='26.513'/>
// 		                  <Cube currency='DKK' rate='7.4544'/>
// 		                  <Cube currency='EEK' rate='15.6466'/>
// 		                  <Cube currency='GBP' rate='0.92650'/>
// 		                  <Cube currency='HUF' rate='299.68'/>
// 		                  <Cube currency='LTL' rate='3.4528'/>
//			                  <Cube currency='LVL' rate='0.7075'/>
//			                  <Cube currency='PLN' rate='4.4900'/>
//			                  <Cube currency='RON' rate='4.2968'/>
//			                  <Cube currency='SEK' rate='11.0265'/>
//			                  <Cube currency='CHF' rate='1.5327'/>
//			                  <Cube currency='NOK' rate='8.8315'/>
//			                  <Cube currency='HRK' rate='7.4543'/>
//			                  <Cube currency='RUB' rate='44.7465'/>
//			                  <Cube currency='TRY' rate='2.2111'/>
//			                  <Cube currency='AUD' rate='1.9624'/>
//			                  <Cube currency='BRL' rate='2.9523'/>
//			                  <Cube currency='CAD' rate='1.6495'/>
//			                  <Cube currency='CNY' rate='8.8487'/>
//			                  <Cube currency='HKD' rate='10.0327'/>
//			                  <Cube currency='IDR' rate='15530.40'/>
//			                  <Cube currency='INR' rate='66.5350'/>
//			                  <Cube currency='KRW' rate='1835.14'/>
//			                  <Cube currency='MXN' rate='18.3950'/>
//			                  <Cube currency='MYR' rate='4.7562'/>
//			                  <Cube currency='NZD' rate='2.4410'/>
//			                  <Cube currency='PHP' rate='62.410'/>
//			                  <Cube currency='SGD' rate='1.9839'/>
//			                  <Cube currency='THB' rate='46.365'/>
//			                  <Cube currency='ZAR' rate='12.9218'/>
//			          </Cube>
//			  </Cube>
// </gesmes:Envelope>
// ============================================================================
// End of Source Code
// ============================================================================
