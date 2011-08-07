// ============================================================================
// C servlet sample for the G-WAN Web Application Server (http://trustleap.ch/)
// ----------------------------------------------------------------------------
// exit.c: test the angel process (it restarts the child gwan process if G-WAN
//         is running in 'daemon' mode, with the -d command-line switch)
//
// Note: this also can be used to force a reload of G-WAN's listeners/hosts 
//       from a C script (after you modified them, for example).
// ============================================================================
#include "gwan.h"    // G-WAN exported functions

int main(int argc, char *argv[])
{
   exit(3);
   return 4;
}
// ============================================================================
// End of Source Code
// ============================================================================
