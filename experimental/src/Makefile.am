#
# Makefile.am --- automake input file for gawk
#
# Copyright (C) 2000-2001 the Free Software Foundation, Inc.
#
# Automatic de-ANSI-fication if needed

# This insures that make flags get passed down to child makes.
#AM_MAKEFLAGS = 'CFLAGS=$(CFLAGS)' 'LDFLAGS=$(LDFLAGS)'
AM_MAKEFLAGS = 'CPPFLAGS=$(CPPFLAGS)' 'LDFLAGS=$(LDFLAGS)'
CPPFLAGS='-Wno-deprecated'

# what to make and install
bin_PROGRAMS = samsparser

# sources
samsparser_SOURCES = \
	samsparser.cpp \
	samsconfig.cpp \
	samsconfig.h \
	samsfile.cpp \
	samsfile.h \
	samsuser.cpp \
	samsuser.h \
	samsdb.cpp \
	samsdb.h \
	debug.cpp \
	debug.h \
	tools.cpp \
	tools.h \
	defines.h

# stuff for compiling gawk/pgawk
DEFPATH="\".:$(datadir)\""

DEFS= -DDEFPATH=$(DEFPATH) -DHAVE_CONFIG_H -DLOCALEDIR="\"$(datadir)/locale\""

INCLUDES = -I. $(MYSQLHEADERS) $(ODBCHEADERS)

LIBS = -L. $(MYSQLLIBS) $(ODBCLIBS)

# We want hard links for install-exec-hook, below
LN= ln

