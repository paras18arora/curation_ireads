<link rel='stylesheet' href='digital_ocean.css'><br> <br> 
      <h3 id="introduction">Introduction</h3>

<p>Drupal is a popular content management system (CMS) used to run some of the largest blogs and websites across the internet.  Due to the stability of the base, the adaptability of the platform, and its active community, Drupal remains a popular choice after more than a decade on the scene.</p>

<p>In this guide, we will cover how to install Drupal on an Ubuntu 14.04 server.  We will be using Apache to serve our site, since this is the configuration recommended by the Drupal team.</p>

<h3 id="prerequisites">Prerequisites</h3>

<p>Before you get started with this guide, you will need an Ubuntu 14.04 server with some basic configuration completed.  Follow our <a href="https://indiareads/community/tutorials/initial-server-setup-with-ubuntu-14-04">Ubuntu 14.04 initial server setup guide</a> to get a non-root user with sudo privileges set up.</p>

<p>You will also need to have Apache, PHP, and MySQL configured on your server.  You can learn how to set this up by following our guide on <a href="https://indiareads/community/tutorials/how-to-install-linux-apache-mysql-php-lamp-stack-on-ubuntu-14-04">getting LAMP installed on Ubuntu 14.04</a>.  </p>

<p>Once you have fulfilled the above requirements, continue on with this guide.</p>

<p>Before we get the Drupal files and install them into our web directory, we need to prepare our system.  While Apache, PHP, and MySQL have already been installed, we need to make some additional changes and do some tweaks to each of these for our installation.</p>

<h2 id="configure-a-mysql-user-and-database-for-drupal">Configure a MySQL User and Database for Drupal</h2>

<p>The first thing we will do is configure a MySQL user and database for our Drupal installation to use.  It is important to configure a dedicated user and database for security reasons.</p>

<p>To begin, log into MySQL:</p>
<pre class="code-pre "><code class="code-highlight language-bash">mysql -u root -p
</code></pre>
<p>You will be prompted for the MySQL root user's password that you configured during the installation of that software.</p>

<p>Once you have successfully authenticated, you will be dropped into a MySQL prompt.  First, create a database for your Drupal installation to use.  We will call our database <code>drupal</code> for simplicity's sake:</p>
<pre class="code-pre "><code class="code-highlight language-sql">CREATE DATABASE drupal;
</code></pre>
<p>Next, you need to create a user that the Drupal software can use to connect to the database.  In this guide, we'll call our user <code>drupaluser</code>.  Select a strong password to replace the one in the block below:</p>
<pre class="code-pre "><code class="code-highlight language-sql">CREATE USER drupaluser@localhost IDENTIFIED BY '<span class="highlight">password</span>';
</code></pre>
<p>Now, we have a database and a user, but our user does not yet have permission to perform any actions on the database.  We can fix that by granting the user permissions.  Drupal needs a variety of permissions in order to function correctly.  Below is a good selection that will allow the software to function without exposing our database unnecessarily:</p>
<pre class="code-pre "><code class="code-highlight language-sql">GRANT SELECT,INSERT,UPDATE,DELETE,CREATE,DROP,INDEX,ALTER,CREATE TEMPORARY TABLES,LOCK TABLES ON drupal.* TO drupaluser@localhost;
</code></pre>
<p>Your user has now been given permission to administer the database we created.  To implement these changes right now, we need to flush the privilege information to disk:</p>
<pre class="code-pre "><code class="code-highlight language-sql">FLUSH PRIVILEGES;
</code></pre>
<p>Now, we can exit our interactive MySQL session:</p>
<pre class="code-pre "><code langs="">exit
</code></pre>
<p>You will be dropped back into your <code>bash</code> session.</p>

<h2 id="install-php-modules-and-tweak-the-configuration">Install PHP Modules and Tweak the Configuration</h2>

<p>Next, we will install a few PHP modules that will be needed by the Drupal application.  Luckily, they are in Ubuntu's default repositories.</p>

<p>Update your local package cache and install them by typing:</p>
<pre class="code-pre "><code class="code-highlight language-bash">sudo apt-get update
sudo apt-get install php5-gd php5-curl libssh2-php
</code></pre>
<p>We will also be making a few small tweaks to our PHP configuration file. These are recommended by the Drupal developers.  Open the Apache PHP configuration file with sudo privileges in your text editor:</p>
<pre class="code-pre "><code class="code-highlight language-bash">sudo nano /etc/php5/apache2/php.ini
</code></pre>
<p>Search for the <code>expose_php</code> directive and the <code>allow_url_fopen</code> directive and set them both to "Off":</p>
<pre class="code-pre "><code class="code-highlight language-ini">. . .
expose_php = <span class="highlight">Off</span>
. . .
allow_url_fopen = <span class="highlight">Off</span>
. . .
</code></pre>
<p>Save and close the file when you are finished.</p>

<h2 id="enable-rewrite-functionality-and-htaccess-files-in-apache">Enable Rewrite Functionality and Htaccess Files in Apache</h2>

<p>Next, we should look at Apache.  First, we want to enable rewrite functionality.  This will allow our Drupal site to modify URLs to human-friendly strings.</p>

<p>The actual Apache <code>mod_rewrite</code> modules is already installed by default.  However, it is not enabled.  We can flip the switch to enable that module by typing:</p>
<pre class="code-pre "><code class="code-highlight language-bash">sudo a2enmod rewrite
</code></pre>
<p>This will enable the module the next time Apache is restarted.  Before we restart Apache, we need to adjust our virtual host configuration to allow the use of an <code>.htaccess</code> file.  This file will contain the actual rewrite rules and is included by default in the Drupal installation.</p>

<p>Open the default virtualhost file now:</p>
<pre class="code-pre "><code class="code-highlight language-bash">sudo nano /etc/apache2/sites-enabled/000-default.conf
</code></pre>
<p>Within the "VirtualHost" block, add a directory block that points to our web root.  Within this block, set the <code>AllowOverride</code> directive to "All".  You may also want to add a <code>ServerName</code> directive to point to your domain name and change the <code>ServerAdmin</code> directive to reflect a valid email address:</p>
<pre class="code-pre "><code langs=""><VirtualHost *:80>
    . . .
    ServerName  <span class="highlight">example.com</span>
    ServerAdmin webmaster@<span class="highlight">example.com</span>
    DocumentRoot /var/www/html

    <span class="highlight"><Directory /var/www/html></span>
        <span class="highlight">AllowOverride All</span>
    <span class="highlight"></Directory></span>
    . . .
</VirtualHost>
</code></pre>
<p>Save and close the file when you are finished.</p>

<p>Now, we just need to restart the web server to implement our changes to Apache and PHP:</p>
<pre class="code-pre "><code class="code-highlight language-bash">sudo service apache2 restart
</code></pre>
<h2 id="install-the-drupal-files">Install the Drupal Files</h2>

<p>Now that our system is ready, we can install Drupal into our web root.</p>

<p>Actually, we will initially unpack the files into our home directory and then copy them to the appropriate location.  Doing so will give us ready access to the original files in the event that something goes wrong or in case any files are accidentally deleted later on.</p>

<p>Go to the <a href="https://www.drupal.org/project/drupal">Drupal download page</a> and checkout the latest version under the "Recommended releases" section.  Right click on the <code>tar.gz</code> link of the version you are interested and choose "copy link address" or whatever similar option your browser provides.</p>

<p>Back on your server, change to your home directory and use <code>wget</code> to download the project file using the link you copied:</p>
<pre class="code-pre "><code langs="">cd ~
wget http://ftp.drupal.org/files/projects/drupal-<span class="highlight">7.32</span>.tar.gz
</code></pre>
<p>Your link will likely have a different version number at the end.  Once the file has been downloaded, extract the application directory by typing:</p>
<pre class="code-pre "><code langs="">tar xzvf drupal*
</code></pre>
<p>Now, move into the newly extracted directory structure and use the <code>rsync</code> utility to safely copy all of the files into the web root directory of your server.  We are using the dot in this command to specify the current directory.  This is necessary in order to copy some hidden files that we need:</p>
<pre class="code-pre "><code langs="">cd drupal*
sudo rsync -avz . /var/www/html
</code></pre>
<p>Now you have the original version of the files in a directory within your home folder in case you ever need to reference them.  We will move into the web root directory to customize our installation:</p>
<pre class="code-pre "><code class="code-highlight language-bash">cd /var/www/html
</code></pre>
<h2 id="adjusting-the-drupal-files-for-security-and-ease-of-installation">Adjusting the Drupal Files for Security and Ease of Installation</h2>

<p>The web-based installation script requires that we make some changes to our Drupal directory in order to complete the process correctly.  We should get this out of the way beforehand so that we do not have to switch back and forth between the web browser and the command line.</p>

<p>First, we need to make a new directory under the sub-tree <code>sites/default</code> called <code>files</code>:</p>
<pre class="code-pre "><code class="code-highlight language-bash">mkdir /var/www/html/sites/default/files
</code></pre>
<p>Next, we should copy the default settings file to the filename that Drupal uses for the active configuration:</p>
<pre class="code-pre "><code class="code-highlight language-bash">cp /var/www/html/sites/default/default.settings.php /var/www/html/sites/default/settings.php
</code></pre>
<p>This active settings file temporarily needs some additional permissions during the installation procedure.  We need to give write permissions to the group owner for the time being (we will be assigning the group owner to the web user momentarily).  We will remove this after the installation is successful:</p>
<pre class="code-pre "><code class="code-highlight language-bash">chmod 664 /var/www/html/sites/default/settings.php
</code></pre>
<p>Next, we need to give group ownership of our files to the web user, which in Ubuntu is <code>www-data</code>.  We want to give the entire Drupal installation these ownership properties:</p>
<pre class="code-pre "><code class="code-highlight language-bash">sudo chown -R :www-data /var/www/html/*
</code></pre>
<p>Your server is now configured appropriately to run the web-based installation script.</p>

<h2 id="complete-the-web-based-installation-procedure">Complete the Web-based Installation Procedure</h2>

<p>The remainder of the installation will take place in your web browser.  Open your browser and navigate to your server's domain name or IP address:</p>
<pre class="code-pre "><code langs="">http://<span class="highlight">server_domain_or_IP</span>
</code></pre>
<p>You will see the Drupal installation procedure's initial page:</p>

<p><img src="https://assets.digitalocean.com/articles/drupal_ubuntu_1404/choose_profile.png" alt="Drupal choose profile" /></p>

<p>Unless you have a reason not to, select the "Standard" installation and click "Save and continue".  Click the next few continue buttons until you get to the database configuration page.  Fill in the details you used when you configured your database and user. </p>

<p>For this guide, we used a database called <code>drupal</code>, a database user named <code>drupaluser</code>, and a password of <code>password</code>.  You should have selected a different password during the user creation stage.  Click "Save and continue" again when you have filled in your database details:</p>

<p><img src="https://assets.digitalocean.com/articles/drupal_ubuntu_1404/database_config.png" alt="Drupal database config" /></p>

<p><strong>Note</strong>: When you click on "Save and continue", there is a chance that you will be redirected back to the same database configuration page.  If this happens, simply refresh the page.  The database will be configured and the profile will be installed.</p>

<p>You will see an info box at the top of the page telling you that it is now appropriate to change the permissions of the settings file.  We will do this momentarily.  For now, you need to set up some basic information about your site.  Fill in the fields using appropriate values for your site:</p>

<p><img src="https://assets.digitalocean.com/articles/drupal_ubuntu_1404/config_site.png" alt="Drupal configure site" /></p>

<p>Click the "Save and Continue" button a final time to complete the installation.  You can now visit your site by going to your domain name:</p>

<p><img src="https://assets.digitalocean.com/articles/drupal_ubuntu_1404/installed_site.png" alt="Drupal completed install" /></p>

<p>You have successfully completed the Drupal installation.</p>

<p>However, we still need to revert the permissions for our settings file so that unauthorized users cannot make changes.  On your server, restrict write access to the file by typing:</p>
<pre class="code-pre "><code class="code-highlight language-bash">chmod 644 /var/www/html/sites/default/settings.php
</code></pre>
<p>This should lock down further changes to the settings file.</p>

<h2 id="troubleshooting">Troubleshooting</h2>

<p>If the final stage of the Drupal installation doesn't complete, check your error logs:</p>
<pre class="code-pre "><code langs="">sudo tail /var/log/apache2/error.log
</code></pre>
<p>If you see an error like this:</p>
<pre class="code-pre "><code langs="">[Wed Nov 12 13:40:10.566144 2014] [:error] [pid 7178] [client 108.29.37.206:55238] PHP Fatal error:  Call to undefined function field_attach_load() in /var/www/html/includes/entity.inc on line 316, referer: http://12.34.56.78/install.php?profile=standard&locale=en
sh: 1: /usr/sbin/sendmail: not found
</code></pre>
<p>This indicates that the installation did not complete successfully. There are quite a few causes and fixes for this error documented by Drupal:</p>

<blockquote>
<p><a href="https://www.drupal.org/node/481758">https://www.drupal.org/node/481758</a></p>
</blockquote>

<p>Some of the most likely fixes include editing the <code>/etc/php5/apache2/php.ini</code> file to raise the <code>max_execution_time</code>:</p>
<pre class="code-pre "><code langs="">sudo nano /etc/php5/apache2/php.ini
</code></pre>
<p>File:</p>
<pre class="code-pre "><code langs="">max_execution_time = <span class="highlight">300</span>
</code></pre>
<p>You may also want to try the browser installation in a browser other than Chrome, such as Safari. Browser cookie settings can interfere with the installation.</p>

<p>Regardless, once you implement your fix, you will have to remove the existing Drupal database and existing <code>/var/www/html/sites/default/settings.php</code> file, replace them with default copies, and start the installation over again. <strong>If you have any data or settings worth preserving, make backups.</strong></p>

<p>To do this, you can log into MySQL and <code>DROP DATABASE drupal;</code> and then follow the previous database section again to create the database and grant the privileges on it.</p>

<p>You can also run <code>cp /var/www/html/sites/default/default.settings.php /var/www/html/sites/default/settings.php</code> again to replace the settings file. Make sure you run the <code>chmod 664 /var/www/html/sites/default/settings.php</code> command to set the correct permissions again as well.</p>

<p>Then visit your IP address again - possibly in a different browser - and attempt the final installation again.</p>

<h2 id="conclusion">Conclusion</h2>

<p>You now have a solid base to build your Drupal site.  Drupal is incredibly flexible, allowing you to customize the look and functionality of the site based on your needs and the needs of your users.</p>

<p>To get some ideas about where to go from here, visit our <a href="https://indiareads/community/tags/explore/drupal">Drupal tags page</a> where you can find tutorials to help you along your way.  You will also find a question and answer area to get help from or contribute to the community.</p>

    