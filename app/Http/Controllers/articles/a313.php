<link rel='stylesheet' href='digital_ocean.css'><br> <br> 
      <div><h3>Introduction</h3>

<p>There are many implementations of the SQL database language available on Linux and Unix-like systems.  MySQL and MariaDB are two popular options for deploying relational databases in server environments.</p>
	
<p>However, like most software, these tools can be security liabilities if they are configured incorrectly.  This tutorial will guide you through some basic steps you can take to secure your MariaDB or MySQL databases, and ensure that they are not an open door into your VPS.</p>
	
<p>For the sake of simplicity and illustration, we will use the MySQL server on an Ubuntu 12.04 VPS instance.  However, these techniques can be applied to other Linux distributions and can be used with MariaDB as well.</p>


<h2>Initial Setup</h2>

<p>MySQL gives you an opportunity to take the first step towards security during installation.  It will request that you set a root password.</p>
	
<pre>sudo apt-get install mysql-server</pre>

<pre> ?????????????????????????? Configuring mysql-server-5.5 ???????????????????????????
 ? While not mandatory, it is highly recommended that you set a password for the   ? 
 ? MySQL administrative "root" user.                                               ? 
 ?                                                                                 ? 
 ? If this field is left blank, the password will not be changed.                  ? 
 ?                                                                                 ? 
 ? New password for the MySQL "root" user:                                         ? 
 ?                                                                                 ? 
 ? _______________________________________________________________________________ ? 
 ?                                                                                 ? 
 ?                                     <Ok>                                        ? 
 ?                                                                                 ? 
 ???????????????????????????????????????????????????????????????????????????????????</Ok></pre>
 
<p>You can always set the root password at a later time, but there is no reason to skip this step, so you should secure your administrator account from the very beginning.</p>
 	
<p>Once the installation is complete, we should run a few included scripts.  First, we will use the "mysql_install_db" script to create a directory layout for our databases.</p>
 	
<pre>sudo mysql_install_db</pre>

<p>Next, run the script called "mysql_secure_installation".  This will guide us through some procedures that will remove some defaults that are dangerous to use in a production environment.</p>
 	
 <pre>sudo mysql_secure_installation</pre>
 
<p>It will first prompt you for the root password you set up during installation.  Immediately following, you will be asked a series of questions, beginning with if you'd like to change the root password.</p>
 	
<p>This is another opportunity to change your password to something secure if you have not done so already.</p>
 	
 <p>You should answer "Y" (for yes) to all of the remaining questions.</p>
 	
 <p>This will remove the ability for anyone to log into MySQL by default, disable logging in remotely with the administrator account, remove some test databases that are insecure, and update the running MySQL instance to reflect these changes.</p>
 	
 
 <h2>Security Considerations</h2>
 
 <p>The overarching theme of securing MySQL (and almost any other system) is that access should be granted only when absolutely necessary.  Your data safety sometimes comes down to a balance between convenience and security.</p>
 	
<p>In this guide, we will lean on the side of security, although your specific usage of the database software may lead you to pick and choose from these options.</p>
 	
<h2>Security Through the My.cnf File</h2>

<p>The main configuration file for MySQL is a file called "my.cnf" that is located in the "/etc/mysql/" directory on Ubuntu and the "/etc/" directory on some other VPS.</p>
	
<p>We will change some settings in this file to lock down our MySQL instance.</p>
	
<p>Open the file with root privileges.  Change the directory path as needed if you are following this tutorial on a different system:</p>
	
<pre>sudo nano /etc/mysql/my.cnf</pre>

<p>The first setting that we should check is the "bind-address" setting within the "[mysqld]" section.  This setting should be set to your local loopback network device, which is "127.0.0.1".</p>
	
<pre>bind-address = 127.0.0.1</pre>

<p>This makes sure that MySQL is not accepting connections from anywhere except for the local machine.</p>
	
<p>If you need to access this database from another machine, consider connecting through SSH to do your database querying and administration locally and sending the results through the ssh tunnel.</p>
	
<p>The next hole we will patch is a function that allows access to the underlying filesystem from within MySQL.  This can have severe security implications and should be shut off unless you absolutely need it.</p>
	
<p>In the same section of the file, we will add a directive to disable this ability to load local files:</p>
	
<pre>local-infile=0</pre>

<p>This will disable loading files from the filesystem for users without file level privileges to the database.</p>
	
<p>If we have enough space and are not operating a huge database, it can be helpful to log additional information to keep an eye on suspicious activity.</p>
	
<p>Logging too much can create a performance hit, so this is something you need to weigh carefully.</p>
	
<p>You can set the log variable within the same "[mysqld]" section that we've been adding to.</p>
	
<pre>log=/var/log/mysql-logfile</pre>

<p>Make sure that the MySQL log, error log, and mysql log directory are not world readable:</p>
	
<pre>sudo ls -l /var/log/mysql*</pre>

<pre>-rw-r----- 1 mysql adm    0 Jul 23 18:06 /var/log/mysql.err
-rw-r----- 1 mysql adm    0 Jul 23 18:06 /var/log/mysql.log

/var/log/mysql:
total 28
-rw-rw---- 1 mysql adm 20694 Jul 23 19:17 error.log</pre>



<h2>Securing MySQL From Within</h2>

<p>There are a number of steps you can take while using MySQL to improve security.</p>
	
<p>We will be inputting the commands in this section into the MySQL prompt interface, so we need to log in.</p>
	
<pre>mysql -u root -p</pre>

<p>You will be asked for the root password that you set up earlier.</p>
	

<h3>Securing Passwords and Host Associations</h3>	
	
<p>First, make sure there are no users without a password or a host association in MySQL:</p>
	
<pre>SELECT User,Host,Password FROM mysql.user;</pre>

<pre>+------------------+-----------+-------------------------------------------+
| user             | host      | password                                  |
+------------------+-----------+-------------------------------------------+
| root             | localhost | *DE06E242B88EFB1FE4B5083587C260BACB2A6158 |
| demo-user        | %         |                                           |
| root             | 127.0.0.1 | *DE06E242B88EFB1FE4B5083587C260BACB2A6158 |
| root             | ::1       | *DE06E242B88EFB1FE4B5083587C260BACB2A6158 |
| debian-sys-maint | localhost | *ECE81E38F064E50419F3074004A8352B6A683390 |
+------------------+-----------+-------------------------------------------+
5 rows in set (0.00 sec)</pre>

<p>As you can see, in our example set up, the user "demo-user" has no password and is valid regardless of what host he is on.  This is very insecure.</p>
	
<p>We can set a password for the user with this command.  Change "<span class="highlight">newPassWord</span>" to reflect the password you wish to assign.</p>
	
<pre>UPDATE mysql.user SET Password=PASSWORD('<span class="highlight">newPassWord</span>') WHERE User="<span class="highlight">demo-user</span>";</pre>

<p>If we check the User table again, we will see that the demo user now has a password:</p>
	
<pre>SELECT User,Host,Password FROM mysql.user;</pre>
<pre>+------------------+-----------+-------------------------------------------+
| user             | host      | password                                  |
+------------------+-----------+-------------------------------------------+
| root             | localhost | *DE06E242B88EFB1FE4B5083587C260BACB2A6158 |
| demo-user        | %         | *D8DECEC305209EEFEC43008E1D420E1AA06B19E0 |
| root             | 127.0.0.1 | *DE06E242B88EFB1FE4B5083587C260BACB2A6158 |
| root             | ::1       | *DE06E242B88EFB1FE4B5083587C260BACB2A6158 |
| debian-sys-maint | localhost | *ECE81E38F064E50419F3074004A8352B6A683390 |
+------------------+-----------+-------------------------------------------+
5 rows in set (0.00 sec)</pre>

<p>If you look in the "Host" field, you will see that we still have a "%", which is a wildcard that means any host.  This is not what we want.  Let's change that to be "localhost":</p>
	
<pre>UPDATE mysql.user SET Host='localhost' WHERE User="<span class="highlight">demo-user</span>";</pre>

<p>If we check again, we can see that the User table now has the appropriate fields set.</p>
	
<pre>SELECT User,Host,Password FROM mysql.user;</pre>

<p>If our table contains any blank users (it should not at this point since we ran "mysql_secure_installation", but we will cover this anyways), we should remove them.</p>
	
<p>To do this, we can use the following call to delete blank users from the access table:</p>
	
<pre>DELETE FROM mysql.user WHERE User="";</pre>

<p>After we are done modifying the User table, we need to input the following command to implement the new permissions:</p>
	
<pre>FLUSH PRIVILEGES;</pre>

<h3>Implementing Application-Specific Users</h3>

<p>Similar to the practice of running processes within Linux as an isolated user, MySQL benefits from the same kind of isolation.</p>
	
<p>Each application that uses MySQL should have its own user that only has limited privileges and only has access to the databases it needs to run.</p>
	
<p>When we configure a new application to use MySQL, we should create the databases needed by that application:</p>
	
<pre>create database <span class="highlight">testDB</span>;</pre>

<pre>Query OK, 1 row affected (0.00 sec)</pre>

<p>Next, we should create a user to manage that database, and assign it only the privileges it needs.  This will vary by application, and some uses need more open privileges than others.</p>
	
<p>To create a new user, use the following command:</p>
	
<pre>CREATE USER '<span class="highlight">demo-user</span>'@'localhost' IDENTIFIED BY '<span class="highlight">password</span>';</pre>
	
<p>We can grant the new user privileges on the new table with the following command.  See the tutorial on <a href="https://indiareads/community/articles/how-to-create-a-new-user-and-grant-permissions-in-mysql">how to create a new user and grant permissions in MySQL</a> to learn more about specific privileges:</p>
	
<pre>GRANT <span class="highlight">SELECT,UPDATE,DELETE</span> ON <span class="highlight">testDB</span>.* TO '<span class="highlight">demo-user</span>'@'localhost';</pre>

<p>As an example, if we later need to revoke update privileges from the account, we could use the following command:</p>
	
<pre>REVOKE <span class="highlight">UPDATE</span> ON <span class="highlight">testDB</span>.* FROM '<span class="highlight">demo-user</span>'@'localhost';</pre>

<p>If we need all privileges on a certain database, we can specify that with the following:</p>
	
<pre>GRANT ALL ON <span class="highlight">testDB</span>.* TO '<span class="highlight">demo-user</span>'@'localhost';</pre>

<p>To show the current privileges of a user, we first must implement the privileges we specified using the "flush privileges" command.  Then, we can query what grants a user has:</p>
	
<pre>FLUSH PRIVILEGES;
show grants for '<span class="highlight">demo-user</span>'@'localhost';</pre>
<pre>+------------------------------------------------------------------------------------------------------------------+
| Grants for demo-user@localhost                                                                                   |
+------------------------------------------------------------------------------------------------------------------+
| GRANT USAGE ON *.* TO 'demo-user'@'localhost' IDENTIFIED BY PASSWORD '*2470C0C06DEE42FD1618BB99005ADCA2EC9D1E19' |
| GRANT SELECT, UPDATE, DELETE ON `testDB`.* TO 'demo-user'@'localhost'                                            |
+------------------------------------------------------------------------------------------------------------------+
2 rows in set (0.00 sec)</pre>

<p>Always flush privileges when you are finished making changes.</p>



<h3>Changing the Root User</h3>

<p>One additional step that you may want to take is to change the root login name.  If an attacker is trying to access the root MySQL login, they will need to perform the additional step of finding the username.</p>
	
<p>The root login can be changed with the following command:</p>
	
<pre>rename user 'root'@'localhost' to '<span class="highlight">newAdminUser</span>'@'localhost';</pre>

<p>We can see the change by using the same query we've been using for the User database:</p>
	
<pre>select user,host,password from mysql.user;</pre>

<p>Again, we must flush privileges for these changes to happen:</p>
	
<pre>FLUSH PRIVILEGES;</pre>

<p>Remember that you will have to log into MySQL as the newly created username from now on when you wish to perform administrative tasks:</p>
	
<pre>mysql -u <span class="highlight">newAdminUser</span> -p</pre>

<h2>Conclusion</h2>

<p>Although this is in no way an exhaustive list of MySQL and MariaDB security practices, it should give you a good introduction to the kinds of decisions you have to make when securing your databases.</p>
	
<p>More information about configuration and security can be found on the MySQL and MariaDB websites as well as in their respective man pages.  The applications you choose to use may also offer security advice.</p>

<div class="author">By Justin Ellingwood</div></div>
    