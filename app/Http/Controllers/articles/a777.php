<link rel='stylesheet' href='digital_ocean.css'><br><img src=https://community-cdn-digitalocean-com.global.ssl.fastly.net/assets/tutorials/images/large/RubyonRails_twitter.png?1459466602/> <br> 
      <h3 id="introduction">Introduction</h3>

<p>Ruby on Rails is an extremely popular open-source web framework that provides a great way to write web applications with Ruby.</p>

<p>This tutorial will show you how to install Ruby on Rails on CentOS 7, using rbenv. This will provide you with a solid environment for developing your Ruby on Rails applications. rbenv provides an easy way to install and manage various versions of Ruby, and it is simpler and less intrusive than <a href="https://indiareads/community/tutorials/how-to-install-ruby-on-rails-on-centos-6-with-rvm">RVM</a>. This will help you ensure that the Ruby version you are developing against matches your production environment. </p>

<h2 id="prerequisites">Prerequisites</h2>

<p>Before installing rbenv, you must have access to a superuser account on a CentOS 7 server. Follow steps 1-3 of this tutorial, if you need help setting this up: <a href="https://indiareads/community/tutorials/initial-server-setup-with-centos-7">Initial Server Setup with CentOS 7</a>.</p>

<p>When you have the prerequisites out of the way, let's move on to installing rbenv.</p>

<h2 id="install-rbenv">Install rbenv</h2>

<p>Let's install rbenv, which we will use to install and manage our Ruby installation.</p>

<p>Install the rbenv and Ruby dependencies with yum:</p>
<pre class="code-pre "><code langs="">sudo yum install -y git-core zlib zlib-devel gcc-c++ patch readline readline-devel libyaml-devel libffi-devel openssl-devel make bzip2 autoconf automake libtool bison curl sqlite-devel
</code></pre>
<p>Now we are ready to install rbenv. The easiest way to do that is to run these commands, as the user that will be using Ruby:</p>
<pre class="code-pre "><code langs="">cd
git clone git://github.com/sstephenson/rbenv.git .rbenv
echo 'export PATH="$HOME/.rbenv/bin:$PATH"' >> ~/.bash_profile
echo 'eval "$(rbenv init -)"' >> ~/.bash_profile
exec $SHELL

git clone git://github.com/sstephenson/ruby-build.git ~/.rbenv/plugins/ruby-build
echo 'export PATH="$HOME/.rbenv/plugins/ruby-build/bin:$PATH"' >> ~/.bash_profile
exec $SHELL
</code></pre>
<p>This installs rbenv into your home directory, and sets the appropriate environment variables that will allow rbenv to the active version of Ruby.</p>

<p>Now we're ready to install Ruby.</p>

<h2 id="install-ruby">Install Ruby</h2>

<p>Before using rbenv, determine which version of Ruby that you want to install. We will install the latest version, Ruby 2.2.1.</p>

<p>As the user that will be using Ruby, install it with these commands:</p>
<pre class="code-pre "><code langs="">rbenv install -v 2.2.1
rbenv global 2.2.1
</code></pre>
<p>The <code>global</code> sub-command sets the default version of Ruby that all of your shells will use. If you want to install and use a different version, simply run the rbenv commands with a different version number.</p>

<p>Verify that Ruby was installed properly with this command:</p>
<pre class="code-pre "><code langs="">ruby -v
</code></pre>
<p>It is likely that you will not want Rubygems to generate local documentation for each gem that you install, as this process can be lengthy. To disable this, run this command:</p>
<pre class="code-pre "><code langs="">echo "gem: --no-document" > ~/.gemrc
</code></pre>
<p>You will also want to install the bundler gem, to manage your application dependencies:</p>
<pre class="code-pre "><code langs="">gem install bundler
</code></pre>
<p>Now that Ruby is installed, let's install Rails.</p>

<h2 id="install-rails">Install Rails</h2>

<p>As the same user, install Rails 4.2.0 with this command:</p>
<pre class="code-pre "><code langs="">gem install rails -v 4.2.0
</code></pre>
<p>Whenever you install a new version of Ruby or a gem that provides commands, you should run the <code>rehash</code> sub-command. This will install <em>shims</em> for all Ruby executables known to rbenv, which will allow you to use the executables:</p>
<pre class="code-pre "><code langs="">rbenv rehash
</code></pre>
<p>Verify that Rails has been installed properly by printing its version, with this command:</p>
<pre class="code-pre "><code langs="">rails -v
</code></pre>
<p>If it installed properly, you will see this output: <code>Rails 4.2.0</code>.</p>

<h3 id="install-javascript-runtime">Install Javascript Runtime</h3>

<p>A few Rails features, such as the Asset Pipeline, depend on a Javascript runtime. We will install Node.js to provide this functionality.</p>

<p>Add the EPEL yum repository:</p>
<pre class="code-pre "><code langs="">sudo yum -y install epel-release
</code></pre>
<p>Then install the Node.js package:</p>
<pre class="code-pre "><code langs="">sudo yum install nodejs
</code></pre>
<p><strong>Note:</strong> This will probably not install the latest release of Node.js, as Enterprise Linux does not consider it to be "stable". If you want to install the latest version, feel free to build it on your own.</p>

<p>Congratulations! Ruby on Rails is now installed on your system.</p>

<h2 id="optional-steps">Optional Steps</h2>

<p>If you're looking to improve your setup, here are a few suggestions:</p>

<h3 id="configure-git">Configure Git</h3>

<p>A good version control system is essential when coding applications. Follow the <a href="https://indiareads/community/tutorials/how-to-install-git-on-centos-7#set-up-git">How To Set Up Git</a> section of the How To Install Git tutorial.</p>

<h3 id="install-a-database">Install a Database</h3>

<p>Rails uses sqlite3 as its default database, which may not meet the requirements of your application. You may want to install an RDBMS, such as MySQL or PostgreSQL, for this purpose.</p>

<p>For example, if you want to use MariaDB as your database, install it with yum:</p>
<pre class="code-pre "><code langs="">sudo yum install mariadb-server mariadb-devel
</code></pre>
<p>Then install the <code>mysql2</code> gem, like this:</p>
<pre class="code-pre "><code langs="">gem install mysql2
</code></pre>
<p>Now you can use MariaDB with your Rails application. Be sure to configure MariaDB and your Rails application properly.</p>

<h2 id="create-a-test-application-optional">Create a Test Application (Optional)</h2>

<p>If you want to make sure that your Ruby on Rails installation went smoothly, you can quickly create a test application to test it out. For simplicity, our test application will use sqlite3 for its database.</p>

<p>Create a new Rails application in your home directory:</p>
<pre class="code-pre "><code langs="">cd ~
rails new testapp
</code></pre>
<p>Then move into the application's directory:</p>
<pre class="code-pre "><code langs="">cd testapp
</code></pre>
<p>Create the sqlite3 database:</p>
<pre class="code-pre "><code langs="">rake db:create
</code></pre>
<p>If you don't already know the public IP address of your server, look it up with this command:</p>
<pre class="code-pre "><code langs="">ip addr show eth0 | grep inet | awk '{ print $2; }' | sed 's/\/.*$//'
</code></pre>
<p>Copy the IPv4 address to your clipboard, then use it with this command to start your Rails application (substitute the highlighted part with the IP address):</p>
<pre class="code-pre "><code langs="">rails server --binding=<span class="highlight">server_public_IP</span>
</code></pre>
<p>If it is working properly, your Rails application should be running on port 3000 of the public IP address of your server. Visit your Rails application by going there in a web browser:</p>
<pre class="code-pre "><code langs="">http://<span class="highlight">server_public_IP</span>:3000
</code></pre>
<p>If you see the Rails "Welcome aboard" page, your Ruby on Rails installation is working properly!</p>

<h2 id="conclusion">Conclusion</h2>

<p>You're now ready to start developing your new Ruby on Rails application. Good luck!</p>

    