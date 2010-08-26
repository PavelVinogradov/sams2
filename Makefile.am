# not a GNU package. You can remove this line, if
# have all needed files, that a GNU package needs
AUTOMAKE_OPTIONS = foreign 1.4

SUBDIRS = src php doc plugins/loadavg plugins/fsusage
bin_SCRIPTS = scripts/sams_send_email

EXTRA_DIST = Makefile.cvs doc php etc spec debian redhat scripts
DISTCLEANFILES = Makefile.in configure aclocal.m4 config.guess config.sub depcomp install-sh ltmain.sh missing

dist-hook:
	find $(distdir) -type d -name .svn | xargs rm -rf

docdir = $(datadir)/doc/$(PACKAGE)-$(VERSION)
HTTPD_ROOT = $(shell $(httpd_server) -V |grep HTTPD_ROOT|sed -e 's/"//g'|sed -e 's/=/ /g'|awk '{print $$3}' |sed -e 's/\n//g'|sed -e 's/ //g')
SERVER_CONFIG_FILE = $(HTTPD_ROOT)/$(shell `/usr/bin/which httpd` -V |grep SERVER_CONFIG_FILE|sed -e 's/"//g'|sed -e 's/=/ /g'|awk '{print $$3}' |sed -e 's/\n//g'|sed -e 's/ //g')
HTTPD_INCLUDE = $(HTTPD_ROOT)/$(shell grep ^"Include " $(SERVER_CONFIG_FILE) |sed -e 's|/.*$ |/|g'|awk '{print $$2,$$3,$$4}'|sed -e 's, ,,g')

install-data-local:
#	$(mkinstalldirs) $(DESTDIR)/$(datadir)/$(distdir)
	$(mkinstalldirs) $(DESTDIR)/$(sysconfdir)
	if [ ! -f $(DESTDIR)/$(sysconfdir)/sams2.conf ] ; then \
	    $(install_sh) -c -m 644 $(top_srcdir)/etc/sams2.conf $(DESTDIR)/$(sysconfdir) ;\
	fi
	if [ ! -f $(DESTDIR)/$(HTTPD_INCLUDE)/sams2.conf ] ; then \
	    $(install_sh) -c -m 644 $(top_srcdir)/etc/httpd_conf $(DESTDIR)/$(HTTPD_INCLUDE)/sams2.conf ;\
	    sed -i -e 's,__WEBPREFIX,$(datadir)/sams2,g' $(DESTDIR)/$(HTTPD_INCLUDE)/sams2.conf ;\
	fi
	if [ ! -f $(DESTDIR)/$(HTTPD_INCLUDE)/doc4sams2.conf ] ; then \
	    $(install_sh) -c -m 644 $(top_srcdir)/etc/doc_sams2_conf $(DESTDIR)/$(HTTPD_INCLUDE)/doc4sams2.conf ;\
	    sed -i -e 's,__DOCPREFIX,$(docdir),g' $(DESTDIR)/$(HTTPD_INCLUDE)/doc4sams2.conf ;\
	fi


uninstall-local:
	if [ -f $(DESTDIR)/$(sysconfdir)/sams2.conf ] ; then \
		mv -f $(DESTDIR)/$(sysconfdir)/sams2.conf $(DESTDIR)/$(sysconfdir)/sams2.conf.save ;\
	fi
	if [ -f $(DESTDIR)/$(HTTPD_INCLUDE)/sams2.conf ] ; then \
		mv -f $(DESTDIR)/$(HTTPD_INCLUDE)/sams2.conf $(DESTDIR)/$(HTTPD_INCLUDE)/sams2.conf.save ;\
	fi
	if [ -f  $(DESTDIR)/$(HTTPD_INCLUDE)/doc4sams2.conf ] ; then \
		mv -f $(DESTDIR)/$(HTTPD_INCLUDE)/doc4sams2.conf $(DESTDIR)/$(HTTPD_INCLUDE)/doc4sams2.conf.save ;\
	fi
