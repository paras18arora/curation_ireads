<link rel='stylesheet' href='digital_ocean.css'><br><img src=https://community-cdn-digitalocean-com.global.ssl.fastly.net/assets/tutorials/images/large/postgresql_twitter.png?1435690079/> <br> 
      <h3 id="introduction">Introduction</h3>

<p>Relational databases are the cornerstone of data organization for a multitude of needs. They power everything from online shopping to rocket launches. A database that is both venerable but very much still in the game is PostgreSQL. PostgreSQL follows most of the SQL standard, has ACID transactions, has support for foreign keys and views, and is still in active development. </p>

<p>If the application that you're running needs stability, package quality, and easy administration, Debian 8 (codename "Jessie") is one of the best candidates for a Linux distribution. It moves a bit more slowly than other "distros," but its stability and quality is well recognized. If your application or service needs a database, the combination of Debian 8 and PostgreSQL is one of the best in town. </p>

<p>In this article we will show you how to install PostgreSQL on a new Debian 8 Stable instance and get started.</p>

<h2 id="prerequisites">Prerequisites</h2>

<p>The first thing is to get a Debian 8 Stable system going. You can follow the instructions from the <a href="https://indiareads/community/tutorials/initial-server-setup-with-debian-8">Initial Server Setup with Debian 8</a> article. This tutorial assumes you have aø Debian 8 Stable Droplet ready. </p>

<p>Except otherwise noted, all of the commands in this tutorial should be run as a non-root user with sudo privileges. To learn how to create users and grant them sudo privileges, check out <a href="https://indiareads/community/tutorials/initial-server-setup-with-debian-8">Initial Server Setup with Debian 8</a>.</p>

<h2 id="installing-postgresql">Installing PostgreSQL</h2>

<p>Before installing PostgreSQL, make sure that you have the latest information from the Debian repositories by updating the apt package list with:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo apt-get update
</li></ul></code></pre>
<p>You should see the package lists being updated and the following message:</p>
<pre class="code-pre "><code langs="">Reading package lists... Done.
</code></pre>
<p>There are several packages that start with <code>postgresql</code>:</p>

<ul>
<li><code>postgresql-9.4</code>: The PostgreSQL server package</li>
<li><code>postgresql-client-9.4</code>: The client for PostgreSQL</li>
<li><code>postgresql</code>: A "metapackage" better explained by the <a href="http://debian-handbook.info/browse/stable/sect.building-first-package.html">Debian Handbook</a> or the <a href="http://www.debian.org/doc/manuals/maint-guide/">Debian New Maintainers' Guide</a></li>
</ul>

<p>To install directly the <code>postgresql-9.4</code> package:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo apt-get install postgresql-9.4 postgresql-client-9.4
</li></ul></code></pre>
<p>When asked, type <code>Y</code> to install the packages. If everything went fine, the packages are now downloaded from the repository and installed. </p>

<h2 id="checking-the-installation">Checking the Installation</h2>

<p>To check that the PostgreSQL server was correctly installed and is running, you can use the command <code>ps</code>:</p>
<pre class="code-pre "><code langs=""># ps -ef | grep postgre
</code></pre>
<p>You should see something like this on the terminal:</p>
<pre class="code-pre "><code langs="">postgres 32164     1  0 21:58 ?        00:00:00 /usr/lib/postgresql/9.4/bin/postgres -D /var/lib/   postgresql/9.4/main -c config_file=/etc/postgresql/9.4/main/postgresql.conf
postgres 32166 32164  0 21:58 ?        00:00:00 postgres: checkpointer process
postgres 32167 32164  0 21:58 ?        00:00:00 postgres: writer process
postgres 32168 32164  0 21:58 ?        00:00:00 postgres: wal writer process
postgres 32169 32164  0 21:58 ?        00:00:00 postgres: autovacuum launcher process
postgres 32170 32164  0 21:58 ?        00:00:00 postgres: stats collector process 
</code></pre>
<p>Success! PostgreSQL has been successfully installed and is running. </p>

<h2 id="accessing-the-postgresql-database">Accessing the PostgreSQL Database</h2>

<p>On Debian, PostgreSQL is installed with a default user and default database both called <code>postgres</code>. To connect to the database, first you need to switch to the <code>postgres</code> user by issuing the following command while logged in as root (this will not work with sudo access):</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">su - postgres
</li></ul></code></pre>
<p>You now should be logged as <code>postgres</code>. To start the PostgreSQL console, type <code>psql</code>:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">psql
</li></ul></code></pre>
<p>Done! You should be logged on the PostgreSQL console. You should see the following prompt:</p>
<pre class="code-pre "><code langs="">psql (9.4.2)
Type "help" for help.

postgres=# 
</code></pre>
<p>To exit the psql console just use the command <code>\q</code>.</p>

<h2 id="creating-new-roles">Creating New Roles</h2>

<p>By default, Postgres uses a concept called "roles" to aid in authentication and authorization. These are, in some ways, similar to regular Unix-style accounts, but PostgreSQL does not distinguish between users and groups and instead prefers the more flexible term "role".</p>

<p>Upon installation PostgreSQL is set up to use "ident" authentication, meaning that it associates PostgreSQL roles with a matching Unix/Linux system account. If a PostgreSQL role exists, it can be signed in by logging into the associated Linux system account.</p>

<p>The installation procedure created a user account called postgres that is associated with the default Postgres role.</p>

<p>To create additional roles we can use the <code>createuser</code> command. Mind that this command should be issued as the user <code>postgres</code>,  not inside the PostgreSQL console:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">createuser --interactive
</li></ul></code></pre>
<p>This basically is an interactive shell script that calls the correct PostgreSQL commands to create a user to your specifications. It will ask you some questions: the name of the role, whether it should be a superuser, if the role should be able to create new databases, and if the role will be able to create new roles. The <code>man</code> page has more information:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">man createuser
</li></ul></code></pre>
<h2 id="creating-a-new-database">Creating a New Database</h2>

<p>PostgreSQL is set up by default with authenticating roles that are requested by matching system accounts. (You can get more information about this at <a href="http://www.postgresql.org/docs/8.1/static/user-manag.html">postgresql.org</a>). It also comes with the assumption that a matching database will exist for the role to connect to. So if I have a user called <code>test1</code>, that role will attempt to connect to a database called <code>test1</code> by default.</p>

<p>You can create the appropriate database by simply calling this command as the <code>postgres</code> user:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">createdb test1
</li></ul></code></pre>
<p>The new database <code>test1</code> now is created.</p>

<h2 id="connecting-to-postgresql-with-the-new-user">Connecting to PostgreSQL with the New User</h2>

<p>Let's assume that you have a Linux account named <code>test1</code>, created a PostgreSQL <code>test1</code> role to match it, and created the database <code>test1</code>. To change the user account in Linux to <code>test1</code>:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">su - test1
</li></ul></code></pre>
<p>Then, connect to the <code>test1</code> database as the <code>test1</code> PostgreSQL role using the command:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">psql
</li></ul></code></pre>
<p>Now you should see the PostgreSQL prompt with the newly created user <code>test1</code> instead of <code>postgres</code>.</p>

<h2 id="creating-and-deleting-tables">Creating and Deleting Tables</h2>

<p>Now that you know how to connect to the PostgreSQL database system, we will start to go over how to complete some basic tasks. </p>

<p>First, let's create a table to store some data. Let's create a table that describes playground equipment. </p>

<p>The basic syntax for this command is something like this:</p>
<pre class="code-pre "><code langs="">CREATE TABLE table_name (
    column_name1 col_type (field_length) column_constraints,
    column_name2 col_type (field_length),
    column_name3 col_type (field_length)
);
</code></pre>
<p>As you can see, we give the table a name, and then define the columns that we want, as well as the column type and the max length of the field data. We can also optionally add table constraints for each column.</p>

<p>You can learn more about how to create and manage tables in Postgres in the <a href="https://indiareads/community/tutorials/how-to-create-remove-manage-tables-in-postgresql-on-a-cloud-server">How To Create, Remove, & Manage Tables in PostgreSQL on a Cloud Server</a> article.</p>

<p>For our purposes, we're going to create a simple table like this:</p>
<pre class="code-pre "><code langs="">CREATE TABLE playground (
    equip_id serial PRIMARY KEY,
    type varchar (50) NOT NULL,
    color varchar (25) NOT NULL,
    location varchar(25) check (location in ('north', 'south', 'west', 'east', 'northeast', 'southeast', 'southwest', 'northwest')),
    install_date date
);
</code></pre>
<p>We have made a playground table that inventories the equipment that we have. This starts with an equipment ID, which is of the serial type. This data type is an auto-incrementing integer. We have given this column the constraint of primary key which means that the values must be unique and not null.</p>

<p>For two of our columns, we have not given a field length. This is because some column types don't require a set length because the length is implied by the type.</p>

<p>We then give columns for the equipment type and color, each of which cannot be empty. We then create a location column and create a constraint that requires the value to be one of eight possible values. The last column is a date column that records the date that we installed the equipment.</p>

<p>To see the tables, use the command <code>\dt</code> on the psql prompt. The result would be similar to </p>
<pre class="code-pre "><code langs="">             List of relations
 Schema |    Name    | Type  |  Owner 
--------+------------+-------+----------
 public | playground | table | postgres
</code></pre>
<p>As you can see, we have our playground table.</p>

<h2 id="adding-querying-and-deleting-data-in-a-table">Adding, Querying, and Deleting Data in a Table</h2>

<p>Now that we have a table created, we can insert some data into it.</p>

<p>Let's add a slide and a swing. We do this by calling the table we're wanting to add to, naming the columns and then providing data for each column. Our slide and swing could be added like this:</p>
<pre class="code-pre "><code langs="">INSERT INTO playground (type, color, location, install_date) VALUES ('slide', 'blue', 'south', '2014-04-28');
INSERT INTO playground (type, color, location, install_date) VALUES ('swing', 'yellow', 'northwest', '2010-08-16');
</code></pre>
<p>You should notice a few things. First, keep in mind that the column names should not be quoted, but the column values that you're entering do need quotes.</p>

<p>Another thing to keep in mind is that we do not enter a value for the <code>equip_id</code> column. This is because this is auto-generated whenever a new row in the table is created.</p>

<p>We can then get back the information we've added by typing:</p>
<pre class="code-pre "><code langs="">SELECT * FROM playground;
</code></pre>
<p>The output should be</p>
<pre class="code-pre "><code langs=""> equip_id | type  | color  | location  | install_date 
----------+-------+--------+-----------+--------------
        1 | slide | blue   | south     | 2014-04-28
        2 | swing | yellow | northwest | 2010-08-16
</code></pre>
<p>Here, you can see that our <code>equip_id</code> has been filled in successfully and that all of our other data has been organized correctly. If our slide breaks, and we remove it from the playground, we can also remove the row from our table by typing:</p>
<pre class="code-pre "><code langs="">DELETE FROM playground WHERE type = 'slide';
</code></pre>
<p>If we query our table again: </p>
<pre class="code-pre "><code langs="">SELECT * FROM playground;
</code></pre>
<p>We will see our slide is no longer a part of the table:</p>
<pre class="code-pre "><code langs=""> equip_id | type  | color | location | install_date 
----------+-------+-------+----------+--------------
        1 | slide | blue  | south    | 2014-04-28
</code></pre>
<h2 id="useful-commands">Useful Commands</h2>

<p>Here are a few commands that can help you get an idea of your current environment:</p>

<ul>
<li><p><strong>\?</strong>: Get a full list of psql commands, including those not listed here.</p></li>
<li><p><strong>\h</strong>: Get help on SQL commands. You can follow this with a specific command to get help with the syntax.</p></li>
<li><p><strong>\q</strong>: Quit the psql program and exit to the Linux prompt.</p></li>
<li><p><strong>\d</strong>: List available tables, views, and sequences in current database.</p></li>
<li><p><strong>\du</strong>: List available roles</p></li>
<li><p><strong>\dp</strong>: List access privileges</p></li>
<li><p><strong>\dt</strong>: List tables</p></li>
<li><p><strong>\l</strong>: List databases</p></li>
<li><p><strong>\c</strong>: Connect to a different database. Follow this by the database name.</p></li>
<li><p><strong>\password</strong>: Change the password for the username that follows.</p></li>
<li><p><strong>\conninfo</strong>: Get information about the current database and connection.</p></li>
</ul>

<p>With these commands you should be able to navigate the PostgreSQL databases, tables, and roles in no time. </p>

<h2 id="conclusion">Conclusion</h2>

<p>You should now have a fully functional PostgreSQL database up and running on your Debian system. Congratulations! There is a plethora of documentation to go from here:</p>

<ul>
<li><p><a href="http://www.postgresql.org/docs/manuals/">PostgreSQL Manuals</a></p></li>
<li><p>Installing the package <code>postgresql-doc</code>: <code>sudo apt-get install postgresql-doc</code></p></li>
<li><p><code>README</code> file installed at  <code>/usr/share/doc/postgresql-doc-9.4/tutorial/README</code></p></li>
</ul>

<p>For a full list of supported SQL commands in PostgreSQL follow this link:</p>

<ul>
<li><a href="http://www.postgresql.org/docs/9.1/static/sql-commands.html">SQL Commands</a></li>
</ul>

<p>To compare the different functionalities of the databases you can check out:</p>

<ul>
<li><a href="https://indiareads/community/tutorials/sqlite-vs-mysql-vs-postgresql-a-comparison-of-relational-database-management-systems">SQLite vs MySQL vs PostgreSQL</a></li>
</ul>

<p>For a better understanding of roles and permissions see:</p>

<ul>
<li><a href="https://indiareads/community/tutorials/how-to-use-roles-and-manage-grant-permissions-in-postgresql-on-a-vps--2">How to use Roles and Manage Grant Permission in PostgreSQL on a VPS</a></li>
</ul>

    