AUTOMAKE_OPTIONS = foreign 1.4
BUILT_SOURCES =
CLEANFILES =
DISTCLEANFILES = Makefile.in config.h.in

bin_PROGRAMS = samsparser sams2daemon samsredir

# set the include path found by configure
INCLUDES = $(all_includes)

# the library search path.
samsparser_SOURCES = \
	samsparser.cpp debug.cpp samsconfig.cpp tools.cpp \
	odbcconn.cpp squidlogline.cpp filter.cpp userfilter.cpp datefilter.cpp odbcquery.cpp \
	samsuserlist.cpp samsuser.cpp net.cpp ip.cpp localnetworks.cpp url.cpp \
	squidlogparser.cpp proxy.cpp dbcleaner.cpp logger.cpp processmanager.cpp mysqlconn.cpp \
	dbconn.cpp dbquery.cpp mysqlquery.cpp templatelist.cpp template.cpp grouplist.cpp \
	pgconn.cpp pgquery.cpp timerangelist.cpp timerange.cpp urlgrouplist.cpp urlgroup.cpp \
	dns.cpp userlist.cpp samsldap.cpp
sams2daemon_SOURCES = \
	datefilter.cpp debug.cpp filter.cpp ip.cpp \
	localnetworks.cpp logger.cpp net.cpp odbcconn.cpp odbcquery.cpp processmanager.cpp \
	proxy.cpp samsconfig.cpp samsuser.cpp samsuserlist.cpp tools.cpp url.cpp \
	userfilter.cpp samsdaemon.cpp dbconn.cpp dbquery.cpp mysqlconn.cpp mysqlquery.cpp \
	template.cpp templatelist.cpp squidlogparser.cpp squidlogline.cpp grouplist.cpp \
	squidconf.cpp timerange.cpp timerangelist.cpp pgconn.cpp pgquery.cpp dbexporter.cpp \
	dbcleaner.cpp urlgrouplist.cpp urlgroup.cpp pluginlist.cpp dns.cpp userlist.cpp \
	samsldap.cpp delaypool.cpp delaypoollist.cpp
samsredir_SOURCES = \
	dbconn.cpp dbquery.cpp debug.cpp ip.cpp localnetworks.cpp \
	logger.cpp mysqlconn.cpp mysqlquery.cpp odbcconn.cpp odbcquery.cpp \
	processmanager.cpp proxy.cpp samsconfig.cpp samsuser.cpp samsuserlist.cpp template.cpp \
	templatelist.cpp tools.cpp url.cpp samsredir.cpp grouplist.cpp net.cpp pgconn.cpp \
	pgquery.cpp urlgrouplist.cpp urlgroup.cpp timerangelist.cpp timerange.cpp dns.cpp userlist.cpp \
	samsldap.cpp
noinst_HEADERS = \
	debug.h samsconfig.h tools.h odbcconn.h squidlogline.h \
	filter.h userfilter.h datefilter.h odbcquery.h samsuserlist.h samsuser.h net.h ip.h \
	localnetworks.h url.h squidlogparser.h proxy.h dbcleaner.h logger.h processmanager.h \
	mysqlconn.h dbconn.h dbquery.h mysqlquery.h templatelist.h template.h grouplist.h \
	squidconf.h timerange.h timerangelist.h pgconn.h pgquery.h dbexporter.h \
	urlgrouplist.h urlgroup.h pluginlist.h dns.h userlist.h samsldap.h delaypool.h \
	delaypoollist.h

AM_CXXFLAGS = -Wall

configmake.h: Makefile
	rm -f $@-t $@
	{ echo '/* DO NOT EDIT! GENERATED AUTOMATICALLY! */'; \
	  echo '#define PREFIX "$(prefix)"'; \
	  echo '#define EXEC_PREFIX "$(exec_prefix)"'; \
	  echo '#define BINDIR "$(bindir)"'; \
	  echo '#define SBINDIR "$(sbindir)"'; \
	  echo '#define LIBEXECDIR "$(libexecdir)"'; \
	  echo '#define DATAROOTDIR "$(datarootdir)"'; \
	  echo '#define DATADIR "$(datadir)"'; \
	  echo '#define SYSCONFDIR "$(sysconfdir)"'; \
	  echo '#define SHAREDSTATEDIR "$(sharedstatedir)"'; \
	  echo '#define LOCALSTATEDIR "$(localstatedir)"'; \
	  echo '#define INCLUDEDIR "$(includedir)"'; \
	  echo '#define OLDINCLUDEDIR "$(oldincludedir)"'; \
	  echo '#define DOCDIR "$(docdir)"'; \
	  echo '#define INFODIR "$(infodir)"'; \
	  echo '#define HTMLDIR "$(htmldir)"'; \
	  echo '#define DVIDIR "$(dvidir)"'; \
	  echo '#define PDFDIR "$(pdfdir)"'; \
	  echo '#define PSDIR "$(psdir)"'; \
	  echo '#define LIBDIR "$(libdir)"'; \
	  echo '#define LISPDIR "$(lispdir)"'; \
	  echo '#define LOCALEDIR "$(localedir)"'; \
	  echo '#define MANDIR "$(mandir)"'; \
	  echo '#define MANEXT "$(manext)"'; \
	  echo '#define PKGDATADIR "$(pkgdatadir)"'; \
	  echo '#define PKGINCLUDEDIR "$(pkgincludedir)"'; \
	  echo '#define PKGLIBDIR "$(pkglibdir)"'; \
	  echo '#define PKGLIBEXECDIR "$(pkglibexecdir)"'; \
	} | sed '/""/d' > $@-t
	mv $@-t $@
BUILT_SOURCES += configmake.h
CLEANFILES += configmake.h configmake.h-t

