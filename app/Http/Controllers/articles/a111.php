<link rel='stylesheet' href='digital_ocean.css'><br> <br> 
      <h3 id="introduction">Introduction</h3>

<p>Froxlor is a server management control panel that can be used to manage multi-user or shared servers. It is an alternative to cPanel or Webmin that allows system administrators to manage customer contact information, as well as the domain names, email accounts, FTP accounts, support tickets, and webroots that are associated with them.</p>

<p>A caveat about Froxlor: the control panel does not automatically configure the underlying services that it uses. You will need a fairly high level of sysadmin knowledge to set up your web server, mail server, and other services. Once it's all set up, though, you can do pretty much any sysadmin task from the control panel, with an added layer of customer management.</p>

<h2 id="prerequisites">Prerequisites</h2>

<p>Have these prerequisites before you begin. <span class="highlight">Red text</span> in this tutorial should be changed to match your desired configuration.</p>

<ul>
<li>A registered domain name</li>
<li>The domain or subdomain you want to use for Froxlor should have an <em>A record</em> pointing to your server's IP address. The A record <code>@</code> specifies the top level of your domain name (<code><span class="highlight">example.com</span></code>), while an A record named <code>froxlor</code> specifies the subdomain <code>froxlor.<span class="highlight">example.com</span></code>. The FQDN of the server in the example in this tutorial is <code><span class="highlight">example.com</span></code></li>
<li>If you want to set up email addresses, your MX records also need to point to the server</li>
<li>A cloud server (Droplet) running a fresh installation of Ubuntu 12.04. This ensures that the server is free of prior configurations or modifications</li>
<li>Make sure to specify your server’s hostname (<strong>Droplet Hostname</strong>) as your desired <em>Fully Qualified Domain Name</em> (FQDN). For example, <code><span class="highlight">example.com</span></code> or <code>froxlor.<span class="highlight">example.com</span></code>. Your FQDN should match the A record you set up</li>
<li>A non-root <a href="https://indiareads/community/tutorials/how-to-add-and-delete-users-on-ubuntu-12-04-and-centos-6">sudo user</a>, in addition to <strong>root</strong> access</li>
<li>Complete the tutorial on <a href="https://indiareads/community/tutorials/how-to-install-linux-apache-mysql-php-lamp-stack-on-ubuntu-14-04">How To Install Linux, Apache, MySQL, PHP (LAMP) stack on Ubuntu 14.04</a>. This will install the packages necessary to install and configure Froxlor. While the tutorial was written for Ubuntu 14.04, the installation process works identically on Ubuntu 12.04</li>
</ul>

<blockquote>
<p><strong>Note:</strong> At the time of writing, Froxlor is not yet compatible with later versions of Ubuntu, so we will be installing it on Ubuntu 12.04.</p>
</blockquote>

<p>Once you access the Droplet, you can verify your hostname with the following command:</p>
<pre class="code-pre "><code langs="">hostname
</code></pre>
<p>Check your fully-qualified domain name:</p>
<pre class="code-pre "><code langs="">hostname -f
</code></pre>
<p>Knowing your hostname and FQDN can save headaches with mail servers later on.</p>

<h2 id="step-1-—-adding-froxlor’s-package-repository">Step 1 — Adding Froxlor’s Package Repository</h2>

<p>The Froxlor Team does not publish its software on the official Ubuntu package repositories, so you will need to add the address of their repository to your server. To install the <code>add-apt-repository</code> package needed, first install the <code>python-software-properties</code> package.</p>
<pre class="code-pre "><code langs="">sudo apt-get install python-software-properties
</code></pre>
<p>Then you can add Froxlor’s repository to your server:</p>
<pre class="code-pre "><code langs="">sudo add-apt-repository "deb http://debian.froxlor.org wheezy main"
</code></pre>
<p>You will need to add the software keys for Froxlor’s repository to your system (again, this is not an official Ubuntu repository).</p>
<pre class="code-pre "><code langs="">sudo apt-key adv --keyserver pool.sks-keyservers.net --recv-key FD88018B6F2D5390D051343FF6B4A8704F9E9BBC
</code></pre>
<blockquote>
<p><strong>Note:</strong> Software keys are used to authenticate the origin of Debian (Ubuntu) software packages. Each repository has its own key that has to be added to Ubuntu manually. When software packages are downloaded, Ubuntu compares the key of the package to the key of the repository it was suppose to come from. If the package is valid, the key will match. The reason you don't usually have to enter the keys for the official Ubuntu repositories is because they come installed with Ubuntu.</p>
</blockquote>

<h2 id="step-2-—-installing-froxlor">Step 2 — Installing Froxlor</h2>

<p>With Froxlor’s repository key added to your server, update your server’s packages list.</p>
<pre class="code-pre "><code langs="">sudo apt-get update
</code></pre>
<p>Then, install Froxlor. The <code>php5-curl</code> package is necessary for Froxlor to function properly, but at the time this tutorial was written Froxlor does not install <code>php5-curl</code> by itself.</p>
<pre class="code-pre "><code langs="">sudo apt-get install froxlor php5-curl
</code></pre>
<p>You will notice Froxlor installs many other packages along with it. That is perfectly normal. Froxlor’s ability to manage customer domain names, email accounts, FTP accounts, support tickets, and webroots in one place relies on these <em>dependencies</em>. Dependencies are other packages that a package depends on to operate.</p>

<p>During Froxlor’s installation, some of its dependencies will ask you questions about your desired configuration. This is the first set of installation questions, as you will be installing more of Froxlor’s dependencies later on in Step 4. The first thing you will be asked looks like this:</p>

<p><img src="https://assets.digitalocean.com/articles/Froxlor_Ubuntu12/1.png" alt="Create Courier Web-Based Administration Directories? <Yes>" /></p>

<p>Courier is one of the email servers Froxlor can use. Froxlor does not use Courier as the default <em>Mail Transfer Agent</em> (MTA) because Dovecot uses less memory, but it installs it as a dependency so you need to answer this question. Since you do not want to configure it manually, use your left arrow button to highlight <strong><Yes></strong> in orange and press the ENTER or RETURN key on your keyboard.</p>

<p>The next thing you will see will be this image, or the one after it:</p>

<p><img src="https://assets.digitalocean.com/articles/Froxlor_Ubuntu12/2.png" alt="Postfix Configuration (first part): If you have a screen with information but no options to select, go past this screen" /></p>

<p>At first glance, this does not make sense because nothing will be highlighted in orange to make a selection. That is because you have to press the TAB key on your keyboard, and press ENTER or RETURN, then use your arrow key to select <strong>Internet Site</strong> from this menu:</p>

<p><img src="https://assets.digitalocean.com/articles/Froxlor_Ubuntu12/3.png" alt="Select Postfix Mail Server Type: Internet Site" /></p>

<p>Then press the ENTER or RETURN key again.</p>

<p>Next, Postfix will ask you a question. Postfix is another mail server that Froxlor can use. Make sure you enter your server's <strong>FQDN as the System mail name</strong>. Chances are, it will already be filled out for you. To accept the mail name Postfix suggests for you, press the ENTER or RETURN key.</p>

<p><img src="https://assets.digitalocean.com/articles/Froxlor_Ubuntu12/4.png" alt="Enter Postfix FQDN" /></p>

<p>Lastly, ProFTPD wants to know how it should run. ProFTPD is the default <em>file transfer protocol</em> (FTP) server that Froxlor can use. Make sure <strong>standalone</strong> is highlighted and press the ENTER or RETURN key.</p>

<p>Once the installation finishes, restart the Apache web server.</p>
<pre class="code-pre "><code langs="">sudo service apache2 restart
</code></pre>
<p>From this point forward, you can access the Froxlor management panel using your server’s IP Address or FQDN with <code>/froxlor</code> appended. For example, you could visit <code>http://<span class="highlight">your_server_ip_</span>/froxlor</code> or <code>http://<span class="highlight">example.com</span>/froxlor</code>.</p>

<h2 id="step-3-—-configuring-froxlor">Step 3 — Configuring Froxlor</h2>

<p>Use your favorite web browser to access Froxlor’s management panel on your server. The first time you access the management panel, it will welcome you to Froxlor and tell you Froxlor is not installed yet; hopefully that phrasing will be fixed in a later release of Froxlor. Nonetheless, click on the <strong>Start install</strong> link.</p>

<p>Froxlor will do a quick check that it has everything it needs on your server to operate properly. <strong>All requirements are satisfied</strong> should be printed in large green print at the bottom of the page. Click on the <strong>Click here to continue</strong> link in the bottom right-hand corner of the window.</p>

<p><img src="https://assets.digitalocean.com/articles/Froxlor_Ubuntu12/5.png" alt="Froxlor Checking System Requirements…, All requirements are satisfied" /></p>

<p>Now it is time to give Froxlor some information about your configuration. Here are the options you will need to change or set:</p>

<p><img src="https://assets.digitalocean.com/articles/Froxlor_Ubuntu12/6.png" alt="Froxlor Initial Admin, Environment and MySQL configuration" /></p>

<ul>
<li><strong>Database connection > Password for the unprivileged MySQL-account:</strong> This will be the password for a new MySQL account Froxlor sets up to store its configuration settings and customer listings. You will need this password again in Step 4, but you do not need to remember it after that. Use the <a href="http://passwordsgenerator.net">Secure Password Generator</a> to generate a strong password. An example of a strong password could be <span class="highlight">&Mk9t(EX"Ce`e?T</span> or <span class="highlight">w>hCt*5#S+$BePv</span>.</li>
<li><strong>Database connection > Password for the MySQL-root account:</strong> This is the same password you set in the prerequisite LAMP tutorial when you installed MySQL, for the <strong>root</strong> MySQL user. Froxlor needs to have access to the root MySQL account so that it can create new MySQL databases and users by itself, which is part of the beauty of Froxlor. You could set up a different privileged MySQL account for added security.</li>
<li><strong>Administrator Account > Administrator Username:</strong> This is the username you will use to log into Froxlor using a web browser. It is recommended that you change the username to anything that is not the default username <strong>admin</strong>. In this tutorial, assume the user is named <span class="highlight"><strong>sammy</strong></span>.</li>
<li><strong>Administrator Account > Administrator Password + (confirm):</strong> This is the password you will use to log into Froxlor using a web browser. You will have to type in this password often; for optimal security, use a complex, long password that can be remembered easily.</li>
</ul>

<p>The rest of the fields should be fine left with the default settings, if you did your installation on a clean Ubuntu 12.04 Droplet.</p>

<p>Once you are happy with your answers, click on the green <strong>Click here to continue</strong> button. Froxlor will test to make sure your settings are operational; once it decides they are, <strong>Froxlor was installed successfully</strong> will be printed in large green print at the bottom of the window.</p>

<p>Use the <strong>Click here to login</strong> link in the bottom right-hand corner of the window to go to Froxlor’s login page.</p>

<p>To log in, use the username and password you specified in the <strong>Administrator Account</strong> section of Froxlor’s setup in Step 3. You should also select your preferred language.</p>

<h2 id="step-4-—-installing-and-configuring-froxlor’s-dependencies">Step 4 — Installing and Configuring Froxlor’s Dependencies</h2>

<p>At this point Froxlor itself is set up, but the underlying software that it uses to do the heavy lifting is not.</p>

<p>While Froxlor does not make this obvious during the its installation, there is more work to do beyond the initial installation and configuration process. In Froxlor’s current state on your server, it would not be able to operate at its full potential or execute commands on the server on the behalf of the control panel user.</p>

<p>To make Froxlor fully functional, we need to install more packages and run a series of commands on the server. An index of these commands is located in the <strong>Configuration</strong> menu of Froxlor’s management panel under the <strong>Server</strong> section.</p>

<p>Visit the <strong>Server > Configuration</strong> page now.</p>

<p>Froxlor’s configuration index uses three questions to direct you to the right set of commands. The first dropdown menu labeled <strong>Distribution</strong> needs the distribution of Linux you are running Froxlor on. You are running Ubuntu 12.04; always answer this question as <strong>Ubuntu 12.04 (Precise)</strong>.</p>

<p><img src="https://assets.digitalocean.com/articles/Froxlor_Ubuntu12/7.png" alt="Froxlor Configuration Index" /></p>

<p>The next two menus, <strong>Service</strong> and <strong>Daemon</strong>, allow you to specify the category of service and the combination of daemons that you are using. Once you select from all three menus, Froxlor will redirect you to a page describing what to do and which commands to execute on your server. You will have to fill out the combination of these three questions once for each service.</p>

<p>The combination of services and daemons you need to select from the menu, and then execute the commands for, are listed below:</p>

<ul>
<li><strong>Web server:</strong> Ubuntu 12.04 (Precise) >> Webserver (HTTP) >> Apache 2</li>
<li><strong>Mail sending:</strong> Ubuntu 12.04 (Precise) >> Mailserver (SMTP) >> Postfix/Dovecot</li>
<li><strong>Mail inboxes:</strong> Ubuntu 12.04 (Precise) >> Mailserver (IMAP/POP3) >> Dovecot</li>
<li><strong>FTP:</strong> Ubuntu 12.04 (Precise) >> FTP-server >> ProFTPd</li>
<li><strong>Cron:</strong> Ubuntu 12.04 (Precise) >> Others (System) >> Crond (cronscript)</li>
</ul>

<p>Once you select all three items from the menu, you'll be brought to a page of commands that need to be run and configuration files that need to be added to the server from the command line.</p>

<p>Froxlor’s configuration instructions assume you will be executing the commands as the <strong>root</strong> user, so you will need to elevate into a <strong>root</strong> shell before you begin.</p>
<pre class="code-pre "><code langs="">sudo su
</code></pre>
<h3 id="configuration-walkthrough-mailserver-imap-pop3">Configuration Walkthrough: Mailserver (IMAP/POP3)</h3>

<p>We'll go through one additional server configuration for Froxlor in this tutorial. Once you've seen how to do it for the IMAP/POP3 server, you can follow a similar process for the other server components, such as the web server.</p>

<p>Make sure you have <strong>Ubuntu 12.04 (Precise) >> Mailserver (IMAP/POP3) >> Dovecot</strong> selected from the menu.</p>

<p>The IMAP/POP3 setup contains some oddities that the other sections do not, so this section needs some explaining.</p>

<p>First, Froxlor tells you to execute an <code>apt-get</code> command.</p>

<p><img src="https://assets.digitalocean.com/articles/Froxlor_Ubuntu12/8.png" alt="Configuration Index - Dovecot apt-get Command" /></p>

<p>The problem with this command is that the <code>dovecot-postfix</code> package no longer exists. It has been merged into the <code>mail-stack-delivery</code> package. Omit the <code>dovecot-postfix</code> package from the command and run it like this instead:</p>
<pre class="code-pre "><code langs="">apt-get install dovecot-imapd dovecot-pop3d dovecot-mysql mail-stack-delivery
</code></pre>
<p>Next, Froxlor asks you to <strong>change the following files or create them with<br />
the following content if they do not exist.</strong></p>

<p><img src="https://assets.digitalocean.com/articles/Froxlor_Ubuntu12/9.png" alt="Configuration Index - Modify /etc/dovecot/conf.d/01-mail-stack-delivery.conf" /></p>

<p>What this really means is:</p>

<ul>
<li>If the file already exists on the server you have two options: if it's a fresh installation you can simply rename the old file and replace it with Froxlor's version. If you have existing configurations you need to preserve, you can merge your existing file with Froxlor’s version</li>
<li>If the file does not exist, copy Froxlor’s version of the file onto your server</li>
</ul>

<p>Since this server has no prior modifications, you do not have to merge the files. You can simply replace the file on your server with Froxlor’s version of the file. To do that, make sure the file path listed above a given text box exists and is empty.</p>
<pre class="code-pre "><code langs="">echo > <span class="highlight">/etc/dovecot/conf.d/01-mail-stack-delivery.conf</span>
</code></pre>
<p>To copy the contents of Froxlor’s version of the file to your server, highlight the text from the text box, right click on it and select <strong>Copy</strong>. Next, open the file on your server in the <code>nano</code> text editor.</p>
<pre class="code-pre "><code langs="">nano <span class="highlight">/etc/dovecot/conf.d/01-mail-stack-delivery.conf</span>
</code></pre>
<p>Right click on your Terminal window and select <strong>Paste</strong>. The contents of the file from Froxlor’s text box will appear inside of nano. Press the CONTROL + X keys simultaneously for a moment. The bottom of nano will ask you this:</p>
<pre class="code-pre "><code langs="">Save modified buffer (ANSWERING "No" WILL DESTROY CHANGES) ?                    
 Y Yes
 N No           ^C Cancel
</code></pre>
<p>Press the Y key on your keyboard to save your changes. Press ENTER.</p>

<p>Add the content for the other three files, <code>/etc/dovecot/conf.d/10-auth.conf</code>, <code>/etc/dovecot/conf.d/auth-sql.conf.ext</code>, and <code>/etc/dovecot/dovecot-sql.conf.ext</code>. You can use <code>nano</code> as we did for the first file.</p>

<p>Two of the files should already exist. Before you use <code>nano</code> to add Froxlor's content for those files, you can back up the originals:</p>
<pre class="code-pre "><code langs="">mv /etc/dovecot/conf.d/10-auth.conf /etc/dovecot/conf.d/10-auth.conf.orig
</code></pre><pre class="code-pre "><code langs="">mv /etc/dovecot/dovecot-sql.conf.ext /etc/dovecot/dovecot-sql.conf.ext.orig
</code></pre>
<p><img src="https://assets.digitalocean.com/articles/Froxlor_Ubuntu12/10.png" alt="Configuration Index - Modify /etc/dovecot/conf.d/01-mail-stack-delivery.conf" /></p>

<p>For the last file, <code>/etc/dovecot/dovecot-sql.conf.ext</code>, notice how it says <strong>Please replace "MYSQL_PASSWORD" on your own. If you forgot your MySQL-password you'll find it in "lib/userdata.inc.php".</strong> Froxlor is referring to the unprivileged MySQL password you created specifically for Froxlor in Step 3. <code><span class="highlight">MYSQL_PASSWORD</span></code> should be replaced with the unprivileged MySQL password anywhere it appears. Assuming the unprivileged MySQL password you created is <code><span class="highlight">&Mk9t(EX"Cee?T</span></code>, this:</p>
<pre class="code-pre "><code langs="">password = <span class="highlight">MYSQL_PASSWORD</span>
</code></pre>
<p>Becomes this:</p>
<pre class="code-pre "><code langs="">password = <span class="highlight">&Mk9t(EX"Cee?T</span>
</code></pre>
<p>You should use your own MySQL password to replace <code><span class="highlight">MYSQL_PASSWORD</span></code>.</p>

<p>Execute the <code>chmod</code> command:</p>
<pre class="code-pre "><code langs="">chmod 0640 /etc/dovecot/dovecot-sql.conf.ext
</code></pre>
<p>Restart the service:</p>
<pre class="code-pre "><code langs="">/etc/init.d/dovecot restart
</code></pre>
<p>Now you can go back to the <strong>Server > Configuration</strong> menu and select another dependency to install, such as your web server. Froxlor will show you more commands and configuration files. The rest of Froxlor’s dependency installations and configurations will be straightforward and should be followed as they are presented.</p>

<p>Note that Froxlor's instructions are not necessarily everything you will need to set up the server. You may have to do some troubleshooting with users, permissions, and other configuration settings from the command line to get everything to work. You can look up the specific server you are trying to install for more instructions. For example, you will likely have to look up additional configuration instructions for <strong>Dovecot</strong> to get email working.</p>

<h3 id="adding-customers-domains-and-more">Adding Customers, Domains, and More</h3>

<p>Once you have all of your servers set up on the backend, you can start adding customers, domains, and email addresses through Froxlor. Start by going to the <strong>Resources > Customers</strong> menu and adding your first customer. You may want to check out the <a href="http://demo.froxlor.org/">Froxlor demo site</a> to see more configuration options.</p>

<h2 id="troubleshooting">Troubleshooting</h2>

<p>At this point, Froxlor should be completely configured and functional. If you find that something is not working properly (e.g. cannot access FTP, not sending emails, etc.), you can refer to <a href="http://forum.froxlor.org/">Froxlor's forums</a>, <a href="http://askubuntu.com">AskUbuntu Q&A</a>, or <a href="https://indiareads/community">IndiaReads’s user community</a>.</p>

<p>Please be prepared to post program log files from the <code>/var/log</code> directory on your server to assist community members in resolving your problem. You can use <a href="http://pastebin.com">Pastebin.com</a> for posting program logs online.</p>

<h3 id="conclusion">Conclusion</h3>

<p><img src="https://assets.digitalocean.com/articles/Froxlor_Ubuntu12/11.png" alt="Froxlor Dashboard" /></p>

<p>Now that you have installed and configured Froxlor, you have a free alternative to cPanel or Webmin that will help you spend less time configuring and maintaining your multi-user or shared server. To further customize your Froxlor installation, refer to the <strong>Server > Settings</strong> menu in Froxlor’s control panel. If you choose to change any of the default daemons, remember to follow Froxlor’s configuration instructions, just like we did in the IMAP/POP3 section above.</p>

    