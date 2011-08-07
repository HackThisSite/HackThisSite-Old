// ----------------------------------------------------------------------------
#define TEST_SQLITE       // usually pre-installed on Linux
#define TEST_GWAN_KV      // available in G-WAN 2.7+
#define TEST_TC           // install Tokyo Cabinet 32-bit to test it
#define TEST_TC_FIXED     // install Tokyo Cabinet 32-bit to test it
// ----------------------------------------------------------------------------
// if(NBR_ITEMS >= 10,000) TC and TC-FIXED die with "threading errors"...
// ----------------------------------------------------------------------------
#define NBR_ITEMS 1000 
#define ITEM_SIZE   12
#define NBR_ROUNDS  10.0 // using a double increases the precision
#define TOTAL_OP    (NBR_ROUNDS * NBR_ITEMS)
// ============================================================================
// C servlet sample for the G-WAN Web Application Server (http://trustleap.ch/)
// ----------------------------------------------------------------------------
// kv_bench.c: G-WAN's (lock-free) Key-Value Store vs:
//                               + SQLite (in-memory B-Tree tables)
//                               + Tokyo Cabinet "TC-FIXED" (an array)
//                               + Tokyo Cabinet "TC" (hash tables)
//
// This is similar in spirit to the official Tokyo Cabinet comparative test:
// http://tokyocabinet.sourceforge.net/benchmark.pdf
// but more things are tested here and a clear distinction is made between
// table traversal and lexical-order/random keys searches or bulk 'deletes'.
//
// The official Tokyo Cabinet benchmark also opens TC in WRITE or READ modes
// (see /tc/bros/tc.testc) but never in READ-WRITE mode to avoid lock issues 
// (this makes it shine in a test, but most real-life applications need both
// READ and WRITE access to a Database table or KV store).
//
//    At NBR_ITEMS >= 10,000 TC & TC-FIXED die with many "threading errors"
//    (locking issues). And we are merely using a single-thread test here...
//
//    TC Flags (FDBONOLCK:no lock, FDBOLCKNB:async. locks) make no difference.
//    The TC documentation explains: "while a writing thread is operating the 
//    database, other reading threads and writing threads are blocked".
//
//    By contrast, G-WAN's KV store never blocks nor it delays any operation,
//    delivering constant-time processing (the "wait-free" Holy-Grail of DBMS).
//
// Comparing the TC-FIXED array (its 'persistance' takes place AFTER I/O)
// to an (in-memory B-Tree) SQL table is a good way to check how the most 
// rudimentary technology (an array) compares to more sophisticated tools
// like SQLite, TC (HashDB) or G-WAN's KV store (which can store variable
// -size keys and values, something that the TC-FIXED array cannot do).
//
// Database Engine        Total time  G-WAN is x times faster
// ---------------------  ----------  -----------------------
// SQLite   (B-tree)          82.286        213.231
// TC       (hash-table)       8.039         20.831
// TC-FIXED (an array)         0.926          2.400
// G-WAN    (a KV store)       0.386          1.000
// ------------------------------------------------------------------
// TC-FIXED "Wipe All" time commented in the code: I/O is done there
// (Ubuntu 8.1 32-bit, Intel Xeon W3580 4-Core 3.33GHz used @ 1.6 GHz)
//
// Not all Key-Value stores are equal under the light. Nor worth considering
// if your Web applications must serve more than one client at a time...
// ============================================================================
#ifdef TEST_SQLITE
# pragma link "sqlite3" // link with "/xxx/[libsqlite3.so].0"
#endif

#if defined(TEST_TC) || defined(TEST_TC_FIXED)
# pragma link "tokyocabinet"
#endif

#pragma include "./libraries/sqlite3"   // SQLite headers' PATH
#pragma include "./libraries/tkcabinet" // Tokyo Cabinet headers' PATH

#include <stdlib.h>
#include <stdio.h>

#ifdef TEST_SQLITE
# include "sqlite3.h"                       // SQLite headers
#endif

#if defined(TEST_TC) || defined(TEST_TC_FIXED)
# include "tcutil.h"                        // Tokyo Cabinet
# include "tcfdb.h"                         // Tokyo Cabinet Fixed-Length
# include "tchdb.h"                         // Tokyo Cabinet Hash Tables
#endif

#include "gwan.h"                           // G-WAN headers

#ifdef TEST_SQLITE
// ============================================================================
// Usage:
//
//    sql_Exec(db, "CREATE TABLE toons(id int, name text)");
//    sql_Exec(db, "DROP TABLE toons");
//    sql_Exec(db, "SELECT * FROM toons");
//    sql_Exec(db, "DROP TABLE toons; SELECT * FROM toons;");
//    sql_Exec(db, "INSERT INTO toons(name) VALUES('Tom')");
//    sql_Exec(db, "UPDATE toons SET name='Jerry Khan' WHERE id=%u", 2);
//    sql_Exec(db, "DELETE FROM toons WHERE name=%s", szName);
//    sql_Exec(db, "BEGIN");
//    sql_Exec(db, "COMMIT");
//    sql_Exec(db, "ROLLBACK");
//    sql_Exec(db, "ATTACH 'blah.db' AS blah");
//    sql_Exec(db, "DETACH blah");
//
// Execute a command, no return expected except an error status
//
// *TO BE USED ONLY WITH SAFE PARAMETERS*
// (use  sql_Query() or  sql_QueryStep() for queries using potentially
//  unsafe parameters provided by end-users)
// ----------------------------------------------------------------------------
static inline int sql_Exec(sqlite3 *db, char *sql, ...)
{
   char req[256];
   va_list ap;
   va_start(ap, sql);
   vsnprintf(req, sizeof(req)-1, sql, ap);
   va_end(ap);

   char *err = 0;         //         callback, args,
   int   ret = sqlite3_exec(db, req, NULL,     NULL, &err);
   return ret;
}
#endif
#ifdef TEST_GWAN_KV
// ============================================================================
// user-defined function executed when we process a G-WAN KV store
// ----------------------------------------------------------------------------
// return:
//  1: success: visited all matching entries
//  2: no entry starts with the 'key' string
//
// It must return 1 to continue searching (any other value stops the search)
// You can use the 'user_defined_ctx' context to store a structure for 
// counters, data collection, etc.
// ----------------------------------------------------------------------------
static int kv_nop(const kv_item *item, const void *my_ctx)
{
   return 1; // continue processing (any other value stops processing)
}
#endif
// ============================================================================
// main
// ----------------------------------------------------------------------------
int main(int argc, char **argv)
{
   u64 start, insert = 0, rinsert = 0, rupdate = 0, visitall = 0, search = 0, 
       rsearch = 0, delete = 0, wipeall = 0;

   // -------------------------------------------------------------------------
   // as the test may last forever, we enlarge the script time-out to avoid
   // being interrupted by G-WAN (Note: if we have to be interrupted, then 
   // this new timeout defined here will be effective only the SECOND time
   // this script is executed: timeouts are setup BEFORE the script is run)
   // -------------------------------------------------------------------------
   u32 *pscripttmo = 0; get_env(argv, SCRIPT_TMO, (char**)&pscripttmo);
   if(pscripttmo) // before you dereference a pointer...
     *pscripttmo = 20 * 1000; // 20 seconds
   
   // -------------------------------------------------------------------------
   // build the top of our HTML page
   // -------------------------------------------------------------------------
   xbuf_t *reply = get_reply(argv);
   xbuf_cat(reply, "<!DOCTYPE HTML>"
      "<html lang=\"en\"><head><title>SQLite vs Tokyo Cabinet (TC) "
      "vs G-WAN</title>"
      "<meta http-equiv=\"Content-Type\" content=\"text/html; charset=utf-8\">"
      "<link href=\"imgs/style.css\" rel=\"stylesheet\" type=\"text/css\">"
      "</head><body style=\"margin:16px;\"><h2>Testing SQLite vs. "
      "Tokyo Cabinet vs. G-WAN's KV store</h2><br>");
            
   // -------------------------------------------------------------------------
   // create and fill an array of pre-formated NBR_ITEMS numeric strings
   // -------------------------------------------------------------------------
   // this is done to wave the cost of number formating from the benchmark,
   // as well as to show what this cost is as compared to KV operations
   char *array = (char*)malloc(ITEM_SIZE * (NBR_ITEMS + 4));
   char *keyval = array;
   if(!array)
      return 500; // out of memory
   
   u64 start = getus();
   int i = 0;
   while(i < NBR_ITEMS + 4) // +2 items for Tokyo Cabinet (no "0" record...)
   {
      sprintf(keyval, "%08d", i++);
      keyval += ITEM_SIZE; // next entry in array
   }
   start = getus() - start;

   xbuf_xcat(reply,
            "<h4>Preparing data for "
            "%U rounds * %U items = %U operations per engine</h4>"
            "<b style=\"color:#ff4040\">"
            "Tokyo Cabinet fails because of locking errors at 10,000+ items"
            " because TC databases can be either be opened for READ or WRITE" 
            " mode but not both.</b><br>"
            "sprintf() Overhead: %U entries processed in %.5F ms<br>",
            (u32)NBR_ROUNDS, NBR_ITEMS, (u32)TOTAL_OP,
            NBR_ITEMS,
            (double)start / 1000.0);
      
   // -------------------------------------------------------------------------
   // calculate the random numbers generation overhead
   // -------------------------------------------------------------------------
   prnd_t rnd; // pseudo-random numbers generator (period: 1 << 158)
   sw_init(&rnd, 123456);
   i = NBR_ITEMS;
   u64 rnd_cost = getus();
   while(i--)
   {
      keyval = array + (ITEM_SIZE * (sw_rand(&rnd) % NBR_ITEMS));
   }
   rnd_cost = getus() - rnd_cost;
   xbuf_xcat(reply,
            "Random Overhead: %U entries processed in %.5F ms<br><br>",
            NBR_ITEMS,
            (double)rnd_cost / 1000.0);
   int rounds;
      
      
#ifdef TEST_SQLITE      
   // =========================================================================
   // SQLite
   // -------------------------------------------------------------------------
   // no file harmed in this experiment (we create an in-memory table)
   // -------------------------------------------------------------------------
   sqlite3 *db;
   i = sqlite3_open(":memory:", &db); // "./test.db"
   if(i || !db) 
      return 500;

   // -------------------------------------------------------------------------
   // create the db table and setup the SQLite cache
   // -------------------------------------------------------------------------
   {
      static char *TableDef[]=
      {
         "CREATE TABLE numb (key     TEXT PRIMARY KEY,"
                            "value   TEXT);",
         NULL
      };
      sqlite3_exec(db, "BEGIN EXCLUSIVE", 0, 0, 0);
      i = 0;
      do
      { 
         if(sql_Exec(db, TableDef[i])) 
         {
            sqlite3_close(db);
            return 503;
         }
      }
      while(TableDef[++i]);
   
      sqlite3_exec(db, "COMMIT", 0, 0, 0);
      
      // give some air to SQLite (at least, this is the intent)
      sql_Exec(db, "PRAGMA cache_size = %d;", NBR_ITEMS * ITEM_SIZE);
   } 

   //puts("SQLite");
   
   // -------------------------------------------------------------------------
   // a loop is necessary to reduce the very highly variable results
   // -------------------------------------------------------------------------
   rounds = NBR_ROUNDS;
   while(rounds--)
   {
      // ----------------------------------------------------------------------
      // Add records to the newly created table
      // ----------------------------------------------------------------------
      keyval = array;
      start = getus();
      i = NBR_ITEMS;
      while(i--)
      {
         sql_Exec(db, "INSERT INTO numb(key, value) VALUES(%s, %s);", 
                  keyval, keyval);
         keyval += ITEM_SIZE;
      }
      insert += getus() - start;
      
      // ----------------------------------------------------------------------
      // visit all the KV Store (in physical order)
      // ----------------------------------------------------------------------
      sqlite3_stmt *stmt;
      sqlite3_prepare(db, "SELECT * FROM numb;", -1, &stmt, 0);
      start = getus();
      while((i = sqlite3_step(stmt)) == SQLITE_ROW)
         ;
      visitall += getus() - start;
      sqlite3_finalize(stmt);
      if(i != SQLITE_DONE)
         printf("> !sql_Step:%d\n", i);
      
      // ----------------------------------------------------------------------
      // Search all the keys that we have inserted
      // ----------------------------------------------------------------------
      keyval = array;
      start = getus();
      i = NBR_ITEMS;
      while(i--)
      {
         sql_Exec(db, "SELECT * FROM numb WHERE key = '%s';", keyval);
         keyval += ITEM_SIZE;
      }
      search += getus() - start;
      
      // ----------------------------------------------------------------------
      // Search again, but in random order
      // ----------------------------------------------------------------------
      sw_init(&rnd, 123456);
      start = getus();
      i = NBR_ITEMS;
      while(i--)
      {
         keyval = array + (ITEM_SIZE * (sw_rand(&rnd) % NBR_ITEMS));
         sql_Exec(db, "SELECT * FROM numb WHERE key = '%s';", keyval);
      }
      rsearch += (getus() - start) - rnd_cost;

      // ----------------------------------------------------------------------
      // Remove all records, one by one
      // ----------------------------------------------------------------------
      keyval = array;
      start = getus();
      i = NBR_ITEMS;
      while(i--)
      {
         sql_Exec(db, "DELETE * FROM numb WHERE key = '%s';", keyval);
         keyval += ITEM_SIZE;
      }
      delete += getus() - start;
      
      // ----------------------------------------------------------------------
      // insert records again, but in random order
      // ----------------------------------------------------------------------
      sw_init(&rnd, 123456);
      keyval = array;
      start = getus();
      i = NBR_ITEMS;
      while(i--)
      {
         keyval = array + (ITEM_SIZE * (sw_rand(&rnd) % NBR_ITEMS));
         sql_Exec(db, "INSERT INTO numb(key, value) VALUES(%s, %s);", 
                  keyval, keyval);
      }
      rinsert += (getus() - start) - rnd_cost;

      // ----------------------------------------------------------------------
      // update all records, in random order
      // ----------------------------------------------------------------------
      sw_init(&rnd, 123456);
      keyval = array;
      start = getus();
      i = NBR_ITEMS;
      while(i--)
      {
         keyval = array + (ITEM_SIZE * (sw_rand(&rnd) % NBR_ITEMS));
         sql_Exec(db, "UPDATE numb SET value='%s' WHERE key='%s';", 
                  keyval, keyval);
      }
      rupdate += (getus() - start) - rnd_cost;

      // ----------------------------------------------------------------------
      // Remove all records - in one single call
      // ----------------------------------------------------------------------
      start = getus();
      i = NBR_ITEMS;
      while(i--)
      {
         sql_Exec(db, "DELETE * FROM numb;");
      }
      wipeall += getus() - start;
   }
   // -------------------------------------------------------------------------
   // done with database   
   // -------------------------------------------------------------------------
   sqlite3_close(db);

   // -------------------------------------------------------------------------
   // calculate results
   // -------------------------------------------------------------------------
   double sql_insert  = (insert   / NBR_ROUNDS) / 1000.0;
   double sql_rinsert = (rinsert  / NBR_ROUNDS) / 1000.0;
   double sql_rupdate = (rinsert  / NBR_ROUNDS) / 1000.0;
   double sql_visit   = (visitall / NBR_ROUNDS) / 1000.0;
   double sql_search  = (search   / NBR_ROUNDS) / 1000.0;
   double sql_rsearch = (rsearch  / NBR_ROUNDS) / 1000.0;
   double sql_wipeall = (wipeall  / NBR_ROUNDS) / 1000.0;
   double sql_delete  = (delete   / NBR_ROUNDS) / 1000.0;
   double sql_total   = sql_insert + sql_rinsert + sql_rupdate + sql_visit + 
                      + sql_search + sql_rsearch + sql_delete + sql_wipeall;
#endif   


   // =========================================================================
   // G-WAN KV Store
   // -------------------------------------------------------------------------
#ifdef TEST_GWAN_KV
   //puts("G-WAN KV");

   // -------------------------------------------------------------------------
   // create and fill a KV Store
   // -------------------------------------------------------------------------
   kv_t store;
   kv_init(&store, "num.dat", NBR_ITEMS, 0, 0, 0); // NBR_ITEM: hint not used 
   kv_item item = {.key = 0, .val = 0, .klen = 8}; // yet by G-WAN
   
   insert = 0, rinsert = 0, rupdate = 0, visitall = 0, search = 0, rsearch = 0, 
   delete = 0, wipeall = 0;
   rounds = NBR_ROUNDS;
   while(rounds--)
   {
      // ----------------------------------------------------------------------
      // Add records to the newly created KV Store
      // ----------------------------------------------------------------------
      keyval = array;
      start = getus();
      i = NBR_ITEMS;
      while(i--)
      {
         item.key = item.val = keyval;
         if(!kv_add(&store, &item)) // add an entry to the store
            printf("gwan > can't add %s\n", keyval);
         keyval += ITEM_SIZE; // next entry in array
      }
      insert += getus() - start;

      // ----------------------------------------------------------------------
      // visit all the KV Store (in physical order)
      // ----------------------------------------------------------------------
      keyval = array;
      start = getus();
      kv_do(&store, 0, 0, kv_nop, 0); // 0:key, 0:klen, fn, 0:user-defined-ctx
      visitall += getus() - start;

      // ----------------------------------------------------------------------
      // search all the KV Store (in lexical order)
      // ----------------------------------------------------------------------
      keyval = array;
      start = getus();
      i = NBR_ITEMS;
      while(i--)
      {
         if(!kv_get(&store, keyval, 8)) // search an entry in the cache
         {
            printf("gwan > can't find %s\n", keyval);
            break;
         }
         keyval += ITEM_SIZE; // next entry in array
      }
      search += getus() - start;

      // ----------------------------------------------------------------------
      // search all the KV Store (in random order)
      // ----------------------------------------------------------------------
      sw_init(&rnd, 123456); // reset generator to get the same serie
      start = getus();
      i = NBR_ITEMS;
      while(i--)
      {
         keyval = array + (ITEM_SIZE * ((sw_rand(&rnd) % NBR_ITEMS)));
         if(!kv_get(&store, keyval, 8)) // search an entry in the cache
         {
            printf("gwan > can't rfind '%s'\n", keyval);
            break;
         }
      }
      rsearch += (getus() - start) - rnd_cost;

      // ----------------------------------------------------------------------
      // delete all the records, one by one
      // ----------------------------------------------------------------------
      keyval = array;
      start = getus();
      i = NBR_ITEMS;
      while(i--)
      {
         if(!kv_del(&store, keyval, 8)) // search an entry in the cache
         {
            printf("gwan > can't delete1 '%s'\n", keyval);
            break;
         }
         keyval += ITEM_SIZE; // next entry in array
      }
      delete += getus() - start;

      // ----------------------------------------------------------------------
      // insert records again, but in random order
      // ----------------------------------------------------------------------
      sw_init(&rnd, 123456);
      start = getus();
      i = NBR_ITEMS;
      while(i--)
      {
         keyval = array + (ITEM_SIZE * (sw_rand(&rnd) % NBR_ITEMS));
         item.key = item.val = keyval;
         if(!kv_add(&store, &item)) // add/update an entry to/in the store
         {
            printf("gwan > can't add %s\n", keyval);
            break;
         }
      }
      rinsert += (getus() - start) - rnd_cost;

      // ----------------------------------------------------------------------
      // update all records, in random order
      // ----------------------------------------------------------------------
      sw_init(&rnd, 123456);
      start = getus();
      i = NBR_ITEMS;
      while(i--)
      {
         keyval = array + (ITEM_SIZE * (sw_rand(&rnd) % NBR_ITEMS));
         item.key = item.val = keyval;
         if(!kv_add(&store, &item)) // add/update an entry to/in the store
         {
            printf("gwan > can't update %s\n", keyval);
            break;
         }
      }
      rupdate += (getus() - start) - rnd_cost;
      
      // delete all the values from the KV Store (to restart from scratch)
      keyval = array;
      i = NBR_ITEMS;
      while(i--)
      {
         // here we are not interested in errors because the random generator
         // creates duplicated entries, creating missing entries in the store
         // which would then lead to deletion 'errors' (key not found).
         // it does not matter in this test as we really add/update NBR_ITEMS
         // (some of them are just duplicated keys); note that others do not
         // report failed deletion attempts (except "TC", "TC-FIXED" like 
         // SQLite does not bother)...
         //
         // in a real-life application, instead of deleting all the possible
         // keys we would either traverse the store with kv_do() or wipe it
         // all and then create a new one (that's even faster).
         kv_del(&store, keyval, 8);
         keyval += ITEM_SIZE; // next entry in array
      }
   }
   // -------------------------------------------------------------------------
   // delete the G-WAN KV Store
   // -------------------------------------------------------------------------
   // add records again so ew can delete them
   keyval = array;
   i = NBR_ITEMS;
   while(i--)
   {
      item.key = item.val = keyval;
      kv_add(&store, &item);
      keyval += ITEM_SIZE; // next entry in array
   }
   // now get the wipe-all time
   start = getus();
   kv_free(&store);
   wipeall = getus() - start;

   // -------------------------------------------------------------------------
   // calculate results
   // -------------------------------------------------------------------------
   double gwan_insert  = (insert   / NBR_ROUNDS) / 1000.0;
   double gwan_rinsert = (rinsert  / NBR_ROUNDS) / 1000.0;
   double gwan_rupdate = (rupdate  / NBR_ROUNDS) / 1000.0;
   double gwan_visit   = (visitall / NBR_ROUNDS) / 1000.0;
   double gwan_search  = (search   / NBR_ROUNDS) / 1000.0;
   double gwan_rsearch = (rsearch  / NBR_ROUNDS) / 1000.0;
   double gwan_delete  = (delete   / NBR_ROUNDS) / 1000.0;
   double gwan_wipeall = (wipeall              ) / 1000.0;
   double gwan_total   = gwan_insert + gwan_visit + gwan_search + gwan_rsearch 
                       + gwan_wipeall + gwan_delete;
#endif


#ifdef TEST_TC
   // =========================================================================
   // Tokyo Cabinet "TC" (hash table), not really fast, not memory efficient
   // -------------------------------------------------------------------------
   double tc_insert = 0, tc_rinsert = 0, tc_rupdate = 0, tc_visit = 0, 
          tc_search = 0, tc_rsearch = 0, tc_delete = 0, tc_wipeall = 0, 
          tc_total = 0;
   //puts("Tokyo Cabinet");
do
{  // -------------------------------------------------------------------------
   // create and fill a KV Store
   // -------------------------------------------------------------------------
   char db_file[32];

   snprintf(db_file, sizeof(db_file), "db%u.tch", (u32)getns());
   TCHDB *hdb = tchdbnew();
   tchdbtune(hdb, 3 * NBR_ITEMS, 0,0,0);
   tchdbsetxmsiz(hdb, 48 * NBR_ITEMS);
   if(!tchdbopen(hdb, db_file, HDBOWRITER | HDBOCREAT | HDBOTRUNC))
   {
      printf("tchdbopen failed:%s\n", tchdberrmsg(tchdbecode(hdb)));
      tchdbdel(hdb);
      break;
   }

   insert = 0, rinsert = 0, rupdate = 0, visitall = 0, search = 0, rsearch = 0, 
   delete = 0, wipeall = 0;
   rounds = NBR_ROUNDS;
   while(rounds--)
   {
      // ----------------------------------------------------------------------
      // Add records to the newly created KV Store
      // ----------------------------------------------------------------------
      keyval = array + ITEM_SIZE; 
      start = getus();
      i = 0;
      while(++i <= NBR_ITEMS)
      {
         if(!tchdbputasync(hdb, keyval, 8, keyval, 8))
         {
            printf("tc > can't add %s: %s\n", 
                   keyval, tchdberrmsg(tchdbecode(hdb)));
            break;
         }
         keyval += ITEM_SIZE; // next entry in array
      }
      insert += getus() - start;

      // ----------------------------------------------------------------------
      // visit all the KV Store (in physical order)
      // ----------------------------------------------------------------------
      char value[16] = { 0,0,0,0, 0,0,0,0, 0,0,0,0, 0,0,0,0 }; // no malloc()
      start = getus();
      tchdbiterinit(hdb);
      while((keyval = tchdbiternext2(hdb)) != NULL)
      {
         if(tchdbget3(hdb, keyval, 8, value, sizeof(value)) == -1)
         {
            printf("tc > can't visit %s: %s\n", 
                   keyval, tchdberrmsg(tchdbecode(hdb)));
            break;
         }
         free(keyval);
      }
      visitall += getus() - start;
      
      // ----------------------------------------------------------------------
      // search all the KV Store (in lexical order)
      // ----------------------------------------------------------------------
      keyval = array + ITEM_SIZE; 
      start = getus();
      i = 0;
      while(++i <= NBR_ITEMS)
      {
         if(tchdbget3(hdb, keyval, 8, value, sizeof(value)) == -1)
         {
            printf("tc > can't find %s: %s\n", 
                   keyval, tchdberrmsg(tchdbecode(hdb)));
            break;
         }
         keyval += ITEM_SIZE; // next entry in array
      }
      search += getus() - start;

      // ----------------------------------------------------------------------
      // search all the KV Store (in random order)
      // ----------------------------------------------------------------------
      sw_init(&rnd, 123456); // reset generator to get the same serie
      start = getus();
      i = 0;
      while(++i <= NBR_ITEMS)
      {
         int r = 1 + (sw_rand(&rnd) % NBR_ITEMS);
         keyval = array + (ITEM_SIZE * r);
         if(tchdbget3(hdb, keyval, 8, value, sizeof(value)) == -1)
         {
            printf("tc > can't rfind %s: %s\n", 
                   keyval, tchdberrmsg(tchdbecode(hdb)));
            break;
         }
      }
      rsearch += (getus() - start) - rnd_cost;

      // ----------------------------------------------------------------------
      // delete all the records, one by one
      // ----------------------------------------------------------------------
      keyval = array + ITEM_SIZE; // TC_FIXED does not support record "0"
      start = getus();
      i = 0;
      while(++i <= NBR_ITEMS)
      {
         if(!tchdbout2(hdb, keyval)) // search an entry in the cache
         {
            printf("tc > can't delete %s: %s\n", 
                   keyval, tchdberrmsg(tchdbecode(hdb)));
            break;
         }
         keyval += ITEM_SIZE; // next entry in array
      }
      delete += getus() - start;

      // ----------------------------------------------------------------------
      // insert records again, but in random order
      // ----------------------------------------------------------------------
      sw_init(&rnd, 123456);
      keyval = array + ITEM_SIZE; 
      start = getus();
      i = 0;
      while(++i <= NBR_ITEMS)
      {
         keyval = array + (ITEM_SIZE * (sw_rand(&rnd) % NBR_ITEMS));
         if(!tchdbputasync(hdb, keyval, 8, keyval, 8))
         {
            printf("tc > can't add %s: %s\n", 
                   keyval, tchdberrmsg(tchdbecode(hdb)));
            break;
         }
      }
      rinsert += (getus() - start) - rnd_cost;

      // ----------------------------------------------------------------------
      // update all records, in random order
      // ----------------------------------------------------------------------
      sw_init(&rnd, 123456);
      keyval = array + ITEM_SIZE; 
      start = getus();
      i = 0;
      while(++i <= NBR_ITEMS)
      {
         keyval = array + (ITEM_SIZE * (sw_rand(&rnd) % NBR_ITEMS));
         if(!tchdbputasync(hdb, keyval, 8, keyval, 8))
         {
            printf("tc > can't add %s: %s\n", 
                   keyval, tchdberrmsg(tchdbecode(hdb)));
            break;
         }
      }
      rupdate += (getus() - start) - rnd_cost;
      
      // delete all the values from the KV Store (to restart from scratch)
      keyval = array + ITEM_SIZE; // TC_FIXED does not support record "0"
      i = 0;
      while(++i <= NBR_ITEMS)
      {
         tchdbout2(hdb, keyval);
         keyval += ITEM_SIZE; // next entry in array
      }
   }
   // -------------------------------------------------------------------------
   /* delete the Tokyo Cabinet store (commented: it lasts 88 ms)
   // -------------------------------------------------------------------------
   start = getus();
   if(!tchdbvanish(hdb))
      printf("tchdbvanish failed:%s\n", tchdberrmsg(tchdbecode(hdb)));
   wipeall = getus() - start; */

   if(!tchdbclose(hdb))
      printf("tchdbclose failed: %s\n", tchdberrmsg(tchdbecode(hdb)));
   tchdbdel(hdb);
   unlink(db_file);

   // -------------------------------------------------------------------------
   // calculate results
   // -------------------------------------------------------------------------
   tc_insert  = (insert   / NBR_ROUNDS) / 1000.0;
   tc_rinsert = (rinsert  / NBR_ROUNDS) / 1000.0;
   tc_rupdate = (rupdate  / NBR_ROUNDS) / 1000.0;
   tc_visit   = (visitall / NBR_ROUNDS) / 1000.0;
   tc_search  = (search   / NBR_ROUNDS) / 1000.0;
   tc_rsearch = (rsearch  / NBR_ROUNDS) / 1000.0;
   tc_delete  = (delete   / NBR_ROUNDS) / 1000.0;
   tc_wipeall = tc_delete; // wipeall; // 88 ms...
   tc_total   = tc_insert + tc_visit + tc_search + tc_rsearch 
                + tc_wipeall + tc_delete;
} while(0);                       
#endif
   
   
#ifdef TEST_TC_FIXED
   // =========================================================================
   // Tokyo Cabinet "TC FIXED", fast because it handles only fixed-size records
   // -------------------------------------------------------------------------
   double tcfx_insert = 0, tcfx_rinsert = 0, tcfx_rupdate = 0, tcfx_visit = 0, 
          tcfx_search = 0, tcfx_rsearch = 0, tcfx_delete = 0, tcfx_wipeall = 0, 
          tcfx_total = 0;
   //puts("Tokyo Cabinet");
do
{  // -------------------------------------------------------------------------
   // create and fill a KV Store
   // -------------------------------------------------------------------------
   const int flags = 0; //FDBONOLCK; // also tried FDBOLCKNB without more luck
   char db_file[32];
   snprintf(db_file, sizeof(db_file), "db%u.tcf", (u32)getns());
   TCFDB *fdb = tcfdbnew();
   tcfdbtune(fdb, ITEM_SIZE, 1024 + NBR_ITEMS * ITEM_SIZE);
   if(!tcfdbopen(fdb, db_file, FDBOWRITER | FDBOCREAT | FDBOTRUNC | flags))
   {
      printf("tcfdbopen failed:%s\n", tcfdberrmsg(tcfdbecode(fdb)));
      tcfdbdel(fdb);
      break;
   }

   insert = 0, rinsert = 0, rupdate = 0, visitall = 0, search = 0, rsearch = 0, 
   delete = 0, wipeall = 0;
   rounds = NBR_ROUNDS;
   while(rounds--)
   {
      // ----------------------------------------------------------------------
      // Add records to the newly created KV Store
      // ----------------------------------------------------------------------
      keyval = array + ITEM_SIZE; // TC_FIXED does not support record "0"
      start = getus();
      i = 0;
      while(++i <= NBR_ITEMS)
      {
         if(!tcfdbput(fdb, i, keyval, 8))
         {
            printf("tcf > can't add %s: %s\n", 
                   keyval, tcfdberrmsg(tcfdbecode(fdb)));
            break;
         }
         keyval += ITEM_SIZE; // next entry in array
      }
      insert += getus() - start;

      // ----------------------------------------------------------------------
      // visit all the KV Store (in physical order)
      // ----------------------------------------------------------------------
      // here we 'cheat' at TC-FIXED's advantage by using an integer as the key
      // (an integer is much faster to lookup than a string - especially in an 
      // array...) but we do this here because using a string is ridiculously
      // slow for TC-FIXED traversal. Even with an integer, TC-FIXED traversal
      // is much slower than G-WAN's KV traversal.
      {
         char value[16] = { 0,0,0,0, 0,0,0,0, 0,0,0,0, 0,0,0,0 }; // no malloc()
         u64 key = 0; i = 0;
         start = getus();
         tcfdbiterinit(fdb);
         while((key = tcfdbiternext(fdb)) != NULL)
         {
            if(tcfdbget4(fdb, key, value, sizeof(value)) == -1)
            {
               printf("tcf > can't visit %s: %s\n", 
                      keyval, tcfdberrmsg(tcfdbecode(fdb)));
               break;
            }
            i++;
         }
         visitall += getus() - start;

         if(i != NBR_ITEMS)
            printf("tcf > visited %d/%d items only\n", i, NBR_ITEMS);
      }
      
      // ----------------------------------------------------------------------
      // search all the KV Store (in lexical order)
      // ----------------------------------------------------------------------
      // here we fall back to key string searches: even TC's benchmark did it
      // to be 'fair' with others
      char *value;
      keyval = array + ITEM_SIZE; // TC_FIXED does not support record "0"
      start = getus();
      i = 0;
      while(++i <= NBR_ITEMS)
      {
         if(!(value = tcfdbget3(fdb, keyval)))
         {
            printf("tcf > can't find %s: %s\n", 
                   keyval, tcfdberrmsg(tcfdbecode(fdb)));
            break;
         }
         free(value);
         keyval += ITEM_SIZE; // next entry in array
      }
      search += getus() - start;

      // ----------------------------------------------------------------------
      // search all the KV Store (in random order)
      // ----------------------------------------------------------------------
      sw_init(&rnd, 123456); // reset generator to get the same serie
      start = getus();
      i = 0;
      while(++i <= NBR_ITEMS)
      {
         int r = 1 + (sw_rand(&rnd) % NBR_ITEMS);
         keyval = array + (ITEM_SIZE * r);
         if(!(value = tcfdbget3(fdb, keyval)))
         {
            printf("tcf > can't find %s: %s\n", 
                   keyval, tcfdberrmsg(tcfdbecode(fdb)));
            break;
         }
         free(value);
      }
      rsearch += (getus() - start) - rnd_cost;

      // ----------------------------------------------------------------------
      // delete all the records, one by one
      // ----------------------------------------------------------------------
      keyval = array + ITEM_SIZE; // TC_FIXED does not support record "0"
      start = getus();
      i = 0;
      while(++i <= NBR_ITEMS)
      {
         if(!tcfdbout3(fdb, keyval)) // search an entry in the cache
            printf("tcf > can't delete %s: %s\n", 
                   keyval, tcfdberrmsg(tcfdbecode(fdb)));

         keyval += ITEM_SIZE; // next entry in array
      }
      delete += getus() - start;

      // ----------------------------------------------------------------------
      // insert records again, but in random order
      // ----------------------------------------------------------------------
      sw_init(&rnd, 123456);
      keyval = array + ITEM_SIZE; // TC_FIXED does not support record "0"
      start = getus();
      i = 0;
      while(++i <= NBR_ITEMS)
      {
         keyval = array + (ITEM_SIZE * (sw_rand(&rnd) % NBR_ITEMS));
         if(!tcfdbput(fdb, i, keyval, 8))
         {
            printf("tcf > can't add %s: %s\n", 
                   keyval, tcfdberrmsg(tcfdbecode(fdb)));
            break;
         }
      }
      rinsert += (getus() - start) - rnd_cost;

      // ----------------------------------------------------------------------
      // update all records, in random order
      // ----------------------------------------------------------------------
      sw_init(&rnd, 123456);
      keyval = array + ITEM_SIZE; // TC_FIXED does not support record "0"
      start = getus();
      i = 0;
      while(++i <= NBR_ITEMS)
      {
         keyval = array + (ITEM_SIZE * (sw_rand(&rnd) % NBR_ITEMS));
         if(!tcfdbput(fdb, i, keyval, 8))
         {
            printf("tcf > can't add %s: %s\n", 
                   keyval, tcfdberrmsg(tcfdbecode(fdb)));
            break;
         }
      }
      rupdate += (getus() - start) - rnd_cost;
      
      // delete all the values from the KV Store (to restart from scratch)
      keyval = array + ITEM_SIZE; // TC_FIXED does not support record "0"
      start = getus();
      i = 0;
      while(++i <= NBR_ITEMS)
      {
         if(!tcfdbout3(fdb, keyval)) // search an entry in the cache
            printf("tcf > can't delete %s: %s\n", 
                   keyval, tcfdberrmsg(tcfdbecode(fdb)));

         keyval += ITEM_SIZE; // next entry in array
      }
   }
   // -------------------------------------------------------------------------
   /* delete the Tokyo Cabinet store (commented: it lasts 55 ms...)
   // -------------------------------------------------------------------------
   start = getus();
   if(!tcfdbvanish(fdb))
      printf("tcfdbvanish failed:%s\n", tcfdberrmsg(tcfdbecode(fdb)));
   wipeall = getus() - start; */

   if(!tcfdbclose(fdb))
      printf("tcfdbclose failed%s\n", tcfdberrmsg(tcfdbecode(fdb)));
   tcfdbdel(fdb);
   unlink(db_file);

   // -------------------------------------------------------------------------
   // calculate results
   // -------------------------------------------------------------------------
   tcfx_insert  = (insert   / NBR_ROUNDS) / 1000.0;
   tcfx_rinsert = (rinsert  / NBR_ROUNDS) / 1000.0;
   tcfx_rupdate = (rupdate  / NBR_ROUNDS) / 1000.0;
   tcfx_visit   = (visitall / NBR_ROUNDS) / 1000.0;
   tcfx_search  = (search   / NBR_ROUNDS) / 1000.0;
   tcfx_rsearch = (rsearch  / NBR_ROUNDS) / 1000.0;
   tcfx_delete  = (delete   / NBR_ROUNDS) / 1000.0;
   tcfx_wipeall = tcfx_delete; // wipeall; // 55 ms...
   tcfx_total   = tcfx_insert + tcfx_visit + tcfx_search + tcfx_rsearch 
                + tcfx_wipeall + tcfx_delete;
} while(0);                       
#endif
   
   
   // =========================================================================
   // we no longer need this list of values
   // -------------------------------------------------------------------------
   free(array);

   // =========================================================================
   // print the final scores and close the HTML page
   // -------------------------------------------------------------------------
   //puts("Results");
   char *score[] = { "<font color=#ff4040>slower</font>", 
                     "<font color=#008800>faster</font>" };
   #define RATE(a,b) ((a) > (b) ? \
                      (b) ? (a) / (b) : 999.999 \
                                : \
                      (a) ? (b) / (a) : 999.999)
   
   xbuf_cat(reply, "<table class=\"clean\" width=480px>"
   "<tr><th width=80px>engine</th>"
       "<th width=80px>insert</th>"
       "<th width=80px>random insert</th>"
       "<th width=80px>random update</th>"
       "<th width=80px>traverse</th>"
       "<th width=80px>in-order search</th>"
       "<th width=80px>random search</th>"
       "<th width=80px>delete</th>"
       "<th width=80px>wipe all</th>"
       "<th width=80px><font color=#f0f000>total time</font></th></tr>");
   // -------------------------------------------------------------------------
#ifdef TEST_SQLITE
   xbuf_xcat(reply, "<tr class=\"d1\"><td><b>SQLite</b></td>"
      "<td>%.3F</td>"
      "<td>%.3F</td>"
      "<td>%.3F</td>"
      "<td>%.3F</td>"
      "<td>%.3F</td>"
      "<td>%.3F</td>"
      "<td>%.3F</td>"
      "<td>%.3F</td>"
      "<td><b>%.3F</b></td></tr>",
   sql_insert, sql_rinsert, sql_rupdate, sql_visit, sql_search, sql_rsearch, 
   sql_delete, sql_wipeall, sql_total);
   // -------------------------------------------------------------------------
# ifdef TEST_GWAN_KV   
   xbuf_xcat(reply, "<tr><th>G-WAN vs SQLite</th>"
   "<th><font color=#f0f080>%.03f</font>x %s</th>"
   "<th><font color=#f0f080>%.03f</font>x %s</th>"
   "<th><font color=#f0f080>%.03f</font>x %s</th>"
   "<th><font color=#f0f080>%.03f</font>x %s</th>"
   "<th><font color=#f0f080>%.03f</font>x %s</th>"
   "<th><font color=#f0f080>%.03f</font>x %s</th>"
   "<th><font color=#f0f080>%.03f</font>x %s</th>"
   "<th><font color=#f0f080>%.03f</font>x %s</th>"
   "<th><font color=#ffff00>%.03f</font>x %s</th></tr>",
   RATE(sql_insert,  gwan_insert),  score[sql_insert  > gwan_insert],
   RATE(sql_rinsert, gwan_rinsert), score[sql_rinsert > gwan_rinsert],
   RATE(sql_rupdate, gwan_rupdate), score[sql_rupdate > gwan_rupdate],
   RATE(sql_visit,   gwan_visit),   score[sql_visit   > gwan_visit],
   RATE(sql_search,  gwan_search),  score[sql_search  > gwan_search],
   RATE(sql_rsearch, gwan_rsearch), score[sql_rsearch > gwan_rsearch],
   RATE(sql_delete,  gwan_delete),  score[sql_delete  > gwan_delete],
   RATE(sql_wipeall, gwan_wipeall), score[sql_wipeall > gwan_wipeall],
   RATE(sql_total,   gwan_total),   score[sql_total   > gwan_total]);
# endif   
#endif   
   // -------------------------------------------------------------------------
#ifdef TEST_TC
   xbuf_xcat(reply, "<tr class=\"d1\"><td><b>TC</b></td>"
      "<td>%.3F</td>"
      "<td>%.3F</td>"
      "<td>%.3F</td>"
      "<td>%.3F</td>"
      "<td>%.3F</td>"
      "<td>%.3F</td>"
      "<td>%.3F</td>"
      "<td>%.3F</td>"
      "<td><b>%.3F</b></td></tr>",
   tc_insert, tc_rinsert, tc_rupdate, tc_visit, tc_search, tc_rsearch, 
   tc_delete, tc_wipeall, tc_total);
   // -------------------------------------------------------------------------
# ifdef TEST_GWAN_KV
   xbuf_xcat(reply, "<tr><th>G-WAN vs TC</th>"
   "<th><font color=#f0f080>%.03f</font>x %s</th>"
   "<th><font color=#f0f080>%.03f</font>x %s</th>"
   "<th><font color=#f0f080>%.03f</font>x %s</th>"
   "<th><font color=#f0f080>%.03f</font>x %s</th>"
   "<th><font color=#f0f080>%.03f</font>x %s</th>"
   "<th><font color=#f0f080>%.03f</font>x %s</th>"
   "<th><font color=#f0f080>%.03f</font>x %s</th>"
   "<th><font color=#f0f080>%.03f</font>x %s</th>"
   "<th><font color=#ffff00>%.03f</font>x %s</th></tr>",
   RATE(tc_insert,  gwan_insert),  score[tc_insert  > gwan_insert],
   RATE(tc_rinsert, gwan_rinsert), score[tc_rinsert > gwan_rinsert],
   RATE(tc_rupdate, gwan_rupdate), score[tc_rupdate > gwan_rupdate],
   RATE(tc_visit,   gwan_visit),   score[tc_visit   > gwan_visit],
   RATE(tc_search,  gwan_search),  score[tc_search  > gwan_search],
   RATE(tc_rsearch, gwan_rsearch), score[tc_rsearch > gwan_rsearch],
   RATE(tc_delete,  gwan_delete),  score[tc_delete  > gwan_delete],
   RATE(tc_wipeall, gwan_wipeall), score[tc_wipeall > gwan_wipeall],
   RATE(tc_total,   gwan_total),   score[tc_total   > gwan_total]);
# endif   
#endif   
   // -------------------------------------------------------------------------
#ifdef TEST_TC_FIXED
   xbuf_xcat(reply, "<tr class=\"d1\"><td><b>TC-FIXED</b></td>"
      "<td>%.3F</td>"
      "<td>%.3F</td>"
      "<td>%.3F</td>"
      "<td>%.3F</td>"
      "<td>%.3F</td>"
      "<td>%.3F</td>"
      "<td>%.3F</td>"
      "<td>%.3F</td>"
      "<td><b>%.3F</b></td></tr>",
   tcfx_insert, tcfx_rinsert, tcfx_rupdate, tcfx_visit, tcfx_search, 
   tcfx_rsearch, tcfx_delete, tcfx_wipeall, tcfx_total);
   // -------------------------------------------------------------------------
# ifdef TEST_GWAN_KV
   xbuf_xcat(reply, "<tr><th>G-WAN vs TC-FIXED</th>"
   "<th><font color=#f0f080>%.03f</font>x %s</th>"
   "<th><font color=#f0f080>%.03f</font>x %s</th>"
   "<th><font color=#f0f080>%.03f</font>x %s</th>"
   "<th><font color=#f0f080>%.03f</font>x %s</th>"
   "<th><font color=#f0f080>%.03f</font>x %s</th>"
   "<th><font color=#f0f080>%.03f</font>x %s</th>"
   "<th><font color=#f0f080>%.03f</font>x %s</th>"
   "<th><font color=#f0f080>%.03f</font>x %s</th>"
   "<th><font color=#ffff00>%.03f</font>x %s</th></tr>",
   RATE(tcfx_insert,  gwan_insert),  score[tcfx_insert  > gwan_insert],
   RATE(tcfx_rinsert, gwan_rinsert), score[tcfx_rinsert > gwan_rinsert],
   RATE(tcfx_rupdate, gwan_rupdate), score[tcfx_rupdate > gwan_rupdate],
   RATE(tcfx_visit,   gwan_visit),   score[tcfx_visit   > gwan_visit],
   RATE(tcfx_search,  gwan_search),  score[tcfx_search  > gwan_search],
   RATE(tcfx_rsearch, gwan_rsearch), score[tcfx_rsearch > gwan_rsearch],
   RATE(tcfx_delete,  gwan_delete),  score[tcfx_delete  > gwan_delete],
   RATE(tcfx_wipeall, gwan_wipeall), score[tcfx_wipeall > gwan_wipeall],
   RATE(tcfx_total,   gwan_total),   score[tcfx_total   > gwan_total]);
# endif   
#endif   
   // -------------------------------------------------------------------------
#ifdef TEST_GWAN_KV   
   xbuf_xcat(reply, "<tr class=\"d0\"><td><b>G-WAN</b></td>"
      "<td>%.3F</td>"
      "<td>%.3F</td>"
      "<td>%.3F</td>"
      "<td>%.3F</td>"
      "<td>%.3F</td>"
      "<td>%.3F</td>"
      "<td>%.3F</td>"
      "<td>%.3F</td>"
      "<td><b>%.3F</b></td></tr>",
   gwan_insert, gwan_rinsert, gwan_rupdate, gwan_visit, gwan_search, 
   gwan_rsearch, gwan_delete, gwan_wipeall, gwan_total);
#endif   
   // -------------------------------------------------------------------------
   xbuf_cat(reply, 
      "</table><small>(times per item operation in milliseconds, "
      "TC-FIXED 'wipe all' time [I/O is done there] is replaced by "
      "smaller 'delete' time)</small><br>"
      "<br>"
      "<h4>Explaining the Results:</h4>"
      "TC and TC-FIXED <b>inserts</b> are fast because TC's hash-table and TC-"
      "FIXED's array are <b>pre-allocated</b><br><i>before</i> any item can be "
      "added (by contrast, G-WAN's KV allocates memory on-demand). "
      "Pre-allocating<br>all the memory helps to shine in benchmarks but "
      "real-life applications may take months to fill these data<br>structures"
      " (if it ever happens), wasting previous memory that the system and "
      "other applications could<br>better use.<br>"
      "<br>"
      "<h4>About the \"Total Time\":</h4>"
      "G-WAN's KV store is 20-30x faster than Tokyo Cabinet \"TC\" "
      "(hash-table of variable-size keys/values).<br>"
      "G-WAN's KV store is 2-3x faster than Tokyo Cabinet \"TC-FIXED\" "
      " (an array of fixed-size keys/values).<br>"
      "<br>"
      "<h4>Concurrency Issues:</h4>"
      "Unlike TC, TC-FIXED and SQLite (where an insert/update blocks all "
      "other read &amp; write threads),<br>G-WAN's Key-Value store is "
      "wait-free (it never blocks and it never delays any mix and number "
      "of<br>reads and writes)."
      "<br><br>"
      "G-WAN's Key-Value store ability to create indexes on existing data "
      "lets you continue using the same<br>indexed-model that everybody "
      "used during decades - just much faster.</body></html>");

   // -------------------------------------------------------------------------
   // restore a reasonable G-WAN C script time-out value
   // -------------------------------------------------------------------------
   if(pscripttmo) // before you dereference a pointer...
     *pscripttmo = 4 * 1000; // 4 seconds

   return 200;
}
// ============================================================================
// End of Source Code
// ============================================================================

