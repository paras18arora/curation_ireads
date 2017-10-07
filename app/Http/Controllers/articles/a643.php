<link rel='stylesheet' href='digital_ocean.css'><br><img src=https://community-cdn-digitalocean-com.global.ssl.fastly.net/assets/tutorials/images/large/ruby_with_rbenv_tw.jpg?1427918534/> <br> 
      <h3 id="introduction">Introduction</h3>

<p>Ruby on Rails is an extremely popular open-source web framework that provides a great way to write web applications with Ruby.</p>

<p>This tutorial will show you how to install Ruby on Rails on Ubuntu 14.04, using rbenv. This will provide you with a solid environment for developing your Ruby on Rails applications. rbenv provides an easy way to install and manage various versions of Ruby, and it is simpler and less intrusive than RVM. This will help you ensure that the Ruby version you are developing against matches your production environment. </p>

<h2 id="prerequisites">Prerequisites</h2>

<p>Before installing rbenv, you must have access to a superuser account on an Ubuntu 14.04 server. Follow steps 1-3 of this tutorial, if you need help setting this up: <a href="https://indiareads/community/tutorials/initial-server-setup-with-ubuntu-14-04">Initial Server Setup on Ubuntu 14.04</a></p>

<p>When you have the prerequisites out of the way, let's move on to installing rbenv.</p>

<h2 id="install-rbenv">Install rbenv</h2>

<p>Let's install rbenv, which we will use to install and manage our Ruby installation.</p>

<p>First, update apt-get:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo apt-get update
</li></ul></code></pre>
<p>Install the rbenv and Ruby dependencies with apt-get:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo apt-get install git-core curl zlib1g-dev build-essential libssl-dev libreadline-dev libyaml-dev libsqlite3-dev sqlite3 libxml2-dev libxslt1-dev libcurl4-openssl-dev python-software-properties libffi-dev
</li></ul></code></pre>
<p>Now we are ready to install rbenv. The easiest way to do that is to run these commands, as the user that will be using Ruby:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">cd
</li><li class="line" prefix="$">git clone git://github.com/sstephenson/rbenv.git .rbenv
</li><li class="line" prefix="$">echo 'export PATH="$HOME/.rbenv/bin:$PATH"' >> ~/.bash_profile
</li><li class="line" prefix="$">echo 'eval "$(rbenv init -)"' >> ~/.bash_profile
</li><li class="line" prefix="$">
</li><li class="line" prefix="$">git clone git://github.com/sstephenson/ruby-build.git ~/.rbenv/plugins/ruby-build
</li><li class="line" prefix="$">echo 'export PATH="$HOME/.rbenv/plugins/ruby-build/bin:$PATH"' >> ~/.bash_profile
</li><li class="line" prefix="$">source ~/.bash_profile
</li></ul></code></pre>
<p><span class="note"><strong>Note:</strong> On Ubuntu Desktop, replace all occurrences <code>.bash_profile</code> in the above code block with <code>.bashrc</code>.<br /></span></p>

<p>This installs rbenv into your home directory, and sets the appropriate environment variables that will allow rbenv to the active version of Ruby.</p>

<p>Now we're ready to install Ruby.</p>

<h2 id="install-ruby">Install Ruby</h2>

<p>Before using rbenv, determine which version of Ruby that you want to install. We will install the latest version, at the time of this writing, Ruby 2.2.3. You can look up the latest version of Ruby by going to the <a href="https://www.ruby-lang.org/en/downloads/">Ruby Downloads page</a>.</p>

<p>As the user that will be using Ruby, install it with these commands:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">rbenv install -v <span class="highlight">2.2.3</span>
</li><li class="line" prefix="$">rbenv global <span class="highlight">2.2.3</span>
</li></ul></code></pre>
<p>The <code>global</code> sub-command sets the default version of Ruby that all of your shells will use. If you want to install and use a different version, simply run the rbenv commands with a different version number.</p>

<p>Verify that Ruby was installed properly with this command:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">ruby -v
</li></ul></code></pre>
<p>It is likely that you will not want Rubygems to generate local documentation for each gem that you install, as this process can be lengthy. To disable this, run this command:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">echo "gem: --no-document" > ~/.gemrc
</li></ul></code></pre>
<p>You will also want to install the bundler gem, to manage your application dependencies:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">gem install bundler
</li></ul></code></pre>
<p>Now that Ruby is installed, let's install Rails.</p>

<h2 id="install-rails">Install Rails</h2>

<p>As the same user, install Rails with this command (you may specify a specific version with the <code>-v</code> option):</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">gem install rails
</li></ul></code></pre>
<p>Whenever you install a new version of Ruby or a gem that provides commands, you should run the <code>rehash</code> sub-command. This will install <em>shims</em> for all Ruby executables known to rbenv, which will allow you to use the executables:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">rbenv rehash
</li></ul></code></pre>
<p>Verify that Rails has been installed properly by printing its version, with this command:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">rails -v
</li></ul></code></pre>
<p>If it installed properly, you will see the version of Rails that was installed.</p>

<h3 id="install-javascript-runtime">Install Javascript Runtime</h3>

<p>A few Rails features, such as the Asset Pipeline, depend on a Javascript runtime. We will install Node.js to provide this functionality.</p>

<p>Add the Node.js PPA to apt-get:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo add-apt-repository ppa:chris-lea/node.js
</li></ul></code></pre>
<p>Then update apt-get and install the Node.js package:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo apt-get update
</li><li class="line" prefix="$">sudo apt-get install nodejs
</li></ul></code></pre>
<p>Congratulations! Ruby on Rails is now installed on your system.</p>

<h2 id="optional-steps">Optional Steps</h2>

<p>If you're looking to improve your setup, here are a few suggestions:</p>

<h3 id="configure-git">Configure Git</h3>

<p>A good version control system is essential when coding applications. Follow the <a href="https://indiareads/community/tutorials/how-to-install-git-on-ubuntu-14-04#how-to-set-up-git">How To Set Up Git</a> section of the How To Install Git tutorial.</p>

<h3 id="install-a-database">Install a Database</h3>

<p>Rails uses sqlite3 as its default database, which may not meet the requirements of your application. You may want to install an RDBMS, such as MySQL or PostgreSQL, for this purpose.</p>

<p>For example, if you want to use MySQL as your database, install MySQL with apt-get:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo apt-get install mysql-server mysql-client libmysqlclient-dev
</li></ul></code></pre>
<p>Then install the <code>mysql2</code> gem, like this:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">gem install mysql2
</li></ul></code></pre>
<p>Now you can use MySQL with your Rails application. Be sure to configure MySQL and your Rails application properly.</p>

<h2 id="create-a-test-application-optional">Create a Test Application (Optional)</h2>

<p>If you want to make sure that your Ruby on Rails installation went smoothly, you can quickly create a test application to test it out. For simplicity, our test application will use sqlite3 for its database.</p>

<p>Create a new Rails application in your home directory:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">cd ~
</li><li class="line" prefix="$">rails new testapp
</li></ul></code></pre>
<p>Then move into the application's directory:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">cd testapp
</li></ul></code></pre>
<p>Create the sqlite3 database:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">rake db:create
</li></ul></code></pre>
<p>If you don't already know the public IP address of your server, look it up with this command:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">ip addr show eth0 | grep inet | awk '{ print $2; }' | sed 's/\/.*$//'
</li></ul></code></pre>
<p>Copy the IPv4 address to your clipboard, then use it with this command to start your Rails application (substitute the highlighted part with the IP address):</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">rails server --binding=<span class="highlight">server_public_IP</span>
</li></ul></code></pre>
<p>If it is working properly, your Rails application should be running on port 3000 of the public IP address of your server. Visit your Rails application by going there in a web browser:</p>
<pre class="code-pre "><code langs="">http://<span class="highlight">server_public_IP</span>:3000
</code></pre>
<p>If you see the Rails "Welcome aboard" page, your Ruby on Rails installation is working properly!</p>

<h2 id="conclusion">Conclusion</h2>

<p>You're now ready to start developing your new Ruby on Rails application. Good luck!</p>

    