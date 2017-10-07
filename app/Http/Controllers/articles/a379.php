<link rel='stylesheet' href='digital_ocean.css'><br> <br> 
      <h3 id="introduction">Introduction</h3>

<p>The general idea of using memcached and its standalone server implementation with MySQL has been described in many fine articles such as the one <a href="https://indiareads/community/tutorials/how-to-install-and-use-memcache-on-ubuntu-14-04">How To Install and Use Memcache on Ubuntu 14.04</a>. However, memcached as a standalone server works as an intermediary in front of the MySQL client access layer and manages information only in the memory without an option to store it persistently. This makes it suitable for tasks such as caching the results of duplicate MySQL queries. This saves resources and optimizes the performance of busy sites.</p>

<p>However, in this article we'll be discussing something different. Memcached will be installed as a MySQL plugin and tightly integrated into MySQL. It will provide a NoSQL style access layer for managing information directly in regular MySQL InnoDB tables. This has numerous benefits as we'll see later in the article.</p>

<h2 id="basic-understanding">Basic Understanding</h2>

<p>To be able to follow this article you will need some basic understanding of what NoSQL and memcached are. Put simply, NoSQL works with information in the form of key-value(s) items. This obviously simpler approach than the standard SQL suggests better performance and scalability, which are especially sought after for working with large amounts of information (Big Data).</p>

<p>However, NoSQL's good performance is not enough to replace the usual SQL. The simplicity of NoSQL makes it unsuitable for structured data with complex relations in it. Thus, NoSQL is not a replacement of SQL but rather an important addition to it. </p>

<p>As to memcached, it can be regarded as a popular implementation of NoSQL. It's very fast and has excellent caching mechanisms as its name suggests. That's why it makes a great choice for bringing NoSQL style to the traditional MySQL.</p>

<p>Some understanding of the memcached protocol is also needed. Memcached works with items which have the following parts:</p>

<ul>
<li>A <span class="highlight">key</span> — Alphanumerical value which will be the key for accessing the <span class="highlight">value</span> of the item.</li>
<li>A <span class="highlight">value</span> — Arbitrary data where the essential payload is kept.</li>
<li>A <span class="highlight">flag</span> — Usually a value used for setting up additional parameters related to the main value. For example, it could be a flag whether or not to use compression. </li>
<li>An <span class="highlight">expiration time</span> — Expiration time in seconds. Recall that memcached was initially designed with caching in mind.</li>
<li>A <span class="highlight">CAS value</span>  — Unique identifier of each item.</li>
</ul>

<h2 id="prerequisites">Prerequisites</h2>

<p>This guide has been tested on Ubuntu 14.04. The described installation and configuration would be similar on other OS or OS versions, but the commands and location of configuration files may vary.</p>

<p>You will need the following:</p>

<ul>
<li>Ubuntu 14.04 fresh install</li>
<li>Non-root user with sudo privileges</li>
</ul>

<p>All the commands in this tutorial should be run as a non-root user. If root access is required for the command, it will be preceded by <code>sudo</code>. If you don't already have that set up, follow this tutorial: <a href="https://indiareads/community/tutorials/initial-server-setup-with-ubuntu-14-04">Initial Server Setup with Ubuntu 14.04</a>.</p>

<h2 id="step-1-— installing-mysql-5-6">Step 1 — Installing MySQL 5.6</h2>

<p>The memcached plugin in MySQL is available in versions of MySQL above 5.6.6. This means that you cannot use the MySQL package (version 5.5) from the standard Ubuntu 14.04 repository. Instead, you'll have to:</p>

<ol>
<li>Add the MySQL official repository</li>
<li>Install the MySQL server, client, and libraries from it</li>
</ol>

<p>First, go to the <a href="https://dev.mysql.com/downloads/repo/apt/">MySQL apt repository page</a> and download the package that will add the MySQL repository to your Ubuntu 14.04 system. You can download the package directly on your Droplet:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">wget https://dev.mysql.com/get/mysql-apt-config_0.3.5-1ubuntu14.04_all.deb
</li></ul></code></pre>
<p>Next, install it with <code>dpkg</code>:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo dpkg -i mysql-apt-config_0.3.5-1ubuntu14.04_all.deb
</li></ul></code></pre>
<p>When you run the above command, a text mode wizard appears with two questions in it:</p>

<ul>
<li>Which MySQL product do you wish to configure? Answer with <code>Server</code>.</li>
<li>Which server version do you wish to receive? Answer with <code>mysql-5.6</code>.</li>
</ul>

<p>Once you answer these two questions you'll return to the first question about which product you wish to install. Answer with <code>Apply</code>, the bottom choice, to confirm your choices and exit the wizard.</p>

<p>Now that you have the new MySQL repo, you'll have to update the apt cache, i.e. the information about the available packages for installation in Ubuntu. Thus, when you opt to install MySQL it will be retrieved from the new repository. To update the apt cache, run the command:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo apt-get update
</li></ul></code></pre>
<p>After that you are ready to install MySQL 5.6 on Ubuntu 14.04 with the command:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo apt-get install mysql-server
</li></ul></code></pre>
<p>Once you run the above command you'll be asked to pick a MySQL root (administrator) password. For convenience, you may choose not to set a password at this point and when prompted just press ENTER. However, once you decide to turn this server in production, it's recommended that you run the command <code>sudo mysql_secure_installation</code> to secure your MySQL installation and configure a root password. </p>

<p>When the installation process completes you will have MySQL server 5.6 installed along with its command line client and necessary libraries. You can verify it by starting the client with the command:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">mysql -u root
</li></ul></code></pre>
<p>If you set a password, you will need to use the following command and enter your MySQL root password when prompted:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">mysql -u root -p
</li></ul></code></pre>
<p>You should see:</p>
<pre class="code-pre "><code langs="">Welcome to the MySQL monitor.  Commands end with ; or \g.
Your MySQL connection id is 2
Server version: 5.6.25 MySQL Community Server (GPL)
...
</code></pre>
<p>While still in the MySQL monitor (client terminal), create a new database called <code>test</code>:</p>
<pre class="code-pre custom_prefix"><code langs=""><ul class="prefixed"><li class="line" prefix="mysql>">CREATE DATABASE test;
</li></ul></code></pre>
<p>We'll need this database later for our testing. </p>

<p>To exit the MySQL client type:</p>
<pre class="code-pre custom_prefix"><code langs=""><ul class="prefixed"><li class="line" prefix="mysql>">quit
</li></ul></code></pre>
<p>Finally, as a dependency for the memcached plugin, you will also need to install the development package for the asynchronous event notification library — <code>libevent-dev</code>. To make this happen run the command:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo apt-get install libevent-dev
</li></ul></code></pre>
<h2 id="step-2-—-installing-the-memcached-plugin-in-mysql">Step 2 — Installing the memcached Plugin in MySQL</h2>

<p>To prepare for the memcached plugin installation you first have to execute the queries found in the file  <code>/usr/share/mysql/innodb_memcached_config.sql</code>. Start the MySQL client:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">mysql -u root
</li></ul></code></pre>
<p>or, if you set a password:</p>
<pre class="code-pre comand"><code langs="">mysql -u root -p
</code></pre>
<p>and execute:</p>
<pre class="code-pre custom_prefix"><code langs=""><ul class="prefixed"><li class="line" prefix="mysql>">source /usr/share/mysql/innodb_memcached_config.sql;
</li></ul></code></pre>
<p>This will create all the necessary settings for the plugin in the database <code>innodb_memcache</code> and also insert some example data in our newly created database <code>test</code>.</p>

<p>After that you can perform the installation of the memcached plugin from the MySQL terminal with the following command:</p>
<pre class="code-pre custom_prefix"><code langs=""><ul class="prefixed"><li class="line" prefix="mysql>">install plugin daemon_memcached soname "libmemcached.so";
</li></ul></code></pre>
<p>Exit the MySQL session:</p>
<pre class="code-pre custom_prefix"><code langs=""><ul class="prefixed"><li class="line" prefix="mysql>">quit
</li></ul></code></pre>
<p>This installs the memcached plugin which is found in the directory <code>/usr/lib/mysql/plugin/</code> in Ubuntu 14.04. This file is available only in MySQL version 5.6 and up.</p>

<p>Once the installation is complete, you have to configure the memcached plugin listener. You will need it to connect to the memcached plugin. For this purpose, open the file <code>/etc/mysql/my.cnf</code> with your favorite editor like this:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo vim /etc/mysql/my.cnf
</li></ul></code></pre>
<p>Somewhere after the <code>[mysqld]</code> line add a new line containing:</p>
<div class="code-label " title="/etc/mysql/my.cnf">/etc/mysql/my.cnf</div><pre class="code-pre "><code langs="">daemon_memcached_option="-p11222 -l 127.0.0.1"
</code></pre>
<p>The above configures the memcached plugin listener on port 11222 enabled only for the loopback IP 127.0.0.1. This means that only clients from the Droplet will be able to connect. If you omit the part about the IP (<code>-l 127.0.0.1</code>), the new listener will be freely accessible from everywhere, which is a serious security risk. If you are further concerned about the security of the memcached plugin please check <a href="http://dev.mysql.com/doc/refman/5.6/en/innodb-memcached-security.html">its security documentation</a>.</p>

<p>To start the new listener process for the memcached plugin, restart the MySQL server with the command:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo service mysql restart
</li></ul></code></pre>
<h2 id="step-3-—-testing-the-memcached-plugin">Step 3 — Testing the memcached Plugin</h2>

<p>To verify the installation is successful run the following MySQL command from the MySQL client (start the client with <code>mysql -u root</code> or <code>mysql -u root -p</code>):</p>
<pre class="code-pre custom_prefix"><code langs=""><ul class="prefixed"><li class="line" prefix="mysql>">show plugins;
</li></ul></code></pre>
<p>If everything is fine, you should see in the output:</p>
<pre class="code-pre "><code langs="">| daemon_memcached           | <span class="highlight">ACTIVE</span>  | DAEMON             | libmemcached.so | GPL     |
</code></pre>
<p>If you don't see this, make sure that you are using MySQL version 5.6 or up and that you have followed the installation instructions precisely.</p>

<p>You can also try to connect to the new memcached plugin interface with Telnet from your Droplet like this:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">telnet localhost 11222
</li></ul></code></pre>
<p>Upon success you should see output such as:</p>
<pre class="code-pre "><code langs="">Connected to localhost.
Escape character is '^]'.
</code></pre>
<p>Now you can run a generic command such as <code>stats</code>, for statistics, to see how this connection works. To exit the prompt press simultaneously the combination of CTRL and ] on your keyboard. After that type <code>quit</code> to exit the Telnet client itself.</p>

<p>Telnet gives you simplest way to connect to the memcached plugin and to the MySQL data itself. It is good for testing, but when you decide to use it professionally you should use the readily available libraries for popular programming languages like PHP and Python.</p>

<h2 id="step-4 —-running-nosql-queries-in-mysql-via-memcached-plugin">Step 4 — Running NoSQL Queries in MySQL via memcached Plugin</h2>

<p>If you go back to the installation part of the memcached plugin in this article, you will see that we executed the statements from the file <code>/usr/share/mysql/innodb_memcached_config.sql</code>. These statements created a new table <code>demo_test</code> in the <code>test</code> database. The <code>demo_test</code> table has the following columns in compliance with the memcached protocol:</p>

<ul>
<li><code>c1</code> implements the <span class="highlight">key</span> field.</li>
<li><code>c2</code> implements the <span class="highlight">value</span> field. </li>
<li><code>c3</code> implements the <span class="highlight">flag</span> field.</li>
<li><code>c4</code> implements the <span class="highlight">CAS</span> field.</li>
<li><code>c5</code> implements the <span class="highlight">expiration</span> field.</li>
</ul>

<p>The table <code>demo_test</code> will be the one we'll be testing with. First, let's open the database/table with the MySQL client with the following command:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">mysql -u root <span class="highlight">test</span>
</li></ul></code></pre>
<p>Or, if you have a MySQL password set:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">mysql -u root <span class="highlight">test</span> -p
</li></ul></code></pre>
<p>There should be already one row in the <code>demo_test</code> table:</p>
<pre class="code-pre custom_prefix"><code langs=""><ul class="prefixed"><li class="line" prefix="mysql>">SELECT * FROM demo_test;
</li></ul></code></pre>
<p>The results should look like:</p>
<pre class="code-pre "><code langs="">+-------------+--------------+------+------+------+
| c1          | c2           | c3   | c4   | c5   |
+-------------+--------------+------+------+------+
| AA          | HELLO, HELLO |    8 |    0 |    0 |
+-------------+--------------+------+------+------+
1 rows in set (0.00 sec)

</code></pre>
<p>Exit the MySQL session:</p>
<pre class="code-pre custom_prefix"><code langs=""><ul class="prefixed"><li class="line" prefix="mysql>">quit
</li></ul></code></pre>
<p>Now, let's create a second record using the memcached NoSQL interface and telnet. Connect again to localhost on TCP port 11222:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">telnet localhost 11222
</li></ul></code></pre>
<p>Then use the following syntax:</p>
<pre class="code-pre "><code langs="">set [<span class="highlight">key</span>] [<span class="highlight">flag</span>] [<span class="highlight">expiration</span>] [<span class="highlight">length in bytes</span>]
[<span class="highlight">value</span>]
</code></pre>
<p>Note that the <span class="highlight">value</span> has to be on a new row. Also, for each record you have to specify the length in bytes for the value when working in the above manner. </p>

<p>As an example, let's create a new item (database row) with key <code>newkey</code>, value <code>0</code> for flag, and value <code>0</code> for expiration (never to expire). The value will be 12 bytes in length.</p>
<pre class="code-pre "><code langs="">set newkey 0 0 12
NewTestValue
</code></pre>
<p>Of course, you can also retrieve values via this NoSQL interface. This is done with the <code>get</code> command which is followed by the name of the key you want to retrieve. While still in the Telnet session, type:</p>
<pre class="code-pre "><code langs="">get newkey
</code></pre>
<p>The result should be:</p>
<pre class="code-pre "><code langs="">VALUE newkey 0 12
NewTestValue
</code></pre>
<p>The above <code>set</code> and <code>get</code> commands are valid for every memcached server. These were just a few simple examples how to insert and retrieve records in a NoSQL style.</p>

<p>Now let's connect again to the MySQL client with the command <code>mysql -u root test</code> or<code>mysql -u root test -p</code> and see the content of the <code>demo_test</code> table again with run the qyery:</p>
<pre class="code-pre custom_prefix"><code langs=""><ul class="prefixed"><li class="line" prefix="mysql>">SELECT * FROM demo_test WHERE c1="newkey";
</li></ul></code></pre>
<p>There you should see the newly created row like this:</p>
<pre class="code-pre "><code langs="">+--------+--------------+------+------+------+
| c1     | c2           | c3   | c4   | c5   |
+--------+--------------+------+------+------+
| newkey | NewTestValue |    0 |    1 |    0 |
+--------+--------------+------+------+------+
</code></pre>
<p>By now you may wonder how the memcached plugin knows which database and table to connect to and how to map information to the table columns. The answer is in the database <code>innodb_memcache</code> and its table <code>containers</code>. </p>

<p>Execute this select statement:</p>
<pre class="code-pre custom_prefix"><code langs=""><ul class="prefixed"><li class="line" prefix="mysql>">select * from containers \G
</li></ul></code></pre>
<p>You will see the following:</p>
<pre class="code-pre "><code langs="">*************************** 1. row ***************************
                  name: aaa
             db_schema: test
              db_table: demo_test
           key_columns: c1
         value_columns: c2
                 flags: c3
            cas_column: c4
    expire_time_column: c5
unique_idx_name_on_key: PRIMARY
1 row in set (0.00 sec)
</code></pre>
<p>To learn more on how to create different mappings and find out advanced features of the memcached plugin please check out <a href="https://dev.mysql.com/doc/refman/5.6/en/innodb-memcached-internals.html">the memcached plugin internals page</a>.</p>

<h2 id="benefits-of-integrating-mysql-with-the-memcached-plugin">Benefits of Integrating MySQL with the memcached Plugin</h2>

<p>The above information and examples outline a few important benefits of integrating MySQL with NoSQL through the memcached plugin:</p>

<ul>
<li>All your data (MySQL and NoSQL) can be kept in one place. You don't have to install and maintain additional software for NoSQL data.</li>
<li>Data persistence, recovery, and replication for NoSQL data is possible thanks to the powerful InnoDB storage engine. </li>
<li>The incredibly fast memcached data access layer can be still used so that you can work with higher volumes of information compared to when working with the slower MySQL client. </li>
<li>NoSQL data can be managed with MySQL interface and syntax. Thus you can include NoSQL data in more complex SQL queries such as left joins.</li>
</ul>

<h2 id="conclusion">Conclusion</h2>

<p>By the end of this article you should be acquainted with the new possibilities for working with NoSQL data provided by MySQL. This may not be an universal solution to replace dedicated NoSQL servers such as MongoDB, but it certainly has its merits.</p>

    