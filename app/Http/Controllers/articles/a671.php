<link rel='stylesheet' href='digital_ocean.css'><br> <br> 
      <h3 id="introduction">Introduction</h3>

<hr />

<p>Modern web-application development heavily relies on frameworks. These sets of readily-prepared libraries make the actual programming a lot easier than it would be, unlike in ye older days. They provide tools varying from authentication to encryption, cookie and session handling to file uploads.</p>

<p>Despite the popularity of the PHP programming language and its many excellent frameworks, there are still time consuming challenges which take the developers away from the fun bits of creating the web-site (or API) they dream of.</p>

<p>In this IndiaReads article, following on our Capistrano Automation Tool Series, we will see how to introduce another little framework (or tool), this time to help you with pushing your code to your servers without dealing with SFTP file managers -- <em>automatically!</em></p>

<h2 id="glossary">Glossary</h2>

<hr />

<h3 id="1-capistrano-in-brief">1. Capistrano In Brief</h3>

<hr />

<h3 id="2-getting-the-ruby-interpreter-and-capistrano">2. Getting The Ruby Interpreter And Capistrano</h3>

<hr />

<ol>
<li>The Ruby Interpreter</li>
<li>Capistrano</li>
</ol>

<h3 id="3-preparing-the-deployment-server">3. Preparing The Deployment Server</h3>

<hr />

<ol>
<li>Creating The Deployment User And Group</li>
<li>Creating The Application Deployment Directory</li>
<li>Setting Up PHP And Nginx</li>
</ol>

<h3 id="4-preparing-php-applications-for-automated-deployments">4. Preparing PHP Applications For Automated Deployments</h3>

<hr />

<ol>
<li>Initiating Git</li>
<li>Initiating Capistrano</li>
<li>Configuring Capistrano Deployment</li>
<li>Configuring Production With Capistrano</li>
<li>Deploying To The Production Server</li>
</ol>

<h3 id="5-troubleshooting">5. Troubleshooting</h3>

<hr />

<p><strong>Note:</strong> Although we will see how to download and set up the necessary dependencies for Capistrano (e.g. Ruby <code>2.1.0</code>) to automate the deployment process, this article assumes that you already have your <em>deployment</em> droplet ready with a functioning web-site installation, online on an Ubuntu 13 cloud server.</p>

<h2 id="capistrano-in-brief">Capistrano In Brief</h2>

<hr />

<p>Capistrano is a Ruby programming language based, open-source server (or deployment) management tool. Using Capistrano, arbitrary functions and procedures can be performed on virtual servers without direct interference by having Capistrano execute a script (i.e. a recipe) with all the instructions listed. In a general sense, this tool can be considered a developer's very own deployment assistant, helping with almost anything from getting the code on the remote machine to bootstrapping the entire getting-online process.</p>

<p>Originally written to help with Rails framework deployments, with its latest version, Capistrano 3 can now be used with (and for) almost anything, including PHP. </p>

<p><strong>Note:</strong> If you would like to learn more about Capistrano and Ruby, check out our article on the subject: <a href="https://indiareads/community/articles/how-to-use-capistrano-to-automate-deployments-getting-started">How To Use Capistrano to Automate Deployments: Getting Started</a>.</p>

<h2 id="getting-the-ruby-interpreter-and-capistrano">Getting The Ruby Interpreter And Capistrano</h2>

<hr />

<h3 id="the-ruby-interpreter">The Ruby Interpreter</h3>

<hr />

<p>On your PHP development machine, you need to have the latest available Ruby interpreter in order to run Capistrano. The instructions below, explaining how to get Ruby on an Ubuntu VPS, is actually a quick summary of our detailed tutorial: <a href="http://link_to_8.3_ruby_ubuntu_sinatra">Preparing An Ubuntu 13 Server To Run Ruby 2.1.0</a>.</p>
<pre class="code-pre "><code langs=""># Update the software sources list
# And upgrade the dated applications:

aptitude    update
aptitude -y upgrade

# Download and install the build-essential package:
aptitude install -y build-essential

# And some additional, commonly used tools:
aptitude install -y cvs subversion git-core libyaml-dev mercurial

# Get the Ruby Version Manager
curl -L get.rvm.io | bash -s stable

# And to create a system environment with RVM:
source /etc/profile.d/rvm.sh

# Download and install Ruby using RVM:
rvm reload
rvm install 2.1.0
</code></pre>
<h3 id="capistrano">Capistrano</h3>

<hr />

<p>Once Ruby is installed, Capistrano can be set up using the default Ruby package manager RubyGems.</p>

<p>Run the following command to download and install Capistrano 3 using <code>gem</code>:</p>
<pre class="code-pre "><code langs="">gem install capistrano --no-ri --no-rdoc
</code></pre>
<h2 id="preparing-the-deployment-server">Preparing The Deployment Server</h2>

<hr />

<p>As a mature automation tool, Capistrano is built with stability and security in mind. In order to use it to deploy your PHP web applications, we first need to perform some work on the deployment server, e.g. create a user-group for Capistrano to use to connect to it.</p>

<h3 id="creating-the-deployment-user-and-group">Creating The Deployment User And Group</h3>

<hr />

<p>Add a new user group:</p>
<pre class="code-pre "><code langs=""># Usage: sudo addgroup [group name]
sudo addgroup www
</code></pre>
<p>Create a new user and add it to this group:</p>
<pre class="code-pre "><code langs=""># Create a new user:
# Usage: sudo adducer [user name]
sudo adduser deployer

# Follow on-screen instructions to user-related
# information such as the desired password.

# Add the user to an already existing group:
# Usage: sudo adducer [user name] [group name]
sudo adduser deployer www
</code></pre>
<p>Edit <code>/etc/sudoers</code> using the text editor <code>nano</code> to let the user <code>deployer</code> <code>sudo</code> for future deployments:</p>
<pre class="code-pre "><code langs="">nano /etc/sudoers
</code></pre>
<p>Scroll down the file and find where <code>root</code> is defined:</p>
<pre class="code-pre "><code langs="">..

# User privilege specification
root    ALL=(ALL:ALL) ALL

..
</code></pre>
<p>Append the following right after <code>root ALL=(ALL) ALL</code>:</p>
<pre class="code-pre "><code langs="">deployer ALL=(ALL:ALL) ALL
</code></pre>
<p>This section of the <code>/etc/sudoers</code> file should now look like this:</p>
<pre class="code-pre "><code langs="">..

# User privilege specification
root     ALL=(ALL:ALL) ALL
deployer ALL=(ALL:ALL) ALL

..
</code></pre>
<p>Press CTRL+X and confirm with Y to save and exit.</p>

<p><strong>Note:</strong> To learn more about SSH and sudo, check out IndiaReads community articles on <a href="https://indiareads/community/community_tags/linux-basics">Linux Basics</a>.</p>

<h3 id="creating-the-application-deployment-directory">Creating The Application Deployment Directory</h3>

<hr />

<p>On the deployment server, we also need to define and create the directory where the PHP codebase will be located for the web-server to run the application.</p>

<p>Create the <code>www</code> web-application directory inside <code>/var</code>:</p>
<pre class="code-pre "><code langs="">sudo mkdir /var/www
</code></pre>
<p>And set the permissions to make it access for the web-server (i.e. Nginx):</p>
<pre class="code-pre "><code langs=""># Set the ownership of the folder to members of `www` group
sudo chown -R :www  /var/www

# Set folder permissions recursively
sudo chmod -R g+rwX /var/www

# Ensure permissions will affect future sub-directories etc.
sudo chmod g+s      /var/www
</code></pre>
<h3 id="setting-up-php-and-nginx">Setting Up PHP And Nginx</h3>

<hr />

<p>Capistrano's duty is to automate deployments. We still need to set up PHP and NGinx - or any other web-server & interpreter combination - to get our web-application working.</p>

<p>In order to fully prepare the deployment server to run PHP web-applications, check out the following articles:</p>

<ul>
<li><strong>Nginx, PHP and MySQL:</strong><br /></li>
</ul>

<p><a href="https://indiareads/community/articles/how-to-install-linux-nginx-mysql-php-lemp-stack-on-ubuntu-12-04">How To Install Linux, nginx, MySQL, PHP (LEMP) stack</a></p>

<ul>
<li><strong>phpMyAdmin:</strong><br /></li>
</ul>

<p><a href="https://indiareads/community/articles/how-to-install-phpmyadmin-on-a-lemp-server">How To Install phpMyAdmin on a LEMP server</a></p>

<h2 id="preparing-php-applications-for-automated-deployments">Preparing PHP Applications For Automated Deployments</h2>

<hr />

<p>Once we are done installing Ruby and Capistrano on our development server, and adding a deployment user on the deployment machine, we can see how to "initiate" Capistrano to get started with the tool.</p>

<p><strong>Note:</strong> In this section, we assume that your web-application source code is located at <code>/home/developer1/my_app</code> directory. The following commands need to be executed <em>from within</em>.</p>
<pre class="code-pre "><code langs=""># cd /path/to/your/app/on/dev/server
cd /home/developer1/my_app
</code></pre>
<h3 id="initiating-git">Initiating Git</h3>

<hr />

<p>Git is a source-code management system and revisiting tool commonly used by developers. Capistrano controls and manages your application lifecycle and deployment process through Git repositories.</p>

<p>In this section, we will create a centrally-accessible Git repository, initiate Git and upload your project there for Capistrano to use during deployments.</p>

<p><strong>Note:</strong> In order to follow this section, you will need a <a href="https://www.github.com">Github</a> account and an empty repository created.</p>

<p>Execute the following, self-explanatory commands inside the directory where your application's source code is located (e.g. <code>my_app</code>) to initiate a repository:</p>
<pre class="code-pre "><code langs=""># !! These commands are to be executed on
#    your development machine, from where you will
#    deploy to your server.
#    Instructions might vary slightly depending on
#    your choice of operating system.

# Initiate the repository
git init

# Add all the files to the repository
git add .

# Commit the changes
git commit -m "first commit"

# Add your Github repository link 
# Example: git remote add origin git@github.com:[user name]/[proj. name].git
git remote add origin git@github.com:user123/my_app.git

# Create an RSA/SSH key
# Follow the on-screen instructions
ssh-keygen -t rsa

# View the contents of the key and add it to your Github
# by copy-and-pasting from the current remote session by
# visiting: https://github.com/settings/ssh
# To learn more about the process,
# visit: https://help.github.com/articles/generating-ssh-keys
cat /root/.ssh/id_rsa.pub

# Set your Github information
# Username:
# Usage: git config --global user.name "[your username]"
# Email:
# Usage: git config --global user.email "[your email]"
git config --global user.name  "user123"    
git config --global user.email "user123@domain.tld"

# Push the project's source code to your Github account
git push -u origin master
</code></pre>
<p><strong>Note:</strong> To learn more about working with Git, check out the <a href="https://indiareads/community/articles/how-to-use-git-effectively">How To Use Git Effectively</a> tutorial at IndiaReads community pages.</p>

<h3 id="initiating-capistrano">Initiating Capistrano</h3>

<hr />

<p>In this step, we will get Capistrano to automatically scaffold its configuration and deployment files inside the project directory.</p>

<p>Run the following to initiate (i.e. <em>install</em>) Capistrano files:</p>
<pre class="code-pre "><code langs="">cap install

# mkdir -p config/deploy
# create config/deploy.rb
# create config/deploy/staging.rb
# create config/deploy/production.rb
# mkdir -p lib/capistrano/tasks
# Capified
</code></pre>
<h3 id="configuring-capistrano-deployment">Configuring Capistrano Deployment</h3>

<hr />

<p>The file <code>config/deploy.rb</code> contains arguments and settings relevant to the deployment server(s). Here, we will tell Capistrano to which server(s) we would like to connect and deploy.</p>

<p>Run the following to edit the file using <code>nano</code> text editor:</p>
<pre class="code-pre "><code langs="">nano config/deploy.rb
</code></pre>
<p>Add the below block of code, modifying it to suit your own settings:</p>
<pre class="code-pre "><code langs=""># !! When editing the file (or defining the configurations),
#    you can either comment them out or add the new lines.
#    Make sure to **not** to have some example settings
#    overriding the ones you are appending.

# Define the name of the application
set :application, 'my_app'

# Define where can Capistrano access the source repository
# set :repo_url, 'https://github.com/[user name]/[application name].git'
set :scm, :git
set :repo_url, 'https://github.com/user123/my_app.git'

# Define where to put your application code
set :deploy_to, "/var/www/my_app"

set :pty, true

set :format, :pretty

# Set your post-deployment settings.
# For example, you can restart your Nginx process
# similar to the below example.
# To learn more about how to work with Capistrano tasks
# check out the official Capistrano documentation at:
# http://capistranorb.com/

# namespace :deploy do
#   desc 'Restart application'
#   task :restart do
#     on roles(:app), in: :sequence, wait: 5 do
#       # Your restart mechanism here, for example:
#       sudo "service nginx restart"
#     end
#   end
# end
</code></pre>
<p>Press CTRL+X and confirm with Y to save and exit.</p>

<h3 id="configuring-production-with-capistrano">Configuring Production With Capistrano</h3>

<hr />

<p><strong>Note:</strong> Similar to <code>config/deploy.rb</code>, you will need to make some amendments to the <code>config/production.rb</code> file. You are better modifying the code instead of appending the below block.</p>

<p>Run the following to edit the file using nano text editor:</p>
<pre class="code-pre "><code langs="">nano config/deploy/production.rb
</code></pre>
<p>Enter your server's settings, similar to below:</p>
<pre class="code-pre "><code langs=""># Define roles, user and IP address of deployment server
# role :name, %{[user]@[IP adde.]}
role :app, %w{deployer@162.243.74.190}

# Define server(s)
# Example:
# server '[your droplet's IP addr]', user: '[the deployer user]', roles: %w{[role names as defined above]}
# server '162.243.74.190', user: 'deployer', roles: %w{app}
server '162.243.74.190', user: 'deployer', roles: %w{app}

# SSH Options
# See the example commented out section in the file
# for more options.
set :ssh_options, {
    forward_agent: false,
    auth_methods: %w(password),
    password: 'user_deployers_password',
    user: 'deployer',
}
</code></pre>
<p>Press CTRL+X and confirm with Y to save and exit.</p>

<h3 id="deploying-to-the-production-server">Deploying To The Production Server</h3>

<hr />

<p>Once we are done with the settings, it is time to deploy.</p>

<p>Run the following code on your development machine to deploy to the production server. As defined in the above files, Capistrano will: </p>

<ul>
<li><p>Connect to the deployment server</p></li>
<li><p>Download the application source</p></li>
<li><p>Perform the deployment actions </p></li>
</ul>

<p>Once all settings are done, you can run the following command to get Capistrano deploy your application source from your development server to deployment machine:</p>
<pre class="code-pre "><code langs="">cap production deploy
</code></pre>
<p>And that's it! Now, you can watch Capistrano take your code online and keep track of your most recent code base.</p>

<h2 id="troubleshooting">Troubleshooting</h2>

<hr />

<p>Working with Capistrano is not always as straightforward as it might seem. Unfortunately, the tool likes to complain instead of guiding and the documentation, at its current stage, is a little bit limited.</p>

<p>For everything to work smoothly, try to:</p>

<ul>
<li><p>Match the directory and the repository names.</p></li>
<li><p>Type everything correctly.</p></li>
<li><p>Make sure that your development and deployment servers contain all necessary tools (i.e. sqlite3, libraries etc.).</p></li>
<li><p>Make sure to test all operations and executions manually before getting Capistrano perform them.</p></li>
<li><p>Consider implementing a more secure authentication method following the official Capistrano docs.</p></li>
</ul>

<p>To learn more about Capistrano and what it can do, consider reading the [Capistrano documentation](capistranorb.com/documentation).</p>

<div class="author">Submitted by: <a href="https://twitter.com/ostezer">O.S. Tezer</a></div> 

    