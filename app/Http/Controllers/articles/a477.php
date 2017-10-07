<link rel='stylesheet' href='digital_ocean.css'><br> <br> 
      <h3 id="introduction">Introduction</h3>

<p>One of the most common needs when setting up a new web server is sending email. The safest and easiest way to do this is to connect your server to a mailing service such as SendGrid or Amazon SES. Using an external service will help you avoid pitfalls like your server IP getting blacklisted by anti-spam services.</p>

<p>In this tutorial we'll go over how to connect FreeBSD's built-in Sendmail service to SendGrid to send emails from your server. You can also adapt the settings for a different external mail service without much trouble.</p>

<p>If you're new to FreeBSD, some of what we do may look a little scary, but you'll soon be comfortable rolling up your sleeves to do a little recompiling of system tools like the FreeBSD pros.</p>

<h2 id="goals">Goals</h2>

<p>In this tutorial, we will:</p>

<ul>
<li>Recompile Sendmail with SASL support so the server can authenticate with an external service</li>
<li>Configure the Sendmail mail server with the appropriate settings</li>
<li>Test outbound email to make sure mail is going out from your server</li>
</ul>

<h2 id="prerequisites">Prerequisites</h2>

<p>Before you begin this guide you'll need the following:</p>

<ul>
<li>A FreeBSD 10.1 Droplet</li>
<li>Access to your <strong>root</strong> account or an account with sudo privileges following this <a href="https://indiareads/community/tutorials/how-to-add-and-remove-users-on-freebsd">tutorial</a></li>
<li>Working knowledge of how to edit text files from the command line</li>
<li>You should install your favorite text editor, such as <code>nano</code> or <code>vim</code></li>
<li>A free <a href="https://sendgrid.com/user/signup">SendGrid</a> account for testing purposes, or another mail provider that gives you SMTP details for the service. You will need these details for your external mail provider:

<ul>
<li>SMTP hostname</li>
<li>username</li>
<li>password </li>
</ul></li>
<li>Your server's hostname, which you can find by running <code>hostname</code></li>
</ul>

<p>This tutorial is most easily followed as <strong>root</strong>:</p>
<pre class="code-pre "><code langs="">sudo su
</code></pre>
<h2 id="step-1-—-set-up-package-management">Step 1 — Set Up Package Management</h2>

<p>First, we need to recompile Sendmail so it can authenticate with an external mail service - in this case, SendGrid.</p>

<p>All of the steps are included here, but if you like, you can follow along with the <a href="https://www.freebsd.org/doc/handbook/SMTP-Auth.html">official FreeBSD handbook</a>.</p>

<p>Some software will be compiled from FreeBSD's <a href="https://www.freebsd.org/ports/">Ports Collection</a>, so we need to make sure that is up to date first.</p>
<pre class="code-pre "><code langs="">portsnap fetch && portsnap update
</code></pre>
<p>The Portmaster utility will let us easily compile software from the Ports tree, so let's get that installed.</p>
<pre class="code-pre "><code langs="">pkg install portmaster
</code></pre>
<p>Run the following command to make sure the system knows to install newly compiled packages in the latest package format for FreeBSD.</p>
<pre class="code-pre "><code langs="">echo 'WITH_PKGNG=yes' >> /etc/make.conf
</code></pre>
<h2 id="step-2-—-install-and-configure-the-sasl-package">Step 2 — Install and Configure the SASL Package</h2>

<p>Using our newly installed Portmaster utility, compile and install the <code>cyrus-sasl2</code> package with the following command. This is used for authentication with the external mail service.</p>
<pre class="code-pre "><code langs="">portmaster security/cyrus-sasl2
</code></pre>
<p>When prompted, ensure <strong>LOGIN</strong> is checked, which should be by default. Choose <strong>OK</strong> and press <code>ENTER</code> twice to choose all the defaults. When prompted, answer <code>y</code> to upgrade and install your packages. You should expect a large amount of output, ending with:</p>
<pre class="code-pre "><code langs=""><div class="secondary-code-label " title="Output">Output</div>===>>> Done displaying pkg-message files

===>>> The following actions were performed:
    Upgrade of pkg-1.4.12 to pkg-1.5.0
    Upgrade of perl5-5.18.4_11 to perl5-5.18.4_13
    Installation of security/cyrus-sasl2 (cyrus-sasl-2.1.26_9)
</code></pre>
<p>Edit the file (creating it if it does not already exist) <code>/usr/local/lib/sasl2/Sendmail.conf</code> and add the following to it:</p>
<pre class="code-pre "><code langs="">vim /usr/local/lib/sasl2/Sendmail.conf
</code></pre><div class="code-label " title="/usr/local/lib/sasl2/Sendmail.conf">/usr/local/lib/sasl2/Sendmail.conf</div><pre class="code-pre "><code langs="">pwcheck_method: saslauthd
</code></pre>
<p>Next, install the <code>saslauthd</code> service for SASL authentication. When prompted, accept the defaults and choose <strong>OK</strong>.</p>
<pre class="code-pre "><code langs="">portmaster security/cyrus-sasl2-saslauthd
</code></pre>
<p>Edit the system configuration file <code>/etc/rc.conf</code> and add the following configuration parameters at the end of the file. Replace <code><span class="highlight">your_hostname</span></code> with your server's hostname.</p>
<pre class="code-pre "><code langs="">vim /etc/rc.conf
</code></pre><div class="code-label " title="/etc/rc.conf">/etc/rc.conf</div><pre class="code-pre "><code langs="">hostname = "<span class="highlight">your_hostname</span>"
sendmail_enable="YES"
saslauthd_enable="YES"
</code></pre>
<p>Now start the <code>saslauthd</code> service.</p>
<pre class="code-pre "><code langs="">service saslauthd start
</code></pre>
<p>You should see this output:</p>
<pre class="code-pre "><code langs=""><div class="secondary-code-label " title="Output">Output</div>usage: hostname [-fs] [name-of-host]
usage: hostname [-fs] [name-of-host]
Starting saslauthd.
</code></pre>
<p>Edit the <code>/etc/make.conf</code> file, adding the following parameters so the system knows which SASL Sendmail options to use.</p>
<pre class="code-pre "><code langs="">vim /etc/make.conf
</code></pre><div class="code-label " title="/etc/make.conf">/etc/make.conf</div><pre class="code-pre "><code langs="">SENDMAIL_CFLAGS=-I/usr/local/include/sasl -DSASL
SENDMAIL_LDFLAGS=-L/usr/local/lib
SENDMAIL_LDADD=-lsasl2
</code></pre>
<h2 id="step-3-—-recompile-sendmail-with-sasl-support">Step 3 — Recompile Sendmail with SASL Support</h2>

<p>In this section we'll recompile Sendmail to use SASL authentication.</p>

<p>Now we need to sync the latest source code for FreeBSD 10.1.</p>

<p>First, we'll install Subversion so we can easily get the source code we need.</p>
<pre class="code-pre "><code langs="">pkg install subversion
</code></pre>
<p>Now we can check out the latest code for recompiling, directly from the FreeBSD project website, to update our sources in <code>/usr/src</code>.</p>
<pre class="code-pre "><code langs="">svn co http://svn.freebsd.org/base/releng/10.1/ /usr/src
</code></pre>
<p>The next commands you need to run in succession, one group at a time. What we are doing here is telling the system to recompile (or rebuild) the built-in Sendmail packages with our new security and login requirements, and then reinstall Sendmail.</p>
<pre class="code-pre "><code langs="">cd /usr/src/lib/libsmutil
make cleandir && make obj && make
</code></pre><pre class="code-pre "><code langs="">cd /usr/src/lib/libsm
make cleandir && make obj && make
</code></pre><pre class="code-pre "><code langs="">cd /usr/src/usr.sbin/sendmail/
make cleandir && make obj && make && make install
</code></pre>
<h2 id="step-4-—-configure-sendmail">Step 4 — Configure Sendmail</h2>

<p>You've made it this far, and we're done recompiling things. Let's keep going!</p>

<p>For this next step we'll walk through a basic Sendmail configuration that will tell Sendmail to route all outbound mail through our selected external smart hosting service.</p>

<p>First we're going to be safe and create a backup of the <code>/etc/mail</code> directory.</p>
<pre class="code-pre "><code langs="">cp -a /etc/mail /etc/mail.bak
</code></pre>
<p>Enter the mail configuration directory.</p>
<pre class="code-pre "><code langs="">cd /etc/mail
</code></pre>
<p>Run the following command to generate a basic mail configuration.</p>
<pre class="code-pre "><code langs="">make
</code></pre>
<p>Create and edit the <code>relay-domains</code> file, adding the following parameters. Replace <code><span class="highlight">your_server.example.com</span></code> with your FQDN, and <code><span class="highlight">example.com</span></code> with your domain name.</p>
<pre class="code-pre "><code langs="">vim /etc/mail/relay-domains
</code></pre><div class="code-label " title="/etc/mail/relay-domains">/etc/mail/relay-domains</div><pre class="code-pre "><code langs=""><span class="highlight">your_server.example.com</span>
<span class="highlight">example.com</span>
</code></pre>
<p>Create and edit the <code>local-host-names</code> file, adding the following parameters. Replace the variables with your local hostnames.</p>
<pre class="code-pre "><code langs="">vim /etc/mail/local-host-names
</code></pre><div class="code-label " title="/etc/mail/local-host-names">/etc/mail/local-host-names</div><pre class="code-pre "><code langs=""><span class="highlight">your_server</span>
<span class="highlight">your_server.example.com</span>
</code></pre>
<p>Create and edit the <code>access</code> file, adding the following parameters. (Note you'll need to change the <code>smtp.sendgrid.net</code> address if you're using a provider other than SendGrid.)</p>
<pre class="code-pre "><code langs="">vim /etc/mail/access
</code></pre><div class="code-label " title="/etc/mail/access">/etc/mail/access</div><pre class="code-pre "><code langs="">smtp.sendgrid.net      OK
GreetPause:localhost    0
</code></pre>
<p>Create and edit the <code>authinfo</code> file, adding the following parameters. Replace <code><span class="highlight">smtp_username</span></code> and <code><span class="highlight">smtp_password</span></code> with your SendGrid account name and password. If you elected to use a different external mail provider, you'll also need to change the <code>smtp.sendgrid.net</code> value on both lines to the server address for your provider.</p>
<pre class="code-pre "><code langs="">vim /etc/mail/authinfo
</code></pre><div class="code-label " title="/etc/mail/authinfo">/etc/mail/authinfo</div><pre class="code-pre "><code langs="">AuthInfo:smtp.sendgrid.net "U:root" "I:<span class="highlight">smtp_username</span>" "P:<span class="highlight">smtp_password</span>" "M:LOGIN"
AuthInfo:smtp.sendgrid.net:587 "U:root" "I:<span class="highlight">smtp_username</span>" "P:<span class="highlight">smtp_password</span>" "M:LOGIN"
</code></pre>
<p>The <code>access</code> and <code>authinfo</code> files are really going to be simple databases from which Sendmail reads configuration parameters. This may sound confusing, especially if you're new to FreeBSD and Sendmail, but you just need to run these two painless commands from the <code>/etc/mail/</code> to generate the databases.</p>
<pre class="code-pre "><code langs="">makemap hash access < access
makemap hash authinfo < authinfo
</code></pre>
<p>Now we'll edit the base configuration we generated a few commands aback. Edit the <code><span class="highlight">your_server</span>.mc</code> file. (You can <code>ls</code> the <code>/etc/mail/</code> directory if you're not sure of the file name.)</p>
<pre class="code-pre "><code langs="">vim /etc/mail/<span class="highlight">your_server.example.com</span>.mc
</code></pre>
<p>Insert the following configuration lines between the <code>dnl define(</code>SMART_HOST', <code>your.isp.mail.server')</code> block and the <code>dnl Uncomment the first line to change the location of the default</code> block as shown below.  </p>

<p>You'll need to change the <code>smtp.sendgrid.net</code> address to your provider's server address if you're not using a SendGrid account like in the example. You'll also need to update the two instances of <code><span class="highlight">example.com</span></code> to the domain you'd like the mail to be <strong>from</strong>. (Note that you may need to set appropriate TXT, DKIM, PTR etc. records to avoid reports of spoofing.)</p>
<div class="code-label " title="/etc/mail/<span class=" highlight>your_server.example.com.mc'>/etc/mail/<span class="highlight">your_server.example.com</span>.mc</div><pre class="code-pre "><code langs="">dnl define(`SMART_HOST', `your.isp.mail.server')

dnl SET OUTBOUND DOMAIN
MASQUERADE_AS(`<span class="highlight">example.com</span>')
MASQUERADE_DOMAIN(<span class="highlight">example.com</span>)
FEATURE(masquerade_envelope)
FEATURE(masquerade_entire_domain)

dnl SMART HOST CONFIG
define(`SMART_HOST', `smtp.sendgrid.net')dnl
define(`RELAY_MAILER_ARGS', `TCP $h 587')dnl
define(`confAUTH_MECHANISMS', `GSSAPI DIGEST-MD5 CRAM-MD5 LOGIN PLAIN')dnl
FEATURE(`authinfo',`hash /etc/mail/authinfo.db')dnl
TRUST_AUTH_MECH(`GSSAPI DIGEST-MD5 CRAM-MD5 LOGIN PLAIN')dnl

dnl Uncomment the first line to change the location of the default
</code></pre>
<p>Before we apply the changes, let's walk through a bit of the above configuration. The first block is telling Sendmail that we'd like to make sure it appears that our outbound mail is coming from our domain <code><span class="highlight">example.com</span></code>.</p>

<p>The second block is defining where we want to <em>smart host</em> our mail to, including the port, authentication methods, and our authentication info that we set up in a previous step. Notice that we're referencing the <code>/etc/mail/authinfo.db</code> file.</p>

<p>Now let's apply the changes we've made. Make sure you're still in the <code>/etc/mail/</code> directory. Make sure Sendmail is started:</p>
<pre class="code-pre "><code langs="">service sendmail start
</code></pre>
<p>Updating our configuration:</p>
<pre class="code-pre "><code langs="">make
make install restart
</code></pre>
<p>Restart Sendmail:</p>
<pre class="code-pre "><code langs="">service sendmail restart
</code></pre>
<p>Our Sendmail configuration is done. The next step is to send a test email.</p>

<h2 id="step-5-—-send-a-test-email">Step 5 — Send a Test Email</h2>

<p>Now that we have gone through all the steps for a proper setup, let's make sure that everything is working.</p>

<p>Use the <code>mailx</code> command to send a test message to a real email account you use every day.</p>
<pre class="code-pre "><code langs="">mailx <span class="highlight">your_real_email_address@example.com</span>
</code></pre>
<p>When prompted, enter <code>test</code> or whatever you want for a subject, and then press <code>ENTER</code>.</p>
<pre class="code-pre "><code langs="">Subject: <span class="highlight">test</span>
</code></pre>
<p>You'll then be presented with just a cursor and the ability to write the body of your test email. Just write the single word <code>test</code> again and press <code>ENTER</code> again.</p>
<pre class="code-pre "><code langs="">test
</code></pre>
<p>You need to tell <code>mailx</code> that you're done writing your message; to do that we have to end the message with a single <code>.</code> and press <code>ENTER</code> one final time.  You'll immediately see <code>EOT</code> as confirmation of that.</p>
<pre class="code-pre "><code langs="">.
EOT
</code></pre>
<p>Next, run the the following command to check that the mail queue is empty and that our message has been sent.</p>
<pre class="code-pre "><code langs="">mailq
</code></pre>
<p>The output should look like this if our test message has been successfully sent, and you should see it in your inbox shortly.</p>
<pre class="code-pre "><code langs="">/var/spool/mqueue is empty
                Total requests: 0
</code></pre>
<p>Go check your email now to make sure the message arrived. It should be from <strong>freebsd@<span class="highlight">example.com</span></strong>.</p>

<p>Blindly trusting the fact that the mail queue is empty is not a valid test of success.  Even if you've already received the message, you're going to want to know the basics in viewing your mail logs.  Run the following command.</p>
<pre class="code-pre "><code langs="">tail -f /var/log/maillog
</code></pre>
<p>The two keys you're looking for in the log output are</p>

<ul>
<li><strong><code>Sent (<message id> Message accepted for delivery)</code></strong><br /></li>
<li><strong><code>relay=smtp.sendgrid.net. [208.43.76.147], dsn=2.0.0, stat=Sent (Delivery in progress)</code></strong></li>
</ul>

<p>Make sure you can spot these messages in the log output below.</p>
<pre class="code-pre "><code langs=""><div class="secondary-code-label " title="Mail Log">Mail Log</div>Feb 11 04:09:13 your_server sm-mta[49080]: t1B49CW0049080: from=<freebsd@your_server>, size=331, class=0, nrcpts=1, msgid=<201502110409.t1B49CZ4049079@your_server>, proto=ESMTP, daemon=Daemon0, relay=localhost [127.0.0.1]
Feb 11 04:09:13 your_server sendmail[49079]: t1B49CZ4049079: to=your_real_email_address@example.com, ctladdr=freebsd (1001/1001), delay=00:00:01, xdelay=00:00:01, mailer=relay, pri=30040, relay=[127.0.0.1] [127.0.0.1], dsn=2.0.0, stat=Sent (t1B49CW0049080 Message accepted for delivery)
Feb 11 04:09:13 your_server sm-mta[49082]: STARTTLS=client, relay=smtp.sendgrid.net., version=TLSv1/SSLv3, verify=FAIL, cipher=AES128-GCM-SHA256, bits=128/128
Feb 11 04:09:13 your_server sm-mta[49082]: t1B49CW0049080: to=<your_real_email_address@example.com>, ctladdr=<freebsd@your_server> (1001/1001), delay=00:00:00, xdelay=00:00:00, mailer=relay, pri=30331, relay=smtp.sendgrid.net. [208.43.76.147], dsn=2.0.0, stat=Sent (Delivery in progress)
</code></pre>
<p>This shows that your message has been accepted and is on its way to your inbox, which may be a bit anticlimactic if you've already received it.</p>

<p>To do live testing and troubleshooting, you can have two terminal sessions open and leave the <code>tail -f /var/log/maillog</code> command running in one, while you send test messages in the other.</p>

<h2 id="conclusion">Conclusion</h2>

<p>You're now ready to start sending outbound email from your FreeBSD Droplet via SendGrid or any other mail service you like. Any web sites or web applications you deploy will now be able to take advantage of this with minimal to no configuration.</p>

<p>If you have any questions or comments, please leave them below.</p>

    