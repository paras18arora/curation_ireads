<link rel='stylesheet' href='digital_ocean.css'><br> <br> 
      <h3 id="written-in-collaboration-with-memsql">Written in collaboration with <a href="http://www.memsql.com/">MemSQL</a></h3>

<h3 id="introduction">Introduction</h3>

<p>MemSQL is a type of in-memory database that can serve faster reads and writes than a traditional database. Even though it is a new technology, it speaks the MySQL protocol, so it feels very familiar to work with.</p>

<p>MemSQL has embraced the newest capabilities of MySQL with modern features, like JSON support and the ability to upsert data. One of the greatest advantages of MemSQL over MySQL is its ability to split a single query across multiple nodes, known as <em>massively parallel processing</em>, which results in much faster read queries.</p>

<p>In this tutorial, we will install MemSQL on a single Ubuntu 14.04 server, run performance benchmarks, and play with inserting JSON data through a command line MySQL client.</p>

<h2 id="prerequisites">Prerequisites</h2>

<p>To follow this tutorial, you will need:</p>

<ul>
<li><p>One Ubuntu 14.04 x64 Droplet with at least 8 GB RAM</p></li>
<li><p>A non-root user with sudo privileges, which you can set up by following the <a href="https://indiareads/community/tutorials/initial-server-setup-with-ubuntu-14-04">Initial Server Setup with Ubuntu 14.04</a> tutorial</p></li>
</ul>

<h2 id="step-1-—-installing-memsql">Step 1 — Installing MemSQL</h2>

<p>In this section, we will prepare our working environment for the MemSQL installation.</p>

<p>The latest version of MemSQL is listed on <a href="http://www.memsql.com/download/">their download page</a>. We'll be downloading and installing MemSQL Ops, which is a program that manages downloading and preparing your server for correctly running MemSQL. The most recent version of MemSQL Ops at the time of writing is 4.0.35.</p>

<p>First, download MemSQL's installation package file from their website.</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">wget http://download.memsql.com/memsql-ops-4.0.35/memsql-ops-4.0.35.tar.gz
</li></ul></code></pre>
<p>Next, extract the package.</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">tar -xzf memsql-ops-4.0.35.tar.gz
</li></ul></code></pre>
<p>Extracting the package has created a folder called <code>memsql-ops-4.0.35</code>. Note that the folder name has the version number, so if you download a newer version than what this tutorial specifies, you will have a folder with the version you downloaded.</p>

<p>Change directories into this folder.</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">cd memsql-ops-4.0.35
</li></ul></code></pre>
<p>Then, run the installation script, which is part of the installation package we just extracted.</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo ./install.sh
</li></ul></code></pre>
<p>You will see some output from the script. After a moment, it will ask you if you'd like to install MemSQL on this host only. We'll look at installing MemSQL across multiple machines in a future tutorial. So, for the purposes of this tutorial, let's say yes by entering <strong>y</strong>.</p>
<div class="code-label " title="Installation script prompt and output">Installation script prompt and output</div><pre class="code-pre "><code langs="">. . .
Do you want to install MemSQL on this host only? [y/N] <span class="highlight">y</span>

2015-09-04 14:30:38: Jd0af3b [INFO] Deploying MemSQL to 45.55.146.81:3306
2015-09-04 14:30:38: J4e047f [INFO] Deploying MemSQL to 45.55.146.81:3307
2015-09-04 14:30:48: J4e047f [INFO] Downloading MemSQL: 100.00%
2015-09-04 14:30:48: J4e047f [INFO] Installing MemSQL
2015-09-04 14:30:49: Jd0af3b [INFO] Downloading MemSQL: 100.00%
2015-09-04 14:30:49: Jd0af3b [INFO] Installing MemSQL
2015-09-04 14:31:01: J4e047f [INFO] Finishing MemSQL Install
2015-09-04 14:31:03: Jd0af3b [INFO] Finishing MemSQL Install
Waiting for MemSQL to start...
</code></pre>
<p>Now you have a MemSQL cluster deployed to your Ubuntu server! However, from the logs above, you'll notice that MemSQL was installed twice. </p>

<p>MemSQL can run as two different roles: an aggregator node and a leaf node. The reason why MemSQL was installed twice is because it needs at least one aggregator node and at least one leaf node for a cluster to operate.</p>

<p>The <em>aggregator node</em> is your interface to MemSQL. To the outside world, it looks a lot like MySQL: it listens on the same port, and you can connect tools that expect to talk to MySQL and standard MySQL libraries to it. The aggregator's job is to know about all of the MemSQL leaf nodes, handle MySQL clients, and translate their queries to MemSQL.</p>

<p>A <em>leaf node</em> actually stores data. When the leaf node receives a request from the aggregator node to read or write data, it executes that query and returns the results to the aggregator node. MemSQL allows you to share your data across multiple hosts, and each leaf node has a portion of that data. (Even with a single leaf node, your data is split within that leaf node.)</p>

<p>When you have multiple leaf nodes, the aggregator is responsible for translating MySQL queries to all the leaf nodes that should be involved in that query. It then takes the responses from all the leaf nodes and aggregates the result into one query that returns to your MySQL client. This is how parallel queries are managed.</p>

<p>Our single-host setup has the aggregator and leaf node running on the same machine, but you can add many more leaf nodes across many other machines.</p>

<h2 id="step-2-—-running-a-benchmark">Step 2 — Running a Benchmark</h2>

<p>Let's see how quickly MemSQL can operate by using the MemSQL Ops tool, which was installed as part of MemSQL's installation script.</p>

<p>In your web browser, go to <code>http://<span class="highlight">your_server_ip</span>:9000</code></p>

<p><img src="https://assets.digitalocean.com/articles/memsql/img1.png" alt="img" /></p>

<p>The MemSQL Ops tool gives you an overview of your cluster. We have 2 MemSQL nodes: the master aggregator and the leaf node.</p>

<p>Let's take the speed test on our single-machine MemSQL node. Click <strong>Speed Test</strong> from the menu on the left, then click <strong>START TEST</strong>. Here's an example of the results you might see:</p>

<p><img src="https://assets.digitalocean.com/articles/memsql/img2.png" alt="Speed test example results" /></p>

<p>We won't cover how to install MemSQL across multiple servers in this tutorial, but for comparison, here's a benchmark from a MemSQL cluster with three 8GB Ubuntu 14.04 nodes (one aggregator node and two leaf nodes):</p>

<p><img src="https://assets.digitalocean.com/articles/memsql/img3.png" alt="img" /></p>

<p>By doubling the number of leaf nodes, we were able to almost double our insert rate. By looking at the <strong>Rows Read</strong> sections, we can see that our three-node cluster was able to simultaneously read 12M more rows than the single-node cluster in the same amount of time.</p>

<h2 id="step-3-—-interacting-with-memsql-via-mysql-client">Step 3 — Interacting with MemSQL via mysql-client</h2>

<p>To clients, MemSQL looks like MySQL; they both speak the same protocol. To start talking to our MemSQL cluster, let's install a mysql-client.</p>

<p>First, update apt so that we install the latest client in the next step.</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo apt-get update
</li></ul></code></pre>
<p>Now, install a MySQL client. This will give us a <code>mysql</code> command to execute.</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo apt-get install mysql-client-core-5.6
</li></ul></code></pre>
<p>We're now ready to connect to MemSQL using a MySQL client. We're going to connect as the <strong>root</strong> user to the host <code>127.0.0.1</code> (which is our localhost IP address) on port 3306. We'll also customize the prompt message to <code>memsql></code>.</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">mysql -u root -h 127.0.0.1 -P 3306 --prompt="memsql> "
</li></ul></code></pre>
<p>You'll see a few lines of output followed by the <code>memsql></code> prompt.</p>

<p>Let's list the databases.</p>
<pre class="code-pre custom_prefix"><code langs=""><ul class="prefixed"><li class="line" prefix="memsql>">show databases;
</li></ul></code></pre>
<p>You'll see this output.</p>
<div class="code-label " title="Database output">Database output</div><pre class="code-pre "><code langs="">+--------------------+
| Database           |
+--------------------+
| information_schema |
| memsql             |
| sharding           |
+--------------------+
3 rows in set (0.01 sec)
</code></pre>
<p>Create a new database called <strong>tutorial</strong>.</p>
<pre class="code-pre custom_prefix"><code langs=""><ul class="prefixed"><li class="line" prefix="memsql>">create database tutorial;
</li></ul></code></pre>
<p>Then switch to using the new database with the <code>use</code> command.</p>
<pre class="code-pre custom_prefix"><code langs=""><ul class="prefixed"><li class="line" prefix="memsql>">use tutorial;
</li></ul></code></pre>
<p>Next, we'll create a <code>users</code> table which will have the <code>id</code> an an <code>email</code> fields. We have to specify a type for these two fields. Let's make id a bigint and email a varchar with a length of 255. We'll also tell the database that the <code>id</code> field is a primary key and the <code>email</code> field can't be null.</p>
<pre class="code-pre custom_prefix"><code langs=""><ul class="prefixed"><li class="line" prefix="memsql>">create table users (id bigint auto_increment primary key, email varchar(255) not null);
</li></ul></code></pre>
<p>You may notice poor execution time on that last command (15 - 20 seconds). There is one main reason why MemSQL is slow to create this new table: code generation.</p>

<p>Under the hood, MemSQL uses <em>code generation</em> to execute queries. This means that any time a new type of query is encountered, MemSQL needs to generate and compile code that represents the query. This code is then shipped to the cluster for execution. This speeds up processing the actual data, but there is a cost to preparation. MemSQL does what it can to re-use pre-generated queries, but new queries whose structure has never yet been seen will have a slowdown.</p>

<p>Back to our users table, take a look at the table definition.</p>
<pre class="code-pre custom_prefix"><code langs=""><ul class="prefixed"><li class="line" prefix="memsql>">describe users;
</li></ul></code></pre><div class="code-label " title="Table definition output">Table definition output</div><pre class="code-pre "><code langs="">+-------+--------------+------+------+---------+----------------+
| Field | Type         | Null | Key  | Default | Extra          |
+-------+--------------+------+------+---------+----------------+
| id    | bigint(20)   | NO   | PRI  | NULL    | auto_increment |
| email | varchar(255) | NO   |      | NULL    |                |
+-------+--------------+------+------+---------+----------------+
2 rows in set (0.00 sec)
</code></pre>
<p>Now, let's insert some example emails into the users table. This syntax is the same that we might use for a MySQL database.</p>
<pre class="code-pre custom_prefix"><code langs=""><ul class="prefixed"><li class="line" prefix="memsql>">insert into users (email) values ('one@example.com'), ('two@example.com'), ('three@example.com');
</li></ul></code></pre><div class="code-label " title="Inserting emails output">Inserting emails output</div><pre class="code-pre "><code langs="">Query OK, 3 rows affected (1.57 sec)
Records: 3  Duplicates: 0  Warnings: 0
</code></pre>
<p>Now query the users table.</p>
<pre class="code-pre custom_prefix"><code langs=""><ul class="prefixed"><li class="line" prefix="memsql>">select * from users;
</li></ul></code></pre>
<p>You can see the data we just entered:</p>
<div class="code-label " title="Users table output">Users table output</div><pre class="code-pre "><code langs="">+----+-------------------+
| id | email             |
+----+-------------------+
|  2 | two@example.com   |
|  1 | one@example.com   |
|  3 | three@example.com |
+----+-------------------+
3 rows in set (0.07 sec)
</code></pre>
<h2 id="step-4-—-inserting-and-querying-json">Step 4 — Inserting and Querying JSON</h2>

<p>MemSQL provides a JSON type, so in this step, we'll create an events table to make use of incoming events. This table will have an <code>id</code> field (like we did for users) and an <code>event</code> field, which will be a JSON type.</p>
<pre class="code-pre custom_prefix"><code langs=""><ul class="prefixed"><li class="line" prefix="memsql>">create table events (id bigint auto_increment primary key, event json not null);
</li></ul></code></pre>
<p>Let's insert a couple of events. Within the JSON, we'll reference an <code>email</code> field that, in turn, references back to the IDs of the users we inserted in step 3.</p>
<pre class="code-pre custom_prefix"><code langs=""><ul class="prefixed"><li class="line" prefix="memsql>">insert into events (event) values ('{"name": "sent email", "email": "one@example.com"}'), ('{"name": "received email", "email": "two@example.com"}');
</li></ul></code></pre>
<p>Now we can take a look at the events we just inserted.</p>
<pre class="code-pre custom_prefix"><code langs=""><ul class="prefixed"><li class="line" prefix="memsql>">select * from events;
</li></ul></code></pre><div class="code-label " title="Event table output">Event table output</div><pre class="code-pre "><code langs="">+----+-----------------------------------------------------+
| id | event                                               |
+----+-----------------------------------------------------+
|  2 | {"email":"two@example.com","name":"received email"} |
|  1 | {"email":"one@example.com","name":"sent email"}     |
+----+-----------------------------------------------------+
2 rows in set (3.46 sec)
</code></pre>
<p>Next, we can query all the events whose JSON <code>name</code> property is the text "received email".</p>
<pre class="code-pre custom_prefix"><code langs=""><ul class="prefixed"><li class="line" prefix="memsql>">select * from events where event::$name = 'received email';
</li></ul></code></pre><div class="code-label " title=""received email" query output">"received email" query output</div><pre class="code-pre "><code langs="">+----+-----------------------------------------------------+
| id | event                                               |
+----+-----------------------------------------------------+
|  2 | {"email":"two@example.com","name":"received email"} |
+----+-----------------------------------------------------+
1 row in set (5.84 sec)
</code></pre>
<p>Try changing that query to finding those whose <code>name</code> property is the text "sent email".</p>
<pre class="code-pre custom_prefix"><code langs=""><ul class="prefixed"><li class="line" prefix="memsql>">select * from events where event::$name = 'sent email';
</li></ul></code></pre><div class="code-label " title=""sent email" query output">"sent email" query output</div><pre class="code-pre "><code langs="">+----+-------------------------------------------------+
| id | event                                           |
+----+-------------------------------------------------+
|  1 | {"email":"one@example.com","name":"sent email"} |
+----+-------------------------------------------------+
1 row in set (0.00 sec)
</code></pre>
<p>This latest query ran much much faster than the previous one. This is because we only changed a parameter in the query, so MemSQL was able to skip the code generation.</p>

<p>Let's do something advanced for a distributed SQL database: let's join two tables on non-primary keys where one value of the join is nested within a JSON value but filters on a different JSON value.</p>

<p>First, we'll ask for all fields of the user table with the events table joined on by matching email where the event name is "received email".</p>
<pre class="code-pre custom_prefix"><code langs=""><ul class="prefixed"><li class="line" prefix="memsql>">select * from users left join events on users.email = events.event::$email where events.event::$name = 'received email';
</li></ul></code></pre><div class="code-label " title=""received email" fields output">"received email" fields output</div><pre class="code-pre "><code langs="">+----+-----------------+------+-----------------------------------------------------+
| id | email           | id   | event                                               |
+----+-----------------+------+-----------------------------------------------------+
|  2 | two@example.com |    2 | {"email":"two@example.com","name":"received email"} |
+----+-----------------+------+-----------------------------------------------------+
1 row in set (14.19 sec)
</code></pre>
<p>Next, try that same query, but filter to only "sent email" events.</p>
<pre class="code-pre custom_prefix"><code langs=""><ul class="prefixed"><li class="line" prefix="memsql>">select * from users left join events on users.email = events.event::$email where events.event::$name = 'sent email';
</li></ul></code></pre><div class="code-label " title=""sent email" fields output">"sent email" fields output</div><pre class="code-pre "><code langs="">+----+-----------------+------+-------------------------------------------------+
| id | email           | id   | event                                           |
+----+-----------------+------+-------------------------------------------------+
|  1 | one@example.com |    1 | {"email":"one@example.com","name":"sent email"} |
+----+-----------------+------+-------------------------------------------------+
1 row in set (0.01 sec)
</code></pre>
<p>Like before, the second query was much faster than the first. The benefits of code generation pay off when executing over millions of rows, as we saw in the benchmark. The flexibility to use a scale-out SQL database that understands JSON and how to arbitrarily join across tables is a powerful user feature.</p>

<h2 id="conclusion">Conclusion</h2>

<p>You've installed MemSQL, run a benchmark of your node's performance, interacted with your node via a standard MySQL client, and played with some advanced features not found in MySQL. This should be a good taste of what an in-memory SQL database can do for you.</p>

<p>There's still plenty left to learn about how MemSQL actually distributes your data, how to structure tables for maximum performance, how to expand MemSQL across multiple nodes, how to replicate your data for high availability, and how to secure MemSQL.</p>

    