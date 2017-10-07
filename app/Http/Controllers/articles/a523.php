<link rel='stylesheet' href='digital_ocean.css'><br><img src=https://community-cdn-digitalocean-com.global.ssl.fastly.net/assets/tutorials/images/large/metrics_topbeat_elk_tw.jpg?1459451598/> <br> 
      <h3 id="introduction">Introduction</h3>

<p>Topbeat, which is one of the several "Beats" data shippers that helps send various types of server data to an Elasticsearch instance, allows you to gather information about the CPU, memory, and process activity on your servers. In conjunction with an ELK server (Elasticsearch, Logstash, and Kibana), the data that Topbeat gathers can be used to easily visualize metrics so that you can see the status of your servers in a centralized place. </p>

<p>In this tutorial, we will show you how to use an ELK stack to gather and visualize infrastructure metrics by using <strong>Topbeat</strong> on a CentOS 7 server.</p>

<h2 id="prerequisites">Prerequisites</h2>

<p>This tutorial assumes that you have the ELK Stack setup described in this tutorial: <a href="https://indiareads/community/tutorials/how-to-install-elasticsearch-logstash-and-kibana-elk-stack-on-centos-7">How To Install Elasticsearch, Logstash, and Kibana on CentOS 7</a>. If you do not already have an ELK server, please complete the linked tutorial before continuing.</p>

<p>We will also assume that, in addition to the ELK server, you have at least one client CentOS 7 server that you want to gather system metrics from by using Topbeat.</p>

<h2 id="load-kibana-dashboards-on-elk-server">Load Kibana Dashboards on ELK Server</h2>

<p><span class="note"><strong>Note:</strong>  This step is from the prerequisite tutorial but is also included here in case you skipped it while setting up your ELK stack. It is safe to load the sample dashboards multiple times.<br /></span></p>

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

<h2 id="load-topbeat-index-template-in-elasticsearch">Load Topbeat Index Template in Elasticsearch</h2>

<p>Because we are planning on using Topbeat to ship logs to Elasticsearch, we should load the Topbeat index template. The index template will configure Elasticsearch to analyze incoming Topbeat fields in an intelligent way.</p>

<p>First, download the Topbeat index template to your home directory:</p>
<pre class="code-pre custom_prefix"><code langs=""><ul class="prefixed"><li class="line" prefix="elk$">cd ~
</li><li class="line" prefix="elk$">curl -O https://raw.githubusercontent.com/elastic/topbeat/master/etc/topbeat.template.json
</li></ul></code></pre>
<p>Then load the template with this command:</p>
<pre class="code-pre custom_prefix"><code langs=""><ul class="prefixed"><li class="line" prefix="elk$">curl -XPUT 'http://localhost:9200/_template/topbeat' -d@topbeat.template.json
</li></ul></code></pre>
<p>Now your ELK server is ready to accept data from Topbeat. Let's set up Topbeat on a client server next.</p>

<h2 id="set-up-topbeat-add-client-servers">Set Up Topbeat (Add Client Servers)</h2>

<p>Do these steps for each CentOS or Red Hat-based server that you want to send metrics data to Logstash on your ELK Server. For instructions on installing Topbeat on Ubuntu or Debian Linux distributions, refer to the <a href="https://indiareads/community/tutorials/how-to-gather-infrastructure-metrics-with-topbeat-and-elk-on-ubuntu-14-04">Ubuntu variation of this tutorial</a>.</p>

<h3 id="copy-ssl-certificate">Copy SSL Certificate</h3>

<p><span class="note"><strong>Note:</strong>  This step is from the prerequisite tutorial but is also included here in case the client server you are setting up hasn't ever been connected to your ELK stack. You may skip this section if the client server already has the ELK server's SSL certificate in the appropriate place.<br /></span></p>

<p>On your <strong>ELK Server</strong>, copy the SSL certificate—created in the prerequisite tutorial—to your <strong>Client Server</strong> (substitute the client server's address, and your own login):</p>
<pre class="code-pre custom_prefix"><code langs=""><ul class="prefixed"><li class="line" prefix="elk$">scp /etc/pki/tls/certs/logstash-forwarder.crt <span class="highlight">user</span>@<span class="highlight">client_server_private_address</span>:/tmp
</li></ul></code></pre>
<p>After providing your login's credentials, ensure that the certificate copy was successful. It is required for communication between the client servers and the ELK Server.</p>

<p>Now, on your <strong>Client Server</strong>, copy the ELK Server's SSL certificate into the appropriate location (/etc/pki/tls/certs):</p>
<pre class="code-pre custom_prefix"><code langs=""><ul class="prefixed"><li class="line" prefix="client$">sudo mkdir -p /etc/pki/tls/certs
</li><li class="line" prefix="client$">sudo cp /tmp/logstash-forwarder.crt /etc/pki/tls/certs/
</li></ul></code></pre>
<p>Now we will install the Topbeat package.</p>

<h3 id="install-topbeat-package">Install Topbeat Package</h3>

<p>On <strong>Client Server</strong>, create run the following command to import the Elasticsearch public GPG key into rpm:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo rpm --import http://packages.elastic.co/GPG-KEY-elasticsearch
</li></ul></code></pre>
<p>Create and edit a new yum repository file for Filebeat:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo vi /etc/yum.repos.d/elastic-beats.repo
</li></ul></code></pre>
<p>Ensure that these lines exists (paste them in if they aren't already present):</p>
<div class="code-label " title="/etc/yum.repos.d/elastic-beats.repo">/etc/yum.repos.d/elastic-beats.repo</div><pre class="code-pre line_numbers"><code langs=""><ul class="prefixed"><li class="line" prefix="1">[beats]
</li><li class="line" prefix="2">name=Elastic Beats Repository
</li><li class="line" prefix="3">baseurl=https://packages.elastic.co/beats/yum/el/$basearch
</li><li class="line" prefix="4">enabled=1
</li><li class="line" prefix="5">gpgkey=https://packages.elastic.co/GPG-KEY-elasticsearch
</li><li class="line" prefix="6">gpgcheck=1
</li></ul></code></pre>
<p>Save and exit.</p>

<p>Install Topbeat with this command:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo yum -y install topbeat
</li></ul></code></pre>
<p>Topbeat is now installed but not yet configured.</p>

<h3 id="configure-topbeat">Configure Topbeat</h3>

<p>Now we will configure Topbeat to connect to Logstash on our ELK Server. This section will step you through modifying the example configuration file that comes with Topbeat. When you complete the steps, you should have a file that looks something like <a href="https://gist.github.com/thisismitch/0f2872d078a2c88cba4c">this</a>.</p>

<p>On <strong>Client Server</strong>, create and edit Topbeat configuration file:</p>
<pre class="code-pre custom_prefix"><code langs=""><ul class="prefixed"><li class="line" prefix="client$">sudo vi /etc/topbeat/topbeat.yml
</li></ul></code></pre>
<p><span class="note"><strong>Note:</strong> Topbeat's configuration file is in YAML format, which means that indentation is very important! Be sure to use the same number of spaces that are indicated in these instructions.<br /></span></p>

<p>Near the top of the file, you will see the <code>input</code> section, which is where you can specify which metrics and statistics should be sent to the ELK server. We'll use the default input settings, but feel free to change it to fit your needs.</p>

<p>Under the <code>output</code> section, find the line that says <code>elasticsearch:</code>, which indicates the Elasticsearch output section (which we are not going to use). <strong>Delete or comment out the entire Elasticsearch output section</strong> (up to the line that says <code>#logstash:</code>).</p>

<p>Find the commented out Logstash output section, indicated by the line that says <code>#logstash:</code>, and uncomment it by deleting the preceding <code>#</code>. In this section, uncomment the <code>hosts: ["localhost:5044"]</code> line. Change <code>localhost</code> to the private IP address (or hostname, if you went with that option) of your ELK server:</p>
<div class="code-label " title="topbeat.yml — 1 of 2">topbeat.yml — 1 of 2</div><pre class="code-pre "><code langs="">  ### Logstash as output
  logstash:
    # The Logstash hosts
    hosts: ["<span class="highlight">ELK_server_private_IP</span>:5044"]
</code></pre>
<p>This configures Topbeat to connect to Logstash on your ELK Server at port <code>5044</code> (the port that we specified a Logstash input for in the prerequisite tutorial).</p>

<p>Next, find the <code>tls</code> section, and uncomment it. Then uncomment the line that specifies <code>certificate_authorities</code>, and change its value to <code>["/etc/pki/tls/certs/logstash-forwarder.crt"]</code>. It should look something like this:</p>
<div class="code-label " title="topbeat.yml — 2 of 2">topbeat.yml — 2 of 2</div><pre class="code-pre "><code langs="">...
<span class="highlight">    tls:</span>
      # List of root certificates for HTTPS server verifications
      <span class="highlight">certificate_authorities: ["/etc/pki/tls/certs/logstash-forwarder.crt"]</span>
</code></pre>
<p>This configures Topbeat to use the SSL certificate that we created on the ELK Server in the prerequisite tutorial.</p>

<p>Save and quit.</p>

<p>Now restart Topbeat to put our changes into place:</p>
<pre class="code-pre custom_prefix"><code langs=""><ul class="prefixed"><li class="line" prefix="client$">sudo systemctl restart topbeat
</li><li class="line" prefix="client$">sudo systemctl enable topbeat
</li></ul></code></pre>
<p>Again, if you're not sure if your Topbeat configuration is correct, compare it against this <a href="https://gist.github.com/thisismitch/0f2872d078a2c88cba4c">example Topbeat configuration</a>.</p>

<p>Now Topbeat is sending your client server's system, processes, and filesystem metrics to your ELK server! Repeat this section for all of the other servers that you wish to Topbeat metrics for.</p>

<h2 id="test-topbeat-installation">Test Topbeat Installation</h2>

<p>If your ELK stack is setup properly, Topbeat (on your client server) should be shipping your logs to Logstash on your ELK server. Logstash should be loading the Topbeat data into Elasticsearch in an date-stamped index, <code>topbeat-YYYY.MM.DD</code>.</p>

<p>On your <strong>ELK Server</strong>, verify that Elasticsearch is indeed receiving the data by querying for the Topbeat index with this command:</p>
<pre class="code-pre custom_prefix"><code langs=""><ul class="prefixed"><li class="line" prefix="elk$">curl -XGET 'http://localhost:9200/topbeat-*/_search?pretty'
</li></ul></code></pre>
<p>You should see a bunch of output that looks like this:</p>
<pre class="code-pre "><code langs=""><div class="secondary-code-label " title="Sample Output:">Sample Output:</div>{
      "_index" : "topbeat-2016.02.01",
      "_type" : "process",
      "_id" : "AVKeLSdP4HKUFv4CjZ7K",
      "_score" : 1.0,
      "_source":{"@timestamp":"2016-02-01T18:51:43.937Z","beat":{"hostname":"topbeat-01","name":"topbeat-01"},"count":1,"proc":{"cpu":{"user":0,"user_p":0,"system":50,"total":50,"start_time":"12:54"},"mem":{"size":0,"rss":0,"rss_p":0,"share":0},"name":"jbd2/vda1-8","pid":125,"ppid":2,"state":"sleeping"},"type":"process","@version":"1","host":"topbeat-01"}
}
</code></pre>
<p>If your output shows 0 total hits, Elasticsearch is not loading any Topbeat data under the index you searched for, and you should review your setup for errors. If you received the expected output, continue to the next step.</p>

<h2 id="connect-to-kibana">Connect to Kibana</h2>

<p>When you are finished setting up Topbeat on all of the servers that you want to gather system stats for, let's look at Kibana.</p>

<p>In a web browser, go to the FQDN or public IP address of your ELK Server. After entering your ELK server's credentials, you should see your Kibana Discover page.</p>

<p>Go ahead and select <strong>[topbeat]-YYY.MM.DD</strong> from the Index Patterns menu (left side) to view your Topbeat data in the Discover view:</p>

<p><img src="https://assets.digitalocean.com/articles/elk/topbeat-index-select.gif" alt="Select Topbeat Index Pattern" /></p>

<p>Here, you can search and drill down your various Topbeat entries.</p>

<p>Next, you will want to check out the sample Topbeat dashboard that we loaded earlier. Click on <strong>Dashboard</strong> (top), then click the <strong>Load Saved Dashboard</strong> icon. Navigate to the second page of dashboards then click on <strong>Topbeat-Dashboard</strong>:</p>

<p><img src="https://assets.digitalocean.com/articles/elk/topbeat-view-dashboard.gif" alt="View Example Topbeat Dashboard" /></p>

<p>Here, you will see a variety of metrics that were gathered from your client servers that you installed Topbeat on.</p>

<h2 id="conclusion">Conclusion</h2>

<p>Now that your system metrics are centralized via Elasticsearch and Logstash, and you are able to visualize them with Kibana, you should be able to see what your servers are up to at a glance. Good luck!</p>

    