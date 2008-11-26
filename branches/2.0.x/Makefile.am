# not a GNU package. You can remove this line, if
# have all needed files, that a GNU package needs
AUTOMAKE_OPTIONS = foreign

SUBDIRS = src doc/src plugins/loadavg
bin_SCRIPTS = scripts/sams_send_email

EXTRA_DIST = doc php etc spec debian redhat scripts

dist-hook:
	find $(distdir) -type d -name .svn | xargs rm -rf

install-data-local:
	$(mkinstalldirs) $(DESTDIR)/$(datadir)/$(distdir)
	(cd $(top_srcdir)/php && find . ! -wholename "*/.svn*" | cpio -pdum --no-preserve-owner $(DESTDIR)/$(datadir)/$(distdir))
	sed -i -e 's,__VERSION,$(VERSION),g' $(DESTDIR)/$(datadir)/$(distdir)/dbclass.php
	find $(DESTDIR)/$(datadir)/$(distdir) -type d | xargs chmod 755
	find $(DESTDIR)/$(datadir)/$(distdir) -type f | xargs chmod 644
	$(mkinstalldirs) $(DESTDIR)/$(sysconfdir)
	if [ ! -f $(DESTDIR)/$(sysconfdir)/sams2.conf ] ; then \
		$(install_sh) -c -m 644 $(top_srcdir)/etc/sams2.conf $(DESTDIR)/$(sysconfdir) ;\
		sed -i -e 's,sams2.conf,${sysconfdir}/sams2.conf,g' $(DESTDIR)/$(datadir)/$(distdir)/config.php ;\
	fi
