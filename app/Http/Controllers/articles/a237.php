<link rel='stylesheet' href='digital_ocean.css'><br> <br> 
      <h3 id="introduction">Introduction</h3>

<p>High availability is a function of system design that allows an application to automatically restart or reroute work to another capable system in the event of a failure.  In terms of servers, there are a few different technologies needed to set up a highly available system.  There must be a component that can redirect the work and there must be a mechanism to monitor for failure and transition the system if an interruption is detected.</p>

<p>The <code>keepalived</code> daemon can be used to monitor services or systems and to automatically failover to a standby if problems occur.  In this guide, we will demonstrate how to use <code>keepalived</code> to set up high availability for your load balancers.  We will configure a <a href="https://indiareads/community/tutorials/how-to-use-floating-ips-on-digitalocean">floating IP address</a> that can be moved between two capable load balancers.  These will each be configured to split traffic between two backend web servers.  If the primary load balancer goes down, the floating IP will be moved to the second load balancer automatically, allowing service to resume.</p>

<p><img src="https://assets.digitalocean.com/articles/high_availability/ha-diagram-animated.gif" alt="High Availability diagram" /></p>

<h2 id="prerequisites">Prerequisites</h2>

<p>In order to complete this guide, you will need to create four Ubuntu 14.04 servers in your IndiaReads account.  All of the servers must be located within the same datacenter and should have private networking enabled.</p>

<p>On each of these servers, you will need a non-root user configured with <code>sudo</code> access.  You can follow our <a href="https://indiareads/community/tutorials/initial-server-setup-with-ubuntu-14-04">Ubuntu 14.04 initial server setup guide</a> to learn how to set up these users.</p>

<h2 id="finding-server-network-information">Finding Server Network Information</h2>

<p>Before we begin the actual configuration of our infrastructure components, it is best to gather some information about each of your servers.</p>

<p>To complete this guide, you will need to have the following information about your servers:</p>

<ul>
<li><strong>web servers</strong>: Private IP address</li>
<li><strong>load balancers</strong> Private and Anchor IP addresses</li>
</ul>

<h3 id="finding-private-ip-addresses">Finding Private IP Addresses</h3>

<p>The easiest way to find your Droplet's private IP address is to use <code>curl</code> to grab the private IP address from the IndiaReads metadata service.  This command should be run from within your Droplets.  On each Droplet, type:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">curl 169.254.169.254/metadata/v1/interfaces/private/0/ipv4/address && echo
</li></ul></code></pre>
<p>The correct IP address should be printed in the terminal window:</p>
<pre class="code-pre "><code langs=""><div class="secondary-code-label " title="Output">Output</div>10.132.20.236
</code></pre>
<h3 id="finding-anchor-ip-addresses">Finding Anchor IP Addresses</h3>

<p>The "anchor IP" is the local private IP address that the floating IP will bind to when attached to a IndiaReads server.  It is simply an alias for the regular <code>eth0</code> address, implemented at the hypervisor level.</p>

<p>The easiest, least error-prone way of grabbing this value is straight from the IndiaReads metadata service.  Using <code>curl</code>, you can reach out to this endpoint on each of your servers by typing:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">curl 169.254.169.254/metadata/v1/interfaces/public/0/anchor_ipv4/address && echo
</li></ul></code></pre>
<p>The anchor IP will be printed on its own line:</p>
<pre class="code-pre "><code langs=""><div class="secondary-code-label " title="Output">Output</div>10.17.1.18
</code></pre>
<h2 id="install-and-configure-the-web-server">Install and Configure the Web Server</h2>

<p>After gathering the data above, we can move on to configuring our services.</p>

<p></p><div class="code-label notes-and-warnings note" title="Note">Note</div><span class="note">
In this setup, the software selected for the web server layer is fairly interchangeable.  This guide will use Nginx because it is generic and rather easy to configure.  If you are more comfortable with Apache or a (production-capable) language-specific web server, feel free to use that instead.  HAProxy will simply pass client requests to the backend web servers which can handle the requests similarly to how it would handle direct client connections.<br /></span>

<p>We will start off by setting up our backend web servers.  Both of these servers will serve exactly the same content.  They will only accept web connections over their private IP addresses.  This will help ensure that traffic is directed through one of the two HAProxy servers we will be configuring later.</p>

<p>Setting up web servers behind a load balancer allows us to distribute the request burden among some number identical web servers.  As our traffic needs change, we can easily scale to meet the new demands by adding or removing web servers from this tier.</p>

<h3 id="installing-nginx">Installing Nginx</h3>

<p>We will be installing Nginx on our web serving machines to provide this functionality.</p>

<p>Start off by logging in with your <code>sudo</code> user to the two machines that you wish to use as the web servers.  Update the local package index on each of your web servers and install Nginx by typing:</p>
<pre class="code-pre custom_prefix"><code langs=""><ul class="prefixed"><li class="line" prefix="webserver$">sudo apt-get update
</li><li class="line" prefix="webserver$">sudo apt-get install nginx
</li></ul></code></pre>
<h3 id="configure-nginx-to-only-allow-requests-from-the-load-balancers">Configure Nginx to Only Allow Requests from the Load Balancers</h3>

<p>Next, we will configure our Nginx instances.  We want to tell Nginx to only listen for requests on the private IP address of the server.  Furthermore, we will only serve requests coming from the private IP addresses of our two load balancers.</p>

<p>To make these changes, open the default Nginx server block file on each of your web servers:</p>
<pre class="code-pre custom_prefix"><code langs=""><ul class="prefixed"><li class="line" prefix="webserver$">sudo nano /etc/nginx/sites-available/default
</li></ul></code></pre>
<p>To start, we will modify the <code>listen</code> directives.  Change the <code>listen</code> directive to listen to the current <strong>web server's private IP address</strong> on port 80.  Delete the extra <code>listen</code> line.  It should look something like this:</p>
<div class="code-label " title="/etc/nginx/sites-available/default">/etc/nginx/sites-available/default</div><pre class="code-pre "><code langs="">server {
    listen <span class="highlight">web_server_private_IP</span>:80;

    . . .
</code></pre>
<p>Afterwards, we will set up two <code>allow</code> directives to permit traffic originating from the private IP addresses of our two load balancers.  We will follow this up with a <code>deny all</code> rule to forbid all other traffic:</p>
<div class="code-label " title="/etc/nginx/sites-available/default">/etc/nginx/sites-available/default</div><pre class="code-pre "><code langs="">server {
    listen <span class="highlight">web_server_private_IP</span>:80;

    allow <span class="highlight">load_balancer_1_private_IP</span>;
    allow <span class="highlight">load_balancer_2_private_IP</span>;
    deny all;

    . . .
</code></pre>
<p>Save and close the files when you are finished.</p>

<p>Test that the changes that you made represent valid Nginx syntax by typing:</p>
<pre class="code-pre custom_prefix"><code langs=""><ul class="prefixed"><li class="line" prefix="webserver$">sudo nginx -t
</li></ul></code></pre>
<p>If no problems were reported, restart the Nginx daemon by typing:</p>
<pre class="code-pre custom_prefix"><code langs=""><ul class="prefixed"><li class="line" prefix="webserver$">sudo service nginx restart
</li></ul></code></pre>
<h3 id="testing-the-changes">Testing the Changes</h3>

<p>To test that your web servers are restricted correctly, you can make requests using <code>curl</code> from various locations.</p>

<p>On your web servers themselves, you can try a simple request of the local content by typing:</p>
<pre class="code-pre custom_prefix"><code langs=""><ul class="prefixed"><li class="line" prefix="webserver$">curl 127.0.0.1
</li></ul></code></pre>
<p>Because of the restrictions we set in place in our Nginx server block files, this request will actually be denied:</p>
<pre class="code-pre "><code langs=""><div class="secondary-code-label " title="Output">Output</div>curl: (7) Failed to connect to 127.0.0.1 port 80: Connection refused
</code></pre>
<p>This is expected and reflects the behavior that we were attempting to implement.</p>

<p>Now, from either of the <strong>load balancers</strong>, we can make a request for either of our web server's public IP address:</p>
<pre class="code-pre custom_prefix"><code langs=""><ul class="prefixed"><li class="line" prefix="loadbalancer$">curl <span class="highlight">web_server_public_IP</span>
</li></ul></code></pre>
<p>Once again, this should fail.  The web servers are not listening on the public interface and furthermore, when using the public IP address, our web servers would not see the allowed private IP addresses in the request from our load balancers:</p>
<pre class="code-pre "><code langs=""><div class="secondary-code-label " title="Output">Output</div>curl: (7) Failed to connect to <span class="highlight">web_server_public_IP</span> port 80: Connection refused
</code></pre>
<p>However, if we modify the call to make the request using the web server's <em>private IP address</em>, it should work correctly:</p>
<pre class="code-pre custom_prefix"><code langs=""><ul class="prefixed"><li class="line" prefix="loadbalancer$">curl <span class="highlight">web_server_private_IP</span>
</li></ul></code></pre>
<p>The default Nginx <code>index.html</code> page should be returned:</p>
<pre class="code-pre "><code langs=""><div class="secondary-code-label " title="Output">Output</div><!DOCTYPE html>
<html>
<head>
<title>Welcome to nginx!</title>

. . .
</code></pre>
<p>Test this from both load balancers to both web servers.  Each request for the private IP address should succeed while each request made to the public addresses should fail.</p>

<p>Once the above behavior is demonstrated, we can move on.  Our backend web server configuration is now complete.</p>

<h2 id="install-and-configure-haproxy">Install and Configure HAProxy</h2>

<p>Next, we will set up the HAProxy load balancers.  These will each sit in front of our web servers and split requests between the two backend servers.  These load balancers are completely redundant.  Only one will receive traffic at any given time.</p>

<p>The HAProxy configuration will pass requests to both of the web servers.  The load balancers will listen for requests on their anchor IP address.  As mentioned earlier, this is the IP address that the floating IP address will bind to when attached to the Droplet.  This ensures that only traffic originating from the floating IP address will be forwarded.</p>

<h3 id="install-haproxy">Install HAProxy</h3>

<p>The first step we need to take on our load balancers will be to install the <code>haproxy</code> package.  We can find this in the default Ubuntu repositories.  Update the local package index on your load balancers and install HAProxy by typing:</p>
<pre class="code-pre custom_prefix"><code langs=""><ul class="prefixed"><li class="line" prefix="loadbalancer$">sudo apt-get update
</li><li class="line" prefix="loadbalancer$">sudo apt-get install haproxy
</li></ul></code></pre>
<h3 id="configure-haproxy">Configure HAProxy</h3>

<p>The first item we need to modify when dealing with HAProxy is the <code>/etc/default/haproxy</code> file.  Open that file now in your editor:</p>
<pre class="code-pre custom_prefix"><code langs=""><ul class="prefixed"><li class="line" prefix="loadbalancer$">sudo nano /etc/default/haproxy
</li></ul></code></pre>
<p>This file determines whether HAProxy will start at boot.  Since we want the service to start automatically each time the server powers on, we need to change the value of <code>ENABLED</code> to "1":</p>
<div class="code-label " title="/etc/default/haproxy">/etc/default/haproxy</div><pre class="code-pre "><code langs=""># Set ENABLED to 1 if you want the init script to start haproxy.
ENABLED=<span class="highlight">1</span>
# Add extra flags here.
#EXTRAOPTS="-de -m 16"
</code></pre>
<p>Save and close the file after making the above edit.</p>

<p>Next, we can open the main HAProxy configuration file:</p>
<pre class="code-pre custom_prefix"><code langs=""><ul class="prefixed"><li class="line" prefix="loadbalancer$">sudo nano /etc/haproxy/haproxy.cfg
</li></ul></code></pre>
<p>The first item that we need to adjust is the mode that HAProxy will be operating in.  We want to configure TCP, or layer 4, load balancing.  To do this, we need to alter the <code>mode</code> line in the <code>default</code> section.  We should also change the option immediately following that deals with the log:</p>
<div class="code-label " title="/etc/haproxy/haproxy.cfg">/etc/haproxy/haproxy.cfg</div><pre class="code-pre "><code langs="">. . .

defaults
    log     global
    mode    <span class="highlight">tcp</span>
    option  <span class="highlight">tcplog</span>

. . .
</code></pre>
<p>At the end of the file, we need to define our front end configuration.  This will dictate how HAProxy listens for incoming connections.  We will bind HAProxy to the load balancer anchor IP address.  This will allow it to listen for traffic originating from the floating IP address.  We will call our front end "www" for simplicity.  We will also specify a default backend to pass traffic to (which we will be configuring in a moment):</p>
<div class="code-label " title="/etc/haproxy/haproxy.cfg">/etc/haproxy/haproxy.cfg</div><pre class="code-pre "><code langs="">. . .

defaults
    log     global
    mode    <span class="highlight">tcp</span>
    option  <span class="highlight">tcplog</span>

. . .

frontend www
    bind    <span class="highlight">load_balancer_anchor_IP</span>:80
    default_backend nginx_pool
</code></pre>
<p>Next, we can configure our backend section.  This will specify the downstream locations where HAProxy will pass the traffic it receives.  In our case, this will be the private IP addresses of both of the Nginx web servers we configured.  We will specify traditional round-robin balancing and will set the mode to "tcp" again:</p>
<div class="code-label " title="/etc/haproxy/haproxy.cfg">/etc/haproxy/haproxy.cfg</div><pre class="code-pre "><code langs="">. . .

defaults
    log     global
    mode    <span class="highlight">tcp</span>
    option  <span class="highlight">tcplog</span>

. . .

frontend www
    bind <span class="highlight">load_balancer_anchor_IP</span>:80
    default_backend nginx_pool

backend nginx_pool
    balance roundrobin
    mode tcp
    server web1 <span class="highlight">web_server_1_private_IP</span>:80 check
    server web2 <span class="highlight">web_server_2_private_IP</span>:80 check
</code></pre>
<p>When you are finished making the above changes, save and close the file.</p>

<p>Check that the configuration changes we made represent valid HAProxy syntax by typing:</p>
<pre class="code-pre custom_prefix"><code langs=""><ul class="prefixed"><li class="line" prefix="loadbalancer$">sudo haproxy -f /etc/haproxy/haproxy.cfg -c
</li></ul></code></pre>
<p>If no errors were reported, restart your service by typing:</p>
<pre class="code-pre custom_prefix"><code langs=""><ul class="prefixed"><li class="line" prefix="loadbalancer$">sudo service haproxy restart
</li></ul></code></pre>
<h3 id="testing-the-changes">Testing the Changes</h3>

<p>We can make sure our configuration is valid by testing with <code>curl</code> again.</p>

<p>From the load balancer servers, try to request the local host, the load balancer's own public IP address, or the server's own private IP address:</p>
<pre class="code-pre custom_prefix"><code langs=""><ul class="prefixed"><li class="line" prefix="loadbalancer$">curl 127.0.0.1
</li><li class="line" prefix="loadbalancer$">curl <span class="highlight">load_balancer_public_IP</span>
</li><li class="line" prefix="loadbalancer$">curl <span class="highlight">load_balancer_private_IP</span>
</li></ul></code></pre>
<p>These should all fail with messages that look similar to this:</p>
<pre class="code-pre "><code langs=""><div class="secondary-code-label " title="Output">Output</div>curl: (7) Failed to connect to <span class="highlight">address</span> port 80: Connection refused
</code></pre>
<p>However, if you make a request to the load balancer's <em>anchor IP address</em>, it should complete successfully:</p>
<pre class="code-pre custom_prefix"><code langs=""><ul class="prefixed"><li class="line" prefix="loadbalancer$">curl <span class="highlight">load_balancer_anchor_IP</span>
</li></ul></code></pre>
<p>You should see the default Nginx <code>index.html</code> page, routed from one of the two backend web servers:</p>
<pre class="code-pre "><code langs=""><div class="secondary-code-label " title="Output">Output</div><!DOCTYPE html>
<html>
<head>
<title>Welcome to nginx!</title>

. . .
</code></pre>
<p>If this behavior matches that of your system, then your load balancers are configured correctly.</p>

<h2 id="build-and-install-keepalived">Build and Install Keepalived</h2>

<p>Our actual service is now up and running.  However, our infrastructure is not highly available yet because we have no way of redirecting traffic if our active load balancer experiences problems.  In order to rectify this, we will install the <code>keepalived</code> daemon on our load balancer servers.  This is the component that will provide failover capabilities if our active load balancer becomes unavailable.</p>

<p>There is a version of <code>keepalived</code> in Ubuntu's default repositories, but it is outdated and suffers from a few bugs that would prevent our configuration from working.  Instead, we will install the latest version of <code>keepalived</code> from source.</p>

<p>Before we begin, we should grab the dependencies we will need to build the software.  The <code>build-essential</code> meta-package will provide the compilation tools we need, while the <code>libssl-dev</code> package contains the SSL development libraries that <code>keepalived</code> needs to build against:</p>
<pre class="code-pre custom_prefix"><code langs=""><ul class="prefixed"><li class="line" prefix="loadbalancer$">sudo apt-get install build-essential libssl-dev
</li></ul></code></pre>
<p>Once the dependencies are in place, we can download the tarball for <code>keepalived</code>.  Visit <a href="http://www.keepalived.org/download.html">this page</a> to find the latest version of the software.  Right-click on the latest version and copy the link address.  Back on your servers, move to your home directory and use <code>wget</code> to grab the link you copied:</p>
<pre class="code-pre custom_prefix"><code langs=""><ul class="prefixed"><li class="line" prefix="loadbalancer$">cd ~
</li><li class="line" prefix="loadbalancer$">wget http://www.keepalived.org/software/keepalived-<span class="highlight">1.2.19</span>.tar.gz
</li></ul></code></pre>
<p>Use the <code>tar</code> command to expand the archive.  Move into the resulting directory:</p>
<pre class="code-pre custom_prefix"><code langs=""><ul class="prefixed"><li class="line" prefix="loadbalancer$">tar xzvf keepalived*
</li><li class="line" prefix="loadbalancer$">cd keepalived*
</li></ul></code></pre>
<p>Build and install the daemon by typing:</p>
<pre class="code-pre custom_prefix"><code langs=""><ul class="prefixed"><li class="line" prefix="loadbalancer$">./configure
</li><li class="line" prefix="loadbalancer$">make
</li><li class="line" prefix="loadbalancer$">sudo make install
</li></ul></code></pre>
<p>The daemon should now be installed on both of the load balancer systems.</p>

<h2 id="create-a-keepalived-upstart-script">Create a Keepalived Upstart Script</h2>

<p>The <code>keepalived</code> installation moved all of the binaries and supporting files into place on our system.  However, one piece that was not included was an Upstart script for our Ubuntu 14.04 systems.</p>

<p>We can create a very simple Upstart script that can handle our <code>keepalived</code> service.  Open a file called <code>keepalived.conf</code> within the <code>/etc/init</code> directory to get started:</p>
<pre class="code-pre custom_prefix"><code langs=""><ul class="prefixed"><li class="line" prefix="loadbalancer$">sudo nano /etc/init/keepalived.conf
</li></ul></code></pre>
<p>Inside, we can start with a simple description of the functionality <code>keepalived</code> provides.  We'll use the description from the included <code>man</code> page.  Next we will specify the runlevels in which the service should be started and stopped.  We want this service to be active in all normal conditions (runlevels 2-5) and stopped for all other runlevels (when reboot, poweroff, or single-user mode is initiated, for instance):</p>
<div class="code-label " title="/etc/init/keepalived.conf">/etc/init/keepalived.conf</div><pre class="code-pre "><code langs="">description "load-balancing and high-availability service"

start on runlevel [2345]
stop on runlevel [!2345]
</code></pre>
<p>Because this service is integral to ensuring our web service remains available, we want to restart this service in the event of a failure.  We can then specify the actual <code>exec</code> line that will start the service.  We need to add the <code>--dont-fork</code> option so that Upstart can track the <code>pid</code> correctly:</p>
<div class="code-label " title="/etc/init/keepalived.conf">/etc/init/keepalived.conf</div><pre class="code-pre "><code langs="">description "load-balancing and high-availability service"

start on runlevel [2345]
stop on runlevel [!2345]

respawn

exec /usr/local/sbin/keepalived --dont-fork
</code></pre>
<p>Save and close the files when you are finished.</p>

<h2 id="create-the-keepalived-configuration-file">Create the Keepalived Configuration File</h2>

<p>With our Upstart files in place, we can now move on to configuring <code>keepalived</code>.</p>

<p>The service looks for its configuration files in the <code>/etc/keepalived</code> directory.  Create that directory now on both of your load balancers:</p>
<pre class="code-pre custom_prefix"><code langs=""><ul class="prefixed"><li class="line" prefix="loadbalancer$">sudo mkdir -p /etc/keepalived
</li></ul></code></pre>
<h3 id="creating-the-primary-load-balancer-39-s-configuration">Creating the Primary Load Balancer's Configuration</h3>

<p>Next, on the load balancer server that you wish to use as your <strong>primary</strong> server, create the main <code>keepalived</code> configuration file.  The daemon looks for a file called <code>keepalived.conf</code> inside of the <code>/etc/keepalived</code> directory:</p>
<pre class="code-pre custom_prefix"><code langs=""><ul class="prefixed"><li class="line" prefix="primary$">sudo nano /etc/keepalived/keepalived.conf
</li></ul></code></pre>
<p>Inside, we will start by defining a health check for our HAProxy service by opening up a <code>vrrp_script</code> block.  This will allow <code>keepalived</code> to monitor our load balancer for failures so that it can signal that the process is down and begin recover measures.</p>

<p>Our check will be very simple.  Every two seconds, we will check that a process called <code>haproxy</code> is still claiming a <code>pid</code>:</p>
<div class="code-label " title="Primary server's /etc/keepalived/keepalived.conf">Primary server's /etc/keepalived/keepalived.conf</div><pre class="code-pre "><code langs="">vrrp_script chk_haproxy {
    script "pidof haproxy"
    interval 2
}
</code></pre>
<p>Next, we will open a block called <code>vrrp_instance</code>.  This is the main configuration section that defines the way that <code>keepalived</code> will implement high availability.</p>

<p>We will start off by telling <code>keepalived</code> to communicate with its peers over <code>eth1</code>, our private interface.  Since we are configuring our primary server, we will set the <code>state</code> configuration to "MASTER".  This is the initial value that <code>keepalived</code> will use until the daemon can contact its peer and hold an election.</p>

<p>During the election, the <code>priority</code> option is used to decide which member is elected.  The decision is simply based on which server has the highest number for this setting.  We will use "200" for our primary server:</p>
<div class="code-label " title="Primary server's /etc/keepalived/keepalived.conf">Primary server's /etc/keepalived/keepalived.conf</div><pre class="code-pre "><code langs="">vrrp_script chk_nginx {
    script "pidof nginx"
    interval 2
}

vrrp_instance VI_1 {
    interface eth1
    state MASTER
    priority 200


}
</code></pre>
<p>Next, we will assign an ID for this cluster group that will be shared by both nodes.  We will use "33" for this example.  We need to set <code>unicast_src_ip</code> to our <strong>primary</strong> load balancer's private IP address.  We will set <code>unicast_peer</code> to our <strong>secondary</strong> load balancer's private IP address:</p>
<div class="code-label " title="Primary server's /etc/keepalived/keepalived.conf">Primary server's /etc/keepalived/keepalived.conf</div><pre class="code-pre "><code langs="">vrrp_script chk_haproxy {
    script "pidof haproxy"
    interval 2
}

vrrp_instance VI_1 {
    interface eth1
    state MASTER
    priority 200

    virtual_router_id 33
    unicast_src_ip <span class="highlight">primary_private_IP</span>
    unicast_peer {
        <span class="highlight">secondary_private_IP</span>
    }


}
</code></pre>
<p>Next, we can set up some simple authentication for our <code>keepalived</code> daemons to communicate with one another.  This is just a basic measure to ensure that the peer being contacted is legitimate.  Create an <code>authentication</code> sub-block.  Inside, specify password authentication by setting the <code>auth_type</code>.  For the <code>auth_pass</code> parameter, set a shared secret that will be used by both nodes.  Unfortunately, only the first eight characters are significant:</p>
<div class="code-label " title="Primary server's /etc/keepalived/keepalived.conf">Primary server's /etc/keepalived/keepalived.conf</div><pre class="code-pre "><code langs="">vrrp_script chk_haproxy {
    script "pidof haproxy"
    interval 2
}

vrrp_instance VI_1 {
    interface eth1
    state MASTER
    priority 200

    virtual_router_id 33
    unicast_src_ip <span class="highlight">primary_private_IP</span>
    unicast_peer {
        <span class="highlight">secondary_private_IP</span>
    }

    authentication {
        auth_type PASS
        auth_pass <span class="highlight">password</span>
    }


}
</code></pre>
<p>Next, we will tell <code>keepalived</code> to use the check we created at the top of the file, labeled <code>chk_haproxy</code>, to determine the health of the local system.  Finally, we will set a <code>notify_master</code> script, which is executed whenever this node becomes the "master" of the pair.  This script will be responsible for triggering the floating IP address reassignment.  We will create this script momentarily:</p>
<div class="code-label " title="Primary server's /etc/keepalived/keepalived.conf">Primary server's /etc/keepalived/keepalived.conf</div><pre class="code-pre "><code langs="">vrrp_script chk_haproxy {
    script "pidof haproxy"
    interval 2
}

vrrp_instance VI_1 {
    interface eth1
    state MASTER
    priority 200

    virtual_router_id 33
    unicast_src_ip <span class="highlight">primary_private_IP</span>
    unicast_peer {
        <span class="highlight">secondary_private_IP</span>
    }

    authentication {
        auth_type PASS
        auth_pass <span class="highlight">password</span>
    }

    track_script {
        chk_haproxy
    }

    notify_master /etc/keepalived/master.sh
}
</code></pre>
<p>Once you've set up the information above, save and close the file.</p>

<h3 id="creating-the-secondary-load-balancer-39-s-configuration">Creating the Secondary Load Balancer's Configuration</h3>

<p>Next, we will create the companion script on our secondary load balancer.  Open a file at <code>/etc/keepalived/keepalived.conf</code> on your secondary server:</p>
<pre class="code-pre custom_prefix"><code langs=""><ul class="prefixed"><li class="line" prefix="secondary$">sudo nano /etc/keepalived/keepalived.conf
</li></ul></code></pre>
<p>Inside, the script that we will use will be largely equivalent to the primary server's script.  The items that we need to change are:</p>

<ul>
<li><code>state</code>: This should be changed to "BACKUP" on the secondary server so that the node initializes to the backup state before elections occur.</li>
<li><code>priority</code>: This should be set to a lower value than the primary server.  We will use the value "100" in this guide.</li>
<li><code>unicast_src_ip</code>: This should be the private IP address of the <strong>secondary</strong> server.</li>
<li><code>unicast_peer</code>: This should contain the private IP address of the <strong>primary</strong> server.</li>
</ul>

<p>When you change those values, the script for the secondary server should look like this:</p>
<div class="code-label " title="Secondary server's /etc/keepalived/keepalived.conf">Secondary server's /etc/keepalived/keepalived.conf</div><pre class="code-pre "><code langs="">vrrp_script chk_haproxy {
    script "pidof haproxy"
    interval 2
}

vrrp_instance VI_1 {
    interface eth1
    state <span class="highlight">BACKUP</span>
    priority <span class="highlight">100</span>

    virtual_router_id 33
    unicast_src_ip <span class="highlight">secondary_private_IP</span>
    unicast_peer {
        <span class="highlight">primary_private_IP</span>
    }

    authentication {
        auth_type PASS
        auth_pass <span class="highlight">password</span>
    }

    track_script {
        chk_haproxy
    }

    notify_master /etc/keepalived/master.sh
}
</code></pre>
<p>Once you've entered the script and changed the appropriate values, save and close the file.</p>

<h2 id="create-the-floating-ip-transition-scripts">Create the Floating IP Transition Scripts</h2>

<p>Next, we need to create a pair of scripts that we can use to reassign the floating IP address to the current Droplet whenever the local <code>keepalived</code> instance becomes the master server.</p>

<h3 id="download-the-floating-ip-assignment-script">Download the Floating IP Assignment Script</h3>

<p>First, we will download a generic Python script (written by a <a href="https://indiareads/community/users/asb">IndiaReads community manager</a>) that can be used to reassign a floating IP address to a Droplet using the IndiaReads API.  We should download this file to the <code>/usr/local/bin</code> directory:</p>
<pre class="code-pre custom_prefix"><code langs=""><ul class="prefixed"><li class="line" prefix="loadbalancer$">cd /usr/local/bin
</li><li class="line" prefix="loadbalancer$">sudo curl -LO http://do.co/assign-ip
</li></ul></code></pre>
<p>This script allows you to re-assign an existing floating IP by running:</p>
<pre class="code-pre custom_prefix"><code langs=""><ul class="prefixed"><li class="line" prefix="loadbalancer$">python /usr/local/bin/assign-ip <span class="highlight">floating_ip</span> <span class="highlight">droplet_ID</span>
</li></ul></code></pre>
<p>This will only work if you have an environmental variable called <code>DO_TOKEN</code> set to a valid IndiaReads API token for your account.</p>

<h3 id="create-a-digitalocean-api-token">Create a IndiaReads API Token</h3>

<p>In order to use the script above, we will need to create a IndiaReads API token in our account.</p>

<p>In the control panel, click on the "API" link at the top.  On the right-hand side of the API page, click "Generate new token":</p>

<p><img src="https://assets.digitalocean.com/articles/keepalived_nginx_1404/generate_api_token.png" alt="IndiaReads generate API token" /></p>

<p>On the next page, select a name for your token and click on the "Generate Token" button:</p>

<p><img src="https://assets.digitalocean.com/articles/keepalived_haproxy_1404/make_token.png" alt="IndiaReads make new token" /></p>

<p>On the API page, your new token will be displayed:</p>

<p><img src="https://assets.digitalocean.com/articles/keepalived_nginx_1404/new_token.png" alt="IndiaReads token" /></p>

<p>Copy the token <strong>now</strong>.  For security purposes, there is no way to display this token again later.  If you lose this token, you will have to destroy it and create another one.</p>

<h3 id="configure-a-floating-ip-for-your-infrastructure">Configure a Floating IP for your Infrastructure</h3>

<p>Next, we will create and assign a floating IP address to use for our servers.</p>

<p>In the IndiaReads control panel, click on the "Networking" tab and select the "Floating IPs" navigation item.  Select your primary load balancer from the menu for the initial assignment:</p>

<p><img src="https://assets.digitalocean.com/site/ControlPanel/fip_assign_to_primary.png" alt="IndiaReads add floating IP" /></p>

<p>A new floating IP address will be created in your account and assigned to the Droplet specified:</p>

<p><img src="https://assets.digitalocean.com/site/ControlPanel/fip_assigned_to_primary.png" alt="IndiaReads floating IP assigned" /></p>

<p>If you visit the floating IP in your web browser, you should see the default Nginx page served from one of the backend web servers:</p>

<p><img src="https://assets.digitalocean.com/articles/keepalived_haproxy_1404/default_index.png" alt="IndiaReads default index.html" /></p>

<p>Copy the floating IP address down.  You will need this value in the script below.</p>

<h3 id="create-the-wrapper-script">Create the Wrapper Script</h3>

<p>Now, we have the items we need to create the wrapper script that will call our <code>/usr/local/bin/assign-ip</code> script with the correct credentials.</p>

<p>Create the file now on <strong>both</strong> of your load balancers by typing:</p>
<pre class="code-pre custom_prefix"><code langs=""><ul class="prefixed"><li class="line" prefix="loadbalancer$">sudo nano /etc/keepalived/master.sh
</li></ul></code></pre>
<p>Inside, start by assigning and exporting a variable called <code>DO_TOKEN</code> that holds the API token you just created.  Below that, we can assign a variable called <code>IP</code> that holds your floating IP address:</p>
<div class="code-label " title="/etc/keepalived/master.sh">/etc/keepalived/master.sh</div><pre class="code-pre "><code langs="">export DO_TOKEN='<span class="highlight">digitalocean_api_token</span>'
IP='<span class="highlight">floating_ip_addr</span>'
</code></pre>
<p>Next, we will use <code>curl</code> to ask the metadata service for the Droplet ID of the server we're currently on.  This will be assigned to a variable called <code>ID</code>.  We will also ask whether this Droplet currently has the floating IP address assigned to it.  We will store the results of that request in a variable called <code>HAS_FLOATING_IP</code>:</p>
<div class="code-label " title="/etc/keepalived/master.sh">/etc/keepalived/master.sh</div><pre class="code-pre "><code langs="">export DO_TOKEN='<span class="highlight">digitalocean_api_token</span>'
IP='<span class="highlight">floating_ip_addr</span>'
ID=$(curl -s http://169.254.169.254/metadata/v1/id)
HAS_FLOATING_IP=$(curl -s http://169.254.169.254/metadata/v1/floating_ip/ipv4/active)
</code></pre>
<p>Now, we can use the variables above to call the <code>assign-ip</code> script.  We will only call the script if the floating IP is not already associated with our Droplet.  This will help minimize API calls and will help prevent conflicting requests to the API in cases where the master status switches between your servers rapidly.</p>

<p>To handle cases where the floating IP already has an event in progress, we will retry the <code>assign-ip</code> script a few times.  Below, we attempt to run the script 10 times, with a 3 second interval between each call.  The loop will end immediately if the floating IP move is successful:</p>
<div class="code-label " title="/etc/keepalived/master.sh">/etc/keepalived/master.sh</div><pre class="code-pre "><code langs="">export DO_TOKEN='<span class="highlight">digitalocean_api_token</span>'
IP='<span class="highlight">floating_ip_addr</span>'
ID=$(curl -s http://169.254.169.254/metadata/v1/id)
HAS_FLOATING_IP=$(curl -s http://169.254.169.254/metadata/v1/floating_ip/ipv4/active)

if [ $HAS_FLOATING_IP = "false" ]; then
    n=0
    while [ $n -lt 10 ]
    do
        python /usr/local/bin/assign-ip $IP $ID && break
        n=$((n+1))
        sleep 3
    done
fi
</code></pre>
<p>Save and close the file when you are finished.</p>

<p>Now, we just need to make the script executable so that <code>keepalived</code> can call it:</p>
<pre class="code-pre custom_prefix"><code langs=""><ul class="prefixed"><li class="line" prefix="loadbalancer$">sudo chmod +x /etc/keepalived/master.sh
</li></ul></code></pre>
<h2 id="start-up-the-keepalived-service-and-test-failover">Start Up the Keepalived Service and Test Failover</h2>

<p>The <code>keepalived</code> daemon and all of its companion scripts should now be completely configured.  We can start the service on both of our load balancers by typing:</p>
<pre class="code-pre custom_prefix"><code langs=""><ul class="prefixed"><li class="line" prefix="loadbalancer$">sudo start keepalived
</li></ul></code></pre>
<p>The service should start up on each server and contact its peer, authenticating with the shared secret we configured.  Each daemon will monitor the local HAProxy process, and will listen to signals from the remote <code>keepalived</code> process.</p>

<p>Your primary load balancer, which should have the floating IP address assigned to it currently, will direct requests to each of the backend Nginx servers in turn.  There is some simple session stickiness that is usually applied, making it more likely that you will get the same backend when making requests through a web browser.</p>

<p>We can test failover in a simple way by simply turning off HAProxy on our primary load balancer:</p>
<pre class="code-pre custom_prefix"><code langs=""><ul class="prefixed"><li class="line" prefix="primary$">sudo service haproxy stop
</li></ul></code></pre>
<p>If we visit our floating IP address in our browser, we might momentarily get an error indicating the page could not be found:</p>
<pre class="code-pre "><code langs="">http://<span class="highlight">floating_IP_addr</span>
</code></pre>
<p><img src="https://assets.digitalocean.com/articles/keepalived_nginx_1404/page_not_available.png" alt="IndiaReads page not available" /></p>

<p>If we refresh the page a few times, in a moment, our default Nginx page will come back:</p>

<p><img src="https://assets.digitalocean.com/articles/keepalived_haproxy_1404/default_index.png" alt="IndiaReads default index.html" /></p>

<p>Our HAProxy service is still down on our primary load balancer, so this indicates that our secondary load balancer has taken over.  Using <code>keepalived</code>, the secondary server was able to determine that a service interruption had occurred.  It then transitioned to the "master" state and claimed the floating IP using the IndiaReads API.</p>

<p>We can now start HAProxy on the primary load balancer again:</p>
<pre class="code-pre custom_prefix"><code langs=""><ul class="prefixed"><li class="line" prefix="primary$">sudo service haproxy start
</li></ul></code></pre>
<p>The primary load balancer will regain control of the floating IP address in a moment, although this should be rather transparent to the user.</p>

<h2 id="visualizing-the-transition">Visualizing the Transition</h2>

<p>In order to visualize the transition between the load balancers better, we can monitor some of our server logs during the transition.</p>

<p>Since information about which proxy server is being used is not returned to the client, the best place to view the logs is from the actual backend web servers.  Each of these servers should maintain logs about which clients request assets.  From the Nginx service's perspective, the client is the load balancer that makes requests on behalf of the real client.</p>

<h3 id="tail-the-logs-on-the-web-servers">Tail the Logs on the Web Servers</h3>

<p>On each of our backend web servers, we can <code>tail</code> the <code>/var/log/nginx/access.log</code> location.  This will show each request made to the server.  Since our load balancers split traffic evenly using a round-robin rotation, each backend web server should see about half of the requests made.</p>

<p>The client address is fortunately the very first field in the access log.  We can extract the value using a simple <code>awk</code> command.  Run the following on <strong>both</strong> of your Nginx web servers:</p>
<pre class="code-pre custom_prefix"><code langs=""><ul class="prefixed"><li class="line" prefix="webserver$">sudo tail -f /var/log/nginx/access.log | awk '{print $1;}'
</li></ul></code></pre>
<p>These will likely show mostly a single address:</p>
<pre class="code-pre "><code langs=""><div class="secondary-code-label " title="Output">Output</div>. . .

<span class="highlight">primary_lb_private_IP</span>
<span class="highlight">primary_lb_private_IP</span>
<span class="highlight">secondary_lb_private_IP</span>
<span class="highlight">secondary_lb_private_IP</span>
<span class="highlight">primary_lb_private_IP</span>
<span class="highlight">primary_lb_private_IP</span>
<span class="highlight">primary_lb_private_IP</span>
<span class="highlight">primary_lb_private_IP</span>
</code></pre>
<p>If you reference your server IP addresses, you will notice that these are mostly coming from your primary load balancer.  Note that the actual distribution will likely be a bit different due to some simple session stickiness that HAProxy implements.</p>

<p>Keep the <code>tail</code> command running on both of your web servers.</p>

<h3 id="automate-requests-to-the-floating-ip">Automate Requests to the Floating IP</h3>

<p>Now, on your local machine, we will request the web content at the floating IP address once every 2 seconds.  This will allow us to easily see the load balancer change happen.  In your local terminal, type the following (we are throwing away the actual response, because this should be the same regardless of which load balancer is being utilized):</p>
<pre class="code-pre custom_prefix"><code langs=""><ul class="prefixed"><li class="line" prefix="local$">while true; do curl -s -o /dev/null <span class="highlight">floating_IP</span>; sleep 2; done
</li></ul></code></pre>
<p>On your web servers, you should begin to see new requests come in.  Unlike requests made through a web browser, simple <code>curl</code> requests do not exhibit the same session stickiness.  You should see a more even split of the requests to your backend web servers.</p>

<h3 id="interrupt-the-haproxy-service-on-the-primary-load-balancer">Interrupt the HAProxy Service on the Primary Load Balancer</h3>

<p>Now, we can again shut down the HAProxy service on our primary load balancer:</p>
<pre class="code-pre custom_prefix"><code langs=""><ul class="prefixed"><li class="line" prefix="primary$">sudo service haproxy stop
</li></ul></code></pre>
<p>After a few seconds, on your web servers, you should see the list of IPs transition from the primary load balancer's private IP address to the secondary load balancer's private IP address:</p>
<pre class="code-pre "><code langs=""><div class="secondary-code-label " title="Output">Output</div>. . .

<span class="highlight">primary_lb_private_IP</span>
<span class="highlight">primary_lb_private_IP</span>
<span class="highlight">primary_lb_private_IP</span>
<span class="highlight">primary_lb_private_IP</span>
<span class="highlight">primary_lb_private_IP</span>
<span class="highlight">secondary_lb_private_IP</span>
<span class="highlight">secondary_lb_private_IP</span>
<span class="highlight">secondary_lb_private_IP</span>
<span class="highlight">secondary_lb_private_IP</span>
</code></pre>
<p>All of the new requests are made from your secondary load balancer.</p>

<p>Now, start up the HAProxy instance again on your primary load balancer:</p>
<pre class="code-pre custom_prefix"><code langs=""><ul class="prefixed"><li class="line" prefix="primary$">sudo service haproxy start
</li></ul></code></pre>
<p>You will see the client requests transition back to the primary load balancer's private IP address within a few seconds:</p>
<pre class="code-pre "><code langs=""><div class="secondary-code-label " title="Output">Output</div>. . .

<span class="highlight">primary_lb_private_IP</span>
<span class="highlight">primary_lb_private_IP</span>
<span class="highlight">primary_lb_private_IP</span>
<span class="highlight">primary_lb_private_IP</span>
<span class="highlight">primary_lb_private_IP</span>
<span class="highlight">secondary_lb_private_IP</span>
<span class="highlight">secondary_lb_private_IP</span>
<span class="highlight">secondary_lb_private_IP</span>
<span class="highlight">secondary_lb_private_IP</span>
<span class="highlight">primary_lb_private_IP</span>
<span class="highlight">primary_lb_private_IP</span>
<span class="highlight">primary_lb_private_IP</span>
</code></pre>
<p>The primary server has regained control of the floating IP address and has resumed its job as the main load balancer for the infrastructure.</p>

<h2 id="configure-nginx-to-log-actual-client-ip-address">Configure Nginx to Log Actual Client IP Address</h2>

<p>As you have seen, the Nginx access logs show that all client requests are from the private IP address of the current load balancer, instead of the actual IP address of the client that originally made the request (i.e. your local machine). It is often useful to log the IP address of the original client, instead of the load balancer server. This is easily achieved by making a few changes to the Nginx configuration on all of your backend web servers.</p>

<p>On both web servers, open the <code>nginx.conf</code> file in an editor:</p>
<pre class="code-pre custom_prefix"><code langs=""><ul class="prefixed"><li class="line" prefix="webserver$">sudo nano /etc/nginx/nginx.conf
</li></ul></code></pre>
<p>Find the "Logging Settings" section (within the <code>http</code> block), and add the following line:</p>
<div class="code-label " title="add to /etc/nginx/nginx.conf">add to /etc/nginx/nginx.conf</div><pre class="code-pre "><code langs="">log_format haproxy_log 'ProxyIP: $remote_addr - ClientIP: $http_x_forwarded_for - $remote_user [$time_local] ' '"$request" $status $body_bytes_sent "$http_referer" ' '"$http_user_agent"';
</code></pre>
<p>Save and exit.  This specifies a new log format called <code>haproxy_log</code>, which adds the <code>$http_x_forwarded_for</code> value — the IP address of the client that made the original request — to the default access log entries.  We also are including <code>$remote_addr</code>, which is the IP address of the reverse proxy load balancer (i.e. the active load balancer server).</p>

<p>Next, to put this new log format to use, we need to add a line to our default server block.</p>

<p>On both web servers, open the <code>default</code> server configuration:</p>
<pre class="code-pre custom_prefix"><code langs=""><ul class="prefixed"><li class="line" prefix="webserver$">sudo nano /etc/nginx/sites-available/default
</li></ul></code></pre>
<p>Within the <code>server</code> block (right below the <code>listen</code> directive is a good place), add the following line:</p>
<div class="code-label " title="add to /etc/nginx/sites-available/default">add to /etc/nginx/sites-available/default</div><pre class="code-pre "><code langs="">        access_log /var/log/nginx/access.log haproxy_log;
</code></pre>
<p>Save and exit. This tells Nginx to write its access logs using the <code>haproxy_log</code> log format that we created above.</p>

<p>On both web servers, restart Nginx to put the changes into effect:</p>
<pre class="code-pre custom_prefix"><code langs=""><ul class="prefixed"><li class="line" prefix="webserver$">sudo service nginx restart
</li></ul></code></pre>
<p>Now your Nginx access logs should contain the actual IP addresses of the clients making requests. Verify this by tailing the logs of your app servers, as we did in the previous section. The log entries should look something like this:</p>
<pre class="code-pre "><code langs=""><div class="secondary-code-label " title="New Nginx access logs:">New Nginx access logs:</div>. . .
ProxyIP: <span class="highlight">load_balancer_private_IP</span> - ClientIP: <span class="highlight">local_machine_IP</span> - - [05/Nov/2015:15:05:53 -0500] "GET / HTTP/1.1" 200 43 "-" "curl/7.43.0"
. . .
</code></pre>
<p>If your logs look good, you're all set!</p>

<h2 id="conclusion">Conclusion</h2>

<p>In this guide, we walked through the complete process of setting up a highly available, load balanced infrastructure.  This configuration works well because the active HAProxy server can distribute the load to the pool of web servers on the backend.  You can easily scale this pool as your demand grows or shrinks.</p>

<p>The floating IP and <code>keepalived</code> configuration eliminates the single point of failure at the load balancing layer, allowing your service to continue functioning even when the primary load balancer completely fails.  This configuration is fairly flexible and can be adapted to your own application environment by setting up your preferred web stack behind the HAProxy servers.</p>

    