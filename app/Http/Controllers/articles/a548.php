<link rel='stylesheet' href='digital_ocean.css'><br><img src=https://community-cdn-digitalocean-com.global.ssl.fastly.net/assets/tutorials/images/large/high_availability_tw.jpg?1457123595/> <br> 
      <h3 id="introduction">Introduction</h3>

<p>Corosync is an open source cluster engine used to implement high availability within applications. Commonly referred to as a <strong><em>messaging layer</em></strong>, Corosync provides a cluster membership and closed communication model for creating replicated state machines, on top of which cluster resource managers like Pacemaker can run. Corosync can be seen as the underlying system that connects the cluster nodes together, while Pacemaker monitors the cluster and takes action in the event of a failure.</p>

<p>This tutorial will demonstrate how to use Corosync and Pacemaker to create a high availability (HA) infrastructure on IndiaReads with CentOS 7 servers and Floating IPs. To facilitate the process of setting up and managing the cluster nodes, we are going to use PCS, a command line interface that interacts with both Corosync and Pacemaker.</p>

<h2 id="prerequisites">Prerequisites</h2>

<p>In order to follow this guide, you will need:</p>

<ul>
<li>Two CentOS 7 Droplets located in the same datacenter, with <a href="https://indiareads/community/tutorials/how-to-set-up-and-use-digitalocean-private-networking">Private Network</a> enabled</li>
<li>A non-root sudo user, which you can set up by following the <a href="https://indiareads/community/tutorials/initial-server-setup-with-centos-7">Initial Server Setup</a> tutorial</li>
<li>A Personal Access Token to the IndiaReads API, which you can generate by following the tutorial <a href="https://indiareads/community/tutorials/how-to-use-the-digitalocean-api-v2">How to Use the IndiaReads API V2</a></li>
</ul>

<p>When creating these Droplets, use descriptive hostnames to uniquely identify them. For this tutorial, we will refer to these Droplets as <strong>primary</strong> and <strong>secondary</strong>.</p>

<p>When you are ready to move on, make sure you are logged into both of your servers with your  <code>sudo</code> user.</p>

<h2 id="step-1-—-set-up-nginx">Step 1 — Set Up Nginx</h2>

<p>To speed things up, we are going to use a simple <a href="http://do.co/nginx-centos">shell script</a> that installs Nginx and sets up a basic web page containing information about that specific server. This way we can easily identify which server is currently active in our Floating IP setup. The script uses IndiaReads's <a href="https://indiareads/community/tutorials/an-introduction-to-droplet-metadata">Metadata service</a> to fetch the Droplet's IP address and hostname.</p>

<p>In order to execute the script, run the following commands on both servers:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo curl -L -o install.sh http://do.co/nginx-centos
</li><li class="line" prefix="$">sudo chmod +x install.sh
</li><li class="line" prefix="$">sudo ./install.sh
</li></ul></code></pre>
<p>After the script is finished running, accessing either Droplet via its public IP address from a browser should give you a basic web page showing the Droplet's hostname and IP address.</p>

<p><span class="note">In order to reduce this tutorial’s complexity, we will be using simple web servers as cluster nodes. In a production environment, the nodes would typically be configured to act as redundant load balancers. For more information about load balancers, check out our <a href="https://indiareads/community/tutorials/an-introduction-to-haproxy-and-load-balancing-concepts">Introduction to HAProxy and Load Balancing Concepts</a> guide.</span></p>

<h2 id="step-2-—-create-and-assign-floating-ip">Step 2 — Create and Assign Floating IP</h2>

<p>The first step is to create a Floating IP and assign it to the <strong>primary</strong> server. In the IndiaReads Control Panel, click <strong>Networking</strong> in the top menu, then <strong>Floating IPs</strong> in the side menu.</p>

<p>You should see a page like this:</p>

<p><img src="https://assets.digitalocean.com/site/ControlPanel/fip_no_floating_ips.png" alt="Floating IPs Control Panel" /></p>

<p>Select your <strong>primary</strong> server and click on the "Assign Floating IP" button. After the Floating IP has been assigned, check that you can reach the <strong>primary</strong> Droplet by accessing the Floating IP address from your browser:</p>
<pre class="code-pre "><code langs="">http://<span class="highlight">your_floating_ip</span>
</code></pre>
<p>You should see the index page of your primary Droplet.</p>

<h2 id="step-3-—-create-ip-reassignment-script">Step 3 — Create IP Reassignment Script</h2>

<p>In this step, we'll demonstrate how the IndiaReads API can be used to reassign a Floating IP to another Droplet. Later on, we will configure Pacemaker to execute this script when the cluster detects a failure in one of the nodes.</p>

<p>For our example, we are going to use a basic Python script that takes a Floating IP address and a Droplet ID as arguments in order to assign the Floating IP to the given Droplet. The Droplet’s ID can be fetched from within the Droplet itself using the Metadata service.</p>

<p>Let's start by downloading the <code>assign-ip</code> script and making it executable. Feel free to review the contents of the script before downloading it. </p>

<p>The following two commands should be executed on <strong>both servers</strong> (primary and secondary):</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo curl -L -o /usr/local/bin/assign-ip http://do.co/assign-ip
</li><li class="line" prefix="$">sudo chmod +x /usr/local/bin/assign-ip
</li></ul></code></pre>
<p>The <code>assign-ip</code> script requires the following information in order to be executed:</p>

<ul>
<li><strong>Floating IP</strong>: The first argument to the script, the Floating IP that is being assigned</li>
<li><strong>Droplet ID</strong>: The second argument to the script, the Droplet ID that the Floating IP should be assigned to</li>
<li><strong>IndiaReads API Token</strong> : Passed in as the environment variable DO_TOKEN, your read/write IndiaReads Personal Access Token</li>
</ul>

<h3 id="testing-the-ip-reassignment-script">Testing the IP Reassignment Script</h3>

<p>To monitor the IP reassignment taking place, we can use a <code>curl</code> command to access the Floating IP address in a loop, with an interval of 1 second between each request. </p>

<p>Open a new local terminal and run the following command, making sure to replace <span class="highlight">floating_IP_address</span> with your actual Floating IP address:</p>
<pre class="code-pre custom_prefix"><code langs=""><ul class="prefixed"><li class="line" prefix="local$">while true; do curl <span class="highlight">floating_IP_address</span>; sleep 1; done
</li></ul></code></pre>
<p>This command will keep running in the active terminal until interrupted with a <code>CTRL+C</code>. It simply fetches the web page hosted by the server that your Floating IP is currently assigned to. The output should look like this:</p>
<pre class="code-pre "><code langs=""><div class="secondary-code-label " title="Output">Output</div>Droplet: primary, IP Address: <span class="highlight">primary_IP_address</span>
Droplet: primary, IP Address: <span class="highlight">primary_IP_address</span>
Droplet: primary, IP Address: <span class="highlight">primary_IP_address</span>
...
</code></pre>
<p>Now, let's run the <code>assign-ip</code> script to reassign the Floating IP to the <strong>secondary</strong> droplet. We will use IndiaReads's Metadata service to fetch the current Droplet ID and use it as an argument to the script. Fetching the Droplet’s ID from the Metadata service can be done with:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">curl -s http://169.254.169.254/metadata/v1/id
</li></ul></code></pre>
<p>Where <code>169.254.169.254</code> is a static IP address used by the Metadata service, and therefore should not be modified. This information is only available from within the Droplet itself.</p>

<p>Before we can execute the script, we need to set the <em>DO_TOKEN</em> environment variable containing the IndiaReads API token. Run the following command from the <strong>secondary</strong> server, and don’t forget to replace <span class="highlight">your_api_token</span> with your read/write Personal Access Token to the IndiaReads API:</p>
<pre class="code-pre custom_prefix"><code langs=""><ul class="prefixed"><li class="line" prefix="secondary$">export DO_TOKEN=<span class="highlight">your_api_token</span>
</li></ul></code></pre>
<p>Still on the <strong>secondary</strong> server, run the <code>assign-ip</code> script replacing <span class="highlight">floating_IP_address</span> with your Floating IP address:</p>
<pre class="code-pre custom_prefix"><code langs=""><ul class="prefixed"><li class="line" prefix="secondary$">assign-ip <span class="highlight">floating_IP_address</span> `curl -s http://169.254.169.254/metadata/v1/id`
</li></ul></code></pre><pre class="code-pre "><code langs=""><div class="secondary-code-label " title="Output">Output</div>Moving IP address: in-progress
</code></pre>
<p>By monitoring the output produced by the <code>curl</code> command on your local terminal, you will notice that the Floating IP will change its assigned IP address and start pointing to the <strong>secondary</strong> Droplet after a few seconds:</p>
<pre class="code-pre "><code langs=""><div class="secondary-code-label " title="Output">Output</div>Droplet: primary, IP Address: <span class="highlight">primary_IP_address</span>
Droplet: primary, IP Address: <span class="highlight">primary_IP_address</span>
Droplet: secondary, IP Address: <span class="highlight">secondary_IP_address</span>
</code></pre>
<p>You can also access the Floating IP address from your browser. You should get a page showing the <strong>secondary</strong> Droplet information. This means that the reassignment script worked as expected. </p>

<p>To reassign the Floating IP back to the primary server, repeat the 2-step process but this time from the <strong>primary</strong> Droplet:</p>
<pre class="code-pre custom_prefix"><code langs=""><ul class="prefixed"><li class="line" prefix="primary$">export DO_TOKEN=<span class="highlight">your_api_token</span>
</li><li class="line" prefix="primary$">assign-ip <span class="highlight">floating_IP_address</span> `curl -s http://169.254.169.254/metadata/v1/id`
</li></ul></code></pre>
<p>After a few seconds, the Floating IP should be pointing to your primary Droplet again. </p>

<h2 id="step-4-—-install-corosync-pacemaker-and-pcs">Step 4 — Install Corosync, Pacemaker and PCS</h2>

<p>The next step is to get Corosync, Pacemaker and PCS installed on your Droplets. Because Corosync is a dependency to Pacemaker, it's usually a better idea to simply install Pacemaker and let the system decide which Corosync version should be installed. </p>

<p>Install the software packages on <strong>both servers</strong>:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo yum install pacemaker pcs
</li></ul></code></pre>
<p>The PCS utility creates a new system user during installation, named <strong><em>hacluster</em></strong>, with a disabled password. We need to define a password for this user on both servers. This will enable PCS to perform tasks such as synchronizing the Corosync configuration on multiple nodes, as well as starting and stopping the cluster.</p>

<p>On <strong>both servers</strong>, run:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">passwd hacluster
</li></ul></code></pre>
<p>You should use the <strong>same password</strong> on both servers. We are going to use this password to configure the cluster in the next step.</p>

<p><span class="note">The user <strong><em>hacluster</em></strong> has no interactive shell or home directory associated with its account, which means it's not possible to log into the server using its credentials.<br /></span></p>

<h2 id="step-5-—-set-up-the-cluster">Step 5 — Set Up the Cluster</h2>

<p>Now that we have Corosync, Pacemaker and PCS installed on both servers, we can set up the cluster. </p>

<h3 id="enabling-and-starting-pcs">Enabling and Starting PCS</h3>

<p>To enable and start the PCS daemon, run the following on <strong>both servers</strong>:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo systemctl enable pcsd.service
</li><li class="line" prefix="$">sudo systemctl start pcsd.service
</li></ul></code></pre>
<h3 id="obtaining-the-private-network-ip-address-for-each-node">Obtaining the Private Network IP Address for Each Node</h3>

<p>For improved network performance and security, the nodes should be connected using the <strong>private network</strong>. The easiest way to obtain the Droplet’s private network IP address is via the Metadata service. On each server, run the following command:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">curl http://169.254.169.254/metadata/v1/interfaces/private/0/ipv4/address && echo
</li></ul></code></pre>
<p>This command will simply output the private network IP address of the Droplet you’re logged in. You can also find this information on your Droplet’s page at the IndiaReads Control Panel (under the <em>Settings</em> tab).</p>

<p>Collect the private network IP address from both Droplets for the next steps.</p>

<h3 id="authenticating-the-cluster-nodes">Authenticating the Cluster Nodes</h3>

<p>Authenticate the cluster nodes using the username <strong><em>hacluster</em></strong> and the same password you defined on step 3. You’ll need to provide the private network IP address for each node. From the <strong>primary</strong> server, run:</p>
<pre class="code-pre custom_prefix"><code langs=""><ul class="prefixed"><li class="line" prefix="primary$">sudo pcs cluster auth <span class="highlight">primary_private_IP_address secondary_private_IP_address</span>
</li></ul></code></pre>
<p>You should get output like this:</p>
<pre class="code-pre "><code langs=""><div class="secondary-code-label " title="Output">Output</div>Username: hacluster
Password: 
<span class="highlight">primary_private_IP_address</span>: Authorized
<span class="highlight">secondary_private_IP_address</span>: Authorized
</code></pre>
<h3 id="generating-the-corosync-configuration">Generating the Corosync Configuration</h3>

<p>Still on the <strong>primary</strong> server, generate the Corosync configuration file with the following command:</p>
<pre class="code-pre custom_prefix"><code langs=""><ul class="prefixed"><li class="line" prefix="primary$">sudo pcs cluster setup --name <span class="highlight">webcluster</span> \ 
</li><li class="line" prefix="primary$"><span class="highlight">primary_private_IP_address secondary_private_IP_address</span>
</li></ul></code></pre>
<p>The output should look similar to this:</p>
<pre class="code-pre "><code langs=""><div class="secondary-code-label " title="Output">Output</div>Shutting down pacemaker/corosync services...
Redirecting to /bin/systemctl stop  pacemaker.service
Redirecting to /bin/systemctl stop  corosync.service
Killing any remaining services...
Removing all cluster configuration files...
<span class="highlight">primary_private_IP_address</span>: Succeeded
<span class="highlight">secondary_private_IP_address</span>: Succeeded
Synchronizing pcsd certificates on nodes <span class="highlight">primary_private_IP_address</span>, <span class="highlight">secondary_private_IP_address</span>...
<span class="highlight">primary_private_IP_address</span>: Success
<span class="highlight">secondary_private_IP_address</span>: Success

Restaring pcsd on the nodes in order to reload the certificates...
<span class="highlight">primary_private_IP_address</span>: Success
<span class="highlight">secondary_private_IP_address</span>: Success
</code></pre>
<p>This will generate a new configuration file located at <code>/etc/corosync/corosync.conf</code> based on the parameters provided to the <code>pcs cluster setup</code> command. We used <strong>webcluster</strong> as the cluster name in this example, but you can use the name of your choice.</p>

<h3 id="starting-the-cluster">Starting the Cluster</h3>

<p>To start the cluster you just set up, run the following command from the <strong>primary</strong> server:</p>
<pre class="code-pre custom_prefix"><code langs=""><ul class="prefixed"><li class="line" prefix="primary$">sudo pcs cluster start --all
</li></ul></code></pre><pre class="code-pre "><code langs=""><div class="secondary-code-label " title="Output">Output</div><span class="highlight">primary_private_IP_address</span>: Starting Cluster...
<span class="highlight">secondary_private_IP_address</span>: Starting Cluster...
</code></pre>
<p>You can now confirm that both nodes joined the cluster by running the following command on any of the servers:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo pcs status corosync
</li></ul></code></pre><pre class="code-pre "><code langs=""><div class="secondary-code-label " title="Output">Output</div>Membership information
----------------------
    Nodeid      Votes Name
         2          1 <span class="highlight">secondary_private_IP_address</span>
         1          1 <span class="highlight">primary_private_IP_address</span> (local)
</code></pre>
<p>To get more information about the current status of the cluster, you can run:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo pcs cluster status
</li></ul></code></pre>
<p>The output should be similar to this:</p>
<pre class="code-pre "><code langs=""><div class="secondary-code-label " title="Output">Output</div>Cluster Status:
 Last updated: Fri Dec 11 11:59:09 2015     Last change: Fri Dec 11 11:59:00 2015 by hacluster via crmd on secondary
 Stack: corosync
 Current DC: secondary (version 1.1.13-a14efad) - partition with quorum
 2 nodes and 0 resources configured
 Online: [ primary secondary ]

PCSD Status:
  primary (<span class="highlight">primary_private_IP_address</span>): Online
  secondary (<span class="highlight">secondary_private_IP_address</span>): Online
</code></pre>
<p>Now you can enable the <code>corosync</code> and <code>pacemaker</code> services to make sure they will start when the system boots. Run the following on <strong>both servers</strong>:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo systemctl enable corosync.service
</li><li class="line" prefix="$">sudo systemctl enable pacemaker.service
</li></ul></code></pre>
<h3 id="disabling-stonith">Disabling STONITH</h3>

<p>STONITH (Shoot The Other Node In The Head) is a fencing technique intended to prevent data corruption caused by faulty nodes in a cluster that are unresponsive but still accessing application data. Because its configuration depends on a number of factors that are out of scope for this guide, we are going to disable STONITH in our cluster setup.</p>

<p>To disable STONITH, run the following command on one of the Droplets, either primary or secondary:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo pcs property set stonith-enabled=false
</li></ul></code></pre>
<h2 id="step-6-—-create-floating-ip-reassignment-resource-agent">Step 6 — Create Floating IP Reassignment Resource Agent</h2>

<p>The only thing left to do is to configure the resource agent that will execute the IP reassignment script when a failure is detected in one of the cluster nodes. The resource agent is responsible for creating an interface between the cluster and the resource itself. In our case, the resource is the assign-ip script. The cluster relies on the resource agent to execute the right procedures when given a start, stop or monitor command. There are different types of resource agents, but the most common one is the OCF (Open Cluster Framework) standard.</p>

<p>We will create a new OCF resource agent to manage the <strong>assign-ip</strong> service on both servers.</p>

<p>First, create the directory that will contain the resource agent. The directory name will be used by Pacemaker as an identifier for this custom agent. Run the following on <strong>both servers</strong>:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo mkdir /usr/lib/ocf/resource.d/digitalocean
</li></ul></code></pre>
<p>Next, download the FloatIP resource agent script and place it in the newly created directory, on <strong>both servers</strong>:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo curl -L -o /usr/lib/ocf/resource.d/digitalocean/floatip http://do.co/ocf-floatip
</li></ul></code></pre>
<p>Now make the script executable with the following command on <strong>both servers</strong>:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo chmod +x /usr/lib/ocf/resource.d/digitalocean/floatip
</li></ul></code></pre>
<p>We still need to register the resource agent within the cluster, using the PCS utility. The following command should be executed from <strong>one</strong> of the nodes (don't forget to replace <span class="highlight">your_api_token</span> with your IndiaReads API token and <span class="highlight">floating_IP_address</span> with your actual Floating IP address):</p>
<pre class="code-pre custom_prefix"><code langs=""><ul class="prefixed"><li class="line" prefix="primary$">sudo pcs resource create FloatIP ocf:digitalocean:floatip \
</li><li class="line" prefix="primary$">    params do_token=<span class="highlight">your_api_token</span> \
</li><li class="line" prefix="primary$">    floating_ip=<span class="highlight">floating_IP_address</span> 
</li></ul></code></pre>
<p>The resource should now be registered and active in the cluster. You can check the registered resources from any of the nodes with the <code>pcs status</code> command:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo pcs status
</li></ul></code></pre><pre class="code-pre "><code langs=""><div class="secondary-code-label " title="Output">Output</div>...
2 nodes and 1 resource configured

Online: [ primary secondary ]

Full list of resources:

 FloatIP    (ocf::digitalocean:floatip):    Started primary

...
</code></pre>
<h2 id="step-7-—-test-failover">Step 7 — Test Failover</h2>

<p>Your cluster should now be ready to handle a node failure. A simple way to test failover is to restart the server that is currently active in your Floating IP setup. If you’ve followed all steps in this tutorial, this should be the <strong>primary</strong> server.</p>

<p>Again, let’s monitor the IP reassignment by using a <code>curl</code> command in a loop. From a local terminal, run:</p>
<pre class="code-pre custom_prefix"><code langs=""><ul class="prefixed"><li class="line" prefix="local$">while true; do curl <span class="highlight">floating_IP_address</span>; sleep 1; done
</li></ul></code></pre>
<p>From the <strong>primary</strong> server, run a reboot command:</p>
<pre class="code-pre custom_prefix"><code langs=""><ul class="prefixed"><li class="line" prefix="primary$">sudo reboot
</li></ul></code></pre>
<p>After a few moments, the primary server should become unavailable. This will cause the secondary server to take over as the active node. You should see output similar to this in your local terminal running <code>curl</code>:</p>
<pre class="code-pre "><code langs=""><div class="secondary-code-label " title="Output">Output</div>...
Droplet: primary, IP Address: <span class="highlight">primary_IP_address</span>
Droplet: primary, IP Address: <span class="highlight">primary_IP_address</span>
curl: (7) Failed connect to floating_IP_address; Connection refused
Droplet: secondary, IP Address: <span class="highlight">secondary_IP_address</span>
Droplet: secondary, IP Address: <span class="highlight">secondary_IP_address</span>
…
</code></pre>
<p><span class="note">The “Connection refused” error happens when the request is made right before or at the same time when the IP reassignment is taking place. It may or may not show up in the output.<br /></span></p>

<p>If you want to point the Floating IP back to the primary node while also testing failover on the secondary node, just repeat the process but this time from the <strong>secondary</strong> Droplet:</p>
<pre class="code-pre custom_prefix"><code langs=""><ul class="prefixed"><li class="line" prefix="secondary$">sudo reboot
</li></ul></code></pre>
<h2 id="conclusion">Conclusion</h2>

<p>In this guide, we saw how Floating IPs can be used together with Corosync, Pacemaker and PCS to create a highly available web server environment on CentOS 7 servers. We used a rather simple infrastructure to demonstrate the usage of Floating IPs, but this setup can be scaled to implement high availability at any level of your application stack.</p>

    