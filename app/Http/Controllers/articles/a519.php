<link rel='stylesheet' href='digital_ocean.css'><br> <br> 
      <p><span class="note"><strong>Note:</strong> This tutorial is for an older version of the ELK stack setup that uses Logstash Forwarder instead of Filebeat. The latest version of this tutorial is available at <a href="https://indiareads/community/tutorials/adding-logstash-filters-to-improve-centralized-logging">Adding Logstash Filters To Improve Centralized Logging</a>.<br /></span></p>

<h3 id="introduction">Introduction</h3>

<p>Logstash is a powerful tool for centralizing and analyzing logs, which can help to provide and overview of your environment, and to identify issues with your servers. One way to increase the effectiveness of your Logstash setup is to collect important application logs and structure the log data by employing filters, so the data can be readily analyzed and query-able. We will build our filters around "grok" patterns, that will parse the data in the logs into useful bits of information. </p>

<p>This guide is a sequel to the <a href="https://indiareads/community/tutorials/how-to-install-elasticsearch-1-7-logstash-1-5-and-kibana-4-1-elk-stack-on-ubuntu-14-04">How To Install Elasticsearch 1.7, Logstash 1.5, and Kibana 4.1 (ELK Stack) on Ubuntu 14.04</a> tutorial, and focuses primarily on adding filters for various common application logs. </p>

<h2 id="prerequisites">Prerequisites</h2>

<p>To follow this tutorial, you must have a working Logstash server, and a way to ship your logs to Logstash. If you do not have Logstash set up, here is another tutorial that will get you started: <a href="https://indiareads/community/tutorials/how-to-install-elasticsearch-1-7-logstash-1-5-and-kibana-4-1-elk-stack-on-ubuntu-14-04">How To Install Elasticsearch 1.7, Logstash 1.5, and Kibana 4.1 (ELK Stack) on Ubuntu 14.04</a>.</p>

<p>Logstash Server Assumptions:</p>

<ul>
<li>Logstash is installed in <code>/opt/logstash</code></li>
<li>You are receiving logs from Logstash Forwarder on port 5000</li>
<li>Your Logstash configuration files are located in <code>/etc/logstash/conf.d</code></li>
<li>You have an input file named <code>01-lumberjack-input.conf</code></li>
<li>You have an output file named <code>30-lumberjack-output.conf</code></li>
</ul>

<p>Logstash Forwarder Assumptions:</p>

<ul>
<li>You have Logstash Forwarder configured, on each application server, to send syslog/auth.log to your Logstash server (as in the <a href="https://indiareads/community/tutorials/how-to-install-elasticsearch-logstash-and-kibana-4-on-ubuntu-14-04#set-up-logstash-forwarder-(add-client-servers)">Set Up Logstash Forwarder</a> section of the previous tutorial)</li>
</ul>

<p>If your setup differs from what we assume, simply adjust this guide to match your environment.</p>

<p>You may need to create the <code>patterns</code> directory by running this command on your Logstash Server:</p>
<pre class="code-pre "><code langs="">sudo mkdir -p /opt/logstash/patterns
sudo chown logstash:logstash /opt/logstash/patterns
</code></pre>
<h2 id="about-grok">About Grok</h2>

<p>Grok works by parsing text patterns, using regular expressions, and assigning them to an identifier.</p>

<p>The syntax for a grok pattern is <code>%{<span class="highlight">PATTERN</span>:<span class="highlight">IDENTIFIER</span>}</code>. A Logstash filter includes a sequence of grok patterns that matches and assigns various pieces of a log message to various identifiers, which is how the logs are given structure.</p>

<p>To learn more about grok, visit the <a href="http://logstash.net/docs/1.4.2/filters/grok">Logstash grok page</a>, and the <a href="https://github.com/elasticsearch/logstash/blob/v1.4.2/patterns/grok-patterns">Logstash Default Patterns listing</a>.</p>

<h2 id="how-to-use-this-guide">How To Use This Guide</h2>

<p>Each main section following this will include the additional configuration details that are necessary to gather and filter logs for a given application. For each application that you want to log and filter, you will have to make some configuration changes on both the application server, and the Logstash server.</p>

<h3 id="logstash-forwarder-subsection">Logstash Forwarder Subsection</h3>

<p>The Logstash Forwarder subsections pertain to the application server that is sending its logs. The additional <em>files</em> configuration should be added to the <code>/etc/logstash-forwarder.conf</code> file directly after the following lines:</p>
<pre class="code-pre "><code langs="">  "files": [
    {
      "paths": [
        "/var/log/syslog",
        "/var/log/auth.log"
       ],
      "fields": { "type": "syslog" }
    }
</code></pre>
<p>Ensure that the additional configuration is before the <code>]</code> that closes the "files" section. This will include the proper log files to send to Logstash, and label them as a specific type (which will be used by the Logstash filters). The Logstash Forwarder must be reloaded to put any changes into effect.</p>

<h3 id="logstash-patterns-subsection">Logstash Patterns Subsection</h3>

<p>If there is a Logstash Patterns subsection, it will contain grok patterns that can be added to a new file in <code>/opt/logstash/patterns</code> on the Logstash Server. This will allow you to use the new patterns in Logstash filters.</p>

<h3 id="logstash-filter-subsection">Logstash Filter Subsection</h3>

<p>The Logstash Filter subsections will include a filter that can can be added to a new file, between the input and output configuration files, in <code>/etc/logstash/conf.d</code> on the Logstash Server. The filter determine how the Logstash server parses the relevant log files. Remember to restart the Logstash server after adding a new filter, to load your changes.</p>

<p>Now that you know how to use this guide, the rest of the guide will show you how to gather and filter application logs!</p>

<h2 id="application-nginx">Application: Nginx</h2>

<h3 id="logstash-forwarder-nginx">Logstash Forwarder: Nginx</h3>

<p>On your <strong>Nginx</strong> servers, open the <code>logstash-forwarder.conf</code> configuration file for editing:</p>
<pre class="code-pre "><code langs="">sudo vi /etc/logstash-forwarder.conf
</code></pre>
<p>Add the following, in the "files" section, to send the Nginx access logs as type "nginx-access" to your Logstash server:</p>
<pre class="code-pre "><code langs="">,
    {
      "paths": [
        "/var/log/nginx/access.log"
       ],
      "fields": { "type": "nginx-access" }
    }
</code></pre>
<p>Save and exit. Reload the Logstash Forwarder configuration to put the changes into effect:</p>
<pre class="code-pre "><code langs="">sudo service logstash-forwarder restart
</code></pre>
<h3 id="logstash-patterns-nginx">Logstash Patterns: Nginx</h3>

<p>Nginx log patterns are not included in Logstash's default patterns, so we will add Nginx patterns manually.</p>

<p>On your <strong>Logstash server</strong>, create a new pattern file called <code>nginx</code>:</p>
<pre class="code-pre "><code langs="">sudo vi /opt/logstash/patterns/nginx
</code></pre>
<p>Then insert the following lines:</p>
<pre class="code-pre "><code langs="">NGUSERNAME [a-zA-Z\.\@\-\+_%]+
NGUSER %{NGUSERNAME}
NGINXACCESS %{IPORHOST:clientip} %{NGUSER:ident} %{NGUSER:auth} \[%{HTTPDATE:timestamp}\] "%{WORD:verb} %{URIPATHPARAM:request} HTTP/%{NUMBER:httpversion}" %{NUMBER:response} (?:%{NUMBER:bytes}|-) (?:"(?:%{URI:referrer}|-)"|%{QS:referrer}) %{QS:agent}
</code></pre>
<p>Save and exit. The NGINXACCESS pattern parses, and assigns the data to various identifiers (e.g. clientip, ident, auth, etc.).</p>

<p>Next, change the ownership of the pattern file to <code>logstash</code>:</p>
<pre class="code-pre "><code langs="">sudo chown logstash:logstash /opt/logstash/patterns/nginx
</code></pre>
<h3 id="logstash-filter-nginx">Logstash Filter: Nginx</h3>

<p>On your <strong>Logstash server</strong>, create a new filter configuration file called <code>11-nginx.conf</code>:</p>
<pre class="code-pre "><code langs="">sudo vi /etc/logstash/conf.d/11-nginx.conf
</code></pre>
<p>Then add the following filter:</p>
<pre class="code-pre "><code langs="">filter {
  if [type] == "nginx-access" {
    grok {
      match => { "message" => "%{NGINXACCESS}" }
    }
  }
}
</code></pre>
<p>Save and exit. Note that this filter will attempt to match messages of "nginx-access" type with the NGINXACCESS pattern, defined above.</p>

<p>Now restart Logstash to reload the configuration:</p>
<pre class="code-pre "><code langs="">sudo service logstash restart
</code></pre>
<p>Now your Nginx logs will be gathered and filtered!</p>

<h2 id="application-apache-http-web-server">Application: Apache HTTP Web Server</h2>

<p>Apache's log patterns are included in the default Logstash patterns, so it is fairly easy to set up a filter for it.</p>

<p><strong>Note:</strong> If you are using a RedHat variant, such as CentOS, the logs are located at <code>/var/log/httpd</code> instead of <code>/var/log/apache2</code>, which is used in the examples.</p>

<h3 id="logstash-forwarder">Logstash Forwarder</h3>

<p>On your <strong>Apache</strong> servers, open the <code>logstash-forwarder.conf</code> configuration file for editing:</p>
<pre class="code-pre "><code langs="">sudo vi /etc/logstash-forwarder.conf
</code></pre>
<p>Add the following, in the "files" section, to send the Apache access logs as type "apache-access" to your Logstash server:</p>
<pre class="code-pre "><code langs="">,
    {
      "paths": [
        "/var/log/<span class="highlight">apache2</span>/access.log"
       ],
      "fields": { "type": "apache-access" }
    }
</code></pre>
<p>Save and exit. Reload the Logstash Forwarder configuration to put the changes into effect:</p>
<pre class="code-pre "><code langs="">sudo service logstash-forwarder restart
</code></pre>
<h3 id="logstash-filter-apache">Logstash Filter: Apache</h3>

<p>On your <strong>Logstash server</strong>, create a new filter configuration file called <code>12-apache.conf</code>:</p>
<pre class="code-pre "><code langs="">sudo vi /etc/logstash/conf.d/12-apache.conf
</code></pre>
<p>Then add the following filter:</p>
<pre class="code-pre "><code langs="">filter {
  if [type] == "apache-access" {
    grok {
      match => { "message" => "%{COMBINEDAPACHELOG}" }
    }
  }
}
</code></pre>
<p>Save and exit. Note that this filter will attempt to match messages of "apache-access" type with the COMBINEDAPACHELOG pattern, one the default Logstash patterns.</p>

<p>Now restart Logstash to reload the configuration:</p>
<pre class="code-pre "><code langs="">sudo service logstash restart
</code></pre>
<p>Now your Apache logs will be gathered and filtered!</p>

<h2 id="conclusion">Conclusion</h2>

<p>It is possible to collect and parse logs of pretty much any type. Try and write your own filters and patterns for other log files.</p>

<p>Feel free to comment with filters that you would like to see, or with patterns of your own!</p>

<p>If you aren't familiar with using Kibana, check out the third tutorial in this series: <a href="https://indiareads/community/tutorials/how-to-use-kibana-dashboards-and-visualizations">How To Use Kibana Visualizations and Dashboards</a>.</p>

    