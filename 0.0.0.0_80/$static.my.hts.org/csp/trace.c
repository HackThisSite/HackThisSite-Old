// ============================================================================
// C servlet sample for the G-WAN Web Application Server (http://trustleap.ch/)
// ----------------------------------------------------------------------------
// trace.c: just to see how easy it is to trace your code 
//          (showing both function names and line numbers with your variables)
// ============================================================================
#include "gwan.h"    // G-WAN exported functions
// ----------------------------------------------------------------------------
#define USE_TRACE // comment this to disable dumps

#ifdef USE_TRACE
# define TRACE(fmt,...) printf("%s:%d] "fmt, __func__,__LINE__,__VA_ARGS__)
#else
# define TRACE
#endif
// ----------------------------------------------------------------------------
void foo(void)
{
   // this is displayed in the G-WAN Terminal window
   int i = 2; TRACE("i = %d\n", i);
}
// ----------------------------------------------------------------------------
int main(int argc, char *argv[])
{
   xbuf_t *reply = get_reply(argv);

   // this is displayed in the G-WAN Terminal window
   int i = 1; TRACE("i = %d\n", i);

   foo();

   // this is displayed in the Internet Browser
   xbuf_xcat(reply, 
             "function: %s (line: %d)<br><br>"
             "C script loaded on: %s %s", 
             __func__, __LINE__, 
             __DATE__, __TIME__);

   return 200; // return an HTTP code (200:'OK')
}
// ============================================================================
// End of Source Code
// ============================================================================
