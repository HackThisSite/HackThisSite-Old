// ============================================================================
// C servlet sample for the G-WAN Web Application Server (http://trustleap.ch/)
// ----------------------------------------------------------------------------
// sqlite.c: using the SQLite library from a G-WAN C script
//
//    This sample provides HTML, JSON, TEXT, CSV and XML helpers illustrating 
//    how to extract SQLite records and use them in Web applications.
//
//    Note the #prama link and #pragma include used to seamlessly use shared 
//    or static library already installed on your system (without having to
//    copy the headers in gwan/include and the library in gwan/libraries if
//    they can be found elsewhere on your system).
// ----------------------------------------------------------------------------
// As SQLite is extremely sensitive to parameters quality (any glitch makes the
// library crash, potentially opening the door for attackers) great care must 
// be taken to avoid triggering those issues.
//
// Except sql_Exec(), all the wrappers use much safer *parameterized* queries
// like "INSERT INTO toons VALUES (?,?);" that protect against SQL injection:
//
//    SELECT * FROM toons WHERE name = $n; ($n= "titi'; DROP TABLE toons; --")
// => SELECT * FROM toons WHERE name = 'titi'; DROP TABLE toons;
//
// With pre-compiled parameterized requests, bad fields do NOT alter the query.
// Crashing the library is certainly possible, but at least an huge part of the
// SQLite surface of vulnerability (using data) is excluded from the equation.
// I am lacking (recent) experience with other SQL database engines but suspect
// that things are not better anywhere else. Therefore, the code below is more
// than pertinent. Use it freely.
// ----------------------------------------------------------------------------
// Comparing JSON to XML hurts: just look at the HTML output. JSON looks much
// better, use it rather than XML when you can.
// ============================================================================
// imported functions:
//   get_reply(): get a pointer on the 'reply' dynamic buffer from the server
//   xbuf_xcat(): like sprintf(), but appends to the specified dynamic buffer 
//    xbuf_cat(): like strcat(), but it works in the specified dynamic buffer 
//     log_err(): lets you dumpt text in the current virtual host's error.log
// ----------------------------------------------------------------------------
#pragma link "sqlite3"

#pragma include "./libraries/sqlite3"

#include <stdlib.h>
#include <stdio.h>

// with #include "./libraries/sqlite3/sqlite3.h" alone, sqlite3ext.h which is
// included by sqlite.h would be missing (that's why we need '#pragma include')

#include "sqlite3.h"
#include "gwan.h"

// ============================================================================
// error.log file helper (doing the formatting here lets you debug more easily)
// ----------------------------------------------------------------------------
void fmt_log_err(char *argv[], char *fmt, ...)
{
   char str[512] = {0}; // adjust as needed
   if(fmt)
   {
      va_list ap;
      va_start(ap, fmt); // if it crashes, look at the ... parameters
      s_vsnprintf(str, sizeof(str)-1, fmt, ap);
      va_end(ap);
   }

// printf("err:%s\n", str); // helping you to debug your servlet
   log_err(argv, str);
}
// ============================================================================
// SQLite helpers (all return an error status, zero means 'no error')
// ----------------------------------------------------------------------------
// 'bind' parameters to a *parameterized* SQL request

static int sql_build(sqlite3_stmt *stmt, char *fmt, va_list args)
{
   u8 ch = *fmt++;
   int param = 1; // sqlite parameters start from 1...

   while(ch)
   {
      //printf("ch :%c\n", ch);
      switch(ch)
      {
         case   0: return 1; // end of string, OK 
         // -------------------------------------------------------------------
         case '%': break;    // take the next char
         // --- integer -------------------------------------------------------
         case 'u':
         case 'd': 
         case 'i': 
            if(fmt[-1] == 'l' && fmt[-2] == 'l')
            {
               s64 ival = va_arg(args, long long);
               if(sqlite3_bind_int64(stmt, param++, ival))
                  return 0; // error
            }               
            else
            {
               int ival = va_arg(args, int);
               if(sqlite3_bind_int(stmt, param++, ival))
                  return 0; // error
            }               
            break;
         // --- float, double -------------------------------------------------
         case 'f': 
         case 'g':
         {
            double dval =  va_arg(args, double);
            sqlite3_bind_double(stmt, param++, dval);
               return 0; // error
            break;
         }
         // --- string, null --------------------------------------------------
         case 's': 
         {
            char *aval = va_arg(args, char*);
            //printf("aval:%s\n", aval);
            if(aval)
            {
               if(sqlite3_bind_text(stmt, param++, aval, -1, SQLITE_TRANSIENT))
                  return 0; // error
               break;
            }            
            if(sqlite3_bind_null(stmt, param++))
               return 0; // error
            break;
         }
      }

      ch = *fmt++;
   }
   return 1; // OK
}
// ============================================================================
// Usage:
//
//    sql_Exec(argv, db, "CREATE TABLE toons(id int, name text)");
//    sql_Exec(argv, db, "DROP TABLE toons");
//    sql_Exec(argv, db, "SELECT * FROM toons");
//    sql_Exec(argv, db, "DROP TABLE toons; SELECT * FROM toons;");
//    sql_Exec(argv, db, "INSERT INTO toons(name) VALUES('Tom')");
//    sql_Exec(argv, db, "UPDATE toons SET name='Jerry Khan' WHERE id=%u", 2);
//    sql_Exec(argv, db, "DELETE FROM toons WHERE name=%s", szName);
//    sql_Exec(argv, db, "BEGIN");
//    sql_Exec(argv, db, "COMMIT");
//    sql_Exec(argv, db, "ROLLBACK");
//    sql_Exec(argv, db, "ATTACH 'blah.db' AS blah");
//    sql_Exec(argv, db, "DETACH blah");
//
// Execute a command, no return expected except an error status
//
// *TO BE USED ONLY WITH SAFE PARAMETERS*
// (use  sql_Query() or  sql_QueryStep() for queries using potentially
//  unsafe parameters provided by end-users)

static int sql_Exec(char **argv, sqlite3 *db, char *sql, ...)
{
   char *req;
   va_list ap;
   va_start(ap, sql);
   req = sqlite3_vmprintf(sql, ap);
   va_end(ap);

   char *err = 0;         //         callback, args,
   int   ret = sqlite3_exec(db, req, NULL,     NULL, &err);
   if(ret) // Error, Busy, Permission, etc.
   {
      fmt_log_err(argv, ">> !sql_Exec:%d:%s\n", ret, err?err:"");
      if(err)
         sqlite3_free(err);
   }
   sqlite3_free(req);
   return ret;
}
// ----------------------------------------------------------------------------
// pre-defined output formats (TEXT, CSV, JSON, XML)
// ----------------------------------------------------------------------------
typedef struct fmt_s
{
   char *header, *footer,       
        *bol, *eol, // begin and end of line
        *prefix[2], *suffix[2];
}fmt_t;

fmt_t fmt_text = 
{ 
   "", "",
   "\t", "\n",
   {"\t", "\t"},
   {"\t", "\t"}
};

fmt_t fmt_csv = 
{ 
   "", "",
   "", "\n",
   {"", ""},
   {",", ","}
};

fmt_t fmt_html = 
{ 
   "<table class=\"clean\" width=200px>", "</table><br>",
   "<tr class=\"d%u\">", "</tr>",
   {"<th>", "<td>"},
   {"</th>", "</td>"}
};

// to support other layouts (JSON, XML), a *different order* is used, 
// which implies an alternate case in the code (see sqlQuery() below)
fmt_t fmt_json = 
{ 
   "[\n", "\n]\n",
   "   { ", " },\n",
   {"", "\"%s\": \""},
   {"", "\", "}
};

fmt_t fmt_xml  = 
{ 
   /* THIS IS THE NON-ESCAPED VERSION THAT YOU SHOULD USE FOR XML DOCS
   "<Object><Array>\n", "</Array></Object>\n",
   "<Object>\n  ", "</Object>\n",
   {"", "<Property><Key>%s</Key><String>"},
   {"", "</String></Property>\n  "} */
   
   // THIS ESCAPED VERSION IS JUST FOR DISPLAYING IN HTML PAGES
   "&lt;Object&gt;&lt;Array&gt;\n", "\n&lt;/Array&gt;&lt;/Object&gt;\n",
   "&lt;Object&gt;\n  ", "&lt;/Object&gt;\n",
   {"", "&lt;Property&gt;&lt;Key&gt;%s&lt;/Key&gt; &lt;String&gt;"},
   {"", "&lt;/String&gt;/Property&gt;\n  "}
};
// ============================================================================
// Usage:
//
// ret = sql_Query(argv, db, reply, fmt_text, 
//                 "SELECT name FROM toons WHERE id = ?", "%u", i);
//
// APPEND the result of a (simple, one-shot) query to a dynamic buffer
// (separator can be used to inject HTML tags; i.e.: to put VALUES in an array)

static int sql_Query(char **argv, sqlite3 *db, xbuf_t *result, 
                     fmt_t *f,
                     char *req,
                     char *arg, ...)
{
   sqlite3_stmt *stmt;
   int ret = sqlite3_prepare(db, req, -1, &stmt, 0);
   if(ret) // Error, Busy, Permission, etc.
   {
      fmt_log_err(argv, ">> !sql_prep:%d:%s\n", ret, sqlite3_errmsg(db));
      goto done;
   }

   if(arg)
   {
      va_list ap;
      va_start(ap, arg);
      int ret = sql_build(stmt, arg, ap);
      va_end(ap);
      if(!ret) // parameter binding error
         goto done;      
   }

   char *bol = f->bol;
   char *eol = f->eol;
   char **prefix = f->prefix;
   char **suffix = f->suffix;

   int ncols = sqlite3_column_count(stmt), i = 0, j = 0;

   xbuf_xcat(result, f->header);
   
   // text/html/csv differ from json/xml in the fact that they dump the
   // column headers ONCE, at the top of the buffer (instead on several
   // times, on each data line)
   if((f == &fmt_text) || (f == &fmt_html) || (f == &fmt_csv))
   {
      xbuf_xcat(result, bol, i & 1);
      
      // dump the column headers once for all
      j = 0;         
      while(j < ncols)
      {
         xbuf_xcat(result, "%s%s%s", 
                   prefix[0], 
                   sqlite3_column_name(stmt, j), 
                   suffix[0]);
         j++;
      }
      xbuf_cat(result, eol);

      while((ret = sqlite3_step(stmt)) == SQLITE_ROW)
      {
         xbuf_xcat(result, bol, i & 1);

         // copy all the requested VALUES in the buffer
         j = 0;         
         while(j < ncols)
         {
             xbuf_xcat(result, "%s%s%s", 
                       prefix[1], 
                       sqlite3_column_text(stmt, j), 
                       suffix[1]);
             j++;
         }

         xbuf_cat(result, eol);
         i++;
      }
   }
   else // we expect fmt_json or fmt_xml
   {
      char tmp[80];

      while((ret = sqlite3_step(stmt)) == SQLITE_ROW)
      {
         xbuf_cat(result, bol);
         
         // copy all the requested VALUES in the buffer
         j = 0;         
         while(j < ncols)
         {
            // dump the column headers on each data line
            s_snprintf(tmp, sizeof(tmp)-1, 
                       prefix[1], sqlite3_column_name(stmt, j));
                         
            xbuf_xcat(result, "%s%s%s", 
                      tmp, 
                      sqlite3_column_text(stmt, j),
                      suffix[1]);
            j++;
         }

         result->len -= 2; // erase last eol marker
         xbuf_cat(result, eol);
         i++;
      }
      result->len -= 2; // erase last eol marker
   }

   xbuf_cat(result, f->footer);      

   if(ret != SQLITE_DONE)
      fmt_log_err(argv, ">> !sql_Step:%d:%s\n", ret, sqlite3_errmsg(db));

done:
   sqlite3_finalize(stmt);
   return ret;
}
// ============================================================================
// Usage:
//
//    sql_QuerySteps(argv, db, 
//                   "SELECT * FROM toons WHERE id > ?", "%u", 1);
//                   or
//                   "SELECT name FROM toons");
//    ret = sql_GetStep(db, stmt, result);
//    while(ret == SQLITE_ROW)
//    {
//       printf(result);
//       ret = sql_GetStep(db, stmt, result);
//    }
//
// Prepare a query which result will be retrieved in multiple steps

static int sql_QuerySteps(char **argv, sqlite3 *db, sqlite3_stmt **stmt, 
                          char *req, 
                          char *arg, ...)
{
   int ret = sqlite3_prepare(db, req, -1, stmt, 0);
   if(ret) // Error, Busy, Permission, etc.
   {
      sqlite3_finalize(*stmt);
      fmt_log_err(argv, ">> !sql_Prep:%d:%s\n", ret, sqlite3_errmsg(db));
   }

   if(arg)
   {
      va_list ap;
      va_start(ap, arg);
      if(!sql_build(*stmt, arg, ap)) // parameter binding error
         sqlite3_reset(*stmt); // will make the following sql_GetStep() fail
      va_end(ap);
   }
   
   return ret;
}
// ----------------------------------------------------------------------------
// Usage:
//
//    sql_QuerySteps(argv, db, "SELECT * FROM toons");
//    ret = sql_GetStep(argv, db, stmt, reply);
//    while(ret == SQLITE_ROW)
//       ret = sql_GetStep(argv, db, stmt, ",", reply);
//
// APPEND to an xbuffer a step of a previously prepared multi-step query
// (separator can be used to inject HTML tags; i.e.: to put VALUES in an array)

static int sql_GetStep(char **argv, sqlite3 *db, sqlite3_stmt *stmt, 
                       char *separator, xbuf_t *result)
{
   int ret = sqlite3_step(stmt);
   if(ret == SQLITE_ROW)
   {
      int ncols = sqlite3_column_count(stmt), i = 0;

      /* get column headers
      while(i < ncols)
      {
          xbuf_xcat(result, "  Column: %s (%i/%s)\n", 
                  sqlite3_column_name(stmt, i),
                  sqlite3_column_type(stmt, i),
                  sqlite3_column_decltype(stmt, i));
          i++;
      }
      i = 0;
      */
      
      while(i < ncols) // copy all the requested VALUES in the buffer
      {
          xbuf_xcat(result, "%s%s", sqlite3_column_text(stmt, i), 
                   ((i + 1) < ncols) ? separator : "");
          i++;
      }
   }
   else // (ret == SQLITE_DONE || ret == SQLITE_ERROR)
   {
      sqlite3_finalize(stmt);

      if(ret == SQLITE_ERROR)
         fmt_log_err(argv, ">> !sql_Step:%d:%s\n", ret, sqlite3_errmsg(db));
   } 
   return ret;
}
// ============================================================================
// main
// ----------------------------------------------------------------------------
int main(int argc, char **argv)
{
   xbuf_t *reply = get_reply(argv);
   
   // build the top of our HTML page
   xbuf_cat(reply, "<!DOCTYPE HTML>"
      "<html lang=\"en\"><head><title>SQLite</title><meta http-equiv"
      "=\"Content-Type\" content=\"text/html; charset=utf-8\">"
      "<link href=\"imgs/style.css\" rel=\"stylesheet\" type=\"text/css\">"
      "</head><body style=\"margin:20px 16px; padding:8px;\">"
      "<h1>C servlet linked with the SQLite library</h1>");

   // -------------------------------------------------------------------------
   // no file harmed in this experiment (we create an in-memory table)
   // -------------------------------------------------------------------------
   sqlite3 *db;
   int ret = sqlite3_open(":memory:", &db); // "./test.db"
   if(ret || !db) 
   {
      if(db)
      {
         fmt_log_err(argv, ">> DB:!OpenSQL:%d:%s\n", ret, sqlite3_errmsg(db));
         sqlite3_close(db);
      }
      else
      {
         fmt_log_err(argv, ">> DB:!OpenSQL:%d:db=null\n", ret);
      }
      return 500;
   }

   sqlite3_busy_timeout(db, 2 * 1000); // limit the joy

   // -------------------------------------------------------------------------
   // create the db schema and add records
   // -------------------------------------------------------------------------
   {
      static char *TableDef[]=
      {
         "CREATE TABLE toons (id        int primary key,"
                             "stamp     int default current_timestamp,"
                             "rate      int,"
                             "name      text not null collate nocase unique,"
                             "photo     blob);",
         // you can add other SQL statements here, to add tables or records
         NULL
      };
      sqlite3_exec(db, "BEGIN EXCLUSIVE", 0, 0, 0);
      int i = 0;
      do
      { 
         if(sql_Exec(argv, db, TableDef[i])) 
         {
            sqlite3_close(db);
            return 503;
         }
      }
      while(TableDef[++i]);
   
      // add some records to the newly created table
      sql_Exec(argv, db, 
               "INSERT INTO toons(rate,name) VALUES(4,'Tom'); "
               "INSERT INTO toons(rate,name) VALUES(2,'Jerry'); "
               "INSERT INTO toons(rate,name) VALUES(6,'Bugs Bunny'); "
               "INSERT INTO toons(rate,name) VALUES(4,'Elmer Fudd'); "
               "INSERT INTO toons(rate,name) VALUES(5,'Road Runner'); "
               "INSERT INTO toons(rate,name) VALUES(9,'Coyote');");
      
      sqlite3_exec(db, "COMMIT", 0, 0, 0);

      // not really useful, just to illustrate how to use it
      xbuf_cat(reply, "<br><h2>SELECT COUNT(*) FROM toons (HTML Format):</h2>");
      sql_Query(argv, db, reply, &fmt_html, "SELECT COUNT(*) FROM toons;", 0);
   } 
   
   // -------------------------------------------------------------------------
   // run a query and append the (formatted) result to our server reply
   // -------------------------------------------------------------------------
   xbuf_cat(reply, 
            "<br><h2>SELECT rate, name FROM toons (Custom Format):</h2>");
   static char szcr[] = "<br>", szseparator[] = ", ";
   sqlite3_stmt *stmt;
   ret = sql_QuerySteps(argv, db, &stmt, "SELECT rate, name FROM toons;", 0);
   if(!ret) // no error
   {
      do 
      {  // format each line of data we fetch from the database
         ret = sql_GetStep(argv, db, stmt, szseparator, reply);
         
         // append line-ending HTML tag
         xbuf_ncat(reply, szcr, sizeof(szcr)-1);
      }
      while(ret == SQLITE_ROW);
   }

   // -------------------------------------------------------------------------
   // run the same query again, using pre-defined formats this time
   // -------------------------------------------------------------------------
   xbuf_cat(reply, 
            "<br><h2>SELECT rate, name FROM toons (TEXT format):</h2><pre>");
   sql_Query(argv, db, reply, &fmt_text, "SELECT rate, name FROM toons;", 0);
   xbuf_cat(reply, "</pre>");

   xbuf_cat(reply, 
            "<br><h2>SELECT rate, name FROM toons (HTML format):</h2>");
   sql_Query(argv, db, reply, &fmt_html, "SELECT rate, name FROM toons;", 0);

   xbuf_cat(reply, 
            "<br><h2>SELECT rate, name FROM toons (JSON format):</h2><pre>");
   sql_Query(argv, db, reply, &fmt_json, "SELECT rate, name FROM toons;", 0);
   xbuf_cat(reply, "</pre>");

   xbuf_cat(reply, 
            "<br><h2>SELECT rate, name FROM toons (XML format):</h2><pre>");
   sql_Query(argv, db, reply, &fmt_xml, "SELECT rate, name FROM toons;", 0);
   xbuf_cat(reply, "</pre>");

   // -------------------------------------------------------------------------
   // run a similar query again, but PARSE the result for further processing
   // -------------------------------------------------------------------------
   // define which characters we want to extract for strings - used by sscanf()
   // (without it, sscanf() would stop at the first space character in strings)
   // (here, we also parse the underscore, dash and single-quote characters)
   #define ALPHAB "%[abcdefghijklmnopqrstuvwxyz" \
                    "ABCDEFGHIJKLMNOPQRSTUVWXYZ '-_]"
   
   xbuf_cat(reply, 
            "<br><h2>SELECT rate, name FROM toons WHERE name LIKE 'T%'"
            " (PARSING RECORD):</h2>");
   u32 rate = 0;
   char name[40] = "T%";
   xbuf_t buf;
   xbuf_init(&buf);
   sql_Query(argv, db, &buf, &fmt_csv,
//           "SELECT rate, name FROM toons WHERE name = 'Tom';", 0);
             "SELECT rate, name FROM toons WHERE name LIKE ?;", "%s", name);

   rate = 0;  // clear rate
   *name = 0; // clear name
   
   if(buf.len)
   {                  
      // pass the column headers
      char *p = (char*)strchr(buf.ptr, '\n');

      if(p) // found it
      {
         p++; // pass the '\n' (each text/csv line ends with a '\n')

         // extract fields from the buf.ptr string (CSV format)
         if(sscanf(p, "%u," ALPHAB "%39s", &rate, name) > 0)
         {
            // reformat extracted fields in another (HTML) string
            xbuf_xcat(reply, 
                      "buffer: '%s'<br>"
                      "Fields(rate: %u, name: %s)<br>", 
                      buf.ptr, rate, name);
         }
      }
   }
   //xbuf_free(&buf); // not done with it yet, see below

   // -------------------------------------------------------------------------
   // same thing, extracting fields from several records in a loop this time
   // -------------------------------------------------------------------------
   xbuf_cat(reply, 
            "<br><h2>SELECT rate, name FROM toons WHERE rate > 4"
            " (PARSING TABLE):</h2>");
   rate = 4;
   ret = sql_QuerySteps(argv, db, &stmt, //"SELECT rate, name FROM toons;", 0);
         "SELECT rate, name FROM toons WHERE rate > ?;", "%u", rate);
   if(!ret) // no error
   {
      do 
      {  // format each line of data we fetch from the database
         //xbuf_empty(&buf);
         buf.len = 0, *buf.ptr = 0; // same as above call (easier to disculp)

         ret = sql_GetStep(argv, db, stmt, szseparator, &buf);
         if(buf.len)
         {
            // extract fields from the buf.ptr string
            sscanf((char*)buf.ptr, "%u," ALPHAB "%39s", &rate, name); 
            
            // reformat extracted fields in another (HTML) string
            xbuf_xcat(reply, 
                      "buffer: '%s'<br>"
                      "Fields(rate: %u, name: %s)<br><br>", 
                      buf.ptr, rate, name);
         }
      }
      while(ret == SQLITE_ROW);
   }

   xbuf_free(&buf); // we are done with it now, free memory
   xbuf_cat(reply, "</body><html>");

   // -------------------------------------------------------------------------
   // done with database   
   // -------------------------------------------------------------------------
   sqlite3_close(db);
   return 200;
}
// ============================================================================
// End of Source Code
// ============================================================================
