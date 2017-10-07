<link rel='stylesheet' href='digital_ocean.css'><br><img src=https://community-cdn-digitalocean-com.global.ssl.fastly.net/assets/tutorials/images/large/open_vpn_server_tw.jpg?1430836576/> <br> 
      <h3 id="introduction">Introduction</h3>

<p>Want to access the Internet safely and securely from your smartphone or laptop when connected to an untrusted network such as the WiFi of a hotel or coffee shop? A <a href="https://en.wikipedia.org/wiki/Virtual_private_network">Virtual Private Network</a> (VPN) allows you to traverse untrusted networks privately and securely to your IndiaReads Droplet as if you were on a secure and private network. The traffic emerges from the Droplet and continues its journey to the destination. </p>

<p>When combined with <a href="https://en.wikipedia.org/wiki/HTTP_Secure">HTTPS connections</a>, this setup allows you to secure your wireless logins and transactions. You can circumvent geographical restrictions and censorship, and shield your location and unencrypted HTTP traffic from the untrusted network.</p>

<p><a href="https://openvpn.net">OpenVPN</a> is a full-featured open source Secure Socket Layer (SSL) VPN solution that accommodates a wide range of configurations. In this tutorial, we'll set up an OpenVPN server on a Droplet and then configure access to it from Windows, OS X, iOS and Android. This tutorial will keep the installation and configuration steps as simple as possible for these setups.</p>

<p><span class="note"><strong>Note:</strong> OpenVPN can be installed automatically on your Droplet by adding <a href="http://do.co/1NZeibM">this script</a> to its User Data when launching it. Check out <a href="https://indiareads/community/tutorials/an-introduction-to-droplet-metadata">this tutorial</a> to learn more about Droplet User Data.<br /></span></p>

<h2 id="prerequisites">Prerequisites</h2>

<p>The only prerequisite is having a Ubuntu 14.04 Droplet established and running. You will need <strong>root</strong> access to complete this guide.</p>

<ul>
<li>Optional: After completion of this tutorial, It would be a good idea to create a standard user account with <a href="https://indiareads/community/tutorials/how-to-add-and-delete-users-on-an-ubuntu-14-04-vps">sudo</a> privileges for performing general maintenance on your server.</li>
</ul>

<h2 id="step-1-—-install-and-configure-openvpn-39-s-server-environment">Step 1 — Install and Configure OpenVPN's Server Environment</h2>

<p>Complete these steps for your server-side setup.</p>

<h3 id="openvpn-configuration">OpenVPN Configuration</h3>

<p>Before we install any packages, first we'll update Ubuntu's repository lists.</p>
<pre class="code-pre "><code langs="">apt-get update
</code></pre>
<p>Then we can install OpenVPN and Easy-RSA.</p>
<pre class="code-pre "><code langs="">apt-get install openvpn easy-rsa
</code></pre>
<p>The example VPN server configuration file needs to be extracted to <code>/etc/openvpn</code> so we can incorporate it into our setup. This can be done with one command:</p>
<pre class="code-pre "><code langs="">gunzip -c /usr/share/doc/openvpn/examples/sample-config-files/server.conf.gz > /etc/openvpn/server.conf
</code></pre>
<p>Once extracted, open <code>server.conf</code> in a text editor. This tutorial will use Vim but you can use whichever editor you prefer.</p>
<pre class="code-pre "><code langs="">vim /etc/openvpn/server.conf
</code></pre>
<p>There are several changes to make in this file. You will see a section looking like this:</p>
<pre class="code-pre "><code langs=""># Diffie hellman parameters.
# Generate your own with:
#   openssl dhparam -out dh1024.pem 1024
# Substitute 2048 for 1024 if you are using
# 2048 bit keys.
dh dh1024.pem
</code></pre>
<p>Edit <code>dh1024.pem</code> to say:</p>
<pre class="code-pre "><code langs="">dh2048.pem
</code></pre>
<p>This will double the RSA key length used when generating server and client keys.</p>

<p>Still in <code>server.conf</code>, now look for this section:</p>
<pre class="code-pre "><code langs=""># If enabled, this directive will configure
# all clients to redirect their default
# network gateway through the VPN, causing
# all IP traffic such as web browsing and
# and DNS lookups to go through the VPN
# (The OpenVPN server machine may need to NAT
# or bridge the TUN/TAP interface to the internet
# in order for this to work properly).
;push "redirect-gateway def1 bypass-dhcp"
</code></pre>
<p>Uncomment <code>push "redirect-gateway def1 bypass-dhcp"</code> so the VPN server passes on clients' web traffic to its destination. It should look like this when done:</p>
<pre class="code-pre "><code langs="">push "redirect-gateway def1 bypass-dhcp"
</code></pre>
<p>The next edit to make is in this area:</p>
<pre class="code-pre "><code langs=""># Certain Windows-specific network settings
# can be pushed to clients, such as DNS
# or WINS server addresses.  CAVEAT:
# http://openvpn.net/faq.html#dhcpcaveats
# The addresses below refer to the public
# DNS servers provided by opendns.com.
;push "dhcp-option DNS 208.67.222.222"
;push "dhcp-option DNS 208.67.220.220"
</code></pre>
<p>Uncomment <code>push "dhcp-option DNS 208.67.222.222"</code> and <code>push "dhcp-option DNS 208.67.220.220"</code>. It should look like this when done:</p>
<pre class="code-pre "><code langs="">push "dhcp-option DNS 208.67.222.222"
push "dhcp-option DNS 208.67.220.220"
</code></pre>
<p>This tells the server to push <a href="https://opendns.com">OpenDNS</a> to connected clients for DNS resolution where possible. This can help prevent DNS requests from leaking outside the VPN connection. However, it's important to specify desired DNS resolvers in client devices as well. Though OpenDNS is the default used by OpenVPN, you can use whichever DNS services you prefer.</p>

<p>The last area to change in <code>server.conf</code> is here:</p>
<pre class="code-pre "><code langs=""># You can uncomment this out on
# non-Windows systems.
;user nobody
;group nogroup
</code></pre>
<p>Uncomment both <code>user nobody</code> and <code>group nogroup</code>. It should look like this when done:</p>
<pre class="code-pre "><code langs="">user nobody
group nogroup
</code></pre>
<p>By default, OpenVPN runs as the <strong>root</strong> user and thus has full root access to the system. We'll instead confine OpenVPN to the user <strong>nobody</strong> and group <strong>nogroup</strong>. This is an unprivileged user with no default login capabilities, often reserved for running untrusted applications like web-facing servers.</p>

<p>Now save your changes and exit Vim.</p>

<h3 id="packet-forwarding">Packet Forwarding</h3>

<p>This is a <em>sysctl</em> setting which tells the server's kernel to forward traffic from client devices out to the Internet. Otherwise, the traffic will stop at the server. Enable packet forwarding during runtime by entering this command:</p>
<pre class="code-pre "><code langs="">echo 1 > /proc/sys/net/ipv4/ip_forward
</code></pre>
<p>We need to make this permanent so the server still forwards traffic after rebooting.</p>
<pre class="code-pre "><code langs="">vim /etc/sysctl.conf
</code></pre>
<p>Near the top of the sysctl file, you will see:</p>
<pre class="code-pre "><code langs=""># Uncomment the next line to enable packet forwarding for IPv4
#net.ipv4.ip_forward=1
</code></pre>
<p>Uncomment <code>net.ipv4.ip_forward</code>. It should look like this when done:</p>
<pre class="code-pre "><code langs=""># Uncomment the next line to enable packet forwarding for IPv4
net.ipv4.ip_forward=1
</code></pre>
<p>Save your changes and exit.</p>

<h3 id="uncomplicated-firewall-ufw">Uncomplicated Firewall (ufw)</h3>

<p>ufw is a front-end for iptables and setting up ufw is not hard. It's included by default in Ubuntu 14.04, so we only need to make a few rules and configuration edits, then switch the firewall on. As a reference for more uses for ufw, see <a href="https://indiareads/community/articles/how-to-setup-a-firewall-with-ufw-on-an-ubuntu-and-debian-cloud-server">How To Setup a Firewall with UFW on an Ubuntu and Debian Cloud Server</a>.</p>

<p>First set ufw to allow SSH. In the command prompt, <code>ENTER</code>:</p>
<pre class="code-pre "><code langs="">ufw allow ssh
</code></pre>
<p>This tutorial will use OpenVPN over UDP, so ufw must also allow UDP traffic over port <code>1194</code>.</p>
<pre class="code-pre "><code langs="">ufw allow 1194/udp
</code></pre>
<p>The ufw forwarding policy needs to be set as well. We'll do this in ufw's primary configuration file.</p>
<pre class="code-pre "><code langs="">vim /etc/default/ufw
</code></pre>
<p>Look for <code>DEFAULT_FORWARD_POLICY="DROP"</code>. This must be changed from <strong>DROP</strong> to <strong>ACCEPT</strong>. It should look like this when done:</p>
<pre class="code-pre "><code langs="">DEFAULT_FORWARD_POLICY="ACCEPT"
</code></pre>
<p>Next we will add additional ufw rules for network address translation and IP masquerading of connected clients.</p>
<pre class="code-pre "><code langs="">vim /etc/ufw/before.rules
</code></pre>
<p>Make the top of your <code>before.rules</code> file look like below. The area in <span class="highlight">red</span> for <strong>OPENVPN RULES</strong> must be added:</p>
<pre class="code-pre "><code langs="">#
# rules.before
#
# Rules that should be run before the ufw command line added rules. Custom
# rules should be added to one of these chains:
#   ufw-before-input
#   ufw-before-output
#   ufw-before-forward
#

<span class="highlight"># START OPENVPN RULES</span>
<span class="highlight"># NAT table rules</span>
<span class="highlight">*nat</span>
<span class="highlight">:POSTROUTING ACCEPT [0:0]</span> 
<span class="highlight"># Allow traffic from OpenVPN client to eth0</span>
<span class="highlight">-A POSTROUTING -s 10.8.0.0/8 -o eth0 -j MASQUERADE</span>
<span class="highlight">COMMIT</span>
<span class="highlight"># END OPENVPN RULES</span>

# Don't delete these required lines, otherwise there will be errors
*filter
</code></pre>
<p>With the changes made to ufw, we can now enable it. Enter into the command prompt:</p>
<pre class="code-pre "><code langs="">ufw enable
</code></pre>
<p>Enabling ufw will return the following prompt:</p>
<pre class="code-pre "><code langs="">Command may disrupt existing ssh connections. Proceed with operation (y|n)?
</code></pre>
<p>Answer <code><span class="highlight">y</span></code>. The result will be this output:</p>
<pre class="code-pre "><code langs="">Firewall is active and enabled on system startup
</code></pre>
<p>To check ufw's primary firewall rules:</p>
<pre class="code-pre "><code langs="">ufw status
</code></pre>
<p>The status command should return these entries:</p>
<pre class="code-pre "><code langs="">Status: active

To                         Action      From
--                         ------      ----
22                         ALLOW       Anywhere
1194/udp                   ALLOW       Anywhere
22 (v6)                    ALLOW       Anywhere (v6)
1194/udp (v6)              ALLOW       Anywhere (v6)
</code></pre>
<h2 id="step-2-—-creating-a-certificate-authority-and-server-side-certificate-amp-key">Step 2 — Creating a Certificate Authority and Server-Side Certificate & Key</h2>

<p>OpenVPN uses certificates to encrypt traffic.</p>

<h3 id="configure-and-build-the-certificate-authority">Configure and Build the Certificate Authority</h3>

<p>It is now time to set up our own Certificate Authority (CA) and generate a certificate and key for the OpenVPN server. OpenVPN supports bidirectional authentication based on certificates, meaning that the client must authenticate the server certificate and the server must authenticate the client certificate before mutual trust is established. We will use Easy RSA's scripts we copied earlier to do this.</p>

<p>First copy over the Easy-RSA generation scripts.</p>
<pre class="code-pre "><code langs="">cp -r /usr/share/easy-rsa/ /etc/openvpn
</code></pre>
<p>Then make the key storage directory.</p>
<pre class="code-pre "><code langs="">mkdir /etc/openvpn/easy-rsa/keys
</code></pre>
<p>Easy-RSA has a variables file we can edit to create certificates exclusive to our person, business, or whatever entity we choose. This information is copied to the certificates and keys, and will help identify the keys later.</p>
<pre class="code-pre "><code langs="">vim /etc/openvpn/easy-rsa/vars
</code></pre>
<p>The variables below marked in <span class="highlight">red</span> should be changed according to your preference.</p>
<pre class="code-pre "><code langs="">export KEY_COUNTRY="<span class="highlight">US</span>"
export KEY_PROVINCE="<span class="highlight">TX</span>"
export KEY_CITY="<span class="highlight">Dallas</span>"
export KEY_ORG="<span class="highlight">My Company Name</span>"
export KEY_EMAIL="<span class="highlight">sammy@example.com</span>"
export KEY_OU="<span class="highlight">MYOrganizationalUnit</span>"
</code></pre>
<p>In the same <code>vars</code> file, also edit this one line shown below. For simplicity, we will use <code>server</code> as the key name. If you want to use a different name, you would also need to update the OpenVPN configuration files that reference <code>server.key</code> and <code>server.crt</code>.</p>
<pre class="code-pre "><code langs="">export KEY_NAME="server"
</code></pre>
<p>We need to generate the Diffie-Hellman parameters; this can take several minutes.</p>
<pre class="code-pre "><code langs="">openssl dhparam -out /etc/openvpn/dh2048.pem 2048
</code></pre>
<p>Now let's change directories so that we're working directly out of where we moved Easy-RSA's scripts to earlier in Step 2.</p>
<pre class="code-pre "><code langs="">cd /etc/openvpn/easy-rsa
</code></pre>
<p>Initialize the PKI (Public Key Infrastructure). Pay attention to the <strong>dot (.)</strong> and <strong>space</strong> in front of <code>./vars</code> command. That signifies the current working directory (source).</p>
<pre class="code-pre "><code langs="">. ./vars
</code></pre>
<p>The output from the above command is shown below. Since we haven't generated anything in the <code>keys</code> directory yet, the warning is nothing to be concerned about.</p>
<pre class="code-pre "><code langs="">NOTE: If you run ./clean-all, I will be doing a rm -rf on /etc/openvpn/easy-rsa/keys
</code></pre>
<p>Now we'll clear the working directory of any possible old or example keys to make way for our new ones.</p>
<pre class="code-pre "><code langs="">./clean-all
</code></pre>
<p>This final command builds the certificate authority (CA) by invoking an interactive OpenSSL command. The output will prompt you to confirm the Distinguished Name variables that were entered earlier into the Easy-RSA's variable file (country name, organization, etc.).</p>
<pre class="code-pre "><code langs="">./build-ca
</code></pre>
<p>Simply press <code>ENTER</code> to pass through each prompt. If something must be changed, you can do that from within the prompt.</p>

<h3 id="generate-a-certificate-and-key-for-the-server">Generate a Certificate and Key for the Server</h3>

<p>Still working from <code>/etc/openvpn/easy-rsa</code>, now enter the command to build the server's key. Where you see <code><span class="highlight">server</span></code> marked in red is the <code>export KEY_NAME</code> variable we set in Easy-RSA's <code>vars</code> file earlier in Step 2.</p>
<pre class="code-pre "><code langs="">./build-key-server <span class="highlight">server</span>
</code></pre>
<p>Similar output is generated as when we ran <code>./build-ca</code>, and you can again press <code>ENTER</code> to confirm each line of the Distinguished Name. However, this time there are two additional prompts:</p>
<pre class="code-pre "><code langs="">Please enter the following 'extra' attributes
to be sent with your certificate request
A challenge password []:
An optional company name []:
</code></pre>
<p>Both should be left blank, so just press <code>ENTER</code> to pass through each one.</p>

<p>Two additional queries at the end require a positive (<code><span class="highlight">y</span></code>) response:</p>
<pre class="code-pre "><code langs="">Sign the certificate? [y/n]
1 out of 1 certificate requests certified, commit? [y/n]
</code></pre>
<p>The last prompt above should complete with:</p>
<pre class="code-pre "><code langs="">Write out database with 1 new entries
Data Base Updated
</code></pre>
<h3 id="move-the-server-certificates-and-keys">Move the Server Certificates and Keys</h3>

<p>OpenVPN expects to see the server's CA, certificate and key in <code>/etc/openvpn</code>. Let's copy them into the proper location.</p>
<pre class="code-pre "><code langs="">cp /etc/openvpn/easy-rsa/keys/{server.crt,server.key,ca.crt} /etc/openvpn
</code></pre>
<p>You can verify the copy was successful with:</p>
<pre class="code-pre "><code langs="">ls /etc/openvpn
</code></pre>
<p>You should see the certificate and key files for the server.</p>

<p>At this point, the OpenVPN server is ready to go. Start it and check the status.</p>
<pre class="code-pre "><code langs="">service openvpn start
service openvpn status
</code></pre>
<p>The status command should return:</p>
<pre class="code-pre "><code langs="">VPN 'server' is running
</code></pre>
<p>Congratulations! Your OpenVPN server is operational. If the status message says the VPN is not running, then take a look at the <code>/var/log/syslog</code> file for errors such as:</p>
<pre class="code-pre "><code langs="">Options error: --key fails with 'server.key': No such file or directory
</code></pre>
<p>That error indicates <code>server.key</code> was not copied to <code>/etc/openvpn</code> correctly. Re-copy the file and try again.</p>

<h2 id="step-3-—-generate-certificates-and-keys-for-clients">Step 3 — Generate Certificates and Keys for Clients</h2>

<p>So far we've installed and configured the OpenVPN server, created a Certificate Authority, and created the server's own certificate and key. In this step, we use the server's CA to generate certificates and keys for each client device which will be connecting to the VPN. These files will later be installed onto the client devices such as a laptop or smartphone.</p>

<h3 id="key-and-certificate-building">Key and Certificate Building</h3>

<p>It's ideal for each client connecting to the VPN to have its own unique certificate and key. This is preferable to generating one general certificate and key to use among all client devices.</p>

<blockquote>
<p><strong>Note:</strong> By default, OpenVPN does not allow simultaneous connections to the server from clients using the same certificate and key. (See <code>duplicate-cn</code> in <code>/etc/openvpn/server.conf</code>.)</p>
</blockquote>

<p>To create separate authentication credentials for each device you intend to connect to the VPN, you should complete this step for each device, but change the name <span class="highlight">client1</span> below to something different such as <span class="highlight">client2</span> or <span class="highlight">iphone2</span>. With separate credentials per device, they can later be deactivated at the server individually, if need be. The remaining examples in this tutorial will use <span class="highlight">client1</span> as our example client device's name.</p>

<p>As we did with the server's key, now we build one for our <span class="highlight">client1</span> example. You should still be working out of <code>/etc/openvpn/easy-rsa</code>.</p>
<pre class="code-pre "><code langs="">./build-key <span class="highlight">client1</span>
</code></pre>
<p>Once again, you'll be asked to change or confirm the Distinguished Name variables and these two prompts which should be left blank. Press <code>ENTER</code> to accept the defaults.</p>
<pre class="code-pre "><code langs="">Please enter the following 'extra' attributes
to be sent with your certificate request
A challenge password []:
An optional company name []:
</code></pre>
<p>As before, these two confirmations at the end of the build process require a (<code><span class="highlight">y</span></code>) response:</p>
<pre class="code-pre "><code langs="">Sign the certificate? [y/n]
1 out of 1 certificate requests certified, commit? [y/n]
</code></pre>
<p>If the key build was successful, the output will again be:</p>
<pre class="code-pre "><code langs="">Write out database with 1 new entries
Data Base Updated
</code></pre>
<p>The example client configuration file should be copied to the Easy-RSA key directory too. We'll use it as a template which will be downloaded to client devices for editing. In the copy process, we are changing the name of the example file from <code>client.conf</code> to <code>client.ovpn</code> because the <code>.ovpn</code> file extension is what the clients will expect to use.</p>
<pre class="code-pre "><code langs="">cp /usr/share/doc/openvpn/examples/sample-config-files/client.conf /etc/openvpn/easy-rsa/keys/client.ovpn
</code></pre>
<p>You can repeat this section again for each client, replacing <span class="highlight">client1</span> with the appropriate client name throughout.</p>

<h3 id="transferring-certificates-and-keys-to-client-devices">Transferring Certificates and Keys to Client Devices</h3>

<p>Recall from the steps above that we created the client certificates and keys, and that they are stored on the OpenVPN server in the <code>/etc/openvpn/easy-rsa/keys</code> directory.</p>

<p>For each client we need to transfer the client certificate, key, and profile template files to a folder on our local computer or another client device.</p>

<p>In this example, our <span class="highlight">client1</span> device requires its certificate and key, located on the server in:</p>

<ul>
<li><code>/etc/openvpn/easy-rsa/keys/<span class="highlight">client1</span>.crt</code></li>
<li><code>/etc/openvpn/easy-rsa/keys/<span class="highlight">client1</span>.key</code> </li>
</ul>

<p>The <code>ca.crt</code> and <code>client.ovpn</code> files are the same for all clients. Download these two files as well; note that the <code>ca.crt</code> file is in a different directory than the others.</p>

<ul>
<li><code>/etc/openvpn/easy-rsa/keys/client.ovpn</code></li>
<li><code>/etc/openvpn/ca.crt</code></li>
</ul>

<p>While the exact applications used to accomplish this transfer will depend on your choice and device's operating system, you want the application to use SFTP (SSH file transfer protocol) or SCP (Secure Copy) on the backend. This will transport your client's VPN authentication files over an encrypted connection.</p>

<p>Here is an example SCP command using our <span class="highlight">client1</span> example. It places the file <code>client1.key</code> into the <strong>Downloads</strong> directory on the local computer.</p>
<pre class="code-pre "><code langs="">scp root@<span class="highlight">your-server-ip</span>:/etc/openvpn/easy-rsa/keys/<span class="highlight">client1</span>.key Downloads/
</code></pre>
<p>Here are several tools and tutorials for securely transfering files from the server to a local computer:</p>

<ul>
<li><a href="http://winscp.net">WinSCP</a></li>
<li><a href="https://indiareads/community/tutorials/how-to-use-sftp-to-securely-transfer-files-with-a-remote-server">How To Use SFTP to Securely Transfer Files with a Remote Server</a></li>
<li><a href="https://indiareads/community/tutorials/how-to-use-filezilla-to-transfer-and-manage-files-securely-on-your-vps">How To Use Filezilla to Transfer and Manage Files Securely on your VPS</a></li>
</ul>

<p>At the end of this section, make sure you have these four files on your <strong>client</strong> device:</p>

<ul>
<li><code><span class="highlight">client1</span>.crt</code></li>
<li><code><span class="highlight">client1</span>.key</code></li>
<li><code>client.ovpn</code></li>
<li><code>ca.crt</code></li>
</ul>

<h2 id="step-4-creating-a-unified-openvpn-profile-for-client-devices">Step 4 - Creating a Unified OpenVPN Profile for Client Devices</h2>

<p>There are several methods for managing the client files but the easiest uses a <em>unified</em> profile. This is created by modifying the <code>client.ovpn</code> template file to include the server's Certificate Authority, and the client's certificate and its key. Once merged, only the single <code>client.ovpn</code> profile needs to be imported into the client's OpenVPN application.</p>

<p>We will create a single profile for our <span class="highlight">client1</span> device on the <strong>local computer</strong> we downloaded all the client files to. This local computer could itself be an intended client or just a temporary work area to merge the authentication files. The original <code>client.ovpn</code> template file should be duplicated and renamed. How you do this will depend on the operating system of your local computer.</p>

<p><strong>Note:</strong> The name of your duplicated <code>client.ovpn</code> doesn't need to be related to the client device. The client-side OpenVPN application will use the file name as an identifier for the VPN connection itself. Instead, you should duplicate <code>client.ovpn</code> to whatever you want the VPN's nametag to be in your operating system. For example: <strong>work.ovpn</strong> will be identified as <strong>work</strong>, <strong>school.ovpn</strong> as <strong>school</strong>, etc.</p>

<p>In this tutorial, we'll name the VPN connection IndiaReads so <code>IndiaReads.ovpn</code> will be the file name referenced from this point on. Once named, we then must open <code>IndiaReads.ovpn</code> in a text editor; you can use whichever editor you prefer.</p>

<p>The first area of attention will be for the IP address of your Droplet. Near the top of the file, change <strong>my-server-1</strong> to reflect your VPN's IP.</p>
<pre class="code-pre "><code langs=""># The hostname/IP and port of the server.
# You can have multiple remote entries
# to load balance between the servers.
remote <span class="highlight">my-server-1</span> 1194
</code></pre>
<p>Next, find the area shown below and uncomment <code>user nobody</code> and <code>group nogroup</code>, just like we did in <code>server.conf</code> in Step 1. <strong>Note:</strong> This doesn't apply to Windows so you can skip it. It should look like this when done:</p>
<pre class="code-pre "><code langs=""># Downgrade privileges after initialization (non-Windows only)
user nobody
group nogroup
</code></pre>
<p>The area given below needs the three lines shown to be commented out so we can instead include the certificate and key directly in the <code>IndiaReads.ovpn</code> file. It should look like this when done:</p>
<pre class="code-pre "><code langs=""># SSL/TLS parms.
# . . .
#ca ca.crt
#cert client.crt
#key client.key
</code></pre>
<p>To merge the individual files into the one unified profile, the contents of the <strong>ca.crt</strong>, <strong><span class="highlight">client1</span>.crt,</strong> and <strong><span class="highlight">client1</span>.key</strong> files are pasted directly into the <code>.ovpn</code> profile using a basic XML-like syntax. The XML at the end of the file should take this form:</p>
<pre class="code-pre "><code langs=""><ca>
<span class="highlight">(insert ca.crt here)</span>
</ca>
<cert>
<span class="highlight">(insert </span>client1<span class="highlight">.crt here)</span>
</cert>
<key>
<span class="highlight">(insert </span>client1<span class="highlight">.key here)</span>
</key>
</code></pre>
<p>When finished, the end of the file should be similar to this abbreviated example:</p>
<pre class="code-pre "><code langs=""><ca>
-----BEGIN CERTIFICATE-----
<span class="highlight">. . .</span>
-----END CERTIFICATE-----
</ca>

<cert>
Certificate:
<span class="highlight">. . .</span>
-----END CERTIFICATE-----
<span class="highlight">. . .</span>
-----END CERTIFICATE-----
</cert>

<key>
-----BEGIN PRIVATE KEY-----
<span class="highlight">. . .</span>
-----END PRIVATE KEY-----
</key>
</code></pre>
<p>The <code>client1.crt</code> file has some extra information in it; it's fine to just include the whole file.</p>

<p>Save the changes and exit. We now have a unified OpenVPN client profile to configure our <span class="highlight">client1</span>.</p>

<h2 id="step-5-installing-the-client-profile">Step 5 - Installing the Client Profile</h2>

<p>Now we'll discuss installing a client VPN profile on Windows, OS X, iOS, and Android. None of these client instructions are dependent on each other so you can skip to whichever is applicable to you.</p>

<p>Remember that the connection will be called whatever you named the <code>.ovpn</code> file. In our example, since the file was named <code>IndiaReads.ovpn</code>, the connection will be named <strong>IndiaReads</strong>.</p>

<h3 id="windows">Windows</h3>

<p><strong>Installing</strong></p>

<p>The OpenVPN client application for Windows can be found on <a href="https://openvpn.net/index.php/open-source/downloads.html">OpenVPN's Downloads page</a>. Choose the appropriate installer version for your version of Windows.</p>

<blockquote>
<p><strong>Note:</strong> OpenVPN needs administrative privileges to install.</p>
</blockquote>

<p>After installing OpenVPN, copy the unified <code>IndiaReads.ovpn</code> profile to:</p>
<pre class="code-pre "><code langs="">C:\Program Files\OpenVPN\config
</code></pre>
<p>When you launch OpenVPN, it will automatically see the profile and makes it available.</p>

<p>OpenVPN must be run as an administrator each time it's used, even by administrative accounts. To do this without having to right-click and select <strong>Run as administrator</strong> every time you use the VPN, you can preset this but it must be done from an administrative account. This also means that standard users will need to enter the administrator's password to use OpenVPN. On the other hand, standard users can't properly connect to the server unless OpenVPN on the client has admin rights, so the elevated privileges are necessary.</p>

<p>To set the OpenVPN application to always run as an administrator, right-click on its shortcut icon and go to <strong>Properties</strong>. At the bottom of the <strong>Compatibility</strong> tab, click the button to <strong>Change settings for all users</strong>. In the new window, check <strong>Run this program as an administrator</strong>.</p>

<p><strong>Connecting</strong></p>

<p>Each time you launch the OpenVPN GUI, Windows will ask if you want to allow the program to make changes to your computer. Click <strong>Yes</strong>. Launching the OpenVPN client application only puts the applet in the system tray so the the VPN can be connected and disconnected as needed; it does not actually make the VPN connection.</p>

<p>Once OpenVPN is started, initiate a connection by going into the system tray applet and right-clicking on the OpenVPN applet icon. This opens the context menu. Select <strong>IndiaReads</strong> at the top of the menu (that's our <code>IndiaReads.ovpn</code> profile) and choose <strong>Connect</strong>.</p>

<p>A status window will open showing the log output while the connection is established, and a message will show once the client is connected.</p>

<p>Disconnect from the VPN the same way: Go into the system tray applet, right-click the OpenVPN applet icon, select the client profile and click <strong>Disconnect</strong>.</p>

<h3 id="os-x">OS X</h3>

<p><strong>Installing</strong></p>

<p><a href="https://code.google.com/p/tunnelblick/">Tunnelblick</a> is a free, open source OpenVPN client for Mac OS X. You can download the latest disk image from the <a href="https://code.google.com/p/tunnelblick/wiki/DownloadsEntry">Tunnelblick Downloads page</a>. Double-click the downloaded <code>.dmg</code> file and follow the prompts to install.</p>

<p>Towards the end of the installation process, Tunnelblick will ask if you have any configuration files. It can be easier to answer <strong>No</strong> and let Tunnelblick finish. Open a Finder window and double-click <code>IndiaReads.ovpn</code>. Tunnelblick will install the client profile. Administrative privileges are required.</p>

<p><strong>Connecting</strong></p>

<p>Launch Tunnelblick by double-clicking Tunnelblick in the <strong>Applications</strong> folder. Once Tunnelblick has been launched, there will be a Tunnelblick icon in the menu bar at the top right of the screen for controlling connections. Click on the icon, and then the <strong>Connect</strong> menu item to initiate the VPN connection. Select the <strong>IndiaReads</strong> connection.</p>

<h3 id="ios">iOS</h3>

<p><strong>Installing</strong></p>

<p>From the iTunes App Store, search for and install <a href="https://itunes.apple.com/us/app/id590379981">OpenVPN Connect</a>, the official iOS OpenVPN client application. To transfer your iOS client profile onto the device, connect it directly to a computer. </p>

<p>Completing the transfer with iTunes will be outlined here. Open iTunes on the computer and click on <strong>iPhone</strong> > <strong>apps</strong>. Scroll down to the bottom to the <strong>File Sharing</strong> section and click the OpenVPN app. The blank window to the right, <strong>OpenVPN Documents</strong>, is for sharing files. Drag the <code>.ovpn</code> file to the OpenVPN Documents window.</p>

<p><img src="https://assets.digitalocean.com/articles/openvpn_ubunutu/1.png" alt="iTunes showing the VPN profile ready to load on the iPhone" /></p>

<p>Now launch the OpenVPN app on the iPhone. There will be a notification that a new profile is ready to import. Tap the green plus sign to import it.</p>

<p><img src="https://assets.digitalocean.com/articles/openvpn_ubunutu/2.png" alt="The OpenVPN iOS app showing new profile ready to import" /></p>

<p><strong>Connecting</strong></p>

<p>OpenVPN is now ready to use with the new profile. Start the connection by sliding the <strong>Connect</strong> button to the <strong>On</strong> position. Disconnect by sliding the same button to <strong>Off</strong>.</p>

<p><strong>Note:</strong> The VPN switch under <strong>Settings</strong> cannot be used to connect to the VPN. If you try, you will receive a notice to only connect using the OpenVPN app.</p>

<p><img src="https://assets.digitalocean.com/articles/openvpn_ubunutu/3.png" alt="The OpenVPN iOS app connected to the VPN" /></p>

<h3 id="android">Android</h3>

<p><strong>Installing</strong></p>

<p>Open the Google Play Store. Search for and install <a href="https://play.google.com/store/apps/details?id=net.openvpn.openvpn">Android OpenVPN Connect</a>, the official Android OpenVPN client application.</p>

<p>The <code>.ovpn</code> profile can be transferred by connecting the Android device to your computer by USB and copying the file over. Alternatively, if you have an SD card reader, you can remove the device's SD card, copy the profile onto it and then insert the card back into the Android device. </p>

<p>Start the OpenVPN app and tap the menu to import the profile.</p>

<p><img src="https://assets.digitalocean.com/articles/openvpn_ubunutu/4.png" alt="The OpenVPN Android app profile import menu selection" /></p>

<p>Then navigate to the location of the saved profile (the screenshot uses <code>/sdcard/Download/</code>) and select the file. The app will make a note that the profile was imported.</p>

<p><img src="https://assets.digitalocean.com/articles/openvpn_ubunutu/5.png" alt="The OpenVPN Android app selecting VPN profile to import" /></p>

<p><strong>Connecting</strong></p>

<p>To connect, simply tap the <strong>Connect</strong> button. You'll  be asked if you trust the OpenVPN application. Choose <strong>OK</strong> to initiate the connection. To disconnect from the VPN, go back to the the OpenVPN app and choose <strong>Disconnect</strong>.</p>

<p><img src="https://assets.digitalocean.com/articles/openvpn_ubunutu/6.png" alt="The OpenVPN Android app ready to connect to the VPN" /></p>

<h2 id="step-6-testing-your-vpn-connection">Step 6 - Testing Your VPN Connection</h2>

<p>Once everything is installed, a simple check confirms everything is working properly. Without having a VPN connection enabled, open a browser and go to <a href="https://www.dnsleaktest.com">DNSLeakTest</a>.</p>

<p>The site will return the IP address assigned by your internet service provider and as you appear to the rest of the world. To check your DNS settings through the same website, click on <strong>Extended Test</strong> and it will tell you which DNS servers you are using.</p>

<p>Now connect the OpenVPN client to your Droplet's VPN and refresh the browser. The completely different IP address of your VPN server should now appear. That is now how you appear to the world. Again, <a href="https://www.dnsleaktest.com">DNSLeakTest's</a> <strong>Extended Test</strong> will check your DNS settings and confirm you are now using the DNS resolvers pushed by your VPN.</p>

<p>Congratulations! You are now securely traversing the internet protecting your identity, location, and traffic from snoopers and censors.</p>

    