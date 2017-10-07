<link rel='stylesheet' href='digital_ocean.css'><br> <br> 
      <h3 id="introduction">Introduction</h3>

<p>One of the major benefits of the CoreOS is the ability to manage services across an entire cluster from a single point.  The CoreOS platform provides integrated tools to make this process simple.</p>

<p>In this guide, we will demonstrate a typical work flow for getting services running on your CoreOS clusters.  This process will demonstrate some simple, practical ways of interacting with some of CoreOS's most interesting utilities in order to set up an application.</p>

<h2 id="prerequisites-and-goals">Prerequisites and Goals</h2>

<p>In order to get started with this guide, you should have a CoreOS cluster with a minimum of three machines configured.  You can follow our <a href="https://indiareads/community/tutorials/how-to-set-up-a-coreos-cluster-on-digitalocean">guide to bootstrapping a CoreOS cluster</a> here.</p>

<p>For the sake of this guide, our three nodes will be as follows:</p>

<ul>
<li>coreos-1</li>
<li>coreos-2</li>
<li>coreos-3</li>
</ul>

<p>These three nodes should be configured using their private network interface for their etcd client address and peer address, as well as the fleet address.  These should be configured using the cloud-config file as demonstrated in the guide above.</p>

<p>In this guide, we will be walking through the basic work flow of getting services running on a CoreOS cluster.  For demonstration purposes, we will be setting up a simple Apache web server.  We will cover setting up a containerized service environment with Docker and then we will create a systemd-style unit file to describe the service and its operational parameters.</p>

<p>Within a companion unit file, we will tell our service to register with etcd, which will allow other services to track its details.  We will submit both of our services to fleet, where we can start and manage the services on machines throughout our cluster.</p>

<h2 id="connect-to-a-node-and-pass-your-ssh-agent">Connect to a Node and Pass your SSH Agent</h2>

<p>The first thing we need to do to get started configuring services is connect to one of our nodes with SSH.</p>

<p>In order for the <code>fleetctl</code> tool to work, which we will be using to communicate with neighboring nodes, we need to pass in our SSH agent information while connecting. </p>

<p>Before you connect through SSH, you must start your SSH agent.  This will allow you to forward your credentials to the server you are connecting to, allowing you to log in from that machine to other nodes.  To start the user agent on your machine, you should type:</p>
<pre class="code-pre "><code class="code-highlight language-bash">eval $(ssh-agent)
</code></pre>
<p>You then can add your private key to the agent's in memory storage by typing:</p>
<pre class="code-pre "><code class="code-highlight language-bash">ssh-add
</code></pre>
<p>At this point, your SSH agent should be running and it should know about your private SSH key.  The next step is to connect to one of the nodes in your cluster and forward your SSH agent information.  You can do this by using the <code>-A</code> flag:</p>
<pre class="code-pre "><code langs="">ssh -A core@<span class="highlight">coreos_node_public_IP</span>
</code></pre>
<p>Once you are connected to one of your nodes, we can get started building out our service.</p>

<h2 id="creating-the-docker-container">Creating the Docker Container</h2>

<p>The first thing that we need to do is create a Docker container that will run our service.  You can do this in one of two ways. You can start up a Docker container and manually configure it, or you can create a Dockerfile that describes the steps necessary to build the image you want.</p>

<p>For this guide, we will build an image using the first method because it is more straight forward for those who are new to Docker.  Follow this link if you would like to find out more about how to <a href="https://indiareads/community/tutorials/docker-explained-using-dockerfiles-to-automate-building-of-images">build a Docker image from a Dockerfile</a>.  Our goal is to install Apache on an Ubuntu 14.04 base image within Docker.</p>

<p>Before you begin, you will need log in or sign up with the Docker Hub registry.  To do this, type:</p>
<pre class="code-pre "><code class="code-highlight language-bash">docker login
</code></pre>
<p>You will be asked to supply a username, password, and email address.  If this is your first time doing this, an account will be created using the details you provided and a confirmation email will be sent to the supplied address.  If you have already created an account in the past, you will be logged in with the given credentials.</p>

<p>To create the image, the first step is to start a Docker container with the base image we want to use.  The command that we will need is:</p>
<pre class="code-pre "><code langs="">docker run -i -t ubuntu:14.04 /bin/bash
</code></pre>
<p>The arguments that we used above are:</p>

<ul>
<li><strong>run</strong>: This tells Docker that we want to start up a container with the parameters that follow.</li>
<li><strong>-i</strong>: Start the Docker container in interactive mode.  This will ensure that STDIN to the container environment will be available, even if it is not attached.</li>
<li><strong>-t</strong>: This creates a pseudo-TTY, allowing us terminal access to the container environment.</li>
<li><strong>ubuntu:14.04</strong>: This is the repository and image combination that we want to run.  In this case, we are running Ubuntu 14.04.  The image is kept within the <a href="https://registry.hub.docker.com/_/ubuntu/">Ubuntu Docker repository at Docker Hub</a>.</li>
<li><strong>/bin/bash</strong>: This is the command that we want to run in the container.  Since we want terminal access, we need to spawn a shell session.</li>
</ul>

<p>The base image layers will be pulled down from the Docker Hub online Docker registry and a bash session will be started.  You will be dropped into the resulting shell session.</p>

<p>From here, we can go ahead with creating our service environment.  We want to install the Apache web server, so we should update our local package index and install through <code>apt</code>:</p>
<pre class="code-pre "><code class="code-highlight language-bash">apt-get update
apt-get install apache2
</code></pre>
<p>After the installation is complete, we can edit the default <code>index.html</code> file:</p>
<pre class="code-pre "><code class="code-highlight language-bash">echo "<h1>Running from Docker on CoreOS</h1>" > /var/www/html/index.html
</code></pre>
<p>When you are finished, you can exit your bash session in the conventional way:</p>
<pre class="code-pre "><code class="code-highlight language-bash">exit
</code></pre>
<p>Back on your host machine, we need to get the container ID of the Docker container we just left.  To do this, we can ask Docker to show the latest process information:</p>
<pre class="code-pre "><code class="code-highlight language-bash">docker ps -l
</code></pre><pre class="code-pre "><code class="code-highlight language-bash">CONTAINER ID        IMAGE               COMMAND             CREATED             STATUS                      PORTS               NAMES
cb58a2ea1f8f        ubuntu:14.04        "/bin/bash"         8 minutes ago       Exited (0) 55 seconds ago                       jovial_perlman
</code></pre>
<p>The column that we need is "CONTAINER ID".  In the example above, this would be <code>cb58a2ea1f8f</code>.  In order to be able to spin up the same container later on with all of the changes that you made, you need to commit the changes to your username's repository.  You will need to select a name for the image as well.</p>

<p>For our purposes, we will pretend that the username is <code>user_name</code> but you should substitute this with the Docker Hub account name you logged in with a bit ago.  We will call our image <code>apache</code>.  The command to commit the image changes is:</p>
<pre class="code-pre "><code langs="">docker commit <span class="highlight">container_ID</span> <span class="highlight">user_name</span>/apache
</code></pre>
<p>This saves the image so that you can recall the current state of the container.  You can verify this by typing:</p>
<pre class="code-pre "><code class="code-highlight language-bash">docker images
</code></pre><pre class="code-pre "><code class="code-highlight language-bash">REPOSITORY           TAG                 IMAGE ID            CREATED             VIRTUAL SIZE
user_name/apache     latest              42a71fb973da        4 seconds ago       247.4 MB
ubuntu               14.04               c4ff7513909d        3 weeks ago         213 MB
</code></pre>
<p>Next, you should publish the image to Docker Hub so that your nodes can pull down and run the image at will.  To do this, use the following command format:</p>
<pre class="code-pre "><code langs="">docker push <span class="highlight">user_name</span>/apache
</code></pre>
<p>You now have a container image configured with your Apache instance.</p>

<h2 id="creating-the-apache-service-unit-file">Creating the Apache Service Unit File</h2>

<p>Now that we have a Docker container available, we can begin building our service files.</p>

<p>Fleet manages the service scheduling for the entire CoreOS cluster.  It provides a centralized interface to the user, while manipulating each host's systemd init systems locally to complete the appropriate actions.</p>

<p>The files that define each service's properties are slightly modified systemd unit files.  If you have worked with systemd in the past, you will be very familiar with the syntax.</p>

<p>To start with, create a file called <code>apache@.service</code> in your home directory.  The <code>@</code> indicates that this is a template service file.  We will go over what that means in a bit.  The CoreOS image comes with the <code>vim</code> text editor:</p>
<pre class="code-pre "><code class="code-highlight language-bash">vim apache@.service
</code></pre>
<p>To start the service definition, we will create a <code>[Unit]</code> section header and set up some metadata about this unit.  We will include a description and specify dependency information.  Since our unit will need to be run after both etcd and Docker are available, we need to define that requirement.</p>

<p>We also need to add the other service file that we will be creating as a requirement.  This second service file will be responsible for updating etcd with information about our service.  Requiring it here will force it into starting when this service is started.  We will explain the <code>%i</code> in the service name later:</p>
<pre class="code-pre "><code langs="">[Unit]
Description=Apache web server service
After=etcd.service
After=docker.service
Requires=apache-discovery@%i.service
</code></pre>
<p>Next, we need to tell the system what needs to happen when starting or stopping this unit.  We do this in the <code>[Service]</code> section, since we are configuring a service.</p>

<p>The first thing we want to do is disable the service startup from timing out.  Because our services are Docker containers, the first time it is started on each host, the image will have to be pulled down from the Docker Hub servers, potentially causing a longer-than-usual start up time on the first run.</p>

<p>We want to set the <code>KillMode</code> to "none" so that systemd will allow our "stop" command to kill the Docker process.  If we leave this out, systemd will think that the Docker process failed when we call our stop command.</p>

<p>We will also want to make sure our environment is clean prior to starting our service.  This is especially important since we will be referencing our services by name and Docker only allows a single container to be running with each unique name.</p>

<p>We will need to kill any leftover containers with the name we want to use and then remove them.  It is at this point that we actually pull down the image from Docker Hub as well.  We want to source the <code>/etc/environment</code> file as well.  This includes variables, such as the public and private IP addresses of the host that is running the service:</p>
<pre class="code-pre "><code langs="">[Unit]
Description=Apache web server service
After=etcd.service
After=docker.service
Requires=apache-discovery@%i.service

[Service]
TimeoutStartSec=0
KillMode=none
EnvironmentFile=/etc/environment
ExecStartPre=-/usr/bin/docker kill apache%i
ExecStartPre=-/usr/bin/docker rm apache%i
ExecStartPre=/usr/bin/docker pull <span class="highlight">user_name</span>/apache
</code></pre>
<p>The <code>=-</code> syntax for the first two <code>ExecStartPre</code> lines indicate that those preparation lines can fail and the unit file will still continue.  Since those commands only succeed if a container with that name exists, they will fail if no container is found.</p>

<p>You may have noticed the <code>%i</code> suffix at the end of the apache container names in the above directives.  The service file we are creating is actually a <a href="https://github.com/coreos/fleet/blob/master/Documentation/unit-files-and-scheduling.md#template-unit-files">template unit file</a>.  This means that upon running the file, fleet will automatically substitute some information with the appropriate values.  Read the information at the provided link to find out more.</p>

<p>In our case, the <code>%i</code> will be replaced anywhere it exists within the file with the portion of the service file's name to the right of the <code>@</code> before the <code>.service</code> suffix.  Our file is simply named <code>apache@.service</code> though.</p>

<p>Although we will submit the file to <code>fleetctl</code> with <code>apache@.service</code>, when we load the file, we will load it as <code>apache@<span class="highlight">PORT_NUM</span>.service</code>, where "PORT_NUM" will be the port that we want to start this server on.  We will be labelling our service based on the port it will be running on so that we can easily differentiate them.</p>

<p>Next, we need to actually start the actual Docker container:</p>
<pre class="code-pre "><code langs="">[Unit]
Description=Apache web server service
After=etcd.service
After=docker.service
Requires=apache-discovery@%i.service

[Service]
TimeoutStartSec=0
KillMode=none
EnvironmentFile=/etc/environment
ExecStartPre=-/usr/bin/docker kill apache%i
ExecStartPre=-/usr/bin/docker rm apache%i
ExecStartPre=/usr/bin/docker pull <span class="highlight">user_name</span>/apache
ExecStart=/usr/bin/docker run --name apache%i -p ${COREOS_PUBLIC_IPV4}:%i:80 <span class="highlight">user_name</span>/apache /usr/sbin/apache2ctl -D FOREGROUND
</code></pre>
<p>We call the conventional <code>docker run</code> command and passed it some parameters.  We pass it the name in the same format we were using above.  We also are going to expose a port from our Docker container to our host machine's public interface.  The host machine's port number will be taken from the <code>%i</code> variable, which is what actually allows us to specify the port.</p>

<p>We will use the <code>COREOS_PUBLIC_IPV4</code> variable (taken from the environment file we sourced) to be explicit to the host interface we want to bind.  We could leave this out, but it sets us up for easy modification later if we want to change this to a private interface (if we are load balancing, for instance).</p>

<p>We reference the Docker container we uploaded to Docker Hub earlier.  Finally, we call the command that will start our Apache service in the container environment.  Since Docker containers shut down as soon as the command given to them exits, we want to run our service in the foreground instead of as a daemon.  This will allow our container to continue running instead of exiting as soon as it spawns a child process successfully.</p>

<p>Next, we need to specify the command to call when the service needs to be stopped.  We will simply stop the container.  The container cleanup is done when restarting each time.</p>

<p>We also want to add a section called <code>[X-Fleet]</code>.  This section is specifically designed to give instructions to fleet as to how to schedule the service.  Here, you can add restrictions so that your service must or must not run in certain arrangements in relation to other services or machine states.</p>

<p>We want our service to run only on hosts that are not already running an Apache web server, since this will give us an easy way to create highly available services.  We will use a wildcard to catch any of the apache service files that we might have running:</p>
<pre class="code-pre "><code class="code-highlight language-ini">[Unit]
Description=Apache web server service
After=etcd.service
After=docker.service
Requires=apache-discovery@%i.service

[Service]
TimeoutStartSec=0
KillMode=none
EnvironmentFile=/etc/environment
ExecStartPre=-/usr/bin/docker kill apache%i
ExecStartPre=-/usr/bin/docker rm apache%i
ExecStartPre=/usr/bin/docker pull <span class="highlight">user_name</span>/apache
ExecStart=/usr/bin/docker run --name apache%i -p ${COREOS_PUBLIC_IPV4}:%i:80 <span class="highlight">user_name</span>/apache /usr/sbin/apache2ctl -D FOREGROUND
ExecStop=/usr/bin/docker stop apache%i

[X-Fleet]
X-Conflicts=apache@*.service
</code></pre>
<p>With that, we are finished with our Apache server unit file.  We will now make a companion service file to register the service with etcd.</p>

<h2 id="registering-service-states-with-etcd">Registering Service States with Etcd</h2>

<p>In order to record the current state of the services started on the cluster, we will want to write some entries to etcd.  This is known as registering with etcd.</p>

<p>In order to do this, we will start up a minimal companion service that can update etcd as to when the server is available for traffic.</p>

<p>The new service file will be called <code>apache-discovery@.service</code>.  Open it now:</p>
<pre class="code-pre "><code class="code-highlight language-bash">vim apache-discovery@.service
</code></pre>
<p>We'll start off with the <code>[Unit]</code> section, just as we did before.  We will describe the purpose of the service and then we will set up a directive called <code>BindsTo</code>.</p>

<p>The <code>BindsTo</code> directive identifies a dependency that this service look to for state information.  If the listed service is stopped, the unit we are writing now will stop as well.  We will use this so that if our web server unit fails unexpectedly, this service will update etcd to reflect that information.  This solves potential issue of having stale information in etcd which could be erroneously used by other services:</p>
<pre class="code-pre "><code langs="">[Unit]
Description=Announce Apache@%i service
BindsTo=apache@%i.service
</code></pre>
<p>For the <code>[Service]</code> section, we want to again source the environment file with the host's IP address information.</p>

<p>For the actual start command, we want to run a simple infinite bash loop.  Within the loop, we will use the <code>etcdctl</code> command, which is used to modify etcd values, to set a key in the etcd store at <code>/announce/services/apache%i</code>.  The <code>%i</code> will be replaced with the section of the service name we will load between the <code>@</code> and the <code>.service</code> suffix, which again will be the port number of the Apache service.</p>

<p>The value of this key will be set to the node's public IP address and the port number.  We will also set an expiration time of 60 seconds on the value so that the key will be removed if the service somehow dies.  We will then sleep 45 seconds.  This will provide an overlap with the expiration so that we are always updating the TTL (time-to-live) value prior to it reaching its timeout.</p>

<p>For the stopping action, we will simply remove the key with the same <code>etcdctl</code> utility, marking the service as unavailable:</p>
<pre class="code-pre "><code langs="">[Unit]
Description=Announce Apache@%i service
BindsTo=apache@%i.service

[Service]
EnvironmentFile=/etc/environment
ExecStart=/bin/sh -c "while true; do etcdctl set /announce/services/apache%i ${COREOS_PUBLIC_IPV4}:%i --ttl 60; sleep 45; done"
ExecStop=/usr/bin/etcdctl rm /announce/services/apache%i
</code></pre>
<p>The last thing we need to do is add a condition to ensure that this service is started on the same host as the web server it is reporting on.  This will ensure that if the host goes down, that the etcd information will change appropriately:</p>
<pre class="code-pre "><code langs="">[Unit]
Description=Announce Apache@%i service
BindsTo=apache@%i.service

[Service]
EnvironmentFile=/etc/environment
ExecStart=/bin/sh -c "while true; do etcdctl set /announce/services/apache%i ${COREOS_PUBLIC_IPV4}:%i --ttl 60; sleep 45; done"
ExecStop=/usr/bin/etcdctl rm /announce/services/apache%i

[X-Fleet]
X-ConditionMachineOf=apache@%i.service
</code></pre>
<p>You now have your sidekick service that can record the current health status of your Apache server in etcd.</p>

<h2 id="working-with-unit-files-and-fleet">Working with Unit Files and Fleet</h2>

<p>You now have two service templates.  We can submit these directly into <code>fleetctl</code> so that our cluster knows about them:</p>
<pre class="code-pre "><code class="code-highlight language-bash">fleetctl submit apache@.service apache-discovery@.service
</code></pre>
<p>You should be able to see your new service files by typing:</p>
<pre class="code-pre "><code class="code-highlight language-bash">fleetctl list-unit-files
</code></pre><pre class="code-pre "><code langs="">UNIT                HASH    DSTATE      STATE       TMACHINE
apache-discovery@.service   26a893f inactive    inactive    -
apache@.service         72bcc95 inactive    inactive    -
</code></pre>
<p>The templates now exist in our cluster-wide init system.</p>

<p>Since we are using templates that depend on being scheduled on specific hosts, we need to load the files next.  This will allow us to specify the new name for these files with the port number.  This is when <code>fleetctl</code> looks at the <code>[X-Fleet]</code> section to see what the scheduling requirements are.</p>

<p>Since we are not doing any load balancing, we will just run our web server on port 80.  We can load each service by specifying that between the <code>@</code> and the <code>.service</code> suffix:</p>
<pre class="code-pre "><code class="code-highlight language-bash">fleetctl load apache@80.service
fleetctl load apache-discovery@80.service
</code></pre>
<p>You should get information about which host in your cluster the service is being loaded on:</p>
<pre class="code-pre "><code langs="">Unit apache@80.service loaded on 41f4cb9a.../10.132.248.119
Unit apache-discovery@80.service loaded on 41f4cb9a.../10.132.248.119
</code></pre>
<p>As you can see, these services have both been loaded on the same machine, which is what we specified.  Since our <code>apache-discovery</code> service file is bound to our Apache service, we can simply start the later to initiate both of our services:</p>
<pre class="code-pre "><code class="code-highlight language-bash">fleetctl start apache@80.service
</code></pre>
<p>Now, if you ask which units are running on our cluster, we should see the following:</p>
<pre class="code-pre "><code class="code-highlight language-bash">fleetctl list-units
</code></pre><pre class="code-pre "><code langs="">UNIT                MACHINE             ACTIVE  SUB
apache-discovery@80.service 41f4cb9a.../10.132.248.119  active  running
apache@80.service       41f4cb9a.../10.132.248.119  active  running
</code></pre>
<p>It appears that our web server is up and running.  In our service file, we told Docker to bind to the host server's public IP address, but the IP displayed with <code>fleetctl</code> is the private address (because we passed in <code>$private_ipv4</code> in the cloud-config when creating this example cluster).</p>

<p>However, we have registered the public IP address and the port number with etcd.  To get the value, you can use the <code>etcdctl</code> utility to query the values we have set.  If you recall, the keys we set were <code>/announce/services/apache<span class="highlight">PORT_NUM</span></code>.  So to get our server's details, type:</p>
<pre class="code-pre "><code class="code-highlight language-bash">etcdctl get /announce/services/apache80
</code></pre><pre class="code-pre "><code langs="">104.131.15.192:80
</code></pre>
<p>If we visit this page in our web browser, we should see the very simple page we created:</p>

<p><img src="https://assets.digitalocean.com/articles/coreos_basic/web_page.png" alt="CoreOS basic web page" /></p>

<p>Our service was deployed successfully.  Let's try to load up another instance using a different port.  We should expect that the web server and the associated sidekick container will be scheduled on the same host.  However, due to our constraint in our Apache service file, we should expect for this host to be <em>different</em> from the one serving our port 80 service.</p>

<p>Let's load up a service running on port 9999:</p>
<pre class="code-pre "><code class="code-highlight language-bash">fleetctl load apache@9999.service apache-discovery@9999.service
</code></pre><pre class="code-pre "><code langs="">Unit apache-discovery@9999.service loaded on 855f79e4.../10.132.248.120
Unit apache@9999.service loaded on 855f79e4.../10.132.248.120
</code></pre>
<p>We can see that both of the new services have been scheduled on the same new host.  Start the web server:</p>
<pre class="code-pre "><code class="code-highlight language-bash">fleetctl start apache@9999.service
</code></pre>
<p>Now, we can get the public IP address of this new host:</p>
<pre class="code-pre "><code class="code-highlight language-bash">etcdctl get /announce/services/apache9999
</code></pre><pre class="code-pre "><code langs="">104.131.15.193:9999
</code></pre>
<p>If we visit the specified address and port number, we should see another web server:</p>

<p><img src="https://assets.digitalocean.com/articles/coreos_basic/web_page.png" alt="CoreOS basic web page" /></p>

<p>We have now deployed two web servers within our cluster.</p>

<p>If you stop a web server, the sidekick container should stop as well:</p>
<pre class="code-pre "><code class="code-highlight language-bash">fleetctl stop apache@80.service
fleetctl list-units
</code></pre><pre class="code-pre "><code langs="">UNIT                MACHINE             ACTIVE      SUB
apache-discovery@80.service 41f4cb9a.../10.132.248.119  inactive    dead
apache-discovery@9999.service   855f79e4.../10.132.248.120  active  running
apache@80.service       41f4cb9a.../10.132.248.119  inactive    dead
apache@9999.service     855f79e4.../10.132.248.120  active  running
</code></pre>
<p>You can check that the etcd key was removed as well:</p>
<pre class="code-pre "><code class="code-highlight language-bash">etcdctl get /announce/services/apache80
</code></pre><pre class="code-pre "><code langs="">Error:  100: Key not found (/announce/services/apache80) [26693]
</code></pre>
<p>This seems to be working exactly as expected.</p>

<h2 id="conclusion">Conclusion</h2>

<p>By following along with this guide, you should now be familiar with some of the common ways of working with the CoreOS components.</p>

<p>We have created our own Docker container with the service we wanted to run installed inside and we have created a fleet unit file to tell CoreOS how to manage our container.  We have implemented a sidekick service to keep our etcd datastore up-to-date with state information about our web server.  We have managed our services with fleetctl, scheduling services on different hosts.</p>

<p>In later guides, we will continue to explore some of the areas we briefly touched upon in this article.</p>

    