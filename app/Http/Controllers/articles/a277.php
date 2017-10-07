<link rel='stylesheet' href='digital_ocean.css'><br><img src=https://community-cdn-digitalocean-com.global.ssl.fastly.net/assets/tutorials/images/large/Confd_and_Etcd-Twitter.png?1426699726/> <br> 
      <h3 id="introduction">Introduction</h3>

<p>CoreOS allows you to easily run services in Docker containers across a cluster of machines.  The procedure for doing so usually involves starting one or multiple instances of a service and then registering each instance with <code>etcd</code>, CoreOS's distributed key-value store.</p>

<p>By taking advantage of this pattern, related services can obtain valuable information about the state of the infrastructure and use this knowledge to inform their own behavior.  This makes it possible for services to dynamically configure themselves whenever significant <code>etcd</code> values change.</p>

<p>In this guide, we will discuss a tool called <code>confd</code>, which is specifically crafted to watch distributed key-value stores for changes.  It is run from within a Docker container and is used to trigger configuration modifications and service reloads.</p>

<h2 id="prerequisites-and-goals">Prerequisites and Goals</h2>

<p>In order to work through this guide, you should have a basic understanding of CoreOS and its component parts.  In previous guides, we set up a CoreOS cluster and became familiar with some of the tools that are used to manage your clusters.</p>

<p>Below are the guides that you should read before starting on this article.  We will be modifying the behavior of some of the services described in these guides, so while it is important to understand the material, you should start fresh when using this guide:</p>

<ul>
<li><a href="https://indiareads/community/tutorials/how-to-set-up-a-coreos-cluster-on-digitalocean">How To Set Up a CoreOS Cluster on IndiaReads</a></li>
<li><a href="https://indiareads/community/tutorials/how-to-create-and-run-a-service-on-a-coreos-cluster">How To Create and Run a Service on a CoreOS Cluster</a></li>
<li><a href="https://indiareads/community/tutorials/how-to-create-flexible-services-for-a-coreos-cluster-with-fleet-unit-files">How to Create Flexible Services for a CoreOS Cluster with Fleet Unit Files</a></li>
</ul>

<p>Additionally, to get more familiar with some of the management tools that we will be using, you want to go through these guides:</p>

<ul>
<li><a href="https://indiareads/community/tutorials/how-to-use-fleet-and-fleetctl-to-manage-your-coreos-cluster">How To Use Fleet and Fleetctl to Manage your CoreOS Cluster</a></li>
<li><a href="https://indiareads/community/tutorials/how-to-use-etcdctl-and-etcd-coreos-s-distributed-key-value-store">How To Use Etcdctl and Etcd, CoreOS's Distributed Key-Value Store</a></li>
</ul>

<p>The "How to Create Flexible Services" guide is especially important for this guide, as the templated main + sidekick services will serve as the basis for the front-end service we will be setting up in this guide.  As we stated earlier, although the above guides discuss the creation of Apache and sidekick services, there are some configuration changes for this guide that make it easier to start from scratch.  We will create modified versions of these services in this guide.</p>

<p>In this tutorial, we will focus on creating a new application container with Nginx.  This will serve as a reverse proxy to the various Apache instances that we can spawn from our template files.  The Nginx container will be configured with <code>confd</code> to watch the service registration that our sidekick services are responsible for.</p>

<p>We will start with the same three machine cluster that we have been using through this series.</p>

<ul>
<li>coreos-1</li>
<li>coreos-2</li>
<li>coreos-3</li>
</ul>

<p>When you have finished reading the preceding guides and have your three machine cluster available, continue on.</p>

<h2 id="configuring-the-backend-apache-services">Configuring the Backend Apache Services</h2>

<p>We will begin by setting up our backend Apache services.  This will mainly mirror the last part of the previous guide, but we will run through the entire procedure here due to some subtle differences.</p>

<p>Log into one of your CoreOS machines to get started:</p>
<pre class="code-pre "><code class="code-highlight language-bash">ssh -A core@<span class="highlight">ip_address</span>
</code></pre>
<h3 id="apache-container-setup">Apache Container Setup</h3>

<p>We will start by creating the basic Apache container.  This is actually identical to the last guide, so you do not have to do this again if you already have that image available in your Docker Hub account.  We'll base this container off of the Ubuntu 14.04 container image.</p>

<p>We can pull down the base image and start a container instance by typing:</p>
<pre class="code-pre "><code class="code-highlight language-bash">docker run -i -t ubuntu:14.04 /bin/bash
</code></pre>
<p>You will be dropped into a <code>bash</code> session once the container starts.  From here, we will update the local <code>apt</code> package index and install <code>apache2</code>:</p>
<pre class="code-pre "><code class="code-highlight language-bash">apt-get update
apt-get install apache2 -y
</code></pre>
<p>We will also set the default page:</p>
<pre class="code-pre "><code class="code-highlight language-bash">echo "<h1>Running from Docker on CoreOS</h1>" > /var/www/html/index.html
</code></pre>
<p>We can exit the container now since it is in the state we need:</p>
<pre class="code-pre "><code class="code-highlight language-bash">exit
</code></pre>
<p>Log into or create your account out Docker Hub by typing:</p>
<pre class="code-pre "><code class="code-highlight language-bash">docker login
</code></pre>
<p>You will have to give your username, password, and email address for your Docker Hub account.</p>

<p>Next, get the container ID of the instance you just left:</p>
<pre class="code-pre "><code class="code-highlight language-bash">docker ps -l
</code></pre><pre class="code-pre "><code langs="">CONTAINER ID        IMAGE               COMMAND             CREATED             STATUS                     PORTS               NAMES
<span class="highlight">1db0c9a40c0d</span>        ubuntu:14.04        "/bin/bash"         2 minutes ago       Exited (0) 4 seconds ago                       jolly_pare
</code></pre>
<p>The highlighted field above is the container ID.  Copy the output that you see on your own computer.</p>

<p>Now, commit using that container ID, your Docker Hub username, and a name for the image.  We'll use "apache" here:</p>
<pre class="code-pre "><code class="code-highlight language-bash">docker commit <span class="highlight">1db0c9a40c0d</span> <span class="highlight">user_name</span>/apache
</code></pre>
<p>Push your new image up to Docker Hub:</p>
<pre class="code-pre "><code class="code-highlight language-bash">docker push <span class="highlight">user_name</span>/apache
</code></pre>
<p>Now can use this image in your service files.</p>

<h3 id="creating-the-apache-service-template-unit-file">Creating the Apache Service Template Unit File</h3>

<p>Now that you have a container available, you can create a template unit file so that <code>fleet</code> and <code>systemd</code> can correctly manage the service.</p>

<p>Before we begin, let's set up a directory structure so that we can stay organized:</p>
<pre class="code-pre "><code class="code-highlight language-bash">cd ~
mkdir static templates instances
</code></pre>
<p>Now, we can make our template file within the <code>templates</code> directory:</p>
<pre class="code-pre "><code class="code-highlight language-bash">vim templates/apache@.service
</code></pre>
<p>Paste the following information into the file.  You can get details about each of the options we are using by following the previous guide on <a href="https://indiareads/community/tutorials/how-to-create-flexible-services-for-a-coreos-cluster-with-fleet-unit-files">creating flexible fleet unit files</a>:</p>
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
ExecStartPre=/usr/bin/docker pull <span class="highlight">user_name</span>/apache
ExecStart=/usr/bin/docker run --name apache.%i -p ${COREOS_PRIVATE_IPV4}:%i:80 \
<span class="highlight">user_name</span>/apache /usr/sbin/apache2ctl -D FOREGROUND

# Stop
ExecStop=/usr/bin/docker stop apache.%i

[X-Fleet]
# Don't schedule on the same machine as other Apache instances
Conflicts=apache@*.service
</code></pre>
<p>One modification we have made here is to use the private interface instead of the public interface.  Since all of our Apache instances will be passed traffic <em>through</em> the Nginx reverse proxy instead of handling connections from the open web, this is a good idea.  Remember, if you use the private interface on IndiaReads, the server that you spun up must have had the "private networking" flag selected upon creation.</p>

<p>Also, remember to change the <code><span class="highlight">user_name</span></code> to reference your Docker Hub username in order to pull down the Docker file correctly.</p>

<h3 id="creating-the-sidekick-template-unit-file">Creating the Sidekick Template Unit File</h3>

<p>Now, we will do the same for the sidekick service.  This one we will modify slightly in anticipation of the information we will need later.</p>

<p>Open the template file in your editor:</p>
<pre class="code-pre "><code class="code-highlight language-bash">vim templates/apache-discovery@.service
</code></pre>
<p>We will be using the following information in this file:</p>
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
    curl -f ${COREOS_PRIVATE_IPV4}:%i; \
    if [ $? -eq 0 ]; then \
      etcdctl set /services/apache/${COREOS_PRIVATE_IPV4} \'${COREOS_PRIVATE_IPV4}:%i\' --ttl 30; \
    else \
      etcdctl rm /services/apache/${COREOS_PRIVATE_IPV4}; \
    fi; \
    sleep 20; \
  done'

# Stop
ExecStop=/usr/bin/etcdctl rm /services/apache/${COREOS_PRIVATE_IPV4}

[X-Fleet]
# Schedule on the same machine as the associated Apache service
MachineOf=apache@%i.service
</code></pre>
<p>The above configuration is different in a few ways from the one in the previous guide.  We have adjusted the value set by the <code>etcdctl set</code> command.  Instead of passing a JSON object, we are setting a simple IP address + port combination.  This way, we can read this value directly to find the connection information necessary to get to this service.</p>

<p>We have also adjusted the information to specify the private interface as we did in our other file.  Leave this as public if you don't have this option available to you.</p>

<h3 id="instantiate-your-services">Instantiate your Services</h3>

<p>Now, let's create two instances of these services.</p>

<p>First, let's create the symbolic links.  Move to the <code>~/instances</code> directory you created and link to define the ports that they will be running on.  We want to run one service on port 7777, and another at port 8888:</p>
<pre class="code-pre "><code class="code-highlight language-bash">cd ~/instances
ln -s ../templates/apache@.service apache@7777.service
ln -s ../templates/apache@.service apache@8888.service
ln -s ../templates/apache-discovery@.service apache-discovery@7777.service
ln -s ../templates/apache-discovery@.service apache-discovery@8888.service
</code></pre>
<p>Now, we can start these services by passing the <code>~/instances</code> directory to <code>fleet</code>:</p>
<pre class="code-pre "><code class="code-highlight language-bash">fleetctl start ~/instances/*
</code></pre>
<p>After your instances start up (this could take a few minutes), you should be able to see the <code>etcd</code> entries that your sidekicks made:</p>
<pre class="code-pre "><code class="code-highlight language-bash">etcdctl ls --recursive /
</code></pre><pre class="code-pre "><code langs="">/coreos.com
/coreos.com/updateengine
/coreos.com/updateengine/rebootlock
/coreos.com/updateengine/rebootlock/semaphore
/services
/services/apache
<span class="highlight">/services/apache/10.132.249.206</span>
<span class="highlight">/services/apache/10.132.249.212</span>
</code></pre>
<p>If you ask for the value of one of these entries, you can see that you get an IP address and a port number:</p>
<pre class="code-pre "><code class="code-highlight language-bash">etcdctl get /services/apache/10.132.249.206
</code></pre><pre class="code-pre "><code langs="">10.132.249.206:8888
</code></pre>
<p>You can use <code>curl</code> to retrieve the page and make sure it's functioning correctly.  This will only work from within your machine if you configured the service to use private networking:</p>
<pre class="code-pre "><code class="code-highlight language-bash">curl 10.132.249.206:8888
</code></pre><pre class="code-pre "><code langs=""><h1>Running from Docker on CoreOS</h1>
</code></pre>
<p>We now have our backend infrastructure set up.  Our next step is to get familiar with <code>confd</code> so that we can watch the <code>/services/apache</code> location in <code>etcd</code> for changes and reconfigure Nginx each time.</p>

<h2 id="creating-the-nginx-container">Creating the Nginx Container</h2>

<p>We will start the Nginx container from the same Ubuntu 14.04 base that we used for the Apache services.</p>

<h3 id="installing-the-software">Installing the Software</h3>

<p>Start up a new container by typing:</p>
<pre class="code-pre "><code class="code-highlight language-bash">docker run -i -t ubuntu:14.04 /bin/bash
</code></pre>
<p>Update your local <code>apt</code> package cache and install Nginx.  We also need to install <code>curl</code> since the base image does not include this and we need it to get the stable <code>confd</code> package from GitHub momentarily:</p>
<pre class="code-pre "><code class="code-highlight language-bash">apt-get update
apt-get install nginx curl -y
</code></pre>
<p>Now, we can go to the <a href="https://github.com/kelseyhightower/confd/releases">releases page</a> for <code>confd</code> on GitHub in our browsers.  We need to find the link to the latest stable release.  At the time of this writing, that is <a href="https://github.com/kelseyhightower/confd/releases/tag/v0.5.0">v0.5.0</a>, but this may have changed.  Right-click on the link for the Linux version of the tool and select "copy link address" or whatever similar option is available.</p>

<p>Now, back in your Docker container, use the copied URL to download the application.  We will be putting this in the <code>/usr/local/bin</code> directory.  We need to choose <code>confd</code> as the output file:</p>
<pre class="code-pre "><code langs="">cd /usr/local/bin
curl -L https://github.com/kelseyhightower/confd/releases/download/<span class="highlight">v0.5.0/confd-</span>0.5.0<^>-linux-amd64 -o confd
</code></pre>
<p>Now, make the file executable so that we can use it within our container:</p>
<pre class="code-pre "><code class="code-highlight language-bash">chmod +x confd
</code></pre>
<p>We should also take this opportunity to create the configuration structure that <code>confd</code> expects.  This will be within the <code>/etc</code> directory:</p>
<pre class="code-pre "><code class="code-highlight language-bash">mkdir -p /etc/confd/{conf.d,templates}
</code></pre>
<h3 id="create-a-confd-configuration-file-to-read-etcd-values">Create a Confd Configuration File to Read Etcd Values</h3>

<p>Now that we have our applications installed, we should begin to configure <code>confd</code>.  We will start by creating a configuration file, or template resource file.</p>

<p>Configuration files in <code>confd</code> are used to set up the service to check certain <code>etcd</code> values and initiate actions when changes are detected.  These use the <a href="https://github.com/toml-lang/toml">TOML</a> file format, which is easy to use and fairly intuitive.</p>

<p>Begin by creating a file within within our configuration directory called <code>nginx.toml</code>:</p>
<pre class="code-pre "><code class="code-highlight language-bash">vi /etc/confd/conf.d/nginx.toml
</code></pre>
<p>We will build out our configuration file within here.  Add the following information:</p>
<pre class="code-pre "><code class="code-highlight language-ini">[template]

# The name of the template that will be used to render the application's configuration file
# Confd will look in `/etc/conf.d/templates` for these files by default
src = "nginx.tmpl"

# The location to place the rendered configuration file
dest = "/etc/nginx/sites-enabled/app.conf"

# The etcd keys or directory to watch.  This is where the information to fill in
# the template will come from.
keys = [ "/services/apache" ]

# File ownership and mode information
owner = "root"
mode = "0644"

# These are the commands that will be used to check whether the rendered config is
# valid and to reload the actual service once the new config is in place
check_cmd = "/usr/sbin/nginx -t"
reload_cmd = "/usr/sbin/service nginx reload"
</code></pre>
<p>The above file has comments explaining some of the basic ideas, but we can go over the options you have below:</p>

<table class="pure-table"><thead>
<tr>
<th>Directive</th>
<th>Required?</th>
<th>Type</th>
<th>Description</th>
</tr>
</thead><tbody>
<tr>
<td>src</td>
<td>Yes</td>
<td>String</td>
<td>The name of the template that will be used to render the information.  If this is located outside of <code>/etc/confd/templates</code>, the entire path is should be used.</td>
</tr>
<tr>
<td>dest</td>
<td>Yes</td>
<td>String</td>
<td>The file location where the rendered configuration file should be placed.</td>
</tr>
<tr>
<td>keys</td>
<td>Yes</td>
<td>Array of strings</td>
<td>The <code>etcd</code> keys that the template requires to be rendered correctly. This can be a directory if the template is set up to handle child keys.</td>
</tr>
<tr>
<td>owner</td>
<td>No</td>
<td>String</td>
<td>The username that will be given ownership of the rendered configuration file.</td>
</tr>
<tr>
<td>group</td>
<td>No</td>
<td>String</td>
<td>The group that will be given group ownership of the rendered configuration file.</td>
</tr>
<tr>
<td>mode</td>
<td>No</td>
<td>String</td>
<td>The octal permissions mode that should be set for the rendered file.</td>
</tr>
<tr>
<td>check_cmd</td>
<td>No</td>
<td>String</td>
<td>The command that should be used to check the syntax of the rendered configuration file.</td>
</tr>
<tr>
<td>reload_cmd</td>
<td>No</td>
<td>String</td>
<td>The command that should be used to reload the configuration of the application.</td>
</tr>
<tr>
<td>prefix</td>
<td>No</td>
<td>String</td>
<td>A part of the <code>etcd</code> hierarchy that comes before the keys in the <code>keys</code> directive.  This can be used to make the <code>.toml</code> file more flexible.</td>
</tr>
</tbody></table>

<p>The file that we created tells us a few important things about how our <code>confd</code> instance will function.  Our Nginx container will use a template stored at <code>/etc/confd/templates/nginx.conf.tmpl</code> to render a configuration file that will be placed at <code>/etc/nginx/sites-enabled/app.conf</code>.  The file will be given a permission set of <code>0644</code> and ownership will be given to the root user.</p>

<p>The <code>confd</code> application will look for changes at the <code>/services/apache</code> node.  When a change is seen, <code>confd</code> will query for the new information under that node.  It will then render a new configuration for Nginx.  It will check the configuration file for syntax errors and reload the Nginx service after the file is in place.</p>

<p>We now have our template resource file created.  We should work on the actual template file that will be used to render our Nginx configuration file.</p>

<h3 id="create-a-confd-template-file">Create a Confd Template File</h3>

<p>For our template file, we will use an example from the <code>confd</code> project's <a href="https://github.com/kelseyhightower/confd/blob/master/docs/templates-interation-example.md">GitHub documentation</a> to get us started.</p>

<p>Create the file that we referenced in our configuration file above.  Put this file in our <code>templates</code> directory:</p>
<pre class="code-pre "><code class="code-highlight language-bash">vi /etc/confd/templates/nginx.tmpl
</code></pre>
<p>In this file, we basically just re-create a standard Nginx reverse proxy configuration file.  However we will be using some Go templating syntax to substitute some of the information that <code>confd</code> is pulling from <code>etcd</code>.</p>

<p>First, we configure the block with the "upstream" servers.  This section is used to define the pool of servers that Nginx can send requests to.  The format is generally like this:</p>
<pre class="code-pre "><code class="code-highlight language-nginx">upstream <span class="highlight">pool_name</span> {
    server <span class="highlight">server_1_IP</span>:<span class="highlight">port_num</span>;
    server <span class="highlight">server_2_IP</span>:<span class="highlight">port_num</span>;
    server <span class="highlight">server_3_IP</span>:<span class="highlight">port_num</span>;
}
</code></pre>
<p>This allows us to pass requests to the <code><span class="highlight">pool_name</span></code> and Nginx will select one of the defined servers to hand the request to.</p>

<p>The idea behind our template file is to parse <code>etcd</code> for the IP addresses and port numbers of our Apache web servers.  So instead of statically defining our upstream servers, we should dynamically fill this information in when the file is rendered.  We can do this by using <a href="http://golang.org/pkg/text/template/">Go templates</a> for the dynamic content.</p>

<p>To do this, we will instead use this as our block:</p>
<pre class="code-pre "><code class="code-highlight language-nginx">upstream apache_pool {
{{ range getvs "/services/apache/*" }}
    server {{ . }};
{{ end }}
}
</code></pre>
<p>Let's explain for a moment what's going on.  We have opened a block to define an upstream pool of servers called <code>apache_pool</code>.  Inside, we specify that we are beginning some Go language code by using the double brackets.</p>

<p>Within these brackets, we specify the <code>etcd</code> endpoint where the values we are interested in are held.  We are using a <code>range</code> to make the list iterable.</p>

<p>We use this to pass all of the entries retrieved from below the <code>/services/apache</code> location in <code>etcd</code> into the <code>range</code> block.  We can then get the value of the key in the current iteration using a single dot within the "{{" and "}}" that indicate an inserted value.  We use this within the range loop to populate the server pool.  Finally, we end the loop with the <code>{{ end }}</code> directive.</p>

<p><strong>Note</strong>: Remember to add the semicolon after the <code>server</code> directive within the loop.  Forgetting this will result in a non-working configuration.</p>

<p>After setting up the server pool, we can just use a proxy pass to direct all connections into that pool.  This will just be a standard server block as a reverse proxy.  The one thing to note is the <code>access_log</code>, which uses a custom format that we will be creating momentarily:</p>
<pre class="code-pre "><code class="code-highlight language-nginx">upstream apache_pool {
{{ range getvs "/services/apache/*" }}
    server {{ . }};
{{ end }}
}

server {
    listen 80 default_server;
    listen [::]:80 default_server ipv6only=on;

    access_log /var/log/nginx/access.log upstreamlog;

    location / {
        proxy_pass http://apache_pool;
        proxy_redirect off;
        proxy_set_header Host $host;
        proxy_set_header X-Real-IP $remote_addr;
        proxy_set_header X-Forwarded-For $proxy_add_x_forwarded_for;
    }
}
</code></pre>
<p>This will respond to all connections on port 80 and pass them to the pool of servers at <code>apache_pool</code> that is generated by looking at the <code>etcd</code> entries.</p>

<p>While we are dealing with this aspect of the service, we should remove the default Nginx configuration file so that we do not run into conflicts later on.  We will just remove the symbolic link enabling the default config:</p>
<pre class="code-pre "><code class="code-highlight language-bash">rm /etc/nginx/sites-enabled/default
</code></pre>
<p>Now is also a good time to configure the log format that we referenced in our template file.  This must go in the <code>http</code> block of the configuration, which is available in the main configuration file.  Open that now:</p>
<pre class="code-pre "><code class="code-highlight language-bash">vi /etc/nginx/nginx.conf
</code></pre>
<p>We will add a <code>log_format</code> directive to define the information we want to log.  It will log the client that is visiting, as well as the backend server that the request is passed to.  We will log some data about the amount of time these procedures take:</p>
<pre class="code-pre "><code class="code-highlight language-nginx">. . .
http {
    ##
    # Basic Settings
    ##
    log_format upstreamlog '[$time_local] $remote_addr passed to: $upstream_addr: $request Upstream Response Time: $upstream_response_time Request time: $request_time';

    sendfile on;
    . . .
</code></pre>
<p>Save and close the file when you are finished.</p>

<h3 id="creating-a-script-to-run-confd">Creating a Script to Run Confd</h3>

<p>We need to create a script file that will call <code>confd</code> with our template resource file and our template file at the appropriate times.</p>

<p>The script must do two things for our service to work correctly:</p>

<ul>
<li>It must run when the container launches to set up the initial Nginx settings based on the current state of the backend infrastructure.</li>
<li>It must continue to watch for changes to the <code>etcd</code> registration for the Apache servers so that it can reconfigure Nginx based on the backend servers available.</li>
</ul>

<p>We will get our script from <a href="https://github.com/marceldegraaf/blog-coreos-1/blob/master/nginx/boot.sh">Marcel de Graaf's GitHub page</a>.  This is a nice, simple script that does <em>exactly</em> what we need.  We will only make a few minor edits for our scenario.</p>

<p>Let's place this script alongside our <code>confd</code> executable.  We will call this <code>confd-watch</code>:</p>
<pre class="code-pre "><code langs="">vi /usr/local/bin/confd-watch
</code></pre>
<p>We will start off with the conventional <code>bash</code> header to identify the interpreter we need.  We then will set some <code>bash</code> options so that the script fails immediately if anything goes wrong.  It will return the value of the last command to fail or run.</p>
<pre class="code-pre "><code class="code-highlight language-bash">#!/bin/bash

set -eo pipefail
</code></pre>
<p>Next, we want to set up some variables.  By using <code>bash</code> parameter substitution, we will set default values, but build in some flexibility to let us override the hard-coded values when calling the script.  This will basically just set up each component of the connection address independently and then group them together to get the full address needed.</p>

<p>The parameter substitution is created with this syntax: <code>${<span class="highlight">var_name</span>:-<span class="highlight">default_value</span>}</code>.  This has the property of using the value of <code>var_name</code> if it is given and not null, otherwise defaulting to the <code>default_value</code>.</p>

<p>We are defaulting to the values that <code>etcd</code> expects by default.  This will allow our script to function well without additional information, but we can customize as necessary when calling the script:</p>
<pre class="code-pre "><code class="code-highlight language-bash">#!/bin/bash

set -eo pipefail

export ETCD_PORT=${ETCD_PORT:-4001}
export HOST_IP=${HOST_IP:-172.17.42.1}
export ETCD=$HOST_IP:$ETCD_PORT
</code></pre>
<p>We will now use <code>confd</code> to render an initial version of the Nginx configuration file by reading the values from <code>etcd</code> that are available when this script is called.  We will use an <code>until</code> loop to continuously try to build the initial configuration.</p>

<p>The looping construct can be necessary in case <code>etcd</code> is not available right away or in the event that the Nginx container is brought online before the backend servers.  This allows it to poll <code>etcd</code> repeatedly until it can finally produce a valid initial configuration.</p>

<p>The actual <code>confd</code> command we are calling executes once and then exits.  This is so we can wait 5 seconds until the next run to give our backend servers a chance to register.  We connect to the full <code>ETCD</code> variable that we built using the defaults or passed in parameters, and we use the template resources file to define the behavior of what we want to do:</p>
<pre class="code-pre "><code class="code-highlight language-bash">#!/bin/bash

set -eo pipefail

export ETCD_PORT=${ETCD_PORT:-4001}
export HOST_IP=${HOST_IP:-172.17.42.1}
export ETCD=$HOST_IP:$ETCD_PORT

echo "[nginx] booting container. ETCD: $ETCD"

# Try to make initial configuration every 5 seconds until successful
until confd -onetime -node $ETCD -config-file /etc/confd/conf.d/nginx.toml; do
    echo "[nginx] waiting for confd to create initial nginx configuration"
    sleep 5
done
</code></pre>
<p>After the initial configuration has been set, the next task of our script should be to put into place a mechanism for continual polling.  We want to make sure any future changes are detected so that Nginx will be updated.</p>

<p>To do this, we can call <code>confd</code> once more.  This time, we want to set a continuous polling interval and place the process in the background so that it will run indefinitely.  We will pass in the same <code>etcd</code> connection information and the same template resources file since our goal is still the same.</p>

<p>After putting the <code>confd</code> process into the background, we can safely start Nginx using the configuration file that was made.  Since this script will be called as our Docker "run" command, we need to keep it running in the foreground so that the container doesn't exit at this point.  We can do this by just tailing the logs, giving us access to all of the information we have been logging:</p>
<pre class="code-pre "><code class="code-highlight language-bash">#!/bin/bash

set -eo pipefail

export ETCD_PORT=${ETCD_PORT:-4001}
export HOST_IP=${HOST_IP:-172.17.42.1}
export ETCD=$HOST_IP:$ETCD_PORT

echo "[nginx] booting container. ETCD: $ETCD."

# Try to make initial configuration every 5 seconds until successful
until confd -onetime -node $ETCD -config-file /etc/confd/conf.d/nginx.toml; do
    echo "[nginx] waiting for confd to create initial nginx configuration."
    sleep 5
done

# Put a continual polling `confd` process into the background to watch
# for changes every 10 seconds
confd -interval 10 -node $ETCD -config-file /etc/confd/conf.d/nginx.toml &
echo "[nginx] confd is now monitoring etcd for changes..."

# Start the Nginx service using the generated config
echo "[nginx] starting nginx service..."
service nginx start

# Follow the logs to allow the script to continue running
tail -f /var/log/nginx/*.log
</code></pre>
<p>When you are finished with this, save and close the file.</p>

<p>The last thing we need to do is make the script executable:</p>
<pre class="code-pre "><code class="code-highlight language-bash">chmod +x /usr/local/bin/confd-watch
</code></pre>
<p>Exit the container now to get back to the host system:</p>
<pre class="code-pre "><code class="code-highlight language-bash">exit
</code></pre>
<h3 id="commit-and-push-the-container">Commit and Push the Container</h3>

<p>Now, we can commit the container and push it up to Docker Hub so that it is available to our machines to pull down.</p>

<p>Find out the container ID:</p>
<pre class="code-pre "><code class="code-highlight language-bash">docker ps -l
</code></pre><pre class="code-pre "><code langs="">CONTAINER ID        IMAGE               COMMAND             CREATED             STATUS                          PORTS               NAMES
<span class="highlight">de4f30617499</span>        ubuntu:14.04        "/bin/bash"         22 hours ago        Exited (0) About a minute ago                       stupefied_albattani
</code></pre>
<p>The highlighted string is the container ID we need.  Commit the container using this ID along with your Docker Hub username and the name you would like to use for this image.  We are going to use the name "nginx_lb" in this guide:</p>
<pre class="code-pre "><code class="code-highlight language-bash">docker commit <span class="highlight">de4f30617499</span> <span class="highlight">user_name</span>/nginx_lb
</code></pre>
<p>Log in to your Docker Hub account if necessary:</p>
<pre class="code-pre "><code class="code-highlight language-bash">docker login
</code></pre>
<p>Now, you should push up your committed image so that your other hosts can pull it down as necessary:</p>
<pre class="code-pre "><code class="code-highlight language-bash">docker push <span class="highlight">user_name</span>/nginx_lb
</code></pre>
<h2 id="build-the-nginx-static-unit-file">Build the Nginx Static Unit File</h2>

<p>The next step is to build a unit file that will start up the container we just created.  This will let us use <code>fleet</code> to control the process.</p>

<p>Since this is not going to be a template, we will put it into the <code>~/static</code> directory we created at the beginning of this directory:</p>
<pre class="code-pre "><code class="code-highlight language-bash">vim static/nginx_lb.service
</code></pre>
<p>We will start off with the standard <code>[Unit]</code> section to describe the service and define the dependencies and ordering:</p>
<pre class="code-pre "><code class="code-highlight language-ini">[Unit]
Description=Nginx load balancer for web server backends

# Requirements
Requires=etcd.service
Requires=docker.service

# Dependency ordering
After=etcd.service
After=docker.service
</code></pre>
<p>Next, we need to define the <code>[Service]</code> portion of the file.  We will set the timeout to zero and adjust the killmode to none again, just as we did with the Apache service files. We will pull in the environment file again so that we can get access to the public and private IP addresses of the host this container is running on.</p>

<p>We will then clean up our environment to make sure any previous versions of this container are killed and removed.  We pull down the container we just created to make sure we always have the most recent version. </p>

<p>Finally, we will start the container.  This involves starting the container, giving it the name we referenced in the remove and kill commands, and passing it the public IP address of the host it is running on to map port 80.  We call the <code>confd-watch</code> script we wrote as the run command.</p>
<pre class="code-pre "><code class="code-highlight language-ini">[Unit]
Description=Nginx load balancer for web server backends

# Requirements
Requires=etcd.service
Requires=docker.service

# Dependency ordering
After=etcd.service
After=docker.service

[Service]
# Let the process take awhile to start up (for first run Docker containers)
TimeoutStartSec=0

# Change killmode from "control-group" to "none" to let Docker remove
# work correctly.
KillMode=none

# Get CoreOS environmental variables
EnvironmentFile=/etc/environment

# Pre-start and Start
## Directives with "=-" are allowed to fail without consequence
ExecStartPre=-/usr/bin/docker kill nginx_lb
ExecStartPre=-/usr/bin/docker rm nginx_lb
ExecStartPre=/usr/bin/docker pull <span class="highlight">user_name</span>/nginx_lb
ExecStart=/usr/bin/docker run --name nginx_lb -p ${COREOS_PUBLIC_IPV4}:80:80 \
<span class="highlight">user_name</span>/nginx_lb /usr/local/bin/confd-watch
</code></pre>
<p>Now, all we need to sort out is the stopping command and the <code>fleet</code> scheduling directions.  We want this container to be initiated only on hosts that are not running other load balancing instances or backend Apache servers.  This will allow our service to spread the load effectively:</p>
<pre class="code-pre "><code class="code-highlight language-ini">[Unit]
Description=Nginx load balancer for web server backends

# Requirements
Requires=etcd.service
Requires=docker.service

# Dependency ordering
After=etcd.service
After=docker.service

[Service]
# Let the process take awhile to start up (for first run Docker containers)
TimeoutStartSec=0

# Change killmode from "control-group" to "none" to let Docker remove
# work correctly.
KillMode=none

# Get CoreOS environmental variables
EnvironmentFile=/etc/environment

# Pre-start and Start
## Directives with "=-" are allowed to fail without consequence
ExecStartPre=-/usr/bin/docker kill nginx_lb
ExecStartPre=-/usr/bin/docker rm nginx_lb
ExecStartPre=/usr/bin/docker pull <span class="highlight">user_name</span>/nginx_lb
ExecStart=/usr/bin/docker run --name nginx_lb -p ${COREOS_PUBLIC_IPV4}:80:80 \
<span class="highlight">user_name</span>/nginx_lb /usr/local/bin/confd-watch

# Stop
ExecStop=/usr/bin/docker stop nginx_lb

[X-Fleet]
Conflicts=nginx.service
Conflicts=apache@*.service
</code></pre>
<p>Save and close the file when you are finished.</p>

<h2 id="running-the-nginx-load-balancer">Running the Nginx Load Balancer</h2>

<p>You should already have two Apache instances running from earlier in the tutorial.  You can check by typing:</p>
<pre class="code-pre "><code class="code-highlight language-bash">fleetctl list-units
</code></pre><pre class="code-pre "><code langs="">UNIT                MACHINE             ACTIVE  SUB
apache-discovery@7777.service   197a1662.../10.132.249.206  active  running
apache-discovery@8888.service   04856ec4.../10.132.249.212  active  running
apache@7777.service     197a1662.../10.132.249.206  active  running
apache@8888.service     04856ec4.../10.132.249.212  active  running
</code></pre>
<p>You can also double check that they are correctly registering themselves with <code>etcd</code> by typing:</p>
<pre class="code-pre "><code class="code-highlight language-bash">etcdctl ls --recursive /services/apache
</code></pre><pre class="code-pre "><code langs="">/services/apache/10.132.249.206
/services/apache/10.132.249.212
</code></pre>
<p>We can now attempt to start up our Nginx service:</p>
<pre class="code-pre "><code class="code-highlight language-bash">fleetctl start ~/static/nginx_lb.service
</code></pre><pre class="code-pre "><code langs="">Unit nginx_lb.service launched on 96ec72cf.../10.132.248.177
</code></pre>
<p>It may take a minute or so for the service to start, depending on how long it takes the image to be pulled down.  After it is started, if you check the logs with the <code>fleetctl journal</code> command, you should be able to see some log information from <code>confd</code>.  It should look something like this:</p>
<pre class="code-pre "><code class="code-highlight language-bash">fleetctl journal nginx_lb.service
</code></pre><pre class="code-pre "><code langs="">-- Logs begin at Mon 2014-09-15 14:54:05 UTC, end at Tue 2014-09-16 17:13:58 UTC. --
Sep 16 17:13:48 lala1 docker[15379]: 2014-09-16T17:13:48Z d7974a70e976 confd[14]: INFO Target config /etc/nginx/sites-enabled/app.conf out of sync
Sep 16 17:13:48 lala1 docker[15379]: 2014-09-16T17:13:48Z d7974a70e976 confd[14]: INFO Target config /etc/nginx/sites-enabled/app.conf has been updated
Sep 16 17:13:48 lala1 docker[15379]: [nginx] confd is monitoring etcd for changes...
Sep 16 17:13:48 lala1 docker[15379]: [nginx] starting nginx service...
Sep 16 17:13:48 lala1 docker[15379]: 2014-09-16T17:13:48Z d7974a70e976 confd[33]: INFO Target config /etc/nginx/sites-enabled/app.conf in sync
Sep 16 17:13:48 lala1 docker[15379]: ==> /var/log/nginx/access.log <==
Sep 16 17:13:48 lala1 docker[15379]: ==> /var/log/nginx/error.log <==
Sep 16 17:13:58 lala1 docker[15379]: 2014-09-16T17:13:58Z d7974a70e976 confd[33]: INFO /etc/nginx/sites-enabled/app.conf has md5sum a8517bfe0348e9215aa694f0b4b36c9b should be 33f42e3b7cc418f504237bea36c8a03e
Sep 16 17:13:58 lala1 docker[15379]: 2014-09-16T17:13:58Z d7974a70e976 confd[33]: INFO Target config /etc/nginx/sites-enabled/app.conf out of sync
Sep 16 17:13:58 lala1 docker[15379]: 2014-09-16T17:13:58Z d7974a70e976 confd[33]: INFO Target config /etc/nginx/sites-enabled/app.conf has been updated
</code></pre>
<p>As you can see, <code>confd</code> looked to <code>etcd</code> for its initial configuration.  It then started <code>nginx</code>.  Afterwards, we can see lines where the <code>etcd</code> entries have been re-evaluated and a new configuration file made.  If the newly generated file does not match the <code>md5sum</code> of the file in place, the file is switched out and the service is reloaded.</p>

<p>This allows our load balancing service to ultimately track our Apache backend servers.  If <code>confd</code> seems to be continuously updating, it may be because your Apache instances are refreshing their TTL too often.  You can increase the sleep and TTL values in the sidekick template to avoid this.</p>

<p>To see the load balancer in action, you can ask for the <code>/etc/environments</code> file from the host that is running the Nginx service.  This contains the host's public IP address.  If you want to make this configuration better, consider running a sidekick service that registers this information with <code>etcd</code>, just as we did for the Apache instances:</p>
<pre class="code-pre "><code class="code-highlight language-bash">fleetctl ssh nginx_lb cat /etc/environment
</code></pre><pre class="code-pre "><code langs="">COREOS_PRIVATE_IPV4=10.132.248.177
COREOS_PUBLIC_IPV4=104.131.16.222
</code></pre>
<p>Now, if we go to the public IPv4 address in our browser, we should see the page that we configured in our Apache instances:</p>

<p><img src="https://assets.digitalocean.com/articles/coreos_confd/apache_index.png" alt="Apache index page" /></p>

<p>Now, if you look at your logs again, you should be able to see information indicating which backend server was actually passed the request:</p>
<pre class="code-pre "><code class="code-highlight language-bash">fleetctl journal nginx_lb
</code></pre><pre class="code-pre "><code langs="">. . .
Sep 16 18:04:38 lala1 docker[18079]: 2014-09-16T18:04:38Z 51c74658196c confd[28]: INFO Target config /etc/nginx/sites-enabled/app.conf in sync
Sep 16 18:04:48 lala1 docker[18079]: 2014-09-16T18:04:48Z 51c74658196c confd[28]: INFO Target config /etc/nginx/sites-enabled/app.conf in sync
Sep 16 18:04:48 lala1 docker[18079]: [16/Sep/2014:18:04:48 +0000] 108.29.37.206 passed to: <span class="highlight">10.132.249.212:8888</span>: GET / HTTP/1.1 Upstream Response Time: 0.003 Request time: 0.003
</code></pre>
<h2 id="conclusion">Conclusion</h2>

<p>As you can see, it is possible to set up your services to check <code>etcd</code> for configuration details.  Tools like <code>confd</code> can make this process relatively simple by allowing for continuous polling of significant entries.</p>

<p>In the example in this guide, we configured our Nginx service to use <code>etcd</code> to generate its initial configuration.  We also set it up in the background to continuously check for changes.  This, combined with the dynamic configuration generation based on templates allowed us to consistently have an up-to-date picture of our backend servers.</p>

    