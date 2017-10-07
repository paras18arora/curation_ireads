<link rel='stylesheet' href='digital_ocean.css'><br> <br> 
      <h3 id="introduction">Introduction</h3>

<p>In this tutorial we will learn how to set up PowerDNS in a master/slave configuration with automatic replication from the master DNS server to the slave. This tutorial is the second tutorial in our <a href="https://indiareads/community/tutorials/how-to-install-and-configure-powerdns-with-a-mariadb-backend-on-ubuntu-14-04">PowerDNS</a> series for Ubuntu.</p>

<p>A master/slave configuration provides additional reliability. If one of your PowerDNS servers goes down, you will have a secondary server to handle the requests.</p>

<p>We recommend provisioning these servers in seperate data centers. If they are in two physical locations, then even a data center outage would not affect your DNS service.</p>

<p>By the end of this tutorial we will have two functional PowerDNS servers using master/slave replication.</p>

<h2 id="prerequisites">Prerequisites</h2>

<p>Please complete these requirements:</p>

<ul>
<li>Two 512 MB Droplets or larger with Ubuntu 14.04 64-bit. 512 MB should be plenty to run a PowerDNS server with a moderate number of zones/records</li>
<li>A <a href="https://indiareads/community/tutorials/how-to-add-and-delete-users-on-an-ubuntu-14-04-vps">sudo user</a></li>
<li><a href="https://indiareads/community/tutorials/how-to-install-and-configure-powerdns-with-a-mariadb-backend-on-ubuntu-14-04">How To Install and Configure PowerDNS with a MariaDB Backend on Ubuntu 14.04</a> (details in Step 1)</li>
<li>Glue records and nameserver settings for domains configured at your registrar</li>
</ul>

<p>In our previous tutorial, we pointed three subdomains to a single PowerDNS server. We will now be using one of these subdomains to point at our slave server. In our examples our <strong>master server IP</strong> will be <code><span class="highlight">111.111.111.111</span></code>, and our <strong>slave server IP</strong> will be <code><span class="highlight">222.222.222.222</span></code>.</p>

<p>You will need to update the glue records with your provider accordingly. Please use the information below as a guide. See the previous PowerDNS tutorial for more information on configuring your DNS records.</p>

<ul>
<li><strong>hostmaster.example-dns.com</strong> <code><span class="highlight">111.111.111.111</span></code>  (Master Server)</li>
<li><strong>ns1.example-dns.com</strong> <code><span class="highlight">111.111.111.111</span></code> (Master Server)</li>
<li><strong>ns2.example-dns.com</strong> <code><span class="highlight">222.222.222.222</span></code>  (Slave Server)</li>
</ul>

<p>Note that you should set up both glue records and SOA records at your registrar for the domain used for the nameservers themselves. On the other hand, you need only SOA records for other domains whose zone files you want to host on your custom nameservers.</p>

<h2 id="step-1-—-install-powerdns-on-both-servers">Step 1 — Install PowerDNS on Both Servers</h2>

<p>First, we need to have two functional PowerDNS servers. One server will become our master server, while the second one will become our slave server.</p>

<p>If you haven't done so already, please follow the previous tutorial, <a href="https://indiareads/community/tutorials/how-to-install-and-configure-powerdns-with-a-mariadb-backend-on-ubuntu-14-04">How To Install and Configure PowerDNS with a MariaDB Backend on Ubuntu 14.04</a>.</p>

<p>You should follow the complete tutorial on your <strong>master server</strong>.</p>

<p>You can follow just Steps 1-7 on your <strong>slave server</strong>, since we don't need Poweradmin on the secondary server.</p>

<p>When you have two functional PowerDNS servers, with at least one of them running Poweradmin, you can proceed to the next step.</p>

<h2 id="step-2-—-configure-master-server-ns1-example-dns-com">Step 2 — Configure Master Server (ns1.example-dns.com)</h2>

<p>We are now ready to configure our master PowerDNS server.</p>

<p>This should be the server that has Poweradmin installed, and will be considered your <strong>primary DNS server</strong>. If you have Poweradmin installed on both servers, you may use either one. If you're following this example, this should be <strong>ns1.example-dns.com</strong>.</p>

<p>Back up the original configuration file.</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">cd /etc/powerdns
</li><li class="line" prefix="$">sudo mv pdns.conf pdns.conf.orig
</li></ul></code></pre>
<p>Create our new configuration file.</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo nano pdns.conf
</li></ul></code></pre>
<p>The details below are for a standard master server configuration with a single slave server. We will enter the slave server IP address, allowing it to communicate with this master server. Remember to substitute your own <strong>slave server IP address</strong> below.</p>

<p><strong>Note: /32 is a single IP subnet, and required for this configuration.</strong></p>
<div class="code-label " title="/etc/powerdns/pdns.conf">/etc/powerdns/pdns.conf</div><pre class="code-pre "><code langs="">allow-recursion=0.0.0.0/0
allow-axfr-ips=<span class="highlight">222.222.222.222</span>/32
config-dir=/etc/powerdns
daemon=yes
disable-axfr=no
guardian=yes
local-address=0.0.0.0
local-port=53
log-dns-details=on
log-failed-updates=on
loglevel=3
module-dir=/usr/lib/powerdns
master=yes
slave=no
setgid=pdns
setuid=pdns
socket-dir=/var/run
version-string=powerdns
include-dir=/etc/powerdns/pdns.d
</code></pre>
<p>Restart the PowerDNS service for changes to take effect.</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo service pdns restart
</li></ul></code></pre>
<h2 id="step-3-—-configure-slave-server-ns2-example-dns-com">Step 3 — Configure Slave Server (ns2.example-dns.com)</h2>

<p>Now we are ready to configure our <strong>slave server</strong>. This server will replicate DNS zones from the master server we just configured. If you're following along with the example, this should be <strong>ns2.example-dns.com</strong>.</p>

<p>Back up the original configuration file.</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">cd /etc/powerdns
</li><li class="line" prefix="$">sudo mv pdns.conf pdns.conf.orig
</li></ul></code></pre>
<p>Create the new configuration file.</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo nano pdns.conf
</li></ul></code></pre>
<p>The details below are for a standard slave server configuration with a 60-second refresh interval. You can copy the configuration exactly.</p>
<div class="code-label " title="/etc/powerdns/pdns.conf">/etc/powerdns/pdns.conf</div><pre class="code-pre "><code langs="">allow-recursion=0.0.0.0/0
config-dir=/etc/powerdns
daemon=yes
disable-axfr=yes
guardian=yes
local-address=0.0.0.0
local-port=53
log-dns-details=on
log-failed-updates=on
loglevel=3
module-dir=/usr/lib/powerdns
master=no
slave=yes
slave-cycle-interval=60
setgid=pdns
setuid=pdns
socket-dir=/var/run
version-string=powerdns
include-dir=/etc/powerdns/pdns.d
</code></pre>
<p>Every 60 seconds, the slave server will query the master server for zone updates. Typically when a zone is updated, the master server will send a notification to the slave servers assigned to that zone. However, if there is a connection issue during a zone update, this ensures the update will eventually propegate to the slave server when it is online again.</p>

<p>Next we need to tell PowerDNS how to communicate with the master server.</p>

<p>Log in to MariaDB with the PowerDNS username and password you created in the previous tutorial. Our example used <code>powerdns_user</code>.</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">mysql -u <span class="highlight">powerdns_user</span> -p
</li></ul></code></pre>
<p>Enter your password at the prompt:</p>
<pre class="code-pre "><code langs=""><div class="secondary-code-label " title="Output">Output</div>Enter password:
</code></pre>
<p>Change to the PowerDNS database you configured in the previous tutorial. Our recommendation was <code>powerdns</code>.</p>
<pre class="code-pre custom_prefix"><code langs=""><ul class="prefixed"><li class="line" prefix="mysql>">USE powerdns;
</li></ul></code></pre>
<p>Next we will crate a new row in the <code>supermasters</code> table. This row will specify the <strong>master server IP</strong> address, and the Fully Qualified Domain Name <strong>(FQDN) of the slave server</strong> we are currently configuring.</p>
<pre class="code-pre custom_prefix"><code langs=""><ul class="prefixed"><li class="line" prefix="mysql>">insert into supermasters values ('<span class="highlight">111.111.111.111</span>', '<span class="highlight">ns2.example-dns.com</span>', 'admin');
</li></ul></code></pre>
<p>We can now exit the MariaDB shell.</p>
<pre class="code-pre custom_prefix"><code langs=""><ul class="prefixed"><li class="line" prefix="mysql>">exit;
</li></ul></code></pre>
<p>Restart the PowerDNS service for changes to take effect.</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo service pdns restart
</li></ul></code></pre>
<h2 id="step-4-—-test-master-slave-connection">Step 4 — Test Master/Slave Connection</h2>

<p>This step requires <strong>ns1.example-dns.com</strong> to be pointing to your master server, and <strong>ns2.example-dns.com</strong> to be pointing to your slave server.</p>

<p>If your glue records, SOA records, and A records haven't propagated yet, you can add an override to your <code>/etc/hosts</code> file. You will want to do this on <strong>both servers</strong>.</p>

<p>Open the <code>/etc/hosts</code> using nano.</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo nano /etc/hosts
</li></ul></code></pre>
<p>Add the entries to your <code>/etc/hosts</code> file.</p>
<div class="code-label " title="/etc/hosts">/etc/hosts</div><pre class="code-pre "><code langs=""><span class="highlight">111.111.111.111 ns1.example-dns.com</span>
<span class="highlight">222.222.222.222 ns2.example-dns.com</span>
</code></pre>
<p>Let's make sure our two servers can communicate now.</p>

<p>From your <strong>master server</strong>, ping both hostnames.</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">ping ns1.example-dns.com
</li></ul></code></pre>
<p>Your result should look like this:</p>
<pre class="code-pre "><code langs=""><div class="secondary-code-label " title="Output">Output</div>64 bytes from ns1.example-dns.com (111.111.111.111): icmp_seq=1 ttl=64 time=0.061 ms
</code></pre>
<p>Ping the slave server:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">ping ns2.example-dns.com
</li></ul></code></pre>
<p>Expected result:</p>
<pre class="code-pre "><code langs=""><div class="secondary-code-label " title="Output">Output</div>64 bytes from ns2.example-dns.com (222.222.222.222): icmp_seq=1 ttl=64 time=48.8 ms
</code></pre>
<p>Now, ping both hostnames from your <strong>slave server</strong>, using the same commands. Once you can ping both servers from both server, continue.</p>

<h2 id="step-5-—-configure-a-dns-zone-with-replication">Step 5 — Configure a DNS Zone with Replication</h2>

<p>If both servers are communicating properly we are ready to create our first DNS zone with master/slave replication.</p>

<p>Log in to Poweradmin on your master server by visitng <code>http://<span class="highlight">111.111.111.111</span>/poweradmin/</code> in your browser.</p>

<p><img src="https://assets.digitalocean.com/articles/poweradmin-slave/SMepFsRh.png" alt="Poweradmin login screen" /></p>

<p>Log in with the admin credentials you set earlier.</p>

<p>Click the <strong>Add master zone</strong> link to create a new zone file. You can test this with the original name or a new domain, <strong>test.com</strong>.</p>

<p><img src="https://assets.digitalocean.com/articles/poweradmin-slave/AVTowJ0h.png" alt="Click the Add master zone link" /></p>

<p>Enter your top-level domain name, and click the <strong>Add zone</strong> button to create the zone.</p>

<p><img src="https://assets.digitalocean.com/articles/poweradmin-slave/DLbU5kMh.png" alt="Enter your domain name in the Zone name field" /></p>

<p>Create <strong>NS</strong> entries for your name servers:</p>

<ul>
<li><strong>hostmaster.example-dns.com</strong></li>
<li><strong>ns1.example-dns.com</strong></li>
<li><strong>ns2.example-dns.com</strong> </li>
</ul>

<p>Create at least one <strong>A</strong> record to test replication.</p>

<p><img src="https://assets.digitalocean.com/articles/poweradmin-slave/ftZDoc7h.png" alt="Add your NS and A records" /></p>

<p><strong>Note: If your Slave Server is not listed as a name server for the zone, it will not replicate the zone.</strong></p>

<p>After a few seconds the new entries should propagate to your slave server.</p>

<p>Test the DNS record saved at <strong>ns1.example-dns.com</strong> using <code>dig</code>.</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">dig <span class="highlight">test.com</span> A @<span class="highlight">ns1.example-dns.com</span>
</li></ul></code></pre>
<p>It should respond with a result similar to the one below.</p>
<pre class="code-pre "><code langs=""><div class="secondary-code-label " title="Output">Output</div>root@ns1:/etc/powerdns# dig test.com A <a href="https://indiareads/community/users/ns1" class="username-tag">@ns1</a>.example-dns.com

; <<>> DiG 9.9.5-3ubuntu0.2-Ubuntu <<>> test.com A <a href="https://indiareads/community/users/ns1" class="username-tag">@ns1</a>.example-dns.com
;; global options: +cmd
;; Got answer:
;; ->>HEADER<<- opcode: QUERY, status: NOERROR, id: 44833
;; flags: qr aa rd; QUERY: 1, ANSWER: 1, AUTHORITY: 0, ADDITIONAL: 1
;; WARNING: recursion requested but not available

;; OPT PSEUDOSECTION:
; EDNS: version: 0, flags:; udp: 2800
;; QUESTION SECTION:
;test.com.                      IN      A

;; ANSWER SECTION:
<span class="highlight">test.com.               86400   IN      A       104.131.174.138</span>

;; Query time: 2 msec
;; SERVER: 45.55.217.94#53(45.55.217.94)
;; WHEN: Tue Apr 28 18:06:54 EDT 2015
;; MSG SIZE  rcvd: 53

</code></pre>
<p>Test the DNS record saved at <strong>ns2.example-dns.com</strong> using <code>dig</code>.</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">dig <span class="highlight">test.com</span> A @<span class="highlight">ns2.example-dns.com</span>
</li></ul></code></pre>
<p>It should respond with a result similar to the one below.</p>
<pre class="code-pre "><code langs=""><div class="secondary-code-label " title="Output">Output</div>root@ns1:/etc/powerdns# dig test.com A <a href="https://indiareads/community/users/ns2" class="username-tag">@ns2</a>.example-dns.com

; <<>> DiG 9.9.5-3ubuntu0.2-Ubuntu <<>> test.com A <a href="https://indiareads/community/users/ns2" class="username-tag">@ns2</a>.example-dns.com
;; global options: +cmd
;; Got answer:
;; ->>HEADER<<- opcode: QUERY, status: NOERROR, id: 11530
;; flags: qr aa rd; QUERY: 1, ANSWER: 1, AUTHORITY: 0, ADDITIONAL: 1
;; WARNING: recursion requested but not available

;; OPT PSEUDOSECTION:
; EDNS: version: 0, flags:; udp: 2800
;; QUESTION SECTION:
;test.com.                      IN      A

;; ANSWER SECTION:
<span class="highlight">test.com.               86400   IN      A       104.131.174.138</span>

;; Query time: 3 msec
;; SERVER: 45.55.217.132#53(45.55.217.132)
;; WHEN: Tue Apr 28 18:08:06 EDT 2015
;; MSG SIZE  rcvd: 53
</code></pre>
<p>Remember that the settings for <strong>test.com</strong> will only become active after setting your nameservers to <strong>ns1.example-dns.com</strong> and <strong>ns2.example-dns.com</strong> at your registrar.</p>

<h2 id="conclusion">Conclusion</h2>

<p>We now have two functional PowerDNS servers using a MariaDB backend in a master/slave configuration.</p>

<p>Any time changes are made to a master zone on the master server, it will notify any slave servers listed with their own <strong>NS</strong> records.</p>

<p>The slave server will automatically query the Master Server for records that have not been updated recently, ensuring your DNS records stay in sync among your PowerDNS nodes.</p>

    