<link rel='stylesheet' href='digital_ocean.css'><br><img src=https://community-cdn-digitalocean-com.global.ssl.fastly.net/assets/tutorials/images/large/VPN_tw.png?1426699782/> <br> 
      <h3 id="introduction">Introduction</h3>

<p>We're going to install and configure OpenVPN on a CentOS 7 server. We'll also discuss how to connect a client to the server on Windows, OS X, and Linux.</p>

<p>OpenVPN is an open-source VPN application that lets you create and join a private network securely over the public Internet.</p>

<h2 id="prerequisites">Prerequisites</h2>

<p>You should complete these prerequisites:</p>

<ul>
<li>CentOS 7 Droplet</li>
<li><strong>root</strong> access to the server (several steps cannot be completed with just sudo access)</li>
<li>Domain or subdomain that resolves to your server that you can use for the certificates</li>
</ul>

<p>Before we start we'll need to install the Extra Packages for Enterprise Linux (EPEL) repository. This is because OpenVPN isn't available in the default CentOS repositories. The EPEL repository is an additional repository managed by the Fedora Project containing non-standard but popular packages.</p>
<pre class="code-pre "><code langs="">yum install epel-release
</code></pre>
<h2 id="step-1-—-installing-openvpn">Step 1 — Installing OpenVPN</h2>

<p>First we need to install OpenVPN. We'll also install Easy RSA for generating our SSL key pairs, which will secure our VPN connections.</p>
<pre class="code-pre "><code langs="">yum install openvpn easy-rsa -y
</code></pre>
<h2 id="step-2-—-configuring-openvpn">Step 2 — Configuring OpenVPN</h2>

<p>OpenVPN has example configuration files in its documentation directory. We're going to copy the sample <code>server.conf</code> file as a starting point for our own configuration file.</p>
<pre class="code-pre "><code langs="">cp /usr/share/doc/openvpn-*/sample/sample-config-files/server.conf /etc/openvpn
</code></pre>
<p>Let's open the file for editing.</p>
<pre class="code-pre "><code langs="">vi /etc/openvpn/server.conf
</code></pre>
<p>There are a few lines we need to change in this file. Most of the lines just need to be uncommented (remove the <strong>;</strong>). Other changes are marked in <span class="highlight">red</span>.</p>

<p>When we generate our keys later, the default Diffie-Hellman encryption length for Easy RSA will be 2048 bytes, so we need to change the <code>dh</code> filename to <code><span class="highlight">dh2048.pem</span></code>.</p>
<pre class="code-pre "><code langs="">dh <span class="highlight">dh2048.pem</span>
</code></pre>
<p>We need to uncomment the <code>push "redirect-gateway def1 bypass-dhcp"</code> line, which tells the client to redirect all traffic through our OpenVPN.</p>
<pre class="code-pre "><code langs="">push "redirect-gateway def1 bypass-dhcp"
</code></pre>
<p>Next we need to provide DNS servers to the client, as it will not be able to use the default DNS servers provided by your Internet service provider. We're going to use Google's public DNS servers, <code><span class="highlight">8.8.8.8</span></code> and <code><span class="highlight">8.8.4.4</span></code>.</p>

<p>Do this by uncommenting the <code>push "dhcp-option DNS</code> lines and updating the IP addresses.</p>
<pre class="code-pre "><code langs="">push "dhcp-option DNS <span class="highlight">8.8.8.8</span>"
push "dhcp-option DNS <span class="highlight">8.8.4.4</span>"
</code></pre>
<p>We want OpenVPN to run with no privileges once it has started, so we need to tell it to run with a user and group of <code>nobody</code>. To enable this you'll need to uncomment these lines:</p>
<pre class="code-pre "><code langs="">user nobody
group nobody
</code></pre>
<p>Save and exit the OpenVPN server configuration file.</p>

<h2 id="step-3-—-generating-keys-and-certificates">Step 3 — Generating Keys and Certificates</h2>

<p>Now that the server is configured we'll need to generate our keys and certificates. Easy RSA installs some scripts to generate these keys and certificates.</p>

<p>Let's create a directory for the keys to go in.</p>
<pre class="code-pre "><code langs="">mkdir -p /etc/openvpn/easy-rsa/keys
</code></pre>
<p>We also need to copy the key and certificate generation scripts into the directory.</p>
<pre class="code-pre "><code langs="">cp -rf /usr/share/easy-rsa/2.0/* /etc/openvpn/easy-rsa
</code></pre>
<p>To make life easier for ourselves we're going to edit the default values the script<br />
uses so we don't have to type our information in each time. This information is stored<br />
in the <code>vars</code> file so let's open this for editing.</p>
<pre class="code-pre "><code langs="">vi /etc/openvpn/easy-rsa/vars
</code></pre>
<p>We're going to be changing the values that start with <code>KEY_</code>. Update the following values to be accurate for your organization.</p>

<p>The ones that matter the most are:</p>

<ul>
<li><code>KEY_NAME</code>: You should enter <code><span class="highlight">server</span></code> here; you could enter something else, but then you would also have to update the configuration files that reference <code>server.key</code> and <code>server.crt</code></li>
<li><code>KEY_CN</code>: Enter the domain or subdomain that resolves to your server</li>
</ul>

<p>For the other values, you can enter information for your organization based on the variable name.</p>
<pre class="code-pre "><code langs="">
. . .

# These are the default values for fields
# which will be placed in the certificate.
# Don't leave any of these fields blank.
export KEY_COUNTRY="<span class="highlight">US</span>"
export KEY_PROVINCE="<span class="highlight">NY</span>"
export KEY_CITY="<span class="highlight">New York</span>"
export KEY_ORG="<span class="highlight">IndiaReads</span>"
export KEY_EMAIL="<span class="highlight">sammy@example.com</span>"
export KEY_OU="<span class="highlight">Community</span>"

# X509 Subject Field
export KEY_NAME="<span class="highlight">server</span>"

. . .

export KEY_CN=<span class="highlight">openvpn.example.com</span>

. . .


</code></pre>
<p>We're also going to remove the chance of our OpenSSL configuration not loading due to the version being undetectable. We're going to do this by copying the required configuration file and removing the version number.</p>
<pre class="code-pre "><code langs="">cp /etc/openvpn/easy-rsa/openssl-1.0.0.cnf /etc/openvpn/easy-rsa/openssl.cnf
</code></pre>
<p>To start generating our keys and certificates we need to move into our <code>easy-rsa</code> directory and <em>source</em> in our new variables.</p>
<pre class="code-pre "><code langs="">cd /etc/openvpn/easy-rsa
source ./vars
</code></pre>
<p>Then we will clean up any keys and certificates which may already be in this folder and generate our certificate authority.</p>
<pre class="code-pre "><code langs="">./clean-all
</code></pre>
<p>When you build the certificate authority, you will be asked to enter all the information we put into the <code>vars</code> file, but you will see that your options are already set as the defaults. So, you can just press ENTER for each one.</p>
<pre class="code-pre "><code langs="">./build-ca
</code></pre>
<p>The next things we need to generate will are the key and certificate for the server. Again you can just go through the questions and press ENTER for each one to use your defaults. At the end, answer Y (yes) to commit the changes.</p>
<pre class="code-pre "><code langs="">./build-key-server server
</code></pre>
<p>We also need to generate a Diffie-Hellman key exchange file. This command will take a minute or two to complete:</p>
<pre class="code-pre "><code langs="">./build-dh
</code></pre>
<p>That's it for our server keys and certificates. Copy them all into our OpenVPN directory.</p>
<pre class="code-pre "><code langs="">cd /etc/openvpn/easy-rsa/keys
cp dh2048.pem ca.crt server.crt server.key /etc/openvpn
</code></pre>
<p>All of our clients will also need certificates to be able to authenticate. These keys and certificates will be shared with your clients, and it's best to generate separate keys and certificates for each client you intend on connecting.</p>

<p>Make sure that if you do this you give them descriptive names, but for now we're going to have one client so we'll just call it <code>client</code>.</p>
<pre class="code-pre "><code langs="">cd /etc/openvpn/easy-rsa
./build-key client
</code></pre>
<p>That's it for keys and certificates.</p>

<h2 id="step-4-—-routing">Step 4 — Routing</h2>

<p>To keep things simple we're going to do our routing directly with iptables rather than the new firewalld.</p>

<p>First, make sure the iptables service is installed and enabled.</p>
<pre class="code-pre "><code langs="">yum install iptables-services -y
systemctl mask firewalld
systemctl enable iptables
systemctl stop firewalld
systemctl start iptables
iptables --flush
</code></pre>
<p>Next we'll add a rule to iptables to forward our routing to our OpenVPN subnet, and save this rule.</p>
<pre class="code-pre "><code langs="">iptables -t nat -A POSTROUTING -s 10.8.0.0/24 -o eth0 -j MASQUERADE
iptables-save > /etc/sysconfig/iptables
</code></pre>
<p>Then we must enable IP forwarding in <code>sysctl</code>. Open <code>sysctl.conf</code> for editing.</p>
<pre class="code-pre "><code langs="">vi /etc/sysctl.conf
</code></pre>
<p>Add the following line at the top of the file:</p>
<pre class="code-pre "><code langs="">net.ipv4.ip_forward = 1
</code></pre>
<p>Then restart the network service so the IP forwarding will take effect.</p>
<pre class="code-pre "><code langs="">systemctl restart network.service
</code></pre>
<h2 id="step-5-—-starting-openvpn">Step 5 — Starting OpenVPN</h2>

<p>Now we're ready to run our OpenVPN service. So lets add it to <code>systemctl</code>:</p>
<pre class="code-pre "><code langs="">systemctl -f enable openvpn@server.service
</code></pre>
<p>Start OpenVPN:</p>
<pre class="code-pre "><code langs="">systemctl start openvpn@server.service
</code></pre>
<p>Well done; that's all the server-side configuration done for OpenVPN.</p>

<p>Next we'll talk about how to connect a client to the server.</p>

<h2 id="step-6-—-configuring-a-client">Step 6 — Configuring a Client</h2>

<p>Regardless of your client machine's operating system, you will definitely need a copy of the ca certificate from the server, along with the client key and certificate.</p>

<p>Locate the following files on the <strong>server</strong>. If you generated multiple client keys with unique descriptive names, then the key and certificate names will be different. In this article we used <code><span class="highlight">client</span></code>.</p>
<pre class="code-pre "><code langs="">/etc/openvpn/easy-rsa/keys/ca.crt
/etc/openvpn/easy-rsa/keys/<span class="highlight">client</span>.crt
/etc/openvpn/easy-rsa/keys/<span class="highlight">client</span>.key
</code></pre>
<p>Copy these three files to your <strong>client machine</strong>. You can use <a href="https://indiareads/community/tutorials/how-to-use-sftp-to-securely-transfer-files-with-a-remote-server">SFTP</a> or your preferred method. You could even open the files in your text editor and copy and paste the contents into new files on your client machine.</p>

<p>Just make sure you make a note of where you save them.</p>

<p>We're going to create a file called <code>client.ovpn</code>. This is a configuration file for an OpenVPN client, telling it how to connect to the server.</p>

<ul>
<li>You'll need to change the first line to reflect the name you gave the client in your key and certificate; in our case, this is just <code><span class="highlight">client</span></code></li>
<li>You also need to update the IP address from <code><span class="highlight">your_server_ip</span></code> to the IP address of your server; port <code>1194</code> can stay the same</li>
<li>Make sure the paths to your key and certificate files are correct</li>
</ul>
<pre class="code-pre "><code langs=""><span class="highlight">client</span>
dev tun
proto udp
remote <span class="highlight">your_server_ip</span> 1194
resolv-retry infinite
nobind
persist-key
persist-tun
comp-lzo
verb 3
ca <span class="highlight">/path/to/</span>ca.crt
cert <span class="highlight">/path/to/client</span>.crt
key <span class="highlight">/path/to/client</span>.key
</code></pre>
<p>This file can now be used by any OpenVPN client to connect to your server.</p>

<p><strong>Windows:</strong></p>

<p>On Windows, you will need the official <a href="http://openvpn.net/index.php/open-source/downloads.html">OpenVPN Community Edition binaries</a> which come with a GUI. Then, place your <code>.ovpn</code> configuration file into the proper directory, <code>C:\Program Files\OpenVPN\config</code>, and click <strong>Connect</strong> in the GUI. OpenVPN GUI on Windows must be executed with administrative privileges.</p>

<p><strong>OS X:</strong></p>

<p>On Mac OS X, the open source application <a href="https://code.google.com/p/tunnelblick/">Tunnelblick</a> provides an interface similar to the OpenVPN GUI on Windows, and comes with OpenVPN and the required TUN/TAP drivers. As with Windows, the only step required is to place your <code>.ovpn</code> configuration file into the <code>~/Library/Application<br />
Support/Tunnelblick/Configurations</code> directory. Or, you can double-click on your <code>.ovpn</code> file.</p>

<p><strong>Linux:</strong></p>

<p>On Linux, you should install OpenVPN from your distribution's official repositories. You can then invoke OpenVPN by executing:</p>
<pre class="code-pre "><code langs="">sudo openvpn --config <span class="highlight">~/path/to/client.ovpn</span>
</code></pre>
<h3 id="conclusion">Conclusion</h3>

<p>Congratulations! You should now have a fully operational virtual private network running on your OpenVPN server.</p>

<p>After you establish a successful client connection, you can verify that your traffic is being routed through the VPN by <a href="https://www.google.com/search?q=what%20is%20my%20ip">checking Google to reveal your public IP</a>.</p>

    