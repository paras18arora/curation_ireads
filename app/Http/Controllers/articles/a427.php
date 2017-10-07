<link rel='stylesheet' href='digital_ocean.css'><br><img src=https://community-cdn-digitalocean-com.global.ssl.fastly.net/assets/tutorials/images/large/system_units_tw.jpg?1427296879/> <br> 
      <h3 id="introduction">Introduction</h3>

<p>Increasingly, Linux distributions are adopting or planning to adopt the <code>systemd</code> init system.  This powerful suite of software can manage many aspects of your server, from services to mounted devices and system states.</p>

<p>In <code>systemd</code>, a <code>unit</code> refers to any resource that the system knows how to operate on and manage.  This is the primary object that the <code>systemd</code> tools know how to deal with.  These resources are defined using configuration files called unit files.</p>

<p>In this guide, we will introduce you to the different units that <code>systemd</code> can handle.  We will also be covering some of the many directives that can be used in unit files in order to shape the way these resources are handled on your system.</p>

<h2 id="what-do-systemd-units-give-you">What do Systemd Units Give You?</h2>

<p>Units are the objects that <code>systemd</code> knows how to manage.  These are basically a standardized representation of system resources that can be managed by the suite of daemons and manipulated by the provided utilities.</p>

<p>Units in some ways can be said to similar to services or jobs in other init systems.  However, a unit has a much broader definition, as these can be used to abstract services, network resources, devices, filesystem mounts, and isolated resource pools.</p>

<p>Ideas that in other init systems may be handled with one unified service definition can be broken out into component units according to their focus.  This organizes by function and allows you to easily enable, disable, or extend functionality without modifying the core behavior of a unit.</p>

<p>Some features that units are able implement easily are:</p>

<ul>
<li><strong>socket-based activation</strong>:  Sockets associated with a service are best broken out of the daemon itself in order to be handled separately.  This provides a number of advantages, such as delaying the start of a service until the associated socket is first accessed.  This also allows the system to create all sockets early in the boot process, making it possible to boot the associated services in parallel.</li>
<li><strong>bus-based activation</strong>:  Units can also be activated on the bus interface provided by <code>D-Bus</code>.  A unit can be started when an associated bus is published.</li>
<li><strong>path-based activation</strong>: A unit can be started based on activity on or the availability of certain filesystem paths.  This utilizes <code>inotify</code>.</li>
<li><strong>device-based activation</strong>: Units can also be started at the first availability of associated hardware by leveraging <code>udev</code> events.</li>
<li><strong>implicit dependency mapping</strong>: Most of the dependency tree for units can be built by <code>systemd</code> itself.  You can still add dependency and ordering information, but most of the heavy lifting is taken care of for you.</li>
<li><strong>instances and templates</strong>: Template unit files can be used to create multiple instances of the same general unit.  This allows for slight variations or sibling units that all provide the same general function.</li>
<li><strong>easy security hardening</strong>:  Units can implement some fairly good security features by adding simple directives.  For example, you can specify no or read-only access to part of the filesystem, limit kernel capabilities, and assign private <code>/tmp</code> and network access.</li>
<li><strong>drop-ins and snippets</strong>: Units can easily be extended by providing snippets that will override parts of the system's unit file.  This makes it easy to switch between vanilla and customized unit implementations.</li>
</ul>

<p>There are many other advantages that <code>systemd</code> units have over other init systems' work items, but this should give you an idea of the power that can be leveraged using native configuration directives.</p>

<h2 id="where-are-systemd-unit-files-found">Where are Systemd Unit Files Found?</h2>

<p>The files that define how <code>systemd</code> will handle a unit can be found in many different locations, each of which have different priorities and implications.</p>

<p>The system's copy of unit files are generally kept in the <code>/lib/systemd/system</code> directory.  When software installs unit files on the system, this is the location where they are placed by default.</p>

<p>Unit files stored here are able to be started and stopped on-demand during a session.  This will be the generic, vanilla unit file, often written by the upstream project's maintainers that should work on any system that deploys <code>systemd</code> in its standard implementation.  You should not edit files in this directory.  Instead you should override the file, if necessary, using another unit file location which will supersede the file in this location.</p>

<p>If you wish to modify the way that a unit functions, the best location to do so is within the <code>/etc/systemd/system</code> directory.  Unit files found in this directory location take precedence over any of the other locations on the filesystem.  If you need to modify the system's copy of a unit file, putting a replacement in this directory is the safest and most flexible way to do this.</p>

<p>If you wish to override only specific directives from the system's unit file, you can actually provide unit file snippets within a subdirectory.  These will append or modify the directives of the system's copy, allowing you to specify only the options you want to change. </p>

<p>The correct way to do this is to create a directory named after the unit file with <code>.d</code> appended on the end.  So for a unit called <code>example.service</code>, a subdirectory called <code>example.service.d</code> could be created.  Within this directory a file ending with <code>.conf</code> can be used to override or extend the attributes of the system's unit file.</p>

<p>There is also a location for run-time unit definitions at <code>/run/systemd/system</code>.  Unit files found in this directory have a priority landing between those in <code>/etc/systemd/system</code> and <code>/lib/systemd/system</code>.  Files in this location are given less weight than the former location, but more weight than the latter.</p>

<p>The <code>systemd</code> process itself uses this location for dynamically created unit files created at runtime.  This directory can be used to change the system's unit behavior for the duration of the session.  All changes made in this directory will be lost when the server is rebooted.</p>

<h2 id="types-of-units">Types of Units</h2>

<p><code>Systemd</code> categories units according to the type of resource they describe.  The easiest way to determine the type of a unit is with its type suffix, which is appended to the end of the resource name.  The following list describes the types of units available to <code>systemd</code>:</p>

<p><strong><code>.service</code></strong>: A service unit describes how to manage a service or application on the server.  This will include how to start or stop the service, under which circumstances it should be automatically started, and the dependency and ordering information for related software.</p>

<ul>
<li><strong><code>.socket</code></strong>: A socket unit file describes a network or IPC socket, or a FIFO buffer that <code>systemd</code> uses for socket-based activation.  These always have an associated <code>.service</code> file that will be started when activity is seen on the socket that this unit defines.</li>
<li><strong><code>.device</code></strong>: A unit that describes a device that has been designated as needing <code>systemd</code> management by <code>udev</code> or the <code>sysfs</code> filesystem.  Not all devices will have <code>.device</code> files.  Some scenarios where <code>.device</code> units may be necessary are for ordering, mounting, and accessing the devices.</li>
<li><strong><code>.mount</code></strong>: This unit defines a mountpoint on the system to be managed by <code>systemd</code>.  These are named after the mount path, with slashes changed to dashes.  Entries within <code>/etc/fstab</code> can have units created automatically.</li>
<li><strong><code>.automount</code></strong>:  An <code>.automount</code> unit configures a mountpoint that will be automatically mounted.  These must be named after the mount point they refer to and must have a matching <code>.mount</code> unit to define the specifics of the mount.</li>
<li><strong><code>.swap</code></strong>: This unit describes swap space on the system.  The name of these units must reflect the device or file path of the space.</li>
<li><strong><code>.target</code></strong>: A target unit is used to provide synchronization points for other units when booting up or changing states.  They also can be used to bring the system to a new state.  Other units specify their relation to targets to become tied to the target's operations.</li>
<li><strong><code>.path</code></strong>: This unit defines a path that can be used for path-based activation.  By default, a <code>.service</code> unit of the same base name will be started when the path reaches the specified state.  This uses <code>inotify</code> to monitor the path for changes.</li>
<li><strong><code>.timer</code></strong>: A <code>.timer</code> unit defines a timer that will be managed by <code>systemd</code>, similar to a <code>cron</code> job for delayed or scheduled activation.  A matching unit will be started when the timer is reached.</li>
<li><strong><code>.snapshot</code></strong>: A <code>.snapshot</code> unit is created automatically by the <code>systemctl snapshot</code> command.  It allows you to reconstruct the current state of the system after making changes.  Snapshots do not survive across sessions and are used to roll back temporary states.</li>
<li><strong><code>.slice</code></strong>: A <code>.slice</code> unit is associated with Linux Control Group nodes, allowing resources to be restricted or assigned to any processes associated with the slice.  The name reflects its hierarchical position within the <code>cgroup</code> tree.  Units are placed in certain slices by default depending on their type.</li>
<li><strong><code>.scope</code></strong>: Scope units are created automatically by <code>systemd</code> from information received from its bus interfaces.  These are used to manage sets of system processes that are created externally.</li>
</ul>

<p>As you can see, there are many different units that <code>systemd</code> knows how to manage.  Many of the unit types work together to add functionality.  For instance, some units are used to trigger other units and provide activation functionality.</p>

<p>We will mainly be focusing on <code>.service</code> units due to their utility and the consistency in which administrators need to managed these units.</p>

<h2 id="anatomy-of-a-unit-file">Anatomy of a Unit File</h2>

<p>The internal structure of unit files are organized with sections.  Sections are denoted by a pair of square brackets "<code>[</code>" and "<code>]</code>" with the section name enclosed within.  Each section extends until the beginning of the subsequent section or until the end of the file.</p>

<h3 id="general-characteristics-of-unit-files">General Characteristics of Unit Files</h3>

<p>Section names are well defined and case-sensitive.  So, the section <code>[Unit]</code> will <strong>not</strong> be interpreted correctly if it is spelled like <code>[UNIT]</code>.  If you need to add non-standard sections to be parsed by applications other than <code>systemd</code>, you can add a <code>X-</code> prefix to the section name.</p>

<p>Within these sections, unit behavior and metadata is defined through the use of simple directives using a key-value format with assignment indicated by an equal sign, like this:</p>
<pre class="code-pre "><code langs="">[<span class="highlight">Section</span>]
<span class="highlight">Directive1</span>=<span class="highlight">value</span>
<span class="highlight">Directive2</span>=<span class="highlight">value</span>

. . .
</code></pre>
<p>In the event of an override file (such as those contained in a <code><span class="highlight">unit</span>.<span class="highlight">type</span>.d</code> directory), directives can be reset by assigning them to an empty string.  For example, the system's copy of a unit file may contain a directive set to a value like this:</p>
<pre class="code-pre "><code langs=""><span class="highlight">Directive1</span>=<span class="highlight">default_value</span>
</code></pre>
<p>The <code>default_value</code> can be eliminated in an override file by referencing <code><span class="highlight">Directive1</span></code> without a value, like this:</p>
<pre class="code-pre "><code langs=""><span class="highlight">Directive1</span>=
</code></pre>
<p>In general, <code>systemd</code> allows for easy and flexible configuration.  For example, multiple boolean expressions are accepted (<code>1</code>, <code>yes</code>, <code>on</code>, and <code>true</code> for affirmative and <code>0</code>, <code>no</code> <code>off</code>, and <code>false</code> for the opposite answer).  Times can be intelligently parsed, with seconds assumed for unit-less values and combining multiple formats accomplished internally.</p>

<h3 id="unit-section-directives">[Unit] Section Directives</h3>

<p>The first section found in most unit files is the <code>[Unit]</code> section.  This is generally used for defining metadata for the unit and configuring the relationship of the unit to other units.</p>

<p>Although section order does not matter to <code>systemd</code> when parsing the file, this section is often placed at the top because it provides an overview of the unit.  Some common directives that you will find in the <code>[Unit]</code> section are:</p>

<ul>
<li><strong><code>Description=</code></strong>: This directive can be used to describe the name and basic functionality of the unit.  It is returned by various <code>systemd</code> tools, so it is good to set this to something short, specific, and informative.</li>
<li><strong><code>Documentation=</code></strong>: This directive provides a location for a list of URIs for documentation.  These can be either internally available <code>man</code> pages or web accessible URLs.  The <code>systemctl status</code> command will expose this information, allowing for easy discoverability.</li>
<li><strong><code>Requires=</code></strong>: This directive lists any units upon which this unit essentially depends.  If the current unit is activated, the units listed here must successfully activate as well, else this unit will fail.  These units are started in parallel with the current unit by default.</li>
<li><strong><code>Wants=</code></strong>: This directive is similar to <code>Requires=</code>, but less strict.  <code>Systemd</code> will attempt to start any units listed here when this unit is activated.  If these units are not found or fail to start, the current unit will continue to function.  This is the recommended way to configure most dependency relationships.  Again, this implies a parallel activation unless modified by other directives.</li>
<li><strong><code>BindsTo=</code></strong>: This directive is similar to <code>Requires=</code>, but also causes the current unit to stop when the associated unit terminates.</li>
<li><strong><code>Before=</code></strong>: The units listed in this directive will not be started until the current unit is marked as started if they are activated at the same time.  This does not imply a dependency relationship and must be used in conjunction with one of the above directives if this is desired.</li>
<li><strong><code>After=</code></strong>: The units listed in this directive will be started before starting the current unit.  This does not imply a dependency relationship and one must be established through the above directives if this is required.</li>
<li><strong><code>Conflicts=</code></strong>: This can be used to list units that cannot be run at the same time as the current unit.  Starting a unit with this relationship will cause the other units to be stopped.</li>
<li><strong><code>Condition...=</code></strong>: There are a number of directives that start with <code>Condition</code> which allow the administrator to test certain conditions prior to starting the unit.  This can be used to provide a generic unit file that will only be run when on appropriate systems.  If the condition is not met, the unit is gracefully skipped.</li>
<li><strong><code>Assert...=</code></strong>: Similar to the directives that start with <code>Condition</code>, these directives check for different aspects of the running environment to decide whether the unit should activate.  However, unlike the <code>Condition</code> directives, a negative result causes a failure with this directive.</li>
</ul>

<p>Using these directives and a handful of others, general information about the unit and its relationship to other units and the operating system can be established.</p>

<h3 id="install-section-directives">[Install] Section Directives</h3>

<p>On the opposite side of unit file, the last section is often the <code>[Install]</code> section.  This section is optional and is used to define the behavior or a unit if it is enabled or disabled.  Enabling a unit marks it to be automatically started at boot.  In essence, this is accomplished by latching the unit in question onto another unit that is somewhere in the line of units to be started at boot.  </p>

<p>Because of this, only units that can be enabled will have this section.  The directives within dictate what should happen when the unit is enabled:</p>

<ul>
<li><strong><code>WantedBy=</code></strong>:  The <code>WantedBy=</code> directive is the most common way to specify how a unit should be enabled.  This directive allows you to specify a dependency relationship in a similar way to the <code>Wants=</code> directive does in the <code>[Unit]</code> section.  The difference is that this directive is included in the ancillary unit allowing the primary unit listed to remain relatively clean.  When a unit with this directive is enabled, a directory will be created within <code>/etc/systemd/system</code> named after the specified unit with <code>.wants</code> appended to the end.  Within this, a symbolic link to the current unit will be created, creating the dependency.  For instance, if the current unit has <code>WantedBy=multi-user.target</code>, a directory called <code>multi-user.target.wants</code> will be created within <code>/etc/systemd/system</code> (if not already available) and a symbolic link to the current unit will be placed within.  Disabling this unit removes the link and removes the dependency relationship.</li>
<li><strong><code>RequiredBy=</code></strong>: This directive is very similar to the <code>WantedBy=</code> directive, but instead specifies a required dependency that will cause the activation to fail if not met.  When enabled, a unit with this directive will create a directory ending with <code>.requires</code>.</li>
<li><strong><code>Alias=</code></strong>: This directive allows the unit to be enabled under another name as well.  Among other uses, this allows multiple providers of a function to be available, so that related units can look for any provider of the common aliased name.</li>
<li><strong><code>Also=</code></strong>: This directive allows units to be enabled or disabled as a set.  Supporting units that should always be available when this unit is active can be listed here.  They will be managed as a group for installation tasks.</li>
<li><strong><code>DefaultInstance=</code></strong>: For template units (covered later) which can produce unit instances with unpredictable names, this can be used as a fallback value for the name if an appropriate name is not provided.</li>
</ul>

<h3 id="unit-specific-section-directives">Unit-Specific Section Directives</h3>

<p>Sandwiched between the previous two sections, you will likely find unit type-specific sections.  Most unit types offer directives that only apply to their specific type.  These are available within sections named after their type.  We will cover those briefly here.</p>

<p>The <code>device</code>, <code>target</code>, <code>snapshot</code>, and <code>scope</code> unit types have no unit-specific directives, and thus have no associated sections for their type.</p>

<h4 id="the-service-section">The [Service] Section</h4>

<p>The <code>[Service]</code> section is used to provide configuration that is only applicable for services.</p>

<p>One of the basic things that should be specified within the <code>[Service]</code> section is the <code>Type=</code> of the service.  This categorizes services by their process and daemonizing behavior.  This is important because it tells <code>systemd</code> how to correctly manage the servie and find out its state. </p>

<p>The <code>Type=</code> directive can be one of the following:</p>

<ul>
<li><strong>simple</strong>: The main process of the service is specified in the start line.  This is the default if the <code>Type=</code> and <code>Busname=</code> directives are not set, but the <code>ExecStart=</code> is set.  Any communication should be handled outside of the unit through a second unit of the appropriate type (like through a <code>.socket</code> unit if this unit must communicate using sockets).</li>
<li><strong>forking</strong>: This service type is used when the service forks a child process, exiting the parent process almost immediately.  This tells <code>systemd</code> that the process is still running even though the parent exited.</li>
<li><strong>oneshot</strong>: This type indicates that the process will be short-lived and that <code>systemd</code> should wait for the process to exit before continuing on with other units.  This is the default <code>Type=</code> and <code>ExecStart=</code> are not set.  It is used for one-off tasks.</li>
<li><strong>dbus</strong>: This indicates that unit will take a name on the D-Bus bus.  When this happens, <code>systemd</code> will continue to process the next unit.</li>
<li><strong>notify</strong>: This indicates that the service will issue a notification when it has finished starting up.  The <code>systemd</code> process will wait for this to happen before proceeding to other units.</li>
<li><strong>idle</strong>: This indicates that the service will not be run until all jobs are dispatched.</li>
</ul>

<p>Some additional directives may be needed when using certain service types.  For instance:</p>

<ul>
<li><strong><code>RemainAfterExit=</code></strong>:  This directive is commonly used with the <code>oneshot</code> type.  It indicates that the service should be considered active even after the process exits.</li>
<li><strong><code>PIDFile=</code></strong>: If the service type is marked as "forking", this directive is used to set the path of the file that should contain the process ID number of the main child that should be monitored.</li>
<li><strong><code>BusName=</code></strong>: This directive should be set to the D-Bus bus name that the service will attempt to acquire when using the "dbus" service type.</li>
<li><strong><code>NotifyAccess=</code></strong>:  This specifies access to the socket that should be used to listen for notifications when the "notify" service type is selected  This can be "none", "main", or "all.  The default, "none", ignores all status messages.  The "main" option will listen to messages from the main process and the "all" option will cause all members of the service's control group to be processed.</li>
</ul>

<p>So far, we have discussed some pre-requisite information, but we haven't actually defined how to manage our services.  The directives to do this are:</p>

<ul>
<li><strong><code>ExecStart=</code></strong>:  This specifies the full path and the arguments of the command to be executed to start the process.  This may only be specified once (except for "oneshot" services).  If the path to the command is preceded by a dash "-" character, non-zero exit statuses will be accepted without marking the unit activation as failed.</li>
<li><strong><code>ExecStartPre=</code></strong>: This can be used to provide additional commands that should be executed before the main process is started.  This can be used multiple times.  Again, commands must specify a full path and they can be preceded by "-" to indicate that the failure of the command will be tolerated.</li>
<li><strong><code>ExecStartPost=</code></strong>: This has the same exact qualities as <code>ExecStartPre=</code> except that it specifies commands that will be run <em>after</em> the main process is started.</li>
<li><strong><code>ExecReload=</code></strong>: This optional directive indicates the command necessary to reload the configuration of the service if available.</li>
<li><strong><code>ExecStop=</code></strong>:  This indicates the command needed to stop the service.  If this is not given, the process will be killed immediately when the service is stopped.</li>
<li><strong><code>ExecStopPost=</code></strong>: This can be used to specify commands to execute following the stop command.</li>
<li><strong><code>RestartSec=</code></strong>:  If automatically restarting the service is enabled, this specifies the amount of time to wait before attempting to restart the service.</li>
<li><strong><code>Restart=</code></strong>: This indicates the circumstances under which <code>systemd</code> will attempt to automatically restart the service.  This can be set to values like "always", "on-success", "on-failure", "on-abnormal", "on-abort", or "on-watchdog".  These will trigger a restart according to the way that the service was stopped.</li>
<li><strong><code>TimeoutSec=</code></strong>: This configures the amount of time that <code>systemd</code> will wait when stopping or stopping the service before marking it as failed or forcefully killing it.  You can set separate timeouts with <code>TimeoutStartSec=</code> and <code>TimeoutStopSec=</code> as well.</li>
</ul>

<h4 id="the-socket-section">The [Socket] Section</h4>

<p>Socket units are very common in <code>systemd</code> configurations because many services implement socket-based activation to provide better parallelization and flexibility.  Each socket unit must have a matching service unit that will be activated when the socket receives activity.</p>

<p>By breaking socket control outside of the service itself, sockets can be initialized early and the associated services can often be started in parallel.  By default, the socket name will attempt to start the service of the same name upon receiving a connection.  When the service is initialized, the socket will be passed to it, allowing it to begin processing any buffered requests.</p>

<p>To specify the actual socket, these directives are common:</p>

<ul>
<li><strong><code>ListenStream=</code></strong>: This defines an address for a stream socket which supports sequential, reliable communication.  Services that use TCP should use this socket type.</li>
<li><strong><code>ListenDatagram=</code></strong>: This defines an address for a datagram socket which supports fast, unreliable communication packets.  Services that use UDP should set this socket type.</li>
<li><strong><code>ListenSequentialPacket=</code></strong>: This defines an address for sequential, reliable communication with max length datagrams that preserves message boundaries.  This is found most often for Unix sockets.</li>
<li><strong><code>ListenFIFO</code></strong>:  Along with the other listening types, you can also specify a FIFO buffer instead of a socket.</li>
</ul>

<p>There are more types of listening directives, but the ones above are the most common.</p>

<p>Other characteristics of the sockets can be controlled through additional directives:</p>

<ul>
<li><strong><code>Accept=</code></strong>: This determines whether an additional instance of the service will be started for each connection.  If set to false (the default), one instance will handle all connections.</li>
<li><strong><code>SocketUser=</code></strong>: With a Unix socket, specifies the owner of the socket.  This will be the root user if left unset.</li>
<li><strong><code>SocketGroup=</code></strong>: With a Unix socket, specifies the group owner of the socket.  This will be the root group if neither this or the above are set.  If only the <code>SocketUser=</code> is set, <code>systemd</code> will try to find a matching group.</li>
<li><strong><code>SocketMode=</code></strong>: For Unix sockets or FIFO buffers, this sets the permissions on the created entity.</li>
<li><strong><code>Service=</code></strong>: If the service name does not match the <code>.socket</code> name, the service can be specified with this directive.</li>
</ul>

<h4 id="the-mount-section">The [Mount] Section</h4>

<p>Mount units allow for mount point management from within <code>systemd</code>.  Mount points are named after the directory that they control, with a translation algorithm applied. </p>

<p>For example, the leading slash is removed, all other slashes are translated into dashes "-", and all dashes and unprintable characters are replaced with C-style escape codes.  The result of this translation is used as the mount unit name.  Mount units will have an implicit dependency on other mounts above it in the hierarchy.</p>

<p>Mount units are often translated directly from <code>/etc/fstab</code> files during the boot process.  For the unit definitions automatically created and those that you wish to define in a unit file, the following directives are useful:</p>

<ul>
<li><strong><code>What=</code></strong>: The absolute path to the resource that needs to be mounted.</li>
<li><strong><code>Where=</code></strong>: The absolute path of the mount point where the resource should be mounted.  This should be the same as the unit file name, except using conventional filesystem notation.</li>
<li><strong><code>Type=</code></strong>: The filesystem type of the mount.</li>
<li><strong><code>Options=</code></strong>: Any mount options that need to be applied.  This is a comma-separated list.</li>
<li><strong><code>SloppyOptions=</code></strong>: A boolean that determines whether the mount will fail if there is an unrecognized mount option.</li>
<li><strong><code>DirectoryMode=</code></strong>: If parent directories need to be created for the mount point, this determines the permission mode of these directories.</li>
<li><strong><code>TimeoutSec=</code></strong>: Configures the amount of time the system will wait until the mount operation is marked as failed.</li>
</ul>

<h4 id="the-automount-section">The [Automount] Section</h4>

<p>This unit allows an associated <code>.mount</code> unit to be automatically mounted at boot.  As with the <code>.mount</code> unit, these units must be named after the translated mount point's path.</p>

<p>The <code>[Automount]</code> section is pretty simple, with only the following two options allowed:</p>

<ul>
<li><strong><code>Where=</code></strong>: The absolute path of the automount point on the filesystem.  This will match the filename except that it uses conventional path notation instead of the translation.</li>
<li><strong><code>DirectoryMode=</code></strong>:  If the automount point or any parent directories need to be created, this will determine the permissions settings of those path components.</li>
</ul>

<h4 id="the-swap-section">The [Swap] Section</h4>

<p>Swap units are used to configure swap space on the system.  The units must be named after the swap file or the swap device, using the same filesystem translation that was discussed above.</p>

<p>Like the mount options, the swap units can be automatically created from <code>/etc/fstab</code> entries, or can be configured through a dedicated unit file.</p>

<p>The <code>[Swap]</code> section of a unit file can contain the following directives for configuration:</p>

<ul>
<li><strong><code>What=</code></strong>: The absolute path to the location of the swap space, whether this is a file or a device.</li>
<li><strong><code>Priority=</code></strong>: This takes an integer that indicates the priority of the swap being configured.</li>
<li><strong><code>Options=</code></strong>: Any options that are typically set in the <code>/etc/fstab</code> file can be set with this directive instead.  A comma-separated list is used.</li>
<li><strong><code>TimeoutSec=</code></strong>: The amount of time that <code>systemd</code> waits for the swap to be activated before marking the operation as a failure.</li>
</ul>

<h4 id="the-path-section">The [Path] Section</h4>

<p>A path unit defines a filesystem path that <code>systmed</code> can monitor for changes.  Another unit must exist that will be be activated when certain activity is detected at the path location.  Path activity is determined thorugh <code>inotify</code> events.</p>

<p>The <code>[Path]</code> section of a unit file can contain the following directives:</p>

<ul>
<li><strong><code>PathExists=</code></strong>:  This directive is used to check whether the path in question exists.  If it does, the associated unit is activated.</li>
<li><strong><code>PathExistsGlob=</code></strong>:  This is the same as the above, but supports file glob expressions for determining path existence.</li>
<li><strong><code>PathChanged=</code></strong>: This watches the path location for changes.  The associated unit is activated if a change is detected when the watched file is closed.</li>
<li><strong><code>PathModified=</code></strong>: This watches for changes like the above directive, but it activates on file writes as well as when the file is closed.</li>
<li><strong><code>DirectoryNotEmpty=</code></strong>: This directive allows <code>systemd</code> to activate the associated unit when the directory is no longer empty.</li>
<li><strong><code>Unit=</code></strong>: This specifies the unit to activate when the path conditions specified above are met.  If this is omitted, <code>systemd</code> will look for a <code>.service</code> file that shares the same base unit name as this unit.</li>
<li><strong><code>MakeDirectory=</code></strong>: This determines if <code>systemd</code> will create the directory structure of the path in question prior to watching.</li>
<li><strong><code>DirectoryMode=</code></strong>: If the above is enabled, this will set the permission mode of any path components that must be created.</li>
</ul>

<h4 id="the-timer-section">The [Timer] Section</h4>

<p>Timer units are used to schedule tasks to operate at a specific time or after a certain delay.  This unit type replaces or supplements some of the functionality of the <code>cron</code> and <code>at</code> daemons.  An associated unit must be provided which will be activated when the timer is reached.</p>

<p>The <code>[Timer]</code> section of a unit file can contain some of the following directives:</p>

<ul>
<li><strong><code>OnActiveSec=</code></strong>: This directive allows the associated unit to be activated relative to the <code>.timer</code> unit's activation.</li>
<li><strong><code>OnBootSec=</code></strong>: This directive is used to specify the amount of time after the system is booted when the associated unit should be activated.</li>
<li><strong><code>OnStartupSec=</code></strong>:  This directive is similar to the above timer, but in relation to when the <code>systemd</code> process itself was started.</li>
<li><strong><code>OnUnitActiveSec=</code></strong>: This sets a timer according to when the associated unit was last activated.</li>
<li><strong><code>OnUnitInactiveSec=</code></strong>: This sets the timer in relation to when the associated unit was last marked as inactive.</li>
<li><strong><code>OnCalendar=</code></strong>:  This allows you to activate the associated unit by specifying an absolute instead of relative to an event.</li>
<li><strong><code>AccuracySec=</code></strong>: This unit is used to set the level of accuracy with which the timer should be adhered to.  By default, the associated unit will be activated within one minute of the timer being reached.  The value of this directive will determine the upper bounds on the window in which <code>systemd</code> schedules the activation to occur.</li>
<li><strong><code>Unit=</code></strong>: This directive is used to specify the unit that should be activated when the timer elapses.  If unset, <code>systemd</code> will look for a <code>.service</code> unit with a name that matches this unit.</li>
<li><strong><code>Persistent=</code></strong>:  If this is set, <code>systemd</code> will trigger the associated unit when the timer becomes active if it would have been triggered during the period in which the timer was inactive.</li>
<li><strong><code>WakeSystem=</code></strong>:  Setting this directive allows you to wake a system from suspend if the timer is reached when in that state.</li>
</ul>

<h4 id="the-slice-section">The [Slice] Section</h4>

<p>The <code>[Slice]</code> section of a unit file actually does not have any <code>.slice</code> unit specific configuration.  Instead, it can contain some resource management directives that are actually available to a number of the units listed above.</p>

<p>Some common directives in the <code>[Slice]</code> section, which may also be used in other units can be found in the <code>systemd.resource-control</code> man page.  These are valid in the following unit-specific sections:</p>

<ul>
<li><code>[Slice]</code></li>
<li><code>[Scope]</code></li>
<li><code>[Service]</code></li>
<li><code>[Socket]</code></li>
<li><code>[Mount]</code></li>
<li><code>[Swap]</code></li>
</ul>

<h2 id="creating-instance-units-from-template-unit-files">Creating Instance Units from Template Unit Files</h2>

<p>We mentioned earlier in this guide the idea of template unit files being used to create multiple instances of units.  In this section, we can go over this concept in more detail.</p>

<p>Template unit files are, in most ways, no different than regular unit files.  However, these provide flexibility in configuring units by allowing certain parts of the file to utilize dynamic information that will be available at runtime.</p>

<h3 id="template-and-instance-unit-names">Template and Instance Unit Names</h3>

<p>Template unit files can be identified because they contain an <code>@</code> symbol after the base unit name and before the unit type suffix.  A template unit file name may look like this:</p>
<pre class="code-pre "><code langs="">example@.service
</code></pre>
<p>When an instance is created from a template, an instance identifier is placed between the <code>@</code> symbol and the period signifying the start of the unit type.  For example, the above template unit file could be used to create an instance unit that looks like this:</p>
<pre class="code-pre "><code langs="">example@<span class="highlight">instance1</span>.service
</code></pre>
<p>An instance file is usually created as a symbolic link to the template file, with the link name including the instance identifier.  In this way, multiple links with unique identifiers can point back to a single template file.  When managing an instance unit, <code>systemd</code> will look for a file with the exact instance name you specify on the command line to use.  If it cannot find one, it will look for an associated template file.</p>

<h3 id="template-specifiers">Template Specifiers</h3>

<p>The power of template unit files is mainly seen through its ability to dynamically substitute appropriate information within the unit definition according to the operating environment.  This is done by setting the directives in the template file as normal, but replacing certain values or parts of values with variable specifiers.</p>

<p>The following are some of the more common specifiers will be replaced when an instance unit is interpreted with the relevant information:</p>

<ul>
<li><strong><code>%n</code></strong>: Anywhere where this appears in a template file, the full resulting unit name will be inserted.</li>
<li><strong><code>%N</code></strong>: This is the same as the above, but any escaping, such as those present in file path patterns, will be reversed.</li>
<li><strong><code>%p</code></strong>: This references the unit name prefix.  This is the portion of the unit name that comes before the <code>@</code> symbol.</li>
<li><strong><code>%P</code></strong>: This is the same as above, but with any escaping reversed.</li>
<li><strong><code>%i</code></strong>: This references the instance name, which is the identifier following the <code>@</code> in the instance unit.  This is one of the most commonly used specifiers because it will be guaranteed to be dynamic.  The use of this identifier encourages the use of configuration significant identifiers.  For example, the port that the service will be run at can be used as the instance identifier and the template can use this specifier to set up the port specification.</li>
<li><strong><code>%I</code></strong>: This specifier is the same as the above, but with any escaping reversed.</li>
<li><strong><code>%f</code></strong>: This will be replaced with the unescaped instance name or the prefix name, prepended with a <code>/</code>.</li>
<li><strong><code>%c</code></strong>: This will indicate the control group of the unit, with the standard parent hierarchy of <code>/sys/fs/cgroup/ssytemd/</code> removed.</li>
<li><strong><code>%u</code></strong>: The name of the user configured to run the unit.</li>
<li><strong><code>%U</code></strong>: The same as above, but as a numeric <code>UID</code> instead of name.</li>
<li><strong><code>%H</code></strong>: The host name of the system that is running the unit.</li>
<li><strong><code>%%</code></strong>: This is used to insert a literal percentage sign.</li>
</ul>

<p>By using the above identifiers in a template file, <code>systemd</code> will fill in the correct values when interpreting the template to create an instance unit.</p>

<h2 id="conclusion">Conclusion</h2>

<p>When working with <code>systemd</code>, understanding units and unit files can make administration simple.  Unlike many other init systems, you do not have to know a scripting language to interpret the init files used to boot services or the system.  The unit files use a fairly simple declarative syntax that allows you to see at a glance the purpose and effects of a unit upon activation.</p>

<p>Breaking functionality such as activation logic into separate units not only allows the internal <code>systemd</code> processes to optimize parallel initialization, it also keeps the configuration rather simple and allows you to modify and restart some units without tearing down and rebuilding their associated connections.  Leveraging these abilities can give you more flexibility and power during administration.</p>

    