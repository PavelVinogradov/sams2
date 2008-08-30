#!/bin/sh
#
#   Startup/shutdown script for the SQUID Access Management System (SAMS).
#
#   chkconfig: 345 56 10
#   description: Startup/shutdown script for the SQUID Access \
#                Management System (SAMS).
#

PATH="/sbin:/bin:/usr/bin:/usr/sbin:/usr/local/bin"

# Source function library.
. /etc/init.d/functions

DAEMON=__PREFIX/samsdaemon

prog=sams

start () {
	echo -n $"Starting $prog: "

	# start daemon
	daemon $DAEMON
        RETVAL=$?
	echo
	[ $RETVAL = 0 ] && touch /var/lock/subsys/samsdaemon
	return $RETVAL
}

stop () {
	# stop daemon
	echo -n $"Stopping $prog: "
	$DAEMON --stop
	RETVAL=$?
	echo
	[ $RETVAL = 0 ] && rm -f /var/lock/subsys/samsdaemon
}

restart() {
	stop
	start
}

case $1 in
	start)
		start
	;;
	stop)
		stop
	;;
	restart)
		restart
	;;
	condrestart)
		[ -f /var/lock/subsys/samsdaemon ] && restart || :
	;;	
	reload)
		echo -n $"Reloading $prog: "
		killproc $DAEMON -HUP
		RETVAL=$?
		echo
	;;
	status)
		status $DAEMON
		RETVAL=$?
	;;
	*)

	echo $"Usage: $prog {start|stop|restart|condrestart|reload|status}"
	exit 3
esac

exit $RETVAL
