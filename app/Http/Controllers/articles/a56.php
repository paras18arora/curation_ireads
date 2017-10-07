<link rel='stylesheet' href='digital_ocean.css'><br><img src=https://community-cdn-digitalocean-com.global.ssl.fastly.net/assets/tutorials/images/large/WebApplication.deploying-twitter.png?1436557933/> <br> 
      <h3 id="introduction">Introduction</h3>

<p>In this part of the tutorial, we will deploy our example PHP application, WordPress, and a private DNS:</p>

<p><img src="https://assets.digitalocean.com/articles/architecture/production/lamp/dns_application.png" alt="DNS + Application Diagram" /></p>

<p>Your users will access your application over HTTPS via a domain name, e.g. "https://www.example.com", that points to the load balancer. The load balancer will act as a reverse proxy to the application servers, which will connect to the database server. The private DNS will enable us to use names to refer to the private network addresses of our servers which ease the process of configuration of our servers.</p>

<p>We will set up the components that we just discussed on six servers, in this order:</p>

<ul>
<li>Private DNS (ns1 and ns2)</li>
<li>Database Server (db1)</li>
<li>Application Servers (app1 and app2)</li>
<li>Load Balancer (lb1)</li>
</ul>

<p>Let's get started with the DNS setup.</p>

<h2 id="private-dns-servers">Private DNS Servers</h2>

<p>Using names for addresses helps with identifying the servers you are working with and becomes essential for the maintenance of a larger server setup, as you can replace a server by simply updating your DNS records (in a single place) instead of updating countless configuration files with IP addresses. In our setup, we will set up our DNS so we can reference the private network addresses of our servers by name instead of IP address.</p>

<p>We will refer to the private network address of each server by a hostname under the "nyc3.example.com" subdomain. For example, the database server's private network address would be "db1.nyc3.example.com", which resolves to it's private IP address. Note that the example subdomain is almost completely arbitrary, and is usually chosen based on logical organization purposes; in our case, we "nyc3" indicates that the servers are in the NYC3 datacenter, and "example.com" is our application's domain name.</p>

<p>Set this up by following this tutorial, and adding DNS records for each server in your setup:</p>

<ul>
<li><a href="https://indiareads/community/tutorials/how-to-configure-bind-as-a-private-network-dns-server-on-ubuntu-14-04">How To Configure BIND as a Private Network DNS</a></li>
</ul>

<p>After completing the DNS tutorial, you should have two BIND servers: <strong>ns1</strong> and <strong>ns2</strong>. If you already know the private IP addresses of all of the servers in your setup, add them to your DNS now; otherwise, add the appropriate DNS records as you create your servers.</p>

<p>Now we're ready to set up our database server.</p>

<h2 id="set-up-database-server">Set Up Database Server</h2>

<p>Because we want to load balance the our application servers, i.e. the ones running Apache and PHP, we need to decouple the database from the application servers by setting it up on a separate server. Decoupling the database from the application is an essential step before horizontally scaling many types of applications, as explained in this blog post: <a href="https://indiareads/company/blog/horizontally-scaling-php-applications/">Horizontally Scaling PHP Applications: A Practical Overview</a>.</p>

<p>This section covers all of the necessary steps to set up our database server, but you can learn more about setting up a remote, decoupled MySQL database server for a PHP application in this tutorial: <a href="https://indiareads/community/tutorials/how-to-set-up-a-remote-database-to-optimize-site-performance-with-mysql">How To Set up a Remote MySQL Database</a>.</p>

<h3 id="install-mysql">Install MySQL</h3>

<p>On the database server, <strong>db1</strong>, install MySQL Server:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo apt-get update
</li><li class="line" prefix="$">sudo apt-get -y install mysql-server
</li></ul></code></pre>
<p>Enter your desired MySQL root password at the prompt.</p>

<p>Now run:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo mysql_install_db
</li><li class="line" prefix="$">sudo mysql_secure_installation
</li></ul></code></pre>
<p>You will have to enter the MySQL administrator's password that you set in the steps above. Afterwards, it will ask if you want to change that password. Type "N" for no if you're happy with your current password. Answer the rest of the questions with the defaults.</p>

<h3 id="configure-mysql-to-listen-on-private-network-interface">Configure MySQL to Listen on Private Network Interface</h3>

<p>Open the MySQL configuration file:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo vi /etc/mysql/my.cnf
</li></ul></code></pre>
<p>Find the <code>bind-address</code> setting, and change it to the address of the private network address of your database server:</p>
<div class="code-label " title="/etc/mysql/my.cnf">/etc/mysql/my.cnf</div><pre class="code-pre "><code langs="">bind-address            = <span class="highlight">db1.nyc3.example.com</span>
</code></pre>
<p>Save and exit.</p>

<p>Restart MySQL:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo service mysql restart
</li></ul></code></pre>
<h3 id="set-up-database-and-database-users">Set Up Database and Database Users</h3>

<p>Now we need to create the database and database users that the application servers will use to connect.</p>

<p>Enter the MySQL console:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">mysql -u root -p
</li></ul></code></pre>
<p>Enter the MySQL root password at the prompt.</p>

<p>At the MySQL prompt, create the database for your application:</p>
<pre class="code-pre custom_prefix"><code langs=""><ul class="prefixed"><li class="line" prefix="mysql>">CREATE DATABASE <span class="highlight">app</span>;
</li></ul></code></pre>
<p>MySQL associates its users to the servers that they should be connecting from. In our case, we have two application servers that will be connecting, so we should make a user for each of them. Create a database user, "appuser" in our example, that can be connected to from private network address of each of your application servers (<strong>app1</strong> and <strong>app2</strong>). You should use the same password for each user:</p>
<pre class="code-pre custom_prefix"><code langs=""><ul class="prefixed"><li class="line" prefix="mysql>">CREATE USER '<span class="highlight">appuser</span>'@'<span class="highlight">app1.nyc3.example.com</span>' IDENTIFIED BY '<span class="highlight">password</span>';
</li><li class="line" prefix="mysql>">CREATE USER '<span class="highlight">appuser</span>'@'<span class="highlight">app2.nyc3.example.com</span>' IDENTIFIED BY '<span class="highlight">password</span>';
</li></ul></code></pre>
<p>We will configure the final database user privileges later, but let's give <strong>appuser</strong> full control over the <strong>app</strong> database:</p>
<pre class="code-pre custom_prefix"><code langs=""><ul class="prefixed"><li class="line" prefix="mysql>">GRANT ALL PRIVILEGES ON <span class="highlight">app</span>.* TO '<span class="highlight">appuser</span>'@'<span class="highlight">app1.nyc3.example.com</span>';
</li><li class="line" prefix="mysql>">GRANT ALL PRIVILEGES ON <span class="highlight">app</span>.* TO '<span class="highlight">appuser</span>'@'<span class="highlight">app2.nyc3.example.com</span>';
</li><li class="line" prefix="mysql>">FLUSH PRIVILEGES;
</li></ul></code></pre>
<p>These relaxed privileges ensure that the application's installer will be able to install the application in the database. If you have more than two application servers, you should create all the necessary database users now.</p>

<p>Exit the MySQL prompt now:</p>
<pre class="code-pre custom_prefix"><code langs=""><ul class="prefixed"><li class="line" prefix="mysql>">exit
</li></ul></code></pre>
<p>The database server setup is complete. Let's set up the application servers.</p>

<h2 id="set-up-application-servers">Set Up Application Servers</h2>

<p>The application servers will run the code of our application, which will connect to the database server. Our example application is WordPress, which is a PHP application that is served through a web server such as Apache or Nginx. Because we want to load balance the application servers, we will set up two identical ones.</p>

<p>This section covers all of the necessary steps to set up our application servers, but the topic is covered in detail in the following tutorial, starting from the <strong>Set Up the Web Server</strong> section: <a href="https://indiareads/community/tutorials/how-to-set-up-a-remote-database-to-optimize-site-performance-with-mysql">How To Set Up a Remote Database</a>.</p>

<h3 id="install-apache-and-php">Install Apache and PHP</h3>

<p>On both application servers, <strong>app1</strong> and <strong>app2</strong>, install Apache and PHP:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo apt-get update
</li><li class="line" prefix="$">sudo apt-get -y install apache2 php5-mysql php5 libapache2-mod-php5 php5-mcrypt
</li></ul></code></pre>
<h3 id="configure-apache">Configure Apache</h3>

<p>We will be using HAProxy, on the load balancer server, to handle SSL termination, so we don't want our users accessing the application servers directly. As such, we will bind Apache to each server's private network address.</p>

<p>On each application server, <strong>app1</strong> and <strong>app2</strong>, open your Apache ports configuration file. By default, this is the <code>ports.conf</code> file:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo vi /etc/apache2/ports.conf
</li></ul></code></pre>
<p>Find the line that says <code>Listen 80</code>, and add your private IP address to it, like so (substitute in the actual IP address of your server):</p>
<div class="code-label " title="Apache ports.conf — Listen on private interface">Apache ports.conf — Listen on private interface</div><pre class="code-pre "><code langs="">Listen <span class="highlight">private_IP</span>:80
</code></pre>
<p>Save and exit. This configures Apache to listen only on the private network interface, which means it cannot be accessed by the public IP address or hostname.</p>

<p>Restart Apache to put the changes into effect:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo service apache2 restart
</li></ul></code></pre>
<p>Apache is now accessible via only the private network address of your application servers. We will configure the load balancer to send user requests here, in a moment.</p>

<h3 id="download-and-configure-application">Download and Configure Application</h3>

<p>In our example, we are using WordPress as our application. If you are using a different PHP application, download it and perform any relevant configuration (e.g. database connection information), then skip to the next section.</p>

<p>On the first application server, <strong>app1</strong>, download the WordPress archive:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">cd ~
</li><li class="line" prefix="$">wget http://wordpress.org/latest.tar.gz
</li></ul></code></pre>
<p>Extract the WordPress archive:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">tar xvf latest.tar.gz
</li></ul></code></pre>
<p>Change to the extracted directory:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">cd wordpress
</li></ul></code></pre>
<p>WordPress needs a directory to be created for its uploads, <code>wp-content/uploads</code>. Let's do that now:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">mkdir wp-content/uploads
</li></ul></code></pre>
<p>We will use the sample WordPress configuration file as a template. Copy it to the proper location:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">cp wp-config-sample.php wp-config.php
</li></ul></code></pre>
<p>Now open the configuration file for editing:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">vi wp-config.php
</li></ul></code></pre>
<p>Configure the WordPress database connection by changing the highlighted information in the following lines:</p>
<div class="code-label " title="wp-config.php">wp-config.php</div><pre class="code-pre "><code langs="">/** The name of the database for WordPress */
define('DB_NAME', '<span class="highlight">app</span>');

/** MySQL database username */
define('DB_USER', '<span class="highlight">appuser</span>');

/** MySQL database password */
define('DB_PASSWORD', '<span class="highlight">password</span>');

/** MySQL hostname */
define('DB_HOST', '<span class="highlight">db1.nyc3.example..com</span>');
</code></pre>
<p>Because we are going to use TLS/SSL encryption on the load balancer server, we must add the following lines so WordPress will be aware that it is behind a reverse proxy that is using SSL:</p>
<pre class="code-pre "><code langs="">define('FORCE_SSL_ADMIN', true);
if ($_SERVER['HTTP_X_FORWARDED_PROTO'] == 'https')
       $_SERVER['HTTPS']='on';
</code></pre>
<p>You will also want to update the keys and salts, so you can invalidate cookies when you want. We won't cover this here but make sure that they are identical on all of your application servers.</p>

<p>Save and exit.</p>

<p>WordPress is now configured, but its files must be copied to the proper location to be served by our web server software.</p>

<h3 id="copy-application-files-to-document-root">Copy Application Files to Document Root</h3>

<p>Now that we have our application configured, we need to copy it into Apache's document root, where it can be served to visitors of our website.</p>

<p>The default location of Apache's DocumentRoot is <code>/var/www/html</code>, so we will use that in our example.</p>

<p>First, delete the default <code>index.html</code> file:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo rm /var/www/html/index.html
</li></ul></code></pre>
<p>Then use rsync to copy the WordPress files to <code>/var/www/html</code>, and make <code>www-data</code> (the user that Apache runs as) the owner:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo rsync -avP ~/wordpress/ /var/www/html
</li><li class="line" prefix="$">sudo chgrp -R www-data /var/www/html/*
</li></ul></code></pre>
<p>Our first application server, app1, is ready. We will set up the other application server.</p>

<h2 id="replicate-application-files-to-other-servers">Replicate Application Files to Other Servers</h2>

<p>In order to keep your application's files consistent across your various application servers, you should set up file replication of your web server's document root. In the case of WordPress, using the web interface to upload files and install plugins will store the files on the particular server that processes the request. If these files are not replicated to all of your application servers, some of your users will be served pages with missing images and broken plugins. If your PHP application is not WordPress and does not store any of its data (e.g. uploaded files or downloaded plugins) on the application server, you can just copy the application files manually, once. If this is the case, use rsync to copy your application files from <strong>app1</strong> to <strong>app2</strong>.</p>

<p>GlusterFS can be used to create a replicated volume of the necessary files, and it is demonstrated in the <strong>Synchronize Web Application Files</strong> section of this tutorial: <a href="https://indiareads/community/tutorials/how-to-use-haproxy-as-a-layer-4-load-balancer-for-wordpress-application-servers-on-ubuntu-14-04#synchronize-web-application-files">How To Use HAProxy as a Load Balancer for WordPress Application Servers</a>. Follow the instructions (skip the <em>Edit Hosts File</em> section, as our DNS takes care of that) and set up replication between <strong>app1</strong> and <strong>app2</strong>.</p>

<p>Once your replication is set up properly, both of your application servers should be configured properly. Let's set up our load balancer now.</p>

<h2 id="set-up-load-balancer-server">Set Up Load Balancer Server</h2>

<p>Our load balancer server will run HAProxy, which will serve as a reverse proxy load balancer for our application servers. Your users will access your application through the the load balancer server via a URL such as <code>https://www.example.com</code>.</p>

<p>This section covers all of the necessary steps to set up our load balancer server, but the subject is covered in detail in the following tutorials:</p>

<ul>
<li><a href="https://indiareads/community/tutorials/how-to-use-haproxy-as-a-layer-7-load-balancer-for-wordpress-and-nginx-on-ubuntu-14-04">How To Use HAProxy As A Layer 7 Load Balancer For WordPress and Nginx On Ubuntu 14.04</a></li>
<li><a href="https://indiareads/community/tutorials/how-to-implement-ssl-termination-with-haproxy-on-ubuntu-14-04">How To Implement SSL Termination With HAProxy on Ubuntu 14.04</a>: </li>
</ul>

<h3 id="copy-ssl-certificate">Copy SSL Certificate</h3>

<p>Perform these steps on the load balancer server, <strong>lb1</strong>.</p>

<p>In the directory that contains your SSL certificate (one of the prerequisites from the part 1), combine your certificate, any intermediate CA certificate, and your certificate's key into a single <code>.pem</code> file. For example (our certs are in <code>/root/certs</code>:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">cd /root/certs
</li><li class="line" prefix="$">cat www.example.com.crt CAintermediate.ca-bundle www.example.com.key > www.example.com.pem
</li></ul></code></pre>
<p>Then copy the pem file to <code>/etc/ssl/private</code>:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo cp www.example.com.pem /etc/ssl/private/
</li></ul></code></pre>
<p>This file will be used by HAProxy for SSL termination.</p>

<h3 id="install-haproxy">Install HAProxy</h3>

<p>On the load balancer server, <strong>lb1</strong>, install HAProxy:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo add-apt-repository ppa:vbernat/haproxy-1.5
</li><li class="line" prefix="$">sudo apt-get update
</li><li class="line" prefix="$">sudo apt-get -y install haproxy
</li></ul></code></pre>
<p>Now let's configure HAProxy.</p>

<h3 id="haproxy-configuration">HAProxy Configuration</h3>

<p>We need to configure HAProxy with some reasonable settings, SSL termination, and the appropriate frontends and backends to make it work with our application servers.</p>

<p>Open the HAProxy configuration file for editing:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo vi /etc/haproxy/haproxy.cfg
</li></ul></code></pre>
<h4 id="haproxy-configuration-general-settings">HAProxy Configuration: General Settings</h4>

<p>The first thing you will want to do is set maxconn to a reasonable number. This setting affects how many concurrent connections HAProxy will allow, which can affect QoS and prevent your web servers from crashing from trying to serve too many requests. You will need to play around with it to find what works for your environment. Add the following line (with a value you think is reasonable) to the global section of the configuration:</p>
<div class="code-label " title="haproxy.cfg — maxconn">haproxy.cfg — maxconn</div><pre class="code-pre "><code langs="">   maxconn <span class="highlight">2048</span>
</code></pre>
<p>Add this line, to configure the maximum size of temporary DHE keys that are generated:</p>
<div class="code-label " title="haproxy.cfg — tune.ssl.default-dh-param">haproxy.cfg — tune.ssl.default-dh-param</div><pre class="code-pre "><code langs="">   tune.ssl.default-dh-param 2048
</code></pre>
<p>Next, in the defaults section, add the following lines under the line that says mode http:</p>
<div class="code-label " title="haproxy.cfg ">haproxy.cfg </div><pre class="code-pre "><code langs="">   option forwardfor
   option http-server-close
</code></pre>
<p>If you would like to enable the HAProxy stats page, add the following lines in the defaults section (substitute user and password with secure values):</p>
<div class="code-label " title="haproxy.cfg ">haproxy.cfg </div><pre class="code-pre "><code langs="">   stats enable
   stats uri /<span class="highlight">stats</span>
   stats realm Haproxy\ Statistics
   stats auth <span class="highlight">user</span>:<span class="highlight">password</span>
</code></pre>
<p>This will allow you to look at the HAProxy stats page by going to your domain on /stats (e.g. "https://www.example.com/stats").</p>

<p>Do not close the config file yet! We will add the proxy configuration next.</p>

<h4 id="haproxy-configuration-proxies">HAProxy Configuration: Proxies</h4>

<p>The first thing we want to add is a frontend to handle incoming HTTP connections. At the end of the file, let's add a frontend called www-http:</p>
<pre class="code-pre "><code langs="">frontend www-http
   bind <span class="highlight">www.example.com</span>:80
   reqadd X-Forwarded-Proto:\ http
   default_backend app-backend
</code></pre>
<p>The purpose of this frontend is to accept HTTP connections so they can be redirected to HTTPS.</p>

<p>Now add a frontend to handle the incoming HTTPS connections. Make sure to specify the appropriate <code>pem</code> certificate:</p>
<pre class="code-pre "><code langs="">frontend www-https
   bind <span class="highlight">www.example.com</span>:443 ssl crt /etc/ssl/private/<span class="highlight">www.example.com</span>.pem
   reqadd X-Forwarded-Proto:\ https
   default_backend app-backend
</code></pre>
<p>After you are finished configuring the frontends, continue adding your backend by adding the following lines:</p>
<pre class="code-pre "><code langs="">backend app-backend
   redirect scheme https if !{ ssl_fc }
   server app1 <span class="highlight">app1.nyc3.example.com</span>:80 check
   server app2 <span class="highlight">app2.nyc3.example.com</span>:80 check
</code></pre>
<p>This backend specifies which application servers to send the load balanced traffic to. Also, the <code>redirect scheme https</code> line tells it to redirect HTTP connections to HTTPS.</p>

<p>Now save and exit haproxy.cfg. HAProxy is now ready to be started, but let's enable logging first.</p>

<h3 id="enable-haproxy-logging">Enable HAProxy Logging</h3>

<p>Open the rsyslog configuration file:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo vi /etc/rsyslog.conf
</li></ul></code></pre>
<p>Then find the following lines and uncomment them to enable UDP syslog reception. It should look like the following when you're done:</p>
<div class="code-label " title="/etc/rsyslog.conf">/etc/rsyslog.conf</div><pre class="code-pre "><code langs="">$ModLoad imudp
$UDPServerRun 514
$UDPServerAddress 127.0.0.1
</code></pre>
<p>Now restart rsyslog to enable the new configuration:</p>
<pre class="code-pre "><code langs="">sudo service rsyslog restart
</code></pre>
<p>HAProxy logging is is now enabled! The log file will be created at <code>/var/log/haproxy.log</code> once HAProxy is started.</p>

<h3 id="restart-haproxy">Restart HAProxy</h3>

<p>Restart HAProxy to put the changes into effect:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo service haproxy restart
</li></ul></code></pre>
<p>Our load balancer is now set up.</p>

<p>Now we need to run the application's install script.</p>

<h2 id="install-wordpress">Install WordPress</h2>

<p>We must run the WordPress installation script, which prepares the database for its use, before we can use it.</p>

<p>Open your site in a web browser:</p>
<pre class="code-pre "><code langs=""><div class="secondary-code-label " title="Open in a Web Browser">Open in a Web Browser</div>https://<span class="highlight">www.example.com</span>/wp-admin/install.php
</code></pre>
<p>This will display the WordPress installation screen. Fill out the forms and click the <strong>Install WordPress</strong> button.</p>

<p>After WordPress installs, the application is ready to be used.</p>

<h2 id="conclusion">Conclusion</h2>

<p>The servers that comprise your application are now set up, and your application is ready to be used. You may log in as the admin user, and your users may access the site over HTTPS via the proper domain name.</p>

<p>Be sure to test out your application and make sure that it works as expected before moving on.</p>

<p>Continue to the next tutorial to start working on the recovery plan for your production application setup: <a href="https://indiareads/community/tutorials/building-for-production-web-applications-recovery-planning">Building for Production: Web Applications — Recovery Planning</a>.</p>

    