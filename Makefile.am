# not a GNU package. You can remove this line, if
# have all needed files, that a GNU package needs
AUTOMAKE_OPTIONS = foreign

SUBDIRS = src

EXTRA_DIST = php etc

dist-hook:
	find $(distdir) -type d -name .svn | xargs rm -rf

install-data-local:
	test -d $(pkgdatadir) || mkdir $(pkgdatadir)
	(cd $(abs_top_srcdir)/php && find . ! -wholename "*/.svn*" | cpio -pdum --no-preserve-owner $(pkgdatadir))
	find $(pkgdatadir) -type d | xargs chmod 755
	find $(pkgdatadir) -type f | xargs chmod 644
	(cd $(abs_top_srcdir)/doc && find . ! -wholename "*/.svn*" | cpio -pdum --no-preserve-owner $(docdir))
	test -f $(sysconfdir)/sams2.conf && mv $(sysconfdir)/sams2.conf $(sysconfdir)/sams2.conf.old
	$(install_sh_DATA) $(abs_top_srcdir)/etc/sams2.conf $(sysconfdir)

uninstall-data-local:
	test -d $(pkgdatadir) && rm -rf $(pkgdatadir)
	test -d $(docdir) && rm -rf $(docdir)
	test -f $(sysconfdir)/sams2.conf && rm -f $(sysconfdir)/sams2.conf
