<link rel='stylesheet' href='digital_ocean.css'><br> <br> 
      <h3 id="introduction">Introduction</h3>

<p>This tutorial is an ELK Stack (Elasticsearch, Logstash, Kibana) troubleshooting guide. It assumes that you followed the <a href="https://indiareads/community/tutorials/how-to-install-elasticsearch-1-7-logstash-1-5-and-kibana-4-1-elk-stack-on-ubuntu-14-04">How To Install Elasticsearch 1.7, Logstash 1.5, and Kibana 4.1 (ELK Stack) on Ubuntu 14.04</a> tutorial, or its <a href="https://indiareads/community/tutorials/how-to-install-elasticsearch-1-7-logstash-1-5-and-kibana-4-1-elk-stack-on-centos-7">CentOS equivalent</a>, with Logstash Forwarder, but it may be useful for troubleshooting other general ELK setups.</p>

<p>This tutorial is structured as a series of common issues, and potential solutions to these issues, along with steps to help you verify that the various components of your ELK stack are functioning properly. As such, feel free to jump around to the sections that are relevant to the issues you are encountering.</p>

<h2 id="issue-kibana-no-default-index-pattern-warning">Issue: Kibana No Default Index Pattern Warning</h2>

<p>When accessing Kibana via a web browser, you may encounter a page with this warning:</p>
<pre class="code-pre "><code langs=""><div class="secondary-code-label " title="Kibana warning:">Kibana warning:</div>Warning No default index pattern. You must select or create one to continue.
...
Unable to fetch mapping. Do you have indices matching the pattern?
</code></pre>
<p>Here is a screenshot of the warning:</p>

<p><img src="https://assets.digitalocean.com/articles/elk/troubleshoot-no-default-index-pattern.png" alt="Warning: No default index pattern. You must select or create one to continue." /></p>

<p>"Unable to fetch mapping" indicates that Elasticsearch does not contain any entries that match the default <code>logstash-*</code> pattern. Typically, this means that your logs are not being stored in Elasticsearch due to communication issues from Logstash to Elasticsearch, and/or from your log shipper (e.g. Logstash Forwarder) to Logstash. In other words, your logs aren't making it through the chain from Logstash Forwarder, to Logstash, to Elasticsearch for some reason.</p>

<p><img src="https://assets.digitalocean.com/articles/elk/elk-infrastructure.png" alt="The ELK Stack" /></p>

<p>To resolve communication issues between Logstash and Elasticsearch, run through the <a href="https://indiareads/community/tutorials/how-to-troubleshoot-common-elk-stack-issues#logstash-how-to-check-if-it-is-running">Logstash troubleshooting</a> sections. To resolve communication issues between Logstash Forwarder and Logstash, run through the <a href="https://indiareads/community/tutorials/how-to-troubleshoot-common-elk-stack-issues#logstash-forwarder-how-to-check-if-it-is-running">Logstash Forwarder troubleshooting</a> sections.</p>

<p>If you configured Logstash to use a non-default index pattern, you can resolve the issue by specifying the proper index pattern in the text box.</p>

<h2 id="issue-kibana-unable-to-connect-to-elasticsearch">Issue: Kibana Unable to connect to Elasticsearch</h2>

<p>When accessing Kibana via a web browser, you may encounter a page with this error:</p>
<pre class="code-pre "><code langs=""><div class="secondary-code-label " title="Kibana error:">Kibana error:</div>Fatal Error
Kibana: Unable to connect to Elasticsearch

Error: Unable to connect to Elasticsearch
Error: Bad Gateway
...
</code></pre>
<p>Here is a screenshot of the error:</p>

<p><img src="https://assets.digitalocean.com/articles/elk/troubleshoot-unable-connect-elasticsearch.png" alt="Unable to connect to Elasticsearch" /></p>

<p>This means that Kibana can't connect to Elasticsearch. Elasticsearch may not be running, or Kibana may be configured to look for Elasticsearch on the wrong host and port.</p>

<p>To resolve this issue, make sure that Elasticsearch is running by following the Elasticsearch troubleshooting sections. Then ensure that Kibana is configured to connect to the host and port that Elasticsearch is running on.</p>

<p>For example, if Elasticsearch is running on <code>localhost</code> on port <code>9200</code>, make sure that Kibana is configured appropriately.</p>

<p>Open the Kibana configuration file:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo vi /opt/kibana/config/kibana.yml
</li></ul></code></pre>
<p>Then make sure <code>elasticsearch_url</code> is set properly.</p>
<pre class="code-pre "><code langs=""><div class="secondary-code-label " title="/opt/kibana/config/kibana.yml excerpt:">/opt/kibana/config/kibana.yml excerpt:</div># The Elasticsearch instance to use for all your queries.
elasticsearch_url: "http://localhost:9200"
</code></pre>
<p>Save and exit.</p>

<p>Now restart the Kibana service to put your changes into place:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo service kibana restart
</li></ul></code></pre>
<p>After Kibana has restarted, open Kibana in a web browser and verify that the error was resolved.</p>

<h2 id="issue-kibana-is-not-accessible">Issue: Kibana Is Not Accessible</h2>

<p>The Nginx component of the ELK stack serves as a reverse proxy to Kibana. If Nginx is not running or configured properly, you will not be able to access the Kibana interface. However, as the rest of the ELK components don't rely on Nginx, they may very well be functioning fine.</p>

<h3 id="cause-nginx-is-not-running">Cause: Nginx Is Not Running</h3>

<p>If Nginx isn't running, and you try to access your ELK stack in a web browser, you may see an error that is similar to this:</p>
<pre class="code-pre "><code langs=""><div class="secondary-code-label " title="Nginx Error:">Nginx Error:</div>This webpage is not available
ERR_CONNECTION_REFUSED
</code></pre>
<p>This usually indicates that Nginx isn't running.</p>

<p>You can check the status of the Nginx service with this command:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo service nginx status
</li></ul></code></pre>
<p>If it reports that the service is not running or not recognized, resolve your issue by following the instructions of the <a href="https://indiareads/community/tutorials/how-to-install-elasticsearch-logstash-and-kibana-4-on-ubuntu-14-04#install-nginx">Install Nginx section</a> of the ELK stack tutorial. If it reports that the service is running, you need to reconfigure Nginx, following the same instructions.</p>

<h3 id="cause-nginx-is-running-but-can-39-t-connect-to-kibana">Cause: Nginx Is Running But Can't Connect to Kibana</h3>

<p>If Kibana is not accessible, and you receive a <code>502 Bad Gateway</code> error, Nginx is running but it's unable to connect to Kibana.</p>

<p><img src="https://assets.digitalocean.com/articles/elk/troubleshoot-nginx-502.png" alt="Nginx 502 Bad Gateway" /></p>

<p>The first step to resolving this issue is to check if Kibana is running with this command:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo service kibana status
</li></ul></code></pre>
<p>If Kibana isn't running or not recognized, follow the instructions of the <a href="https://indiareads/community/tutorials/how-to-install-elasticsearch-logstash-and-kibana-4-on-ubuntu-14-04#install-kibana">Install Kibana section</a> of the ELK stack tutorial.</p>

<p>If that doesn't resolve the issue, you may have an issue with your Nginx configuration. You should review the configuration portion of the <a href="https://indiareads/community/tutorials/how-to-install-elasticsearch-logstash-and-kibana-4-on-ubuntu-14-04#install-nginx">Install Nginx section</a> of the ELK stack tutorial. You can check the Nginx error logs for clues:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo tail /var/log/nginx/error.log
</li></ul></code></pre>
<p>This should tell you exactly why Nginx can't connect to Kibana.</p>

<h3 id="cause-unable-to-authenticate-user">Cause: Unable to Authenticate User</h3>

<p>If you have basic authentication enabled, and you are having trouble passing the authentication step, you should look at the Nginx error logs to determine the specifics of the problem. </p>

<p><img src="https://assets.digitalocean.com/articles/elk/troubleshoot-unable-to-authenticate.png" alt="Authentication Required" /></p>

<p>To look at the recent Nginx errors, use this command:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo tail /var/log/nginx/error.log
</li></ul></code></pre>
<p>If you see a <code>user was not found</code> error, the user does not exist in the <code>htpasswd</code> file. This type of error is indicated by the following log entry:</p>
<pre class="code-pre "><code langs=""><div class="secondary-code-label " title="Nginx error logs (user was not found):">Nginx error logs (user was not found):</div>2015/10/26 12:11:57 [error] 3933#0: *242 user "NonExistentUser" was not found in "/etc/nginx/htpasswd.users", client: 108.60.145.130, server: example.com, request: "GET / HTTP/1.1", host: "45.55.252.231"
</code></pre>
<p>If you see a <code>password mismatch</code> error, the user exists but you supplied the incorrect password. This type of error is indicated by the following log entry:</p>
<pre class="code-pre "><code langs=""><div class="secondary-code-label " title="Nginx error logs (user password mismatch):">Nginx error logs (user password mismatch):</div>2015/10/26 12:12:56 [error] 3933#0: *242 user "kibanaadmin": password mismatch, client: 108.60.145.130, server: example.com, request: "GET / HTTP/1.1", host: "45.55.252.231"
</code></pre>
<p>The resolution to these two errors is to either provide the proper login information, or modify your existing <code>htpasswd</code> file with user logins that you expect to exist. For example, to create or overwrite a user called <code>kibanaadmin</code> in the <code>htpasswd.users</code> file, use this command:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo htpasswd /etc/nginx/htpasswd.users <span class="highlight">kibanaadmin</span>
</li></ul></code></pre>
<p>Then supply your desired password, and confirm it.</p>

<p>If you see a <code>No such file or directory</code> error, the <code>htpasswd</code> file specified in the Nginx configuration does not exist. This type of error is indicated by the following log entry:</p>
<pre class="code-pre "><code langs=""><div class="secondary-code-label " title="Nginx error logs (htpasswd file does not exist):">Nginx error logs (htpasswd file does not exist):</div>2015/10/26 12:17:38 [error] 3933#0: *266 open() "/etc/nginx/htpasswd.users" failed (2: No such file or directory), client: 108.60.145.130, server: example.com, request: "GET / HTTP/1.1", host: "45.55.252.231"
</code></pre>
<p>Here, you should create a new <code>/etc/nginx/htpasswd.users</code> file, and add a user (<code>kibanaadmin</code> in this example) to it, with this command:</p>
<pre class="code-pre "><code langs="">sudo htpasswd -c /etc/nginx/htpasswd.users <span class="highlight">kibanaadmin</span>
</code></pre>
<p>Enter a new password, and confirm it.</p>

<p>Now, try authenticating as the user you just created.</p>

<h2 id="logstash-how-to-check-if-it-is-running">Logstash: How To Check If It is Running</h2>

<p>If Logstash isn't running, you won't be able to receive and parse logs from log shippers, such as Logstash Forwarder, and store the processed logs in Elasticsearch. This section will show you how to check if Logstash is functioning normally.</p>

<h3 id="verify-service-is-running">Verify Service is Running</h3>

<p>The most basic thing to check is the status of the Logstash status:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo service logstash status
</li></ul></code></pre>
<p>If Logstash is running, you will see this output:</p>
<pre class="code-pre "><code langs=""><div class="secondary-code-label " title="Logstash status (OK):">Logstash status (OK):</div>logstash is running
</code></pre>
<p>Otherwise, if the service is not running, you will see this message:</p>
<pre class="code-pre "><code langs=""><div class="secondary-code-label " title="Logstash status (Bad):">Logstash status (Bad):</div>logstash is not running
</code></pre>
<p>If Logstash isn't running, try starting it with this command:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo service logstash start
</li></ul></code></pre>
<p>Then check its status again, after several seconds. Logstash is a Java application and it will report as "running" for a few seconds after every start attempt, so it is important to wait a few seconds before checking for a "not running" status. If it reports as "not running", it is probably misconfigured.  The next two sections cover troubleshooting common Logstash issues.</p>

<h2 id="issue-logstash-is-not-running">Issue: Logstash is Not Running</h2>

<p>If Logstash is not running, there are a few potential causes. This section will cover a variety of common cases where Logstash will fail to run, and propose potential solutions.</p>

<h3 id="cause-configuration-contains-a-syntax-error">Cause: Configuration Contains a Syntax Error</h3>

<p>If Logstash has errors in its configuration files, which are located in the <code>/etc/logstash/conf.d</code> directory, the service will not be able to start properly. The best thing to do is check the Logstash logs for clues about why it is failing.</p>

<p>Open two terminal sessions to your server, so you can view the Logstash logs while trying to start the service.</p>

<p>In the first terminal session, we'll look at the logs:</p>
<pre class="code-pre custom_prefix"><code langs=""><ul class="prefixed"><li class="line" prefix="terminal-1$">tail -f /var/log/logstash/logstash.log
</li></ul></code></pre>
<p>This will display the last few log entries, plus any future log entries.</p>

<p>In the second terminal session, try to start the Logstash service:</p>
<pre class="code-pre custom_prefix"><code langs=""><ul class="prefixed"><li class="line" prefix="terminal-2$">sudo service logstash start
</li></ul></code></pre>
<p>Switch back to the first terminal session to look at the logs that are generated when Logstash is starting up.</p>

<p>If you see log entries that include error messages, try and read the message(s) to figure out what is going wrong. Here is an example of the error logs you might see if the Logstash configuration has a syntax error (mismatched curly braces):</p>
<pre class="code-pre "><code langs=""><div class="secondary-code-label " title="Logstash logs (Syntax error):">Logstash logs (Syntax error):</div>...
{:timestamp=>"2015-10-28T11:51:09.205000-0400", :message=>"Error: Expected one of #, => at line 12, column 6 (byte 209) after input {\n  lumberjack {\n    port => 5043\n    type => \"logs\"\n    ssl_certificate => \"/etc/pki/tls/certs/logstash-forwarder.crt\"\n    ssl_key => \"/etc/pki/tls/private/logstash-forwarder.key\"\n  \n}\n\n\nfilter {\n  if "}
{:timestamp=>"2015-10-28T11:51:09.228000-0400", :message=>"You may be interested in the '--configtest' flag which you can\nuse to validate logstash's configuration before you choose\nto restart a running system."}
</code></pre>
<p>The last message that says that we might be interested in validating the configuration indicates that the configuration contains a syntax error. The previous message provides a more specific error message, in this case, that there is a missing closing curly brace in the <code>input</code> section of the configuration. To resolve this issue, edit the offending portion of your Logstash configuration:</p>
<pre class="code-pre custom_prefix"><code langs=""><ul class="prefixed"><li class="line" prefix="terminal-2$">sudo vi /etc/logstash/conf.d/<span class="highlight">01-lumberjack-input.conf</span>
</li></ul></code></pre>
<p>Find the line that has the bad entry, and fix it, then save and exit.</p>

<p>Now, on the second terminal, start the Logstash service:</p>
<pre class="code-pre custom_prefix"><code langs=""><ul class="prefixed"><li class="line" prefix="terminal-2$">sudo service logstash start
</li></ul></code></pre>
<p>If the issue has been resolved, there should be no new log entries (Logstash doesn't log a successful startup). After several seconds, check the status of the Logstash service:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo service logstash status
</li></ul></code></pre>
<p>If it's running, you have resolved the issue.</p>

<p>You may have a different configuration problem than our example. We will cover a few other common Logstash configuration issues. As always, if you're able to figure out what the error means, try and fix it yourself.</p>

<h3 id="cause-ssl-files-do-not-exist">Cause: SSL Files Do Not Exist</h3>

<p>Another common cause for Logstash not running is problem with the SSL certificate and key files. For example, if they don't exist where your Logstash configuration specifies them to, your logs will show an error like this:</p>
<pre class="code-pre "><code langs=""><div class="secondary-code-label " title="Logstash logs (SSL key file does not exist):">Logstash logs (SSL key file does not exist):</div>{:timestamp=>"2015-10-28T14:29:07.311000-0400", :message=>"Invalid setting for lumberjack input plugin:\n\n  input {\n    lumberjack {\n      # This setting must be a path\n      # File does not exist or cannot be opened /etc/pki/tls/private/logstash-forwarder.key\n      ssl_key => \"/etc/pki/tls/private/logstash-forwarder.key\"\n      ...\n    }\n  }", :level=>:error}
{:timestamp=>"2015-10-28T14:29:07.339000-0400", :message=>"Error: Something is wrong with your configuration."}
{:timestamp=>"2015-10-28T14:29:07.340000-0400", :message=>"You may be interested in the '--configtest' flag which you can\nuse to validate logstash's configuration before you choose\nto restart a running system."}
</code></pre>
<p>To resolve this particular issue, you need to make sure that you have an SSL key file (<a href="https://indiareads/community/tutorials/how-to-install-elasticsearch-logstash-and-kibana-4-on-ubuntu-14-04#generate-ssl-certificates">generate one</a> if you forgot to), and that it is placed in the proper location (<code>/etc/pki/tls/private/logstash-forwarder.key</code>, in the example). If you already do have a key file, make sure to move it to the proper location, and ensure that the Logstash configuration is pointing to it.</p>

<p>Now, start the Logstash service:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo service logstash start
</li></ul></code></pre>
<p>If the issue has been resolved, there should be no new log entries. After several seconds, check the status of the Logstash service:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo service logstash status
</li></ul></code></pre>
<p>If it's running, you have resolved the issue.</p>

<h2 id="issue-logstash-is-running-but-not-storing-logs-in-elasticsearch">Issue: Logstash Is Running but Not Storing Logs in Elasticsearch</h2>

<p>If Logstash is running but not storing logs in Elasticsearch, it is because it can't reach Elasticsearch. Typically, this is a result of Elasticsearch not running. If this is a case, the Logstash logs will show error messages like this:</p>
<pre class="code-pre "><code langs=""><div class="secondary-code-label " title="Logstash logs (Elasticsearch isn't running):">Logstash logs (Elasticsearch isn't running):</div>{:timestamp=>"2015-10-28T14:46:35.355000-0400", :message=>"CircuitBreaker::rescuing exceptions", :name=>"Lumberjack input", :exception=>LogStash::SizedQueueTimeout::TimeoutError, :level=>:warn}
{:timestamp=>"2015-10-28T14:46:35.399000-0400", :message=>"Lumberjack input: The circuit breaker has detected a slowdown or stall in the pipeline, the input is closing the current connection and rejecting new connection until the pipeline recover.", :exception=>LogStash::CircuitBreaker::HalfOpenBreaker, :level=>:warn}
...
{:timestamp=>"2015-10-28T14:47:49.987000-0400", :message=>"Lumberjack input: the pipeline is blocked, temporary refusing new connection.", :level=>:warn}
...
</code></pre>
<p>In this case, ensure that Elasticsearch is running by following the Elasticsearch troubleshooting steps.</p>

<p>You may also see errors like this:</p>
<pre class="code-pre "><code langs=""><div class="secondary-code-label " title="Logstash logs (Logstash is configured to send its output to the wrong host):">Logstash logs (Logstash is configured to send its output to the wrong host):</div>{:timestamp=>"2015-10-28T14:53:56.528000-0400", :message=>"Got error to send bulk of actions: blocked by: [SERVICE_UNAVAILABLE/1/state not recovered / initialized];[SERVICE_UNAVAILABLE/2/no master];", :level=>:error}
{:timestamp=>"2015-10-28T14:53:56.531000-0400", :message=>"Failed to flush outgoing items", :outgoing_count=>25, :exception=>"Java::OrgElasticsearchClusterBlock::ClusterBlockException", :backtrace=>["org.elasticsearch.cluster.block.ClusterBlocks.globalBlockedException(org/elasticsearch/cluster/block/ClusterBlocks.java:151)", "org.elasticsearch.cluster.block.ClusterBlocks.globalBlockedRaiseException(org/elasticsearch/cluster/block/ClusterBlocks.java:141)", "org.elasticsearch.action.bulk.TransportBulkAction.executeBulk(org/elasticsearch/action/bulk/TransportBulkAction.java:215)", "org.elasticsearch.action.bulk.TransportBulkAction.access$000(org/elasticsearch/action/bulk/TransportBulkAction.java:67)", "org.elasticsearch.action.bulk.TransportBulkAction$1.onFailure(org/elasticsearch/action/bulk/TransportBulkAction.java:153)", "org.elasticsearch.action.support.TransportAction$ThreadedActionListener$2.run(org/elasticsearch/action/support/TransportAction.java:137)", "java.util.concurrent.ThreadPoolExecutor.runWorker(java/util/concurrent/ThreadPoolExecutor.java:1142)", "java.util.concurrent.ThreadPoolExecutor$Worker.run(java/util/concurrent/ThreadPoolExecutor.java:617)", "java.lang.Thread.run(java/lang/Thread.java:745)"], :level=>:warn}
{:timestamp=>"2015-10-28T14:54:57.543000-0400", :message=>"Got error to send bulk of actions: blocked by: [SERVICE_UNAVAILABLE/1/state not recovered / initialized];[SERVICE_UNAVAILABLE/2/no master];", :level=>:error}
</code></pre>
<p>This indicates that the <code>output</code> section of your Logstash configuration may be pointing to the wrong host. To resolve this issue, ensure that Elasticsearch is running, and check your Logstash configuration:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo vi /etc/logstash/conf.d/30-lumberjack-output.conf
</li></ul></code></pre>
<p>Verify that the <code>elasticsearch { host => <span class="highlight">localhost</span> }</code> line is pointing to the host that is running Elasticsearch:</p>
<pre class="code-pre "><code langs=""><div class="secondary-code-label " title="Logstash output configuration:">Logstash output configuration:</div>output {
  elasticsearch { host => localhost }
  stdout { codec => rubydebug }
}
</code></pre>
<p>Save and exit. This example assumes that Elasticsearch is running on <code>localhost</code>.</p>

<p>Restart the Logstash service.</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo service logstash restart
</li></ul></code></pre>
<p>Then check the Logstash logs for any errors.</p>

<h2 id="logstash-forwarder-how-to-check-if-it-is-running">Logstash Forwarder: How To Check If It is Running</h2>

<p>Logstash Forwarder runs on the your <strong>Client</strong> machines, and ships logs to your ELK server. If Logstash Forwarder isn't running, you won't be able to send your various logs to Logstash. As a result, the logs will not get stored in Elasticsearch, and they will not appear in Kibana. This section will show you how to check if Logstash Forwarder is functioning normally.</p>

<h3 id="verify-logs-are-successfully-being-shipped">Verify Logs Are Successfully Being Shipped</h3>

<p>The easiest way to tell if Logstash Forwarder is properly shipping logs to Logstash is to check its error log. You can look at the Logstash Forwarder logs in real-time with the <code>tail</code> command:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">tail -f /var/log/logstash-forwarder/logstash-forwarder.err
</li></ul></code></pre>
<p>If everything is set up properly, you should see log entries that look like this:</p>
<pre class="code-pre "><code langs=""><div class="secondary-code-label " title="Logstash Forwarder logs (Logs are successfully being shipped):">Logstash Forwarder logs (Logs are successfully being shipped):</div>2015/10/22 12:40:05.022748 Connecting to [10.132.102.48]:5043 (10.132.102.48)
2015/10/22 12:40:05.435781 Connected to 10.132.102.48
2015/10/22 12:40:10.990751 Setting trusted CA from file: /etc/pki/tls/certs/logstash-forwarder.crt
2015/10/22 12:40:10.991384 Connecting to [10.132.102.48]:5043 (10.132.102.48)
2015/10/22 12:40:11.132721 Connected to 10.132.102.48
2015/10/22 12:40:21.699062 Registrar: processing 1024 events
2015/10/22 12:40:25.003609 Registrar: processing 713 events
...
</code></pre>
<p>If you see that the Logstash Forwarder Registrar is processing events, that means that it is shipping logs to Logstash.</p>

<p>If you don't see any log entries, you should verify that Logstash Forwarder is running.</p>

<h3 id="verify-service-is-running">Verify Service is Running</h3>

<p>The most basic thing to check is the status of Logstash Forwarder:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo service logstash-forwarder status
</li></ul></code></pre>
<p>If Logstash Forwarder is running, you will see this output:</p>
<pre class="code-pre "><code langs=""><div class="secondary-code-label " title="Logstash-forwarder status (OK):">Logstash-forwarder status (OK):</div>logstash-forwarder is running
</code></pre>
<p>Otherwise, if the service is not running, you will see this message:</p>
<pre class="code-pre "><code langs=""><div class="secondary-code-label " title="Logstash-forwarder status (Bad):">Logstash-forwarder status (Bad):</div>logstash-forwarder is not running
</code></pre>
<p>If Logstash Forwarder isn't running, try starting it with this command:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo service logstash-forwarder start
</li></ul></code></pre>
<p>Then check the status again. If this doesn't resolve the problem, the following sections will help you troubleshoot your Logstash Forwarder problems.</p>

<p>In the next few sections, we'll cover common Logstash Forwarder issues, and how to resolve them.</p>

<h2 id="issue-logstash-forwarder-is-not-running">Issue: Logstash Forwarder is Not Running</h2>

<p>If Logstash Forwarder is not running on your <strong>client</strong> machine, there are several potential causes. This section will cover a variety of common cases where Logstash Forwarder will fail to run, and propose potential solutions.</p>

<h3 id="cause-configuration-contains-a-syntax-error">Cause: Configuration Contains a Syntax Error</h3>

<p>If Logstash Forwarder has errors in its configuration file, which is located in the <code>/etc/logstash-forwarder.conf</code>, the service will not be able to start properly. The best thing to do is check the Logstash Forwarder error logs for clues about why it is failing:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">tail /etc/logstash-forwarder.conf
</li></ul></code></pre>
<p>If you see log entries that look like this, your configuration file has a syntax error:</p>
<pre class="code-pre "><code langs=""><div class="secondary-code-label " title="Logstash-forwarder logs (Invalid):">Logstash-forwarder logs (Invalid):</div>2015/10/28 17:20:25.047062 Failed unmarshalling json: invalid character '{' looking for beginning of object key string
2015/10/28 17:20:25.047084 Could not load config file /etc/logstash-forwarder.conf: invalid character '{' looking for beginning of object key string
</code></pre>
<p>In this case, that there is a typo in the configuration file. To resolve this issue, edit the offending portion of your Logstash configuration. For guidance, follow the <strong>Configure Logstash Forwarder</strong> subsection of the <a href="https://indiareads/community/tutorials/how-to-install-elasticsearch-logstash-and-kibana-4-on-ubuntu-14-04#set-up-logstash-forwarder-(add-client-servers)">Set Up Logstash Forwarder (Add Client Servers)</a> of the ELK stack tutorial.</p>

<p>After editing the Logstash Forwarder configuration, attempt to start the service again:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo service logstash-forwarder start
</li></ul></code></pre>
<p>Check the Logstash Forwarder logs again, to make sure the issue has been resolved.</p>

<h3 id="cause-ssl-certificate-is-missing-or-invalid">Cause: SSL Certificate is Missing or Invalid</h3>

<p>Communications between Logstash Forwarder and Logstash require an SSL certificate, for authentication and encryption. If Logstash Forwarder is not starting properly, you should check the Logstash Forwarder logs:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">tail /var/log/logstash-forwarder/logstash-forwarder.err
</li></ul></code></pre>
<p>If the <strong>client</strong> machine that is running Logstash Forwarder does not have the Logstash SSL certificate, you will see log entries that look like this:</p>
<pre class="code-pre "><code langs=""><div class="secondary-code-label " title="Logstash-forwarder logs (logstash-forwarder.crt is missing):">Logstash-forwarder logs (logstash-forwarder.crt is missing):</div>2015/10/28 16:48:27.388971 Setting trusted CA from file: <span class="highlight">/etc/pki/tls/certs/logstash-forwarder.crt</span>
2015/10/28 16:48:27.389126 Failure reading CA certificate: open <span class="highlight">/etc/pki/tls/certs/logstash-forwarder.crt</span>: no such file or directory
</code></pre>
<p>This indicates that the <code>logstash-forwarder.crt</code> file is not in the appropriate location. To resolve this issue, copy the SSL certificate from the ELK server to your client machine by following the appropriate subsections of the <a href="https://indiareads/community/tutorials/how-to-install-elasticsearch-logstash-and-kibana-4-on-ubuntu-14-04#set-up-logstash-forwarder-(add-client-servers)">Set Up Logstash Forwarder (Add Client Servers) section</a> of the ELK stack tutorial.</p>

<p>After placing the appropriate SSL certificate file in the proper location, try starting Logstash Forwarder again.</p>

<p>If the SSL certificate is invalid, the logs should look like this:</p>
<pre class="code-pre "><code langs=""><div class="secondary-code-label " title="Logstash-forwarder logs (Certificate is invalid):">Logstash-forwarder logs (Certificate is invalid):</div>2015/10/22 12:39:52.989385 Connecting to [10.132.102.48]:5043 (10.132.102.48)
2015/10/22 12:39:53.010214 Failed to tls handshake with 10.132.102.48 x509: <span class="highlight">certificate is valid for 10.17.0.52, not 10.132.102.48</span>
</code></pre>
<p>Note that the error message indicates that the certificate exists, but it was created with the wrong IP address (the certificate's is for an IP address that does not match the ELK server). In this case, you need to follow the <a href="https://indiareads/community/tutorials/how-to-install-elasticsearch-logstash-and-kibana-4-on-ubuntu-14-04#generate-ssl-certificates">Generate SSL Certificates section</a> of the ELK stack tutorial, then copy the SSL certificate to the client machine (<a href="https://indiareads/community/tutorials/how-to-install-elasticsearch-logstash-and-kibana-4-on-ubuntu-14-04#set-up-logstash-forwarder-(add-client-servers)">Set Up Logstash Forwarder (Add Client Servers)</a>).</p>

<p>After ensuring that the certificate is valid, and that it is in the proper location, you will need to restart Logstash (on the ELK server) to force it to use the new SSL key:</p>
<pre class="code-pre custom_prefix"><code langs=""><ul class="prefixed"><li class="line" prefix="elk$">sudo service logstash restart
</li></ul></code></pre>
<p>Then start Logstash Forwarder (on the client machine):</p>
<pre class="code-pre custom_prefix"><code langs=""><ul class="prefixed"><li class="line" prefix="client$">sudo service logstash-forwarder start
</li></ul></code></pre>
<p>Check the Logstash Forwarder logs again, to make sure the issue has been resolved.</p>

<h3 id="issue-logstash-forwarder-can-39-t-connect-to-logstash">Issue: Logstash Forwarder Can't Connect to Logstash</h3>

<p>If Logstash (on the ELK server) is not reachable by Logstash Forwarder (your client server), you will see error log entries like this:</p>
<pre class="code-pre "><code langs=""><div class="secondary-code-label " title="Logstash-forwarder logs (Connection refused):">Logstash-forwarder logs (Connection refused):</div>2015/10/22 12:39:54.010719 Connecting to [10.132.102.48]:5043 (10.132.102.48)
2015/10/22 12:39:54.011269 Failure connecting to 10.132.102.48: dial tcp 10.132.102.48:5043: connection refused
</code></pre>
<p>Common reasons for Logstash being unreachable include the following:</p>

<ul>
<li>Logstash is not running (on the ELK server)</li>
<li>Firewalls on either server are blocking the connection on port <code>5043</code></li>
<li>Logstash Forwarder is not configured with the proper IP address, hostname, or port</li>
</ul>

<p>To resolve this issue, first verify that Logstash is running on the ELK server by following the Logstash troubleshooting sections of this guide. Second, verify that the firewall is not blocking the network traffic. Third, verify that Logstash Forwarder is configured with the correct IP address (or hostname) and port of the ELK server.</p>

<p>The Logstash Forwarder configuration can be edited with this command:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo vi /etc/logstash-forwarder.conf
</li></ul></code></pre>
<p>After verifying that the Logstash connection information is correct, try restarting Logstash Forwarder:</p>
<pre class="code-pre "><code langs="">sudo service logstash-forwarder restart
</code></pre>
<p>Check the Logstash Forwarder logs again, to make sure the issue has been resolved.</p>

<p>For general Logstash Forwarder guidance, follow the <strong>Configure Logstash Forwarder</strong> subsection of the <a href="https://indiareads/community/tutorials/how-to-install-elasticsearch-logstash-and-kibana-4-on-ubuntu-14-04#set-up-logstash-forwarder-(add-client-servers)">Set Up Logstash Forwarder (Add Client Servers)</a> of the ELK stack tutorial.</p>

<h2 id="elasticsearch-how-to-check-if-it-is-running">Elasticsearch: How To Check If It is Running</h2>

<p>If Elasticsearch isn't running, none of your ELK stack will function. Logstash will not be able to add new logs to Elasticsearch, and Kibana will not be able to retrieve logs from Elasticsearch for reporting. This section will show you how to check if Elasticsearch is functioning normally.</p>

<h3 id="verify-service-is-running">Verify Service is Running</h3>

<p>The most basic thing to check is the status of the Elasticsearch status:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo service elasticsearch status
</li></ul></code></pre>
<p>If Elasticsearch is running, you will see this output:</p>
<pre class="code-pre "><code langs=""><div class="secondary-code-label " title="Elasticsearch status (OK):">Elasticsearch status (OK):</div> * elasticsearch is running
</code></pre>
<p>Otherwise, if the service is not running, you will see this message:</p>
<pre class="code-pre "><code langs=""><div class="secondary-code-label " title="Elasticsearch status (Bad):">Elasticsearch status (Bad):</div> * elasticsearch is not running
</code></pre>
<p>In this case, you should follow the next few sections, which cover troubleshooting Elasticsearch.</p>

<h3 id="verify-that-it-responds-to-http-requests">Verify that it Responds to HTTP Requests</h3>

<p>By default, Elasticsearch responds to HTTP requests on port <code>9200</code> (this can be customized, in its configuration file, by specifying a new <code>http.port</code> value). We can use <code>curl</code> to send requests to, and retrieve useful information from Elasticsearch.</p>

<p>Send an HTTP GET request using curl with this command (assuming that your Elasticsearch can be reached at <code>localhost</code>):</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">curl localhost:9200
</li></ul></code></pre>
<p>If Elasticsearch is running, you should see a response that looks something like this:</p>
<pre class="code-pre "><code langs=""><div class="secondary-code-label " title="curl localhost:9200 output:">curl localhost:9200 output:</div>{
  "status" : 200,
  "name" : "Fan Boy",
  "cluster_name" : "elasticsearch",
  "version" : {
    "number" : "1.7.3",
    "build_hash" : "05d4530971ef0ea46d0f4fa6ee64dbc8df659682",
    "build_timestamp" : "2015-10-15T09:14:17Z",
    "build_snapshot" : false,
    "lucene_version" : "4.10.4"
  },
  "tagline" : "You Know, for Search"
}
</code></pre>
<p>You may also check the health of your Elasticsearch cluster with this command:</p>
<pre class="code-pre "><code langs="">curl localhost:9200/_cluster/health?pretty
</code></pre>
<p>Your output should look something like this:</p>
<pre class="code-pre "><code langs=""><div class="secondary-code-label " title="curl localhost:9200/_cluster/health?pretty output:">curl localhost:9200/_cluster/health?pretty output:</div>{
  "cluster_name" : "elasticsearch",
  "status" : "yellow",
  "timed_out" : false,
  "number_of_nodes" : 1,
  "number_of_data_nodes" : 1,
  "active_primary_shards" : 56,
  "active_shards" : 56,
  "relocating_shards" : 0,
  "initializing_shards" : 0,
  "unassigned_shards" : 56,
  "delayed_unassigned_shards" : 0,
  "number_of_pending_tasks" : 0,
  "number_of_in_flight_fetch" : 0
}
</code></pre>
<p>Note that if your Elasticsearch cluster consists of a single node, your cluster will probably have a <code>yellow</code> status. This is normal for a single node cluster; you can upgrade to a <code>green</code> status by adding at least one more node to your Elasticsearch cluster.</p>

<h2 id="issue-elasticsearch-is-not-running">Issue: Elasticsearch is Not Running</h2>

<p>If Elasticsearch is not running, there are many potential causes. This section will cover a variety of common cases where Elasticsearch will fail to run, and propose potential solutions.</p>

<h3 id="cause-it-was-never-started">Cause: It Was Never Started</h3>

<p>If Elasticsearch isn't running, it may not have been started in the first place; Elasticsearch does not start automatically after installation. The solution to this is to manually start it the first time:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo service elasticsearch start
</li></ul></code></pre>
<p>This should report that Elasticsearch is starting. Wait about 10 seconds, then check the status of the Elasticsearch status again.</p>

<h3 id="cause-elasticsearch-service-was-not-enabled-and-the-server-rebooted">Cause: Elasticsearch service was not enabled, and the server rebooted</h3>

<p>If Elasticsearch was working fine but doesn't work anymore, this may be your issue. By default, the Elasticsearch service is not enabled to start on boot. The solution  means that you must enable Elasticsearch to start automatically on boot:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo update-rc.d elasticsearch defaults 95 10
</li></ul></code></pre>
<p>Elasticsearch should now automatically start on boot. Test that it works by rebooting your server.</p>

<h3 id="cause-elasticsearch-is-misconfigured">Cause: Elasticsearch is Misconfigured</h3>

<p>If Elasticsearch has errors in its configuration file, which is located at <code>/etc/elasticsearch/elasticsearch.yml</code>, the service will not be able to start properly. The best thing to do is check the Elasticsearch error logs for clues about why it is failing.</p>

<p>Open two terminal sessions to your server, so you can view the Elasticsearch logs while trying to start the service.</p>

<p>In the first terminal session, we'll look at the logs:</p>
<pre class="code-pre custom_prefix"><code langs=""><ul class="prefixed"><li class="line" prefix="terminal-1$">tail -f /var/log/elasticsearch/elasticsearch.log
</li></ul></code></pre>
<p>This will display the last few log entries, plus any future log entries.</p>

<p>In the second terminal session, try to start the Elasticsearch service:</p>
<pre class="code-pre custom_prefix"><code langs=""><ul class="prefixed"><li class="line" prefix="terminal-2$">sudo service elasticsearch start
</li></ul></code></pre>
<p>Switch back to the first terminal session to look at the logs that are generated when Elasticsearch is starting up.</p>

<p>If you see log entries that indicate errors or exceptions (e.g. <code>ERROR</code>, <code>Exception</code>, or <code>error</code>), try and find a line that indicates what caused the error. Here is an example of the error logs you will see if the Elasticsearch <code>network.host</code> is set to a hostname or IP address that is not resolvable:</p>
<pre class="code-pre "><code langs=""><div class="secondary-code-label " title="Elasticsearch logs (Bad):">Elasticsearch logs (Bad):</div>...
[2015-10-27 15:24:43,495][INFO ][node                     ] [Shadrac] starting ...
[2015-10-27 15:24:43,626][ERROR][bootstrap                ] [Shadrac] Exception
org.elasticsearch.transport.BindTransportException: Failed to resolve host [null]
    at org.elasticsearch.transport.netty.NettyTransport.bindServerBootstrap(NettyTransport.java:402)
    at org.elasticsearch.transport.netty.NettyTransport.doStart(NettyTransport.java:283)
    at org.elasticsearch.common.component.AbstractLifecycleComponent.start(AbstractLifecycleComponent.java:85)
    at org.elasticsearch.transport.TransportService.doStart(TransportService.java:153)
    at org.elasticsearch.common.component.AbstractLifecycleComponent.start(AbstractLifecycleComponent.java:85)
    at org.elasticsearch.node.internal.InternalNode.start(InternalNode.java:257)
    at org.elasticsearch.bootstrap.Bootstrap.start(Bootstrap.java:160)
    at org.elasticsearch.bootstrap.Bootstrap.main(Bootstrap.java:248)
    at org.elasticsearch.bootstrap.Elasticsearch.main(Elasticsearch.java:32)
Caused by: java.net.UnknownHostException: <span class="highlight">incorrect_hostname</span>: unknown error
...
</code></pre>
<p>Note that the last line of the example logs indicates that an <code>UnknownHostException: <span class="highlight">incorrect_hostname</span></code> error has occurred. This particular example indicates that the <code>network.host</code> is set to <code>incorrect_hostname</code>, which doesn't resolve to anything. In a single-node Elasticsearch setup, this should be set to <code>localhost</code> or <code>127.0.0.1</code>.</p>

<p>To resolve this issue, edit the Elasticsearch configuration file:</p>
<pre class="code-pre custom_prefix"><code langs=""><ul class="prefixed"><li class="line" prefix="terminal-2$">sudo vi /etc/elasticsearch/elasticsearch.yml
</li></ul></code></pre>
<p>Find the line that has the bad entry, and fix it. In the case of the example, we should look for the line that specifies <code>network.host: incorrect_hostname</code> and change it so it looks like this:</p>
<div class="code-label " title="/etc/elasticsearch/elasticsearch.yml excerpt">/etc/elasticsearch/elasticsearch.yml excerpt</div><pre class="code-pre "><code langs="">...
network.host: <span class="highlight">localhost</span>
...
</code></pre>
<p>Save and exit.</p>

<p>Now, on the second terminal, start the Elasticsearch service:</p>
<pre class="code-pre custom_prefix"><code langs=""><ul class="prefixed"><li class="line" prefix="terminal-2$">sudo service elasticsearch start
</li></ul></code></pre>
<p>If the issue has been resolved, you should see error-free logs that indicate that Elasticsearch has started. It might look something like this:</p>
<pre class="code-pre "><code langs=""><div class="secondary-code-label " title="Elasticsearch logs (Good):">Elasticsearch logs (Good):</div>...
[2015-10-27 15:29:21,980][INFO ][node                     ] [Garrison Kane] initializing ...
[2015-10-27 15:29:22,084][INFO ][plugins                  ] [Garrison Kane] loaded [], sites []
[2015-10-27 15:29:22,124][INFO ][env                      ] [Garrison Kane] using [1] data paths, mounts [[/ (/dev/vda1)]], net usable_space [52.1gb], net total_space [58.9gb], types [ext4]
[2015-10-27 15:29:24,532][INFO ][node                     ] [Garrison Kane] initialized
[2015-10-27 15:29:24,533][INFO ][node                     ] [Garrison Kane] starting ...
[2015-10-27 15:29:24,646][INFO ][transport                ] [Garrison Kane] bound_address {inet[/127.0.0.1:9300]}, publish_address {inet[localhost/127.0.0.1:9300]}
[2015-10-27 15:29:24,682][INFO ][discovery                ] [Garrison Kane] elasticsearch/WJvkRFnbQ5mLTgOatk0afQ
[2015-10-27 15:29:28,460][INFO ][cluster.service          ] [Garrison Kane] new_master [Garrison Kane][WJvkRFnbQ5mLTgOatk0afQ][elk-run][inet[localhost/127.0.0.1:9300]], reason: zen-disco-join (elected_as_master)
[2015-10-27 15:29:28,561][INFO ][http                     ] [Garrison Kane] bound_address {inet[/127.0.0.1:9200]}, publish_address {inet[localhost/127.0.0.1:9200]}
[2015-10-27 15:29:28,562][INFO ][node                     ] [Garrison Kane] started
...
</code></pre>
<p>Now if you check the Elasticsearch status, and you should see that it is running fine.</p>

<p>You may have a different configuration problem than our example. If you're able to figure out what the error means, try and fix it yourself. If that fails, try and search the Internet for individual error lines that do not contain information that is specific to your server (e.g. the IP address, or the automatically generated Elasticsearch node name).</p>

<h2 id="conclusion">Conclusion</h2>

<p>Hopefully this troubleshooting guide has helped you resolve any issues you were having with your ELK stack setup. If you have any questions or suggestions, leave them in the comments below!</p>

    