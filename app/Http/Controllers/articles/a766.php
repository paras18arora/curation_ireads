<link rel='stylesheet' href='digital_ocean.css'><br> <br> 
      <h3 id="an-article-from-azk">An Article from <a href="http://www.azk.io/">azk</a></h3>

<h3 id="introduction">Introduction</h3>

<p><a href="https://github.com/azukiapp/azk">azk</a> is a lightweight open-source tool you can use to orchestrate application environments.</p>

<p>Have you ever gotten an application running on your local workstation, only to find the setup is completely different when you deploy it to a production server?</p>

<p>This article introduces an orchestration tool called azk, currently implemented for <a href="http://run.azk.io/">these applications</a>, but applicable to many more. When you deploy an azk-ready application, you can run it locally <strong>and</strong> in production with minimal fuss.</p>

<p>azk makes it quick and easy to run not just the application but all of its dependencies, including the required OS, languages, frameworks, databases and other dependencies (an otherwise labor-intensive, repetitive, long, and thus error-prone task), whether you're on your local environment or a server.</p>

<p>The purpose of this article is to show how azk works as an orchestration tool, using a simple Rails app called <a href="https://github.com/run-project/stringer">Stringer</a> as an example.</p>

<p>azk takes care of many steps behind the scenes to make orchestration easier. So, this tutorial contains a few optional steps which are not strictly necessary to set up the sample app, but explain what azk is doing.</p>

<p>We'll run the app from source code on our local computer, deploy it to a server, make some local changes, deploy the changes, and demonstrate a rollback.</p>

<p>After completing this article, you should have a good idea of how azk works as an orchestration tool for your development/deployment workflow.</p>

<h3 id="how-it-works">How It Works</h3>

<p>First, azk orchestrates the application's environment on your local computer. Once you have the application running locally, azk also automates its deployment to your Droplet.</p>

<p>Since azk always runs applications from source code, you can also tinker with the application locally (if you want to), and then deploy or roll back, again with no special extra steps.</p>

<p>azk isolates the environments using <em>containers</em>, so it's safe to run applications on your local computer. It works with both new projects started from scratch and previously existing code. </p>

<h3 id="using-azk-with-custom-applications">Using azk with Custom Applications</h3>

<p>Using the current list of <a href="http://run.azk.io/">applications preconfigured to work with azk</a> as examples, with some extra work you can configure any project to work with azk.</p>

<p>To do this, <a href="http://docs.azk.io/en/azkfilejs/">add an Azkfile</a> to the project.</p>

<p>That's a simple manifest file that lists the elements necessary to run the application and summarizes their relationships (OS, languages, databases etc.).</p>

<p>The benefits of adding an <code>Azkfile</code> to your project include:</p>

<ul>
<li>Use azk to automate the environment setup for your project both locally and in deployment</li>
<li>Other people who want to deploy your app can do it with azk</li>
</ul>

<p>Composing an <code>Azkfile</code> is beyond the scope of this tutorial, but you can check azk's documentation to learn <a href="http://docs.azk.io/en/azkfilejs/">how to compose an Azkfile</a> and how to <a href="http://docs.azk.io/en/run-project-button/">add the Run Project GitHub button</a> to your code.</p>

<h2 id="prerequisites">Prerequisites</h2>

<p>To follow along with this guide, you'll need a local computer running any of these operating systems (64-bit) for your local environment:</p>

<ul>
<li>Mac OS X 10.6 (Snow Leopard) or later</li>
<li>Ubuntu 12.04, 14.04 or 15.10</li>
<li>Fedora 21 or 22</li>
</ul>

<p>You'll also need to be able to make <code>git</code> commits.</p>

<ul>
<li>Your local computer needs to have Git installed. See <a href="https://indiareads/community/tutorials/how-to-install-git-on-ubuntu-14-04">this series on using Git</a> for Linux instructions, or visit the <a href="https://git-scm.com/downloads">Git download page</a></li>
<li>Make sure you've run the suggested commands <code>git config --global user.email "you@example.com"</code> and <code>git config --global user.name "Your Name"</code> before starting the tutorial; see the previous links for details about Git</li>
</ul>

<p>Notice that having an active Droplet isn't a requirement for this tutorial. azk will create one for you using IndiaReads's API.</p>

<p><span class="note">Deploying a Droplet costs money! This tutorial deploys a single 1 GB Droplet by default.<br /></span></p>

<h3 id="linux-users-installing-docker">Linux Users: Installing Docker</h3>

<p>If you're using Linux (Ubuntu or Fedora), you'll need to install <a href="https://www.docker.com/">Docker</a> 1.8.1 or later as your container software.</p>

<p>One way to install Docker is to run Docker's installation script. (In general, <strong>make sure you understand what a script does before running it</strong>):</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">wget -nv <span class="highlight">https://get.docker.com/</span> -O- -t 2 -T 10 | bash
</li></ul></code></pre>
<p>If you want to learn more about installing Docker on Linux, check the <a href="http://docs.docker.com/engine/installation/">instructions</a> available on the official documentation, or IndiaReads's <a href="https://indiareads/community/tutorials/how-to-install-and-use-docker-getting-started">Docker installation article</a>.</p>

<h3 id="mac-os-x-users-installing-virtualbox">Mac OS X Users: Installing VirtualBox</h3>

<p>You'll need <a href="https://www.virtualbox.org/">VirtualBox</a> 4.3.6 or later as your container software.</p>

<p>To install VirtualBox, <a href="https://www.virtualbox.org/wiki/Downloads">download the appropriate Virtualbox installation package</a> from the official download page.</p>

<h2 id="step-1-—-installing-azk-locally">Step 1 — Installing azk Locally</h2>

<p>We will install azk using the project's installation script. <strong>Make sure you understand what any script does before executing it on your system.</strong></p>

<p>If you already have an older version of azk installed, you can use that installation script to update azk.</p>

<p>Alternately, check out the <a href="http://docs.azk.io/en/installation/">package installation instructions</a> for supported operating systems.</p>

<h3 id="installing-azk-on-linux">Installing azk on Linux</h3>

<p>If you're using Linux (Ubuntu or Fedora), run this command in a terminal to install azk using the project's script. We recommend <strong>vetting any script before running it</strong> on your system:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">wget -nv http://azk.io/install.sh -O- -t 2 -T 10 | bash
</li></ul></code></pre>
<p>After the installation is complete, log out and then log in again to make all alterations effective.</p>

<p>The reason you need to log out is because, during the installation process, your user will be added to the <strong>docker</strong> group. This is a required step so that we can use Docker without being the <strong>root</strong> user. You have to exit the current session for this to take effect.</p>

<p>If you want to learn more about the <a href="http://docs.docker.com/v1.8/installation/ubuntulinux/#create-a-docker-group">Docker group</a>, you can check Docker's official documentation.</p>

<h3 id="installing-azk-on-mac-os-x">Installing azk on Mac OS X</h3>

<p>Run this command in a terminal to install azk using the project's script. We recommend <strong>vetting any script before running it</strong> on your system:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">curl -sSL http://www.azk.io/install.sh | bash
</li></ul></code></pre>
<h2 id="step-2-—-checking-azk-installation">Step 2 — Checking azk Installation</h2>

<p>Once the azk installation is complete, run the command below to check if the installation process was successful:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">azk version
</li></ul></code></pre>
<p>This command verifies the azk version installed. If it returns a version number (e.g. <code>azk 0.17.0</code> or later), we're good to go and you can move on to the next step.</p>

<p>Congratulations on installing azk for your local environment!</p>

<p>If not, read one of the troubleshooting sections below for help.</p>

<p><strong>Troubleshooting azk installation for Linux</strong></p>

<p>Let's check the Docker version installed by running:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">docker version
</li></ul></code></pre>
<p>You'll need version 1.8.1 or later.</p>

<p>However, if you get an error message, it means you don't have Docker installed yet. In that case, <a href="http://docs.docker.com/engine/installation/">follow the specific installation instructions</a> for your OS from Docker's documentation.</p>

<p>After you have confirmed you have a proper version of Docker installed, run this command as your <strong>sudo user</strong> to make sure your user is in the <strong>docker</strong> group:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">id -Gn
</li></ul></code></pre>
<p>If your list of groups includes <strong>docker</strong>, it means it is correctly configured. Otherwise, if you don't get the word <strong>docker</strong> among them, run this command to add your user to the group:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo usermod -aG docker <span class="highlight">$USER</span>
</li></ul></code></pre>
<p>Then log out and log in again.</p>

<p>Check the <code>id -Gn</code> command once more to make sure it returns the list of groups with <strong>docker</strong> among them.</p>

<p>If these instructions weren't enough for you to get Docker running properly (e.g. you're still not able to successfully run the <code>docker version</code> command), please <a href="http://docs.docker.com/engine/installation/">refer to Docker's installation instructions</a>.</p>

<p><strong>Troubleshooting azk installation for Mac OS X</strong></p>

<p>Make sure you have VirtualBox installed:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">which VBoxManage
</li></ul></code></pre>
<p>If this returns a file path (such as <code>/usr/local/bin/VBoxManage</code>), we're good to proceed. Otherwise, if it returns a "not found" message, it means you don't have VirtualBox installed.</p>

<p>In this case, <a href="https://www.virtualbox.org/wiki/Downloads">download and install the VirtualBox installation package</a> from their official website.</p>

<h2 id="optional-step-3-—-learning-about-the-demo-application-stringer">(Optional) Step 3 — Learning About the Demo Application, Stringer</h2>

<p>We chose <a href="https://github.com/swanson/stringer">Stringer</a> as a demo application for this guide because it's a simple application that's already configured to work with azk.</p>

<p>It's a Rails app with a well-defined use case: a bare-bones RSS reader.</p>

<span class="note"><p>
<strong>More about Stringer:</strong></p>

<p>Some news sites also offer their content in the format of an RSS feed. This is a standard XML file format that enables publishers to syndicate data automatically. An RSS reader is a program used to subscribe and present RSS content. Subscribing to a website RSS feed removes the need for users to manually check the website for new content. Users can create a list of subscribed feeds and consume their contents on the RSS reader (usually in the format of a cronologically ordered list).<br /></p></span>

<h2 id="optional-step-4-—-configuring-a-custom-application-to-use-azk">(Optional) Step 4 — Configuring a Custom Application to Use azk</h2>

<p>While the primary focus of this guide is to show how azk works for an application that already has its environment details spelled out for azk, in the long run the tool is most useful when you can use it to deploy any application.</p>

<p>So, take a look at how <a href="https://github.com/run-project/stringer">this forked version of Stringer</a> compares to <a href="https://github.com/swanson/stringer">the primary Stringer repository</a>.</p>

<p>The azk version has only two additions to Stringer's original version:</p>

<ul>
<li>An <code>Azkfile</code>, which provides the environment information for azk</li>
<li>An azk <strong>Run Project</strong> button</li>
</ul>

<p>You can learn more about making azk work with other applications from azk's documentation on the <a href="http://docs.azk.io/en/azkfilejs/">Azkfile</a> and the <a href="http://docs.azk.io/en/run-project-button/">Run Project button</a>.</p>

<p>Next we'll see how an azk-friendly application looks on GitHub.</p>

<h2 id="optional-step-5-—-using-azk-39-s-run-project-button-on-github">(Optional) Step 5 — Using azk's Run Project Button on GitHub</h2>

<p>Part of azk's best practices for GitHub projects is to make it very obvious how to run that project with azk. So, instead of just showing the azk command in the middle of the project readme, projects that use azk can use the <strong>Run Project</strong> button to visually isolate the azk command.</p>

<p>Stringer uses this button.</p>

<p>Visit the <a href="https://github.com/run-project/stringer#running-locally">Running Locally</a> section of azk's forked version of Stringer.</p>

<p>Click the <strong>Run Project</strong> button.</p>

<p><img src="https://assets.digitalocean.com/articles/ask/UGWmrZM.png" alt="Run Project button on Stringer project" /></p>

<p>The first time you click the <strong>Run Project</strong> button, you'll see a short explanation of what's going on. When you're ready to move on, just click <strong>OK, DISMISS</strong> at the bottom of the explanation.</p>

<p><img src="https://assets.digitalocean.com/articles/ask/LqWwheV.png" alt="'What is azk?' message" /></p>

<p>Then you'll be taken to a page with the azk command for the Stringer project:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">azk start -o run-project/stringer
</li></ul></code></pre>
<p><img src="https://assets.digitalocean.com/articles/ask/tmDRhbw.png" alt="Command to run the Stringer app using azk: azk start -o run-project/stringer" /></p>

<p>You can always click the <strong>What is this?</strong> link on the upper right corner to see the explanation again.</p>

<p>In the center of the screen, there's a command box with three tabs: <strong>curl</strong>, <strong>wget</strong> and <strong>azk</strong>. Since we already have azk installed, we can use the <strong>azk</strong> one.</p>

<p>This is the command we'll use in the next step to actually run Stringer.</p>

<h2 id="step-6-—-running-stringer-locally">Step 6 — Running Stringer Locally</h2>

<p>In this section we'll use azk to run Stringer on our local workstation.</p>

<p>On our local computer, let's make sure we're in our home directory (if you choose a different installation folder, just remember to adapt later commands to your chosen directory):</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">cd ~
</li></ul></code></pre>
<p>Go ahead and run that command on your local workstation to run Stringer:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">azk start -o run-project/stringer
</li></ul></code></pre>
<p>Since this is your first time starting azk, you'll be asked to accept its terms of service. A message like the following should be prompted:</p>
<pre class="code-pre "><code langs="">? =========================================================================
  Thank you for using azk! Welcome!
  Before we start, we need to ask: do you accept our Terms of Use?
  http://docs.azk.io/en/terms-of-use
 =========================================================================
 (Y/n)
</code></pre>
<p>Press either <code>Y</code> if you agree or <code>N</code> otherwise. Then press <code>ENTER</code> to inform your answer. In the case you disagree, you won't be able to use azk.</p>

<p>Finally, azk will automatically download Stringer's source code as well as the attached Azkfile to run this code in a completely safe and isolated environment on your local computer.</p>

<p>Next, you'll be asked if you want to start the azk <em>agent</em>:</p>
<div class="code-label " title="Output">Output</div><pre class="code-pre "><code langs="">? The agent is not running, would you like to start it? (<span class="highlight">Y/n</span>)
</code></pre>
<p>The <em>agent</em> is an azk component that configures Docker (on Linux) or a VirtualBox VM (on Mac OS X).</p>

<p>Press <code>ENTER</code> to answer "Yes" (default option).</p>

<p>The first time you run the agent, azk will run its setup.</p>

<p>The setup does a lot of things behind the scenes, including creating the file <code>/etc/resolver/dev.azk.io</code>, which contains a DNS configuration to resolve addresses ending with the <code>dev.azk.io</code> suffix.</p>

<p>azk uses this suffix when running applications to apply human-readable addresses to them instead of requiring us to manually configure <code>http://localhost:PORT_NUMBER</code> addresses. This also avoids port conflict across different applications.</p>

<p>(It's basically doing the same thing as editing your <code>/etc/hosts</code> file to redirect a domain name locally.)</p>

<p>If you get a message like the following:</p>
<div class="code-label " title="Output">Output</div><pre class="code-pre "><code langs="">? Enter the vm ip: (192.168.50.4)
</code></pre>
<p>You can enter any local IP address that you would like to run the app on. The default should be fine for most cases. To accept it, just press <code>ENTER</code>.</p>

<p>To complete the azk agent setup, you'll be asked for your <strong>sudo</strong> password (for Mac OS X users, this is your admin password).</p>

<p>Now azk will start. You'll see azk downloading the elements listed in the Azkfile (in the form of Docker images).</p>

<p>These images may take a few minutes to download the first time (around 10 minutes or less).</p>

<p>As soon as azk completes the setup, your default browser will automatically load Stringer's initial screen running on your local computer.</p>

<p><img src="https://assets.digitalocean.com/articles/ask/D6Tf6GF.png" alt="Stringer is up and running" /></p>

<p><img src="https://assets.digitalocean.com/articles/ask/ARJCeDT.png" alt="Accessing Stringer app" /></p>

<p>As you can see, it's using local DNS, so the app is visible at <code>http://stringer.dev.azk.io</code>. You can also access the application manually by going to <code>http://stringer.dev.azk.io</code>.</p>

<p>If you want to set a password and start using the application you can, but it's not necessary for this tutorial. We just wanted to see that azk could run Stringer locally.</p>

<p>Now that we have Stringer running on our local computer, we can deploy it from the computer to a Droplet.</p>

<h2 id="step-7-—-obtaining-a-digitalocean-api-token">Step 7 — Obtaining a IndiaReads API Token</h2>

<p>Before we can deploy a Droplet from azk, we need an API token. The token gives azk permission to deploy a new IndiaReads server on your account.</p>

<p>The first time you run azk from this environment with this token, it will deploy a new 1 GB Ubuntu 14.04 Droplet. Subsequent deployments from the same local environment will use that same single Droplet.</p>

<p>Follow the instructions from the <a href="https://indiareads/community/tutorials/how-to-use-the-digitalocean-api-v2#how-to-generate-a-personal-access-token">How To Generate a Personal Access Token</a> section of the linked tutorial. The generated token must have <strong>read and write permissions</strong>.</p>

<p>Copy the 64 hexadecimal characters of your token, similar to the example below:</p>
<div class="code-label " title="Example API Token">Example API Token</div><pre class="code-pre "><code langs="">a17d6a72566200ad1a8f4e090209fe1841d77d7c85223f769e8c5de47475a726
</code></pre>
<p>You'll see the token string only once, so note it down in a safe place. (Remember, if this token gets compromised, it could be used to access your account, so keep it private.)</p>

<p>For the instructions below, remember to replace the example token with your real one.</p>

<p>Go to Stringer's folder:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">cd ~/stringer
</li></ul></code></pre>
<p>Save your Personal Access Token in a file you'll call <code>.env</code>. To do this, run this command to create the file (don't forget to replace the token):</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">echo "DEPLOY_API_TOKEN=<span class="highlight">a17d6a72566200ad1a8f4e090209fe1841d77d7c85223f769e8c5de47475a726</span>" >> .env
</li></ul></code></pre>
<p>The <code>.env</code> file contents should look like this:</p>
<div class="code-label " title=".env">.env</div><pre class="code-pre "><code langs="">DEPLOY_API_TOKEN=<span class="highlight">a17d6a72566200ad1a8f4e090209fe1841d77d7c85223f769e8c5de47475a726</span>
</code></pre>
<h2 id="optional-step-8-—-learning-about-ssh-keys">(Optional) Step 8 — Learning about SSH Keys</h2>

<p>You don't have to do anything to set up an SSH key for azk, but it's useful to know how azk uses one.</p>

<p>azk uses an SSH key to access the Droplet. If you already have an SSH key, azk will use that one.</p>

<p>To find out if you have an SSH key on you computer, run:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">ls ~/.ssh/*.pub
</li></ul></code></pre>
<p>If it returns a "not found" message, you don't have any SSH keys on your computer.</p>

<p>In that case, azk will automatically create a new SSH key to use exclusively for deploying each new application from your computer.</p>

<p>azk will create its key in reserved storage of its own and won't make any modifications to your <code>~/.ssh</code> directory.</p>

<p>If you want to check the generated public key, you can run the following command after the first application deployment:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">azk deploy shell -c "cat /azk/deploy/.config/ssh/*.pub"
</li></ul></code></pre>
<p>To learn more about SSH keys in general, <a href="https://indiareads/community/tutorials/how-to-set-up-ssh-keys--2">read this tutorial about SSH keys</a>.</p>

<h2 id="step-9-—-deploying-with-azk">Step 9 — Deploying with azk</h2>

<p>By default, azk will create a 1 GB IndiaReads Droplet running Ubuntu 14.04 to deploy your application.</p>

<p><span class="note">If you'd like to deploy a Droplet with different specs, you can change the settings in the <code>envs</code> property of the <code>deploy</code> system in the <code>Azkfile.js</code> file. Refer to <a href="http://docs.azk.io/en/deploy/#supported-customization">azk deployment documentation</a> for additional instructions.<br /></span></p>

<p>First, go to Stringer's (or your application's) directory:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">cd ~/stringer
</li></ul></code></pre>
<p>Then, to start the deployment, just run:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">azk deploy
</li></ul></code></pre>
<p>The command <code>azk deploy</code> is the command you'll run the most often when using azk to orchestrate your application.</p>

<p>The first deployment may take a while (around 10 minutes) as azk does all the work.</p>

<p>In specific, azk has to:</p>

<ul>
<li>Download support elements (Docker image for deployment)</li>
<li>Create and configure the Droplet</li>
<li>Upload the application's source code to the Droplet</li>
<li>Run the application</li>
</ul>

<p>Every new deployment of this application from your computer will be a lot faster (around 30 seconds or less) since the longer steps will already be completed.</p>

<p>If your SSH key is password protected, it will be required a few times during the deployment process. Just type your SSH key password and press <code>ENTER</code> every time a message as the one below appears:</p>
<div class="code-label " title="Output">Output</div><pre class="code-pre "><code langs="">Enter passphrase for ~/.ssh/id_rsa:
</code></pre>
<p>The terminal output will show quite a few actions being taken on the <strong>remote</strong> system, ending with the successful deployment message <code>App successfully deployed at http://<span class="highlight">your_server_ip</span></code>.</p>

<p><img src="https://assets.digitalocean.com/articles/ask/448PDqn.png" alt="Stringer has been successfully deployed" /></p>

<p>Visit <code>http://<span class="highlight">your_server_ip</span></code> to view the application hosted on your server.</p>

<p><img src="https://assets.digitalocean.com/articles/ask/peQgdYG.png" alt="Accessing Stringer running in a Droplet" /></p>

<p>From now on, you can alter the code of the application on your computer, test it locally, and deploy the changes to your Droplet with the <code>azk deploy</code> command.</p>

<h2 id="step-10-—-modifying-stringer">Step 10 — Modifying Stringer</h2>

<p>To show how easy is to use azk for application development, for customization, or for version control, let's make a simple change to the Stringer signup page, and redeploy the application.</p>

<p>Be sure you're in Stringer's directory:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">cd ~/stringer
</li></ul></code></pre>
<p>Let's edit the file <code>app/views/first_run/password.erb</code>, which is the page that contains the text for that first signup page.</p>

<p>Use <code>nano</code> or your favorite text editor:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">nano ~/stringer/app/views/first_run/password.erb
</li></ul></code></pre>
<p>Here we're adding an extra line that says "It's easy with azk!":</p>
<div class="code-label " title="app/views/first_run/password.erb">app/views/first_run/password.erb</div><pre class="code-pre "><code langs=""><div class="setup" id="password-setup">
  <h1><%= t('first_run.password.title') %> <span class="orange"><%= t('first_run.password.anti_social') %></span>.</h1>
  <h2><%= t('first_run.password.subtitle') %></h2>
  <span class="highlight"><h2>It's easy with azk!</h2></span>
  <hr />
  . . .
</div>
</code></pre>
<p>Save and exit the text editor. If you're using nano, press <code>CTRL+O</code> to save and <code>CTRL+X</code> to exit.</p>

<p>Since Stringer is set to run in production mode by default, refreshing your browser isn't enough to make your change goes live. Restart the application from azk:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">azk restart stringer -o
</li></ul></code></pre>
<p>A new browser tab should open with the new version of Stringer. Right below the default text <strong>There is only one user: you.</strong>, it should now say <strong>It's easy with azk!</strong> as well.</p>

<h2 id="step-11-—-redeploying-stringer">Step 11 — Redeploying Stringer</h2>

<p>Now, let's commit the changes into our version control system so we can deploy them.</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">git add app/views/first_run/password.erb
</li><li class="line" prefix="$">git commit .
</li></ul></code></pre>
<p>You'll be shown a text editor (most likely nano or vim).</p>

<p>Enter a commit message, like <code>It is easy with azk</code>.</p>

<p>This commit message will be used to label versions of your application within azk, so choose one that will jog your memory if you need to roll back later.</p>

<p>Save and close the commit message.</p>

<p><span class="note">If you get the <code>fatal: empty ident name (for <sammy@azk.(none)>) not allowed</code> error, please run the suggested setup commands for Git to set an email address and name (more details in the <strong>Prerequisites</strong> section).<br /></span></p>

<p>To deploy your change and update the application running on your Droplet, run:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">azk deploy
</li></ul></code></pre>
<p>Once it's done, access your Droplet's IP address from your browser (e.g. <code>http://<span class="highlight">your_server_ip</span></code>). You should see the new line <strong>It's easy with azk!</strong> here, too.</p>

<p><img src="https://assets.digitalocean.com/articles/ask/tOmAsIH.png" alt="New version of Stringer running on Droplet" /></p>

<p>This new deployment will create a new version of the application on the Droplet. All versions of the application are stored, so you can roll back to a previous one and then forward again.</p>

<h2 id="step-12-—-rolling-back-to-a-previous-version">Step 12 — Rolling Back to a Previous Version</h2>

<p>To list all available versions of our application on the Droplet, run this command locally:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">azk deploy versions
</li></ul></code></pre>
<p>This should result in a list like:</p>
<div class="code-label " title="Output">Output</div><pre class="code-pre "><code langs="">⇲ Retrieving deployed versions...

  ➜ v2              It is easy with azk
    v1              Merge branch 'master' of https://github.com/swanson/stringer
</code></pre>
<p>To roll back the application to an older version, just run:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">azk deploy rollback <span class="highlight">v1</span>
</li></ul></code></pre>
<p>The argument <code><span class="highlight">v1</span></code> is a version number shown by the output of the <code>azk deploy versions</code> command. If you run the command without an argument (e.g. <code>azk deploy rollback</code>), the application will be rolled back to the version right before the current one.</p>

<p>To check that the rollback completed, just refresh the browser tab that's showing the server version.</p>

<p>Now you should see the application without our custom text, the way it was in the original deployment.</p>

<p>If you'd like to roll forward again, you can choose the newest version:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">azk deploy rollback <span class="highlight">v2</span>
</li></ul></code></pre>
<p>The labels for these versions come from the <code>git commit</code> messages in the previous step.</p>

<h2 id="conclusion">Conclusion</h2>

<p>In this guide, we used a simple Rails app to demonstrate how azk automates our application environment setup tasks. This makes it easy to deploy the same application in multiple environments.</p>

<p>If you like azk's deployment process, consider using it for your own project or adding an <code>Azkfile</code> to a fork of another open-source project. Learn about <a href="http://docs.azk.io/en/azkfilejs/">creating an Azkfile here</a> and how to <a href="http://docs.azk.io/en/run-project-button/">add the Run Project GitHub button here</a>.</p>

<p>Or, you can check out this <a href="http://run.azk.io/">demonstration gallery</a> of other applications that already have the legwork done to run with azk.</p>

<p>Besides <code>rollback</code> and <code>versions</code>, azk supports other auxiliary subcommands that allow us to perform some additional actions (e.g., access the Droplet's shell via SSH).</p>

<p><a href="http://docs.azk.io/en/deploy/#additional-features">Check the complete subcommands list in azk's documentation</a>.</p>

    