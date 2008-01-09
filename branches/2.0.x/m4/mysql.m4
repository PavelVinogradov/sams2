
AC_DEFUN([AC_FIND_MYSQL_INC],
[
#
# Looking for MySQL include path and mysql.h usability
#
AC_ARG_WITH(mysql-includes,
            [  --with-mysql-includes=DIR Find MySQL headers in DIR],
            mysql_includes="$withval",
            mysql_includes="")

if test "x$mysql_includes" = "x"; then
  mysql_includes="/usr/local/mysql/include/mysql /usr/local/include/mysql /usr/include/mysql /usr/local/include /usr/include"
fi

AC_MSG_CHECKING(for MySQL headers)
AC_FIND_FILE([mysql.h], $mysql_includes, mysql_inc_path)
case "$mysql_inc_path" in
  no)
    AC_MSG_RESULT(no)
    mysql_inc_path=""
    ;;
  *)
    AC_MSG_RESULT($mysql_inc_path)
    saveCPPFLAGS="$CPPFLAGS"
    CPPFLAGS="$CPPFLAGS -I$mysql_inc_path"
    AC_CHECK_HEADER([mysql.h], [], [mysql_inc_path=""])
    CPPFLAGS="$saveCPPFLAGS"
    ;;
esac

if test "x$mysql_inc_path" != "x"; then
  MYSQL_INC="-I$mysql_inc_path"
  AC_SUBST(MYSQL_INC)
  $1="yes"
else
  $1="no"
fi
])


AC_DEFUN([AC_FIND_MYSQL_LIB],
[
#
# Looking for MySQL library path and libmysqlclient usability
#
AC_ARG_WITH(mysql-libs,
            [  --with-mysql-libs=DIR Find MySQL libraries in DIR],
            mysql_libs="$withval",
            mysql_libs="")

if test "x$mysql_libs" = "x"; then
  mysql_libs="/usr/local/mysql/lib/mysql /usr/local/lib/mysql /usr/lib/mysql /usr/local/lib /usr/lib"
fi

AC_MSG_CHECKING(for MySQL libraries)
AC_FIND_FILE([libmysqlclient.so], $mysql_libs, mysql_lib_path)
case "$mysql_lib_path" in
  no)
    AC_MSG_RESULT(no)
    mysql_lib_path=""
    ;;
  *)
    AC_MSG_RESULT($mysql_lib_path)
    saveLDFLAGS="$LDFLAGS"
    saveLIBS="$LIBS"
    LDFLAGS="$LDFLAGS -L$mysql_lib_path"
    AC_CHECK_LIB([mysqlclient], [mysql_init], [], [mysql_lib_path=""])
    LDFLAGS="$saveLDFLAGS"
    LIBS="$saveLIBS"
    ;;
esac

if test "x$mysql_lib_path" != "x"; then
  MYSQL_LIB="-L$mysql_lib_path -lmysqlclient"
  AC_SUBST(MYSQL_LIB)
  $1="yes"
else
  $1="no"
fi
])

AC_DEFUN([AC_CHECK_MYSQL],
[
AC_FIND_MYSQL_INC([mysql_inc_found])

if test "$mysql_inc_found" = "yes"; then
  AC_FIND_MYSQL_LIB([mysql_lib_found])

  if test "$mysql_lib_found" = "yes"; then
    AC_DEFINE([USE_MYSQL], [1], [use MySQL])
  fi
fi
])
