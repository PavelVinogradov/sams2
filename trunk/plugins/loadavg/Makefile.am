AUTOMAKE_OPTIONS = foreign 1.4
pkglib_LTLIBRARIES = libloadavg.la

libloadavg_la_SOURCES = loadavg.cpp readutmp.c uptime.c getloadavg.c
libloadavg_la_LDFLAGS = -no-undefined -version-info 1:0:0

noinst_HEADERS = readutmp.h

DISTCLEANFILES = Makefile.in

INCLUDES = $(all_includes)

AM_CXXFLAGS = -Wall
