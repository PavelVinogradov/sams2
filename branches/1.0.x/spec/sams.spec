%define samsrelease %{nil}
#define samsrelease credit
%define mysqllib64path /usr/lib64/mysql
%define is_Mandriva %(grep -qi  "mandriva" /etc/mandriva-release &>/dev/null && echo 1 || echo 0)

%if %{is_Mandriva}
%define mysqllib64path /usr/lib64
%endif

Summary: SAMS (Squid Account Management System)
%if "%{samsrelease}" == "credit"
Name: sams-credit
%else
Name: sams
%endif
Version: 1.0.5
Release: 0
Group: Applications/Internet
License: GPL
Source: http://nixdev.net/release/sams/sams-%{version}.tar.bz2
#Source1: credit_alarm.gif
#Source2: change.sql
Patch0: sams-1.0.5.rpm.patch
%if "%{samsrelease}" == "credit"
Patch1: credit-0.1-sams-1.0.5.patch
%endif
Distribution: Red Hat Linux
Vendor: Sams community
Packager: Pavel Vinogradov <Pavel.Vinogradov@nixdev.net>
URL: http://sams.perm.ru
BuildRoot: %{_tmppath}/%{name}-buildroot
BuildRequires: mysql-devel, pcre-devel
Requires: mysql >= 3.23, mysql-server, php >= 4.3.2, php-gd, php-mysql, php-ldap, httpd, squid, pcre
PreReq: coreutils

%description
This program basically used for administrative purposes of squid proxy.
There are access control for users by ntlm, ncsa, basic or ip
authorization mode.

%description -l ru
Этот пакет используется для администрирования прокси-сервера Squid.
С установкой этого пакета доступны возможности по управлению трафиком
пользователей и авторизацией по схемам ntlm, ncsa, basic or ip.

%prep
%setup -q -n sams-%{version}
%patch0 -p1
%if "%{samsrelease}" == "credit"
%patch1 -p0
%endif
%build
%configure \
	--prefix=%{_prefix} \
        %ifarch x86_64
        --with-mysql-libpath=%{mysqllib64path} \
        --with-pcre-libpath=/usr/lib64/ \
        %endif #x86_64 
	--with-configfile=%{_sysconfdir}/sams.conf \
	--with-rcd-locations=%{_sysconfdir}/rc.d/init.d \
	--with-httpd-locations=%{_var}/www/html

make

%install
#rm -rf $RPM_BUILD_ROOT

mkdir -p $RPM_BUILD_ROOT%{_bindir}
mkdir -p $RPM_BUILD_ROOT%{_sysconfdir}
mkdir -p $RPM_BUILD_ROOT%{_sysconfdir}/rc.d/init.d
mkdir -p $RPM_BUILD_ROOT%{_var}/www/html

%makeinstall \
	RCDPATH=$RPM_BUILD_ROOT%{_sysconfdir}/rc.d/init.d \
	HTTPDPATH=$RPM_BUILD_ROOT%{_var}/www/html
rm -f $RPM_BUILD_ROOT%{_var}/www/html/sams
#php5 dependency resolving (need for suse linux)
test -x /usr/bin/php5 &&  sed -i -e 's,/usr/bin/php,/usr/bin/php5,g' \
	    "$RPM_BUILD_ROOT%{_datadir}"/sams/data/upgrade_mysql_table.php
#sams-credit addon
%if "%{samsrelease}" == "credit"
    install -m 644 contribs/credit/credit_alarm.gif $RPM_BUILD_ROOT%{_datadir}/sams/icon/classic/credit_alarm.gif
    install -m 644 contribs/credit/credit_alarm.gif $RPM_BUILD_ROOT%{_datadir}/sams/icon/bumper/credit_alarm.gif
    install -m 644 contribs/credit/credit-change.sql $RPM_BUILD_ROOT%{_datadir}/sams/data/credit-change.sql
%endif

%clean
rm -rf $RPM_BUILD_ROOT

%files
%defattr(-,root,root,-)
%doc CHANGELOG INSTALL* README*
%doc doc/img
%lang(en) %doc doc/EN
%lang(ru) %doc doc/RU
%config(noreplace) %{_sysconfdir}/sams.conf
%{_bindir}/*
%{_datadir}/sams
%attr(775,root,apache) %dir %{_datadir}/sams/data
%{_sysconfdir}/rc.d/init.d/sams

%pre
if [ "$1" = 2 ] ; then
    /sbin/service samsd stop || /sbin/service sams stop
fi
exit 0

%post
if [ "$1" = 1 ] ; then
ln -s %{_datadir}/sams %{_var}/www/html/
/sbin/chkconfig --add sams
/sbin/chkconfig --level 345 sams on
fi

if [ "$1" = 2 ] ; then
    echo Warning: you are upgrading existing sams package.
    echo Please run %{_datadir}/sams/data/upgrade_mysql_table.php manually.
    echo This is strongly recommended to ensure your sams tables is up to date.
fi
exit 0

%preun
if [ "$1" = 0 ] ; then 
/sbin/service sams stop
/sbin/chkconfig --del sams
fi
exit 0

%postun
rm -f %{_var}/www/html/sams
exit 0

%changelog
* Mon Dec 28 2009 Denis Zagirov <foomail@yandex.ru> 1.0.5
New upstream version 1.0.5

* Fri Nov 14 2008 Denis Zagirov <foomail@yandex.ru> 1.0.4
[Denis Zagirov]
 Credit patch by cj_nik for version 1.0.4 added.
 Credit patch filename changed according to sams version.
 Packages added to Requires section: mysql-server. php-ldap, php-gd , php-mysql

[Pavel Vinogradov]
 Small build fixed and grammar corection
 New upstream version 1.0.4

* Sat Nov 8 2008 Denis Zagirov <foomail@yandex.ru> 1.0.3
Credit patch  by cj_nik added.

* Fri Oct 31 2008 Denis Zagirov <foomail@yandex.ru>
Path to /usr/lib64 added for x86_64 added to configure stage.

* Thu Oct 30 2008 Denis Zagirov <foomail@yandex.ru>
Stages added: pre post preun postun 
service name changed to 'sams'
Dealing with /var/www/html/sams moved to post and postun section
Added pcre-devel in to Buildrequire section
Added mysql tables upgrade warning

* Fri Jul 25 2008 Denis Zagirov <foomail@yandex.ru>
Fixed simlink to %%{_datadir}/sams
Workaround for moving old sams.conf, ensure not to move sams.conf during rpm
build on host with working sams. (Makefile.am lines 200-203 out)

* Thu Jun 16 2005 Dmitry Chemerik <chemerik@mail.ru>
New version
