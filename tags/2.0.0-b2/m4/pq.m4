
AC_DEFUN([AC_CHECK_PQ],
[
$1="no"
AC_ARG_WITH(pq,
    AC_HELP_STRING([--without-pq], [build without PostgreSQL API @<:@default=no@:>@]),
    [ac_use_pq=$withval],
    [ac_use_pq=yes]
)

AC_ARG_WITH(pq-includes,
    AC_HELP_STRING([--with-pq-includes=DIR], [where the LibPQ includes are.]),
    [ac_pq_includes="$withval"])

AC_ARG_WITH(pq-libraries,
    AC_HELP_STRING([--with-pq-libraries=DIR], [where the LibPQ libraries are.]),
    [ac_pq_libraries="$withval"])

if test "x$ac_pq_includes" = "x"; then
  ac_pq_includes="/usr/local/include /usr/include /usr/include/pgsql"
fi

if test "x$ac_pq_libraries" = "x"; then
  ac_pq_libraries="/usr/local/lib /usr/lib64 /usr/lib"
fi

if test "$ac_use_pq" = "no"; then
    AC_DEFINE([WITHOUT_PQ], [1], [Defined if compiling without PostgreSQL API])
else
    pq_incdir=""
    pq_libdir=""
    pq_ac_cppflags_save="$CPPFLAGS"
    pq_ac_ldflags_save="$LDFLAGS"
    pq_ac_libs_save="$LIBS"

    if test ! x"$ac_pq_includes" = "x"; then
        AC_FIND_FILE([libpq-fe.h], $ac_pq_includes, pq_incdir)
        if test ! "$pq_incdir" = "no" ; then
            CPPFLAGS="$pq_ac_cppflags_save -I$pq_incdir"
        fi
    fi

    if test ! x"$ac_pq_libraries" = "x"; then
        AC_FIND_FILE([libpq.so], $ac_pq_libraries, pq_libdir)
        if test ! "$pq_libdir" = "no" ; then
            LDFLAGS="$pq_ac_ldflags_save -L$pq_libdir"
        fi
    fi

    AC_CHECK_HEADER([libpq-fe.h],
        [AC_CHECK_LIB([pq], [PQprepare], [AC_DEFINE([USE_PQ], [1], [Define to 1 if compile with PostgreSQL API])], [])],
        [])

    CPPFLAGS="$pq_ac_cppflags_save"
    LDFLAGS="$pq_ac_ldflags_save"
    LIBS="$pq_ac_libs_save"
    if test "$ac_cv_lib_pq_PQprepare" = yes; then
        $1="yes"
        if test ! x"$pq_incdir" = "x"; then
            CPPFLAGS="$CPPFLAGS -I$pq_incdir"
        fi
        if test ! x"$pq_libdir" = "x"; then
            LDFLAGS="$LDFLAGS -L$pq_libdir"
        fi
        LIBS="$LIBS -lpq"
    fi

fi

])
