<link rel='stylesheet' href='digital_ocean.css'><br><img src=https://community-cdn-digitalocean-com.global.ssl.fastly.net/assets/tutorials/images/large/Postfix_SMTP_server_tw_Kasia.png?1463422661/> <br> 
      <h3 id="introduction">Introduction</h3>

<p>Postfix is a <em>mail transfer agent</em> (MTA), an application used to send and receive email. In this tutorial, we will install and configure Postfix so that it can be used to send emails by local applications only — that is, those installed on the same server that Postfix is installed on.</p>

<p>Why would you want to do that?</p>

<p>If you're already using a third-party email provider for sending and receiving emails, you do not need to run your own mail server. However, if you manage a cloud server on which you have installed applications that need to send email notifications, running a local, send-only SMTP server is a good alternative to using a 3rd party email service provider or running a full-blown SMTP server.</p>

<p>In this tutorial, you'll learn how to install and configure Postfix as a send-only SMTP server.</p>

<h2 id="prerequisites">Prerequisites</h2>

<p>To follow this tutorial, you will need:</p>

<ul>
<li><p>One Ubuntu 16.04 Droplet set up with the <a href="https://indiareads/community/tutorials/initial-server-setup-with-ubuntu-16-04">Ubuntu 16.04 initial setup guide</a>, including creating a sudo non-root user</p></li>
<li><p>A valid domain name, like <code>example.com</code>, pointing to your server. You can set that up by following <a href="https://indiareads/community/tutorials/how-to-set-up-a-host-name-with-digitalocean">this host name tutorial</a></p></li>
</ul>

<p>Note that your server's hostname should match this domain or subdomain. You can verify the server's hostname by typing <code>hostname</code> at the command prompt. The output should match the name you gave the Droplet when it was being created.</p>

<h2 id="step-1-—-installing-postfix">Step 1 — Installing Postfix</h2>

<p>In this step, you'll learn how to install Postfix. The most efficient way to install Postfix and other programs needed for testing email is to install the <code>mailutils</code> package. </p>

<p>First, update the package database:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo apt-get update
</li></ul></code></pre>
<p>Finally, install Postfix. Installing <code>mailtuils</code> will install Postfix as well as a few other programs needed for Postfix to function.</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo apt install mailutils
</li></ul></code></pre>
<p>Near the end of the installation process, you will be presented with a window that looks exactly like the one in the image below. The default option is <strong>Internet Site</strong>. That's the recommended option for this tutorial, so press <code>TAB</code>, then <code>ENTER</code>.</p>

<p><img src="https://assets.digitalocean.com/articles/postfix-16.04/zJuFrgI.png?1" alt="Select Internet Site from the menu, then press TAB to select <Ok>, then ENTER" /></p>

<p>After that, you'll get another window just like the one in the next image. The <strong>System mail name</strong> should be the same as the name you assigned to the server when you were creating it. If it shows a subdomain like <code>subdomain.example.com</code>, change it to just <code>example.com</code>. When you've finished, press <code>TAB</code>, then <code>ENTER</code>.</p>

<p><img src="https://assets.digitalocean.com/articles/postfix-16.04/sVEi9SW.png?1" alt="Enter your domain name, then press TAB to select <Ok>, ENTER" /></p>

<p>After installation has completed successfully, proceed to step two.</p>

<h2 id="step-2-—-configuring-postfix">Step 2 — Configuring Postfix</h2>

<p>In this step, you'll read how to configure Postfix to process requests to send emails only from the server on which it is running, that is, from <strong>localhost</strong>.</p>

<p>For that to happen, Postfix needs to be configured to listen only on the <em>loopback interface</em>, the virtual network interface that the server uses to communicate internally. To make the change, open the main Postfix configuration file using <code>nano</code> or your favorite text editor.</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo nano /etc/postfix/main.cf
</li></ul></code></pre>
<p>With the file open, scroll down until you see the following section.</p>
<div class="code-label " title="/etc/postfix/main.cf">/etc/postfix/main.cf</div><pre class="code-pre "><code langs="">. . .
mailbox_size_limit = 0
recipient_delimiter = +
inet_interfaces = all
. . .
</code></pre>
<p>Change the line that reads <code>inet_interfaces = all</code> to <code>inet_interfaces = loopback-only</code>.</p>
<div class="code-label " title="/etc/postfix/main.cf">/etc/postfix/main.cf</div><pre class="code-pre "><code langs="">. . .
mailbox_size_limit = 0
recipient_delimiter = +
inet_interfaces = <span class="highlight">loopback-only</span>
. . .
</code></pre>
<p>Another directive you'll need to modify is <code>mydestination</code>, which is used to specify the list of domains that are delivered via the <code>local_transport</code> mail delivery transport. By default, the values are similar to these:</p>
<pre class="code-pre "><code langs=""><div class="secondary-code-label " title="/etc/postfix/main.cf">/etc/postfix/main.cf</div>. . .
mydestination = $myhostname, <span class="highlight">example.com</span>, localhost.com, , localhost
. . .
</code></pre>
<p>The <a href="http://www.postfix.org/postconf.5.html#mydestination">recommended defaults</a> for that scenario are given in the code block below, so modify yours to match:</p>
<pre class="code-pre "><code langs=""><div class="secondary-code-label " title="/etc/postfix/main.cf">/etc/postfix/main.cf</div>. . .
mydestination = $myhostname, <span class="highlight">localhost.$mydomain, $mydomain</span>
. . .

</code></pre>
<p>Save and close the file.</p>

<p><span class="note">If you're hosting multiple domains on a single server, the other domains can also be passed to Postfix using the <code>mydestination</code> directive. However, to configure Postfix in a manner that scales and that does not present issues for such a setup involves additional configurations that are beyond the scope of this article.<br /></span></p>

<p>Finally, restart Postfix.</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo systemctl restart postfix
</li></ul></code></pre>
<h2 id="step-3-—-testing-the-smtp-server">Step 3 — Testing the SMTP Server</h2>

<p>In this step, you'll test whether Postfix can send emails to an external email account using the <code>mail</code> command, which is part of the <code>mailutils</code> package that was installed in Step 1.</p>

<p>To send a test email, type:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">echo "This is the body of the email" | mail -s "This is the subject line" <span class="highlight">your_email_address</span>
</li></ul></code></pre>
<p>In performing your own test(s), you may use the body and subject line text as-is, or change them to your liking. However, in place of <code><span class="highlight">your_email_address</span></code>, use a valid email address. The domain part can be <code>gmail.com</code>, <code>fastmail.com</code>, <code>yahoo.com</code>, or any other email service provider that you use.</p>

<p>Now check the email address where you sent the test message. You should see the message in your inbox. If not, check your spam folder.</p>

<p>Note that with this configuration, the address in the <strong>From</strong> field for the test emails you send will be <code><span class="highlight">sammy</span>@<span class="highlight">example.com</span></code>, where <strong>sammy</strong> is your Linux username and the domain part is the server's hostname. If you change your username, the <strong>From</strong> address will also change.</p>

<h2 id="step-4-—-forwarding-system-mail">Step 4 — Forwarding System Mail</h2>

<p>The last thing we want to set up is forwarding, so you'll get emails sent to <strong>root</strong> on the system at your personal, external email address.</p>

<p>To configure Postfix so that system-generated emails will be sent to your email address, you need to edit the <code>/etc/aliases</code> file.</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo nano /etc/aliases
</li></ul></code></pre>
<p>The full contents of the file on a default installation of Ubuntu 16.04 are as follows:</p>
<div class="code-label " title="/etc/aliases">/etc/aliases</div><pre class="code-pre "><code langs=""># See man 5 aliases for format
postmaster:    root
</code></pre>
<p>With that setting, system generated emails are sent to the root user. What you want to do is edit it so that those emails are rerouted to your email address. To accomplish that, edit the file so that it reads:</p>
<pre class="code-pre "><code langs=""><div class="secondary-code-label " title="/etc/aliases">/etc/aliases</div># See man 5 aliases for format
postmaster:    root
root:          <span class="highlight">your_email_address</span>
</code></pre>
<p>Replace <code><span class="highlight">your_email_address</span></code> with your personal email address. When finished, save and close the file. For the change to take effect, run the following command:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo newaliases
</li></ul></code></pre>
<p>You may now test that it works by sending an email to the root account using:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">echo "This is the body of the email" | mail -s "This is the subject line" root
</li></ul></code></pre>
<p>You should receive the email at your email address. If not, check your spam folder.</p>

<h2 id="conclusion">Conclusion</h2>

<p>That's all it takes to set up a send-only email server using Postfix. You may want to take some additional steps to protect your domain from spammers.</p>

<p>If your use case is to receive notifications from your server at a single address, emails being marked as spam is a major issue because you can whitelist them. However, if your use case is to send emails to potential site users (such as confirmation emails for a message board sign-up), you should definitely set up SPF records and DKIM so your server's emails are more likely to be seen as legitimate.</p>

<ul>
<li><p><a href="https://indiareads/community/tutorials/how-to-use-an-spf-record-to-prevent-spoofing-improve-e-mail-reliability">How To use an SPF Record to Prevent Spoofing & Improve E-mail Reliability</a></p></li>
<li><p><a href="https://indiareads/community/tutorials/how-to-install-and-configure-dkim-with-postfix-on-debian-wheezy">How To Install and Configure DKIM with Postfix on Debian Wheezy</a> Though that article was written for Debian Wheezy, the same steps apply for Ubuntu 16.04.</p></li>
</ul>

<p>If configured correctly, this makes it difficult to send spam with an address that appears to originate from your domain. Doing these additional configuration steps will also make it more likely for common mail providers to see emails from your server as legitimate.</p>

    