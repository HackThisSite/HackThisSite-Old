#include <stdlib.h>
#include <stdio.h>
#include <string.h>

#pragma link "./libraries/mongodb/libmongoc.a"
#pragma link "./libraries/mongodb/libbson.a"
#pragma link "./libraries/mongodb/libmongoc.so"
#pragma link "./libraries/mongodb/libbson.so"

#include <mongodb/bson.h>
#include <mongodb/mongo.h>

int main() {
    mongo_connection conn[1]; /* ptr */
  
    status = mongo_connect( conn, "localhost", 27017 );

    switch (status) {
        case mongo_conn_success: printf( "connection succeeded\n" ); break;
        case mongo_conn_bad_arg: printf( "bad arguments\n" ); return 1;
        case mongo_conn_no_socket: printf( "no socket\n" ); return 1;
        case mongo_conn_fail: printf( "connection failed\n" ); return 1;
        case mongo_conn_not_master: printf( "not master\n" ); return 1;
    }

    /* CODE WILL GO HERE */ 

    mongo_destroy( conn );
    printf( "\nconnection closed\n" );

    return 0;
}
