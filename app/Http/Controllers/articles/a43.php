<link rel='stylesheet' href='digital_ocean.css'><br><img src=https://community-cdn-digitalocean-com.global.ssl.fastly.net/assets/tutorials/images/large/ProtextWordpress_%281%29.png?1461618525/> <br> 
      <h2 id="introduction">Introduction</h2>

<p>WordPress is a popular and powerful CMS (content management system) platform. Its popularity can bring unwanted attention in the form of malicious traffic specially targeted at a WordPress site.</p>

<p>There are many instances where a server that has not been protected or optimized could experience issues or errors after receiving a small amount of malicious traffic. These attacks result in exhaustion of system resources causing services like MySQL to be unresponsive. The most common visual cue of this would be an <code>Error connecting to database</code> message. The web console may also display <code>Out of Memory</code> errors.</p>

<p>This guide will show you how to protect WordPress from XML-RPC attacks on an Ubuntu 14.04 system.</p>

<h2 id="prerequisites">Prerequisites</h2>

<p>For this guide, you need the following:</p>

<ul>
<li>Ubuntu 14.04 Droplet</li>
<li>A non-root user with sudo privileges (<a href="https://indiareads/community/tutorials/initial-server-setup-with-ubuntu-14-04">Initial Server Setup with Ubuntu 14.04</a> explains how to set this up.)</li>
</ul>

<p>We assume you already have WordPress installed on an Ubuntu 14.04 Droplet. There are many ways to install WordPress, but here are two common methods:</p>

<ul>
<li><a href="https://indiareads/community/tutorials/how-to-install-wordpress-on-ubuntu-14-04">How To Install Wordpress on Ubuntu 14.04</a></li>
<li><a href="https://indiareads/community/tutorials/one-click-install-wordpress-on-ubuntu-14-04-with-digitalocean">One-Click Install WordPress on Ubuntu 14.04 with IndiaReads</a></li>
</ul>

<p>All the commands in this tutorial should be run as a non-root user. If root access is required for the command, it will be preceded by <code>sudo</code>. <a href="https://indiareads/community/tutorials/initial-server-setup-with-ubuntu-14-04">Initial Server Setup with Ubuntu 14.04</a> explains how to add users and give them sudo access.</p>

<h2 id="what-is-xml-rpc">What is XML-RPC?</h2>

<p>WordPress utilizes <a href="https://en.wikipedia.org/wiki/XML-RPC">XML-RPC</a> to remotely execute <a href="https://codex.wordpress.org/XML-RPC_WordPress_API">functions</a>. The popular plugin JetPack and the WordPress mobile application are two great examples of how WordPress uses XML-RPC. This same functionality also can be exploited to send thousands of requests to WordPress in a short amount of time. This scenario is effectively a brute force attack.</p>

<h2 id="recognizing-an-xml-rpc-attack">Recognizing an XML-RPC Attack</h2>

<p>The two main ways to recognize an XML-RPC attack are as follows:</p>

<p>1) Seeing the “Error connecting to database” message when your WordPress site is down<br />
2) Finding many entries similar to <code>"POST /xmlrpc.php HTTP/1.0”</code> in your web server logs</p>

<p>The location of your web server log files depends on what Linux distribution you are running and what web server you are running.</p>

<p>For Apache on Ubuntu 14.04, use this command to search for XML-RPC attacks:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">grep xmlrpc /var/log/apache2/access.log
</li></ul></code></pre>
<p>For Nginx on Ubuntu 14.04, use this command to search for XML-RPC attacks:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">grep xmlrpc /var/log/nginx/access.log
</li></ul></code></pre>
<p>Your WordPress site is receiving XML-RPC attacks if the commands above result in many lines of output, similar to this example:</p>
<div class="code-label " title="access.log">access.log</div><pre class="code-pre "><code langs="">111.222.333.444:80 555.666.777.888 - - [01/Jan/2016:16:33:50 -0500] "POST /xmlrpc.php HTTP/1.0" 200 674 "-" "Mozilla/4.0 (compatible: MSIE 7.0; Windows NT 6.0)"
</code></pre>
<p>The rest of this article focuses on three different methods for preventing further XML-RPC attacks.</p>

<h2 id="method-1-installing-the-jetpack-plugin">Method 1: Installing the Jetpack Plugin</h2>

<p>Ideally, you want to prevent XML-RPC attacks before they happen. The <a href="https://wordpress.org/plugins/jetpack/">Jetpack</a> plugin for WordPress can block the XML-RPC multicall method requests with its <em>Protect</em> function. You will still see XML-RPC entries in your web server logs with Jetpack enabled. However, Jetpack will reduce the load on the database from these malicious log in attempts by nearly 90%.</p>

<p><span class="note"><strong>Note:</strong> A WordPress.com account is required to activate the Jetpack plugin.<br /></span></p>

<p>Jetpack installs easily from the WordPress backend. First, log into your WordPress control panel and select <strong>Plugins->Add New</strong> in the left menu.</p>

<p><img src="https://assets.digitalocean.com/articles/wordpress_xmlrpc/plugins_menu.png" alt="WordPress Plugins Menu" /></p>

<p>Jetpack should be automatically listed on the featured Plugins section of the <strong>Add New</strong> page. If you do not see it, you can search for <strong>Jetpack</strong> using the search box.</p>

<p><img src="https://assets.digitalocean.com/articles/wordpress_xmlrpc/jetpack_install.png" alt="Jetpack Install Page" /></p>

<p>Click the <strong>Install Now</strong> button to download, unpack, and install Jetpack. Once it is successfully installed, there will be an <strong>Activate Plugin</strong> link on the page. Click that <strong>Activate Plugin</strong> link. You will be returned to the <strong>Plugins</strong> page and a green header will be at the top that states <strong>Your Jetpack is almost ready!</strong>. Click the <strong>Connect to Wordpress.com</strong> button to complete the activation of Jetpack.</p>

<p><img src="https://assets.digitalocean.com/articles/wordpress_xmlrpc/connect.png" alt="Connect to Wordpress.com button" /></p>

<p>Now, log in with a WordPress.com account. You can also create an account if needed.</p>

<p><img src="https://assets.digitalocean.com/articles/wordpress_xmlrpc/log_in.png" alt="Log into Wordpress.com form" /></p>

<p>After you log into your WordPress.com account, Jetpack will be activated. You will be presented with an option to run <strong>Jump Start</strong> which will automatically enable common features of Jetpack. Click the <strong>Skip</strong> link at this step. </p>

<p><img src="https://assets.digitalocean.com/articles/wordpress_xmlrpc/jump_start.png" alt="Jump Start Screen" />.</p>

<p>The Protect function is automatically enabled, even if you skip the Jump Start process. You can now see a Jetpack dashboard which also displays the Protect function as being Active. White list IP addresses from potentially being blocked by <em>Protect</em> by clicking the gear next to the <strong>Protect</strong> name.</p>

<p><img src="https://assets.digitalocean.com/articles/wordpress_xmlrpc/jetpack_dashboard.png" alt="Jetpack Dashboard" /></p>

<p>Enter the IPv4 or IPv6 addresses that you want to white list and click the <strong>Save</strong> button to update the <em>Protect</em> white list.</p>

<p><img src="https://assets.digitalocean.com/articles/wordpress_xmlrpc/protect.png" alt="Protect Settings" /></p>

<h2 id="method-2-enabling-block-xmlrpc-with-a2enconf">Method 2: Enabling block-xmlrpc with a2enconf</h2>

<p>The <code>a2enconf block-xmlrpc</code> feature was added to the IndiaReads WordPress one-click image in December of 2015. With it, you can block all XML-RPC requests at the web server level.</p>

<p><span class="note"><strong>Note:</strong> This method is only available on a <a href="https://indiareads/community/tutorials/one-click-install-wordpress-on-ubuntu-14-04-with-digitalocean">IndiaReads One-Click WordPress Install</a> created in December 2015 and later.<br /></span></p>

<p>To enable the XML-RPC block script, run the following command on your Droplet with the DO WordPress one-click image installed:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo a2enconf block-xmlrpc
</li></ul></code></pre>
<p>Restart Apache to enable the change:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo service apache2 restart
</li></ul></code></pre>
<p><span class="warning"><strong>Warning:</strong> This method will stop anything that utilizes XML-RPC from functioning, including Jetpack or the WordPress mobile app.<br /></span></p>

<h2 id="method-3-manually-blocking-all-xml-rpc-traffic">Method 3: Manually Blocking All XML-RPC Traffic</h2>

<p>Alternatively, the XML-RPC block can manually be applied to your Apache or Nginx configuration.</p>

<p>For Apache on Ubuntu 14.04, edit the configuration file with the following command:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo nano /etc/apache2/sites-available/000-default.conf
</li></ul></code></pre>
<p>Add the highlighted lines below between the <code><VirtualHost></code> tags.</p>
<div class="code-label " title="Apache VirtualHost Config">Apache VirtualHost Config</div><pre class="code-pre "><code langs=""><VirtualHost>
…    
    <span class="highlight"><files xmlrpc.php></span>
      <span class="highlight">order allow,deny</span>
      <span class="highlight">deny from all</span>
    <span class="highlight"></files></span>
</VirtualHost>
</code></pre>
<p>Save and close this file when you are finished.</p>

<p>Restart the web server to enable the changes:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo service apache2 restart
</li></ul></code></pre>
<p>For Nginx on Ubuntu 14.04, edit the configuration file with the following command (<em>change the path to reflect your configuration file</em>):</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo nano /etc/nginx/sites-available/<span class="highlight">example.com</span>
</li></ul></code></pre>
<p>Add the highlighted lines below within the server block:</p>
<div class="code-label " title="Nginx Server Block File">Nginx Server Block File</div><pre class="code-pre "><code langs="">server {
…
 <span class="highlight">location /xmlrpc.php {</span>
      <span class="highlight">deny all;</span>
    <span class="highlight">}</span>
}
</code></pre>
<p>Save and close this file when you are finished.</p>

<p>Restart the web server to enable the changes:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo service nginx restart
</li></ul></code></pre>
<p><span class="warning"><strong>Warning:</strong> This method will stop anything that utilizes XML-RPC from functioning, including Jetpack or the WordPress mobile app.<br /></span></p>

<h2 id="verifying-attack-mitigation-steps">Verifying Attack Mitigation Steps</h2>

<p>Whatever method you chose to prevent attacks, you should verify that it is working.</p>

<p>If you enable the Jetpack Protect function, you will see XML-RPC requests continue in your web server logs. The frequency should be lower and Jetpack will reduce the load an attack can place on the database server process. Jetpack will also progressively block the attacking IP addresses.</p>

<p>If you manually block all XML-RPC traffic, your logs will still show attempts, but the resulting error code be something other than 200. For example entries in the Apache <code>access.log</code> file may look like:</p>
<div class="code-label " title="access.log">access.log</div><pre class="code-pre "><code langs="">111.222.333.444:80 555.666.777.888 - - [01/Jan/2016:16:33:50 -0500] "POST /xmlrpc.php HTTP/1.0" <span class="highlight">500</span> 674 "-" "Mozilla/4.0 (compatible: MSIE 7.0; Windows NT 6.0)"
</code></pre>
<h2 id="conclusion">Conclusion</h2>

<p>By taking steps to mitigate malicious XML-RPC traffic, your WordPress site will consume less system resources. Exhausting system resources is the most common reason why a WordPress site would go offline on a VPS. The methods of preventing XML-RPC attacks mentioned in this article along with will ensure your WordPress site stays online.</p>

<p>To learn more about brute force attacks on WordPress XML-RPC, read <a href="https://blog.sucuri.net/2015/10/brute-force-amplification-attacks-against-wordpress-xmlrpc.html">Sucuri.net — Brute Force Amplification Attacks Against WordPress XMLRPC</a>.</p>

    