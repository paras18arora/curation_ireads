<link rel='stylesheet' href='digital_ocean.css'><br><img src=https://community-cdn-digitalocean-com.global.ssl.fastly.net/assets/tutorials/images/large/Let&#39;s-Encrypt-twitter-%28HAProxy%29.png?1458317346/> <br> 
      <h3 id="introduction">Introduction</h3>

<p>Let's Encrypt is a new Certificate Authority (CA) that provides an easy way to obtain and install free TLS/SSL certificates, thereby enabling encrypted HTTPS on web servers. It simplifies the process by providing a software client, <code>letsencrypt</code>, that attempts to automate most (if not all) of the required steps. Currently, as Let's Encrypt is still in open beta, the entire process of obtaining and installing a certificate is fully automated only on Apache web servers. However, Let's Encrypt can be used to easily obtain a free SSL certificate, which can be installed manually, regardless of your choice of web server software.</p>

<p>In this tutorial, we will show you how to use Let's Encrypt to obtain a free SSL certificate and use it with HAProxy on Ubuntu 14.04. We will also show you how to automatically renew your SSL certificate.</p>

<p><img src="https://assets.digitalocean.com/articles/letsencrypt/haproxy-letsencrypt.png" alt="HAProxy with Let's Encrypt TLS/SSL Certificate and Auto-renewal" /></p>

<h2 id="prerequisites">Prerequisites</h2>

<p>Before following this tutorial, you'll need a few things.</p>

<p>You should have an Ubuntu 14.04 server with a non-root user who has <code>sudo</code> privileges.  You can learn how to set up such a user account by following steps 1-3 in our <a href="https://indiareads/community/articles/initial-server-setup-with-ubuntu-14-04">initial server setup for Ubuntu 14.04</a>.</p>

<p>You must own or control the registered domain name that you wish to use the certificate with. If you do not already have a registered domain name, you may register one with one of the many domain name registrars out there (e.g. Namecheap, GoDaddy, etc.).</p>

<p>If you haven't already, be sure to create an <strong>A Record</strong> that points your domain to the public IP address of your server. This is required because of how Let's Encrypt validates that you own the domain it is issuing a certificate for. For example, if you want to obtain a certificate for <code>example.com</code>, that domain must resolve to your server for the validation process to work. Our setup will use <code>example.com</code> and <code>www.example.com</code> as the domain names, so <strong>both DNS records are required</strong>.</p>

<p>Once you have all of the prerequisites out of the way, let's move on to installing the Let's Encrypt client software.</p>

<h2 id="step-1-—-install-let-39-s-encrypt-client">Step 1 — Install Let's Encrypt Client</h2>

<p>The first step to using Let's Encrypt to obtain an SSL certificate is to install the <code>letsencrypt</code> software on your server. Currently, the best way to install Let's Encrypt is to simply clone it from the official GitHub repository. In the future, it will likely be available via a package manager.</p>

<h3 id="install-git-and-bc">Install Git and bc</h3>

<p>Let's install Git and bc now, so we can clone the Let's Encrypt repository.</p>

<p>Update your server's package manager with this command:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo apt-get update
</li></ul></code></pre>
<p>Then install the <code>git</code> and <code>bc</code> packages with apt-get:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo apt-get -y install git bc
</li></ul></code></pre>
<p>With <code>git</code> and <code>bc</code> installed, we can easily download <code>letsencrypt</code> by cloning the repository from GitHub.</p>

<h3 id="clone-let-39-s-encrypt">Clone Let's Encrypt</h3>

<p>We can now clone the Let’s Encrypt repository in <code>/opt</code> with this command:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo git clone https://github.com/letsencrypt/letsencrypt /opt/letsencrypt
</li></ul></code></pre>
<p>You should now have a copy of the <code>letsencrypt</code> repository in the <code>/opt/letsencrypt</code> directory.</p>

<h2 id="step-2-—-obtain-a-certificate">Step 2 — Obtain a Certificate</h2>

<p>Let's Encrypt provides a variety of ways to obtain SSL certificates, through various plugins. Unlike the Apache plugin, which is covered in <a href="https://indiareads/community/tutorials/how-to-secure-apache-with-let-s-encrypt-on-ubuntu-14-04">a different tutorial</a>, most of the plugins will only help you with obtaining a certificate which you must manually configure your web server to use. Plugins that only obtain certificates, and don't install them, are referred to as "authenticators" because they are used to authenticate whether a server should be issued a certificate.</p>

<p>We'll show you how to use the <strong>Standalone</strong> plugin to obtain an SSL certificate.</p>

<h3 id="verify-port-80-is-open">Verify Port 80 is Open</h3>

<p>The Standalone plugin provides a very simple way to obtain SSL certificates. It works by temporarily running a small web server, on port <code>80</code>, on your server, to which the Let's Encrypt CA can connect and validate your server's identity before issuing a certificate. As such, this method requires that port <code>80</code> is not in use. That is, be sure to stop your normal web server, if it's using port <code>80</code> (i.e. <code>http</code>), before attempting to use this plugin.</p>

<p>For example, if you're using HAProxy, you can stop it by running this command:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo service haproxy stop
</li></ul></code></pre>
<p>If you're not sure if port <code>80</code> is in use, you can run this command:</p>
<pre class="code-pre "><code langs="">netstat -na | grep ':80.*LISTEN'
</code></pre>
<p>If there is no output when you run this command, you can use the Standalone plugin.</p>

<h3 id="run-let-39-s-encrypt">Run Let's Encrypt</h3>

<p>Before using Let's Encrypt, change to the <code>letsencrypt</code> directory:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">cd /opt/letsencrypt
</li></ul></code></pre>
<p>Now use the Standalone plugin by running this command:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">./letsencrypt-auto certonly --standalone
</li></ul></code></pre>
<p><span class="note"><strong>Note:</strong> The Let's Encrypt software requires superuser privileges, so you will be required to enter your password if you haven't used <code>sudo</code> recently.<br /></span></p>

<p>After <code>letsencrypt</code> initializes, you will be prompted for some information. This exact prompts may vary depending on if you've used Let's Encrypt before, but we'll step you through the first time.</p>

<p>At the prompt, enter an email address that will be used for notices and lost key recovery:</p>

<p><img src="https://assets.digitalocean.com/articles/letsencrypt/le-email.png" alt="Email prompt" /></p>

<p>Then you must agree to the Let's Encrypt Subscribe Agreement. Select Agree:</p>

<p><img src="https://assets.digitalocean.com/articles/letsencrypt/le-agreement.png" alt="Let's Encrypt Subscriber's Agreement" /></p>

<p>Then enter your domain name(s). Note that if you want a single cert to work with multiple domain names (e.g. <code>example.com</code> and <code>www.example.com</code>), be sure to include all of them:</p>

<p><img src="https://assets.digitalocean.com/articles/letsencrypt/le-domain.png" alt="Domain name prompt" /></p>

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

<p><span class="note"><strong>Note:</strong> If your domain is routing through a DNS service like CloudFlare, you will need to temporarily disable it until you have obtained the certificate.<br /></span></p>

<h3 id="certificate-files">Certificate Files</h3>

<p>After obtaining the cert, you will have the following PEM-encoded files:</p>

<ul>
<li><strong>cert.pem:</strong> Your domain's certificate</li>
<li><strong>chain.pem:</strong> The Let's Encrypt chain certificate</li>
<li><strong>fullchain.pem:</strong> <code>cert.pem</code> and <code>chain.pem</code> combined</li>
<li><strong>privkey.pem:</strong> Your certificate's private key</li>
</ul>

<p>It's important that you are aware of the location of the certificate files that were just created, so you can use them in your web server configuration. The files themselves are placed in a subdirectory in <code>/etc/letsencrypt/archive</code>. However, Let's Encrypt creates symbolic links to the most recent certificate files in the <code>/etc/letsencrypt/live/<span class="highlight">your_domain_name</span></code> directory. </p>

<p>You can check that the files exist by running this command (substituting in your domain name):</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo ls /etc/letsencrypt/live/<span class="highlight">your_domain_name</span>
</li></ul></code></pre>
<p>The output should be the four previously mentioned certificate files.</p>

<h3 id="combine-fullchain-pem-and-privkey-pem">Combine Fullchain.pem and Privkey.pem</h3>

<p>When configuring HAProxy to perform SSL termination, so it will encrypt traffic between itself and the end user, you must combine <code>fullchain.pem</code> and <code>privkey.pem</code> into a single file.</p>

<p>First, create the directory where the combined file will be placed, <code>/etc/haproxy/certs</code>:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo mkdir -p /etc/haproxy/certs
</li></ul></code></pre>
<p>Next, create the combined file with this <code>cat</code> command (substitute the highlighted <code>example.com</code> with your domain name):</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">DOMAIN='<span class="highlight">example.com</span>' sudo -E bash -c 'cat /etc/letsencrypt/live/$DOMAIN/fullchain.pem /etc/letsencrypt/live/$DOMAIN/privkey.pem > /etc/haproxy/certs/$DOMAIN.pem'
</li></ul></code></pre>
<p>Secure access to the combined file, which contains the private key, with this command:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo chmod -R go-rwx /etc/haproxy/certs
</li></ul></code></pre>
<p>Now we're ready to use the SSL cert and private key with HAProxy.</p>

<h2 id="step-3-—-install-haproxy">Step 3 — Install HAProxy</h2>

<p>This step covers the installation of HAProxy. If it's already installed on your server, skip this step.</p>

<p>We will install HAProxy 1.6, which is not in the default Ubuntu repositories. However, we can still use a package manager to install HAProxy 1.6, if we use a PPA, with this command:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo add-apt-repository ppa:vbernat/haproxy-1.6
</li></ul></code></pre>
<p>Update the local package index on your load balancers and install HAProxy by typing:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo apt-get update
</li><li class="line" prefix="$">sudo apt-get install haproxy
</li></ul></code></pre>
<p>HAProxy is now installed but needs to be configured.</p>

<h2 id="step-4-—-configure-haproxy">Step 4 — Configure HAProxy</h2>

<p>This section will show you how to configure basic HAProxy with SSL setup. It also covers how to configure HAProxy to allow us to auto-renew our Let's Encrypt certificate.</p>

<p>Open <code>haproxy.cfg</code> in a text editor:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo nano /etc/haproxy/haproxy.cfg
</li></ul></code></pre>
<p>Keep this file open as we edit it in the next several sections.</p>

<h3 id="global-section">Global Section</h3>

<p>Let's add some basic settings under the <code>global</code> section.</p>

<p>The first thing you will want to do is set <em>maxconn</em> to a reasonable number. This affects how many concurrent connections HAProxy will allow, which can affect QoS and prevent your web servers from crashing from trying to serve too many requests. You will need to play around with it to find what works for your environment. Add the following line (with a value you think is reasonable) to the <strong>global</strong> section:</p>
<div class="code-label " title="haproxy.cfg — 1 of 7">haproxy.cfg — 1 of 7</div><pre class="code-pre "><code langs="">   maxconn <span class="highlight">2048</span>
</code></pre>
<p>Next, add this line, to configure the maximum size of temporary DHE keys that are generated:</p>
<div class="code-label " title="haproxy.cfg — 2 of 7">haproxy.cfg — 2 of 7</div><pre class="code-pre "><code langs="">   tune.ssl.default-dh-param 2048
</code></pre>
<h3 id="defaults-section">Defaults Section</h3>

<p>Add the following lines under the <strong>defaults</strong> section:</p>
<div class="code-label " title="haproxy.cfg — 3 of 7">haproxy.cfg — 3 of 7</div><pre class="code-pre "><code langs="">   option forwardfor
   option http-server-close
</code></pre>
<p>The forwardfor option sets HAProxy to add <code>X-Forwarded-For</code> headers to each request, and the <code>http-server-close</code> option reduces latency between HAProxy and your users by closing connections but maintaining keep-alives.</p>

<h3 id="frontend-sections">Frontend Sections</h3>

<p>Now we're ready to define our <code>frontend</code> sections.</p>

<p>The first thing we want to add is a frontend to handle incoming HTTP connections, and send them to a default backend (which we'll define later). At the end of the file, let's add a frontend called <strong>www-http</strong>. Be sure to replace <code>haproxy_public_IP</code> with the public IP address of your HAProxy server:</p>
<div class="code-label " title="haproxy.cfg — 4 of 7">haproxy.cfg — 4 of 7</div><pre class="code-pre "><code langs="">frontend www-http
   bind <span class="highlight">haproxy_www_public_IP</span>:80
   reqadd X-Forwarded-Proto:\ http
   default_backend www-backend
</code></pre>
<p>Next, we will add a frontend to handle incoming HTTPS connections. At the end of the file, add a frontend called <strong>www-https</strong>. Be sure to replace <code>haproxy_www_public_IP</code> with the public IP of your HAProxy server. Also, you will need to replace <code>example.com</code> with your domain name (which should correspond to the certificate file you created earlier):</p>
<div class="code-label " title="haproxy.cfg — 5 of 7">haproxy.cfg — 5 of 7</div><pre class="code-pre "><code langs="">frontend www-https
   bind <span class="highlight">haproxy_www_public_IP</span>:443 ssl crt /etc/haproxy/certs/<span class="highlight">example.com</span>.pem
   reqadd X-Forwarded-Proto:\ https
   acl letsencrypt-acl path_beg /.well-known/acme-challenge/
   use_backend letsencrypt-backend if letsencrypt-acl
   default_backend www-backend
</code></pre>
<p>This frontend uses an ACL (<code>letsencrypt-acl</code>) to send Let's Encrypt validation requests (for <code>/.well-known/acme-challenge</code>) to the <code>letsencrypt-backend</code> backend, which will enable us to renew the certificate without stopping the HAProxy service. All other requests will be forwarded to the <code>www-backend</code>, which is the backend that will serve our web application or site.</p>

<h3 id="backend-sections">Backend Sections</h3>

<p>After you are finished configuring the frontends, add the <code>www-backend</code> backend by adding the following lines. Be sure to replace the highlighted words with the respective private IP addresses of your web servers (adjust the number of <code>server</code> lines to match how many backend servers you have):</p>
<div class="code-label " title="haproxy.cfg — 6 of 7">haproxy.cfg — 6 of 7</div><pre class="code-pre "><code langs="">backend www-backend
   redirect scheme https if !{ ssl_fc }
   server www-1 <span class="highlight">www_1_private_IP</span>:80 check
   server www-2 <span class="highlight">www_2_private_IP</span>:80 check
</code></pre>
<p>Any traffic that this backend receives will be balanced across its <code>server</code> entries, over HTTP (port 80).</p>

<p>Lastly, add the <code>letsencrypt-backend</code> backend, by adding these lines</p>
<div class="code-label " title="haproxy.cfg — 7 of 7">haproxy.cfg — 7 of 7</div><pre class="code-pre "><code langs="">backend letsencrypt-backend
   server letsencrypt 127.0.0.1:54321
</code></pre>
<p>This backend, which only handles Let's Encrypt ACME challenges that are used for certificate requests and renewals, sends traffic to the localhost on port <code>54321</code>. We'll use this port instead of <code>80</code> and <code>443</code> when we renew our Let's Encrypt SSL certificate.</p>

<p>Now we're ready to start HAProxy:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo service haproxy restart
</li></ul></code></pre>
<p><span class="note"><strong>Note:</strong> If you're having trouble with the <code>haproxy.cfg</code> configuration file, check out <a href="https://gist.github.com/thisismitch/7c91e9b2b63f837a0c4b">this GitHub Gist</a> for an example.<br /></span></p>

<p>The Let's Encrypt TLS/SSL certificate is now in place, and we're ready to set up the auto-renewal script. At this point, you should test that the TLS/SSL certificate works by visiting your domain in a web browser.</p>

<h2 id="step-5-—-set-up-auto-renewal">Step 5 — Set Up Auto Renewal</h2>

<p>Let’s Encrypt certificates are valid for 90 days, but it’s recommended that you renew the certificates every 60 days to allow a margin of error. At the time of this writing, automatic renewal is still not available as a feature of the client itself, but you can manually renew your certificates by running the Let’s Encrypt client again.</p>

<p>A practical way to ensure your certificates won’t get outdated is to create a cron job that will automatically handle the renewal process for you. In order to avoid the interactive, menu-driven process that we used earlier, we will use different parameters when calling the Let’s Encrypt client in the cron job.</p>

<p>We will use the Standalone plugin used earlier, but configure it to use port <code>54321</code> so it doesn't conflict with HAProxy (which is listening on port <code>80</code> and <code>443</code>). To do so, we'll use this command (substituting in your domain name for both highlighted <code>example.com</code> domains):</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">cd /opt/letsencrypt
</li><li class="line" prefix="$">./letsencrypt-auto certonly --agree-tos --renew-by-default --standalone-supported-challenges http-01 --http-01-port 54321 -d <span class="highlight">example.com</span> -d <span class="highlight">www.example.com</span>
</li></ul></code></pre>
<p>Once that succeeds, you will need to create a new combined certificate file (replace <code>example.com</code> with your domain name):</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">DOMAIN='<span class="highlight">example.com</span>' sudo -E bash -c 'cat /etc/letsencrypt/live/$DOMAIN/fullchain.pem /etc/letsencrypt/live/$DOMAIN/privkey.pem > /etc/haproxy/certs/$DOMAIN.pem'
</li></ul></code></pre>
<p>Then reload HAProxy to start using the new certificate:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo service haproxy reload
</li></ul></code></pre>
<p>Now that we know the commands that we need to renew our certificate, we can automate this process using scripts and a cron job.</p>

<h3 id="create-a-let-39-s-encrypt-configuration-file">Create a Let's Encrypt Configuration File</h3>

<p>Before moving on, let's simplify our renewal process by creating a Let's Encrypt configuration file at <code>/usr/local/etc/le-renew-haproxy.ini</code>.</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo cp /opt/letsencrypt/examples/cli.ini /usr/local/etc/le-renew-haproxy.ini
</li></ul></code></pre>
<p>Now open the file for editing;</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo nano /usr/local/etc/le-renew-haproxy.ini
</li></ul></code></pre>
<p>Next, uncomment the <code>email</code> and <code>domains</code> lines, and update them with your own information. The file (with comments removed) should look something like this:</p>
<div class="code-label " title="le-cli-example.com.ini — 1 of 2">le-cli-example.com.ini — 1 of 2</div><pre class="code-pre "><code langs="">rsa-key-size = 4096

email = <span class="highlight">you@example.com</span>

domains = <span class="highlight">example.com</span>, <span class="highlight">www.example.com</span>
</code></pre>
<p>Next, uncomment the <code>standalone-supported-challenges</code> line, and replace its value with <code>http-01</code>. This tells Let's Encrypt to use  It should look like this (with the changed value highlighted red):</p>
<div class="code-label " title="le-cli-example.com.ini — 2 of 2">le-cli-example.com.ini — 2 of 2</div><pre class="code-pre "><code langs="">standalone-supported-challenges = <span class="highlight">http-01</span>
</code></pre>
<p>Now, instead of specifying the domain names in the command, we can use the Let's Encrypt configuration file to fill in the blanks. Assuming your configuration file is correct, this command can be used to renew your certificate:</p>
<pre class="code-pre "><code langs="">cd /opt/letsencrypt
./letsencrypt-auto certonly --renew-by-default --config /usr/local/etc/le-renew-haproxy.ini --http-01-port 54321
</code></pre>
<p><span class="note"><strong>Note:</strong> If you're having trouble with the <code>le-renew-haproxy.ini</code> configuration file, check out <a href="https://gist.github.com/thisismitch/7c91e9b2b63f837a0c4b">this GitHub Gist</a> for an example.<br /></span></p>

<p>Now let's create a script that we can use to renew our certificate.</p>

<h3 id="create-a-renewal-script">Create a Renewal Script</h3>

<p>To automate the renewal process, we will use a shell script that will verify the certificate expiration date for the provided domain and request a renewal when the expiration is less than 30 days away. This script will be scheduled to run once a week. This way, even if a cron job fails, there’s a 30-day window to try again every week. </p>

<p>First, download the script and make it executable. Feel free to review the contents of the script before downloading it.</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo curl -L -o /usr/local/sbin/le-renew-haproxy https://gist.githubusercontent.com/thisismitch/7c91e9b2b63f837a0c4b/raw/700cfe953e5d5e71e528baf20337198195606630/le-renew-haproxy
</li><li class="line" prefix="$">sudo chmod +x /usr/local/sbin/le-renew-haproxy
</li></ul></code></pre>
<p>The <code>le-renew-haproxy</code> script takes as argument the domain name whose certificate you want to check for renewal. When the renewal is not yet necessary, it will simply output how many days are left until the given certificate expiration.</p>

<p><span class="note"><strong>Note:</strong> The script will not run if the <code>/usr/local/etc/le-renew-haproxy.ini</code> file does not exist. Also, be sure that the first domain that is specified in the configuration file is the same as the first domain you specified when you originally created the certificate.<br /></span></p>

<p>If you run the script now, you will be able to see how many days are left for this certificate to expire:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo le-renew-haproxy
</li></ul></code></pre><pre class="code-pre "><code langs=""><div class="secondary-code-label " title="output">output</div>Checking expiration date for <span class="highlight">example.com</span>...
The certificate is up to date, no need for renewal (89 days left).
</code></pre>
<p>Next, we will edit the crontab to create a new job that will run this command every week.  To edit the crontab for the root user, run:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo crontab -e
</li></ul></code></pre>
<p>Include the following content, all in one line:</p>
<div class="code-label " title="crontab entry">crontab entry</div><pre class="code-pre "><code langs="">30 2 * * 1 /usr/local/sbin/le-renew-haproxy >> /var/log/le-renewal.log
</code></pre>
<p>Save and exit. This will create a new cron job that will execute the <code>le-renew-haproxy</code> command every Monday at 2:30 am. The output produced by the command will be piped to a log file located at <code>/var/log/le-renewal.log</code>.</p>

<h2 id="conclusion">Conclusion</h2>

<p>That's it! HAProxy is now using a free Let's Encrypt TLS/SSL certificate to securely serve HTTPS traffic.</p>

    