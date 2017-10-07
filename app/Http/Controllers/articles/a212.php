<link rel='stylesheet' href='digital_ocean.css'><br> <br> 
      <h3 id="introduction">Introduction</h3>

<p>Apache Tomcat is a web server and servlet container designed to serve Java applications.  Frequently used in production enterprise deployments and for smaller application needs, Tomcat is both flexible and powerful.</p>

<p>In this guide, we will discuss how to secure your Ubuntu 16.04 Tomcat installation with SSL.  By default, upon installation, all communication between the Tomcat server and clients is unencrypted, including any passwords entered or any sensitive data.  There are a number of ways that we can incorporate SSL into our Tomcat installation.  This guide will cover how to set up a SSL-enabled proxy server to securely negotiate with clients and then hand requests off to Tomcat.</p>

<p>We will cover how to set this up with both <strong>Apache</strong> and <strong>Nginx</strong>.</p>

<h2 id="why-a-reverse-proxy">Why a Reverse Proxy?</h2>

<p>There are a number of ways that you can set up SSL for a Tomcat installation, each with its set of trade-offs.  After learning that Tomcat has the ability to encrypt connections natively, it might seem strange that we'd discuss a reverse proxy solution.</p>

<p>SSL with Tomcat has a number of drawbacks that make it difficult to manage:</p>

<ul>
<li><strong>Tomcat, when run as recommended with an unprivileged user, cannot bind to restricted ports like the conventional SSL port 443</strong>: There are workarounds to this, like using the <code>authbind</code> program to map an unprivileged program with a restricted port, setting up port forwarding with a firewall, etc., but they still represent additional complexity.</li>
<li><strong>SSL with Tomcat is not as widely supported by other software</strong>: Projects like Let's Encrypt provide no native way of interacting with Tomcat.  Furthermore, the Java keystore format requires conventional certificates to be converted before use, which complicates automation.</li>
<li><strong>Conventional web servers release more frequently than Tomcat</strong>: This can have significant security implications for your applications.  For instance, the supported Tomcat SSL cipher suite can become out-of-date quickly, leaving your applications with suboptimal protection.  In the event that security updates are needed, it is likely easier to update a web server than your Tomcat installation.</li>
</ul>

<p>A reverse proxy solution bypasses many of these issues by simply putting a strong web server in front of the Tomcat installation.  The web server can handle client requests with SSL, functionality it is specifically designed to handle.  It can then proxy requests to Tomcat running in its normal, unprivileged configuration.</p>

<p>This separation of concerns simplifies the configuration, even if it does mean running an additional piece of software.</p>

<h2 id="prerequisites">Prerequisites</h2>

<p>In order to complete this guide, you will have to have Tomcat already set up on your server.  This guide will assume that you used the instructions in our <a href="https://indiareads/community/tutorials/how-to-install-apache-tomcat-8-on-ubuntu-16-04">Tomcat 8 on Ubuntu 16.04 installation guide</a> to get set up.</p>

<p>When you have a Tomcat up and running, continue below with the section for your preferred web server.  <strong>Apache</strong> starts directly below, while the <strong>Nginx</strong> configuration can be found by skipping ahead a bit.</p>

<h2 id="option-1-proxying-with-the-apache-web-server-39-s-mod_jk">(Option 1) Proxying with the Apache Web Server's <code>mod_jk</code></h2>

<p>The Apache web server has a module called <code>mod_jk</code> which can communicate directly with Tomcat using the Apache JServ Protocol.  A connector for this protocol is enabled by default within Tomcat, so Tomcat is already ready to handle these requests.</p>

<h3 id="section-prerequisites">Section Prerequisites</h3>

<p>Before we can discuss how to proxy Apache web server connections to Tomcat, you must install and secure an Apache web server.</p>

<p>You can install the Apache web server by following step 1 of <a href="https://indiareads/community/tutorials/how-to-install-linux-apache-mysql-php-lamp-stack-on-ubuntu-16-04">this guide</a>.  Do not install MySQL or PHP.</p>

<p>Afterwards, you will need to set up SSL on the server.  The way you do this will depend on whether you have a domain name or not.</p>

<ul>
<li><strong>If you have a domain name...</strong> the easiest way to secure your server is with Let's Encrypt, which provides free, trusted certificates.  Follow our <a href="https://indiareads/community/tutorials/how-to-secure-apache-with-let-s-encrypt-on-ubuntu-16-04">Let's Encrypt guide for Apache</a> to set this up.</li>
<li><strong>If you do not have a domain...</strong> and you are just using this configuration for testing or personal use, you can use a self-signed certificate instead.  This provides the same type of encryption, but without domain validation.  Follow our <a href="https://indiareads/community/tutorials/how-to-create-a-self-signed-ssl-certificate-for-apache-in-ubuntu-16-04">self-signed SSL guide for Apache</a> to get set up.</li>
</ul>

<p>When you are finished with these steps, continue below to learn how to hook up the Apache web server to your Tomcat installation.</p>

<h3 id="step-1-install-and-configure-mod_jk">Step 1: Install and Configure <code>mod_jk</code></h3>

<p>First, we need to install the <code>mod_jk</code> module.  The Apache web server uses this to communicate with Tomcat using the Apache JServ Protocol.</p>

<p>We can install <code>mod_jk</code> from Ubuntu's default repositories.  Update the local package index and install by typing:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo apt-get update
</li><li class="line" prefix="$">sudo apt-get install libapache2-mod-jk
</li></ul></code></pre>
<p>The module will be enabled automatically upon installation.</p>

<p>Next, we need to configure the module. The main configuration file is located at <code>/etc/libapache2-mod-jk/workers.properties</code>.  Open this file now in your text editor:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo nano /etc/libapache2-mod-jk/workers.properties
</li></ul></code></pre>
<p>Inside, find the <code>workers.tomcat_home</code> directive.  Set this to your Tomcat installation home directory.  For our Tomcat installation, that would be <code>/opt/tomcat</code>:</p>
<div class="code-label " title="/etc/libapache2-mod-jk/workers.properties">/etc/libapache2-mod-jk/workers.properties</div><pre class="code-pre "><code langs="">workers.tomcat_home=<span class="highlight">/opt/tomcat</span>
</code></pre>
<p>Save and close the file when you are finished.</p>

<h3 id="step-2-adjust-the-apache-virtual-host-to-proxy-with-mod_jk">Step 2: Adjust the Apache Virtual Host to Proxy with <code>mod_jk</code></h3>

<p>Next, we need to adjust our Apache Virtual Host to proxy requests to our Tomcat installation.</p>

<p>The correct Virtual Host file to open will depend on which method you used to set up SSL.</p>

<p>If you set up a self-signed SSL certificate using the guide linked to above, open the <code>default-ssl.conf</code> file:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo nano /etc/apache2/sites-available/default-ssl.conf
</li></ul></code></pre>
<p>If you set up SSL with Let's Encrypt, the file location will depend on what options you selected during the certificate process.  You can find which Virtual Hosts are involved in serving SSL requests by typing:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo apache2ctl -S
</li></ul></code></pre>
<p>Your output will likely begin with something like this:</p>
<pre class="code-pre line_numbers"><code langs=""><div class="secondary-code-label " title="Output">Output</div><ul class="prefixed"><li class="line" prefix="1">VirtualHost configuration:
</li><li class="line" prefix="2">*:80                   <span class="highlight">example.com</span> (/etc/apache2/sites-enabled/000-default.conf:1)
</li><li class="line" prefix="3">*:443                  is a NameVirtualHost
</li><li class="line" prefix="4">         default server <span class="highlight">example.com</span> (/etc/apache2/sites-enabled/000-default-le-ssl.conf:2)
</li><li class="line" prefix="5">         port 443 namevhost <span class="highlight">example.com</span> (/etc/apache2/sites-enabled/000-default-le-ssl.conf:2)
</li><li class="line" prefix="6">         port 443 namevhost www.<span class="highlight">example.com</span> (/etc/apache2/sites-enabled/default-ssl.conf:2)
</li><li class="line" prefix="7">
</li><li class="line" prefix="8">. . .
</li></ul></code></pre>
<p>Looking at the lines associated with SSL port 443 (lines 3-6 in this example), we can determine which Virtual Hosts files are involved in serving those domains.  Here, we see that both the <code>000-default-le-ssl.conf</code> file and the <code>default-ssl.conf</code> file are involved, so you should edit both of these.  Your results will likely differ:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo nano /etc/apache2/sites-enabled/000-default-le-ssl.conf
</li><li class="line" prefix="$">sudo nano /etc/apache2/sites-enabled/default-ssl.conf
</li></ul></code></pre>
<p>Regardless of which files you have to open, the procedure will be the same.  Somewhere within the <code>VirtualHost</code> tags, you should enter the following:</p>
<pre class="code-pre "><code langs=""><VirtualHost *:443>

    . . .

    <span class="highlight">JKMount /* ajp13_worker</span>

    . . .

</VirtualHost>
</code></pre>
<p>Save and close the file.  Repeat the above process for any other files you identified that need to be edited.</p>

<p>When you are finished, check your configuration by typing:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo apache2ctl configtest
</li></ul></code></pre>
<p>If the output contains <code>Syntax OK</code>, restart the Apache web server process:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo systemctl restart apache2
</li></ul></code></pre>
<p>You should now be able get to your Tomcat installation by visiting the SSL version of your site in your web browser:</p>
<pre class="code-pre "><code langs="">https://<span class="highlight">example.com</span>
</code></pre>
<p>Next, skip past the Nginx configuration below and continue at the section detailing how to restrict access to Tomcat in order to complete your configuration.</p>

<h2 id="option-2-http-proxying-with-nginx">(Option 2) HTTP Proxying with Nginx</h2>

<p>Proxying is also easy with Nginx, if you prefer it to the Apache web server.  While Nginx does not have a module allowing it to speak the Apache JServ Protocol, it can use its robust HTTP proxying capabilities to communicate with Tomcat.</p>

<h3 id="section-prerequisites">Section Prerequisites</h3>

<p>Before we can discuss how to proxy Nginx connections to Tomcat, you must install and secure Nginx.</p>

<p>You can install Nginx by following <a href="https://indiareads/community/tutorials/how-to-install-nginx-on-ubuntu-16-04">our guide on installing Nginx on Ubuntu 16.04</a>.</p>

<p>Afterwards, you will need to set up SSL on the server.  The way you do this will depend on whether you have a domain name or not.</p>

<ul>
<li><strong>If you have a domain name...</strong> the easiest way to secure your server is with Let's Encrypt, which provides free, trusted certificates.  Follow our <a href="https://indiareads/community/tutorials/how-to-secure-nginx-with-let-s-encrypt-on-ubuntu-16-04">Let's Encrypt guide for Nginx</a> to set this up.</li>
<li><strong>If you do not have a domain...</strong> and you are just using this configuration for testing or personal use, you can use a self-signed certificate instead.  This provides the same type of encryption, but without domain validation.  Follow our <a href="https://indiareads/community/tutorials/how-to-create-a-self-signed-ssl-certificate-for-nginx-in-ubuntu-16-04">self-signed SSL guide for Nginx</a> to get set up.</li>
</ul>

<p>When you are finished with these steps, continue below to learn how to hook up the Nginx web server to your Tomcat installation.</p>

<h3 id="step-1-adjusting-the-nginx-server-block-configuration">Step 1: Adjusting the Nginx Server Block Configuration</h3>

<p>Setting up Nginx to proxy to Tomcat is very straight forward.</p>

<p>Begin by opening the server block file associated with your site.  We will assume you are using the default server block file in this guide:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo nano /etc/nginx/sites-available/default
</li></ul></code></pre>
<p>Inside, towards the top of the file, we need to add an <code>upstream</code> block.  This will outline the connection details so that Nginx knows where our Tomcat server is listening.  Place this outside of any of the <code>server</code> blocks defined within the file:</p>
<div class="code-label " title="/etc/nginx/sites-available/default">/etc/nginx/sites-available/default</div><pre class="code-pre "><code langs=""><span class="highlight">upstream tomcat {</span>
    <span class="highlight">server 127.0.0.1:8080 fail_timout=0;</span>
<span class="highlight">}</span>

server {

    . . .
</code></pre>
<p>Next, within the <code>server</code> block defined for port 443, modify the <code>location /</code> block.  We want to pass all requests directly to the <code>upstream</code> block we just defined.  Comment out the current contents and use the <code>proxy_pass</code> directive to pass to the "tomcat" upstream we just defined.</p>

<p>We will also need to include the <code>proxy_params</code> configuration within this block.  This file defines many of the details of how Nginx will proxy the connection:</p>
<div class="code-label " title="/etc/nginx/sites-available/default">/etc/nginx/sites-available/default</div><pre class="code-pre "><code langs="">upstream tomcat {
    server 127.0.0.1:8080 fail_timout=0;
}

server {
    . . .

    location / {
        <span class="highlight">#</span>try_files $uri $uri/ =404;
        <span class="highlight">include proxy_params;</span>
        <span class="highlight">proxy_pass http://tomcat/;</span>
    }

    . . .
}
</code></pre>
<p>When you are finished, save and close the file.</p>

<h3 id="step-2-test-and-restart-nginx">Step 2: Test and Restart Nginx</h3>

<p>Next, test to make sure your configuration changes did not introduce any syntax errors:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo nginx -t
</li></ul></code></pre>
<p>If no errors are reported, restart Nginx to implement your changes:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo systemctl restart nginx
</li></ul></code></pre>
<p>You should now be able get to your Tomcat installation by visiting the SSL version of your site in your web browser:</p>
<pre class="code-pre "><code langs="">https://<span class="highlight">example.com</span>
</code></pre>
<h2 id="restricting-access-to-the-tomcat-installation">Restricting Access to the Tomcat Installation</h2>

<p>Now you have SSL encrypted access to your Tomcat installation, we can lock down the Tomcat installation a bit more.</p>

<p>Since we want all of our requests to Tomcat to come through our proxy, we can configure Tomcat to only listen for connections on the local loopback interface.  This ensures that outside parties cannot attempt to make requests from Tomcat directly.</p>

<p>Open the <code>server.xml</code> file within your Tomcat configuration directory to change these settings:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo nano /opt/tomcat/conf/server.xml
</li></ul></code></pre>
<p>Within this file, we need to modify the <strong>Connector</strong> definitions.  Currently there are two Connectors enabled within the configuration.  One handles normal HTTP requests on port 8080, while the other handles Apache JServ Protocol requests on port 8009.  The configuration will look something like this:</p>
<div class="code-label " title="/opt/tomcat/conf/server.xml">/opt/tomcat/conf/server.xml</div><pre class="code-pre "><code langs="">. . .

    <Connector port="8080" protocol="HTTP/1.1"
               connectionTimeout="20000"
               redirectPort="8443" />
. . .

    <Connector port="8009" protocol="AJP/1.3" redirectPort="8443" />
</code></pre>
<p>In order to restrict access to the local loopback interface, we just need to add an "address" attribute set to <code>127.0.0.1</code> in each of these Connector definitions.  The end result will look like this:</p>
<div class="code-label " title="/opt/tomcat/conf/server.xml">/opt/tomcat/conf/server.xml</div><pre class="code-pre "><code langs="">. . .

    <Connector port="8080" protocol="HTTP/1.1"
               connectionTimeout="20000"
               <span class="highlight">address="127.0.0.1"</span>
               redirectPort="8443" />
. . .

    <Connector port="8009" <span class="highlight">address="127.0.0.1"</span> protocol="AJP/1.3" redirectPort="8443" />
</code></pre>
<p>After you've made those two changes, save and close the file.</p>

<p>We need to restart our Tomcat process to implement these changes:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo systemctl restart tomcat
</li></ul></code></pre>
<p>If you followed our Tomcat installation guide, you have a <code>ufw</code> firewall enabled on your installation.  Now that all of our requests to Tomcat are restricted to the local loopback interface, we can remove the rule from our firewall that allowed external requests to Tomcat.</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo ufw delete allow 8080
</li></ul></code></pre>
<p>Your Tomcat installation should now only be accessible through your web server proxy.</p>

<h2 id="conclusion">Conclusion</h2>

<p>At this point, connections to your Tomcat instance should be encrypted with SSL with the help of a web server proxy.  While configuring a separate web server process might increase the software involved in serving your applications, it simplifies the process of securing your traffic significantly.</p>

    