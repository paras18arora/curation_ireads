<link rel='stylesheet' href='digital_ocean.css'><br> <br> 
      <h3 id="introduction">Introduction</h3>

<hr />

<p>Previously, we have covered various ways of deploying Ruby-on-Rails applications (e.g. <a href="https://indiareads/community/articles/how-to-deploy-rails-apps-using-unicorn-and-nginx-on-centos-6-5">Rails with Unicorn & Nginx</a>, <a href="https://indiareads/community/articles/how-to-deploy-rails-apps-using-passenger-with-nginx-on-centos-6-5">Rails with Passenger & Nginx</a>), setting up a <a href="https://indiareads/community/articles/how-to-scale-ruby-on-rails-applications-across-multiple-droplets-part-1">scalable server structure</a> and learned how to connect a <a href="https://indiareads/community/articles/scaling-ruby-on-rails-setting-up-a-dedicated-mysql-server-part-2">dedicated MySQL instance</a> to our Rails application servers.</p>

<p>In this installation of our IndiaReads <a href="https://indiareads/community/articles/how-to-scale-ruby-on-rails-applications-across-multiple-droplets-part-1"><em>Scaling-Rails</em></a> series, we are going to find out how to build a <a href="http://www.postgresql.org/">PostgreSQL</a> server from scratch to use as the database persistence layer for Ruby-on-Rails web-applications. Continuing, we are going to see how to connect our Rails application servers with the database by making the necessary configuration changes.</p>

<p><strong>Note:</strong> This article, as we have mentioned, is part of our Scaling-Rails series and consists of installing PostgreSQL server on a dedicated Ubuntu VPS. However, you can very well install PostgreSQL, the exact same way explained here, to use on a single virtual server together with your Rails application. In order to see how to deploy Rails on a single droplet using Unicorn or Passenger, please click the links provided on the first paragraph and then continue from here to form the database layer.</p>

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

<h3 id="4-installing-postgresql">4. Installing PostgreSQL</h3>

<hr />

<ol>
<li>Adding The PostgreSQL Software Repository</li>
<li>Installing PostgreSQL</li>
</ol>

<h3 id="5-configuring-postgresql">5. Configuring PostgreSQL</h3>

<hr />

<ol>
<li>Changing The Default <code>postgres</code> User Password</li>
<li>Creating Roles And Databases</li>
<li>Enabling Remote Connections</li>
</ol>

<h3 id="6-configuring-rails-applications">6. Configuring Rails Applications</h3>

<hr />

<ol>
<li>Configuring <code>database.yml</code> For Rails</li>
<li>Getting The <code>PostgreSQL</code> Gem</li>
</ol>

<h2 id="choosing-a-database">Choosing A Database</h2>

<hr />

<p>Ruby on Rails application development framework provides a large array of support for database servers. For a majority of applications, a relational database management system is the way to go. However, some might require a non-relational, schema-less NoSQL database server -- either instead of the relational one or both running together.</p>

<p>When you begin working with Rails on your own development computer, the simplest and probably the most logical way is to start with using a capable but basic database implementation [such as the SQLite library]. However, for real-world deployments, chances are SQLite would be insufficient to handle your application load [thus requiring a full-fledged RDBMS].</p>

<p>Depending on your needs and application type, you need to decide on a database management system (i.e. a database server) to create the <em>database layer</em> of your application deployment set-up.</p>

<p><strong>For relational databases some of the more popular choices are:</strong></p>

<ul>
<li><strong>PostgreSQL and derivatives:</strong><br /></li>
</ul>

<p>The most popular and commonly used RDBMS and related, forked projects.</p>

<ul>
<li><strong>PostgreSQL:</strong><br /></li>
</ul>

<p>The most advanced, SQL-compliant and open-source objective-RDBMS.</p>

<p><strong>For non-relational database servers:</strong></p>

<ul>
<li><strong>Column based:</strong><br /></li>
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

<p><a href="https://indiareads/community/tutorials/understanding-sql-and-nosql-databases-and-different-database-models">Understanding SQL And NoSQL Databases And Different Database Models</a></p>

<ul>
<li><strong>Relational:</strong><br /></li>
</ul>

<p><a href="https://indiareads/community/tutorials/sqlite-vs-mysql-vs-postgresql-a-comparison-of-relational-database-management-systems">A Comparison Of Relational Database Management Systems</a></p>

<ul>
<li><strong>NoSQL:</strong><br /></li>
</ul>

<p><a href="https://indiareads/community/tutorials/a-comparison-of-nosql-database-management-systems-and-models">A Comparison Of NoSQL Database Management Systems And Models</a></p>

<h2 id="server-set-up-structure">Server Set-Up Structure</h2>

<hr />

<p>Before we begin with building the database layer, let's see what our final deployment set up will look like.</p>

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
1 x Database Server (e.g. PostgreSQL, PostgreSQL, MongoDB etc.)

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
                |  |       PostgreSQL,      |  |
                +->|     PostgreSQL,   |<-+
                   |        etc.       |
                   |                   |
                   +-------------------+
</code></pre>
<h2 id="adding-the-database-server-to-the-deployment-set-up">Adding The Database Server To The Deployment Set-Up</h2>

<hr />

<p>In this article, we are going to create and configure a PostgreSQL database management server on a Ubuntu 13 VPS.</p>

<p>Let's begin!</p>

<h3 id="preparing-the-server">Preparing The Server</h3>

<hr />

<p>Update the software sources list and upgrade the dated applications:</p>
<pre class="code-pre "><code langs="">aptitude    update
aptitude -y upgrade
</code></pre>
<p>Run the following command to install <code>build-essential</code> package:</p>
<pre class="code-pre "><code langs="">aptitude install -y build-essential
</code></pre>
<p>Run the following command to install some additional, commonly used tools:</p>
<pre class="code-pre "><code langs="">aptitude install -y cvs subversion git-core mercurial
</code></pre>
<h2 id="installing-postgresql">Installing PostgreSQL</h2>

<hr />

<h3 id="adding-the-postgresql-software-repository">Adding The PostgreSQL Software Repository</h3>

<hr />

<p>In order to download the latest version of PostgreSQL (<code>9.3</code>), we need to add the repository to <code>aptitude</code> sources list.</p>

<p>Run the following command to create a sources list for PostgreSQL:</p>
<pre class="code-pre "><code langs="">nano  /etc/apt/sources.list.d/pgdg.list
</code></pre>
<p>Copy-and-paste the below contents:</p>
<pre class="code-pre "><code langs="">deb http://apt.postgresql.org/pub/repos/apt/ saucy-pgdg main
</code></pre>
<p>Save and exit by pressing CTRL+X and confirming with Y.</p>

<p><strong>Note:</strong> We are assuming that you are working with Ubuntu 13 (saucy). If you are using a different version, run <code>lsb_release -c</code> to find out your distribution's name and replace it with <code>saucy</code> in the instructions above.</p>

<p>Update the sources list to include the new additions:</p>
<pre class="code-pre "><code langs="">aptitude    update
aptitude -y upgrade
</code></pre>
<h3 id="installing-postgresql">Installing PostgreSQL</h3>

<hr />

<p>Since now we have access to the source, using the default package manager <code>aptitude</code> (or <code>apt-get</code>) we can directly install the latest available version of PostgreSQL.</p>

<p>Run the following command to install PostgreSQL v. <code>9.3</code>:</p>
<pre class="code-pre "><code langs="">aptitude install postgresql-9.3 pgadmin3 
</code></pre>
<h2 id="configuring-postgresql">Configuring PostgreSQL</h2>

<hr />

<h3 id="changing-the-default-postgres-user-password">Changing The Default <code>postgres</code> User Password</h3>

<hr />

<p>In order to work with the database, we need to change the default password.</p>

<p>Run the following command to initiate the process:</p>
<pre class="code-pre "><code langs="">sudo -u postgres psql postgres
</code></pre>
<p>Once you see the prompt similar to <code>postgres=#</code>, type the following:</p>
<pre class="code-pre "><code langs="">\password postgres
</code></pre>
<p>Enter your password, re-enter again to verify and press CTRL+Z or type <strong>\q</strong> to exit.</p>

<h3 id="creating-roles-and-databases">Creating Roles And Databases</h3>

<hr />

<p>Login to PostgreSQL using the following command:</p>
<pre class="code-pre "><code langs="">sudo -u postgres psql
</code></pre>
<p>And run the instructions given below to create a <em>role</em> and a <em>database</em> to be used by Rails:</p>
<pre class="code-pre "><code langs=""># Usage: CREATE USER [user name] WITH PASSWORD '[password]';
# Example:
CREATE USER rails_myapp_user WITH PASSWORD 'pwd';

# Usage: CREATE DATABASE [database name] OWNER [user name];
# Example:
CREATE DATABASE rails_myapp OWNER rails_myapp_user;
</code></pre>
<p>Press CTRL+Z or type <strong>\q</strong> to exit.</p>

<p><strong>Note:</strong> To learn about PostgreSQL roles and management, check out the following articles:</p>

<ul>
<li><p><a href="https://indiareads/community/articles/how-to-create-remove-manage-tables-in-postgresql-on-a-cloud-server">How To Create, Remove, & Manage Tables in PostgreSQL</a></p></li>
<li><p><a href="https://indiareads/community/articles/how-to-use-roles-and-manage-grant-permissions-in-postgresql-on-a-vps--2">How To Use Roles and Manage Grant Permissions in PostgreSQL</a></p></li>
</ul>

<h3 id="enabling-remote-connections">Enabling Remote Connections</h3>

<hr />

<p>Since we need PostgreSQL server to be accessible from remote computers running the Rails application, the configuration file must be modified.</p>

<p>Run the following command to edit the PostgreSQL configuration <code>postgresql.conf</code> using the nano text editor:</p>
<pre class="code-pre "><code langs="">nano /etc/postgresql/9.3/main/postgresql.conf
</code></pre>
<p>We would like to tell PostgreSQL to listen to connections from the IP address assigned to our droplet.</p>

<p>Scroll down the file and find the following line:</p>
<pre class="code-pre "><code langs="">#listen_addresses = 'localhost'
</code></pre>
<p>Change it to:</p>
<pre class="code-pre "><code langs="">listen_addresses = '*'
</code></pre>
<p>And save and exit by pressing CTRL+X and confirming with Y.</p>

<p>Next, we need to tell PostgreSQL the specific connections we would like it to accept, similarly to how firewalls work.</p>

<p>Run the following command to edit the PostgreSQL <code>hba</code> file <code>pg_hba.conf</code> using the nano text editor:</p>
<pre class="code-pre "><code langs="">nano /etc/postgresql/9.3/main/pg_hba.conf
</code></pre>
<p>Scroll down the file and find the section:</p>
<pre class="code-pre "><code langs=""># Put your actual configuration here
# ..
</code></pre>
<p>After the comment block, append the following line:</p>
<pre class="code-pre "><code langs=""># TYPE   DATABASE      USER        ADDRESS        METHOD
host        all        all        0.0.0.0/0        md5
</code></pre>
<p>And again, save and exit by pressing CTRL+X and confirming with Y.</p>

<p>Restart the PostgreSQL daemon with the following command:</p>
<pre class="code-pre "><code langs="">service postgresql restart

#  * Restarting PostgreSQL 9.3 database server
# ...done.
</code></pre>
<h2 id="configuring-rails-applications">Configuring Rails Applications</h2>

<hr />

<p>In this section, we will modify the Rails application servers so that they start working with the database server we have just set up.</p>

<h3 id="configuring-database-yml-for-rails">Configuring <code>database.yml</code> For Rails</h3>

<hr />

<p>Database settings for Rails applications are kept inside the <code>database.yml</code> file in <code>/config</code> directory.</p>

<p>Run the following command to edit the <code>database.yml</code> file using the nano text editor:</p>
<pre class="code-pre "><code langs=""># Make sure to enter your application deployment directory
# Example:
# cd /var/www/my_app

nano config/database.yml
</code></pre>
<p>Once you open up this file, you will see database settings divided by environment names. Since an application needs to run using the <code>production</code> environment, let's edit the configuration for that.</p>

<p>Replace the <code>production:</code> <code>YML</code> code block with the following, changing the necessary bits to suit your own set-up configuration, e.g. the IP address etc.</p>
<pre class="code-pre "><code langs=""># Example:
# production:
#   adapter: postgresql
#   encoding: utf8
#   database: [database name]
#   username: [user name]
#   password: [password]
#   host: [server IP address]
#   port: [port number]
#   protocol: [protocol]
#   pool: [connection pool]

production:
  adapter: postgresql
  encoding: utf8
  database: rails_myapp
  username: rails_myapp_user
  password: pwd
  host: 128.199.233.36
  port: 5432
  pool: 10
</code></pre>
<p><strong>Note:</strong> As provided in the example above, you might need to specify the protocol. </p>

<p><strong>Note:</strong> The <code>pool</code> argument contains the number of maximum simultaneous database connection slots (i.e. pool) available. You need to assess your needs and set a number accordingly.</p>

<p>Save and exit by pressing CTRL+X and confirming with Y.</p>

<h3 id="getting-the-postgresql-gem">Getting The <code>PostgreSQL</code> Gem</h3>

<hr />

<p>Start editing the Gemfile using nano using the following:</p>
<pre class="code-pre "><code langs="">nano Gemfile
</code></pre>
<p>Add the following line to the file:</p>
<pre class="code-pre "><code langs="">gem 'pg'
</code></pre>
<p>Save and exit by pressing CTRL+X and confirming with Y.</p>

<p>Install the new gem using <code>bundle</code>:</p>
<pre class="code-pre "><code langs="">bundle install
</code></pre>
<p>And that's it! From now on, your Rails application servers will be using your brand new database server for all operations.</p>

<div class="author">Submitted by: <a href="https://twitter.com/ostezer">O.S. Tezer</a></div>

    