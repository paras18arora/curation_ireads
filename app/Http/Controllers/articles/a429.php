<link rel='stylesheet' href='digital_ocean.css'><br> <br> 
      <h2 id="introduction">Introduction</h2>

<p>sysdig is a brand new system-level exploration and troubleshooting tool that combines the benefits of well-known utilities such as strace, tcpdump, and lsof into one single application. And, as if this were not enough, sysdig also provides the capabilities to save system activity to trace files for later analysis. </p>

<p>In addition, a rich library of scripts (called <em>chisels</em>) is provided along with the installation in order to help you solve common problems or meet monitoring needs, from displaying failed disk I/O operations to finding the files where a given process has spent most time, and everything in between. You can also write your own scripts to enhance sysdig even further according to your needs.</p>

<p>In this article we will first introduce basic sysdig usage, and then explore network analysis with sysdig, including an example of auditing network traffic on a CentOS 7 LAMP server. Please note that the VPS used in the examples has not been placed under significant load, but it is enough for showing the basics of the present auditing tasks.</p>

<h3 id="prerequisites">Prerequisites</h3>

<p>Before you begin, please make sure you have these prerequisites.</p>

<ul>
<li>CentOS 7 Droplet</li>
<li>Set up a LAMP server on your CentOS 7 VPS. Please refer to <a href="https://indiareads/community/tutorials/how-to-install-linux-apache-mysql-php-lamp-stack-on-centos-7">this article</a> for instructions</li>
<li>In addition, you should have a non-root user account with <a href="https://indiareads/community/tutorials/how-to-add-and-delete-users-on-a-centos-7-server">sudo access</a> that will be used to run sysdig</li>
</ul>

<h3 id="installing-sysdig">Installing sysdig</h3>

<p>Log in to your server and follow these steps:  </p>

<h2 id="step-1-—-trust-the-draios-gpg-key">Step 1 — Trust the Draios GPG key</h2>

<p>Draios is the firm behind sysdig.</p>

<p>Before proceeding with the installation itself, yum will use this key to verify the authenticity of the package you’re about to download.  </p>

<p>To manually add the Draios key to your RPM keyring, use the <code>rpm</code> tool with the <code>--import</code> flag:</p>
<pre class="code-pre "><code langs="">sudo rpm --import https://s3.amazonaws.com/download.draios.com/DRAIOS-GPG-KEY.public  
</code></pre>
<p>Then, download the Draios repository and configure yum to use it:  </p>
<pre class="code-pre "><code langs="">sudo curl -s -o /etc/yum.repos.d/draios.repo http://download.draios.com/stable/rpm/draios.repo
</code></pre>
<h2 id="step-2-—-enable-the-epel-repository">Step 2 — Enable the EPEL Repository</h2>

<p>Extra Packages for Enterprise Linux (EPEL) is a repository of high-quality free and open-source software maintained by the Fedora project and is 100% compatible with its spinoffs, such as Red Hat Enterprise Linux and CentOS. This repository is needed in order to download the Dynamic Kernel Module Support (DKMS) package, which is needed by sysdig, and to download other dependencies.</p>
<pre class="code-pre "><code langs="">sudo yum -y install epel-release
</code></pre>
<h2 id="step-3-—-install-kernel-headers">Step 3 — Install Kernel Headers</h2>

<p>This is needed because sysdig will need to build a customized kernel module (named <code>sysdig-probe</code>) and use it to operate.</p>
<pre class="code-pre "><code langs="">sudo yum -y install kernel-devel-$(uname -r)
</code></pre>
<h2 id="step-4-—-install-the-sysdig-package">Step 4 — Install the sysdig package</h2>

<p>Now we can install sysdig.</p>
<pre class="code-pre "><code langs="">sudo yum -y install sysdig
</code></pre>
<h2 id="step-5-—-run-sysdig-as-a-non-root-user">Step 5 — Run sysdig as a Non-root User</h2>

<p>For security, it's best to have a non-root user to run sysdig. Create a custom group for sysdig:</p>
<pre class="code-pre "><code langs="">sudo groupadd sysdig
</code></pre>
<p>Add one or more users to the group. In our example, we'll add the user <strong>sammy</strong>.</p>
<pre class="code-pre "><code langs="">sudo usermod -aG sysdig <span class="highlight">sammy</span>
</code></pre>
<p>Locate the binary file for sysdig: </p>
<pre class="code-pre "><code langs="">which sysdig  
</code></pre>
<p>You might receive a response like</p>
<pre class="code-pre "><code langs=""><span class="highlight">/usr/bin/sysdig</span>
</code></pre>
<p>Give all members of the <strong>sysdig</strong> group privileges to run the <code>sysdig</code> executable (and that binary only). Edit <code>/etc/sudoers</code> with:</p>
<pre class="code-pre "><code langs="">sudo visudo
</code></pre>
<p>Add the following lines for the <strong>sysdig</strong> group in the groups section. Adding the new lines after the <code>%wheel</code> section is fine. Replace the path with sysdig's location on your system:</p>
<pre class="code-pre "><code langs="">## Same thing without a password
# %wheel        ALL=(ALL)       NOPASSWD: ALL

## sysdig
%sysdig ALL= <span class="highlight">/usr/bin/sysdig</span> 
</code></pre>
<p>If you need further clarifications on editing the <code>/etc/sudoers</code> file, it is recommended that you take a look at <a href="https://indiareads/community/tutorials/how-to-add-and-delete-users-on-a-centos-7-server">this article</a>.</p>

<h2 id="running-sysdig">Running sysdig</h2>

<p>You can run sysdig in two modes. </p>

<p>You can view the the real-time stream of server activity live, or you can save records of system operations to a file for later offline analysis.</p>

<p>Since you will most likely want to use the second option, that is what we will cover here. Note that when saving system activity to a file, sysdig takes a full snapshot of the operating system, so that everything that happens on your server during that interval of time will be available for offline analysis.  </p>

<blockquote>
<p><strong>Note:</strong> When you run sysdig commands, please make sure that each option is preceded by a single short dash. Copying and pasting may cause an issue where a single dash is pasted as a long dash and therefore not recognized by the program.</p>
</blockquote>

<p>Let's run a basic sysdig command to capture 1000 lines of server activity.</p>

<p>To capture system activity to a file named <code>act1.scap</code>, and limit the output to 1000 events, run the following command (omit the <code>-n 1000</code> part if you want to run sysdig for an unspecified period of time). The <code>-z</code> switch is used to enable compression of the trace file.  </p>
<pre class="code-pre "><code langs="">sudo sysdig -w act1.scap.gz -n 1000 -z
</code></pre>
<blockquote>
<p><strong>Note:</strong> If you omitted the <span class="highlight">-n</span> switch in the last step, you can interrupt the execution of sysdig by pressing the CTRL + C key combination.  </p>
</blockquote>

<h2 id="chisels-—-an-overview-of-sysdig-scripts">Chisels — An Overview of sysdig Scripts</h2>

<p><em>Chisels</em> are sysdig scripts. To display a list of the available chisels, we need to run the following command:</p>
<pre class="code-pre "><code langs="">sudo sysdig -cl
</code></pre>
<p>In order to audit the network traffic on our CentOS 7 LAMP server using the trace file created by sysdig, we will use the chisels available under the Net category:</p>
<pre class="code-pre "><code langs="">Category: Net
-------------
iobytes_net         Show total network I/O bytes
spy_ip              Show the data exchanged with the given IP address
spy_port            Show the data exchanged using the given IP port number
topconns            top network connections by total bytes
topports_server     Top TCP/UDP server ports by R+W bytes
topprocs_net        Top processes by network I/O
</code></pre>
<p>Further description of a specific chisel, along with instructions for its use, can be viewed with: </p>
<pre class="code-pre "><code langs="">sudo sysdig -i <span class="highlight">chisel name</span>
</code></pre>
<p>For example: </p>
<pre class="code-pre "><code langs="">sudo sysdig -i spy_ip
</code></pre>
<p>This outputs:</p>
<pre class="code-pre "><code langs="">Category: Net
-------------
spy_ip              Show the data exchanged with the given IP address
shows the network payloads exchanged with an IP endpoint. You can combine this chisel with the -x, -X or -A sysdig command line switches to customize the screen output
Args:
[ipv4] host_ip - the remote host IP address
[string] disable_color - Set to 'disable_colors' if you want to disable color output
</code></pre>
<p>The <code>Args</code> section indicates whether you need to pass an argument to the chisel or not. In the case of <code>spy_ip</code>, you need to pass an IP address as an argument to the chisel.  </p>

<h2 id="auditing-network-traffic-practical-example">Auditing Network Traffic (Practical Example)</h2>

<p>Let's walk through a practical example of how to use sysdig to analyze bandwidth use and see detailed information about network traffic.</p>

<p>To get the best results from this test, you will need to set up a dummy web form on your server so appropriate traffic is generated. If this is a server with a fresh LAMP installation, you can make this form at <code>/var/www/html/index.php</code>.</p>
<pre class="code-pre "><code langs=""><body>
<form id="loginForm" name="loginForm" method="post" action="login.php">
  <table width="300" border="0" align="center" cellpadding="2" cellspacing="0">
    <tr>
      <td width="112"><b>Username:</b></td>
      <td width="188"><input name="login" type="text" class="textfield" id="login" /></td>
    </tr>
    <tr>
      <td><b>Password:</b></td>
      <td><input name="pass" type="password" class="textfield" id="pass" /></td>
    </tr>
    <tr>
      <td>&nbsp;</td>
      <td><br />
      <input type="submit" name="Submit" value="Login" /></td></tr>
  </table>
</form>
</body>
</code></pre>
<p>This isn't required, but to make everything tidy, you can also create the <code>/var/www/html/login.php</code> page:</p>
<pre class="code-pre "><code langs=""><body>
    <p>Form submitted.</p>
</body>
</code></pre>
<blockquote>
<p><strong>Warning:</strong> Please delete this form when you are done testing!</p>
</blockquote>

<h2 id="step-1-—-saving-live-data-for-offline-analysis">Step 1 — Saving Live Data for Offline Analysis</h2>

<p>We will starting capturing our log collection of data by issuing the following command:</p>
<pre class="code-pre "><code langs="">sudo sysdig -w act1.scap.gz -z -s 4096
</code></pre>
<p>Leave sysdig running for a reasonable amount of time. Your command prompt will hang while sysdig runs.</p>

<p>Now, visit your server's domain or IP address in your web browser. You can visit both existing and non-existing pages to generate some traffic. If you want this specific example to work, you should visit the home page, fill out the login information with anything you like, and <strong>submit the login form a few times</strong>. In addition, feel free to run queries to your MySQL/MariaDB database as well.</p>

<p>Once you've generated some traffic, press CTRL + C to stop sysdig. Then you will be ready to run the analysis queries that we will discuss later in this tutorial.</p>

<p>In a production environment, you could start the sysdig data collection during a busy time on your server.</p>

<h3 id="understanding-filters-classes-and-fields">Understanding Filters: Classes and Fields</h3>

<p>Before we get into sorting the sysdig data, let's explain some basic sysdig command elements.</p>

<p>Sysdig provides classes and fields as filters. You can think of classes as objects and fields as properties, following an analogy based on object-oriented programming theory.  </p>

<p>You can display the complete list of classes and fields with: </p>
<pre class="code-pre "><code langs="">sudo sysdig -l
</code></pre>
<p>We will use classes and fields to filter output when analyzing a trace file.  </p>

<h2 id="step-2-—-performing-offline-analysis-using-trace-files">Step 2 — Performing Offline Analysis Using Trace Files</h2>

<p>Since we want to audit the network traffic to and from our LAMP server, we will load the trace file <code>act1.scap.gz</code> and perform the following tests with sysdig:  </p>

<h3 id="displaying-the-list-of-top-processes-using-network-bandwidth">Displaying the list of top processes using network bandwidth</h3>
<pre class="code-pre "><code langs="">sudo sysdig -r act1.scap.gz -c topprocs_net
</code></pre>
<p>You should see output somewhat like this:</p>
<pre class="code-pre "><code langs="">Bytes     Process
------------------------------
331.68KB  httpd
24.14KB   sshd
4.48KB    mysqld
</code></pre>
<p>Here you can see that Apache is using the most bandwidth (the <code>httpd</code> process).</p>

<p>Based on this output, you can make an informed and supported judgment call to decide whether you need to increase your available bandwidth in order to serve your current and future estimated requests. Otherwise, you may want to place appropriate restrictions on the maximum rate of already available bandwidth that can be used by a process.</p>

<h3 id="displaying-network-usage-by-process">Displaying network usage by process</h3>

<p>We may also want to know which IPs are using the network bandwidth consumed by <code>httpd</code>, as shown in the previous example.</p>

<p>To that purpose, we will use the <code>topconns</code> chisel (which shows the top network connections by total bytes) and add a filter formed with the class <code>proc</code> and field <code>name</code> to filter results to show only <code>http</code> connections. In other words, the following command:</p>
<pre class="code-pre "><code langs="">sudo sysdig -r act1.scap.gz -c topconns proc.name=httpd
</code></pre>
<p>This will return the top network connections to your server, including the source, where the process serving the request is <code>httpd</code>.</p>
<pre class="code-pre "><code langs="">Bytes     Proto     Conn     
------------------------------
56.24KB   tcp       111.111.111.111:12574->your_server_ip:80
51.94KB   tcp       111.111.111.111:15249->your_server_ip:80
51.57KB   tcp       111.111.111.111:27832->your_server_ip:80
51.26KB   tcp       111.111.222.222:42487->your_server_ip:80
48.20KB   tcp       111.111.222.222:42483->your_server_ip:80
48.20KB   tcp       111.111.222.222:42493->your_server_ip:80
4.17KB    tcp       111.111.111.111:13879->your_server_ip:80
3.14KB    tcp       111.111.111.111:27873->your_server_ip:80
3.06KB    tcp       111.111.222.222:42484->your_server_ip:80
3.06KB    tcp       111.111.222.222:42494->your_server_ip:80
</code></pre>
<p>Note that the original source and destination IP addresses have been obscured for privacy reasons.</p>

<p>This type of query can help you find top bandwidth users that are sending traffic to your server.</p>

<p>After looking at the output above you may be thinking that the numbers after the source IP addresses represent ports. However, that is not the case. Those numbers indicate the event numbers as recorded by sysdig.  </p>

<h2 id="step-3-—-analyzing-data-exchanged-between-a-specific-ip-and-apache">Step 3 — Analyzing Data Exchanged Between a Specific IP and Apache</h2>

<p>Now we'll examine the connections between a specific IP address and Apache in more detail.</p>

<p>The <code>echo_fds</code> chisel allows us to display the data that was read and written by processes. When combined with a specific process name and a client IP (such as <code>proc.name=httpd and fd.cip=111.111.111.111</code> in this case), this chisel will show the data that was exchanged between our LAMP server and that client IP address.  </p>

<p>In addition, using the following switches helps us to show results in a more friendly and accurate way:</p>

<ul>
<li><p><code>-s 4096</code>: For each event, read up to 4096 bytes from its buffer (this flag can also be used to specify how many bytes of each data buffer should be saved to disk when saving live data to a trace file for offline analysis) </p></li>
<li><p><code>-A</code>:  Print only the text portion of data buffers, and echo end-of-lines (we want to only display human-readable data)</p></li>
</ul>

<p>Here's the command. Be sure to replace <code><span class="highlight">111.111.111.111</span></code> with a client IP address from the previous output.</p>
<pre class="code-pre "><code langs="">sudo sysdig -r act1.scap.gz -s 4096 -A -c echo_fds fd.cip=<span class="highlight">111.111.111.111</span> and proc.name=httpd
</code></pre>
<p>You should see quite a bit of output, depending on the number of connections made by that IP address. Here's an example showing a 404 error:</p>
<pre class="code-pre "><code langs="">GET /hi HTTP/1.1
Host: your_server_ip
Connection: keep-alive
Cache-Control: m

------ Write 426B to <span class="highlight">111.111.111.111</span>:39003->your_server_ip:80

HTTP/1.1 404 Not Found
Date: Tue, 02 Dec 2014 19:38:16 GMT
Server: Apache/2.4.6 (CentOS) PHP/5.4.16
Content-Length: 200
Keep-Alive: timeout=5, max=99
Connection: Keep-Alive
Content-Type: text/html; charset=iso-8859-1

<!DOCTYPE HTML PUBLIC \"-//IETF//DTD HTML 2.0//EN\">
<html><head>
<title>404 Not Found</title>
</head><body>
<h1>Not Found</h1>
<p>The requested URL /hi was not found on this server.</p>
</body></html>
</code></pre>
<p>This type of query can help you figure out exactly what kinds of connections were made by a top bandwidth-using IP address. For example, if you found that the IP address was reaching a certain page very frequently, you could make that page's assets as small as possible, to reduce bandwidth use. Or, if the traffic doesn't seem to be legitimate, you could create a new <a href="https://indiareads/community/tutorials/how-the-iptables-firewall-works">firewall rule</a> to block bandwidth-hogging IPs.</p>

<h2 id="step-4-—-examining-data-exchanged-with-an-ip-address-by-keyword">Step 4 — Examining Data Exchanged with an IP Address by Keyword</h2>

<p>Depending on the server activity during the capture interval, the trace file may contain quite a lot of events and information. Thus, going through the results of the command in the previous section by hand could take an impractical amount of time. For that reason, we can look for specific words in event buffers.</p>

<p>Suppose we have a set of web applications running on our web server, and we want to make sure that login credentials are not being passed as plain text through forms.  </p>

<p>Let’s add a few flags to the command used in the previous example:</p>
<pre class="code-pre "><code langs="">sudo sysdig -r act1.scap.gz -A -c echo_fds fd.ip=<span class="highlight">111.111.111.111</span> and proc.name=httpd and evt.is_io_read=true and evt.buffer contains <span class="highlight">form</span>
</code></pre>
<p>Here the class <code>evt</code>, along with field <code>is_io_read</code>, allow us to examine only read events (from the server’s point of view). In addition, <code>evt.buffer</code> allows us to search for a specific word inside the event buffer (the word is <code><span class="highlight">form</span></code> in this case). You can change the search keyword to one that make sense for your own applications.  </p>

<p>The following output shows that a username and password are being passed from the client to the server in plain text (thus becoming readable to anyone with enough expertise):</p>
<pre class="code-pre "><code langs="">------ Read 551B from <span class="highlight">111.111.111.111</span>:41135->your_server_ip:80

POST /login.php HTTP/1.1
Host: your_server_ip
Connection: keep-alive
Content-Length: 35
Cache-Control: max-age=0
Accept: text/html,application/xhtml+xml,application/xml;q=0.9,image/webp,*/*;q=0.8
Origin: http://104.236.40.111
User-Agent: Mozilla/5.0 (Macintosh; Intel Mac OS X 10_10_1) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/38.0.2125.122 Safari/537.36
Content-Type: application/x-www-form-urlencoded
Referer: http://104.236.40.111/
Accept-Encoding: gzip,deflate
Accept-Language: en-US,en;q=0.8

login=<span class="highlight">sammy</span>&pass=<span class="highlight">password</span>&Submit=Login
</code></pre>
<p>Should you find a similar security hole, notify your developer team immediately.   </p>

<h3 id="conclusion">Conclusion</h3>

<p>What you can accomplish with sysdig in auditing network traffic on a LAMP server is mostly limited by one’s imagination and application requests. We've seen how to find top bandwidth users, examine traffic from specific IPs, and sort connections by keywords based on requests from your applications.</p>

<p>Should you have any further questions about the present article, or would like suggestions on how to work with sysdig in your current LAMP environment, feel free to submit your comment using the form below.</p>

    