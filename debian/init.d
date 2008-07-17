#!/bin/sh -e

### BEGIN INIT INFO
# Provides:		sams
# Required-Start:	$local_fs $network $time
# Required-Stop:	
# Should-Start:		$named $mysql $squid
# Should-Stop:
# Default-Start:	2 3 4 5
# Default-Stop:		0 1 6
# Short-Description:	Starting sams daemon
# Description:		Squid Account Management System (SAMS)
#  Starting sams management daemon - samsdaemon
### END INIT INFO
#
# Author:	Pavel Vinogradov <Pavel.Vinogradov@nixdev.net>
#
# /etc/init.d/sams: start and stop the sams daemon

SAMSPATH=`cat /etc/sams.conf | grep SAMSPATH | tr "SAMSPATH=" "\0"`
NAME="sams"
DAEMON=$SAMSPATH/bin/samsdaemon
LOCKFILE=/var/lock/samsd
PIDFILE=/var/run/samsdaemon.pid
RETVAL=0
SAMSENABLE=true

test -x $DAEMON || exit 0

if ! [ -x "/lib/lsb/init-functions" ]; then
	. /lib/lsb/init-functions
else
	echo "E: /lib/lsb/init-functions not found, lsb-base (>= 3.0-6) needed"
	exit 1
fi

. /etc/default/rcS

case "$1" in 
	start)
		if "$SAMSENABLE"; then
			log_daemon_msg "Starting $NAME daemon" "$NAME"
			if [ -s $PIDFILE ] && kill -0 $(cat $PIDFILE) >/dev/null 2>&1; then
				log_progress_msg "apparently already running"
				log_end_msg 0
				exit 0
			fi
      			
			start-stop-daemon --start --quiet --background \
				--pidfile $PIDFILE \
				--exec $DAEMON
			RETVAL=$?
			[ $RETVAL -eq 0 ] && touch "$LOCKFILE"
			log_end_msg $RETVAL
		else
			[ "VERBOSE" != no ] && log_warning_msg "$NAME daemon not enabled, not starting..."
		fi
	;;

	stop)
		log_daemon_msg "Stopping $NAME daemon" "$NAME"
		start-stop-daemon --stop --quiet --oknodo --pidfile $PIDFILE 
		RETVAL=$?
		[ $RETVAL -eq 0 ] && rm -f "$LOCKFILE"
		log_end_msg $RETVAL
	;;
esac

start()
{
	echo -n "Starting samsd: "
	start-stop-daemon --start --quiet --exec $DAEMON
	RETVAL=$?
	[ $RETVAL -eq 0 ] && touch "$LOCKFILE"
        echo
}

stop()
{
	echo -n "Shutting down samsd: "
	start-stop-daemon --stop --quiet --pidfile $PIDFILE
	RETVAL=$?
	[ $RETVAL -eq 0 ] && rm -f "$LOCKFILE"
        echo
}

restart()
{
	stop
	start
}

# See how we were called.
case "$1" in
	start)
		start
		;;
	stop)
		stop
		;;
	restart)
		restart
		;;
	status)
	        status "$DAEMON"
		;;
	*)
		echo "Usage: ${0##*/} {start|stop|restart}"
		RETVAL=1
esac

exit $RETVAL