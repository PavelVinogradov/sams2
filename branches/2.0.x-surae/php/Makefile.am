# not a GNU package. You can remove this line, if
# have all needed files, that a GNU package needs
AUTOMAKE_OPTIONS = foreign 1.4

DISTCLEANFILES = Makefile.in
distdir = $(PACKAGE)

dist-hook:
	find $(distdir) -type d -name .svn | xargs rm -rf

install-data-local:
	$(mkinstalldirs) $(DESTDIR)/$(datadir)/$(distdir)
	for f in `find .` ; do \
		echo "$$f" | grep -w .svn > /dev/null && continue ;\
		echo "$$f" | grep Makefile > /dev/null && continue ;\
		test "$$f" = "." && continue ;\
		if [ -d "$$f" ] ; then \
			$(mkinstalldirs) $(DESTDIR)/$(datadir)/$(distdir)/"$$f" ;\
		else \
			mode=644 ;\
			echo "$$f" | grep -w bin > /dev/null && mode=755 ;\
			$(install_sh) -c -m "$$mode" "$$f" $(DESTDIR)/$(datadir)/$(distdir)/"$$f" ;\
		fi \
	done
	sed -i -e 's,__VERSION,$(VERSION),g' $(DESTDIR)/$(datadir)/$(distdir)/dbclass.php
	sed -i -e 's,/etc/sams2.conf,${sysconfdir}/sams2.conf,g' $(DESTDIR)/$(datadir)/$(distdir)/config.php
	chmod 0777 $(DESTDIR)/$(datadir)/$(distdir)/data

uninstall-local:
	rm -fr $(DESTDIR)/$(datadir)/$(distdir)
