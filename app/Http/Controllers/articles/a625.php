<link rel='stylesheet' href='digital_ocean.css'><br> <br> 
      <h3 id="an-article-from-fluentd">An Article from <a href="https://www.fluentd.org/">Fluentd</a></h3>

<h2 id="overview">Overview</h2>

<p>Elasticsearch, Fluentd, and Kibana (EFK) allow you to collect, index, search, and visualize log data. This is a great alternative to the proprietary software Splunk, which lets you get started for free, but requires a paid license once the data volume increases.</p>

<p>This tutorial shows you how to build a log solution using three open source software components: <a href="http://www.elasticsearch.org">Elasticsearch</a>,  <a href="https://www.fluentd.org/">Fluentd</a> and <a href="http://www.kibana.org">Kibana</a>.</p>

<h3 id="prerequisites">Prerequisites</h3>

<ul>
<li>Droplet with <strong>Ubuntu 14.04</strong></li>
<li>User with <a href="https://indiareads/community/tutorials/how-to-add-and-delete-users-on-an-ubuntu-14-04-vps">sudo</a> privileges</li>
</ul>

<h2 id="installing-and-configuring-elasticsearch">Installing and Configuring Elasticsearch</h2>

<h3 id="getting-java">Getting Java</h3>

<p>Elasticsearch requires Java, so the first step is to install Java.</p>
<pre class="code-pre "><code langs="">sudo apt-get update
sudo apt-get install openjdk-7-jre-headless --yes
</code></pre>
<p>Check that Java was indeed installed. Run:</p>
<pre class="code-pre "><code langs="">java -version
</code></pre>
<p>The output should be as follows:</p>
<pre class="code-pre "><code langs="">java version "1.7.0_55"
OpenJDK Runtime Environment (IcedTea 2.4.7) (7u55-2.4.7-1ubuntu1)
OpenJDK 64-Bit Server VM (build 24.51-b03, mixed mode)
</code></pre>
<h3 id="getting-elasticsearch">Getting Elasticsearch</h3>

<p>Next, download and install Elasticsearch's deb package as follows.</p>
<pre class="code-pre "><code langs="">sudo wget https://download.elasticsearch.org/elasticsearch/elasticsearch/elasticsearch-1.2.2.deb
sudo dpkg -i elasticsearch-1.2.2.deb
</code></pre>
<h3 id="securing-elasticsearch">Securing Elasticsearch</h3>

<p>Up to version 1.2, Elasticsearch's dynamic scripting capability was enabled by default. Since this tutorial sets up the Kibana dashboard to be accessed from the public Internet, let's disable dynamic scripting by appending the following line at the end of <code>/etc/elasticsearch/elasticsearch.yml</code>:</p>
<pre class="code-pre "><code langs="">script.disable_dynamic: true
</code></pre>
<h3 id="starting-elasticsearch">Starting Elasticsearch</h3>

<p>Start running Elasticsearch with the following command.</p>
<pre class="code-pre "><code langs="">sudo service elasticsearch start
</code></pre>
<h2 id="installing-and-configuring-kibana">Installing and Configuring Kibana</h2>

<h3 id="getting-kibana">Getting Kibana</h3>

<p>Move to your home directory:</p>
<pre class="code-pre "><code langs="">cd ~
</code></pre>
<p>We will download Kibana as follows:</p>
<pre class="code-pre "><code langs="">curl -L https://download.elasticsearch.org/kibana/kibana/kibana-3.1.0.tar.gz | tar xzf -
sudo cp -r kibana-3.1.0 /usr/share/
</code></pre>
<h3 id="configuring-kibana">Configuring Kibana</h3>

<p>Since Kibana will use port 80 to talk to Elasticsearch as opposed to the default port 9200, Kibana's <code>config.js</code> must be updated.</p>

<p>Open <code>/usr/share/kibana-3.1.0/config.js</code> and look for the following line:</p>
<pre class="code-pre "><code langs="">elasticsearch: "http://"+window.location.hostname+":<span class="highlight">9200</span>",
</code></pre>
<p>and replace it with the following line:</p>
<pre class="code-pre "><code langs="">elasticsearch: "http://"+window.location.hostname+":<span class="highlight">80</span>",
</code></pre>
<h2 id="installing-and-configuring-nginx-proxy-server">Installing and Configuring Nginx (Proxy Server)</h2>

<p>We will use Nginx as a proxy server to allow access to the dashboard from the Public Internet (with basic authentication).</p>

<p>Install Nginx as follows:</p>
<pre class="code-pre "><code langs="">sudo apt-get install nginx --yes
</code></pre>
<p>Kibana provides a good default nginx.conf, which we will modify slightly.</p>

<p>First, install the configuration file as follows:</p>
<pre class="code-pre "><code langs="">wget https://assets.digitalocean.com/articles/fluentd/nginx.conf
sudo cp nginx.conf /etc/nginx/sites-available/default
</code></pre>
<p>Note: The original file is from this <a href="https://github.com/elasticsearch/kibana/raw/master/sample/nginx.conf">Kibana GitHub repository</a>.</p>

<p>Then, edit <code>/etc/nginx/sites-available/default</code> as follows (changes marked in red):</p>
<pre class="code-pre "><code langs="">#
# Nginx proxy for Elasticsearch + Kibana
#
# In this setup, we are password protecting the saving of dashboards. You may
# wish to extend the password protection to all paths.
#
# Even though these paths are being called as the result of an ajax request, the
# browser will prompt for a username/password on the first request
#
# If you use this, you'll want to point config.js at http://FQDN:80/ instead of
# http://FQDN:9200
#
server {
 listen                *:80 ;
 server_name           <span class="highlight">localhost</span>;
 access_log            <span class="highlight">/var/log/nginx/kibana.log</span>;
 location / {
   root  /usr/share/<span class="highlight">kibana-3.1.0</span>;
   index  index.html  index.htm;
 }
</code></pre>
<p>Finally, restart nginx as follows:</p>
<pre class="code-pre "><code langs="">$ sudo service nginx restart
</code></pre>
<p>Now, you should be able to see the generic Kibana dashboard at your server's IP address or domain, using your favorite browser.</p>

<p><img src="https://assets.digitalocean.com/articles/fluentd/kibana_welcome.png" alt="Kibana Welcome" /></p>

<h2 id="installing-and-configuring-fluentd">Installing and Configuring Fluentd</h2>

<p>Finally, let's install <a href="https://www.fluentd.org">Fluentd</a>. We will use td-agent, the packaged version of Fluentd, built and maintained by <a href="http://www.treasuredata.com">Treasure Data</a>.</p>

<h3 id="installing-fluentd-via-the-td-agent-package">Installing Fluentd via the td-agent package</h3>

<p>Install Fluentd with the following commands:</p>
<pre class="code-pre "><code langs="">wget http://packages.treasuredata.com/2/ubuntu/trusty/pool/contrib/t/td-agent/td-agent_2.0.4-0_amd64.deb
sudo dpkg -i td-agent_2.0.4-0_amd64.deb
</code></pre>
<h3 id="installing-plugins">Installing Plugins</h3>

<p>We need a couple of plugins:</p>

<ol>
<li>out_elasticsearch: this plugin lets Fluentd to stream data to Elasticsearch.</li>
<li>out<em>record</em>reformer: this plugin lets us process data into a more useful format.</li>
</ol>

<p>The following commands install both plugins (the first apt-get is for out_elasticsearch: it requires <code>make</code> and <code>libcurl</code>)</p>
<pre class="code-pre "><code langs="">sudo apt-get install make libcurl4-gnutls-dev --yes
sudo /opt/td-agent/embedded/bin/fluent-gem install fluent-plugin-elasticsearch
sudo /opt/td-agent/embedded/bin/fluent-gem install fluent-plugin-record-reformer
</code></pre>
<p>Next, we configure Fluentd to listen to syslog messages and send them to Elasticsearch. Open <code>/etc/td-agent/td-agent.conf</code> and add the following lines at the top of the file:</p>
<pre class="code-pre "><code langs=""><source>
 type syslog
 port 5140
 tag  system
</source>
<match system.*.*>
 type record_reformer
 tag elasticsearch
 facility ${tag_parts[1]}
 severity ${tag_parts[2]}
</match>
<match elasticsearch>
 type copy
 <store>
   type stdout
 </store>
 <store>
 type elasticsearch
 logstash_format true
 flush_interval 5s #debug
 </store>
</match>
</code></pre>
<h3 id="starting-fluentd">Starting Fluentd</h3>

<p>Start Fluentd with the following command:</p>
<pre class="code-pre "><code langs="">sudo service td-agent start
</code></pre>
<h2 id="forwarding-rsyslog-traffic-to-fluentd">Forwarding rsyslog Traffic to Fluentd</h2>

<p>Ubuntu 14.04 ships with rsyslogd. It needs to be reconfigured to forward syslog events to the port Fluentd listens to (port 5140 in this example).</p>

<p>Open <code>/etc/rsyslog.conf</code> (you need to <code>sudo</code>) and add the following line at the top</p>
<pre class="code-pre "><code langs="">*.* @127.0.0.1:5140
</code></pre>
<p>After saving and exiting the editor, restart rsyslogd as follows:</p>
<pre class="code-pre "><code langs="">sudo service rsyslog restart
</code></pre>
<h2 id="setting-up-kibana-dashboard-panels">Setting Up Kibana Dashboard Panels</h2>

<p>Kibana's default panels are very generic, so it's recommended to customize them. Here, we show two methods.</p>

<h3 id="method-1-using-a-template">Method 1: Using a Template</h3>

<p>The Fluentd team offers an alternative Kibana configuration that works with this setup better than the default one. To use this alternative configuration, run the following command:</p>
<pre class="code-pre "><code langs="">wget -O default.json https://assets.digitalocean.com/articles/fluentd/default.json
sudo cp default.json /usr/share/kibana-3.1.0/app/dashboards/default.json
</code></pre>
<p>Note: The original configuration file is from the author's <a href="https://bit.ly/fluentd-kibana">GitHub gist</a>.</p>

<p>If you refresh your Kibana dashboard home page at your server's URL, Kibana should now be configured to show histograms by syslog severity and facility, as well as recent log lines in a table.</p>

<h3 id="method-2-manually-configuring">Method 2: Manually Configuring</h3>

<p>Go to your server's IP address or domain to view the Kibana dashboard.</p>

<p><img src="https://assets.digitalocean.com/articles/fluentd/kibana_welcome.png" alt="Kibana Welcome" /></p>

<p>There are a couple of starter templates, but let's choose the blank one called <strong>Blank Dashboard: I'm comfortable configuring on my own</strong>, shown at the bottom of the welcome text.</p>

<p><img src="https://assets.digitalocean.com/articles/fluentd/kibana_blank.png" alt="Kibana Blank Template" /></p>

<p>Next, click on the <strong>+ ADD A ROW</strong> button on the right side of the dashboard. A configuration screen for a new row (a <strong>row</strong> consists of one or more panels) should show up. Enter a title, press the <strong>Create Row</strong> button, followed by <strong>Save</strong>. This creates a row.</p>

<p><img src="https://assets.digitalocean.com/articles/fluentd/kibana_row.png" alt="Kibana Row" /></p>

<p>When an empty row is created, Kibana shows the prompt <strong>Add panel to empty row</strong> on the left. Click this button. It takes you to the configuration screen to add a new panel. Choose <strong>histogram</strong> from the dropdown menu. A histogram is a time chart; for more information, see <a href="http://www.elasticsearch.org/guide/en/kibana/current/_histogram.html#_histogram">Kibana's documentation</a>.</p>

<p><img src="https://assets.digitalocean.com/articles/fluentd/kibana_histogram.png" alt="Kibana Histogram" /></p>

<p>There are many parameters to configure for a new histogram, but you can just scroll down and press the <strong>Save</strong> button. This creates a new panel.</p>

<p><img src="https://assets.digitalocean.com/articles/fluentd/kibana_histogram_details.png" alt="Kibana Histogram Details" /></p>

<h2 id="further-information">Further Information</h2>

<p>For further information about configuring Kibana, please see the <a href="http://www.elasticsearch.org/guide/en/kibana/current/">Kibana documentation page</a>.</p>

    