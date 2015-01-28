%global github_owner     fkooman
%global github_name      php-html5-keygen

Name:       php-html5-keygen
Version:    0.1.0
Release:    1%{?dist}
Summary:    Generate client certificates with a CA using PHP software.

Group:      Applications/Internet
License:    AGPLv3+
URL:        https://github.com/%{github_owner}/%{github_name}
Source0:    https://github.com/%{github_owner}/%{github_name}/archive/%{version}.tar.gz
Source1:    php-html5-keygen-httpd-conf
Source2:    php-html5-keygen-autoload.php

BuildArch:  noarch

Requires:   php >= 5.3.3
Requires:   php-openssl
Requires:   php-pdo
Requires:   httpd

Requires:   php-composer(fkooman/json) >= 0.6.0
Requires:   php-composer(fkooman/json) < 0.7.0
Requires:   php-composer(fkooman/ini) >= 0.2.0
Requires:   php-composer(fkooman/ini) < 0.3.0
Requires:   php-composer(fkooman/rest) >= 0.6.4
Requires:   php-composer(fkooman/rest) < 0.7.0
Requires:   php-pear(pear.twig-project.org/Twig) >= 1.15
Requires:   php-pear(pear.twig-project.org/Twig) < 2.0

#Starting F21 we can use the composer dependency for Symfony
#Requires:   php-composer(symfony/classloader) >= 2.3.9
#Requires:   php-composer(symfony/classloader) < 3.0
Requires:   php-pear(pear.symfony.com/ClassLoader) >= 2.3.9
Requires:   php-pear(pear.symfony.com/ClassLoader) < 3.0

Requires(post): policycoreutils-python
Requires(postun): policycoreutils-python

%description
This aims to be a complete solution to generate client side certificates from
a self-signed CA using PHP software.

%prep
%setup -qn %{github_name}-%{version}

sed -i "s|dirname(__DIR__)|'%{_datadir}/php-html5-keygen'|" bin/php-html5-keygen-init-ca

%build

%install
# Apache configuration
install -m 0644 -D -p %{SOURCE1} ${RPM_BUILD_ROOT}%{_sysconfdir}/httpd/conf.d/php-html5-keygen.conf

# Application
mkdir -p ${RPM_BUILD_ROOT}%{_datadir}/php-html5-keygen
cp -pr web views src ${RPM_BUILD_ROOT}%{_datadir}/php-html5-keygen

# use our own class loader
mkdir -p ${RPM_BUILD_ROOT}%{_datadir}/php-html5-keygen/vendor
cp -pr %{SOURCE2} ${RPM_BUILD_ROOT}%{_datadir}/php-html5-keygen/vendor/autoload.php

mkdir -p ${RPM_BUILD_ROOT}%{_bindir}
cp -pr bin/* ${RPM_BUILD_ROOT}%{_bindir}

# Config
mkdir -p ${RPM_BUILD_ROOT}%{_sysconfdir}/php-html5-keygen
cp -p config/config.ini.defaults ${RPM_BUILD_ROOT}%{_sysconfdir}/php-html5-keygen/config.ini
ln -s ../../../etc/php-html5-keygen ${RPM_BUILD_ROOT}%{_datadir}/php-html5-keygen/config

# Data
mkdir -p ${RPM_BUILD_ROOT}%{_localstatedir}/lib/php-html5-keygen

%post
semanage fcontext -a -t httpd_sys_rw_content_t '%{_localstatedir}/lib/php-html5-keygen(/.*)?' 2>/dev/null || :
restorecon -R %{_localstatedir}/lib/php-html5-keygen || :

%postun
if [ $1 -eq 0 ] ; then  # final removal
semanage fcontext -d -t httpd_sys_rw_content_t '%{_localstatedir}/lib/php-html5-keygen(/.*)?' 2>/dev/null || :
fi

%files
%defattr(-,root,root,-)
%config(noreplace) %{_sysconfdir}/httpd/conf.d/php-html5-keygen.conf
%config(noreplace) %{_sysconfdir}/php-html5-keygen
%{_bindir}/php-html5-keygen-init-ca
%dir %{_datadir}/php-html5-keygen
%{_datadir}/php-html5-keygen/src
%{_datadir}/php-html5-keygen/vendor
%{_datadir}/php-html5-keygen/web
%{_datadir}/php-html5-keygen/views
%{_datadir}/php-html5-keygen/config
%dir %attr(0700,apache,apache) %{_localstatedir}/lib/php-html5-keygen
%doc README.md agpl-3.0.txt composer.json config/

%changelog
* Wed Jan 28 2015 François Kooman <fkooman@tuxed.net> - 0.1.0-1
- initial package
