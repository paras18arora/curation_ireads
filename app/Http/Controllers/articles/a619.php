<link rel='stylesheet' href='digital_ocean.css'><br> <br> 
      <h3 id="introduction">Introduction</h3>

<p>Apache Tomcat is a web server and servlet container that is used to serve Java applications. Tomcat is an open source implementation of the Java Servlet and JavaServer Pages technologies, released by the Apache Software Foundation. This tutorial covers the basic installation and some configuration of the latest release of Tomcat 8 on your CentOS 7 server.</p>

<h2 id="prerequisites">Prerequisites</h2>

<p>Before you begin with this guide, you should have a separate, non-root user account set up on your server. You can learn how to do this by completing steps 1-3 in the <a href="https://indiareads/community/articles/initial-server-setup-with-centos-7">initial server setup</a> for CentOS 7. We will be using the <code>demo</code> user created here for the rest of this tutorial.</p>

<h2 id="install-java">Install Java</h2>

<p>Tomcat requires that Java is installed on the server, so any Java web application code can be executed. Let's satisfy that requirement by installing OpenJDK 7 with yum.</p>

<p>To install OpenJDK 7 JDK using yum, run this command:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo yum install java-1.7.0-openjdk-devel
</li></ul></code></pre>
<p>Answer <code>y</code> at the prompt to continue installing OpenJDK 7.</p>

<p>Note that a shortcut to the JAVA_HOME directory, which we will need to configure Tomcat later, can be found at <code>/usr/lib/jvm/jre</code>.</p>

<p>Now that Java is installed, let's create a <code>tomcat</code> user, which will be used to run the Tomcat service.</p>

<h2 id="create-tomcat-user">Create Tomcat User</h2>

<p>For security purposes, Tomcat should be run as an unprivileged user (i.e. not root). We will create a new user and group that will run the Tomcat service.</p>

<p>First, create a new <code>tomcat</code> group:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo groupadd tomcat
</li></ul></code></pre>
<p>Then create a new  <code>tomcat</code> user. We'll make this user a member of the <code>tomcat</code> group, with a home directory of <code>/opt/tomcat</code> (where we will install Tomcat), and with a shell of <code>/bin/false</code> (so nobody can log into the account):</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo useradd -M -s /bin/nologin -g tomcat -d /opt/tomcat tomcat
</li></ul></code></pre>
<p>Now that our <code>tomcat</code> user is set up, let's download and install Tomcat.</p>

<h2 id="install-tomcat">Install Tomcat</h2>

<p>The easiest way to install Tomcat 8 at this time is to download the latest binary release then configure it manually.</p>

<h3 id="download-tomcat-binary">Download Tomcat Binary</h3>

<p>Find the latest version of Tomcat 8 at the <a href="http://tomcat.apache.org/download-80.cgi">Tomcat 8 Downloads page</a>. At the time of writing, the latest version is <strong>8.0.23</strong>. Under the <strong>Binary Distributions</strong> section, then under the <strong>Core</strong> list, copy the link to the "tar.gz".</p>

<p>Let's download the latest binary distribution to our home directory.</p>

<p>First, change to your home directory:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">cd ~
</li></ul></code></pre>
<p>Then use <code>wget</code> and paste in the link to download the Tomcat 8 archive, like this (your mirror link will probably differ from the example):</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">wget <span class="highlight">http://mirror.sdunix.com/apache/tomcat/tomcat-8/v8.0.23/bin/apache-tomcat-8.0.23.tar.gz</span>
</li></ul></code></pre>
<p>We're going to install Tomcat to the <code>/opt/tomcat</code> directory. Create the directory, then extract the the archive to it with these commands:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo mkdir /opt/tomcat
</li><li class="line" prefix="$">sudo tar xvf apache-tomcat-8*tar.gz -C /opt/tomcat --strip-components=1
</li></ul></code></pre>
<p>Now we're ready to set up the proper user permissions.</p>

<h3 id="update-permissions">Update Permissions</h3>

<p>The <code>tomcat</code> user that we set up needs to have the proper access to the Tomcat installation. We'll set that up now.</p>

<p>Change to the Tomcat installation path:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">cd /opt/tomcat
</li></ul></code></pre>
<p>Then give the <code>tomcat</code> user <strong>write</strong> access to the <code>conf</code> directory, and <strong>read</strong> access to the files in that directory:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo chgrp -R tomcat conf
</li><li class="line" prefix="$">sudo chmod g+rwx conf
</li><li class="line" prefix="$">sudo chmod g+r conf/*
</li></ul></code></pre>
<p>Then make the <code>tomcat</code> user the owner of the <code>webapps</code>, <code>work</code>, <code>temp</code>, and <code>logs</code> directories:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo chown -R tomcat webapps/ work/ temp/ logs/
</li></ul></code></pre>
<p>Now that the proper permissions are set up, let's set up a Systemd unit file.</p>

<h3 id="install-systemd-unit-file">Install Systemd Unit File</h3>

<p>Because we want to be able to run Tomcat as a service, we will set up a Tomcat Systemd unit file .</p>

<p>Create and open the unit file by running this command:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo vi /etc/systemd/system/tomcat.service
</li></ul></code></pre>
<p>Paste in the following script. You may also want to modify the memory allocation settings that are specified in <code>CATALINA_OPTS</code>:</p>
<div class="code-label " title="/etc/systemd/system/tomcat.service">/etc/systemd/system/tomcat.service</div><pre class="code-pre "><code langs=""># Systemd unit file for tomcat
[Unit]
Description=Apache Tomcat Web Application Container
After=syslog.target network.target

[Service]
Type=forking

Environment=JAVA_HOME=/usr/lib/jvm/jre
Environment=CATALINA_PID=/opt/tomcat/temp/tomcat.pid
Environment=CATALINA_HOME=/opt/tomcat
Environment=CATALINA_BASE=/opt/tomcat
Environment='CATALINA_OPTS=-Xms<span class="highlight">512M</span> -Xmx<span class="highlight">1024M</span> -server -XX:+UseParallelGC'
Environment='JAVA_OPTS=-Djava.awt.headless=true -Djava.security.egd=file:/dev/./urandom'

ExecStart=/opt/tomcat/bin/startup.sh
ExecStop=/bin/kill -15 $MAINPID

User=tomcat
Group=tomcat

[Install]
WantedBy=multi-user.target
</code></pre>
<p>Save and exit. This script tells the server to run the Tomcat service as the <code>tomcat</code> user, with the settings specified.</p>

<p>Now reload Systemd to load the Tomcat unit file:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo systemctl daemon-reload
</li></ul></code></pre>
<p>Now you can start the Tomcat service with this <code>systemctl</code> command:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo systemctl start tomcat
</li></ul></code></pre>
<p>If you want to enable the Tomcat service, so it starts on server boot, run this command:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo systemctl enable tomcat
</li></ul></code></pre>
<p>Tomcat is not completely set up yet, but you can access the default splash page by going to your domain or IP address followed by <code>:8080</code> in a web browser:</p>
<pre class="code-pre "><code langs=""><div class="secondary-code-label " title="Open in web browser:">Open in web browser:</div>http://<span class="highlight">server_IP_address</span>:8080
</code></pre>
<p>You will see the default Tomcat splash page, in addition to other information.  Now we will go deeper into the installation of Tomcat.</p>

<h2 id="configure-tomcat-web-management-interface">Configure Tomcat Web Management Interface</h2>

<p>In order to use the manager webapp that comes with Tomcat, we must add a login to our Tomcat server. We will do this by editing the <code>tomcat-users.xml</code> file:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo vi /opt/tomcat/conf/tomcat-users.xml
</li></ul></code></pre>
<p>This file is filled with comments which describe how to configure the file.  You may want to delete all the comments between the following two lines, or you may leave them if you want to reference the examples:</p>
<div class="code-label " title="tomcat-users.xml excerpt">tomcat-users.xml excerpt</div><pre class="code-pre "><code langs=""><tomcat-users>
...
</tomcat-users>
</code></pre>
<p>You will want to add a user who can access the <code>manager-gui</code> and <code>admin-gui</code> (webapps that come with Tomcat).  You can do so by defining a user similar to the example below.  Be sure to change the username and password to something secure:</p>
<div class="code-label " title="tomcat-users.xml — Admin User">tomcat-users.xml — Admin User</div><pre class="code-pre "><code langs=""><tomcat-users>
    <user username="<span class="highlight">admin</span>" password="<span class="highlight">password</span>" roles="manager-gui,admin-gui"/>
</tomcat-users>
</code></pre>
<p>Save and quit the tomcat-users.xml file. To put our changes into effect, restart the Tomcat service:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo systemctl restart tomcat
</li></ul></code></pre>
<h2 id="access-the-web-interface">Access the Web Interface</h2>

<p>Now that Tomcat is up and running, let's access the web management interface in a web browser. You can do this by accessing the public IP address of the server, on port 8080:</p>
<pre class="code-pre "><code langs=""><div class="secondary-code-label " title="Open in web browser:">Open in web browser:</div>http://<span class="highlight">server_IP_address</span>:8080
</code></pre>
<p>You will see something like the following image:</p>

<p><img src="https://assets.digitalocean.com/articles/tomcat8_centos/splashscreen.png" alt="Tomcat root" /></p>

<p>As you can see, there are links to the admin webapps that we configured an admin user for.</p>

<p>Let's take a look at the Manager App, accessible via the link or <code>http://<span class="highlight">server_IP_address</span>:8080/manager/html</code>:</p>

<p><img src="https://assets.digitalocean.com/articles/tomcat8_centos/manager.png" alt="Tomcat Web Application Manager" /></p>

<p>The Web Application Manager is used to manage your Java applications. You can Start, Stop, Reload, Deploy, and Undeploy here. You can also run some diagnostics on your apps (i.e. find memory leaks). Lastly, information about your server is available at the very bottom of this page.</p>

<p>Now let's take a look at the Host Manager, accessible via the link or <code>http://<span class="highlight">server_IP_address</span>:8080/host-manager/html/</code>:</p>

<p><img src="https://assets.digitalocean.com/articles/tomcat8_centos/host-manager.png" alt="Tomcat Virtual Host Manager" /></p>

<p>From the Virtual Host Manager page, you can add virtual hosts to serve your applications from.</p>

<h2 id="conclusion">Conclusion</h2>

<p>Your installation of Tomcat is complete!  Your are now free to deploy your own Java web applications!</p>

    