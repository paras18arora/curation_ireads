<link rel='stylesheet' href='digital_ocean.css'><br> <br> 
      <h3 id="introduction">Introduction</h3>

<p>In this second part of the tutorial about starting Linux services automatically, we'll take a step back and explain init processes in more detail. You should gain a good understanding of how they control a daemon's start-up behavior.</p>

<p>In the <a href="https://indiareads/community/tutorials/how-to-configure-a-linux-service-to-start-automatically-after-a-crash-or-reboot-part-1-practical-examples">first part</a> of this tutorial series we shared some practical examples using MySQL for how to enable a Linux service to auto-start after a crash or reboot.</p>

<p>We saw how to do this from three different <em>init</em> modes: System V, Upstart, and systemd. Read the <a href="https://indiareads/community/tutorials/how-to-configure-a-linux-service-to-start-automatically-after-a-crash-or-reboot-part-1-practical-examples">first tutorial</a> for a refresher on which distributions use which init system by default.</p>

<p>In this tutorial, we will take a step back and explain why we ran the commands and edited the config files that we did. We'll start with the System V init daemon. We will also see why it was replaced over time with newer init modes.</p>

<h3 id="prerequisites">Prerequisites</h3>

<p>To follow this tutorial, you will need the three IndiaReads Droplets that you created <a href="https://indiareads/community/tutorials/how-to-configure-a-linux-service-to-start-automatically-after-a-crash-or-reboot-part-1-practical-examples">before</a>.</p>

<p>We had:</p>

<ul>
<li>A Debian 6 server running MySQL</li>
<li>An Ubuntu 14.04 server running MySQL</li>
<li>A CentOS 7 server running MySQL</li>
</ul>

<p>We recommend you go back to Part 1 of this series and create the Droplets first. </p>

<p>Also, you will need to be the root user or have sudo privilege on the servers. To understand how sudo privileges work see <a href="https://indiareads/community/tutorials/how-to-edit-the-sudoers-file-on-ubuntu-and-centos">this IndiaReads tutorial about sudo</a>.</p>

<p><strong>You should not run any commands, queries or configurations from this tutorial on a production Linux server.</strong></p>

<h2 id="runlevels">Runlevels</h2>

<p>A <em>runlevel</em> represents the current state of a Linux system.</p>

<p>The concept comes from System V init, where the Linux system boots, initializes the kernel, and then enters one (and only one) runlevel.</p>

<p>For example, a runlevel can be the shutdown state of a Linux server, a single-user mode, the restart mode, etc. Each mode will dictate what services can be running in that state.</p>

<p>Some services can run in one or more runlevels but not in others.</p>

<p>Runlevels are denoted by single digits and they can have a value between 0 and 6. The following list shows what each of these levels mean: </p>

<ul>
<li><strong>Runlevel 0:</strong> System shutdown</li>
<li><strong>Runlevel 1:</strong> Single-user, rescue mode</li>
<li><strong>Runlevels 2, 3, 4:</strong> Multi-user, text mode with networking enabled</li>
<li><strong>Runlevel 5:</strong> Multi-user, network enabled, graphical mode</li>
<li><strong>Runlevel 6:</strong> System reboot</li>
</ul>

<p>Runlevels 2, 3, and 4 vary by distribution. For example, some Linux distributions don't implement runlevel 4, while others do. Some distributions have a clear distinction between these three levels. In general, runlevel 2, 3 or 4 means a state where Linux has booted in multi-user, network enabled, text mode.</p>

<p>When we enable a service to auto-start, we are actually adding it to a runlevel. In System V, the OS will start with a particular runlevel; and, when it starts, it will try to start all the services that are associated with that runlevel.</p>

<p>Runlevels become <em>targets</em> in systemd, which we'll discuss in the systemd section.</p>

<h2 id="init-and-pid-1">Init and PID 1</h2>

<p><strong>init</strong> is the first process that starts in a Linux system after the machine boots and the kernel loads into memory.</p>

<p>Among other things, it decides how a user process or a system service should load, in what order, and whether it should start automatically.</p>

<p>Every process in Linux has a process ID (PID) and <strong>init</strong> has a PID of 1. It's the parent of all other processes that subsequently spawn as the system comes online.</p>

<p><strong>History of Init</strong></p>

<p>As Linux has evolved, so has the behavior of the init daemon. Originally, Linux started out with System V init, the same that was used in UNIX. Since then, Linux has implemented the <strong>Upstart</strong> init daemon (created by Ubuntu) and now the <strong>systemd</strong> init daemon (first implemented by Fedora).</p>

<p>Most Linux distributions have gradually migrated away from System V or on their way to phasing it out, keeping it only for backward compatibility. FreeBSD, a variant of UNIX, uses a different implementation of System V, known as BSD init. Older versions of Debian use SysVinit too.</p>

<p>Each version of the init daemon has different ways of managing services. The reason behind these changes was the need for a robust service management tool that would handle not only services, but devices, ports, and other resources; that would load resources in parallel, and that would gracefully recovering from a crash.</p>

<h2 id="system-v-init-sequence">System V Init Sequence</h2>

<p>System V uses an <code>inittab</code> file, which later init methods like Upstart have kept for backwards compatibility.</p>

<p>Let's run through System V's startup sequence:</p>

<ol>
<li>The init daemon is created from the binary file <code>/sbin/init</code></li>
<li>The first file the init daemon reads is <code>/etc/inittab</code></li>
<li>One of the entries in this file decides the runlevel the machine should boot into. For example, if the value for the runlevel is specified as 3, Linux will boot in multi-user, text mode with networking enabled. (This runlevel is known as the default runlevel)</li>
<li>Next, the init daemon looks further into the <code>/etc/inittab</code> file and reads what <em>init scripts</em> it needs to run for that runlevel</li>
</ol>

<p>So when the init daemon finds what init scripts its needs to run for the given runlevel, it's essentially finding out what services it needs to start up. These init scripts are where you can configure startup behavior for individual services, like we did for MySQL in the first tutorial.</p>

<p>Next, let's look at init scripts in detail.</p>

<h2 id="system-v-configuration-files-init-scripts">System V Configuration Files: Init Scripts</h2>

<p>An init script is what controls a specific service, like MySQL Server, in System V.</p>

<p>Init scripts for services are either provided by the application's vendor or come with the Linux distribution (for native services). We can also create our own init scripts for custom created services.</p>

<p>When a process or service such as MySQL Server starts, its binary program file has to load into memory.</p>

<p>Depending on how the service is configured, this program may have to keep executing in the background continuously (and accept client connections). The job of starting, stopping, or reloading this binary application is handled by the service's init script. It's called the init script because it <em>initializes</em> the service.</p>

<p>In System V, an init script is a shell script.</p>

<p>Init scripts are also called <em>rc</em> (run command) scripts.</p>

<h3 id="directory-structure">Directory Structure</h3>

<p>The <code>/etc</code> directory is the parent directory for init scripts.</p>

<p>The actual location for init shell scripts is under <code>/etc/init.d</code>. These scripts are symlinked to the <code>rc</code> directories.</p>

<p>Within the <code>/etc</code> directory, we have a number of <code>rc</code> directories, each with a number in its name.</p>

<p>The numbers represent different runlevels. So we have <code>/etc/rc0.d</code>, <code>/etc/rc1.d</code>, <code>/etc/rc2.d</code> and so on.</p>

<p>Then, within each <code>rc<span class="highlight">n</span>.d</code> directory, we have files that start with either <code>K</code> or <code>S</code> in their file name, followed by two digits. These are symbolic link files that point back to the actual init shell scripts. Why the <code>K</code> and <code>S</code>? K means Kill (i.e. stop) and "S" stands for Start.</p>

<p>The two digits represents the order of execution of the script. So if we have a file named K25<em>some_script</em>, it will execute before K99<em>another_script</em>.</p>

<h3 id="startup">Startup</h3>

<p>Let's pick back up with our startup sequence. So how are the init scripts called? Who calls them?</p>

<p>The K and S scripts are not called directly by the init daemon, but by another script: the <code>/etc/init.d/rc</code> script.</p>

<p>If you remember, the <code>/etc/inittab</code> file tells the init daemon what runlevel the system should enter by default. For each runlevel, a line in the <code>/etc/inittab</code> file calls the <code>/etc/init.d/rc</code> script, passing on that runlevel as a parameter. Based on this parameter, the script then calls the files under the corresponding <code>/etc/rc<span class="highlight">n</span>.d</code> directory. So, if the server boots with runlevel 2, scripts under the <code>/etc/rc2.d</code> will be called; for runlevel 3, scripts under <code>/etc/rc3.d</code> are executed, and so on.</p>

<p>Within an <code>rc</code> directory, first, all K scripts are run in numerical order with an argument of "stop", and then all S scripts are run in similar fashion with an argument of "start." Behind the scenes, the corresponding init shell scripts will be called with stop and start parameters respectively.</p>

<p>Now since the files under the <code>/etc/rc<span class="highlight">n</span>.d</code> directories (<code>K<span class="highlight">nn</span></code> and <code>S<span class="highlight">nn</span></code> files) are symbolic links only, calling them means calling the actual init shell scripts with stop and start parameters.</p>

<p>To sum up, when the Linux server enters a runlevel, certain scripts will be run to stop some services while others will be run to start other services.</p>

<span class="note"><p>
This calling of init scripts also happens whenever the system switches to a new runlevel: the corresponding <code>/etc/rc<n>.d</code> directory scripts are executed. And since those K and S files are nothing but links, the actual shell scripts under the <code>/etc/init.d</code> directory are executed with the appropriate start or stop argument.</p>

<p>The whole process ensures any service not supposed to run in that runlevel is stopped and all services supposed to run in that runlevel are started.</p></span>

<h2 id="system-v-auto-starting">System V Auto-Starting</h2>

<p>As we enable a service to auto-start at boot time, we are actually modifying the init behavior.</p>

<p>So, for example, when we enable a service to auto-start at runlevel 3, behind the scenes the process creates the appropriate links in the <code>/etc/rc3.d</code> directory. </p>

<p>If this sounds confusing, don't worry - we will see what it all means in a minute.</p>

<h2 id="system-v-example">System V Example</h2>

<p>We'll go back to our MySQL service example, this time with more theory.</p>

<h3 id="step-1-—-logging-in-to-debian-droplet">Step 1 — Logging in to Debian Droplet</h3>

<p>For the purpose of this part of the tutorial, we will go back to the Debian 6 Droplet we created in Part 1. Use the SSH command to connect to the server (Windows users can connect using a tool like PuTTy).</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">ssh sammy@<span class="highlight">your_server_ip</span>
</li></ul></code></pre>
<h3 id="step-2-—-looking-at-inittab">Step 2 — Looking at inittab</h3>

<p>Run the following command to see the <code>inittab</code> file contents:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">cat /etc/inittab | grep initdefault
</li></ul></code></pre>
<p>The output should be something like this:</p>
<pre class="code-pre "><code langs=""><div class="secondary-code-label " title="Output">Output</div><span class="highlight">id:2:initdefault:</span>
</code></pre>
<p>The 2 after the id field shows the system is configured to start with runlevel 2. That's the default runlevel. In this case Debian designates 2 as multi-user, text mode.  If you execute the following command: </p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">cat /etc/inittab | grep Runlevel
</li></ul></code></pre>
<p>the output confirms this:</p>
<pre class="code-pre "><code langs=""><div class="secondary-code-label " title="Output">Output</div># Runlevel 0 is halt.
# Runlevel 1 is single-user.
<span class="highlight"># Runlevels 2-5 are multi-user.</span>
# Runlevel 6 is reboot.
</code></pre>
<h3 id="step-3-—-looking-at-the-rc-directories">Step 3 — Looking at the rc Directories</h3>

<p>Run the following command to list the <code>rc</code> directories. You should see there are six of these:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">ls -ld /etc/rc*.d
</li></ul></code></pre><pre class="code-pre "><code langs=""><div class="secondary-code-label " title="Output">Output</div>drwxr-xr-x 2 root root 4096 Jul 31 07:09 /etc/rc0.d
drwxr-xr-x 2 root root 4096 Jul 31 07:09 /etc/rc1.d
drwxr-xr-x 2 root root 4096 Jul 31 07:21 /etc/rc2.d
drwxr-xr-x 2 root root 4096 Jul 31 07:21 /etc/rc3.d
drwxr-xr-x 2 root root 4096 Jul 31 07:21 /etc/rc4.d
drwxr-xr-x 2 root root 4096 Jul 31 07:21 /etc/rc5.d
drwxr-xr-x 2 root root 4096 Jul 31 07:09 /etc/rc6.d
drwxr-xr-x 2 root root 4096 Jul 23  2012 /etc/rcS.d
</code></pre>
<p>Since the system boots in runlevel 2 (default init from the inittab file), scripts under the <code>/etc/rc2.d</code> directory will execute at system startup.</p>

<p>List the contents of this directory:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">ls -l /etc/rc2.d
</li></ul></code></pre>
<p>This shows the files are nothing but symbolic links, each pointing to script files under /etc/init.d:</p>
<pre class="code-pre "><code langs=""><div class="secondary-code-label " title="Output">Output</div>. . .
lrwxrwxrwx 1 root root  17 Jul 23  2012 S01rsyslog -> ../init.d/rsyslog
lrwxrwxrwx 1 root root  22 Jul 23  2012 S02acpi-support -> ../init.d/acpi-support
lrwxrwxrwx 1 root root  15 Jul 23  2012 S02acpid -> ../init.d/acpid
lrwxrwxrwx 1 root root  17 Jul 23  2012 S02anacron -> ../init.d/anacron
lrwxrwxrwx 1 root root  13 Jul 23  2012 S02atd -> ../init.d/atd
lrwxrwxrwx 1 root root  14 Jul 23  2012 S02cron -> ../init.d/cron
<span class="highlight">lrwxrwxrwx 1 root root  15 Jul 31 07:09 S02mysql -> ../init.d/mysql</span>
lrwxrwxrwx 1 root root  13 Jul 23  2012 S02ssh -> ../init.d/ssh
. . .
</code></pre>
<p>We can see there are no K scripts here, only S (start) scripts. The scripts start known services like <em>rsyslog</em>, <em>cron</em>, or <em>ssh</em>.</p>

<p>Remember that the two digits after S decide the order of starting: for example, rsyslog starts before the cron daemon. We can also see that MySQL is listed here.</p>

<h3 id="step-4-—-looking-at-an-init-script">Step 4 — Looking at an Init Script</h3>

<p>We now know that when a System V-compliant service is installed, it creates a shell script under the <code>/etc/init.d</code> directory. Check the shell script for MySQL:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">ls -l /etc/init.d/my*
</li></ul></code></pre><pre class="code-pre "><code langs=""><div class="secondary-code-label " title="Output">Output</div>-rwxr-xr-x 1 root root 5437 Jan 14  2014 <span class="highlight">/etc/init.d/mysql</span>
</code></pre>
<p>To see what the start-up script actually looks like, read the file:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">cat /etc/init.d/mysql | less
</li></ul></code></pre>
<p>From the output, you will see it's a large bash script.</p>

<h3 id="step-5-—-using-chkconfig-or-sysv-rc-conf">Step 5 — Using chkconfig or sysv-rc-conf</h3>

<p>In RHEL-based distributions like CentOS, a command called <code>chkconfig</code> can be used to enable or disable a service in System V. It can also list installed services and their runlevels.</p>

<span class="note"><p>
The syntax for checking the status of a service for all runlevels on a CentOS system would be:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">chkonfig --list | grep <span class="highlight">service_name</span>
</li></ul></code></pre>
<p></p></span>

<p>No such utility ships with Debian natively (<code>update-rc.d</code> installs or removes services from runlevels only). We can, however, install a custom tool called <code>sysv-rc-conf</code> to help us manage services.</p>

<p>Run the following command to install <code>sysv-rc-conf</code>:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo apt-get install sysv-rc-conf -y
</li></ul></code></pre>
<p>Once the tool has been installed, simply execute this command to see the runlevel behavior for various services:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo sysv-rc-conf
</li></ul></code></pre>
<p>The output will be a pretty graphical window as shown below. From here, we can clearly see what services are enabled for what runlevels (marked by X).</p>

<p><img src="https://assets.digitalocean.com/articles/auto-starting-2/drQB0Hx.jpg" alt="sysv-rc-conf Window showing X marks for various services for each runlevel" /></p>

<p>Using the arrow keys and <code>SPACEBAR</code>, we can enable or disable a service for one or more runlevels.</p>

<p>For now, leave the screen by pressing <code>Q</code>.</p>

<h3 id="step-7-—-testing-mysql-startup-behavior-at-boot">Step 7 — Testing MySQL Startup Behavior at Boot</h3>

<p>As you can see from the screenshot in the previous section, and from our testing in Part 1 of the tutorial, MySQL is currently enabled on runlevels 2-5.</p>

<p>Run the command below to <strong>disable</strong> the MySQL Service:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo update-rc.d mysql disable
</li></ul></code></pre><pre class="code-pre "><code langs=""><div class="secondary-code-label " title="Output">Output</div>update-rc.d: using dependency based boot sequencing
insserv: warning: current start runlevel(s) (empty) of script `mysql' overwrites defaults (2 3 4 5).
insserv: warning: current stop runlevel(s) (0 1 2 3 4 5 6) of script `mysql' overwrites defaults (0 1 6).
</code></pre>
<p>Now run the command:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">ls -l /etc/rc2.d
</li></ul></code></pre>
<p>The output should show that symlink from <code>/etc/rc2.d</code> to <code>/etc/init.d/mysql</code> has changed to <code>K</code>:</p>
<pre class="code-pre "><code langs=""><div class="secondary-code-label " title="Output">Output</div>. . .
lrwxrwxrwx 1 root root  15 Jul 31 07:09 <span class="highlight">K</span>02mysql -> ../init.d/mysql
. . .
</code></pre>
<p>In other words, MySQL will no longer start at default runlevel (2).</p>

<p>This is what happens behind the scenes in System V when we enable and disable a service. As long as there is an S script under the default runlevel directory for the service, init will start that service when booting.</p>

<p>Enable the service again:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo update-rc.d mysql enable
</li></ul></code></pre>
<h3 id="step-8-—-testing-mysql-start-up-behavior-on-crash">Step 8 — Testing MySQL Start-up Behavior on Crash</h3>

<p>Let's see how System V handles service crashes.</p>

<p>Remember that we made a change to the <code>/etc/inittab</code> file in Part 1 of this tutorial, to enable MySQL to start automatically after a crash. We added the following line:</p>
<div class="code-label " title="/etc/inittab">/etc/inittab</div><pre class="code-pre "><code langs="">ms:2345:respawn:/bin/sh /usr/bin/mysqld_safe
</code></pre>
<p>This was to ensure the MySQL service starts after a crash. To check if that happens, first reboot the server:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo reboot
</li></ul></code></pre>
<p>Once the server comes back, SSH in to it and check the MySQL process IDs like before:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">ps -ef | grep mysql
</li></ul></code></pre>
<p>Note the process IDs for <code>mysqld_safe</code> and <code>mysqld</code>. In our case, these were 895 and 1019 respectively:</p>
<pre class="code-pre "><code langs=""><div class="secondary-code-label " title="Output">Output</div>root       <span class="highlight">907</span>     1  0 07:30 ?        00:00:00 /bin/sh /usr/bin/mysqld_safe
mysql     <span class="highlight">1031</span>   907  0 07:30 ?        00:00:00 /usr/sbin/mysqld --basedir=/usr --datadir=/var/lib/mysql --user=mysql --pid-file=/var/run/mysqld/mysqld.pid --socket=/var/run/mysqld/mysqld.sock --port=3306
root      1032   907  0 07:30 ?        00:00:00 logger -t mysqld -p daemon.error
root      2550  2532  0 07:31 pts/0    00:00:00 grep mysql
</code></pre>
<p>Kill the processes again with a <code>-9</code> switch (substitute the PIDs with those of your Debian system):</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo kill -9 <span class="highlight">907</span>
</li><li class="line" prefix="$">sudo kill -9 <span class="highlight">1031</span>
</li></ul></code></pre>
<p><!-- mark variables in red --></p>

<p>Wait for five minutes or so and then execute the command:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo service mysql status
</li></ul></code></pre>
<p>The output will show MySQL service is running, starting with this line:</p>
<pre class="code-pre "><code langs=""><div class="secondary-code-label " title="Output">Output</div>/usr/bin/mysqladmin  Ver 8.42 Distrib 5.1.73, for debian-linux-gnu on x86_64
</code></pre>
<p>If you run the <code>ps -ef | grep mysql</code> command again, you will see that both the <code>mysqld_safe</code> and <code>mysqld</code> processes have come up.</p>

<p>Try to kill the process a few more times, and in each case it should respawn after five minutes.</p>

<p>This is the reason we added that extra line in <code>/etc/inittab</code>: this is how you configure a System V service to respawn in a crash. There is a detailed explanation of the syntax for this line in <a href="https://indiareads/community/tutorials/how-to-configure-a-linux-service-to-start-automatically-after-a-crash-or-reboot-part-1-practical-examples#step-4-%E2%80%94-configuring-mysql-to-auto-start-after-crash">Part 1</a>.</p>

<p>However, be careful when you add an automatic restart for a service: if a service tries to respawn and fails more than ten times within two minutes, Linux will disable the respawn for the next five minutes. This is so the system remains stable and does not run out of computing resources.</p>

<p>If you happen to receive a message in the console about such event or find it in system logs, you will know there's a problem with the application that needs to be fixed, since it keeps crashing. </p>

<h2 id="upstart-introduction">Upstart Introduction</h2>

<p>Classic SysVinit had been part of mainstream Linux distributions for a long time before Upstart came along.</p>

<p>As the Linux market grew, serialized ways of loading jobs and services became more time consuming and complex. At the same time, as more and more modern devices like hot-pluggable storage media proliferated the market, SysVinit was found to be incapable of handling them quickly.</p>

<p>The need for faster loading of the OS, graceful clean-up of crashed services, and predictable dependency between system services drove the need for a better service manager. The developers at Ubuntu came up with another means of initialization, the Upstart daemon. </p>

<p>Upstart init is better than System V init in a few ways:</p>

<ul>
<li>Upstart does not deal with arcane shell scripts to load and manage services. Instead, it uses simple configuration files that are easy to understand and modify</li>
<li>Upstart does not load services serially like System V. This cuts down on system boot time</li>
<li>Upstart's uses a flexible <em>event</em> system to customize how services are handled in various states </li>
<li>Upstart has better ways of handling how a crashed service should respawn</li>
<li>There is no need to keep a number of redundant symbolic links, all pointing to the same script</li>
<li>Upstart is backwards-compatible with System V. The <code>/etc/init.d/rc</code> script still runs to manage native System V services</li>
</ul>

<h2 id="upstart-events">Upstart Events</h2>

<p>Upstart allows for multiple <em>events</em> to be associated with a service. This event-based architecture allows Upstart to treat service management flexibly.</p>

<p>Each event can fire off a shell script that takes care of that event.</p>

<p>Upstart events include:</p>

<ul>
<li>Starting</li>
<li>Started</li>
<li>Stopping</li>
<li>Stopped</li>
</ul>

<p>In between these events, a service can be in a number of <em>states</em>, like:</p>

<ul>
<li>waiting</li>
<li>pre-start</li>
<li>starting</li>
<li>running</li>
<li>pre-stop</li>
<li>stopping</li>
<li>etc.</li>
</ul>

<p>Upstart can take actions for each of these states as well, creating a very flexible architecture.</p>

<h2 id="upstart-init-sequence">Upstart Init Sequence</h2>

<p>Like System V, Upstart also runs the <code>/etc/init.d/rc</code> script at startup. This script executes any System V init scripts normally.</p>

<p>Upstart also looks under the <code>/etc/init</code> directory and executes the shell commands in each service config file.</p>

<h2 id="upstart-configuration-files">Upstart Configuration Files</h2>

<p>Upstart uses configuration files to control services.</p>

<p>Upstart does not use Bash scripts the way System V does. Instead, Upstart uses <em>service configuration</em> files with a naming standard of <code><span class="highlight">service_name</span>.conf</code>.</p>

<p>The files have plain text content with different sections, called <em>stanzas</em>. Each stanza describes a different aspect of the service and how it should behave.</p>

<p>Different stanzas control different events for the service, like <em>pre-start</em>, <em>start</em>, <em>pre-stop</em> or <em>post-stop</em>.</p>

<p>The stanzas themselves contain shell commands. Therefore, it's possible to call multiple actions for each event for each service. </p>

<p>Each configuration file also specifies two things:</p>

<ul>
<li>Which runlevels the service should start and stop on</li>
<li>Whether the service should <em>respawn</em> if it crashes</li>
</ul>

<h3 id="directory-structure">Directory Structure</h3>

<p>The Upstart configuration files are located under the <code>/etc/init</code> directory (not to be confused with <code>/etc/init.d</code>).</p>

<h2 id="upstart-example">Upstart Example</h2>

<p>Let's take a look at how Upstart handles MySQL Server again, this time with more background knowledge.</p>

<h3 id="step-1-—-logging-in-to-ubuntu-droplet">Step 1 — Logging in to Ubuntu Droplet</h3>

<p>Go back to the Ubuntu 14.04 Droplet we created in Part 1.</p>

<p>Use the SSH command to connect to the server (Windows users can connect using a tool like PuTTy).</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">ssh sammy@<span class="highlight">your_server_ip</span>
</li></ul></code></pre>
<h3 id="step-2-—-looking-at-the-init-and-rc-directories">Step 2 — Looking at the init and rc Directories</h3>

<p>Most of Upstart's config files are in the <code>/etc/init</code> directory. This is the directory you should use when creating new services.</p>

<p>Once logged into the server, execute the following command:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo ls -l /etc/init/ | less
</li></ul></code></pre>
<p>The result will show a large number of service configuration files, one screen at a time. These are services that run natively under Upstart:</p>
<pre class="code-pre "><code langs=""><div class="secondary-code-label " title="Output">Output</div>total 356
. . .
-rw-r--r-- 1 root root  297 Feb  9  2013 cron.conf
-rw-r--r-- 1 root root  489 Nov 11  2013 dbus.conf
-rw-r--r-- 1 root root  273 Nov 19  2010 dmesg.conf
. . .
<span class="highlight">-rw-r--r-- 1 root root 1770 Feb 19  2014 mysql.conf</span>
-rw-r--r-- 1 root root 2493 Mar 20  2014 networking.conf
</code></pre>
<p>Press <code>Q</code> to exit <code>less</code>.</p>

<p>Compare this with the native System V init services in the system:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo ls -l /etc/rc3.d/* | less
</li></ul></code></pre>
<p>There will be only a handful:</p>
<pre class="code-pre "><code langs=""><div class="secondary-code-label " title="Output">Output</div>-rw-r--r-- 1 root root 677 Jun 14 23:31 /etc/rc3.d/README
lrwxrwxrwx 1 root root  15 Apr 17  2014 /etc/rc3.d/S20rsync -> ../init.d/rsync
lrwxrwxrwx 1 root root  24 Apr 17  2014 /etc/rc3.d/S20screen-cleanup -> ../init.d/screen-cleanup
lrwxrwxrwx 1 root root  19 Apr 17  2014 /etc/rc3.d/S70dns-clean -> ../init.d/dns-clean
lrwxrwxrwx 1 root root  18 Apr 17  2014 /etc/rc3.d/S70pppd-dns -> ../init.d/pppd-dns
lrwxrwxrwx 1 root root  26 Apr 17  2014 /etc/rc3.d/S99digitalocean -> ../init.d//rc.digitalocean
lrwxrwxrwx 1 root root  21 Apr 17  2014 /etc/rc3.d/S99grub-common -> ../init.d/grub-common
lrwxrwxrwx 1 root root  18 Apr 17  2014 /etc/rc3.d/S99ondemand -> ../init.d/ondemand
lrwxrwxrwx 1 root root  18 Apr 17  2014 /etc/rc3.d/S99rc.local -> ../init.d/rc.local
</code></pre>
<h3 id="step-3-—-looking-at-an-upstart-file">Step 3 — Looking at an Upstart File</h3>

<p>We've already seen the <code>mysql.conf</code> file in Part 1 of this tutorial. So, let's open another config file: the one for the cron daemon.</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo nano /etc/init/cron.conf
</li></ul></code></pre>
<p>As you can see, this is a fairly simple config file for the cron daemon:</p>
<div class="code-label " title="/etc/init/cron.conf">/etc/init/cron.conf</div><pre class="code-pre "><code langs=""># cron - regular background program processing daemon
#
# cron is a standard UNIX program that runs user-specified programs at
# periodic scheduled times

description     "regular background program processing daemon"

start on runlevel [2345]
stop on runlevel [!2345]

expect fork
respawn

exec cron
</code></pre>
<p>The important fields to be mindful of here are <code>start on</code>, <code>stop on</code> and <code>respawn</code>.</p>

<p>The <code>start on</code> directive tells Ubuntu to start the <code>crond</code> daemon when the system enters runlevels 2, 3, 4 or 5. 2, 3, and 4 are multi-user text modes with networking enabled, and 5 is multi-user graphical mode. The service does not run on any other runlevels (like 0,1 or 6).</p>

<p>The <code>fork</code> directive tells Upstart the process should detach from the console and run in the background.</p>

<p>Next comes the <code>respawn</code> directive. This tells the system that cron should start automatically if it crashes for any reason.</p>

<p>Exit the editor without making any changes. </p>

<p>The cron config file is a fairly small configuration file. The MySQL configuration file is structurally similar to the cron configuration file; it also has stanzas for start, stop, and respawn. In addition, it also has two script blocks for pre-start and post-start events. These code blocks tell the system what to execute when the mysqld process is either coming up or has already come up.</p>

<p>For practical help on making your own Upstart file, see <a href="https://indiareads/community/tutorials/the-upstart-event-system-what-it-is-and-how-to-use-it">this tutorial about Upstart</a>.</p>

<h3 id="step-4-—-testing-mysql-startup-behavior-at-boot">Step 4 — Testing MySQL Startup Behavior at Boot</h3>

<p>We know the MySQL instance on our Ubuntu 14.04 server is set to auto-start at boot time by default. Let's see how we can disable it. </p>

<p>In Upstart, disabling a service depends on the existence of a file under <code>/etc/init/</code> called <code><span class="highlight">service_name</span>.override</code>. The content of the file should be a simple word: <code>manual</code>.</p>

<p>To see how we can use this file to disable MySQL, execute the following command to create this override file for MySQL:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo nano /etc/init/mysql.override
</li></ul></code></pre>
<p>Add this single line: </p>
<div class="code-label " title="/etc/init/mysql.override">/etc/init/mysql.override</div><pre class="code-pre "><code langs="">manual
</code></pre>
<p>Save your changes.</p>

<p>Next, reboot the server:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo reboot
</li></ul></code></pre>
<p>Once the server comes back online, check the staus of the service</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo initctl status mysql
</li></ul></code></pre>
<p>The output should be:</p>
<pre class="code-pre "><code langs=""><div class="secondary-code-label " title="Output">Output</div>mysql stop/waiting
</code></pre>
<p>This means MySQL didn't start up.</p>

<p>Check if the <code>start</code> directive has changed in the MySQL service configuration file:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo cat /etc/init/mysql.conf | grep start\ on
</li></ul></code></pre>
<p>It should still be the same:</p>
<pre class="code-pre "><code langs=""><div class="secondary-code-label " title="Output">Output</div>start on runlevel [2345]
</code></pre>
<p><span class="note">This means that checking the <code>.conf</code> file in the <code>init</code> directory is not the sole factor to see if the service will start at the appropriate levels. You also need to make sure the <code>.override</code> file doesn't exist.</span></p>

<p>To reenable MySQL, delete the override file and reboot the server:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo rm -f /etc/init/mysql.override
</li><li class="line" prefix="$">sudo reboot
</li></ul></code></pre>
<p>Once the server reboots, remotely connect to it.</p>

<p>Running the <code>sudo initctl status mysql</code> command will show the service has started automatically.</p>

<h3 id="step-5-—-testing-mysql-startup-behavior-on-crash">Step 5 — Testing MySQL Startup Behavior on Crash</h3>

<p>By default, MySQL comes up automaticaly after a crash.</p>

<p>To stop MySQL this, open the <code>/etc/init/mysql.conf</code> service configuration file:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo nano /etc/init/mysql.conf
</li></ul></code></pre>
<p>Comment out both the <code>respawn</code> directives.</p>
<div class="code-label " title="/etc/init/mysql.conf">/etc/init/mysql.conf</div><pre class="code-pre "><code langs=""># respawn
# respawn limit 2 5
</code></pre>
<p>Run the following commands to restart the service:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo initctl stop mysql
</li><li class="line" prefix="$">sudo initctl start mysql
</li></ul></code></pre>
<p>We are explicitly stopping and starting the service because our test showed <code>initctl restart</code> or <code>initctl reload</code> would not work here.</p>

<p>The second command to start the service shows the PID MySQL started with:</p>
<pre class="code-pre "><code langs=""><div class="secondary-code-label " title="Output">Output</div>mysql start/running, process <span class="highlight">1274</span>
</code></pre>
<p>Note the PID for your instance of MySQL. If you crash the <code>mysql</code> process now, it won't be coming up automatically.  Kill the process ID (replacing it with your own number):</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo kill -9 <span class="highlight">1274</span>
</li></ul></code></pre>
<p>Now check its status:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo initctl status mysql
</li></ul></code></pre><pre class="code-pre "><code langs=""><div class="secondary-code-label " title="Output">Output</div>mysql stop/waiting
</code></pre>
<p>Try to find the status a few more times, giving some time between each. In every case, MySQL will still be stopped. This is happening because the service configuration file does not have the <code>respawn</code> directives anymore.</p>

<p><a href="https://indiareads/community/tutorials/how-to-configure-a-linux-service-to-start-automatically-after-a-crash-or-reboot-part-1-practical-examples">Part 1</a> of the tutorial has a more detailed explanation of the <code>respawn</code> directives.</p>

<span class="note"><p>
When would you <em>not</em> want an Upstart service to come up after a reboot or crash?</p>

<p>Say you have upgraded your Linux kernel or put the latest patch in. You don't want any drama; you just the server to come up. You can largely eliminate risks by disabling auto-start for any Upstart process.</p>

<p>If your service comes up but keeps crashing, you can first stop it and then change its respawn behavior as well.</p></span>

<h2 id="systemd-introduction">systemd Introduction</h2>

<p>The latest in Linux init daemons is systemd. In fact it's more than an init daemon: systemd is a whole new framework that encompasses many components of a modern Linux system.</p>

<p>One of its functions is to work as a <em>system and service manager</em> for Linux. In this capacity, one of the things systemd controls is how a service should behave if it crashes or the machine reboots. You can read about <a href="https://indiareads/community/tutorials/how-to-use-systemctl-to-manage-systemd-services-and-units">systemd's systemctl here</a>.</p>

<p>systemd backward-compatible with System V commands and initialization scripts. That means any System V service will also run under systemd. This is possible because most Upstart and System V administrative commands have been modified to work under systemd.</p>

<p>In fact, if we run the <code>ps -ef | grep systemd</code> command in an operating system that supports it, we won't see anything, because <code>systemd</code> renames itself to <code>init</code> at boot time. There is an <code>/sbin/init</code> file that's a symbolic link to <code>/bin/systemd</code>.</p>

<h2 id="systemd-configuration-files-unit-files">systemd Configuration Files: Unit Files</h2>

<p>At the heart of systemd are <em>unit files</em>. Each unit file represents a system resource. The main difference between systemd and the other two init methods is that systemd is responsible for initialization of not only service daemons but also other types of resources like sockets, device operating system paths, mount points, sockets, etc. A resource can be any of these.</p>

<p>Information about the resource is kept track of in the unit file.</p>

<p>Each unit file represents a specific system resource and has a naming style of <code><span class="highlight">service name</span>.<span class="highlight">unit type</span></code>.</p>

<p>So, we will have files like <code>dbus.service</code>, <code>sshd.socket</code>, or <code>home.mount</code>.</p>

<p>As we will see later, service unit files are simple text files (like Upstart <code>.conf</code> files) with a declarative syntax. These files are pretty easy to understand and modify.</p>

<h3 id="directory-structure">Directory Structure</h3>

<p>In Red Hat-based systems like CentOS, unit files are located in two places. The main location is <code>/lib/systemd/system/</code>.</p>

<p>Custom-created unit files or existing unit files modified by system administrators will live under <code>/etc/systemd/system</code>.</p>

<p>If a unit file with the same name exists in both locations, systemd will use the one under <code>/etc</code>. If a service is enabled to start at boot time or any other target/runlevel, a symbolic link will be created for that service unit file under appropriate directories in <code>/etc/systemd/system</code>. Unit files under <code>/etc/systemd/system</code> are actually symbolic links to the files with same name under <code>/lib/systemd/system</code>.</p>

<h2 id="systemd-init-sequence-target-units">systemd Init Sequence: Target Units</h2>

<p>A special type of unit file is a <em>target unit</em>.</p>

<p>A target unit filename is suffixed by <code>.target</code>. Target units are different from other unit files because they don't represent one particular resource. Rather, they represent the state of the system at any one time.</p>

<p>Target units do this by grouping and launching multiple unit files that should be part of that state. systemd <em>targets</em> can therefore be loosely compared to System V runlevels, although they are not the same.</p>

<p>Each target has a name instead of a number. For example, we have <code>multi-user.target</code> instead of runlevel 3 or <code>reboot.target</code> instead of runlevel 6.</p>

<p>When a Linux server boots with say, <code>multi-user.target</code>, it's essentially bringing the server to runlevel 2, 3, or 4, which is the multi-user text mode with networking enabled.</p>

<p>How it brings the server up to that stage is where the difference lies. Unlike System V, systemd does not bring up services sequentially. Along the way, it can check for the existence of other services or resources and decide the order of their loading. This makes it possible for services to load in parallel.</p>

<p>Another difference between target units and runlevels is that in System V, a Linux system could exist in only one runlevel. You could change the runlevel, but the system would exist in that new runlevel only. With systemd, target units can be <em>inclusive</em>, which means when a target unit activates, it can ensure other target units are loaded as part of it.</p>

<p>For example, a Linux system that boots with a graphical user interface will have the <code>graphical.target</code> activated, which in turn will automatically ensure <code>multi-user.target</code> is loaded and activated as well.</p>

<p>(In System V terms, that would be like having runlevels 3 and 5 activated at the same time.)</p>

<p>The table below compares runlevels and targets:</p>

<table class="pure-table"><thead>
<tr>
<th>Runlevel (System V init)</th>
<th>Target Units (Systemd)</th>
</tr>
</thead><tbody>
<tr>
<td>runlevel 0</td>
<td>poweroff.target</td>
</tr>
<tr>
<td>runlevel 1</td>
<td>resuce.target</td>
</tr>
<tr>
<td>runlevel 2, 3, 4</td>
<td>multi-user.target</td>
</tr>
<tr>
<td>runlevel 5</td>
<td>graphical.target</td>
</tr>
<tr>
<td>runlevel 6</td>
<td>reboot.target</td>
</tr>
</tbody></table>

<h2 id="systemd-default-target">systemd default.target</h2>

<p><code>default.target</code> is equivalent to the default runlevel.</p>

<p>In System V, we had the default runlevel defined in a file called <code>inittab</code>. In systemd, that file is replaced by <code>default.target</code>. The default target unit file lives under <code>/etc/systemd/system</code> directory. It's a symbolic link to one of the target unit files under <code>/lib/systemd/system</code>.</p>

<p>When we change the default target, we are essentially recreating that symbolic link and changing the system's runlevel.</p>

<p>The inittab file in System V also specified which directory Linux will execute its init scripts from: it could be any of the rc<em>n</em>.d directories. In systemd, the default target unit determines which resource units will be loaded at boot time.</p>

<p>All those units are activated, but not all in parallel or all in sequence. How a resource unit loads may depend on other resource units it <em>wants</em> or <em>requires</em>.</p>

<h2 id="systemd-dependencies-wants-and-requires">systemd Dependencies: Wants and Requires</h2>

<p>The reason for this discussion on unit files and target units is to highlight how systemd addresses dependency among its daemons.</p>

<p>As we saw before, Upstart ensures parallel loading of services using configuration files. In System V, a service could start in particular runlevels, but it also could be made to wait until another service or resource became available. In similar fashion, systemd services can be made to load in one or more targets, or wait until another service or resource became active.</p>

<p>In systemd, a unit that <em>requires</em> another unit will not start until the required unit is loaded and activated. If the required unit fails for some reason while the first unit is active, the first unit will also stop.</p>

<p>If you think about it, this ensures system stability. A service that requires a particular directory to be present can thus be made to wait until the mount point to that directory is active. On other hand, a unit that <em>wants</em> another unit will not impose such restrictions. It won't stop if the wanted unit stops when the caller is acive. An example of this would be the non-essential services that come up in graphical-target mode.</p>

<h2 id="systemd-example">systemd Example</h2>

<p>It's time for our deep dive into MySQL's startup behavior under systemd.</p>

<h3 id="step-1-—-log-in-to-centos-droplet">Step 1 — Log in to CentOS Droplet</h3>

<p>To understand all these concepts and how they relate to enabling a service to auto-start, let's go back to the CentOS 7 Droplet that we created in Part 1.</p>

<p>Use the SSH command to connect to the server (Windows users can connect using a tool like PuTTy).</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">ssh sammy@<span class="highlight">your_server_ip</span>
</li></ul></code></pre>
<h3 id="step-2-—-looking-at-the-default-target-file-and-dependencies">Step 2 — Looking at the default.target File and Dependencies</h3>

<p>This is a long section, because we're going to follow the <code>.target</code> rabbit-trail as far as we can. systemd's startup sequence follows a long chain of dependencies.</p>

<p><strong>defaul.target</strong></p>

<p>The <code>default.target</code> file controls which services start during a normal server boot.</p>

<p>Execute the following command to list the default target unit file:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo ls -l /etc/systemd/system/default.target
</li></ul></code></pre>
<p>This shows output like the following:</p>
<pre class="code-pre "><code langs=""><div class="secondary-code-label " title="Output">Output</div>lrwxrwxrwx. 1 root root 37 Jul  8  2014 /etc/systemd/system/default.target -> /lib/systemd/system/multi-user.target
</code></pre>
<p>As we can see, the default target is actually a symbolic link to the multi-user target file under <code>/lib/systemd/system/</code>. So, the system is supposed to boot under <code>multi-user.target</code>, which is similar to runlevel 3.</p>

<p><strong>multi-user.target.wants</strong></p>

<p>Next, execute the following command to check all the services the <code>multi-user.target</code> file <em>wants</em>:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo ls -l /etc/systemd/system/multi-user.target.wants/*.service
</li></ul></code></pre>
<p>This should show an output like this:</p>
<pre class="code-pre "><code langs=""><div class="secondary-code-label " title="Output">Output</div>. . .
lrwxrwxrwx. 1 root root  37 Jul  8  2014 /etc/systemd/system/multi-user.target.wants/crond.service -> /usr/lib/systemd/system/crond.service
. . .
lrwxrwxrwx  1 root root  38 Jul 31 22:02 /etc/systemd/system/multi-user.target.wants/mysqld.service -> /usr/lib/systemd/system/<span class="highlight">mysqld.service</span>
lrwxrwxrwx. 1 root root  46 Jul  8  2014 /etc/systemd/system/multi-user.target.wants/NetworkManager.service -> /usr/lib/systemd/system/NetworkManager.service
lrwxrwxrwx. 1 root root  39 Jul  8  2014 /etc/systemd/system/multi-user.target.wants/postfix.service -> /usr/lib/systemd/system/postfix.service
lrwxrwxrwx. 1 root root  39 Jul  8  2014 /etc/systemd/system/multi-user.target.wants/rsyslog.service -> /usr/lib/systemd/system/rsyslog.service
lrwxrwxrwx. 1 root root  36 Jul  8  2014 /etc/systemd/system/multi-user.target.wants/sshd.service -> /usr/lib/systemd/system/sshd.service
. . .
</code></pre>
<p>We can see these are all symbolic link files, pointing back to actual unit files under <code>/lib/systemd/system/</code>. We can also see that <code>mysqld.service</code> is part of <code>multi-user.target</code>.</p>

<p>The same information can be found if you execute this command to filter the output:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo systemctl show --property "Wants" multi-user.target | fmt -10 | grep mysql
</li></ul></code></pre><pre class="code-pre "><code langs=""><div class="secondary-code-label " title="Output">Output</div>mysqld.service
</code></pre>
<p>Other than <code>multi-user.target</code>, there are different types of targets like <code>system-update.target</code> or <code>basic.target</code>.</p>

<p>To see what targets our multi-user target depends on, execute the following command:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo systemctl show --property "Requires" multi-user.target | fmt -10
</li></ul></code></pre><pre class="code-pre "><code langs=""><div class="secondary-code-label " title="Output">Output</div>Requires=basic.target
</code></pre>
<p>So to start the system in <code>multi-user.target</code> mode, <code>basic.target</code> will have to load first.</p>

<p><strong>basic.target</strong></p>

<p>To see what other targets <code>basic.target</code> depends on, execute this command:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo systemctl show --property "Requires" basic.target | fmt -10
</li></ul></code></pre>
<p>The output will be:</p>
<pre class="code-pre "><code langs=""><div class="secondary-code-label " title="Output">Output</div>Requires=sysinit.target
</code></pre>
<p><strong>sysinit.target</strong></p>

<p>Going recursively, we can see if there are any required units for <code>sysinit.target</code>. There are none. However, we can see what services are <em>wanted</em> by <code>sysinit.target</code>:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo systemctl show --property "Wants" sysinit.target | fmt -10
</li></ul></code></pre>
<p>This will show a number of services wanted by sysinit.</p>
<pre class="code-pre "><code langs=""><div class="secondary-code-label " title="Output">Output</div>Wants=local-fs.target
swap.target
cryptsetup.target
systemd-udevd.service
systemd-update-utmp.service
systemd-journal-flush.service
plymouth-read-write.service
. . .
</code></pre>
<p>As you can see, the system does not stay in one target only. It loads services in a dependent fashion as it transitions between targets.</p>

<h3 id="step-3-—-looking-at-a-unit-file">Step 3 — Looking at a Unit File</h3>

<p>Going a step further now, let's look inside a service unit file. We saw the MySQL service unit file in Part 1 of this tutorial, and we will use it again shortly, but for now let's open another service unit file, the one for sshd:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo nano /etc/systemd/system/multi-user.target.wants/sshd.service
</li></ul></code></pre>
<p>It looks like this:</p>
<pre class="code-pre "><code langs=""><div class="secondary-code-label " title="Output">Output</div>[Unit]
Description=OpenSSH server daemon
After=syslog.target network.target auditd.service

[Service]
EnvironmentFile=/etc/sysconfig/sshd
ExecStartPre=/usr/sbin/sshd-keygen
ExecStart=/usr/sbin/sshd -D $OPTIONS
ExecReload=/bin/kill -HUP $MAINPID
KillMode=process
Restart=on-failure
RestartSec=42s

[Install]
WantedBy=multi-user.target
</code></pre>
<p>Just like an Upstart daemon config file, this service unit file is clean and easy to understand.</p>

<p>The first important bit to understand is the <code>After</code> clause. This says the SSHD service needs to load after the system and network targets and the audit logging service are loaded.</p>

<p>The file also shows the service is <em>wanted</em> by the <code>multi-user.target</code>, which means the target will load this service, but it won't shut down or crash if sshd fails.</p>

<p>Since <code>multi-user.target</code> is the default target, sshd daemon is supposed to start at boot time.</p>

<p>Exit the editor.</p>

<h3 id="step-4-—-testing-mysql-startup-behavior-at-boot">Step 4 — Testing MySQL Startup Behavior at Boot</h3>

<p>In Part 1 of the tutorial, we left the MySQL service enabled and running. Let's see how to change that.</p>

<p>In the last section, we ran a command to confirm that <code>mysqld.service</code> is wanted by <code>multi-user.target</code>. When we listed the contents of the <code>/etc/systemd/system/multi-user.target.wants/</code> directory, we saw a symbolic link pointing back to the original service unit under <code>/usr/lib/systemd/system/</code>.</p>

<p>Run the following command to disable the service so it does not auto-start at boot time:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo systemctl disable mysqld.service
</li></ul></code></pre>
<p>Now, run this command to check if MySQL is still wanted by <code>multi-user.target</code>:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo systemctl show --property "Wants" multi-user.target | fmt -10 | grep mysql
</li></ul></code></pre>
<p>Nothing will be returned. Run the command below to check if the symbolic link still exists:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo ls -l /etc/systemd/system/multi-user.target.wants/mysql*
</li></ul></code></pre>
<p>The link doesn't exist:</p>
<pre class="code-pre "><code langs=""><div class="secondary-code-label " title="Output">Output</div>ls: cannot access /etc/systemd/system/multi-user.target.wants/mysql*: No such file or directory
</code></pre>
<p>If you'd like, try rebooting the server. MySQL should not come up.</p>

<p>Now reenable the service:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo systemctl enable mysqld.service
</li></ul></code></pre>
<p>The link will come back:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo ls -l /etc/systemd/system/multi-user.target.wants/mysql*
</li></ul></code></pre><pre class="code-pre "><code langs=""><div class="secondary-code-label " title="Output">Output</div>lrwxrwxrwx 1 root root 38 Aug  1 04:43 /etc/systemd/system/multi-user.target.wants/mysqld.service -> /usr/lib/systemd/system/mysqld.service
</code></pre>
<p>(If you rebooted before, you should start MySQL again.)</p>

<p>As you can see, enabling or disabling a systemd service creates or removes the symbolic link from the default target's <code>wants</code> directory. </p>

<h3 id="step-5-—-testing-mysql-startup-behavior-on-crash">Step 5 — Testing MySQL Startup Behavior on Crash</h3>

<p>MySQL will currently come up automatically after a crash. Let's see how to disable that.</p>

<p>Open the MySQL service unit file in an editor:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo nano /etc/systemd/system/multi-user.target.wants/mysqld.service
</li></ul></code></pre>
<p>After the header information, the contents of the file looks like this:</p>
<div class="code-label " title="/etc/systemd/system/multi-user.target.wants/mysqld.service">/etc/systemd/system/multi-user.target.wants/mysqld.service</div><pre class="code-pre "><code langs="">[Unit]
Description=MySQL Community Server
After=network.target
After=syslog.target

[Install]
WantedBy=multi-user.target
Alias=mysql.service

[Service]
User=mysql
Group=mysql

# Execute pre and post scripts as root
PermissionsStartOnly=true

# Needed to create system tables etc.
ExecStartPre=/usr/bin/mysql-systemd-start pre

# Start main service
ExecStart=/usr/bin/mysqld_safe

# Don't signal startup success before a ping works
ExecStartPost=/usr/bin/mysql-systemd-start post

# Give up if ping don't get an answer
TimeoutSec=600

<span class="highlight">Restart=always</span>
PrivateTmp=false
</code></pre>
<p>As we saw in Part 1, the value of the <code>Restart</code> parameter is set to <code>always</code> (for sshd, this was set to <code>on-failure</code> only). This means the MySQL service will restart for clean or unclean exit codes or timeouts.</p>

<p>The <a href="http://www.freedesktop.org/software/systemd/man/systemd.service.html">man page for systemd service</a> shows the following table for Restart parameters:</p>

<table class="pure-table"><thead>
<tr>
<th>Restart settings/Exit causes</th>
<th>no</th>
<th>always</th>
<th>on-success</th>
<th>on-failure</th>
<th>on-abnormal</th>
<th>on-abort</th>
<th>on-watchdog</th>
</tr>
</thead><tbody>
<tr>
<td>Clean exit code or signal</td>
<td></td>
<td>X</td>
<td>X</td>
<td></td>
<td></td>
<td></td>
<td></td>
</tr>
<tr>
<td>Unclean exit code</td>
<td></td>
<td>X</td>
<td></td>
<td>X</td>
<td></td>
<td></td>
<td></td>
</tr>
<tr>
<td>Unclean signal</td>
<td></td>
<td>X</td>
<td></td>
<td>X</td>
<td>X</td>
<td>X</td>
<td></td>
</tr>
<tr>
<td>Timeout</td>
<td></td>
<td>X</td>
<td></td>
<td>X</td>
<td>X</td>
<td></td>
<td></td>
</tr>
<tr>
<td>Watchdog</td>
<td></td>
<td>X</td>
<td></td>
<td>X</td>
<td>X</td>
<td></td>
<td>X</td>
</tr>
</tbody></table>

<p>In a systemd service unit file, the two parameters - <code>Restart</code> and <code>RestartSec</code> - control crash behaviour. The first parameter specifies when the service should restart, and the second parameter defines how long it should wait before restarting.</p>

<p>Comment out the Restart directive, save the file, and exit the editor. This will disable the restart behavior.</p>
<div class="code-label " title="/etc/systemd/system/multi-user.target.wants/mysqld.service">/etc/systemd/system/multi-user.target.wants/mysqld.service</div><pre class="code-pre "><code langs=""># Restart=always
</code></pre>
<p>Next, reload the systemd daemon, followed  by a restart of the <code>mysqld</code> service:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo systemctl daemon-reload
</li></ul></code></pre><pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo systemctl restart mysqld.service
</li></ul></code></pre>
<p>Next, find the Main PID of the service by running this command:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo systemctl status mysqld.service
</li></ul></code></pre><pre class="code-pre "><code langs=""><div class="secondary-code-label " title="Output">Output</div>. . .
Main PID: <span class="highlight">11217</span> (mysqld_safe)
</code></pre>
<p>Using the <code>kill -9</code> command, kill the main PID, using your own number. </p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo kill -9 <span class="highlight">11217</span>
</li></ul></code></pre>
<p>Running the <code>sudo systemctl status mysqld.service</code> again will show that the service has failed:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo systemctl status mysqld.service
</li></ul></code></pre><pre class="code-pre "><code langs=""><div class="secondary-code-label " title="Output">Output</div>mysqld.service - MySQL Community Server
   Loaded: loaded (/usr/lib/systemd/system/mysqld.service; enabled)
   Active: <span class="highlight">failed (Result: signal)</span> since Sun 2015-06-21 02:28:17 EDT; 1min 33s ago
  Process: 2566 ExecStartPost=/usr/bin/mysql-systemd-start post (code=exited, status=0/SUCCESS)
  Process: 2565 ExecStart=/usr/bin/mysqld_safe (code=killed, signal=KILL)
  Process: 2554 ExecStartPre=/usr/bin/mysql-systemd-start pre (code=exited, status=0/SUCCESS)
 Main PID: 2565 (code=killed, signal=KILL)

Jun 21 02:20:09 test-centos7 systemd[1]: Starting MySQL Community Server...
Jun 21 02:20:09 test-centos7 mysqld_safe[2565]: 150621 02:20:09 mysqld_safe Logging to '/var/log/mysqld.log'.
Jun 21 02:20:09 test-centos7 mysqld_safe[2565]: 150621 02:20:09 mysqld_safe Starting mysqld daemon with databases from /var/lib/mysql
Jun 21 02:20:10 test-centos7 systemd[1]: Started MySQL Community Server.
Jun 21 02:28:16 test-centos7 systemd[1]: <span class="highlight">mysqld.service: main process exited, code=killed, status=9/KILL</span>
Jun 21 02:28:17 test-centos7 systemd[1]: <span class="highlight">Unit mysqld.service entered failed state.</span>
</code></pre>
<p>Try to find the service status a few times, and each time the service will be shown as failed.</p>

<p>So, we have emulated a crash where the service has stopped and hasn't come back. This is because we have instructed systemd not to restart the service.</p>

<p>Now, if you edit the <code>mysqld.service</code> unit file again, uncomment the <code>Restart</code> parameter, save it, reload the systemctl daemon, and finally start the service, it should be back to what it was before.</p>

<p>This is how a native systemd service can be configured to auto-start after crash. All we have to do is to add an extra directive for <code>Restart</code> (and optionally <code>RestartSec</code>) under the <code>[Service]</code> section of the service unit file.</p>

<h2 id="conclusion">Conclusion</h2>

<p>So this is how Linux handles service startup. We have seen how System V, Upstart, and systemd init processes work and how they relate to auto-starting a service after a reboot or crash.</p>

<p>The declarative syntax of Upstart config files or systemd unit files is an improvement over the arcane System V init scripts.</p>

<p>As you work with your own Linux environment, check your distribution's version and see what init daemon it supports.</p>

<p>It will be worthwhile to think about where would you want to enable a service and where would you want to disable it.  In most cases, you don't have to change anything for third-party applications or native Linux daemons. It's only when you create your own service-based applications that you have to think about their startup and respawn behavior.</p>

    