<link rel='stylesheet' href='digital_ocean.css'><br><img src=https://community-cdn-digitalocean-com.global.ssl.fastly.net/assets/tutorials/images/large/Tomcat_twitter_pat.png?1466190399/> <br> 
      <h3 id="introduction">Introduction</h3>

<p>Apache Tomcat is a web server and servlet container that is used to serve Java applications. Tomcat is an open source implementation of the Java Servlet and JavaServer Pages technologies, released by the Apache Software Foundation. This tutorial covers the basic installation and some configuration of the latest release of Tomcat 8 on your Ubuntu 16.04 server.</p>

<h2 id="prerequisites">Prerequisites</h2>

<p>Before you begin with this guide, you should have a non-root user with <code>sudo</code> privileges set up on your server. You can learn how to do this by completing our <a href="https://indiareads/community/tutorials/initial-server-setup-with-ubuntu-16-04">Ubuntu 16.04 initial server setup guide</a>.</p>

<h2 id="step-1-install-java">Step 1: Install Java</h2>

<p>Tomcat requires Java to be installed on the server so that any Java web application code can be executed. We can satisfy that requirement by installing OpenJDK with apt-get.</p>

<p>First, update your apt-get package index:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo apt-get update
</li></ul></code></pre>
<p>Then install the Java Development Kit package with apt-get:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo apt-get install default-jdk
</li></ul></code></pre>
<p>Now that Java is installed, we can create a <code>tomcat</code> user, which will be used to run the Tomcat service.</p>

<h2 id="step-2-create-tomcat-user">Step 2: Create Tomcat User</h2>

<p>For security purposes, Tomcat should be run as an unprivileged user (i.e. not root). We will create a new user and group that will run the Tomcat service.</p>

<p>First, create a new <code>tomcat</code> group:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo groupadd tomcat
</li></ul></code></pre>
<p>Next, create a new  <code>tomcat</code> user. We'll make this user a member of the <code>tomcat</code> group, with a home directory of <code>/opt/tomcat</code> (where we will install Tomcat), and with a shell of <code>/bin/false</code> (so nobody can log into the account):</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo useradd -s /bin/false -g tomcat -d /opt/tomcat tomcat
</li></ul></code></pre>
<p>Now that our <code>tomcat</code> user is set up, let's download and install Tomcat.</p>

<h2 id="step-3-install-tomcat">Step 3: Install Tomcat</h2>

<p>The best way to install Tomcat 8 is to download the latest binary release then configure it manually.</p>

<p>Find the latest version of Tomcat 8 at the <a href="http://tomcat.apache.org/download-80.cgi">Tomcat 8 Downloads page</a>. At the time of writing, the latest version is <strong>8.0.33</strong>, but you should use a later stable version if it is available. Under the <strong>Binary Distributions</strong> section, then under the <strong>Core</strong> list, copy the link to the "tar.gz".</p>

<p>Next, change to the <code>/tmp</code> directory on your server.  This is a good directory to download ephemeral items, like the Tomcat tarball, which we won't need after extracting the Tomcat contents:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">cd /tmp
</li></ul></code></pre>
<p>Use <code>curl</code> to download the link that you copied from the Tomcat website:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">curl -O <span class="highlight">http://apache.mirrors.ionfish.org/tomcat/tomcat-8/v8.0.33/bin/apache-tomcat-8.0.33.tar.gz</span>
</li></ul></code></pre>
<p>We will install Tomcat to the <code>/opt/tomcat</code> directory. Create the directory, then extract the archive to it with these commands:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo mkdir /opt/tomcat
</li><li class="line" prefix="$">sudo tar xzvf apache-tomcat-8*tar.gz -C /opt/tomcat --strip-components=1
</li></ul></code></pre>
<p>Next, we can set up the proper user permissions for our installation.</p>

<h2 id="step-4-update-permissions">Step 4: Update Permissions</h2>

<p>The <code>tomcat</code> user that we set up needs to have access to the Tomcat installation. We'll set that up now.</p>

<p>Change to the directory where we unpacked the Tomcat installation:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">cd /opt/tomcat
</li></ul></code></pre>
<p>Give the <code>tomcat</code> user <strong>write</strong> access to the <code>conf</code> directory, and <strong>read</strong> access to the files in that directory:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo chgrp -R tomcat conf
</li><li class="line" prefix="$">sudo chmod g+rwx conf
</li><li class="line" prefix="$">sudo chmod g+r conf/*
</li></ul></code></pre>
<p>Make the <code>tomcat</code> user the owner of the <code>webapps</code>, <code>work</code>, <code>temp</code>, and <code>logs</code> directories:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo chown -R tomcat webapps/ work/ temp/ logs/
</li></ul></code></pre>
<p>Now that the proper permissions are set up, we can create a systemd service file to manage the Tomcat process.</p>

<h2 id="step-5-create-a-systemd-service-file">Step 5: Create a systemd Service File</h2>

<p>We want to be able to run Tomcat as a service, so we will set up systemd service file.</p>

<p>Tomcat needs to know where Java is installed. This path is commonly referred to as "JAVA_HOME". The easiest way to look up that location is by running this command:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo update-java-alternatives -l
</li></ul></code></pre><pre class="code-pre "><code langs=""><div class="secondary-code-label " title="Output">Output</div>java-1.8.0-openjdk-amd64       1081       <span class="highlight">/usr/lib/jvm/java-1.8.0-openjdk-amd64</span>
</code></pre>
<p>The correct <code>JAVA_HOME</code> variable can be constructed by taking the output from the last column (highlighted in red) and appending <code>/jre</code> to the end.  Given the example above, the correct <code>JAVA_HOME</code> for this server would be:</p>
<pre class="code-pre "><code langs=""><div class="secondary-code-label " title="JAVA_HOME">JAVA_HOME</div>/usr/lib/jvm/java-1.8.0-openjdk-amd64/jre
</code></pre>
<p>Your <code>JAVA_HOME</code> may be different.</p>

<p>With this piece of information, we can create the systemd service file.  Open a file called <code>tomcat.service</code> in the <code>/etc/systemd/system</code> directory by typing:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo nano /etc/systemd/system/tomcat.service
</li></ul></code></pre>
<p>Paste the following contents into your service file.  Modify the value of <code>JAVA_HOME</code> if necessary to match the value you found on your system. You may also want to modify the memory allocation settings that are specified in <code>CATALINA_OPTS</code>:</p>
<div class="code-label " title="/etc/systemd/system/tomcat.service">/etc/systemd/system/tomcat.service</div><pre class="code-pre "><code langs="">[Unit]
Description=Apache Tomcat Web Application Container
After=network.target

[Service]
Type=forking

Environment=JAVA_HOME=<span class="highlight">/usr/lib/jvm/java-8-openjdk-amd64</span>/jre
Environment=CATALINA_PID=/opt/tomcat/temp/tomcat.pid
Environment=CATALINA_HOME=/opt/tomcat
Environment=CATALINA_BASE=/opt/tomcat
Environment='CATALINA_OPTS=-Xms512M -Xmx1024M -server -XX:+UseParallelGC'
Environment='JAVA_OPTS=-Djava.awt.headless=true -Djava.security.egd=file:/dev/./urandom'

ExecStart=/opt/tomcat/bin/startup.sh
ExecStop=/opt/tomcat/bin/shutdown.sh

User=tomcat
Group=tomcat
RestartSec=10
Restart=always

[Install]
WantedBy=multi-user.target
</code></pre>
<p>When you are finished, save and close the file.</p>

<p>Next, reload the systemd daemon so that it knows about our service file:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo systemctl daemon-reload
</li></ul></code></pre>
<p>Start the Tomcat service by typing:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo systemctl start tomcat
</li></ul></code></pre>
<p>Double check that it started without errors by typing:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo systemctl status tomcat
</li></ul></code></pre>
<h2 id="step-6-adjust-the-firewall-and-test-the-tomcat-server">Step 6: Adjust the Firewall and Test the Tomcat Server</h2>

<p>Now that the Tomcat service is started, we can test to make sure the default page is available.</p>

<p>Before we do that, we need to adjust the firewall to allow our requests to get to the service.  If you followed the prerequisites, you will have a <code>ufw</code> firewall enabled currently.</p>

<p>Tomcat uses port <code>8080</code> to accept conventional requests.  Allow traffic to that port by typing:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo ufw allow 8080
</li></ul></code></pre>
<p>With the firewall modified, you can access the default splash page by going to your domain or IP address followed by <code>:8080</code> in a web browser:</p>
<pre class="code-pre "><code langs=""><div class="secondary-code-label " title="Open in web browser">Open in web browser</div>http://<span class="highlight">server_domain_or_IP</span>:8080
</code></pre>
<p>You will see the default Tomcat splash page, in addition to other information.  However, if you click the links for the Manager App, for instance, you will be denied access.  We can configure that access next.</p>

<p>If you were able to successfully accessed Tomcat, now is a good time to enable the service file so that Tomcat automatically starts at boot:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo systemctl enable tomcat
</li></ul></code></pre>
<h2 id="step-7-configure-tomcat-web-management-interface">Step 7: Configure Tomcat Web Management Interface</h2>

<p>In order to use the manager web app that comes with Tomcat, we must add a login to our Tomcat server. We will do this by editing the <code>tomcat-users.xml</code> file:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo nano /opt/tomcat/conf/tomcat-users.xml
</li></ul></code></pre>
<p>You will want to add a user who can access the <code>manager-gui</code> and <code>admin-gui</code> (web apps that come with Tomcat).  You can do so by defining a user, similar to the example below, between the <code>tomcat-users</code> tags.  Be sure to change the username and password to something secure:</p>
<div class="code-label " title="tomcat-users.xml — Admin User">tomcat-users.xml — Admin User</div><pre class="code-pre "><code langs=""><tomcat-users . . .>
    <user username="<span class="highlight">admin</span>" password="<span class="highlight">password</span>" roles="manager-gui,admin-gui"/>
</tomcat-users>
</code></pre>
<p>Save and close the file when you are finished.  To put our changes into effect, restart the Tomcat service:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo systemctl restart tomcat
</li></ul></code></pre>
<h2 id="step-8-access-the-web-interface">Step 8: Access the Web Interface</h2>

<p>Now that we have create a user, we can access the web management interface again in a web browser.  Once again, you can get to the correct interface by entering your server's domain name or IP address followed on port 8080 in your browser:</p>
<pre class="code-pre "><code langs=""><div class="secondary-code-label " title="Open in web browser">Open in web browser</div>http://<span class="highlight">server_domain_or_IP</span>:8080
</code></pre>
<p>The page you see should be the same one you were given when you tested earlier:</p>

<p><img src="https://assets.digitalocean.com/articles/tomcat8_1604/splashscreen.png" alt="Tomcat root" /></p>

<p>Let's take a look at the Manager App, accessible via the link or <code>http://<span class="highlight">server_domain_or_IP</span>:8080/manager/html</code>.  You will need to enter the account credentials that you added to the <code>tomcat-users.xml</code> file.  Afterwards, you should see a page that looks like this:</p>

<p><img src="https://assets.digitalocean.com/articles/tomcat8_1604/manager.png" alt="Tomcat Web Application Manager" /></p>

<p>The Web Application Manager is used to manage your Java applications. You can Start, Stop, Reload, Deploy, and Undeploy here. You can also run some diagnostics on your apps (i.e. find memory leaks). Lastly, information about your server is available at the very bottom of this page.</p>

<p>Now let's take a look at the Host Manager, accessible via the link or <code>http://<span class="highlight">server_domain_or_IP</span>:8080/host-manager/html/</code>:</p>

<p><img src="https://assets.digitalocean.com/articles/tomcat8_1604/host-manager.png" alt="Tomcat Virtual Host Manager" /></p>

<p>From the Virtual Host Manager page, you can add virtual hosts to serve your applications from.</p>

<h2 id="conclusion">Conclusion</h2>

<p>Your installation of Tomcat is complete!  Your are now free to deploy your own Java web applications!</p>

<p>Currently, your Tomcat installation is functional, but entirely unencrypted.  This means that all data, including sensitive items like passwords, are sent in plain text that can be intercepted and read by other parties on the internet.  In order to prevent this from happening, it is strongly recommended that you encrypt your connections with SSL.  You can find out how to encrypt your connections to Tomcat by following <a href="https://indiareads/community/tutorials/how-to-encrypt-tomcat-8-connections-with-apache-or-nginx-on-ubuntu-16-04">this guide</a>.</p>

    