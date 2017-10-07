<link rel='stylesheet' href='digital_ocean.css'><br> <br> 
      <h3 id="introduction">Introduction</h3>

<p>OpenLiteSpeed is an optimized open source web server that can be used to manage and serve sites.  As far as Linux web servers are concerned, OpenLiteSpeed has some interesting features that make it a solid choice for many installations.  It features Apache compatible rewrite rules, a web administration interface, and customized PHP processing optimized for the server.</p>

<p>In this guide, we'll demonstrate how to install and configure OpenLiteSpeed on an Ubuntu 14.04 server.  We will also download and install MySQL to complete the conventional setup of a web server, dynamic script processor, and database management system.</p>

<h2 id="prerequisites-and-goals">Prerequisites and Goals</h2>

<p>Before we begin, you should have a non-root user account configured on your server, complete with <code>sudo</code> privileges.  To learn how to set up an account like this, follow our <a href="https://indiareads/community/tutorials/initial-server-setup-with-ubuntu-14-04">Ubuntu 14.04 initial server setup guide</a>.</p>

<p>This tutorial will guide you through the process of compiling, installing, and configuring an OpenLiteSpeed instance on your server.  We will also install and configure MySQL to facilitate interaction with many common web applications and services.  OpenLiteSpeed comes with PHP embedded into the actual server, but we will show you where to go to customize this if you have specific needs.</p>

<h2 id="install-dependencies-and-build-dependencies">Install Dependencies and Build Dependencies</h2>

<p>We will be installing OpenLiteSpeed from source since the project does not provide any pre-built binaries for Ubuntu.  Before we can begin the installation process however, we need to take care of some dependencies.</p>

<p>Fortunately, all of the dependencies that we need can be found in Ubuntu's default repositories.  We can update the local package index file and then install all of the components that we need.  These will be pieces that are needed to compile the software, as well as the supporting components that OpenLiteSpeed will use to implement certain functionality:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo apt-get update
</li><li class="line" prefix="$">sudo apt-get install build-essential libexpat1-dev libgeoip-dev libpng-dev libpcre3-dev libssl-dev libxml2-dev rcs zlib1g-dev
</li></ul></code></pre>
<p>At this point, we have everything we need to compile and install OpenLiteSpeed.</p>

<h2 id="compile-and-install-openlitespeed">Compile and Install OpenLiteSpeed</h2>

<p>Next, we need to download the current latest version of the OpenLiteSpeed software.  You can find the source files on the <a href="http://open.litespeedtech.com/mediawiki/index.php/Downloads">OpenLiteSpeed download page</a>.</p>

<p>We want to install the latest current stable version of the software.  At the time of this writing, that would be version 1.3.10, but it will likely be different for you.  Right-click on the link for the latest stable version in your browser and select "Copy link address" or whatever similar option your browser provides.</p>

<p>Back in your terminal, move into your home directory.  Type in the <code>wget</code> command and then paste in the link that you copied from the website (again, your link will likely be different):</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">cd ~
</li><li class="line" prefix="$">wget http://open.litespeedtech.com/packages/openlitespeed-<span class="highlight">1.3.10</span>.tgz
</li></ul></code></pre>
<p>Once the archive has been downloaded, extract it and then move into the resulting project directory by typing:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">tar xzvf openlitespeed*
</li><li class="line" prefix="$">cd openlitespeed*
</li></ul></code></pre>
<p>Next, we need to configure the software so that it can be properly build for our system.  After the configuration has completed, we can compile the software to build our binaries:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">./configure
</li><li class="line" prefix="$">make
</li></ul></code></pre>
<p>Once our software is compiled, we can install it onto our system by typing:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo make install
</li></ul></code></pre>
<p>This will install the entire OpenLiteSpeed system under the <code>/usr/local/lsws</code> location.</p>

<h2 id="install-and-configure-mysql">Install and Configure MySQL</h2>

<p>Before we move any further, we will install the MySQL database management system so that our applications have a place to store persistent data.</p>

<p>We can install MySQL from Ubuntu's repositories by typing:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo apt-get install mysql-server
</li></ul></code></pre>
<p>You will be asked to select and confirm an administrative password for the database system during the installation procedure.</p>

<p>Once the installation is complete, you can initialize the MySQL directory structure by typing:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo mysql_install_db
</li></ul></code></pre>
<p>Next, we need to fix some insecure defaults by running a simple cleanup script.  Type:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo mysql_secure_installation
</li></ul></code></pre>
<p>You will be asked to provide the MySQL administrative password that you selected during installation.  Afterwards, you will be asked if you want to select a different password.  You can choose "N" for "no" here if you are content with your password choice.  For the remaining questions, hit ENTER to accept the default suggestions.</p>

<h2 id="set-the-administrative-password-and-start-openlitespeed">Set the Administrative Password and Start OpenLiteSpeed</h2>

<p>With OpenLiteSpeed and MySQL installed, we are now almost ready to start the web server.</p>

<p>Before we begin, we should set an administrative password for OpenLiteSpeed.  By default, the password is set to "123456", so we should change this before ever starting the server.  We can do that by running an administrative script.  Type:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo /usr/local/lsws/admin/misc/admpass.sh
</li></ul></code></pre>
<p>You will be asked to optionally provide a username for the administrative user.  If you just press ENTER, the username "admin" will be selected.  Afterwards, you will be asked to select and confirm a new password for the account.</p>

<p>Once the password has been changed, start the web server by typing:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo service lsws start
</li></ul></code></pre>
<p>In your web browser, you can now access the default web page.  Navigate to your server's domain name or IP address, followed by <code>:8088</code> to specify the port:</p>
<pre class="code-pre "><code langs="">http://<span class="highlight">server_domain_or_IP</span>:8088
</code></pre>
<p>You will see a page the default OpenLiteSpeed web page, that looks like this:</p>

<p><img src="https://assets.digitalocean.com/articles/openlitespeed_ubuntu_14.04/default_landing.png" alt="Default OpenLiteSpeed landing page" /></p>

<p>If you click through the links, you should notice that many features are already installed and configured correctly.  For instance, an example CGI script is available, a customized PHP instance is up and running, custom error pages and authentication gates are configured.  Click around to explore a little.</p>

<p>When you are satisfied with the default site, we can move on to the administrative interface.  In your web browser, using HTTPS, navigate to your server's domain name or IP address followed by <code>:7080</code> to specify the port:</p>
<pre class="code-pre "><code langs="">https://<span class="highlight">server_domain_or_IP</span>:7080
</code></pre>
<p>You will likely see a page warning your that the SSL certificate from the server cannot be validated.  Since this is a self-signed certificate, this is expected.  Click through the options available to proceed to the site (in Chrome, you must click "Advanced" and then "Proceed to...").</p>

<p>You will be prompted to enter the administrative name and password that you selected with the <code>admpass.sh</code> script a moment ago:</p>

<p><img src="https://assets.digitalocean.com/articles/openlitespeed_ubuntu_14.04/admin_login.png" alt="OpenLiteSpeed admin login" /></p>

<p>Once you correctly authenticate, you will be presented with the OpenLiteSpeed administration interface:</p>

<p><img src="https://assets.digitalocean.com/articles/openlitespeed_ubuntu_14.04/admin_page.png" alt="OpenLiteSpeed admin page" /></p>

<p>This is where the majority of your configuration for the web server will take place.</p>

<h3 id="change-the-port-for-the-default-page">Change the Port for the Default Page</h3>

<p>To demonstrate the basic idea behind configuring options through the web interface, we will change the port that the default site is using from "8088" to the conventional port 80.</p>

<p>To accomplish this, you can use the "Configuration" menu item in the menu bar and select "Listeners":</p>

<p><img src="https://assets.digitalocean.com/articles/openlitespeed_ubuntu_14.04/listeners.png" alt="OpenLiteSpeed listeners configuration" /></p>

<p>In the list of listeners, you can click the "View/Edit" button for the "Default" listener:</p>

<p><img src="https://assets.digitalocean.com/articles/openlitespeed_ubuntu_14.04/list_of_listeners.png" alt="OpenLiteSpeed list of listeners" /></p>

<p>You can click the edit button in the top-right corner of the "Address Settings" table to modify its values:</p>

<p><img src="https://assets.digitalocean.com/articles/openlitespeed_ubuntu_14.04/change_listener.png" alt="OpenLiteSpeed change listener" /></p>

<p>On the next screen, change port "8088" to port "80" and click "Save".</p>

<p>After the modification, you will need to restart the server, which can be accomplished through the "Actions" menu by selecting "Graceful Restart":</p>

<p><img src="https://assets.digitalocean.com/articles/openlitespeed_ubuntu_14.04/restart.png" alt="OpenLiteSpeed graceful restart" /></p>

<p>The default web page should now be accessible in your browser on port "80" instead of port "8088".  Visiting your server's domain name or IP address without providing a port will now display the site.</p>

<h3 id="information-about-configuring-openlitespeed">Information about Configuring OpenLiteSpeed</h3>

<p>OpenLiteSpeed is a fully-featured web server that is primarily managed through the administrative web interface.  A full run through of how to configure your site through this interface is outside of the scope of this guide. </p>

<p>However, to get you started, we'll touch on a few important points below:</p>

<ul>
<li>Everything associated with OpenLiteSpeed will be found under the <code>/usr/local/lsws</code> directory.</li>
<li>The document root (where your files will be served from) for the default virtual host is located at <code>/usr/local/lsws/DEFAULT/html</code>.  The configuration and logs for this virtual host can be found under the <code>/usr/local/lsws/DEFAULT</code> directory.</li>
<li>You can create new virtual hosts for different sites using the admin interface.  However, all of the directories that you will reference when setting up your configuration <em>must</em> be created ahead of time on your server.  OpenLiteSpeed will not create the directories by itself.</li>
<li>You can set up virtual host templates for virtual hosts that share the same general format.</li>
<li>Often, it is easiest to copy the default virtual host's directory structure and configuration to use as a jumping off point for new configurations.</li>
<li>The admin interface has a built-in tool tip help system for almost all fields.  There is also a "Help" menu option in the menu bar that links to the server documentation.  Consult these sources of information during configuration if you need more information.</li>
<li>After modifying the configuration and doing a graceful restart, always click the "Home" button to see if any error messages were reported at the bottom of the status screen.  You can see the full error logs by clicking "Actions > Server Log Viewer".</li>
<li>PHP is included with OpenLiteSpeed by default, but may not be the correct version for your application.  Because OpenLiteSpeed uses a specially optimized PHP instance, if you need a different version, you'll have to compile it using the admin interface.  You can get started by going to "Actions > Compile PHP" to select options.  The interface will walk you through the process.</li>
</ul>

<h2 id="conclusion">Conclusion</h2>

<p>At this point, you should have OpenLiteSpeed (with PHP included) and MySQL installed and running on an Ubuntu 14.04 server.  OpenLiteSpeed offers great performance, an easy-to-use interface, and pre-configured options for script handling.  Dive in and learn the ropes to start leveraging these capabilities to serve your sites.</p>

    