
AC_DEFUN([AC_CHECK_HTTPD],
[
ac_httpd="/usr/sbin/httpd /usr/local/sbin/httpd /usr/sbin/httpd2 /usr/local/sbin/httpd2 /usr/sbin/apache /usr/local/sbin/apache /usr/sbin/apache2 /usr/local/sbin/apache2"
for i in $ac_httpd; do
    if test -e $i
    then
        $1="$i"
        HTTPD_SERVER="$i"
    fi
done

HTTPD_ROOT=`$HTTPD_SERVER -V |grep HTTPD_ROOT|sed -e 's/"//g'|sed -e 's/=/ /g'|sed -e 's/-D HTTPD_ROOT//g'|sed -e 's/\n//g'|sed -e 's/ //g'`
SERVER_CONFIG_FILE=$HTTPD_ROOT/`$HTTPD_SERVER -V |grep SERVER_CONFIG_FILE|sed -e 's/"//g'|sed -e 's/=/ /g'|sed -e 's/-D SERVER_CONFIG_FILE//g'|sed -e 's/\n//g'|sed -e 's/ //g'`
HTTPD_INCLUDE=$HTTPD_ROOT/`grep ^"Include " $SERVER_CONFIG_FILE |sed -e 's|/.*$ |/|g'|sed -e 's/Include//g'|sed -e 's, ,,g'| sed -e 's/[[^\/]]*$//g'`

if [[ "$HTTPD_INCLUDE" = "$HTTPD_ROOT/" ]]
then
    HTTPD_INCLUDE=`echo $SERVER_CONFIG_FILE |sed -e 's/\/httpd.conf//g'`
fi

])
