<link rel='stylesheet' href='digital_ocean.css'><br> <br> 
      <h3 id="introduction">Introduction</h3>

<p><strong>NAT</strong>, or network address translation, is a general term for mangling packets in order to redirect them to an alternative address.  Usually, this is used to allow traffic to transcend network boundaries.  A host that implements NAT typically has access to two or more networks and is configured to route traffic between them.</p>

<p><strong>Port forwarding</strong> is the process of forwarding requests for a specific port to another host, network, or port.  As this process modifies the destination of the packet in-flight, it is considered a type of NAT operation.</p>

<p>In this guide, we'll demonstrate how to use <code>iptables</code> to forward ports to hosts behind a firewall by using NAT techniques.  This is useful if you've configured a private network, but still want to allow certain traffic inside through a designated gateway machine.  We will be using two Ubuntu 14.04 hosts to demonstrate this.</p>

<h2 id="prerequisites-and-goals">Prerequisites and Goals</h2>

<p>To follow along with this guide, you will need two Ubuntu 14.04 hosts in the same datacenter with private networking enabled.  On each of these machines, you will need to set up a non-root user account with <code>sudo</code> privileges.  You can learn how to create a user with <code>sudo</code> privileges by following our <a href="https://indiareads/community/tutorials/initial-server-setup-with-ubuntu-14-04">Ubuntu 14.04 initial server setup guide</a>.</p>

<p>The first host will function as our firewall and router for the private network.  For demonstration purposes, the second host will be configured with a web server that is only accessible using its private interface.  We will be configuring the firewall machine to forward requests received on its public interface to the web server, which it will reach on its private interface.</p>

<h2 id="host-details">Host Details</h2>

<p>Before you begin, we need to know the what interfaces and addresses are being used by both of our servers.</p>

<h3 id="finding-your-network-details">Finding Your Network Details</h3>

<p>To get the details of your own systems, begin by finding your network interfaces.  You can find the interfaces on your machines and the addresses associated with them by typing:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">ip -4 addr show scope global
</li></ul></code></pre><pre class="code-pre "><code langs=""><div class="secondary-code-label " title="Sample Output">Sample Output</div>2: <span class="highlight">eth0</span>: <BROADCAST,MULTICAST,UP,LOWER_UP> mtu 1500 qdisc pfifo_fast state UP group default qlen 1000
    inet <span class="highlight">198.51.100.45</span>/18 brd 45.55.191.255 scope global eth0
       valid_lft forever preferred_lft forever
3: <span class="highlight">eth1</span>: <BROADCAST,MULTICAST,UP,LOWER_UP> mtu 1500 qdisc pfifo_fast state UP group default qlen 1000
    inet <span class="highlight">192.168.1.5</span>/16 brd 10.132.255.255 scope global eth1
       valid_lft forever preferred_lft forever
</code></pre>
<p>The highlighted output above shows two interfaces (<code>eth0</code> and <code>eth1</code>) and the addresses assigned to each (<code>192.51.100.45</code> and <code>192.168.1.5</code> respectively).  To find out which of these interfaces is your public interface, type:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">ip route show | grep default
</li></ul></code></pre><pre class="code-pre "><code langs=""><div class="secondary-code-label " title="Output">Output</div>default via 111.111.111.111 <span class="highlight">dev eth0</span>
</code></pre>
<p>The interface shown (<code>eth0</code> in this example) will be the interface connected to your default gateway.  This is almost certainly your public interface.</p>

<p>Find these values on each of your machines and use them to correctly follow along with this guide.</p>

<h3 id="sample-data-for-this-guide">Sample Data for this Guide</h3>

<p>To make it easier to follow along, we'll be using the following dummy address and interface assignments throughout this tutorial.  Please substitute your own values for the ones you see below:</p>

<p>Web server network details:</p>

<ul>
<li>Public IP Address: <code><span class="highlight">203.0.113.2</span></code></li>
<li>Private IP Address: <code><span class="highlight">192.0.2.2</span></code></li>
<li>Public Interface: <code><span class="highlight">eth0</span></code></li>
<li>Private Interface: <code><span class="highlight">eth1</span></code></li>
</ul>

<p>Firewall network details:</p>

<ul>
<li>Public IP Address: <code><span class="highlight">203.0.113.15</span></code></li>
<li>Private IP Address: <code><span class="highlight">192.0.2.15</span></code></li>
<li>Public Interface: <code><span class="highlight">eth0</span></code></li>
<li>Private Interface: <code><span class="highlight">eth1</span></code></li>
</ul>

<h2 id="setting-up-the-web-server">Setting Up the Web Server</h2>

<p>We will begin with our web server host.  Log in with your <code>sudo</code> user to begin.</p>

<h3 id="install-nginx">Install Nginx</h3>

<p>The first process we will complete is to install <code>Nginx</code> on our web server host and lock it down so that it only listens to its private interface.  This will ensure that our web server will only be available if we correctly set up port forwarding.</p>

<p>Begin by updating the local package cache and using <code>apt</code> to download and install the software:</p>
<pre class="code-pre custom_prefix"><code langs=""><ul class="prefixed"><li class="line" prefix="webserver $">sudo apt-get update
</li><li class="line" prefix="webserver $">sudo apt-get install nginx
</li></ul></code></pre>
<h3 id="restrict-nginx-to-the-private-network">Restrict Nginx to the Private Network</h3>

<p>After Nginx is installed, we will open up the default server block configuration file to ensure that it only listens to the private interface.  Open the file now:</p>
<pre class="code-pre custom_prefix"><code langs=""><ul class="prefixed"><li class="line" prefix="webserver $">sudo nano /etc/nginx/sites-enabled/default
</li></ul></code></pre>
<p>Inside, find the <code>listen</code> directive.  You should find it twice in a row towards the top of the configuration:</p>
<div class="code-label " title="/etc/nginx/sites-enabled/default">/etc/nginx/sites-enabled/default</div><pre class="code-pre "><code langs="">server {
    listen 80 default_server;
    listen [::]:80 default_server ipv6only=on;

    . . .
}
</code></pre>
<p>At the first <code>listen</code> directive, add your <em>web server's private IP address</em> and a colon just ahead of the <code>80</code> to tell Nginx to only listen on the private interface.  We are only demonstrating IPv4 forwarding in this guide, so we can remove the second listen directive, which is configured for IPv6.</p>

<p>In our example, we'd modify the listen directives to look like this:</p>
<div class="code-label " title="/etc/nginx/sites-enabled/default">/etc/nginx/sites-enabled/default</div><pre class="code-pre "><code langs="">server {
    listen <span class="highlight">192.0.2.2</span>:80 default_server;

    . . .
}
</code></pre>
<p>Save and close the file when you are finished.  Test the file for syntax errors by typing:</p>
<pre class="code-pre custom_prefix"><code langs=""><ul class="prefixed"><li class="line" prefix="webserver $">sudo nginx -t
</li></ul></code></pre>
<p>If no errors are shown, restart Nginx to enable the new configuration:</p>
<pre class="code-pre custom_prefix"><code langs=""><ul class="prefixed"><li class="line" prefix="webserver $">sudo service nginx restart
</li></ul></code></pre>
<h3 id="verify-the-network-restriction">Verify the Network Restriction</h3>

<p>At this point, it's useful to verify the level of access we have to our web server.</p>

<p>From our <strong>firewall</strong> server, if we try to access our web server from the private interface, it should work:</p>
<pre class="code-pre custom_prefix"><code langs=""><ul class="prefixed"><li class="line" prefix="firewall $">curl --connect-timeout 5 <span class="highlight">192.0.2.2</span>
</li></ul></code></pre><pre class="code-pre "><code langs=""><div class="secondary-code-label " title="Output">Output</div><!DOCTYPE html>
<html>
<head>
<title>Welcome to nginx!</title>
<style>
    body {
        width: 35em;
        margin: 0 auto;
        font-family: Tahoma, Verdana, Arial, sans-serif;
    }
</style>
</head>
<body>
<h1>Welcome to nginx!</h1>
. . .
</code></pre>
<p>If we try to use the public interface, we will see that we cannot connect:</p>
<pre class="code-pre custom_prefix"><code langs=""><ul class="prefixed"><li class="line" prefix="firewall $">curl --connect-timeout 5 <span class="highlight">203.0.113.2</span>
</li></ul></code></pre><pre class="code-pre "><code langs="">curl: (7) Failed to connect to 203.0.113.2 port 80: Connection refused
</code></pre>
<p>This is exactly what we expect to happen.</p>

<h2 id="configuring-the-firewall-to-forward-port-80">Configuring the Firewall to Forward Port 80</h2>

<p>Now, we can work on implementing port forwarding on our firewall machine.  </p>

<h3 id="enable-forwarding-in-the-kernel">Enable Forwarding in the Kernel</h3>

<p>The first thing we need to do is enable traffic forwarding at the kernel level.  By default, most systems have forwarding turned off.</p>

<p>To turn port forwarding on for this session only, type:</p>
<pre class="code-pre custom_prefix"><code langs=""><ul class="prefixed"><li class="line" prefix="firewall $">echo 1 | sudo tee /proc/sys/net/ipv4/ip_forward
</li></ul></code></pre>
<p>To turn port forwarding on permanently, you will have to edit the <code>/etc/sysctl.conf</code> file.  Open the file with <code>sudo</code> privileges by typing:</p>
<pre class="code-pre custom_prefix"><code langs=""><ul class="prefixed"><li class="line" prefix="firewall $">sudo nano /etc/sysctl.conf
</li></ul></code></pre>
<p>Inside, find and uncomment the line that looks like this:</p>
<div class="code-label " title="/etc/sysctl.conf">/etc/sysctl.conf</div><pre class="code-pre "><code langs="">net.ipv4.ip_forward=1
</code></pre>
<p>Save and close the file when you are finished.  You apply the settings in this file by typing:</p>
<pre class="code-pre custom_prefix"><code langs=""><ul class="prefixed"><li class="line" prefix="firewall $">sudo sysctl -p
</li><li class="line" prefix="firewall $">sudo sysctl --system
</li></ul></code></pre>
<h3 id="setting-up-the-basic-firewall">Setting Up the Basic Firewall</h3>

<p>We will use the firewall in <a href="https://indiareads/community/tutorials/how-to-implement-a-basic-firewall-template-with-iptables-on-ubuntu-14-04">this guide</a> as the basic framework for the rules in this tutorial.  Run through the guide now on your firewall machine in order to get set up.  Upon finishing, you will have:</p>

<ul>
<li>Installed <code>iptables-persistent</code></li>
<li>Saved the default rule set into <code>/etc/iptables/rules.v4</code></li>
<li>Learned how to add or adjust rules by editing the rule file or by using the <code>iptables</code> command</li>
</ul>

<p>When you have the basic firewall set up, continue below so that we can adjust it for port forwarding.</p>

<h3 id="adding-the-forwarding-rules">Adding the Forwarding Rules</h3>

<p>We want to configure our firewall so that traffic flowing into our public interface (<code>eth0</code>) on port 80 will be forwarded to our private interface (<code>eth1</code>).</p>

<p>Our basic firewall has a our <code>FORWARD</code> chain set to <code>DROP</code> traffic by default.  We need to add rules that will allow us to forward connections to our web server.  For security's sake, we will lock this down fairly tightly so that only the connections we wish to forward are allowed.</p>

<p>In the <code>FORWARD</code> chain, we will accept new connections destined for port 80 that are coming from our public interface and travelling to our private interface.  New connections are identified by the <code>conntrack</code> extension and will specifically be represented by a TCP SYN packet:</p>
<pre class="code-pre custom_prefix"><code langs=""><ul class="prefixed"><li class="line" prefix="firewall $">sudo iptables -A FORWARD -i <span class="highlight">eth0</span> -o <span class="highlight">eth1</span> -p tcp --syn --dport 80 -m conntrack --ctstate NEW -j ACCEPT
</li></ul></code></pre>
<p>This will let the first packet, meant to establish a connection, through the firewall.  We also need to allow any subsequent traffic in both directions that results from that connection.  To allow <code>ESTABLISHED</code> and <code>RLEATED</code> traffic between our public and private interfaces, type:</p>
<pre class="code-pre custom_prefix"><code langs=""><ul class="prefixed"><li class="line" prefix="firewall $">iptables -A FORWARD -i eth0 -o eth1 -m conntrack --ctstate ESTABLISHED,RELATED -j ACCEPT
</li><li class="line" prefix="firewall $">iptables -A FORWARD -i eth1 -o eth0 -m conntrack --ctstate ESTABLISHED,RELATED -j ACCEPT
</li></ul></code></pre>
<p>We can double check that our policy on the <code>FORWARD</code> chain is set to <code>DROP</code> by typing:</p>
<pre class="code-pre custom_prefix"><code langs=""><ul class="prefixed"><li class="line" prefix="firewall $">sudo iptables -P FORWARD DROP
</li></ul></code></pre>
<p>At this point, we have allowed certain traffic between our public and private interfaces to proceed through our firewall.  However, we haven't yet configured the rules that will actually tell <code>iptables</code> how to translate and direct the traffic.</p>

<h3 id="adding-the-nat-rules-to-direct-packets-correctly">Adding the NAT Rules to Direct Packets Correctly</h3>

<p>Next, we'll add the rules that will tell <code>iptables</code> how to route our traffic.  We need to perform two separate operations in order for <code>iptables</code> to correctly alter the packets so that clients can communicate with the web server.</p>

<p>The first operation, called <code>DNAT</code>, will take place in the <code>PREROUTING</code> chain of the <code>nat</code> table.  <code>DNAT</code> is an operation which alters a packet's destination address in order to enable it to be correctly routed as it passes between networks.  The clients on the public network will be connecting to our firewall server and will have no knowledge of our private network topology.  We need to alter the destination address of each packet so that when it is sent out on our private network, it knows how to correctly reach our web server.</p>

<p>Since we are only configuring port forwarding and not performing NAT on every packet that hits our firewall, we'll want to match port 80 on our rule.  We will match packets aimed at port 80 to our web server's private IP address (<code>192.0.2.2</code> in our example):</p>
<pre class="code-pre custom_prefix"><code langs=""><ul class="prefixed"><li class="line" prefix="firewall $">sudo iptables -t nat -A PREROUTING -i eth0 -p tcp --dport 80 -j DNAT --to-destination <span class="highlight">192.0.2.2</span>
</li></ul></code></pre>
<p>This takes care of half of the picture.  The packet should get routed correctly to our web server.  However, right now, the packet will still have the client's original address as the source address.  The server will attempt to send the reply directly to that address, which will make it impossible to establish a legitimate TCP connection.</p>

<p></p><div class="code-label notes-and-warnings note" title="Note">Note</div><span class="note">
On IndiaReads, packets leaving a Droplet with a different source address will actually be dropped by the hypervisor, so your packets at this stage will never even make it to the web server (we will fix this by implementing SNAT momentarily).  This is an anti-spoofing measure put in place to prevent attacks where large amounts of data are requested to be sent to a victim's computer by faking the source address in the request.  To find out more, view this <a href="https://indiareads/community/questions/nat-gateway-on-digital-ocean-s-droplet-possible?answer=13896">response in our community</a>.<br /></span>

<p>To configure proper routing, we also need to modify the packet's source address as it leaves the firewall en route to the web server.  We need to modify the source address to our firewall server's private IP address (<code>192.0.2.15</code> in our example).  The reply will then be sent back to the firewall, which can then forward it back to the client as expected.</p>

<p>To enable this functionality, we'll add a rule to the <code>POSTROUTING</code> chain of the <code>nat</code> table, which is evaluated right before packets are sent out on the network.  We'll match the packets destined for our web server by IP address and port:</p>
<pre class="code-pre custom_prefix"><code langs=""><ul class="prefixed"><li class="line" prefix="firewall $">sudo iptables -t nat -A POSTROUTING -o eth1 -p tcp --dport 80 -d <span class="highlight">192.0.2.2</span> -j SNAT --to-source <span class="highlight">192.0.2.15</span>
</li></ul></code></pre>
<p>Once this rule is in place, our web server should be accessible by pointing our web browser at our firewall machine's public address:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">curl <span class="highlight">203.0.113.15</span>
</li></ul></code></pre><pre class="code-pre "><code langs=""><div class="secondary-code-label " title="Output">Output</div><!DOCTYPE html>
<html>
<head>
<title>Welcome to nginx!</title>
<style>
    body {
        width: 35em;
        margin: 0 auto;
        font-family: Tahoma, Verdana, Arial, sans-serif;
    }
</style>
</head>
<body>
<h1>Welcome to nginx!</h1>
. . .
</code></pre>
<p>Our port forwarding setup is complete.</p>

<h2 id="adjusting-the-permanent-rule-set">Adjusting the Permanent Rule Set</h2>

<p>Now that we have set up port forwarding, we can save this to our permanent rule set.</p>

<p>If you do not care about losing the comments that are in your current rule set, just use the <code>iptables-persistent</code> service to save your rules:</p>
<pre class="code-pre custom_prefix"><code langs=""><ul class="prefixed"><li class="line" prefix="firewall $">sudo service iptables-persistent save
</li></ul></code></pre>
<p>If you would like to keep the comments in your file, open it up and edit manually:</p>
<pre class="code-pre custom_prefix"><code langs=""><ul class="prefixed"><li class="line" prefix="firewall $">sudo nano /etc/iptables/rules.v4
</li></ul></code></pre>
<p>You will need to adjust the configuration in the <code>filter</code> table for the <code>FORWARD</code> chain rules that were added.  You will also need to adjust the section which configures the <code>nat</code> table so that you can add your <code>PREROUTING</code> and <code>POSTROUTING</code> rules.  For our example, it would look something like this:</p>
<div class="code-label " title="/etc/iptables/rules.v4">/etc/iptables/rules.v4</div><pre class="code-pre "><code langs="">*filter
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

<span class="highlight"># Rules to forward port 80 to our web server</span>

# Web server network details:

# * Public IP Address: <span class="highlight">203.0.113.2</span>
# * Private IP Address: <span class="highlight">192.0.2.2</span>
# * Public Interface: <span class="highlight">eth0</span>
# * Private Interface: <span class="highlight">eth1</span>
# 
# Firewall network details:
# 
# * Public IP Address: <span class="highlight">203.0.113.15</span>
# * Private IP Address: <span class="highlight">192.0.2.15</span>
# * Public Interface: <span class="highlight">eth0</span>
# * Private Interface: <span class="highlight">eth1</span>
-A FORWARD -i eth0 -o eth1 -p tcp --syn --dport 80 -m conntrack --ctstate NEW -j ACCEPT
-A FORWARD -i eth0 -o eth1 -m conntrack --ctstate ESTABLISHED,RELATED -j ACCEPT
-A FORWARD -i eth1 -o eth0 -m conntrack --ctstate ESTABLISHED,RELATED -j ACCEPT
<span class="highlight"># End of Forward filtering rules</span>

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

<span class="highlight"># Rules to translate requests for port 80 of the public interface</span>
<span class="highlight"># so that we can forward correctly to the web server using the</span>
<span class="highlight"># private interface.</span>

# Web server network details:

# * Public IP Address: <span class="highlight">203.0.113.2</span>
# * Private IP Address: <span class="highlight">192.0.2.2</span>
# * Public Interface: <span class="highlight">eth0</span>
# * Private Interface: <span class="highlight">eth1</span>
# 
# Firewall network details:
# 
# * Public IP Address: <span class="highlight">203.0.113.15</span>
# * Private IP Address: <span class="highlight">192.0.2.15</span>
# * Public Interface: <span class="highlight">eth0</span>
# * Private Interface: <span class="highlight">eth1</span>
-A PREROUTING -i eth0 -p tcp --dport 80 -j DNAT --to-destination <span class="highlight">192.0.2.2</span>
-A POSTROUTING -d <span class="highlight">192.0.2.2</span> -o eth1 -p tcp --dport 80 -j SNAT --to-source <span class="highlight">192.0.2.15</span>
<span class="highlight"># End of NAT translations for web server traffic</span>
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
<p>Save and close the file once you have added the above and adjusted the values to reflect your own network environment.</p>

<p>Test the syntax of your rules file by typeing:</p>
<pre class="code-pre custom_prefix"><code langs=""><ul class="prefixed"><li class="line" prefix="firewall $">sudo iptables-restore -t < /etc/iptables/rules.v4
</li></ul></code></pre>
<p>If no errors are detected, load the rule set:</p>
<pre class="code-pre custom_prefix"><code langs=""><ul class="prefixed"><li class="line" prefix="firewall $">sudo service iptables-persistent reload
</li></ul></code></pre>
<p>Test that your web server is still accessible through your firewall's public IP address:</p>
<pre class="code-pre custom_prefix"><code langs=""><ul class="prefixed"><li class="line" prefix="firewall $">curl <span class="highlight">203.0.113.15</span>
</li></ul></code></pre>
<p>This should work just as it did before.</p>

<h2 id="conclusion">Conclusion</h2>

<p>By now, you should be comfortable with forwarding ports on a Linux server with <code>iptables</code>.  The process involves permitting forwarding at the kernel level, setting up access to allow forwarding of the specific port's traffic between two interfaces on the firewall system, and configuring the NAT rules so that the packets can be routed correctly.  This may seem like an unwieldy process, but it also demonstrates the flexibility of the <code>netfilter</code> packet filtering framework and the <code>iptables</code> firewall.  This can be used to disguise your private networks topology while permitting service traffic to flow freely through your gateway firewall machine.</p>

    