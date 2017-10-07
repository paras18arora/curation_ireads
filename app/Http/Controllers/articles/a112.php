<link rel='stylesheet' href='digital_ocean.css'><br> <br> 
      <h2 id="introduction">Introduction</h2>

<p>Most businesses require several server types such as file servers, print servers, email servers, etc. Zentyal combines these services and more, as a complete small business server for Linux.</p>

<p>Zentyal servers are simple to use because of the Graphical User Interface (GUI).  The GUI provides an easy and intuitive interface for use by novice and experienced administrators alike. Command-line administration is available, too. We'll be showing how to use both of these methods in this tutorial.</p>

<p>To see a list of the specific software that can be installed with Zentyal, please see either of the <strong>Installing Packages</strong> sections.</p>

<p>Some people may be familiar with the Microsoft Small Business Server (SBS), now called Windows Server Essentials. Zentyal is a similar product that is based on Linux, and more specifically Ubuntu. Zentyal is also a drop-in replacement for Microsoft SBS and Microsoft Exchange Servers. Since Zentyal is open source, it is a cost-effective choice.</p>

<h3 id="zentyal-editions">Zentyal Editions</h3>

<p>There are two types of Zentyal available. The first is the Community Edition and the other is the Commercial Edition.</p>

<p>The Community Edition has all the latest features, stable or otherwise. No official support is offered by the company for technical issues. No cloud services are provided with the Community Edition. A new version is released every three months with unofficial support for the most recent release. Users are unlimited.</p>

<p>The Commercial Edition has all the latest features, stable and tested. Support is offered based on the Small and Medium Business Edition. Cloud Services are integrated into the server and based on the SMB Edition. The number of users supported by the Commercial Edition is based on the SMB Edition purchased. A new Commercial Edition is released every two years and supported for four years.</p>

<p>Note: The Community Edition cannot be upgraded to the Commercial Edition.</p>

<h3 id="zentyal-requirements">Zentyal Requirements</h3>

<p>Zentyal is Debian-based and built on the latest Ubuntu Long Term Support (LTS) version. The current hardware requirements for Zentyal 3.5 are based on Ubuntu Trusty 14.04.1 LTS (kernel 3.5). Zentyal uses the LXDE desktop and the Openbox window manager.</p>

<p>The minimum hardware requirements for Ubuntu Server Edition include 300 MHz CPU, 128 MB of RAM, and 500 MB of disk space. Of course, these are bare minimums and would produce undesired responses on a network when running multiple network services.</p>

<p>Keep in mind that every network service requires different hardware resources and the more services installed, the more hardware requirements are increased. In most cases, it is best to start with the basic services you require and then add other services as needed. If the server starts to lag in processing user requests, you should consider upgrading your server plan.</p>

<p>Depending on your number of users, and which Zentyal services you plan to run, your hardware requirements will change. These are the Zentyal recommendations. For IndiaReads deployments, you should go by the RAM column:</p>

<table class="pure-table"><thead>
<tr>
<th style="text-align: center">Profile</th>
<th style="text-align: center">Number of Users</th>
<th style="text-align: center">CPU</th>
<th style="text-align: center">RAM</th>
<th style="text-align: center">Disk Space</th>
<th style="text-align: center">Network Cards</th>
</tr>
</thead><tbody>
<tr>
<td style="text-align: center">Gateway</td>
<td style="text-align: center"><50</td>
<td style="text-align: center">P4</td>
<td style="text-align: center">2 GB</td>
<td style="text-align: center">80 GB</td>
<td style="text-align: center">2+</td>
</tr>
<tr>
<td style="text-align: center"></td>
<td style="text-align: center">50+</td>
<td style="text-align: center">Xeon dual core</td>
<td style="text-align: center">4 GB</td>
<td style="text-align: center">160 GB</td>
<td style="text-align: center">2+</td>
</tr>
<tr>
<td style="text-align: center">Infrastructure</td>
<td style="text-align: center"><50</td>
<td style="text-align: center">P4</td>
<td style="text-align: center">1 GB</td>
<td style="text-align: center">80 GB</td>
<td style="text-align: center">1</td>
</tr>
<tr>
<td style="text-align: center"></td>
<td style="text-align: center">50+</td>
<td style="text-align: center">P4</td>
<td style="text-align: center">2 GB</td>
<td style="text-align: center">160 GB</td>
<td style="text-align: center">1</td>
</tr>
<tr>
<td style="text-align: center">Office</td>
<td style="text-align: center"><50</td>
<td style="text-align: center">P4</td>
<td style="text-align: center">1 GB</td>
<td style="text-align: center">250 GB</td>
<td style="text-align: center">1</td>
</tr>
<tr>
<td style="text-align: center"></td>
<td style="text-align: center">50+</td>
<td style="text-align: center">Xeon dual core</td>
<td style="text-align: center">2 GB</td>
<td style="text-align: center">500 GB</td>
<td style="text-align: center">1</td>
</tr>
<tr>
<td style="text-align: center">Communications</td>
<td style="text-align: center"><100</td>
<td style="text-align: center">Xeon dual core</td>
<td style="text-align: center">4 GB</td>
<td style="text-align: center">250 GB</td>
<td style="text-align: center">1</td>
</tr>
<tr>
<td style="text-align: center"></td>
<td style="text-align: center">100+</td>
<td style="text-align: center">Xeon dual core</td>
<td style="text-align: center">8 GB</td>
<td style="text-align: center">500 GB</td>
<td style="text-align: center">1</td>
</tr>
</tbody></table>

<p>We'll talk more about the profiles and different types of Zentyal services later in the article.</p>

<h2 id="installing-zentyal">Installing Zentyal</h2>

<p>Create a <strong>1 GB</strong> Droplet running <strong>Ubuntu 14.04</strong>.</p>

<p>Add a <a href="https://indiareads/community/tutorials/how-to-add-and-delete-users-on-an-ubuntu-14-04-vps">user with sudo access</a>.</p>

<p>First, you need to add the Zentyal repository to your repository list with the following command:</p>
<pre class="code-pre "><code langs="">sudo add-apt-repository "deb http://archive.zentyal.org/zentyal 3.5 main extra"
</code></pre>
<p>After the packages are downloaded they should be verified using a public key from Zentyal. To add the public key, execute the following two commands:</p>
<pre class="code-pre "><code langs="">sudo apt-key adv --keyserver keyserver.ubuntu.com --recv-keys 10E239FF
wget -q http://keys.zentyal.org/zentyal-3.5-archive.asc -O- | sudo apt-key add -
</code></pre>
<p>Now that the repository list is updated, you need to update the package lists from the repositories. To update the package lists, execute this command:</p>
<pre class="code-pre "><code langs="">sudo apt-get update
</code></pre>
<p>Once the package list is updated, you can install Zentyal by running:</p>
<pre class="code-pre "><code langs="">sudo apt-get install zentyal
</code></pre>
<p>When prompted, set a secure root password (twice) for MySQL. Confirm port 443.</p>

<p>Zentyal is now installed.</p>

<p>If you prefer to use the command line to install your Zentyal packages, read the next section. Or, if you prefer to use a dashboard, skip to the <strong>Accessing Zentyal Dashboard</strong> section.</p>

<h2 id="installing-packages-command-line">Installing Packages (Command Line)</h2>

<p>Now, you can start installing the specific services you require. There are four basic profiles which install many related modules at once. These profiles are:</p>

<ul>
<li><p><strong>zentyal-office</strong> — The profile is for setting up an office network to share resources.  Resources can include files, printers, calendars, user profiles, and groups.</p></li>
<li><p><strong>zentyal-communication</strong> — Server can be used for business communications such as email, instant messaging, and Voice Over IP (VOIP).</p></li>
<li><p><strong>zentyal-gateway</strong> — The server will be a controlled gateway for the business to and from the Internet.  Internet access can be controlled and secured for internal systems and users.</p></li>
<li><p><strong>zentyal-infrastructure</strong> — The server can manage the network infrastructure for the business.  Managment consists of NTP, DHCP, DNS, etc. </p></li>
</ul>

<p>You can see what's installed with each profile <a href="http://www.zentyal.org/server/#server-features">here</a>. To install a profile, run this command:</p>
<pre class="code-pre "><code langs="">sudo apt-get install zenytal-office
</code></pre>
<p>You can also install each module individually as needed. For example, if you wanted only the antivirus module of the Office profile installed, you would execute the following:</p>
<pre class="code-pre "><code langs="">sudo apt-get install zentyal-antivirus
</code></pre>
<p>You can also install all the profiles in one command:</p>
<pre class="code-pre "><code langs="">sudo apt-get install zentyal-all
</code></pre>
<p>When you are installing certain packages, you will need to provide information about your systems via the interactive menus.</p>

<p>Some of the module names are straightforward, but here is a defined list of Zentyal packages:</p>

<ul>
<li><strong>zentyal-all - Zentyal</strong> - All Component Modules (all Profiles)</li>
<li><strong>zentyal-office</strong> - Zentyal Office Suite (Profile)</li>
<li><strong>zentyal-antivirus</strong> - Zentyal Antivirus</li>
<li><strong>zentyal-dns</strong> – Zentyal DNS</li>
<li><strong>zentyal-ebackup</strong> - Zentyal Backup</li>
<li><strong>zentyal-firewall</strong> – Zentyal Firewall Services</li>
<li><strong>zentyal-ntp</strong> – NTP Services</li>
<li><strong>zentyal-remoteservices</strong> - Zentyal Cloud Client</li>
<li><strong>zentyal-samba</strong> - Zentyal File Sharing and Domain Services</li>
<li><strong>zentyal-communication</strong> - Zentyal Communications Suite</li>
<li><strong>zentyal-jabber</strong> - Zentyal Jabber (Instant Messaging)</li>
<li><strong>zentyal-mail</strong> - Zentyal Mail Service</li>
<li><strong>zentyal-mailfilter</strong> - Zentyal Mail Filter</li>
<li><strong>zentyal-gateway</strong> - Zentyal Gateway Suite</li>
<li><strong>zentyal-l7-protocols</strong> - Zentyal Layer-7 Filter</li>
<li><strong>zentyal-squid</strong> – HTTP Proxy</li>
<li><strong>zentyal-trafficshaping</strong> - Zentyal Traffic Shaping</li>
<li><strong>zentyal-infrastructure</strong> - Zentyal Network Infrastructure Suite</li>
<li><strong>zentyal-ca</strong> – Zentyal Certificate Authority</li>
<li><strong>zentyal-dhcp</strong> – DHCP Services</li>
<li><strong>zentyal-openvpn</strong> – VPN Services</li>
<li><strong>zentyal-webserver</strong> - Zentyal Web Server</li>
</ul>

<p>Other modules which are not included in the profiles are as follows:</p>

<ul>
<li><strong>zentyal-bwmonitor</strong> - Zentyal Bandwidth Monitor</li>
<li><strong>zentyal-captiveportal</strong> - Zentyal Captive Portal</li>
<li><strong>zentyal-ips</strong> - Zentyal Intrusion Prevention System</li>
<li><strong>zentyal-ipsec</strong> - Zentyal IPsec and L2TP/IPsec</li>
<li><strong>zentyal-monitor</strong> - Zentyal Monitor</li>
<li><strong>zentyal-nut</strong> - Zentyal UPS Management</li>
<li><strong>zentyal-openchange</strong> - Zentyal OpenChange Server</li>
<li><strong>zentyal-radius</strong> - Zentyal RADIUS</li>
<li><strong>zentyal-software</strong> - Zentyal Software Management</li>
<li><strong>zentyal-sogo</strong> - Zentyal OpenChange Webmail</li>
<li><strong>zentyal-usercorner</strong> - Zentyal User Corner</li>
<li><strong>zentyal-users</strong> - Zentyal Users and Computers</li>
<li><strong>zentyal-webmail</strong> - Zentyal Webmail Service</li>
</ul>

<h2 id="accessing-the-zentyal-dashboard">Accessing the Zentyal Dashboard</h2>

<p>Access the Zentyal dashboard by visiting the IP address or domain of your server in your browser, over HTTPS (port 443):</p>

<p>https://<span class="highlight">SERVER IP</span></p>

<p>The Zentyal server creates a self-signed SSL certificate for use when being accessed remotely. Any browser accessing the server's dashboard remotely will be asked if the site is trusted and an exception will need to be made as shown below. The method will vary based on your browser.</p>

<p>Because of the SSL certificate, an error is generated that the site is untrusted. You need to click on the line <strong>I Understand the Risks.</strong> Then click on the <span class="highlight">Add Exception</span> button. Select <span class="highlight">Confirm Security Exception</span>. After the exception is added, it is a permanent listing that does not occur again unless the server IP Address should change.</p>

<p><img src="https://assets.digitalocean.com/articles/Install_Zentyal/Figure%202.jpg" alt="Certificate warning" /></p>

<p><img src="https://assets.digitalocean.com/articles/Install_Zentyal/Figure%203.jpg" alt="Certificate exception" /></p>

<p>You should see the dashboard login page.</p>

<p><img src="https://assets.digitalocean.com/articles/Install_Zentyal/Figure%201.jpg" alt="Zentyal dashboard login page" /></p>

<p>Your Zentyal username and password are the same user and password that you use to SSH to your Ubuntu server. This user must be added to the sudo group. (Granting full permissions to the user by some other method will NOT work.) If an existing user account needs to be added to the sudo group, run the following command:</p>
<pre class="code-pre "><code langs="">sudo adduser <span class="highlight">username</span> sudo
</code></pre>
<p>To add more Zentyal users, add new Ubuntu users. To add a new user use the following command to create the user and also add the user to the sudo group:</p>
<pre class="code-pre "><code langs="">sudo adduser <span class="highlight">username</span> --ingroup sudo
</code></pre>
<p>Once you log into the Zentyal server, you will see a collection of packages available for installation.</p>

<p><img src="https://assets.digitalocean.com/articles/Install_Zentyal/Figure%204.jpg" alt="Zentyal dashboard package list" /></p>

<p>You can also see a module list at https://<span class="highlight">SERVER IP</span>/Software/EBox as shown below.</p>

<p><img src="https://assets.digitalocean.com/articles/Install_Zentyal/Figure%205.jpg" alt="Zentyal dashboard component list" /></p>

<h2 id="installing-packages-dashboard">Installing Packages (Dashboard)</h2>

<p>You can install Zentyal packages from the dashboard. There are four basic profiles which install many related modules at once. You can see what's installed with each profile <a href="http://www.zentyal.org/server/#server-features">here</a>. Or, check the list below:</p>

<p><strong>Office:</strong></p>

<p>This profile sets up shared office resources like files, printers, calendars, user profiles, and groups.</p>

<ul>
<li><p>Samba4</p></li>
<li><p>Heimdal Kerberos</p></li>
<li><p>CUPS</p></li>
<li><p>Duplicity</p></li>
</ul>

<p><strong>Communication:</strong></p>

<p>This profile includes email, instant messaging, and Voice Over IP (VOIP).</p>

<ul>
<li><p>Postfix</p></li>
<li><p>Dovecot</p></li>
<li><p>Roundcube</p></li>
<li><p>Sieve</p></li>
<li><p>Fetchmail</p></li>
<li><p>Spamassassin</p></li>
<li><p>ClamAV</p></li>
<li><p>Postgrey</p></li>
<li><p>OpenChange</p></li>
<li><p>Roundcube</p></li>
<li><p>ejabbered</p></li>
</ul>

<p><strong>Gateway:</strong></p>

<p>This profile includes software to control and secure Internet access.</p>

<ul>
<li><p>Corosync</p></li>
<li><p>Pacemaker</p></li>
<li><p>Netfilter</p></li>
<li><p>Iproute2</p></li>
</ul>

<p><strong>Linux networking subsystem:</strong></p>

<ul>
<li><p>Iproute2</p></li>
<li><p>Squid</p></li>
<li><p>Dansguardian</p></li>
<li><p>ClamAV</p></li>
<li><p>FREERadius</p></li>
<li><p>OpenVPN</p></li>
<li><p>OpenSWAN</p></li>
<li><p>xl2tpd</p></li>
<li><p>Suricata</p></li>
<li><p>Amavisd-new</p></li>
<li><p>Spamassasin</p></li>
<li><p>ClamAV</p></li>
<li><p>Postgrey</p></li>
</ul>

<p><strong>Infrastructure:</strong></p>

<p>This profile allows you to manage the office network, including NTP, DHCP, DNS, etc.</p>

<ul>
<li><p>ISC DHCP</p></li>
<li><p>BIND 9</p></li>
<li><p>NTPd</p></li>
<li><p>OpenSSL</p></li>
<li><p>Apache</p></li>
<li><p>NUT</p></li>
</ul>

<p>In the left-hand navigation, go to "Software Management" then "Zentyal Components" – You'll see the four profiles at the top. (Or, click <strong>View basic mode</strong> to see the four profiles.)</p>

<p><img src="https://assets.digitalocean.com/articles/Install_Zentyal/Figure%2011.jpg" alt="Zentyal dashboard component list, profiles" /></p>

<p>Below the profiles is a list of all the modules you can install individually.</p>

<p><img src="https://assets.digitalocean.com/articles/Install_Zentyal/Figure%2012.jpg" alt="Zentyal dashboard component list, modules" /></p>

<p>The previous images show the basic view. If you click on <strong>View advanced mode</strong>, the screen should look like this:</p>

<p><img src="https://assets.digitalocean.com/articles/Install_Zentyal/Figure%2013.jpg" alt="Zentyal dashboard component list, advanced mode" /></p>

<p>Once you have selected your modules, click the <strong>INSTALL</strong> button at the bottom of the page.</p>

<p>Once the packages are installed, you'll see links for them in the dashboard navigation menu on the left. You can start setting up your new software through the Zentyal dashboard by navigating to the appropriate menu item in the control panel.</p>

<h2 id="updating-packages-dashboard">Updating Packages (Dashboard)</h2>

<p>It's important to keep your system up to date with the latest security patches and features.</p>

<p>Let's install some updates from the dashboard. Click the <strong>Dashboard</strong> link on the left. In the image below, you can see there are 26 System Updates, with 12 of them being Security Updates. To start the system update, simply click on <strong>26 system updates (12 security)</strong>.</p>

<p><img src="https://assets.digitalocean.com/articles/Install_Zentyal/Figure%206.jpg" alt="Zentyal dashboard update notification" /></p>

<p>This will take you to the <strong>System updates</strong> page with a list of all updates available for the Zentyal server.</p>

<p><img src="https://assets.digitalocean.com/articles/Install_Zentyal/Figure%207.jpg" alt="Zentyal dashboard update list" /></p>

<p>Here you can check the items you wish to update. At the bottom is an item to <strong>Update all packages</strong> as shown below.</p>

<p><img src="https://assets.digitalocean.com/articles/Install_Zentyal/Figure%208.jpg" alt="Zentyal dashboard update notification" /></p>

<p>Once you have selected the necessary updates, you can click on the <strong>UPDATE</strong> button at the bottom of the page. The download and installation of the update packages will begin as shown below.</p>

<p><img src="https://assets.digitalocean.com/articles/Install_Zentyal/Figure%209.jpg" alt="Zentyal dashboard update notification" /></p>

<p>Once done, you should see a screen similar to the one below, which shows that the update successfully completed.</p>

<p><img src="https://assets.digitalocean.com/articles/Install_Zentyal/Figure%2010.jpg" alt="Zentyal dashboard update notification" /></p>

<p>Once the update is completed, you can press the <strong>UPDATE LIST</strong> button to verify that no other updates are available.</p>

<h2 id="conclusion">Conclusion</h2>

<p>For a small or medium business, Zentyal is a server that can do it all. Services can be enabled as they are needed and disabled when they are not needed. Zentyal is also user-friendly enough that novice administrators can perform system updates and profile/module installation, using the command line or the Graphical User Interface (GUI).</p>

<p>If needed, multiple Zentyal servers can be used to distribute the services required by the business to create a more efficient network.</p>

    