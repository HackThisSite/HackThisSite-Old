// ============================================================================
// C servlet header for the G-WAN Web Application Server (http://trustleap.ch/)
// ----------------------------------------------------------------------------
// gwan.h: functions exported by G-WAN and made available to C servlets
//         (this file should be located in the gwan/include folder, along
//         with the other include files listed below)
// ============================================================================
#include "short_types.h" // u8/s8, u16/s16, u32/int, u64/s64
#include "xbuffer.h"     // xbuf_xcat(), etc.
#include "float.h"       // limits, conversions
#include "stdarg.h"      // va_start()/va_end()
#include "stdbool.h"     // true/false

// ----------------------------------------------------------------------------
// Sections covered below:
// ----------------------------------------------------------------------------
// G-WAN server reply' xbuffer
// G-WAN server 'error.log' file
// G-WAN server 'environment' variables
// G-WAN server URL parameters
// G-WAN server handlers
// G-WAN server cache
// JSON (de-)serialization
// escaping
// formatting
// in-memory GIF
// frame buffer
// charts & sparklines
// email
// time
// random numbers
// checksums
// hashing
// encryption
// compression
// shared libraries (see also "#pragma link" in sqlite.c)
// ----------------------------------------------------------------------------

// ----------------------------------------------------------------------------
// G-WAN server 'reply' xbuffer
// ----------------------------------------------------------------------------
// get the memory pointer for the server reply dynamic buffer
// xbuf_t *reply = get_reply(argv);

xbuf_t *get_reply(char *argv[]);

// ----------------------------------------------------------------------------
// G-WAN server 'error.log' file
// ----------------------------------------------------------------------------
// output text in the current virtual host 'error.log' file:
// char str[256];
// u64 nbr_records = 1234567890123;
// s_snprintf(str, sizeof(str)-1, "database records: %llu", nbr_records);
// log_err(argv, str);

void log_err(char *argv[], const char *msg);

// ----------------------------------------------------------------------------
// G-WAN server 'environment' variables
// ----------------------------------------------------------------------------
// get an environment variable (a performance counter, or a persistence ptr)
// (see the 'contact.c' sample and 'enum HTTP_Env' below for all values)

// 'inval' is only used to SET a value (HTTP_CODE or DOWNLOAD_SPEED)
u64 get_env(char *argv[], int name, char **inval);

enum HTTP_Method
{
   HTTP_BAD=0, HTTP_GET, // RFC 2616
   HTTP_HEAD, HTTP_POST, HTTP_PUT, HTTP_DELETE, HTTP_OPTIONS
};

enum HTTP_Type
{
   TYPE_URLENCODED=1, TYPE_MULTIPART, TYPE_OCTETSTREAM
};

enum ENC_Type
{
   ENC_IDENTITY=0, // default
   ENC_GZIP=1, ENC_DEFLATE=2, ENC_COMPRESS=4, ENC_CHUNKED=8
};

enum AUTH_Type
{
   AUTH_ANY=1, AUTH_BASIC=2, AUTH_DIGEST=3,
   AUTH_SRP=4, // DH-like authentication and key exchange (shared secret)
   AUTH_x509=5 // can be used on the top of AUTH_BASIC/AUTH_DIGEST/AUTH_SRP
};

enum HTTP_Env
{
   // -------------------------------------------------------------------------
   // Server 'environment' variables
   // -------------------------------------------------------------------------
   REQUEST=0,       // char  *REQUEST;        // "GET / HTTP/1.1\r\n..."
   REQUEST_METHOD,  // int    REQUEST_METHOD  // 1=GET, 2=HEAD, 3=PUT, 4=POST
   QUERY_STRING,    // char  *QUERY_STRING    // request URL after '?'
   REQ_ENTITY,      // char  *ENTITY          // "arg=x&arg=y..."
   CONTENT_TYPE,    // int    CONTENT_TYPE;   // 1="x-www-form-urlencoded"
   CONTENT_LENGTH,  // int    CONTENT_LENGTH  // body length provided by client
   CONTENT_ENCODING,// int    CONTENT_ENCODING// entity, gzip, deflate
   SESSION_ID,      // int    SESSION_ID;     // 12345678 (range: 0-4294967295)
   HTTP_CODE,       // int   *HTTP_CODE;      // 100-600 range (200:'OK')
   AUTH_TYPE,       // int    AUTH_TYPE;      // see enum AUTH_Type {}
   REMOTE_ADDR,     // char  *REMOTE_ADDR;    // "192.168.54.128"
   REMOTE_PORT,     // int    REMOTE_PORT;    // 1460 (range: 1024-65535)
   REMOTE_PROTOCOL, // int    REMOTE_PROTOCOL // ((HTTP_major*1000)+HTTP_minor)
   REMOTE_USER,     // char  *REMOTE_USER     // "Pierre"
   USER_AGENT,      // char  *USER_AGENT;     // "Mozilla ... Firefox"
   SERVER_SOFTWARE, // char  *SERVER_SOFTWARE // "G-WAN/1.0.2"
   SERVER_NAME,     // char  *SERVER_NAME;    // "domain.com"
   SERVER_ADDR,     // char  *SERVER_ADDR;    // "192.168.10.14"
   SERVER_PORT,     // int    SERVER_PORT;    // 80 (443, 8080, etc.)
   SERVER_DATE,     // char  *SERVER_DATE;    // "Tue, 06 Jan 2009 06:12:20 GMT"
   SERVER_PROTOCOL, // int    SERVER_PROTOCOL // ((HTTP_major*1000)+HTTP_minor)
   WWW_ROOT,        // char  *WWW_ROOT;       // the HTML pages root folder
   CSP_ROOT,        // char  *CSP_ROOT;       // the CSP .C files folder
   LOG_ROOT,        // char  *LOG_ROOT;       // the log files folder
   HLD_ROOT,        // char  *HLD_ROOT;       // the handlers folder
   FNT_ROOT,        // char  *FNT_ROOT;       // the fonts folder
   DOWNLOAD_SPEED,  // int   *DOWNLOAD_SPEED; // minimum allowed transfer rate
   // -------------------------------------------------------------------------
   // Server performance counters
   // -------------------------------------------------------------------------
   CC_BYTES_IN=100, CC_BYTES_OUT,  CC_ACCEPTED,  CC_CLOSED,   CC_REQUESTS,
   CC_HTTP_REQ,     CC_CACHE_MISS, CC_ACPT_TMO,  CC_READ_TMO, CC_SLOW_TMO,
   CC_SEND_TMO,     CC_BUILD_TMO,  CC_CLOSE_TMO, CC_CSP_REQ,  CC_STAT_REQ,
   CC_HTTP_ERR,     CC_EXCEPTIONS, CC_BYTES_INDAY, CC_BYTES_OUTDAY,
   // -------------------------------------------------------------------------
   // Handler and VirtualHost Persistence pointers
   // -------------------------------------------------------------------------
   US_HANDLER_DATA=200, US_VHOST_DATA
};

// ----------------------------------------------------------------------------
// G-WAN server URL parameters
// ----------------------------------------------------------------------------
// get an URL parameter: "http://127.0.0.1/csp?hellox&name=Eva"
// example: char name=0; get_arg("name=", &name, argc, argv);
//          (now, 'name' points to "Eva")

void get_arg(char *name, char **value, int argc, char *argv[]);

// you can also walk main()'s argv[] values:
// int i = 0;
// while(i < argc)
// {
//    xbuf_xcat(&reply, "argv[%u] '%s'<br>", i, argv[i]);
//    i++;
// }

// ----------------------------------------------------------------------------
// G-WAN server handlers
// ----------------------------------------------------------------------------

enum HANDLER_ACT
{
   HDL_INIT=0, HDL_AFTER_ACCEPT, HDL_AFTER_READ, HDL_BEFORE_PARSE, 
   HDL_AFTER_PARSE, HDL_BEFORE_WRITE, HDL_CLEANUP
};

// ----------------------------------------------------------------------------
// G-WAN server cache
// ----------------------------------------------------------------------------
// create/update a cache entry ('file' MUST be imaginary if 'buf' is not NULL)
// example: cacheadd(argv, "/tool/counter", buf, 1024, 200, 60); // expire:60sec
// example: cacheadd(argv, "/archives/doc_1.pdf", 0, 0, 200, 0); // never expire
//                             ('file' MUST exist if 'buf' is NULL)
// if(expire ==       0) never expires
// if(expire >        0) expires in 'expires' seconds
//
// 'code' is the HTTP status code that the server will send to clients
//
// return 0:failure, !=0:success
//
// see the cache0.c, cache1.c, cache2.c, etc. samples.

int cacheadd(char *argv[], char *file, char *buf, u32 buflen, u32 code, 
             u32 expire);

// delete a cached entry
// example: cachedel(argv, "/tool/counter");

void cachedel(char *argv[], char *file);

// ----------------------------------------------------------------------------
// JSON (de-)serialization
// ----------------------------------------------------------------------------
// NOTE: numbers are stored as 'double', don't forget to *cast* in xbuf_xcat()
// see json.c for an extensive sample (it uses all the functions below)
enum JSN_TYPE
{
   jsn_FALSE = 0, jsn_TRUE, jsn_NULL, jsn_NUMBER, jsn_STRING,
   jsn_NODE, jsn_ARRAY
};

typedef struct jsn_s
{
   struct jsn_s *prev;   // node's prev item (parent if node is 1st child)
   struct jsn_s *next;   // node's next item (list ends with NULL)
   struct jsn_s *child;  // node's child node (NULL if none)
   char         *name;   // node's name
   int           type;   // node's value type (see JSN_TYPE above)
   union {
   char         *string; // value 'type' == jsn_STRING
   double        number; // value 'type' == jsn_NUMBER
   };
   u64           x;      // context
   long          y;      // context
} jsn_t;

// take JSON text as input and return a jsn_t tree
// (call jsn_free() when you are done with the jsn_t tree)
jsn_t *jsn_frtext(char *text, char *name);

// append and format a jsn_t object into text in the specified dynamic buffer
// if(!formated) then a compact formating is used (no separator, CRLF)
// (call free() when you are done with the text)
char *jsn_totext(xbuf_t *text, jsn_t *node, int formated);

// return a node's item[i] or NULL if item[i] does not exist
jsn_t *jsn_byindex(jsn_t *node, int i);

// search for 'name' in all same-level items; if(deep) in children
// (case insensitive search)
jsn_t *jsn_byname(jsn_t *node, char *name, int deep);

// search for 'value' of 'type' in all same-level items; if(deep) in children
// (case insensitive search)
jsn_t *jsn_byvalue(jsn_t *node, int type, double value, int deep);

// add an 'item' or a 'node' to the specified node
jsn_t *jsn_add(jsn_t *node, char *name, int type, double value);

// update an 'item' or a 'node'
jsn_t *jsn_updt(jsn_t *node, double value);

// remove an 'item' or a 'node' (and all its nodes/items)
void jsn_del(jsn_t *node);

// free all memory and delete a jsn_t node and all its nodes and items
void jsn_free(jsn_t *node);

// helpers for code clarity
#define jsn_add_null(node, name)      jsn_add(node, name, jsn_NULL,        0)
#define jsn_add_false(node, name)     jsn_add(node, name, jsn_FALSE,       0)
#define jsn_add_true(node, name)      jsn_add(node, name, jsn_TRUE,        0)
#define jsn_add_bool(node, name, n)   jsn_add(node, name, (n != 0),        0)
#define jsn_add_number(node, name, n) jsn_add(node, name, jsn_NUMBER,      n)
#define jsn_add_string(node, name, s) jsn_add(node, name, jsn_STRING, (u64)s)
#define jsn_add_node(node, name)      jsn_add(node, name, jsn_NODE,        0)
#define jsn_add_array(node, name, n)  jsn_add(node, name, jsn_ARRAY,  (u64)n)

// ----------------------------------------------------------------------------
// escaping
// ----------------------------------------------------------------------------
u32  url_encode   (u8 *dst, u8 *src, u32 maxdstlen);  // return len
u32  escape_html  (u8 *dst, u8 *src, u32 maxdstlen);  // return len
u32  unescape_html(u8 *str);                          // inplace, return len
int  html2txt     (u8 *html, u8 *text, int maxtxlen); // return len

// ----------------------------------------------------------------------------
// formatting
// ----------------------------------------------------------------------------
// extended sprintf(): (these extensions are also used by xbuf_xcat())
// "%F","%D","%I","%U" - pretty thousands (the ' formatter is also supported)
// "%b"                - binary (8 => "1000")
// "%B","%-B"          - base 64 encode/decode ("%12B" encode a binary buffer)
// "%3C"               - generate a string of length n ("%3C", 'A' => "AAA")
// "%k"                - KB, MB, GB, etc. (1024 => "1 KB")

int s_snprintf(char *str, u32 str_m, const char *fmt, ...);

// ----------------------------------------------------------------------------
// in-memory GIF
// ----------------------------------------------------------------------------
// to save a GIF image on disk, just save the buffer made by gif_build()

// build an in-memory GIF image from a raw 'bitmap'
// params: buffer      - destination buffer (must be pre-allocated)
//         bitmap      - input pixels (8-bit pixels)
//         width       - image width
//         height      - image height
//         palette     - color palette (3 * 256 = 768 bytes maximum)
//         nbrcolors   - number of entries in palette (256 maximum)
//         transparent - index of transparent colour (-1: no transparency)
//         comment     - a pointer on a text comment (0:none)
// return: length of GIF image, -1 if failure (see the fractal.c sample)

int gif_build(u8 *gif, u8 *bitmap, u32 width, u32 height, u8 *palette, 
              u32 nbcolors, int transparency, u8 *comment);

// split an in-memory GIF (loaded with xbuf_frfile()?) into its components
// params: buf         - buffer to parse
//         buflen      - the size in bytes of the input buffer
//         width       - returned image width
//         height      - returned image height
//         palette     - pre-allocated palette (3 * 256 = 768 bytes maximum)
//         nbcolors    - returned nbr of colors
//         transparent - returned color transparent index (-1 if none)
//         comment     - returned allocated GIF comment   ( 0 if none)
// return: pointer on newly allocated bitmap (0 on failure)
// notes:  if 'comment' different from null, you MUST free(comment); but you
//         can pass a null to gif_parse() to say that you don't want comments

u8 *gif_parse(u8 *buf, u32 buflen, u32 *width, u32 *height, u8 *palette,
              u32 *nbcolors, int *transparent, u8 **comment);

// ----------------------------------------------------------------------------
// frame buffer
// ----------------------------------------------------------------------------
typedef struct { u8  r,g,b; } rgb_t;
typedef struct { u32 x,y,X,Y; } rect_t;

typedef struct 
{
  u8    *bmp,       // bitmap pixels
        *p,         // current cursor position, as a pointer
         bbp,       // bits per pixel
         pen, bgd;  // current drawing color index / background color index
  rect_t rect;      // used for clipping, windows, etc.
  u32    flags,     // alignment, type of chart, whatever you need
         w, h,      // bitmap width and height
         x, y;      // current cursor position, as row/column coordinates
} bmp_t;            // (used when the 'p' pointer above is null)

// use powers of two for all the flags so we can combine them in 'bmp_t.flags'

enum C_TEXT_STYLE
{
   V_TOP_ALIGN = 1 << 0, // vertical
   V_CEN_ALIGN = 1 << 1, 
   V_BOT_ALIGN = 1 << 2, 
   H_LEF_ALIGN = 1 << 3, // horizontal 
   H_CEN_ALIGN = 1 << 4, 
   H_RIG_ALIGN = 1 << 5,
   T_OPAQUE    = 1 << 6  // more flags can be added until 1 << 12:C_CHART_STYLE
};

// create a multi-gradient 'nbcolors'-palette using 'nbsteps' RGB values
// palette   - position in the palette where to store gradient
// nbcolors  - gradient size (in colors)
// steps     - array of rgb_t values used to build the gradient
// nbsteps   - number of entries in the rgb_t values array (must be >= 2)

void dr_gradient(u8 *palette, int nbcolors, rgb_t *steps, int nbsteps);

// img.x/y     - starting point
// img.pen     - color
// img.flags   - alignment
// if alignment is provided then x/y is the left/center/right point used to
// align the text
// (if the full-path 'font' is null then "./fonts/9pts.gif" is used)
// return the lenght of the printed text in pixels
// (clipping is done only on the right side of the frame-buffer)

u32  dr_text  (bmp_t *img, u8 *font, const char *fmt, ...);

// img.pen  - color

void dr_line  (bmp_t *img, int x1, int y1, int x2, int y2);

// img.pen  - color
// if(img.bgd > 0), the circle is filled with the img.bgd color palette index

void dr_circle(bmp_t *img, int x, int y, int radius);

// ----------------------------------------------------------------------------
// charts & sparklines
// ----------------------------------------------------------------------------
// to make a sparkline, use C_LINE without (C_TITLES | C_LABELS | C_AVERAGE)
// float tab[] = {10042, 10098, 10182, 10154, 10160, 10132, 10160, 10146};
// bmp_t img;
// img.w     = 30;
// img.h     = 10; 
// img.bbp   =  3; // 1 << 3 = 8 colors
// img.flags = C_LINE | C_GRID; // or img.flags = C_BAR;
// dr_chart(&img, 0, 0, 0, 0, tab, sizeof(tab) / sizeof(float));

// use powers of two for all the flags so we can combine them in 'bmp_t.flags'

enum C_CHART_STYLE
{
   C_LINE    = 1 << 12, // line chart
   C_AREA    = 1 << 13, // fill line/bar/pie/ring/dot charts with 'img.bgd' color
   C_BAR     = 1 << 14, // bar  chart
   C_PIE     = 1 << 15, // pie  chart (prettier with dedicated palette entries)
   C_RING    = 1 << 16, // ring chart (a pie chart with a central hole)
   C_DOT     = 1 << 17, // dot  chart
   C_AVERAGE = 1 << 18, // horizontal dotted line showing the average value
   C_LABELS  = 1 << 19, // make room for (and print) x/y axis ticks and labels
   C_TITLES  = 1 << 20, // make room for (and print) title and sub-title (if any)
   C_GRID    = 1 << 21, // draw vertical and horizontal lines in the background
   C_FGRID   = 1 << 22  // the background grid is filled by alternance
};

// params:
// img      - pointer on a bmp_t bitmap structure
// title    - text printed in black at the upper-left corner of the bitmap
// subtitle - text printed in black at the upper-right corner of the bitmap
// tags     - x axis labels (if null, 'ntag' numbers are printed instead)
// ntag     - number of x axis labels
// val      - values to render as a chart
// nval     - number of values to render as a chart

void dr_chart(bmp_t *img, u8 *title, u8 *subtitle, 
              u8  **tags, u32 ntag, 
              float *val, u32 nval);

// ----------------------------------------------------------------------------
// email
// ----------------------------------------------------------------------------
// the 'error' parameter must point to an allocated buffer sized to the total
// size of headers + email body + attachments (encoded in base64: worst case). 
// Using 'total size' * 2 is more than safe as base64 inflates by ~33%.
// See the contact.c sample for more details:

typedef struct attach_s
{
   char *name; // file name (the extension is used to find the MIME type)
   char *file; // file contents
   u32  size;  // file size
   u32  errlen;// size of the pre-allocated error buffer (mandatory)
   u32  nbr;   // number of attachments (only the 1st array item is necessary)
} attach_t;

// If you wonder why 'nbr' is reduncdant then this is because the attachment
// feature was added to sendemail() but I did not want prior code using it
// to break because of a new 'attachment' function argument.

typedef struct email_s
{
   char       *text;   // must be first
   const char *tag;    // *tag = "@"; to detect an attachment structure
   attach_t   *attach; // array of attachments
} email_t;

// So the email_t structure must be passed as the 'text' sendemail() argument
// when you want to send an email with attachment(s) (or a body with contents
// using 8-bit data, in which case the 'text' field of the attach_t structure
// can be set to "."). See the email.c sample.

int sendemail(char *mail_server,
              char *src_mailaddr, char *dst_mailaddr,
              char *subject, char *text,
              char *auth_user, char *auth_pass, // 'login' auth. only
              char *error); // pre-allocated (and later freed) by caller

int isvalidemailaddr(char *szEmail);

// ----------------------------------------------------------------------------
// time
// ----------------------------------------------------------------------------
typedef struct _tm_s // just to make our life easier under MS-Windows
{
  u32 tm_sec;   // seconds after the minute - [0,59] 
  u32 tm_min;   // minutes after the hour   - [0,59] 
  u32 tm_hour;  // hours since midnight     - [0,23] 
  u32 tm_mday;  // day of the month         - [1,31] 
  u32 tm_mon;   // months since January     - [0,11] 
  u32 tm_year;  // years since 1900 
  u32 tm_wday;  // days since Sunday        - [0,6] 
  u32 tm_yday;  // days since January 1     - [0,365] 
  u32 tm_isdst; // daylight savings time flag 
} tm_t;

u32      cycles     (void); // return CPU clock cycles (in-minutes overflow)
u64      cycles64   (void); // return CPU clock cycles (will never overflow)
u64      getus      (void); // elapsed microseconds    (1 millisecond/1000)
u64      getms      (void); // elapsed miliseconds     (1 second/1000)

time_t   s_time     (void); // on Windows, much much faster than time(0);
tm_t    *s_gmtime   (time_t t, tm_t *ts); // those are thread-safe
tm_t    *s_localtime(time_t t, tm_t *ts);
char    *s_asctime  (time_t t, char *buf);

size_t   rfc2time   (char *s); // "Tue, 06 Jan 2009 06:12:20 GMT" => u32
char    *time2rfc   (time_t t, char *buf); // inverse of above operation

// ----------------------------------------------------------------------------
// random numbers
// ----------------------------------------------------------------------------
typedef struct { u32 x[5]; } prnd_t;
void sw_init(prnd_t *rnd, u32 seed); // pseudo-random numbers generator
u32  sw_rand(prnd_t *rnd);           // (period: 1 << 158)

typedef struct { u32 x[270340]; } rnd_t;
void hw_init(rnd_t *rnd); // hardware random numbers generator
u32  hw_rand(rnd_t *rnd); // (cache the context: hw_init() takes time)

// ----------------------------------------------------------------------------
// checksums
// ----------------------------------------------------------------------------
// u32 crc = 0;
// int i = 10;
// while(i--)
//    crc = crc32(data[i].ptr, data[i].len, crc);

u32 crc32  (char *data, u32 len, u32 crc);
u32 adler32(char *data, u32 len, u32 crc); // adler32 is slower than crc32

// ----------------------------------------------------------------------------
// hashing
// ----------------------------------------------------------------------------
// u8 dst[16]; // the resulting 128-bit hash
// md5_t ctx;
// md5_init(&ctx);
// int i = 10;
// while(i--)
//    md5_add(&ctx, data[i].ptr, data[i].len);
// md5_end(&ctx, dst);

typedef struct { u8 x[216]; } md5_t;
void md5_init(md5_t *ctx);
void md5_add (md5_t *ctx, u8 *src, int srclen);
void md5_end (md5_t *ctx, u8 *dst);
// a wrapper on all the above MD5 calls
void md5(u8 *input, int ilen, u8 *dst);

// u8 dst[20]; // the resulting 160-bit hash
// sha1_t ctx;
// sha1_init(&ctx);
// int i = 10;
// while(i--)
//    sha1_add(&ctx, data[i].ptr, data[i].len);
// sha1_end(&ctx, dst);

typedef struct { u8 x[220]; } sha1_t;
void sha1_init(sha1_t *ctx);
void sha1_add (sha1_t *ctx, u8 *src, int srclen);
void sha1_end (sha1_t *ctx, u8 *dst);
// a wrapper on all the above SHA-160 calls
void sha1(u8 *input, int ilen, u8 *dst);

// u8 dst[32]; // the resulting 256-bit hash
// sha2_t ctx;
// sha2_init(&ctx);
// int i = 10;
// while(i--)
//    sha2_add(&ctx, data[i].ptr, data[i].len);
// sha2_end(&ctx, dst);

typedef struct { u8 x[236]; } sha2_t;
void sha2_init(sha2_t *ctx);
void sha2_add (sha2_t *ctx, u8 *src, int srclen);
void sha2_end (sha2_t *ctx, u8 *dst);
// a wrapper on all the above SHA-256 calls
void sha2(u8 *input, int ilen, u8 *dst);

// ----------------------------------------------------------------------------
// encryption
// ----------------------------------------------------------------------------
// AES is the U.S. NIST FIPS PUB 197 standard (2001) developed by Belgians
// Joan Daemen & Vincent Rijmen and approved by the NSA. Useful to comply.

typedef struct aes_s
{
   u32 rounds;
   u32 *keys;
   u32 buf[68];
} aes_t;

// mode     - values 1:ENCRYPT, 0:DECRYPT
// keylen   - values 128, 192 or 256* 
//            (*) AES-128 is faster and safer than AES-256

void aes_init(aes_t *ctx, u32 mode, u8 *key, u32 keylen);

// mode     - values 1:ENCRYPT, 0:DECRYPT
// len      - length in bytes to process
// iv       - initialization vector (modified), declare: u8 iv[16];
// src      - source to encrypt
// dst      - destination (encrypted)
//
// Cipher-Block Chaining (CBC) has been invented by IBM in 1976:
// Each plaintext block is XORed with the previously encrypted block before 
// being encrypted. It makes all blocks dependent on all the previous blocks. 
// To make the ciphertext unique, an IV must be used for the first block.
// It makes encryption sequential (no parallelization) and the message
// requires padding to match the block size.
// A bit change in a plaintext affects all the ciphertext. A plaintext can 
// be recovered from 2 contiguous ciphertext blocks, which makes it possible 
// to parallelize decryption.

void aes_enc(aes_t *ctx, u32 mode, u32 len, u8 *iv, u8 *src, u8 *dst);

// ----------------------------------------------------------------------------
// compression
// ----------------------------------------------------------------------------
// LZJB is fast, safe (no overrun during decompression), and uses little CPU
// (this algorithm created by Oracle is used by Solaris in the ZFS file system)
// return dstlen

size_t lzjb_cmp(void *src, void *dst, size_t srclen, size_t dstlen);

// return the dstlen, 0 on error

size_t lzjb_exp(void *src, void *dst, size_t srclen, size_t dstlen);

// GZip is slow, unsafe and uses tons of CPU (but, hey, that's the standard)
// if(gzip != 0) then use the 'gzip' format, else use the 'zlib' format 
// (the new 'zlib' format is both slower and larger than the old 'gzip' format)
// return the dstlen, 0 on error

u32 zlib_cmp(char *src, u32 srclen, char *dst, u32 dstlen, char gzip);

// ----------------------------------------------------------------------------
// shared libraries (see also "#pragma link" in sqlite.c)
// ----------------------------------------------------------------------------
#define RTLD_LAZY   0x001
#define RTLD_NOW    0x002
#define RTLD_GLOBAL 0x100

void       *dlopen (const char *filename, int flag);
const char *dlerror(void);
void       *dlsym  (void *handle, char *symbol);
int         dlclose(void *handle);

// ============================================================================
// End of Header file
// ============================================================================
