<link rel='stylesheet' href='digital_ocean.css'><br><img src=https://community-cdn-digitalocean-com.global.ssl.fastly.net/assets/tutorials/images/large/mysql_with_ror_tw.jpg?1426863195/> <br> 
      <h3 id="introduction">Introduction</h3>

<p>Ruby on Rails uses sqlite3 as its default database, which works great in many cases, but may not be sufficient for your application. If your application requires the scalability, centralization, and control (or any other feature) that a client/server SQL database, such as <a href="https://indiareads/community/tutorials/sqlite-vs-mysql-vs-postgresql-a-comparison-of-relational-database-management-systems">PostgreSQL or MySQL</a>, you will need to perform a few additional steps to get it up and running.</p>

<p>This tutorial will show you how to set up a development Ruby on Rails environment that will allow your applications to use a MySQL database, on an Ubuntu 14.04 server. First, we will cover how to install MySQL and the MySQL adapter gem. Then we'll show you how to create a rails application that uses MySQL as its database server.</p>

<h2 id="prerequisites">Prerequisites</h2>

<p>This tutorial requires that have a working Ruby on Rails development environment. If you do not already have that, you may follow the tutorial in this link: <a href="https://indiareads/community/tutorials/how-to-install-ruby-on-rails-with-rbenv-on-ubuntu-14-04">How To Install Ruby on Rails with rbenv on Ubuntu 14.04</a>.</p>

<p>You will also need to have access to a superuser, or <code>sudo</code>, account, so you can install the MySQL database software.</p>

<p>Once you're ready, let's install MySQL.</p>

<h2 id="install-mysql">Install MySQL</h2>

<p>If you don't already have MySQL installed, let's do that now.</p>

<p>First, update apt-get:</p>
<pre class="code-pre "><code langs="">sudo apt-get update
</code></pre>
<p>Then install MySQL and its development libraries:</p>
<pre class="code-pre "><code langs="">sudo apt-get install mysql-server mysql-client libmysqlclient-dev
</code></pre>
<p>During the installation, your server will ask you to select and confirm a password for the MySQL "root" user.</p>

<p>When the installation is complete, we need to run some additional commands to get our MySQL environment set up securely. First, we need to tell MySQL to create its database directory structure where it will store its information. You can do this by typing:</p>
<pre class="code-pre "><code langs="">sudo mysql_install_db
</code></pre>
<p>Afterwards, we want to run a simple security script that will remove some dangerous defaults and lock down access to our database system a little bit. Start the interactive script by running:</p>
<pre class="code-pre "><code langs="">sudo mysql_secure_installation
</code></pre>
<p>You will be asked to enter the password you set for the MySQL root account. Next, it will ask you if you want to change that password. If you are happy with your current password, type <code>n</code> at the prompt.</p>

<p>For the rest of the questions, you should simply hit the "ENTER" key through each prompt to accept the default values. This will remove some sample users and databases, disable remote root logins, and load these new rules so that MySQL immediately respects the changes we have made.</p>

<p>MySQL is now installed, but we still need to install the MySQL gem.</p>

<h2 id="install-mysql-gem">Install MySQL Gem</h2>

<p>Before your Rails application can connect to a MySQL server, you need to install the MySQL adapter. The <code>mysql2</code> gem provides this functionality.</p>

<p>As the Rails user, install the <code>mysql2</code> gem, like this:</p>
<pre class="code-pre "><code langs="">gem install mysql2
</code></pre>
<p>Now your Rails applications can use MySQL databases.</p>

<h2 id="create-new-rails-application">Create New Rails Application</h2>

<p>Create a new Rails application in your home directory. Use the <code>-d mysql</code> option to set MySQL as the database, and be sure to substitute the highlighted word with your application name:</p>
<pre class="code-pre "><code langs="">cd ~
rails new <span class="highlight">appname</span> -d mysql
</code></pre>
<p>Then move into the application's directory:</p>
<pre class="code-pre "><code langs="">cd <span class="highlight">appname</span>
</code></pre>
<p>The next step is to configure the application's database connection.</p>

<h3 id="configure-database-connection">Configure Database Connection</h3>

<p>If you followed the MySQL install instructions from this tutorial, you set a password for MySQL's root user. The MySQL root login will be used to create your application's test and development databases.</p>

<p>Open your application's database configuration file in your favorite text editor. We'll use vi:</p>
<pre class="code-pre "><code langs="">vi config/database.yml
</code></pre>
<p>Under the <code>default</code> section, find the line that says "password:" and add the password to the end of it. It should look something like this (replace the highlighted part with your MySQL root password):</p>
<pre class="code-pre "><code langs="">password: <span class="highlight">mysql_root_password</span>
</code></pre>
<p>Save and exit.</p>

<h3 id="create-application-databases">Create Application Databases</h3>

<p>Create your application's <code>development</code> and <code>test</code> databases by using this rake command:</p>
<pre class="code-pre "><code langs="">rake db:create
</code></pre>
<p>This will create two databases in your MySQL server. For example, if your application's name is "appname", it will create databases called "appname_development" and "appname_test".</p>

<p>If you get an error that says "Access denied for user 'root'@'localhost' (using password: YES)Please provide the root password for your MySQL installation", press <code>Ctrl-c</code> to quit. Then revisit the previous subsection (Configure Database Connection) to be sure that the password in <code>database.yml</code> is correct. After ensuring that the password is correct, try creating the application databases again.</p>

<h2 id="test-configuration">Test Configuration</h2>

<p>The easiest way to test that your application is able to use the MySQL database is to try to run it.</p>

<p>For example, to run the development environment (the default), use this command:</p>
<pre class="code-pre "><code langs="">rails server
</code></pre>
<p>This will start your Rails application on your localhost on port 3000.</p>

<p>If your Rails application is on a remote server, and you want to access it through a web browser, an easy way is to bind it to the public IP address of your server. First, look up the public IP address of your server, then use it with the <code>rails server</code> command like this:</p>
<pre class="code-pre "><code langs="">rails server --binding=<span class="highlight">server_public_IP</span>
</code></pre>
<p>Now you should be able to access your Rails application in a web browser via the server's public IP address on port 3000:</p>
<pre class="code-pre "><code langs="">http://<span class="highlight">server_public_IP</span>:3000
</code></pre>
<p>If you see the "Welcome aboard" Ruby on Rails page, your application is properly configured, and connected to the MySQL database.</p>

<h2 id="conclusion">Conclusion</h2>

<p>You're now ready to start development on your Ruby on Rails application, with MySQL as the database, on Ubuntu 14.04!</p>

<p>Good luck!</p>

    