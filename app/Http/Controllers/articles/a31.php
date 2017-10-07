<link rel='stylesheet' href='digital_ocean.css'><br><img src=https://community-cdn-digitalocean-com.global.ssl.fastly.net/assets/tutorials/images/large/Install_LEMP-twitter.png?1461607909/> <br> 
      <h3 id="introduction">Introduction</h3>

<p>The LEMP software stack is a group of software that can be used to serve dynamic web pages and web applications.  This is an acronym that describes a Linux operating system, with an Nginx web server.  The backend data is stored in the MySQL database and the dynamic processing is handled by PHP.</p>

<p>In this guide, we will demonstrate how to install a LEMP stack on an Ubuntu 16.04 server.  The Ubuntu operating system takes care of the first requirement.  We will describe how to get the rest of the components up and running.</p>

<h2 id="prerequisites">Prerequisites</h2>

<p>Before you complete this tutorial, you should have a regular, non-root user account on your server with <code>sudo</code> privileges.  You can learn how to set up this type of account by completing our <a href="https://indiareads/community/tutorials/initial-server-setup-with-ubuntu-16-04">Ubuntu 16.04 initial server setup</a>.</p>

<p>Once you have your user available, sign into your server with that username.  You are now ready to begin the steps outlined in this guide.</p>

<h2 id="step-1-install-the-nginx-web-server">Step 1: Install the Nginx Web Server</h2>

<p>In order to display web pages to our site visitors, we are going to employ Nginx, a modern, efficient web server.</p>

<p>All of the software we will be using for this procedure will come directly from Ubuntu's default package repositories.  This means we can use the <code>apt</code> package management suite to complete the installation.</p>

<p>Since this is our first time using <code>apt</code> for this session, we should start off by updating our local package index.  We can then install the server:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo apt-get update
</li><li class="line" prefix="$">sudo apt-get install nginx
</li></ul></code></pre>
<p>On Ubuntu 16.04, Nginx is configured to start running upon installation.</p>

<p>If you are have the <code>ufw</code> firewall running, as outlined in our initial setup guide, you will need to allow connections to Nginx.  Nginx registers itself with <code>ufw</code> upon installation, so the procedure is rather straight forward.</p>

<p>It is recommended that you enable the most restrictive profile that will still allow the traffic you want.  Since we haven't configured SSL for our server yet, in this guide, we will only need to allow traffic on port 80.</p>

<p>You can enable this by typing:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo ufw allow 'Nginx HTTP'
</li></ul></code></pre>
<p>You can verify the change by typing:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo ufw status
</li></ul></code></pre>
<p>You should see HTTP traffic allowed in the displayed output:</p>
<pre class="code-pre "><code langs=""><div class="secondary-code-label " title="Output">Output</div>Status: active

To                         Action      From
--                         ------      ----
OpenSSH                    ALLOW       Anywhere                  
Nginx HTTP                 ALLOW       Anywhere                  
OpenSSH (v6)               ALLOW       Anywhere (v6)             
Nginx HTTP (v6)            ALLOW       Anywhere (v6)
</code></pre>
<p>With the new firewall rule added, you can test if the server is up and running by accessing your server's domain name or public IP address in your web browser.</p>

<p>If you do not have a domain name pointed at your server and you do not know your server's public IP address, you can find it by typing one of the following into your terminal:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">ip addr show eth0 | grep inet | awk '{ print $2; }' | sed 's/\/.*$//'
</li></ul></code></pre>
<p>This will print out a few IP addresses.  You can try each of them in turn in your web browser.</p>

<p>As an alternative, you can check which IP address is accessible as viewed from other locations on the internet:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">curl -4 icanhazip.com
</li></ul></code></pre>
<p>Type one of the addresses that you receive in your web browser.  It should take you to Nginx's default landing page:</p>
<pre class="code-pre "><code langs="">http://<span class="highlight">server_domain_or_IP</span>
</code></pre>
<p><img src="https://assets.digitalocean.com/articles/lemp_ubuntu_1604/nginx_default.png" alt="Nginx default page" /></p>

<p>If you see the above page, you have successfully installed Nginx.</p>

<h2 id="step-2-install-mysql-to-manage-site-data">Step 2: Install MySQL to Manage Site Data</h2>

<p>Now that we have a web server, we need to install MySQL, a database management system, to store and manage the data for our site.</p>

<p>You can install this easily by typing:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo apt-get install mysql-server
</li></ul></code></pre>
<p>You will be asked to supply a root (administrative) password for use within the MySQL system.</p>

<p>The MySQL database software is now installed, but its configuration is not exactly complete yet. </p>

<p>To secure the installation, we can run a simple security script that will ask whether we want to modify some insecure defaults.  Begin the script by typing:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo mysql_secure_installation
</li></ul></code></pre>
<p>You will be asked to enter the password you set for the MySQL root account.  Next, you will be asked if you want to configure the <code>VALIDATE PASSWORD PLUGIN</code>.</p>

<p><span class="warning"><strong>Warning:</strong> Enabling this feature is something of a judgment call.  If enabled, passwords which don't match the specified criteria will be rejected by MySQL with an error.  This will cause issues if you use a weak password in conjunction with software which automatically configures MySQL user credentials, such as the Ubuntu packages for phpMyAdmin.  It is safe to leave validation disabled, but you should always use strong, unique passwords for database credentials.<br /></span></p>

<p>Answer <strong>y</strong> for yes, or anything else to continue without enabling.</p>
<pre class="code-pre "><code langs="">VALIDATE PASSWORD PLUGIN can be used to test passwords
and improve security. It checks the strength of password
and allows the users to set only those passwords which are
secure enough. Would you like to setup VALIDATE PASSWORD plugin?

Press y|Y for Yes, any other key for No:
</code></pre>
<p>If you've enabled validation, you'll be asked to select a level of password validation.  Keep in mind that if you enter <strong>2</strong>, for the strongest level, you will receive errors when attempting to set any password which does not contain numbers, upper and lowercase letters, and special characters, or which is based on common dictionary words.</p>
<pre class="code-pre "><code langs="">There are three levels of password validation policy:

LOW    Length >= 8
MEDIUM Length >= 8, numeric, mixed case, and special characters
STRONG Length >= 8, numeric, mixed case, special characters and dictionary                  file

Please enter 0 = LOW, 1 = MEDIUM and 2 = STRONG: <span class="highlight">1</span>
</code></pre>
<p>If you enabled password validation, you'll be shown a password strength for the existing root password, and asked you if you want to change that password.  If you are happy with your current password, enter <strong>n</strong> for "no" at the prompt:</p>
<pre class="code-pre "><code langs="">Using existing password for root.

Estimated strength of the password: <span class="highlight">100</span>
Change the password for root ? ((Press y|Y for Yes, any other key for No) : <span class="highlight">n</span>
</code></pre>
<p>For the rest of the questions, you should press <strong>Y</strong> and hit the <strong>Enter</strong> key at each prompt.  This will remove some anonymous users and the test database, disable remote root logins, and load these new rules so that MySQL immediately respects the changes we have made.</p>

<p>At this point, your database system is now set up and we can move on.</p>

<h2 id="step-3-install-php-for-processing">Step 3: Install PHP for Processing</h2>

<p>We now have Nginx installed to serve our pages and MySQL installed to store and manage our data.  However, we still don't have anything that can generate dynamic content.  We can use PHP for this.</p>

<p>Since Nginx does not contain native PHP processing like some other web servers, we will need to install <code>php-fpm</code>, which stands for "fastCGI process manager".  We will tell Nginx to pass PHP requests to this software for processing.</p>

<p>We can install this module and will also grab an additional helper package that will allow PHP to communicate with our database backend.  The installation will pull in the necessary PHP core files.  Do this by typing:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo apt-get install php-fpm php-mysql
</li></ul></code></pre>
<h3 id="configure-the-php-processor">Configure the PHP Processor</h3>

<p>We now have our PHP components installed, but we need to make a slight configuration change to make our setup more secure.</p>

<p>Open the main <code>php-fpm</code> configuration file with root privileges:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo nano /etc/php/7.0/fpm/php.ini
</li></ul></code></pre>
<p>What we are looking for in this file is the parameter that sets <code>cgi.fix_pathinfo</code>.  This will be commented out with a semi-colon (;) and set to "1" by default.</p>

<p>This is an extremely insecure setting because it tells PHP to attempt to execute the closest file it can find if the requested PHP file cannot be found.  This basically would allow users to craft PHP requests in a way that would allow them to execute scripts that they shouldn't be allowed to execute.</p>

<p>We will change both of these conditions by uncommenting the line and setting it to "0" like this:</p>
<div class="code-label " title="/etc/php/7.0/fpm/php.ini">/etc/php/7.0/fpm/php.ini</div><pre class="code-pre "><code langs="">cgi.fix_pathinfo=0
</code></pre>
<p>Save and close the file when you are finished.</p>

<p>Now, we just need to restart our PHP processor by typing:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo systemctl restart php7.0-fpm
</li></ul></code></pre>
<p>This will implement the change that we made.</p>

<h2 id="step-4-configure-nginx-to-use-the-php-processor">Step 4: Configure Nginx to Use the PHP Processor</h2>

<p>Now, we have all of the required components installed.  The only configuration change we still need is to tell Nginx to use our PHP processor for dynamic content.</p>

<p>We do this on the server block level (server blocks are similar to Apache's virtual hosts).  Open the default Nginx server block configuration file by typing:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo nano /etc/nginx/sites-available/default
</li></ul></code></pre>
<p>Currently, with the comments removed, the Nginx default server block file looks like this:</p>
<div class="code-label " title="/etc/nginx/sites-available/default">/etc/nginx/sites-available/default</div><pre class="code-pre "><code langs="">server {
    listen 80 default_server;
    listen [::]:80 default_server;

    root /var/www/html;
    index index.html index.htm index.nginx-debian.html;

    server_name _;

    location / {
        try_files $uri $uri/ =404;
    }
}
</code></pre>
<p>We need to make some changes to this file for our site.</p>

<ul>
<li>First, we need to add <code>index.php</code> as the first value of our <code>index</code> directive so that files named <code>index.php</code> are served, if available, when a directory is requested.</li>
<li>We can modify the <code>server_name</code> directive to point to our server's domain name or public IP address.</li>
<li>For the actual PHP processing, we just need to uncomment a segment of the file that handles PHP requests.  This will be the <code>location ~\.php$</code> location block, the included <code>fastcgi-php.conf</code> snippet, and the socket associated with <code>php-fpm</code>.</li>
<li>We will also uncomment the location block dealing with <code>.htaccess</code> files.  Nginx doesn't process these files.  If any of these files happen to find their way into the document root, they should not be served to visitors.</li>
</ul>

<p>The changes that you need to make are in red in the text below:</p>
<div class="code-label " title="/etc/nginx/sites-available/default">/etc/nginx/sites-available/default</div><pre class="code-pre "><code langs="">server {
    listen 80 default_server;
    listen [::]:80 default_server;

    root /var/www/html;
    index <span class="highlight">index.php</span> index.html index.htm index.nginx-debian.html;

    server_name <span class="highlight">server_domain_or_IP</span>;

    location / {
        try_files $uri $uri/ =404;
    }

    <span class="highlight">location ~ \.php$ {</span>
        <span class="highlight">include snippets/fastcgi-php.conf;</span>
        <span class="highlight">fastcgi_pass unix:/run/php/php7.0-fpm.sock;</span>
    <span class="highlight">}</span>

    <span class="highlight">location ~ /\.ht {</span>
        <span class="highlight">deny all;</span>
    <span class="highlight">}</span>
}
</code></pre>
<p>When you've made the above changes, you can save and close the file.</p>

<p>Test your configuration file for syntax errors by typing:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo nginx -t
</li></ul></code></pre>
<p>If any errors are reported, go back and recheck your file before continuing.</p>

<p>When you are ready, reload Nginx to make the necessary changes:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo systemctl reload nginx
</li></ul></code></pre>
<h2 id="step-5-create-a-php-file-to-test-configuration">Step 5: Create a PHP File to Test Configuration</h2>

<p>Your LEMP stack should now be completely set up.  We can test it to validate that Nginx can correctly hand <code>.php</code> files off to our PHP processor.</p>

<p>We can do this by creating a test PHP file in our document root.  Open a new file called <code>info.php</code> within your document root in your text editor:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo nano /var/www/html/info.php
</li></ul></code></pre>
<p>Type or paste the following lines into the new file.  This is valid PHP code that will return information about our server:</p>
<div class="code-label " title="/var/www/html/info.php">/var/www/html/info.php</div><pre class="code-pre "><code langs=""><?php
phpinfo();
</code></pre>
<p>When you are finished, save and close the file.</p>

<p>Now, you can visit this page in your web browser by visiting your server's domain name or public IP address followed by <code>/info.php</code>:</p>
<pre class="code-pre "><code langs="">http://<span class="highlight">server_domain_or_IP</span>/info.php
</code></pre>
<p>You should see a web page that has been generated by PHP with information about your server:</p>

<p><img src="https://assets.digitalocean.com/articles/lemp_ubuntu_1604/php_info.png" alt="PHP page info" /></p>

<p>If you see a page that looks like this, you've set up PHP processing with Nginx successfully.</p>

<p>After verifying that Nginx renders the page correctly, it's best to remove the file you created as it can actually give unauthorized users some hints about your configuration that may help them try to break in.  You can always regenerate this file if you need it later.</p>

<p>For now, remove the file by typing:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo rm /var/www/html/info.php
</li></ul></code></pre>
<h2 id="conclusion">Conclusion</h2>

<p>You should now have a LEMP stack configured on your Ubuntu 16.04 server.  This gives you a very flexible foundation for serving web content to your visitors.</p>

    