<link rel='stylesheet' href='digital_ocean.css'><br> <br> 
      <div><h3>What is MySQL and MariaDB</h3>

<p>MySQL and MariaDB are two popular database systems that use the SQL language.  Many applications on Ubuntu use MySQL or MariaDB to manage their information.</p>
	
<p>In this article, we will discuss how to create tables within the MySQL or MariaDB interface.  We will be performing these tasks on an Ubuntu 12.04 VPS server, but most of the commands should be the same for any Ubuntu machine.</p>

	
<h2>How to Install MySQL and MariaDB on Ubuntu</h2>

<p>MySQL and MariaDB have the same command syntax, so either database system will work for this guide.</p>
	
<p>To install MySQL on Ubuntu, use the following command:</p>
	
<pre>sudo apt-get install mysql-server</pre>

<p>To install MariaDB on Ubuntu 12.04, type the following into the terminal:</p>
	
<pre>sudo apt-get update
sudo apt-get install python-software-properties
sudo apt-key adv --recv-keys --keyserver keyserver.ubuntu.com 0xcbcb082a1bb943db
sudo add-apt-repository 'deb http://repo.maxindo.net.id/mariadb/repo/5.5/ubuntu precise main'
sudo apt-get update
sudo apt-get install mariadb-server</pre>
	
<p>For more information on <a href="https://indiareads/community/articles/how-to-install-linux-apache-mysql-php-lamp-stack-on-ubuntu">how to install MySQL on Ubuntu</a> follow this guide.</p>


<h2>Creating a Database in MySQL and MariaDB</h2>

<p>Before we can look at tables, we need to configure an initial database environment within MySQL.</p>
	
<p>Log into MySQL or MariaDB using the following command:</p>
	
<pre>mysql -u root -p</pre>

<p>Type in the password you set up during installation to continue.</p>
	
<p>We will create a database to learn on called "playground".  Create the database with the following command:</p>
	
<pre>CREATE DATABASE playground;</pre>

<p>We will switch to the new database with the following command:</p>
	
<pre>USE playground;</pre>

<p>We are now ready to begin learning about tables.</p>
	
	
<h2>How to Create a Table in MySQL and MariaDB</h2>

<p>We have named our database "playground", so now let's create a table with this database that describes equipment found in a playground.</p>
	
<p>The table creation syntax follows this convention:</p>
	
<pre>CREATE TABLE [IF NOT EXISTS] name_of_table (list_of_table_columns) [engine=database_engine]</pre>

<p>The sections in brackets ("[" and "]") are optional.  The "IF NOT EXISTS" option forces the table creation to abort if there is already a table with the same name.  It is important to use this option to avoid getting an error if the table is already created.</p>
	
<p>The "engine=database_engine" section is for choosing a specific type of table to optimize your information handling.  This is outside of the scope of this article and a good default (InnoDB) is selected if this option is omitted.</p>

<p>We will explain the different fields needed in the columns section in a moment, but for now, let's create our table:</p>
	
<pre>CREATE TABLE IF NOT EXISTS equipment (
    equip_id int(5) NOT NULL AUTO_INCREMENT,
    type varchar(50) DEFAULT NULL,
    install_date DATE DEFAULT NULL,
    color varchar(20) DEFAULT NULL,
    working bool DEFAULT NULL,
    location varchar(250) DEFAULT NULL,
    PRIMARY KEY(equip_id)
    );</pre>
<pre>Query OK, 0 rows affected (0.03 sec)</pre>


<h3>Defining Columns</h3>

<p>To see what we've accomplished, use the following command to print out the columns of our new table:</p>
	
<pre>show columns in equipment;</pre>

<pre>+--------------+--------------+------+-----+---------+----------------+
| Field        | Type         | Null | Key | Default | Extra          | |+--------------+--------------+------+-----+---------+----------------+
| equip_id     | int(5)       | NO   | PRI | NULL    | auto_increment |
| type         | varchar(50)  | YES  |     | NULL    |                |
| install_date | date         | YES  |     | NULL    |                |
| color        | varchar(20)  | YES  |     | NULL    |                |
| working      | tinyint(1)   | YES  |     | NULL    |                |
| location     | varchar(250) | YES  |     | NULL    |                |
+--------------+--------------+------+-----+---------+----------------+
6 rows in set (0.00 sec)</pre>

<p>The results give us some insight into the fields necessary to define a column.  Each column description in the table creation command is separated by a comma, and follows this convention:</p>
	
<pre>Column_Name Data_Type[(size_of_data)] [NULL or NOT NULL] [DEFAULT default_value] [AUTO_INCREMENT]</pre>

<p>These are the values of each column definition:</p>
	
	<ul>
	<li><strong>Column Name</strong>: Describes the attribute being assigned.  For instance, our first column is called "equip_id" because it will hold the unique ID number associated with each piece of equipment.</li>
	
	<li><strong>Data Type</strong>: Specifies the type of data the column will hold.  Can be any of MySQL's data types.  For instance, "int" specifies that only integer values will be accepted, while "varchar" is used to hold string values.  There are many data types, but these are outside of the scope of this article.

	<em>Note: Most data types need a size value in parentheses to specify the maximum amount of space needed to hold the values for that field.</em></li>
	
	<li><strong>Null</strong>: Defines whether null is a valid value for that field.  Can be "null" or "not null".</li>
	
	<li><strong>Default Value</strong>: Sets the initial value of all newly created records that do no specify a value. The "default" keyword is followed by the value.</li>
	
	<li><strong>auto_increment</strong>: MySQL will handle the sequential numbering internally of any column marked with this option, in order to provide a unique value for each record.</li>
	</ul>
	
<p>Finally, before closing the column declarations, you need to specify which columns to use as the primary key by typing "PRIMARY KEY (columns_to_be_primary_keys).</p>
	
<p>We used our "equip_id" column as the primary key because the "auto_increment" option guarantees the value to be unique, which is a requirement of a primary key.</p>


<h2>How to Insert Data Into a MySQL or MariaDB Table</h2>

<p>Let's insert a record into our table.  To do this, we'll use the following syntax:</p>
	
<pre>INSERT INTO table_name (field1, field2, ...) VALUES (value1, value2, ...);</pre>

<p>Every string value must be placed in quotation marks.  Every column with "auto_increment" set does not need a value, as the database will provide it with the next sequential number.</p>
	
<p>We can add a slide to our playground equipment table like this:</p>
	
<pre>INSERT INTO equipment (type, install_date, color, working, location)
VALUES
("Slide", Now(), "blue", 1, "Southwest Corner");</pre>

<p>We used a special function called "Now()" that fills in the current date for the date column.</p>
	
<p>To see the information, query the table.  The asterisk (*) is a special wildcard character that matches everything.  This query selects everything in the equipment table:</p>
	
<pre>SELECT * FROM equipment;</pre>

<pre>+----------+-------+--------------+-------+---------+------------------+
| equip_id | type  | install_date | color | working | location         |
+----------+-------+--------------+-------+---------+------------------+
|        1 | Slide | 2013-07-26   | blue  |       1 | Southwest Corner |
+----------+-------+--------------+-------+---------+------------------+
1 row in set (0.00 sec)</pre>

<p>Let's add another entry:</p>
	
<pre>INSERT INTO equipment (type, install_date, color, working, location)
VALUES
("Swing", Now(), "green", 1, "Northwest Corner");</pre>

<p>We can see that our new data is present in the table:</p>
	
<pre>SELECT * FROM equipment;</pre>

<pre>+----------+-------+--------------+-------+---------+------------------+
| equip_id | type  | install_date | color | working | location         |
+----------+-------+--------------+-------+---------+------------------+
|        1 | Slide | 2013-07-26   | blue  |       1 | Southwest Corner |
|        2 | Swing | 2013-07-26   | green |       1 | Northwest Corner |
+----------+-------+--------------+-------+---------+------------------+
2 rows in set (0.00 sec)</pre>


<h2>How to Delete Tables in MySQL and MariaDB</h2>

<p>To delete a table we can use the following syntax:</p>
	
<pre>DROP TABLE table_name;</pre>

<p>Be very careful with this command, because once the table is deleted, the data inside cannot be recovered.</p>
	
<p>First, let's view our current table so that we can establish what the "show tables" command looks like:</p>
	
<pre>SHOW tables;</pre>

<pre>+----------------------+
| Tables_in_playground |
+----------------------+
| equipment            |
+----------------------+
1 row in set (0.00 sec)</pre>	
	
<p>Let's delete our equipment table:</p>
	
<pre>DROP TABLE equipment;</pre>

<p>And now, check the "playground" tables list again:</p>
	
<pre>SHOW tables;</pre>

<pre>Empty set (0.00 sec)</pre>

<p>We no longer have any tables in the "playground" database, so the operation was successful.</p>


<h2>Conclusion</h2>

<p>You should now be comfortable with performing basic operations on a table.</p>
	
<p>These are fundamental skills needed to manage MySQL or MariaDB.  Gaining familiarity with them now will pay off as you dive into other areas of database management.</p>

<div class="author">By Justin Ellingwood</div></div>
    