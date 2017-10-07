<link rel='stylesheet' href='digital_ocean.css'><br> <br> 
      <h3 id="introduction">Introduction</h3>

<p>IndiaReads is now offering IPv6 addresses in select datacenters (starting with Singapore 1).</p>

<p>IPv6 is the most recent version of the IP protocol that the entire internet relies on to connect to other locations (IP protocol is a bit redundant because IP stands for internet protocol, but we will use it because it is easy). While IPv4 is still in use in many areas of the world, the IPv4 address space is being consumed at a rapid rate and it is not large enough to sustain the rapid deployment of internet-ready devices.</p>

<p>IPv6 looks to solve these problems. As well as making general improvements on the protocol, the most obvious benefit of utilizing IPv6 addresses is that it has a much larger address space. While IPv4 allowed for 2^32 addresses (with some of those reserved for special purposes), the IPv6 address space allows for 2^128 addresses, which is an incredible increase.  To find out <a href="https://indiareads/community/tutorials/how-to-enable-ipv6-for-digitalocean-droplets">how to enable IPv6 on your Droplets</a> follow the linked guide. </p>

<p>It is often desirable to have both IPv4 and IPv6 interfaces available on a single Droplet.  This is the state of the Droplet after IPv6 has been enabled.  However, for some purposes, you may wish to disable IPv4 entirely and rely solely on IPv6.</p>

<p>In this guide, we will discuss how to turn off IPv4 networking if you wish to only have IPv6 enabled.  Most people will not need to do this, but there are situations where it may make sense.  You will still need to have IPv4 available for the localhost so that your programs operate as expected.  This is the scenario we will demonstrate.</p>

<p><strong>Note</strong>: If you are connecting to your Droplet through an IPv4 connection, disabling the IPv4 interface will drop your connection!  If this happens, you will need to either connect using IPv6 (if your local configuration supports it) or log in using the control panel.</p>

<h2 id="disabling-ipv4-temporarily">Disabling IPv4 Temporarily</h2>

<p>If you wish to disable IPv4 temporarily, you can do so by simply editing the <code>/etc/resolv.conf</code> file to use IPv6 DNS servers (if you enabled IPv6 during the Droplet's creation, this step should already be done) and then deleting the rule that configures your IPv4 address.  These changes will be reverted next time you reboot.</p>

<p>First, edit your <code>/etc/resolv.conf</code> file if necessary:</p>
<pre class="code-pre "><code langs="">sudo nano /etc/resolv.conf
</code></pre>
<p>If your <code>nameservers</code> configuration points to IPv4 addresses, you'll need to change those to IPv6 name servers instead.  This will already be done if you enabled IPv6 when you initially created the Droplet.</p>

<p>If this isn't configured yet, you can set the directives to Google's IPv6 name servers by changing the file to look like this:</p>
<pre class="code-pre "><code langs="">nameserver 2001:4860:4860::8844
nameserver 2001:4860:4860::8888
nameserver 209.244.0.3
</code></pre>
<p>Save and close the file when you are finished.</p>

<p>Next, you'll need to see what your IPv4 address and CIDR routing prefix is by typing:</p>
<pre class="code-pre "><code langs="">ip -4 addr show eth0
</code></pre><pre class="code-pre "><code langs="">2: eth0: <BROADCAST,MULTICAST,UP,LOWER_UP> mtu 1500 qdisc pfifo_fast state UP qlen 1000
    inet <span class="highlight">128.199.175.162/18</span> brd 128.199.191.255 scope global eth0
</code></pre>
<p>The value in red above is the information you need.  You can then remove that from your active network connections by typing this (<strong>Note</strong>: This is where your connection will drop if you are using SSH through IPv4):</p>
<pre class="code-pre "><code langs="">ip addr del <span class="highlight">128.199.175.162/18</span> dev eth0
</code></pre>
<p>Make sure that you change the red portion to reflect your own IPv4/CIDR values that you discovered in the last command.</p>

<h2 id="disabling-ipv4-permanently-on-centos-and-fedora">Disabling IPv4 Permanently on CentOS and Fedora</h2>

<p>To disable IPv4 permanently, we will have to modify the files that build up the interfaces and generate the necessary files at boot.</p>

<p>Start by looking at the <code>/etc/sysconfig/network</code> file.  If you enabled IPv6 after the Droplet was already created, you'll have to add a line here.</p>

<p>Open the file now:</p>
<pre class="code-pre "><code langs="">sudo nano /etc/sysconfig/network
</code></pre>
<p>If it is not already there, add the line that tells the server to enable IPv6.</p>
<pre class="code-pre "><code langs="">NETWORKING=yes
HOSTNAME=centafter
<span class="highlight">NETWORKING_IPV6=yes</span>
</code></pre>
<p>Next, you'll need to modify the <code>/etc/sysconfig/network-scripts/ifcfg-eth0</code> file.  This specifies how the network should be configured when it is brought up:</p>
<pre class="code-pre "><code langs="">sudo nano /etc/sysconfig/network-scripts/ifcfg-eth0
</code></pre>
<p>First, you'll want to comment out the IPv4 information so that this is not taken into account at boot:</p>
<pre class="code-pre "><code langs=""><span class="highlight">#</span>IPADDR=128.199.175.162
<span class="highlight">#</span>NETMASK=255.255.192.0
<span class="highlight">#</span>GATEWAY=128.199.128.1
</code></pre>
<p>Next, if you enabled IPv6 after the Droplet was already created, you'll need to add your IPv6 information.  You should add the following lines or make sure they are set correctly:</p>
<pre class="code-pre "><code langs="">IPV6INIT=yes
IPV6ADDR=<span class="highlight">public_ipv6_address</span>/64
IPV6_DEFAULTGW=<span class="highlight">public_ipv6_gateway</span>
IPV6_AUTOCONF=no
</code></pre>
<p>Additionally, you'll need to adjust the DNS directives so that they mainly point to IPv6 name servers.  Once again, this will be something you'll have to adjust if you enabled IPv6 after creation:</p>
<pre class="code-pre "><code langs="">DNS1=<span class="highlight">2001:4860:4860::8844</span>
DNS2=<span class="highlight">2001:4860:4860::8888</span>
DNS3=<span class="highlight">209.244.0.3</span>
</code></pre>
<p>Save and close the file when you are finished.</p>

<p>If you need to disable IPv4 right now, you can follow the steps outlined in the section on temporarily disabling IPv4.  Otherwise, IPv4 will be disabled at next boot.</p>

<h2 id="disabling-ipv4-permanently-on-debian-and-ubuntu">Disabling IPv4 Permanently on Debian and Ubuntu</h2>

<p>On a Debian or Ubuntu machine, you will have to modify files in a similar way.  The file you are looking for is called <code>/etc/network/interfaces</code>.</p>

<p>Open this file with root privileges with your text editor:</p>
<pre class="code-pre "><code langs="">sudo nano /etc/network/interfaces
</code></pre>
<p>If you enabled IPv6 upon creation, you will see both of these sections (and maybe more if you also enabled private networking):</p>
<pre class="code-pre "><code langs="">iface eth0 inet6 static
    . . .
iface eth0 inet static
    . . .
</code></pre>
<p>To make sure that the IPv4 interface doesn't come back up on a reboot, delete or comment out the <code>inet</code> section for <code>eth0</code> so that only the <code>inet6</code> section is defined for <code>eth0</code>:</p>
<pre class="code-pre "><code langs="">iface eth0 inet6 static
    . . .
<span class="highlight">#</span>iface eth0 inet static
    <span class="highlight">#</span>. . .
</code></pre>
<p>If you enabled IPv6 <em>after</em> the Droplet's creation by clicking the "Enable IPv6" button, you will need to delete or comment out the <code>inet</code> portion of the <code>eth0</code> configuration just like above. </p>

<p>However, you'll also need to add the IPv6 section to your configuration.  Add the following details to configure IPv6:</p>
<pre class="code-pre "><code langs="">iface eth0 <span class="highlight">inet6</span> static
    address <span class="highlight">public_ipv6_address</span>
    netmask 64
    gateway <span class="highlight">public_ipv6_gateway</span>
    autoconf 0
    dns-nameservers <span class="highlight">2001:4860:4860::8844 2001:4860:4860::8888 209.244.0.3</span>
</code></pre>
<p>Save and close the file when you are finished. </p>

<p>If you need to disable IPv4 right now, you can go through the steps in the section on temporarily disabling IPv4.  Otherwise, IPv4 will be disabled at next boot.</p>

<h2 id="conclusion">Conclusion</h2>

<p>You should now have a Droplet that is only available through its IPv6 addresses.  This might make certain procedures more difficult, but it can also be useful in certain circumstances.  You can easily reverse this by reversing the steps you took in this guide.</p>

<p>To find out <a href="https://indiareads/community/tutorials/how-to-add-additional-ipv6-addresses-to-your-droplet">how to add additional IPv6 addresses to your Droplet</a>, click here.  For more information about <a href="https://indiareads/community/tutorials/how-to-configure-tools-to-use-ipv6-on-a-linux-vps">using tools and configuring applications to work with IPv6</a>, check out this link.</p>

    