<link rel='stylesheet' href='digital_ocean.css'><br><img src=https://community-cdn-digitalocean-com.global.ssl.fastly.net/assets/tutorials/images/large/Let&#39;s-Encrypt-twitter-%28nginx%29.png?1459361570/> <br> 
      <h3 id="introduction">Introduction</h3>

<p>Let's Encrypt is a new Certificate Authority (CA) that provides an easy way to obtain and install free TLS/SSL certificates, thereby enabling encrypted HTTPS on web servers. It simplifies the process by providing a software client, <code>letsencrypt</code>, that attempts to automate most (if not all) of the required steps. Currently, as Let's Encrypt is still in open beta, the entire process of obtaining and installing a certificate is fully automated only on Apache web servers. However, Let's Encrypt can be used to easily obtain a free SSL certificate, which can be installed manually, regardless of your choice of web server software.</p>

<p>In this tutorial, we will show you how to use Let's Encrypt to obtain a free SSL certificate and use it with Nginx on Ubuntu 16.04. We will also show you how to automatically renew your SSL certificate. If you're running a different web server, simply follow your web server's documentation to learn how to use the certificate with your setup.</p>

<p><img src="https://assets.digitalocean.com/articles/letsencrypt/nginx-letsencrypt.png" alt="Nginx with Let's Encrypt TLS/SSL Certificate and Auto-renewal" /></p>

<h2 id="prerequisites">Prerequisites</h2>

<p>Before following this tutorial, you'll need a few things.</p>

<p>You should have an Ubuntu 16.04 server with a non-root user who has <code>sudo</code> privileges.  You can learn how to set up such a user account by following our <a href="https://indiareads/community/articles/initial-server-setup-with-ubuntu-16-04">initial server setup for Ubuntu 16.04 tutorial</a>.</p>

<p>You must own or control the registered domain name that you wish to use the certificate with. If you do not already have a registered domain name, you may register one with one of the many domain name registrars out there (e.g. Namecheap, GoDaddy, etc.).</p>

<p>If you haven't already, be sure to create an <strong>A Record</strong> that points your domain to the public IP address of your server. This is required because of how Let's Encrypt validates that you own the domain it is issuing a certificate for. For example, if you want to obtain a certificate for <code>example.com</code>, that domain must resolve to your server for the validation process to work. Our setup will use <code>example.com</code> and <code>www.example.com</code> as the domain names, so <strong>both DNS records are required</strong>.</p>

<p>Once you have all of the prerequisites out of the way, let's move on to installing the Let's Encrypt client software.</p>

<h2 id="step-1-install-let-39-s-encrypt-client">Step 1: Install Let's Encrypt Client</h2>

<p>The first step to using Let's Encrypt to obtain an SSL certificate is to install the <code>letsencrypt</code> software on your server. Currently, the best way to install Let's Encrypt is to simply clone it from the official GitHub repository. In the future, it will likely be available via a package manager.</p>

<h3 id="install-git-and-bc">Install Git and bc</h3>

<p>Let's install Git and bc now, so we can clone the Let's Encrypt repository.</p>

<p>Update your server's package manager with this command:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo apt-get update
</li></ul></code></pre>
<p>Then install the <code>git</code> package with apt-get:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo apt-get -y install git
</li></ul></code></pre>
<p>With <code>git</code> installed, we can easily download <code>letsencrypt</code> by cloning the repository from GitHub.</p>

<h3 id="clone-let-39-s-encrypt">Clone Let's Encrypt</h3>

<p>We can now clone the Let’s Encrypt repository in <code>/opt</code> with this command:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo git clone https://github.com/letsencrypt/letsencrypt /opt/letsencrypt
</li></ul></code></pre>
<p>You should now have a copy of the <code>letsencrypt</code> repository in the <code>/opt/letsencrypt</code> directory.</p>

<h2 id="step-2-obtain-an-ssl-certificate">Step 2: Obtain an SSL Certificate</h2>

<p>Let's Encrypt provides a variety of ways to obtain SSL certificates, through various plugins. Unlike the Apache plugin, which is covered in <a href="https://indiareads/community/tutorials/how-to-secure-apache-with-let-s-encrypt-on-ubuntu-14-04">a different tutorial</a>, most of the plugins will only help you with obtaining a certificate which you must manually configure your web server to use. Plugins that only obtain certificates, and don't install them, are referred to as "authenticators" because they are used to authenticate whether a server should be issued a certificate.</p>

<p>We'll show you how to use the <strong>Webroot</strong> plugin to obtain an SSL certificate.</p>

<h3 id="how-to-use-the-webroot-plugin">How To Use the Webroot Plugin</h3>

<p>The Webroot plugin works by placing a special file in the <code>/.well-known</code> directory within your document root, which can be opened (through your web server) by the Let's Encrypt service for validation. Depending on your configuration, you may need to explicitly allow access to the <code>/.well-known</code> directory. </p>

<p>If you haven't installed Nginx yet, do so by following <a href="https://indiareads/community/tutorials/how-to-install-nginx-on-ubuntu-16-04">this tutorial</a>.  Continue below when you are finished.</p>

<p>To ensure that the directory is accessible to Let's Encrypt for validation, let's make a quick change to our Nginx configuration. By default, it's located at <code>/etc/nginx/sites-available/default</code>. We'll use <code>nano</code> to edit it:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo nano /etc/nginx/sites-available/default
</li></ul></code></pre>
<p>Inside the server block, add this location block:</p>
<div class="code-label " title="Add to SSL server block">Add to SSL server block</div><pre class="code-pre "><code langs="">        location ~ /.well-known {
                allow all;
        }
</code></pre>
<p>You will also want look up what your document root is set to by searching for the <code>root</code> directive, as the path is required to use the Webroot plugin. If you're using the default configuration file, the root will be <code>/var/www/html</code>.</p>

<p>Save and exit.</p>

<p>Check your configuration for syntax errors:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo nginx -t
</li></ul></code></pre>
<p>Reload Nginx with this command:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo systemctl reload nginx
</li></ul></code></pre>
<p>Now that we know our <code>webroot-path</code>, we can use the Webroot plugin to request an SSL certificate with these commands. Here, we are also specifying our domain names with the <code>-d</code> option. If you want a single cert to work with multiple domain names (e.g. <code>example.com</code> and <code>www.example.com</code>), be sure to include all of them. Also, make sure that you replace the highlighted parts with the appropriate webroot path and domain name(s):</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">cd /opt/letsencrypt
</li><li class="line" prefix="$">./letsencrypt-auto certonly -a webroot --webroot-path=<span class="highlight">/var/www/html</span> -d <span class="highlight">example.com</span> -d <span class="highlight">www.example.com</span>
</li></ul></code></pre>
<p><span class="note"><strong>Note:</strong> The Let's Encrypt software requires superuser privileges, so you will be required to enter your password if you haven't used <code>sudo</code> recently.<br /></span></p>

<p>After <code>letsencrypt</code> initializes, you will be prompted for some information. The exact prompts may vary depending on if you've used Let's Encrypt before, but we'll step you through the first time.</p>

<p>At the prompt, enter an email address that will be used for notices and lost key recovery:</p>

<p><img src="https://assets.digitalocean.com/articles/letsencrypt/le-email.png" alt="Email prompt" /></p>

<p>Then you must agree to the Let's Encrypt Subscribe Agreement. Select Agree:</p>

<p><img src="https://assets.digitalocean.com/articles/letsencrypt/le-agreement.png" alt="Let's Encrypt Subscriber's Agreement" /></p>

<p>If everything was successful, you should see an output message that looks something like this:</p>
<pre class="code-pre "><code langs=""><div class="secondary-code-label " title="Output:">Output:</div>IMPORTANT NOTES:
 - If you lose your account credentials, you can recover through
   e-mails sent to sammy@digitalocean.com
 - Congratulations! Your certificate and chain have been saved at
   <span class="highlight">/etc/letsencrypt/live/example.com/</span>fullchain.pem. Your
   cert will expire on <span class="highlight">2016-03-15</span>. To obtain a new version of the
   certificate in the future, simply run Let's Encrypt again.
 - Your account credentials have been saved in your Let's Encrypt
   configuration directory at /etc/letsencrypt. You should make a
   secure backup of this folder now. This configuration directory will
   also contain certificates and private keys obtained by Let's
   Encrypt so making regular backups of this folder is ideal.
 - If like Let's Encrypt, please consider supporting our work by:

   Donating to ISRG / Let's Encrypt:   https://letsencrypt.org/donate
   Donating to EFF:                    https://eff.org/donate-le
</code></pre>
<p>You will want to note the path and expiration date of your certificate, which was highlighted in the example output.</p>

<span class="note"><p>
<strong>Firewall Note:</strong> If you receive an error like <code>Failed to connect to host for DVSNI challenge</code>, your server's firewall may need to be configured to allow TCP traffic on port <code>80</code> and <code>443</code>.</p>

<p><strong>Note:</strong> If your domain is routing through a DNS service like CloudFlare, you will need to temporarily disable it until you have obtained the certificate.<br /></p></span>

<h3 id="certificate-files">Certificate Files</h3>

<p>After obtaining the cert, you will have the following PEM-encoded files:</p>

<ul>
<li><strong>cert.pem:</strong> Your domain's certificate</li>
<li><strong>chain.pem:</strong> The Let's Encrypt chain certificate</li>
<li><strong>fullchain.pem:</strong> <code>cert.pem</code> and <code>chain.pem</code> combined</li>
<li><strong>privkey.pem:</strong> Your certificate's private key</li>
</ul>

<p>It's important that you are aware of the location of the certificate files that were just created, so you can use them in your web server configuration. The files themselves are placed in a subdirectory in <code>/etc/letsencrypt/archive</code>. However, Let's Encrypt creates symbolic links to the most recent certificate files in the <code>/etc/letsencrypt/live/<span class="highlight">your_domain_name</span></code> directory. Because the links will always point to the most recent certificate files, this is the path that you should use to refer to your certificate files.</p>

<p>You can check that the files exist by running this command (substituting in your domain name):</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo ls -l /etc/letsencrypt/live/<span class="highlight">your_domain_name</span>
</li></ul></code></pre>
<p>The output should be the four previously mentioned certificate files. In a moment, you will configure your web server to use <code>fullchain.pem</code> as the certificate file, and <code>privkey.pem</code> as the certificate key file.</p>

<h3 id="generate-strong-diffie-hellman-group">Generate Strong Diffie-Hellman Group</h3>

<p>To further increase security, you should also generate a strong Diffie-Hellman group. To generate a 2048-bit group, use this command:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo openssl dhparam -out /etc/ssl/certs/dhparam.pem 2048
</li></ul></code></pre>
<p>This may take a few minutes but when it's done you will have a strong DH group at <code>/etc/ssl/certs/dhparam.pem</code>.</p>

<h2 id="step-3-configure-tls-ssl-on-web-server-nginx">Step 3: Configure TLS/SSL on Web Server (Nginx)</h2>

<p>Now that you have an SSL certificate, you need to configure your Nginx web server to use it.</p>

<p>We will make a few adjustments to our configuration:</p>

<ol>
<li>We will create a configuration snippet containing our SSL key and certificate file locations.</li>
<li>We will create a configuration snippet containing strong SSL settings that can be used with any certificates in the future.</li>
<li>We will adjust the Nginx server blocks to handle SSL requests and use the two snippets above.</li>
</ol>

<p>This method of configuring Nginx will allow us to keep clean server blocks and put common configuration segments into reusable modules.</p>

<h3 id="create-a-configuration-snippet-pointing-to-the-ssl-key-and-certificate">Create a Configuration Snippet Pointing to the SSL Key and Certificate</h3>

<p>First, let's create a new Nginx configuration snippet in the <code>/etc/nginx/snippets</code> directory.</p>

<p>To properly distinguish the purpose of this file, we will name it <code>ssl-</code> followed by our domain name, followed by <code>.conf</code> on the end:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo nano /etc/nginx/snippets/ssl-<span class="highlight">example.com</span>.conf
</li></ul></code></pre>
<p>Within this file, we just need to set the <code>ssl_certificate</code> directive to our certificate file and the <code>ssl_certificate_key</code> to the associated key.  In our case, this will look like this:</p>
<div class="code-label " title="/etc/nginx/snippets/ssl-example.com.conf">/etc/nginx/snippets/ssl-example.com.conf</div><pre class="code-pre "><code langs="">ssl_certificate /etc/letsencrypt/live/<span class="highlight">example.com</span>/fullchain.pem;
ssl_certificate_key /etc/letsencrypt/live/<span class="highlight">example.com</span>/privkey.pem;
</code></pre>
<p>When you've added those lines, save and close the file.</p>

<h3 id="create-a-configuration-snippet-with-strong-encryption-settings">Create a Configuration Snippet with Strong Encryption Settings</h3>

<p>Next, we will create another snippet that will define some SSL settings.  This will set Nginx up with a strong SSL cipher suite and enable some advanced features that will help keep our server secure.</p>

<p>The parameters we will set can be reused in future Nginx configurations, so we will give the file a generic name:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo nano /etc/nginx/snippets/ssl-params.conf
</li></ul></code></pre>
<p>To set up Nginx SSL securely, we will be using the recommendations by <a href="https://raymii.org/s/static/About.html">Remy van Elst</a> on the <a href="https://cipherli.st">Cipherli.st</a> site.  This site is designed to provide easy-to-consume encryption settings for popular software.  You can read more about his decisions regarding the Nginx choices <a href="https://raymii.org/s/tutorials/Strong_SSL_Security_On_nginx.html">here</a>.</p>

<span class="note"><p>
<strong>Note:</strong> The default suggested settings on <a href="https://cipherli.st">Cipherli.st</a> offer strong security.  Sometimes, this comes at the cost of greater client compatibility.  If you need to support older clients, there is an alternative list that can be accessed by clicking the link on the link labeled "Yes, give me a ciphersuite that works with legacy / old software."</p>

<p>The compatibility list can be used instead of the default suggestions in the configuration below.  The choice of which config you use will depend largely on what you need to support.</p></span>

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

<p>We will be splitting the configuration into two separate blocks.  After the two first <code>listen</code> directives, we will add a <code>server_name</code> directive, set to your server's domain name.  We will then set up a redirect to the second server block we will be creating.  Afterwards, we will close this short block:</p>
<div class="code-label " title="/etc/nginx/sites-available/default">/etc/nginx/sites-available/default</div><pre class="code-pre "><code langs="">server {
    listen 80 default_server;
    listen [::]:80 default_server;
    server_name <span class="highlight">example.com</span> www.<span class="highlight">example.com</span>;
    <span class="highlight">return 301 https://$server_name$request_uri;</span>
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
    server_name <span class="highlight">example.com</span> www.<span class="highlight">example.com</span>;
    return 301 https://$server_name$request_uri;
}

<span class="highlight">server {</span>

    # SSL configuration

    <span class="highlight">listen 443 ssl http2 default_server;</span>
    <span class="highlight">listen [::]:443 ssl http2 default_server;</span>
    <span class="highlight">include snippets/ssl-example.com.conf;</span>
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

    server_name <span class="highlight">example.com</span> www.<span class="highlight">example.com</span>;
    <span class="highlight">include snippets/ssl-example.com.conf;</span>
    <span class="highlight">include snippets/ssl-params.conf;</span>

    . . .
</code></pre>
<p>Save and close the file when you are finished.</p>

<h2 id="step-4-adjust-the-firewall">Step 4: Adjust the Firewall</h2>

<p>If you have the <code>ufw</code> firewall enabled, as recommended by the prerequisite guides, you'll need to adjust the settings to allow for SSL traffic.  Luckily, Nginx registers a few profiles with <code>ufw</code> upon installation.</p>

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
<h2 id="step-5-enabling-the-changes-in-nginx">Step 5: Enabling the Changes in Nginx</h2>

<p>Now that we've made our changes and adjusted our firewall, we can restart Nginx to implement our new changes.</p>

<p>First, we should check to make sure that there are no syntax errors in our files.  We can do this by typing:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo nginx -t
</li></ul></code></pre>
<p>If everything is successful, you will get a result that looks like this:</p>
<pre class="code-pre "><code langs=""><div class="secondary-code-label " title="Output">Output</div>nginx: the configuration file /etc/nginx/nginx.conf syntax is ok
nginx: configuration file /etc/nginx/nginx.conf test is successful
</code></pre>
<p>If your output matches the above, your configuration file has no syntax errors.  We can safely restart Nginx to implement our changes:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo systemctl restart nginx
</li></ul></code></pre>
<p>The Let's Encrypt TLS/SSL certificate is now in place and the firewall now allows traffic to port 80 and 443. At this point, you should test that the TLS/SSL certificate works by visiting your domain via HTTPS in a web browser.</p>

<p>You can use the Qualys SSL Labs Report to see how your server configuration scores:</p>
<pre class="code-pre "><code langs=""><div class="secondary-code-label " title="In a web browser:">In a web browser:</div>https://www.ssllabs.com/ssltest/analyze.html?d=<span class="highlight">example.com</span>
</code></pre>
<p>This SSL setup should report an <strong>A+</strong> rating.</p>

<h2 id="step-6-set-up-auto-renewal">Step 6: Set Up Auto Renewal</h2>

<p>Let’s Encrypt certificates are valid for 90 days, but it’s recommended that you renew the certificates every 60 days to allow a margin of error. At the time of this writing, automatic renewal is still not available as a feature of the client itself, but you can manually renew your certificates by running the Let’s Encrypt client with the <code>renew</code> option.</p>

<p>To trigger the renewal process for all installed domains, run this command:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">/opt/letsencrypt/letsencrypt-auto renew
</li></ul></code></pre>
<p>Because we recently installed the certificate, the command will only check for the expiration date and print a message informing that the certificate is not due to renewal yet. The output should look similar to this:</p>
<pre class="code-pre "><code langs=""><div class="secondary-code-label " title="Output:">Output:</div>Checking for new version...
Requesting root privileges to run letsencrypt...
   /root/.local/share/letsencrypt/bin/letsencrypt renew
Processing /etc/letsencrypt/renewal/<span class="highlight">example.com</span>.conf

The following certs are not due for renewal yet:
  /etc/letsencrypt/live/<span class="highlight">example.com</span>/fullchain.pem (skipped)
No renewals were attempted.
</code></pre>
<p>Notice that if you created a bundled certificate with multiple domains, only the base domain name will be shown in the output, but the renewal should be valid for all domains included in this certificate.</p>

<p>A practical way to ensure your certificates won’t get outdated is to create a cron job that will periodically execute the automatic renewal command for you. Since the renewal first checks for the expiration date and only executes the renewal if the certificate is less than 30 days away from expiration, it is safe to create a cron job that runs every week or even every day, for instance.</p>

<p>Let's edit the crontab to create a new job that will run the renewal command every week.  To edit the crontab for the root user, run:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo crontab -e
</li></ul></code></pre>
<p>Add the following lines:</p>
<pre class="code-pre "><code langs=""><div class="secondary-code-label " title="crontab entry">crontab entry</div>30 2 * * 1 /opt/letsencrypt/letsencrypt-auto renew >> /var/log/le-renew.log
35 2 * * 1 /bin/systemctl reload nginx
</code></pre>
<p>Save and exit. This will create a new cron job that will execute the <code>letsencrypt-auto renew</code> command every Monday at 2:30 am, and reload Nginx at 2:35am (so the renewed certificate will be used). The output produced by the command will be piped to a log file located at <code>/var/log/le-renewal.log</code>.</p>

<p><span class="note">For more information on how to create and schedule cron jobs, you can check our <a href="https://indiareads/community/tutorials/how-to-use-cron-to-automate-tasks-on-a-vps">How to Use Cron to Automate Tasks in a VPS</a> guide. <br /></span></p>

<h2 id="step-5-update-the-let’s-encrypt-client-optional">Step 5: Update the Let’s Encrypt Client (optional)</h2>

<p>Whenever new updates are available for the client, you can update your local copy by running a <code>git pull</code> from inside the Let’s Encrypt directory:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">cd /opt/letsencrypt
</li><li class="line" prefix="$">sudo git pull
</li></ul></code></pre>
<p>This will download all recent changes to the repository, updating your client.</p>

<h2 id="conclusion">Conclusion</h2>

<p>That's it! Your web server is now using a free Let's Encrypt TLS/SSL certificate to securely serve HTTPS content.</p>

    