<link rel='stylesheet' href='digital_ocean.css'><br><img src=https://community-cdn-digitalocean-com.global.ssl.fastly.net/assets/tutorials/images/large/mail-in-a-box_twitter.png?1435249614/> <br> 
      <h3 id="introduction">Introduction</h3>

<p><a href="https://mailinabox.email/">Mail-in-a-Box</a> is an open source software bundle that makes it easy to turn your Ubuntu server into a full-stack email solution for multiple domains.</p>

<p>For securing the server, Mail-in-a-Box makes use of Fail2ban and an SSL certificate (self-signed by default). It auto-configures a UFW firewall with all the required ports open. Its anti-spam and other security features include graylisting, SPF, DKIM, DMARC, opportunistic TLS, strong ciphers, HSTS, and DNSSEC (with DANE TLSA).</p>

<p>Mail-in-a-Box is designed to handle SMTP, IMAP/POP, spam filtering, webmail, and even DNS as part of its all-in-one solution. Since the server itself is handling your DNS, you'll get an off-the-shelf DNS solution optimized for mail. Basically, this means you'll get sophisticated DNS records for your email (including SPF and DKIM records) without having to research and set them up manually. You can tweak your DNS settings afterwards as needed, but the defaults should work very well for most users hosting their own mail.</p>

<p>This tutorial shows how to set up Mail-in-a-Box on a IndiaReads Droplet running Ubuntu 14.04 x86-64.</p>

<h2 id="prerequisites">Prerequisites</h2>

<p>Mail-in-a-Box is very particular about the resources that are available to it. Specifically, it requires:</p>

<ul>
<li>An Ubuntu 14.04 x86-64 Droplet</li>
<li>The server must have at least 768 MB of RAM (1 GB recommended)</li>
<li>Be sure that the server has been set up along the lines given in <a href="https://indiareads/community/tutorials/initial-server-setup-with-ubuntu-14-04">this tutorial</a>, including adding a sudo user and disabling password SSH access for the root user (and possibly all users if your SSH keys are set up)</li>
<li>When setting up the IndiaReads Droplet, the name should be set to <strong><span class="highlight">box.example.com</span></strong>. Setting the hostname is discussed later in this tutorial</li>
<li>We'll go into more detail later, but your domain registrar needs to support setting custom nameservers and glue records so you can host your own DNS on your Droplet; the term <em>vanity nameservers</em> is frequently used</li>
<li>(Optional) Purchase an <a href="https://indiareads/community/tutorials/how-to-install-an-ssl-certificate-from-a-commercial-certificate-authority">SSL certificate</a> to use in place of the self-signed one; this is recommended for production environments</li>
</ul>

<p>On the RAM requirement, the installation script will abort with the following output if the RAM requirement is not met:</p>
<pre class="code-pre "><code langs=""><div class="secondary-code-label " title="Error">Error</div>Your Mail-in-a-Box needs more memory (RAM) to function properly.
Please provision a machine with at least 768 MB, 1 GB recommended.
This machine has 513 MB memory
</code></pre>
<p>Before embarking on this, be sure that you have an Ubuntu server with 1 GB of RAM.</p>

<p>For this article, we'll assume that the domain for which you are setting up an email server is <strong><span class="highlight">example.com</span></strong>. You are, of course, expected to replace this with your real domain name.</p>

<h2 id="step-1-—-configure-hostname">Step 1 — Configure Hostname</h2>

<p>In this step, you'll learn how to set the hostname properly, if it is not already set. Then you'll modify the <code>/etc/hosts</code> file to match.</p>

<p>From here on, it is assumed that you're logged into your IndiaReads account and also logged into the server as a sudo user via SSH using:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">ssh <span class="highlight">sammy@your_server_ip</span>
</li></ul></code></pre>
<p>Officially, it is recommended that the hostname of your server be set to <code><span class="highlight">box.example.com</span></code>. This should also be the name of the Droplet as it appears on your IndiaReads dashboard. If the name of the Droplet is set to just the domain name, rename it by clicking on the name of the Droplet, then <strong>Settings > Rename</strong>.</p>

<p>After setting the name of the Droplet as recommended, verify that it matches what appears in the <code>/etc/hostname</code> file by typing the command:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">hostname
</li></ul></code></pre>
<p>The output should read something like this:</p>
<pre class="code-pre "><code langs=""><div class="secondary-code-label " title="Output">Output</div><span class="highlight">box.example.com</span>
</code></pre>
<p>If the output does not match the name as it appears on your IndiaReads dashboard, correct it by typing:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo echo "<span class="highlight">box.example.com</span>" > /etc/hostname
</li></ul></code></pre>
<h2 id="step-2-—-modify-etc-hosts-file">Step 2 — Modify /etc/hosts File</h2>

<p>The <code>/etc/hosts</code> file needs to be modified to associate the hostname with the server's IP address. To edit it, open it with nano or your favorite editor using:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo nano /etc/hosts
</li></ul></code></pre>
<p>Modify the IPv4 addresses, so that they read:</p>
<div class="code-label " title="/etc/hosts">/etc/hosts</div><pre class="code-pre "><code langs="">127.0.0.1 <span class="highlight">localhost.localdomain localhost</span>
<span class="highlight">your_server_ip box.example.com box</span>
</code></pre>
<p>You can copy the <code><span class="highlight">localhost.localdomain localhost</span></code> line exactly. Use your own IP and domain on the second line.</p>

<p>Save and close the file.</p>

<h2 id="step-3-—-create-glue-records">Step 3 — Create Glue Records</h2>

<p>While it's possible to have an external DNS service, like that provided by your domain registrar, handle all DNS resolutions for the server, it's strongly recommended to delegate DNS responsibilities to the Mail-in-a-Box server.</p>

<p>That means you'll need to set up <em>glue records</em> when using Mail-in-a-Box. Using glue records makes it easier to securely and correctly set up the server for email. When using this method, it is very important that <em>all</em> DNS responsibilities be delegated to the Mail-in-a-Box server, even if there's an active website using the target domain.</p>

<p><span class="warning">If you do have an active website at your domain, make sure to set up the appropriate additional DNS records on your Mail-in-a-Box server. Otherwise, your domain won't resolve to your website. You can copy your existing DNS records to make sure everything works the same.<br /></span></p>

<p>Setting up glue records (also called <em>private nameservers</em>, <em>vanity nameservers</em>, and <em>child nameservers</em>) has to be accomplished at your domain registrar.</p>

<p>To set up a glue record, the following tasks have to be completed:</p>

<ol>
<li>Set the glue records themselves. This involves creating custom nameserver addresses that associate the server's fully-qualified hostname, plus the <strong>ns1</strong> and <strong>ns2</strong> prefixes, with its IP address. These should be as follows:</li>
</ol>

<ul>
<li><strong>ns1.box.<span class="highlight">example.com</span> <span class="highlight">your<em>server</em>ip</span></strong></li>
<li><strong>ns2.box.<span class="highlight">example.com</span> <span class="highlight">your<em>server</em>ip</span></strong></li>
</ul>

<ol>
<li>Transfer DNS responsibilities to the Mail-in-a-Box server.</li>
</ol>

<ul>
<li><strong><span class="highlight">example.com</span> NS ns1.box.<span class="highlight">example.com</span></strong></li>
<li><strong><span class="highlight">example.com</span> NS ns2.box.<span class="highlight">example.com</span></strong></li>
</ul>

<span class="note"><p>
<strong>Note:</strong> Both tasks must be completed correctly. Otherwise, the server will not be able to function as a mail server. (Alternately, you can set up all the appropriate MX, SPF, DKIM, etc., records on a different nameserver.)</p>

<p>The exact steps involved in this process vary by domain registrar. If the steps given in this article do not match yours, <strong>contact your domain registrar's tech support team</strong> for assistance.<br /></p></span>

<p><strong>Example: Namecheap</strong></p>

<p>To start, log into your domain registrar's account. How your domain registrar's account dashboard looks depends on the domain registrar you're using. The example uses Namecheap, so the steps and images used in this tutorial are exactly as you'll find them if you have a Namecheap account. If you're using a different registrar, call their tech support or go through their knowledgebase to learn how to create a glue record.</p>

<p>After logging in, find a list of the domains that you manage and click on the target domain; that is, the one you're about to use to set up the mail server.</p>

<p>Look for a menu item that allows you to modify its nameserver address information. On the Namecheap dashboard, that menu item is called <strong>Nameserver Registration</strong> under the <strong>Advanced Options</strong> menu category. You should get an interface that looks like the following:</p>

<p><img src="http://i.imgur.com/HGGLt7q.png" alt="Modifying the Nameservers" /></p>

<p>We're going to set up two glue records for the server:</p>

<ul>
<li><strong><span class="highlight">ns1.box.example.com</span></strong></li>
<li><strong><span class="highlight">ns2.box.example.com</span></strong></li>
</ul>

<p>Since only one custom field is provided, they'll have to be configured in sequence. As shown in the image below, type <strong><span class="highlight">ns1.box</span></strong> where the number <strong>1</strong> appears, then type the IP address of the Mail-in-a-Box server in the IP Address field (indicated by the number <strong>2</strong>). Finally, click the <strong>Add Nameservers</strong> button to add the record (number <strong>3</strong>).</p>

<p>Repeat for the other record, making sure to use <strong><span class="highlight">ns2.box</span></strong> along with the same domain name and IP address.</p>

<p>After both records have been created, look for another menu entry that says <strong>Transfer DNS to Webhost</strong>. You should get a window that looks just like the one shown in the image below. Select the custom DNS option, then type in the first two fields:</p>

<ul>
<li><strong><span class="highlight">ns1.box.example.com</span></strong></li>
<li><strong><span class="highlight">ns2.box.example.com</span></strong></li>
</ul>

<p><img src="http://i.imgur.com/LmXg3ZW.png" alt="Custom DNS" /></p>

<p>Click to apply the changes.</p>

<p><span class="note"><strong>Note:</strong> The custom DNS servers you type here should be the same as the ones you just specified for the Nameserver Registration.<br /></span></p>

<p>Changes to DNS take some time to propagate. It could take up to 24 hours, but it took only about 15 minutes for the changes made to the test domain to propagate.</p>

<p>You can verify that the DNS changes have been propagated by visiting <a href="https://www.whatsmydns.net">whatsmydns.net</a>. Search for the <strong>A</strong> and <strong>MX</strong> records of the target domain. If they match what you set in this step, then you may proceed to Step 4. Otherwise go through this step again or contact your registrar for assistance.</p>

<h2 id="step-4-—-install-mail-in-a-box">Step 4 — Install Mail-in-a-Box</h2>

<p>In this step, you'll run the script to install Mail-in-a-Box on your Droplet. The Mail-in-a-Box installation script installs every package required to run a full-blown email server, so all you need to do is run a simple command and follow the prompts.</p>

<p>Assuming you're still logged into the server, move to your home directory:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">cd ~
</li></ul></code></pre>
<p>Install Mail-in-a-Box:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">curl -s https://mailinabox.email/bootstrap.sh | sudo bash
</li></ul></code></pre>
<p>The script will prompt you with the introductory message in the following image. Press <code>ENTER</code>.</p>

<p><img src="http://i.imgur.com/rwyVRUO.png" alt="Mail-in-a-Box Installation" /></p>

<p>You'll now be prompted to create the first email address, which you'll later use to log in to the system. You could enter <strong><span class="highlight">contact@example.com</span></strong> or another email address at your domain. Accept or modify the suggested email address, and press <code>ENTER</code>. After that, you'll be prompted to specify and confirm a password for the email account.</p>

<p><img src="http://i.imgur.com/Y2MHRk0.png" alt="Your Email Address" /></p>

<p>After the email setup, you'll be prompted to confirm the hostname of the server. It should match the one you set in Step 1, which in this example is <strong><span class="highlight">box.example.com</span></strong>. Press <code>ENTER</code>.</p>

<p><img src="http://i.imgur.com/LGHOcar.png" alt="Hostname" /></p>

<p>Next you'll be prompted to select your country. Select it by scrolling up or down using the arrows keys. Press <code>ENTER</code> after you've made the right choice.</p>

<p><img src="http://i.imgur.com/6WxmdC3.png" alt="Country Code" /></p>

<p>At some point, you'll get this prompt:</p>
<pre class="code-pre "><code langs=""><div class="secondary-code-label " title="Output">Output</div>Okay. I'm about to set up <span class="highlight">contact@example.com</span> for you. This account will also have access to the box's control panel.
password:
</code></pre>
<p>Specify a password for the default email account, which will also be the default web interface admin account.</p>

<p>After installation has completed successfully, you should see some post-installation output that includes:</p>
<pre class="code-pre "><code langs=""><div class="secondary-code-label " title="Output">Output</div>mail user added
added alias hostmaster@box.example.com (=> administrator@box.example.com)
added alias postmaster@example.com (=> administrator@box.example.com)
added alias admin@example.com (=> administrator@box.example.com)
updated DNS: example.com
web updated

alias added
added alias admin@box.example.com (=> administrator@box.example.com)
added alias postmaster@box.example.com (=> administrator@box.example.com)


-----------------------------------------------

Your Mail-in-a-Box is running.

Please log in to the control panel for further instructions at:

https://<span class="highlight">your_server_ip</span>/admin

You will be alerted that the website has an invalid certificate. Check that
the certificate fingerprint matches:

1F:C1:EE:C7:C6:2C:7C:47:E8:EF:AC:5A:82:C1:21:67:17:8B:0C:5B

Then you can confirm the security exception and continue.
</code></pre>
<h2 id="step-5-—-log-in-to-mail-in-a-box-dashboard">Step 5 — Log In to Mail-in-a-Box Dashboard</h2>

<p>Now you'll log in to the administrative interface of Mail-in-a-Box and get to know your new email server. To access the admin interface, use the URL provided in the post-installation output. This should be:</p>

<ul>
<li><code>https://<span class="highlight">your_server_ip</span>/admin#</code></li>
</ul>

<p>Because HTTPS and a self-signed certificate were used, you will get a security warning in your browser window. You'll have to create a security exception. How that's done depends on the browser you're using.</p>

<p>If you're using Firefox, for example, you will get a browser window with the familiar warning shown in the next image.</p>

<p>To accept the certificate, click the <strong>I Understand the Risks</strong> button, then on the <strong>Add Exception</strong> button.</p>

<p><img src="http://i.imgur.com/oSERTMV.png" alt="The connection is untrusted in Firefox" /></p>

<p>On the next screen, you may verify that the certificate fingerprint matches the one in the post-installation output, then click the <strong>Confirm Security Exception</strong> button.</p>

<p><img src="http://i.imgur.com/jvRbbqX.png" alt="Add Security Exception in Firefox" /></p>

<p>After the exception has been created, log in using the username and password of the email account created during installation. Note that the username is the complete email address, like <code>contact@<span class="highlight">example.com</span></code>.</p>

<p>When you log in, a system status check is initiated. Mail-in-a-Box will check that all aspects of the server, including the glue records, have been configured correctly. If true, you should see a sea of green (and some yellowish green) text, except for the part pertaining to SSL certificates, which will be in red. You might also see a message about a reboot, which you can take care of.</p>

<p><span class="note"><strong>Note:</strong> If there are outputs in red about incorrect DNS MX records for the configured domain, then Step 3 was not completed correctly. Revisit that step or contact your registrar's tech support team for assistance.<br /></span></p>

<p>If the only red texts you see are because of SSL certificates, congratulations! You have now successfully set up your own mail server using Mail-in-a-Box.</p>

<p>If you want to revisit this section (for example, after waiting for DNS to propagate), it's under <strong>System > Status Checks</strong>.</p>

<h2 id="step-6-—-access-webmail-amp-send-test-email">Step 6 — Access Webmail & Send Test Email</h2>

<p>To access the webmail interface, click on <strong>Mail > Instructions</strong> from the top navigation bar, and access the URL provided on that page. It should be something like this:</p>

<ul>
<li><code>https://<span class="highlight">box.example.com</span>/mail</code></li>
</ul>

<p>Log in with the email address (include the <strong>@example.com</strong> part) and password that you set up earlier.</p>

<p>Mail-in-a-box uses <a href="http://trac.roundcube.net/wiki">Roundcube</a> as its webmail app. Try sending a test email to an external email address. Then, reply or send a new message to the address managed by your Mail-in-a-Box server.</p>

<p>The outgoing email should be received almost immediately, but because graylisting is in effect on the Mail-in-a-Box server, it will take about 15 minutes before incoming email shows up.</p>

<p>This won't work if DNS is not set up correctly.</p>

<p>If you can both send and receive test messages, you are now running your own email server. Congratulations!</p>

<h2 id="optional-step-7-—-install-ssl-certificate">(Optional) Step 7 — Install SSL Certificate</h2>

<p>Mail-in-a-box generates its own self-signed certificate by default. If you want to use this server in a production environment, we highly recommend installing an official SSL certificate.</p>

<p>First, <a href="https://indiareads/community/tutorials/how-to-install-an-ssl-certificate-from-a-commercial-certificate-authority">purchase your certificate</a>. Or, to learn how to create a free signed SSL certificate, refer to the <a href="https://indiareads/community/tutorials/how-to-set-up-apache-with-a-free-signed-ssl-certificate-on-a-vps">How To Set Up Apache with a Free Signed SSL Certificate on a VPS</a> tutorial.</p>

<p>Then, from the Mail-in-a-Box admin dashboard, select <strong>System > SSL Certificates</strong> from the top navigation menu.</p>

<p>From there, use the <strong>Install Certificate</strong> button next to the appropriate domain or subdomain. Copy and paste your certificate and any chain certificates into the provided text fields. Finally click the <strong>Install</strong> button.</p>

<p>Now you and your users should be able to acces webmail and the admin panel without browser warnings.</p>

<h2 id="conclusion">Conclusion</h2>

<p>It's easy to keep adding domains and additional email addresses to your Mail-in-a-Box server. To add a new address at a new or existing domain, just add another email account from <strong>Mail > Users</strong> in the admin dashboard. If the email address is at a new domain, Mail-in-a-box will automatically add appropriate new settings for it.</p>

<p>If you're adding a new domain, make sure you set the domain's nameservers to <strong><span class="highlight">ns1.box.example.com</span></strong> and <strong><span class="highlight">ns2.box.example.com</span></strong> (the same ones we set up earlier for the first domain) at your domain registrar. Your Droplet will handle all of the DNS for the new domain.</p>

<p>To see the current DNS settings, visit <strong>System > External DNS</strong>. To add your own entries, visit <strong>System > Custom DNS</strong>.</p>

<p>Mail-in-a-Box also provides functionality beyond the scope of this article. It can serve as a hosted contact and calendar manager courtesy of ownCloud. It can also be used to host static websites.</p>

<p>Further information about Mail-in-a-Box is available at the <a href="https://mailinabox.email/">project's home page</a>.</p>

    