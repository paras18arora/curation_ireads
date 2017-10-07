<link rel='stylesheet' href='digital_ocean.css'><br><img src=https://community-cdn-digitalocean-com.global.ssl.fastly.net/assets/tutorials/images/large/Securely-Browse-Internet-Open-VPN-TW.png?1443035271/> <br> 
      <h3 id="introduction">Introduction</h3>

<p>Reasons for browsing the Internet with more privacy vary as much as the ways to achieve it.</p>

<p>In this tutorial we will explain in detail how to set up a <em>virtual private network</em> (VPN) on a server so it secures three important components of your Internet browsing experience:</p>

<ul>
<li>Privatize your web traffic by securing unencrypted traffic, preventing cookies and other trackers, and masking your local computer's IP address</li>
<li>Prevent your local ISP from logging DNS queries, by sending them from the VPN straight to Google's DNS servers</li>
<li>Scan for and prevent access to viruses and malicious applications</li>
</ul>

<p>By running your own VPN server rather than using a commercial one, you can also avoid logging your browsing history (unless you choose to do so). Finally, you get to choose its physical location, so you can minimize latency. However, using a VPN is usually slower than using a direct Internet connection.</p>

<p>We'll do this by installing and configuring the following applications on your Debian 8 server:</p>

<ul>
<li><p><strong><a href="http://www.clamav.net/index.html">ClamAV</a></strong> is an open source antivirus engine for detecting trojans, viruses, malware, other malicious threats</p></li>
<li><p><strong><a href="http://www.thekelleys.org.uk/dnsmasq/doc.html">Dnsmasq</a></strong> is a software package that provides DNS (and few more) services. We will use it only as a DNS cache </p></li>
<li><p><strong><a href="http://www.server-side.de/documentation.htm">HAVP</a></strong> HTTP AntiVirus proxy is a proxy with an anti-virus filter. It does not cache or filter content. It scans all the traffic with third-party antivirus engines. In this tutorial we will use <code>HAVP</code> as a <em>Transparent Proxy</em> and chain <code>HAVP</code>and <code>Privoxy</code> together</p></li>
<li><p><strong><a href="https://openvpn.net/index.php/open-source.html">OpenVPN Community Edition</a></strong> is a popular VPN server. It provides a secure connection to your trusted server, and can also push DNS Server settings to its clients. In this tutorial the term <em>OpenVPN</em> will be used as the shortened form of the VPN server's name</p></li>
<li><p><strong><a href="http://www.privoxy.org/">Privoxy</a></strong> is, from the official website, <em>a non-caching web proxy with advanced filtering capabilities for enhancing privacy, modifying web page data and HTTP headers, controlling access, and removing ads and other obnoxious Internet junk</em></p></li>
</ul>

<p>After completing this tutorial, you will have a privacy gateway that:</p>

<ul>
<li> Secures your connection when using public WiFi spots</li>
<li> Blocks advertisements and tracking features from web sites</li>
<li> Speeds up web page loading times by caching server-side DNS responses</li>
<li> Scans the pages you visit and files you download for known viruses</li>
</ul>

<h3 id="how-it-works">How It Works</h3>

<p>The following diagram displays the path that a web request follows through the VPN we will set up in this tutorial. </p>

<p>The lanes with green backgrounds are the components of the VPN server. Green boxes represent the request steps, and blue and red boxes represent the response steps. </p>

<p><img src="https://assets.digitalocean.com/articles/3-openvpn-examples/openvpn-final.png" alt="Flow chart of web request through VPN server" /></p>

<p>The traffic between your computer and the privacy server will flow through a VPN tunnel. When you open a web page in your browser, your request will be transferred to the VPN server. On the VPN server, your request will be redirected to HAVP and subsequently to Privoxy.</p>

<p>Privoxy will match the URL against its database of patterns. If the URL matches, it will block the URL and return a valid but empty response. </p>

<p>If the URL is not blocked, Privoxy acts as a non-caching proxy server to query DNS and retrieve the content of the URL. DNS queries are handled and cached by Dnsmasq.</p>

<p>HAVP receives the content from Privoxy and performs a virus scan via ClamAV. If any virus is found it returns an error page.</p>

<h2 id="prerequisites">Prerequisites</h2>

<p>Please make sure you complete the following prerequisites:</p>

<ul>
<li>Debian 8 Droplet with <strong>1 GB</strong> of RAM</li>
<li><a href="https://indiareads/community/tutorials/initial-server-setup-with-debian-8">Initial Server Setup with Debian 8</a></li>
<li><a href="https://indiareads/community/tutorials/how-to-set-up-an-openvpn-server-on-debian-8">How To Set Up an OpenVPN Server on Debian 8</a></li>
</ul>

<p><strong>System Requirements</strong></p>

<p>The server we will configure will be easy on CPU, RAM, and disk space. Select a Droplet with at least 1GB of RAM and that provides enough bandwidth to accommodate your browsing needs.</p>

<p>The operating system of choice for this tutorial is Debian 8. It should also work more or less the same way for other Debian-based Linux distros like Ubuntu.</p>

<p><strong>Licenses</strong></p>

<p>All of the software used in this tutorial is available from Debian repositories and subject to <a href="https://www.debian.org/doc/debian-policy">Debian policies</a>. </p>

<p><strong>Security</strong></p>

<p>This server will intercept all of your HTTP requests. Someone who takes control of this server could act as a man-in-the-middle and monitor all of your HTTP traffic, redirect DNS requests, etc. You <strong>do</strong> need to secure your server. Please refer to the tutorials mentioned in the beginning of this section to set up sudo access and a firewall as an initial level of protection. </p>

<h2 id="step-1-—-installing-openvpn-and-other-prerequisites">Step 1 — Installing OpenVPN and Other Prerequisites</h2>

<p>If you have not yet installed OpenVPN please do so now.</p>

<p>You can follow the tutorial <a href="https://indiareads/community/tutorials/how-to-set-up-an-openvpn-server-on-debian-8">How To Set Up an OpenVPN Server on Debian 8</a>.</p>

<p>In the following steps we will install a few packages. To make sure your package indexes are up to date, please execute the following command.</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo apt-get update
</li></ul></code></pre>
<p>If you have not yet enabled <code>ssh</code> in your UFW firewall setup, pease do so with the following commands.</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo ufw allow ssh
</li><li class="line" prefix="$">sudo ufw enable
</li></ul></code></pre>
<h2 id="step-2-—-installing-dnsmasq">Step 2 — Installing Dnsmasq</h2>

<p>In this step we will install and configure Dnsmasq. Our privacy proxy server will use Dnsmasq to speed up and secure its DNS queries.</p>

<p>Every time you connect to a web page, your computer tries to resolve the Internet address of that server by asking a DNS (Domain Name System) server. Your computer uses the DNS servers of your ISP by default.</p>

<p>Using your own DNS server has the following advantages:</p>

<ul>
<li>Your ISP will not have any knowledge of the host names you connect to</li>
<li>Your ISP cannot redirect your requests to other servers, which is one of the main methods of censorship</li>
<li>Your DNS lookup speed will improve</li>
</ul>

<p><span class="warning">The DNS servers you choose will know about all the DNS requests you make to them and can use this information to profile your browsing habits, redirect your searches to their own engines, or prevent your access to unapproved web sites. Choose your DNS servers wisely. OpenDNS and Google DNS servers are generally considered safe.<br /></span></p>

<p>On a Debian system, nameserver configuration is kept in a file named <code>/etc/resolv.conf</code>.</p>

<p>Check your current nameserver configuration with the following command.</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">cat /etc/resolv.conf
</li></ul></code></pre>
<p>Output:</p>
<div class="code-label " title="/etc/resolv.conf">/etc/resolv.conf</div><pre class="code-pre "><code langs=""># Dynamic resolv.conf(5) file for glibc resolver(3) generated by resolvconf(8)
#     DO NOT EDIT THIS FILE BY HAND -- YOUR CHANGES WILL BE OVERWRITTEN
nameserver 8.8.8.8
nameserver 8.8.4.4
</code></pre>
<p>As you can see, the default nameservers on this system are set to Google's DNS servers.</p>

<p>Now install <code>dnsmasq</code> with the following command:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo apt-get install dnsmasq
</li></ul></code></pre>
<p>After the package is installed check your configuration again:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">cat /etc/resolv.conf
</li></ul></code></pre>
<p>Output:</p>
<div class="code-label " title="/etc/resolv.conf">/etc/resolv.conf</div><pre class="code-pre "><code langs=""># Dynamic resolv.conf(5) file for glibc resolver(3) generated by resolvconf(8)
#     DO NOT EDIT THIS FILE BY HAND -- YOUR CHANGES WILL BE OVERWRITTEN
nameserver 127.0.0.1
</code></pre>
<p>The default nameserver is set to <strong>127.0.0.1</strong>, which is the local interface Dnsmasq runs on.</p>

<p>You can test the installation with the following command. Take note of the query time in the output.</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">dig digitalocean.com <a href="https://indiareads/community/users/localhost" class="username-tag">@localhost</a>
</li></ul></code></pre>
<p>Output:</p>
<pre class="code-pre "><code langs=""><div class="secondary-code-label " title="Output">Output</div>. . .

;; Query time: <span class="highlight">20 msec</span>
;; SERVER: 127.0.0.1#53(127.0.0.1)

. . .
</code></pre>
<p>Now run the same command again and check the query time:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">dig digitalocean.com <a href="https://indiareads/community/users/localhost" class="username-tag">@localhost</a>
</li></ul></code></pre>
<p>Output:</p>
<pre class="code-pre "><code langs=""><div class="secondary-code-label " title="Output">Output</div>. . .

;; Query time: <span class="highlight">1 msec</span>
;; SERVER: 127.0.0.1#53(127.0.0.1)

. . .
</code></pre>
<p>Our second query is answered by <code>dnsmasq</code> from cache. The response time went down from 20 milliseconds to 1 millisecond. Depending on the load of your system, the cached results are usually returned in under 1 millisecond.</p>

<h2 id="step-3-—-installing-clamav">Step 3 — Installing ClamAV</h2>

<p>Let's install our antivirus scanner so our VPN will protect us from known malicious downloads.</p>

<h3 id="install-clamav">Install ClamAV</h3>

<p>ClamAV is a widely used open-source antivirus scanner. </p>

<p>Install ClamAV and its scanner deamon:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo apt-get install clamav clamav-daemon
</li></ul></code></pre>
<h3 id="update-virus-database">Update Virus Database</h3>

<p>ClamAV will update its database right after the installation and check for updates every hour.</p>

<p>ClamAV logs its database update status to <code>/var/log/clamav/freshclam.log</code>. You can check this file to see how its automatic updates are processing. </p>

<p>Now we will wait until automatic updates are completed; otherwise, our scanning proxy (HAVP) will complain and will not start.</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo tail -f /var/log/clamav/freshclam.log
</li></ul></code></pre>
<p>During update progress, the current status will be written to screen.</p>
<pre class="code-pre "><code langs=""><div class="secondary-code-label " title="Output">Output</div>Fri Jun 19 12:56:03 2015 -> ClamAV update process started at Fri Jun 19 12:56:03 2015
Fri Jun 19 12:56:12 2015 -> Downloading main.cvd [100%]
Fri Jun 19 12:56:21 2015 -> main.cvd updated (version: 55, sigs: 2424225, f-level: 60, builder: neo)
Fri Jun 19 12:56:28 2015 -> Downloading daily.cvd [100%]
Fri Jun 19 12:56:34 2015 -> daily.cvd updated (version: 20585, sigs: 1430267, f-level: 63, builder: neo)
Fri Jun 19 12:56:35 2015 -> Downloading bytecode.cvd [100%]
Fri Jun 19 12:56:35 2015 -> bytecode.cvd updated (version: 260, sigs: 47, f-level: 63, builder: shurley)
Fri Jun 19 12:56:41 2015 -> Database updated (3854539 signatures) from db.local.clamav.net (IP: 200.236.31.1)
Fri Jun 19 12:56:55 2015 -> <span class="highlight">Clamd successfully notified about the update.</span>
Fri Jun 19 12:56:55 2015 -> --------------------------------------
</code></pre>
<p>Wait until you see the text marked in red, <code><span class="highlight">Clamd successfully notified about the update.</span></code>.</p>

<p>Press <code>CTRL+C</code> on your keyboard to exit the tail. This will return you to the command prompt.</p>

<p>You can continue with the <strong>Configure ClamAV</strong> section if everything went normally.</p>

<h3 id="optional-troubleshooting">(Optional) Troubleshooting</h3>

<p>If the virus update takes too long, you can invoke it manually. This will not be needed in normal circumstances.</p>

<p>Stop the autoupdate service.</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo service clamav-freshclam stop
</li></ul></code></pre>
<p>Invoke the updater manually and wait for its completion. Download progress will be shown in percentages.</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo freshclam
</li></ul></code></pre>
<p>Start the autoupdate service:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo service clamav-freshclam start
</li></ul></code></pre>
<h3 id="configure-clamav">Configure ClamAV</h3>

<p>Now we will allow other groups to access ClamAV. This is needed because we will configure a virus scanning proxy (HAVP) to use ClamAV in the following steps.</p>

<p>Edit the ClamAV configuration file <code>clamd.conf</code> with your favorite text editor.</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo vi /etc/clamav/clamd.conf
</li></ul></code></pre>
<p>Set the following parameter to <code>true</code>.</p>
<div class="code-label " title="/etc/clamav/clamd.conf">/etc/clamav/clamd.conf</div><pre class="code-pre "><code langs="">AllowSupplementaryGroups <span class="highlight">true</span>
</code></pre>
<p>Save the configuration and exit.</p>

<p>Restart <code>clamav-daemon</code></p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo service clamav-daemon restart
</li></ul></code></pre>
<h2 id="step-4-—-installing-havp">Step 4 — Installing HAVP</h2>

<p>HAVP is a virus scanning proxy server. It scans every item on the pages you visit and blocks malicious content. HAVP does not contain a virus scanner engine but can use quite a few third party engines. In this tutorial we will configure it with ClamAV. </p>

<p>Install HAVP from Debian repositories.</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo apt-get install havp
</li></ul></code></pre>
<p><span class="note">If there is not enough memory for ClamAV libraries, HAVP might not start. You can ignore this error (for now) and continue with the setup.<br /></span></p>

<p>Installation will take a while, so please be patient.</p>

<h3 id="editing-the-configuration-file">Editing the Configuration File</h3>

<p>Load HAVP's configuration file in your favorite editor:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo vi /etc/havp/havp.config
</li></ul></code></pre>
<p>We will need to set a few configuration options to make HAVP run with the ClamAV daemon. </p>

<p>HAVP can work with the ClamAV libraries (by default) or the ClamAV daemon. Library mode requires much more RAM than daemon (socket scanner) mode. If your Droplet has 4 GB or more of RAM, you can set <code>ENABLECLAMLIB</code> to <code>true</code> and use library mode.</p>

<p>Otherwise, use these settings, located near the bottom of the configuration file.</p>
<div class="code-label " title="/etc/havp/havp.config">/etc/havp/havp.config</div><pre class="code-pre "><code langs="">ENABLECLAMLIB <span class="highlight">false</span>

. . .

ENABLECLAMD <span class="highlight">true</span>
</code></pre>
<p>HAVP's default configuration might interfere with some video streaming sites. To allow <em>HTTP Range Requests</em>, set the following parameter.</p>
<div class="code-label " title="/etc/havp/havp.config">/etc/havp/havp.config</div><pre class="code-pre "><code langs="">RANGE <span class="highlight">true</span>
</code></pre>
<p>A lot of content on the Internet consists of images. Although there are some exploits that uses images as vectors, it is more or less safe not to scan images.</p>

<p>We recommend setting <code>SCANIMAGES</code> to <code>false</code>, but you can leave this setting as <code>true</code> if you want HAVP to scan images. </p>
<div class="code-label " title="/etc/havp/havp.config">/etc/havp/havp.config</div><pre class="code-pre "><code langs="">SCANIMAGES <span class="highlight">false</span>
</code></pre>
<p>Do not scan files that have image, video, and audio MIME types. This setting will improve performance and enable you to watch streaming video content (provided the VPN as a whole has enough bandwidth). Uncomment this line to enable it.</p>
<div class="code-label " title="/etc/havp/havp.config">/etc/havp/havp.config</div><pre class="code-pre "><code langs="">SKIPMIME image/* video/* audio/*
</code></pre>
<p>There is one more parameter that we will change.</p>

<p>This parameter will tell HAVP not to log successful requests to the log file at <code>/var/log/havp/access.log</code>. Leave the default value (<code>true</code>) if you want to check the access logs to see if HAVP is working. For production, set this parameter to <code>false</code> in order to improve performance and privacy.</p>
<div class="code-label " title="/etc/havp/havp.config">/etc/havp/havp.config</div><pre class="code-pre "><code langs="">LOG_OKS <span class="highlight">false</span>
</code></pre>
<p>Save your changes and exit the file.</p>

<h3 id="user-configuration">User Configuration</h3>

<p>Remember when we configured ClamAV to be accessed by other groups?</p>

<p>Now, we will add the <strong>clamav</strong> user to the <strong>havp</strong> group and allow HAVP to access ClamAV. Execute the following command:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo gpasswd -a clamav havp
</li></ul></code></pre>
<p>Output:</p>
<pre class="code-pre "><code langs=""><div class="secondary-code-label " title="Output">Output</div>Adding user clamav to group havp
</code></pre>
<p>We need to restart <code>clamav-daemon</code> for our changes to groups to take effect.</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo service clamav-daemon restart
</li></ul></code></pre>
<p>Now that we've configured HAVP, we can start it with the following command:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo service havp restart
</li></ul></code></pre>
<p>Service restart commands should complete silently; there should be no messages displayed on the console.</p>

<h3 id="checking-the-logs">Checking the Logs</h3>

<p>HAVP stores its log files in the <code>/var/log/havp</code> directory. Error and initialization messages goes into the <code>error.log</code> file. You can check the status of HAVP by checking this file.</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo tail /var/log/havp/error.log
</li></ul></code></pre>
<p>The <code>tail</code> command displays the last few lines of the file. If HAVP has started successfully, you will see something like the output shown below. Of course, the date and time will be your system's:</p>
<pre class="code-pre "><code langs=""><div class="secondary-code-label " title="Output">Output</div>17/06/2015 12:48:13 === Starting HAVP Version: 0.92
17/06/2015 12:48:13 Running as user: havp, group: havp
17/06/2015 12:48:13 --- Initializing Clamd Socket Scanner
17/06/2015 12:48:22 Clamd Socket Scanner passed EICAR virus test (Eicar-Test-Signature)
17/06/2015 12:48:22 --- All scanners initialized
17/06/2015 12:48:22 Process ID: 3896
</code></pre>
<h2 id="step-5-—-testing-havp">Step 5 — Testing HAVP</h2>

<p>In this section we'll make sure HAVP is actually blocking viruses.</p>

<p>The log shown above mentions something called the <code>EICAR virus test</code>.</p>

<p>On initialization HAVP tests the virus scanner engines with a specially constructed virus signature. All virus scanner software detects files that contain this (harmless) signature as a virus. You can get more information about EICAR on the <a href="http://www.eicar.org/86-0-Intended-use.html">EICAR Intended Use</a> page.</p>

<p>Let's do our own manual test with the EICAR file and see that HAVP and ClamAV block it.</p>

<p>We will use the <code>wget</code> command line utility to download file from EICAR web page.</p>

<p>First, download the EICAR test file without using a proxy:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">wget http://www.eicar.org/download/eicar.com -O /tmp/eicar.com
</li></ul></code></pre>
<p>Your server will download the file without complaint:</p>
<pre class="code-pre "><code langs=""><div class="secondary-code-label " title="Output">Output</div>converted 'http://www.eicar.org/download/eicar.com' (ISO-8859-1) -> 'http://www.eicar.org/download/eicar.com' (UTF-8)
--2015-06-16 13:53:41--  http://www.eicar.org/download/eicar.com
Resolving www.eicar.org (www.eicar.org)... 188.40.238.250
Connecting to www.eicar.org (www.eicar.org)|188.40.238.250|:80... connected.
HTTP request sent, awaiting response... 200 OK
Length: 68 [application/octet-stream]
Saving to: '/tmp/eicar.com'

/tmp/eicar.com       100%[=====================>]      68  --.-KB/s   in 0s

2015-06-16 13:53:41 (13.7 MB/s) - '/tmp/eicar.com' saved [68/68]
</code></pre>
<p>As you can see, <code>wget</code> downloaded the test file containing the virus signature without any complaints. </p>

<p>Now let's try to download the same file with our newly-configured proxy. We will set the environment variable <code>http_proxy</code> to our HAVP address and port.</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">http_proxy=127.0.0.1:8080 wget http://www.eicar.org/download/eicar.com -O /tmp/eicar.com
</li></ul></code></pre>
<p>Output:</p>
<pre class="code-pre "><code langs=""><div class="secondary-code-label " title="Output">Output</div>converted 'http://www.eicar.org/download/eicar.com' (ISO-8859-1) -> 'http://www.eicar.org/download/eicar.com' (UTF-8)
--2015-06-25 20:47:38--  http://www.eicar.org/download/eicar.com
Connecting to 127.0.0.1:8080... connected.
Proxy request sent, awaiting response... 403 Virus found by HAVP
2015-06-25 20:47:39 <span class="highlight">ERROR 403: Virus found by HAVP.</span>
</code></pre>
<p>Our proxy successfully intercepted the download and blocked the virus.</p>

<p>EICAR also provides a virus signature file hidden inside a ZIP compressed file.</p>

<p>You can test that HAVP scans files inside ZIP archives with the following command:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">http_proxy=127.0.0.1:8080 wget http://www.eicar.org/download/eicarcom2.zip -O /tmp/eicarcom2.zip
</li></ul></code></pre>
<p>Output:</p>
<pre class="code-pre "><code langs=""><div class="secondary-code-label " title="Output">Output</div>converted 'http://www.eicar.org/download/eicarcom2.zip' (ISO-8859-1) -> 'http://www.eicar.org/download/eicarcom2.zip' (UTF-8)
--2015-06-25 20:48:28--  http://www.eicar.org/download/eicarcom2.zip
Connecting to 127.0.0.1:8080... connected.
Proxy request sent, awaiting response... 403 Virus found by HAVP
2015-06-25 20:48:28 <span class="highlight">ERROR 403: Virus found by HAVP.</span>
</code></pre>
<p>HAVP (with ClamAV) found the virus again.</p>

<h2 id="step-6-—-installing-privoxy">Step 6 — Installing Privoxy</h2>

<p>So far we have configured a proxy server to scan web pages for viruses. What about ads and tracking cookies? In this step we will install and configure Privoxy.</p>

<p><span class="note">Blocking advertisements is harmful to the web sites that rely on advertisements to cover operational costs. Please consider adding exceptions to the sites that you trust and frequent.<br /><br /></span></p>

<p>Use the following command to install Privoxy:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo apt-get install privoxy
</li></ul></code></pre>
<p>Privoxy's configuration resides in the file <code>/etc/privoxy/config</code>. We need to set two parameters before we start using Privoxy.</p>

<p>Open the config file in your favorite editor.</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo vi /etc/privoxy/config
</li></ul></code></pre>
<p>Now uncomment and set the following two parameters:</p>
<div class="code-label " title="/etc/privoxy/config">/etc/privoxy/config</div><pre class="code-pre "><code langs="">listen-address  <span class="highlight">127.0.0.1:8118</span>

. . .

hostname <span class="highlight">your_server</span>
</code></pre>
<p>The parameter <code>listen-address</code> determines on which IP and port privoxy runs. The default value is <code>localhost:8118</code>; we will change this to <code>127.0.0.1:8118</code>.</p>

<p>The parameter <code>hostname</code> specifies the host Privoxy runs on and logs; set this to the hostname or DNS address of your server. It can be any valid hostname.</p>

<p>Now, restart Privoxy with its new configuration.</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo service privoxy restart
</li></ul></code></pre>
<h2 id="step-7-—-chaining-havp-to-privoxy">Step 7 — Chaining HAVP to Privoxy</h2>

<p>HAVP and Privoxy both are essentially HTTP proxy servers. We will now <em>chain</em> these two proxies so that, when your client requests a web page from HAVP, it will forward this request to Privoxy. Privoxy will retrieve the requested web page, remove the privacy threats and ads, and then HAVP will further process the response and remove viruses and malicious code.</p>

<p>Load the HAVP configuration file into your favorite text editor:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo vi /etc/havp/havp.config
</li></ul></code></pre>
<p>Uncomment the following lines (remove the <code>#</code> character at the beginning of the lines) and set their values as shown below. Privoxy runs on IP <code>127.0.0.1</code> and port <code>8118</code>.</p>
<div class="code-label " title="/etc/havp/havp.config">/etc/havp/havp.config</div><pre class="code-pre "><code langs="">PARENTPROXY <span class="highlight">127.0.0.1</span>
PARENTPORT <span class="highlight">8118</span>
</code></pre>
<p>Save your changes and exit the file.</p>

<p>Restart HAVP for the changes to take effect:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo service havp restart
</li></ul></code></pre>
<p>Check HAVP's error log, taking note of the <code>Use parent proxy: 127.0.0.1:8118</code> message.</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo tail /var/log/havp/error.log
</li></ul></code></pre>
<p>Output:</p>
<pre class="code-pre "><code langs=""><div class="secondary-code-label " title="Output">Output</div>17/06/2015 12:57:37 === Starting HAVP Version: 0.92
17/06/2015 12:57:37 Running as user: havp, group: havp
17/06/2015 12:57:37 <span class="highlight">Use parent proxy: 127.0.0.1:8118</span>
17/06/2015 12:57:37 --- Initializing Clamd Socket Scanner
17/06/2015 12:57:37 Clamd Socket Scanner passed EICAR virus test (Eicar-Test-Signature)
17/06/2015 12:57:37 --- All scanners initialized
17/06/2015 12:57:37 Process ID: 4646
</code></pre>
<p>Our proxy server configuration is now complete. Lets test it again with the EICAR virus test.</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">http_proxy=127.0.0.1:8080 wget http://www.eicar.org/download/eicarcom2.zip -O /tmp/eicarcom2.zip
</li></ul></code></pre>
<p>If your configuration is good, you should again see the <code>ERROR 403: Virus found by HAVP</code> message.</p>

<h2 id="step-8-—-setting-dns-options-for-openvpn-server">Step 8 — Setting DNS Options for OpenVPN Server</h2>

<p>Although the default configuration of OpenVPN Server is adequate for our needs, it is possible to improve it a little bit more. </p>

<p>Load the OpenVPN server's configuration file in a text editor:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo vi /etc/openvpn/server.conf
</li></ul></code></pre>
<p>OpenVPN is configured to use OpenDNS's servers by default. If you want to change it to use Google's DNS servers, change the <code>dhcp-option DNS</code> parameters as below.</p>

<p>Add the new line <code>push "register-dns"</code>, which some Windows clients might need in order to use the DNS servers.</p>

<p>Also, add the new line <code>push "block-ipv6"</code> to block IPv6 while connected to VPN. (IPv6 traffic can bypass our VPN server.)</p>

<p>Here's what this section should look like:</p>
<div class="code-label " title="/etc/openvpn/server.conf">/etc/openvpn/server.conf</div><pre class="code-pre "><code langs="">push "dhcp-option DNS 8.8.8.8"
push "dhcp-option DNS 8.8.4.4"
push "register-dns"
push "block-ipv6"
</code></pre>
<p>If you want to allow multiple clients to connect with the same <em>ovpn</em> file, uncomment the following line. (This is convenient but NOT more secure!)</p>
<div class="code-label " title="/etc/openvpn/server.conf">/etc/openvpn/server.conf</div><pre class="code-pre "><code langs="">duplicate-cn
</code></pre>
<p>Restart the OpenVPN service for changes to take effect.</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo service openvpn restart
</li></ul></code></pre>
<h2 id="step-9-—-configuring-your-transparent-proxy">Step 9 — Configuring Your Transparent Proxy</h2>

<p>We will now set up our privacy server to intercept the HTTP traffic between its clients (your browser) and the internet. </p>

<h3 id="enable-packet-forwarding">Enable Packet Forwarding</h3>

<p>For our server to forward HTTP traffic to the proxy server, we need to enable packet forwarding. You should have enabled it already in the OpenVPN setup tutorial.</p>

<p>Test the configuration with the following command.</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo sysctl -p
</li></ul></code></pre>
<p>It should display the changed parameters as below. If it does not, please revisit the OpenVPN tutorial.</p>
<pre class="code-pre "><code langs=""><div class="secondary-code-label " title="Output">Output</div>net.ipv4.ip_forward = 1
</code></pre>
<h3 id="configure-ufw">Configure UFW</h3>

<p>We need to forward HTTP packets that originate from OpenVPN clients to HAVP. We will use <code>ufw</code> for this purpose.</p>

<p>First we need to allow traffic originating from OpenVPN clients</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo ufw allow in on tun0 from 10.8.0.0/24
</li></ul></code></pre>
<p>In the OpenVPN tutorial, you should have changed the <code>/etc/ufw/before.rules</code> file and added some rules for OpenVPN. Now we will revisit the same file and configure port redirection for the transparent proxy. </p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo vi /etc/ufw/before.rules
</li></ul></code></pre>
<p>Change the lines you have added in the OpenVPN configuration as shown below. Add the lines in red.</p>
<div class="code-label " title="/etc/ufw/before.rules">/etc/ufw/before.rules</div><pre class="code-pre "><code langs=""> # START OPENVPN RULES
 # NAT table rules
*nat
<span class="highlight">:PREROUTING ACCEPT [0:0]</span>
:POSTROUTING ACCEPT [0:0]
<span class="highlight"># transparent proxy</span>
<span class="highlight">-A PREROUTING -i tun+ -p tcp --dport 80 -j REDIRECT --to-port 8080</span>
 # Allow traffic from OpenVPN client to eth0
-A POSTROUTING -s 10.8.0.0/8 -o eth0 -j MASQUERADE
COMMIT
 # END OPENVPN RULES
</code></pre>
<p>Reload your firewall configuration.</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo ufw reload
</li></ul></code></pre>
<p>Check UFW's status:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo ufw status
</li></ul></code></pre>
<p>Output:</p>
<pre class="code-pre "><code langs=""><div class="secondary-code-label " title="Output">Output</div>Status: active

To                         Action      From
--                         ------      ----
22                         ALLOW       Anywhere
1194/udp                   ALLOW       Anywhere
Anywhere on tun0           ALLOW       10.8.0.0/24
22                         ALLOW       Anywhere (v6)
1194/udp                   ALLOW       Anywhere (v6)
</code></pre>
<h3 id="enable-havp-39-s-transparent-mode">Enable HAVP's Transparent Mode</h3>

<p>In the previous steps, we forced all HTTP packets to go through HAVP. This configuration is called a <em>transparent proxy</em>. </p>

<p>We need to configure <code>HAVP</code> as such.</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo vi /etc/havp/havp.config
</li></ul></code></pre>
<p>Set the following parameter:</p>
<div class="code-label " title="/etc/havp/havp.config">/etc/havp/havp.config</div><pre class="code-pre "><code langs="">TRANSPARENT <span class="highlight">true</span>
</code></pre>
<p>Restart the HAVP service:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo service havp restart
</li></ul></code></pre>
<p>Our server is now ready to use. </p>

<h2 id="step-10-—-testing-client-configuration">Step 10 — Testing Client Configuration</h2>

<p>On your client (Windows, OS X, tablet ...) connect your client to your OpenVPN server. Note that you can use the same <code>.ovpn</code> file from the original OpenVPN tutorial; all the changes are on the server side.</p>

<p><span class="note">For detailed setup instructions for your OpenVPN client, please see <a href="https://indiareads/community/tutorials/how-to-set-up-an-openvpn-server-on-ubuntu-14-04#step-5-installing-the-client-profile">Installing the Client Profile</a> in the Ubuntu 14.04 tutorial.</span></p>

<p>After the VPN connection is established, you should see your preferred DNS settings in the OpenVPN client logs. The following sample is taken from the IOS client.</p>
<pre class="code-pre "><code langs="">DNS Servers
    8.8.8.8
    8.8.4.4
Search Domains:
</code></pre>
<p>If you use Tunnelblick, you might see a line like this:</p>
<pre class="code-pre "><code langs="">Changed DNS ServerAddresses setting from '8.8.8.8 208.67.222.222 8.8.4.4' to '8.8.8.8 8.8.4.4'
</code></pre>
<p>To test your configuration, go to the [EICAR test page](www.eicar.org) in your browser and attempt to download the EICAR test file. You should see a <strong>HAVP - Access Denied</strong> page.</p>

<ul>
<li><code>http://www.eicar.org/download/eicarcom2.zip</code></li>
<li><code>http://www.eicar.org/85-0-Download.html</code></li>
</ul>

<p><img src="https://assets.digitalocean.com/articles/3-openvpn-examples/havp-denied.png" alt="HAVP - Access Denied" /></p>

<h2 id="step-11-—-troubleshooting">Step 11 — Troubleshooting</h2>

<p>This section will help you troubleshoot some common issues.</p>

<h3 id="cannot-watch-videos-or-use-my-favorite-site">Cannot watch videos or use my favorite site</h3>

<p>Privoxy can be configured to be less strict with sites that are loading too slowly. This behavior is configured in the <code>user.action</code> configuration file. </p>

<p>Load the user action file in your favorite text editor.</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo vi /etc/privoxy/user.action
</li></ul></code></pre>
<p>Go to the end of file and add the following content with the additional site addresses you want. </p>
<div class="code-label " title="/etc/privoxy/user.action">/etc/privoxy/user.action</div><pre class="code-pre "><code langs="">{ fragile -deanimate-gifs }
.googlevideo.com
.youtube.com
.imgur.com
<span class="highlight">.example.com</span>
</code></pre>
<p>After these changes, you do not need to restart Privoxy. However, you should clear your browser's cache and refresh a few times.</p>

<p>If you still experience problems, add whitelisted domains to the HAVP whitelist file. HAVP will check this file and not perform a virus scan if the host name matches.</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">vi /etc/havp/whitelist
</li></ul></code></pre>
<p>Add your sites at the end of the file.</p>
<div class="code-label " title="/etc/havp/whitelist">/etc/havp/whitelist</div><pre class="code-pre "><code langs=""># Whitelist Windowsupdate, so RANGE is allowed too
*.microsoft.com/*
*.windowsupdate.com/*

<span class="highlight">*.youtube.com/*</span>
</code></pre>
<h3 id="browser-stops-responding-during-heavy-use-of-internet">Browser stops responding during heavy use of Internet</h3>

<p>If you open multiple web pages at once, your server's memory might not be enough for HAVP to scan all your requests.</p>

<p>You can try to increase your Droplet's RAM and/or add swap memory. Please refer to the <a href="https://indiareads/community/tutorials/how-to-configure-virtual-memory-swap-file-on-a-vps">How To Configure Virtual Memory (Swap File) on a VPS</a> article.</p>

<p>Keep in mind that adding a VPN to your browsing experience will add some latency in most cases.</p>

<h2 id="conclusion">Conclusion</h2>

<p>After following this tutorial, you'll have taken your VPN use to the next level with browsing privacy and security.</p>

    