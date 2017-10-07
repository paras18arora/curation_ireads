<link rel='stylesheet' href='digital_ocean.css'><br> <br> 
      <h3 id="introduction">Introduction</h3>

<p>OpenLDAP provides an LDAP directory service that is flexible and well-supported.  However, out-of-the-box, the server itself communicates over an unencrypted web connection.  In this guide, we will demonstrate how to encrypt connections to OpenLDAP using STARTTLS to upgrade conventional connections to TLS.  We will be using an Ubuntu 14.04 as our LDAP server.</p>

<h2 id="prerequisites">Prerequisites</h2>

<p>Before you get started with this guide, you should have a non-root user with <code>sudo</code> set up on your server.  To set up a user of this type, follow our <a href="https://indiareads/community/tutorials/initial-server-setup-with-ubuntu-14-04">Ubuntu 14.04 initial setup guide</a>.</p>

<p>We will cover how to install OpenLDAP on an Ubuntu 14.04 server in this guide.  If you already have OpenLDAP installed on your server, you can skip the relevant installation and configuration steps.</p>

<h2 id="ldap-over-ssl-vs-ldap-with-starttls">LDAP Over SSL vs LDAP with STARTTLS</h2>

<p>There are two ways to encrypt LDAP connections with SSL/TLS.</p>

<p>Traditionally, LDAP connections that needed to be encrypted were handled on a separate port, typically <code>636</code>.  The entire connection would be wrapped with SSL/TLS.  This process, called LDAP over SSL, uses the <code>ldaps://</code> protocol.  This method of encryption is now deprecated.</p>

<p>STARTTLS is an alternative approach that is now the preferred method of encrypting an LDAP connection.  STARTTLS "upgrades" a non-encrypted connection by wrapping it with SSL/TLS after/during the connection process.  This allows unencrypted and encrypted connections to be handled by the same port.  This guide will utilize STARTTLS to encrypt connections.</p>

<h2 id="setting-the-hostname-and-fqdn">Setting the Hostname and FQDN</h2>

<p>Before you get started, we should set up our server so that it correctly resolves its hostname and fully qualified domain name (FQDN).  This will be necessary in order for our certificates to be validated by clients.  We will assume that our LDAP server will be hosted on a machine with the FQDN of <code>ldap.example.com</code>.</p>

<p>To set the hostname in all of the relevant places on your server, use the <code>hostnamectl</code> command with the <code>set-hostname</code> option.  Set the hostname to the short hostname (do not include the domain name component):</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo hostnamectl set-hostname <span class="highlight">ldap</span>
</li></ul></code></pre>
<p>Next, we need to set the FQDN of our server by making sure that our <code>/etc/hosts</code> file has the correct information:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo nano /etc/hosts
</li></ul></code></pre>
<p>Find the line that maps the <code>127.0.1.1</code> IP address.  Change the first field after the IP address to the FQDN of the server, and the second field to the short hostname.  For our example, it would look something like this:</p>
<div class="code-label " title="/etc/hosts">/etc/hosts</div><pre class="code-pre "><code langs="">. . .

127.0.1.1 <span class="highlight">ldap.example.com ldap</span>
127.0.0.1 localhost

. . .
</code></pre>
<p>Save and close the file when you are finished.</p>

<p>You can check that you've configured these values correctly by typing:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">hostname
</li></ul></code></pre>
<p>This should return your short hostname:</p>
<div class="code-label " title="short hostname">short hostname</div><pre class="code-pre "><code langs="">ldap
</code></pre>
<p>Check the FQDN by typing:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">hostname -f
</li></ul></code></pre>
<p>This should return the FQDN:</p>
<div class="code-label " title="FQDN setting">FQDN setting</div><pre class="code-pre "><code langs="">ldap.example.com
</code></pre>
<h2 id="installing-the-ldap-server-and-gnutls-software">Installing the LDAP Server and GnuTLS Software</h2>

<p>After ensuring that your hostname is set properly, we can install the software we need.  If you already have OpenLDAP installed and configured, you can skip the first sub-section.</p>

<h3 id="install-the-openldap-server">Install the OpenLDAP Server</h3>

<p>If you do not already have OpenLDAP installed, now is the time to fix that.  Update your server's local package index and install the software by typing:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo apt-get update
</li><li class="line" prefix="$">sudo apt-get install slapd ldap-utils
</li></ul></code></pre>
<p>You will be asked to provide an LDAP administrative password.  Feel free to skip the prompt, as we will be reconfiguring immediately after.</p>

<p>In order to access some additional prompts that we need, we'll reconfigure the package after installation.  To do so, type:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo dpkg-reconfigure slapd
</li></ul></code></pre>
<p>Answer the prompts appropriately, using the information below as a starting point:</p>

<ul>
<li>Omit OpenLDAP server configuration? <strong>No</strong> (we want an initial database and configuration)</li>
<li>DNS domain name: <strong><code><span class="highlight">example.com</span></code></strong> (use the server's domain name, minus the hostname.  This will be used to create the base entry for the information tree)</li>
<li>Organization name: <strong>Example Inc</strong> (This will simply be added to the base entry as the name of your organization)</li>
<li>Administrator password: [whatever you'd like]</li>
<li>Confirm password: [must match the above]</li>
<li>Database backend to use: <strong>HDB</strong> (out of the two choices, this has the most functionality)</li>
<li>Do you want the database to be removed when slapd is purged? (your choice.  Choose "Yes" to allow a completely clean removal, choose "No" to save your data even when the software is removed)</li>
<li>Move old database? <strong>Yes</strong></li>
<li>Allow LDAPv2 protocol? <strong>No</strong></li>
</ul>

<h3 id="install-the-ssl-components">Install the SSL Components</h3>

<p>Once your OpenLDAP server is configured, we can go ahead and install the packages we'll use to encrypt our connection.  The Ubuntu OpenLDAP package is compiled against the GnuTLS SSL libraries, so we will use GnuTLS to generate our SSL credentials:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo apt-get install gnutls-bin ssl-cert
</li></ul></code></pre>
<p>With all of our tools installed, we can begin creating the certificates and keys needed to encrypt our connections.</p>

<h2 id="create-the-certificate-templates">Create the Certificate Templates</h2>

<p>To encrypt our connections, we'll need to configure a certificate authority and use it to sign the keys for the LDAP server(s) in our infrastructure.  So for our single server setup, we will need two sets of key/certificate pairs: one for the certificate authority itself and one that is associated with the LDAP service.</p>

<p>To create the certificates needed to represent these entities, we'll create some template files.  These will contain the information that the <code>certtool</code> utility needs in order to create certificates with the appropriate properties.</p>

<p>Start by making a directory to store the template files:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo mkdir /etc/ssl/templates
</li></ul></code></pre>
<h3 id="create-the-ca-template">Create the CA Template</h3>

<p>Create the template for the certificate authority first.  We'll call the file <code>ca_server.conf</code>.  Create and open the file in your text editor:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo nano /etc/ssl/templates/ca_server.conf
</li></ul></code></pre>
<p>We only need to provide a few pieces of information in order to successfully create a certificate authority.  We need to specify that the certificate will be for a CA (certificate authority) by adding the <code>ca</code> option.  We also need the <code>cert_signing_key</code> option to give the generated certificate the ability to sign additional certificates.  We can set the <code>cn</code> to whatever descriptive name we'd like for our certificate authority:</p>
<div class="code-label " title="caserver.conf">caserver.conf</div><pre class="code-pre "><code langs="">cn = LDAP Server CA
ca
cert_signing_key
</code></pre>
<p>Save and close the file.</p>

<h3 id="create-the-ldap-service-template">Create the LDAP Service Template</h3>

<p>Next, we can create a template for our LDAP server certificate called <code>ldap_server.conf</code>.  Create and open the file in your text editor with <code>sudo</code> privileges:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo nano /etc/ssl/templates/ldap_server.conf
</li></ul></code></pre>
<p>Here, we'll provide a few different pieces of information.  We'll provide the name of our organization and set the <code>tls_www_server</code>, <code>encryption_key</code>, and <code>signing_key</code> options so that our cert has the basic functionality it needs.</p>

<p>The <code>cn</code> in this template <strong>must</strong> match the FQDN of the LDAP server.  If this value does not match, the client will reject the server's certificate.  We will also set the expiration date for the certificate.  We'll create a 10 year certificate to avoid having to manage frequent renewals:</p>
<div class="code-label " title="ldapserver.conf">ldapserver.conf</div><pre class="code-pre "><code langs="">organization = "<span class="highlight">Example Inc</span>"
cn = <span class="highlight">ldap.example.com</span>
tls_www_server
encryption_key
signing_key
expiration_days = 3652
</code></pre>
<p>Save and close the file when you're finished.</p>

<h2 id="create-ca-key-and-certificate">Create CA Key and Certificate</h2>

<p>Now that we have our templates, we can create our two key/certificate pairs.  We need to create the certificate authority's set first.</p>

<p>Use the <code>certtool</code> utility to generate a private key.  The <code>/etc/ssl/private</code> directory is protected from non-root users and is the appropriate location to place the private keys we will be generating.  We can generate a private key and write it to a file called <code>ca_server.key</code> within this directory by typing:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo certtool -p --outfile /etc/ssl/private/ca_server.key
</li></ul></code></pre>
<p>Now, we can use the private key that we just generated and the template file we created in the last section to create the certificate authority certificate.  We will write this to a file in the <code>/etc/ssl/certs</code> directory called <code>ca_server.pem</code>:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo certtool -s --load-privkey /etc/ssl/private/ca_server.key --template /etc/ssl/templates/ca_server.conf --outfile /etc/ssl/certs/ca_server.pem
</li></ul></code></pre>
<p>We now have the private key and certificate pair for our certificate authority.  We can use this to sign the key that will be used to actually encrypt the LDAP session.</p>

<h2 id="create-ldap-service-key-and-certificate">Create LDAP Service Key and Certificate</h2>

<p>Next, we need to generate a private key for our LDAP server.  We will again put the generated key in the <code>/etc/ssl/private</code> directory for security purposes and will call the file <code>ldap_server.key</code> for clarity.</p>

<p>We can generate the appropriate key by typing:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo certtool -p --sec-param high --outfile /etc/ssl/private/ldap_server.key
</li></ul></code></pre>
<p>Once we have the private key for the LDAP server, we have everything we need to generate a certificate for the server.  We will need to pull in almost all of the components we've created thus far (the CA certificate and key, the LDAP server key, and the LDAP server template).</p>

<p>We will put the certificate in the <code>/etc/ssl/certs</code> directory and name it <code>ldap_server.pem</code>.  The command we need is:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo certtool -c --load-privkey /etc/ssl/private/ldap_server.key --load-ca-certificate /etc/ssl/certs/ca_server.pem --load-ca-privkey /etc/ssl/private/ca_server.key --template /etc/ssl/templates/ldap_server.conf --outfile /etc/ssl/certs/ldap_server.pem
</li></ul></code></pre>
<h2 id="give-openldap-access-to-the-ldap-server-key">Give OpenLDAP Access to the LDAP Server Key</h2>

<p>We now have all of the certificates and keys we need.  However, currently, our OpenLDAP process will be unable to access its own key.</p>

<p>A group called <code>ssl-cert</code> already exists as the group-owner of the <code>/etc/ssl/private</code> directory.  We can add the user our OpenLDAP process runs under (<code>openldap</code>) to this group:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo usermod -aG ssl-cert openldap
</li></ul></code></pre>
<p>Now, our OpenLDAP user has access to the directory.  We still need to give that group ownership of the <code>ldap_server.key</code> file though so that we can allow read access.  Give the <code>ssl-cert</code> group ownership over that file by typing:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo chown :ssl-cert /etc/ssl/private/ldap_server.key
</li></ul></code></pre>
<p>Now, give the <code>ssl-cert</code> group read access to the file:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo chmod 640 /etc/ssl/private/ldap_server.key
</li></ul></code></pre>
<p>Our OpenSSL process can now access the key file properly.</p>

<h2 id="configure-openldap-to-use-the-certificate-and-keys">Configure OpenLDAP to Use the Certificate and Keys</h2>

<p>We have our files and have configured access to the components correctly.  Now, we need to modify our OpenLDAP configuration to use the files we've made.  We will do this by creating an LDIF file with our configuration changes and loading it into our LDAP instance.</p>

<p>Move to your home directory and open a file called <code>addcerts.ldif</code>.  We will put our configuration changes in this file:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">cd ~
</li><li class="line" prefix="$">nano addcerts.ldif
</li></ul></code></pre>
<p>To make configuration changes, we need to target the <code>cn=config</code> entry of the configuration DIT.  We need to specify that we are wanting to modify the attributes of the entry.  Afterwards we need to add the <code>olcTLSCACertificateFile</code>, <code>olcCertificateFile</code>, and <code>olcCertificateKeyFile</code> attributes and set them to the correct file locations.</p>

<p>The end result will look like this:</p>
<div class="code-label " title="addcerts.ldif">addcerts.ldif</div><pre class="code-pre "><code langs="">dn: cn=config
changetype: modify
add: olcTLSCACertificateFile
olcTLSCACertificateFile: /etc/ssl/certs/ca_server.pem
-
add: olcTLSCertificateFile
olcTLSCertificateFile: /etc/ssl/certs/ldap_server.pem
-
add: olcTLSCertificateKeyFile
olcTLSCertificateKeyFile: /etc/ssl/private/ldap_server.key
</code></pre>
<p>Save and close the file when you are finished.  Apply the changes to your OpenLDAP system using the <code>ldapmodify</code> command:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo ldapmodify -H ldapi:// -Y EXTERNAL -f addcerts.ldif
</li></ul></code></pre>
<p>We can reload OpenLDAP to apply the changes:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo service slapd force-reload
</li></ul></code></pre>
<p>Our clients can now encrypt their connections to the server over the conventional <code>ldap://</code> port by using STARTTLS.</p>

<h2 id="setting-up-the-client-machines">Setting up the Client Machines</h2>

<p>In order to connect to the LDAP server and initiate a STARTTLS upgrade, the clients must have access to the certificate authority certificate and must request the upgrade.</p>

<h3 id="on-the-openldap-server">On the OpenLDAP Server</h3>

<p>If you are interacting with the OpenLDAP server from the server itself, you can set up the client utilities by copying the CA certificate and adjusting the client configuration file.</p>

<p>First, copy the CA certificate from the <code>/etc/ssl/certs</code> directory to a file within the <code>/etc/ldap</code> directory.  We will call this file <code>ca_certs.pem</code>.  This file can be used to store all of the CA certificates that clients on this machine may wish to access.  For our purposes, this will only contain a single certificate:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo cp /etc/ssl/certs/ca_server.pem /etc/ldap/ca_certs.pem
</li></ul></code></pre>
<p>Now, we can adjust the system-wide configuration file for the OpenLDAP utilities.  Open up the configuration file in your text editor with <code>sudo</code> privileges:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo nano /etc/ldap/ldap.conf
</li></ul></code></pre>
<p>Adjust the value of the <code>TLS_CACERT</code> option to point to the file we just created:</p>
<div class="code-label " title="/etc/ldap/ldap.conf">/etc/ldap/ldap.conf</div><pre class="code-pre "><code langs="">. . .

TLS_CACERT /etc/ldap/ca_certs.pem

. . .
</code></pre>
<p>Save and close the file.</p>

<p>You should now be able to upgrade your connections to use STARTTLS by passing the <code>-Z</code> option when using the OpenLDAP utilities.  You can force STARTTLS upgrade by passing it twice.  Test this by typing:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">ldapwhoami -H ldap:// -x -ZZ
</li></ul></code></pre>
<p>This forces a STARTTLS upgrade.  If this is successful, you should see:</p>
<div class="code-label " title="STARTTLS success">STARTTLS success</div><pre class="code-pre "><code langs="">anonymous
</code></pre>
<p>If you mis-configured something, you will likely see an error like this:</p>
<div class="code-label " title="STARTTLS failure">STARTTLS failure</div><pre class="code-pre "><code langs="">ldap_start_tls: Connect error (-11)
    additional info: (unknown error code)
</code></pre>
<h3 id="configuring-remote-clients">Configuring Remote Clients</h3>

<p>If you are connecting to your OpenLDAP server from remote servers, you will need to complete a similar process.  First, you must copy the CA certificate to the client machine.  You can do this easily with the <code>scp</code> utility.</p>

<h4 id="forwarding-ssh-keys-to-the-client">Forwarding SSH Keys to the Client</h4>

<p>If you connect to your OpenLDAP server using SSH keys and your client machine is also remote, you will need to add them to an agent and forward them when connecting to your client machine.</p>

<p>To do this, on your local machine, start the SSH agent by typing:</p>
<pre class="code-pre custom_prefix"><code langs=""><ul class="prefixed"><li class="line" prefix="local$">eval $(ssh-agent)
</li></ul></code></pre>
<p>Add your SSH key to the agent by typing:</p>
<pre class="code-pre custom_prefix"><code langs=""><ul class="prefixed"><li class="line" prefix="local$">ssh-add
</li></ul></code></pre>
<p>Now, you can forward your SSH keys when you connect to your LDAP client machine by adding the <code>-A</code> flag:</p>
<pre class="code-pre custom_prefix"><code langs=""><ul class="prefixed"><li class="line" prefix="local$">ssh -A <span class="highlight">user</span>@<span class="highlight">ldap_client</span>
</li></ul></code></pre>
<h4 id="copying-the-ca-certificate">Copying the CA Certificate</h4>

<p>Once you are connected to the OpenLDAP client, you can copy the CA certificate by typing:</p>
<pre class="code-pre custom_prefix"><code langs=""><ul class="prefixed"><li class="line" prefix="client$">scp <span class="highlight">user</span>@<span class="highlight">ldap.example.com</span>:/etc/ssl/certs/ca_server.pem ~/
</li></ul></code></pre>
<p>Now, append the copied certificate to the list of CA certificates that the client knows about.  This will append the certificate to the file if it already exists and will create the file if it doesn't:</p>
<pre class="code-pre custom_prefix"><code langs=""><ul class="prefixed"><li class="line" prefix="client$">cat ~/ca_server.pem | sudo tee -a /etc/ldap/ca_certs.pem
</li></ul></code></pre>
<h4 id="adjust-the-client-configuration">Adjust the Client Configuration</h4>

<p>Next, we can adjust the global configuration file for the LDAP utilities to point to our <code>ca_certs.pem</code> file.  Open the file with <code>sudo</code> privileges:</p>
<pre class="code-pre custom_prefix"><code langs=""><ul class="prefixed"><li class="line" prefix="client$">sudo nano /etc/ldap/ldap.conf
</li></ul></code></pre>
<p>Find the <code>TLS_CACERT</code> option and set it to the <code>ca_certs.pem</code> file:</p>
<div class="code-label " title="/etc/ldap/ldap.conf">/etc/ldap/ldap.conf</div><pre class="code-pre "><code langs="">. . .

TLS_CACERT /etc/ldap/ca_certs.pem

. . .
</code></pre>
<p>Save and close the file when you are finished.</p>

<p>Test the STARTTLS upgrade by typing this:</p>
<pre class="code-pre custom_prefix"><code langs=""><ul class="prefixed"><li class="line" prefix="client$">ldapwhoami -H ldap://<span class="highlight">ldap.example.com</span> -x -ZZ
</li></ul></code></pre>
<p>If the STARTTLS upgrade is successful, you should see:</p>
<div class="code-label " title="STARTTLS success">STARTTLS success</div><pre class="code-pre "><code langs="">anonymous
</code></pre>
<h2 id="force-connections-to-use-tls-optional">Force Connections to Use TLS (Optional)</h2>

<p>We've successfully configured our OpenLDAP server so that it can seamlessly upgrade normal LDAP connections to TLS through the STARTTLS process.  However, this still allows unencrypted sessions, which may not be what you want.</p>

<p>If you wish to force STARTTLS upgrades for every connection, you can adjust your server's settings.  We will only be applying this requirement to the regular DIT, not the configuration DIT accessible beneath the <code>cn=config</code> entry.</p>

<p>First, you need to find the appropriate entry to modify.  We will print a list of all of the DITs (directory information trees: the hierarchies of entries that an LDAP server handles) that the OpenLDAP server has information about as well as the entry that configures each DIT.</p>

<p>On your OpenLDAP server, type:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo ldapsearch -H ldapi:// -Y EXTERNAL -b "cn=config" -LLL -Q "(olcSuffix=*)" dn olcSuffix
</li></ul></code></pre>
<p>The response should look something like this:</p>
<div class="code-label " title="DITs Served by OpenLDAP">DITs Served by OpenLDAP</div><pre class="code-pre "><code langs="">dn: olcDatabase={1}hdb,cn=config
olcSuffix: dc=example,dc=com
</code></pre>
<p>You may have more DIT and database pairs if your server is configured to handle more than one DIT.  Here, we have a single DIT with the base entry of <code>dc=example,dc=com</code>, which would be the entry created for a domain of <code>example.com</code>.  This DIT's configuration is handled by the <code>olcDatabase={1}hdb,cn=config</code> entry.  Make note of the DNs of the DITs you want to force encryption on.</p>

<p>We will use an LDIF file to make the changes.  Create the LDIF file in your home directory.  We will call it <code>forcetls.ldif</code>:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">nano ~/forcetls.ldif
</li></ul></code></pre>
<p>Inside, target the DN you want to force TLS on.  In our case, this will be <code>dn: olcDatabase={1}hdb,cn=config</code>.  We will set the <code>changetype</code> to "modify" and add the <code>olcSecurity</code> attribute.  Set the value of the attribute to "tls=1" to force TLS for this DIT:</p>
<div class="code-label " title="forcetls.ldif">forcetls.ldif</div><pre class="code-pre "><code langs="">dn: olcDatabase={1}hdb,cn=config
changetype: modify
add: olcSecurity
olcSecurity: tls=1
</code></pre>
<p>Save and close the file when you are finished.</p>

<p>To apply the change, type:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo ldapmodify -H ldapi:// -Y EXTERNAL -f forcetls.ldif
</li></ul></code></pre>
<p>Reload the OpenLDAP service by typing:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo service slapd force-reload
</li></ul></code></pre>
<p>Now, if you search the <code>dc=example,dc=com</code> DIT, you will be refused if you do not use the <code>-Z</code> option to initiate a STARTTLS upgrade:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">ldapsearch -H ldap:// -x -b "dc=example,dc=com" -LLL dn
</li></ul></code></pre><div class="code-label " title="TLS required failure">TLS required failure</div><pre class="code-pre "><code langs="">Confidentiality required (13)
Additional information: TLS confidentiality required
</code></pre>
<p>We can demonstrate that STARTTLS connections still function correctly:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">ldapsearch -H ldap:// -x -b "dc=example,dc=com" -LLL -Z dn
</li></ul></code></pre><div class="code-label " title="TLS required success">TLS required success</div><pre class="code-pre "><code langs="">dn: dc=example,dc=com

dn: cn=admin,dc=example,dc=com
</code></pre>
<h2 id="conclusion">Conclusion</h2>

<p>You should now have an OpenLDAP server configured with STARTTLS encryption.  Encrypting your connection to the OpenLDAP server with TLS allows you to verify the identity of the server you are connecting with.  It also shields your traffic from intermediate parties.  When connecting over an open network, encrypting your traffic is essential.</p>

    