<link rel='stylesheet' href='digital_ocean.css'><br><img src=https://community-cdn-digitalocean-com.global.ssl.fastly.net/assets/tutorials/images/large/wp_openlightspped_tw__1_.jpg?1430322968/> <br> 
      <h3 id="introduction">Introduction</h3>

<p>WordPress is currently the most popular content management system (CMS) in the world.  It allows you to easily set up flexible blogs and websites on top of a database backend, using PHP to execute scripts and process dynamic content.  WordPress has a large online community for support and is a great way to get websites up and running quickly.</p>

<p>In this guide, we will focus on how to get a WordPress instance set up and running on Ubuntu 14.04 using the OpenLiteSpeed web server.</p>

<h2 id="prerequisites">Prerequisites</h2>

<p>Before you begin this guide, there are some important steps that you must complete to prepare your server.</p>

<p>We will be running through the steps in this guide using a non-root user with <code>sudo</code> privileges.  To learn how to set up a user of this type, follow our <a href="https://indiareads/community/articles/initial-server-setup-with-ubuntu-14-04">initial server setup guide for Ubuntu 14.04</a>.</p>

<p>This guide will not cover how to install OpenLiteSpeed or MySQL.  You can learn how to install and configure these components by following our guide on <a href="https://indiareads/community/tutorials/how-to-install-the-openlitespeed-web-server-on-ubuntu-14-04">installing OpenLiteSpeed on Ubuntu 14.04</a>.  This will also cover the MySQL installation.</p>

<p>Finally, if you are using a server with less than 1 Gigabyte of RAM, you should create and enable a swap file.  Learn how to do this by running through <a href="https://indiareads/community/tutorials/how-to-add-swap-on-ubuntu-14-04">this guide</a>.</p>

<p>When you are finished preparing your server using the guides linked to above, you can proceed with this article.</p>

<h2 id="create-a-database-and-database-user-for-wordpress">Create a Database and Database User for WordPress</h2>

<p>We will start by creating a database and database user for WordPress to use.</p>

<p>Start a MySQL session by using the <code>root</code> MySQL username:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">mysql -u root -p
</li></ul></code></pre>
<p>You will be prompted to enter the MySQL administrative password that you selected during installation.  Afterwards, you will be dropped into a MySQL prompt.</p>

<p>First, create a database for our application.  To keep things simple, we'll call our database <code>wordpress</code> in this guide, but you can use whatever name you'd like:</p>
<pre class="code-pre custom_prefix"><code langs=""><ul class="prefixed"><li class="line" prefix="mysql>">CREATE DATABASE wordpress;
</li></ul></code></pre>
<p>Next, we'll create a database user and grant it access to manage the database that we just created.  We will call this user <code>wordpressuser</code>, but again, feel free to choose a different name.  Replace <code>password</code> in the command below with a strong password for your user:</p>
<pre class="code-pre custom_prefix"><code langs=""><ul class="prefixed"><li class="line" prefix="mysql>">GRANT ALL ON wordpress.* TO wordpressuser@localhost IDENTIFIED BY '<span class="highlight">password</span>';
</li></ul></code></pre>
<p>Flush the changes you've made to make them available to the current MySQL process:</p>
<pre class="code-pre custom_prefix"><code langs=""><ul class="prefixed"><li class="line" prefix="mysql>">FLUSH PRIVILEGES;
</li></ul></code></pre>
<p>Now, exit out of the MySQL prompt to get back to your regular shell:</p>
<pre class="code-pre custom_prefix"><code langs=""><ul class="prefixed"><li class="line" prefix="mysql>">exit
</li></ul></code></pre>
<h2 id="configure-and-compile-php-for-wordpress">Configure and Compile PHP for WordPress</h2>

<p>With our database configured, we can go ahead and shift our focus to configuring PHP.  A customized version of PHP is included with the OpenLiteSpeed installation.  However, the included version does not have the PHP extensions we need compiled in.  Due to the way that the interpreter ties into the OpenLiteSpeed web server, we will need to recompile a custom version of PHP with the modules we need.</p>

<p>Before starting the configuration and compilation process, we need to download and install some libraries that our extensions will need from the Ubuntu repositories.  Update your local package index file and download the libraries by typing:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo apt-get update
</li><li class="line" prefix="$">sudo apt-get install libgd-dev libmcrypt-dev libcurl4-openssl-dev
</li></ul></code></pre>
<p>Once the components are installed, visit the OpenLiteSpeed admin interface by navigating to your server's domain or IP address and port <code>:7080</code> in your web browser:</p>
<pre class="code-pre "><code langs="">https://<span class="highlight">server_domain_or_IP</span>:7080
</code></pre>
<p>If prompted, log in using the username and password you configured for OpenLiteSpeed in the installation tutorial.</p>

<p>To get started, select "Compile PHP" from the "Actions" menu in the top menu bar:</p>

<p><img src="https://assets.digitalocean.com/articles/openlitespeed_wp_1404/compile_php.png" alt="OpenLiteSpeed compile PHP" /></p>

<p>You will be presented with a menu where you can select the version of PHP you'd like to compile:</p>

<p><img src="https://assets.digitalocean.com/articles/openlitespeed_wp_1404/select_php_version.png" alt="OpenLiteSpeed select PHP version" /></p>

<p>Select the most recent version out of the "PHP 5" selection menu.  This should be already be preselected, but it doesn't hurt to check.  Click on the "Next" button in that row to continue.</p>

<p>On the next page, you will be able to select the compilation options for PHP:</p>

<p><img src="https://assets.digitalocean.com/articles/openlitespeed_wp_1404/select_php_options.png" alt="OpenLiteSpeed select PHP options" /></p>

<p>In the "Configure Parameters" section, we need to add some additional flags.  Specifically, we need to add the <code>--with-mysql</code> and <code>--with-curl</code> parameters.  If your server uses the <code>x64</code> architecture, you will also need to add the <code>--with-libdir=lib64</code> option. The complete list of options will look like this:</p>
<pre class="code-pre "><code langs="">--with-mysqli --with-zlib --with-gd --enable-shmop --enable-sockets --enable-sysvsem --enable-sysvshm --enable-mbstring --with-iconv --with-mcrypt <span class="highlight">--with-mysql --with-curl --with-libdir=lib64</span>
</code></pre>
<p>Click on the "Build PHP [version number]" button at the bottom when you are finished.</p>

<p>You will be taken to a screen where the PHP build will be prepared:</p>

<p><img src="https://assets.digitalocean.com/articles/openlitespeed_wp_1404/prepare_php_build.png" alt="OpenLiteSpeed prepare PHP build" /></p>

<p>If the preparation completes successfully, click on the "Next" button to continue to the compilation process.</p>

<p>A PHP build script has been generated using the options that you have selected.  However, OpenLiteSpeed will not actually run the build script automatically for security reasons:</p>

<p><img src="https://assets.digitalocean.com/articles/openlitespeed_wp_1404/manual_php_build.png" alt="OpenLiteSpeed must manually build PHP" /></p>

<p>To run the script, you must go back to your terminal and run a command.  Keep your browser page open so that you can see the output of the build process.  In your terminal, type:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo /usr/local/lsws/phpbuild/buildphp_manual_run.sh
</li></ul></code></pre>
<p>The compilation process can take quite a long time (anywhere from 5 to 20 minutes).  When it completes, it will automatically link to the correct location so that it will be enabled on the next restart.  Before we restart though, we will configure some aspects of our virtual host to prepare for WordPress.</p>

<h2 id="configure-the-virtual-host-for-wordpress">Configure the Virtual Host for WordPress</h2>

<p>We will be modifying the default virtual host that is already present in the OpenLiteSpeed configuration so that we can use it for our WordPress installation.</p>

<p>To begin, in the admin interface, select "Virtual Hosts" from the "Configuration" item in the menu bar:</p>

<p><img src="https://assets.digitalocean.com/articles/openlitespeed_wp_1404/virtual_host_config.png" alt="OpenLiteSpeed virtual host config" /></p>

<p>On the "Example" virtual host, click the "View/Edit" link:</p>

<p><img src="https://assets.digitalocean.com/articles/openlitespeed_wp_1404/edit_virtual_host.png" alt="OpenLiteSpeed edit virtual host" /></p>

<p>This will allow you to edit the configuration of your virtual host.</p>

<h3 id="allow-index-php-processing">Allow index.php Processing</h3>

<p>To start, we will enable <code>index.php</code> files so that they can be used to process requests that aren't handled by static files.  This will allow the main logic of WordPress to function correctly.</p>

<p>Start by clicking on the "General" tab for the virtual host and then clicking the "Edit" button for the "Index Files" table:</p>

<p><img src="https://assets.digitalocean.com/articles/openlitespeed_wp_1404/edit_index_files.png" alt="OpenLiteSpeed edit index files" /></p>

<p>In the field for valid "Index Files", add <code>index.php</code> before <code>index.html</code> to allow PHP index files to take precedence:</p>

<p><img src="https://assets.digitalocean.com/articles/openlitespeed_wp_1404/add_index_php.png" alt="OpenLiteSpeed add index.php" /></p>

<p>Click "Save" when you are finished.</p>

<h3 id="configure-wordpress-rewrites-to-enable-permalink-support">Configure WordPress Rewrites to Enable Permalink Support</h3>

<p>Next, we will set up the rewrite instructions so that we can use permalinks within our WordPress installation.</p>

<p>To do so, click on the "Rewrite" tab for the virtual host.  In the next screen, click on the "Edit" button for the "Rewrite Control" table:</p>

<p><img src="https://assets.digitalocean.com/articles/openlitespeed_wp_1404/enable_rewrites.png" alt="OpenLiteSpeed enable rewrites" /></p>

<p>Select "Yes" under the "Enable Rewrite" option:</p>

<p><img src="https://assets.digitalocean.com/articles/openlitespeed_wp_1404/rewrite_select_yes.png" alt="OpenLiteSpeed rewrite select yes" /></p>

<p>Click "Save" to go back to the main rewrite menu.  Click on the "Edit" button for the "Rewrite Rules" table:</p>

<p><img src="https://assets.digitalocean.com/articles/openlitespeed_wp_1404/rewrite_rules.png" alt="OpenLiteSpeed rewrite rules" /></p>

<p>Remove the rules that are already present and add the following rules to enable rewrites for WordPress:</p>
<pre class="code-pre "><code class="code-highlight language-apache">RewriteRule ^/index\.php$ - [L]
RewriteCond %{REQUEST_FILENAME} !-f
RewriteCond %{REQUEST_FILENAME} !-d
RewriteRule . /index.php [L]
</code></pre>
<p>Click on the "Save" button to implement your new rewrite rules.</p>

<h3 id="remove-unused-password-protection">Remove Unused Password Protection</h3>

<p>The default virtual host that is included with the OpenLiteSpeed installation includes some password protected areas to showcase OpenLiteSpeed's user authentication features.  WordPress includes its own authentication mechanisms and we will not be using the file-based authentication included in OpenLiteSpeed.  We should get rid of these in order to minimize the stray configuration fragments active on our WordPress installation.</p>

<p>First, click on the "Security" tab, and then click the "Delete" link next to "SampleProtectedArea" within the "Realms List" table:</p>

<p><img src="https://assets.digitalocean.com/articles/openlitespeed_wp_1404/security_realm_list.png" alt="OpenLiteSpeed security realm list" /></p>

<p>You will be asked to confirm the deletion.  Click "Yes" to proceed:</p>

<p><img src="https://assets.digitalocean.com/articles/openlitespeed_wp_1404/confirm_realm_deletion.png" alt="OpenLiteSpeed confirm realm deletion" /></p>

<p>Next, click on the "Context" tab.  In the "Context List", delete the <code>/protected/</code> context that was associated with the security realm you just deleted:</p>

<p><img src="https://assets.digitalocean.com/articles/openlitespeed_wp_1404/delete_protected_context.png" alt="OpenLiteSpeed delete protected context" /></p>

<p>Again, you will have to confirm the deletion by clicking "Yes".</p>

<p>You can safely delete any or all of the other contexts as well using the same technique.  We will not be needing them.  We specifically delete the <code>/protected/</code> context because otherwise, an error would be produced due to the deletion of its associated security realm (which we just removed in the "Security" tab).</p>

<h3 id="restart-the-server-to-implement-the-changes">Restart the Server to Implement the Changes</h3>

<p>With all of the above configuration out of the way, we can now gracefully restart the OpenLiteSpeed server to enable our changes.</p>

<p>Go to the "Actions" item in the main menu bar and select "Graceful Restart":</p>

<p><img src="https://assets.digitalocean.com/articles/openlitespeed_wp_1404/graceful_restart.png" alt="OpenLiteSpeed graceful restart" /></p>

<p>Once the server has restarted, click on the "Home" link in the menu bar.  Any errors that have occurred will be printed at the bottom of this page.  If you see errors, click on "Actions" and then "Server Log Viewer" to get more information.</p>

<h2 id="prepare-the-virtual-host-and-document-root-directories">Prepare the Virtual Host and Document Root Directories</h2>

<p>The last thing that we need to do before installing and configuring WordPress is clean up our virtual host and document root directories.  As we said in the last section, the default site has some extraneous pieces that we won't be using for our WordPress site.</p>

<p>Start by moving into the virtual host root directory:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">cd /usr/local/lsws/DEFAULT
</li></ul></code></pre>
<p>If you deleted all of the entries in the "Contexts" tab in the last section, you can get rid of the <code>cgi-bin</code> and <code>fsci-bin</code> directories entirely:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo rm -rf cgi-bin fcgi-bin
</li></ul></code></pre>
<p>If you have left these contexts enabled, you should at least remove any scripts currently present in these directories by typing:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo rm cgi-bin/* fcgi-bin/*
</li></ul></code></pre>
<p>You may see a warning about not being able to remove <code>fastcgi-bin/*</code>.  This will happen if there was nothing present in that directory and is completely normal.</p>

<p>Next, we should remove the password and group files that previously protected our "/protected/" context.  Do this by typing:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo rm conf/ht*
</li></ul></code></pre>
<p>Finally, we should clear out the present contents of our document root directory.  You can do that by typing:</p>
<pre class="code-pre "><code langs="">sudo rm -rf html/*
</code></pre>
<p>We now have a clean place to transfer our WordPress files.</p>

<h2 id="install-and-configure-wordpress">Install and Configure WordPress</h2>

<p>We are now ready to download and install WordPress.  Move to your home directory and download the latest version of WordPress by typing:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">cd ~
</li><li class="line" prefix="$">wget https://wordpress.org/latest.tar.gz
</li></ul></code></pre>
<p>Extract the archive and enter the directory by typing:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">tar xzvf latest.tar.gz
</li><li class="line" prefix="$">cd wordpress
</li></ul></code></pre>
<p>We can copy the sample WordPress configuration file to <code>wp-config.php</code>, the file that WordPress actually reads and processes.  This is where we will put our database connection details:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">cp wp-config-sample.php wp-config.php
</li></ul></code></pre>
<p>Open the configuration file so that we can add our database credentials:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">nano wp-config.php
</li></ul></code></pre>
<p>We need to find the settings for <code>DB_NAME</code>, <code>DB_USER</code>, and <code>DB_PASSWORD</code> so that WordPress can authenticate and utilized the database that we set up for it.</p>

<p>Fill in the values of these parameters with the information for the database you created.  It should look something like this:</p>
<pre class="code-pre "><code class="code-highlight language-php">// ** MySQL settings - You can get this info from your web host ** //
/** The name of the database for WordPress */
define('DB_NAME', '<span class="highlight">wordpress</span>');

/** MySQL database username */
define('DB_USER', '<span class="highlight">wordpressuser</span>');

/** MySQL database password */
define('DB_PASSWORD', '<span class="highlight">password</span>');
</code></pre>
<p>Save and close the file when you are finished.</p>

<p>Now, we are ready to copy the files into our document root.  To do this, type:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo cp -r ~/wordpress/* /usr/local/lsws/DEFAULT/html/
</li></ul></code></pre>
<p>Give permission of the entire directory structure to the user that the web server runs under so that changes can be made through the WordPress interface:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo chown -R nobody:nogroup /usr/local/lsws/DEFAULT/html
</li></ul></code></pre>
<h2 id="finishing-the-installation-through-the-wordpress-interface">Finishing the Installation Through the WordPress Interface</h2>

<p>With the files installed, we can access our WordPress installation by going to our server's domain name or IP address.  If you changed the port for the default site to port 80 during the OpenLiteSpeed installation in the prerequisite guide, you can access the site directly:</p>
<pre class="code-pre "><code langs="">http://<span class="highlight">server_domain_or_IP</span>
</code></pre>
<p>If you have not switched to port 80, you will have to add <code>:8088</code> to the end of your address.  Consider switching to port 80 when launching your site using the instructions in the last guide:</p>
<pre class="code-pre "><code langs="">http://<span class="highlight">server_domain_or_IP</span>:8088
</code></pre>
<p>You should see the first screen of the WordPress installation interface, asking you to select a language:</p>

<p><img src="https://assets.digitalocean.com/articles/openlitespeed_wp_1404/wp_lang_selection.png" alt="WordPress select language" /></p>

<p>Make your selection and click "Continue".</p>

<p>On the next page, you will need to fill in some information about the site you are creating.  This will include the site title, an administrative username and password, the admin email account to set, as well as a decision as to whether to prohibit web crawlers:</p>

<p><img src="https://assets.digitalocean.com/articles/openlitespeed_wp_1404/wp_setup.png" alt="WordPress setup page" /></p>

<p>After the installation, you will have to login using the account you just created.  Once authenticated, you will be taken to the WordPress admin dashboard, allowing you to configure your site:</p>

<p><img src="https://assets.digitalocean.com/articles/openlitespeed_wp_1404/wp_admin_dashboard.png" alt="WordPress admin dashboard" /></p>

<p>Your WordPress installation should now be complete.</p>

<h2 id="conclusion">Conclusion</h2>

<p>In this guide, we've installed and configured a WordPress instance on Ubuntu 14.04 using the OpenLiteSpeed web server.  This configuration is ideal for many users because both WordPress and the web server itself can mainly be administered through a web browser.  This can make administration and modifications easier for those who do not always have access to an SSH session or who may not feel comfortable managing a web server completely from the command line.</p>

    