<link rel='stylesheet' href='digital_ocean.css'><br> <br> 
      <h3 id="introduction">Introduction</h3>

<p>PHP 7, which was released on December 3, 2015, promises substantial speed improvements over previous versions of the language, along with new features like scalar type hinting.  This guide explains how to quickly upgrade an Apache or Nginx web server running PHP 5.x (any release) to PHP 7, using community-provided packages.</p>

<p><span class="warning"><strong>Warning:</strong> As with most major-version language releases, it's best to wait a little while before switching to PHP 7 in production.  In the meanwhile, it's a good time to test your applications for compatibility with the new release, perform benchmarks, and familiarize yourself with new language features.<br /></span></p>

<p>If you have installed phpMyAdmin for database management, it is strongly recommended that you wait for official CentOS PHP 7 packages before upgrading, as phpMyAdmin packages do not yet support the upgrade.  If you're running any other services or applications with active users, it is safest to first test this process in a staging environment.</p>

<h2 id="prerequisites">Prerequisites</h2>

<p>This guide assumes that you are running PHP 5.x on CentOS 7, using either <code>mod_php</code> in conjunction with Apache, or PHP-FPM in conjunction with Nginx.  It also assumes that you have a non-root user configured with <code>sudo</code> privileges for administrative tasks.</p>

<p>The PHP 5 installation process is documented in these guides:</p>

<ul>
<li><a href="https://indiareads/community/tutorials/how-to-install-linux-apache-mysql-php-lamp-stack-on-centos-7">How To Install Linux, Apache, MySQL, PHP (LAMP) stack On CentOS 7</a></li>
<li><a href="https://indiareads/community/tutorials/how-to-install-linux-nginx-mysql-php-lemp-stack-on-centos-7">How To Install Linux, Nginx, MySQL, PHP (LEMP) stack On CentOS 7</a></li>
</ul>

<h2 id="subscribing-to-the-ius-community-project-repository">Subscribing to the IUS Community Project Repository</h2>

<p>Since PHP 7.x is not yet packaged in official repositories for the major distributions, we'll have to rely on a third-party source.  Several repositories offer PHP 7 RPM files.  We'll use the <a href="https://ius.io">IUS repository</a>.</p>

<p>IUS offers an installation script for subscribing to their repository and importing associated GPG keys.  Make sure you're in your home directory, and retrieve the script using <code>curl</code>:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">cd ~
</li><li class="line" prefix="$">curl 'https://setup.ius.io/' -o setup-ius.sh
</li></ul></code></pre>
<p>Run the script:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo bash setup-ius.sh
</li></ul></code></pre>
<h2 id="upgrading-mod_php-with-apache">Upgrading <code>mod_php</code> with Apache</h2>

<p>This section describes the upgrade process for a system using Apache as the web server and <code>mod_php</code> to execute PHP code.  If, instead, you are running Nginx and PHP-FPM, skip ahead to the next section.</p>

<p>Begin by removing existing PHP packages.  Press <strong>y</strong> and hit <strong>Enter</strong> to continue when prompted.</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo yum remove php-cli mod_php php-common
</li></ul></code></pre>
<p>Install the new PHP 7 packages from IUS.  Again, press <strong>y</strong> and <strong>Enter</strong> when prompted.</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo yum install mod_php70u php70u-cli php70u-mysqlnd
</li></ul></code></pre>
<p>Finally, restart Apache to load the new version of <code>mod_php</code>:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo apachectl restart
</li></ul></code></pre>
<p>You can check on the status of Apache, which is managed by the <code>httpd</code> <code>systemd</code> unit, using <code>systemctl</code>:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">systemctl status httpd
</li></ul></code></pre>
<h2 id="upgrading-php-fpm-with-nginx">Upgrading PHP-FPM with Nginx</h2>

<p>This section describes the upgrade process for a system using Nginx as the web server and PHP-FPM to execute PHP code.  If you have already upgraded an Apache-based system, skip ahead to the PHP Testing section.</p>

<p>Begin by removing existing PHP packages.  Press <strong>y</strong> and hit <strong>Enter</strong> to continue when prompted.</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo yum remove php-fpm php-cli php-common
</li></ul></code></pre>
<p>Install the new PHP 7 packages from IUS.  Again, press <strong>y</strong> and <strong>Enter</strong> when prompted.</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo yum install php70u-fpm-nginx php70u-cli php70u-mysqlnd
</li></ul></code></pre>
<p>Once the installation is finished, you'll need to make a few configuration changes for both PHP-FPM and Nginx.  As configured, PHP-FPM listens for connections on a local TCP socket, while Nginx expects a <a href="https://en.wikipedia.org/wiki/Unix_domain_socket">Unix domain socket</a>, which maps to a path on the filesystem.</p>

<p>PHP-FPM can handle multiple <strong>pools</strong> of child processes.  As configured, it provides a single pool called <strong>www</strong>, which is defined in <code>/etc/php-fpm.d/www.conf</code>.  Open this file with <code>nano</code> (or your preferred text editor):</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo nano /etc/php-fpm.d/www.conf
</li></ul></code></pre>
<p>Look for the block containing <code>listen = 127.0.0.1:9000</code>, which tells PHP-FPM to listen on the loopback address at port 9000.  Comment this line with a semicolon, and uncomment <code>listen = /run/php-fpm/www.sock</code> a few lines below.</p>
<div class="code-label " title="/etc/php-fpm.d/www.conf">/etc/php-fpm.d/www.conf</div><pre class="code-pre "><code langs="">; The address on which to accept FastCGI requests.
; Valid syntaxes are:
;   'ip.add.re.ss:port'    - to listen on a TCP socket to a specific IPv4 address on
;                            a specific port;
;   '[ip:6:addr:ess]:port' - to listen on a TCP socket to a specific IPv6 address on
;                            a specific port;
;   'port'                 - to listen on a TCP socket to all addresses
;                            (IPv6 and IPv4-mapped) on a specific port;
;   '/path/to/unix/socket' - to listen on a unix socket.
; Note: This value is mandatory.
<span class="highlight">;</span>listen = 127.0.0.1:9000
; WARNING: If you switch to a unix socket, you have to grant your webserver user
;          access to that socket by setting listen.acl_users to the webserver user.
<span class="highlight">listen = /run/php-fpm/www.sock</span>
</code></pre>
<p>Next, look for the block containing <code>listen.acl_users</code> values, and uncomment <code>listen.acl_users = nginx</code>:</p>
<div class="code-label " title="/etc/php-fpm.d/www.conf">/etc/php-fpm.d/www.conf</div><pre class="code-pre "><code langs="">; When POSIX Access Control Lists are supported you can set them using
; these options, value is a comma separated list of user/group names.
; When set, listen.owner and listen.group are ignored
;listen.acl_users = apache,nginx
;listen.acl_users = apache
<span class="highlight">listen.acl_users = nginx</span>
;listen.acl_groups =
</code></pre>
<p>Exit and save the file.  In <code>nano</code>, you can accomplish this by pressing <strong>Ctrl-X</strong> to exit, <strong>y</strong> to confirm, and <strong>Enter</strong> to confirm the filename to overwrite.</p>

<p>Next, make sure that Nginx is using the correct socket path to handle PHP files.  Start by opening <code>/etc/nginx/conf.d/default.conf</code>:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo nano /etc/nginx/conf.d/php-fpm.conf
</li></ul></code></pre>
<p><code>php-fpm.conf</code> defines an <a href="http://nginx.org/en/docs/http/ngx_http_upstream_module.html">upstream</a>, which can be referenced by other Nginx configuration directives.  Inside of the upstream block, use a <code>#</code> to comment out <code>server 127.0.0.1:9000;</code>, and uncomment <code>server unix:/run/php-fpm/www.sock;</code>:</p>
<div class="code-label " title="/etc/nginx/conf.d/php-fpm.conf">/etc/nginx/conf.d/php-fpm.conf</div><pre class="code-pre "><code langs=""># PHP-FPM FastCGI server
# network or unix domain socket configuration

upstream php-fpm {
        <span class="highlight">#</span>server 127.0.0.1:9000;
        <span class="highlight">server unix:/run/php-fpm/www.sock;</span>
}
</code></pre>
<p>Exit and save the file, then open <code>/etc/nginx/conf.d/default.conf</code>:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo nano /etc/nginx/conf.d/default.conf
</li></ul></code></pre>
<p>Look for a block beginning with <code>location ~ \.php$ {</code>.  Within this block, look for the <code>fastcgi_pass</code> directive.  Comment out or delete this line, and replace it with <code>fastcgi_pass php-fpm</code>, which will reference the upstream defined in <code>php-fpm.conf</code>:</p>
<div class="code-label " title="/etc/nginx/conf.d/default.conf">/etc/nginx/conf.d/default.conf</div><pre class="code-pre "><code langs="">  location ~ \.php$ {
      try_files $uri =404;
      fastcgi_split_path_info ^(.+\.php)(/.+)$;
      <span class="highlight">#</span> fastcgi_pass unix:/var/run/php-fpm/php-fpm.sock;
      fastcgi_pass php-fpm;
      fastcgi_index index.php;
      fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
      include fastcgi_params;
  }
</code></pre>
<p>Exit and save the file, then restart PHP-FPM and Nginx so that the new configuration directives take effect:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo systemctl restart php-fpm
</li><li class="line" prefix="$">sudo systemctl restart nginx
</li></ul></code></pre>
<p>You can check on the status of each service using <code>systemctl</code>:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">systemctl status php-fpm
</li><li class="line" prefix="$">systemctl status nginx
</li></ul></code></pre>
<h2 id="testing-php">Testing PHP</h2>

<p>With a web server configured and the new packages installed, we should be able to verify that PHP is up and running.  Begin by checking the installed version of PHP at the command line:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">php -v
</li></ul></code></pre><div class="code-label " title="Output">Output</div><pre class="code-pre "><code langs="">PHP 7.0.1 (cli) (built: Dec 18 2015 16:35:26) ( NTS )
Copyright (c) 1997-2015 The PHP Group
Zend Engine v3.0.0, Copyright (c) 1998-2015 Zend Technologies
</code></pre>
<p>You can also create a test file in the web server's document root.  Although its location depends on your server configuration, the document root is typically set to one of these directories:</p>

<ul>
<li><code>/var/www/html</code></li>
<li><code>/var/www/</code></li>
<li><code>/usr/share/nginx/html</code></li>
</ul>

<p>Using <code>nano</code>, open a new file called <code>info.php</code> in the document root.  By default, on Apache, this would be:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo nano /var/www/html/info.php
</li></ul></code></pre>
<p>On Nginx, you might instead use: </p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo nano /usr/share/nginx/html/info.php
</li></ul></code></pre>
<p>Paste the following code:</p>
<div class="code-label " title="info.php">info.php</div><pre class="code-pre "><code langs=""><?php
phpinfo();
</code></pre>
<p>Exit the editor, saving <code>info.php</code>.  Now, load the following address in your browser:</p>
<pre class="code-pre "><code langs="">http://<span class="highlight">server_domain_name_or_IP</span>/info.php
</code></pre>
<p>You should see the PHP 7 information page, which lists the running version and configuration.  Once you've double-checked this, it's safest to delete <code>info.php</code>:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo rm /var/www/html/info.php
</li></ul></code></pre>
<p>You now have a working PHP 7 installation.  From here, you may want to check out Erika Heidi's <a href="https://indiareads/company/blog/getting-ready-for-php-7/">Getting Ready for PHP 7</a> blog post, and look over the <a href="https://secure.php.net/manual/en/migration70.php">official migration guide</a>.</p>

    