<link rel='stylesheet' href='digital_ocean.css'><br><img src=https://community-cdn-digitalocean-com.global.ssl.fastly.net/assets/tutorials/images/large/wordpress_freeBSD_tw.png?1426699792/> <br> 
      <h3 id="introduction">Introduction</h3>

<p>WordPress is a popular open source content management system (CMS) that can be used to easily set up a blog. It is a very flexible system, through its plugin and template support, that allows users to extend its functionality to meet their specific needs; WordPress can be customized to support anything from a basic blog to a fully-featured eCommerce site.</p>

<p>In this tutorial, we will show you how to set up WordPress with an Apache web server on FreeBSD 10.1.</p>

<h2 id="prerequisites">Prerequisites</h2>

<p>Before you begin this guide, you must have a FAMP (FreeBSD, Apache, MySQL, and PHP) stack server setup. This WordPress installation tutorial is based on this FAMP tutorial: <a href="https://indiareads/community/tutorials/how-to-install-an-apache-mysql-and-php-famp-stack-on-freebsd-10-1">How To Install an Apache, MySQL, and PHP (FAMP) Stack on FreeBSD 10.1</a>.</p>

<p>This tutorial assumes that you want to serve WordPress from the root of your web site, e.g. <code>http://example.com/</code>, and that your Apache document root is empty (aside from the default <code>index.html</code> file).</p>

<p>If you do not already have a FAMP setup, follow the linked guide before continuing with this tutorial. Note that this tutorial, like the linked FAMP guide, uses PHP 5.6.</p>

<h2 id="step-one-—-install-additional-php-modules">Step One — Install Additional PHP Modules</h2>

<p>Although you already have PHP 5.6 installed, WordPress requires additional PHP modules in order to function properly. We will use <code>pkg</code> to install these required PHP modules.</p>

<p>At the command prompt of your server, use this command to install all of the required PHP 5.6 modules:</p>
<pre class="code-pre "><code langs="">sudo pkg install php56-mysql \
 php56-xml \
 php56-hash \
 php56-gd \
 php56-curl \
 php56-tokenizer \
 php56-zlib \
 php56-zip
</code></pre>
<p>Each of these modules allows WordPress to use various functions in order to perform certain tasks. For example, <code>php56-gd</code> provides libraries for image handling, and <code>php56-curl</code> allows WordPress to download files from external servers for tasks such as plugin updates. Also note that if you followed the prerequisite FAMP tutorial, you should have already installed <code>php56-mysql</code>, which allows WordPress to interact with a MySQL database.</p>

<h2 id="step-two-—-prepare-mysql-database">Step Two — Prepare MySQL Database</h2>

<p>WordPress uses a relational database, such as MySQL, to manage and store site and user information. In this step, we will prepare a MySQL database and user for WordPress to use.</p>

<p>Log into the MySQL administrative account, <code>root</code>, by issuing this command:</p>
<pre class="code-pre "><code langs="">mysql -u root -p
</code></pre>
<p>You will be prompted for the password that you set for the MySQL root account when you first installed MySQL. After providing the password, you will enter the <strong>MySQL command prompt</strong>.</p>

<p>We will now create the MySQL database that WordPress will use to store its data. You can call this whatever you like, but we will call ours <code>wordpress</code> for our example. At the MySQL prompt, enter this SQL statement to create the database:</p>
<pre class="code-pre "><code langs="">CREATE DATABASE <span class="highlight">wordpress</span>;
</code></pre>
<p>Note that every MySQL statement must end in a semi-colon (<code>;</code>) before it will execute.</p>

<p>Next, we are going to create a MySQL user account that WordPress will use to interact with the database that we just created. For our example, we will call the new user <code>wordpressuser</code> with a password of <code>password</code>. You should definitely change the password to something more secure, and you can use a different user name if you wish. This SQL statement will create our example user:</p>
<pre class="code-pre "><code langs="">CREATE USER <span class="highlight">wordpressuser</span>@localhost IDENTIFIED BY '<span class="highlight">password</span>';
</code></pre>
<p>At this point, you have the MySQL database and user that WordPress will use. However, we must grant the user access to the database. To do this, we will use this SQL statement:</p>
<pre class="code-pre "><code langs="">GRANT ALL PRIVILEGES ON <span class="highlight">wordpress</span>.* TO <span class="highlight">wordpressuser</span>@localhost;
</code></pre>
<p>Before this change in privileges will go into effect, we must flush the privileges with this SQL statement:</p>
<pre class="code-pre "><code langs="">FLUSH PRIVILEGES;
</code></pre>
<p>Now exit the MySQL prompt:</p>
<pre class="code-pre "><code langs="">exit
</code></pre>
<p>The MySQL database and user are now ready for use with a new WordPress installation. Let's download WordPress now.</p>

<h2 id="step-three-—-download-wordpress">Step Three — Download Wordpress</h2>

<p>Now we must download the WordPress files from the project's website.</p>

<p>The archive of the latest stable release of WordPress is always available from the same URL. Download it to your home directory with the following commands:</p>
<pre class="code-pre "><code langs="">cd ~
fetch http://wordpress.org/latest.tar.gz
</code></pre>
<p>Now extract the archive with this command:</p>
<pre class="code-pre "><code langs="">tar xvf latest.tar.gz
</code></pre>
<p>This extracts the contents of the archive to a directory called <code>wordpress</code>, in your home directory.</p>

<p>If you wish, you may delete the WordPress archive now:</p>
<pre class="code-pre "><code langs="">rm latest.tar.gz
</code></pre>
<h2 id="step-four-—-configure-wordpress">Step Four — Configure WordPress</h2>

<p>Before making WordPress accessible via our web server, we must configure it so that it will able to connect to the database that we created earlier.</p>

<p>First, change to the <code>wordpress</code> directory:</p>
<pre class="code-pre "><code langs="">cd ~/wordpress
</code></pre>
<p>To make the configuration simple, let's base our WordPress configuration on the provided sample configuration, <code>wp-config-sample.php</code>. Copy the sample to <code>wp-config.php</code>, the default WordPress configuration file:</p>
<pre class="code-pre "><code langs="">cp wp-config-sample.php wp-config.php
</code></pre>
<p>Now open the configuration file in an editor. We will use <code>vi</code> for this purpose, but feel free to use your editor of choice:</p>
<pre class="code-pre "><code langs="">vi wp-config.php
</code></pre>
<p>The only modifications we need to make are to the MySQL settings. We must update the values of the following parameters:</p>

<ul>
<li><code>DB_NAME</code></li>
<li><code>DB_USER</code></li>
<li><code>DB_PASSWORD</code></li>
</ul>

<p>These correspond to the MySQL database and user that we prepared in an earlier step. Look for the following lines and update the highlighted parts with your database name, user, and password:</p>
<pre class="code-pre "><code langs="">// ** MySQL settings - You can get this info from your web host ** //
/** The name of the database for WordPress */
define('DB_NAME', '<span class="highlight">wordpress</span>');

/** MySQL database username */
define('DB_USER', '<span class="highlight">wordpressuser</span>');

/** MySQL database password */
define('DB_PASSWORD', '<span class="highlight">password</span>');
</code></pre>
<p>Save and exit.</p>

<h2 id="step-five-—-copy-files-to-apache-document-root">Step Five — Copy Files to Apache Document Root</h2>

<p>Now that your WordPress application is configured to connect to your database, we must copy it to Apache's <code>DocumentRoot</code> directory, where it can be served to your site's visitors.</p>

<p>If you followed the prerequisite FAMP tutorial, Apache's document root will be located at <code>/usr/local/www/apache24/data</code>—if your document root is located somewhere else, be sure to update the highlighted path in the commands in this section.</p>

<p>Let's copy the WordPress files to Apache's document root with the <code>cp</code> command:</p>
<pre class="code-pre "><code langs="">sudo cp -rp ~/wordpress/* <span class="highlight">/usr/local/www/apache24/data</span>/
</code></pre>
<p>Now change the ownership of the WordPress files to the <code>www</code> user and group, which is the name of the user that runs the Apache process, so Apache will have appropriate access:</p>
<pre class="code-pre "><code langs="">sudo chown -R www:www <span class="highlight">/usr/local/www/apache24/data</span>/*
</code></pre>
<p>Now that the WordPress files are being served by Apache, you are almost ready to start using WordPress.</p>

<h2 id="step-six-—-run-wordpress-installation-script">Step Six — Run WordPress Installation Script</h2>

<p>The next step is to run the WordPress installation script. The script will ask you a few questions about your WordPress site, then initialize the database.</p>

<p>In your web browser, navigate to your server's domain name or public IP address. For example, we will use "example.com" here:</p>
<pre class="code-pre "><code langs="">http://<span class="highlight">example.com</span>
</code></pre>
<p>The first time you visit your WordPress site, you will be prompted by a Language Select screen. Select your preferred language, and click the <strong>Continue</strong> button:</p>

<p><img src="https://assets.digitalocean.com/articles/freebsd_wordpress/language_select.png" alt="Language Select" /></p>

<p>Next, you will see the WordPress installation page, where you will choose a <em>Site Title</em>, and set an administrative username and password, among a few other things:</p>

<p><img src="https://assets.digitalocean.com/articles/freebsd_wordpress/install.png" alt="Install WordPress" /></p>

<p>Fill out the site information. Once you are finished, click the <strong>Install WordPress</strong> button.</p>

<p>WordPress will confirm the installation, and then ask you to log in with the account you just created:</p>

<p><img src="https://assets.digitalocean.com/articles/freebsd_wordpress/installation_complete.png" alt="Installation Complete" /></p>

<p>Click the <strong>Log In</strong> button at the bottom of the screen, then enter your login (the one that you just created):</p>

<p><img src="https://assets.digitalocean.com/articles/freebsd_wordpress/login.png" alt="Log In" /></p>

<p>Now click the <strong>Log In</strong> button to log in to the Administrative <em>Dashboard</em> of your WordPress site:</p>

<p><img src="https://assets.digitalocean.com/articles/freebsd_wordpress/dashboard.png" alt="Dashboard" /></p>

<p>Congratulations! Your WordPress site is up and running. Continue reading if you want to set up pretty permalinks.</p>

<h2 id="step-seven-optional-—-configure-permalinks">Step Seven (Optional) — Configure Permalinks</h2>

<p>By default, WordPress creates new posts with URLs that reference the post ID. For example, the second post you make would have a URL that looks something like this:</p>
<pre class="code-pre "><code langs="">http://<span class="highlight">example.com</span>/?p=2
</code></pre>
<p>WordPress has the ability to create "pretty" permalinks which will rewrite the URL to a more human-readable format. For example, you could set WordPress to use a URL that corresponds to the title of your post, like this:</p>
<pre class="code-pre "><code langs="">http://<span class="highlight">example.com</span>/my-second-post/
</code></pre>
<p>To change your permalink settings, we must reconfigure Apache then our WordPress settings.</p>

<h3 id="configure-apache-to-allow-url-rewrites">Configure Apache to Allow URL Rewrites</h3>

<p>First, we need to enable the Apache <em>rewrite</em> module.</p>

<p>Open the Apache configuration file for editing. We are assuming that this file is located at <code>/usr/local/etc/apache24/httpd.conf</code>:</p>
<pre class="code-pre "><code langs="">sudo vi /usr/local/etc/apache24/httpd.conf
</code></pre>
<p>Find and uncomment the <code>#LoadModule rewrite_module ...</code> line, by deleting the <code>#</code>, so it look like this:</p>
<pre class="code-pre "><code langs="">LoadModule rewrite_module libexec/apache24/mod_rewrite.so
</code></pre>
<p>Now, we need to modify the Apache configuration to allow WordPress the ability to perform <code>.htaccess</code> overrides.</p>

<p>Find the <code><Directory "/usr/local/www/apache24/data"></code> section, then find the <code>AllowOverride None</code> directive within it. Set <code>AllowOverride</code> to <code>All</code>, so it looks like this:</p>
<pre class="code-pre "><code langs="">    AllowOverride <span class="highlight">All</span>
</code></pre>
<p>Save and exit.</p>

<p>Now restart Apache to put the changes into effect:</p>
<pre class="code-pre "><code langs="">sudo service apache24 restart
</code></pre>
<p>Now Apache is configured to allow URL rewrites but we must create an <code>.htaccess</code> file that WordPress will use to reconfigure the permalink settings.</p>

<h3 id="create-an-htaccess-file">Create an .htaccess File</h3>

<p>Now that Apache is configured to allow rewrites through <code>.htaccess</code> files, we need to create the actual file that WordPress will write its permalink rules to.</p>

<p>Change to your document root path. Assuming that your document root is located at <code>/usr/local/www/apache24/data</code>, use this command:</p>
<pre class="code-pre "><code langs="">cd /usr/local/www/apache24/data
</code></pre>
<p>Create the <code>.htaccess</code> file in your document root:</p>
<pre class="code-pre "><code langs="">sudo touch .htaccess
</code></pre>
<p>Now change the ownership of the file to the <code>www</code> user and group, so WordPress will have permission to write to the file:</p>
<pre class="code-pre "><code langs="">sudo chown www:www .htaccess
</code></pre>
<p>Now we can use the WordPress dashboard to reconfigure the permalink settings.</p>

<h3 id="change-permalink-settings-in-wordpress">Change Permalink Settings in WordPress</h3>

<p>When you are finished doing the server-side changes, you can easily adjust the permalink settings through the WordPress administration interface (dashboard). This is accessible via the <code>/wp-admin</code> link, for example:</p>
<pre class="code-pre "><code langs="">http://<span class="highlight">example.com</span>/wp-admin/
</code></pre>
<p>On the left-hand side, under the <strong>Settings</strong> menu, click the  <strong>Permalinks</strong> link:</p>

<p><img src="https://assets.digitalocean.com/articles/freebsd_wordpress/permalinks_link.png" alt="Permalinks link" /></p>

<p>You can choose any of the premade permalink settings, or you can create your own:</p>

<p><img src="https://assets.digitalocean.com/articles/freebsd_wordpress/permalink_settings.png" alt="Permalink Settings" /></p>

<p>When you have made your selection, click the <strong>Save Changes</strong> button at the bottom of the page. This will generate the rewrite rules, and write them to the <code>.htaccess</code> file that you created earlier.</p>

<p>You should see a message like this:</p>

<p><img src="https://assets.digitalocean.com/articles/freebsd_wordpress/permalinks_updated.png" alt="Permalink structure updated" /></p>

<p>Your new permalink settings should be working now. Test it out by visiting one of your posts, and observing the URL.</p>

<h2 id="conclusion">Conclusion</h2>

<p>You should now have a WordPress instance with Apache up and running on your FreeBSD 10.1 cloud server.</p>

    