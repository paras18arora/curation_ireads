<link rel='stylesheet' href='digital_ocean.css'><br><img src=https://community-cdn-digitalocean-com.global.ssl.fastly.net/assets/tutorials/images/large/Firewall-with-UFW-TW.png?1444002169/> <br> 
      <h3 id="introduction">Introduction</h3>

<p>UFW, or Uncomplicated Firewall, is an interface to <code>iptables</code> that is geared towards simplifying the process of configuring a firewall. While <code>iptables</code> is a solid and flexible tool, it can be difficult for beginners to learn how to use it to properly configure a firewall. If you're looking to get started securing your network, and you're not sure which tool to use, UFW may be the right choice for you.</p>

<p>This tutorial will show you how to set up a firewall with UFW on Ubuntu 14.04.</p>

<h2 id="prerequisites">Prerequisites</h2>

<p>Before you start using this tutorial, you should have a separate, non-root superuser account—a user with sudo privileges—set up on your Ubuntu server. You can learn how to do this by completing at least steps 1-3 in the <a href="https://indiareads/community/tutorials/initial-server-setup-with-ubuntu-14-04">Initial Server Setup with Ubuntu 14.04</a> tutorial.</p>

<p>UFW is installed by default on Ubuntu. If it has been uninstalled for some reason, you can install it with <code>apt-get</code>:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo apt-get install ufw
</li></ul></code></pre>
<h3 id="using-ipv6-with-ufw">Using IPv6 with UFW</h3>

<p>If your Ubuntu server has IPv6 enabled, ensure that UFW is configured to support IPv6 so that it will manage firewall rules for IPv6 in addition to IPv4. To do this, open the UFW configuration with your favorite editor. We'll use nano:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo nano /etc/default/ufw
</li></ul></code></pre>
<p>Then make sure the value of "IPV6" is to equal "yes". It should look like this:</p>
<div class="code-label " title="/etc/default/ufw excerpt">/etc/default/ufw excerpt</div><pre class="code-pre "><code langs="">...
IPV6=<span class="highlight">yes</span>
...
</code></pre>
<p>Save and quit. Hit <code>Ctrl-X</code> to exit the file, then <code>Y</code> to save the changes that you made, then <code>ENTER</code> to confirm the file name.</p>

<p>When UFW is enabled, it will be configured to write both IPv4 and IPv6 firewall rules.</p>

<p>This tutorial is written with IPv4 in mind, but will work fine for IPv6 as long as you enable it.</p>

<h2 id="check-ufw-status-and-rules">Check UFW Status and Rules</h2>

<p>At any time, you can check the status of UFW with this command:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo ufw status verbose
</li></ul></code></pre>
<p>By default, UFW is disabled so you should see something like this:</p>
<pre class="code-pre "><code langs=""><div class="secondary-code-label " title="Output:">Output:</div>Status: inactive
</code></pre>
<p>If UFW is active, the output will say that it's active, and it will list any rules that are set. For example, if the firewall is set to allow SSH (port 22) connections from anywhere, the output might look something like this:</p>
<pre class="code-pre "><code langs=""><div class="secondary-code-label " title="Output:">Output:</div>Status: active
Logging: on (low)
Default: deny (incoming), allow (outgoing), disabled (routed)
New profiles: skip

To                         Action      From
--                         ------      ----
22/tcp                     ALLOW IN    Anywhere
</code></pre>
<p>As such, use the status command if you ever need to check how UFW has configured the firewall.</p>

<p>Before enabling UFW, we will want to ensure that your firewall is configured to allow you to connect via SSH. Let's start with setting the default policies.</p>

<h2 id="set-up-default-policies">Set Up Default Policies</h2>

<p>If you're just getting started with your firewall, the first rules to define are your default policies. These rules control how to handle traffic that does not explicitly match any other rules. By default, UFW is set to deny all incoming connections and allow all outgoing connections. This means anyone trying to reach your cloud server would not be able to connect, while any application within the server would be able to reach the outside world.</p>

<p>Let's set your UFW rules back to the defaults so we can be sure that you'll be able to follow along with this tutorial. To set the defaults used by UFW, use these commands:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo ufw default deny incoming
</li><li class="line" prefix="$">sudo ufw default allow outgoing
</li></ul></code></pre>
<p>As you might have guessed, these commands set the defaults to deny incoming and allow outgoing connections. These firewall defaults, by themselves, might suffice for a personal computer but servers typically need to respond to incoming requests from outside users. We'll look into that next.</p>

<h2 id="allow-ssh-connections">Allow SSH Connections</h2>

<p>If we enabled our UFW firewall now, it would deny all incoming connections. This means that we will need to create rules that explicitly allow legitimate incoming connections—SSH or HTTP connections, for example—if we want our server to respond to those types of requests. If you're using a cloud server, you will probably want to allow incoming SSH connections so you can connect to and manage your server.</p>

<p>To configure your server to allow incoming SSH connections, you can use this UFW command:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo ufw allow ssh
</li></ul></code></pre>
<p>This will create firewall rules that will allow all connections on port 22, which is the port that the SSH daemon listens on. UFW knows what "ssh", and a bunch of other service names, means because it's listed as a service that uses port 22 in the <code>/etc/services</code> file.</p>

<p>We can actually write the equivalent rule by specifying the <strong>port</strong> instead of the service name. For example, this command works the same as the one above:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo ufw allow 22
</li></ul></code></pre>
<p>If you configured your SSH daemon to use a different port, you will have to specify the appropriate port. For example, if your SSH server is listening on port 2222, you can use this command to allow connections on that port:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo ufw allow <span class="highlight">2222</span>
</li></ul></code></pre>
<p>Now that your firewall is configured to allow incoming SSH connections, we can enable it.</p>

<h2 id="enable-ufw">Enable UFW</h2>

<p>To enable UFW, use this command:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo ufw enable
</li></ul></code></pre>
<p>You will receive a warning that says the "command may disrupt existing ssh connections." We already set up a firewall rule that allows SSH connections so it should be fine to continue. Respond to the prompt with <code>y</code>.</p>

<p>The firewall is now active. Feel free to run the <code>sudo ufw status verbose</code> command to see the rules that are set.</p>

<h2 id="allow-other-connections">Allow Other Connections</h2>

<p>Now you should allow all of the other connections that your server needs to respond to. The connections that you should allow depends your specific needs. Luckily, you already know how to write rules that allow connections based on a service name or port—we already did this for SSH on port 22.</p>

<p>We will show a few examples of very common services that you may need to allow. If you have any other services for which you want to allow all incoming connections, follow this format.</p>

<h3 id="http—port-80">HTTP—port 80</h3>

<p>HTTP connections, which is what unencrypted web servers use, can be allowed with this command:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo ufw allow http
</li></ul></code></pre>
<p>If you'd rather use the port number, 80, use this command:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo ufw allow 80
</li></ul></code></pre>
<h3 id="https—port-443">HTTPS—port 443</h3>

<p>HTTPS connections, which is what encrypted web servers use, can be allowed with this command:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo ufw allow https
</li></ul></code></pre>
<p>If you'd rather use the port number, 443, use this command:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo ufw allow 443
</li></ul></code></pre>
<h3 id="ftp—port-21">FTP—port 21</h3>

<p>FTP connections, which is used for unencrypted file transfers (which you probably shouldn't use anyway), can be allowed with this command:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo ufw allow ftp
</li></ul></code></pre>
<p>If you'd rather use the port number, 21, use this command:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo ufw allow 21/tcp
</li></ul></code></pre>
<h2 id="allow-specific-port-ranges">Allow Specific Port Ranges</h2>

<p>You can specify port ranges with UFW. Some applications use multiple ports, instead of a single port.</p>

<p>For example, to allow X11 connections, which use ports 6000-6007, use these commands:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo ufw allow <span class="highlight">6000</span>:<span class="highlight">6007</span>/tcp
</li><li class="line" prefix="$">sudo ufw allow <span class="highlight">6000</span>:<span class="highlight">6007</span>/udp
</li></ul></code></pre>
<p>When specifying port ranges with UFW, you must specify the protocol (<code>tcp</code> or <code>udp</code>) that the rules should apply to. We haven't mentioned this before because not specifying the protocol simply allows both protocols, which is OK in most cases.</p>

<h2 id="allow-specific-ip-addresses">Allow Specific IP Addresses</h2>

<p>When working with UFW, you can also specify IP addresses. For example, if you want to allow connections from a specific IP address, such as a work or home IP address of <code>15.15.15.51</code>, you need to specify "from" then the IP address:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo ufw allow from <span class="highlight">15.15.15.51</span>
</li></ul></code></pre>
<p>You can also specify a specific port that the IP address is allowed to connect to by adding "to any port" followed by the port number. For example, If you want to allow <code>15.15.15.51</code> to connect to port 22 (SSH), use this command:</p>
<pre class="code-pre "><code langs="">sudo ufw allow from <span class="highlight">15.15.15.51</span> to any port <span class="highlight">22</span>
</code></pre>
<h2 id="allow-subnets">Allow Subnets</h2>

<p>If you want to allow a subnet of IP addresses, you can do so using CIDR notation to specify a netmask. For example, if you want to allow all of the IP addresses ranging from <code>15.15.15.1</code> to <code>15.15.15.254</code> you could use this command:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo ufw allow from <span class="highlight">15.15.15.0</span>/<span class="highlight">24</span>
</li></ul></code></pre>
<p>Likewise, you may also specify the destination port that the subnet <code>15.15.15.0/24</code> is allowed to connect to. Again, we'll use port 22 (SSH) as an example:</p>
<pre class="code-pre "><code langs="">sudo ufw allow from <span class="highlight">15.15.15.0</span>/<span class="highlight">24</span> to any port 22
</code></pre>
<h2 id="allow-connections-to-a-specific-network-interface">Allow Connections to a Specific Network Interface</h2>

<p>If you want to create a firewall rule that only applies to a specific network interface, you can do so by specifying "allow in on" followed by the name of the network interface.</p>

<p>You may want to look up your network interfaces before continuing. To do so, use this command:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">ip addr
</li></ul></code></pre><pre class="code-pre "><code langs=""><div class="secondary-code-label " title="Output Excerpt:">Output Excerpt:</div>...
2: <span class="highlight">eth0</span>: <BROADCAST,MULTICAST,UP,LOWER_UP> mtu 1500 qdisc pfifo_fast state
...
3: <span class="highlight">eth1</span>: <BROADCAST,MULTICAST> mtu 1500 qdisc noop state DOWN group default 
...
</code></pre>
<p>The highlighted output indicates the network interface names. They are typically named something like "eth0" or "eth1".</p>

<p>So, if your server has a public network interface called <code>eth0</code>, you could allow HTTP traffic (port 80) to it with this command:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo ufw allow in on <span class="highlight">eth0</span> to any port <span class="highlight">80</span>
</li></ul></code></pre>
<p>Doing so would allow your server to receive HTTP requests from the public Internet.</p>

<p>Or, if you want your MySQL database server (port 3306) to listen for connections on the private network interface <code>eth1</code>, for example, you could use this command:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo ufw allow in on <span class="highlight">eth1</span> to any port <span class="highlight">3306</span>
</li></ul></code></pre>
<p>This would allow other servers on your private network to connect to your MySQL database.</p>

<h2 id="deny-connections">Deny Connections</h2>

<p>If you haven't changed the default policy for incoming connections, UFW is configured to deny all incoming connections. Generally, this simplifies the process of creating a secure firewall policy by requiring you to create rules that explicitly allow specific ports and IP addresses through. However, sometimes you will want to deny specific connections based on the source IP address or subnet, perhaps because you know that your server is being attacked from there. Also, if you want change your default incoming policy to <strong>allow</strong> (which isn't recommended in the interest of security), you would need to create <strong>deny</strong> rules for any services or IP addresses that you don't want to allow connections for.</p>

<p>To write <strong>deny</strong> rules, you can use the commands that we described above except you need to replace "allow" with "deny".</p>

<p>For example to deny HTTP connections, you could use this command:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo ufw deny http
</li></ul></code></pre>
<p>Or if you want to deny all connections from <code>15.15.15.51</code> you could use this command:</p>
<pre class="code-pre "><code langs="">sudo ufw deny from <span class="highlight">15.15.15.51</span>
</code></pre>
<p>If you need help writing any other <strong>deny</strong> rules, just look at the previous <strong>allow</strong> rules and update them accordingly.</p>

<p>Now let's take a look at how to delete rules.</p>

<h2 id="delete-rules">Delete Rules</h2>

<p>Knowing how to delete firewall rules is just as important as knowing how to create them. There are two different ways specify which rules to delete: by rule number or by the actual rule (similar to how the rules were specified when they were created). We'll start with the <strong>delete by rule number</strong> method because it is easier, compared to writing the actual rules to delete, if you're new to UFW.</p>

<h3 id="by-rule-number">By Rule Number</h3>

<p>If you're using the rule number to delete firewall rules, the first thing you'll want to do is get a list of your firewall rules. The UFW status command has an option to display numbers next to each rule, as demonstrated here:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo ufw status numbered
</li></ul></code></pre><pre class="code-pre "><code langs=""><div class="secondary-code-label " title="Numbered Output:">Numbered Output:</div>Status: active

     To                         Action      From
     --                         ------      ----
[ 1] 22                         ALLOW IN    15.15.15.0/24
[ 2] 80                         ALLOW IN    Anywhere
</code></pre>
<p>If we decide that we want to delete rule 2, the one that allows port 80 (HTTP) connections, we can specify it in a UFW delete command like this:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo ufw delete <span class="highlight">2</span>
</li></ul></code></pre>
<p>This would show a confirmation prompt then delete rule 2, which allows HTTP connections. Note that if you have IPv6 enabled, you would want to delete the corresponding IPv6 rule as well.</p>

<h3 id="by-actual-rule">By Actual Rule</h3>

<p>The alternative to rule numbers is to specify the actual rule to delete. For example, if you want to remove the "allow http" rule, you could write it like this:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo ufw delete <span class="highlight">allow http</span>
</li></ul></code></pre>
<p>You could also specify the rule by "allow 80", instead of by service name:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo ufw delete <span class="highlight">allow 80</span>
</li></ul></code></pre>
<p>This method will delete both IPv4 and IPv6 rules, if they exist.</p>

<h2 id="how-to-disable-ufw-optional">How To Disable UFW (optional)</h2>

<p>If you decide you don't want to use UFW for whatever reason, you can disable it with this command:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo ufw disable
</li></ul></code></pre>
<p>Any rules that you created with UFW will no longer be active. You can always run <code>sudo ufw enable</code> if you need to activate it later.</p>

<h3 id="reset-ufw-rules-optional">Reset UFW Rules (optional)</h3>

<p>If you already have UFW rules configured but you decide that you want to start over, you can use the reset command:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo ufw reset
</li></ul></code></pre>
<p>This will disable UFW and delete any rules that were previously defined. Keep in mind that the default policies won't change to their original settings, if you modified them at any point. This should give you a fresh start with UFW.</p>

<h2 id="conclusion">Conclusion</h2>

<p>Your firewall should now be configured to allow (at least) SSH connections. Be sure to allow any other incoming connections that your server, while limiting any unnecessary connections, so your server will be functional and secure.</p>

<p>To learn about more common UFW configurations, check out this tutorial: <a href="https://indiareads/community/tutorials/ufw-essentials-common-firewall-rules-and-commands">UFW Essentials: Common Firewall Rules and Commands</a></p>

<p>Good luck!</p>

    