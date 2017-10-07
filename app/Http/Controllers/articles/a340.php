<link rel='stylesheet' href='digital_ocean.css'><br><img src=https://community-cdn-digitalocean-com.global.ssl.fastly.net/assets/tutorials/images/large/PhpMyAdmin_Install_twitter_mostov.png?1463769508/> <br> 
      <h3 id="introduction">Introduction</h3>

<p>While many users need the functionality of a database management system like MySQL, they may not feel comfortable interacting with the system solely from the MySQL prompt.</p>

<p><strong>phpMyAdmin</strong> was created so that users can interact with MySQL through a web interface.  In this guide, we'll discuss how to install and secure phpMyAdmin so that you can safely use it to manage your databases from an Ubuntu 16.04 system.</p>

<h2 id="prerequisites">Prerequisites</h2>

<p>Before you get started with this guide, you need to have some basic steps completed.</p>

<p>First, we'll assume that you are using a non-root user with sudo privileges, as described in steps 1-4 in the <a href="https://digitalocean.com/community/articles/initial-server-setup-with-ubuntu-16-04">initial server setup of Ubuntu 16.04</a>.</p>

<p>We're also going to assume that you've completed a LAMP (Linux, Apache, MySQL, and PHP) installation on your Ubuntu 16.04 server.  If this is not completed yet, you can follow this guide on <a href="https://digitalocean.com/community/articles/how-to-install-linux-apache-mysql-php-lamp-stack-on-ubuntu-16-04">installing a LAMP stack on Ubuntu 16.04</a>.</p>

<p>Finally, there are important security considerations when using software like phpMyAdmin, since it:</p>

<ul>
<li>Communicates directly with your MySQL installation</li>
<li>Handles authentication using MySQL credentials</li>
<li>Executes and returns results for arbitrary SQL queries</li>
</ul>

<p>For these reasons, and because it is a widely-deployed PHP application which is frequently targeted for attack, you should never run phpMyAdmin on remote systems over a plain HTTP connection.  If you do not have an existing domain configured with an SSL/TLS certificate, you can follow this guide on <a href="https://indiareads/community/tutorials/how-to-secure-apache-with-let-s-encrypt-on-ubuntu-16-04">securing Apache with Let's Encrypt on Ubuntu 16.04</a>.</p>

<p>Once you are finished with these steps, you're ready to get started with this guide.</p>

<h2 id="step-one-—-install-phpmyadmin">Step One — Install phpMyAdmin</h2>

<p>To get started, we can simply install phpMyAdmin from the default Ubuntu repositories.</p>

<p>We can do this by updating our local package index and then using the <code>apt</code> packaging system to pull down the files and install them on our system:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo apt-get update
</li><li class="line" prefix="$">sudo apt-get install phpmyadmin php-mbstring php-gettext
</li></ul></code></pre>
<p>This will ask you a few questions in order to configure your installation correctly.</p>

<p><span class="warning"><strong>Warning:</strong> When the first prompt appears, apache2 is highlighted, but <strong>not</strong> selected.  If you do not hit <strong>Space</strong> to select Apache, the installer will <em>not</em> move the necessary files during installation.  Hit <strong>Space</strong>, <strong>Tab</strong>, and then <strong>Enter</strong> to select Apache.<br /></span></p>

<ul>
<li>For the server selection, choose <strong>apache2</strong>.</li>
<li>Select <strong>yes</strong> when asked whether to use <code>dbconfig-common</code> to set up the database</li>
<li>You will be prompted for your database administrator's password</li>
<li>You will then be asked to choose and confirm a password for the <code>phpMyAdmin</code> application itself</li>
</ul>

<p>The installation process actually adds the phpMyAdmin Apache configuration file into the <code>/etc/apache2/conf-enabled/</code> directory, where it is automatically read.</p>

<p>The only thing we need to do is explicitly enable the PHP <code>mcrypt</code> and <code>mbstring</code> extensions, which we can do by typing:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo phpenmod mcrypt
</li><li class="line" prefix="$">sudo phpenmod mbstring
</li></ul></code></pre>
<p>Afterwards, you'll need to restart Apache for your changes to be recognized:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo systemctl restart apache2
</li></ul></code></pre>
<p>You can now access the web interface by visiting your server's domain name or public IP address followed by <code>/phpmyadmin</code>:</p>
<pre class="code-pre "><code langs="">https://<span class="highlight">domain_name_or_IP</span>/phpmyadmin
</code></pre>
<p><img src="https://assets.digitalocean.com/articles/phpmyadmin_1604/small_login_screen.png" alt="phpMyAdmin login screen" /></p>

<p>You can now log into the interface using the <code>root</code> username and the administrative password you set up during the MySQL installation.</p>

<p>When you log in, you'll see the user interface, which will look something like this:</p>

<p><img src="https://assets.digitalocean.com/articles/phpmyadmin_1604/small_user_interface.png" alt="phpMyAdmin user interface" /></p>

<h2 id="step-two-—-secure-your-phpmyadmin-instance">Step Two — Secure your phpMyAdmin Instance</h2>

<p>We were able to get our phpMyAdmin interface up and running fairly easily.  However, we are not done yet.  Because of its ubiquity, phpMyAdmin is a popular target for attackers.  We should take extra steps to prevent unauthorized access.</p>

<p>One of the easiest way of doing this is to place a gateway in front of the entire application.  We can do this using Apache's built-in <code>.htaccess</code> authentication and authorization functionalities.</p>

<h3 id="configure-apache-to-allow-htaccess-overrides">Configure Apache to Allow .htaccess Overrides</h3>

<p>First, we need to enable the use of <code>.htaccess</code> file overrides by editing our Apache configuration file.</p>

<p>We will edit the linked file that has been placed in our Apache configuration directory:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo nano /etc/apache2/conf-available/phpmyadmin.conf
</li></ul></code></pre>
<p>We need to add an <code>AllowOverride All</code> directive within the <code><Directory /usr/share/phpmyadmin></code> section of the configuration file, like this:</p>
<div class="code-label " title="/etc/apache2/conf-available/phpmyadmin.conf">/etc/apache2/conf-available/phpmyadmin.conf</div><pre class="code-pre "><code langs=""><Directory /usr/share/phpmyadmin>
    Options FollowSymLinks
    DirectoryIndex index.php
    <span class="highlight">AllowOverride All</span>
    . . .
</code></pre>
<p>When you have added this line, save and close the file.</p>

<p>To implement the changes you made, restart Apache:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo systemctl restart apache2
</li></ul></code></pre>
<h3 id="create-an-htaccess-file">Create an .htaccess File</h3>

<p>Now that we have enabled <code>.htaccess</code> use for our application, we need to create one to actually implement some security.</p>

<p>In order for this to be successful, the file must be created within the application directory.  We can create the necessary file and open it in our text editor with root privileges by typing:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo nano /usr/share/phpmyadmin/.htaccess
</li></ul></code></pre>
<p>Within this file, we need to enter the following information:</p>
<div class="code-label " title="/usr/share/phpmyadmin/.htaccess">/usr/share/phpmyadmin/.htaccess</div><pre class="code-pre "><code langs="">AuthType Basic
AuthName "Restricted Files"
AuthUserFile /etc/phpmyadmin/.htpasswd
Require valid-user
</code></pre>
<p>Let's go over what each of these lines mean:</p>

<ul>
<li><code>AuthType Basic</code>: This line specifies the authentication type that we are implementing.  This type will implement password authentication using a password file.</li>
<li><code>AuthName</code>: This sets the message for the authentication dialog box.  You should keep this generic so that unauthorized users won't gain any information about what is being protected.</li>
<li><code>AuthUserFile</code>: This sets the location of the password file that will be used for authentication.  This should be outside of the directories that are being served.  We will create this file shortly.</li>
<li><code>Require valid-user</code>: This specifies that only authenticated users should be given access to this resource.  This is what actually stops unauthorized users from entering.</li>
</ul>

<p>When you are finished, save and close the file.</p>

<h3 id="create-the-htpasswd-file-for-authentication">Create the .htpasswd file for Authentication</h3>

<p>Now that we have specified a location for our password file through the use of the <code>AuthUserFile</code> directive within our <code>.htaccess</code> file, we need to create this file.</p>

<p>We actually need an additional package to complete this process.  We can install it from our default repositories:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo apt-get install apache2-utils
</li></ul></code></pre>
<p>Afterward, we will have the <code>htpasswd</code> utility available.</p>

<p>The location that we selected for the password file was "<code>/etc/phpmyadmin/.htpasswd</code>".  Let's create this file and pass it an initial user by typing:</p>
<pre class="code-pre "><code langs="">sudo htpasswd -c /etc/phpmyadmin/.htpasswd <span class="highlight">username</span>
</code></pre>
<p>You will be prompted to select and confirm a password for the user you are creating.  Afterwards, the file is created with the hashed password that you entered.</p>

<p>If you want to enter an additional user, you need to do so <strong>without</strong> the <code>-c</code> flag, like this:</p>
<pre class="code-pre "><code langs="">sudo htpasswd /etc/phpmyadmin/.htpasswd <span class="highlight">additionaluser</span>
</code></pre>
<p>Now, when you access your phpMyAdmin subdirectory, you will be prompted for the additional account name and password that you just configured:</p>
<pre class="code-pre "><code langs="">https://<span class="highlight">domain_name_or_IP</span>/phpmyadmin
</code></pre>
<p><img src="https://assets.digitalocean.com/articles/phpmyadmin_1404/apache_auth.png" alt="phpMyAdmin apache password" /></p>

<p>After entering the Apache authentication, you'll be taken to the regular phpMyAdmin authentication page to enter your other credentials.  This will add an additional layer of security since phpMyAdmin has suffered from vulnerabilities in the past.</p>

<h2 id="conclusion">Conclusion</h2>

<p>You should now have phpMyAdmin configured and ready to use on your Ubuntu 16.04 server.  Using this interface, you can easily create databases, users, tables, etc., and perform the usual operations like deleting and modifying structures and data.</p>

    