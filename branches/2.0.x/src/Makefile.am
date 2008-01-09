BUILT_SOURCES =
CLEANFILES =

bin_PROGRAMS = samsparser samsdaemon

# set the include path found by configure
INCLUDES = $(all_includes)

# the library search path.
samsparser_SOURCES = samsparser.cpp debug.cpp samsconfig.cpp tools.cpp \
													odbcconn.cpp squidlogline.cpp filter.cpp userfilter.cpp datefilter.cpp odbcquery.cpp \
											samsusers.cpp samsuser.cpp net.cpp ip.cpp localnetworks.cpp url.cpp \
									squidlogparser.cpp proxy.cpp dbcleaner.cpp logger.cpp processmanager.cpp mysqlconn.cpp \
				dbconn.cpp global.cpp dbquery.cpp mysqlquery.cpp
noinst_HEADERS = debug.h samsconfig.h tools.h odbcconn.h squidlogline.h \
												filter.h userfilter.h datefilter.h odbcquery.h samsusers.h samsuser.h net.h ip.h \
									localnetworks.h url.h squidlogparser.h proxy.h dbcleaner.h logger.h processmanager.h \
					mysqlconn.h dbconn.h global.h dbquery.h mysqlquery.h
samsparser_LDFLAGS = $(MYSQL_LIB) $(UNIXODBC_LIB)
AM_CXXFLAGS = -Wall $(MYSQL_INC) $(UNIXODBC_INC)

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

samsdaemon_SOURCES = datefilter.cpp debug.cpp filter.cpp ip.cpp \
			localnetworks.cpp logger.cpp net.cpp odbcconn.cpp odbcquery.cpp processmanager.cpp \
			proxy.cpp samsconfig.cpp samsuser.cpp samsusers.cpp tools.cpp url.cpp \
			userfilter.cpp samsdaemon.cpp dbconn.cpp dbquery.cpp global.cpp mysqlconn.cpp \
	mysqlquery.cpp
samsdaemon_LDFLAGS = $(MYSQL_LIB) $(UNIXODBC_LIB)
