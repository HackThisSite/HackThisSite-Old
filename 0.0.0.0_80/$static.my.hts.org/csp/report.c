// ============================================================================
// C servlet sample for the G-WAN Web Application Server (http://trustleap.ch/)
// ----------------------------------------------------------------------------
// report.c: create a server report, dumping G-WAN's internal counters:
// ----------------------------------------------------------------------------
// Server statistics 
//
// Local time: Sat, 07 May 2011 17:17:06 GMT
// Version 	 : G-WAN 2.1.20 (built: May  7 2011 19:16:56)
// Uptime 	 : 00 day(s) 00 month(s) 00 year(s) 00:00:03
// RAM       : 9.40 MB resident set size
//
// SYSTEM:
// Uptime 	: 00 day(s) 00 month(s) 00 year(s) 10:55:24 (99.26% Idle)
// Disk 	   : FREE: 397.64 GB (88.51%) / TOTAL: 449.22 GB
// RAM 	   : FREE: 2.15 GB (67.00%) / TOTAL: 3.22 GB
// Details 	: USED: 1.62 GB (33.00%), SHARED: 0 (0.00%), BUFFERS: 44.61 MB (1.36%)
// CPU(s) 	: 4 Intel(R) Xeon(R) CPU W3580 @ 3.33GHz (8 logical CPUs)
// Allowed 	: 8 Cores 
// Load 	   : 0.26 (1 min) 0.24 (5 min) 0.20 (15 min)
//
// TRAFFIC:
// Total 	: IN: 426 (142/sec)  OUT: 0 (0/sec)
// Daily 	: IN: 426 (142/sec)  OUT: 0 (0/sec)
// Today 	: IN: 426 (  0/sec)  OUT: 0 (0/sec)
//
// LISTENERS: 1
// 5 host(s): 192.168.12.88_8080
// Alias 	: 192.168.12.88:#trustleap.com
// Alias 	: 192.168.12.88:#gwan.ch
// Alias 	: 192.168.12.88:#gwan.com
// Root     : #192.168.12.88
// Virtual 	: $forum.trustleap.com
//
// REQUESTS:
// All Requests 	: 1 (0.00% of Cache misses)
// HTTP Requests 	: 0 (0.00% of all requests)
// HTTP Errors 	: 0 (0.00% of all requests)
// CSP Requests 	: 1 (100.00% of all requests) Exceptions: 0
// Stats Requests	: 0
//
// CONNECTIONS:
// Accepted : 1 (1.00 requests per connection)
// Closed 	: 0
// Timeouts : 0 (0.00%) Accept:0   Read:0   Slow:0   Build:0   Send:0   Close:0
// Busy 	   : 1
// Waiting 	: 0
// Reading 	: 0
// Replying : 1
// Sending 	: 0
// Closing 	: 0
//
// thread socket alive last_read timeout to_send IP state request
// 0 9 00:00:00 00:00:00 00:00:04 0 192.168.200.88 REPLY "GET /csp?report"
// ============================================================================
// imported functions:
//     get_reply(): get a pointer on the 'reply' dynamic buffer from the server
// server_report(): see below
// ----------------------------------------------------------------------------
#include "gwan.h" // G-WAN exported functions

int main(int argc, char *argv[])
{
   server_report(get_reply(argv), 1); // 0:text, 1:HTML

   return 200; // return an HTTP code (200:'OK')
}
// ============================================================================
// End of Source Code
// ============================================================================
