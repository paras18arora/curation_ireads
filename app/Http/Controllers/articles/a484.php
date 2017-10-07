<link rel='stylesheet' href='digital_ocean.css'><br> <br> 
      <h3 id="introduction">Introduction</h3>

<p>Postfix is a very popular open source Mail Transfer Agent (MTA) that can be used to route and deliver email on a Linux system.  It is estimated that around 25% of public mail servers on the internet run Postfix.</p>

<p>In this guide, we'll teach you how to get up and running quickly with Postfix on an Ubuntu 14.04 server.</p>

<h2 id="prerequisites">Prerequisites</h2>

<p>In order to follow this guide, you should have a Fully Qualified Domain Name pointed at your Ubuntu 14.04 server.  You can find help on <a href="https://indiareads/community/articles/how-to-set-up-a-host-name-with-digitalocean">setting up your domain name with IndiaReads</a> by clicking here.</p>

<h2 id="install-the-software">Install the Software</h2>

<p>The installation process of Postfix on Ubuntu 14.04 is easy because the software is in Ubuntu's default package repositories.</p>

<p>Since this is our first operation with <code>apt</code> in this session, we're going to update our local package index and then install the Postfix package:</p>
<pre class="code-pre "><code langs="">sudo apt-get update
sudo apt-get install postfix
</code></pre>
<p>You will be asked what type of mail configuration you want to have for your server.  For our purposes, we're going to choose "Internet Site" because the description is the best match for our server.</p>

<p>Next, you will be asked for the Fully Qualified Domain Name (FQDN) for your server.  This is your full domain name (like <code>example.com</code>).  Technically, a FQDN is required to end with a dot, but Postfix does not need this.  So we can just enter it like:</p>
<pre class="code-pre "><code langs="">example.com
</code></pre>
<p>The software will now be configured using the settings you provided.  This takes care of the installation, but we still have to configure other items that we were not prompted for during installation.</p>

<h2 id="configure-postfix">Configure Postfix</h2>

<p>We are going to need to change some basic settings in the main Postfix configuration file.</p>

<p>Begin by opening this file with root privileges in your text editor:</p>
<pre class="code-pre "><code langs="">sudo nano /etc/postfix/main.cf
</code></pre>
<p>First, we need to find the <code>myhostname</code> parameter.  During the configuration, the FQDN we selected was added to the <code>mydestination</code> parameter, but <code>myhostname</code> remained set to <code>localhost</code>.  We want to point this to our FQDN too:</p>

<pre>
myhostname = <span class="highlight">example.com</span>
</pre>

<p>If you would like to configuring mail to be forwarded to other domains or wish to deliver to addresses that don't map 1-to-1 with system accounts, we can remove the <code>alias_maps</code> parameter and replace it with <code>virtual_alias_maps</code>.  We would then need to change the location of the hash to <code>/etc/postfix/virtual</code>:</p>
<pre class="code-pre "><code langs="">virtual_alias_maps = hash:/etc/postfix/virtual
</code></pre>
<p>As we said above, the <code>mydestination</code> parameter has been modified with the FQDN you entered during installation.  This parameter holds any domains that this installation of Postfix is going to be responsible for.  It is configured for the FQDN and the localhost.</p>

<p>One important parameter to mention is the <code>mynetworks</code> parameter.  This defines the computers that are able to use this mail server.  It should be set to local only (<code>127.0.0.0/8</code> and the other representations).  Modifying this to allow other hosts to use this is a huge vulnerability that can lead to extreme cases of spam.</p>

<p>To be clear, the line should be set like this.  This should be set automatically, but double check the value in your file:</p>

<pre>
mynetworks = <span class="highlight">127.0.0.0/8 [::ffff:127.0.0.0]/104 [::1]/128</span>
</pre>

<h2 id="configure-additional-email-addresses">Configure Additional Email Addresses</h2>

<p>We can configure additional email addresses by creating aliases.  These aliases can be used to deliver mail to other user accounts on the system.</p>

<p>If you wish to utilize this functionality, make sure that you configured the <code>virtual_alias_maps</code> directive like we demonstrated above.  We will use this file to configure our address mappings.  Create the file by typing:</p>
<pre class="code-pre "><code langs="">sudo nano /etc/postfix/virtual
</code></pre>
<p>In this file, you can specify emails that you wish to create on the left-hand side, and username to deliver the mail to on the right-hand side, like this:</p>

<pre>
<span class="highlight">blah@example.com username1</span>
</pre>

<p>For our installation, we're going to create a few email addresses and route them to some user accounts.  We can also set up certain addresses to forward to multiple accounts by using a comma-separated list:</p>
<pre class="code-pre "><code langs="">blah@example.com        demouser
dinosaurs@example.com   demouser
roar@example.com        root
contact@example.com     demouser,root
</code></pre>
<p>Save and close the file when you are finished.</p>

<p>Now, we can implement our mapping by calling this command:</p>
<pre class="code-pre "><code langs="">sudo postmap /etc/postfix/virtual
</code></pre>
<p>Now, we can reload our service to read our changes:</p>
<pre class="code-pre "><code langs="">sudo service postfix restart
</code></pre>
<h2 id="test-your-configuration">Test your Configuration</h2>

<p>You can test that your server can receive and route mail correctly by sending mail from your regular email address to one of your user accounts on the server or one of the aliases you set up.</p>

<p>Once you send an email to:</p>

<pre>
<span class="highlight">demouser</span>@<span class="highlight">your_server_domain.com</span>
</pre>

<p>You should get mail delivered to a file that matches the delivery username in <code>/var/mail</code>.  For instance, we could read this message by looking at this file:</p>
<pre class="code-pre "><code langs="">nano /var/mail/demouser
</code></pre>
<p>This will contain all of the email messages, including the headers, in one big file.  If you want to consume your email in a more friendly way, you might want to install a few helper programs:</p>
<pre class="code-pre "><code langs="">sudo apt-get install mailutils
</code></pre>
<p>This will give you access to the <code>mail</code> program that you can use to check your inbox:</p>
<pre class="code-pre "><code langs="">mail
</code></pre>
<p>This will give you an interface to interact with your mail.</p>

<h2 id="conclusion">Conclusion</h2>

<p>You should now have basic email functionality configured on your server.</p>

<p>It is important to secure your server and make sure that Postfix is not configured as an open relay.  Mail servers are heavily targeted by attackers because they can send out massive amounts of spam email, so be sure to set up a firewall and implement other security measures to protect your server.  You can learn about some <a href="https://digitalocean.com/community/articles/an-introduction-to-securing-your-linux-vps">security options here</a>.</p>

<div class="author">By Justin Ellingwood</div>

    