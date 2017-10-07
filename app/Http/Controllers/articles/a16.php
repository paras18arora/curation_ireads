<link rel='stylesheet' href='digital_ocean.css'><br><img src=https://community-cdn-digitalocean-com.global.ssl.fastly.net/assets/tutorials/images/large/PHP_7-twitter.png?1453917614/> <br> 
      <h3 id="introduction">Introduction</h3>

<p>PHP 7, which was released on December 3, 2015, promises substantial speed improvements over previous versions of the language, along with new features like scalar type hinting.  This guide explains how to quickly upgrade an Apache or Nginx web server running PHP 5.x (any release) to PHP 7.</p>

<span class="warning"><p>
<strong>Warning:</strong> As with most major-version language releases, it's best to wait a little while before switching to PHP 7 in production.  In the meanwhile, it's a good time to test your applications for compatibility with the new release, perform benchmarks, and familiarize yourself with new language features.</p>

<p>If you're running any services or applications with active users, it is safest to first test this process in a staging environment.<br /></p></span>

<h2 id="prerequisites">Prerequisites</h2>

<p>This guide assumes that you are running PHP 5.x on an Ubuntu 14.04 machine, using either <code>mod_php</code> in conjunction with Apache, or PHP-FPM in conjunction with Nginx.  It also assumes that you have a non-root user configured with <code>sudo</code> privileges for administrative tasks.</p>

<h2 id="adding-a-ppa-for-php-7-0-packages">Adding a PPA for PHP 7.0 Packages</h2>

<p>A <strong>Personal Package Archive</strong>, or PPA, is an Apt repository hosted on <a href="https://launchpad.net/">Launchpad</a>.  PPAs allow third-party developers to build and distribute packages for Ubuntu outside of the official channels.  They're often useful sources of beta software, modified builds, and backports to older releases of the operating system.</p>

<p>Ondřej Surý maintains the PHP packages for Debian, and offers <a href="https://launchpad.net/%7Eondrej/+archive/ubuntu/php">a PPA for PHP 7.0 on Ubuntu</a>.  Before doing anything else, log in to your system, and add Ondřej's PPA to the system's Apt sources:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo add-apt-repository ppa:ondrej/php
</li></ul></code></pre>
<p>You'll see a description of the PPA, followed by a prompt to continue.  Press <strong>Enter</strong> to proceed.</p>

<span class="note"><p>
<strong>Note:</strong> If your system's locale is set to anything other than UTF-8, adding the PPA may fail due to a bug handling characters in the author's name.  As a workaround, you can install <code>language-pack-en-base</code> to make sure that locales are generated, and override system-wide locale settings while adding the PPA:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo apt-get install -y language-pack-en-base
</li><li class="line" prefix="$">sudo LC_ALL=en_US.UTF-8 add-apt-repository ppa:ondrej/php
</li></ul></code></pre>
<p></p></span>

<p>Once the PPA is installed, update the local package cache to include its contents:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo apt-get update
</li></ul></code></pre>
<p>Now that we have access to packages for PHP 7.0, we can replace the existing PHP installation.</p>

<h2 id="upgrading-mod_php-with-apache">Upgrading <code>mod_php</code> with Apache</h2>

<p>This section describes the upgrade process for a system using Apache as the web server and <code>mod_php</code> to execute PHP code.  If, instead, you are running Nginx and PHP-FPM, skip ahead to the next section.</p>

<p>First, install the new packages.  This will upgrade all of the important PHP packages, with the exception of <code>php5-mysql</code>, which will be removed.</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo apt-get install php7.0
</li></ul></code></pre>
<p><span class="note"><strong>Note:</strong> If you have made substantial modifications to any configuration files in <code>/etc/php5/</code>, those files are still in place, and can be referenced.  Configuration files for PHP 7.0 now live in <code>/etc/php/7.0</code>.<br /></span></p>

<p>If you are using MySQL, make sure to re-add the updated PHP MySQL bindings:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo apt-get install php7.0-mysql
</li></ul></code></pre>
<h2 id="upgrading-php-fpm-with-nginx">Upgrading PHP-FPM with Nginx</h2>

<p>This section describes the upgrade process for a system using Nginx as the web server and PHP-FPM to execute PHP code.</p>

<p>First, install the new PHP-FPM package and its dependencies:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo apt-get install php7.0-fpm
</li></ul></code></pre>
<p>You'll be prompted to continue.  Press <strong>Enter</strong> to complete the installation.</p>

<p>If you are using MySQL, be sure to re-install the PHP MySQL bindings:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo apt-get install php7.0-mysql
</li></ul></code></pre>
<p><span class="note"><strong>Note:</strong> If you have made substantial modifications to any configuration files in <code>/etc/php5/</code>, those files are still in place, and can be referenced.  Configuration files for PHP 7.0 now live in <code>/etc/php/7.0</code>.<br /></span></p>

<h3 id="updating-nginx-site-s-to-use-new-socket-path">Updating Nginx Site(s) to Use New Socket Path</h3>

<p>Nginx communicates with PHP-FPM using a <a href="https://en.wikipedia.org/wiki/Unix_domain_socket">Unix domain socket</a>.  Sockets map to a path on the filesystem, and our PHP 7 installation uses a new path by default:</p>

<table class="pure-table"><thead>
<tr>
<th>PHP 5</th>
<th>PHP 7</th>
</tr>
</thead><tbody>
<tr>
<td>/var/run/php5-fpm.sock</td>
<td>/var/run/php/php7.0-fpm.sock</td>
</tr>
</tbody></table>

<p>Open the <code>default</code> site configuration file with <code>nano</code> (or your editor of choice):</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo nano /etc/nginx/sites-enabled/default
</li></ul></code></pre>
<p>Your configuration may differ somewhat.  Look for a block beginning with <code>location ~ \.php$ {</code>, and a line that looks something like <code>fastcgi_pass unix:/var/run/php5-fpm.sock;</code>.  Change this to use <code>unix:/var/run/php/php7.0-fpm.sock</code>.</p>
<div class="code-label " title="/etc/nginx/sites-enabled/default">/etc/nginx/sites-enabled/default</div><pre class="code-pre line_number"><code langs="">server {
    listen 80 default_server;
    listen [::]:80 default_server ipv6only=on;

    root /var/www/html;
    index index.php index.html index.htm;

    server_name <span class="highlight">server_domain_name_or_IP</span>;

    location / {
        try_files $uri $uri/ =404;
    }

    error_page 404 /404.html;
    error_page 500 502 503 504 /50x.html;
    location = /50x.html {
        root /usr/share/nginx/html;
    }

    location ~ \.php$ {
        try_files $uri =404;
        fastcgi_split_path_info ^(.+\.php)(/.+)$;
        <span class="highlight">fastcgi_pass unix:/var/run/php/php7.0-fpm.sock;</span>
        fastcgi_index index.php;
        fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
        include fastcgi_params;
    }
}
</code></pre>
<p>Exit and save the file.  In <code>nano</code>, you can accomplish this by pressing <strong>Ctrl-X</strong> to exit, <strong>y</strong> to confirm, and <strong>Enter</strong> to confirm the filename to overwrite.</p>

<p>You should repeat this process for any other virtual sites defined in <code>/etc/nginx/sites-enabled</code> which need to support PHP.</p>

<p>Now we can restart <code>nginx</code>:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo service nginx restart
</li></ul></code></pre>
<h2 id="testing-php">Testing PHP</h2>

<p>With a web server configured and the new packages installed, we should be able to verify that PHP is up and running.  Begin by checking the installed version of PHP at the command line:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">php -v
</li></ul></code></pre><pre class="code-pre "><code langs=""><div class="secondary-code-label " title="Output">Output</div>PHP 7.0.0-5+deb.sury.org~trusty+1 (cli) ( NTS )
Copyright (c) 1997-2015 The PHP Group
Zend Engine v3.0.0, Copyright (c) 1998-2015 Zend Technologies
    with Zend OPcache v7.0.6-dev, Copyright (c) 1999-2015, by Zend Technologies
</code></pre>
<p>You can also create a test file in the web server's document root.  Depending on your server and configuration, this may be one of:</p>

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
?>
</code></pre>
<p>Exit the editor, saving <code>info.php</code>.  Now, load the following address in your browser:</p>
<pre class="code-pre "><code langs="">http://<span class="highlight">server_domain_name_or_IP</span>/info.php
</code></pre>
<p>You should see PHP version and configuration info for PHP 7.  Once you've double-checked this, it's safest to to delete <code>info.php</code>:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo rm /var/www/html/info.php
</li></ul></code></pre>
<h2 id="conclusion">Conclusion</h2>

<p>You now have a working PHP 7 installation.  From here, you may want to check out Erika Heidi's <a href="https://indiareads/company/blog/getting-ready-for-php-7/">Getting Ready for PHP 7</a> blog post, and look over the <a href="https://secure.php.net/manual/en/migration70.php">official migration guide</a>.</p>

    