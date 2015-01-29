# Introduction
This aims to be a complete solution to generate client side certificates from 
a self-signed CA using PHP software.

This software is currently tested with Mozilla Firefox and Chromium.

# Development

    $ cd /var/www
    $ sudo mkdir php-html5-keygen
    $ sudo chown fkooman.fkooman php-html5-keygen
    $ git clone https://github.com/fkooman/php-html5-keygen.git
    $ cd php-html5-keygen
    $ /path/to/composer.phar install
    $ mkdir -p data
    $ sudo chown -R apache.apache data
    $ sudo semanage fcontext -a -t httpd_sys_rw_content_t '/var/www/php-html5-keygen/data(/.*)?'
    $ sudo restorecon -R /var/www/php-html5-keygen/data
    $ cd config
    $ cp config.ini.default config.ini

# References
* [https://wiki.mozilla.org/CA:Certificate_Download_Specification](https://wiki.mozilla.org/CA:Certificate_Download_Specification)
* [https://developer.mozilla.org/en-US/docs/Web/HTML/Element/keygen](https://developer.mozilla.org/en-US/docs/Web/HTML/Element/keygen)

# License
Licensed under the GNU Affero General Public License as published by the Free
Software Foundation, either version 3 of the License, or (at your option) any
later version.

    https://www.gnu.org/licenses/agpl.html

This roughly means that if you use this software in your service you need to
make the source code available to the users of your service (if you modify
it). Refer to the license for the exact details.
