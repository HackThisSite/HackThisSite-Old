#include <v8.h>
#include <string.h>

#include "v8wrapper.h"

using namespace v8;

char * runv8(const char *jssrc)
{
    // Create a stack-allocated handle scope.
    HandleScope handle_scope;

    // Create a new context.
    Persistent<Context> context = Context::New();

    // Enter the created context for compiling and
    // running the script.
    Context::Scope context_scope(context);

    // Create a string containing the JavaScript source code.
    Handle<String> source = String::New(jssrc);

    // Compile the source code.
    Handle<Script> script = Script::Compile(source);

    // Run the script
    Handle<Value> result = script->Run();

    // Dispose the persistent context.
    context.Dispose();

    // return result as string, must be deallocated in cgo wrapper
    String::AsciiValue ascii(result);
    return strdup(*ascii);
}