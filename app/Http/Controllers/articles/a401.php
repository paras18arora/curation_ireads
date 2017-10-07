<link rel='stylesheet' href='digital_ocean.css'><br> <br> 
      <h3 id="introduction">Introduction</h3>

<p>Ruby on Rails uses sqlite3 as its default database, which works great in many cases, but may not be sufficient for your application. If your application requires the scalability, centralization, and control (or any other feature) that a client/server SQL database, such as <a href="https://indiareads/community/tutorials/sqlite-vs-mysql-vs-postgresql-a-comparison-of-relational-database-management-systems">PostgreSQL or MySQL</a>, you will need to perform a few additional steps to get it up and running.</p>

<p>This tutorial will show you how to set up a development Ruby on Rails environment that will allow your applications to use a PostgreSQL database, on an CentOS 7 or RHEL server. First, we will cover how to install and configure PostgreSQL. Then we'll show you how to create a rails application that uses PostgreSQL as its database server.</p>

<h2 id="prerequisites">Prerequisites</h2>

<p>This tutorial requires that have a working Ruby on Rails development environment. If you do not already have that, you may follow the tutorial in this link: <a href="https://indiareads/community/tutorials/how-to-install-ruby-on-rails-with-rbenv-on-centos-7">How To Install Ruby on Rails with rbenv on CentOS 7</a>.</p>

<p>You will also need to have access to a superuser, or <code>sudo</code>, account, so you can install the PostgreSQL database software.</p>

<p>This guide also assumes that SELinux is disabled.</p>

<p>Once you're ready, let's install PostgreSQL.</p>

<h2 id="install-postgresql">Install PostgreSQL</h2>

<p>If you don't already have PostgreSQL installed, let's do that now.</p>

<p>If you haven't already done so, add the EPEL repository to yum with this command:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo yum install epel-release
</li></ul></code></pre>
<p>Install PostgreSQL server and its development libraries:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo yum install postgresql-server postgresql-contrib postgresql-devel
</li></ul></code></pre>
<p>PostgreSQL is installed but we still need to do some basic configuration.</p>

<p>Create a new PostgreSQL database cluster:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo postgresql-setup initdb
</li></ul></code></pre>
<p>By default, PostgreSQL does not allow password authentication. We will change that by editing its host-based authentication configuration.</p>

<p>Open the HBA configuration with your favorite text editor. We will use vi:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo vi /var/lib/pgsql/data/pg_hba.conf
</li></ul></code></pre>
<p>Find the lines that looks like this, near the bottom of the file:</p>
<div class="code-label " title="pg_hba.conf excerpt (original)">pg_hba.conf excerpt (original)</div><pre class="code-pre "><code langs="">host    all             all             127.0.0.1/32            ident
host    all             all             ::1/128                 ident
</code></pre>
<p>Then replace "ident" with "md5", so they look like this:</p>
<div class="code-label " title="pg_hba.conf excerpt (updated)">pg_hba.conf excerpt (updated)</div><pre class="code-pre "><code langs="">host    all             all             127.0.0.1/32            md5
host    all             all             ::1/128                 md5
</code></pre>
<p>Save and exit. PostgreSQL is now configured to allow password authentication.</p>

<p>Now start and enable PostgreSQL:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo systemctl start postgresql
</li><li class="line" prefix="$">sudo systemctl enable postgresql
</li></ul></code></pre>
<p>PostgreSQL is now installed but you should create a new database user, that your Rails application will use.</p>

<h2 id="create-database-user">Create Database User</h2>

<p>First, change to the <code>postgres</code> system user:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo su - postgres
</li></ul></code></pre>
<p>Create a PostgreSQL superuser user with this command (substitute the highlighted word with your own username):</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">createuser -s <span class="highlight">pguser</span>
</li></ul></code></pre>
<p>To set a password for the database user, enter the PostgreSQL console with this command:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">psql
</li></ul></code></pre>
<p>The PostgreSQL console is indicated by the <code>postgres=#</code> prompt. At the PostgreSQL prompt, enter this command to set the password for the database user that you created:</p>
<pre class="code-pre custom_prefix"><code langs=""><ul class="prefixed"><li class="line" prefix="postgres=#">\password <span class="highlight">pguser</span>
</li></ul></code></pre>
<p>Enter your desired password at the prompt, and confirm it.</p>

<p>Now you may exit the PostgreSQL console by entering this command:</p>
<pre class="code-pre custom_prefix"><code langs=""><ul class="prefixed"><li class="line" prefix="postgres=#">\q
</li></ul></code></pre>
<p>Now that your PostgreSQL user is set up, switch back to your normal user:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">exit
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

<p>If your Rails application is on a remote server, and you want to access it through a web browser, an easy way is to bind it to the public IP address of your server. First, look up the public IP address of your server, then use it with the <code>rails server</code> command like this:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">rails server --binding=<span class="highlight">server_public_IP</span>
</li></ul></code></pre>
<p>Now you should be able to access your Rails application in a web browser via the server's public IP address on port 3000:</p>
<div class="code-label " title="Visit in a web browser:">Visit in a web browser:</div><pre class="code-pre "><code langs="">http://<span class="highlight">server_public_IP</span>:3000
</code></pre>
<p>If you see the "Welcome aboard" Ruby on Rails page, your application is properly configured, and connected to the PostgreSQL database.</p>

<h2 id="conclusion">Conclusion</h2>

<p>You're now ready to start development on your Ruby on Rails application, with PostgreSQL as the database, on CentOS 7!</p>

<p>Good luck!</p>

    