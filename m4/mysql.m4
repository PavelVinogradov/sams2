
AC_DEFUN([AC_CHECK_MYSQL],
[
$1="no"
AC_ARG_WITH(mysql,
    AC_HELP_STRING([--without-mysql], [build without mysql API @<:@default=no@:>@]),
    [ac_use_mysql=$withval],
    [ac_use_mysql=yes]
)

AC_ARG_WITH(mysql-includes,
    AC_HELP_STRING([--with-mysql-includes=DIR], [where the MySQL includes are.]),
    [ac_mysql_includes="$withval"])

AC_ARG_WITH(mysql-libraries,
    AC_HELP_STRING([--with-mysql-libraries=DIR], [where the MySQL libraries are.]),
    [ac_mysql_libraries="$withval"])

if test "x$ac_mysql_includes" = "x"; then
  ac_mysql_includes="/usr/local/mysql/include/mysql /usr/local/include/mysql /usr/include/mysql /usr/local/include /usr/include"
fi

if test "x$ac_mysql_libraries" = "x"; then
  ac_mysql_libraries="/usr/lib64/mysql /usr/local/mysql/lib/mysql /usr/local/lib/mysql /usr/lib/mysql /usr/local/lib /usr/lib"
fi

if test "$ac_use_mysql" = "no"; then
    AC_DEFINE([WITHOUT_MYSQL], [1], [Defined if compiling without MySQL API])
else
    mysql_incdir=""
    mysql_libdir=""
    mysql_ac_cppflags_save="$CPPFLAGS"
    mysql_ac_ldflags_save="$LDFLAGS"
    mysql_ac_libs_save="$LIBS"

    if test ! x"$ac_mysql_includes" = "x"; then
        AC_FIND_FILE([mysql.h], $ac_mysql_includes, mysql_incdir)
        if test ! "$mysql_incdir" = "no" ; then
            CPPFLAGS="$mysql_ac_cppflags_save -I$mysql_incdir"
        fi
    fi

    if test ! x"$ac_mysql_libraries" = "x"; then
        AC_FIND_FILE([libmysqlclient.so], $ac_mysql_libraries, mysql_libdir)
        if test ! "$mysql_libdir" = "no" ; then
            LDFLAGS="$mysql_ac_ldflags_save -L$mysql_libdir"
            LIBS="$mysql_ac_libs_save -lmysqlclient"
        fi
    fi

    AC_CHECK_HEADER([mysql.h],
        [AC_CHECK_LIB([mysqlclient], [mysql_init], [AC_DEFINE([USE_MYSQL], [1], [Define to 1 if compile with MySQL API])], [])],
        [])

    CPPFLAGS="$mysql_ac_cppflags_save"
    LDFLAGS="$mysql_ac_ldflags_save"
    LIBS="$mysql_ac_libs_save"
    if test "$ac_cv_lib_mysqlclient_mysql_init" = yes; then
        $1="yes"
        if test ! x"$mysql_incdir" = "x"; then
            CPPFLAGS="$CPPFLAGS -I$mysql_incdir"
        fi
        if test ! x"$mysql_libdir" = "x"; then
            LDFLAGS="$LDFLAGS -L$mysql_libdir"
        fi
        LIBS="$LIBS -lmysqlclient"
    fi

fi

])
