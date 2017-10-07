<link rel='stylesheet' href='digital_ocean.css'><br> <br> 
      <h3 id="introduction">Introduction</h3>

<p>Postfix is an MTA (Mail Transfer Agent), an application used to send and receive email. In this tutorial, we will install and configure Postfix so that it can be used to send emails by local applications only – that is, those installed on the same server that Postfix is installed on.</p>

<p><strong>Why would you want to do that?</strong></p>

<p>If you're already using a third-party email provider for sending and receiving emails, you, of course, do not need to run your own mail server. However, if you manage a cloud server on which you have installed applications that need to send email notifications, running a local, send-only SMTP server is a good alternative to using a 3rd party email service provider or running a full-blown SMTP server.</p>

<p>An example of an application that sends email notifications is OSSEC, which will send email alerts to any configured email address (see <a href="https://indiareads/community/tutorials/how-to-install-and-configure-ossec-security-notifications-on-ubuntu-14-04">How To Install and Configure OSSEC Security Notifications on Ubuntu 14.04</a>). Though OSSEC or any other application of its kind can use a third-party email provider's SMTP server to send email alerts, it can also use a local (send-only) SMTP server.</p>

<p>That's what you'll learn how to do in this tutorial: how to install and configure Postfix as a send-only SMTP server.</p>

<blockquote>
<p><strong>Note:</strong> If your use case is to receive notifications from your server at a single address, emails being marked as spam is not a significant issue, since you can whitelist them.</p>

<p>If your use case is to send emails to potential site users, such as confirmation emails for message board sign-ups, you should definitely do <strong>Step 5</strong> so your server's emails are more likely to be seen as legitimate. If you're still having problems with your server's emails being marked as spam, you will need to do further troubleshooting on your own.</p>
</blockquote>

<h3 id="prerequisites">Prerequisites</h3>

<p>Please complete the following prerequisites.</p>

<ul>
<li>Ubuntu 14.04 Droplet</li>
<li>Go through the <a href="https://indiareads/community/tutorials/initial-server-setup-with-ubuntu-14-04">initial setup</a>. That means you should have a standard user account with <code>sudo</code> privileges</li>
<li>Have a valid domain name, like <strong>example.com</strong>, pointing to your Droplet</li>
<li>Your server's hostname should match this domain or subdomain. You can verify the server's hostname by typing <code>hostname</code> at the command prompt. The output should match the name you gave the Droplet when it was being created, such as <strong>example.com</strong></li>
</ul>

<p>If all the prerequisites have been met, you're now ready for the first step of this tutorial.</p>

<h2 id="step-1-—-install-postfix">Step 1 — Install Postfix</h2>

<p>In this step, you'll learn how to install Postfix. The most efficient way to install Postfix and other programs needed for testing email is to install the <code>mailutils</code> package by typing:</p>
<pre class="code-pre "><code langs="">sudo apt-get install mailutils
</code></pre>
<p>Installing mailtuils will also cause Postfix to be installed, as well as a few other programs needed for Postfix to function. After typing that command, you will be presented with output that reads something like:</p>
<pre class="code-pre "><code langs="">The following NEW packages will be installed:
guile-2.0-libs libgsasl7 libkyotocabinet16 libltdl7 liblzo2-2 libmailutils4 libmysqlclient18 libntlm0 libunistring0 mailutils mailutils-common mysql-common postfix ssl-cert

0 upgraded, 14 newly installed, 0 to remove and 3 not upgraded.
Need to get 5,481 kB of archives.
After this operation, 26.9 MB of additional disk space will be used.
Do you want to continue? [Y/n]
</code></pre>
<p>Press ENTER to install them. Near the end of the installation process, you will be presented with a window that looks exactly like the one in the image below. The default option is <strong>Internet Site</strong>. That's the recommended option for this tutorial, so press TAB, then ENTER.</p>

<p><img src="https://assets.digitalocean.com/articles/postfix_sendonly/1.png" alt="Select Internet Site from the menu, then press TAB to select <Ok>, then ENTER" /></p>

<p>After that, you'll get another window just like the one in this next image. The <strong>System mail name</strong> should be the same as the name you assigned to the Droplet when you were creating it. If it shows a subdomain like <strong>mars.example.com</strong>, change it to just <strong>example.com</strong>. When you're done, Press TAB, then ENTER.</p>

<p><img src="https://assets.digitalocean.com/articles/postfix_sendonly/2.png" alt="Enter your domain name, then press TAB to select <Ok>, ENTER" /></p>

<p>After installation has completed successfully, proceed to Step 2.</p>

<h2 id="step-2-—-configure-postfix">Step 2 — Configure Postfix</h2>

<p>In this step, you'll read how to configure Postfix to process requests to send emails only from the server on which it is running, that is, from <strong>localhost</strong>. For that to happen, Postfix needs to be configured to listen only on the <em>loopback interface</em>, the virtual network interface that the server uses to communicate internally. To make the change, open the main Postfix configuration file using the nano editor.</p>
<pre class="code-pre "><code langs="">sudo nano /etc/postfix/main.cf
</code></pre>
<p>With the file open, scroll down until you see the entries shown in this code block.</p>
<pre class="code-pre "><code langs="">mailbox_size_limit = 0
recipient_delimiter = +
inet_interfaces = all
</code></pre>
<p>Change the line that reads <code>inet_interfaces = all</code> to <code>inet_interfaces = loopback-only</code>. When you're done, that same section of the file should now read:</p>
<pre class="code-pre "><code langs="">mailbox_size_limit = 0
recipient_delimiter = +
inet_interfaces = <span class="highlight">loopback-only</span>
</code></pre>
<p>In place of <code>loopback-only</code> you may also use <code>localhost</code>, so that the modified section may also read:</p>
<pre class="code-pre "><code langs="">mailbox_size_limit = 0
recipient_delimiter = +
inet_interfaces = <span class="highlight">localhost</span>
</code></pre>
<p>When you're done editing the file, save and close it (press CTRL+X, followed by pressing Y, then ENTER). After that, restart Postfix by typing:</p>
<pre class="code-pre "><code langs="">sudo service postfix restart
</code></pre>
<h2 id="step-3-—-test-that-the-smtp-server-can-send-emails">Step 3 — Test That the SMTP Server Can Send Emails</h2>

<p>In this step, you'll read how to test whether Postfix can send emails to any external email account. You'll be using the <code>mail</code> command, which is part of the <code>mailutils</code> package that was installed in Step 1.</p>

<p>To send a test email, type:</p>
<pre class="code-pre "><code langs="">echo "This is the body of the email" | mail -s "This is the subject line" <span class="highlight">user@example.com</span>
</code></pre>
<p>In performing your own test(s), you may use the body and subject line text as-is, or change them to your liking. However, in place of <strong><span class="highlight">user@example.com</span></strong>, use a valid email address, where the domain part can be <strong>gmail.com</strong>, <strong>fastmail.com</strong>, <strong>yahoo.com</strong>, or any other email service provider that you use.</p>

<p>Now check the email address where you sent the test message.</p>

<p>You should see the message in your inbox. If not, check your spam folder.</p>

<p><strong>Note:</strong> With this configuration, the address in the <strong>From</strong> field for the test emails you send will be <strong>sammy@example.com</strong>, where <strong>sammy</strong> is your Linux username and the domain part is the server's hostname. If you change your username, the <strong>From</strong> address will also change.</p>

<h2 id="step-4-—-forward-system-mail">Step 4 — Forward System Mail</h2>

<p>The last thing we want to set up is forwarding, so that you'll get emails sent to <strong>root</strong> on the system at your personal, external email address.</p>

<p>To configure Postfix so that system-generated emails will be sent to your email address, you need to edit the <code>/etc/aliases</code> file. </p>
<pre class="code-pre "><code langs="">sudo nano /etc/aliases
</code></pre>
<p>The full content of the file on a default installation of Ubuntu 14.04 is shown in this code block:</p>
<pre class="code-pre "><code langs=""># See man 5 aliases for format
postmaster:    root
</code></pre>
<p>With that setting, system generated emails are sent to the root user. What you want to do is edit it so that those emails are rerouted to your email address. To accomplish that, edit the file so that it reads:</p>
<pre class="code-pre "><code langs=""># See man 5 aliases for format
postmaster:    root
root:          <span class="highlight">sammy@example.com</span>
</code></pre>
<p>Replace <strong>sammy@example.com</strong> with your personal email address. When done, save and close the file. For the change to take effect, run the following command:</p>
<pre class="code-pre "><code langs="">sudo newaliases
</code></pre>
<p>You may now test that it works by sending an email to the root account using:</p>
<pre class="code-pre "><code langs="">echo "This is the body of the email" | mail -s "This is the subject line" root
</code></pre>
<p>You should receive the email at your email address. If not, check your spam folder.</p>

<h3 id="optional-step-5-—-protect-your-domain-from-spammers">(Optional) Step 5 — Protect Your Domain from Spammers</h3>

<p>In this step, you'll be given links to articles to help you protect your domain from being used for spamming. This is an optional but highly recommended step, because if configured correctly, this makes it difficult to send spam with an address that appears to originate from your domain.</p>

<p>Doing these additional configuration steps will also make it more likely for common mail providers to see emails from your server as legitimate, rather than marking them as spam.</p>

<ul>
<li><a href="https://indiareads/community/tutorials/how-to-use-an-spf-record-to-prevent-spoofing-improve-e-mail-reliability">How To use an SPF Record to Prevent Spoofing & Improve E-mail Reliability</a></li>
<li><a href="https://indiareads/community/tutorials/how-to-install-and-configure-dkim-with-postfix-on-debian-wheezy">How To Install and Configure DKIM with Postfix on Debian Wheezy</a></li>
<li>Also, make sure the PTR record for your server matches the hostname being used by the mail server when it sends messages. At IndiaReads, you can change your PTR record by changing your Droplet's name in the control panel</li>
</ul>

<p>Though the second article was written for Debian Wheezy, the same steps apply for Ubuntu 14.04.</p>

    