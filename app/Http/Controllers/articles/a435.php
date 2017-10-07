<link rel='stylesheet' href='digital_ocean.css'><br><img src=https://community-cdn-digitalocean-com.global.ssl.fastly.net/assets/tutorials/images/large/mongo_DB_backups_tw.png?1461011416/> <br> 
      <p>MongoDB is one of the most popular NoSQL database engines. It is famous for being scalable, powerful, reliable and easy to use. In this article we'll show you how to back up, restore, and migrate your MongoDB databases.</p>

<p>Importing and exporting a database means dealing with data in a human-readable format, compatible with other software products. In contrast, the backup and restore operations create or use MongoDB-specific binary data, which preserves not only the consistency and integrity of your data but also its specific MongoDB attributes. Thus, for migration its usually preferable to use backup and restore as long as the source and target systems are compatible. </p>

<h2 id="prerequisites">Prerequisites</h2>

<p>Before following this tutorial, please make sure you complete the following prerequisites:</p>

<ul>
<li>Ubuntu 14.04 Droplet</li>
<li>Non-root sudo user. Check out <a href="https://indiareads/community/tutorials/initial-server-setup-with-ubuntu-14-04">Initial Server Setup with Ubuntu 14.04</a> for details.</li>
<li>MongoDB installed and configured using the article <a href="https://indiareads/community/tutorials/how-to-install-mongodb-on-ubuntu-14-04">How to Install MongoDB on Ubuntu 14.04</a>.</li>
<li>Example MongoDB database imported using the instructions in <a href="https://indiareads/community/tutorials/how-to-import-and-export-a-mongodb-database-on-ubuntu-14-04">How To Import and Export a MongoDB Database on Ubuntu 14.04</a></li>
</ul>

<p>Except otherwise noted, all of the commands that require root privileges in this tutorial should be run as a non-root user with sudo privileges.</p>

<h2 id="understanding-the-basics">Understanding the Basics</h2>

<p>Before continue further with this article some basic understanding on the matter is needed. If you have experience with popular relational database systems such as MySQL, you may find some similarities when working with MongoDB.</p>

<p>The first thing you should know is that MongoDB uses <a href="http://json.org/">json</a> and bson (binary json) formats for storing its information. Json is the human-readable format which is perfect for exporting and, eventually, importing your data. You can further manage your exported data with any tool which supports json, including a simple text editor. </p>

<p>An example json document looks like this:</p>
<div class="code-label " title="Example of json Format">Example of json Format</div><pre class="code-pre "><code langs="">{"address":[
    {"building":"1007", "street":"Park Ave"},
    {"building":"1008", "street":"New Ave"},
]}
</code></pre>
<p>Json is very convenient to work with, but it does not support all the data types available in bson. This means that there will be the so called 'loss of fidelity' of the information if you use json. For backing up and restoring, it's better to use the binary bson.</p>

<p>Second, you don't have to worry about explicitly creating a MongoDB database. If the database you specify for import doesn't already exist, it is automatically created. Even better is the case with the collections' (database tables) structure. In contrast to other database engines, in MongoDB the structure is again automatically created upon the first document (database row) insert.</p>

<p>Third, in MongoDB reading or inserting large amounts of data, such as for the tasks of this article, can be resource intensive and consume much of the CPU, memory, and disk space. This is something critical considering that MongoDB is frequently used for large databases and Big Data. The simplest solution to this problem is to run the exports and backups during the night or during non-peak hours.</p>

<p>Fourth, information consistency could be problematic if you have a busy MongoDB server where the information changes during the database export or backup process. There is no simple solution to this problem, but at the end of this article, you will see recommendations to further read about replication.</p>

<p>While you can use the <a href="https://indiareads/community/tutorials/how-to-import-and-export-a-mongodb-database-on-ubuntu-14-04">import and export functions</a> to backup and restore your data, there are better ways to ensure the full integrity of your MongoDB databases. To backup your data you should use the command <code>mongodump</code>. For restoring, use <code>mongorestore</code>. Let's see how they work.</p>

<h2 id="backing-up-a-mongodb-database">Backing Up a MongoDB Database</h2>

<p>Let's cover backing up your MongoDB database first.</p>

<p>An important argument to <code>mongodump</code> is <code>--db</code>, which specifies the name of the database which you want to back up. If you don't specify a database name, <code>mongodump</code> backups all of your databases. The second important argument is <code>--out</code> which specifies the directory in which the data will be dumped. Let's take an example with backing up the <code>newdb</code> database and storing it in the <code>/var/backups/mongobackups</code> directory. Ideally, we'll have each of our backups in a directory with the current date like <code>/var/backups/mongobackups/01-20-16</code> (20th January 2016). First, let's create that directory <code>/var/backups/mongobackups</code> with the command:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo mkdir /var/backups/mongobackups
</li></ul></code></pre>
<p>Then our backup command should look like this:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo mongodump --db newdb --out /var/backups/mongobackups/`date +"%m-%d-%y"`
</li></ul></code></pre>
<p>A successfully executed backup will have an output such as:</p>
<div class="code-label " title="Output of mongodump">Output of mongodump</div><pre class="code-pre "><code langs="">2016-01-20T10:11:57.685-0500    writing newdb.restaurants to /var/backups/mongobackups/01-20-16/newdb/restaurants.bson
2016-01-20T10:11:57.907-0500    writing newdb.restaurants metadata to /var/backups/mongobackups/01-20-16/newdb/restaurants.metadata.json
2016-01-20T10:11:57.911-0500    done dumping newdb.restaurants (25359 documents)
2016-01-20T10:11:57.911-0500    writing newdb.system.indexes to /var/backups/mongobackups/01-20-16/newdb/system.indexes.bson
</code></pre>
<p>Note that in the above directory path we have used <code>date +"%m-%d-%y"</code> which gets the current date automatically. This will allow us to have the backups inside the directory <code>/var/backups/<span class="highlight">01-20-16</span>/</code>. This is especially convenient when we automate the backups.</p>

<p>At this point you have a complete backup of the <code>newdb</code> database in the directory <code>/var/backups/mongobackups/<span class="highlight">01-20-16</span>/newdb/</code>. This backup has everything to restore the <code>newdb</code> properly and preserve its so called "fidelity".</p>

<p>As a general rule, you should make regular backups, such as on a daily basis, and preferably during a time when the server is least loaded. Thus, you can set the <code>mongodump</code> command as a cron job so that it's run regularly, e.g. every day at 03:03 AM. To accomplish this open crontab, cron's editor like this:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo crontab -e
</li></ul></code></pre>
<p>Note that when you run <code>sudo crontab</code> you will be editing the cron jobs for the root user. This is recommended because if you set the crons for your user, they might not be executed properly, especially if your sudo profile requires password verification. </p>

<p>Inside the crontab prompt insert the following <code>mongodump</code> command:</p>
<div class="code-label " title="Crontab window">Crontab window</div><pre class="code-pre "><code langs="">3 3 * * * mongodump --out /var/backups/mongobackups/`date +"%m-%d-%y"`
</code></pre>
<p>In the above command we are omitting the <code>--db</code> argument on purpose because typically you will want to have all of your databases backed up. </p>

<p>Depending on your MongoDB database sizes you may soon run out of disk space with too many backups. That's why it's also recommended to clean the old backups regularly or to compress them. For example, to delete all the backups older than 7 days you can use the following bash command:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">find /var/backups/mongobackups/ -mtime +7 -exec rm -rf {} \;
</li></ul></code></pre>
<p>Similarly to the previous <code>mongodump</code> command, this one can be also added as a cron job. It should run just before you start the next backup, e.g. at 03:01 AM. For this purpose open again crontab:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo crontab -e
</li></ul></code></pre>
<p>After that insert the following line:</p>
<div class="code-label " title="Crontab window">Crontab window</div><pre class="code-pre "><code langs="">3 1 * * * find /var/backups/mongobackups/ -mtime +7 -exec rm -rf {} \;
</code></pre>
<p>Completing all the tasks in this step will ensure a good backup solution for your MongoDB databases.</p>

<h2 id="restoring-and-migrating-a-mongodb-database">Restoring and Migrating a MongoDB Database</h2>

<p>By restoring your MongoDB database from a previous backup (such as one from the previous step) you will be able to have the exact copy of your MongoDB information taken at a certain time, including all the indexes and data types. This is especially useful when you want to migrate your MongoDB databases. For restoring MongoDB we'll be using the command <code>mongorestore</code> which works with the binary backup produced by <code>mongodump</code>.</p>

<p>Let's continue our examples with the <code>newdb</code> database and see how we can restore it from the previously taken backup. As arguments we'll specify first the name of the database with the <code>--db</code> argument. Then with  <code>--drop</code> we'll make sure that the target database is first dropped so that the backup is restored in a clean database. As a final argument we'll specify the directory of the last backup <code>/var/backups/mongobackups/<span class="highlight">01-20-16</span>/newdb/</code>. So the whole command will look like this (replace with the date of the backup you wish to restore):</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo mongorestore --db newdb --drop /var/backups/mongobackups/<span class="highlight">01-20-16</span>/newdb/
</li></ul></code></pre>
<p>A successful execution will show the following output:</p>
<div class="code-label " title="Output of mongorestore">Output of mongorestore</div><pre class="code-pre "><code langs="">2016-01-20T10:44:47.876-0500    building a list of collections to restore from /var/backups/mongobackups/01-20-16/newdb/ dir
2016-01-20T10:44:47.908-0500    reading metadata file from /var/backups/mongobackups/01-20-16/newdb/restaurants.metadata.json
2016-01-20T10:44:47.909-0500    restoring newdb.restaurants from file /var/backups/mongobackups/01-20-16/newdb/restaurants.bson
2016-01-20T10:44:48.591-0500    restoring indexes for collection newdb.restaurants from metadata
2016-01-20T10:44:48.592-0500    finished restoring newdb.restaurants (25359 documents)
2016-01-20T10:44:48.592-0500    done
</code></pre>
<p>In the above case we are restoring the data on the same server where the backup has been created. If you wish to migrate the data to another server and use the same technique, you should just copy the backup directory, which is <code>/var/backups/mongobackups/<span class="highlight">01-20-16</span>/newdb/</code> in our case, to the other server. </p>

<h2 id="conclusion">Conclusion</h2>

<p>This article has introduced you to the essentials of managing your MongoDB data in terms of backing up, restoring, and migrating databases. You can continue further reading on <a href="https://indiareads/community/tutorials/how-to-set-up-a-scalable-mongodb-database">How To Set Up a Scalable MongoDB Database</a> in which MongoDB replication is explained. </p>

<p>Replication is not only useful for scalability, but it's also important for the current topics. Replication allows you to continue running your MongoDB service uninterrupted from a slave MongoDB server while you are restoring the master one from a failure. Part of the replication is also the <a href="https://docs.mongodb.org/manual/core/replica-set-oplog/">operations log (oplog)</a>, which records all the operations that modify your data. You can use this log, just as you would use the binary log in MySQL, to restore your data after the last backup has taken place. Recall that backups usually take place during the night, and if you decide to restore a backup in the evening you will be missing all the updates since the last backup.</p>

    