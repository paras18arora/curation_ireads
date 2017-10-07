<link rel='stylesheet' href='digital_ocean.css'><br><img src=https://community-cdn-digitalocean-com.global.ssl.fastly.net/assets/tutorials/images/large/RedisSession-twitter.png?1443735772/> <br> 
      <h3 id="introduction">Introduction</h3>

<p>Redis is an open source key-value cache and storage system, also referred to as a data structure server for its advanced support for several data types, such as hashes, lists, sets, and bitmaps, amongst others. It also supports clustering, which makes it often used for highly-available and scalable environments.</p>

<p>In this tutorial, we'll see how to install and configure an external Redis server to be used as a session handler for a PHP application running on Ubuntu 14.04.</p>

<p>The session handler is responsible for storing and retrieving data saved into sessions - by default, PHP uses <strong>files</strong> for that. An external session handler can be used for creating <a href="https://indiareads/company/blog/horizontally-scaling-php-applications/">scalable PHP environments</a> behind a <a href="https://indiareads/community/tutorials/an-introduction-to-haproxy-and-load-balancing-concepts">load balancer</a>, where all application nodes will connect to a central server to share session information.</p>

<h2 id="prerequisites">Prerequisites</h2>

<p>We will be working with two distinct servers in this tutorial. For security and performance reasons, it's important that both Droplets are located in the same datacenter with <a href="https://indiareads/community/tutorials/how-to-set-up-and-use-digitalocean-private-networking">private networking</a> enabled. This is what you will need:</p>

<ul>
<li>A PHP web server running <a href="https://indiareads/community/tutorials/how-to-install-linux-apache-mysql-php-lamp-stack-on-ubuntu-14-04">LAMP</a> or <a href="https://indiareads/community/tutorials/how-to-install-linux-nginx-mysql-php-lemp-stack-on-ubuntu-14-04">LEMP</a> on Ubuntu 14.04 - we will refer to this server as <strong>web</strong></li>
<li>A second, clean Ubuntu 14.04 server where Redis will be installed - we will refer to this server as <strong>redis</strong></li>
</ul>

<p>You'll need proper SSH access to both servers as a regular user with <a href="https://indiareads/community/tutorials/initial-server-setup-with-ubuntu-14-04">sudo permission</a>.</p>

<p><span class="note">For the Redis server, you can also use our <a href="https://indiareads/community/tutorials/how-to-use-the-redis-one-click-application">Redis One-Click Application</a> and skip to <a href="#step-2-%E2%80%94-configure-redis-to-accept-external-connections">Step 2</a>.<br /></span></p>

<h2 id="step-1-—-install-the-redis-server">Step 1 — Install the Redis Server</h2>

<p>The first thing we need to do is get the Redis server up and running, on our <strong>redis</strong> Droplet.</p>

<p>We will be using the regular Ubuntu package manager with a trusted PPA repository provided by Chris Lea. This is necessary to make sure we get the latest stable version of Redis.</p>

<p><span class="note">As a general piece of security advice, you should only use PPAs from trusted sources.<br /></span></p>

<p>First, add the PPA repository by running:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo add-apt-repository ppa:chris-lea/redis-server
</li></ul></code></pre>
<p>Press <code>ENTER</code> to confirm.</p>

<p>Now you need to update the package manager cache:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo apt-get update
</li></ul></code></pre>
<p>And finally, let's install Redis by running:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo apt-get install redis-server
</li></ul></code></pre>
<p>Redis should now be installed on your server. To test the installation, try this command:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">redis-cli ping
</li></ul></code></pre>
<p>This will connect to a Redis instance running on <strong>localhost</strong> on port <strong>6379</strong>. You should get a <strong>PONG</strong> as response.</p>

<h2 id="step-2-—-configure-redis-to-accept-external-connections">Step 2 — Configure Redis to Accept External Connections</h2>

<p>By default, Redis only allows connections to <code>localhost</code>, which basically means you´ll only have access from inside the server where Redis is installed. We need to change this configuration to allow connections coming from other servers on the same private network as the <strong>redis</strong> server.</p>

<p>The first thing we need to do is find out the private network IP address of the Redis machine. The following steps should be executed on the <strong>redis</strong> server.</p>

<p>Run <code>ifconfig</code> to get information about your network interfaces:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo ifconfig
</li></ul></code></pre>
<p>You should get an output similar to this:</p>
<pre class="code-pre "><code langs=""><div class="secondary-code-label " title="Output">Output</div>    eth0      Link encap:Ethernet  HWaddr 04:01:63:7e:a4:01  
              inet addr:188.166.77.33  Bcast:188.166.127.255  Mask:255.255.192.0
              inet6 addr: fe80::601:63ff:fe7e:a401/64 Scope:Link
              UP BROADCAST RUNNING MULTICAST  MTU:1500  Metric:1
              RX packets:3497 errors:0 dropped:0 overruns:0 frame:0
              TX packets:3554 errors:0 dropped:0 overruns:0 carrier:0
              collisions:0 txqueuelen:1000 
              RX bytes:4895060 (4.8 MB)  TX bytes:619070 (619.0 KB)

    eth1      Link encap:Ethernet  HWaddr 04:01:63:7e:a4:02  
              inet addr:<span class="highlight">10.133.14.9</span>  Bcast:10.133.255.255  Mask:255.255.0.0
              inet6 addr: fe80::601:63ff:fe7e:a402/64 Scope:Link
              UP BROADCAST RUNNING MULTICAST  MTU:1500  Metric:1
              RX packets:8 errors:0 dropped:0 overruns:0 frame:0
              TX packets:7 errors:0 dropped:0 overruns:0 carrier:0
              collisions:0 txqueuelen:1000 
              RX bytes:648 (648.0 B)  TX bytes:578 (578.0 B)

</code></pre>
<p>Look for the <code>inet_addr</code> assigned to the <strong>eth1</strong> interface. In this case, it's <code>10.133.14.9</code> - this is the IP address we will be using later to connect to the <strong>redis</strong> server from the <strong>web</strong> server.</p>

<p>Using your favorite command line editor, open the file <code>/etc/redis/redis.conf</code> and look for the line that contains the <code>bind</code> definition. You should add your <strong>private network IP address</strong> to the line, as follows:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo vim /etc/redis/redis.conf
</li></ul></code></pre><div class="code-label " title="/etc/redis/redis.conf">/etc/redis/redis.conf</div><pre class="code-pre "><code langs="">bind localhost <span class="highlight">10.133.14.9</span>
</code></pre>
<p>If you see <code>127.0.0.1</code> instead of <code>localhost</code> that's fine; just add your private IP after what's already there.</p>

<p>Now you just need to restart the Redis service to apply the changes:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo service redis-server restart
</li></ul></code></pre>
<p><span class="note">If you installed Redis using our One-click application, the service name will be <strong>redis</strong> instead of <strong>redis-server</strong>. To restart it, you should run: <code>sudo service redis restart</code> .<br /></span></p>

<p>With this change, any server inside the same private network will also be able to connect to this Redis instance.</p>

<h2 id="step-3-—-set-a-password-for-the-redis-server">Step 3 — Set a Password for the Redis Server</h2>

<p>To add an extra layer of security to your Redis installation, you are encouraged to set a password for accessing the server data. We will edit the same configuration file from the previous step, <code>/etc/redis/redis.conf</code>:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo vim /etc/redis/redis.conf
</li></ul></code></pre>
<p>Now, uncomment the line that contains <code>requirepass</code>, and set a strong password:</p>
<div class="code-label " title="/etc/redis/redis.conf">/etc/redis/redis.conf</div><pre class="code-pre "><code langs="">requirepass <span class="highlight">yourverycomplexpasswordhere</span>
</code></pre>
<p>Restart the Redis service so the changes take effect:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo service redis-server restart
</li></ul></code></pre>
<h2 id="step-4-—-test-redis-connection-and-authentication">Step 4 — Test Redis Connection and Authentication</h2>

<p>To test if all your changes worked as expected, connect to the Redis service from inside the <strong>redis</strong> machine:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">redis-cli -h <span class="highlight">10.133.14.9</span>
</li></ul></code></pre><pre class="code-pre "><code langs=""><div class="secondary-code-label " title="Output">Output</div>10.133.14.9:6379>
</code></pre>
<p><span class="note">Even though it´s not mandatory to specify the <code>host</code> parameter here (since we are connecting from <code>localhost</code>), we did it to make sure the Redis service will accept connections targeted at the private network interface. <br /></span></p>

<p>If you defined a password and now try to access the data, you should get an AUTH error:</p>
<pre class="code-pre custom_prefix"><code langs=""><ul class="prefixed"><li class="line" prefix="10.133.14.9:6379>">keys *
</li></ul></code></pre><pre class="code-pre "><code langs=""><div class="secondary-code-label " title="Output">Output</div>(error) NOAUTH Authentication required.
</code></pre>
<p>To authenticate, you just need to run the <code>AUTH</code> command, providing the same password you defined in the <code>/etc/redis/redis.conf</code> file:</p>
<pre class="code-pre custom_prefix"><code langs=""><ul class="prefixed"><li class="line" prefix="10.133.14.9:6379>">AUTH yourverycomplexpasswordhere
</li></ul></code></pre>
<p>You should get an <strong>OK</strong> as response. Now if you run: </p>
<pre class="code-pre custom_prefix"><code langs=""><ul class="prefixed"><li class="line" prefix="10.133.14.9:6379>">keys *
</li></ul></code></pre>
<p>The output should be similar to this:</p>
<pre class="code-pre "><code langs=""><div class="secondary-code-label " title="Output">Output</div>(empty list or set)
</code></pre>
<p>This output just means your Redis server is empty, which is exactly what we expected, since the <strong>web</strong> server is not yet configured to use this Redis server as a session handler.</p>

<p>Keep this SSH session opened and connected to the <code>redis-cli</code> while we perform the next steps - we will get back to the <code>redis-cli</code> prompt to check if the session data is being properly stored, after we make the necessary changes to the <strong>web</strong> server.</p>

<h2 id="step-5-—-install-the-redis-extension-on-the-web-server">Step 5 — Install the Redis Extension on the Web Server</h2>

<p>The next steps should be executed on the <strong>web</strong> server. We need to install the PHP Redis extension, otherwise PHP won't be able to connect to the Redis server.</p>

<p>First, update your package manager cache by running:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo apt-get update
</li></ul></code></pre>
<p>Then install the <code>php5-redis</code> package:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo apt-get install php5-redis
</li></ul></code></pre>
<p>Your web server should now be able to connect to Redis.</p>

<h2 id="step-6-—-set-redis-as-the-default-session-handler-on-the-web-server">Step 6 — Set Redis as the Default Session Handler on the Web Server</h2>

<p>Now we need to edit the <code>php.ini</code> file on the <strong>web</strong> server to change the default session handler for PHP. The location of this file will depend on your current stack. For a <strong>LAMP</strong> stack on Ubuntu 14.04, this is usually <code>/etc/php5/apache2/php.ini</code>. For a <strong>LEMP</strong> stack on Ubuntu 14.04, the path is usually <code>/etc/php5/fpm/php.ini</code>. </p>

<p>If you are unsure about the location of your main <code>php.ini</code> file, an easy way to find out is by using the function <code>phpinfo()</code>. Just place the following code in a file named <code>info.php</code> inside your web root directory:</p>
<pre class="code-pre line_numbers"><code class="code-highlight language-php"><ul class="prefixed"><li class="line" prefix="1"><?php
</li><li class="line" prefix="2">phpinfo();
</li></ul></code></pre>
<p>When accessing the script from your browser, look for the row containing "Loaded Configuration File", and you should find the exact location of the main <code>php.ini</code> loaded. </p>

<p><span class="note">Don't forget to remove the <code>info.php</code> file afterwards, as it contains sensitive information about your environment.<br /></span></p>

<p>Open your <code>php.ini</code> file and search for the line containing <code>session.save_handler</code>. The default value is <code>files</code>. You should change it to <code>redis</code>.</p>

<p>On <strong>LAMP</strong> environments:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo vim /etc/php5/apache2/php.ini
</li></ul></code></pre>
<p>On <strong>LEMP</strong> environments:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo vim /etc/php5/fpm/php.ini
</li></ul></code></pre><div class="code-label " title="/etc/php5/fpm/php.ini">/etc/php5/fpm/php.ini</div><pre class="code-pre "><code langs=""> session.save_handler = redis
</code></pre>
<p>Now you should find the line containing <code>session.save_path</code>. Uncomment it and change the value so it contains the Redis connection string. The content should follow this format, all in one line: <code>tcp://IPADDRESS:PORT?auth=REDISPASSWORD</code></p>
<div class="code-label " title="/etc/php5/fpm/php.ini">/etc/php5/fpm/php.ini</div><pre class="code-pre "><code langs=""> session.save_path = "tcp://<span class="highlight">10.133.14.9:6379</span>?auth=<span class="highlight">yourverycomplexpasswordhere</span>"
</code></pre>
<p><span class="note">You only need to provide the parameter <em>auth</em> if you did set a password when configuring Redis.<br /></span></p>

<p>Save the file and restart the <strong>php</strong> service.</p>

<p>On <strong>LAMP</strong> environments:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo service apache2 restart
</li></ul></code></pre>
<p>On <strong>LEMP</strong> environments:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo service php5-fpm restart 
</li></ul></code></pre>
<h2 id="step-7-—-test-redis-session-handling">Step 7 — Test Redis Session Handling</h2>

<p>To make sure your sessions are now handled by Redis, you will need a PHP script or application that stores information on sessions. We are going to use a simple script that implements a counter - each time you reload the page, the printed number is incremented. </p>

<p>Create a file named <code>test.php</code> on the <strong>web</strong> server and place it inside your document root folder:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo vim <span class="highlight">/usr/share/nginx/html/</span>test.php
</li></ul></code></pre>
<p><span class="note">Don't forget to change <code>/usr/share/nginx/html</code> to reflect your document root path.<br /></span></p>
<div class="code-label " title="/usr/share/nginx/html/test.php">/usr/share/nginx/html/test.php</div><pre class="code-pre line_numbers"><code class="code-highlight language-php"><ul class="prefixed"><li class="line" prefix="1"> <?php
</li><li class="line" prefix="2">//simple counter to test sessions. should increment on each page reload.
</li><li class="line" prefix="3">session_start();
</li><li class="line" prefix="4">$count = isset($_SESSION['count']) ? $_SESSION['count'] : 1;
</li><li class="line" prefix="5">
</li><li class="line" prefix="6">echo $count;
</li><li class="line" prefix="7">
</li><li class="line" prefix="8">$_SESSION['count'] = ++$count;
</li></ul></code></pre>
<p>Point your browser to <code>http://<span class="highlight">web</span>/test.php</code> in order to access the script. It should increment the number each time you reload the page.</p>

<p>Now you should have session information stored on the Redis server. To verify, go back to your SSH session on the <strong>redis</strong> machine, where we previously connected to the Redis service using <code>redis-cli</code>. Fetch the content again with <code>keys *</code>:</p>
<pre class="code-pre custom_prefix"><code langs=""><ul class="prefixed"><li class="line" prefix="10.133.14.9:6379>">keys *
</li></ul></code></pre>
<p>And you should get an output similar to this:</p>
<pre class="code-pre "><code langs=""><div class="secondary-code-label " title="Output">Output</div>1) "PHPREDIS_SESSION:j9rsgtde6st2rqb6lu5u6f4h83"
</code></pre>
<p>This shows that the session information is being stored on the Redis server. You can connect additional web servers to the Redis server in a similar way.</p>

<h2 id="conclusion">Conclusion</h2>

<p>Redis is a powerful and fast key-value storage service that can also be used as session handler for PHP, enabling scalable PHP environments by providing a distributed system for session storage. For more information about scaling PHP applications, you can check this article: <a href="https://indiareads/company/blog/horizontally-scaling-php-applications/">Horizontally Scaling PHP Applications</a>.</p>

    