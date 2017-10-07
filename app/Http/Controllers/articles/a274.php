<link rel='stylesheet' href='digital_ocean.css'><br> <br> 
      <h3 id="introduction">Introduction</h3>

<p>Kubernetes is a system designed to manage applications built within Docker containers across clustered environments.  It handles the entire life cycle of a containerized application including deployment and scaling.</p>

<p>In this guide, we'll demonstrate how to get started with Kubernetes on a CoreOS cluster.  This system will allow us to group related services together for deployment as a unit on a single host using what Kubernetes calls "pods".  It also provides health checking functionality, high availability, and efficient usage of resources.</p>

<p>This tutorial was tested with Kubernetes v0.7.0. Keep in mind that this software changes frequently. To see your version, once it's installed, run:</p>
<pre class="code-pre "><code langs="">kubecfg -version
</code></pre>
<h3 id="prerequisites-and-goals">Prerequisites and Goals</h3>

<p>We will start with the same basic CoreOS clusters we have used in previous CoreOS guides.  To get this three member cluster up and running, follow our <a href="https://indiareads/community/tutorials/how-to-set-up-a-coreos-cluster-on-digitalocean">CoreOS clustering guide</a>.</p>

<p>This will give you three servers to configure.  While each node is essentially interchangeable at the CoreOS level, within Kubernetes, we'll need to assign more specialized roles.  We need one node to act as the master, this will run a few extra services, such as an API server and a controller manager.</p>

<p>For this guide, we will use the following details:</p>

<table class="pure-table"><thead>
<tr>
<th>Hostname</th>
<th>Public IPv4</th>
<th>Private IPv4</th>
<th>Role</th>
</tr>
</thead><tbody>
<tr>
<td>coreos-1</td>
<td>192.168.2.1</td>
<td>10.120.0.1</td>
<td>Master</td>
</tr>
<tr>
<td>coreos-2</td>
<td>192.168.2.2</td>
<td>10.120.0.2</td>
<td>Minion1</td>
</tr>
<tr>
<td>coreos-3</td>
<td>192.168.2.3</td>
<td>10.120.0.3</td>
<td>Minion2</td>
</tr>
</tbody></table>

<p>In the configuration we will be following, the master will also be a fully functional minion server capable of completing work.  The idea for this configuration was taken from <a href="https://github.com/bketelsen/coreos-kubernetes-digitalocean">Brian Ketelson's guide on setting up Kubernetes on CoreOS</a> here.</p>

<p>If you followed the guide above to create the cluster, both <code>etcd</code> and <code>fleet</code> should be configured to use each server's private IPv4 for communication.  The public IP address can be used for connecting from your local machine.</p>

<p>This guide will take this basic CoreOS cluster and install a number of services on top of it.</p>

<p>First, we will configure <code>flannel</code>, a network fabric layer that provides each machine with an individual subnet for container communication.  This is a relatively new CoreOS project made in a large part to adapt to Kubernetes assumptions about the networking environment.</p>

<p>We will configure Docker to use this networking layer for deployments.  On top of this, we will set up Kubernetes.  This involves a number of pieces.  We need to configure a proxying service, an API layer, and a node-level "pod" management system called Kubelet. </p>

<h2 id="create-the-flannel-network-fabric-layer">Create the Flannel Network Fabric Layer</h2>

<p>The first thing we need to do is configure the <code>flannel</code> service.  This is the component that provides individual subnets for each machine in the cluster.  Docker will be configured to use this for deployments.  Since this is a base requirement, it is a great place to start.</p>

<p>At the time of this writing, there are no pre-built binaries of <code>flannel</code> provided by the project.  Due to this fact, we'll have to build the binary and install it ourselves.  To save build time, we will be building this on a single machine and then later transferring the executable to our other nodes.</p>

<p>Like many parts of CoreOS, Flannel is built in the Go programming language.  Rather than setting up a complete Go environment to build the package, we'll use a container pre-built for this purpose.  Google maintains a Go container specifically for these types of situations.</p>

<p>All of the applications we will be installing will be placed in the <code>/opt/bin</code> directory, which is not created automatically in CoreOS.  Create the directory now:</p>
<pre class="code-pre "><code class="code-highlight language-bash">sudo mkdir -p /opt/bin
</code></pre>
<p>Now we can build the project using the Go container.  Just run this Docker command to pull the image from Docker Hub, run the container, and download and build the package within the container:</p>
<pre class="code-pre "><code langs="">docker run -i -t google/golang /bin/bash -c "go get github.com/coreos/flannel"
</code></pre>
<p>When the operation is complete, we can copy the compiled binary out of the container.  First, we need to know the container ID:</p>
<pre class="code-pre "><code langs="">docker ps -l -q
</code></pre>
<p>The result will be the ID that looks like this:</p>
<pre class="code-pre "><code langs=""><span class="highlight">004e7a7e4b70</span>
</code></pre>
<p>We can use this ID to specify a copy operation into the <code>/opt/bin</code> directory.  The binary has been placed at <code>/gopath/bin/flannel</code> within the container.  Since the <code>/opt/bin</code> directory isn't writeable by our <code>core</code> user, we'll have to use <code>sudo</code>:</p>
<pre class="code-pre "><code langs="">sudo docker cp <span class="highlight">004e7a7e4b70</span>:/gopath/bin/flannel /opt/bin/
</code></pre>
<p>We now have flannel available on our first machine.  A bit later, we'll copy this to our other machines.</p>

<h2 id="build-the-kubernetes-binaries">Build the Kubernetes Binaries</h2>

<p>Kubernetes is composed of quite a few different applications and layered technologies.  Currently, the project does not contain pre-built binaries for the various components we need.  We will build them ourselves instead.</p>

<p>We will only complete this process on <em>one</em> of our servers.  Since our servers are uniform in nature, we can avoid unnecessary build times by simply transferring the binaries that we will produce.</p>

<p>The first step is to clone the project from its GitHub repository.  We will clone it into our home directory:</p>
<pre class="code-pre "><code class="code-highlight language-bash">cd ~
git clone https://github.com/GoogleCloudPlatform/kubernetes.git
</code></pre>
<p>Next, we will go into the build directory within the repository.  From here, we can build the binaries using an included script:</p>
<pre class="code-pre "><code class="code-highlight language-bash">cd kubernetes/build
./release.sh
</code></pre>
<p>This process will take quite a long time.  It will start up a Docker container to build the necessary binary packages.</p>

<p>When the build process is completed, you will be able to find the binaries in the <code>~/kubernetes/_output/dockerized/bin/linux/amd64</code> directory:</p>
<pre class="code-pre "><code class="code-highlight language-bash">cd ~/kubernetes/_output/dockerized/bin/linux/amd64
ls
</code></pre><pre class="code-pre "><code langs="">e2e          kube-apiserver           kube-proxy      kubecfg  kubelet
integration  kube-controller-manager  kube-scheduler  kubectl  kubernetes
</code></pre>
<p>We will transfer these to the <code>/opt/bin</code> directory that we created earlier:</p>
<pre class="code-pre "><code class="code-highlight language-bash">sudo cp * /opt/bin
</code></pre>
<p>Our first machine now has all of the binaries needed for our project.  We can now focus on getting these applications on our other servers.</p>

<h2 id="transfer-executables-to-your-other-servers">Transfer Executables to your Other Servers</h2>

<p>Our first machine has all of the components necessary to start up a Kubernetes cluster.  We need to copy these to our other machines though before this will work.</p>

<p>Since Kubernetes is not a uniform installation (there is one master and multiple minions), each host does not need all of the binaries.  Each minion server only needs the scheduler, docker, proxy, kubelet, and flannel executables.</p>

<p>However, transferring all of the executables gives us more flexibility down the road.  It is also easier.  We will be transferring everything in this guide.</p>

<p>When you connected to your first machine, you should have forwarded your SSH agent information by connecting with the <code>-A</code> flag (after starting the agent and adding your key).  This is an important step.  Disconnect and reconnect if you did not pass this flag earlier.</p>

<p>You will need to run the <code>eval</code> and <code>ssh-add</code> commands from the <strong>Step 2—Authenticate</strong> section of <a href="https://indiareads/community/tutorials/how-to-connect-to-your-droplet-with-ssh#ssh-login-as-root">this tutorial</a> before connecting with <code>-A</code>.</p>

<p>Start by moving into the directory where we have placed our binaries:</p>
<pre class="code-pre "><code class="code-highlight language-bash">cd /opt/bin
</code></pre>
<p>Now, we can copy the files in this directory to our other hosts.  We will do this by tarring the executables directly to our shell's standard out.  We will then pipe this into our SSH command where we will connect to one of our other hosts. </p>

<p>The SSH command we will use will create the <code>/opt/bin</code> directory on our other host, change to the directory, and untar the information it receives through the SSH tunnel.  The entire command looks like this:</p>
<pre class="code-pre "><code langs="">tar -czf - . | ssh core@<span class="highlight">192.168.2.2</span> "sudo mkdir -p /opt/bin; cd /opt/bin; sudo tar xzvf -"
</code></pre>
<p>This will transfer all of the executables to the IP address you specified.  Run the command again using your third host:</p>
<pre class="code-pre "><code langs="">tar -czf - . | ssh core@<span class="highlight">192.168.2.3</span> "sudo mkdir -p /opt/bin; cd /opt/bin; sudo tar xzvf -"
</code></pre>
<p>You now have all of the executables in place on your three machines.</p>

<h2 id="setting-up-master-specific-services">Setting up Master-Specific Services</h2>

<p>The next step is to set up our <code>systemd</code> unit files to correctly configure and launch our new applications.  We will begin by handling the applications that will only run on our master server.</p>

<p>We will be placing these files in the <code>/etc/systemd/system</code> directory.  Move there now:</p>
<pre class="code-pre "><code class="code-highlight language-bash">cd /etc/systemd/system
</code></pre>
<p>Now we can begin building our service files. We will create two files on master only and five files that also belong on the minions. All of these files will be in <code>/etc/systemd/system/*.service</code>.</p>

<p>Master files:</p>

<ul>
<li><code>apiserver.service</code></li>
<li><code>controller-manager.service</code></li>
</ul>

<p>Minion files for all servers:</p>

<ul>
<li><code>scheduler.service</code></li>
<li><code>flannel.service</code></li>
<li><code>docker.service</code></li>
<li><code>proxy.service</code></li>
<li><code>kubelet.service</code></li>
</ul>

<h3 id="create-the-api-server-unit-file">Create the API Server Unit File</h3>

<p>The first file we will configure is the API server's unit file.  The API server is used to serve information about the cluster, handle post requests to alter information, schedule work on each server, and synchronize shared information.</p>

<p>We will be calling this unit file <code>apiserver.service</code> for simplicity.  Create and open that file now:</p>
<pre class="code-pre "><code langs="">sudo vim apiserver.service
</code></pre>
<p>Within this file, we will start with the basic metadata about our service.  We need to make sure this unit is not started until the <code>etcd</code> and Docker services are up and running:</p>
<pre class="code-pre "><code langs="">[Unit]
Description=Kubernetes API Server
After=etcd.service
After=docker.service
Wants=etcd.service
Wants=docker.service
</code></pre>
<p>Next, we will complete the <code>[Service]</code> section.  This will mainly be used to start the API server with some parameters describing our environment.  We will also set up restart conditions:</p>
<pre class="code-pre "><code langs="">[Unit]
Description=Kubernetes API Server
After=etcd.service
After=docker.service
Wants=etcd.service
Wants=docker.service

[Service]
ExecStart=/opt/bin/kube-apiserver \
-address=127.0.0.1 \
-port=8080 \
-etcd_servers=http://127.0.0.1:4001 \
-portal_net=10.100.0.0/16 \
-logtostderr=true
ExecStartPost=-/bin/bash -c "until /usr/bin/curl http://127.0.0.1:8080; do echo \"waiting for API server to come online...\"; sleep 3; done"
Restart=on-failure
RestartSec=5
</code></pre>
<p>The above section establishes the networking address and port where the server will run, as well as the location where <code>etcd</code> is listening.  The <code>portal_net</code> parameter gives the network range that the <code>flannel</code> service will use.</p>

<p>After we start the service, we check that it is up and running in a loop.  This ensures that the service is actually able to accept connections before the dependent services are initiated.  Not having this can lead to errors in the dependent services that would require a manual restart.</p>

<p>Finally, we will have to install this unit.  We can do that with an <code>[Install]</code> section that will tell our host to start this service when the machine is completely booted:</p>
<pre class="code-pre "><code langs="">[Unit]
Description=Kubernetes API Server
After=etcd.service
After=docker.service
Wants=etcd.service
Wants=docker.service

[Service]
ExecStart=/opt/bin/kube-apiserver \
-address=127.0.0.1 \
-port=8080 \
-etcd_servers=http://127.0.0.1:4001 \
-portal_net=10.100.0.0/16 \
-logtostderr=true
ExecStartPost=-/bin/bash -c "until /usr/bin/curl http://127.0.0.1:8080; do echo \"waiting for API server to come online...\"; sleep 3; done"
Restart=on-failure
RestartSec=5

[Install]
WantedBy=multi-user.target
</code></pre>
<p>When you are finished, close the file.</p>

<h3 id="create-the-controller-manager-unit-file">Create the Controller Manager Unit File</h3>

<p>The next piece required by Kubernetes is the Controller Manager server.  This component is used to perform data replication among the cluster units.</p>

<p>Open up a file called <code>controller-manager.service</code> in the same directory:</p>
<pre class="code-pre "><code langs="">sudo vim controller-manager.service
</code></pre>
<p>We'll begin with the basic metadata again.  This will follow the same format as the last file.  In addition to the other dependencies, this service must start up after the API server unit we just configured:</p>
<pre class="code-pre "><code langs="">[Unit]
Description=Kubernetes Controller Manager
After=etcd.service
After=docker.service
After=apiserver.service
Wants=etcd.service
Wants=docker.service
Wants=apiserver.service
</code></pre>
<p>For the <code>[Service]</code> portion of this file, we just need to pass a few parameters to the executable.  Mainly, we are pointing the application to the location of our API server.  Here, we have passed in each of our machines' private IP addresses, separated by commas.  Modify these values to mirror your own configuration. Again, we will make sure this unit restarts on failure since it is required for our Kubernetes cluster to function correctly:</p>
<pre class="code-pre "><code langs="">[Unit]
Description=Kubernetes Controller Manager
After=etcd.service
After=docker.service
After=apiserver.service
Wants=etcd.service
Wants=docker.service
Wants=apiserver.service

[Service]
ExecStart=/opt/bin/kube-controller-manager \
-master=http://127.0.0.1:8080 \
-machines=<span class="highlight">10.120.0.1</span>,<span class="highlight">10.120.0.2</span>,<span class="highlight">10.120.0.3</span>
Restart=on-failure
RestartSec=5
</code></pre>
<p>We will also be using the same installation instructions so that this unit starts on boot as well:</p>
<pre class="code-pre "><code langs="">[Unit]
Description=Kubernetes Controller Manager
After=etcd.service
After=docker.service
After=apiserver.service
Wants=etcd.service
Wants=docker.service
Wants=apiserver.service

[Service]
ExecStart=/opt/bin/kube-controller-manager \
-master=http://127.0.0.1:8080 \
-machines=<span class="highlight">10.120.0.1</span>,<span class="highlight">10.120.0.2</span>,<span class="highlight">10.120.0.3</span>
Restart=on-failure
RestartSec=5

[Install]
WantedBy=multi-user.target
</code></pre>
<p>Save and close the file when you are finished.</p>

<h2 id="setting-up-cluster-services">Setting Up Cluster Services</h2>

<p>Now that we have our master-specific services configured, we can configure the unit files that need to be present on <strong>all</strong> of our machines.  This means that you should add these files to both the master and the minion servers and configure them accordingly.</p>

<p>These five files should be created on all machines, in <code>/etc/systemd/system/*.service</code>.</p>

<ul>
<li><code>scheduler.service</code></li>
<li><code>flannel.service</code></li>
<li><code>docker.service</code></li>
<li><code>proxy.service</code></li>
<li><code>kubelet.service</code></li>
</ul>

<h3 id="create-the-scheduler-unit-file">Create the Scheduler Unit File</h3>

<p>The next component is the scheduler.  The scheduler decides which minion to run workloads on and communicates to make sure this happens.</p>

<p>Create and open a file for this unit now:</p>
<pre class="code-pre "><code langs="">sudo vim scheduler.service
</code></pre>
<p>This unit starts off in much of the same way as the last one.  It has dependencies on all of the same services:</p>
<pre class="code-pre "><code langs="">[Unit]
Description=Kubernetes Scheduler
After=etcd.service
After=docker.service
After=apiserver.service
Wants=etcd.service
Wants=docker.service
Wants=apiserver.service
</code></pre>
<p>The service section itself is very straight-forward.  We only need to point the executable at the network address and port that the API server is located on.  Again, we'll restart the service in case of failure.</p>

<p>The installation section mirrors the others we have seen so far:</p>
<pre class="code-pre "><code langs="">[Unit]
Description=Kubernetes Scheduler
After=etcd.service
After=docker.service
After=apiserver.service
Wants=etcd.service
Wants=docker.service
Wants=apiserver.service

[Service]
ExecStart=/opt/bin/kube-scheduler -master=127.0.0.1:8080
Restart=on-failure
RestartSec=5

[Install]
WantedBy=multi-user.target
</code></pre>
<p>When you are finished, save and close the file.</p>

<h3 id="create-the-flannel-unit-file">Create the Flannel Unit File</h3>

<p>The next component that we need to get up and running is <code>flannel</code>, our network fabric layer.  This will be used to give each node its own subnet for Docker containers.</p>

<p>Again, on each of your machines, change to the <code>systemd</code> configuration directory:</p>
<pre class="code-pre "><code langs="">cd /etc/systemd/system
</code></pre>
<p>Create and open the <code>flannel</code> unit file in your text editor:</p>
<pre class="code-pre "><code langs="">sudo vim flannel.service
</code></pre>
<p>Inside of this file, we will start with the metadata information.  Since this service requires <code>etcd</code> to register the subnet information, we need to start this after <code>etcd</code>:</p>
<pre class="code-pre "><code langs="">[Unit]
Description=Flannel network fabric for CoreOS
Requires=etcd.service
After=etcd.service
</code></pre>
<p>For the <code>[Service]</code> section, we're first going to source the <code>/etc/environment</code> file so that we can have access to the private IP address of our host.</p>

<p>The next step will be to place an <code>ExecStartPre=</code> line that attempts to register the subnet range with <code>etcd</code>.  It will continually try to register with etcd until it is successful.  We will be using the <code>10.100.0.0/16</code> range for this guide.</p>

<p>Then, we will start <code>flannel</code> with the private IP address we're sourcing from the environment file. </p>

<p>Afterwards, we want to check whether <code>flannel</code> has written its information to its file (so that Docker can read it in a moment) and sleep if it has not.  This ensures that the Docker service does not try to read the file before it is available (this can happen on the first server to come online).  We will configure the restart using the usual parameters and install the unit using the <code>multi-user.target</code> again:</p>
<pre class="code-pre "><code langs="">[Unit]
Description=Flannel network fabric for CoreOS
Requires=etcd.service
After=etcd.service

[Service]
EnvironmentFile=/etc/environment
ExecStartPre=-/bin/bash -c "until /usr/bin/etcdctl set /coreos.com/network/config '{\"Network\": \"10.100.0.0/16\"}'; do echo \"waiting for etcd to become available...\"; sleep 5; done"
ExecStart=/opt/bin/flannel -iface=${COREOS_PRIVATE_IPV4}
ExecStartPost=-/bin/bash -c "until [ -e /run/flannel/subnet.env ]; do echo \"waiting for write.\"; sleep 3; done"
Restart=on-failure
RestartSec=5

[Install]
WantedBy=multi-user.target
</code></pre>
<p>Save and close the file when you are finished.  Create the same file on your other hosts.</p>

<h3 id="create-the-docker-unit-file">Create the Docker Unit File</h3>

<p>The next file that we will create is not actually related to the executables in our <code>/opt/bin</code> directory.  We need to create a Docker service file so that the service will be started with knowledge of the <code>flannel</code> networking overlay we just configured.</p>

<p>Create the appropriate unit file with your text editor:</p>
<pre class="code-pre "><code langs="">sudo vim docker.service
</code></pre>
<p>Start with the usual metadata.  We need this to start after the <code>flannel</code> service has been configured and brought online:</p>
<pre class="code-pre "><code langs="">[Unit]
Description=Docker container engine configured to run with flannel
Requires=flannel.service
After=flannel.service
</code></pre>
<p>For the <code>[Service]</code> section, we'll need to source the file that <code>flannel</code> uses to store the environmental variables it is creating.  This will have the current host's subnet information.</p>

<p>We then bring down the current <code>docker0</code> bridge interface if it is running and delete it.  This allows us to restart Docker with a clean slate.  The process will configure the <code>docker0</code> interface using the <code>flannel</code> networking information.</p>

<p>We use the same restart and <code>[Install]</code> details that we've been using with our other units:</p>
<pre class="code-pre "><code langs="">[Unit]
Description=Docker container engine configured to run with flannel
Requires=flannel.service
After=flannel.service

[Service]
EnvironmentFile=/run/flannel/subnet.env
ExecStartPre=-/usr/bin/ip link set dev docker0 down
ExecStartPre=-/usr/sbin/brctl delbr docker0
ExecStart=/usr/bin/docker -d -s=btrfs -H fd:// --bip=${FLANNEL_SUBNET} --mtu=${FLANNEL_MTU}
Restart=on-failure
RestartSec=5

[Install]
WantedBy=multi-user.target
</code></pre>
<p>Save and close the file when you are finished.  Create this same file on each of your hosts.</p>

<h3 id="create-the-proxy-unit-file">Create the Proxy Unit File</h3>

<p>The next logical unit to discuss is the proxy server that each of the cluster members runs.  The Kubernetes proxy server is used to route and forward traffic to and from containers.</p>

<p>Open a proxy unit file with your text editor:</p>
<pre class="code-pre "><code langs="">sudo vim proxy.service
</code></pre>
<p>For the metadata section, we will need to define dependencies on <code>etcd</code> and Docker.  For the <code>[Service]</code> section, we just need to start the executable with the local <code>etcd</code> server's address.  The restarting configuration and the installation details will mirror our previous service files:</p>
<pre class="code-pre "><code langs="">[Unit]
Description=Kubernetes proxy server
After=etcd.service
After=docker.service
Wants=etcd.service
Wants=docker.service

[Service]
ExecStart=/opt/bin/kube-proxy -etcd_servers=http://127.0.0.1:4001 -logtostderr=true
Restart=on-failure
RestartSec=5

[Install]
WantedBy=multi-user.target
</code></pre>
<p>Save the file when you are finished.  Create this same file on each of your hosts.</p>

<h3 id="create-the-kubelet-unit-file">Create the Kubelet Unit File</h3>

<p>Now, we will create the Kubelet unit file.  This component is used to manage container deployments.  It ensures that the containers are in the state they are supposed to be in and monitors the system for changes in the desired state of the deployments.</p>

<p>Create and open the file in your text editor:</p>
<pre class="code-pre "><code langs="">sudo vim kubelet.service
</code></pre>
<p>The metadata section will contain the same dependency information about <code>etcd</code> and Docker:</p>
<pre class="code-pre "><code langs="">[Unit]
Description=Kubernetes Kubelet
After=etcd.service
After=docker.service
Wants=etcd.service
Wants=docker.service
</code></pre>
<p>For the <code>[Service]</code> section, we again have to source the <code>/etc/environment</code> file to get access to the private IP address of the host.  We will then call the <code>kubelet</code> executable, setting its address and port.  We also override the hostname to use the same private IP address and point the service to the local <code>etcd</code> instance.  We provide the same restart and install details that we've been using:</p>
<pre class="code-pre "><code langs="">[Unit]
Description=Kubernetes Kubelet
After=etcd.service
After=docker.service
Wants=etcd.service
Wants=docker.service

[Service]
EnvironmentFile=/etc/environment
ExecStart=/opt/bin/kubelet \
-address=${COREOS_PRIVATE_IPV4} \
-port=10250 \
-hostname_override=${COREOS_PRIVATE_IPV4} \
-etcd_servers=http://127.0.0.1:4001 \
-logtostderr=true
Restart=on-failure
RestartSec=5

[Install]
WantedBy=multi-user.target
</code></pre>
<p>Save and close the file when you are finished.</p>

<h2 id="enabling-the-services">Enabling the Services</h2>

<p>Now that you have all of your service files started, you can enable these units.  Doing so processes the information in the <code>[Install]</code> section of each unit.</p>

<p>Since our units say that they are wanted by the multi-user target, this means that when the system tries to bring the server into multi-user mode, all of our services will be started automatically.</p>

<p>To accomplish this, go to your <code>/etc/systemd/system</code> directory:</p>
<pre class="code-pre "><code langs="">cd /etc/systemd/system
</code></pre>
<p>From here, we can enable all of the scripts:</p>
<pre class="code-pre "><code langs="">sudo systemctl enable *
</code></pre>
<p>This will create a <code>multi-user.target.wants</code> directory with symbolic links to our unit files.  This directory will be processed by <code>systemd</code> toward the end of the boot process.</p>

<p>Repeat this step on each of your servers.</p>

<p>Now that we have our services enabled, we can reboot the servers in turn.</p>

<p>We will start with the master server, but you can do so in any order.  While it is not necessary to reboot to start these services, doing so ensures that our unit files have been written in a way that permits a seamless dependency chain:</p>
<pre class="code-pre "><code langs="">sudo reboot
</code></pre>
<p>Once the master comes back online, you can reboot your minion servers:</p>
<pre class="code-pre "><code langs="">sudo reboot
</code></pre>
<p>Once all of your servers are online, make sure your services started correctly.  You can check this by typing:</p>
<pre class="code-pre "><code langs="">systemctl status <span class="highlight">service_name</span>
</code></pre>
<p>Or you can go the journal by typing:</p>
<pre class="code-pre "><code langs="">journalctl -b -u <span class="highlight">service_name</span>
</code></pre>
<p>Look for an indication that the services are up and running correctly.  If there are any issues, a restart of the specific service might help:</p>
<pre class="code-pre "><code langs="">sudo systemctl restart <span class="highlight">service_name</span>
</code></pre>
<p>When you are finished, you should be able to view your machines from your master server.  After logging into your master server, check that all of the servers are available by typing:</p>
<pre class="code-pre "><code langs="">kubecfg list minions
</code></pre><pre class="code-pre "><code langs="">Minion identifier
----------
<span class="highlight">10.200.0.1</span>
<span class="highlight">10.200.0.2</span>
<span class="highlight">10.200.0.3</span>
</code></pre>
<p>In a future guide, we'll talk about how to use Kubernetes to schedule and control services on your CoreOS cluster.</p>

<h2 id="conclusion">Conclusion</h2>

<p>You should now have Kubernetes set up across your CoreOS cluster.  This gives you a great management and scheduling interface for working with services in logical groupings.</p>

<p>You probably noticed that the steps above lead to a very manual process.  A large part of this is because we built the binaries on our machines.  If you were to host the binaries on a web server accessible within your private network, you could pull down the binaries and automatically configure them by crafting special cloud-config files. </p>

<p>Cloud-config files are flexible enough that you could inject most of the unit files without any modification (with the exception of the <code>apiserver.service</code> file, which needs access to the IPs of each node) and start them up as the CoreOS node is booted.  This is outside of the scope of this guide, but a good next step in terms of automating the process.</p>

    