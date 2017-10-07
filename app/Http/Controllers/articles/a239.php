<link rel='stylesheet' href='digital_ocean.css'><br><img src=https://community-cdn-digitalocean-com.global.ssl.fastly.net/assets/tutorials/images/large/high_availability_tw.jpg?1457123534/> <br> 
      <h3 id="introduction">Introduction</h3>

<p>This tutorial will demonstrate how you can use Corosync and Pacemaker with a Floating IP to create a high availability (HA) server infrastructure on IndiaReads.</p>

<p>Corosync is an open source program that provides cluster membership and messaging capabilities, often referred to as the <strong>messaging</strong> layer, to client servers. Pacemaker is an open source cluster resource manager (CRM), a system that coordinates resources and services that are managed and made highly available by a cluster. In essence, Corosync enables servers to communicate as a cluster, while Pacemaker provides the ability to control how the cluster behaves.</p>

<h2 id="goal">Goal</h2>

<p>When completed, the HA setup will consist of two Ubuntu 14.04 servers in an active/passive configuration. This will be accomplished by pointing a Floating IP, which is how your users will access your web service, to point to the primary (active) server unless a failure is detected. In the event that Pacemaker detects that the primary server is unavailable, the secondary (passive) server will automatically run a script that will reassign the Floating IP to itself via the IndiaReads API. Thus, subsequent network traffic to the Floating IP will be directed to your secondary server, which will act as the active server and process the incoming traffic.</p>

<p>This diagram demonstrates the concept of the described setup:</p>

<p><img src="https://assets.digitalocean.com/articles/high_availability/ha-diagram-animated.gif" alt="Active/passive Diagram" /></p>

<p><span class="note"><strong>Note:</strong> This tutorial only covers setting up active/passive high availability at the gateway level. That is, it includes the Floating IP, and the <em>load balancer</em> servers—Primary and Secondary. Furthermore, for demonstration purposes, instead of configuring reverse-proxy load balancers on each server, we will simply configure them to respond with their respective hostname and public IP address.<br /></span></p>

<p>To achieve this goal, we will follow these steps:</p>

<ul>
<li>Create 2 Droplets that will receive traffic</li>
<li>Create Floating IP and assign it to one of the Droplets</li>
<li>Install and configure Corosync</li>
<li>Install and configure Pacemaker</li>
<li>Configure Floating IP Reassignment Cluster Resource</li>
<li>Test failover</li>
<li>Configure Nginx Cluster Resource</li>
</ul>

<h2 id="prerequisites">Prerequisites</h2>

<p>In order to automate the Floating IP reassignment, we must use the IndiaReads API. This means that you need to generate a Personal Access Token (PAT), which is an API token that can be used to authenticate to your IndiaReads account, with <em>read</em> and <em>write</em> access by following the <a href="https://indiareads/community/tutorials/how-to-use-the-digitalocean-api-v2#how-to-generate-a-personal-access-token">How To Generate a Personal Access Token</a> section of the API tutorial. Your PAT will be used in a script that will be added to both servers in your cluster, so be sure to keep it somewhere safe—as it allows full access to your IndiaReads account—for reference.</p>

<p>In addition to the API, this tutorial utilizes the following IndiaReads features:</p>

<ul>
<li><a href="https://indiareads/community/tutorials/how-to-use-floating-ips-on-digitalocean">Floating IPs</a></li>
<li><a href="https://indiareads/community/tutorials/an-introduction-to-droplet-metadata">Metadata</a></li>
<li><a href="https://indiareads/community/tutorials/an-introduction-to-cloud-config-scripting">User Data (Cloud-Config scripts)</a></li>
</ul>

<p>Please read the linked tutorials if you want to learn more about them.</p>

<h2 id="create-droplets">Create Droplets</h2>

<p>The first step is to create two Ubuntu Droplets, with Private Networking enabled, in the same datacenter, which will act as the primary and secondary servers described above. In our example setup, we will name them "primary" and "secondary" for easy reference. We will install Nginx on both Droplets and replace their index pages with information that uniquely identifies them. This will allow us a simple way to demonstrate that the HA setup is working. For a real setup, your servers should run the web server or load balancer of your choice, such as Nginx or HAProxy.</p>

<p>Create two Ubuntu 14.04 Droplets, <strong>primary</strong> and <strong>secondary</strong>. If you want to follow the example setup, use this bash script as the user data:</p>
<div class="code-label " title="Example User Data">Example User Data</div><pre class="code-pre "><code langs="">#!/bin/bash

apt-get -y update
apt-get -y install nginx
export HOSTNAME=$(curl -s http://169.254.169.254/metadata/v1/hostname)
export PUBLIC_IPV4=$(curl -s http://169.254.169.254/metadata/v1/interfaces/public/0/ipv4/address)
echo Droplet: $HOSTNAME, IP Address: $PUBLIC_IPV4 > /usr/share/nginx/html/index.html
</code></pre>
<p>This user data will install Nginx and replace the contents of <code>index.html</code> with the droplet's hostname and IP address (by referencing the Metadata service). Accessing either Droplet via its public IP address will show a basic webpage with the Droplet hostname and IP address, which will be useful for testing which Droplet the Floating IP is pointing to at any given moment.</p>

<h2 id="create-a-floating-ip">Create a Floating IP</h2>

<p>In the IndiaReads Control Panel, click <strong>Networking</strong>, in the top menu, then <strong>Floating IPs</strong> in the side menu.</p>

<p><img src="https://assets.digitalocean.com/site/ControlPanel/fip_no_floating_ips.png" alt="No Floating IPs" /></p>

<p>Assign a Floating IP to your <strong>primary</strong> Droplet, then click the <strong>Assign Floating IP</strong> button.</p>

<p>After the Floating IP has been assigned, take a note of its IP address. Check that you can reach the Droplet that it was assigned to by visiting the Floating IP address in a web browser.</p>
<pre class="code-pre "><code langs="">http://<span class="highlight">your_floating_ip</span>
</code></pre>
<p>You should see the index page of your primary Droplet.</p>

<h2 id="configure-dns-optional">Configure DNS (Optional)</h2>

<p>If you want to be able to access your HA setup via a domain name, go ahead and create an <strong>A record</strong> in your DNS that points your domain to your Floating IP address. If your domain is using IndiaReads's nameservers, follow <a href="https://indiareads/community/tutorials/how-to-set-up-a-host-name-with-digitalocean#step-three%E2%80%94configure-your-domain">step three</a> of the How To Set Up a Host Name with IndiaReads tutorial. Once that propagates, you may access your active server via the domain name.</p>

<p>The example domain name we'll use is <code>example.com</code>. If you don't have a domain name to use right now, you will use the Floating IP address to access your setup instead.</p>

<h2 id="configure-time-synchronization">Configure Time Synchronization</h2>

<p>Whenever you have multiple servers communicating with each other, especially with clustering software, it is important to ensure their clocks are synchronized. We'll use NTP (Network Time Protocol) to synchronize our servers.</p>

<p>On <strong>both servers</strong>, use this command to open a time zone selector:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo dpkg-reconfigure tzdata
</li></ul></code></pre>
<p>Select your desired time zone. For example, we'll choose <code>America/New_York</code>.</p>

<p>Next, update apt-get:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo apt-get update
</li></ul></code></pre>
<p>Then install the <code>ntp</code> package with this command;</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo apt-get -y install ntp
</li></ul></code></pre>
<p>Your server clocks should now be synchronized using NTP. To learn more about NTP, check out this tutorial: <a href="https://indiareads/community/tutorials/additional-recommended-steps-for-new-ubuntu-14-04-servers#configure-timezones-and-network-time-protocol-synchronization">Configure Timezones and Network Time Protocol Synchronization</a>.</p>

<h2 id="configure-firewall">Configure Firewall</h2>

<p>Corosync uses UDP transport between ports <code>5404</code> and <code>5406</code>. If you are running a firewall, ensure that communication on those ports are allowed between the servers.</p>

<p>For example, if you're using <code>iptables</code>, you could allow traffic on these ports and <code>eth1</code> (the private network interface) with these commands:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo iptables -A INPUT  -i eth1 -p udp -m multiport --dports 5404,5405,5406 -m conntrack --ctstate NEW,ESTABLISHED -j ACCEPT
</li><li class="line" prefix="$">sudo iptables -A OUTPUT  -o eth1 -p udp -m multiport --sports 5404,5405,5406 -m conntrack --ctstate ESTABLISHED -j ACCEPT
</li></ul></code></pre>
<p>It is advisable to use firewall rules that are more restrictive than the provided example.</p>

<h2 id="install-corosync-and-pacemaker">Install Corosync and Pacemaker</h2>

<p>On <strong>both servers</strong>, install Corosync and Pacemaker using apt-get:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo apt-get install pacemaker
</li></ul></code></pre>
<p>Note that Corosync is installed as a dependency of the Pacemaker package.</p>

<p>Corosync and Pacemaker are now installed but they need to be configured before they will do anything useful.</p>

<h2 id="configure-corosync">Configure Corosync</h2>

<p>Corosync must be configured so that our servers can communicate as a cluster.</p>

<h3 id="create-cluster-authorization-key">Create Cluster Authorization Key</h3>

<p>In order to allow nodes to join a cluster, Corosync requires that each node possesses an identical cluster authorization key.</p>

<p>On the <strong>primary</strong> server, install the <code>haveged</code> package:</p>
<pre class="code-pre custom_prefix"><code langs=""><ul class="prefixed"><li class="line" prefix="primary$">sudo apt-get install haveged
</li></ul></code></pre>
<p>This software package allows us to easily increase the amount of entropy on our server, which is required by the <code>corosync-keygen</code> script.</p>

<p>On the <strong>primary</strong> server, run the <code>corosync-keygen</code> script:</p>
<pre class="code-pre custom_prefix"><code langs=""><ul class="prefixed"><li class="line" prefix="primary$">sudo corosync-keygen
</li></ul></code></pre>
<p>This will generate a 128-byte cluster authorization key, and write it to <code>/etc/corosync/authkey</code>.</p>

<p>Now that we no longer need the <code>haveged</code> package, let's remove it from the <strong>primary</strong> server:</p>
<pre class="code-pre custom_prefix"><code langs=""><ul class="prefixed"><li class="line" prefix="primary$">sudo apt-get remove --purge haveged
</li><li class="line" prefix="primary$">sudo apt-get clean
</li></ul></code></pre>
<p>On the <strong>primary</strong> server, copy the <code>authkey</code> to the secondary server:</p>
<pre class="code-pre custom_prefix"><code langs=""><ul class="prefixed"><li class="line" prefix="primary$">sudo scp /etc/corosync/authkey <span class="highlight">username</span>@<span class="highlight">secondary_ip</span>:/tmp
</li></ul></code></pre>
<p>On the <strong>secondary</strong> server, move the <code>authkey</code> file to the proper location, and restrict its permissions to root:</p>
<pre class="code-pre custom_prefix"><code langs=""><ul class="prefixed"><li class="line" prefix="secondary$">sudo mv /tmp/authkey /etc/corosync
</li><li class="line" prefix="secondary$">sudo chown root: /etc/corosync/authkey
</li><li class="line" prefix="secondary$">sudo chmod 400 /etc/corosync/authkey
</li></ul></code></pre>
<p>Now both servers should have an identical authorization key in the <code>/etc/corosync/authkey</code> file.</p>

<h3 id="configure-corosync-cluster">Configure Corosync Cluster</h3>

<p>In order to get our desired cluster up and running, we must set up these </p>

<p>On <strong>both servers</strong>, open the <code>corosync.conf</code> file for editing in your favorite editor (we'll use <code>vi</code>):</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo vi /etc/corosync/corosync.conf
</li></ul></code></pre>
<p>Here is a Corosync configuration file that will allow your servers to communicate as a cluster. Be sure to replace the highlighted parts with the appropriate values. <code>bindnetaddr</code> should be set to the private IP address of the server you are currently working on. The two other highlighted items should be set to the indicated server's private IP address. With the exception of the <code>bindnetaddr</code>, the file should be identical on both servers.</p>

<p>Replace the contents of <code>corosync.conf</code> with this configuration, with the changes that are specific to your environment:</p>
<div class="code-label " title="/etc/corosync/corosync.conf">/etc/corosync/corosync.conf</div><pre class="code-pre line_numbers"><code langs=""><ul class="prefixed"><li class="line" prefix="1">totem {
</li><li class="line" prefix="2">  version: 2
</li><li class="line" prefix="3">  cluster_name: lbcluster
</li><li class="line" prefix="4">  transport: udpu
</li><li class="line" prefix="5">  interface {
</li><li class="line" prefix="6">    ringnumber: 0
</li><li class="line" prefix="7">    bindnetaddr: <span class="highlight">server_private_IP_address</span>
</li><li class="line" prefix="8">    broadcast: yes
</li><li class="line" prefix="9">    mcastport: 5405
</li><li class="line" prefix="10">  }
</li><li class="line" prefix="11">}
</li><li class="line" prefix="12">
</li><li class="line" prefix="13">quorum {
</li><li class="line" prefix="14">  provider: corosync_votequorum
</li><li class="line" prefix="15">  two_node: 1
</li><li class="line" prefix="16">}
</li><li class="line" prefix="17">
</li><li class="line" prefix="18">nodelist {
</li><li class="line" prefix="19">  node {
</li><li class="line" prefix="20">    ring0_addr: <span class="highlight">primary_private_IP_address</span>
</li><li class="line" prefix="21">    name: primary
</li><li class="line" prefix="22">    nodeid: 1
</li><li class="line" prefix="23">  }
</li><li class="line" prefix="24">  node {
</li><li class="line" prefix="25">    ring0_addr: <span class="highlight">secondary_private_IP_address</span>
</li><li class="line" prefix="26">    name: secondary
</li><li class="line" prefix="27">    nodeid: 2
</li><li class="line" prefix="28">  }
</li><li class="line" prefix="29">}
</li><li class="line" prefix="30">
</li><li class="line" prefix="31">logging {
</li><li class="line" prefix="32">  to_logfile: yes
</li><li class="line" prefix="33">  logfile: /var/log/corosync/corosync.log
</li><li class="line" prefix="34">  to_syslog: yes
</li><li class="line" prefix="35">  timestamp: on
</li><li class="line" prefix="36">}
</li></ul></code></pre>
<p>The <strong>totem</strong> section (lines 1-11), which refers to the Totem protocol that Corosync uses for cluster membership, specifies how the cluster members should communicate with each other. In our setup, the important settings include <code>transport: udpu</code> (specifies unicast mode) and <code>bindnetaddr</code> (specifies which network address Corosync should bind to).</p>

<p>The <strong>quorum</strong> section (lines 13-16) specifies that this is a two-node cluster, so only a single node is required for quorum (<code>two_node: 1</code>). This is a workaround of the fact that achieving a quorum requires at least three nodes in a cluster. This setting will allow our two-node cluster to elect a coordinator (DC), which is the node that controls the cluster at any given time.</p>

<p>The <strong>nodelist</strong> section (lines 18-29) specifies each node in the cluster, and how each node can be reached. Here, we configure both our primary and secondary nodes, and specify that they can be reached via their respective private IP addresses.</p>

<p>The <strong>logging</strong> section (lines 31-36) specifies that the Corosync logs should be written to <code>/var/log/corosync/corosync.log</code>. If you run into any problems with the rest of this tutorial, be sure to look here while you troubleshoot.</p>

<p>Save and exit.</p>

<p>Next, we need to configure Corosync to allow the Pacemaker service.</p>

<p>On <strong>both servers</strong>, create the <code>pcmk</code> file in the Corosync service directory with an editor. We'll use <code>vi</code>:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo vi /etc/corosync/service.d/pcmk
</li></ul></code></pre>
<p>Then add the Pacemaker service:</p>
<pre class="code-pre "><code langs="">service {
  name: pacemaker
  ver: 1
}
</code></pre>
<p>Save and exit. This will be included in the Corosync configuration, and allows Pacemaker to use Corosync to communicate with our servers.</p>

<p>By default, the Corosync service is disabled. On <strong>both servers</strong>, change that by editing <code>/etc/default/corosync</code>:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo vi /etc/default/corosync
</li></ul></code></pre>
<p>Change the value of <code>START</code> to <code>yes</code>:</p>
<div class="code-label " title="/etc/default/corosync">/etc/default/corosync</div><pre class="code-pre "><code langs="">START=<span class="highlight">yes</span>
</code></pre>
<p>Save and exit. Now we can start the Corosync service.</p>

<p>On <strong>both</strong> servers, start Corosync with this command:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo service corosync start
</li></ul></code></pre>
<p>Once Corosync is running on both servers, they should be clustered together. We can verify this by running this command:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo corosync-cmapctl | grep members
</li></ul></code></pre>
<p>The output should look something like this, which indicates that the primary (node 1) and secondary (node 2) have joined the cluster:</p>
<pre class="code-pre "><code langs=""><div class="secondary-code-label " title="corosync-cmapctl output:">corosync-cmapctl output:</div>runtime.totem.pg.mrp.srp.members.1.config_version (u64) = 0
runtime.totem.pg.mrp.srp.members.1.ip (str) = r(0) ip(<span class="highlight">primary_private_IP_address</span>)
runtime.totem.pg.mrp.srp.members.1.join_count (u32) = 1
runtime.totem.pg.mrp.srp.members.1.status (str) = joined
runtime.totem.pg.mrp.srp.members.2.config_version (u64) = 0
runtime.totem.pg.mrp.srp.members.2.ip (str) = r(0) ip(<span class="highlight">secondary_private_IP_address</span>)
runtime.totem.pg.mrp.srp.members.2.join_count (u32) = 1
runtime.totem.pg.mrp.srp.members.2.status (str) = joined
</code></pre>
<p>Now that you have Corosync set up properly, let's move onto configuring Pacemaker.</p>

<h2 id="start-and-configure-pacemaker">Start and Configure Pacemaker</h2>

<p>Pacemaker, which depends on the messaging capabilities of Corosync, is now ready to be started and to have its basic properties configured.</p>

<h3 id="enable-and-start-pacemaker">Enable and Start Pacemaker</h3>

<p>The Pacemaker service requires Corosync to be running, so it is disabled by default.</p>

<p>On <strong>both servers</strong>, enable Pacemaker to start on system boot with this command:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo update-rc.d pacemaker defaults 20 01
</li></ul></code></pre>
<p>With the prior command, we set Pacemaker's start priority to <code>20</code>. It is important to specify a start priority that is higher than Corosync's (which is <code>19</code> by default), so that Pacemaker starts after Corosync.</p>

<p>Now let's start Pacemaker:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo service pacemaker start
</li></ul></code></pre>
<p>To interact with Pacemaker, we will use the <code>crm</code> utility.</p>

<p>Check Pacemaker with <code>crm</code>:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo crm status
</li></ul></code></pre>
<p>This should output something like this (if not, wait for 30 seconds, then run the command again):</p>
<pre class="code-pre "><code langs=""><div class="secondary-code-label " title="crm status:">crm status:</div>Last updated: Fri Oct 16 14:38:36 2015
Last change: Fri Oct 16 14:36:01 2015 via crmd on primary
Stack: corosync
Current DC: primary (1) - partition with quorum
Version: 1.1.10-42f2063
2 Nodes configured
0 Resources configured


Online: [ primary secondary ]
</code></pre>
<p>There are a few things to note about this output. First, <strong>Current DC</strong> (Designated Coordinator) should be set to either <code>primary (1)</code> or <code>secondary (2)</code>. Second, there should be <strong>2 Nodes configured</strong> and <strong>0 Resources configured</strong>. Third, both nodes should be marked as <strong>online</strong>. If they are marked as <strong>offline</strong>, try waiting 30 seconds and check the status again to see if it corrects itself.</p>

<p>From this point on, you may want to run the interactive CRM monitor in another SSH window (connected to either cluster node). This will give you real-time updates of the status of each node, and where each resource is running:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo crm_mon
</li></ul></code></pre>
<p>The output of this command looks identical to the output of <code>crm status</code> except it runs continuously. If you want to quit, press <code>Ctrl-C</code>.</p>

<h3 id="configure-cluster-properties">Configure Cluster Properties</h3>

<p>Now we're ready to configure the basic properties of Pacemaker. Note that all Pacemaker (<code>crm</code>) commands can be run from either node server, as it automatically synchronizes all cluster-related changes across all member nodes.</p>

<p>For our desired setup, we want to disable STONITH—a mode that many clusters use to remove faulty nodes—because we are setting up a two-node cluster. To do so, run this command on either server:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo crm configure property stonith-enabled=false
</li></ul></code></pre>
<p>We also want to disable quorum-related messages in the logs:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo crm configure property no-quorum-policy=ignore
</li></ul></code></pre>
<p>Again, this setting only applies to 2-node clusters.</p>

<p>If you want to verify your Pacemaker configuration, run this command:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo crm configure show
</li></ul></code></pre>
<p>This will display all of your active Pacemaker settings. Currently, this will only include two nodes, and the STONITH and quorum properties you just set.</p>

<h2 id="create-floating-ip-reassignment-resource-agent">Create Floating IP Reassignment Resource Agent</h2>

<p>Now that Pacemaker is running and configured, we need to add resources for it to manage. As mentioned in the introduction, resources are services that the cluster is responsible for making highly available. In Pacemaker, adding a resource requires the use of a <strong>resource agent</strong>, which act as the interface to the service that will be managed. Pacemaker ships with several resource agents for common services, and allows custom resource agents to be added.</p>

<p>In our setup, we want to make sure that the service provided by our web servers, <strong>primary</strong> and <strong>secondary</strong>, is highly available in an active/passive setup, which means that we need a way to ensure that our Floating IP is always pointing to server that is available. To enable this, we need to set up a <strong>resource agent</strong> that each node can run to determine if it owns the Floating IP and, if necessary, run a script to point the Floating IP to itself. We'll refer to the resource agent as "FloatIP OCF", and the Floating IP reassignment script as <code>assign-ip</code>. Once we have the FloatIP OCF resource agent installed, we can define the resource itself, which we'll refer to as <code>FloatIP</code>.</p>

<h3 id="download-assign-ip-script">Download assign-ip Script</h3>

<p>As we just mentioned, we need a script that can reassign which Droplet our Floating IP is pointing to, in case the <code>FloatIP</code> resource needs to be moved to a different node. For this purpose, we'll download a basic Python script that assigns a Floating IP to a given Droplet ID, using the IndiaReads API.</p>

<p>On <strong>both servers</strong>, download the <code>assign-ip</code> Python script:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo curl -L -o /usr/local/bin/assign-ip http://do.co/assign-ip
</li></ul></code></pre>
<p>On <strong>both servers</strong>, make it executable:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo chmod +x /usr/local/bin/assign-ip
</li></ul></code></pre>
<p>Use of the <code>assign-ip</code> script requires the following details:</p>

<ul>
<li><strong>Floating IP:</strong> The first argument to the script, the Floating IP that is being assigned</li>
<li><strong>Droplet ID:</strong> The second argument to the script, the Droplet ID that the Floating IP should be assigned to</li>
<li><strong>IndiaReads PAT (API token):</strong> Passed in as the environment variable <code>DO_TOKEN</code>, your read/write IndiaReads PAT</li>
</ul>

<p>Feel free to review the contents of the script before continuing.</p>

<p>So, if you wanted to manually run this script to reassign a Floating IP, you could run it like so: <code>DO_TOKEN=<span class="highlight">your_digitalocean_pat</span> /usr/local/bin/assign-ip <span class="highlight">your_floating_ip</span> <span class="highlight">droplet_id</span></code>. However, this script will be invoked from the FloatIP OCF resource agent in the event that the <code>FloatIP</code> resource needs to be moved to a different node.</p>

<p>Let's install the Float IP Resource Agent next.</p>

<h3 id="download-floatip-ocf-resource-agent">Download FloatIP OCF Resource Agent</h3>

<p>Pacemaker allows the addition of OCF resource agents by placing them in a specific directory.</p>

<p>On <strong>both servers</strong>, create the <code>digitalocean</code> resource agent provider directory with this command:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo mkdir /usr/lib/ocf/resource.d/digitalocean
</li></ul></code></pre>
<p>On <strong>both servers</strong>, download the FloatIP OCF Resource Agent:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo curl -o /usr/lib/ocf/resource.d/digitalocean/floatip https://gist.githubusercontent.com/thisismitch/b4c91438e56bfe6b7bfb/raw/2dffe2ae52ba2df575baae46338c155adbaef678/floatip-ocf
</li></ul></code></pre>
<p>On <strong>both servers</strong>, make it executable:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo chmod +x /usr/lib/ocf/resource.d/digitalocean/floatip
</li></ul></code></pre>
<p>Feel free to review the contents of the resource agent before continuing. It is a bash script that, if called with the <code>start</code> command, will look up the Droplet ID of the node that calls it (via Metadata), and assign the Floating IP to the Droplet ID. Also, it responds to the <code>status</code> and <code>monitor</code> commands by returning whether the calling Droplet has a Floating IP assigned to it.</p>

<p>It requires the following OCF parameters:</p>

<ul>
<li><strong>do_token:</strong>: The IndiaReads API token to use for Floating IP reassignments, i.e. your IndiaReads Personal Access Token</li>
<li><strong>floating_ip:</strong>: Your Floating IP (address), in case it needs to be reassigned</li>
</ul>

<p>Now we can use the FloatIP OCF resource agent to define our <code>FloatIP</code> resource.</p>

<h2 id="add-floatip-resource">Add FloatIP Resource</h2>

<p>With our FloatIP OCF resource agent installed, we can now configure our <code>FloatIP</code> resource.</p>

<p>On either server, create the <code>FloatIP</code> resource with this command (be sure to specify the two highlighted parameters with your own information):</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo crm configure primitive FloatIP ocf:digitalocean:floatip \
</li><li class="line" prefix="$">  params do_token=<span class="highlight">your_digitalocean_personal_access_token</span> \
</li><li class="line" prefix="$">  floating_ip=<span class="highlight">your_floating_ip</span>
</li></ul></code></pre>
<p>This creates a primitive resource, which is a generic type of cluster resource, called "FloatIP", using the FloatIP OCF Resource Agent we created earlier (<code>ocf:digitalocean:floatip</code>). Notice that it requires the <code>do_token</code> and <code>floating_ip</code> to be passed as parameters. These will be used if the Floating IP needs to be reassigned.</p>

<p>If you check the status of your cluster (<code>sudo crm status</code> or <code>sudo crm_mon</code>), you should see that the <code>FloatIP</code> resource is defined and started on one of your nodes:</p>
<pre class="code-pre "><code langs=""><div class="secondary-code-label " title="crm_mon:">crm_mon:</div>...
2 Nodes configured
1 Resource configured

Online: [ primary secondary ]

 FloatIP    (ocf::digitalocean:floatip):    Started <span class="highlight">primary</span>
</code></pre>
<p>Assuming that everything was set up properly, you should now have an active/passive HA setup! As it stands, the Floating IP will get reassigned to an online server if the node that the <code>FloatIP</code> is started on goes offline or into <code>standby</code> mode. Right now, if the active node—<strong>primary</strong>, in our example output—becomes unavailable, the cluster will instruct the <strong>secondary</strong> node to start the <code>FloatIP</code> resource and claim the Floating IP address for itself. Once the reassignment occurs, the Floating IP will direct users to the newly active <strong>secondary</strong> server.</p>

<p>Currently, the failover (Floating IP reassignment) is only triggered if the active host goes offline or is unable to communicate with the cluster. A better version of this setup would specify additional resources that should be managed by Pacemaker. This would allow the cluster to detect failures of specific services, such as load balancer or web server software. Before setting that up, though, we should make sure the basic failover works.</p>

<h2 id="test-high-availability">Test High Availability</h2>

<p>It's important to test that our high availability setup works, so let's do that now.</p>

<p>Currently, the Floating IP is assigned to the one of your nodes (let's assume <strong>primary</strong>). Accessing the Floating IP now, via the IP address or by the domain name that is pointing to it, will simply show the index page of the <strong>primary</strong> server. If you used the example user data script, it will look something like this:</p>
<pre class="code-pre "><code langs=""><div class="secondary-code-label " title="Floating IP is pointing to primary server:">Floating IP is pointing to primary server:</div>Droplet: <span class="highlight">primary</span>, IP Address: <span class="highlight">primary_ip_address</span>
</code></pre>
<p>This indicates that the Floating IP is, in fact, assigned to the primary Droplet.</p>

<p>Now, let's open a new local terminal and use <code>curl</code> to access the Floating IP on a 1 second loop. Use this command to do so, but be sure to replace the URL with your domain or Floating IP address:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">while true; do curl <span class="highlight">floating_IP_address</span>; sleep 1; done
</li></ul></code></pre>
<p>Currently, this will output the same Droplet name and IP address of the primary server. If we cause the primary server to fail, by powering it off or by changing  the primary node's cluster status to <code>standby</code>, we will see if the Floating IP gets reassigned to the secondary server.</p>

<p>Let's reboot the <strong>primary</strong> server now. Do so via the IndiaReads Control Panel or by running this command on the primary server:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo reboot
</li></ul></code></pre>
<p>After a few moments, the primary server should become unavailable. Pay attention to the output of the <code>curl</code> loop that is running in the terminal. You should notice output that looks like this:</p>
<pre class="code-pre "><code langs=""><div class="secondary-code-label " title="curl loop output:">curl loop output:</div>Droplet: <span class="highlight">primary</span>, IP Address: <span class="highlight">primary_IP_address</span>
...
curl: (7) Failed to connect to <span class="highlight">floating_IP_address</span> port 80: Connection refused
Droplet: <span class="highlight">secondary</span>, IP Address: <span class="highlight">secondary_IP_address</span>
...
</code></pre>
<p>That is, the Floating IP address should be reassigned to point to the IP address of the <strong>secondary</strong> server. That means that your HA setup is working, as a successful automatic failover has occurred.</p>

<p>You may or may not see the <code>Connection refused</code> error, which can occur if you try and access the Floating IP between the primary server failure and the Floating IP reassignment completion. </p>

<p>If you check the status of Pacemaker, you should see that the <code>FloatIP</code> resource is started on the <strong>secondary</strong> server. Also, the <strong>primary</strong> server should temporarily be marked as <code>OFFLINE</code> but will join the <code>Online</code> list as soon as it completes its reboot and rejoins the cluster.</p>

<h2 id="troubleshooting-the-failover-optional">Troubleshooting the Failover (Optional)</h2>

<p>Skip this section if your HA setup works as expected. If the failover did not occur as expected, you should review your setup before moving on. In particular, make sure that any references to your own setup, such as node IP addresses, your Floating IP, and your API token.</p>

<h3 id="useful-commands-for-troubleshooting">Useful Commands for Troubleshooting</h3>

<p>Here are some commands that can help you troubleshoot your setup.</p>

<p>As mentioned earlier, the <code>crm_mon</code> tool can be very helpful in viewing the real-time status of your nodes and resources:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo crm_mon
</li></ul></code></pre>
<p>Also, you can look at your cluster configuration with this command:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo crm configure show
</li></ul></code></pre>
<p>If the <code>crm</code> commands aren't working at all, you should look at the Corosync logs for clues:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo tail -f /var/log/corosync/corosync.log
</li></ul></code></pre>
<h3 id="miscellaneous-crm-commands">Miscellaneous CRM Commands</h3>

<p>These commands can be useful when configuring your cluster.</p>

<p>You can set a node to <code>standby</code> mode, which can be used to simulate a node becoming unavailable, with this command:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo crm node standby <span class="highlight">NodeName</span>
</li></ul></code></pre>
<p>You can change a node's status from <code>standby</code> to <code>online</code> with this command:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo crm node online <span class="highlight">NodeName</span>
</li></ul></code></pre>
<p>You can edit a resource, which allows you to reconfigure it, with this command:</p>
<pre class="code-pre "><code langs="">sudo crm configure edit <span class="highlight">ResourceName</span>
</code></pre>
<p>You can delete a resource, which must be stopped before it is deleted, with these command:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo crm resource stop <span class="highlight">ResourceName</span>
</li><li class="line" prefix="$">sudo crm configure delete <span class="highlight">ResourceName</span>
</li></ul></code></pre>
<p>Lastly, the <code>crm</code> command can be run by itself to access an interactive <code>crm</code> prompt:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">crm
</li></ul></code></pre>
<p>We won't cover the usage of the interactive <code>crm</code> prompt, but it can be used to do all of the <code>crm</code> configuration we've done up to this point.</p>

<h2 id="add-nginx-resource-optional">Add Nginx Resource (optional)</h2>

<p>Now that you are sure that your Floating IP failover works, let's look into adding a new resource to your cluster. In our example setup, Nginx is the main service that we are making highly available, so let's work on adding it as a resource that our cluster will manage.</p>

<p>Pacemaker comes with an Nginx resource agent, so we can easily add Nginx as a cluster resource.</p>

<p>Use this command to create a new primitive cluster resource called "Nginx":</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo crm configure primitive Nginx ocf:heartbeat:nginx \
</li><li class="line" prefix="$">  params httpd="/usr/sbin/nginx" \
</li><li class="line" prefix="$">  op start timeout="40s" interval="0" \
</li><li class="line" prefix="$">  op monitor timeout="30s" interval="10s" on-fail="restart" \
</li><li class="line" prefix="$">  op stop timeout="60s" interval="0"
</li></ul></code></pre>
<p>The specified resource tells the cluster to monitor Nginx every 10 seconds, and to restart it if it becomes unavailable.</p>

<p>Check the status of your cluster resources by using <code>sudo crm_mon</code> or <code>sudo crm status</code>:</p>
<pre class="code-pre "><code langs=""><div class="secondary-code-label " title="crm_mon:">crm_mon:</div>...
Online: [ primary secondary ]

 FloatIP    (ocf::digitalocean:floatip):    Started <span class="highlight">primary</span>
 Nginx  (ocf::heartbeat:nginx): Started <span class="highlight">secondary</span>
</code></pre>
<p>Unfortunately, Pacemaker will decide to start the <code>Nginx</code> and <code>FloatIP</code> resources on separate nodes because we have not defined any resource constraints. This is a problem because this means that the Floating IP will be pointing to one Droplet, while the Nginx service will only be running on the other Droplet. Accessing the Floating IP will point you to a server that is not running the service that should be highly available.</p>

<p>To resolve this issue, we'll create a <strong>clone</strong> resource, which specifies that an existing primitive resource should be started on multiple nodes.</p>

<p>Create a clone resource of the <code>Nginx</code> resource called "Nginx-clone" with this command:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo crm configure clone Nginx-clone Nginx
</li></ul></code></pre>
<p>The cluster status should now look something like this:</p>
<pre class="code-pre "><code langs=""><div class="secondary-code-label " title="crm_mon:">crm_mon:</div>Online: [ primary secondary ]

FloatIP (ocf::digitalocean:floatip):    Started primary
 Clone Set: Nginx-clone [Nginx]
     Started: [ primary secondary ]
</code></pre>
<p>As you can see, the clone resource, <code>Nginx-clone</code>, is now started on both of our nodes.</p>

<p>The last step is to configure a <strong>colocation</strong> restraint, to specify that the <code>FloatIP</code> resource should run on a node with an active <code>Nginx-clone</code> resource. To create a colocation restraint called "FloatIP-Nginx", use this command:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo crm configure colocation FloatIP-Nginx inf: FloatIP Nginx-clone
</li></ul></code></pre>
<p>You won't see any difference in the <code>crm status</code> output, but you can see that the colocation resource was created with this command:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo crm configure show
</li></ul></code></pre>
<p>Now, both of your servers should have Nginx running, while only one of them, has the <code>FloatIP</code> resource running. Now is a good time to test your HA setup by stopping your Nginx service and by rebooting or powering off your <strong>active</strong> server.</p>

<h2 id="conclusion">Conclusion</h2>

<p>Congratulations! You now have a basic HA server setup using Corosync, Pacemaker, and a IndiaReads Floating IP.</p>

<p>The next step is to replace the example Nginx setup with a reverse-proxy load balancer. You can use Nginx or HAProxy for this purpose. Keep in mind that you will want to bind your load balancer to the <strong>anchor IP address</strong>, so that your users can only access your servers via the Floating IP address (and not via the public IP address of each server). This process is detailed in the <a href="https://indiareads/community/tutorials/how-to-create-a-high-availability-haproxy-setup-with-corosync-pacemaker-and-floating-ips-on-ubuntu-14-04">How To Create a High Availability HAProxy Setup with Corosync, Pacemaker, and Floating IPs on Ubuntu 14.04</a> tutorial.</p>

    