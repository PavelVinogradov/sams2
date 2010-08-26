
AC_DEFUN([AC_CHECK_HTTPD],
[
ac_httpd="/usr/sbin/httpd /usr/local/sbin/httpd /usr/sbin/httpd2 /usr/local/sbin/httpd2 /usr/sbin/apache /usr/local/sbin/apache /usr/sbin/apache2 /usr/local/sbin/apache2"
for i in $ac_httpd; do
    if [[ -a $i ]]
    then
        $1="$i"
        HTTPD_SERVER="$i"
    fi
done

])
