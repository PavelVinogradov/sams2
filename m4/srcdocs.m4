dnl ------------------------------------------------------------------------
dnl Find a file (or one of more files in a list of dirs)
dnl ------------------------------------------------------------------------
dnl
AC_DEFUN([AC_CHECK_SRC_DOCS],
[
AC_ARG_WITH(srcdocs,
    AC_HELP_STRING([--with-srcdocs], [build with source documentation @<:@default=no@:>@]),
    [ac_build_src_docs=$withval],
    [ac_build_src_docs=no]
)

if test ! "$ac_build_src_docs" = "no"; then
    AC_CHECK_PROG([HAVE_DOXYGEN], doxygen, yes, no)
fi
AM_CONDITIONAL([BUILD_SRC_DOCS], [test "x$HAVE_DOXYGEN" = xyes])
])
