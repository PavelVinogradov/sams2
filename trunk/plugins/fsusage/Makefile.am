AUTOMAKE_OPTIONS = foreign 1.4
pkglib_LTLIBRARIES = libfsusage.la

libfsusage_la_SOURCES = fsusage.cpp df_c.c fsusage_c.c save-cwd.c dirname.c mountlist.c
libfsusage_la_LDFLAGS = -no-undefined -version-info 1:0:0

DISTCLEANFILES = Makefile.in

INCLUDES = $(all_includes)

AM_CXXFLAGS = -Wall
