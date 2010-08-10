AC_DEFUN([AC_CHECK_LDAP],
[
$1="no"
AC_CHECK_HEADERS([ldap.h lber.h], [ldap_headers_found=yes], [])

AC_CHECK_LIB([ldap], [ldap_initialize], [ldap_lib_found=yes], [])

if test "x$ldap_headers_found" = "xyes" -a "x$ldap_lib_found" = "xyes"; then
    ldap_ac_ldflags_save="$LDFLAGS"
    ldap_ac_libs_save="$LIBS"
    LIBS="-lldap $LIBS"
    AC_CHECK_FUNCS([ldap_sasl_bind_s ldap_unbind_ext_s ldap_search_ext_s ldap_count_entries ldap_err2string], [ldap_found=yes], [])
    LDFLAGS="$ldap_ac_ldflags_save"
    LIBS="$ldap_ac_libs_save"
fi

if test "x$ldap_found" = xyes; then
    LIBS="-lldap $LIBS"
    AC_DEFINE([USE_LDAP], [1], [Define to 1 if compile with LDAP API])
    $1="yes"
fi

])
