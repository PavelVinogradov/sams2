#!/bin/bash
#
#     Template LSB system startup script for example service/daemon FOO
#     Copyright (C) 1995--2005  Kurt Garloff, SUSE / Novell Inc.
#          
#     This library is free software; you can redistribute it and/or modify it
#     under the terms of the GNU Lesser General Public License as published by
#     the Free Software Foundation; either version 2.1 of the License, or (at
#     your option) any later version.
#			      
#     This library is distributed in the hope that it will be useful, but
#     WITHOUT ANY WARRANTY; without even the implied warranty of
#     MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the GNU
#     Lesser General Public License for more details.
#      
#     You should have received a copy of the GNU Lesser General Public
#     License along with this library; if not, write to the Free Software
#     Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA 02111-1307,
#     USA.
#
# /etc/init.d/sams2
# LSB compatible service control script; see http://www.linuxbase.org/spec/
# Please send feedback to http://www.suse.de/feedback/
# 
# Note: This template uses functions rc_XXX defined in /etc/rc.status on
# UnitedLinux/SUSE/Novell based Linux distributions. However, it will work
# on other distributions as well, by using the LSB (Linux Standard Base) 
# or RH functions or by open coding the needed functions.
# Read http://www.tldp.org/HOWTO/HighQuality-Apps-HOWTO/ if you prefer not 
# to use this template.
#
# chkconfig: 345 99 00
# description: sams2 main daemon providing parse and config functionality
# 
### BEGIN INIT INFO
# Provides:          sams2
# Required-Start:    $syslog $network $named $remote_fs
# Should-Start:      squid mysql
# Required-Stop:     
# Should-Stop:       squid
# Default-Start:     3 4 5
# Default-Stop:      0 1 2 6
# Short-Description: sams2 main daemon providing parse and config functionality
# Description:       Start FOO to allow XY and provide YZ
#	continued on second line by '#<TAB>'
#	should contain enough info for the runlevel editor
#	to give admin some idea what this service does and
#	what it's needed for ...
#	(The Short-Description should already be a good hint.)
### END INIT INFO
# 
# Any extensions to the keywords given above should be preceeded by 
# X-VendorTag- according to LSB.
# 
# Notes on Required-Start/Should-Start:
# * There are two different issues that are solved by Required-Start
#    and Should-Start
# (a) Hard dependencies: This is used by the runlevel editor to determine
#     which services absolutely need to be started to make the start of
#     this service make sense. Example: nfsserver should have
#     Required-Start: $portmap
#     Also, required services are started before the dependent ones.
#     The runlevel editor will warn about such missing hard dependencies
#     and suggest enabling. During system startup, you may expect an error,
#     if the dependency is not fulfilled.
# (b) Specifying the init script ordering, not real (hard) dependencies.
#     This is needed by insserv to determine which service should be
#     started first (and at a later stage what services can be started
#     in parallel). The tag Should-Start: is used for this.
#     It tells, that if a service is available, it should be started
#     before. If not, never mind.
# * When specifying hard dependencies or ordering requirements, you can 
#   use names of services (contents of their Provides: section)
#   or pseudo names starting with a $. The following ones are available
#   according to LSB (1.1):
#	$local_fs		all local file systems are mounted
#				(most services should need this!)
#	$remote_fs		all remote file systems are mounted
#				(note that /usr may be remote, so
#				 many services should Require this!)
#	$syslog			system logging facility up
#	$network		low level networking (eth card, ...)
#	$named			hostname resolution available
#	$netdaemons		all network daemons are running
#   The $netdaemons pseudo service has been removed in LSB 1.2.
#   For now, we still offer it for backward compatibility.
#   These are new (LSB 1.2):
#	$time			the system time has been set correctly	
#	$portmap		SunRPC portmapping service available
#   UnitedLinux/SUSE/Novell extensions:
#	$ALL			indicates that a script should be inserted
#				at the end
# * The services specified in the stop tags 
#   (Required-Stop/Should-Stop)
#   specify which services need to be still running when this service
#   is shut down. Often the entries there are just copies or a subset 
#   from the respective start tag.
# * Should-Start/Stop are now part of LSB as of 2.0,
#   formerly SUSE/Unitedlinux used X-UnitedLinux-Should-Start/-Stop.
#   insserv does support both variants.
# * X-UnitedLinux-Default-Enabled: yes/no is used at installation time
#   (%fillup_and_insserv macro in %post of many RPMs) to specify whether
#   a startup script should default to be enabled after installation.
#
# Note on runlevels:
# 0 - halt/poweroff 			6 - reboot
# 1 - single user			2 - multiuser without network exported
# 3 - multiuser w/ network (text mode)  5 - multiuser w/ network and X11 (xdm)
# 
# Note on script names:
# http://www.linuxbase.org/spec/refspecs/LSB_1.3.0/gLSB/gLSB/scrptnames.html
# A registry has been set up to manage the init script namespace.
# http://www.lanana.org/
# Please use the names already registered or register one or use a
# vendor prefix.


# Check for missing binaries (stale symlinks should not happen)
# Note: Special treatment of stop for LSB conformance
SAMS2_BIN=__PREFIX/sams2daemon
test -x $SAMS2_BIN || { echo "$SAMS2_BIN not installed"; 
	if [ "$1" = "stop" ]; then exit 0;
	else exit 5; fi; }

# Check for existence of needed config file and read it
SAMS2_CONFIG=/etc/sysconfig/sams2
test -r $SAMS2_CONFIG || { echo "$SAMS2_CONFIG not existing";
	if [ "$1" = "stop" ]; then exit 0;
	else exit 6; fi; }
# Read config	
. $SAMS2_CONFIG

OPTIONS=${OPTIONS:-"-l syslog"}
PIDFILE_TIMEOUT=${PIDFILE_TIMEOUT:-5}
SHUTDOWN_TIMEOUT=${SHUTDOWN_TIMEOUT:-60}

SAMS2_INI=__CONFDIR/sams2.conf
test -r $SAMS2_INI || { echo "$SAMS2_INI not existing";
	if [ "$1" = "stop" ]; then exit 0;
	else exit 6; fi; }


# Source LSB init functions
# providing start_daemon, killproc, pidofproc, 
# log_success_msg, log_failure_msg and log_warning_msg.
# This is currently not used by UnitedLinux based distributions and
# not needed for init scripts for UnitedLinux only. If it is used,
# the functions from rc.status should not be sourced or used.
#. /lib/lsb/init-functions

# Shell functions sourced from /etc/rc.status:
#      rc_check         check and set local and overall rc status
#      rc_status        check and set local and overall rc status
#      rc_status -v     be verbose in local rc status and clear it afterwards
#      rc_status -v -r  ditto and clear both the local and overall rc status
#      rc_status -s     display "skipped" and exit with status 3
#      rc_status -u     display "unused" and exit with status 3
#      rc_failed        set local and overall rc status to failed
#      rc_failed <num>  set local and overall rc status to <num>
#      rc_reset         clear both the local and overall rc status
#      rc_exit          exit appropriate to overall rc status
#      rc_active        checks whether a service is activated by symlinks

# Use the SUSE rc_ init script functions;
# emulate them on LSB, RH and other systems

# Default: Assume sysvinit binaries exist
start_daemon() { /sbin/start_daemon ${1+"$@"}; }
killproc()     { /sbin/killproc     ${1+"$@"}; }
pidofproc()    { /sbin/pidofproc    ${1+"$@"}; }
checkproc()    { /sbin/checkproc    ${1+"$@"}; }
if test -e /etc/rc.status; then
    # SUSE rc script library
    . /etc/rc.status
else
    export LC_ALL=POSIX
    _cmd=$1
    declare -a _SMSG
    if test "${_cmd}" = "status"; then
	_SMSG=(running dead dead unused unknown reserved)
	_RC_UNUSED=3
    else
	_SMSG=(done failed failed missed failed skipped unused failed failed reserved)
	_RC_UNUSED=6
    fi
    if test -e /lib/lsb/init-functions; then
	# LSB    
    	. /lib/lsb/init-functions
	echo_rc()
	{
	    if test ${_RC_RV} = 0; then
		log_success_msg "  [${_SMSG[${_RC_RV}]}] "
	    else
		log_failure_msg "  [${_SMSG[${_RC_RV}]}] "
	    fi
	}
	# TODO: Add checking for lockfiles
	checkproc() { return pidofproc ${1+"$@"} >/dev/null 2>&1; }
    elif test -e /etc/init.d/functions; then
	# RHAT
	. /etc/init.d/functions
	echo_rc()
	{
	    #echo -n "  [${_SMSG[${_RC_RV}]}] "
	    if test ${_RC_RV} = 0; then
		success "  [${_SMSG[${_RC_RV}]}] "
	    else
		failure "  [${_SMSG[${_RC_RV}]}] "
	    fi
	}
	checkproc() { return status ${1+"$@"}; }
	start_daemon() { return daemon ${1+"$@"}; }
    else
	# emulate it
	echo_rc() { echo "  [${_SMSG[${_RC_RV}]}] "; }
    fi
    rc_reset() { _RC_RV=0; }
    rc_failed()
    {
	if test -z "$1"; then 
	    _RC_RV=1;
	elif test "$1" != "0"; then 
	    _RC_RV=$1; 
    	fi
	return ${_RC_RV}
    }
    rc_check()
    {
	return rc_failed $?
    }	
    rc_status()
    {
	rc_failed $?
	if test "$1" = "-r"; then _RC_RV=0; shift; fi
	if test "$1" = "-s"; then rc_failed 5; echo_rc; rc_failed 3; shift; fi
	if test "$1" = "-u"; then rc_failed ${_RC_UNUSED}; echo_rc; rc_failed 3; shift; fi
	if test "$1" = "-v"; then echo_rc; shift; fi
	if test "$1" = "-r"; then _RC_RV=0; shift; fi
	return ${_RC_RV}
    }
    rc_exit() { exit ${_RC_RV}; }
    rc_active() 
    {
	if test -z "$RUNLEVEL"; then read RUNLEVEL REST < <(/sbin/runlevel); fi
	if test -e /etc/init.d/S[0-9][0-9]${1}; then return 0; fi
	return 1
    }
fi

# Reset status of this service
rc_reset

case "$1" in
    start)
	echo -n "Starting SAMS2 "
	## Start daemon with startproc(8). If this fails
	## the return value is set appropriately by startproc.
	start_daemon $SAMS2_BIN $OPTIONS

	# Remember status and be verbose
	rc_status -v
	;;
    stop)
	echo -n "Shutting down sams2 "
	## Stop daemon with killproc(8) and if this fails
	## killproc sets the return value according to LSB.

	killproc -TERM $SAMS2_BIN

	# Remember status and be verbose
	rc_status -v
	;;
    try-restart|condrestart)
	## Do a restart only if the service was active before.
	## Note: try-restart is now part of LSB (as of 1.9).
	## RH has a similar command named condrestart.
	if test "$1" = "condrestart"; then
		echo "${attn} Use try-restart ${done}(LSB)${attn} rather than condrestart ${warn}(RH)${norm}"
	fi
	$0 status
	if test $? = 0; then
		$0 restart
	else
		rc_reset	# Not running is not a failure.
	fi
	# Remember status and be quiet
	rc_status
	;;
    restart)
	## Stop the service and regardless of whether it was
	## running or not, start it again.
	$0 stop
	$0 start

	# Remember status and be quiet
	rc_status
	;;
    force-reload)
	## Signal the daemon to reload its config. Most daemons
	## do this on signal 1 (SIGHUP).
	## If it does not support it, restart the service if it
	## is running.

	echo -n "Reload service SAMS2 "
	## if it supports it:
	killproc -HUP $SAMS2_BIN
	#touch /var/run/sams2.pid
	rc_status -v

	## Otherwise:
	#$0 try-restart
	#rc_status
	;;
    reload)
	## Like force-reload, but if daemon does not support
	## signaling, do nothing (!)

	# If it supports signaling:
	echo -n "Reload service sams2 "
	killproc -HUP $SAMS2_BIN
	#touch /var/run/sams2.pid
	rc_status -v
	
	## Otherwise if it does not support reload:
	#rc_failed 3
	#rc_status -v
	;;
    status)
	echo -n "Checking for service SAMS2 "
	## Check status with checkproc(8), if process is running
	## checkproc will return with exit status 0.

	# Return value is slightly different for the status command:
	# 0 - service up and running
	# 1 - service dead, but /var/run/  pid  file exists
	# 2 - service dead, but /var/lock/ lock file exists
	# 3 - service not running (unused)
	# 4 - service status unknown :-(
	# 5--199 reserved (5--99 LSB, 100--149 distro, 150--199 appl.)
	
	# NOTE: checkproc returns LSB compliant status values.
	checkproc $SAMS2_BIN
	# NOTE: rc_status knows that we called this init script with
	# "status" option and adapts its messages accordingly.
	rc_status -v
	;;
    probe)
	## Optional: Probe for the necessity of a reload, print out the
	## argument to this init script which is required for a reload.
	## Note: probe is not (yet) part of LSB (as of 1.9)

	test __CONFDIR/sams2.conf -nt /var/run/sams2.pid && echo reload
	;;
    *)
	echo "Usage: $0 {start|stop|status|try-restart|restart|force-reload|reload|probe}"
	exit 1
	;;
esac
rc_exit
