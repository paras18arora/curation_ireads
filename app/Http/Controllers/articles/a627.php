<link rel='stylesheet' href='digital_ocean.css'><br> <br> 
      <h3 id="about-apache-tomcat">About Apache Tomcat</h3>

<p>Apache Tomcat is a web server and servlet container that is used to serve Java applications. Tomcat is an open source implementation of the Java Servlet and JavaServer Pages technologies, released by the Apache Software Foundation.</p>

<p>This tutorial covers the basic installation and some configuration of Tomcat 7 on your Ubuntu 14.04 server.</p>

<p><span class="note"><strong>Note:</strong> Tomcat can be installed automatically on your Droplet by adding <a href="http://do.co/1BV0PQJ">this script</a> to its User Data when launching it. Check out <a href="https://indiareads/community/tutorials/an-introduction-to-droplet-metadata">this tutorial</a> to learn more about Droplet User Data.<br /></span></p>

<p><strong>There are two basic ways to install Tomcat on Ubuntu:</strong></p>

<ul>
<li>Install through apt-get. This is the simplest method.</li>
<li>Download the binary distribution from the Apache Tomcat <a href="http://tomcat.apache.org/download-70.cgi">site</a>. This guide does not cover this method; refer to <a href="http://tomcat.apache.org/tomcat-7.0-doc/index.html">Apache Tomcat Documentation</a> for instructions.</li>
</ul>

<p>For this tutorial, we will use the simplest method: <code>apt-get</code>. Please note that this will install the latest release of Tomcat that is in the official Ubuntu repositories, which may or may not be the latest release of Tomcat. If you want to guarantee that you are installing the latest version of Tomcat, you can always download the latest binary distribution.</p>

<h2 id="step-one-—-prerequisites">Step One — Prerequisites</h2>

<p>Before you begin with this guide, you should have a separate, non-root user account set up on your server. You can learn how to do this by completing steps 1-4 in the <a href="https://indiareads/community/articles/initial-server-setup-with-ubuntu-14-04">initial server setup</a> for Ubuntu 14.04. We will be using the <code>demo</code> user created here for the rest of this tutorial.</p>

<h2 id="step-two-install-tomcat">Step Two - Install Tomcat</h2>

<p>The first thing you will want to do is update your apt-get package lists:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo apt-get update
</li></ul></code></pre>
<p>Now you are ready to install Tomcat.  Run the following command to start the installation:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo apt-get install tomcat7
</li></ul></code></pre>
<p>Answer <code>yes</code> at the prompt to install tomcat.  This will install Tomcat and its dependencies, such as Java, and it will also create the <code>tomcat7</code> user.  It also starts Tomcat with its default settings.</p>

<p>Let's make a quick change to the Java options that Tomcat uses when it starts. Open the Tomcat7 parameters file:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo nano /etc/default/tomcat7
</li></ul></code></pre>
<p>Find the <code>JAVA_OPTS</code> line and replace it with the following. Feel free to change the <code>Xmx</code> and <code>MaxPermSize</code> values—these settings affect how much memory Tomcat will use:</p>
<div class="code-label " title="/etc/default/tomcat7 — JAVA_OPTS">/etc/default/tomcat7 — JAVA_OPTS</div><pre class="code-pre "><code langs="">JAVA_OPTS="-Djava.security.egd=file:/dev/./urandom -Djava.awt.headless=true -Xmx<span class="highlight">512m</span> -XX:MaxPermSize=<span class="highlight">256m</span> -XX:+UseConcMarkSweepGC"
</code></pre>
<p>Save and exit.</p>

<p>Now restart Tomcat with this command:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo service tomcat7 restart
</li></ul></code></pre>
<p>Tomcat is not completely set up yet, but you can access the default splash page by going to your domain or IP address followed by <code>:8080</code> in a web browser:</p>
<pre class="code-pre "><code langs=""><div class="secondary-code-label " title="Open in web browser:">Open in web browser:</div>http://<span class="highlight">server_IP_address</span>:8080
</code></pre>
<p>You will see a splash page that says "It works!", in addition to other information.  Now we will go deeper into the installation of Tomcat.</p>

<h2 id="step-three-installing-additional-packages">Step Three - Installing Additional Packages</h2>

<p><em>Note:</em> This section is not necessary if you are already familiar with Tomcat and you do not need to use the web management interface, documentation, or examples.  If you are just getting into Tomcat for the first time, please continue.</p>

<p>With the following command, we will install the Tomcat online documentation, the web interface (manager webapp), and a few example webapps:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo apt-get install tomcat7-docs tomcat7-admin tomcat7-examples
</li></ul></code></pre>
<p>Answer <code>yes</code> at the prompt to install these packages.  We will get into the usage and configuration of these tools in a later section. Next, we will install the Java Development Kit.</p>

<h2 id="step-four-install-java-development-kit-optional">Step Four - Install Java Development Kit (Optional)</h2>

<p>If you are planning on developing apps on this server, you will want to be sure to install the software in this section.</p>

<p>The Java Development Kit (JDK) enables us to develop Java applications to run in our Tomcat server. Running the following command will install openjdk-7-jdk:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo apt-get install default-jdk
</li></ul></code></pre>
<p>In addition to JDK, the Tomcat documentation suggests also installing Apache Ant, which is used to build Java applications, and a source control system, such as git.  Let's install both of those with the following command:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo apt-get install ant git
</li></ul></code></pre>
<p>For more information about Apache Ant, refer to <a href="http://ant.apache.org/manual/index.html">the official manual</a>.  For a tutorial on using git, refer to <a href="https://indiareads/community/articles/how-to-use-git-effectively">DigitalCloud's Git Tutorial</a>.</p>

<h2 id="step-5-configure-tomcat-web-management-interface">Step 5 - Configure Tomcat Web Management Interface</h2>

<p>In order to use the manager webapp installed in Step 3, we must add a login to our Tomcat server. We will do this by editing the <code>tomcat-users.xml</code> file:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo nano /etc/tomcat7/tomcat-users.xml
</li></ul></code></pre>
<p>This file is filled with comments which describe how to configure the file.  You may want to delete all the comments between the following two lines, or you may leave them if you want to reference the examples:</p>
<div class="code-label " title="tomcat-users.xml excerpt">tomcat-users.xml excerpt</div><pre class="code-pre "><code langs=""><tomcat-users>
...
</tomcat-users>
</code></pre>
<p>You will want to add a user who can access the <code>manager-gui</code> and <code>admin-gui</code> (the management interface that we installed in Step Three).  You can do so by defining a user similar to the example below.  Be sure to change the username and password to something secure:</p>
<div class="code-label " title="tomcat-users.xml — Admin User">tomcat-users.xml — Admin User</div><pre class="code-pre "><code langs=""><tomcat-users>
    <user username="<span class="highlight">admin</span>" password="<span class="highlight">password</span>" roles="manager-gui,admin-gui"/>
</tomcat-users>
</code></pre>
<p>Save and quit the tomcat-users.xml file. To put our changes into effect, restart the Tomcat service:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo service tomcat7 restart
</li></ul></code></pre>
<h2 id="step-6-access-the-web-interface">Step 6 - Access the Web Interface</h2>

<p>Now that we've configured an admin user, let's access the web management interface in a web browser:</p>
<pre class="code-pre "><code langs=""><div class="secondary-code-label " title="Open in web browser:">Open in web browser:</div>http://<span class="highlight">server_IP_address</span>:8080
</code></pre>
<p>You will see something like the following image:</p>

<p><img src="https://assets.digitalocean.com/blog/Tomcat7_Ubuntu14/3.png" alt="Tomcat Splashscreen" /></p>

<p>As you can see, there are four links to packages you installed in Step Three:</p>

<ul>
<li>tomcat7-docs: Online documentation for Tomcat. Accessible via <code>http://<span class="highlight">server_IP_address</span>:8080/docs/</code></li>
<li>tomcat7-examples: Tomcat 7 Servlet and JSP examples. You can click through the example webapps to get a basic idea of how they work (and also look at the source code to see how they were implemented).  Accessible via <code>http://<span class="highlight">server_IP_address</span>:8080/examples/</code></li>
<li>tomcat7-admin (manager-webapp): Tomcat Web Application Manager.  This will allow you to manage and your Java applications.</li>
<li>tomcat7-admin (host-manager): Tomcat Virtual Host Manager.</li>
</ul>

<p>Let's take a look at the Web Application Manager, accessible via the link or <code>http://<span class="highlight">server_IP_address</span>:8080/manager/html</code>:</p>

<p><img src="https://assets.digitalocean.com/blog/Tomcat7_Ubuntu14/1.png" alt="Tomcat Web Application Manager" /></p>

<p>The Web Application Manager is used to manage your Java applications. You can Start, Stop, Reload, Deploy, and Undeploy here. You can also run some diagnostics on your apps (i.e. find memory leaks). Lastly, information about your server is available at the very bottom of this page.</p>

<p>Now let's take a look at the Virtual Host Manager, accessible via the link or <code>http://<span class="highlight">server_IP_address</span>:8080/host-manager/html/</code>:</p>

<p><img src="https://assets.digitalocean.com/blog/Tomcat7_Ubuntu14/2.png" alt="Tomcat Virtual Host Manager" /></p>

<p>From the Virtual Host Manager page, you can add virtual hosts to serve your applications in.</p>

<h2 id="finished">Finished!</h2>

<p>Your installation of Tomcat is complete!  Your are now free to deploy your own webapps!</p>

    