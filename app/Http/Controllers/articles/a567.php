<link rel='stylesheet' href='digital_ocean.css'><br><img src=https://community-cdn-digitalocean-com.global.ssl.fastly.net/assets/tutorials/images/large/openshift_origin_tw.png?1426699757/> <br> 
      <h3 id="introduction">Introduction</h3>

<p>OpenShift is Red Hat's Platform-as-a-Service (PaaS) that allows developers to quickly develop, host, and scale applications in a cloud environment. OpenShift Origin is the open source upstream of OpenShift. It has built-in support for a variety of languages, runtimes, and data layers, including <span class="highlight">Java EE6</span>, <span class="highlight">Ruby</span>, <span class="highlight">PHP</span>, <span class="highlight">Python</span>, <span class="highlight">Perl</span>, <span class="highlight">MongoDB</span>, <span class="highlight">MySQL</span>, and <span class="highlight">PostgreSQL</span>. You can add new runtimes and frameworks to OpenShift with custom or community cartridges.</p>

<p>Easily scaling your web applications is a primary reason to run them on OpenShift Origin.</p>

<p><strong>NOTE: Throughout this tutorial user input will be highlighted in <span class="highlight">red</span>.</strong></p>

<h2 id="how-openshift-works">How OpenShift Works</h2>

<h3 id="openshift-roles">OpenShift Roles</h3>

<p>There are four roles used on the OpenShift platform. While it is not significantly important you know what the roles do for this tutorial, if you wish to deploy a cluster of servers to offer high availability, load-balancing, etc., you will need to understand the functions these roles provide.</p>

<p>In our tutorial, we'll be configuring a single server to run all of these roles.</p>

<p><strong>Broker</strong></p>

<p>The Broker role consists of the OpenShift Broker RPMs and an MCollective client. The Broker serves as a central hub of the OpenShift deployment, and provides a web interface where users can manage their hosted applications.</p>

<p><strong>DBServer</strong></p>

<p>This role consists of the MongoDB database that the Broker uses to track users and applications.</p>

<p><strong>MsgServer</strong></p>

<p>The MsgServer role includes the ActiveMQ server plus an MCollective client.</p>

<p><strong>Node</strong></p>

<p>The Node role is assigned to any host that will actually be used to store and serve OpenShift-hosted applications. oo-install supports the deployment of Nodes as part of an initial installation and as part of a workflow to add a new Node to an existing OpenShift deployment.</p>

<h3 id="the-openshift-architecture">The OpenShift Architecture</h3>

<p>OpenShift is designed to be a high-availability, scalable application platform. When configured properly, a large OpenShift deployment can offer an easy way to scale your application when demands increase, while providing zero downtime. With a cluster of OpenShift hosts in multiple data center locations, you can survive an entire data center going down. In this tutorial we will set up our first OpenShift host running all the roles required by OpenShift.</p>

<h3 id="how-it-works-from-a-client-39-s-perspective">How it works from a client's perspective</h3>

<ul>
<li>A client wants to visit the site <span class="highlight">app-owner.apps.example.com</span>.</li>
<li>The client's browser requests the DNS record for the domain.</li>
<li>The DNS server responds with the IP Address of a Node hosting the application.</li>
<li>The clients browser sends a GET request to the Node.</li>
<li>The Node maps the request to the desired Application.</li>
<li>The Application itself responds to the request directly.</li>
</ul>

<p><img src="https://assets.digitalocean.com/articles/configure_openshift/1.png" alt="" /></p>

<p><strong>How did the DNS server know which Node is running the application?</strong></p>

<p>The developer connects to a Broker to create/manage an application. When the owner modifies an application, the Broker will send a message to the DNS server with the new information. This information includes the domain(s) being used for the application, and which Nodes are hosting the application. Because of this automation, it is a requirement for OpenShift to have control over the DNS Zone of the domain or subdomain used for apps.</p>

<p>OpenShift uses the Bind DNS Server. If you have an existing Bind DNS server, you can configure OpenShift to work with it. However, in this tutorial, we will cover the process of using a new DNS server configured automatically by the OpenShift Origin installer.</p>

<p>If you would prefer to use an existing BIND DNS server, you can read instructions for setting up DNS in the <a href="http://openshift.github.io/documentation/oo_deployment_guide_comprehensive.html#dns">OpenShift Origin Comprehensive Deployment Guide</a>.</p>

<h3 id="dns-configuration">DNS Configuration</h3>

<p>For the remainder of this tutorial we will be using the following domains. Substitute these with your own, and feel free to use a personalized naming convention. </p>

<ul>
<li><strong>example-dns.com</strong> - Used for our nameservers

<ul>
<li><strong>ns1.example-dns.com</strong></li>
<li><strong>ns2.example-dns.com</strong></li>
</ul></li>
<li><strong>example.com</strong>

<ul>
<li><strong>apps.example.com</strong> - Used for OpenShift applications</li>
<li><strong>openshift.example.com</strong> - Used for OpenShift Hosts</li>
<li><strong>master.openshift.example.com</strong> - The host name of our Droplet</li>
</ul></li>
</ul>

<h2 id="prerequisites">Prerequisites</h2>

<h3 id="droplet-requirements">Droplet Requirements</h3>

<ul>
<li><strong>1GB Droplet or larger</strong></li>
</ul>

<p>The Installation of OpenShift is fairly resource intensive, and some packages can exceed 512 MB of RAM usage. You should use a 1 GB or larger Droplet. If you have any issues registering the cartridges at the end of the installer, chances are some packages failed to install do to the lack of memory. This can be confirmed by examining the installation logs.</p>

<p>To check the installation log:</p>
<pre class="code-pre "><code langs="">cat /tmp/openshift-deploy.log
</code></pre>
<h3 id="supported-operating-systems">Supported Operating Systems</h3>

<ul>
<li><strong>CentOS 6.5 64-bit</strong> (standard IndiaReads image)</li>
</ul>

<p>OpenShift Origin 4 is supported on 64-bit versions of Red Hat Enterprise Linux (RHEL) 6.4 or higher and CentOS 6.4 or higher. It is not supported on Fedora, RHEL 7.x, or CentOS 7.x. A minimal installation of RHEL / CentOS is recommended to avoid package incompatibilities with OpenShift. This tutorial will use the standard Digital Ocean CentOS 6.5 x64 image on a 1 GB Droplet.</p>

<h3 id="installer-dependencies">Installer Dependencies</h3>

<p>The following utilities are required by the OpenShift Origin installer. This tutorial will show you how to install Ruby. The other packages are already installed by default with the IndiaReads CentOS 6.5 image.</p>

<ul>
<li><strong>curl</strong></li>
<li><strong>ruby</strong> - 1.8.7 or greater</li>
<li><strong>ssh</strong> - If you are deploying to systems other than the installer host</li>
</ul>

<h3 id="root-access">Root Access</h3>

<p>The rest of this tutorial will assume you are connected to your server with the root user account, or a user account with sudo privileges.</p>

<p>To enter the root shell from another account:</p>
<pre class="code-pre "><code langs="">sudo su
</code></pre>
<h2 id="step-one-—-install-updates">Step One — Install Updates</h2>

<p>Before proceeding, it is always a good idea to make sure you have the latest updates installed.</p>

<p>To install updates:</p>
<pre class="code-pre "><code langs="">yum update
</code></pre>
<h2 id="step-two-—-install-preferred-text-editor">Step Two — Install Preferred Text Editor</h2>

<p>You can use your favorite text editor throughout this tutorial; however, the examples will use Nano.</p>

<p>Install Nano with:</p>
<pre class="code-pre "><code langs="">yum install nano
</code></pre>
<p>When you are done editing a file in Nano, press <strong>Ctrl+X</strong>, press <strong>Y</strong> to save, and press <strong>Enter</strong> to overwrite the existing file.</p>

<h2 id="step-three-—-install-ruby">Step Three — Install Ruby</h2>

<p>Ruby is not installed by default on a minimal CentOS 6.5 installation.</p>

<p>To install Ruby:</p>
<pre class="code-pre "><code langs="">yum install ruby
</code></pre>
<h2 id="step-four-—-set-your-hostname">Step Four — Set Your Hostname</h2>

<p>We need to make sure our hostname is configured correctly and resolves to our local machine. If this is configured incorrectly, Puppet will not be able to deploy some required services.</p>

<p>To check the current hostname:</p>
<pre class="code-pre "><code langs="">hostname
</code></pre>
<p>It should show the URL you want to use for the OpenShift control panel. In our case, this is <strong>master.openshift.example.com</strong>.</p>

<p>Open the file <span class="highlight">/etc/sysconfig/network</span>:</p>
<pre class="code-pre "><code langs="">nano /etc/sysconfig/network
</code></pre>
<p>Edit the file to suit your needs:</p>
<pre class="code-pre "><code langs="">NETWORKING=yes
HOSTNAME=<span class="highlight">master.openshift.example.com</span>
</code></pre>
<p>Upon next reboot your hostname will be updated. We will reboot after a few more steps.</p>

<h2 id="step-five-—-make-hostname-resolve-to-localhost">Step Five — Make Hostname Resolve to localhost</h2>

<p>This will ensure that puppet can resolve the hostname correctly during the installation.</p>

<p>Next, open the file /etc/hosts:</p>
<pre class="code-pre "><code langs="">nano /etc/hosts
</code></pre>
<p>Add your hostname to the <span class="highlight">127.0.0.1</span> line:</p>
<pre class="code-pre "><code langs="">127.0.0.1   <span class="highlight">master.openshift.example.com</span> localhost localhost.localdomain
::1         localhost6 localhost6.localdomain6
</code></pre>
<h2 id="step-six-—-enable-selinux">Step Six — Enable SELinux</h2>

<p>SELinux (Security-Enhanced Linux) is a Linux kernel security module that provides a mechanism for supporting access control security policies, including United States Department of Defense–style mandatory access controls (MAC). This kernel module is a requirement for OpenShift to isolate applications securely.</p>

<p>For more information on SELinux, and advanced configurations that should be done before using OpenShift in a production environment, please see the series linked below. While the series is based on CentOS 7, the principles and deployment process are the same.</p>

<ul>
<li><a href="https://indiareads/community/tutorial_series/an-introduction-to-selinux-on-centos-7">An Introduction to SELinux on CentOS 7</a> </li>
</ul>

<p>For the purposes of this tutorial we will enable SELinux by setting it to <code>enforcing</code> mode.</p>

<p>Open <span class="highlight">/etc/sysconfig/selinux</span>:</p>
<pre class="code-pre "><code langs="">nano /etc/sysconfig/selinux
</code></pre>
<p>Change SELinux to <span class="highlight">enforcing</span>:</p>
<pre class="code-pre "><code langs=""># This file controls the state of SELinux on the system.
# SELINUX= can take one of these three values:
#       enforcing - SELinux security policy is enforced.
#       permissive - SELinux prints warnings instead of enforcing.
#       disabled - SELinux is fully disabled.
<span class="highlight">SELINUX=enforcing</span>
# SELINUXTYPE= type of policy in use. Possible values are:
#       targeted - Only targeted network daemons are protected.
#       strict - Full SELinux protection.
SELINUXTYPE=targeted

# SETLOCALDEFS= Check local definition changes
SETLOCALDEFS=0
</code></pre>
<p>Then reboot to enable our settings:</p>
<pre class="code-pre "><code langs="">reboot
</code></pre>
<p>If using SSH, you will have to reconnect after the reboot is complete.</p>

<h2 id="step-seven-—-install-openshift-origin">Step Seven — Install OpenShift Origin</h2>

<p>Now we'll install OpenShift Origin.</p>

<p>We have three options to install OpenShift: curl-to-shell, a portable installer, or installing from source. In this article we will be using the curl-to-shell method for installing OpenShift Origin.</p>

<p><strong>This configuration will take a few minutes, and the installation itself can take up to an hour, although you don't have to babysit the server for that part.</strong></p>

<p>To start the installer:</p>
<pre class="code-pre "><code langs="">sh <(curl -s https://install.openshift.com/)
</code></pre>
<h3 id="optional-installation-options">(Optional) Installation Options</h3>

<p>The command line options are useful for larger and Enterprise deployments. If you have predefined configuration files or have an existing Puppet installation, you can use these options to speed up the installation process. Since this is our first deployment on a single server, we will not be using any of the options listed below. However, it's useful to know what functions these options provide if you need to scale your Openshift deployment in the future.</p>

<p>For more information you can check the <a href="http://openshift.github.io/documentation/oo_install_users_guide.html#installer-command-line-options">official documentation</a>.</p>
<pre class="code-pre "><code langs="">-a  --advanced-mode             Enable access to message server and db server customization
-c  --config-file FILEPATH      The path to an alternate config file
-w  --workflow WORKFLOW_ID      The installer workflow for unattended deployment
    --force                     Ignore workflow warnings and automatically install missing RPMs
-l  --list-workflows            List the workflow IDs for use with unattended deployment
-e  --enterprise-mode           Show OpenShift Enterprise options (ignored in unattended mode)
-s  --subscription-type TYPE    The software source for installation packages
-u  --username USERNAME         Login username
-p  --password PASSWORD         Login password
    --use-existing-puppet       For Origin; do not attempt to install the Puppet module
-d  --debug                     Enable debugging messages
</code></pre>
<h2 id="step-eight-—-answer-installer-questions">Step Eight — Answer Installer Questions</h2>

<p>OpenShift Origin uses an interactive installation process. There are quite a few questions to answer, so pay attention! The questions are shown below, with the user input in <span class="highlight">red</span>.</p>
<pre class="code-pre "><code langs="">Welcome to OpenShift.

This installer will guide you through a basic system deployment, based
on one of the scenarios below.

Select from the following installation scenarios.
You can also type '?' for Help or 'q' to Quit:
1. Install OpenShift Origin
2. Add a Node to an OpenShift Origin deployment
3. Generate a Puppet Configuration File
Type a selection and press <return>: <span class="highlight">1</span>
</code></pre>
<p>The installer will prompt you for an installation scenario. Enter <strong>1</strong> and press <strong>Enter</strong>.</p>

<h3 id="dns-—-install-a-new-dns-server">DNS — Install a new DNS Server</h3>
<pre class="code-pre "><code langs="">----------------------------------------------------------------------
DNS Configuration
----------------------------------------------------------------------

First off, we will configure some DNS information for this system.

Do you want me to install a new DNS server for OpenShift-hosted
applications, or do you want this system to use an existing DNS
server? (Answer 'yes' to have me install a DNS server.) (y/n/q/?) <span class="highlight">y</span>
</code></pre>
<p>For this tutorial we want to deploy a new DNS server, so enter <strong>y</strong> and press <strong>Enter</strong>.</p>

<h3 id="dns-—-application-domain">DNS — Application Domain</h3>
<pre class="code-pre "><code langs="">All of your hosted applications will have a DNS name of the form:
<app_name>-<owner_namespace>.<all_applications_domain>

What domain name should be used for all the hosted apps in your
OpenShift system? |example.com| <span class="highlight">apps.example.com</span>
</code></pre>
<p>Enter the domain you would like to use for your hosted applications, which in this example is <strong>apps.example.com</strong>,  and press <strong>Enter</strong>.</p>

<h3 id="dns-—-openshift-hosts-domain">DNS — OpenShift Hosts Domain</h3>
<pre class="code-pre "><code langs="">Do you want to register DNS entries for your OpenShift hosts with the
same OpenShift DNS service that will be managing DNS records for the
hosted applications? (y/n/q) <span class="highlight">y</span>

What domain do you want to use for the OpenShift hosts? <span class="highlight">openshift.example.com</span>
</code></pre>
<p>Enter the domain you would like to use for your OpenShift Hosts, which in this example is <code>openshift.example.com</code>, and press <strong>Enter</strong>.</p>

<h3 id="dns-—-fqdn-of-the-name-server">DNS — FQDN of the Name Server</h3>
<pre class="code-pre "><code langs="">Hostname (the FQDN that other OpenShift hosts will use to connect to
the host that you are describing): <span class="highlight">master.openshift.example.com</span>
</code></pre>
<p>Since we are hosting the DNS on the same Droplet, we will use this machine's Fully Qualified Domain Name. Enter your host's FQDN, which in this example is <code>master.openshift.example.com</code>, and press <strong>Enter</strong>.</p>

<h3 id="dns-—-ssh-host-name">DNS — SSH Host Name</h3>
<pre class="code-pre "><code langs="">Hostname / IP address for SSH access to master.openshift.example.com
from the host where you are running oo-install. You can say
'localhost' if you are running oo-install from the system that you are
describing: |master.openshift.example.com| <span class="highlight">localhost</span>
Using current user (root) for local installation.
</code></pre>
<p>This is the hostname used to perform the installation of OpenShift. Since we are installing to the same Droplet running the installer, we can use localhost. Enter <code>localhost</code>, and press <strong>Enter</strong>.</p>

<h3 id="dns-—-ip-address-configuration">DNS — IP Address Configuration</h3>

<p>If you have private networking enabled, you will need to use the WAN interface/IP Address for any host you wish to assign the Node Role. Since we are only installing to a single host in this tutorial, make sure you use eth0 as your interface for this host. In a large setup with multiple Brokers and DBServers, you would use the private networking interface for those hosts only. Attempting to use the private interface on a Node will cause an IP address error during deployment.</p>
<pre class="code-pre "><code langs="">Detected IP address 104.131.174.112 at interface eth0 for this host.
Do you want Nodes to use this IP information to reach this host?
(y/n/q/?) <span class="highlight">y</span>

Normally, the BIND DNS server that is installed on this host will be
reachable from other OpenShift components using the host's configured
IP address (104.131.174.112).

If that will work in your deployment, press <enter> to accept the
default value. Otherwise, provide an alternate IP address that will
enable other OpenShift components to reach the BIND DNS service on
this host: |104.131.174.112| <span class="highlight">104.131.174.112</span>

That's all of the DNS information that we need right now. Next, we
need to gather information about the hosts in your OpenShift
deployment.
</code></pre>
<p>For the purposes of this tutorial we will use the default settings, as shown in the image above.</p>

<h3 id="broker-configuration">Broker Configuration</h3>
<pre class="code-pre "><code langs="">----------------------------------------------------------------------
Broker Configuration
----------------------------------------------------------------------
Do you already have a running Broker? (y/n/q) <span class="highlight">n</span>

Okay. I'm going to need you to tell me about the host where you want
to install the Broker.

Do you want to assign the Broker role to master.openshift.example.com?
(y/n/q/?) <span class="highlight">y</span>

Okay. Adding the Broker role to master.openshift.example.com.

That's everything we need to know right now for this Broker.

Do you want to configure an additional Broker? (y/n/q) <span class="highlight">n</span>

Moving on to the next role.
</code></pre>
<p>The installer will now ask us to set up a Broker. In this example we do not have any Brokers yet, so we will install the role on master.openshift.example.com.</p>

<h3 id="node-configuration">Node Configuration</h3>
<pre class="code-pre "><code langs="">----------------------------------------------------------------------
Node Configuration
----------------------------------------------------------------------
Do you already have a running Node? (y/n/q) <span class="highlight">n</span>

Okay. I'm going to need you to tell me about the host where you want
to install the Node.

Do you want to assign the Node role to master.openshift.example.com?
(y/n/q/?) <span class="highlight">y</span>

Okay. Adding the Node role to master.openshift.example.com.

That's everything we need to know right now for this Node.

Do you want to configure an additional Node? (y/n/q) <span class="highlight">n</span>
</code></pre>
<p>The installer will now ask us to set up a Node. In this example we do not have any Nodes yet, so we will install the role on <span class="highlight">master.openshift.example.com</span>. At this point the installer will also ask you to configure the user accounts. In this example we chose to have the installer generate the credentials for us.</p>

<h3 id="username-and-password-configuration">Username and Password Configuration</h3>
<pre class="code-pre "><code langs="">Do you want to manually specify usernames and passwords for the
various supporting service accounts? Answer 'N' to have the values
generated for you (y/n/q) <span class="highlight">n</span>
</code></pre>
<p>If you would like to manually configure the usernames and passwords used for your deployment, you can do that here. In our example we decided to have them automatically generated for us. Enter <strong>n</strong>, and press <strong>Enter</strong>.</p>

<p>Pay attention to the output. You will need the values in the "Account Settings" table later in the tutorial, specifically the <span class="highlight">OpenShift Console User</span> and the <span class="highlight">OpenShift Console Password</span>.</p>
<pre class="code-pre "><code langs="">Account Settings
+----------------------------+------------------------+
| OpenShift Console User     | demo                   |
| OpenShift Console Password | S94XXXXXXXXXXXXXXXH8w  |
...

</code></pre>
<h3 id="finish-deployment">Finish Deployment</h3>
<pre class="code-pre "><code langs="">Host Information
+------------------------------+------------+
| Hostname                     | Roles      |
+------------------------------+------------+
| master.openshift.example.com | Broker     |
|                              | NameServer |
|                              | Node       |
+------------------------------+------------+

Choose an action:
1. Change the deployment configuration
2. View the full host configuration details
3. Proceed with deployment
Type a selection and press <return>: <span class="highlight">3</span>
</code></pre>
<p>When you are satisfied with the configuration, enter <strong>3</strong>, and press <strong>Enter</strong>.</p>

<h3 id="repository-subscriptions">Repository Subscriptions</h3>
<pre class="code-pre "><code langs="">Do you want to make any changes to the subscription info in the
configuration file? (y/n/q/?) <span class="highlight">n</span>

Do you want to set any temporary subscription settings for this
installation only? (y/n/q/?) <span class="highlight">n</span>
</code></pre>
<p>For the purposes of this tutorial we will use the default mirrors. Enter <strong>n</strong> and press <strong>Enter</strong>, for both questions.</p>

<h3 id="pre-flight-check">Pre-Flight Check</h3>
<pre class="code-pre "><code langs="">The following RPMs are required, but not installed on this host:
* puppet
* bind
Do you want to want me to try to install them for you? (y/n/q) <span class="highlight">y</span>
</code></pre>
<p>The installer will now perform a pre-flight check. If you need any packages installed, such as Puppet and BIND in our example, enter <strong>y</strong> and press <strong>Enter</strong>.</p>

<p><em>Note: Once you answer this question, Puppet will run for up to an hour on your server to configure OpenShift Origin.</em></p>

<p>Here's some example output:</p>
<pre class="code-pre "><code langs="">master.openshift.example.com: Running Puppet deployment for host
<^>Error: Could not uninstall module 'openshift-openshift_origin'
  Module 'openshift-openshift_origin' is not installed
master.openshift.example.com: Puppet module removal failed. This is expected if the module was not installed.<^>
master.openshift.example.com: Attempting Puppet module installation (try #1)
<^>Warning: Symlinks in modules are unsupported. Please investigate symlink duritong-sysctl-0.0.5/spec/fixtures/modules/sysctl/manifests->../../../../manifests.
Warning: Symlinks in modules are unsupported. Please investigate symlink duritong-sysctl-0.0.5/spec/fixtures/modules/sysctl/lib->../../../../lib.<^>
master.openshift.example.com: Puppet module installation succeeded.
master.openshift.example.com: Cleaning yum repos.
master.openshift.example.com: Running the Puppet deployment. This step may take up to an hour.
</code></pre>
<p><em>NOTE: Red text in the output is used to highlight errors and warnings.</em></p>

<p>The installer will now perform the rest of the deployment. You may see some warnings during this process (see the image above). These are normal and will not affect the deployment. <strong>This process can take upwards of an hour to complete.</strong></p>

<h3 id="redeploying">Redeploying</h3>

<p>If Puppet did not configure everything correctly the first time, you can re-run the Puppet deployment without running the entire configuration again. If you see an error when you first access the OpenShift Origin dashboard, you'll probably want to do this.</p>

<p>Run the installer again:</p>
<pre class="code-pre "><code langs="">sh <(curl -s https://install.openshift.com/)
</code></pre>
<p>This time, you'll selection the third option, to generate a new Puppet configuration file. Not all of the output is shown below - just the questions and answers.</p>
<pre class="code-pre "><code langs="">Select from the following installation scenarios.
You can also type '?' for Help or 'q' to Quit:
1. Install OpenShift Origin
2. Add a Node to an OpenShift Origin deployment
3. Generate a Puppet Configuration File
Type a selection and press <return>: <span class="highlight">3</span>

Choose an action:
1. Change the deployment configuration
2. View the full host configuration details
3. Proceed with deployment
Type a selection and press <return>: <span class="highlight">3</span>

Do you want to make any changes to the subscription info in the
configuration file? (y/n/q/?) <span class="highlight">n</span>

Do you want to set any temporary subscription settings for this
installation only? (y/n/q/?) <span class="highlight">n</span>
</code></pre>
<p>Make a note of the file name shown in the output:</p>
<pre class="code-pre "><code langs="">Puppt template created at /root/oo_install_configure_master.openshift.example.com.pp
To run it, copy it to its host and invoke it with puppet: `puppet
apply <filename>`.

All tasks completed.
oo-install exited; removing temporary assets.
</code></pre>
<p>Run the Puppet configuration, using the file name you were given:</p>
<pre class="code-pre "><code langs="">puppet apply <span class="highlight">/root/oo_install_configure_master.openshift.example.com.pp</span>
</code></pre>
<h2 id="step-nine-—-test-your-openshift-deployment">Step Nine — Test Your OpenShift Deployment</h2>

<p>Your OpenShift installation is now complete. You can test your OpenShift Deployment by visiting the following url in a web browser. </p>
<pre class="code-pre "><code langs="">https://<span class="highlight">104.131.174.112</span>/
</code></pre>
<p><img src="https://assets.digitalocean.com/articles/configure_openshift/2.png" alt="Browser certificate warning" /></p>

<p>OpenShift will be using a self-signed certificate, so you will have to add an exception for this in your web browser.</p>

<p>If you didn't note the credentials before, scroll back up to the "Account Settings" output section, and use the <span class="highlight">OpenShift Console User</span> and <span class="highlight">OpenShift Console Password</span> to log in.</p>
<pre class="code-pre "><code langs="">Account Settings
+----------------------------+------------------------+
| OpenShift Console User     | <span class="highlight">demo</span>                   |
| OpenShift Console Password | <span class="highlight">tARvXXXXXXXmm5g</span>        |
| MCollective User           | mcollective            |
| MCollective Password       | dtdRNs8i1pWi3mL9JsNotA |
| MongoDB Admin User         | admin                  |
| MongoDB Admin Password     | RRgY8vJd2h5v4Irzfi8kkA |
| MongoDB Broker User        | openshift              |
| MongoDB Broker Password    | 28pO0rU8ohJ0KXgpqZKw   |
+----------------------------+------------------------+
</code></pre>
<p><img src="https://assets.digitalocean.com/articles/configure_openshift/3.png" alt="OpenShift welcome page" /></p>

<p>If you can log into the console but see an error, you may need to redeploy the Puppet configuration. See the previous section for details.</p>

<h2 id="step-ten-—-configure-your-domains-for-openshift">Step Ten — Configure Your Domains for OpenShift</h2>

<p>In general you will want to follow your domain registrar's documentation for creating your DNS entries. We have provided images below for illustrative purposes. For the nameserver domains, you will want to substitute the IP address of your OpenShift host or BIND DNS server. In our example we created two name server records that point to the same IP. This is because most domain registrars will require a minimum of two NS records. In this tutorial we did not setup a Secondary Bind DNS server.</p>

<p><strong>example-dns.com</strong><br />
A Record | ns1.example-dns.com => 104.131.174.112<br />
A Record | ns2.example-dns.com => 104.131.174.112</p>

<p><img src="https://assets.digitalocean.com/articles/configure_openshift/4.png" alt="Nameserver DNS settings" /></p>

<p>Direct the application domain to use the OpenShift DNS servers you just set up.</p>

<p><strong>example.com</strong><br />
NS Record | ns1.example.com.<br />
NS Record | ns2.example.com.</p>

<p><img src="https://assets.digitalocean.com/articles/configure_openshift/5.png" alt="App domain DNS settings" /></p>

<p>Note: For testing purposes, you can also just point your app domain or subdomain to the OpenShift server's IP address, since we're deploying only a single OpenShift Origin server at this time.</p>

<p>Now you will be able to access the OpenShift Console from the domain name of your Broker. In our example we used master.openshift.example.com. You will have to add an exception for the self-signed certificate again with the new domain.</p>

<p>For in-depth information on configuring your DNS records, please see the tutorials listed below.</p>

<p><a href="https://indiareads/community/tutorials/how-to-create-vanity-or-branded-nameservers-with-digitalocean-cloud-servers">How To Create Vanity or Branded Nameservers with IndiaReads Cloud Servers</a> </p>

<p><a href="https://indiareads/community/tutorials/how-to-set-up-and-test-dns-subdomains-with-digitalocean-s-dns-panel">How To Set Up and Test DNS Subdomains with IndiaReads's DNS Panel</a> </p>

<p><a href="https://indiareads/community/tutorials/how-to-point-to-digitalocean-nameservers-from-common-domain-registrars">How to Point to IndiaReads Nameservers From Common Domain Registrars</a> </p>

<h2 id="step-eleven-—-create-your-first-application">Step Eleven — Create Your First Application</h2>

<p><img src="https://assets.digitalocean.com/articles/configure_openshift/6.png" alt="Click "Create your first application now"" /></p>

<p>In the OpenShift Origin console, click <strong>Create your first application now</strong> on the Applications page.</p>

<p><img src="https://assets.digitalocean.com/articles/configure_openshift/7.png" alt="Click "PHP 5.4"" /></p>

<p>Click <strong>PHP 5.4</strong> to select it as your cartridge.</p>

<p><img src="https://assets.digitalocean.com/articles/configure_openshift/8.png" alt="Set the domain name" /></p>

<p>Since this is your first application, you will also have to specify a domain name. In our example we used demo.apps.example.com with the application name of php. The final URL will be php-demo.apps.example.com.</p>

<p>Leave the rest of the default settings.</p>

<p><img src="https://assets.digitalocean.com/articles/configure_openshift/9.png" alt="Click "Create Application"" /></p>

<p>Click <strong>Create Application</strong>. It may take a couple minutes to initialize the application. Once this process is complete, you can click <strong>visit app in the browser</strong> to see the test application. You be presented with the default PHP cartridge page. This page will also give you useful information on how to edit and deploy applications using OpenShift.</p>

<p><img src="https://assets.digitalocean.com/articles/configure_openshift/10.png" alt="PHP application default page" /></p>

<h3 id="conclusion">Conclusion</h3>

<p>We have successfully deployed a single-server Openshift Origin environment. This server has all four OpenShift roles applied to it. It is also configured to be a DNS server. We configured one domain (example-dns.com) used for our nameserver pointers. We configured a second domain (example.com) used to resolve applications and OpenShift hosts.</p>

    