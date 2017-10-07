<link rel='stylesheet' href='digital_ocean.css'><br> <br> 
      <div><h3>Intro</h3>

<p>One of the commonly asked questions from our users is how to add another IP address to their server.  You can assign your own private IP address to your droplet by creating a VPN tunnel. Whether you want to build your own Virtual Private Network (VPN), or assign an SSL certificate to that IP address, you have several options.  From all of the possible options, the most optimal ones are between PPTP and OpenVPN.  A Point-To-Point Tunneling Protocol (PPTP) allows you to implement your own VPN very quickly, and is compatible with most mobile devices.  Even though PPTP is less secure than OpenVPN, it is also faster and uses less CPU resources.</p> 

<h2>Step 1 - PPTP Installation</h2>

<p>You will have to select one server to be responsible for handling out IPs to others and authenticating all of your servers into your VPN.  This will become your PPTP Server.</p> 

<p>On CentOS 6 x64:</p>

<pre>
rpm -i http://poptop.sourceforge.net/yum/stable/rhel6/pptp-release-current.noarch.rpm
yum -y install pptpd
</pre>

<p>On Ubuntu 12.10 x64:</p>

<pre>apt-get install pptpd</pre>

<p>Now you should edit /etc/pptpd.conf and add the following lines:</p>

<pre>
localip 10.0.0.1
remoteip 10.0.0.100-200
</pre>

<p>Where localip is IP address of your server and remoteip are IPs that will be assigned to clients that connect to it.</p>

Next, you should setup authentication for PPTP by adding users and passwords.  Simply add them to /etc/ppp/chap-secrets :

<img src="https://assets.digitalocean.com/articles/community/PPTP1.png" width="680" />

<p>Where client is the username, server is type of service – pptpd for our example, secret is the password, and IP addresses specifies which IP address may authenticate.
By setting ‘*’ in IP addresses field, you specify that you would accept username/password pair for any IP.</p>

<h2>Step 2 - Add DNS servers to /etc/ppp/pptpd-options</h2>

<pre>
ms-dns 8.8.8.8
ms-dns 8.8.4.4
</pre>

<p>Now you can start PPTP daemon:</p>

<pre>
service pptpd restart
</pre>

<p>Verify that it is running and accepting connections:</p>

<img src="https://assets.digitalocean.com/articles/community/PPTP2.png" width="680" />

<h2>Step 3 - Setup Forwarding</h2>

<p>It is important to enable IP forwarding on your PPTP server.  This will allow you to forward packets between public IP and private IPs that you setup with PPTP.
Simply edit /etc/sysctl.conf and add the following line if it doesn’t exist there already:</p>

<pre>
net.ipv4.ip_forward = 1
</pre>

<p>To make changes active, run <b>sysctl -p</b></p>

<h2>Step 4 - Create a NAT rule for iptables</h2>

<pre>
iptables -t nat -A POSTROUTING -o eth0 -j MASQUERADE && iptables-save
</pre>

<p>If you would also like your PPTP clients to talk to each other, add the following iptables rules:</p>

<pre>
iptables --table nat --append POSTROUTING --out-interface ppp0 -j MASQUERADE
iptables -I INPUT -s 10.0.0.0/8 -i ppp0 -j ACCEPT
iptables --append FORWARD --in-interface eth0 -j ACCEPT
</pre>

<p>Now your PPTP server also acts as a router.</p>  

<p>If you would like to restrict which servers can connect to your droplets, you can setup an iptables rule that restricts TCP connects to port 1723.</p>

<h2>Step 5 - Setup Clients</h2>

<p>On your client servers, install PPTP client:</p>

<pre>yum -y install pptp</pre>

<h2>Step 6 - Add necessary Kernel module</h2>

<pre>
modprobe ppp_mppe
</pre>

<p>Create a new file /etc/ppp/peers/pptpserver and add the following lines, replacing name and password with your own values:</p>

<pre>
pty "pptp 198.211.104.17 --nolaunchpppd"
name box1
password 24oiunOi24
remotename PPTP
require-mppe-128
</pre>

<p>Where 198.211.104.17 is the public IP address of our PPTP server, with username ‘box1’ and password ‘24oiunOi24’  that we specified /etc/ppp/chap-secrets file on our PPTP server.</p>

<p>Now we can ‘call’ this PPTP server, since this is a point-to-point protocol.  
Whichever name you gave your peers file in/etc/ppp/peers/ should be used in this next line.  Since we called our file pptpserver:</p>

<pre>
pppd call pptpserver
</pre>

<p>You should see successful connection from PPTP server logs:</p>

<img src="https://assets.digitalocean.com/articles/community/PPTP3.png" width="680" />

<p>On your PPTP client, setup routing to your private network via ppp0 interface:</p>

<pre>
ip route add 10.0.0.0/8 dev ppp0
</pre>

<p>Your interface ppp0 should come up on PPTP client server, and can be checked by running ifconfig</p>

<img src="https://assets.digitalocean.com/articles/community/PPTP4.png" width="680" />

<p>Now you can ping your PPTP server and any other clients that are connected to this network:</p>

<img src="https://assets.digitalocean.com/articles/community/PPTP5.png" width="680" />

<p>We can add our second PPTP client to this network:</p>

<pre>
yum -y install pptp
modprobe ppp_mppe
</pre>

<p>Add to /etc/ppp/peers/pptpserver (replacing with your own name and password values):</p>

<pre>
pty "pptp 198.211.104.17 --nolaunchpppd"
name box2
password 239Aok24ma
remotename PPTP
require-mppe-128
</pre>

<p>Now run on your second client the following:</p>

<pre>
pppd call pptpserver
ip route add 10.0.0.0/8 dev ppp0
</pre>

<img src="https://assets.digitalocean.com/articles/community/PPTP6.png" width="680" />

<p>You can also ping the first client, as packets would go through the PPTP server and be routed using the iptables rules we’ve placed earlier:</p>

<img src="https://assets.digitalocean.com/articles/community/PPTP7.png" width="680" />

<p>This setup allows you to create your own virtual private network:</p>

<img src="https://assets.digitalocean.com/articles/community/PPTP8.png" width="680" />

<p>If you wanted to have all of your devices communicating securely on one network, this is a quick way of implementing it.</p>

<p>You can use it with Nginx, Squid, MySQL, and any other application you can think of.</p>

<p>Since traffic is 128-bit encrypted, it is less CPU-intensive than OpenVPN, and still provides an added level of security to your traffic.</p>

<div class="author">By Bulat Khamitov</div></div>
    