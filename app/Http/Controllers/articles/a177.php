<link rel='stylesheet' href='digital_ocean.css'><br> <br> 
      <h3 id="introduction">Introduction</h3>

<p>A firewall is a system that provides network security by filtering incoming and outgoing network traffic based on a set of user-defined rules. In general, the purpose of a firewall is to reduce or eliminate the occurrence of unwanted network communications while allowing all legitimate communication to flow freely. In most server infrastructures, firewalls provide an essential layer of security that, combined with other measures, prevent attackers from accessing your servers in malicious ways.</p>

<p>This guide will discuss how firewalls work, with a focus on <strong>stateful</strong> software firewalls, such as iptables and FirewallD, as they relate to cloud servers. We'll start with a brief explanation of TCP packets and the different types of firewalls. Then we'll discuss a variety of topics that a relevant to stateful firewalls. Lastly, we will provide links to other tutorials that will help you set up a firewall on your own server.</p>

<h2 id="tcp-network-packets">TCP Network Packets</h2>

<p>Before discussing the different types of firewalls, let's take a quick look at what Transport Control Protocol (TCP) network traffic looks like.</p>

<p>TCP network traffic moves around a network in <strong>packets</strong>, which are containers that consist of a packet header—this contains control information such as source and destination addresses, and packet sequence information—and the data (also known as a payload). While the control information in each packet helps to ensure that its associated data gets delivered properly, the elements it contains also provides firewalls a variety of ways to match packets against firewall rules.</p>

<p>It is important to note that successfully receiving incoming TCP packets requires the receiver to send outgoing acknowledgment packets back to the sender. The combination of the control information in the incoming and outgoing packets can be used to determine the connection state (e.g. new, established, related) of between the sender and receiver.</p>

<h2 id="types-of-firewalls">Types of Firewalls</h2>

<p>Let's quickly discuss the three basic types of network firewalls: packet filtering (stateless), stateful, and application layer.</p>

<p>Packet filtering, or stateless, firewalls work by inspecting individual packets in isolation. As such, they are unaware of connection state and can only allow or deny packets based on individual packet headers.</p>

<p>Stateful firewalls are able to determine the connection state of packets, which makes them much more flexible than stateless firewalls. They work by collecting related packets until the connection state can be determined before any firewall rules are applied to the traffic.</p>

<p>Application firewalls go one step further by analyzing the data being transmitted, which allows network traffic to be matched against firewall rules that are specific to individual services or applications. These are also known as proxy-based firewalls.</p>

<p>In addition to firewall software, which is available on all modern operating systems, firewall functionality can also be provided by hardware devices, such as routers or firewall appliances. Again, our discussion will be focused on <strong>stateful</strong> software firewalls that run on the servers that they are intended to protect.</p>

<h2 id="firewall-rules">Firewall Rules</h2>

<p>As mentioned above, network traffic that traverses a firewall is matched against rules to determine if it should be allowed through or not. An easy way to explain what firewall rules looks like is to show a few examples, so we'll do that now.</p>

<p>Suppose you have a server with this list of firewall rules that apply to incoming traffic:</p>

<ol>
<li>Accept new and established incoming traffic to the public network interface on port 80 and 443 (HTTP and HTTPS web traffic)</li>
<li>Drop incoming traffic from IP addresses of the non-technical employees in your office to port 22 (SSH)</li>
<li>Accept new and established incoming traffic from your office IP range to the private network interface on port 22 (SSH)</li>
</ol>

<p>Note that the first word in each of these examples is either "accept", "reject", or "drop". This specifies the action that the firewall should do in the event that a piece of network traffic matches a rule. <strong>Accept</strong> means to allow the traffic through, <strong>reject</strong> means to block the traffic but reply with an "unreachable" error, and <strong>drop</strong> means to block the traffic and send no reply. The rest of each rule consists of the condition that each packet is matched against.</p>

<p>As it turns out, network traffic is matched against a list of firewall rules in a sequence, or chain, from first to last. More specifically, once a rule is matched, the associated action is applied to the network traffic in question. In our example, if an accounting employee attempted to establish an SSH connection to the server they would be rejected based on rule 2, before rule 3 is even checked. A system administrator, however, would be accepted because they would match only rule 3.</p>

<h3 id="default-policy">Default Policy</h3>

<p>It is typical for a chain of firewall rules to not explicitly cover every possible condition. For this reason, firewall chains must always have a default policy specified, which consists only of an action (accept, reject, or drop).</p>

<p>Suppose the default policy for the example chain above was set to <strong>drop</strong>. If any computer outside of your office attempted to establish an SSH connection to the server, the traffic would be dropped because it does not match the conditions of any rules.</p>

<p>If the default policy were set to <strong>accept</strong>, anyone, except your own non-technical employees, would be able to establish a connection to any open service on your server. This would be an example of a very poorly configured firewall because it only keeps a subset of your employees out.</p>

<h2 id="incoming-and-outgoing-traffic">Incoming and Outgoing Traffic</h2>

<p>As network traffic, from the perspective of a server, can be either incoming or outgoing, a firewall maintains a distinct set of rules for either case. Traffic that originates elsewhere, incoming traffic, is treated differently than outgoing traffic that the server sends. It is typical for a server to allow most outgoing traffic because the server is usually, to itself, trustworthy. Still, the outgoing rule set can be used to prevent unwanted communication in the case that a server is compromised by an attacker or a malicious executable.</p>

<p>In order to maximize the security benefits of a firewall, you should identify all of the ways you want other systems to interact with your server, create rules that explicitly allow them, then drop all other traffic. Keep in mind that the appropriate outgoing rules must be in place so that a server will allow itself to send outgoing acknowledgements to any appropriate incoming connections. Also, as a server typically needs to initiate its own outgoing traffic for various reasons—for example, downloading updates or connecting to a database—it is important to include those cases in your outgoing rule set as well.</p>

<h3 id="writing-outgoing-rules">Writing Outgoing Rules</h3>

<p>Suppose our example firewall is set to <strong>drop</strong> outgoing traffic by default. This means our incoming <strong>accept</strong> rules would be useless without complementary outgoing rules.</p>

<p>To complement the example incoming firewall rules (1 and 3), from the <strong>Firewall Rules</strong> section, and allow proper communication on those addresses and ports to occur, we could use these outgoing firewall rules:</p>

<ol>
<li>Accept established outgoing traffic to the public network interface on port 80 and 443 (HTTP and HTTPS)</li>
<li>Accept established outgoing traffic to the private network interface on port 22 (SSH)</li>
</ol>

<p>Note that we don't need to explicitly write a rule for incoming traffic that is dropped (incoming rule 2) because the server doesn't need to establish or acknowledge that connection.</p>

<h2 id="firewall-software-and-tools">Firewall Software and Tools</h2>

<p>Now that we've gone over how firewalls work, let's take a look at common software packages that can help us set up an effective firewall. While there are many other firewall-related packages, these are effective and are the ones you will encounter the most.</p>

<h3 id="iptables">Iptables</h3>

<p>Iptables is a standard firewall included in most Linux distributions by default (a modern variant called nftables will begin to replace it). It is actually a front end to the kernel-level netfilter hooks that can manipulate the Linux network stack. It works by matching each packet that crosses the networking interface against a set of rules to decide what to do.</p>

<p>To learn how to implement a firewall with iptables, check out these links:</p>

<ul>
<li><a href="https://indiareads/community/tutorials/how-to-set-up-a-firewall-using-iptables-on-ubuntu-14-04">How To Set Up a Firewall Using IPTables on Ubuntu 14.04</a></li>
<li><a href="https://indiareads/community/tutorials/how-to-implement-a-basic-firewall-template-with-iptables-on-ubuntu-14-04">How To Implement a Basic Firewall Template with Iptables on Ubuntu 14.04</a></li>
<li><a href="https://indiareads/community/tutorials/how-to-set-up-an-iptables-firewall-to-protect-traffic-between-your-servers">How To Set Up an Iptables Firewall to Protect Traffic Between your Servers</a></li>
</ul>

<h3 id="ufw">UFW</h3>

<p>UFW, which stands for Uncomplicated Firewall, is an interface to iptables that is geared towards simplifying the process of configuring a firewall.</p>

<p>To learn more about using UFW, check out this tutorial: <a href="https://indiareads/community/tutorials/how-to-setup-a-firewall-with-ufw-on-an-ubuntu-and-debian-cloud-server">How To Setup a Firewall with UFW on an Ubuntu and Debian Cloud Server</a>.</p>

<h3 id="firewalld">FirewallD</h3>

<p>FirewallD is a complete firewall solution available by default on CentOS 7 servers. Incidentally, FirewallD uses iptables to configure netfilter.</p>

<p>To learn more about using FirewallD, check out this tutorial: <a href="https://indiareads/community/tutorials/how-to-configure-firewalld-to-protect-your-centos-7-server">How To Configure FirewallD to Protect Your CentOS 7 Server</a>.</p>

<p>If you're running CentOS 7 but prefer to use iptables, follow this tutorial: <a href="https://indiareads/community/tutorials/how-to-migrate-from-firewalld-to-iptables-on-centos-7">How To Migrate from FirewallD to Iptables on CentOS 7</a>.</p>

<h3 id="fail2ban">Fail2ban</h3>

<p>Fail2ban is an intrusion prevention software that can automatically configure your firewall to block brute force login attempts and DDOS attacks.</p>

<p>To learn more about Fail2ban, check out these links:</p>

<ul>
<li><a href="https://indiareads/community/tutorials/how-fail2ban-works-to-protect-services-on-a-linux-server">How Fail2ban Works to Protect Services on a Linux Server</a></li>
<li><a href="https://indiareads/community/tutorials/how-to-protect-ssh-with-fail2ban-on-ubuntu-14-04">How To Protect SSH with Fail2Ban on Ubuntu 14.04</a></li>
<li><a href="https://indiareads/community/tutorials/how-to-protect-an-nginx-server-with-fail2ban-on-ubuntu-14-04">How To Protect an Nginx Server with Fail2Ban on Ubuntu 14.04</a></li>
<li><a href="https://indiareads/community/tutorials/how-to-protect-an-apache-server-with-fail2ban-on-ubuntu-14-04">How To Protect an Apache Server with Fail2Ban on Ubuntu 14.04</a></li>
</ul>

<h2 id="conclusion">Conclusion</h2>

<p>Now that you understand how firewalls work, you should look into implementing a firewall that will improve your security of your server setup by using the tutorials above.</p>

<p>If you want to learn more about how firewalls work, check out these links:</p>

<ul>
<li><a href="https://indiareads/community/tutorials/how-the-iptables-firewall-works">How the Iptables Firewall Works</a></li>
<li><a href="https://indiareads/community/tutorials/how-to-choose-an-effective-firewall-policy-to-secure-your-servers">How To Choose an Effective Firewall Policy to Secure your Servers</a></li>
<li><a href="https://indiareads/community/tutorials/a-deep-dive-into-iptables-and-netfilter-architecture">A Deep Dive into Iptables and Netfilter Architecture</a></li>
</ul>

    