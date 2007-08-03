#
# Makefile.am --- automake input file for gawk
#
# Copyright (C) 2000-2001 the Free Software Foundation, Inc.
#
# Automatic de-ANSI-fication if needed

# This undocumented variable insures that aclocal runs
# correctly after changing configure.in
ACLOCAL_AMFLAGS = -I m4

# This insures that make flags get passed down to child makes.
AM_MAKEFLAGS = 'CFLAGS=$(CFLAGS)' 'LDFLAGS=$(LDFLAGS)'

# Stuff to include in the dist that doesn't need it's own
# Makefile.am files
EXTRA_DIST = \
	COPYING 

# what to make and install
bin_PROGRAMS = sams samsf samsredir samsdaemon 

# sources for both squidlog
base_sources = \
	demon.c \
	redirect.c \
	daemon.c \
	samsdaemon.c \
	define.h \
	tools.c \
	tools.h 

squidlog_SOURCES = $(base_sources) 

# stuff for compiling gawk/pgawk
DEFPATH="\".:$(datadir)\""

DEFS= -DDEFPATH=$(DEFPATH) -DHAVE_CONFIG_H -DLOCALEDIR="\"$(datadir)/locale\""

INCLUDES = -I. $(MYSQLHEADERS) 

LIBS = -I. $(MYSQLLIBS) 

# We want hard links for install-exec-hook, below
LN= ln

