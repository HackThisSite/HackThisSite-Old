// ============================================================================
// C servlet header for the G-WAN Web Application Server (http://trustleap.ch/)
// ----------------------------------------------------------------------------
// xbuffer.h: a dynamically growing buffer, with handy functions
// ============================================================================

#ifndef _XBUFFER_H
#define _XBUFFER_H

#ifndef _SHORT_TYPES_H
# include "short_types.h"
#endif 

typedef struct _xbuf_t
{
	char *ptr;       // data buffer
	u32   allocated; // memory allocated
	u32   len;       // memory used
	u32   growby;    // memory allocation increment
} xbuf_t, xbuf_ctx; // xbuf_t is just a shorter alias for xbuf_ctx

void  xbuf_frfile  (xbuf_t *ctx, char *szFile);
void  xbuf_tofile  (xbuf_t *ctx, char *szFile);
u32   xbuf_growto  (xbuf_t *ctx, u32 len);
void  xbuf_empty   (xbuf_t *ctx);
char *xbuf_getend  (xbuf_t *ctx);
void  xbuf_attach  (xbuf_t *ctx, char *ptr, s32 size, s32 len);
char *xbuf_detach  (xbuf_t *ctx);
void  xbuf_free    (xbuf_t *ctx);
void  xbuf_clear   (xbuf_t *ctx);
void  xbuf_reset   (xbuf_t *ctx);
char *xbuf_ncat    (xbuf_t *ctx, char *pIn, s32 nInLength);
char *xbuf_cat     (xbuf_t *ctx, char *str);
char *xbuf_xcat    (xbuf_t *ctx, char *src, ...);
void  xbuf_sort    (xbuf_t *ctx, char Separator, s32 RemoveDuplicates);
s32   xbuf_findstr (xbuf_t *ctx, char *SearchValue);
s32   xbuf_repl    (xbuf_t *ctx, char *Search, char *New);
s32   xbuf_replfrto(xbuf_t *ctx, char *beg, char *end, char *Search, char *New);
void  xbuf_truncptr(xbuf_t *ctx, char *Ptr);
void  xbuf_trunclen(xbuf_t *ctx, s32 Len);
s32   xbuf_getln   (xbuf_t *ctx, char *pBuffer, s32 nSize);
s32   xbuf_pull    (xbuf_t *ctx, char *pBuffer, s32 nSize);
void  xbuf_delete  (xbuf_t *ctx, char *pos, s32 Len, char *bytes);
s32   xbuf_insert  (xbuf_t *ctx, char *pos, s32 Len, char *bytes);
s32   xbuf_http    (xbuf_t *ctx, s32 code, char *body);
s32   xbuf_frurl   (xbuf_t *ctx, char *host, u32 port, u32 method, char *uri, 
                    u32 mstimeout, char *headers);

#endif // _XBUFFER_H

// ============================================================================
// End of Source Code
// ============================================================================
