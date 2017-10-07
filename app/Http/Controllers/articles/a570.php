<link rel='stylesheet' href='digital_ocean.css'><br><img src=https://community-cdn-digitalocean-com.global.ssl.fastly.net/assets/tutorials/images/large/Deis-Cluster_twitter.png?1426699740/> <br> 
      <h3 id="introduction">Introduction</h3>

<p>Deis is an open source private Platform as a Service (PaaS) that simplifies deploying and managing your applications on your own servers. By leveraging technologies such as Docker and CoreOS, Deis provides a workflow and scaling features that are similar to that of Heroku, on the hosting provider of your choice. Deis supports applications that can run in a Docker container, and can run on any platform that supports CoreOS.</p>

<p>In this tutorial, we will show you how to set up your own 3-machine Deis platform cluster on IndiaReads.</p>

<p><span class="note"><strong>Note:</strong> This tutorial is based on the v1.9.0 release of Deis.<br /></span></p>

<h2 id="prerequisites">Prerequisites</h2>

<p>To complete this guide you must have the following:</p>

<ul>
<li>The ability to create at least <strong>3 Droplets</strong> that have at least <strong>4GB memory</strong> and <strong>40GB disk space</strong>. These will be the CoreOS machines that the Deis platform will be installed on</li>
<li>SSH Key(s) to add to the droplets, which is used for authentication when using the <code>deisctl</code> tool</li>
<li>SSH Key to authenticate against Deis' builder</li>
<li>A domain to point to the cluster machines with--this tutorial will use <code>example.com</code> as its domain. An alternative is to use <a href="http://xip.io">xip</a></li>
</ul>

<p>This tutorial is based heavily on <a href="https://indiareads/community/tutorials/how-to-set-up-a-coreos-cluster-on-digitalocean">How To Set Up a CoreOS Cluster on IndiaReads</a>. Review it if you have trouble following the steps in this tutorial related to creating a CoreOS cluster.</p>

<p>We will also install the <a href="https://github.com/deis/deis/tree/master/deisctl#installation">Deis Control Utility</a>, <code>deisctl</code>, and the <a href="https://github.com/deis/deis">Deis client</a>, <code>deis</code>, on a local Mac OS X computer but you may install them elsewhere, such as an Ubuntu 14.04 Droplet, if you wish. The private SSH key that corresponds with the public SSH key used to create the CoreOS Droplets must exist on the computer where <code>deisctl</code> is installed.</p>

<h2 id="generate-a-new-discovery-url">Generate a New Discovery URL</h2>

<p>To create the CoreOS cluster that we will install the Deis platform on, we first need to generate a new discovery URL, a unique address that stores peer CoreOS addresses and metadata. The easiest way to do this is to use <code>https://discovery.etcd.io</code>, a free discovery service. A new discovery URL by visiting <a href="https://discovery.etcd.io/new?size=3">https://discovery.etcd.io/new?size=3</a> in a web browser or by running the following <code>curl</code> command:</p>
<pre class="code-pre "><code langs="">curl -w "\n" "https://discovery.etcd.io/new?size=3"
</code></pre>
<p>Either method will return a unique and fresh discovery URL that looks something like the following (the highlighted part will be a unique token):</p>
<pre class="code-pre "><code langs="">https://discovery.etcd.io/<span class="highlight">f8d48be35b794da45e249bb149729a27</span>
</code></pre>
<p>You will use your resulting discovery URL to create your new Deis platform cluster. The same discovery URL must be specified in the <code>etcd</code> section of the cloud-config of each CoreOS server that you want to add to a particular Deis platform cluster.</p>

<p>Now that we have a discovery URL, let's look at adding it to the <code>cloud-config</code> that we will use to create each machine of our Deis cluster.</p>

<h2 id="deis-user-data">Deis User Data</h2>

<p>To create a cluster of CoreOS machines that will be used in our Deis cluster, we will use a special <code>cloud-config</code> file that is supplied by the maintainers of Deis. This file is available at the Deis GitHub repository:</p>

<p><a href="https://raw.githubusercontent.com/deis/deis/master/contrib/coreos/user-data.example">Deis User Data</a></p>

<p>Open the supplied user data in a text editor, and find the line where the <em>discovery URL</em> is defined. It will look like this:</p>
<pre class="code-pre "><code langs="">        --discovery <span class="highlight">#DISCOVERY_URL</span>
</code></pre>
<p>Uncomment it, and replace highlighted <code>#DISCOVERY_URL</code> with the unique discovery URL that you generated in the previous step.</p>

<p>This tutorial is based on Deis 1.9.0, but the version specified in the sample user data could be more current. Look for the following line (the highlighted numbers represent the version):</p>
<pre class="code-pre "><code langs="">ExecStart=/usr/bin/sh -c 'curl -sSL --retry 5 --retry-delay 2 http://deis.io/deisctl/install.sh | sh -s <span class="highlight">1.9.0</span>'
</code></pre>
<p>Take note of the version number. Be sure to use the version in the user data when installing <code>deisctl</code> and <code>deis</code> in the following steps.</p>

<p>You may save this user data file somewhere if you wish.</p>

<h2 id="create-coreos-droplets">Create CoreOS Droplets</h2>

<p>Now that we have the <em>user data</em> that we will use, let's create the CoreOS machines now. If you are unfamiliar with how to provide a <code>cloud-config</code> file via <em>user data</em> during Droplet creation, please refer to the <a href="https://indiareads/community/tutorials/how-to-set-up-a-coreos-cluster-on-digitalocean#how-to-provide-cloud-config-on-digitalocean">How to Provide Cloud-Config</a> section of our CoreOS clustering tutorial.</p>

<p>Create at least three Droplets, <strong>in the same region</strong>, with the following specifications:</p>

<ul>
<li> At least <strong>4GB memory</strong> and <strong>40GB disk space</strong></li>
<li><strong>Private Networking</strong> enabled, if available. If private networking is not available in your selected region, edit the user data and replace every occurrence of <code>$private_ipv4</code> with <code>$public_ipv4</code></li>
<li><strong>Enable User Data</strong> and enter the user data from the previous step</li>
<li>Select <strong>CoreOS Stable channel</strong> as the Linux distribution</li>
<li>Select your SSH key from the list</li>
</ul>

<p>If you would prefer to use the <code>docl</code> convenience tool to provision your Droplets, follow the instructions <a href="http://docs.deis.io/en/latest/installing_deis/digitalocean/">here</a>.</p>

<p>Once you have created at least three of these machines, you should have a CoreOS cluster that is ready for the Deis platform installation. Let's move onto setting up the DNS.</p>

<h2 id="configure-dns">Configure DNS</h2>

<p>Deis requires a wildcard DNS record to function properly. If the top-level domain (TLD) that you are using is <code>example.com</code>, your applications will exist at the <code>*.example.com</code> level. For example, an application called "app" would be accessible via <code>app.example.com</code>.</p>

<p>One way to configure this on IndiaReads is to setup round-robin DNS via the <a href="https://cloud.digitalocean.com/domains">DNS control panel</a>. To do this, add the following records to your domain:</p>

<ul>
<li>A wildcard CNAME record at your top-level domain, i.e. a CNAME record with <code>*</code> as the name, and <code>@</code> as the canonical hostname</li>
<li>For each CoreOS machine created, an A-record that points to the TLD, i.e. an A-record named <code>@</code>, with the Droplet's public IP address</li>
</ul>

<p>The zone file will now have the following entries in it: (your IP addresses will be different)</p>
<pre class="code-pre "><code langs=""><div class="secondary-code-label " title="DNS Records 1 of 2">DNS Records 1 of 2</div>*   CNAME   @
@   IN A    <span class="highlight">104.131.93.162</span>
@   IN A    <span class="highlight">104.131.47.125</span>
@   IN A    <span class="highlight">104.131.113.138</span>
</code></pre>
<p>In this example, the Deis router/controller will be reachable via <code>deis.example.com</code>.</p>

<p>For convenience, you can also set up DNS records for each node:</p>
<pre class="code-pre "><code langs=""><div class="secondary-code-label " title="DNS Records 2 of 2">DNS Records 2 of 2</div>deis-1  IN A    <span class="highlight">104.131.93.162</span>
deis-2  IN A    <span class="highlight">104.131.47.125</span>
deis-3  IN A    <span class="highlight">104.131.113.138</span>
</code></pre>
<p>If you need help using the DNS control panel, check out <a href="https://indiareads/community/tutorials/how-to-set-up-a-host-name-with-digitalocean">this tutorial</a>.</p>

<h3 id="using-xip-io">Using xip.io</h3>

<p>If you do not want to set up your own domain, you may use <code>xip.io</code> to provide your wildcard DNS. Basically, this will allow you to use your Droplet's IP address appended by ".xip.io" as a wildcard domain that resolves to the IP address.</p>

<p>After you install the Deis platform on your cluster, determine which CoreOS machine is running the <code>deis-router@1.service</code> unit with <code>deisctl list</code>—we will install <code>deisctl</code> in the next section. Then determine the public IP address of that machine. If it is <code>104.131.47.125</code>, you may use the following domain to reach the Deis router:</p>
<pre class="code-pre "><code langs=""><div class="secondary-code-label " title="xip.io example">xip.io example</div>deis.<span class="highlight">104.131.47.125</span>.xip.io
</code></pre>
<h2 id="apply-security-group-settings">Apply Security Group Settings</h2>

<p>IndiaReads Droplets do not have firewalls enabled by default, so we should add some <code>iptables</code> rules to ensure that our components are not accessible to outsiders. The Deis repository provides a script, which can be found <a href="https://github.com/deis/deis/blob/master/contrib/util/custom-firewall.sh">here</a> that can do just that.</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">curl -O https://raw.githubusercontent.com/deis/deis/master/contrib/util/custom-firewall.sh
</li></ul></code></pre>
<p>After reviewing the contents of the script, execute it on each of your servers. For example, using the DNS entries we created earlier, we would run these commands:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">ssh core@<span class="highlight">deis-1.example.com</span> 'bash -s' < custom-firewall.sh
</li><li class="line" prefix="$">ssh core@<span class="highlight">deis-2.example.com</span> 'bash -s' < custom-firewall.sh
</li><li class="line" prefix="$">ssh core@<span class="highlight">deis-3.example.com</span> 'bash -s' < custom-firewall.sh
</li></ul></code></pre>
<p>Be sure to run the script on all of your servers.</p>

<h2 id="install-deis-tools">Install Deis Tools</h2>

<p>Now that we have the CoreOS cluster set up, we will install the Deis Control Utility and Client. You should install these tools on the computer that you want to control your Deis cluster from. We will demonstrate installing them on a separate Ubuntu 14.04 Droplet, but you can install them anywhere you wish.</p>

<h3 id="install-deis-control-utility">Install Deis Control Utility</h3>

<p>The Deis Control Utility allows you to interact with the Deis machines, and install the Deis platform on the cluster.</p>

<p>Change to the directory where you would like to install the <code>deisctl</code> binary. Then, install the Deis Control Utility by downloading and running the install script with the following command (replace the version with the number that was found in the user data):</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">/bin/bash -c 'curl -sSL http://deis.io/deisctl/install.sh | sh -s <span class="highlight">1.9.0</span>'
</li></ul></code></pre>
<p>This installs <code>deisctl</code> to the current directory, and refreshes the Deis unit files.</p>

<p>Let's link it to <code>/usr/local/bin</code>, so it will be in our <code>PATH</code>:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo ln -fs $(pwd)/deisctl /usr/local/bin/deisctl
</li></ul></code></pre>
<p>Now you may use the <code>deisctl</code> command.</p>

<h3 id="install-deis-client">Install Deis Client</h3>

<p>The Deis client, also known as the Deis command-line interface, allows you to interact with the Deis controller unit.</p>

<p>Change to the directory where you would like to install the <code>deis</code> binary. Install the Deis client by downloading and running the install script with the following command (replace the version with the number that was found in the user data):</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">/bin/bash -c 'curl -sSL http://deis.io/deis-cli/install.sh | sh -s <span class="highlight">1.9.0</span>'
</li></ul></code></pre>
<p>This installs <code>deis</code>, which is the client to the current directory. Let's link it to <code>/usr/local/bin</code>, so it will be in our <code>PATH</code>:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo ln -fs $(pwd)/deis /usr/local/bin/deis
</li></ul></code></pre>
<p>The Deis client is installed. Now you may use the <code>deis</code> command.</p>

<h2 id="provision-deis-platform">Provision Deis Platform</h2>

<p>From the computer you installed the Deis tools on, we will provision the Deis platform.</p>

<p>Ensure your SSH agent is running (and select the private key that corresponds to the SSH keys added to your CoreOS Droplets):</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">eval `ssh-agent -s`
</li><li class="line" prefix="$">ssh-add ~/.ssh/<span class="highlight">id_rsa_deis</span>
</li></ul></code></pre>
<p>Next, we must export the <code>DEISCTL_TUNNEL</code> to point to one of our Deis machines, by name or public IP address. If you set up the "convenience" DNS records, you may use one of those for the tunnel. For example:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">export DEISCTL_TUNNEL=<span class="highlight">deis-1.example.com</span>
</li></ul></code></pre>
<p>This is where <code>deisctl</code> will attempt to communicate with the cluster. You can test that it is working properly by running <code>deisctl list</code>. If you see a single line of output, the control utility is communicating with the CoreOS machine that was specified.</p>

<p>Before provisioning the platform, we'll need to add an SSH key to Deis, so it can connect to the remote hosts during <code>deis run</code>:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">deisctl config platform set sshPrivateKey=~/.ssh/<span class="highlight">id_rsa_deis</span>
</li></ul></code></pre>
<p>We'll also need to tell the controller which domain name we are deploying applications under:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">deisctl config platform set domain=<span class="highlight">example.com</span>
</li></ul></code></pre>
<p>Once the prior configuration commands have been run, use this command to provision the Deis platform:</p>
<pre class="code-pre "><code langs="">deisctl install platform
</code></pre>
<p>You will see output like the following, which indicates that the units required to run Deis have been loaded on the CoreOS cluster:</p>
<pre class="code-pre "><code langs=""><div class="secondary-code-label " title="Install Output:">Install Output:</div>● ▴ ■
■ ● ▴ Installing Deis...
▴ ■ ●
Storage subsystem...
deis-store-metadata.service: loaded
...
Done.

Please run `deisctl start platform` to boot up Deis.
</code></pre>
<p>Run this command to start the Deis platform:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">deisctl start platform
</li></ul></code></pre>
<p>Once you see "Deis started.", your Deis platform is running on a cluster!</p>

<p>You may verify that all of the Deis units are <em>loaded</em> and <em>active</em> by running the following command:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">deisctl list
</li></ul></code></pre>
<p>All of the units should be active.</p>

<h3 id="install-deis-store-admin-optional">Install Deis-store-admin (Optional)</h3>

<p>You may want to install the <code>deis-store-admin</code> component before moving on. It is often helpful when diagnosing storage issues.</p>

<p>To install the component, run these commands:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">deisctl install store-admin
</li><li class="line" prefix="$">deisctl start store-admin
</li></ul></code></pre>
<p>Now that you've finished provisioning a cluster, register a Deis admin user to get started using the platform!</p>

<h2 id="register-a-deis-user">Register a Deis User</h2>

<p>Now that the Deis platform is running, we must register a user with the <code>deis</code> command. The <code>deis</code> command communicates with the Deis controller via the <em>router</em> unit, which is accessible at <code>deis.example.com</code> in our example:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">deis register http://deis.<span class="highlight">example.com</span>
</li></ul></code></pre>
<p>You will be prompted for a <strong>username</strong>, <strong>password</strong>, and <strong>email address</strong>. After providing these items, you will be logged into the Deis platform automatically.</p>

<p>Next, add the proper SSH key to <code>deis</code>. Enter the following command:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">deis keys:add
</li></ul></code></pre>
<p>You will be prompted to select an SSH key from your available keys. Select the key you would like to add.</p>

<h3 id="log-in-to-deis">Log in to Deis</h3>

<p>If you need to log in later, use the following command:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">deis login http://deis.<span class="highlight">example.com</span>
</li></ul></code></pre>
<p>You will be prompted for the login you created earlier.</p>

<h2 id="deploy-sample-application-optional">Deploy Sample Application (Optional)</h2>

<p>Deis supports three different ways of building applications:</p>

<ol>
<li>Heroku Buildpacks</li>
<li>Dockerfiles</li>
<li>Docker Images</li>
</ol>

<p>We will demonstrate how to use the Heroku Buildpack workflow to deploy an application using <a href="https://github.com/deis/example-ruby-sinatra">example-ruby-sinatra</a>, which is provided by Deis. </p>

<p>Change to a directory that you want to download the example application to. After you are in the desired location, run this command to clone the git repository:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">git clone https://github.com/deis/example-ruby-sinatra.git
</li><li class="line" prefix="$">cd example-ruby-sinatra
</li></ul></code></pre>
<p>The <code>deis create</code> command can be used to create the application on the Deis controller. Run it now:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">deis create
</li></ul></code></pre>
<p>This creates an application, and names it with Deis' automatic naming algorithm:</p>
<pre class="code-pre "><code langs=""><div class="secondary-code-label " title="deis create output:">deis create output:</div>Creating application... done, created <span class="highlight">dapper-yachting</span>
Git remote deis added
</code></pre>
<p>In this case, the application's name is <code>dapper-yachting</code>.</p>

<p>Now, to deploy the application, use <code>git push deis master</code>. Do it now:</p>
<pre class="code-pre commmand"><code langs="">git push deis master
</code></pre>
<p>After running the command to deploy, you will see many lines of output. Once it completes, the output will say the application has been deployed, and it will tell you its automatically generated name, and where it can be accessed:</p>
<pre class="code-pre "><code langs=""><div class="secondary-code-label " title="git push deis master output:">git push deis master output:</div>...
-----> Launching...
       done, <span class="highlight">dapper-yachting</span>:v2 deployed to Deis

       <span class="highlight">http://dapper-yachting.example.com</span>

       To learn more, use `deis help` or visit http://deis.io

To ssh://git@deis.example.com:2222/dapper-yachting.git
 * [new branch]      master -> master
</code></pre>
<p>In this example, the URL is <code>http://dapper-yachting.dev.example.com</code>, which is the application name combined with the cluster name.</p>

<p>You may test that it works by going to the application URL in a web browser, or by using the following curl command (substitute in your own URL):</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">curl http://<span class="highlight">dapper-yachting.dev.example.com</span>
</li></ul></code></pre>
<p>You should see output that looks similar to the following:</p>
<pre class="code-pre "><code langs=""><div class="secondary-code-label " title="App output:">App output:</div>Powered by Deis! Running on container ID a0d35733aad8
</code></pre>
<p>The sample application looks up the container ID of where it is running, and outputs it. Congrats! Your Deis platform works!</p>

<h2 id="conclusion">Conclusion</h2>

<p>Now that you have a working Deis setup, try deploying Deis applications using the other two workflows. The <em>dockerfile</em> workflow is similar to the Heroku Buildpack flow, in that it also uses <code>git push</code> to deploy, while the <em>docker image</em> workflow uses <code>deis pull</code> to deploy. Also, Deis provides much more functionality than was covered here--check out <a href="http://docs.deis.io/en/latest/">their documentation</a> to learn more!</p>

    