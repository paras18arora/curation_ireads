<link rel='stylesheet' href='digital_ocean.css'><br> <br> 
      <h3 id="introduction">Introduction</h3>

<p>OpenVPN is an open source VPN application that lets you create and join a private network securely over the public Internet. In short, this allows the end user to mask connections and more securely navigate an untrusted network.</p>

<p>With that said, this tutorial teaches you how to setup OpenVPN, an open source Secure Socket Layer (SSL) VPN solution, on Debian 8.</p>

<h2 id="prerequisites">Prerequisites</h2>

<p>This tutorial assumes you have the following:</p>

<ul>
<li>One fresh Debian 8.1 Droplet</li>
<li>A root user</li>
<li>Optional: After completion of this tutorial, use a sudo-enabled, non-root account for general maintenance; you can set one up by following steps 2 and 3 of <a href="https://indiareads/community/tutorials/initial-server-setup-with-debian-8">this tutorial</a></li>
</ul>

<h2 id="step-1-—-install-openvpn">Step 1 — Install OpenVPN</h2>

<p>Before installing any packages, update the apt package index.</p>
<pre class="code-pre super_user"><code langs=""><ul class="prefixed"><li class="line" prefix="#">apt-get update
</li></ul></code></pre>
<p>Now, we can install the OpenVPN server along with easy-RSA for encryption.</p>
<pre class="code-pre super_user"><code langs=""><ul class="prefixed"><li class="line" prefix="#">apt-get install openvpn easy-rsa
</li></ul></code></pre>
<h2 id="step-2-—-configure-openvpn">Step 2 — Configure OpenVPN</h2>

<p>The example VPN server configuration file needs to be extracted to <code>/etc/openvpn</code> so we can incorporate it into our setup. This can be done with one command:</p>
<pre class="code-pre super_user"><code langs=""><ul class="prefixed"><li class="line" prefix="#">gunzip -c /usr/share/doc/openvpn/examples/sample-config-files/server.conf.gz > /etc/openvpn/server.conf
</li></ul></code></pre>
<p>Once extracted, open the server configuration file using nano or your favorite text editor.</p>
<pre class="code-pre super_user"><code langs=""><ul class="prefixed"><li class="line" prefix="#">nano /etc/openvpn/server.conf
</li></ul></code></pre>
<p>In this file, we will need to make four changes (each will be explained in detail):</p>

<ol>
<li>Secure server with higher-level encryption</li>
<li>Forward web traffic to destination</li>
<li>Prevent DNS requests from leaking outside the VPN connection</li>
<li>Setup permissions</li>
</ol>

<p>First, we'll double the RSA key length used when generating server and client keys. After the main comment block and several more chunks, search for the line that reads:</p>
<div class="code-label " title="/etc/openvpn/server.conf">/etc/openvpn/server.conf</div><pre class="code-pre "><code langs=""># Diffie hellman parameters.
# Generate your own with:
#   openssl dhparam -out dh1024.pem 1024
# Substitute 2048 for 1024 if you are using
# 2048 bit keys.
dh <span class="highlight">dh1024.pem</span>
</code></pre>
<p>Change <code>dh1024.pem</code> to <code>dh2048.pem</code>, so that the line now reads:</p>
<div class="code-label " title="/etc/openvpn/server.conf">/etc/openvpn/server.conf</div><pre class="code-pre "><code langs="">dh  <span class="highlight">dh2048.pem</span>
</code></pre>
<p>Second, we'll make sure to redirect all traffic to the proper location. Still in <code>server.conf</code>, scroll past more comment blocks, and look for the following section:</p>
<div class="code-label " title="/etc/openvpn/server.conf">/etc/openvpn/server.conf</div><pre class="code-pre "><code langs=""># If enabled, this directive will configure
# all clients to redirect their default
# network gateway through the VPN, causing
# all IP traffic such as web browsing and
# and DNS lookups to go through the VPN
# (The OpenVPN server machine may need to NAT
# or bridge the TUN/TAP interface to the internet
# in order for this to work properly).
<span class="highlight">;</span>push "redirect-gateway def1 bypass-dhcp"
</code></pre>
<p>Uncomment <code>push "redirect-gateway def1 bypass-dhcp"</code> so the VPN server passes on clients' web traffic to its destination. It should look like this when done:</p>
<div class="code-label " title="/etc/openvpn/server.conf">/etc/openvpn/server.conf</div><pre class="code-pre "><code langs="">push "redirect-gateway def1 bypass-dhcp"
</code></pre>
<p>Third, we will tell the server to use <a href="https://opendns.com">OpenDNS</a> for DNS resolution where possible. This can help prevent DNS requests from leaking outside the VPN connection. Immediately after the previously modified block, edit the following:</p>
<div class="code-label " title="/etc/openvpn/server.conf">/etc/openvpn/server.conf</div><pre class="code-pre "><code langs=""># Certain Windows-specific network settings
# can be pushed to clients, such as DNS
# or WINS server addresses.  CAVEAT:
# http://openvpn.net/faq.html#dhcpcaveats
# The addresses below refer to the public
# DNS servers provided by opendns.com.
<span class="highlight">;</span>push "dhcp-option DNS 208.67.222.222"
<span class="highlight">;</span>push "dhcp-option DNS 208.67.220.220"
</code></pre>
<p>Uncomment <code>push "dhcp-option DNS 208.67.222.222"</code> and <code>push "dhcp-option DNS 208.67.220.220"</code>. It should look like this when done:</p>
<div class="code-label " title="/etc/openvpn/server.conf">/etc/openvpn/server.conf</div><pre class="code-pre "><code langs="">push "dhcp-option DNS 208.67.222.222"
push "dhcp-option DNS 208.67.220.220"
</code></pre>
<p>Fourth, we will define permissions in <code>server.conf</code>:</p>
<div class="code-label " title="/etc/openvpn/server.conf">/etc/openvpn/server.conf</div><pre class="code-pre "><code langs=""># You can uncomment this out on
# non-Windows systems.
;user nobody
;group nogroup
</code></pre>
<p>Uncomment both <code>user nobody</code> and <code>group nogroup</code>. It should look like this when done:</p>
<div class="code-label " title="/etc/openvpn/server.conf">/etc/openvpn/server.conf</div><pre class="code-pre "><code langs="">user nobody
group nogroup
</code></pre>
<p>By default, OpenVPN runs as the <strong>root</strong> user and thus has full root access to the system. We'll instead confine OpenVPN to the user <strong>nobody</strong> and group <strong>nogroup</strong>. This is an unprivileged user with no default login capabilities, often reserved for running untrusted applications like web-facing servers.</p>

<p>Now save your changes and exit.</p>

<h2 id="step-3-—-enable-packet-forwarding">Step 3 — Enable Packet Forwarding</h2>

<p>In this section, we will tell the server's kernel to forward traffic from client services out to the Internet. Otherwise, the traffic will stop at the server.</p>

<p>Enable packet forwarding during runtime by entering this command:</p>
<pre class="code-pre super_user"><code langs=""><ul class="prefixed"><li class="line" prefix="#">echo 1 > /proc/sys/net/ipv4/ip_forward
</li></ul></code></pre>
<p>Next, we'll need to make this permanent so that this setting persists after a server reboot. Open the <code>sysctl</code> configuration file using nano or your favorite text editor.</p>
<pre class="code-pre super_user"><code langs=""><ul class="prefixed"><li class="line" prefix="#">nano /etc/sysctl.conf
</li></ul></code></pre>
<p>Near the top of the <code>sysctl</code> file, you will see:</p>
<div class="code-label " title="/etc/openvpn/server.conf">/etc/openvpn/server.conf</div><pre class="code-pre "><code langs=""># Uncomment the next line to enable packet forwarding for IPv4
<span class="highlight">#net.ipv4.ip_forward=1</span>
</code></pre>
<p>Uncomment <code>net.ipv4.ip_forward</code>. It should look like this when done:</p>
<div class="code-label " title="/etc/openvpn/server.conf">/etc/openvpn/server.conf</div><pre class="code-pre "><code langs=""># Uncomment the next line to enable packet forwarding for IPv4
<span class="highlight">net.ipv4.ip_forward=1</span>
</code></pre>
<p>Save your changes and exit.</p>

<h2 id="step-4-—-install-and-configure-ufw">Step 4 — Install and Configure ufw</h2>

<p>UFW is a front-end for IPTables. We only need to make a few rules and configuration edits. Then we will switch the firewall on. As a reference for more uses for UFW, see <a href="https://indiareads/community/articles/how-to-setup-a-firewall-with-ufw-on-an-ubuntu-and-debian-cloud-server">How To Setup a Firewall with UFW on an Ubuntu and Debian Cloud Server</a>.</p>

<p>First, install the <code>ufw</code> package.</p>
<pre class="code-pre super_user"><code langs=""><ul class="prefixed"><li class="line" prefix="#">apt-get install ufw
</li></ul></code></pre>
<p>Second, set UFW to allow SSH:</p>
<pre class="code-pre super_user"><code langs=""><ul class="prefixed"><li class="line" prefix="#">ufw allow ssh
</li></ul></code></pre>
<p>This tutorial will use OpenVPN over UDP, so UFW must also allow UDP traffic over port <code>1194</code>.</p>
<pre class="code-pre super_user"><code langs=""><ul class="prefixed"><li class="line" prefix="#">ufw allow 1194/udp
</li></ul></code></pre>
<p>The UFW forwarding policy needs to be set as well. We'll do this in the primary configuration file.</p>
<pre class="code-pre super_user"><code langs=""><ul class="prefixed"><li class="line" prefix="#">nano /etc/default/ufw
</li></ul></code></pre>
<p>Look for the following line:</p>
<div class="code-label " title="/etc/default/ufw">/etc/default/ufw</div><pre class="code-pre "><code langs="">DEFAULT_FORWARD_POLICY="<span class="highlight">DROP</span>"
</code></pre>
<p>This must be changed from <code>DROP</code> to <code>ACCEPT</code>. It should look like this when done:</p>
<div class="code-label " title="/etc/default/ufw">/etc/default/ufw</div><pre class="code-pre "><code langs="">DEFAULT_FORWARD_POLICY="<span class="highlight">ACCEPT</span>"
</code></pre>
<p>Save and exit.</p>

<p>Next we will add additional UFW rules for network address translation and IP masquerading of connected clients.</p>
<pre class="code-pre super_user"><code langs=""><ul class="prefixed"><li class="line" prefix="#">nano /etc/ufw/before.rules
</li></ul></code></pre>
<p>Next, add the area in <span class="highlight">red</span> for <strong>OPENVPN RULES</strong>:</p>
<div class="code-label " title="/etc/ufw/before.rules">/etc/ufw/before.rules</div><pre class="code-pre "><code langs="">#
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
<p>Save and exit.</p>

<p>With the changes made to UFW, we can now enable it. Enter into the command prompt:</p>
<pre class="code-pre super_user"><code langs=""><ul class="prefixed"><li class="line" prefix="#">ufw enable
</li></ul></code></pre>
<p>Enabling UFW will return the following prompt:</p>
<pre class="code-pre "><code langs="">Command may disrupt existing ssh connections. Proceed with operation (y|n)?
</code></pre>
<p>Answer <code>y</code>. The result will be this output:</p>
<pre class="code-pre "><code langs="">Firewall is active and enabled on system startup
</code></pre>
<p>To check UFW's primary firewall rules:</p>
<pre class="code-pre super_user"><code langs=""><ul class="prefixed"><li class="line" prefix="#">ufw status
</li></ul></code></pre>
<p>The status command should return these entries:</p>
<pre class="code-pre "><code langs="">Status: active

To                         Action      From
--                         ------      ----
22                         ALLOW       Anywhere
1194/udp                   ALLOW       Anywhere
22 (v6)                    ALLOW       Anywhere (v6)
1194/udp (v6)              ALLOW       Anywhere (v6)
</code></pre>
<h2 id="step-5-—-configure-and-build-the-certificate-authority">Step 5 — Configure and Build the Certificate Authority</h2>

<p>OpenVPN uses certificates to encrypt traffic.</p>

<p>In this section, we will setup our own Certificate Authority (CA) in two steps: (1) setup variables and (2) generate the CA.</p>

<p>OpenVPN supports bidirectional authentication based on certificates, meaning that the client must authenticate the server certificate and the server must authenticate the client certificate before mutual trust is established. We will use Easy RSA's scripts to do this.</p>

<p>First copy over the Easy-RSA generation scripts.</p>
<pre class="code-pre super_user"><code langs=""><ul class="prefixed"><li class="line" prefix="#">cp -r /usr/share/easy-rsa/ /etc/openvpn
</li></ul></code></pre>
<p>Then, create a directory to house the key.</p>
<pre class="code-pre super_user"><code langs=""><ul class="prefixed"><li class="line" prefix="#">mkdir /etc/openvpn/easy-rsa/keys
</li></ul></code></pre>
<p>Next, we will set parameters for our certificate. Open the variables file using nano or your favorite text editor.</p>
<pre class="code-pre super_user"><code langs=""><ul class="prefixed"><li class="line" prefix="#">nano /etc/openvpn/easy-rsa/vars
</li></ul></code></pre>
<p>The variables below marked in <span class="highlight">red</span> should be changed according to your preference.</p>
<div class="code-label " title="/etc/openvpn/easy-rsa/vars">/etc/openvpn/easy-rsa/vars</div><pre class="code-pre "><code langs="">export KEY_COUNTRY="<span class="highlight">US</span>"
export KEY_PROVINCE="<span class="highlight">TX</span>"
export KEY_CITY="<span class="highlight">Dallas</span>"
export KEY_ORG="<span class="highlight">My Company Name</span>"
export KEY_EMAIL="<span class="highlight">sammy@example.com</span>"
export KEY_OU="<span class="highlight">MYOrganizationalUnit</span>"
</code></pre>
<p>In the same <code>vars</code> file, also edit this one line shown below. For simplicity, we will use <code>server</code> as the key name. If you want to use a different name, you would also need to update the OpenVPN configuration files that reference <code>server.key</code> and <code>server.crt</code>.</p>

<p>Below, in the same file, we will specify the correct certificate. Look for the line, right after the previously modified block that reads</p>
<div class="code-label " title="/etc/openvpn/easy-rsa/vars">/etc/openvpn/easy-rsa/vars</div><pre class="code-pre "><code langs=""># X509 Subject Field
export KEY_NAME="<span class="highlight">EasyRSA</span>"
</code></pre>
<p>Change <code>KEY_NAME</code>'s default value of <code>EasyRSA</code> to your desired server name. This tutorial will use the name <code><span class="highlight">server</span></code>.</p>
<div class="code-label " title="/etc/openvpn/easy-rsa/vars">/etc/openvpn/easy-rsa/vars</div><pre class="code-pre "><code langs=""># X509 Subject Field
export KEY_NAME="<span class="highlight">server</span>"
</code></pre>
<p>Save and exit.</p>

<p>Next, we will generate the Diffie-Helman parameters using a built-in OpenSSL tool called <code>dhparam</code>; this may take several minutes.</p>

<p>The <code>-out</code> flag specifies where to save the new parameters.</p>
<pre class="code-pre super_user"><code langs=""><ul class="prefixed"><li class="line" prefix="#">openssl dhparam -out /etc/openvpn/dh2048.pem 2048
</li></ul></code></pre>
<p>Our certificate is now generated, and it's time to generate a key.</p>

<p>First, we will switch into the <code>easy-rsa</code> directory.</p>
<pre class="code-pre super_user"><code langs=""><ul class="prefixed"><li class="line" prefix="#">cd /etc/openvpn/easy-rsa
</li></ul></code></pre>
<p>Now, we can begin setting up the CA itself.  First, initialize the Public Key Infrastructure (PKI).</p>

<p>Pay attention to the <strong>dot (.)</strong> and <strong>space</strong> in front of <code>./vars</code> command. That signifies the current working directory (source).</p>
<pre class="code-pre super_user"><code langs=""><ul class="prefixed"><li class="line" prefix="#">. ./vars
</li></ul></code></pre>
<p><span class="note">The following warning will be printed. Do not worry, as the directory specified in the warning is empty. <code>NOTE: If you run ./clean-all, I will be doing a rm -rf on /etc/openvpn/easy-rsa/keys</code>.<br /></span></p>

<p>Next, we'll clear all other keys that may interfere with our installation.</p>
<pre class="code-pre super_user"><code langs=""><ul class="prefixed"><li class="line" prefix="#">./clean-all
</li></ul></code></pre>
<p>Finally, we will build the CA using an OpenSSL command. This command will prompt you for a confirmation of "Distinguished Name" variables that were entered earlier. Press <code>ENTER</code> to accept existing values.</p>
<pre class="code-pre super_user"><code langs=""><ul class="prefixed"><li class="line" prefix="#">./build-ca
</li></ul></code></pre>
<p>Press <code>ENTER</code> to pass through each prompt since you just set their values in the <code>vars</code> file.</p>

<p>The Certificate Authority is now setup.</p>

<h2 id="step-6-—-generate-a-certificate-and-key-for-the-server">Step 6 — Generate a Certificate and Key for the Server</h2>

<p>In this section, we will setup and launch our OpenVPN server.</p>

<p>First, still working from <code>/etc/openvpn/easy-rsa</code>, build your key with the server name. This was specified earlier as <code>KEY_NAME</code> in your configuration file. The default for this tutorial is <code>server</code>.</p>
<pre class="code-pre super_user"><code langs=""><ul class="prefixed"><li class="line" prefix="#">./build-key-server <span class="highlight">server</span>
</li></ul></code></pre>
<p>Again, output will ask for confirmation of the Distinguished Name. Hit <code>ENTER</code> to accept defined, default values. This time, there will be two additional prompts.</p>
<pre class="code-pre "><code langs="">Please enter the following 'extra' attributes
to be sent with your certificate request
A challenge password []:
An optional company name []:
</code></pre>
<p>Both should be left blank, so just press ENTER to pass through each one.</p>

<p>Two additional queries at the end require a positive (<code>y</code>) response:</p>
<pre class="code-pre "><code langs="">Sign the certificate? [y/n]
1 out of 1 certificate requests certified, commit? [y/n]
</code></pre>
<p>You will then be prompted with the following, indicating success.</p>
<pre class="code-pre "><code langs=""><div class="secondary-code-label " title="Output">Output</div>Write out database with 1 new entries
Data Base Updated
</code></pre>
<h2 id="step-7-—-move-the-server-certificates-and-keys">Step 7 — Move the Server Certificates and Keys</h2>

<p>We will now copy the certificate and key to <code>/etc/openvpn</code>, as OpenVPN will search in that directory for the server's CA, certificate, and key.</p>
<pre class="code-pre super_user"><code langs=""><ul class="prefixed"><li class="line" prefix="#">cp /etc/openvpn/easy-rsa/keys/{<span class="highlight">server</span>.crt,<span class="highlight">server</span>.key,ca.crt} /etc/openvpn
</li></ul></code></pre>
<p>You can verify the copy was successful with:</p>
<pre class="code-pre super_user"><code langs=""><ul class="prefixed"><li class="line" prefix="#">ls /etc/openvpn
</li></ul></code></pre>
<p>You should see the certificate and key files for the server.</p>

<p>At this point, the OpenVPN server is ready to go. Start it and check the status.</p>
<pre class="code-pre super_user"><code langs=""><ul class="prefixed"><li class="line" prefix="#">service openvpn start
</li><li class="line" prefix="#">service openvpn status
</li></ul></code></pre>
<p>The status command will return something to the following effect:</p>
<pre class="code-pre "><code langs=""><div class="secondary-code-label " title="Output">Output</div>* openvpn.service - OpenVPN service
   Loaded: loaded (/lib/systemd/system/openvpn.service; enabled)
   Active: active (exited) since Thu 2015-06-25 02:20:18 EDT; 9s ago
  Process: 2505 ExecStart=/bin/true (code=exited, status=0/SUCCESS)
 Main PID: 2505 (code=exited, status=0/SUCCESS)
</code></pre>
<p>Most importantly, from the output above, you should find <code>Active: active (exited) since...</code> instead of <code>Active: inactive (dead) since...</code>.</p>

<p>Your OpenVPN server is now operational. If the status message says the VPN is not running, then take a look at the <code>/var/log/syslog</code> file for errors such as:</p>
<pre class="code-pre "><code langs="">Options error: --key fails with 'server.key': No such file or directory
</code></pre>
<p>That error indicates <code>server.key</code> was not copied to <code>/etc/openvpn</code> correctly. Re-copy the file and try again.</p>

<h2 id="step-8-—-generate-certificates-and-keys-for-clients">Step 8 — Generate Certificates and Keys for Clients</h2>

<p>So far we've installed and configured the OpenVPN server, created a Certificate Authority, and created the server's own certificate and key. In this step, we use the server's CA to generate certificates and keys for each client device which will be connecting to the VPN.</p>

<h3 id="key-and-certificate-building">Key and Certificate Building</h3>

<p>It's ideal for each client connecting to the VPN to have its own unique certificate and key. This is preferable to generating one general certificate and key to use among all client devices.</p>

<p><span class="note"><strong>Note:</strong> By default, OpenVPN does not allow simultaneous connections to the server from clients using the same certificate and key. (See <code>duplicate-cn</code> in <code>/etc/openvpn/server.conf</code>.)<br /></span></p>

<p>To create separate authentication credentials for each device you intend to connect to the VPN, you should complete this step for each device, but change the name <code>client1</code> below to something different such as <code>client2</code> or <code>iphone2</code>. With separate credentials per device, they can later be deactivated at the server individually, if need be. The remaining examples in this tutorial will use <code>client1</code> as our example client device's name.</p>

<p>As we did with the server's key, now we build one for our <code>client1</code> example. You should still be working out of <code>/etc/openvpn/easy-rsa</code>.</p>
<pre class="code-pre super_user"><code langs=""><ul class="prefixed"><li class="line" prefix="#">./build-key client1
</li></ul></code></pre>
<p>Once again, you'll be asked to change or confirm the Distinguished Name variables and these two prompts which should be left blank. Press <code>ENTER</code> to accept the defaults.</p>
<pre class="code-pre "><code langs="">Please enter the following 'extra' attributes
to be sent with your certificate request
A challenge password []:
An optional company name []:
</code></pre>
<p>As before, these two confirmations at the end of the build process require a (<code>y</code>) response:</p>
<pre class="code-pre "><code langs="">Sign the certificate? [y/n]
1 out of 1 certificate requests certified, commit? [y/n]
</code></pre>
<p>You will then receive the following output, confirming successful key build.</p>
<pre class="code-pre "><code langs="">Write out database with 1 new entries.
Data Base Updated
</code></pre>
<p>Then, we'll copy the generated key to the Easy-RSA <code>keys</code> directory that we created earlier. Note that we change the extension from <code>.conf</code> to <code>.ovpn</code>. This is to match convention.</p>
<pre class="code-pre super_user"><code langs=""><ul class="prefixed"><li class="line" prefix="#">cp /usr/share/doc/openvpn/examples/sample-config-files/client.conf /etc/openvpn/easy-rsa/keys/client.ovpn
</li></ul></code></pre>
<p>You can repeat this section again for each client, replacing <code>client1</code> with the appropriate client name throughout.</p>

<p><strong>Note:</strong> The name of your duplicated <code>client.ovpn</code> doesn't need to be related to the client device. The client-side OpenVPN application will use the filename as an identifier for the VPN connection itself. Instead, you should duplicate <code>client.ovpn</code> to whatever you want the VPN's name tag to be in your operating system. For example: <strong>work.ovpn</strong> will be identified as <strong>work</strong>, <strong>school.ovpn</strong> as <strong>school</strong>, etc.</p>

<p>We need to modify each client file to include the IP address of the OpenVPN server so it knows what to connect to. Open <code>client.ovpn</code> using nano or your favorite text editor.</p>
<pre class="code-pre super_user"><code langs=""><ul class="prefixed"><li class="line" prefix="#">nano /etc/openvpn/easy-rsa/keys/<span class="highlight">client.ovpn</span>
</li></ul></code></pre>
<p>First, edit the line starting with <code>remote</code>. Change <code>my-server-1</code> to <code><span class="highlight">your_server_ip</span></code>.</p>
<div class="code-label " title=" /etc/openvpn/easy-rsa/keys/client.ovpn"> /etc/openvpn/easy-rsa/keys/client.ovpn</div><pre class="code-pre "><code langs=""># The hostname/IP and port of the server.
# You can have multiple remote entries
# to load balance between the servers.
remote <span class="highlight">your_server_ip</span> 1194
</code></pre>
<p>Next, find the area shown below and uncomment <code>user nobody</code> and <code>group nogroup</code>, just like we did in <code>server.conf</code> in Step 1. <strong>Note:</strong> This doesn't apply to Windows so you can skip it. It should look like this when done:</p>
<div class="code-label " title=" /etc/openvpn/easy-rsa/keys/client.ovpn"> /etc/openvpn/easy-rsa/keys/client.ovpn</div><pre class="code-pre "><code langs=""># Downgrade privileges after initialization (non-Windows only)
user nobody
group no group
</code></pre>
<h3 id="transferring-certificates-and-keys-to-client-devices">Transferring Certificates and Keys to Client Devices</h3>

<p>Recall from the steps above that we created the client certificates and keys, and that they are stored on the OpenVPN server in the <code>/etc/openvpn/easy-rsa/keys</code> directory.</p>

<p>For each client we need to transfer the client certificate, key, and profile template files to a folder on our local computer or another client device.</p>

<p>In this example, our <code>client1</code> device requires its certificate and key, located on the server in:</p>

<ul>
<li>  <code>/etc/openvpn/easy-rsa/keys/<span class="highlight">client1</span>.crt</code></li>
<li>  <code>/etc/openvpn/easy-rsa/keys/<span class="highlight">client1</span>.key</code></li>
</ul>

<p>The <code>ca.crt</code> and <code>client.ovpn</code> files are the same for all clients. Download these two files as well; note that the <code>ca.crt</code> file is in a different directory than the others.</p>

<ul>
<li>  <code>/etc/openvpn/easy-rsa/keys/client.ovpn</code></li>
<li>  <code>/etc/openvpn/ca.crt</code></li>
</ul>

<p>While the exact applications used to accomplish this transfer will depend on your choice and device's operating system, you want the application to use SFTP (SSH file transfer protocol) or SCP (Secure Copy) on the backend. This will transport your client's VPN authentication files over an encrypted connection.</p>

<p>Here is an example SCP command using our <code>client1</code> example. It places the file <code>client1.key</code> into the <strong>Downloads</strong> directory on the local computer.</p>
<pre class="code-pre super_user"><code langs=""><ul class="prefixed"><li class="line" prefix="#">scp root@your-server-ip:/etc/openvpn/easy-rsa/keys/client1.key Downloads/
</li></ul></code></pre>
<p>Here are several tools and tutorials for securely transferring files from the server to a local computer:</p>

<ul>
<li>  <a href="http://winscp.net">WinSCP</a></li>
<li>  <a href="https://indiareads/community/tutorials/how-to-use-sftp-to-securely-transfer-files-with-a-remote-server">How To Use SFTP to Securely Transfer Files with a Remote Server</a></li>
<li>  <a href="https://indiareads/community/tutorials/how-to-use-filezilla-to-transfer-and-manage-files-securely-on-your-vps">How To Use Filezilla to Transfer and Manage Files Securely on your VPS</a></li>
</ul>

<p>At the end of this section, make sure you have these four files on your <strong>client</strong> device:</p>

<ul>
<li>  `<code>client1</code>.crt`</li>
<li>  `<code>client1</code>.key`</li>
<li>  <code>client.ovpn</code></li>
<li>  <code>ca.crt</code></li>
</ul>

<h2 id="step-9-—-creating-a-unified-openvpn-profile-for-client-devices">Step 9 — Creating a Unified OpenVPN Profile for Client Devices</h2>

<p>There are several methods for managing the client files but the easiest uses a <em>unified</em> profile. This is created by modifying the <code>client.ovpn</code> template file to include the server's Certificate Authority, and the client's certificate and its key. Once merged, only the single <code>client.ovpn</code> profile needs to be imported into the client's OpenVPN application.</p>

<p>The area given below needs the three lines shown to be commented out so we can instead include the certificate and key directly in the <code>client.ovpn</code> file. It should look like this when done:</p>
<div class="code-label " title=" /etc/openvpn/easy-rsa/keys/client.ovpn"> /etc/openvpn/easy-rsa/keys/client.ovpn</div><pre class="code-pre "><code langs=""># SSL/TLS parms.
# . . .
;ca ca.crt
;cert client.crt
;key client.key
</code></pre>
<p>Save the changes and exit. We will add the certificates by code.</p>

<p>First, add the Certificate Authority.</p>
<pre class="code-pre super_user"><code langs=""><ul class="prefixed"><li class="line" prefix="#">echo '<ca>' >> /etc/openvpn/easy-rsa/keys/client.ovpn
</li><li class="line" prefix="#">cat /etc/openvpn/ca.crt >> /etc/openvpn/easy-rsa/keys/client.ovpn
</li><li class="line" prefix="#">echo '</ca>' >> /etc/openvpn/easy-rsa/keys/client.ovpn
</li></ul></code></pre>
<p>Second, add the certificate.</p>
<pre class="code-pre super_user"><code langs=""><ul class="prefixed"><li class="line" prefix="#">echo '<cert>' >> /etc/openvpn/easy-rsa/keys/client.ovpn
</li><li class="line" prefix="#">cat /etc/openvpn/easy-rsa/keys/client1.crt >> /etc/openvpn/easy-rsa/keys/client.ovpn
</li><li class="line" prefix="#">echo '</cert>' >> /etc/openvpn/easy-rsa/keys/client.ovpn
</li></ul></code></pre>
<p>Third and finally, add the key.</p>
<pre class="code-pre super_user"><code langs=""><ul class="prefixed"><li class="line" prefix="#">echo '<key>' >> /etc/openvpn/easy-rsa/keys/client.ovpn
</li><li class="line" prefix="#">cat /etc/openvpn/easy-rsa/keys/client1.key >> /etc/openvpn/easy-rsa/keys/client.ovpn
</li><li class="line" prefix="#">echo '</key>' >> /etc/openvpn/easy-rsa/keys/client.ovpn
</li></ul></code></pre>
<p>We now have a unified client profile. Using <code>scp</code>, you can then copy the <code>client.ovpn</code> file to your second system.</p>

<h2 id="step-10-—-installing-the-client-profile">Step 10 — Installing the Client Profile</h2>

<p>Various platforms have more user-friendly applications to connect to this OpenVPN server. For platform-specific instructions, see Step 5 in <a href="https://indiareads/community/tutorials/how-to-set-up-an-openvpn-server-on-ubuntu-14-04#step-5---installing-the-client-profile">this tutorial</a>.</p>

<h2 id="conclusion">Conclusion</h2>

<p>Congratulations! You now have a working OpenVPN server and client file.</p>

<p>From your OpenVPN client, you can test the connection using <a href="https://www.google.com/search?q=what%20is%20my%20ip">Google to reveal your public IP</a>. On the client, load it once before starting the OpenVPN connection and once after. The IP address should change.</p>

    