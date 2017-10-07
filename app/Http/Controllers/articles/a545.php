<link rel='stylesheet' href='digital_ocean.css'><br><img src=https://community-cdn-digitalocean-com.global.ssl.fastly.net/assets/tutorials/images/large/elk---twitter.png?1428343951/> <br> 
      <h3 id="introduction">Introduction</h3>

<p>In this tutorial, we will go over the installation of the Elasticsearch ELK Stack on CentOS 7—that is, Elasticsearch 2.2.x, Logstash 2.2.x, and Kibana 4.4.x. We will also show you how to configure it to gather and visualize the syslogs of your systems in a centralized location, using Filebeat 1.1.x. Logstash is an open source tool for collecting, parsing, and storing logs for future use. Kibana is a web interface that can be used to search and view the logs that Logstash has indexed. Both of these tools are based on Elasticsearch, which is used for storing logs.</p>

<p>Centralized logging can be very useful when attempting to identify problems with your servers or applications, as it allows you to search through all of your logs in a single place. It is also useful because it allows you to identify issues that span multiple servers by correlating their logs during a specific time frame.</p>

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

<p>We will install the first three components on a single server, which we will refer to as our <strong>ELK Server</strong>. Filebeat will be installed on all of the client servers that we want to gather logs for, which we will refer to collectively as our <strong>Client Servers</strong>.</p>

<h2 id="prerequisites">Prerequisites</h2>

<p>To complete this tutorial, you will require root access to an CentOS 7 VPS. Instructions to set that up can be found here (steps 3 and 4): <a href="https://indiareads/community/tutorials/initial-server-setup-with-centos-7">Initial Server Setup with CentOS 7</a>.</p>

<p>If you would prefer to use Ubuntu instead, check out this tutorial: <a href="https://indiareads/community/tutorials/how-to-install-elasticsearch-logstash-and-kibana-elk-stack-on-ubuntu-14-04">How To Install ELK on Ubuntu 14.04</a>.</p>

<p>The amount of CPU, RAM, and storage that your ELK Server will require depends on the volume of logs that you intend to gather. For this tutorial, we will be using a VPS with the following specs for our ELK Server:</p>

<ul>
<li>OS: CentOS 7</li>
<li>RAM: 4GB</li>
<li>CPU: 2</li>
</ul>

<p>In addition to your ELK Server, you will want to have a few other servers that you will gather logs from.</p>

<p>Let's get started on setting up our ELK Server!</p>

<h2 id="install-java-8">Install Java 8</h2>

<p>Elasticsearch and Logstash require Java, so we will install that now. We will install a recent version of Oracle Java 8 because that is what Elasticsearch recommends. It should, however, work fine with OpenJDK, if you decide to go that route. Following the steps in this section means that you accept the Oracle Binary License Agreement for Java SE.</p>

<p>Change to your home directory and download the Oracle Java 8 (Update 73, the latest at the time of this writing) JDK RPM with these commands:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">cd ~
</li><li class="line" prefix="$">wget --no-cookies --no-check-certificate --header "Cookie: gpw_e24=http%3A%2F%2Fwww.oracle.com%2F; oraclelicense=accept-securebackup-cookie" "http://download.oracle.com/otn-pub/java/jdk/8u73-b02/jdk-8u73-linux-x64.rpm"
</li></ul></code></pre>
<p>Then install the RPM with this yum command (if you downloaded a different release, substitute the filename here):</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo yum -y localinstall jdk-8u73-linux-x64.rpm
</li></ul></code></pre>
<p>Now Java should be installed at <code>/usr/java/jdk1.8.0_73/jre/bin/java</code>, and linked from <code>/usr/bin/java</code>.</p>

<p>You may delete the archive file that you downloaded earlier:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">rm ~/jdk-8u*-linux-x64.rpm
</li></ul></code></pre>
<p>Now that Java 8 is installed, let's install ElasticSearch.</p>

<h2 id="install-elasticsearch">Install Elasticsearch</h2>

<p>Elasticsearch can be installed with a package manager by adding Elastic's package repository.</p>

<p>Run the following command to import the Elasticsearch public GPG key into rpm:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo rpm --import http://packages.elastic.co/GPG-KEY-elasticsearch
</li></ul></code></pre>
<p>Create a new yum repository file for Elasticsearch. Note that this is a single command:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">echo '[elasticsearch-2.x]
</li><li class="line" prefix="$">name=Elasticsearch repository for 2.x packages
</li><li class="line" prefix="$">baseurl=http://packages.elastic.co/elasticsearch/2.x/centos
</li><li class="line" prefix="$">gpgcheck=1
</li><li class="line" prefix="$">gpgkey=http://packages.elastic.co/GPG-KEY-elasticsearch
</li><li class="line" prefix="$">enabled=1
</li><li class="line" prefix="$">' | sudo tee /etc/yum.repos.d/elasticsearch.repo
</li></ul></code></pre>
<p>Install Elasticsearch with this command:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo yum -y install elasticsearch
</li></ul></code></pre>
<p>Elasticsearch is now installed.  Let's edit the configuration:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo vi /etc/elasticsearch/elasticsearch.yml
</li></ul></code></pre>
<p>You will want to restrict outside access to your Elasticsearch instance (port 9200), so outsiders can't read your data or shutdown your Elasticsearch cluster through the HTTP API. Find the line that specifies <code>network.host</code>, uncomment it, and replace its value with "localhost" so it looks like this:</p>
<div class="code-label " title="elasticsearch.yml excerpt (updated)">elasticsearch.yml excerpt (updated)</div><pre class="code-pre "><code langs="">network.host: localhost
</code></pre>
<p>Save and exit <code>elasticsearch.yml</code>.</p>

<p>Now start Elasticsearch:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo systemctl start elasticsearch
</li></ul></code></pre>
<p>Then run the following command to start Elasticsearch automatically on boot up:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo systemctl enable elasticsearch
</li></ul></code></pre>
<p>Now that Elasticsearch is up and running, let's install Kibana.</p>

<h2 id="install-kibana">Install Kibana</h2>

<p>The Kibana package shares the same GPG Key as Elasticsearch, and we already installed that public key.</p>

<p>Create and edit a new yum repository file for Kibana:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo vi /etc/yum.repos.d/kibana.repo
</li></ul></code></pre>
<p>Add the following repository configuration:</p>
<div class="code-label " title="/etc/yum.repos.d/kibana.repo">/etc/yum.repos.d/kibana.repo</div><pre class="code-pre line_numbers"><code langs=""><ul class="prefixed"><li class="line" prefix="1">[kibana-4.4]
</li><li class="line" prefix="2">name=Kibana repository for 4.4.x packages
</li><li class="line" prefix="3">baseurl=http://packages.elastic.co/kibana/4.4/centos
</li><li class="line" prefix="4">gpgcheck=1
</li><li class="line" prefix="5">gpgkey=http://packages.elastic.co/GPG-KEY-elasticsearch
</li><li class="line" prefix="6">enabled=1
</li></ul></code></pre>
<p>Save and exit.</p>

<p>Install Kibana with this command:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo yum -y install kibana
</li></ul></code></pre>
<p>Open the Kibana configuration file for editing:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo vi /opt/kibana/config/kibana.yml
</li></ul></code></pre>
<p>In the Kibana configuration file, find the line that specifies <code>server.host</code>, and replace the IP address ("0.0.0.0" by default) with "localhost":</p>
<div class="code-label " title="kibana.yml excerpt (updated)">kibana.yml excerpt (updated)</div><pre class="code-pre "><code langs="">server.host: "localhost"
</code></pre>
<p>Save and exit. This setting makes it so Kibana will only be accessible to the localhost. This is fine because we will install an Nginx reverse proxy, on the same server, to allow external access.</p>

<p>Now start the Kibana service, and enable it:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo systemctl start kibana
</li><li class="line" prefix="$">sudo chkconfig kibana on
</li></ul></code></pre>
<p>Before we can use the Kibana web interface, we have to set up a reverse proxy. Let's do that now, with Nginx.</p>

<h2 id="install-nginx">Install Nginx</h2>

<p>Because we configured Kibana to listen on <code>localhost</code>, we must set up a reverse proxy to allow external access to it. We will use Nginx for this purpose.</p>

<p><strong>Note:</strong> If you already have an Nginx instance that you want to use, feel free to use that instead. Just make sure to configure Kibana so it is reachable by your Nginx server (you probably want to change the <code>host</code> value, in <code>/opt/kibana/config/kibana.yml</code>, to your Kibana server's private IP address). Also, it is recommended that you enable SSL/TLS.</p>

<p>Add the EPEL repository to yum:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo yum -y install epel-release
</li></ul></code></pre>
<p>Now use yum to install Nginx and httpd-tools:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo yum -y install nginx httpd-tools
</li></ul></code></pre>
<p>Use htpasswd to create an admin user, called "kibanaadmin" (you should use another name), that can access the Kibana web interface:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo htpasswd -c /etc/nginx/htpasswd.users <span class="highlight">kibanaadmin</span>
</li></ul></code></pre>
<p>Enter a password at the prompt. Remember this login, as you will need it to access the Kibana web interface.</p>

<p>Now open the Nginx configuration file in your favorite editor. We will use vi:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo vi /etc/nginx/nginx.conf
</li></ul></code></pre>
<p>Find the default server block (starts with <code>server {</code>), the last configuration block in the file, and delete it. When you are done, the last two lines in the file should look like this:</p>
<div class="code-label " title="nginx.conf excerpt">nginx.conf excerpt</div><pre class="code-pre "><code langs="">    include /etc/nginx/conf.d/*.conf;
}
</code></pre>
<p>Save and exit.</p>

<p>Now we will create an Nginx server block in a new file:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo vi /etc/nginx/conf.d/kibana.conf
</li></ul></code></pre>
<p>Paste the following code block into the file. Be sure to update the <code>server_name</code> to match your server's name:</p>
<div class="code-label " title="/etc/nginx/conf.d/kibana.conf">/etc/nginx/conf.d/kibana.conf</div><pre class="code-pre line_numbers"><code langs=""><ul class="prefixed"><li class="line" prefix="1">server {
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

<p>Now start and enable Nginx to put our changes into effect:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo systemctl start nginx
</li><li class="line" prefix="$">sudo systemctl enable nginx
</li></ul></code></pre>
<p><span class="note"><strong>Note:</strong> This tutorial assumes that SELinux is disabled. If this is not the case, you may need to run the following command for Kibana to work properly: <code>sudo setsebool -P httpd_can_network_connect 1</code><br /></span></p>

<p>Kibana is now accessible via your FQDN or the public IP address of your ELK Server i.e. http://elk_server_public_ip/. If you go there in a web browser, after entering the "kibanaadmin" credentials, you should see a Kibana welcome page which will ask you to configure an index pattern. Let's get back to that later, after we install all of the other components.</p>

<h2 id="install-logstash">Install Logstash</h2>

<p>The Logstash package shares the same GPG Key as Elasticsearch, and we already installed that public key, so let's create and edit a new Yum repository file for Logstash:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo vi /etc/yum.repos.d/logstash.repo
</li></ul></code></pre>
<p>Add the following repository configuration:</p>
<div class="code-label " title="/etc/yum.repos.d/logstash.repo">/etc/yum.repos.d/logstash.repo</div><pre class="code-pre line_numbers"><code langs=""><ul class="prefixed"><li class="line" prefix="1">[logstash-2.2]
</li><li class="line" prefix="2">name=logstash repository for 2.2 packages
</li><li class="line" prefix="3">baseurl=http://packages.elasticsearch.org/logstash/2.2/centos
</li><li class="line" prefix="4">gpgcheck=1
</li><li class="line" prefix="5">gpgkey=http://packages.elasticsearch.org/GPG-KEY-elasticsearch
</li><li class="line" prefix="6">enabled=1
</li></ul></code></pre>
<p>Save and exit.</p>

<p>Install Logstash with this command:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo yum -y install logstash
</li></ul></code></pre>
<p>Logstash is installed but it is not configured yet.</p>

<h2 id="generate-ssl-certificates">Generate SSL Certificates</h2>

<p>Since we are going to use Filebeat to ship logs from our Client Servers to our ELK Server, we need to create an SSL certificate and key pair. The certificate is used by Filebeat to verify the identity of ELK Server. Create the directories that will store the certificate and private key with the following commands:</p>

<p>Now you have two options for generating your SSL certificates. If you have a DNS setup that will allow your client servers to resolve the IP address of the ELK Server,  use <strong>Option 2</strong>. Otherwise, <strong>Option 1</strong> will allow you to use IP addresses.</p>

<h3 id="option-1-ip-address">Option 1: IP Address</h3>

<p>If you don't have a DNS setup—that would allow your servers, that you will gather logs from, to resolve the IP address of your ELK Server—you will have to add your ELK Server's private IP address to the <code>subjectAltName</code> (SAN) field of the SSL certificate that we are about to generate. To do so, open the OpenSSL configuration file:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo vi /etc/pki/tls/openssl.cnf
</li></ul></code></pre>
<p>Find the <code>[ v3_ca ]</code> section in the file, and add this line under it (substituting in the ELK Server's private IP address):</p>
<div class="code-label " title="openssl.cnf excerpt">openssl.cnf excerpt</div><pre class="code-pre line_numbers"><code langs=""><ul class="prefixed"><li class="line" prefix="1">subjectAltName = IP: <span class="highlight">ELK_server_private_ip</span>
</li></ul></code></pre>
<p>Save and exit.</p>

<p>Now generate the SSL certificate and private key in the appropriate locations (/etc/pki/tls/), with the following commands:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">cd /etc/pki/tls
</li><li class="line" prefix="$">sudo openssl req -config /etc/pki/tls/openssl.cnf -x509 -days 3650 -batch -nodes -newkey rsa:2048 -keyout private/logstash-forwarder.key -out certs/logstash-forwarder.crt
</li></ul></code></pre>
<p>The <em>logstash-forwarder.crt</em> file will be copied to all of the servers that will send logs to Logstash but we will do that a little later. Let's complete our Logstash configuration. If you went with this option, skip option 2 and move on to <strong>Configure Logstash</strong>.</p>

<h3 id="option-2-fqdn-dns">Option 2: FQDN (DNS)</h3>

<p>If you have a DNS setup with your private networking, you should create an A record that contains the ELK Server's private IP address—this domain name will be used in the next command, to generate the SSL certificate. Alternatively, you can use a record that points to the server's public IP address. Just be sure that your servers (the ones that you will be gathering logs from) will be able to resolve the domain name to your ELK Server.</p>

<p>Now generate the SSL certificate and private key, in the appropriate locations (/etc/pki/tls/...), with the following command (substitute in the FQDN of the ELK Server):</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">cd /etc/pki/tls
</li><li class="line" prefix="$">sudo openssl req -subj '/CN=<span class="highlight">ELK_server_fqdn</span>/' -x509 -days 3650 -batch -nodes -newkey rsa:2048 -keyout private/logstash-forwarder.key -out certs/logstash-forwarder.crt
</li></ul></code></pre>
<p>The <em>logstash-forwarder.crt</em> file will be copied to all of the servers that will send logs to Logstash but we will do that a little later. Let's complete our Logstash configuration.</p>

<h2 id="configure-logstash">Configure Logstash</h2>

<p>Logstash configuration files are in the JSON-format, and reside in /etc/logstash/conf.d. The configuration consists of three sections: inputs, filters, and outputs.</p>

<p>Let's create a configuration file called <code>02-beats-input.conf</code> and set up our "filebeat" input:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo vi /etc/logstash/conf.d/02-beats-input.conf
</li></ul></code></pre>
<p>Insert the following <strong>input</strong> configuration:</p>
<div class="code-label " title="02-beats-input.conf">02-beats-input.conf</div><pre class="code-pre line_numbers"><code langs=""><ul class="prefixed"><li class="line" prefix="1">input {
</li><li class="line" prefix="2">  beats {
</li><li class="line" prefix="3">    port => 5044
</li><li class="line" prefix="4">    ssl => true
</li><li class="line" prefix="5">    ssl_certificate => "/etc/pki/tls/certs/logstash-forwarder.crt"
</li><li class="line" prefix="6">    ssl_key => "/etc/pki/tls/private/logstash-forwarder.key"
</li><li class="line" prefix="7">  }
</li><li class="line" prefix="8">}
</li></ul></code></pre>
<p>Save and quit. This specifies a <code>beats</code> input that will listen on tcp port <code>5044</code>, and it will use the SSL certificate and private key that we created earlier.</p>

<p>Now let's create a configuration file called <code>10-syslog-filter.conf</code>, where we will add a filter for syslog messages:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo vi /etc/logstash/conf.d/10-syslog-filter.conf
</li></ul></code></pre>
<p>Insert the following syslog <strong>filter</strong> configuration:</p>
<div class="code-label " title="10-syslog-filter.conf">10-syslog-filter.conf</div><pre class="code-pre line_numbers"><code langs=""><ul class="prefixed"><li class="line" prefix="1">filter {
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
<p>Save and quit. This filter looks for logs that are labeled as "syslog" type (by Filebeat), and it will try to use <code>grok</code> to parse incoming syslog logs to make it structured and query-able.</p>

<p>Lastly, we will create a configuration file called <code>30-elasticsearch-output.conf</code>:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo vi /etc/logstash/conf.d/30-elasticsearch-output.conf
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
<p>Save and exit. This output basically configures Logstash to store the beats data in Elasticsearch which is running at <code>localhost:9200</code>, in an index named after the beat used (filebeat, in our case).</p>

<p>If you want to add filters for other applications that use the Filebeat input, be sure to name the files so they sort between the input and the output configuration (i.e. between 02- and 30-).</p>

<p>Test your Logstash configuration with this command:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo service logstash configtest
</li></ul></code></pre>
<p>It should display <code>Configuration OK</code> if there are no syntax errors. Otherwise, try and read the error output to see what's wrong with your Logstash configuration.</p>

<p>Restart and enable Logstash to put our configuration changes into effect:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo systemctl restart logstash
</li><li class="line" prefix="$">sudo chkconfig logstash on
</li></ul></code></pre>
<p>Next, we'll load the sample Kibana dashboards.</p>

<h2 id="load-kibana-dashboards">Load Kibana Dashboards</h2>

<p>Elastic provides several sample Kibana dashboards and Beats index patterns that can help you get started with Kibana. Although we won't use the dashboards in this tutorial,  we'll load them anyway so we can use the Filebeat index pattern that it includes.</p>

<p>First, download the sample dashboards archive to your home directory:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">cd ~
</li><li class="line" prefix="$">curl -L -O https://download.elastic.co/beats/dashboards/beats-dashboards-1.1.0.zip
</li></ul></code></pre>
<p>Install the <code>unzip</code> package with this command:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo yum -y install unzip
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
<li>[packetbeat-]YYYY.MM.DD</li>
<li>[topbeat-]YYYY.MM.DD</li>
<li>[filebeat-]YYYY.MM.DD</li>
<li>[winlogbeat-]YYYY.MM.DD</li>
</ul>

<p>When we start using Kibana, we will select the Filebeat index pattern as our default.</p>

<h2 id="load-filebeat-index-template-in-elasticsearch">Load Filebeat Index Template in Elasticsearch</h2>

<p>Because we are planning on using Filebeat to ship logs to Elasticsearch, we should load a Filebeat index template. The index template will configure Elasticsearch to analyze incoming Filebeat fields in an intelligent way.</p>

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

<p>Do these steps for each <strong>CentOS or RHEL 7</strong> server that you want to send logs to your ELK Server. For instructions on installing Filebeat on Debian-based Linux distributions (e.g. Ubuntu, Debian, etc.), refer to the <a href="https://indiareads/community/tutorials/how-to-install-elasticsearch-logstash-and-kibana-elk-stack-on-ubuntu-14-04#set-up-filebeat(add-client-servers)">Set Up Filebeat (Add Client Servers) section</a> of the Ubuntu variation of this tutorial.</p>

<h3 id="copy-ssl-certificate">Copy SSL Certificate</h3>

<p>On your <strong>ELK Server</strong>, copy the SSL certificate—created in the prerequisite tutorial—to your <strong>Client Server</strong> (substitute the client server's address, and your own login):</p>
<pre class="code-pre custom_prefix"><code langs=""><ul class="prefixed"><li class="line" prefix="elk$">scp /etc/pki/tls/certs/logstash-forwarder.crt <span class="highlight">user</span>@<span class="highlight">client_server_private_address</span>:/tmp
</li></ul></code></pre>
<p>After providing your login's credentials, ensure that the certificate copy was successful. It is required for communication between the client servers and the ELK Server.</p>

<p>Now, on your <strong>Client Server</strong>, copy the ELK Server's SSL certificate into the appropriate location (/etc/pki/tls/certs):</p>
<pre class="code-pre custom_prefix"><code langs=""><ul class="prefixed"><li class="line" prefix="client$">sudo mkdir -p /etc/pki/tls/certs
</li><li class="line" prefix="client$">sudo cp /tmp/logstash-forwarder.crt /etc/pki/tls/certs/
</li></ul></code></pre>
<p>Now we will install the Topbeat package.</p>

<h3 id="install-filebeat-package">Install Filebeat Package</h3>

<p>On <strong>Client Server</strong>, create run the following command to import the Elasticsearch public GPG key into rpm:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo rpm --import http://packages.elastic.co/GPG-KEY-elasticsearch
</li></ul></code></pre>
<p>Create and edit a new yum repository file for Filebeat:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo vi /etc/yum.repos.d/elastic-beats.repo
</li></ul></code></pre>
<p>Add the following repository configuration:</p>
<div class="code-label " title="/etc/yum.repos.d/elastic-beats.repo">/etc/yum.repos.d/elastic-beats.repo</div><pre class="code-pre line_numbers"><code langs=""><ul class="prefixed"><li class="line" prefix="1">[beats]
</li><li class="line" prefix="2">name=Elastic Beats Repository
</li><li class="line" prefix="3">baseurl=https://packages.elastic.co/beats/yum/el/$basearch
</li><li class="line" prefix="4">enabled=1
</li><li class="line" prefix="5">gpgkey=https://packages.elastic.co/GPG-KEY-elasticsearch
</li><li class="line" prefix="6">gpgcheck=1
</li></ul></code></pre>
<p>Save and exit.</p>

<p>Install Filebeat with this command:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo yum -y install filebeat
</li></ul></code></pre>
<p>Filebeat is installed but it is not configured yet.</p>

<h3 id="configure-filebeat">Configure Filebeat</h3>

<p>Now we will configure Filebeat to connect to Logstash on our ELK Server. This section will step you through modifying the example configuration file that comes with Filebeat. When you complete the steps, you should have a file that looks something like <a href="https://gist.githubusercontent.com/thisismitch/3429023e8438cc25b86c/raw/de660ffdd3decacdcaf88109e5683e1eef75c01f/filebeat.yml-centos">this</a>.</p>

<p>On <strong>Client Server</strong>, create and edit Filebeat configuration file:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo vi /etc/filebeat/filebeat.yml
</li></ul></code></pre>
<p><span class="note"><strong>Note:</strong> Filebeat's configuration file is in YAML format, which means that indentation is very important! Be sure to use the same number of spaces that are indicated in these instructions.<br /></span></p>

<p>Near the top of the file, you will see the <code>prospectors</code> section, which is where you can define <strong>prospectors</strong> that specify which log files should be shipped and how they should be handled. Each prospector is indicated by the <code>-</code> character. </p>

<p>We'll modify the existing prospector to send <code>secure</code> and <code>messages</code> logs to Logstash. Under <code>paths</code>, comment out the <code>- /var/log/*.log</code> file. This will prevent Filebeat from sending every <code>.log</code> in that directory to Logstash. Then add new entries for <code>syslog</code> and <code>auth.log</code>. It should look something like this when you're done:</p>
<div class="code-label " title="filebeat.yml excerpt 1 of 5">filebeat.yml excerpt 1 of 5</div><pre class="code-pre "><code langs="">...
      paths:
<span class="highlight">        - /var/log/secure</span>
<span class="highlight">        - /var/log/messages</span>
<span class="highlight">#</span>        - /var/log/*.log
...
</code></pre>
<p>Then find the line that specifies <code>document_type:</code>, uncomment it and change its value to "syslog". It should look like this after the modification:</p>
<div class="code-label " title="filebeat.yml excerpt 2 of 5">filebeat.yml excerpt 2 of 5</div><pre class="code-pre "><code langs="">...
      document_type: <span class="highlight">syslog</span>
...
</code></pre>
<p>This specifies that the logs in this prospector are of type <strong>syslog</strong> (which is the type that our Logstash filter is looking for).</p>

<p>If you want to send other files to your ELK server, or make any changes to how Filebeat handles your logs, feel free to modify or add prospector entries.</p>

<p>Next, under the <code>output</code> section, find the line that says <code>elasticsearch:</code>, which indicates the Elasticsearch output section (which we are not going to use). <strong>Delete or comment out the entire Elasticsearch output section</strong> (up to the line that says <code>logstash:</code>).</p>

<p>Find the commented out Logstash output section, indicated by the line that says <code>#logstash:</code>, and uncomment it by deleting the preceding <code>#</code>. In this section, uncomment the <code>hosts: ["localhost:5044"]</code> line. Change <code>localhost</code> to the private IP address (or hostname, if you went with that option) of your ELK server:</p>
<div class="code-label " title="filebeat.yml excerpt 3 of 5">filebeat.yml excerpt 3 of 5</div><pre class="code-pre "><code langs="">  ### Logstash as output
  logstash:
    # The Logstash hosts
    hosts: ["<span class="highlight">ELK_server_private_IP</span>:5044"]
</code></pre>
<p>This configures Filebeat to connect to Logstash on your ELK Server at port 5044 (the port that we specified an input for earlier).</p>

<p>Directly under the <code>hosts</code> entry, and with the same indentation, add this line:</p>
<div class="code-label " title="filebeat.yml excerpt 4 of 5">filebeat.yml excerpt 4 of 5</div><pre class="code-pre "><code langs="">    bulk_max_size: 1024
</code></pre>
<p>Next, find the <code>tls</code> section, and uncomment it. Then uncomment the line that specifies <code>certificate_authorities</code>, and change its value to <code>["/etc/pki/tls/certs/logstash-forwarder.crt"]</code>. It should look something like this:</p>
<div class="code-label " title="filebeat.yml excerpt 5 of 5">filebeat.yml excerpt 5 of 5</div><pre class="code-pre "><code langs="">...
<span class="highlight">    tls:</span>
      # List of root certificates for HTTPS server verifications
      <span class="highlight">certificate_authorities: ["/etc/pki/tls/certs/logstash-forwarder.crt"]</span>
</code></pre>
<p>This configures Filebeat to use the SSL certificate that we created on the ELK Server.</p>

<p>Save and quit. </p>

<p>Now start and enable Filebeat to put our changes into place:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo systemctl start filebeat
</li><li class="line" prefix="$">sudo systemctl enable filebeat
</li></ul></code></pre>
<p>Again, if you're not sure if your Filebeat configuration is correct, compare it against this <a href="https://gist.githubusercontent.com/thisismitch/3429023e8438cc25b86c/raw/de660ffdd3decacdcaf88109e5683e1eef75c01f/filebeat.yml-centos">example Filebeat configuration</a>.</p>

<p>Now Filebeat is sending your syslog <code>messages</code> and <code>secure</code> files to your ELK Server! Repeat this section for all of the other servers that you wish to gather logs for.</p>

<h2 id="test-filebeat-installation">Test Filebeat Installation</h2>

<p>If your ELK stack is setup properly, Filebeat (on your client server) should be shipping your logs to Logstash on your ELK server. Logstash should be loading the Filebeat data into Elasticsearch in an date-stamped index, <code>filebeat-YYYY.MM.DD</code>.</p>

<p>On your <strong>ELK Server</strong>, verify that Elasticsearch is indeed receiving the data by querying for the Filebeat index with this command:</p>
<pre class="code-pre custom_prefix"><code langs=""><ul class="prefixed"><li class="line" prefix="ELK$">curl -XGET 'http://localhost:9200/filebeat-*/_search?pretty'
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
<p>If your output shows 0 total hits, Elasticsearch is not loading any logs under the index you searched for, and you should review your setup for errors. If you received the expected output, continue to the next step.</p>

<h2 id="connect-to-kibana">Connect to Kibana</h2>

<p>When you are finished setting up Filebeat on all of the servers that you want to gather logs for, let's look at Kibana, the web interface that we installed earlier.</p>

<p>In a web browser, go to the FQDN or public IP address of your ELK Server. After entering the "kibanaadmin" credentials, you should see a page prompting you to configure a default index pattern:</p>

<p><img src="https://assets.digitalocean.com/articles/elk/1-filebeat-index.gif" alt="Create index" /></p>

<p>Go ahead and select <strong>[filebeat]-YYY.MM.DD</strong> from the Index Patterns menu (left side), then click the <strong>Star (Set as default index)</strong> button to set the Filebeat index as the default.</p>

<p>Now click the <strong>Discover</strong> link in the top navigation bar. By default, this will show you all of the log data over the last 15 minutes. You should see a histogram with log events, with log messages below:</p>

<p><img src="https://assets.digitalocean.com/articles/elk/2-filebeat-discover.png" alt="Discover page" /></p>

<p>Right now, there won't be much in there because you are only gathering syslogs from your client servers. Here, you can search and browse through your logs. You can also customize your dashboard.</p>

<p>Try the following things:</p>

<ul>
<li>Search for "root" to see if anyone is trying to log into your servers as root</li>
<li>Search for a particular hostname (search for <code>host: "<span class="highlight">hostname</span>"</code>)</li>
<li>Change the time frame by selecting an area on the histogram or from the menu above</li>
<li>Click on messages below the histogram to see how the data is being filtered</li>
</ul>

<p>Kibana has many other features, such as graphing and filtering, so feel free to poke around!</p>

<h2 id="conclusion">Conclusion</h2>

<p>Now that your syslogs are centralized via Elasticsearch and Logstash, and you are able to visualize them with Kibana, you should be off to a good start with centralizing all of your important logs. Remember that you can send pretty much any type of log or indexed data to Logstash, but the data becomes even more useful if it is parsed and structured with grok.</p>

<p>To improve your new ELK stack, you should look into gathering and filtering your other logs with Logstash, and <a href="https://indiareads/community/tutorials/how-to-use-kibana-dashboards-and-visualizations">creating Kibana dashboards</a>. You may also want to <a href="https://indiareads/community/tutorials/how-to-gather-infrastructure-metrics-with-topbeat-and-elk-on-centos-7">gather system metrics by using Topbeat</a> with your ELK stack. All of these topics are covered in the other tutorials in this series.</p>

<p>Good luck!</p>

    