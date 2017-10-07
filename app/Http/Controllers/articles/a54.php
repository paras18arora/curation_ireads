<link rel='stylesheet' href='digital_ocean.css'><br> <br> 
      <h3 id="introduction">Introduction</h3>

<p>The LAMP stack of software, consisting of the Linux operating system, Apache web server, MySQL database, and PHP scripting language, is a great foundation for web or application development. Installed together, this software stack enables your server to host dynamic websites and web applications.</p>

<p>In this tutorial, we'll walk  you through the installation of this software on a Debian 8 (Jessie) IndiaReads Droplet.</p>

<p>Debian 8, as our operating system, is the Linux part of the stack.</p>

<h2 id="prerequisites">Prerequisites</h2>

<p>Before we get started, please complete the following:</p>

<ul>
<li>Create your Debian 8 IndiaReads Droplet via the web interface</li>
<li>Complete the <a href="https://indiareads/community/tutorials/initial-server-setup-with-debian-8">Initial Server Setup</a> for Debian 8</li>
<li>As part of this setup, you'll create a sudo user and install sudo</li>
<li>You'll also install basic security packages such as IPTables, Fail2Ban, and others</li>
</ul>

<p>If you have not yet finished your basic Droplet setup, go ahead and finish that up and then come back to this tutorial. We'll be using a sudo user to complete this tutorial.</p>

<h2 id="step-1-—-update-the-system">Step 1 — Update the System</h2>

<p>Before we install any software, it’s important to make sure your system is up to date.</p>

<p>Log in to your system via SSH, which you have set up previously (if not, see the <a href="https://indiareads/community/tutorials/initial-server-setup-with-debian-8">Initial Server Setup</a> tutorial).</p>

<p>To update your package lists, type:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo aptitude update
</li></ul></code></pre>
<p>What this does is tell your operating system to compare the software packages currently installed with any new versions that might have been updated recently in the Debian online repositories, where base software packages are stored.  </p>

<p>One note of caution is in order.  If you are running a development or mission-critical high-use server, be cautious about installing updates without carefully going through each package to determine if it is actually needed for your system. In our example here, all packages have been installed for the purposes of this tutorial only.   </p>

<p>For now, let’s go ahead and update our system.  You can do this by typing this command:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo aptitude safe-upgrade
</li></ul></code></pre>
<p>Once you’ve determined that these updated software components are relevant for your needs, go ahead and update your Droplet. This may take a while, depending on the current version of the operating system you have installed, software packages, and network conditions. On a fresh Droplet, it will take a couple of seconds.</p>

<p>Once done however, the Droplet is fully patched, updated, and ready for our LAMP installation.  </p>

<h2 id="step-2-—-install-apache">Step 2 — Install Apache</h2>

<p>The next step in our LAMP installation is installing the Apache web server.  This is a popular web server that allows your server to display web content. To install Apache, type the following:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo aptitude install apache2 apache2-doc
</li></ul></code></pre>
<p>This installs the basic Apache web server package as well as the documentation that goes along with it. This may take a few seconds as Apache and its required packages are installed. Once done, Aptitude will exit; Apache is now installed.  </p>

<p>Let's test that the web server will respond with a sample web page. First up, you will need the IP address of your Droplet. You can view your IP address in your IndiaReads account dashboard or simply use your current SSH session:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo ifconfig eth0
</li></ul></code></pre>
<p>On your screen, you will see a few lines of output, including your server's IP address. You'll want the four-part number shown after <code>inet addr:</code>:</p>
<pre class="code-pre "><code langs=""><div class="secondary-code-label " title="Output">Output</div>inet addr:<span class="highlight">111.111.111.111</span>
</code></pre>
<p>Note the IP address listed and type it into your favorite web browser like this: </p>

<ul>
<li><code>http://<span class="highlight">111.111.111.111</span></code></li>
</ul>

<p>Once done, you will see the default Apache 2 web page, similar to this:</p>

<p><img src="https://assets.digitalocean.com/articles/lamp-debian8/JUGu5aW.png" alt="Apache2 Debian Default Page" /></p>

<p>If you see this page, then congratulations — you have successfully installed Apache on your Droplet!</p>

<p>You can upload your website content to the <code>/var/www/html</code> directory. If you want to set up multiple websites, please see this article on setting up <a href="https://indiareads/community/tutorials/how-to-set-up-apache-virtual-hosts-on-ubuntu-14-04-lts">Apache virtual hosts</a>.</p>

<p>For additional instructions and security information, please take a look at <a href="https://wiki.debian.org/Apache">Debian's Apache information</a>.</p>

<h2 id="step-3-—-install-and-secure-mysql">Step 3 — Install and Secure MySQL</h2>

<p>The next component of the LAMP server is MySQL. This relational database software is an essential backend component for other software packages such as WordPress, Joomla, Drupal, and many others.</p>

<p>To install MySQL and PHP support for it, type the following:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo aptitude install mysql-server php5-mysql
</li></ul></code></pre>
<p>This will install MySQL and other required packages. Note that the installation routine will ask you to enter a new password for the <strong>root</strong> MySQL user:</p>

<p><img src="https://assets.digitalocean.com/articles/lamp-debian8/a0O038P.png" alt="New password for the MySQL "root" user" /></p>

<p>This is a separate account used specifically for MySQL for administrative functions. The username is <strong>root</strong> and the password is whatever you set here. Be sure to set a good password with various combinations of letters and numbers.</p>

<p>After this, the MySQL installation is finished. </p>

<p>To keep your new database server safe, there is an additional script you need to run. Type the following to get started:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo mysql_secure_installation
</li></ul></code></pre>
<p>At this point, the script will now ask you a few questions.  When prompted, go ahead and enter the password for the root MySQL account.  The system will then ask you:</p>
<pre class="code-pre "><code langs=""><div class="secondary-code-label " title="Interactive">Interactive</div>Change the root password? [Y/n] <span class="highlight">n</span>
</code></pre>
<p>Since we already set the root MySQL password at our installation, you can say no at this point. The script will then ask:</p>
<pre class="code-pre "><code langs=""><div class="secondary-code-label " title="Interactive">Interactive</div>Remove anonymous users? [Y/n] <span class="highlight">y</span>
</code></pre>
<p>Go ahead and answer yes to remove the anonymous users option for safety. You can answer yes to the rest of the questions as well by entering <code><span class="highlight">y</span></code>.</p>

<p>Next, the script will ask you to either allow or disallow remote logins for the root account. For safety, disallow remote logins for root unless your environment requires this.</p>

<p>Finally, the script will ask you to remove the test database and then reload the privilege tables. Answer yes to both of these. This will remove the test database and process the security changes.</p>

<p>If everything is correct, once done, the script will return with:</p>
<pre class="code-pre "><code langs=""><div class="secondary-code-label " title="Output">Output</div>All done!  If you have completed all of the above steps, your MySQL installation should now be secure.
</code></pre>
<p>Let's double-check that our new MySQL server is running. Type this command:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">mysql -u root -p
</li></ul></code></pre>
<p>Enter the root password you set up for MySQL when you installed the software package.  Remember, this is <strong>not</strong> the root account used for your Droplet administration. Once in, now type the following to get the server status, version information and more:</p>
<pre class="code-pre custom_prefix"><code langs=""><ul class="prefixed"><li class="line" prefix="mysql>">status
</li></ul></code></pre>
<p>This is a good way to ensure that you’ve installed MySQL and are ready for further configuration.  When you are finished examining the output, exit the application by typing this:</p>
<pre class="code-pre custom_prefix"><code langs=""><ul class="prefixed"><li class="line" prefix="mysql>">exit
</li></ul></code></pre>
<h2 id="step-4-—-install-php">Step 4 — Install PHP</h2>

<p>For our last component, we will set up and install PHP, known as PHP: Hypertext Preprocessor. This widely-used server-side scripting language is used far and wide for dynamic web content, making it essential to many web and application developers. Fortunately, installing this on your Droplet is quite easy.  </p>

<p>To install PHP, simply type the following:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo aptitude install php5-common libapache2-mod-php5 php5-cli
</li></ul></code></pre>
<p>Agree to the installation and PHP will be installed on your Droplet. You will see many packages being installed beyond just PHP; don’t worry, as this is integrating the software with your existing Apache2 installation and other programs.</p>

<p>Restart Apache on your Droplet to make sure all of the changes with the PHP installation take effect. To do this, type the following:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo service apache2 restart
</li></ul></code></pre>
<p>Now, let’s take a moment to test the PHP software that you just installed. Move into your public web directory: </p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">cd /var/www/html
</li></ul></code></pre>
<p>Once there, use your favorite console text editor to create a file named <code>info.php</code>.  Here’s one method of doing this:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo vi info.php
</li></ul></code></pre>
<p>This command will use the command line editor vi to open a new blank file with this name. Inside this file, type the following:</p>
<div class="code-label " title="/var/www/html/info.php">/var/www/html/info.php</div><pre class="code-pre "><code langs=""><?php phpinfo(); ?>
</code></pre>
<p>Save your changes. Open your web browser and type the following URL:</p>

<ul>
<li><code>http://<span class="highlight">111.111.111.111</span>/info.php</code></li>
</ul>

<p>If you’ve done everything correctly, you will see the default PHP information page, like the one shown below: </p>

<p><img src="https://assets.digitalocean.com/articles/lamp-debian8/kAOmYue.png" alt="PHP Information Page" /></p>

<p>When you are done looking at this test PHP page, please remove it for security. To do that, run this command:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo rm -i /var/www/html/info.php
</li></ul></code></pre>
<p>The system will then ask you if you wish to remove the test file that you've created.  Answer yes and you're finished; you have completed the basic PHP installation.</p>

<h2 id="conclusion">Conclusion</h2>

<p>You have now installed the basic LAMP stack on your IndiaReads Droplet.</p>

<p>Now it's time to customize your server. This includes any custom programs that you may need to install on your Droplet, and basic security measures to keep unwanted visitors away! Take a look at these articles and others in the IndiaReads community:</p>

<ul>
<li><a href="https://indiareads/community/tutorials/how-to-set-up-apache-virtual-hosts-on-ubuntu-14-04-lts">Apache virtual hosts</a> for multiple websites</li>
<li><a href="https://indiareads/community/tutorials/an-introduction-to-securing-your-linux-vps">An Introduction to Securing Your Linux VPS</a></li>
</ul>

    