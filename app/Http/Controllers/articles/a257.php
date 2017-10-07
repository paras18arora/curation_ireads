<link rel='stylesheet' href='digital_ocean.css'><br> <br> 
      <h3 id="introduction">Introduction</h3>

<p>In this tutorial, we will teach you how to use HAProxy as a layer 4 load balancer for your WordPress servers--specifically the web application tier. Load balancing the application servers adds redundancy to your setup, which increases reliability in case of server failures or networking issues, and spreads the load across multiple servers for increased read performance. We are assuming that your setup includes a WordPress application server that connects to a separate MySQL database server (see the prerequisites for a tutorial on how to set that up).</p>

<p>Layer 4 load balancing is suitable for your site if you are only running a single web server application. If your environment is more complex (e.g. you want to run WordPress and a static web server on separate servers, with a single entry point), you will need to look into Application Layer (Layer 7) load balancing.</p>

<p>This tutorial is written with WordPress as the example, but its general concepts can be used to load balance other, stateless web applications.</p>

<h2 id="prerequisites">Prerequisites</h2>

<p>Before continuing with this tutorial, you should have completed the tutorial on setting up a WordPress site with a separate database server (or have a similar setup): <a href="https://indiareads/community/articles/how-to-set-up-a-remote-database-to-optimize-site-performance-with-mysql">How To Set Up a Remote Database to Optimize Site Performance with MySQL</a></p>

<p>After following that tutorial to set up WordPress on separate web application and database servers, you should have two VPSs. Because we will be dealing with several VPSs, for reference purposes, we will call your two existing VPSs the following:</p>

<ul>
<li><strong>wordpress-1</strong>: Your WordPress web application server</li>
<li><strong>mysql-1</strong>: Your MySQL server for WordPress</li>
</ul>

<p>An abstract view of your environment currently looks something like this:</p>

<p><img src="https://assets.digitalocean.com/articles/HAProxy/wordpress_web_server.png" alt="WordPress Application and Database Server" /></p>

<p>In addition to your current environment, we will require two additional VPSs during this tutorial. We will call them:</p>

<ul>
<li><strong>wordpress-2</strong>: Your second WordPress web application server</li>
<li><strong>haproxy-www</strong>: Your HAProxy server, for load balancing</li>
</ul>

<p>If you are unfamiliar with basic load-balancing concepts or terminology, like <em>layer 4 load balancing</em> or <em>backends</em> or <em>ACLs</em>, here is an article that explains the basics: <a href="https://indiareads/community/articles/an-introduction-to-haproxy-and-load-balancing-concepts">An Introduction to HAProxy and Load Balancing Concepts</a>.</p>

<h2 id="our-goal">Our Goal</h2>

<p>By the end of this tutorial, we want to have an environment that looks like this:</p>

<p><img src="https://assets.digitalocean.com/articles/HAProxy/wordpress_layer4_appbalanced.png" alt="HAProxy Load Balanced Web Application" /></p>

<p>That is, your users will access your WordPress site by going to your HAProxy Server, which will forward them to your load balanced WordPress Application Servers in a round-robin fashion. Your two (or more, if you wish) will both access your MySQL database.</p>

<h2 id="snapshot-your-current-environment">Snapshot Your Current Environment</h2>

<p><em>Optional</em>: Before continuing with this tutorial, you will want to create snapshots of your current environment. Snapshotting serves two purposes in this tutorial:</p>

<ol>
<li>To revert to a working environment if a mistake is made</li>
<li>To do a one-time replication of the original server, eliminating the need to install and configure PHP and Nginx again</li>
</ol>

<p><strong>Note:</strong> Snapshotting your environment requires you to poweroff your VPSs briefly.</p>

<p>Take a snapshot of your <em>wordpress-1</em> and <em>mysql-1</em> VPSs.</p>

<p>Now that we have snapshots, we are ready to move on to building out the rest of our environment.</p>

<h2 id="create-your-second-web-application-server">Create Your Second Web Application Server</h2>

<p>Now we need to create a second VPS that will share the load with our original web application server. There are two options for this:</p>

<ol>
<li>Create a new VPS from the snapshot you took of the original VPS, <em>wordpress-1</em></li>
<li>Create a new VPS from scratch and manually set it up with the same software and configuration as <em>wordpress-1</em></li>
</ol>

<p>With either method, be sure to select the <em>Private Networking</em> option if it's available. Private networking is recommended for all of the VPSs used in this tutorial.</p>

<p><strong>If you do not have a private networking option, substitute the private IP addresses with your VPSs public IP addresses.</strong> Note that using public IP addresses when you are transmitting sensitive data, such as unencrypted database passwords between your application and database servers, is not good practice because that information will travel over the public internet.</p>

<h3 id="option-1-create-new-vps-with-snapshot">Option 1: Create New VPS With Snapshot</h3>

<p>Create a new VPS called <em>wordpress-2</em>, using the snapshot you took of <em>wordpress-1</em>. </p>

<p>If you opted for this method, skip over "Option 2" to the "Synchronize Web Application Files" section.</p>

<h3 id="option-2-create-new-vps-from-scratch">Option 2: Create New VPS From Scratch</h3>

<p>This is an alternative to "Option 1."</p>

<p>If you want to set up the <em>wordpress-2</em> server from scratch, instead of using a snapshot of <em>wordpress-1</em>, make sure that you install the same software. Refer to the <a href="https://indiareads/community/articles/how-to-set-up-a-remote-database-to-optimize-site-performance-with-mysql#SetUptheWebServer">Set Up the Web Server</a> section of the prerequisite document if you don't remember how you installed and configured your original WordPress server.</p>

<p>For a quick reference, here is a list of the pertinent software and configuration files that you will need to install or replicate:</p>

<p>Software:</p>

<ul>
<li>MySQL Client</li>
<li>Nginx</li>
<li>PHP</li>
</ul>

<p>To install this software, run the following on your <em>wordpress-2</em> server:</p>
<pre class="code-pre "><code langs="">sudo apt-get update
sudo apt-get install mysql-client
sudo apt-get install nginx php5-fpm php5-mysql
</code></pre>
<p>Configuration Files that need to be edited or created to match your original application server:</p>

<ul>
<li>/etc/php5/fpm/php.ini</li>
<li>/etc/php5/fpm/pool.d/www.conf</li>
<li>/etc/nginx/sites-available/example.com</li>
<li>/etc/nginx/sites-enabled/example.com</li>
</ul>

<p>Don't forget to restart PHP and Nginx once you are done configuring the software, with these commands:</p>
<pre class="code-pre "><code langs="">sudo service php5-fpm restart
sudo service nginx restart
</code></pre>
<p>After you are done installing and configuring your new application server, we will need to synchronize the WordPress application files.</p>

<h2 id="synchronize-web-application-files">Synchronize Web Application Files</h2>

<p>Before the application can be load balanced, we need to ensure that the new server's web application files are synchronized with your original WordPress server. The location of these files is dependent on where you installed WordPress, and a few other files. In addition to the php files that WordPress needs to run, files uploaded and plugins installed through the WordPress interface need to be synchronized as they are uploaded or installed. In the prerequisite document, we installed WordPress in <code>/var/www/example.com</code>--we will use this location for all of our examples, but you need to substitute this with your actual WordPress install path.</p>

<p>There are a several ways to synchronize files between servers--NFS or glusterFS are both suitable options. We will use glusterFS to fulfill our synchronization needs because it allows each application server to store its own copy of the application files, while maintaining consistency across the file system. Here is a conceptual diagram of our target shared storage:</p>

<p><img src="https://assets.digitalocean.com/articles/HAProxy/wordpress_glusterfs.png" alt="glusterFS Shared Volume" /></p>

<p>If you are unfamiliar with any of the glusterFS terminology that is used in this section please refer to <a href="https://indiareads/community/articles/how-to-create-a-redundant-storage-pool-using-glusterfs-on-ubuntu-servers">this GlusterFS Tutorial</a>, on which this section is based on.</p>

<p><strong>Note:</strong> The following subsections jump between <em>wordpress-1</em> and <em>wordpress-2</em> servers frequently. Be sure to run the commands on the proper servers, or you will run into problems!</p>

<h3 id="edit-hosts-file">Edit Hosts File</h3>

<p><strong>Note:</strong> If you have an internal DNS, and it has records for the private IP addresses of your VPSs, feel free to skip this step and substitute those host names for the rest of the glusterFS setup commands and configuration.</p>

<p>Otherwise, <strong>on wordpress-1 and wordpress-2 VPSs:</strong></p>

<p>Edit /etc/hosts:</p>
<pre class="code-pre "><code langs="">sudo vi /etc/hosts
</code></pre>
<p>Add the following two lines, substituting the highlighted words with your application servers' IP respective IP addresses:</p>

<pre>
<span class="highlight">wordpress_1_private_IP</span>  wordpress-1
<span class="highlight">wordpress_2_private_IP</span>  wordpress-2
</pre>

<p>Save and quit.</p>

<h3 id="install-glusterfs-and-configure-a-replicated-volume">Install GlusterFS and Configure a Replicated Volume</h3>

<p><strong>On <em>wordpress-1</em> and <em>wordpress-2</em> VPSs:</strong></p>

<p>Use apt-get to install the glusterFS server software:</p>
<pre class="code-pre "><code langs="">sudo apt-get install glusterfs-server
</code></pre>
<p><strong>On wordpress-1</strong>, run the following command to peer with your <em>wordpress-2</em>:</p>
<pre class="code-pre "><code langs="">sudo gluster peer probe wordpress-2
</code></pre>
<p><strong>On wordpress-2</strong>, run the following command to peer with <em>wordpress-1</em>:</p>
<pre class="code-pre "><code langs="">sudo gluster peer probe wordpress-1
</code></pre>
<p><strong>On wordpress-1 and wordpress-2</strong>, to create the location where glusterFS will store the files it manages, run:</p>
<pre class="code-pre "><code langs="">sudo mkdir /gluster-storage
</code></pre>
<p><strong>On wordpress-1</strong>, to create a replicating glusterFS volume called <code>volume1</code>, which will store its data in <code>/gluster-storage</code> on both of your application servers, run:</p>

<pre>
sudo gluster volume create <span class="highlight">volume1</span> replica 2 transport tcp wordpress-1:<span class="highlight">/gluster-storage</span> wordpress-2:<span class="highlight">/gluster-storage</span> force
</pre>

<pre>
Expected Output: volume create: volume1: success: please start the volume to access data
</pre>

<p><strong>On wordpress-1</strong> again, run the following command to start the glusterFS volume that you just created, <code>volume1</code>:</p>

<pre>
sudo gluster volume start <span class="highlight">volume1</span>
</pre>

<pre>
Expected Output: volume start: volume1: success
</pre>

<p><strong>On wordpress-1</strong>, if you want to see information about the glusterFS volume you just created and started, run:</p>
<pre class="code-pre "><code langs="">sudo gluster volume info
</code></pre>
<p>You should see that you have two glusterFS "bricks", one for each WordPress server.</p>

<p>Now that we have a glusterFS volume running, let's mount it so we can use it as a replicating filesystem.</p>

<h3 id="mount-shared-storage">Mount Shared Storage</h3>

<p>Let's mount the filesystem on wordpress-1 first.</p>

<p><strong>On wordpress-1</strong>, edit fstab so our shared file system will mount on boot:</p>
<pre class="code-pre "><code langs="">sudo vi /etc/fstab
</code></pre>
<p>Add the following line to the end of the file to use <code>/storage-pool</code> as our mount point. Feel free to substitute this (here and for the rest of this glusterFS setup):</p>

<pre>
wordpress-1:<span class="highlight">/volume1</span>   <span class="highlight">/storage-pool</span>   glusterfs defaults,_netdev 0 0
</pre>

<p>Save and Quit.</p>

<p><strong>On wordpress-1</strong>, you are now able to mount the glusterFS volume to the <code>/storage_pool</code> filesystem:</p>

<pre>
sudo mkdir <span class="highlight">/storage-pool</span>
sudo mount <span class="highlight">/storage-pool</span>
</pre>

<p>That mounts the shared volume, /storage-pool, on your <em>wordpress-1</em> VPS. You can run <code>df -h</code> and it should be listed as a mounted filesystem. Next, we will follow a similar process to mount the shared storage on <em>wordpress-2</em>.</p>

<p><strong>On wordpress-2</strong>, edit fstab so our shared file system will mount on boot:</p>
<pre class="code-pre "><code langs="">sudo vi /etc/fstab
</code></pre>
<p>Add the following line to the end of the file to use <code>/storage-pool</code> as our mount point. If you used a different value, make sure you substitute that in here:</p>

<pre>
wordpress-2:<span class="highlight">/volume1</span>   <span class="highlight">/storage-pool</span>   glusterfs defaults,_netdev 0 0
</pre>

<p><strong>On wordpress-2</strong>, you are now able to mount the glusterFS volume to the <code>/storage_pool</code> filesystem:</p>
<pre class="code-pre "><code langs="">sudo mkdir /storage-pool
sudo mount /storage-pool
</code></pre>
<p>Now, any files that are created, modified, or deleted in the <code>/storage-pool</code> filesystem will be synchronized across both servers, even if one of the servers goes down temporarily.</p>

<h3 id="move-wordpress-files-to-shared-storage">Move WordPress Files to Shared Storage</h3>

<p>The next step is to move <em>wordpress-1</em>'s WordPress files to the shared storage. Please substitute the highlighted words with your own values. <code>/var/www/example.com</code> represents where your WordPress files were located (and where Nginx is looking for the files), and <code>example.com</code> by itself is simply the directory's basename.</p>

<p><strong>On wordpress-1</strong>, run these commands to move your WordPress application files to your shared filesystem, <code>/storage-pool</code>:</p>

<pre>
sudo mv <span class="highlight">/var/www/example.com</span> /storage-pool/
sudo chown www-data:www-data /storage-pool/<span class="highlight">example.com</span>
</pre>

<p>Next, you will want to create a symbolic link, that points to the WordPress files on the shared filesystem, where your WordPress files were originally stored by running:</p>

<pre>
sudo ln -s /storage-pool/<span class="highlight">example.com</span> <span class="highlight">/var/www/example.com</span>
</pre>

<p>Now your WordPress files are located on the shared filesystem, <code>/storage-pool</code>, and they are still accessible to Nginx via their original location, <code>/var/www/example.com</code>.</p>

<h3 id="point-new-application-server-to-shared-storage">Point New Application Server to Shared Storage</h3>

<p>The next step is to create a symbolic link on our new web application server that points to the WordPress files on the shared filesystem.</p>

<p>If you created <em>wordpress-2</em> using the <strong>snapshot option</strong>, run the following commands <strong>on wordpress-2</strong>:</p>

<pre>
sudo rm <span class="highlight">/var/www/example.com</span>
sudo ln -s /storage-pool/<span class="highlight">example.com</span> <span class="highlight">/var/www/example.com</span>
</pre>

<p>If you created <em>wordpress-2 *</em>from scratch<strong>, run the following commands **on wordpress-2</strong>:</p>

<pre>
sudo mkdir -p <span class="highlight">/var/www</span>
sudo ln -s /storage-pool/<span class="highlight">example.com</span> <span class="highlight">/var/www/example.com</span>
</pre>

<p>That's it for synchronizing the WordPress application files! The next step is giving our new application server, <em>wordpress-2</em>, access to the database.</p>

<h2 id="create-a-new-database-user">Create a New Database User</h2>

<p>Because MySQL identifies users by username and source host, we need to create a new <em>wordpressuser</em> that can connect from our new application server, <em>wordpress-2</em>.</p>

<p>On your database VPS, <em>mysql-1</em>, connect to the MySQL console:</p>
<pre class="code-pre "><code langs="">mysql -u root -p
</code></pre>
<p>In the following MySQL statements, replace all of the highlighted words with whatever is appropriate for your environment:</p>

<ul>
<li><strong>wordpressuser</strong>: your MySQL WordPress user. Ensure it is the same as the already existing username</li>
<li><strong>wordpress<em>2</em>private_IP</strong>:the private IP of your <em>wordpress-2</em> VPS</li>
<li><strong>password</strong>: your MySQL WordPress user's password. Ensure it is the same as the already existing password (and that it's a good password!)</li>
</ul>

<p>Run this statement create a MySQL user that can connect from your new WordPress server, <em>wordpress-2</em>:</p>

<pre>
CREATE USER '<span class="highlight">wordpressuser</span>'@'<span class="highlight">wordpress_2_private_IP</span>' IDENTIFIED BY '<span class="highlight">password</span>';
</pre>

<p>Again, substitute your own values for <code>wordpressuser</code>, <code>wordpress_2_private_IP</code>, and, if your <em>database</em> isn't named "wordpress", make sure to change that as well.</p>

<pre>
GRANT SELECT,DELETE,INSERT,UPDATE ON <span class="highlight">wordpress</span>.* TO '<span class="highlight">wordpressuser</span>'@'<span class="highlight">wordpress_2_private_IP</span>';
FLUSH PRIVILEGES;
</pre>

<p>Now your second web application server, <em>wordpress-2</em>, can log in to MySQL on your database server, <em>mysql-1</em>.</p>

<h2 id="not-yet-load-balanced">Not Yet Load Balanced</h2>

<p>Note that there are two web application servers that are running but the application isn't load balanced because each server has to be accessed via their respective Public IP Addresses. We want to be able to access the application via the same URL, such as <em>http://example.com/</em>, and have the traffic balanced between the two web application servers. This is where HAProxy comes in.</p>

<h2 id="install-haproxy">Install HAProxy</h2>

<p>Create a new VPS with Private Networking. For this tutorial, we will call it <em>haproxy-www</em>.</p>

<p>In our <strong>haproxy-www</strong> VPS, let's install HAProxy with <em>apt-get</em>:</p>
<pre class="code-pre "><code langs="">sudo apt-get update
sudo apt-get install haproxy
</code></pre>
<p>We need to enable the HAProxy init script, so HAProxy will start and stop along with your VPS.</p>
<pre class="code-pre "><code langs="">sudo vi /etc/default/haproxy
</code></pre>
<p>Change the value of <code>ENABLED</code> to <code>1</code> to enable the HAProxy init script:</p>
<pre class="code-pre "><code langs="">ENABLED=1
</code></pre>
<p>Save and quit. Now HAProxy will start and stop with your VPS. Also, you can now use the <code>service</code> command to control your HAProxy. Let's check to see if it is running:</p>
<pre class="code-pre "><code langs="">user@haproxy-www:/etc/init.d$ sudo service haproxy status
haproxy not running.
</code></pre>
<p>It is not running. That's fine, because it needs to be configured before we can use it. Let's configure HAProxy for our environment next.</p>

<h2 id="haproxy-configuration">HAProxy Configuration</h2>

<p>HAProxy's configuration file is divided into two major sections:</p>

<ul>
<li><strong>Global</strong>: sets process-wide parameters</li>
<li><strong>Proxies</strong>: consists of <em>defaults</em>, <em>listen</em>, <em>frontend</em>, and <em>backend</em> parameters</li>
</ul>

<p>Again, if you are unfamiliar with HAProxy or basic load-balancing concepts and terminology, please refer to this link: <a href="https://indiareads/community/articles/an-introduction-to-haproxy-and-load-balancing-concepts">An Introduction to HAProxy and Load Balancing Concepts<br />
</a></p>

<h3 id="haproxy-configuration-global">HAProxy Configuration: Global</h3>

<p><strong>All of the HAProxy configuration should be done on your HAProxy VPS, <em>haproxy-www</em>.</strong></p>

<p>First, let's make a copy of the default <em>haproxy.cfg</em> file:</p>
<pre class="code-pre "><code langs="">cd /etc/haproxy; sudo cp haproxy.cfg haproxy.cfg.orig
</code></pre>
<p>Now open haproxy.cfg in a text editor:</p>
<pre class="code-pre "><code langs="">sudo vi /etc/haproxy/haproxy.cfg
</code></pre>
<p>You will see that there are two sections already defined: <em>global</em> and <em>defaults</em>. First we will make a few changes to some of the default parameters.</p>

<p>Under <em>defaults</em>, look for the following lines:</p>
<pre class="code-pre "><code langs="">mode    http
option  httplog
</code></pre>
<p>Replace the word "http" with "tcp in both instances:</p>
<pre class="code-pre "><code langs="">mode    tcp
option  tcplog
</code></pre>
<p>Selecting tcp as the mode configures HAProxy to perform layer 4 load balancing. In our case, this means that all of the incoming traffic on a specific IP address and port will be forwarded to the same backend. If you are unfamiliar with this concept, please read the <em>Types of Load Balancing</em> section in our <a href="https://indiareads/community/articles/an-introduction-to-haproxy-and-load-balancing-concepts#TypesofLoadBalancing">Intro to HAProxy</a>.</p>

<p>Do not close the config file yet! We will add the proxy configuration next.</p>

<h3 id="haproxy-configuration-proxies">HAProxy Configuration: Proxies</h3>

<p>The first thing we want to add is a frontend. For a basic layer 4 load balancing setup, a frontend listens for traffic on a specific IP address and port then forwards incoming traffic to a specified backend.</p>

<p>At the end of the file, let's add our frontend, <em>www</em>. Be sure to replace <code>haproxy_www_public_IP</code> with the <strong>public IP</strong> of your haproxy-www VPS:</p>

<pre>
frontend www
   bind <span class="highlight">haproxy_www_public_IP</span>:80
   default_backend wordpress-backend
</pre>

<p>Here is an explanation of what each line in the frontend config snippet above means:</p>

<ul>
<li><strong>frontend www</strong>: specifies a frontend named "www", as we will use it to handle incoming www traffic</li>
<li><strong>bind haproxy_www_public_IP:80</strong>: replace <code>haproxy_www_public_IP</code> with haproxy-www's public IP address. This tells HAProxy that this frontend will handle the incoming network traffic on this IP address and port</li>
<li><strong>default_backend wordpress-backend</strong>: this specifies that all of this frontend's traffic will be forwarded to <em>wordpress-backend</em>, which we will define in the next step</li>
</ul>

<p>After you are finished configuring the frontend, continue adding the backend by adding the following lines. Be sure to replace the highlighted words with the appropriate values:</p>

<pre>
backend wordpress-backend
   balance roundrobin
   mode tcp
   server wordpress-1 <span class="highlight">wordpress_1_private_IP</span>:80 check
   server wordpress-2 <span class="highlight">wordpress_2_private_IP</span>:80 check
</pre>

<p>Here is an explanation of what each line in the backend config snippet above means:</p>

<ul>
<li><strong>backend wordpress-backend</strong>: specifies a backend named "wordpress-backend"</li>
<li><strong>balance roundrobin</strong>: specifies that this backend will use the "roundrobin" load balancing algorithm</li>
<li><strong>mode tcp</strong>: specifies that this backend will use "tcp" or layer 4 proxying</li>
<li><strong>server wordpress-1 ...</strong>: specifies a backend server named "wordpress-1", the private IP (which you must substitute) and port that it is listening on, <em>80</em> in this case. The "check" option makes the load balancer periodically perform a health check on this server</li>
<li><strong>server wordpress-2 ...</strong>: this specifies a backend server named "wordpress-2"</li>
</ul>

<p>Now save and quit. HAProxy is now ready to be started, but let's enable logging first.</p>

<h2 id="enabling-haproxy-logging">Enabling HAProxy Logging</h2>

<p>Enabling logging in HAProxy is very simple. First edit the rsyslog.conf file:</p>
<pre class="code-pre "><code langs="">sudo vi /etc/rsyslog.conf
</code></pre>
<p>Then find the following two lines, and uncomment them to enable UDP syslog reception. It should look like the following when you are done:</p>
<pre class="code-pre "><code langs="">$ModLoad imudp
$UDPServerRun 514
$UDPServerAddress 127.0.0.1
</code></pre>
<p>Now restart rsyslog to enable the new configuration:</p>
<pre class="code-pre "><code langs="">sudo service rsyslog restart
</code></pre>
<p>HAProxy logging is is now enabled! The log file will be created at <code>/var/log/haproxy.log</code> once HAProxy is started.</p>

<h2 id="start-haproxy-and-php-nginx">Start HAProxy and PHP/Nginx</h2>

<p><strong>On haproxy-www</strong>, start HAProxy to make your config changes take effect:</p>
<pre class="code-pre "><code langs="">sudo service haproxy restart
</code></pre>
<p>Depending on how you set up your new application server, you might need to restart your WordPress application by restarting PHP and Nginx.</p>

<p><strong>On wordpress-2</strong>, restart PHP and Nginx by running these commands:</p>
<pre class="code-pre "><code langs="">sudo service php5-fpm restart
sudo service nginx restart
</code></pre>
<p>Now WordPress should be running on both of your application servers, and they are load balanced. But there is still one last configuration change to be made.</p>

<h2 id="update-wordpress-configuration">Update WordPress Configuration</h2>

<p>Now that your WordPress application's URL has changed, we must update a couple of settings in WordPress.</p>

<p><strong>On either WordPress server</strong>, edit your wp-config.php. It is located where you installed WordPress (in the tutorial, it was installed in <em>/var/www/example.com</em> but your installation may vary):</p>

<pre>
cd <span class="highlight">/var/www/example.com</span>; sudo vi wp-config.php
</pre>

<p>Find the line near the top that says <code>define('DB_NAME', 'wordpress');</code> and add the following lines above it, substituting the highlighted values,:</p>

<pre>
define('WP_SITEURL', '<span class="highlight">http://haproxy_www_public_IP</span>');
define('WP_HOME', '<span class="highlight">http://haproxy_www_public_IP</span>');
</pre>

<p>Save and quit. Now the WordPress URLs are configured to point to your load balancer instead of only your original WordPress server, which comes into play when you try and access the wp-admin Dashboard.</p>

<h2 id="load-balancing-complete">Load Balancing Complete!</h2>

<p>Your web application servers are now load balanced! Your load balanced WordPress is now accessible to your user via the public IP address or domain name of your load balancer, <em>haproxy-www</em>!</p>

<h2 id="conclusion">Conclusion</h2>

<p>Now the load of your users will be spread between your two WordPress servers. Additionally, if one of your WordPress application servers goes down, your site will still be available because the other WordPress server will be forwarded all of the traffic!</p>

<p>With this setup, remember that your HAProxy load balancer server, <em>haproxy-www</em>, and your database server, <em>mysql-1</em>, need to be running for your site to work properly.</p>

<div class="author">By Mitchell Anicas</div>

    