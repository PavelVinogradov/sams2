#!/bin/sh
#
#   Startup/shutdown script for the SQUID Access Management System (SAMS).
#
#   chkconfig: - 70 30
#   description: Startup/shutdown script for the SQUID Access \
#                Management System (SAMS).
#

PATH="/sbin:/bin:/usr/bin:/usr/sbin:/usr/local/bin"

# Source function library.
. /etc/init.d/functions

# check if the sams conf file is present
[ -f __CONFDIR/sams2.conf ] || exit 0

if [ -f /etc/sysconfig/sams2 ]; then
  . /etc/sysconfig/sams2
fi

# don't raise an error if the config file is incomplete
# set defaults instead:
OPTIONS=${OPTIONS:-"-l syslog"}
PIDFILE_TIMEOUT=${PIDFILE_TIMEOUT:-5}
SHUTDOWN_TIMEOUT=${SHUTDOWN_TIMEOUT:-60}

DAEMON=__PREFIX/sams2daemon

prog=sams2

start () {
	echo -n $"Starting $prog: "

	# start daemon
	$DAEMON $OPTIONS


	RETVAL=$?
	if [ $RETVAL -eq 0 ]; then
		timeout=0;
		while : ; do
			[ ! -f /var/run/sams2daemon.pid ] || break
			if [ $timeout -ge $PIDFILE_TIMEOUT ]; then
				RETVAL=1
				break
			fi
			sleep 1 && echo -n "."
			timeout=$((timeout+1))
		done
	fi
	[ $RETVAL -eq 0 ] && touch /var/lock/subsys/sams2daemon
	[ $RETVAL -eq 0 ] && echo_success
	[ $RETVAL -ne 0 ] && echo_failure
	echo
	return $RETVAL
}

stop () {
	# stop daemon
	echo -n $"Stopping $prog: "
	$DAEMON --stop
	RETVAL=$?
	if [ $RETVAL -eq 0 ] ; then
		rm -f /var/lock/subsys/sams2daemon
		timeout=0
		while : ; do
			[ -f /var/run/sams2daemon.pid ] || break
			if [ $timeout -ge $SHUTDOWN_TIMEOUT ]; then
				echo_failure
				echo
				return 1
			fi
			sleep 2 && echo -n "."
			timeout=$((timeout+2))
		done
		echo_success
		echo
	else
		echo_failure
		echo
	fi
	return $RETVAL
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
		[ -f /var/lock/subsys/sams2daemon ] && restart || :
	;;	
	reload)
		echo -n $"Reloading $prog: "
		killproc $DAEMON -HUP
		RETVAL=$?
		[ $RETVAL -eq 0 ] && echo_success
		[ $RETVAL -ne 0 ] && echo_failure
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
