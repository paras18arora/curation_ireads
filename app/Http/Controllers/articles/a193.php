<link rel='stylesheet' href='digital_ocean.css'><br> <br> 
      <h3 id="introduction">Introduction</h3>

<p>IndiaReads is now offering IPv6 addresses in select datacenters (starting with Singapore 1).</p>

<p>IPv6 is the most recent version of the IP protocol that the entire internet relies on to connect to other locations (IP protocol is a bit redundant because IP stands for internet protocol, but we will use it because it is easy). While IPv4 is still in use in many areas of the world, the IPv4 address space is being consumed at a rapid rate and it is not large enough to sustain the rapid deployment of internet-ready devices.</p>

<p>IPv6 looks to solve these problems. As well as making general improvements on the protocol, the most obvious benefit of utilizing IPv6 addresses is that it has a much larger address space. While IPv4 allowed for 2^32 addresses (with some of those reserved for special purposes), the IPv6 address space allows for 2^128 addresses, which is an incredible increase.  To find out <a href="https://indiareads/community/tutorials/how-to-enable-ipv6-for-digitalocean-droplets">how to enable IPv6 on your Droplets</a> follow the linked guide. </p>

<p>In this tutorial, we will discuss how to add additional IPv6 addresses to your Droplets.</p>

<h2 id="find-the-ipv6-address-range-for-your-droplet">Find the IPv6 Address Range for your Droplet</h2>

<p>By default, the each Droplet with IPv6 enabled will be configured with a single IPv6 address.  This is the address that will be used for any PTR records that will be generated for domains pointing at your server.</p>

<p>However, a broader range of addresses is also available for your Droplet.  The available range is given in the networking section of your Droplet's configuration page.</p>

<p>To find this value, click on your Droplet's name in the "Droplets" page of your IndiaReads control panel:</p>

<p><img src="https://assets.digitalocean.com/articles/ipv6_add_address/droplet_name.png" alt="IndiaReads Droplet name" /></p>

<p>In the configuration page, click on the "Settings" tab and then select the "Networking" sub-navigation item:</p>

<p><img src="https://assets.digitalocean.com/articles/ipv6_add_address/settings_networking.png" alt="IndiaReads settings networking" /></p>

<p>If you have enabled IPv6, you will have a section of that identifies the IPv6 networking details.  Among this information, you will have a range of addresses labeled "Configurable address range":</p>

<p><img src="https://assets.digitalocean.com/articles/ipv6_add_address/address_range.png" alt="IndiaReads IPv6 address range" /></p>

<p>This represents the IPv6 addresses you have available to allocate to your Droplet. </p>

<h2 id="temporarily-add-additional-ipv6-addresses-to-your-droplet">Temporarily Add Additional IPv6 Addresses to your Droplet</h2>

<p>The IndiaReads backend is already set up to serve requests for these addresses to your Droplet.  However, you will need to configure the network within the Droplet so that it knows about the additional addresses.</p>

<p>To do so, you need to add each of the addresses that you wish to configure to the Droplet's interface.  Log into the Droplet using SSH or the control panel console.</p>

<p>To configure the Droplet's networking interface, select the address you want to configure out of your address range and adding it like this:</p>
<pre class="code-pre "><code langs="">ip -6 addr add <span class="highlight">new_IPv6_address_in_range</span>/64 dev eth0
</code></pre>
<p>The new address will immediately be available in your session.  You should be able to see it in the list here:</p>
<pre class="code-pre "><code langs="">ip -6 addr show eth0
</code></pre><pre class="code-pre "><code langs="">2: eth0: <BROADCAST,MULTICAST,UP,LOWER_UP> mtu 1500 qlen 1000
    inet6 <span class="highlight">first_ipv6_address</span>/64 scope global 
       valid_lft forever preferred_lft forever
    inet6 <span class="highlight">second_ipv6_address</span>/64 scope global 
       valid_lft forever preferred_lft forever
</code></pre>
<p>The new address will be available for the duration of your current session.  If you wish to add the additional IP address permanently, you will need to add some information to your configuration files.</p>

<h2 id="permanently-add-additional-ipv6-addresses-in-debian-and-ubuntu">Permanently Add Additional IPv6 Addresses in Debian and Ubuntu</h2>

<p>On Debian or Ubuntu, you need to add the additional IPv6 addresses to the file that configures your network at boot.  The file that is responsible for this is <code>/etc/network/interfaces</code>. </p>

<p>Open this file with root privileges with your text editor:</p>
<pre class="code-pre "><code langs="">sudo nano /etc/network/interfaces
</code></pre>
<p>You will find a section in this file for each of the different networks that you have configured.  These may be public IPv4, public IPv6, private IPv4, etc.  The public IPv6 interface is defined by the section that looks like this:</p>
<pre class="code-pre "><code langs="">. . .
iface eth0 inet6 static
        address <span class="highlight">primary_ipv6_address</span>
        netmask 64
        gateway <span class="highlight">ipv6_gateway</span>
        autoconf 0
        dns-nameservers 2001:4860:4860::8844 2001:4860:4860::8888 209.244.0.3
. . .
</code></pre>
<p>We want to add an additional IPv6 address that will be also be available publicly.</p>

<p>To do this, you need to add an additional section that mirrors the specification you currently have.  This will only need to include the new address you are adding and a netmask specification:</p>
<pre class="code-pre "><code langs="">. . .
iface eth0 inet6 static
        address <span class="highlight">primary_ipv6_address</span>
        netmask 64
        gateway <span class="highlight">ipv6_gateway</span>
        autoconf 0
        dns-nameservers 2001:4860:4860::8844 2001:4860:4860::8888 209.244.0.3

<span class="highlight">iface eth0 inet6 static</span>
        <span class="highlight">address new_ipv6_address</span>
        <span class="highlight">netmask 64</span>
. . .
</code></pre>
<p>Save and close the file when you are finished. </p>

<p>On the next boot, your Droplet will automatically add the additional IPv6 addresses that you configured.  If you need the additional addresses to be available now, you can use the temporary method given above.</p>

<h2 id="permanently-add-additional-ipv6-addresses-in-centos-and-fedora">Permanently Add Additional IPv6 Addresses in CentOS and Fedora</h2>

<p>On CentOS or Fedora, a similar configuration change needs to be made.  The file that controls the interfaces we are concerned with is <code>/etc/sysconfig/network-scripts/ifcfg-eth0</code>. </p>

<p>Open this file in your text editor with root privileges:</p>
<pre class="code-pre "><code langs="">sudo nano /etc/sysconfig/network-scripts/ifcfg-eth0
</code></pre>
<p>The portion of this file that deals with IPv6 addresses should look something like this:</p>
<pre class="code-pre "><code langs="">. . .
IPV6INIT=yes
IPV6ADDR=<span class="highlight">primary_ipv6_address</span>/64
IPV6_DEFAULTGW=<span class="highlight">ipv6_gateway</span>
IPV6_AUTOCONF=no
. . .
</code></pre>
<p>To add additional IPv6 addresses, we will use a parameter called <code>IPV6ADDR_SECONDARIES</code>.  This will be set to a string which defines any other IPv6 addresses that we may want to add.</p>

<p>This will look something like this:</p>
<pre class="code-pre "><code langs="">. . .
IPV6INIT=yes
IPV6ADDR=<span class="highlight">primary_ipv6_address</span>/64
IPV6_DEFAULTGW=<span class="highlight">ipv6_gateway</span>
IPV6ADDR_SECONDARIES="<span class="highlight">second_ipv6_address</span>/64 <span class="highlight">third_ipv6_address</span>/64 <span class="highlight">...</span>/64"
IPV6_AUTOCONF=no
. . .
</code></pre>
<p>When you are finished adding the additional IPv6 addresses, you can save and close the file.</p>

<p>On the next boot, these addresses will be automatically configured.  If you need the additional addresses prior to rebooting, you should follow the directions in the temporary solution as well.</p>

<h2 id="conclusion">Conclusion</h2>

<p>You should now be able to add as many addresses within your Droplet's addressable range as you would like.  This can give you flexibility in your configuration and allows you to use different addresses for specific purposes.</p>

<p>You can follow this link to find out more about <a href="https://indiareads/community/tutorials/how-to-configure-tools-to-use-ipv6-on-a-linux-vps">how to use tools and common applications with IPv6</a>.  If you wish to turn IPv4 off completely and only have IPv6 available, you can do so by following our guide on <a href="https://indiareads/community/tutorials/how-to-configure-your-droplet-to-only-use-ipv6-networking">configuring your Droplet to use IPv6 only</a>.</p>

<p>By Justin Ellingwood</p>

    