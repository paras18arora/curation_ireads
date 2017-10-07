<link rel='stylesheet' href='digital_ocean.css'><br> <br> 
      <h3 id="introduction">Introduction</h3>

<p>Setting up a firewall for your infrastructure is a great way to provide some basic security for your services.  Once you've developed a policy you are happy with, the next step is to test your firewall rules.  It is important to get a good idea of whether your firewall rules are doing what you think they are doing and to get an impression of what your infrastructure looks like to the outside world.</p>

<p>In this guide, we'll go over some simple tools and techniques that you can use to validate your firewall rules.  These are some of the same tools that malicious users may use, so you will be able to see what information they can find by making requests of your servers.</p>

<h2 id="prerequisites">Prerequisites</h2>

<p>In this guide, we will assume that you have a firewall configured on at least one server.  You can get started building your firewall policy by following one or more of these guides:</p>

<ul>
<li>Iptables

<ul>
<li><a href="https://indiareads/community/tutorials/how-to-set-up-a-firewall-using-iptables-on-ubuntu-14-04">How To Set Up a Firewall Using Iptables on Ubuntu 14.04</a></li>
<li><a href="https://indiareads/community/tutorials/iptables-essentials-common-firewall-rules-and-commands">Iptables Essentials: Common Firewall Rules and Commands</a></li>
<li><a href="https://indiareads/community/tutorials/how-to-migrate-from-firewalld-to-iptables-on-centos-7">How To Migrate from FirewallD to Iptables on CentOS 7</a></li>
<li><a href="https://indiareads/community/tutorials/how-to-implement-a-basic-firewall-template-with-iptables-on-ubuntu-14-04">How To Implement a Basic Firewall Template with Iptables on Ubuntu 14.04</a></li>
</ul></li>
<li>UFW

<ul>
<li><a href="https://indiareads/community/tutorials/how-to-set-up-a-firewall-with-ufw-on-ubuntu-14-04">How To Set Up a Firewall with UFW on Ubuntu 14.04</a></li>
<li><a href="https://indiareads/community/tutorials/ufw-essentials-common-firewall-rules-and-commands">UFW Essentials: Common Firewall Rules and Commands</a></li>
</ul></li>
<li>FirewallD

<ul>
<li><a href="https://indiareads/community/tutorials/how-to-set-up-a-firewall-using-firewalld-on-centos-7">How To Set Up a Firewall Using FirewallD on CentOS 7</a></li>
</ul></li>
</ul>

<p>In this guide, we will call the server containing the firewall policies you wish to test the <strong>target</strong>.  In addition to your target, you will also need to have access to a server to test from, located outside of the network that your firewall protects.  In this guide, we will use an Ubuntu 14.04 server as our auditing machine.</p>

<p>Once you have a server to test from and the targets you wish to evaluate, you can continue with this guide.</p>

<p></p><div class="code-label notes-and-warnings warning" title="Warning">Warning</div><span class="warning">
You should only perform the activities outlined in this guide on infrastructure that you control, for the purpose of security auditing.  The laws surrounding port scanning are uncertain in many jurisdictions.  ISPs and other providers have been known to ban users who are found port scanning.<br /></span>

<h2 id="the-tools-we-will-use-to-test-firewall-policies">The Tools We Will Use to Test Firewall Policies</h2>

<p>There are quite a few different tools that we can use to test our firewall policies.  Some of them have overlapping functionality.  We will not cover every possible tool.  Instead, we will cover some general categories of auditing tools and go over the tools we will be using in this guide.</p>

<h3 id="packet-analyzers">Packet Analyzers</h3>

<p>Packet analyzers can be used to watch all of the network traffic that goes over an interface in great detail.  Most packet analyzers have the option of operating in real time, displaying the packets as they are sent or received, or of writing packet information to a file and processing it at a later time.  Packet analysis gives us the ability to see, at a granular level, what types of responses our target machines are sending back to hosts on the open network.</p>

<p>For the purposes of our guide, we will be using the <code>tcpdump</code> tool.  This is a good option because it is powerful, flexible, and rather ubiquitous on Linux systems.  We will use it to capture the raw packets as we run our tests in case we need the transcript for later analysis.  Some other popular options are Wireshark (or <code>tshark</code>, its command line cousin) and <code>tcpflow</code> which can piece together entire TCP conversations in an organized fashion.</p>

<h3 id="port-scanners">Port Scanners</h3>

<p>In order to generate the traffic and responses for our packet analyzer to capture, we will use a port scanner.  Port scanners can be used to craft and send various types of packets to remote hosts in order to discover type of traffic the server accepts.  Malicious users often use this as a discovery tool to try to find vulnerable services to exploit (part of the reason to use a firewall in the first place), so we will use this to try to see what an attacker could discover.</p>

<p>For this guide, we will use the <code>nmap</code> network mapping and port scanning tool.  We can use <code>nmap</code> to send packets of different types to try to figure out which services are on our target machine and what firewall rules protect it.</p>

<h2 id="setting-up-the-auditing-machine">Setting Up the Auditing Machine</h2>

<p>Before we get started, we should make sure we have the tools discussed above.  We can get <code>tcpdump</code> from Ubuntu's repositories.  We can also get <code>nmap</code> with this method, but the repository version is likely out of date.  Instead, we will install some packages to assist us in software compilation and then build it ourselves from source.</p>

<p>Update the local package index and install the software if it is not already available.  We will also purge <code>nmap</code> from our system if it is already installed to avoid conflicts:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo apt-get update
</li><li class="line" prefix="$">sudo apt-get purge nmap
</li><li class="line" prefix="$">sudo apt-get install tcpdump build-essential libssl-dev
</li></ul></code></pre>
<p>Now that we have our compilation tools and the SSL development library, we can get the latest version of <code>nmap</code> from the download page on the <a href="https://nmap.org/download.html">official site</a>.  Open the page in your web browser.</p>

<p>Scroll down to the "Source Code Distribution" section.  At the bottom, you will see a link to the source code for the latest version of <code>nmap</code>.  At the time of this writing, it looks like this:</p>

<p><img src="https://assets.digitalocean.com/articles/firewall_testing/nmap_latest.png" alt="Nmap latest version" /></p>

<p>Right-click on the link and copy the link address.</p>

<p>Back on your auditing machine, move into your home directory and use <code>wget</code> to download the link you pasted.  Make sure to update the link below to reflect the most recent version you copied from the site:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">cd ~
</li><li class="line" prefix="$">wget https://nmap.org/dist/<span class="highlight">nmap-6.49BETA4</span>.tar.bz2
</li></ul></code></pre>
<p>Decompress the file you downloaded and move into the resulting directory by typing:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">tar xjvf nmap*
</li><li class="line" prefix="$">cd nmap*
</li></ul></code></pre>
<p>Configure and compile the source code by typing:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">./configure
</li><li class="line" prefix="$">make
</li></ul></code></pre>
<p>Once the compilation is complete, you can install the resulting executables and supporting files on your system by typing:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo make install
</li></ul></code></pre>
<p>Confirm your installation by typing:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">nmap -V
</li></ul></code></pre>
<p>The output should match the version you downloaded from the <code>nmap</code> website:</p>
<pre class="code-pre "><code langs=""><div class="secondary-code-label " title="Output">Output</div>Nmap version 6.49BETA4 ( https://nmap.org )
Platform: x86_64-unknown-linux-gnu
Compiled with: nmap-liblua-5.2.3 openssl-1.0.1f nmap-libpcre-7.6 nmap-libpcap-1.7.3 nmap-libdnet-1.12 ipv6
Compiled without:
Available nsock engines: epoll poll select
</code></pre>
<p>Next, we will create a directory where we can store our scan results:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">mkdir ~/scan_results
</li></ul></code></pre>
<p>To make sure that you get clean results, exit out of any sessions you might have open between your auditing system and the target system.  This includes SSH sessions, any HTTP(S) connections you may have established in a web browser, etc.</p>

<h2 id="scan-your-target-for-open-tcp-ports">Scan your Target for Open TCP Ports</h2>

<p>Now that we have our server and files ready, we will begin by scanning our target host for open TCP ports.</p>

<p>There are actually a few TCP scans that <code>nmap</code> knows how to do.  The best one to usually start off with is a SYN scan, also known as a "half-open scan" because it never actually negotiates a full TCP connection.  This is often used by attackers because it fails to register on some intrusion detection systems because it never completes a full handshake.</p>

<h3 id="setting-up-the-packet-capture">Setting Up the Packet Capture</h3>

<p>Before we scan, we will set up <code>tcpdump</code> to capture the traffic generated by the test.  This will help us analyze the packets sent and received in more depth later on if we need to.  Let's create a directory within <code>~/scan_results</code> so that we can keep the files related to our SYN scan together:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">mkdir ~/scan_results/syn_scan
</li></ul></code></pre>
<p>We can start a <code>tcpdump</code> capture and write the results to a file in our <code>~/scan_results/syn_scan</code> directory with the following command:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo tcpdump host <span class="highlight">target_ip_addr</span> -w ~/scan_results/syn_scan/packets
</li></ul></code></pre>
<p>By default, <code>tcpdump</code> will run in the foreground.  In order to run our <code>nmap</code> scan in the same window, we'll need to pause the <code>tcpdump</code> process and then restart it in the background.</p>

<p>We can pause the running process by hitting <code>CTRL-Z</code>:</p>
<pre class="code-pre "><code langs="">CTRL-Z
</code></pre>
<p>This will pause the running process:</p>
<pre class="code-pre "><code langs=""><div class="secondary-code-label " title="Output">Output</div>^Z
[1]+  Stopped                 sudo tcpdump host <span class="highlight">target_ip_addr</span> -w ~/scan_results/syn_scan/packets
</code></pre>
<p>Now, you can restart the job in the background by typing <code>bg</code>:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">bg
</li></ul></code></pre>
<p>You should see a similar line of output, this time without the "Stopped" label and with an ampersand at the end to indicate that the process will be run in the background:</p>
<pre class="code-pre "><code langs=""><div class="secondary-code-label " title="Output">Output</div>[1]+ sudo tcpdump host <span class="highlight">target_ip_addr</span> -w ~/scan_results/syn_scan/packets &
</code></pre>
<p>The command is now running in the background, watching for any packets going between our audit and target machines.  We can now run our SYN scan.</p>

<h3 id="run-the-syn-scan">Run the SYN Scan</h3>

<p>With <code>tcpdump</code> recording our traffic to the target machine, we are ready to run <code>nmap</code>.  We will use the following flags to get <code>nmap</code> to perform the actions we require:</p>

<ul>
<li><strong><code>-sS</code></strong>: This starts a SYN scan.  This is technically the default scan that <code>nmap</code> will perform if no scan type is given, but we will include it here to be explicit.</li>
<li><strong><code>-Pn</code></strong>: This tells <code>nmap</code> to skip the host discovery step, which would abort the test early if the host doesn't respond to a ping.  Since we know that the target is online, we can skip this.</li>
<li><strong><code>-p-</code></strong>: By default, SYN scans will only try the 1000 most commonly used ports.  This tells <code>nmap</code> to check every available port.</li>
<li><strong><code>-T4</code></strong>: This sets a timing profile for <code>nmap</code>, telling it to speed up the test at the risk of slightly less accurate results.  0 is the slowest and 5 is the fastest.  Since we're scanning every port, we can use this as our baseline and re-check any ports later that might have been reported incorrectly.</li>
<li><strong><code>-vv</code></strong>: This increases the verbosity of the output.</li>
<li><strong><code>--reason</code></strong>: This tells <code>nmap</code> to provide the reason that a port's state was reported a certain way.</li>
<li><strong><code>-oN</code></strong>: This writes the results to a file that we can use for later analysis.</li>
</ul>

<p></p><div class="code-label notes-and-warnings note" title="Note">Note</div><span class="note">
One thing to keep in mind is that in order to check IPv6, you will need to add the <code>-6</code> flag to your commands.  Because most of the prerequisite tutorials do not accept IPv6 traffic, we will be skipping IPv6 for this guide.  Add this flag if your firewall accepts IPv6 traffic.<br /></span>

<p>Together, the command will look something like this:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo nmap -sS -Pn -p- -T4 -vv --reason -oN ~/scan_results/syn_scan/nmap.results <span class="highlight">target_ip_addr</span>
</li></ul></code></pre>
<p>Even with the timing template set at 4, the scan will likely take quite some time as it runs through 65,535 ports (my test run lasted about forty minutes).  You will see results begin to print that look something like this:</p>
<pre class="code-pre "><code langs=""><div class="secondary-code-label " title="Output">Output</div>Starting Nmap 6.49BETA4 ( https://nmap.org ) at 2015-08-26 16:54 EDT
Initiating Parallel DNS resolution of 1 host. at 16:54
Completed Parallel DNS resolution of 1 host. at 16:54, 0.12s elapsed
Initiating SYN Stealth Scan at 16:54
Scanning 198.51.100.15 [65535 ports]
Discovered open port 22/tcp on 198.51.100.15
Discovered open port 80/tcp on 198.51.100.15
SYN Stealth Scan Timing: About 6.16% done; ETC: 17:02 (0:07:52 remaining)
SYN Stealth Scan Timing: About 8.60% done; ETC: 17:06 (0:10:48 remaining)

. . .
</code></pre>
<h3 id="stop-the-tcpdump-packet-capture">Stop the tcpdump Packet Capture</h3>

<p>Once the scan is complete, we can bring our <code>tcpdump</code> process back into the foreground and stop it.</p>

<p>Bring it out of the background by typing:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">fg
</li></ul></code></pre>
<p>Stop the process by holding the control key and hitting "c":</p>
<pre class="code-pre "><code langs="">CTRL-C
</code></pre>
<h3 id="analyzing-the-results">Analyzing the Results</h3>

<p>You should now have two files in your <code>~/scan_results/syn_scan</code> directory.  One called <code>packets</code>, generated by the <code>tcpdump</code> run, and one generated by <code>nmap</code> called <code>nmap.results</code>.</p>

<p>Let's look at the <code>nmap.results</code> file first:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">less ~/scan_results/syn_scan/nmap.results
</li></ul></code></pre><div class="code-label " title="~/scan_results/syn_scan/nmap.results">~/scan_results/syn_scan/nmap.results</div><pre class="code-pre "><code langs=""># Nmap 6.49BETA4 scan initiated Wed Aug 26 17:05:13 2015 as: nmap -sS -Pn -p- -T4 -vv --reason -oN /home/user/scan_results/syn_scan/nmap.results 198.51.100.15
Increasing send delay for 198.51.100.15 from 0 to 5 due to 9226 out of 23064 dropped probes since last increase.
Increasing send delay for 198.51.100.15 from 5 to 10 due to 14 out of 34 dropped probes since last increase.
Nmap scan report for 198.51.100.15
Host is up, received user-set (0.00097s latency).
Scanned at 2015-08-26 17:05:13 EDT for 2337s
<span class="highlight">Not shown: 65533 closed ports</span>
<span class="highlight">Reason: 65533 resets</span>
<span class="highlight">PORT   STATE SERVICE REASON</span>
<span class="highlight">22/tcp open  ssh     syn-ack ttl 63</span>
<span class="highlight">80/tcp open  http    syn-ack ttl 63</span>

Read data files from: /usr/local/bin/../share/nmap
# Nmap done at Wed Aug 26 17:44:10 2015 -- 1 IP address (1 host up) scanned in 2336.85 seconds
</code></pre>
<p>The highlighted area above contains the main results of the scan.  We can see that port 22 and port 80 are open on the scanned host in order to allow SSH and HTTP traffic.  We can also see that 65,533 ports were closed.  Another possible result would be "filtered".  Filtered means that these ports were identified as being stopped by something along the network path.  It could be a firewall on the target, but it could also be filtering rules on any of the intermediate hosts between the audit and target machines.</p>

<p>If we want to see the actual packet traffic that was sent to and received from the target, we can read the <code>packets</code> file back into <code>tcpdump</code>, like this:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo tcpdump -nn -r ~/scan_results/syn_scan/packets | less
</li></ul></code></pre>
<p>This file contains the entire conversation that took place between the two hosts.  You can filter in a number of ways.</p>

<p>For instance, to view only the traffic <em>sent</em> to the target, you can type:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo tcpdump -nn -r ~/scan_results/syn_scan/packets 'dst <span class="highlight">target_ip_addr</span>' | less
</li></ul></code></pre>
<p>Likewise, to view only the response traffic, you can change the "dst" to "src":</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo tcpdump -nn -r ~/scan_results/syn_scan/packets 'src <span class="highlight">target_ip_addr</span>' | less
</li></ul></code></pre>
<p>Open TCP ports would respond to these requests with a SYN packet.  We can search directly for responses for this type with a filter like this:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo tcpdump -nn -r ~/scan_results/syn_scan/packets 'src <span class="highlight">target_ip_addr</span> and tcp[tcpflags] & tcp-syn != 0' | less
</li></ul></code></pre>
<p>This will show you only the successful SYN responses, and should match the ports that you saw in the <code>nmap</code> run:</p>
<pre class="code-pre "><code langs=""><div class="secondary-code-label " title="Output">Output</div>reading from file packets, link-type EN10MB (Ethernet)
17:05:13.557597 IP 198.51.100.15.22 > 198.51.100.2.63872: Flags [S.], seq 2144564104, ack 4206039348, win 29200, options [mss 1460], length 0
17:05:13.558085 IP 198.51.100.15.80 > 198.51.100.2.63872: Flags [S.], seq 3550723926, ack 4206039348, win 29200, options [mss 1460], length 0
</code></pre>
<p>You can do more analysis of the data as you see fit.  It has all been captured for asynchronous processing and analysis.</p>

<h2 id="scan-your-target-for-open-udp-ports">Scan your Target for Open UDP Ports</h2>

<p>Now that you have a good handle on how to run these tests, we can complete a similar process to scan for open UDP ports.</p>

<h3 id="setting-up-the-packet-capture">Setting Up the Packet Capture</h3>

<p>Once again, let's create a directory to hold our results:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">mkdir ~/scan_results/udp_scan
</li></ul></code></pre>
<p>Start a <code>tcpdump</code> capture again.  This time, write the file to the new <code>~/scan_results/udp_scan</code> directory:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo tcpdump host <span class="highlight">target_ip_addr</span> -w ~/scan_results/udp_scan/packets
</li></ul></code></pre>
<p>Pause the process and put it into the background:</p>
<pre class="code-pre "><code langs="">CTRL-Z
</code></pre><pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">bg
</li></ul></code></pre>
<h3 id="run-the-udp-scan">Run the UDP Scan</h3>

<p>Now, we are ready to run the UDP scan.  Due to the nature of the UDP protocol, this scan will typically take <strong>significantly</strong> longer than the SYN scan.  In fact, it could take over a day if you are scanning every port on the system.  UDP is a connectionless protocol, so receiving no response could mean that the target's port is blocked, that it was accepted, or that the packet was lost.  To try to distinguish between these, <code>nmap</code> must retransmit additional packets to try to get a response.</p>

<p>Most of the flags will be the same as we used for the SYN scan.  In fact, the only new flag is:</p>

<ul>
<li><strong><code>-sU</code></strong>: This tells <code>nmap</code> to perform a UDP scan.</li>
</ul>

<h4 id="speeding-up-the-udp-test">Speeding up the UDP Test</h4>

<p>If you are worried about the amount of time this test takes, you may only want to test a subset of your UDP ports at first.  You can test only the 1000 most common ports by leaving out the <code>-p-</code> flag.  This can shorten your scan time considerably.  If you want a complete picture though, you'll have to go back later and scan your entire port range.</p>

<p>Because you are scanning your own infrastructure, perhaps the best option to speed up the UDP scans is to temporarily disable ICMP rate limiting on the target system.  Typically, Linux hosts limit ICMP responses to 1 per second (this is typically a good thing, but not for our auditing), which means that a full UDP scan would take over 18 hours.  You can check this setting on your target machine by typing:</p>
<pre class="code-pre custom_prefix"><code langs=""><ul class="prefixed"><li class="line" prefix="target_machine $">sudo sysctl net.ipv4.icmp_ratelimit
</li></ul></code></pre><pre class="code-pre "><code langs=""><div class="secondary-code-label " title="Output">Output</div>net.ipv4.icmp_ratelimit = 1000
</code></pre>
<p>The "1000" is the number of milliseconds between responses.  We can temporarily disable this rate limiting on the target system by typing:</p>
<pre class="code-pre custom_prefix"><code langs=""><ul class="prefixed"><li class="line" prefix="target_machine $">sudo sysctl -w net.ipv4.icmp_ratelimit=0
</li></ul></code></pre>
<p>It is very important to revert this value after your test.</p>

<h4 id="running-the-test">Running the Test</h4>

<p>Be sure to write the results to the <code>~/scan_results/udp_scan</code> directory.  All together, the command should look like this:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo nmap -sU -Pn -p- -T4 -vv --reason -oN ~/scan_results/udp_scan/nmap.results <span class="highlight">target_ip_addr</span>
</li></ul></code></pre>
<p>Even with disabling ICMP rate limiting on the target, this scan took about 2 hours and 45 minutes during our test run.  After the scan is complete, you should revert your ICMP rate limit (if you modified it) on the target machine:</p>
<pre class="code-pre custom_prefix"><code langs=""><ul class="prefixed"><li class="line" prefix="target_machine $">sudo sysctl -w net.ipv4.icmp_ratelimit=1000
</li></ul></code></pre>
<h3 id="stop-the-tcpdump-packet-capture">Stop the tcpdump Packet Capture</h3>

<p>Bring the <code>tcpdump</code> process back into the foreground on your audit machine by typing:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">fg
</li></ul></code></pre>
<p>Stop the packet capture by holding control and hitting "c":</p>
<pre class="code-pre "><code langs="">CTRL-c
</code></pre>
<h3 id="analyzing-the-results">Analyzing the Results</h3>

<p>Now, we can take a look at the generated files.</p>

<p>The resulting <code>nmap.results</code> file should look fairly similar to the one we saw before:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">less ~/scan_results/udp_scan/nmap.results
</li></ul></code></pre><div class="code-label " title="~/scan_results/udp_scan/nmap.results">~/scan_results/udp_scan/nmap.results</div><pre class="code-pre "><code langs=""># Nmap 6.49BETA4 scan initiated Thu Aug 27 12:42:42 2015 as: nmap -sU -Pn -p- -T4 -vv --reason -oN /home/user/scan_results/udp_scan/nmap.results 198.51.100.15
Increasing send delay for 198.51.100.15 from 0 to 50 due to 10445 out of 26111 dropped probes since last increase.
Increasing send delay for 198.51.100.15 from 50 to 100 due to 11 out of 23 dropped probes since last increase.
Increasing send delay for 198.51.100.15 from 100 to 200 due to 3427 out of 8567 dropped probes since last increase.
Nmap scan report for 198.51.100.15
Host is up, received user-set (0.0010s latency).
Scanned at 2015-08-27 12:42:42 EDT for 9956s
Not shown: 65532 closed ports
Reason: 65532 port-unreaches
PORT    STATE         SERVICE REASON
22/udp  open|filtered ssh     no-response
80/udp  open|filtered http    no-response
443/udp open|filtered https   no-response

Read data files from: /usr/local/bin/../share/nmap
# Nmap done at Thu Aug 27 15:28:39 2015 -- 1 IP address (1 host up) scanned in 9956.97 seconds
</code></pre>
<p>A key difference between this result and the SYN result earlier will likely be the amount of ports marked <code>open|filtered</code>.  This means that <code>nmap</code> couldn't determine whether the lack of a response meant that a service accepted the traffic or whether it was dropped by some firewall or filtering mechanism along the delivery path.</p>

<p>Analyzing the <code>tcpdump</code> output is also significantly more difficult because there are no connection flags and because we must match up ICMP responses to UDP requests.</p>

<p>We can see how <code>nmap</code> had to send out many packets to the ports that were reported as <code>open|filtered</code> by asking to see the UDP traffic to one of the reported ports:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo tcpdump -nn -P out -r ~/scan_results/udp_scan/packets 'udp and port 22'
</li></ul></code></pre>
<p>You will likely see something that looks like this:</p>
<pre class="code-pre "><code langs=""><div class="secondary-code-label " title="Output">Output</div>reading from file /home/user/scan_results/udp_scan/packets, link-type EN10MB (Ethernet)
14:57:40.801956 IP 198.51.100.2.60181 > 198.51.100.15.22: UDP, length 0
14:57:41.002364 IP 198.51.100.2.60182 > 198.51.100.15.22: UDP, length 0
14:57:41.202702 IP 198.51.100.2.60183 > 198.51.100.15.22: UDP, length 0
14:57:41.403099 IP 198.51.100.2.60184 > 198.51.100.15.22: UDP, length 0
14:57:41.603431 IP 198.51.100.2.60185 > 198.51.100.15.22: UDP, length 0
14:57:41.803885 IP 198.51.100.2.60186 > 198.51.100.15.22: UDP, length 0
</code></pre>
<p>Compare this to the results we see from one of the scanned ports that was marked as "closed":</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo tcpdump -nn -P out -r ~/scan_results/udp_scan/packets 'udp and port 53'
</li></ul></code></pre><pre class="code-pre "><code langs=""><div class="secondary-code-label " title="Output">Output</div>reading from file /home/user/scan_results/udp_scan/packets, link-type EN10MB (Ethernet)
13:37:24.219270 IP 198.51.100.2.60181 > 198.51.100.15.53: 0 stat [0q] (12)
</code></pre>
<p>We can try to manually reconstruct the process that <code>nmap</code> goes through by first compiling a list of all of the ports that we're sending UDP packets to using something like this:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo tcpdump -nn -P out -r ~/scan_results/udp_scan/packets "udp" | awk '{print $5;}' | awk 'BEGIN { FS = "." } ; { print $5 +0}' | sort -u | tee outgoing
</li></ul></code></pre>
<p>Then, we can see which ICMP packets we received back saying the port was unreachable:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo tcpdump -nn -P in -r ~/scan_results/udp_scan/packets "icmp" |  awk '{print $10,$11}' | grep unreachable | awk '{print $1}' | sort -u | tee response
</li></ul></code></pre>
<p>We can see then take these two responses and see which UDP packets never received an ICMP response back by typing:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">comm -3 outgoing response
</li></ul></code></pre>
<p>This should mostly match the list of ports that <code>nmap</code> reported (it may contain some false positives from lost return packets).</p>

<h2 id="host-and-service-discovery">Host and Service Discovery</h2>

<p>We can run some additional tests on our target to see if it is possible for <code>nmap</code> to identify the operating system running or any of the service versions.</p>

<p>Let's make a directory to hold our versioning results:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">mkdir ~/scan_results/versions
</li></ul></code></pre>
<h3 id="discovering-the-versions-of-services-on-the-server">Discovering the Versions of Services on the Server</h3>

<p>We can attempt to guess the versions of services running on the target through a process known as fingerprinting.  We retrieve information from the server and compare it to known versions in our database.</p>

<p>A <code>tcpdump</code> wouldn't be too useful in this scenario, so we can skip it.  If you want to capture it anyways, follow the process we used last time.</p>

<p>The <code>nmap</code> scan we need to use is triggered by the <code>-sV</code> flag.  Since we already did SYN and UDP scans, we can pass in the exact ports we want to look at with the <code>-p</code> flag.  Here, we'll look at 22 and 80 (the ports that were shown in our SYN scan):</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo nmap -sV -Pn -p 22,80 -vv --reason -oN ~/scan_results/versions/service_versions.nmap <span class="highlight">target_ip_addr</span>
</li></ul></code></pre>
<p>If you view the file that results, you may get information about the service running, depending on how "chatty" or even how unique the service's response is:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">less ~/scan_results/versions/service_versions.nmap
</li></ul></code></pre><div class="code-label " title="~/scan_results/versions/service_versions.nmap">~/scan_results/versions/service_versions.nmap</div><pre class="code-pre "><code langs=""># Nmap 6.49BETA4 scan initiated Thu Aug 27 15:46:12 2015 as: nmap -sV -Pn -p 22,80 -vv --reason -oN /home/user/scan_results/versions/service_versions.nmap 198.51.100.15
Nmap scan report for 198.51.100.15
Host is up, received user-set (0.0011s latency).
Scanned at 2015-08-27 15:46:13 EDT for 8s
PORT   STATE SERVICE REASON         VERSION
22/tcp open  ssh     syn-ack ttl 63 <span class="highlight">OpenSSH 6.6.1p1 Ubuntu 2ubuntu2 (Ubuntu Linux; protocol 2.0)</span>
80/tcp open  http    syn-ack ttl 63 <span class="highlight">nginx 1.4.6 (Ubuntu)</span>
Service Info: OS: Linux; CPE: cpe:/o:linux:linux_kernel

Read data files from: /usr/local/bin/../share/nmap
Service detection performed. Please report any incorrect results at https://nmap.org/submit/ .
# Nmap done at Thu Aug 27 15:46:21 2015 -- 1 IP address (1 host up) scanned in 8.81 seconds
</code></pre>
<p>Here, you can see that the test was able to identify the SSH server version and the Linux distribution that packaged it as well as the SSH protocol version accepted.  It also recognized the version of Nginx and again identified it as matching an Ubuntu package.</p>

<h3 id="discovering-the-host-operating-system">Discovering the Host Operating System</h3>

<p>We can try to have <code>nmap</code> guess the host operating system based on characteristics of its software and responses as well.  This works much in the same way as service versioning.  Once again, we will omit the <code>tcpdump</code> run from this test, but you can perform it if you'd like.</p>

<p>The flag we need in order to perform operating system detection is <code>-O</code> (the capitalized letter "O").  A full command may look something like this:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo nmap -O -Pn -vv --reason -oN ~/scan_results/versions/os_version.nmap <span class="highlight">target_ip_addr</span>
</li></ul></code></pre>
<p>If you view the output file, you might see something that looks like this:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">less ~/scan_results/versions/os_version.nmap
</li></ul></code></pre><div class="code-label " title="~/scan_results/versions/os_versions.nmap">~/scan_results/versions/os_versions.nmap</div><pre class="code-pre "><code langs=""># Nmap 6.49BETA4 scan initiated Thu Aug 27 15:53:54 2015 as: nmap -O -Pn -vv --reason -oN /home/user/scan_results/versions/os_version.nmap 198.51.100.15
Increasing send delay for 198.51.100.15 from 0 to 5 due to 65 out of 215 dropped probes since last increase.
Increasing send delay for 198.51.100.15 from 5 to 10 due to 11 out of 36 dropped probes since last increase.
Increasing send delay for 198.51.100.15 from 10 to 20 due to 11 out of 35 dropped probes since last increase.
Increasing send delay for 198.51.100.15 from 20 to 40 due to 11 out of 29 dropped probes since last increase.
Increasing send delay for 198.51.100.15 from 40 to 80 due to 11 out of 31 dropped probes since last increase.
Nmap scan report for 198.51.100.15
Host is up, received user-set (0.0012s latency).
Scanned at 2015-08-27 15:53:54 EDT for 30s
Not shown: 998 closed ports
Reason: 998 resets
PORT   STATE SERVICE REASON
22/tcp open  ssh     syn-ack ttl 63
80/tcp open  http    syn-ack ttl 63
No exact OS matches for host (If you know what OS is running on it, see https://nmap.org/submit/ ).
TCP/IP fingerprint:
OS:SCAN(V=6.49BETA4%E=4%D=8/27%OT=22%CT=1%CU=40800%PV=N%DS=2%DC=I%G=Y%TM=55
OS:DF6AF0%P=x86_64-unknown-linux-gnu)SEQ(SP=F5%GCD=1%ISR=106%TI=Z%CI=Z%TS=8
OS:)OPS(O1=M5B4ST11NW8%O2=M5B4ST11NW8%O3=M5B4NNT11NW8%O4=M5B4ST11NW8%O5=M5B
OS:4ST11NW8%O6=M5B4ST11)WIN(W1=7120%W2=7120%W3=7120%W4=7120%W5=7120%W6=7120
OS:)ECN(R=Y%DF=Y%T=40%W=7210%O=M5B4NNSNW8%CC=Y%Q=)T1(R=Y%DF=Y%T=40%S=O%A=S+
OS:%F=AS%RD=0%Q=)T2(R=N)T3(R=N)T4(R=Y%DF=Y%T=40%W=0%S=A%A=Z%F=R%O=%RD=0%Q=)
OS:T5(R=Y%DF=Y%T=40%W=0%S=Z%A=S+%F=AR%O=%RD=0%Q=)T6(R=Y%DF=Y%T=40%W=0%S=A%A
OS:=Z%F=R%O=%RD=0%Q=)T7(R=N)U1(R=Y%DF=N%T=40%IPL=164%UN=0%RIPL=G%RID=G%RIPC
OS:K=G%RUCK=G%RUD=G)U1(R=N)IE(R=N)

Uptime guess: 1.057 days (since Wed Aug 26 14:32:23 2015)
Network Distance: 2 hops
TCP Sequence Prediction: Difficulty=245 (Good luck!)
IP ID Sequence Generation: All zeros

Read data files from: /usr/local/bin/../share/nmap
OS detection performed. Please report any incorrect results at https://nmap.org/submit/ .
# Nmap done at Thu Aug 27 15:54:24 2015 -- 1 IP address (1 host up) scanned in 30.94 seconds
</code></pre>
<p>We can see that in this case, <code>nmap</code> has no guesses for the operating system based on the signature it saw.  If it had received more information, it would likely show various percentages which indicate how the target machine's signature matches the operating system signatures in its databases.  You can see the fingerprint signature that <code>nmap</code> received from the target below the <code>TCP/IP fingerprint:</code> line.</p>

<p>Operating system identification can help an attacker determine which exploits may be useful on the system.  Configuring your firewall to respond to fewer inquiries can help to hinder the accuracy of some of these detection methods.</p>

<h2 id="conclusion">Conclusion</h2>

<p>Testing your firewall and building an awareness of what your internal network looks like to an outside attacker can help minimize your risk.  The information you find from probing your own infrastructure may open up a conversation about whether any of your policy decisions need to be revisited in order to increase security.  It may also illuminate any gaps in your security that may have occurred due to incorrect rule ordering or forgotten test policies.  It is recommended that you test your policies with the latest scanning databases regularity in order improve, or at least maintain, your current level of security.</p>

<p>To get an idea of some policy improvements for your firewall, check out <a href="https://indiareads/community/tutorials/how-to-choose-an-effective-firewall-policy-to-secure-your-servers">this guide</a>.</p>

    