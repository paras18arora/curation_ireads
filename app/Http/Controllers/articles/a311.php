<link rel='stylesheet' href='digital_ocean.css'><br> <br> 
      <div><h3>What are MySQL and MariaDB?</h3>

<p>MySQL and MariaDB are relational database management systems.  These tools can be used on your VPS server to manage the data from many different programs.  Both implement forms of the SQL querying language, and either can be used on a cloud server.</p>
	
<p>This guide will cover how to create a database using these tools.  This is a fundamental skill needed to manage your data in an SQL environment.  We will also cover several other aspects of database management.</p>
	
<p>For the purposes of this guide, we will be using an Ubuntu 12.04 server on a small droplet.  However, everything should translate directly to other distributions.</p>


<h2>How to Create a Database in MySQL and MariaDB</h2>

<p>To begin, sign into MySQL or MariaDB with the following command:</p>

<pre>mysql -u root -p</pre>

<p>Enter the administrator password you set up during installation.  You will be given a MySQL/MariaDB prompt.</p>
	
<p>We can now create a database by typing the following command:</p>
	
<pre>CREATE DATABASE <span class="highlight">new_database</span>;</pre>

<pre>Query OK, 1 row affected (0.00 sec)</pre>

<p>To avoid errors in the event that the database name we've chosen already exists, use the following command:</p>
	
<pre>CREATE DATABASE IF NOT EXISTS <span class="highlight">new_database</span>;</pre>

<pre>Query OK, 1 row affected, 1 warning (0.01 sec)</pre>

<p>The warning indicates that the database already existed and no new database was created.</p>
	
<p>If we leave the "IF NOT EXISTS" option off, and the database already exists, we will receive the following error:</p>
	
<pre>ERROR 1007 (HY000): Can't create database 'other_database'; database exists</pre>


<h2>How to View Databases in MySQL and MariaDB</h2>

<p>To view a list of the current databases that you have created, use the following command:</p>
	
<pre>SHOW DATABASES;</pre>

<pre>+--------------------+
| Database           |
+--------------------+
| information_schema |
| mysql              |
| new_database       |
| other_database     |
| performance_schema |
+--------------------+
5 rows in set (0.00 sec)</pre>

<p>The "information_schema", "performance_schema", and "mysql" databases are set up by default in most cases and should be left alone unless you know what you are doing.</p>


<h2>How to Change Databases in MySQL and MariaDB</h2>

<p>Any operations performed without explicitly specifying a database will be performed on the currently selected database.</p>
	
<p>Find out which database is currently selected with the following command:</p>
	
<pre>SELECT database();</pre>

<pre>+------------+
| database() |
+------------+
| NULL       |
+------------+
1 row in set (0.01 sec)</pre>

<p>We have received a result of "null".  This means that no database is currently selected.</p>
	
<p>To select a database to use for subsequent operations, use the following command:</p>
	
<pre>USE <span class="highlight">new_database</span>;</pre>

<pre>Database changed</pre>

<p>We can see that the database has been selected by re-issuing the command we ran previously:</p>
	
<pre>SELECT database();</pre>	

<pre>+--------------+
| database()   |
+--------------+
| new_database |
+--------------+
1 row in set (0.00 sec)</pre>

<h2>How to Delete a Database in MySQL and MariaDB</h2>

<p>To delete a database in MySQL or MariaDB, use the following command:</p>
	
<pre>DROP DATABASE <span class="highlight">new_database</span>;</pre>

<pre>Query OK, 0 rows affected (0.00 sec)</pre>

<p><strong>This operation cannot be reversed! Make certain you wish to delete before pressing enter!</strong></p>
	
<p>If this command is executed on a database that does not exist, the following error message will be given:</p>
	
<pre>DROP DATABASE <span class="highlight">new_database</span>;</pre>

<pre>ERROR 1008 (HY000): Can't drop database 'new_database'; database doesn't exist</pre>

<p>To prevent this error, and ensure that the command executes successfully regardless of if the database exists, call it with the following syntax:</p>
	
<pre>DROP DATABASE IF EXISTS <span class="highlight">new_database</span>;</pre>

<pre>Query OK, 0 rows affected, 1 warning (0.00 sec)</pre>

<p>The warning indicates that the database did not exist, but the command executes successfully anyways.</p>

<h2>Conclusion</h2>

<p>You now have the basic skills necessary to manage databases using MySQL and MariaDB.  There are many things to learn, but you now have a good starting point to manage your databases.</p>
	
<p>To <a href="https://indiareads/community/articles/how-to-create-a-table-in-mysql-and-mariadb-on-an-ubuntu-cloud-server">learn about tables in MySQL and MariaDB</a>, click here.</p>
	
<div class="author">By Justin Ellingwood</div></div>
    