<link rel='stylesheet' href='digital_ocean.css'><br><img src=https://community-cdn-digitalocean-com.global.ssl.fastly.net/assets/tutorials/images/large/deploy_rails_app_tw.png?1426699773/> <br> 
      <h3 id="introduction">Introduction</h3>

<p>In this tutorial, we will demonstrate how to install <a href="https://www.phusionpassenger.com">Phusion Passenger</a> as your Rails-friendly web server, which is easy to install, configure, and maintain. We will integrate it into Apache on Ubuntu 14.04. By the end of this tutorial, we will have a test Rails application deployed on our Droplet.</p>

<p>If you prefer Nginx over Apache, take a look at <a href="https://indiareads/community/tutorials/how-to-deploy-a-rails-app-with-passenger-and-nginx-on-ubuntu-14-04">how to deploy a Rails app with Passenger and Nginx on Ubuntu 14.04</a> by following the link.</p>

<h2 id="prerequisites">Prerequisites</h2>

<p>The first step is to create a new Droplet. For smaller sites it is enough to use the 512 MB plan.</p>

<p>You may want to choose the 32-bit Ubuntu image because of smaller memory consumption (64-bit programs use about 50% more memory then their 32-bit counterparts). However, if you need a bigger machine, or there is a chance that you will upgrade to more than 4 GB of RAM, you should consider the 64-bit version.</p>

<p>Be sure to use Ubuntu 14.04. At the time of this writing, Ubuntu 14.10 does not have a Passanger APT repository yet. Moreover, Ubuntu 14.04 has an additional benefit: it's a LTS version, which stands for "long term support." LTS releases are designed to be stable platforms that we can stick with for a long time. Ubuntu guarantees LTS releases will receive security updates and other bug fixes for five years.</p>

<ul>
<li>Ubuntu 14.04 32-bit Droplet</li>
</ul>

<h2 id="step-1-—-add-a-sudo-user">Step 1 — Add a Sudo User</h2>

<p>After the Droplet is created, you should create a system user and secure the server. You can do so by following the <a href="https://indiareads/community/tutorials/initial-server-setup-with-ubuntu-14-04">Initial Server Setup</a> article.</p>

<p>If you want to follow this tutorial, you need a basic user with sudo privileges. We will use the <strong>rails</strong> user in this example. If your user has another name, make sure that you use correct paths in the next steps.</p>

<h2 id="step-2-optional-—-set-up-your-domain">Step 2 (Optional) — Set Up Your Domain</h2>

<p>In order to ensure that your site will be up and visible, you need to set up your DNS records to point your domain name towards your new server. You can find more information on <a href="https://indiareads/community/tutorials/how-to-set-up-a-host-name-with-digitalocean">setting up a hostname</a> by following the link.</p>

<p>However, this step is optional, since you can access your site via an IP address.</p>

<h2 id="step-3-—-install-ruby">Step 3 — Install Ruby</h2>

<p>We will install Ruby manually from source.</p>

<p>Before we do anything else, we should run an update to make sure that all of the packages we want to install are up to date:</p>
<pre class="code-pre "><code langs="">sudo apt-get update
</code></pre>
<p>Next, install some libraries and other dependencies. This will make the installation as smooth as possible:</p>
<pre class="code-pre "><code langs="">sudo apt-get install build-essential libssl-dev libyaml-dev libreadline-dev openssl curl git-core zlib1g-dev bison libxml2-dev libxslt1-dev libcurl4-openssl-dev libsqlite3-dev sqlite3
</code></pre>
<p>Create a temporary folder for the Ruby source files:</p>
<pre class="code-pre "><code langs="">mkdir ~/ruby
</code></pre>
<p>Move to the new folder:</p>
<pre class="code-pre "><code langs="">cd ~/ruby
</code></pre>
<p>Download the latest stable Ruby source code. At the time of this writing, this is version 2.1.4. You can get the current latest version from the <a href="https://www.ruby-lang.org/en/downloads/">Ruby website</a>. If a newer version is available, you will need to replace the link in the following command:</p>
<pre class="code-pre "><code langs="">wget http://cache.ruby-lang.org/pub/ruby/2.1/ruby-2.1.4.tar.gz
</code></pre>
<p>Decompress the downloaded file:</p>
<pre class="code-pre "><code langs="">tar -xzf ruby-2.1.4.tar.gz
</code></pre>
<p>Select the extracted directory:</p>
<pre class="code-pre "><code langs="">cd ruby-2.1.4
</code></pre>
<p>Run the <span class="highlight">configure</span> script. This will take some time as it checks for dependencies and creates a new <strong>Makefile</strong>, which will contain steps that need to be taken to compile the code:</p>
<pre class="code-pre "><code langs="">./configure
</code></pre>
<p>Run the <span class="highlight">make</span> utility, which will use the <strong>Makefile</strong> to build the executable program. This step can take a bit longer:</p>
<pre class="code-pre "><code langs="">make
</code></pre>
<p>Now, run the same command with the <span class="highlight">install</span> parameter. It will try to copy the compiled binaries to the <span class="highlight">/usr/local/bin</span> folder. This step requires root access to write to this directory:</p>
<pre class="code-pre "><code langs="">sudo make install
</code></pre>
<p>Ruby should now be installed on the system. We can check it with the following command, which should print the Ruby version:</p>
<pre class="code-pre "><code langs="">ruby -v
</code></pre>
<p>If your Ruby installation was successful, you should see output like the following:</p>
<pre class="code-pre "><code langs="">ruby 2.1.4p265 (2014-10-27 revision 48166) [x86_64-linux]
</code></pre>
<p>Finally, we can delete the temporary folder:</p>
<pre class="code-pre "><code langs="">rm -rf ~/ruby
</code></pre>
<h2 id="step-4-—-install-apache">Step 4 — Install Apache</h2>

<p>To install Apache, type this command:</p>
<pre class="code-pre "><code langs="">sudo apt-get install apache2
</code></pre>
<p>Yes, that’s all!</p>

<h2 id="step-5-—-install-passenger">Step 5 — Install Passenger</h2>

<p>First, install the PGP key for the repository server:</p>
<pre class="code-pre "><code langs="">sudo apt-key adv --keyserver keyserver.ubuntu.com --recv-keys 561F9B9CAC40B2F7
</code></pre>
<p>Create an APT source file:</p>
<pre class="code-pre "><code langs="">sudo nano /etc/apt/sources.list.d/passenger.list
</code></pre>
<p>Insert the following line to add the Passenger repository to the file:</p>
<pre class="code-pre "><code langs="">deb https://oss-binaries.phusionpassenger.com/apt/passenger trusty main
</code></pre>
<p>Press <strong>CTRL+X</strong> to exit, type <strong>Y</strong> to save the file, and then press ENTER to confirm the file location.</p>

<p>Change the owner and permissions for this file to restrict access to <strong>root</strong>:</p>
<pre class="code-pre "><code langs="">sudo chown root: /etc/apt/sources.list.d/passenger.list
sudo chmod 600 /etc/apt/sources.list.d/passenger.list
</code></pre>
<p>Update the APT cache:</p>
<pre class="code-pre "><code langs="">sudo apt-get update
</code></pre>
<p>Finally, install Passenger:</p>
<pre class="code-pre "><code langs="">sudo apt-get install libapache2-mod-passenger
</code></pre>
<p>Make sure the Passenger Apache module; it maybe enabled already:</p>
<pre class="code-pre "><code langs="">sudo a2enmod passenger
</code></pre>
<p>Restart Apache:</p>
<pre class="code-pre "><code langs="">sudo service apache2 restart
</code></pre>
<p>This step will overwrite our Ruby version to an older one. To resolve this, simply remove the incorrect Ruby location and create a new symlink to the correct Ruby binary file:</p>
<pre class="code-pre "><code langs="">sudo rm /usr/bin/ruby
sudo ln -s /usr/local/bin/ruby /usr/bin/ruby
</code></pre>
<h2 id="step-6-—-deploy">Step 6 — Deploy</h2>

<p>At this point you can deploy your own Rails application if you have one ready. If you want to deploy an existing app, you can upload your project to the server and skip to the <code>/etc/apache2/sites-available/default</code> step.</p>

<p>For this tutorial, we will create a new Rails app directly on the Droplet. We will need the <span class="highlight">rails</span> gem to create the new app.</p>

<p>Move to your user's home directory:</p>
<pre class="code-pre "><code langs="">cd ~
</code></pre>
<p>Install the <span class="highlight">rails</span> gem without extra documentation, which makes the installation faster. This will still take a few minutes:</p>
<pre class="code-pre "><code langs="">sudo gem install --no-rdoc --no-ri rails
</code></pre>
<p>Now we can create a new app. In our example, we will use the name <span class="highlight">testapp</span>. If you want to use another name, make sure you update the paths in the other commands and files in this section.</p>

<p>We will skip the Bundler installation because we want to run it manually later.</p>
<pre class="code-pre "><code langs="">rails new testapp --skip-bundle
</code></pre>
<p>Enter the directory:</p>
<pre class="code-pre "><code langs="">cd testapp
</code></pre>
<p>Now we need to install a JavaScript execution environment. It can be installed as the <span class="highlight">therubyracer</span> gem. To install it, first open the <strong>Gemfile</strong>:</p>
<pre class="code-pre "><code langs="">nano Gemfile
</code></pre>
<p>Find the following line:</p>
<pre class="code-pre "><code langs=""># gem 'therubyracer',  platforms: :ruby
</code></pre>
<p>Uncomment it:</p>
<pre class="code-pre "><code langs="">gem 'therubyracer',  platforms: :ruby
</code></pre>
<p>Save the file, and run Bundler:</p>
<pre class="code-pre "><code langs="">bundle install
</code></pre>
<p>Now, we need to create a virtual host file for our project. We'll do this by copying the default Apache virtual host:</p>
<pre class="code-pre "><code langs="">sudo cp /etc/apache2/sites-available/000-default.conf /etc/apache2/sites-available/testapp.conf
</code></pre>
<p>Open the config file:</p>
<pre class="code-pre "><code langs="">sudo nano /etc/apache2/sites-available/testapp.conf
</code></pre>
<p>Edit it or replace the existing contents so your final result matches the file shown below. Changes you need to make are highlighted in <span class="highlight">red</span>. Remember to use your own domain name, and the correct path to your Rails app:</p>
<pre class="code-pre "><code langs=""><VirtualHost *:80>
    <span class="highlight">ServerName example.com</span>
    <span class="highlight">ServerAlias www.example.com</span>
    ServerAdmin webmaster@localhost
    DocumentRoot <span class="highlight">/home/rails/testapp/public</span>
    <span class="highlight">RailsEnv development</span>
    ErrorLog ${APACHE_LOG_DIR}/error.log
    CustomLog ${APACHE_LOG_DIR}/access.log combined
    <span class="highlight"><Directory "/home/rails/testapp/public"></span>
        <span class="highlight">Options FollowSymLinks</span>
        <span class="highlight">Require all granted</span>
    <span class="highlight"></Directory></span>
</VirtualHost>
</code></pre>
<p>Basically, this file enables listening to our domain name on port 80, sets an alias for the <strong>www</strong> subdomain, sets the mail address of our server administrator, sets the root directory for the public directory of our new project, and allows access to our site. You can learn more about <a href="https://indiareads/community/tutorials/how-to-set-up-apache-virtual-hosts-on-ubuntu-14-04-lts">Apache virtual hosts</a> by following the link.</p>

<p>To test our setup, we want to see the Rails <strong>Welcome aboard</strong> page. However, this works only if the application is started in the development environment. Passenger starts the application in the production environment by default, so we need to change this with the <strong>RailsEnv</strong> option. If your app is ready for production you'll want to leave this setting out.</p>

<p>If you don't want to assign your domain to this app, you can skip the <code>ServerName</code> and <code>ServerAlias</code> lines, or use your IP address.</p>

<p>Save the file (CTRL+X, Y, ENTER).</p>

<p>Disable the default site, enable your new site, and restart Apache:</p>
<pre class="code-pre "><code langs="">sudo a2dissite 000-default
sudo a2ensite testapp
sudo service apache2 restart
</code></pre>
<p>Now your app's website should be accessible. Navigate to your Droplet's domain or IP address:</p>
<pre class="code-pre "><code langs="">http://droplet_ip_address
</code></pre>
<p>Verify that your app is deployed. You should see either your custom application, or the <strong>Welcome aboard</strong> default Rails page:</p>

<p><img src="https://assets.digitalocean.com/articles/rails_passenger/1.png" alt="Test page" /></p>

<p>The Rails app is now live on your server.</p>

<h2 id="step-7-—-update-regularly">Step 7 — Update Regularly</h2>

<p>To update Ruby, you will need to compile the latest version as shown in Step 4 in this tutorial.</p>

<p>To update Passenger and Apache, you will need to run a basic system update:</p>
<pre class="code-pre "><code langs="">sudo apt-get update && sudo apt-get upgrade
</code></pre>
<p>However, if there is a new system Ruby version available, it will probably overwrite our Ruby (installed from source). For this reason, you might need to re-run the commands for removing the existing symlink to the Ruby binary file and creating a new (correct) one. They are listed at the end of Step 6 in this tutorial.</p>

<p>After the update process, you will need to restart the web server:</p>
<pre class="code-pre "><code langs="">sudo service apache2 restart
</code></pre>
    