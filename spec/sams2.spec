%define debug_packages %{nil}

%define dist redhat
%define disttag .el5

%define webuser apache
%define webgroup apache
%define apacheconf /httpd/conf.d
%define is_suse     %(echo %{_target_platform}| grep -qi suse && echo 1 || echo 0)
%define is_Mandriva_2008 %(grep -qi  "mandriva.*2008" /etc/mandriva-release &>/dev/null&& echo 1 || echo 0)
%define is_Mandriva_2009 %(grep -qi  "mandriva.*2009" /etc/mandriva-release &>/dev/null && echo 1 || echo 0)
%define is_Fedora %(rpm -q filesystem |grep -qi  ".fc9" &>/dev/null && echo 1 || echo 0)
%define is_CentOS %(rpm -q filesystem |grep -qi "centos"&>/dev/null && echo 1 || echo 0)
#define is_RHEL %(grep -qi  "^red hat" /etc/redhat-release &>/dev/null && echo 1 || echo 0)

%if %is_Mandriva_2008
%define dist mandriva8
%define disttag .mdv2008
%endif
%if %is_Mandriva_2009
%define dist mandriva9
%define disttag .mdv2009
%endif
%if %is_suse
%define dist suse
%define disttag .suse
%define webuser wwwrun
%define webgroup www
%define apacheconf /apache2/conf.d
%endif
%if %is_Fedora
%define disttag .fc9
%define enable_debug_packages %{nil}
%endif
%if %is_CentOS
%define disttag .centos
%endif


Name:          sams2
Version:       2.0.0
Epoch:         618
Release:       a2.%{epoch}%{disttag}
Summary:       SAMS2 (Squid Account Management System)
Group:         Applications/Internet
License:       GPL
Source:        %{name}-%{version}-%{epoch}.tar.bz2
#Source1:       doc_sams2_conf
URL:           http://sams.perm.ru
Packager:      SAMS Development Group
BuildRoot:     %{_tmppath}/%{name}-buildroot

%if %{dist} == "suse"
Requires:      mysql, postgresql-server, unixODBC, pcre, squid
BuildRequires: mysql-devel, postgresql-devel, unixODBC-devel, gcc-c++, pcre-devel, autoconf, automake, libtool
%endif
%if %{dist} == "redhat"
Requires:      mysql-server, postgresql-server, unixODBC, pcre, squid
BuildRequires: mysql-devel, postgresql-devel, unixODBC-devel, gcc-c++, pcre-devel, autoconf, automake, libtool
%endif
%if %dist == "mandriva9"
Requires:      mysql-common, libpcre, squid
BuildRequires: mysql-devel, gcc-c++, libpcre-devel, autoconf, automake, libtool
%endif
%if %dist == "mandriva8"
Requires:      mysql-common, postgresql8.2-server, libunixODBC1, libpcre, squid
BuildRequires: mysql-devel, postgresql8.2-devel, unixODBC-devel, gcc-c++, libpcre-devel, autoconf, automake, libtool
%endif


%description
This program basically used for administrative purposes of squid proxy.
There are access control for users by ntlm, ldap, ncsa, basic or ip
authorization mode.

#######################################################################
%package web
Summary:       SAMS2 web administration tool
Group:         Applications/System
%if %{dist} == "mandriva9"
Requires:      apache-base, php, php-mysql, php-gd, php-ldap, php-zlib, squid,  /usr/bin/wbinfo
%endif
%if %{dist} == "mandriva8"
Requires:      apache-base, php, php-mysql, php-gd, php-ldap, php-pgsql, php-odbc, php-zlib, squid,  /usr/bin/wbinfo
%endif
%if %{dist} == "suse"
Requires: apache2, apache2-mod_php5, php, php-mysql, php-gd, php-ldap, php-pgsql, php-odbc, php-zlib, squid, /usr/bin/wbinfo
%endif
%if %{dist} == "redhat"
Requires: httpd, php, php-mysql, php-gd, php-ldap, php-pgsql, php-odbc,php-zlib, squid,  /usr/bin/wbinfo
%endif

%description web
The sams2-web package provides web administration tool
for remotely managing sams2 using your favorite
Web browser.

#######################################################################
%package doc
Summary:       SAMS2 Documentation
Group:         Documentation/Other
Prereq:        /usr/bin/find /bin/rm /usr/bin/xargs

%description doc
The sams2-doc package includes the HTML versions of the "Using SAMS2".

#######################################################################


%prep
%setup -q -n %{name}-%{version}-%{epoch}

%build
make -f Makefile.cvs
%configure

make
	
%install
# Clean up in case there is trash left from a previous build
[ "${RPM_BUILD_ROOT}" != "/" ] && [ -d "${RPM_BUILD_ROOT}" ] && \
    rm -rf "${RPM_BUILD_ROOT}"
mkdir %{buildroot}
	    
make DESTDIR=$RPM_BUILD_ROOT install

install -d "${RPM_BUILD_ROOT}%{_initrddir}"
install -d "${RPM_BUILD_ROOT}%{_sysconfdir}"%{apacheconf}

install -m755 redhat/init.d 					\
    "${RPM_BUILD_ROOT}%{_initrddir}"/sams2
	sed -i -e 's,__PREFIX,%{_prefix}/bin,g'				\
	    -e 's,__CONFDIR,%{_sysconfdir},g'		\
	    "${RPM_BUILD_ROOT}%{_initrddir}"/sams2

install -m644 "etc/httpd_conf"				\
    "${RPM_BUILD_ROOT}%{_sysconfdir}"%{apacheconf}/sams2.conf
sed -i -e 's,__WEBPREFIX,%{_datadir}/%{name},g'	\
    "${RPM_BUILD_ROOT}%{_sysconfdir}"%{apacheconf}/sams2.conf
install -m644 etc/doc_sams2_conf			\
    "${RPM_BUILD_ROOT}%{_sysconfdir}"%{apacheconf}/doc4sams2.conf
sed -i -e 's,__DOCPREFIX,%{_docdir}/%{name}-%{version},g'	\
	    "${RPM_BUILD_ROOT}%{_sysconfdir}"%{apacheconf}/doc4sams2.conf

sed -i -e 's,^SQUIDCACHEDIR=.*$,SQUIDCACHEDIR=/var/spool/squid,g'	\
	    "${RPM_BUILD_ROOT}%{_sysconfdir}"/sams2.conf
sed -i -e 's,^SAMSPATH=.*$,SAMSPATH=/usr,g'	\
	    "${RPM_BUILD_ROOT}%{_sysconfdir}"/sams2.conf

install -d "${RPM_BUILD_ROOT}%{_docdir}/%{name}-%{version}"
install -m644 ChangeLog AUTHORS COPYING NEWS INSTALL "${RPM_BUILD_ROOT}%{_docdir}/%{name}-%{version}"
%if %{dist}=="suse"
mv -f --target-directory="${RPM_BUILD_ROOT}"%{_docdir}/%{name}-%{version}		\
    "${RPM_BUILD_ROOT}"%{_datadir}/doc/%{name}-%{version}/*
%endif
								    
%clean
[ "${RPM_BUILD_ROOT}" != "/" ] && [ -d "${RPM_BUILD_ROOT}" ] && \
rm -rf "${RPM_BUILD_ROOT}"

%post
/sbin/chkconfig --add sams2

%post web
%if %{dist}=="suse"
%{_initrddir}/apache2 reload
%else
%{_initrddir}/httpd reload
%endif

%post doc
%if %{dist}=="suse"
%{_initrddir}/apache2 reload
%else
%{_initrddir}/httpd reload
%endif

%preun
%if %{dist} == "suse"
%stop_on_removal /etc/initd/sams2
%else
    if [ $1 = 0 ] ; then
/	sbin/service sams2 stop >/dev/null 2>&1
	/sbin/chkconfig --del sams2
    fi
%endif

%postun
%if %{dist} == "suse"
%insserv_cleanup /etc/initd/sams2
%endif

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
%attr(640,%{webuser},%{webgroup}) %config(noreplace) %{_sysconfdir}/sams2.conf
%{_libdir}/sams2/*

##########
%files doc
%defattr(-,root,root)
%attr(640,%{webuser},%{webgroup}) %config(noreplace) %{_sysconfdir}%{apacheconf}/doc4sams2.conf
%doc %{_docdir}/%{name}-%{version}

##########
%files web
%defattr(-,%{webuser},%{webgroup})
%attr(640,%{webuser},%{webgroup}) %config(noreplace) %{_sysconfdir}/sams2.conf
%config(noreplace) %{_sysconfdir}%{apacheconf}/sams2.conf
%{_datadir}/%{name}

%changelog
