<link rel='stylesheet' href='digital_ocean.css'><br><img src=https://community-cdn-digitalocean-com.global.ssl.fastly.net/assets/tutorials/images/large/Graylog_tutorial.png?1459466092/> <br> 
      <h3 id="introduction">Introduction</h3>

<p>In this tutorial, we will cover how to install Graylog v1.3.x (sometimes referred to as Graylog2) on Ubuntu 14.04, and configure it to gather the syslogs of your systems in a centralized location. Graylog is a powerful log management and analysis tool that has many use cases, from monitoring SSH logins and unusual activity to debugging applications. It is based on Elasticsearch, Java, and MongoDB.</p>

<p>It is possible to use Graylog to gather and monitor a large variety of logs, but we will limit the scope of this tutorial to syslog gathering. Also, because we are demonstrating the basics of Graylog, we will be installing all of the components on a single server.</p>

<h2 id="about-graylog-components">About Graylog Components</h2>

<p>Graylog has four main components:</p>

<ul>
<li><strong>Graylog Server nodes</strong>: Serves as a worker that receives and processes messages, and communicates with all other non-server components. Its performance is CPU dependent</li>
<li><strong>Elasticsearch nodes</strong>: Stores all of the logs/messages. Its performance is RAM and disk I/O dependent</li>
<li><strong>MongoDB</strong>: Stores metadata and does not experience much load</li>
<li><strong>Web Interface</strong>: The user interface</li>
</ul>

<p>Here is a diagram of the Graylog components (note that the messages are sent from your other servers):</p>

<p><img src="https://assets.digitalocean.com/articles/graylog2/graylog_simple_setup_v2.png" alt="Basic Graylog Setup" /></p>

<p>This tutorial will implement a very basic Graylog setup, with all of the components installed on the same server. For a larger, production setup, it is advisable to set up install the components on separate servers for performance reasons.</p>

<h2 id="prerequisites">Prerequisites</h2>

<p>The setup described in this tutorial requires an Ubuntu 14.04 server with at least 2GB of RAM. You also need root access (Steps 1-4 of <a href="https://indiareads/community/articles/initial-server-setup-with-ubuntu-14-04">Initial Server Setup with Ubuntu 14.04</a>).</p>

<p>If you use a VPS with less than 2GB of RAM you will not be able to start all of the Graylog components.</p>

<p>Let's start installing software!</p>

<h2 id="install-mongodb">Install MongoDB</h2>

<p>The MongoDB installation is simple and quick. Run the following command to import the MongoDB public GPG key into apt:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo apt-key adv --keyserver keyserver.ubuntu.com --recv 7F0CEB10
</li></ul></code></pre>
<p>Create the MongoDB source list:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">echo "deb http://repo.mongodb.org/apt/ubuntu "$(lsb_release -sc)"/mongodb-org/3.0 multiverse" | sudo tee /etc/apt/sources.list.d/mongodb-org-3.0.list
</li></ul></code></pre>
<p>Update your apt package database:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo apt-get update
</li></ul></code></pre>
<p>Install the latest stable version of MongoDB with this command:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo apt-get install mongodb-org
</li></ul></code></pre>
<p>MongoDB should be up and running now. Let's move on to installing Java.</p>

<h2 id="install-java">Install Java</h2>

<p>Elasticsearch requires Java, so we will install that now. We will install Oracle Java 8 because that is what is recommended by Elastic. It should, however, work fine with OpenJDK, if you decide to go that route.</p>

<p>Add the Oracle Java PPA to apt:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo add-apt-repository ppa:webupd8team/java
</li></ul></code></pre>
<p>Update your apt package database:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo apt-get update
</li></ul></code></pre>
<p>Install the latest stable version of Oracle Java 8 with this command (and accept the license agreement that pops up):</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo apt-get install oracle-java8-installer
</li></ul></code></pre>
<p>Now that Java is installed, let's install Elasticsearch.</p>

<h2 id="install-elasticsearch">Install Elasticsearch</h2>

<p>Graylog 1.x only works with pre-2.0 versions of Elasticsearch, so we will install Elasticsearch 1.7.x. Elasticsearch can be installed with a package manager by adding Elastic's package source list.</p>

<p>Run the following command to import the Elasticsearch public GPG key into apt:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">wget -qO - https://packages.elastic.co/GPG-KEY-elasticsearch | sudo apt-key add -
</li></ul></code></pre>
<p>If your prompt is just hanging there, it is probably waiting for your user's password (to authorize the <code>sudo</code> command). If this is the case, enter your password.</p>

<p>Create the Elasticsearch source list:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">echo "deb http://packages.elastic.co/elasticsearch/1.7/debian stable main" | sudo tee -a /etc/apt/sources.list.d/elasticsearch-1.7.x.list
</li></ul></code></pre>
<p>Update your apt package database:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo apt-get update
</li></ul></code></pre>
<p>Install Elasticsearch with this command:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo apt-get -y install elasticsearch
</li></ul></code></pre>
<p>Elasticsearch is now installed.  Let's edit the configuration:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo vi /etc/elasticsearch/elasticsearch.yml
</li></ul></code></pre>
<p>Find the section that specifies <code>cluster.name</code>. Uncomment it, and replace the default value with "graylog-development", so it looks like the following:</p>
<div class="code-label " title="elasticsearch.yml — 1 of 2">elasticsearch.yml — 1 of 2</div><pre class="code-pre "><code langs="">cluster.name: graylog-development
</code></pre>
<p>You will want to restrict outside access to your Elasticsearch instance (port 9200), so outsiders can't read your data or shutdown your Elasticsearch cluster through the HTTP API. Find the line that specifies <code>network.host</code>, uncomment it, and replace its value with "localhost" so it looks like this:</p>
<div class="code-label " title="elasticsearch.yml — 2 of 2">elasticsearch.yml — 2 of 2</div><pre class="code-pre "><code langs="">network.host: localhost
</code></pre>
<p>Save and exit <code>elasticsearch.yml</code>.</p>

<p>Now start Elasticsearch:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo service elasticsearch restart
</li></ul></code></pre>
<p>Then run the following command to start Elasticsearch on boot up:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo update-rc.d elasticsearch defaults 95 10
</li></ul></code></pre>
<p>After a few moments, run the following to test that Elasticsearch is running properly:</p>
<pre class="code-pre "><code langs="">curl -XGET 'http://localhost:9200/_cluster/health?pretty=true'
</code></pre>
<p>Now that Elasticsearch is up and running, let's install the Graylog server.</p>

<h2 id="install-graylog-server">Install Graylog Server</h2>

<p>Now that we have installed the other required software, let's install the server component of Graylog, <code>graylog-server</code>.</p>

<p>First, download the Graylog Debian package to your home directory with these commands:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">cd ~
</li><li class="line" prefix="$">wget https://packages.graylog2.org/repo/packages/graylog-1.3-repository-ubuntu14.04_latest.deb
</li></ul></code></pre>
<p>Then add the package to your package manager with this command:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo dpkg -i graylog-1.3-repository-ubuntu14.04_latest.deb
</li></ul></code></pre>
<p>Then install the <code>graylog-server</code> package with these commands:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo apt-get update
</li><li class="line" prefix="$">sudo apt-get install apt-transport-https
</li><li class="line" prefix="$">sudo apt-get install graylog-server
</li></ul></code></pre>
<p>Install pwgen, which we will use to generate password secret keys:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo apt-get install pwgen
</li></ul></code></pre>
<p>Now we must configure the <em>admin</em> password and secret key. The password secret key is configured in <em>server.conf</em>, by the <code>password_secret</code> parameter. We can generate a random key and insert it into the Graylog configuration with the following two commands:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">SECRET=$(pwgen -s 96 1)
</li><li class="line" prefix="$">sudo -E sed -i -e 's/password_secret =.*/password_secret = '$SECRET'/' /etc/graylog/server/server.conf
</li></ul></code></pre>
<p>The <em>admin</em> password is assigned by creating an <code>shasum</code> of the desired password, and assigning it to the <code>root_password_sha2</code> parameter in the Graylog configuration file. Create shasum of your desired password with the following command, substituting the highlighted "password" with your own. The sed command inserts it into the Graylog configuration for you:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">PASSWORD=$(echo -n <span class="highlight">password</span> | shasum -a 256 | awk '{print $1}')
</li><li class="line" prefix="$">sudo -E sed -i -e 's/root_password_sha2 =.*/root_password_sha2 = '$PASSWORD'/' /etc/graylog/server/server.conf
</li></ul></code></pre>
<p>Now that the admin password is setup, let's open the Graylog configuration to make a few changes:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo vi /etc/graylog/server/server.conf
</li></ul></code></pre>
<p>You should see that <code>password_secret</code> and <code>root_password_sha2</code> have random strings to them because of the commands that you ran in the steps above.</p>

<p>Now we will configure the <code>rest_transport_uri</code>, which is how the Graylog web interface will communicate with the server. Because we are installing all of the components on a single server, let's set the value to <code>127.0.0.1</code>, or <code>localhost</code>. Find and uncomment <code>rest_transport_uri</code>, and change it's value so it looks like the following:</p>
<div class="code-label " title="/etc/graylog/server/server.conf — 1 of 4">/etc/graylog/server/server.conf — 1 of 4</div><pre class="code-pre "><code langs="">rest_transport_uri = http://127.0.0.1:12900/
</code></pre>
<p>Next, because we only have one Elasticsearch shard (which is running on this server), we will change the value of <code>elasticsearch_shards</code> to 1:</p>
<div class="code-label " title="/etc/graylog/server/server.conf — 2 of 4">/etc/graylog/server/server.conf — 2 of 4</div><pre class="code-pre "><code langs="">elasticsearch_shards = <span class="highlight">1</span>
</code></pre>
<p>Next, change the value of <code>elasticsearch_cluster_name</code> to "graylog-development" (the same as the Elasticsearch <code>cluster.name</code>):</p>
<div class="code-label " title="/etc/graylog/server/server.conf — 3 of 4">/etc/graylog/server/server.conf — 3 of 4</div><pre class="code-pre "><code langs="">elasticsearch_cluster_name = graylog-development
</code></pre>
<p>Uncomment these two lines to discover the Elasticsearch instance using unicast instead of multicast:</p>
<div class="code-label " title="/etc/graylog/server/server.conf — 4 of 4">/etc/graylog/server/server.conf — 4 of 4</div><pre class="code-pre "><code langs="">elasticsearch_discovery_zen_ping_multicast_enabled = false
elasticsearch_discovery_zen_ping_unicast_hosts = 127.0.0.1:9300
</code></pre>
<p>Save and quit. Now <code>graylog-server</code> is configured and ready to be started.</p>

<p>Start the Graylog server with the service command:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo start graylog-server
</li></ul></code></pre>
<p>The next step is to install the Graylog web interface. Let's do that now!</p>

<h2 id="install-graylog-web">Install Graylog Web</h2>

<p>Install Graylog Web with the following commands:</p>
<pre class="code-pre "><code langs="">sudo apt-get install graylog-web
</code></pre>
<p>Next, we want to configure the web interface's secret key, the <code>application.secret</code> parameter in <strong>web.conf</strong>. We will generate another key, as we did with the Graylog server configuration, and insert it with sed, like so:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">SECRET=$(pwgen -s 96 1)
</li><li class="line" prefix="$">sudo -E sed -i -e 's/application\.secret=""/application\.secret="'$SECRET'"/' /etc/graylog/web/web.conf
</li></ul></code></pre>
<p>Now open the web interface configuration file, with this command:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo vi /etc/graylog/web/web.conf
</li></ul></code></pre>
<p>Now we need to update the web interface's configuration to specify the <code>graylog2-server.uris</code> parameter. This is a comma delimited list of the server REST URIs. Since we only have one Graylog server node, the value should match that of <code>rest_listen_uri</code> in the Graylog server configuration (i.e. "http://127.0.0.1:12900/"). </p>
<div class="code-label " title="/etc/graylog/web/web.conf excerpt">/etc/graylog/web/web.conf excerpt</div><pre class="code-pre "><code langs="">graylog2-server.uris="http://127.0.0.1:12900/"
</code></pre>
<p>The Graylog web interface is now configured. Start the Graylog web interface:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo start graylog-web
</li></ul></code></pre>
<p>Now we can use the Graylog web interface. Let's do that now.</p>

<h2 id="configure-graylog-to-receive-syslog-messages">Configure Graylog to Receive syslog messages</h2>

<h3 id="log-into-graylog-web-interface">Log into Graylog Web Interface</h3>

<p>In your favorite web browser, go to the port <code>9000</code> of your server's public IP address:</p>
<pre class="code-pre "><code langs=""><div class="secondary-code-label " title="In a web browser:">In a web browser:</div>http://<span class="highlight">graylog_public_IP</span>:9000/
</code></pre>
<p>You should see a login screen. Enter <code>admin</code> as your username and the admin password that you set earlier.</p>

<p>Once logged in, you will see something like the following:</p>

<p><img src="https://assets.digitalocean.com/articles/graylog/getting_started.png" alt="Graylog Dashboard" /></p>

<p>The red number at the top is a notification. If you click on it, you will see a message that says you have a node without any running inputs. Let's add an input to receive syslog messages over UDP now.</p>

<h3 id="create-syslog-udp-input">Create Syslog UDP Input</h3>

<p>To add an input to receive syslog messages, click on the <strong>System</strong> drop-down in the top menu.</p>

<p>Now, from the drop-down menu, select <strong>Inputs</strong>.</p>

<p>Select <strong>Syslog UDP</strong>  from the drop-down menu and click the <strong>Launch new input</strong> button.</p>

<p>A "Launch a new input: <em>Syslog UDP</em>" modal window will pop up. Enter the following information (substitute in your server's private IP address for the bind address):</p>

<ul>
<li><strong>Title:</strong> <code>syslog</code></li>
<li><strong>Port:</strong> <code>8514</code></li>
<li><strong>Bind address:</strong> <code>graylog_private_IP</code></li>
</ul>

<p>Then click <strong>Launch</strong>.</p>

<p>You should now see an input named "syslog" in the <strong>Local inputs</strong> section (and it should have a green box that says "running" next to it), like so:</p>

<p><img src="https://assets.digitalocean.com/articles/graylog/inputs.png" alt="Graylog syslog input" /></p>

<p>Now our Graylog server is ready to receive syslog messages on port <code>8514</code> from your servers. Let's configure your servers to send their syslog messages to Graylog now.</p>

<h2 id="configure-rsyslog-to-send-syslogs-to-graylog-server">Configure Rsyslog to Send Syslogs to Graylog Server</h2>

<p>On all of your <strong>client servers</strong>, the servers that you want to send syslog messages to Graylog, do the following steps.</p>

<p>Create an rsyslog configuration file in /etc/rsyslog.d. We will call ours <code>90-graylog.conf</code>:</p>
<pre class="code-pre custom_prefix"><code langs=""><ul class="prefixed"><li class="line" prefix="client$">sudo vi /etc/rsyslog.d/90-graylog.conf
</li></ul></code></pre>
<p>In this file, add the following lines to configure rsyslog to send syslog messages to your Graylog server (replace <code>graylog_private_IP</code> with your Graylog server's private IP address):</p>
<div class="code-label " title="/etc/rsyslog.d/90-graylog.conf">/etc/rsyslog.d/90-graylog.conf</div><pre class="code-pre "><code langs="">$template GRAYLOGRFC5424,"<%pri%>%protocol-version% %timestamp:::date-rfc3339% %HOSTNAME% %app-name% %procid% %msg%\n"
*.* @<span class="highlight">graylog_private_IP</span>:8514;GRAYLOGRFC5424
</code></pre>
<p>Save and quit. This file will be loaded as part of your rsyslog configuration from now on. Now you need to restart rsyslog to put your change into effect.</p>
<pre class="code-pre custom_prefix"><code langs=""><ul class="prefixed"><li class="line" prefix="client$">sudo service rsyslog restart
</li></ul></code></pre>
<p>After you are finished configuring rsyslog on all of the servers you want to monitor, go back to the Graylog web interface.</p>

<h2 id="viewing-your-graylog-sources">Viewing Your Graylog Sources</h2>

<p>In your favorite web browser, go to the port <code>9000</code> of your server's public IP address:</p>
<pre class="code-pre "><code langs=""><div class="secondary-code-label " title="In a web browser:">In a web browser:</div>http://<span class="highlight">graylog_public_IP</span>:9000/
</code></pre>
<p>Click on <strong>Sources</strong> in the top bar. You will see a list of all of the servers that you configured rsyslog on.</p>

<p>The hostname of the sources is on the left, with the number of messages received by Graylog on the right.</p>

<h2 id="searching-your-graylog-data">Searching Your Graylog Data</h2>

<p>After letting your Graylog collect messages for some time, you will be able to search through the messages. As an example, let's search for "sshd" to see what kind of SSH activity is happening on our servers. Here is a snippet of our results:</p>

<p><img src="https://assets.digitalocean.com/articles/graylog/search_sshd.png" alt="Graylog Example Search" /></p>

<p>As you can see, our example search results revealed sshd logs for various servers, and a lot of failed root login attempts. Your results may vary, but it can help you to identify many issues, including how unauthorized users are attempting to access your servers.</p>

<p>In addition to the basic search functionality on all of your sources, you can search the logs of a specific host, or in a specific time frame.</p>

<p>Searching through data in Graylog is useful, for example, if you would like to review the logs of a server or several servers after an incident has occurred. Centralized logging makes it easier to correlate related incidents because you do not need to log into multiple servers to see all the events that have happened.</p>

<p>For more information on how the search bar works, check out the official documentation: <a href="http://docs.graylog.org/en/1.3/pages/queries.html">Graylog Searching</a></p>

<h2 id="conclusion">Conclusion</h2>

<p>Now that you have Graylog set up, feel free to explore the other functionality that it offers. You can send other types of logs into Graylog, and set up extractors (or reformat logs with software like logstash) to make the logs more structured and searchable. You can also look into expanding your Graylog environment by separating the components and adding redundancy to increase performance and availability.</p>

<p>Good luck!</p>

    