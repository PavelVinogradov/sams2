AUTOMAKE_OPTIONS = foreign 1.4

build_docs = src/html

static_docs = \
	images \
	sams2.css \
	index.html \
	overview.html \
	requirements.html \
	install.html \
	setup.html \
	configure.html \
	license.html \
	faq.html \
	commands.html \
	notes.html \
	tables.html \
	sams2_conf.html \
	news.html \
	squid.html \
	synchronization.html \
	syncwithad.html \
	syncwithldap.html \
	templates.html \
	delaypools.html \
	timerange.html \
	webinterface.html \
	samsbackup.html \
	accesslists.html

docdir = $(datadir)/doc/$(PACKAGE)-$(VERSION)

if BUILD_SRC_DOCS
doc_list = $(static_docs) $(build_docs)
else
doc_list = $(static_docs)
endif

#doc_DATA = $(doc_list)

EXTRA_DIST = $(doc_list)

DISTCLEANFILES=Makefile.in

if BUILD_SRC_DOCS
DISTCLEANFILES += src/Doxyfile src/sams.tag
endif

if BUILD_SRC_DOCS
src/Doxyfile: src/Doxyfile.in
	sed -e 's|@INPUT@|$(abs_top_srcdir)/src|g' $(srcdir)/src/Doxyfile.in > src/Doxyfile

src/html: src/Doxyfile
	cd src && doxygen Doxyfile
endif

install-data-local: $(doc_list)
	$(mkinstalldirs) $(DESTDIR)/$(docdir)
	for f in $(doc_list) ; do \
		if [ -d "$$f" ] ; then \
			$(mkinstalldirs) $(DESTDIR)/$(docdir)/"$$f" ;\
			cp -R "$$f" $(DESTDIR)/$(docdir) ;\
		else \
			cp "$$f" $(DESTDIR)/$(docdir) ;\
		fi \
	done

uninstall-local:
	rm -fr $(DESTDIR)/$(docdir)

if BUILD_SRC_DOCS
distclean-am: clean-am distclean-generic
	rm -rf $(build_docs)
endif
