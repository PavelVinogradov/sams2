doc = html

html: Doxyfile
	test $(DOXYGEN) != "no" && $(DOXYGEN) Doxyfile

Doxyfile: Doxyfile.in
	sed -e 's|@INPUT@|$(abs_top_srcdir)/src|g' $(srcdir)/Doxyfile.in > Doxyfile

#install-data-am: html
#	$(mkinstalldirs) $(DESTDIR)/$(docdir)/src
#	test -d html && cp -R html $(DESTDIR)/$(docdir)/src

#uninstall-data:
#	test -d $(DESTDIR)/$(docdir)/src/html && rm -rf $(DESTDIR)/$(docdir)/src/html
