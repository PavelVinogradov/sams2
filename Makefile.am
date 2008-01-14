# not a GNU package. You can remove this line, if
# have all needed files, that a GNU package needs
AUTOMAKE_OPTIONS = foreign

SUBDIRS = src

EXTRA_DIST = php etc

dist-hook:
	find $(distdir) -type d -name .svn | xargs rm -rf

