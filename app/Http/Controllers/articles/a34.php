<link rel='stylesheet' href='digital_ocean.css'><br><img src=https://community-cdn-digitalocean-com.global.ssl.fastly.net/assets/tutorials/images/large/NGINX_http2_twitter_mostov.png?1465493195/> <br> 
      <h3 id="introduction">Introduction</h3>

<p>NGINX is a fast and reliable open-source web server. It gained its popularity due to its low memory footprint, high scalability, ease of configuration, and support for the vast majority of different protocols.</p>

<p>One of the protocols supported is the relatively new HTTP/2, which was published in May 2015. The main advantage of HTTP/2 is its high transfer speed for content-rich websites.</p>

<p>This tutorial will help you set up a fast and secure Nginx server with HTTP/2 support.</p>

<h2 id="prerequisites">Prerequisites</h2>

<p>Before we get started, we will need a few things:</p>

<ul>
<li>Ubuntu 16.04 Droplet</li>
<li>Non-root user with sudo privileges (Check out <a href="https://indiareads/community/tutorials/initial-server-setup-with-ubuntu-16-04">Initial Server Setup with Ubuntu 16.04</a> for details.)</li>
<li>Fully registered domain. You can purchase one on <a href="https://namecheap.com">Namecheap</a> or get one for free on <a href="http://www.freenom.com/en/index.html">Freenom</a>.</li>
<li>Make sure your domain name is configured to point to your Droplet. Check out <a href="https://indiareads/community/tutorials/how-to-set-up-a-host-name-with-digitalocean">this tutorial</a> if you need help.</li>
<li>An SSL certificate. <a href="https://indiareads/community/tutorials/how-to-create-a-self-signed-ssl-certificate-for-nginx-in-ubuntu-16-04">Generate a self-signed certificate</a>, <a href="https://indiareads/community/tutorials/how-to-secure-nginx-with-let-s-encrypt-on-ubuntu-16-04">obtain a free one from Let's Encrypt</a>, or <a href="https://indiareads/community/tutorials/how-to-install-an-ssl-certificate-from-a-commercial-certificate-authority">buy one from another provider</a>.</li>
</ul>

<p>That is all. If you have everything listed above, you are ready to go.</p>

<h2 id="differences-between-http-1-1-and-http-2">Differences Between HTTP 1.1 and HTTP/2</h2>

<p>HTTP/2 is a new version of the Hypertext Transport Protocol, which is used on the Web to deliver pages from server to browser. HTTP/2 is the first major update of HTTP in almost two decades: HTTP1.1 was introduced to the public back in 1999 when webpages were usually just a single HTML file with inline CSS stylesheet. The Internet has dramatically changed since then, and now we are facing the limitations of HTTP 1.1 — the protocol limits potential transfer speeds for most modern websites because it downloads parts of a page in a queue (the previous part must download completely before the download of the next part begins), and an average modern webpage requires about 100 request to be downloaded (each request is a picture, js file, css file, etc).</p>

<p>HTTP/2 solves this problem because it brings a few fundamental changes:</p>

<ul>
<li>All requests are downloaded in parallel, not in a queue</li>
<li>HTTP headers are compressed</li>
<li>Pages transfer as a binary, not as a text file, which is more efficient</li>
<li>Servers can “push” data even without the user’s request, which improves speed for users with high latency</li>
</ul>

<p>Even though HTTP/2 does not require encryption, developers of two most popular browsers, Google Chrome and Mozilla Firefox, stated that for the security reasons they will support HTTP/2 only for HTTPS connections. Hence, if you decide to set up servers with HTTP/2 support, you must also secure them with HTTPS.</p>

<h2 id="step-1-—-installing-the-latest-version-of-nginx">Step 1 — Installing the Latest Version of Nginx</h2>

<p>Support of the HTTP/2 protocol was introduced in Nginx 1.9.5. Fortunately, the default repository in Ubuntu 16.04 contains a version higher than this, so we don't have to add a third party repository. </p>

<p>First, update the list of available packages in the apt packaging system: </p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo apt-get update
</li></ul></code></pre>
<p>Then, install Nginx:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo apt-get install nginx
</li></ul></code></pre>
<p>After the installation process finishes, you can check the version of Nginx by typing:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo nginx -v
</li></ul></code></pre>
<p>The output should be similar to the following:</p>
<div class="code-label " title="Ouput of sudo nginx -v">Ouput of sudo nginx -v</div><pre class="code-pre "><code langs="">nginx version: nginx/1.10.0 (Ubuntu)
</code></pre>
<p>In the next several steps, we will modify the Nginx configuration files. Each step will change an Nginx configuration option. We will test the syntax of the configuration file along the way. Finally, we will verify that Nginx supports HTTP/2 and make a few changes to optimize performance.</p>

<h2 id="step-2-—-changing-the-listening-port-and-enabling-http-2">Step 2 — Changing the Listening Port and Enabling HTTP/2</h2>

<p>The first change we will make will be to change the listening port from 80 to 443.</p>

<p>Let's open the configuration file:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo nano /etc/nginx/sites-available/default
</li></ul></code></pre>
<p>By default, Nginx is set to listen to port 80, which is the standard HTTP port:</p>
<div class="code-label " title="/etc/nginx/sites-available/default">/etc/nginx/sites-available/default</div><pre class="code-pre "><code langs="">listen <span class="highlight">80</span> default_server;
listen [::]:<span class="highlight">80</span> default_server;
</code></pre>
<p>As you can see, we have two different <code>listen</code> variables. The first one is for all IPv4 connections. The second one is for IPv6 connections. We will enable encryption for both.</p>

<p>Modify the listening port to <code>443</code>, which is used by the HTTPS protocol:</p>
<div class="code-label " title="/etc/nginx/sites-available/default">/etc/nginx/sites-available/default</div><pre class="code-pre "><code langs="">listen <span class="highlight">443 ssl http2</span> default_server;
listen [::]:<span class="highlight">443 ssl http2</span> default_server;
</code></pre>
<p>Notice that in addition to <code>ssl</code>, we also added <code>http2</code> to the line. This variable tells Nginx to use HTTP/2 with supported browsers. </p>

<h2 id="step-3-—-changing-the-server-name">Step 3 — Changing the Server Name</h2>

<p>The next line after <code>listen</code> is <code>server_name</code>. Here is where we specify which domain should be associated with the configuration file. By default, <code>server_name</code> is set to <code>_</code> (underscore), which means the config file is responsible for all incoming requests. Change <code>_</code> to your actual domain, like this:</p>
<div class="code-label " title="/etc/nginx/sites-available/default">/etc/nginx/sites-available/default</div><pre class="code-pre "><code langs="">server_name <span class="highlight">example.com</span>;
</code></pre>
<p>Save the configuration file, and edit the text editor.</p>

<p>Check the configuration for syntax errors:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo nginx -t
</li></ul></code></pre>
<p>If the syntax is error-free, you will see the following output:</p>
<div class="code-label " title="Output of sudo nginx -t">Output of sudo nginx -t</div><pre class="code-pre "><code langs="">nginx: the configuration file /etc/nginx/nginx.conf syntax is ok
nginx: configuration file /etc/nginx/nginx.conf test is successful
</code></pre>
<h2 id="step-4-— adding-the-ssl-certificates">Step 4 — Adding the SSL Certificates</h2>

<p>Next, you need to configure Nginx for your SSL certificate. If you don’t know what an SSL certificate is or currently don’t have any, please follow one of the tutorials in the Prerequisites section of this article.</p>

<p>Create a directory to store your SSL certificates inside the Nginx configuration directory:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo mkdir /etc/nginx/ssl
</li></ul></code></pre>
<p>Copy your certificate and the private key to this location. We will also rename the files to show which domain they are associated. (It will come in handy in the future, when you will have more than one domain.) Replace <code>example.com</code> with your actual hostname:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo cp /path/to/your/certificate.crt /etc/nginx/ssl/<span class="highlight">example.com</span>.crt
</li><li class="line" prefix="$">sudo cp /path/to/your/private.key /etc/nginx/ssl/<span class="highlight">example.com</span>.key
</li></ul></code></pre>
<p>Now, let's open our configuration file one again and configure SSL. </p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo nano /etc/nginx/sites-available/default
</li></ul></code></pre>
<p>On new lines inside the <code>server</code> block, define the location of your certificates:</p>
<div class="code-label " title="/etc/nginx/sites-available/default">/etc/nginx/sites-available/default</div><pre class="code-pre "><code langs="">ssl_certificate /etc/nginx/ssl/<span class="highlight">example.com</span>.crt;
ssl_certificate_key /etc/nginx/ssl/<span class="highlight">example.com</span>.key;
</code></pre>
<p>Save the file, and exit the text editor.</p>

<h2 id="step-5-—-avoiding-old-cipher-suites">Step 5 — Avoiding Old Cipher Suites</h2>

<p>HTTP/2 has a <a href="https://http2.github.io/http2-spec/#BadCipherSuites">huge blacklist</a> of old and insecure ciphers, so we must avoid them. Cipher suites are a bunch of cryptographic algorithms, which describe how the transferring data should be encrypted.  </p>

<p>We will use a really popular cipher set, whose security was approved by Internet giants like CloudFlare. It does not allow the usage of MD5 encryption (which was known as insecure since 1996, but despite this fact, its use is widespread even to this day).</p>

<p>Open the following configuration file:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo nano /etc/nginx/nginx.conf
</li></ul></code></pre>
<p>Add this line after <code>ssl_prefer_server_ciphers on;</code>.</p>
<div class="code-label " title="/etc/nginx/nginx.conf">/etc/nginx/nginx.conf</div><pre class="code-pre "><code langs="">ssl_ciphers EECDH+CHACHA20:EECDH+AES128:RSA+AES128:EECDH+AES256:RSA+AES256:EECDH+3DES:RSA+3DES:!MD5;
</code></pre>
<p>Save the file, and exit the text editor.</p>

<p>Once again, check the configuration for syntax errors:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo nginx -t
</li></ul></code></pre>
<h2 id="step-6-—-increasing-key-exchange-security">Step 6 — Increasing Key Exchange Security</h2>

<p>The first step in the establishment of a secure connection is the exchange of the private keys between server and client. The problem is that, up to this point, the connection between them is not encrypted — which means the transferring of data is visible to any third party. That is why we need the Diffie–Hellman–Merkle algorithm. The technical details about how does it work is a complicated matter that cannot be explained in a nutshell, but if you are really interested in details, you can watch <a href="https://www.youtube.com/watch?v=M-0qt6tdHzk">this YouTube video</a>.</p>

<p>By default, Nginx uses a 1028-bit DHE (Ephemeral Diffie-Hellman) key, which is relatively easy to decrypt. To provide maximum security, we should build our own, more secure DHE key.</p>

<p>To do it, issue the following command:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo openssl dhparam -out <span class="highlight">/etc/nginx/ssl/</span>dhparam.pem 2048
</li></ul></code></pre>
<p><span class="note">Keep in mind that we should generate DH parameters in the same folder as our SSL certificates. In this tutorial, the certificates are located in <code>/etc/nginx/ssl/</code>. The reason for this is that Nginx always looks for user-provided DHE key in the certificates folder and uses it if exists.<br /></span></p>

<p>The variable after the file path (in our case it is <code>2048</code>) specifies the length of the key. A key with a 2048-bit length is secure enough and <a href="https://wiki.mozilla.org/Security/Server_Side_TLS#Pre-defined_DHE_groups">recommended by the Mozilla Foundation</a>, but if you are looking for even more encryption, you can change it to <code>4096</code>.</p>

<p>The generation process will take about 5 minutes.</p>

<p>Once it is complete, open the default Nginx configuration file again:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo nano /etc/nginx/sites-available/default
</li></ul></code></pre>
<p>On a new line inside <code>server</code> block, define the location of your custom DHE key:</p>
<div class="code-label " title="/etc/nginx/sites-available/default">/etc/nginx/sites-available/default</div><pre class="code-pre "><code langs="">ssl_dhparam  /etc/nginx/ssl/dhparam.pem;
</code></pre>
<h2 id="step-7-—-redirecting-all-http-request-to-https">Step 7 — Redirecting all HTTP Request to HTTPS</h2>

<p>Since we are interested in serving the content through HTTPS only, we should tell Nginx what it should do if the server receives an HTTP request.</p>

<p>At the bottom of our file, we will create a new server block for redirecting all HTTP requests to HTTPS (be sure to replace the server name with your actual domain name):</p>
<div class="code-label " title="/etc/nginx/sites-available/default">/etc/nginx/sites-available/default</div><pre class="code-pre "><code langs="">server {
       listen         80;
       listen    [::]:80;
       server_name    <span class="highlight">example.com</span>;
       return         301 https://$server_name$request_uri;
}
</code></pre>
<p>Save the file, and exit the configuration file.</p>

<p>Check the configuration for syntax errors:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo nginx -t
</li></ul></code></pre>
<h2 id="step-8-—-reloading-nginx">Step 8 — Reloading Nginx</h2>

<p>That's it for all the Nginx configuration changes. Since we checked for syntax errors with each change, you should be ready to restart Nginx and test your changes.</p>

<p>To summarize, ignoring commented out lines, your configuration file should now look similar to this:</p>
<div class="code-label " title="/etc/nginx/sites-available/default">/etc/nginx/sites-available/default</div><pre class="code-pre "><code langs="">server {
        listen 443 ssl http2 default_server;
        listen [::]:443 ssl http2 default_server;

        root /var/www/html;

        index index.html index.htm index.nginx-debian.html;

        server_name <span class="highlight">example.com</span>;

        location / {
                try_files $uri $uri/ =404;
        }

        ssl_certificate /etc/nginx/ssl/<span class="highlight">example.com</span>.crt;
        ssl_certificate_key /etc/nginx/ssl/<span class="highlight">example.com</span>.key;
        ssl_dhparam /etc/nginx/ssl/dhparam.pem;
}


server {
       listen         80;
       listen    [::]:80;
       server_name    <span class="highlight">example.com</span>;
       return         301 https://$server_name$request_uri;
}

</code></pre>
<p>To apply the changes,  restart the Nginx server.</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo systemctl restart nginx
</li></ul></code></pre>
<h2 id="step-9-—-verifying-the-changes">Step 9 — Verifying the Changes</h2>

<p>Let's check that our server is up and running. Open your web browser and navigate to your domain (replace <code>example.com</code> with your actual domain name):</p>
<pre class="code-pre "><code langs=""><span class="highlight">example.com</span>
</code></pre>
<p>If everything was configured properly, you should be automatically redirected to HTTPS. Now, let's check that HTTP/2 is working: open the Chrome Developer Tools (<strong>View</strong> -> <strong>Developer</strong> -> <strong>Developer Tools</strong>) and reload the page (<strong>View</strong> -> <strong>Reload This Page</strong>). Then navigate to the <strong>Network</strong> tab, click on table header row that starts with <strong>Name</strong>, right-click on it, and select the <strong>Protocol</strong> option. </p>

<p>Now you should see <code>h2</code> (which stands for HTTP/2) in a new column for your website serving HTTP/2 content.</p>

<p><img src="https://assets.digitalocean.com/articles/nginx_http2/http2_check.png" alt="Chrome Developer Tools HTTP/2 check" /></p>

<p>At this point, our server is ready to serve content through HTTP/2 protocol, but there are still some things we should do to prepare the server to be used in production.</p>

<h2 id="step-10-—-optimizing-nginx-for-best-performance">Step 10 —  Optimizing Nginx for Best Performance</h2>

<p>In this step we will tune the main Nginx configuration file for best performance and security.</p>

<p>First of all, let's open <code>nginx.conf</code> by typing the following in the console:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo nano /etc/nginx/nginx.conf
</li></ul></code></pre>
<h3 id="enabling-connection-credentials-caching">Enabling Connection Credentials Caching</h3>

<p>Compared to HTTP, HTTPS takes a relatively longer time to establish initial connection between server and user. To minimize this difference in page load speed, we will enable caching of the connection credentials. That means instead of creating a new session on every page requested, the server will use a cached version of the credentials instead.</p>

<p>To enable session caching, add these lines at the end of <code>http</code> block of your <code>nginx.conf</code> file:</p>
<div class="code-label " title="/etc/nginx/nginx.conf">/etc/nginx/nginx.conf</div><pre class="code-pre "><code langs="">ssl_session_cache shared:SSL:5m;
ssl_session_timeout 1h;
</code></pre>
<p><code>ssl_session_cache</code> specifies the size of cache that will contain session information. 1 MB of it can store information for about 4000 sessions. The default value of 5 MB will be more than enough for most users, but if you expect really heavy traffic, you can increase this value accordingly. </p>

<p><code>ssl_session_timeout</code> limits the time particular sessions are stored in the cache. This value shouldn’t be too big (more than an hour), but setting the value too low is pointless as well.</p>

<h3 id="enabling-http-strict-transport-security-hsts">Enabling HTTP Strict Transport Security (HSTS)</h3>

<p>Even though we have already made all regular HTTP requests redirect to HTTPS in our Nginx configuration file, we also should enable HTTP Strict Transport Security to avoid having to do those redirects in the first place.</p>

<p>If the browser finds an HSTS header, it will not try to connect to the server via regular HTTP again for the given time period. No matter what, it will exchange data using only encrypted HTTPS connection. This header should also protect us from protocol downgrade attacks.</p>

<p>Add this line in <code>nginx.conf</code>:</p>
<div class="code-label " title="/etc/nginx/nginx.conf">/etc/nginx/nginx.conf</div><pre class="code-pre "><code langs="">add_header Strict-Transport-Security "max-age=15768000" always;
</code></pre>
<p>The <code>max-age</code> is set in seconds. 15768000 seconds is equivalent to 6 months.</p>

<p>By default, this header is not added to subdomain requests. If you have subdomains and want HSTS to apply to all of them, you should add the <code>includeSubDomains</code> variable at the end of the line, like this:</p>
<div class="code-label " title="/etc/nginx/nginx.conf">/etc/nginx/nginx.conf</div><pre class="code-pre "><code langs="">add_header Strict-Transport-Security "max-age=15768000; includeSubDomains: always;";
</code></pre>
<p>Save the file, and exit the text editor.</p>

<p>Once again, check the configuration for syntax errors:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo nginx -t
</li></ul></code></pre>
<p>Finally, restart the Nginx server to apply the changes.</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo systemctl restart nginx
</li></ul></code></pre>
<h2 id="conclusion">Conclusion</h2>

<p>Your Nginx server is now serving HTTP/2 pages. If you want to test the strength of your SSL connection, please visit <a href="https://www.ssllabs.com/ssltest/">Qualys SSL Lab</a> and run a test against your server. If everything is configured properly, you should get an A+ mark for security.</p>

    