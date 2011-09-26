
AC_DEFUN([AC_CHECK_UNIXODBC],
[
$1="no"
AC_ARG_WITH(unixodbc,
    AC_HELP_STRING([--without-unixodbc], [build without unixODBC API @<:@default=no@:>@]),
    [ac_use_unixodbc=$withval],
    [ac_use_unixodbc=yes]
)

AC_ARG_WITH(unixodbc-includes,
    AC_HELP_STRING([--with-unixodbc-includes=DIR], [where the unixODBC includes are.]),
    [ac_unixodbc_includes="$withval"])

AC_ARG_WITH(unixodbc-libraries,
    AC_HELP_STRING([--with-unixodbc-libraries=DIR], [where the unixODBC libraries are.]),
    [ac_unixodbc_libraries="$withval"])

if test x"$ac_unixodbc_includes" = "x"; then
  ac_unixodbc_includes="/usr/local/include /usr/include"
fi

if test x"$ac_unixodbc_libraries" = "x"; then
  ac_unixodbc_libraries="/usr/lib64 /usr/local/lib /usr/lib"
fi

if test "$ac_use_unixodbc" = "no"; then
    AC_DEFINE([WITHOUT_UNIXODBC], [1], [Defined if compiling without unixODBC API])
else
    unixodbc_incdir=""
    unixodbc_libdir=""
    unixodbc_ac_cppflags_save="$CPPFLAGS"
    unixodbc_ac_ldflags_save="$LDFLAGS"
    unixodbc_ac_libs_save="$LIBS"

    if test ! x"$ac_unixodbc_includes" = "x"; then
        AC_FIND_FILE([sqlext.h], $ac_unixodbc_includes, unixodbc_incdir)
        if test ! "$unixodbc_incdir" = "no"; then
            CPPFLAGS="$unixodbc_ac_cppflags_save -I$unixodbc_incdir"
        fi
    fi

    if test ! x"$ac_unixodbc_libraries" = "x"; then
        AC_FIND_FILE([libodbc.so], $ac_unixodbc_libraries, unixodbc_libdir)
        if test ! "$unixodbc_libdir" = "no"; then
            LDFLAGS="$unixodbc_ac_ldflags_save -L$unixodbc_libdir"
        fi
    fi

    AC_CHECK_HEADERS([sql.h sqlext.h sqltypes.h],
        [AC_CHECK_LIB([odbc], [SQLConnect], [AC_DEFINE([USE_UNIXODBC], [1], [Define to 1 if compile with unixODBC API])], [])],
        [])

    CPPFLAGS="$unixodbc_ac_cppflags_save"
    LDFLAGS="$unixodbc_ac_ldflags_save"
    LIBS="$unixodbc_ac_libs_save"
    if test "$ac_cv_lib_odbc_SQLConnect" = yes; then
        $1="yes"
        if test ! x"$unixodbc_incdir" = "x"; then
            CPPFLAGS="$CPPFLAGS -I$unixodbc_incdir"
        fi
        if test ! x"$unixodbc_libdir" = "x"; then
            LDFLAGS="$LDFLAGS -L$unixodbc_libdir"
        fi
        LIBS="$LIBS -lodbc"
    fi
fi

])

