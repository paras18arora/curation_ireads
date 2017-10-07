<link rel='stylesheet' href='digital_ocean.css'><br> <br> 
      <h3 id="introduction">Introduction</h3>

<p>This tutorial shows you how to configure system services to automatically restart after a crash or a server reboot.</p>

<p>The example uses MySQL, but you can apply these principles to other services running on your server, like Nginx, Apache, or your own application.</p>

<p>We cover the three most common init systems in this tutorial, so be sure to follow the one for your distribution. (Many distributions offer multiple options, or allow an alternate init system to be installed.)</p>

<ul>
<li><strong>System V</strong> is the older init system:

<ul>
<li>Debian 6 and earlier</li>
<li>Ubuntu 9.04 and earlier</li>
<li>CentOS 5 and earlier</li>
</ul></li>
<li><strong>Upstart</strong>:

<ul>
<li>Ubuntu 9.10 to Ubuntu 14.10, including Ubuntu 14.04</li>
<li>CentOS 6</li>
</ul></li>
<li><strong>systemd</strong> is the init system for the most recent distributions featured here:

<ul>
<li>Debian 7 and Debian 8</li>
<li>Ubuntu 15.04 and newer</li>
<li>CentOS 7</li>
</ul></li>
</ul>

<p>You can also check out <a href="https://indiareads/community/tutorials/how-to-configure-a-linux-service-to-start-automatically-after-a-crash-or-reboot-part-2-reference">Part 2, the reference article</a>.</p>

<h3 id="background">Background</h3>

<p>Your running Linux or Unix system will have a number of background processes executing at any time. These processes - also known as <em>services</em> or <em>daemons</em> - may be native to the operating system, or run as part of an application.</p>

<p>Examples of operating system services:</p>

<ul>
<li><strong>sshd</strong> daemon that allows remote connections</li>
<li><strong>cupsd</strong> daemon that controls printing</li>
</ul>

<p>Examples of application daemons:</p>

<ul>
<li><strong>httpd</strong>/<strong>apache2</strong> is a web server service</li>
<li><strong>mongod</strong> is a database daemons</li>
</ul>

<p>These services are supposed to run continously to make sure our websites, mail, databases, and other apps are always up.</p>

<p>As administrators, we want our Linux services to:</p>

<ul>
<li>Run continuously without failing</li>
<li>Start automatically after the system reboots or crashes</li>
</ul>

<p>Yet, sometimes, these services go down, making our websites or apps unavailable.</p>

<p>A reboot can happen for many reasons: it can be a planned restart, the last step of a patch update, or the result of unexpected system behavior. A crash is what happens when the process stopping unexpectedly or becomes unresponsive to user or application requests.</p>

<p><strong>The goal of this article is to get your services up and running again, even after a crash or reboot.</strong></p>

<p>Although there's no substitute for continuous monitoring and alerting, Linux services can be made largely self-healing by changing the way they are handled by service management daemons, also known as <em>init</em> systems.</p>

<p>There is no single way to do this: it all depends on the particular Linux distribution and the service management daemon that comes with it. The default init systems for many common operating systems are shown in the introduction above.</p>

<span class="note"><p>
We'll go into more detail about <em>runlevels</em> in <a href="https://indiareads/community/tutorials/how-to-configure-a-linux-service-to-start-automatically-after-a-crash-or-reboot-part-2-reference">Part 2</a>, but for this article, it'll help to understand that every Linux system has four basic runlevels in common:</p>

<ul>
<li>0 - Runlevel 0 means system shutdown</li>
<li>1 - Runlevel 1 means single-user, rescue mode</li>
<li>5 - Runlevel 5 means multi-user, network enabled, graphical mode</li>
<li>6 - Runlevel 6 is for system reboot</li>
</ul>

<p>In general, runlevels 2, 3 and 4 mean states where Linux has booted in multi-user, network enabled, text mode.</p>

<p><strong>When we enable a service to auto-start, we are actually adding it to a runlevel.</strong></p></span>

<h3 id="goals">Goals</h3>

<p>In this two-part tutorial, we will see how to configure a Linux service to automatically start when the system reboots or crashes.</p>

<p>This installment of the series, Part 1, will be a quick run though of how to do it in three different init (initialization) modes:</p>

<ul>
<li>System V init (also known as classic init)</li>
<li>Upstart</li>
<li>systemd</li>
</ul>

<p>In <a href="https://indiareads/community/tutorials/how-to-configure-a-linux-service-to-start-automatically-after-a-crash-or-reboot-part-2-reference">Part 2</a>, we will explain why we ran the commands, and how they work behind the scenes. We will talk about startup scripts, important files, and configuration parameters for each init method. A lot of the discussion in Part 2 may seem theoretical, but will serve as useful reference for understanding the basics.</p>

<p>Part 1 will cover just the practical aspects of setting up automatic restarting.</p>

<h2 id="prerequisites">Prerequisites</h2>

<p>To follow this tutorial, you will need to create a number of IndiaReads Droplets (or create your own Linux servers), each with at least <strong>1 GB</strong> of RAM. We won't go into the details of creating a Droplet, but you can find more information <a href="https://indiareads/community/tutorials/how-to-create-your-first-digitalocean-droplet-virtual-server">here</a>.</p>

<p>We will use different distributions for our examples.</p>

<ul>
<li>Debian 6 x64 (this older OS is needed to demonstrate the System V init system)</li>
<li>Ubuntu 14.04 x64 (for Upstart)</li>
<li>CentOS 7 x64 (for systemd)</li>
<li>You should set up a sudo user on each server. To understand how sudo privileges work, see this IndiaReads <a href="https://indiareads/community/tutorials/how-to-edit-the-sudoers-file-on-ubuntu-and-centos">tutorial about enabling sudo access</a></li>
</ul>

<p>We advise you to keep the Droplets after following Part 1 of this tutorial, since we will use the same setup for <a href="https://indiareads/community/tutorials/how-to-configure-a-linux-service-to-start-automatically-after-a-crash-or-reboot-part-2-reference">Part 2</a>.</p>

<p><strong>You should not run any commands, queries, or configurations from this tutorial on a production Linux server.</strong> We're going to disrupt services as part of testing, and you wouldn't want your live server to hiccup.</p>

<h2 id="auto-starting-services-with-system-v">Auto-starting Services with System V</h2>

<p>Let's start our discussion with System V init, the oldest init system discussed here.</p>

<ul>
<li>Debian 6 and earlier</li>
<li>Ubuntu 9.04 and earlier</li>
<li>CentOS 5 and earlier</li>
</ul>

<p>With System V, most standard applications you can install, such as Nginx or MySQL, will <strong>start after reboot</strong> by default, but <strong>NOT start after a crash</strong> by default. They will come with their own init scripts in <code>/etc/init.d</code> already.</p>

<p>For custom applications, you'll have to create your own init scripts and enable the services to start automatically on your own.</p>

<p>Creating your own init script is beyond the scope of this article, but you can reference existing example scripts to help you build your own, should you need it. System V uses Bash for init scripts.</p>

<h2 id="auto-start-checklist-for-system-v">Auto-start Checklist for System V</h2>

<p>This section is a quick reference to make sure your service is set to automatically start.</p>

<p><strong>Configuration Checklist</strong></p>

<ul>
<li>Make sure the service has a functional Bash init script located at <code>/etc/init.d/service</code></li>
<li>Use the <code>update-rc.d</code> command to enable the service (or for a CentOS system, <code>chkconfig</code>):</li>
</ul>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">  sudo update-rc.d <span class="highlight">service</span> enable
</li></ul></code></pre>
<ul>
<li>This should create a symlink in <code>/etc/rc2.d</code> that looks like the following (do <strong>NOT</strong> create this manually):</li>
</ul>
<pre class="code-pre "><code langs="">  lrwxrwxrwx 1 root root  15 Jul 31 07:09 S02mysql -> ../init.d/<span class="highlight">service</span>
</code></pre>
<p>Note that you should also see links from directories <code>/etc/rc3.d</code> through <code>/etc/rc5.d</code>; learn more about these numbers when we discuss <em>runlevels</em>.</p>

<ul>
<li>Add a <code>respawn</code> line for this service at the bottom of the <code>/etc/inittab</code> file. Here's a generic example:</li>
</ul>
<div class="code-label " title="/etc/inittab">/etc/inittab</div><pre class="code-pre "><code langs="">    <span class="highlight">id</span>:2345:respawn:<span class="highlight">/bin/sh /path/to/application/startup</span>
</code></pre>
<ul>
<li>Stop, then start, the service:</li>
</ul>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">  sudo service <span class="highlight">service</span> stop
</li><li class="line" prefix="$">  sudo service <span class="highlight">service</span> start
</li></ul></code></pre>
<ul>
<li>Reboot the server.</li>
</ul>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">  sudo reboot
</li></ul></code></pre>
<p><strong>Test</strong></p>

<p>To test that these are working, you can:</p>

<ul>
<li>Reboot the server, then verify that the service is up</li>
<li>Search for the process number:</li>
</ul>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">  ps -ef | grep <span class="highlight">service</span>
</li></ul></code></pre>
<ul>
<li>Kill the process:</li>
</ul>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">  sudo kill -9 <span class="highlight">process_number</span>
</li></ul></code></pre>
<ul>
<li>Wait five minutes, then verify that the service is back up</li>
</ul>

<h2 id="step-1-—-connecting-to-your-debian-6-droplet">Step 1 — Connecting to Your Debian 6 Droplet</h2>

<p>Now we'll run through a practical example, using MySQL.</p>

<p>From the IndiaReads control panel, create a <strong>Debian 6.0 x64</strong> Droplet with 1 GB of RAM.</p>

<p>Once the Droplet has been initialized, use SSH to connect to the server (Windows users can connect using a tool like PuTTY).</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">ssh <span class="highlight">sammy</span>@<span class="highlight">your_server_ip</span>
</li></ul></code></pre>
<p>In the following instructions, we assume your account has sudo privileges.</p>

<h2 id="step-2-—-installing-mysql">Step 2 — Installing MySQL</h2>

<p>We'll use MySQL as our test service. Execute the next command to install MySQL Server:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo apt-get install mysql-server -y
</li></ul></code></pre>
<p>A graphical screen like the one shown below will appear asking for a new root password. Provide that:</p>

<p><img src="https://assets.digitalocean.com/articles/auto-restart-part-1/LDNF8JU.jpg" alt="Provide a root password for MySQL" /></p>

<p>Repeat the password in the next prompt:</p>

<p><img src="https://assets.digitalocean.com/articles/auto-restart-part-1/qrsnj7W.jpg" alt="Repeat root password at prompt" /></p>

<p>Press <code>ENTER</code> to confirm.</p>

<p>Lines will scroll by as MySQL is installed. Once the installation completes, run the following command to harden your installation:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">mysql_secure_installation
</li></ul></code></pre>
<p>This will ask for the current root password. Press <code>N</code> to keep the same password. Then, press <code>Y</code> to remove the anonymous user, disable remote root login, and remove the test database. Finally, press <code>Y</code> to reload the privilege tables.</p>

<p>Our MySQL installation should now be complete.</p>

<p>To check if the service is running, execute this command:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">service mysql status
</li></ul></code></pre>
<p>The output will show a few lines of information, one of which will show how long the MySQL service has been running (uptime).</p>
<pre class="code-pre "><code langs=""><div class="secondary-code-label " title="Output">Output</div>/usr/bin/mysqladmin  Ver 8.42 Distrib 5.1.73, for debian-linux-gnu on x86_64

. . .

Uptime:         4 days 18 hours 58 min 27 sec

Threads: 1  Questions: 18  Slow queries: 0  Opens: 15  Flush tables: 1  Open tables: 8  Queries per second avg: 0.0.
</code></pre>
<h2 id="step-3-—-configuring-mysql-to-auto-start-after-reboot">Step 3 — Configuring MySQL to Auto-start After Reboot</h2>

<p>By default, MySQL is already set to start after a reboot.</p>

<p>You should see this symlink to MySQL's init script in the <code>/etc/rc2.d</code> directory. Note that you should <strong>NOT</strong> try to create these symlinks manually; use the <code>update-rc.d</code> command to enable and disable services.</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">ls -l /etc/rc2.d
</li></ul></code></pre><pre class="code-pre "><code langs=""><div class="secondary-code-label " title="Output">Output</div>lrwxrwxrwx 1 root root  15 Jul 31 07:09 S02mysql -> ../init.d/mysql
</code></pre>
<p>As long as there is an <code>S</code> script under the default runlevel directory for the service, init will start the service when the server boots.</p>

<p>So, MySQL should be running. Now it's time to verify that it will auto-start at boot time. Reboot the machine with this command:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo reboot
</li></ul></code></pre>
<p>Once the server comes back online, connect to it with SSH.</p>

<p>Run the <code>service mysql status</code> command again. Again, the service will be shown as running. This means the service automatically starts when the operating system boots.</p>

<p>Not all services will be like this, though. In those cases we will have to manually configure the services for auto-restart. For Debian, the <code>update-rc.d</code> command lets you add (or remove) services to be automatically started at boot.</p>

<p>Let's disable the MySQL service and then see how to re-enable it for auto start. Let's run this command to disable MySQL:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo update-rc.d mysql disable
</li></ul></code></pre>
<p>To test, reboot the server again. </p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo reboot
</li></ul></code></pre>
<p>Connect to your server with SSH.</p>

<p>Try to connect to MySQL using the MySQL client tool:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">mysql -u root -p
</li></ul></code></pre>
<p>You will receive this message:</p>
<pre class="code-pre "><code langs=""><div class="secondary-code-label " title="Output">Output</div>ERROR 2002 (HY000): Can't connect to local MySQL server through socket '/var/run/mysqld/mysqld.sock' (2)
</code></pre>
<p>Re-enable the service:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo update-rc.d mysql enable
</li></ul></code></pre>
<p>The output will be: </p>
<pre class="code-pre "><code langs=""><div class="secondary-code-label " title="Output">Output</div>update-rc.d: using dependency based boot sequencing
</code></pre>
<p><span class="note">If you are running a CentOS system with System V, the commands will use <code>chkconfig</code> rather than <code>update-rc.d</code>.</span></p>

<p>Note that enabling a service for auto-start at boot time does not automatically start it if it is stopped. To start MySQL, run this command:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo service mysql start
</li></ul></code></pre>
<h2 id="step-4-—-configuring-mysql-to-auto-start-after-crash">Step 4 — Configuring MySQL to Auto-start After Crash</h2>

<p>Now that our service is running again, let's see if it automatically starts after a crash. With System V, it will <strong>NOT</strong> come up automatically by default.</p>

<p>We will emulate a crash by killing the process abruptly. Find its process ID by executing the following command:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">ps -ef | grep mysql
</li></ul></code></pre>
<p>The output will be similar to this:</p>
<pre class="code-pre "><code langs=""><div class="secondary-code-label " title="Output">Output</div>root      <span class="highlight">1167</span>     1  0 07:21 pts/0    00:00:00 /bin/sh /usr/bin/mysqld_safe
mysql     <span class="highlight">1292</span>  1167  0 07:21 pts/0    00:00:00 /usr/sbin/mysqld --basedir=/usr --datadir=/var/lib/mysql --user=mysql --pid-file=/var/run/mysqld/mysqld.pid --socket=/var/run/mysqld/mysqld.sock --port=3306
root      1293  1167  0 07:21 pts/0    00:00:00 logger -t mysqld -p daemon.error
root      1384  1123  0 07:21 pts/0    00:00:00 grep mysql
</code></pre>
<p>The main processes that run MySQL are <code>mysqld_safe</code> and <code>mysqld</code>. <code>mysqld_safe</code> is the parent process of <code>mysqld</code>.</p>

<p>In our example here, we can see they have process IDs 1167 and 1292 respectively. Their process numbers are highlighted in red above.</p>

<p>Let's emulate a crash with a <code>kill -9</code> command. Make sure to use your own process IDs:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo kill -9 <span class="highlight">1167</span>
</li><li class="line" prefix="$">sudo kill -9 <span class="highlight">1292</span>
</li></ul></code></pre>
<p>Check the service status:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo service mysql status
</li></ul></code></pre>
<p>The output will show that the service has stopped:</p>
<pre class="code-pre "><code langs=""><div class="secondary-code-label " title="Output">Output</div>MySQL is stopped..
</code></pre>
<p>Now that our service has crashed, how do we bring it up? Of course we can restart it, but that would be a manual process; we want restarting to be automatic. To make MySQL auto restart after a crash, we have to edit the <code>/etc/inittab</code> file.</p>

<p>We will talk about the <code>/etc/inittab</code> file in greater detail in Part 2, but for now, let's understand that it's the first file System V init reads when booting up.</p>

<p>Among other things, <code>/etc/inittab</code> decides how a process should behave if it crashes. For some processes it's meant to respawn the service again. We need to ensure MySQL is among those services. So let's make a copy of it first:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo cp /etc/inittab /etc/inittab.orig
</li></ul></code></pre>
<p><span class="warning">A note of caution: be extremely careful when editing the <code>/etc/inittab</code> file. If you make a mistake in your commands or delete any existing configuration, the system may not come up when you reboot it.</span></p>

<p>Open <code>/etc/inittab</code> with a text editor:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo nano /etc/inittab
</li></ul></code></pre>
<p>At the end of the file, add this line:</p>
<div class="code-label " title="/etc/inittab">/etc/inittab</div><pre class="code-pre "><code langs="">ms:2345:respawn:/bin/sh /usr/bin/mysqld_safe
</code></pre>
<p>So what are we doing here?</p>

<p>Well, we are putting a command in the <code>/etc/inittab</code> file to <em>respawn</em> the <code>mysqld_safe process</code> when it crashes. It has four fields, each one separated from the other by a colon (:).</p>

<ul>
<li><code>ms:</code> The first two characters specify an <em>id</em> for the process.</li>
<li><code>2345:</code> The second field specifies the <em>runlevels</em> it's supposed to apply to. In this case, it's for runlevels 2, 3, 4, and 5</li>
<li><code>respawn:</code> The third field specifies the <em>action</em> (we are respawning the service)</li>
<li><code>/bin/sh /usr/bin/mysqld_safe</code>: Finally, the fourth field is the <em>process</em> (the command to execute)</li>
</ul>

<p>We'll come back to the <code>/etc/inittab</code> file in more detail in Part 2, and see how it helps with auto-starting MySQL after a crash. For now, save the file and exit the editor.</p>

<p>Start the service again:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo service mysql start
</li></ul></code></pre>
<p>Reboot the server so the change takes effect:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo reboot
</li></ul></code></pre>
<p>Now, repeat the commands to locate the process numbers, kill the processes, and check the status again, starting with <code>ps -ef | grep mysql</code>.</p>

<p><strong>Wait for five minutes</strong> or so and then execute the command:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo service mysql status
</li></ul></code></pre>
<p>You should see that this time, MySQL has started automatically after the crash.</p>

<p>That's it! MySQL will now automatically start after a service crash or system reboot.</p>

<h2 id="auto-starting-services-with-upstart">Auto-starting Services with Upstart</h2>

<p>Upstart is another init method, which was first introduced in Ubuntu 6. It became the default in Ubuntu 9.10, and was later adopted into RHEL 6 and its derivatives. Google's Chrome OS also uses Upstart.</p>

<ul>
<li>Ubuntu 9.10 to Ubuntu 14.10, including Ubuntu 14.04</li>
<li>CentOS 6</li>
</ul>

<p>While it's going strong on the current LTS version of Ubuntu (14.04 at the time of writing), it's being phased out everywhere in favor of systemd, which we cover in the last section.</p>

<p>Upstart is better than System V in handling system services, and it's also easy to understand. However, we will not dive deeply into Upstart in this part of the tutorial; there is a <a href="https://indiareads/community/tutorials/the-upstart-event-system-what-it-is-and-how-to-use-it">very good tutorial on Upstart</a> in the IndiaReads community.</p>

<p>Today we will primarily focus on Upstart configuration files and see how to use them to auto-start services.</p>

<p>Upstart uses configuration files to control services. The files are under the <code>/etc/init</code> directory. The files have plain text content with easy-to-read sections called <em>stanzas</em>. Each stanza describes a different aspect of the service and how it should behave.</p>

<p>By default, most standard applications you can install, such as Nginx or MySQL, will <strong>start after reboot</strong> and also <strong>start after a crash</strong> by default, so you don't have to do anything to make this work. They will come with their own init scripts in <code>/etc/init</code> already.</p>

<p>For custom applications, you will have to set this up yourself. To learn how to create your own custom Upstart script, read the <a href="https://indiareads/community/tutorials/the-upstart-event-system-what-it-is-and-how-to-use-it">introductory Upstart tutorial</a> referenced earlier.</p>

<h2 id="auto-start-checklist-for-upstart">Auto-start Checklist for Upstart</h2>

<p>This section is a quick reference to make sure your service is set to automatically start.</p>

<p><strong>Configuration Checklist</strong></p>

<ul>
<li>Make sure the service has a functional Upstart init script located at <code>/etc/init/<span class="highlight">service</span>.conf</code>

<ul>
<li>The <code>/etc/init/<span class="highlight">service</span>.conf</code> file should contain a line like <code>start on runlevel [2345]</code> to enable automatic starting after a reboot</li>
<li>The <code>/etc/init/<span class="highlight">service</span>.conf</code> file should also contain a line like <code>respawn</code> to enable the service to respawn after a crash</li>
</ul></li>
<li>Make sure there is <strong>no override file</strong> for the service: <code>/etc/init/<span class="highlight">service</span>.override</code></li>
</ul>

<p>(There would be one only if you or another admin made one earlier)</p>

<ul>
<li>Stop, then start, the service:</li>
</ul>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">  sudo initctl stop <span class="highlight">service</span>
</li><li class="line" prefix="$">  sudo initctl start <span class="highlight">service</span>
</li></ul></code></pre>
<ul>
<li>Reboot the server.</li>
</ul>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">  sudo reboot
</li></ul></code></pre>
<p><strong>Test</strong></p>

<p>To test that these are working, you can:</p>

<ul>
<li>Reboot the server, then verify that the service is up</li>
<li>Search for the process number:</li>
</ul>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">  ps -ef | grep <span class="highlight">service</span>
</li></ul></code></pre>
<ul>
<li>Kill the process:</li>
</ul>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">  sudo kill -9 <span class="highlight">process_number</span>
</li></ul></code></pre>
<ul>
<li>Within a few seconds, verify that the service is back up</li>
</ul>

<h2 id="step-1-—-connecting-to-your-ubuntu-14-04-droplet">Step 1 — Connecting to Your Ubuntu 14.04 Droplet</h2>

<p>We'll use an Ubuntu 14.04 server, running MySQL, to demonstrate Upstart.</p>

<p>Create a Droplet with 1 GB of RAM and choose <strong>Ubuntu 14.04 x64</strong> as the base image.</p>

<p>Connect to the server with SSH (Windows users can connect using a tool like PuTTy).</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">ssh <span class="highlight">sammy</span>@<span class="highlight">your_server_ip</span>
</li></ul></code></pre>
<p>In the following instructions, we assume your account has sudo privileges.</p>

<h2 id="step-2-—-installing-mysql">Step 2 — Installing MySQL</h2>

<p>Now we'll install MySQL.</p>

<p>Execute the next command to update the package list:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo apt-get update
</li></ul></code></pre>
<p>Install MySQL Server:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo apt-get install mysql-server -y
</li></ul></code></pre>
<p>Create a new root password for MySQL, and confirm it when prompted.</p>

<p>Once the installation completes, run the <code>mysql_secure_installation</code> command:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">mysql_secure_installation
</li></ul></code></pre>
<p>Provide the same answers to the prompts as you did before when installing in Debian (see the earlier section).</p>

<h2 id="step-3-—-configuring-mysql-to-auto-start-after-reboot">Step 3 — Configuring MySQL to Auto-start After Reboot</h2>

<p>By default, MySQL will automatically start after a reboot. It's useful to look at its configuration so you can set up your own services this way.</p>

<p>First of all, let's check whether the MySQL server process is running:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo initctl status mysql
</li></ul></code></pre>
<p>You should see output like this:</p>
<pre class="code-pre "><code langs=""><div class="secondary-code-label " title="Output">Output</div>mysql start/running, process 2553
</code></pre>
<p>Reboot the server:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo reboot
</li></ul></code></pre>
<p>When the server comes back online, use SSH to reconnect.</p>

<p>Check MySQL's status: </p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo initctl status mysql
</li></ul></code></pre>
<p>The output will show that MySQL has automatically started. So, we don't have to do anything specific here to enable the service.</p>

<p>Keep in mind that may not be the case for other application daemons where you have to manually enable the service by creating your own Upstart file in the <code>/etc/init/</code> directory.</p>

<p>Also, how does Upstart know MySQL should auto-start at reboot?</p>

<p>Let's take a look at MySQL's Upstart init file. Open the <code>/etc/init/mysql.conf</code> file in a text editor: </p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo nano /etc/init/mysql.conf
</li></ul></code></pre>
<p>An Upstart file is not a shell script like we saw on our Debian machine.</p>

<p>The init configuration file for MySQL will have script blocks for pre-start and post-start events. These code blocks tell the Upstart system what to execute when the mysqld process is coming up or has already come up.</p>

<p>Let's take a closer look at the first ten lines of the file:</p>
<div class="code-label " title="/etc/init/mysql.conf">/etc/init/mysql.conf</div><pre class="code-pre "><code langs="">...
description     "MySQL Server"
author          "Mario Limonciello <superm1@ubuntu.com>"

start on runlevel [2345]
stop on starting rc RUNLEVEL=[016]

respawn
respawn limit 2 5
</code></pre>
<p>We can see MySQL is supposed to start on runlevels 2, 3, 4 and 5, and it's not supposed to run on runlevels 0, 1 and 6.</p>

<p>This is where we define service start-up behaviour for an Upstart daemon. Unlike System V where we used the <code>update-rc.d</code> or <code>chkconfig</code> commands, we use service configuration files in Upstart. All we have to do is to add/change the <code>start</code> stanza. In Part 2, we will play with this file and see how enabling and disabling the MySQL service affects this file, and vice-versa.</p>

<p>The <code>respawn</code> directives restart the service after a crash, so we'll discuss them in the next step.</p>

<h2 id="step-4-—-configuring-mysql-to-auto-start-after-crash">Step 4 — Configuring MySQL to Auto-start After Crash</h2>

<p>You should still have <code>/etc/init/mysql.conf</code> open.</p>

<p>The <code>respawn</code> directive is self-explanatory: MySQL will start if it crashes. This is already enabled by default.</p>

<p>The directive after that is more interesting: the <code>respawn limit</code> directive stipulates how many times Linux will try to restart the crashed service in an interval specified in seconds. In this case, the first argument (<code>2</code>) is the number of tries, and the second one (<code>5</code>) is the interval. If the service does not start up (respawn) successfully within this threshold, it will be kept in a stopped state. This sane default behavior because if a service is crashing continuously, it's better to disable it than affect your entire system's stability.</p>

<p>For now, exit the text editor without making any changes.</p>

<p>As we have just seen, by default MySQL is also configured to come back automatically after a crash.</p>

<p>To test this, let's check the service PID:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo initctl status mysql
</li></ul></code></pre>
<p>The new PID (after reboot) for our system should look like this:</p>
<pre class="code-pre "><code langs=""><div class="secondary-code-label " title="Output">Output</div>mysql start/running, process <span class="highlight">961</span>
</code></pre>
<p>Note the process ID for your test case. Next, emulate a crash by killing the process with a <code>kill -9</code> command, using your own process number:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo kill -9 <span class="highlight">961</span>
</li></ul></code></pre>
<p>Check the MySQL status now. It should be running (immediately or within a few seconds) with a new PID:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo initctl status mysql
</li></ul></code></pre>
<p>In our case the new PID is 1552:</p>
<pre class="code-pre "><code langs=""><div class="secondary-code-label " title="Output">Output</div>mysql start/running, process <span class="highlight">1552</span>
</code></pre>
<p>If you'd like, you can kill it again. It will come up again each time:</p>

<p>This is happening because of the <code>respawn</code> directive in the <code>mysql.conf</code> file.</p>
<div class="code-label " title="/etc/init/mysql.conf">/etc/init/mysql.conf</div><pre class="code-pre "><code langs="">respawn
</code></pre>
<p>MySQL comes with the ability to restart after a crash by default, but for other services you may have to add this directive manually in the Upstart file. Again, in Part 2, we will see how we can change the crash behaviour from the config file.   </p>

<h2 id="auto-starting-services-with-systemd">Auto-starting Services with systemd</h2>

<p>systemd is a <em>system and service manager</em> for Linux which has become the de facto initialization daemon for most new Linux distributions.</p>

<p>First implemented in Fedora, systemd now comes with RHEL 7 and its derivatives like CentOS 7. Ubuntu 15.04 ships with native systemd as well. Other distributions have either incorporated systemd, or announced they will soon.</p>

<ul>
<li>Debian 7 and Debian 8</li>
<li>Ubuntu 15.04</li>
<li>CentOS 7</li>
</ul>

<p>systemd is backwards-compatible with System V commands and initialization scripts.</p>

<p>That means any System V service will also run under systemd. Most Upstart and System V administrative commands have been modified to work under systemd. That's why it's often referred to as a <em>drop-in replacement</em> for System V init.</p>

<p>With systemd, most standard applications you can install, such as Nginx or MySQL, will <strong>start after reboot</strong> and also <strong>start after a crash</strong> by default, so you don't have to do anything to make this work. They will come with their own init scripts in <code>/etc/systemd/system</code> already.</p>

<p>For custom applications, you'll have to create your own init scripts and enable the services to start automatically on your own. We won't go into the specifics of what goes into a custom init script in detail here, but you can read more about systemd in this <a href="https://indiareads/community/tutorials/how-to-use-systemctl-to-manage-systemd-services-and-units">introductory systemd article</a>.</p>

<h2 id="auto-start-checklist-for-systemd">Auto-start Checklist for systemd</h2>

<p>This section is a quick reference to make sure your service is set to automatically start.</p>

<p><strong>Configuration Checklist</strong></p>

<ul>
<li>Make sure the service has a functional systemd init script located at <code>/etc/systemd/system/multi-user.target.wants/<span class="highlight">service</span>.service</code></li>
<li>Use the <code>systemctl</code> command to enable the service:</li>
</ul>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">  sudo systemctl enable <span class="highlight">service</span>.service
</li></ul></code></pre>
<ul>
<li>This should create a symlink in <code>/etc/systemd/system/multi-user.target.wants/</code> that looks like the following (do <strong>NOT</strong> create this manually):</li>
</ul>
<pre class="code-pre "><code langs="">  lrwxrwxrwx 1 root root 38 Aug  1 04:43 /etc/systemd/system/multi-user.target.wants/<span class="highlight">service</span>.service -> /usr/lib/systemd/system/<span class="highlight">service</span>.service
</code></pre>
<p>This will enable automatic starting after a reboot.</p>

<ul>
<li>The <code>/etc/systemd/system/multi-user.target.wants/<span class="highlight">service</span>.service</code> file should also contain a line like <code>Restart=always</code> under the <code>[Service]</code> section of the file to enable the service to respawn after a crash</li>
<li>Reload the systemd daemon, followed by a restart of the service:</li>
</ul>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">  sudo systemctl daemon-reload
</li><li class="line" prefix="$">  sudo systemctl restart <span class="highlight">service</span>.service
</li></ul></code></pre>
<p><strong>Test</strong></p>

<p>To test that these are working, you can:</p>

<ul>
<li>Reboot the server, then verify that the service is up</li>
</ul>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">  sudo reboot
</li></ul></code></pre>
<ul>
<li>Search for the process number:</li>
</ul>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">  ps -ef | grep <span class="highlight">service</span>
</li></ul></code></pre>
<ul>
<li>Kill the process:</li>
</ul>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">  sudo kill -9 <span class="highlight">process_number</span>
</li></ul></code></pre>
<ul>
<li>Within a few seconds, verify that the service is back up</li>
</ul>

<h2 id="step-1-—-connecting-to-your-centos-7-droplet">Step 1 — Connecting to Your CentOS 7 Droplet</h2>

<p>We'll use CentOS 7 and MySQL to show you how to configure systemd services.</p>

<p>Create a Droplet with 1 GB of RAM and choose <strong>CentOS 7 x64</strong> as its base image.</p>

<p>Use SSH to connect to the server (Windows users can connect using a tool like PuTTy).</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">ssh <span class="highlight">sammy</span>@<span class="highlight">your_server_ip</span>
</li></ul></code></pre>
<p>We assume you are using an account with sudo privileges.</p>

<h2 id="step-2-—-installing-mysql">Step 2 — Installing MySQL</h2>

<p>Run the following commands to download and install the MySQL Community server repo:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo wget http://repo.mysql.com/mysql-community-release-el7-5.noarch.rpm
</li></ul></code></pre><pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo rpm -ivh mysql-community-release-el7-5.noarch.rpm
</li></ul></code></pre>
<p>Install MySQL server:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo yum install mysql-server -y
</li></ul></code></pre>
<p>Once the installation completes, start the mysqld service.</p>

<p>(Note that this is not the same as when we installed MySQL under Debian and Ubuntu. In the other distributions, MySQL started automatically.)</p>

<p>Also, we are using a new systemd command called <code>systemctl</code> to control the service:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo systemctl start mysqld
</li></ul></code></pre>
<p>Next, run the <code>mysql_secure_installation</code> command. </p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">mysql_secure_installation
</li></ul></code></pre>
<p>In this case the MySQL root password will be empty, so you should choose to create a new password. Provide the same answers to the other prompts as you did when installing under Debian or Ubuntu (see the earlier Debian section for details).</p>

<h2 id="step-3-—-configuring-mysql-to-auto-start-after-reboot">Step 3 — Configuring MySQL to Auto-start After Reboot</h2>

<p>By default, MySQL is configured to start automatically after a reboot. Let's look at how this works.</p>

<p>To check whether the <code>mysqld</code> daemon was configured to start automatically at boot time, execute the <code>systemctl</code> command with the <code>is-enabled</code> option:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo systemctl is-enabled mysqld.service
</li></ul></code></pre>
<p>The result will be:</p>
<pre class="code-pre "><code langs=""><div class="secondary-code-label " title="Output">Output</div>enabled
</code></pre>
<p>Let's reboot the machine:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo reboot
</li></ul></code></pre>
<p>When the server comes back up, connect to it with SSH.</p>

<p>Execute the following command to check the service status:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo systemctl status mysqld.service
</li></ul></code></pre>
<p>The output will show the service is running:</p>
<pre class="code-pre "><code langs=""><div class="secondary-code-label " title="Output">Output</div>mysqld.service - MySQL Community Server
   Loaded: loaded (/usr/lib/systemd/system/mysqld.service; enabled)
   Active: active (running) since Fri 2015-07-31 21:58:03 EDT; 1min 52s ago
  Process: 662 ExecStartPost=/usr/bin/mysql-systemd-start post (code=exited, status=0/SUCCESS)
  Process: 625 ExecStartPre=/usr/bin/mysql-systemd-start pre (code=exited, status=0/SUCCESS)
 Main PID: 661 (mysqld_safe)
...
</code></pre>
<p>To disable the service, run the following command:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo systemctl disable mysqld.service
</li></ul></code></pre>
<p>This will not stop the service, but disable it. In fact, the output shows the symbolic links have been deleted:</p>
<pre class="code-pre "><code langs=""><div class="secondary-code-label " title="Output">Output</div>rm '/etc/systemd/system/multi-user.target.wants/mysqld.service'
rm '/etc/systemd/system/mysql.service'
</code></pre>
<p>If you'd like, you can reboot the server and test again to see if the MySQL server is running (it won't be).</p>

<p>Or, execute <code>systemctl is-enabled</code> again; we should get a response of <code>disabled</code>:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo systemctl is-enabled mysqld.service
</li></ul></code></pre><pre class="code-pre "><code langs=""><div class="secondary-code-label " title="Output">Output</div>disabled
</code></pre>
<p>Enable the service again:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo systemctl enable mysqld.service
</li></ul></code></pre>
<p>The output will show the symlinks being recreated:</p>
<pre class="code-pre "><code langs=""><div class="secondary-code-label " title="Output">Output</div>ln -s '/usr/lib/systemd/system/mysqld.service' '/etc/systemd/system/mysql.service'
ln -s '/usr/lib/systemd/system/mysqld.service' '/etc/systemd/system/multi-user.target.wants/mysqld.service'
</code></pre>
<p>Your service will automatically start after a reboot.</p>

<h2 id="step-4-—-configuring-mysql-to-auto-start-after-crash">Step 4 — Configuring MySQL to Auto-start After Crash</h2>

<p>Now we will see how MySQL is configured to auto-start after a crash.</p>

<p>First, open the <code>mysqld.service</code> unit file in an editor (remember, systemd services use unit files for configuration):</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo nano /etc/systemd/system/multi-user.target.wants/mysqld.service
</li></ul></code></pre>
<p>At the end of the file, there is a directive for restart:</p>
<div class="code-label " title="/etc/systemd/system/multi-user.target.wants/mysqld.service">/etc/systemd/system/multi-user.target.wants/mysqld.service</div><pre class="code-pre "><code langs="">[Unit]
...

[Install]
...

[Service]
...
...
<span class="highlight">Restart=always</span>
...
</code></pre>
<p>The value of the <code>Restart</code> parameter is set to <code>always</code>. This means MySQL service will restart for clean or unclean exit codes or timeouts. </p>

<p>That's where an automatic restart is defined in systemd.</p>

<p>Just like the <code>respawn</code> directive in Upstart, the <code>Restart</code> parameter in systemd defines how the service should behave if it crashes.</p>

<p>Not all systemd services have this capability enabled by default; to make a service come up after a crash, all we have to do is to add this extra directive under the <code>[Service]</code> section of the service unit file. If the section header does not exist, we have to add the <code>[Service]</code> header too.</p>

<p>To emulate a crash, first exit the editor and then check the MySQL process ID:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo systemctl status mysqld.service
</li></ul></code></pre><pre class="code-pre "><code langs=""><div class="secondary-code-label " title="Output">Output</div>mysqld.service - MySQL Community Server
   Loaded: loaded (/usr/lib/systemd/system/mysqld.service; enabled)
   Active: active (running) since Fri 2015-07-31 21:58:03 EDT; 1h 7min ago
 Main PID: <span class="highlight">661</span> (mysqld_safe)
...
</code></pre>
<p>Kill this process with a <code>kill -9</code> signal. In our case the main PID is 661; replace the PID with your own:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo kill -9 <span class="highlight">661</span>
</li></ul></code></pre>
<p>Check the status:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo systemctl status mysqld.service
</li></ul></code></pre>
<p>The output will show that MySQL has restarted with a new PID (in our case the new Process ID is 11217):</p>
<pre class="code-pre "><code langs=""><div class="secondary-code-label " title="Output">Output</div>mysqld.service - MySQL Community Server
   Loaded: loaded (/usr/lib/systemd/system/mysqld.service; enabled)
   Active: active (running) since Fri 2015-07-31 23:06:38 EDT; 1min 8s ago
  Process: 11218 ExecStartPost=/usr/bin/mysql-systemd-start post (code=exited, status=0/SUCCESS)
  Process: 11207 ExecStartPre=/usr/bin/mysql-systemd-start pre (code=exited, status=0/SUCCESS)
 Main PID: <span class="highlight">11217</span> (mysqld_safe)
...
...
</code></pre>
<p>So you see that the service comes up even after a crash.</p>

<h2 id="conclusion">Conclusion</h2>

<p>In this first part of the tutorial, we have seen how System V, Upstart, and systemd services can be configured to auto-start after a reboot or crash.</p>

<p>We have also seen the files, configuration parameters, and commands that control this behavior.</p>

<p>This was more of a hands-on introduction. We will cover the concepts and basics in greater detail in the next installment of the series.</p>

<p>Don't delete the Droplets yet - keep them running, as we will come back to them in <a href="https://indiareads/community/tutorials/how-to-configure-a-linux-service-to-start-automatically-after-a-crash-or-reboot-part-2-reference">the next part</a>.</p>

    