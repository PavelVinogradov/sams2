
AC_DEFUN([AC_FIND_UNIXODBC_INC],
[
#
# Looking for unixODBC include path and headers usability
#
AC_ARG_WITH(unixODBC-incs,
            [  --with-unixODBC-includes=DIR Find unixODBC headers in DIR],
            unixODBC_includes="$withval",
            unixODBC_includes="")

if test "x$unixODBC_includes" = "x"; then
  unixODBC_includes="/usr/local/include /usr/include"
fi

AC_MSG_CHECKING(for unixODBC headers)
AC_FIND_FILE([sqlext.h], $unixODBC_includes, unixODBC_inc_path)
case "$unixODBC_inc_path" in
  no)
    AC_MSG_RESULT(no)
    unixODBC_inc_path=""
    ;;
  *)
    AC_MSG_RESULT($unixODBC_inc_path)
    saveCPPFLAGS="$CPPFLAGS"
    CPPFLAGS="$CPPFLAGS -I$unixODBC_inc_path"
    AC_CHECK_HEADERS([sql.h sqlext.h sqltypes.h], [], [unixODBC_inc_path=""])
    CPPFLAGS="$saveCPPFLAGS"
    ;;
esac

if test "x$unixODBC_inc_path" != "x"; then
  UNIXODBC_INC="-I$unixODBC_inc_path"
  AC_SUBST(UNIXODBC_INC)
  $1="yes"
else
  $1="no"
fi
])

AC_DEFUN([AC_FIND_UNIXODBC_LIB],
[
AC_ARG_WITH(unixODBC-libs,
            [  --with-unixODBC-libs=DIR Find unixODBC libraries in DIR],
            unixODBC_libs="$withval",
            unixODBC_libs="")

if test "x$unixODBC_libs" = "x"; then
  unixODBC_libs="/usr/local/lib /usr/lib64 /usr/lib"
fi

AC_MSG_CHECKING(for unixODBC libraries)
AC_FIND_FILE([libodbc.so], $unixODBC_libs, unixODBC_lib_path)
case "$unixODBC_lib_path" in
  no)
    AC_MSG_RESULT(no)
    unixODBC_lib_path=""
    ;;
  *)
    AC_MSG_RESULT($unixODBC_lib_path)
    saveLDFLAGS="$LDFLAGS"
    saveLIBS="$LIBS"
    LDFLAGS="$LDFLAGS -L$unixODBC_lib_path"
    AC_CHECK_LIB([odbc], [SQLConnect], [], [unixODBC_lib_path=""])
    LDFLAGS="$saveLDFLAGS"
    LIBS="$saveLIBS"
    ;;
esac

if test "x$unixODBC_lib_path" != "x"; then
  UNIXODBC_LIB="-L$unixODBC_lib_path -lodbc"
  AC_SUBST(UNIXODBC_LIB)
  $1="yes"
else
  $1="no"
fi
])


AC_DEFUN([AC_CHECK_UNIXODBC],
[
$1="no"
AC_FIND_UNIXODBC_INC([unixODBC_inc_found])

if test "$unixODBC_inc_found" = "yes"; then
  AC_FIND_UNIXODBC_LIB([unixODBC_lib_found])

  if test "$unixODBC_lib_found" = "yes"; then
    $1="yes"
    AC_DEFINE([USE_UNIXODBC], [1], [use unixODBC])
  fi
fi
])

