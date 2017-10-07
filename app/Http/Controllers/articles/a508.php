<link rel='stylesheet' href='digital_ocean.css'><br><img src=https://community-cdn-digitalocean-com.global.ssl.fastly.net/assets/tutorials/images/large/systemdessentials-twitter.png?1430413740/> <br> 
      <h3 id="introduction">Introduction</h3>

<p>In recent years, Linux distributions have increasingly transitioned from other init systems to <code>systemd</code>.  The <code>systemd</code> suite of tools provides a fast and flexible init model for managing an entire machine from boot onwards.</p>

<p>In this guide, we'll give you a quick run through of the most important commands you'll want to know for managing a <code>systemd</code> enabled server.  These should work on any server that implements <code>systemd</code> (any OS version at or above Ubuntu 15.04, Debian 8, CentOS 7, Fedora 15).  Let's get started.</p>

<h2 id="basic-unit-management">Basic Unit Management</h2>

<p>The basic object that <code>systemd</code> manages and acts upon is a "unit".  Units can be of many types, but the most common type is a "service" (indicated by a unit file ending in <code>.service</code>).  To manage services on a <code>systemd</code> enabled server, our main tool is the <code>systemctl</code> command.</p>

<p>All of the normal init system commands have equivalent actions with the <code>systemctl</code> command.  We will use the <code>nginx.service</code> unit to demonstrate (you'll have to install Nginx with your package manager to get this service file).</p>

<p>For instance, we can start the service by typing:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo systemctl start nginx.service
</li></ul></code></pre>
<p>We can stop it again by typing:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo systemctl stop nginx.service
</li></ul></code></pre>
<p>To restart the service, we can type:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo systemctl restart nginx.service
</li></ul></code></pre>
<p>To attempt to reload the service without interrupting normal functionality, we can type:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo systemctl reload nginx.service
</li></ul></code></pre>
<h2 id="enabling-or-disabling-units">Enabling or Disabling Units</h2>

<p>By default, most <code>systemd</code> unit files are not started automatically at boot.  To configure this functionality, you need to "enable" to unit.  This hooks it up to a certain boot "target", causing it to be triggered when that target is started.</p>

<p>To enable a service to start automatically at boot, type:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo systemctl enable nginx.service
</li></ul></code></pre>
<p>If you wish to disable the service again, type:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo systemctl disable nginx.service
</li></ul></code></pre>
<h2 id="getting-an-overview-of-the-system-state">Getting an Overview of the System State</h2>

<p>There is a great deal of information that we can pull from a <code>systemd</code> server to get an overview of the system state.</p>

<p>For instance, to get all of the unit files that <code>systemd</code> has listed as "active", type (you can actually leave off the <code>list-units</code> as this is the default <code>systemctl</code> behavior):</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">systemctl list-units
</li></ul></code></pre>
<p>To list all of the units that <code>systemd</code> has loaded or attempted to load into memory, including those that are not currently active, add the <code>--all</code> switch:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">systemctl list-units --all
</li></ul></code></pre>
<p>To list all of the units installed on the system, including those that <code>systemd</code> has not tried to load into memory, type:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">systemctl list-unit-files
</li></ul></code></pre>
<h2 id="viewing-basic-log-information">Viewing Basic Log Information</h2>

<p>A <code>systemd</code> component called <code>journald</code> collects and manages journal entries from all parts of the system.  This is basically log information from applications and the kernel.</p>

<p>To see all log entries, starting at the oldest entry, type:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">journalctl
</li></ul></code></pre>
<p>By default, this will show you entries from the current and previous boots if <code>journald</code> is configured to save previous boot records.  Some distributions enable this by default, while others do not (to enable this, either edit the <code>/etc/systemd/journald.conf</code> file and set the <code>Storage=</code> option to "persistent", or create the persistent directory by typing <code>sudo mkdir -p /var/log/journal</code>).</p>

<p>If you only wish to see the journal entries from the current boot, add the <code>-b</code> flag:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">journalctl -b
</li></ul></code></pre>
<p>To see only kernel messages, such as those that are typically represented by <code>dmesg</code>, you can use the <code>-k</code> flag:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">journalctl -k
</li></ul></code></pre>
<p>Again, you can limit this only to the current boot by appending the <code>-b</code> flag:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">journalctl -k -b
</li></ul></code></pre>
<h2 id="querying-unit-states-and-logs">Querying Unit States and Logs</h2>

<p>While the above commands gave you access to the general system state, you can also get information about the state of individual units.</p>

<p>To see an overview of the current state of a unit, you can use the <code>status</code> option with the <code>systemctl</code> command.  This will show you whether the unit is active, information about the process, and the latest journal entries:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">systemctl status nginx.service
</li></ul></code></pre>
<p>To see all of the journal entries for the unit in question, give the <code>-u</code> option with the unit name to the <code>journalctl</code> command:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">journalctl -u nginx.service
</li></ul></code></pre>
<p>As always, you can limit the entries to the current boot by adding the <code>-b</code> flag:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">journalctl -b -u nginx.service
</li></ul></code></pre>
<h2 id="inspecting-units-and-unit-files">Inspecting Units and Unit Files</h2>

<p>By now, you know how to modify a unit's state by starting or stopping it, and you know how to view state and journal information to get an idea of what is happening with the process.  However, we haven't seen yet how to inspect other aspects of units and unit files.</p>

<p>A unit file contains the parameters that <code>systemd</code> uses to manage and run a unit.  To see the full contents of a unit file, type:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">systemctl cat nginx.service
</li></ul></code></pre>
<p>To see the dependency tree of a unit (which units <code>systemd</code> will attempt to activate when starting the unit), type:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">systemctl list-dependencies nginx.service
</li></ul></code></pre>
<p>This will show the dependent units, with <code>target</code> units recursively expanded.  To expand all dependent units recursively, pass the <code>--all</code> flag:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">systemctl list-dependencies --all nginx.service
</li></ul></code></pre>
<p>Finally, to see the low-level details of the unit's settings on the system, you can use the <code>show</code> option:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">systemctl show nginx.service
</li></ul></code></pre>
<p>This will give you the value of each parameter being managed by <code>systemd</code>.</p>

<h2 id="modifying-unit-files">Modifying Unit Files</h2>

<p>If you need to make a modification to a unit file, <code>systemd</code> allows you to make changes from the <code>systemctl</code> command itself so that you don't have to go to the actual disk location.</p>

<p>To add a unit file snippet, which can be used to append or override settings in the default unit file, simply call the <code>edit</code> option on the unit:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo systemctl edit nginx.service
</li></ul></code></pre>
<p>If you prefer to modify the entire content of the unit file instead of creating a snippet, pass the <code>--full</code> flag:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo systemctl edit --full nginx.service
</li></ul></code></pre>
<p>After modifying a unit file, you should reload the <code>systemd</code> process itself to pick up your changes:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo systemctl daemon-reload
</li></ul></code></pre>
<h2 id="using-targets-runlevels">Using Targets (Runlevels)</h2>

<p>Another function of an init system is to transition the server itself between different states.  Traditional init systems typically refer to these as "runlevels", allowing the system to only be in one runlevel at any one time. </p>

<p>In <code>systemd</code>, "targets" are used instead.  Targets are basically synchronization points that the server can used to bring the server into a specific state.  Service and other unit files can be tied to a target and multiple targets can be active at the same time.</p>

<p>To see all of the targets available on your system, type:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">systemctl list-unit-files --type=target
</li></ul></code></pre>
<p>To view the default target that <code>systemd</code> tries to reach at boot (which in turn starts all of the unit files that make up the dependency tree of that target), type:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">systemctl get-default
</li></ul></code></pre>
<p>You can change the default target that will be used at boot by using the <code>set-default</code> option:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo systemctl set-default multi-user.target
</li></ul></code></pre>
<p>To see what units are tied to a target, you can type:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">systemctl list-dependencies multi-user.target
</li></ul></code></pre>
<p>You can modify the system state to transition between targets with the <code>isolate</code> option.  This will stop any units that are not tied to the specified target.  Be sure that the target you are isolating does not stop any essential services:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo systemctl isolate multi-user.target
</li></ul></code></pre>
<h2 id="stopping-or-rebooting-the-server">Stopping or Rebooting the Server</h2>

<p>For some of the major states that a system can transition to, shortcuts are available.  For instance, to power off your server, you can type:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo systemctl poweroff
</li></ul></code></pre>
<p>If you wish to reboot the system instead, that can be accomplished by typing:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo systemctl reboot
</li></ul></code></pre>
<p>You can boot into rescue mode by typing:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo systemctl rescue
</li></ul></code></pre>
<p>Note that most operating systems include traditional aliases to these operations so that you can simply type <code>sudo poweroff</code> or <code>sudo reboot</code> without the <code>systemctl</code>.  However, this is not guaranteed to be set up on all systems.</p>

<h2 id="next-steps">Next Steps</h2>

<p>By now, you should know the basics of how to manage a server that uses <code>systemd</code>.  However, there is much more to learn as your needs expand.  Below are links to guides with more in-depth information about some of the components we discussed in this guide:</p>

<ul>
<li><a href="https://indiareads/community/tutorials/how-to-use-systemctl-to-manage-systemd-services-and-units">How To Use Systemctl to Manage Systemd Services and Units</a></li>
<li><a href="https://indiareads/community/tutorials/how-to-use-journalctl-to-view-and-manipulate-systemd-logs">How To Use Journalctl to View and Manipulate Systemd Logs</a></li>
<li><a href="https://indiareads/community/tutorials/understanding-systemd-units-and-unit-files">Understanding Systemd Units and Unit Files</a></li>
</ul>

<p>By learning how to leverage your init system's strengths, you can control the state of your machines and more easily manage your services and processes.</p>

    