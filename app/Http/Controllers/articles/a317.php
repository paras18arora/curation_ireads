<link rel='stylesheet' href='digital_ocean.css'><br><img src=https://community-cdn-digitalocean-com.global.ssl.fastly.net/assets/tutorials/images/large/mongo_DB_backups_tw.png?1460745017/> <br> 
      <h3 id="introduction">Introduction</h3>

<p>A lot of modern web application developers today choose to use a NoSQL database in their projects, and MongoDB is often their first choice. If you're using MongoDB in a production scenario, it is important that you regularly create backups in order to avoid data loss. Fortunately, MongoDB offers simple command line tools to create and use backups. This tutorial will explain how to use those tools.</p>

<p>To understand how backups work without tampering with your existing databases, this tutorial will start by walking you through creating a new database and adding a small amount of data to it. You are then going to create a backup of the database, then delete the database and restore it using the backup.</p>

<h2 id="prerequisites">Prerequisites</h2>

<p>To follow along, you will need:</p>

<ul>
<li><p>One 64-bit Ubuntu 14.04 Droplet with a <a href="https://indiareads/community/tutorials/initial-server-setup-with-ubuntu-14-04">sudo non-root user</a></p></li>
<li><p>MongoDB 3.0.7 installed on your server, which you can do by following <a href="https://indiareads/community/tutorials/how-to-install-mongodb-on-ubuntu-14-04">this MongoDB installation guide</a></p></li>
</ul>

<h2 id="step-1-—-creating-an-example-database">Step 1 — Creating an Example Database</h2>

<p>Creating a backup of an empty database isn't very useful, so in this step, we'll create an example database and add some data to it.</p>

<p>The easiest way to interact with a MongoDB instance is to use the <code>mongo</code> shell. Open it with the <code>mongo</code> command.</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">mongo
</li></ul></code></pre>
<p>Once you have the MongoDB prompt, create a new database called <strong>myDatabase</strong> using the <code>use</code> helper.</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">use myDatabase
</li></ul></code></pre><div class="code-label " title="Output">Output</div><pre class="code-pre "><code langs="">switched to db myDatabase
</code></pre>
<p>All data in a MongoDB database should belong to a <em>collection</em>. However, you don't have to create a collection explicitly. When you use the <code>insert</code> method to write to a non-existent collection, the collection is created automatically before the data is written.</p>

<p>You can use the following code to add three small documents to a collection called <strong>myCollection</strong> using the <code>insert</code> method:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">db.myCollection.insert([
</li><li class="line" prefix="$">    {'name': 'Alice', 'age': 30},
</li><li class="line" prefix="$">    {'name': 'Bill', 'age': 25},
</li><li class="line" prefix="$">    {'name': 'Bob', 'age': 35}
</li><li class="line" prefix="$">]);
</li></ul></code></pre>
<p>If the insertion is successful, you'll see a message which looks like this:</p>
<div class="code-label " title="Output of a successful insert() operation">Output of a successful insert() operation</div><pre class="code-pre "><code langs="">BulkWriteResult({
    "writeErrors" : [ ],
    "writeConcernErrors" : [ ],
    "nInserted" : 3,
    "nUpserted" : 0,
    "nMatched" : 0,
    "nModified" : 0,
    "nRemoved" : 0,
    "upserted" : [ ]
})
</code></pre>
<h2 id="step-2-—-checking-the-size-of-the-database">Step 2 — Checking the Size of the Database</h2>

<p>Now that you have a database containing data, you can create a backup for it. However, backups will be large if you have a large database, and in order to avoid the risk of running out of storage space, and consequently slowing down or crashing your server, you should check the size of your database before you create a backup.</p>

<p>You can use the <code>stats</code> method and inspect the value of the <code>dataSize</code> key to know the size of your database in bytes.</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">db.stats().dataSize;
</li></ul></code></pre>
<p>For the current database, the value of <code>dataSize</code> will be a small number:</p>
<div class="code-label " title="Output of db.stats().datasize">Output of db.stats().datasize</div><pre class="code-pre "><code langs="">592
</code></pre>
<p>Note that the value of <code>dataSize</code> is only a rough estimate of the size of the backup.</p>

<h2 id="step-3-—-creating-a-backup">Step 3 — Creating a Backup</h2>

<p>To create a backup, you can use a command-line utility called <code>mongodump</code>. By default, <code>mongodump</code> will create a backup of all the databases present in a MongoDB instance. To create a backup of a specific database, you must use the <code>-d</code> option and specify the name of the database. Additionally, to let <code>mongodump</code> know where to store the backup, you must use the <code>-o</code> option and specify a path.</p>

<p>If you are still inside the <code>mongo</code> shell, exit it by pressing <code>CTRL+D</code>.</p>

<p>Type in the following command to create a backup of <strong>myDatabase</strong> and store it in <code>~/backups/first_backup</code>:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">mongodump -d myDatabase -o ~/backups/first_backup
</li></ul></code></pre>
<p>If the backup creation is successful, you will see the following log messages:</p>
<div class="code-label " title="Successful backup creation logs">Successful backup creation logs</div><pre class="code-pre "><code langs="">2015-11-24T18:11:58.590-0500  writing myDatabase.myCollection to /home/me/backups/first_backup/myDatabase/myCollection.bson
2015-11-24T18:11:58.591-0500  writing myDatabase.myCollection metadata to /home/me/backups/first_backup/myDatabase/myCollection.metadata.json
2015-11-24T18:11:58.592-0500  done dumping myDatabase.myCollection (3 documents)
2015-11-24T18:11:58.592-0500  writing myDatabase.system.indexes to /home/me/backups/first_backup/myDatabase/system.indexes.bson
</code></pre>
<p>Note that the backup is not a single file; it's actually a directory which has the following structure:</p>
<div class="code-label " title="Directory structure of a MongoDB backup">Directory structure of a MongoDB backup</div><pre class="code-pre "><code langs="">first_backup
└── myDatabase
    ├── myCollection.bson
    ├── myCollection.metadata.json
    └── system.indexes.bson
</code></pre>
<h2 id="step-4-—-deleting-the-database">Step 4 — Deleting the Database</h2>

<p>To test the backup you created, you can either use a MongoDB instance running on a different server or delete the database on your current server. In this tutorial, we'll do the latter.</p>

<p>Open the <code>mongo</code> shell and connect to <strong>myDatabase</strong>.</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">mongo myDatabase
</li></ul></code></pre>
<p>Delete the database using the <code>dropDatabase</code> method.</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">db.dropDatabase();
</li></ul></code></pre>
<p>If the deletion is successful, you'll see the following message:</p>
<div class="code-label " title="Output of dropDatabase()">Output of dropDatabase()</div><pre class="code-pre "><code langs="">{ "dropped" : "myDatabase", "ok" : 1 }
</code></pre>
<p>You can now use the <code>find</code> method of your collection to see that all the data you inserted earlier is gone.</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">db.myCollection.find(); 
</li></ul></code></pre>
<p>There will be no output from this command because there's no data to display in the database.</p>

<h2 id="step-5-—-restoring-the-database">Step 5 — Restoring the Database</h2>

<p>To restore a database using a backup created using <code>mongodump</code>, you can use another command line utility called <code>mongorestore</code>. Before you use it, exit the <code>mongo</code> shell by pressing <code>CTRL+D</code>.</p>

<p>Using <code>mongorestore</code> is very simple. All it needs is the path of the directory containing the backup. Here's how you can restore your database using the backup stored in <code>~/backupts/first_backup</code>:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">mongorestore ~/backups/first_backup/
</li></ul></code></pre>
<p>You'll see the following log messages if the restore operation is successful:</p>
<div class="code-label " title="Successful restore logs">Successful restore logs</div><pre class="code-pre "><code langs="">2015-11-24T18:27:04.250-0500  building a list of dbs and collections to restore from /home/me/backups/first_backup/ dir
2015-11-24T18:27:04.251-0500  reading metadata file from /home/me/backups/first_backup/myDatabase/myCollection.metadata.json
2015-11-24T18:27:04.252-0500  restoring myDatabase.myCollection from file /home/me/backups/first_backup/myDatabase/myCollection.bson
2015-11-24T18:27:04.309-0500  restoring indexes for collection myDatabase.myCollection from metadata
2015-11-24T18:27:04.310-0500  finished restoring myDatabase.myCollection (3 documents)
2015-11-24T18:27:04.310-0500  done
</code></pre>
<p>To examine the restored data, first, open the <code>mongo</code> shell and connect to <code>myDatabase</code>.</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">mongo myDatabase
</li></ul></code></pre>
<p>Then, call the <code>find</code> method on your <code>collection</code>.</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">db.myCollection.find();
</li></ul></code></pre>
<p>If everything went well, you should now be able to see all the data you inserted earlier.</p>
<div class="code-label " title="Output of find()">Output of find()</div><pre class="code-pre "><code langs="">{ "_id" : ObjectId("5654e76f21299039c2ba8720"), "name" : "Alice", "age" : 30 }
{ "_id" : ObjectId("5654e76f21299039c2ba8721"), "name" : "Bill", "age" : 25 }
{ "_id" : ObjectId("5654e76f21299039c2ba8722"), "name" : "Bob", "age" : 35 }
</code></pre>
<h2 id="conclusion">Conclusion</h2>

<p>In this tutorial, you learned how to use <code>mongodump</code> and <code>mongorestore</code> to back up and restore a MongoDB database. Note that creating a backup is an expensive operation, and can reduce the performance of your MongoDB instance. Therefore, it is recommended that you create your backups only during off-peak hours.</p>

<p>To learn more about MongoDB backup strategies, you can refer to the <a href="https://docs.mongodb.org/manual/core/backups/">MongoDB 3.0 manual</a>.</p>

    