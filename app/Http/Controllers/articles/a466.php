<link rel='stylesheet' href='digital_ocean.css'><br><img src=https://community-cdn-digitalocean-com.global.ssl.fastly.net/assets/tutorials/images/large/Postfix_Install_twitter_mostov_-.png?1466189536/> <br> 
      <h3 id="introduction">Introduction</h3>

<p>Postfix is a popular open-source Mail Transfer Agent (MTA) that can be used to route and deliver email on a Linux system.  It is estimated that around 25% of public mail servers on the internet run Postfix.</p>

<p>In this guide, we'll teach you how to get up and running quickly with Postfix on an Ubuntu 16.04 server.</p>

<h2 id="prerequisites">Prerequisites</h2>

<p>In order to follow this guide, you should have access to a non-root user with <code>sudo</code> privileges.  You can follow our <a href="https://indiareads/community/tutorials/initial-server-setup-with-ubuntu-16-04">Ubuntu 16.04 initial server setup guide</a> to create the necessary user.</p>

<p>In order to properly configure Postfix, you will need a Fully Qualified Domain Name pointed at your Ubuntu 16.04 server.  You can find help on setting up your domain name with IndiaReads by following <a href="https://indiareads/community/articles/how-to-set-up-a-host-name-with-digitalocean">this guide</a>.</p>

<p>For the purposes of this tutorial, we will assume that you are configuring a host that has the FQDN of <code>mail.example.com</code>.</p>

<h2 id="step-1-install-postfix">Step 1: Install Postfix</h2>

<p>Postfix is included in Ubuntu's default repositories, so installation is incredibly simple.</p>

<p>To begin, update your local <code>apt</code> package cache and then install the software.  We will be passing in the <code>DEBIAN_PRIORITY=low</code> environmental variable into our installation command in order to answer some additional prompts:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo apt-get update
</li><li class="line" prefix="$">sudo DEBIAN_PRIORITY=low apt-get install postfix
</li></ul></code></pre>
<p>Use the following information to fill in your prompts correctly for your environment:</p>

<ul>
<li><strong>General type of mail configuration?</strong>: For this, we will choose <strong>Internet Site</strong> since this matches our infrastructure needs.</li>
<li><strong>System mail name</strong>: This is the base domain used to construct a valid email address when only the account portion of the address is given.  For instance, the hostname of our server is <code>mail.example.com</code>, but we probably want to set the system mail name to <code>example.com</code> so that given the username <code>user1</code>, Postfix will use the address <code>user1@example.com</code>.</li>
<li><strong>Root and postmaster mail recipient</strong>: This is the Linux account that will be forwarded mail addressed to <code>root@</code> and <code>postmaster@</code>.  Use your primary account for this.  In our case, <strong>sammy</strong>.</li>
<li><strong>Other destinations to accept mail for</strong>: This defines the mail destinations that this Postfix instance will accept.  If you need to add any other domains that this server will be responsible for receiving, add those here, otherwise, the default should work fine.</li>
<li><strong>Force synchronous updates on mail queue?</strong>: Since you are likely using a journaled filesystem, accept <strong>No</strong> here.</li>
<li><strong>Local networks</strong>: This is a list of the networks that your mail server is configured to relay messages for.  The default should work for most scenarios.  If you choose to modify it, make sure to be very restrictive in regards to the network range.</li>
<li><strong>Mailbox size limit</strong>: This can be used to limit the size of messages.  Setting it to "0" disables any size restriction.</li>
<li><strong>Local address extension character</strong>: This is the character that can be used to separate the regular portion of the address from an extension (used to create dynamic aliases).</li>
<li><strong>Internet protocols to use</strong>: Choose whether to restrict the IP version that Postfix supports.  We'll pick "all" for our purposes.</li>
</ul>

<p>To be explicit, these are the settings we'll use for this guide:</p>

<ul>
<li><strong>General type of mail configuration?</strong>: Internet Site</li>
<li><strong>System mail name</strong>: example.com (not mail.example.com)</li>
<li><strong>Root and postmaster mail recipient</strong>: sammy</li>
<li><strong>Other destinations to accept mail for</strong>: $myhostname, example.com, mail.example.com, localhost.example.com, localhost</li>
<li><strong>Force synchronous updates on mail queue?</strong>: No</li>
<li><strong>Local networks</strong>: 127.0.0.0/8 [::ffff:127.0.0.0]/104 [::1]/128</li>
<li><strong>Mailbox size limit</strong>: 0</li>
<li><strong>Local address extension character</strong>: +</li>
<li><strong>Internet protocols to use</strong>: all</li>
</ul>

<p>If you need to ever return to re-adjust these settings, you can do so by typing:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo dpkg-reconfigure postfix
</li></ul></code></pre>
<p>The prompts will be pre-populated with your previous responses.</p>

<p>When you are finished, we can now do a bit more configuration to set up our system how we'd like it.</p>

<h2 id="step-2-tweak-the-postfix-configuration">Step 2: Tweak the Postfix Configuration</h2>

<p>Next, we can adjust some settings that the package did not prompt us for.</p>

<p>To begin, we can set the mailbox.  We will use the <strong>Maildir</strong> format, which separates messages into individual files that are then moved between directories based on user action.  The other option is the <strong>mbox</strong> format (which we won't cover here) which stores all messages within a single file.</p>

<p>We will set the <code>home_mailbox</code> variable to <code>Maildir/</code> which will create a directory structure under that name within the user's home directory.  The <code>postconf</code> command can be used to query or set configuration settings.  Configure <code>home_mailbox</code> by typing:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo postconf -e 'home_mailbox= Maildir/'
</li></ul></code></pre>
<p>Next, we can set the location of the <code>virtual_alias_maps</code> table.  This table maps arbitrary email accounts to Linux system accounts.  We will create this table at <code>/etc/postfix/virtual</code>.  Again, we can use the <code>postconf</code> command:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo postconf -e 'virtual_alias_maps= hash:/etc/postfix/virtual'
</li></ul></code></pre>
<h2 id="step-3-map-mail-addresses-to-linux-accounts">Step 3: Map Mail Addresses to Linux Accounts</h2>

<p>Next, we can set up the virtual maps file.  Open the file in your text editor:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo nano /etc/postfix/virtual
</li></ul></code></pre>
<p>The virtual alias map table uses a very simple format.  On the left, you can list any addresses that you wish to accept email for.  Afterwards, separated by whitespace, enter the Linux user you'd like that mail delivered to.</p>

<p>For example, if you would like to accept email at <code>contact@example.com</code> and <code>admin@example.com</code> and would like to have those emails delivered to the <code>sammy</code> Linux user, you could set up your file like this:</p>
<div class="code-label " title="/etc/postfix/virtual">/etc/postfix/virtual</div><pre class="code-pre "><code langs="">contact@example.com sammy
admin@example.com sammy
</code></pre>
<p>After you've mapped all of the addresses to the appropriate server accounts, save and close the file.</p>

<p>We can apply the mapping by typing:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo postmap /etc/postfix/virtual
</li></ul></code></pre>
<p>Restart the Postfix process to be sure that all of our changes have been applied:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo systemctl restart postfix
</li></ul></code></pre>
<h2 id="step-4-adjust-the-firewall">Step 4: Adjust the Firewall</h2>

<p>If you are running the UFW firewall, as configured in the initial server setup guide, we'll have to allow an exception for Postfix.</p>

<p>You can allow connections to the service by typing:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo ufw allow Postfix
</li></ul></code></pre>
<p>The Postfix server component is installed and ready.  Next, we will set up a client that can handle the mail that Postfix will process.</p>

<h2 id="step-5-setting-up-the-environment-to-match-the-mail-location">Step 5: Setting up the Environment to Match the Mail Location</h2>

<p>Before we install a client, we should make sure our <code>MAIL</code> environmental variable is set correctly.  The client will inspect this variable to figure out where to look for user's mail.</p>

<p>In order for the variable to be set regardless of how you access your account (through <code>ssh</code>, <code>su</code>, <code>su -</code>, <code>sudo</code>, etc.) we need to set the variable in a few different locations.  We'll add it to <code>/etc/bash.bashrc</code> and a file within <code>/etc/profile.d</code> to make sure each user has this configured.</p>

<p>To add the variable to these files, type:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">echo 'export MAIL=~/Maildir' | sudo tee -a /etc/bash.bashrc | sudo tee -a /etc/profile.d/mail.sh
</li></ul></code></pre>
<p>To read the variable into your current session, you can source the <code>/etc/profile.d/mail.sh</code> file:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">source /etc/profile.d/mail.sh
</li></ul></code></pre>
<h2 id="step-6-install-and-configure-the-mail-client">Step 6: Install and Configure the Mail Client</h2>

<p>In order to interact with the mail being delivered, we will install the <code>s-nail</code> package.  This is a variant of the BSD <code>xmail</code> client, which is feature-rich, can handle the Maildir format correctly, and is mostly backwards compatible.  The GNU version of <code>mail</code> has some frustrating limitations, such as always saving read mail to the mbox format regardless of the source format.</p>

<p>To install the <code>s-nail</code> package, type:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo apt-get install s-nail
</li></ul></code></pre>
<p>We should adjust a few settings.  Open the <code>/etc/s-nail.rc</code> file in your editor:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo nano /etc/s-nail.rc
</li></ul></code></pre>
<p>Towards the bottom of the file, add the following options:</p>
<div class="code-label " title="/etc/s-nail.rc">/etc/s-nail.rc</div><pre class="code-pre "><code langs="">. . .
set emptystart
set folder=Maildir
set record=+sent
</code></pre>
<p>This will allow the client to open even with an empty inbox.  It will also set the <code>Maildir</code> directory to the internal <code>folder</code> variable and then use this to create a <code>sent</code> mbox file within that, for storing sent mail.</p>

<p>Save and close the file when you are finished.</p>

<h2 id="step-7-initialize-the-maildir-and-test-the-client">Step 7: Initialize the Maildir and Test the Client</h2>

<p>Now, we can test the client out.</p>

<h3 id="initializing-the-directory-structure">Initializing the Directory Structure</h3>

<p>The easiest way to create the Maildir structure within our home directory is to send ourselves an email.  We can do this with the <code>mail</code> command.  Because the <code>sent</code> file will only be available once the Maildir is created, we should disable writing to that for our initial email.  We can do this by passing the <code>-Snorecord</code> option.</p>

<p>Send the email by piping a string to the <code>mail</code> command.  Adjust the command to mark your Linux user as the recipient:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">echo 'init' | mail -s 'init' -Snorecord <span class="highlight">sammy</span>
</li></ul></code></pre>
<p>You should get the following response:</p>
<pre class="code-pre "><code langs=""><div class="secondary-code-label " title="Output">Output</div>Can't canonicalize "/home/<span class="highlight">sammy</span>/Maildir"
</code></pre>
<p>This is normal and will only show during this first message.  We can check to make sure the directory was created by looking for our <code>~/Maildir</code> directory:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">ls -R ~/Maildir
</li></ul></code></pre>
<p>You should see the directory structure has been created and that a new message file is in the <code>~/Maildir/new</code> directory:</p>
<pre class="code-pre "><code langs=""><div class="secondary-code-label " title="Output">Output</div>/home/<span class="highlight">sammy</span>/Maildir/:
cur  new  tmp

/home/<span class="highlight">sammy</span>/Maildir/cur:

/home/<span class="highlight">sammy</span>/Maildir/new:
1463177269.Vfd01I40e4dM691221.mail.example.com

/home/<span class="highlight">sammy</span>/Maildir/tmp:
</code></pre>
<p>It looks like our mail has been delivered.</p>

<h3 id="managing-mail-with-the-client">Managing Mail with the Client</h3>

<p>Use the client to check your mail:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">mail
</li></ul></code></pre>
<p>You should see your new message waiting:</p>
<pre class="code-pre "><code langs=""><div class="secondary-code-label " title="Output">Output</div>s-nail version v14.8.6.  Type ? for help.
"/home/sammy/Maildir": 1 message 1 new
>N  1 sammy@example.com     Wed Dec 31 19:00   14/369   init
</code></pre>
<p>Just hitting <strong>ENTER</strong> should display your message:</p>
<pre class="code-pre "><code langs=""><div class="secondary-code-label " title="Output">Output</div>[-- Message  1 -- 14 lines, 369 bytes --]:
From sammy@example.com Wed Dec 31 19:00:00 1969
Date: Fri, 13 May 2016 18:07:49 -0400
To: sammy@example.com
Subject: init
Message-Id: <20160513220749.A278F228D9@mail.example.com>
From: sammy@example.com

init
</code></pre>
<p>You can get back to your message list by typing <strong>h</strong>:</p>
<pre class="code-pre custom_prefix"><code langs=""><ul class="prefixed"><li class="line" prefix="?">h
</li></ul></code></pre><pre class="code-pre "><code langs=""><div class="secondary-code-label " title="Output">Output</div>s-nail version v14.8.6.  Type ? for help.
"/home/sammy/Maildir": 1 message 1 new
>R  1 sammy@example.com     Wed Dec 31 19:00   14/369   init
</code></pre>
<p>Since this message isn't very useful, we can delete it with <strong>d</strong>:</p>
<pre class="code-pre custom_prefix"><code langs=""><ul class="prefixed"><li class="line" prefix="?">d
</li></ul></code></pre>
<p>Quit to get back to the terminal by typing <strong>q</strong>:</p>
<pre class="code-pre custom_prefix"><code langs=""><ul class="prefixed"><li class="line" prefix="?">q
</li></ul></code></pre>
<h3 id="sending-mail-with-the-client">Sending Mail with the Client</h3>

<p>You can test sending mail by typing a message in a text editor:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">nano ~/test_message
</li></ul></code></pre>
<p>Inside, enter some text you'd like to email:</p>
<div class="code-label " title="~/test_message">~/test_message</div><pre class="code-pre "><code langs="">Hello,

This is a test.  Please confirm receipt!
</code></pre>
<p>Using the <code>cat</code> command, we can pipe the message to the <code>mail</code> process.  This will send the message as your Linux user by default.  You can adjust the "From" field with the <code>-r</code> flag if you want to modify that value to something else:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">cat ~/<span class="highlight">test_message</span> | mail -s '<span class="highlight">Test email subject line</span>' -r <span class="highlight">from_field_account</span> <span class="highlight">user</span>@<span class="highlight">email.com</span>
</li></ul></code></pre>
<p>The options above are:</p>

<ul>
<li><code>-s</code>: The subject line of the email</li>
<li><code>-r</code>: An optional change to the "From:" field of the email.  By default, the Linux user you are logged in as will be used to populate this field.  The <code>-r</code> option allows you to override this.</li>
<li><code>user@email.com</code>: The account to send the email to.  Change this to be a valid account you have access to.</li>
</ul>

<p>You can view your sent messages within your <code>mail</code> client.  Start the interactive client again by typing:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">mail
</li></ul></code></pre>
<p>Afterwards, view your sent messages by typing:</p>
<pre class="code-pre custom_prefix"><code langs=""><ul class="prefixed"><li class="line" prefix="?">file +sent
</li></ul></code></pre>
<p>You can manage sent mail using the same commands you use for incoming mail.</p>

<h2 id="conclusion">Conclusion</h2>

<p>You should now have Postfix configured on your Ubuntu 16.04 server.  Managing email servers can be a tough task for beginning administrators, but with this configuration, you should have basic MTA email functionality to get you started.</p>

    