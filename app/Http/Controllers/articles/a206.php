<link rel='stylesheet' href='digital_ocean.css'><br> <br> 
      <h3 id="introduction">Introduction</h3>

<p>VPN, or virtual private network, is a secure method of connecting remote internet resources together as if they were under the same LAN.  OpenVPN is a popular implementation that works on Linux, Windows, and Mac operating systems and can be utilized to create complex, encrypted networks between physically dispersed servers.</p>

<p>The OpenVPN Access Server is a solution built on top of traditional OpenVPN that is used as a complete portal for managing connections, users, and interfaces.  It provides the underlying VPN instance, a web interface for managing the suite, and a client that can be used within a web browser.</p>

<p>In this guide, we'll install and configure the OpenVPN Access Server on a CentOS 6.5 VPS instance.</p>

<h2 id="download-and-install-packages">Download and Install Packages</h2>

<p>We can obtain the OpenVPN Access Server package for CentOS from the <a href="https://openvpn.net/index.php/access-server/download-openvpn-as-sw/113.html?osfamily=CentOS">project's website</a>.</p>

<p>Right click on the package that matches your version of CentOS and your machine's architecture.  Select the "copy link address" item or whatever option is closest.</p>

<p>On your CentOS droplet, download the package with <code>curl -O</code> (that's the letter "o" not a zero) followed by the URL you copied from the page.  In my case, this turned out to be:</p>
<pre class="code-pre "><code langs="">cd ~
curl -O http://swupdate.openvpn.org/as/openvpn-as-2.0.5-CentOS6.x86_64.rpm
</code></pre>
<p>When the package has been downloaded, you can install it with using the <code>rpm</code> command:</p>
<pre class="code-pre "><code langs="">sudo rpm -i openvpn-as-2.0.5-CentOS6.x86_64.rpm
</code></pre>
<p>After installing the package, an administration account is created called <code>openvpn</code>.  However, no password has been set.</p>

<p>Set a password for the administrator's account by typing:</p>
<pre class="code-pre "><code langs="">sudo passwd openvpn
</code></pre>
<p>Now, the command line configuration steps are complete.  The rest of the guide will focus on configuring options through the web interface.</p>

<h2 id="accessing-the-web-interface">Accessing the Web Interface</h2>

<p>We can access our VPN portal by going to our server's IP address or domain name, at port <code>943</code>.  The server operates using TLS, so we will need to specify the <code>https</code> protocol.</p>

<p>For our initial interaction, we actually want to go to the administrative interface, which is the same, except ending with <code>/admin</code>:</p>

<pre>
https://<span class="highlight">server_ip_or_domain</span>:943/admin
</pre>

<p>You will get a warning that the site's SSL certificates are not trusted:</p>

<p><img src="https://assets.digitalocean.com/articles/openvpn-access-cent/ssl_warning.png" alt="OpenVPN access server ssl warning" /></p>

<p>This is expected and perfectly fine.  All that this is telling us is that OpenVPN is using a self-signed SSL certificate, which is not trusted by default by our browser.  We can click on the "Proceed anyway" button or whatever similar option you have.</p>

<p>You will be presented with the admin login page.  Use the username <code>openvpn</code> and the password you set for this user:</p>

<p><img src="https://assets.digitalocean.com/articles/openvpn-access-cent/admin_login.png" alt="OpenVPN access admin login" /></p>

<p>You will be taken to the OpenVPN Access Server's EULA, which you will have to agree to if you wish to continue:</p>

<p><img src="https://assets.digitalocean.com/articles/openvpn-access-cent/eula.png" alt="OpenVPN access EULA" /></p>

<p>Once you log in, you can see the administrative interface, complete with some useful at-a-glance stats on the landing page:</p>

<p><img src="https://assets.digitalocean.com/articles/openvpn-access-cent/admin_landing.png" alt="OpenVPN admin landing" /></p>

<p>Here, you can configure your VPN server.  The access server separates the web interface and the actual VPN access and each can be configured independently.</p>

<p>For instance, if you go to the <code>Server Network Settings</code> in the left-hand menu, you will see a page where you can configure the port and interface that each component operates on.  You can also specify the address pool that will be available to the clients.</p>

<p>Another thing you might want to do is add users and configure the authentication methods.  You can add VPN users that match your system users, or add users that you might be controlling through an LDAP server.</p>

<h2 id="logging-in-as-a-client">Logging in as a Client</h2>

<p>When you are finished configuring things as an admin, you can visit the client portion by going to your IP address or domain name followed by port <code>943</code>:</p>

<pre>
https://<span class="highlight">server_ip_or_domain</span>:943
</pre>

<p>You will have to type in a username of a user that you have configured VPN access for:</p>

<p><img src="https://assets.digitalocean.com/articles/openvpn-access-cent/client_login.png" alt="OpenVPN client login" /></p>

<p>If you the user that you logged in as has been designated as an OpenVPN admin account, you will see an "Admin" button that can be used to take you back to the admin control panel.  Either way, you will be taken to a page that will allow you to download software for your client to connect to the VPN server:</p>

<p><img src="https://assets.digitalocean.com/articles/openvpn-access-cent/download_client.png" alt="OpenVPN client software" /></p>

<p>If your desktop is Windows or OS X, or if you have an android or iOS device, you can download an OpenVPN Connect client that will operate within your browser.  If your desktop is a Linux machine, you will be asked to download the normal VPN client.</p>

<p>You should follow the directions of the client of your choice.  If you are using the Linux client, you'll need to download the connection settings profile by clicking on the "Yourself" link:</p>

<p><img src="https://assets.digitalocean.com/articles/openvpn-access-cent/yourself.png" alt="OpenVPN download yourself" /></p>

<p>Using the regular Linux <code>openvpn</code> client, you can connect using something like this:</p>
<pre class="code-pre "><code langs="">sudo openvpn --config client.ovpn
</code></pre>
<h2 id="conclusion">Conclusion</h2>

<p>Now, you should have a portal that can be used to configure your VPN access.  This is an easy to manage interface that can be set up once and configured on-the-fly.  It automatically generates valid configuration files for your users to connect to the server, which can save a lot of headaches with explaining how to configure access.</p>

<div class="author">By Justin Ellingwood</div>

    