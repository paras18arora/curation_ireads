<link rel='stylesheet' href='digital_ocean.css'><br><img src=https://community-cdn-digitalocean-com.global.ssl.fastly.net/assets/tutorials/images/large/RubyonRails_twitter.png?1459466583/> <br> 
      <h3 id="introduction">Introduction</h3>

<p>Ruby on Rails uses sqlite3 as its default database, which works great in many cases, but may not be sufficient for your application. If your application requires the scalability, centralization, and control (or any other feature) that is provided by a client/server SQL database, such as <a href="https://indiareads/community/tutorials/sqlite-vs-mysql-vs-postgresql-a-comparison-of-relational-database-management-systems">PostgreSQL or MySQL</a>, you will need to perform a few additional steps to get it up and running.</p>

<p>This tutorial will show you how to set up a development Ruby on Rails environment that will allow your applications to use a PostgreSQL database, on an Ubuntu 14.04 server. First, we will cover how to install and configure PostgreSQL. Then we'll show you how to create a rails application that uses PostgreSQL as its database server.</p>

<h2 id="prerequisites">Prerequisites</h2>

<p>This tutorial requires that have a working Ruby on Rails development environment. If you do not already have that, you may follow the tutorial in this link: <a href="https://indiareads/community/tutorials/how-to-install-ruby-on-rails-with-rbenv-on-ubuntu-14-04">How To Install Ruby on Rails with rbenv on Ubuntu 14.04</a>.</p>

<p>You will also need to have access to a superuser, or <code>sudo</code>, account, so you can install the PostgreSQL database software.</p>

<p>Once you're ready, let's install PostgreSQL.</p>

<h2 id="install-postgresql">Install PostgreSQL</h2>

<p>If you don't already have PostgreSQL installed, let's do that now.</p>

<p>First, update apt-get:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo apt-get update
</li></ul></code></pre>
<p>Then install PostgreSQL and its development libraries:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo apt-get install postgresql postgresql-contrib libpq-dev
</li></ul></code></pre>
<p>PostgreSQL is now installed but you should create a new database user, that your Rails application will use.</p>

<h2 id="create-database-user">Create Database User</h2>

<p>Create a PostgreSQL superuser user with this command (substitute the highlighted word with your own username):</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo -u postgres createuser -s <span class="highlight">pguser</span>
</li></ul></code></pre>
<p>If you want to set a password for the database user, enter the PostgreSQL console with this command:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo -u postgres psql
</li></ul></code></pre>
<p>The PostgreSQL console is indicated by the <code>postgres=#</code> prompt. At the PostgreSQL prompt, enter this command to set the password for the database user that you created:</p>
<pre class="code-pre custom_prefix"><code langs=""><ul class="prefixed"><li class="line" prefix="postgres=#">\password <span class="highlight">pguser</span>
</li></ul></code></pre>
<p>Enter your desired password at the prompt, and confirm it.</p>

<p>Now you may exit the PostgreSQL console by entering this command:</p>
<pre class="code-pre custom_prefix"><code langs=""><ul class="prefixed"><li class="line" prefix="postgres=#">\q
</li></ul></code></pre>
<p>Let's create a Rails application now.</p>

<h2 id="create-new-rails-application">Create New Rails Application</h2>

<p>Create a new Rails application in your home directory. Use the <code>-d postgresql</code> option to set PostgreSQL as the database, and be sure to substitute the highlighted word with your application name:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">cd ~
</li><li class="line" prefix="$">rails new <span class="highlight">appname</span> -d postgresql
</li></ul></code></pre>
<p>Then move into the application's directory:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">cd <span class="highlight">appname</span>
</li></ul></code></pre>
<p>The next step is to configure the application's database connection.</p>

<h3 id="configure-database-connection">Configure Database Connection</h3>

<p>The PostgreSQL user that you created will be used to create your application's test and development databases. We need to configure the proper database settings for your application.</p>

<p>Open your application's database configuration file in your favorite text editor. We'll use vi:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">vi config/database.yml
</li></ul></code></pre>
<p>Under the <code>default</code> section, find the line that says "pool: 5" and add the following lines under it. It should look something like this (replace the highlighted parts with your PostgreSQL user and password):</p>
<div class="code-label " title="config/database.yml excerpt">config/database.yml excerpt</div><pre class="code-pre "><code langs="">  host: localhost
  username: <span class="highlight">pguser</span>
  password: <span class="highlight">pguser_password</span>
</code></pre>
<p>Save and exit.</p>

<h3 id="create-application-databases">Create Application Databases</h3>

<p>Create your application's <code>development</code> and <code>test</code> databases by using this rake command:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">rake db:create
</li></ul></code></pre>
<p>This will create two databases in your PostgreSQL server. For example, if your application's name is "appname", it will create databases called "appname_development" and "appname_test".</p>

<p>If you get an error at this point, revisit the previous subsection (Configure Database Connection) to be sure that the <code>host</code>, <code>username</code>, and <code>password</code> in <code>database.yml</code> are correct. After ensuring that the database information is correct, try creating the application databases again.</p>

<h2 id="test-configuration">Test Configuration</h2>

<p>The easiest way to test that your application is able to use the PostgreSQL database is to try to run it.</p>

<p>For example, to run the development environment (the default), use this command:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">rails server
</li></ul></code></pre>
<p>This will start your Rails application on your localhost on port 3000.</p>

<p>If your Rails application is on a remote server, and you want to access it through a web browser, an easy way is to bind it to the public IP address of your server. First, look up the public IP address of your server, then use it with the <code>rails server</code> command like this (substitute it for the highlighted part):</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">rails server --binding=<span class="highlight">server_public_IP</span>
</li></ul></code></pre>
<p>Now you should be able to access your Rails application in a web browser via the server's public IP address on port 3000:</p>
<div class="code-label " title="Visit in a web browser:">Visit in a web browser:</div><pre class="code-pre "><code langs="">http://<span class="highlight">server_public_IP</span>:3000
</code></pre>
<p>If you see the "Welcome aboard" Ruby on Rails page, your application is properly configured, and connected to the PostgreSQL database.</p>

<h2 id="conclusion">Conclusion</h2>

<p>You're now ready to start development on your Ruby on Rails application, with PostgreSQL as the database, on Ubuntu 14.04!</p>

<p>Good luck!</p>

    