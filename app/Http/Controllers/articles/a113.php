<link rel='stylesheet' href='digital_ocean.css'><br> <br> 
      <h3 id="introduction">Introduction</h3>

<p>Although the command line is a powerful tool that can allow you to work quickly and easily in many circumstances, there are instances where a visual interface is helpful.  If you are configuring many different services on one machine, or administering portions of your system for clients, tools like <strong>ISPConfig</strong> can make this a much simpler task.</p>

<p>ISPConfig is a control panel for your server that allows you to easily configure domains, email addresses, site configurations, and user accounts.  We will be installing the panel on an Ubuntu 14.04 server.</p>

<h2 id="prerequisites">Prerequisites</h2>

<p>Before we get started, you should have a domain name pointed at the server that you will be using.  To find out <a href="https://indiareads/community/articles/how-to-set-up-a-host-name-with-digitalocean">how to configure your domain name with IndiaReads</a>, click here.</p>

<p>You will also need a non-root user with sudo privileges.  You can learn how to set up a non-root account by following steps 1-4 in our <a href="https://indiareads/community/articles/initial-server-setup-with-ubuntu-14-04">Ubuntu 14.04 initial server setup guide</a>.  Log in as this user to get started.</p>

<h2 id="upgrade-the-system">Upgrade the System</h2>

<p>The first thing we should do is upgrade the base system.  This will ensure that the packages on our system are the newest packaged versions.</p>

<p>We should update our local package index before we do this so that <code>apt</code> knows about the latest package versions:</p>
<pre class="code-pre "><code langs="">sudo apt-get update
sudo apt-get upgrade
</code></pre>
<p>Our system should now be up to date and we can get going with the rest of the installation.</p>

<h2 id="verify-hostnames-are-configured-correctly">Verify Hostnames are Configured Correctly</h2>

<p>We will start by making sure our hostnames are configured correctly.  In this guide, we are going to be assuming that the domain name that we are setting up is <code>server.test.com</code> and the IP address for the server is <code>111.111.111.111</code>.</p>

<p>We need to verify that our hostname is configured correctly.  We should look at our hosts file:</p>
<pre class="code-pre "><code langs="">sudo nano /etc/hosts
</code></pre>
<p>It may look something like this:</p>
<pre class="code-pre "><code langs="">127.0.0.1           localhost server.test.com server
</code></pre>
<p>We want to make our hostnames use our public IP address.  You can do this by splitting up the line into two lines and pointing the domain name portion to our public IP address:</p>

<pre>
127.0.0.1           localhost
<span class="highlight">111.111.111.111     server.test.com server</span>
</pre>

<p>Save and close the file when you are finished.</p>

<p>We should also edit our <code>hostname</code> file to make sure that it contains the correct domain name as well:</p>
<pre class="code-pre "><code langs="">sudo nano /etc/hostname
</code></pre>
<p>If your whole hostname is not displayed, modify the value:</p>

<pre>
<span class="highlight">server.test.com</span>
</pre>

<p>You should make sure the system uses the new value by typing:</p>
<pre class="code-pre "><code langs="">sudo hostname -F /etc/hostname
</code></pre>
<h2 id="change-system-settings">Change System Settings</h2>

<p>There are a few items that Ubuntu configures in an unconventional way that we need to undo in order for our software to function properly.</p>

<p>The first thing we need to do is disable AppArmor, which is incompatible with ISPConfig.  First, we should stop the service:</p>
<pre class="code-pre "><code langs="">sudo service apparmor stop
</code></pre>
<p>We can also tell it to unload its profiles by typing:</p>
<pre class="code-pre "><code langs="">sudo service apparmor teardown
</code></pre>
<p>After this is done, we need to tell our server not to start this service at boot:</p>
<pre class="code-pre "><code langs="">sudo update-rc.d -f apparmor remove
</code></pre>
<p>We can actually delete all of the associated files and packages by typing:</p>
<pre class="code-pre "><code langs="">sudo apt-get remove apparmor
</code></pre>
<p>Another configuration that we need to modify is the default system shell.  Ubuntu uses the <code>dash</code> shell for system processes, but ISPConfig leverages additional functionality that is provided specifically by <code>bash</code>.  We can set <code>bash</code> to be the default system shell by typing:</p>
<pre class="code-pre "><code langs="">sudo dpkg-reconfigure dash
</code></pre>
<p>At the prompt, select "No" to have the utility reconfigure the system shell pointer to use <code>bash</code> instead of <code>dash</code>.</p>

<h2 id="install-additional-components">Install Additional Components</h2>

<p>Now that we have our base system ready to go, we can begin installing some of the services that ISPConfig can manage and some software that supports ISPConfig.</p>

<p>We will be installing basic LAMP (Linux, Apache, MySQL, PHP) components, mail software, anti-virus scanning software for our mail, and other packages.</p>

<p>We will do this all in one big <code>apt</code> command, so this will be a lot of packages installed at once:</p>
<pre class="code-pre "><code langs="">sudo apt-get install apache2 apache2-utils libapache2-mod-suphp libapache2-mod-fastcgi libapache2-mod-python libapache2-mod-fcgid apache2-suexec libapache2-mod-php5 php5 php5-fpm php5-gd php5-mysql php5-curl php5-intl php5-memcache php5-memcached php5-ming php5-ps php5-xcache php5-pspell php5-recode php5-snmp php5-sqlite php5-tidy php5-xmlrpc php5-xsl php5-imap php5-cgi php-pear php-auth php5-mcrypt mcrypt php5-imagick imagemagick libruby memcached phpmyadmin postfix postfix-mysql postfix-doc mysql-server openssl getmail4 rkhunter binutils dovecot-imapd dovecot-pop3d dovecot-mysql dovecot-sieve mailman amavisd-new spamassassin clamav clamav-daemon zoo unzip zip arj nomarch lzop cabextract apt-listchanges libnet-ldap-perl libauthen-sasl-perl daemon libio-string-perl libio-socket-ssl-perl libnet-ident-perl libnet-dns-perl bind9 dnsutils vlogger webalizer awstats geoip-database libclass-dbi-mysql-perl squirrelmail pure-ftpd-common pure-ftpd-mysql snmp
</code></pre>
<p>During the installation, you will be asked a few questions.  You will be asked to select a language for <code>mailman</code>.  Select <code>en (English)</code> to continue.  You will also be asked to select and confirm a password for the MySQL administrative user.</p>

<p>Another prompt that you will get is whether to create a self-signed SSL certificate for <code>dovecot</code>.  You should select "Yes".  You will have to enter the "commonName" for your SSL certificate.  This is just your fully qualified domain name:</p>
<pre class="code-pre "><code langs="">server.test.com
</code></pre>
<p>For <code>postfix</code>, you will be asked what kind of mail configuration you need.  Select <code>Internet Site</code>.  You will then be asked to choose the system mail name.  You should set this to your domain name as well:</p>
<pre class="code-pre "><code langs="">server.test.com
</code></pre>
<p>For phpMyAdmin, the software has the ability to automatically configure itself based on your web server.  Select "apache2" and press "SPACE" to select that option.  Hit "TAB" then "ENTER" to make the selection.</p>

<p>Later on, you will asked whether you wish to configure the database for phpMyAdmin with <code>dbconfig-common</code>.  Choose "Yes" here.  You will need to enter the password of the MySQL administrator account that you selected above.  You can then select and confirm a password for the phpMyAdmin user.</p>

<p>At this point, all of your components should be installed.</p>

<h2 id="configure-the-backend-components">Configure the Backend Components</h2>

<p>Now that everything is installed, we need to start configuring our services and tools.</p>

<h3 id="mail-configuration">Mail Configuration</h3>

<p>Let's start by enabling some functionality in <code>postfix</code>.  Open the default configuration file with your editor:</p>
<pre class="code-pre "><code langs="">sudo nano /etc/postfix/master.cf
</code></pre>
<p>We just need to uncomment some of the lines in this file.  Specifically the line that deals with the submission service and the first three option lines beneath, and the smtps service and the first three option lines for that one as well:</p>
<pre class="code-pre "><code langs="">submission inet n       -       -       -       -       smtpd
  -o syslog_name=postfix/submission
  -o smtpd_tls_security_level=encrypt
  -o smtpd_sasl_auth_enable=yes
. . .
smtps     inet  n       -       -       -       -       smtpd
  -o syslog_name=postfix/smtps
  -o smtpd_tls_wrappermode=yes
  -o smtpd_sasl_auth_enable=yes
</code></pre>
<p>Now, we need to append an additional option under both of these services.  It will be the same for each:</p>

<pre>
submission inet n       -       -       -       -       smtpd
  -o syslog_name=postfix/submission
  -o smtpd_tls_security_level=encrypt
  -o smtpd_sasl_auth_enable=yes
  <span class="highlight">-o smtpd_client_restrictions=permit_sasl_authenticated,reject</span>
. . .
smtps     inet  n       -       -       -       -       smtpd
  -o syslog_name=postfix/smtps
  -o smtpd_tls_wrappermode=yes
  -o smtpd_sasl_auth_enable=yes
  <span class="highlight">-o smtpd_client_restrictions=permit_sasl_authenticated,reject</span>
</pre>

<p>Save and close the file when you are finished.</p>

<p>Another mail related service that we should configure is <code>mailman</code>, which can handle mailing lists.</p>

<p>We can start off by telling it to create a new list:</p>
<pre class="code-pre "><code langs="">sudo newlist mailman
</code></pre>
<p>You will be asked to provide the email that will be associated with the list.  You will also be asked to select a password.</p>

<p>The script will output a long list of aliases.  You should add those to the bottom of the your <code>/etc/aliases</code> file:</p>
<pre class="code-pre "><code langs="">sudo nano /etc/aliases
</code></pre>
<p>It should look something like this:</p>

<pre>
postmaster:     root
<span class="highlight">mailman:              "|/var/lib/mailman/mail/mailman post mailman"</span>
<span class="highlight">mailman-admin:        "|/var/lib/mailman/mail/mailman admin mailman"</span>
<span class="highlight">mailman-bounces:      "|/var/lib/mailman/mail/mailman bounces mailman"</span>
<span class="highlight">mailman-confirm:      "|/var/lib/mailman/mail/mailman confirm mailman"</span>
<span class="highlight">mailman-join:         "|/var/lib/mailman/mail/mailman join mailman"</span>
<span class="highlight">mailman-leave:        "|/var/lib/mailman/mail/mailman leave mailman"</span>
<span class="highlight">mailman-owner:        "|/var/lib/mailman/mail/mailman owner mailman"</span>
<span class="highlight">mailman-request:      "|/var/lib/mailman/mail/mailman request mailman"</span>
<span class="highlight">mailman-subscribe:    "|/var/lib/mailman/mail/mailman subscribe mailman"</span>
<span class="highlight">mailman-unsubscribe:  "|/var/lib/mailman/mail/mailman unsubscribe mailman"</span>
</pre>

<p>Save and close the file after you're done.  You need to make <code>postfix</code> aware of the aliases you added.  You can do that by typing:</p>
<pre class="code-pre "><code langs="">sudo newaliases
</code></pre>
<p>We can start the <code>mailman</code> service by typing:</p>
<pre class="code-pre "><code langs="">sudo service mailman start
</code></pre>
<p>Restart the <code>postfix</code> service to enable mail changes:</p>
<pre class="code-pre "><code langs="">sudo service postfix restart
</code></pre>
<p>While we're dealing with services, we should also stop and disable <code>spamassassin</code>.  ISPConfig calls this as needed and it does not need to be running all of the time:</p>
<pre class="code-pre "><code langs="">sudo service spamassassin stop
</code></pre>
<p>We can then tell the server to not start it again at boot:</p>
<pre class="code-pre "><code langs="">sudo update-rc.d -f spamassassin remove
</code></pre>
<h3 id="lamp-configuration">LAMP Configuration</h3>

<p>We need to enable <code>mcrypt</code> functionality in PHP:</p>
<pre class="code-pre "><code langs="">sudo php5enmod mcrypt
</code></pre>
<p>Another thing we need to do is enable some of the Apache modules we installed.</p>
<pre class="code-pre "><code langs="">sudo a2enmod rewrite ssl actions include cgi dav_fs suexec dav auth_digest fastcgi alias
</code></pre>
<p>We also need to make some adjustments to some of the Apache configuration files.</p>

<p>One of the modules that we enabled will currently intercept all of our PHP files.  We want to stop it from doing this.  Open the <code>suphp</code> configuration file:</p>

<pre>
sudo nano /etc/apache2/mods-available/suphp.conf
</pre>

<pre>
<IfModule mod_suphp.c>
    <FilesMatch "\.ph(p3?|tml)$">
        SetHandler application/x-httpd-suphp
    </FilesMatch>
        suPHP_AddHandler application/x-httpd-suphp
. . .
</pre>

<p>We are going to replace the top block with a single command.  It should look like this when you are finished:</p>

<pre>
<IfModule mod_suphp.c>
   <span class="highlight">AddType application/x-httpd-suphp .php .php3 .php4 .php5 .phtml</span>
   suPHP_AddHandler application/x-httpd-suphp
</pre>

<p>Save and close the file when you are done.</p>

<p>We are going to have to manually create the symbolic link for the <code>mailman</code> Apache file.  We can do that by typing:</p>
<pre class="code-pre "><code langs="">sudo ln -s /etc/mailman/apache.conf /etc/apache2/conf-available/mailman.conf
</code></pre>
<p>We can then enable that by typing:</p>
<pre class="code-pre "><code langs="">sudo a2enconf mailman
</code></pre>
<p>If you plan on creating sites that host Ruby files, you should comment out the processing of <code>.rb</code> files in the <code>mime.types</code> file.  ISPConfig will handle this itself:</p>

<pre>
sudo nano /etc/mime.types
</pre>

<pre>
application/x-rss+xml                           rss
<span class="highlight">#</span>application/x-ruby                              rb
application/x-rx
</pre>

<p>Save and close the file when you are done.</p>

<p>Now, we can restart Apache to implement our changes:</p>
<pre class="code-pre "><code langs="">sudo service apache2 restart
</code></pre>
<h2 id="miscellaneous-configuration">Miscellaneous Configuration</h2>

<p>We still need to edit a few more pieces of the system.</p>

<p>Since ISPConfig is often used to subdivide server space for reselling purposes, providing clients with FTP access is often a requirement.  We've already installed the necessary software, but we need to make some adjustments.</p>

<p>Start by editing the FTP server's configuration:</p>
<pre class="code-pre "><code langs="">sudo nano /etc/default/pure-ftpd-common
</code></pre>
<p>We need to make sure our FTP users are confined to a chroot environment so that they do not interfere with the rest of the system.  We can do this by changing the <code>VIRTUALCHROOT</code> setting to <code>true</code>:</p>

<pre>
VIRTUALCHROOT=<span class="highlight">true</span>
</pre>

<p>Since FTP is inherently insecure we should at least protect it with TLS encryption.  We can set this up by creating a flag file that simply contains the <code>1</code> character:</p>
<pre class="code-pre "><code langs="">sudo nano /etc/pure-ftpd/conf/TLS
</code></pre>
<hr />
<pre class="code-pre "><code langs="">1
</code></pre>
<p>Now, we need to create a self-signed certificate that the process can use.  We can do this by calling:</p>
<pre class="code-pre "><code langs="">sudo openssl req -x509 -nodes -days 365 -newkey rsa:2048 -keyout /etc/ssl/private/pure-ftpd.pem -out /etc/ssl/private/pure-ftpd.pem
</code></pre>
<p>This certificate will be valid for one year.  You will have to answer some prompts.  Fill them out with your information.  The <code>Common Name</code> is perhaps the most important part.</p>

<p>We need to lock down the key file afterwards by typing:</p>
<pre class="code-pre "><code langs="">sudo chmod 600 /etc/ssl/private/pure-ftpd.pem
</code></pre>
<p>When all of this is done, we can restart the service:</p>
<pre class="code-pre "><code langs="">sudo service pure-ftpd-mysql restart
</code></pre>
<p>This will allow our FTP daemon to use encryption.</p>

<p>One of the reasons we are getting FTP set up on this system is because we have installed a monitoring daemon called <code>awstats</code> that is configured to expect the existence of this service.</p>

<p>ISPConfig will call <code>awstats</code> as necessary, so it does not need to rely on the <code>cron</code> job that is usually used to poll the server.  We can remove this by typing:</p>
<pre class="code-pre "><code langs="">sudo rm /etc/cron.d/awstats
</code></pre>
<h2 id="install-ispconfig">Install ISPConfig</h2>

<p>We are finally ready to install the actual ISPConfig software.</p>

<p>We can do that by downloading the latest stable version onto our server.  As of this writing, the latest stable version that has a direct link available is version 3.  We will update the installation once we get everything installed.</p>

<p>Now, you should change to your home directory and download the project using <code>wget</code>:</p>
<pre class="code-pre "><code langs="">cd ~
wget http://www.ispconfig.org/downloads/ISPConfig-3-stable.tar.gz
</code></pre>
<p>After the download is complete, extract the directory structure and move into the <code>install</code> subdirectory of the extracted folder structure:</p>
<pre class="code-pre "><code langs="">tar xzvf ISPConfig*
cd ispconfig3_install/install/
</code></pre>
<p>Now, we are ready to install the software.  Do so by typing:</p>
<pre class="code-pre "><code langs="">sudo php -q install.php
</code></pre>
<p>You will be taken through a very lengthy installation processes.</p>

<p>Luckily, the only detail you <strong>actually</strong> need to enter is your MySQL root password!  For every other entry, just press "ENTER" to use the default value and skip ahead.</p>

<p>When you are finished with the installation, go ahead and update to the latest version by typing:</p>
<pre class="code-pre "><code langs="">sudo php -q update.php
</code></pre>
<p>Again, just press "ENTER" to use the defaults for each command.</p>

<p>When you are finished, you can visit your ISPConfig service by visiting your domain name followed by <code>:8080</code> in your web browser:</p>

<pre>
https://<span class="highlight">server_domain_name</span>:8080
</pre>

<p>You will get an SSL warning since we are using self-signed certificates:</p>

<p><img src="https://assets.digitalocean.com/articles/ispconfig/ssl_warning.png" alt="ISPConfig SSL warning" /></p>

<p>Click "proceed" or "continue" to accept the certificate.</p>

<p>You will be taken to a login screen.</p>

<p><img src="https://assets.digitalocean.com/articles/ispconfig/login.png" alt="ISPConfig login screen" /></p>

<p>The default username and password are both <code>admin</code>:</p>
<pre class="code-pre "><code langs="">Username: admin
Password: admin
</code></pre>
<p>Enter those values and you will be taken to the ISPConfig3 interface:</p>

<p><img src="https://assets.digitalocean.com/articles/ispconfig/main_site.png" alt="ISPConfig main interface" /></p>

<p>Once you get here, you should change the <code>admin</code> user's password by clicking on the "System" button, then clicking the "CP Users" link under the "User Management" category of the left-hand navigation menu.</p>

<p>Click on the <code>admin</code> user account in the main window.  You will have the option to change the password for the admin user on this page.</p>

<h2 id="conclusion">Conclusion</h2>

<p>You should now have your ISPConfig panel installed and configured.  You should be able to manage domains, mail, and accounts from within this interface.</p>

<div class="author">By Justin Ellingwood</div>

    