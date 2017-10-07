<link rel='stylesheet' href='digital_ocean.css'><br> <br> 
      <h3 id="introduction">Introduction</h3>

<p>Iptables is the software firewall that is included with most Linux distributions by default. This cheat sheet-style guide provides a quick reference to iptables commands that will create firewall rules are useful in common, everyday scenarios. This includes iptables examples of allowing and blocking various services by port, network interface, and source IP address.</p>

<h4 id="how-to-use-this-guide">How To Use This Guide</h4>

<ul>
<li>If you are just getting started with configuring your iptables firewall, check out our <a href="https://indiareads/community/tutorials/how-to-set-up-a-firewall-using-iptables-on-ubuntu-14-04">introduction to iptables</a></li>
<li>Most of the rules that are described here assume that your iptables is set to <strong>DROP</strong> incoming traffic, through the default input policy, and you want to selectively allow traffic in</li>
<li>Use whichever subsequent sections are applicable to what you are trying to achieve. Most sections are not predicated on any other, so you can use the examples below independently</li>
<li>Use the Contents menu on the right side of this page (at wide page widths) or your browser's find function to locate the sections you need</li>
<li>Copy and paste the command-line examples given, substituting the values in red with your own values</li>
</ul>

<p>Keep in mind that the order of your rules matter. All of these <code>iptables</code> commands use the <code>-A</code> option to append the new rule to the end of a chain. If you want to put it somewhere else in the chain, you can use the <code>-I</code> option which allows you to specify the position of the new rule (or simply place it at the beginning of the chain by not specifying a rule number).</p>

<p><span class="note"><strong>Note:</strong> When working with firewalls, take care not to lock yourself out of your own server by blocking SSH traffic (port 22, by default). If you lose access due to your firewall settings, you may need to <a href="https://indiareads/community/tutorials/how-to-use-the-digitalocean-console-to-access-your-droplet">connect to it via the console</a> to fix your access. Once you are connected via the console, you can change your firewall rules to allow SSH access (or allow all traffic). If your saved firewall rules allow SSH access, another method is to reboot your server.<br /></span></p>

<p>Remember that you can check your current iptables ruleset with <code>sudo iptables -S</code> and <code>sudo iptables -L</code>.</p>

<p>Let's take a look at the iptables commands!</p>

<h2 id="saving-rules">Saving Rules</h2>

<p>Iptables rules are ephemeral, which means they need to be manually saved for them to persist after a reboot.</p>

<h3 id="ubuntu">Ubuntu</h3>

<p>On Ubuntu, the easiest way to save iptables rules, so they will survive a reboot, is to use the <code>iptables-persistent</code> package. Install it with apt-get like this:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo apt-get install iptables-persistent
</li></ul></code></pre>
<p>During the installation, you will asked if you want to save your current firewall rules.</p>

<p>If you update your firewall rules and want to save the changes, run this command:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo invoke-rc.d iptables-persistent save
</li></ul></code></pre>
<h3 id="centos-6-and-older">CentOS 6 and Older</h3>

<p>On CentOS 6 and older—CentOS 7 uses FirewallD by default—you can use the <code>iptables</code> init script to save your iptables rules:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo service iptables save
</li></ul></code></pre>
<p>This will save your current iptables rules to the <code>/etc/sysconfig/iptables</code> file.</p>

<h2 id="listing-and-deleting-rules">Listing and Deleting Rules</h2>

<p>If you want to learn how to list and delete iptables rules, check out this tutorial: <a href="https://indiareads/community/tutorials/how-to-list-and-delete-iptables-firewall-rules">How To List and Delete Iptables Firewall Rules</a>.</p>

<h2 id="generally-useful-rules">Generally Useful Rules</h2>

<p>This section includes a variety of iptables commands that will create rules that are generally useful on most servers.</p>

<h3 id="allow-loopback-connections">Allow Loopback Connections</h3>

<p>The <strong>loopback</strong> interface, also referred to as <code>lo</code>, is what a computer uses to for network connections to itself. For example, if you run <code>ping localhost</code> or <code>ping 127.0.0.1</code>, your server will ping itself using the loopback. The loopback interface is also used if you configure your application server to connect to a database server with a "localhost" address. As such, you will want to be sure that your firewall is allowing these connections.</p>

<p>To accept all traffic on your loopback interface, run these commands:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo iptables -A INPUT -i lo -j ACCEPT
</li><li class="line" prefix="$">sudo iptables -A OUTPUT -o lo -j ACCEPT
</li></ul></code></pre>
<h3 id="allow-established-and-related-incoming-connections">Allow Established and Related Incoming Connections</h3>

<p>As network traffic generally needs to be two-way—incoming and outgoing—to work properly, it is typical to create a firewall rule that allows <strong>established</strong> and <strong>related</strong> incoming traffic, so that the server will allow return traffic to outgoing connections initiated by the server itself. This command will allow that:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo iptables -A INPUT -m conntrack --ctstate ESTABLISHED,RELATED -j ACCEPT
</li></ul></code></pre>
<h3 id="allow-established-outgoing-connections">Allow Established Outgoing Connections</h3>

<p>You may want to allow outgoing traffic of all <strong>established</strong> connections, which are typically the response to legitimate incoming connections.  This command will allow that:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo iptables -A OUTPUT -m conntrack --ctstate ESTABLISHED -j ACCEPT
</li></ul></code></pre>
<h3 id="internal-to-external">Internal to External</h3>

<p>Assuming <code>eth0</code> is your external network, and <code>eth1</code> is your internal network, this will allow your internal to access the external:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo iptables -A FORWARD -i eth1 -o eth0 -j ACCEPT
</li></ul></code></pre>
<h3 id="drop-invalid-packets">Drop Invalid Packets</h3>

<p>Some network traffic packets get marked as <strong>invalid</strong>. Sometimes it can be useful to log this type of packet but often it is fine to drop them. Do so with this command:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo iptables -A INPUT -m conntrack --ctstate INVALID -j DROP
</li></ul></code></pre>
<h2 id="block-an-ip-address">Block an IP Address</h2>

<p>To block network connections that originate from a specific IP address, <code>15.15.15.51</code> for example, run this command:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo iptables -A INPUT -s <span class="highlight">15.15.15.51</span> -j DROP
</li></ul></code></pre>
<p>In this example, <code>-s 15.15.15.51</code> specifies a <strong>source</strong> IP address of "15.15.15.51". The source IP address can be specified in any firewall rule, including an <strong>allow</strong> rule.</p>

<p>If you want to <strong>reject</strong> the connection instead, which will respond to the connection request with a "connection refused" error, replace "DROP" with "REJECT" like this:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo iptables -A INPUT -s <span class="highlight">15.15.15.51</span> -j REJECT
</li></ul></code></pre>
<h3 id="block-connections-to-a-network-interface">Block Connections to a Network Interface</h3>

<p>To block connections from a specific IP address, e.g. <code>15.15.15.51</code>, to a specific network interface, e.g. <code>eth0</code>, use this command:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">iptables -A INPUT -i <span class="highlight">eth0</span> -s <span class="highlight">15.15.15.51</span> -j DROP
</li></ul></code></pre>
<p>This is the same as the previous example, with the addition of <code>-i eth0</code>. The network interface can be specified in any firewall rule, and is a great way to limit the rule to a particular network.</p>

<h2 id="service-ssh">Service: SSH</h2>

<p>If you're using a cloud server, you will probably want to allow incoming SSH connections (port 22) so you can connect to and manage your server. This section covers how to configure your firewall with various SSH-related rules.</p>

<h3 id="allow-all-incoming-ssh">Allow All Incoming SSH</h3>

<p>To allow all incoming SSH connections run these commands:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo iptables -A INPUT -p tcp --dport 22 -m conntrack --ctstate NEW,ESTABLISHED -j ACCEPT
</li><li class="line" prefix="$">sudo iptables -A OUTPUT -p tcp --sport 22 -m conntrack --ctstate ESTABLISHED -j ACCEPT
</li></ul></code></pre>
<p>The second command, which allows the outgoing traffic of <strong>established</strong> SSH connections, is only necessary if the <code>OUTPUT</code> policy is not set to <code>ACCEPT</code>.</p>

<h3 id="allow-incoming-ssh-from-specific-ip-address-or-subnet">Allow Incoming SSH from Specific IP address or subnet</h3>

<p>To allow incoming SSH connections from a specific IP address or subnet, specify the source. For example, if you want to allow the entire <code>15.15.15.0/24</code> subnet, run these commands:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo iptables -A INPUT -p tcp -s <span class="highlight">15.15.15.0/24</span> --dport 22 -m conntrack --ctstate NEW,ESTABLISHED -j ACCEPT
</li><li class="line" prefix="$">sudo iptables -A OUTPUT -p tcp --sport 22 -m conntrack --ctstate ESTABLISHED -j ACCEPT
</li></ul></code></pre>
<p>The second command, which allows the outgoing traffic of <strong>established</strong> SSH connections, is only necessary if the <code>OUTPUT</code> policy is not set to <code>ACCEPT</code>.</p>

<h3 id="allow-outgoing-ssh">Allow Outgoing SSH</h3>

<p>If your firewall <code>OUTPUT</code> policy is not set to <code>ACCEPT</code>, and you want to allow outgoing SSH connections—your server initiating an SSH connection to another server—you can run these commands:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo iptables -A OUTPUT -p tcp --dport 22 -m conntrack --ctstate NEW,ESTABLISHED -j ACCEPT
</li><li class="line" prefix="$">sudo iptables -A INPUT -p tcp --sport 22 -m conntrack --ctstate ESTABLISHED -j ACCEPT
</li></ul></code></pre>
<h3 id="allow-incoming-rsync-from-specific-ip-address-or-subnet">Allow Incoming Rsync from Specific IP Address or Subnet</h3>

<p>Rsync, which runs on port 873, can be used to transfer files from one computer to another.</p>

<p>To allow incoming rsync connections from a specific IP address or subnet, specify the source IP address and the destination port. For example, if you want to allow the entire <code>15.15.15.0/24</code> subnet to be able to rsync to your server, run these commands:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo iptables -A INPUT -p tcp -s <span class="highlight">15.15.15.0/24</span> --dport 873 -m conntrack --ctstate NEW,ESTABLISHED -j ACCEPT
</li><li class="line" prefix="$">sudo iptables -A OUTPUT -p tcp --sport 873 -m conntrack --ctstate ESTABLISHED -j ACCEPT
</li></ul></code></pre>
<p>The second command, which allows the outgoing traffic of <strong>established</strong> rsync connections, is only necessary if the <code>OUTPUT</code> policy is not set to <code>ACCEPT</code>.</p>

<h2 id="service-web-server">Service: Web Server</h2>

<p>Web servers, such as Apache and Nginx, typically listen for requests on port 80 and 443 for HTTP and HTTPS connections, respectively. If your default policy for incoming traffic is set to drop or deny, you will want to create rules that will allow your server to respond to those requests.</p>

<h3 id="allow-all-incoming-http">Allow All Incoming HTTP</h3>

<p>To allow all incoming HTTP (port 80) connections run these commands:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo iptables -A INPUT -p tcp --dport 80 -m conntrack --ctstate NEW,ESTABLISHED -j ACCEPT
</li><li class="line" prefix="$">sudo iptables -A OUTPUT -p tcp --sport 80 -m conntrack --ctstate ESTABLISHED -j ACCEPT
</li></ul></code></pre>
<p>The second command, which allows the outgoing traffic of <strong>established</strong> HTTP connections, is only necessary if the <code>OUTPUT</code> policy is not set to <code>ACCEPT</code>.</p>

<h3 id="allow-all-incoming-https">Allow All Incoming HTTPS</h3>

<p>To allow all incoming HTTPS (port 443) connections run these commands:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo iptables -A INPUT -p tcp --dport 443 -m conntrack --ctstate NEW,ESTABLISHED -j ACCEPT
</li><li class="line" prefix="$">sudo iptables -A OUTPUT -p tcp --sport 443 -m conntrack --ctstate ESTABLISHED -j ACCEPT
</li></ul></code></pre>
<p>The second command, which allows the outgoing traffic of <strong>established</strong> HTTP connections, is only necessary if the <code>OUTPUT</code> policy is not set to <code>ACCEPT</code>.</p>

<h3 id="allow-all-incoming-http-and-https">Allow All Incoming HTTP and HTTPS</h3>

<p>If you want to allow both HTTP and HTTPS traffic, you can use the <strong>multiport</strong> module to create a rule that allows both ports. To allow all incoming HTTP and HTTPS (port 443) connections run these commands:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo iptables -A INPUT -p tcp -m multiport --dports 80,443 -m conntrack --ctstate NEW,ESTABLISHED -j ACCEPT
</li><li class="line" prefix="$">sudo iptables -A OUTPUT -p tcp -m multiport --dports 80,443 -m conntrack --ctstate ESTABLISHED -j ACCEPT
</li></ul></code></pre>
<p>The second command, which allows the outgoing traffic of <strong>established</strong> HTTP and HTTPS connections, is only necessary if the <code>OUTPUT</code> policy is not set to <code>ACCEPT</code>.</p>

<h2 id="service-mysql">Service: MySQL</h2>

<p>MySQL listens for client connections on port 3306. If your MySQL database server is being used by a client on a remote server, you need to be sure to allow that traffic.</p>

<h3 id="allow-mysql-from-specific-ip-address-or-subnet">Allow MySQL from Specific IP Address or Subnet</h3>

<p>To allow incoming MySQL connections from a specific IP address or subnet, specify the source. For example, if you want to allow the entire <code>15.15.15.0/24</code> subnet, run these commands:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo iptables -A INPUT -p tcp -s <span class="highlight">15.15.15.0/24</span> --dport 3306 -m conntrack --ctstate NEW,ESTABLISHED -j ACCEPT
</li><li class="line" prefix="$">sudo iptables -A OUTPUT -p tcp --sport 3306 -m conntrack --ctstate ESTABLISHED -j ACCEPT
</li></ul></code></pre>
<p>The second command, which allows the outgoing traffic of <strong>established</strong> MySQL connections, is only necessary if the <code>OUTPUT</code> policy is not set to <code>ACCEPT</code>.</p>

<h3 id="allow-mysql-to-specific-network-interface">Allow MySQL to Specific Network Interface</h3>

<p>To allow MySQL connections to a specific network interface—say you have a private network interface <code>eth1</code>, for example—use these commands:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo iptables -A INPUT -i <span class="highlight">eth1</span> -p tcp --dport 3306 -m conntrack --ctstate NEW,ESTABLISHED -j ACCEPT
</li><li class="line" prefix="$">sudo iptables -A OUTPUT -o <span class="highlight">eth1</span> -p tcp --sport 3306 -m conntrack --ctstate ESTABLISHED -j ACCEPT
</li></ul></code></pre>
<p>The second command, which allows the outgoing traffic of <strong>established</strong> MySQL connections, is only necessary if the <code>OUTPUT</code> policy is not set to <code>ACCEPT</code>.</p>

<h2 id="service-postgresql">Service: PostgreSQL</h2>

<p>PostgreSQL listens for client connections on port 5432. If your PostgreSQL database server is being used by a client on a remote server, you need to be sure to allow that traffic.</p>

<h3 id="postgresql-from-specific-ip-address-or-subnet">PostgreSQL from Specific IP Address or Subnet</h3>

<p>To allow incoming PostgreSQL connections from a specific IP address or subnet, specify the source. For example, if you want to allow the entire <code>15.15.15.0/24</code> subnet, run these commands:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo iptables -A INPUT -p tcp -s <span class="highlight">15.15.15.0/24</span> --dport 5432 -m conntrack --ctstate NEW,ESTABLISHED -j ACCEPT
</li><li class="line" prefix="$">sudo iptables -A OUTPUT -p tcp --sport 5432 -m conntrack --ctstate ESTABLISHED -j ACCEPT
</li></ul></code></pre>
<p>The second command, which allows the outgoing traffic of <strong>established</strong> PostgreSQL connections, is only necessary if the <code>OUTPUT</code> policy is not set to <code>ACCEPT</code>.</p>

<h3 id="allow-postgresql-to-specific-network-interface">Allow PostgreSQL to Specific Network Interface</h3>

<p>To allow PostgreSQL connections to a specific network interface—say you have a private network interface <code>eth1</code>, for example—use these commands:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo iptables -A INPUT -i <span class="highlight">eth1</span> -p tcp --dport 5432 -m conntrack --ctstate NEW,ESTABLISHED -j ACCEPT
</li><li class="line" prefix="$">sudo iptables -A OUTPUT -o <span class="highlight">eth1</span> -p tcp --sport 5432 -m conntrack --ctstate ESTABLISHED -j ACCEPT
</li></ul></code></pre>
<p>The second command, which allows the outgoing traffic of <strong>established</strong> PostgreSQL connections, is only necessary if the <code>OUTPUT</code> policy is not set to <code>ACCEPT</code>.</p>

<h2 id="service-mail">Service: Mail</h2>

<p>Mail servers, such as Sendmail and Postfix, listen on a variety of ports depending on the protocols being used for mail delivery. If you are running a mail server, determine which protocols you are using and allow the appropriate types of traffic. We will also show you how to create a rule to block outgoing SMTP mail.</p>

<h3 id="block-outgoing-smtp-mail">Block Outgoing SMTP Mail</h3>

<p>If your server shouldn't be sending outgoing mail, you may want to block that kind of traffic. To block outgoing SMTP mail, which uses port 25, run this command:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo iptables -A OUTPUT -p tcp --dport 25 -j REJECT
</li></ul></code></pre>
<p>This configures iptables to <strong>reject</strong> all outgoing traffic on port 25. If you need to reject a different service by its port number, instead of port 25, simply replace it.</p>

<h3 id="allow-all-incoming-smtp">Allow All Incoming SMTP</h3>

<p>To allow your server to respond to SMTP connections, port 25, run these commands:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo iptables -A INPUT -p tcp --dport 25 -m conntrack --ctstate NEW,ESTABLISHED -j ACCEPT
</li><li class="line" prefix="$">sudo iptables -A OUTPUT -p tcp --sport 25 -m conntrack --ctstate ESTABLISHED -j ACCEPT
</li></ul></code></pre>
<p>The second command, which allows the outgoing traffic of <strong>established</strong> SMTP connections, is only necessary if the <code>OUTPUT</code> policy is not set to <code>ACCEPT</code>.</p>

<p><span class="note"><strong>Note:</strong> It is common for SMTP servers to use port 587 for outbound mail.<br /></span></p>

<h3 id="allow-all-incoming-imap">Allow All Incoming IMAP</h3>

<p>To allow your server to respond to IMAP connections, port 143, run these commands:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo iptables -A INPUT -p tcp --dport 143 -m conntrack --ctstate NEW,ESTABLISHED -j ACCEPT
</li><li class="line" prefix="$">sudo iptables -A OUTPUT -p tcp --sport 143 -m conntrack --ctstate ESTABLISHED -j ACCEPT
</li></ul></code></pre>
<p>The second command, which allows the outgoing traffic of <strong>established</strong> IMAP connections, is only necessary if the <code>OUTPUT</code> policy is not set to <code>ACCEPT</code>.</p>

<h3 id="allow-all-incoming-imaps">Allow All Incoming IMAPS</h3>

<p>To allow your server to respond to IMAPS connections, port 993, run these commands:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo iptables -A INPUT -p tcp --dport 993 -m conntrack --ctstate NEW,ESTABLISHED -j ACCEPT
</li><li class="line" prefix="$">sudo iptables -A OUTPUT -p tcp --sport 993 -m conntrack --ctstate ESTABLISHED -j ACCEPT
</li></ul></code></pre>
<p>The second command, which allows the outgoing traffic of <strong>established</strong> IMAPS connections, is only necessary if the <code>OUTPUT</code> policy is not set to <code>ACCEPT</code>.</p>

<h3 id="allow-all-incoming-pop3">Allow All Incoming POP3</h3>

<p>To allow your server to respond to POP3 connections, port 110, run these commands:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo iptables -A INPUT -p tcp --dport 110 -m conntrack --ctstate NEW,ESTABLISHED -j ACCEPT
</li><li class="line" prefix="$">sudo iptables -A OUTPUT -p tcp --sport 110 -m conntrack --ctstate ESTABLISHED -j ACCEPT
</li></ul></code></pre>
<p>The second command, which allows the outgoing traffic of <strong>established</strong> POP3 connections, is only necessary if the <code>OUTPUT</code> policy is not set to <code>ACCEPT</code>.</p>

<h3 id="allow-all-incoming-pop3s">Allow All Incoming POP3S</h3>

<p>To allow your server to respond to POP3S connections, port 995, run these commands:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo iptables -A INPUT -p tcp --dport 995 -m conntrack --ctstate NEW,ESTABLISHED -j ACCEPT
</li><li class="line" prefix="$">sudo iptables -A OUTPUT -p tcp --sport 995 -m conntrack --ctstate ESTABLISHED -j ACCEPT
</li></ul></code></pre>
<p>The second command, which allows the outgoing traffic of <strong>established</strong> POP3S connections, is only necessary if the <code>OUTPUT</code> policy is not set to <code>ACCEPT</code>.</p>

<h2 id="conclusion">Conclusion</h2>

<p>That should cover many of the commands that are commonly used when configuring an iptables firewall. Of course, iptables is a very flexible tool so feel free to mix and match the commands with different options to match your specific needs if they aren't covered here.</p>

<p>If you're looking for help determining how your firewall should be set up, check out this tutorial: <a href="https://indiareads/community/tutorials/how-to-choose-an-effective-firewall-policy-to-secure-your-servers">How To Choose an Effective Firewall Policy to Secure your Servers</a>.</p>

<p>Good luck!</p>

    