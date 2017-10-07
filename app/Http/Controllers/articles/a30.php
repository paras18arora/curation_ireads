<link rel='stylesheet' href='digital_ocean.css'><br><img src=https://community-cdn-digitalocean-com.global.ssl.fastly.net/assets/tutorials/images/large/installnginx.twitter.jpg?1462909180/> <br> 
      <h3 id="introduction">Introduction</h3>

<p>Nginx is one of the most popular web servers in the world and is responsible for hosting some of the largest and highest-traffic sites on the internet.  It is more resource-friendly than Apache in most cases and can be used as a web server or a reverse proxy.</p>

<p>In this guide, we'll discuss how to get Nginx installed on your Ubuntu 16.04 server.</p>

<h2 id="prerequisites">Prerequisites</h2>

<p>Before you begin this guide, you should have a regular, non-root user with <code>sudo</code> privileges configured on your server.  You can learn how to configure a regular user account by following our <a href="https://indiareads/community/tutorials/initial-server-setup-with-ubuntu-16-04">initial server setup guide for Ubuntu 16.04</a>.</p>

<p>When you have an account available, log in as your non-root user to begin.</p>

<h2 id="step-1-install-nginx">Step 1: Install Nginx</h2>

<p>Nginx is available in Ubuntu's default repositories, so the installation is rather straight forward.</p>

<p>Since this is our first interaction with the <code>apt</code> packaging system in this session, we will update our local package index so that we have access to the most recent package listings.  Afterwards, we can install <code>nginx</code>:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo apt-get update
</li><li class="line" prefix="$">sudo apt-get install nginx
</li></ul></code></pre>
<p>After accepting the procedure, <code>apt-get</code> will install Nginx and any required dependencies to your server.</p>

<h2 id="step-2-adjust-the-firewall">Step 2: Adjust the Firewall</h2>

<p>Before we can test Nginx, we need to reconfigure our firewall software to allow access to the service.  Nginx registers itself as a service with <code>ufw</code>, our firewall, upon installation.  This makes it rather easy to allow Nginx access.</p>

<p>We can list the applications configurations that <code>ufw</code> knows how to work with by typing:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo ufw app list
</li></ul></code></pre>
<p>You should get a listing of the application profiles:</p>
<pre class="code-pre "><code langs=""><div class="secondary-code-label " title="Output">Output</div>Available applications:
  Nginx Full
  Nginx HTTP
  Nginx HTTPS
  OpenSSH
</code></pre>
<p>As you can see, there are three profiles available for Nginx:</p>

<ul>
<li><strong>Nginx Full</strong>: This profile opens both port 80 (normal, unencrypted web traffic) and port 443 (TLS/SSL encrypted traffic)</li>
<li><strong>Nginx HTTP</strong>: This profile opens only port 80 (normal, unencrypted web traffic)</li>
<li><strong>Nginx HTTPS</strong>: This profile opens only port 443 (TLS/SSL encrypted traffic)</li>
</ul>

<p>It is recommended that you enable the most restrictive profile that will still allow the traffic you've configured.  Since we haven't configured SSL for our server yet, in this guide, we will only need to allow traffic on port 80.</p>

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
<h2 id="step-3-check-your-web-server">Step 3: Check your Web Server</h2>

<p>At the end of the installation process, Ubuntu 16.04 starts Nginx.  The web server should already be up and running.</p>

<p>We can check with the <code>systemd</code> init system to make sure the service is running by typing:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">systemctl status nginx
</li></ul></code></pre><pre class="code-pre "><code langs=""><div class="secondary-code-label " title="Output">Output</div>● nginx.service - A high performance web server and a reverse proxy server
   Loaded: loaded (/lib/systemd/system/nginx.service; enabled; vendor preset: enabled)
   Active: <span class="highlight">active (running)</span> since Mon 2016-04-18 16:14:00 EDT; 4min 2s ago
 Main PID: 12857 (nginx)
   CGroup: /system.slice/nginx.service
           ├─12857 nginx: master process /usr/sbin/nginx -g daemon on; master_process on
           └─12858 nginx: worker process
</code></pre>
<p>As you can see above, the service appears to have started successfully.  However, the best way to test this is to actually request a page from Nginx.</p>

<p>You can access the default Nginx landing page to confirm that the software is running properly.  You can access this through your server's domain name or IP address.</p>

<p>If you do not have a domain name set up for your server, you can learn <a href="https://digitalocean.com/community/articles/how-to-set-up-a-host-name-with-digitalocean">how to set up a domain with IndiaReads</a> here.</p>

<p>If you do not want to set up a domain name for your server, you can use your server's public IP address.  If you do not know your server's IP address, you can get it a few different ways from the command line.</p>

<p>Try typing this at your server's command prompt:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">ip addr show eth0 | grep inet | awk '{ print $2; }' | sed 's/\/.*$//'
</li></ul></code></pre>
<p>You will get back a few lines.  You can try each in your web browser to see if they work.</p>

<p>An alternative is typing this, which should give you your public IP address as seen from another location on the internet:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo apt-get install curl
</li><li class="line" prefix="$">curl -4 icanhazip.com
</li></ul></code></pre>
<p>When you have your server's IP address or domain, enter it into your browser's address bar:</p>
<pre class="code-pre "><code langs="">http://<span class="highlight">server_domain_or_IP</span>
</code></pre>
<p>You should see the default Nginx landing page, which should look something like this:</p>

<p><img src="https://assets.digitalocean.com/articles/nginx_1604/default_page.png" alt="Nginx default page" /></p>

<p>This page is simply included with Nginx to show you that the server is running correctly.</p>

<h2 id="step-4-manage-the-nginx-process">Step 4: Manage the Nginx Process</h2>

<p>Now that you have your web server up and running, we can go over some basic management commands.</p>

<p>To stop your web server, you can type:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo systemctl stop nginx
</li></ul></code></pre>
<p>To start the web server when it is stopped, type:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo systemctl start nginx
</li></ul></code></pre>
<p>To stop and then start the service again, type:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo systemctl restart nginx
</li></ul></code></pre>
<p>If you are simply making configuration changes, Nginx can often reload without dropping connections.  To do this, this command can be used:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo systemctl reload nginx
</li></ul></code></pre>
<p>By default, Nginx is configured to start automatically when the server boots.  If this is not what you want, you can disable this behavior by typing:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo systemctl disable nginx
</li></ul></code></pre>
<p>To re-enable the service to start up at boot, you can type:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo systemctl enable nginx
</li></ul></code></pre>
<h2 id="step-5-get-familiar-with-important-nginx-files-and-directories">Step 5: Get Familiar with Important Nginx Files and Directories</h2>

<p>Now that you know how to manage the service itself, you should take a few minutes to familiarize yourself with a few important directories and files.</p>

<h3 id="content">Content</h3>

<ul>
<li><code>/var/www/html</code>: The actual web content, which by default only consists of the default Nginx page you saw earlier, is served out of the <code>/var/www/html</code> directory.  This can be changed by altering Nginx configuration files.</li>
</ul>

<h3 id="server-configuration">Server Configuration</h3>

<ul>
<li><code>/etc/nginx</code>: The nginx configuration directory.  All of the Nginx configuration files reside here.</li>
<li><code>/etc/nginx/nginx.conf</code>: The main Nginx configuration file.  This can be modified to make changes to the Nginx global configuraiton.</li>
<li><code>/etc/nginx/sites-available</code>: The directory where per-site "server blocks" can be stored.  Nginx will not use the configuration files found in this directory unless they are linked to the <code>sites-enabled</code> directory (see below).  Typically, all server block configuration is done in this directory, and then enabled by linking to the other directory.</li>
<li><code>/etc/nginx/sites-enabled/</code>: The directory where enabled per-site "server blocks" are stored.  Typically, these are created by linking to configuration files found in the <code>sites-available</code> directory.</li>
<li><code>/etc/nginx/snippets</code>: This directory contains configuration fragments that can be included elsewhere in the Nginx configuration.  Potentially repeatable configuration segments are good candidates for refactoring into snippets.</li>
</ul>

<h3 id="server-logs">Server Logs</h3>

<ul>
<li><code>/var/log/nginx/access.log</code>: Every request to your web server is recorded in this log file unless Nginx is configured to do otherwise.</li>
<li><code>/var/log/nginx/error.log</code>: Any Nginx errors will be recorded in this log.</li>
</ul>

<h2 id="conclusion">Conclusion</h2>

<p>Now that you have your web server installed, you have many options for the type of content to serve and the technologies you want to use to create a richer experience.</p>

<p>Learn <a href="https://indiareads/community/articles/how-to-set-up-nginx-server-blocks-virtual-hosts-on-ubuntu-14-04-lts">how to use Nginx server blocks</a> here.  If you'd like to build out a more complete application stack, check out this article on <a href="https://indiareads/community/articles/how-to-install-linux-nginx-mysql-php-lemp-stack-on-ubuntu-14-04">how to configure a LEMP stack on Ubuntu 14.04</a>.</p>

    