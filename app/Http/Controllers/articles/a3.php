<link rel='stylesheet' href='digital_ocean.css'><br><img src=https://community-cdn-digitalocean-com.global.ssl.fastly.net/assets/tutorials/images/large/Pydio_setup_tutorial_patricia.png?1463426171/> <br> 
      <h3 id="introduction">Introduction</h3>

<p>As adoption of the cloud increases, more and more data is being stored remotely. From music to pictures to personal documents, many people are uploading files onto servers they don't manage. If you'd rather keep your files on a server you control, you can host your own Dropbox-like file sharing server using <a href="https://pydio.com/">Pydio</a> (formerly AjaXplorer).</p>

<p>Pydio provides many of the same features as other file syncing services: a web interface, native clients for Mac, Windows, and Linux, mobile clients for iOS and Android, and the ability to share files with other Pydio users or the public.</p>

<h2 id="goals">Goals</h2>

<p>In this article, we'll stand up a straightforward Pydio installation that runs well even without powerful hardware. Like many enterprise-grade open source projects, Pydio has a community edition and an enterprise edition. We'll be installing the community edition, but note that the enterprise license is free for teams smaller than 10 people.</p>

<p>In terms of our software stack, we'll use Postfix for email support; by default, PHP can't send emails. If you'd like a lighter solution, you can install <code>ssmtp</code>, but this setup uses Postfix because it requires the least amount of tweaking to get PHP to support it. Note that enabling email support isn't required, but it makes things much simpler; without it, Pydio won't be able to send password reset emails or welcome emails.</p>

<p>We'll also be sticking with Apache as our web server (for simplicity) and a SQLite database (instead of MySQL, for fewer moving parts). This setup is great if the group using Pydio isn't big or doesn't push a lot of data to the server at the same time. However, if you need something with a bit more power, there are some guidelines on how to improve performance in the conclusion.</p>

<h2 id="prerequisites">Prerequisites</h2>

<p>To follow this tutorial, you'll need:</p>

<ul>
<li><p>One Ubuntu 14.04 Droplet with a <a href="https://indiareads/community/tutorials/initial-server-setup-with-ubuntu-14-04">sudo non-root user</a>.</p></li>
<li><p>Apache installed, which you can do by following <a href="https://indiareads/community/tutorials/how-to-install-linux-apache-mysql-php-lamp-stack-on-ubuntu-14-04#step-1-install-apache">step 2 of this LAMP tutorial</a>.</p></li>
<li><p>A FQDN (Fully Qualified Domain Name), which you can set up by following <a href="https://indiareads/community/tutorials/how-to-set-up-a-host-name-with-digitalocean">this host name tutorial</a>.</p></li>
<li><p>Postfix installed, which you can set up by following the steps through "Configure Postfix" from <a href="https://indiareads/community/tutorials/how-to-install-and-setup-postfix-on-ubuntu-14-04">this Postfix installation tutorial</a>. When prompted during installation, choose the <strong>Internet Site</strong> configuration, and enter your domain name (e.g. <code><span class="highlight">example.com</span></code>) for the <strong>System mail name</strong>.</p></li>
</ul>

<p>We'll also be setting SSL certificates for your domain using Let's Encrypt. You'll be following <a href="https://indiareads/community/tutorials/how-to-secure-apache-with-let-s-encrypt-on-ubuntu-14-04">this Let's Encrypt on Apache tutorial</a>, but to simplify setup, we won't be setting that up until Pydio is installed during step 3.</p>

<p>If you'd like to learn more about the how SSL/TLS certs work, please read <a href="https://indiareads/community/tutorials/openssl-essentials-working-with-ssl-certificates-private-keys-and-csrs">this OpenSSL essentials article</a>.</p>

<h2 id="step-1-—-installing-pydio">Step 1 — Installing Pydio</h2>

<p>In this step, we'll install Pydio's dependencies and Pydio itself.</p>

<p>First, update your package index.</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo apt-get update
</li></ul></code></pre>
<p>Now, install PHP.</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo apt-get install php5 libapache2-mod-php5
</li></ul></code></pre>
<p>Next, we need to download the Pydio tar file and decompress it on our web server. The Pydio download is hosted on <a href="http://sourceforge.net/projects/ajaxplorer/files/pydio/stable-channel/">SourceForge</a>. You can click through to find the mirror closest to your geographically, or you can just use the link below to use the UK mirror.</p>

<p>As of publishing time, Pydio is at version 6.2.2. You may want to check if Pydio has been updated and grab the latest version from SourceForge if so.</p>

<p>Download the Pydio tar file into your home directory.</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">wget -P ~/ <span class="highlight">http://vorboss.dl.sourceforge.net/project/ajaxplorer/pydio/stable-channel/6.2.2/pydio-core-6.2.2.tar.gz</span>
</li></ul></code></pre>
<p>Decompress the tarball.</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">tar -xvzf ~/pydio-core-<span class="highlight">6.2.2</span>.tar.gz
</li></ul></code></pre>
<p>Then move it into the default location for web sites on a Ubuntu server, <code>/var/www</code>.</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo mv ~/pydio-core-<span class="highlight">6.2.2</span> /var/www/pydio
</li></ul></code></pre>
<p>Once the directory is in place, we'll need to change its permissions so Apache can store data and update configuration files.</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo chown -R www-data:www-data /var/www/pydio
</li></ul></code></pre>
<h2 id="step-2-—-setting-up-php-modules">Step 2 — Setting Up PHP Modules</h2>

<p>With Pydio in place, we need to install and set up a few dependencies to get Pydio to working correctly. We'll be making a lot of changes in this step, and we'll be prompted to restart Apache after every step. You can do this if you want, but here, we'll wait to the very end of all the setup and restart Apache once.</p>

<p>First, we'll install and enable the extra PHP modules we need.</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo apt-get install php5-mcrypt php5-gd php5-sqlite
</li></ul></code></pre>
<p>One of these PHP modules, <code>mcrypt</code>, isn't enabled by default. We can enable it using <code>phpenmod</code>.</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo php5enmod mcrypt
</li></ul></code></pre>
<p>In addition to installing and enabling some PHP modules, we need to enable the <code>a2enmod</code> Apache module or the sync client won't work.</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo a2enmod rewrite
</li></ul></code></pre>
<p>Now that PHP is fully installed, we need to make a few edits in the <code>php.ini</code> file. There are three lines that need to be updated. Using your preferred editor, like <code>nano</code>, edit the <code>php.ini</code> file. </p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo nano /etc/php5/apache2/php.ini
</li></ul></code></pre>
<p>Find the line with <code>output_buffering = 4096</code> and change <code>4096</code> to <code>Off</code>.</p>
<div class="code-label " title="Updated /etc/php5/apache2/php.ini">Updated /etc/php5/apache2/php.ini</div><pre class="code-pre "><code langs="">; Default Value: Off
; Development Value: 4096
; Production Value: 4096
; http://php.net/output-buffering
output_buffering = <span class="highlight">Off</span>
</code></pre>
<p>Next, find <code>upload_max_filesize = 2M</code> and change <code>2M</code> to any large number, like <code>1G</code>. (M is short for MB and G for GB.)</p>
<div class="code-label " title="Updated /etc/php5/apache2/php.ini">Updated /etc/php5/apache2/php.ini</div><pre class="code-pre "><code langs="">; Maximum allowed size for uploaded files.
; http://php.net/upload-max-filesize
upload_max_filesize = <span class="highlight">1G</span>
</code></pre>
<p>Finally, find <code>post_max_size = 8M</code> and change it the same number as <code>upload_max_filesize</code> or larger. If you think you'll have multiple large uploads going at the same time or multiple users using the system at once, you can go with a bigger number.</p>
<div class="code-label " title="Updated /etc/php5/apache2/php.ini">Updated /etc/php5/apache2/php.ini</div><pre class="code-pre "><code langs="">; Maximum size of POST data that PHP will accept.
; Its value may be 0 to disable the limit. It is ignored if POST data reading
; is disabled through enable_post_data_reading.
; http://php.net/post-max-size
post_max_size = <span class="highlight">1G</span>
</code></pre>
<p>You can save and close <code>/etc/php5/apache2/php.ini</code>. This is Apache's <code>php.ini</code> file; next, open the command line access <code>php.ini</code> file at <code>/etc/php5/cli/php.ini</code>.</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo nano /etc/php5/cli/php.ini
</li></ul></code></pre>
<p>Make the same three changes in this file as above, then save and close it.</p>

<h2 id="step-3-—-configuring-apache">Step 3 — Configuring Apache</h2>

<p>In this step, we'll customize our Apache configuration.</p>

<p>First, create and open a new file called <code>pydio.conf</code> with your favorite text editor.</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo nano /etc/apache2/sites-available/pydio.conf
</li></ul></code></pre>
<p>The following Apache configuration is a copy of the <code>000-default</code> configuration file with the comments removed and a few additional blocks added for this specific Pydio install, like the document root and log files.</p>
<div class="code-label " title="/etc/apache2/sites-available/pydio.conf">/etc/apache2/sites-available/pydio.conf</div><pre class="code-pre "><code langs=""><VirtualHost *:80>
    ServerAdmin <span class="highlight">sammy</span>@<span class="highlight">your_server_ip</span>
    ServerName <span class="highlight">your_server_ip</span>
    DocumentRoot /var/www/pydio

    ErrorLog ${APACHE_LOG_DIR}/pydio-error.log
    CustomLog ${APACHE_LOG_DIR}/pydio-access.log combined

    <Directory /var/www/pydio/>
        AllowOverride All
    </Directory>
</VirtualHost>
</code></pre>
<p>Before you copy and paste this into <code>pydio.conf</code>, let's go over what is in it:</p>

<ul>
<li><p><code><VirtualHost *:80></code> defines a <em>virtual host</em>, which allows multiple sites to be hosted on a single server. This line specifically defines this virtual host as the default site on this server and it'll connect over port 80.</p></li>
<li><p><code>ServerAdmin</code> defines an email address for Apache to send errors to, if error handling is setup that way.</p></li>
<li><p><code>ServerName</code> is the DNS name for the Pydio server or your server's IP. If you start with an IP and want to change it later, you can, or you can leave the IP but add a <code>ServerAlias</code> line with a new DNS name; both will work.</p></li>
<li><p><code>DocumentRoot</code> is where the website is stored on your Droplet that Apache needs to server up.</p></li>
<li><p><code>ErrorLog</code> and <code>CustomLog</code> define where to save the Apache logs. These log options can get pretty complicated if you need a very custom logging setup, but these defaults will work for our purposes.</p></li>
</ul>

<p>Paste this configuration into the file, then save and close it. With our Apache configuration files in place, we now need to disable the default Apache configuration.</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo a2dissite 000-default
</li></ul></code></pre>
<p>Now, enable our two config files.</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo a2ensite pydio
</li></ul></code></pre>
<p>In the prerequisites, we mentioned that we would set up Let's Encrypt to enable SSL/TLS encryption. This keeps our login information and our data secure from people who can sniff packets on our local network or over the internet. Because we are using Apache as our webserver, Let's Encrypt has support to automatically configure Apache for us. To make things easy, we've set up Apache without SSL so when we run the auto Let's Encrypt script it'll set it all up for us.</p>

<p>Now is time to follow the <a href="https://indiareads/community/tutorials/how-to-secure-apache-with-let-s-encrypt-on-ubuntu-14-04">Let's Encrypt on Apache tutorial</a>. Use your FQDN you've chosen during the setup of Let's Encrypt and the installer script will see our Apache config and create an SSL version for you.</p>

<p>Make sure to choose <code>pydio.conf</code> if you're asked which virtual host you'd like to choose. Because we want our connection to always be secure, make sure to select <strong>Secure — Make all requests redirect to secure HTTPS access</strong> when the Let's Encrypt script asks.</p>

<p>Finally, we can restart Apache for our changes to take effect.</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo service apache2 restart
</li></ul></code></pre>
<h2 id="step-4-—-customizing-php-mailer-support">Step 4 — Customizing PHP Mailer Support</h2>

<p>Postfix should be installed from the prerequisites. Next, we'll want to make a quick change to prevent anything but local apps from using it.</p>

<p>To make this change, we'll need to edit the Postfix config file, <code>/etc/postfix/main.cf</code>.</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo nano /etc/postfix/main.cf
</li></ul></code></pre>
<p>Search for the <code>inet_interfaces</code> line and update it from <code>all</code> to <code>localhost</code>.</p>
<div class="code-label " title="/etc/postfix/main.cf">/etc/postfix/main.cf</div><pre class="code-pre "><code langs="">. . .
recipient_delimiter = +
inet_interfaces = <span class="highlight">localhost</span>
inet_protocols = all
. . .
</code></pre>
<p>Now restart the Postfix service to enable the changes.</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo service postfix restart
</li></ul></code></pre>
<h2 id="step-5-—-finishing-pydio-setup">Step 5 — Finishing Pydio Setup</h2>

<p>Pydio is installed; in this step, we'll finish setting it up.</p>

<p>Visit <code>https://<span class="highlight">example.com</span></code> in your favorite browser. The first page you see will be labeled <strong>Pydio Diagnostic Tool</strong>. In the list on that page, the top item is a warning about <strong>Server charset encoding</strong>. We'll fix that in a moment. As long as that is the only warning and the rest of the items are <strong>OK</strong>, click on the button <strong>CLICK HERE TO CONTINUE TO PYDIO</strong>.</p>

<p>When prompted, click the <strong>Start Wizard ></strong> button. On the first page for <strong>Main options</strong>, fill out the fields:</p>

<ul>
<li><strong>Application Title</strong>, which is what's seen in the browser's title bar</li>
<li><strong>Welcome Message</strong>, which is seen on the login screen</li>
<li><strong>Administrator Login</strong>, the admin username</li>
<li><strong>Admin Display Name</strong>, which is what it sounds like</li>
<li><strong>Adminstrator Password</strong></li>
</ul>

<p>When you have that all filled in, press the red <strong>>></strong> button in the bottom right.</p>

<p><img src="https://assets.digitalocean.com/articles/pydio/H6sSWHv.png" alt="Pydio installer Main Options" /></p>

<p>On the second page for <strong>Database Connexion</strong>, choose <strong>Sqlite 3</strong> from the <strong>Database</strong> pull down menu. Don't modify the file line to where the SQLite database will be stored. Click on the <strong>Test Connection</strong> button to make sure everything is working. You should see a green box will appear at the bottom of the screen if the test is successful. Then continue by clicking on the <strong>>></strong> button.</p>

<p>On stage three for <strong>Advanced Options</strong>, most things will be automatically detected, so you just need to confirm they're correct. The one thing we will need to do is enable email support.</p>

<ul>
<li>For <strong>Enable emails</strong>, select <strong>Yes</strong> from the pull down menu</li>
<li>For <strong>Php Mailer</strong>, select <strong>Mail</strong></li>
<li>Enter your email address for the <strong>Administrator Email</strong></li>
</ul>

<p>You can click the <strong>Try sending an email with the configured data</strong> button to make sure everything is working.</p>

<p>Finally, finish the installation by clicking on the <strong>Install Pydio</strong> button.</p>

<h2 id="step-6-—-using-pydio">Step 6 — Using Pydio</h2>

<p>Now that Pydio is installed, we will be at the login screen with our custom welcome message. We can now log in with the admin user we defined in the previous step.</p>

<p>Once we've logged in, we'll see two options listed on the left: <strong>Common Files</strong> and <strong>My Files</strong>. These two options are called <em>workspaces</em>, which are essentially file shares or folders where you can store files. <strong>My Files</strong> is just for you, and <strong>Common Files</strong> is a shared folder for all users on this Pydio installation.</p>

<p>We'll be able make other workspaces and share them with whomever you wish. Now that Pydio is installed, click around and see how it works and invite other users to store their files with you.</p>

<p>Though the web interface is useful and you can upload, download, arrange, and share your data, you'll probably upload your files with Pydio directly through a native client. You can download the <a href="https://pydio.com/en/products/downloads/pydiosync-desktop-app">desktop clients here</a> (Mac/Win/Linux), the <a href="https://itunes.apple.com/fr/app/pydio/id709275884">iOS client here</a> and the <a href="https://play.google.com/store/apps/details?id=com.pydio.android.Client">Android client here</a>.</p>

<p>With the sync client installed, launch Pydio Sync and follow the wizard to get it syncing our first workspace locally. </p>

<p><img src="https://assets.digitalocean.com/articles/pydio/uljFNvX.png" alt="Pydio Sync wizard" /></p>

<p>If you chose to use a self signed certificate (instead of Let's Encrypt), you'll get an error about the certificate. If you do, check the <strong>Trust SSL Certificate</strong> box at the bottom that appears after the warning, and then click the <strong>Connect</strong> button again. </p>

<p>Unlike other file sharing tools that will sync all of the content under your account, Pydio lets you choose to sync each workspace individually. When you run the client for the first time, you can choose which workspace to sync locally. Once the first synchronization is set, you can add additional workplace synchronizations. </p>

<h2 id="conclusion">Conclusion</h2>

<p>Pydio lets you take control of your data and with the native clients on all major desktop and mobile platforms, your data can be accessible whenever you need it as well. But Pydio can do more than just host your files. There is a collection of <a href="https://pydio.com/en/docs/references/plugins">plugins</a> to extend functionality.</p>

<p>If you find Pydio's performance in this setup a little too slow for your use case, here are a few tips for improving it:</p>

<ul>
<li>Couple Pydio with a MySQL or PostgreSQL backend instead of Sqlite.</li>
<li>Use <a href="https://indiareads/community/tutorials/how-to-install-nginx-on-ubuntu-14-04-lts">Nginx</a> as the web server instead of Apache.</li>
<li>Upgrade your server to have more RAM and CPU</li>
<li>You can even <a href="https://pydio.com/en/docs/v6/setup-webdav-server-access">enable WebDAV access</a> on your server for 3rd party app syncing that are WebDAV aware.</li>
</ul>

    