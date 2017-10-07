<link rel='stylesheet' href='digital_ocean.css'><br><img src=https://community-cdn-digitalocean-com.global.ssl.fastly.net/assets/tutorials/images/large/rails_with_unicorn_tw.jpg?1428609970/> <br> 
      <h3 id="introduction">Introduction</h3>

<p>When you are ready to deploy your Ruby on Rails application, there are many valid setups to consider. This tutorial will help you deploy the production environment of your Ruby on Rails application, with PostgreSQL as the database, using Unicorn and Nginx on Ubuntu 14.04.</p>

<p>Unicorn is an application server, like <a href="https://indiareads/community/tutorials/how-to-deploy-a-rails-app-with-passenger-and-nginx-on-ubuntu-14-04">Passenger</a> or <a href="https://indiareads/community/tutorials/how-to-deploy-a-rails-app-with-puma-and-nginx-on-ubuntu-14-04">Puma</a>, that enables your Rails application to process requests concurrently. As Unicorn is not designed to be accessed by users directly, we will use Nginx as a reverse proxy that will buffer requests and responses between users and your Rails application.</p>

<h2 id="prerequisites">Prerequisites</h2>

<p>This tutorial assumes that you have an Ubuntu 14.04 server with the following software installed, on the user that will deploy the application:</p>

<ul>
<li><a href="https://indiareads/community/tutorials/how-to-install-ruby-on-rails-with-rbenv-on-ubuntu-14-04">Ruby on Rails, using rbenv</a></li>
<li><a href="https://indiareads/community/tutorials/how-to-use-postgresql-with-your-ruby-on-rails-application-on-ubuntu-14-04">PostgreSQL with Rails</a></li>
</ul>

<p>If you do not have that set up already, follow the tutorials that are linked above. We will assume that your user is called <strong>deploy</strong>.</p>

<p>Also, this tutorial does not cover how to set up your development or test environments. If you need help with that, follow the example in the PostgreSQL with Rails tutorial.</p>

<h2 id="create-rails-application">Create Rails Application</h2>

<p>Ideally, you already have a Rails application that you want to deploy. If this is the case, you may skip this section, and make the appropriate substitutions while following along. If not, the first step is to create a new Rails application that uses PostgreSQL as its database.</p>

<p>This command will create a new Rails application, named "appname" that will use PostgreSQL as the database. Feel free to substitute the highlighted "appname" with something else:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">rails new <span class="highlight">appname</span> -d postgresql
</li></ul></code></pre>
<p>Then change into the application directory:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">cd <span class="highlight">appname</span>
</li></ul></code></pre>
<p>Let's take a moment to create the PostgreSQL user that will be used by the production environment of your Rails application.</p>

<h2 id="create-production-database-user">Create Production Database User</h2>

<p>To keep things simple, let's name the production database user the same as your application name. For example, if your application is called "appname", you should create a PostgreSQL user like this:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo -u postgres createuser -s <span class="highlight">appname</span>
</li></ul></code></pre>
<p>We want to set the database user's password, so enter the PostgreSQL console like this:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo -u postgres psql
</li></ul></code></pre>
<p>Then set the password for the database user, "appname" in the example, like this:</p>
<pre class="code-pre custom_prefix"><code langs=""><ul class="prefixed"><li class="line" prefix="postgres=#">\password <span class="highlight">appname</span>
</li></ul></code></pre>
<p>Enter your desired password and confirm it.</p>

<p>Exit the PostgreSQL console with this command:</p>
<pre class="code-pre custom_prefix"><code langs=""><ul class="prefixed"><li class="line" prefix="postgres=#">\q
</li></ul></code></pre>
<p>Now we're ready to configure the your application with the proper database connection information.</p>

<h2 id="configure-database-connection">Configure Database Connection</h2>

<p>Ensure that you are in your application's root directory (<code>cd ~/<span class="highlight">appname</span></code>).</p>

<p>Open your application's database configuration file in your favorite text editor. We'll use vi:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">vi config/database.yml
</li></ul></code></pre>
<p>Under the <code>default</code> section, find the line that says "pool: 5" and add the following line under it (if it doesn't already exist):</p>
<pre class="code-pre "><code langs="">  host: localhost
</code></pre>
<p>If you scroll to the bottom of the file, you will notice that the <code>production</code> section is set the following:</p>
<pre class="code-pre "><code langs="">  username: <span class="highlight">appname</span>
  password: <%= ENV['<span class="highlight">APPNAME</span>_DATABASE_PASSWORD'] %>
</code></pre>
<p>If your production username doesn't match the database user that you created earlier, set it now.</p>

<p>Note that the database password is configured to be read by an environment variable, <code><span class="highlight">APPNAME</span>_DATABASE_PASSWORD</code>. It is considered best practice to keep production passwords and secrets outside of your application codebase, as they can easily be exposed if you are using a distributed version control system such as Git. We will go over how to set up the database authentication with environment variables next.</p>

<h2 id="install-rbenv-vars-plugin">Install rbenv-vars Plugin</h2>

<p>Before deploying a production Rails application, you should set the production secret key and database password using environment variables. An easy way to manage environment variables, which we can use to load passwords and secrets into our application at runtime, is to use the <strong>rbenv-vars</strong> plugin.</p>

<p>To install the rbenv-vars plugin, simply change to the <code>.rbenv/plugins</code> directory and clone it from GitHub. For example, if rbenv is installed in your home directory, run these commands:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">cd ~/.rbenv/plugins
</li><li class="line" prefix="$">git clone https://github.com/sstephenson/rbenv-vars.git
</li></ul></code></pre>
<h3 id="set-environment-variables">Set Environment Variables</h3>

<p>Now that the rbenv-vars plugin is installed, let's set up the required environment variables.</p>

<p>First, generate the secret key, which will be used to verify the integrity of signed cookies:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">cd ~/appname
</li><li class="line" prefix="$">rake secret
</li></ul></code></pre>
<p>Copy the secret key that is generated, then open the <code>.rbenv-vars</code> file with your favorite editor. We will use vi:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">vi .rbenv-vars
</li></ul></code></pre>
<p>Any environment variables that you set here can be read by your Rails application.</p>

<p>First, set the <code>SECRET_KEY_BASE</code> variable like this (replace the highlighted text with the secret that you just generated and copied):</p>
<pre class="code-pre "><code langs="">SECRET_KEY_BASE=<span class="highlight">your_generated_secret</span>
</code></pre>
<p>Next, set the <code><span class="highlight">APPNAME</span>_DATABASE_PASSWORD</code> variable like this (replace the highlighted "APPNAME" with your your application name, and "prod_db_pass" with your production database user password):</p>
<pre class="code-pre "><code langs=""><span class="highlight">APPNAME</span>_DATABASE_PASSWORD=<span class="highlight">prod_db_pass</span>
</code></pre>
<p>Save and exit.</p>

<p>You may view which environment variables are set for your application with the rbenv-vars plugin by running this command:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">rbenv vars
</li></ul></code></pre>
<p>If you change your secret or database password, update your <code>.rbenv-vars</code> file. Be careful to keep this file private, and don't include it any public code repositories.</p>

<h2 id="create-production-database">Create Production Database</h2>

<p>Now that your application is configured to talk to your PostgreSQL database, let's create the production database:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">RAILS_ENV=production rake db:create
</li></ul></code></pre>
<h3 id="generate-a-controller">Generate a Controller</h3>

<p>If you are following along with the example, we will generate a scaffold controller so our application will have something to look at:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">rails generate scaffold Task title:string note:text
</li></ul></code></pre>
<p>Now run this command to update the production database:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">RAILS_ENV=production rake db:migrate
</li></ul></code></pre>
<h3 id="precompile-assets">Precompile Assets</h3>

<p>At this point, the application should work but you will need to precompile its assets so that any images, CSS, and scripts will load. To do so, run this command:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">RAILS_ENV=production rake assets:precompile
</li></ul></code></pre>
<h3 id="test-application">Test Application</h3>

<p>To test out if your application works, you can run the production environment, and bind it to the public IP address of your server (substitute your server's public IP address):</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">RAILS_ENV=production rails server --binding=<span class="highlight">server_public_IP</span>
</li></ul></code></pre>
<p>Now visit this URL in a web browser:</p>
<pre class="code-pre "><code langs="">http://<span class="highlight">server_public_IP</span>:3000/tasks
</code></pre>
<p>If it's working properly, you should see this page:</p>

<p><img src="https://assets.digitalocean.com/articles/rails_unicorn/tasks.png" alt="Tasks controller" /></p>

<p>Go back to your Rails server, and press <code>Ctrl-c</code> to stop the application.</p>

<h2 id="install-unicorn">Install Unicorn</h2>

<p>Now we are ready to install Unicorn.</p>

<p>An easy way to do this is to add it to your application's <code>Gemfile</code>. Open the Gemfile in your favorite editor (make sure you are in your application's root directory):</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">vi Gemfile
</li></ul></code></pre>
<p>At the end of the file, add the Unicorn gem with this line:</p>
<pre class="code-pre "><code langs="">gem 'unicorn'
</code></pre>
<p>Save and exit.</p>

<p>To install Unicorn, and any outstanding dependencies, run Bundler:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">bundle
</li></ul></code></pre>
<p>Unicorn is now installed, but we need to configure it.</p>

<h2 id="configure-unicorn">Configure Unicorn</h2>

<p>Let's add our Unicorn configuration to <code>config/unicorn.rb</code>. Open the file in a text editor:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">vi config/unicorn.rb
</li></ul></code></pre>
<p>Copy and paste this configuration into the file:</p>
<pre class="code-pre "><code langs=""># set path to application
app_dir = File.expand_path("../..", __FILE__)
shared_dir = "#{app_dir}/shared"
working_directory app_dir


# Set unicorn options
worker_processes 2
preload_app true
timeout 30

# Set up socket location
listen "#{shared_dir}/sockets/unicorn.sock", :backlog => 64

# Logging
stderr_path "#{shared_dir}/log/unicorn.stderr.log"
stdout_path "#{shared_dir}/log/unicorn.stdout.log"

# Set master PID location
pid "#{shared_dir}/pids/unicorn.pid"
</code></pre>
<p>Save and exit. This configures Unicorn with the location of your application, and the location of its socket, logs, and PIDs. Feel free to modify the file, or add any other options that you require.</p>

<p>Now create the directories that were referred to in the configuration file:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">mkdir -p shared/pids shared/sockets shared/log
</li></ul></code></pre>
<h2 id="create-unicorn-init-script">Create Unicorn Init Script</h2>

<p>Let's create an init script so we can easily start and stop Unicorn, and ensure that it will start on boot.</p>

<p>Create a script and open it for editing with this command (replace the highlighted part with your application name, if you wish):</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo vi /etc/init.d/unicorn_<span class="highlight">appname</span>
</li></ul></code></pre>
<p>Copy and paste the following code block into it, and be sure to substitute <code>USER</code> and <code>APP_NAME</code> (highlighted) with the appropriate values:</p>
<pre class="code-pre "><code langs="">#!/bin/sh

### BEGIN INIT INFO
# Provides:          unicorn
# Required-Start:    $all
# Required-Stop:     $all
# Default-Start:     2 3 4 5
# Default-Stop:      0 1 6
# Short-Description: starts the unicorn app server
# Description:       starts unicorn using start-stop-daemon
### END INIT INFO

set -e

USAGE="Usage: $0 <start|stop|restart|upgrade|rotate|force-stop>"

# app settings
USER="<span class="highlight">deploy</span>"
APP_NAME="<span class="highlight">appname</span>"
APP_ROOT="/home/$USER/$APP_NAME"
ENV="production"

# environment settings
PATH="/home/$USER/.rbenv/shims:/home/$USER/.rbenv/bin:$PATH"
CMD="cd $APP_ROOT && bundle exec unicorn -c config/unicorn.rb -E $ENV -D"
PID="$APP_ROOT/shared/pids/unicorn.pid"
OLD_PID="$PID.oldbin"

# make sure the app exists
cd $APP_ROOT || exit 1

sig () {
  test -s "$PID" && kill -$1 `cat $PID`
}

oldsig () {
  test -s $OLD_PID && kill -$1 `cat $OLD_PID`
}

case $1 in
  start)
    sig 0 && echo >&2 "Already running" && exit 0
    echo "Starting $APP_NAME"
    su - $USER -c "$CMD"
    ;;
  stop)
    echo "Stopping $APP_NAME"
    sig QUIT && exit 0
    echo >&2 "Not running"
    ;;
  force-stop)
    echo "Force stopping $APP_NAME"
    sig TERM && exit 0
    echo >&2 "Not running"
    ;;
  restart|reload|upgrade)
    sig USR2 && echo "reloaded $APP_NAME" && exit 0
    echo >&2 "Couldn't reload, starting '$CMD' instead"
    $CMD
    ;;
  rotate)
    sig USR1 && echo rotated logs OK && exit 0
    echo >&2 "Couldn't rotate logs" && exit 1
    ;;
  *)
    echo >&2 $USAGE
    exit 1
    ;;
esac
</code></pre>
<p>Save and exit. This will allow you to use <code>service unicorn_<span class="highlight">appname</span></code> to start and stop your Unicorn and your Rails application.</p>

<p>Update the script's permissions and enable Unicorn to start on boot:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo chmod 755 /etc/init.d/unicorn_appname
</li><li class="line" prefix="$">sudo update-rc.d unicorn_appname defaults
</li></ul></code></pre>
<p>Let's start it now:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo service unicorn_<span class="highlight">appname</span> start
</li></ul></code></pre>
<p>Now your Rails application's production environment is running under Unicorn, and it's listening on the <code>shared/sockets/unicorn.sock</code> socket. Before your application will be accessible to an outside user, you must set up the Nginx reverse proxy.</p>

<h2 id="install-and-configure-nginx">Install and Configure Nginx</h2>

<p>Install Nginx using apt-get:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo apt-get install nginx
</li></ul></code></pre>
<p>Now open the default server block with a text editor:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo vi /etc/nginx/sites-available/default
</li></ul></code></pre>
<p>Replace the contents of the file with the following code block. Be sure to replace the the highlighted parts with the appropriate username and application name:</p>
<pre class="code-pre "><code langs="">upstream app {
    # Path to Unicorn SOCK file, as defined previously
    server unix:/home/<span class="highlight">deploy</span>/<span class="highlight">appname</span>/shared/sockets/unicorn.sock fail_timeout=0;
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
<p>Save and exit. This configures Nginx as a reverse proxy, so HTTP requests get forwarded to the Unicorn application server via a Unix socket. Feel free to make any changes as you see fit.</p>

<p>Restart Nginx to put the changes into effect:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo service nginx restart
</li></ul></code></pre>
<p>Now the production environment of your Rails application is accessible via your server's public IP address or FQDN. To access the Tasks controller that we created earlier, visit your application server in a web browser:</p>
<pre class="code-pre "><code langs="">http://<span class="highlight">server_public_IP</span>/tasks
</code></pre>
<p>You should see the same page that you saw the first time you tested your application, but now it's being served through Nginx and Unicorn.</p>

<h2 id="conclusion">Conclusion</h2>

<p>Congratulations! You have deployed the production environment of your Ruby on Rails application using Nginx and Unicorn.</p>

<p>If you are looking to improve your production Rails application deployment, you should check out our tutorial series on <a href="https://indiareads/community/tutorial_series/how-to-use-capistrano-to-automate-deployments">How To Use Capistrano to Automate Deployments</a>. The series is based on CentOS, but it should still be helpful in automating your deployments.</p>

    