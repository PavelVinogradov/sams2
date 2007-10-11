dnl ---------------------------------------------------------
dnl
dnl Macro:
dnl     AC_CHECK_UNIXODBC
dnl
dnl Arguments:
dnl     $1=includedir
dnl     $2=libdir
dnl
dnl Description:
dnl     Check for unixODBC. If found configure for it.
dnl
dnl ---------------------------------------------------------

AC_DEFUN([AC_CHECK_UNIXODBC],
[
check_iobc_inc_path="$1"
check_iobc_lib_path="$2"
CPPFLAGS="$CPPFLAGS -I$check_iobc_inc_path"
AC_CHECK_HEADERS([sql.h sqlext.h sqltypes.h],
[unixODBC_ok=yes;odbc_headers="$odbc_headers $ac_hdr"],[unixODBC_ok=no; break])

if test "x$unixODBC_ok" != "xyes"
then
        AC_MSG_ERROR([Unable to find the unixODBC headers in '$check_iobc_inc_path'])
fi

# new autoconf tools doesn't detect through ac_hdr, so define
# odbc_headers manually to make AC_CHECK_ODBC_TYPE to work
if test "x$odbc_headers" = "x   "
then
  odbc_headers="sql.h sqlext.h sqltypes.h"
fi

AC_CHECK_HEADERS(odbcinst.h)

if test "x$ac_cv_header_odbcinst_h" = "xyes"
then

  odbc_headers="$odbc_headers odbcinst.h"
  save_LDFLAGS="$LDFLAGS"
  LDFLAGS="-L$check_iobc_lib_path $LDFLAGS"

  AC_CHECK_LIB(odbcinst,SQLGetPrivateProfileString,
  [AC_DEFINE(HAVE_SQLGETPRIVATEPROFILESTRING,1,[Define if SQLGetPrivateProfileString is defined])
  LIBS="$LIBS -L$check_iobc_lib_path -lodbcinst" ; have_odbcinst=yes], [])
  LDFLAGS="$save_LDFLAGS"

fi
])

