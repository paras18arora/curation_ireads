<link rel='stylesheet' href='digital_ocean.css'><br><img src=https://community-cdn-digitalocean-com.global.ssl.fastly.net/assets/tutorials/images/large/WebApplication.logging-twitter.png?1436558075/> <br> 
      <h3 id="introduction">Introduction</h3>

<p>We're finally ready to set up centralized logging for our production application setup. Centralized logging is a great way to gather and visualize the logs of your servers. Generally, setting up an elaborate logging system is not as important as having solid backups and monitoring set up, but it can be very useful when trying to identify trends or problems with your application.</p>

<p><img src="https://assets.digitalocean.com/articles/architecture/production/centralized_logging.png" alt="Centralized Logging Diagram" /></p>

<p>In this tutorial, we will set up an ELK stack (Elasticsearch, Logstash, and Kibana), and configure the servers that comprise our application to send their relevant logs to the logging server. We will also set up <a href="https://indiareads/community/tutorials/adding-logstash-filters-to-improve-centralized-logging">Logstash filters</a> that will parse and structure our logs which will allow us to easily search and filter them, and use them in Kibana visualizations.</p>

<h2 id="prerequisites">Prerequisites</h2>

<p>If you want to access your logging dashboard via a domain name, create an <strong>A Record</strong> under your domain, like "logging.example.com", that points to your <strong>logging</strong> server's public IP address. Alternatively, you can access the monitoring dashboard via the public IP address. It is advisable that you set up the logging web server to use HTTPS, and limit access to it by placing it behind a VPN.</p>

<h2 id="install-elk-on-logging-server">Install ELK on Logging Server</h2>

<p>Set up ELK on your <strong>logging</strong> server by following this tutorial: <a href="https://indiareads/community/tutorials/how-to-install-elasticsearch-logstash-and-kibana-4-on-ubuntu-14-04">How To Install Elasticsearch, Logstash, and Kibana 4 on Ubuntu 14.04</a>.</p>

<p>If you are using a private DNS for name resolution, be sure to follow <strong>Option 2</strong> in the <a href="https://indiareads/community/tutorials/how-to-install-elasticsearch-logstash-and-kibana-4-on-ubuntu-14-04#generate-ssl-certificates">Generate SSL Certificates section</a>.</p>

<p>Stop when you reach the <strong>Set Up Logstash Forwarder</strong> section.</p>

<h2 id="set-up-logstash-forwarder-on-clients">Set Up Logstash Forwarder on Clients</h2>

<p>Set up Logstash Forwarder, a log shipper, on your client servers, i.e. db1, app1, app2, and lb1, by following the <a href="https://indiareads/community/tutorials/how-to-install-elasticsearch-logstash-and-kibana-4-on-ubuntu-14-04#set-up-logstash-forwarder-(add-client-servers)">Set Up Logstash Forwarder section</a>, of the ELK tutorial.</p>

<p>When you are finished, you should be able to log into Kibana via the <strong>logging</strong> server's public network address, and view the syslogs of each of your servers.</p>

<h2 id="identify-logs-to-collect">Identify Logs to Collect</h2>

<p>Depending on your exact application and setup, different logs will be available to be collected into your ELK stack. In our case, we will collect the following logs:</p>

<ul>
<li>MySQL slow query logs (db1)</li>
<li>Apache access and error logs (app1 and app2)</li>
<li>HAProxy logs (lb1)</li>
</ul>

<p>We chose these logs because they can provide some useful information when troubleshooting or trying to identify trends. Your servers may have other logs that you want to gather, but this will help you get started.</p>

<h2 id="set-up-mysql-logs">Set Up MySQL Logs</h2>

<p>MySQL's slow query log is typically located at <code>/var/log/mysql/mysql-slow</code>. It consists of logs that take run long enough to be considered "slow queries", so identifying these queries can help you optimize or troubleshoot your application.</p>

<h3 id="enable-mysql-slow-query-log">Enable MySQL Slow Query Log</h3>

<p>The slow query log isn't enabled by default, so let's configure MySQL to log these types of queries.</p>

<p>Open your MySQL configuration file:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo vi /etc/mysql/my.cnf
</li></ul></code></pre>
<p>Find the commented "log<em>slow</em>queries" line, and uncomment it so it looks like this:</p>
<div class="code-label " title="/etc/mysql/my.cnf">/etc/mysql/my.cnf</div><pre class="code-pre "><code langs="">log_slow_queries        = /var/log/mysql/mysql-slow.log
</code></pre>
<p>Save and exit.</p>

<p>We need to restart MySQL to put the change into effect:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo service mysql restart
</li></ul></code></pre>
<p>Now MySQL will log its long running queries to the log file specified in the configuration.</p>

<h3 id="ship-mysql-log-files">Ship MySQL Log Files</h3>

<p>We must configure Logstash Forwarder to ship the MySQL slow query log to our logging server.</p>

<p>On your database server, db1, open the Logstash Forwarder configuration file:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo vi /etc/logstash-forwarder.conf
</li></ul></code></pre>
<p>Add the following, in the "files" section under the existing entries, to send the MySQL slow query logs as type "mysql-slow" to your Logstash server:</p>
<div class="code-label " title="logstash-forwarder.conf — MySQL slow query">logstash-forwarder.conf — MySQL slow query</div><pre class="code-pre "><code langs="">,
    {
      "paths": [
        "/var/log/mysql/mysql-slow.log"
       ],
      "fields": { "type": "mysql-slow" }
    }
</code></pre>
<p>Save and exit. This configures Logstash Forwarder to ship the MySQL slow query logs and mark them "mysql-slow" type logs, which will be used for filtering later.</p>

<p>Restart Logstash Forwarder to start shipping the logs: </p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo service logstash-forwarder restart
</li></ul></code></pre>
<h3 id="multiline-input-codec">Multiline Input Codec</h3>

<p>The MySQL slow query log is in a multiline format (i.e. each entry spans multiple lines), so we must enable Logstash's multiline codec to be able to process this type of log.</p>

<p>On the ELK server, <strong>logging</strong>, open the configuration file where your Lumberjack input is defined:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo vi /etc/logstash/conf.d/01-lumberjack-input.conf
</li></ul></code></pre>
<p>Within the <code>lumberjack</code> input definition, add these lines:</p>
<pre class="code-pre "><code langs="">    codec => multiline {
      pattern => "^# User@Host:"
      negate => true
      what => previous
    }
</code></pre>
<p>Save and exit. This configures Logstash to use the multiline log processor when it encounters logs that contain the specified pattern (i.e. starts with "# User@Host:").</p>

<p>Next, we will set up the Logstash filter for the MySQL logs.</p>

<h3 id="mysql-log-filter">MySQL Log Filter</h3>

<p>On the ELK server, <strong>logging</strong>, open a new file to add our MySQL log filters to Logstash. We will name it <code>11-mysql.conf</code>, so it will be read after the Logstash input configuration (in the <code>01-lumberjack-input.conf</code> file):</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo vi /etc/logstash/conf.d/11-mysql.conf
</li></ul></code></pre>
<p>Add the following filter definition:</p>
<div class="code-label " title="11-mysql.conf">11-mysql.conf</div><pre class="code-pre "><code langs="">filter {
  # Capture user, optional host and optional ip fields
  # sample log file lines:
  if [type] == "mysql-slow" {
    grok {
      match => [ "message", "^# User@Host: %{USER:user}(?:\[[^\]]+\])?\s+@\s+%{HOST:host}?\s+\[%{IP:ip}?\]" ]
    }
    # Capture query time, lock time, rows returned and rows examined
    grok {
      match => [ "message", "^# Query_time: %{NUMBER:duration:float}\s+Lock_time: %{NUMBER:lock_wait:float} Rows_sent: %{NUMBER:results:int} \s*Rows_examined: %{NUMBER:scanned:int}"]
    }
    # Capture the time the query happened
    grok {
      match => [ "message", "^SET timestamp=%{NUMBER:timestamp};" ]
    }
    # Extract the time based on the time of the query and not the time the item got logged
    date {
      match => [ "timestamp", "UNIX" ]
    }
    # Drop the captured timestamp field since it has been moved to the time of the event
    mutate {
      remove_field => "timestamp"
    }
  }
}
</code></pre>
<p>Save and exit. This configures Logstash to filter <code>mysql-slow</code> type logs with the Grok patterns specified in the <code>match</code> directives. The <code>apache-access</code> type logs are being parsed by the Logstash-provided Grok pattern that matches the default Apache log message format, while the <code>apache-error</code> type logs are being parsed by a Grok filter that was written to match the default error log format.</p>

<p>To put these filters to work, let's restart Logstash:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo service logstash restart
</li></ul></code></pre>
<p>At this point, you will want to ensure that Logstash is running properly, as configuration errors will cause it to fail.</p>

<p><img src="https://assets.digitalocean.com/articles/architecture/production/lamp/kibana.png" alt="Kibana Dashboard" /></p>

<p>You will also want to confirm that Kibana is able to view the filtered Apache logs.</p>

<h2 id="apache-logs">Apache Logs</h2>

<p>Apache's logs are typically located in <code>/var/log/apache2</code>, named "access.log" and "error.log". Gathering these logs will allow you to look at the IP addresses of who is accessing your servers, what they are requesting, and which OS and web browsers they are using, in addition to any error messages that Apache is reporting.</p>

<h3 id="ship-apache-log-files">Ship Apache Log Files</h3>

<p>We must configure Logstash Forwarder to ship the Apache access and error logs to our logging server.</p>

<p>On your application servers, app1 and app2, open the Logstash Forwarder configuration file:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo vi /etc/logstash-forwarder.conf
</li></ul></code></pre>
<p>Add the following, in the "files" section under the existing entries, to send the Apache logs, as the appropriate types, to your Logstash server:</p>
<div class="code-label " title="logstash-forwarder.conf — Apache access and error logs">logstash-forwarder.conf — Apache access and error logs</div><pre class="code-pre "><code langs="">,
    {
      "paths": [
        "/var/log/apache2/access.log"
       ],
      "fields": { "type": "apache-access" }
    },
    {
      "paths": [
        "/var/log/apache2/error.log"
       ],
      "fields": { "type": "apache-error" }
    }
</code></pre>
<p>Save and exit. This configures Logstash Forwarder to ship the Apache access and error logs and mark them as their respective types, which will be used for filtering the logs.</p>

<p>Restart Logstash Forwarder to start shipping the logs: </p>
<pre class="code-pre "><code langs="">sudo service logstash-forwarder restart
</code></pre>
<p>Right now, all of your Apache logs will have a client source IP address that matches the HAProxy server's private IP address, as the HAProxy reverse proxy is the only way to access your application servers from the Internet. To change this to show the source IP of the actual user that is accessing your site, we can modify the default Apache log format to use the <code>X-Forwarded-For</code> headers that HAProxy is sending.</p>

<p>Open your Apache configuration file (apache2.conf):</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo vi /etc/apache2/apache2.conf
</li></ul></code></pre>
<p>Find the line that looks like this:</p>
<pre class="code-pre "><code langs="">[Label apache2.conf — Original "combined" LogFormat]
LogFormat "%h %l %u %t \"%r\" %>s %O \"%{Referer}i\" \"%{User-Agent}i\"" combined
</code></pre>
<p>Replace <strong>%h</strong> with <strong>%{X-Forwarded-For}i</strong>, so it looks like this:</p>
<pre class="code-pre "><code langs="">[Label apache2.conf — Updated "combined" LogFormat]
LogFormat "<span class="highlight">%{X-Forwarded-For}i</span> %l %u %t \"%r\" %>s %O \"%{Referer}i\" \"%{User-Agent}i\"" combined
</code></pre>
<p>Save and exit. This configures the Apache access log to include the source IP address of your actual users, instead of the HAProxy server's private IP address.</p>

<p>Restart Apache to put the log change into effect:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo service apache2 restart
</li></ul></code></pre>
<p>Now we're ready to add Apache log filters to Logstash.</p>

<h3 id="apache-log-filters">Apache Log Filters</h3>

<p>On the ELK server, <strong>logging</strong>, open a new file to add our Apache log filters to Logstash. We will name it <code>12-apache.conf</code>, so it will be read after the Logstash input configuration (in the <code>01-lumberjack-input.conf</code> file):</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo vi /etc/logstash/conf.d/12-apache.conf
</li></ul></code></pre>
<p>Add the following filter definitions:</p>
<div class="code-label " title="12-apache.conf">12-apache.conf</div><pre class="code-pre "><code langs="">filter {
  if [type] == "apache-access" {
    grok {
      match => { "message" => "%{COMBINEDAPACHELOG}" }
    }
  }
}
filter {
  if [type] == "apache-error" {
    grok {
      match => { "message" => "\[(?<timestamp>%{DAY:day} %{MONTH:month} %{MONTHDAY} %{TIME} %{YEAR})\] \[%{DATA:severity}\] \[pid %{NUMBER:pid}\] \[client %{IPORHOST:clientip}:%{POSINT:clientport}] %{GREEDYDATA:error_message}" }
    }
  }
}
</code></pre>
<p>Save and exit. This configures Logstash to filter <code>apache-access</code> and <code>apache-error</code> type logs with the Grok patterns specified in the respective <code>match</code> directives. The <code>apache-access</code> type logs are being parsed by the Logstash-provided Grok pattern that matches the default Apache log message format, while the <code>apache-error</code> type logs are being parsed by a Grok filter that was written to match the default error log format.</p>

<p>To put these filters to work, let's restart Logstash:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo service logstash restart
</li></ul></code></pre>
<p>At this point, you will want to ensure that Logstash is running properly, as configuration errors will cause it to fail. You will also want to confirm that Kibana is able to view the filtered Apache logs.</p>

<h2 id="haproxy-logs">HAProxy Logs</h2>

<p>HAProxy's logs are typically located in <code>/var/log/haproxy.log</code>. Gathering these logs will allow you to look at the IP addresses of who is accessing your load balancer, what they are requesting, which application server is serving their requests, and various other details about the connection.</p>

<h3 id="ship-haproxy-log-files">Ship HAProxy Log Files</h3>

<p>We must configure Logstash Forwarder to ship the HAProxy logs.</p>

<p>On your HAProxy server, <strong>lb1</strong>, open the Logstash Forwarder configuration file:</p>
<pre class="code-pre "><code langs="">sudo vi /etc/logstash-forwarder.conf
</code></pre>
<p>Add the following, in the "files" section under the existing entries, to send the HAProxy logs as type "haproxy-log" to your Logstash server:</p>
<div class="code-label " title="logstash-forwarder.conf — HAProxy logs">logstash-forwarder.conf — HAProxy logs</div><pre class="code-pre "><code langs="">,
    {
      "paths": [
        "/var/log/haproxy.log"
       ],
      "fields": { "type": "haproxy-log" }
    }
</code></pre>
<p>Save and exit. This configures Logstash Forwarder to ship the HAProxy logs and mark them as <code>haproxy-log</code>, which will be used for filtering the logs.</p>

<p>Restart Logstash Forwarder to start shipping the logs: </p>
<pre class="code-pre "><code langs="">sudo service logstash-forwarder restart
</code></pre>
<h3 id="haproxy-log-filter">HAProxy Log Filter</h3>

<p>On the ELK server, <strong>logging</strong>, open a new file to add our HAProxy log filter to Logstash. We will name it <code>13-haproxy.conf</code>, so it will be read after the Logstash input configuration (in the <code>01-lumberjack-input.conf</code> file):</p>
<pre class="code-pre "><code langs="">sudo vi /etc/logstash/conf.d/13-haproxy.conf
</code></pre>
<p>Add the following filter definition:</p>
<pre class="code-pre "><code langs="">filter {
  if [type] == "haproxy-log" {
    grok {
      match => { "message" => "%{SYSLOGTIMESTAMP:timestamp} %{HOSTNAME:hostname} %{SYSLOGPROG}: %{IPORHOST:clientip}:%{POSINT:clientport} \[%{MONTHDAY}[./-]%{MONTH}[./-]%{YEAR}:%{TIME}\] %{NOTSPACE:frontend_name} %{NOTSPACE:backend_name}/%{NOTSPACE:server_name} %{INT:time_request}/%{INT:time_queue}/%{INT:time_backend_connect}/%{INT:time_backend_response}/%{NOTSPACE:time_duration} %{INT:http_status_code} %{NOTSPACE:bytes_read} %{DATA:captured_request_cookie} %{DATA:captured_response_cookie} %{NOTSPACE:termination_state} %{INT:actconn}/%{INT:feconn}/%{INT:beconn}/%{INT:srvconn}/%{NOTSPACE:retries} %{INT:srv_queue}/%{INT:backend_queue} "(%{WORD:http_verb} %{URIPATHPARAM:http_request} HTTP/%{NUMBER:http_version})|<BADREQ>|(%{WORD:http_verb} (%{URIPROTO:http_proto}://))" }
    }
  }
}
</code></pre>
<p>Save and exit. This configures Logstash to filter <code>haproxy-log</code> type logs with the Grok patterns specified in the respective <code>match</code> directive. The <code>haproxy-log</code> type logs are being parsed by the Logstash-provided Grok pattern that matches the default HAProxy log message format.</p>

<p>To put these filters to work, let's restart Logstash:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo service logstash restart
</li></ul></code></pre>
<p>At this point, you will want to ensure that Logstash is running properly, as configuration errors will cause it to fail. </p>

<h2 id="set-up-kibana-visualizations">Set Up Kibana Visualizations</h2>

<p>Now that you are collecting your logs in a central location, you can start using Kibana to visualize them. This tutorial can help you get started with that: <a href="https://indiareads/community/tutorials/how-to-use-kibana-dashboards-and-visualizations">How To Use Kibana Dashboards and Visualizations</a>.</p>

<p>Once you are somewhat comfortable with Kibana, try out this tutorial to visualize your users in an interesting way: <a href="https://indiareads/community/tutorials/how-to-map-user-location-with-geoip-and-elk-elasticsearch-logstash-and-kibana">How To Map User Location with GeoIP and ELK</a>.</p>

<h2 id="conclusion">Conclusion</h2>

<p>Congratulations! You have completed the Production Web Application Setup tutorial series. If you followed all of the tutorials, you should have a setup that looks like what we described in the overview tutorial (with private DNS and remote backups):</p>

<p><img src="https://assets.digitalocean.com/articles/architecture/production/lamp/final.png" alt="Production Setup" /></p>

<p>That is, you should have a working application, with decoupled components, that is supported by backups, monitoring, and centralized logging components. Be sure to test out your application, and make sure all of the components work as expected.</p>

    