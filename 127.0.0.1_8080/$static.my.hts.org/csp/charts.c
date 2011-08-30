// ============================================================================
// C servlet sample for the G-WAN Web Application Server (http://trustleap.ch/)
// ----------------------------------------------------------------------------
// charts.c: compose an HTML page with "in-line" Charts built on-the-fly
//
//            The base64 encoding makes Data URIs 33% larger than original
//            contents (which can be anything, not only pictures), but this
//            is usually compensated by the HTTP Headers that you save with
//            the HTTP requests necessary to fetch small image (and gzip
//            compression reduces the payload even further).
//
//            As a welcome bonus, Data URIs reduce the number of requests
//            necessary to render an HTML page using pictures, making your
//            Web application serve many more users.
//
// ============================================================================
#include "gwan.h" // G-WAN exported functions

#include <stdarg.h> // dr_text()

static u8 *g_path = 0;  // CSP_ROOT path, loaded only once

static int draw_chart(xbuf_t *reply, int w, int h, int type)
{
   // -------------------------------------------------------------------------
   // the color palette used by our charts (sized to 256 for enhancements)
   // -------------------------------------------------------------------------
   // this is the place for you to edit chart colors:
   static const rgb_t pal[256] = {
           {  0,   0,   0},   // Title             - Black
           {128, 128, 128},   // Ticks/Labels      - Dark  Grey
           {164, 164, 164},   // Sub-title         - Med.  Grey
           {220, 220, 220},   // Grid lines        - Light Grey
           {240, 240, 240},   // Grid filling      - Pale  Grey
           { 63, 116, 167},   // Data line         - Dark  Blue
           {195, 216, 230},   // Data Grid lines   - Med.  Blue
           {204, 225, 239},   // Data Grid filling - Light Blue
           {211, 231, 244},   // Data area         - Pale  Blue
           {255,  75,   3},   // Data average      - Orange
           {255, 255, 255} }; // Background        - White

   // -------------------------------------------------------------------------
   // build a smooth gradient color palette (used by pie/ring charts)
   // -------------------------------------------------------------------------
   if(!pal[11]) // setup only once
   {
      static const rgb_t tabcol[] = { 
              {128,   0,   0},   // Red
              {255, 128,   0},   // Orange
              {255, 255,   0},   // Yellow
              {  0, 220, 100},   // Green
              {  0, 100, 200},   // Light Blue
              {  0,   0, 128} }; // Dark Blue
                  
      // generate a gradient palette from these pre-defined color steps
      dr_gradient(pal + 11, 32 - 11, tabcol, sizeof(tabcol) / sizeof(rgb_t));
   }
   
   // -------------------------------------------------------------------------
   // make data for our charts
   // -------------------------------------------------------------------------
   u8 *tags [] = {"","10am","","","","12pm","","","","2pm","","","","4pm"};
   u32 ntag    = sizeof(tags) / sizeof(u8*);
   u8  date [] = "Jun 01 03:59pm EDT";
   u8  title[] = "DOW";
   // Chart data sets
   float tab[] = {10042, 10098, 10182, 10154, 10160, 10132, 10160, 10146, 
                  10215, 10134, 10152, 10122, 10116, 10030};
// float tab[] = {18, 80, 18, 54, 60, 32, 60, 46, 15, 34, 52, 22, -16, 100};
// float tab[] = {1, 8, 3, 5, 7, 1, -6, 4, 5, 3, 5, 2, -1, 1};
// float tab[] = {-1.5, .8, .3, .5, .7, .1, .6, .9, .5, .3, .5, .2, .3, .1};
   int ntab    = sizeof(tab) / sizeof(float), sign[] = {1, -1};
   #define nvals 200  // use more values than we have pixels, forcing dr_chart()
   static float vals[nvals] = {0}; // to use interpolation
   if(!vals[0]) // setup only once
   {
      prnd_t rnd;
      sw_init(&rnd, cycles());
      int i = 0, n, nb = (nvals / ntab) + 1;
      for(; i < nvals; i++)
      {
          n = sw_rand(&rnd); // use pseudo-random data to fill the gaps
          vals[i] = tab[i / nb] + sign[n & 1] * (n % ntab);
      }
   }
   
   // -------------------------------------------------------------------------
   // setup the Chart SIZE and STYLE and allocate memory for a raw bitmap
   // -------------------------------------------------------------------------
   bmp_t img; 
   img.w     =    w;
   img.h     =    h;
   img.bbp   =    5; // 1 << 5 = 32 colors // allowing 'rainbow' charts...
   img.pen   =   11; // pie/ring: gradient starting color index (0:no gradient)
   img.flags = type;
   img.bmp   = (u8*)malloc(img.w * img.h);
   if(!img.bmp) return 503; // service unavailable

   // -------------------------------------------------------------------------
   // render the Chart in our raw bitmap
   // -------------------------------------------------------------------------
   //dr_chart(&img, title, date, tags, ntag, vals, nvals); // use more values
   dr_chart(&img, title, date, tags, ntag, tab, ntab); // no interpolation

   // -------------------------------------------------------------------------
   // build and append a GIF image (-1:no transparency) to the 'reply' buffer
   // -------------------------------------------------------------------------
   u8 *gif = (u8*)malloc(img.w * img.h);
   if(!gif) { free(img.bmp); return 503; } // service unavailable
   int len = gif_build(gif, img.bmp, img.w, img.h, pal, 1 << img.bbp, -1, 0);
   if(len < 0) len = 0; // (len == -1) if gif_build() failed
   free(img.bmp);       // the raw bitmap is no longer needed
   
   // -------------------------------------------------------------------------
   // store the base64 encoded GIF in the 'reply' buffer
   // -------------------------------------------------------------------------
   if(len == -1) // gif_build() failed
   { 
      free(gif);
      return 503; // service unavailable
   }
   
   static u32 num = 1;
   xbuf_xcat(reply, 
             "<h3>Chart #%u:</h3>"
             "<img src=\"data:image/gif;base64,%*B\" alt=\"A chart\" />"
             "<br><br>\r\n",
             num++,
             len, gif);
   free(gif);
   return 200;
}
// ----------------------------------------------------------------------------
// imported functions:
//   get_reply(): get a pointer on the 'reply' dynamic buffer from the server
//       getus(): get current time in microseconds (1 millisecond = 1,000 us)
//     sw_rand(): a decent pseudo-random numbers generator
//     get_env(): get connection's 'environment' variables from the server
//    xbuf_cat(): like strcat(), but it works in the specified dynamic buffer 
//   gif_build(): build an in-memory GIF image from a bitmap and palette
// ----------------------------------------------------------------------------
int main(int argc, char *argv[])
{
   // -------------------------------------------------------------------------
   // build the top of our HTML page
   // -------------------------------------------------------------------------
   static u8 top[]=
     "<!DOCTYPE HTML>"
     "<html lang=\"en\"><head><title>GIF Charts in Data URIs</title>"
     "<meta http-equiv"
     "=\"Content-Type\" content=\"text/html; charset=utf-8\">"
     "<link href=\"/imgs/style.css\" rel=\"stylesheet\" type=\"text/css\">"
     "</head><body style=\"margin-left:16px;\"><br><h2>Generating Charts "
     "\"inline\" in the HTML page (using Data URIs):</h2>"
     "(this page requires one single HTTP request to serve all the base64"
     "-encoded pictures below)<br><br>\r\n\r\n";

   xbuf_t *reply = get_reply(argv);
   xbuf_ncat(reply, top, sizeof(top) - 1);

   // -------------------------------------------------------------------------
   // select a type of chart and render it directly in the HTML page
   // -------------------------------------------------------------------------
   int types[] = { C_LINE,
                   C_LINE | C_GRID,
                   C_LINE | C_TITLES | C_LABELS | C_GRID,
                   C_LINE | C_TITLES | C_LABELS | C_FGRID,
                   C_LINE | C_TITLES | C_LABELS | C_GRID | C_AVERAGE,
                   C_LINE | C_TITLES | C_LABELS | C_FGRID | C_AREA | C_AVERAGE,
                   C_BAR,
                   C_BAR | C_TITLES | C_GRID,
                   C_BAR | C_LABELS | C_GRID | C_AVERAGE,
                   C_BAR | C_TITLES | C_LABELS | C_FGRID | C_AREA | C_AVERAGE,
                   C_DOT | C_AREA | C_GRID,
                   C_DOT | C_TITLES | C_LABELS | C_FGRID | C_AREA | C_AVERAGE,
                   C_RING,
                   C_RING | C_AREA,
                   C_RING | C_TITLES | C_LABELS,
                   C_RING | C_TITLES | C_LABELS | C_AREA,
                   C_PIE,
                   C_PIE | C_AREA,
                   C_PIE | C_TITLES | C_LABELS,
                   C_PIE | C_TITLES | C_LABELS | C_AREA
                 };
   int i = 0;
   while(i < sizeof(types) / sizeof(int))
   {
      if(draw_chart(reply, 192, 96, types[i]) != 200)
      {
         reply->len = 0;
         *reply->ptr = 0;
         return 503; // error
      }
      i++;
   }
   
   // -------------------------------------------------------------------------
   // close our HTML page
   // -------------------------------------------------------------------------
   static char footer[] = 
      "page is served with one single HTTP request: the charts"
      " are generated on-the-fly and embedded into the HTML code by "
      " using the base64 encoding (look at the HTML source code)."
      "<br><br></body></html>");
            
   xbuf_xcat(reply, "\r\n<br>This (%.2F KB) %s", 
            (reply->len + sizeof(footer) - 1) / 1024.0, footer);

   return 200; // return an HTTP code (200:'OK')
}
// ============================================================================
// End of Source Code
// ============================================================================
