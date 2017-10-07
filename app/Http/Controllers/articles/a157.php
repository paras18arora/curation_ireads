<link rel='stylesheet' href='digital_ocean.css'><br><img src=https://community-cdn-digitalocean-com.global.ssl.fastly.net/assets/tutorials/images/large/Set-up-DNSSEC_twitter.png?1426699735/> <br> 
      <h3 id="about-dnssec">About DNSSEC</h3>

<p>DNS Security Extensions (DNSSEC) is a technology designed to protect applications and DNS resolvers from using forged or manipulated DNS data.</p>

<p><strong>The problem:</strong><br />
It is possible for an attacker to tamper with a DNS response or <a href="http://en.wikipedia.org/wiki/DNS_cache_poisoning">poison the DNS cache</a> and take users to a malicious site with the legitimate domain name in their address bar.</p>

<p><strong>The solution:</strong><br />
DNSSEC configured authoritative DNS servers prevent this kind of attack by digitally signing each resource record with a private key. The DNS resolver verifies the integrity of a zone record using the public key and the digital signature.</p>

<h3 id="about-nsd">About NSD</h3>

<p>Name Server Daemon (<strong>NSD</strong>) is an open source authoritative-only DNS server software developed by <a href="http://www.nlnetlabs.nl/">NLNet Labs</a>. It uses BIND-style zone files for easy configuration.</p>

<p>An authoritative-only DNS server provides answers to queries for the zones that it is responsible for. In this article we will be setting up our own authoritative NSD nameservers for two domain names. We will configure NSD to provide DNSSEC signed replies for both domain names.</p>

<h2 id="prerequisites">Prerequisites</h2>

<p>This article requires the reader to be knowledgeable in the following areas:</p>

<ul>
<li><a href="https://indiareads/community/tutorials/an-introduction-to-dns-terminology-components-and-concepts">An Introduction to DNS Terminology, Components, and Concepts</a></li>
<li><a href="https://indiareads/community/tutorials/a-comparison-of-dns-server-types-how-to-choose-the-right-dns-configuration">A Comparison of DNS Server Types: How To Choose the Right DNS Configuration</a></li>
</ul>

<p><strong>Two domain names will be used in this article:</strong></p>

<table class="pure-table"><thead>
<tr>
<th>Domain Name</th>
<th>Nameservers</th>
</tr>
</thead><tbody>
<tr>
<td>example.com</td>
<td>master.example.com</td>
</tr>
<tr>
<td></td>
<td>slave.example.com</td>
</tr>
<tr>
<td>foobar.org</td>
<td>master.example.com</td>
</tr>
<tr>
<td></td>
<td>slave.example.com</td>
</tr>
</tbody></table>

<p><strong>The following two Droplets will run NSD:</strong></p>

<table class="pure-table"><thead>
<tr>
<th>Hostname</th>
<th>IP Address</th>
</tr>
</thead><tbody>
<tr>
<td>master.example.com</td>
<td>1.1.1.1</td>
</tr>
<tr>
<td>slave.example.com</td>
<td>2.2.2.2</td>
</tr>
</tbody></table>

<p>You should replace <span class="highlight">1.1.1.1</span> with the IP address of your master nameserver throughout the tutorial, and <span class="highlight">2.2.2.2</span> with the IP address of your slave nameserver.</p>

<p>The objective of this article is to show how to set up a nameserver that, regardless of its own domain's DNSSEC status, can serve domains that use DNSSEC. The domain <span class="highlight">example.com</span> is used for the nameservers for convenience; there is no requirement to configure DNSSEC for the nameserver domain name. The nameservers could just as easily be set to master.my-soa.com and slave.my-soa.com.</p>

<p>You will also want to have an IP address in mind where you want your domains to resolve. If you don't have web hosts set up for these domains yet, you can create another test Droplet which will run a web server. Choose the <strong>LAMP on Ubuntu 14.04</strong> image.</p>

<p><img src="https://assets.digitalocean.com/articles/dnssec_nsdnameserver/1.png" alt="LAMP on Ubuntu 14.04 image" /></p>

<p>The IP address of the LAMP Droplet will be <strong>3.3.3.3</strong>. This IP will be used as the A record for both the domain names to check if they resolve from a web browser. You should replace <span class="highlight">3.3.3.3</span> with your desired web host IP address(es) throughout the tutorial.</p>

<h2 id="dnssec-terminology">DNSSEC Terminology</h2>

<p>DNSSEC works on the concept of <a href="http://en.wikipedia.org/wiki/Public-key_cryptography">public-key cryptography</a> and introduces new DNS record types. In this section we will discuss some of the terms that will be used in this article.</p>

<h3 id="keys">Keys</h3>

<ul>
<li><strong>ZSK</strong>: <strong>Z</strong>one <strong>S</strong>igning <strong>K</strong>ey is a private/public pair of keys. The private key creates a digital signature for all the DNS records while the public key is used by the DNS resolver to verify it.</li>
<li><strong>KSK</strong>: <strong>K</strong>ey <strong>S</strong>igning <strong>K</strong>ey is a private/public pair of keys. The private key signs the ZSK while the public key verifies it.</li>
</ul>

<h3 id="records">Records</h3>

<ul>
<li><strong>DNSKEY</strong>: Contains the public keys of KSK and ZSK.</li>
<li><strong>RRSIG</strong>: <strong>R</strong>esource <strong>R</strong>ecord <strong>Sig</strong>nature exists for each record and provides the digital signature of that record. The RRSIG record is based on the record itself and the ZSK.</li>
<li><strong>DS</strong>: The <strong>D</strong>elegation <strong>S</strong>igner record is used to verify the integrity of the DNSKEY records. This record is entered in the domain registrar's control panel and resides on the TLD's authoritative nameserver.</li>
</ul>

<p>Setting up DNSSEC for a domain requires appropriate records with both the nameservers and the registrar.</p>

<h2 id="how-dnssec-works">How DNSSEC Works</h2>

<p>First we'll talk about <strong>DNSSEC</strong> from the domain owner's perspective (that's you!). You want to make sure all of the DNS records that are coming from your nameservers are signed. That way, if someone tries to spoof your DNS records, they'll be identified as false, and your visitors can avoid going to a malicious site.</p>

<p>So how do you set that up? First, for every domain, you have to generate two unique pairs of private/public keys on the nameserver. The public keys for the domain get stored in the DNSKEY records, which are listed in the zone file for that domain. Two other types of records, the DS records and the RRSIG records, get generated from the DNSKEY records. These three types of records are all cryptographically linked. That is, once you have seen one of the three, you can tell whether the other two are valid.</p>

<p>(Note: For clarity, while there are multiples of each type of record for each domain, we'll refer to them in the singular for the rest of this explanation.)</p>

<p>Next, you upload the DS record to your registrar, which publishes it to the TLD nameservers for your domain. Since the only way to publish a DS record is through the registrar, this proves that the domain owner is the one who published the DS record, which proves the validity of that DS record. The DS record's purpose is to establish an <em>authentication chain</em> between the TLD nameserver and the nameservers you are running for your domain. This works because the DS record is based on the DNSKEY, so any DNS resolver can check that your DNSKEY matches the DS record, and thus that it is the correct one for the domain.</p>

<p>An RRSIG record is a signature that accompanies other types of DNS record (like A, MX, etc) that is based on the record value itself (such as an IP address) and the DNSKEY.</p>

<p>With the DNSKEY, DS, and RRSIG records configured, DNSSEC is now set up for your domain.</p>

<p>Next we'll talk about it from a user perspective. Say that a user wants to visit your domain, so they query a DNS resolver for your domain's A record. In this example, the recursive DNS resolver has already checked the validity of the DNSKEY for this domain against the DS record on the TLD nameservers, although it could easily check this for the first time as well.</p>

<p><strong>Here's an illustration of how this query works:</strong></p>

<ol>
<li>The user sends a query for an A record, which reaches a DNSSEC aware recursive DNS server.</li>
<li>The DNS server finds out that the queried domain supports DNSSEC by discovering its DS records. It sends a query for the A record with the <a href="https://www.dnssec-tools.org/wiki/index.php/DO">DO bit</a> to your authoritative nameservers.</li>
<li>Your nameservers respond with the A record and the corresponding RRSIG record.</li>
<li>The recursive DNS server calculates the value of the A record + the DNSKEY record it has on file, and checks it against the decrypted RRSIG record. (It could check the DS record to validate the DNSKEY record first, if it's not on file.) If the hashes match, the DNS server returns the A record to the user, who can now visit your website.</li>
</ol>

<p><img src="https://assets.digitalocean.com/articles/dnssec_nsdnameserver/2.png" alt="DNSSEC validation" /></p>

<p>For more about how DNSSEC works, you may want to read <a href="http://technet.microsoft.com/en-in/library/jj200221.aspx">this article</a>. For a more comprehensive list of DNSSEC terminology, read <a href="http://technet.microsoft.com/en-us/library/ee649132(v=ws.10).aspx">this</a>.</p>

<h2 id="step-zero-—-check-for-domain-and-registrar-support">Step Zero — Check for Domain and Registrar Support</h2>

<p>Before deciding to setup DNSSEC on your own NSD nameservers, make sure your domain extension (.com, .org, etc.) and registrar support DNSSEC.</p>

<p>To check if a domain extension is DNSSEC ready, query for its DNSKEY record with the following command:</p>
<pre class="code-pre "><code langs="">dig DNSKEY <span class="highlight">com</span>. +short
</code></pre>
<p>This should return the public keys as follows:</p>
<pre class="code-pre "><code langs="">256 3 8 AQPbokupKUJ5LLAtDEs6R3nDOHxF2jQEFtJEFTiDcfbsZia4fg3EK9Wv D9ZIr+7t2n1ddqRGHnTTInHTjduaKFPqm2iKaDHdrc6095o1mzqojnd1 bTtI45XNu61QmT5IU4VPT7HDUSby+53gLAsjLPyNsNEMp7Cc52RVxCHD no9efw==
257 3 8 AQPDzldNmMvZFX4NcNJ0uEnKDg7tmv/F3MyQR0lpBmVcNcsIszxNFxsB fKNW9JYCYqpik8366LE7VbIcNRzfp2h9OO8HRl+H+E08zauK8k7evWEm u/6od+2boggPoiEfGNyvNPaSI7FOIroDsnw/taggzHRX1Z7SOiOiPWPN IwSUyWOZ79VmcQ1GLkC6NlYvG3HwYmynQv6oFwGv/KELSw7ZSdrbTQ0H XvZbqMUI7BaMskmvgm1G7oKZ1YiF7O9ioVNc0+7ASbqmZN7Z98EGU/Qh 2K/BgUe8Hs0XVcdPKrtyYnoQHd2ynKPcMMlTEih2/2HDHjRPJ2aywIpK Nnv4oPo/
</code></pre>
<p>No output indicates lack of DNSSEC support for that domain extension.</p>

<p>It is not enough if your TLD supports DNSSEC; the domain registrar must also have an option for entering DS records in their control panel. This can be confirmed by Googling "<span class="highlight">registrar name</span> dnssec" or by asking a registrar directly. Following are some popular registrars who support DNSSEC:</p>

<ul>
<li><a href="https://www.dynadot.com/">Dynadot</a></li>
<li><a href="http://gandi.net/">Gandi</a></li>
<li><a href="http://godaddy.com/">GoDaddy</a></li>
<li><a href="https://www.gkg.net/">GKG</a></li>
<li><a href="http://www.name.com/">Name.com</a></li>
</ul>

<p>Once you confirm that both your TLD and domain registrar support DNSSEC, you can start setting up your custom nameservers.</p>

<h2 id="step-one-—-install-and-set-up-nsd-on-both-servers">Step One — Install and Set Up NSD on Both Servers</h2>

<p>In this step we will install and configure NSD on both the master and slave servers. We will also set up DNS records for the domain <strong>example.com</strong>. This section will serve as a quick setup for NSD. Read <a href="https://indiareads/community/tutorials/how-to-use-nsd-an-authoritative-only-dns-server-on-ubuntu-14-04">this article</a> for detailed instructions on setting up NSD.</p>

<h3 id="master-server">Master Server</h3>

<p>In addition to the NSD server package the master server requires the following packages:</p>

<ul>
<li><strong>ldnsutils</strong>: For DNSSEC key generation and zone signing.</li>
<li><strong>haveged</strong>: For <a href="https://indiareads/community/tutorials/how-to-setup-additional-entropy-for-cloud-servers-using-haveged">increasing entropy</a>. Installing this package quickens the key generation process.</li>
</ul>

<p>To avoid an error during installation create a system user named <span class="highlight">nsd</span>:</p>
<pre class="code-pre "><code langs="">useradd -r nsd
</code></pre>
<p>The <span class="highlight">-r</span> option creates a system user. Update the repository and install NSD, ldnsutils, and haveged.</p>
<pre class="code-pre "><code langs="">apt-get update
apt-get install nsd ldnsutils haveged
</code></pre>
<p>DNS zone transfer from the master server to the slave server is secured by a shared secret. Use the following command to generate the secret randomly:</p>
<pre class="code-pre "><code langs="">dd if=/dev/random count=1 bs=32 2> /dev/null | base64
</code></pre>
<p>Note down the output string. We will be using it in the configuration file of both the master and slave servers.</p>
<pre class="code-pre "><code langs=""><span class="highlight">sHi0avMk1bME89cnJdHkYzFBbvQmQ8YZ</span>
</code></pre>
<p>Create a separate directory for zone files:</p>
<pre class="code-pre "><code langs="">mkdir /etc/nsd/zones
</code></pre>
<p>Edit NSD's configuration file:</p>
<pre class="code-pre "><code langs="">nano /etc/nsd/nsd.conf
</code></pre>
<p>The first is the <span class="highlight">server</span> section which specifies locations for the zone files, logs, and PID (Process ID) files:</p>
<pre class="code-pre "><code langs="">server:
    username: nsd
    hide-version: yes
    zonesdir: "/etc/nsd/zones"
    logfile: "/var/log/nsd.log"
    pidfile: "/run/nsd/nsd.pid"
</code></pre>
<p>The <span class="highlight">hide-version</span> directive prevents NSD from <a href="http://support.microsoft.com/kb/314780">returning its version</a> when CHAOS class query is done.</p>

<p>In the <span class="highlight">key</span> section we define a key named <strong>mykey</strong> and input the previously generated secret.</p>
<pre class="code-pre "><code langs="">key:
    name: "mykey"
    algorithm: hmac-sha256
    secret: "<span class="highlight">sHi0avMk1bME89cnJdHkYzFBbvQmQ8YZ</span>"
</code></pre>
<p>Each <span class="highlight">zone</span> section will contain the domain name, zone filename, and details of its slave server:</p>
<pre class="code-pre "><code langs="">zone:
    name: <span class="highlight">example.com</span>
    zonefile: <span class="highlight">example.com</span>.zone
    notify: <span class="highlight">2.2.2.2</span> mykey
    provide-xfr: <span class="highlight">2.2.2.2</span> mykey
zone:
    name: <span class="highlight">foobar.org</span>
    zonefile: <span class="highlight">foobar.org</span>.zone
    notify: <span class="highlight">2.2.2.2</span> mykey
    provide-xfr: <span class="highlight">2.2.2.2</span> mykey
</code></pre>
<p>The <span class="highlight">notify:</span> and <span class="highlight">provide-xfr:</span> lines should have the <strong>IP address of the slave server</strong>. Save the file and create a zone file for <strong>example.com</strong>.</p>
<pre class="code-pre "><code langs="">nano /etc/nsd/zones/<span class="highlight">example.com</span>.zone
</code></pre>
<p>We will add the following data into the zone file. Variables are not marked, since you will need to customize all of the entries:</p>
<pre class="code-pre "><code langs="">$ORIGIN example.com.
$TTL 1800
@       IN      SOA     master.example.com.    email.example.com. (
                        2014080301
                        3600
                        900
                        1209600
                        1800
                        )
@       IN      NS      master.example.com.
@       IN      NS      slave.example.com.
master  IN      A       1.1.1.1
slave   IN      A       2.2.2.2
@       IN      A       3.3.3.3
www     IN      CNAME   example.com.
@       IN      MX      10 aspmx.l.google.com.
@       IN      MX      20 alt1.aspmx.l.google.com.
@       IN      MX      20 alt2.aspmx.l.google.com.
@       IN      MX      30 aspmx2.googlemail.com.
@       IN      MX      30 aspmx3.googlemail.com.
</code></pre>
<p>Save this file and create a zone file for <strong>foobar.org</strong>.</p>
<pre class="code-pre "><code langs="">nano /etc/nsd/zones/<span class="highlight">foobar.org</span>.zone
</code></pre>
<p>The second zone file:</p>
<pre class="code-pre "><code langs="">$ORIGIN foobar.org.
$TTL 1800
@       IN      SOA     master.example.com.    email.example.com. (
                        2014080301
                        3600
                        900
                        1209600
                        1800
                        )
@       IN      NS      master.example.com.
@       IN      NS      slave.example.com.
@       IN      A       3.3.3.3
www     IN      CNAME   foobar.org.
@       IN      MX      0 mx.sendgrid.com.
</code></pre>
<p>Save the file and check for configuration errors using the <span class="highlight">nsd-checkconf</span> command:</p>
<pre class="code-pre "><code langs="">nsd-checkconf /etc/nsd/nsd.conf
</code></pre>
<p>A valid configuration should not output anything. Restart the NSD server:</p>
<pre class="code-pre "><code langs="">service nsd restart
</code></pre>
<p>Check if the DNS records are in effect for the domains using the <span class="highlight">dig</span> command.</p>
<pre class="code-pre "><code langs="">dig ANY <span class="highlight">example.com</span>. @localhost +norec +short
</code></pre>
<p>A sample output from this command:</p>
<pre class="code-pre "><code langs="">master.example.com. email.example.com. 2014080301 3600 900 1209600 1800
master.example.com.
slave.example.com.
3.3.3.3
10 aspmx.l.google.com.
20 alt1.aspmx.l.google.com.
20 alt2.aspmx.l.google.com.
30 aspmx2.googlemail.com.
30 aspmx3.googlemail.com.
</code></pre>
<p>Repeat the <span class="highlight">dig</span> command for the second domain:</p>
<pre class="code-pre "><code langs="">dig ANY <span class="highlight">foobar.org</span>. @localhost +norec +short
</code></pre>
<p>We have successfully installed and configured NSD on the master server and have also created two zones.</p>

<h2 id="slave-server">Slave Server</h2>

<p>The slave server requires only the NSD package as no key generation or signing is done on it.</p>

<p>Create a system user named <span class="highlight">nsd</span>:</p>
<pre class="code-pre "><code langs="">useradd -r nsd
</code></pre>
<p>Update the repository and install NSD:</p>
<pre class="code-pre "><code langs="">apt-get update
apt-get install nsd
</code></pre>
<p>Create a directory for the zone files:</p>
<pre class="code-pre "><code langs="">mkdir /etc/nsd/zones
</code></pre>
<p>Edit the NSD configuration file:</p>
<pre class="code-pre "><code langs="">nano /etc/nsd/nsd.conf
</code></pre>
<p>Add configuration directives:</p>
<pre class="code-pre "><code langs="">server:
    username: nsd
    hide-version: yes
    zonesdir: "/etc/nsd/zones"
    logfile: "/var/log/nsd.log"
    pidfile: "/run/nsd/nsd.pid"

key:
    name: "mykey"
    algorithm: hmac-sha256
    secret: "<span class="highlight">sHi0avMk1bME89cnJdHkYzFBbvQmQ8YZ</span>"

zone:
    name: <span class="highlight">example.com</span>
    zonefile: <span class="highlight">example.com</span>.zone
    allow-notify: <span class="highlight">1.1.1.1</span> mykey
    request-xfr: <span class="highlight">1.1.1.1</span> mykey
zone:
    name: <span class="highlight">foobar.org</span>
    zonefile: <span class="highlight">foobar.org</span>.zone
    allow-notify: <span class="highlight">1.1.1.1</span> mykey
    request-xfr: <span class="highlight">1.1.1.1</span> mykey
</code></pre>
<p>The <span class="highlight">secret</span> for <strong>mykey</strong> should be exactly same as the one entered in the master server. Use the <strong>master server's IP address</strong> in the <span class="highlight">allow-notify</span> and <span class="highlight">request-xfr</span> lines.</p>

<p>Check for configuration errors:</p>
<pre class="code-pre "><code langs="">nsd-checkconf /etc/nsd/nsd.conf
</code></pre>
<p>Restart the NSD service:</p>
<pre class="code-pre "><code langs="">service nsd restart
</code></pre>
<p>Force a zone transfer for both the domains with the <span class="highlight">nsd-control</span> command:</p>
<pre class="code-pre "><code langs="">nsd-control force_transfer <span class="highlight">example.com</span>
nsd-control force_transfer <span class="highlight">foobar.org</span>
</code></pre>
<p>Now check if this server can answer queries for the domain <strong>example.com</strong>.</p>
<pre class="code-pre "><code langs="">dig ANY <span class="highlight">example.com</span>. @localhost +norec +short
</code></pre>
<p>If this returns the same result as the master this zone is setup properly. Repeat the <span class="highlight">dig</span> command for the <strong>foorbar.org</strong> domain to verify if its zone is set up properly. We now have a pair of NSD DNS servers which are authoritative for the domains <strong>example.com</strong> and <strong>foobar.org</strong>.</p>

<p>At this point, you should be able to visit your domains in your web browser. They will resolve to the default LAMP server we set up, or whichever host you specified.</p>

<h2 id="step-two-—-generate-the-keys-and-sign-the-zone">Step Two — Generate the Keys and Sign the Zone</h2>

<p>In this step we will generate a pair (private and public) of Zone Signing Keys (ZSK) and Key Signing Keys (KSK) for each domain. <em>The commands in the section should be executed on the master server unless otherwise specified.</em></p>

<p>Change the current directory to NSD's zone directory:</p>
<pre class="code-pre "><code langs="">cd /etc/nsd/zones
</code></pre>
<p>The <span class="highlight">ldns-keygen</span> command generates key files and prints their names in the format <code>K<domain>+<algorithm>+<key-id></code>. Instead of noting down this name we will assign it to variable so that it can be easily referenced later.</p>

<p>Generate the ZSK in the <span class="highlight">RSASHA1-NSEC3-SHA1</span> algorithm:</p>
<pre class="code-pre "><code langs="">export ZSK=`ldns-keygen -a RSASHA1-NSEC3-SHA1 -b 1024 <span class="highlight">example.com</span>`
</code></pre>
<p>Next generate a KSK by adding the <span class="highlight">-k</span> option to the same command:</p>
<pre class="code-pre "><code langs="">export KSK=`ldns-keygen -k -a RSASHA1-NSEC3-SHA1 -b 2048 <span class="highlight">example.com</span>`
</code></pre>
<p>This directory will now have the following six additional files:</p>

<ul>
<li>2 private keys with a <span class="highlight">.private</span> extension.</li>
<li>2 public keys with a <span class="highlight">.key</span> extension.</li>
<li>2 DS records with a <span class="highlight">.ds</span> extension.</li>
</ul>

<p>In <strong>Step Three</strong> we will be generating DS records of a different <strong>digest type</strong>, so, to avoid confusion, delete these DS record files.</p>
<pre class="code-pre "><code langs="">rm $ZSK.ds $KSK.ds
</code></pre>
<p>Repeat the <span class="highlight">ldns-keygen</span> commands for the <strong>foobar.org</strong> domain:</p>
<pre class="code-pre "><code langs="">export ZSK2=`ldns-keygen -a RSASHA1-NSEC3-SHA1 -b 1024 <span class="highlight">foobar.org</span>`
export KSK2=`ldns-keygen -k -a RSASHA1-NSEC3-SHA1 -b 2048 <span class="highlight">foobar.org</span>`
rm $ZSK2.ds $KSK2.ds
</code></pre>
<p>The <span class="highlight">ldns-signzone</span> command is used to sign the DNS zone. The <span class="highlight">-s</span> option of this command takes in a <em>salt</em> value. We generate random charaters, compute a SHA1 hash, and pass this value as the salt.</p>
<pre class="code-pre "><code langs="">ldns-signzone -n -p -s $(head -n 1000 /dev/random | sha1sum | cut -b 1-16) <span class="highlight">example.com</span>.zone $ZSK $KSK
</code></pre>
<p>A new file named <strong>example.com.zone.signed</strong> is created.</p>

<p>Execute the <span class="highlight">ldns-signzone</span> command for the <strong>foobar.org</strong> domain:</p>
<pre class="code-pre "><code langs="">ldns-signzone -n -p -s $(head -n 1000 /dev/random | sha1sum | cut -b 1-16) <span class="highlight">foobar.org</span>.zone $ZSK2 $KSK2
</code></pre>
<p>NSD has to be configured to use the <span class="highlight">.signed</span> zone files. Edit the configuration file:</p>
<pre class="code-pre "><code langs="">nano /etc/nsd/nsd.conf
</code></pre>
<p>Modify the <span class="highlight">zonefile:</span> option under the <span class="highlight">zone:</span> section for both the domains.</p>
<pre class="code-pre "><code langs="">zone:
    name: example.com
    zonefile: example.com.zone<span class="highlight">.signed</span>
    notify: 2.2.2.2 mykey
    provide-xfr: 2.2.2.2 mykey
zone:
    name: foobar.org
    zonefile: foobar.org.zone<span class="highlight">.signed</span>
    notify: 2.2.2.2 mykey
    provide-xfr: 2.2.2.2 mykey
</code></pre>
<p>To apply the changes and reload the zone file execute the following commands:</p>
<pre class="code-pre "><code langs="">nsd-control reconfig
nsd-control reload <span class="highlight">example.com</span>
nsd-control reload <span class="highlight">foobar.org</span>
</code></pre>
<p>Check for DNSKEY records by doing a DNS query:</p>
<pre class="code-pre "><code langs="">dig DNSKEY <span class="highlight">example.com</span>. @localhost +multiline +norec
</code></pre>
<p>This should print the public keys of ZSK and KSK as follows:</p>
<pre class="code-pre "><code langs="">; <<>> DiG 9.9.5-3-Ubuntu <<>> DNSKEY example.com. @localhost +norec +multiline
;; global options: +cmd
;; Got answer:
;; ->>HEADER<<- opcode: QUERY, status: NOERROR, id: 14231
;; flags: qr aa; QUERY: 1, ANSWER: 2, AUTHORITY: 0, ADDITIONAL: 1

;; OPT PSEUDOSECTION:
; EDNS: version: 0, flags:; udp: 4096
;; QUESTION SECTION:
;example.com.                IN DNSKEY

;; ANSWER SECTION:
example.com.            1800 IN DNSKEY 256 3 7 (
                                AwEAAbUfMzOJWWWniRSwDb2/2Q6bVpVoEPltPj0h5Qu6
                                hzBdYA4HJYlVXTJ6veNENI/5lV1y84Dhc47j4VAoA66F
                                j7xuTTZjzcuu0KAkQg8Jr2uCmmOuI/rZR7sWZMooHFZ1
                                JPPJZak8HKSNGvHXlMJiz9JPOA3ebJ/liG6lCGJshPah
                                ) ; ZSK; alg = NSEC3RSASHA1; key id = 2870
example.com.            1800 IN DNSKEY 257 3 7 (
                                AwEAAeMDpaVQJixHg1deUDBRRwVldJadgyRZPlieSoVf
                                ps3tYPvTD0nVBOQxenf+m4N/ALpnC5TH4GpxZLYS9IFc
                                rujudQrqA0UuTXBvIWP+XvuJ1yoyZCxO9PHV+GsefjI7
                                kvnmBD1V9UJlGVlHlB3YXHa3f/J5E0RujMnE4a19KG7b
                                HkYebK/2zjzhqXan9442VAG6jhw0lUUJZrCpZjMDEi9n
                                LhJOUSymxglQv1BftALmYnYcuHId9NCwZbvZMb7bS239
                                bm6ONjwqSHqW2slNhBnDVnng2tDfNwjR+eDz5oUbtw4b
                                LMtVACx1WzJEKbIN4rHY7aRe7Ao+4jvSJ8ozVrM=
                                ) ; KSK; alg = NSEC3RSASHA1; key id = 17385

;; Query time: 5 msec
;; SERVER: 127.0.0.1#53(127.0.0.1)
;; WHEN: Thu Sep 04 01:37:18 IST 2014
;; MSG SIZE  rcvd: 467
</code></pre>
<p>Repeat the <span class="highlight">dig</span> command for the second domain and verify the response:</p>
<pre class="code-pre "><code langs="">dig DNSKEY <span class="highlight">foobar.org</span>. @localhost +multiline +norec
</code></pre>
<p>The master server now provides <strong>signed</strong> DNS responses.</p>

<h3 id="slave">Slave</h3>

<p>This zone has to be transfered to the slave server now. Log in to the slave server and force a transfer of both the zones.</p>
<pre class="code-pre "><code langs="">nsd-control force_transfer <span class="highlight">example.com</span>
nsd-control force_transfer <span class="highlight">foobar.org</span>
</code></pre>
<p>Query for the DNSKEY records on this server:</p>
<pre class="code-pre "><code langs="">dig DNSKEY <span class="highlight">example.com</span>. @localhost +multiline +norec
</code></pre>
<p>This should return the same DNSKEY we saw on the master server. Both the DNS servers have been configured to provide <strong>signed DNS replies</strong>.</p>

<h2 id="step-three-—-generate-ds-records">Step Three — Generate DS Records</h2>

<p>In this step we will generate two DS records which, in the next step, you will enter in the domain registar's control panel. The DS records will be of the following specification:</p>

<table class="pure-table"><thead>
<tr>
<th></th>
<th>Algorithm</th>
<th>Digest Type</th>
</tr>
</thead><tbody>
<tr>
<td>DS record 1</td>
<td>RSASHA1-NSEC3-SHA1</td>
<td>SHA1</td>
</tr>
<tr>
<td>DS record 2</td>
<td>RSASHA1-NSEC3-SHA1</td>
<td>SHA256</td>
</tr>
</tbody></table>

<p><strong>The following commands are to be executed on the master server.</strong></p>

<p>The <span class="highlight">ldns-key2ds</span> command generates DS records from the signed zone file. Switch to the zone files directory and execute the commands:</p>
<pre class="code-pre "><code langs="">cd /etc/nsd/zones
ldns-key2ds -n -1 <span class="highlight">example.com</span>.zone.signed && ldns-key2ds -n -2 <span class="highlight">example.com</span>.zone.signed
</code></pre>
<p>The <span class="highlight">-1</span> option uses SHA1 as the hash function while <span class="highlight">-2</span> uses SHA256 for the same. The <span class="highlight">-n</span> option writes the result DS record to stdout instead of a file.</p>

<p>This returns two lines of output:</p>
<pre class="code-pre "><code langs="">example.com. 1800    IN      DS      17385 7 1 c1b9f7f1425bc44976dc19165e48c60032e7820d
example.com. 1800    IN      DS      17385 7 2 98216f4d66d24dbb752c46523a747a97bbad49d5846bbaa6256b6950b4a40995
</code></pre>
<p>The following table shows each field of these DS records:</p>

<table class="pure-table"><thead>
<tr>
<th></th>
<th>Key tag</th>
<th>Algorithm</th>
<th>Digest type</th>
<th>Digest</th>
</tr>
</thead><tbody>
<tr>
<td>DS record #1</td>
<td>17385</td>
<td>7</td>
<td>1</td>
<td>c1b9f7f1[...]</td>
</tr>
<tr>
<td>DS record #2</td>
<td>17385</td>
<td>7</td>
<td>2</td>
<td>98216f4d[..]</td>
</tr>
</tbody></table>

<p>Generate DS records for the <strong>foobar.org</strong>:</p>
<pre class="code-pre "><code langs="">cd /etc/nsd/zones
ldns-key2ds -n -1 <span class="highlight">foobar.org</span>.zone.signed && ldns-key2ds -n -2 <span class="highlight">foobar.org</span>.zone.signed
</code></pre>
<p>Note down all the pieces of all four DS records (two per domain) as shown in the table above. We will be needing them in the next step.</p>

<h2 id="step-four-—-configure-ds-records-with-the-registrar">Step Four — Configure DS Records with the Registrar</h2>

<p>In this section we will add the DS records in the domain registrar's control panel. This publishes the DS records to the nameservers of the Top Level Domain (TLD). This step will use GoDaddy's control panel as an example.</p>

<p>Log in to GoDaddy and choose your domain name.</p>

<p><strong>First time nameserver setup only:</strong></p>

<p>The <strong>Host Names</strong> section needs to be done once to set up the nameservers for the first time. If your nameserver domain is something different like <strong>my-soa.com</strong>, you should do this step for the nameserver domain <strong>only</strong>.</p>

<p>Click <strong>Manage</strong> in the <strong>Host Names</strong> section.</p>

<p><img src="https://assets.digitalocean.com/articles/dnssec_nsdnameserver/3.png" alt="GoDaddy Host Names" /></p>

<p>Some registrars may refer to this as "Child nameservers." Click <strong>Add Hostname</strong> and create a hostname <strong>master.<span class="highlight">example.com</span></strong> pointing to the IP of the first Droplet.</p>

<p><img src="https://assets.digitalocean.com/articles/dnssec_nsdnameserver/4.png" alt="Adding Hostnames" /></p>

<p>Click <strong>Add</strong>. Repeat this step once more and create a hostname <strong>slave.<span class="highlight">example.com</span></strong> pointing to the IP of the second Droplet.</p>

<p><strong>All domains:</strong></p>

<p>These two hostnames have to be set as nameservers for this domain. Click <strong>Manage</strong> in the <strong>Nameservers</strong> section and add both of them.</p>

<p><img src="https://assets.digitalocean.com/articles/dnssec_nsdnameserver/5.png" alt="Adding Nameservers" /></p>

<p>Click <strong>Manage</strong> in the <strong>DS records</strong> section.</p>

<p><img src="https://assets.digitalocean.com/articles/dnssec_nsdnameserver/6.png" alt="GoDaddy manage DS records" /></p>

<p>Fill in the details in the appropriate fields. Reference the chart in the previous step if necessary.</p>

<p><img src="https://assets.digitalocean.com/articles/dnssec_nsdnameserver/7.png" alt="Enter the Key tag, Algorithm, Digest type, and Digest for the first DS record." /></p>

<p><img src="https://assets.digitalocean.com/articles/dnssec_nsdnameserver/8.png" alt="Enter the Key tag, Algorithm, Digest type, and Digest for the second DS record." /></p>

<p>Save both the records.</p>

<p><img src="https://assets.digitalocean.com/articles/dnssec_nsdnameserver/9.png" alt="" /></p>

<p>After a few minutes, query for DS records.</p>
<pre class="code-pre "><code langs="">dig DS <span class="highlight">example.com</span>. +trace +short | egrep '^DS'
</code></pre>
<p>The output should contain both the DS records.</p>
<pre class="code-pre "><code langs="">DS 17385 7 2 98216F4D66D24DBB752C46523A747A97BBAD49D5846BBAA6256B6950 B4A40995 from server 192.55.83.30 in 1 ms.
DS 17385 7 1 C1B9F7F1425BC44976DC19165E48C60032E7820D from server 192.55.83.30 in 1 ms.
</code></pre>
<p>When doing these steps for the second domain, make sure you set the nameservers to the appropriate nameserver domain.</p>

<p><img src="https://assets.digitalocean.com/articles/dnssec_nsdnameserver/10.png" alt="Second domain nameservers" /></p>

<p>No hostnames have to be created for this domain.</p>

<h2 id="step-five-—-verify-dnssec-operation">Step Five — Verify DNSSEC Operation</h2>

<p>DNSSEC can be verified at the following sites:</p>

<ul>
<li><a href="http://dnssec-debugger.verisignlabs.com/">http://dnssec-debugger.verisignlabs.com/</a></li>
<li><a href="http://dnsviz.net/">http://dnsviz.net/</a></li>
</ul>

<p>A successful test from the first website displays the following result:</p>

<p><img src="https://assets.digitalocean.com/articles/dnssec_nsdnameserver/11.png" alt="DNSSEC test" /></p>

<p>Take note of the marked lines. In plain terms they read:</p>

<ol>
<li>DS record #2 (digest type SHA256) verifies KSK (key id 17385)</li>
<li>KSK (key id 17385) verifies the other DNSKEY (ZSK)</li>
<li>ZSK (key id 2870) verifies the A record's signature</li>
</ol>

<p>Both the master and the slave servers now provide DNSSEC responses.</p>

<p>You should also be able to view both domains in your web browser. They should point to the default Apache/Ubuntu page on the test web server we set up on 3.3.3.3, or whatever web hosts you specified in the domains' <strong>@</strong> entries.</p>

<h2 id="modifying-zone-records">Modifying Zone Records</h2>

<p>To modify a zone record the non-signed file (<strong><span class="highlight">example.com</span>.zone</strong>) must be edited. Once modified, the SOA serial number must be incremented, and the zone must be signed again for the changes to take effect.</p>

<p>The SOA serial is in the following format.</p>
<pre class="code-pre "><code langs="">YYYYMMDD<span class="highlight">nn</span>
</code></pre>
<p>When making changes to the zone files, set it to the current date. So when making the first change on 22nd September 2014, the serial would be:</p>
<pre class="code-pre "><code langs="">20140922<span class="highlight">01</span>
</code></pre>
<p>The first two digits should be incremented when making subsequent changes on the same day. If you forget to increment the SOA serial, changes made to the zone file will not be transferred to the slave server.</p>

<p><strong>Note: Making changes to the <code>.signed</code> file directly will invalidate the signature and cause validation failure.</strong></p>

<p>Instead of entering long commands each time to sign the zone we will create a shell script. Create a file <strong>on the master DNS server</strong> and edit it.</p>
<pre class="code-pre "><code langs="">nano /usr/local/bin/dnszonesigner
</code></pre>
<p>Paste the following code:</p>
<pre class="code-pre "><code class="code-highlight language-bash">#!/bin/bash
PDIR=`pwd`
ZONEDIR="/etc/nsd/zones" #location of your zone files
DOMAIN=$1
cd $ZONEDIR
KSK=$(basename $(grep -r "`grep '(ksk)' $DOMAIN.zone.signed | cut -f3-10`" K$DOMAIN.*.key | cut -d':' -f1) .key)
ZSK=$(basename $(grep -r "`grep '(zsk)' $DOMAIN.zone.signed | cut -f3-10`" K$DOMAIN.*.key | cut -d':' -f1) .key)
/usr/bin/ldns-signzone -n -p -s $(head -n 1000 /dev/random | sha1sum | cut -b 1-16) -f $ZONEDIR/$DOMAIN.zone.signed $DOMAIN.zone $ZSK $KSK
/usr/sbin/nsd-control reload $DOMAIN
/usr/sbin/nsd-control notify $DOMAIN
cd $PDIR
</code></pre>
<p>You should recognize most of these lines from earlier in the tutorial where we executed them manually.</p>

<p>Make this file executable:</p>
<pre class="code-pre "><code langs="">chmod +x /usr/local/bin/dnszonesigner
</code></pre>
<p>Now after adding, removing or editing DNS records make sure to <strong>increment the SOA serial</strong> and execute this script.</p>
<pre class="code-pre "><code langs="">dnszonesigner <span class="highlight">example.com</span>
</code></pre>
<p>This shell script works from any directory as we placed it in a directory defined in the <span class="highlight">$PATH</span> variable.</p>

<h3 id="additional-reading">Additional Reading</h3>

<ul>
<li><a href="https://indiareads/community/tutorials/how-to-setup-dnssec-on-an-authoritative-bind-dns-server--2">Configuring DNSSEC on BIND DNS Server</a></li>
<li><a href="http://technet.microsoft.com/en-in/library/jj200221.aspx">Overview of DNSSEC</a> - Microsoft</li>
</ul>

<p><em>Additional copy by Sharon Campbell</em></p>

    