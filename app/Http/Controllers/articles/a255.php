<link rel='stylesheet' href='digital_ocean.css'><br> <br> 
      <h3 id="introduction">Introduction</h3>

<p>In this tutorial, we will teach you how to use HAProxy as a layer 7 load balancer to serve multiple applications from a single domain name or IP address. Load balancing can improve the performance, availability, and resilience of your environment.</p>

<p>Layer 7 reverse proxying and load balancing is suitable for your site if you want to have a single domain name that serves multiple applications, as the http requests can be analyzed to decide which application should receive the traffic.</p>

<p>This tutorial is written with WordPress and a static web site as examples, but its general concepts can be used with other applications to a similar effect.</p>

<h2 id="prerequisites">Prerequisites</h2>

<p>Before continuing with this tutorial, you should have at least two applications running on separate servers. We will use a static website hosted on Nginx and WordPress as our two applications. If you want to follow this tutorial exactly, here are the tutorials that we used to set up our prerequisite environment:</p>

<ul>
<li><strong>wordpress-1</strong> VPS: <a href="https://indiareads/community/articles/how-to-set-up-a-remote-database-to-optimize-site-performance-with-mysql">How To Set Up a Remote Database to Optimize Site Performance with MySQL</a></li>
<li><strong>web-1</strong> VPS: <a href="https://indiareads/community/articles/how-to-install-nginx-on-ubuntu-14-04-lts">How To Install Nginx on Ubuntu 14.04</a></li>
</ul>

<p>Our starting environment looks like this:</p>

<p><img src="https://assets.digitalocean.com/articles/HAProxy/l7/prereq.png" alt="Separate Application Servers" /></p>

<p>In addition to your current environment, we will be creating the following VPSs:</p>

<ul>
<li><strong>haproxy-www</strong>: Your HAProxy server, for load balancing and reverse proxying</li>
<li><strong>wordpress-2</strong>: Your second WordPress web application server (only required if you want to load balance the WordPress component of your environment)</li>
<li><strong>web-2</strong>: Your second Nginx web server (only required if you want to load balance the Nginx component of your environment)</li>
</ul>

<p>If you are unfamiliar with basic load-balancing concepts or terminology, like <em>layer 7 load balancing</em> or <em>backends</em> or <em>ACLs</em>, here is an article that explains the basics: <a href="https://indiareads/community/articles/an-introduction-to-haproxy-and-load-balancing-concepts">An Introduction to HAProxy and Load Balancing Concepts</a>.</p>

<h2 id="our-goal">Our Goal</h2>

<p>By the end of this tutorial, we want to have an environment that looks like this:</p>

<p><img src="https://assets.digitalocean.com/articles/HAProxy/l7/layer_7_load_balancing-goal.png" alt="Layer 7 Load Balancing" /></p>

<p>That is, your users will access both of your applications through <em>http://example.com</em>. All requests that begin with <em>http://example.com/wordpress</em> will be forwarded to your WordPress servers, and all of the other requests will be forwarded to your basic Nginx servers. Note that you do not necessarily need to load balance your applications to have them appear on a single domain, but we will cover load balancing in this tutorial.</p>

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

<h2 id="haproxy-configuration-global">HAProxy Configuration: Global</h2>

<p><strong>All of the HAProxy configuration should be done on your HAProxy VPS, <em>haproxy-www</em>.</strong></p>

<p>First, let's make a copy of the default <em>haproxy.cfg</em> file:</p>
<pre class="code-pre "><code langs="">cd /etc/haproxy; sudo cp haproxy.cfg haproxy.cfg.orig
</code></pre>
<p>Now open haproxy.cfg in a text editor:</p>
<pre class="code-pre "><code langs="">sudo vi /etc/haproxy/haproxy.cfg
</code></pre>
<p>You will see that there are two sections already defined: <em>global</em> and <em>defaults</em>. First we will take a look at some of the default parameters.</p>

<p>Under <em>defaults</em>, look for the following lines:</p>
<pre class="code-pre "><code langs="">mode    http
option  httplog
</code></pre>
<p>Selecting http as the mode configures HAProxy to perform layer 7, or application layer, load balancing. This means that the load balancer will look at the content of the http requests and forward it to the appropriate server based on the rules defined in the frontend. If you are unfamiliar with this concept, please read the <em>Types of Load Balancing</em> section in our <a href="https://indiareads/community/articles/an-introduction-to-haproxy-and-load-balancing-concepts#TypesofLoadBalancing">Intro to HAProxy</a>.</p>

<p>Do not close the config file yet! We will add the proxy configuration next.</p>

<h2 id="haproxy-configuration-proxies">HAProxy Configuration: Proxies</h2>

<h3 id="frontend-configuration">Frontend Configuration</h3>

<p>The first thing we want to add is a frontend. For a basic layer 7 reverse proxying and load balancing setup, we will want to define an ACL that will be used to direct our traffic to the appropriate backend servers. There are many ACLs that can be used in HAProxy, and we will only cover one of them in this tutorial (<em>path_beg</em>)--for a complete list of ACLs in HAProxy, check out the official documentation: <a href="http://cbonte.github.io/haproxy-dconv/configuration-1.4.html#7.5.3">HAProxy ACLs</a></p>

<p>At the end of the file, let's add our frontend, <em>www</em>. Be sure to replace <code>haproxy_www_public_IP</code> with the <strong>public IP</strong> of your haproxy-www VPS:</p>

<pre>
frontend www
   bind <span class="highlight">haproxy_www_public_IP</span>:80
   option http-server-close
   acl url_wordpress path_beg /wordpress
   use_backend wordpress-backend if url_wordpress
   default_backend web-backend
</pre>

<p>Here is an explanation of what each line in the frontend config snippet above means:</p>

<ul>
<li><strong>frontend www</strong>: specifies a frontend named "www", as we will use it to handle incoming www traffic</li>
<li><strong>bind haproxy_www_public_IP:80</strong>: replace <code>haproxy_www_public_IP</code> with haproxy-www's public IP address. This tells HAProxy that this frontend will handle the incoming network traffic on this IP address and port</li>
<li><strong>option http-server-close</strong>: enables HTTP connection-close mode on the server and maintains the ability to support HTTP keep-alive and pipelining on the client. This option will allow HAProxy to process multiple client requests with a single connection, which often improves performance</li>
<li><strong>acl url_wordpress path_beg /wordpress</strong>: specifies an ACL called <em>url_wordpress</em> that evaluates as true if the path of the request begins with "/wordpress", e.g. <em>http://example.com/wordpress/hello-world</em></li>
<li><strong>use_backend wordpress-backend if url_wordpress</strong>: directs any traffic that matches the <em>url_wordpress</em> ACL to <em>wordpress-backend</em>, which we will define soon</li>
<li><strong>default_backend web-backend</strong>: this specifies that any traffic that does not match a <em>use_backend</em> rule will be forwarded to <em>web-backend</em>, which we will define in the next step</li>
</ul>

<h3 id="backend-configuration">Backend Configuration</h3>

<p>After you are finished configuring the frontend, continue adding your first backend by adding the following lines. Be sure to replace the highlighted words with the appropriate values:</p>

<pre>
backend web-backend
   server web-1 <span class="highlight">web_1_private_IP</span>:80 check
</pre>

<p>Here is an explanation of what each line in the backend config snippet above means:</p>

<ul>
<li><strong>backend web-backend</strong>: specifies a backend named <em>web-backend</em></li>
<li><strong>server web-1 ...</strong>: specifies a backend server named <em>web-1</em>, the private IP (which you must substitute) and port that it is listening on, <em>80</em> in this case. The <em>check</em> option makes the load balancer periodically perform a health check on this server</li>
</ul>

<p>Then add the backend for your WordPress application :</p>

<pre>
backend wordpress-backend
   reqrep ^([^\ :]*)\ /<span class="highlight">wordpress</span>/(.*) \1\ /\2
   server wordpress-1 <span class="highlight">wordpress_1_private_IP</span>:80 check
</pre>

<p>Here is an explanation of what each line in the backend config snippet above means:</p>

<ul>
<li><strong>backend wordpress-backend</strong>: specifies a backend named <em>wordpress-backend</em></li>
<li><strong>reqrep ...</strong>: rewrites requests for <em>/wordpress</em> to <em>/</em> when forwarding traffic to the WordPress servers. This is not necessary if the WordPress application is installed in the server root but we want it to be accessible through /wordpress on our HAProxy server</li>
<li><strong>server wordpress-1 ...</strong>: specifies a backend server named <em>wordpress-1</em>, the private IP (which you must substitute) and port that it is listening on, <em>80</em> in this case. The <em>check</em> option makes the load balancer periodically perform a health check on this server</li>
</ul>

<h2 id="haproxy-configuration-stats">HAProxy Configuration: Stats</h2>

<p>If you want to enable HAProxy stats, which can be useful in determining how HAProxy is handling incoming traffic, you will want to add the following into your configuration:</p>

<pre>
listen stats :1936
   stats enable   
   stats scope www
   stats scope web-backend
   stats scope wordpress-backend
   stats uri /
   stats realm Haproxy\ Statistics
   stats auth <span class="highlight">user</span>:<span class="highlight">password</span>
</pre>

<p>Here is an explanation of the non-trivial lines in the <em>listen stats</em> configuration snippet above:</p>

<ul>
<li><strong>listen stats :1936</strong>: configures HAProxy's stats page to be accessible on port 1936 (i.e. http://haproxy_www_public_IP:1936 )</li>
<li><strong>stats scope ...</strong>: collect stats on the specified frontend or backend</li>
<li><strong>stats uri /</strong>: specifies the URI of the stats page as <em>/</em> </li>
<li><strong>stats realm Haproxy\ Statistics</strong>: enable statistics and set authentication realm (pop-up authentication) name, used in conjunction with <em>stats auth</em> option</li>
<li><strong>stats auth haproxy:password</strong>: specifies authentication credentials for the stats page. Change the username and password to your own</li>
</ul>

<p>Now save and quit. When you start HAProxy, the stats page will be available via <strong>http://haproxy_www_public_ip:1936/</strong> once you start your HAProxy service. HAProxy is now ready to be started, but let's enable logging first.</p>

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

<h2 id="start-haproxy">Start HAProxy</h2>

<p><strong>On haproxy-www</strong>, start HAProxy to make your config changes take effect:</p>
<pre class="code-pre "><code langs="">sudo service haproxy restart
</code></pre>
<h2 id="reverse-proxy-complete">Reverse Proxy Complete</h2>

<p>Now your applications are accessible through the same domain, <em>example.com</em>, via a layer 7 reverse proxy, but they are not yet load balanced. Your environment should look like the following diagram:</p>

<p><img src="https://assets.digitalocean.com/articles/HAProxy/l7/layer_7_proxy_no_lb.png" alt="Reverse Proxy With No Load Balancing" /></p>

<p>In accordance with the frontend that we defined earlier, here is a description of how HAProxy will forward your traffic:</p>

<ul>
<li><strong>http://example.com/wordpress</strong>: any requests that begin with <em>/wordpress</em> will be sent to <em>wordpress-backend</em> (which consists of your <em>wordpress-1</em> server)</li>
<li><strong>http://example.com/</strong>: any other requests will be sent to <em>web-backend</em> (which consists of your <em>web-1</em> server)</li>
</ul>

<p>If all you wanted to do was host multiple applications on a single domain, you are done! If you want to load balance your applications, you will need to read on.</p>

<h2 id="how-to-add-load-balancing">How to Add Load Balancing</h2>

<h3 id="load-balancing-web-1">Load Balancing web-1</h3>

<p>To load balance a basic web server, all you need to do is create a new web server that has identical configuration and content as your original. We will call this new server: <strong>web-2</strong>.</p>

<p>You have two options when creating the new VPS:</p>

<ol>
<li>If you have the option to create a new VPS from a snapshot of <em>web-1</em>, that is the simplest way to create <em>web-2</em></li>
<li>Create it from scratch. Install all the same software, configure it identically, then copy the contents of your Nginx server root from <em>web-1</em> to <em>web-2</em> using rsync (See <a href="https://indiareads/community/articles/how-to-use-rsync-to-sync-local-and-remote-directories-on-a-vps">Rsync Tutorial</a>).</li>
</ol>

<p><strong>Note</strong>: Both of the aforementioned methods do a one time copy of your server root contents. If you update any of your files on one of your server nodes, <em>web-1</em> or <em>web-2</em>, make sure you synchronize the files again.</p>

<p>After your identical web server has been set up, add it to the <em>web-backend</em> in the HAProxy configuration.</p>

<p>On <strong>haproxy-www</strong>, edit haproxy.cfg:</p>
<pre class="code-pre "><code langs="">sudo vi /etc/haproxy/haproxy.cfg
</code></pre>
<p>Find the <em>web-backend</em> section of the configuration:</p>

<pre>
backend web-backend
   server web-1 <span class="highlight">web_1_private_IP</span>:80 check
</pre>

<p>Then add your <em>web-2</em> server on the next line:</p>

<pre>
   server web-2 <span class="highlight">web_2_private_IP</span>:80 check
</pre>

<p>Save and quit. Now reload HAProxy to put your change into effect:</p>
<pre class="code-pre "><code langs="">sudo service haproxy reload
</code></pre>
<p>Now your <em>web-backend</em> has two servers handling all of your non-WordPress traffic! It is load balanced!`</p>

<h3 id="load-balancing-wordpress-1">Load Balancing wordpress-1</h3>

<p>Load balancing an application such as WordPress is slightly more complicated than load balancing a static web server because you have to worry about things like synchronizing uploaded files and additional database users.</p>

<p>All of the steps that are required to create an additional, identical WordPress server are described in another load balancing tutorial: <a href="https://indiareads/community/articles/how-to-use-haproxy-as-a-layer-4-load-balancer-for-wordpress-application-servers-on-ubuntu-14-04">How To Use HAProxy as a Layer 4 Load Balancer for WordPress</a>. Complete the three following steps from that tutorial to create your second WordPress server, <em>wordpress-2</em>:</p>

<ol>
<li><a href="https://indiareads/community/articles/how-to-use-haproxy-as-a-layer-4-load-balancer-for-wordpress-application-servers-on-ubuntu-14-04#CreateYourSecondWebApplicationServer">Create Your Second Web Application Server</a></li>
<li><a href="https://indiareads/community/articles/how-to-use-haproxy-as-a-layer-4-load-balancer-for-wordpress-application-servers-on-ubuntu-14-04#SynchronizeWebApplicationFiles">Synchronize Web Application Files</a></li>
<li><a href="https://indiareads/community/articles/how-to-use-haproxy-as-a-layer-4-load-balancer-for-wordpress-application-servers-on-ubuntu-14-04#CreateaNewDatabaseUser">Create a New Database User</a></li>
</ol>

<p>Stop once you get to the section that is called <strong>Not Yet Load Balanced</strong>.</p>

<p>Once you have <em>wordpress-2</em> created and you have your database set up correctly, all you have to do is add it to your <em>wordpress-backend</em> in the HAProxy configuration.</p>

<p>On <strong>haproxy-www</strong>, edit haproxy.cfg:</p>
<pre class="code-pre "><code langs="">sudo vi /etc/haproxy/haproxy.cfg
</code></pre>
<p>Find the <em>wordpress-backend</em> section of the configuration:</p>

<pre>
backend wordpress-backend
   server wordpress-1 <span class="highlight">wordpress_1_private_IP</span>:80 check
</pre>

<p>Then add your <em>wordpress-2</em> server on the next line:</p>

<pre>
   server wordpress-2 <span class="highlight">wordpress_2_private_IP</span>:80 check
</pre>

<p>Save and quit. Now reload HAProxy to put your change into effect:</p>
<pre class="code-pre "><code langs="">sudo service haproxy reload
</code></pre>
<p>Now your <em>wordpress-backend</em> has two servers handling all of your WordPress traffic! It is load balanced!</p>

<h2 id="conclusion">Conclusion</h2>

<p>Now that you have completed this tutorial, you should be able to expand on the reverse proxying and load balancing concepts to add more applications and servers to your environment to make it fit your needs better. Remember that there are limitless ways to configure your environment, and you may need to dig into the HAProxy Configuration Manual if you have more complex requirements.</p>

<p>Additionally, if you are looking for another way to improve the performance of your WordPress instance, you may want to look into MySQL replication. Check out this tutorial that describes how set that up with WordPress:</p>

<ul>
<li><a href="https://indiareads/community/articles/how-to-optimize-wordpress-performance-with-mysql-replication-on-ubuntu-14-04">How To Optimize WordPress Performance With MySQL Replication On Ubuntu 14.04</a></li>
</ul>

<div class="author">By Mitchell Anicas</div>

    