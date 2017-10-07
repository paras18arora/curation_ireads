<link rel='stylesheet' href='digital_ocean.css'><br> <br> 
      <h3 id="introduction">Introduction</h3>

<p>With more and more people using multiple devices (smartphones, computers, tablets, etc.) the need to keep every thing in sync keeps growing.</p>

<p>While syncing <a href="https://indiareads/community/tutorials/how-to-use-bittorrent-sync-to-synchronize-directories-in-ubuntu-14-04">files</a> is important, it's also useful to be able to sync calendars and contacts in their native formats.</p>

<p>The CalDAV and CardDAV standards provide an easy way to keep all our smart things up-to-date with what we are doing, as well as how to get hold of our friends and other contacts. In this tutorial, we'll show you how to sync calendars and contacts from a server you control, using a super simple installation of <a href="http://baikal-server.com">Baïkal</a>, a PHP CalDAV and CardDAV server.</p>

<blockquote>
<p><strong>Note:</strong> If you are looking for an all-in-one solution, you may want to take a look at <a href="https://indiareads/community/tutorials/how-to-use-the-owncloud-one-click-install-application">ownCloud</a> instead.</p>

<p><strong>Note:</strong> Baïkal is quick and easy but not really designed for large scale deployment. If you want calendar and contact syncing for a medium or large business, this solution may not work well for you.</p>
</blockquote>

<h3 id="prerequisites">Prerequisites</h3>

<p>Please make sure you have these prerequisites in place.</p>

<ul>
<li>Fresh Ubuntu 14.04 Droplet with <a href="https://indiareads/community/tutorials/how-to-connect-to-your-droplet-with-ssh">SSH access</a></li>
<li>A <a href="https://indiareads/community/tutorials/how-to-add-and-delete-users-on-an-ubuntu-14-04-vps">sudo user</a></li>
<li>The Baïkal instructions highly recommend having a domain, preferably a subdomain, for the server. This tutorial will use the domain name <code>dav.example.com</code>. You can use <code><span class="highlight">dav.yourdomain.com</span></code>. If you host your DNS with Digital Ocean, <a href="https://indiareads/community/tutorials/how-to-set-up-and-test-dns-subdomains-with-digitalocean-s-dns-panel">this article</a> can help you set up that subdomain</li>
</ul>

<p>We'll also be installing some packages that Baïkal needs; we'll be using an SSL certificate; and we'll go over setting those up in the article itself. If you want to purchase an SSL certificate, you should purchase it for your Baïkal server's domain or subdomain.</p>

<h2 id="step-1-—-installing-baïkal">Step 1 — Installing Baïkal</h2>

<p>To get started, we'll install some required packages, download the tarball of Baïkal, and then extract it.</p>

<p>In the examples below we're using the latest version of Baïkal, which at the time of writing is <span class="highlight">0.2.7</span>, but we recommend double-checking the latest <a href="http://baikal-server.com">version of Baïkal</a> before you get started. To find the latest version, go to the Baïkal site, and either click on the <strong>Download to get started</strong> button, or scroll down to the <strong>Get Baïkal</strong> section. If there is a newer version, copy the download link for the <strong>Regular package</strong>.</p>

<p>To get started you'll need to SSH into your Ubuntu Droplet.</p>

<p>Now let's install the packages that Baïkal will need to run. We'll assume this is a fresh Ubuntu installation, so before we can install some packages from the repos we need to update the repo cache with <code>apt-get update</code>.</p>
<pre class="code-pre "><code langs="">sudo apt-get update
</code></pre>
<p>Install some prerequisite packages: PHP, Apache, and SQLite.</p>
<pre class="code-pre "><code langs="">sudo apt-get install apache2 php5 php5-sqlite sqlite3
</code></pre>
<blockquote>
<p><strong>Note:</strong> In the Baïkal installation file, the author notes that Apache can be replaced with Nginx and SQLite can be replaced with MySQL.</p>
</blockquote>

<p>Now that we have the required pieces to get Baïkal working, lets get Baïkal installed! Since Baïkal is a PHP website of sorts, we're going to download and extract it in the Apache site directory, <code>/var/www</code>.</p>
<pre class="code-pre "><code langs="">cd /var/www
sudo wget http://baikal-server.com/get/baikal-regular-0.2.7.tgz
sudo tar -xvzf baikal-regular-0.2.7.tgz
</code></pre>
<blockquote>
<p><strong>Note:</strong> For those who'd like to know what we just told <code>tar</code> to do: <code>x</code> = e<strong>x</strong>tract, <code>v</code> = <strong>v</strong>erbose, <code>z</code> = un<strong>z</strong>ip, and <code>f</code> = <strong>f</strong>ile, followed by the file name.</p>
</blockquote>

<p>One last step and Baïkal will be <em>installed</em>. Since we've extracted the PHP app we don't need the tar file any more, so we'll remove that, rename the extracted folder to something more relevant, and then make sure it is readable and writeable by the Apache user.</p>
<pre class="code-pre "><code langs="">sudo rm baikal-regular-0.2.7.tgz
sudo mv baikal-regular <span class="highlight">dav.example.com</span>
sudo chown -R www-data:www-data <span class="highlight">dav.example.com</span>
</code></pre>
<blockquote>
<p><strong>Note:</strong> You can name the folder whatever you want, but it's much easier to identify sites, if you intend to host several, if you use the website name for the website folder.</p>
</blockquote>

<h2 id="step-2-—-setting-up-apache">Step 2 — Setting Up Apache</h2>

<p>Our application is installed, and now we need to tell Apache about it. To make things easy, Baïkal actually includes its own Apache configuration file as a template. We'll copy that file to the Apache <code>sites-available</code> directory and then edit it to fit our site.</p>
<pre class="code-pre "><code langs="">sudo cp /var/www/dav.example.com/Specific/virtualhosts/baikal.apache2 /etc/apache2/sites-available/dav_example_com.conf
</code></pre>
<p>Using your favorite text editor, open up the <code>dav_example_com.conf</code> file and change all of the URLs to use your own URL, and the paths to where you stored your site. Here is what it'll look like:</p>
<pre class="code-pre "><code langs="">sudo nano /etc/apache2/sites-available/dav_example_com.conf
</code></pre><pre class="code-pre "><code langs=""><VirtualHost *:80>
    DocumentRoot /var/www/<span class="highlight">dav.example.com</span>/html
    ServerName <span class="highlight">dav.example.com</span>

    RewriteEngine On
    RewriteRule /.well-known/carddav /card.php [R,L]
    RewriteRule /.well-known/caldav /cal.php [R,L]

    <Directory "/var/www/<span class="highlight">dav.example.com</span>/html">
        Options None
        Options +FollowSymlinks
        AllowOverride All
    </Directory>
</VirtualHost>
</code></pre>
<p>Now we'll need an <a href="https://indiareads/community/tutorials/how-to-create-a-ssl-certificate-on-apache-for-ubuntu-14-04">SSL certificate</a>.</p>

<p>You can either create or purchase your certificate. We'll assume that you followed the linked SSL tutorial, and that your key and certificate are in the <code>/etc/apache2/ssl</code> directory and called <code>apache.crt</code> and <code>apache.key</code>. Please replace these with the paths to your own certificate and key as appropriate.</p>

<p>Now we need to tell Apache how to use the SSL certificate. For this we need to combine the default SSL config file (<code>default-ssl.conf</code>) with our Baïkal config file, and name it <code>dav_example_com-ssl.conf</code>. Below is an example of what that will look like, with all the comments taken out.</p>
<pre class="code-pre "><code langs="">sudo nano /etc/apache2/sites-available/dav_example_com-ssl.conf
</code></pre><pre class="code-pre "><code langs=""><IfModule mod_ssl.c>
    <VirtualHost _default_:443>
        ServerAdmin <span class="highlight">webmaster@localhost</span>

        DocumentRoot /var/www/<span class="highlight">dav.example.com</span>/html
        ServerName <span class="highlight">dav.example.com</span>

            RewriteEngine On
            RewriteRule /.well-known/carddav /card.php [R,L]
            RewriteRule /.well-known/caldav /cal.php [R,L]

        <Directory "/var/www/<span class="highlight">dav.example.com</span>/html">
            Options None
            Options +FollowSymlinks
            AllowOverride All
        </Directory>

        ErrorLog ${APACHE_LOG_DIR}/error.log
        CustomLog ${APACHE_LOG_DIR}/access.log combined

        SSLEngine on

        SSLCertificateFile    <span class="highlight">/etc/apache2/ssl/apache.crt</span>
        SSLCertificateKeyFile <span class="highlight">/etc/apache2/ssl/apache.key</span>

        <FilesMatch "\.(cgi|shtml|phtml|php)$">
                SSLOptions +StdEnvVars
        </FilesMatch>
        <Directory /usr/lib/cgi-bin>
                SSLOptions +StdEnvVars
        </Directory>

        BrowserMatch "MSIE [2-6]" \
                nokeepalive ssl-unclean-shutdown \
                downgrade-1.0 force-response-1.0
        BrowserMatch "MSIE [17-9]" ssl-unclean-shutdown

    </VirtualHost>
</IfModule>
</code></pre>
<p>We are on the home stretch. We have the site installed and the appropriate Apache configs created. Now we need to tell Apache to enable the <code>rewrite</code> module, enable the sites, and then finally restart to get the new settings loaded.</p>
<pre class="code-pre "><code langs="">sudo a2enmod rewrite
sudo a2ensite dav_example_com
sudo a2ensite dav_example_com-ssl
sudo service apache2 restart
</code></pre>
<h2 id="step-3-—-configuring-baïkal">Step 3 — Configuring Baïkal</h2>

<p>We have one last thing to do on the command line and the rest can be done in a web browser. Baïkal uses a file called <code>ENABLE_INSTALL</code> to enable the final step of the installation. Before we open up the web browser, let's make sure that this file exists. We'll use <code>touch</code> to create the file if it isn't there, and if it's already there, all we do is update the modification date.</p>
<pre class="code-pre "><code langs="">sudo touch /var/www/<span class="highlight">dav.example.com</span>/Specific/ENABLE_INSTALL
</code></pre>
<p>That's it! We are ready to open a browser and finish the setup of Baïkal. In your favorite browser navigate to <code>https://dav.example.com</code>.</p>

<p><img src="https://assets.digitalocean.com/sync_baikal/1.png" alt="Baïkal initialization wizard" /></p>

<p>Once you're there, you will be presented with a screen with options. Set your time zone using the dropdown menu, create a new admin password (you'll have to enter it twice), and leave everything else with the default settings.</p>

<p>Click the <strong>Save changes</strong> button.</p>

<p>On the next screen you can choose the default SQLite settings or enable MySQL support.</p>

<p><img src="https://assets.digitalocean.com/sync_baikal/2.png" alt="Baïkal Database setup" /></p>

<p>If you chose to use MySQL, you can enable that support. (Using MySQL as a backend will give this tool a greater capacity and increased performance, but if this DAV server is just for you, your family and friends, or a small business, SQLite should do just fine.)</p>

<p>For this example, we'll leave the SQLite defaults enabled, and click the <strong>Save changes</strong> button on this page, too.</p>

<p>Then you'll see the option to <strong>Start using Baïkal</strong>; click this button.</p>

<p><img src="https://assets.digitalocean.com/sync_baikal/3.png" alt="Start using Baïkal" /></p>

<p>You'll be taken to the Baïkal home page.</p>

<blockquote>
<p><strong>Note:</strong> If you see the default Apache website instead of your Baïkal website, you need to disable the default Apache website and restart Apache. Things should start working now.</p>
<pre class="code-pre "><code langs="">sudo a2dissite 000-default.conf
sudo service apache2 reload
</code></pre></blockquote>

<h2 id="step-4-—-creating-a-user">Step 4 — Creating a User</h2>

<p>After running through the initial setup, all that is left is creating a user, and then connecting your clients to start syncing.</p>

<p>To create a user, log in to the Baïkal website using the username <strong>admin</strong> and the password you set during the configuration step above.</p>

<p>The first page of the application is the Dashboard. It shows you what is enabled and running and some basic stats, like the number of users, calendars, and contacts.</p>

<p>Creating a user is a <em>three click process</em>.</p>

<ol>
<li>At the top of the page, click on the link <strong>Users and resources</strong></li>
<li>Now click on the button on the right, <strong>+ Add user</strong></li>
<li>Fill out all the fields and then click the <strong>Save changes</strong> button</li>
</ol>

<p><img src="https://assets.digitalocean.com/sync_baikal/4.png" alt="Users form; fill out the fields as desired" /></p>

<blockquote>
<p><strong>Note:</strong> There aren't any requirements on the server side for the formatting of the username, but some clients may complain if the username doesn't look like an email address, like: <strong>sammy@example.com</strong></p>
</blockquote>

<h2 id="troubleshooting">Troubleshooting</h2>

<p>If you run into any problems, such as your admin password not being accepted, then there are a few commands you can run to reset the app, allowing you to set it up again. To do so, you'll need to SSH back into your Droplet to run the following commands.</p>

<p><strong>Don't do this unless you want to reset the server.</strong></p>
<pre class="code-pre "><code langs="">cd /var/www/<span class="highlight">dav.example.com</span>/Specific/
sudo rm config*.php
sudo touch ENABLE_INSTALL
</code></pre>
<p>Now you can jump back into the web browser and go through the app setup wizard again, and hopefully this time everything works.</p>

<h3 id="conclusion">Conclusion</h3>

<p>Congratulations! You've installed a CalDAV and CardDAV syncing server with a GUI control panel. At this point you can configure your clients to connect to server. When doing so, use <code>https://<span class="highlight">dav.example.com</span></code> as your host name.</p>

    