<link rel='stylesheet' href='digital_ocean.css'><br> <br> 
      <h3 id="introduction">Introduction</h3>

<p>This tutorial will show you how to set up a TLS/SSL certificate from <a href="https://letsencrypt.org/">Let’s Encrypt</a> on a CentOS 7 server running Apache as a web server. Additionally, we will cover how to automate the certificate renewal process using a cron job.</p>

<p>SSL certificates are used within web servers to encrypt the traffic between server and client, providing extra security for users accessing your application. Let’s Encrypt provides an easy way to obtain and install trusted certificates for free.</p>

<h2 id="prerequisites">Prerequisites</h2>

<p>In order to complete this guide, you will need:</p>

<ul>
<li>A CentOS 7 server with a non-root sudo user, which you can set up by following our <a href="https://indiareads/community/tutorials/initial-server-setup-with-centos-7">Initial Server Setup</a> guide</li>
<li>The Apache web server installed with one or more domain names properly configured</li>
</ul>

<p>For the purpose of this guide, we will install a Let’s Encrypt certificate for the domain <code>example.com</code>. This will be referenced throughout the guide, but you should substitute it with your own domain while following along.</p>

<p>When you are ready to move on, log into your server using your sudo account.</p>

<h2 id="step-1-—-create-a-virtual-host-for-your-domain">Step 1 — Create a Virtual Host for your Domain</h2>

<p>The Apache plugin for Let’s Encrypt greatly simplifies the process of generating and installing SSL certificates for domains hosted with Apache. However, at the time of this writing, it is required that you have your domains organized into virtual hosts, each one in a separate configuration file. </p>

<p>If your domain is already configured as a virtual host in a separate configuration file, you can skip to the next step.</p>

<h3 id="create-the-directory-structure">Create the Directory Structure</h3>

<p>We will start by creating a new directory structure to hold your virtual host files inside the Apache configuration directory, <code>/etc/httpd</code>. We will follow the standard structure introduced by Debian based distributions, which makes it easier to enable and disable sites that are configured as virtual hosts within Apache.</p>

<p>Access the Apache configuration directory with:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">cd /etc/httpd
</li></ul></code></pre>
<p>First, create the directory that will hold all sites available on this server:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo mkdir /etc/httpd/sites-available
</li></ul></code></pre>
<p>Next, create the directory that will have the currently active (enabled) websites hosted on this server. This directory will contain only symbolic links to the virtual host files located inside <code>/etc/httpd/sites-available</code>.</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo mkdir /etc/httpd/sites-enabled
</li></ul></code></pre>
<p>Now we need to tell Apache how to find the virtual host files. To accomplish this, we will edit Apache's main configuration file and add a line declaring an optional directory for additional configuration files. Using your favorite command line text editor, open the file <code>/etc/httpd/conf/httpd.conf</code>:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo nano /etc/httpd/conf/httpd.conf
</li></ul></code></pre>
<p>Add this line to the end of the file:</p>
<div class="code-label " title="/etc/httpd/conf/httpd.conf">/etc/httpd/conf/httpd.conf</div><pre class="code-pre "><code langs="">IncludeOptional sites-enabled/*.conf
</code></pre>
<p>Save and close the file when you are done adding that line. </p>

<h3 id="create-a-new-virtual-host-file">Create a New Virtual Host File</h3>

<p>The next step is to create the virtual host configuration file. Using your favorite command line text editor, create a new file under <code>/etc/httpd/sites-available</code>. We will be naming the file <code>example.com.conf</code>, but you can choose the name of your choice. It is important, however, that the file ends with the <code>.conf</code> extension.</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo nano /etc/httpd/sites-available/<span class="highlight">example.com</span>.conf
</li></ul></code></pre>
<p>Add the following contents to the file, replacing <code>example.com</code> with your own domain:</p>
<div class="code-label " title="/etc/httpd/sites-available/example.com.conf">/etc/httpd/sites-available/example.com.conf</div><pre class="code-pre "><code langs=""><VirtualHost *:80>
    ServerName <span class="highlight">example.com</span>
    ServerAlias <span class="highlight">www.example.com</span>
    DocumentRoot <span class="highlight">/var/www/html</span>
    ErrorLog <span class="highlight">/var/log/apache/example.com/error.log</span>
</VirtualHost>

</code></pre>
<p>Save the file and exit. Below, you can find a brief explanation of each configuration option used for this example:</p>

<ul>
<li><strong>ServerName:</strong> your main domain name.</li>
<li><strong>ServerAlias (optional):</strong> an alias for your main domain. It is a common practice to add the <code>www</code> subdomain as an alias of the main domain. </li>
<li><strong>DocumentRoot:</strong> the location where the website files should be found. With the default Apache configuration on CentOS 7, the main document root is typically  <code>/var/www/html</code>, but you can change this value if you want to place your website files in a different location on the server.</li>
<li><strong>ErrorLog (optional):</strong> a custom location for logging errors specific to this virtual host. If you don’t specify this option, errors will be logged to the default Apache error log: <code>/var/log/httpd/error_log</code> .</li>
</ul>

<h3 id="enabling-the-virtual-host">Enabling the Virtual Host</h3>

<p>The virtual host file is now created, but we still need to tell Apache that we want this website to be enabled. In order to accomplish that, we need to create a symbolic link inside <code>sites-enabled</code> pointing to the new virtual host configuration file. Run:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo ln -s /etc/httpd/sites-available/<span class="highlight">example.com.conf</span> /etc/httpd/sites-enabled/<span class="highlight">example.com.conf</span>
</li></ul></code></pre>
<p>This way, whenever you want to disable a virtual host, you can simply remove the link inside <code>sites-enabled</code> and reload the Apache service, keeping the original virtual host file inside <code>sites-available</code> for any future needs.</p>

<p>If your domain was previously configured as the main Apache website inside the <code>httpd.conf</code> file, it is important that you remove the old configuration from that file, in order to avoid unexpected behavior when generating your SSL certificate. </p>

<p>Open the file <code>/etc/httpd/conf/httpd.conf</code> and search for the directives <code>ServerName</code> and <code>ServerAlias</code>. If they are set to the same domain you configured as a virtual host, you should comment them out by adding a <code>#</code> sign at the beginning of the line:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo nano /etc/httpd/conf/httpd.conf
</li></ul></code></pre><div class="code-label " title="/etc/httpd/conf/httpd.conf">/etc/httpd/conf/httpd.conf</div><pre class="code-pre "><code langs="">
# ServerName gives the name and port that the server uses to identify itself.
# This can often be determined automatically, but we recommend you specify
# it explicitly to prevent problems during startup.
#
# If your host doesn't have a registered DNS name, enter its IP address here.
#
#ServerName example.com:80
#ServerAlias www.example.com
</code></pre>
<p>All that’s left to do now is to restart Apache so the changes take effect:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo systemctl restart httpd
</li></ul></code></pre>
<p><span class="note">For a detailed guide on <a href="https://indiareads/community/tutorials/how-to-set-up-apache-virtual-hosts-on-centos-7">how to set up Apache virtual hosts on CentOS 7</a>, check this link.</span></p>

<h2 id="step-2-—-install-the-server-dependencies">Step 2 — Install the Server Dependencies</h2>

<p>Before we can install the Let’s Encrypt client and generate the SSL certificate, we need to install a few dependencies on our CentOS server.</p>

<p>First, install the EPEL (Extra Packages for Enterprise Linux) repository:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo yum install epel-release
</li></ul></code></pre>
<p>We will need <code>git</code> in order to download the Let’s Encrypt client. To install <code>git</code>, run:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo yum install git 
</li></ul></code></pre>
<p>You should now be ready to follow the rest of this guide.</p>

<h2 id="step-3-—-download-the-let’s-encrypt-client">Step 3 — Download the Let’s Encrypt Client</h2>

<p>Next, we will download the Let’s Encrypt client from its official repository, placing its files in a special location on the server. We will do this to facilitate the process of updating the repository files when a new release is available. Because the Let’s Encrypt client is still in beta, frequent updates might be necessary to correct bugs and implement new functionality. </p>

<p>We will clone the Let’s Encrypt repository under <code>/opt</code>, which is a standard directory for placing third-party software on Unix systems:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo git clone https://github.com/letsencrypt/letsencrypt /opt/letsencrypt
</li></ul></code></pre>
<p>This will create a local copy of the official Let’s Encrypt repository under <code>/opt/letsencrypt</code>.</p>

<h2 id="step-4-—-set-up-the-ssl-certificate">Step 4 — Set Up the SSL Certificate</h2>

<p>Generating the SSL Certificate for Apache using the Let’s Encrypt client is quite straightforward. The client will automatically obtain and install a new SSL certificate that is valid for the domains provided as parameters.</p>

<p>Access the <code>letsencrypt</code> directory:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">cd /opt/letsencrypt
</li></ul></code></pre>
<p>To execute the interactive installation and obtain a certificate that covers only a single domain, run the <code>letsencrypt-auto</code> command with:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">./letsencrypt-auto --apache -d <span class="highlight">example.com</span>
</li></ul></code></pre>
<p>If you want to install a single certificate that is valid for multiple domains or subdomains, you can pass them as additional parameters to the command. The first domain name in the list of parameters will be the <strong>base</strong> domain used by Let’s Encrypt to create the certificate, and for that reason we recommend that you pass the bare top-level domain name as first in the list, followed by any additional subdomains or aliases:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">./letsencrypt-auto --apache -d <span class="highlight">example.com</span> -d <span class="highlight">www.example.com</span>
</li></ul></code></pre>
<p>For this example, the <strong>base</strong> domain will be <code>example.com</code>. </p>

<p>After the dependencies are installed, you will be presented with a step-by-step guide to customize your certificate options. You will be asked to provide an email address for lost key recovery and notices, and you will be able to choose between enabling both <code>http</code> and <code>https</code> access or forcing all requests to redirect to <code>https</code>.</p>

<p>When the installation is successfully finished, you should see a message similar to this:</p>
<pre class="code-pre "><code langs="">IMPORTANT NOTES:
 - If you lose your account credentials, you can recover through
   e-mails sent to user@example.com.
 - Congratulations! Your certificate and chain have been saved at
   /etc/letsencrypt/live/example.com/fullchain.pem. Your cert
   will expire on 2016-04-21. To obtain a new version of the
   certificate in the future, simply run Let's Encrypt again.
 - Your account credentials have been saved in your Let's Encrypt
   configuration directory at /etc/letsencrypt. You should make a
   secure backup of this folder now. This configuration directory will
   also contain certificates and private keys obtained by Let's
   Encrypt so making regular backups of this folder is ideal.
 - If you like Let's Encrypt, please consider supporting our work by:

   Donating to ISRG / Let's Encrypt:   https://letsencrypt.org/donate
   Donating to EFF:                    https://eff.org/donate-le

</code></pre>
<p>The generated certificate files should be available on <code>/etc/letsencrypt/live</code>. </p>

<h3 id="reorganizing-your-virtual-hosts">Reorganizing your Virtual Hosts</h3>

<p>The Apache plugin for Let’s Encrypt creates a new virtual host file for enabling <code>https</code> access for your domain. This is done automatically by the client when you generate your certificate using the Apache plugin. However, the file is created inside <code>sites-enabled</code>, a directory that should contain only links for the actual virtual host files that are located inside the <code>sites-available</code> directory.</p>

<p>To keep your virtual host files organized and consistent with defaults, it is a good idea to move this new virtual host file to the <code>sites-available</code> directory and create a symbolic link inside <code>sites-enabled</code>:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo mv /etc/httpd/sites-enabled/<span class="highlight">example.com</span>-le-ssl.conf /etc/httpd/sites-available/<span class="highlight">example.com</span>-le-ssl.conf
</li><li class="line" prefix="$">sudo ln -s /etc/httpd/sites-available/<span class="highlight">example.com</span>-le-ssl.conf /etc/httpd/sites-enabled/<span class="highlight">example.com</span>-le-ssl.conf
</li></ul></code></pre>
<p>Your <code>sites-enabled</code> directory should look similar to this:</p>
<div class="code-label " title="ls -la /etc/httpd/sites-enabled">ls -la /etc/httpd/sites-enabled</div><pre class="code-pre "><code langs="">lrwxrwxrwx 1 root root   48 Jan 25 12:37 <span class="highlight">example.com</span>.conf -> /etc/httpd/sites-available/<span class="highlight">example.com</span>.conf
lrwxrwxrwx 1 root root   55 Jan 25 12:44 <span class="highlight">example.com</span>-le-ssl.conf -> /etc/httpd/sites-available/<span class="highlight">example.com</span>-le-ssl.conf
</code></pre>
<p>Restart Apache to apply the changes:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo systemctl restart httpd
</li></ul></code></pre>
<h3 id="checking-your-certificate-status">Checking your Certificate Status</h3>

<p>You can verify the status of your SSL certificate with the following link (don’t forget to replace <span class="highlight">example.com</span> with your <strong>base</strong> domain):</p>
<pre class="code-pre "><code langs="">https://www.ssllabs.com/ssltest/analyze.html?d=<span class="highlight">example.com</span>&latest
</code></pre>
<p>You should now be able to access your website using a <code>https</code> prefix.</p>

<h2 id="step-5-—-set-up-auto-renewal">Step 5 — Set Up Auto Renewal</h2>

<p>Let’s Encrypt certificates are valid for 90 days, but it’s recommended that you renew the certificates every 60 days to allow a margin of error. The Let's Encrypt client has a <code>renew</code> command that automatically checks the currently installed certificates and tries to renew them if they are less than 30 days away from the expiration date.</p>

<p>To trigger the renewal process for all installed domains, you should run:</p>
<pre class="code-pre "><code langs="">./letsencrypt-auto renew
</code></pre>
<p>Because we recently installed the certificate, the command will only check for the expiration date and print a message informing that the certificate is not due to renewal yet. The output should look similar to this:</p>
<pre class="code-pre "><code langs="">Checking for new version...
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
<p>Include the following content, all in one line:</p>
<pre class="code-pre "><code langs=""><div class="secondary-code-label " title="crontab">crontab</div>
30 2 * * 1 /opt/letsencrypt/letsencrypt-auto renew >> /var/log/le-renew.log
</code></pre>
<p>Save and exit. This will create a new cron job that will execute the <code>letsencrypt-auto renew</code> command every Monday at 2:30 am. The output produced by the command will be piped to a log file located at <code>/var/log/le-renewal.log</code>.</p>

<p><span class="note">For more information on how to create and schedule cron jobs, you can check our <a href="https://indiareads/community/tutorials/how-to-use-cron-to-automate-tasks-on-a-vps">How to Use Cron to Automate Tasks in a VPS</a> guide. <br /></span></p>

<h2 id="step-6-—-updating-the-let’s-encrypt-client-optional">Step 6 — Updating the Let’s Encrypt Client (optional)</h2>

<p>Whenever new updates are available for the client, you can update your local copy by running a <code>git pull</code> from inside the Let’s Encrypt directory:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">cd /opt/letsencrypt
</li><li class="line" prefix="$">sudo git pull
</li></ul></code></pre>
<p>This will download all recent changes to the repository, updating your client.</p>

<h2 id="conclusion">Conclusion</h2>

<p>In this guide, we saw how to install a free SSL certificate from Let’s Encrypt in order to secure a website hosted with Apache, on a CentOS 7 server. Because the Let’s Encrypt client is still in beta, we recommend that you check the official <a href="https://letsencrypt.org/blog/">Let’s Encrypt blog</a> for important updates from time to time.</p>

    