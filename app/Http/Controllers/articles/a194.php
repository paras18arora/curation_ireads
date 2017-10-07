<link rel='stylesheet' href='digital_ocean.css'><br><img src=https://community-cdn-digitalocean-com.global.ssl.fastly.net/assets/tutorials/images/large/Configure-Tools-for-IPV6-Twitter.png?1426699670/> <br> 
      <h3 id="introduction">Introduction</h3>

<p>IPv6 is the most recent version of the IP protocol that the entire internet relies on to connect to other locations (IP protocol is a bit redundant because IP stands for internet protocol, but we will use it because it is easy).  While IPv4 is still in use in many areas of the world, the IPv4 address space is being consumed at a rapid rate and it is not large enough to sustain the rapid deployment of internet-ready devices.</p>

<p>IPv6 looks to solve these problems.  As well as making general improvements on the protocol, the most obvious benefit of utilizing IPv6 addresses is that it has a <em>much</em> larger address space.  While IPv4 allowed for 2^32 addresses (with some of those reserved for special purposes), the IPv6 address space allows for 2^128 addresses, which is an incredible increase.</p>

<p>While IPv6 opens up a lot of opportunities and solves many long-standing issues, it does require a bit of an adjustment to some of your routine network configurations if you are used to using IPv4 exclusively.  In this guide, we'll talk about some of the IPv6 counterparts to some popular IPv4 tools and utilities and discuss how to configure some popular services to utilize IPv6.</p>

<h2 id="trivial-network-diagnostics-with-ipv6">Trivial Network Diagnostics with IPv6</h2>

<p>Some of the simplest utilities used to diagnose network issues were created with IPv4 in mind.  To address this, we can use their IPv6 cousins when we wish to deal with IPv6 traffic.</p>

<p>First of all, to see your currently configured IPv6 addresses for your server, you can use the <code>iproute2</code> tools to show you the current configured addresses:</p>
<pre class="code-pre "><code langs="">ip -6 addr show
</code></pre>
<hr />
<pre class="code-pre "><code langs="">1: lo: <LOOPBACK,UP,LOWER_UP> mtu 65536 
    inet6 ::1/128 scope host 
       valid_lft forever preferred_lft forever
2: eth0: <BROADCAST,MULTICAST,UP,LOWER_UP> mtu 1500 qlen 1000
    inet6 2400:6180:0:d0::41f/64 scope global 
       valid_lft forever preferred_lft forever
    inet6 fe80::601:15ff:fe43:b201/64 scope link 
       valid_lft forever preferred_lft forever
3: eth1: <BROADCAST,MULTICAST,UP,LOWER_UP> mtu 1500 qlen 1000
    inet6 fe80::601:15ff:fe43:b202/64 scope link 
       valid_lft forever preferred_lft forever
</code></pre>
<p>To print out the IPv6 routing table, you can use <code>netstat</code> by typing something like this:</p>
<pre class="code-pre "><code langs="">netstat -A inet6 -rn
</code></pre>
<hr />
<pre class="code-pre "><code langs="">Kernel IPv6 routing table
Destination                    Next Hop                   Flag Met Ref Use If
2400:6180:0:d0::/64            ::                         U    256 0     1 eth0
fe80::/64                      ::                         U    256 0     0 eth1
fe80::/64                      ::                         U    256 0     0 eth0
::/0                           2400:6180:0:d0::1          UG   1024 0     0 eth0
::/0                           ::                         !n   -1  1    90 lo
::1/128                        ::                         Un   0   1    20 lo
2400:6180:0:d0::41f/128        ::                         Un   0   1    86 lo
fe80::601:15ff:fe43:b201/128   ::                         Un   0   1    75 lo
fe80::601:15ff:fe43:b202/128   ::                         Un   0   1     0 lo
ff00::/8                       ::                         U    256 0     0 eth1
ff00::/8                       ::                         U    256 0     0 eth0
::/0                           ::                         !n   -1  1    90 lo
</code></pre>
<p>If you prefer the iproute2 tools, you can get similar information by typing:</p>
<pre class="code-pre "><code langs="">ip -6 route show
</code></pre>
<hr />
<pre class="code-pre "><code langs="">2400:6180:0:d0::/64 dev eth0  proto kernel  metric 256 
fe80::/64 dev eth1  proto kernel  metric 256 
fe80::/64 dev eth0  proto kernel  metric 256 
default via 2400:6180:0:d0::1 dev eth0  metric 1024 
</code></pre>
<p>Now that you know about how to get some of your own IPv6 information, let's learn a bit about how to use some tools that work with IPv6.</p>

<p>The ubiquitous <code>ping</code> command is actually IPv4-specific.  The IPv6 version of the command, which works exactly the same but for IPv6 addresses, is named unsurprisingly <code>ping6</code>.  This will ping the local loopback interface:</p>
<pre class="code-pre "><code langs="">ping6 -c 3 ::1
</code></pre>
<hr />
<pre class="code-pre "><code langs="">PING ::1(::1) 56 data bytes
64 bytes from ::1: icmp_seq=1 ttl=64 time=0.021 ms
64 bytes from ::1: icmp_seq=2 ttl=64 time=0.028 ms
64 bytes from ::1: icmp_seq=3 ttl=64 time=0.022 ms

--- ::1 ping statistics ---
3 packets transmitted, 3 received, 0% packet loss, time 1998ms
rtt min/avg/max/mdev = 0.021/0.023/0.028/0.006 ms
</code></pre>
<p>As you can see, this works exactly as expected, the only difference being the protocol version being used for the addressing.</p>

<p>Another tool that you might rely on is <code>traceroute</code>.  There is also an IPv6 equivalent available:</p>
<pre class="code-pre "><code langs="">traceroute6 google.com
</code></pre>
<hr />
<pre class="code-pre "><code langs="">traceroute to google.com (2404:6800:4003:803::1006) from 2400:6180:0:d0::41f, 30 hops max, 24 byte packets
 1  2400:6180:0:d0:ffff:ffff:ffff:fff1 (2400:6180:0:d0:ffff:ffff:ffff:fff1)  0.993 ms  1.034 ms  0.791 ms
 2  2400:6180::501 (2400:6180::501)  0.613 ms  0.636 ms  0.557 ms
 3  2400:6180::302 (2400:6180::302)  0.604 ms  0.506 ms  0.561 ms
 4  10gigabitethernet1-1.core1.sin1.he.net (2001:de8:4::6939:1)  6.21 ms  10.869 ms  1.249 ms
 5  15169.sgw.equinix.com (2001:de8:4::1:5169:1)  1.522 ms  1.205 ms  1.165 ms
 6  2001:4860::1:0:337f (2001:4860::1:0:337f)  2.131 ms  2.164 ms  2.109 ms
 7  2001:4860:0:1::523 (2001:4860:0:1::523)  2.266 ms  2.18 ms  2.02 ms
 8  2404:6800:8000:1c::8 (2404:6800:8000:1c::8)  1.741 ms  1.846 ms  1.895 ms
</code></pre>
<p>You may be familiar with is the <code>tracepath</code> command.  This follows the example of the other commands for the IPv6 version:</p>
<pre class="code-pre "><code langs="">tracepath6 ::1
</code></pre>
<hr />
<pre class="code-pre "><code langs=""> 1?: [LOCALHOST]                        0.045ms pmtu 65536
 1:  ip6-localhost                                         0.189ms reached
 1:  ip6-localhost                                         0.110ms reached
     Resume: pmtu 65536 hops 1 back 64
</code></pre>
<p>If you need to monitor traffic as it comes into your machine, the <code>tcpdump</code> program is often used.  We can get this utility to show only our IPv6 traffic by filtering it with the expression <code>ip6 or proto ipv6</code> after our options.</p>

<p>For example, we can measure rapidly flowing IPv6 traffic easily by telling the tool to only capture the information we're interested in.  We can use this command as taken from <a href="http://www.tldp.org/HOWTO/Linux+IPv6-HOWTO/x811.html">here</a> to only gather a summary of the information to avoid delaying output:</p>
<pre class="code-pre "><code langs="">tcpdump -t -n -i eth0 -s 512 -vv ip6 or proto ipv6
</code></pre>
<h2 id="checking-ipv6-dns-information">Checking IPv6 DNS Information</h2>

<p>You can easily check the DNS information for your domains by using the typical tools.  The main difference is that you will probably be asking for <code>AAAA</code> records, which are used for IPv6 addresses instead of <code>A</code> records, which are only used for IPv4 mapping.</p>

<p>To retrieve an IPv6 address record for a domain, you can simply request the <code>AAAA</code> record.  With the <code>host</code> command, you can do that like this:</p>
<pre class="code-pre "><code langs="">host -t AAAA google.com
</code></pre>
<hr />
<pre class="code-pre "><code langs="">google.com has IPv6 address 2404:6800:4003:803::1004
</code></pre>
<p>If you prefer to use <code>dig</code>, you can get similar results by using this syntax:</p>
<pre class="code-pre "><code langs="">dig google.com AAAA
</code></pre>
<hr />
<pre class="code-pre "><code langs="">; <<>> DiG 9.8.1-P1 <<>> google.com AAAA
;; global options: +cmd
;; Got answer:
;; ->>HEADER<<- opcode: QUERY, status: NOERROR, id: 14826
;; flags: qr rd ra; QUERY: 1, ANSWER: 1, AUTHORITY: 0, ADDITIONAL: 0

;; QUESTION SECTION:
;google.com.            IN  AAAA

;; ANSWER SECTION:
google.com.     299 IN  AAAA    2404:6800:4003:803::1006

;; Query time: 5 msec
;; SERVER: 8.8.4.4#53(8.8.4.4)
;; WHEN: Tue Apr  1 13:59:23 2014
;; MSG SIZE  rcvd: 56
</code></pre>
<p>As you can see, checking that your DNS is resolving correctly for your domains is just as easy when you are working with IPv6 addresses.</p>

<h2 id="network-services-with-ipv6">Network Services with IPv6</h2>

<p>Most of your common network services should have the ability to handle IPv6 traffic.  Sometimes, they need special flags or syntax, and other times, they provide an alternative implementation specifically for IPv6.</p>

<h3 id="ssh-configuration">SSH Configuration</h3>

<p>For SSH, the daemon can be configured to listen to an IPv6 address.  This is controlled in the configuration file that you can open with:</p>
<pre class="code-pre "><code langs="">sudo nano /etc/ssh/sshd_config
</code></pre>
<p>The <code>ListenAddress</code> specifies which address the SSH daemon will bind to.  For IPv4 addresses, this looks like this:</p>
<pre class="code-pre "><code langs="">ListenAddress 111.111.111.111:22
</code></pre>
<p>This listens to the IPv4 address <code>111.111.111.111</code> on port 22.  For an IPv6 address, you can do the same by placing the address in square brackets:</p>
<pre class="code-pre "><code langs="">ListenAddress [1341:8954:a389:33:ba33::1]:22
</code></pre>
<p>This tells the SSH daemon to listen to the <code>1341:8954:a389:33:ba33::1</code> address on port 22.  You can tell it to listen to <em>all</em> available IPv6 addresses by typing:</p>
<pre class="code-pre "><code langs="">ListenAddress ::
</code></pre>
<p>Remember to reload the daemon after you've made changes:</p>
<pre class="code-pre "><code langs="">sudo service ssh restart
</code></pre>
<p>On the client side, if the daemon that you are connecting to is configured to listen using IPv4 <em>and</em> IPv6, you can force the client to use IPv6 only by using the <code>-6</code> flag, like this:</p>
<pre class="code-pre "><code langs="">ssh -6 username@host.com
</code></pre>
<h3 id="web-server-configuration">Web Server Configuration</h3>

<p>Similar to the SSH daemon, web servers also must be configured to listen on IPv6 addresses.</p>

<p>In Apache, you can configure the server to respond to requests on a certain IPv6 address using this syntax:</p>
<pre class="code-pre "><code langs="">Listen [1341:8954:a389:33:ba33::1]:80
</code></pre>
<p>This tells the server to listen to this specific address on port 80.  We can combine this with an IPv4 address to allow more flexibility like this:</p>
<pre class="code-pre "><code langs="">Listen 111.111.111.111:80
Listen [1341:8954:a389:33:ba33::1]:80
</code></pre>
<p>In practice, if you want to listen to connections on all interfaces in all protocols on port 80, you could just use:</p>
<pre class="code-pre "><code langs="">Listen 80
</code></pre>
<p>On the virtualhost level, you can also specify an IPv6 address.  Here, you can see that it's possible to configure a virtualhost to match for both an IPv4 address and an IPv6 address:</p>
<pre class="code-pre "><code langs=""><VirtualHost 111.111.111.111:80, [1341:8954:a389:33:ba33::1]:80>
    . . .
</VirtualHost>
</code></pre>
<p>Remember to restart the service to make the changes:</p>
<pre class="code-pre "><code langs="">sudo service apache2 restart
</code></pre>
<p>If you prefer to use Nginx as your web server, we can implement similar configurations.  For the listen directive, we can use this for IPv6 traffic:</p>
<pre class="code-pre "><code langs="">listen [1341:8954:a389:33:ba33::1]:80;
</code></pre>
<p>In Linux, this actually enables IPv4 traffic on port 80 as well because it automatically maps IPv4 requests to the IPv6 address.  This actually prevents you from specifying an IPv6 address and IPv4 address separately like this:</p>
<pre class="code-pre "><code langs="">listen [1341:8954:a389:33:ba33::1]:80;
listen 111.111.111.111:80;
</code></pre>
<p>This will result in an error saying that the port is already bound to another service.  If you want to use separate directives like this, you must turn off this functionality using <code>sysctl</code> like this:</p>
<pre class="code-pre "><code langs="">sysctl -w net.ipv6.bindv6only=1
</code></pre>
<p>You can make sure this is automatically applied at boot by adding it to <code>/etc/sysctl.conf</code>:</p>
<pre class="code-pre "><code langs="">sudo nano /etc/sysctl.conf
</code></pre>
<hr />
<pre class="code-pre "><code langs="">. . .
net.ipv6.bindv6only=1
</code></pre>
<p>Afterwards, you can use use a similar configuration to the one that was failing before by adding the <code>ipv6only=on</code> flag to the IPv6 listening directive:</p>
<pre class="code-pre "><code langs="">listen [1341:8954:a389:33:ba33::1]:80 ipv6only=on;
listen 111.111.111.111:80;
</code></pre>
<p>Again, restart Nginx to make the changes:</p>
<pre class="code-pre "><code langs="">sudo service nginx restart
</code></pre>
<h3 id="firewall-configuration">Firewall Configuration</h3>

<p>If you are used to configuring your firewall rules using netfilter configuration front-ends like <code>iptables</code>, you'll be happy to know that there is an equivalent tool called <code>ip6tables</code>.</p>

<p>We have a guide here on <a href="https://indiareads/community/articles/how-to-set-up-a-firewall-using-ip-tables-on-ubuntu-12-04">how to configure iptables for Ubuntu</a> here.</p>

<p>For the IPv6 variant, you can simply replace the command with <code>ip6tables</code> to manage the IPv6 packet filter rules.  For instance, to list the IPv6 rules, you can type:</p>
<pre class="code-pre "><code langs="">sudo ip6tables -L
</code></pre>
<hr />
<pre class="code-pre "><code langs="">Chain INPUT (policy ACCEPT)
target     prot opt source               destination         

Chain FORWARD (policy ACCEPT)
target     prot opt source               destination         

Chain OUTPUT (policy ACCEPT)
target     prot opt source               destination
</code></pre>
<p>If you are using the <code>ufw</code> tool, then congratulations, you're already done!  The <code>ufw</code> tool configures both stacks at the same time unless otherwise specified.  You may have to add rules for your specific IPv6 addresses, but you will not have to use a different tool.</p>

<p>You can learn more about <a href="https://indiareads/community/articles/how-to-setup-a-firewall-with-ufw-on-an-ubuntu-and-debian-cloud-server">how to use ufw</a> here.</p>

<h3 id="tcp-wrappers-configuration">TCP Wrappers Configuration</h3>

<p>If you use TCP wrappers to control access to your server through the <code>/etc/hosts.allow</code> and <code>/etc/hosts.deny</code> files, you can simply use IPv6 syntax to match certain source rules.</p>

<p>For example, you could allow only an IPv4 and an IPv6 address to connect through SSH by typing editing the <code>/etc/hosts.allow</code> file and adding this:</p>
<pre class="code-pre "><code langs="">sudo nano /etc/hosts.allow
</code></pre>
<hr />
<pre class="code-pre "><code langs="">. . .
sshd: 111.111.0.0/255.255.254.0, [1341:8954:a389:33::]/64
</code></pre>
<p>As you can see, it is very easy to adapt your current TCP wrapper rules to apply to IPv6 addresses.  You can learn more about <a href="https://indiareads/community/articles/understanding-ip-addresses-subnets-and-cidr-notation-for-networking">how to format IP addresses and subnets</a> here.</p>

<h2 id="conclusion">Conclusion</h2>

<p>Hopefully, by now you realize that transitioning to IPv6 or taking advantage of IPv6 in addition to IPv4 is a fairly straight forward process.</p>

<p>You will have to specifically investigate any network services that you use to find out if there are any additional configuration changes that are needed to correctly utilize your IPv6 resources.  However, you should now feel more comfortable working with IPv6 with your most basic utilities and services.</p>

<div class="author">By Justin Ellingwood</div>

    