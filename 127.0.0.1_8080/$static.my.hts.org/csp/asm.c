// ============================================================================
// C servlet sample for the G-WAN Web Application Server (http://trustleap.ch/)
// ----------------------------------------------------------------------------
// asm.c: using (GCC-style) inline assembly in G-WAN C scripts
// 
// Note: gwan is currently a 32-bit process (even on Linux 64-bit)...
// ============================================================================
#include "gwan.h" // G-WAN exported functions
// ----------------------------------------------------------------------------
static inline void *my_memcpy(void *to, const void *from, size_t n)
{
   int d0, d1, d2;
   __asm__ __volatile__(
   "rep ; movsl\n\t"
   "testb $2,%b4\n\t"
   "je 1f\n\t"
   "movsw\n"
   "1:\ttestb $1,%b4\n\t"
   "je 2f\n\t"
   "movsb\n"
   "2:"
   : "=&c"(d0), "=&D"(d1), "=&S"(d2)
   : "0"(n/4), "q"(n), "1"((long)to), "2"((long)from)
   : "memory");
   return (to);
}
// ----------------------------------------------------------------------------
int main(int argc, char *argv[])
{
   char str[80], src[] = "G-WAN talks ANSI C (with inline ASM)."; 
   
   my_memcpy(str, src, sizeof(src)); // including ending null
   xbuf_cat(get_reply(argv), str);   // just to illustrate the point

   return 200; // return an HTTP code (200:'OK')
}
// ============================================================================
// End of Source Code
// ============================================================================

