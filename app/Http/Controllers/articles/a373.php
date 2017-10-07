<link rel='stylesheet' href='digital_ocean.css'><br> <br> 
      <h3 id="introduction">Introduction</h3>

<p>OrientDB is a multi-model, NoSQL database with support for graph and document databases. It is a Java application and can run on any operating system. It's also fully ACID-complaint with support for multi-master replication.</p>

<p>In this article, you'll learn how to install and configure the latest Community edition of OrientDB on an Ubuntu 14.04 server.</p>

<h2 id="prerequisites">Prerequisites</h2>

<p>To follow this tutorial, you will need the following:</p>

<ul>
<li>Ubuntu 14.04 Droplet</li>
<li>Non-root user with sudo privileges (<a href="https://indiareads/community/tutorials/initial-server-setup-with-ubuntu-14-04">Initial Server Setup with Ubuntu 14.04</a> explains how to set this up.)</li>
</ul>

<h2 id="step-1-—-installing-oracle-java">Step 1 — Installing Oracle Java</h2>

<p>OrientDB is a Java application that requires Java version 1.6 or higher. Because it's much faster than Java 6 and 7, Java 8 is highly recommended. And that's the version of Java we'll install in this step.</p>

<p>To install Java JRE, add the following Personal Package Archives (PPA):</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo add-apt-repository ppa:webupd8team/java
</li></ul></code></pre>
<p>Update the package database:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo apt-get update
</li></ul></code></pre>
<p>Then install Oracle Java. Installing it using this particular package not only installs it, but also makes it the default Java JRE. When prompted, accept the license agreement:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo apt-get install oracle-java8-set-default
</li></ul></code></pre>
<p>After installing it, verify that it's now the default Java JRE:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">java -version
</li></ul></code></pre>
<p>The expected output is as follows (the exact version may vary):</p>
<pre class="code-pre "><code langs=""><div class="secondary-code-label " title="output">output</div>java version "1.8.0_60"
Java(TM) SE Runtime Environment (build 1.8.0_60-b27)
Java HotSpot(TM) 64-Bit Server VM (build 25.60-b23, mixed mode)
</code></pre>
<h2 id="step-2-—-downloading-and-installing-orientdb">Step 2 — Downloading and Installing OrientDB</h2>

<p>In this step, we'll download and install the latest Community edition of OrientDB. At the time of this publication, OrientDB Community 2.1.3 is the latest version. If a newer version has been released, change the version number to match:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">wget https://orientdb.com/download.php?file=orientdb-community-2.1.3.tar.gz
</li></ul></code></pre>
<p>The downloaded tarball contains pre-compiled binary files that you need to run OrientDB on your system, so all you need to do is untar it to a suitable directory. Since the <code>/opt</code> is the traditional location for third party programs on Linux, let's untar it there:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo tar -xf download.php?file=orientdb-community-2.1.3.tar.gz -C /opt
</li></ul></code></pre>
<p>The files are extracted into a directory named <code>orientdb-community-2.1.3</code>. To make it easier to work with, let's rename it:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo mv /opt/orientdb-community-2.1.3 /opt/orientdb
</li></ul></code></pre>
<h2 id="step-3-—-starting-the-server">Step 3 — Starting the Server</h2>

<p>Now that the binary is in place, you can start the server and connect to the console. Before that, navigate to the installation directory:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">cd /opt/orientdb
</li></ul></code></pre>
<p>Then start the server:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo bin/server.sh
</li></ul></code></pre>
<p>Aside from generating a bunch of output, by starting the server for the first time, you'll be prompted to specify a password for the <strong>root</strong> user account. This is an internal OrientDB account that will be used to access the server. For example, it's the username and password combination that will be used to access OrientDB Studio, the web-based interface for managing OrientDB. If you don't specify a password, one will be generated automatically. However, it's best to specify one yourself, do so when prompted.</p>

<p>Part of the output generated from starting the server tells you what ports the server and OrientDB Studio are listening on:</p>
<pre class="code-pre "><code langs=""><div class="secondary-code-label " title="Output">Output</div>2015-10-12 11:27:45:095 INFO  Databases directory: /opt/orientdb/databases [OServer]
2015-10-12 11:27:45:263 INFO  Listening binary connections on 0.0.0.0:<span class="highlight">2424</span> (protocol v.32, socket=default) [OServerNetworkListener]
2015-10-12 11:27:45:285 INFO  Listening http connections on 0.0.0.0:<span class="highlight">2480</span> (protocol v.10, socket=default) [OServerNetworkListener]

...

2015-10-12 11:27:45:954 INFO  OrientDB Server v2.1.3 (build UNKNOWN@r; 2015-10-04 10:56:30+0000) is active. [OServer]
</code></pre>
<p>Since OrientDB is now running in your terminal window, in a second terminal window to the same Droplet, confirm that the server is listening on ports 2424 (for binary connections) and 2480 (for HTTP connections). To confirm that it's listening for binary connections, execute:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo netstat -plunt | grep 2424
</li></ul></code></pre>
<p>The output should look similar to</p>
<pre class="code-pre "><code langs=""><div class="secondary-code-label " title="Output">Output</div>tcp6       0      0 :::2424                 :::*                    LISTEN      1617/java
</code></pre>
<p>To confirm that it's listening for HTTP connections, execute:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo netstat -plunt | grep 2480
</li></ul></code></pre>
<p>The expected output is as follows:</p>
<pre class="code-pre "><code langs=""><div class="secondary-code-label " title="Output">Output</div>tcp6       0      0 :::2480                 :::*                    LISTEN      1617/java
</code></pre>
<h2 id="step-4-—-connecting-to-the-console">Step 4 — Connecting to the Console</h2>

<p>Now that the server is running, you can connect to it using the console, that is, the command line interface:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo /opt/orientdb/bin/console.sh
</li></ul></code></pre>
<p>You will see the following:</p>
<pre class="code-pre "><code langs=""><div class="secondary-code-label " title="Output">Output</div>OrientDB console v.2.1.3 (build UNKNOWN@r; 2015-10-04 10:56:30+0000) www.orientdb.com
Type 'help' to display all the supported commands.
Installing extensions for GREMLIN language v.2.6.0

orientdb>
</code></pre>
<p>Now, connect to the server instance. The password required is the one you specified when you first started the server in the earlier:</p>
<pre class="code-pre custom_prefix"><code langs=""><ul class="prefixed"><li class="line" prefix="orientdb>">connect remote:127.0.0.1 root <span class="highlight">root-password</span>
</li></ul></code></pre>
<p>If connected, the output should be:</p>
<pre class="code-pre "><code langs=""><div class="secondary-code-label " title="Output">Output</div>Connecting to remote Server instance [remote:127.0.0.1] with user 'root'...OK
orientdb {server=remote:127.0.0.1/}>
</code></pre>
<p>Type <code>exit</code> to quit:</p>
<pre class="code-pre "><code langs="">exit
</code></pre>
<p>So you've just installed OrientDB, manually started it, and connected to it. That's all good. However, it also means starting it manually anytime you reboot the server. That's not good. In the next steps, we'll configure and set up OrientDB to run just like any other daemon on the server.</p>

<p>Type <code>CTRL-C</code> in the terminal window with OrientDB still running to stop it.</p>

<h2 id="step-5-—-configuring-orientdb">Step 5 — Configuring OrientDB</h2>

<p>At this point OrientDB is installed on your system, but it's just a bunch of scripts on the server. In this step, we'll modify the configuration file, and also configure it to run as a daemon on the system. That involves modifying the <code>/opt/orientdb/bin/orientdb.sh</code> script and the <code>/opt/orientdb/config/orientdb-server-config.xml</code> configuration file.</p>

<p>Let's start by modifying the <code>/opt/orientdb/bin/orientdb.sh</code> script to tell OrientDB the user it should be run as, and to point it to the installation directory.</p>

<p>So, first, create the system user that you want OrientDB to run as. The command will also create the <strong>orientdb</strong> group:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo useradd -r orientdb -s /bin/false
</li></ul></code></pre>
<p>Give ownership of the OrientDB directory and files to the newly-created OrientDB user and group:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo chown -R orientdb:orientdb /opt/orientdb
</li></ul></code></pre>
<p>Now let's make a few changes to the <code>orientdb.sh</code> script. We start by opening it using:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo nano /opt/orientdb/bin/orientdb.sh
</li></ul></code></pre>
<p>First, we need to point it to the proper installation directory, then tell it what user it should be run as. So look for the following two lines at the top of the file:</p>
<pre class="code-pre "><code langs=""><div class="secondary-code-label " title="/opt/orientdb/bin/orientdb.sh">/opt/orientdb/bin/orientdb.sh</div># You have to SET the OrientDB installation directory here
ORIENTDB_DIR="YOUR_ORIENTDB_INSTALLATION_PATH"
ORIENTDB_USER="USER_YOU_WANT_ORIENTDB_RUN_WITH"
</code></pre>
<p>And change them to:</p>
<pre class="code-pre "><code langs=""><div class="secondary-code-label " title="/opt/orientdb/bin/orientdb.sh">/opt/orientdb/bin/orientdb.sh</div># You have to SET the OrientDB installation directory here
ORIENTDB_DIR="<span class="highlight">/opt/orientdb</span>"
ORIENTDB_USER="<span class="highlight">orientdb</span>"
</code></pre>
<p>Now, let's makes it possible for the system user to run the script using <code>sudo</code>.</p>

<p>Further down, under the <strong>start</strong> function of the script, look for the following line and comment it out by adding the <code>#</code> character in front of it. It must appear as shown:</p>
<pre class="code-pre "><code langs=""><div class="secondary-code-label " title="/opt/orientdb/bin/orientdb.sh">/opt/orientdb/bin/orientdb.sh</div><span class="highlight">#</span>su -c "cd \"$ORIENTDB_DIR/bin\"; /usr/bin/nohup ./server.sh 1>../log/orientdb.log 2>../log/orientdb.err &" - $ORIENTDB_USER
</code></pre>
<p>Copy and paste the following line right after the one you just commented out:</p>
<pre class="code-pre "><code langs=""><div class="secondary-code-label " title="/opt/orientdb/bin/orientdb.sh">/opt/orientdb/bin/orientdb.sh</div><span class="highlight">sudo -u $ORIENTDB_USER sh -c "cd \"$ORIENTDB_DIR/bin\"; /usr/bin/nohup ./server.sh 1>../log/orientdb.log 2>../log/orientdb.err &"</span>
</code></pre>
<p>Under the <strong>stop</strong> function, look for the following line and comment it out as well. It must appear as shown.</p>
<pre class="code-pre "><code langs=""><div class="secondary-code-label " title="/opt/orientdb/bin/orientdb.sh">/opt/orientdb/bin/orientdb.sh</div><span class="highlight">#</span>su -c "cd \"$ORIENTDB_DIR/bin\"; /usr/bin/nohup ./shutdown.sh 1>>../log/orientdb.log 2>>../log/orientdb.err &" - $ORIENTDB_USER
</code></pre>
<p>Copy and paste the following line right after the one you just commented out:</p>
<pre class="code-pre "><code langs=""><div class="secondary-code-label " title="/opt/orientdb/bin/orientdb.sh">/opt/orientdb/bin/orientdb.sh</div><span class="highlight">sudo -u $ORIENTDB_USER sh -c "cd \"$ORIENTDB_DIR/bin\"; /usr/bin/nohup ./shutdown.sh 1>>../log/orientdb.log 2>>../log/orientdb.err &"</span>
</code></pre>
<p>Save and close the file.</p>

<p>Next, open the configuration file:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo nano /opt/orientdb/config/orientdb-server-config.xml
</li></ul></code></pre>
<p>We're going to modify the <strong>storages</strong> tag and, optionally, add another user to the <strong>users</strong> tag. So scroll to the <strong>storages</strong> element and modify it so that it reads like the following. The <strong>username</strong> and <strong>password</strong> are your login credentials, that is, those you used to log into your server:</p>
<pre class="code-pre "><code langs=""><div class="secondary-code-label " title="/opt/orientdb/config/orientdb-server-config.xml">/opt/orientdb/config/orientdb-server-config.xml</div><storages>
        <storage path="memory:temp" name="temp" userName="<span class="highlight">username</span>" userPassword="<span class="highlight">password</span>" loaded-at-startup="true" />
</storages>
</code></pre>
<p>If you scroll to the <strong>users</strong> tag, you should see the username and password of the root user you specified when you first start the OrientDB server in Step 3. Also listed will be a guest account. You do not have to add any other users, but if you wanted to, you could add the username and password that you used to log into your IndiaReads server. Below is an example of how to add a user within the <strong>users</strong> tag:</p>
<pre class="code-pre "><code langs=""><div class="secondary-code-label " title="/opt/orientdb/config/orientdb-server-config.xml">/opt/orientdb/config/orientdb-server-config.xml</div><user name="<span class="highlight">username</span>" password="<span class="highlight">password</span>" resources="*"/>
</code></pre>
<p>Save and close the file.</p>

<p>Finally, modify the file's permissions to prevent unauthorized users from reading it:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo chmod 640 /opt/orientdb/config/orientdb-server-config.xml
</li></ul></code></pre>
<h2 id="step-6-—-installing-the-startup-script">Step 6 — Installing the Startup Script</h2>

<p>Now that the scripts have been configured, you can now copy them to their respective system directories. For the script responsible for running the console, copy it to the <code>/usr/bin</code> directory:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo cp /opt/orientdb/bin/console.sh /usr/bin/orientdb
</li></ul></code></pre>
<p>Then copy the script responsible for starting and stopping the service or daemon to the <code>/etc/init.d</code> directory:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo cp /opt/orientdb/bin/orientdb.sh /etc/init.d/orientdb
</li></ul></code></pre>
<p>Change to the <code>/etc/init.d</code> directory:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">cd /etc/init.d
</li></ul></code></pre>
<p>Then update the <code>rc.d</code> directory so that the system is aware of the new script and will start it on boot just like the other system daemons.</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo update-rc.d orientdb defaults
</li></ul></code></pre>
<p>You should get the following output:</p>
<pre class="code-pre "><code langs=""><div class="secondary-code-label " title="Output">Output</div>update-rc.d: warning: /etc/init.d/orientdb missing LSB information
update-rc.d: see <http://wiki.debian.org/LSBInitScripts>
 Adding system startup for /etc/init.d/orientdb ...
   /etc/rc0.d/K20orientdb -> ../init.d/orientdb
   /etc/rc1.d/K20orientdb -> ../init.d/orientdb
   /etc/rc6.d/K20orientdb -> ../init.d/orientdb
   /etc/rc2.d/S20orientdb -> ../init.d/orientdb
   /etc/rc3.d/S20orientdb -> ../init.d/orientdb
   /etc/rc4.d/S20orientdb -> ../init.d/orientdb
   /etc/rc5.d/S20orientdb -> ../init.d/orientdb
</code></pre>
<h2 id="step-7-—-starting-orientdb">Step 7 — Starting OrientDB</h2>

<p>With everything in place, you may now start the service:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo service orientdb start
</li></ul></code></pre>
<p>Verify that it really did start:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo service orientdb status
</li></ul></code></pre>
<p>You may also use the <code>netstat</code> commands from Step 3 to verify that the server is listening on the ports. If the server does not start, check for clues in the error log file in the <code>/opt/orientdb/log</code> directory.</p>

<h2 id="step-8-—-connecting-to-orientdb-studio">Step 8 — Connecting to OrientDB Studio</h2>

<p>OrientDB Studio is the web interface for managing OrientDB. By default, it's listening on port 2480. To connect to it, open your browser and type the following into the address bar:</p>
<pre class="code-pre "><code langs="">http://<span class="highlight">server-ip-address</span>:2480
</code></pre>
<p>If the page loads, you should see the login screen. You should be able to login as <code>root</code> and the password you set earlier.</p>

<p>If the page does not load, it's probably because it's being blocked by the firewall. So you'll have to add a rule to the firewall to allow OrientDB traffic on port 2480. To do that, open the IPTables firewall rules file for IPv4 traffic:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo /etc/iptables/rules.v4
</li></ul></code></pre>
<p>Within the <strong>INPUT</strong> chain, add the following rule:</p>
<pre class="code-pre "><code langs=""><div class="secondary-code-label " title="/etc/iptables/rules.v4">/etc/iptables/rules.v4</div>-A INPUT -p tcp --dport 2480 -j ACCEPT
</code></pre>
<p>Restart iptables:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo service iptables-persistent reload
</li></ul></code></pre>
<p>That should do it for connecting to the OrientDB Studio.</p>

<h2 id="conclusion">Conclusion</h2>

<p>Congratulations! You've just installed the Community edition of OrientDB on your server. To learn more, check out the <a href="https://indiareads/community/tutorials/how-to-back-up-your-orientdb-databases-on-ubuntu-14-04">How To Back Up Your OrientDB Databases on Ubuntu 14.04</a> and <a href="https://indiareads/community/tutorials/how-to-import-and-export-an-orientdb-database-on-ubuntu-14-04">How To Import and Export an OrientDB Database on Ubuntu 14.04</a> articles.</p>

<p>More information and official OrientDB documentation links can be found on <a href="http://orientdb.com/docs/last/">orientdb.com</a>.</p>

    