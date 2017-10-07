<link rel='stylesheet' href='digital_ocean.css'><br> <br> 
      <h3 id="introduction">Introduction</h3>

<p>In this tutorial, we will go over how to use Tinc, an open source Virtual Private Network (VPN) daemon, to create a secure VPN that your servers can communicate on as if they were on a local network. We will also demonstrate how to use Tinc to set up a secure tunnel into a private network. We will be using Ubuntu 14.04 servers, but the configurations can be adapted for use with any other OS.</p>

<p>A few of the features that Tinc has that makes it useful include encryption, optional compression, automatic mesh routing (VPN traffic is routed directly between the communicating servers, if possible), and easy expansion. These features differentiate Tinc from other VPN solutions such as OpenVPN, and make it a good solution for creating a VPN out of many small networks that are geographically distributed. Tinc is supported on many operating systems, including Linux, Windows, and Mac OS X.</p>

<p><span class="note"><strong>Note:</strong> If you want to set up a Tinc mesh VPN quickly and easily, check out this tutorial: <a href="https://indiareads/community/tutorials/how-to-use-ansible-and-tinc-vpn-to-secure-your-server-infrastructure">How To Use Ansible and Tinc VPN to Secure Your Server Infrastructure</a>.<br /></span></p>

<h2 id="prerequisites">Prerequisites</h2>

<p>To complete this tutorial, you will require root access on at least three Ubuntu 14.04 servers. Instructions to set up root access can be found here (steps 3 and 4): <a href="https://indiareads/community/tutorials/initial-server-setup-with-ubuntu-14-04">Initial Server Setup with Ubuntu 14.04</a>.</p>

<p>If you are planning on using this in your own environment, you will have to plan out how your servers need to access each other, and adapt the examples presented in this tutorial to your own needs. If you are adapting this to your own setup, be sure to substitute the highlighted values in the examples with your own values.</p>

<p>If you would like to follow this tutorial exactly, create two VPSs in the same datacenter, with private networking, and create another VPS in a separate datacenter. We will create two VPSs in the NYC2 datacenter and one in AMS2 datacenter with the following names:</p>

<ul>
<li><strong>externalnyc</strong>: All of the VPN nodes will connect to this server, and the connection must be maintained for proper VPN functionality. Additional servers can be configured in a similarly to this one to provide redundancy, if desired.</li>
<li><strong>internalnyc</strong>: Connects to <em>externalnyc</em> VPN node using its <em>private</em> network interface</li>
<li><strong>ams1</strong>: Connects to <em>externalnyc</em> VPN node over the public Internet</li>
</ul>

<h2 id="our-goal">Our Goal</h2>

<p>Here is a diagram of the VPN that we want to set up (described in Prerequisites):</p>

<p><img src="https://assets.digitalocean.com/articles/tinc/tinc.png" alt="Tinc VPN Setup" /></p>

<p>The green represents our VPN, the gray represents the public Internet, and the orange represents the private network. All three servers can communicate on the VPN, even though the private network is inaccessible to ams1.</p>

<p>Let's get started by installing Tinc!</p>

<h2 id="install-tinc">Install Tinc</h2>

<p>On each VPS that you want to join the private network, install Tinc. Let's start by updating apt:</p>
<pre class="code-pre "><code langs="">sudo apt-get update
</code></pre>
<p>Then install Tinc via apt:</p>
<pre class="code-pre "><code langs="">sudo apt-get install tinc
</code></pre>
<p>Now that Tinc is installed, let's look at the Tinc configuration.</p>

<h2 id="tinc-configuration">Tinc Configuration</h2>

<p>Tinc uses a "netname" to distinguish one Tinc VPN from another (in case of multiple VPNs), and it is recommended to use a netname even if you are only planning on configuring one VPN. We will call our VPN "<span class="highlight">netname</span>" for simplicity.</p>

<p>Every server that will be part of our VPN requires the following three configuration components:</p>

<ul>
<li>Configuration files: tinc.conf, tinc-up, and tinc-down, for example</li>
<li>Public/private key pairs: For encryption and node authentication</li>
<li>Host configuration files: Which contain public keys and other VPN configuration</li>
</ul>

<p>Let's start by configuring our <em>externalnyc</em> node.</p>

<h2 id="configure-externalnyc">Configure externalnyc</h2>

<p>On <strong>externalnyc</strong>, create the configuration directory structure for our VPN called "netname":</p>
<pre class="code-pre "><code langs="">sudo mkdir -p /etc/tinc/<span class="highlight">netname</span>/hosts
</code></pre>
<p>Now open tinc.conf for editing:</p>
<pre class="code-pre "><code langs="">sudo vi /etc/tinc/<span class="highlight">netname</span>/tinc.conf
</code></pre>
<p>Now add the following lines:</p>
<pre class="code-pre "><code langs="">Name = <span class="highlight">externalnyc</span>
AddressFamily = ipv4
Interface = tun0
</code></pre>
<p>This simply configures a node called <span class="highlight">externalnyc</span>, with a network interface that will use IPv4 called "tun0". Save and quit.</p>

<p>Next, let's create an <em>externalnyc</em> hosts configuration file:</p>
<pre class="code-pre "><code langs="">sudo vi /etc/tinc/<span class="highlight">netname</span>/hosts/<span class="highlight">externalnyc</span>
</code></pre>
<p>Add the following lines to it (substitute the public IP address of your VPS here):</p>
<pre class="code-pre "><code langs="">Address = <span class="highlight">externalnyc_public_IP</span>
Subnet = 10.0.0.1/32
</code></pre>
<p>Ultimately, this file will be used on other servers to communicate with this server. The address specifies how other nodes will connect to this server, and the subnet specifies which subnet this daemon will serve. Save and quit.</p>

<p>Now generate the public/private keypair for this host with the following command:</p>
<pre class="code-pre "><code langs="">sudo tincd -n <span class="highlight">netname</span> -K4096
</code></pre>
<p>This creates the private key (/etc/tinc/<em>netname</em>/rsa_key.priv) and appends the public key to the <em>externalnyc</em> hosts configuration file that we recently created (/etc/tinc/netname/hosts/<em>externalnyc</em>).</p>

<p>Now we must create <code>tinc-up</code>, the script that will run whenever our <em>netname</em> VPN is started. Open the file for editing now:</p>
<pre class="code-pre "><code langs="">sudo vi /etc/tinc/<span class="highlight">netname</span>/tinc-up
</code></pre>
<p>Add the following lines:</p>
<pre class="code-pre "><code langs="">#!/bin/sh
ifconfig $INTERFACE 10.0.0.1 netmask 255.255.255.0
</code></pre>
<p>When we start our VPN, this script will run to create the network interface that our VPN will use. On the VPN, this server will have an IP address of 10.0.0.1.</p>

<p>Let's also create a script to remove network interface when our VPN is stopped:</p>
<pre class="code-pre "><code langs="">sudo vi /etc/tinc/<span class="highlight">netname</span>/tinc-down
</code></pre>
<p>Add the following lines:</p>
<pre class="code-pre "><code langs="">#!/bin/sh
ifconfig $INTERFACE down
</code></pre>
<p>Save and quit.</p>

<p>Lastly, make tinc network scripts executable:</p>
<pre class="code-pre "><code langs="">sudo chmod 755 /etc/tinc/<span class="highlight">netname</span>/tinc-*
</code></pre>
<p>Save and quit.</p>

<p>Let's move on to our other nodes.</p>

<h2 id="configure-internalnyc-and-ams1">Configure internalnyc and ams1</h2>

<p>These steps are required on both <em>internalnyc</em> and <em>ams1</em>, with slight variations that will be noted.</p>

<p>On <strong>internalnyc</strong> and <strong>ams1</strong>, create the configuration directory structure for our VPN called "netname" and edit the Tinc configuration file:</p>
<pre class="code-pre "><code langs="">sudo mkdir -p /etc/tinc/<span class="highlight">netname</span>/hosts
sudo vi /etc/tinc/<span class="highlight">netname</span>/tinc.conf
</code></pre>
<p>Add the following lines (substitute the name with the node name):</p>
<pre class="code-pre "><code langs="">Name = <span class="highlight">node_name</span>
AddressFamily = ipv4
Interface = tun0
ConnectTo = <span class="highlight">externalnyc</span>
</code></pre>
<p>These nodes are configured to attempt to connect to "externalnyc" (the node we created prior to this). Save and quit.</p>

<p>Next, let's create the hosts configuration file:</p>
<pre class="code-pre "><code langs="">sudo vi /etc/tinc/netname/hosts/<span class="highlight">node_name</span>
</code></pre>
<p>For <strong>internalnyc</strong>, add this line:</p>
<pre class="code-pre "><code langs="">Subnet = 10.0.0.<span class="highlight">2</span>/32
</code></pre>
<p>For <strong>ams1</strong>, add this line:</p>
<pre class="code-pre "><code langs="">Subnet = 10.0.0.<span class="highlight">3</span>/32
</code></pre>
<p>Note that the numbers differ. Save and quit.</p>

<p>Next, generate the keypairs:</p>
<pre class="code-pre "><code langs="">sudo tincd -n netname -K4096
</code></pre>
<p>And create the network interface start script:</p>
<pre class="code-pre "><code langs="">sudo vi /etc/tinc/netname/tinc-up
</code></pre>
<p>For <strong>internalnyc</strong>, add this line:</p>
<pre class="code-pre "><code langs="">ifconfig $INTERFACE 10.0.0.<span class="highlight">2</span> netmask 255.255.255.0
</code></pre>
<p>For <strong>ams1</strong>, add this line:</p>
<pre class="code-pre "><code langs="">ifconfig $INTERFACE 10.0.0.<span class="highlight">3</span> netmask 255.255.255.0
</code></pre>
<p>These IP addresses are how these nodes will be accessed on the VPN. Save and quit.</p>

<p>Now create the network interface stop script:</p>
<pre class="code-pre "><code langs="">sudo vi /etc/tinc/netname/tinc-down
</code></pre>
<p>And add this line:</p>
<pre class="code-pre "><code langs="">ifconfig $INTERFACE down
</code></pre>
<p>Save and quit.</p>

<p>Lastly, make tinc network scripts executable:</p>
<pre class="code-pre "><code langs="">sudo chmod 755 /etc/tinc/<span class="highlight">netname</span>/tinc-*
</code></pre>
<p>Save and quit.</p>

<p>Now we must distribute the hosts configuration files to each node.</p>

<h2 id="distribute-the-keys">Distribute the Keys</h2>

<p>If you happen to use a configuration management system, here is a good application. Minimally, each node that wants communicate directly with another node must have exchanged public keys, which are inside of the hosts configuration files. In our case, for example, only <em>externalnyc</em> needs to exchange public keys with the other nodes. It is easier to manage if you just copy each public key to all members of the node. Note that you will want to change the "Address" value in <em>externalnyc</em>'s hosts configuration file to its private IP address when it is copied to <em>internalnyc</em>, so that connection is established over the private network.</p>

<p>Because our VPN is called "netname", here is the location of the hosts configuration files: <code>/etc/tinc/<span class="highlight">netname</span>/hosts</code></p>

<h3 id="exchange-keys-between-externalnyc-and-internalnyc">Exchange Keys Between externalnyc and internalnyc</h3>

<p>On <strong>internalnyc</strong>, copy its hosts configuration file to <em>externalnyc</em>:</p>
<pre class="code-pre "><code langs="">scp /etc/tinc/netname/hosts/<span class="highlight">internalnyc</span> <span class="highlight">user</span>@<span class="highlight">externalnyc_private_IP</span>:/tmp
</code></pre>
<p>Then on <strong>externalnyc</strong>, copy the <em>internalnyc</em>'s file into the appropriate location:</p>
<pre class="code-pre "><code langs="">cd /etc/tinc/netname/hosts; sudo cp /tmp/<span class="highlight">internalnyc</span> .
</code></pre>
<p>Then on <strong>externalnyc</strong> again, copy its hosts configuration file to <em>internalnyc</em>:</p>
<pre class="code-pre "><code langs="">scp /etc/tinc/netname/hosts/<span class="highlight">externalnyc</span> <span class="highlight">user</span>@<span class="highlight">internalnyc_private_IP</span>:/tmp
</code></pre>
<p>On <strong>internalnyc</strong>, copy <em>externalnyc</em>'s file to the appropriate location:</p>
<pre class="code-pre "><code langs="">cd /etc/tinc/netname/hosts; sudo cp /tmp/<span class="highlight">externalnyc</span> .
</code></pre>
<p>On <strong>internalnyc</strong>, let's edit <em>externalnyc</em>'s hosts configuration file so the "Address" field is set to <em>externalnyc</em>'s private IP address (so internalnyc will connect  to the VPN via the private network). Edit <em>externalnyc</em>'s hosts configuration file:</p>
<pre class="code-pre "><code langs="">sudo vi /etc/tinc/netname/hosts/externalnyc
</code></pre>
<p>Change the "Address" value to <em>externalnyc</em>'s private IP address:</p>
<pre class="code-pre "><code langs="">Address = <span class="highlight">externalnyc_private_IP</span>
</code></pre>
<p>Save and quit. Now let's move on to our remaining node, ams1.</p>

<h3 id="exchange-keys-between-externalnyc-and-ams1">Exchange Keys Between externalnyc and ams1</h3>

<p>On <strong>ams1</strong>, copy its hosts configuration file to <em>externalnyc</em>:</p>
<pre class="code-pre "><code langs="">scp /etc/tinc/netname/hosts/<span class="highlight">ams1</span> <span class="highlight">user</span>@<span class="highlight">externalnyc_public_IP</span>:/tmp
</code></pre>
<p>Then on <strong>externalnyc</strong>, copy the <em>ams1</em>'s file into the appropriate location:</p>
<pre class="code-pre "><code langs="">cd /etc/tinc/netname/hosts; sudo cp /tmp/<span class="highlight">ams1</span> .
</code></pre>
<p>Then on <strong>externalnyc</strong> again, copy its hosts configuration file to <em>ams1</em>:</p>
<pre class="code-pre "><code langs="">scp /etc/tinc/netname/hosts/<span class="highlight">externalnyc</span> <span class="highlight">user</span>@ams1_public_IP:/tmp
</code></pre>
<p>On <strong>ams1</strong>, copy <em>externalnyc</em>'s file to the appropriate location:</p>
<pre class="code-pre "><code langs="">cd /etc/tinc/netname/hosts; sudo cp /tmp/<span class="highlight">externalnyc</span> .
</code></pre>
<h3 id="exchange-keys-between-additional-nodes">Exchange Keys Between Additional Nodes</h3>

<p>If you are creating a larger VPN, now is a good time to exchange the keys between those other nodes. Remember that if you want two nodes to directly communicate with each other (without a forwarding server between), they need to have exchanged their keys/hosts configuration files, and they need to be able to access each other's real network interfaces. Also, it is fine to just copy each hosts configuration to every node in the VPN.</p>

<h2 id="test-our-configuration">Test Our Configuration</h2>

<p>On <strong>each</strong> node, starting with <em>externalnyc</em>, start Tinc in debug mode like so (netname is the name of our VPN):</p>
<pre class="code-pre "><code langs="">sudo tincd -n <span class="highlight">netname</span> -D -d3
</code></pre>
<p>After starting the daemon on each node, you should see output with the names of each node as they connect to externalnyc. Now let's test the connection over the VPN.</p>

<p>In a separate window, on <strong>ams1</strong>, ping internalnyc's VPN IP address (which we assigned to 10.0.0.2, earlier):</p>
<pre class="code-pre "><code langs="">ping <span class="highlight">10.0.0.2</span>
</code></pre>
<p>The ping should work fine, and you should see some debug output in the other windows about the connection on the VPN. This indicates that ams1 is able to communicate over the VPN through externalnyc to internalnyc. Press CTRL-C to quit pinging.</p>

<p>You may also use the VPN interfaces to do any other network communication, like application connections, copying files, and SSH.</p>

<p>On each Tinc daemon debug window, quit the daemon by pressing CTRL-\.</p>

<p><strong>Note</strong>: If the connections aren't working, ensure that your firewall is not blocking the connections or forwarding.</p>

<h2 id="configure-tinc-to-startup-on-boot">Configure Tinc To Startup on Boot</h2>

<p>Before the Tinc init script will function properly, we have to put our VPN's name into the <code>nets.boot</code> configuration file.</p>

<p>On <strong>each node</strong>, edit nets.boot:</p>
<pre class="code-pre "><code langs="">sudo vi /etc/tinc/nets.boot
</code></pre>
<p>Add the name of your VPN(s) into this file. Ours is "netname":</p>
<pre class="code-pre "><code langs=""># This file contains all names of the networks to be started on system startup.
netname
</code></pre>
<p>Save and quit. Tinc is now configured to start on boot, and it can be controlled via the <code>service</code> command. If you would like to start it now run the following command on each of your nodes:</p>
<pre class="code-pre "><code langs="">sudo service tinc start
</code></pre>
<p>Congrats! Your Tinc VPN is set up.</p>

<h2 id="conclusion">Conclusion</h2>

<p>Now that you have gone through this tutorial, you should have a good foundation to build out your VPN to meet your needs. Tinc is very flexible, and any node can be configured to connect to any other node (that it can access over the network) so it can act as a mesh VPN, not relying on a single node.</p>

<p>Good luck!</p>

    