<link rel='stylesheet' href='digital_ocean.css'><br> <br> 
      <h3 id="introduction">Introduction</h3>

<p>When you first log into a fresh Fedora 21 or RHEL server, it's not ready for use as a production system. There are a number of recommended steps to take in order to customize and secure it, such as enabling a firewall.</p>

<p>This tutorial will show you how to give a fresh installation of a Fedora 21 server a better security profile and be ready for use.</p>

<h2 id="prerequisites">Prerequisites</h2>

<p>To follow this tutorial, you will need:</p>

<ul>
<li>A Fedora 21 Droplet with root SSH keys.</li>
</ul>

<p>You can follow <a href="https://indiareads/community/tutorials/how-to-configure-ssh-key-based-authentication-on-a-linux-server#how-to-create-ssh-keys">this section</a> of the SSH key tutorial to create keys if you don't have them, and <a href="https://indiareads/community/tutorials/how-to-configure-ssh-key-based-authentication-on-a-linux-server#how-to-embed-your-public-key-when-creating-your-server">this section</a> of the same tutorial to automatically embed your SSH key in your server's root account when you create your Droplet.</p>

<h2 id="step-1-—-creating-a-standard-user-account">Step 1 — Creating a Standard User Account</h2>

<p>First, log into your server as <strong>root</strong>.</p>
<pre class="code-pre "><code langs="">ssh root@<span class="highlight">your_server_ip</span>
</code></pre>
<p>Operating as root is a security risk, so in this step, we'll set up a sudo non-root user account to use for system and other computing tasks. The username used in this tutorial is <strong>sammy</strong>, but you can use any name you like. </p>

<p>To add the user, type:</p>
<pre class="code-pre "><code langs="">adduser <span class="highlight">sammy</span>
</code></pre>
<p>Specify a strong password for the user using the command below. You'll be prompted to enter the password twice.</p>
<pre class="code-pre "><code langs="">passwd <span class="highlight">sammy</span>
</code></pre>
<p>Then add the user to the wheel group, which gives it sudo privileges. </p>
<pre class="code-pre "><code langs="">gpasswd -a <span class="highlight">sammy</span> wheel
</code></pre>
<p>Log out of your server and add your SSH key to the new user account by running the following on your local machine.</p>
<pre class="code-pre "><code langs="">ssh-copy-id <span class="highlight">sammy</span>@<span class="highlight">your_server_ip</span>
</code></pre>
<p>For more information on how to copy your SSH keys from your local machine to your server, you can read <a href="https://indiareads/community/tutorials/how-to-configure-ssh-key-based-authentication-on-a-linux-server#how-to-copy-a-public-key-to-your-server">this section</a> of the SSH tutorial.</p>

<p>Finally, log back in as the new sudo non-root user. You won't be prompted for a password because this account now has SSH keys.</p>
<pre class="code-pre "><code langs="">ssh <span class="highlight">sammy</span>@<span class="highlight">your_server_ip</span>
</code></pre>
<h2 id="step-2-—-disallowing-root-login-and-password-authentication">Step 2 — Disallowing Root Login and Password Authentication</h2>

<p>In this step, we'll make SSH logins more secure by disabling root logins and password authentication.</p>

<p>To edit configuration files, you'll need to install a text editor. We'll use <code>nano</code> but you can use whichever is your favorite.</p>

<p>First, apply any available updates using:</p>
<pre class="code-pre "><code langs="">sudo yum update
</code></pre>
<p>Then, to install <code>nano</code>, type:</p>
<pre class="code-pre "><code langs="">sudo yum install -y nano
</code></pre>
<p>Now, open the the SSH daemon's configuration file for editing.</p>
<pre class="code-pre "><code langs="">sudo nano /etc/ssh/sshd_config
</code></pre>
<p>Inside that file, look for the <code>PermitRootLogin</code> directive. Uncomment it (that means remove the starting <code>#</code> character) and set it to <strong>no</strong>.</p>
<pre class="code-pre "><code langs="">PermitRootLogin no
</code></pre>
<p>Similarly, look for the <code>PasswordAuthentication</code> directive and set it to <strong>no</strong> as well.</p>
<pre class="code-pre "><code langs="">PasswordAuthentication no
</code></pre>
<p>Save and exit the file, then reload the configuration to put your changes into place.</p>
<pre class="code-pre "><code langs="">sudo systemctl reload sshd
</code></pre>
<p>If anyone tries to log in as <strong>root</strong> now, the response should be <code>Permission denied (publickey,gssapi-keyex,gssapi-with-mic)</code>.</p>

<h2 id="step-3-­—-configuring-the-time-zone">Step 3 ­— Configuring the Time Zone</h2>

<p>In this step, you'll read how to change the system clock to your local time zone. The default clock is set to UTC. </p>

<p>All the known timezones are under the <code>/usr/share/zoneinfo/</code> directory. Take a look at the files and directories in <code>/usr/share/zoneinfo/</code>.</p>
<pre class="code-pre "><code langs="">ls /usr/share/zoneinfo/
</code></pre>
<p>To set the clock to use the local timezone, find your country or geographical area in that directory, locate the zone file under it, then create a symbolic soft link from it to the <code>/etc/localtime</code> directory. For example, if you're in the central part of the United States, where the timezone is <strong>Central</strong>, or <strong>CST</strong>, the zone file will be <code>/usr/share/zoneinfo/US/Central</code>.</p>

<p>Create a symbolic soft link from your zone file to <code>/etc/localtime</code>.</p>
<pre class="code-pre "><code langs="">sudo ln -sf /usr/share/zoneinfo/<span class="highlight">your_zone_file</span> /etc/localtime
</code></pre>
<p>Verify that the clock is now set to local time by viewing the output of the <code>date</code> command.</p>
<pre class="code-pre "><code langs="">date
</code></pre>
<p>The output will look something like:</p>
<pre class="code-pre "><code langs="">Wed Mar 25 14:41:20 CST 2015
</code></pre>
<p>The <strong>CST</strong> in that output confirms that it's Central time.</p>

<h2 id="step-4-—-enabling-a-firewall">Step 4 — Enabling a Firewall</h2>

<p>A new Fedora 21 server has no active firewall application. In this step, we'll learn how to enable the IPTables firewall application and make sure that runtime rules persist after a reboot.</p>

<p>The IPTables package is already installed, but to be enable to enable it, you need to install the <code>iptables-services</code> package.</p>
<pre class="code-pre "><code langs="">sudo yum install -y iptables-services
</code></pre>
<p>You may then enable IPTables so that it automatically starts on boot.</p>
<pre class="code-pre "><code langs="">sudo systemctl enable iptables
</code></pre>
<p>Next, start IPTables.</p>
<pre class="code-pre "><code langs="">sudo systemctl start iptables
</code></pre>
<p>IPTables on Fedora 21 ships with a default set of rules. One of those rules permits SSH traffic. To view the default rules, type:</p>
<pre class="code-pre "><code langs="">sudo iptables -L
</code></pre>
<p>The output should read:</p>
<pre class="code-pre "><code langs="">Chain INPUT (policy ACCEPT)
target     prot opt source               destination
ACCEPT     all  --  anywhere             anywhere             state RELATED,ESTABLISHED
ACCEPT     icmp --  anywhere             anywhere
ACCEPT     all  --  anywhere             anywhere
ACCEPT     tcp  --  anywhere             anywhere             state NEW tcp dpt:ssh
REJECT     all  --  anywhere             anywhere             reject-with icmp-host-prohibited

Chain FORWARD (policy ACCEPT)
target     prot opt source               destination
REJECT     all  --  anywhere             anywhere             reject-with icmp-host-prohibited

Chain OUTPUT (policy ACCEPT)
target     prot opt source               destination
</code></pre>
<p>Those rules are runtime rules and will be lost if the system is rebooted. To save the current runtime rules to a file so that they persist after a reboot, type:</p>
<pre class="code-pre "><code langs="">sudo /usr/libexec/iptables/iptables.init save
</code></pre>
<p>The rules are now saved to a file called <code>iptables</code> in the <code>/etc/sysconfig</code> directory.</p>

<h2 id="step-5-optional-—-allowing-http-and-https-traffic">Step 5 (Optional) — Allowing HTTP and HTTPS Traffic</h2>

<p>In this section, we'll cover how to edit the firewall rules to allow services for ports 80 (HTTP) and 443 (HTTPS).</p>

<p>The default IPTables rules allow SSH traffic in by default, but HTTP and its relatively more secure cousin, HTTPS, are services that many applications use, so you may want to allow these to pass through the firewall as well.</p>

<p>To proceed, open the firewall rules file by typing:</p>
<pre class="code-pre "><code langs="">sudo nano /etc/sysconfig/iptables
</code></pre>
<p>All you need to do is add two rules, one for port 80 and another for port 443, after the rule for SSH (port 22) traffic. The lines below in red are the ones you will add; the lines before and after are included for context to help you find where to add the new rules.</p>
<pre class="code-pre "><code langs="">-A INPUT -p tcp -m state --state NEW -m tcp --dport 22 -j ACCEPT
<span class="highlight">-A INPUT -p tcp -m state --state NEW -m tcp --dport 80 -j ACCEPT</span>
<span class="highlight">-A INPUT -p tcp -m state --state NEW -m tcp --dport 443 -j ACCEPT</span>
-A INPUT -j REJECT --reject-with icmp-host-prohibited
</code></pre>
<p>To activate the new ruleset, restart IPTables.</p>
<pre class="code-pre "><code langs="">sudo systemctl restart iptables
</code></pre>
<h2 id="step-6-optional-installing-mlocate">Step 6 (Optional) - Installing Mlocate</h2>

<p>The <code>locate</code> command is a very useful utility for looking up the location of files in the system. For example, to find a file called <strong>example</strong>, you would type:</p>
<pre class="code-pre "><code langs="">locate example
</code></pre>
<p>That will scan the file system and print the location or locations of the file on your screen. There are more advanced ways of using <code>locate</code>, too.</p>

<p>To make the command available on your server, first you need to install the <code>mlocate</code> package.</p>
<pre class="code-pre "><code langs="">sudo yum install -y mlocate
</code></pre>
<p>Then, run the <code>updatedb</code> command to update the search database.</p>
<pre class="code-pre "><code langs="">sudo updatedb
</code></pre>
<p>After that, you should be able to use <code>locate</code> to find any file by name.</p>

<h3 id="conclusion">Conclusion</h3>

<p>After completing the last step, your Fedora 21 server should be configured, reasonably secure, and ready for use!</p>

    