<link rel='stylesheet' href='digital_ocean.css'><br><img src=https://community-cdn-digitalocean-com.global.ssl.fastly.net/assets/tutorials/images/large/elk---twitter.png?1462816754/> <br> 
      <h3 id="introduction">Introduction</h3>

<p>In this tutorial, we will go over the installation of the Elasticsearch ELK Stack on Ubuntu 16.04 (that is, Elasticsearch 2.3.x, Logstash 2.3.x, and Kibana 4.5.x).  We will also show you how to configure it to gather and visualize the syslogs of your systems in a centralized location, using Filebeat 1.2.x.  Logstash is an open source tool for collecting, parsing, and storing logs for future use.  Kibana is a web interface that can be used to search and view the logs that Logstash has indexed.  Both of these tools are based on Elasticsearch, which is used for storing logs.</p>

<p>Centralized logging can be very useful when attempting to identify problems with your servers or applications, as it allows you to search through all of your logs in a single place.  It is also useful because it allows you to identify issues that span multiple servers by correlating their logs during a specific time frame.</p>

<p>It is possible to use Logstash to gather logs of all types, but we will limit the scope of this tutorial to syslog gathering.</p>

<h2 id="our-goal">Our Goal</h2>

<p>The goal of the tutorial is to set up Logstash to gather syslogs of multiple servers, and set up Kibana to visualize the gathered logs.</p>

<p>Our ELK stack setup has four main components:</p>

<ul>
<li><strong>Logstash</strong>: The server component of Logstash that processes incoming logs</li>
<li><strong>Elasticsearch</strong>: Stores all of the logs</li>
<li><strong>Kibana</strong>: Web interface for searching and visualizing logs, which will be proxied through Nginx</li>
<li><strong>Filebeat</strong>: Installed on client servers that will send their logs to Logstash, Filebeat serves as a log shipping agent that utilizes the <em>lumberjack</em> networking protocol to communicate with Logstash</li>
</ul>

<p><img src="https://assets.digitalocean.com/articles/elk/elk-infrastructure.png" alt="ELK Infrastructure" /></p>

<p>We will install the first three components on a single server, which we will refer to as our <strong>ELK Server</strong>.  Filebeat will be installed on all of the client servers that we want to gather logs for, which we will refer to collectively as our <strong>Client Servers</strong>.</p>

<h2 id="prerequisites">Prerequisites</h2>

<p>To complete this tutorial, you will require <code>sudo</code> access on an Ubuntu 16.04 server.  Instructions to set that up can be found here: <a href="https://indiareads/community/tutorials/initial-server-setup-with-ubuntu-16-04">Initial Server Setup with Ubuntu 16.04</a>.</p>

<p>If you would prefer to use CentOS instead, check out this tutorial: <a href="https://indiareads/community/tutorials/how-to-install-elasticsearch-logstash-and-kibana-elk-stack-on-centos-7">How To Install ELK on CentOS 7</a>.</p>

<p>The amount of CPU, RAM, and storage that your ELK Server will require depends on the volume of logs that you intend to gather.  For this tutorial, we will be using a VPS with the following specs for our ELK Server:</p>

<ul>
<li>OS: Ubuntu 16.04</li>
<li>RAM: 4GB</li>
<li>CPU: 2</li>
</ul>

<p>In addition to your ELK Server, you will want to have a few other servers that you will gather logs from.</p>

<p>Let's get started on setting up our ELK Server!</p>

<h2 id="install-java-8">Install Java 8</h2>

<p>Elasticsearch and Logstash require Java, so we will install that now.  We will install a recent version of Oracle Java 8 because that is what Elasticsearch recommends.  It should, however, work fine with OpenJDK, if you decide to go that route.</p>

<p>Add the Oracle Java PPA to <code>apt</code>:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo add-apt-repository -y ppa:webupd8team/java
</li></ul></code></pre>
<p>Update your <code>apt</code> package database:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo apt-get update
</li></ul></code></pre>
<p>Install the latest stable version of Oracle Java 8 with this command (and accept the license agreement that pops up):</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo apt-get -y install oracle-java8-installer
</li></ul></code></pre>
<p>Now that Java 8 is installed, let's install ElasticSearch.</p>

<h2 id="install-elasticsearch">Install Elasticsearch</h2>

<p>Elasticsearch can be installed with a package manager by adding Elastic's package source list.</p>

<p>Run the following command to import the Elasticsearch public GPG key into apt:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">wget -qO - https://packages.elastic.co/GPG-KEY-elasticsearch | sudo apt-key add -
</li></ul></code></pre>
<p>If your prompt seems to hang, it is likely waiting for your user's password (to authorize the <code>sudo</code> command).  If this is the case, enter your password.</p>

<p>Create the Elasticsearch source list:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">echo "deb http://packages.elastic.co/elasticsearch/2.x/debian stable main" | sudo tee -a /etc/apt/sources.list.d/elasticsearch-2.x.list
</li></ul></code></pre>
<p>Update the <code>apt</code> package database again:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo apt-get update
</li></ul></code></pre>
<p>Install Elasticsearch with this command:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo apt-get -y install elasticsearch
</li></ul></code></pre>
<p>Elasticsearch is now installed.  Let's edit the configuration:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo nano /etc/elasticsearch/elasticsearch.yml
</li></ul></code></pre>
<p>You will want to restrict outside access to your Elasticsearch instance (port 9200), so outsiders can't read your data or shutdown your Elasticsearch cluster through the HTTP API.  Find the line that specifies <code>network.host</code>, uncomment it, and replace its value with "localhost" so it looks like this:</p>
<div class="code-label " title="/etc/elasticsearch/elasticsearch.yml excerpt (updated)">/etc/elasticsearch/elasticsearch.yml excerpt (updated)</div><pre class="code-pre "><code langs="">network.host: <span class="highlight">localhost</span>
</code></pre>
<p>Save and exit <code>elasticsearch.yml</code>.</p>

<p>Now, start Elasticsearch:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo systemctl restart elasticsearch
</li></ul></code></pre>
<p>Then, run the following command to start Elasticsearch on boot up:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo systemctl daemon-reload
</li><li class="line" prefix="$">sudo systemctl enable elasticsearch
</li></ul></code></pre>
<p>Now that Elasticsearch is up and running, let's install Kibana.</p>

<h2 id="install-kibana">Install Kibana</h2>

<p>Kibana can be installed with a package manager by adding Elastic's package source list.</p>

<p>Add the Kibana to your source list:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">echo "deb http://packages.elastic.co/kibana/4.5/debian stable main" | sudo tee -a /etc/apt/sources.list
</li></ul></code></pre>
<p>Update your <code>apt</code> package database:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo apt-get update
</li></ul></code></pre>
<p>Install Kibana with this command:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo apt-get -y install kibana
</li></ul></code></pre>
<p>Kibana is now installed.</p>

<p>Open the Kibana configuration file for editing:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo nano /opt/kibana/config/kibana.yml
</li></ul></code></pre>
<p>In the Kibana configuration file, find the line that specifies <code>server.host</code>, and replace the IP address ("0.0.0.0" by default) with "localhost":</p>
<div class="code-label " title="/opt/kibana/config/kibana.yml excerpt (updated)">/opt/kibana/config/kibana.yml excerpt (updated)</div><pre class="code-pre "><code langs="">server.host: "<span class="highlight">localhost</span>"
</code></pre>
<p>Save and exit.  This setting makes it so Kibana will only be accessible to the localhost.  This is fine because we will use an Nginx reverse proxy to allow external access.</p>

<p>Now enable the Kibana service, and start it:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo systemctl daemon-reload
</li><li class="line" prefix="$">sudo systemctl enable kibana
</li><li class="line" prefix="$">sudo systemctl start kibana
</li></ul></code></pre>
<p>Before we can use the Kibana web interface, we have to set up a reverse proxy.  Let's do that now, with Nginx.</p>

<h2 id="install-nginx">Install Nginx</h2>

<p>Because we configured Kibana to listen on <code>localhost</code>, we must set up a reverse proxy to allow external access to it.  We will use Nginx for this purpose.</p>

<p></p><div class="code-label notes-and-warnings note" title="Note">Note</div><span class="note">
If you already have an Nginx instance that you want to use, feel free to use that instead.  Just make sure to configure Kibana so it is reachable by your Nginx server (you probably want to change the <code>host</code> value, in <code>/opt/kibana/config/kibana.yml</code>, to your Kibana server's private IP address or hostname).  Also, it is recommended that you enable SSL/TLS.<br /></span>

<p>Use <code>apt</code> to install Nginx:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo apt-get -y install nginx
</li></ul></code></pre>
<p>Use <code>openssl</code> to create an admin user, called "kibanaadmin" (you should use another name), that can access the Kibana web interface:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo -v
</li><li class="line" prefix="$">echo "<span class="highlight">kibanaadmin</span>:`openssl passwd -apr1`" | sudo tee -a /etc/nginx/htpasswd.users
</li></ul></code></pre>
<p>Enter a password at the prompt.  Remember this login, as you will need it to access the Kibana web interface.</p>

<p>Now open the Nginx default server block in your favorite editor:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo nano /etc/nginx/sites-available/default
</li></ul></code></pre>
<p>Delete the file's contents, and paste the following code block into the file.  Be sure to update the <code>server_name</code> to match your server's name or public IP address:</p>
<div class="code-label " title="/etc/nginx/sites-available/default">/etc/nginx/sites-available/default</div><pre class="code-pre line_numbers"><code langs=""><ul class="prefixed"><li class="line" prefix="1">server {
</li><li class="line" prefix="2">    listen 80;
</li><li class="line" prefix="3">
</li><li class="line" prefix="4">    server_name <span class="highlight">example.com</span>;
</li><li class="line" prefix="5">
</li><li class="line" prefix="6">    auth_basic "Restricted Access";
</li><li class="line" prefix="7">    auth_basic_user_file /etc/nginx/htpasswd.users;
</li><li class="line" prefix="8">
</li><li class="line" prefix="9">    location / {
</li><li class="line" prefix="10">        proxy_pass http://localhost:5601;
</li><li class="line" prefix="11">        proxy_http_version 1.1;
</li><li class="line" prefix="12">        proxy_set_header Upgrade $http_upgrade;
</li><li class="line" prefix="13">        proxy_set_header Connection 'upgrade';
</li><li class="line" prefix="14">        proxy_set_header Host $host;
</li><li class="line" prefix="15">        proxy_cache_bypass $http_upgrade;        
</li><li class="line" prefix="16">    }
</li><li class="line" prefix="17">}
</li></ul></code></pre>
<p>Save and exit. This configures Nginx to direct your server's HTTP traffic to the Kibana application, which is listening on <code>localhost:5601</code>. Also, Nginx will use the <code>htpasswd.users</code> file, that we created earlier, and require basic authentication.</p>

<p>Now, check the config for syntax errors and restart Nginx if none are found:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo nginx -t
</li><li class="line" prefix="$">sudo systemctl restart nginx
</li></ul></code></pre>
<p>If you followed the initial server setup guide for 16.04, you have a UFW firewall enabled.  To allow connections to Nginx, we can adjust the rules by typing:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo ufw allow 'Nginx Full'
</li></ul></code></pre>
<p>Kibana is now accessible via your FQDN or the public IP address of your ELK Server i.e. http://elk_server_public_ip/.  If you go there in a web browser, after entering the "kibanaadmin" credentials, you should see a Kibana welcome page which will ask you to configure an index pattern.  Let's get back to that later, after we install all of the other components.</p>

<h2 id="install-logstash">Install Logstash</h2>

<p>The Logstash package is available from the same repository as Elasticsearch, and we already installed that public key, so let's add Logstash to our source list:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">echo "deb http://packages.elastic.co/logstash/2.3/debian stable main" | sudo tee -a /etc/apt/sources.list
</li></ul></code></pre>
<p>Update your <code>apt</code> package database:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo apt-get update
</li></ul></code></pre>
<p>Install Logstash with this command:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo apt-get install logstash
</li></ul></code></pre>
<p>Logstash is installed but it is not configured yet.</p>

<h2 id="generate-ssl-certificates">Generate SSL Certificates</h2>

<p>Since we are going to use Filebeat to ship logs from our Client Servers to our ELK Server, we need to create an SSL certificate and key pair.  The certificate is used by Filebeat to verify the identity of ELK Server.  Create the directories that will store the certificate and private key with the following commands:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo mkdir -p /etc/pki/tls/certs
</li><li class="line" prefix="$">sudo mkdir /etc/pki/tls/private
</li></ul></code></pre>
<p>Now you have two options for generating your SSL certificates.  If you have a DNS setup that will allow your client servers to resolve the IP address of the ELK Server,  use <strong>Option 2</strong>. Otherwise, <strong>Option 1</strong> will allow you to use IP addresses.</p>

<h3 id="option-1-ip-address">Option 1: IP Address</h3>

<p>If you don't have a DNS setup—that would allow your servers, that you will gather logs from, to resolve the IP address of your ELK Server—you will have to add your ELK Server's private IP address to the <code>subjectAltName</code> (SAN) field of the SSL certificate that we are about to generate.  To do so, open the OpenSSL configuration file:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo nano /etc/ssl/openssl.cnf
</li></ul></code></pre>
<p>Find the <code>[ v3_ca ]</code> section in the file, and add this line under it (substituting in the ELK Server's <strong>private IP address</strong>):</p>
<div class="code-label " title="/etc/ssl/openssl.cnf excerpt (updated)">/etc/ssl/openssl.cnf excerpt (updated)</div><pre class="code-pre "><code langs="">subjectAltName = IP: <span class="highlight">ELK_server_private_IP</span>
</code></pre>
<p>Save and exit.</p>

<p>Now generate the SSL certificate and private key in the appropriate locations (<code>/etc/pki/tls/...</code>), with the following commands:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">cd /etc/pki/tls
</li><li class="line" prefix="$">sudo openssl req -config /etc/ssl/openssl.cnf -x509 -days 3650 -batch -nodes -newkey rsa:2048 -keyout private/logstash-forwarder.key -out certs/logstash-forwarder.crt
</li></ul></code></pre>
<p>The <em>logstash-forwarder.crt</em> file will be copied to all of the servers that will send logs to Logstash but we will do that a little later. Let's complete our Logstash configuration. If you went with this option, skip option 2 and move on to <strong>Configure Logstash</strong>.</p>

<h3 id="option-2-fqdn-dns">Option 2: FQDN (DNS)</h3>

<p>If you have a DNS setup with your private networking, you should create an A record that contains the ELK Server's private IP address—this domain name will be used in the next command, to generate the SSL certificate.  Alternatively, you can use a record that points to the server's public IP address.  Just be sure that your servers (the ones that you will be gathering logs from) will be able to resolve the domain name to your ELK Server.</p>

<p>Now generate the SSL certificate and private key, in the appropriate locations (<code>/etc/pki/tls/...</code>), with the following (substitute in the FQDN of the ELK Server):</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">cd /etc/pki/tls
</li><li class="line" prefix="$">sudo openssl req -subj '/CN=<span class="highlight">ELK_server_fqdn</span>/' -x509 -days 3650 -batch -nodes -newkey rsa:2048 -keyout private/logstash-forwarder.key -out certs/logstash-forwarder.crt
</li></ul></code></pre>
<p>The <em>logstash-forwarder.crt</em> file will be copied to all of the servers that will send logs to Logstash but we will do that a little later.  Let's complete our Logstash configuration.</p>

<h2 id="configure-logstash">Configure Logstash</h2>

<p>Logstash configuration files are in the JSON-format, and reside in <code>/etc/logstash/conf.d</code>.  The configuration consists of three sections: inputs, filters, and outputs.</p>

<p>Let's create a configuration file called <code>02-beats-input.conf</code> and set up our "filebeat" input:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo nano /etc/logstash/conf.d/02-beats-input.conf
</li></ul></code></pre>
<p>Insert the following <strong>input</strong> configuration:</p>
<div class="code-label " title="/etc/logstash/conf.d/02-beats-input.conf">/etc/logstash/conf.d/02-beats-input.conf</div><pre class="code-pre line_numbers"><code langs=""><ul class="prefixed"><li class="line" prefix="1">input {
</li><li class="line" prefix="2">  beats {
</li><li class="line" prefix="3">    port => 5044
</li><li class="line" prefix="4">    ssl => true
</li><li class="line" prefix="5">    ssl_certificate => "/etc/pki/tls/certs/logstash-forwarder.crt"
</li><li class="line" prefix="6">    ssl_key => "/etc/pki/tls/private/logstash-forwarder.key"
</li><li class="line" prefix="7">  }
</li><li class="line" prefix="8">}
</li></ul></code></pre>
<p>Save and quit.  This specifies a <code>beats</code> input that will listen on TCP port <code>5044</code>, and it will use the SSL certificate and private key that we created earlier.</p>

<p>If you followed the Ubuntu 16.04 initial server setup guide, you will have a UFW firewall configured.  To allow Logstash to receive connections on port <code>5044</code>, we need to open that port:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo ufw allow 5044
</li></ul></code></pre>
<p>Now let's create a configuration file called <code>10-syslog-filter.conf</code>, where we will add a filter for syslog messages:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo nano /etc/logstash/conf.d/10-syslog-filter.conf
</li></ul></code></pre>
<p>Insert the following syslog <strong>filter</strong> configuration:</p>
<div class="code-label " title="/etc/logstash/conf.d/10-syslog-filter.conf">/etc/logstash/conf.d/10-syslog-filter.conf</div><pre class="code-pre line_numbers"><code langs=""><ul class="prefixed"><li class="line" prefix="1">filter {
</li><li class="line" prefix="2">  if [type] == "syslog" {
</li><li class="line" prefix="3">    grok {
</li><li class="line" prefix="4">      match => { "message" => "%{SYSLOGTIMESTAMP:syslog_timestamp} %{SYSLOGHOST:syslog_hostname} %{DATA:syslog_program}(?:\[%{POSINT:syslog_pid}\])?: %{GREEDYDATA:syslog_message}" }
</li><li class="line" prefix="5">      add_field => [ "received_at", "%{@timestamp}" ]
</li><li class="line" prefix="6">      add_field => [ "received_from", "%{host}" ]
</li><li class="line" prefix="7">    }
</li><li class="line" prefix="8">    syslog_pri { }
</li><li class="line" prefix="9">    date {
</li><li class="line" prefix="10">      match => [ "syslog_timestamp", "MMM  d HH:mm:ss", "MMM dd HH:mm:ss" ]
</li><li class="line" prefix="11">    }
</li><li class="line" prefix="12">  }
</li><li class="line" prefix="13">}
</li></ul></code></pre>
<p>Save and quit.  This filter looks for logs that are labeled as "syslog" type (by Filebeat), and it will try to use <code>grok</code> to parse incoming syslog logs to make it structured and query-able.</p>

<p>Lastly, we will create a configuration file called <code>30-elasticsearch-output.conf</code>:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo nano /etc/logstash/conf.d/30-elasticsearch-output.conf
</li></ul></code></pre>
<p>Insert the following <strong>output</strong> configuration:</p>
<div class="code-label " title="/etc/logstash/conf.d/30-elasticsearch-output.conf">/etc/logstash/conf.d/30-elasticsearch-output.conf</div><pre class="code-pre line_numbers"><code langs=""><ul class="prefixed"><li class="line" prefix="1">output {
</li><li class="line" prefix="2">  elasticsearch {
</li><li class="line" prefix="3">    hosts => ["localhost:9200"]
</li><li class="line" prefix="4">    sniffing => true
</li><li class="line" prefix="5">    manage_template => false
</li><li class="line" prefix="6">    index => "%{[@metadata][beat]}-%{+YYYY.MM.dd}"
</li><li class="line" prefix="7">    document_type => "%{[@metadata][type]}"
</li><li class="line" prefix="8">  }
</li><li class="line" prefix="9">}
</li></ul></code></pre>
<p>Save and exit.  This output basically configures Logstash to store the beats data in Elasticsearch which is running at <code>localhost:9200</code>, in an index named after the beat used (filebeat, in our case).</p>

<p>If you want to add filters for other applications that use the Filebeat input, be sure to name the files so they sort between the input and the output configuration (i.e. between 02- and 30-).</p>

<p>Test your Logstash configuration with this command:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo /opt/logstash/bin/logstash --configtest -f /etc/logstash/conf.d/
</li></ul></code></pre>
<p>After a few seconds, it should display <code>Configuration OK</code> if there are no syntax errors.  Otherwise, try and read the error output to see what's wrong with your Logstash configuration.</p>

<p>Restart Logstash, and enable it, to put our configuration changes into effect:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo systemctl restart logstash
</li><li class="line" prefix="$">sudo systemctl enable logstash
</li></ul></code></pre>
<p>Logstash will be listening for </p>

<p>Next, we'll load the sample Kibana dashboards.</p>

<h2 id="load-kibana-dashboards">Load Kibana Dashboards</h2>

<p>Elastic provides several sample Kibana dashboards and Beats index patterns that can help you get started with Kibana. Although we won't use the dashboards in this tutorial,  we'll load them anyway so we can use the Filebeat index pattern that it includes.</p>

<p>Use <code>curl</code> to download the file to your home directory:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">cd ~
</li><li class="line" prefix="$">curl -L -O https://download.elastic.co/beats/dashboards/beats-dashboards-1.2.2.zip
</li></ul></code></pre>
<p>Install the <code>unzip</code> package with this command:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo apt-get -y install unzip
</li></ul></code></pre>
<p>Next, extract the contents of the archive:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">unzip beats-dashboards-*.zip
</li></ul></code></pre>
<p>And load the sample dashboards, visualizations and Beats index patterns into Elasticsearch with these commands:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">cd beats-dashboards-*
</li><li class="line" prefix="$">./load.sh
</li></ul></code></pre>
<p>These are the index patterns that we just loaded:</p>

<ul>
<li>packetbeat-*</li>
<li>topbeat-*</li>
<li>filebeat-*</li>
<li>winlogbeat-*</li>
</ul>

<p>When we start using Kibana, we will select the Filebeat index pattern as our default.</p>

<h2 id="load-filebeat-index-template-in-elasticsearch">Load Filebeat Index Template in Elasticsearch</h2>

<p>Because we are planning on using Filebeat to ship logs to Elasticsearch, we should load a Filebeat index template.  The index template will configure Elasticsearch to analyze incoming Filebeat fields in an intelligent way.</p>

<p>First, download the Filebeat index template to your home directory:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">cd ~
</li><li class="line" prefix="$">curl -O https://gist.githubusercontent.com/thisismitch/3429023e8438cc25b86c/raw/d8c479e2a1adcea8b1fe86570e42abab0f10f364/filebeat-index-template.json
</li></ul></code></pre>
<p>Then load the template with this command:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">curl -XPUT 'http://localhost:9200/_template/filebeat?pretty' -d@filebeat-index-template.json
</li></ul></code></pre>
<p>If the template loaded properly, you should see a message like this:</p>
<pre class="code-pre "><code langs=""><div class="secondary-code-label " title="Output:">Output:</div>{
  "acknowledged" : true
}
</code></pre>
<p>Now that our ELK Server is ready to receive Filebeat data, let's move onto setting up Filebeat on each client server.</p>

<h2 id="set-up-filebeat-add-client-servers">Set Up Filebeat (Add Client Servers)</h2>

<p>Do these steps for each Ubuntu or Debian server that you want to send logs to Logstash on your ELK Server.  For instructions on installing Filebeat on Red Hat-based Linux distributions (e.g. RHEL, CentOS, etc.), refer to the <a href="https://indiareads/community/tutorials/how-to-install-elasticsearch-logstash-and-kibana-elk-stack-on-centos-7#set-up-filebeat-(add-client-servers)">Set Up Filebeat (Add Client Servers) section</a> of the CentOS variation of this tutorial.</p>

<h3 id="copy-ssl-certificate">Copy SSL Certificate</h3>

<p>On your <strong>ELK Server</strong>, copy the SSL certificate you created to your <strong>Client Server</strong> (substitute the client server's address, and your own login):</p>
<pre class="code-pre custom_prefix"><code langs=""><ul class="prefixed"><li class="line" prefix="elk$">scp /etc/pki/tls/certs/logstash-forwarder.crt <span class="highlight">user</span>@<span class="highlight">client_server_private_address</span>:/tmp
</li></ul></code></pre>
<p>After providing your login credentials, ensure that the certificate copy was successful.  It is required for communication between the client servers and the ELK Server.</p>

<p>Now, on your <strong>Client Server</strong>, copy the ELK Server's SSL certificate into the appropriate location (<code>/etc/pki/tls/certs</code>):</p>
<pre class="code-pre custom_prefix"><code langs=""><ul class="prefixed"><li class="line" prefix="client$">sudo mkdir -p /etc/pki/tls/certs
</li><li class="line" prefix="client$">sudo cp /tmp/logstash-forwarder.crt /etc/pki/tls/certs/
</li></ul></code></pre>
<p>Now we will install the Topbeat package.</p>

<h3 id="install-filebeat-package">Install Filebeat Package</h3>

<p>On <strong>Client Server</strong>, create the Beats source list:</p>
<pre class="code-pre custom_prefix"><code langs=""><ul class="prefixed"><li class="line" prefix="client$">echo "deb https://packages.elastic.co/beats/apt stable main" |  sudo tee -a /etc/apt/sources.list.d/beats.list
</li></ul></code></pre>
<p>It also uses the same GPG key as Elasticsearch, which can be installed with this command:</p>
<pre class="code-pre custom_prefix"><code langs=""><ul class="prefixed"><li class="line" prefix="client$">wget -qO - https://packages.elastic.co/GPG-KEY-elasticsearch | sudo apt-key add -
</li></ul></code></pre>
<p>Then install the Filebeat package:</p>
<pre class="code-pre custom_prefix"><code langs=""><ul class="prefixed"><li class="line" prefix="client$">sudo apt-get update
</li><li class="line" prefix="client$">sudo apt-get install filebeat
</li></ul></code></pre>
<p>Filebeat is installed but it is not configured yet.</p>

<h3 id="configure-filebeat">Configure Filebeat</h3>

<p>Now we will configure Filebeat to connect to Logstash on our ELK Server.  This section will step you through modifying the example configuration file that comes with Filebeat.  When you complete the steps, you should have a file that looks something like <a href="https://gist.githubusercontent.com/thisismitch/3429023e8438cc25b86c/raw/de660ffdd3decacdcaf88109e5683e1eef75c01f/filebeat.yml-ubuntu">this</a>.</p>

<p>On the <strong>Client Server</strong>, create and edit Filebeat configuration file:</p>
<pre class="code-pre custom_prefix"><code langs=""><ul class="prefixed"><li class="line" prefix="client$">sudo nano /etc/filebeat/filebeat.yml
</li></ul></code></pre>
<p></p><div class="code-label notes-and-warnings note" title="Note">Note</div><span class="note">
Filebeat's configuration file is in YAML format, which means that indentation is very important!  Be sure to use the same number of spaces that are indicated in these instructions.<br /></span>

<p>Near the top of the file, you will see the <code>prospectors</code> section, which is where you can define <strong>prospectors</strong> that specify which log files should be shipped and how they should be handled.  Each prospector is indicated by the <code>-</code> character. </p>

<p>We'll modify the existing prospector to send <code>syslog</code> and <code>auth.log</code> to Logstash.  Under <code>paths</code>, comment out the <code>- /var/log/*.log</code> file.  This will prevent Filebeat from sending every <code>.log</code> in that directory to Logstash.  Then add new entries for <code>syslog</code> and <code>auth.log</code>.  It should look something like this when you're done:</p>
<div class="code-label " title="/etc/filebeat/filebeat.yml excerpt 1 of 5">/etc/filebeat/filebeat.yml excerpt 1 of 5</div><pre class="code-pre "><code langs="">...
      paths:
<span class="highlight">        - /var/log/auth.log</span>
<span class="highlight">        - /var/log/syslog</span>
       <span class="highlight">#</span> - /var/log/*.log
...
</code></pre>
<p>Then find the line that specifies <code>document_type:</code>, uncomment it and change its value to "syslog".  It should look like this after the modification:</p>
<div class="code-label " title="/etc/filebeat/filebeat.yml excerpt 2 of 5">/etc/filebeat/filebeat.yml excerpt 2 of 5</div><pre class="code-pre "><code langs="">...
      document_type: <span class="highlight">syslog</span>
...
</code></pre>
<p>This specifies that the logs in this prospector are of type <strong>syslog</strong> (which is the type that our Logstash filter is looking for).</p>

<p>If you want to send other files to your ELK server, or make any changes to how Filebeat handles your logs, feel free to modify or add prospector entries.</p>

<p>Next, under the <code>output</code> section, find the line that says <code>elasticsearch:</code>, which indicates the Elasticsearch output section (which we are not going to use).  <strong>Delete or comment out the entire Elasticsearch output section</strong> (up to the line that says <code>#logstash:</code>).</p>

<p>Find the commented out Logstash output section, indicated by the line that says <code>#logstash:</code>, and uncomment it by deleting the preceding <code>#</code>.  In this section, uncomment the <code>hosts: ["localhost:5044"]</code> line.  Change <code>localhost</code> to the private IP address (or hostname, if you went with that option) of your ELK server:</p>
<div class="code-label " title="/etc/filebeat/filebeat.yml excerpt 3 of 5">/etc/filebeat/filebeat.yml excerpt 3 of 5</div><pre class="code-pre "><code langs="">  ### Logstash as output
  logstash:
    # The Logstash hosts
    hosts: ["<span class="highlight">ELK_server_private_IP</span>:5044"]
</code></pre>
<p>This configures Filebeat to connect to Logstash on your ELK Server at port <code>5044</code> (the port that we specified a Logstash input for earlier).</p>

<p>Directly under the <code>hosts</code> entry, and with the same indentation, add this line:</p>
<div class="code-label " title="/etc/filebeat/filebeat.yml excerpt 4 of 5">/etc/filebeat/filebeat.yml excerpt 4 of 5</div><pre class="code-pre "><code langs="">  ### Logstash as output
  logstash:
    # The Logstash hosts
    hosts: ["<span class="highlight">ELK_server_private_IP</span>:5044"]
    <span class="highlight">bulk_max_size: 1024</span>
</code></pre>
<p>Next, find the <code>tls</code> section, and uncomment it.  Then uncomment the line that specifies <code>certificate_authorities</code>, and change its value to <code>["/etc/pki/tls/certs/logstash-forwarder.crt"]</code>. It should look something like this:</p>
<div class="code-label " title="/etc/filebeat/filebeat.yml excerpt 5 of 5">/etc/filebeat/filebeat.yml excerpt 5 of 5</div><pre class="code-pre "><code langs="">...
<span class="highlight">    tls:</span>
      # List of root certificates for HTTPS server verifications
      <span class="highlight">certificate_authorities: ["/etc/pki/tls/certs/logstash-forwarder.crt"]</span>
</code></pre>
<p>This configures Filebeat to use the SSL certificate that we created on the ELK Server.</p>

<p>Save and quit.</p>

<p>Now restart Filebeat to put our changes into place:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo systemctl restart filebeat
</li><li class="line" prefix="$">sudo systemctl enable filebeat
</li></ul></code></pre>
<p>Again, if you're not sure if your Filebeat configuration is correct, compare it against this <a href="https://gist.githubusercontent.com/thisismitch/3429023e8438cc25b86c/raw/de660ffdd3decacdcaf88109e5683e1eef75c01f/filebeat.yml-ubuntu">example Filebeat configuration</a>.</p>

<p>Now Filebeat is sending <code>syslog</code> and <code>auth.log</code> to Logstash on your ELK server!  Repeat this section for all of the other servers that you wish to gather logs for.</p>

<h2 id="test-filebeat-installation">Test Filebeat Installation</h2>

<p>If your ELK stack is setup properly, Filebeat (on your client server) should be shipping your logs to Logstash on your ELK server.  Logstash should be loading the Filebeat data into Elasticsearch using the indexes we imported earlier.</p>

<p>On your <strong>ELK Server</strong>, verify that Elasticsearch is indeed receiving the data by querying for the Filebeat index with this command:</p>
<pre class="code-pre custom_prefix"><code langs=""><ul class="prefixed"><li class="line" prefix="elk$">curl -XGET 'http://localhost:9200/filebeat-*/_search?pretty'
</li></ul></code></pre>
<p>You should see a bunch of output that looks like this:</p>
<pre class="code-pre "><code langs=""><div class="secondary-code-label " title="Sample Output:">Sample Output:</div>...
{
      "_index" : "filebeat-2016.01.29",
      "_type" : "log",
      "_id" : "AVKO98yuaHvsHQLa53HE",
      "_score" : 1.0,
      "_source":{"message":"Feb  3 14:34:00 rails sshd[963]: Server listening on :: port 22.","@version":"1","@timestamp":"2016-01-29T19:59:09.145Z","beat":{"hostname":"topbeat-u-03","name":"topbeat-u-03"},"count":1,"fields":null,"input_type":"log","offset":70,"source":"/var/log/auth.log","type":"log","host":"topbeat-u-03"}
    }
...
</code></pre>
<p>If your output shows 0 total hits, Elasticsearch is not loading any logs under the index you searched for, and you should review your setup for errors.  If you received the expected output, continue to the next step.</p>

<h2 id="connect-to-kibana">Connect to Kibana</h2>

<p>When you are finished setting up Filebeat on all of the servers that you want to gather logs for, let's look at Kibana, the web interface that we installed earlier.</p>

<p>In a web browser, go to the FQDN or public IP address of your ELK Server.  After entering the "kibanaadmin" credentials, you should see a page prompting you to configure a default index pattern:</p>

<p><img src="https://assets.digitalocean.com/articles/elk/1-filebeat-index.gif" alt="Create index" /></p>

<p>Go ahead and select <strong>filebeat-*</strong> from the Index Patterns menu (left side), then click the <strong>Star (Set as default index)</strong> button to set the Filebeat index as the default.</p>

<p>Now click the <strong>Discover</strong> link in the top navigation bar.  By default, this will show you all of the log data over the last 15 minutes.  You should see a histogram with log events, with log messages below:</p>

<p><img src="https://assets.digitalocean.com/articles/elk/2-filebeat-discover.png" alt="Discover page" /></p>

<p>Right now, there won't be much in there because you are only gathering syslogs from your client servers.  Here, you can search and browse through your logs.  You can also customize your dashboard.</p>

<p>Try the following things:</p>

<ul>
<li>Search for "root" to see if anyone is trying to log into your servers as root</li>
<li>Search for a particular hostname (search for <code>host: "<span class="highlight">hostname</span>"</code>)</li>
<li>Change the time frame by selecting an area on the histogram or from the menu above</li>
<li>Click on messages below the histogram to see how the data is being filtered</li>
</ul>

<p>Kibana has many other features, such as graphing and filtering, so feel free to poke around!</p>

<h2 id="conclusion">Conclusion</h2>

<p>Now that your syslogs are centralized via Elasticsearch and Logstash, and you are able to visualize them with Kibana, you should be off to a good start with centralizing all of your important logs.  Remember that you can send pretty much any type of log or indexed data to Logstash, but the data becomes even more useful if it is parsed and structured with grok.</p>

<p>To improve your new ELK stack, you should look into gathering and filtering your other logs with Logstash, and <a href="https://indiareads/community/tutorials/how-to-use-kibana-dashboards-and-visualizations">creating Kibana dashboards</a>.  You may also want to <a href="https://indiareads/community/tutorials/how-to-gather-infrastructure-metrics-with-topbeat-and-elk-on-ubuntu-14-04">gather system metrics by using Topbeat</a> with your ELK stack.  All of these topics are covered in the other tutorials in this series.</p>

<p>Good luck!</p>

    