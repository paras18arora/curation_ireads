<link rel='stylesheet' href='digital_ocean.css'><br> <br> 
      <div> <h3>Introduction</h3>

<p>OpenVPN Access Server, from the official website is "a full featured SSL VPN software solution that integrates OpenVPN server capabilities, enterprise management capabilities, simplified OpenVPN Connect UI, and OpenVPN Client software packages that accommodate Windows, MAC, and Linux OS environments."</p>

<p>The installation of OpenVPN AS is much simpler compared to the traditional OpenVPN (without any GUI). Another great thing about about OpenVPN AS (Access Server) is that it has a mobile application for both Android and iOS platforms, enabling you to access your OpenVPN server on your smartphone as well.</p>

<h2>Basic Server Setup</h2>

<p>In this tutorial, we are using an Ubuntu 12.04 64-bit cloud server. Go ahead and create one to follow along. If you need help with this, you can refer to this tutorial <a href="https://indiareads/community/articles/how-to-create-your-first-digitalocean-droplet-virtual-server">here</a>. After you have started up your cloud server, let's make some adjustments before we install OpenVPN AS. Please follow this <a href="https://indiareads/community/articles/initial-server-setup-with-ubuntu-12-04">guide</a>
to prepare our cloud server for installation.</p>

<h2>Installing OpenVPN Acess Server</h2>

<p>Let's begin by logging in as the root user. From here, download the OpenVPN AS package:</p>

<pre>sudo wget http://swupdate.openvpn.org/as/openvpn-as-2.0.7-Ubuntu12.amd_64.deb</pre>

<p>The above link is for 64-bit cloud servers since that is what we've decided to use. If by any chance you're using a 32-bit version, the download link would be:
</p><pre>sudo wget http://swupdate.openvpn.org/as/openvpn-as-2.0.7-Ubuntu12.i386.deb</pre>

<p>To install OpenVPN AS, enter the following command:</p>

<pre>dpkg -i openvpn-as-2.0.7-Ubuntu12.amd_64.deb </pre>

<p>If you are using a 32-bit cloud server, enter the following command instead:</p>

<pre>dpkg -i openvpn-as-2.0.7-Ubuntu12.i386.deb</pre>

<p>That's it. OpenVPN AS is now installed. However, there are still some things left to do before we can use it. During the installation, OpenVPN has created a default admin user called 'openvpn'. We need to set a password for 'openvpn'. To do that, enter the following command:</p>

<pre>sudo passwd openvpn</pre>

<p>You'll be prompted to enter your desired password. Make sure your password is secure.</p> 

<h2>Administration and Client Software Setup</h2>

<p>OpenVPN AS web interfaces can be found at:</p>

<pre>
Admin  UI: https://YourIpAddress:943/admin
Client UI: https://YourIPAddress:943/</pre>

<p>Replace "YourIPAddress" with your actual cloud server's IP address. Then, head over to the Client UI to use the access server. You'll see a big bad security warning. But don't be alarmed, it is perfectly okay since we've self-signed our server's SSL. Ignore the warning and click Ok/Proceed and you'll be prompted for username and password. Enter 'openvpn' as the username and the password should be what you've set for 'openvpn' before. After filling out username/password, click 'Go' and you'll see a screen like this:</p>

<img src="https://assets.digitalocean.com/tutorial_images/a9Q3C6Y.jpg?1" alt="openvpn" />

<p>Download the 'OpenVPN Connect' software by clicking the link. After it has finished downloading, run it and enter your login credentials. And voilà! You are now connected to your OpenVPN Access Server.</p>

<p>You can login to the Admin UI if you need to make changes to your access server, although default settings works fine.</p>

<p>One more thing: remember that you can use OpenVPN access server with your smartphone? Download the official Android app <a href="https://play.google.com/store/apps/details?id=net.openvpn.openvpn&hl=en">here</a> and the iOS app <a href="https://itunes.apple.com/us/app/openvpn-connect/id590379981?mt=8">here</a>.</p>

<p>Now, have fun with your OpenVPN Access Server!</p>

<h3>Update:</h3>

<p>As of OpenVPN Access Server v2.0, OpenVPN will no longer uses the <code>5.5.16.0/20</code> subnetwork for clients and will use the <code>172.27.240.0/20</code> subnet instead.</p>

<p><small><a href="https://openvpn.net/index.php/access-server/download-openvpn-as-sw/532-release-notes-v200.html">OpenVPN Access Server v2.0 Release Notes</a></small></p></div>
    