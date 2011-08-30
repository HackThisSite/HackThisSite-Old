// ============================================================================
// C servlet sample for the G-WAN Web Application Server (http://trustleap.ch/)
// ----------------------------------------------------------------------------
// data_uri.c: generate an in-memory dynamically generated GIF image served
//             "inline" as a Data URI in the HTML code of a page (instead of
//             as a GIF image sent to the client).
//
//             The base64 encoding makes Data URIs 33% larger than original
//             contents (which can be anything, not only pictures), but this
//             is usually compensated by the HTTP Headers that you save with
//             the HTTP requests necessary to fetch small image (and gzip
//             compression reduces the payload even further).
//
//             As a welcome bonus, Data URIs reduce the number of requests
//             necessary to render an HTML page using pictures, making your
//             Web application serve many more users.
//
// ============================================================================
#include "gwan.h" // G-WAN exported functions

#include <math.h> // sin(), cos()
// ----------------------------------------------------------------------------
// draw a fractal tree
// ----------------------------------------------------------------------------
void tree(char *bmp, int w, int h, 
          double posX, double posY, 
          double dirX, double dirY, double size, int rounds, int color)
{
   #define pi 3.1415926535897932384626433832795
   #define maxRecursions       16  // branching level (larger = slower)
   #define angle        (0.2 * pi) // angle in radians
   #define shrink             1.8  // relative size of new branches
   
   // structure needed by G-WAN's frame buffer routines like dr_line()
   bmp_t img ={ .bmp = bmp, .p = 0, .bbp = 8, .pen = color, .bgd = 0, 
                .rect = {0,0, w,h}, .flags = 0, .w = w, .h = h, 
                .x = 0, .y = 0 };

   int x1 = (int)posX, x2 = (int)(posX + size * dirX), 
       y1 = (int)posY, y2 = (int)(posY + size * dirY);

   // clipping, just in case the parameters do not fit the bitmap size
   if(x1 < 0 || x1 > w - 1 || x2 < 0 || x2 > w - 1 
   || y1 < 0 || y1 > h - 1 || y2 < 0 || y2 > h - 1)
      x1 = x1;
   else
      dr_line(&img, x1, y1, x2, y2);

   if(rounds > maxRecursions)
      return;
    
   double posX2 = posX + size * dirX, 
          posY2 = posY + size * dirY, dirX2, dirY2, size2 = size / shrink;
   
   dirX2 =  cos(angle) * dirX + sin(angle) * dirY;
   dirY2 = -sin(angle) * dirX + cos(angle) * dirY;
   tree(bmp, w, h, posX2, posY2, dirX2, dirY2, size2, rounds + 1, color);
   
   dirX2 =  cos(-angle) * dirX + sin(-angle) * dirY;
   dirY2 = -sin(-angle) * dirX + cos(-angle) * dirY;
   tree(bmp, w, h, posX2, posY2, dirX2, dirY2, size2, rounds + 1, color);
}
// ----------------------------------------------------------------------------
// imported functions:
//   get_reply(): get a pointer on the 'reply' dynamic buffer from the server
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
     "<html lang=\"en\"><head><title>Data URIs</title><meta http-equiv"
     "=\"Content-Type\" content=\"text/html; charset=utf-8\">"
     "<link href=\"/imgs/style.css\" rel=\"stylesheet\" type=\"text/css\">"
     "</head><body style=\"margin:16px;\"><h2>Using \"inline\" HTML "
     "Data URIs to serve dynamic contents:</h2>\r\n";

   xbuf_t *reply = get_reply(argv);
   xbuf_ncat(reply, top, sizeof(top) - 1);

   // -------------------------------------------------------------------------
   // allocate memory for a raw bitmap
   // -------------------------------------------------------------------------
   int   w = 128, h = 128, wXh = w * h;
   u8 *bmp = (u8*)calloc(w, h);
   if(!bmp) return 503; // service unavailable

   // -------------------------------------------------------------------------
   // render the fractal tree in our bitmap, cycling colors
   // -------------------------------------------------------------------------
   static int color = 2; // [1 - 3] range (small pallette, small file)
   if(++color == 4) color = 1;
   tree(bmp, w, h, w / 2, h - 1, 0, -1, h / 2.3, 2, color);

   // -------------------------------------------------------------------------
   // build the GIF image (-1:no transparency, 0: no comment)
   // -------------------------------------------------------------------------
   u8 pal[12] = {255, 255, 255,  160, 40, 80,  80, 160, 40,  40, 140, 220};
   const int nbcolors = (sizeof(pal) / sizeof(u8)) / 3; // RGB values

   u8 *gif = (u8*)malloc(wXh);
   if(!gif) { free(bmp); return 503; } // service unavailable
   int len = gif_build(gif, bmp, w, h, pal, nbcolors, -1, 0);

   // -------------------------------------------------------------------------
   // store the base64 encoded GIF in the 'reply' buffer
   // -------------------------------------------------------------------------
   if(len > 0) // (len == -1) if gif_build() failed
      xbuf_xcat(reply, 
                "<img src=\"data:image/gif;base64,%*B\" alt=\"A tree\" /><br>",
                len, gif);
   free(gif);
   free(bmp);

   // -------------------------------------------------------------------------
   // close our HTML page
   // -------------------------------------------------------------------------
   static char footer[] = 
      "page is served with one single request: the tree picture"
      " is generated on-the-fly and embedded into the HTML code by "
      " using the base64 encoding (look at the HTML source code)."
      "<br></body></html>";
      
   xbuf_xcat(reply, "\r\n<br>This (%.2F KB) %s", 
            (reply->len + sizeof(footer) - 1) / 1024.0, footer);

   return 200; // return an HTTP code (200:'OK')
}
// ============================================================================
// End of Source Code
// ============================================================================
