
AC_DEFUN([AC_FIND_PQ_INC],
[
#
# Looking for LibPQ include path and libpq-fe.h usability
#
AC_ARG_WITH(pq-includes,
            [  --with-pq-includes=DIR Find LibPQ headers in DIR],
            pq_includes="$withval",
            pq_includes="")

if test "x$pq_includes" = "x"; then
  pq_includes="/usr/local/include /usr/include"
fi

AC_MSG_CHECKING(for LibPQ headers)
AC_FIND_FILE([libpq-fe.h], $pq_includes, pq_inc_path)
case "$pq_inc_path" in
  no)
    AC_MSG_RESULT(no)
    pq_inc_path=""
    ;;
  *)
    AC_MSG_RESULT($pq_inc_path)
    saveCPPFLAGS="$CPPFLAGS"
    CPPFLAGS="$CPPFLAGS -I$pq_inc_path"
    AC_CHECK_HEADER([libpq-fe.h], [], [pq_inc_path=""])
    CPPFLAGS="$saveCPPFLAGS"
    ;;
esac

if test "x$pq_inc_path" != "x"; then
  PQ_INC="-I$pq_inc_path"
  AC_SUBST(PQ_INC)
  $1="yes"
else
  $1="no"
fi
])


AC_DEFUN([AC_FIND_PQ_LIB],
[
#
# Looking for LibPQ library path and libpq usability
#
AC_ARG_WITH(pq-libs,
            [  --with-pq-libs=DIR Find LibPQ libraries in DIR],
            pq_libs="$withval",
            pq_libs="")

if test "x$pq_libs" = "x"; then
  pq_libs="/usr/local/lib /usr/lib"
fi

AC_MSG_CHECKING(for LibPQ libraries)
AC_FIND_FILE([libpq.so], $pq_libs, pq_lib_path)
case "$pq_lib_path" in
  no)
    AC_MSG_RESULT(no)
    pq_lib_path=""
    ;;
  *)
    AC_MSG_RESULT($pq_lib_path)
    saveLDFLAGS="$LDFLAGS"
    saveLIBS="$LIBS"
    LDFLAGS="$LDFLAGS -L$pq_lib_path"
    AC_CHECK_LIB([pq], [PQconnectdb], [], [pq_lib_path=""])
    LDFLAGS="$saveLDFLAGS"
    LIBS="$saveLIBS"
    ;;
esac

if test "x$pq_lib_path" != "x"; then
  PQ_LIB="-L$pq_lib_path -lpq"
  AC_SUBST(PQ_LIB)
  $1="yes"
else
  $1="no"
fi
])

AC_DEFUN([AC_CHECK_PQ],
[
$1="no"
AC_FIND_PQ_INC([pq_inc_found])

if test "$pq_inc_found" = "yes"; then
  AC_FIND_PQ_LIB([pq_lib_found])

  if test "$pq_lib_found" = "yes"; then
    $1="yes"
    AC_DEFINE([USE_PQ], [1], [use LibPQ])
  fi
fi
])
