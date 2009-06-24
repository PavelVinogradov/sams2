#serial 22
# Obtaining file system usage information.

# Copyright (C) 1997, 1998, 2000, 2001, 2003, 2004, 2005, 2006 Free Software
# Foundation, Inc.
#
# This file is free software; the Free Software Foundation
# gives unlimited permission to copy and/or distribute it,
# with or without modifications, as long as this notice is preserved.

# Written by Jim Meyering.

AC_DEFUN([gl_FSUSAGE],
[
  AC_CHECK_HEADERS(mntent.h sys/statvfs.h sys/mount.h)
  AC_CHECK_FUNCS(getmntent getmntinfo)
])
