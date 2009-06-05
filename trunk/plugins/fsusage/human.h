/* human.h -- print human readable file size

   Copyright (C) 1996, 1997, 1998, 1999, 2000, 2001, 2002, 2003, 2004,
   2005, 2006 Free Software Foundation, Inc.

   This program is free software; you can redistribute it and/or modify
   it under the terms of the GNU General Public License as published by
   the Free Software Foundation; either version 2, or (at your option)
   any later version.

   This program is distributed in the hope that it will be useful,
   but WITHOUT ANY WARRANTY; without even the implied warranty of
   MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
   GNU General Public License for more details.

   You should have received a copy of the GNU General Public License
   along with this program; if not, write to the Free Software Foundation,
   Inc., 51 Franklin Street, Fifth Floor, Boston, MA 02110-1301, USA.  */

/* Written by Paul Eggert and Larry McVoy.  */

#ifndef HUMAN_H_
# define HUMAN_H_ 1

#include <stdbool.h>

# ifndef __STDC_LIMIT_MACROS
# define __STDC_LIMIT_MACROS 1 /* to make it work also in C++ mode */
# endif
# ifndef __STDC_CONSTANT_MACROS
# define __STDC_CONSTANT_MACROS 1 /* to make it work also in C++ mode */
# endif
#include <stdint.h>

#include <limits.h>
#include <unistd.h>

/* True if the arithmetic type T is an integer type.  bool counts as
   an integer.  */
#define TYPE_IS_INTEGER(t) ((t) 1.5 == 1)

/* True if negative values of the signed integer type T use two's
   complement, ones' complement, or signed magnitude representation,
   respectively.  Much GNU code assumes two's complement, but some
   people like to be portable to all possible C hosts.  */
#define TYPE_TWOS_COMPLEMENT(t) ((t) ~ (t) 0 == (t) -1)
#define TYPE_ONES_COMPLEMENT(t) ((t) ~ (t) 0 == 0)
#define TYPE_SIGNED_MAGNITUDE(t) ((t) ~ (t) 0 < (t) -1)

/* True if the arithmetic type T is signed.  */
#define TYPE_SIGNED(t) (! ((t) 0 < (t) -1))

/* The maximum and minimum values for the integer type T.  These
   macros have undefined behavior if T is signed and has padding bits.
   If this is a problem for you, please let us know how to fix it for
   your host.  */
#define TYPE_MINIMUM(t) \
  ((t) (! TYPE_SIGNED (t) \
        ? (t) 0 \
        : TYPE_SIGNED_MAGNITUDE (t) \
        ? ~ (t) 0 \
        : ~ (t) 0 << (sizeof (t) * CHAR_BIT - 1)))
#define TYPE_MAXIMUM(t) \
  ((t) (! TYPE_SIGNED (t) \
        ? (t) -1 \
        : ~ (~ (t) 0 << (sizeof (t) * CHAR_BIT - 1))))













/* A conservative bound on the maximum length of a human-readable string.
   The output can be the square of the largest uintmax_t, so double
   its size before converting to a bound.
   log10 (2.0) < 146/485.  Add 1 for integer division truncation.
   Also, the output can have a thousands separator between every digit,
   so multiply by MB_LEN_MAX + 1 and then subtract MB_LEN_MAX.
   Append 1 for a space before the suffix.
   Finally, append 3, the maximum length of a suffix.  */
# define LONGEST_HUMAN_READABLE \
  ((2 * sizeof (uintmax_t) * CHAR_BIT * 146 / 485 + 1) * (MB_LEN_MAX + 1) \
   - MB_LEN_MAX + 1 + 3)

/* Return zero if T can be determined to be an unsigned type.
   Otherwise, return 1.
   When compiling with GCC, INT_STRLEN_BOUND uses this macro to obtain a
   tighter bound.  Otherwise, it overestimates the true bound by one byte
   when applied to unsigned types of size 2, 4, 16, ... bytes.
   The symbol signed_type_or_expr__ is private to this header file.  */
#if __GNUC__ >= 2
# define signed_type_or_expr__(t) TYPE_SIGNED (__typeof__ (t))
#else
# define signed_type_or_expr__(t) 1
#endif

/* Bound on length of the string representing an integer type or expression T.
   Subtract 1 for the sign bit if T is signed; log10 (2.0) < 146/485;
   add 1 for integer division truncation; add 1 more for a minus sign
   if needed.  */
#define INT_STRLEN_BOUND(t) \
  ((sizeof (t) * CHAR_BIT - signed_type_or_expr__ (t)) * 146 / 485 \
   + signed_type_or_expr__ (t) + 1)

/* Options for human_readable.  */
enum
{
  /* Unless otherwise specified these options may be ORed together.  */

  /* The following three options are mutually exclusive.  */
  /* Round to plus infinity (default).  */
  human_ceiling = 0,
  /* Round to nearest, ties to even.  */
  human_round_to_nearest = 1,
  /* Round to minus infinity.  */
  human_floor = 2,

  /* Group digits together, e.g. `1,000,000'.  This uses the
     locale-defined grouping; the traditional C locale does not group,
     so this has effect only if some other locale is in use.  */
  human_group_digits = 4,

  /* When autoscaling, suppress ".0" at end.  */
  human_suppress_point_zero = 8,

  /* Scale output and use SI-style units, ignoring the output block size.  */
  human_autoscale = 16,

  /* Prefer base 1024 to base 1000.  */
  human_base_1024 = 32,

  /* Prepend " " before unit symbol.  */
  human_space_before_unit = 64,

  /* Append SI prefix, e.g. "k" or "M".  */
  human_SI = 128,

  /* Append "B" (if base 1000) or "iB" (if base 1024) to SI prefix.  */
  human_B = 256
};

char *human_readable (uintmax_t, char *, int, uintmax_t, uintmax_t);
char const *df_readable (bool, uintmax_t, char *, uintmax_t, uintmax_t);

#endif /* HUMAN_H_ */
