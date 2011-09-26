

AC_DEFUN([AC_CHECK_PCRE],
[
$1="none"
AC_ARG_WITH(pcre,
    AC_HELP_STRING([--without-pcre], [build without pcre @<:@default=no@:>@]),
    [ac_use_pcre=$withval],
    [ac_use_pcre=yes]
)

AC_ARG_WITH(pcre-includes,
    AC_HELP_STRING([--with-pcre-includes=DIR], [where the pcre includes are.]),
    [ac_pcre_includes="$withval"])

AC_ARG_WITH(pcre-libraries,
    AC_HELP_STRING([--with-pcre-libraries=DIR], [where the pcre libraries are.]),
    [ac_pcre_libraries="$withval"])

if test "x$ac_pcre_includes" = "x"; then
    ac_pcre_includes="/usr/local/include/pcre /usr/include/pcre /usr/local/include /usr/include"
fi

if test "x$ac_pcre_libraries" = "x"; then
    ac_pcre_libraries="/usr/local/lib/pcre /usr/lib/pcre /usr/local/lib /usr/lib"
fi

if test ! "$ac_use_pcre" = "no"; then
    pcre_incdir=""
    pcre_libdir=""

    pcre_ac_ext_save="$ac_ext"
    pcre_ac_cpp_save="$ac_cpp"
    pcre_ac_compile_save="$ac_compile"
    pcre_ac_link_save="$ac_link"
    pcre_ac_compiler_gnu_save="$ac_compiler_gnu"
    pcre_ac_cxxflags_save="$CXXFLAGS"
    pcre_ac_ldflags_save="$LDFLAGS"
    pcre_ac_libs_save="$LIBS"

    ac_ext=cpp
    ac_cpp='$CXXCPP $CPPFLAGS'
    ac_compile='$CXX -c $CXXFLAGS $CPPFLAGS conftest.$ac_ext >&5'
    ac_link='$CXX -o conftest$ac_exeext $CXXFLAGS $CPPFLAGS $LDFLAGS conftest.$ac_ext $LIBS >&5'
    ac_compiler_gnu=$ac_cv_cxx_compiler_gnu

    if test ! x"$ac_pcre_includes" = "x"; then
        AC_FIND_FILE([pcre.h], $ac_pcre_includes, pcre_incdir)
        if test ! "$pcre_incdir" = "no" ; then
            CXXFLAGS="$pcre_ac_cxxflags_save -I$pcre_incdir"
        fi
    fi

    if test ! x"$ac_pcre_libraries" = "x"; then
        AC_FIND_FILE([libpcre.so], $ac_pcre_libraries, pcre_libdir)
        if test ! "$pcre_libdir" = "no" ; then
            LDFLAGS="$pcre_ac_ldflags_save -L$pcre_libdir"
            LIBS="$pcre_ac_libs_save -lpcre"
        fi
    fi

    AC_CHECK_HEADER([pcre.h],
        [AC_CHECK_LIB([pcre], [pcre_compile], [AC_DEFINE([HAVE_PCRE], [1], [Define to 1 if pcre is available])], [])],
        [])
    if test "$ac_cv_lib_pcre_pcre_compile" = yes; then
        $1="pcre"
    fi

    AC_CHECK_HEADER([pcrecpp.h],
       [AC_CHECK_LIB([pcrecpp], [pcre_compile], [AC_DEFINE([HAVE_PCRECPP], [1], [Define to 1 if pcrecpp is available])], [])],
       [])
    if test "$ac_cv_lib_pcrecpp_pcre_compile" = yes; then
        $1="pcrecpp"
    fi

    ac_ext="$pcre_ac_ext_save"
    ac_cpp="$pcre_ac_cpp_save"
    ac_compile="$pcre_ac_compile_save"
    ac_link="$pcre_ac_link_save"
    ac_compiler_gnu="$pcre_ac_compiler_gnu_save"

    CXXFLAGS="$pcre_ac_cxxflags_save"
    LDFLAGS="$pcre_ac_ldflags_save"
    LIBS="$pcre_ac_libs_save"
fi

case $$1 in
    pcre)
        AC_DEFINE([USE_PCRE], [1], [Define to 1 if use pcre for regular expressions])
        if test ! x"$pcre_incdir" = "x" -a ! x"$pcre_incdir" = "xno"; then
            CXXFLAGS="$CXXFLAGS -I$pcre_incdir"
        fi
        if test ! x"$pcre_libdir" = "x" -a ! x"$pcre_libdir" = "xno"; then
            LDFLAGS="$LDFLAGS -L$pcre_libdir"
        fi
        LIBS="$LIBS -lpcre"
        ;;
    pcrecpp)
        AC_DEFINE([USE_PCRECPP], [1], [Define to 1 if use pcrecpp for regular expressions])
        if test ! x"$pcre_incdir" = "x" -a ! x"$pcre_incdir" = "xno"; then
            CXXFLAGS="$CXXFLAGS -I$pcre_incdir"
        fi
        if test ! x"$pcre_libdir" = "x" -a ! x"$pcre_libdir" = "xno"; then
            LDFLAGS="$LDFLAGS -L$pcre_libdir"
        fi
        LIBS="$LIBS -lpcrecpp"
        ;;
    *)
        AC_DEFINE([WITHOUT_PCRE], [1], [Defined if compiling without pcre])
        ;;
esac

])
