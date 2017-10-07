<link rel='stylesheet' href='digital_ocean.css'><br><img src=https://community-cdn-digitalocean-com.global.ssl.fastly.net/assets/tutorials/images/large/hero-9d0b05e0.png?1445356568/> <br> 
      <h3 id="introduction">Introduction</h3>

<p>A IndiaReads Floating IP is a publicly-accessible static IP address that can be assigned to one of your Droplets. A Floating IP can also be instantly remapped, via the IndiaReads Control Panel or API, to one of your other Droplets in the same datacenter. This instant remapping capability grants you the ability to design and create High Availability (HA) server infrastructures—setups that do not have a single point of failure—by adding redundancy to the entry point, or gateway, to your servers. Achieving a complete HA setup also requires redundancy at every layer of your infrastructure, such as your application and database servers, which is often difficult to implement but can prove to be invaluable for reducing downtime and maintaining a happy user base.</p>

<p>Note that assigning a Floating IP to a Droplet <em>will not</em> replace the Droplet's original public IP address, which will remain unchanged. Instead, a Floating IP will provide an additional static IP address that can be used to access the Droplet it is currently assigned to.</p>

<p>This tutorial will cover the following topics:</p>

<ul>
<li>A Basic High Availability Setup</li>
<li>How To Manage IndiaReads Floating IPs</li>
<li>Droplet Anchor IPs</li>
<li>Floating IP Metadata</li>
<li>How To Implement an HA Setup</li>
<li>Other Floating IP Use Cases</li>
</ul>

<p>Let's look at a basic HA setup example to get started.</p>

<h2 id="a-basic-high-availability-setup">A Basic High Availability Setup</h2>

<p>The easiest way to learn about how an HA server setup works, if you are not familiar, is to look at a very basic one. The most basic HA server setup consists of a Floating IP that points to a set of, minimally, two load balancers in an active/passive configuration. This acts as the gateway layer of your server setup, which your users will access to get to your web servers.</p>

<p><img src="https://assets.digitalocean.com/articles/high_availability/ha-diagram-animated.gif" alt="Active/passive Diagram" /></p>

<p>Here is a description of each component in the diagram:</p>

<ul>
<li><strong>Active Server:</strong> The server that receives user traffic that is forwarded from the Floating IP. Typically, this is a load balancer that forwards the traffic to a backend of web application servers</li>
<li><strong>Passive Server:</strong> A standby server that is usually configured identically to the active server. It only will receive traffic during a failover event—i.e. if the active server becomes unavailable, and the Floating IP is remapped to the standby server</li>
<li><strong>Floating IP:</strong> The IP address that points to one of the servers, and can be remapped in the event of the failure of the active server</li>
</ul>

<p>It is important to note that the Floating IP does not automatically provide high availability by itself; a <strong>failover mechanism</strong>, which automates the process of detecting failures of the active server and reassigning the Floating IP to the passive server, must be devised and implemented for the setup to be considered highly available. Assuming that an effective failover strategy has been implemented, the above setup allows for the service to be available even if one of the servers fail.</p>

<p>There are several different ways to approach failover, which we will look at later, but let's look at how to use IndiaReads Floating IPs next.</p>

<h2 id="how-to-manage-digitalocean-floating-ips">How To Manage IndiaReads Floating IPs</h2>

<p>As with most IndiaReads resources, Floating IPs can be managed via the Control Panel or API. While the Control Panel allows you to create, reassign, and destroy Floating IPs, utilizing the API is necessary for implementing an effective automatic failover mechanism. We'll look at both methods for managing Floating IPs, starting with the Control Panel.</p>

<h3 id="control-panel">Control Panel</h3>

<p>To manage your Floating IPs via the IndiaReads Control Panel, click on the <strong>Networking</strong> link (top navigation menu), then <strong>Floating IPs</strong> in the left menu. The first time you visit the page, you will see a page that says you have no Floating IPs but you can create one:</p>

<p><img src="https://assets.digitalocean.com/site/ControlPanel/fip_no_floating_ips.png" alt="No Floating IPs" /></p>

<p>Here, you can create a Floating IP by selecting one of your Droplets and clicking the <strong>Assign Floating IP</strong> button. If you want to acquire a Floating IP without assigning it to a Droplet immediately, you can simply select a particular datacenter from the list.</p>

<p><span class="note"><strong>Note:</strong> If you assign a Floating IP to a Droplet that was created before October 20, 2015, you will be presented with a modal message that will include <a href="https://indiareads/community/tutorials/how-to-enable-floating-ips-on-an-older-droplet">instructions</a> that must be followed before a Floating IP can be assigned to that Droplet. This will create an <em>anchor IP</em> on your Droplet, which we will discuss later in this tutorial.<br /></span></p>

<p>After a few moments, you will have a new Floating IP that points to the Droplet that you selected:</p>

<p><img src="https://assets.digitalocean.com/site/ControlPanel/fip_assigned_to_primary.png" alt="Floating IP Assigned" /></p>

<p>If you have at least one Floating IP, this page will display a list of your Floating IPs, which includes the following details about each entry:</p>

<ul>
<li><strong>Address:</strong> The Floating IP address, which is how it can be accessed and also how it is internally identified</li>
<li><strong>Datacenter:</strong> The datacenter in which the Floating IP was created. A Floating IP can only be assigned to Droplets within the same datacenter</li>
<li><strong>Droplet:</strong> The Droplet that the Floating IP is assigned to. Requests sent to the Floating IP address will be directed to this Droplet. This can also be set to "Unassigned", which means the Floating IP is reserved but will not pass network traffic to any Droplet</li>
</ul>

<p>In addition to the Floating IP information, this section also allows you to perform the following actions:</p>

<ul>
<li><strong>Reassign (Blue Pen Button):</strong> Assign the Floating IP to a different Droplet, within the same datacenter. You may also unassign the Floating IP</li>
<li><strong>Delete (Red X Button):</strong> Release the Floating IP from your account</li>
</ul>

<p><img src="https://assets.digitalocean.com/site/ControlPanel/fip_reassign.png" alt="Reassign Floating IP" /></p>

<p>Now that you are familiar with managing Floating IPs through the Control Panel, let's take a look at using the API.</p>

<h3 id="api">API</h3>

<p>The IndiaReads API allows you to perform all of the Floating IPs management actions that you can do through the Control Panel, except it allows you to programmatically make changes. This is particularly useful because an HA setup requires the ability to automate the reassignment of a Floating IP to other Droplets.</p>

<p>The API allows you to perform the following Floating IP actions:</p>

<ul>
<li>List Floating IPs</li>
<li>Reserve a new Floating IP to a region</li>
<li>Assign new Floating IP to a Droplet</li>
<li>Reassign Floating IP to a Droplet</li>
<li>Unassign Floating IP</li>
<li>Delete Floating IP</li>
</ul>

<p>If you aren't familiar with the IndiaReads API yet, check out this tutorial: <a href="https://indiareads/community/tutorials/how-to-use-the-digitalocean-api-v2">How To Use the IndiaReads API v2</a>. Full documentation of the IndiaReads API, which covers Floating IPs, can be found here: <a href="https://developers.digitalocean.com/documentation/v2/">IndiaReads API Documentation</a>.</p>

<p><span class="note"><strong>Note:</strong> The official IndiaReads API wrappers for Ruby (<a href="https://github.com/digitalocean/droplet_kit">DropletKit</a>) and Go (<a href="https://github.com/digitalocean/godo">Go</a>) have been updated with full Floating IP support.<br /></span></p>

<p>We won't get into all of the details of managing your Floating IPs via the API but we'll show a quick example. Here's an example of using the <code>curl</code> command to reassign an existing Floating IP to a Droplet:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">curl -X POST -H 'Content-Type: application/json' -H 'Authorization: Bearer <span class="highlight">your_api_token</span>' -d '{ "type": "assign", "droplet_id": <span class="highlight">5000</span> }' "https://api.digitalocean.com/v2/floating_ips/<span class="highlight">8.8.8.9</span>/actions" 
</li></ul></code></pre>
<p>Assuming that you replaced the highlighted values with real values, such as your API token, the target Droplet ID, and the Floating IP address to reassign, this would point <code>8.8.8.9</code> (the Floating IP) to your Droplet with an ID of <code>5000</code>.</p>

<p>Now that you are familiar with managing your Floating IPs with the Control Panel and API, let's take a look at how Floating IPs communicate with Droplets.</p>

<h2 id="droplet-anchor-ips">Droplet Anchor IPs</h2>

<p>Network traffic between a Floating IP and a Droplet flows through an <strong>anchor IP</strong>, which is an IP address that is aliased to a Droplet's public network interface (<code>eth0</code>). As such, a Droplet must have an anchor IP before a Floating IP can be assigned to it. Droplets created after October 20, 2015 will automatically have an anchor IP. If you have a Droplet that was created before this, you can add an anchor IP by following the instructions in the <a href="https://indiareads/community/tutorials/how-to-enable-floating-ips-on-an-older-droplet">How to Enable Floating IPs on an Older Droplet</a> tutorial.</p>

<p>An anchor IP is only accessible to the Droplet that it belongs to, and to a Floating IP that is assigned to the Droplet. The implication of this is that the anchor IP is where you should bind any public services that you want to make highly available through a Floating IP. For example, if you are using a Floating IP in an active/passive load balancer setup, you should bind your load balancer services to their respective Droplet anchor IPs so they can only be accessed via the Floating IP address. This will prevent your users from using the public IP addresses of your Droplets to bypass your Floating IP.</p>

<h3 id="how-to-retrieve-your-anchor-ip">How To Retrieve Your Anchor IP</h3>

<p>The easiest way to retrieve your Droplet's Anchor IP is to use the <a href="https://indiareads/community/tutorials/an-introduction-to-droplet-metadata">Droplet Metadata</a> service. Anchor IP information, like any data stored in Metadata, can be retrieved by running basic <code>curl</code> commands from the command line of a Droplet.</p>

<p>This command will retrieve the anchor IP address of a Droplet:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">curl -s http://169.254.169.254/metadata/v1/interfaces/public/0/anchor_ipv4/address
</li></ul></code></pre>
<p>This command will retrieve the netmask of a Droplet's anchor IP:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">curl -s http://169.254.169.254/metadata/v1/interfaces/public/0/anchor_ipv4/netmask
</li></ul></code></pre>
<p>This command will retrieve the gateway of a Droplet's anchor IP:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">curl -s http://169.254.169.254/metadata/v1/interfaces/public/0/anchor_ipv4/gateway
</li></ul></code></pre>
<p>The other way to look up information about your Droplet's anchor IP is to use the <code>ip addr</code> command:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">ip addr show eth0
</li></ul></code></pre>
<p>The anchor IP (highlighted) will be under your normal public IP address information:</p>
<pre class="code-pre "><code langs="">[secondary_output Example output:]
2: eth0: <BROADCAST,MULTICAST,UP,LOWER_UP> mtu 1500 qdisc pfifo_fast state UP group default qlen 1000
    link/ether 04:01:7d:c2:a2:01 brd ff:ff:ff:ff:ff:ff
    inet 159.203.90.122/20 brd 159.203.95.255 scope global eth0
       valid_lft forever preferred_lft forever
    inet <span class="highlight">10.17.0.47</span>/16 scope global eth0
       valid_lft forever preferred_lft forever
    inet6 fe80::601:7dff:fec2:a201/64 scope link
       valid_lft forever preferred_lft forever
</code></pre>
<h2 id="floating-ip-metadata">Floating IP Metadata</h2>

<p>A Droplet can see if it has a Floating IP assigned to itself by using the <a href="https://indiareads/community/tutorials/an-introduction-to-droplet-metadata">Droplet Metadata</a> service. If a Floating IP is assigned, the Droplet can also retrieve the address of the Floating IP. This information can be very useful when implementing an HA server setup.</p>

<p>Like any information stored in Metadata, these details can be retrieved by running basic <code>curl</code> commands from the command line of a Droplet.</p>

<p>To see if a Droplet has a Floating IP assigned to itself, run this command:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">curl -s http://169.254.169.254/metadata/v1/floating_ip/ipv4/active
</li></ul></code></pre>
<p>If a Floating IP is assigned to the Droplet, you may retrieve its address with this command:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">curl -s http://169.254.169.254/metadata/v1/floating_ip/ipv4/ip_address
</li></ul></code></pre>
<p>Full documentation of Droplet Metadata can be found <a href="https://developers.digitalocean.com/documentation/metadata/">here</a>.</p>

<h2 id="how-to-implement-an-ha-setup">How To Implement an HA Setup</h2>

<p>Now that you are familiar with how IndiaReads Floating IPs work, you are ready to start building your own high availability server setups. If you need help getting started, here are several tutorials that will walk you through the creation of various active/passive high availability setups. Each tutorial uses a different software solution to achieve HA, so feel free to choose the one that is the best fit for your needs.</p>

<h3 id="corosync-and-pacemaker">Corosync and Pacemaker</h3>

<p>Corosync and Pacemaker provide a cluster software package that can be used to create an effective HA setup. Corosync provides a messaging layer, which enables servers to communicate as a cluster, while Pacemaker provides the ability to control how the cluster behaves. This tutorial will demonstrate how you can use Corosync and Pacemaker with a Floating IP to create an active/passive HA server infrastructure on IndiaReads: <a href="https://indiareads/community/tutorials/how-to-create-a-high-availability-setup-with-corosync-pacemaker-and-floating-ips-on-ubuntu-14-04">How To Create a High Availability Setup with Corosync, Pacemaker, and Floating IPs on Ubuntu 14.04</a></p>

<h3 id="keepalived">Keepalived</h3>

<p>Keepalived is a service that can monitor servers or processes in order to implement high availability on your infrastructure.  This guide uses the <code>keepalived</code> daemon to monitor two web servers.  The secondary server will take over the web traffic if the primary server experiences issues by automatically claiming ownership of a shared Floating IP address using the IndiaReads API: <a href="https://indiareads/community/tutorials/how-to-set-up-highly-available-web-servers-with-keepalived-and-floating-ips-on-ubuntu-14-04">How To Set Up Highly Available Web Servers with Keepalived and Floating IPs on Ubuntu 14.04</a></p>

<h3 id="heartbeat">Heartbeat</h3>

<p>Heartbeat provides clustering functionality that can be used with Floating IPs to implement a basic active/passive high availability server setup. This setup is not recommended for production use, but it effectively demonstrates how a simple HA server setup can be achieved: <a href="https://indiareads/community/tutorials/how-to-create-a-high-availability-setup-with-heartbeat-and-floating-ips-on-ubuntu-14-04">How To Create a High Availability Setup with Heartbeat and Floating IPs on Ubuntu 14.04</a></p>

<h2 id="other-floating-ip-use-cases">Other Floating IP Use Cases</h2>

<p>Another way to leverage Floating IPs is Blue-green deployment, a strategy for deploying and releasing software.  It relies on maintaining two separate production-capable environments, nicknamed blue and green for ease of discussion.  This guide, discusses how to use blue-green deployments on IndiaReads to simplify the process of transitioning your users to a new version of your software: <a href="https://indiareads/community/tutorials/how-to-use-blue-green-deployments-to-release-software-safely">How To Use Blue-Green Deployments to Release Software Safely</a></p>

<h2 id="conclusion">Conclusion</h2>

<p>You should now understand how IndiaReads Floating IPs work, and how they can be used to create a high availability server infrastructure.</p>

<p>If you have any questions about Floating IPs or high availability, please leave them in the comments below!</p>

    