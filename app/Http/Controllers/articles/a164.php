<link rel='stylesheet' href='digital_ocean.css'><br> <br> 
      <h3 id="introduction">Introduction</h3>

<hr />

<p>DNS, or the Domain Name System, is often a difficult component to get right when learning how to configure websites and servers.  While most people will probably choose to use the DNS servers provided by their hosting company or their domain registrar, there are some advantages to creating your own DNS servers.</p>

<p>In this guide, we will discuss how to install and configure the Bind9 DNS server as a caching or forwarding DNS server on Ubuntu 14.04 machines.  These two configurations both have advantages when serving networks of machines.</p>

<h2 id="prerequisites-and-goals">Prerequisites and Goals</h2>

<p>To complete this guide, you will first need to be familiar with some common DNS terminology.  Check out <a href="https://indiareads/community/tutorials/an-introduction-to-dns-terminology-components-and-concepts">this guide</a> to learn about some of the concepts we will be implementing in this guide.</p>

<p>We will be demonstrating two separate configurations that accomplish similar goals: a caching and a forwarding DNS server.</p>

<p>To follow along, you will need to have access to two computers (at least one of which should be an Ubuntu 14.04 server).  One will function as the client and the other will be configured as the DNS server.  The details of our example configuration are:</p>

<table class="pure-table"><thead>
<tr>
<th>Role</th>
<th>IP Address</th>
</tr>
</thead><tbody>
<tr>
<td>DNS Server</td>
<td>192.0.2.1</td>
</tr>
<tr>
<td>Client</td>
<td>192.0.2.100</td>
</tr>
</tbody></table>

<p>We will show you how to configure the client machine to use the DNS server for queries.  We will show you how to configure the DNS server in two different configurations, depending on your needs.</p>

<h3 id="caching-dns-server">Caching DNS Server</h3>

<p>The first configuration will be for a <strong>caching</strong> DNS server.  This type of server is also known as a resolver because it handles recursive queries and generally can handle the grunt work of tracking down DNS data from other servers.</p>

<p>When a caching DNS server tracks down the answer to a client's query, it returns the answer to the client.  But it also stores the answer in its cache for the period of time allowed by the records' TTL value.  The cache can then be used as a source for subsequent requests in order to speed up the total round-trip time.</p>

<p>Almost all DNS servers that you might have in your network configuration will be caching DNS servers.  These make up for the lack of adequate DNS resolver libraries implemented on most client machines.  A caching DNS server is a good choice for many situations.  If you do not wish to rely on your ISPs DNS or other publicly available DNS servers, making your own caching server is a good choice.  If it is in close physical proximity to the client machines, it is also very likely to improve the DNS query times.</p>

<h3 id="forwarding-dns-server">Forwarding DNS Server</h3>

<p>The second configuration that we will be demonstrating is a <strong>forwarding</strong> DNS server.  A forwarding DNS server will look almost identical to a caching server from a client's perspective, but the mechanisms and work load are quite different.</p>

<p>A forwarding DNS server offers the same advantage of maintaining a cache to improve DNS resolution times for clients.  However, it actually does none of the recursive querying itself.  Instead, it forwards all requests to an outside resolving server and then caches the results to use for later queries.</p>

<p>This lets the forwarding server respond from its cache, while not requiring it to do all of the work of recursive queries.  This allows the server to only make single requests (the forwarded client request) instead of having to go through the entire recursion routine.  This may be an advantage in environments where external bandwidth transfer is costly, where your caching servers might need to be changed often, or when you wish to forward local queries to one server and external queries to another server.</p>

<h2 id="install-bind-on-the-dns-server">Install Bind on the DNS Server</h2>

<p>Regardless of which configuration choice you wish to use, the first step in implementing a Bind DNS server is to install the actual software.</p>

<p>The Bind software is available within Ubuntu's default repositories, so we just need to update our local package index and install the software using <code>apt</code>.  We will also include the documentation and some common utilities:</p>
<pre class="code-pre "><code langs="">sudo apt-get update
sudo apt-get install bind9 bind9utils bind9-doc
</code></pre>
<p>Now that the Bind components are installed, we can begin to configure the server.  The forwarding server will use the caching server configuration as a jumping off point, so regardless of your end goal, configure the server as a Caching server first.</p>

<h2 id="configure-as-a-caching-dns-server">Configure as a Caching DNS Server</h2>

<p>First, we will cover how to configure Bind to act as a caching DNS server.  This configuration will force the server to recursively seek answers from other DNS servers when a client issues a query.  This means that it is doing the work of querying each related DNS server in turn until it finds the entire response.</p>

<p>The Bind configuration files are kept by default in a directory at <code>/etc/bind</code>.  Move into that directory now:</p>
<pre class="code-pre "><code langs="">cd /etc/bind
</code></pre>
<p>We are not going to be concerned with the majority of the files in this directory.  The main configuration file is called <code>named.conf</code> (<code>named</code> and <code>bind</code> are two names for the same application).  This file simply sources the <code>named.conf.options</code> file, the <code>named.conf.local</code> file, and the <code>named.conf.default-zones</code> file.</p>

<p>For a caching DNS server, we will only be modifying the <code>named.conf.options</code> file.  Open this in your text editor with sudo privileges:</p>
<pre class="code-pre "><code langs="">sudo nano named.conf.options
</code></pre>
<p>With the comments stripped out for readability, the file looks like this:</p>
<pre class="code-pre "><code langs="">options {
        directory "/var/cache/bind";

        dnssec-validation auto;

        auth-nxdomain no;    # conform to RFC1035
        listen-on-v6 { any; };
};
</code></pre>
<p>To configure caching, the first step is to set up an access control list, or ACL.</p>

<p>As a DNS server that will be used to resolve recursive queries, we do not want the DNS server to be abused by malicious users.  An attack called a <strong>DNS amplification attack</strong> is especially troublesome because it can cause your server to participate in distributed denial of service attacks.</p>

<p>A DNS amplification attack is one way that malicious users try to take down servers or sites on the internet.  To do so, they try to find public DNS servers that will resolve recursive queries.  They spoof the victim's IP address and send a query that will return a large response to the DNS server.  In doing so, the DNS server responds to a small request with a large payload directed at the victims server, effectively amplifying the available bandwidth of the attacker.</p>

<p>Hosting a public, recursive DNS server requires a great deal of special configuration and administration.  To avoid the possibility of your server being used for malicious purposes, we will configure a list of IP addresses or network ranges that we trust.</p>

<p>Above the <code>options</code> block, we will create a new block called <code>acl</code>.  Create a label for the ACL group that you are configuring.  In this guide, we will call the group <code>goodclients</code>.</p>
<pre class="code-pre "><code langs="">acl goodclients {
};

options {
    . . .
</code></pre>
<p>Within this block, list the IP addresses or networks that should be allowed to use this DNS server.  Since both our server and client are operating within the same /24 subnet, we will restrict the example to this network.  We will also add <code>localhost</code> and <code>localnets</code> which will attempt to do this automatically:</p>
<pre class="code-pre "><code langs="">acl goodclients {
    192.0.2.0/24;
    localhost;
    localnets;
};

options {
    . . .
</code></pre>
<p>Now that we have an ACL of clients that we want to resolve request for, we can configure those capabilities in the <code>options</code> block.  Within this block, add the following lines:</p>
<pre class="code-pre "><code langs="">options {
    directory "/var/cache/bind";

    <span class="highlight">recursion yes;</span>
    <span class="highlight">allow-query { goodclients; };</span>
    . . .
</code></pre>
<p>We explicitly turned recursion on, and then configured the <code>allow-query</code> parameter to use our ACL specification.  We could have used a different parameter, like <code>allow-recursion</code> to reference our ACL group.  If present and recursion is on, <code>allow-recursion</code> will dictate the list of clients that can use recursive services. </p>

<p>However, if <code>allow-recursion</code> is not set, then Bind falls back on the <code>allow-query-cache</code> list, then the <code>allow-query</code> list, and finally a default of <code>localnets</code> and <code>localhost</code> only.  Since we are configuring a caching only server (it has no authoritative zones of its own and doesn't forward requests), the <code>allow-query</code> list will always apply only to recursion.  We are using it because it is the most general way of specifying the ACL.</p>

<p>When you are finished making these changes, save and close the file.</p>

<p>This is actually all that is required for a caching DNS server.  If you decided that this is the server type you wish to use, feel free to skip ahead to learn how to check your configuration files, restart the service, and implement client configurations.</p>

<p>Otherwise, continue reading to learn how to set up a forwarding DNS server instead.</p>

<h2 id="configure-as-a-forwarding-dns-server">Configure as a Forwarding DNS Server</h2>

<p>If a forwarding DNS server is a better fit for your infrastructure, we can easily set that up instead.</p>

<p>We will start with the configuration that we left off in the caching server configuration.  The <code>named.conf.options</code> file should look like this:</p>
<pre class="code-pre "><code langs="">acl goodclients {
        107.170.41.189;
        localhost;
        localnets;
};

options {
        directory "/var/cache/bind";

        recursion yes;
        allow-query { goodclients; };

        dnssec-validation auto;

        auth-nxdomain no;    # conform to RFC1035
        listen-on-v6 { any; };
};
</code></pre>
<p>We will be using the same ACL list to restrict our DNS server to a specific list of clients.  However, we need to change the configuration so that the server no longer attempts to perform recursive queries itself.</p>

<p>To do this, we do <em>not</em> change <code>recursion</code> to no.  The forwarding server is still providing recursive services by answering queries for zones it is not authoritative for.  Instead, we need to set up a list of caching servers to forward our requests to.</p>

<p>This will be done within the <code>options {}</code> block.  First, we create a block inside called <code>forwarders</code> that contains the IP addresses of the recursive name servers that we want to forward requests to.  In our guide, we will use Google's public DNS servers (<code>8.8.8.8</code> and <code>8.8.4.4</code>):</p>
<pre class="code-pre "><code langs="">. . .
options {
        directory "/var/cache/bind";

        recursion yes;
        allow-query { goodclients; };

        <span class="highlight">forwarders {</span>
                <span class="highlight">8.8.8.8;</span>
                <span class="highlight">8.8.4.4;</span>
        <span class="highlight">};</span>
        . . .
</code></pre>
<p>Afterward, we should set the <code>forward</code> directive to "only" since this server will forward all requests and should not attempt to resolve requests on its own.</p>

<p>The configuration file will look like this when you are finished:</p>
<pre class="code-pre "><code langs="">acl goodclients {
        107.170.41.189;
        localhost;
        localnets;
};

options {
        directory "/var/cache/bind";

        recursion yes;
        allow-query { goodclients; };

        forwarders {
                8.8.8.8;
                8.8.4.4;
        };
        <span class="highlight">forward only;</span>

        dnssec-validation auto;

        auth-nxdomain no;    # conform to RFC1035
        listen-on-v6 { any; };
};
</code></pre>
<p>One final change we should make is to the <code>dnssec</code> parameters.  With the current configuration, depending on the configuration of forwarded DNS servers, you may see some errors that look like this in the logs:</p>
<pre class="code-pre "><code langs="">Jun 25 15:03:29 cache named[2512]: error (chase DS servers) resolving 'in-addr.arpa/DS/IN': 8.8.8.8#53
Jun 25 15:03:29 cache named[2512]: error (no valid DS) resolving '111.111.111.111.in-addr.arpa/PTR/IN': 8.8.4.4#53
</code></pre>
<p>To avoid this, change the <code>dnssec-validation</code> setting to "yes" and explicitly enable dnssec:</p>
<pre class="code-pre "><code langs="">. . .
forward only;

<span class="highlight">dnssec-enable yes;</span>
dnssec-validation <span class="highlight">yes</span>;

auth-nxdomain no;    # conform to RFC1035
. . .
</code></pre>
<p>Save and close the file when you are finished.  You should now have a forwarding DNS server in place.  Continue to the next section to validate your configuration files and restart the daemon.</p>

<h2 id="test-your-configuration-and-restart-bind">Test your Configuration and Restart Bind</h2>

<p>Now that you have your Bind server configured as either a caching DNS server or a forwarding DNS server, we are ready to implement our changes.</p>

<p>Before we take the plunge and restart the Bind server on our system, we should use Bind's included tools to check the syntax of our configuration files.</p>

<p>We can do this easily by typing:</p>
<pre class="code-pre "><code langs="">sudo named-checkconf
</code></pre>
<p>If there are no syntax errors in your configuration, the shell prompt will return immediately without displaying any output. </p>

<p>If you have syntax errors in your configuration files, you will be alerted to the error and line number where it occurs.  If this happens, go back and check your files for errors.</p>

<p>When you have verified that your configuration files do not have any syntax errors, restart the Bind daemon to implement your changes:</p>
<pre class="code-pre "><code langs="">sudo service bind9 restart
</code></pre>
<p>Afterwards, keep an eye on the server logs while you set up your client machine to make sure that everything goes smoothly.  Leave this running on the server:</p>
<pre class="code-pre "><code langs="">sudo tail -f /var/log/syslog
</code></pre>
<p>Now, open a new terminal window to configure your client machines.</p>

<h2 id="configure-the-client-machine">Configure the Client Machine</h2>

<p>Now that you have your server up and running, you can configure your client machine to use this DNS server for queries.</p>

<p>Log into your client machine.  Make sure that the client you are using was specified in the ACL group you set for your DNS server.  Otherwise the DNS server will refuse to serve requests for the client.</p>

<p>We need to edit the <code>/etc/resolv.conf</code> file to point our server to the name server.  Changes made here will only last until reboot, which is great for testing.  If we are satisfied with the results of our tests, we can make these changes permanent.</p>

<p>Open the file with sudo privileges in your text editor:</p>
<pre class="code-pre "><code langs="">sudo nano /etc/resolv.conf
</code></pre>
<p>The file will list the DNS servers to use to resolve queries by setting the <code>nameserver</code> directives.  Comment out all of the current entries and add a <code>nameserver</code> line that points to your DNS server:</p>
<pre class="code-pre "><code langs="">nameserver <span class="highlight">192.0.2.1</span>
# nameserver 8.8.4.4
# nameserver 8.8.8.8
# nameserver 209.244.0.3
</code></pre>
<p>Save and close the file.</p>

<p>Now, you can test to make sure queries can resolve correctly by using some common tools.</p>

<p>You can use <code>ping</code> to test that connections can be made to domains:</p>
<pre class="code-pre "><code langs="">ping -c 1 google.com
</code></pre><pre class="code-pre "><code langs="">PING google.com (173.194.33.1) 56(84) bytes of data.
64 bytes from sea09s01-in-f1.1e100.net (173.194.33.1): icmp_seq=1 ttl=55 time=63.8 ms

--- google.com ping statistics ---
1 packets transmitted, 1 received, 0% packet loss, time 0ms
rtt min/avg/max/mdev = 63.807/63.807/63.807/0.000 ms
</code></pre>
<p>This means that our client can connect with <code>google.com</code> using our DNS server.</p>

<p>We can get more detailed information by using DNS specific tools like <code>dig</code>.  Try a different domain this time:</p>
<pre class="code-pre "><code langs="">dig linuxfoundation.org
</code></pre><pre class="code-pre "><code langs="">; <<>> DiG 9.9.5-3-Ubuntu <<>> linuxfoundation.org
;; global options: +cmd
;; Got answer:
;; ->>HEADER<<- opcode: QUERY, status: NOERROR, id: 35417
;; flags: qr rd ra; QUERY: 1, ANSWER: 1, AUTHORITY: 0, ADDITIONAL: 1

;; OPT PSEUDOSECTION:
; EDNS: version: 0, flags:; udp: 4096
;; QUESTION SECTION:
;linuxfoundation.org.       IN  A

;; ANSWER SECTION:
linuxfoundation.org.    6017    IN  A   140.211.169.4

;; Query time: <span class="highlight">36 msec</span>
;; SERVER: 192.0.2.1#53(192.0.2.1)
;; WHEN: Wed Jun 25 15:45:57 EDT 2014
;; MSG SIZE  rcvd: 64
</code></pre>
<p>You can see that the query took 36 milliseconds.  If we make the request again, the server should pull the data from its cache, decreasing the response time:</p>
<pre class="code-pre "><code langs="">dig linuxfoundation.org
</code></pre><pre class="code-pre "><code langs="">; <<>> DiG 9.9.5-3-Ubuntu <<>> linuxfoundation.org
;; global options: +cmd
;; Got answer:
;; ->>HEADER<<- opcode: QUERY, status: NOERROR, id: 18275
;; flags: qr rd ra; QUERY: 1, ANSWER: 1, AUTHORITY: 0, ADDITIONAL: 1

;; OPT PSEUDOSECTION:
; EDNS: version: 0, flags:; udp: 4096
;; QUESTION SECTION:
;linuxfoundation.org.       IN  A

;; ANSWER SECTION:
linuxfoundation.org.    6012    IN  A   140.211.169.4

;; Query time: <span class="highlight">1 msec</span>
;; SERVER: 192.0.2.1#53(192.0.2.1)
;; WHEN: Wed Jun 25 15:46:02 EDT 2014
;; MSG SIZE  rcvd: 64
</code></pre>
<p>As you can see, the cached response is significantly faster.</p>

<p>We can also test the reverse lookup by using the IP address that we found (<code>140.211.169.4</code> in our case) with dig's <code>-x</code> option:</p>
<pre class="code-pre "><code langs="">dig -x 140.211.169.4
</code></pre><pre class="code-pre "><code langs="">; <<>> DiG 9.9.5-3-Ubuntu <<>> -x 140.211.169.4
;; global options: +cmd
;; Got answer:
;; ->>HEADER<<- opcode: QUERY, status: NOERROR, id: 61516
;; flags: qr rd ra; QUERY: 1, ANSWER: 2, AUTHORITY: 0, ADDITIONAL: 1

;; OPT PSEUDOSECTION:
; EDNS: version: 0, flags:; udp: 4096
;; QUESTION SECTION:
;4.169.211.140.in-addr.arpa.    IN  PTR

;; ANSWER SECTION:
4.169.211.140.in-addr.arpa. 3402 IN CNAME   4.0-63.169.211.140.in-addr.arpa.
4.0-63.169.211.140.in-addr.arpa. 998 IN PTR load1a.linux-foundation.org.

;; Query time: 31 msec
;; SERVER: 192.0.2.1#53(192.0.2.1)
;; WHEN: Wed Jun 25 15:51:23 EDT 2014
;; MSG SIZE  rcvd: 117
</code></pre>
<p>As you can see, the reverse lookup also succeeds.</p>

<p>Back on your DNS server, you should see if any errors have been recorded during your tests.  One common error that may show up looks like this:</p>
<pre class="code-pre "><code langs="">. . .
Jun 25 13:16:22 cache named[2004]: error (network unreachable) resolving 'ns4.apnic.net/A/IN': 2001:dc0:4001:1:0:1836:0:140#53
Jun 25 13:16:22 cache named[2004]: error (network unreachable) resolving 'ns4.apnic.com/A/IN': 2001:503:a83e::2:30#53
Jun 25 13:16:23 cache named[2004]: error (network unreachable) resolving 'sns-pb.isc.org/AAAA/IN': 2001:500:f::1#53
Jun 25 13:16:23 cache named[2004]: error (network unreachable) resolving 'ns3.nic.fr/A/IN': 2a00:d78:0:102:193:176:144:22#53
</code></pre>
<p>These indicate that the server is trying to resolve IPv6 information but that the server is not configured for IPv6.  You can fix this issue by telling Bind to only use IPv4.</p>

<p>To do this, open the <code>/etc/default/bind9</code> file with sudo privileges:</p>
<pre class="code-pre "><code langs="">sudo nano /etc/default/bind9
</code></pre>
<p>Inside, modify the <code>OPTIONS</code> parameter to include the <code>-4</code> flag to force IPv4 only behavior:</p>
<pre class="code-pre "><code langs="">OPTIONS="-u bind <span class="highlight">-4</span>"
</code></pre>
<p>Save and close the file.</p>

<p>Restart the server:</p>
<pre class="code-pre "><code langs="">sudo service bind9 restart
</code></pre>
<p>You should not see these errors in the logs again.</p>

<h3 id="making-client-dns-settings-permanent">Making Client DNS Settings Permanent</h3>

<p>As mentioned before, the <code>/etc/resolv.conf</code> settings that point the client machine to our DNS server will not survive a reboot.  To make the changes last, we need to modify the files that are used to generate this file.</p>

<p>If the client machine is running Debian or Ubuntu, open the <code>/etc/network/interfaces</code> file with sudo privileges:</p>
<pre class="code-pre "><code langs="">sudo nano /etc/network/interfaces
</code></pre>
<p>Look for the <code>dns-nameservers</code> parameter.  You can remove the existing entries and replace them with your DNS server or just add your DNS server as one of the options:</p>
<pre class="code-pre "><code langs="">. . .
iface eth0 inet static
        address 111.111.111.111
        netmask 255.255.255.0
        gateway 111.111.0.1
        dns-nameservers <span class="highlight">192.0.2.1</span>
. . .
</code></pre>
<p>Save and close the file when you are finished.  Next time you boot up, your settings will be applied.</p>

<p>If the client is running CentOS or Fedora, you need to open the <code>/etc/sysconfig/network/network-scripts/ifcfg-eth0</code> file instead:</p>
<pre class="code-pre "><code langs="">sudo nano /etc/sysconfig/network-scripts/ifcfg-eth0
</code></pre>
<p>Inside, look for the lines that begin with <code>DNS</code>.  Change <code>DNS1</code> to your DNS server.  If you don't want to use the other DNS servers as a fallback, remove the other entries:</p>
<pre class="code-pre "><code langs="">DNS1=<span class="highlight">192.0.2.1</span>
</code></pre>
<p>Save and close the file when you are finished.  Your client should use those settings at next boot.</p>

<h2 id="conclusion">Conclusion</h2>

<p>You should now have either a caching or forwarding DNS server configured to serve your clients.  This can be a great way to speed up DNS queries for the machines you are managing.</p>

<p>If you want to create a DNS server that is authoritative for your own domain zones, you can configure an <a href="https://indiareads/community/tutorials/how-to-configure-bind-as-an-authoritative-only-dns-server-on-ubuntu-14-04">authoritative-only DNS server</a> or combine these solutions.</p>

    