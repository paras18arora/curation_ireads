<link rel='stylesheet' href='digital_ocean.css'><br> <br> 
      <h3 id="introduction">Introduction</h3>

<p>Setting up a DNS server to be responsible for domain names can be a complex task even for seasoned administrators.  DNS zone management is a vital duty, but can be bewildering, especially when attempting to get started.</p>

<p>Software like the <strong>Bind</strong> DNS server is incredibly flexible and can be configured to operate as many of the components in the overall DNS hierarchy.  However, that flexibility also means that Bind is not optimized for any one task.  This has a few side effects.</p>

<p>Most of the time there are huge chunks of functionality that your configuration has no need for.  This additional complexity makes management more difficult.  It also means that the software itself will be less responsive for any one task.</p>

<p>To solve this problem, alternative DNS servers have been created that specialize in a single area of DNS resolution.  A piece of software known as <strong>NSD</strong> is an authoritative-only DNS server that is ideal for managing DNS zones authoritatively.  Without the need to ever worry about recursion or caching, this server operates with high performance and a lower footprint.</p>

<p>In this guide, we will demonstrate how to install and configure NSD to securely administer our DNS zones on Ubuntu 14.04 servers.</p>

<h2 id="prerequisites-and-goals">Prerequisites and Goals</h2>

<p>Before you begin with this guide, you should be familiar with some <a href="https://indiareads/community/tutorials/an-introduction-to-dns-terminology-components-and-concepts">basic DNS concepts and terminology</a>.  If you need help understanding what an authoritative-only DNS server is used for, check out our guide on <a href="https://indiareads/community/tutorials/a-comparison-of-dns-server-types-how-to-choose-the-right-dns-configuration">the differences between DNS server types</a>.</p>

<p>As an authoritative-only DNS server, NSD does not provide any caching, forwarding, or recursive functionality.  It only responds to iterative requests for the zones it controls.  It can also refer resolvers to other name servers for zones that it has delegated away.</p>

<p>For the purposes of this guide, we will be configuring two servers with NSD software to act as our master and slave servers for our zones.  We will also provide configuration data that will let clients reach a web server on a third host.</p>

<p>We will be using the dummy domain <code><span class="highlight">example.com</span></code> for this guide.  You should substitute your own domain to follow along.  The machines that we will be configuring will have the following properties:</p>

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

<p>After you are finished with this guide, you should have the first two servers configured with NSD to act as authoritative-only server for your zones.  You will be able to use the host names that we configure to reach your servers from the internet, as well as find out the host names by querying the IP addresses.  Any resolving client capable of reaching our servers will be able to get the domain data from our servers.</p>

<h2 id="setting-the-hostname-on-the-name-servers">Setting the Hostname on the Name Servers</h2>

<p>The first step that we need to take is a preparatory one.  Before worrying about the DNS configuration, we need to make sure our name servers can correctly resolve their own hostname in the way we require.</p>

<p>On your first DNS server, edit the <code>/etc/hosts</code> file to set up the FQDN of this computer:</p>
<pre class="code-pre "><code langs="">sudo nano /etc/hosts
</code></pre>
<p>In our case, we need to map the <code>192.0.2.1</code> IP address to our first name server's full name, <code>ns1.example.com</code>.  We can do this by replacing the line that specifies our host name with our public IP address, the FQDN, and the shortened alias for our host:</p>
<pre class="code-pre "><code langs="">127.0.0.1       localhost
<span class="highlight">192.0.2.1       ns1.example.com ns1</span>
</code></pre>
<p>Save and close the file when you are finished.</p>

<p>Next, we need to double-check the <code>/etc/hostname</code> file:</p>
<pre class="code-pre "><code langs="">sudo nano /etc/hostname
</code></pre>
<p>This should contain the value of our <em>unqualified</em> host name.  Modify it if necessary:</p>
<pre class="code-pre "><code langs=""><span class="highlight">ns1</span>
</code></pre>
<p>Save and close the file when you are finished.</p>

<p>If you modified the <code>/etc/hostname</code> file above, tell the system to re-read the file:</p>
<pre class="code-pre "><code langs="">sudo hostname -F /etc/hostname
</code></pre>
<p>We are done with our first DNS server for the time being.  Repeat the steps on the second server.</p>

<p>Modify the <code>/etc/hosts</code> file to specify the second DNS server's host:</p>
<pre class="code-pre "><code langs="">sudo nano /etc/hosts
</code></pre><pre class="code-pre "><code langs="">127.0.0.1       localhost
<span class="highlight">192.0.2.2       ns2.example.com ns2</span>
</code></pre>
<p>Check the <code>/etc/hostname</code> file as well.  This should only have the short unqualified name:</p>
<pre class="code-pre "><code langs="">sudo nano /etc/hostname
</code></pre><pre class="code-pre "><code langs=""><span class="highlight">ns2</span>
</code></pre>
<p>Again, make the system re-read the file if you had to modify anything:</p>
<pre class="code-pre "><code langs="">sudo hostname -F /etc/hostname
</code></pre>
<p>Your servers can now resolve their own names without using DNS.  You are now ready to set up NSD on your servers.</p>

<h2 id="install-nsd-on-both-name-servers">Install NSD on Both Name Servers</h2>

<p>The next step is to actually install the software on your name servers. </p>

<p>Before we begin, we actually have to take one additional preparatory step.  The NSD package in the repos installs the software, configures some components, and attempts to start the service.  The service expects to run as a user called <code>nsd</code>, but the package does not actually create this user account.</p>

<p>To avoid an error upon installation, we will create this user <em>before</em> we install the software.  On each of your machines, create the <code>nsd</code> system user by typing:</p>
<pre class="code-pre "><code langs="">sudo useradd -r nsd
</code></pre>
<p>This will create the correct account needed to complete the installation successfully.</p>

<p>Now, we just need to install the NSD software.  Luckily, NSD is included in the Ubuntu 14.04 repositories, so we can just use <code>apt</code> to pull it down.  We will update our local package index and then download the appropriate package:</p>
<pre class="code-pre "><code langs="">sudo apt-get update
sudo apt-get install nsd
</code></pre>
<p>This will install the software and do some initial configuration.  It will also start the service, even though we have not configured it to serve anything yet.</p>

<h2 id="configure-the-master-nsd-server">Configure the Master NSD Server</h2>

<p>We will begin by setting up our <code>ns1</code> server, which will be configured as the master DNS server for our zones.</p>

<p>The first thing we should do is make sure all of the SSL keys and certificates that NSD uses to securely communicate between the daemon portion of the application and the controller are generated.</p>

<p>To do this, type:</p>
<pre class="code-pre "><code langs="">sudo nsd-control-setup
</code></pre>
<p>There are probably already keys and certificates present in the <code>/etc/nsd</code> directory, but this command will generate anything that is missing.</p>

<h3 id="configure-the-nsd-conf-file">Configure the nsd.conf File</h3>

<p>The main configuration file for NSD is a file called <code>nsd.conf</code> located in the <code>/etc/nsd</code> directory.</p>

<p>There is a file containing only a few comments already in this directory, but we will use a more fully commented example file as our template.  Copy this now to overwrite the current file:</p>
<pre class="code-pre "><code langs="">sudo cp /usr/share/doc/nsd/examples/nsd.conf /etc/nsd/nsd.conf
</code></pre>
<p>Now, open the new file in your text editor with sudo privileges:</p>
<pre class="code-pre "><code langs="">sudo nano /etc/nsd/nsd.conf
</code></pre>
<p>Inside, you will see a number of commented configuration lines organized into sections.  The main sections are <code>server</code>, <code>remote-control</code>, <code>key</code>, <code>pattern</code>, and <code>zone</code>.  We will use most of these for our configuration.</p>

<p>To start with, we should configure the basic properties of our DNS server in the <code>server</code> section.  We will be handling basic IPv4 traffic on the default DNS port 53.  We will use the <code>nsd</code> user we set up earlier.  Most of these will be the default values, but we will uncomment the associated lines to make their values explicit.</p>

<p>We also want to explicitly set the directory that contains our zone data, and our log and pid file locations.  There are many other configuration choices that you can set for this section, but we are going to keep it relatively simple.  Feel free to make additional changes.</p>

<p>Our server section will look like this:</p>
<pre class="code-pre "><code langs="">server:
    do-ip4: yes
    port: 53
    username: nsd
    zonesdir: "/etc/nsd"
    logfile: "/var/log/nsd.log"
    pidfile: "/run/nsd/nsd.pid"
</code></pre>
<p>Next, let's take a look at the <code>remote-control</code> section.  This section is a bit of a misnomer because it is not only used for remote control of our daemon.  We are going to configure this to control the daemon locally.</p>

<p>First, we need to enable the resource and set its interface and port number.  This can all be done by uncommenting the appropriate lines and changing the <code>control-enable</code> directive to "yes".</p>

<p>Next, we can uncomment the lines that specify the key and certificate files.  These match the file names generated when we ran the <code>nsd-control-setup</code> command and should not need to be modified once they are uncommented.</p>

<p>Our values for this section should look like this:</p>
<pre class="code-pre "><code langs="">remote-control:
    control-enable: yes
    control-interface: 127.0.0.1
    control-port: 8952
    server-key-file: "/etc/nsd/nsd_server.key"
    server-cert-file: "/etc/nsd/nsd_server.pem"
    control-key-file: "/etc/nsd/nsd_control.key"
    control-cert-file: "/etc/nsd/nsd_control.pem"
</code></pre>
<p>Next, we will configure the <code>key</code> section.  This section will contain the secret keys that NSD will use to securely execute zone transfers between our master and slave servers.</p>

<p>We need to set the name and algorithm that will be used.  We will use the name <code><span class="highlight">demokey</span></code> for our example.  We will also use the default algorithm (<code>hmac-sha256</code>) that they have selected.</p>

<p>For the secret itself, we will take the advice in the comment on how to securely generate one.  Exit the text editor.  In your terminal, run the following command:</p>
<pre class="code-pre "><code langs="">dd if=/dev/random of=/dev/stdout count=1 bs=32 | base64
</code></pre>
<p>You will receive a randomly generated key in the output of the command:</p>
<pre class="code-pre "><code langs="">0+1 records in
0+1 records out
19 bytes (19 B) copied, 0.000571766 s, 33.2 kB/s
<span class="highlight">+kO0Vu6gC+9bxzMy3TIZVLH+fg==</span>
</code></pre>
<p>Copy the output in red above and open your configuration file again.  Use the copied output as the value of the <code>secret</code> parameter.  This section should look like this:</p>
<pre class="code-pre "><code langs="">key:
    name: "demokey"
    algorithm: hmac-sha256
    secret: "+kO0Vu6gC+9bxzMy3TIZVLH+fg=="
</code></pre>
<p>Next, we'll set up a simple pattern since we have some repetitive information involving our slave server.  We will be notifying and transferring our zones to the same slave each time, so creating a pattern makes sense.</p>

<p>We will call our pattern <code>toslave</code> to describe what the pattern will be used for.  We will set the name and file for each zone individually, so we don't need to worry about that in the pattern.</p>

<p>We want to set the <code>notify</code> parameter in our pattern to reference our slave server's IP address.  We also want to use the key that we specified to securely transfer the zones with TSIG.  We will set up the <code>provide-xfr</code> parameter exactly the same way.</p>

<p>In the end our <code>pattern</code> section should look like this:</p>
<pre class="code-pre "><code langs="">pattern:
    name: "toslave"
    notify: <span class="highlight">192.0.2.2</span> demokey
    provide-xfr: <span class="highlight">192.0.2.2</span> demokey
</code></pre>
<p>Finally, we get to our <code>zone</code> section.  Here, we configure how NSD will handle our specific zones and their associated files.</p>

<p>First, we will configure our forward zone.  We need to set up the zone for our <code><span class="highlight">example.com</span></code> zone.  This is as simple as specifying the domain itself under the <code>name</code> parameter, specifying the name we will use for the zone file, and including the pattern we created above in order to transfer this to our slave server.</p>

<p>The finished forward zone for our demo should look like this:</p>
<pre class="code-pre "><code langs="">zone:
    name: "example.com"
    zonefile: "example.com.zone"
    include-pattern: "toslave"
</code></pre>
<p>Next, we can take care of the reverse zone.  A reverse zone is basically a zone file that allows DNS software to map an IP address back to a host name for clients.  In general, with hosting like IndiaReads, this is taken care of by the hosting provider.</p>

<p>For instance, with IndiaReads, you are not delegated responsibility for a range of IP addresses to set up reverse mappings.  Instead, IndiaReads automatically creates the necessary reverse mappings if you set the host name of the server in the control panel to the FQDN you would like it mapped back to.</p>

<p>You can learn more about reverse mappings by reading the "A Bit About Reverse Zones" section of the <a href="https://indiareads/community/tutorials/how-to-configure-bind-as-an-authoritative-only-dns-server-on-ubuntu-14-04">Bind authoritative-only guide</a>.  We will show you how to set up the reverse zones for NSD for informational purposes and for greater flexibility, even though this will only be relevant in situations where you have been delegated control over the reverse mappings for a block of IPs.</p>

<p>For a reverse zone, we take the first three octets of the IP address, reverse them, and add them as subdomain delegations onto the special domain <code>in-addr.arpa</code>.  This is how the DNS system searches for IP addresses using the same lookup methods as regular domains.  For our case, we will be making a reverse zone that defines the <code>2.0.192.in-addr.arpa</code> mapping.  This will look very similar to the forward zone specification:</p>
<pre class="code-pre "><code langs="">zone:
    name: "2.0.192.in-addr.arpa"
    zonefile: "192.0.2.zone"
    include-pattern: "toslave"
</code></pre>
<p>Save and close the file when you are finished.</p>

<h3 id="create-the-forward-zone-file">Create the Forward Zone File</h3>

<p>Now, we need to create the forward zone file.  In our configuration, we named the zone file as "example.com.zone".  We will have to create a file with this name in our <code>/etc/nsd</code> directory.</p>

<p>Open that file up in your text editor with sudo privileges:</p>
<pre class="code-pre "><code langs="">sudo nano /etc/nsd/example.com.zone
</code></pre>
<p>The first thing we need to do is set a few parameters up top.  We will set the <code>$ORIGIN</code> parameter which points to the domain we are configuring in FQDN format (complete with the ending dot).  We also want to set the default time-to-live.  We will use 1800 seconds, or 30 minutes:</p>
<pre class="code-pre "><code langs="">$ORIGIN <span class="highlight">example.com</span>.
$TTL 1800
</code></pre>
<p>Next, we need our SOA, or start of authority record.  This will look like this:</p>
<pre class="code-pre "><code langs="">@       IN      SOA     ns1.<span class="highlight">example.com</span>.      admin.<span class="highlight">example.com</span>. (
                        <span class="highlight">2014070201</span>        ; serial number
                        3600                    ; refresh
                        900                     ; retry
                        1209600                 ; expire
                        1800                    ; ttl
                        )
</code></pre>
<p>This defines some zone-wide values.  The <code>ns1.example.com.</code> value is used to specify the domain location of one of the authoritative servers for this zone.  The <code>admin.example.com.</code> is used to specify an email address where the zone administrators can be reached.</p>

<p>The email address, in this case is <code>admin@example.com</code>.  In a DNS zone file, the "@" symbol must be changed into a dot.  The ending dot is also important, as they always are when specifying a FQDN.</p>

<p>The values in the parentheses define some of the values for our zone.  The only one we will mention here is the serial number.  This value <strong>must</strong> be incremented every time that you make a change to the zone file.  Here, we are demonstrating using the date of this writing (July 02, 2014) plus a revision number.</p>

<p>Next, we need to use NS records to define the name servers that are authoritative for this zone.  Remember to use the FQDN for your domain, including the ending dot:</p>
<pre class="code-pre "><code langs="">                    IN      NS      ns1.<span class="highlight">example.com</span>.
                    IN      NS      ns2.<span class="highlight">example.com</span>.
</code></pre>
<p>Next, we need to set up the A records that will actually tell clients how to reach the name servers we specified.  This is what maps our host names to their actual IP addresses:</p>
<pre class="code-pre "><code langs="">ns1                 IN      A       <span class="highlight">192.0.2.1</span>
ns2                 IN      A       <span class="highlight">192.0.2.2</span>
</code></pre>
<p>Finally, we want to add any additional A records for our other hosts.  In our case, we will be setting up our base domain (<code>example.com</code>) and the <code>www</code> hostname to map to our web server:</p>
<pre class="code-pre "><code langs="">@                   IN      A       <span class="highlight">192.0.2.3</span>
www                 IN      A       <span class="highlight">192.0.2.3</span>
</code></pre>
<p>When you are finished, your completed file should look like this:</p>
<pre class="code-pre "><code langs="">$ORIGIN <span class="highlight">example.com</span>.
$TTL 1800
@       IN      SOA     ns1.<span class="highlight">example.com</span>.      admin.<span class="highlight">example.com</span>. (
                        <span class="highlight">2014070201</span>        ; serial number
                        3600                    ; refresh
                        900                     ; retry
                        1209600                 ; expire
                        1800                    ; ttl
                        )
; Name servers
                    IN      NS      ns1.<span class="highlight">example.com</span>.
                    IN      NS      ns2.<span class="highlight">example.com</span>.

; A records for name servers
ns1                 IN      A       <span class="highlight">192.0.2.1</span>
ns2                 IN      A       <span class="highlight">192.0.2.2</span>

; Additional A records
@                   IN      A       <span class="highlight">192.0.2.3</span>
www                 IN      A       <span class="highlight">192.0.2.3</span>
</code></pre>
<p>Save and close the file when you are finished.</p>

<h3 id="create-the-reverse-zone-file">Create the Reverse Zone File</h3>

<p>Next, we will make a similar file for our reverse zone.  Remember that this is only necessary if you have been delegated responsibility for the reverse mapping of a block of addresses.</p>

<p>Create the reverse zone file that you referenced in your <code>nsd.conf</code> file and open it with sudo privileges in your text editor:</p>
<pre class="code-pre "><code langs="">sudo nano /etc/nsd/192.0.2.zone
</code></pre>
<p>Again, we will start off by defining the <code>$ORIGIN</code> and <code>$TTL</code> parameters.  This time, remember to set the origin to the <code>in-addr.arpa</code> subdomain for your zone.  In our case this will look like this:</p>
<pre class="code-pre "><code langs="">$ORIGIN <span class="highlight">2.0.192</span>.in-addr.arpa.
$TTL 1800
</code></pre>
<p>Next, we need to set the SOA records, just as before.  We can pretty much use the exact same values for this file since the same email and authoritative name server are responsible for both zones.  Furthermore, the numerical values should work in this instance as well.  Remember to modify the serial number though every time you make a change:</p>
<pre class="code-pre "><code langs="">@       IN      SOA     ns1.<span class="highlight">example.com</span>.      admin.<span class="highlight">example.com</span>. (
                        <span class="highlight">2014070201</span>        ; serial number
                        3600                    ; refresh
                        900                     ; retry
                        1209600                 ; expire
                        1800                    ; ttl
                        )
</code></pre>
<p>When you are finished, the file should look like this:</p>

<p>Again, we need to define the name servers that are authoritative for the zone.  These will be the same servers again:</p>
<pre class="code-pre "><code langs="">                        IN      NS      ns1.<span class="highlight">example.com</span>.
                        IN      NS      ns2.<span class="highlight">example.com</span>.
</code></pre>
<p>Finally, we need to provide the actual reverse domain mappings by routing the last octet of each IP address to the FQDN of the associated host using PTR records:</p>
<pre class="code-pre "><code langs="">1                       IN      PTR     ns1.<span class="highlight">example.com</span>.
2                       IN      PTR     ns2.<span class="highlight">example.com</span>.
3                       IN      PTR     www.<span class="highlight">example.com</span>.
</code></pre>
<p>When you are finished, the file should look like this:</p>
<pre class="code-pre "><code langs="">$ORIGIN <span class="highlight">2.0.192</span>.in-addr.arpa.
$TTL 1800
@       IN      SOA     ns1.<span class="highlight">example.com</span>.      admin.<span class="highlight">example.com</span>. (
                        <span class="highlight">2014070201</span>        ; serial number
                        3600                    ; refresh
                        900                     ; retry
                        1209600                 ; expire
                        1800                    ; ttl
                        )
; Name servers
                        IN      NS      ns1.<span class="highlight">example.com</span>.
                        IN      NS      ns2.<span class="highlight">example.com</span>.

; PTR records
1                       IN      PTR     ns1.<span class="highlight">example.com</span>.
2                       IN      PTR     ns2.<span class="highlight">example.com</span>.
3                       IN      PTR     www.<span class="highlight">example.com</span>.
</code></pre>
<p>Save and close the file when you are finished.</p>

<h3 id="testing-the-files-and-restarting-the-service">Testing the Files and Restarting the Service</h3>

<p>Now that we have our master server configured, we can go ahead and test our configuration file and implement our changes.</p>

<p>You can check the syntax of the main configuration file by using the included <code>nsd-checkconf</code> tool.  Simply point the tool to your main configuration file:</p>
<pre class="code-pre "><code langs="">sudo nsd-checkconf /etc/nsd/nsd.conf
</code></pre>
<p>If this returns immediately with no output, it means the syntax of your main configuration file is valid.  If you get an error, check the syntax of your configuration file to fix any mistakes.</p>

<p>After you are able to execute the check cleanly, you can restart the service by typing:</p>
<pre class="code-pre "><code langs="">sudo service nsd restart
</code></pre>
<p>This will stop and start the NSD daemon.</p>

<p>Check the logs to see any messages:</p>
<pre class="code-pre "><code langs="">sudo tail -f /var/log/nsd.log
</code></pre>
<p>You should see a number of errors that look like this:</p>
<pre class="code-pre "><code langs="">. . .
[1404333729] nsd[2142]: error: xfrd: zone 2.0.192.in-addr.arpa: received notify response error NAME ERROR from 192.0.2.2
[1404333729] nsd[2142]: error: xfrd: zone 2.0.192.in-addr.arpa: max notify send count reached, 192.0.2.2 unreachable
</code></pre>
<p>This is here because NSD is attempting to transfer the zone to the slave server, which has not been configured yet.</p>

<h2 id="configure-the-slave-nsd-server">Configure the Slave NSD Server</h2>

<p>Now that we have the master server set up, we can go ahead and get the slave server ready as well.</p>

<p>Again, we want to make sure that our SSL certificates and keys are all generated and available.  To do this, issue the following command:</p>
<pre class="code-pre "><code langs="">sudo nsd-control-setup
</code></pre>
<p>This will ensure that all of the credential files needed to control the daemon are available to us.</p>

<h3 id="configure-the-nsd-conf-file">Configure the nsd.conf File</h3>

<p>The <code>nsd.conf</code> file for the slave server will be mostly the same as the master server.  There are only a few things that we will need to modify.  Begin by copying the master server's <code>/etc/nsd/nsd.conf</code> file into the slave server's <code>/etc/nsd/nsd.conf</code> file.</p>

<p>This slave server's file should now look like this:</p>
<pre class="code-pre "><code langs="">server:
    do-ip4: yes
    port: 53
    username: nsd
    zonesdir: "/etc/nsd"
    logfile: "/var/log/nsd.log"
    pidfile: "/run/nsd/nsd.pid"

remote-control:
    control-enable: yes
    control-interface: 127.0.0.1
    control-port: 8952
    server-key-file: "/etc/nsd/nsd_server.key"
    server-cert-file: "/etc/nsd/nsd_server.pem"
    control-key-file: "/etc/nsd/nsd_control.key"
    control-cert-file: "/etc/nsd/nsd_control.pem"

key:
    name: "demokey"
    algorithm: hmac-sha256
    secret: "+kO0Vu6gC+9bxzMy3TIZVLH+fg=="

pattern:
    name: "toslave"
    notify: 192.0.2.2 demokey
    provide-xfr: 192.0.2.2 demokey

zone:
    name: "example.com"
    zonefile: "example.com.zone"
    include-pattern: "toslave"

zone:
    name: "2.0.192.in-addr.arpa"
    zonefile: "192.0.2.zone"
    include-pattern: "toslave"
</code></pre>
<p>This is almost exactly what we need.</p>

<p>The <code>server</code>, <code>remote-control</code>, and <code>key</code> sections are already completely configured.  The "secret" in the <code>key</code> section <em>must</em> match the master server's value, so copying the complete file contents makes it easy to satisfy this requirement.</p>

<p>The first thing we will need to modify is the <code>pattern</code> section.  The section that we copied is specific to the master server, so we want to modify it to address things from the slave server's perspective.</p>

<p>First, change the name to something more descriptive.  We will use the same convention and call this <code>frommaster</code>.  We also need to change the directives that this sets.  Instead of the <code>notify</code> parameter, slave servers need an <code>allow-notify</code> parameter, which specifies the servers that are allowed to notify it.  We will still use the same key, so we just need to modify the name and the appropriate IP address.</p>

<p>In a similar manner, we need to change the <code>provide-xfr</code> parameter to <code>request-xfr</code>.  The format of this changes slightly.  We need to specify that we are wanting a AXFR transfer (the only kind that NSD masters are capable of) and we need to specify the IP address <em>and</em> the port number of the master.</p>

<p>The <code>pattern</code> section will look something like this when you are finished:</p>
<pre class="code-pre "><code langs="">pattern:
    name: "frommaster"
    allow-notify: <span class="highlight">192.0.2.1</span> demokey
    request-xfr: AXFR <span class="highlight">192.0.2.1</span>@53 demokey
</code></pre>
<p>For the <code>zone</code> sections, the only thing we need to modify is the <code>include-pattern</code> to match our the new pattern we just created:</p>
<pre class="code-pre "><code langs="">zone:
    name: "example.com"
    zonefile: "example.com.zone"
    include-pattern: "frommaster"

zone:
    name: "2.0.192.in-addr.arpa"
    zonefile: "192.0.2.zone"
    include-pattern: "frommaster"
</code></pre>
<p>When you are finished, save and close the file.</p>

<h3 id="testing-the-files-and-restarting-the-service">Testing the Files and Restarting the Service</h3>

<p>Since our slave server will receive all of its zone data through transfers from the master, we do not actually need to configure the zone files on this host.</p>

<p>Again, we should check the syntax of our main configuration file by typing:</p>
<pre class="code-pre "><code langs="">sudo nsd-checkconf /etc/nsd/nsd.conf
</code></pre>
<p>If you receive any errors, you need to take another look at your <code>nsd.conf</code> file to address the syntax issues.  If the command returns without any output, it means that your syntax is valid in the file.</p>

<p>When your configuration file passes the test, you can restart the service by typing:</p>
<pre class="code-pre "><code langs="">sudo service nsd restart
</code></pre>
<p>Check the logs to make sure things are going okay:</p>
<pre class="code-pre "><code langs="">sudo tail -f /var/log/nsd.log
</code></pre>
<h2 id="delegate-authority-to-your-name-servers">Delegate Authority to your Name Servers</h2>

<p>Now, your authoritative-only NSD servers should be configured and ready to serve DNS information about your domain.  We still need to configure your domain so that it knows to use your name servers though.</p>

<p>To do this, you need to adjust some settings under the registrar where you purchased your domain name.  Some of the terminology and certainly the interface will vary from registrar to registrar, but you should be able to find the settings if you look carefully.</p>

<p>I will be demonstrating how to do this with <a href="https://www.namecheap.com/">Namecheap</a>, a fairly standard domain name registrar.</p>

<p>We need to adjust your name servers in a way that will allow us to set <strong>glue records</strong> at the domain's parent.  This is necessary whenever the name servers are <em>within</em> the domain itself.</p>

<p>When you delegate a subdomain (like <code>example.com</code> from the <code>com</code> domain), you must specify the name servers that are authoritative for the domain.  If the name servers are within the domain, you <em>also</em> must include a glue record, which is simply an A record for each of the name server's that are authoritative for the delegated zone.</p>

<p>We need this because DNS lookups would get caught in a loop if the glue records were not included.  Clients would ask our registrar who is authoritative for the domain <code>example.com</code> and our registrar would (after we configure this) return <code>ns1.example.com</code> and <code>ns2.example.com</code>.  If we do not include A records to resolve these to IP addresses, then the client will never be able to move beyond this point.  It would have no way of finding the IP addresses of the name servers it needs because these are typically defined in the name servers themselves.</p>

<p>The location in the registrar's interface where you can configure your name servers <em>and</em> their associated IP addresses will vary depending on your provider.  With Namecheap, there is a section called "Nameserver Registration" that allows you to set the IP addresses of name servers to create glue records:</p>

<p><img src="https://assets.digitalocean.com/articles/bind_auth/register.png" alt="Namecheap nameserver registration" /></p>

<p>Here, you can set up the name servers and map them to a specific IP address:</p>

<p><img src="https://assets.digitalocean.com/articles/bind_auth/give_ips.png" alt="Namecheap map name servers" /></p>

<p>When you're done with this, you'll need to set the active name servers that are being used for your domain.  Namecheap has an option called "Domain Name Server Setup" that accomplishes that:</p>

<p><img src="https://assets.digitalocean.com/articles/bind_auth/server_setup.png" alt="Namecheap set nameservers" /></p>

<p>In the interface that you get when selecting that option, you can enter the host names of your name servers that you just registered:</p>

<p><img src="https://assets.digitalocean.com/articles/bind_auth/use_servers.png" alt="Namecheap input nameservers" /></p>

<p>The changes that you make with your registrar might take some time to propagate.  The data will also take additional time to spread to the rest of the world's DNS servers.  Typically, this process should complete in the next 24-48 hours.</p>

<h2 id="conclusion">Conclusion</h2>

<p>Using this guide, you should now have a master and slave authoritative-only DNS servers that can be used to serve DNS information about your domains.  Unlike Bind, NSD is optimized for high performance authoritative behavior, so you can get greater performance that is tuned specifically your needs.</p>

    