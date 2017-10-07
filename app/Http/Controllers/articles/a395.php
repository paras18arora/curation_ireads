<link rel='stylesheet' href='digital_ocean.css'><br><img src=https://community-cdn-digitalocean-com.global.ssl.fastly.net/assets/tutorials/images/large/postgresql_twitter.png?1433535152/> <br> 
      <h3 id="introduction">Introduction</h3>

<p>Relational database management systems are a key component of many web sites and applications.  They provide a structured way to store, organize, and access information.</p>

<p><strong>PostgreSQL</strong>, or Postgres, is a relational database management system that provides an implementation of the SQL querying language.  It is a popular choice for many small and large projects and has the advantage of being standards-compliant and having many advanced features like reliable transactions and concurrency without read locks.</p>

<p>In this guide, we will demonstrate how to install Postgres on CentOS 7 and go over some basic ways to use it.</p>

<h2 id="installation">Installation</h2>

<p>CentOS's default repositories contain Postgres packages, so we can install them without a hassle using the <code>yum</code> package system.</p>

<p>Install the postgresql-server package and the "contrib" package, that adds some additional utilities and functionality:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo yum install postgresql-server postgresql-contrib
</li></ul></code></pre>
<p>Accept the prompt, by responding with a <code>y</code>.</p>

<p>Now that our software is installed, we have to perform a few steps before we can use it.</p>

<p>Create a new PostgreSQL database cluster:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo postgresql-setup initdb
</li></ul></code></pre>
<p>By default, PostgreSQL does not allow password authentication. We will change that by editing its host-based authentication (HBA) configuration.</p>

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
<p>PostgreSQL is now ready to be used. We can go over how it works and how it may be different from similar database management systems you may have used.</p>

<h2 id="using-postgresql-roles-and-databases">Using PostgreSQL Roles and Databases</h2>

<p>By default, Postgres uses a concept called "roles" to aid in authentication and authorization.  These are, in some ways, similar to regular Unix-style accounts, but Postgres does not distinguish between users and groups and instead prefers the more flexible term "role".</p>

<p>Upon installation Postgres is set up to use "ident" authentication, meaning that it associates Postgres roles with a matching Unix/Linux system account.  If a Postgres role exists, it can be signed in by logging into the associated Linux system account.</p>

<p>The installation procedure created a user account called <code>postgres</code> that is associated with the default Postgres role.  In order to use Postgres, we'll need to log into that account.  You can do that by typing:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo -i -u postgres
</li></ul></code></pre>
<p>You will be asked for your normal user password and then will be given a shell prompt for the <code>postgres</code> user.</p>

<p>You can get a Postgres prompt immediately by typing:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">psql
</li></ul></code></pre>
<p>You will be auto-logged in and will be able to interact with the database management system right away.</p>

<p>However, we're going to explain a little bit about how to use other roles and databases so that you have some flexibility as to which user and database you wish to work with.</p>

<p>Exit out of the PostgreSQL prompt by typing:</p>
<pre class="code-pre custom_prefix"><code langs=""><ul class="prefixed"><li class="line" prefix="postgres=#">\q
</li></ul></code></pre>
<p>You should now be back in the <code>postgres</code> user command prompt.</p>

<h2 id="create-a-new-role">Create a New Role</h2>

<p>From the <code>postgres</code> Linux account, you have the ability to log into the database system.  However, we're also going to demonstrate how to create additional roles.  The <code>postgres</code> Linux account, being associated with the Postgres administrative role, has access to some utilities to create users and databases.</p>

<p>We can create a new role by typing:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">createuser --interactive
</li></ul></code></pre>
<p>This basically is an interactive shell script that calls the correct Postgres commands to create a user to your specifications.  It will only ask you two questions: the name of the role and whether it should be a superuser.  You can get more control by passing some additional flags.  Check out the options by looking at the <code>man</code> page:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">man createuser
</li></ul></code></pre>
<h2 id="create-a-new-database">Create a New Database</h2>

<p>The way that Postgres is set up by default (authenticating roles that are requested by matching system accounts) also comes with the assumption that a matching database will exist for the role to connect to.</p>

<p>So if I have a user called <code>test1</code>, that role will attempt to connect to a database called <code>test1</code> by default.</p>

<p>You can create the appropriate database by simply calling this command as the <code>postgres</code> user:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">createdb <span class="highlight">test1</span>
</li></ul></code></pre>
<h2 id="connect-to-postgres-with-the-new-user">Connect to Postgres with the New User</h2>

<p>Let's assume that you have a Linux system account called <code>test1</code> (you can create one by typing: <code>sudo adduser test1</code>), and that you have created a Postgres role and database also called <code>test1</code>.</p>

<p>You can change to the Linux system account by typing:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo -i -u <span class="highlight">test1</span>
</li></ul></code></pre>
<p>You can then connect to the <code>test1</code> database as the <code>test1</code> Postgres role by typing:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">psql
</li></ul></code></pre>
<p>This will log in automatically assuming that all of the components have been configured.</p>

<p>If you want your user to connect to a different database, you can do so by specifying the database like this (make sure you <code>\q</code> to the command prompt):</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">psql -d <span class="highlight">postgres</span>
</li></ul></code></pre>
<p>You can get information about the Postgres user you're logged in as and the database you're currently connected to by typing:</p>
<pre class="code-pre custom_prefix"><code langs=""><ul class="prefixed"><li class="line" prefix="postgres=#">\conninfo
</li></ul></code></pre><pre class="code-pre "><code langs=""><div class="secondary-code-label " title="Output:">Output:</div>You are connected to database "postgres" as user "postgres" via socket in "/var/run/postgresql" at port "5432".
</code></pre>
<p>This can help remind you of your current settings if you are connecting to non-default databases or with non-default users.</p>

<h2 id="create-and-delete-tables">Create and Delete Tables</h2>

<p>Now that you know how to connect to the PostgreSQL database system, we will start to go over how to complete some basic tasks.</p>

<p>First, let's create a table to store some data.  Let's create a table that describes playground equipment.</p>

<p>The basic syntax for this command is something like this:</p>
<pre class="code-pre custom_prefix"><code langs=""><ul class="prefixed"><li class="line" prefix="postgres=#">CREATE TABLE <span class="highlight">table_name</span> (
</li><li class="line" prefix="postgres=#">    <span class="highlight">column_name1 col_type</span> (<span class="highlight">field_length</span>) <span class="highlight">column_constraints</span>,
</li><li class="line" prefix="postgres=#">    <span class="highlight">column_name2 col_type</span> (<span class="highlight">field_length</span>),
</li><li class="line" prefix="postgres=#">    <span class="highlight">column_name3 col_type</span> (<span class="highlight">field_length</span>)
</li><li class="line" prefix="postgres=#">);
</li></ul></code></pre>
<p>As you can see, we give the table a name, and then define the columns that we want, as well as the column type and the max length of the field data.  We can also optionally add table constraints for each column.</p>

<p>You can learn more about <a href="https://digitalocean.com/community/articles/how-to-create-remove-manage-tables-in-postgresql-on-a-cloud-server">how to create and manage tables in Postgres</a> here.</p>

<p>For our purposes, we're going to create a simple table like this:</p>
<pre class="code-pre custom_prefix"><code langs=""><ul class="prefixed"><li class="line" prefix="postgres=#">CREATE TABLE playground (
</li><li class="line" prefix="postgres=#">    equip_id serial PRIMARY KEY,
</li><li class="line" prefix="postgres=#">    type varchar (50) NOT NULL,
</li><li class="line" prefix="postgres=#">    color varchar (25) NOT NULL,
</li><li class="line" prefix="postgres=#">    location varchar(25) check (location in ('north', 'south', 'west', 'east', 'northeast', 'southeast', 'southwest', 'northwest')),
</li><li class="line" prefix="postgres=#">    install_date date
</li><li class="line" prefix="postgres=#">);
</li></ul></code></pre>
<p>We have made a playground table that inventories the equipment that we have.  This starts with an equipment ID, which is of the <code>serial</code> type. This data type is an auto-incrementing integer.  We have given this column the constraint of <code>primary key</code> which means that the values must be unique and not null.</p>

<p>For two of our columns, we have not given a field length.  This is because some column types don't require a set length because the length is implied by the type.</p>

<p>We then give columns for the equipment type and color, each of which cannot be empty.  We then create a location column and create a constraint that requires the value to be one of eight possible values.  The last column is a date column that records the date that we installed the equipment.</p>

<p>We can see our new table by typing this:</p>
<pre class="code-pre custom_prefix"><code langs=""><ul class="prefixed"><li class="line" prefix="postgres=#">\d
</li></ul></code></pre><pre class="code-pre "><code langs=""><div class="secondary-code-label " title="Output:">Output:</div>                   List of relations
 Schema |          Name           |   Type   |  Owner   
--------+-------------------------+----------+----------
 public | playground              | table    | postgres
 public | playground_equip_id_seq | sequence | postgres
(2 rows)
</code></pre>
<p>As you can see, we have our playground table, but we also have something called <code>playground_equip_id_seq</code> that is of the type <code>sequence</code>.  This is a representation of the "serial" type we gave our <code>equip_id</code> column.  This keeps track of the next number in the sequence.</p>

<p>If you want to see just the table, you can type:</p>
<pre class="code-pre custom_prefix"><code langs=""><ul class="prefixed"><li class="line" prefix="postgres=#">\dt
</li></ul></code></pre><pre class="code-pre "><code langs=""><div class="secondary-code-label " title="Output:">Output:</div>           List of relations
 Schema |    Name    | Type  |  Owner   
--------+------------+-------+----------
 public | playground | table | postgres
(1 row)
</code></pre>
<h2 id="add-query-and-delete-data-in-a-table">Add, Query, and Delete Data in a Table</h2>

<p>Now that we have a table created, we can insert some data into it.</p>

<p>Let's add a slide and a swing.  We do this by calling the table we're wanting to add to, naming the columns and then providing data for each column.  Our slide and swing could be added like this:</p>
<pre class="code-pre custom_prefix"><code langs=""><ul class="prefixed"><li class="line" prefix="postgres=#">INSERT INTO playground (type, color, location, install_date) VALUES ('slide', 'blue', 'south', '2014-04-28');
</li><li class="line" prefix="postgres=#">INSERT INTO playground (type, color, location, install_date) VALUES ('swing', 'yellow', 'northwest', '2010-08-16');
</li></ul></code></pre>
<p>You should notice a few things.  First, keep in mind that the column names should not be quoted, but the column <em>values</em> that you're entering do need quotes.</p>

<p>Another thing to keep in mind is that we do not enter a value for the <code>equip_id</code> column.  This is because this is auto-generated whenever a new row in the table is created.</p>

<p>We can then get back the information we've added by typing:</p>
<pre class="code-pre custom_prefix"><code langs=""><ul class="prefixed"><li class="line" prefix="postgres=#">SELECT * FROM playground;
</li></ul></code></pre><pre class="code-pre "><code langs=""><div class="secondary-code-label " title="Output:">Output:</div> equip_id | type  | color  | location  | install_date 
----------+-------+--------+-----------+--------------
        1 | slide | blue   | south     | 2014-04-28
        2 | swing | yellow | northwest | 2010-08-16
(2 rows)
</code></pre>
<p>Here, you can see that our <code>equip_id</code> has been filled in successfully and that all of our other data has been organized correctly.</p>

<p>If our slide breaks and we remove it from the playground, we can also remove the row from our table by typing:</p>
<pre class="code-pre custom_prefix"><code langs=""><ul class="prefixed"><li class="line" prefix="postgres=#">DELETE FROM playground WHERE type = 'slide';
</li></ul></code></pre>
<p>If we query our table again, we will see our slide is no longer a part of the table:</p>
<pre class="code-pre custom_prefix"><code langs=""><ul class="prefixed"><li class="line" prefix="postgres=#">SELECT * FROM playground;
</li></ul></code></pre><pre class="code-pre "><code langs=""><div class="secondary-code-label " title="Output:">Output:</div> equip_id | type  | color  | location  | install_date 
----------+-------+--------+-----------+--------------
        2 | swing | yellow | northwest | 2010-08-16
(1 row)
</code></pre>
<h2 id="how-to-add-and-delete-columns-from-a-table">How To Add and Delete Columns from a Table</h2>

<p>If we want to modify a table after it has been created to add an additional column, we can do that easily.</p>

<p>We can add a column to show the last maintenance visit for each piece of equipment by typing:</p>
<pre class="code-pre custom_prefix"><code langs=""><ul class="prefixed"><li class="line" prefix="postgres=#">ALTER TABLE playground ADD last_maint date;
</li></ul></code></pre>
<p>If you view your table information again, you will see the new column has been added (but no data has been entered):</p>
<pre class="code-pre custom_prefix"><code langs=""><ul class="prefixed"><li class="line" prefix="postgres=#">SELECT * FROM playground;
</li></ul></code></pre><pre class="code-pre "><code langs=""><div class="secondary-code-label " title="Output:">Output:</div> equip_id | type  | color  | location  | install_date | last_maint 
----------+-------+--------+-----------+--------------+------------
        2 | swing | yellow | northwest | 2010-08-16   | 
(1 row)
</code></pre>
<p>We can delete a column just as easily.  If we find that our work crew uses a separate tool to keep track of maintenance history, we can get rid of the column here by typing:</p>
<pre class="code-pre custom_prefix"><code langs=""><ul class="prefixed"><li class="line" prefix="postgres=#">ALTER TABLE playground DROP last_maint;
</li></ul></code></pre>
<h2 id="how-to-update-data-in-a-table">How To Update Data in a Table</h2>

<p>We know how to add records to a table and how to delete them, but we haven't covered how to modify existing entries yet.</p>

<p>You can update the values of an existing entry by querying for the record you want and setting the column to the value you wish to use.  We can query for the "swing" record (this will match <em>every</em> swing in our table) and change its color to "red".  This could be useful if we gave it a paint job:</p>
<pre class="code-pre custom_prefix"><code langs=""><ul class="prefixed"><li class="line" prefix="postgres=#">UPDATE playground SET color = 'red' WHERE type = 'swing';
</li></ul></code></pre>
<p>We can verify that the operation was successful by querying our data again:</p>
<pre class="code-pre custom_prefix"><code langs=""><ul class="prefixed"><li class="line" prefix="postgres=#">SELECT * FROM playground;
</li></ul></code></pre><pre class="code-pre "><code langs=""><div class="secondary-code-label " title="Output:">Output:</div> equip_id | type  | color | location  | install_date 
----------+-------+-------+-----------+--------------
        2 | swing | red   | northwest | 2010-08-16
(1 row)
</code></pre>
<p>As you can see, our slide is now registered as being red.</p>

<h2 id="conclusion">Conclusion</h2>

<p>You are now set up with PostgreSQL on your CentOS 7 server.  However, there is still <em>much</em> more to learn with Postgres. Although many of them were written with Ubuntu in mind, these tutorials should be helpful in learning more about PostgreSQL:</p>

<ul>
<li><a href="https://digitalocean.com/community/articles/sqlite-vs-mysql-vs-postgresql-a-comparison-of-relational-database-management-systems">A comparison of relational database management systems</a></li>
<li><a href="https://digitalocean.com/community/articles/how-to-create-remove-manage-tables-in-postgresql-on-a-cloud-server">Learn how to create and manage tables with Postgres</a></li>
<li><a href="https://digitalocean.com/community/articles/how-to-use-roles-and-manage-grant-permissions-in-postgresql-on-a-vps--2">Get better at managing roles and permissions</a></li>
<li><a href="https://digitalocean.com/community/articles/how-to-create-data-queries-in-postgresql-by-using-the-select-command">Craft queries with Postgres with Select</a></li>
<li><a href="https://digitalocean.com/community/articles/how-to-install-and-use-phppgadmin-on-ubuntu-12-04">Install phpPgAdmin to administer databases from a web interface on Ubuntu</a></li>
<li><a href="https://digitalocean.com/community/articles/how-to-secure-postgresql-on-an-ubuntu-vps">Learn how to secure PostgreSQL on Ubuntu</a></li>
<li><a href="https://digitalocean.com/community/articles/how-to-set-up-master-slave-replication-on-postgresql-on-an-ubuntu-12-04-vps">Set up master-slave replication with Postgres on Ubuntu</a></li>
<li><a href="https://digitalocean.com/community/articles/how-to-backup-postgresql-databases-on-an-ubuntu-vps">Learn how to backup a Postgres database on Ubuntu</a></li>
</ul>

    