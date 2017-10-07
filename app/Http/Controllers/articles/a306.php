<link rel='stylesheet' href='digital_ocean.css'><br> <br> 
      <h3 id="introduction">Introduction</h3>

<hr />

<p>Each server that is part of a multi-machine application deployment stack should be like a good Italian pizza: a solid base needs to be garnished only with the necessary ingredients, without over-bloating or heavily loading, in order to keep everything easy to handle (and manage).</p>

<p>In this second part of our <a href="https://indiareads/community/articles/how-to-scale-ruby-on-rails-applications-across-multiple-droplets-part-1"><em>Scaling-Rails</em></a> IndiaReads article series, we are going to see how to create a droplet to host the database layer, for application servers to connect-to and work-with. Our aim here is to minimize the chances of letting a Single Point of Failure (SPoF) emerge as a possible culprit of downtime (or loss), by distinctively delegating one single task <em>per server</em>.</p>

<h2 id="glossary">Glossary</h2>

<hr />

<h3 id="1-choosing-a-database">1. Choosing A Database</h3>

<hr />

<h3 id="2-server-set-up-structure">2. Server Set-Up Structure</h3>

<hr />

<ol>
<li>Load-Balancing Multiple Application Servers</li>
<li>The Database Server Layer</li>
</ol>

<h3 id="3-adding-the-database-server-to-the-deployment-set-up">3. Adding The Database Server To The Deployment Set-Up</h3>

<hr />

<ol>
<li>Preparing The Server</li>
</ol>

<h3 id="4-installing-mysql">4. Installing MySQL</h3>

<hr />

<ol>
<li>Downloading The Database Server</li>
<li>Performing The Initial Set-Up</li>
<li>Connect To The Database Server</li>
<li>Create A New Database</li>
<li>Create A New Database User</li>
<li>Granting Privileges</li>
<li>Enabling Remote Connections</li>
</ol>

<h3 id="5-configuring-rails-applications">5. Configuring Rails Applications</h3>

<hr />

<ol>
<li>Installing Database Server Libraries</li>
<li>Configuring <code>database.yml</code> For Rails</li>
<li>Getting The <code>mysql</code> Gem</li>
<li>Migrating Data Between Servers</li>
</ol>

<h2 id="choosing-a-database">Choosing A Database</h2>

<hr />

<p>Ruby on Rails application development framework provides a large array of support for database servers. For a majority of applications, a relational database management system is the way to go. However, some might require a non-relational, schema-less NoSQL database server -- either instead of the relational one or both running together.</p>

<p>When you begin working with Rails on your own development computer, the simplest and probably the most logical way is to start with using a capable but basic database implementation, such as the SQLite library. However, for real-world deployments, chances are SQLite would be insufficient to handle your application load, thus requiring a full-fledged RDBMS.</p>

<p>Depending on your needs and application type, you need to decide on a <em>database management system</em> (i.e. a database server) to create the <em>database layer</em> of your application deployment set-up.</p>

<p><strong>For relational databases some of the more popular choices are:</strong></p>

<ul>
<li><strong>MySQL and derivatives:</strong><br /></li>
</ul>

<p>The most popular and commonly used RDBMS and related, forked projects.</p>

<ul>
<li><strong>PostgreSQL:</strong><br /></li>
</ul>

<p>The most advanced, SQL-compliant and open-source objective-RDBMS.</p>

<p><strong>For non-relational database servers:</strong></p>

<ul>
<li><strong>Column based:</strong></li>
</ul>

<p>Cassandra, HBase, etc.</p>

<ul>
<li><strong>Document:</strong><br /></li>
</ul>

<p>MongoDB, Couchbase, etc</p>

<ul>
<li><strong>Graph:</strong><br /></li>
</ul>

<p>OrientDB, Neo4J, etc.</p>

<p>In order to make a clear and long-term decision before continuing with deploying a database server, you might be interested in reading our articles on the subject:</p>

<ul>
<li><strong>Introduction To Databases:</strong><br /></li>
</ul>

<p><a href="https://link_to_10_1_understanding_databases">Understanding SQL And NoSQL Databases And Different Database Models</a></p>

<ul>
<li><strong>Relational:</strong><br /></li>
</ul>

<p><a href="https://link_to_10_2_rdbms_comparison">A Comparison Of Relational Database Management Systems</a></p>

<ul>
<li><strong>NoSQL:</strong><br /></li>
</ul>

<p><a href="https://link_to_10_3_nosql_comparison">A Comparison Of NoSQL Database Management Systems And Models</a></p>

<h2 id="server-set-up-structure">Server Set-Up Structure</h2>

<hr />

<p>Before we begin with building the <em>database layer</em>, let's see what our final deployment set up will look like.</p>

<h3 id="load-balancing-multiple-application-servers">Load-Balancing Multiple Application Servers</h3>

<hr />

<p>Previously, after creating a load-balancer / reverse-proxy with multiple application servers, this is what we had in the end:</p>
<pre class="code-pre "><code langs="">Three droplets with each having a distinct role:
------------------------------------------------
1 x Load-Balancer / Reverse-Proxy
2 x Application Servers Running Your Rails Web-Application / API

                             ---

                    DEPLOYMENT STRUCTURE

             +-------------------------------+
             |                               |
             | LOAD-BALANCER / REVERSE PROXY |
             |                               |
             +-------------------------------+
                             +
                             |
                             |
        +---------------+    |    +---------------+
        |  APP  SERVER  |    |    |  APP  SERVER  |
        |---------------|    |    |---------------|
        |               |    |    |               |
        |     RAILS     |<---+--->|     RAILS     |
        |               |         |               |
        +---------------+         +---------------+
</code></pre>
<h3 id="the-database-server-layer">The Database Server Layer</h3>

<hr />

<p>In order to have a centrally accessible database server (e.g. a RDBMS and/or NoSQL database), we will add a 4th element to our server set-up:</p>
<pre class="code-pre "><code langs="">Four droplets:
------------------------------------------------
1 x Load-Balancer / Reverse-Proxy
2 x Application Servers Running Your Rails Web-Application / API
1 x Database Server (e.g. MySQL, PostgreSQL, MongoDB etc.)

             +-------------------------------+
             |                               |
             | LOAD-BALANCER / REVERSE PROXY |
             |                               |
             +-------------------------------+
                             +
                             |
                             |
        +---------------+    |    +---------------+
        |  APP  SERVER  |    |    |  APP  SERVER  |
        |---------------|    |    |---------------|
        |               |    |    |               |
        |     RAILS     |<---+--->|     RAILS     |
        |               |         |               |
        +---------------+         +---------------+
                +                         +
                |                         |
                |  +-------------------+  |
                |  |  DATABASE SERVER  |  |
                |  |-------------------|  |
                |  |                   |  |
                |  |       MySQL,      |  |
                +->|     PostgreSQL,   |<-+
                   |        etc.       |
                   |                   |
                   +-------------------+
</code></pre>
<h2 id="adding-the-database-server-to-the-deployment-set-up">Adding The Database Server To The Deployment Set-Up</h2>

<hr />

<p>In this article, for the purposes of demonstration, we are going to create and configure a MySQL database.</p>

<p><em>Let's begin!</em></p>

<h3 id="preparing-the-server">Preparing The Server</h3>

<hr />

<p><strong>Note:</strong> This part is a summary of the server preparation section from our <a href="https://link_to_scaling_rails">Scaling-Rails</a> tutorial. It explains how to get started with a CentOS VPS. If you would like to deploy your MySQL instance on an Ubuntu machine, check out <a href="https://link_to_8_deploying_sinatra">Deploying Sinatra</a> tutorial to see how to prepare an Ubuntu server before continuing with installing MySQL, or any other database server. </p>

<p>Run the following command to update the default tools of your CentOS based virtual server:</p>
<pre class="code-pre "><code langs="">yum -y update
</code></pre>
<p>Install the application bundle containing several development tools by executing the following command:</p>
<pre class="code-pre "><code langs="">yum groupinstall -y 'development tools'
</code></pre>
<p>Add the EPEL software repository for YUM package manager to use.</p>
<pre class="code-pre "><code langs=""># Enable EPEL Repository
sudo su -c 'rpm -Uvh http://dl.fedoraproject.org/pub/epel/6/x86_64/epel-release-6-8.noarch.rpm'

# Update everything, once more.
yum -y update
</code></pre>
<p>Install some additional libraries:</p>
<pre class="code-pre "><code langs="">yum install -y curl-devel nano sqlite-devel libyaml-devel
</code></pre>
<h2 id="installing-mysql">Installing MySQL</h2>

<hr />

<h3 id="downloading-the-database-server">Downloading The Database Server</h3>

<hr />

<p>In order to install MySQL, execute the following command:</p>
<pre class="code-pre "><code langs="">yum install mysql-server mysql-devel
</code></pre>
<p>Start the MySQL server daemon:</p>
<pre class="code-pre "><code langs="">service mysqld start
</code></pre>
<p><strong>Note:</strong> If you are working with Ubuntu, instead of <code>mysql-devel</code>, you need to install <code>mysql-client</code> and <code>libmysqlclient-dev</code> packages using <code>aptitude</code> (or <code>apt-get</code>) on your application servers for Rails to be able to work with MySQL.</p>

<h3 id="performing-the-initial-set-up">Performing The Initial Set-Up</h3>

<hr />

<p>Run the following command to start the initial MySQL set-up process:</p>
<pre class="code-pre "><code langs="">/usr/bin/mysql_secure_installation
</code></pre>
<p>Once you run the above command, you will see a welcome screen similar to below:</p>
<pre class="code-pre "><code langs="">NOTE: RUNNING ALL PARTS OF THIS SCRIPT IS RECOMMENDED FOR ALL MySQL
      SERVERS IN PRODUCTION USE!  PLEASE READ EACH STEP CAREFULLY!


In order to log into MySQL to secure it, we'll need the current
password for the root user.  If you've just installed MySQL, and
you haven't set the root password yet, the password will be blank,
so you should just press enter here.

Enter current password for root (enter for none): 
</code></pre>
<p>Unless have already created a password using the:</p>
<pre class="code-pre "><code langs="">/usr/bin/mysqladmin -u root password 'new-password'
/usr/bin/mysqladmin -u root -h myt password 'new-password'
</code></pre>
<p>commands, press enter and move on with the next steps, answering the questions similarly to this:</p>
<pre class="code-pre "><code langs=""># Set root password?                     --> Y
# Remove anonymous users?                --> Y
# Disallow root login remotely?          --> Y
# Remove test database and access to it? --> Y
# Reload privilege tables now?           --> Y
</code></pre>
<h3 id="connect-to-the-database-server">Connect To The Database Server</h3>

<hr />

<p>Connect to the database using the MySQL client:</p>
<pre class="code-pre "><code langs="">mysql -u root -p
</code></pre>
<p>Enter your root password set at the previous step:</p>
<pre class="code-pre "><code langs=""># Enter password:
# ..
# .
mysql>
</code></pre>
<h3 id="create-a-new-database">Create A New Database</h3>

<hr />

<p>Let's begin with creating a default database for our Rails application.</p>

<p>Run the following command to create a new MySQL database:</p>
<pre class="code-pre "><code langs=""># Usage: create database [database_name];
# Example:
create database rails_myapp;
</code></pre>
<h3 id="create-a-new-database-user">Create A New Database User</h3>

<hr />

<p>For reasons of security, let's now create a database user for Rails application to use that will have remote access.</p>

<p>Add the new user with both local and remote access:</p>
<pre class="code-pre "><code langs=""># Usage:
# CREATE USER '[user name]'@'localhost' IDENTIFIED BY '[password]';
# CREATE USER '[user name]'@'%' IDENTIFIED BY '[password]'; 
# Example:
CREATE USER 'rails_myapp_user'@'localhost' IDENTIFIED BY 'pwd';
CREATE USER 'rails_myapp_user'@'%' IDENTIFIED BY 'pwd';
</code></pre>
<p>To verify that the users have been created, run the following:</p>
<pre class="code-pre "><code langs="">SELECT User,host FROM mysql.user;

# Example:
# +------------------+-----------+
# | User             | host      |
# +------------------+-----------+
# | rails_myapp_user | %         |
# | root             | 127.0.0.1 |
# | rails_myapp_user | localhost |
# | root             | localhost |
# +------------------+-----------+ 
</code></pre>
<h3 id="granting-privileges">Granting Privileges</h3>

<hr />

<p>Run the following commands to grant privileges to a specific user:</p>
<pre class="code-pre "><code langs=""># Usage:
# GRANT ALL ON [database name].* TO '[user name]'@'localhost';
# GRANT ALL ON [database name].* TO '[user name]'@'%';
# Example:
GRANT ALL ON rails_myapp.* TO 'rails_myapp_user'@'localhost';
GRANT ALL ON rails_myapp.* TO 'rails_myapp_user'@'%';
</code></pre>
<p>And <em>flush</em> privileges:</p>
<pre class="code-pre "><code langs="">FLUSH PRIVILEGES;
</code></pre>
<p><strong>Note:</strong> To fine-tune the privileges according to your needs, check out the official MySQL documentation on the subject: <a href="http://dev.mysql.com/doc/refman/5.1/en/privileges-provided.html">Privileges Provided by MySQL</a></p>

<p>Exist the client:</p>
<pre class="code-pre "><code langs="">exit
# Bye
</code></pre>
<h3 id="enabling-remote-connections">Enabling Remote Connections</h3>

<hr />

<p>Since we need MySQL server to be accessible from remote computers running the Rails application, the configuration file must be modified.</p>

<p>Run the following command to edit the MySQL configuration <code>my.cnf</code> using the <code>nano</code> text editor:</p>
<pre class="code-pre "><code langs="">nano /etc/my.cnf
</code></pre>
<p>We would like to tell MySQL to listen to connections from the IP address assigned to our droplet, so let's add the following line:</p>
<pre class="code-pre "><code langs="">bind-address   =  0.0.0.0
</code></pre>
<p>At the end of the <code>[mysqld]</code> block:</p>
<pre class="code-pre "><code langs="">[mysqld]
..
.
bind-address   =  0.0.0.0
</code></pre>
<p>Save and exit by pressing CTRL+X and confirming with Y.</p>

<p>Restart the MySQL daemon with the following command:</p>
<pre class="code-pre "><code langs="">service mysqld restart

# Stopping mysqld:                               [  OK  ]
# Starting mysqld:                               [  OK  ]
</code></pre>
<h2 id="configuring-rails-applications">Configuring Rails Applications</h2>

<hr />

<p>In this section, we will modify the Rails application servers so that they start working with the database server we have just set up.</p>

<h3 id="installing-database-server-libraries">Installing Database Server Libraries</h3>

<hr />

<p>The first thing to do is installing the necessary database libraries. In our case, it is MySQL's development package.</p>

<p>Run the following to install MySQL development package <code>mysql-devel</code>:</p>
<pre class="code-pre "><code langs="">yum install -y mysql-devel
</code></pre>
<h3 id="configuring-database-yml-for-rails">Configuring <code>database.yml</code> For Rails</h3>

<hr />

<p>Database settings for Rails applications are kept inside the <code>database.yml</code> file in <code>/config</code> directory.</p>

<p>Run the following command to edit the <code>database.yml</code> file using the <code>nano</code> text editor:</p>
<pre class="code-pre "><code langs=""># Make sure to enter your application deployment directory
# Example:
# cd /var/www/my_app

nano config/database.yml
</code></pre>
<p>Once you open up this file, you will see database settings, divided by environment names. Since an application needs to run using the <code>production</code> environment, let's edit the configuration for that.</p>

<p>Replace the <code>production:</code> <code>YML</code> code block with the following, changing the necessary bits to suit your own set-up configuration, e.g. the IP address etc.</p>
<pre class="code-pre "><code langs=""># Example:
# production:
#   adapter: mysql
#   encoding: utf8
#   database: [database name]
#   username: [user name]
#   password: [password]
#   host: [server IP address]
#   port: [port number]
#   protocol: [protocol]
#   pool: [connection pool]

production:
  adapter: mysql
  encoding: utf8
  database: rails_myapp
  username: rails_myapp_user
  password: pwd
  host: 128.199.233.36
  port: 3306
  pool: 10
</code></pre>
<p><strong>Note:</strong> As provided in the example above, you might need to specify the protocol. </p>

<p><strong>Note:</strong> The <code>pool</code> argument contains the number of maximum simultaneous database connection slots (i.e. pool) available. You need to assess your needs and set a number accordingly.</p>

<p>Save and exit by pressing CTRL+X and confirming with Y.</p>

<h3 id="getting-the-mysql-gem">Getting The <code>mysql</code> Gem</h3>

<hr />

<p>Start editing the Gemfile using nano using the following:</p>
<pre class="code-pre "><code langs="">nano Gemfile
</code></pre>
<p>Add the following line to the file:</p>
<pre class="code-pre "><code langs="">gem 'mysql'
</code></pre>
<p>Save and exit by pressing CTRL+X and confirming with Y.</p>

<p>Install the new gem using <code>bundle</code>:</p>
<pre class="code-pre "><code langs="">bundle install
</code></pre>
<p>And that's it! From now on, your Rails application servers will be using your brand new database server for all operations.</p>

<h3 id="migrating-data-between-servers">Migrating Data Between Servers</h3>

<hr />

<p>If you already have data on your development machine which you would like to migrate to your VPS, check out the IndiaReads community article on the subject: <a href="https://indiareads/community/articles/how-to-migrate-a-mysql-database-between-two-servers">How To Migrate a MySQL Database Between Two Servers</a>.</p>

<div class="author">Submitted by: <a href="https://twitter.com/ostezer">O.S. Tezer</a></div>

    