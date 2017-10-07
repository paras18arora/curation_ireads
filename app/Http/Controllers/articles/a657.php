<link rel='stylesheet' href='digital_ocean.css'><br> <br> 
      <h3 id="introduction">Introduction</h3>

<hr />

<p>If a couple words were chosen to define Sinatra, they would most certainly be <em>inspirational</em> and <em>concise</em>. This tiny but remarkable little project led the way to the creation of many other similar ones – across different programming languages and platforms.</p>

<p>The "classy" web-development library Sinatra can allow you to quickly build web applications from scratch. Unlike the ever-so-popular Ruby on Rails framework, applications created on Sinatra can consist of a single file, solely depending on the Sinatra gem. </p>

<p>In this IndiaReads article, we are going to learn how to install the latest available version of the official Ruby interpreter (v 2.1.0) on a Ubuntu 13 droplet along with Sinatra web-application development library. Afterwards, we will create a sample project and continue with real-world deployments.</p>

<h2 id="glossary">Glossary</h2>

<hr />

<h3 id="1-installing-ruby-and-sinatra">1. Installing Ruby And Sinatra</h3>

<hr />

<ol>
<li>Updating The Operating-System</li>
<li>Getting The Essential Build/Development Tools</li>
<li>Installing Ruby Version Manager (RVM)</li>
<li>Installing Ruby 2.1.0</li>
<li>Installing Sinatra</li>
<li>Creating A www User Group And A deployer User</li>
</ol>

<h3 id="2-creating-a-quot-hello-world-quot-application-with-sinatra">2. Creating A "Hello world!"Application With Sinatra</h3>

<hr />

<ol>
<li>Application Directory</li>
<li>Sample Application Files</li>
<li>Testing The Application (rackup)</li>
</ol>

<h3 id="3-deployments">3. Deployments</h3>

<hr />

<p><strong>Note:</strong> This article is the first one of our two-piece Sinatra series. After finishing this one, to learn about actual deployments check out <a href="https://indiareads/community/articles/how-to-deploy-sinatra-based-ruby-web-applications-on-ubuntu-13">How To Deploy Sinatra Based Ruby Web-Applications</a>.</p>

<h2 id="installing-ruby-and-sinatra">Installing Ruby And Sinatra</h2>

<hr />

<h3 id="updating-the-operating-system">Updating The Operating-System</h3>

<hr />

<p>We will start our tutorial by preparing our VPS, which means upgrading its default components to the latest versions to make sure we have everything up-to-date. </p>

<p>Update the software sources list and upgrade the dated applications:</p>
<pre class="code-pre "><code langs="">aptitude    update
aptitude -y upgrade
</code></pre>
<h3 id="getting-the-essential-build-development-tools">Getting The Essential Build/Development Tools</h3>

<hr />

<p>Before continuing with installation of our target applications, we are going to install the essential development tools package: <em>build-essential</em> using the default package manager <code>aptitude</code>. This package contains tools necessary to install certain things <em>from source</em>.</p>

<p>Run the following command to install <code>build-essential</code> package:</p>
<pre class="code-pre "><code langs="">aptitude install -y build-essential
</code></pre>
<p>Next, we are going to get commonly used development and deployment related tools, such as Git.</p>

<p>Run the following command to install some additional, commonly used tools:</p>
<pre class="code-pre "><code langs="">aptitude install -y cvs subversion git-core mercurial
</code></pre>
<h3 id="installing-ruby-version-manager-rvm">Installing Ruby Version Manager (RVM)</h3>

<hr />

<p>Ruby Version Manager (or RVM) lets developers quickly get started using Ruby and develop applications with it.</p>

<p>Not only does RVM allow you to work with multiple versions of Ruby simultaneously, but also it comes with built-in tools to create and work with <em>virtual environments</em> called <strong>gemsets</strong>. With the help of RVM, it is possible to create any number of perfectly isolated - and self-contained - gemsets where dependencies, packages, and the default Ruby installation is crafted to match your needs and kept accordingly between different stages of deployment -- guaranteed to work the same way regardless of where.</p>

<p><strong>Note:</strong> To learn more about how to work with gemsets and how to use RVM, check out the article <a href="https://indiareads/community/articles/how-to-use-rvm-to-manage-ruby-installations-and-environments-on-a-vps">How To Use RVM to Manage Ruby Installations and Environments on a VPS</a>.</p>

<p>In order to download and install RVM, run the following:</p>
<pre class="code-pre "><code langs="">curl -L get.rvm.io | bash -s stable
</code></pre>
<p>And to create a system environment using RVM shell script:</p>
<pre class="code-pre "><code langs="">source /etc/profile.d/rvm.sh
</code></pre>
<h3 id="installing-ruby-2-1-0">Installing Ruby 2.1.0</h3>

<hr />

<p>All that is needed from now on to work with Ruby 2.1.0 (or any other version), after downloading RVM and configuring a system environment is the actual installation of Ruby from source - which is to be handled by RVM.</p>

<p>In order to install Ruby 2.1.0 from source using RVM, run the following:</p>
<pre class="code-pre "><code langs="">rvm reload
rvm install 2.1.0
</code></pre>
<h3 id="installing-sinatra">Installing Sinatra</h3>

<hr />

<p>Once we have RVM install Ruby, we can use RubyGems package which comes with it by default to download and set up Sinatra on our system. RubyGems is the default Ruby package manager and it's an excellent tool at what it does.</p>

<p>Run the following command to install Sinatra with gem:</p>
<pre class="code-pre "><code langs="">gem install sinatra
</code></pre>
<h3 id="creating-a-www-user-group-and-a-deployer-user">Creating A www User Group And A deployer User</h3>

<hr />

<p>After we are done with all the installations, it is time to get into basics and create a Linux group and a user to host web applications. For this purpose, we can name our group as <code>www</code> and the user as <code>deployer</code>.</p>

<p>Add a new user group:</p>
<pre class="code-pre "><code langs=""># Usage: sudo addgroup [group name]
sudo addgroup www
</code></pre>
<p>Create a new user and add it to this group:</p>
<pre class="code-pre "><code langs=""># Create a new user:
# Usage: sudo adducer [user name]
# Follow on-screen instructions to user-related
# information such as the desired password.
sudo adduser deployer

# Add the user to an already existing group:
# Usage: sudo adducer [user name] [group name]
sudo adduser deployer www
</code></pre>
<p>Now create the application folder in <code>/var</code> directory:</p>
<pre class="code-pre "><code langs="">sudo mkdir /var/www
</code></pre>
<p>And set the permissions:</p>
<pre class="code-pre "><code langs=""># Set the ownership of the folder to members of `www` group
sudo chown -R :www  /var/www

# Set folder permissions recursively
sudo chmod -R g+rwX /var/www

# Ensure permissions will affect future sub-directories etc.
sudo chmod g+s      /var/www
</code></pre>
<p>Edit <code>/etc/sudoers</code> using the text editor nano to let the user <code>deployer</code> sudo for future deployments:</p>
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

<h2 id="creating-a-quot-hello-world-quot-application-with-sinatra">Creating A "Hello world!" Application With Sinatra</h2>

<hr />

<p><strong>Note:</strong> Below is a short tutorial on creating a two-page Sinatra-based application for demonstration purposes which is intended to be used as an example for our deployment articles. To get a more in-depth knowledge on working with Sinatra, check out the official <a href="http://www.sinatrarb.com/intro.html">Sinatra: Getting Started</a> documentation.</p>

<h3 id="application-directory">Application Directory</h3>

<hr />

<p>Let's begin our Sinatra journey by creating a directory to host our sample <code>Hello world!</code> application.</p>

<p>Run the following command to create an application directory:</p>
<pre class="code-pre "><code langs="">mkdir /var/www/my_app
cd    /var/www/my_app
</code></pre>
<p>RACK make certain assumptions regarding file hierarchy. Therefore, we need to have two more directories created alongside our application files: <code>tmp</code> and <code>public</code>.</p>

<p>Let's create them:</p>
<pre class="code-pre "><code langs="">mkdir tmp
mkdir public
mkdir pids
mkdir logs
</code></pre>
<p>And also add a <code>restart.txt</code> to be used later by application servers:</p>
<pre class="code-pre "><code langs="">touch tmp/restart.txt
</code></pre>
<p>Our final application structure:</p>
<pre class="code-pre "><code langs="">/my_app                    # /var/www/my_app
   |-- /public             # Static files (and for Passenger server)
   |-- /tmp              
         |-- restart.txt   # Application restart / reload file
   |-- /pids               # PID files
   |-- /logs               # Log files
   |-- config.ru           # Rack file (for servers)
   |-- app.rb              # Application module
</code></pre>
<p><strong>Note:</strong> To learn about different Ruby web-application servers and understand what <em>Rack</em> is, check out our article <a href="https://indiareads/community/articles/a-comparison-of-rack-web-servers-for-ruby-web-applications">A Comparison of (Rack) Web Servers for Ruby Web Applications</a>.</p>

<h3 id="sample-application-files">Sample Application Files</h3>

<hr />

<p>Now, we can begin constructing a <code>Hello world!</code> application.</p>

<p>Run the following command to create a <code>app.rb</code> inside the application directory <code>my_app</code> using the nano text editor:</p>
<pre class="code-pre "><code langs="">nano app.rb
</code></pre>
<p>Copy and paste the below code block:</p>
<pre class="code-pre "><code langs="">require 'rubygems'
require 'sinatra/base'

class MyApp < Sinatra::Base

  get '/' do
    'Hello world!'
  end

end
</code></pre>
<p>Save and exit by pressing CTRL+X and confirming with Y.</p>

<p>Next, we can create our <code>config.ru</code> file that web-application servers will use to run our program.</p>

<p>Run the following command to create a <code>config.ru</code> inside the application directory <code>my_app</code> using the nano text editor:</p>
<pre class="code-pre "><code langs="">nano config.ru
</code></pre>
<p>Copy and paste the below code block:</p>
<pre class="code-pre "><code langs="">require File.expand_path('../app.rb', __FILE__)
use Rack::ShowExceptions
run MyApp.new    
</code></pre>
<p>Save and exit by pressing CTRL+X and confirming with Y.</p>

<p>Now let's create our Gemfile:</p>
<pre class="code-pre "><code langs="">nano Gemfile
</code></pre>
<p>Copy and paste the below code block:</p>
<pre class="code-pre "><code langs="">source 'https://rubygems.org'
gem 'rack'
gem 'sinatra'
</code></pre>
<p>Save and exit by pressing CTRL+X and confirming with Y.</p>

<p>And perform an installation of these gems using <code>bundle</code>:</p>
<pre class="code-pre "><code langs="">bundle install
</code></pre>
<h3 id="testing-the-application-rackup">Testing The Application (rackup)</h3>

<hr />

<p>In order to test your application, you can simply run a test server using <code>rackup</code>.</p>

<p>Run the following command to start a test server:</p>
<pre class="code-pre "><code langs="">rackup config.ru --port=8080

# Hello world!

# To turn off the test server, press CTRL+C
</code></pre>
<h2 id="deployments">Deployments</h2>

<hr />

<p>Although we have covered the basics of creating a Sinatra application, for deployment purposes you will be dealing with source code from your development computer machine to get your application online. Therefore, you will need to put (i.e. upload) your application's repository (i.e. source code) on your droplet.</p>

<p>Here are some ways you can achieve this before continuing with our Sinatra deployment article:</p>

<ul>
<li><p>To learn about working with SFTP, check out the article: <a href="https://indiareads/community/articles/how-to-use-sftp-to-securely-transfer-files-with-a-remote-server">How To Use SFTP</a>.</p></li>
<li><p>To learn about FileZilla, check out the article on the subject: <a href="https://indiareads/community/articles/how-to-use-filezilla-to-transfer-and-manage-files-securely-on-your-vps">How To Use FileZilla</a>.</p></li>
</ul>

<p><strong>Note:</strong> Make sure to pay attention to file/folder permissions for deployments with certain server set ups. To learn about actual web deployments, check out our article on the subject <a href="https://indiareads/community/tutorials/how-to-deploy-sinatra-based-ruby-web-applications-on-ubuntu-13">How To Deploy Sinatra Based Ruby Web-Applications</a>. </p>

<div class="author">Submitted by: <a href="https://twitter.com/ostezer">O.S. Tezer</a></div>

    