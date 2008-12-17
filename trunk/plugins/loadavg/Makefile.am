AUTOMAKE_OPTIONS = foreign 1.4
pkglib_LTLIBRARIES = libloadavg.la

libloadavg_la_SOURCES = loadavg.cpp
libloadavg_la_LDFLAGS = -no-undefined -version-info 1:0:0

DISTCLEANFILES = Makefile.in

INCLUDES = $(all_includes)

AM_CXXFLAGS = -Wall
