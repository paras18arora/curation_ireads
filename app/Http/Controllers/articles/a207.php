<link rel='stylesheet' href='digital_ocean.css'><br> <br> 
      <div><h3>Introduction</h3>

<p>OpenVPN is a great tool to ensure traffic is not eavesdropped. You can use this to ensure a secure connection from your laptop to your IndiaReads VPS (droplet) as well as between cloud servers. You can also have both done simultaneously.</p>

<p>This is not a foolproof, definitive, perfectly-secure, life-depends-on-it set of instructions. We will be taking three shortcuts here, which in my opinion are reasonable tradeoffs between ease of use and security, but I, nor IndiaReads can be held responsible for security of your VPS, even if you follow these instructions.</p>

<p>To quote a cryptography rock-star, <em>"You have to know what you are doing every step of the way, from conception through installation."</em>
— Bruce Schneier</p>

<p>This article is to help get you started on your way to setting up a Virtual Private Network. You have been warned. I'll point out the shortcuts taken and the general sequence to avoid making these shortcuts at <a id="t1" href="#a1">Appendix 1</a>.</p>

<p>If you only want to have two cloud servers to connect to each other, you may want to find a simpler (yet less secure) <a href="http://openvpn.net/index.php/open-source/documentation/miscellaneous/78-static-key-mini-howto.html" target="_blank">tutorial</a> — though this is a good compromise between ease of setup and security.</p>

<p><b>Note:</b> <i>This tutorial covers IPv4 security. In Linux, IPv6 security is maintained separately from IPv4. For example, "iptables" only maintains firewall rules for IPv4 addresses but it has an IPv6 counterpart called "ip6tables", which can be used to maintain firewall rules for IPv6 network addresses.</i></p>

<p><i>If your VPS is configured for IPv6, please remember to secure both your IPv4 and IPv6 network interfaces with the appropriate tools. For more information about IPv6 tools, refer to this guide: <a href="https://indiareads/community/tutorials/how-to-configure-tools-to-use-ipv6-on-a-linux-vps">How To Configure Tools to Use IPv6 on a Linux VPS</a></i></p>

<h2>Getting Started</h2>

<p>You'll need at least two droplets or VPS for this OpenVPN setup, and will work up to around 60 VPS without major modifications. So to get started, create two droplets. For the rest of this tutorial, I'll refer to them as <em>Droplet 1</em> and <em>Droplet 2</em>.</p>

<h3>On Droplet 1</h3>

<p>• Create the droplet with Ubuntu 13.04 x32.</p>

<p>This should work without modification on any version of Ubuntu that IndiaReads offers, but was only tested on 13.04.</p>

<p>Connect to the VPS via secure shell. We're going to update packages and install a few things.</p>

<p><code>aptitude update && aptitude dist-upgrade -y && aptitude install openvpn firehol -y && reboot</code></p>

<p>Note, if your shell goes purple during this, just choose "Install Package Maintainer's Version" twice.</p>

<h3>Meanwhile, on Droplet 2</h3>

<p>• Create the droplet with Ubuntu 13.04 x32.</p>

<p>Again, this should work on any version of Ubuntu.</p>

<p>Connect to the VPS via secure shell. We're going to update packages in install a few things.</p>

<p><code>aptitude update && aptitude dist-upgrade -y && aptitude install openvpn -y && reboot</code></p>

<p>Again, if your shell goes purple during this, just choose "Install Package Maintainer's Version" twice.</p>

<h2>Generating the Keys</h2>

<p>The key generation is going to be done exclusively on Droplet 1. Type the following commands into the shell:</p>

<pre>cd /etc/openvpn/
mkdir easy-rsa
cd easy-rsa
cp -r /usr/share/doc/openvpn/examples/easy-rsa/2.0/* .
</pre>

<p>Next, we're going to type in some presets which will vastly speed up the key generation process. Type the following command:</p>

<pre>nano /etc/openvpn/easy-rsa/vars</pre>

<p>Go ahead and edit the following values (you only need do to these, although there are several more present):</p>

<ul>
<li>  <em>KEY_COUNTRY</em></li>
<li>  <em>KEY_PROVINCE</em></li>
<li>  <em>KEY_CITY</em></li>
<li>  <em>KEY_ORG</em> and</li>
<li>  <em>KEY_EMAIL</em></li>
</ul>

<p>You may adjust the KEY_SIZE to 2048 or higher for added protection.</p>

<p>Save and exit with Control-O, Enter, and Control-X.</p>

<h2>Create the Certificate Authority Certificate and Key</h2>

<p>Next, type the following commands:</p>

<pre>source vars
./clean-all
./build-ca</pre>

<p>You should be able to hit Enter though all of the questions.</p>

<p><b><small>Note: if you ever have to go back and create more keys, you'll need to retype <em><u>source vars</u></em> but <em>don't</em> type <em><u>./clean-all</u></em> or you'll erase your Certificate Authority, undermining your whole VPN setup.</small></b></p>

<h2>Create Server Certificate and Key</h2>

<p>Generate the server certificate and key with the following command:</p>

<pre>./build-key-server server</pre>

<p>You should be able to hit Enter on defaults, but make sure the Common Name of the certificate is "server".</p>

<p>It will ask you to add a pass phrase, but just hit Enter without typing one.</p>

<p>When it asks you "Sign the certificate?", type <em><u>y</u></em> and hit Enter.</p>

<p>When it says "1 out of 1 certificate requests certified, commit?", type <em><u>y</u></em> and hit Enter.</p>

<h2>Generate Client Keys</h2>

<p>Next is generating the certificate and keys for the clients. For security purposes, each client will get its own certificate and key.</p>

<p>I'm naming the first client "client1", so if you change this, you'll have to adjust it several times later. So type in the following:</p>

<pre>./build-key client1</pre>

<p>As with the server key, when it asks you "Sign the certificate?", type <em><u>y</u></em> and hit Enter.</p>

<p>When it says "1 out of 1 certificate requests certified, commit?", type <em><u>y</u></em> and hit Enter.</p>

<p>Go ahead and repeat this for as many clients as you need to make. You can also come back to this later (though remember to "source var" again if you do so).</p>


<h2>Generate Diffie-Hellman Parameters</h2>

<p>This is used after authentication, to determine the encryption parameters. Simply type the following line:</p>

<pre>./build-dh</pre>

<h2>Copy Keys into Place</h2>

<p>Next, we copy the various keys and certificates into place on the cloud server:</p>

<pre>cd /etc/openvpn/easy-rsa/keys
cp ca.crt dh1024.pem server.crt server.key /etc/openvpn</pre>

<p>It's very important that keys are kept secure. Double check that only root has permission to read. So type:</p>

<pre>ls -lah /etc/openvpn</pre>

<p>What you're looking for is that <em>server.key</em> has <b>-rw-------</b> for permissions (read/write for owner, none for group, and none everyone).
If you need to change it, use this command:</p>

<pre>chmod 600 /etc/openvpn/server.key</pre>

<h2>Distribute Client Certificate and Key</h2>

<p>The following table shows which files go onto which client.</p>

<table style="width:100%;">
  <tr>
    <th style="background-color: #666; color: #FFF;">client1</th>
    <th style="background-color: #666; color: #FFF;">client2</th>
  </tr>
  <tr>
    <td>ca.crt</td>
    <td>ca.crt</td>
  </tr>
  <tr>
    <td>client1.crt</td>
    <td>client2.crt</td>
  </tr>
  <tr>
    <td>client1.key <small><b>(SECRET)</b></small></td>
    <td>client2.key <small><b>(SECRET)</b></small></td>
  </tr>
</table>

<p>We'll securely copy the files to the second VPS using secure copy. (You could also cat, then copy and paste across SSH windows. But this is a nice technique to securely copy files.)</p> 

<h3>On Droplet 1</h3>

<p>Generate SSH keys with the following command:</p> 

<pre>ssh-keygen -t rsa</pre>

<p>It will choose a default filename and then ask you for a secure passphrase, which you should set. Find the SSH public key you just generated and type:</p>

<pre>cat ~/.ssh/id_rsa.pub</pre>

<p>Copy the results onto the clipboard. It's a few lines of letters and numbers looking like:</p> 

<pre>ssh-rsa AAAAB3NzaC1yc2EAAAADAQABAAABAQCo249TgbI1gYP42RbLcDhsNN28r/fNT6ljdFOZxhk+05UAPhxq8bASaqSXZI3K8EEI3wSpigaceNUu65pxLEsZWS8xTtjY4AVxZU2w8GIlnFDSQYr3M2A77ZAq5DqyhGmnnB3cPsIJi5Q6JQNaQ/Meg1v7mYR9prfEENJeXrDiXjxUqi41NlVdb5ZQnPL1EdKM+KN/EPjiTD5XY1q4ICmLJUB8RkffHwH2knEcBoSZW2cNADpMu/IqtxTZpFL0I1eIEtoCWg4mGIdIo8Dj/nzjheFjavDhiqvUEImt1vWFPxHEXt79Iap/VQp/yc80fhr2UqXmxOa0XS7oSGGfFuXz root@openvpn1</pre>

<p>But USE YOUR OWN, not mine. Your id_rsa.pub doesn't need to be kept secure, but if you use the key above, that would allow me access to your VPS.</p>

<h3>Meanwhile, on Droplet 2</h3>

<pre>cd ~/.ssh</pre>

<p>(If you get an error, create the folder with <code>mkdir ~/.ssh</code>).</p>

<pre>nano authorized_keys</pre>

<p>Paste the public key that is in your clipboard onto a new line, then save and exit with Control-O, Enter, Control-X.</p>

<h3>Back to Droplet 1</h3>

Next, we copy the appropriate keys onto the second server:

<pre>scp /etc/openvpn/easy-rsa/keys/ca.crt \
    /etc/openvpn/easy-rsa/keys/client1.crt \
    /etc/openvpn/easy-rsa/keys/client1.key \
    root@droplet2ip:~/</pre>

<p>It will ask you "Are you sure you want to continue connecting (yes/no)?", so type <em>yes</em> and hit Enter.</p>

<p>Then input the passphrase you've just created.</p>

<h3>Switching again to Droplet 2</h3>

<p>Next, we move the certificates and keys into their final location:</p>

<pre>cd ~
mv ca.crt client1.crt client1.key /etc/openvpn
ls -l /etc/openvpn</pre>

<p>As the key must be kept secure, let's make sure client1.key has the correct permissions (<b>-rw-------</b>).</p>

<p>Again, if need be, the permissions can be reset with the following command:</p>

<pre>chmod 600 /etc/openvpn/client1.key</pre>

<h2>Networking</h2>

<p>Next comes the excitement that is networking on a VPN. You can use OpenVPN using routing or bridging. If you know what the difference is, you don't need my help choosing. For this tutorial, we'll use routing. We'll also use OpenVPN's default network range, which is <b>10.8.0.0/24</b>. Unless you already use this network range somewhere, this will be fine. If you do need a different range, pick a private range and make sure you adjust all the later configuration steps accordingly.</p>

<h3>Droplet 1</h3>

<p>On the OpenVPN server, we need to configure routing and setup a firewall as well. I use a tool called <em>firehol</em> to configure iptables, which makes it very simple to set up a complex firewall. So, type the following commands:</p>

<pre>nano /etc/firehol/firehol.conf</pre>

<p>While we could allow incoming OpenVPN connections from any address, we're going to limit these connections to the IP addresses of the computers you want to connect. Make this list of your IP addresses now.</p>

<p>Note: The following configuration only allows incoming SSH and OpenVPN connections. If you have other services that need to receive incoming connections, you'll need to modify the firewall to support these.</p>

<pre>
version 5

interface eth0 inet
  client all accept                           // allow all outgoing connections
  server ssh accept                           // allow all incoming SSH connections
  server openvpn accept src "1.2.3.4 2.3.4.5" // allow incoming OpenVPN connections
                                              //   from these designated addresses 
                                              //   NOTE: EDIT THESE ADDRESSES

interface tun0 vpn                            
  server all accept                           // allow all incoming connections on the VPN 
  client all accept                           // allow all outgoing connections on the 

router inet2vpn inface eth0 outface tun0
  route all accept                            // route freely to the VPN

router vpn2inet inface tun0 outface eth0
  masquerade                                  // use NAT masquerading from the VPN
  route all accept                            // route freely to the VPN
</pre>

<p>Then, start the firewall with the following command:</p>

<pre>firehol start</pre>

<p>If you have an issue with your firewall, you can restart your VPS and the firewall configuration will be cleared. To make the firewall permanent, input the following:</p>

<pre>nano /etc/default/firehol</pre>

<p>Find the following line:</p>

<pre>START_FIREHOL=NO</pre>

<p>Now, change NO to YES. Save and exit with Control-O, Enter, Control-X.</p>

<h2>OpenVPN Server config files</h2>

<h3>On Droplet 1</h3>

<p>The next step is to copy the example server configuration into place and edit it to our needs.</p>

<pre>cd /etc/openvpn
cp /usr/share/doc/openvpn/examples/sample-config-files/server.conf.gz .
gunzip server.conf.gz
nano /etc/openvpn/server.conf</pre>

<p>The OpenVPN server will start as root, but we can set it to drop to lower privileges after startup, which is a good security measure. To configure this, find the following lines and uncomment them by removing the semicolons:</p>

<pre>;user nobody
;group nogroup</pre>

<p>If you have multiple servers that should communicate to each other, find the following line and remove the semicolon:</p>

<pre>;client-to-client </pre>

<p>If you increased the key size of your DH key, find the line:</p>

<pre>dh dh1024.pem</pre>

<p>and change 1024 to 2048 (or whatever number you selected).</p>

<p>We're going to assign the different clients static IP addresses from the OpenVPN server, so to do that, uncomment the following line:</p>

<pre>;client-config-dir ccd</pre>

<p>Save with Control-O, Enter, Control-X. Next, make the client configuration directory:</p>

<pre>mkdir /etc/openvpn/ccd</pre>

<p>and we'll add configuration for the first client here:</p>

<pre>nano /etc/openvpn/ccd/client1</pre>

<p>Type the following command, which assigns client1 to IP address, 10.8.0.5:</p>

<pre>ifconfig-push 10.8.0.5 10.8.0.6</pre>

<p>Save and exit with Control-O, Enter, Control-X.</p>

<p>For reasons that require an in-depth knowledge of networking to understand, use the following addresses for additional clients:</p>

<p><b>/etc/openvpn/ccd/client2</b>
ifconfig-push 10.8.0.9 10.8.0.10</p>

<p><b>/etc/openvpn/ccd/client3:</b>
ifconfig-push 10.8.0.13 10.8.0.14</p>

<p>Simply, add 4 to each IP for each new set. A more technical explanation is at <a href="#a2" id="t2">Appendix 2</a>.</p>

<p>Now we can start the OpenVPN server with the following command:</p>

<pre>service openvpn start</pre>

<p>Give it a second, then type the following commands to ensure OpenVPN is running:</p>

<pre>ifconfig</pre>

<p>And among the network interfaces, you should see that the interface tun0 look like this:</p>

<pre>tun0      Link encap:UNSPEC  HWaddr 00-00-00-00-00-00-00-00-00-00-00-00-00-00-00-00  
          inet addr:10.8.0.1  P-t-P:10.8.0.2  Mask:255.255.255.255
          UP POINTOPOINT RUNNING NOARP MULTICAST  MTU:1500  Metric:1
          RX packets:140 errors:0 dropped:0 overruns:0 frame:0
          TX packets:149 errors:0 dropped:0 overruns:0 carrier:0
          collisions:0 txqueuelen:100 
          RX bytes:13552 (13.5 KB)  TX bytes:14668 (14.6 KB)</pre>

<p>You can also type:</p>

<pre>service openvpn status</pre>

<p>and if OpenVPN is running, you'll see the following:</p> 

<pre> * VPN 'server' is running</pre>

<p>If both of these are in order, then the server is up and running and we'll configure the client connection next.</p>

<h2>OpenVPN Client Config Files</h2>

<h3>On Droplet 2</h3>

<p>First, let's copy the sample client configuration file to the proper locations:</p>

<pre>cd /etc/openvpn
cp /usr/share/doc/openvpn/examples/sample-config-files/client.conf .</pre>
It's mostly configured, but we have to "tell" the address of our OpenVPN server, Droplet 1:
<pre>nano /etc/openvpn/client.conf</pre>

<p>Find the line that says:</p>

<pre>remote my-server-1 1194</pre>

<p>And change <b>my-server-1</b> to the IP address of Droplet 1.
Next, we have to ensure that the client key and the certificate matches the actual file names. Search for the following lines:</p>

<pre>cert client.crt
key client.key</pre>

<p>and adjust them to the keys copied over (e.g. <em>client1.crt</em> and <em>client1.key</em>).</p>

<p>Save and exit with Control-O, Enter, Control-X.</p>

<p>And next, let's start up the VPN:</p>

<pre>service openvpn start</pre>

Again, we can test it with the following commands:

<pre>service openvpn status</pre>

<pre>ifconfig</pre>

<p>Now that both ends of the VPN are up, we should test the network. Use the following command:</p>

<pre>ping 10.8.0.1</pre>

<p>And if successful, you should see something like:</p>

<pre>PING 10.8.0.1 (10.8.0.1) 56(84) bytes of data.
64 bytes from 10.8.0.1: icmp_req=1 ttl=64 time=0.102 ms
64 bytes from 10.8.0.1: icmp_req=2 ttl=64 time=0.056 ms</pre>


<h2>Congratulations, You're Now Done!</h2>

<p>Any traffic you do not need encrypted, you can connect via the public-facing IP address. Any traffic between cloud servers you want encrypted, connect to the Internet network address, e.g. Droplet 1 connect to <b>10.8.0.1</b>. Droplet 2 is <b>10.8.0.5</b>, Droplet 3 is <b>10.8.0.9</b>, and so on.</p>

<p>Encrypted traffic will be slower than unencrypted, especially if your cloud servers are in different datacenters, but either traffic methods are available simultaneously, so choose accordingly.</p>

<p>Also, now is a good time to learn more about OpenVPN and encryption in general. The <a href="http://openvpn.net/index.php/open-source.html" target="_blank">OpenVPN</a> website has some good resources for this.</p>

<a id="a1"></a><h2>Appendix 1</h2>

<h3>Security</h3>

<p>There were three shortcuts used here which if security is of the utmost importance, you should not do.</p>

<ul>
<li>First, the keys were all generated remotely on a virtual server that is both on the Internet and not fully under one's control. The most secure way of doing this is have the Certificate Authority keys generated on a standalone (not Internet-connected) computer in a secure location.</li>

<li>Second, the keys were transmitted rather than generated in place. SSH provides a reasonably secure method of transmitting files but there are <a href="http://www.schneier.com/blog/archives/2008/05/random_number_b.html" target="_blank">various</a> <a href="https://indiareads/blog_posts/avoid-duplicate-ssh-host-keys" target="_blank">instances</a> where SSH has not been fully secure. If you were to generate in host, transfer the CSRs to your offline CA, sign them there, then transmit the signed requests back, this would be more secure.</li>

<li>Third, no passphrases were assigned to the keys. As these are servers and will likely need to reboot unattended, this tradeoff was made.</li>
</ul>

<p>Additionally, OpenVPN supports loads of other hardening features, beyond the scope of this tutorial. Reading up at openvpn.org should be done.</p>


<small> <a href="#t1">back</a></small>



<a id="a2"></a><h2>Appendix 2 </h2>

<h3>A note on networking</h3>

<p>So the first client will use 10.8.0.6 as its IP address, and 10.8.0.5 is the VPN tunnel endpoint. The second address is only used to route traffic through the tunnel.
This is because each client uses a CIDR /30 network, meaning 4 IP addresses are used per client computer.</p>

<p>So the VPN server will use the 10.8.0.0/30 network:</p>

<table style="width:100%">
  <tr>
    <td>10.8.0.0</td>
    <td>Network</td>
  </tr>
  <tr>
    <td>10.8.0.1</td>
    <td>Server IP address</td>
  </tr>
  <tr>
    <td>10.8.0.2</td>
    <td>Tunnel Endpoint</td>
  </tr>
  <tr>
    <td>10.8.0.3</td>
    <td>Broadcast</td>
  </tr>
</table>

<p>And the first client, client1, will use the 10.8.0.4/30 network:</p>

<table style="width:100%">
  <tr>
    <td>10.8.0.4</td>
    <td>Network</td>
  </tr>
  <tr>
    <td>10.8.0.5</td>
    <td>Server IP address</td>
  </tr>
  <tr>
    <td>10.8.0.6</td>
    <td>Tunnel Endpoint</td>
  </tr>
  <tr>
    <td>10.8.0.7</td>
    <td>Broadcast</td>
  </tr>
</table>

<p>And so on...</p>

<small> <a href="#t2">back</a></small></div>
    