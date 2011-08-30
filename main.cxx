// ============================================================================
// This is a Maintenance script for the G-WAN Web Server (http://trustleap.ch)
// ----------------------------------------------------------------------------
// main.c: do whatever you need to do here, like backups, alerts, etc.
// ============================================================================
#include "gwan.h"    // G-WAN exported functions

int main(int argc, char *argv[])
{
   char *pdate  = 0; get_env(argv, SERVER_DATE, &pdate);
   u64 requests = 0, old_requests = 0;
   for(;;)
   {
      // we watch the server activity and could send an alert (pager, email)
      // if the server is reaching a given load
      sleep(5);
      requests = get_env(argv, CC_REQUESTS, 0);
      if(requests != old_requests)
      {
         old_requests = requests;
         printf("%s number of requests:%llu\n", pdate, requests);
      }
   }
   return(0);
}
// ============================================================================
// End of Source Code
// ============================================================================
