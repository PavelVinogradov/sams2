%define _prefix /usr/local

Name:          sams2
Version:       2.0.0
Release:       a1
Summary:       SAMS2 (Squid Account Management System)
Group:         Applications/Internet
License:       GPL
Source:        %{name}-%{version}.tar.gz
URL:           http://sams.perm.ru
Vendor:        SAMS Development group
Packager:      SAMS Development Group
Requires:      mysql
Requires:      squid

Provides:      sams2 = %{version}

Prefix:        %{_prefix}
BuildRoot:     %{_tmppath}/%{name}-buildroot
BuildRequires: mysql-devel
BuildRequires: postgresql-devel
BuildRequires: unixODBC-devel

%description
This program basically used for administrative purposes of squid proxy.
There are access control for users by ntlm, ldap, ncsa, basic or ip
authorization mode.

#######################################################################
%package web
Summary:       SAMS2 web administration tool
Group:         Applications/System
Requires:      httpd
Provides:      sams2-web = %{version}
#BuildArch:     noarch

%description web
The sams2-web package provides web administration tool
for remotely managing sams2 using your favorite
Web browser.

#######################################################################
%package doc
Summary:       SAMS2 Documentation
Group:         Documentation/Other
Provides:      sams2-doc = %{version}
#BuildArch:     noarch
BuildRequires: doxygen
Prereq:        /usr/bin/find /bin/rm /usr/bin/xargs

%description doc
The sams2-doc package includes the HTML versions of the "Using SAMS2".


#######################################################################


%prep
%setup -q -n %{name}-%{version}

%build
./configure \
	--prefix=%{_prefix}

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
		"${RPM_BUILD_ROOT}%{_initrddir}"/sams2
install -m644 redhat/httpd_conf					\
		"${RPM_BUILD_ROOT}%{_sysconfdir}"/httpd/conf.d/sams2.conf
sed -i -e 's,__WEBPREFIX,%{_datadir}/%{name}-%{version},g'	\
		"${RPM_BUILD_ROOT}%{_sysconfdir}"/httpd/conf.d/sams2.conf
sed -i -e 's,sams2.conf,%{_prefix}/etc/sams2.conf,g'		\
		"${RPM_BUILD_ROOT}%{_datadir}"/"%{name}"-"%{version}"/config.php

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
%{_initrddir}/sams2
%attr(640,apache,apache) %config(noreplace) %{_prefix}/etc/sams2.conf

##########
%files doc
%defattr(-,root,root)
%doc ChangeLog INSTALL* README*
%doc doc/*

##########
%files web
%defattr(-,apache,apache)
%attr(640,apache,apache) %config(noreplace) %{_prefix}/etc/sams2.conf
%config(noreplace) %{_sysconfdir}/httpd/conf.d/sams2.conf
%{_datadir}/%{name}-%{version}
