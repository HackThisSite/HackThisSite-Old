// ============================================================================
// C servlet sample for the G-WAN Web Application Server (http://trustleap.ch/)
// ----------------------------------------------------------------------------
// crash_syscall.c: making it easy to find a crash location in the code helps
//          to fix bugs. They are displayed on the server console and can also
//          be found in the /logs/error.log file (enable log files by creating
//          the gwan/.../logs sub-folder).
//
//          When a crash happens, clients will be sent a 500:'Internal Error'
//          and the server will continue to run seamlessly.
//
//          This example just creates a crash, which will be handled gracefully
//          by G-WAN, producing something like the following report (the 'line'
//          numbers below are the line numbers in 'csp/crash_syscall.c'):
//
// Signal        : 11:invalid access to valid memory
// Signal src    : 1:SEGV_MAPERR
// errno         : 0
// Code   Pointer: 08073003
// Access Address: 0badc0de
//
// Registers     : EAX=0badc0de CS=0073 EIP=08073003 EFLGS=00010206
//                 EBX=00000248 SS=007b ESP=b75bb8f0 EBP=b75bb8f0
//                 ECX=096cdab0 DS=007b ESI=a23c9568 FS=c0100000
//                 EDX=ffffffff ES=007b EDI=a23c9568 CS=0073
//
// Call chain    :(line) PgrmCntr(EIP) RetAddress FramePtr(EBP) StackPtr(ESP)
//  s_vsnprintf():    -      08073003   08068471      b75bb8f0      b75bb8f0
//   s_snprintf():    -      08068471   096cda8c      b75bb8f0      b75bb8f0
//        crash():   45      096cda8c   00000000      b75bb90c      b75bb8f0
// 
// csp/crash_gwcall.c Execution failed.
// ----------------------------------------------------------------------------
// The char beg[]/end[] buffers prevent the stack from being erased by the
// invalid memory access, leaving the stack frames intact for investigation.
// Of course, those buffers are a waste of memory if no crash is happening
// so you should use them only when necessary.
// ============================================================================
void crash(void)
{
   char beg[100]; // DEBUG MODE: helps to keep stack frames intact

   char *src = "0123456789";
   char *dst = (char*)0xBADC0DE;
   s_snprintf(dst, 0xffffffff, src); // outchtime

   char end[100]; // DEBUG MODE: helps to keep stack frames intact
}
// ----------------------------------------------------------------------------
int main(int argc, char *argv[])
{
   char beg[100]; // DEBUG MODE: helps to keep stack frames intact
   
   crash();    // choose your poison...
   
   char end[100]; // DEBUG MODE: helps to keep stack frames intact
   return 200;
}
// ============================================================================
// End of Source Code
// ============================================================================
