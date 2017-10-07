<link rel='stylesheet' href='digital_ocean.css'><br><img src=https://community-cdn-digitalocean-com.global.ssl.fastly.net/assets/tutorials/images/large/open_vpn_server_tw.jpg?1462909439/> <br> 
      <h3 id="introduction">Introduction</h3>

<p>Want to access the Internet safely and securely from your smartphone or laptop when connected to an untrusted network such as the WiFi of a hotel or coffee shop? A <a href="https://en.wikipedia.org/wiki/Virtual_private_network">Virtual Private Network</a> (VPN) allows you to traverse untrusted networks privately and securely as if you were on a private network. The traffic emerges from the VPN server and continues its journey to the destination. </p>

<p>When combined with <a href="https://en.wikipedia.org/wiki/HTTP_Secure">HTTPS connections</a>, this setup allows you to secure your wireless logins and transactions. You can circumvent geographical restrictions and censorship, and shield your location and any unencrypted HTTP traffic from the untrusted network.</p>

<p><a href="https://openvpn.net">OpenVPN</a> is a full-featured open source Secure Socket Layer (SSL) VPN solution that accommodates a wide range of configurations.  In this tutorial, we'll set up an OpenVPN server on a Droplet and then configure access to it from Windows, OS X, iOS and Android. This tutorial will keep the installation and configuration steps as simple as possible for these setups.</p>

<h2 id="prerequisites">Prerequisites</h2>

<p>To complete this tutorial, you will need access to an Ubuntu 16.04 server.</p>

<p>You will need to configure a non-root user with <code>sudo</code> privileges before you start this guide.  You can follow our <a href="https://indiareads/community/tutorials/initial-server-setup-with-ubuntu-16-04">Ubuntu 16.04 initial server setup guide</a> to set up a user with appropriate permissions.</p>

<p>When you are ready to begin, log into your Ubuntu server as your <code>sudo</code> user and continue below.</p>

<h2 id="step-1-install-openvpn">Step 1: Install OpenVPN</h2>

<p>To start off, we will install OpenVPN onto our server.  OpenVPN is available in Ubuntu's default repositories, so we can use <code>apt</code> for the installation.  We will also be installing the <code>easy-rsa</code> package, which will help us set up an internal CA (certificate authority) for use with our VPN.</p>

<p>To update your server's package index and install the necessary packages type:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo apt-get update
</li><li class="line" prefix="$">sudo apt-get install openvpn easy-rsa
</li></ul></code></pre>
<p>The needed software is now on the server, ready to be configured.</p>

<h2 id="step-2-set-up-the-ca-directory">Step 2: Set Up the CA Directory</h2>

<p>OpenVPN is an TLS/SSL VPN.  This means that it utilizes certificates in order to encrypt traffic between the server and clients.  In order to issue trusted certificates, we will need to set up our own simple certificate authority (CA).</p>

<p>To begin, we can copy the <code>easy-rsa</code> template directory into our home directory with the <code>make-cadir</code> command:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">make-cadir ~/openvpn-ca
</li></ul></code></pre>
<p>Move into the newly created directory to begin configuring the CA:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">cd ~/openvpn-ca
</li></ul></code></pre>
<h2 id="step-3-configure-the-ca-variables">Step 3: Configure the CA Variables</h2>

<p>To configure the values our CA will use, we need to edit the <code>vars</code> file within the directory.  Open that file now in your text editor:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">nano vars
</li></ul></code></pre>
<p>Inside, you will find some variables that can be adjusted to determine how your certificates will be created.  We only need to worry about a few of these.</p>

<p>Towards the bottom of the file, find the settings that set field defaults for new certificates.  It should look something like this:</p>
<div class="code-label " title="~/openvpn-ca/vars">~/openvpn-ca/vars</div><pre class="code-pre "><code langs="">. . .

export KEY_COUNTRY="US"
export KEY_PROVINCE="CA"
export KEY_CITY="SanFrancisco"
export KEY_ORG="Fort-Funston"
export KEY_EMAIL="me@myhost.mydomain"
export KEY_OU="MyOrganizationalUnit"

. . .
</code></pre>
<p>Edit the values in red to whatever you'd prefer, but do not leave them blank:</p>
<div class="code-label " title="~/openvpn-ca/vars">~/openvpn-ca/vars</div><pre class="code-pre "><code langs="">. . .

export KEY_COUNTRY="<span class="highlight">US</span>"
export KEY_PROVINCE="<span class="highlight">NY</span>"
export KEY_CITY="<span class="highlight">New York City</span>"
export KEY_ORG="<span class="highlight">IndiaReads</span>"
export KEY_EMAIL="<span class="highlight">admin@example.com</span>"
export KEY_OU="<span class="highlight">Community</span>"

. . .
</code></pre>
<p>While we are here, we will also edit the <code>KEY_NAME</code> value just below this section, which populates the subject field.  To keep this simple, we'll call it <code>server</code> in this guide:</p>
<div class="code-label " title="~/openvpn-ca/vars">~/openvpn-ca/vars</div><pre class="code-pre "><code langs="">export KEY_NAME="<span class="highlight">server</span>"
</code></pre>
<p>When you are finished, save and close the file.</p>

<h2 id="step-4-build-the-certificate-authority">Step 4: Build the Certificate Authority</h2>

<p>Now, we can use the variables we set and the <code>easy-rsa</code> utilities to build our certificate authority.</p>

<p>Ensure you are in your CA directory, and then source the <code>vars</code> file you just edited:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">cd ~/openvpn-ca
</li><li class="line" prefix="$">source vars
</li></ul></code></pre>
<p>You should see the following if it was sourced correctly:</p>
<pre class="code-pre "><code langs=""><div class="secondary-code-label " title="Output">Output</div>NOTE: If you run ./clean-all, I will be doing a rm -rf on /home/sammy/openvpn-ca/keys
</code></pre>
<p>Make sure we're operating in a clean environment by typing:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">./clean-all
</li></ul></code></pre>
<p>Now, we can build our root CA by typing:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">./build-ca
</li></ul></code></pre>
<p>This will initiate the process of creating the root certificate authority key and certificate.  Since we filled out the <code>vars</code> file, all of the values should be populated automatically.  Just press <strong>ENTER</strong> through the prompts to confirm the selections:</p>
<pre class="code-pre "><code langs=""><div class="secondary-code-label " title="Output">Output</div>Generating a 2048 bit RSA private key
..........................................................................................+++
...............................+++
writing new private key to 'ca.key'
-----
You are about to be asked to enter information that will be incorporated
into your certificate request.
What you are about to enter is what is called a Distinguished Name or a DN.
There are quite a few fields but you can leave some blank
For some fields there will be a default value,
If you enter '.', the field will be left blank.
-----
Country Name (2 letter code) [US]:
State or Province Name (full name) [NY]:
Locality Name (eg, city) [New York City]:
Organization Name (eg, company) [IndiaReads]:
Organizational Unit Name (eg, section) [Community]:
Common Name (eg, your name or your server's hostname) [IndiaReads CA]:
Name [server]:
Email Address [admin@email.com]:
</code></pre>
<p>We now have a CA that can be used to create the rest of the files we need.</p>

<h2 id="step-5-create-the-server-certificate-key-and-encryption-files">Step 5: Create the Server Certificate, Key, and Encryption Files</h2>

<p>Next, we will generate our server certificate and key pair, as well as some additional files used during the encryption process.</p>

<p>Start by generating the OpenVPN server certificate and key pair.  We can do this by typing:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">./build-key-server server
</li></ul></code></pre>
<p>Once again, the prompts will have default values based on the argument we just passed in (<code>server</code>) and the contents of our <code>vars</code> file we sourced.</p>

<p>Feel free to accept the default values by pressing <strong>ENTER</strong>.  Do <em>not</em> enter a challenge password for this setup.  Towards the end, you will have to enter <strong>y</strong> to two questions to sign and commit the certificate:</p>
<pre class="code-pre "><code langs=""><div class="secondary-code-label " title="Output">Output</div>. . .

Certificate is to be certified until May  1 17:51:16 2026 GMT (3650 days)
Sign the certificate? [y/n]:<span class="highlight">y</span>


1 out of 1 certificate requests certified, commit? [y/n]<span class="highlight">y</span>
Write out database with 1 new entries
Data Base Updated
</code></pre>
<p>Next, we'll generate a few other items.  We can generate a strong Diffie-Hellman keys to use during key exchange by typing:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">./build-dh
</li></ul></code></pre>
<p>This might take a few minutes to complete.</p>

<p>Afterwards, we can generate an HMAC signature to strengthen the server's TLS integrity verification capabilities:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">openvpn --genkey --secret keys/ta.key
</li></ul></code></pre>
<h2 id="step-6-generate-a-client-certificate-and-key-pair">Step 6: Generate a Client Certificate and Key Pair</h2>

<p>Next, we can generate a client certificate and key pair.  Although this can be done on the client machine and then signed by the server/CA for security purposes, for this guide we will generate the signed key on the server for the sake of simplicity.</p>

<p>We will generate a single client key/certificate for this guide, but if you have more than one client, you can repeat this process as many times as you'd like.  Pass in a unique value to the script for each client.</p>

<p>Because you may come back to this step at a later time, we'll re-source the <code>vars</code> file.  We will use <code>client1</code> as the value for our first certificate/key pair for this guide:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">cd ~/openvpn-ca
</li><li class="line" prefix="$">source vars
</li><li class="line" prefix="$">./build-key <span class="highlight">client1</span>
</li></ul></code></pre>
<p>Again, the defaults should be populated, so you can just hit <strong>ENTER</strong> to continue.  Leave the challenge password blank and make sure to enter <strong>y</strong> for the prompts that ask whether to sign and commit the certificate.</p>

<h2 id="step-7-configure-the-openvpn-service">Step 7: Configure the OpenVPN Service</h2>

<p>Next, we can begin configuring the OpenVPN service using the credentials and files we've generated.</p>

<h3 id="copy-the-files-to-the-openvpn-directory">Copy the Files to the OpenVPN Directory</h3>

<p>To begin, we need to copy the files we need to the <code>/etc/openvpn</code> configuration directory.</p>

<p>We can start with all of the files that we just generated.  These were placed within the <code>~/openvpn-ca/keys</code> directory as they were created.  We need to move our CA cert and key, our server cert and key, the HMAC signature, and the Diffie-Hellman file:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">cd ~/openvpn-ca/keys
</li><li class="line" prefix="$">sudo cp ca.crt ca.key server.crt server.key ta.key dh2048.pem /etc/openvpn
</li></ul></code></pre>
<p>Next, we need to copy and unzip a sample OpenVPN configuration file into configuration directory so that we can use it as a basis for our setup:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">gunzip -c /usr/share/doc/openvpn/examples/sample-config-files/server.conf.gz | sudo tee /etc/openvpn/server.conf
</li></ul></code></pre>
<h3 id="adjust-the-openvpn-configuration">Adjust the OpenVPN Configuration</h3>

<p>Now that our files are in place, we can modify the server configuration file:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo nano /etc/openvpn/server.conf
</li></ul></code></pre>
<p>First, let's uncomment a few directives that will configure client machines to redirect all web traffic through the VPN.  Find the <code>redirect-gateway</code> section and remove the semicolon "<strong>;</strong>" from the beginning of the <code>redirect-gateway</code> line to uncomment it:</p>
<div class="code-label " title="/etc/openvpn/server.conf">/etc/openvpn/server.conf</div><pre class="code-pre "><code langs="">push "redirect-gateway def1 bypass-dhcp"
</code></pre>
<p>Just below this, find the <code>dhcp-option</code> section.  Again, remove the "<strong>;</strong>" from in front of both of the lines to uncomment them:</p>
<div class="code-label " title="/etc/openvpn/server.conf">/etc/openvpn/server.conf</div><pre class="code-pre "><code langs="">push "dhcp-option DNS 208.67.222.222"
push "dhcp-option DNS 208.67.220.220"
</code></pre>
<p>Next, find the HMAC section by looking for the <code>tls-auth</code> directive.  Remove the "<strong>;</strong>" to uncomment the <code>tls-auth</code> line.  Below this, add the <code>key-direction</code> parameter set to "0":</p>
<div class="code-label " title="/etc/openvpn/server.conf">/etc/openvpn/server.conf</div><pre class="code-pre "><code langs="">tls-auth ta.key 0 # This file is secret
<span class="highlight">key-direction 0</span>
</code></pre>
<p>Finally, find the <code>user</code> and <code>group</code> settings and remove the "<strong>;</strong>" at the beginning of to uncomment those lines:</p>
<div class="code-label " title="/etc/openvpn/server.conf">/etc/openvpn/server.conf</div><pre class="code-pre "><code langs="">user nobody
group nogroup
</code></pre>
<p>When you are finished, save and close the file.</p>

<h2 id="step-8-adjust-the-server-networking-configuration">Step 8: Adjust the Server Networking Configuration</h2>

<p>Next, we need to adjust some aspects of the server's networking so that OpenVPN can correctly route traffic.</p>

<h3 id="allow-ip-forwarding">Allow IP Forwarding</h3>

<p>First, we need to allow the server to forward traffic.  This is fairly essential to the functionality we want our VPN server to provide.</p>

<p>We can adjust this setting by modifying the <code>/etc/sysctl.conf</code> file:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo nano /etc/sysctl.conf
</li></ul></code></pre>
<p>Inside, look for the line that sets <code>net.ipv4.ip_forward</code>.  Remove the "<strong>#</strong>" character from the beginning of the line to uncomment that setting:</p>
<div class="code-label " title="/etc/sysctl.conf">/etc/sysctl.conf</div><pre class="code-pre "><code langs="">net.ipv4.ip_forward=1
</code></pre>
<p>Save and close the file when you are finished.</p>

<p>To read the file and adjust the values for the current session, type:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo sysctl -p
</li></ul></code></pre>
<h3 id="adjust-the-ufw-rules-to-masquerade-client-connections">Adjust the UFW Rules to Masquerade Client Connections</h3>

<p>If you followed the Ubuntu 16.04 initial server setup guide in the prerequisites, you should have the UFW firewall in place.  Regardless of whether you use the firewall to block unwanted traffic (which you almost always should do), we need the firewall in this guide to manipulate some of the traffic coming into the server.  We need to modify the rules file to set up masquerading, an <code>iptables</code> concept that provides on-the-fly dynamic NAT to correctly route client connections.</p>

<p>Open the <code>/etc/ufw/before.rules</code> file to add the relevant configuration:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo nano /etc/ufw/before.rules
</li></ul></code></pre>
<p>This file handles configuration that should be put into place before the conventional UFW rules are loaded.  Towards the top of the file, add the highlighted lines below.  This will set the default policy for the <code>POSTROUTING</code> chain in the <code>nat</code> table and masquerade any traffic coming from the VPN:</p>
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
. . .
</code></pre>
<p>In the above lines, you may need to modify the <code>-A POSTROUTING</code> line to match your public network interface.  To find your public interface, back at the command line, type:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">ip route | grep default
</li></ul></code></pre>
<p>Your public interface should follow the word "dev".  For example, this result shows the interface named <code>wlp11s0</code>, which is highlighted below:</p>
<pre class="code-pre "><code langs=""><div class="secondary-code-label " title="Output">Output</div>default via 192.168.1.1 dev <span class="highlight">wlp11s0</span>  proto static  metric 600
</code></pre>
<p>Use the results you find to modify the <code>-A POSTROUTING</code> rule to change the interface to match your server:</p>
<div class="code-label " title="/etc/ufw/before.rules">/etc/ufw/before.rules</div><pre class="code-pre "><code langs="">#
# rules.before
#
# Rules that should be run before the ufw command line added rules. Custom
# rules should be added to one of these chains:
#   ufw-before-input
#   ufw-before-output
#   ufw-before-forward
#

# START OPENVPN RULES
# NAT table rules
*nat
:POSTROUTING ACCEPT [0:0]
# Allow traffic from OpenVPN client to eth0
-A POSTROUTING -s 10.8.0.0/8 -o <span class="highlight">wlp11s0</span> -j MASQUERADE
COMMIT
# END OPENVPN RULES

# Don't delete these required lines, otherwise there will be errors
*filter
. . .
</code></pre>
<p>Save and close the file when you are finished.</p>

<p>We need to tell UFW to allow forwarded packages by default as well.  To do this, we will open the <code>/etc/default/ufw</code> file:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo nano /etc/default/ufw
</li></ul></code></pre>
<p>Inside, find the <code>DEFAULT_FORWARD_POLICY</code> directive.  We will change the value from <code>DROP</code> to <code>ACCEPT</code>:</p>
<div class="code-label " title="/etc/default/ufw">/etc/default/ufw</div><pre class="code-pre "><code langs="">DEFAULT_FORWARD_POLICY="<span class="highlight">ACCEPT</span>"
</code></pre>
<p>Save and close the file when you are finished.</p>

<h3 id="open-the-openvpn-port-and-enable-the-changes">Open the OpenVPN Port and Enable the Changes</h3>

<p>Next, we'll adjust the firewall itself to allow UDP traffic to port 1194:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo ufw allow 1194/udp
</li></ul></code></pre>
<p>Now, we can disable and re-enable UFW to load the changes from all of the files we've modified:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo ufw disable
</li><li class="line" prefix="$">sudo ufw enable
</li></ul></code></pre>
<p>Our server is now configured to correctly handle OpenVPN traffic.</p>

<h2 id="step-9-start-and-enable-the-openvpn-service">Step 9: Start and Enable the OpenVPN Service</h2>

<p>We're finally ready to start the OpenVPN service on our server.  We can do this using systemd.</p>

<p>We need to start the OpenVPN server by specifying our configuration file name as an instance variable after the systemd unit file name.  Our configuration file for our server is called <code>/etc/openvpn/<span class="highlight">server</span>.conf</code>, so we will add <code><span class="highlight">@server</span></code> to end of our unit file when calling it:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo systemctl start openvpn@<span class="highlight">server</span>
</li></ul></code></pre>
<p>Double-check that the service has started successfully by typing:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo systemctl status openvpn@server
</li></ul></code></pre>
<p>If everything went well, your output should look something that looks like this:</p>
<pre class="code-pre "><code langs=""><div class="secondary-code-label " title="Output">Output</div>● openvpn@server.service - OpenVPN connection to server
   Loaded: loaded (/lib/systemd/system/openvpn@.service; disabled; vendor preset: enabled)
   Active: <span class="highlight">active (running)</span> since Tue 2016-05-03 15:30:05 EDT; 47s ago
     Docs: man:openvpn(8)
           https://community.openvpn.net/openvpn/wiki/Openvpn23ManPage
           https://community.openvpn.net/openvpn/wiki/HOWTO
  Process: 5852 ExecStart=/usr/sbin/openvpn --daemon ovpn-%i --status /run/openvpn/%i.status 10 --cd /etc/openvpn --script-security 2 --config /etc/openvpn/%i.conf --writepid /run/openvpn/%i.pid (code=exited, sta
 Main PID: 5856 (openvpn)
    Tasks: 1 (limit: 512)
   CGroup: /system.slice/system-openvpn.slice/openvpn@server.service
           └─5856 /usr/sbin/openvpn --daemon ovpn-server --status /run/openvpn/server.status 10 --cd /etc/openvpn --script-security 2 --config /etc/openvpn/server.conf --writepid /run/openvpn/server.pid

May 03 15:30:05 openvpn2 ovpn-server[5856]: /sbin/ip addr add dev tun0 local 10.8.0.1 peer 10.8.0.2
May 03 15:30:05 openvpn2 ovpn-server[5856]: /sbin/ip route add 10.8.0.0/24 via 10.8.0.2
May 03 15:30:05 openvpn2 ovpn-server[5856]: GID set to nogroup
May 03 15:30:05 openvpn2 ovpn-server[5856]: UID set to nobody
May 03 15:30:05 openvpn2 ovpn-server[5856]: UDPv4 link local (bound): [undef]
May 03 15:30:05 openvpn2 ovpn-server[5856]: UDPv4 link remote: [undef]
May 03 15:30:05 openvpn2 ovpn-server[5856]: MULTI: multi_init called, r=256 v=256
May 03 15:30:05 openvpn2 ovpn-server[5856]: IFCONFIG POOL: base=10.8.0.4 size=62, ipv6=0
May 03 15:30:05 openvpn2 ovpn-server[5856]: IFCONFIG POOL LIST
May 03 15:30:05 openvpn2 ovpn-server[5856]: Initialization Sequence Completed
</code></pre>
<p>You can also check that the OpenVPN <code>tun0</code> interface is available by typing:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">ip addr show tun0
</li></ul></code></pre>
<p>You should see a configured interface:</p>
<pre class="code-pre "><code langs=""><div class="secondary-code-label " title="Output">Output</div>4: tun0: <POINTOPOINT,MULTICAST,NOARP,UP,LOWER_UP> mtu 1500 qdisc noqueue state UNKNOWN group default qlen 100
    link/none 
    inet 10.8.0.1 peer 10.8.0.2/32 scope global tun0
       valid_lft forever preferred_lft forever
</code></pre>
<p>If everything went well, enable the service so that it starts automatically at boot:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo systemctl enable openvpn@server
</li></ul></code></pre>
<h2 id="step-10-create-client-configuration-infrastructure">Step 10: Create Client Configuration Infrastructure</h2>

<p>Next, we need to set up a system that will allow us to create client configuration files easily.</p>

<h3 id="creating-the-client-config-directory-structure">Creating the Client Config Directory Structure</h3>

<p>Create a directory structure within your home directory to store the files:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">mkdir -p ~/client-configs/files
</li></ul></code></pre>
<p>Since our client configuration files will have the client keys embedded, we should lock down permissions on our inner directory:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">chmod 700 ~/client-configs/files
</li></ul></code></pre>
<h3 id="creating-a-base-configuration">Creating a Base Configuration</h3>

<p>Next, let's copy an example client configuration into our directory to use as our base configuration:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">cp /usr/share/doc/openvpn/examples/sample-config-files/client.conf ~/client-configs/base.conf
</li></ul></code></pre>
<p>Open this new file in your text editor:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">nano ~/client-configs/base.conf
</li></ul></code></pre>
<p>Inside, we need to make a few adjustments.</p>

<p>First, locate the <code>remote</code> directive.  This points the client to our OpenVPN server address.  This should be the public IP address of your OpenVPN server:</p>
<div class="code-label " title="~/client-configs/base.conf">~/client-configs/base.conf</div><pre class="code-pre "><code langs="">. . .
# The hostname/IP and port of the server.
# You can have multiple remote entries
# to load balance between the servers.
remote <span class="highlight">server_IP_address</span> 1194
. . .
</code></pre>
<p>Next, uncomment the <code>user</code> and <code>group</code> directives by removing the "<strong>;</strong>":</p>
<div class="code-label " title="~/client-configs/base.conf">~/client-configs/base.conf</div><pre class="code-pre "><code langs=""># Downgrade privileges after initialization (non-Windows only)
user nobody
group nogroup
</code></pre>
<p>Find the directives that set the <code>ca</code>, <code>cert</code>, and <code>key</code>.  Comment out these directives since we will be adding the certs and keys within the file itself:</p>
<div class="code-label " title="~/client-configs/base.conf">~/client-configs/base.conf</div><pre class="code-pre "><code langs=""># SSL/TLS parms.
# See the server config file for more
# description.  It's best to use
# a separate .crt/.key file pair
# for each client.  A single ca
# file can be used for all clients.
<span class="highlight">#</span>ca ca.crt
<span class="highlight">#</span>cert client.crt
<span class="highlight">#</span>key client.key
</code></pre>
<p>Finally, add the <code>key-direction</code> directive somewhere in the file.  This should be set to "1" to work with the server:</p>
<div class="code-label " title="~/client-configs/base.conf">~/client-configs/base.conf</div><pre class="code-pre "><code langs=""><span class="highlight">key-direction 1</span>
</code></pre>
<p>Save the file when you are finished.</p>

<h3 id="creating-a-configuration-generation-script">Creating a Configuration Generation Script</h3>

<p>Next, we will create a simple script to compile our base configuration with the relevant certificate, key, and encryption files.  This will place the generated configuration in the <code>~/client-configs/files</code> directory.</p>

<p>Create and open a file called <code>make_config.sh</code> within the <code>~/client-configs</code> directory:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">nano ~/client-configs/make_config.sh
</li></ul></code></pre>
<p>Inside, paste the following script:</p>
<div class="code-label " title="~/client-configs/make_config.sh">~/client-configs/make_config.sh</div><pre class="code-pre "><code class="code-highlight language-sh">#!/bin/bash

# First argument: Client identifier

KEY_DIR=~/openvpn-ca/keys
OUTPUT_DIR=~/client-configs/files
BASE_CONFIG=~/client-configs/base.conf

cat ${BASE_CONFIG} \
    <(echo -e '<ca>') \
    ${KEY_DIR}/ca.crt \
    <(echo -e '</ca>\n<cert>') \
    ${KEY_DIR}/${1}.crt \
    <(echo -e '</cert>\n<key>') \
    ${KEY_DIR}/${1}.key \
    <(echo -e '</key>\n<tls-auth>') \
    ${KEY_DIR}/ta.key \
    <(echo -e '</tls-auth>') \
    > ${OUTPUT_DIR}/${1}.ovpn
</code></pre>
<p>Save and close the file when you are finished.</p>

<p>Mark the file as executable by typing:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">chmod 700 ~/client-configs/make_config.sh
</li></ul></code></pre>
<h2 id="step-11-generate-client-configurations">Step 11: Generate Client Configurations</h2>

<p>Now, we can easily generate client configuration files.</p>

<p>If you followed along with the guide, you created a client certificate and key called <code>client1.crt</code> and <code>client1.key</code> respectively by running the <code>./build-key <span class="highlight">client1</span></code> command in step 6.  We can generate a config for these credentials by moving into our <code>~/client-configs</code> directory and using the script we made:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">cd ~/client-configs
</li><li class="line" prefix="$">./make_config.sh <span class="highlight">client1</span>
</li></ul></code></pre>
<p>If everything went well, we should have a <code>client1.ovpn</code> file in our <code>~/client-configs/files</code> directory:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">ls ~/client-configs/files
</li></ul></code></pre><pre class="code-pre "><code langs=""><div class="secondary-code-label " title="Output">Output</div>client1.ovpn
</code></pre>
<h3 id="transferring-configuration-to-client-devices">Transferring Configuration to Client Devices</h3>

<p>We need to transfer the client configuration file to the relevant device.  For instance, this could be your local computer or a mobile device.</p>

<p>While the exact applications used to accomplish this transfer will depend on your choice and device's operating system, you want the application to use SFTP (SSH file transfer protocol) or SCP (Secure Copy) on the backend. This will transport your client's VPN authentication files over an encrypted connection.</p>

<p>Here is an example SFTP command using our <span class="highlight">client1.ovpn</span> example.  This command can be run from your local computer (OS X or Linux).  It places the <code>.ovpn</code> file in your home directory:</p>
<pre class="code-pre custom_prefix"><code langs=""><ul class="prefixed"><li class="line" prefix="local$">sftp <span class="highlight">sammy</span>@<span class="highlight">openvpn_server_ip</span>:client-configs/files/client1.ovpn ~/
</li></ul></code></pre>
<p>Here are several tools and tutorials for securely transferring files from the server to a local computer:</p>

<ul>
<li><a href="http://winscp.net">WinSCP</a></li>
<li><a href="https://indiareads/community/tutorials/how-to-use-sftp-to-securely-transfer-files-with-a-remote-server">How To Use SFTP to Securely Transfer Files with a Remote Server</a></li>
<li><a href="https://indiareads/community/tutorials/how-to-use-filezilla-to-transfer-and-manage-files-securely-on-your-vps">How To Use Filezilla to Transfer and Manage Files Securely on your VPS</a></li>
</ul>

<h2 id="step-12-install-the-client-configuration">Step 12: Install the Client Configuration</h2>

<p>Now, we'll discuss how to install a client VPN profile on Windows, OS X, iOS, and Android. None of these client instructions are dependent on one another, so feel free to skip to whichever is applicable to you.</p>

<p>The OpenVPN connection will be called whatever you named the <code>.ovpn</code> file.  In our example, this means that the connection will be called <code>client1.ovpn</code> for the first client file we generated.</p>

<h3 id="windows">Windows</h3>

<p><strong>Installing</strong></p>

<p>The OpenVPN client application for Windows can be found on <a href="https://openvpn.net/index.php/open-source/downloads.html">OpenVPN's Downloads page</a>. Choose the appropriate installer version for your version of Windows.</p>

<p></p><div class="code-label notes-and-warnings note" title="Note">Note</div><span class="note">
OpenVPN needs administrative privileges to install.<br /></span>

<p>After installing OpenVPN, copy the <code>.ovpn</code> file to:</p>
<pre class="code-pre "><code langs="">C:\Program Files\OpenVPN\config
</code></pre>
<p>When you launch OpenVPN, it will automatically see the profile and makes it available.</p>

<p>OpenVPN must be run as an administrator each time it's used, even by administrative accounts. To do this without having to right-click and select <strong>Run as administrator</strong> every time you use the VPN, you can preset this, but this must be done from an administrative account. This also means that standard users will need to enter the administrator's password to use OpenVPN. On the other hand, standard users can't properly connect to the server unless the OpenVPN application on the client has admin rights, so the elevated privileges are necessary.</p>

<p>To set the OpenVPN application to always run as an administrator, right-click on its shortcut icon and go to <strong>Properties</strong>. At the bottom of the <strong>Compatibility</strong> tab, click the button to <strong>Change settings for all users</strong>. In the new window, check <strong>Run this program as an administrator</strong>.</p>

<p><strong>Connecting</strong></p>

<p>Each time you launch the OpenVPN GUI, Windows will ask if you want to allow the program to make changes to your computer. Click <strong>Yes</strong>. Launching the OpenVPN client application only puts the applet in the system tray so that the VPN can be connected and disconnected as needed; it does not actually make the VPN connection.</p>

<p>Once OpenVPN is started, initiate a connection by going into the system tray applet and right-clicking on the OpenVPN applet icon. This opens the context menu. Select <strong>client1</strong> at the top of the menu (that's our <code>client1.ovpn</code> profile) and choose <strong>Connect</strong>.</p>

<p>A status window will open showing the log output while the connection is established, and a message will show once the client is connected.</p>

<p>Disconnect from the VPN the same way: Go into the system tray applet, right-click the OpenVPN applet icon, select the client profile and click <strong>Disconnect</strong>.</p>

<h3 id="os-x">OS X</h3>

<p><strong>Installing</strong></p>

<p><a href="https://tunnelblick.net/">Tunnelblick</a> is a free, open source OpenVPN client for Mac OS X. You can download the latest disk image from the <a href="https://tunnelblick.net/downloads.html">Tunnelblick Downloads page</a>. Double-click the downloaded <code>.dmg</code> file and follow the prompts to install.</p>

<p>Towards the end of the installation process, Tunnelblick will ask if you have any configuration files. It can be easier to answer <strong>No</strong> and let Tunnelblick finish. Open a Finder window and double-click <code>client1.ovpn</code>. Tunnelblick will install the client profile. Administrative privileges are required.</p>

<p><strong>Connecting</strong></p>

<p>Launch Tunnelblick by double-clicking Tunnelblick in the <strong>Applications</strong> folder. Once Tunnelblick has been launched, there will be a Tunnelblick icon in the menu bar at the top right of the screen for controlling connections. Click on the icon, and then the <strong>Connect</strong> menu item to initiate the VPN connection. Select the <strong>client1</strong> connection.</p>

<h3 id="ios">iOS</h3>

<p><strong>Installing</strong></p>

<p>From the iTunes App Store, search for and install <a href="https://itunes.apple.com/us/app/id590379981">OpenVPN Connect</a>, the official iOS OpenVPN client application. To transfer your iOS client configuration onto the device, connect it directly to a computer. </p>

<p>Completing the transfer with iTunes will be outlined here. Open iTunes on the computer and click on <strong>iPhone</strong> > <strong>apps</strong>. Scroll down to the bottom to the <strong>File Sharing</strong> section and click the OpenVPN app. The blank window to the right, <strong>OpenVPN Documents</strong>, is for sharing files. Drag the <code>.ovpn</code> file to the OpenVPN Documents window.</p>

<p><img src="https://assets.digitalocean.com/articles/openvpn_ubunutu/1.png" alt="iTunes showing the VPN profile ready to load on the iPhone" /></p>

<p>Now launch the OpenVPN app on the iPhone. There will be a notification that a new profile is ready to import. Tap the green plus sign to import it.</p>

<p><img src="https://assets.digitalocean.com/articles/openvpn_ubunutu/2.png" alt="The OpenVPN iOS app showing new profile ready to import" /></p>

<p><strong>Connecting</strong></p>

<p>OpenVPN is now ready to use with the new profile. Start the connection by sliding the <strong>Connect</strong> button to the <strong>On</strong> position. Disconnect by sliding the same button to <strong>Off</strong>.</p>

<p></p><div class="code-label notes-and-warnings note" title="Note">Note</div><span class="note">
The VPN switch under <strong>Settings</strong> cannot be used to connect to the VPN. If you try, you will receive a notice to only connect using the OpenVPN app.<br /></span>

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

<p>To connect, simply tap the <strong>Connect</strong> button. You'll  be asked if you trust the OpenVPN application. Choose <strong>OK</strong> to initiate the connection. To disconnect from the VPN, go back to the OpenVPN app and choose <strong>Disconnect</strong>.</p>

<p><img src="https://assets.digitalocean.com/articles/openvpn_ubunutu/6.png" alt="The OpenVPN Android app ready to connect to the VPN" /></p>

<h2 id="step-13-test-your-vpn-connection">Step 13: Test Your VPN Connection</h2>

<p>Once everything is installed, a simple check confirms everything is working properly. Without having a VPN connection enabled, open a browser and go to <a href="https://www.dnsleaktest.com">DNSLeakTest</a>.</p>

<p>The site will return the IP address assigned by your internet service provider and as you appear to the rest of the world. To check your DNS settings through the same website, click on <strong>Extended Test</strong> and it will tell you which DNS servers you are using.</p>

<p>Now connect the OpenVPN client to your Droplet's VPN and refresh the browser. The completely different IP address of your VPN server should now appear. That is now how you appear to the world. Again, <a href="https://www.dnsleaktest.com">DNSLeakTest's</a> <strong>Extended Test</strong> will check your DNS settings and confirm you are now using the DNS resolvers pushed by your VPN.</p>

<h2 id="conclusion">Conclusion</h2>

<p>Congratulations! You are now securely traversing the internet protecting your identity, location, and traffic from snoopers and censors.</p>

<p>To configure more clients, you only need to follow steps <strong>6</strong>, and <strong>11-13</strong> for each additional device.</p>

    