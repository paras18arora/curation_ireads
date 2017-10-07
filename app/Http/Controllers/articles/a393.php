<link rel='stylesheet' href='digital_ocean.css'><br><img src=https://community-cdn-digitalocean-com.global.ssl.fastly.net/assets/tutorials/images/large/rails_githooks_tw_%281%29.jpg?1438270929/> <br> 
      <h3 id="introduction">Introduction</h3>

<p>In this tutorial, we will show you how to use Git hooks to automate the deployment of the production environment of your Rails application to a remote Ubuntu 14.04 server. Using Git hooks will allow you to deploy your application by simply pushing your changes to a production server, instead of having to manually pull and do things like execute database migrations. As you continue to work on your application, setting up some form of automated deploys, such as Git hooks, will save you time in the long run.</p>

<p>This particular setup uses a simple "post-receive" Git hook, in addition to Puma as the application server, Nginx as a reverse proxy to Puma, and PostgreSQL as the database.</p>

<p>If you are new to Git Hooks and would like to learn more before moving on, read this tutorial: <a href="https://indiareads/community/tutorials/how-to-use-git-hooks-to-automate-development-and-deployment-tasks">How To Use Git Hooks To Automate Development and Deployment Tasks</a>.</p>

<h2 id="prerequisites">Prerequisites</h2>

<p>You will require access to a non-root user that has superuser privileges on your Ubuntu server. In our example setup, we will use a user called <code><span class="highlight">deploy</span></code>. This tutorial will show you how to set that up: <a href="https://indiareads/community/tutorials/initial-server-setup-with-ubuntu-14-04">Initial Server Setup with Ubuntu 14.04</a>. If you want to deploy without entering a password, be sure to set up SSH keys.</p>

<p>You will need to install Ruby on your server. If you haven't done so already, you can install it along with Rails using <a href="https://indiareads/community/tutorials/how-to-install-ruby-on-rails-with-rbenv-on-ubuntu-14-04">rbenv</a> or <a href="https://indiareads/community/tutorials/how-to-install-ruby-on-rails-on-ubuntu-14-04-using-rvm">RVM</a>.</p>

<p>You will also need to have a Rails application that is managed in a git repository on your local development machine. If you don't have one and would like to follow along, we'll provide a simple example app.</p>

<p>Let's get started!</p>

<h2 id="install-postgresql">Install PostgreSQL</h2>

<p>Most production Rails environments use PostgreSQL as the database, so let's install it on your server now.</p>

<p>On your <strong>production</strong> server, update apt-get:</p>
<pre class="code-pre custom_prefix"><code langs=""><ul class="prefixed"><li class="line" prefix="prod$">sudo apt-get update
</li></ul></code></pre>
<p>Then install PostgreSQL with these commands:</p>
<pre class="code-pre custom_prefix"><code langs=""><ul class="prefixed"><li class="line" prefix="prod$">sudo apt-get install postgresql postgresql-contrib libpq-dev
</li></ul></code></pre>
<p><span class="note"><strong>Note:</strong> You should also install PostgreSQL on your development machine, so you can install the <code>pg</code> gem, the PostgreSQL adapter, locally. This will be required to run <code>bundle install</code> when we add the gem to your application's Gemfile. As the installation steps vary by OS, this is an exercise left to the reader.<br /></span></p>

<h3 id="create-production-database-user">Create Production Database User</h3>

<p>To keep things simple, let's name the production database user the same as your application name. For example, if your application is called "appname", you should create a PostgreSQL user like this:</p>
<pre class="code-pre custom_prefix"><code langs=""><ul class="prefixed"><li class="line" prefix="prod$">sudo -u postgres createuser -s <span class="highlight">appname</span>
</li></ul></code></pre>
<p>We want to set the database user's password, so enter the PostgreSQL console like this:</p>
<pre class="code-pre custom_prefix"><code langs=""><ul class="prefixed"><li class="line" prefix="prod$">sudo -u postgres psql
</li></ul></code></pre>
<p>Then set the password for the database user, "appname" in the example, like this:</p>
<pre class="code-pre custom_prefix"><code langs=""><ul class="prefixed"><li class="line" prefix="postgres=#">\password <span class="highlight">appname</span>
</li></ul></code></pre>
<p>Enter your desired password and confirm it.</p>

<p>Exit the PostgreSQL console with this command:</p>
<pre class="code-pre custom_prefix"><code langs=""><ul class="prefixed"><li class="line" prefix="postgres=#">\q
</li></ul></code></pre>
<p>Now we're ready to configure the your application with the proper database connection information.</p>

<h2 id="prepare-your-rails-application">Prepare Your Rails Application</h2>

<p>On your <strong>development machine</strong>, most likely your local computer, we will prepare your application to be deployed.</p>

<h3 id="optional-create-a-rails-application">Optional: Create a Rails Application</h3>

<p>Ideally, you already have a Rails application that you want to deploy. If this is the case, you may skip this subsection, and make the appropriate substitutions while following along. If not, the first step is to create a new Rails application.</p>

<p>These commands will create a new Rails application, named "appname", in our home directory. Feel free to substitute the highlighted "appname" with something else:</p>
<pre class="code-pre custom_prefix"><code langs=""><ul class="prefixed"><li class="line" prefix="dev$">cd ~
</li><li class="line" prefix="dev$">rails new <span class="highlight">appname</span>
</li></ul></code></pre>
<p>Then change into the application directory:</p>
<pre class="code-pre custom_prefix"><code langs=""><ul class="prefixed"><li class="line" prefix="dev$">cd <span class="highlight">appname</span>
</li></ul></code></pre>
<p>For our sample app, we will generate a scaffold controller so our application will have something to display:</p>
<pre class="code-pre custom_prefix"><code langs=""><ul class="prefixed"><li class="line" prefix="dev$">rails generate scaffold Task title:string note:text
</li></ul></code></pre>
<p>Now let's make sure our application is in a git repository.</p>

<h3 id="initialize-git-repo">Initialize Git Repo</h3>

<p>If your application isn't already in a Git repository for some reason, initialize it and perform an initial commit.</p>

<p>On your <strong>development machine</strong>, change to your application's directory. In our example, our app is called "appname" and it is located in our home directory:</p>
<pre class="code-pre custom_prefix"><code langs=""><ul class="prefixed"><li class="line" prefix="dev$">cd <span class="highlight">~/appname</span>
</li></ul></code></pre><pre class="code-pre custom_prefix"><code langs=""><ul class="prefixed"><li class="line" prefix="dev$">git init
</li><li class="line" prefix="dev$">git add -A
</li><li class="line" prefix="dev$">git commit -m 'initial commit'
</li></ul></code></pre>
<p>Now let's tweak our application to prepare it to connect to our production PostgreSQL database.</p>

<h3 id="update-database-configuration">Update Database Configuration</h3>

<p>On your <strong>development machine</strong>, change to your application's directory if you aren't already there. In our example, our app is called "appname" and it is located in our home directory:</p>
<pre class="code-pre custom_prefix"><code langs=""><ul class="prefixed"><li class="line" prefix="dev$">cd <span class="highlight">~/appname</span>
</li></ul></code></pre>
<p>Now open the database configuration file in your favorite editor. We'll use <code>vi</code>:</p>
<pre class="code-pre custom_prefix"><code langs=""><ul class="prefixed"><li class="line" prefix="dev$">vi config/database.yml
</li></ul></code></pre>
<p>Find the <strong>production</strong> section of your application's database configuration, and replace it with your production database connection information. If you followed the example set up exactly, it will look something like this (substitute any values where appropriate):</p>
<div class="code-label " title="config/database.yml excerpt">config/database.yml excerpt</div><pre class="code-pre "><code langs="">production:
  <<: *default
  host: localhost
  adapter: postgresql
  encoding: utf8
  database: <span class="highlight">appname_production</span>
  pool: 5
  username: <%= ENV['<span class="highlight">APPNAME</span>_DATABASE_USER'] %>
  password: <%= ENV['<span class="highlight">APPNAME</span>_DATABASE_PASSWORD'] %>
</code></pre>
<p>Save and exit. This specifies that the production environment of the application should use a PostgreSQL database called "appname_production" on the localhost—the production server. Note that the database username and password are set to environment variables. We'll specify those on the server later.</p>

<h3 id="update-gemfile">Update Gemfile</h3>

<p>If your Gemfile does not already have the PostgreSQL adapter gem, <code>pg</code>, and the Puma gem specified, you should add them now.</p>

<p>Open your application's Gemfile in your favorite editor. We'll use <code>vi</code> here:</p>
<pre class="code-pre custom_prefix"><code langs=""><ul class="prefixed"><li class="line" prefix="dev$">vi Gemfile
</li></ul></code></pre>
<p>Add the following lines to the Gemfile:</p>
<div class="code-label " title="Gemfile excerpt">Gemfile excerpt</div><pre class="code-pre "><code langs="">group :production do
  gem 'pg'
  gem 'puma'
end
</code></pre>
<p>Save and exit. This specifies that the <code>production</code> environment should use the <code>pg</code> and <code>puma</code> gems.</p>

<h3 id="configure-puma">Configure Puma</h3>

<p>Before configuring Puma, you should look up the number of CPU cores your server has. You can easily to that, on your server, with this command:</p>
<pre class="code-pre custom_prefix"><code langs=""><ul class="prefixed"><li class="line" prefix="prod$">grep -c processor /proc/cpuinfo
</li></ul></code></pre>
<p>Now, on your <strong>development machine</strong>, add the Puma configuration to <code>config/puma.rb</code>. Open the file in a text editor:</p>
<pre class="code-pre custom_prefix"><code langs=""><ul class="prefixed"><li class="line" prefix="dev$">vi config/puma.rb
</li></ul></code></pre>
<p>Copy and paste this configuration into the file:</p>
<div class="code-label " title="config/puma.rb">config/puma.rb</div><pre class="code-pre "><code langs=""># Change to match your CPU core count
workers <span class="highlight">2</span>

# Min and Max threads per worker
threads 1, 6

app_dir = File.expand_path("../..", __FILE__)
shared_dir = "#{app_dir}/shared"

# Default to production
rails_env = ENV['RAILS_ENV'] || "production"
environment rails_env

# Set up socket location
bind "unix://#{shared_dir}/sockets/puma.sock"

# Logging
stdout_redirect "#{shared_dir}/log/puma.stdout.log", "#{shared_dir}/log/puma.stderr.log", true

# Set master PID and state locations
pidfile "#{shared_dir}/pids/puma.pid"
state_path "#{shared_dir}/pids/puma.state"
activate_control_app

on_worker_boot do
  require "active_record"
  ActiveRecord::Base.connection.disconnect! rescue ActiveRecord::ConnectionNotEstablished
  ActiveRecord::Base.establish_connection(YAML.load_file("#{app_dir}/config/database.yml")[rails_env])
end
</code></pre>
<p>Change the number of <code>workers</code> to the number of CPU cores of your server. The example assumes you have 2 cores.</p>

<p>Save and exit. This configures Puma with the location of your application, and the location of its socket, logs, and PIDs. Feel free to modify the file, or add any other options that you require.</p>

<p>Commit your recent changes:</p>
<pre class="code-pre custom_prefix"><code langs=""><ul class="prefixed"><li class="line" prefix="dev$">git add -A
</li><li class="line" prefix="dev$">git commit -m 'added pg and puma'
</li></ul></code></pre>
<p>Before moving on, generate a secret key that will be used for the production environment of your app:</p>
<pre class="code-pre custom_prefix"><code langs=""><ul class="prefixed"><li class="line" prefix="dev$">rake secret
</li></ul></code></pre><pre class="code-pre "><code langs=""><div class="secondary-code-label " title="rake secret sample output:">rake secret sample output:</div>29cc5419f6b0ee6b03b717392c28f5869eff0d136d8ae388c68424c6e5dbe52c1afea8fbec305b057f4b071db1646473c1f9a62f803ab8386456ad3b29b14b89
</code></pre>
<p>You will copy the output and use it to set your application's <code>SECRET_KEY_BASE</code> in the next step.</p>

<h2 id="create-puma-upstart-script">Create Puma Upstart Script</h2>

<p>Let's create an Upstart init script so we can easily start and stop Puma, and ensure that it will start on boot.</p>

<p>On your <strong>production server</strong>, download the Jungle Upstart tool from the Puma GitHub repository to your home directory:</p>
<pre class="code-pre custom_prefix"><code langs=""><ul class="prefixed"><li class="line" prefix="prod$">cd ~
</li><li class="line" prefix="prod$">wget https://raw.githubusercontent.com/puma/puma/master/tools/jungle/upstart/puma-manager.conf
</li><li class="line" prefix="prod$">wget https://raw.githubusercontent.com/puma/puma/master/tools/jungle/upstart/puma.conf
</li></ul></code></pre>
<p>Now open the provided <code>puma.conf</code> file, so we can configure the Puma deployment user:</p>
<pre class="code-pre custom_prefix"><code langs=""><ul class="prefixed"><li class="line" prefix="prod$">vi puma.conf
</li></ul></code></pre>
<p>Look for the two lines that specify <code>setuid</code> and <code>setgid</code>, and replace "apps" with the name of your deployment user and group. For example, if your deployment user is called "deploy", the lines should look like this:</p>
<div class="code-label " title="puma.conf excerpt 1 of 2">puma.conf excerpt 1 of 2</div><pre class="code-pre "><code langs="">setuid <span class="highlight">deploy</span>
setgid <span class="highlight">deploy</span>
</code></pre>
<p>Now look for the line with this: <code>exec /bin/bash <<'EOT'</code>. Add the following lines under it, making sure to substitute the PostgreSQL username and password, and the rake secret that you created earlier:</p>
<div class="code-label " title="puma.conf excerpt 2 of 2">puma.conf excerpt 2 of 2</div><pre class="code-pre "><code langs="">  export <span class="highlight">APPNAME</span>_DATABASE_USER='<span class="highlight">appname</span>'
  export <span class="highlight">APPNAME</span>_DATABASE_PASSWORD='<span class="highlight">appname_password</span>'
  export SECRET_KEY_BASE='<span class="highlight">rake_secret_generated_above</span>'
</code></pre>
<p>Save and exit.</p>

<p>Now copy the scripts to the Upstart services directory:</p>
<pre class="code-pre custom_prefix"><code langs=""><ul class="prefixed"><li class="line" prefix="prod$">sudo cp puma.conf puma-manager.conf /etc/init
</li></ul></code></pre>
<p>The <code>puma-manager.conf</code> script references <code>/etc/puma.conf</code> for the applications that it should manage. Let's create and edit that inventory file now:</p>
<pre class="code-pre custom_prefix"><code langs=""><ul class="prefixed"><li class="line" prefix="prod$">sudo vi /etc/puma.conf
</li></ul></code></pre>
<p>Each line in this file should be the path to an application that you want <code>puma-manager</code> to manage. We are going to deploy our application to a directory named "appname" in our user's home directory. In our example, it would be the following (be sure to update the path to where your app will live:</p>
<div class="code-label " title="/etc/puma.conf">/etc/puma.conf</div><pre class="code-pre "><code langs="">/home/<span class="highlight">deploy</span>/<span class="highlight">appname</span>
</code></pre>
<p>Save and exit.</p>

<p>Now your application is configured to start at boot time, through Upstart. This means that your application will start even after your server is rebooted. Keep in mind that we haven't deployed the application, so we don't want to start it just yet.</p>

<h2 id="install-and-configure-nginx">Install and Configure Nginx</h2>

<p>To make the application accessible to the internet, we should use Nginx as a web server.</p>

<p>Install Nginx using apt-get:</p>
<pre class="code-pre custom_prefix"><code langs=""><ul class="prefixed"><li class="line" prefix="prod$">sudo apt-get install nginx
</li></ul></code></pre>
<p>Now open the default server block with a text editor:</p>
<pre class="code-pre custom_prefix"><code langs=""><ul class="prefixed"><li class="line" prefix="prod$">sudo vi /etc/nginx/sites-available/default
</li></ul></code></pre>
<p>Replace the contents of the file with the following code block. Be sure to replace the the highlighted parts with the appropriate username and application name (two locations):</p>
<div class="code-label " title="/etc/nginx/sites-available/default">/etc/nginx/sites-available/default</div><pre class="code-pre "><code langs="">upstream app {
    # Path to Puma SOCK file, as defined previously
    server unix:/home/<span class="highlight">deploy</span>/<span class="highlight">appname</span>/shared/sockets/puma.sock fail_timeout=0;
}

server {
    listen 80;
    server_name localhost;

    root /home/<span class="highlight">deploy</span>/<span class="highlight">appname</span>/public;

    try_files $uri/index.html $uri @app;

    location @app {
        proxy_pass http://app;
        proxy_set_header X-Forwarded-For $proxy_add_x_forwarded_for;
        proxy_set_header Host $http_host;
        proxy_redirect off;
    }

    error_page 500 502 503 504 /500.html;
    client_max_body_size 4G;
    keepalive_timeout 10;
}
</code></pre>
<p>Save and exit. This configures Nginx as a reverse proxy, so HTTP requests get forwarded to the Puma application server via a Unix socket. Feel free to make any changes as you see fit.</p>

<p>We'll hold off from restarting Nginx for now, as the application doesn't exist on the server yet. We'll prepare the application next.</p>

<h2 id="prepare-production-git-remote">Prepare Production Git Remote</h2>

<p>On your <strong>production server</strong>, install git with apt-get:</p>
<pre class="code-pre custom_prefix"><code langs=""><ul class="prefixed"><li class="line" prefix="prod$">sudo apt-get install git
</li></ul></code></pre>
<p>Then create a directory for the remote repository. We will create a bare git repository in the home directory called "appname_production". Feel free to name your remote repository whatever you want (except don't put it in <code>~/appname</code> because that's where we will deploy the application to):</p>
<pre class="code-pre custom_prefix"><code langs=""><ul class="prefixed"><li class="line" prefix="prod$">mkdir ~/<span class="highlight">appname_production</span>
</li><li class="line" prefix="prod$">cd ~/<span class="highlight">appname_production</span>
</li><li class="line" prefix="prod$">git init --bare
</li></ul></code></pre>
<p>Since this is a bare repository, there is no working directory and all of the files that are located in .git in a conventional setup are in the main directory itself.</p>

<p>We need to create post-receive git hook, which is the script that will run when the production server receives a git push. Open the <code>hooks/post-receive</code> file in your editor:</p>
<pre class="code-pre custom_prefix"><code langs=""><ul class="prefixed"><li class="line" prefix="prod$">vi hooks/post-receive
</li></ul></code></pre>
<p>Copy and paste the following script into the <code>post-receive</code> file:</p>
<div class="code-label " title="hooks/post-receive">hooks/post-receive</div><pre class="code-pre "><code langs="">#!/bin/bash

GIT_DIR=/home/<span class="highlight">deploy/appname_production</span>
WORK_TREE=/home/<span class="highlight">deploy/appname</span>
export <span class="highlight">APPNAME</span>_DATABASE_USER='<span class="highlight">appname</span>'
export <span class="highlight">APPNAME</span>_DATABASE_PASSWORD='<span class="highlight">appname_password</span>'

export RAILS_ENV=production
. ~/.bash_profile

while read oldrev newrev ref
do
    if [[ $ref =~ .*/master$ ]];
    then
        echo "Master ref received.  Deploying master branch to production..."
        mkdir -p $WORK_TREE
        git --work-tree=$WORK_TREE --git-dir=$GIT_DIR checkout -f
        mkdir -p $WORK_TREE/shared/pids $WORK_TREE/shared/sockets $WORK_TREE/shared/log

        # start deploy tasks
        cd $WORK_TREE
        bundle install
        rake db:create
        rake db:migrate
        rake assets:precompile
        sudo restart puma-manager
        sudo service nginx restart
        # end deploy tasks
        echo "Git hooks deploy complete"
    else
        echo "Ref $ref successfully received.  Doing nothing: only the master branch may be deployed on this server."
    fi
done
</code></pre>
<p>Be sure to update the following highlighted values:</p>

<ul>
<li><code>GIT_DIR</code>: the directory of bare git repository you created earlier</li>
<li><code>WORK_TREE</code>: the directory to which you want to deploy your application (this should match the location that you specified in the Puma configuration)</li>
<li><code>APPNAME_DATABASE_USER</code>: PostgreSQL username (required for rake tasks)</li>
<li><code>APPNAME_DATABASE_PASSWORD</code>: PostgreSQL password (required for rake tasks)</li>
</ul>

<p>Next, you should review the commands between the <code># start deploy tasks</code> and <code># end deploy tasks</code> comments. These are the commands that will run every time the master branch is pushed to the production git remote (<code>appname_production</code>). If you leave them as is, the server will attempt to do the following for the production environment of your application:</p>

<ul>
<li>Run bundler</li>
<li>Create the database</li>
<li>Migrate the database</li>
<li>Precompile the assets</li>
<li>Restart Puma</li>
<li>Restart Nginx</li>
</ul>

<p>If you want to make any changes, or add error checking, feel free to do that here. </p>

<p>Once you are done reviewing the post-receive script, save and exit.</p>

<p>Next, make the script executable:</p>
<pre class="code-pre custom_prefix"><code langs=""><ul class="prefixed"><li class="line" prefix="prod$">chmod +x hooks/post-receive
</li></ul></code></pre>
<h3 id="passwordless-sudo">Passwordless Sudo</h3>

<p>Because the post-receive hook needs to run sudo commands, we will allow the deploy user to use passwordless <code>sudo</code> (substitute your deploy username here if it's different):</p>
<pre class="code-pre custom_prefix"><code langs=""><ul class="prefixed"><li class="line" prefix="prod$">sudo sh -c 'echo "<span class="highlight">deploy</span> ALL=(ALL) NOPASSWD:ALL" > /etc/sudoers.d/90-deploy'
</li></ul></code></pre>
<p>This will allow the <code>deploy</code> user to run <code>sudo</code> commands without supplying a password. Note that you will probably want to restrict what the commands that the deploy user can run with superuser privileges. At a minimum, you will want to use SSH key authentication and disable password authentication.</p>

<h2 id="add-production-git-remote">Add Production Git Remote</h2>

<p>Now that we have everything set up on the production server, let's add the production git remote to our application's repository.</p>

<p>On your <strong>development machine</strong>, ensure that you are in your application's directory:</p>
<pre class="code-pre custom_prefix"><code langs=""><ul class="prefixed"><li class="line" prefix="dev$">cd <span class="highlight">~/appname</span>
</li></ul></code></pre>
<p>Then add a new git remote named "production" that points to the bare git repository, <code>appname_production</code>, that you created on your production server. Substitute the username (deploy), server IP address, and remote repository name (appname_production):</p>
<pre class="code-pre custom_prefix"><code langs=""><ul class="prefixed"><li class="line" prefix="dev$">git remote add production <span class="highlight">deploy</span>@<span class="highlight">production_server_public_IP</span>:<span class="highlight">appname_production</span>
</li></ul></code></pre>
<p>Now your application is ready to be deployed with a git push.</p>

<h2 id="deploy-to-production">Deploy to Production</h2>

<p>With all the preparation you've done, you can now deploy your application to your production server by running the following git command:</p>
<pre class="code-pre custom_prefix"><code langs=""><ul class="prefixed"><li class="line" prefix="dev$">git push production master
</li></ul></code></pre>
<p>This simply pushes your local master branch to the production remote that you created earlier. When the production remote receives the push, it will execute the <code>post-receive</code> hook script that we set up earlier. If you set everything up correctly, your application should now be available at the public IP address of the your production server.</p>

<p>If you used our example application, you should be able to access <code>http://<span class="highlight">production_server_IP</span>/tasks</code> in a web browser and see something like this:</p>

<p><img src="https://assets.digitalocean.com/articles/rails_githooks/tasks.png" alt="Sample Rails App" /></p>

<h2 id="conclusion">Conclusion</h2>

<p>Any time you make a change to your application, you can run the same git push command to deploy to your production server. This alone should save you a lot of time over the life of your project.</p>

<p>This tutorial only covered the "post-receive" hook, but there are several other types of hooks that can help improve the automation of your deployment process. Read this tutorial to learn more about Git hooks: <a href="https://indiareads/community/tutorials/how-to-use-git-hooks-to-automate-development-and-deployment-tasks">How To Use Git Hooks To Automate Development and Deployment Tasks</a>!</p>

    