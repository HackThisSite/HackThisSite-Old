// ============================================================================
// C servlet sample for the G-WAN Web Application Server (http://trustleap.ch/)
// ----------------------------------------------------------------------------
// json.c: import, export, query and modify a JSON (RFC 4627) tree
//
// G-WAN provides native JSON functions (rather than using a library) because
// ease of use, reliability and raw speed *all* matter.
//
// A 'node' contains named same-level 'items' (data) 
//                           or child 'nodes' (containers):
//
// "users": {                       // [node: container]
//    "Title": "G-WAN Forum users", //  item: data
//    "Pierre":   {                 // [node: container]
//       "created":  1292597473,    //  item: data
//       "password": "123"          //  item: data
//    }
// }
//
// A JSON tree is stored as follows by G-WAN (NOTE: all numbers are 'double'):
//
// enum JSN_TYPE
// {
//    jsn_FALSE = 0, jsn_TRUE, jsn_NULL, jsn_NUMBER, jsn_STRING,
//    jsn_NODE, jsn_ARRAY
// };
// 
// typedef struct jsn_s
// {
//    struct jsn_s *prev,   // node's prev item (parent if node is 1st child)
//                 *next,   // node's next item (list ends with NULL)
//                 *child;  // node's first child node (NULL if none)
//    char         *name;   // node's name
//    int           type;   // node's value type (see JSN_TYPE above)
//    union {
//    char         *string; // value 'type' == jsn_STRING
//    double        number; // value 'type' == jsn_NUMBER
//    };
//    u64           x;      // context
//    long          y;      // context
// } jsn_t;
//
// NOTE: don't navigate a JSON record using ->next or ->prev without checking
//       the node ->name unless you are SURE that all items are sorted in a 
//       given order (it may no longer be the case after insertions/removals).
//
//       In order to search for a same-level item ("password" from "created"
//       in the JSON sample above), you should use jsn_byname(..., ..., 0) 
//       or jsn_byvalue(..., ..., 0), the 0 means "search on same level" 
//       (rather than "in children nodes"). G-WAN search routines are fast,
//       you will not save CPU cycles by using the node pointers directly
//       if you also have to check the node ->name.
//
// ============================================================================
// imported functions:
//    get_reply(): get a pointer on the 'reply' dynamic buffer from the server
//    xbuf_xcat(): like sprintf(), but it works in the specified dynamic buffer 
//
//   jsn_frtext(): parse a JSON text buffer into a JSON tree structure
//   jsn_totext(): format a JSON tree into a dynamic buffer and return a ptr
//  jsn_byindex(): return node's array item[i], or NULL if it doesn't exist
//   jsn_byname(): search for node 'name' in all the JSON tree
//  jsn_byvalue(): search for item 'value' of 'type' in all the JSON tree
//      jsn_add(): add an 'item' or a 'node' to the specified node (if any)
//     jsn_updt(): update an 'item' or a 'node'
//      jsn_del(): delete an 'item' or a 'node' (and all its nodes/items)
//     jsn_free(): free a JSON tree
// ----------------------------------------------------------------------------
#include "gwan.h" // G-WAN exported functions

// ============================================================================
// two (useful) helpers showing how to navigate a JSON tree directly
// ----------------------------------------------------------------------------
// traverse the tree (skeletton below)
// ----------------------------------------------------------------------------
// jsn_t *traverse(jsn_t *node, int depth, xbuf_t *buf)
// {
//    jsn_t *f = node->child;
//    while(f)
//    {
//       traverse(f, depth + 1, buf);
//       f = f->next;
//    }
// }
// 
// here, we use it to dump the JSON tree but you could use it to export a JSON
// tree in another format, like XML:
// ----------------------------------------------------------------------------
void traverse(jsn_t *node, int depth, xbuf_t *buf)
{
   static char *jsn_TYPEN[] = { "FALSE", "TRUE", "NULL", "NUMBER", "STRING", 
                                "NODE", "ARRAY", "?" };
   char *p;
   jsn_t *f = node->child;
   while(f)
   {
      switch(f->type)
      {
         case jsn_NUMBER: p = -12345678; break;
         case jsn_STRING: p = f->string; break;
         default:         p = "N/A";     break;
      }
      
      if(p == -12345678)
         xbuf_xcat(buf, "%*C Type: <b style=\"color:#4080d0\">%s</b> "
                        "Name: <b style=\"color:#4080d0\">%s</b> "
                        "Value: <b style=\"color:#4080d0\">%f</b><br>", 
                   depth, 'o', // output 'depth' times character 'o'
                   jsn_TYPEN[f->type & 7], // & 7: better safe than sorry...
                   f->name,
                   f->number);
      else
         xbuf_xcat(buf, "%*C Type: <b style=\"color:#8080d0\">%s</b> "
                        "Name: <b style=\"color:#4080d0\">%s</b> "
                        "Value: <b style=\"color:#4080d0\">%s</b><br>", 
                   depth, 'o',
                   jsn_TYPEN[f->type & 7],
                   f->name,
                   p);
                   
      traverse(f, depth + 1, buf);

      f = f->next;
      if(!f) 
         xbuf_xcat(buf, "%*C (null)<br>", depth, 'o');
   }
}
// ----------------------------------------------------------------------------
// find the parent of any given node
// ----------------------------------------------------------------------------
// return NULL if the node is root (no parent)
jsn_t *find_parent(jsn_t *node)
{
    jsn_t *n = node;

    // n->prev points to its parent when 'n' is the first parent's child
    // (otherwise, n->prev points to the previous same-level node)
    while(n && n->prev) 
    {
       if(n == n->prev->child) // reached parent
          break;

       n = n->prev;
    }
    return n;
}
// ============================================================================
int main(int argc, char *argv[])
{
   int now = time(0);
   static u8 top[]=
     "<!DOCTYPE HTML>"
     "<html lang=\"en\"><head><title>JSON</title><meta http-equiv"
     "=\"Content-Type\" content=\"text/html; charset=utf-8\">"
     "<link href=\"/imgs/style.css\" rel=\"stylesheet\" type=\"text/css\">"
     "</head><body style=\"margin:16px;\"><h2>EXERCISING JSON</h2>\r\n";

   xbuf_t *reply = get_reply(argv);
   xbuf_ncat(reply, top, sizeof(top) - 1);

   // -------------------------------------------------------------------------
   // create a first 'node' (container)
   // -------------------------------------------------------------------------
   // jsn_add_node(0, means create a new 'root' JSON context
   // you must do that only ONCE for any given JSON tree
   // (subsequent jsn_add_node() calls re-use 'users' or its children)
   jsn_t *users = jsn_add_node(0, "users");

   // add a first 'item' (data)
   jsn_add_string(users, "Title", "G-WAN Forum users");

   // add a 'node' (container) that will contain 'items' (data)
   jsn_t *user = jsn_add_node(users, "Pierre");
   jsn_add_number(user, "created",  now);
   jsn_add_string(user, "password", "123");
   jsn_add_bool  (user, "coder",    true);

   // add a 'node' (container) that will contain 'items' (data)
   user = jsn_add_node(users, "Paul");
   jsn_add_number(user, "created",  now);
   jsn_add_string(user, "password", "456");
   jsn_add_bool  (user, "coder",    false);

   // -------------------------------------------------------------------------
   // dump the whole 'users' node as text
   // -------------------------------------------------------------------------
   xbuf_cat(reply, "<b>JSON compact output:</b><br>");
   xbuf_t xbuf;
   xbuf_init(&xbuf);
   char *text = jsn_totext(&xbuf, users, 0); // 0:compact form
   xbuf_xcat(reply, "<pre>%s</pre>", text);

   xbuf_cat(reply, "<b>JSON formated output (with tabs, spaces, CRs):</b><br>");
   xbuf_empty(&xbuf);
   text = jsn_totext(&xbuf, users, 1); // 1:formated form
   xbuf_xcat(reply, "<pre>%s</pre>", text);

   // -------------------------------------------------------------------------
   // search the whole 'users' node for a named 'item' (data)
   // -------------------------------------------------------------------------
   jsn_t *search = 0;
   xbuf_cat(reply, "<b>Searching, Querying and Updating Paul:</b><br>");
   user = jsn_byname(users, "pAUl", 1); // case-insensitive, 1:deep search
   xbuf_xcat(reply, "Does Paul exist: %s<br>", user ? "yes" : "no");
   if(user)
   {
      // ----------------------------------------------------------------------
      // search the "Paul" 'node' for a named 'item' (data)
      // ----------------------------------------------------------------------
      // we could use a 0:flat (same-level) search if we searched 
      // "coder" from the "password" node because they are next to 
      // each-other (rather than child and parent)
      search = jsn_byname(user, "PassWord", 1); // 1:deep search
      xbuf_xcat(reply, "Paul's password: %s<br>", search ? search->string : "-");

      // ----------------------------------------------------------------------
      // update Paul's password
      // ----------------------------------------------------------------------
      search = jsn_updt(search, "abc");
      if(search)
         xbuf_xcat(reply, "New password: %s<br>", search ? search->string : "-");
      else
         xbuf_cat(reply, "Failed to update password<br>");

      // ----------------------------------------------------------------------
      // show an item's (number, not string) value
      // ----------------------------------------------------------------------
      search = jsn_byname(user, "CreaTed", 1); // 1:deep search
      time_t stamp = (time_t)search->number; // the item numeric value
      xbuf_xcat(reply, "Account created: %s<br>", search ? ctime(&stamp) : "-");

      // ----------------------------------------------------------------------
      // remove Paul, Pierre and Title
      // ----------------------------------------------------------------------
      xbuf_cat(reply, "<br><b>Removed Pierre, Paul and Title:</b><br>");
      user = jsn_byname(users, "pierre", 1); // 1:deep search
      jsn_del(user);
      user = jsn_byname(users, "paul", 1); // 1:deep search
      jsn_del(user);

      search = jsn_byname(users, "title", 1); // 1:deep search
      jsn_del(search);

      // list 'users' again
      xbuf_empty(&xbuf);
      text = jsn_totext(&xbuf, users, 1); // 1:formated form
      xbuf_xcat(reply, "%s<br><br>", text);

      // add Pierre again
      xbuf_cat(reply, "<b>Adding Pierre again.</b><br>");
      user = jsn_add_node(users, "Pierre");
      jsn_add_number(user, "created",  now);
      jsn_add_string(user, "password", "123");
      jsn_add_bool  (user, "coder",    true);
   }

   // -------------------------------------------------------------------------
   // add an array of items to user "Pierre"
   // -------------------------------------------------------------------------
   xbuf_cat(reply, "<b>Adding IDs[], an array of items to Pierre:</b><br>");
   user = jsn_byname(users, "Pierre", 1); // 1:deep search
   if(user)
   {
      // could be an array of integers (just wanted to test decimals)
      double ids[] = {1.0002, -23.4, 345, -4578};
      jsn_t *array = jsn_add_array(user, "IDs", 0);
      
      int i = 0;
      while(i < (sizeof(ids) / sizeof(double)))
      {
         jsn_add_number(array, "", ids[i]); // 'name' can be "" or null
         i++;
      }
     
      // list 'users' again
      xbuf_empty(&xbuf);
      text = jsn_totext(&xbuf, users, 1); // 1:formated form
      xbuf_xcat(reply, "<pre>%s</pre>", text);

      // ----------------------------------------------------------------------
      // get an item[i] by its array index value
      // ----------------------------------------------------------------------
      xbuf_cat(reply, "<b>Get IDs[i] by its array index value:</b><br>");
      int index = 2;
      search = jsn_byindex(array, index);
      if(search)
         xbuf_xcat(reply, "IDs[%3d] = %f<br>", index, search->number);
      else
         xbuf_xcat(reply, "IDs[%3d] not found<br>", index);

      index = 200; // this one does not exist
      search = jsn_byindex(array, index);
      if(search)
         xbuf_xcat(reply, "IDs[%3d] = %f<br><br>", index, search->number);
      else
         xbuf_xcat(reply, "IDs[%3d] not found<br><br>", index);

      // ----------------------------------------------------------------------
      // update an array item[i]'s NUMERIC value directly
      // ----------------------------------------------------------------------
      // NOTE: if we want to print integers (32 or 64-bit) then we should use
      //       a cast each time we address 'search->number' to avoid sending
      //       garbage to any printf-like code (item->number is a double):
      //
      //       xbuf_xcat(reply, "IDs[2] = %f<br><br>", search->number);
      //                                  ^^           
      //       xbuf_xcat(reply, "IDs[2] = %d<br><br>", (int)search->number);
      //                                  ^^           ^^^^^
      
      xbuf_cat(reply, "<b>Update IDs[i]'s NUMERIC value:</b><br>");
      search = jsn_byindex(array, 2);
      if(search)
      {
         // the traditional way, like for a jsn_STRING
         search = jsn_updt(search, 777);
         xbuf_xcat(reply, "IDs[2] = %f<br><br>", search->number);
      
         // do this only if you are sure that jsn_byindex(array, 2) != NULL
         xbuf_cat(reply, "<b>Update IDs[i]'s NUMERIC value directly:</b><br>");
         jsn_byindex(array, 2)->number = -88.8;
         xbuf_xcat(reply, "IDs[2] = %f<br><br>", 
                  jsn_byindex(array, 2)->number);
      }
   }

   // -------------------------------------------------------------------------
   // add an array of nodes
   // -------------------------------------------------------------------------
   xbuf_cat(reply, "<b>Adding an array of nodes (Jack, Tom, Kate):</b><br>");
   jsn_t *array = jsn_add_array(users, "staff", 0);
   if(array)
   {
      // as we name the node AND have a "name" item, we can search users with
      // jsn_byname() for the node or jsn_byvalue() for the item
      jsn_t *node;
      node = jsn_add_node(array, "Jack");
      jsn_add_string(node, "name",     "Jack");
      jsn_add_number(node, "created",  now);
      jsn_add_string(node, "password", "qve");
      jsn_add_bool  (node, "coder",    false);

      node = jsn_add_node(array, "Tom");
      jsn_add_string(node, "name",     "Tom");
      jsn_add_number(node, "created",  now);
      jsn_add_string(node, "password", "smt");
      jsn_add_bool  (node, "coder",    true);

      node = jsn_add_node(array, "Kate");
      jsn_add_string(node, "name",     "Kate");
      jsn_add_number(node, "created",  now);
      jsn_add_string(node, "password", "fix");
      jsn_add_bool  (node, "coder",    true);
   }
   
   // list 'users' again
   xbuf_empty(&xbuf);
   text = jsn_totext(&xbuf, users, 1); // 1:formated form
   xbuf_xcat(reply, "<pre>%s</pre>", text);

   // -------------------------------------------------------------------------
   // search an item by its value
   // -------------------------------------------------------------------------
   xbuf_cat(reply, "<b>Searching item 'KATE' by its value:</b><br>");
   // since we named the users[] nodes, we can use jsn_byname()
   search = jsn_byname(users, "KATE", 1); // 1:deep search
   if(search)
   {
      // if you searched "Kate" by_value then 'search' would point to the
      // Kate->name item and jsn_byname(search, "password") would not find
      // "password" because "password" is a child of "Kate" (Kate->password), 
      // not a child of Kate->name, the 'search' node that we would have.
      //
      // From the Kate->name node, you can find "password" as follows:
      //
      //    - use find_parent('name' node) to get the 'Kate' node and use 
      //      this parent node to search "password" by_name like done below
      //      or, if you want to do it manually, read the explanation below:
      //
      //    - use name->prev to find the parent node ("Kate") and use this
      //      parent node to search "password" by_name like done below
      //      (item->prev points to parent when item is the first child), or,
      //
      //    - use name->next ("created") to find "password" at created->next
      //      (providing that you know in which order items are listed).
      
      search = jsn_byname(search, "password", 1); // 1:deep search
      xbuf_xcat(reply, "Kate found: Kate's password: '%s'<br><br>", 
                search ? search->string : "?");
                
      // how to find a node's parent with find_parent()
      if(search)
      {
         xbuf_cat(reply, "<b>Finding 'Kate' (the parent node) from its (child)"
                         " 'password' node:</b><br>");
         user = find_parent(search); // find "Kate" from its "password" node
         xbuf_xcat(reply, "user found: '%s'<br><br>", 
                   user ? user->string : "?");
      }
   }
   else
      xbuf_cat(reply, "'Kate' not found<br><br>");

   // -------------------------------------------------------------------------
   // clear our JSON tree, rebuilt it from text, and parse the new JSON tree
   // -------------------------------------------------------------------------
   xbuf_cat(reply, "<b>Clear tree, rebuild it from text and parse it:</b><br>");
   xbuf_empty(&xbuf);
   text = jsn_totext(&xbuf, users, 0); // 0:compact form
   jsn_t *import = jsn_frtext(text, "import"); // import from text
   
   // free the original JSON tree: we now use 'import' instead
   jsn_free(users);

   xbuf_xcat(reply, "Parsing %s.<br><br>", import ? "worked" : "failed");

   // redisplay all in compact and formatted form
   xbuf_cat(reply, "<b>JSON compact output:</b><br>");
   text = jsn_totext(&xbuf, import, 0); // 0:compact form
   xbuf_xcat(reply, "<pre>%s</pre>", text);

   xbuf_cat(reply, "<b>JSON formated output (with tabs, spaces, CRs):</b><br>");
   xbuf_empty(&xbuf);
   text = jsn_totext(&xbuf, import, 1); // 1:formatted form
   xbuf_xcat(reply, "<pre>%s</pre>", text);

   // -------------------------------------------------------------------------
   // free the 'users' node and its contents
   // -------------------------------------------------------------------------
   xbuf_free(&xbuf); // no longer needed
   
   // -------------------------------------------------------------------------
   // traverse the tree with our custom function
   // -------------------------------------------------------------------------
   // note how array item *names* have beem wiped-out from the 'import' node:
   // as the JSON standard does not make room for named array items, names
   // have not been exported (so they are not in the imported tree)
   xbuf_cat(reply, "<b>Traversing our JSON tree:</b><br>");
   if(import)
      traverse(import, 1, reply);
   
   // -------------------------------------------------------------------------
   // free the 'users' node and its contents
   // -------------------------------------------------------------------------
   xbuf_cat(reply, "<br><b>Freeing the JSON tree:</b><br>");
   jsn_free(import);
   xbuf_cat(reply, "Done.<br><br>");

   return 200; // return an HTTP code (200:'OK')
}
// ============================================================================
// End of Source Code
// ============================================================================
