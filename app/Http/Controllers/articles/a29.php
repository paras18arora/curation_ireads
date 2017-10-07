<link rel='stylesheet' href='digital_ocean.css'><br><img src=https://community-cdn-digitalocean-com.global.ssl.fastly.net/assets/tutorials/images/large/wordpress_tw.png?1461950431/> <br> 
      <h3 id="introduction">Introduction</h3>

<p>WordPress is the most popular CMS (content management system) on the internet.  It allows you to easily set up flexible blogs and websites on top of a MySQL backend with PHP processing.  WordPress has seen incredible adoption and is a great choice for getting a website up and running quickly.  After setup, almost all administration can be done through the web frontend.</p>

<p>In this guide, we'll focus on getting a WordPress instance set up on a LEMP stack (Linux, Nginx, MySQL, and PHP) on an Ubuntu 16.04 server.</p>

<h2 id="prerequisites">Prerequisites</h2>

<p>In order to complete this tutorial, you will need access to an Ubuntu 16.04 server.</p>

<p>You will need to perform the following tasks before you can start this guide:</p>

<ul>
<li><strong>Create a <code>sudo</code> user on your server</strong>: We will be completing the steps in this guide using a non-root user with <code>sudo</code> privileges.  You can create a user with <code>sudo</code> privileges by following our <a href="https://indiareads/community/tutorials/initial-server-setup-with-ubuntu-16-04">Ubuntu 16.04 initial server setup guide</a>.</li>
<li><strong>Install a LEMP stack</strong>: WordPress will need a web server, a database, and PHP in order to correctly function.  Setting up a LEMP stack (Linux, Nginx, MySQL, and PHP) fulfills all of these requirements.  Follow <a href="https://indiareads/community/tutorials/how-to-install-linux-nginx-mysql-php-lemp-stack-in-ubuntu-16-04">this guide</a> to install and configure this software.</li>
<li><strong>Secure your site with SSL</strong>: WordPress serves dynamic content and handles user authentication and authorization.  TLS/SSL is the technology that allows you to encrypt the traffic from your site so that your connection is secure.  The way you set up SSL will depend on whether you have a domain name for your site.

<ul>
<li><strong>If you have a domain name...</strong> the easiest way to secure your site is with Let's Encrypt, which provides free, trusted certificates.  Follow our <a href="https://indiareads/community/tutorials/how-to-secure-nginx-with-let-s-encrypt-on-ubuntu-16-04">Let's Encrypt guide for Nginx</a> to set this up.</li>
<li><strong>If you do not have a domain...</strong> and you are just using this configuration for testing or personal use, you can use a self-signed certificate instead.  This provides the same type of encryption, but without the domain validation.  Follow our <a href="https://indiareads/community/tutorials/how-to-create-a-self-signed-ssl-certificate-for-nginx-in-ubuntu-16-04">self-signed SSL guide for Nginx</a> to get set up.</li>
</ul></li>
</ul>

<p>When you are finished the setup steps, log into your server as your <code>sudo</code> user and continue below.</p>

<h2 id="step-1-create-a-mysql-database-and-user-for-wordpress">Step 1: Create a MySQL Database and User for WordPress</h2>

<p>The first step that we will take is a preparatory one.  WordPress uses MySQL to manage and store site and user information.  We have MySQL installed already, but we need to make a database and a user for WordPress to use.</p>

<p>To get started, log into the MySQL root (administrative) account by issuing this command:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">mysql -u root -p
</li></ul></code></pre>
<p>You will be prompted for the password you set for the MySQL root account when you installed the software.</p>

<p>First, we can create a separate database that WordPress can control.  You can call this whatever you would like, but we will be using <code>wordpress</code> in this guide to keep it simple.  You can create the database for WordPress by typing:</p>
<pre class="code-pre custom_prefix"><code langs=""><ul class="prefixed"><li class="line" prefix="mysql>">CREATE DATABASE <span class="highlight">wordpress</span> DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci;
</li></ul></code></pre>
<p></p><div class="code-label notes-and-warnings note" title="Note">Note</div><span class="note">
Every MySQL statement must end in a semi-colon (;).  Check to make sure this is present if you are running into any issues.<br /></span>

<p>Next, we are going to create a separate MySQL user account that we will use exclusively to operate on our new database.  Creating one-function databases and accounts is a good idea from a management and security standpoint.  We will use the name <code>wordpressuser</code> in this guide.  Feel free to change this if you'd like.</p>

<p>We are going to create this account, set a password, and grant access to the database we created.  We can do this by typing the following command.  Remember to choose a strong password here for your database user:</p>
<pre class="code-pre custom_prefix"><code langs=""><ul class="prefixed"><li class="line" prefix="mysql>">GRANT ALL ON <span class="highlight">wordpress</span>.* TO '<span class="highlight">wordpressuser</span>'@'localhost' IDENTIFIED BY '<span class="highlight">password</span>';
</li></ul></code></pre>
<p>You now have a database and user account, each made specifically for WordPress.  We need to flush the privileges so that the current instance of MySQL knows about the recent changes we've made:</p>
<pre class="code-pre custom_prefix"><code langs=""><ul class="prefixed"><li class="line" prefix="mysql>">FLUSH PRIVILEGES;
</li></ul></code></pre>
<p>Exit out of MySQL by typing:</p>
<pre class="code-pre custom_prefix"><code langs=""><ul class="prefixed"><li class="line" prefix="mysql>">EXIT;
</li></ul></code></pre>
<h2 id="step-2-adjust-nginx-39-s-configuration-to-correctly-handle-wordpress">Step 2: Adjust Nginx's Configuration to Correctly Handle WordPress</h2>

<p>Next, we will be making a few minor adjustments to our Nginx server block files.</p>

<p>Open the default server block file with <code>sudo</code> privileges to begin:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo nano /etc/nginx/sites-available/default
</li></ul></code></pre>
<p>Within the main <code>server</code> block, we need to add a few <code>location</code> blocks.</p>

<p>Start by creating exact-matching location blocks for requests to <code>/favicon.ico</code> and <code>/robots.txt</code>, both of which we do not want to log requests for.</p>

<p>We will use a regular expression location to match any requests for static files.  We will again turn off the logging for these requests and will mark them as highly cacheable since these are typically expensive resources to serve.  You can adjust this static files list to contain any other file extensions your site may use:</p>
<div class="code-label " title="/etc/nginx/sites-available/default">/etc/nginx/sites-available/default</div><pre class="code-pre "><code langs="">server {
    . . .

    <span class="highlight">location = /favicon.ico { log_not_found off; access_log off; }</span>
    <span class="highlight">location = /robots.txt { log_not_found off; access_log off; allow all; }</span>
    <span class="highlight">location ~* \.(css|gif|ico|jpeg|jpg|js|png)$ {</span>
        <span class="highlight">expires max;</span>
        <span class="highlight">log_not_found off;</span>
    <span class="highlight">}</span>
    . . .
}
</code></pre>
<p>Inside of the existing <code>location /</code> block, we need to adjust the <code>try_files</code> list so that instead of returning a 404 error as the default option, control is passed to the  <code>index.php</code> file with the request arguments.</p>

<p>This should look something like this:</p>
<div class="code-label " title="/etc/nginx/sites-available/default">/etc/nginx/sites-available/default</div><pre class="code-pre "><code langs="">server {
    . . .
    location / {
        <span class="highlight">#</span>try_files $uri $uri/ =404;
        <span class="highlight">try_files $uri $uri/ /index.php$is_args$args;</span>
    }
    . . .
}
</code></pre>
<p>When you are finished, save and close the file.</p>

<p>Now, we can check our configuration for syntax errors by typing:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo nginx -t
</li></ul></code></pre>
<p>If no errors were reported, reload Nginx by typing:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo systemctl reload nginx
</li></ul></code></pre>
<h2 id="step-3-install-additional-php-extensions">Step 3: Install Additional PHP Extensions</h2>

<p>When setting up our LEMP stack, we only required a very minimal set of extensions in order to get PHP to communicate with MySQL.  WordPress and many of its plugins leverage additional PHP extensions.</p>

<p>We can download and install some of the most popular PHP extensions for use with WordPress by typing:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo apt-get update
</li><li class="line" prefix="$">sudo apt-get install php-curl php-gd php-mbstring php-mcrypt php-xml php-xmlrpc
</li></ul></code></pre>
<p></p><div class="code-label notes-and-warnings note" title="Note">Note</div><span class="note">
Each WordPress plugin has its own set of requirements.  Some may require additional PHP packages to be installed.  Check your plugin documentation to discover its PHP requirements.  If they are available, they can be installed with <code>apt-get</code> as demonstrated above.<br /></span>

<p>When you are finished installing the extensions, restart the PHP-FPM process so that the running PHP processor can leverage the newly installed features:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo systemctl restart php7.0-fpm
</li></ul></code></pre>
<h2 id="step-4-download-wordpress">Step 4: Download WordPress</h2>

<p>Now that our server software is configured, we can download and set up WordPress.  For security reasons in particular, it is always recommended to get the latest version of WordPress from their site.</p>

<p>Change into a writable directory and then download the compressed release by typing:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">cd /tmp
</li><li class="line" prefix="$">curl -O https://wordpress.org/latest.tar.gz
</li></ul></code></pre>
<p>Extract the compressed file to create the WordPress directory structure:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">tar xzvf latest.tar.gz
</li></ul></code></pre>
<p>We will be moving these files into our document root momentarily.  Before we do that, we can copy over the sample configuration file to the filename that WordPress actually reads:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">cp /tmp/wordpress/wp-config-sample.php /tmp/wordpress/wp-config.php
</li></ul></code></pre>
<p>We can also create the <code>upgrade</code> directory, so that WordPress won't run into permissions issues when trying to do this on its own following an update to its software:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">mkdir /tmp/wordpress/wp-content/upgrade
</li></ul></code></pre>
<p>Now, we can copy the entire contents of the directory into our document root.  We are using the <code>-a</code> flag to make sure our permissions are maintained.  We are using a dot at the end of our source directory to indicate that everything within the directory should be copied, including any hidden files:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo cp -a /tmp/wordpress/. /var/www/html
</li></ul></code></pre>
<h2 id="step-5-configure-the-wordpress-directory">Step 5: Configure the WordPress Directory</h2>

<p>Before we do the web-based WordPress setup, we need to adjust some items in our WordPress directory.</p>

<h3 id="adjusting-the-ownership-and-permissions">Adjusting the Ownership and Permissions</h3>

<p>One of the big things we need to accomplish is setting up reasonable file permissions and ownership.  We need to be able to write to these files as a regular user, and we need the web server to also be able to access and adjust certain files and directories in order to function correctly.</p>

<p>We'll start by assigning ownership over all of the files in our document root to our username.  We will use <code>sammy</code> as our username in this guide, but you should change this to match whatever your <code>sudo</code> user is called.  We will assign group ownership to the <code>www-data</code> group:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo chown -R <span class="highlight">sammy</span>:www-data /var/www/html
</li></ul></code></pre>
<p>Next, we will set the <code>setgid</code> bit on each of the directories within the document root.  This causes new files created within these directories to inherit the group of the parent directory (which we just set to <code>www-data</code>) instead of the creating user's primary group.  This just makes sure that whenever we create a file in the directory on the command line, the web server will still have group ownership over it.</p>

<p>We can set the <code>setgid</code> bit on every directory in our WordPress installation by typing:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo find /var/www/html -type d -exec chmod g+s {} \;
</li></ul></code></pre>
<p>There are a few other fine-grained permissions we'll adjust.  First, we'll give group write access to the <code>wp-content</code> directory so that the web interface can make theme and plugin changes:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo chmod g+w /var/www/html/wp-content
</li></ul></code></pre>
<p>As part of this process, we will give the web server write access to all of the content in these two directories:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo chmod -R g+w /var/www/html/wp-content/themes
</li><li class="line" prefix="$">sudo chmod -R g+w /var/www/html/wp-content/plugins
</li></ul></code></pre>
<p>This should be a reasonable permissions set to start with.  Some plugins and procedures might require additional tweaks.</p>

<h3 id="setting-up-the-wordpress-configuration-file">Setting up the WordPress Configuration File</h3>

<p>Now, we need to make some changes to the main WordPress configuration file.</p>

<p>When we open the file, our first order of business will be to adjust some secret keys to provide some security for our installation.  WordPress provides a secure generator for these values so that you do not have to try to come up with good values on your own.  These are only used internally, so it won't hurt usability to have complex, secure values here.</p>

<p>To grab secure values from the WordPress secret key generator, type:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">curl -s https://api.wordpress.org/secret-key/1.1/salt/
</li></ul></code></pre>
<p>You will get back unique values that look something like this:</p>

<p></p><div class="code-label notes-and-warnings warning" title="Warning">Warning</div><span class="warning">
It is important that you request unique values each time.  Do <strong>NOT</strong> copy the values shown below!<br /></span>
<pre class="code-pre "><code langs=""><div class="secondary-code-label " title="Output">Output</div>define('AUTH_KEY',         '1jl/vqfs<XhdXoAPz9 <span class="highlight">DO NOT COPY THESE VALUES</span> c_j{iwqD^<+c9.k<J@4H');
define('SECURE_AUTH_KEY',  'E2N-h2]Dcvp+aS/p7X <span class="highlight">DO NOT COPY THESE VALUES</span> {Ka(f;rv?Pxf})CgLi-3');
define('LOGGED_IN_KEY',    'W(50,{W^,OPB%PB<JF <span class="highlight">DO NOT COPY THESE VALUES</span> 2;y&,2m%3]R6DUth[;88');
define('NONCE_KEY',        'll,4UC)7ua+8<!4VM+ <span class="highlight">DO NOT COPY THESE VALUES</span> #`DXF+[$atzM7 o^-C7g');
define('AUTH_SALT',        'koMrurzOA+|L_lG}kf <span class="highlight">DO NOT COPY THESE VALUES</span>  07VC*Lj*lD&?3w!BT#-');
define('SECURE_AUTH_SALT', 'p32*p,]z%LZ+pAu:VY <span class="highlight">DO NOT COPY THESE VALUES</span> C-?y+K0DK_+F|0h{!_xY');
define('LOGGED_IN_SALT',   'i^/G2W7!-1H2OQ+t$3 <span class="highlight">DO NOT COPY THESE VALUES</span> t6**bRVFSD[Hi])-qS`|');
define('NONCE_SALT',       'Q6]U:K?j4L%Z]}h^q7 <span class="highlight">DO NOT COPY THESE VALUES</span> 1% ^qUswWgn+6&xqHN&%');
</code></pre>
<p>These are configuration lines that we can paste directly in our configuration file to set secure keys.  Copy the output you received now.</p>

<p>Now, open the WordPress configuration file:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">nano /var/www/html/wp-config.php
</li></ul></code></pre>
<p>Find the section that contains the dummy values for those settings.  It will look something like this:</p>
<div class="code-label " title="/var/www/html/wp-config.php">/var/www/html/wp-config.php</div><pre class="code-pre "><code langs="">. . .

define('AUTH_KEY',         'put your unique phrase here');
define('SECURE_AUTH_KEY',  'put your unique phrase here');
define('LOGGED_IN_KEY',    'put your unique phrase here');
define('NONCE_KEY',        'put your unique phrase here');
define('AUTH_SALT',        'put your unique phrase here');
define('SECURE_AUTH_SALT', 'put your unique phrase here');
define('LOGGED_IN_SALT',   'put your unique phrase here');
define('NONCE_SALT',       'put your unique phrase here');

. . .
</code></pre>
<p>Delete those lines and paste in the values you copied from the command line:</p>
<div class="code-label " title="/var/www/html/wp-config.php">/var/www/html/wp-config.php</div><pre class="code-pre "><code langs="">. . .

define('AUTH_KEY',         '<span class="highlight">VALUES COPIED FROM THE COMMAND LINE</span>');
define('SECURE_AUTH_KEY',  '<span class="highlight">VALUES COPIED FROM THE COMMAND LINE</span>');
define('LOGGED_IN_KEY',    '<span class="highlight">VALUES COPIED FROM THE COMMAND LINE</span>');
define('NONCE_KEY',        '<span class="highlight">VALUES COPIED FROM THE COMMAND LINE</span>');
define('AUTH_SALT',        '<span class="highlight">VALUES COPIED FROM THE COMMAND LINE</span>');
define('SECURE_AUTH_SALT', '<span class="highlight">VALUES COPIED FROM THE COMMAND LINE</span>');
define('LOGGED_IN_SALT',   '<span class="highlight">VALUES COPIED FROM THE COMMAND LINE</span>');
define('NONCE_SALT',       '<span class="highlight">VALUES COPIED FROM THE COMMAND LINE</span>');

. . .
</code></pre>
<p>Next, we need to modify some of the database connection settings at the beginning of the file.  You need to adjust the database name, the database user, and the associated password that we configured within MySQL.</p>

<p>The other change we need to make is to set the method that WordPress should use to write to the filesystem.  Since we've given the web server permission to write where it needs to, we can explicitly set the filesystem method to "direct".  Failure to set this with our current settings would result in WordPress prompting for FTP credentials when we perform some actions.</p>

<p>This setting can be added below the database connection settings, or anywhere else in the file:</p>
<div class="code-label " title="/var/www/html/wp-config.php">/var/www/html/wp-config.php</div><pre class="code-pre "><code langs="">. . .

define('DB_NAME', '<span class="highlight">wordpress</span>');

/** MySQL database username */
define('DB_USER', '<span class="highlight">wordpressuser</span>');

/** MySQL database password */
define('DB_PASSWORD', '<span class="highlight">password</span>');

. . .

<span class="highlight">define('FS_METHOD', 'direct');</span>
</code></pre>
<p>Save and close the file when you are finished.</p>

<h2 id="step-6-complete-the-installation-through-the-web-interface">Step 6: Complete the Installation Through the Web Interface</h2>

<p>Now that the server configuration is complete, we can complete the installation through the web interface.</p>

<p>In your web browser, navigate to your server's domain name or public IP address:</p>
<pre class="code-pre "><code langs="">http://<span class="highlight">server_domain_or_IP</span>
</code></pre>
<p>Select the language you would like to use:</p>

<p><img src="https://assets.digitalocean.com/articles/wordpress_lamp_1604/language_selection.png" alt="WordPress language selection" /><br />
<img src="https://assets.digitalocean.com/articles/wordpress_lemp_1604/language_selection.png" alt="WordPress language selection" /></p>

<p>Next, you will come to the main setup page.</p>

<p>Select a name for your WordPress site and choose a username (it is recommended not to choose something like "admin" for security purposes).  A strong password is generated automatically.  Save this password or select an alternative strong password.</p>

<p>Enter your email address and select whether you want to discourage search engines from indexing your site:</p>

<p><img src="https://assets.digitalocean.com/articles/wordpress_lemp_1604/setup_installation.png" alt="WordPress setup installation" /></p>

<p>When you click ahead, you will be taken to a page that prompts you to log in:</p>

<p><img src="https://assets.digitalocean.com/articles/wordpress_lemp_1604/login_prompt.png" alt="WordPress login prompt" /></p>

<p>Once you log in, you will be taken to the WordPress administration dashboard:</p>

<p><img src="https://assets.digitalocean.com/articles/wordpress_lemp_1604/admin_screen.png" alt="WordPress login prompt" /></p>

<h2 id="upgrading-wordpress">Upgrading WordPress</h2>

<p>As WordPress upgrades become available, you will be unable in install them through the interface with the current permissions.</p>

<p>The permissions we selected here are meant to provide a good balance between security and usability for the 99% of times between upgrading.  However, they are a bit too restrictive for the software to automatically apply updates.</p>

<p>When an update becomes available, log back into your server as your <code>sudo</code> user.  Temporarily give the web server process access to the whole document root:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo chown -R www-data /var/www/html
</li></ul></code></pre>
<p>Now, go back the WordPress administration panel and apply the update.</p>

<p>When you are finished, lock the permissions down again for security:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo chown -R <span class="highlight">sammy</span> /var/www/html
</li></ul></code></pre>
<p>This should only be necessary when applying upgrades to WordPress itself.</p>

<h2 id="conclusion">Conclusion</h2>

<p>WordPress should be installed and ready to use!  Some common next steps are to choose the permalinks setting for your posts (can be found in <code>Settings > Permalinks</code>) or to select a new theme (in <code>Appearance > Themes</code>).  If this is your first time using WordPress, explore the interface a bit to get acquainted with your new CMS.</p>

    