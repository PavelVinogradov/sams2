# not a GNU package. You can remove this line, if
# have all needed files, that a GNU package needs
AUTOMAKE_OPTIONS = foreign 1.4

SUBDIRS = src php doc plugins/loadavg plugins/fsusage
bin_SCRIPTS = scripts/sams_send_email

EXTRA_DIST = Makefile.cvs doc php etc spec debian redhat scripts
DISTCLEANFILES = Makefile.in configure aclocal.m4 config.guess config.sub depcomp install-sh ltmain.sh missing

dist-hook:
	find $(distdir) -type d -name .svn | xargs rm -rf

install-data-local:
	$(mkinstalldirs) $(DESTDIR)/$(datadir)/$(distdir)
	$(mkinstalldirs) $(DESTDIR)/$(sysconfdir)
	if [ ! -f $(DESTDIR)/$(sysconfdir)/sams2.conf ] ; then \
		$(install_sh) -c -m 644 $(top_srcdir)/etc/sams2.conf $(DESTDIR)/$(sysconfdir) ;\
	fi

uninstall-local:
	if [ -f $(DESTDIR)/$(sysconfdir)/sams2.conf ] ; then \
		mv $(DESTDIR)/$(sysconfdir)/sams2.conf $(DESTDIR)/$(sysconfdir)/sams2.conf.save ;\
	fi
