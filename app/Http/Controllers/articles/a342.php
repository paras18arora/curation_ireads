<link rel='stylesheet' href='digital_ocean.css'><br><img src=https://community-cdn-digitalocean-com.global.ssl.fastly.net/assets/tutorials/images/large/LAMP_tw.png?1461607150/> <br> 
      <h3 id="introduction">Introduction</h3>

<p>A "LAMP" stack is a group of open source software that is typically installed together to enable a server to host dynamic websites and web apps.  This term is actually an acronym which represents the <strong>L</strong>inux operating system, with the <strong>A</strong>pache web server.  The site data is stored in a <strong>M</strong>ySQL database, and dynamic content is processed by <strong>P</strong>HP.</p>

<p>In this guide, we'll get a LAMP stack installed on an Ubuntu 16.04 Droplet.  Ubuntu will fulfill our first requirement: a Linux operating system.</p>

<h2 id="prerequisites">Prerequisites</h2>

<p>Before you begin with this guide, you should have a separate, non-root user account with <code>sudo</code> privileges set up on your server.  You can learn how to do this by completing steps 1-4 in the <a href="https://indiareads/community/articles/initial-server-setup-with-ubuntu-16-04">initial server setup for Ubuntu 16.04</a>.</p>

<h2 id="step-1-install-apache-and-allow-in-firewall">Step 1: Install Apache and Allow in Firewall</h2>

<p>The Apache web server is among the most popular web servers in the world.  It's well-documented, and has been in wide use for much of the history of the web, which makes it a great default choice for hosting a website.</p>

<p>We can install Apache easily using Ubuntu's package manager, <code>apt</code>.  A package manager allows us to install most software pain-free from a repository maintained by Ubuntu.  You can learn more about <a href="https://indiareads/community/articles/how-to-manage-packages-in-ubuntu-and-debian-with-apt-get-apt-cache">how to use <code>apt</code></a> here.</p>

<p>For our purposes, we can get started by typing these commands:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo apt-get update
</li><li class="line" prefix="$">sudo apt-get install apache2
</li></ul></code></pre>
<p>Since we are using a <code>sudo</code> command, these operations get executed with root privileges.  It will ask you for your regular user's password to verify your intentions.</p>

<p>Once you've entered your password, <code>apt</code> will tell you which packages it plans to install and how much extra disk space they'll take up.  Press <strong>Y</strong> and hit <strong>Enter</strong> to continue, and the installation will proceed.</p>

<p>Next, assuming that you have followed the initial server setup instructions to enable the UFW firewall, make sure that your firewall allows HTTP and HTTPS traffic.  You can make sure that UFW has an application profile for Apache like so:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo ufw app list
</li></ul></code></pre><pre class="code-pre "><code langs=""><div class="secondary-code-label " title="Output">Output</div>Available applications:
  <span class="highlight">Apache</span>
  <span class="highlight">Apache Full</span>
  <span class="highlight">Apache Secure</span>
  OpenSSH
</code></pre>
<p>If you look at the <code>Apache Full</code> profile, it should show that it enables traffic to ports 80 and 443:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo ufw app info "Apache Full"
</li></ul></code></pre><pre class="code-pre "><code langs=""><div class="secondary-code-label " title="Output">Output</div>Profile: Apache Full
Title: Web Server (HTTP,HTTPS)
Description: Apache v2 is the next generation of the omnipresent Apache web
server.

Ports:
  <span class="highlight">80,443/tcp</span>
</code></pre>
<p>Allow incoming traffic for this profile:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo ufw allow in "Apache Full"
</li></ul></code></pre>
<p>You can do a spot check right away to verify that everything went as planned by visiting your server's public IP address in your web browser (see the note under the next heading to find out what your public IP address is if you do not have this information already):</p>
<pre class="code-pre "><code langs="">http://<span class="highlight">your_server_IP_address</span>
</code></pre>
<p>You will see the default Ubuntu 16.04 Apache web page, which is there for informational and testing purposes.  It should look something like this:</p>

<p><img src="http://assets.digitalocean.com/articles/how-to-install-lamp-ubuntu-16/small_apache_default.png" alt="Ubuntu 16.04 Apache default" /></p>

<p>If you see this page, then your web server is now correctly installed and accessible through your firewall.</p>

<h3 id="how-to-find-your-server-39-s-public-ip-address">How To Find your Server's Public IP Address</h3>

<p>If you do not know what your server's public IP address is, there are a number of ways you can find it.  Usually, this is the address you use to connect to your server through SSH.</p>

<p>From the command line, you can find this a few ways.  First, you can use the <code>iproute2</code> tools to get your address by typing this:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">ip addr show eth0 | grep inet | awk '{ print $2; }' | sed 's/\/.*$//'
</li></ul></code></pre>
<p>This will give you two or three lines back.  They are all correct addresses, but your computer may only be able to use one of them, so feel free to try each one.</p>

<p>An alternative method is to use the <code>curl</code> utility to contact an outside party to tell you how <em>it</em> sees your server.  You can do this by asking a specific server what your IP address is:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo apt-get install curl
</li><li class="line" prefix="$">curl http://icanhazip.com
</li></ul></code></pre>
<p>Regardless of the method you use to get your IP address, you can type it into your web browser's address bar to get to your server.</p>

<h2 id="step-2-install-mysql">Step 2: Install MySQL</h2>

<p>Now that we have our web server up and running, it is time to install MySQL.  MySQL is a database management system.  Basically, it will organize and provide access to databases where our site can store information.</p>

<p>Again, we can use <code>apt</code> to acquire and install our software.  This time, we'll also install some other "helper" packages that will assist us in getting our components to communicate with each other:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo apt-get install mysql-server
</li></ul></code></pre>
<p><span class="note"><strong>Note</strong>: In this case, you do not have to run <code>sudo apt-get update</code> prior to the command.  This is because we recently ran it in the commands above to install Apache.  The package index on our computer should already be up-to-date.<br /></span></p>

<p>Again, you will be shown a list of the packages that will be installed, along with the amount of disk space they'll take up.  Enter <strong>Y</strong> to continue.</p>

<p>During the installation, your server will ask you to select and confirm a password for the MySQL "root" user.  This is an administrative account in MySQL that has increased privileges.  Think of it as being similar to the root account for the server itself (the one you are configuring now is a MySQL-specific account, however).  Make sure this is a strong, unique password, and do not leave it blank.</p>

<p>When the installation is complete, we want to run a simple security script that will remove some dangerous defaults and lock down access to our database system a little bit.  Start the interactive script by running:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo mysql_secure_installation
</li></ul></code></pre>
<p>You will be asked to enter the password you set for the MySQL root account.  Next, you will be asked if you want to configure the <code>VALIDATE PASSWORD PLUGIN</code>.</p>

<p><span class="warning"><strong>Warning:</strong> Enabling this feature is something of a judgment call.  If enabled, passwords which don't match the specified criteria will be rejected by MySQL with an error.  This will cause issues if you use a weak password in conjunction with software which automatically configures MySQL user credentials, such as the Ubuntu packages for phpMyAdmin.  It is safe to leave validation disabled, but you should always use strong, unique passwords for database credentials.<br /></span></p>

<p>Answer <strong>y</strong> for yes, or anything else to continue without enabling.</p>
<pre class="code-pre "><code langs="">VALIDATE PASSWORD PLUGIN can be used to test passwords
and improve security. It checks the strength of password
and allows the users to set only those passwords which are
secure enough. Would you like to setup VALIDATE PASSWORD plugin?

Press y|Y for Yes, any other key for No:
</code></pre>
<p>You'll be asked to select a level of password validation.  Keep in mind that if you enter <strong>2</strong>, for the strongest level, you will receive errors when attempting to set any password which does not contain numbers, upper and lowercase letters, and special characters, or which is based on common dictionary words.</p>
<pre class="code-pre "><code langs="">There are three levels of password validation policy:

LOW    Length >= 8
MEDIUM Length >= 8, numeric, mixed case, and special characters
STRONG Length >= 8, numeric, mixed case, special characters and dictionary                  file

Please enter 0 = LOW, 1 = MEDIUM and 2 = STRONG: <span class="highlight">1</span>
</code></pre>
<p>If you enabled password validation, you'll be shown a password strength for the existing root password, and asked you if you want to change that password.  If you are happy with your current password, enter <strong>n</strong> for "no" at the prompt:</p>
<pre class="code-pre "><code langs="">Using existing password for root.

Estimated strength of the password: <span class="highlight">100</span>
Change the password for root ? ((Press y|Y for Yes, any other key for No) : <span class="highlight">n</span>
</code></pre>
<p>For the rest of the questions, you should press <strong>Y</strong> and hit the <strong>Enter</strong> key at each prompt.  This will remove some anonymous users and the test database, disable remote root logins, and load these new rules so that MySQL immediately respects the changes we have made.</p>

<p>At this point, your database system is now set up and we can move on.</p>

<h2 id="step-3-install-php">Step 3: Install PHP</h2>

<p>PHP is the component of our setup that will process code to display dynamic content.  It can run scripts, connect to our MySQL databases to get information, and hand the processed content over to our web server to display.</p>

<p>We can once again leverage the <code>apt</code> system to install our components.  We're going to include some helper packages as well, so that PHP code can run under the Apache server and talk to our MySQL database:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo apt-get install php libapache2-mod-php php-mcrypt php-mysql
</li></ul></code></pre>
<p>This should install PHP without any problems.  We'll test this in a moment.</p>

<p>In most cases, we'll want to modify the way that Apache serves files when a directory is requested.  Currently, if a user requests a directory from the server, Apache will first look for a file called <code>index.html</code>.  We want to tell our web server to prefer PHP files, so we'll make Apache look for an <code>index.php</code> file first.</p>

<p>To do this, type this command to open the <code>dir.conf</code> file in a text editor with root privileges:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo nano /etc/apache2/mods-enabled/dir.conf
</li></ul></code></pre>
<p>It will look like this:</p>
<div class="code-label " title="/etc/apache2/mods-enabled/dir.conf">/etc/apache2/mods-enabled/dir.conf</div><pre class="code-pre "><code langs=""><IfModule mod_dir.c>
    DirectoryIndex index.html index.cgi index.pl <span class="highlight">index.php</span> index.xhtml index.htm
</IfModule>
</code></pre>
<p>We want to move the PHP index file highlighted above to the first position after the <code>DirectoryIndex</code> specification, like this:</p>
<div class="code-label " title="/etc/apache2/mods-enabled/dir.conf">/etc/apache2/mods-enabled/dir.conf</div><pre class="code-pre "><code langs=""><IfModule mod_dir.c>
    DirectoryIndex <span class="highlight">index.php</span> index.html index.cgi index.pl index.xhtml index.htm
</IfModule>
</code></pre>
<p>When you are finished, save and close the file by pressing <strong>Ctrl-X</strong>.  You'll have to confirm the save by typing <strong>Y</strong> and then hit <strong>Enter</strong> to confirm the file save location.</p>

<p>After this, we need to restart the Apache web server in order for our changes to be recognized.  You can do this by typing this:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo systemctl restart apache2
</li></ul></code></pre>
<p>We can also check on the status of the <code>apache2</code> service using <code>systemctl</code>:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo systemctl status apache2
</li></ul></code></pre><pre class="code-pre "><code langs=""><div class="secondary-code-label " title="Sample Output">Sample Output</div>● apache2.service - LSB: Apache2 web server
   Loaded: loaded (/etc/init.d/apache2; bad; vendor preset: enabled)
  Drop-In: /lib/systemd/system/apache2.service.d
           └─apache2-systemd.conf
   Active: active (running) since Wed 2016-04-13 14:28:43 EDT; 45s ago
     Docs: man:systemd-sysv-generator(8)
  Process: 13581 ExecStop=/etc/init.d/apache2 stop (code=exited, status=0/SUCCESS)
  Process: 13605 ExecStart=/etc/init.d/apache2 start (code=exited, status=0/SUCCESS)
    Tasks: 6 (limit: 512)
   CGroup: /system.slice/apache2.service
           ├─13623 /usr/sbin/apache2 -k start
           ├─13626 /usr/sbin/apache2 -k start
           ├─13627 /usr/sbin/apache2 -k start
           ├─13628 /usr/sbin/apache2 -k start
           ├─13629 /usr/sbin/apache2 -k start
           └─13630 /usr/sbin/apache2 -k start

Apr 13 14:28:42 ubuntu-16-lamp systemd[1]: Stopped LSB: Apache2 web server.
Apr 13 14:28:42 ubuntu-16-lamp systemd[1]: Starting LSB: Apache2 web server...
Apr 13 14:28:42 ubuntu-16-lamp apache2[13605]:  * Starting Apache httpd web server apache2
Apr 13 14:28:42 ubuntu-16-lamp apache2[13605]: AH00558: apache2: Could not reliably determine the server's fully qualified domain name, using 127.0.1.1. Set the 'ServerNam
Apr 13 14:28:43 ubuntu-16-lamp apache2[13605]:  *
Apr 13 14:28:43 ubuntu-16-lamp systemd[1]: Started LSB: Apache2 web server.
</code></pre>
<h3 id="install-php-modules">Install PHP Modules</h3>

<p>To enhance the functionality of PHP, we can optionally install some additional modules.</p>

<p>To see the available options for PHP modules and libraries, you can pipe the results of <code>apt-cache search</code> into <code>less</code>, a pager which lets you scroll through the output of other commands:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">apt-cache search php- | less
</li></ul></code></pre>
<p>Use the arrow keys to scroll up and down, and <strong>q</strong> to quit.</p>

<p>The results are all optional components that you can install.  It will give you a short description for each:</p>
<pre class="code-pre "><code langs="">libnet-libidn-perl - Perl bindings for GNU Libidn
php-all-dev - package depending on all supported PHP development packages
php-cgi - server-side, HTML-embedded scripting language (CGI binary) (default)
php-cli - command-line interpreter for the PHP scripting language (default)
php-common - Common files for PHP packages
php-curl - CURL module for PHP [default]
php-dev - Files for PHP module development (default)
php-gd - GD module for PHP [default]
php-gmp - GMP module for PHP [default]
…
:
</code></pre>
<p>To get more information about what each module does, you can either search the internet, or you can look at the long description of the package by typing:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">apt-cache show <span class="highlight">package_name</span>
</li></ul></code></pre>
<p>There will be a lot of output, with one field called <code>Description-en</code> which will have a longer explanation of the functionality that the module provides.</p>

<p>For example, to find out what the <code>php-cli</code> module does, we could type this:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">apt-cache show php-cli
</li></ul></code></pre>
<p>Along with a large amount of other information, you'll find something that looks like this:</p>
<pre class="code-pre "><code langs=""><div class="secondary-code-label " title="Output">Output</div>…
Description-en: command-line interpreter for the PHP scripting language (default)
 This package provides the /usr/bin/php command interpreter, useful for
 testing PHP scripts from a shell or performing general shell scripting tasks.
 .
 PHP (recursive acronym for PHP: Hypertext Preprocessor) is a widely-used
 open source general-purpose scripting language that is especially suited
 for web development and can be embedded into HTML.
 .
 This package is a dependency package, which depends on Debian's default
 PHP version (currently 7.0).
…
</code></pre>
<p>If, after researching, you decide you would like to install a package, you can do so by using the <code>apt-get install</code> command like we have been doing for our other software.</p>

<p>If we decided that <code>php-cli</code> is something that we need, we could type:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo apt-get install php-cli
</li></ul></code></pre>
<p>If you want to install more than one module, you can do that by listing each one, separated by a space, following the <code>apt-get install</code> command, like this:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo apt-get install <span class="highlight">package1</span> <span class="highlight">package2</span> <span class="highlight">...</span>
</li></ul></code></pre>
<p>At this point, your LAMP stack is installed and configured.  We should still test out our PHP though.</p>

<h2 id="step-4-test-php-processing-on-your-web-server">Step 4: Test PHP Processing on your Web Server</h2>

<p>In order to test that our system is configured properly for PHP, we can create a very basic PHP script.</p>

<p>We will call this script <code>info.php</code>.  In order for Apache to find the file and serve it correctly, it must be saved to a very specific directory, which is called the "web root".</p>

<p>In Ubuntu 14.04, this directory is located at <code>/var/www/html/</code>.  We can create the file at that location by typing:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo nano /var/www/html/info.php
</li></ul></code></pre>
<p>This will open a blank file.  We want to put the following text, which is valid PHP code, inside the file:</p>
<div class="code-label " title="info.php">info.php</div><pre class="code-pre "><code langs=""><?php
phpinfo();
</code></pre>
<p>When you are finished, save and close the file.</p>

<p>Now we can test whether our web server can correctly display content generated by a PHP script.  To try this out, we just have to visit this page in our web browser.  You'll need your server's public IP address again.</p>

<p>The address you want to visit will be:</p>
<pre class="code-pre "><code langs="">http://<span class="highlight">your_server_IP_address</span>/info.php
</code></pre>
<p>The page that you come to should look something like this:</p>

<p><img src="https://assets.digitalocean.com/articles/how-to-install-lamp-ubuntu-16/small_php_info.png" alt="Ubuntu 16.04 default PHP info" /></p>

<p>This page basically gives you information about your server from the perspective of PHP.  It is useful for debugging and to ensure that your settings are being applied correctly.</p>

<p>If this was successful, then your PHP is working as expected.</p>

<p>You probably want to remove this file after this test because it could actually give information about your server to unauthorized users.  To do this, you can type this:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo rm /var/www/html/info.php
</li></ul></code></pre>
<p>You can always recreate this page if you need to access the information again later.</p>

<h2 id="conclusion">Conclusion</h2>

<p>Now that you have a LAMP stack installed, you have many choices for what to do next.  Basically, you've installed a platform that will allow you to install most kinds of websites and web software on your server.</p>

<p>As an immediate next step, you should ensure that connections to your web server are secured, by serving them via HTTPS.  The easiest option here is to <a href="https://indiareads/community/tutorials/how-to-secure-apache-with-let-s-encrypt-on-ubuntu-16-04">use Let's Encrypt</a> to secure your site with a free TLS/SSL certificate.</p>

<p>Some other popular options are:</p>

<ul>
<li><a href="https://indiareads/community/articles/how-to-install-wordpress-on-ubuntu-14-04">Install Wordpress</a> the most popular content management system on the internet.</li>
<li><a href="https://indiareads/community/articles/how-to-install-and-secure-phpmyadmin-on-ubuntu-12-04">Set Up PHPMyAdmin</a> to help manage your MySQL databases from web browser.</li>
<li><a href="https://indiareads/community/articles/a-basic-mysql-tutorial">Learn more about MySQL</a> to manage your databases.</li>
<li><a href="https://indiareads/community/articles/how-to-use-sftp-to-securely-transfer-files-with-a-remote-server">Learn how to use SFTP</a> to transfer files to and from your server.</li>
</ul>

<p><strong>Note</strong>: We will be updating the links above to our 16.04 documentation as it is written.</p>

    