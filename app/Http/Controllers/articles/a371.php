<link rel='stylesheet' href='digital_ocean.css'><br> <br> 
      <h3 id="introduction">Introduction</h3>

<p>OrientDB is a multi-model, NoSQL database, with support for graph and document databases. It is a Java application and can run on any operating system. It's also fully ACID-complaint with support for multi-master replication. It is developed by a company of the same name, with an Enterprise and a Community edition. </p>

<p>In this article, we'll be using the <strong>GratefulDeadConcerts</strong> database to demonstrate how to export and import an OrientDB database. That database comes with every installation of OrientDB, so you don't have to create a new one.</p>

<h2 id="prerequisites">Prerequisites</h2>

<p>To complete the tutorial, you'll need the following:</p>

<ul>
<li><p>Ubuntu 14.04 Droplet (see the <a href="https://indiareads/community/tutorials/initial-server-setup-with-ubuntu-14-04">initial setup guide</a>)</p></li>
<li><p>Latest edition of OrientDB installed using <a href="https://indiareads/community/tutorials/how-to-install-and-configure-orientdb-on-ubuntu-14-04">How To Install and Configure OrientDB on Ubuntu 14.04</a></p></li>
</ul>

<p>If you all all those things in place, let's get started.</p>

<h2 id="step-1-—-export-an-existing-orientdb-database">Step 1 — Export an Existing OrientDB Database</h2>

<p>To import an OrientDB database, you must first export the DB to be imported. In this step, we'll export the database that we need to import. </p>

<p>If OrientDB is not running, start it:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo service orientdb start
</li></ul></code></pre>
<p>If you aren't sure whether or not it is running, you can always check its status:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo service orientdb status
</li></ul></code></pre>
<p>Then connect to the server using the OrientDB console:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo -u orientdb /opt/orientdb/bin/console.sh
</li></ul></code></pre>
<p>The output should be:</p>
<pre class="code-pre "><code langs=""><div class="secondary-code-label " title="Output">Output</div>OrientDB console v.2.1.3 (build UNKNOWN@r; 2015-10-04 10:56:30+0000) www.orientdb.com
Type 'help' to display all the supported commands.
Installing extensions for GREMLIN language v.2.6.0

orientdb>
</code></pre>
<p>Connect to the database that you wish to export. Here we're connecting to the <strong>GratefulDeadConcerts</strong> database using the database's default user  <strong>admin</strong> and its password <strong>admin</strong>:</p>
<pre class="code-pre custom_prefix"><code langs=""><ul class="prefixed"><li class="line" prefix="orientdb>">connect plocal:/opt/orientdb/databases/GratefulDeadConcerts  admin admin
</li></ul></code></pre>
<p>You should see an output like this:</p>
<pre class="code-pre "><code langs=""><div class="secondary-code-label " title="Output">Output</div>Connecting to database [plocal:/opt/orientdb/databases/GratefulDeadConcerts] with user 'admin'...OK
orientdb {db=GratefulDeadConcerts}>
</code></pre>
<p>Alternatively, you can also connect to the database using the remote mode, which allows multiple users to access the same database.</p>
<pre class="code-pre custom_prefix"><code langs=""><ul class="prefixed"><li class="line" prefix="orientdb>">connect remote:127.0.0.1/GratefulDeadConcerts  admin admin
</li></ul></code></pre>
<p>The connection output should be of this sort:</p>
<pre class="code-pre "><code langs=""><div class="secondary-code-label " title="Output">Output</div>Disconnecting from the database [null]...OK
Connecting to database [remote:127.0.0.1/GratefulDeadConcerts] with user 'admin'...OK
orientdb {db=GratefulDeadConcerts}>
</code></pre>
<p>Now, export the database. The <code>export</code> command exports the current database to a gzipped, compressed JSON file. In this example, we're exporting it into OrientDB's database directory <code>/opt/orientdb/databases</code>:</p>
<pre class="code-pre custom_prefix"><code langs=""><ul class="prefixed"><li class="line" prefix="orientdb>">export database /opt/orientdb/databases/GratefulDeadConcerts.export
</li></ul></code></pre>
<p>The complete export command output for the target database is:</p>
<pre class="code-pre "><code langs=""><div class="secondary-code-label " title="Output">Output</div>Exporting current database to: database /opt/orientdb/databases/GratefulDeadConcerts.export in GZipped JSON format ...

Started export of database 'GratefulDeadConcerts' to /opt/orientdb/databases/GratefulDeadConcerts.export.gz...
Exporting database info...OK
Exporting clusters...OK (15 clusters)
Exporting schema...OK (14 classes)
Exporting records...
- Cluster 'internal' (id=0)...OK (records=3/3)
- Cluster 'index' (id=1)...OK (records=5/5)
- Cluster 'manindex' (id=2)...OK (records=1/1)
- Cluster 'default' (id=3)...OK (records=0/0)
- Cluster 'orole' (id=4)...OK (records=3/3)
- Cluster 'ouser' (id=5)...OK (records=3/3)
- Cluster 'ofunction' (id=6)...OK (records=0/0)
- Cluster 'oschedule' (id=7)...OK (records=0/0)
- Cluster 'orids' (id=8)...OK (records=0/0)
- Cluster 'v' (id=9).............OK (records=809/809)
- Cluster 'e' (id=10)...OK (records=0/0)
- Cluster 'followed_by' (id=11).............OK (records=7047/7047)
- Cluster 'written_by' (id=12).............OK (records=501/501)
- Cluster 'sung_by' (id=13).............OK (records=501/501)
- Cluster '_studio' (id=14)...OK (records=0/0)

Done. Exported 8873 of total 8873 records

Exporting index info...
- Index OUser.name...OK
- Index dictionary...OK
- Index ORole.name...OK
OK (3 indexes)
Exporting manual indexes content...
- Exporting index dictionary ...OK (entries=0)
OK (1 manual indexes)

Database export completed in 60498ms
</code></pre>
<p>That completes the export step. </p>

<p>Open another terminal to your Droplet, and list the contents of the database directory: </p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">ls -lh /opt/orientdb/databases
</li></ul></code></pre>
<p>You should see the original database plus the compressed file for your database export:</p>
<pre class="code-pre "><code langs=""><div class="secondary-code-label " title="Output">Output</div>total 164K
drwxr-xr-x 2 orientdb orientdb 4.0K Nov 27 02:36 GratefulDeadConcerts
-rw-r--r-- 1 orientdb orientdb 158K Nov 27 14:19 GratefulDeadConcerts.export.gz
</code></pre>
<p>Back at the terminal with your OrientDB console, you may now disconnect from the current database by typing:</p>
<pre class="code-pre custom_prefix"><code langs=""><ul class="prefixed"><li class="line" prefix="orientdb>">disconnect
</li></ul></code></pre>
<p>If successfully disconnected, you should get an output similar to:</p>
<pre class="code-pre "><code langs=""><div class="secondary-code-label " title="Output">Output</div>Disconnecting from the database [GratefulDeadConcerts]...OK
orientdb>
</code></pre>
<p>Keep the connection to the console open, because you'll be using it in the next step.</p>

<h2 id="step-2-—-import-database">Step 2 — Import Database</h2>

<p>In this step, we'll import the database we exported in Step 1. By default, importing a database overwrites the existing data in the one it's being imported into. So, first connect to the target database. In this example, we'll be connecting to the default database that we used in Step 1.</p>
<pre class="code-pre custom_prefix"><code langs=""><ul class="prefixed"><li class="line" prefix="orientdb>">connect plocal:/opt/orientdb/databases/GratefulDeadConcerts  admin admin
</li></ul></code></pre>
<p>You can also connect using:</p>
<pre class="code-pre custom_prefix"><code langs=""><ul class="prefixed"><li class="line" prefix="orientdb>">connect remote:127.0.0.1/GratefulDeadConcerts  admin admin
</li></ul></code></pre>
<p>Either output should be similar to this:</p>
<pre class="code-pre "><code langs=""><div class="secondary-code-label " title="Output">Output</div>Connecting to database [remote:127.0.0.1/GratefulDeadConcerts] with user 'admin'...OK
orientdb {db=GratefulDeadConcerts}>
</code></pre>
<p>With the connection established, let's import the exported file:</p>
<pre class="code-pre custom_prefix"><code langs=""><ul class="prefixed"><li class="line" prefix="orientdb>">import database /opt/orientdb/databases/GratefulDeadConcerts.export.gz
</li></ul></code></pre>
<p>Depending on the number of records to be imported, this can take more than a few minutes. So sit back and relax, or reach for that cup of your favorite liquid. </p>

<p>The import output should be (output truncated):</p>
<pre class="code-pre "><code langs=""><div class="secondary-code-label " title="Output">Output</div>Importing database database /opt/orientdb/databases/GratefulDeadConcerts.export.gz...
Started import of database 'remote:127.0.0.1/GratefulDeadConcerts' from /opt/orientdb/databases/GratefulDeadConcerts.export.gz...
Non merge mode (-merge=false): removing all default non security classes

...

Done. Imported 8,865 records in 915.51 secs


Importing indexes ...
- Index 'OUser.name'...OK
- Index 'dictionary'...OK
- Index 'ORole.name'...OK
Done. Created 3 indexes.
Importing manual index entries...
- Index 'dictionary'...OK (0 entries)
Done. Imported 1 indexes.
Rebuild of stale indexes...
Stale indexes were rebuilt...
Deleting RID Mapping table...OK


Database import completed in 1325943 ms
</code></pre>
<p>You can now disconnect from the database:</p>
<pre class="code-pre custom_prefix"><code langs=""><ul class="prefixed"><li class="line" prefix="orientdb>">disconnect
</li></ul></code></pre>
<p>The exit the OrientDB console and return to your regular shell prompt, type <code>exit</code>:</p>
<pre class="code-pre custom_prefix"><code langs=""><ul class="prefixed"><li class="line" prefix="orientdb>">exit
</li></ul></code></pre>
<h2 id="conclusion">Conclusion</h2>

<p>You've just seen how to export and import an OrientDB database. Note that the import/export feature does not lock the database during the entire process, so it's possible for it to be receiving writes as the process is taking place. For more information on this topic, see the <a href="http://orientdb.com/docs/last/Export-and-Import.html">official OrientDB export/import guide</a>.</p>

    