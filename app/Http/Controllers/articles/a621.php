<link rel='stylesheet' href='digital_ocean.css'><br> <br> 
      <h3 id="introduction">Introduction</h3>

<p>Apache Tomcat is a web server and servlet container that is used to serve Java applications. Tomcat is an open source implementation of the Java Servlet and JavaServer Pages technologies, released by the Apache Software Foundation.</p>

<p>This tutorial covers the basic installation and some configuration of Tomcat 7 with yum on your CentOS 7 server. Please note that this will install the latest release of Tomcat that is in the official Ubuntu repositories, which may or may not be the latest release of Tomcat. If you want to guarantee that you are installing the latest version of Tomcat, you can always download the <a href="http://tomcat.apache.org/download-70.cgi">latest binary distribution</a>.</p>

<p><span class="note"><strong>Note:</strong> Tomcat can be installed automatically on your Droplet by adding <a href="http://do.co/1GKQHWK">this script</a> to its User Data when launching it. Check out <a href="https://indiareads/community/tutorials/an-introduction-to-droplet-metadata">this tutorial</a> to learn more about Droplet User Data.<br /></span></p>

<h2 id="prerequisites">Prerequisites</h2>

<p>Before you begin with this guide, you should have a separate, non-root user account set up on your server. You can learn how to do this by completing the <a href="https://indiareads/community/tutorials/initial-server-setup-with-centos-7">initial server setup</a> for CentOS 7 tutorial. We will be using the <code>demo</code> user for the rest of this tutorial.</p>

<h2 id="install-tomcat">Install Tomcat</h2>

<p>Now you are ready to install Tomcat 7. Run the following command to install the Tomcat package:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo yum install tomcat
</li></ul></code></pre>
<p>Answer <code>y</code> at the confirmation prompt to install tomcat.  This will install Tomcat 7 and its dependencies, such as Java, and it will also create the <code>tomcat</code> user.</p>

<p>Most of the important Tomcat files will be located in <code>/usr/share/tomcat</code>. If you already have a Tomcat application that you want to run, you can place it in the <code>/usr/share/tomcat/webapps</code> directory, configure Tomcat, and restart the Tomcat service. In this tutorial, however, we will install a few additional packages that will help you manage your Tomcat applications and virtual hosts.</p>

<p>Let's make a quick change to the Java options that Tomcat uses when it starts. Open the Tomcat configuration file:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo vi /usr/share/tomcat/conf/tomcat.conf
</li></ul></code></pre>
<p>Add the following <code>JAVA_OPTS</code> line to the file. Feel free to change the <code>Xmx</code> and <code>MaxPermSize</code> values—these settings affect how much memory Tomcat will use:</p>
<div class="code-label " title="/etc/default/tomcat7 — JAVA_OPTS">/etc/default/tomcat7 — JAVA_OPTS</div><pre class="code-pre "><code langs="">JAVA_OPTS="-Djava.security.egd=file:/dev/./urandom -Djava.awt.headless=true -Xmx<span class="highlight">512m</span> -XX:MaxPermSize=<span class="highlight">256m</span> -XX:+UseConcMarkSweepGC"
</code></pre>
<p>Save and exit.</p>

<p>Note that the Tomcat service will not be running yet.</p>

<h2 id="install-admin-packages">Install Admin Packages</h2>

<p>If you are just getting started with Apache Tomcat, you will most likely want to install some admin tools that will help you deploy your Java applications and manage your virtual hosts. Luckily, there are packages that include these tools as web applications.</p>

<p>To install the default Tomcat root page (tomcat-webapps), and the Tomcat Web Application Manager and Virtual Host Manager (tomcat-admin-webapps), run this command:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo yum install tomcat-webapps tomcat-admin-webapps 
</li></ul></code></pre>
<p>Answer <code>y</code> at the confirmation prompt.</p>

<p>This adds the <code>ROOT</code>, <code>examples</code>, <code>sample</code>, <code>manager</code>, and <code>host-manager</code> web apps to the <code>tomcat/webapps</code> directory.</p>

<h3 id="install-online-documentation-optional">Install Online Documentation (Optional)</h3>

<p>If you want to install the Tomcat documentation, so that all of the links on the default Tomcat page will work, run this command:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo yum install tomcat-docs-webapp tomcat-javadoc
</li></ul></code></pre>
<p>Answer <code>y</code> at the prompt to install the documentation packages.</p>

<h2 id="configure-tomcat-web-management-interface">Configure Tomcat Web Management Interface</h2>

<p>In order to use the manager webapp installed in the previous step, we must add a login to our Tomcat server. We will do this by editing the <code>tomcat-users.xml</code> file:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo vi /usr/share/tomcat/conf/tomcat-users.xml
</li></ul></code></pre>
<p>This file is filled with comments which describe how to configure the file.  You may want to delete all the comments <strong>between</strong> the following lines, or you may leave them if you want to reference the examples:</p>
<div class="code-label " title="tomcat-users.xml excerpt">tomcat-users.xml excerpt</div><pre class="code-pre "><code langs=""><tomcat-users>
...
</tomcat-users>
</code></pre>
<p>You will want to add a user who can access the <code>manager-gui</code> and <code>admin-gui</code> (the management interface that we installed earlier).  You can do so by defining a user similar to the example below.  Be sure to change the username and password to something secure:</p>
<div class="code-label " title="tomcat-users.xml — Admin User">tomcat-users.xml — Admin User</div><pre class="code-pre "><code langs=""><tomcat-users>
    <user username="<span class="highlight">admin</span>" password="<span class="highlight">password</span>" roles="manager-gui,admin-gui"/>
</tomcat-users>
</code></pre>
<p>Save and exit the <code>tomcat-users.xml</code> file.</p>

<p>Now we're ready to start the Tomcat service.</p>

<h2 id="start-tomcat">Start Tomcat</h2>

<p>To put our changes into effect, restart the Tomcat service:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo systemctl start tomcat
</li></ul></code></pre>
<p>If you started the service earlier for some reason, run the restart command instead:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo systemctl restart tomcat
</li></ul></code></pre>
<h3 id="enable-tomcat-service">Enable Tomcat Service</h3>

<p>If you want Tomcat to run every time the server is booted up, you will need to enable the service:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo systemctl enable tomcat
</li></ul></code></pre>
<p>Now we're ready to access the web interface.</p>

<h2 id="access-the-web-interface">Access the Web Interface</h2>

<p>Now that Tomcat is up and running, let's access the web management interface in a web browser. You can do this by accessing the public IP address of the server, on port 8080:</p>
<pre class="code-pre "><code langs=""><div class="secondary-code-label " title="Open in web browser:">Open in web browser:</div>http://<span class="highlight">server_IP_address</span>:8080
</code></pre>
<p>You will see something like the following image:</p>

<p><img src="https://assets.digitalocean.com/articles/tomcat7_centos7/splashscreen.png" alt="Tomcat root" /></p>

<p>As you can see, there are links to the admin webapps that you installed earlier.</p>

<p>Let's take a look at the Manager App, accessible via the link or <code>http://<span class="highlight">server_IP_address</span>:8080/manager/html</code>:</p>

<p><img src="https://assets.digitalocean.com/articles/tomcat7_centos7/manager.png" alt="Tomcat Web Application Manager" /></p>

<p>The Web Application Manager is used to manage your Java applications. You can Start, Stop, Reload, Deploy, and Undeploy here. You can also run some diagnostics on your apps (i.e. find memory leaks). Lastly, information about your server is available at the very bottom of this page.</p>

<p>Now let's take a look at the Host Manager, accessible via the link or <code>http://<span class="highlight">server_IP_address</span>:8080/host-manager/html/</code>:</p>

<p><img src="https://assets.digitalocean.com/articles/tomcat7_centos7/host-manager.png" alt="Tomcat Virtual Host Manager" /></p>

<p>From the Virtual Host Manager page, you can add virtual hosts to serve your applications from.</p>

<h2 id="conclusion">Conclusion</h2>

<p>Your installation of Tomcat is complete!  Your are now free to deploy your own Java web applications!</p>

    