<link rel='stylesheet' href='digital_ocean.css'><br> <br> 
      <h3 id="pre-conditions">Pre-conditions</h3>

<p>There are many tutorials available to help you install OpenJDK and JBoss. This is one on the latest concerning Oracle Java and Glassfish. Hopefully this will make deploying easier for Java EE developers.</p>

<p>You will need a droplet with Ubuntu 12.04.3 x64 that has been created with IndiaReads. Login as root by ssh. This article assumes no Java installed and at least 1G memory, as Java EE servers are quite demanding.</p>

<p><strong>What is Glassfish?</strong></p>

<p>GlassFish is an open-source application server and the reference implementation of Java EE. GlassFish 4.0 release supports the latest Java Platform: Enterprise Edition 7. It supports Enterprise JavaBeans, JPA, JavaServer Faces, JMS, RMI, JavaServer Pages, servlets, etc.</p>

<h2 id="step-one-install-oracle-java-7">Step One: Install Oracle Java 7</h2>

<p>Start by updating the package index:</p>
<pre class="code-pre "><code langs="">sudo apt-get update
</code></pre>
<p>In order to get Oracle Installer of Java 7, we need to add a new apt repository. In order to use add-apt-repository, you need to install python-software-properties. Here's how to do it by apt-get:</p>
<pre class="code-pre "><code langs="">sudo apt-get install python-software-properties
</code></pre>
<p>Now you can add the new repository and install from Oracle Installer:</p>
<pre class="code-pre "><code langs="">sudo add-apt-repository ppa:webupd8team/java
</code></pre>
<p>Make source list up-to-date:</p>
<pre class="code-pre "><code langs="">sudo apt-get update
</code></pre>
<p>Install Java 7 by apt-get:</p>
<pre class="code-pre "><code langs="">sudo apt-get install oracle-java7-installer
</code></pre>
<p>After installing, confirm the current Java is Oracle version:</p>
<pre class="code-pre "><code langs="">java -version
</code></pre>
<p><strong>You will see this:</strong></p>
<pre class="code-pre "><code langs="">java version "1.7.0_51"
Java(TM) SE Runtime Environment (build 1.7.0_51-b13)
Java HotSpot(TM) 64-Bit Server VM (build 24.51-b03, mixed mode)
</code></pre>
<h2 id="step-two-install-glassfish-4-0">Step Two: Install Glassfish 4.0</h2>

<p><strong>Get Glassfish Zip file</strong></p>
<pre class="code-pre "><code langs="">wget download.java.net/glassfish/4.0/release/glassfish-4.0.zip
</code></pre>
<p>Install unzip first before unpackage to /opt</p>
<pre class="code-pre "><code langs="">apt-get install unzip
</code></pre>
<p>Create the directory /opt and then unzip the package to /opt:</p>
<pre class="code-pre "><code langs="">unzip glassfish-4.0.zip -d /opt
</code></pre>
<p>For convenience, add <code>export PATH=/opt/glassfish4/bin:$PATH</code> to the end of ~/.profile.</p>

<p><strong>Start the glassfish server:</strong></p>
<pre class="code-pre "><code langs="">asadmin start-domain
</code></pre>
<p><strong>You will see:</strong></p>
<pre class="code-pre "><code langs="">Waiting for domain1 to start ...................
Successfully started the domain : domain1
domain  Location: /opt/glassfish4/glassfish/domains/domain1
Log File: /opt/glassfish4/glassfish/domains/domain1/logs/server.log
Admin Port: 4848
Command start-domain executed successfully.
</code></pre>
<p>A domain is a set of one or more GlassFish Server instances managed by one administration server. Default GlassFish Server’s port number: 8080. Default administration server’s port number: 4848. Administration user name: admin; password: none.</p>

<p>In order to visit admin page (your<em>server</em>id:4848) remotely, you need to enable secure admin:</p>
<pre class="code-pre "><code langs="">asadmin enable-secure-admin
</code></pre>
<p><strong>You will see:</strong></p>
<pre class="code-pre "><code langs="">Enter admin user name>  admin
Enter admin password for user "admin"> 
You must restart all running servers for the change in secure admin to take effect.
Command enable-secure-admin executed successfully.
</code></pre>
<p>Restart domain to make effect of secure admin:</p>
<pre class="code-pre "><code langs="">asadmin restart-domain
</code></pre>
<p><strong>You will see:</strong></p>
<pre class="code-pre "><code langs="">Successfully restarted the domain
Command restart-domain executed successfully.
</code></pre>
<p>Now you can visit admin page (your<em>server</em>id:4848) in browser</p>

<p><strong>To stop the GlassFish server:</strong></p>
<pre class="code-pre "><code langs="">asadmin stop-domain
</code></pre>
<p><strong>You will see:</strong></p>
<pre class="code-pre "><code langs="">Waiting for the domain to stop .
Command stop-domain executed successfully.
</code></pre>
<h2 id="demo-service-deploy-hello-war-on-glassfish">Demo service: deploy hello.war on Glassfish</h2>

<p>Download the sample application from Glassfish official samples:</p>
<pre class="code-pre "><code langs="">wget https://glassfish.java.net/downloads/quickstart/hello.war
</code></pre>
<p>Deploy war file:</p>
<pre class="code-pre "><code langs="">asadmin deploy /home/ee/glassfish/sample/hello.war
</code></pre>
<p><strong>You will see:</strong></p>
<pre class="code-pre "><code langs="">Enter admin user name>  admin
Enter admin password for user "admin"> 
Application deployed with name hello.
Command deploy executed successfully.
</code></pre>
<p>Now you can visit your<em>server</em>id:8080/hello</p>

<p>To undeploy the application:</p>
<pre class="code-pre "><code langs="">asadmin undeploy hello
</code></pre>
<p><strong>You will see:</strong></p>
<pre class="code-pre "><code langs="">Enter admin user name>  admin
Enter admin password for user "admin"> 
Command undeploy executed successfully.
</code></pre>
<p>In order to save typing "admin user name" and "password" every time you deploy or undeploy an application, create a password file pwdfile with content:</p>
<pre class="code-pre "><code langs="">AS_ADMIN_PASSWORD=your_admin_password
</code></pre>
<p>Add --passwordfile in command:</p>
<pre class="code-pre "><code langs="">asadmin --passwordfile pwdfile deploy /home/ee/glassfish/sample/hello.war
</code></pre>
<p>Now the prompt for user name/password won't appear.</p>

<div class="author">Submitted by: <a href="http://www.fromwheretowhere.net/"> Xuan Wu</a></div>

    