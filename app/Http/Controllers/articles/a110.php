<link rel='stylesheet' href='digital_ocean.css'><br> <br> 
      <h3 id="introduction">Introduction</h3>

<p>Webmin is a web-based system administration tool for Unix-like systems. It provides an easy alternative to command line system administration and can be used to manage various aspects of a system, such as users and services, through the use of the provided Webmin modules. If you want to manage your own server but you are uncomfortable with the command line, Webmin is a good tool to help you get started.</p>

<p>This tutorial covers the installation of Webmin with SSL using apt-get on Ubuntu 14.04.</p>

<h2 id="prerequisites">Prerequisites</h2>

<p>To install Webmin, you will need to have access to a user with <strong>root</strong> privileges. It is recommended that you set up a non-root user with <strong>sudo</strong> access by following steps 1-3 of this link: <a href="https://indiareads/community/tutorials/initial-server-setup-with-ubuntu-14-04">Initial Server Setup with Ubuntu 14.04</a>. This user will also be used to login to the Webmin interface, and Webmin will use the credentials to administer your server.</p>

<p>Note that you are required to use password-based authentication enabled to log in to your server via Webmin.</p>

<h3 id="log-in-via-ssh">Log in Via SSH</h3>

<p>Log in to your server as the new user that you created (or root) via SSH (substitute your user name and server IP address here):</p>
<pre class="code-pre "><code langs="">ssh <span class="highlight">new_user</span>@<span class="highlight">server_IP_address</span>
</code></pre>
<p>Answer the password prompt to complete the login process.</p>

<p>Let's get started with the Webmin installation!</p>

<h2 id="install-webmin">Install Webmin</h2>

<p>To install Webmin via apt-get, you must first add the Webmin repository to your <code>sources.list</code> file.</p>

<p>On your server, open the <code>sources.list</code> file in your favorite text editor. We will use <code>nano</code> in this tutorial:</p>
<pre class="code-pre "><code langs="">sudo nano /etc/apt/sources.list
</code></pre>
<p>If you are prompted for a "[sudo] password", enter your user's password.</p>

<p>Now press <code>Ctrl-W</code> then <code>Ctrl-V</code> to navigate to the end of the file, then add the following lines to the file:</p>
<pre class="code-pre "><code langs="">deb http://download.webmin.com/download/repository sarge contrib
deb http://webmin.mirror.somersettechsolutions.co.uk/repository sarge contrib
</code></pre>
<p>When you are finished editing, save the file by pressing <code>Ctrl-X</code>, then <code>y</code>, <code>RETURN</code>.</p>

<p>Now add the Webmin GPG key to apt, so the source repository you added will be trusted. This command will do that:</p>
<pre class="code-pre "><code langs="">wget -q http://www.webmin.com/jcameron-key.asc -O- | sudo apt-key add -
</code></pre>
<p>Before installing Webmin, you must update apt-get's package lists:</p>
<pre class="code-pre "><code langs="">sudo apt-get update
</code></pre>
<p>Now run this apt-get command to install Webmin:</p>
<pre class="code-pre "><code langs="">sudo apt-get install webmin
</code></pre>
<p>Enter <code>y</code> to confirm the installation.</p>

<p>After the installation is complete, the Webmin service will start automatically.</p>

<h2 id="log-in-to-webmin">Log in to Webmin</h2>

<p>In a web browser, access your server's Webmin login page via its public IP address (the same IP address you used to login via SSH) on port <code>10000</code>. By default, Webmin will start with SSL/TLS enabled, so you will need to use HTTPS to connect to it.</p>

<p>Open this URL in your web browser (substitute the IP address):</p>
<pre class="code-pre "><code langs="">https://<span class="highlight">server_IP_address</span>:10000
</code></pre>
<p>You will be prompted with a warning that says your server's SSL certificate is not trusted. This is because Webmin automatically generates and installs an SSL certificate upon installation, and this SSL certificate was not issued by a certificate authority that is trusted by your computer. Although your computer cannot verify the validity of the certificate, you know that you are, in fact, accessing your own server. It is fine to proceed.</p>

<p>Instruct your web browser to trust the certificate. If you are using Chrome, for example, click the <strong>Advanced</strong> link, then click the <strong>Proceed to <span class="highlight">server_IP_address</span> (unsafe)</strong> link. If you are using Firefox, click <strong>I Understand the Risks</strong>, then the <strong>Add Exception...</strong> button, then the <strong>Confirm Security Exception</strong> button.</p>

<p>At this point, you will see the Webmin login screen:</p>

<p><img src="https://assets.digitalocean.com/articles/webmin/login.png" alt="Webmin login screen" /></p>

<p>Enter the same login credentials that you used to log in to your server via SSH. This user must have <strong>root</strong> privileges via sudo.</p>

<p>Congratulations! You have successfully installed Webmin, and it is ready to be used. Remember that, because you are using a privileged user to access Webmin, the Webmin application has full access to your server—keep your login credentials secure!</p>

<h2 id="using-webmin">Using Webmin</h2>

<p>When you first log into Webmin, you will be taken to the <strong>System Information</strong> page, which will show you an overview of your system's resources and other miscellaneous information. This view also shows you any Webmin updates that are available.</p>

<p><img src="https://assets.digitalocean.com/articles/webmin/dashboard.png" alt="Webmin Dashboard" /></p>

<p>On the left side, you will see the navigation menu, which you can use to access the various Webmin modules and manage your server. The navigation menu is organized into categories, and each category has its own set of modules. The <strong>Webmin</strong> category is special because contains modules that are used to configure the Webmin application, while the other categories are used to perform various system administration tasks.</p>

<p>Take some time to explore the modules that are available, to familiarize yourself with Webmin.</p>

<h3 id="example-create-a-new-user">Example: Create a New User</h3>

<p>A basic system administration task that you can perform with Webmin is <em>user management</em>. We will show you how to create a new user with the <strong>Users and Groups</strong> module.</p>

<p>Expand the <strong>System</strong> category in the navigation menu, then click on <strong>Users and Groups</strong>.</p>

<p>Then click the <strong>Create a new user.</strong> link.</p>

<p><img src="https://assets.digitalocean.com/articles/webmin/create_user.png" alt="Create user" /></p>

<p>Enter the <strong>Username</strong> and any other settings you want to assign to the new user, then click the <strong>Create</strong> button.</p>

<p>The user will be created on the server, with the specified settings.</p>

<p>The <strong>Users and Groups</strong> module can also be used to perform other user management tasks, such as deleting and disabling users and groups.</p>

<h3 id="example-install-apache">Example: Install Apache</h3>

<p>Webmin comes with a large variety of modules that manage different software packages. We will demonstrate how to install a web server using the the <strong>Apache Webserver</strong> module, as an example.</p>

<p>In the navigation menu, click <strong>Un-used Modules</strong> to expand the category, and then click <strong>Apache Webserver</strong>.</p>

<p>If you do not have Apache installed on your server, the module will notify you and provide you with a way to install Apache.</p>

<p>Use the <strong>Click here</strong> link (in the last sentence) to install Apache via apt-get through Webmin.</p>

<p>After the Apache installation is complete, your server will be running the default Apache server.</p>

<p>The <strong>Apache Webserver</strong> module will be moved to the <strong>Servers</strong> category, and you may use it to manage the configuration of your Apache server.</p>

<h2 id="conclusion">Conclusion</h2>

<p>Now that you have Webmin installed on your Ubuntu server, you should be able to use it to perform basic system administration tasks.</p>

<p>Good luck!</p>

    