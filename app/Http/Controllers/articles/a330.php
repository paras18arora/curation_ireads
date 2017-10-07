<link rel='stylesheet' href='digital_ocean.css'><br> <br> 
      <h3 id="introduction">Introduction</h3>

<p>In this tutorial, we will cover the installation of Graylog2 (v0.20.2), and configure it to gather the syslogs of our systems in a centralized location. Graylog2 is a powerful log management and analysis tool that has many use cases, from monitoring SSH logins and unusual activity to debugging applications. It is based on Elasticsearch, Java, MongoDB, and Scala.</p>

<p><span class="note"><strong>Note:</strong> This tutorial is for an outdated version of Graylog2. A new version is available here: <a href="https://indiareads/community/tutorials/how-to-install-graylog-1-x-on-ubuntu-14-04">How To Install Graylog 1.x on Ubuntu 14.04</a>.<br /></span></p>

<p>It is possible to use Graylog2 to gather and monitor a large variety of logs, but we will limit the scope of this tutorial to syslog gathering. Also, because we are demonstrating the basics of Graylog2, we will be installing all of the components on a single server.</p>

<h2 id="about-graylog2-components">About Graylog2 Components</h2>

<p>Graylog2 has four main components:</p>

<ul>
<li><strong>Graylog2 Server nodes</strong>: Serves as a worker that receives and processes messages, and communicates with all other non-server components. Its performance is CPU dependent</li>
<li><strong>Elasticsearch nodes</strong>: Stores all of the logs/messages. Its performance is RAM and disk I/O dependent</li>
<li><strong>MongoDB</strong>: Stores metadata and does not experience much load</li>
<li><strong>Web Interface</strong>: The user interface</li>
</ul>

<p>Here is a diagram of the Graylog2 components (note that the messages are sent from your other servers):</p>

<p><img src="https://assets.digitalocean.com/articles/graylog2/graylog_simple_setup_v2.png" alt="Basic Graylog2 Setup" /></p>

<p>For a very basic setup, all of the components can be installed on the same server. For a larger, production setup, it would be wise to set up some high-availability features because if the server, Elasticsearch, or MongoDB components experiences an outage, Graylog2 will not gather the messages generated during the outage.</p>

<h2 id="prerequisites">Prerequisites</h2>

<p>The setup described in this tutorial requires an Ubuntu 14.04 VPS with at least 2GB of RAM. You also need root access (Steps 1-4 of <a href="https://indiareads/community/articles/initial-server-setup-with-ubuntu-14-04">Initial Server Setup with Ubuntu 14.04</a>).</p>

<p>If you use a VPS with less than 2GB of RAM you will not be able to start all of the Graylog2 components.</p>

<p>Let's start installing software!</p>

<h2 id="install-mongodb">Install MongoDB</h2>

<p>The MongoDB installation is simple and quick. Run the following command to import the MongoDB public GPG key into apt:</p>
<pre class="code-pre "><code langs="">sudo apt-key adv --keyserver keyserver.ubuntu.com --recv 7F0CEB10
</code></pre>
<p>Create the MongoDB source list:</p>
<pre class="code-pre "><code langs="">echo 'deb http://downloads-distro.mongodb.org/repo/debian-sysvinit dist 10gen' | sudo tee /etc/apt/sources.list.d/mongodb.list
</code></pre>
<p>Update your apt package database:</p>
<pre class="code-pre "><code langs="">sudo apt-get update
</code></pre>
<p>Install the latest stable version of MongoDB with this command:</p>
<pre class="code-pre "><code langs="">sudo apt-get install mongodb-org
</code></pre>
<p>MongoDB should be up and running now. Let's move on to installing Java 7.</p>

<h2 id="install-java-7">Install Java 7</h2>

<p>Elasticsearch requires Java 7, so we will install that now. We will install Oracle Java 7 because that is what is recommended on elasticsearch.org. It should, however, work fine with OpenJDK, if you decide to go that route.</p>

<p>Add the Oracle Java PPA to apt:</p>
<pre class="code-pre "><code langs="">sudo add-apt-repository ppa:webupd8team/java
</code></pre>
<p>Update your apt package database:</p>
<pre class="code-pre "><code langs="">sudo apt-get update
</code></pre>
<p>Install the latest stable version of Oracle Java 7 with this command (and accept the license agreement that pops up):</p>
<pre class="code-pre "><code langs="">sudo apt-get install oracle-java7-installer
</code></pre>
<p>Now that Java 7 is installed, let's install Elasticsearch.</p>

<h2 id="install-elasticsearch">Install Elasticsearch</h2>

<p>Graylog2 v0.20.2 requires Elasticsearch v.0.90.10. Download and install it with these commands:</p>
<pre class="code-pre "><code langs="">cd ~; wget https://download.elasticsearch.org/elasticsearch/elasticsearch/elasticsearch-0.90.10.deb
sudo dpkg -i elasticsearch-0.90.10.deb
</code></pre>
<p>We need to change the Elasticsearch <em>cluster.name</em> setting. Open the Elasticsearch configuration file:</p>
<pre class="code-pre "><code langs="">sudo vi /etc/elasticsearch/elasticsearch.yml
</code></pre>
<p>Find the section that specifies <code>cluster.name</code>. Uncomment it, and replace the default value with "graylog2", so it looks like the following:</p>
<pre class="code-pre "><code langs="">cluster.name: graylog2
</code></pre>
<p>You will also want to restrict outside access to your Elasticsearch instance (port 9200), so outsiders can't read your data or shutdown your Elasticseach cluster through the HTTP API. Find the line that specifies network.bind_host and uncomment it so it looks like this:</p>
<pre class="code-pre "><code langs="">network.bind_host: localhost
</code></pre>
<p>Then add the following line somewhere in the file, to disable dynamic scripts:</p>
<pre class="code-pre "><code langs="">script.disable_dynamic: true
</code></pre>
<p>Save and quit. Next, restart Elasticsearch to put our changes into effect:</p>
<pre class="code-pre "><code langs="">sudo service elasticsearch restart
</code></pre>
<p>After a few seconds, run the following to test that Elasticsearch is running properly:</p>
<pre class="code-pre "><code langs="">curl -XGET 'http://localhost:9200/_cluster/health?pretty=true'
</code></pre>
<p>Now that Elasticsearch is up and running, let's install the Graylog2 server.</p>

<h2 id="install-graylog2-server">Install Graylog2 server</h2>

<p>Now that we have installed the other required software, let's install the Graylog2 server. We will install Graylog2 Server v0.20.2 in /opt. First, download the Graylog2 archive to /opt with this command:</p>
<pre class="code-pre "><code langs="">cd /opt; sudo wget https://github.com/Graylog2/graylog2-server/releases/download/0.20.2/graylog2-server-0.20.2.tgz
</code></pre>
<p>Then extract the archive:</p>
<pre class="code-pre "><code langs="">sudo tar xvf graylog2-server-0.20.2.tgz
</code></pre>
<p>Let's create a symbolic link to the newly created directory, to simplify the directory name:</p>
<pre class="code-pre "><code langs="">sudo ln -s graylog2-server-0.20.2 graylog2-server
</code></pre>
<p>Copy the example configuration file to the proper location, in /etc:</p>
<pre class="code-pre "><code langs="">sudo cp /opt/graylog2-server/graylog2.conf.example /etc/graylog2.conf
</code></pre>
<p>Install pwgen, which we will use to generate password secret keys:</p>
<pre class="code-pre "><code langs="">sudo apt-get install pwgen
</code></pre>
<p>Now we must configure the <em>admin</em> password and secret key. The password secret key is configured in <em>graylog2.conf</em>, by the <code>password_secret</code> parameter. We can generate a random key and insert it into the Graylog2 configuration with the following two commands:</p>
<pre class="code-pre "><code langs="">SECRET=$(pwgen -s 96 1)
sudo -E sed -i -e 's/password_secret =.*/password_secret = '$SECRET'/' /etc/graylog2.conf
</code></pre>
<p>The <em>admin</em> password is assigned by creating an <code>shasum</code> of the desired password, and assigning it to the <code>root_password_sha2</code> parameter in the Graylog2 configuration file. Create shasum of your desired password with the following command, substituting the highlighted "password" with your own. The sed command inserts it into the Graylog2 configuration for you:</p>

<pre>
PASSWORD=$(echo -n <span class="highlight">password</span> | shasum -a 256 | awk '{print $1}')
sudo -E sed -i -e 's/root_password_sha2 =.*/root_password_sha2 = '$PASSWORD'/' /etc/graylog2.conf
</pre>

<p>Now that the admin password is setup, let's open the Graylog2 configuration to make a few changes:</p>
<pre class="code-pre "><code langs="">sudo vi /etc/graylog2.conf
</code></pre>
<p>You should see that <code>password_secret</code> and <code>root_password_sha2</code> have random strings to them, because of the commands that you ran in the steps above. Now we will configure the <code>rest_transport_uri</code>, which is how the Graylog2 web interface will communicate with the server. Because we are installing all of the components on a single server, let's set the value to 127.0.0.1, or localhost. Find and uncomment <code>rest_transport_uri</code>, and change it's value so it looks like the following:</p>

<pre>
rest_transport_uri = http://<span class="highlight">127.0.0.1</span>:12900/
</pre>

<p>Next, because we only have one Elasticsearch shard (which is running on this server), we will change the value of <code>elasticsearch_shards</code> to 1:</p>

<pre>
elasticsearch_shards = <span class="highlight">1</span>
</pre>

<p>Save and quit. Now our Graylog2 server is configured and ready to be started.</p>

<p><strong>Optional</strong>: If you want to test it out, run the following command:</p>
<pre class="code-pre "><code langs="">sudo java -jar /opt/graylog2-server/graylog2-server.jar --debug
</code></pre>
<p>You should see a lot of output. Once you see output similar to the following lines, you will know that your Graylog2 server was configured correctly:</p>
<pre class="code-pre "><code langs="">2014-06-06 14:16:13,420 INFO : org.graylog2.Core - Started REST API at <http://127.0.0.1:12900/>
2014-06-06 14:16:13,421 INFO : org.graylog2.Main - Graylog2 up and running.
</code></pre>
<p>Press <code>CTRL-C</code> to kill the test and return to the shell.</p>

<p>Now let's install the Graylog2 init script. Copy <code>graylog2ctl</code> to /etc/init.d:</p>
<pre class="code-pre "><code langs="">sudo cp /opt/graylog2-server/bin/graylog2ctl /etc/init.d/graylog2
</code></pre>
<p>Update the startup script to put the Graylog2 logs in <code>/var/log</code> and to look for the Graylog2 server JAR file in <code>/opt/graylog2-server</code> by running the two following sed commands:</p>

<pre>
sudo sed -i -e 's/GRAYLOG2_SERVER_JAR=\${GRAYLOG2_SERVER_JAR:=graylog2-server.jar}/GRAYLOG2_SERVER_JAR=\${GRAYLOG2_SERVER_JAR:=<span class="highlight">\/opt\/graylog2-server\/</span>graylog2-server.jar}/' /etc/init.d/graylog2
sudo sed -i -e 's/LOG_FILE=\${LOG_FILE:=log\/graylog2-server.log}/LOG_FILE=\${LOG_FILE:=<span class="highlight">\/var\/log\/</span>graylog2-server.log}/' /etc/init.d/graylog2
</pre>

<p>Next, install the startup script:</p>
<pre class="code-pre "><code langs="">sudo update-rc.d graylog2 defaults
</code></pre>
<p>Now we can start the Graylog2 server with the service command:</p>
<pre class="code-pre "><code langs="">sudo service graylog2 start
</code></pre>
<p>The next step is to install the Graylog2 web interface. Let's do that now!</p>

<h2 id="install-graylog2-web-interface">Install Graylog2 Web Interface</h2>

<p>We will download and install the Graylog2 v.0.20.2 web interface in /opt with the following commands:</p>
<pre class="code-pre "><code langs="">cd /opt; sudo wget https://github.com/Graylog2/graylog2-web-interface/releases/download/0.20.2/graylog2-web-interface-0.20.2.tgz
sudo tar xvf graylog2-web-interface-0.20.2.tgz
</code></pre>
<p>Let's create a symbolic link to the newly created directory, to simplify the directory name:</p>
<pre class="code-pre "><code langs="">sudo ln -s graylog2-web-interface-0.20.2 graylog2-web-interface
</code></pre>
<p>Next, we want to configure the web interface's secret key, the <code>application.secret</code> parameter in <em>graylog2-web-interface.conf</em>. We will generate another key, as we did with the Graylog2 server configuration, and insert it with sed, like so:</p>
<pre class="code-pre "><code langs="">SECRET=$(pwgen -s 96 1)
sudo -E sed -i -e 's/application\.secret=""/application\.secret="'$SECRET'"/' /opt/graylog2-web-interface/conf/graylog2-web-interface.conf
</code></pre>
<p>Now open the web interface configuration file, with this command:</p>
<pre class="code-pre "><code langs="">sudo vi /opt/graylog2-web-interface/conf/graylog2-web-interface.conf
</code></pre>
<p>Now we need to update the web interface's configuration to specify the <code>graylog2-server.uris</code> parameter. This is a comma delimited list of the server REST URIs. Since we only have one Graylog2 server node, the value should match that of <code>rest_listen_uri</code> in the Graylog2 server configuration (i.e. "http://127.0.0.1:12900/"). </p>

<pre>
graylog2-server.uris="<span class="highlight">http://127.0.0.1:12900/</span>"
</pre>

<p>The Graylog2 web interface is now configured. Let's start it up to test it out:</p>
<pre class="code-pre "><code langs="">sudo /opt/graylog2-web-interface-0.20.2/bin/graylog2-web-interface
</code></pre>
<p>You will know it started properly when you see the following two lines:</p>
<pre class="code-pre "><code langs="">[info] play - Application started (Prod)
[info] play - Listening for HTTP on /0:0:0:0:0:0:0:0:9000
</code></pre>
<p>Hit <code>CTRL-C</code> to kill the web interface. Now let's install a startup script. You can either create your own, or download one that I created for this tutorial. To download the script to your home directory, use this command:</p>
<pre class="code-pre "><code langs="">cd ~; wget https://assets.digitalocean.com/articles/graylog2/graylog2-web
</code></pre>
<p>Next, you will want to copy it to <code>/etc/init.d</code>, and change its ownership to <code>root</code> and its permissions to <code>755</code>:</p>
<pre class="code-pre "><code langs="">sudo cp ~/graylog2-web /etc/init.d/
sudo chown root:root /etc/init.d/graylog2-web
sudo chmod 755 /etc/init.d/graylog2-web
</code></pre>
<p>Now you can install the web interface init script with this command:</p>
<pre class="code-pre "><code langs="">sudo update-rc.d graylog2-web defaults
</code></pre>
<p>Start the Graylog2 web interface:</p>
<pre class="code-pre "><code langs="">sudo service graylog2-web start
</code></pre>
<p>Now we can use the Graylog2 web interface. Let's do that now.</p>

<h2 id="configure-graylog2-to-receive-syslog-messages">Configure Graylog2 to Receive syslog messages</h2>

<h3 id="log-into-graylog2-web-interface">Log into Graylog2 Web Interface</h3>

<p>In your favorite browser, go to the port 9000 of your VPS's public IP address:</p>

<pre>
http://<span class="highlight">gl2_public_IP</span>:9000/
</pre>

<p>You should see a login screen. Enter "admin" as your username and the password the admin password that you set earlier.</p>

<p>Once logged in, you will see something like the following:</p>

<p><img src="https://assets.digitalocean.com/articles/graylog2/2-dashboard.png" alt="Graylog2 Dashboard" /></p>

<p>The flashing red "1" is a notification. If you click on it, you will see a message that says you have a node without any running <em>inputs</em>. Let's add an input to receive syslog messages over UDP now.</p>

<h3 id="create-syslog-udp-input">Create Syslog UDP Input</h3>

<p>To add an input to receive syslog messages, click on <em>Inputs</em> in the <em>System</em> menu on the right side.</p>

<p>Now, from the drop-down menu, select <em>Syslog UDP</em> and click <em>Launch new input</em>.</p>

<p>A "Launch a new input <em>Syslog UDP</em>" window will pop up. Enter the following information:</p>

<ul>
<li>Title: syslog</li>
<li>Port: 514 </li>
<li>Bind address: <code>gl2_private_IP</code></li>
</ul>

<p>Then click <em>Launch</em>.</p>

<p>You should now see an input named "syslog" in <em>Running local inputs section</em> (and it should have a green box that says "running" in it), like so:</p>

<p><img src="https://assets.digitalocean.com/articles/graylog/inputs.png" alt="Graylog syslog input" /></p>

<p>Now our Graylog2 server is ready to receive syslog messages from your servers. Let's configure our servers to send their syslog messages to Graylog2 now.</p>

<h3 id="configure-rsyslog-to-send-to-your-graylog2-server">Configure rsyslog to Send to Your Graylog2 server</h3>

<p>On all of the servers that you want to send syslog messages to Graylog2, do the following steps.</p>

<p>Create an rsyslog configuration file in /etc/rsyslog.d. We will call ours <code>90-graylog2.conf</code>:</p>
<pre class="code-pre "><code langs="">sudo vi /etc/rsyslog.d/90-graylog2.conf
</code></pre>
<p>In this file, add the following lines to configure rsyslog to send syslog messages to your Graylog2 server (replace <code>gl2_private_IP</code> with your Graylog2 server's private IP address):</p>

<pre>
$template GRAYLOGRFC5424,"%protocol-version% %timestamp:::date-rfc3339% %HOSTNAME% %app-name% %procid% %msg%\n"
*.* @<span class="highlight">gl2_private_IP</span>:514;GRAYLOGRFC5424
</pre>

<p>Save and quit. This file will be loaded as part of your rsyslog configuration from now on. Now you need to restart rsyslog to put your change into effect.</p>
<pre class="code-pre "><code langs="">sudo service rsyslog restart
</code></pre>
<p>After you are finished configuring rsyslog on all of the servers you want to monitor, let's go back to the Graylog2 web interface.</p>

<h2 id="viewing-your-graylog2-sources">Viewing Your Graylog2 Sources</h2>

<p>In your favorite browser, go to the port 9000 of your VPS's public IP address:</p>

<pre>
http://<span class="highlight">gl2_public_IP</span>:9000/
</pre>

<p>Click on <em>Sources</em> in the top bar. You will see a list of all of the servers that you configured rsyslog on. Here is an example of what it might look like:</p>

<p><img src="https://assets.digitalocean.com/articles/graylog2/sources.png" alt="Graylog2 Sources" /></p>

<p>The hostname of the sources is on the left, with the number of messages received by Graylog2 on the right.</p>

<h2 id="searching-your-graylog2-data">Searching Your Graylog2 Data</h2>

<p>After letting your Graylog2 collect messages for some time, you will be able to search through the messages. As an example, let's search for "sshd" to see what kind of SSH activity is happening on our servers. Here is a snippet of our results:</p>

<p><img src="https://assets.digitalocean.com/articles/graylog2/search_sshd.png" alt="Graylog2 Example Search" /></p>

<p>As you can see, our example search results revealed sshd logs for various servers, and a lot of failed root login attempts. Your results may vary, but it can help you to identify many issues, including how unauthorized users are attempting to access your servers.</p>

<p>In addition to the basic search functionality on all of your sources, you can search the logs of a specific host, or in a specific time frame.</p>

<p>Searching through data in Graylog2 is useful, for example, if you would like to review the logs of a server or several servers after an incident has occurred. Centralized logging makes it easier to correlate related incidents because you do not need to log into multiple servers to see all the events that have happened.</p>

<p>For more information on how the search bar works, check out the official documentation: <a href="http://support.torch.sh/help/kb/graylog2-web-interface/the-search-bar-explained">The Search Bar Explained</a></p>

<h2 id="conclusion">Conclusion</h2>

<p>Now that you have Graylog2 set up, feel free to explore the other functionality that it offers. You can send other types of logs into Graylog2, and set up extractors (or reformat logs with software like logstash) to make the logs more structured and searchable. You can also look into expanding your Graylog2 environment by separating the components and adding redundancy to increase performance and availability.</p>

<p>Good luck!</p>

<div class="author">By Mitchell Anicas</div>

    