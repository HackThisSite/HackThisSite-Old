// ============================================================================
// C servlet sample for the G-WAN Web Application Server (http://trustleap.ch/)
// ----------------------------------------------------------------------------
// crash.c: making it easy to find a crash location in the code helps immensely
//          to fix bugs. They are displayed on the server console and can also
//          be found in the /logs/error.log file (enable log files by creating
//          the gwan/.../logs sub-folder).
//
//          When a crash happens, clients will be sent a 500:'Internal Error'
//          and the server will continue to run seamlessly.
//
//          This example just creates a crash, which will be handled gracefully
//          by G-WAN, producing something like the following report (the 'line'
//          numbers below are the line numbers in the 'csp/crash.c' file):
//
// Exception      : c0000005 Write Access Violation
// Address        : 05f952df
// Access Address : 00000000
// 
// Registers      : EAX=0badc0de CS=001b EIP=05f952df EFLGS=00010206
//                  EBX=05f25eb0 SS=0023 ESP=017ae478 EBP=017ae478
//                  ECX=05f9530d DS=0023 ESI=05f25fc1 FS=003b
//                  EDX=00000000 ES=0023 EDI=017af5fc CS=001b
// 
// Call Chain     :(line) PgrmCntr(EIP) RetAddress FramePtr(EBP) StackPtr(ESP)
//         crash():    33     05f952df   05f95344      017ae478      017ae478
//          main():    38     05f95344   00416355      017ae494      017ae478
// 
// csp/crash.c Execution failed.
// ============================================================================
void crash(void)
{
   *((int*)(0)) = 0xBADC0DE; // write access violation
}
// ----------------------------------------------------------------------------
int main(int argc, char *argv[])
{
   crash();    // choose your poison...
   return 200; // return an HTTP code (200:'OK')
}
// ============================================================================
// End of Source Code
// ============================================================================
