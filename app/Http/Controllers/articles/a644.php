<link rel='stylesheet' href='digital_ocean.css'><br> <br> 
      <h3 id="introduction">Introduction</h3>

<p>Diaspora is an open-source distributed social network.</p>

<p>It differs from most other social networks because it's decentralized — a true network with no central base. There are servers (called <em>pods</em>) all over the world, each containing the data of users who have chosen to register with it. These pods communicate with each other seamlessly so that you can register with any pod and communicate freely with your contacts, wherever they are on the network. You can read more on  Diaspora's <a href="https://diasporafoundation.org/about">about page</a>.</p>

<p>In this tutorial we are going to set up and configure a Diaspora pod. Among other<br />
things, you will learn:</p>

<ul>
<li>How to set up a Rails application (Diaspora) for production</li>
<li>How to configure MariaDB with Diaspora</li>
<li>How to set up Nginx as a reverse proxy server for Diaspora</li>
<li>The best practices for an SSL configuration for Nginx</li>
<li>How to write custom systemd unit files to use in a Rails application</li>
<li>For the security-conscious, there's a bonus section on how to configure SELinux to play well with Diaspora</li>
</ul>

<p><strong>Deviations from the official Diaspora installation guide</strong></p>

<p>The Diaspora <a href="https://wiki.diasporafoundation.org/">wiki</a> installation guides suggest we use Ruby Version Manager. While you could do that, we are going to use the system packaged Ruby instead. That way we avoid installing Ruby from source and having another dependency like RVM to worry about.</p>

<p>The official guide also suggests the use of <code>script/server</code>, a script that starts <code>unicorn</code> and <code>sidekiq</code>, two apps we need for Diaspora. Since CentOS 7 uses systemd, we'll write our own init files for these services instead.</p>

<h3 id="prerequisites">Prerequisites</h3>

<p>Please complete these prerequisites before starting the tutorial.</p>

<ul>
<li><p>CentOS 7 with 1 GB RAM</p>

<p>The minimum RAM needed for a small community is <strong>1 GB</strong>, so we will use the <strong>1 GB / 1 CPU</strong> Droplet.</p></li>
<li><p>sudo user</p>

<p>Most of the commands below need root privileges. Check the <a href="https://indiareads/community/tutorials/how-to-add-and-delete-users-on-a-centos-7-server">how to add users</a> guide to add your user and give it sudo privileges. This user account will be in addition to the <strong>diaspora</strong> user account which we will create later, and which Diaspora's services will run under with more limited privileges.</p></li>
<li><p>SSL certificate</p>

<p>Although Diaspora can run without an SSL certificate, Diaspora's mechanism for connecting to other pods requires a valid SSL certificate. For production, you should have a <a href="https://indiareads/community/tutorials/how-to-install-an-ssl-certificate-from-a-commercial-certificate-authority">paid SSL certificate</a>. We just need the two cert files created in this article (public, private), so you can skip the web server configuration part of that tutorial. We'll do that on our own.</p>

<p>Alternately, for testing purposes, you can generate a self-signed certificate. See <a href="https://indiareads/community/tutorials/openssl-essentials-working-with-ssl-certificates-private-keys-and-csrs#generating-ssl-certificates">this tutorial</a> for details, or just run this command from your home directory:</p>
<pre class="code-pre "><code langs="">openssl req \
   -newkey rsa:2048 -nodes -keyout ssl.key \
   -x509 -days 365 -out ssl.crt
</code></pre></li>
<li><p>Registered domain name pointing to your Droplet's IP</p></li>
<li><p>Swap file</p>

<p>For a 1 GB server, a swap file of at least 1 GB is needed. Follow the <a href="https://indiareads/community/tutorials/how-to-add-swap-on-centos-7">Add swap on CentOS 7</a> tutorial to set one up.</p></li>
<li><p>Follow the <a href="https://indiareads/community/tutorials/initial-server-setup-with-centos-7">Initial Server Setup with CentOS 7</a> guide</p></li>
<li><p>Follow the <a href="https://indiareads/community/tutorials/additional-recommended-steps-for-new-centos-7-servers">Additional Recommended Steps for New CentOS 7 Servers</a> guide</p></li>
</ul>

<h2 id="step-1-—-install-utilities">Step 1 — Install Utilities</h2>

<p>Let's install a couple of packages for utilities that will come in handy later:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo yum install deltarpm yum-cron vim
</li></ul></code></pre>
<p>Then update our system:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo yum update
</li></ul></code></pre>
<h2 id="step-2-—-enable-the-epel-repository">Step 2 — Enable the EPEL Repository</h2>

<p><a href="https://fedoraproject.org/wiki/EPEL">EPEL</a> stands for Extra Packages for Enterprise Linux, and it has some packages we'll need to install that are not part of the base CentOS repositories.</p>

<p>Let's enable it by installing the <code>epel-release</code> package and checking for any<br />
package updates:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo yum install epel-release
</li><li class="line" prefix="$">sudo yum update
</li></ul></code></pre>
<p>If you are asked to import the EPEL 7 gpg key as shown below, answer yes:</p>
<pre class="code-pre "><code langs="">Retrieving key from file:///etc/pki/rpm-gpg/RPM-GPG-KEY-EPEL-7
Importing GPG key 0x352C64E5:
  Userid     : "Fedora EPEL (7) <epel@fedoraproject.org>"
  Fingerprint: 91e9 7d7c 4a5e 96f1 7f3e 888f 6a2f aea2 352c 64e5
  Package    : epel-release-7-5.noarch (@extras)
  From       : /etc/pki/rpm-gpg/RPM-GPG-KEY-EPEL-7
  Is this ok [y/N]: <span class="highlight">y</span>
</code></pre>
<h2 id="step-3-—-install-packages-for-ruby-and-c">Step 3 — Install Packages for Ruby and C</h2>

<p>The following packages are needed by Diaspora and its gems that have native C extensions.</p>

<p>Install the packages:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo yum install git ruby ruby-devel libxslt-devel libxml2-devel gcc gcc-c++ automake net-tools libcurl-devel libffi-devel make redis nodejs ImageMagick-devel
</li></ul></code></pre>
<p><em>Redis</em> is an open-source key value data store which Diaspora uses as its database. Now that Redis is installed, let's configure it to be enabled at boot, and start the service:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo systemctl enable redis
</li><li class="line" prefix="$">sudo systemctl start redis
</li></ul></code></pre>
<h2 id="step-4-—-add-a-dedicated-diaspora-user">Step 4 — Add a Dedicated Diaspora User</h2>

<p>Create a user account to run Diaspora. You can name this account whatever you like, but this tutorial will assume that this user is called <strong>diaspora</strong>.</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo useradd diaspora
</li></ul></code></pre>
<h2 id="step-5-—-configure-the-firewall">Step 5 — Configure the Firewall</h2>

<p>Configuring and tightening the firewall is of great importance when setting up a production environment. The tool we will use is <code>firewalld</code>, which simplifies things compared to pure <code>iptables</code> commands.</p>

<p>First, start the <code>firewalld</code> service and enable it to start at boot:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo systemctl start firewalld
</li><li class="line" prefix="$">sudo systemctl enable firewalld
</li></ul></code></pre>
<p>Now we'll allow <code>ssh</code> on port <code>22</code>, <code>http</code> on port <code>80</code>, <code>https</code> on port <code>443</code> and <code>smtp</code> on port <code>25</code>. As your sudo user, add these services:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo firewall-cmd --permanent --add-service=ssh
</li><li class="line" prefix="$">sudo firewall-cmd --permanent --add-service=http
</li><li class="line" prefix="$">sudo firewall-cmd --permanent --add-service=https
</li><li class="line" prefix="$">sudo firewall-cmd --permanent --add-service=smtp
</li></ul></code></pre>
<p>Reload the firewall rules:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo firewall-cmd --reload
</li></ul></code></pre>
<p>For more information on using <code>firewalld</code>, read the <a href="https://indiareads/community/tutorials/additional-recommended-steps-for-new-centos-7-servers">Additional Recommended Steps for New CentOS 7 Servers</a> tutorial.</p>

<h2 id="step-6-—-install-and-secure-mariadb">Step 6 — Install and Secure MariaDB</h2>

<p>The next big step is to set up a database for Diaspora. In this tutorial, we will use MariaDB, although we'll include a few tidbits for PostgreSQL throughout the tutorial.</p>

<p>Install the required packages:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo yum install mariadb-server mariadb-devel
</li></ul></code></pre>
<p>Ensure that MariaDB is started and enabled on boot:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo systemctl start mariadb
</li><li class="line" prefix="$">sudo systemctl enable mariadb
</li></ul></code></pre>
<p>Secure the MariaDB installation by running the following command:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo mysql_secure_installation
</li></ul></code></pre>
<p>Answer the questions as shown below in <span class="highlight">red</span> text and add a strong root password when prompted:</p>
<pre class="code-pre "><code langs="">Enter current password for root (enter for none): <span class="highlight">ENTER</span>
Set root password? [Y/n] <span class="highlight">Y</span>
Remove anonymous users? [Y/n] <span class="highlight">Y</span>
Disallow root login remotely? [Y/n] <span class="highlight">Y</span>
Remove test database and access to it? [Y/n] <span class="highlight">Y</span>
Reload privilege tables now? [Y/n] <span class="highlight">Y</span>
</code></pre>
<h2 id="step-7-—-create-diaspora-user-and-database">Step 7 — Create Diaspora User and Database</h2>

<p>Next we will log in to MariaDB to create the <strong>diaspora</strong> user. When prompted, enter the <strong>root</strong> password you created above:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">mysql -u root -p
</li></ul></code></pre>
<p>Create a user for Diaspora. Change <code><span class="highlight">password</span></code> in the command below to a real password. This should not be the same as the <strong>root</strong> password you provided during <code>mysql_secure_installation</code>.</p>
<pre class="code-pre custom_prefix"><code langs=""><ul class="prefixed"><li class="line" prefix="MariaDB [(none)]>">CREATE USER 'diaspora'@'localhost' IDENTIFIED BY '<span class="highlight">password</span>';
</li></ul></code></pre>
<p>Create the Diaspora production database:</p>
<pre class="code-pre custom_prefix"><code langs=""><ul class="prefixed"><li class="line" prefix="MariaDB [(none)]>">CREATE DATABASE IF NOT EXISTS `diaspora_production` DEFAULT CHARACTER SET `utf8mb4` COLLATE `utf8mb4_bin`;
</li></ul></code></pre>
<p>Grant the MariaDB <strong>diaspora</strong> user the necessary permissions on the database:</p>
<pre class="code-pre custom_prefix"><code langs=""><ul class="prefixed"><li class="line" prefix="MariaDB [(none)]>">GRANT SELECT, INSERT, UPDATE, DELETE, CREATE, DROP, INDEX, ALTER, LOCK TABLES ON `diaspora_production`.* TO 'diaspora'@'localhost';
</li></ul></code></pre>
<p>Quit the database session.</p>
<pre class="code-pre custom_prefix"><code langs=""><ul class="prefixed"><li class="line" prefix="MariaDB [(none)]>">\q
</li></ul></code></pre>
<p>Try connecting to the new database with the user <strong>diaspora</strong> (use the password you entered for <code>IDENTIFIED BY '<span class="highlight">password</span>'</code> above).</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">mysql -u diaspora -p -D diaspora_production
</li></ul></code></pre>
<p>You should now see the prompt: <code>MariaDB [diaspora_production]></code>. Quit the<br />
database session by entering:</p>
<pre class="code-pre custom_prefix"><code langs=""><ul class="prefixed"><li class="line" prefix="MariaDB [diaspora_production]>">\q
</li></ul></code></pre>
<p>We are now done installing the MariaDB database.</p>

<p>For additional security, check the detailed article on <a href="https://indiareads/community/tutorials/how-to-secure-mysql-and-mariadb-databases-in-a-linux-vps">How To Secure MySQL and MariaDB Databases</a>.</p>

<p>Next we'll grab the Diaspora source code and get it configured to run on your VPS.</p>

<h2 id="step-8-—-install-bundler">Step 8 — Install Bundler</h2>

<p>Bundler is the package manager for Ruby gems. We will install it to be globally available.</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo gem install bundler
</li></ul></code></pre>
<p>Since the <code>bundle</code> executable is installed in <code>/usr/local/bin/bundle</code>, make a symbolic link in order to include it in users' <code>PATH</code>.</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo ln -sf /usr/local/bin/bundle /usr/bin/bundle
</li></ul></code></pre>
<h2 id="step-9-—-fetch-the-diaspora-source-code">Step 9 — Fetch the Diaspora Source Code</h2>

<p>Diaspora is developed in three main branches. <code>stable</code> contains code that is considered stable and is to be released, <code>master</code> has the stable tagged versions which we will use, while <code>develop</code> has the latest code with possible bugs.</p>

<p>Change to the <strong>diaspora</strong> user account.</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo su - diaspora
</li></ul></code></pre>
<p>Check out the master branch. At the time of this writing, it contains Diaspora version <code>0.5.1.1</code>.</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">git clone -b master https://github.com/diaspora/diaspora.git
</li></ul></code></pre>
<h2 id="step-10-—-configure-the-diaspora-database">Step 10 — Configure the Diaspora Database</h2>

<p>Now change into the working directory.</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">cd ~/diaspora/
</li></ul></code></pre>
<p>Copy the example database configuration file and open <code>database.yml</code> with your favorite editor.</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">cp config/database.yml.example config/database.yml
</li><li class="line" prefix="$">vim config/database.yml
</li></ul></code></pre>
<p>Now we'll edit the configuration file to match the database settings created earlier.</p>

<p><span class="note"><strong>Note:</strong> Be very careful not to break the indentation when editing yaml (<code>.yml</code>) files. Always use spaces instead of tabs.<br /></span></p>

<p>Edit the very first lines where the <code>mysql2</code> adapter is defined. (MariaDB is a drop-in replacement for MySQL.) Replace <strong>root</strong> with <strong>diaspora</strong> and change the <code><span class="highlight">password</span></code> to the password for the database user <strong>diaspora</strong> you created earlier. Do not remove the quotes. When finished, save and close the file.</p>
<pre class="code-pre "><code langs="">mysql: &mysql
  adapter: mysql2
  host: "localhost"
  port: 3306
  username: "<span class="highlight">diaspora</span>"
  password: "<span class="highlight">password</span>"
  encoding: utf8mb4
  collation: utf8mb4_bin
</code></pre>
<p><span class="note"><strong>Note:</strong> You'll need to fill out the PostgreSQL section instead if you're using that database, and change the database to PostgreSQL.<br /></span></p>

<h2 id="step-11-—-configure-diaspora-39-s-basic-settings">Step 11 — Configure Diaspora's Basic Settings</h2>

<p>Let's start by copying the example configuration file.</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">cp config/diaspora.yml.example config/diaspora.yml
</li></ul></code></pre>
<p>You will need to edit a few settings in this file for Diaspora to work properly. Read the whole file carefully to grasp the idea of what it does and how. It is pretty self-explanatory, but let's look at some of the most crucial settings.</p>

<p>Open the file in a text editor.</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">vim config/diaspora.yml
</li></ul></code></pre>
<p>Line 39 — Uncomment the <code>url</code> line so it looks like the following:</p>
<pre class="code-pre "><code langs="">url: "<span class="highlight">https://example.org/</span>"
</code></pre>
<p>Replace <code><span class="highlight">https://example.org/</span></code> with your own domain name. The URL you use here will get hard-coded into the database, so make sure it's accurate. <strong>Read the comments above this configuration line for details</strong>.</p>

<p>Line 47 — Uncomment the <code>certificate_authorities</code> line so it looks like the following:</p>
<pre class="code-pre "><code langs="">certificate_authorities: '/etc/pki/tls/certs/ca-bundle.crt'
</code></pre>
<p><span class="note"><strong>Note:</strong> There are two <code>certificate_authorities</code> lines; make sure you uncomment the one for CentOS.<br /></span></p>

<p>Line 166 — Uncomment the <code>rails_environment</code> line and replace <code>development</code> with <code>production</code> so it looks like the following:</p>
<pre class="code-pre "><code langs="">rails_environment: '<span class="highlight">production</span>'
</code></pre>
<p>Save and close the file.</p>

<p>These are the minimum changes required to have a working Diaspora pod. There are many more options to explore and configure to your liking such as connecting with other social networks (Twitter, WordPress, Tumblr, Facebook). Please read through the file and make your desired configuration changes.</p>

<h2 id="step-12-—-install-gems-and-set-up-the-database">Step 12 — Install Gems and Set Up the Database</h2>

<p>Install the needed gems, set up the database, and precompile the assets.</p>

<p>Make sure you are in the right directory:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">cd /home/diaspora/diaspora/
</li></ul></code></pre>
<p>First we tell the nokogiri gem to use the system libxm2 library we previously installed:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">bundle config build.nokogiri --use-system-libraries
</li></ul></code></pre>
<p>Next, use bundler to install the needed gems:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">RAILS_ENV=production bin/bundle install --without test development --deployment
</li></ul></code></pre>
<p>Set up the database:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">RAILS_ENV=production bin/rake db:create db:schema:load
</li></ul></code></pre>
<p>Precompile the assets:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">RAILS_ENV=production bin/rake assets:precompile
</li></ul></code></pre>
<p>At this point, you can leave the <strong>diaspora</strong> user account and switch back to the sudo user you created when following the Prerequisites of this tutorial.</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">exit
</li></ul></code></pre>
<h2 id="step-13-—-configure-the-diaspora-systemd-services">Step 13 — Configure the Diaspora systemd Services</h2>

<p>Diaspora consists of two main services that need to run:</p>

<ul>
<li>unicorn, the application server</li>
<li>sidekiq, for background jobs processing</li>
</ul>

<p>A script is provided for this case, which resides in <code>script/server</code>, but we'll use <em>systemd</em> instead. systemd is the init system used in CentOS 7.</p>

<p>For a better understanding of how systemd works, read the following articles:</p>

<ul>
<li><a href="https://indiareads/community/tutorials/how-to-use-systemctl-to-manage-systemd-services-and-units">How To Use Systemctl to Manage Systemd Services and Units</a></li>
<li><a href="https://indiareads/community/tutorials/understanding-systemd-units-and-unit-files">Understanding Systemd Units and Unit Files</a></li>
<li><a href="https://indiareads/community/tutorials/how-to-use-journalctl-to-view-and-manipulate-systemd-logs">How To Use Journalctl to View and Manipulate Systemd Logs</a></li>
</ul>

<h3 id="create-the-tmpfiles-directory">Create the tmpfiles Directory</h3>

<p>Create the directory which will hold the <code>unicorn</code> Unix socket.</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo mkdir /run/diaspora
</li></ul></code></pre>
<p>Change ownership to the <strong>diaspora</strong> user and set the permissions.</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo chown diaspora:diaspora /run/diaspora
</li><li class="line" prefix="$">sudo chmod 750 /run/diaspora
</li></ul></code></pre>
<p>Since the <code>/run</code> and <code>/var/run</code> directories are volatile, the <code>/run/diaspora</code> directory we just created will not survive a system reboot. With systemd, we can use <em>tmpfiles</em> to preserve this directory between reboots.</p>

<p>Open <code>/etc/tmpfiles.d/diaspora.conf</code> for editing.</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo vim /etc/tmpfiles.d/diaspora.conf
</li></ul></code></pre>
<p>Paste in the following line:</p>
<div class="code-label " title="/etc/tmpfiles.d/diaspora.conf">/etc/tmpfiles.d/diaspora.conf</div><pre class="code-pre "><code langs="">d /run/diaspora 0750 diaspora diaspora - -
</code></pre>
<p>The configuration format is one line per path, containing type, path, mode, ownership, age, and argument fields respectively. You can learn more about <code>tmpfiles.d</code> at its <a href="http://www.freedesktop.org/software/systemd/man/tmpfiles.d.html">official web page</a> or its man page.</p>

<h3 id="unicorn">Unicorn</h3>

<p>First we will edit <code>diaspora.yml</code> so that the service listens to a Unix socket. We will change back to the <em>diaspora</em> user for this.</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo su - diaspora
</li></ul></code></pre>
<p>Open the config file for editing:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">vim /home/diaspora/diaspora/config/diaspora.yml
</li></ul></code></pre>
<p>Uncomment line 157 so it reads:</p>
<div class="code-label " title="/home/diaspora/diaspora/config/diaspora.yml">/home/diaspora/diaspora/config/diaspora.yml</div><pre class="code-pre "><code langs="">listen: 'unix:/run/diaspora/diaspora.sock'
</code></pre>
<p>Save and exit the file.</p>

<p>Now go back to your sudo user.</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">su - <span class="highlight">username</span>
</li></ul></code></pre>
<p>Create the <code>unicorn.service</code> file.</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo vim /etc/systemd/system/diaspora-unicorn.service
</li></ul></code></pre>
<p>Paste in the following content exactly to create the script. Remember to check the earlier links about systemd if you need help understanding what's in this file:</p>
<div class="code-label " title="/etc/systemd/system/diaspora-unicorn.service">/etc/systemd/system/diaspora-unicorn.service</div><pre class="code-pre "><code langs="">[Unit]
Description=Diaspora Unicorn Server
Requires=redis.service
After=redis.service network.target

[Service]
User=diaspora
Group=diaspora
SyslogIdentifier=diaspora-unicorn
WorkingDirectory=/home/diaspora/diaspora
Environment=RAILS_ENV=production
## Uncomment if postgres is installed
#Environment=DB=postgres

PIDFile=/run/diaspora/unicorn.pid
Restart=always

CPUAccounting=true
emoryAccounting=true
BlockIOAccounting=true
CapabilityBoundingSet=
PrivateTmp=true
NoNewPrivileges=true

ExecStart=/usr/bin/bundle exec "unicorn_rails -c config/unicorn.rb -E production"

[Install]
WantedBy=multi-user.target
</code></pre>
<p><span class="note"><strong>Note:</strong> Uncomment the <code>Environment=DB=postgres</code> line if you are using PostgreSQL. For MariaDB, no change is needed.<br /></span></p>

<p>Start the unicorn service and enable it on boot.</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo systemctl start diaspora-unicorn
</li><li class="line" prefix="$">sudo systemctl enable diaspora-unicorn
</li></ul></code></pre>
<p>Now check the service status:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">systemctl status diaspora-unicorn
</li></ul></code></pre>
<p>If all went well, that command should return an output similar to this:</p>
<pre class="code-pre "><code langs="">diaspora-unicorn.service - Diaspora Unicorn Server
Loaded: loaded (/etc/systemd/system/diaspora-unicorn.service; enabled)
Active: active (running) since Tue 2015-06-23 10:18:25 EDT; 16s ago
Main PID: 16658 (ruby)
CGroup: /system.slice/diaspora-unicorn.service
└─16658 ruby /home/diaspora/diaspora/vendor/bundle/ruby/bin/unicorn_rails -c config/unicorn.rb -E production
</code></pre>
<h3 id="sidekiq">Sidekiq</h3>

<p>Likewise with <code>sidekiq</code>, let's create the <code>sidekiq.service</code> file.</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo vim /etc/systemd/system/diaspora-sidekiq.service
</li></ul></code></pre>
<p>Paste in the following:</p>
<div class="code-label " title="/etc/systemd/system/diaspora-sidekiq.service">/etc/systemd/system/diaspora-sidekiq.service</div><pre class="code-pre "><code langs="">[Unit]
Description=Diaspora Sidekiq Worker
Requires=redis.service
After=redis.service network.target

[Service]
User=diaspora
Group=diaspora
SyslogIdentifier=diaspora-sidekiq
WorkingDirectory=/home/diaspora/diaspora
Environment=RAILS_ENV=production
## Uncomment if postgres is installed
#Environment=DB=postgres

Restart=always

CPUAccounting=true
emoryAccounting=true
BlockIOAccounting=true
CapabilityBoundingSet=
PrivateTmp=true

ExecStart=/usr/bin/bundle exec "sidekiq -e production -L log/sidekiq.log >> log/sidekiq.log 2>&1"

[Install]
WantedBy=multi-user.target
</code></pre>
<p><span class="note"><strong>Note:</strong> Uncomment the <code>Environment=DB=postgres</code> line if you are using PostgreSQL. For MariaDB, no change is needed.<br /></span></p>

<p>Start the sidekiq service and enable it at boot:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo systemctl start diaspora-sidekiq
</li><li class="line" prefix="$">sudo systemctl enable diaspora-sidekiq
</li></ul></code></pre>
<p>Now run:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">systemctl status diaspora-sidekiq
</li></ul></code></pre>
<p>The output should be similar to this:</p>
<pre class="code-pre "><code langs="">diaspora-sidekiq.service - Diaspora Sidekiq Worker
   Loaded: loaded (/etc/systemd/system/diaspora-sidekiq.service; enabled)
   Active: active (running) since Mon 2014-12-29 08:21:45 UTC; 44s ago
 Main PID: 18123 (sh)
   CGroup: /system.slice/diaspora-sidekiq.service
           ├─18123 sh -c sidekiq -e production -L log/sidekiq.log >> log/sidekiq.log 2>&1
           └─18125 sidekiq 2.17.7 diaspora [0 of 5 busy]
</code></pre>
<h2 id="step-14-—-install-nginx">Step 14 — Install Nginx</h2>

<p>Nginx will be serve as our reverse proxy so that nearly all requests will be sent to Unicorn. Only the files in <code>public/</code> will be served directly by Nginx.</p>

<p>Let's first install the web server.</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo yum install nginx
</li></ul></code></pre>
<p>Start the service and enable it on boot:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo systemctl start nginx
</li><li class="line" prefix="$">sudo systemctl enable nginx
</li></ul></code></pre>
<h2 id="step-15-—-give-nginx-permissions">Step 15 — Give Nginx Permissions</h2>

<p>For Nginx to be able to access the <strong>diaspora</strong> user's home folder, we need to add the <strong>nginx</strong> user to the <strong>diaspora group</strong>:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo usermod -a -G diaspora nginx
</li></ul></code></pre>
<p>Finally, we will relax the <strong>diaspora</strong> user's home directory permissions to allow read and execute access to the diaspora group:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo chmod 750 /home/diaspora/
</li></ul></code></pre>
<h2 id="step-16-—-upload-ssl-certificate-and-enable-forward-secrecy">Step 16 — Upload SSL Certificate and Enable Forward Secrecy</h2>

<p>You will now need the SSL certificate files from your Certificate Authority. In the configuration example below, we use <code>/etc/ssl/diaspora/ssl.crt</code> for the public certificate and <code>/etc/ssl/diaspora/ssl.key</code> for the private key.</p>

<p>Create a directory to store the certificate files.</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo mkdir /etc/ssl/diaspora
</li></ul></code></pre>
<p>Upload or move the certificate and key files to the server and place them under the<br />
directory we just created. SFTP or SCP can be used to do this; see <a href="https://indiareads/community/tutorials/how-to-use-filezilla-to-transfer-and-manage-files-securely-on-your-vps">this tutorial</a>.</p>

<p><span class="note"><strong>Note:</strong> If you created a self-signed certificate, move to that directory and copy the files to <code>/etc/ssl/diaspora</code> with the <code>sudo cp ssl.crt ssl.key /etc/ssl/diaspora</code> command.<br /></span></p>

<p>Forward secrecy has become an essential part of SSL/TLS encrypted communications. For a more detailed explanation of forward secrecy, see this <a href="https://wiki.mozilla.org/Security/Server_Side_TLS#Forward_Secrecy">Mozilla server security wiki entry</a>.</p>

<p>Change again to the system's <strong>root</strong> user.</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo su -
</li></ul></code></pre>
<p>Create the <code>dhparam.pem</code> file.</p>
<pre class="code-pre super_user"><code langs=""><ul class="prefixed"><li class="line" prefix="#">openssl dhparam 2048 > /etc/ssl/dhparam.pem
</li></ul></code></pre>
<p>The dhparam file might take several minutes to complete. When it's finished, log back in to your sudo user's account.</p>
<pre class="code-pre super_user"><code langs=""><ul class="prefixed"><li class="line" prefix="#">su - <span class="highlight">username</span>
</li></ul></code></pre>
<h2 id="step-17-—-disable-the-default-site-in-nginx-conf">Step 17 — Disable the default site in nginx.conf</h2>

<p>We will now modify <code>/etc/nginx/nginx.conf</code> so that the default <em>Welcome to Nginx</em> message doesn't interfere with the Diaspora configuration file we will create.</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo vim /etc/nginx/nginx.conf
</li></ul></code></pre>
<p>Search under the <code>server</code> block for the lines including <code>default_server;</code>. Remove <code>default_server</code> from these entries so that the area of the server block looks like this:</p>
<div class="code-label " title="/etc/nginx/nginx.conf">/etc/nginx/nginx.conf</div><pre class="code-pre "><code langs="">server {
<span class="highlight">listen 80;</span>
<span class="highlight">listen [::]:80;</span>
server_name localhost;
root /usr/share/nginx/html;
</code></pre>
<p><span class="note"><strong>Note:</strong> You could even comment out the whole <code>server</code> block if you like; that would work too.<br /></span></p>

<h2 id="step-18-—-create-diaspora-39-s-own-nginx-configuration-file">Step 18 — Create Diaspora's Own Nginx Configuration File</h2>

<p>Create a new nginx configuration file for our Diaspora pod:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo vim /etc/nginx/conf.d/diaspora.conf
</li></ul></code></pre>
<p>Paste in the following content; explanations are given just below the code:</p>
<div class="code-label " title="/etc/nginx/conf.d/diaspora.conf">/etc/nginx/conf.d/diaspora.conf</div><pre class="code-pre "><code langs="">upstream diaspora {
  server unix:/run/diaspora/diaspora.sock fail_timeout=0;
}
server {
  listen [::]:80;
  listen 80;
  server_name _;
  return 301 https://<span class="highlight">example.com</span>$request_uri;
}
server {
  listen [::]:443 ssl spdy;
  listen 443 ssl spdy;
  server_name <span class="highlight">example.com</span>;
  root /home/diaspora/diaspora/public;
  server_tokens off;
  error_log /var/log/nginx/diaspora_error.log;

  # Configure maximum picture size
  # Note that Diaspora has a client side check set at 4M
  client_max_body_size 4M;

  ## SSL settings
  ssl_certificate <span class="highlight">/etc/ssl/diaspora/ssl.crt</span>;
  ssl_certificate_key <span class="highlight">/etc/ssl/diaspora/ssl.key</span>;

  # https://wiki.mozilla.org/Security/Server_Side_TLS
  ssl_dhparam /etc/ssl/dhparam.pem;
  ssl_protocols TLSv1 TLSv1.1 TLSv1.2;
  ssl_ciphers 'ECDHE-RSA-AES128-GCM-SHA256:ECDHE-ECDSA-AES128-GCM-SHA256:ECDHE-RSA-AES256-GCM-SHA384:ECDHE-ECDSA-AES256-GCM-SHA384:DHE-RSA-AES128-GCM-SHA256:DHE-DSS-AES128-GCM-SHA256:kEDH+AESGCM:ECDHE-RSA-AES128-SHA256:ECDHE-ECDSA-AES128-SHA256:ECDHE-RSA-AES128-SHA:ECDHE-ECDSA-AES128-SHA:ECDHE-RSA-AES256-SHA384:ECDHE-ECDSA-AES256-SHA384:ECDHE-RSA-AES256-SHA:ECDHE-ECDSA-AES256-SHA:DHE-RSA-AES128-SHA256:DHE-RSA-AES128-SHA:DHE-DSS-AES128-SHA256:DHE-RSA-AES256-SHA256:DHE-DSS-AES256-SHA:DHE-RSA-AES256-SHA:AES128-GCM-SHA256:AES256-GCM-SHA384:AES128:AES256:AES:!aNULL:!eNULL:!EXPORT:!DES:!RC4:!MD5:!PSK';
  ssl_session_timeout 5m;
  ssl_prefer_server_ciphers on;
  ssl_session_cache shared:SSL:50m;
  add_header Strict-Transport-Security "max-age=31536000";

  location / {
    # Proxy if requested file not found
    try_files $uri $uri/index.html $uri.html @diaspora;
  }

  location @diaspora {
    gzip off;
    proxy_set_header  X-Forwarded-Ssl   on;
    proxy_set_header  X-Real-IP         $remote_addr;
    proxy_set_header  X-Forwarded-For   $proxy_add_x_forwarded_for;
    proxy_set_header  X-Forwarded-Proto https;
    proxy_set_header  Host              $http_host;
    proxy_set_header  X-Frame-Options   SAMEORIGIN;
    proxy_redirect                      off;
    proxy_pass http://diaspora;
  }
}
</code></pre>
<p>Replace the following variables:</p>

<ul>
<li><code><span class="highlight">example.com</span></code> with your own registered domain name; you'll need to do this in <strong>two</strong> places</li>
<li><code><span class="highlight">/etc/ssl/diaspora/ssl.crt</span></code> with the path to your own public certificate</li>
<li><code><span class="highlight">/etc/ssl/diaspora/ssl.key</span></code> with the path to your own private key</li>
</ul>

<p>Explanation:</p>

<ul>
<li>The <code>upstream</code> block is where we set up the Unix socket Diaspora listens to (which we also set in Unicorn earlier). This is used later as the <code>proxy_pass</code> directive.</li>
<li>The first <code>server</code> block listens to the standard HTTP port <code>80</code> and redirects any requests to HTTPS.</li>
<li>The second <code>server</code> block listens to the port <code>443</code> (SSL) and sets some strong SSL parameters which were taken from the Mozilla wiki.</li>
</ul>

<p>For more about Nginx server blocks in general, please read <a href="https://indiareads/community/tutorials/how-to-set-up-nginx-server-blocks-on-centos-7">this tutorial</a>.</p>

<p>After all modifications are complete, check the configuration file for any errors.</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo nginx -t
</li></ul></code></pre>
<p>If all went well, this should return:</p>
<pre class="code-pre "><code langs="">nginx: the configuration file /etc/nginx/nginx.conf syntax is ok
nginx: configuration file /etc/nginx/nginx.conf test is successful
</code></pre>
<p>Restart Nginx to apply the changes.</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo systemctl restart nginx
</li></ul></code></pre>
<p>If you now visit your Diaspora pod's domain name in your browser (<code>https://example.com</code>, for example), you should reach the Diaspora welcome page. Congratulations!</p>

<p><img src="https://assets.digitalocean.com/articles/socialnetwork_diaspora/diaspora.png" alt="Diaspora welcome page" /></p>

<p><span class="note"><strong>Note:</strong> Click through the browser warning if you use a self-signed certificate.<br /></span></p>

<h2 id="step-19-—-create-diaspora-user">Step 19 — Create Diaspora User</h2>

<p>Let's create your first Diaspora user. Click the link in <strong>Start by creating an account.</strong></p>

<p>Fill in the details to create a new Diaspora user. Then, you should be able to view your user's home page and start using the Diaspora social network.</p>

<h2 id="step-20-—-configure-selinux-optional">Step 20 — Configure SELinux (Optional)</h2>

<blockquote>
<p><strong>Warning:</strong> If you're not familiar with SELinux, please be aware that <strong>this can break things</strong>. You can skip this section and start using Diaspora.</p>
</blockquote>

<p>CentOS 7 Droplets have SELinux disabled by default. For maximum security, you can enable SELinux and configure it to work with your Diaspora pod's services. If you are new to SELinux, here is a series of tutorials you can refer to for more information:</p>

<ul>
<li><a href="https://indiareads/community/tutorials/an-introduction-to-selinux-on-centos-7-part-1-basic-concepts">An Introduction to SELinux on CentOS 7 – Part 1: Basic Concepts</a></li>
<li><a href="https://indiareads/community/tutorials/an-introduction-to-selinux-on-centos-7-part-2-files-and-processes">An Introduction to SELinux on CentOS 7 – Part 2: Files and Processes</a></li>
<li><a href="https://indiareads/community/tutorials/an-introduction-to-selinux-on-centos-7-part-3-users">An Introduction to SELinux on CentOS 7 – Part 3: Users</a></li>
</ul>

<h3 id="enable-selinux">Enable SELinux</h3>

<p>Open <code>/etc/selinux/config</code> in a text editor.</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo vim /etc/selinux/config
</li></ul></code></pre>
<p>Change the <code>SELINUX</code> setting from <code>disabled</code> to <code><span class="highlight">permissive</span></code> as shown below. It is necessary to first set a permissive status because every file in the system needs to have its context labeled before SELinux can be enforced.</p>
<pre class="code-pre "><code langs=""># This file controls the state of SELinux on the system.
# SELINUX= can take one of these three values:
#     enforcing - SELinux security policy is enforced.
#     permissive - SELinux prints warnings instead of enforcing.
#     disabled - No SELinux policy is loaded.
SELINUX=<span class="highlight">permissive</span>
# SELINUXTYPE= can take one of these two values:
#     targeted - Targeted processes are protected,
#     minimum - Modification of targeted policy. Only selected processes are protected.
#     mls - Multi Level Security protection.
SELINUXTYPE=targeted
</code></pre>
<p>Save and close the file.</p>

<p>After making this change, reboot the Droplet for the setting to take effect. Simply type:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo reboot
</li></ul></code></pre>
<p>Enter your sudo user's password to reboot the system. Then SSH back in to the Droplet and change back into your sudo user's account with:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">su - <span class="highlight">username</span>
</li></ul></code></pre>
<p>Now, edit <code>/etc/selinux/config</code> once again and set the <code>SELINUX</code> setting to <code><span class="highlight">enforcing</span></code>.</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo vim /etc/selinux/config
</li></ul></code></pre>
<p>When finished, the line should say this:</p>
<pre class="code-pre "><code langs="">SELINUX=<span class="highlight">enforcing</span>
</code></pre>
<p>Save and close the file. <strong>Reboot the Droplet once more.</strong></p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo reboot
</li></ul></code></pre>
<p>Then SSH back in to your Droplet after it comes back online.</p>

<h3 id="selinux-nginx-policy">SELinux Nginx Policy</h3>

<p>From here, you want to remain as the <code>root</code> user. If you now visit your domain, you will be presented with a <strong>502</strong> error. In our case, SELinux is blocking Nginx's socket and access to the <strong>diaspora</strong> user's home directory.</p>

<p>You can check the audit logs with:</p>
<pre class="code-pre super_user"><code langs=""><ul class="prefixed"><li class="line" prefix="#">grep denied /var/log/audit/audit.log
</li></ul></code></pre>
<p>You should see messages like the ones below:</p>
<pre class="code-pre "><code langs="">type=AVC msg=audit(1424394514.632:385): avc:  denied  { search } for  pid=1114 comm="nginx" name="diaspora" dev="vda1" ino=783369 scontext=system_u:system_r:httpd_t:s0 tcontext=system_u:object_r:user_home_dir_t:s0 tclass=dir
type=AVC msg=audit(1424394514.632:386): avc:  denied  { write } for  pid=1114 comm="nginx" name="diaspora.sock" dev="tmpfs" ino=21382 scontext=system_u:system_r:httpd_t:s0 tcontext=system_u:object_r:var_run_t:s0 tclass=sock_file
</code></pre>
<p>Install the tools below to begin fixing the problem:</p>
<pre class="code-pre super_user"><code langs=""><ul class="prefixed"><li class="line" prefix="#">yum install policycoreutils-{python,devel} setroubleshoot-server
</li></ul></code></pre>
<p>We'll grep through the <code>audit.log</code> file and allow the <em>Denied</em> entries in our SELinx policy. Run:</p>
<pre class="code-pre super_user"><code langs=""><ul class="prefixed"><li class="line" prefix="#">grep nginx /var/log/audit/audit.log | audit2allow -M nginx_diaspora
</li></ul></code></pre>
<p>The generated SELinux policy is stored in the file <code>nginx_diaspora.te</code> in your root's <code>/home</code> directory (though you can organize your SELinux policies in any location). The binary <code>nginx_diaspora.pp</code> should be passed to the <code>semodule</code> command to import the policy. Open <code>nginx_diaspora.te</code> to see what is now allowed by SELinux.</p>
<pre class="code-pre super_user"><code langs=""><ul class="prefixed"><li class="line" prefix="#">vim nginx_diaspora.te
</li></ul></code></pre>
<p>It should contain the following entries. If not, replace the contents of <code>nginx_diaspora.te</code> with what's shown below.</p>
<pre class="code-pre "><code langs="">module nginx_diaspora 1.0;

require {
        type var_run_t;
        type httpd_t;
        type user_home_t;
        type init_t;
        class sock_file write;
        class unix_stream_socket connectto;
        class file { read open };
}


#============= httpd_t ==============
allow httpd_t init_t:unix_stream_socket connectto;

#!!!! This avc can be allowed using the boolean 'httpd_read_user_content'
allow httpd_t user_home_t:file { read open };
allow httpd_t var_run_t:sock_file write;
</code></pre>
<p>Many of the allowed contexts could probably be narrowed down, but this is an appropriate starting point. Then let's import the policy module.</p>
<pre class="code-pre super_user"><code langs=""><ul class="prefixed"><li class="line" prefix="#">semodule -i nginx_diaspora.pp
</li></ul></code></pre>
<p>If you now refresh the page in your browser, you should see the Diaspora welcome page again. Congratulations on configuring an SELinux-hardened Diaspora pod running on CentOS 7!</p>

<h3 id="brief-selinux-troubleshooting">Brief SELinux Troubleshooting</h3>

<p>If the welcome page loads but shows broken image placeholders and not actual images, follow these steps:</p>

<ol>
<li>Run the command below to <code>grep</code> through <code>audit.log</code> and add new <em>Denied</em> entries to the Nginx policy.</li>
</ol>
<pre class="code-pre super_user"><code langs=""><ul class="prefixed"><li class="line" prefix="#">grep nginx /var/log/audit/audit.log | audit2allow -M nginx_diaspora
</li></ul></code></pre>
<ol>
<li>Reload the policy module.</li>
</ol>
<pre class="code-pre super_user"><code langs=""><ul class="prefixed"><li class="line" prefix="#">semodule -i nginx_diaspora.pp
</li></ul></code></pre>
<p>You can repeat these steps a few times.</p>

<p><span class="note"><strong>Note:</strong> You can use the command below to open a real-time output stream from <code>/var/log/messages</code>. This will show you human-readable SELinux error messages and provide suggested fixes.<br /></span></p>
<pre class="code-pre super_user"><code langs=""><ul class="prefixed"><li class="line" prefix="#">tail -f /var/log/messages
</li></ul></code></pre>
<p>Here is an example readout:</p>
<pre class="code-pre "><code langs="">. . .

*****  Plugin catchall (100. confidence) suggests   **************************

If you believe that nginx should be allowed write access on the  sock_file by default.
Then you should report this as a bug.
You can generate a local policy module to allow this access.
Do
allow this access for now by executing:
# grep nginx /var/log/audit/audit.log | audit2allow -M mypol
# semodule -i mypol.pp

. . .
</code></pre>
<h2 id="conclusion">Conclusion</h2>

<p>Now that you've set up your Diaspora pod, you can start inviting your friends and family to connect to it. While it does lack some features present in the big commercial, closed-source social networks, one of Diaspora's advantages is that you get to own your data.</p>

<p>From here, you can read the Diaspora wiki for <a href="https://wiki.diasporafoundation.org/FAQ_for_pod_maintainers">pod maintainers</a> and register your pod to a global pod list so that others can benefit from your installation.</p>

    