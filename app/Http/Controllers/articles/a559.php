<link rel='stylesheet' href='digital_ocean.css'><br><img src=https://community-cdn-digitalocean-com.global.ssl.fastly.net/assets/tutorials/images/large/jenkins_rancher_tw__1_.jpg?1427321199/> <br> 
      <h3 id="an-article-from-rancher">An Article from <a href="http://rancher.com/">Rancher</a></h3>

<h2 id="introduction">Introduction</h2>

<p>Effective continuous integration (CI) is a core requirement for any successful development team. Because CI is not a front-line service, it often gets run on mid-tier or surplus hardware. Adding builds for pull requests, automated deployments, acceptance tests, content uploads, and a host of other tasks can quickly overwhelm the resources of the build machine — especially close to launch, when there are a lot of commits and deployment activity.</p>

<p>In this article, we will construct a distributed build system using Docker to create and run our Jenkins images and Rancher to orchestrate our Docker cluster. Jenkins is one of the most prominent open-source CI solutions. Docker automates application deployment within software containers, and Rancher provides a complete platform for managing Docker in production.</p>

<p>This article covers an exclusively cloud-based Jenkins deployment. However, an alternative is to use an in-house Jenkins master with cloud servers to provide overflow capacity when more resources are needed. This is where Docker and Rancher really shine: Docker provides us almost identical deployment environments on any node and Rancher lets us combine nodes from various cloud providers or in-house servers into a single cluster running over its own VPN. By the end of this tutorial, you should be able to easily set up a Dockerized Jenkins deployment with an arbitrary number of slaves.</p>

<h3 id="prerequisites">Prerequisites</h3>

<p>This tutorial will use a total of three Droplets: one for a Rancher server, one for a Rancher compute node running the Jenkins master, and one for a second Rancher compute node running the Jenkins slave.</p>

<p><img src="https://assets.digitalocean.com/articles/Jenkins_Rancher/1.png" alt="" /></p>

<p>We'll refer to the IP addresses of these Droplets with <code><span class="highlight">your_rancher_server_ip</span></code>, <code><span class="highlight">your_jenkins_master_ip</span></code>, and <code><span class="highlight">your_jenkins_slave_ip</span></code> respectively; wherever you see these variables in this tutorial, replace them with the appropriate IP address.</p>

<p>Initially, we will create one Droplet for the Rancher server; creation of the two compute nodes will be covered in a later step. So, to begin this tutorial, you will need:</p>

<ul>
<li>One Ubuntu 14.04 Droplet with the Docker 1.5.0 image. Because this Droplet will be used exclusively as as a Rancher server, you can choose a small Droplet size.</li>
</ul>

<p>You can find the Docker 1.5.0 image option on the Droplet creation page, in the <strong>Applications</strong> tab under <strong>Select Image</strong>. This Droplet will also requires custom user data. To add this, click <strong>Enable User Data</strong> in the <strong>Available Settings</strong> section, and enter the script below in the text box that appears. This script tells the Droplet to run a Rancher server upon start-up. </p>
<pre class="code-pre "><code langs="">#!/bin/bash
docker run -d --name rancher-server -p 8080:8080 rancher/server
</code></pre>
<h2 id="step-1-—-configuring-authentication">Step 1 — Configuring Authentication</h2>

<p>Once your Rancher server is created, after a moment you'll be able to access its UI through a browser pointed to <code>http://<span class="highlight">your_rancher_server_ip</span>:8080/</code>. Because the Rancher server is open to the Internet, it's a good idea to set up authentication. In this step, we will set up Github OAuth based authentication, which is what Rancher currently supports.</p>

<p>You will see a warning in the top right corner which says <strong>Access Control is not configured</strong> followed by a link to <strong>Settings</strong>. Click <strong>Settings</strong> and follow the instructions given there to register a new Application with Github, and copy the Client ID and Secret into the respective text fields. </p>

<p>When you finish, click <strong>Authenticate with Github</strong>, then <strong>Authorize application</strong> in the window that pops up. Once you do, the page will reload, and the instructions on setting up OAuth will be replaced by the <strong>Configure Authorization</strong> section. Add any additional users and organizations that should be given access to Rancher. If you make any changes, a button that reads <strong>Save authorization configuration</strong> will appear. Click it when you're done.</p>

<p>Once you save the authorization configuration, the warning in the top right corner should be replaced by your Github profile image and a project selection menu (which says <strong>Default</strong> initially). Click <strong>Default</strong> to open the project selection menu, then click <strong>Manage Projects</strong>, and finally <strong>Create a project</strong>. Add a project called Jenkins, then use the project selection menu again to select the Jenkins project.</p>

<p><img src="https://assets.digitalocean.com/articles/Jenkins_Rancher/2.png" alt="" /></p>

<p>This will help keep your Rancher interface uncluttered by keeping the various projects you run on Rancher isolated. You can create additional projects (which require additional compute nodes) if you want to run other services in addition to Jenkins on the same Rancher cluster. Also note that the Default project is specific to the logged in user, so if you intend to give multiple people access to your Rancher agents, you should not use the default project.</p>

<h2 id="step-2-—-registering-the-rancher-compute-nodes">Step 2 — Registering the Rancher Compute Nodes</h2>

<p>Now that the server and authentication are set up, we can register some compute nodes to run our Jenkins deployments on.</p>

<p><strong>Note</strong>: Prior to authentication, Rancher compute nodes can be registered without providing a registration token. However, because we have enabled authentication, all agents must provide a registration token to be added to the cluster. </p>

<p>In the Rancher UI, click on <strong>Hosts</strong> (in the menu on the left), then <strong>Register a new host</strong>. Copy the Docker run command from the window that pops up, then close the window. Return to the IndiaReads control panel and create two additional Droplets with the Docker 1.5.0 image, like the Rancher server. You may want to select a larger instance size for these two Droplets if your builds are resource intensive.</p>

<p>For the user data in both of these Droplets, add <code>#!/bin/bash</code> followed by the Docker run command you copied earlier. It should look similar to this.</p>
<pre class="code-pre "><code langs="">#!/bin/bash
sudo docker run -d --privileged
  -v /var/run/docker.sock:/var/run/docker.sock rancher/agent
  http://<span class="highlight">your_rancher_server_ip</span>:8080/v1/scripts/<span class="highlight">A2DE06535002ECCAAFCD:1426622400000:iniUzPiTnjyFaXs9lCKauvoZOMQ</span>
</code></pre>
<p>The long string of numbers and letters at the end will be different for your command. Please make sure you have selected your project before clicking <strong>Register a new host</strong>, as the token is unique for each project.</p>

<p>After a few minutes, you should be able to see both of your Rancher compute nodes in the Rancher UI. You'll see the names of your Droplets where it says <strong>RancherAgent</strong> in the image below.</p>

<p><img src="https://assets.digitalocean.com/articles/Jenkins_Rancher/3.png" alt="" /></p>

<h2 id="step-3-—-launching-the-jenkins-master-node">Step 3 — Launching the Jenkins Master Node</h2>

<p>We are now ready to launch our Jenkins master node using the official Jenkins image.</p>

<p>To launch the container, click <strong>Add Container</strong> under the compute node you want to use, and add the following options:</p>

<ul>
<li>Use <strong>Master</strong> as the container name, in the text box next to <strong>Name</strong>.</li>
<li>Use <strong>jenkins</strong> as the source image, in the text box next to <strong>Select Image</strong>.</li>
</ul>

<p>Next, click the <strong>+</strong> next to <strong>Port Map</strong>. Fill in 8080 in both fields, and leave TCP as the protocol. This will give us access to the Jenkins web UI. Click the <strong>+</strong> again and add port 50000 in both fields, and leave TCP as the protocol. This allows slaves can connect to the master.</p>

<p>Next, click <strong>Advanced Options</strong>, then the <strong>Volumes</strong> tab. Click the <strong>+</strong> next to <strong>Volumes</strong>, and specify <code>/var/jenkins_home</code> in the text box that comes up. Having your Jenkins home directory in a volume allows you to retain your configuration if you restart your container and also allows you to back up your container using the volumes from another container feature. </p>

<p>Finally, click <strong>Create</strong> to start your Jenkins container.</p>

<h2 id="step-4-­—-launching-the-jenkins-slave-node">Step 4 ­— Launching the Jenkins Slave Node</h2>

<p>In this step, we will launch the Jenkins slave.</p>

<p>Point your browser to <code>http://<span class="highlight">your_jenkins_master_ip</span>:8080</code> to load the Jenkins UI.</p>

<p><img src="https://assets.digitalocean.com/articles/Jenkins_Rancher/4.png" alt="" /></p>

<p>In the Jenkins UI, create a node configuration by browsing to <strong>Manage Jenkins</strong> on the left, then <strong>Manage Nodes</strong> in the next menu, and finally <strong>New Node</strong> on the left of the final page. In the next menu, enter a name for your slave in the text box next to <strong>Node name</strong> (and remember it — we'll need it again in a moment), choose <strong>Dumb Slave</strong> as the type, and click <strong>OK</strong>.</p>

<p>You will be redirected to a page with details about this node. For <strong>Remote root directory</strong>, type <code>/var/jenkins</code>. For <strong>Launch method</strong>, choose <strong>Launch slave agents via Java Web Start</strong>. You may also want to update the <strong># of executors</strong> setting to higher than its default of 1 to increase the number of parallel builds allowed on the slave. The rest of the settings can be left to their default values. Click <strong>save</strong> to commit the slave configuration. </p>

<p>We are now ready to launch our slave container. In the Rancher UI, click <strong>Add Container</strong> on the remaining compute node, and add the following options:</p>

<ul>
<li>Use <strong>Slave 1</strong> as the container name, in the text box next to <strong>Name</strong>.</li>
<li>Use <strong>usman/jenkins-slave</strong> as the source image, in the text box next to <strong>Select Image</strong>.</li>
</ul>

<p>Then click <strong>Advanced Options</strong>. You'll start out in the <strong>Command</strong> tab. Click the <strong>+</strong> next to <strong>Environment Vars</strong> and add one entry with <strong>Name</strong> as <code>MASTER_HOST</code> and <strong>Value</strong> as <code><span class="highlight">your_jenkins_master_ip</span></code>. Click the <strong>+</strong> again and add another entry with <strong>Name</strong> as <code>NODE</code> and <strong>Value</strong> as the name of your Jenkins slave as specified in the <strong>New Node</strong> menu via the Jenkins UI earlier in this step.</p>

<p>Next, click the <strong>Volumes</strong> tab. Click the <strong>+</strong> next to <strong>Volumes</strong>, and specify <code>/var/jenkins</code> in the text box that comes up.</p>

<p>Finally, click <strong>Create</strong>.</p>

<p>The <code>jenkins-slave</code> container will download a jar file from the Jenkins master and run a Jenkins slave node. When the slave comes up, you should be able to see its status after refreshing the <strong>Manage Nodes</strong> page, where we left off in the Jenkins UI. You should see your slave node with a response time value and no red X over the computer icon as shown below. </p>

<p><img src="https://assets.digitalocean.com/articles/Jenkins_Rancher/5.png" alt="" /></p>

<h3 id="conclusion">Conclusion</h3>

<p>In this article we have set up a Jenkins CI deployment using Docker and Rancher. Our Jenkins cluster is now ready for further configuration and the creation of build jobs.</p>

<p>Docker provides us with a consistent environment to run Jenkins, and Rancher provides networking between the host and allows us to manage the cluster from the web UI without having to manually access the Droplets or provision servers. Using this toolset, we are able to scale our build system resources up and down rapidly. This can be essential in maintaining unobtrusive build systems at critical times such as launches. </p>

    