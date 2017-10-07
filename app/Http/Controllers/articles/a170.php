<link rel='stylesheet' href='digital_ocean.css'><br> <br> 
      <h3 id="introduction">Introduction</h3>

<p>The frustration of getting falsely flagged as a spammer is not strange to most of the mail server admins. By excluding the possibility of a compromised server, a false flag is usually caused by one of the following:</p>

<ul>
<li>the server is an open mail relay</li>
<li>the sender's or server's IP address is blacklisted</li>
<li>the server does not have a Fully Qualified Domain Name (<a href="http://en.wikipedia.org/wiki/Fully_qualified_domain_name">FQDN</a>) and a PTR record</li>
<li>the Sender Policy Framework (<a href="http://www.openspf.org/">SPF</a>) DNS record is missing or it is misconfigured</li>
<li>the DomainKeys Identified Mail (<a href="http://www.dkim.org/">DKIM</a>) implementation is missing or it's not properly set up</li>
</ul>

<p>These are some of the basic properties that are being checked by the majority of proprietary and open source spam filters (including SpamAssassin). Passing these tests is extremely important for a well configured mail server.</p>

<p>This tutorial will focus on installing and configuring <a href="http://www.opendkim.org/">OpenDKIM</a>]: an open source implementation of the DKIM sender authentication system.</p>

<p>It is assumed that the reader knows how to access the server over SSH, Postfix and Dovecot is already installed and configured (<a href="https://indiareads/community/articles/how-to-set-up-a-postfix-e-mail-server-with-dovecot">tutorial</a>), the host name and the FQDN are set up (<a href="https://github.com/IndiaReads-User-Projects/Articles-and-Tutorials/blob/master/set_hostname_fqdn_on_ubuntu_centos.md">tutorial</a>, <a href="https://indiareads/community/articles/how-to-set-up-a-host-name-with-digitalocean">tutorial</a>) and the SPF record is in place (<a href="https://indiareads/community/articles/how-to-use-an-spf-record-to-prevent-spoofing-improve-e-mail-reliability">tutorial</a>).</p>

<h2 id="about-dkim">About DKIM</h2>

<p>DKIM is an Internet Standard that enables a person or organisation to associate a domain name with an email message.  This, in effect, serves as a method of claiming responsibility for a message. At its core, DKIM is powered by asymmetric cryptography. The sender's Mail Transfer Agent (MTA) signs every outgoing message with a private key. The recipient retrieves the public key from the sender's DNS records and verifies if the message body and some of the header fields were not altered since the message signing took place.</p>

<h2 id="install-opendkim">Install OpenDKIM</h2>

<p>Before starting the installation, a system update is recommended:</p>
<pre class="code-pre "><code langs="">sudo apt-get update
sudo apt-get dist-upgrade
</code></pre>
<p>Install OpenDKIM and it's dependencies:</p>
<pre class="code-pre "><code langs="">sudo apt-get install opendkim opendkim-tools
</code></pre>
<p>Additional packages will be listed as dependencies, type <code>yes</code> and press <code>Enter</code> to continue.</p>

<h2 id="configure-opendkim">Configure OpenDKIM</h2>

<p>A couple of files must be created and edited in order to configure OpenDKIM.</p>

<p><strong>Nano</strong> will be used as an editor because it's installed by default on IndiaReads droplets and it's simple to operate:</p>

<ul>
<li>navigate with the arrow keys</li>
<li>exit without saving changes: press <code>CTRL + X</code> and then <code>N</code></li>
<li>exit and save changes: press <code>CTRL + X</code> and then <code>Y</code>, and finally press <code>Enter</code></li>
</ul>

<p><strong>Important: replace every instance of example.com with your own domain in all commands and configuration files.  Don't forget to save your files after editing.</strong></p>

<p>Let's start with the main configuration file:</p>
<pre class="code-pre "><code langs="">sudo nano /etc/opendkim.conf
</code></pre>
<p>Append the following lines to the end of the conf file (each parameter is explained below).  Optionally, you can choose a custom port number for the <code>Socket</code>.  Make sure that it's not used by a different application.</p>
<pre class="code-pre "><code langs="">AutoRestart             Yes
AutoRestartRate         10/1h
UMask                   002
Syslog                  yes
SyslogSuccess           Yes
LogWhy                  Yes

Canonicalization        relaxed/simple

ExternalIgnoreList      refile:/etc/opendkim/TrustedHosts
InternalHosts           refile:/etc/opendkim/TrustedHosts
KeyTable                refile:/etc/opendkim/KeyTable
SigningTable            refile:/etc/opendkim/SigningTable

Mode                    sv
PidFile                 /var/run/opendkim/opendkim.pid
SignatureAlgorithm      rsa-sha256

UserID                  opendkim:opendkim

Socket                  inet:12301@localhost
</code></pre>
<ul>
<li><p><strong>AutoRestart</strong>: auto restart the filter on failures</p></li>
<li><p><strong>AutoRestartRate</strong>: specifies the filter's maximum restart rate, if restarts begin to happen faster than this rate, the filter will terminate; <code>10/1h</code> - 10 restarts/hour are allowed at most</p></li>
<li><p><strong>UMask</strong>: gives all access permissions to the user group defined by <code>UserID</code> and allows other users to read and execute files, in this case it will allow the creation and modification of a Pid file.</p></li>
<li><p><strong>Syslog</strong>, <strong>SyslogSuccess</strong>, *<em>LogWhy</em>: these parameters enable detailed logging via calls to syslog</p></li>
<li><p><strong>Canonicalization</strong>: defines the canonicalization methods used at message signing, the <code>simple</code> method allows almost no modification while the <code>relaxed</code> one tolerates minor changes such as <br />
whitespace replacement; <code>relaxed/simple</code> - the message header will be processed with the <code>relaxed</code> algorithm and the body with the <code>simple</code> one</p></li>
<li><p><strong>ExternalIgnoreList</strong>: specifies the external hosts that can send mail through the server as one of the signing domains without credentials</p></li>
<li><p><strong>InternalHosts</strong>: defines a list of internal hosts whose mail should not be verified but signed instead</p></li>
<li><p><strong>KeyTable</strong>: maps key names to signing keys</p></li>
<li><p><strong>SigningTable</strong>: lists the signatures to apply to a message based on the address found in the <code>From:</code> header field</p></li>
<li><p><strong>Mode</strong>: declares operating modes; in this case the milter acts as a signer (<code>s</code>) and a verifier (<code>v</code>)</p></li>
<li><p><strong>PidFile</strong>: the path to the Pid file which contains the process identification number</p></li>
<li><p><strong>SignatureAlgorithm</strong>: selects the signing algorithm to use when creating signatures</p></li>
<li><p><strong>UserID</strong>: the opendkim process runs under this user and group</p></li>
<li><p><strong>Socket</strong>: the milter will listen on the socket specified here, Posfix will send messages to opendkim for signing and verification through this socket; <code>12301@localhost</code> defines a TCP socket that listens on <code>localhost</code>, port <code>12301</code></p></li>
</ul>

<p>This simple configuration is meant to allow message signing for one or more domains, to learn about other options please go <a href="http://www.opendkim.org/opendkim.conf.5.html">here</a>.</p>

<p>Connect the milter to Postfix:</p>
<pre class="code-pre "><code langs="">sudo nano /etc/default/opendkim
</code></pre>
<p>Add the following line, edit the port number only if a custom one is used:</p>
<pre class="code-pre "><code langs="">SOCKET="inet:12301@localhost"
</code></pre>
<p>Configure postfix to use this milter:</p>
<pre class="code-pre "><code langs="">sudo nano /etc/postfix/main.cf
</code></pre>
<p>Make sure that these two lines are present in the Postfix config file and are not commented out:</p>
<pre class="code-pre "><code langs="">milter_protocol = 2
milter_default_action = accept
</code></pre>
<p>It is likely that a filter (SpamAssasin, Clamav etc.) is already used by Postfix; if the following parameters are present, just append the opendkim milter to them (milters are separated by a comma), the port number should be the same as in <code>opendkim.conf</code>:</p>
<pre class="code-pre "><code langs="">smtpd_milters = unix:/spamass/spamass.sock, inet:localhost:12301
non_smtpd_milters = unix:/spamass/spamass.sock, inet:localhost:12301
</code></pre>
<p>If the parameters are missing, define them as follows:</p>
<pre class="code-pre "><code langs="">smtpd_milters = inet:localhost:12301
non_smtpd_milters = inet:localhost:12301
</code></pre>
<p>Create a directory structure that will hold the trusted hosts, key tables, signing tables and crypto keys:</p>
<pre class="code-pre "><code langs="">sudo mkdir /etc/opendkim
sudo mkdir /etc/opendkim/keys
</code></pre>
<p>Specify trusted hosts:</p>
<pre class="code-pre "><code langs="">sudo nano /etc/opendkim/TrustedHosts
</code></pre>
<p>We will use this file to define both <code>ExternalIgnoreList</code> and <code>InternalHosts</code>, messages originating from these hosts, domains and IP addresses will be trusted and signed.</p>

<p>Because our main configuration file declares <code>TrustedHosts</code> as a regular expression file (<code>refile</code>), we can use wildcard patters, <code>*.example.com</code> means that messages coming from example.com's subdomains will be trusted too, not just the ones sent from the root domain.</p>

<p>Customize and add the following lines to the newly created file. Multiple domains can be specified, do not edit the first three lines:</p>
<pre class="code-pre "><code langs="">127.0.0.1
localhost
192.168.0.1/24

*.example.com

#*.example.net
#*.example.org
</code></pre>
<p>Create a key table:</p>
<pre class="code-pre "><code langs="">sudo nano /etc/opendkim/KeyTable
</code></pre>
<p>A key table contains each selector/domain pair and the path to their private key. Any alphanumeric string can be used as a selector, in this example <code>mail</code> is used and it's not necessary to change it.</p>
<pre class="code-pre "><code langs="">mail._domainkey.example.com example.com:mail:/etc/opendkim/keys/example.com/mail.private

#mail._domainkey.example.net example.net:mail:/etc/opendkim/keys/example.net/mail.private
#mail._domainkey.example.org example.org:mail:/etc/opendkim/keys/example.org/mail.private
</code></pre>
<p>Create a signing table:</p>
<pre class="code-pre "><code langs="">sudo nano /etc/opendkim/SigningTable
</code></pre>
<p>This file is used for declaring the domains/email addresses and their selectors.</p>
<pre class="code-pre "><code langs="">*@example.com mail._domainkey.example.com

#*@example.net mail._domainkey.example.net
#*@example.org mail._domainkey.example.org
</code></pre>
<h2 id="generate-the-public-and-private-keys">Generate the public and private keys</h2>

<p>Change to the keys directory:</p>
<pre class="code-pre "><code langs="">cd /etc/opendkim/keys
</code></pre>
<p>Create a separate folder for the domain to hold the keys:</p>
<pre class="code-pre "><code langs="">sudo mkdir example.com
cd example.com
</code></pre>
<p>Generate the keys:</p>
<pre class="code-pre "><code langs="">sudo opendkim-genkey -s mail -d example.com
</code></pre>
<p><code>-s</code> specifies the selector and <code>-d</code> the domain, this command will create two files, <code>mail.private</code> is our private key and <code>mail.txt</code> contains the public key.</p>

<p>Change the owner of the private key to <code>opendkim</code>:</p>
<pre class="code-pre "><code langs="">sudo chown opendkim:opendkim mail.private
</code></pre>
<h2 id="add-the-public-key-to-the-domain-39-s-dns-records">Add the public key to the domain's DNS records</h2>

<p>Open <code>mail.txt</code>:</p>
<pre class="code-pre "><code langs="">sudo nano -$ mail.txt
</code></pre>
<p>The public key is defined under the <code>p</code> parameter. Do not use the example key below, it's only an illustration and will not work on your server.</p>
<pre class="code-pre "><code langs="">mail._domainkey IN TXT "v=DKIM1; k=rsa; p=MIGfMA0GCSqGSIb3DQEBAQUAA4GNADCBiQKBgQC5N3lnvvrYgPCRSoqn+awTpE+iGYcKBPpo8HHbcFfCIIV10Hwo4PhCoGZSaKVHOjDm4yefKXhQjM7iKzEPuBatE7O47hAx1CJpNuIdLxhILSbEmbMxJrJAG0HZVn8z6EAoOHZNaPHmK2h4UUrjOG8zA5BHfzJf7tGwI+K619fFUwIDAQAB" ; ----- DKIM key mail for example.com
</code></pre>
<p>Copy that key and add a TXT record to your domain's DNS entries:</p>
<pre class="code-pre "><code langs="">Name: mail._domainkey.example.com.

Text: "v=DKIM1; k=rsa; p=MIGfMA0GCSqGSIb3DQEBAQUAA4GNADCBiQKBgQC5N3lnvvrYgPCRSoqn+awTpE+iGYcKBPpo8HHbcFfCIIV10Hwo4PhCoGZSaKVHOjDm4yefKXhQjM7iKzEPuBatE7O47hAx1CJpNuIdLxhILSbEmbMxJrJAG0HZVn8z6EAoOHZNaPHmK2h4UUrjOG8zA5BHfzJf7tGwI+K619fFUwIDAQAB"
</code></pre>
<p><img src="https://assets.digitalocean.com/articles/DKIM_Postfix/1.jpg" alt="" /></p>

<p><img src="https://assets.digitalocean.com/articles/DKIM_Postfix/2.jpg" alt="" /></p>

<p>Please note that the DNS changes may take a couple of hours to propagate.</p>

<p>Restart Postfix and OpenDKIM:</p>
<pre class="code-pre "><code langs="">sudo service postfix restart
sudo service opendkim restart
</code></pre>
<p><strong>Congratulations! You have successfully configured DKIM for your mail server!</strong></p>

<p>The configuration can be tested by sending an empty email to <code>check-auth@verifier.port25.com</code> and a reply will be received. If everything works correctly you should see <code>DKIM check: pass</code> under <code>Summary of Results</code>.</p>
<pre class="code-pre "><code langs="">==========================================================
Summary of Results
==========================================================
SPF check:          pass
DomainKeys check:   neutral
DKIM check:         pass
Sender-ID check:    pass
SpamAssassin check: ham
</code></pre>
<p>Alternatively, you can send a message to a Gmail address that you control, view the received email's headers in your Gmail inbox, <code>dkim=pass</code> should be present in the <code>Authentication-Results</code> header field.</p>
<pre class="code-pre "><code langs="">Authentication-Results: mx.google.com;
       spf=pass (google.com: domain of contact@example.com designates --- as permitted sender) smtp.mail=contact@example.com;
       dkim=pass header.i=@example.com;
</code></pre>
<div class="author">Submitted by: P. Sebastian</div>

    