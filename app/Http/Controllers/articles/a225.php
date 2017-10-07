<link rel='stylesheet' href='digital_ocean.css'><br> <br> 
      <h3 id="introduction">Introduction</h3>

<p>Let's Encrypt is a new Certificate Authority (CA) that provides an easy way to obtain and install free TLS/SSL certificates, thereby enabling encrypted HTTPS on web servers. It simplifies the process by providing a software client, <code>letsencrypt</code>, that attempts to automate most (if not all) of the required steps. Currently, as Let's Encrypt is still in open beta, the entire process of obtaining and installing a certificate is fully automated only on Apache web servers. However, Let's Encrypt can be used to easily obtain a free SSL certificate, which can be installed manually, regardless of your choice of web server software.</p>

<p>In this tutorial, we will show you how to use Let's Encrypt to obtain a free SSL certificate and use it with Nginx on CentOS 7. We will also show you how to automatically renew your SSL certificate. If you're running a different web server, simply follow your web server's documentation to learn how to use the certificate with your setup.</p>

<p><img src="https://assets.digitalocean.com/articles/letsencrypt/nginx-letsencrypt.png" alt="Nginx with Let's Encrypt TLS/SSL Certificate and Auto-renewal" /></p>

<h2 id="prerequisites">Prerequisites</h2>

<p>Before following this tutorial, you'll need a few things.</p>

<p>You should have an CentOS 7 server with a non-root user who has <code>sudo</code> privileges.  You can learn how to set up such a user account by following steps 1-3 in our <a href="https://indiareads/community/articles/initial-server-setup-with-centos-7">initial server setup for CentOS 7 tutorial</a>.</p>

<p>You must own or control the registered domain name that you wish to use the certificate with. If you do not already have a registered domain name, you may register one with one of the many domain name registrars out there (e.g. Namecheap, GoDaddy, etc.).</p>

<p>If you haven't already, be sure to create an <strong>A Record</strong> that points your domain to the public IP address of your server. This is required because of how Let's Encrypt validates that you own the domain it is issuing a certificate for. For example, if you want to obtain a certificate for <code>example.com</code>, that domain must resolve to your server for the validation process to work. Our setup will use <code>example.com</code> and <code>www.example.com</code> as the domain names, so <strong>both DNS records are required</strong>.</p>

<p>Once you have all of the prerequisites out of the way, let's move on to installing the Let's Encrypt client software.</p>

<h2 id="step-1-—-install-let-39-s-encrypt-client">Step 1 — Install Let's Encrypt Client</h2>

<p>The first step to using Let's Encrypt to obtain an SSL certificate is to install the <code>letsencrypt</code> software on your server. Currently, the best way to install Let's Encrypt is to simply clone it from the official GitHub repository. In the future, it will likely be available via a package manager.</p>

<h3 id="install-git-and-bc">Install Git and Bc</h3>

<p>Let's install Git now, so we can clone the Let's Encrypt repository.</p>

<p>Install the <code>git</code> and <code>bc</code> packages with yum:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo yum -y install git bc
</li></ul></code></pre>
<p>With <code>git</code> and <code>bc</code> installed, we can easily download <code>letsencrypt</code> by cloning the repository from GitHub.</p>

<h3 id="clone-let-39-s-encrypt">Clone Let's Encrypt</h3>

<p>We can now clone the Let’s Encrypt repository in <code>/opt</code> with this command:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo git clone https://github.com/letsencrypt/letsencrypt /opt/letsencrypt
</li></ul></code></pre>
<p>You should now have a copy of the <code>letsencrypt</code> repository in the <code>/opt/letsencrypt</code> directory.</p>

<h2 id="step-2-—-obtain-a-certificate">Step 2 — Obtain a Certificate</h2>

<p>Let's Encrypt provides a variety of ways to obtain SSL certificates, through various plugins. Unlike the Apache plugin, which is covered in <a href="https://indiareads/community/tutorials/how-to-secure-apache-with-let-s-encrypt-on-centos-7">a different tutorial</a>, most of the plugins will only help you with obtaining a certificate which you must manually configure your web server to use. Plugins that only obtain certificates, and don't install them, are referred to as "authenticators" because they are used to authenticate whether a server should be issued a certificate.</p>

<p>We'll show you how to use the <strong>Webroot</strong> plugin to obtain an SSL certificate.</p>

<h3 id="how-to-use-the-webroot-plugin">How To Use the Webroot Plugin</h3>

<p>The Webroot plugin works by placing a special file in the <code>/.well-known</code> directory within your document root, which can be opened (through your web server) by the Let's Encrypt service for validation. Depending on your configuration, you may need to explicitly allow access to the <code>/.well-known</code> directory. </p>

<p>If you haven't installed Nginx yet, first install the <code>epel-release</code> repository:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo yum -y install epel-release
</li></ul></code></pre>
<p>Then install Nginx with this command:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo yum -y install nginx
</li></ul></code></pre>
<p>To ensure that the directory is accessible to Let's Encrypt for validation, let's make a quick change to our default Nginx server block. The default Nginx configuration file allows us to easily add directives to the port 80 server block by adding files in the <code>/etc/nginx/default.d</code> directory. If you're using the default configuration, create a new file called <code>le-well-known.conf</code> and open it for editing with this command:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo vi /etc/nginx/default.d/le-well-known.conf
</li></ul></code></pre>
<p>Then paste in these lines:</p>
<div class="code-label " title="/etc/nginx/default.d/le-well-known.conf">/etc/nginx/default.d/le-well-known.conf</div><pre class="code-pre "><code langs="">location ~ /.well-known {
        allow all;
}
</code></pre>
<p>Save and exit.</p>

<p>Start Nginx with this command:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo systemctl restart nginx
</li></ul></code></pre>
<p>If you aren't using the default server block, you will need to look up what your document root is set to by looking for the <code>root</code> directive in your default Nginx server block. This is the value that Let's Encrypt requires, as <code>webroot-path</code>, when using the Webroot plugin. The default root is <code>/usr/share/nginx/html</code>.</p>

<p>Now that we know our <code>webroot-path</code>, we can use the Webroot plugin to request an SSL certificate with these commands. Here, we are also specifying our domain names with the <code>-d</code> option. If you want a single cert to work with multiple domain names (e.g. <code>example.com</code> and <code>www.example.com</code>), be sure to include all of them. Also, make sure that you replace the highlighted parts with the appropriate webroot path and domain name(s):</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">cd /opt/letsencrypt
</li><li class="line" prefix="$">./letsencrypt-auto certonly -a webroot --webroot-path=<span class="highlight">/usr/share/nginx/html</span> -d <span class="highlight">example.com</span> -d <span class="highlight">www.example.com</span>
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

<h2 id="step-3-—-configure-tls-ssl-on-web-server-nginx">Step 3 — Configure TLS/SSL on Web Server (Nginx)</h2>

<p>Now you must edit the Nginx configuration to use the Let's Encrypt certificate files. The default Nginx configuration on CentOS is pretty open-ended but we will create a new server block that uses SSL/TLS and listens on port 443. Then we'll configure the default (HTTP on port 80) server block to redirect to the HTTPS-enabled server block.</p>

<p>By default, additional server block configuration can be placed in <code>/etc/nginx/conf.d</code>. Create a new file called <code>ssl.conf</code> and open it for editing with this command:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo vi /etc/nginx/conf.d/ssl.conf
</li></ul></code></pre>
<p>Then paste this configuration in. Be sure to change every instance of <code>example.com</code>, all four, with your own domain name:</p>
<div class="code-label " title="/etc/nginx/conf.d/ssl.conf">/etc/nginx/conf.d/ssl.conf</div><pre class="code-pre "><code langs="">server {
        listen 443 ssl;

        server_name <span class="highlight">example.com</span> www.<span class="highlight">example.com</span>;

        ssl_certificate /etc/letsencrypt/live/<span class="highlight">example.com</span>/fullchain.pem;
        ssl_certificate_key /etc/letsencrypt/live/<span class="highlight">example.com</span>/privkey.pem;

        ssl_protocols TLSv1 TLSv1.1 TLSv1.2;
        ssl_prefer_server_ciphers on;
        ssl_dhparam /etc/ssl/certs/dhparam.pem;
        ssl_ciphers 'ECDHE-RSA-AES128-GCM-SHA256:ECDHE-ECDSA-AES128-GCM-SHA256:ECDHE-RSA-AES256-GCM-SHA384:ECDHE-ECDSA-AES256-GCM-SHA384:DHE-RSA-AES128-GCM-SHA256:DHE-DSS-AES128-GCM-SHA256:kEDH+AESGCM:ECDHE-RSA-AES128-SHA256:ECDHE-ECDSA-AES128-SHA256:ECDHE-RSA-AES128-SHA:ECDHE-ECDSA-AES128-SHA:ECDHE-RSA-AES256-SHA384:ECDHE-ECDSA-AES256-SHA384:ECDHE-RSA-AES256-SHA:ECDHE-ECDSA-AES256-SHA:DHE-RSA-AES128-SHA256:DHE-RSA-AES128-SHA:DHE-DSS-AES128-SHA256:DHE-RSA-AES256-SHA256:DHE-DSS-AES256-SHA:DHE-RSA-AES256-SHA:AES128-GCM-SHA256:AES256-GCM-SHA384:AES128-SHA256:AES256-SHA256:AES128-SHA:AES256-SHA:AES:CAMELLIA:DES-CBC3-SHA:!aNULL:!eNULL:!EXPORT:!DES:!RC4:!MD5:!PSK:!aECDH:!EDH-DSS-DES-CBC3-SHA:!EDH-RSA-DES-CBC3-SHA:!KRB5-DES-CBC3-SHA';
        ssl_session_timeout 1d;
        ssl_session_cache shared:SSL:50m;
        ssl_stapling on;
        ssl_stapling_verify on;
        add_header Strict-Transport-Security max-age=15768000;

        location ~ /.well-known {
                allow all;
        }

        # The rest of your server block
        root /usr/share/nginx/html;
        index index.html index.htm;

        location / {
                # First attempt to serve request as file, then
                # as directory, then fall back to displaying a 404.
                try_files $uri $uri/ =404;
                # Uncomment to enable naxsi on this location
                # include /etc/nginx/naxsi.rules
        }
}
</code></pre>
<p>Save and exit. This configures Nginx to use SSL, and tells it to use the Let's Encrypt SSL certificate that we obtained earlier. Also, the SSL options specified here ensure that only the most secure protocols and ciphers will be used. Note that this example configuration simply serves the default Nginx page, so you may want to modify it to meet your needs.</p>

<p>Next, we'll configure Nginx to redirect HTTP requests on port 80 to HTTPS on port 443.</p>

<p>The default Nginx configuration file allows us to easily add directives to the port 80 server block by adding files in the <code>/etc/nginx/default.d</code> directory. Create a new file called <code>ssl-redirect.conf</code> and open it for editing with this command:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo vi /etc/nginx/default.d/ssl-redirect.conf
</li></ul></code></pre>
<p>Then paste in this line:</p>
<div class="code-label " title="/etc/nginx/default.d/ssl-redirect.conf">/etc/nginx/default.d/ssl-redirect.conf</div><pre class="code-pre "><code langs="">    return 301 https://$host$request_uri;
</code></pre>
<p>Save and exit. This configures the HTTP on port 80 (default) server block to redirect incoming requests to HTTPS.</p>

<p>Now reload Nginx:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo systemctl reload nginx
</li></ul></code></pre>
<p>You will also want to enable Nginx, so it starts when your server boots:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo systemctl enable nginx
</li></ul></code></pre>
<p>The Let's Encrypt TLS/SSL certificate is now in place. At this point, you should test that the TLS/SSL certificate works by visiting your domain via HTTPS in a web browser.</p>

<p>You can use the Qualys SSL Labs Report to see how your server configuration scores:</p>
<pre class="code-pre "><code langs=""><div class="secondary-code-label " title="In a web browser:">In a web browser:</div>https://www.ssllabs.com/ssltest/analyze.html?d=<span class="highlight">example.com</span>
</code></pre>
<p>This SSL setup should report an <strong>A+</strong> rating.</p>

<h2 id="step-4-—-set-up-auto-renewal">Step 4 — Set Up Auto Renewal</h2>

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
35 2 * * 1 /usr/bin/systemctl reload nginx
</code></pre>
<p>Save and exit. This will create a new cron job that will execute the <code>letsencrypt-auto renew</code> command every Monday at 2:30 am, and reload Nginx at 2:35am (so the renewed certificate will be used). The output produced by the command will be piped to a log file located at <code>/var/log/le-renewal.log</code>.</p>

<p><span class="note">For more information on how to create and schedule cron jobs, you can check our <a href="https://indiareads/community/tutorials/how-to-use-cron-to-automate-tasks-on-a-vps">How to Use Cron to Automate Tasks in a VPS</a> guide. <br /></span></p>

<h2 id="step-5-—-updating-the-let’s-encrypt-client-optional">Step 5 — Updating the Let’s Encrypt Client (optional)</h2>

<p>Whenever new updates are available for the client, you can update your local copy by running a <code>git pull</code> from inside the Let’s Encrypt directory:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">cd /opt/letsencrypt
</li><li class="line" prefix="$">sudo git pull
</li></ul></code></pre>
<p>This will download all recent changes to the repository, updating your client.</p>

<h2 id="conclusion">Conclusion</h2>

<p>That's it! Your web server is now using a free Let's Encrypt TLS/SSL certificate to securely serve HTTPS content.</p>

    