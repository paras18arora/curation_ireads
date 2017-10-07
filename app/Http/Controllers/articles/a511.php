<link rel='stylesheet' href='digital_ocean.css'><br><img src=https://community-cdn-digitalocean-com.global.ssl.fastly.net/assets/tutorials/images/large/Systemctl_tw.png?1426699814/> <br> 
      <h3 id="introduction">Introduction</h3>

<p><code>Systemd</code> is an init system and system manager that is widely becoming the new standard for Linux machines.  While there are considerable opinions about whether <code>systemd</code> is an improvement over the traditional <code>SysV</code> init systems it is replacing, the majority of distributions plan to adopt it or have already done so.</p>

<p>Due to its heavy adoption, familiarizing yourself with <code>systemd</code> is well worth the trouble, as it will make administrating these servers considerably easier.  Learning about and utilizing the tools and daemons that comprise <code>systemd</code> will help you better appreciate the power, flexibility, and capabilities it provides, or at least help you to do your job with minimal hassle.</p>

<p>In this guide, we will be discussing the <code>systemctl</code> command, which is the central management tool for controlling the init system.  We will cover how to manage services, check statuses, change system states, and work with the configuration files.</p>

<h2 id="service-management">Service Management</h2>

<p>The fundamental purpose of an init system is to initialize the components that must be started after the Linux kernel is booted (traditionally known as "userland" components).  The init system is also used to manage services and daemons for the server at any point while the system is running.  With that in mind, we will start with some simple service management operations.</p>

<p>In <code>systemd</code>, the target of most actions are "units", which are resources that <code>systemd</code> knows how to manage.  Units are categorized by the type of resource they represent and they are defined with files known as unit files.  The type of each unit can be inferred from the suffix on the end of the file.</p>

<p>For service management tasks, the target unit will be service units, which have unit files with a suffix of <code>.service</code>.  However, for most service management commands, you can actually leave off the <code>.service</code> suffix, as <code>systemd</code> is smart enough to know that you probably want to operate on a service when using service management commands.</p>

<h3 id="starting-and-stopping-services">Starting and Stopping Services</h3>

<p>To start a <code>systemd</code> service, executing instructions in the service's unit file, use the <code>start</code> command.  If you are running as a non-root user, you will have to use <code>sudo</code> since this will affect the state of the operating system:</p>
<pre class="code-pre "><code langs="">sudo systemctl start <span class="highlight">application</span>.service
</code></pre>
<p>As we mentioned above, <code>systemd</code> knows to look for <code>*.service</code> files for service management commands, so the command could just as easily be typed like this:</p>
<pre class="code-pre "><code langs="">sudo systemctl start <span class="highlight">application</span>
</code></pre>
<p>Although you may use the above format for general administration, for clarity, we will use the <code>.service</code> suffix for the remainder of the commands to be explicit about the target we are operating on.</p>

<p>To stop a currently running service, you can use the <code>stop</code> command instead:</p>
<pre class="code-pre "><code langs="">sudo systemctl stop <span class="highlight">application</span>.service
</code></pre>
<h3 id="restarting-and-reloading">Restarting and Reloading</h3>

<p>To restart a running service, you can use the <code>restart</code> command:</p>
<pre class="code-pre "><code langs="">sudo systemctl restart <span class="highlight">application</span>.service
</code></pre>
<p>If the application in question is able to reload its configuration files (without restarting), you can issue the <code>reload</code> command to initiate that process:</p>
<pre class="code-pre "><code langs="">sudo systemctl reload <span class="highlight">application</span>.service
</code></pre>
<p>If you are unsure whether the service has the functionality to reload its configuration, you can issue the <code>reload-or-restart</code> command.  This will reload the configuration in-place if available.  Otherwise, it will restart the service so the new configuration is picked up:</p>
<pre class="code-pre "><code langs="">sudo systemctl reload-or-restart <span class="highlight">application</span>.service
</code></pre>
<h3 id="enabling-and-disabling-services">Enabling and Disabling Services</h3>

<p>The above commands are useful for starting or stopping commands during the current session.  To tell <code>systemd</code> to start services automatically at boot, you must enable them.</p>

<p>To start a service at boot, use the <code>enable</code> command:</p>
<pre class="code-pre "><code langs="">sudo systemctl enable <span class="highlight">application</span>.service
</code></pre>
<p>This will create a symbolic link from the system's copy of the service file (usually in <code>/lib/systemd/system</code> or <code>/etc/systemd/system</code>) into the location on disk where <code>systemd</code> looks for autostart files (usually <code>/etc/systemd/system/<span class="highlight">some_target</span>.target.wants</code>.  We will go over what a target is later in this guide).</p>

<p>To disable the service from starting automatically, you can type:</p>
<pre class="code-pre "><code langs="">sudo systemctl disable <span class="highlight">application</span>.service
</code></pre>
<p>This will remove the symbolic link that indicated that the service should be started automatically.</p>

<p>Keep in mind that enabling a service does not start it in the current session.  If you wish to start the service and enable it at boot, you will have to issue both the <code>start</code> and <code>enable</code> commands.</p>

<h3 id="checking-the-status-of-services">Checking the Status of Services</h3>

<p>To check the status of a service on your system, you can use the <code>status</code> command:</p>
<pre class="code-pre "><code langs="">systemctl status <span class="highlight">application</span>.service
</code></pre>
<p>This will provide you with the service state, the cgroup hierarchy, and the first few log lines.</p>

<p>For instance, when checking the status of an Nginx server, you may see output like this:</p>
<pre class="code-pre "><code langs="">● nginx.service - A high performance web server and a reverse proxy server
   Loaded: loaded (/usr/lib/systemd/system/nginx.service; enabled; vendor preset: disabled)
   Active: active (running) since Tue 2015-01-27 19:41:23 EST; 22h ago
 Main PID: 495 (nginx)
   CGroup: /system.slice/nginx.service
           ├─495 nginx: master process /usr/bin/nginx -g pid /run/nginx.pid; error_log stderr;
           └─496 nginx: worker process

Jan 27 19:41:23 desktop systemd[1]: Starting A high performance web server and a reverse proxy server...
Jan 27 19:41:23 desktop systemd[1]: Started A high performance web server and a reverse proxy server.
</code></pre>
<p>This gives you a nice overview of the current status of the application, notifying you of any problems and any actions that may be required.</p>

<p>There are also methods for checking for specific states.  For instance, to check to see if a unit is currently active (running), you can use the <code>is-active</code> command:</p>
<pre class="code-pre "><code langs="">systemctl is-active <span class="highlight">application</span>.service
</code></pre>
<p>This will return the current unit state, which is usually <code>active</code> or <code>inactive</code>.  The exit code will be "0" if it is active, making the result simpler to parse programatically.</p>

<p>To see if the unit is enabled, you can use the <code>is-enabled</code> command:</p>
<pre class="code-pre "><code langs="">systemctl is-enabled <span class="highlight">application</span>.service
</code></pre>
<p>This will output whether the service is <code>enabled</code> or <code>disabled</code> and will again set the exit code to "0" or "1" depending on the answer to the command question.</p>

<p>A third check is whether the unit is in a failed state.  This indicates that there was a problem starting the unit in question:</p>
<pre class="code-pre "><code langs="">systemctl is-failed <span class="highlight">application</span>.service
</code></pre>
<p>This will return <code>active</code> if it is running properly or <code>failed</code> if an error occurred.  If the unit was intentionally stopped, it may return <code>unknown</code> or <code>inactive</code>.  An exit status of "0" indicates that a failure occurred and an exit status of "1" indicates any other status.</p>

<h2 id="system-state-overview">System State Overview</h2>

<p>The commands so far have been useful for managing single services, but they are not very helpful for exploring the current state of the system.  There are a number of <code>systemctl</code> commands that provide this information.</p>

<h3 id="listing-current-units">Listing Current Units</h3>

<p>To see a list of all of the active units that <code>systemd</code> knows about, we can use the <code>list-units</code> command:</p>
<pre class="code-pre "><code langs="">systemctl list-units
</code></pre>
<p>This will show you a list of all of the units that <code>systemd</code> currently has active on the system.  The output will look something like this:</p>
<pre class="code-pre "><code langs="">UNIT                                      LOAD   ACTIVE SUB     DESCRIPTION
atd.service                               loaded active running ATD daemon
avahi-daemon.service                      loaded active running Avahi mDNS/DNS-SD Stack
dbus.service                              loaded active running D-Bus System Message Bus
dcron.service                             loaded active running Periodic Command Scheduler
dkms.service                              loaded active exited  Dynamic Kernel Modules System
getty@tty1.service                        loaded active running Getty on tty1

. . .
</code></pre>
<p>The output has the following columns:</p>

<ul>
<li><strong>UNIT</strong>: The <code>systemd</code> unit name</li>
<li><strong>LOAD</strong>: Whether the unit's configuration has been parsed by <code>systemd</code>.  The configuration of loaded units is kept in memory.</li>
<li><strong>ACTIVE</strong>: A summary state about whether the unit is active.  This is usually a fairly basic way to tell if the unit has started successfully or not.</li>
<li><strong>SUB</strong>: This is a lower-level state that indicates more detailed information about the unit.  This often varies by unit type, state, and the actual method in which the unit runs.</li>
<li><strong>DESCRIPTION</strong>: A short textual description of what the unit is/does.</li>
</ul>

<p>Since the <code>list-units</code> command shows only active units by default, all of the entries above will show "loaded" in the LOAD column and "active" in the ACTIVE column.  This display is actually the default behavior of <code>systemctl</code> when called without additional commands, so you will see the same thing if you call <code>systemctl</code> with no arguments:</p>
<pre class="code-pre "><code langs="">systemctl
</code></pre>
<p>We can tell <code>systemctl</code> to output different information by adding additional flags.  For instance, to see all of the units that <code>systemd</code> has loaded (or attempted to load), regardless of whether they are currently active, you can use the <code>--all</code> flag, like this:</p>
<pre class="code-pre "><code langs="">systemctl list-units --all
</code></pre>
<p>This will show any unit that <code>systemd</code> loaded or attempted to load, regardless of its current state on the system.  Some units become inactive after running, and some units that <code>systemd</code> attempted to load may have not been found on disk.</p>

<p>You can use other flags to filter these results.  For example, we can use the <code>--state=</code> flag to indicate the LOAD, ACTIVE, or SUB states that we wish to see.  You will have to keep the <code>--all</code> flag so that <code>systemctl</code> allows non-active units to be displayed:</p>
<pre class="code-pre "><code langs="">systemctl list-units --all --state=inactive
</code></pre>
<p>Another common filter is the <code>--type=</code> filter.  We can tell <code>systemctl</code> to only display units of the type we are interested in.  For example, to see only active service units, we can use:</p>
<pre class="code-pre "><code langs="">systemctl list-units --type=service
</code></pre>
<h3 id="listing-all-unit-files">Listing All Unit Files</h3>

<p>The <code>list-units</code> command only displays units that <code>systemd</code> has attempted to parse and load into memory.  Since <code>systemd</code> will only read units that it thinks it needs, this will not necessarily include all of the available units on the system.  To see <em>every</em> available unit file within the <code>systemd</code> paths, including those that <code>systemd</code> has not attempted to load, you can use the <code>list-unit-files</code> command instead:</p>
<pre class="code-pre "><code langs="">systemctl list-unit-files
</code></pre>
<p>Units are representations of resources that <code>systemd</code> knows about.  Since <code>systemd</code> has not necessarily read all of the unit definitions in this view, it only presents information about the files themselves.  The output has two columns: the unit file and the state.</p>
<pre class="code-pre "><code langs="">UNIT FILE                                  STATE   
proc-sys-fs-binfmt_misc.automount          static  
dev-hugepages.mount                        static  
dev-mqueue.mount                           static  
proc-fs-nfsd.mount                         static  
proc-sys-fs-binfmt_misc.mount              static  
sys-fs-fuse-connections.mount              static  
sys-kernel-config.mount                    static  
sys-kernel-debug.mount                     static  
tmp.mount                                  static  
var-lib-nfs-rpc_pipefs.mount               static  
org.cups.cupsd.path                        enabled

. . .
</code></pre>
<p>The state will usually be "enabled", "disabled", "static", or "masked".  In this context, static means that the unit file does not contain an "install" section, which is used to enable a unit.  As such, these units cannot be enabled.  Usually, this means that the unit performs a one-off action or is used only as a dependency of another unit and should not be run by itself.</p>

<p>We will cover what "masked" means momentarily.</p>

<h2 id="unit-management">Unit Management</h2>

<p>So far, we have been working with services and displaying information about the unit and unit files that <code>systemd</code> knows about.  However, we can find out more specific information about units using some additional commands.</p>

<h3 id="displaying-a-unit-file">Displaying a Unit File</h3>

<p>To display the unit file that <code>systemd</code> has loaded into its system, you can use the <code>cat</code> command (this was added in <code>systemd</code> version 209).  For instance, to see the unit file of the <code>atd</code> scheduling daemon, we could type:</p>
<pre class="code-pre "><code langs="">systemctl cat atd.service
</code></pre><pre class="code-pre "><code langs="">[Unit]
Description=ATD daemon

[Service]
Type=forking
ExecStart=/usr/bin/atd

[Install]
WantedBy=multi-user.target
</code></pre>
<p>The output is the unit file as known to the currently running <code>systemd</code> process.  This can be important if you have modified unit files recently or if you are overriding certain options in a unit file fragment (we will cover this later).</p>

<h3 id="displaying-dependencies">Displaying Dependencies</h3>

<p>To see a unit's dependency tree, you can use the <code>list-dependencies</code> command:</p>
<pre class="code-pre "><code langs="">systemctl list-dependencies sshd.service
</code></pre>
<p>This will display a hierarchy mapping the dependencies that must be dealt with in order to start the unit in question.  Dependencies, in this context, include those units that are either required by or wanted by the units above it.</p>
<pre class="code-pre "><code langs="">sshd.service
├─system.slice
└─basic.target
  ├─microcode.service
  ├─rhel-autorelabel-mark.service
  ├─rhel-autorelabel.service
  ├─rhel-configure.service
  ├─rhel-dmesg.service
  ├─rhel-loadmodules.service
  ├─paths.target
  ├─slices.target

. . .
</code></pre>
<p>The recursive dependencies are only displayed for <code>.target</code> units, which indicate system states.  To recursively list all dependencies, include the <code>--all</code> flag. </p>

<p>To show reverse dependencies (units that depend on the specified unit), you can add the <code>--reverse</code> flag to the command.  Other flags that are useful are the <code>--before</code> and <code>--after</code> flags, which can be used to show units that depend on the specified unit starting before and after themselves, respectively.</p>

<h3 id="checking-unit-properties">Checking Unit Properties</h3>

<p>To see the low-level properties of a unit, you can use the <code>show</code> command.  This will display a list of properties that are set for the specified unit using a <code>key=value</code> format:</p>
<pre class="code-pre "><code langs="">systemctl show sshd.service
</code></pre><pre class="code-pre "><code langs="">Id=sshd.service
Names=sshd.service
Requires=basic.target
Wants=system.slice
WantedBy=multi-user.target
Conflicts=shutdown.target
Before=shutdown.target multi-user.target
After=syslog.target network.target auditd.service systemd-journald.socket basic.target system.slice
Description=OpenSSH server daemon

. . .
</code></pre>
<p>If you want to display a single property, you can pass the <code>-p</code> flag with the property name.  For instance, to see the conflicts that the <code>sshd.service</code> unit has, you can type:</p>
<pre class="code-pre "><code langs="">systemctl show sshd.service -p Conflicts
</code></pre><pre class="code-pre "><code langs="">Conflicts=shutdown.target
</code></pre>
<h3 id="masking-and-unmasking-units">Masking and Unmasking Units</h3>

<p>We saw in the service management section how to stop or disable a service, but <code>systemd</code> also has the ability to mark a unit as <em>completely</em> unstartable, automatically or manually, by linking it to <code>/dev/null</code>.  This is called masking the unit, and is possible with the <code>mask</code> command:</p>
<pre class="code-pre "><code langs="">sudo systemctl mask nginx.service
</code></pre>
<p>This will prevent the Nginx service from being started, automatically or manually, for as long as it is masked.</p>

<p>If you check the <code>list-unit-files</code>, you will see the service is now listed as masked:</p>
<pre class="code-pre "><code langs="">systemctl list-unit-files
</code></pre><pre class="code-pre "><code langs="">. . .

kmod-static-nodes.service              static  
ldconfig.service                       static  
mandb.service                          static  
messagebus.service                     static  
nginx.service                          <span class="highlight">masked</span>
quotaon.service                        static  
rc-local.service                       static  
rdisc.service                          disabled
rescue.service                         static

. . .
</code></pre>
<p>If you attempt to start the service, you will see a message like this:</p>
<pre class="code-pre "><code langs="">sudo systemctl start nginx.service
</code></pre><pre class="code-pre "><code langs="">Failed to start nginx.service: Unit nginx.service is masked.
</code><</pre>