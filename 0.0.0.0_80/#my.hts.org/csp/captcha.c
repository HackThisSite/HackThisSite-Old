// ============================================================================
// C servlet sample for the G-WAN Web Application Server (http://trustleap.ch/)
// ----------------------------------------------------------------------------
// captcha.c: generate an in-memory dynamically generated GIF image served
//            "inline" as a Data URI in the HTML code of a page (instead of
//            as a GIF image sent to the client). CSS could be embedded too.
//
//            As text is rendered as a bitmap, it requires OCR to break the
//            captcha (a question of basic arithmetic which gets trickier
//            for robots because the colors used to draw in the bitmap have
//            the same arithmetic contrast: humans will only see one view 
//            rathan the other - due to the HTML background color).
//
// ============================================================================
#include "gwan.h" // G-WAN exported functions

// ----------------------------------------------------------------------------
// render random chars into a bitmap, with a char per line painted in green
// ----------------------------------------------------------------------------
#define NBR_COLUMNS 3 // try '16' to get on the nerves of your users...
#define CHAR_WIDTH  8
#define BMP_WIDTH   (NBR_COLUMNS * CHAR_WIDTH)
#define BMP_HEIGHT  (NBR_COLUMNS * CHAR_WIDTH + 1)

u32 captcha(bmp_t *img, prnd_t *rnd)
{
   u8 *img_p = img->p;
   u32 sum = 0;
   
   for(;;) // loop until we have different results for the two sums
   {
      u8  l[] = { 0, 0 }, color[] = { 1, 2 }, dic[] = "ABCDEF0123456789";
      u32 j = 0, r = sw_rand(rnd), c = r % NBR_COLUMNS;
      char *old_p;
      img->p = img_p + img->w + 1; // top position
      while(j < NBR_COLUMNS)
      {
         int i = 0; // number of characters per line to generate
         while(i < NBR_COLUMNS)
         {
           *l = dic[r % (sizeof(dic) - 1)]; // pick a character
            sum += ((*l < '0' || *l > '9')) ? 0 
                 : (i != c) ? (*l - '0') : (*l - '0') << 16;
            
            old_p = img->p;           // remember last position in bitmap
            img->pen = color[i == c]; // select pen color
            dr_text(img, 0, l);       // 0:default font
            //printf("%s ", l);         // debugging

            // turn proportional spacing into fixed spacing (CHAR_WIDTH)
            img->p += CHAR_WIDTH - (img->p - old_p);

            r = sw_rand(rnd);
            i++;
         }
         //printf("\n"); // debugging
         
         c = r % NBR_COLUMNS; // pick the next random GREEN column
         img->p += (CHAR_WIDTH-1) * img->w; // new line position in buffer
         j++;
      }

      if(((sum & 0xffff0000) >> 16) != (sum & 0x0000ffff))
         break;

      img->p = img_p; // reset bitmap and retry until we get <> sums
      memset(img_p, 0, CHAR_WIDTH * img->w * img->h);
   }
   
   //printf("\n"); // debugging
   return sum; // return the SUM of all the GREEN figures
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
     "<html lang=\"en\"><head><title>Captcha</title><meta http-equiv"
     "=\"Content-Type\" content=\"text/html; charset=utf-8\">"
     "<link href=\"/imgs/style.css\" rel=\"stylesheet\" type=\"text/css\">"
     "</head><body style=\"margin:0 16px;\"><br><h2>Captcha for Humans</h2>"
     "<p>Please enter the SUM of all the GREEN FIGURES (not letters) below "
     "(that's twice the same image, just with a different HTML background "
     "- the Data-URI-inlined GIF background is transparent):</p><br>\r\n";

   xbuf_t *reply = get_reply(argv);
   xbuf_ncat(reply, top, sizeof(top) - 1);
   
   // -------------------------------------------------------------------------
   // allocate memory for a raw bitmap
   // -------------------------------------------------------------------------
   const int w = BMP_WIDTH, h = BMP_HEIGHT, wXh = w * h;
   u8 *bmp = (u8*)calloc(CHAR_WIDTH * w, h); 
   if(!bmp) return 503; // service unavailable

   // -------------------------------------------------------------------------
   // render the captcha in our bitmap
   // -------------------------------------------------------------------------
   u32 seed = (u32)getns();
   prnd_t rnd; // pseudo-random generator (period: 1 << 158)
   sw_init(&rnd, seed); // EPOCH time in nano-seconds

   // structure needed by G-WAN's frame buffer routines like dr_text()
   bmp_t img ={ .bmp = bmp, .p = bmp, .bbp = 8, .pen = 1, .bgd = 0, 
                .rect = {0,0, w,h}, .flags = 0, .w = w, .h = h, 
                .x = 0, .y = 0 };
   u32 sum = captcha(&img, &rnd);

   // -------------------------------------------------------------------------
   // build the GIF image, gif_build(0:transparent color index, 0: no comment)
   // -------------------------------------------------------------------------
   u8 pal[] = { 255, 255, 255,  223, 255, 191,  132, 164, 100,  0, 0, 0 };
   const int nbcolors = (sizeof(pal) / sizeof(u8)) / 3; // RGB values

   u8 *gif = (u8*)malloc(CHAR_WIDTH * wXh);
   if(!gif) { free(bmp); return 503; } // service unavailable
   int gln = gif_build(gif, bmp, w, h, pal, nbcolors, 0, 0);

   // -------------------------------------------------------------------------
   // store the base64 encoded GIF in the 'reply' buffer
   // -------------------------------------------------------------------------
   if(gln > 0) // (gln == -1) if gif_build() failed
   {   
      // a real captcha test would only display the first of those two views:
      // (they are shown side-by-side to visualize the background trick)
      xbuf_cat(reply, "<table><tr>\r\n"
                      "<td style=\"background:#dfffbf;\">\r\n");
      u32 img_pos = reply->len;
      xbuf_xcat(reply,
                "<img src=\"data:image/gif;base64,%*B\" alt=\"A tree\" "
                "width=\"%d\" height=\"%d\" /></td>\r\n",
                gln, gif, w + w, h + h); // scale picture
      xbuf_xcat(reply, 
                "<td style=\"background:#84a464;\">%.*s</tr>\r\n</table>\n\r",
                reply->len - img_pos, reply->ptr + img_pos);
   }
   free(gif);
   free(bmp);

   // -------------------------------------------------------------------------
   // close our HTML page
   // -------------------------------------------------------------------------
   xbuf_xcat(reply, 
            "<br>The two sums are: <b>%u</b> and <b>%u</b>... "
            "for the same Captcha image!<br><br>"
            "By just changing the <b>HTML background color</b> (mouse cursor "
            "hovering, previous state or input or shared secret) used for "
            "the transparent GIF Captcha image we can make something simple "
            "for humans become difficult or even completely impossible "
            "for robots.<br><br>"
            "HTML and GIF are served with one single request: the picture"
            " is generated on-the-fly and embedded into the HTML code by "
            " using the base64 encoding (look at the HTML source code)."
            "<br></body></html>", 
            (sum & 0xffff0000) >> 16, 
            sum & 0x0000ffff);

   return 200; // return an HTTP code (200:'OK')
}
// ============================================================================
// End of Source Code
// ============================================================================
