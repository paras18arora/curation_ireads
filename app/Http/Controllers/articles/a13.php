<link rel='stylesheet' href='digital_ocean.css'><br> <br> 
      <h3 id="introduction">Introduction</h3>

<p>SSL certificates are used within web servers to encrypt the traffic between server and client, providing extra security for users accessing your application. Let’s Encrypt provides an easy way to obtain and install trusted certificates for free.</p>

<p>This tutorial will show you how to set up TLS/SSL certificates from <a href="https://letsencrypt.org/">Let’s Encrypt</a> for securing multiple virtual hosts on Apache, within an Ubuntu 14.04 server.</p>

<p>We will also cover how to automate the certificate renewal process using a cron job.</p>

<h2 id="prerequisites">Prerequisites</h2>

<p>In order to complete this guide, you will need:</p>

<ul>
<li>An Ubuntu 14.04 server with a non-root sudo user, which you can set up by following our <a href="https://indiareads/community/tutorials/initial-server-setup-with-ubuntu-14-04">Initial Server Setup</a> guide</li>
<li>A functional Apache web server installation hosting <a href="https://indiareads/community/tutorials/how-to-set-up-apache-virtual-hosts-on-ubuntu-14-04-lts">multiple virtual hosts</a></li>
</ul>

<p>It is important that each virtual host is set up in its own separate configuration file, and can be accessed externally via browser. For a detailed guide on how to properly set up Apache virtual hosts on Ubuntu, follow this link.</p>

<p>For the purpose of this guide, we will install Let’s Encrypt certificates for the domains <code>example.com</code> and <code>test.com</code>. These will be referenced throughout the guide, but you should substitute them with your own domains while following along.</p>

<p>When you are ready to move on, log into your server using your sudo account.</p>

<h2 id="step-1-—-install-the-server-dependencies">Step 1 — Install the Server Dependencies</h2>

<p>The first thing we need to do is to update the package manager cache with:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo apt-get update
</li></ul></code></pre>
<p>We will need <code>git</code> in order to download the Let’s Encrypt client. To install <code>git</code>, run:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo apt-get install git 
</li></ul></code></pre>
<h2 id="step-2-—-download-the-let’s-encrypt-client">Step 2 — Download the Let’s Encrypt Client</h2>

<p>Next, we will download the Let’s Encrypt client from its official repository, placing its files in a special location on the server. We will do this to facilitate the process of updating the repository files when a new release is available. Because the Let’s Encrypt client is still in beta, frequent updates might be necessary to correct bugs and implement new functionality. </p>

<p>We will clone the Let’s Encrypt repository under <code>/opt</code>, which is a standard directory for placing third-party software on Unix systems:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo git clone https://github.com/letsencrypt/letsencrypt /opt/letsencrypt
</li></ul></code></pre>
<p>This will create a local copy of the official Let’s Encrypt repository under <code>/opt/letsencrypt</code>.</p>

<h2 id="step-3-—-set-up-the-certificates">Step 3 — Set Up the Certificates</h2>

<p>Generating an SSL Certificate for Apache using the Let’s Encrypt client is quite straightforward. The client will automatically obtain and install a new SSL certificate that is valid for the domains provided as parameters.</p>

<p>Although it is possible to bundle multiple Let’s Encrypt certificates together, even when the domain names are different, it is recommended that you create separate certificates for unique domain names. As a general rule of thumb, only subdomains of a particular domain should be bundled together.</p>

<h3 id="generating-the-first-ssl-certificate">Generating the first SSL certificate</h3>

<p>We will start by setting up the SSL certificate for the first virtual host, <code>example.com</code>.</p>

<p>Access the <code>letsencrypt</code> directory:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">cd /opt/letsencrypt
</li></ul></code></pre>
<p>Next, we will execute the interactive installation and obtain a bundled certificate that is valid for a domain and a subdomain, namely <code>example.com</code> as base domain and <code>www.example.com</code> as subdomain. You can include any additional subdomains that are currently configured in your Apache setup as either virtual hosts or aliases. </p>

<p>Run the <code>letsencrypt-auto</code> command with:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">./letsencrypt-auto --apache -d <span class="highlight">example.com</span> -d <span class="highlight">www.example.com</span>
</li></ul></code></pre>
<p>Notice that the first domain name in the list of parameters will be the <strong>base</strong> domain used by Let’s Encrypt to create the certificate, and for that reason we recommend that you pass the bare top-level domain name as first in the list, followed by any additional subdomains or aliases.</p>

<p>For this example, the <strong>base</strong> domain will be <code>example.com</code>. </p>

<p>After the dependencies are installed, you will be presented with a step-by-step guide to customize your certificate options. You will be asked to provide an email address for lost key recovery and notices, and you will be able to choose between enabling both <code>http</code> and <code>https</code> access or forcing all requests to redirect to <code>https</code>.</p>

<p>When the installation is finished, you should be able to find the generated certificate files at <code>/etc/letsencrypt/live</code>. You can verify the status of your SSL certificate with the following link (don’t forget to replace <span class="highlight">example.com</span> with your <strong>base</strong> domain):</p>
<pre class="code-pre "><code langs="">https://www.ssllabs.com/ssltest/analyze.html?d=<span class="highlight">example.com</span>&latest
</code></pre>
<p>You should now be able to access your website using a <code>https</code> prefix.</p>

<h3 id="generating-the-second-ssl-certificate">Generating the second SSL certificate</h3>

<p>Generating certificates for your additional virtual hosts should follow the same process described in the previous step. </p>

<p>Repeat the certificate install command, now with the second virtual host you want to secure with Let’s Encrypt:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">./letsencrypt-auto --apache -d <span class="highlight">test.com</span> -d <span class="highlight">www.test.com</span>
</li></ul></code></pre>
<p>For this example, the <strong>base</strong> domain will be <code>test.com</code>. </p>

<p>Again, you can verify the status of your SSL certificate with the following link (don’t forget to replace <span class="highlight">test.com</span> with your <strong>base</strong> domain):</p>
<pre class="code-pre "><code langs="">https://www.ssllabs.com/ssltest/analyze.html?d=<span class="highlight">test.com</span>&latest
</code></pre>
<p>If you want to generate certificates for additional virtual hosts, simply repeat the process, and don’t forget to use the bare top-level domain as your <strong>base</strong> domain. </p>

<h2 id="step-3-—-set-up-auto-renewal">Step 3 — Set Up Auto-Renewal</h2>

<p>Let’s Encrypt certificates are valid for 90 days, but it’s recommended that you renew the certificates every 60 days to allow a margin of error. The Let's Encrypt client has a <code>renew</code> command that automatically checks the currently installed certificates and tries to renew them if they are less than 30 days away from the expiration date.</p>

<p>To trigger the renewal process for all installed domains, you should run:</p>
<pre class="code-pre "><code langs="">./letsencrypt-auto renew
</code></pre>
<p>Because we recently installed the certificates, the command will only check for the expiration date and print a message informing that the certificate is not due to renewal yet. The output should look similar to this:</p>
<pre class="code-pre "><code langs="">Checking for new version...
Requesting root privileges to run letsencrypt...
   /root/.local/share/letsencrypt/bin/letsencrypt renew
Processing /etc/letsencrypt/renewal/example.com.conf

The following certs are not due for renewal yet:
  /etc/letsencrypt/live/example.com/fullchain.pem (skipped)
No renewals were attempted.
</code></pre>
<p>Notice that if you created a bundled certificate with multiple domains, only the base domain name will be shown in the output, but the renewal should be valid for all domains included in this certificate.</p>

<p>A practical way to ensure your certificates won’t get outdated is to create a cron job that will periodically execute the automatic renewal command for you. Since the renewal first checks for the expiration date and only executes the renewal if the certificate is less than 30 days away from expiration, it is safe to create a cron job that runs every week or even every day, for instance.</p>

<p>Let's edit the crontab to create a new job that will run the renewal command every week.  To edit the crontab for the root user, run:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo crontab -e
</li></ul></code></pre>
<p>Include the following content, all in one line:</p>
<pre class="code-pre "><code langs=""><div class="secondary-code-label " title="crontab">crontab</div>
30 2 * * 1 /opt/letsencrypt/letsencrypt-auto renew >> /var/log/le-renew.log
</code></pre>
<p>Save and exit. This will create a new cron job that will execute the <code>letsencrypt-auto renew</code> command every Monday at 2:30 am. The output produced by the command will be piped to a log file located at <code>/var/log/le-renewal.log</code>.</p>

<p><span class="note">For more information on how to create and schedule cron jobs, you can check our <a href="https://indiareads/community/tutorials/how-to-use-cron-to-automate-tasks-on-a-vps">How to Use Cron to Automate Tasks in a VPS</a> guide. <br /></span></p>

<h2 id="step-5-—-updating-the-let’s-encrypt-client-optional">Step 5 — Updating the Let’s Encrypt Client (optional)</h2>

<p>Whenever new updates are available for the client, you can update your local copy by running a <code>git pull</code> from inside the Let’s Encrypt directory:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">cd /opt/letsencrypt
</li><li class="line" prefix="$">sudo git pull
</li></ul></code></pre>
<p>This will download all recent changes to the repository, updating your client.</p>

<h2 id="conclusion">Conclusion</h2>

<p>In this guide, we saw how to install  free SSL certificates from Let’s Encrypt in order to secure multiple virtual hosts on Apache. Because the Let’s Encrypt client is still in beta, we recommend that you check the official <a href="https://letsencrypt.org/blog/">Let’s Encrypt blog</a> for important updates from time to time.</p>

    