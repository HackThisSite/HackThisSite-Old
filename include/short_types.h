// ============================================================================
// C servlet header for the G-WAN Web Application Server (http://trustleap.ch/)
// ----------------------------------------------------------------------------
// short_types.h: short default types
// ============================================================================

#ifndef _SHORT_TYPES_H
#define _SHORT_TYPES_H

typedef   signed char       s8;
typedef unsigned char       u8;
typedef   signed short int s16;
typedef unsigned short int u16;
typedef   signed int       s32;
typedef unsigned int       u32;

# ifdef _WIN32
typedef          __int64   s64;
typedef unsigned __int64   u64;
# elif def _LP64
typedef          long      s64;
typedef unsigned long      u64;
# else
typedef          long long s64;
typedef unsigned long long u64;
# endif

/*
# if __WORDSIZE == 64
typedef s64 size_t;
# else
typedef s32 size_t;
# endif
*/
typedef __SIZE_TYPE__ size_t;
typedef long int      time_t;

#endif // _SHORT_TYPES_H

// ============================================================================
// End of Source Code
// ============================================================================
