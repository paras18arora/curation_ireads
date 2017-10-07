<link rel='stylesheet' href='digital_ocean.css'><br> <br> 
      <h3 id="introduction">Introduction</h3>

<p>Deploying discrete components in your application setup onto different nodes is a common way to decrease load and begin scaling horizontally.  A typical example is configuring a database on a separate server from your application.  While there are a great number of advantages with this setup, connecting over a network involves a new set of security concerns.</p>

<p>In this guide, we'll demonstrate how to set up a simple firewall on each of your servers in a distributed setup.  We will configure our policy to allow legitimate traffic between our components while denying other traffic.</p>

<p>For the demonstration in this guide, we'll be using two Ubuntu 14.04 servers.  One will have a WordPress instance served with Nginx and the other will host the MySQL database for the application.  Although we will be using this setup as an example, you should be able to extrapolate the techniques involved to fit your own server requirements.</p>

<h3 id="prerequisites">Prerequisites</h3>

<p>To get started, you will have to have two fresh Ubuntu 14.04 servers.  Add a regular user account with <code>sudo</code> privileges on each.  To learn how to do this correctly, follow our <a href="https://indiareads/community/tutorials/initial-server-setup-with-ubuntu-14-04">Ubuntu 14.04 initial server setup guide</a>.</p>

<p>The application setup we will be securing is based on <a href="https://indiareads/community/tutorials/how-to-set-up-a-remote-database-to-optimize-site-performance-with-mysql">this guide</a>.  If you'd like to follow along, set up your application and database servers as indicated by that tutorial.</p>

<h2 id="setting-up-a-basic-firewall">Setting Up a Basic Firewall</h2>

<p>We will begin by implementing a baseline firewall configuration for each of our servers.  The policy that we will be implementing takes a security-first approach.  We will be locking down almost everything other than SSH traffic and then poking holes in the firewall for our specific application.</p>

<p>The firewall in <a href="https://indiareads/community/tutorials/how-to-implement-a-basic-firewall-template-with-iptables-on-ubuntu-14-04">this guide</a> provides the basic setup that we need.  Install the <code>iptables-persistent</code> package and paste the basic rules into the <code>/etc/iptables/rules.v4</code> file:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo apt-get update
</li><li class="line" prefix="$">sudo apt-get install iptables-persistent
</li><li class="line" prefix="$">sudo nano /etc/iptables/rules.v4
</li></ul></code></pre><div class="code-label " title="/etc/iptables/rules.v4">/etc/iptables/rules.v4</div><pre class="code-pre "><code langs="">*filter
# Allow all outgoing, but drop incoming and forwarding packets by default
:INPUT DROP [0:0]
:FORWARD DROP [0:0]
:OUTPUT ACCEPT [0:0]

# Custom per-protocol chains
:UDP - [0:0]
:TCP - [0:0]
:ICMP - [0:0]

# Acceptable UDP traffic

# Acceptable TCP traffic
-A TCP -p tcp --dport 22 -j ACCEPT

# Acceptable ICMP traffic

# Boilerplate acceptance policy
-A INPUT -m conntrack --ctstate ESTABLISHED,RELATED -j ACCEPT
-A INPUT -i lo -j ACCEPT

# Drop invalid packets
-A INPUT -m conntrack --ctstate INVALID -j DROP

# Pass traffic to protocol-specific chains
## Only allow new connections (established and related should already be handled)
## For TCP, additionally only allow new SYN packets since that is the only valid
## method for establishing a new TCP connection
-A INPUT -p udp -m conntrack --ctstate NEW -j UDP
-A INPUT -p tcp --syn -m conntrack --ctstate NEW -j TCP
-A INPUT -p icmp -m conntrack --ctstate NEW -j ICMP

# Reject anything that's fallen through to this point
## Try to be protocol-specific w/ rejection message
-A INPUT -p udp -j REJECT --reject-with icmp-port-unreachable
-A INPUT -p tcp -j REJECT --reject-with tcp-reset
-A INPUT -j REJECT --reject-with icmp-proto-unreachable

# Commit the changes
COMMIT

*raw
:PREROUTING ACCEPT [0:0]
:OUTPUT ACCEPT [0:0]
COMMIT

*nat
:PREROUTING ACCEPT [0:0]
:INPUT ACCEPT [0:0]
:OUTPUT ACCEPT [0:0]
:POSTROUTING ACCEPT [0:0]
COMMIT

*security
:INPUT ACCEPT [0:0]
:FORWARD ACCEPT [0:0]
:OUTPUT ACCEPT [0:0]
COMMIT

*mangle
:PREROUTING ACCEPT [0:0]
:INPUT ACCEPT [0:0]
:FORWARD ACCEPT [0:0]
:OUTPUT ACCEPT [0:0]
:POSTROUTING ACCEPT [0:0]
COMMIT
</code></pre>
<p>If you are implementing this in a live environment <strong>do not reload your firewall rules yet</strong>.  Loading the basic rule set outlined here will immediately drop the connection between your application and database server.  We will need to adjust the rules to reflect our operational needs before reloading.</p>

<h2 id="discover-the-ports-being-used-by-your-services">Discover the Ports Being Used by Your Services</h2>

<p>In order to add exceptions to allow communication between our components, we need to know the network ports being used.  We could find the correct network ports by examining our configuration files, but an application-agnostic method of finding the correct ports is to just check which services are listening for connections on each of our machines.</p>

<p>We can use the <code>netstat</code> tool to find this out.  Since our application is only communicating over IPv4, we will add the <code>-4</code> argument but you can remove that if you are using IPv6 as well.  The other arguments we need in order to find our running services are <code>-plunt</code>.</p>

<p>On your web server, we would see something like this:</p>
<pre class="code-pre custom_prefix"><code langs=""><ul class="prefixed"><li class="line" prefix="web_server$">sudo netstat -4plunt
</li></ul></code></pre><pre class="code-pre "><code langs=""><div class="secondary-code-label " title="Output">Output</div>Active Internet connections (only servers)
Proto Recv-Q Send-Q Local Address           Foreign Address         State       PID/Program name
tcp        0      0 <span class="highlight">0.0.0.0:22</span>              0.0.0.0:*               LISTEN      1058/<span class="highlight">sshd</span>
tcp        0      0 <span class="highlight">0.0.0.0:80</span>              0.0.0.0:*               LISTEN      4187/<span class="highlight">nginx</span>
</code></pre>
<p>The first highlighted column shows the IP address and port that the service highlighted towards the end of the line is listening on.  The special <code>0.0.0.0</code> address means that the service in question is listening on all available addresses.</p>

<p>On our database server we would see something like this:</p>
<pre class="code-pre custom_prefix"><code langs=""><ul class="prefixed"><li class="line" prefix="db_server$">sudo netstat -4plunt
</li></ul></code></pre><pre class="code-pre "><code langs=""><div class="secondary-code-label " title="Output">Output</div>Active Internet connections (only servers)
Proto Recv-Q Send-Q Local Address           Foreign Address         State       PID/Program name
tcp        0      0 <span class="highlight">0.0.0.0:22</span>              0.0.0.0:*               LISTEN      1097/<span class="highlight">sshd</span>
tcp        0      0 <span class="highlight">192.0.2.30:3306</span>     0.0.0.0:*               LISTEN      3112/<span class="highlight">mysqld</span>
</code></pre>
<p>You can read these columns exactly the same.  In the above example, the <code>192.0.2.30</code> address represents the database server's private IP address.  In the application setup, we locked MySQL down to the private interface for security reasons.</p>

<p>Take note of the values you find in this step.  These are the networking details that we need in order to adjust our firewall configuration.</p>

<p>In our example scenario, we can note that on our web server, we need to ensure that the following ports are accessible:</p>

<ul>
<li>Port 80 on all addresses</li>
<li>Port 22 on all addresses (already accounted for in firewall rules)</li>
</ul>

<p>Our database server would have to ensure that the following ports are accessible:</p>

<ul>
<li>Port 3306 on the address <code>192.0.2.30</code> (or the interface associated with it)</li>
<li>Port 22 on all addresses (already accounted for in firewall rules)</li>
</ul>

<h2 id="adjust-the-web-server-firewall-rules">Adjust the Web Server Firewall Rules</h2>

<p>Now that we have the port information we need, we will adjust our web server's firewall rule set.  Open the rules file in your editor with <code>sudo</code> privileges:</p>
<pre class="code-pre custom_prefix"><code langs=""><ul class="prefixed"><li class="line" prefix="web_server$">sudo nano /etc/iptables/rules.v4
</li></ul></code></pre>
<p>On the web server, we need to add port 80 to our list of acceptable traffic.  Since the server is listening on all available addresses, we will not restrict the rule by interface or destination address.</p>

<p>Our web visitors will be using the TCP protocol to connect.  Our basic framework already has a custom chain called <code>TCP</code> for TCP application exceptions.  We can add port 80 to that chain, right below the exception for our SSH port:</p>
<div class="code-label " title="/etc/iptables/rules.v4">/etc/iptables/rules.v4</div><pre class="code-pre "><code langs="">*filter
. . .

# Acceptable TCP traffic
-A TCP -p tcp --dport 22 -j ACCEPT
<span class="highlight">-A TCP -p tcp --dport 80 -j ACCEPT</span>

. . .
</code></pre>
<p>Our web server will initiate the connection with our database server.  Our outgoing traffic is not restricted in our firewall and incoming traffic associated with established connections is permitted, so we do not have to open any additional ports on this server allow this connection.</p>

<p>Save and close the file when you are finished.  Our web server now has a firewall policy that will allow all legitimate traffic while blocking everything else.</p>

<p>Test your rules file for syntax errors:</p>
<pre class="code-pre custom_prefix"><code langs=""><ul class="prefixed"><li class="line" prefix="web_server$">sudo iptables-restore -t < /etc/iptables/rules.v4
</li></ul></code></pre>
<p>If no syntax errors are displayed, reload the firewall to implement the new rule set:</p>
<pre class="code-pre custom_prefix"><code langs=""><ul class="prefixed"><li class="line" prefix="web_server$">sudo service iptables-persistent reload
</li></ul></code></pre>
<h2 id="adjust-the-database-server-firewall-rules">Adjust the Database Server Firewall Rules</h2>

<p>On our database server, we need to allow access to port <code>3306</code> on our server's private IP address.  In our case, that address was <code>192.0.2.30</code>.  We can limit access destined for this address specifically, or we can limit access by matching against the interface that is assigned that address.</p>

<p>To find the network interface associated with that address, type:</p>
<pre class="code-pre custom_prefix"><code langs=""><ul class="prefixed"><li class="line" prefix="db_server$">ip -4 addr show scope global
</li></ul></code></pre><pre class="code-pre "><code langs=""><div class="secondary-code-label " title="Output">Output</div>2: eth0: <BROADCAST,MULTICAST,UP,LOWER_UP> mtu 1500 qdisc pfifo_fast state UP group default qlen 1000
    inet 203.0.113.5/24 brd 104.236.113.255 scope global eth0
       valid_lft forever preferred_lft forever
3: <span class="highlight">eth1</span>: <BROADCAST,MULTICAST,UP,LOWER_UP> mtu 1500 qdisc pfifo_fast state UP group default qlen 1000
    inet <span class="highlight">192.0.2.30</span>/24 brd 192.0.2.255 scope global eth1
       valid_lft forever preferred_lft forever
</code></pre>
<p>The highlighted areas show that the <code>eth1</code> interface is associated with that address.</p>

<p>Next, we will adjust the firewall rules on the database server.  Open the rules file with <code>sudo</code> privileges on your database server:</p>
<pre class="code-pre custom_prefix"><code langs=""><ul class="prefixed"><li class="line" prefix="db_server$">sudo nano /etc/iptables/rules.v4
</li></ul></code></pre>
<p>Again, we will be adding a rule to our <code>TCP</code> chain to form an exception for the connection between our web and database servers.</p>

<p>If you wish to restrict access based on the actual address in question, you would add the rule like this:</p>
<div class="code-label " title="/etc/iptables/rules.v4">/etc/iptables/rules.v4</div><pre class="code-pre "><code langs="">*filter
. . .

# Acceptable TCP traffic
-A TCP -p tcp --dport 22 -j ACCEPT
<span class="highlight">-A TCP -p tcp --dport 3306 -d 192.0.2.30 -j ACCEPT</span>

. . .
</code></pre>
<p>If you would rather allow the exception based on the interface that houses that address, you can add a rule similar to this one instead:</p>
<div class="code-label " title="/etc/iptables/rules.v4">/etc/iptables/rules.v4</div><pre class="code-pre "><code langs="">*filter
. . .

# Acceptable TCP traffic
-A TCP -p tcp --dport 22 -j ACCEPT
<span class="highlight">-A TCP -p tcp --dport 3306 -i eth1 -j ACCEPT</span>

. . .
</code></pre>
<p>Save and close the file when you are finished.</p>

<p>Check for syntax errors with this command:</p>
<pre class="code-pre custom_prefix"><code langs=""><ul class="prefixed"><li class="line" prefix="db_server$">sudo iptables-restore -t < /etc/iptables/rules.v4
</li></ul></code></pre>
<p>When you are ready, reload the firewall rules:</p>
<pre class="code-pre custom_prefix"><code langs=""><ul class="prefixed"><li class="line" prefix="db_server$">sudo service iptables-persistent reload
</li></ul></code></pre>
<p>Both of your servers should now be protected without restricting the necessary flow of data between them.</p>

<h2 id="conclusion">Conclusion</h2>

<p>Implementing a proper firewall should always be part of your deployment plan when setting up an application.  Although we demonstrated this configuration using the two servers running Nginx and MySQL to provide a WordPress instance, the techniques demonstrated above are applicable regardless of your specific technology choices.</p>

<p>To learn more about firewalls and <code>iptables</code> specifically, take a look at the following guides:</p>

<ul>
<li><a href="https://indiareads/community/tutorials/how-to-choose-an-effective-firewall-policy-to-secure-your-servers">How To Choose an Effective Firewall Policy to Secure your Servers</a></li>
<li><a href="https://indiareads/community/tutorials/a-deep-dive-into-iptables-and-netfilter-architecture">A Deep Dive into Iptables and Netfilter Architecture</a></li>
<li><a href="https://indiareads/community/tutorials/how-to-test-your-firewall-configuration-with-nmap-and-tcpdump">How To Test your Firewall Configuration with Nmap and Tcpdump</a></li>
</ul>

    