<link rel='stylesheet' href='digital_ocean.css'><br><img src=https://community-cdn-digitalocean-com.global.ssl.fastly.net/assets/tutorials/images/large/Elasticsearch-twitter.png?1463175457/> <br> 
      <h3 id="an-article-from-elastic">An Article from <a href="https://www.elastic.co/">Elastic</a></h3>

<h2 id="introduction">Introduction</h2>

<p>Making sense of the millions of log lines your organization generates can be a daunting challenge.  On one hand, these log lines provide a view into application performance, server performance metrics, and security.  On the other hand, log management and analysis can be very time consuming, which may hinder adoption of these increasingly necessary services.</p>

<p>Open-source software, such as <a href="http://rsyslog.com">rsyslog</a>, <a href="https://www.elastic.co/products/elasticsearch">Elasticsearch</a>, and <a href="https://www.elastic.co/products/logstash">Logstash</a> provide the tools to transmit, transform, and store your log data.</p>

<p>In this tutorial, you will learn how to create a centralized rsyslog server to store log files from multiple systems and then use Logstash to send them to an Elasticsearch server. From there, you can decide how best to analyze the data.</p>

<h2 id="goals">Goals</h2>

<p>This tutorial teaches you how to centralize logs generated or received by syslog, specifically the variant known as <a href="http://rsyslog.com">rsyslog</a>. Syslog, and syslog-based tools like rsyslog, collect important information from the kernel and many of the programs that run to keep UNIX-like servers running.  As syslog is a standard, and not just a program, many software projects support sending data to syslog.  By centralizing this data, you can more easily audit security, monitor application behavior, and keep track of other vital server information.</p>

<p>From a centralized, or aggregating rsyslog server, you can then forward the data to Logstash, which can further parse and enrich your log data before sending it on to Elasticsearch.</p>

<p>The final objectives of this tutorial are to:</p>

<ol>
<li>Set up a single, client (or forwarding) rsyslog server</li>
<li>Set up a single, server (or collecting) rsyslog server, to receive logs from the rsyslog client</li>
<li>Set up a Logstash instance to receive the messages from the rsyslog collecting server</li>
<li>Set up an Elasticsearch server to receive the data from Logstash</li>
</ol>

<h2 id="prerequisites">Prerequisites</h2>

<p>In the <strong>same IndiaReads data center</strong>, create the following Droplets with <strong>private networking enabled</strong>:</p>

<ul>
<li>Ubuntu 14.04 Droplet named <strong>rsyslog-client</strong></li>
<li>Ubuntu 14.04 Droplet (<strong>1 GB</strong> or greater) named <strong>rsyslog-server</strong> where centralized logs will be stored and Logstash will be installed</li>
<li>Ubuntu 14.04 Droplet with Elasticsearch installed from <a href="https://indiareads/community/tutorials/how-to-install-and-configure-elasticsearch-on-ubuntu-14-04">How To Install and Configure Elasticsearch on Ubuntu 14.04</a></li>
</ul>

<p>You will also need a non-root user with sudo privileges for each of these servers. <a href="https://indiareads/community/tutorials/initial-server-setup-with-ubuntu-14-04">Initial Server Setup with Ubuntu 14.04</a> explains how to set this up.</p>

<p><span class="note"><strong>Note:</strong> To maximize performance, Logstash will try to allocate 1 gigabyte of memory by default, so ensure the centralized server instance is sized accordingly.<br /></span></p>

<p><span class="warning"><strong>Warning:</strong> IndiaReads's private networking option grants a second networking interface to a VPS, which is only accessible to other VPSs in the same datacenter — which includes the VPSs of other customers in the same datacenter. This is known as shared private networking. To simulate true private networking, you need to use iptables as described in <a href="https://indiareads/community/tutorials/how-to-isolate-servers-within-a-private-network-using-iptables">this tutorial</a>.<br /></span></p>

<p>Refer to <a href="https://indiareads/community/tutorials/how-to-set-up-and-use-digitalocean-private-networking">How To Set Up And Use IndiaReads Private Networking</a> for help on enabling private networking while creating the Droplets. </p>

<p>If you created the Droplets without private networking, refer to <a href="https://indiareads/community/tutorials/how-to-enable-digitalocean-private-networking-on-existing-droplets">How To Enable IndiaReads Private Networking on Existing Droplets</a>.</p>

<h2 id="step-1-—-determining-private-ip-addresses">Step 1 — Determining Private IP Addresses</h2>

<p>In this section, you will determine which private IP addresses are assigned to each Droplet.  This information will be needed through the tutorial.</p>

<p>On each Droplet, find its IP addresses with the <code>ifconfig</code> command:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo ifconfig -a
</li></ul></code></pre>
<p>The <code>-a</code> option is used to show all interfaces. The primary Ethernet interface is usually called <code>eth0</code>.  In this case, however, we want the IP from <code>eth1</code>, the <em>private</em> IP address.  These private IP addresses are not routable over the Internet and are used to communicate in private LANs — in this case, between servers in the same data center over secondary interfaces.</p>

<p>The output will look similar to:</p>
<div class="code-label " title="Output from ifconfig -a">Output from ifconfig -a</div><pre class="code-pre "><code langs="">eth0      Link encap:Ethernet  HWaddr 04:01:06:a7:6f:01  
          inet addr:123.456.78.90  Bcast:123.456.78.255  Mask:255.255.255.0
          inet6 addr: fe80::601:6ff:fea7:6f01/64 Scope:Link
          UP BROADCAST RUNNING MULTICAST  MTU:1500  Metric:1
          RX packets:168 errors:0 dropped:0 overruns:0 frame:0
          TX packets:137 errors:0 dropped:0 overruns:0 carrier:0
          collisions:0 txqueuelen:1000 
          RX bytes:18903 (18.9 KB)  TX bytes:15024 (15.0 KB)

eth1      Link encap:Ethernet  HWaddr 04:01:06:a7:6f:02  
          inet addr:<span class="highlight">10.128.2.25</span>  Bcast:10.128.255.255  Mask:255.255.0.0
          inet6 addr: fe80::601:6ff:fea7:6f02/64 Scope:Link
          UP BROADCAST RUNNING MULTICAST  MTU:1500  Metric:1
          RX packets:6 errors:0 dropped:0 overruns:0 frame:0
          TX packets:5 errors:0 dropped:0 overruns:0 carrier:0
          collisions:0 txqueuelen:1000 
          RX bytes:468 (468.0 B)  TX bytes:398 (398.0 B)

lo        Link encap:Local Loopback  
          inet addr:127.0.0.1  Mask:255.0.0.0
          inet6 addr: ::1/128 Scope:Host
          UP LOOPBACK RUNNING  MTU:16436  Metric:1
          RX packets:0 errors:0 dropped:0 overruns:0 frame:0
          TX packets:0 errors:0 dropped:0 overruns:0 carrier:0
          collisions:0 txqueuelen:0 
          RX bytes:0 (0.0 B)  TX bytes:0 (0.0 B)
</code></pre>
<p>The section to note here is <code>eth1</code> and within that <code>inet addr</code>. In this case, the private network address is <strong>10.128.2.25</strong>. This address is only accessible from other servers, within the same region, that have private networking enabled.</p>

<p>Be sure to repeat this step for all 3 Droplets. Save these private IP addresses somewhere secure. They will be used throughout this tutorial.</p>

<h2 id="step-2-—-setting-the-bind-address-for-elasticsearch">Step 2 — Setting the Bind Address for Elasticsearch</h2>

<p>As part of the Prerequisites, you setup Elasticsearch on its own Droplet. The <a href="https://indiareads/community/tutorials/how-to-install-and-configure-elasticsearch-on-ubuntu-14-04">How To Install and Configure Elasticsearch on Ubuntu 14.04</a> tutorial shows you how to set the bind address to <code>localhost</code> so that other servers can't access the service. However, we need to change this so Logstash can send it data over its private network address.</p>

<p>We will bind Elasticsearch to its private IP address. <strong>Elasticsearch will only listen to requests to this IP address.</strong></p>

<p>On the Elasticsearch server, edit the configuration file:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo nano /etc/elasticsearch/elasticsearch.yml
</li></ul></code></pre>
<p>Find the line that contains <code>network.bind_host</code>. If it is commented out, uncomment it by removing the <code>#</code> character at the beginning of the line. Change the value to the private IP address for the Elasticsearch server so it looks like this:</p>
<div class="code-label " title="/etc/elasticsearch/elasticsearch.yml">/etc/elasticsearch/elasticsearch.yml</div><pre class="code-pre "><code langs="">network.bind_host: <span class="highlight">private_ip_address</span>
</code></pre>
<p>Finally, restart Elasticsearch to enable the change.</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo service elasticsearch restart
</li></ul></code></pre>
<p><span class="warning"><strong>Warning:</strong> It is very important that you only allow servers you trust to connect to Elasticsearch. Using <a href="https://indiareads/community/tutorials/how-to-isolate-servers-within-a-private-network-using-iptables">iptables</a> is highly recommended. For this tutorial, you only want to trust the private IP address of the <strong>rsyslog-server</strong> Droplet, which has Logstash running on it. <br /></span></p>

<h2 id="step-3-—-configuring-the-centralized-server-to-receive-data">Step 3 — Configuring the Centralized Server to Receive Data</h2>

<p>In this section, we will configure the <strong>rsyslog-server</strong> Droplet to be the <em>centralized</em> server able to receive data from other syslog servers on port 514.</p>

<p>To configure the <strong>rsyslog-server</strong> to receive data from other syslog servers, edit <code>/etc/rsyslog.conf</code> on the <strong>rsyslog-server</strong> Droplet:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo nano /etc/rsyslog.conf
</li></ul></code></pre>
<p>Find these lines already commented out in your <code>rsyslog.conf</code>:</p>
<div class="code-label " title="/etc/rsyslog.conf">/etc/rsyslog.conf</div><pre class="code-pre "><code langs=""># provides UDP syslog reception
<span class="highlight">#$ModLoad imudp</span>
<span class="highlight">#$UDPServerRun 514</span>

# provides TCP syslog reception
<span class="highlight">#$ModLoad imtcp</span>
<span class="highlight">#$InputTCPServerRun 514</span>
</code></pre>
<p>The first lines of each section (<code>$ModLoad imudp</code> and <code>$ModLoad imtcp</code>) load the <code>imudp</code> and <code>imtcp</code> modules, respectively. The <code>imudp</code> stands for <strong>i</strong>nput <strong>m</strong>odule <strong>udp</strong>, and <code>imtcp</code> stands for <strong>i</strong>nput <strong>m</strong>odule <strong>tcp</strong>. These modules listen for incoming data from other syslog servers.</p>

<p>The second lines of each section (<code>$UDPSerververRun 514</code> and <code>$TCPServerRun 514</code>) indicate that rsyslog should start the respective UDP and TCP servers for these protocols listening on port 514 (which is the syslog default port).</p>

<p>To enable these modules and servers, uncomment the lines so the file now contains:</p>
<div class="code-label " title="/etc/rsyslog.conf">/etc/rsyslog.conf</div><pre class="code-pre "><code langs=""># provides UDP syslog reception
<span class="highlight">$ModLoad imudp</span>
<span class="highlight">$UDPServerRun 514</span>

# provides TCP syslog reception
<span class="highlight">$ModLoad imtcp</span>
<span class="highlight">$InputTCPServerRun 514</span>
</code></pre>
<p>Save and close the rsyslog configuration file.</p>

<p>Restart rsyslog by running:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo service rsyslog restart
</li></ul></code></pre>
<p>Your centralized rsyslog server is now configured to listen for messages from remote syslog (including rsyslog) instances.</p>

<p><span class="tip"><strong>Tip:</strong> To validate your rsyslog configuration file, you can run the <code>sudo rsyslogd -N1</code> command.<br /></span></p>

<h2 id="step-4-—-configuring-rsyslog-to-send-data-remotely">Step 4 — Configuring rsyslog to Send Data Remotely</h2>

<p>In this section, we will configure the <strong>rsyslog-client</strong> to send log data to the <strong>ryslog-server</strong> Droplet we configured in the last step.</p>

<p>In a default rsyslog setup on Ubuntu, you'll find two files in <code>/etc/rsyslog.d</code>:</p>

<ul>
<li><code>20-ufw.conf</code></li>
<li><code>50-default.conf</code></li>
</ul>

<p>On the <strong>rsyslog-client</strong>, edit the default configuration file:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo nano /etc/rsyslog.d/50-default.conf
</li></ul></code></pre>
<p>Add the following line at the top of the file before the <code>log by facility</code> section, replacing <code><span class="highlight">private_ip_of_ryslog_server</span></code> with the <strong>private</strong> IP of your <em>centralized</em> server:</p>
<div class="code-label " title="/etc/rsyslog.d/50-default.conf">/etc/rsyslog.d/50-default.conf</div><pre class="code-pre "><code langs="">*.*                         @<span class="highlight">private_ip_of_ryslog_server</span>:514
</code></pre>
<p>Save and exit the file.</p>

<p>The first part of the line (<em>.</em>) means we want to send all messages.  While it is outside the scope of this tutorial, you can configure rsyslog to send only certain messages. The remainder of the line explains how to send the data and where to send the data. In our case, the <code>@</code> symbol before the IP address tells rsyslog to use UDP to send the messages. Change this to <code>@@</code> to use TCP. This is followed by the private IP address of <strong>rsyslog-server</strong> with rsyslog and Logstash installed on it. The number after the colon is the port number to use.</p>

<p>Restart rsyslog to enable the changes:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo service rsyslog restart
</li></ul></code></pre>
<p>Congratulations!  You are now sending your syslog messages to a centralized server!</p>

<p><span class="tip"><strong>Tip:</strong> To validate your rsyslog configuration file, you can run the <code>sudo rsyslogd -N1</code> command.<br /></span></p>

<h2 id="step-5-—-formatting-the-log-data-to-json">Step 5 — Formatting the Log Data to JSON</h2>

<p>Elasticsearch requires that all documents it receives be in JSON format, and rsyslog provides a way to accomplish this by way of a template.</p>

<p>In this step, we will configure our centralized rsyslog server to use a JSON template to format the log data before sending it to Logstash, which will then send it to Elasticsearch on a different server.</p>

<p>Back on the <strong>rsyslog-server</strong> server, create a new configuration file to format the messages into JSON format before sending to Logstash:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo nano /etc/rsyslog.d/01-json-template.conf
</li></ul></code></pre>
<p>Copy the following contents to the file exactly as shown:</p>
<div class="code-label " title="/etc/rsyslog.d/01-json-template.conf">/etc/rsyslog.d/01-json-template.conf</div><pre class="code-pre "><code langs="">template(name="json-template"
  type="list") {
    constant(value="{")
      constant(value="\"@timestamp\":\"")     property(name="timereported" dateFormat="rfc3339")
      constant(value="\",\"@version\":\"1")
      constant(value="\",\"message\":\"")     property(name="msg" format="json")
      constant(value="\",\"sysloghost\":\"")  property(name="hostname")
      constant(value="\",\"severity\":\"")    property(name="syslogseverity-text")
      constant(value="\",\"facility\":\"")    property(name="syslogfacility-text")
      constant(value="\",\"programname\":\"") property(name="programname")
      constant(value="\",\"procid\":\"")      property(name="procid")
    constant(value="\"}\n")
}
</code></pre>
<p>Other than the first and the last, notice that the lines produced by this template have a comma at the beginning of them.  This is to maintain the JSON structure <em>and</em> help keep the file readable by lining everything up neatly.  This template formats your messages in the way that Elasticsearch and Logstash expect to receive them. This is what they will look like:</p>
<div class="code-label " title="Example JSON message">Example JSON message</div><pre class="code-pre "><code langs="">{
  "@timestamp" : "2015-11-18T18:45:00Z",
  "@version" : "1",
  "message" : "Your syslog message here",
  "sysloghost" : "hostname.example.com",
  "severity" : "info",
  "facility" : "daemon",
  "programname" : "my_program",
  "procid" : "1234"
}
</code></pre>
<p><span class="tip"><strong>Tip:</strong> The <a href="http://www.rsyslog.com/doc/v8-stable/configuration/properties.html">rsyslog.com docs</a> show the variables available from rsyslog if you would like to custom the log data. However, you must send it in JSON format to Logstash and then to Elasticsearch.<br /></span></p>

<p>The data being sent is not using this format yet. The next step shows out to configure the server to use this template file.</p>

<h2 id="step-6-—-configuring-the-centralized-server-to-send-to-logstash">Step 6 — Configuring the Centralized Server to Send to Logstash</h2>

<p>Now that we have the template file that defines the proper JSON format, let's configure the centralized rsyslog server to send the data to Logstash, which is on the same Droplet for this tutorial.</p>

<p>At startup, rsyslog will look through the files in <code>/etc/rsyslog.d</code> and create its configuration from them.  Let's add our own configuration file to extended the configuration.</p>

<p>On the <strong>rsyslog-server</strong>, create <code>/etc/rsyslog.d/60-output.conf</code>:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo nano /etc/rsyslog.d/60-output.conf
</li></ul></code></pre>
<p>Copy the following lines to this file:</p>
<div class="code-label " title="/etc/rsyslog.d/60-output.conf">/etc/rsyslog.d/60-output.conf</div><pre class="code-pre "><code langs=""># This line sends all lines to defined IP address at port 10514,
# using the "json-template" format template

*.*                         @<span class="highlight">private_ip_logstash</span>:10514;json-template
</code></pre>
<p>The <code>*.*</code> at the beginning means to process the remainder of the line for all log messages. The <code>@</code> symbols means to use UDP (Use <code>@@</code> to instead use TCP). The IP address or hostname after the <code>@</code> is where to forward the messages. In our case, we are using the private IP address for <strong>rsyslog-server</strong> since the rsyslog centralized server and the Logstash server are installed on the same Droplet. <strong>This must match the private IP address you configure Logstash to listen on in the next step.</strong></p>

<p>The port number is next. This tutorial uses port 10514. Note that the Logstash server must listen on the same port using the same protocol. The last part is our template file that shows how to format the data before passing it along.</p>

<p>Do not restart rsyslog yet. First, we have to configure Logstash to receive the messages.</p>

<h2 id="step-7-—-configure-logstash-to-receive-json-messages">Step 7 — Configure Logstash to Receive JSON Messages</h2>

<p>In this step you will install Logstash, configure it to receive JSON messages from rsyslog, and configure it to send the JSON messages on to Elasticsearch.</p>

<p>Logstash requires Java 7 or later. Use the instructions from <strong>Step 1</strong> of the <a href="https://indiareads/community/tutorials/how-to-install-and-configure-elasticsearch-on-ubuntu-14-04">Elasticsearch tutorial</a> to install Java 7 or 8 on the <strong>rsyslog-server</strong> Droplet.</p>

<p>Next, install the security key for the Logstash repository:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">wget -qO - https://packages.elastic.co/GPG-KEY-elasticsearch | sudo apt-key add -
</li></ul></code></pre>
<p>Add the repository definition to your <code>/etc/apt/sources.list</code> file:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">echo "deb http://packages.elastic.co/logstash/2.3/debian stable main" | sudo tee -a /etc/apt/sources.list
</li></ul></code></pre>
<p><span class="note"><strong>Note:</strong> Use the <code>echo</code> method described above to add the Logstash repository. Do not use <code>add-apt-repository</code> as it will add a <code>deb-src</code> entry as well, but Elastic does not provide a source package. This will result in an error when you attempt to run <code>apt-get update</code>.<br /></span></p>

<p>Update your package lists to include the Logstash repository:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo apt-get update
</li></ul></code></pre>
<p>Finally, install Logstash:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo apt-get install logstash
</li></ul></code></pre>
<p>Now that Logstash is installed, let's configure it to listen for messages from rsyslog.</p>

<p>The default installation of Logstash looks for configuration files in <code>/etc/logstash/conf.d</code>.  Edit the main configuration file:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo nano /etc/logstash/conf.d/logstash.conf
</li></ul></code></pre>
<p>Then, add these lines to <code>/etc/logstash/conf.d/logstash.conf</code>:</p>
<div class="code-label " title="/etc/logstash/conf.d/logstash.conf`">/etc/logstash/conf.d/logstash.conf`</div><pre class="code-pre "><code langs=""># This input block will listen on port 10514 for logs to come in.
# host should be an IP on the Logstash server.
# codec => "json" indicates that we expect the lines we're receiving to be in JSON format
# type => "rsyslog" is an optional identifier to help identify messaging streams in the pipeline.

input {
  udp {
    host => "<span class="highlight">logstash_private_ip</span>"
    port => 10514
    codec => "json"
    type => "rsyslog"
  }
}

# This is an empty filter block.  You can later add other filters here to further process
# your log lines

filter { }

# This output block will send all events of type "rsyslog" to Elasticsearch at the configured
# host and port into daily indices of the pattern, "rsyslog-YYYY.MM.DD"

output {
  if [type] == "rsyslog" {
    elasticsearch {
      hosts => [ "<span class="highlight">elasticsearch_private_ip</span>:9200" ]
    }
  }
}
</code></pre>
<p>The syslog protocol is UDP by definition, so this configuration mirrors that standard.</p>

<p>In the input block, set the Logstash host address by replacing <span class="highlight">logstash<em>private</em>ip</span> with the private IP address of <strong>rsyslog-server</strong>, which also has Logstash installed on it. </p>

<p>The input block configure Logstash to listen on port <code>10514</code> so it won't compete with syslog instances on the same machine. A port less than 1024 would require Logstash to be run as root, which is not a good security practice.</p>

<p>Be sure to replace <span class="highlight">elasticsearch<em>private</em>ip</span> with the <strong>private IP address</strong> of your Elasticsearch Droplet. The output block shows a simple <a href="https://www.elastic.co/guide/en/logstash/current/event-dependent-configuration.html#conditionals">conditional</a> configuration.  Its object is to only allow matching events through.  In this case, that is only events with a "type" of "rsyslog". </p>

<p>Test your Logstash configuraiton changes:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo service logstash configtest
</li></ul></code></pre>
<p>It should display <code>Configuration OK</code> if there are no syntax errors. Otherwise, try and read the error output to see what's wrong with your Logstash configuration.</p>

<p>When all these steps are completed, you can start your Logstash instance by running:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo service logstash start
</li></ul></code></pre>
<p>Also restart rsyslog on the same server since it has a Logstash instance to forward to now:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo service rsyslog restart
</li></ul></code></pre>
<p>To verify that Logstash is listening on port 10514:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">netstat -na | grep 10514
</li></ul></code></pre>
<p>You should see something like this:</p>
<div class="code-label " title="Output of netstat">Output of netstat</div><pre class="code-pre "><code langs="">udp6       0      0 10.128.33.68:10514     :::*  
</code></pre>
<p>You will see the private IP address of <strong>rsyslog-server</strong> and the 10514 port number we are using to listen for rsyslog data.</p>

<span class="tip"><p>
<strong>Tip:</strong> To troubleshoot Logstash, stop the service with <code>sudo service logstash stop</code> and run it in the foreground with verbose messages:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">/opt/logstash/bin/logstash -f /etc/logstash/conf.d/logstash.conf --verbose
</li></ul></code></pre>
<p>It will contain usual information such as verifying with IP address and UDP port Logstash is using:</p>
<pre class="code-pre "><code langs="">Starting UDP listener {:address=>"10.128.33.68:10514", :level=>:info}
</code></pre>
<p></p></span>

<h2 id="step-8-—-verifying-elasticsearch-input">Step 8 — Verifying Elasticsearch Input</h2>

<p>Earlier, we configured Elasticsearch to listen on its private IP address. It should now be receiving messages from Logstash. In this step, we will verify that Elasticsearch is receiving the log data.</p>

<p>The <strong>rsyslog-client</strong> and <strong>rsyslog-server</strong> Droplets should be sending all their log data to Logstash, which is then passed along to Elasticsearch. Let's generate a security message to verify that Elasticsearch is indeed receiving these messages.</p>

<p>On <strong>rsyslog-client</strong>, execute the following command:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo tail /var/log/auth.log
</li></ul></code></pre>
<p>You will see the security log on the local system at the end of the output. It will look similar to:</p>
<div class="code-label " title="Output of tail /var/log/auth.log">Output of tail /var/log/auth.log</div><pre class="code-pre "><code langs="">May  2 16:43:15 rsyslog-client sudo:    sammy : TTY=pts/0 ; PWD=/etc/rsyslog.d ; USER=root ; COMMAND=/usr/bin/tail /var/log/auth.log
May  2 16:43:15 rsyslog-client sudo: pam_unix(sudo:session): session opened for user root by sammy(uid=0)
</code></pre>
<p>With a simple query, you can check Elasticsearch:</p>

<p>Run the following command on the Elasticsearch server or any system that is allowed to access it. Replace <span class="highlight">elasticsearch_ip</span> with the private IP address of the Elasticsearch server. This IP address must also be the one you configured Elasticsearch to listen on earlier in this tutorial.</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">curl -XGET 'http://<span class="highlight">elasticsearch_ip</span>:9200/_all/_search?q=*&pretty'
</li></ul></code></pre>
<p>In the output you will see something similar to the following:</p>
<div class="code-label " title="Output of curl">Output of curl</div><pre class="code-pre "><code langs="">{
      "_index" : "logstash-2016.05.04",
      "_type" : "rsyslog",
      "_id" : "AVR8fpR-e6FP4Elp89Ww",
      "_score" : 1.0,
      "_source":{"@timestamp":"2016-05-04T15:59:10.000Z","@version":"1","message":"    sammy : TTY=pts/0 ; PWD=/home/sammy ; USER=root ; COMMAND=/usr/bin/tail /var/log/auth.log","sysloghost":"rsyslog-client","severity":"notice","facility":"authpriv","programname":"sudo","procid":"-","type":"rsyslog","host":"10.128.33.68"}
    },
</code></pre>
<p>Notice that the name of the Droplet that generated the rsyslog message is in the log (<strong>rsyslog-client</strong>).</p>

<p>With this simple verification step, your centralized rsyslog setup is complete and fully operational!</p>

<h2 id="conclusion">Conclusion</h2>

<p>Your logs are in Elasticsearch now.  What's next?  Consider reading up on what <a href="https://www.elastic.co/products/kibana">Kibana</a> can do to visualize the data you have in Elasticsearch, including line and bar graphs, pie charts, maps, and more. <a href="https://indiareads/community/tutorials/how-to-use-logstash-and-kibana-to-centralize-and-visualize-logs-on-ubuntu-14-04#connect-to-kibana">How To Use Logstash and Kibana To Centralize Logs On Ubuntu 14.04</a> explains how to use Kibana web interface to search and visualize logs.</p>

<p>Perhaps your data would be more valuable with further parsing and tokenization.  If so, then learning more about <a href="https://www.elastic.co/products/logstash">Logstash</a> will help you achieve that result.</p>

    