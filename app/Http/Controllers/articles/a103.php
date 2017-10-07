<link rel='stylesheet' href='digital_ocean.css'><br> <br> 
      <h3 id="introduction">Introduction</h3>

<p>AppScale is an open source computing platform designed to deploy Google App Engine applications on public clouds, private clouds, and on-premise clusters. AppScale is fully compatible with the Google App Engine APIs and has support for Python, Go, PHP, and Java. With AppScale you can migrate existing apps to any cloud compute platform, including IndiaReads. Below you will find a list of the open source components used to serve a given API.</p>

<ul>
<li>Datastore API: <a href="http://cassandra.apache.org/">Cassandra</a> and <a href="http://zookeeper.apache.org/doc/trunk/zookeeperOver.html">ZooKeeper</a></li>
<li>Memcache API: <a href="http://memcached.org/">memcached</a></li>
<li>Task Queue API: <a href="https://www.rabbitmq.com/">RabbitMQ</a> and <a href="http://www.celeryproject.org/">Celery</a></li>
<li>XMPP API: <a href="https://www.ejabberd.im/">ejabberd</a></li>
<li>Channel API: <a href="http://strophe.im/strophejs/">strophe.js</a> and <a href="https://www.ejabberd.im/">ejabberd</a></li>
<li>Blobstore API: <a href="http://cassandra.apache.org/">Cassandra</a> and <a href="http://zookeeper.apache.org/doc/trunk/zookeeperOver.html">ZooKeeper</a></li>
<li>Images API: <a href="http://www.pythonware.com/products/pil/">PIL</a></li>
<li>Cron API: <a href="https://wiki.gentoo.org/wiki/Cron#vixie-cron">Vixie Cron</a></li>
</ul>

<h2 id="prerequisites">Prerequisites</h2>

<p>For this tutorial, you will need:</p>

<ul>
<li>4GB+ Droplet with Ubuntu 12.04.5</li>
</ul>

<p>AppScale requires at least 2 GB of RAM to compile the required components in addition to the 2 GB of RAM that AppScale uses at idle. A minimum of 4 GB of RAM is <strong>strongly recommended</strong> for standard application deployments. It may be possible to use a 2 GB Droplet with a swap file. However, that is beyond the scope of this tutorial.</p>

<p>At the time of writing, AppScale only has official support for Ubuntu 12.04. If you modify the build script, it may be possible to install on Ubuntu 14.04. However, that is also outside the scope of this tutorial and may not be supported by the community.</p>

<p>The first 2 steps, installing AppScale and the AppScale Tools, must be run as the root user. The remaining steps can be run as a non-root user.</p>

<h2 id="step-1-—-install-appscale">Step 1 — Install AppScale</h2>

<p>For the first two sections we will want to run all the commands as the root user. If you are connected to the server as a sudo user, enter the root shell with:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo su
</li></ul></code></pre>
<p>First, update your apt-get package index:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">apt-get update
</li></ul></code></pre>
<p>We are now ready to install AppScale. We will be compiling AppScale from source. Please note compiling source code can be very time consuming. Expect this process to take upwards of 15 minutes or longer to complete.</p>

<p>Ensure you are in the <code>/root</code> directory:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">cd /root
</li></ul></code></pre>
<p>Install Git so you can use it to download the AppScale source code:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">apt-get install -y git-core
</li></ul></code></pre>
<p>Clone the AppScale source code from GitHub:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">git clone git://github.com/AppScale/appscale.git
</li></ul></code></pre>
<p>Change to the <code>appscale/debian</code> directory, and run the build script.</p>

<p><span class="note"><strong>Note:</strong> This process will take some time. The build script will install any missing dependencies and compile the AppScale source code.<br /></span></p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">cd appscale/debian
</li><li class="line" prefix="$">bash appscale_build.sh
</li></ul></code></pre>
<h2 id="step-2-—-install-appscale-tools">Step 2 — Install AppScale Tools</h2>

<p>The AppScale Tools are used to manage AppScale Clusters and deploy applications. These tools can be installed on a local machine or your server. For simplicity we will be installing the tools on our server. The install process on Mac OS X and Windows is very similar. You need to use <a href="https://www.cygwin.com/">Cygwin</a>  on Windows. See the <a href="https://github.com/AppScale/appscale-tools">GitHub Page</a> for more information.</p>

<p>Change back to the <code>/root</code> directory:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">cd /root
</li></ul></code></pre>
<p>Clone the AppScale Tools source code from GitHub:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">git clone git://github.com/AppScale/appscale-tools.git
</li></ul></code></pre>
<p>Change to the <code>appscale-tools/debian</code> directory, and run the build script.</p>

<p><span class="note"><strong>Note:</strong> This process will take some time. The build script will install any missing dependencies and compile the AppScale Tools source code.<br /></span></p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">cd appscale-tools/debian
</li><li class="line" prefix="$">bash appscale_build.sh
</li></ul></code></pre>
<p>After the build script completes, it would be a good idea to reboot.</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">reboot
</li></ul></code></pre>
<h2 id="step-3-—-configure-your-appscale-deployment">Step 3 — Configure Your AppScale Deployment</h2>

<p>For the remaining portion of this tutorial, you can run the AppScale Tools as any user. This does not need to be a sudo user. However, you will need to know the root user's password when starting AppScale for the first time. AppScale will automatically create authentication certificates, and the root password will no longer be required when working with the AppScale Tools in the future.</p>

<p>After the server has finished rebooting, and you have established your SSH connection, you need to configure your AppScale deployment. The AppScale Tools require a configuration file every time you run the toolset. In this step we will create the configuration file called <code>AppScalefile</code>, start AppScale, and configure the administrator account.</p>

<p>Ensure you are in your user's home directory:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">cd ~
</li></ul></code></pre>
<p>Create the initial <code>AppScalefile</code> configuration file:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">appscale init cluster
</li></ul></code></pre>
<p>Now, we will add the server's IP address to the <code>AppScalefile</code>.</p>

<p>Open the file with nano:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">nano AppScalefile
</li></ul></code></pre>
<p>At the top of the file, you will see the following section:</p>
<div class="code-label " title="AppScalefile">AppScalefile</div><pre class="code-pre "><code langs=""># The deployment strategy (roles -> machines) that should be used in this
# AppScale deployment.
# The following is a sample layout for running everything on one machine:
ips_layout :
  master : <span class="highlight">your_server_ip</span>
  appengine : <span class="highlight">your_server_ip</span>
  database : <span class="highlight">your_server_ip</span>
  zookeeper : <span class="highlight">your_server_ip</span>
</code></pre>
<p>Replace the default IP address with your server's IP address. When you are done editing the file, press <strong>CTRL-X</strong>, press <strong>Y</strong> to save, and press <strong>ENTER</strong> to overwrite the existing file name.</p>

<p>Now we can start AppScale from the same directory as the <code>AppScalefile</code> just created:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">appscale up
</li></ul></code></pre>
<p>AppScale will ask you to verify the host fingerprint and root password. </p>
<pre class="code-pre "><code langs="">The authenticity of host '111.111.111.111 (111.111.111.111)' can't be established.
ECDSA key fingerprint is ab:3a:f0:87:c8:4e:8c:ba:59:0e:06:64:1b:f6:fe:e8.
Are you sure you want to continue connecting (yes/no)? <span class="highlight">yes</span>
</code></pre>
<p>Type <code>yes</code>, and press <strong>ENTER</strong>. You will then see the following:</p>
<pre class="code-pre "><code langs="">root@111.111.111.111's password: 
</code></pre>
<p>Enter the root user password, and press <strong>ENTER</strong>.</p>

<p>After entering the correct root password, you will see the following:</p>
<pre class="code-pre "><code langs="">Generated a new SSH key for this deployment at /root/.appscale/appscale69de89364b624a8a9be1b7f45ac23d40
Starting AppScale 2.3.1 over a virtualized cluster.
Log in to your head node: ssh -i /root/.appscale/appscale69de89364b624a8a9be1b7f45ac23d40.key root@111.111.111.111
Head node successfully initialized at 111.111.111.111. It is now starting up cassandra.
Copying over deployment credentials
Starting AppController at 111.111.111.111
Please wait for the AppController to finish pre-processing tasks.

Please wait for AppScale to prepare your machines for use.
AppController just started
</code></pre>
<p>When starting AppScale, it may seem like it is hung at <code>AppController just started</code>. This is normal. It can take some time for all the AppScale components to initialize.</p>

<p>Eventually, you will see the following:</p>
<pre class="code-pre "><code langs="">UserAppServer is at 111.111.111.111
Enter your desired admin e-mail address:
</code></pre>
<p>Create an admin user account. Enter the email address for the user, and provide a password. Remember these details. You will need them to access the AppScale Administration Panel.</p>

<p>After you create the admin user account, you will see:</p>
<pre class="code-pre "><code langs="">Creating new user account admin@example.com
Creating new user account admin@example.com
Your XMPP username is admin@111.111.111.111
Granting admin privileges to admin@example.com
AppScale successfully started!
View status information about your AppScale deployment at http://111.111.111.111:1080/status
</code></pre>
<p>AppScale will provide you with a link to the administration panel. Usually in the following format. Typically the http address will automatically redirect to the secure https address.</p>
<pre class="code-pre "><code langs="">http://<span class="highlight">your_server_ip</span>:1080/status
https://<span class="highlight">your_server_ip</span>:1443/status
</code></pre>
<h2 id="step-4-—-the-appscale-administration-panel">Step 4 — The AppScale Administration Panel</h2>

<p>Open the AppScale Administration Panel in your browser. The link should have been provided to you after starting AppScale:</p>
<pre class="code-pre "><code langs="">http://<span class="highlight">your_server_ip</span>:1080/status
</code></pre>
<p>You may be prompted to accept the self signed certificate.</p>

<p><img src="https://assets.digitalocean.com/articles/AppScale_Ubuntu_1204/self-signed-cert.png" alt="Accept the Self-Signed Certificate" /> </p>

<p>From the AppScale Administration Panel, users can create their own accounts by clicking <strong>Create Account</strong>. However, you will need to change their permissions using the admin account before they can upload and remove their own apps.</p>

<p>Click the <strong>Login</strong> button in the top right. The <strong>Login</strong> button may look different on smaller screens, but it will still be green.</p>

<p><img src="https://assets.digitalocean.com/articles/AppScale_Ubuntu_1204/appscale-login.png" alt="Log into AppScale" /> </p>

<p>Login with the admin email and password you set in the previous step. You will then be presented with the AppScale status page.</p>

<p><img src="https://assets.digitalocean.com/articles/AppScale_Ubuntu_1204/appscale-status-page.png" alt="AppScale Status Page" /> </p>

<p>The Administration Panel gives you access to server statistics and application statistics. You can also deploy and remove applications. It is fairly straight forward to deploy an application from the Administration Panel. For the purposes of this tutorial we will learn how to deploy an application from the command line. When you are finished exploring the Administration Panel, continue to the next step.</p>

<h2 id="step-5-—-deploying-your-first-application">Step 5 — Deploying Your First Application</h2>

<p>AppScale provides a collection of sample applications that are ready to deploy. These applications are a good way to test your AppScale cluster. They also familiarize you with the application deployment process.</p>

<p>You should be using the same user account, and your current directory should contain the <code>AppScaleFile</code>. This file contains all the configurations AppScale requires to manage you deployment.</p>

<p>Make sure we are back in your user's home directory:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">cd ~
</li></ul></code></pre>
<p>From GitHub, clone the sample application source code to create the Guestbook App:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">git clone https://github.com/AppScale/sample-apps.git
</li></ul></code></pre>
<p>You will see the following as the source code downloads:</p>
<pre class="code-pre "><code langs="">Cloning into 'sample-apps'...
remote: Counting objects: 15742, done.
remote: Total 15742 (delta 0), reused 0 (delta 0), pack-reused 15742
Receiving objects: 100% (15742/15742), 318.96 MiB | 23.52 MiB/s, done.
Resolving deltas: 100% (4944/4944), done.
</code></pre>
<p>The Guestbook App is a great way to test the datastore and authentication APIs.</p>

<p>Deploy the application:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">appscale deploy sample-apps/go/go-guestbook/
</li></ul></code></pre>
<p>You will be asked to assign an email address to your application. Enter the email address, and press <strong>Enter</strong>.</p>
<pre class="code-pre "><code langs="">Enter your desired e-mail address: <span class="highlight">admin@example.com</span>
</code></pre>
<p>This can be any email address. If the user does not already exist in the database, you will be prompted to set a password. For the purposes of this tutorial we decided to use the admin account.</p>

<p>Next, you will see the following:</p>
<pre class="code-pre "><code langs="">Uploading initial version of app guestbookgo
We have reserved guestbookgo for your app
Tarring application
Copying over application
Please wait for your app to start serving.
Waiting 1 second(s) to check on application...
Waiting 2 second(s) to check on application...
Waiting 4 second(s) to check on application...
Waiting 8 second(s) to check on application...
Waiting 16 second(s) to check on application...
Your app can be reached at the following URL: http://111.111.111.111:8080
</code></pre>
<p>Open the URL provided in your browser, and you will be served by the Guestbook App. If you are still signed into AppScale, the Guestbook App will use your email address. If you go back to the AppScale Administration Panel and logout, it will sign the guestbook as an anonymous user.</p>

<p><img src="https://assets.digitalocean.com/articles/AppScale_Ubuntu_1204/guestbook-app.png" alt="Guestbook App" /> </p>

<p>To update an application, simply use the <code>appscale deploy</code> command again. AppScale will automatically detect and update the existing application. You must use the same email address that already owns the application. If you want to change ownership, you can remove and redeploy the application.</p>

<p>If you want to run multiple versions of the same application side-by-side, you will need to change the name of the app in the <code>app.yaml</code> file. This is the main configuration file for the application, and it is located in the root directory of the application.</p>

<p>To remove an application you can use the following command (substitute <code>guestbookgo</code> with the ID AppScale assigned to your app during the deployment process):</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">appscale remove <span class="highlight">guestbookgo</span>
</li></ul></code></pre>
<p>You can also remove and deploy your applications from the AppScale Administration Panel.</p>

<h2 id="troubleshooting">Troubleshooting</h2>

<p>AppScale is a very complicated platform, and things can go wrong. We will cover a few steps you can take to help solve some of the most common errors. It is recommended you read the official <a href="https://github.com/AppScale/appscale/wiki/Troubleshooting">AppScale Troubleshooting Page</a> for more details.</p>

<p>If you cannot find a solution to your problem, AppScale has a very active <a href="https://groups.google.com/forum/#!forum/appscale_community">mailing list</a>. Make sure when submitting a topic to the mailing list you include as much detail as possible and a copy of your log files. You will be more likely to receive a quick solution to your problem.</p>

<h3 id="forcefully-cleaning-up-appscale-state">Forcefully Cleaning Up AppScale State</h3>

<p>The <code>appscale clean</code> command is used to forcefully bring your VMs to a clean state, removing any configuration problems.</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">appscale clean
</li></ul></code></pre>
<p>This script will also forcefully kill all AppScale related processes. If you are having problems with an initial deployment, always try this first before contacting the mailing list. This command will usually fix any configuration problems. You can then run <code>appscale up</code> again to re-deploy AppScale.</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">appscale up
</li></ul></code></pre>
<h3 id="appscale-log-files">AppScale Log Files</h3>

<p>The <code>appscale logs</code> command will gather the logs files from all nodes in an AppScale deployment and copy them to the specified directory.</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">appscale logs <span class="highlight">directory/</span>
</li></ul></code></pre>
<p>The log files can be accessed directly in the <code>/var/log/appscale</code> directory. </p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">cd /var/log/appscale
</li></ul></code></pre>
<p>If for some reasons the <code>appscale logs</code> command fails, you will want to access the logs in this manner. However, for a multi-node deployment you will need to do this on every server, which is why it's recommended you use the AppScale Tools to gather the log files.</p>

<p>The <code>appscale tail</code> command will provide a real-time readout of the AppScale logs in a deployment. This is useful for monitoring application and connection problems in real-time.</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">appscale tail
</li></ul></code></pre>
<h3 id="debugging-appscale-deployments">Debugging AppScale Deployments</h3>

<p>There are three main logs we should be interested in while debugging an AppScale Deployment.</p>

<ul>
<li><p><code>controller-17443.log</code> — This log is the output of the AppController, the provisioning daemon of AppScale. Since this daemon is responsible for starting all the required services of AppScale, it is the best place start when having problems with an AppScale deployment.</p></li>
<li><p><code>app___app_id-*.log</code> — Every deployed application will have its own log file. If you are having problems deploying an application, or it is not behaving as expected, this is where you will want to start.</p></li>
<li><p><code>datastore_server-400*.log</code> — This is the log file for the AppScale datastore.</p></li>
</ul>

<h2 id="conclusion">Conclusion</h2>

<p>We have installed and configured AppScale for a single server deployment. We learned how to deploy and remove applications. We also put our deployment to the test by signing the Guestbook App. Signing the Guestbook App proved that a number of APIs are functioning correctly. We can now use this AppScale installation to deploy custom applications based on Google App Engine.</p>

    