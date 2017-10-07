<link rel='stylesheet' href='digital_ocean.css'><br> <br> 
      <h3 id="introduction">Introduction</h3>

<p>MEAN.JS is a full-stack JavaScript development solution that pulls together some of the best JavaScript technologies so that you can get applications into production quickly and easily.  MEAN.JS consists of MongoDB, ExpressJS, AngularJS, and Node.</p>

<p>In this guide, we will install each of these components onto an Ubuntu 14.04 server.  This will give us the applications and structure we need to create and deploy MEAN apps easily.</p>

<h2 id="prerequisites">Prerequisites</h2>

<p>To begin this guide, you will need to have access to an Ubuntu 14.04 server.</p>

<p>You will need a non-root user account with <code>sudo</code> privileges to correctly install and configure the components we will be working with.  Follow our <a href="https://indiareads/community/tutorials/initial-server-setup-with-ubuntu-14-04">Ubuntu 14.04 initial server setup guide</a> to set up an account of this type.</p>

<p>When you are finished with the initial configuration of your server, log in with your non-root user and continue with this guide.</p>

<h2 id="download-and-install-mongodb-and-dependencies-through-apt">Download and Install MongoDB and Dependencies through Apt</h2>

<p>Throughout this guide, we will be installing software using a number of different techniques depending on each project's requirements.  The first set of installations will use <code>apt</code>, Ubuntu's package management system.</p>

<p>Before we can begin installing software, we will be adding an additional repository with up-to-date MongoDB packages.  This repository is provided by the MongoDB project itself, so it should always have recent, stable versions of MongoDB.</p>

<p>First, we must add the MongoDB team's key to our system's list of trusted keys.  This will allow us to confirm that the packages are genuine.  The following command will add the correct key to our list (if you wish, you can verify the key ID through <a href="http://docs.mongodb.org/manual/tutorial/install-mongodb-on-ubuntu/">MongoDB's official documentation</a>):</p>
<pre class="code-pre "><code langs="">sudo apt-key adv --keyserver hkp://keyserver.ubuntu.com:80 --recv 7F0CEB10
</code></pre>
<p>Now that we trust packages signed by the MongoDB maintainers, we need to add a reference to the actual repository to our <code>apt</code> configuration.  We can create a separate file that will be sourced by <code>apt</code> with the correct repository reference by typing:</p>
<pre class="code-pre "><code langs="">echo 'deb http://downloads-distro.mongodb.org/repo/ubuntu-upstart dist 10gen' | sudo tee /etc/apt/sources.list.d/mongodb.list
</code></pre>
<p>Our system is now configured with the new MongoDB repository.  We can update our system's local package cache so that it knows about the new packages, and then we can install the software we need.</p>

<p>We will be installing MongoDB packages to use as our database, <code>git</code> to help us with later installations, and some packages that we will need as dependencies and build dependencies for Node:</p>
<pre class="code-pre "><code langs="">sudo apt-get update
sudo apt-get install mongodb-org git build-essential openssl libssl-dev pkg-config
</code></pre>
<p>Once the installation is complete, we can move on to building Node.</p>

<h2 id="download-build-and-install-node-from-source">Download, Build, and Install Node from Source</h2>

<p>Node is a very fast-moving project that cuts releases frequently.  In order to get an up-to-date copy of Node, built to run on our specific system, we will be downloading the most recent source and compiling the binary manually.  This is a rather straight forward procedure.</p>

<p>First go to the <a href="http://nodejs.org/download/">download section of the Node website</a>.  In the main section of the page, there are download links separated by operating system, as well as a link for the source code in the upper-right corner of the downloads:</p>

<p><img src="https://assets.digitalocean.com/articles/mean_install_1404/node_source.png" alt="Node source code link" /></p>

<p>Right-click on the source code link and select "Copy link address" or whatever similar option your browser provides.  </p>

<p>Back on your server, move into your home directory and use the <code>wget</code> command to download the source code from the link you just copied.  Your Node source URL will likely be different from the one shown below:</p>
<pre class="code-pre "><code langs="">cd ~
wget http://nodejs.org/dist/v<span class="highlight">0.10.33</span>/node-v<span class="highlight">0.10.33</span>.tar.gz
</code></pre>
<p>Once the file has been download, extract the archive using the <code>tar</code> command:</p>
<pre class="code-pre "><code langs="">tar xzvf node-v*
</code></pre>
<p>This will create the directory structure that contains the source code.  Move into the new directory:</p>
<pre class="code-pre "><code langs="">cd node-v*
</code></pre>
<p>Since we already installed all of the Node dependencies using <code>apt</code> in the last section, we can begin building the software right away.  Configure and build the software using these commands:</p>
<pre class="code-pre "><code langs="">./configure
make
</code></pre>
<p>Once the software is compiled, we can install it onto our system by typing:</p>
<pre class="code-pre "><code langs="">sudo make install
</code></pre>
<p>Node is now installed on our system (along with some helper apps).  Before we continue, we can get rid of both the source code archive and the source directory to keep our system clean:</p>
<pre class="code-pre "><code langs="">cd ~
rm -rf ~/node-v*
</code></pre>
<h2 id="install-the-rest-of-the-components-with-npm-git-and-bower">Install the Rest of the Components with NPM, Git, and Bower</h2>

<p>Now that we have Node installed, we have access to the <code>npm</code> package manager, which we can use to install some of the other software we require.</p>

<p>MEAN.JS uses a separate package manager, called <code>bower</code>, to manage front-end application packages.  It also uses the Grunt Task Runner to automate common tasks.  Since these are management packages that should be available to assist us with every app we create, we should tell <code>npm</code> that we need these globally installed:</p>
<pre class="code-pre "><code langs="">sudo npm install -g bower grunt-cli
</code></pre>
<p>Now, we finally have all of the prerequisite packages installed.  We can move onto installing the actual MEAN.JS boilerplate used to create applications.  We will clone the official GitHub repository into a directory at <code>/opt/mean</code> in order to get the most up-to-date version of the project:</p>
<pre class="code-pre "><code langs="">sudo git clone https://github.com/meanjs/mean.git /opt/mean
</code></pre>
<p>Enter the directory and tell <code>npm</code> to install all of the packages the project references.  We need to use <code>sudo</code> since we are in a system directory:</p>
<pre class="code-pre "><code langs="">cd /opt/mean
sudo npm install
</code></pre>
<p>Finally, since we are operating in a system directory, we need to call <code>bower</code> with <code>sudo</code> and the <code>--allow-root</code> option in order to install and configure our front-end packages:</p>
<pre class="code-pre "><code langs="">sudo bower --allow-root --config.interactive=false install
</code></pre>
<h2 id="see-the-results">See the Results</h2>

<p>MEAN.JS is now completely installed.  We can start up the sample application using the Grunt Task Runner within our project directory.  This will run the application and allow it to begin accepting requests:</p>
<pre class="code-pre "><code langs="">cd /opt/mean
grunt
</code></pre>
<p>Once the process starts, you can visit your server's domain name or IP address in your web browser on port "3000":</p>
<pre class="code-pre "><code langs="">http://<span class="highlight">server_domain_or_IP</span>:3000
</code></pre>
<p>You should see the sample MEAN.JS application:</p>

<p><img src="https://assets.digitalocean.com/articles/mean_install_1404/mean_sample_app.png" alt="MEAN.JS sample application" /></p>

<h2 id="conclusion">Conclusion</h2>

<p>Now that you have the necessary components and the MEAN.JS boilerplate, you can begin making and deploying your own apps.  Check out the <a href="http://meanjs.org/docs.html">documentation on MEAN.JS website for specific help on working with MEAN.JS</a>.</p>

<p>After you get your application up and running, you'll probably want to configure a reverse proxy to your application server in order to feed your app connections.  We will cover this in a later guide.</p>

    