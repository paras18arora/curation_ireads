<link rel='stylesheet' href='digital_ocean.css'><br> <br> 
      <h3 id="introduction">Introduction</h3>

<p>UFW is a firewall configuration tool for iptables that is included with Ubuntu by default. This cheat sheet-style guide provides a quick reference to UFW commands that will create iptables firewall rules are useful in common, everyday scenarios. This includes UFW examples of allowing and blocking various services by port, network interface, and source IP address.</p>

<h4 id="how-to-use-this-guide">How To Use This Guide</h4>

<ul>
<li>If you are just getting started with using UFW to configure your firewall, check out our <a href="https://indiareads/community/tutorials/how-to-set-up-a-firewall-with-ufw-on-ubuntu-14-04">introduction to UFW</a></li>
<li>Most of the rules that are described here assume that you are using the default UFW ruleset. That is, it is set to allow outgoing and deny incoming traffic, through the default policies, so you have to selectively allow traffic in</li>
<li>Use whichever subsequent sections are applicable to what you are trying to achieve. Most sections are not predicated on any other, so you can use the examples below independently</li>
<li>Use the Contents menu on the right side of this page (at wide page widths) or your browser's find function to locate the sections you need</li>
<li>Copy and paste the command-line examples given, substituting the values in red with your own values</li>
</ul>

<p>Remember that you can check your current UFW ruleset with <code>sudo ufw status</code> or <code>sudo ufw status verbose</code>.</p>

<h2 id="block-an-ip-address">Block an IP Address</h2>

<p>To block all network connections that originate from a specific IP address, <code>15.15.15.51</code> for example, run this command:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo ufw deny from <span class="highlight">15.15.15.51</span>
</li></ul></code></pre>
<p>In this example, <code>from 15.15.15.51</code> specifies a <strong>source</strong> IP address of "15.15.15.51". If you wish, a subnet, such as <code>15.15.15.0/24</code>, may be specified here instead. The source IP address can be specified in any firewall rule, including an <strong>allow</strong> rule.</p>

<h3 id="block-connections-to-a-network-interface">Block Connections to a Network Interface</h3>

<p>To block connections from a specific IP address, e.g. <code>15.15.15.51</code>, to a specific network interface, e.g. <code>eth0</code>, use this command:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo ufw deny in on eth0 from <span class="highlight">15.15.15.51</span>
</li></ul></code></pre>
<p>This is the same as the previous example, with the addition of <code>in on eth0</code>. The network interface can be specified in any firewall rule, and is a great way to limit the rule to a particular network.</p>

<h2 id="service-ssh">Service: SSH</h2>

<p>If you're using a cloud server, you will probably want to allow incoming SSH connections (port 22) so you can connect to and manage your server. This section covers how to configure your firewall with various SSH-related rules.</p>

<h3 id="allow-ssh">Allow SSH</h3>

<p>To allow all incoming SSH connections run this command:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo ufw allow ssh
</li></ul></code></pre>
<p>An alternative syntax is to specify the port number of the SSH service:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo ufw allow 22
</li></ul></code></pre>
<h3 id="allow-incoming-ssh-from-specific-ip-address-or-subnet">Allow Incoming SSH from Specific IP Address or Subnet</h3>

<p>To allow incoming SSH connections from a specific IP address or subnet, specify the source. For example, if you want to allow the entire <code>15.15.15.0/24</code> subnet, run this command:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo ufw allow from <span class="highlight">15.15.15.0/24</span>  to any port 22
</li></ul></code></pre>
<h3 id="allow-incoming-rsync-from-specific-ip-address-or-subnet">Allow Incoming Rsync from Specific IP Address or Subnet</h3>

<p>Rsync, which runs on port 873, can be used to transfer files from one computer to another.</p>

<p>To allow incoming rsync connections from a specific IP address or subnet, specify the source IP address and the destination port. For example, if you want to allow the entire <code>15.15.15.0/24</code> subnet to be able to rsync to your server, run this command:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo ufw allow from <span class="highlight">15.15.15.0/24</span> to any port 873
</li></ul></code></pre>
<h2 id="service-web-server">Service: Web Server</h2>

<p>Web servers, such as Apache and Nginx, typically listen for requests on port 80 and 443 for HTTP and HTTPS connections, respectively. If your default policy for incoming traffic is set to drop or deny, you will want to create rules that will allow your server to respond to those requests.</p>

<h3 id="allow-all-incoming-http">Allow All Incoming HTTP</h3>

<p>To allow all incoming HTTP (port 80) connections run this command:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo ufw allow http
</li></ul></code></pre>
<p>An alternative syntax is to specify the port number of the HTTP service:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo ufw allow 80
</li></ul></code></pre>
<h3 id="allow-all-incoming-https">Allow All Incoming HTTPS</h3>

<p>To allow all incoming HTTPS (port 443) connections run this command:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo ufw allow https
</li></ul></code></pre>
<p>An alternative syntax is to specify the port number of the HTTPS service:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo ufw allow 443
</li></ul></code></pre>
<h3 id="allow-all-incoming-http-and-https">Allow All Incoming HTTP and HTTPS</h3>

<p>If you want to allow both HTTP and HTTPS traffic, you can create a single rule that allows both ports. To allow all incoming HTTP and HTTPS (port 443) connections run this command:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo ufw allow proto tcp from any to any port 80,443
</li></ul></code></pre>
<p>Note that you need to specify the protocol, with <code>proto tcp</code>, when specifying multiple ports.</p>

<h2 id="service-mysql">Service: MySQL</h2>

<p>MySQL listens for client connections on port 3306. If your MySQL database server is being used by a client on a remote server, you need to be sure to allow that traffic.</p>

<h3 id="allow-mysql-from-specific-ip-address-or-subnet">Allow MySQL from Specific IP Address or Subnet</h3>

<p>To allow incoming MySQL connections from a specific IP address or subnet, specify the source. For example, if you want to allow the entire <code>15.15.15.0/24</code> subnet, run this command:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo ufw allow from <span class="highlight">15.15.15.0/24</span> to any port 3306
</li></ul></code></pre>
<h3 id="allow-mysql-to-specific-network-interface">Allow MySQL to Specific Network Interface</h3>

<p>To allow MySQL connections to a specific network interface—say you have a private network interface <code>eth1</code>, for example—use this command:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo ufw allow in on <span class="highlight">eth1</span> to any port 3306
</li></ul></code></pre>
<h2 id="service-postgresql">Service: PostgreSQL</h2>

<p>PostgreSQL listens for client connections on port 5432. If your PostgreSQL database server is being used by a client on a remote server, you need to be sure to allow that traffic.</p>

<h3 id="postgresql-from-specific-ip-address-or-subnet">PostgreSQL from Specific IP Address or Subnet</h3>

<p>To allow incoming PostgreSQL connections from a specific IP address or subnet, specify the source. For example, if you want to allow the entire <code>15.15.15.0/24</code> subnet, run this command:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo ufw allow from <span class="highlight">15.15.15.0/24</span> to any port 5432
</li></ul></code></pre>
<p>The second command, which allows the outgoing traffic of <strong>established</strong> PostgreSQL connections, is only necessary if the <code>OUTPUT</code> policy is not set to <code>ACCEPT</code>.</p>

<h3 id="allow-postgresql-to-specific-network-interface">Allow PostgreSQL to Specific Network Interface</h3>

<p>To allow PostgreSQL connections to a specific network interface—say you have a private network interface <code>eth1</code>, for example—use this command:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo ufw allow in on <span class="highlight">eth1</span> to any port 5432
</li></ul></code></pre>
<p>The second command, which allows the outgoing traffic of <strong>established</strong> PostgreSQL connections, is only necessary if the <code>OUTPUT</code> policy is not set to <code>ACCEPT</code>.</p>

<h2 id="service-mail">Service: Mail</h2>

<p>Mail servers, such as Sendmail and Postfix, listen on a variety of ports depending on the protocols being used for mail delivery. If you are running a mail server, determine which protocols you are using and allow the appropriate types of traffic. We will also show you how to create a rule to block outgoing SMTP mail.</p>

<h3 id="block-outgoing-smtp-mail">Block Outgoing SMTP Mail</h3>

<p>If your server shouldn't be sending outgoing mail, you may want to block that kind of traffic. To block outgoing SMTP mail, which uses port 25, run this command:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo ufw deny out 25
</li></ul></code></pre>
<p>This configures your firewall to <strong>drop</strong> all outgoing traffic on port 25. If you need to reject a different service by its port number, instead of port 25, simply replace it.</p>

<h3 id="allow-all-incoming-smtp">Allow All Incoming SMTP</h3>

<p>To allow your server to respond to SMTP connections, port 25, run this command:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo ufw allow 25
</li></ul></code></pre>
<p><span class="note"><strong>Note:</strong> It is common for SMTP servers to use port 587 for outbound mail.<br /></span></p>

<h3 id="allow-all-incoming-imap">Allow All Incoming IMAP</h3>

<p>To allow your server to respond to IMAP connections, port 143, run this command:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo ufw allow 143
</li></ul></code></pre>
<h3 id="allow-all-incoming-imaps">Allow All Incoming IMAPS</h3>

<p>To allow your server to respond to IMAPS connections, port 993, run this command:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo ufw allow 993
</li></ul></code></pre>
<h3 id="allow-all-incoming-pop3">Allow All Incoming POP3</h3>

<p>To allow your server to respond to POP3 connections, port 110, run this command:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo ufw allow 110
</li></ul></code></pre>
<h3 id="allow-all-incoming-pop3s">Allow All Incoming POP3S</h3>

<p>To allow your server to respond to POP3S connections, port 995, run this command:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo ufw allow 995
</li></ul></code></pre>
<h2 id="conclusion">Conclusion</h2>

<p>That should cover many of the commands that are commonly used when using UFW to configure a firewall. Of course, UFW is a very flexible tool so feel free to mix and match the commands with different options to match your specific needs if they aren't covered here.</p>

<p>Good luck!</p>

    