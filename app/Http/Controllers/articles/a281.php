<link rel='stylesheet' href='digital_ocean.css'><br><img src=https://community-cdn-digitalocean-com.global.ssl.fastly.net/assets/tutorials/images/large/Flexible_Services-Twitter.png?1426699724/> <br> 
      <h3 id="introduction">Introduction</h3>

<p>CoreOS installations leverage a number of tools to make clustering and Docker contained services easy to manage.  While <code>etcd</code> is involved in linking up the separate nodes and providing an area for global data, most of the actual service management and administration tasks involve working with the <code>fleet</code> daemon.</p>

<p>In a <a href="https://indiareads/community/tutorials/how-to-use-fleet-and-fleetctl-to-manage-your-coreos-cluster">previous guide</a>, we went over the basic usage of the <code>fleetctl</code> command for manipulating the services and cluster members.  In that guide, we touched briefly on the unit files that fleet uses to define services, but these were simplified examples used to provide a working service to learn <code>fleetctl</code>.</p>

<p>In this guide, we will be exploring <code>fleet</code> unit files in depth to learn about how to create them and some techniques to make your services more robust in production.</p>

<h2 id="prerequisites">Prerequisites</h2>

<p>In order to complete this tutorial, we will assume that you have a CoreOS cluster configured as described in our <a href="https://indiareads/community/tutorials/how-to-set-up-a-coreos-cluster-on-digitalocean">clustering guide</a>.  This will leave you with three servers named as such:</p>

<ul>
<li>coreos-1</li>
<li>coreos-2</li>
<li>coreos-3</li>
</ul>

<p>Although most of this tutorial will focus on unit file creation, these machines will be used later to demonstrate the scheduling affects of certain directives.</p>

<p>We will also assume that you have read our guide on <a href="https://indiareads/community/tutorials/how-to-use-fleet-and-fleetctl-to-manage-your-coreos-cluster">how to use fleetctl</a>.  You should have a working knowledge of <code>fleetctl</code> so that you can submit and use these unit files with the cluster.</p>

<p>When you have completed these requirements, continue on with the rest of the guide.</p>

<h2 id="unit-file-sections-and-types">Unit File Sections and Types</h2>

<p>Because the service management aspect of <code>fleet</code> relies mainly on each local system's <code>systemd</code> init system, <code>systemd</code> unit files are used to define services.</p>

<p>While services are by far the most common unit type configured with CoreOS, there are actually other unit types that can be defined.  These are a subset of those available to conventional <code>systemd</code> unit files.  Each of these types are identified by the type being used as a file suffix, like <code>example.service</code>:</p>

<ul>
<li><strong>service</strong>: This is the most common type of unit file.  It is used to define a service or application that can be run on one of the machines in the cluster.</li>
<li><strong>socket</strong>: Defines details about a socket or socket-like files.  These include network sockets, IPC sockets, and FIFO buffers.  These are used to call services to start when traffic is seen on the file.</li>
<li><strong>device</strong>: Defines information about a device available in the udev device tree.  Systemd will create these as needed on individual hosts for kernel devices based on udev rules.  These are usually used for ordering issues to make sure devices are available before attempting to mount.</li>
<li><strong>mount</strong>: Defines information about a mount point for a device.  These are named after the mount points they reference, with slashes replaced by dashes.</li>
<li><strong>automount</strong>: Defines an automount point.  They follow the same naming convention as mount units and must be accompanied by the associated mount unit.  These are used to describe on demand and parallelized mounting.</li>
<li><strong>timer</strong>: Defines a timer associated with another unit.  When the point in time defined in this file is reached, the associated unit is started.</li>
<li><strong>path</strong>: Defines a path that can be monitored for path-based activation.  This can be used to start another unit when changes are made to a certain path.</li>
</ul>

<p>Although these options are all available, service units will be used most often.  In this guide, we will only be discussing service unit configurations.</p>

<p>Unit files are simple text files ending with a dot and one of the above suffixes.  Inside, they are organized by sections.  For <code>fleet</code>, most unit files will have the following general format:</p>
<pre class="code-pre "><code class="code-highlight language-ini">[Unit]
<span class="highlight">generic_unit_directive_1</span>
<span class="highlight">generic_unit_directive_2</span>

[Service]
<span class="highlight">service_specific_directive_1</span>
<span class="highlight">service_specific_directive_2</span>
<span class="highlight">service_specific_directive_3</span>

[X-Fleet]
<span class="highlight">fleet_specific_directive</span>
</code></pre>
<p>The section headers and everything else in a unit file is case-sensitive.  The <code>[Unit]</code> section is used to define generic information about a unit.  Options that are common for all unit-types are generally placed here.</p>

<p>The <code>[Service]</code> section is used to set directives that are specific to service units.  Most (but not all) of the unit-types above have associated sections for unit-type-specific information.  Check out the <a href="http://www.freedesktop.org/software/systemd/man/systemd.unit.html">generic systemd unit file man page</a> for links to the different unit types to see more information.</p>

<p>The <code>[X-Fleet]</code> section is used to set scheduling requirements for the unit for use with <code>fleet</code>.  Using this section, you can require that certain conditions be true in order for a unit to be scheduled on a host.</p>

<h2 id="building-the-main-service">Building the Main Service</h2>

<p>For this section, we will start with a variant of the unit file described in our <a href="https://indiareads/community/tutorials/how-to-create-and-run-a-service-on-a-coreos-cluster">basic guide on running services on CoreOS</a>.  The file is called <code>apache.1.service</code> and will look like this:</p>
<pre class="code-pre "><code class="code-highlight language-ini">[Unit]
Description=Apache web server service

# Requirements
Requires=etcd.service
Requires=docker.service
Requires=apache-discovery.1.service

# Dependency ordering
After=etcd.service
After=docker.service
Before=apache-discovery.1.service

[Service]
# Let processes take awhile to start up (for first run Docker containers)
TimeoutStartSec=0

# Change killmode from "control-group" to "none" to let Docker remove
# work correctly.
KillMode=none

# Get CoreOS environmental variables
EnvironmentFile=/etc/environment

# Pre-start and Start
## Directives with "=-" are allowed to fail without consequence
ExecStartPre=-/usr/bin/docker kill apache
ExecStartPre=-/usr/bin/docker rm apache
ExecStartPre=/usr/bin/docker pull <span class="highlight">username</span>/apache
ExecStart=/usr/bin/docker run --name apache -p ${COREOS_PUBLIC_IPV4}:80:80 \
<span class="highlight">username</span>/apache /usr/sbin/apache2ctl -D FOREGROUND

# Stop
ExecStop=/usr/bin/docker stop apache

[X-Fleet]
# Don't schedule on the same machine as other Apache instances
X-Conflicts=apache.*.service
</code></pre>
<p>We start with the <code>[Unit]</code> section.  Here, the basic idea is to describe the unit and lay down the dependency information.  We start with a set of requirements.  We have used hard requirements for this example.  If we wanted <code>fleet</code> to attempt to start additional services, but not stop on a failure, we could have used the <code>Wants</code> directive instead.</p>

<p>Afterwards, we explicitly list out what the ordering of the requirements should be.  This is important so that the prerequisite services are available when they are needed.  It is also the way that we automatically kick off the sidekick etcd announce service that we will be building.</p>

<p>For the <code>[Service]</code> section, we turn off the service startup timeout.  The first time a service is run on a host, the container will have to be pulled down from the Docker registry, which counts towards the startup timeout.  This is defaulted to 90 seconds, which will typically enough time, but with more complex containers, it can take longer.</p>

<p>We then set the killmode to none.  This is used because the normal kill mode (control-group) will sometimes cause container removal commands to fail (especially when attempted by Docker's <code>--rm</code> option).  This can cause issues on next restart.</p>

<p>We pull in the environment file so that we have access to the <code>COREOS_PUBLIC_IPV4</code> and, if private networking was enabled during creation, the <code>COREOS_PRIVATE_IPV4</code> environmental variables.  These are very useful for configuring Docker containers to use their specific host's information.</p>

<p>The <code>ExecStartPre</code> lines are used to tear down any leftover cruft from previous runs to make sure the execution environment is clean.  We use <code>=-</code> on the first two of these to indicate that <code>systemd</code> should ignore and continue if these commands fail.  Because of this, Docker will attempt to kill and remove previous containers, but will not worry if it cannot find any.  The last pre-start is used to ensure that the most up-to-date version of the container is being run.</p>

<p>The actual start command boots the Docker container and binds it to the host machine's public IPv4 interface. This uses the info in the environment file and makes it trivial to switch interfaces and ports.  The process is run in the foreground because the container will exit if the running process ends.  The stop command attempts to stop the container gracefully.</p>

<p>The <code>[X-Fleet]</code> section contains a simple condition that forces <code>fleet</code> to schedule the service on a machine that is not already running another Apache service.  This is an easy way to make a service highly available by forcing duplicate services to start on separate machines.</p>

<h3 id="basic-take-aways-for-building-main-services">Basic Take-Aways For Building Main Services</h3>

<p>In the above example, we went over a fairly basic configuration.  However, there are plenty of lessons that we can can from this to assist us in building services in general.</p>

<p>Some behavior to keep in mind when building a main service:</p>

<ul>
<li><strong>Separate logic for dependencies and ordering</strong>: Lay out your dependencies with <code>Requires=</code> or <code>Wants=</code> directives dependent on whether the unit you are building should fail if the dependency cannot be fulfilled.  Separate out the ordering with separate <code>After=</code> and <code>Before=</code> lines so that you can easily adjust if the requirements change.  Separating the dependency list from ordering can help you debug in case of dependency issues.</li>
<li><strong>Handle service registration with a separate process</strong>: Your service should be registered with <code>etcd</code> to take advantage of service discovery and the dynamic configuration features that this allows.  <em>However</em>, this should be handled by a separate "sidekick" container to keep the logic separate.  This will allow you to more accurately report on the service's health as seen from an outside perspective, which is what other components will need.</li>
<li><strong>Be aware of the possibility of your service timing out</strong>: Consider adjusting the <code>TimeoutStartSec</code> directive in order to allow for longer start times.  Setting this to "0" will disable a startup timeout.  This is often necessary because there are times when Docker has to pull an image (on first-run or when updates are found), which can add significant time to initializing the service.</li>
<li><strong>Adjust the KillMode if your service does not stop cleanly</strong>: Be aware of the <code>KillMode</code> option if your services or containers seem to be stopping uncleanly.  Setting this to "none" can sometimes resolve issues with your containers not being removed after stopping.  This is especially important when you name your containers since Docker will fail if a container with the same name has been left behind from a previous run.  Check out the <a href="http://www.freedesktop.org/software/systemd/man/systemd.kill.html">documentation on KillMode for more information</a></li>
<li><strong>Clean up the environment before starting up</strong>: Related to the above item, make sure to clean up previous Docker containers at each start.  You should not assume that the previous run of the service exited as expected.  These cleanup lines should use the <code>=-</code> specifier to allow them to fail silently if no cleanup is needed.  While you should stop containers with <code>docker stop</code> normally, you should probably use <code>docker kill</code> during cleanup.</li>
<li><strong>Pull in and use host-specific information for service portability</strong>: If you need to bind your service to a specific network interface, pull in the <code>/etc/environment</code> file to get access to <code>COREOS_PUBLIC_IPV4</code> and, if configured, <code>COREOS_PRIVATE_IPV4</code>.  If you need to know the hostname of the machine running your service, use the <code>%H</code> systemd specifier.  To learn more about possible specifiers, check out the <a href="http://www.freedesktop.org/software/systemd/man/systemd.unit.html#Specifiers">systemd specifiers docs</a>.  In the <code>[X-Fleet]</code> section, only the <code>%n</code>, <code>%N</code>, <code>%i</code>, and <code>%p</code> specifiers will work.</li>
</ul>

<h2 id="building-the-sidekick-announce-service">Building the Sidekick Announce Service</h2>

<p>Now that we have a good idea about what to keep in mind when building a main service, we can get started looking at a conventional "sidekick" service.  These sidekick services are associated with a main service and are used as an external point to register services with <code>etcd</code>.</p>

<p>This file, as it was referenced in the main unit file, is called <code>apache-discovery.1.service</code> and looks like this:</p>
<pre class="code-pre "><code class="code-highlight language-ini">[Unit]
Description=Apache web server etcd registration

# Requirements
Requires=etcd.service
Requires=apache.1.service

# Dependency ordering and binding
After=etcd.service
After=apache.1.service
BindsTo=apache.1.service

[Service]

# Get CoreOS environmental variables
EnvironmentFile=/etc/environment

# Start
## Test whether service is accessible and then register useful information
ExecStart=/bin/bash -c '\
  while true; do \
    curl -f ${COREOS_PUBLIC_IPV4}:80; \
    if [ $? -eq 0 ]; then \
      etcdctl set /services/apache/${COREOS_PUBLIC_IPV4} \'{"host": "%H", "ipv4_addr": ${COREOS_PUBLIC_IPV4}, "port": 80}\' --ttl 30; \
    else \
      etcdctl rm /services/apache/${COREOS_PUBLIC_IPV4}; \
    fi; \
    sleep 20; \
  done'

# Stop
ExecStop=/usr/bin/etcdctl rm /services/apache/${COREOS_PUBLIC_IPV4}

[X-Fleet]
# Schedule on the same machine as the associated Apache service
X-ConditionMachineOf=apache.1.service
</code></pre>
<p>We start the sidekick service off in much the same way as we did the main service.  We describe the purpose of the unit before moving on to dependency information and ordering logic.</p>

<p>The first new item here is the <code>BindsTo=</code> directive.  This directive causes this unit to follow the start, stop, and restart commands sent to the listed unit.  Basically, this means that we can manage both of these units by manipulating the main unit once both are loaded into <code>fleet</code>.  This is a one-way mechanism, so controlling the sidekick will not affect the main unit.</p>

<p>For the <code>[Service]</code> section, we again source the <code>/etc/environment</code> file because we need the variables it holds.  The <code>ExecStart=</code> directive in this instance is basically a short <code>bash</code> script.  It attempts to connect to the main services using the interface and port that was exposed.</p>

<p>If the connection is successful, the <code>etcdctl</code> command is used to set a key at the host machine's public IP address within <code>/services/apache</code> within <code>etcd</code>.  The value of this is a JSON object containing information about the service.  The key is set to expire in 30 seconds so if this unit goes down unexpectedly, stale service information won't be left in <code>etcd</code>.  If the connection fails, the key is removed immediately since the service cannot be verified to be available.</p>

<p>This loop includes a 20 second sleep command.  This means that every 20 seconds (before the 30 second <code>etcd</code> key timeout), this unit re-checks whether the main unit is available and resets the key.  This basically refreshes the TTL on the key so that it will be considered valid for another 30 seconds.</p>

<p>The stop command in this case just results in a manual removal of the key.  This will cause the service registration to be removed when the main unit's stop command is mirrored to this unit due to the <code>BindsTo=</code> directive.</p>

<p>For the <code>[X-Fleet]</code> section, we need to ensure that this unit is started on the same server as the main unit.  While this does not allow the unit to report on the availability of the service to remote machines, it is important for the <code>BindsTo=</code> directive to function correctly.</p>

<h3 id="basic-take-aways-for-building-sidekick-services">Basic Take-Aways For Building Sidekick Services</h3>

<p>In building this sidekick, we can see some things that we should keep in mind as a general rule for these types of units:</p>

<ul>
<li><strong>Check the actual availability of the main unit</strong>: It is important to actually check the state of main unit.  Do not assume that the main unit is available just because the sidekick has been initialized.  This is dependent on what the main unit's design and functionality is, but the more robust your check, the more credible your registration state will be.  The check can be anything that makes sense for the unit, from checking a <code>/health</code> endpoint to attempting to connect to a database with a client.</li>
<li><strong>Loop the registration logic to re-check regularly</strong>: Checking the availability of the service at start is important, but it is also essential that you recheck at regular intervals.  This can catch instances of unexpected service failures, especially if they somehow result in the container not stopping.  The pause between cycles will have to be tweaked according to your needs by weighing the importance of quick discovery against the additional load on your main unit.</li>
<li><strong>Use the TTL flag when registering with etcd for automatic de-registration on failures</strong>: Unexpected failures of the sidekick unit can result in stale discovery information in <code>etcd</code>.  To avoid conflicts between the registered and actual state of your services, you should let your keys time out.  With the looping construct above, you can refresh each key before the timeout interval to make sure that the key never actually expires while the sidekick is running.  The sleep interval in your loop should be set to slightly less than your timeout interval to ensure this functions correctly.</li>
<li><strong>Register useful information with etcd, not just a confirmation</strong>: During your first iteration of a sidekick, you may only be interested in accurately registering with <code>etcd</code> when the unit is started.  However, this is a missed opportunity to provide plenty of useful information for other services to utilize.  While you may not need this information now, it will become more useful as you build other components with the ability to read values from <code>etcd</code> for their own configurations.  The <code>etcd</code> service is a global key-value store, so don't forget to leverage this by providing key information.  Storing details in JSON objects is a good way to pass multiple pieces of information.</li>
</ul>

<p>By keeping these considerations in mind, you can begin to build robust registration units that will be able to intelligently ensure that <code>etcd</code> has the correct information.</p>

<h2 id="fleet-specific-considerations">Fleet-Specific Considerations</h2>

<p>While <code>fleet</code> unit files are for the most part no different from conventional <code>systemd</code> unit files, there are some additional capabilities and pitfalls.</p>

<p>The most obvious difference is the addition of a section called <code>[X-Fleet]</code> which can be used to direct <code>fleet</code> on how to make scheduling decisions.  The available options are:</p>

<ul>
<li><strong>X-ConditionMachineID</strong>: This can be used to specify an exact machine to load the unit.  The provided value is a complete machine ID.  This value can be retrieved from an individual member of the cluster by examining the <code>/etc/machine-id</code> file, or through <code>fleetctl</code> by issuing the <code>list-machines -l</code> command.  The entire ID string is required.  This may be needed if you are running a database with the data directory kept on a specific machine.  Unless you have a specific reason to use this, try to avoid it because it decreases the flexibility of the unit.</li>
<li><strong>X-ConditionMachineOf</strong>: This directive can be used to schedule this unit on the same machine that is loaded with the specified unit.  This is helpful for sidekick units or for lumping associated units together.</li>
<li><strong>X-Conflicts</strong>: This is the opposite of the above declaration, in that it specifies unit files which this unit <em>cannot</em> be scheduled alongside.  This is useful for easily configuring high availability by starting multiple versions of the same service, each on a different machine.</li>
<li><strong>X-ConditionMachineMetadata</strong>: This is used to specify scheduling requirements based on the metadata of the machines available.  In the "METADATA" column of the <code>fleetctl list-machines</code> output, you can see the metadata that has been set for each host.  To set metadata, pass it in your <code>cloud-config</code> file when initializing the server instance.</li>
<li><strong>Global</strong>: This is a special directive that takes a boolean argument indicating whether this should be scheduled on all machines in the cluster.  Only the metadata conditional can be used alongside this directive.</li>
</ul>

<p>These additional directives give an administrator greater flexibility and power in defining how services should be run on the machines available.  These are evaluated prior to passing them to a specific machine's <code>systemd</code> instance during the <code>fleetctl load</code> stage.</p>

<p>This brings us to the next thing to be aware of when working with related units in <code>fleet</code>.  The <code>fleetctl</code> utility does not evaluate dependency requirements outside of the <code>[X-Fleet]</code> section of the unit file.  This leads to some interesting problems when working with companion units in <code>fleet</code>.</p>

<p>This means that, while the <code>fleetctl</code> tool will take the necessary step to get the target unit into the desired state, stepping through the submission, loading, and starting process as needed based on the command given, it will not do this for the dependencies of the unit.</p>

<p>So, if you have both your main and sidekick unit submitted, but not loaded, in <code>fleet</code>, typing <code>fleetctl start main.service</code> will load and then attempt to start the <code>main.service</code> unit.  However, since the <code>sidekick.service</code> unit is not yet loaded, and because <code>fleetctl</code> will not evaluate the dependency information to bring the dependency units through the loading and starting process, the <code>main.service</code> unit will fail.  This is because once the machine's <code>systemd</code> instance processes the <code>main.service</code> unit, it will not be able to find the <code>sidekick.service</code> when <em>it</em> evaluates the dependencies.  The <code>sidekick.service</code> unit was never loaded on the machine.</p>

<p>To avoid this situation when dealing with companion units, you can start the services manually at the same time, not relying on the <code>BindsTo=</code> directive bringing the sidekick into a running state:</p>
<pre class="code-pre "><code class="code-highlight language-bash">fleetctl start main.service sidekick.service
</code></pre>
<p>Another option is to ensure that the sidekick unit is at least loaded when the main unit is run.  The loading stage is where a machine is selected and the unit file is submitted to local <code>systemd</code> instance.  This will ensure that dependencies are satisfied and that the <code>BindsTo=</code> directive will be able to execute correctly to bring up the second unit:</p>
<pre class="code-pre "><code class="code-highlight language-bash">fleetctl load main.service sidekick.service
fleetctl start main.service
</code></pre>
<p>Keep this in mind in the event that your related units are not responding correctly to your <code>fleetctl</code> commands.</p>

<h2 id="instances-and-templates">Instances and Templates</h2>

<p>One of the most powerful concepts when working with <code>fleet</code> is unit templates.</p>

<p>Unit templates rely on a feature of <code>systemd</code> called "instances".  These are instantiated units that are created at runtime by processing a template unit file.  The template file is for the most part very similar to a regular unit file, with a few small modifications.  However, these are extremely powerful when utilized correctly.</p>

<p>Template files can be identified by the <code>@</code> in their filename.  While a conventional service takes this form:</p>
<pre class="code-pre "><code class="code-highlight language-bash"><span class="highlight">unit</span>.service
</code></pre>
<p>A template file can look like this:</p>
<pre class="code-pre "><code class="code-highlight language-bash"><span class="highlight">unit</span>@.service
</code></pre>
<p>When a unit is instantiated from a template, its instance identifier is placed between the <code>@</code> and the <code>.service</code> suffix.  This identifier is a unique string selected by the administrator:</p>
<pre class="code-pre "><code class="code-highlight language-bash"><span class="highlight">unit</span>@<span class="highlight">instance_id</span>.service
</code></pre>
<p>The base unit name can be accessed from within the unit file by the <code>%p</code> specifier.  Similarly, the given instance identifier can be accessed with <code>%i</code>. </p>

<h3 id="main-unit-file-as-a-template">Main Unit File as a Template</h3>

<p>This means that instead of creating your main unit file called <code>apache.1.service</code> with the contents we saw earlier, you could create a template called <code>apache@.service</code> that looks like this:</p>
<pre class="code-pre "><code class="code-highlight language-ini">[Unit]
Description=Apache web server service on port %i

# Requirements
Requires=etcd.service
Requires=docker.service
Requires=apache-discovery@%i.service

# Dependency ordering
After=etcd.service
After=docker.service
Before=apache-discovery@%i.service

[Service]
# Let processes take awhile to start up (for first run Docker containers)
TimeoutStartSec=0

# Change killmode from "control-group" to "none" to let Docker remove
# work correctly.
KillMode=none

# Get CoreOS environmental variables
EnvironmentFile=/etc/environment

# Pre-start and Start
## Directives with "=-" are allowed to fail without consequence
ExecStartPre=-/usr/bin/docker kill apache.%i
ExecStartPre=-/usr/bin/docker rm apache.%i
ExecStartPre=/usr/bin/docker pull <span class="highlight">username</span>/apache
ExecStart=/usr/bin/docker run --name apache.%i -p ${COREOS_PUBLIC_IPV4}:%i:80 \
<span class="highlight">username</span>/apache /usr/sbin/apache2ctl -D FOREGROUND

# Stop
ExecStop=/usr/bin/docker stop apache.%i

[X-Fleet]
# Don't schedule on the same machine as other Apache instances
X-Conflicts=apache@*.service
</code></pre>
<p>As you can see, we have modified the <code>apache-discovery.1.service</code> dependency to be <code>apache-discovery@%i.service</code>.  This will mean that if we have an instance of this unit file called <code>apache@8888.service</code>, this will require a sidekick called <code>apache-discovery@8888.service</code>.  The <code>%i</code> has been replaced by the instance identifier.  In this case, we are using the identifier to hold dynamic information about the way our service is being run, specifically the port the Apache server will be available on.</p>

<p>To make this work, we are changing the <code>docker run</code> parameter that exposes the container's ports to a port on the host.  In the static unit file, the parameter we used was <code>${COREOS_PUBLIC_IPV4}:80:80</code>, which mapped port 80 of the container to port 80 of the host on the public IPv4 interface.  In this template file, we have replaced this with <code>${COREOS_PUBLIC_IPV4}:%i:80</code> since we are using the instance identifier to tell us what port to use.  A clever choice for the instance identifier can mean greater flexibility within your template file.</p>

<p>The Docker name itself has also been modified so that it also uses a unique container name based on the instance ID.  Keep in mind that Docker containers cannot use the <code>@</code> symbol, so we had choose a different name from the unit file.  We modify all of the directives that operate on the Docker container.</p>

<p>In the <code>[X-Fleet]</code> section, we've also modified the scheduling information to recognize these instantiated units instead of the static kind we were using before.</p>

<h3 id="sidekick-unit-as-a-template">Sidekick Unit as a Template</h3>

<p>We can run through a similar procedure to adapt the our sidekick unit for templating.</p>

<p>Our new sidekick unit will be called <code>apache-discovery@.service</code> and will look like this:</p>
<pre class="code-pre "><code class="code-highlight language-ini">[Unit]
Description=Apache web server on port %i etcd registration

# Requirements
Requires=etcd.service
Requires=apache@%i.service

# Dependency ordering and binding
After=etcd.service
After=apache@%i.service
BindsTo=apache@%i.service

[Service]

# Get CoreOS environmental variables
EnvironmentFile=/etc/environment

# Start
## Test whether service is accessible and then register useful information
ExecStart=/bin/bash -c '\
  while true; do \
    curl -f ${COREOS_PUBLIC_IPV4}:%i; \
    if [ $? -eq 0 ]; then \
      etcdctl set /services/apache/${COREOS_PUBLIC_IPV4} \'{"host": "%H", "ipv4_addr": ${COREOS_PUBLIC_IPV4}, "port": %i}\' --ttl 30; \
    else \
      etcdctl rm /services/apache/${COREOS_PUBLIC_IPV4}; \
    fi; \
    sleep 20; \
  done'

# Stop
ExecStop=/usr/bin/etcdctl rm /services/apache/${COREOS_PUBLIC_IPV4}

[X-Fleet]
# Schedule on the same machine as the associated Apache service
X-ConditionMachineOf=apache@%i.service
</code></pre>
<p>We have gone through the same steps of requiring and binding to the instantiated version of the main unit processes instead of the static version.  This will match the instantiated sidekick unit with the correct instantiated main unit.</p>

<p>During the <code>curl</code> command, when we are checking the actual availability of the service, we replace the static port 80 with the instant ID so that it is connecting to the correct place.  This is necessary since we changed the port exposure mapping within the Docker command for our main unit.</p>

<p>We also modify the "port" being logged to <code>etcd</code> so that it uses this same instance ID.  With this change, the JSON data being set in <code>etcd</code> is entirely dynamic.  It will pick up the hostname, the IP address, and the port where the service is being run.</p>

<p>Finally, we change the conditional in the <code>[X-Fleet]</code> section again.  We need to make sure this process is started on the same machine as the main unit instance.</p>

<h3 id="instantiating-units-from-templates">Instantiating Units from Templates</h3>

<p>To actually instantiate units from a template file, you have a few different options.</p>

<p>Both <code>fleet</code> and <code>systemd</code> can handle symbolic links, which gives us the option of creating links with the full instance IDs to the template files, like this:</p>
<pre class="code-pre "><code class="code-highlight language-bash">ln -s apache@.service apache@8888.service
ln -s apache-discovery@.service apache-discovery@8888.service
</code></pre>
<p>This will create two links, called <code>apache@8888.service</code> and <code>apache-discovery@8888.service</code>.  Each of these have all of the information needed for <code>fleet</code> and <code>systemd</code> to run these units now.  However, they are pointed back to the templates so that we can make any changes needed in a single place.</p>

<p>We can then submit, load, or start these services with <code>fleetctl</code> like this:</p>
<pre class="code-pre "><code class="code-highlight language-bash">fleetctl start apache@8888.service apache-discovery@8888.service
</code></pre>
<p>If you do not want to make symbolic links to define your instances, another option is to submit the templates themselves into <code>fleetctl</code>, like this:</p>
<pre class="code-pre "><code class="code-highlight language-bash">fleetctl submit apache@.service apache-discovery@.service
</code></pre>
<p>You can instantiate units from these templates right from within <code>fleetctl</code> by just assigning instance identifiers at runtime.  For instance, you could get the same service running by typing:</p>
<pre class="code-pre "><code class="code-highlight language-bash">fleetctl start apache@8888.service apache-discovery@8888.service
</code></pre>
<p>This eliminates the need for symbolic links.  Some administrators prefer the linking mechanism though since it means that you have the instance files available at any time.  It also allows you to pass in a directory to <code>fleetctl</code> so that everything is started at once.</p>

<p>For instance, in your working directory, you may have a subdirectory called <code>templates</code> for your template files and a subdirectory called <code>instances</code> for the instantiated linked versions.  You could even have one called <code>static</code> for non-templated units.  You could do this like so:</p>
<pre class="code-pre "><code class="code-highlight language-bash">mkdir templates instances static
</code></pre>
<p>You could then move your static files into <code>static</code> and your template files into <code>templates</code>:</p>
<pre class="code-pre "><code class="code-highlight language-bash">mv apache.1.service apache-discovery.1.service static
mv apache@.service apache-discovery@.service templates
</code></pre>
<p>From here, you can create the instance links you need.  Let's run our service on ports <code>5555</code>, <code>6666</code>, and <code>7777</code>:</p>
<pre class="code-pre "><code class="code-highlight language-bash">cd instances
ln -s ../templates/apache@.service apache@5555.service
ln -s ../templates/apache@.service apache@6666.service
ln -s ../templates/apache@.service apache@7777.service
ln -s ../templates/apache-discovery@.service apache-discovery@5555.service
ln -s ../templates/apache-discovery@.service apache-discovery@6666.service
ln -s ../templates/apache-discovery@.service apache-discovery@7777.service
</code></pre>
<p>You can then start all of your instances at once by typing something like:</p>
<pre class="code-pre "><code class="code-highlight language-bash">cd ..
fleetctl start instances/*
</code></pre>
<p>This can be incredibly useful starting up your services quickly.</p>

<h2 id="conclusion">Conclusion</h2>

<p>You should have a decent understanding of how to build unit files for <code>fleet</code> by this point.  By taking advantage of some of the dynamic features available within unit files, you can make sure your services are evenly distributed, close to their dependencies, and registering useful information with <code>etcd</code>.</p>

<p>In a <a href="https://indiareads/community/tutorials/how-to-use-confd-and-etcd-to-dynamically-reconfigure-services-in-coreos">later guide</a>, we will cover how to configure your containers to use the information you are registering with <code>etcd</code>.  This can assist your services in building up a working knowledge of your actual deployment environment in order to pass requests to the appropriate containers in the backend.</p>

    