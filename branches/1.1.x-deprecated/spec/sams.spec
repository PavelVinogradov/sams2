%define debug_packages %{nil}
# to build sams with credit patch, just define samsrelease as "credit"
%define samsrelease %{nil}
#define samsrelease credit

%define dist redhat
%define disttag .el5
%define mysqllib64path /usr/lib64/mysql/

%define webuser apache
%define webgroup apache
%define squidgroup squid
%define apacheconf /httpd/conf.d
%define is_suse     %(echo %{_target_platform}| grep -qi suse && echo 1 || echo 0)
%define is_Mandriva_2008 %(grep -qi  "mandriva.*2008" /etc/mandriva-release &>/dev/null && echo 1 || echo 0)
%define is_Mandriva_2009 %(grep -qi  "mandriva.*2009" /etc/mandriva-release &>/dev/null && echo 1 || echo 0)
%define is_Fedora %(rpm -q filesystem |grep -qiE "fc[0-9]" &>/dev/null && echo 1 || echo 0)
%define is_CentOS %(rpm -q filesystem |grep -qi "centos"&>/dev/null && echo 1 || echo 0)
#define is_RHEL %(grep -qi  "^red hat" /etc/redhat-release &>/dev/null && echo 1 || echo 0)

%if %{is_Mandriva_2008}
%define dist mandriva8
%define disttag .mdv2008
%define mysqllib64path /usr/lib64/
%endif
%if %{is_Mandriva_2009}
%define dist mandriva9
%define disttag .mdv2009
%define mysqllib64path /usr/lib64/
%endif
%if %{is_suse}
%define dist suse
%define disttag .suse
%define webuser wwwrun
%define webgroup www
%define squidgroup nogroup
%define apacheconf /apache2/conf.d
%endif
%if %{is_Fedora}
%define disttag .%(rpm -q filesystem |egrep -oi "fc[0-9]+")
%define enable_debug_packages %{nil}
%endif
%if %{is_CentOS}
%define disttag .centos
%endif
###########################################################################

Summary: SAMS (Squid Account Management System)
%if "%{samsrelease}" == "credit"
Name: sams-credit
%else
Name: sams
%endif
Version: 1.1.0
Epoch:		742
Release: %{epoch}%{disttag}
Group: Applications/Internet
License: GPL
Source: http://nixdev.net/release/sams/sams-%{version}.tar.bz2
#Source: http://nixdev.net/release/sams/sams-%{version}-%{epoch}.tar.bz2
Patch0: sams-1.1.0.rpm.patch
Patch1: credit-0.1-sams-1.1.0.patch
Vendor: Sams community
Packager: SAMS Development Group
URL: http://sams.perm.ru

%if %{dist} == "suse"
Requires:      mysql, pcre, squid
BuildRequires: mysql-devel, pcre-devel, autoconf, automake, libtool
%endif
%if %{dist} == "redhat"
Requires:      mysql-server, pcre, squid
BuildRequires: mysql-devel, pcre-devel, autoconf, automake, libtool
%endif
%if %dist == "mandriva9"
Requires:      mysql-common, libpcre, squid
BuildRequires: mysql-devel, libpcre-devel, autoconf, automake, libtool
%endif
%if %dist == "mandriva8"
Requires:      mysql-common, libpcre, squid
BuildRequires: mysql-devel, libpcre-devel, autoconf, automake, libtool
%endif
PreReq: coreutils grep
BuildRoot: %{_tmppath}/%{name}-buildroot

%description
This program basically used for administrative purposes of squid proxy.
There are access control for users by ntlm, ldap, ncsa, basic or ip
authorization mode.

#######################################################################
%package web
Summary:       SAMS web administration tool
Group:         Applications/System
%if %{dist} == "mandriva9"
Requires:      apache-base, php, php-mysql, php-gd, php-ldap, php-zlib, squid,  /usr/bin/wbinfo
%endif
%if %{dist} == "mandriva8"
Requires:      apache-base, php, php-mysql, php-gd, php-ldap, php-zlib, squid,  /usr/bin/wbinfo
%endif
%if %{dist} == "suse"
Requires: apache2, apache2-mod_php5, php5, php5-mysql, php5-gd, php5-ldap, php5-zlib, squid, /usr/bin/wbinfo
%endif
%if %{dist} == "redhat"
Requires: httpd, php, php-mysql, php-gd, php-ldap, php-zlib, squid,  /usr/bin/wbinfo
%endif

%description web
The sams-web package provides web administration tool
for remotely managing sams using your favorite
Web browser.

#######################################################################
%package doc
Summary:       SAMS Documentation
Group:         Documentation/Other
Prereq:        /usr/bin/find /bin/rm /usr/bin/xargs

%description doc
The sams-doc package includes the HTML versions of the "Using SAMS".

#######################################################################

%prep
echo Building for %{dist}
#%setup -q -n sams-%{version}-%{epoch}
%setup -q -n sams-%{version}
%patch0 -p1
%if "%{samsrelease}" == "credit"
%patch1 -p0
%endif
############################################################################
%build
%configure \
    --prefix=%{_prefix} \
    %ifarch x86_64
	--with-pcre-libpath=/usr/lib64/ \
        --with-mysql-libpath=%{mysqllib64path} \
    %endif
    --with-configfile=%{_sysconfdir}/sams.conf \
    --with-rcd-locations=%{_initrddir} \
    --with-httpd-locations=%{_var}/www/html
                                                            
make
###############################################################################
%install

mkdir -p $RPM_BUILD_ROOT%{_bindir}
mkdir -p $RPM_BUILD_ROOT%{_initrddir}
mkdir -p $RPM_BUILD_ROOT%{_sysconfdir}/%{apacheconf}
#mkdir -p $RPM_BUILD_ROOT%{_var}/www/html

%makeinstall \
    RCDPATH=$RPM_BUILD_ROOT%{_initrddir} \
    HTTPDPATH=$RPM_BUILD_ROOT%{_var}/www/html
#Remove symlink /var/www/html/sams. We shall create symlink it in post stage
rm -f $RPM_BUILD_ROOT%{_var}/www/html/sams
#Remove duplicate files in /usr/share/sams/doc. We shall symlink it in post stage
rm -fr $RPM_BUILD_ROOT%{_datadir}/sams/doc
#httpd/conf.d alias for location /sams/doc files
install -m 644 debian/etc/apache.sams.conf $RPM_BUILD_ROOT%{_sysconfdir}/%{apacheconf}/sams.conf

#sams-credit addon
%if "%{samsrelease}" == "credit"
    install -m 644 contribs/credit/credit_alarm.gif $RPM_BUILD_ROOT%{_datadir}/sams/icon/classic/credit_alarm.gif
    install -m 644 contribs/credit/credit_alarm.gif $RPM_BUILD_ROOT%{_datadir}/sams/icon/bumper/credit_alarm.gif
    install -m 644 contribs/credit/credit-change.sql $RPM_BUILD_ROOT%{_datadir}/sams/data/credit-change.sql
%endif
#############################################################################
install -m644 etc/doc_sams_conf			\
    "${RPM_BUILD_ROOT}%{_sysconfdir}"%{apacheconf}/doc4sams.conf
#if suse just remove redhat init script and replace it by lsb script
%if %{dist} == "suse"
    rm -f $RPM_BUILD_ROOT%{_initrddir}/sams
    install -m 755 etc/sams.suse \
    $RPM_BUILD_ROOT%{_initrddir}/sams
    sed -i -e 's,__PREFIX,%{_prefix}/bin,g' \
	    -e 's,__CONFDIR,%{_sysconfdir},g' \
	    "$RPM_BUILD_ROOT%{_initrddir}"/sams
    sed -i -e 's,/usr/bin/php,/usr/bin/php5,g' \
	    "$RPM_BUILD_ROOT%{_datadir}"/sams/data/upgrade_mysql_table.php
    sed -i -e 's,__DOCPREFIX,%{_docdir}/%{name}-doc,g'	\
	    "${RPM_BUILD_ROOT}%{_sysconfdir}"%{apacheconf}/doc4sams.conf
%else
    %define samsdoc %{name}-doc-%{version}
    %if %{dist} == "mandriva8"
	%define samsdoc %{name}-doc
    %endif
    %if %{dist} == "mandriva9"
	%define samsdoc %{name}-doc
    %endif
    sed -i -e 's,__DOCPREFIX,%{_docdir}/%{samsdoc},g'	\
	    "${RPM_BUILD_ROOT}%{_sysconfdir}"%{apacheconf}/doc4sams.conf
%endif

%clean
rm -rf $RPM_BUILD_ROOT        

#######################################################################
%files
%defattr(-,root,root,-)
%{_bindir}/*
%{_initrddir}/sams
%attr(640,%{webuser},%{squidgroup}) %config(noreplace) %{_sysconfdir}/sams.conf

##########
%files doc
%defattr(-,root,root)
%attr(644,root,root) %config(noreplace) %{_sysconfdir}%{apacheconf}/doc4sams.conf
%doc CHANGELOG INSTALL* README*
%doc doc/img
%lang(en) %doc doc/EN
%lang(ru) %doc doc/RU

##########
%files web
%defattr(-,%{webuser},%{webgroup})
%attr(640,%{webuser},%{squidgroup}) %config(noreplace) %{_sysconfdir}/sams.conf
%attr(644,root,root) %config(noreplace) %{_sysconfdir}%{apacheconf}/sams.conf
%attr(775,root,%{webgroup}) %dir %{_datadir}/sams/data
%{_datadir}/sams


%pre  
if [ "$1" = 2 ] ; then  
    /sbin/service samsd stop || /sbin/service sams stop  
fi  
exit 0  
  
%post  
if [ "$1" = 1 ] ; then  
#   workaround for theose who still uses obsolete apache-1
    rpm -q apache 2>&1 >/dev/null && ln -s %{_datadir}/sams %{_var}/www/html/
    /sbin/chkconfig --add sams  
    /sbin/chkconfig sams on  
fi  
  
if [ "$1" = 2 ] ; then  
    echo Warning: you are upgrading existing sams package.  
    echo Please run %{_datadir}/sams/data/upgrade_mysql_table.php manually.  
    echo This is strongly recommended to ensure your sams tables is up to date.  
fi  
exit 0

%post web
%if %{dist}=="suse"
%{_initrddir}/apache2 reload
%else
%{_initrddir}/httpd reload
%endif
exit 0

%post doc
%if %{dist}=="suse"
%{_initrddir}/apache2 reload
%else
%{_initrddir}/httpd reload
%endif
exit 0

%preun  
%if %{dist} == "suse"
    %stop_on_removal sams
%else
    if [ $1 = 0 ] ; then
	/sbin/service sams stop >/dev/null 2>&1
	/sbin/chkconfig --del sams
    fi
%endif
exit 0  
  
%postun  

%if %{dist} == "suse"
%insserv_cleanup sams
%endif

exit 0

%postun doc
%if %{dist}=="suse"
%{_initrddir}/apache2 reload
%else
%{_initrddir}/httpd reload
%endif
exit 0

%postun web
%if %{dist}=="suse"
%{_initrddir}/apache2 reload
%else
%{_initrddir}/httpd reload
%endif
exit 0

%changelog
* Mon Dec 28 2009 Denis Zagirov <foomail@yandex.ru> 1.1.0
Added compatibility with sams2.spec
Package split into three packages: sams sams-web sams-doc
Lot of build bugs fixed
New upstream version 1.1.0

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