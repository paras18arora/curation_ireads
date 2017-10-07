<link rel='stylesheet' href='digital_ocean.css'><br> <br> 
      <h3 id="introduction">Introduction</h3>

<hr />

<p>There are two methods to scaling an application, service, server, etc. The first, vertical scaling, calls for more resources to a machine. The second, horizontal scaling, calls for a separation of functionality to create a more piecemeal assembly of parts. </p>

<p>For example, does a machine spit out memory errors in the syslog? It may make sense to just add more RAM or a swapfile. However, let's say the database server is growing to a large amount of entries and the web server alone is starting to increase in traffic – a better idea may be to set up a more controllable environment (not to mention more cost effective). To do so, a separation of the web server and database server into separate machines is the key. That is horizontal scaling.</p>

<h2 id="requirements">Requirements</h2>

<hr />

<ul>
<li><p>Two droplets each running Debian 7. Each VPS should have completed <a href="https://indiareads/community/articles/initial-server-setup-with-debian-7">Initial Server Setup</a> tutorial.</p></li>
<li><p>A basic understanding of Linux commands and what they do. See <a href="https://indiareads/community/articles/an-introduction-to-linux-basics">An Introduction to Linux Basics</a> for a good starting point.</p></li>
<li><p>You will need to have installed Laravel successfully on one of the droplets. This tutorial will work for NGINX + Laravel 4 (skip the <em>Wrap Up</em> step): <a href="https://indiareads/community/articles/how-to-install-laravel-with-nginx-on-an-ubuntu-12-04-lts-vps">Laravel + Nginx</a></p></li>
</ul>

<p><strong>Do not install a database on the same virtual server as your Laravel install</strong></p>

<p>For the sake of simplicity, we will refer to the first droplet with the Laravel and Nginx install as the <em>Laravel droplet</em> with an IP of 192.0.2.5. The second droplet with the PostgreSQL install will be called the <em>database droplet</em> with an IP of 192.0.2.1.</p>

<h2 id="prepping-the-database">Prepping the Database</h2>

<hr />

<p>Horizontal scaling is a rather simple concept that can be come rather complex when you start thinking about more advanced topics such as database replication and load balancing. However we'll only be covering a basic separation of services: the web server frontend and the database backend. Luckily PostgreSQL and Laravel make this a rather simple process.</p>

<p>First we will need to install PostgreSQL on our <em>database droplet</em>:</p>

<p><code>sudo apt-get install postgresql</code></p>

<p>Next, we must create a database and user within the server that will have the proper permissions to interact with the database. To do so, we must log into the PostgreSQL server:</p>

<p><code>sudo -u postgres psql</code></p>

<p>First, let's create the database user:</p>
<pre class="code-pre "><code langs="">CREATE USER databaseuser WITH PASSWORD 'password';
GRANT CREATE ON SCHEMA public TO databaseuser;
GRANT USAGE ON SCHEMA public TO databaseuser;
</code></pre>
<p>Then create the database with the user as the owner and then quit the server:</p>
<pre class="code-pre "><code langs="">CREATE DATABASE mydatabase WITH OWNER databaseuser;
\q 
</code></pre>
<p>Next, the database droplet will need to know that it's okay for the Laravel droplet to connect to it. PostgreSQL has a client authentication file that makes this super easy.</p>

<p><code>sudo nano /etc/postgresql/9.1/main/pg_hba.conf</code></p>

<p>Add a line that includes the connection, database name, database user, address to be accepted, and the method of connection:</p>
<pre class="code-pre "><code langs=""># IPv4 local connections:
host  mydatabase   databaseuser   192.0.2.5/32   md5
</code></pre>
<p>Save and exit, then open up <strong>postgresql.conf</strong> and find the line that says "listen_addresses = 'localhost'".</p>

<p><code>sudo nano /etc/postgresql/9.1/main/postgresql.conf</code></p>
<pre class="code-pre "><code langs="">listen_addresses = '192.0.2.1'
</code></pre>
<p>You may also change this value to one that will accept any address:</p>
<pre class="code-pre "><code langs="">listen_addresses = '*'
</code></pre>
<p>Save, exit, and restart the PostgreSQL server:</p>

<p><code>sudo service postgresql restart</code></p>

<h2 id="configuring-laravel">Configuring Laravel</h2>

<hr />

<p>The first thing that needs to be done is to give PHP some knowledge of how to work with the PostgreSQL server. Do so by installing the php5-pgsql extension.</p>

<p><code>sudo apt-get install php5-pgsql</code><br />
<code>sudo service php5-fpm restart</code></p>

<p>Next we need to tell Laravel where our database server is located and how to access it. If you followed the NGINX + Laravel tutorial, then Laravel should be installed at <code>/var/www/laravel</code>.</p>

<p><code>sudo nano /var/www/laravel/app/config/database.php</code></p>

<p>First let's have Laravel use it's PostgreSQL driver:</p>
<pre class="code-pre "><code langs="">'default' => 'pgsql',
</code></pre>
<p>Next, let's setup information about the PostgreSQL server.</p>
<pre class="code-pre "><code langs="">'pgsql' => array(
      'driver'   => 'pgsql',
      'host'     => '192.0.2.1',
      'database' => 'mydatabase',
      'username' => 'databaseuser',
      'password' => 'password',
      'charset'  => 'utf8',
      'prefix'   => '',
      'schema'   => 'public',
    ),
</code></pre>
<p>Save and exit.</p>

<h2 id="testing-the-connection">Testing the Connection</h2>

<hr />

<p>To test this connection, let's run a migration in from the command line that will help build our database tables out.</p>

<p>Note: Migrations isn't an extra package to be installed. It comes with laravel and it's a set of commands to interact with our database.</p>

<p>First enter the application directory where artisan is located.</p>

<p><code>cd /var/www/laravel</code></p>

<p>Great! Now it's time to install migrations and see if our database connection is working.</p>

<p><code>php artisan migrate:install</code></p>

<p>If this command runs successfully without errors you should see a new table in your database called <strong>migrations</strong>.</p>

<h3 id="wrapping-it-up">Wrapping it Up</h3>

<hr />

<p>As you can see, splitting servers is rather simple. By combining horizontal scaling with vertical scaling, a sysadmin can achieve separation of services and increased performance. Even better these methods require 0 extra software.</p>

<div class="author">Submitted by: <a href="https://twitter.com/alexkavon">Alex Kavon</a></div>

    