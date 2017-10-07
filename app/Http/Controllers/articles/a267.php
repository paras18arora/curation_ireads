<link rel='stylesheet' href='digital_ocean.css'><br> <br> 
      <h3 id="introduction">Introduction</h3>

<p>High availability is an important topic nowadays because service outages can be very costly. It's prudent to take measures which will keep your your website or web application running in case of an outage. With the Pacemaker stack, you can configure a high availability cluster.</p>

<p>Pacemaker is a <em>cluster resource manager</em>. It manages all cluster services (<em>resources</em>) and uses the messaging and membership capabilities of the underlying <em>cluster engine</em>. We will use Corosync as our cluster engine. Resources have a <em>resource agent</em>, which is a external program that abstracts the service.</p>

<p>In an active-passive cluster, all services run on a primary system. If the primary system fails, all services get moved to the backup system. An active-passive cluster makes it possible to do maintenance work without interruption.</p>

<p>In this tutorial, you will learn how to build a high availability Apache active-passive cluster. The web cluster will get addressed by its virtual IP address and will automatically fail over if a node fails.</p>

<p>Your users will access your web application by the virtual IP address, which is managed by Pacemaker. The Apache service and the virtual IP are always located on the same host. When this host fails, they get migrated to the second host and your users will not notice the outage.</p>

<h3 id="prerequisites">Prerequisites</h3>

<p>Before you get started with this tutorial, you will need the following:</p>

<ul>
<li><p>Two CentOS 7 Droplets, which will be the cluster nodes. We'll refer to these as webnode01 (IP address: <code><span class="highlight">your_first_server_ip</span></code>) and webnode02 (IP address: <code><span class="highlight">your_second_server_ip</span></code>).</p></li>
<li><p>A user on both servers with root privileges. You can set this up by following this <a href="https://indiareads/community/tutorials/initial-server-setup-with-centos-7">Initial Server Setup with CentOS 7</a> tutorial.</p></li>
</ul>

<p>You'll have to run some commands on both servers, and some commands on only one.</p>

<h2 id="step-1-—-configuring-name-resolution">Step 1 — Configuring Name Resolution</h2>

<p>First, we need to make sure that both hosts can resolve the hostname of the two cluster nodes. To accomplish that, we'll add entries to the <code>/etc/hosts</code> file. Follow this step on both webnode01 and webnode02.</p>

<p>Open <code>/etc/hosts</code> with <code>nano</code> or your favorite text editor.</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo nano /etc/hosts
</li></ul></code></pre>
<p>Add the following entries to the end of the file.</p>
<div class="code-label " title="/etc/hosts">/etc/hosts</div><pre class="code-pre "><code langs=""><span class="highlight">your_first_server_ip</span> webnode01.example.com webnode01
<span class="highlight">your_second_server_ip</span> webnode02.example.com webnode02
</code></pre>
<p>Save and close the file.</p>

<h2 id="step-2-—-installing-apache">Step 2 — Installing Apache</h2>

<p>In this section, we will install the Apache web server. You have to complete this step on both hosts.</p>

<p>First, install Apache.</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo yum install httpd
</li></ul></code></pre>
<p>The Apache resource agent uses the Apache server status page for checking the health of the Apache service. You have to activate the status page by creating the file <code>/etc/httpd/conf.d/status.conf</code>.</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo nano /etc/httpd/conf.d/status.conf
</li></ul></code></pre>
<p>Paste the following directive in this file. These directives allow the access to the status page from localhost but not from any other host.</p>
<div class="code-label " title="/etc/httpd/conf.d/status.conf">/etc/httpd/conf.d/status.conf</div><pre class="code-pre "><code langs=""><Location /server-status>
   SetHandler server-status
   Order Deny,Allow
   Deny from all
   Allow from 127.0.0.1
</Location>
</code></pre>
<p>Save and close the file.</p>

<h2 id="step-3-—-installing-pacemaker">Step 3 — Installing Pacemaker</h2>

<p>Now we will install the Pacemaker stack. You have to complete this step on both hosts.</p>

<p>Install the Pacemaker stack and the pcs cluster shell. We'll use the latter later to configure the cluster.</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo yum install pacemaker pcs
</li></ul></code></pre>
<p>Now we have to start the pcs daemon, which is used for synchronizing the Corosync configuration across the nodes.</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo systemctl start pcsd.service
</li></ul></code></pre>
<p>In order that the daemon gets started after every reboot, we will also enable the service.</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo systemctl enable pcsd.service
</li></ul></code></pre>
<p>After you have installed these packages, there will be a new user on your system called <strong>hacluster</strong>. After the installation, remote login is disabled for this user. For tasks like synchronizing the configuration or starting services on other nodes, we have to set the same password for this user.</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo passwd hacluster
</li></ul></code></pre>
<h2 id="step-4-—-configuring-pacemaker">Step 4 — Configuring Pacemaker</h2>

<p>Next, we'll allow cluster traffic in FirewallD to allow our hosts to communicate.</p>

<p>First, check if FirewallD is running.</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo firewall-cmd --state
</li></ul></code></pre>
<p>If it's not running, start it.</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo systemctl start firewalld.service
</li></ul></code></pre>
<p>You'll need to do this on both hosts. Once it's running, add the <code>high-availability</code> service to FirewallD.</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo firewall-cmd --permanent --add-service=high-availability
</li></ul></code></pre>
<p>After this change, you need to reload FirewallD.</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo firewall-cmd --reload
</li></ul></code></pre>
<p>If you want to learn more about FirewallD, you can read this <a href="https://indiareads/community/tutorials/how-to-configure-firewalld-to-protect-your-centos-7-server">guide about how to configure FirewallD on CentOS 7</a>.</p>

<p>Now that our two hosts can talk to each other, we can set up the authentication between the two nodes by running this command on one host (in our case, <strong>webnode01</strong>).</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo pcs cluster auth webnode01 webnode02
</li><li class="line" prefix="$">Username: <span class="highlight">hacluster</span>
</li></ul></code></pre>
<p>You should see the following output:</p>
<div class="code-label " title="Output">Output</div><pre class="code-pre "><code langs="">webnode01: Authorized
webnode02: Authorized
</code></pre>
<p>Next, we'll generate and synchronize the Corosync configuration on the same host. Here, we'll name the cluster <strong>webcluster</strong>, but you can call it whatever you like.</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo pcs cluster setup --name <span class="highlight">webcluster</span> webnode01 webnode02
</li></ul></code></pre>
<p>You'll see the following output:</p>
<div class="code-label " title="Output">Output</div><pre class="code-pre "><code langs="">Shutting down pacemaker/corosync services...
Redirecting to /bin/systemctl stop  pacemaker.service
Redirecting to /bin/systemctl stop  corosync.service
Killing any remaining services...
Removing all cluster configuration files...
webnode01: Succeeded
webnode02: Succeeded
</code></pre>
<p>The corosync configuration is now created and distributed across all nodes. The configuration is stored in the file <code>/etc/corosync/corosync.conf</code>.</p>

<h2 id="step-5-—-starting-the-cluster">Step 5 — Starting the Cluster</h2>

<p>The cluster can be started by running the following command on webnode01.</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo pcs cluster start --all
</li></ul></code></pre>
<p>To ensure that Pacemaker and corosync starts at boot, we have to enable the services on both hosts.</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo systemctl enable corosync.service
</li><li class="line" prefix="$">sudo systemctl enable pacemaker.service
</li></ul></code></pre>
<p>We can now check the status of the cluster by running the following command on either host.</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo pcs status
</li></ul></code></pre>
<p>Check that both hosts are marked as online in the output.</p>
<div class="code-label " title="Output">Output</div><pre class="code-pre "><code langs="">. . .

Online: [ webnode01 webnode02 ]

Full list of resources:


PCSD Status:
  webnode01: Online
  webnode02: Online

Daemon Status:
  corosync: active/enabled
  pacemaker: active/enabled
  pcsd: active/enabled
</code></pre>
<p><span class="note"><strong>Note:</strong> After the first setup, it can take some time before the nodes are marked as online.<br /></span></p>

<h2 id="step-6-—-disabling-stonith-and-ignoring-quorum">Step 6 — Disabling STONITH and Ignoring Quorum</h2>

<h3 id="what-is-stonith">What is STONITH?</h3>

<p>You will see a warning in the output of <code>pcs status</code> that no STONITH devices are configured and STONITH is not disabled:</p>
<div class="code-label " title="Warning">Warning</div><pre class="code-pre "><code langs="">. . .
WARNING: no stonith devices and stonith-enabled is not false
. . .
</code></pre>
<p>What does this mean and why should you care?</p>

<p>When the cluster resource manager cannot determine the state of a node or of a resource on a node, <em>fencing</em> is used to bring the cluster to a known state again.</p>

<p><em>Resource level fencing</em> ensures mainly that there is no data corruption in case of an outage by configuring a resource. You can use resource level fencing, for instance, with DRBD (Distributed Replicated Block Device) to mark the disk on a node as outdated when the communication link goes down.</p>

<p><em>Node level fencing</em> ensures that a node does not run any resources. This is done by resetting the node and the Pacemaker implementation of it is called STONITH (which stands for "shoot the other node in the head"). Pacemaker supports a great variety of fencing devices, e.g. an uninterruptible power supply or management interface cards for servers.</p>

<p>Because the node level fencing configuration depends heavily on your environment, we will disable it for this tutorial.</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo pcs property set stonith-enabled=false
</li></ul></code></pre>
<p><span class="note"><strong>Note:</strong> If you plan to use Pacemaker in a production environment, you should plan a STONITH implementation depending on your environment and keep it enabled.<br /></span></p>

<h3 id="what-is-quorum">What is Quorum?</h3>

<p>A cluster has <em>quorum</em> when more than half of the nodes are online. Pacemaker's default behavior is to stop all resources if the cluster does not have quorum. However, this does not make sense in a two-node cluster; the cluster will lose quorum if one node fails.</p>

<p>For this tutorial, we will tell Pacemaker to ignore quorum by setting the <code>no-quorum-policy</code>:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo pcs property set no-quorum-policy=ignore
</li></ul></code></pre>
<h2 id="step-7-—-configuring-the-virtual-ip-address">Step 7 — Configuring the Virtual IP address</h2>

<p>From now on, we will interact with the cluster via the <code>pcs</code> shell, so all commands need only be executed on one host; it doesn't matter which one.</p>

<p>The Pacemaker cluster is now up and running and we can add the first resource to it, which is the virtual IP address. To do this, we will configure the <code>ocf:heartbeat:IPaddr2</code> resource agent, but first, let's cover some terminology.</p>

<p>Every resource agent name has either three or two fields that are separated by a colon:</p>

<ul>
<li><p>The first field is the resource class, which is the standard the resource agent conforms to. It also tells Pacemaker where to find the script. The <code>IPaddr2</code> resource agent conforms to the OCF (Open Cluster Framework) standard.</p></li>
<li><p>The second field depends on the standard. OCF resources use the second field for the OCF namespace.</p></li>
<li><p>The third field is the name of the resource agent.</p></li>
</ul>

<p>Resources can have <em>meta-attributes</em> and <em>instance attributes</em>. Meta-attributes do not depend on the resource type; instance attributes are resource agent-specific. The only required instance attribute of this resource agent is <code>ip</code> (the virtual IP address), but for the sake of explicitness we will also set <code>cidr_netmask</code> (the subnetmask in CIDR notation).</p>

<p><em>Resource operations</em> are actions the cluster can perform on a resource (e.g. start, stop, monitor). They are indicated by the keyword <code>op</code>. We will add the <code>monitor</code> operation with an interval of 20 seconds so that the cluster checks every 20 seconds if the resource is still healthy. What's considered healthy depends on the resource agent.</p>

<p>First, we will create the virtual IP address resource. Here, we'll use <code>127.0.0.2</code> as our virtual IP and <strong>Cluster_VIP</strong> for the name of the resource.</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo pcs resource create Cluster_VIP ocf:heartbeat:IPaddr2 ip=<span class="highlight">127.0.0.2</span> cidr_netmask=24 op monitor interval=20s
</li></ul></code></pre>
<p>Next, check the status of the resource.</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo pcs status
</li></ul></code></pre>
<p>Look for the following line in the output:</p>
<div class="code-label " title="Output">Output</div><pre class="code-pre "><code langs="">...
Full list of resources:

 Cluster_VIP    (ocf::heartbeat:IPaddr2):   Started webnode01
...
</code></pre>
<p>The virtual IP address is active on the host webnode01.</p>

<h2 id="step-8-—-adding-the-apache-resource">Step 8 — Adding the Apache Resource</h2>

<p>Now we can add the second resource to the cluster, which will the Apache service. The resource agent of the service is <code>ocf:heartbeat:apache</code>.</p>

<p>We will name the resource <code>WebServer</code> and set the instance attributes <code>configfile</code> (the location of the Apache configuration file) and <code>statusurl</code> (the URL of the Apache server status page). We will choose a monitor interval of 20 seconds again.</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo pcs resource create WebServer ocf:heartbeat:apache configfile=/etc/httpd/conf/httpd.conf statusurl="http://127.0.0.1/server-status" op monitor interval=20s
</li></ul></code></pre>
<p>We can query the status of the resource like before.</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo pcs status
</li></ul></code></pre>
<p>You should see <strong>WebServer</strong> in the output running on webnode02.</p>
<div class="code-label " title="Output">Output</div><pre class="code-pre "><code langs="">...
Full list of resources:

 Cluster_VIP    (ocf::heartbeat:IPaddr2):   Started webnode01
 WebServer  (ocf::heartbeat:apache):    Started webnode02
...
</code></pre>
<p>As you can see, the resources run on different hosts. We did not yet tell Pacemaker that these resources must run on the same host, so they are evenly distributed across the nodes.</p>

<p><span class="note"><strong>Note:</strong> You can restart the Apache resource by running <code>sudo pcs resource restart WebServer</code> (e.g. if you change the Apache configuration). Make sure not to use <code>systemctl</code> to manage the Apache service.<br /></span></p>

<h2 id="step-9-—-configuring-colocation-constraints">Step 9 — Configuring Colocation Constraints</h2>

<p>Almost every decision in a Pacemaker cluster, like choosing where a resource should run, is done by comparing scores. Scores are calculated per resource, and the cluster resource manager chooses the node with the highest score for a particular resource. (If a node has a negative score for a resource, the resource cannot run on that node.)</p>

<p>We can manipulate the decisions of the cluster with constraints. Constraints have a score. If a constraint has a score lower than INFINITY, it is only a recommendation. A score of INFINITY means it is a must.</p>

<p>We want to ensure that both resources are run on the same host, so we will define a colocation constraint with a score of INFINITY.</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo pcs constraint colocation add WebServer Cluster_VIP INFINITY
</li></ul></code></pre>
<p>The order of the resources in the constraint definition is important. Here, we specify that the Apache resource (<code>WebServer</code>) must run on the same hosts the virtual IP (<code>Cluster_VIP</code>) is active on. This also means that <code>WebSite</code> is not permitted to run anywhere if <code>Cluster_VIP</code> is not active.</p>

<p>It is also possible to define in which order the resources should run by creating ordering constraints or to prefer certain hosts for some resources by creating location constraints.</p>

<p>Verify that both resources run on the same host.</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo pcs status
</li></ul></code></pre><div class="code-label " title="Output">Output</div><pre class="code-pre "><code langs="">...
Full list of resources:

 Cluster_VIP    (ocf::heartbeat:IPaddr2):   Started webnode01
 WebServer  (ocf::heartbeat:apache):    Started webnode01
...
</code></pre>
<p>Both resources are now on webnode01.</p>

<h2 id="conclusion">Conclusion</h2>

<p>You have set up an Apache two node active-passive cluster which is accessible by the virtual IP address. You can now configure Apache further, but make sure to synchronize the configuration across the hosts. You can write a custom script for this (e.g. with <code>rsync</code>) or you can use something like <a href="http://oss.linbit.com/csync2/">csync2</a>.</p>

<p>If you want to distribute the files of your web application among the hosts, you can set up a DRBD volume and <a href="https://drbd.linbit.com/users-guide/ch-pacemaker.html">integrate it with Pacemaker</a>.</p>

    