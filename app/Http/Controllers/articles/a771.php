<link rel='stylesheet' href='digital_ocean.css'><br><img src=https://community-cdn-digitalocean-com.global.ssl.fastly.net/assets/tutorials/images/large/rails_with_puma_tw.jpg?1428609926/> <br> 
      <h3 id="introduction">Introduction</h3>

<p>When you are ready to deploy your Ruby on Rails application, there are many valid setups to consider. This tutorial will help you deploy the production environment of your Ruby on Rails application, with PostgreSQL as the database, using Puma and Nginx on Ubuntu 14.04.</p>

<p>Puma is an application server, like <a href="https://indiareads/community/tutorials/how-to-deploy-a-rails-app-with-passenger-and-nginx-on-ubuntu-14-04">Passenger</a> or <a href="https://indiareads/community/tutorials/how-to-deploy-a-rails-app-with-unicorn-and-nginx-on-ubuntu-14-04">Unicorn</a>, that enables your Rails application to process requests concurrently. As Puma is not designed to be accessed by users directly, we will use Nginx as a reverse proxy that will buffer requests and responses between users and your Rails application.</p>

<h2 id="prerequisites">Prerequisites</h2>

<p>This tutorial assumes that you have an Ubuntu 14.04 server with the following software installed, on the user that will deploy the application:</p>

<ul>
<li><a href="https://indiareads/community/tutorials/how-to-install-ruby-on-rails-with-rbenv-on-ubuntu-14-04">Ruby on Rails, using rbenv</a></li>
<li><a href="https://indiareads/community/tutorials/how-to-use-postgresql-with-your-ruby-on-rails-application-on-ubuntu-14-04">PostgreSQL with Rails</a></li>
</ul>

<p>If you do not have that set up already, follow the tutorials that are linked above. We will assume that your user is called <strong>deploy</strong>.</p>

<p>Also, this tutorial does not cover how to set up your development or test environments. If you need help with that, follow the example in the <a href="https://indiareads/community/tutorials/how-to-use-postgresql-with-your-ruby-on-rails-application-on-ubuntu-14-04">PostgreSQL with Rails tutorial</a>.</p>

<h2 id="create-rails-application">Create Rails Application</h2>

<p>Ideally, you already have a Rails application that you want to deploy. If this is the case, you may skip this section, and make the appropriate substitutions while following along. If not, the first step is to create a new Rails application that uses PostgreSQL as its database.</p>

<p>This command will create a new Rails application, named "appname" that will use PostgreSQL as the database. Feel free to substitute the highlighted "appname" with something else:</p>
<pre class="code-pre "><code langs="">rails new <span class="highlight">appname</span> -d postgresql
</code></pre>
<p>Then change into the application directory:</p>
<pre class="code-pre "><code langs="">cd <span class="highlight">appname</span>
</code></pre>
<p>Let's take a moment to create the PostgreSQL user that will be used by the production environment of your Rails application.</p>

<h2 id="create-production-database-user">Create Production Database User</h2>

<p>To keep things simple, let's name the production database user the same as your application name. For example, if your application is called "appname", you should create a PostgreSQL user like this:</p>
<pre class="code-pre "><code langs="">sudo -u postgres createuser -s <span class="highlight">appname</span>
</code></pre>
<p>We want to set the database user's password, so enter the PostgreSQL console like this:</p>
<pre class="code-pre "><code langs="">sudo -u postgres psql
</code></pre>
<p>Then set the password for the database user, "appname" in the example, like this:</p>
<pre class="code-pre "><code langs="">\password <span class="highlight">appname</span>
</code></pre>
<p>Enter your desired password and confirm it.</p>

<p>Exit the PostgreSQL console with this command:</p>
<pre class="code-pre "><code langs="">\q
</code></pre>
<p>Now we're ready to configure the your application with the proper database connection information.</p>

<h2 id="configure-database-connection">Configure Database Connection</h2>

<p>Ensure that you are in your application's root directory (<code>cd ~/<span class="highlight">appname</span></code>).</p>

<p>Open your application's database configuration file in your favorite text editor. We'll use vi:</p>
<pre class="code-pre "><code langs="">vi config/database.yml
</code></pre>
<p>Update the <code>production</code> section so it looks something like this:</p>
<pre class="code-pre "><code langs="">production:
  <<: *default
  host: localhost
  adapter: postgresql
  encoding: utf8
  database: <span class="highlight">appname_production</span>
  pool: 5
  username: <%= ENV['<span class="highlight">APPNAME</span>_DATABASE_USER'] %>
  password: <%= ENV['<span class="highlight">APPNAME</span>_DATABASE_PASSWORD'] %>
</code></pre>
<p>Note that the database username and password are configured to be read by environment variables,  <code><span class="highlight">APPNAME</span>_DATABASE_USER</code> and <code><span class="highlight">APPNAME</span>_DATABASE_PASSWORD</code>. It is considered best practice to keep production passwords and secrets outside of your application codebase, as they can easily be exposed if you are using a distributed version control system such as Git. We will go over how to set up the database authentication with environment variables next.</p>

<p>Save and exit.</p>

<h2 id="install-rbenv-vars-plugin">Install rbenv-vars Plugin</h2>

<p>Before deploying a production Rails application, you should set the production secret key and database password using environment variables. An easy way to manage environment variables, which we can use to load passwords and secrets into our application at runtime, is to use the <strong>rbenv-vars</strong> plugin.</p>

<p>To install the rbenv-vars plugin, simply change to the <code>.rbenv/plugins</code> directory and clone it from GitHub. For example, if rbenv is installed in your home directory, run these commands:</p>
<pre class="code-pre "><code langs="">cd ~/.rbenv/plugins
git clone https://github.com/sstephenson/rbenv-vars.git
</code></pre>
<h3 id="set-environment-variables">Set Environment Variables</h3>

<p>Now that the rbenv-vars plugin is installed, let's set up the required environment variables.</p>

<p>First, generate the secret key, which will be used to verify the integrity of signed cookies:</p>
<pre class="code-pre "><code langs="">cd ~/appname
rake secret
</code></pre>
<p>Copy the secret key that is generated, then open the <code>.rbenv-vars</code> file with your favorite editor. We will use vi:</p>
<pre class="code-pre "><code langs="">vi .rbenv-vars
</code></pre>
<p>Any environment variables that you set here can be read by your Rails application.</p>

<p>First, set the <code>SECRET_KEY_BASE</code> variable like this (replace the highlighted text with the secret that you just generated and copied):</p>
<pre class="code-pre "><code langs="">SECRET_KEY_BASE=<span class="highlight">your_generated_secret</span>
</code></pre>
<p>Next, set the <code><span class="highlight">APPNAME</span>_DATABASE_USER</code> variable like this (replace the highlighted "APPNAME" with your your application name, and "appname" with your production database username):</p>
<pre class="code-pre "><code langs=""><span class="highlight">APPNAME</span>_DATABASE_USER=<span class="highlight">appname</span>
</code></pre>
<p>Lastly, set the <code><span class="highlight">APPNAME</span>_DATABASE_PASSWORD</code> variable like this (replace the highlighted "APPNAME" with your your application name, and "prod_db_pass" with your production database user password):</p>
<pre class="code-pre "><code langs=""><span class="highlight">APPNAME</span>_DATABASE_PASSWORD=<span class="highlight">prod_db_pass</span>
</code></pre>
<p>Save and exit.</p>

<p>You may view which environment variables are set for your application with the rbenv-vars plugin by running this command:</p>
<pre class="code-pre "><code langs="">rbenv vars
</code></pre>
<p>If you change your secret or database password, update your <code>.rbenv-vars</code> file. Be careful to keep this file private, and don't include it any public code repositories.</p>

<h2 id="create-production-database">Create Production Database</h2>

<p>Now that your application is configured to talk to your PostgreSQL database, let's create the production database:</p>
<pre class="code-pre "><code langs="">RAILS_ENV=production rake db:create
</code></pre>
<h3 id="generate-a-controller">Generate a Controller</h3>

<p>If you are following along with the example, we will generate a scaffold controller so our application will have something to look at:</p>
<pre class="code-pre "><code langs="">rails generate scaffold Task title:string note:text
</code></pre>
<p>Now run this command to update the production database:</p>
<pre class="code-pre "><code langs="">RAILS_ENV=production rake db:migrate
</code></pre>
<p>You should also precompile the assets:</p>
<pre class="code-pre "><code langs="">RAILS_ENV=production rake assets:precompile
</code></pre>
<p>To test out if your application works, you can run the production environment, and bind it to the public IP address of your server (substitute your server's public IP address):</p>
<pre class="code-pre "><code langs="">RAILS_ENV=production rails server --binding=<span class="highlight">server_public_IP</span>
</code></pre>
<p>Now visit this URL in a web browser:</p>
<pre class="code-pre "><code langs="">http://<span class="highlight">server_public_IP</span>:3000/tasks
</code></pre>
<p>If it's working properly, you should see this page:</p>

<p><img src="https://assets.digitalocean.com/articles/rails_unicorn/tasks.png" alt="Tasks controller" /></p>

<p>Go back to your Rails server, and press <code>Ctrl-c</code> to stop the application.</p>

<h2 id="install-puma">Install Puma</h2>

<p>Now we are ready to install Puma.</p>

<p>An easy way to do this is to add it to your application's <code>Gemfile</code>. Open the Gemfile in your favorite editor (make sure you are in your application's root directory):</p>
<pre class="code-pre "><code langs="">vi Gemfile
</code></pre>
<p>At the end of the file, add the Puma gem with this line:</p>
<pre class="code-pre "><code langs="">gem 'puma'
</code></pre>
<p>Save and exit.</p>

<p>To install Puma, and any outstanding dependencies, run Bundler:</p>
<pre class="code-pre "><code langs="">bundle
</code></pre>
<p>Puma is now installed, but we need to configure it.</p>

<h2 id="configure-puma">Configure Puma</h2>

<p>Before configuring Puma, you should look up the number of CPU cores your server has. You can easily to that with this command:</p>
<pre class="code-pre "><code langs="">grep -c processor /proc/cpuinfo
</code></pre>
<p>Now, let's add our Puma configuration to <code>config/puma.rb</code>. Open the file in a text editor:</p>
<pre class="code-pre "><code langs="">vi config/puma.rb
</code></pre>
<p>Copy and paste this configuration into the file:</p>
<pre class="code-pre "><code langs=""># Change to match your CPU core count
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
<p>Change the number of <code>workers</code> to the number of CPU cores of your server.</p>

<p>Save and exit. This configures Puma with the location of your application, and the location of its socket, logs, and PIDs. Feel free to modify the file, or add any other options that you require.</p>

<p>Now create the directories that were referred to in the configuration file:</p>
<pre class="code-pre "><code langs="">mkdir -p shared/pids shared/sockets shared/log
</code></pre>
<h2 id="create-puma-upstart-script">Create Puma Upstart Script</h2>

<p>Let's create an Upstart init script so we can easily start and stop Puma, and ensure that it will start on boot.</p>

<p>Download the Jungle Upstart tool from the Puma GitHub repository to your home directory:</p>
<pre class="code-pre "><code langs="">cd ~
wget https://raw.githubusercontent.com/puma/puma/master/tools/jungle/upstart/puma-manager.conf
wget https://raw.githubusercontent.com/puma/puma/master/tools/jungle/upstart/puma.conf
</code></pre>
<p>Now open the provided <code>puma.conf</code> file, so we can configure the Puma deployment user:</p>
<pre class="code-pre "><code langs="">vi puma.conf
</code></pre>
<p>Look for the two lines that specify <code>setuid</code> and <code>setgid</code>, and replace "apps" with the name of your deployment user and group. For example, if your deployment user is called "deploy", the lines should look like this:</p>
<pre class="code-pre "><code langs="">setuid <span class="highlight">deploy</span>
setgid <span class="highlight">deploy</span>
</code></pre>
<p>Save and exit.</p>

<p>Now copy the scripts to the Upstart services directory:</p>
<pre class="code-pre "><code langs="">sudo cp puma.conf puma-manager.conf /etc/init
</code></pre>
<p>The <code>puma-manager.conf</code> script references <code>/etc/puma.conf</code> for the applications that it should manage. Let's create and edit that inventory file now:</p>
<pre class="code-pre "><code langs="">sudo vi /etc/puma.conf
</code></pre>
<p>Each line in this file should be the path to an application that you want <code>puma-manager</code> to manage. Add the path to your application now. For example:</p>
<pre class="code-pre "><code langs="">/home/<span class="highlight">deploy</span>/<span class="highlight">appname</span>
</code></pre>
<p>Save and exit.</p>

<p>Now your application is configured to start at boot time, through Upstart. This means that your application will start even after your server is rebooted.</p>

<h3 id="start-puma-applications-manually">Start Puma Applications Manually</h3>

<p>To start all of your managed Puma apps now, run this command:</p>
<pre class="code-pre "><code langs="">sudo start puma-manager
</code></pre>
<p>You may also start a single Puma application by using the <code>puma</code> Upstart script, like this:</p>
<pre class="code-pre "><code langs="">sudo start puma app=/home/<span class="highlight">deploy</span>/<span class="highlight">appname</span>
</code></pre>
<p>You may also use <code>stop</code> and <code>restart</code> to control the application, like so:</p>
<pre class="code-pre "><code langs="">sudo stop puma-manager
sudo restart puma-manager
</code></pre>
<p>Now your Rails application's production environment is running under Puma, and it's listening on the <code>shared/sockets/puma.sock</code> socket. Before your application will be accessible to an outside user, you must set up the Nginx reverse proxy.</p>

<h2 id="install-and-configure-nginx">Install and Configure Nginx</h2>

<p>Install Nginx using apt-get:</p>
<pre class="code-pre "><code langs="">sudo apt-get install nginx
</code></pre>
<p>Now open the default server block with a text editor:</p>
<pre class="code-pre "><code langs="">sudo vi /etc/nginx/sites-available/default
</code></pre>
<p>Replace the contents of the file with the following code block. Be sure to replace the the highlighted parts with the appropriate username and application name (two locations):</p>
<pre class="code-pre "><code langs="">upstream app {
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

<p>Restart Nginx to put the changes into effect:</p>
<pre class="code-pre "><code langs="">sudo service nginx restart
</code></pre>
<p>Now the production environment of your Rails application is accessible via your server's public IP address or FQDN. To access the Tasks controller that we created earlier, visit your application server in a web browser:</p>
<pre class="code-pre "><code langs="">http://<span class="highlight">server_public_IP</span>/tasks
</code></pre>
<p>You should see the same page that you saw the first time you tested your application, but now it's being served through Nginx and Puma.</p>

<h2 id="conclusion">Conclusion</h2>

<p>Congratulations! You have deployed the production environment of your Ruby on Rails application using Nginx and Puma.</p>

<p>If you are looking to improve your production Rails application deployment, you should check out our tutorial series on <a href="https://indiareads/community/tutorial_series/how-to-use-capistrano-to-automate-deployments">How To Use Capistrano to Automate Deployments</a>. The series is based on CentOS, but it should still be helpful in automating your deployments.</p>

    