<link rel='stylesheet' href='digital_ocean.css'><br> <br> 
      <p><span class="note"><strong>Note:</strong> This tutorial is for an older version of the ELK stack, which is not compatible with the latest version. The latest version of this tutorial is available at <a href="https://indiareads/community/tutorials/how-to-install-elasticsearch-logstash-and-kibana-elk-stack-on-centos-7">How To Install Elasticsearch, Logstash, and Kibana (ELK Stack) on CentOS 7</a>.<br /></span></p>

<h3 id="introduction">Introduction</h3>

<p>In this tutorial, we will go over the installation of the Elasticsearch ELK Stack on CentOS 7—that is, Elasticsearch 1.7.3, Logstash 1.5.4, and Kibana 4.1.1. We will also show you how to configure it to gather and visualize the syslogs of your systems in a centralized location. Logstash is an open source tool for collecting, parsing, and storing logs for future use. Kibana is a web interface that can be used to search and view the logs that Logstash has indexed. Both of these tools are based on Elasticsearch.</p>

<p>Centralized logging can be very useful when attempting to identify problems with your servers or applications, as it allows you to search through all of your logs in a single place. It is also useful because it allows you to identify issues that span multiple servers by correlating their logs during a specific time frame.</p>

<p>It is possible to use Logstash to gather logs of all types, but we will limit the scope of this tutorial to syslog gathering.</p>

<h2 id="our-goal">Our Goal</h2>

<p>The goal of the tutorial is to set up Logstash to gather syslogs of multiple servers, and set up Kibana to visualize the gathered logs.</p>

<p>Our Logstash / Kibana setup has four main components:</p>

<ul>
<li><strong>Logstash</strong>: The server component of Logstash that processes incoming logs</li>
<li><strong>Elasticsearch</strong>: Stores all of the logs</li>
<li><strong>Kibana</strong>: Web interface for searching and visualizing logs, which will be proxied through Nginx</li>
<li><strong>Logstash Forwarder</strong>: Installed on servers that will send their logs to Logstash, Logstash Forwarder serves as a log forwarding agent that utilizes the <em>lumberjack</em> networking protocol to communicate with Logstash</li>
</ul>

<p><img src="https://assets.digitalocean.com/articles/elk/elk-infrastructure-logstashforwarder.png" alt="ELK Infrastructure" /></p>

<p>We will install the first three components on a single server, which we will refer to as our <strong>Logstash Server</strong>. The Logstash Forwarder will be installed on all of the client servers that we want to gather logs for, which we will refer to collectively as our <strong>Client Servers</strong>.</p>

<h2 id="prerequisites">Prerequisites</h2>

<p>To complete this tutorial, you will require root access to an CentOS 7 VPS. Instructions to set that up can be found here (steps 3 and 4): <a href="https://indiareads/community/tutorials/initial-server-setup-with-centos-7">Initial Server Setup with CentOS 7</a>.</p>

<p>If you would prefer to use Ubuntu instead, check out this tutorial: <a href="https://indiareads/community/tutorials/how-to-install-elasticsearch-logstash-and-kibana-4-on-ubuntu-14-04">How To Install ELK on Ubuntu 14.04</a>.</p>

<p>The amount of CPU, RAM, and storage that your Logstash Server will require depends on the volume of logs that you intend to gather. For this tutorial, we will be using a VPS with the following specs for our Logstash Server:</p>

<ul>
<li>OS: CentOS 7</li>
<li>RAM: 4GB</li>
<li>CPU: 2</li>
</ul>

<p>In addition to your Logstash Server, you will want to have a few other servers that you will gather logs from.</p>

<p>Let's get started on setting up our Logstash Server!</p>

<h2 id="install-java-8">Install Java 8</h2>

<p>Elasticsearch and Logstash require Java, so we will install that now. We will install a recent version of Oracle Java 8 because that is what Elasticsearch recommends. It should, however, work fine with OpenJDK, if you decide to go that route. Following the steps in this section means that you accept the Oracle Binary License Agreement for Java SE.</p>

<p>Change to your home directory and download the Oracle Java 8 (Update 65) JDK RPM with these commands:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">cd ~
</li><li class="line" prefix="$">wget --no-cookies --no-check-certificate --header "Cookie: gpw_e24=http%3A%2F%2Fwww.oracle.com%2F; oraclelicense=accept-securebackup-cookie" "http://download.oracle.com/otn-pub/java/jdk/8u65-b17/jdk-8u65-linux-x64.rpm"
</li></ul></code></pre>
<p>Then install the RPM with this yum command (if you downloaded a different release, substitute the filename here):</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo yum localinstall jdk-8u65-linux-x64.rpm
</li></ul></code></pre>
<p>Now Java should be installed at <code>/usr/java/jdk1.8.0_65/jre/bin/java</code>, and linked from /usr/bin/java.</p>

<p>You may delete the archive file that you downloaded earlier:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">rm ~/jdk-8u65-linux-x64.rpm
</li></ul></code></pre>
<p>Now that Java 8 is installed, let's install ElasticSearch.</p>

<h2 id="install-elasticsearch">Install Elasticsearch</h2>

<p>Elasticsearch can be installed with a package manager by adding Elastic's package repository.</p>

<p>Run the following command to import the Elasticsearch public GPG key into rpm:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo rpm --import http://packages.elastic.co/GPG-KEY-elasticsearch
</li></ul></code></pre>
<p>Create and edit a new yum repository file for Elasticsearch:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo vi /etc/yum.repos.d/elasticsearch.repo
</li></ul></code></pre>
<p>Add the following repository configuration:</p>
<div class="code-label " title="/etc/yum.repos.d/elasticsearch.repo">/etc/yum.repos.d/elasticsearch.repo</div><pre class="code-pre line_numbers"><code langs=""><ul class="prefixed"><li class="line" prefix="1">[elasticsearch-1.7]
</li><li class="line" prefix="2">name=Elasticsearch repository for 1.7.x packages
</li><li class="line" prefix="3">baseurl=http://packages.elastic.co/elasticsearch/1.7/centos
</li><li class="line" prefix="4">gpgcheck=1
</li><li class="line" prefix="5">gpgkey=http://packages.elastic.co/GPG-KEY-elasticsearch
</li><li class="line" prefix="6">enabled=1
</li></ul></code></pre>
<p>Save and exit.</p>

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

<p>The Kibana package shares the same GPG Key as Elasticsearch, and we already installed that public key, so let's create and edit a new Yum repository file for Kibana:</p>

<p>Create and edit a new yum repository file for Kibana:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo vi /etc/yum.repos.d/kibana.repo
</li></ul></code></pre>
<p>Add the following repository configuration:</p>
<div class="code-label " title="/etc/yum.repos.d/kibana.repo">/etc/yum.repos.d/kibana.repo</div><pre class="code-pre line_numbers"><code langs=""><ul class="prefixed"><li class="line" prefix="1">[kibana-4.1]
</li><li class="line" prefix="2">name=Kibana repository for 4.1.x packages
</li><li class="line" prefix="3">baseurl=http://packages.elastic.co/kibana/4.1/centos
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
<p>In the Kibana configuration file, find the line that specifies <code>host</code>, and replace the IP address ("0.0.0.0" by default) with "localhost":</p>
<div class="code-label " title="kibana.yml excerpt (updated)">kibana.yml excerpt (updated)</div><pre class="code-pre "><code langs="">host: "localhost"
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

<p>Kibana is now accessible via your FQDN or the public IP address of your Logstash Server i.e. http://logstash_server_public_ip/. If you go there in a web browser, after entering the "kibanaadmin" credentials, you should see a Kibana welcome page which will ask you to configure an index pattern. Let's get back to that later, after we install all of the other components.</p>

<h2 id="install-logstash">Install Logstash</h2>

<p>The Logstash package shares the same GPG Key as Elasticsearch, and we already installed that public key, so let's create and edit a new Yum repository file for Logstash:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo vi /etc/yum.repos.d/logstash.repo
</li></ul></code></pre>
<p>Add the following repository configuration:</p>
<div class="code-label " title="/etc/yum.repos.d/logstash.repo">/etc/yum.repos.d/logstash.repo</div><pre class="code-pre line_numbers"><code langs=""><ul class="prefixed"><li class="line" prefix="1">[logstash-1.5]
</li><li class="line" prefix="2">name=logstash repository for 1.5.x packages
</li><li class="line" prefix="3">baseurl=http://packages.elasticsearch.org/logstash/1.5/centos
</li><li class="line" prefix="4">gpgcheck=1
</li><li class="line" prefix="5">gpgkey=http://packages.elasticsearch.org/GPG-KEY-elasticsearch
</li><li class="line" prefix="6">enabled=1
</li></ul></code></pre>
<p>Save and exit.</p>

<p>Install Logstash 1.5 with this command:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo yum -y install logstash
</li></ul></code></pre>
<p>Logstash is installed but it is not configured yet.</p>

<h2 id="generate-ssl-certificates">Generate SSL Certificates</h2>

<p>Since we are going to use Logstash Forwarder to ship logs from our Servers to our Logstash Server, we need to create an SSL certificate and key pair. The certificate is used by the Logstash Forwarder to verify the identity of Logstash Server.</p>

<p>Now you have two options for generating your SSL certificates. If you have a DNS setup that will allow your client servers to resolve the IP address of the Logstash Server,  use <strong>Option 2</strong>. Otherwise, <strong>Option 1</strong> will allow you to use IP addresses.</p>

<h3 id="option-1-ip-address">Option 1: IP Address</h3>

<p>If you don't have a DNS setup—that would allow your servers, that you will gather logs from, to resolve the IP address of your Logstash Server—you will have to add your Logstash Server's private IP address to the <code>subjectAltName</code> (SAN) field of the SSL certificate that we are about to generate. To do so, open the OpenSSL configuration file:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo vi /etc/pki/tls/openssl.cnf
</li></ul></code></pre>
<p>Find the <code>[ v3_ca ]</code> section in the file, and add this line under it (substituting in the Logstash Server's private IP address):</p>
<div class="code-label " title="openssl.cnf excerpt">openssl.cnf excerpt</div><pre class="code-pre line_numbers"><code langs=""><ul class="prefixed"><li class="line" prefix="1">subjectAltName = IP: <span class="highlight">logstash_server_private_ip</span>
</li></ul></code></pre>
<p>Save and exit.</p>

<p>Now generate the SSL certificate and private key in the appropriate locations (/etc/pki/tls/), with the following commands:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">cd /etc/pki/tls
</li><li class="line" prefix="$">sudo openssl req -config /etc/pki/tls/openssl.cnf -x509 -days 3650 -batch -nodes -newkey rsa:2048 -keyout private/logstash-forwarder.key -out certs/logstash-forwarder.crt
</li></ul></code></pre>
<p>The <em>logstash-forwarder.crt</em> file will be copied to all of the servers that will send logs to Logstash but we will do that a little later. Let's complete our Logstash configuration. If you went with this option, skip option 2 and move on to <strong>Configure Logstash</strong>.</p>

<h3 id="option-2-fqdn-dns">Option 2: FQDN (DNS)</h3>

<p>If you have a DNS setup with your private networking, you should create an A record that contains the Logstash Server's private IP address—this domain name will be used in the next command, to generate the SSL certificate. Alternatively, you can use a record that points to the server's public IP address. Just be sure that your servers (the ones that you will be gathering logs from) will be able to resolve the domain name to your Logstash Server.</p>

<p>Now generate the SSL certificate and private key, in the appropriate locations (/etc/pki/tls/...), with the following command (substitute in the FQDN of the Logstash Server):</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">cd /etc/pki/tls
</li><li class="line" prefix="$">sudo openssl req -subj '/CN=<span class="highlight">logstash_server_fqdn</span>/' -x509 -days 3650 -batch -nodes -newkey rsa:2048 -keyout private/logstash-forwarder.key -out certs/logstash-forwarder.crt
</li></ul></code></pre>
<p>The <em>logstash-forwarder.crt</em> file will be copied to all of the servers that will send logs to Logstash but we will do that a little later. Let's complete our Logstash configuration.</p>

<h2 id="configure-logstash">Configure Logstash</h2>

<p>Logstash configuration files are in the JSON-format, and reside in /etc/logstash/conf.d. The configuration consists of three sections: inputs, filters, and outputs.</p>

<p>Let's create a configuration file called <code>01-lumberjack-input.conf</code> and set up our "lumberjack" input (the protocol that Logstash Forwarder uses):</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo vi /etc/logstash/conf.d/01-lumberjack-input.conf
</li></ul></code></pre>
<p>Insert the following <em>input</em> configuration:</p>
<div class="code-label " title="/etc/logstash/conf.d/01-lumberjack-input.conf">/etc/logstash/conf.d/01-lumberjack-input.conf</div><pre class="code-pre line_numbers"><code langs=""><ul class="prefixed"><li class="line" prefix="1">input {
</li><li class="line" prefix="2">  lumberjack {
</li><li class="line" prefix="3">    port => 5043
</li><li class="line" prefix="4">    type => "logs"
</li><li class="line" prefix="5">    ssl_certificate => "/etc/pki/tls/certs/logstash-forwarder.crt"
</li><li class="line" prefix="6">    ssl_key => "/etc/pki/tls/private/logstash-forwarder.key"
</li><li class="line" prefix="7">  }
</li><li class="line" prefix="8">}
</li></ul></code></pre>
<p>Save and quit. This specifies a <code>lumberjack</code> input that will listen on tcp port <code>5043</code>, and it will use the SSL certificate and private key that we created earlier.</p>

<p>Now let's create a configuration file called <code>10-syslog.conf</code>, where we will add a filter for syslog messages:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo vi /etc/logstash/conf.d/10-syslog.conf
</li></ul></code></pre>
<p>Insert the following syslog <em>filter</em> configuration:</p>
<div class="code-label " title="/etc/logstash/conf.d/10-syslog.conf">/etc/logstash/conf.d/10-syslog.conf</div><pre class="code-pre line_numbers"><code langs=""><ul class="prefixed"><li class="line" prefix="1">filter {
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
<p>Save and quit. This filter looks for logs that are labeled as "syslog" type (by a Logstash Forwarder), and it will try to use "grok" to parse incoming syslog logs to make it structured and query-able.</p>

<p>Lastly, we will create a configuration file called <code>30-lumberjack-output.conf</code>:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo vi /etc/logstash/conf.d/30-lumberjack-output.conf
</li></ul></code></pre>
<p>Insert the following <em>output</em> configuration:</p>
<div class="code-label " title="/etc/logstash/conf.d/30-lumberjack-output.conf">/etc/logstash/conf.d/30-lumberjack-output.conf</div><pre class="code-pre line_numbers"><code langs=""><ul class="prefixed"><li class="line" prefix="1">output {
</li><li class="line" prefix="2">  elasticsearch { host => localhost }
</li><li class="line" prefix="3">  stdout { codec => rubydebug }
</li><li class="line" prefix="4">}
</li></ul></code></pre>
<p>Save and exit. This output basically configures Logstash to store the logs in Elasticsearch.</p>

<p>With this configuration, Logstash will also accept logs that do not match the filter, but the data will not be structured (e.g. unfiltered Nginx or Apache logs would appear as flat messages instead of categorizing messages by HTTP response codes, source IP addresses, served files, etc.).</p>

<p>If you want to add filters for other applications that use the Logstash Forwarder input, be sure to name the files so they sort between the input and the output configuration (i.e. between 01- and 30-).</p>

<p>Restart and enable Logstash to put our configuration changes into effect:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo systemctl restart logstash
</li></ul></code></pre>
<p>Now that our Logstash Server is ready, let's move onto setting up Logstash Forwarder.</p>

<h2 id="set-up-logstash-forwarder-add-client-servers">Set Up Logstash Forwarder (Add Client Servers)</h2>

<p>Do these steps for each <strong>CentOS or RHEL 7</strong> server that you want to send logs to your Logstash Server. For instructions on installing Logstash Forwarder on Debian-based Linux distributions (e.g. Ubuntu, Debian, etc.), refer to the <a href="https://indiareads/community/tutorials/how-to-install-elasticsearch-logstash-and-kibana-4-on-ubuntu-14-04#set-up-logstash-forwarder-(add-client-servers)">Build and Package Logstash Forwarder section</a> of the Ubuntu variation of this tutorial.</p>

<h3 id="copy-ssl-certificate-and-logstash-forwarder-package">Copy SSL Certificate and Logstash Forwarder Package</h3>

<p>On <strong>Logstash Server</strong>, copy the SSL certificate to <strong>Client Server</strong> (substitute the client server's IP address, and your own login):</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">scp /etc/pki/tls/certs/logstash-forwarder.crt <span class="highlight">user</span>@<span class="highlight">server_private_IP</span>:/tmp
</li></ul></code></pre>
<p>After providing the login credentials, ensure that the certificate copy was successful. It is required for communication between the client servers and the Logstash server.</p>

<h3 id="install-logstash-forwarder-package">Install Logstash Forwarder Package</h3>

<p>On <strong>Client Server</strong>, create run the following command to import the Elasticsearch public GPG key into rpm:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo rpm --import http://packages.elastic.co/GPG-KEY-elasticsearch
</li></ul></code></pre>
<p>Create and edit a new yum repository file for Logstash Forwarder:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo vi /etc/yum.repos.d/logstash-forwarder.repo
</li></ul></code></pre>
<p>Add the following repository configuration:</p>
<div class="code-label " title="/etc/yum.repos.d/logstash-forwarder.repo">/etc/yum.repos.d/logstash-forwarder.repo</div><pre class="code-pre line_numbers"><code langs=""><ul class="prefixed"><li class="line" prefix="1">[logstash-forwarder]
</li><li class="line" prefix="2">name=logstash-forwarder repository
</li><li class="line" prefix="3">baseurl=http://packages.elastic.co/logstashforwarder/centos
</li><li class="line" prefix="4">gpgcheck=1
</li><li class="line" prefix="5">gpgkey=http://packages.elasticsearch.org/GPG-KEY-elasticsearch
</li><li class="line" prefix="6">enabled=1
</li></ul></code></pre>
<p>Save and exit.</p>

<p>Then install the Logstash Forwarder package:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo yum -y install logstash-forwarder
</li></ul></code></pre>
<p>Now copy the Logstash server's SSL certificate into the appropriate location (/etc/pki/tls/certs):</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo cp /tmp/logstash-forwarder.crt /etc/pki/tls/certs/
</li></ul></code></pre>
<h3 id="configure-logstash-forwarder">Configure Logstash Forwarder</h3>

<p>On <strong>Client Server</strong>, create and edit Logstash Forwarder configuration file, which is in JSON format:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo vi /etc/logstash-forwarder.conf
</li></ul></code></pre>
<p>Under the <code>network</code> section, add the following lines into the file, substituting in your Logstash Server's private IP address for <code>logstash_server_private_IP</code>:</p>
<div class="code-label " title="logstash-forwarder.conf excerpt 1 of 2">logstash-forwarder.conf excerpt 1 of 2</div><pre class="code-pre "><code langs="">    "servers": [ "<span class="highlight">logstash_server_private_IP</span>:5043" ],
    "ssl ca": "/etc/pki/tls/certs/logstash-forwarder.crt",
    "timeout": 15
</code></pre>
<p>Under the <code>files</code> section (between the square brackets), add the following lines, </p>
<div class="code-label " title="logstash-forwarder.conf excerpt 2 of 2">logstash-forwarder.conf excerpt 2 of 2</div><pre class="code-pre "><code langs="">    {
      "paths": [
        "/var/log/messages",
        "/var/log/secure"
       ],
      "fields": { "type": "syslog" }
    }
</code></pre>
<p>Save and quit. This configures Logstash Forwarder to connect to your Logstash Server on port 5043 (the port that we specified an input for earlier), and uses the SSL certificate that we created earlier. The <em>paths</em> section specifies which log files to send (here we specify <code>messages</code> and <code>secure</code>), and the <em>type</em> section specifies that these logs are of type "syslog* (which is the type that our filter is looking for).</p>

<p>Note that this is where you would add more files/types to configure Logstash Forwarder to other log files to Logstash on port 5043.</p>

<p>Now start and enable Logstash Forwarder to put our changes into place:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo systemctl start logstash-forwarder
</li><li class="line" prefix="$">sudo chkconfig logstash-forwarder on
</li></ul></code></pre>
<p>Now Logstash Forwarder is sending your syslog <code>messages</code> and <code>secure</code> files to your Logstash Server! Repeat this section for all of the other servers that you wish to gather logs for.</p>

<h2 id="connect-to-kibana">Connect to Kibana</h2>

<p>When you are finished setting up Logstash Forwarder on all of the servers that you want to gather logs for, let's look at Kibana, the web interface that we installed earlier.</p>

<p>In a web browser, go to the FQDN or public IP address of your Logstash Server. After entering the "kibanaadmin" credentials, you should see a page prompting you to configure an index pattern:</p>

<p><img src="https://assets.digitalocean.com/articles/elk/1-select-index.gif" alt="Create index" /></p>

<p>Go ahead and select <strong>@timestamp</strong> from the dropdown menu, then click the <strong>Create</strong> button to create the first index.</p>

<p>Now click the <strong>Discover</strong> link in the top navigation bar. By default, this will show you all of the log data over the last 15 minutes. You should see a histogram with log events, with log messages below:</p>

<p><img src="https://assets.digitalocean.com/articles/elk/2-discover.png" alt="Discover page" /></p>

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

<p>Now that your syslogs are centralized via Elasticsearch and Logstash, and you are able to visualize them with Kibana, you should be off to a good start with centralizing all of your important logs. Remember that you can send pretty much any type of log to Logstash, but the data becomes even more useful if it is parsed and structured with grok.</p>

<p>To improve your new ELK stack, you should look into gathering and filtering your other logs with Logstash, and creating Kibana dashboards. These topics are covered in the second and third tutorials in this series. Also, if you are having trouble with your setup, follow our <a href="https://indiareads/community/tutorials/how-to-troubleshoot-common-elk-stack-issues">How To Troubleshoot Common ELK Stack Issues</a> tutorial.</p>

<p>Scroll down for links to learn more about using your ELK stack!</p>

    