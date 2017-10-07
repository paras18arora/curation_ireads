<link rel='stylesheet' href='digital_ocean.css'><br> <br> 
      <h3 id="an-article-from-discourse">An Article from <a href="http://www.discourse.org/">Discourse</a></h3>

<h2 id="introduction">Introduction</h2>

<p><a href="http://www.discourse.org/">Discourse</a> is an open source discussion platform built for the next decade of the Internet. We'll walk through all of the steps required to get Discourse running on your IndiaReads Droplet.</p>

<h3 id="prerequisites">Prerequisites</h3>

<p>Before we get started, there are a few things we need to set up first:</p>

<ul>
<li><p>Ubuntu 14.04 Droplet (64 bit) with a minimum of 2 GB of RAM. If you need help with this part, <a href="https://indiareads/community/tutorials/how-to-create-your-first-digitalocean-droplet-virtual-server">this tutorial</a> will get you started.</p>

<p>Discourse recommends 1 GB of RAM for small communities and 2 GB of RAM for larger communities. It also requires a swap file if you are using 1 GB of RAM. Although swap is generally recommended for systems utilizing traditional spinning hard drives, using swap with SSDs can cause issues with hardware degradation over time. Due to this consideration, we do not recommend enabling swap on IndiaReads or any other provider that utilizes SSD storage. Doing so can impact the reliability of the underlying hardware for you and your neighbors. Hence, we recommend a minimum of 2 GB of RAM to run Discourse on a IndiaReads Droplet. Refer to <a href="https://indiareads/community/tutorials/how-to-add-swap-on-ubuntu-14-04">How To Add Swap on Ubuntu 14.04</a> for details on using swap.</p>

<p>If you need to improve the performance of your server, we recommend upgrading your Droplet. This will lead to better results in general and will decrease the likelihood of contributing to hardware issues that can affect your service.</p></li>
<li><p>You can use an IP address as your domain for testing, but for a production server, you should have a domain that resolves to your Droplet. <a href="https://indiareads/community/articles/how-to-set-up-a-host-name-with-digitalocean">This tutorial</a> can help.</p></li>
<li><p>Non-root user with sudo privileges (<a href="https://indiareads/community/tutorials/initial-server-setup-with-ubuntu-14-04">Initial Server Setup with Ubuntu 14.04</a> explains how to set this up.)</p></li>
<li><p>Free account on <a href="https://mandrill.com/">Mandrill</a> and <a href="http://help.mandrill.com/entries/23744737-Where-do-I-find-my-SMTP-credentials">get SMTP credentials</a>. It wouldn't hurt to test the validity of these credentials beforehand, although you can use them for the first time with Discourse.</p></li>
</ul>

<p>All the commands in this tutorial should be run as a non-root user. If root access is required for the command, it will be preceded by <code>sudo</code>. <a href="https://indiareads/community/tutorials/initial-server-setup-with-ubuntu-14-04">Initial Server Setup with Ubuntu 14.04</a> explains how to add users and give them sudo access.</p>

<h2 id="step-1-—-install-git">Step 1 — Install Git</h2>

<p>In this section we will install <a href="http://git-scm.com/">Git</a> to download the Discourse source files. Git is an open source distributed version control and source code management system.</p>

<p>Before we get started, it is highly recommend to make sure that your system is up to date. SSH into your Droplet as the root user:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">ssh sammy@<span class="highlight">your-server-ip</span>
</li></ul></code></pre>
<p>Execute the following commands on your Droplet to update the system:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo apt-get update
</li><li class="line" prefix="$">sudo apt-get upgrade
</li></ul></code></pre>
<p>Once that is complete, install Git by running following command:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo apt-get install git
</li></ul></code></pre>
<h2 id="step-2-—-install-docker">Step 2 — Install Docker</h2>

<p>In this section we will install <a href="https://www.docker.com/">Docker</a> so that Discourse will have an isolated environment in which to run. Docker is an open source project that can pack, ship, and run any application in a lightweight container. For more introductory information about Docker, please see <a href="https://indiareads/community/tutorials/how-to-install-and-use-docker-getting-started">this tutorial</a>.</p>

<p>Docker provides a public script to get Docker installed:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">wget -qO- https://get.docker.io/ | sh
</li></ul></code></pre>
<p>You need to add your non-root user to the <code>docker</code> group to be able to run a Docker container as this user:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo usermod -aG docker <span class="highlight">sammy</span>
</li></ul></code></pre>
<p>You also have to log out and log back in as that user to enable the change:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">exit
</li><li class="line" prefix="$">su - sammy
</li></ul></code></pre>
<h2 id="step-3-—-download-discourse">Step 3 — Download Discourse</h2>

<p>In this section we will download Discourse.</p>

<p>Create a <span class="highlight">/var/discourse</span> folder, where all the Discourse-related files will reside:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo mkdir /var/discourse
</li></ul></code></pre>
<p>Clone the <a href="https://github.com/discourse/discourse_docker">official Discourse Docker Image</a> into this <span class="highlight">/var/discourse</span> folder:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo git clone https://github.com/discourse/discourse_docker.git /var/discourse
</li></ul></code></pre>
<h2 id="step-4-—-configure-discourse">Step 4 — Configure Discourse</h2>

<p>In this section we will configure your initial Discourse settings.</p>

<p>Switch to the <span class="highlight">/var/discourse</span> directory:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">cd /var/discourse
</li></ul></code></pre>
<p>Copy the <span class="highlight">samples/standalone.yml</span> file into the <code>containers</code> folder as <code>app.yml</code>:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo cp samples/standalone.yml containers/app.yml
</li></ul></code></pre>
<p>Edit the Discourse configuration in the <code>app.yml</code> file:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo nano containers/app.yml
</li></ul></code></pre>
<p>The configuration file will open in the <a href="http://www.nano-editor.org/">nano text editor</a>.</p>

<p>Locate the <span class="highlight">env</span> section and update it with your custom email, domain, and SMTP server information, as shown below. The individual lines are explained after the example block:</p>
<div class="code-label " title="app.yml">app.yml</div><pre class="code-pre "><code langs="">...
env:
  LANG: en_US.UTF-8
  ## TODO: How many concurrent web requests are supported?
  ## With 2GB we recommend 3-4 workers, with 1GB only 2
  #UNICORN_WORKERS: 3
  ##
  ## TODO: List of comma delimited emails that will be made admin and developer
  ## on initial signup example 'user1@example.com,user2@example.com'
  DISCOURSE_DEVELOPER_EMAILS: '<span class="highlight">me@example.com</span>'
  ##
  ## TODO: The domain name this Discourse instance will respond to
  DISCOURSE_HOSTNAME: '<span class="highlight">discourse.example.com</span>'
  ##
  ## TODO: The mailserver this Discourse instance will use
  DISCOURSE_SMTP_ADDRESS: <span class="highlight">smtp.mandrillapp.com</span>         # (mandatory)
  DISCOURSE_SMTP_PORT: <span class="highlight">587</span>                        # (optional)
  DISCOURSE_SMTP_USER_NAME: <span class="highlight">login@example.com</span>      # (optional)
  DISCOURSE_SMTP_PASSWORD: <span class="highlight">9gM5oAw5pBB50KvjcwAmpQ</span>               # (optional)
  ##
  ## The CDN address for this Discourse instance (configured to pull)
  #DISCOURSE_CDN_URL: //discourse-cdn.example.com
  ...

</code></pre>
<p>Here are the individual lines that need to be changed:</p>

<p>1) <strong>Set Admin Email</strong></p>

<p>Choose the email address that you want to use for the Discourse admin account. It can be totally unrelated to your Discourse domain and can be any email address you find convenient. Set this email address in the <span class="highlight">DISCOURSE_DEVELOPER_EMAILS</span> line. This email address will be made the Discourse admin by default, once a user registers with that email. You'll need this email address later when you set up Discourse from its web control panel.</p>
<pre class="code-pre "><code langs="">DISCOURSE_DEVELOPER_EMAILS: '<span class="highlight">me@example.com</span>'
</code></pre>
<p>Replace <span class="highlight">me@example.com</span> with your email.</p>

<p>Developer Email setup is required for creating and activating your initial administrator account.</p>

<p>2) <strong>Set Domain</strong></p>

<p>Set <span class="highlight">DISCOURSE_HOSTNAME</span> to <span class="highlight">discourse.example.com</span>. This means you want your Discourse forum to be available at <span class="highlight">http://discourse.example.com/</span>. You can use an IP address here instead if you don't have a domain pointing to your server yet. Only one domain (or IP) can be listed here.</p>
<pre class="code-pre "><code langs="">DISCOURSE_HOSTNAME: '<span class="highlight">discourse.example.com</span>'
</code></pre>
<p>Replace <span class="highlight">discourse.example.com</span> with your domain. A hostname is required to access your Discourse instance from the web.</p>

<p>3) <strong>Set Mail Credentials</strong></p>

<p>We recommend Mandrill for your SMTP mail server. <a href="http://help.mandrill.com/entries/23744737-Where-do-I-find-my-SMTP-credentials">Get your SMTP credentials from Mandrill</a>.</p>

<p>Enter your SMTP credentials in the lines for <span class="highlight">DISCOURSE_SMTP_ADDRESS</span>, <span class="highlight">DISCOURSE_SMTP_PORT</span>, <span class="highlight">DISCOURSE_SMTP_USER_NAME</span>, and <span class="highlight">DISCOURSE_SMTP_PASSWORD</span>. (Be sure you remove the comment <strong>#</strong> character from the beginnings of these lines as necessary.)</p>
<pre class="code-pre "><code langs="">DISCOURSE_SMTP_ADDRESS: <span class="highlight">smtp.mandrillapp.com</span>         # (mandatory)
DISCOURSE_SMTP_PORT: <span class="highlight">587</span>                        # (optional)
DISCOURSE_SMTP_USER_NAME: <span class="highlight">login@example.com</span>      # (optional)
DISCOURSE_SMTP_PASSWORD: <span class="highlight">9gM5oAw5pBB50KvjcwAmpQ</span>               # (optional)
</code></pre>
<p>The SMTP settings are required to send mail from your Discourse instance; for example, to send registration emails, password reset emails, reply notifications, etc.</p>

<p>Having trouble setting up mail credentials? See the Discourse <a href="https://meta.discourse.org/t/troubleshooting-email-on-a-new-discourse-install/16326">Email Troubleshooting guide</a>.</p>

<p>Setting up mail credentials is required, or else you will not be able to bootstrap your Discourse instance. The credentials must be correct, or else you will not be able to register users (including the admin user) for the forum.</p>

<p>4) <strong>Optional: Tune Memory Settings (preferred for 1 GB Droplet)</strong></p>

<p>Also in the <span class="highlight">env</span> section of the configuration file, set <span class="highlight">db_shared_buffers</span> to <strong>128MB</strong> and <span class="highlight">UNICORN_WORKERS</span> to <strong>2</strong> so you have more memory room.</p>
<pre class="code-pre "><code langs="">db_shared_buffers: "128MB"
</code></pre>
<p>and</p>
<pre class="code-pre "><code langs="">UNICORN_WORKERS: 2
</code></pre>
<p>Tuning these memory settings will optimize Discourse performance on a 1 GB Droplet.</p>

<p><strong>NOTE:</strong> The above changes are mandatory and should not be skipped, or else you will have a broken Discourse forum.</p>

<p>Save the <code>app.yml</code> file, and exit the text editor.</p>

<h2 id="step-5-—-bootstrap-discourse">Step 5 — Bootstrap Discourse</h2>

<p>In this section we will bootstrap Discourse.</p>

<p>First, we need to make sure that Docker can access all of the outside resources it needs. Open the Docker settings file <code>/etc/default/docker</code>:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo nano /etc/default/docker
</li></ul></code></pre>
<p>Uncomment the <span class="highlight">DOCKER_OPTS</span> line so Docker uses Google's DNS:</p>
<div class="code-label " title="/etc/default/docker">/etc/default/docker</div><pre class="code-pre "><code langs="">...

# Use DOCKER_OPTS to modify the daemon startup options.
DOCKER_OPTS="--dns 8.8.8.8 --dns 8.8.4.4"

...
</code></pre>
<p>Restart Docker to apply the new settings:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo service docker restart
</li></ul></code></pre>
<p><strong>Note:</strong> If you don't change Docker's DNS settings before running the bootstrap command, you may get an error like "fatal: unable to access 'https://github.com/SamSaffron/pups.git/': Could not resolve host: github.com".</p>

<p>Now use the bootstrap process to build Discourse and initialize it with all the settings you configured in the previous section. This also starts the Docker container. You must be in the <span class="highlight">/var/discourse</span> directory:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">cd /var/discourse
</li></ul></code></pre>
<p>Bootstrap Discourse:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo ./launcher bootstrap app
</li></ul></code></pre>
<p>This command will take about 8 minutes to run while it configures your Discourse environment. (Early in this process you will be asked to generate a SSH key; press <strong>Y</strong> to accept.)</p>

<p>After the bootstrap process completes, start Discourse:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo ./launcher start app   
</li></ul></code></pre>
<p>Congratulations! You now have your very own Discourse instance!</p>

<h2 id="step-6-—-access-discourse">Step 6 —  Access Discourse</h2>

<p>Visit the domain or IP address (that you set for the Discourse hostname previously) in your web browser to view the default Discourse web page.</p>

<p><img src="https://assets.digitalocean.com/articles/Install_Discourse/1.png" alt="discourse" /></p>

<p>If you receive a 502 Bad Gateway error, try waiting a minute or two and then refreshing so Discourse can finish starting.</p>

<h2 id="step-7-—-sign-up-and-create-admin-account">Step 7 —  Sign Up and Create Admin Account</h2>

<p>Use the <strong>Sign Up</strong> button at the top right of the page to register a new Discourse account. You should use the email address you provided in the <span class="highlight">DISCOURSE_DEVELOPER_EMAILS</span> setting previously. Once you confirm your account, that account will automatically be granted admin privileges.</p>

<p><img src="https://assets.digitalocean.com/articles/Install_Discourse/2.png" alt="sign_up" /></p>

<p>Once you sign up and log in, you should see the Staff topics and the <a href="https://github.com/discourse/discourse/blob/master/docs/ADMIN-QUICK-START-GUIDE.md">Admin Quick Start Guide</a>. It contains the next steps for further configuring and customizing your Discourse installation.</p>

<p>You can access the admin dashboard by visting <span class="highlight">/admin</span>.</p>

<p><img src="https://assets.digitalocean.com/articles/Install_Discourse/3.png" alt="dash" /></p>

<p>If you <em>don't</em> get any email from signing up, and are unable to register a new admin account, please see the Discourse <a href="https://meta.discourse.org/t/troubleshooting-email-on-a-new-discourse-install/16326">email troubleshooting checklist</a>.</p>

<p>If you are still unable to register a new admin account via email, see the <a href="https://meta.discourse.org/t/create-admin-account-from-console/17274">Create Admin Account from Console</a> walkthrough, but please note that <em>you will have a broken site</em> until you get normal SMTP email working.</p>

<p>That's it! You can now let users sign up and start managing your Discourse forum.</p>

<h3 id="post-installation-upgrade">Post-Installation Upgrade</h3>

<p>To <strong>upgrade Discourse to the latest version</strong>, visit <code>/admin/upgrade</code> and follow the instructions.</p>

<p><img src="https://assets.digitalocean.com/articles/Install_Discourse/4.png" alt="upgrade" /></p>

    