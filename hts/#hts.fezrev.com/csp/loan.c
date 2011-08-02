// ============================================================================
// C servlet sample for the G-WAN Web Application Server (http://trustleap.ch/)
// ----------------------------------------------------------------------------
// loan.c: An AJAX Web application to help people calculate the cost of loans
//
//            When clients use the loan.html form, we use the form fields to 
//            build new content which is then inserted into the original page
//            (the javascript code in loan.html will tell you how AJAX works).
//
//            GET and POST forms are processed with the SAME code (G-WAN does
//            the URL/Entity parsing for C servlets).
//
//            POST is often prefered to GET because the parameters are passed
//            in the URL (so they are visible) while POST is using the request
//            entity. This difference is *not* relevant in our case because we
//            use AJAX to process the query (as a result, the URL is not seen
//            by end-users in the Web browser 'address' field).
//
//       Tip: When using XMLHttpRequest (AJAX), Web browsers split POST in two
//            steps: they send HTTP headers and the query separately.
//            As GET takes only one step then you can save a connection by not 
//            using POST in your forms -enhancing your web server scalability.
//            (this is an efficient way to boost AJAX graphic user interfaces)
//
//            Using AJAX here saves you from using mod_rewrite to have "normal"
//            search-friendly URLs while taking advantage of servlets.
//
//            One of the most important things to keep in mind here is to make 
//            sure that clients will not lock-up your code with bad input data.
//            The motto is 'filter'. You can also do it on the client side with
//            javascript -but don't count on it. Filter again in your servlets
//            because you just cannot trust what the network brings you (IPSec
//            and SSL make absolutely no difference in this matter).
//
//     Notes: xbuf_xcat() will insert ',' every 3 digits if you use uppercase
//            formatters like: "%D", "%U", "%F" (instead of "%d", "%u", "%f").
//            You can use the ' standard formater as well (I don't like it).
//
//            To format s64/u64 values, use "%llu"/"%lld".
//
//            Not using math.h (fabs, ceil, pow) or string.h (atod) gives you 
//            more control on what your code is doing and avoids system calls
//            overhead. Of course you can use any library you want with G-WAN.
//            This example only illustrates how fun *real* programming can be
//            (as opposed to 'copy & paste' or, even worse, 'button pushing').
//
//            I have been told that loan.php is shorter. That's true, but the
//            PHP code does not even do half of what this C code does, it is 
//            much less commented -and is much slower than loan.c.
// ============================================================================
#include "gwan.h" // G-WAN exported functions
// ----------------------------------------------------------------------------
// imported functions:
//   get_reply(): get a pointer on the 'reply' dynamic buffer from the server
//   xbuf_ncat(): like strncat(), but in the specified dynamic buffer 
//   xbuf_xcat(): formatted strcat() (a la printf) in a given dynamic buffer 
//     get_arg(): get the specified form field value
// escape_html(): translate HTML tags into harmless escaped sequences
// ----------------------------------------------------------------------------
// like atof() -but here you can *filter* input strings ("%16.2f" here)
// ----------------------------------------------------------------------------
long double atod(char *p)
{
	u32 sign = 0;
   while(*p == ' ') p++; // pass space characters
   switch(*p)
   {
      case '-': sign=1;
      case '+': p++;
   }
   
   long double d = 0.0, factor = 0.1;
   u32 v, i = 16; // i:the maximum integral part we want to scan
   while(i && (v=*p) && v >= '0' && v <= '9') // integer part
      p++, d = 10. * d + (v - '0'), i--;
	
   if(*p == '.' || *p == ',') // fractional part
   {
		p++; i = 2; // i:the maximum fractional precision we want to scan
		while(i && (v=*p) && v >= '0' && v <= '9')
		   p++, d += (v - '0') * factor, factor *= 0.1, i--;
	}
	return sign?-d:d;   
}
// ----------------------------------------------------------------------------
// return the smallest integer greater than 'x'
// ----------------------------------------------------------------------------
static inline u32 uceil(double x)
{
   u32 ipart = (int)x;
   if(x - ipart) return(x < 0.0) ? ipart : ipart + 1;
   else          return(ipart);
}
// ----------------------------------------------------------------------------
// raise 'x' to the power of 'p'
// ----------------------------------------------------------------------------
static inline long double powd(long double x, u32 p)
{
   switch(p)
   {
      case 0: return 1;
      case 1: return x;
   }

   long double res = 1;
   do
   {  // for each bit of power 'p', multiply 'res' by the x^bit factor
      if(p & 1)    //                               [8421]
         res *= x; // x^10 = x^8 * x^2 (power=10 is "1010")

      x *= x, p >>= 1; // get next 'p' bit
   } 
   while(p); // if(p==1024) 10 iterations, instead of: while(p--) x *= x;
   return res;
}
// ----------------------------------------------------------------------------
// main() is receiving the query parameters ("csp?arg1&arg2&arg3...") in argv[]
// ----------------------------------------------------------------------------
int main(int argc, char *argv[])
{
   // create a dynamic buffer and get a pointer on the server response buffer
   xbuf_t *reply = get_reply(argv);

   // -------------------------------------------------------------------------
   // no query parameters were provided, redirect client to "loan.html"
   // -------------------------------------------------------------------------
   if(argc < 2)
   {
      static char redir[] = "HTTP/1.1 302 Found\r\n"
         "Content-type:text/html\r\n"
         "Location: loan.html\r\n\r\n"
         "<!DOCTYPE HTML PUBLIC \"-//IETF//DTD HTML 2.0//EN\">"
         "<html><head><title>Redirect</title></head><body>"
         "Click <a href=\"loan.html\">HERE</A> for redirect.</body></html>";

      xbuf_ncat(reply, redir, sizeof(redir) - 1);
      return 302; // return an HTTP code (302:'Found')
   }
   
   // -------------------------------------------------------------------------
   // if we have query parameters, we process a GET/POST form (the same way)
   // -------------------------------------------------------------------------
   char *Months[] = {"January", "February", "March", "April", "May",
                     "June", "July", "August", "September", "October",
                     "November", "December"};
   char szName[400] = {0};
   double amount, down, rate, term, payment, interest, principal, cost;
   int month = 0, year = 1, lastpayment = 1;

   // the form field "names" we want to find values for 
   char *Name = "", *Amount = "0", *Down = "0", *Rate = "0", *Term = "0";
   //u64 start = cycles64(); // current CPU clock cycles' counter
   u64 start = getus(); // elapsed micro-seconds (1 us = 1,000 milliseconds)

   // get the form field values (note the ending '=' name delimiter)
   get_arg("name=",   &Name,   argc, argv);
   get_arg("amount=", &Amount, argc, argv);
   get_arg("rate=",   &Rate,   argc, argv);
   get_arg("down=",   &Down,   argc, argv); // amount/percentage paid upfront
   get_arg("term=",   &Term,   argc, argv);

   // all litteral strings provided by a client must be escaped this way
   // if you inject them into an HTML page
   escape_html(szName, Name, sizeof(szName) - 1);

   // filter input data to avoid all the useless/nasty cases
   amount = atod(Amount);
   if(amount < 1)
      amount = 1;

   down = atod(Down);
   if(down > 0)
   {
      if(down < 100) // convert the percentage into an amount
         down = amount * (down / 100.);

      amount -= down;
   }
   
   rate = atod(Rate);
   if(rate > 1)
   {
      if(rate > 19) // limit the damage...
         rate = 20;
         
      rate /= 100;
   }
   else
   if(rate > 0)
      rate = 1. / 100.;
   else
      rate = .000001;
   
   term = atod(Term);
   if(term < 0.1)
      term = 1. / 12.;
   else 
   if(term > 800) term = 800;

   // calculate the monthly payment amount
   payment = amount * (rate/12 / (1 - powd(1 / (1 + rate/12), term*12)));
   cost = (term * 12 * payment) - amount;

   // build the top of our HTML page
   xbuf_xcat(reply, "<!DOCTYPE HTML PUBLIC \"-//IETF//DTD HTML 2.0//EN\">"
      "<html lang=\"en\"><head><title>Loan Calculator</title><meta http-equiv"
      "=\"Content-Type\" content=\"text/html; charset=utf-8\">"
      "<link href=\"imgs/style.css\" rel=\"stylesheet\" type=\"text/css\">"
      "</head><body style=\"background:#fff;\"><h2>"
      "Dear %s, your loan goes as follows:</h2>", 
      (*szName && *szName != '-')?szName:"client");  

   xbuf_xcat(reply, "<br><table class=\"clean\" width=240px>"
      "<tr><th>loan</th><th>details</th></tr>"
      "<tr class=\"d1\"><td>Amount</td><td>%.2F</td></tr>"
      "<tr class=\"d0\"><td>Rate</td><td>%.2F%%</td></tr>"
      "<tr class=\"d1\"><td>Term</td><td>%u %s(s)</td></tr>"
      "<tr class=\"d0\"><td>Cost</td><td>%.2F (%.2F%%)</td></tr>"
      "</table>", amount, rate * 100,
            ((u32)term)?((u32)term):uceil(12 * term),
            ((u32)term)?"year":"month", cost, 100 / (amount / cost));

   xbuf_xcat(reply, "<br><table class=\"clean\" width=112px>"
      "<tr class=\"d1\"><td><b>YEAR %u</b></td></tr></table>"
      "<table class=\"clean\" width=550px>"
      "<tr><th>month</th><th>payment</th><th>interest</th>"
      "<th>principal</th><th>balance</th></tr>", year);

   for(;;) // output monthly payments
   {
      month++;
      interest = (amount * rate) / 12;

      if(amount > payment)
      {
         principal = payment - interest;
         amount -= principal;
      }
      else // calculate last payment
      {
         if(lastpayment)
         {
            lastpayment = 0;
            payment = amount;
            principal = amount - interest;
            amount = 0;
         }
         else // all payments are done, just padd the table
         {
            amount = 0;
            payment = 0;
            interest = 0;
            principal = 0;
         }
      }

      xbuf_xcat(reply,
               "<tr class=\"d%u\"><td>%s</td><td>%.2F</td><td>%.2F</td>"
               "<td>%.2F</td><td>%.2F</td></tr>", 
               month & 1, Months[month - 1], 
               payment, interest, principal, amount);

      if(month == 12)
      {
         if(amount)
         {
            month = 0;
            year++;
            xbuf_xcat(reply,
                     "</table><br><table class=\"clean\" width=112px>"
                     "<tr class=\"d1\"><td><b>YEAR %u</b></td></tr></table>"
                     "<table class=\"clean\" width=550px>"
                     "<tr><th>month</th><th>payment</th><th>interest</th>"
                     "<th>principal</th><th>balance</th></tr>", year);
         }
         else
            break;
      }
   }

   // -------------------------------------------------------------------------
   // time the process and close the HTML page
   /* -------------------------------------------------------------------------
   xbuf_xcat(reply,
            "</table><br>This page was generated in %llU CPU clock cycles."
            "<br>(on a 3GHz CPU 1 ms = 3,000,000 cycles)"
            "<br></body></html>", cycles64() - start);*/
   xbuf_xcat(reply,
            "</table><br>This page was generated in %.2F ms."
            "<br>(on a 3GHz CPU 1 ms = 3,000,000 cycles)"
            "<br></body></html>", (double)(getus() - start)/1000.);

   return 200; // return an HTTP code (200:'OK')
}
// ============================================================================
// End of Source Code
// ============================================================================
