<link rel='stylesheet' href='digital_ocean.css'><br> <br> 
      <h3 id="introduction">Introduction</h3>

<hr />

<p>DNS, or the Domain Name System, is often a difficult component to get right when learning how to configure websites and servers.  While most people will probably choose to use the DNS servers provided by their hosting company or their domain registrar, there are some advantages to creating your own DNS servers.</p>

<p>In this guide, we will discuss how to install and configure the Bind9 DNS server as authoritative-only DNS servers on Ubuntu 14.04 machines.  We will set these up two Bind servers for our domain in a master-slave configuration.</p>

<h2 id="prerequisites-and-goals">Prerequisites and Goals</h2>

<p>To complete this guide, you will first need to be familiar with some common DNS terminology.  Check out <a href="https://indiareads/community/tutorials/an-introduction-to-dns-terminology-components-and-concepts">this guide</a> to learn about the concepts we will be implementing in this guide.</p>

<p>You will also need at least two servers.  One will be for the "master" DNS server where the zone files for our domain will originate and one will be the "slave" server which will receive the zone data through transfers and be available in the event that the other server goes down.  This avoids the peril of having a single point of failure for your DNS servers.</p>

<p>Unlike <a href="">caching or forwarding DNS servers</a> or a multi-purpose DNS server, authoritative-only servers only respond to iterative queries for the zones that they are authoritative for.  This means that if the server does not know the answer, it will just tell the client (usually some kind of resolving DNS server) that it does not know the answer and give a reference to a server that may know more.</p>

<p>Authoritative-only DNS servers are often a good configuration for high performance because they do not have the overhead of resolving recursive queries from clients.  They only care about the zones that they are designed to serve.</p>

<p>For the purposes of this guide, we will actually be referencing <strong>three</strong> servers.  The two name servers mentioned above, plus a web server that we want to configure as a host within our zone.</p>

<p>We will use the dummy domain <code><span class="highlight">example.com</span></code> for this guide.  You should replace it with the domain that you are configuring.  These are the details of the machines we will be configuring:</p>

<table class="pure-table"><thead>
<tr>
<th>Purpose</th>
<th>DNS FQDN</th>
<th>IP Address</th>
</tr>
</thead><tbody>
<tr>
<td>Master name server</td>
<td>ns1.example.com.</td>
<td>192.0.2.1</td>
</tr>
<tr>
<td>Slave name server</td>
<td>ns2.example.com.</td>
<td>192.0.2.2</td>
</tr>
<tr>
<td>Web Server</td>
<td>www.example.com.</td>
<td>192.0.2.3</td>
</tr>
</tbody></table>

<p>After completing this guide, you should have two authoritative-only name servers configured for your domain zones.  The names in the center column in the table above will be able to be used to reach your various hosts.  Using this configuration, a recursive DNS server will be able to return data about the domain to clients.</p>

<h2 id="setting-the-hostname-on-the-name-servers">Setting the Hostname on the Name Servers</h2>

<p>Before we get into the configuration of our name servers, we must ensure that our hostname is configured properly on both our master and slave DNS server.</p>

<p>Begin by investigating the <code>/etc/hosts</code> file.  Open the file with sudo privileges in your text editor:</p>
<pre class="code-pre "><code langs="">sudo nano /etc/hosts
</code></pre>
<p>We need to configure this so that it correctly identifies each server's hostname and FQDN.  For the master name server, the file will look something like this initially:</p>
<pre class="code-pre "><code langs="">127.0.0.1       localhost
127.0.1.1       ns1 ns1
. . .
</code></pre>
<p>We should modify the second line to reference our specific host and domain combination and point this to our public, static IP address.  We can then add the unqualified name as an alias at the end.  For the master server in this example, you would change the second line to this:</p>
<pre class="code-pre "><code langs="">127.0.0.1       localhost
<span class="highlight">192.0.2.1       ns1.example.com ns1</span>
. . .
</code></pre>
<p>Save and close the file when you are finished.</p>

<p>We should also modify the <code>/etc/hostname</code> file to contain our unqualified hostname:</p>
<pre class="code-pre "><code langs="">sudo nano /etc/hostname
</code></pre><pre class="code-pre "><code langs=""><span class="highlight">ns1</span>
</code></pre>
<p>We can read this value into the currently running system then by typing:</p>
<pre class="code-pre "><code langs="">sudo hostname -F /etc/hostname
</code></pre>
<p>We want to complete the same procedure on our slave server.</p>

<p>Start with the <code>/etc/hosts</code> file:</p>
<pre class="code-pre "><code langs="">sudo nano /etc/hosts
</code></pre><pre class="code-pre "><code langs="">127.0.0.1       localhost
<span class="highlight">192.0.2.2       ns2.example.com ns2</span>
</code></pre>
<p>Save and close the file when you are finished.</p>

<p>Then, modify the <code>/etc/hostname</code> file.  Remember to only use the actual host (just <code>ns2</code> in our example) for this file:</p>
<pre class="code-pre "><code langs="">sudo nano /etc/hostname
</code></pre><pre class="code-pre "><code langs=""><span class="highlight">ns2</span>
</code></pre>
<p>Again, read the file to modify the current system:</p>
<pre class="code-pre "><code langs="">sudo hostname -F /etc/hostname
</code></pre>
<p>Your servers should now have their host definitions set correctly.</p>

<h2 id="install-bind-on-both-name-servers">Install Bind on Both Name Servers</h2>

<p>On each of your name servers, you can now install Bind, the DNS server that we will be using.</p>

<p>The Bind software is available within Ubuntu's default repositories, so we just need to update our local package index and install the software using <code>apt</code>.  We will also include the documentation and some common utilities:</p>
<pre class="code-pre "><code langs="">sudo apt-get update
sudo apt-get install bind9 bind9utils bind9-doc
</code></pre>
<p>Run this installation command on your master and slave DNS servers to acquire the appropriate files.</p>

<h2 id="configure-the-master-bind-server">Configure the Master Bind Server</h2>

<p>Now that we have the software installed, we can begin by configuring our DNS server on the master server.</p>

<h3 id="configuring-the-options-file">Configuring the Options File</h3>

<p>The first thing that we will configure to get started is the <code>named.conf.options</code> file.</p>

<p>The Bind DNS server is also known as <code>named</code>.  The main configuration file is located at <code>/etc/bind/named.conf</code>.  This file calls on the other files that we will be actually configuring.</p>

<p>Open the options file with sudo privileges in your editor:</p>
<pre class="code-pre "><code langs="">sudo nano /etc/bind/named.conf.options
</code></pre>
<p>Below, most of the commented lines have been stripped out for brevity, but in general the file should look like this after installation:</p>
<pre class="code-pre "><code langs="">options {
        directory "/var/cache/bind";

        dnssec-validation auto;

        auth-nxdomain no;    # conform to RFC1035
        listen-on-v6 { any; };
};
</code></pre>
<p>The main thing that we need to configure in this file is recursion.  Since we are trying to set up an authoritative-only server, we do not want to enable recursion on this server.  We can turn this off within the <code>options</code> block.</p>

<p>We are also going to default to not allowing transfers.  We will override this in individual zone specifications later:</p>
<pre class="code-pre "><code langs="">options {
        directory "/var/cache/bind";
        <span class="highlight">recursion no;</span>
        <span class="highlight">allow-transfer { none; };</span>

        dnssec-validation auto;

        auth-nxdomain no;    # conform to RFC1035
        listen-on-v6 { any; };
};
</code></pre>
<p>When you are finished, save and close the file.</p>

<h3 id="configuring-the-local-file">Configuring the Local File</h3>

<p>The next step that we need to take is to specify the zones that we wish to control this server.  A zone is any portion of the domain that is delegated for management to a name server that has not been sub-delegated to other servers.</p>

<p>We are configuring the <code>example.com</code> domain and we are not going to be sub-delegating responsibility for any portion of the domain to other servers.  So the zone will cover our entire domain.</p>

<p>To configure our zones, we need to open the <code>/etc/bind/named.conf.local</code> file with sudo privileges:</p>
<pre class="code-pre "><code langs="">sudo nano /etc/bind/named.conf.local
</code></pre>
<p>This file will initially be empty besides comments.  There are other zones that our server knows about for general management, but these are specified in the <code>named.conf.default-zones</code> file.</p>

<p>To start off, we need to configure the forward zone for our <code>example.com</code> domain.  Forward zone are the conventional name-to-IP resolution that most of us think of when we discuss DNS.  We create a configuration block that defines the domain zone we wish to configure:</p>
<pre class="code-pre "><code langs="">zone "<span class="highlight">example.com</span>" {
};
</code></pre>
<p>Inside of this block, we add the management information about this zone.  We specify relationship of this DNS server to the zone. This is "master" in this case since we are configuring this machine as the master name server for all of our zones.  We also point Bind to the file that holds the actual resource records that define the zone.</p>

<p>We are going to keep our master zone files in a subdirectory called <code>zones</code> within the Bind configuration directory.  We will call our file <code>db.example.com</code> to borrow convention from the other zone files in the Bind directory.  Our block will look like this now:</p>
<pre class="code-pre "><code langs="">zone "example.com" {
    type master;
    file "/etc/bind/zones/db.<span class="highlight">example.com</span>";
};
</code></pre>
<p>We want to allow this zone to be transferred to our slave server, we need to add a line like this:</p>
<pre class="code-pre "><code langs="">zone "example.com" {
    type master;
    file "/etc/bind/zones/db.example.com";
    <span class="highlight">allow-transfer { 192.0.2.2; };</span>
};
</code></pre>
<p>Next, we are going to define the reverse zone for our domain. </p>

<h4 id="a-bit-about-reverse-zones">A Bit About Reverse Zones</h4>

<p>If the organization that gave you your IP addresses did not give you a network range and delegate responsibility for that range to you, then your reverse zone file will not be referenced and will be handled by the organization itself.</p>

<p>With hosting providers, the reverse mapping is usually taken care of by the company itself.  For instance, with IndiaReads, reverse mappings for your servers will be automatically created if use the machine's FQDN as the server name in the control panel.  For instance, the reverse mappings for this tutorial could be created by naming the servers like this:</p>

<p><img src="https://assets.digitalocean.com/articles/bind_auth/auto_reverse.png" alt="IndiaReads auto reverse DNS mapping" /></p>

<p>In instances like these, since you have not been allocated a chunk of addresses to administer, you should use this strategy.  The strategy outlined below is covered for completeness and to make it applicable if you have been delegated control over larger groups of contiguous addresses.</p>

<p>Reverse zones are used to connect an IP address back to a domain name.  However, the domain name system was designed for the forward mappings originally, so some thought is needed to adapt this to allow for reverse mappings.</p>

<p>The pieces of information that you need to keep in mind to understand reverse mappings are:</p>

<ul>
<li>In a domain, the most specific portion is of the address is on the left.  For an IP address, the most specific portion is on the right.</li>
<li>The most specific part of a domain specification is either a subdomain or a host name.  This is defined in the zone file for the domain.</li>
<li>Each subdomain can, in turn, define more subdomains or hosts.</li>
</ul>

<p>All reverse zone mappings are defined under the special domain <code>in-addr.arpa</code>, which is controlled by the Internet Assigned Numbers Authority (IANA).  Under this domain, a tree exists that uses subdomains to map out each of the octets in an IP address.  To make sure that the specificity of the IP addresses mirrors that of normal domains, the octets of the IP addresses are actually reversed.</p>

<p>So our master DNS server, with an IP address of <code>192.0.2.1</code>, would be flipped to read as <code>1.2.0.192</code>.  When we add this host specification as a hierarchy existing under the <code>in-addr.arpa</code> domain, the specific host can be referenced as <code>1.2.0.192.in-addr.arpa</code>.</p>

<p>Since we define individual hosts (like the leading "1" here) within the zone file itself when using DNS, the zone we would be configuring would be <code>2.0.192.in-addr.arpa</code>.  If our network provider has given us a /24 block of addresses, say <code>192.0.2.0/24</code>, they would have delegated this <code>in-addr.arpa</code> portion to us.</p>

<p>Now that you know how to specify the reverse zone name, the actual definition is exactly the same as the forward zone.  Below the <code>example.com</code> zone definition, make a reverse zone for the network you have been given.  Again, this is probably only necessary if you were delegated control over a block of addresses:</p>
<pre class="code-pre "><code langs="">zone "2.0.192.in-addr.arpa" {
    type master;
    file "/etc/bind/zones/db.192.0.2";
};
</code></pre>
<p>We have chosen to name the file <code>db.192.0.2</code>.  This is specific about what the zone configures and is more readable than the reverse notation.</p>

<p>Save and close the file when you are finished.</p>

<h3 id="create-the-forward-zone-file">Create the Forward Zone File</h3>

<p>We have told Bind about our forward and reverse zones now, but we have not yet created the files that will define these zones.</p>

<p>If you recall, we specified the file locations as being within a subdirectory called <code>zones</code>.  We need to create this directory:</p>
<pre class="code-pre "><code langs="">sudo mkdir /etc/bind/zones
</code></pre>
<p>Now, we can use some of the pre-existing zone files in the Bind directory as templates for the zone files we want to create.  For the forward zone, the <code>db.local</code> file will be close to what we need.  Copy that file into the <code>zones</code> subdirectory with the name used in the <code>named.conf.local</code> file.</p>
<pre class="code-pre "><code langs="">sudo cp /etc/bind/db.local /etc/bind/zones/db.<span class="highlight">example.com</span>
</code></pre>
<p>While we are doing this, we can copy a template for the reverse zone as well.  We will use the <code>db.127</code> file, since it's a close match for what we need:</p>
<pre class="code-pre "><code langs="">sudo cp /etc/bind/db.127 /etc/bind/zones/db.<span class="highlight">192.0.2</span>
</code></pre>
<p>Now, open the forward zone file with sudo privileges in your text editor:</p>
<pre class="code-pre "><code langs="">sudo nano /etc/bind/zones/db.<span class="highlight">example.com</span>
</code></pre>
<p>The file will look like this:</p>
<pre class="code-pre "><code langs="">$TTL    604800
@       IN      SOA     localhost. root.localhost. (
                              2         ; Serial
                         604800         ; Refresh
                          86400         ; Retry
                        2419200         ; Expire
                         604800 )       ; Negative Cache TTL
;
@       IN      NS      localhost.
@       IN      A       127.0.0.1
@       IN      AAAA    ::1
</code></pre>
<p>The first thing we need want to do is modify the <code>SOA</code> (start of authority) record that starts with the first <code>@</code> symbol and continues until the closing parenthesis.</p>

<p>We need to replace the <code>localhost.</code> with the name of the FQDN of this machine.  This portion of the record is used to define any name server that will respond authoritatively for the zone being defined.  This will be the machine we are configuring now, <code>ns1.example.com.</code> in our case (notice the trailing dot.  This is important for our entry to register correctly!).  </p>

<p>We also want to change the next piece, which is actually a specially formatted email address with the <code>@</code> replaced by a dot.  We want our emails to go to an administer of the domain, so the traditional email is <code>admin@<span class="highlight">example.com</span></code>.  We would translate this so it looks like <code>admin.<span class="highlight">example.com</span>.</code>:</p>
<pre class="code-pre "><code langs="">@       IN      SOA     ns1.example.com. admin.example.com. (
</code></pre>
<p>The next piece we need to edit is the serial number.  The value of the serial number is how Bind tells if it needs to send updated information to the slave server.</p>

<p><strong>Note</strong>:  Failing to increment the serial number is one of the most common mistakes that leads to issues with zone updates.  Each time you make an edit, you <em>must</em> bump the serial number.</p>

<p>One common practice is to use a convention for incrementing the number.  One approach is to use the date in YYYYMMDD format along with a revision number for the day added onto the end.  So the first revision made on June 05, 2014 could have a serial number of 2014060501 and an update made later that day could have a serial number of 2014060502.  The value can be a 10 digit number.</p>

<p>It is worth adopting a convention for ease of use, but to keep things simple for our demonstration, we will just set ours to <code>5</code> for now:</p>
<pre class="code-pre "><code langs="">@       IN      SOA     ns1.example.com. admin.example.com. (
                              <span class="highlight">5</span>         ; Serial
</code></pre>
<p>Next, we can get rid of the last three lines in the file (the ones at the bottom that start with <code>@</code>) as we will be making our own.</p>

<p>The first thing we want to establish after the SOA record are the name servers for our zone.  We specify the domain and then our two name servers that are authoritative for the zone, by name.  Since these name servers will be hosts within the domain itself, it will look a bit self-referential. </p>

<p>For our guide, it will look like this.  Again, pay attention to the ending dots!:</p>
<pre class="code-pre "><code langs="">; Name servers
<span class="highlight">example.com</span>.    IN      NS      ns1.<span class="highlight">example.com</span>.
<span class="highlight">example.com</span>.    IN      NS      ns2.<span class="highlight">example.com</span>.
</code></pre>
<p>Since the purpose of a zone file is mainly to map host names and services to specific addresses, we are not done yet.  Any software reading this zone file is going to want to know where the <code>ns1</code> and <code>ns2</code> servers are in order to access the authoritative zones.</p>

<p>So next, we need to create the <code>A</code> records that will associate these name server names to the actual IP addresses of our name servers:</p>
<pre class="code-pre "><code langs="">; A records for name servers
ns1             IN      A       192.0.2.1
ns2             IN      A       192.0.2.2
</code></pre>
<p>Now that we have the A records to successfully resolve our name servers to their correct IP addresses, we can add any additional records.  Remember, we have a web server on one of our hosts that we want to use to serve our site.  We will point requests for the general domain (<code>example.com</code> in our case) to this host, as well as requests for the <code>www</code> host.  It will look like this:</p>
<pre class="code-pre "><code langs="">; Other A records
@               IN      A       192.0.2.3
www             IN      A       192.0.2.3
</code></pre>
<p>You can add any additional hosts that you need to define by creating additional <code>A</code> records.  Reference our <a href="https://indiareads/community/tutorials/an-introduction-to-dns-terminology-components-and-concepts">DNS basics guide</a> to get familiar with some of your options with creating additional records.</p>

<p>When you are finished, your file should look something like this:</p>
<pre class="code-pre "><code langs="">$TTL    604800
@       IN      SOA     ns1.<span class="highlight">example.com</span>. admin.<span class="highlight">example.com</span>. (
                              <span class="highlight">5</span>         ; Serial
                         604800         ; Refresh
                          86400         ; Retry
                        2419200         ; Expire
                         604800 )       ; Negative Cache TTL
;

; Name servers
<span class="highlight">example.com</span>.    IN      NS      ns1.<span class="highlight">example.com</span>.
<span class="highlight">example.com</span>.    IN      NS      ns2.<span class="highlight">example.com</span>.

; A records for name servers
ns1             IN      A       192.0.2.1
ns2             IN      A       192.0.2.2

; Other A records
@               IN      A       192.0.2.3
www             IN      A       192.0.2.3
</code></pre>
<p>Save and close the file when you are finished.</p>

<h3 id="create-the-reverse-zone-file">Create the Reverse Zone File</h3>

<p>Now, we have the forward zone configured, but we need to set up the reverse zone file that we specified in our configuration file.  We already created the file at the beginning of the last section.</p>

<p>Open the file in your text editor with sudo privileges:</p>
<pre class="code-pre "><code langs="">sudo nano db.<span class="highlight">192.0.2</span>
</code></pre>
<p>The file should look like this:</p>
<pre class="code-pre "><code langs="">$TTL    604800
@       IN      SOA     localhost. root.localhost. (
                              1         ; Serial
                         604800         ; Refresh
                          86400         ; Retry
                        2419200         ; Expire
                         604800 )       ; Negative Cache TTL
;
@       IN      NS      localhost.
1.0.0   IN      PTR     localhost.
</code></pre>
<p>We will go through much of the same procedure as we did with the forward zone.  First, adjust the domain name, the admin email, and the serial number to match exactly what you had in the last file (The serial number can be different, but should be incremented):</p>
<pre class="code-pre "><code langs="">@       IN      SOA     <span class="highlight">example.com</span>. admin.<span class="highlight">example.com</span>. (
                              <span class="highlight">5</span>         ; Serial
</code></pre>
<p>Again, wipe out the lines under the closing parenthesis of the <code>SOA</code> record.  We will be taking the last octet of each IP address in our network range and mapping it back to that host's FQDN using a <code>PTR</code> record.  Each IP address should only have a single <code>PTR</code> record to avoid problems in some software, so you must choose the host name you wish to reverse map to.</p>

<p>For instance, if you have a mail server set up, you probably want to set up the reverse mapping to the mail name, since many systems use the reverse mapping to validate addresses.</p>

<p>First, we need to set our name servers again:</p>
<pre class="code-pre "><code langs="">; Name servers
        IN      NS      ns1.<span class="highlight">example.com</span>.
        IN      NS      ns2.<span class="highlight">example.com</span>.
</code></pre>
<p>Next, you will use the last octet of the IP address you are referring to and point that back to the fully qualified domain name you want to return with.  For our example, we will use this:</p>
<pre class="code-pre "><code langs="">; PTR Records
1       IN      PTR      ns1.<span class="highlight">example.com</span>.
2       IN      PTR      ns2.<span class="highlight">example.com</span>.
3       IN      PTR      www.<span class="highlight">example.com</span>.
</code></pre>
<p>When you are finished, the file should look something like this:</p>
<pre class="code-pre "><code langs="">$TTL    604800
@       IN      SOA     <span class="highlight">example.com</span>. admin.<span class="highlight">example.com</span>. (
                              <span class="highlight">5</span>         ; Serial
                         604800         ; Refresh
                          86400         ; Retry
                        2419200         ; Expire
                         604800 )       ; Negative Cache TTL
;

; Name servers
        IN      NS      ns1.example.com.
        IN      NS      ns2.example.com.

; PTR records
1       IN      PTR      ns1.<span class="highlight">example.com</span>.
2       IN      PTR      ns2.<span class="highlight">example.com</span>.
3       IN      PTR      www.<span class="highlight">example.com</span>.
</code></pre>
<p>Save and close the file when you are finished.</p>

<h3 id="testing-the-files-and-restarting-the-service">Testing the Files and Restarting the Service</h3>

<p>The configuration for the master server is now complete, but we still need to implement our changes.</p>

<p>Before we restart our service, we should test all of our configuration files to make sure that they're configured correctly.  We have some tools that can check the syntax of each of our files.</p>

<p>First, we can check the <code>named.conf.local</code> and <code>named.conf.options</code> files by using the <code>named-checkconf</code> command.  Since both of these files are source by the skeleton <code>named.conf</code> file, it will test the syntax of the files we modified.</p>
<pre class="code-pre "><code langs="">sudo named-checkconf
</code></pre>
<p>If this returns without any messages, it means that the <code>named.conf.local</code> and <code>named.conf.options</code> files are syntactically valid.</p>

<p>Next, you can check your individual zone files by passing the domain that the zone handles and the zone file location to the <code>named-checkzone</code> command.  So for our guide, you could check the forward zone file by typing:</p>
<pre class="code-pre "><code langs="">sudo named-checkzone <span class="highlight">example.com</span> /etc/bind/zones/db.<span class="highlight">example.com</span>
</code></pre>
<p>If your file has no problems, it should tell you that it loaded the correct serial number and give the "OK" message;</p>
<pre class="code-pre "><code langs="">zone example.com/IN: loaded serial 5
OK
</code></pre>
<p>If you run into any other messages, it means that you have a problem with your zone file.  Usually, the message is quite descriptive about what portion is invalid.</p>

<p>You can check the reverse zone by passing the <code>in-addr.arpa</code> address and the file name.  For our demonstration, we would be type this:</p>
<pre class="code-pre "><code langs="">sudo named-checkzone <span class="highlight">2.0.192</span>.in-addr.arpa /etc/bind/zones/db.<span class="highlight">192.0.2</span>
</code></pre>
<p>Again, this should give you a similar message about loading the correct serial number:</p>
<pre class="code-pre "><code langs="">zone 2.0.192.in-addr.arpa/IN: loaded serial 5
OK
</code></pre>
<p>If all of your files are checking out, you can restart your Bind service:</p>
<pre class="code-pre "><code langs="">sudo service bind9 restart
</code></pre>
<p>You should check the logs by typing:</p>
<pre class="code-pre "><code langs="">sudo tail -f /var/log/syslog
</code></pre>
<p>Keep an eye on this log to make sure that there are no errors.</p>

<h2 id="configure-the-slave-bind-server">Configure the Slave Bind Server</h2>

<p>Now that we have the master server configured, we can go ahead and get the slave server set up.  This will be significantly easier than the master server.</p>

<h3 id="configuring-the-options-file">Configuring the Options File</h3>

<p>Again, we will start with the <code>named.conf.options</code> file.  Open it with sudo privileges in your text editor:</p>
<pre class="code-pre "><code langs="">sudo nano /etc/bind/named.conf.options
</code></pre>
<p>We will make the same exact modifications to this file that we made to our master server's file.</p>
<pre class="code-pre "><code langs="">options {
        directory "/var/cache/bind";
        <span class="highlight">recursion no;</span>
        <span class="highlight">allow-transfer { none; };</span>

        dnssec-validation auto;

        auth-nxdomain no;    # conform to RFC1035
        listen-on-v6 { any; };
};
</code></pre>
<p>Save and close the file when you are finished.</p>

<h3 id="configuring-the-local-configuration-file">Configuring the Local Configuration File</h3>

<p>Next, we will configure the <code>named.conf.local</code> file on the slave server.  Open it with sudo privileges in your text editor:</p>
<pre class="code-pre "><code langs="">sudo nano /etc/bind/named.conf.local
</code></pre>
<p>Here, we will create each of our zone specifications like we did on our master server.  However, the values and some of the parameters will be different.</p>

<p>First, we will work on the forward zone.  Start it off the same way that you did in the master file:</p>
<pre class="code-pre "><code langs="">zone "example.com" {
};
</code></pre>
<p>This time, we are going to set the <code>type</code> to <code>slave</code> since this server is acting as a slave for this zone.  This simply means that it receives its zone files through transfer rather than a file on the local system.  Additionally, we are just going to specify the relative filename instead of the absolute path to the zone file.</p>

<p>The reason for this is that, for slave zones, Bind stores the files <code>/var/cache/bind</code>.  Bind is already configured to look in this directory location, so we do not need to specify the path.</p>

<p>For our forward zone, these details will look like this:</p>
<pre class="code-pre "><code langs="">zone "example.com" {
    type <span class="highlight">slave</span>;
    file "db.<span class="highlight">example.com</span>";
};
</code></pre>
<p>Finally, instead of the <code>allow-transfer</code> directive, we will specify the master servers, by IP address, that this server will accept zone transfers from.  This is done in a directive called <code>masters</code>:</p>
<pre class="code-pre "><code langs="">zone "example.com" {
    type <span class="highlight">slave</span>;
    file "db.<span class="highlight">example.com</span>";
    masters { <span class="highlight">192.0.2.1</span>; };
};
</code></pre>
<p>This completes our forward zone specification.  We can use this same exact format to take care of our reverse zone specification:</p>
<pre class="code-pre "><code langs="">zone "2.0.192.in-addr.arpa" {
    type <span class="highlight">slave</span>;
    file "db.<span class="highlight">192.0.2</span>";
    masters { <span class="highlight">192.0.2.1</span>; };
};
</code></pre>
<p>When you are finished, you can save and close the file.</p>

<h3 id="testing-the-files-and-restarting-the-service">Testing the Files and Restarting the Service</h3>

<p>We do not actually have to do any of the actual zone file creation on the slave machine because, like we mentioned before, this server will receive the zone files from the master server.  So we are ready to test.</p>

<p>Again, we should check the configuration file syntax.  Since we don't have any zone files to check, we only need to use the <code>named-checkconf</code> tool:</p>
<pre class="code-pre "><code langs="">sudo named-checkconf
</code></pre>
<p>If this returns without any errors, it means that the files you modified have no syntax errors.</p>

<p>If this is the case, you can restart your Bind service:</p>
<pre class="code-pre "><code langs="">sudo service bind9 restart
</code></pre>
<p>Check the logs on both the master and slave server using:</p>
<pre class="code-pre "><code langs="">sudo tail -f /var/log/syslog
</code></pre>
<p>You should see some entries that indicate that the zone files have been transferred correctly.</p>

<h2 id="delegate-authority-to-your-name-servers">Delegate Authority to your Name Servers</h2>

<p>Your authoritative-only name servers should now be completely configured.  However, you still need to delegate authority for your domain to your name servers.</p>

<p>To do this, you will have to go to the website where you purchased your domain name.  The interface and perhaps the terminology will be different depending on the domain name registrar that you used.</p>

<p>In your domain settings, look for an option that will allow you to specify the name servers you wish to use.  Since our name servers are <em>within</em> our domain, this is a special case.</p>

<p>Instead of the registrar simply delegating authority for the zone through the use of NS records, it will need to create a <strong>glue record</strong>.  A glue record is an A record that specifies the IP addresses for the name servers after it specifies the name servers that it is delegating authority to.</p>

<p>Usually, the delegation only lists the name servers that will handle the authority of the domain, but when the name servers are within the domain itself, an A record is needed for the name servers in the parent zone.  If this didn't happen, DNS resolvers would get stuck in a loop because it would never be able to find the IP address of the domain's name servers to follow the delegation path.</p>

<p>So you need to find a section of your domain registrar's control panel that allows you to specify name servers <em>and</em> their IP addresses.</p>

<p>As a demonstration, the registrar <a href="https://www.namecheap.com">Namecheap</a> has two different name server sections.</p>

<p>There is a section called "Nameserver Registration" that allows you to specify the IP addresses for name servers within your domain:</p>

<p><img src="https://assets.digitalocean.com/articles/bind_auth/register.png" alt="NameCheap register name servers" /></p>

<p>Inside, you will be able input the IP addresses of the name servers that exist within the domain:</p>

<p><img src="https://assets.digitalocean.com/articles/bind_auth/give_ips.png" alt="NameCheap internal name server" /></p>

<p>This will create the A record that that serve as the glue records that you need in the parent zone file.</p>

<p>After you've done this, you should be able to change the active name servers to your domain's servers.  In NameCheap, this is done using the "Domain Name Server Setup" menu option:</p>

<p><img src="https://assets.digitalocean.com/articles/bind_auth/server_setup.png" alt="NameCheap domain name setup" /></p>

<p>Here, you can tell it to use the name servers you added as the authoritative servers for your site:</p>

<p><img src="https://assets.digitalocean.com/articles/bind_auth/use_servers.png" alt="NameCheap use name servers" /></p>

<p>The changes might take awhile to propagate, but you should see the data from your name servers being used within the next 24-48 hours for most registrars.</p>

<h2 id="conclusion">Conclusion</h2>

<p>You should now have two authoritative-only DNS servers configured to server your domains.  These can be used to store zone information for additional domains as you acquire more.</p>

<p>Configuring and managing your own DNS servers gives you the most control over how the DNS records are handled.  You can make changes and be sure that all relevant pieces of DNS data are up-to-date at the source.  While other DNS solutions may make this process easier, it is important to know that you have options and to understand what is happening in more packaged solutions.</p>

    