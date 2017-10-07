<link rel='stylesheet' href='digital_ocean.css'><br><img src=https://community-cdn-digitalocean-com.global.ssl.fastly.net/assets/tutorials/images/large/HowToConfigureBIND-CentOS-twitter.png?1441228385/> <br> 
      <h3 id="introduction">Introduction</h3>

<p>An important part of managing server configuration and infrastructure includes maintaining an easy way to look up network interfaces and IP addresses by name, by setting up a proper Domain Name System (DNS). Using fully qualified domain names (FQDNs), instead of IP addresses, to specify network addresses eases the configuration of services and applications, and increases the maintainability of configuration files. Setting up your own DNS for your private network is a great way to improve the management of your servers.</p>

<p>In this tutorial, we will go over how to set up an internal DNS server, using the BIND name server software (BIND9) on CentOS 7, that can be used by your Virtual Private Servers (VPS) to resolve private host names and private IP addresses. This provides a central way to manage your internal hostnames and private IP addresses, which is indispensable when your environment expands to more than a few hosts.</p>

<p>The Ubuntu version of this tutorial can be found <a href="https://indiareads/community/tutorials/how-to-configure-bind-as-a-private-network-dns-server-on-ubuntu-14-04">here</a>.</p>

<h2 id="prerequisites">Prerequisites</h2>

<p>To complete this tutorial, you will need the following:</p>

<ul>
<li>Some servers that are running in the same datacenter and have <a href="https://indiareads/community/tutorials/how-to-set-up-and-use-digitalocean-private-networking">private networking enabled</a></li>
<li>A new VPS to serve as the Primary DNS server, <em>ns1</em></li>
<li>Optional: A new VPS to serve as a Secondary DNS server, <em>ns2</em></li>
<li>Root access to all of the above (<a href="https://indiareads/community/tutorials/initial-server-setup-with-centos-7">steps 1-4 here</a>)</li>
</ul>

<p>If you are unfamiliar with DNS concepts, it is recommended that you read at least the first three parts of our <a href="https://indiareads/community/tutorial_series/an-introduction-to-managing-dns">Introduction to Managing DNS</a>.</p>

<h3 id="example-hosts">Example Hosts</h3>

<p>For example purposes, we will assume the following:</p>

<ul>
<li>We have two existing VPS called "host1" and "host2"</li>
<li>Both VPS exist in the nyc3 datacenter</li>
<li>Both VPS have private networking enabled (and are on the 10.128.0.0/16 subnet)</li>
<li>Both VPS are somehow related to our web application that runs on "example.com"</li>
</ul>

<p>With these assumptions, we decide that it makes sense to use a naming scheme that uses "nyc3.example.com" to refer to our private subnet or zone. Therefore, <em>host1</em>'s private Fully-Qualified Domain Name (FQDN) will be "host1.nyc3.example.com". Refer to the following table the relevant details:</p>

<table class="pure-table"><thead>
<tr>
<th>Host</th>
<th>Role</th>
<th>Private FQDN</th>
<th>Private IP Address</th>
</tr>
</thead><tbody>
<tr>
<td>host1</td>
<td>Generic Host 1</td>
<td>host1.nyc3.example.com</td>
<td>10.128.100.101</td>
</tr>
<tr>
<td>host2</td>
<td>Generic Host 2</td>
<td>host2.nyc3.example.com</td>
<td>10.128.200.102</td>
</tr>
</tbody></table>

<p><strong>Note:</strong> Your existing setup will be different, but the example names and IP addresses will be used to demonstrate how to configure a DNS server to provide a functioning internal DNS. You should be able to easily adapt this setup to your own environment by replacing the host names and private IP addresses with your own. It is not necessary to use the region name of the datacenter in your naming scheme, but we use it here to denote that these hosts belong to a particular datacenter's private network. If you utilize multiple datacenters, you can set up an internal DNS within each respective datacenter.</p>

<h2 id="our-goal">Our Goal</h2>

<p>By the end of this tutorial, we will have a primary DNS server, <em>ns1</em>, and optionally a secondary DNS server, <em>ns2</em>, which will serve as a backup.</p>

<p>Here is a table with example names and IP addresses:</p>

<table class="pure-table"><thead>
<tr>
<th>Host</th>
<th>Role</th>
<th>Private FQDN</th>
<th>Private IP Address</th>
</tr>
</thead><tbody>
<tr>
<td>ns1</td>
<td>Primary DNS Server</td>
<td>ns1.nyc3.example.com</td>
<td>10.128.10.11</td>
</tr>
<tr>
<td>ns2</td>
<td>Secondary DNS Server</td>
<td>ns2.nyc3.example.com</td>
<td>10.128.20.12</td>
</tr>
</tbody></table>

<p>Let's get started by installing our Primary DNS server, ns1.</p>

<h2 id="install-bind-on-dns-servers">Install BIND on DNS Servers</h2>

<p><strong>Note:</strong> Text that is highlighted in <span class="highlight">red</span> is important! It will often be used to denote something that needs to be replaced with your own settings or that it should be modified or added to a configuration file. For example, if you see something like <span class="highlight">host1.nyc3.example.com</span>, replace it with the FQDN of your own server. Likewise, if you see <span class="highlight">host1_private_IP</span>, replace it with the private IP address of your own server.</p>

<p>On both DNS servers, <em>ns1</em> and <em>ns2</em>, install BIND with yum:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo yum install bind bind-utils
</li></ul></code></pre>
<p>Confirm the prompt by entering <code>y</code>.</p>

<p>Now that BIND is installed, let's configure the primary DNS server.</p>

<h2 id="configure-primary-dns-server">Configure Primary DNS Server</h2>

<p>BIND's configuration consists of multiple files, which are included from the main configuration file, <code>named.conf</code>. These filenames begin with "named" because that is the name of the process that BIND runs. We will start with configuring the options file.</p>

<h3 id="configure-bind">Configure Bind</h3>

<p>BIND's process is known as <strong>named</strong>. As such, many of the files refer to "named" instead of "BIND".</p>

<p>On <em>ns1</em>, open the <code>named.conf</code> file for editing:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo vi /etc/named.conf
</li></ul></code></pre>
<p>Above the existing <code>options</code> block, create a new ACL block called "trusted". This is where we will define list of clients that we will allow recursive DNS queries from (i.e. your servers that are in the same datacenter as ns1). Using our example private IP addresses, we will add <em>ns1</em>, <em>ns2</em>, <em>host1</em>, and <em>host2</em> to our list of trusted clients:</p>
<div class="code-label " title="/etc/named.conf — 1 of 4">/etc/named.conf — 1 of 4</div><pre class="code-pre "><code langs="">acl "trusted" {
        <span class="highlight">10.128.10.11</span>;    # ns1 - can be set to localhost
        <span class="highlight">10.128.20.12</span>;    # ns2
        <span class="highlight">10.128.100.101</span>;  # host1
        <span class="highlight">10.128.200.102</span>;  # host2
};
</code></pre>
<p>Now that we have our list of trusted DNS clients, we will want to edit the <code>options</code> block. Add the private IP address of ns1 to the <code>listen-on port 53</code> directive, and comment out the <code>listen-on-v6</code> line:</p>
<div class="code-label " title="/etc/named.conf — 2 of 4">/etc/named.conf — 2 of 4</div><pre class="code-pre "><code langs="">options {
        listen-on port 53 { 127.0.0.1; <span class="highlight">10.128.10.11;</span> };
<span class="highlight">#</span>        listen-on-v6 port 53 { ::1; };
...
</code></pre>
<p>Below those entries, change the <code>allow-transfer</code> directive to from "none" to <strong>ns2</strong>'s private IP address. Also, change <code>allow-query</code> directive from "localhost" to "trusted":</p>
<div class="code-label " title="/etc/named.conf — 3 of 4">/etc/named.conf — 3 of 4</div><pre class="code-pre "><code langs="">...
options {
...
        allow-transfer { <span class="highlight">10.128.20.12</span>; };      # disable zone transfers by default
...
        allow-query { <span class="highlight">trusted;</span> };  # allows queries from "trusted" clients
...
</code></pre>
<p>At the end of the file, add the following line:</p>
<div class="code-label " title="/etc/named.conf — 4 of 4">/etc/named.conf — 4 of 4</div><pre class="code-pre "><code langs="">include "/etc/named/named.conf.local";
</code></pre>
<p>Now save and exit <code>named.conf</code>. The above configuration specifies that only your own servers (the "trusted" ones) will be able to query your DNS server.</p>

<p>Next, we will configure the local file, to specify our DNS zones.</p>

<h3 id="configure-local-file">Configure Local File</h3>

<p>On <em>ns1</em>, open the <code>named.conf.local</code> file for editing:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo vi /etc/named/named.conf.local
</li></ul></code></pre>
<p>The file should be empty. Here, we will specify our forward and reverse zones.</p>

<p>Add the forward zone with the following lines (substitute the zone name with your own):</p>
<div class="code-label " title="/etc/named/named.conf.local — 1 of 2">/etc/named/named.conf.local — 1 of 2</div><pre class="code-pre "><code langs="">zone "<span class="highlight">nyc3.example.com</span>" {
    type master;
    file "/etc/named/zones/db.<span class="highlight">nyc3.example.com</span>"; # zone file path
};
</code></pre>
<p>Assuming that our private subnet is <em>10.128.0.0/16</em>, add the reverse zone by with the following lines (note that our reverse zone name starts with "128.10" which is the octet reversal of "10.128"):</p>
<div class="code-label " title="/etc/named/named.conf.local — 2 of 2">/etc/named/named.conf.local — 2 of 2</div><pre class="code-pre "><code langs="">zone "<span class="highlight">128.10</span>.in-addr.arpa" {
    type master;
    file "/etc/named/zones/db.<span class="highlight">10.128</span>";  # 10.128.0.0/16 subnet
    };
</code></pre>
<p>If your servers span multiple private subnets but are in the same datacenter, be sure to specify an additional zone and zone file for each distinct subnet. When you are finished adding all of your desired zones, save and exit the <code>named.conf.local</code> file.</p>

<p>Now that our zones are specified in BIND, we need to create the corresponding forward and reverse zone files.</p>

<h3 id="create-forward-zone-file">Create Forward Zone File</h3>

<p>The forward zone file is where we define DNS records for forward DNS lookups. That is, when the DNS receives a name query, "host1.nyc3.example.com" for example, it will look in the forward zone file to resolve <em>host1</em>'s corresponding private IP address.</p>

<p>Let's create the directory where our zone files will reside. According to our <em>named.conf.local</em> configuration, that location should be <code>/etc/named/zones</code>:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo chmod 755 /etc/named
</li><li class="line" prefix="$">sudo mkdir /etc/named/zones
</li></ul></code></pre>
<p>Now let's edit our forward zone file:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo vi /etc/named/zones/db.<span class="highlight">nyc3.example.com</span>
</li></ul></code></pre>
<p>First, you will want to add the SOA record. Replace the highlighted ns1 FQDN with your own FQDN, then replace the second "nyc3.example.com" with your own domain. Every time you edit a zone file, you should increment the <em>serial</em> value before you restart the <code>named</code> process--we will increment it to "3". It should look something like this:</p>
<div class="code-label " title="/etc/named/zones/db.nyc3.example.com — 1 of 3">/etc/named/zones/db.nyc3.example.com — 1 of 3</div><pre class="code-pre "><code langs="">@       IN      SOA     <span class="highlight">ns1.nyc3.example.com</span>. admin.<span class="highlight">nyc3.example.com</span>. (
                              <span class="highlight">3</span>         ; Serial
             604800     ; Refresh
              86400     ; Retry
            2419200     ; Expire
             604800 )   ; Negative Cache TTL
</code></pre>
<p>After that, add your nameserver records with the following lines (replace the names with your own). Note that the second column specifies that these are "NS" records:</p>
<div class="code-label " title="/etc/named/zones/db.nyc3.example.com — 2 of 3">/etc/named/zones/db.nyc3.example.com — 2 of 3</div><pre class="code-pre "><code langs="">; name servers - NS records
    IN      NS      ns1.<span class="highlight">nyc3.example.com</span>.
    IN      NS      ns2.<span class="highlight">nyc3.example.com</span>.
</code></pre>
<p>Then add the A records for your hosts that belong in this zone. This includes any server whose name we want to end with ".nyc3.example.com" (substitute the names and private IP addresses). Using our example names and private IP addresses, we will add A records for <em>ns1</em>, <em>ns2</em>, <em>host1</em>, and <em>host2</em> like so:</p>
<div class="code-label " title="/etc/named/zones/db.nyc3.example.com — 3 of 3">/etc/named/zones/db.nyc3.example.com — 3 of 3</div><pre class="code-pre "><code langs="">; name servers - A records
ns1.<span class="highlight">nyc3.example.com</span>.          IN      A       <span class="highlight">10.128.10.11</span>
ns2.<span class="highlight">nyc3.example.com</span>.          IN      A       <span class="highlight">10.128.20.12</span>

; 10.128.0.0/16 - A records
<span class="highlight">host1.nyc3.example.com</span>.        IN      A      <span class="highlight">10.128.100.101</span>
<span class="highlight">host2.nyc3.example.com</span>.        IN      A      <span class="highlight">10.128.200.102</span>
</code></pre>
<p>Save and exit the <code>db.nyc3.example.com</code> file.</p>

<p>Our final example forward zone file looks like the following:</p>
<div class="code-label " title="/etc/named/zones/db.nyc3.example.com — complete">/etc/named/zones/db.nyc3.example.com — complete</div><pre class="code-pre line_numbers"><code langs=""><ul class="prefixed"><li class="line" prefix="1">$TTL    604800
</li><li class="line" prefix="2">@       IN      SOA     <span class="highlight">ns1.nyc3.example.com</span>. admin.<span class="highlight">nyc3.example.com</span>. (
</li><li class="line" prefix="3">                  <span class="highlight">3</span>       ; Serial
</li><li class="line" prefix="4">             604800     ; Refresh
</li><li class="line" prefix="5">              86400     ; Retry
</li><li class="line" prefix="6">            2419200     ; Expire
</li><li class="line" prefix="7">             604800 )   ; Negative Cache TTL
</li><li class="line" prefix="8">;
</li><li class="line" prefix="9">; name servers - NS records
</li><li class="line" prefix="10">     IN      NS      ns1.<span class="highlight">nyc3.example.com</span>.
</li><li class="line" prefix="11">     IN      NS      ns2.<span class="highlight">nyc3.example.com</span>.
</li><li class="line" prefix="12">
</li><li class="line" prefix="13">; name servers - A records
</li><li class="line" prefix="14">ns1.<span class="highlight">nyc3.example.com</span>.          IN      A       <span class="highlight">10.128.10.11</span>
</li><li class="line" prefix="15">ns2.<span class="highlight">nyc3.example.com</span>.          IN      A       <span class="highlight">10.128.20.12</span>
</li><li class="line" prefix="16">
</li><li class="line" prefix="17">; 10.128.0.0/16 - A records
</li><li class="line" prefix="18"><span class="highlight">host1.nyc3.example.com</span>.        IN      A      <span class="highlight">10.128.100.101</span>
</li><li class="line" prefix="19"><span class="highlight">host2.nyc3.example.com</span>.        IN      A      <span class="highlight">10.128.200.102</span>
</li></ul></code></pre>
<p>Now let's move onto the reverse zone file(s).</p>

<h3 id="create-reverse-zone-file-s">Create Reverse Zone File(s)</h3>

<p>Reverse zone file are where we define DNS PTR records for reverse DNS lookups. That is, when the DNS receives a query by IP address, "10.128.100.101" for example, it will look in the reverse zone file(s) to resolve the corresponding FQDN, "host1.nyc3.example.com" in this case.</p>

<p>On <em>ns1</em>, for each reverse zone specified in the <code>named.conf.local</code> file, create a reverse zone file.</p>

<p>Edit the reverse zone file that corresponds to the reverse zone(s) defined in <code>named.conf.local</code>:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo vi /etc/named/zones/db.<span class="highlight">10.128</span>
</li></ul></code></pre>
<p>In the same manner as the forward zone file, replace the highlighted ns1 FQDN with your own FQDN, then replace the second "nyc3.example.com" with your own domain. Every time you edit a zone file, you should increment the <em>serial</em> value before you restart the <code>named</code> process--we will increment it to "3". It should look something like this:</p>
<div class="code-label " title="/etc/named/zones/db.10.128 — 1 of 3">/etc/named/zones/db.10.128 — 1 of 3</div><pre class="code-pre "><code langs="">@       IN      SOA     <span class="highlight">ns1.nyc3.example.com</span>. admin.<span class="highlight">nyc3.example.com</span>. (
                              <span class="highlight">3</span>         ; Serial
                         604800         ; Refresh
                          86400         ; Retry
                        2419200         ; Expire
                         604800 )       ; Negative Cache TTL
</code></pre>
<p>After that, add your nameserver records with the following lines (replace the names with your own). Note that the second column specifies that these are "NS" records:</p>
<div class="code-label " title="/etc/named/zones/db.10.128 — 2 of 3">/etc/named/zones/db.10.128 — 2 of 3</div><pre class="code-pre "><code langs="">; name servers - NS records
      IN      NS      ns1.<span class="highlight">nyc3.example.com</span>.
      IN      NS      ns2.<span class="highlight">nyc3.example.com</span>.
</code></pre>
<p>Then add <code>PTR</code> records for all of your servers whose IP addresses are on the subnet of the zone file that you are editing. In our example, this includes all of our hosts because they are all on the 10.128.0.0/16 subnet. Note that the first column consists of the last two octets of your servers' private IP addresses in reversed order. Be sure to substitute names and private IP addresses to match your servers:</p>
<div class="code-label " title="/etc/named/zones/db.10.128 — 3 of 3">/etc/named/zones/db.10.128 — 3 of 3</div><pre class="code-pre "><code langs="">; PTR Records
<span class="highlight">11.10</span>   IN      PTR     ns1.<span class="highlight">nyc3.example.com</span>.    ; 10.128.10.11
<span class="highlight">12.20</span>   IN      PTR     ns2.<span class="highlight">nyc3.example.com</span>.    ; 10.128.20.12
<span class="highlight">101.100</span> IN      PTR     <span class="highlight">host1.nyc3.example.com</span>.  ; 10.128.100.101
<span class="highlight">102.200</span> IN      PTR     <span class="highlight">host2.nyc3.example.com</span>.  ; 10.128.200.102
</code></pre>
<p>Save and exit the reverse zone file (repeat this section if you need to add more reverse zone files).</p>

<p>Our final example reverse zone file looks like the following:</p>
<div class="code-label " title="/etc/named/zones/db.10.128 — complete">/etc/named/zones/db.10.128 — complete</div><pre class="code-pre line_numbers"><code langs=""><ul class="prefixed"><li class="line" prefix="1">$TTL    604800
</li><li class="line" prefix="2">@       IN      SOA     <span class="highlight">nyc3.example.com</span>. admin.nyc3.example.com. (
</li><li class="line" prefix="3">                              <span class="highlight">3</span>         ; Serial
</li><li class="line" prefix="4">                         604800         ; Refresh
</li><li class="line" prefix="5">                          86400         ; Retry
</li><li class="line" prefix="6">                        2419200         ; Expire
</li><li class="line" prefix="7">                         604800 )       ; Negative Cache TTL
</li><li class="line" prefix="8">; name servers
</li><li class="line" prefix="9">      IN      NS      ns1.<span class="highlight">nyc3.example.com</span>.
</li><li class="line" prefix="10">      IN      NS      ns2.<span class="highlight">nyc3.example.com</span>.
</li><li class="line" prefix="11">
</li><li class="line" prefix="12">; PTR Records
</li><li class="line" prefix="13"><span class="highlight">11.10</span>   IN      PTR     ns1.<span class="highlight">nyc3.example.com</span>.    ; 10.128.10.11
</li><li class="line" prefix="14"><span class="highlight">12.20</span>   IN      PTR     ns2.<span class="highlight">nyc3.example.com</span>.    ; 10.128.20.12
</li><li class="line" prefix="15"><span class="highlight">101.100</span> IN      PTR     <span class="highlight">host1.nyc3.example.com</span>.  ; 10.128.100.101
</li><li class="line" prefix="16"><span class="highlight">102.200</span> IN      PTR     <span class="highlight">host2.nyc3.example.com</span>.  ; 10.128.200.102
</li></ul></code></pre>
<h3 id="check-bind-configuration-syntax">Check BIND Configuration Syntax</h3>

<p>Run the following command to check the syntax of the <code>named.conf*</code> files:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo named-checkconf
</li></ul></code></pre>
<p>If your named configuration files have no syntax errors, you will return to your shell prompt and see no error messages. If there are problems with your configuration files, review the error message and the <a href="https://indiareads/community/tutorials/how-to-configure-bind-as-an-private-network-dns-server-on-centos-7#ConfigurePrimaryDNSServer">Configure Primary DNS Server</a> section, then try <code>named-checkconf</code> again.</p>

<p>The <code>named-checkzone</code> command can be used to check the correctness of your zone files. Its first argument specifies a zone name, and the second argument specifies the corresponding zone file, which are both defined in <code>named.conf.local</code>.</p>

<p>For example, to check the "<span class="highlight">nyc3.example.com</span>" forward zone configuration, run the following command (change the names to match your forward zone and file):</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo named-checkzone <span class="highlight">nyc3.example.com</span> /etc/named/zones/db.<span class="highlight">nyc3.example.com</span>
</li></ul></code></pre>
<p>And to check the "<span class="highlight">128.10</span>.in-addr.arpa" reverse zone configuration, run the following command (change the numbers to match your reverse zone and file):</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo named-checkzone <span class="highlight">128.10</span>.in-addr.arpa /etc/named/zones/db.<span class="highlight">10.128</span>
</li></ul></code></pre>
<p>When all of your configuration and zone files have no errors in them, you should be ready to restart the BIND service.</p>

<h3 id="start-bind">Start BIND</h3>

<p>Start BIND:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo systemctl start named
</li></ul></code></pre>
<p>Now you will want to enable it, so it will start on boot:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo systemctl enable named
</li></ul></code></pre>
<p>Your primary DNS server is now setup and ready to respond to DNS queries. Let's move on to creating the secondary DNS server.</p>

<h2 id="configure-secondary-dns-server">Configure Secondary DNS Server</h2>

<p>In most environments, it is a good idea to set up a secondary DNS server that will respond to requests if the primary becomes unavailable. Luckily, the secondary DNS server is much easier to configure.</p>

<p>On <em>ns2</em>, edit the <code>named.conf</code> file:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo vi /etc/named.conf
</li></ul></code></pre>
<p><span class="note"><strong>Note:</strong> If you prefer to skip these instructions, you can copy <em>ns1</em>'s <code>named.conf</code> file and modify it to listen on <em>ns2</em>'s private IP address, and not allow transfers.<br /></span></p>

<p>Above the existing <code>options</code> block, create a new ACL block called "trusted". This is where we will define list of clients that we will allow recursive DNS queries from (i.e. your servers that are in the same datacenter as ns1). Using our example private IP addresses, we will add <em>ns1</em>, <em>ns2</em>, <em>host1</em>, and <em>host2</em> to our list of trusted clients:</p>
<div class="code-label " title="/etc/named.conf — 1 of 4">/etc/named.conf — 1 of 4</div><pre class="code-pre "><code langs="">acl "trusted" {
        <span class="highlight">10.128.10.11</span>;    # ns1 - can be set to localhost
        <span class="highlight">10.128.20.12</span>;    # ns2
        <span class="highlight">10.128.100.101</span>;  # host1
        <span class="highlight">10.128.200.102</span>;  # host2
};
</code></pre>
<p>Now that we have our list of trusted DNS clients, we will want to edit the <code>options</code> block. Add the private IP address of ns1 to the <code>listen-on port 53</code> directive, and comment out the <code>listen-on-v6</code> line:</p>
<div class="code-label " title="/etc/named.conf — 2 of 4">/etc/named.conf — 2 of 4</div><pre class="code-pre "><code langs="">options {
        listen-on port 53 { 127.0.0.1; <span class="highlight">10.128.20.12;</span> };
<span class="highlight">#</span>        listen-on-v6 port 53 { ::1; };
...
</code></pre>
<p>Change <code>allow-query</code> directive from "localhost" to "trusted":</p>
<div class="code-label " title="/etc/named.conf — 3 of 4">/etc/named.conf — 3 of 4</div><pre class="code-pre "><code langs="">...
options {
...
        allow-query { <span class="highlight">trusted;</span> }; # allows queries from "trusted" clients
...
</code></pre>
<p>At the end of the file, add the following line:</p>
<div class="code-label " title="/etc/named.conf — 4 of 4">/etc/named.conf — 4 of 4</div><pre class="code-pre "><code langs="">include "/etc/named/named.conf.local";
</code></pre>
<p>Now save and exit <code>named.conf</code>. The above configuration specifies that only your own servers (the "trusted" ones) will be able to query your DNS server.</p>

<p>Next, we will configure the local file, to specify our DNS zones.</p>

<p>Save and exit <code>named.conf</code>.</p>

<p>Now edit the <code>named.conf.local</code> file:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo chmod 755 /etc/named
</li><li class="line" prefix="$">sudo vi /etc/named/named.conf.local
</li></ul></code></pre>
<p>Define slave zones that correspond to the master zones on the primary DNS server. Note that the type is "slave", the file does not contain a path, and there is a <code>masters</code> directive which should be set to the primary DNS server's private IP. If you defined multiple reverse zones in the primary DNS server, make sure to add them all here:</p>
<div class="code-label " title="/etc/named/named.conf.local">/etc/named/named.conf.local</div><pre class="code-pre line_numbers"><code langs=""><ul class="prefixed"><li class="line" prefix="1">zone "<span class="highlight">nyc3.example.com</span>" {
</li><li class="line" prefix="2">    type slave;
</li><li class="line" prefix="3">    file "slaves/db.<span class="highlight">nyc3.example.com</span>";
</li><li class="line" prefix="4">    masters { <span class="highlight">10.128.10.11</span>; };  # ns1 private IP
</li><li class="line" prefix="5">};
</li><li class="line" prefix="6">
</li><li class="line" prefix="7">zone "<span class="highlight">128.10</span>.in-addr.arpa" {
</li><li class="line" prefix="8">    type slave;
</li><li class="line" prefix="9">    file "slaves/db.<span class="highlight">10.128</span>";
</li><li class="line" prefix="10">    masters { <span class="highlight">10.128.10.11</span>; };  # ns1 private IP
</li><li class="line" prefix="11">};
</li></ul></code></pre>
<p>Now save and exit <code>named.conf.local</code>.</p>

<p>Run the following command to check the validity of your configuration files:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo named-checkconf
</li></ul></code></pre>
<p>Once that checks out, start BIND:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo systemctl start named
</li></ul></code></pre>
<p>Enable BIND to start on boot:</p>
<pre class="code-pre "><code langs="">sudo systemctl enable named
</code></pre>
<p>Now you have primary and secondary DNS servers for private network name and IP address resolution. Now you must configure your servers to use your private DNS servers.</p>

<h2 id="configure-dns-clients">Configure DNS Clients</h2>

<p>Before all of your servers in the "trusted" ACL can query your DNS servers, you must configure each of them to use <em>ns1</em> and <em>ns2</em> as nameservers. This process varies depending on OS, but for most Linux distributions it involves adding your name servers to the <code>/etc/resolv.conf</code> file.</p>

<h3 id="centos-clients">CentOS Clients</h3>

<p>On CentOS, RedHat, and Fedora Linux VPS, simply edit the <code>resolv.conf</code> file:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo vi /etc/resolv.conf
</li></ul></code></pre>
<p>Then add the following lines to the TOP of the file (substitute your private domain, and <em>ns1</em> and <em>ns2</em> private IP addresses):</p>
<div class="code-label " title="/etc/resolv.conf">/etc/resolv.conf</div><pre class="code-pre "><code langs="">search <span class="highlight">nyc3.example.com</span>  # your private domain
nameserver <span class="highlight">10.128.10.11</span>  # ns1 private IP address
nameserver <span class="highlight">10.128.20.12</span>  # ns2 private IP address
</code></pre>
<p>Now save and exit. Your client is now configured to use your DNS servers.</p>

<h3 id="ubuntu-clients">Ubuntu Clients</h3>

<p>On Ubuntu and Debian Linux VPS, you can edit the <code>head</code> file, which is prepended to <code>resolv.conf</code> on boot:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo vi /etc/resolvconf/resolv.conf.d/head
</li></ul></code></pre>
<p>Add the following lines to the file (substitute your private domain, and <em>ns1</em> and <em>ns2</em> private IP addresses):</p>
<div class="code-label " title="/etc/resolvconf/resolv.conf.d/head">/etc/resolvconf/resolv.conf.d/head</div><pre class="code-pre "><code langs="">search <span class="highlight">nyc3.example.com</span>  # your private domain
nameserver <span class="highlight">10.128.10.11</span>  # ns1 private IP address
nameserver <span class="highlight">10.128.20.12</span>  # ns2 private IP address
</code></pre>
<p>Now run <code>resolvconf</code> to generate a new <code>resolv.conf</code> file:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo resolvconf -u
</li></ul></code></pre>
<p>Your client is now configured to use your DNS servers.</p>

<h2 id="test-clients">Test Clients</h2>

<p>Use <code>nslookup</code>—included in the "bind-utils" package—to test if your clients can query your name servers. You should be able to do this on all of the clients that you have configured and are in the "trusted" ACL.</p>

<h3 id="forward-lookup">Forward Lookup</h3>

<p>For example, we can perform a forward lookup to retrieve the IP address of <em>host1.nyc3.example.com</em> by running the following command:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">nslookup host1
</li></ul></code></pre>
<p>Querying "host1" expands to "host1.nyc3.example.com because of the <code>search</code> option is set to your private subdomain, and DNS queries will attempt to look on that subdomain before looking for the host elsewhere. The output of the command above would look like the following:</p>
<pre class="code-pre "><code langs=""><div class="secondary-code-label " title="Output:">Output:</div>Server:     10.128.10.11
Address:    10.128.10.11#53

Name:   host1.nyc3.example.com
Address: 10.128.100.101
</code></pre>
<h3 id="reverse-lookup">Reverse Lookup</h3>

<p>To test the reverse lookup, query the DNS server with <em>host1</em>'s private IP address:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">nslookup 10.128.100.101
</li></ul></code></pre>
<p>You should see output that looks like the following:</p>
<pre class="code-pre "><code langs=""><div class="secondary-code-label " title="Output:">Output:</div>Server:     10.128.10.11
Address:    10.128.10.11#53

11.10.128.10.in-addr.arpa   name = host1.nyc3.example.com.
</code></pre>
<p>If all of the names and IP addresses resolve to the correct values, that means that your zone files are configured properly. If you receive unexpected values, be sure to review the zone files on your primary DNS server (e.g. <code>db.nyc3.example.com</code> and <code>db.10.128</code>).</p>

<p>Congratulations! Your internal DNS servers are now set up properly! Now we will cover maintaining your zone records.</p>

<h2 id="maintaining-dns-records">Maintaining DNS Records</h2>

<p>Now that you have a working internal DNS, you need to maintain your DNS records so they accurately reflect your server environment.</p>

<h3 id="adding-host-to-dns">Adding Host to DNS</h3>

<p>Whenever you add a host to your environment (in the same datacenter), you will want to add it to DNS. Here is a list of steps that you need to take:</p>

<h4 id="primary-nameserver">Primary Nameserver</h4>

<ul>
<li>Forward zone file: Add an "A" record for the new host, increment the value of "Serial"</li>
<li>Reverse zone file: Add a "PTR" record for the new host, increment the value of "Serial"</li>
<li>Add your new host's private IP address to the "trusted" ACL (<code>named.conf.options</code>)</li>
</ul>

<p>Then reload BIND:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo systemctl reload named
</li></ul></code></pre>
<h4 id="secondary-nameserver">Secondary Nameserver</h4>

<ul>
<li>Add your new host's private IP address to the "trusted" ACL (<code>named.conf.options</code>)</li>
</ul>

<p>Then reload BIND:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo systemctl reload named
</li></ul></code></pre>
<h4 id="configure-new-host-to-use-your-dns">Configure New Host to Use Your DNS</h4>

<ul>
<li>Configure resolv.conf to use your DNS servers</li>
<li>Test using <code>nslookup</code></li>
</ul>

<h3 id="removing-host-from-dns">Removing Host from DNS</h3>

<p>If you remove a host from your environment or want to just take it out of DNS, just remove all the things that were added when you added the server to DNS (i.e. the reverse of the steps above).</p>

<h2 id="conclusion">Conclusion</h2>

<p>Now you may refer to your servers' private network interfaces by name, rather than by IP address. This makes configuration of services and applications easier because you no longer have to remember the private IP addresses, and the files will be easier to read and understand. Also, now you can change your configurations to point to a new servers in a single place, your primary DNS server, instead of having to edit a variety of distributed configuration files, which eases maintenance.</p>

<p>Once you have your internal DNS set up, and your configuration files are using private FQDNs to specify network connections, it is <strong>critical</strong> that your DNS servers are properly maintained. If they both become unavailable, your services and applications that rely on them will cease to function properly. This is why it is recommended to set up your DNS with at least one secondary server, and to maintain working backups of all of them.</p>

    