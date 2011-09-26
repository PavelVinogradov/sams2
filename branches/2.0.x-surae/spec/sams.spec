#%define _prefix /usr
%define debug_packages  %{nil}


Name:          sams2
Version:       2.0.0
Release:       a2
Epoch:         569
Summary:       SAMS2 (Squid Account Management System)
Group:         Applications/Internet
License:       GPL
Source:        %{name}-%{version}-%{epoch}.tar.gz
URL:           http://sams.perm.ru
Packager:      SAMS Development Group
Requires:      mysql, pcre, squid, samba
Provides:      sams2 = %{version}
Prefix:        %{_prefix}
BuildRoot:     %{_tmppath}/%{name}-buildroot
BuildRequires: mysql-devel, postgresql-devel, unixODBC-devel, gcc-c++, pcre-devel, samba

%description
This program basically used for administrative purposes of squid proxy.
There are access control for users by ntlm, ldap, ncsa, basic or ip
authorization mode.

#######################################################################
%package web
Summary:       SAMS2 web administration tool
Group:         Applications/System
Requires:      httpd, php, php-mysql, php-gd, php-ldap, samba
Provides:      sams2-web = %{version}
BuildRequires: samba

%description web
The sams2-web package provides web administration tool
for remotely managing sams2 using your favorite
Web browser.

#######################################################################
%package doc
Summary:       SAMS2 Documentation
Group:         Documentation/Other
Provides:      sams2-doc = %{version}
Prereq:        /usr/bin/find /bin/rm /usr/bin/xargs

%description doc
The sams2-doc package includes the HTML versions of the "Using SAMS2".


#######################################################################


%prep
%setup -q -n %{name}-%{version}-%{epoch}

%build
make -f Makefile.cvs
%configure \
	--prefix=%{_prefix} \
	--with-configfile=%{_sysconfdir}/sams2.conf 
make

%install
# Clean up in case there is trash left from a previous build
[ "${RPM_BUILD_ROOT}" != "/" ] && [ -d "${RPM_BUILD_ROOT}" ] && \
	rm -rf "${RPM_BUILD_ROOT}"

make DESTDIR=$RPM_BUILD_ROOT install

install -d "${RPM_BUILD_ROOT}%{_initrddir}"
install -d "${RPM_BUILD_ROOT}%{_sysconfdir}"/httpd/conf.d

install -m755 redhat/init.d 					\
		"${RPM_BUILD_ROOT}%{_initrddir}"/sams2
sed -i -e 's,__PREFIX,%{_prefix}/bin,g'				\
		-e 's,__CONFDIR,%{_sysconfdir},g'		\
		"${RPM_BUILD_ROOT}%{_initrddir}"/sams2
install -m644 redhat/httpd_conf					\
		"${RPM_BUILD_ROOT}%{_sysconfdir}"/httpd/conf.d/sams2.conf
sed -i -e 's,__WEBPREFIX,%{_datadir}/%{name}-%{version},g'	\
		"${RPM_BUILD_ROOT}%{_sysconfdir}"/httpd/conf.d/sams2.conf
install -d "${RPM_BUILD_ROOT}%{_docdir}/%{name}-%{version}"
install -m644 ChangeLog AUTHORS COPYING NEWS INSTALL "${RPM_BUILD_ROOT}%{_docdir}/%{name}-%{version}"

%clean
[ "${RPM_BUILD_ROOT}" != "/" ] && [ -d "${RPM_BUILD_ROOT}" ] && \
	rm -rf "${RPM_BUILD_ROOT}"

%post
/sbin/chkconfig --add sams2

%post web
%{_initrddir}/httpd reload

%preun
if [ $1 = 0 ] ; then
    /sbin/service sams2 stop >/dev/null 2>&1
    /sbin/chkconfig --del sams2
fi

#######################################################################
## Files section                                                     ##
#######################################################################
%files
%defattr(-,root,root,-)
%{_prefix}/bin/samsparser
%{_prefix}/bin/samsdaemon
%{_prefix}/bin/samsredir
%{_prefix}/bin/sams_send_email
%{_initrddir}/sams2
%attr(640,apache,apache) %config(noreplace) %{_sysconfdir}/sams2.conf
%{_libdir}/sams2/*

##########
%files doc
%defattr(-,root,root)
%doc %{_docdir}/%{name}-%{version}

##########
%files web
%defattr(-,apache,apache)
%attr(640,apache,apache) %config(noreplace) %{_sysconfdir}/sams2.conf
%config(noreplace) %{_sysconfdir}/httpd/conf.d/sams2.conf
%{_datadir}/%{name}-%{version}
