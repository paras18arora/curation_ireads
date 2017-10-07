<link rel='stylesheet' href='digital_ocean.css'><br> <br> 
      <h3 id="about-dnssec">About DNSSEC</h3>

<p>We all know that DNS is a protocol which resolves domain names to IP addresses, but how do we know the authenticity of the returned IP address? It is possible for an attacker to tamper a DNS response or <a href="http://en.wikipedia.org/wiki/DNS_cahe_poisoning">poison the DNS cache</a> and take users to a malicious site with the legitimate domain name in the address bar. DNS Security Extensions (DNSSEC) is a specification which aims at maintaining the data integrity of DNS responses. DNSSEC signs all the DNS resource records (A, MX, CNAME etc.) of a zone using PKI (Public Key Infrastructure). Now DNSSEC enabled DNS resolvers (like Google Public DNS) can verify the authenticity of a DNS reply (containing an IP address) using the public DNSKEY record.</p>

<h2 id="dnssec-resource-records">DNSSEC Resource Records</h2>

<p>A Resource Record (RR) contains a specific information about the domain. Some common ones are A record which contains the IP address of the domain, AAAA record which holds the IPv6 information, and MX record which has mail servers of a domain. A complete list of DNS RRs can be found <a href="http://en.wikipedia.org/wiki/List_of_DNS_record_types">here</a>.</p>

<p>Likewise DNSSEC too requires several RRs.</p>

<ul>
<li><strong>DNSKEY</strong> Holds the public key which resolvers use to verify.</li>
<li><strong>RRSIG</strong> Exists for each RR and contains the digital signature of a record.</li>
<li><strong>DS</strong> - Delegation Signer – this record exists in the TLD's nameservers. So if example.com was your domain name, the TLD is "com" and its nameservers are <code>a.gtld-servers.net.</code>, <code>b.gtld-servers.net.</code> up to <code>m.gtld-servers.net.</code>. The purpose of this record is to verify the authenticity of the DNSKEY itself.</li>
</ul>

<h2 id="setup-environment">Setup Environment</h2>

<p><strong>Domain Name:</strong> example.com </p>

<p>I used a real .COM domain to do this, but have replaced it with <em>example.com</em> for this article.</p>

<p><strong>Master Nameserver:</strong> <br />
<strong>IP Address:</strong> 1.1.1.1 <br />
<strong>Hostname:</strong> master.example.com <br />
<strong>OS:</strong> Debian 7</p>

<p><strong>Slave Nameserver:</strong> <br />
<strong>IP Address:</strong> 2.2.2.2 <br />
<strong>Hostname:</strong> slave.example.com <br />
<strong>OS:</strong> CentOS</p>

<h3 id="file-locations-and-names">File locations and names</h3>

<p>The names and locations of configuration and zone files of BIND different according to the Linux distribution used.</p>

<h4 id="debian-ubuntu">Debian/Ubuntu</h4>

<p>Service name: <br />
<strong>bind9</strong> <br />
Main configuration file: <br />
<code>/etc/bind/named.conf.options</code> <br />
Zone names file: <br />
<code>/etc/bind/named.conf.local</code> <br />
Default zone file location: <br />
<code>/var/cache/bind/</code></p>

<h4 id="centos-fedora">CentOS/Fedora</h4>

<p>Service name: <br />
<strong>named</strong> <br />
Main configuration and zone names file: <br />
<code>/etc/named.conf</code> <br />
Default zone file location: <br />
<code>/var/named/</code></p>

<p>These may change if you're using <code>bind-chroot</code>. For this tutorial, I've used Debian for the Master NS and CentOS for the Slave NS, so change it according to your distribution.</p>

<h2 id="dnssec-master-configuration">DNSSEC Master Configuration</h2>

<p>Enable DNSSEC by adding the following configuration directives inside <code>options{ }</code></p>

<p><code>nano /etc/bind/named.conf.options</code></p>
<pre class="code-pre "><code langs="">dnssec-enable yes;
dnssec-validation yes;
dnssec-lookaside auto;
</code></pre>
<p>It is possible that these are already added in some distributions. Navigate to the location of your zone files.</p>
<pre class="code-pre "><code langs="">cd /var/cache/bind
</code></pre>
<p>Create a Zone Signing Key(ZSK) with the following command.</p>
<pre class="code-pre "><code langs="">dnssec-keygen -a NSEC3RSASHA1 -b 2048 -n ZONE example.com
</code></pre>
<p>If you have installed <a href="https://indiareads/community/articles/how-to-setup-additional-entropy-for-cloud-servers-using-haveged"><strong>haveged</strong></a>, it'll take only a few seconds for this key to be generated; otherwise it'll take a very long time. Sample output.</p>
<pre class="code-pre "><code langs="">root@master:/var/cache/bind# dnssec-keygen -a NSEC3RSASHA1 -b 2048 -n ZONE example.com
Generating key pair..................+++ .............+++
Kexample.com.+007+40400
</code></pre>
<p>Create a Key Signing Key(KSK) with the following command.</p>
<pre class="code-pre "><code langs="">dnssec-keygen -f KSK -a NSEC3RSASHA1 -b 4096 -n ZONE example.com
</code></pre>
<p>Sample output.</p>
<pre class="code-pre "><code langs="">root@master:/var/cache/bind# dnssec-keygen -f KSK -a NSEC3RSASHA1 -b 4096 -n ZONE example.com
Generating key pair......................++ .............................................................................................................................................................................................................++
Kexample.com.+007+62910
</code></pre>
<p>The directory will now have 4 keys - private/public pairs of ZSK and KSK. We have to add the public keys which contain the <strong>DNSKEY</strong> record to the zone file. The following <code>for</code> loop will do this.</p>
<pre class="code-pre "><code langs="">for key in `ls Kexample.com*.key`
do
echo "\$INCLUDE $key">> example.com.zone
done
</code></pre>
<p>Sign the zone with the <code>dnssec-signzone</code> command.</p>
<pre class="code-pre "><code langs="">dnssec-signzone -3 <salt> -A -N INCREMENT -o <zonename> -t <zonefilename>
</code></pre>
<p>Replace salt with something random. Here is an example with the output.</p>
<pre class="code-pre "><code langs="">root@master:/var/cache/bind# dnssec-signzone -A -3 $(head -c 1000 /dev/random | sha1sum | cut -b 1-16) -N INCREMENT -o example.com -t example.com.zone
Verifying the zone using the following algorithms: NSEC3RSASHA1.
Zone signing complete:
Algorithm: NSEC3RSASHA1: KSKs: 1 active, 0 stand-by, 0 revoked
                        ZSKs: 1 active, 0 stand-by, 0 revoked
example.com.zone.signed
Signatures generated:                       14
Signatures retained:                         0
Signatures dropped:                          0
Signatures successfully verified:            0
Signatures unsuccessfully verified:          0
Signing time in seconds:                 0.046
Signatures per second:                 298.310
Runtime in seconds:                      0.056
</code></pre>
<p>A 16 character string must be entered as the "salt". The following command</p>
<pre class="code-pre "><code langs="">head -c 1000 /dev/random | sha1sum | cut -b 1-16
</code></pre>
<p>outputs a random string of 16 characters which will be used as the salt.</p>

<p>This creates a new file named <code>example.com.zone.signed</code> which contains <strong>RRSIG</strong> records for each DNS record. We have to tell BIND to load this "signed" zone.</p>
<pre class="code-pre "><code langs="">nano /etc/bind/named.conf.local
</code></pre>
<p>Change the <code>file</code> option inside the <code>zone { }</code> section.</p>
<pre class="code-pre "><code langs="">zone "example.com" IN {
    type master;
    file "example.com.zone.signed";
    allow-transfer { 2.2.2.2; };
    allow-update { none; };
};
</code></pre>
<p>Save this file and reload bind</p>
<pre class="code-pre "><code langs="">service bind9 reload
</code></pre>
<p>Check if for the DNSKEY record using <code>dig</code> on the same server.</p>
<pre class="code-pre "><code langs="">dig DNSKEY example.com. @localhost +multiline
</code></pre>
<p>Sample output</p>
<pre class="code-pre "><code langs="">root@master:/var/cache/bind# dig DNSKEY example.com. @localhost +multiline
;; Truncated, retrying in TCP mode.

; <<>> DiG 9.8.4-rpz2+rl005.12-P1 <<>> DNSKEY example.com. @localhost +multiline
;; global options: +cmd
;; Got answer:
;; ->>HEADER<<- opcode: QUERY, status: NOERROR, id: 43986
;; flags: qr aa rd; QUERY: 1, ANSWER: 2, AUTHORITY: 0, ADDITIONAL: 0
;; WARNING: recursion requested but not available

;; QUESTION SECTION:
;example.com.       IN DNSKEY

;; ANSWER SECTION:
example.com.        86400 IN DNSKEY   256 3 7 (
                AwEAActPMYurNEyhUgHjPctbLCI1VuSj3xcjI8QFTpdM
                8k3cYrfwB/WlNKjnnjt98nPmHv6frnuvs2LKIvvGzz++
                kVwVc8uMLVyLOxVeKhygDurFQpLNNdPumuc2MMRvV9me
                fPrdKWtEEtOxq6Pce3DW2qRLjyE1n1oEq44gixn6hjgo
                sG2FzV4fTQdxdYCzlYjsaZwy0Kww4HpIaozGNjoDQVI/
                f3JtLpE1MYEb9DiUVMjkwVR5yH2UhJwZH6VVvDOZg6u6
                YPOSUDVvyofCGcICLqUOG+qITYVucyIWgZtHZUb49dpG
                aJTAdVKlOTbYV9sbmHNuMuGt+1/rc+StsjTPTHU=
                ) ; key id = 40400
example.com.        86400 IN DNSKEY   257 3 7 (
                AwEAAa2BE0dAvMs0pe2f+D6HaCyiFSHw47BA82YGs7Sj
                qSqH3MprNra9/4S0aV6SSqHM3iYZt5NRQNTNTRzkE18e
                3j9AGV8JA+xbEow74n0eu33phoxq7rOpd/N1GpCrxUsG
                kK4PDkm+R0hhfufe1ZOSoiZUV7y8OVGFB+cmaVb7sYqB
                RxeWPi1Z6Fj1/5oKwB6Zqbs7s7pmxl/GcjTvdQkMFtOQ
                AFGqaaSxVrisjq7H3nUj4hJIJ+SStZ59qfW3rO7+Eqgo
                1aDYaz+jFHZ+nTc/os4Z51eMWsZPYRnPRJG2EjJmkBrJ
                huZ9x0qnjEjUPAcUgMVqTo3hkRv0D24I10LAVQLETuw/
                QOuWMG1VjybzLbXi5YScwcBDAgtEpsQA9o7u6VC00DGh
                +2+4RmgrQ7mQ5A9MwhglVPaNXKuI6sEGlWripgTwm425
                JFv2tGHROS55Hxx06A416MtxBpSEaPMYUs6jSIyf9cjB
                BMV24OjkCxdz29zi+OyUyHwirW51BFSaOQuzaRiOsovM
                NSEgKWLwzwsQ5cVJBEMw89c2V0sHa4yuI5rr79msRgZT
                KCD7wa1Hyp7s/r+ylHhjpqrZwViOPU7tAGZ3IkkJ2SMI
                e/h+FGiwXXhr769EHbVE/PqvdbpcsgsDqFu0K2oqY70u
                SxnsLB8uVKYlzjG+UIoQzefBluQl
                ) ; key id = 62910

;; Query time: 0 msec
;; SERVER: 127.0.0.1#53(127.0.0.1)
;; WHEN: Wed Nov 27 18:18:30 2013
;; MSG SIZE  rcvd: 839
</code></pre>
<p>Check for the presence of RRSIG records.</p>
<pre class="code-pre "><code langs="">dig A example.com. @localhost +noadditional +dnssec +multiline
; <<>> DiG 9.8.4-rpz2+rl005.12-P1 <<>> A example.com. @localhost +noadditional +dnssec +multiline
;; global options: +cmd
;; Got answer:
;; ->>HEADER<<- opcode: QUERY, status: NOERROR, id: 32902
;; flags: qr aa rd; QUERY: 1, ANSWER: 2, AUTHORITY: 3, ADDITIONAL: 5
;; WARNING: recursion requested but not available

;; OPT PSEUDOSECTION:
; EDNS: version: 0, flags: do; udp: 4096
;; QUESTION SECTION:
;example.com.         IN A

;; ANSWER SECTION:
example.com.          86400 IN A 93.184.216.119
example.com.          86400 IN RRSIG A 7 2 86400 20131227171405 (
                            20131127171405 40400 example.com.
                            JCoL8L7As1a8CXnx1W62O94eQl6zvVQ3prtNK7BWIW9O
                            lir/4V+a6c+0tbt4z4lhgmb0sb+qdvqRnlI7CydaSZDb
                            hlrJA93fHqFqNXw084YD1gWC+M8m3ewbobiZgBUh5W66
                            1hsVjWZGvvQL+HmobuSvsF8WBMAFgJgYLg0YzBAvwHIk
                            886be6vbNeAltvPl9I+tjllXkMK5dReMH40ulgKo+Cwb
                            xNQ+RfHhCQIwKgyvL1JGuHB125rdEQEVnMy26bDcC9R+
                            qJNYj751CEUZxEEGI9cZkD44oHwDvPgF16hpNZGUdo8P
                            GtuH4JwP3hDIpNtGTsQrFWYWL5pUuuQRwA== )

;; AUTHORITY SECTION:
example.com.          86400 IN NS master.example.com.
example.com.          86400 IN NS slave.example.com.
example.com.          86400 IN RRSIG NS 7 2 86400 20131227171405 (
                            20131127171405 40400 example.com.
                            hEGzNvKnc3sXkiQKo9/+ylU5WSFWudbUc3PAZvFMjyRA
                            j7dzcVwM5oArK5eXJ8/77CxL3rfwGvi4LJzPQjw2xvDI
                            oVKei2GJNYekU38XUwzSMrA9hnkremX/KoT4Wd0K1NPy
                            giaBgyyGR+PT3jIP95Ud6J0YS3+zg60Zmr9iQPBifH3p
                            QrvvY3OjXWYL1FKBK9+rJcwzlsSslbmj8ndL1OBKPEX3
                            psSwneMAE4PqSgbcWtGlzySdmJLKqbI1oB+d3I3bVWRJ
                            4F6CpIRRCb53pqLvxWQw/NXyVefNTX8CwOb/uanCCMH8
                            wTYkCS3APl/hu20Y4R5f6xyt8JZx3zkZEQ== )

;; Query time: 0 msec
;; SERVER: 127.0.0.1#53(127.0.0.1)
;; WHEN: Thu Nov 28 00:01:06 2013
;; MSG SIZE  rcvd: 1335
</code></pre>
<p>The configuration of the master server is complete.</p>

<h2 id="dnssec-slave-configuration">DNSSEC Slave Configuration</h2>

<p>The <a href="http://jesin.tk/setup-secondary-slave-dns-servers-free/">slave servers</a> only require DNSSEC to be enabled and the zone file location to be changed. Edit the main configuration file of BIND.</p>
<pre class="code-pre "><code langs="">nano /etc/named.conf
</code></pre>
<p>Place these lines inside the <code>options { }</code> section if they don't exist.</p>
<pre class="code-pre "><code langs="">dnssec-enable yes;
dnssec-validation yes;
dnssec-lookaside auto;
</code></pre>
<p>Edit the <code>file</code> option inside the <code>zone { }</code> section.</p>
<pre class="code-pre "><code langs="">zone "example.com" IN {
    type slave;
    file "example.com.zone.signed";
    masters { 1.1.1.1; };
    allow-notify { 1.1.1.1; };
};
</code></pre>
<p>Reload the BIND service.</p>
<pre class="code-pre "><code langs="">service named reload
</code></pre>
<p>Check if there is a new <code>.signed</code> zone file.</p>
<pre class="code-pre "><code langs="">[root@slave ~]# ls -l /var/named/slaves/
total 16
-rw-r--r-- 1 named named  472 Nov 27 17:25 example.com.zone
-rw-r--r-- 1 named named 9180 Nov 27 18:29 example.com.zone.signed
</code></pre>
<p>Voila! That's it. Just to make sure things are working as they should ,query the DNSKEY using <code>dig</code> as mentioned in the previous section.</p>

<h2 id="configure-ds-records-with-the-registrar">Configure DS records with the registrar</h2>

<p>When we ran the <code>dnssec-signzone</code> command apart from the <code>.signed</code> zone file, a file named <code>dsset-example.com</code> was also created, this contains the DS records.</p>
<pre class="code-pre "><code langs="">root@master:/var/cache/bind# cat dsset-example.com.
example.com.        IN DS 62910 7 1 1D6AC75083F3CEC31861993E325E0EEC7E97D1DD
example.com.        IN DS 62910 7 2 198303E265A856DE8FE6330EDB5AA76F3537C10783151AEF3577859F FFC3F59D
</code></pre>
<p>These have to be entered in your domain registrar's control panel. The screenshots below will illustrate the steps on GoDaddy.</p>

<p>Login to your domain registrar's control panel, choose your domain, and select the option to manage DS records. GoDaddy's control panel looks like this.</p>

<p><img src="https://assets.digitalocean.com/articles/DNSSEC_DNSSERVER/2.png" alt="GoDaddy's Domain control panel" /></p>

<p>Here is a breakup of the data in the <code>dsset-example.com.</code> file.</p>

<h3 id="ds-record-1">DS record 1:</h3>

<p><strong>Key tag:</strong> 62910 <br />
<strong>Algorithm:</strong> 7 <br />
<strong>Digest Type:</strong> 1 <br />
<strong>Digest:</strong> 1D6AC75083F3CEC31861993E325E0EEC7E97D1DD</p>

<p><img src="https://assets.digitalocean.com/articles/DNSSEC_DNSSERVER/3.png" alt="DS record 1" /></p>

<h3 id="ds-record-2">DS record 2:</h3>

<p><strong>Key tag:</strong> 62910 <br />
<strong>Algorithm:</strong> 7 <br />
<strong>Digest Type:</strong> 2 <br />
<strong>Digest:</strong> 198303E265A856DE8FE6330EDB5AA76F3537C10783151AEF3577859FFFC3F59D</p>

<p><img src="https://assets.digitalocean.com/articles/DNSSEC_DNSSERVER/4.png" alt="DS record 2" /></p>

<p>The second DS record in the <code>dsset-example.com.</code> file had a space in the digest, but when entering it in the form you should omit it. Click <em>Next</em>, click <em>Finish</em> and <em>Save</em> the records.</p>

<p><img src="https://assets.digitalocean.com/articles/DNSSEC_DNSSERVER/5.png" alt="" /></p>

<p>It'll take a few minutes for these changes to be saved. To check if the DS records have been created query the nameservers of your TLD. Instead of finding the TLD's nameservers we can do a <code>dig +trace</code> which is much simpler.</p>
<pre class="code-pre "><code langs="">root@master:~# dig +trace +noadditional DS example.com. @8.8.8.8 | grep DS
; <<>> DiG 9.8.2rc1-RedHat-9.8.2-0.17.rc1.el6_4.6 <<>> +trace +noadditional DS example.com. @8.8.8.8
example.com.          86400   IN      DS      62910 7 2 198303E265A856DE8FE6330EDB5AA76F3537C10783151AEF3577859F FFC3F59D
example.com.          86400   IN      DS      62910 7 1 1D6AC75083F3CEC31861993E325E0EEC7E97D1DD
</code></pre>
<p>Once this is confirmed, we can check if DNSSEC is working fine using any of the following online services.</p>

<ul>
<li><p><a href="http://dnssec-debugger.verisignlabs.com">http://dnssec-debugger.verisignlabs.com</a></p></li>
<li><p><a href="http://dnsviz.net/">http://dnsviz.net/</a></p></li>
</ul>

<p>The first tool is a simple one, while the second gives you a visual representation of things. Here is a screenshot from the first tool.</p>

<p><img src="https://assets.digitalocean.com/articles/DNSSEC_DNSSERVER/6.png" alt="" /></p>

<p>Notice the lines I've marked. The first one mentions the <strong>Key tag</strong> value (62910) of the DS record while the second one <strong>key id</strong> (40400) of the DNSKEY record which holds the ZSK (Zone Signing Key).</p>

<h2 id="modifying-zone-records">Modifying Zone Records</h2>

<p>Each time you edit the zone by adding or removing records, it has to be signed to make it work. So we will create a script for this so that we don't have to type long commands every time.</p>
<pre class="code-pre "><code langs="">root@master# nano /usr/sbin/zonesigner.sh

#!/bin/sh
PDIR=`pwd`
ZONEDIR="/var/cache/bind" #location of your zone files
ZONE=$1
ZONEFILE=$2
DNSSERVICE="bind9" #On CentOS/Fedora replace this with "named"
cd $ZONEDIR
SERIAL=`/usr/sbin/named-checkzone $ZONE $ZONEFILE | egrep -ho '[0-9]{10}'`
sed -i 's/'$SERIAL'/'$(($SERIAL+1))'/' $ZONEFILE
/usr/sbin/dnssec-signzone -A -3 $(head -c 1000 /dev/random | sha1sum | cut -b 1-16) -N increment -o $1 -t $2
service $DNSSERVICE reload
cd $PDIR
</code></pre>
<p>Save the file and make it executable.</p>
<pre class="code-pre "><code langs="">root@master# chmod +x /usr/sbin/zonesigner.sh
</code></pre>
<p>Whenever you want to add or remove records, edit the <code>example.com.zone</code> and <strong>NOT the <code>.signed</code> file</strong>. This file also takes care of incrementing the serial value, so you needn't do it each time you edit the file.  After editing it run the script by passing the domain name and zone filename as parameters.</p>
<pre class="code-pre "><code langs="">root@master# zonesigner.sh example.com example.com.zone
</code></pre>
<p>You do not have to do anything on the slave nameserver as the incremented serial will ensure the zone if transferred and updated.</p>

<h2 id="securing-the-dnssec-setup-from-zone-walking">Securing the DNSSEC setup from Zone Walking</h2>

<p><a href="http://en.wikipedia.org/wiki/Domain_Name_System_Security_Extensions#Zone_enumeration_issue.2C_controversy.2C_and_NSEC3">Zone Walking</a> is a technique used to find all the Resource Records of a zone by querying the <strong>NSEC</strong> (Next-Secure) record. <strong>NSEC3</strong> was released which "hashed" this information using a salt. Recall the <code>dnssec-signzone</code> command in which we specified a <code>-3</code> option followed by another elaborate command to generate a random string. This is the salt which can be found using the following <code>dig</code> query.</p>
<pre class="code-pre "><code langs=""># dig NSEC3PARAM example.com. @master.example.com. +short
1 0 10 7CBAA916230368F2
</code></pre>
<p>All this makes zone walking difficult but not impossible. A determined hacker using <a href="http://en.wikipedia.org/wiki/Rainbow_table">rainbow tables</a> can break the hash, though it'll take a long time. To prevent this we can recompute this salt at regular intervals, which makes a hacker's attempt futile as there is a new salt before he/she can find the hash with the old salt. Create a cron job to do this for you using the <strong>zonesigner.sh</strong> script we created previously. If you run the cronjob as <code>root</code> you don't have to worry about file ownership. Or else make sure the user under whom you're placing the cron has <strong>write permission on the zone directory</strong> and <strong>read permission on the private keys</strong> (Kexample.com.*.private).</p>
<pre class="code-pre "><code langs="">root@master:~# crontab -e

0       0       */3     *       *       /usr/sbin/zonesigner.sh example.com example.com.zone
</code></pre>
<p>This will sign the zone every 3 days and as a result a new salt will be generated. You'll also receive an email containing the output of the <code>dnssec-signzone</code> command.     </p>

<p></p><div class="author">Submitted by: <a rel="author" href="http://jesin.tk/">Jesin A</a></div>                                     

    