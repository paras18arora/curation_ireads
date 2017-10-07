<link rel='stylesheet' href='digital_ocean.css'><br> <br> 
      <h3 id="introduction">Introduction</h3>

<p>When setting up a web server, there are often sections of the site that you wish to restrict access to.  Web applications often provide their own authentication and authorization methods, but the web server itself can be used to restrict access if these are inadequate or unavailable.</p>

<p>In this guide, we'll demonstrate how to password protect assets on an Apache web server running on Ubuntu 14.04.</p>

<h2 id="prerequisites">Prerequisites</h2>

<p>To get started, you will need access to an Ubuntu 14.04 server environment.  You will need a non-root user with <code>sudo</code> privileges in order to perform administrative tasks.  To learn how to create such a user, follow our <a href="https://indiareads/community/tutorials/initial-server-setup-with-ubuntu-14-04">Ubuntu 14.04 initial server setup guide</a>.</p>

<h2 id="install-the-apache-utilities-package">Install the Apache Utilities Package</h2>

<p>In order to create the file that will store the passwords needed to access our restricted content, we will use a utility called <code>htpasswd</code>.  This is found in the <code>apache2-utils</code> package within the Ubuntu repositories.</p>

<p>Update the local package cache and install the package by typing this command.  We will take this opportunity to also grab the Apache2 server in case it is not yet installed on the server:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo apt-get update
</li><li class="line" prefix="$">sudo apt-get install apache2 apache2-utils
</li></ul></code></pre>
<h2 id="create-the-password-file">Create the Password File</h2>

<p>We now have access to the <code>htpasswd</code> command.  We can use this to create a password file that Apache can use to authenticate users.  We will create a hidden file for this purpose called <code>.htpasswd</code> within our <code>/etc/apache2</code> configuration directory.</p>

<p>The first time we use this utility, we need to add the <code>-c</code> option to create the specified file.  We specify a username (<code>sammy</code> in this example) at the end of the command to create a new entry within the file:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo htpasswd -c /etc/apache2/.htpasswd <span class="highlight">sammy</span>
</li></ul></code></pre>
<p>You will be asked to supply and confirm a password for the user.</p>

<p>Leave out the <code>-c</code> argument for any additional users you wish to add:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo htpasswd /etc/apache2/.htpasswd <span class="highlight">another_user</span>
</li></ul></code></pre>
<p>If we view the contents of the file, we can see the username and the encrypted password for each record:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">cat /etc/apache2/.htpasswd
</li></ul></code></pre><pre class="code-pre "><code langs=""><div class="secondary-code-label " title="Output">Output</div>sammy:$apr1$lzxsIfXG$tmCvCfb49vpPFwKGVsuYz.
another_user:$apr1$p1E9MeAf$kiAhneUwr.MhAE2kKGYHK.
</code></pre>
<h2 id="configure-apache-password-authentication">Configure Apache Password Authentication</h2>

<p>Now that we have a file with our users and passwords in a format that Apache can read, we need to configure Apache to check this file before serving our protected content.  We can do this in two different ways.</p>

<p>The first option is to edit the Apache configuration and add our password protection to the virtual host file.  This will generally give better performance because it avoids the expense of reading distributed configuration files.  If you have this option, this method is recommended.</p>

<p>If you do not have the ability to modify the virtual host file (or if you are already using <code>.htaccess files for other purposes), you can restrict access using an</code>.htaccess<code>file.  Apache uses</code>.htaccess` files in order to allow certain configuration items to be set within a file in a content directory.  The disadvantage is that Apache has to re-read these files on every request that involves the directory, which can impact performance.</p>

<p>Choose the option that best suits your needs below.</p>

<h3 id="configuring-access-control-within-the-virtual-host-definition">Configuring Access Control within the Virtual Host Definition</h3>

<p>Begin by opening up the virtual host file that you wish to add a restriction to.  For our example, we'll be using the <code>000-default.conf</code> file that holds the default virtual host installed through Ubuntu's apache package:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo nano /etc/apache2/sites-enabled/<span class="highlight">000-default.conf</span>
</li></ul></code></pre>
<p>Inside, with the comments stripped, the file should look similar to this:</p>
<div class="code-label " title="/etc/apache2/sites-enabled/000-default.conf">/etc/apache2/sites-enabled/000-default.conf</div><pre class="code-pre "><code langs=""><VirtualHost *:80>
    ServerAdmin webmaster@localhost
    DocumentRoot /var/www/html
    ErrorLog ${APACHE_LOG_DIR}/error.log
    CustomLog ${APACHE_LOG_DIR}/access.log combined
</VirtualHost>
</code></pre>
<p>Authentication is done on a per-directory basis.  To set up authentication, you will need to target the directory you wish to restrict with a <code><Directory ___></code> block.  In our example, we'll restrict the entire document root, but you can modify this listing to only target a specific directory within the web space:</p>
<div class="code-label " title="/etc/apache2/sites-enabled/000-default.conf">/etc/apache2/sites-enabled/000-default.conf</div><pre class="code-pre "><code langs=""><VirtualHost *:80>
    ServerAdmin webmaster@localhost
    DocumentRoot /var/www/html
    ErrorLog ${APACHE_LOG_DIR}/error.log
    CustomLog ${APACHE_LOG_DIR}/access.log combined

    <span class="highlight"><Directory "/var/www/html"></span>
    <span class="highlight"></Directory></span>
</VirtualHost>
</code></pre>
<p>Within this directory block, specify that we wish to set up <code>Basic</code> authentication.  For the <code>AuthName</code>, choose a realm name that will be displayed to the user when prompting for credentials.  Use the <code>AuthUserFile</code> directive to point Apache to the password file we created.  Finally, we will require a <code>valid-user</code> to access this resource, which means anyone who can verify their identity with a password will be allowed in:</p>
<div class="code-label " title="/etc/apache2/sites-enabled/000-default.conf">/etc/apache2/sites-enabled/000-default.conf</div><pre class="code-pre "><code langs=""><VirtualHost *:80>
    ServerAdmin webmaster@localhost
    DocumentRoot /var/www/html
    ErrorLog ${APACHE_LOG_DIR}/error.log
    CustomLog ${APACHE_LOG_DIR}/access.log combined

    <Directory "/var/www/html">
        <span class="highlight">AuthType Basic</span>
        <span class="highlight">AuthName "Restricted Content"</span>
        <span class="highlight">AuthUserFile /etc/apache2/.htpasswd</span>
        <span class="highlight">Require valid-user</span>
    </Directory>
</VirtualHost>
</code></pre>
<p>Save and close the file when you are finished.  Restart Apache to implement your password policy:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo service apache2 restart
</li></ul></code></pre>
<p>The directory you specified should now be password protected.</p>

<h3 id="configuring-access-control-with-htaccess-files">Configuring Access Control with .htaccess Files</h3>

<p>If you wish to set up password protection using <code>.htaccess</code> files instead, you should begin by editing the main Apache configuration file to allow <code>.htaccess</code> files:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo nano /etc/apache2/apache2.conf
</li></ul></code></pre>
<p>Find the <code><Directory></code> block for the <code>/var/www</code> directory that holds the document root.  Turn on <code>.htaccess</code> processing by changing the <code>AllowOverride</code> directive within that block from "None" to "All":</p>
<div class="code-label " title="/etc/apache2/apache2.conf">/etc/apache2/apache2.conf</div><pre class="code-pre "><code langs="">. . .

<Directory /var/www/>
    Options Indexes FollowSymLinks
    AllowOverride <span class="highlight">All</span>
    Require all granted
</Directory>

. . .
</code></pre>
<p>Save and close the file when you are finished.</p>

<p>Next, we need to add an <code>.htaccess</code> file to the directory we wish to restrict.  In our demonstration, we'll restrict the entire document root (the entire website) which is based at <code>/var/www/html</code>, but you can place this file in any directory you wish to restrict access to:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo nano /var/www/html/.htaccess
</li></ul></code></pre>
<p>Within this file, specify that we wish to set up <code>Basic</code> authentication.  For the <code>AuthName</code>, choose a realm name that will be displayed to the user when prompting for credentials.  Use the <code>AuthUserFile</code> directive to point Apache to the password file we created.  Finally, we will require a <code>valid-user</code> to access this resource, which means anyone who can verify their identity with a password will be allowed in:</p>
<div class="code-label " title="/var/www/html/.htaccess">/var/www/html/.htaccess</div><pre class="code-pre "><code langs="">AuthType Basic
AuthName "Restricted Content"
AuthUserFile /etc/apache2/.htpasswd
Require valid-user
</code></pre>
<p>Save and close the file.  Restart the web server to password protect all content in or below the directory with the <code>.htaccess</code> file:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo service apache2 restart
</li></ul></code></pre>
<h2 id="confirm-the-password-authentication">Confirm the Password Authentication</h2>

<p>To confirm that your content is protected, try to access your restricted content in a web browser.  You should be presented with a username and password prompt that looks like this:</p>

<p><img src="https://assets.digitalocean.com/articles/apache_password_1404/password_prompt.png" alt="Apache2 password prompt" /></p>

<p>If you enter the correct credentials, you will be allowed to access the content.  If you enter the wrong credentials or hit "Cancel", you will see the "Unauthorized" error page:</p>

<p><img src="https://assets.digitalocean.com/articles/apache_password_1404/unauthorized_error.png" alt="Apache2 unauthorized error" /></p>

<h2 id="conclusion">Conclusion</h2>

<p>You should now have everything you need to set up basic authentication for your site.  Keep in mind that password protection should be combined with SSL encryption so that your credentials are not sent to the server in plain text.  To learn how to create a self-signed SSL certificate to use with Apache, follow <a href="https://indiareads/community/tutorials/how-to-create-a-ssl-certificate-on-apache-for-ubuntu-14-04">this guide</a>.  To learn how to install a commercial certificate, follow <a href="https://indiareads/community/tutorials/how-to-install-an-ssl-certificate-from-a-commercial-certificate-authority">this guide</a>.</p>

    