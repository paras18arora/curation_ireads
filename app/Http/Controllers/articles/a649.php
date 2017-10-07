<link rel='stylesheet' href='digital_ocean.css'><br> <br> 
      <h3 id="introduction">Introduction</h3>

<p>Ruby on Rails is an open source web framework based on Ruby. It has been a popular choice among startups since it's easier to build and ship the application. To start using Ruby on Rails, you need to have Ruby installed. However, due to the way Debian packaging system works, you'll mostly end up with an old version of Ruby. So, this guide will show you a safe way for you to use the latest version of Ruby and Ruby on Rails.</p>

<h2 id="install-rbenv">Install rbenv</h2>

<p>We need to install some packages so that Debian won't complain about missing files or libraries. We'll use Debian packaging system for that. </p>

<p>First we need to sure we have all the latest packages that can be installed:</p>
<pre class="code-pre "><code langs="">sudo apt-get update
</code></pre>
<p>Next, we'll install git package so that we can use git commands:</p>
<pre class="code-pre "><code langs="">sudo apt-get install git-core
</code></pre>
<p>rbenv doesn't provide any installer, so we have to get use the source from Github. So, we need to clone it in our home directory.</p>
<pre class="code-pre "><code langs="">git clone https://github.com/sstephenson/rbenv.git ~/.rbenv
</code></pre>
<p>PATH is a variable used by Debian to search for executables whenever you run a command. So, we have to append path to rbenv executables in it. We have to do this every time we login as our user. So, we'll use bashrc that will be run every time we login as a user:</p>
<pre class="code-pre "><code langs="">echo 'export PATH="$HOME/.rbenv/bin:$PATH"' >> ~/.bashrc
</code></pre>
<p>Add another command in our bashrc for shims[1] and auto completion for rbenv:</p>
<pre class="code-pre "><code langs="">echo 'eval "$(rbenv init -)"' >> ~/.bashrc
</code></pre>
<p>Restart your shell (opening a new terminal tab should suffice). To check if everything is working, run:</p>
<pre class="code-pre "><code langs="">type rbenv
</code></pre>
<p>You should get an output like this:</p>
<pre class="code-pre "><code langs="">rbenv is a function
</code></pre>
<p>That's it. You've just successfully installed rbenv. But we're not done yet. To simplify ruby installation, we need to install ruby-build which is one of the rbenv plugins, by cloning it from Github:</p>
<pre class="code-pre "><code langs="">git clone https://github.com/sstephenson/ruby-build.git ~/.rbenv/plugins/ruby-build
</code></pre>
<p>Due to the way shims work, we need to run <code>rbenv rehash</code> every time we install or uninstall gem. To prevent it, we can use another rbenv plugin which will automatically do it for us. As usual, we'll clone it for installation</p>
<pre class="code-pre "><code langs="">git clone https://github.com/sstephenson/rbenv-gem-rehash.git ~/.rbenv/plugins/rbenv-gem-rehash
</code></pre>
<h2 id="install-ruby">Install ruby</h2>

<p>Alright, everything is done for our rbenv installation. Next, we will install a ruby. But before that, we need to install some required packages to ensure smooth installation.</p>
<pre class="code-pre "><code langs="">apt-get install build-essential libssl-dev libcurl4-openssl-dev libreadline-dev -y
</code></pre>
<p>In order to list all available Ruby versions for you to choose, we can use:</p>
<pre class="code-pre "><code langs="">rbenv install --list
</code></pre>
<p>I'll just use version 2.1.0 for this guide. To install it, just run this command:</p>
<pre class="code-pre "><code langs="">rbenv install 2.1.0 -k
</code></pre>
<p><code>-k</code> will keep Ruby's source. It will help building other gems in the future. </p>

<p>Right now, we need to set which version we want to use every time we run <code>ruby</code> command. Make sure you will remove any directory in your <code>~/.rbenv/sources</code> if you encounter any problems for this command.</p>

<p>To set it for global usage, just run:</p>
<pre class="code-pre "><code langs="">rbenv global 2.1.0
</code></pre>
<p>That's it, now you have ruby 2.1.0 installed. You can verify it by running:</p>
<pre class="code-pre "><code langs="">ruby -v
</code></pre>
<p>You should get something like this (depends on your version):</p>
<pre class="code-pre "><code langs="">ruby 2.1.0p0 (2013-12-25 revision 44422) [x86_64-linux]
</code></pre>
<h2 id="ruby-on-rails-installation">Ruby on Rails Installation</h2>

<p>Ruby on Rails (RoR) provides a command for your initial application creation. The command will setup your directories, gem dependencies and so on. Before running it, we need to ensure every required packages are installed.</p>

<p>By default, RoR will use sqlite as its database. In order to use it without any error, we need sqlite packages for Debian:</p>
<pre class="code-pre "><code langs="">sudo apt-get install sqlite3 libsqlite3-dev
</code></pre>
<p>RoR will also requires JavaScript runtime[2]. There are multiple ways to do this, but we'll use nodejs from Debian Backports[3] to install it. </p>

<p>To use it, we need to add Debian Backports repositories:</p>

<p>Open the file where your Debian repositories are defined:</p>
<pre class="code-pre "><code langs="">sudo nano /etc/apt/sources.list
</code></pre>
<p>Append these lines into the file:</p>
<pre class="code-pre "><code langs="">deb http://ftp.us.debian.org/debian/ wheezy-backports main
deb-src http://ftp.us.debian.org/debian/ wheezy-backports main
</code></pre>
<p>As usual, run this command to make sure you'll get all of the packages list:</p>
<pre class="code-pre "><code langs="">sudo apt-get update
</code></pre>
<p>To install nodejs, just run this command:</p>
<pre class="code-pre "><code langs="">sudo apt-get -t wheezy-backports install nodejs
</code></pre>
<p>Go ahead and install Ruby on Rails with this command:</p>
<pre class="code-pre "><code langs="">gem install rails
</code></pre>
<h2 id="out-first-ruby-on-rails-application">Out First Ruby on Rails Application</h2>

<p>We're now ready to create our first RoR application. Run this command to create it (you can change mynewapplication to anything you want):</p>
<pre class="code-pre "><code langs="">rails new mynewapplication
</code></pre>
<p>It will automatically install required gems. After it's finished, go into its directory:</p>
<pre class="code-pre "><code langs="">cd mynewapplication
</code></pre>
<p>Run this command to start your application</p>
<pre class="code-pre "><code langs="">rails s
</code></pre>
<p>Fire up your browser and go to this address:</p>
<pre class="code-pre "><code langs="">http://your_ip_or_domain:3000
</code></pre>
<p>You should be seeing a Welcome aboard page. Click "About your application's environment" and you should see something like below.</p>

<p><img src="https://assets.digitalocean.com/tutorial_images/kyHpGCY.jpg" alt="Screenshot" /></p>

<p>Congratulations! You've successfully installed and created your first RoR application.</p>

<p>[1]</p>

<p>https://assets.digitalocean.com/articles/Debian<em>Ruby</em>rbenv/WelcomeAboard.jpg</p>

<p>[2] </p>

<p>https://github.com/sstephenson/execjs#readme</p>

<p>[3] </p>

<p>http://backports.debian.org/</p>

    