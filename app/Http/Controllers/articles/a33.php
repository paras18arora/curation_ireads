<link rel='stylesheet' href='digital_ocean.css'><br> <br> 
      <h3 id="introduction">Introduction</h3>

<p><a href="http://www.discourse.org">Discourse</a> is an open source community discussion platform built for the modern web. </p>

<p>This tutorial will walk you through the steps of configuring Discourse, moving it behind a reverse proxy with Nginx, and configuring an SSL certificate for it with <a href="https://letsencrypt.org/">Let's Encrypt</a>. Moving Discourse behind a reserve proxy provides you with the flexibility to run other websites on your Droplet.</p>

<h2 id="prerequisites">Prerequisites</h2>

<p>Before we get started, be sure you have the following:</p>

<ul>
<li>Ubuntu 14.04 Droplet (1 GB or bigger)</li>
<li>Non-root user with sudo privileges (<a href="https://indiareads/community/tutorials/initial-server-setup-with-ubuntu-14-04">Initial Server Setup with Ubuntu 14.04</a> explains how to set this up.)</li>
<li>Discourse installed using <a href="https://indiareads/community/tutorials/how-to-install-discourse-on-ubuntu-14-04">this tutorial</a></li>
<li>Fully registered domain. You can purchase one on <a href="https://namecheap.com">Namecheap</a> or get one for free on <a href="http://www.freenom.com/en/index.html">Freenom</a>.</li>
<li>Make sure your domain name is configured to point to your Droplet. Check out <a href="https://indiareads/community/tutorials/how-to-set-up-a-host-name-with-digitalocean">this tutorial</a> if you need help.</li>
</ul>

<p>All the commands in this tutorial should be run as a non-root user. If root access is required for the command, it will be preceded by <code>sudo</code>.</p>

<h2 id="step-1-—-configuring-discourse">Step 1 — Configuring Discourse</h2>

<p>Now that you have Discourse installed, we need to configure it to work behind Nginx.</p>

<p><span class="warning"><strong>Warning</strong>: This will incur downtime on your Discourse forum until we configure Nginx. Make sure this is a fresh install of Discourse or have a backup server until configuration is complete.<br /></span></p>

<p>There's just one setting we'll need to change to Discourse so we can move it behind Nginx. Change into the directory that contains the configuration file:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">cd /var/discourse
</li></ul></code></pre>
<p>Then, open the configuration file we need to change:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo nano containers/app.yml
</li></ul></code></pre>
<p>Using the arrow keys, scroll down to the <code>expose</code> section (it should be near the top) and change the first port number on this line:</p>
<div class="code-label " title="/var/discourse/containers/app.yml">/var/discourse/containers/app.yml</div><pre class="code-pre "><code langs="">...
## which TCP/IP ports should this container expose?
expose:
  - "<span class="highlight">25654</span>:80"   # fwd host port 80   to container port 80 (http)
...
</code></pre>
<p>This number can be random and shouldn't be shared with others. You can even block unauthorized access to it <a href="https://indiareads/community/tutorials/how-to-set-up-a-firewall-using-iptables-on-ubuntu-14-04">with an iptables firewall rule</a> if you'd like.</p>

<p>Now save and exit the text editor.</p>

<p>Enable the configuration change by running:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo ./launcher rebuild app
</li></ul></code></pre>
<p>This step might take a while, so please be patient.</p>

<p>You can verify everything is working by visiting your website. Your domain name for Discourse (such as <code>http://<span class="highlight">discourse.example.com</span></code>) will no longer load the interface in a web browser, but it should be accessible if you use the port just configured for Discourse such as <code>http:///<span class="highlight">discourse.example.com</span>:<span class="highlight">25654</span></code> (replace <span class="highlight">discourse.example.com</span> with your domain name and <span class="highlight">25654</span> with the port you just used in this step).</p>

<h2 id="step-2-—-installing-and-configuring-nginx">Step 2 — Installing and Configuring Nginx</h2>

<p>Now that Discourse is installed and configured to work behind Nginx, it is time to install Nginx.</p>

<p>To install Nginx on Ubuntu, simply enter this command and the installation will start:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo apt-get install nginx
</li></ul></code></pre>
<p>Browsing to your old Discourse URL at <code>http://<span class="highlight">discourse.example.com</span></code> will show the default Nginx webpage:</p>

<p><img src="https://assets.digitalocean.com/articles/discouse_behind_nginx/default-webpage.png" alt="Default Nginx landing page" /></p>

<p>This is fine. We'll change this to your forum now. First, let's stop Nginx:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo service nginx stop
</li></ul></code></pre>
<p>Then, delete this default webpage configuration — we won't need it:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo rm /etc/nginx/sites-enabled/default
</li></ul></code></pre>
<p>Next, we'll make a new configuration file for our Discourse server, which we'll name <code>discourse</code>.</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo nano /etc/nginx/sites-enabled/discourse
</li></ul></code></pre>
<p>Copy and paste in the following configuration. Replace <code><span class="highlight">discourse.example.com</span></code> with your domain name and <code><span class="highlight">25654</span></code> with the port you just used in the previous step:</p>
<div class="code-label " title="/etc/nginx/sites-enabled/discourse">/etc/nginx/sites-enabled/discourse</div><pre class="code-pre "><code langs="">server {
        listen 80;
        server_name <span class="highlight">discourse.example.com</span>;
        return 301 https://<span class="highlight">discourse.example.com</span>$request_uri;
}
server {
        listen 443 ssl spdy; 
        server_name <span class="highlight">discourse.example.com</span>;
        ssl_certificate /etc/letsencrypt/live/<span class="highlight">discourse.example.com</span>/fullchain.pem;
        ssl_certificate_key /etc/letsencrypt/live/<span class="highlight">discourse.example.com</span>/privkey.pem;
        ssl_protocols TLSv1 TLSv1.1 TLSv1.2;
        ssl_ciphers 'ECDHE-RSA-AES128-GCM-SHA256:ECDHE-ECDSA-AES128-GCM-SHA256:ECDHE-RSA-AES256-GCM-SHA384:ECDHE-ECDSA-AES256-GCM-SHA384:DHE-RSA-AES128-GCM-SHA256:DHE-DSS-AES128-GCM-SHA256:kEDH+AESGCM:ECDHE-RSA-AES128-SHA256:ECDHE-ECDSA-AES128-SHA256:ECDHE-RSA-AES128-SHA:ECDHE-ECDSA-AES128-SHA:ECDHE-RSA-AES256-SHA384:ECDHE-ECDSA-AES256-SHA384:ECDHE-RSA-AES256-SHA:ECDHE-ECDSA-AES256-SHA:DHE-RSA-AES128-SHA256:DHE-RSA-AES128-SHA:DHE-DSS-AES128-SHA256:DHE-RSA-AES256-SHA256:DHE-DSS-AES256-SHA:DHE-RSA-AES256-SHA:ECDHE-RSA-DES-CBC3-SHA:ECDHE-ECDSA-DES-CBC3-SHA:AES128-GCM-SHA256:AES256-GCM-SHA384:AES128-SHA256:AES256-SHA256:AES128-SHA:AES256-SHA:AES:CAMELLIA:DES-CBC3-SHA:!aNULL:!eNULL:!EXPORT:!DES:!RC4:!MD5:!PSK:!aECDH:!EDH-DSS-DES-CBC3-SHA:!EDH-RSA-DES-CBC3-SHA:!KRB5-DES-CBC3-SHA';
        ssl_prefer_server_ciphers on;
        location / {
                proxy_pass      http://<span class="highlight">discourse.example.com</span>:<span class="highlight">25654</span>/;
                proxy_read_timeout      90;
                proxy_redirect  http://<span class="highlight">discourse.example.com</span>:<span class="highlight">25654</span>/ https://<span class="highlight">discourse.example.com</span>;
        }
}
</code></pre>
<p>Here's what this config does:</p>

<ul>
<li>The first server block is listening on the <code><span class="highlight">discourse.example.com</span></code> domain on port 80, and it redirects all requests to SSL on port 443. This is optional, but it forces SSL on your website for all users.</li>
<li>The second server block is on port 443 and is passing requests to the web server running on port <code><span class="highlight">25654</span></code> (in this case, Discourse). This essentially uses a reverse proxy to send Discourse pages to your users and back over SSL.</li>
</ul>

<p>You may have noticed we're referencing some certificates at <code>/etc/letsencrypt</code>. In the next step we'll generate those before restarting Nginx.</p>

<h2 id="step-3-—-generating-the-ssl-certificates">Step 3 — Generating the SSL Certificates</h2>

<p>To generate the SSL certificates, we will first install the Let's Encrypt's ACME client. This software allows us to generate SSL certificates.</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo git clone https://github.com/letsencrypt/letsencrypt /opt/letsencrypt
</li></ul></code></pre>
<p>Then go to the <code>letsencrypt</code> directory:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">cd /opt/letsencrypt/
</li></ul></code></pre>
<p>Install the packages required by Let's Encrypt the first time:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">./letsencrypt-auto --help
</li></ul></code></pre>
<p>Now we can generate your certificates by running (replace with your email address and domain name):</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">./letsencrypt-auto certonly --standalone --email <span class="highlight">sammy@example.com</span> --agree-tos -d <span class="highlight">discourse.example.com</span>
</li></ul></code></pre>
<p><span class="note"><strong>Note:</strong> Let's Encrypt will only issue certificates for domain names. You will get an error if you try to use an IP address. If you need a domain name, check out the links in the Prerequisites section.<br /></span></p>

<p>You should get a response fairly quickly, similar to this:</p>
<div class="code-label " title="Let's Encrypt Output">Let's Encrypt Output</div><pre class="code-pre "><code langs="">IMPORTANT NOTES:
 - If you lose your account credentials, you can recover through
   e-mails sent to <span class="highlight">sammy@example.com</span>.
 - Congratulations! Your certificate and chain have been saved at
   /etc/letsencrypt/live/<span class="highlight">discourse.example.com</span>/fullchain.pem. Your
   cert will expire on <span class="highlight">2016-04-26</span>. To obtain a new version of the
   certificate in the future, simply run Let's Encrypt again.
 - Your account credentials have been saved in your Let's Encrypt
   configuration directory at /etc/letsencrypt. You should make a
   secure backup of this folder now. This configuration directory will
   also contain certificates and private keys obtained by Let's
   Encrypt so making regular backups of this folder is ideal.
</code></pre>
<p>You'll notice it said your certificates were saved in <code>/etc/letsencrypt/live/<span class="highlight">discourse.example.com</span></code>. This means our Nginx config is now valid. You'll also notice that expiration date isn't too far away. This is normal with Let's Encrypt certificates. All you have to do to renew is run that exact same command again, but logging in every 90 days isn't fun, so we'll automate it in our next step.</p>

<h2 id="step-4-—-automating-the-let-39-s-encrypt-certificate-renewal">Step 4 — Automating the Let's Encrypt Certificate Renewal</h2>

<p>Now that we've set up our certificates for the first time, we should make sure they renew automatically. Let's Encrypt certificates are only valid for 90 days, after which they will expire and display a warning to all visitors to your site in the browser. At the time of writing auto-renewal is not built into the client, but we can set up a script to manually renew them.</p>

<p>Refer to the <a href="https://indiareads/community/tutorials/how-to-secure-nginx-with-let-s-encrypt-on-ubuntu-14-04#step-4-%E2%80%94-set-up-auto-renewal">Set Up Auto Renewal</a> step of <a href="https://indiareads/community/tutorials/how-to-secure-nginx-with-let-s-encrypt-on-ubuntu-14-04">How To Secure Nginx with Let's Encrypt on Ubuntu 14.04</a> for details on setting up a cron job to renew your certificate automatically.</p>

<p>Any output created by this command will be at <code>/var/log/certificate-renewal.log</code> for troubleshooting.</p>

<h2 id="step-5-—-restarting-nginx">Step 5 — Restarting Nginx</h2>

<p>Finally, our configuration should be complete. Restart Nginx by running this command:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo service nginx restart
</li></ul></code></pre>
<p>Now if you browse to <code>https://<span class="highlight">discourse.example.com</span>/</code> your website should be online and secured with Let's Encrypt, shown as a green lock in most browsers.</p>

<h2 id="conclusion">Conclusion</h2>

<p>That's it! You now have a Discourse forum set up behind Nginx, secured with the latest SSL standards with Let's Encrypt.</p>

    