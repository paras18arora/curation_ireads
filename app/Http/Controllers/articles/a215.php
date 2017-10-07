<link rel='stylesheet' href='digital_ocean.css'><br><img src=https://community-cdn-digitalocean-com.global.ssl.fastly.net/assets/tutorials/images/large/SSLCertificate%28Nginx%29_Create_twitter_mostov.png?1466190255/> <br> 
      <h3 id="introduction">Introduction</h3>

<p><strong>TLS</strong>, or transport layer security, and its predecessor <strong>SSL</strong>, which stands for secure sockets layer, are web protocols used to wrap normal traffic in a protected, encrypted wrapper.</p>

<p>Using this technology, servers can send traffic safely between the server and clients without the possibility of the messages being  intercepted by outside parties.  The certificate system also assists users in verifying the identity of the sites that they are connecting with.</p>

<p>In this guide, we will show you how to set up a self-signed SSL certificate for use with an Nginx web server on an Ubuntu 16.04 server.</p>

<span class="note"><p>
<strong>Note:</strong> A self-signed certificate will encrypt communication between your server and any clients.  However, because it is not signed by any of the trusted certificate authorities included with web browsers, users cannot use the certificate to validate the identity of your server automatically.</p>

<p>A self-signed certificate may be appropriate if you do not have a domain name associated with your server and for instances where the encrypted web interface is not user-facing.  If you <em>do</em> have a domain name, in many cases it is better to use a CA-signed certificate.  You can find out how to set up a free trusted certificate with the Let's Encrypt project <a href="https://indiareads/community/tutorials/how-to-secure-nginx-with-let-s-encrypt-on-ubuntu-16-04">here</a>.<br /></p></span>

<h2 id="prerequisites">Prerequisites</h2>

<p>Before you begin, you should have a non-root user configured with <code>sudo</code> privileges.  You can learn how to set up such a user account by following our <a href="https://indiareads/community/tutorials/initial-server-setup-with-ubuntu-16-04">initial server setup for Ubuntu 16.04</a>.</p>

<p>You will also need to have the Nginx web server installed.  If you would like to install an entire LEMP (Linux, Nginx, MySQL, PHP) stack on your server, you can follow our guide on <a href="https://indiareads/community/tutorials/how-to-install-linux-nginx-mysql-php-lemp-stack-on-ubuntu-16-04">setting up LEMP on Ubuntu 16.04</a>.</p>

<p>If you just want the Nginx web server, you can instead follow our guide on <a href="https://indiareads/community/tutorials/how-to-install-nginx-on-ubuntu-16-04">installing Nginx on Ubuntu 16.04</a>.</p>

<p>When you have completed the prerequisites, continue below.</p>

<h2 id="step-1-create-the-ssl-certificate">Step 1: Create the SSL Certificate</h2>

<p>TLS/SSL works by using a combination of a public certificate and a private key.  The SSL key is kept secret on the server.  It is used to encrypt content sent to clients.  The SSL certificate is publicly shared with anyone requesting the content.  It can be used to decrypt the content signed by the associated SSL key.</p>

<p>We can create a self-signed key and certificate pair with OpenSSL in a single command:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo openssl req -x509 -nodes -days 365 -newkey rsa:2048 -keyout /etc/ssl/private/nginx-selfsigned.key -out /etc/ssl/certs/nginx-selfsigned.crt
</li></ul></code></pre>
<p>You will be asked a series of questions.  Before we go over that, let's take a look at what is happening in the command we are issuing:</p>

<ul>
<li><strong>openssl</strong>: This is the basic command line tool for creating and managing OpenSSL certificates, keys, and other files.</li>
<li><strong>req</strong>: This subcommand specifies that we want to use X.509 certificate signing request (CSR) management.  The "X.509" is a public key infrastructure standard that SSL and TLS adheres to for its key and certificate management.  We want to create a new X.509 cert, so we are using this subcommand.</li>
<li><strong>-x509</strong>: This further modifies the previous subcommand by telling the utility that we want to make a self-signed certificate instead of generating a certificate signing request, as would normally happen.</li>
<li><strong>-nodes</strong>: This tells OpenSSL to skip the option to secure our certificate with a passphrase.  We need Nginx to be able to read the file, without user intervention, when the server starts up.  A passphrase would prevent this from happening because we would have to enter it after every restart.</li>
<li><strong>-days 365</strong>: This option sets the length of time that the certificate will be considered valid.  We set it for one year here.</li>
<li><strong>-newkey rsa:2048</strong>: This specifies that we want to generate a new certificate and a new key at the same time.  We did not create the key that is required to sign the certificate in a previous step, so we need to create it along with the certificate.  The <code>rsa:2048</code> portion tells it to make an RSA key that is 2048 bits long.</li>
<li><strong>-keyout</strong>: This line tells OpenSSL where to place the generated private key file that we are creating.</li>
<li><strong>-out</strong>: This tells OpenSSL where to place the certificate that we are creating.</li>
</ul>

<p>As we stated above, these options will create both a key file and a certificate.  We will be asked a few questions about our server in order to embed the information correctly in the certificate.</p>

<p>Fill out the prompts appropriately.  <strong>The most important line is the one that requests the <code>Common Name (e.g. server FQDN or YOUR name)</code>.  You need to enter the domain name associated with your server or, more likely, your server's public IP address.</strong></p>

<p>The entirety of the prompts will look something like this:</p>
<pre class="code-pre "><code langs=""><div class="secondary-code-label " title="Output">Output</div>Country Name (2 letter code) [AU]:<span class="highlight">US</span>
State or Province Name (full name) [Some-State]:<span class="highlight">New York</span>
Locality Name (eg, city) []:<span class="highlight">New York City</span>
Organization Name (eg, company) [Internet Widgits Pty Ltd]:<span class="highlight">Bouncy Castles, Inc.</span>
Organizational Unit Name (eg, section) []:<span class="highlight">Ministry of Water Slides</span>
Common Name (e.g. server FQDN or YOUR name) []:<span class="highlight">server_IP_address</span>
Email Address []:<span class="highlight">admin@your_domain.com</span>
</code></pre>
<p>Both of the files you created will be placed in the appropriate subdirectories of the <code>/etc/ssl</code> directory.</p>

<p>While we are using OpenSSL, we should also create a strong Diffie-Hellman group, which is used in negotiating <a href="https://en.wikipedia.org/wiki/Forward_secrecy">Perfect Forward Secrecy</a> with clients.</p>

<p>We can do this by typing:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo openssl dhparam -out /etc/ssl/certs/dhparam.pem 2048
</li></ul></code></pre>
<p>This may take a few minutes, but when it's done you will have a strong DH group at <code>/etc/ssl/certs/dhparam.pem</code> that we can use in our configuration.</p>

<h2 id="step-2-configure-nginx-to-use-ssl">Step 2: Configure Nginx to Use SSL</h2>

<p>We have created our key and certificate files under the <code>/etc/ssl</code> directory.  Now we just need to modify our Nginx configuration to take advantage of these.</p>

<p>We will make a few adjustments to our configuration.</p>

<ol>
<li>We will create a configuration snippet containing our SSL key and certificate file locations.</li>
<li>We will create a configuration snippet containing strong SSL settings that can be used with any certificates in the future.</li>
<li>We will adjust our Nginx server blocks to handle SSL requests and use the two snippets above.</li>
</ol>

<p>This method of configuring Nginx will allow us to keep clean server blocks and put common configuration segments into reusable modules.</p>

<h3 id="create-a-configuration-snippet-pointing-to-the-ssl-key-and-certificate">Create a Configuration Snippet Pointing to the SSL Key and Certificate</h3>

<p>First, let's create a new Nginx configuration snippet in the <code>/etc/nginx/snippets</code> directory.</p>

<p>To properly distinguish the purpose of this file, let's call it <code>self-signed.conf</code>:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo nano /etc/nginx/snippets/self-signed.conf
</li></ul></code></pre>
<p>Within this file, we just need to set the <code>ssl_certificate</code> directive to our certificate file and the <code>ssl_certificate_key</code> to the associated key.  In our case, this will look like this:</p>
<div class="code-label " title="/etc/nginx/snippets/self-signed.conf">/etc/nginx/snippets/self-signed.conf</div><pre class="code-pre "><code langs="">ssl_certificate /etc/ssl/certs/nginx-selfsigned.crt;
ssl_certificate_key /etc/ssl/private/nginx-selfsigned.key;
</code></pre>
<p>When you've added those lines, save and close the file.</p>

<h3 id="create-a-configuration-snippet-with-strong-encryption-settings">Create a Configuration Snippet with Strong Encryption Settings</h3>

<p>Next, we will create another snippet that will define some SSL settings.  This will set Nginx up with a strong SSL cipher suite and enable some advanced features that will help keep our server secure.</p>

<p>The parameters we will set can be reused in future Nginx configurations, so we will give the file a generic name:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo nano /etc/nginx/snippets/ssl-params.conf
</li></ul></code></pre>
<p>To set up Nginx SSL securely, we will be using the recommendations by <a href="https://raymii.org/s/static/About.html">Remy van Elst</a> on the <a href="https://cipherli.st">Cipherli.st</a> site.  This site is designed to provide easy-to-consume encryption settings for popular software.  You can read more about his decisions regarding the Nginx choices <a href="https://raymii.org/s/tutorials/Strong_SSL_Security_On_nginx.html">here</a>.</p>

<span class="note"><p>
The suggested settings on the site linked to above offer strong security.  Sometimes, this comes at the cost of greater client compatibility.  If you need to support older clients, there is an alternative list that can be accessed by clicking the link on the page labelled "Yes, give me a ciphersuite that works with legacy / old software."  That list can be substituted for the items copied below.</p>

<p>The choice of which config you use will depend largely on what you need to support.  They both will provide great security.<br /></p></span>

<p>For our purposes, we can copy the provided settings in their entirety.  We just need to add our preferred DNS resolver for upstream requests.  We will use Google's for this guide.</p>

<p>We will also go ahead and set the <code>ssl_dhparam</code> setting to point to the Diffie-Hellman file we generated earlier:</p>
<div class="code-label " title="/etc/nginx/snippets/ssl-params.conf">/etc/nginx/snippets/ssl-params.conf</div><pre class="code-pre "><code langs=""># from https://cipherli.st/
# and https://raymii.org/s/tutorials/Strong_SSL_Security_On_nginx.html

ssl_protocols TLSv1 TLSv1.1 TLSv1.2;
ssl_prefer_server_ciphers on;
ssl_ciphers "EECDH+AESGCM:EDH+AESGCM:AES256+EECDH:AES256+EDH";
ssl_ecdh_curve secp384r1;
ssl_session_cache shared:SSL:10m;
ssl_session_tickets off;
ssl_stapling on;
ssl_stapling_verify on;
resolver <span class="highlight">8.8.8.8 8.8.4.4</span> valid=300s;
resolver_timeout 5s;
add_header Strict-Transport-Security "max-age=63072000; includeSubdomains; preload";
add_header X-Frame-Options DENY;
add_header X-Content-Type-Options nosniff;

<span class="highlight">ssl_dhparam /etc/ssl/certs/dhparam.pem;</span>
</code></pre>
<p>Because we are using a self-signed certificate, the SSL stapling will not be used.  Nginx will simply output a warning, disable stapling for our self-signed cert, and continue to operate correctly.</p>

<p>Save and close the file when you are finished.</p>

<h3 id="adjust-the-nginx-configuration-to-use-ssl">Adjust the Nginx Configuration to Use SSL</h3>

<p>Now that we have our snippets, we can adjust our Nginx configuration to enable SSL.</p>

<p>We will assume in this guide that you are using the <code>default</code> server block file in the <code>/etc/nginx/sites-available</code> directory.  If you are using a different server block file, substitute it's name in the below commands.</p>

<p>Before we go any further, let's back up our current server block file:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo cp /etc/nginx/sites-available/default /etc/nginx/sites-available/default.bak
</li></ul></code></pre>
<p>Now, open the server block file to make adjustments:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo nano /etc/nginx/sites-available/default
</li></ul></code></pre>
<p>Inside, your server block probably begins like this:</p>
<div class="code-label " title="/etc/nginx/sites-available/default">/etc/nginx/sites-available/default</div><pre class="code-pre "><code langs="">server {
    listen 80 default_server;
    listen [::]:80 default_server;

    # SSL configuration

    # listen 443 ssl default_server;
    # listen [::]:443 ssl default_server;

    . . .
</code></pre>
<p>We will be modifying this configuration so that unencrypted HTTP requests are automatically redirected to encrypted HTTPS.  This offers the best security for our sites.  If you want to allow both HTTP and HTTPS traffic, use the alternative configuration that follows.</p>

<p>We will be splitting the configuration into two separate blocks.  After the two first <code>listen</code> directives, we will add a <code>server_name</code> directive, set to your server's domain name or, more likely, IP address.  We will then set up a redirect to the second server block we will be creating.  Afterwards, we will close this short block:</p>

<p><span class="note"><strong>Note:</strong> We will use a 302 redirect until we have verified that everything is working properly.  Afterwards, we can change this to a permanent 301 redirect.<br /></span></p>
<div class="code-label " title="/etc/nginx/sites-available/default">/etc/nginx/sites-available/default</div><pre class="code-pre "><code langs="">server {
    listen 80 default_server;
    listen [::]:80 default_server;
    <span class="highlight">server_name server_domain_or_IP;</span>
    <span class="highlight">return 302 https://$server_name$request_uri;</span>
<span class="highlight">}</span>

    # SSL configuration

    # listen 443 ssl default_server;
    # listen [::]:443 ssl default_server;

    . . .
</code></pre>
<p>Next, we need to start a new server block directly below to contain the remaining configuration.  We can uncomment the two <code>listen</code> directives that use port 443.  We can add <code>http2</code> to these lines in order to enable HTTP/2 within this block.  Afterwards, we just need to include the two snippet files we set up:</p>

<p><span class="note"><strong>Note:</strong> You may only have <strong>one</strong> <code>listen</code> directive that includes the <code>default_server</code> modifier for each IP version and port combination.  If you have other server blocks enabled for these ports that have <code>default_server</code> set, you must remove the modifier from one of the blocks.<br /></span></p>
<div class="code-label " title="/etc/nginx/sites-available/default">/etc/nginx/sites-available/default</div><pre class="code-pre "><code langs="">server {
    listen 80 default_server;
    listen [::]:80 default_server;
    server_name <span class="highlight">server_domain_or_IP</span>;
    return 302 https://$server_name$request_uri;
}

<span class="highlight">server {</span>

    # SSL configuration

    <span class="highlight">listen 443 ssl http2 default_server;</span>
    <span class="highlight">listen [::]:443 ssl http2 default_server;</span>
    <span class="highlight">include snippets/self-signed.conf;</span>
    <span class="highlight">include snippets/ssl-params.conf;</span>

    . . .
</code></pre>
<p>Save and close the file when you are finished.</p>

<h3 id="alternative-configuration-allow-both-http-and-https-traffic">(Alternative Configuration) Allow Both HTTP and HTTPS Traffic</h3>

<p>If you want or need to allow both encrypted and unencrypted content, you will have to configure Nginx a bit differently.  This is generally not recommended if it can be avoided, but in some situations it may be necessary.  Basically, we just compress the two separate server blocks into one block and remove the redirect:</p>
<div class="code-label " title="/etc/nginx/sites-available/default">/etc/nginx/sites-available/default</div><pre class="code-pre "><code langs="">server {
    listen 80 default_server;
    listen [::]:80 default_server;
    <span class="highlight">listen 443 ssl http2 default_server;</span>
    <span class="highlight">listen [::]:443 ssl http2 default_server;</span>

    server_name <span class="highlight">server_domain_or_IP</span>;
    <span class="highlight">include snippets/self-signed.conf;</span>
    <span class="highlight">include snippets/ssl-params.conf;</span>

    . . .
</code></pre>
<p>Save and close the file when you are finished.</p>

<h2 id="step-3-adjust-the-firewall">Step 3: Adjust the Firewall</h2>

<p>If you have the <code>ufw</code> firewall enabled, as recommended by the prerequisite guides, you'll need to adjust the settings to allow for SSL traffic.  Luckily, Nginx registers a few profiles with <code>ufw</code> upon installation.</p>

<p>We can see the available profiles by typing:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo ufw app list
</li></ul></code></pre>
<p>You should see a list like this:</p>
<pre class="code-pre "><code langs=""><div class="secondary-code-label " title="Output">Output</div>Available applications:
  Nginx Full
  Nginx HTTP
  Nginx HTTPS
  OpenSSH
</code></pre>
<p>You can see the current setting by typing:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo ufw status
</li></ul></code></pre>
<p>It will probably look like this, meaning that only HTTP traffic is allowed to the web server:</p>
<pre class="code-pre "><code langs=""><div class="secondary-code-label " title="Output">Output</div>Status: active

To                         Action      From
--                         ------      ----
OpenSSH                    ALLOW       Anywhere
Nginx HTTP                 ALLOW       Anywhere
OpenSSH (v6)               ALLOW       Anywhere (v6)
Nginx HTTP (v6)            ALLOW       Anywhere (v6)
</code></pre>
<p>To additionally let in HTTPS traffic, we can allow the "Nginx Full" profile and then delete the redundant "Nginx HTTP" profile allowance:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo ufw allow 'Nginx Full'
</li><li class="line" prefix="$">sudo ufw delete allow 'Nginx HTTP'
</li></ul></code></pre>
<p>Your status should look like this now:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo ufw status
</li></ul></code></pre><pre class="code-pre "><code langs=""><div class="secondary-code-label " title="Output">Output</div>Status: active

To                         Action      From
--                         ------      ----
OpenSSH                    ALLOW       Anywhere
Nginx Full                 ALLOW       Anywhere
OpenSSH (v6)               ALLOW       Anywhere (v6)
Nginx Full (v6)            ALLOW       Anywhere (v6)
</code></pre>
<h2 id="step-4-enable-the-changes-in-nginx">Step 4: Enable the Changes in Nginx</h2>

<p>Now that we've made our changes and adjusted our firewall, we can restart Nginx to implement our new changes.</p>

<p>First, we should check to make sure that there are no syntax errors in our files.  We can do this by typing:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo nginx -t
</li></ul></code></pre>
<p>If everything is successful, you will get a result that looks like this:</p>
<pre class="code-pre "><code langs=""><div class="secondary-code-label " title="Output">Output</div>nginx: [warn] "ssl_stapling" ignored, issuer certificate not found
nginx: the configuration file /etc/nginx/nginx.conf syntax is ok
nginx: configuration file /etc/nginx/nginx.conf test is successful
</code></pre>
<p>Notice the warning in the beginning.  As noted earlier, this particular setting throws a warning since our self-signed certificate can't use SSL stapling.  This is expected and our server can still encrypt connections correctly.</p>

<p>If your output matches the above, your configuration file has no syntax errors.  We can safely restart Nginx to implement our changes:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo systemctl restart nginx
</li></ul></code></pre>
<h2 id="step-5-test-encryption">Step 5: Test Encryption</h2>

<p>Now, we're ready to test our SSL server.</p>

<p>Open your web browser and type <code>https://</code> followed by your server's domain name or IP into the address bar:</p>
<pre class="code-pre "><code langs="">https://<span class="highlight">server_domain_or_IP</span>
</code></pre>
<p>Because the certificate we created isn't signed by one of your browser's trusted certificate authorities, you will likely see a scary looking warning like the one below:</p>

<p><img src="https://assets.digitalocean.com/articles/nginx_ssl_1604/self_signed_warning.png" alt="Nginx self-signed cert warning" /></p>

<p>This is expected and normal.  We are only interested in the encryption aspect of our certificate, not the third party validation of our host's authenticity.  Click "ADVANCED" and then the link provided to proceed to your host anyways:</p>

<p><img src="https://assets.digitalocean.com/articles/nginx_ssl_1604/warning_override.png" alt="Nginx self-signed override" /></p>

<p>You should be taken to your site.  If you look in the browser address bar, you will see a lock with an "x" over it.  In this case, this just means that the certificate cannot be validated.  It is still encrypting your connection.</p>

<p>If you configured Nginx with two server blocks, automatically redirecting HTTP content to HTTPS, you can also check whether the redirect functions correctly:</p>
<pre class="code-pre "><code langs="">http://<span class="highlight">server_domain_or_IP</span>
</code></pre>
<p>If this results in the same icon, this means that your redirect worked correctly.</p>

<h2 id="step-6-change-to-a-permanent-redirect">Step 6: Change to a Permanent Redirect</h2>

<p>If your redirect worked correctly and you are sure you want to allow only encrypted traffic, you should modify the Nginx configuration to make the redirect permanent.</p>

<p>Open your server block configuration file again:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo nano /etc/nginx/sites-available/default
</li></ul></code></pre>
<p>Find the <code>return 302</code> and change it to <code>return 301</code>:</p>
<div class="code-label " title="/etc/nginx/sites-available/default">/etc/nginx/sites-available/default</div><pre class="code-pre "><code langs="">server {
    listen 80 default_server;
    listen [::]:80 default_server;
    server_name <span class="highlight">server_domain_or_IP</span>;
    return <span class="highlight">301</span> https://$server_name$request_uri;
}

. . .
</code></pre>
<p>Save and close the file.</p>

<p>Check your configuration for syntax errors:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo nginx -t
</li></ul></code></pre>
<p>When you're ready, restart Nginx to make the redirect permanent:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo systemctl restart nginx
</li></ul></code></pre>
<h2 id="conclusion">Conclusion</h2>

<p>You have configured your Nginx server to use strong encryption for client connections.  This will allow you serve requests securely, and will prevent outside parties from reading your traffic.</p>

    