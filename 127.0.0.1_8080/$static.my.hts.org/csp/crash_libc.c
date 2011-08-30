// ============================================================================
// C servlet sample for the G-WAN Web Application Server (http://trustleap.ch/)
// ----------------------------------------------------------------------------
// crash_libc.c: making it easy to find a crash location in the code helps
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
// Code   Pointer: b7f61da4
// Access Address: 0badc0de
//
// Registers     : EAX=00000030 CS=0073 EIP=b7f61da4 EFLGS=00010246
//                 EBX=00000248 SS=007b ESP=b68ddeec EBP=b68ddef4
//                 ECX=0badc0dd DS=007b ESI=0947e260 FS=c0100000
//                 EDX=00000000 ES=007b EDI=0badc0de CS=0073
//
// Call chain    :(line) PgrmCntr(EIP) RetAddress FramePtr(EBP) StackPtr(ESP)
//       strcpy():    -      b7f61da4   0947e236      b68ddef4      b68ddeec
//        crash():   44      0947e236   00000000      b68ddef4      b68ddeec
// 
// csp/crash_libc.c Execution failed.
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
   strcpy(dst, src); // outchtime

   char end[100]; // DEBUG MODE: helps to keep stack frames intact
}
// ----------------------------------------------------------------------------
int main(int argc, char *argv[])
{
   char beg[100]; // DEBUG MODE: helps to keep stack frames intact
   
   crash();    // choose your poison...
   
   char end[100]; // DEBUG MODE: helps to keep stack frames intact
   
   return 200; // return an HTTP code (200:'OK')
}
// ============================================================================
// End of Source Code
// ============================================================================
