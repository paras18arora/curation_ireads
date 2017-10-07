<link rel='stylesheet' href='digital_ocean.css'><br><img src=https://community-cdn-digitalocean-com.global.ssl.fastly.net/assets/tutorials/images/large/Heartbeat-twitter-01.png?1447339237/> <br> 
      <h3 id="introduction">Introduction</h3>

<p>Heartbeat is an open source program that provides cluster infrastructure capabilities—cluster membership and messaging—to client servers, which is a critical component in a high availability (HA) server infrastructure. Heartbeat is typically used in conjunction with a cluster resource manager (CRM), such as Pacemaker, to achieve a complete HA setup. However, in this tutorial, we will demonstrate how to create a 2-node HA server setup by simply using Heartbeat and a IndiaReads Floating IP.</p>

<p>If you are looking to create a more robust HA setup, look into using <a href="https://indiareads/community/tutorials/how-to-create-a-high-availability-setup-with-corosync-pacemaker-and-floating-ips-on-ubuntu-14-04">Corosync and Pacemaker</a> or <a href="https://indiareads/community/tutorials/how-to-set-up-highly-available-web-servers-with-keepalived-and-floating-ips-on-ubuntu-14-04">Keepalived</a>.</p>

<h2 id="goal">Goal</h2>

<p>When completed, the HA setup will consist of two Ubuntu 14.04 servers in an active/passive configuration. This will be accomplished by pointing a Floating IP, which is how your users will access your services or website, to point to the primary, or active, server unless a failure is detected. In the event that the Heartbeat service detects that the primary server is unavailable, the secondary server will automatically run a script to reassign the Floating IP to itself via the IndiaReads API. Thus, subsequent network traffic to the Floating IP will be directed to your secondary server, which will act as the active server until the primary server becomes available again (at which point, the primary server will reassign the Floating IP to itself).</p>

<p><img src="https://assets.digitalocean.com/articles/high_availability/ha-diagram-animated.gif" alt="Active/passive Diagram" /></p>

<p><span class="note"><strong>Note:</strong> This tutorial only covers setting up active/passive high availability at the gateway level. That is, it includes the Floating IP, and the <em>load balancer</em> servers—Primary and Secondary. Furthermore, for demonstration purposes, instead of configuring reverse-proxy load balancers on each server, we will simply configure them to respond with their respective hostname and public IP address.<br /></span></p>

<p>To achieve this goal, we will follow these steps:</p>

<ul>
<li>Create 2 Droplets that will receive traffic</li>
<li>Create Floating IP and assign it to one of the Droplets</li>
<li>Create DNS A record that points to Floating IP (optional)</li>
<li>Install Heartbeat on Droplets</li>
<li>Configure Heartbeat to Run Floating IP Reassignment Service</li>
<li>Create Floating IP Reassignment Service</li>
<li>Test failover</li>
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

<p>The first step is to create two Ubuntu Droplets in the same datacenter, which will act as the primary and secondary servers described above. In our example setup, we will name them "primary" and "secondary" for easy reference. We will install Nginx on both Droplets and replace their index pages with information that uniquely identifies them. This will allow us a simple way to demonstrate that the HA setup is working. For a real setup, your servers should run the web server or load balancer of your choice.</p>

<p>Create two Ubuntu 14.04 Droplets, <strong>primary</strong> and <strong>secondary</strong>, with this bash script as the user data:</p>
<div class="code-label " title="Example User Data">Example User Data</div><pre class="code-pre "><code langs="">#!/bin/bash

apt-get -y update
apt-get -y install nginx
export HOSTNAME=$(curl -s http://169.254.169.254/metadata/v1/hostname)
export PUBLIC_IPV4=$(curl -s http://169.254.169.254/metadata/v1/interfaces/public/0/ipv4/address)
echo Droplet: $HOSTNAME, IP Address: $PUBLIC_IPV4 > /usr/share/nginx/html/index.html
</code></pre>
<p>This will install Nginx and replace the contents of <code>index.html</code> with the droplet's hostname and IP address (by referencing the Metadata service). Accessing either Droplet via its public IP address will show a basic webpage with the Droplet hostname and IP address, which will be useful for testing which Droplet the Floating IP is pointing to at any given moment.</p>

<h2 id="create-a-floating-ip">Create a Floating IP</h2>

<p>In the IndiaReads Control Panel, click <strong>Networking</strong>, in the top menu, then <strong>Floating IPs</strong> in the side menu.</p>

<p><img src="https://assets.digitalocean.com/site/ControlPanel/fip_no_floating_ips.png" alt="No Floating IPs" /></p>

<p>Assign a Floating IP to your <strong>primary</strong> Droplet, then click the <strong>Assign Floating IP</strong> button.</p>

<p>After the Floating IP has been assigned, check that you can reach the Droplet that it was assigned to by visiting it in a web browser.</p>
<pre class="code-pre "><code langs="">http://<span class="highlight">your_floating_ip</span>
</code></pre>
<p>You should see the index page of your primary Droplet.</p>

<h2 id="configure-dns-optional">Configure DNS (Optional)</h2>

<p>If you want to be able to access your HA setup via a domain name, go ahead and create an <strong>A record</strong> in your DNS that points your domain to your Floating IP address. If your domain is using IndiaReads's nameservers, follow <a href="https://indiareads/community/tutorials/how-to-set-up-a-host-name-with-digitalocean#step-three%E2%80%94configure-your-domain">step three</a> of the How To Set Up a Host Name with IndiaReads tutorial. Once that propagates, you may access your active server via the domain name.</p>

<p>The example domain name we'll use is <code>example.com</code>. If you don't have a domain name right now, you should use the Floating IP address instead.</p>

<h2 id="install-heartbeat">Install Heartbeat</h2>

<p>The next step is to install Heartbeat on both servers. The simplest way to install Heartbeat is to use apt-get:</p>
<pre class="code-pre "><code langs="">sudo apt-get update
sudo apt-get install heartbeat
</code></pre>
<p>Heartbeat is now installed but it needs to be configured before it will do anything.</p>

<h2 id="configure-heartbeat">Configure Heartbeat</h2>

<p>In order to get our desired cluster up and running, we must set up these Heartbeat configuration files in <code>/etc/ha.d</code>, identically on both servers:</p>

<ol>
<li><strong>ha.cf:</strong> Global configuration of the Heartbeat cluster, including its member nodes</li>
<li><strong>authkeys:</strong> Contains a security key that provides nodes a way to authenticate to the cluster</li>
<li><strong>haresources:</strong> Specifies the services that are managed by the cluster and the node that is the preferred owner of the services. Note that this file is not used in a setup that uses a CRM like Pacemaker</li>
</ol>

<p>We will also need to provide a script that will perform the Floating IP reassignment in the event that the primary Droplet's availability changes.</p>

<h3 id="gather-node-information">Gather Node Information</h3>

<p>Before configuring <code>ha.cf</code>, we should look up the names of each node. Heartbeat requires that each node name matches their respective <code>uname -n</code> output. </p>

<p>On <strong>both servers</strong>, run this command to look up the appropriate node names:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">uname -n
</li></ul></code></pre>
<p>Note the output of the command. The example node names are "primary" and "secondary", which matches what we named the Droplets.</p>

<p>We will also need to look up the network interface and IP address that each node will use to communicate with the rest of the cluster, to determine which nodes are available. You may use any network interface, as long as each node can reach the other nodes in the cluster. We'll use the public interface of our Droplets, which happens to be <code>eth0</code>.</p>

<p>On <strong>both servers</strong>, use this command to look up the IP address of the <code>eth0</code> interface (or look it up in the IndiaReads Control Panel):</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">ip addr show eth0
</li></ul></code></pre><pre class="code-pre "><code langs=""><div class="secondary-code-label " title="ip addr show eth0 output:">ip addr show eth0 output:</div>2: eth0: <BROADCAST,MULTICAST,UP,LOWER_UP> mtu 1500 qdisc pfifo_fast state UP group default qlen 1000
    link/ether 04:01:76:a5:45:01 brd ff:ff:ff:ff:ff:ff
    inet <span class="highlight">104.236.6.11</span>/18 brd 104.236.63.255 scope global eth0
       valid_lft forever preferred_lft forever
    inet 10.17.0.28/16 scope global eth0
       valid_lft forever preferred_lft forever
    inet6 fe80::601:76ff:fea5:4501/64 scope link
       valid_lft forever preferred_lft forever
</code></pre>
<p>Note the IP address of the network interface (highlighted in the example). Be sure to get the IP addresses of both servers.</p>

<h3 id="create-ha-cf-file">Create ha.cf File</h3>

<p>On <strong>both servers</strong>, open <code>/etc/ha.d/ha.cf</code> in your favorite editor. We'll use <code>vi</code>:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo vi /etc/ha.d/ha.cf
</li></ul></code></pre>
<p>The file should be new and empty. We need to add the network interfaces and names of each node in our cluster.</p>

<p>Copy and paste this configuration into the file, then replace the respective node names and IP addresses with the values that we looked up earlier. In this example, <strong>primary</strong>'s IP address is <code>104.236.6.11</code> and <strong>secondary</strong>'s IP address is <code>104.236.6.22</code>:</p>
<pre class="code-pre "><code langs="">node <span class="highlight">primary</span>
ucast eth0 <span class="highlight">104.236.6.11</span>
node <span class="highlight">secondary</span>
ucast eth0 <span class="highlight">104.236.6.22</span>
</code></pre>
<p>Save and exit. Next, we'll set up the cluster's authorization key.</p>

<h2 id="create-authkeys-file">Create authkeys File</h2>

<p>The authorization key is used to allow cluster members to join a cluster. We can simply generate a random key for this purpose.</p>

<p>On the <strong>primary</strong> node, run these commands to generate a suitable authorization key in an environment variable named <code>AUTH_KEY</code>:</p>
<pre class="code-pre "><code langs="">if [ -z "${AUTH_KEY}" ]; then
  export AUTH_KEY="$(command dd if='/dev/urandom' bs=512 count=1 2>'/dev/null' \
      | command openssl sha1 \
      | command cut --delimiter=' ' --fields=2)"
fi
</code></pre>
<p>Then write the <code>/etc/ha.d/authkeys</code> file with these commands:</p>
<pre class="code-pre "><code langs="">sudo bash -c "{
  echo auth1
  echo 1 sha1 $AUTH_KEY
} > /etc/ha.d/authkeys"
</code></pre>
<p>Check the contents of the <code>authkeys</code> file like this:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo cat /etc/ha.d/authkeys
</li></ul></code></pre>
<p>It should like something like this (with a different authorization key):</p>
<pre class="code-pre "><code langs=""><div class="secondary-code-label " title="/etc/ha.d/authkeys example:">/etc/ha.d/authkeys example:</div>auth1
1 sha1 <span class="highlight">d1e6557e2fcb30ff8d4d3ae65b50345fa46a2faa</span>
</code></pre>
<p>Ensure that the file is only readable by root:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo chmod 600 /etc/ha.d/authkeys
</li></ul></code></pre>
<p>Now copy the <code>/etc/ha.d/authkeys</code> file from your primary node to your secondary node. You can do this manually, or with <code>scp</code>.</p>

<p>On the <strong>secondary</strong> server, be sure to set the permissions of the <code>authkeys</code> file:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo chmod 600 /etc/ha.d/authkeys
</li></ul></code></pre>
<p>Both servers should have an identical <code>/etc/ha.d/authkeys</code> file.</p>

<h3 id="create-haresources-file">Create haresources File</h3>

<p>The <code>haresources</code> file specifies <strong>preferred hosts</strong> paired with services that the cluster manages. The preferred host is the node that <em>should</em> run the associated service(s) if the node is available. If the preferred host <strong>is not</strong> available, i.e. it is not reachable by the cluster, one of the other nodes will take over. In other words, the secondary server will take over if the primary server goes down.</p>

<p>On <strong>both servers</strong>, open the <code>haresources</code> file in your favorite editor. We'll use <code>vi</code>:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo vi /etc/ha.d/haresources
</li></ul></code></pre>
<p>Now add this line to the file, substituting in your primary node's name:</p>
<div class="code-label " title="/etc/ha.d/haresources">/etc/ha.d/haresources</div><pre class="code-pre line_numbers"><code langs=""><ul class="prefixed"><li class="line" prefix="1"><span class="highlight">primary</span> floatip
</li></ul></code></pre>
<p>Save and exit. This configures the <strong>primary</strong> server as the preferred host for the <code>floatip</code> service, which is currently undefined. Let's set up the <code>floatip</code> service next.</p>

<h2 id="create-floating-ip-reassignment-service">Create Floating IP Reassignment Service</h2>

<p>Our Heartbeat cluster is configured to maintain the <code>floatip</code> service, which a node can use to assign the Floating IP to itself, but we still need to create the service. Before we set up the service itself, however, let's create a script that will assign the Floating IP, via the IndiaReads API, to the node that runs it. Then we will create the <code>floatip</code> service which will run the Floating IP reassignment script.</p>

<h3 id="create-assign-ip-script">Create assign-ip Script</h3>

<p>For our example, we'll download a basic Python script that assigns a Floating IP to a given Droplet ID, using the IndiaReads API.</p>

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

<p>Now we're ready to create the <code>floatip</code> service.</p>

<h3 id="create-floatip-service">Create floatip Service</h3>

<p>To create the <code>floatip</code> service, all we need to do is create an init script that invokes the <code>assign-ip</code> script that we created earlier, and responds to <code>start</code> and <code>stop</code> subcommands. This init script will be responsible for looking up the Droplet ID of the server, via the Droplet Metadata service. Also, it will require the Floating IP that will be reassigned, and the IndiaReads API token (the Personal Access Token mentioned in the prerequisites section).</p>

<p>On <strong>both servers</strong>, add open <code>/etc/init.d/floatip</code> in an editor:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo vi /etc/init.d/floatip
</li></ul></code></pre>
<p>Then copy and paste in this init script, replacing the highlighted parts with your IndiaReads API key and the Floating IP that should be reassigned:</p>
<div class="code-label " title="/etc/init.d/floatip">/etc/init.d/floatip</div><pre class="code-pre line_numbers"><code langs=""><ul class="prefixed"><li class="line" prefix="1">#!/bin/bash
</li><li class="line" prefix="2">
</li><li class="line" prefix="3">param=$1
</li><li class="line" prefix="4">
</li><li class="line" prefix="5">export DO_TOKEN='<span class="highlight">b7d03a6947b217efb6f3ec3bd3504582</span>'
</li><li class="line" prefix="6">IP='<span class="highlight">45.55.96.8</span>'
</li><li class="line" prefix="7">ID=$(curl -s http://169.254.169.254/metadata/v1/id)
</li><li class="line" prefix="8">
</li><li class="line" prefix="9">if [ "start" == "$param" ] ; then
</li><li class="line" prefix="10">  python /usr/local/bin/assign-ip $IP $ID
</li><li class="line" prefix="11">  exit 0
</li><li class="line" prefix="12">elif [ "stop" == "$param" ] ; then
</li><li class="line" prefix="13">  exit 0;
</li><li class="line" prefix="14">elif [ "status" == "$param" ] ; then
</li><li class="line" prefix="15">  exit 0;
</li><li class="line" prefix="16">else
</li><li class="line" prefix="17">  echo "no such command $param"
</li><li class="line" prefix="18">  exit 1;
</li><li class="line" prefix="19">fi
</li></ul></code></pre>
<p>Save and exit.</p>

<p>Make the script executable:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo chmod u+x /etc/init.d/floatip
</li></ul></code></pre>
<p>When this <code>floatip</code> service is started, it will simply call the <code>assign-ip</code> Python script and assign the specified Floating IP to the Droplet that executed the script. This is the script that will be called by the <strong>secondary</strong> server, to reassign the Floating IP to itself, if the <strong>primary</strong> server fails. Likewise, the same script will be used by the <strong>primary</strong> server, to reclaim the Floating IP, once it rejoins the cluster.</p>

<h2 id="start-heartbeat">Start Heartbeat</h2>

<p>Now that Heartbeat is configured, and all of the scripts it relies on are set up, we're ready to start the Heartbeat cluster!</p>

<p>On <strong>both servers</strong>, run this command to start Heartbeat:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo service heartbeat start
</li></ul></code></pre>
<p>You should see output like this:</p>
<pre class="code-pre "><code langs=""><div class="secondary-code-label " title="Heartbeat output:">Heartbeat output:</div>Starting High-Availability services: Done.
</code></pre>
<p>Our HA setup is now complete! Before moving on, let's test that it works as intended.</p>

<h2 id="test-high-availability">Test High Availability</h2>

<p>It's important to test that a high availability setup works, so let's do that now.</p>

<p>Currently, the Floating IP is assigned to the <strong>primary</strong> node. Accessing the Floating IP now, via the IP address or by the domain name that is pointing to it, will simply show the index page of the <strong>primary</strong> server. If you used the example user data script, it will look something like this:</p>
<pre class="code-pre "><code langs=""><div class="secondary-code-label " title="Floating IP is pointing to primary server">Floating IP is pointing to primary server</div>Droplet: <span class="highlight">primary</span>, IP Address: <span class="highlight">104.236.6.11</span>
</code></pre>
<p>This indicates that the Floating IP is, in fact, assigned to the primary Droplet.</p>

<p>Now, let's open a terminal and use <code>curl</code> to access the Floating IP on a 1 second loop. Use this command to do so, but be sure to replace the URL with your domain or Floating IP address:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">while true; do curl http://<span class="highlight">example.com</span>; sleep 1; done
</li></ul></code></pre>
<p>Currently, this will output the same Droplet name and IP address of the primary server. If we cause the primary server to fail, by powering it off or stopping the Heartbeat service, we will see if the Floating IP gets reassigned to the secondary server.</p>

<p>Let's power off the <strong>primary</strong> server now. Do so via the IndiaReads Control Panel or by running this command on the primary server:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo poweroff
</li></ul></code></pre>
<p>After a few moments, the primary server should become unavailable. Pay attention to the output of the <code>curl</code> loop that is running in the terminal. You should notice output that looks like this:</p>
<pre class="code-pre "><code langs=""><div class="secondary-code-label " title="curl loop output:">curl loop output:</div>Droplet: <span class="highlight">primary</span>, IP Address: <span class="highlight">104.236.6.11</span>
...
curl: (7) Failed to connect to <span class="highlight">example.com</span> port 80: Connection refused
Droplet: <span class="highlight">secondary</span>, IP Address: <span class="highlight">104.236.6.22</span>
Droplet: <span class="highlight">secondary</span>, IP Address: <span class="highlight">104.236.6.22</span>
...
</code></pre>
<p>That is, the Floating IP address should be reassigned to point to the IP address of the <strong>secondary</strong> server. That means that your HA setup is working, as a successful automatic failover has occurred.</p>

<p>You may or may not see the <code>Connection refused</code> error, which can occur if you try and access the Floating IP between the primary server failure and the Floating IP reassignment completion. </p>

<p>Now, you may power on your <strong>primary</strong> Droplet, via the IndiaReads Control Panel. Because Heartbeat is configured with the primary Droplet as the <strong>preferred host</strong> to run the Floating IP reassignment script, the Floating IP will automatically point back to the primary server as soon as it becomes available again.</p>

<h2 id="conclusion">Conclusion</h2>

<p>Congratulations! You now have a basic HA server setup using Heartbeat and a IndiaReads Floating IP.</p>

<p>If you are looking to create a more robust HA setup, look into using <a href="https://indiareads/community/tutorials/how-to-create-a-high-availability-setup-with-corosync-pacemaker-and-floating-ips-on-ubuntu-14-04">Corosync and Pacemaker</a> or <a href="https://indiareads/community/tutorials/how-to-set-up-highly-available-web-servers-with-keepalived-and-floating-ips-on-ubuntu-14-04">Keepalived</a>.</p>

<p>If you want to extend your Heartbeat setup, the next step is to replace the example Nginx setup with a reverse-proxy load balancer. You can use Nginx or HAProxy for this purpose. Keep in mind that you will want to bind your load balancer to the <strong>anchor IP address</strong>, so that your users can only access your servers via the Floating IP address (and not via the public IP address of each server).</p>

    