<link rel='stylesheet' href='digital_ocean.css'><br><img src=https://community-cdn-digitalocean-com.global.ssl.fastly.net/assets/tutorials/images/large/postgresql_twitter.png?1462909712/> <br> 
      <h3 id="introduction">Introduction</h3>

<p>Relational database management systems are a key component of many web sites and applications.  They provide a structured way to store, organize, and access information.</p>

<p><strong>PostgreSQL</strong>, or Postgres, is a relational database management system that provides an implementation of the SQL querying language.  It is a popular choice for many small and large projects and has the advantage of being standards-compliant and having many advanced features like reliable transactions and concurrency without read locks.</p>

<p>In this guide, we will demonstrate how to install Postgres on an Ubuntu 16.04 VPS instance and go over some basic ways to use it.</p>

<h2 id="installation">Installation</h2>

<p>Ubuntu's default repositories contain Postgres packages, so we can install these easily using the <code>apt</code> packaging system.</p>

<p>Since this is our first time using <code>apt</code> in this session, we need to refresh our local package index.  We can then install the Postgres package and a <code>-contrib</code> package that adds some additional utilities and functionality:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo apt-get update
</li><li class="line" prefix="$">sudo apt-get install postgresql postgresql-contrib
</li></ul></code></pre>
<p>Now that our software is installed, we can go over how it works and how it may be different from similar database management systems you may have used.</p>

<h2 id="using-postgresql-roles-and-databases">Using PostgreSQL Roles and Databases</h2>

<p>By default, Postgres uses a concept called "roles" to handle in authentication and authorization.  These are, in some ways, similar to regular Unix-style accounts, but Postgres does not distinguish between users and groups and instead prefers the more flexible term "role".</p>

<p>Upon installation Postgres is set up to use <strong>ident</strong> authentication, which means that it associates Postgres roles with a matching Unix/Linux system account.  If a role exists within Postgres, a Unix/Linux username with the same name will be able to sign in as that role.</p>

<p>There are a few ways to utilize this account to access Postgres.</p>

<h3 id="switching-over-to-the-postgres-account">Switching Over to the postgres Account</h3>

<p>The installation procedure created a user account called <code>postgres</code> that is associated with the default Postgres role.  In order to use Postgres, we can log into that account.</p>

<p>Switch over to the <code>postgres</code> account on your server by typing:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo -i -u postgres
</li></ul></code></pre>
<p>You can now access a Postgres prompt immediately by typing:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">psql
</li></ul></code></pre>
<p>You will be logged in and able to interact with the database management system right away.</p>

<p>Exit out of the PostgreSQL prompt by typing:</p>
<pre class="code-pre custom_prefix"><code langs=""><ul class="prefixed"><li class="line" prefix="postgres=#">\q
</li></ul></code></pre>
<p>You should now be back in the <code>postgres</code> Linux command prompt.</p>

<h3 id="accessing-a-postgres-prompt-without-switching-accounts">Accessing a Postgres Prompt Without Switching Accounts</h3>

<p>You can also run the command you'd like with the <code>postgres</code> account directly with <code>sudo</code>.</p>

<p>For instance, in the last example, we just wanted to get to a Postgres prompt.  We could do this in one step by running the single command <code>psql</code> as the <code>postgres</code> user with <code>sudo</code> like this:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo -u postgres psql
</li></ul></code></pre>
<p>This will log you directly into Postgres without the intermediary <code>bash</code> shell in between.</p>

<p>Again, you can exit the interactive Postgres session by typing:</p>
<pre class="code-pre custom_prefix"><code langs=""><ul class="prefixed"><li class="line" prefix="postgres=#">\q
</li></ul></code></pre>
<h2 id="create-a-new-role">Create a New Role</h2>

<p>Currently, we just have the <code>postgres</code> role configured within the database.  We can create new roles from the command line with the <code>createrole</code> command.  The <code>--interactive</code> flag will prompt you for the necessary values.</p>

<p>If you are logged in as the <code>postgres</code> account, you can create a new user by typing:</p>
<pre class="code-pre custom_prefix"><code langs=""><ul class="prefixed"><li class="line" prefix="postgres@server:~$">createuser --interactive
</li></ul></code></pre>
<p>If, instead, you prefer to use <code>sudo</code> for each command without switching from your normal account, you can type:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo -u postgres createuser --interactive
</li></ul></code></pre>
<p>The script will prompt you with some choices and, based on your responses, execute the correct Postgres commands to create a user to your specifications.</p>
<pre class="code-pre "><code langs=""><div class="secondary-code-label " title="Output">Output</div>Enter name of role to add: <span class="highlight">sammy</span>
Shall the new role be a superuser? (y/n) <span class="highlight">y</span>
</code></pre>
<p>You can get more control by passing some additional flags.  Check out the options by looking at the <code>man</code> page:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">man createuser
</li></ul></code></pre>
<h2 id="create-a-new-database">Create a New Database</h2>

<p>By default, another assumption that the Postgres authentication system makes is that there will be an database with the same name as the role being used to login, which the role has access to.</p>

<p>So if in the last section, we created a user called <code><span class="highlight">sammy</span></code>, that role will attempt to connect to a database which is also called <code>sammy</code> by default.  You can create the appropriate database with the <code>createdb</code> command.</p>

<p>If you are logged in as the <code>postgres</code> account, you would type something like:</p>
<pre class="code-pre custom_prefix"><code langs=""><ul class="prefixed"><li class="line" prefix="postgres@server:~$">createdb <span class="highlight">sammy</span>
</li></ul></code></pre>
<p>If, instead, you prefer to use <code>sudo</code> for each command without switching from your normal account, you would type:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo -u postgres createdb <span class="highlight">sammy</span>
</li></ul></code></pre>
<h2 id="open-a-postgres-prompt-with-the-new-role">Open a Postgres Prompt with the New Role</h2>

<p>To log in with <code>ident</code> based authentication, you'll need a Linux user with the same name as your Postgres role and database.</p>

<p>If you don't have a matching Linux user available, you can create one with the <code>adduser</code> command.  You will have to do this from an account with <code>sudo</code> privileges (not logged in as the <code>postgres</code> user):</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo adduser <span class="highlight">sammy</span>
</li></ul></code></pre>
<p>Once you have the appropriate account available, you can either switch over and connect to the database by typing:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo -i -u <span class="highlight">sammy</span>
</li><li class="line" prefix="$">psql
</li></ul></code></pre>
<p>Or, you can do this inline:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo -u <span class="highlight">sammy</span> psql
</li></ul></code></pre>
<p>You will be logged in automatically assuming that all of the components have been properly configured.</p>

<p>If you want your user to connect to a different database, you can do so by specifying the database like this:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">psql -d <span class="highlight">postgres</span>
</li></ul></code></pre>
<p>Once logged in, you can get check your current connection information by typing:</p>
<pre class="code-pre custom_prefix"><code langs=""><ul class="prefixed"><li class="line" prefix="sammy=#">\conninfo
</li></ul></code></pre><pre class="code-pre "><code langs=""><div class="secondary-code-label " title="Output">Output</div>You are connected to database "sammy" as user "sammy" via socket in "/var/run/postgresql" at port "5432".
</code></pre>
<p>This can be useful if you are connecting to non-default databases or with non-default users.</p>

<h2 id="create-and-delete-tables">Create and Delete Tables</h2>

<p>Now that you know how to connect to the PostgreSQL database system, we can to go over how to complete some basic tasks.</p>

<p>First, we can create a table to store some data.  Let's create a table that describes playground equipment.</p>

<p>The basic syntax for this command is something like this:</p>
<pre class="code-pre "><code langs="">CREATE TABLE <span class="highlight">table_name</span> (
    <span class="highlight">column_name1 col_type</span> (<span class="highlight">field_length</span>) <span class="highlight">column_constraints</span>,
    <span class="highlight">column_name2 col_type</span> (<span class="highlight">field_length</span>),
    <span class="highlight">column_name3 col_type</span> (<span class="highlight">field_length</span>)
);
</code></pre>
<p>As you can see, we give the table a name, and then define the columns that we want, as well as the column type and the max length of the field data.  We can also optionally add table constraints for each column.</p>

<p>You can learn more about <a href="https://digitalocean.com/community/articles/how-to-create-remove-manage-tables-in-postgresql-on-a-cloud-server">how to create and manage tables in Postgres</a> here.</p>

<p>For our purposes, we're going to create a simple table like this:</p>
<pre class="code-pre "><code langs="">CREATE TABLE playground (
    equip_id serial PRIMARY KEY,
    type varchar (50) NOT NULL,
    color varchar (25) NOT NULL,
    location varchar(25) check (location in ('north', 'south', 'west', 'east', 'northeast', 'southeast', 'southwest', 'northwest')),
    install_date date
);
</code></pre>
<p>We have made a playground table that inventories the equipment that we have.  This starts with an equipment ID, which is of the <code>serial</code> type.  This data type is an auto-incrementing integer.  We have given this column the constraint of <code>primary key</code> which means that the values must be unique and not null.</p>

<p>For two of our columns (<code>equip_id</code> and <code>install_date</code>), we have not given a field length.  This is because some column types don't require a set length because the length is implied by the type.</p>

<p>We then give columns for the equipment <code>type</code> and <code>color</code>, each of which cannot be empty.  We create a <code>location</code> column and create a constraint that requires the value to be one of eight possible values.  The last column is a date column that records the date that we installed the equipment.</p>

<p>We can see our new table by typing:</p>
<pre class="code-pre custom_prefix"><code langs=""><ul class="prefixed"><li class="line" prefix="sammy=#">\d
</li></ul></code></pre><pre class="code-pre "><code langs=""><div class="secondary-code-label " title="Output">Output</div>                  List of relations
 Schema |          Name           |   Type   | Owner 
--------+-------------------------+----------+-------
 public | playground              | table    | sammy
 public | playground_equip_id_seq | sequence | sammy
(2 rows)
</code></pre>
<p>Our playground table is here, but we also have something called <code>playground_equip_id_seq</code> that is of the type <code>sequence</code>.  This is a representation of the <code>serial</code> type we gave our <code>equip_id</code> column.  This keeps track of the next number in the sequence and is created automatically for columns of this type.</p>

<p>If you want to see just the table without the sequence, you can type:</p>
<pre class="code-pre custom_prefix"><code langs=""><ul class="prefixed"><li class="line" prefix="sammy=#">\dt
</li></ul></code></pre><pre class="code-pre "><code langs=""><div class="secondary-code-label " title="Output">Output</div>          List of relations
 Schema |    Name    | Type  | Owner 
--------+------------+-------+-------
 public | playground | table | sammy
(1 row)
</code></pre>
<h2 id="add-query-and-delete-data-in-a-table">Add, Query, and Delete Data in a Table</h2>

<p>Now that we have a table, we can insert some data into it.</p>

<p>Let's add a slide and a swing.  We do this by calling the table we're wanting to add to, naming the columns and then providing data for each column.  Our slide and swing could be added like this:</p>
<pre class="code-pre custom_prefix"><code langs=""><ul class="prefixed"><li class="line" prefix="sammy=#">INSERT INTO playground (type, color, location, install_date) VALUES ('slide', 'blue', 'south', '2014-04-28');
</li><li class="line" prefix="sammy=#">INSERT INTO playground (type, color, location, install_date) VALUES ('swing', 'yellow', 'northwest', '2010-08-16');
</li></ul></code></pre>
<p>You should take care when entering the data to avoid a few common hangups.  First, keep in mind that the column names should not be quoted, but the column <em>values</em> that you're entering do need quotes.</p>

<p>Another thing to keep in mind is that we do not enter a value for the <code>equip_id</code> column.  This is because this is auto-generated whenever a new row in the table is created.</p>

<p>We can then get back the information we've added by typing:</p>
<pre class="code-pre custom_prefix"><code langs=""><ul class="prefixed"><li class="line" prefix="sammy=#">SELECT * FROM playground;
</li></ul></code></pre><pre class="code-pre "><code langs=""><div class="secondary-code-label " title="Output">Output</div> equip_id | type  | color  | location  | install_date 
----------+-------+--------+-----------+--------------
        1 | slide | blue   | south     | 2014-04-28
        2 | swing | yellow | northwest | 2010-08-16
(2 rows)
</code></pre>
<p>Here, you can see that our <code>equip_id</code> has been filled in successfully and that all of our other data has been organized correctly.</p>

<p>If the slide on the playground breaks and we have to remove it, we can also remove the row from our table by typing:</p>
<pre class="code-pre custom_prefix"><code langs=""><ul class="prefixed"><li class="line" prefix="sammy=#">DELETE FROM playground WHERE type = 'slide';
</li></ul></code></pre>
<p>If we query our table again, we will see our slide is no longer a part of the table:</p>
<pre class="code-pre custom_prefix"><code langs=""><ul class="prefixed"><li class="line" prefix="sammy=#">SELECT * FROM playground;
</li></ul></code></pre><pre class="code-pre "><code langs=""><div class="secondary-code-label " title="Output">Output</div> equip_id | type  | color  | location  | install_date 
----------+-------+--------+-----------+--------------
        2 | swing | yellow | northwest | 2010-08-16
(1 row)
</code></pre>
<h2 id="how-to-add-and-delete-columns-from-a-table">How To Add and Delete Columns from a Table</h2>

<p>If we want to modify a table after it has been created to add an additional column, we can do that easily.</p>

<p>We can add a column to show the last maintenance visit for each piece of equipment by typing:</p>
<pre class="code-pre custom_prefix"><code langs=""><ul class="prefixed"><li class="line" prefix="sammy=#">ALTER TABLE playground ADD last_maint date;
</li></ul></code></pre>
<p>If you view your table information again, you will see the new column has been added (but no data has been entered):</p>
<pre class="code-pre custom_prefix"><code langs=""><ul class="prefixed"><li class="line" prefix="sammy=#">SELECT * FROM playground;
</li></ul></code></pre><pre class="code-pre "><code langs=""><div class="secondary-code-label " title="Output">Output</div> equip_id | type  | color  | location  | install_date | last_maint 
----------+-------+--------+-----------+--------------+------------
        2 | swing | yellow | northwest | 2010-08-16   | 
(1 row)
</code></pre>
<p>We can delete a column just as easily.  If we find that our work crew uses a separate tool to keep track of maintenance history, we can get rid of the column here by typing:</p>
<pre class="code-pre custom_prefix"><code langs=""><ul class="prefixed"><li class="line" prefix="sammy=#">ALTER TABLE playground DROP last_maint;
</li></ul></code></pre>
<h2 id="how-to-update-data-in-a-table">How To Update Data in a Table</h2>

<p>We know how to add records to a table and how to delete them, but we haven't covered how to modify existing entries yet.</p>

<p>You can update the values of an existing entry by querying for the record you want and setting the column to the value you wish to use.  We can query for the "swing" record (this will match <em>every</em> swing in our table) and change its color to "red".  This could be useful if we gave the swing set a paint job:</p>
<pre class="code-pre custom_prefix"><code langs=""><ul class="prefixed"><li class="line" prefix="sammy=#">UPDATE playground SET color = 'red' WHERE type = 'swing';
</li></ul></code></pre>
<p>We can verify that the operation was successful by querying our data again:</p>
<pre class="code-pre custom_prefix"><code langs=""><ul class="prefixed"><li class="line" prefix="sammy=#">SELECT * FROM playground;
</li></ul></code></pre><pre class="code-pre "><code langs=""><div class="secondary-code-label " title="Output">Output</div> equip_id | type  | color | location  | install_date 
----------+-------+-------+-----------+--------------
        2 | swing | red   | northwest | 2010-08-16
(1 row)
</code></pre>
<p>As you can see, our slide is now registered as being red.</p>

<h2 id="conclusion">Conclusion</h2>

<p>You are now set up with PostgreSQL on your Ubuntu 16.04 server.  However, there is still <em>much</em> more to learn with Postgres.  Here are some more guides that cover how to use Postgres:</p>

<ul>
<li><a href="https://digitalocean.com/community/articles/sqlite-vs-mysql-vs-postgresql-a-comparison-of-relational-database-management-systems">A comparison of relational database management systems</a></li>
<li><a href="https://digitalocean.com/community/articles/how-to-create-remove-manage-tables-in-postgresql-on-a-cloud-server">Learn how to create and manage tables with Postgres</a></li>
<li><a href="https://digitalocean.com/community/articles/how-to-use-roles-and-manage-grant-permissions-in-postgresql-on-a-vps--2">Get better at managing roles and permissions</a></li>
<li><a href="https://digitalocean.com/community/articles/how-to-create-data-queries-in-postgresql-by-using-the-select-command">Craft queries with Postgres with Select</a></li>
<li><a href="https://digitalocean.com/community/articles/how-to-secure-postgresql-on-an-ubuntu-vps">Learn how to secure PostgreSQL</a></li>
<li><a href="https://digitalocean.com/community/articles/how-to-backup-postgresql-databases-on-an-ubuntu-vps">Learn how to backup a Postgres database</a></li>
</ul>

    