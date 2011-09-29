#!/bin/sh -e

### BEGIN INIT INFO
# Provides:		sams
# Required-Start:	$local_fs $network $time $remote_fs
# Required-Stop:	
# Should-Start:		$named $mysql $squid
# Should-Stop:
# Default-Start:	2 3 4 5
# Default-Stop:		0 1 6
# Short-Description:	Starting sams daemon
# Description:		Squid Account Management System (SAMS)
#  Starting sams management daemon - sams2daemon
### END INIT INFO
#
# Author:	Pavel Vinogradov <Pavel.Vinogradov@nixdev.net>
#
# /etc/init.d/sams2: start and stop the sams daemon

SAMSPATH=`cat /etc/sams2.conf | grep SAMSPATH | tr "SAMSPATH=" "\0"`
NAME="sams"
DAEMON=$SAMSPATH/bin/sams2daemon
LOCKFILE=/var/lock/samsd
PIDFILE=/var/run/sams2daemon.pid
RETVAL=0
SAMS_ENABLE=false

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
		if "$SAMS_ENABLE"; then
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
			[ "VERBOSE" != no ] && log_warning_msg "$NAME daemon not enabled, not starting. Please read /usr/share/doc/sams2/README.Debian"
		fi
	;;

	stop)
		if "$SAMS_ENABLE"; then
			log_daemon_msg "Stopping $NAME daemon" "$NAME"
			start-stop-daemon --stop --quiet --oknodo --pidfile $PIDFILE 
			RETVAL=$?
			[ $RETVAL -eq 0 ] && rm -f "$LOCKFILE"
			log_end_msg $RETVAL
		else
			[ "VERBOSE" != no ] && log_warning_msg "$NAME daemon not enabled, not stoping..."
		fi
			
	;;

	restart|force-reload)
		/etc/init.d/sams2 stop
		/etc/init.d/sams2 start
	;;
	
	*)
		echo "Usage: ${0##*/} {start|stop|restart}"
		RETVAL=1
	;;
esac
