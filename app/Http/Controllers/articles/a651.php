<link rel='stylesheet' href='digital_ocean.css'><br> <br> 
      <h3 id="introduction">Introduction</h3>

<hr />

<p>If you are not already fed up with repeating the same mundane tasks to update your application servers to get your project online, you probably will be eventually The joy you feel whilst developing your project tends to take a usual hit when it comes to the boring bits of system administration (e.g. uploading your codebase, amending configurations, executing commands over and over again, etc.)</p>

<p>But do not fear! Capistrano, the task-automation-tool, is here to help.</p>

<p>In this IndiaReads article, we are going create a rock-solid server setup, running the latest version of CentOS to host Ruby-on-Rails applications using Nginx and Passenger. We will continue with learning how to automate the process of deployments - and updates - using the Ruby based automation tool Capistrano.</p>

<p><strong>Note:</strong> This article builds on the knowledge from our past Capistrano article: <a href="https://indiareads/community/articles/how-to-use-capistrano-to-automate-deployments-getting-started">Automating Deployments With Capistrano: Getting Started</a>. In order to gain a good knowledge of the tool, which is highly recommended if you are going to use it, you are advised to read it before continuing with this piece. Likewise, if you would like to learn more about preparing a fresh droplet for Rails based application deployments with Passenger (and Nginx), check out the <a href="https://indiareads/community/articles/how-to-deploy-rails-apps-using-passenger-with-nginx-on-centos-6-5">How To Deploy Rails Apps Using Passenger With Nginx</a> article.</p>

<p><strong>Note:</strong> Capistrano relies on Git for deployments. To learn more consider reading IndiaReads community articles on the subject by clicking <a href="https://indiareads/community/community_tags/git">here</a>.</p>

<h2 id="glossary">Glossary</h2>

<hr />

<h3 id="1-preparing-the-deployment-server">1. Preparing The Deployment Server</h3>

<hr />

<ol>
<li>Updating And Preparing The Operating System</li>
<li>Setting Up Ruby Environment and Rails</li>
<li>Downloading And Installing App. & HTTP Servers</li>
<li>Creating The Nginx Management Script</li>
<li>Configuring Nginx For Application Deployment</li>
<li>Downloading And Installing Capistrano</li>
<li>Creating A System User For Deployment</li>
</ol>

<h3 id="2-preparing-rails-applications-for-git-based-capistrano-deployment">2. Preparing Rails Applications For Git-Based Capistrano Deployment</h3>

<hr />

<ol>
<li>Creating A Basic Ruby-On-Rails Application</li>
<li>Creating A Git Repository</li>
</ol>

<h3 id="3-working-with-capistrano-to-automate-deployments">3. Working With Capistrano To Automate Deployments</h3>

<hr />

<ol>
<li>Installing Capistrano Inside The Project Directory</li>
<li>Working With <code>config/deploy.rb</code> Inside The Project Directory</li>
<li>Working With <code>config/deploy/production.rb</code> Inside The Project Directory</li>
<li>Deploying To The Production Server</li>
</ol>

<h2 id="preparing-the-deployment-server">Preparing The Deployment Server</h2>

<hr />

<p><strong>Note:</strong> To have a better understanding of the below section, which can be considered a lengthy summary, check out the full article on the subject: <a href="https://indiareads/community/articles/how-to-deploy-rails-apps-using-passenger-with-nginx-on-centos-6-5">How To Deploy Rails Apps Using Passenger With Nginx</a>.</p>

<h3 id="updating-and-preparing-the-operating-system">Updating And Preparing The Operating System</h3>

<hr />

<p>Run the following command to update the default tools of your CentOS based droplet:</p>
<pre class="code-pre "><code langs="">yum -y update
</code></pre>
<p>Install the bundle containing development tools by executing the following command:</p>
<pre class="code-pre "><code langs="">yum groupinstall -y 'development tools'
</code></pre>
<p>Some of the packages we need for this tutorial (e.g. libyaml-devel, nginx etc.) are <em>not</em> found within the official CentOS repository.</p>

<p>Run the following to add the EPEL repository:</p>
<pre class="code-pre "><code langs="">sudo su -c 'rpm -Uvh http://dl.fedoraproject.org/pub/epel/6/x86_64/epel-release-6-8.noarch.rpm'

yum -y update
</code></pre>
<p>Finally, in order to install some additional libraries and tools, run the following command:</p>
<pre class="code-pre "><code langs="">yum install -y curl-devel nano sqlite-devel libyaml-devel
</code></pre>
<h3 id="setting-up-ruby-environment-and-rails">Setting Up Ruby Environment and Rails</h3>

<hr />

<p><strong>Note:</strong> This section is a summary of our dedicated article <a href="https://indiareads/community/articles/how-to-install-ruby-2-1-0-on-centos-6-5-using-rvm">How To Install Ruby 2.1.0 On CentOS 6.5</a>.</p>

<p>Run the following two commands to install RVM and create a system environment for Ruby:</p>
<pre class="code-pre "><code langs="">curl -L get.rvm.io | bash -s stable

source /etc/profile.d/rvm.sh
rvm reload
rvm install 2.1.0
</code></pre>
<p>Since Rails needs a JavaScript interpreter, we will also need to set up Node.js.</p>

<p>Run the following to download and install nodejs using <code>yum</code>:</p>
<pre class="code-pre "><code langs="">yum install -y nodejs
</code></pre>
<p>Execute the following command using RubyGems' <code>gem</code> to download and install <code>rails</code>:</p>
<pre class="code-pre "><code langs="">gem install bundler rails
</code></pre>
<h3 id="downloading-and-installing-app-amp-http-servers">Downloading And Installing App. & HTTP Servers</h3>

<hr />

<p><strong>Note:</strong> If your VPS has less than 1 GB of RAM, you will need to perform the below simple procedure to prepare a SWAP disk space to be used as a temporary data holder (RAM substitute). Since IndiaReads servers come with fast SSD disks, this does not really constitute an issue whilst performing the server application installation tasks.</p>
<pre class="code-pre "><code langs=""># Create a 1024 MB SWAP space
sudo dd if=/dev/zero of=/swap bs=1M count=1024
sudo mkswap /swap
sudo swapon /swap
</code></pre>
<h3 id="phusion-passenger">Phusion Passenger</h3>

<hr />

<p>Red Hat Linux's default package manager RPM (RPM Package Manager) ships applications contained within <code>.rpm</code> files. Unfortunately, in Passenger's case, they are quite outdated. Therefore, we will be using RubyGem, once again, to download and install the latest available version of Passenger -- <strong>version 4</strong>.</p>

<p>Use the below command to simply download and install passenger:</p>
<pre class="code-pre "><code langs="">gem install passenger
</code></pre>
<h3 id="nginx">Nginx</h3>

<hr />

<p><strong>Note:</strong> Normally, to download and install Nginx, you could add the EPEL repository (as we have already done) and get Nginx via <code>yum</code>. However, to get Nginx work with Passenger, its source must be compiled with the necessary modules.</p>

<p>Run the following to start compiling Nginx with native Passenger module:</p>
<pre class="code-pre "><code langs="">passenger-install-nginx-module
</code></pre>
<p>Once you run the command, press Enter and confirm your choice of language(s) (i.e. Ruby, in our case). You can use arrow keys and the space bar to select Ruby alone, if you wish.</p>
<pre class="code-pre "><code langs="">Use <space> to select.
If the menu doesn't display correctly, ensure that your terminal supports UTF-8.

 ‣ ⬢  Ruby
   ⬢  Python
   ⬢  Node.js
   ⬡  Meteor
</code></pre>
<p>In the next step, choose <code>Item 1</code>:</p>
<pre class="code-pre "><code langs="">1. Yes: download, compile and install Nginx for me. (recommended)
    The easiest way to get started. A stock Nginx 1.4.4 with Passenger
    support, but with no other additional third party modules, will be
    installed for you to a directory of your choice.
</code></pre>
<p>And press Enter to continue.</p>

<p>Now, Nginx source will be downloaded, compiled, and installed with Passenger support.</p>

<p><strong>Note:</strong> This action might take a little while -- probably longer than one would like or expect!</p>

<h3 id="creating-the-nginx-management-script">Creating The Nginx Management Script</h3>

<hr />

<p>After compiling Nginx, in order to control it with ease, we need to create a simple management script.</p>

<p>Run the following commands to create the script:</p>
<pre class="code-pre "><code langs="">nano /etc/rc.d/init.d/nginx
</code></pre>
<p>Copy and paste the below contents:</p>
<pre class="code-pre "><code langs="">#!/bin/sh
. /etc/rc.d/init.d/functions
. /etc/sysconfig/network
[ "$NETWORKING" = "no" ] && exit 0

nginx="/opt/nginx/sbin/nginx"
prog=$(basename $nginx)

NGINX_CONF_FILE="/opt/nginx/conf/nginx.conf"

lockfile=/var/lock/subsys/nginx

start() {
    [ -x $nginx ] || exit 5
    [ -f $NGINX_CONF_FILE ] || exit 6
    echo -n $"Starting $prog: "
    daemon $nginx -c $NGINX_CONF_FILE
    retval=$?
    echo
    [ $retval -eq 0 ] && touch $lockfile
    return $retval
}

stop() {
    echo -n $"Stopping $prog: "
    killproc $prog -QUIT
    retval=$?
    echo
    [ $retval -eq 0 ] && rm -f $lockfile
    return $retval
}

restart() {
    configtest || return $?
    stop
    start
}

reload() {
    configtest || return $?
    echo -n $”Reloading $prog: ”
    killproc $nginx -HUP
    RETVAL=$?
    echo
}

force_reload() {
    restart
}

configtest() {
    $nginx -t -c $NGINX_CONF_FILE
}

rh_status() {
    status $prog
}

rh_status_q() {
    rh_status >/dev/null 2>&1
}

case "$1" in
start)
rh_status_q && exit 0
$1
;;
stop)
rh_status_q || exit 0
$1
;;
restart|configtest)
$1
;;
reload)
rh_status_q || exit 7
$1
;;
force-reload)
force_reload
;;
status)
rh_status
;;
condrestart|try-restart)
rh_status_q || exit 0
;;
*)
echo $"Usage: $0 {start|stop|status|restart|condrestart|try-restart|reload|force-reload|configtest}"
exit 2
esac
</code></pre>
<p>Press CTRL+X and confirm with Y to save and exit.</p>

<p>Set the mode of this management script as executable:</p>
<pre class="code-pre "><code langs="">chmod +x /etc/rc.d/init.d/nginx
</code></pre>
<h3 id="configuring-nginx-for-application-deployment">Configuring Nginx For Application Deployment</h3>

<hr />

<p>In this final step of configuring our servers, we need to create an Nginx server block, which roughly translates to Apache's virtual hosts.</p>

<p>As you might remember seeing during Passenger's Nginx installation, this procedure consists of adding a block of code to Nginx's configuration file <code>nginx.conf</code>. By default, unless you states otherwise, this file can be found under <code>/opt/nginx/conf/nginx.conf</code>.</p>

<p>Type the following command to open up this configuration file to edit it with the text editor nano:</p>
<pre class="code-pre "><code langs="">nano /opt/nginx/conf/nginx.conf
</code></pre>
<p>As the first step, find the <code>http {</code> node and append the following right after the <code>passenger_root</code> and <code>passenger_ruby</code> directives:</p>
<pre class="code-pre "><code langs=""># Only for development purposes.
# Remove this line when you upload an actual application.
# For * TESTING * purposes only.
passenger_app_env development;    
</code></pre>
<p>Scroll down the file and find <code>server { ..</code>. Comment out the default location, i.e.:</p>
<pre class="code-pre "><code langs="">..

#    location / {
#            root   html;
#            index  index.html index.htm;
#        }

..
</code></pre>
<p>And define your default application root:</p>
<pre class="code-pre "><code langs=""># Set the folder where you will be deploying your application.
# We are using: /home/deployer/apps/my_app
root              /home/deployer/apps/my_app/public;
passenger_enabled on;
</code></pre>
<p>Press CTRL+X and confirm with Y to save and exit.</p>

<p>Run the following to reload the Nginx with the new application configuration:</p>
<pre class="code-pre "><code langs=""># !! Remember to create an Nginx management script
#    by following the main Rails deployment article for CentOS
#    linked at the beginning of this section.

/etc/init.d/nginx restart
</code></pre>
<p>To check the status of Nginx, you can use:</p>
<pre class="code-pre "><code langs="">/etc/init.d/nginx status
</code></pre>
<p><strong>Note:</strong> To learn more about Nginx, please refer to <a href="https://indiareads/community/articles/how-to-configure-the-nginx-web-server-on-a-virtual-private-server">How to Configure Nginx Web Server on a VPS</a>.</p>

<h3 id="downloading-and-installing-capistrano">Downloading And Installing Capistrano</h3>

<hr />

<p>Once we have our system ready, getting Capistrano's latest version, thanks to RubyGems is a breeze.</p>

<p>You can simply use the following to get Capistrano version 3:</p>
<pre class="code-pre "><code langs="">gem install capistrano
</code></pre>
<h3 id="creating-a-system-user-for-deployment">Creating A System User For Deployment</h3>

<hr />

<p>In this step, we are going to create a CentOS system user to perform the actions of deployment. This is going to be the user for Capistrano to use.</p>

<p><strong>Note:</strong> To keep things basic, we are going to create a <code>deployer</code> user with necessary privileges. For a more complete set up, consider using the <em>groups</em> example from the Capistrano introduction tutorial.</p>

<p>Create a new system user <code>deployer</code>:</p>
<pre class="code-pre "><code langs="">adduser deployer
</code></pre>
<p>Set up <code>deployer</code>'s password:</p>
<pre class="code-pre "><code langs="">passwd deployer

# Enter a password
# Confirm the password
</code></pre>
<p>Edit <code>/etc/sudoers</code> using the text editor <code>nano</code>:</p>
<pre class="code-pre "><code langs="">nano /etc/sudoers
</code></pre>
<p>Scroll down the file and find where <code>root</code> is defined:</p>
<pre class="code-pre "><code langs="">..

## The COMMANDS section may have other options added to it.
##
## Allow root to run any commands anywhere
root    ALL=(ALL)   ALL

..
</code></pre>
<p>Append the following right after <code>root ALL=(ALL) ALL</code>:</p>
<pre class="code-pre "><code langs="">deployer ALL=(ALL) ALL
</code></pre>
<p>This section of the <code>/etc/sudoers</code> file should now look like this:</p>
<pre class="code-pre "><code langs="">..

## The COMMANDS section may have other options added to it.
##
## Allow root to run any commands anywhere
root     ALL=(ALL)  ALL
deployer ALL=(ALL) ALL

..
</code></pre>
<p>Press CTRL+X and confirm with Y to save and exit.</p>

<h2 id="preparing-rails-applications-for-git-based-capistrano-deployment">Preparing Rails Applications For Git-Based Capistrano Deployment</h2>

<hr />

<p>Once we have our system ready, with all the necessary applications set up and working correctly, we can move on to creating an exemplary Rails application to use as a sample.</p>

<p>In the second stage, we are going to create a Git repository and push the code base to a central, accessible location at Github for Capistrano to use for deployments.</p>

<p><strong>Note:</strong> Here, we are creating a sample application. For the actual deployments, you should perform these actions on your own, after making sure that everything is backed up -- <em>just in case!</em> Also, please note that you will need to run Capistrano from a different location than the server where the application needs to be deployed.</p>

<h3 id="creating-a-basic-ruby-on-rails-application">Creating A Basic Ruby-On-Rails Application</h3>

<hr />

<p><strong>Note:</strong> The below step is there to create a substitute Rails application to try out Capistrano.</p>

<p>Having Ruby and Rails already installed leaves us with just a single command to get started.</p>

<p>Execute the following command to get Rails create a new application called <em>my_app</em>:</p>
<pre class="code-pre "><code langs=""># Create a sample Rails application
rails new my_app

# Enter the application directory
cd my_app

# Create a sample resource
rails generate scaffold Task title:string note:text

# Create a sample database
RAILS_ENV=development rake db:migrate
</code></pre>
<p>To test that your application is set correctly and everything is working fine, enter the app directory and run a simple server via <code>rails s</code>:</p>
<pre class="code-pre "><code langs=""># Enter the application directory
cd my_app

# Run a simple server
rails s

# You should now be able to access it by
# visiting: http://[your droplet's IP]:3000

# In order to terminate the server process,
# Press CTRL+C
</code></pre>
<h3 id="creating-a-git-repository">Creating A Git Repository</h3>

<hr />

<p><strong>Note:</strong> To learn more about working with Git, check out the <a href="https://indiareads/community/articles/how-to-use-git-effectively">How To Use Git Effectively</a> tutorial at IndiaReads community pages.</p>

<p><strong>Note:</strong> In order to follow this section, you will need a Github account. Alternatively, you can set up a droplet to host your own Git repository following <a href="https://indiareads/community/articles/how-to-set-up-a-private-git-server-on-a-vps">this</a> IndiaReads article on the subject. If you choose to do so, make sure to use the relevant URL on deployment files. </p>

<p>We are going to use the sample instructions supplied by <a href="https://www.github.com">Github</a> to create a source repository.</p>

<p>Execute the following, self-explanatory commands inside the <code>my_app</code> directory to initiate a repository:</p>
<pre class="code-pre "><code langs=""># !! These commands are to be executed on
#    your development machine, from where you will
#    deploy to your server.
#    Instructions might vary slightly depending on
#    your choice of operating system.
#
#    Make sure to set correct paths for application
#    Otherwise Nginx might not be able to locate it.

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
git config --global user.name "user123"

# Email:
# Usage: git config --global user.email "[your email]"
git config --global user.email "user123@domain.tld"

# Push the project's source code to your Github account
git push -u origin master
</code></pre>
<h2 id="working-with-capistrano-to-automate-deployments">Working With Capistrano To Automate Deployments</h2>

<hr />

<p>As you will remember from our first Capistrano article, the way to begin using the library is by <em>installing</em> it inside the project directory. In this section, we will see how to do that, followed by creating files that are needed to set servers.</p>

<h3 id="installing-capistrano-inside-the-project-directory">Installing Capistrano Inside The Project Directory</h3>

<hr />

<p>Another simple step in our article is installing the Capistrano files. The below command will scaffold some directories and files to be used by the tool for the deployment.</p>

<p>Run the following to initiate (i.e. <em>install</em>) Capistrano files:</p>
<pre class="code-pre "><code langs="">cap install

# mkdir -p config/deploy
# create config/deploy.rb
# create config/deploy/staging.rb
# create config/deploy/production.rb
# mkdir -p lib/capistrano/tasks
# Capified
</code></pre>
<h3 id="working-with-config-deploy-rb-inside-the-project-directory">Working With config/deploy.rb Inside The Project Directory</h3>

<hr />

<p>The file <code>deploy.rb</code> contains arguments and settings relevant to the deployment server(s). Here, we will tell Capistrano to which server(s) we would like to connect and deploy and how.</p>

<p><strong>Note:</strong> When editing the file (or defining the configurations), you can either comment them out or add the new lines. Make sure to <strong>not</strong> to have some example settings overriding the ones you are appending.</p>

<p>Run the following to edit the file using <code>nano</code> text editor:</p>
<pre class="code-pre "><code langs="">nano config/deploy.rb
</code></pre>
<p>Add the below block of code, modifying it to suit your own settings:</p>
<pre class="code-pre "><code langs=""># Define the name of the application
set :application, 'my_app'

# Define where can Capistrano access the source repository
# set :repo_url, 'https://github.com/[user name]/[application name].git'
set :scm, :git
set :repo_url, 'https://github.com/user123/my_app.git'

# Define where to put your application code
set :deploy_to, "/home/deployer/apps/my_app"

set :pty, true

set :format, :pretty

# Set the post-deployment instructions here.
# Once the deployment is complete, Capistrano
# will begin performing them as described.
# To learn more about creating tasks,
# check out:
# http://capistranorb.com/

# namespace: deploy do

#   desc 'Restart application'
#   task :restart do
#     on roles(:app), in: :sequence, wait: 5 do
#       # Your restart mechanism here, for example:
#       execute :touch, release_path.join('tmp/restart.txt')
#     end
#   end

#   after :publishing, :restart

#   after :restart, :clear_cache do
#     on roles(:web), in: :groups, limit: 3, wait: 10 do
#       # Here we can do anything such as:
#       # within release_path do
#       #   execute :rake, 'cache:clear'
#       # end
#     end
#   end

# end
</code></pre>
<p>Press CTRL+X and confirm with Y to save and exit.</p>

<h3 id="working-with-config-deploy-production-rb-inside-the-project-directory">Working With config/deploy/production.rb Inside The Project Directory</h3>

<hr />

<p><strong>Note:</strong> Similar to <code>deploy.rb</code>, you will need to make some amendments to the <code>production.rb</code> file. You are better modifying the code instead of appending the below block.</p>

<p>Run the following to edit the file using <code>nano</code> text editor:</p>
<pre class="code-pre "><code langs="">nano config/deploy/production.rb
</code></pre>
<p>Enter your server's settings, similar to below:</p>
<pre class="code-pre "><code langs=""># Define roles, user and IP address of deployment server
# role :name, %{[user]@[IP adde.]}
role :app, %w{deployer@162.243.74.190}
role :web, %w{deployer@162.243.74.190}
role :db,  %w{deployer@162.243.74.190}

# Define server(s)
server '162.243.74.190', user: 'deployer', roles: %w{web}

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
<li><p>Perform the deployment actions (i.e. get passenger restart the application) </p></li>
</ul>
<pre class="code-pre "><code langs="">cap production deploy
</code></pre>
<p>To learn more about Capistrano and what it can do, consider reading the [Capistrano documentation](capistranorb.com/documentation).</p>

<div class="author">Submitted by: <a href="https://twitter.com/ostezer">O.S. Tezer</a></div>

    