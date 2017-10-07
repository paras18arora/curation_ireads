<link rel='stylesheet' href='digital_ocean.css'><br> <br> 
      <h3 id="introduction">Introduction</h3>

<p>PHP is a server side scripting language used by many popular CMS and blog platforms like WordPress and Drupal. It is also part of the popular LAMP and LEMP stacks. Updating the PHP configuration settings is a common task when setting up a PHP-based website. Locating the exact PHP configuration file may not be easy. There are multiple installations of PHP running normally on a server, and each one has its own configuration file. Knowing which file to edit and what the current settings are can be a bit of a mystery.</p>

<p>This guide will show how to view the current PHP configuration settings of your web server and how to make updates to the PHP settings.</p>

<h2 id="prerequisites">Prerequisites</h2>

<p>For this guide, you need the following:</p>

<ul>
<li>Ubuntu 14.04 Droplet</li>
<li>A non-root user with sudo privileges (<a href="https://indiareads/community/tutorials/initial-server-setup-with-ubuntu-14-04">Initial Server Setup with Ubuntu 14.04</a> explains how to set this up).</li>
<li>An understanding of editing files on a Linux system. The <a href="https://indiareads/community/tutorials/basic-linux-navigation-and-file-management#editing-files">Basic Linux Navigation and File Management</a> tutorial explains how to edit files.</li>
<li>A web server with PHP installed.</li>
</ul>

<p>There are many web server configurations with PHP, but here are two common methods:</p>

<ul>
<li><a href="https://indiareads/community/tutorials/how-to-install-linux-apache-mysql-php-lamp-stack-on-ubuntu-14-04">How To Install a LAMP stack on Ubuntu 14.04</a></li>
<li><a href="https://indiareads/features/one-click-apps/">One-Click Install LAMP on Ubuntu 14.04 with IndiaReads</a></li>
</ul>

<p>This tutorial is applicable to these IndiaReads One-click Apps as well:</p>

<ul>
<li>LAMP</li>
<li>LEMP</li>
<li><a href="https://indiareads/community/tutorials/one-click-install-wordpress-on-ubuntu-14-04-with-digitalocean">WordPress</a></li>
<li><a href="https://indiareads/community/tutorials/how-to-use-the-phpmyadmin-one-click-application-image">PHPMyAdmin</a></li>
<li><a href="https://indiareads/community/tutorials/how-to-use-the-magento-one-click-install-image">Magento</a></li>
<li><a href="https://indiareads/community/tutorials/how-to-use-the-digitalocean-joomla-one-click-application">Joomla</a></li>
<li><a href="https://indiareads/community/tutorials/how-to-use-the-digitalocean-one-click-drupal-image">Drupal</a></li>
<li><a href="https://indiareads/community/tutorials/how-to-use-the-mediawiki-one-click-application-image">Mediawiki</a></li>
<li><a href="https://indiareads/community/tutorials/how-to-use-the-owncloud-one-click-install-application">ownCloud</a></li>
</ul>

<p><span class="note"><strong>Note:</strong> This tutorial assumes you are running Ubuntu 14.04. Editing the <code>php.ini</code> file should be the same on other systems, but the file locations might be different.<br /></span></p>

<p>All the commands in this tutorial should be run as a non-root user. If root access is required for the command, it will be preceded by <code>sudo</code>.</p>

<h2 id="reviewing-the-php-configuration">Reviewing the PHP Configuration</h2>

<p>You can review the live PHP configuration by placing a page with a <code>phpinfo</code> function along with your website files.</p>

<p>To create a file with this command, first change into the directory that contains your website files. For example, the default directory for webpage files for Apache on Ubuntu 14.04 is <code>/var/www/html/</code>:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">cd /var/www/html
</li></ul></code></pre>
<p>Then, create the <code>info.php</code> file:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo nano /var/www/html/info.php
</li></ul></code></pre>
<p>Paste the following lines into this file and save it:</p>
<div class="code-label " title="info.php">info.php</div><pre class="code-pre "><code langs=""><?php
phpinfo();
?>
</code></pre>
<p><span class="note"><strong>Note:</strong> Some IndiaReads One-click Apps have an <code>info.php</code> file placed in the web root automatically.<br /></span></p>

<p>When visiting the <code>info.php</code> file on your web server (http://<span class="highlight">www.example.com</span>/info.php) you will see a page that displays details on the PHP environment, OS version, paths, and values of configuration settings. The file to the right of the <strong>Loaded Configuration File</strong> line shows the proper file to edit in order to update your PHP settings.</p>

<p><img src="https://assets.digitalocean.com/articles/php_edit/phpinfo.png" alt="PHP Info Page" /></p>

<p>This page can be used to reveal the current settings your web server is using. For example, using the <em>Find</em> function of your web browser, you can search for the settings named <strong>post_max_size</strong> and <strong>upload_max_filesize</strong> to see the current settings that restrict file upload sizes.</p>

<p><span class="warning"><strong>Warning:</strong> Since the <code>info.php</code> file displays version details of the OS, Web Server, and PHP, this file should be removed when it is not needed to keep the server as secure as possible.<br /></span></p>

<h2 id="modifying-the-php-configuration">Modifying the PHP Configuration</h2>

<p>The <code>php.ini</code> file can be edited to change the settings and configuration of how PHP functions. This section gives a few common examples.</p>

<p>Sometimes a PHP application might need to allow for larger upload files such as uploading themes and plugins on a WordPress site.  To allow larger uploads for your PHP application, edit the <code>php.ini</code> file with the following command (<em>Change the path and file to match your Loaded Configuration File. This example shows the path for Apache on Ubuntu 14.04.</em>):</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo nano /etc/php5/apache2/php.ini
</li></ul></code></pre>
<p>The default lines that control the file size upload are:</p>
<div class="code-label " title="php.ini">php.ini</div><pre class="code-pre "><code langs="">post_max_size = 8M
upload_max_filesize = 2M
</code></pre>
<p>Change these default values to your desired maximum file upload size. For example, if you needed to upload a 30MB file you would changes these lines to:</p>
<div class="code-label " title="php.ini">php.ini</div><pre class="code-pre "><code langs="">post_max_size = <span class="highlight">30M</span>
upload_max_filesize = <span class="highlight">30M</span>
</code></pre>
<p>Other common resource settings include the amount of memory PHP can use as set by <code>memory_limit</code>:</p>
<div class="code-label " title="php.ini">php.ini</div><pre class="code-pre "><code langs="">memory_limit = 128M
</code></pre>
<p>or <code>max_execution_time</code>, which defines how many seconds a PHP process can run for:</p>
<div class="code-label " title="php.ini">php.ini</div><pre class="code-pre "><code langs="">max_execution_time = 30
</code></pre>
<p>When you have the <code>php.ini</code> file configured for your needs, save the changes, and exit the text editor.</p>

<p>Restart the web server to enable the changes. For Apache on Ubuntu 14.04, this command will restart the web server:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo service apache2 restart
</li></ul></code></pre>
<p>Refreshing the <code>info.php</code> page should now show your updated settings. Remember to remove the <code>info.php</code> when you are done changing your PHP configuration.</p>

<h2 id="conclusion">Conclusion</h2>

<p>Many PHP-based applications require slight changes to the PHP configuration. By using the <code>phpinfo</code> function, the exact PHP configuration file and settings are easy to find. Use the method described in this article to make these changes.</p>

    