AUTOMAKE_OPTIONS = foreign 1.4
pkglib_LTLIBRARIES = libfsusage.la

libfsusage_la_SOURCES = fsusage.cpp getfsusage.cpp human.cpp
libfsusage_la_LDFLAGS = -no-undefined -version-info 1:0:0

noinst_HEADERS = human.h

DISTCLEANFILES = Makefile.in

INCLUDES = $(all_includes)

AM_CXXFLAGS = -Wall
