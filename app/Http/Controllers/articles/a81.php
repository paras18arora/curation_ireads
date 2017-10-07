<link rel='stylesheet' href='digital_ocean.css'><br><img src=https://community-cdn-digitalocean-com.global.ssl.fastly.net/assets/tutorials/images/large/VarnishCache_Twitter.png?1426699708/> <br> 
      <h3 id="introduction">Introduction</h3>

<p>In this tutorial, we will cover how to use Varnish Cache 4.0 to improve the performance of your existing web server. We will also show you a way to add HTTPS support to Varnish, with Nginx performing the SSL termination. We will assume that you already have a web application server set up, and we will use a generic LAMP (Linux, Apache, MySQL, PHP) server as our starting point.</p>

<p>Varnish Cache is a caching HTTP reverse proxy, or HTTP accelerator, which reduces the time it takes to serve content to a user. The main technique it uses is caching responses from a web or application server in memory, so future requests for the same content can be served without having to retrieve it from the web server. Performance can be improved greatly in a variety of environments, and it is especially useful when you have content-heavy dynamic web applications. Varnish was built with caching as its primary feature but it also has other uses, such as reverse proxy load balancing.</p>

<p>In many cases, Varnish works well with its defaults but keep in mind that it must be tuned to improve performance with certain applications, especially ones that use cookies. In depth tuning of Varnish is outside of the scope of this tutorial.</p>

<h2 id="prerequisites">Prerequisites</h2>

<p>In this tutorial, we assume that you already have a web application server that is listening on HTTP (port 80) on its private IP address. If you do not already have a web server set up, use the following link to set up your own LAMP stack: <a href="https://indiareads/community/tutorials/how-to-install-linux-apache-mysql-php-lamp-stack-on-ubuntu-14-04">How To Install Linux, Apache, MySQL, PHP (LAMP) stack on Ubuntu 14.04</a>. We will refer to this server as <strong>LAMP_VPS</strong>.</p>

<p><img src="https://assets.digitalocean.com/articles/varnish/prereq.png" alt="Existing Environment" /></p>

<p>You will need to create a new Ubuntu 14.04 VPS which will be used for your Varnish installation. Create a non-root user with sudo permissions by completing steps 1-4 in the <a href="https://indiareads/community/articles/initial-server-setup-with-ubuntu-14-04">initial server setup for Ubuntu 14.04 guide</a>. We will refer to this server as <strong>Varnish_VPS</strong>.</p>

<p>Keep in mind that the Varnish server will be receiving user requests and should be adequately sized for the amount of traffic you expect to receive. </p>

<h2 id="our-goal">Our Goal</h2>

<p><img src="https://assets.digitalocean.com/articles/varnish/goal.png" alt="Our Goal" /></p>

<p>Our goal is to set up Varnish Cache in front of our web application server, so requests can be served quickly and efficiently. After the caching is set up, we will show you how to add HTTPS support to Varnish, by utlizing Nginx to handle incoming SSL requests. After your setup is complete, both your HTTP and HTTPS traffic will see the performance benefits of caching.</p>

<p>Now that you have the prerequisites set up, and you know what you are trying to build, let's get started!</p>

<h2 id="install-varnish">Install Varnish</h2>

<p>The recommended way to get the latest release of Varnish 4.0 is to install the package avaiable through the official repository.</p>

<p>Ubuntu 14.04 comes with <code>apt-transport-https</code>, but just run the following command on <em>Varnish_VPS</em> to be sure:</p>
<pre class="code-pre "><code langs="">sudo apt-get install apt-transport-https
</code></pre>
<p>Now add the Varnish GPG key to apt:</p>
<pre class="code-pre "><code langs="">curl https://repo.varnish-cache.org/ubuntu/GPG-key.txt | sudo apt-key add -
</code></pre>
<p>Then add the Varnish 4.0 repository to your list of apt sources:</p>
<pre class="code-pre "><code langs="">sudo sh -c 'echo "deb https://repo.varnish-cache.org/ubuntu/ trusty varnish-4.0" >> /etc/apt/sources.list.d/varnish-cache.list'
</code></pre>
<p>Finally, update apt-get and install Varnish with the following commands:</p>
<pre class="code-pre "><code langs="">sudo apt-get update
sudo apt-get install varnish
</code></pre>
<p>By default, Varnish is configured to listen on port <code>6081</code> and expects your web server to be on the same server and listening on port <code>8080</code>. Open a browser and go to port 6081 of your server (replace the highlighted part with your public IP address or domain):</p>
<pre class="code-pre "><code langs="">http://<span class="highlight">varnish_VPS_public_IP</span>:6081
</code></pre>
<p>Because we installed Varnish on a new VPS, visiting port <code>6081</code> on your server's public IP address or domain name will return the following error page:</p>

<p><img src="https://assets.digitalocean.com/articles/varnish/varnish_initial_error.png" alt="503 Error" /></p>

<p>This indicates that Varnish is installed and running, but it can't find the web server that it is supposed to be caching. Let's configure it to use our web server as a backend now.</p>

<h2 id="configure-varnish">Configure Varnish</h2>

<p>First, we will configure Varnish to use our <em>LAMP_VPS</em> as a backend.</p>

<p>The Varnish configuration file is located at <code>/etc/varnish/default.vcl</code>. Let's edit it now:</p>
<pre class="code-pre "><code langs="">sudo vi /etc/varnish/default.vcl
</code></pre>
<p>Find the following lines:</p>
<pre class="code-pre "><code langs="">backend default {
    .host = "127.0.0.1";
    .port = "8080";
}
</code></pre>
<p>And change the values of <code>host</code> and <code>port</code> match your LAMP server private IP address and listening port, respectively.  Note that we are assuming that your web application is listening on its private IP address and port 80. If this is not the case, modify the configuration to match your needs:</p>
<pre class="code-pre "><code langs="">backend default {
    .host = "<span class="highlight">LAMP_VPS_private_IP</span>";
    .port = "<span class="highlight">80</span>";
}
</code></pre>
<p>Varnish has a feature called "grace mode" that, when enabled, instructs Varnish to serve a cached copy of requested pages if your web server backend goes down and becomes unavailable. Let's enable that now. Find the following <code>sub vcl_backend_response</code> block, and add the following highlighted lines to it:</p>
<pre class="code-pre "><code langs="">sub vcl_backend_response {
    <span class="highlight">set beresp.ttl = 10s;</span>
    <span class="highlight">set beresp.grace = 1h;</span>
}
</code></pre>
<p>This sets the grace period of cached pages to one hour, meaning Varnish will continue to serve cached pages for up to an hour if it can't reach your web server to look for a fresh copy. This can be handy if your application server goes down and you prefer that stale content is served to users instead of an error page (like the 503 error that we've seen previously), while you bring your web server back up.</p>

<p>Save and exit the <code>default.vcl</code> file.</p>

<p>We will want to set Varnish to listen on the default HTTP port (80), so your users will be able to access your site without adding an unusual port number to your URL. This can be set in the <code>/etc/default/varnish</code> file. Let's edit it now:</p>
<pre class="code-pre "><code langs="">sudo vi /etc/default/varnish
</code></pre>
<p>You will see a lot of lines, but most of them are commented out. Find the following <code>DAEMON_OPTS</code> line (it should be uncommented already):</p>
<pre class="code-pre "><code langs="">DAEMON_OPTS="-a :6081 \
</code></pre>
<p>The <code>-a</code> option is used to assign the address and port that Varnish will listen for requests on. Let's change it to listen to the default HTTP port, port 80. After your modification, it should look like this:</p>
<pre class="code-pre "><code langs="">DAEMON_OPTS="-a :<span class="highlight">80</span> \
</code></pre>
<p>Save and exit.</p>

<p>Now restart Varnish to put the changes into effect:</p>
<pre class="code-pre "><code langs="">sudo service varnish restart
</code></pre>
<p>Now test it out with a web browser, by visiting your Varnish server by its public IP address, on port 80 (HTTP) this time:</p>
<pre class="code-pre "><code langs="">http://<span class="highlight">varnish_VPS_public_IP</span>
</code></pre>
<p>You should see the same thing that is served from your LAMP_VPS. In our case, it's just a plain Apache2 Ubuntu page:</p>

<p><img src="https://assets.digitalocean.com/articles/varnish/apache_default.png" alt="Apache2 Ubuntu Default Page" /></p>

<p>At this point, Varnish is caching our application server--hopefully will you see performance benefits in decreased response time. If you had a domain name pointing to your existing application server, you may change its DNS entry to point to your <em>Varnish<em>VPS</em>public_IP</em>.</p>

<p>Now that we have the basic caching set up, let's add SSL support with Nginx!</p>

<h2 id="ssl-support-with-nginx-optional">SSL Support with Nginx (Optional)</h2>

<p>Varnish does not support SSL termination natively, so we will install Nginx for the sole purpose of handling HTTPS traffic. We will cover the steps to install and configure Nginx with a self-signed SSL certificate, and reverse proxy traffic from an HTTPS connection to Varnish over HTTP.</p>

<p>If you would like a more detailed explanation of setting up a self-signed SSL certificate with Nginx, refer to this link: <a href="https://indiareads/community/tutorials/how-to-create-a-ssl-certificate-on-nginx-for-ubuntu-14-04">SSL with Nginx for Ubuntu</a>. If you want to try out a certificate from StartSSL, <a href="https://indiareads/community/tutorials/how-to-set-up-apache-with-a-free-signed-ssl-certificate-on-a-vps">here is a tutorial that covers that</a>.</p>

<p>Let's install Nginx.</p>

<h3 id="install-nginx">Install Nginx</h3>

<p>On <em>Varnish_VPS</em>, let's install Nginx with the following apt command:</p>
<pre class="code-pre "><code langs="">sudo apt-get install nginx
</code></pre>
<p>After the installation is complete, you will notice that Nginx is not running. This is because it is configured to listen on port 80 by default, but Varnish is already using that port. This is fine because we want to listen on the default HTTPS port, port 443.</p>

<p>Let's generate the SSL certificate that we will use.</p>

<h3 id="generate-self-signed-ssl-certificate">Generate Self-signed SSL Certificate</h3>

<p>On <em>Varnish_VPS</em>, create a directory where SSL certificate can be placed:</p>
<pre class="code-pre "><code langs="">sudo mkdir /etc/nginx/ssl
</code></pre>
<p>Generate a self-signed, 2048-bit SSL key and certicate pair:</p>
<pre class="code-pre "><code langs="">sudo openssl req -x509 -nodes -days 365 -newkey rsa:2048 -keyout /etc/nginx/ssl/nginx.key -out /etc/nginx/ssl/nginx.crt
</code></pre>
<p>Make sure that you set <code>common name</code> to match your domain name. This particular certificate will expire in a year.</p>

<p>Now that we have our certificate in place, let's configure Nginx to use it.</p>

<h3 id="configure-nginx">Configure Nginx</h3>

<p>Open the default Nginx server block configuration for editing:</p>
<pre class="code-pre "><code langs="">sudo vi /etc/nginx/sites-enabled/default
</code></pre>
<p>Delete everything in the file and replace it with the following (and change the <code>server_name</code> to match your domain name):</p>
<pre class="code-pre "><code class="code-highlight language-nginx">server {
        listen 443 ssl;

        server_name <span class="highlight">example.com</span>;
        ssl_certificate /etc/nginx/ssl/nginx.crt;
        ssl_certificate_key /etc/nginx/ssl/nginx.key;

        location / {
            proxy_pass http://127.0.0.1:80;
            proxy_set_header X-Real-IP  $remote_addr;
            proxy_set_header X-Forwarded-For $proxy_add_x_forwarded_for;
            proxy_set_header X-Forwarded-Proto https;
            proxy_set_header X-Forwarded-Port 443;
            proxy_set_header Host $host;
        }
}
</code></pre>
<p>Save and exit. The above configuration has a few important lines that we will explain in more detail:</p>

<ul>
<li><strong>ssl_certificate</strong>: specifies SSL certificate location</li>
<li><strong>ssl<em>certificate</em>key</strong>: specifies SSL key location</li>
<li><strong>listen 443 ssl</strong>: configures Nginx to listen on port 443</li>
<li><strong>server_name</strong>: specifies your server name, and should match the common name of your SSL certificate</li>
<li><strong>proxy_pass http://127.0.0.1:80;</strong>: redirects traffic to Varnish (which is running on port 80 of 127.0.0.1 (i.e. <code>localhost</code>)</li>
</ul>

<p>The other <code>proxy_set_header</code> lines tell Nginx to forward information, such as the original user's IP address, along with any user requests.</p>

<p>Now let's start Nginx so our server can handle HTTPS requests.</p>
<pre class="code-pre "><code langs="">sudo service nginx start
</code></pre>
<p>Now test it out with a web browser, by visiting your Varnish server by its public IP address, on port 443 (HTTPS) this time:</p>
<pre class="code-pre "><code langs="">https://<span class="highlight">varnish_VPS_public_IP</span>
</code></pre>
<p><strong>Note:</strong> If you used a self-signed certificate, you will see a warning saying something like "The site's security certificate is not trusted". Since you know you just created the certificate, it is safe to proceed.</p>

<p>Again, you should see the same application page as before. The difference is that you are actually visiting the Nginx server, which handles the SSL encryption and forwards the unencrypted request to Varnish, which treats the request like it normally does.</p>

<h3 id="configure-backend-web-server">Configure Backend Web Server</h3>

<p>If your backend web server is binding to all of its network interfaces (i.e. public and private network interfaces), you will want to modify your web server configuration so it is only listening on its private interface. This is to prevent users from accessing your backend web server directly via its public IP address, which would bypass your Varnish Cache.</p>

<p>In Apache or Nginx, this would involve assigning the value of the <code>listen</code> directives to bind to the private IP address of your backend server.</p>

<h2 id="troubleshooting-varnish">Troubleshooting Varnish</h2>

<p>If you are having trouble getting Varnish to serve your pages properly, here are a few commands that will help you see what Varnish is doing behind the scenes.</p>

<h3 id="stats">Stats</h3>

<p>If you want to get an idea of how well your cache is performing, you will want to take a look at the <code>varnishstat</code> command. Run it like this:</p>
<pre class="code-pre "><code langs="">varnishstat
</code></pre>
<p>You will a screen that looks like the following:</p>

<p><img src="https://assets.digitalocean.com/articles/varnish/varnishstat.png" alt="Varnish Stats" /></p>

<p>There is a large variety of stats that come up, and using the up/down arrows to scroll will show you a short description of each item. The <code>cache_hit</code> stat shows you how many requests were served with a cached result--you want this number to be as close to the total number of client requests (<code>client_req</code>) as possible.</p>

<p>Press <code>q</code> to quit.</p>

<h3 id="logs">Logs</h3>

<p>If you want to get a detailed view of how Varnish is handling each individual request, in the form of a streaming log, you will want to use the <code>varnishlog</code> command. Run it like this:</p>
<pre class="code-pre "><code langs="">varnishlog
</code></pre>
<p>Once it is running, try and access your Varnish server via a web browser. For each request you send to Varnish, you will see a detailed output that can be used to help troubleshoot and tune your Varnish configuration.</p>

<p>Press <code>CTRL + C</code> to quit.</p>

<h2 id="conclusion">Conclusion</h2>

<p>Now that your web server has a Varnish Cache server in front of it, you will see improved performance in most cases. Remember that Varnish is very powerful and tuneable, and it may require additional tweaks to get the full benefit from it.</p>

    