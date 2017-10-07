<link rel='stylesheet' href='digital_ocean.css'><br><img src=https://community-cdn-digitalocean-com.global.ssl.fastly.net/assets/tutorials/images/large/high_availability_tw.jpg?1456265897/> <br> 
      <h3 id="introduction">Introduction</h3>

<p>This tutorial will show you how to create a High Availability HAProxy load balancer setup on IndiaReads, with the support of a Floating IP and the Corosync/Pacemaker cluster stack. The HAProxy load balancers will each be configured to split traffic between two backend application servers.  If the primary load balancer goes down, the Floating IP will be moved to the second load balancer automatically, allowing service to resume.</p>

<p><img src="https://assets.digitalocean.com/articles/high_availability/ha-diagram-animated.gif" alt="High Availability HAProxy setup" /></p>

<h2 id="prerequisites">Prerequisites</h2>

<p>In order to complete this guide, you will need to have completed the <a href="https://indiareads/community/tutorials/how-to-create-a-high-availability-setup-with-corosync-pacemaker-and-floating-ips-on-ubuntu-14-04">How To Create a High Availability Setup with Corosync, Pacemaker, and Floating IPs on Ubuntu 14.04</a> tutorial (you should skip the optional <strong>Add Nginx Resource</strong> section). This will leave you with two Droplets, which we'll refer to as <strong>primary</strong> and <strong>secondary</strong>, with a Floating IP that can transition between them. Collectively, we'll refer to these servers as <strong>load balancers</strong>. These are the Droplets where we'll install a load balancer, HAProxy.</p>

<p>You will also need to be able to create two additional Ubuntu 14.04 Droplets in the same datacenter, with Private Networking enabled, to demonstrate that the HA load balancer setup works. These are the servers that will be load balanced by HAProxy. We will refer to these application servers, which we will install Nginx on, as <strong>app-1</strong> and <strong>app-2</strong>. If you already have application servers that you want to load balance, feel free to use those instead.</p>

<p>On each of these servers, you will need a non-root user configured with <code>sudo</code> access.  You can follow our <a href="https://indiareads/community/tutorials/initial-server-setup-with-ubuntu-14-04">Ubuntu 14.04 initial server setup guide</a> to learn how to set up these users.</p>

<h2 id="create-app-droplets">Create App Droplets</h2>

<p>The first step is to create two Ubuntu Droplets, with Private Networking enabled, in the same datacenter as your load balancers, which will act as the <strong>app-1</strong> and <strong>app-2</strong> servers described above. We will install Nginx on both Droplets and replace their index pages with information that uniquely identifies them. This will allow us a simple way to demonstrate that the HA load balancer setup is working. If you already have application servers that you want to load balance, feel free to adapt the appropriate parts of this tutorial to make that work (and skip any parts that are irrelevant to your setup).</p>

<p>If you want to follow the example setup, create two Ubuntu 14.04 Droplets, <strong>app-1</strong> and <strong>app-2</strong>, and use this bash script as the user data:</p>
<div class="code-label " title="Example User Data">Example User Data</div><pre class="code-pre "><code langs="">#!/bin/bash

apt-get -y update
apt-get -y install nginx
export HOSTNAME=$(curl -s http://169.254.169.254/metadata/v1/hostname)
export PUBLIC_IPV4=$(curl -s http://169.254.169.254/metadata/v1/interfaces/public/0/ipv4/address)
echo Droplet: $HOSTNAME, IP Address: $PUBLIC_IPV4 > /usr/share/nginx/html/index.html
</code></pre>
<p>This user data will install Nginx and replace the contents of index.html with the droplet's hostname and public IP address (by referencing the Metadata service). Accessing either Droplet will show a basic webpage with the Droplet hostname and public IP address, which will be useful for testing which app server the load balancers are directing traffic to.</p>

<h2 id="gather-server-network-information">Gather Server Network Information</h2>

<p>Before we begin the actual configuration of our infrastructure components, it is best to gather some information about each of your servers.</p>

<p>To complete this guide, you will need to have the following information about your servers:</p>

<ul>
<li><strong>app servers</strong>: Private IP address</li>
<li><strong>load balancers</strong> Private and Anchor IP addresses</li>
</ul>

<h3 id="find-private-ip-addresses">Find Private IP Addresses</h3>

<p>The easiest way to find your Droplet's private IP address is to use <code>curl</code> to grab the private IP address from the IndiaReads metadata service.  This command should be run from within your Droplets.  On each Droplet, type:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">curl 169.254.169.254/metadata/v1/interfaces/private/0/ipv4/address && echo
</li></ul></code></pre>
<p>The correct IP address should be printed in the terminal window:</p>
<pre class="code-pre "><code langs=""><div class="secondary-code-label " title="Private IP address:">Private IP address:</div>10.132.20.236
</code></pre>
<p>Perform this step on all four Droplets, and copy the Private IP addresses somewhere that you can easily reference.</p>

<h3 id="find-anchor-ip-addresses">Find Anchor IP Addresses</h3>

<p>The <strong>anchor IP</strong> is the local private IP address that the Floating IP will bind to when attached to a IndiaReads server. It is simply an alias for the regular <code>eth0</code> address, implemented at the hypervisor level.</p>

<p>The easiest, least error-prone way of grabbing this value is straight from the IndiaReads metadata service. Using <code>curl</code>, you can reach out to this endpoint on each of your servers by typing:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">curl 169.254.169.254/metadata/v1/interfaces/public/0/anchor_ipv4/address && echo
</li></ul></code></pre>
<p>The anchor IP will be printed on its own line:</p>
<pre class="code-pre "><code langs=""><div class="secondary-code-label " title="Output">Output</div>10.17.1.18
</code></pre>
<p>Perform this step on both of your load balancer Droplets, and copy the anchor IP addresses somewhere that you can easily reference.</p>

<h2 id="configure-app-servers">Configure App Servers</h2>

<p>After gathering the data above, we can move on to configuring our services.</p>

<p></p><div class="code-label notes-and-warnings note" title="Note">Note</div><span class="note">
In this setup, the software selected for the web server layer is fairly interchangeable.  This guide will use Nginx because it is generic and rather easy to configure.  If you are more comfortable with Apache or a (production-capable) language-specific web server, feel free to use that instead.  HAProxy will simply pass client requests to the backend web servers which can handle the requests similarly to how it would handle direct client connections.<br /></span>

<p>We will start off by setting up our backend app servers.  Both of these servers will simply serve their name and public IP address; in a real setup, these servers would serve identical content. They will only accept web connections over their private IP addresses.  This will help ensure that traffic is directed exclusively through one of the two HAProxy servers we will be configuring later.</p>

<p>Setting up app servers behind a load balancer allows us to distribute the request burden among some number identical app servers.  As our traffic needs change, we can easily scale to meet the new demands by adding or removing app servers from this tier.</p>

<h3 id="configure-nginx-to-only-allow-requests-from-the-load-balancers">Configure Nginx to Only Allow Requests from the Load Balancers</h3>

<p>If you're following the example, and you used the provided <strong>user data</strong> when creating your app servers, your servers will already have Nginx installed. The next step is to make a few configuration changes.</p>

<p>We want to configure Nginx to only listen for requests on the private IP address of the server.  Furthermore, we will only serve requests coming from the private IP addresses of our two load balancers. This will force users to access your app servers through your load balancers (which we will configure to be accessible only via the Floating IP address).</p>

<p>To make these changes, open the default Nginx server block file on each of your app servers:</p>
<pre class="code-pre custom_prefix"><code langs=""><ul class="prefixed"><li class="line" prefix="appserver$">sudo vi /etc/nginx/sites-available/default
</li></ul></code></pre>
<p>To start, we will modify the <code>listen</code> directives.  Change the <code>listen</code> directive to listen to the current <strong>app server's private IP address</strong> on port 80.  Delete the extra <code>listen</code> line.  It should look something like this:</p>
<div class="code-label " title="/etc/nginx/sites-available/default (1 of 2)">/etc/nginx/sites-available/default (1 of 2)</div><pre class="code-pre "><code langs="">server {
    listen <span class="highlight">app_server_private_IP</span>:80;

    . . .
</code></pre>
<p>Directly below the <code>listen</code> directive, we will set up two <code>allow</code> directives to permit traffic originating from the private IP addresses of our two load balancers.  We will follow this up with a <code>deny all</code> rule to forbid all other traffic:</p>
<div class="code-label " title="/etc/nginx/sites-available/default (2 of 2)">/etc/nginx/sites-available/default (2 of 2)</div><pre class="code-pre "><code langs="">    allow <span class="highlight">load_balancer_1_private_IP</span>;
    allow <span class="highlight">load_balancer_2_private_IP</span>;
    deny all;
</code></pre>
<p>Save and close the files when you are finished.</p>

<p>Test that the changes that you made represent valid Nginx syntax by typing:</p>
<pre class="code-pre custom_prefix"><code langs=""><ul class="prefixed"><li class="line" prefix="webserver$">sudo nginx -t
</li></ul></code></pre>
<p>If no problems were reported, restart the Nginx daemon by typing:</p>
<pre class="code-pre custom_prefix"><code langs=""><ul class="prefixed"><li class="line" prefix="appserver$">sudo service nginx restart
</li></ul></code></pre>
<p>Remember to perform all of these steps (with the appropriate app server private IP addresses) on both app servers.</p>

<h3 id="testing-the-changes">Testing the Changes</h3>

<p>To test that your app servers are restricted correctly, you can make requests using <code>curl</code> from various locations.</p>

<p>On your app servers themselves, you can try a simple request of the local content by typing:</p>
<pre class="code-pre custom_prefix"><code langs=""><ul class="prefixed"><li class="line" prefix="appserver$">curl 127.0.0.1
</li></ul></code></pre>
<p>Because of the restrictions we set in place in our Nginx server block files, this request will actually be denied:</p>
<pre class="code-pre "><code langs=""><div class="secondary-code-label " title="Output">Output</div>curl: (7) Failed to connect to 127.0.0.1 port 80: Connection refused
</code></pre>
<p>This is expected and reflects the behavior that we were attempting to implement.</p>

<p>Now, from either of the <strong>load balancers</strong>, we can make a request for either of our app server's public IP address:</p>
<pre class="code-pre custom_prefix"><code langs=""><ul class="prefixed"><li class="line" prefix="loadbalancer$">curl <span class="highlight">web_server_public_IP</span>
</li></ul></code></pre>
<p>Once again, this should fail.  The app servers are not listening on the public interface and furthermore, when using the public IP address, our app servers would not see the allowed private IP addresses in the request from our load balancers:</p>
<pre class="code-pre "><code langs=""><div class="secondary-code-label " title="Output">Output</div>curl: (7) Failed to connect to <span class="highlight">app_server_public_IP</span> port 80: Connection refused
</code></pre>
<p>However, if we modify the call to make the request using the app server's <em>private IP address</em>, it should work correctly:</p>
<pre class="code-pre custom_prefix"><code langs=""><ul class="prefixed"><li class="line" prefix="loadbalancer$">curl <span class="highlight">app_server_private_IP</span>
</li></ul></code></pre>
<p>The Nginx <code>index.html</code> page should be returned. If you used the example user data, the page should contain the name and public IP address of the app server being accessed:</p>
<pre class="code-pre "><code langs=""><div class="secondary-code-label " title="app server index.html">app server index.html</div>Droplet: app-1, IP Address: 159.203.130.34
</code></pre>
<p>Test this from both load balancers to both app servers.  Each request for the private IP address should succeed while each request made to the public addresses should fail.</p>

<p>Once the above behavior is demonstrated, we can move on.  Our backend app server configuration is now complete.</p>

<h2 id="remove-nginx-from-load-balancers">Remove Nginx from Load Balancers</h2>

<p>By following the prerequisite <strong>HA Setup with Corosync, Pacemaker, and Floating IPs</strong> tutorial, your load balancer servers will have Nginx installed. Because we're going to use HAProxy as the reverse proxy load balancer, we should delete Nginx and any associated cluster resources.</p>

<h3 id="remove-nginx-cluster-resources">Remove Nginx Cluster Resources</h3>

<p>If you added an Nginx cluster resource while following the prerequisite tutorial, stop and delete the <code>Nginx</code> resource with these commands on <strong>one of your load balancers</strong>:</p>
<pre class="code-pre custom_prefix"><code langs=""><ul class="prefixed"><li class="line" prefix="loadbalancer$">sudo crm resource stop Nginx
</li><li class="line" prefix="loadbalancer$">sudo crm configure delete Nginx
</li></ul></code></pre>
<p>This should also delete any cluster settings that depend on the <code>Nginx</code> resource. For example, if you created a <code>clone</code> or <code>colocation</code> that references the <code>Nginx</code> resource, they will also be deleted.</p>

<h3 id="remove-nginx-package">Remove Nginx Package</h3>

<p>Now we're ready to uninstall Nginx on <strong>both of the load balancer servers</strong>.</p>

<p>First, stop the Nginx service:</p>
<pre class="code-pre custom_prefix"><code langs=""><ul class="prefixed"><li class="line" prefix="loadbalancer$">sudo service nginx stop
</li></ul></code></pre>
<p>Then purge the package with this command:</p>
<pre class="code-pre custom_prefix"><code langs=""><ul class="prefixed"><li class="line" prefix="loadbalancer$">sudo apt-get purge nginx
</li></ul></code></pre>
<p>You may also want to delete the Nginx configuration files:</p>
<pre class="code-pre custom_prefix"><code langs=""><ul class="prefixed"><li class="line" prefix="loadbalancer$">sudo rm -r /etc/nginx
</li></ul></code></pre>
<p>Now we're ready to install and configure HAProxy.</p>

<h2 id="install-and-configure-haproxy">Install and Configure HAProxy</h2>

<p>Next, we will set up the HAProxy load balancers.  These will each sit in front of our web servers and split requests between the two backend app servers.  These load balancers will be completely redundant, in an active-passive configuration; only one will receive traffic at any given time.</p>

<p>The HAProxy configuration will pass requests to both of the web servers.  The load balancers will listen for requests on their anchor IP address.  As mentioned earlier, this is the IP address that the floating IP address will bind to when attached to the Droplet.  This ensures that only traffic originating from the floating IP address will be forwarded.</p>

<h3 id="install-haproxy">Install HAProxy</h3>

<p>This section needs to be performed on <strong>both load balancer servers</strong>.</p>

<p>We will install HAProxy 1.6, which is not in the default Ubuntu repositories. However, we can still use a package manager to install HAProxy 1.6 if we use a PPA, with this command:</p>
<pre class="code-pre custom_prefix"><code langs=""><ul class="prefixed"><li class="line" prefix="loadbalancer$">sudo add-apt-repository ppa:vbernat/haproxy-1.6
</li></ul></code></pre>
<p>Update the local package index on your load balancers and install HAProxy by typing:</p>
<pre class="code-pre custom_prefix"><code langs=""><ul class="prefixed"><li class="line" prefix="loadbalancer$">sudo apt-get update
</li><li class="line" prefix="loadbalancer$">sudo apt-get install haproxy
</li></ul></code></pre>
<p>HAProxy is now installed, but we need to configure it now.</p>

<h3 id="configure-haproxy">Configure HAProxy</h3>

<p>Open the main HAProxy configuration file:</p>
<pre class="code-pre custom_prefix"><code langs=""><ul class="prefixed"><li class="line" prefix="loadbalancer$">sudo vi /etc/haproxy/haproxy.cfg
</li></ul></code></pre>
<p>Find the <code>defaults</code> section, and add the two following lines under it:</p>
<div class="code-label " title="/etc/haproxy/haproxy.cfg (1 of 3)">/etc/haproxy/haproxy.cfg (1 of 3)</div><pre class="code-pre "><code langs="">    option forwardfor
    option http-server-close
</code></pre>
<p>The <em>forwardfor</em> option sets HAProxy to add <code>X-Forwarded-For</code> headers to each request—which is useful if you want your app servers to know which IP address originally sent a request—and the <em>http-server-close</em> option reduces latency between HAProxy and your users by closing connections but maintaining keep-alives.</p>

<p>Next, at the end of the file, we need to define our frontend configuration.  This will dictate how HAProxy listens for incoming connections.  We will bind HAProxy to the load balancer anchor IP address.  This will allow it to listen for traffic originating from the floating IP address.  We will call our frontend "http" for simplicity.  We will also specify a default backend, <code>app_pool</code>, to pass traffic to (which we will be configuring in a moment):</p>
<div class="code-label " title="/etc/haproxy/haproxy.cfg (2 of 3)">/etc/haproxy/haproxy.cfg (2 of 3)</div><pre class="code-pre "><code langs="">frontend http
    bind    <span class="highlight">load_balancer_anchor_IP</span>:80
    default_backend app_pool
</code></pre>
<p><span class="note"><strong>Note:</strong> The anchor IP is the only part of the HAProxy configuration that should differ between the load balancer servers. That is, be sure to specify the anchor IP of the load balancer server that you are currently working on.<br /></span></p>

<p>Next, we can define the backend configuration.  This will specify the downstream locations where HAProxy will pass the traffic it receives.  In our case, this will be the private IP addresses of both of the Nginx app servers we configured:</p>
<div class="code-label " title="/etc/haproxy/haproxy.cfg (3 of 3)">/etc/haproxy/haproxy.cfg (3 of 3)</div><pre class="code-pre "><code langs="">backend app_pool
    server app-1 <span class="highlight">app_server_1_private_IP</span>:80 check
    server app-2 <span class="highlight">app_server_2_private_IP</span>:80 check
</code></pre>
<p>When you are finished making the above changes, save and exit the file.</p>

<p>Check that the configuration changes we made represent valid HAProxy syntax by typing:</p>
<pre class="code-pre custom_prefix"><code langs=""><ul class="prefixed"><li class="line" prefix="loadbalancer$">sudo haproxy -f /etc/haproxy/haproxy.cfg -c
</li></ul></code></pre>
<p>If no errors were reported, restart your service by typing:</p>
<pre class="code-pre custom_prefix"><code langs=""><ul class="prefixed"><li class="line" prefix="loadbalancer$">sudo service haproxy restart
</li></ul></code></pre>
<p>Again, be sure to perform all of the steps in this section on both load balancer servers.</p>

<h3 id="testing-the-changes">Testing the Changes</h3>

<p>We can make sure our configuration is valid by testing with <code>curl</code> again.</p>

<p>From the load balancer servers, try to request the local host, the load balancer's own public IP address, or the server's own private IP address:</p>
<pre class="code-pre custom_prefix"><code langs=""><ul class="prefixed"><li class="line" prefix="loadbalancer$">curl 127.0.0.1
</li><li class="line" prefix="loadbalancer$">curl <span class="highlight">load_balancer_public_IP</span>
</li><li class="line" prefix="loadbalancer$">curl <span class="highlight">load_balancer_private_IP</span>
</li></ul></code></pre>
<p>These should all fail with messages that look similar to this:</p>
<pre class="code-pre "><code langs=""><div class="secondary-code-label " title="Output">Output</div>curl: (7) Failed to connect to <span class="highlight">IP_address</span> port 80: Connection refused
</code></pre>
<p>However, if you make a request to the load balancer's <em>anchor IP address</em>, it should complete successfully:</p>
<pre class="code-pre custom_prefix"><code langs=""><ul class="prefixed"><li class="line" prefix="loadbalancer$">curl <span class="highlight">load_balancer_anchor_IP</span>
</li></ul></code></pre>
<p>You should see the Nginx <code>index.html</code> page of one of the app servers:</p>
<pre class="code-pre "><code langs=""><div class="secondary-code-label " title="app server index.html">app server index.html</div>Droplet: app-1, IP Address: app1_IP_address
</code></pre>
<p>Perform the same curl request again:</p>
<pre class="code-pre custom_prefix"><code langs=""><ul class="prefixed"><li class="line" prefix="loadbalancer$">curl <span class="highlight">load_balancer_anchor_IP</span>
</li></ul></code></pre>
<p>You should see the <code>index.html</code> page of the other app server, because HAProxy uses round-robin load balancing by default:</p>
<pre class="code-pre "><code langs=""><div class="secondary-code-label " title="app server index.html">app server index.html</div>Droplet: app-2, IP Address: app2_IP_address
</code></pre>
<p>If this behavior matches that of your system, then your load balancers are configured correctly; you have successfully tested that your load balancer servers are balancing traffic between both backend app servers. Also, your floating IP should already be assigned to one of the load balancer servers, as that was set up in the prerequisite <strong>HA Setup with Corosync, Pacemaker, and Floating IPs</strong> tutorial.</p>

<h2 id="download-haproxy-ocf-resource-agent">Download HAProxy OCF Resource Agent</h2>

<p>At this point, you have a basic, host-level failover in place but we can improve the setup by adding HAProxy as a cluster resource. Doing so will allow your cluster to ensure that HAProxy is running on the server that your Floating IP is assigned to. If Pacemaker detects that HAProxy isn't running, it can restart the service or assign the Floating IP to the other node (that should be running HAProxy).</p>

<p>Pacemaker allows the addition of OCF resource agents by placing them in a specific directory.</p>

<p>On <strong>both load balancer servers</strong>, download the HAProxy OCF resource agent with these commands:</p>
<pre class="code-pre custom_prefix"><code langs=""><ul class="prefixed"><li class="line" prefix="loadbalancer$">cd /usr/lib/ocf/resource.d/heartbeat
</li><li class="line" prefix="loadbalancer$">sudo curl -O https://raw.githubusercontent.com/thisismitch/cluster-agents/master/haproxy
</li></ul></code></pre>
<p>On <strong>both load balancer servers</strong>, make it executable:</p>
<pre class="code-pre custom_prefix"><code langs=""><ul class="prefixed"><li class="line" prefix="loadbalancer$">sudo chmod +x haproxy
</li></ul></code></pre>
<p>Feel free to review the contents of the resource before continuing. It is a shell script that can be used to manage the HAProxy service.</p>

<p>Now we can use the HAProxy OCF resource agent to define our <code>haproxy</code> cluster resource.</p>

<h2 id="add-haproxy-resource">Add haproxy Resource</h2>

<p>With our HAProxy OCF resource agent installed, we can now configure an <code>haproxy</code> resource that will allow the cluster to manage HAProxy.</p>

<p>On <strong>either load balancer server</strong>, create the <code>haproxy</code> primitive resource with this command:</p>
<pre class="code-pre custom_prefix"><code langs=""><ul class="prefixed"><li class="line" prefix="loadbalancer$">sudo crm configure primitive haproxy ocf:heartbeat:haproxy op monitor interval=15s
</li></ul></code></pre>
<p>The specified resource tells the cluster to monitor HAProxy every 15 seconds, and to restart it if it becomes unavailable.</p>

<p>Check the status of your cluster resources by using <code>sudo crm_mon</code> or <code>sudo crm status</code>:</p>
<pre class="code-pre "><code langs=""><div class="secondary-code-label " title="crm_mon:">crm_mon:</div>...
Online: [ primary secondary ]

 FloatIP    (ocf::digitalocean:floatip):    Started <span class="highlight">primary</span>
 Nginx  (ocf::heartbeat:nginx): Started <span class="highlight">secondary</span>
</code></pre>
<p>Unfortunately, Pacemaker might decide to start the <code>haproxy</code> and <code>FloatIP</code> resources on separate nodes because we have not defined any resource constraints. This is a problem because the Floating IP might be pointing to one Droplet while the HAProxy service is running on the other Droplet. Accessing the Floating IP will point you to a server that is not running the service that should be highly available.</p>

<p>To resolve this issue, we'll create a <strong>clone</strong> resource, which specifies that an existing primitive resource should be started on multiple nodes.</p>

<p>Create a clone of the <code>haproxy</code> resource called "haproxy-clone" with this command:</p>
<pre class="code-pre custom_prefix"><code langs=""><ul class="prefixed"><li class="line" prefix="loadbalancer$">sudo crm configure clone haproxy-clone haproxy
</li></ul></code></pre>
<p>The cluster status should now look something like this:</p>
<pre class="code-pre "><code langs=""><div class="secondary-code-label " title="crm_mon:">crm_mon:</div>Online: [ primary secondary ]

FloatIP (ocf::digitalocean:floatip):    Started primary
 Clone Set: haproxy-clone [Nginx]
     Started: [ primary secondary ]
</code></pre>
<p>As you can see, the clone resource, <code>haproxy-clone</code>, is now started on both of our nodes.</p>

<p>The last step is to configure a colocation restraint, to specify that the <code>FloatIP</code> resource should run on a node with an active <code>haproxy-clone</code> resource. To create a colocation restraint called "FloatIP-haproxy", use this command:</p>
<pre class="code-pre custom_prefix"><code langs=""><ul class="prefixed"><li class="line" prefix="loadbalancer$">sudo crm configure colocation FloatIP-haproxy inf: FloatIP haproxy-clone
</li></ul></code></pre>
<p>You won't see any difference in the crm status output, but you can see that the colocation resource was created with this command:</p>
<pre class="code-pre custom_prefix"><code langs=""><ul class="prefixed"><li class="line" prefix="loadbalancer$">sudo crm configure show
</li></ul></code></pre>
<p>Now, both of your servers should have HAProxy running, while only one of them, has the FloatIP resource running.</p>

<p>Try stopping the HAProxy service on either load balancer server:</p>
<pre class="code-pre custom_prefix"><code langs=""><ul class="prefixed"><li class="line" prefix="loadbalancer$">sudo service haproxy stop
</li></ul></code></pre>
<p>You will notice that it will start up again sometime within the next 15 seconds.</p>

<p>Next, we'll test your HA setup by rebooting your active load balancer server (the one that the <code>FloatIP</code> resource is currently "started" on).</p>

<h2 id="test-high-availability-of-load-balancers">Test High Availability of Load Balancers</h2>

<p>With your new High Availability HAProxy setup, you will want test that everything works as intended.</p>

<p>In order to visualize the transition between the load balancers better, we can monitor the app server Nginx logs during the transition.</p>

<p>Since information about which proxy server is being used is not returned to the client, the best place to view the logs is from the actual backend web servers.  Each of these servers should maintain logs about which clients request assets.  From the Nginx service's perspective, the client is the load balancer that makes requests on behalf of the real client.</p>

<h3 id="monitor-the-cluster-status">Monitor the Cluster Status</h3>

<p>While performing the upcoming tests, you might want to look at the real-time status of the cluster nodes and resources. You can do so with this command, on either load balancer server (as long as it is running):</p>
<pre class="code-pre custom_prefix"><code langs=""><ul class="prefixed"><li class="line" prefix="loadbalancer$">sudo crm_mon
</li></ul></code></pre>
<p>The output should look something like this:</p>
<pre class="code-pre "><code langs=""><div class="secondary-code-label " title="crm_mon output:">crm_mon output:</div>Last updated: Thu Nov  5 13:51:41 2015
Last change: Thu Nov  5 13:51:27 2015 via cibadmin on primary
Stack: corosync
Current DC: secondary (2) - partition with quorum
Version: 1.1.10-42f2063
2 Nodes configured
3 Resources configured

Online: [ primary secondary ]

FloatIP (ocf::digitalocean:floatip):    Started primary
 Clone Set: haproxy-clone [haproxy]
     Started: [ primary secondary ]
</code></pre>
<p>This will show you which load balancer nodes are online, and which nodes the <code>FloatIP</code> and <code>haproxy</code> resources are started on.</p>

<p>Note that the node that the <code>FloatIP</code> resource is <code>Started</code> on, <strong>primary</strong> in the above example, is the load balancer server that the Floating IP is currently assigned to. We will refer to this server as the <strong>active load balancer server</strong>.</p>

<h3 id="automate-requests-to-the-floating-ip">Automate Requests to the Floating IP</h3>

<p>On your local machine, we will request the web content at the floating IP address once every 2 seconds.  This will allow us to easily see the how the active load balancer is handling incoming traffic. That is, we will see which backend app servers it is sending traffic to. In your local terminal, enter this command:</p>
<pre class="code-pre custom_prefix"><code langs=""><ul class="prefixed"><li class="line" prefix="local$">while true; do curl <span class="highlight">floating_IP_address</span>; sleep 2; done
</li></ul></code></pre>
<p>Every two seconds, you should see a response from one of the backend app servers. It will probably alternate between <strong>app-1</strong> and <strong>app-2</strong> because HAProxy's default balance algorithm, which we haven't specified, is set to <strong>round-robin</strong>. So, your terminal should show something like this:</p>
<pre class="code-pre "><code langs="">[secondary_label curl loop output:
Droplet: app-1, IP Address: <span class="highlight">app_1_IP_address</span>
Droplet: app-2, IP Address: <span class="highlight">app_2_IP_address</span>
...
</code></pre>
<p>Keep this terminal window open so that requests are continually sent to your servers. They will be helpful in our next testing steps.</p>

<h3 id="tail-the-logs-on-the-web-servers">Tail the Logs on the Web Servers</h3>

<p>On each of our backend app servers, we can <code>tail</code> the <code>/var/log/nginx/access.log</code> location.  This will show each request made to the server.  Since our load balancers split traffic evenly using a round-robin rotation, each backend app server should see about half of the requests made.</p>

<p>The client address is the very first field in the access log, so it will be easy to find. Run the following on <strong>both</strong> of your Nginx app servers (in separate terminal windows):</p>
<pre class="code-pre custom_prefix"><code langs=""><ul class="prefixed"><li class="line" prefix="appserver$">sudo tail -f /var/log/nginx/access.log
</li></ul></code></pre>
<p>The first field should show private IP address of your active load balancer server, every four seconds (we'll assume it's the <strong>primary</strong> load balancer, but it could be the <strong>secondary</strong> one in your case):</p>
<pre class="code-pre "><code langs=""><div class="secondary-code-label " title="Output">Output</div>. . .
<span class="highlight">primary_loadbalancer_IP</span> - - [05/Nov/2015:14:26:37 -0500] "GET / HTTP/1.1" 200 43 "-" "curl/7.43.0"
<span class="highlight">primary_loadbalancer_IP</span> - - [05/Nov/2015:14:26:37 -0500] "GET / HTTP/1.1" 200 43 "-" "curl/7.43.0"
. . .
</code></pre>
<p>Keep the <code>tail</code> command running on both of your app servers.</p>

<h3 id="interrupt-the-haproxy-service-on-the-primary-load-balancer">Interrupt the HAProxy Service on the Primary Load Balancer</h3>

<p>Now, let's reboot the <strong>primary</strong> load balancer, to make sure that the Floating IP failover works:</p>
<pre class="code-pre custom_prefix"><code langs=""><ul class="prefixed"><li class="line" prefix="primary_loadbalancer$">sudo reboot
</li></ul></code></pre>
<p>Now pay attention to the Nginx access logs on both of your app servers. You should notice that, after the Floating IP failover occurs, the access logs show that the app servers are being accessed by a different IP address than before. The logs should indicate that the <strong>secondary</strong> load balancer server is sending the requests:</p>
<pre class="code-pre "><code langs=""><div class="secondary-code-label " title="Output">Output</div>. . .
<span class="highlight">secondary_loadbalancer_IP</span> - - [05/Nov/2015:14:27:37 -0500] "GET / HTTP/1.1" 200 43 "-" "curl/7.43.0"
<span class="highlight">secondary_loadbalancer_IP</span> - - [05/Nov/2015:14:27:37 -0500] "GET / HTTP/1.1" 200 43 "-" "curl/7.43.0"
. . .
</code></pre>
<p>This shows that the failure of the primary load balancer was detected, and the Floating IP was reassigned to the secondary load balancer successfully.</p>

<p>You may also want to check the output of your local terminal (which is accessing the Floating IP every two seconds) to verify that the secondary load balancer is sending requests to both backend app servers:</p>
<pre class="code-pre "><code langs="">[secondary_label curl loop output:
Droplet: app-1, IP Address: <span class="highlight">app_1_IP_address</span>
Droplet: app-2, IP Address: <span class="highlight">app_2_IP_address</span>
...
</code></pre>
<p>You may also try the failover in the other direction, once the other load balancer is online again.</p>

<h2 id="configure-nginx-to-log-actual-client-ip-address">Configure Nginx to Log Actual Client IP Address</h2>

<p>As you have seen, the Nginx access logs show that all client requests are from the private IP address of the current load balancer, instead of the actual IP address of the client that originally made the request (i.e. your local machine). It is often useful to log the IP address of the original requestor, instead of the load balancer server. This is easily achieved by making a few changes to the Nginx configuration on all of your backend app servers.</p>

<p>On both <strong>app servers</strong>, open the <code>nginx.conf</code> file in an editor:</p>
<pre class="code-pre custom_prefix"><code langs=""><ul class="prefixed"><li class="line" prefix="webserver$">sudo vi /etc/nginx/nginx.conf
</li></ul></code></pre>
<p>Find the "Logging Settings" section (within the <code>http</code> block), and add the following line:</p>
<div class="code-label " title="add to /etc/nginx/nginx.conf">add to /etc/nginx/nginx.conf</div><pre class="code-pre "><code langs="">log_format haproxy_log 'ProxyIP: $remote_addr - ClientIP: $http_x_forwarded_for - $remote_user [$time_local] ' '"$request" $status $body_bytes_sent "$http_referer" ' '"$http_user_agent"';
</code></pre>
<p>Save and exit. This specifies a new log format called <code>haproxy_log</code>, which adds the <code>$http_x_forwarded_for</code> value—the IP address of the client that made the original request—to the default access log entries. We also are including <code>$remote_addr</code>, which is the IP address of the reverse proxy load balancer (i.e. the active load balancer server).</p>

<p>Next, to put this new log format to use, we need to add a line to our default server block.</p>

<p>On <strong>both app servers</strong>, open the <code>default</code> server configuration:</p>
<pre class="code-pre custom_prefix"><code langs=""><ul class="prefixed"><li class="line" prefix="webserver$">sudo vi /etc/nginx/sites-available/default
</li></ul></code></pre>
<p>Within the <code>server</code> block (right below the <code>listen</code> directive is a good place), add the following line:</p>
<div class="code-label " title="add to /etc/nginx/sites-available/default">add to /etc/nginx/sites-available/default</div><pre class="code-pre "><code langs="">        access_log /var/log/nginx/access.log haproxy_log;
</code></pre>
<p>Save and exit. This tells Nginx to write its access logs using the <code>haproxy_log</code> log format that we recently created.</p>

<p>On <strong>both app servers</strong>, restart Nginx to put the changes into effect:</p>
<pre class="code-pre custom_prefix"><code langs=""><ul class="prefixed"><li class="line" prefix="webserver$">sudo service nginx restart
</li></ul></code></pre>
<p>Now your Nginx access logs should contain the actual IP addresses of the clients making requests. Verify this by tailing the logs of your app servers, as we did in the previous section. The log entries should look something like this:</p>
<pre class="code-pre "><code langs=""><div class="secondary-code-label " title="New Nginx access logs:">New Nginx access logs:</div>. . .
ProxyIP: <span class="highlight">load_balancer_private_IP</span> - ClientIP: <span class="highlight">local_machine_IP</span> - - [05/Nov/2015:15:05:53 -0500] "GET / HTTP/1.1" 200 43 "-" "curl/7.43.0"
. . .
</code></pre>
<p>If your logs look good, you're all set!</p>

<h2 id="conclusion">Conclusion</h2>

<p>In this guide, we walked through the complete process of setting up a highly available, load balanced infrastructure.  This configuration works well because the active HAProxy server can distribute the load to the pool of app servers on the backend.  You can easily scale this pool as your demand grows or shrinks.</p>

<p>The Floating IP and Corosync/Pacemaker configuration eliminates the single point of failure at the load balancing layer, allowing your service to continue functioning even when the primary load balancer completely fails.  This configuration is fairly flexible and can be adapted to your own application environment by setting up your preferred application stack behind the HAProxy servers.</p>

    