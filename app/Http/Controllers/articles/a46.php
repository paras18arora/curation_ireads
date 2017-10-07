<link rel='stylesheet' href='digital_ocean.css'><br><img src=https://community-cdn-digitalocean-com.global.ssl.fastly.net/assets/tutorials/images/large/Let&#39;s-Encrypt-twitter-%28apache%29.png?1461605852/> <br> 
      <h3 id="introduction">Introduction</h3>

<p>This tutorial will show you how to set up a TLS/SSL certificate from <a href="https://letsencrypt.org/">Let’s Encrypt</a> on an Ubuntu 14.04 server running Apache as a web server. We will also cover how to automate the certificate renewal process using a cron job.</p>

<p>SSL certificates are used within web servers to encrypt the traffic between the server and client, providing extra security for users accessing your application. Let’s Encrypt provides an easy way to obtain and install trusted certificates for free.</p>

<h2 id="prerequisites">Prerequisites</h2>

<p>In order to complete this guide, you will need:</p>

<ul>
<li>An Ubuntu 16.04 server with a non-root sudo user, which you can set up by following our <a href="https://indiareads/community/tutorials/initial-server-setup-with-ubuntu-16-04">Initial Server Setup</a> guide</li>
<li>The Apache web server installed with <a href="https://indiareads/community/tutorials/how-to-set-up-apache-virtual-hosts-on-ubuntu-14-04-lts">one or more domain names</a> properly configured</li>
</ul>

<p>When you are ready to move on, log into your server using your sudo account.</p>

<h2 id="step-1-—-install-the-server-dependencies">Step 1 — Install the Server Dependencies</h2>

<p>The first thing we need to do is to update the local package manager cache with:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo apt-get update
</li></ul></code></pre>
<p>We will need <code>git</code> in order to download the Let’s Encrypt client. To install <code>git</code>, run:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo apt-get install git
</li></ul></code></pre>
<h2 id="step-2-—-download-the-let’s-encrypt-client">Step 2 — Download the Let’s Encrypt Client</h2>

<p>Next, we will download the Let’s Encrypt client from its official repository, placing its files in a special location on the server. We will do this to facilitate the process of updating the repository files when a new release is available. Because the Let’s Encrypt client is still in beta, frequent updates might be necessary to correct bugs and implement new functionality.</p>

<p>We will clone the Let’s Encrypt repository under <code>/opt</code>, which is a standard directory for placing third-party software on Unix systems:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo git clone https://github.com/letsencrypt/letsencrypt /opt/letsencrypt
</li></ul></code></pre>
<p>This will create a local copy of the official Let’s Encrypt repository under <code>/opt/letsencrypt</code>.</p>

<h2 id="step-3-—-set-up-the-ssl-certificate">Step 3 — Set Up the SSL Certificate</h2>

<p>Generating the SSL Certificate for Apache using the Let’s Encrypt client is quite straightforward. The client will automatically obtain and install a new SSL certificate that is valid for the domains provided as parameters.</p>

<p>Access the <strong>letsencrypt</strong> directory:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">cd /opt/letsencrypt
</li></ul></code></pre>
<p>To execute the interactive installation and obtain a certificate that covers only a single domain, run the <code>letsencrypt-auto</code> command like so, where <span class="highlight">example.com</span> is your domain:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">./letsencrypt-auto --apache -d <span class="highlight">example.com</span>
</li></ul></code></pre>
<p>If you want to install a single certificate that is valid for multiple domains or subdomains, you can pass them as additional parameters to the command. The first domain name in the list of parameters will be the <strong>base</strong> domain used by Let’s Encrypt to create the certificate, and for that reason we recommend that you pass the bare top-level domain name as first in the list, followed by any additional subdomains or aliases:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">./letsencrypt-auto --apache -d <span class="highlight">example.com</span> -d <span class="highlight">www.example.com</span>
</li></ul></code></pre>
<p>For this example, the <strong>base</strong> domain will be <code>example.com</code>. </p>

<p>After the dependencies are installed, you will be presented with a step-by-step guide to customize your certificate options. You will be asked to provide an email address for lost key recovery and notices, and you will be able to choose between enabling both <code>http</code> and <code>https</code> access or forcing all requests to redirect to <code>https</code>.  It is usually safest to require <code>https</code>, unless you have a specific need for unencrypted <code>http</code> traffic.</p>

<p>When the installation is finished, you should be able to find the generated certificate files at <code>/etc/letsencrypt/live</code>. You can verify the status of your SSL certificate with the following link (don’t forget to replace <span class="highlight">example.com</span> with your <strong>base</strong> domain):</p>
<pre class="code-pre "><code langs="">https://www.ssllabs.com/ssltest/analyze.html?d=<span class="highlight">example.com</span>&latest
</code></pre>
<p>You should now be able to access your website using a <code>https</code> prefix.</p>

<h2 id="step-4-—-set-up-auto-renewal">Step 4 — Set Up Auto Renewal</h2>

<p>Let’s Encrypt certificates are valid for 90 days, but it’s recommended that you renew the certificates every 60 days to allow a margin of error. The Let's Encrypt client has a <code>renew</code> command that automatically checks the currently installed certificates and tries to renew them if they are less than 30 days away from the expiration date.</p>

<p>To trigger the renewal process for all installed domains, you should run:</p>
<pre class="code-pre "><code langs="">./letsencrypt-auto renew
</code></pre>
<p>Because we recently installed the certificate, the command will only check for the expiration date and print a message informing that the certificate is not due to renewal yet. The output should look similar to this:</p>
<pre class="code-pre "><code langs="">Checking for new version...
Requesting root privileges to run letsencrypt...
   /home/brennen/.local/share/letsencrypt/bin/letsencrypt renew

   -------------------------------------------------------------------------------
   Processing /etc/letsencrypt/renewal/<span class="highlight">example.com</span>.conf
   -------------------------------------------------------------------------------

   The following certs are not due for renewal yet:
     /etc/letsencrypt/live/<span class="highlight">example.com</span>/fullchain.pem (skipped)
     No renewals were attempted.
</code></pre>
<p>Notice that if you created a bundled certificate with multiple domains, only the base domain name will be shown in the output, but the renewal should be valid for all domains included in this certificate.</p>

<p>A practical way to ensure your certificates won’t get outdated is to create a cron job that will periodically execute the automatic renewal command for you. Since the renewal first checks for the expiration date and only executes the renewal if the certificate is less than 30 days away from expiration, it is safe to create a cron job that runs every week or even every day, for instance.</p>

<p>Let's edit the crontab to create a new job that will run the renewal command every week.  To edit the crontab for the root user, run:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo crontab -e
</li></ul></code></pre>
<p>You may be prompted to select an editor:</p>
<pre class="code-pre "><code langs=""><div class="secondary-code-label " title="Output">Output</div>no crontab for root - using an empty one

Select an editor.  To change later, run 'select-editor'.
  1. /bin/ed
  2. /bin/nano        <---- easiest
  3. /usr/bin/vim.basic
  4. /usr/bin/vim.tiny

Choose 1-4 [2]:
</code></pre>
<p>Unless you're more comfortable with <code>ed</code> or <code>vim</code>, press <strong>Enter</strong> to use <code>nano</code>, the default.</p>

<p>Include the following content at the end of the crontab, all in one line:</p>
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

<p>In this guide, we saw how to install a free SSL certificate from Let’s Encrypt in order to secure a website hosted with Apache. Because the Let’s Encrypt client is still in beta, we recommend that you check the official <a href="https://letsencrypt.org/blog/">Let’s Encrypt blog</a> for important updates from time to time.</p>

    