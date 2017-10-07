<link rel='stylesheet' href='digital_ocean.css'><br> <br> 
      <h3 id="introduction">Introduction</h3>

<p>As your websites grow and see an increase in traffic, one of the components that shows stress the fastest is the backend database.  If your database is not distributed and configured to handle high loads, it can easily be overwhelmed by a relatively modest increase in traffic.</p>

<p>One way of dealing with this is leveraging a memory object caching system, like <strong>memcached</strong>.  Memcached is a caching system that works by temporarily storing information in memory that would usually be retrieved from a database.  The next request for the in-memory information is then incredibly fast without putting stress on the backend database.</p>

<p>In this guide, we're going to discuss how to install and use memcached on an Ubuntu 14.04 server.</p>

<h2 id="prerequisites">Prerequisites</h2>

<p>Before we get started, you should have a regular, non-root user on your server who has access to <code>sudo</code> privileges.  If you have not already created such a user, you can do so by following steps 1-4 in our <a href="https://indiareads/community/articles/initial-server-setup-with-ubuntu-14-04">initial setup guide for Ubuntu 14.04</a>.</p>

<p>When you have your regular user configured, continue on with this guide.</p>

<h2 id="install-memcached-and-the-components">Install Memcached and the Components</h2>

<p>To get started, we should to get all of the components we need from Ubuntu's repositories.  Luckily, everything we need is available.</p>

<p>Since this is our first operation with <code>apt</code> in this session, we should update our local package index.  Then we can install our programs.</p>

<p>We're going to install memcached as well as a MySQL database backend and PHP to handle the interaction.  We also are installing the PHP extension that handles memcached interactions.  You can get everything you need by typing:</p>
<pre class="code-pre "><code langs="">sudo apt-get update
sudo apt-get install mysql-server php5-mysql php5 php5-memcached memcached
</code></pre>
<p>Note that there are *two" PHP memcache extensions available. One is called <code>php5-memcache</code> and the other is called <code>php5-memcached</code> (note the trailing "d" on the second example).  We are using the second of these because it is stable and implements a wider range of features.</p>

<p>If you don't already have MySQL installed, the installation will prompt you to select and confirm an administrator's password.</p>

<p>This should install and configure everything you need.</p>

<h2 id="check-the-installation">Check the Installation</h2>

<p>Believe it or not, memcached is already completely installed and ready to go.  We can test this a number of different ways.</p>

<p>The first way is rather simple.  We can just ask PHP if it knows about our memcached extension and whether it is enabled or not.  We can do this by creating the ubiquitous PHP info page.</p>

<p>This is easily accomplished by creating a file called <code>info.php</code> in our document root.  In Apache on Ubuntu 14.04, our default document root is <code>/var/www/html</code>.  Open the file here with root privileges:</p>
<pre class="code-pre "><code langs="">sudo nano /var/www/html/info.php
</code></pre>
<p>In this file, type this out.  This basically just calls a PHP function that collects and prints information about our server into a web-friendly layout.</p>
<pre class="code-pre "><code langs=""><?php
phpinfo();
?>
</code></pre>
<p>Now, you can visit your server's domain name or public IP address followed by <code>/info.php</code> and you should see an information page.</p>

<pre>
http://<span class="highlight">server_domain_name_or_IP</span>/info.php
</pre>

<p>If you scroll down or search for the "memcached" section header, you should find something that looks like this:</p>

<p><img src="https://assets.digitalocean.com/articles/memcache_1404/php_info.png" alt="Memcache PHP info section" /></p>

<p>This means that the memcached extension is enabled and being found by the web server.</p>

<p>We can also check whether the memcached service is running by typing:</p>
<pre class="code-pre "><code langs="">ps aux | grep memcached
</code></pre>
<hr />
<pre class="code-pre "><code langs="">memcache  6584  0.0  0.0 327448  3004 ?        Sl   14:07   0:00 /usr/bin/memcached -m 64 -p 11211 -u memcache -l 127.0.0.1
demouser  6636  0.0  0.0  11744   904 pts/0    S+   14:29   0:00 grep --color=auto memcached
</code></pre>
<p>You can query the service for stats by typing:</p>
<pre class="code-pre "><code langs="">echo "stats settings" | nc localhost 11211
</code></pre>
<p>If you ever need to stop, start, or restart the memcached service, this can be done by typing something like this:</p>

<pre>
sudo service memcached <span class="highlight">restart</span>
</pre>

<h2 id="test-whether-memcached-can-cache-data">Test Whether Memcached can Cache Data</h2>

<p>Now that we have verified that memcached is running and that our PHP extension to connect with it is enabled, we can try to get it to store data.</p>

<p>We're going to do this by creating another PHP script.  This time, it'll be more complex.</p>

<p>Open a file called <code>cache_test.php</code> in our document root:</p>
<pre class="code-pre "><code langs="">sudo nano /var/www/html/cache_test.php
</code></pre>
<p>Inside, begin by creating the PHP wrapper tags:</p>

<pre>
<?php
?>
</pre>

<p>Within these, we're going to create a new instance of the PHP Memcached object and store it in a variable.  We're going to define the location where this PHP object can connect to the actual memcached service running on our server.  Memcached runs on port <code>11211</code> by default:</p>

<pre>
<?php
<span class="highlight">$mem = new Memcached();</span>
<span class="highlight">$mem->addServer("127.0.0.1", 11211);</span>
?>
</pre>

<p>Next, we're going tell our Memcached instance to query for a key from our cache.  This key can be called anything, because we haven't created it yet.  We'll use "blah".  The result of this request will be stored into a <code>$result</code> variable:</p>

<pre>
<?php
$mem = new Memcached();
$mem->addServer("127.0.0.1", 11211);

<span class="highlight">$result = $mem->get("blah");</span>
?>
</pre>

<p>Next, we just need to test whether anything was returned.  If memcached found a key called "blah", we want it to print the value associated with that key.  If memcached was unable to find the matching key, we should print out a message saying so.</p>

<p>We then should set the key with a value so that the next time we ask for the value, memcached will find the value we give it:</p>

<pre>
<?php
$mem = new Memcached();
$mem->addServer("127.0.0.1", 11211);

$result = $mem->get("blah");

<span class="highlight">if ($result) {</span>
    <span class="highlight">echo $result;</span>
<span class="highlight">} else {</span>
    <span class="highlight">echo "No matching key found.  I'll add that now!";</span>
    <span class="highlight">$mem->set("blah", "I am data!  I am held in memcached!") or die("Couldn't save anything to memcached...");</span>
<span class="highlight">}</span>
?>
</pre>

<p>At this point, our script is done.  If we visit this page in our web browser, we can see how this works:</p>

<pre>
http://<span class="highlight">server_domain_name_or_IP</span>/cache_test.php
</pre>

<p>You should initially see a page that looks like this:</p>

<p><img src="https://assets.digitalocean.com/articles/memcache_1404/uncached_message.png" alt="Memcached uncached message" /></p>

<p>However, if we refresh the page, we should see a different message:</p>

<p><img src="https://assets.digitalocean.com/articles/memcache_1404/cached_message.png" alt="Memcached cached message" /></p>

<p>As you can see, our memcached service is now caching the data that our script set.</p>

<h2 id="test-temporarily-caching-database-values">Test Temporarily Caching Database Values</h2>

<p>Now that we have tested our ability to store data in memcached, we can demonstrate a more realistic scenario: temporarily caching results from a database query.</p>

<h3 id="create-sample-data-in-mysql">Create Sample Data in MySQL</h3>

<p>To do this, we first need to store some information in our database.</p>

<p>Connect to your MySQL instance as the administrative user by typing this.  You'll have to enter the MySQL root password that you set during installation:</p>
<pre class="code-pre "><code langs="">mysql -u root -p
</code></pre>
<p>Afterwards, you'll be given a MySQL prompt.</p>

<p>First, we want to create a database to test on.  We'll then select the database:</p>
<pre class="code-pre "><code langs="">CREATE DATABASE mem_test;
USE mem_test;
</code></pre>
<p>Let's create a user called <code>test</code> with a password <code>testing123</code> that has access to the database we created:</p>
<pre class="code-pre "><code langs="">GRANT ALL ON mem_test.* TO test@localhost IDENTIFIED BY 'testing123';
</code></pre>
<p>Now, we are going to create a really basic table and insert a record into it.  The table will be called <code>sample_data</code> and it will just have an index and a string field:</p>
<pre class="code-pre "><code langs="">CREATE TABLE sample_data (id int, name varchar(30));
INSERT INTO sample_data VALUES (1, "some_data");
</code></pre>
<p>Now, we have our structure created and the data inserted.  We can exit out of MySQL:</p>
<pre class="code-pre "><code langs="">exit
</code></pre>
<h3 id="create-the-php-script-to-cache-mysql-data">Create the PHP Script to Cache MySQL Data</h3>

<p>Now that we have our data in MySQL, we can create another PHP script that will operate in a similar way to a production PHP application.</p>

<p>It will look for the data in memcached and return it if it finds the data.  If it does not find the data, it will query from the database itself and then store the results in memcached for future queries.</p>

<p>To start, create another PHP script in our document root.  We will call this script <code>database_test.php</code>:</p>
<pre class="code-pre "><code langs="">sudo nano /var/www/html/database_test.php
</code></pre>
<p>Start off in a similar way to our last script.  We're going to create a PHP memcached instance and then tell it where the memcached service running on our server is located, just as we did last time:</p>

<pre>
<?php
$mem = new Memcached();
$mem->addServer("127.0.0.1", 11211);
?>
</pre>

<p>Next, in our first departure from our last script, we're going to have to define how PHP can connect to our MySQL database.  We need to specify the login credentials for the user we created and then we'll need to tell it which database to use:</p>

<pre>
<?php
$mem = new Memcached();
$mem->addServer("127.0.0.1", 11211);

<span class="highlight">mysql_connect("localhost", "test", "testing123") or die(mysql_error());</span>
<span class="highlight">mysql_select_db("mem_test") or die(mysql_error());</span>
?>
</pre>

<p>Next, we're going to have to design the query that we need to fetch the data we inserted into our table.  We'll store this into a <code>$query</code> variable.</p>

<p>We'll then create a <code>$querykey</code> variable to store the key that memcached will use to reference our information.</p>

<p>We create this key by using the string "KEY" and then appending the md5 (a hashing method) checksum of our query to the end.  This will ensure that each key is unique if we were to use this technique on a larger dataset.  It also ensures that a matching query will produce the same key for subsequent requests.</p>

<pre>
<?php
$mem = new Memcached();
$mem->addServer("127.0.0.1", 11211);

mysql_connect("localhost", "test", "testing123") or die(mysql_error());
mysql_select_db("mem_test") or die(mysql_error());

<span class="highlight">$query = "SELECT ID FROM sample_data WHERE name = 'some_data'";</span>
<span class="highlight">$querykey = "KEY" . md5($query);</span>
?>
</pre>

<p>Next, we'll create a <code>$result</code> variable, just like our last script.  This will hold the result from our memcached query, just as it did before.  We are asking memcached for the query key that we've generated to see if it has a record identified by that key in its system.</p>

<pre>
<?php
$mem = new Memcached();
$mem->addServer("127.0.0.1", 11211);

mysql_connect("localhost", "test", "testing123") or die(mysql_error());
mysql_select_db("mem_test") or die(mysql_error());

$query = "SELECT name FROM sample_data WHERE id = 1";
$querykey = "KEY" . md5($query);

<span class="highlight">$result = $mem->get($querykey);</span>
?>
</pre>

<p>We're now ready to do the actual testing logic that will determine what will happen when the result is found in memcached.  If the results are found, we want to print the data that we pulled out and tell the user that we were able to retrieve it from memcached directly:</p>

<pre>
<?php
$mem = new Memcached();
$mem->addServer("127.0.0.1", 11211);

mysql_connect("localhost", "test", "testing123") or die(mysql_error());
mysql_select_db("mem_test") or die(mysql_error());

$query = "SELECT name FROM sample_data WHERE id = 1";
$querykey = "KEY" . md5($query);

$result = $mem->get($querykey);

<span class="highlight">if ($result) {</span>
    <span class="highlight">print "<p>Data was: " . $result[0] . "</p>";</span>
    <span class="highlight">print "<p>Caching success!</p><p>Retrieved data from memcached!</p>";</span>
<span class="highlight">}</span>
?>
</pre>

<p>Now, let's add logic for the alternate scenario.  If the results are <em>not</em> found, we want to use the query that we crafted to ask MySQL for the data.  We will store this into the <code>$result</code> variable we made.  This will be in the form of an array.</p>

<p>After we have the result of the query, we need to add that result to memcached so that the data will be there the next time we do that.  We can do this by feeding memcached the key that we want to use to reference the data (we already created this with the <code>$querykey</code> variable), the data itself (stored in the <code>$result</code> variable from the MySQL query), and the time to cache the data in seconds.</p>

<p>We are going to cache our content for 10 seconds.  In the real world, it would most likely be beneficial to cache content for longer.  Perhaps something closer to 10 minutes (600 seconds) if your content doesn't change much.  For testing, a smaller value lets us see what's happening faster, without restarting our memcached service.</p>

<p>Afterwards, we'll print out a similar message with the query results and tell the user what happened.  We should add this whole block as an <code>else</code> for our previous <code>if</code>:</p>

<pre>
<?php
$mem = new Memcached();
$mem->addServer("127.0.0.1", 11211);

mysql_connect("localhost", "test", "testing123") or die(mysql_error());
mysql_select_db("mem_test") or die(mysql_error());

$query = "SELECT name FROM sample_data WHERE id = 1";
$querykey = "KEY" . md5($query);

$result = $mem->get($querykey);

if ($result) {
    print "<p>Data was: " . $result[0] . "</p>";
    print "<p>Caching success!</p><p>Retrieved data from memcached!</p>";
} <span class="highlight">else {</span>
    <span class="highlight">$result = mysql_fetch_array(mysql_query($query)) or die(mysql_error());</span>
    <span class="highlight">$mem->set($querykey, $result, 10);</span>
    <span class="highlight">print "<p>Data was: " . $result[0] . "</p>";</span>
    <span class="highlight">print "<p>Data not found in memcached.</p><p>Data retrieved from MySQL and stored in memcached for next time.</p>";</span>
<span class="highlight">}</span>
?>
</pre>

<p>This is our completed script.  It will attempt to get data from memcached and return it.  Failing that, it will query from MySQL directly and cache the results for 10 seconds.</p>

<h3 id="test-the-script">Test the Script</h3>

<p>Now that we have the script written, we can run it by going to our file location in our web browser:</p>

<pre>
http://<span class="highlight">server_domain_name_or_IP</span>/database_test.php
</pre>

<p>The first time we visit the page, we should see output that looks like this:</p>

<p><img src="https://assets.digitalocean.com/articles/memcache_1404/db_uncached.png" alt="Memcached uncached database query" /></p>

<p>If we refresh this (within 10 seconds of our last visit), the page should now show a different message:</p>

<p><img src="https://assets.digitalocean.com/articles/memcache_1404/db_cached.png" alt="Memcached cached database query" /></p>

<p>If we wait a bit again, the cached content will expire and be removed from memcached again.  We can refresh at this point to get the first message again, since the server must go back to the database to get the appropriate values.</p>

<h2 id="conclusion">Conclusion</h2>

<p>By now, you should have a decent understanding of how memcached works and how you can leverage it to keep your web server from hitting the database repeatedly for the same content.</p>

<p>Although the PHP scripts that we created in this guide were only examples, they should give you a good idea of how the system works.  It should also give you a good idea of how to structure your code so that you can check memcached and fall back on the database if necessary.</p>

<div class="author">By Justin Ellingwood</div>

    