// ============================================================================
// C servlet sample for the G-WAN Web Application Server (http://trustleap.ch/)
// ----------------------------------------------------------------------------
// kv.c: illustrate how to use the (lock-free) G-WAN Key-Value Store.
//
//       Keys are limited to a length of 256 bytes (2^2048 entries maximum), 
//       and the Value is limited to 16 MB (per value). These limitations 
//       have no other reason to exist than an attempt to keep the structure
//       more compact, and could be removed in case of need.
//
//       You can store and search ASCII Keys as well as BINARY Keys. Values
//       can store records like a database engine or be used as a mere index 
//       (see below for an example of each usage).
//
//       The kv_do() call be can used to create parallel indexes on-the-fly
//       for any value's field of a KV Store. See below for details.
//
//       A kv_t object can be attached to a G-WAN persistant pointer to serve
//       the needs of servlet and handler C scripts without having to care
//       about concurrency issues (the G-WAN KV Store is thread-safe).
//
//       G-WAN's KV store is much faster than Redis or Memcached because it
//       works locally rather than using a dedicated server. But G-WAN can 
//       also be used as a KV server to make a KV Store available remotely,
//       in which case the KV store obviously benefits from G-WAN's speed.
// ============================================================================
// imported functions:
//   get_reply(): get a pointer on the 'reply' dynamic buffer from the server
//    xbuf_cat(): like strcat(), but it works in the specified dynamic buffer 
//     kv_init(): initialize a G-WAN KV Store
//     kv_free(): remove all entries and delete a G-WAN KV Store
//      kv_add(): put the specified contents into a G-WAN KV Store
//      kv_del(): delete the specified contents from a G-WAN KV Store
//       kv_do(): visit the entries of a G-WAN KV Store that match a filter
// ----------------------------------------------------------------------------
#include "gwan.h" // G-WAN exported functions

typedef struct record_s // this is a user-defined 'record' structure
{
   char *name;  // the Key(name) is a field of the Value(name, email)
   char *email; 
   u32   id;      
} record_t;

// ----------------------------------------------------------------------------
// user-defined function to free memory we allocated for a KV *item.val*
// ----------------------------------------------------------------------------
static void my_delete_record(void *value)
{
   record_t *rec = (record_t*)value;
   if(!rec) return;

   if(rec->name)  free(rec->name);
   if(rec->email) free(rec->email);
   free(rec); // free the Value of the Key-Value item
}
// ----------------------------------------------------------------------------
// user-defined function executed when we process a KV store
// ----------------------------------------------------------------------------
// return:
//  1: success: visited all matching entries
//  2: no entry starts with the 'key' string
//
// It must return 1 to continue searching (any other value stops the search)
// You can use the 'user_defined_ctx' context to store a structure for 
// counters, data collection, etc.
// ----------------------------------------------------------------------------
// ('nop' is reserved for internal use)
static int my_process(const kv_item *item, const void *my_ctx)
{
   xbuf_t *reply = (xbuf_t*)my_ctx; // used here to pass the 'reply' buffer
   
   record_t *record = (void*)item->val; // pointer on the KV item's 'Value'

   static int line = 0; // just to make the HTML table look nicer
   xbuf_xcat(reply, 
             "<tr class=\"d%u\"><td>%u</td><td>%u</td><td>%s</td><td>%s</tr>", 
             !(++line & 1), 
             item->klen,
             record->id, record->name, record->email);
           
   return 1; // continue processing (any other value stops processing)
}
// ============================================================================
// servlet entry point
// ----------------------------------------------------------------------------
int main(int argc, char *argv[])
{
   static u8 top[]=
     "<!DOCTYPE HTML>"
     "<html lang=\"en\"><head><title>G-WAN Key Value Store</title>"
     "<meta http-equiv=\"Content-Type\" content=\"text/html; charset=utf-8\">"
     "<link href=\"/imgs/style.css\" rel=\"stylesheet\" type=\"text/css\">"
     "</head><body style=\"margin:16px;\">\r\n";

   xbuf_t *reply = get_reply(argv);
   xbuf_ncat(reply, top, sizeof(top) - 1);
   
   // =========================================================================
   // this first test is to show the basic usage
   // -------------------------------------------------------------------------
   {
      xbuf_cat(reply,
              "<h2>Basic KV Store usage</h2><br>"
              "Using key(name) and value(email):<br><br>"
              "<b>Adding</b> two Key / Values: "
              "(\"pierre\" / \"pierre@example.com\")"
              " and (\"paul\" / \"paul@example.com\")<br>");

      // ----------------------------------------------------------------------
      // add entries to the store
      // ----------------------------------------------------------------------
      // here kv_init() does not define a 'delete' function because items are
      // using static data rather than dynamically allocated memory:
      kv_t store;
      kv_init(&store, "users", 10, 0, 0, 0); // 10:nb items (hint not used yet)
      // (this hint is not used today, but it may be the case in the future)

      kv_item item;
      item.key = "pierre"; 
      item.klen = sizeof("pierre") - 1;
      item.val = "pierre@example.com";
      kv_add(&store, &item); // add an entry to the store

      item.key = "paul"; 
      item.klen = sizeof("paul") - 1;
      item.val = "paul@example.com";
      kv_add(&store, &item); 

      // ----------------------------------------------------------------------
      // get the current count of items in KV store
      // ----------------------------------------------------------------------
      xbuf_xcat(reply, "<br><b>Count</b>: %d key(s) in KV store<br>",
                store.nbr_items); 
      
      // ----------------------------------------------------------------------
      // search an entry in the store
      // ----------------------------------------------------------------------
      xbuf_cat(reply, "<br><b>Searching</b> key \"pierre\":");
      char *p = kv_get(&store, "pierre", sizeof("pierre") - 1); // 0:not found
      xbuf_xcat(reply, "<br>pierre's email address: %s<br>", p);
      
      // ----------------------------------------------------------------------
      // delete a record
      // ----------------------------------------------------------------------
      p = "pierre";
      kv_del(&store, p, sizeof("pierre") - 1);

      // check that the record was deleted (0:not found)
      p = kv_get(&store, p, sizeof("pierre") - 1);
      xbuf_xcat(reply,
               "<br><b>Deleting</b>: pierre's record %s<br>",
               p ? "<font color=#ff4040>is still there</font>" 
                 : "<font color=#008800>has been deleted</font>");

      // ----------------------------------------------------------------------
      // get the current count of items in KV store
      // ----------------------------------------------------------------------
      xbuf_xcat(reply, "<br><b>Count</b>: %d key(s) in KV store<br>",
                store.nbr_items); 

      // ----------------------------------------------------------------------
      // delete the KV Store
      // ----------------------------------------------------------------------
      xbuf_cat(reply, "<br><b>Deleting</b> KV Store.<br><br>");
      kv_free(&store);
   }


   // =========================================================================
   // this second test is to show the 'advanced' usage
   // -------------------------------------------------------------------------
   // note: data attached to an item MUST be allocated (either statically
   //       like above or dynamically in the code below) but it MUST remain
   //       in memory for your code to retrieve it later.
   //
   //       So, most of the time, you will explicitely allocate memory for 
   //       the item.key and item.val pointers.
   //
   //       When memory is dynamically allocated (like this is the case now), 
   //       use a kv_free() user-defined function to free it automatically 
   //       when an item (or the whole KV Store) is deleted.
   // -------------------------------------------------------------------------
   xbuf_cat(reply,
   "<h2>Advanced KV Store usage</h2><br>"
   "Using Key-Values where Values are <u>C structures:</u><br><br>"
   "<pre>"
   "typedef struct record_s\n"
   "{\n"
   "   char *name;  // here, the Key (name) is\n"
   "   char *email; // a field of the record:\n" 
   "   u32   id;    // Value(name, email, id)\n"
   "} record_t;</pre><br>"
   "A Key can be ASCII or binary and up to 4 GB in length, while"
   " the length of a Value is only limited by the available memory."
   "<br><br>"
   "We create two KV stores:<ul>"
   "<li><b>name_dat</b> (the data table made of records indexed on 'name')</li>"
   "<li><b>id_idx</b> (an additional index for the name_dat's 'id' binary "
   "field)</li></ul>");

   xbuf_cat(reply,
           "<b>Adding</b> Records for \"pierre\", \"paul\", \"pascal\" "
           "and \"pat\".<br><br>");

   // here kv_init() defines a 'delete' function because item values are 
   // using dynamically allocated memory (rather than static data):
   kv_t name_dat, id_idx;
   kv_init(&name_dat, "users.dat",    10, 0, my_delete_record, 0); // data
   kv_init(&id_idx,   "users_id.idx", 10, 0, 0, 0); // index

   // -------------------------------------------------------------------------
   // we also create 2 INDEXES on the 'id' and 'name' fields to search records
   // -------------------------------------------------------------------------
   // we have our records already stored in memory, so all we need to do is to
   // build a new KV store with the desired 'key' *pointing* on those records
   // -------------------------------------------------------------------------
   // allocate memory for each new record, and add new records to the store
   // -------------------------------------------------------------------------
   kv_item item;
   record_t *record = (record_t*)malloc(sizeof(record_t));
   record->name = (char*)strdup("pierre");
   record->email = (char*)strdup("pierre@example.com");
   record->id = 1000001;

   item.key = record->name; 
 //item.klen = 0; // 0:G-WAN will find the length
   item.klen = sizeof("pierre") - 1; // faster
   item.val = record;
   kv_add(&name_dat, &item); // add an entry to the name_dat Store index

   item.key = &record->id; item.klen = sizeof(u32);
   item.val = record;
   kv_add(&id_idx, &item); // add an entry to the id_idx Store index

   // --- second record -------------------------------------------------------
   record = (record_t*)malloc(sizeof(record_t));
   record->name = (char*)strdup("paul");
   record->email = (char*)strdup("paul@example.com");
   record->id = 1000002;

   item.key = record->name; 
   item.klen = sizeof("paul") - 1;
   item.val = record;
   kv_add(&name_dat, &item); // add an entry to the name_dat Store index

   item.key = &record->id; item.klen = sizeof(u32);
   item.val = record;
   kv_add(&id_idx, &item); // add an entry to the id_idx Store index

   // --- third record --------------------------------------------------------
   record = (record_t*)malloc(sizeof(record_t));
   record->name = (char*)strdup("pascal");
   record->email = (char*)strdup("pascal@example.com");
   record->id = 1000003;

   item.key = record->name; 
   item.klen = sizeof("pascal") - 1;
   item.val = record;
   kv_add(&name_dat, &item); // add an entry to the name_dat Store index
   
   item.key = &record->id; item.klen = sizeof(u32);
   item.val = record;
   kv_add(&id_idx, &item); // add an entry to the id_idx Store index

   // --- fourth record -------------------------------------------------------
   record = (record_t*)malloc(sizeof(record_t));
   record->name = (char*)strdup("pat");
   record->email = (char*)strdup("pat@example.com");
   record->id = 1000004;

   item.key = record->name; 
   item.klen = sizeof("pat") - 1;
   item.val = record;
   kv_add(&name_dat, &item); // add an entry to the name_dat Store index
   
   item.key = &record->id; item.klen = sizeof(u32);
   item.val = record;
   kv_add(&id_idx, &item); // add an entry to the id_idx Store index

   // -------------------------------------------------------------------------
   // get the current count of items in KV store
   // -------------------------------------------------------------------------
   xbuf_xcat(reply, "<b>Count</b>: %d key(s) in KV store<br><br>",
             name_dat.nbr_items); 

   // -------------------------------------------------------------------------
   // search an entry in the store
   // -------------------------------------------------------------------------
   xbuf_cat(reply, "<b>Searching</b> (existing) <u>ASCII</u> key "
                   "\"pierre\" in <b>name_dat</b>:");
   record = kv_get(&name_dat, "pierre", sizeof("pierre") - 1);
   xbuf_xcat(reply,
            "<br>pierre's email address: %s<br>",
            record ? record->email : "<font color=#ff4040>not found</font>");

   // -------------------------------------------------------------------------
   // use the 'id' INDEX to search records
   // -------------------------------------------------------------------------
   xbuf_cat(reply, "<br><b>Searching</b> (existing) <u>binary</u> key:"
                   "'1000003' in <b>id_idx</b>:");
   u32 val = 1000003;
   record = kv_get(&id_idx, &val, sizeof(u32));
   xbuf_xcat(reply,
            "<br>record with id = '%u' name's field: \"%s\"<br>",
            val,
            record ? record->name : "<font color=#ff4040>not found</font>");
   // -------------------------------------------------------------------------
   // process the KV store, executing a user-defined function on all records
   // that match the provided filter (if any)
   // -------------------------------------------------------------------------
   xbuf_cat(reply, 
            "<br><b>Processing</b> all entries with filter \"pa*\" "
            "(note that \"pierre\" is missing):<br> "
            "(<u>using <b>name_dat</b>'s 'name' key to sort entries</u>)"
            "<br><br>"
            "<table class=\"clean\" width=480px>"
            "<th>key length</th><th>id</th><th>name</th><th>email</th>");
   void *my_ctx = (void*)reply; // pass the 'reply' xbuffer as the context
   kv_do(&name_dat, "pa", sizeof("pa") - 1, my_process, my_ctx);
   xbuf_cat(reply, "<table><br>");

   // -------------------------------------------------------------------------
   // Note
   // -------------------------------------------------------------------------
   xbuf_cat(reply, 
            "Note: kv_do() allows to use a filter that limits processing to "
            "a subset of the table."
            " This is much faster than accessing all the records one by one "
            "with kv_get().<br><br>");

   // -------------------------------------------------------------------------
   // delete a record, using the id_idx index
   // -------------------------------------------------------------------------
   u32 val = 1000001;
   kv_del(&id_idx, (char*)&val, sizeof(u32));

   // check that the record was deleted (0:not found)
   record = kv_get(&id_idx, (char*)&val, sizeof(u32));
   xbuf_xcat(reply,
            "<b>Deleting</b> pierre's record, using the id_idx index "
            "(key:%u): <br>record %s<br>",
            val,
            record ? "<font color=#ff4040>is still there</font>" 
                   : "<font color=#008800>has been deleted</font>");

   // -------------------------------------------------------------------------
   // process the KV store, executing a user-defined function on *all* records
   // (this time we don't use any filter)
   // -------------------------------------------------------------------------
   xbuf_cat(reply, 
            "<br><b>Processing</b> all entries without any filter:<br> "
            "(<u>using <b>id_idx</b>'s 'id' index to sort entries</u>)"
            "<br><br>"
            "<table class=\"clean\" width=480px>"
            "<th>key length</th><th>id</th><th>name</th><th>email</th>");
   my_ctx = (void*)reply; // pass the 'reply' xbuffer as the context
   kv_do(&id_idx, 0, 0, my_process, my_ctx);
   xbuf_cat(reply, "<table><br>");

   // -------------------------------------------------------------------------
   // get the current count of items in KV store
   // -------------------------------------------------------------------------
   xbuf_xcat(reply, "<b>Count</b>: %d key(s) in <b>%s</b> KV store",
             name_dat.nbr_items, name_dat.name);

   xbuf_xcat(reply, "<br><b>Count</b>: %d key(s) in <b>%s</b> KV store<br><br>",
             id_idx.nbr_items, id_idx.name);

   xbuf_cat(reply, 
            "Note: we have deleted 'pierre' from the 'id_idx' index and then "
            "verified that it no longer appears in this index file - but it "
            "remains in the 'name_dat' table and can be searched by using the "
            "default 'name' key. To completely remove a record using external "
            "indexes, the record must be deleted from all the external "
            "indexes and from the main table itself.<br><br>");

   // =========================================================================
   // delete the KV Store
   // -------------------------------------------------------------------------
   xbuf_cat(reply, "<b>Deleting</b> KV Store.<br><br>");
   kv_free(&id_idx);   // an additional index on our data stored in 'name_dat'
   kv_free(&name_dat); // the data itself

   return 200; // return an HTTP code (200:'OK')
}
// ============================================================================
// End of Source Code
// ============================================================================
