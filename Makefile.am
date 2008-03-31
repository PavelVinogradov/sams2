# not a GNU package. You can remove this line, if
# have all needed files, that a GNU package needs
AUTOMAKE_OPTIONS = foreign

SUBDIRS = src doc/src

EXTRA_DIST = doc php etc spec

dist-hook:
	find $(distdir) -type d -name .svn | xargs rm -rf

install-data-local:
	$(mkinstalldirs) $(DESTDIR)/$(datadir)/$(distdir)
	(cd $(abs_top_srcdir)/php && find . ! -wholename "*/.svn*" | cpio -pdum --no-preserve-owner $(DESTDIR)/$(datadir)/$(distdir))
	find $(DESTDIR)/$(datadir)/$(distdir) -type d | xargs chmod 755
	find $(DESTDIR)/$(datadir)/$(distdir) -type f | xargs chmod 644
	$(mkinstalldirs) $(DESTDIR)/$(sysconfdir)
	$(install_sh) -c -m 644 $(abs_top_srcdir)/etc/sams2.conf $(DESTDIR)/$(sysconfdir)

#uninstall-data:
#	rm -f $(DESTDIR)/$(sysconfdir)/sams2.conf
#	rm -rf $(DESTDIR)/$(datadir)/$(distdir)
