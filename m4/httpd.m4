
AC_DEFUN([AC_CHECK_HTTPD],
[
AC_MSG_NOTICE([Trying to find some web servers, provides the virtual package "httpd"])
HTTPD=no
AC_PATH_PROGS([HTTPD], [httpd httpd2 apachectl apache2ctl], [no], 
		[$PATH$PATH_SEPARATOR/usr/local/sbin$PATH_SEPARATOR]dnl
     [/usr/sbin])

if test  "x$HTTPD" = "xno"; then
	AC_MSG_ERROR([httpd not found in $PATH$PATH_SEPARATOR/usr/local/sbin$PATH_SEPARATOR/usr/sbin])
else
	$1="$HTTPD"
	HTTPD_SERVER="$HTTPD"
fi

# This code mostly copied from GNU macro AX_PROG_HTTPD
HTTPD_ROOT=`$HTTPD_SERVER -V | grep HTTPD_ROOT | sed 's/^.*HTTPD_ROOT[[[:blank:]]]*=[[[:blank:]]]*"\(.*\)"$/\1/'`
SERVER_CONFIG_FILE=`$HTTPD_SERVER -V | grep SERVER_CONFIG_FILE | sed 's/^.*SERVER_CONFIG_FILE[[[:blank:]]]*=[[[:blank:]]]*"\(.*\)"$/\1/'`
if echo $SERVER_CONFIG_FILE | grep ^[[^/]] > /dev/null; then
        SERVER_CONFIG_FILE=$HTTPD_ROOT/$SERVER_CONFIG_FILE
fi
SERVER_ROOT_PATTERN='^[[[:blank:]]]*ServerRoot[[[:blank:]]][[[:blank:]]]*"\([[^"]]*\)"$'
HTTPD_USER_PATTERN='^User[[[:blank:]]][[[:blank:]]]*\([[^[:blank:]]][[^[:blank:]]]*\)$'
HTTPD_GROUP_PATTERN='^Group[[[:blank:]]][[[:blank:]]]*\([[^[:blank:]]][[^[:blank:]]]*\)$'
DOCUMENT_ROOT_PATTERN='^[[[:blank:]]]*DocumentRoot[[[:blank:]]][[[:blank:]]]*"\([[^"]]*\)"$'
SCRIPT_ALIAS_PATTERN='^[[[:blank:]]]*ScriptAlias[[[:blank:]]][[[:blank:]]]*[[^[:blank:]]][[^[:blank:]]]*[[[:blank:]]][[[:blank:]]]*"\([[^"]]*\)"$'
AC_CHECK_FILE($SERVER_CONFIG_FILE,
        [HTTPD_SERVER_ROOT=`grep $SERVER_ROOT_PATTERN $SERVER_CONFIG_FILE | head -n 1 | sed "s/$SERVER_ROOT_PATTERN/\1/" | sed s/[[/]]$//`;
                HTTPD_USER=`grep $HTTPD_USER_PATTERN $SERVER_CONFIG_FILE | sed "s/$HTTPD_USER_PATTERN/\1/"`;
                HTTPD_GROUP=`grep $HTTPD_GROUP_PATTERN $SERVER_CONFIG_FILE | sed "s/$HTTPD_GROUP_PATTERN/\1/"`;
                HTTPD_DOC_HOME=`grep $DOCUMENT_ROOT_PATTERN $SERVER_CONFIG_FILE | head -n 1 | sed "s/$DOCUMENT_ROOT_PATTERN/\1/" | sed s/[[/]]$//`;
                HTTPD_SCRIPT_HOME=`grep $SCRIPT_ALIAS_PATTERN $SERVER_CONFIG_FILE | head -n 1 | sed "s/$SCRIPT_ALIAS_PATTERN/\1/" | sed s/[[/]]$//`],
        AC_MSG_ERROR([httpd server-config-file (detected as $SERVER_CONFIG_FILE by $HTTPD_SERVER -V) cannot be found]))dnl

altlinux5=`grep -qi  "ALT Linux 5" /etc/altlinux-release &>/dev/null && echo 1 || echo 0`
altlinux4=`grep -qi  "ALT Linux 4" /etc/altlinux-release &>/dev/null && echo 1 || echo 0`
debian=`test -e /etc/debian_version && echo 1 || echo 0`

if [[ $altlinux5 -eq 1 ]]
then
    HTTPD_INCLUDE=$HTTPD_ROOT/`grep ^"Include " $SERVER_CONFIG_FILE | grep "sites-enabled" |sed -e 's|/.*$ |/|g'|sed -e 's/Include//g'|sed -e 's, ,,g'| sed -e 's/[[^\/]]*$//g'`
else 
    if [[ $altlinux4 -eq 1 ]]
    then
	HTTPD_INCLUDE=$HTTPD_ROOT/`grep ^"Include " $SERVER_CONFIG_FILE | grep "sites-enabled" |sed -e 's|/.*$ |/|g'|sed -e 's/Include//g'|sed -e 's, ,,g'| sed -e 's/[[^\/]]*$//g'`
    else
	if [[ $debian -eq 1 ]]
	then
	    HTTPD_INCLUDE=$HTTPD_ROOT/conf-available/
	else
	    HTTPD_INCLUDE=$HTTPD_ROOT/`grep ^"Include " $SERVER_CONFIG_FILE |sed -e 's|/.*$ |/|g'|sed -e 's/Include//g'|sed -e 's, ,,g'| sed -e 's/[[^\/]]*$//g'`
	fi
    fi
fi

if [[ "$HTTPD_INCLUDE" = "$HTTPD_ROOT/" ]]
then
    HTTPD_INCLUDE=`echo $SERVER_CONFIG_FILE |sed -e 's/\/httpd.conf//g'`
fi

])
