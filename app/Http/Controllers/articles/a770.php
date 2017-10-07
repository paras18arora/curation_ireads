<link rel='stylesheet' href='digital_ocean.css'><br> <br> 
      <h3 id="introduction">Introduction</h3>

<p>Rails is an open source web application framework written in Ruby. It follows the <em>Convention over Configuration</em> philosophy by making assumptions that there is the 'best' way of doing things. This allows you to write less code while accomplishing more without having you go through endless config files.</p>

<p>Nginx is a high performance HTTP server, reverse proxy, and a load balancer known for its focus on concurrency, stability, scalability, and low memory consumption. Like Nginx, Puma is another extremely fast and concurrent web server with a very small memory footprint but built for Ruby web applications.</p>

<p>Capistrano is a remote server automation tool focusing mainly on Ruby web apps. It's used to reliably deploy web apps to any number of remote machines by scripting arbitrary workflows over SSH and automate common tasks such as asset pre-compilation and restarting the Rails server.</p>

<p>In this tutorial we'll install Ruby and Nginx on a IndiaReads Ubuntu Droplet and configure Puma and Capistrano in our web app. Nginx will be used to capture client requests and pass them over to the Puma web server running Rails. We'll use Capistrano to automate common deployment tasks, so every time we have to deploy a new version of our Rails app to the server, we can do that with a few simple commands.</p>

<h2 id="prerequisites">Prerequisites</h2>

<p>To follow this tutorial, you must have the following:</p>

<ul>
<li>Ubuntu 14.04 x64 Droplet</li>
<li>A non-root user named <code>deploy</code> with sudo privileges (<a href="https://indiareads/community/tutorials/initial-server-setup-with-ubuntu-14-04">Initial Server Setup with Ubuntu 14.04</a> explains how to set this up.)</li>
<li>Working Rails app hosted in a remote git repository that's ready to be deployed</li>
</ul>

<p>Optionally, for heightened security, you can disable root login via SSH and change the SSH port number as described in <a href="https://indiareads/community/tutorials/initial-server-setup-with-ubuntu-14-04">Initial Server Setup with Ubuntu 14.04</a>.</p>

<p><span class="warning"><strong>Warning:</strong> After disabling root login, make sure you can SSH to your Droplet as the <code>deploy</code> user and use <code>sudo</code> for this user <em>before</em> closing the root SSH session you opened to make these changes.<br /></span></p>

<p>All the commands in this tutorial should be run as the <code>deploy</code> user. If root access is required for the command, it will be preceded by <code>sudo</code>. </p>

<h2 id="step-1-—-installing-nginx">Step 1 — Installing Nginx</h2>

<p>Once the VPS is secure, we can start installing packages. Update the package index files:</p>
<pre class="code-pre custom_prefix"><code langs=""><ul class="prefixed"><li class="line" prefix="deploy@droplet:~$">sudo apt-get update
</li></ul></code></pre>
<p>Then, install Nginx:</p>
<pre class="code-pre custom_prefix"><code langs=""><ul class="prefixed"><li class="line" prefix="deploy@droplet:~$">sudo apt-get install curl git-core nginx -y
</li></ul></code></pre>
<h2 id="step-2-—-installing-databases">Step 2 — Installing Databases</h2>

<p>Install the database that you'll be using in your Rails app. Since there are lots of databases to choose from, we won't cover them in this guide. You can see instructions for major ones here:</p>

<ul>
<li><a href="https://indiareads/community/tutorials/a-basic-mysql-tutorial">MySQL</a></li>
<li><a href="https://indiareads/community/tutorials/how-to-install-and-use-postgresql-on-ubuntu-14-04">PostgreSQL</a></li>
<li><a href="https://indiareads/community/tutorials/how-to-install-mongodb-on-ubuntu-12-04">MongoDB</a></li>
</ul>

<p>Also be sure to check out:</p>

<ul>
<li> <a href="https://indiareads/community/tutorials/how-to-use-mysql-with-your-ruby-on-rails-application-on-ubuntu-14-04">How To Use MySQL with Your Ruby on Rails Application on Ubuntu 14.04</a></li>
<li> <a href="https://indiareads/community/tutorials/how-to-use-postgresql-with-your-ruby-on-rails-application-on-ubuntu-14-04">How To Use PostgreSQL with Your Ruby on Rails Application on Ubuntu 14.04</a></li>
</ul>

<h2 id="step-3-—-installing-rvm-and-ruby">Step 3 — Installing RVM and Ruby</h2>

<p>We won't be installing Ruby directly. Instead, we'll use a Ruby Version Manager. There are lots of them to choose from (rbenv, chruby, etc.), but we'll use RVM for this tutorial. RVM allows you to easily install and manage multiple rubies on the same system and use the correct one according to your app. This makes life much easier when you have to upgrade your Rails app to use a newer ruby.</p>

<p>Before installing RVM, you need to import the RVM GPG Key:</p>
<pre class="code-pre custom_prefix"><code langs=""><ul class="prefixed"><li class="line" prefix="deploy@droplet:~$">gpg --keyserver hkp://keys.gnupg.net --recv-keys 409B6B1796C275462A1703113804BB82D39DC0E3
</li></ul></code></pre>
<p>Then install RVM to manage our Rubies:</p>
<pre class="code-pre custom_prefix"><code langs=""><ul class="prefixed"><li class="line" prefix="deploy@droplet:~$">curl -sSL https://get.rvm.io | bash -s stable
</li></ul></code></pre>
<p>This command uses <code>curl</code> to download the RVM Installation script from <code>https://get.rvm.io</code>. The <code>-sSL</code> option is composed of three flags: </p>

<ul>
<li><code>-s</code> tells curl to download the file in 'silent mode'</li>
<li><code>-S</code> tells curl to show an error message if it fails</li>
<li><code>-L</code> tells curl to follow all HTTP redirects while retrieving the installation script</li>
</ul>

<p>Once downloaded, the script is <em>piped</em> to <code>bash</code>. The <code>-s</code> option passes <code>stable</code> as an argument to the RVM Installation script to download and install the stable release of RVM.</p>

<p><span class="note"><strong>Note:</strong> If the second command fails with the message "GPG signature verification failed", that means the GPG Key has changed, simply copy the command from the error output and run it to download the signatures. Then run the curl command for the RVM Installation.<br /></span></p>

<p>We need to load the RVM script (as a function) so we can start using it. We then need to run the <code>requirements</code> command to automatically install required dependencies and files for RVM and Ruby to function properly:</p>
<pre class="code-pre custom_prefix"><code langs=""><ul class="prefixed"><li class="line" prefix="deploy@droplet:~$">source ~/.rvm/scripts/rvm
</li><li class="line" prefix="deploy@droplet:~$">rvm requirements
</li></ul></code></pre>
<p>We can now install the Ruby of our choice. We'll be installing the latest <code>Ruby 2.2.1</code> (at the time of writing) as our default Ruby:</p>
<pre class="code-pre custom_prefix"><code langs=""><ul class="prefixed"><li class="line" prefix="deploy@droplet:~$">rvm install 2.2.1
</li><li class="line" prefix="deploy@droplet:~$">rvm use 2.2.1 --default
</li></ul></code></pre>
<h2 id="step-4-—-installing-rails-and-bundler">Step 4 — Installing Rails and Bundler</h2>

<p>Once Ruby is set up, we can start installing Rubygems. We'll start by installing the Rails gem that will allow your Rails application to run, and then we'll install <code>bundler</code> which can read your app's <code>Gemfile</code> and automatically install all required gems.  </p>

<p>To install Rails and Bundler:</p>
<pre class="code-pre custom_prefix"><code langs=""><ul class="prefixed"><li class="line" prefix="deploy@droplet:~$">gem install rails -V --no-ri --no-rdoc
</li><li class="line" prefix="deploy@droplet:~$">gem install bundler -V --no-ri --no-rdoc
</li></ul></code></pre>
<p>Three flags were used:</p>

<ul>
<li><code>-V</code> (Verbose Output): Prints detailed information about Gem installation</li>
<li><code>--no-ri</code> - (Skips Ri Documentation): Doesn't install Ri Docs, saving space and making installation fast</li>
<li><code>--no-rdoc</code> - (Skips RDocs): Doesn't install RDocs, saving space and speeding up installation</li>
</ul>

<span class="note"><p>
<strong>Note:</strong> You can also install a specific version of Rails according to your requirements by using the <code>-v</code> flag:</p>
<pre class="code-pre custom_prefix"><code langs=""><ul class="prefixed"><li class="line" prefix="deploy@droplet:~$">gem install rails <span class="highlight">-v '4.2.0'</span> -V --no-ri --no-rdoc
</li></ul></code></pre>
<p></p></span>

<h2 id="step-5-—-setting-up-ssh-keys">Step 5 — Setting up SSH Keys</h2>

<p>Since we want to set up smooth deployments, we'll be using SSH Keys for authorization. First shake hands with GitHub, Bitbucket, or any other Git Remote where the codebase for your Rails app is hosted:</p>
<pre class="code-pre custom_prefix"><code langs=""><ul class="prefixed"><li class="line" prefix="deploy@droplet:~$">ssh -T git@github.com
</li><li class="line" prefix="deploy@droplet:~$">ssh -T git@bitbucket.org
</li></ul></code></pre>
<p>Don't worry if you get a <code>Permission denied (publickey)</code> message. Now, generate a SSH key (a Public/Private Key Pair) for your server:</p>
<pre class="code-pre custom_prefix"><code langs=""><ul class="prefixed"><li class="line" prefix="deploy@droplet:~$">ssh-keygen -t rsa 
</li></ul></code></pre>
<p>Add the newly created public key (<code>~/.ssh/id_rsa.pub</code>) to your repository's deployment keys:</p>

<ul>
<li><a href="https://developer.github.com/guides/managing-deploy-keys/">Instructions for Github</a></li>
<li><a href="https://confluence.atlassian.com/display/BITBUCKET/Use+deployment+keys">Instructions for Bitbucket</a></li>
</ul>

<p>If all the steps were completed correctly, you should now be able to <code>clone</code> your git repository (over the SSH Protocol, not HTTP) without entering your password:</p>
<pre class="code-pre custom_prefix"><code langs=""><ul class="prefixed"><li class="line" prefix="deploy@droplet:~$">git clone <span class="highlight">git@example.com:username/appname.git</span>
</li></ul></code></pre>
<p>If you need a sample app for testing, you can fork the following test app specifically created for this tutorial: <a href="https://github.com/sheharyarn/testapp_rails">Sample Rails App on GitHub</a></p>

<p>The <code>git clone</code> command will create a directory with the same name as your app. For example, a directory named <code>testapp_rails</code> will be created. </p>

<p>We are cloning only to check if our deployment keys are working, we don't need to clone or pull our repository every time we push new changes. We'll let Capistrano handle all that for us. You can now delete this cloned directory if you want to.</p>

<p>Open a terminal on your local machine. If you don't have a SSH Key for your local computer, create one for it as well. In your local terminal session:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">ssh-keygen -t rsa 
</li></ul></code></pre>
<p>Add your local SSH Key to your Droplet's <em>Authorized Keys</em> file (remember to replace the port number with your customized port number):</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">cat ~/.ssh/id_rsa.pub | ssh -p <span class="highlight">your_port_num</span> deploy@<span class="highlight">your_server_ip</span> 'cat >> ~/.ssh/authorized_keys'
</li></ul></code></pre>
<h2 id="step-6-—-adding-deployment-configurations-in-the-rails-app">Step 6 — Adding Deployment Configurations in the Rails App</h2>

<p>On your local machine, create configuration files for Nginx and Capistrano in your Rails application. Start by adding these lines to the <code>Gemfile</code> in the Rails App:</p>
<div class="code-label " title="Gemfile">Gemfile</div><pre class="code-pre "><code langs="">
group :development do
    gem 'capistrano',         require: false
    gem 'capistrano-rvm',     require: false
    gem 'capistrano-rails',   require: false
    gem 'capistrano-bundler', require: false
    gem 'capistrano3-puma',   require: false
end

gem 'puma'
</code></pre>
<p>Use <code>bundler</code> to install the gems you just specified in your <code>Gemfile</code>. Enter the following command to bundle your Rails app:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">bundle
</li></ul></code></pre>
<p>After bundling, run the following command to configure Capistrano:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">cap install
</li></ul></code></pre>
<p>This will create:</p>

<ul>
<li><code>Capfile</code> in the root directory of your Rails app</li>
<li><code>deploy.rb</code> file in the <code>config</code> directory</li>
<li><code>deploy</code> directory in the <code>config</code> directory</li>
</ul>

<p>Replace the contents of your <code>Capfile</code> with the following:</p>
<div class="code-label " title="Capfile">Capfile</div><pre class="code-pre "><code langs=""># Load DSL and Setup Up Stages
require 'capistrano/setup'
require 'capistrano/deploy'

require 'capistrano/rails'
require 'capistrano/bundler'
require 'capistrano/rvm'
require 'capistrano/puma'

# Loads custom tasks from `lib/capistrano/tasks' if you have any defined.
Dir.glob('lib/capistrano/tasks/*.rake').each { |r| import r }
</code></pre>
<p>This <code>Capfile</code> loads some pre-defined tasks in to your Capistrano configuration files to make your deployments hassle-free, such as automatically:</p>

<ul>
<li>Selecting the correct Ruby</li>
<li>Pre-compiling Assets</li>
<li>Cloning your Git repository to the correct location</li>
<li>Installing new dependencies when your Gemfile has changed </li>
</ul>

<p>Replace the contents of <code>config/deploy.rb</code> with the following, updating fields marked in red with your app and Droplet parameters: </p>
<div class="code-label " title="config/deploy.rb">config/deploy.rb</div><pre class="code-pre "><code langs="">
# Change these
server <span class="highlight">'your_server_ip'</span>, port: <span class="highlight">your_port_num</span>, roles: [:web, :app, :db], primary: true

set :repo_url,        <span class="highlight">'git@example.com:username/appname.git'</span>
set :application,     <span class="highlight">'appname'</span>
set :user,            <span class="highlight">'deploy'</span>
set :puma_threads,    [4, 16]
set :puma_workers,    0

# Don't change these unless you know what you're doing
set :pty,             true
set :use_sudo,        false
set :stage,           :production
set :deploy_via,      :remote_cache
set :deploy_to,       "/home/#{fetch(:user)}/apps/#{fetch(:application)}"
set :puma_bind,       "unix://#{shared_path}/tmp/sockets/#{fetch(:application)}-puma.sock"
set :puma_state,      "#{shared_path}/tmp/pids/puma.state"
set :puma_pid,        "#{shared_path}/tmp/pids/puma.pid"
set :puma_access_log, "#{release_path}/log/puma.error.log"
set :puma_error_log,  "#{release_path}/log/puma.access.log"
set :ssh_options,     { forward_agent: true, user: fetch(:user), keys: %w(~/.ssh/id_rsa.pub) }
set :puma_preload_app, true
set :puma_worker_timeout, nil
set :puma_init_active_record, true  # Change to false when not using ActiveRecord

## Defaults:
# set :scm,           :git
# set :branch,        :master
# set :format,        :pretty
# set :log_level,     :debug
# set :keep_releases, 5

## Linked Files & Directories (Default None):
# set :linked_files, %w{config/database.yml}
# set :linked_dirs,  %w{bin log tmp/pids tmp/cache tmp/sockets vendor/bundle public/system}

namespace :puma do
  desc 'Create Directories for Puma Pids and Socket'
  task :make_dirs do
    on roles(:app) do
      execute "mkdir #{shared_path}/tmp/sockets -p"
      execute "mkdir #{shared_path}/tmp/pids -p"
    end
  end

  before :start, :make_dirs
end

namespace :deploy do
  desc "Make sure local git is in sync with remote."
  task :check_revision do
    on roles(:app) do
      unless `git rev-parse HEAD` == `git rev-parse origin/master`
        puts "WARNING: HEAD is not the same as origin/master"
        puts "Run `git push` to sync changes."
        exit
      end
    end
  end

  desc 'Initial Deploy'
  task :initial do
    on roles(:app) do
      before 'deploy:restart', 'puma:start'
      invoke 'deploy'
    end
  end

  desc 'Restart application'
  task :restart do
    on roles(:app), in: :sequence, wait: 5 do
      invoke 'puma:restart'
    end
  end

  before :starting,     :check_revision
  after  :finishing,    :compile_assets
  after  :finishing,    :cleanup
  after  :finishing,    :restart
end

# ps aux | grep puma    # Get puma pid
# kill -s SIGUSR2 pid   # Restart puma
# kill -s SIGTERM pid   # Stop puma
</code></pre>
<p>This <code>deploy.rb</code> file contains some sane defaults that work out-of-the-box to help you manage your app releases and automatically perform some tasks when you make a deployment:</p>

<ul>
<li>Uses <code>production</code> as the default environment for your Rails app</li>
<li>Automatically manages multiple releases of your app</li>
<li>Uses optimized SSH options</li>
<li>Checks if your git remotes are up to date</li>
<li>Manages your app's logs</li>
<li>Preloads the app in memory when managing Puma workers</li>
<li>Starts (or restarts) the Puma server after finishing a deployment</li>
<li>Opens a socket to the Puma server at a specific location in your release</li>
</ul>

<p>You can change all options depending on your requirements. Now, Nginx needs to be configured. Create <code>config/nginx.conf</code> in your Rails project directory, and add the following to it (again, replacing with your parameters):</p>
<div class="code-label " title="config/nginx.conf">config/nginx.conf</div><pre class="code-pre "><code langs="">
upstream puma {
  server unix:///home/<span class="highlight">deploy</span>/apps/<span class="highlight">appname</span>/shared/tmp/sockets/<span class="highlight">appname</span>-puma.sock;
}

server {
  listen 80 default_server deferred;
  # server_name example.com;

  root /home/<span class="highlight">deploy</span>/apps/<span class="highlight">appname</span>/current/public;
  access_log /home/<span class="highlight">deploy</span>/apps/<span class="highlight">appname</span>/current/log/nginx.access.log;
  error_log /home/<span class="highlight">deploy</span>/apps/<span class="highlight">appname</span>/current/log/nginx.error.log info;

  location ^~ /assets/ {
    gzip_static on;
    expires max;
    add_header Cache-Control public;
  }

  try_files $uri/index.html $uri @puma;
  location @puma {
    proxy_set_header X-Forwarded-For $proxy_add_x_forwarded_for;
    proxy_set_header Host $http_host;
    proxy_redirect off;

    proxy_pass http://puma;
  }

  error_page 500 502 503 504 /500.html;
  client_max_body_size 10M;
  keepalive_timeout 10;
}
</code></pre>
<p>Like the previous file, this <code>nginx.conf</code> contains defaults that work out-of-the-box with the configurations in your <code>deploy.rb</code> file. This listens for traffic on port 80 and passes on the request to your Puma socket, writes nginx logs to the 'current' release of your app, compresses all assets and caches them in the browser with maximum expiry, serves the HTML pages in the public folder as static files, and sets default maximum <code>Client Body Size</code> and <code>Request Timeout</code> values. </p>

<h2 id="step-7-—-deploying-your-rails-application">Step 7 — Deploying your Rails Application</h2>

<p>If you are using your own Rails app, commit the changes you just made, and push them to remote from your Local Machine:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">git add -A
</li><li class="line" prefix="$">git commit -m "Set up Puma, Nginx & Capistrano"
</li><li class="line" prefix="$">git push origin master
</li></ul></code></pre>
<span class="note"><p>
<strong>Note:</strong> If this is the first time using GitHub from this system, you might have to issue the following commands with your GitHub username and email address:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">git config --global user.name 'Your Name'
</li><li class="line" prefix="$">git config --global user.email you@example.com
</li></ul></code></pre>
<p></p></span>

<p>Again, from your local machine, make your first deployment:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">cap production deploy:initial
</li></ul></code></pre>
<p>This will push your Rails app to the Droplet, install all required gems for your app, and start the Puma web server. This may take anywhere between 5-15 minutes depending on the number of Gems your app uses. You will see debug messages as this process occurs.</p>

<p>If everything goes smoothly, we're now ready to connect your Puma web server to the Nginx reverse proxy.</p>

<p>On the Droplet, Symlink the <code>nginx.conf</code> to the <code>sites-enabled</code> directory:</p>
<pre class="code-pre custom_prefix"><code langs=""><ul class="prefixed"><li class="line" prefix="deploy@droplet:~$">sudo rm /etc/nginx/sites-enabled/default
</li><li class="line" prefix="deploy@droplet:~$">sudo ln -nfs "/home/<span class="highlight">deploy</span>/apps/<span class="highlight">appname</span>/current/config/nginx.conf" "/etc/nginx/sites-enabled/<span class="highlight">appname</span>"
</li></ul></code></pre>
<p>Restart the Nginx service:</p>
<pre class="code-pre custom_prefix"><code langs=""><ul class="prefixed"><li class="line" prefix="deploy@droplet:~$">sudo service nginx restart
</li></ul></code></pre>
<p>You should now be able to point your web browser to your server IP and see your Rails app in action!</p>

<h2 id="normal-deployments">Normal Deployments</h2>

<p>Whenever you make changes to your app and want to deploy a new release to the server, commit the changes, push to your git remote like usual, and run the <code>deploy</code> command:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">git add -A
</li><li class="line" prefix="$">git commit -m "Deploy Message"
</li><li class="line" prefix="$">git push origin master
</li><li class="line" prefix="$">cap production deploy
</li></ul></code></pre>
<span class="note"><p>
<strong>Note:</strong> If you make changes to your <code>config/nginx.conf</code> file, you'll have to reload or restart your Nginx service on the server after deploying your app:</p>
<pre class="code-pre custom_prefix"><code langs=""><ul class="prefixed"><li class="line" prefix="deploy@droplet:~$">sudo service nginx restart
</li></ul></code></pre>
<p></p></span>

<h2 id="conclusion">Conclusion</h2>

<p>Okay, so by now you would be running a Rails app on your Droplet with Puma as your Web Server as well as Nginx and Capistrano configured with basic settings. You should now take a look at other docs that can help you optimize your configurations to get the maximum out of your Rails application:</p>

<ul>
<li><a href="https://github.com/puma/puma/">Puma Configurations</a> </li>
<li><a href="https://github.com/seuros/capistrano-puma">Puma DSL in Capistrano</a></li>
<li><a href="https://github.com/stve/capistrano-local-precompile">Local Asset Pre-compilation in Capistrano</a></li>
<li><a href="https://github.com/schneems/puma_worker_killer">Automatically restart Puma workers based on RAM</a></li>
<li><a href="https://blog.martinfjordvald.com/2011/04/optimizing-nginx-for-high-traffic-loads/">Optimizing Nginx for High Traffic Loads</a></li>
</ul>

    