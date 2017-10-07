<link rel='stylesheet' href='digital_ocean.css'><br> <br> 
      <h3 id="introduction">Introduction</h3>

<p>OrientDB is a multi-model, NoSQL database with support for graph and document databases. It is a Java application and can run on any operating system; it's also fully ACID-complaint with support for multi-master replication.</p>

<p>An OrientDB database can be backed up using a backup script and also via the command line interface, with built-in support for compression of backup files using the ZIP algorithm.</p>

<p>By default, backing up an OrientDB database is a blocking operation — writes to be database are locked until the end of the backup operation, but if the operating system was installed on an LVM partitioning scheme, the backup script can perform a non-blocking backup. LVM is the Linux Logical Volume Manager.</p>

<p>In this article, you'll learn how to backup your OrientDB database on an Ubuntu 14.04 server.</p>

<h2 id="prerequisites">Prerequisites</h2>

<ul>
<li>Ubuntu 14.04 server (see (<a href="https://indiareads/community/tutorials/initial-server-setup-with-ubuntu-14-04">Initial Server Setup with Ubuntu 14.04</a>)</li>
<li>OrientDB installed and configured using <a href="https://indiareads/community/tutorials/how-to-install-and-configure-orientdb-on-ubuntu-14-04">How To Install and Configure OrientDB on Ubuntu 14.04</a></li>
</ul>

<h2 id="step-1-—-backing-up-orientdb-using-the-backup-script">Step 1 — Backing Up OrientDB Using the Backup Script</h2>

<p>OrientDB comes with a backup script located in the <code>bin</code> folder of the installation directory. If you installed OrientDB using <a href="https://indiareads/community/tutorials/how-to-install-and-configure-orientdb-on-ubuntu-14-04">How To Install and Configure OrientDB on Ubuntu 14.04</a>, then the installation directory is <code>/opt/orientdb</code>, so the backup script <code>backup.sh</code> should be in the <code>/opt/orientdb/bin</code>.</p>

<p>For this tutorial, create a <code>backup</code> folder under the installation directory to hold the backups. You may also opt to save the backups in the <code>databases</code> folder, which is the application's data directory. For this tutorial, we will use the <code>backup</code> folder, so create the <code>backup</code> directory:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo mkdir -p /opt/orientdb/backup
</li></ul></code></pre>
<p>The newly-created folder is owned by root, so let's change the ownership so that it's owned by the <strong>orientdb</strong> user. Failure to do this will lead to an error when backing up from the command line interface, which you'll learn how to accomplish in Step 2:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo chown -R orientdb:orientdb /opt/orientdb/backup
</li></ul></code></pre>
<p>With that out of the way, navigate into the <code>bin</code> directory:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">cd /opt/orientdb/bin
</li></ul></code></pre>
<p>By default, a database called <code>GratefulDeadConcerts</code> exists. Listing of the contents of the <code>databases</code> directory will show this default database and any that you have created:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">ls -l /opt/orientdb/databases
</li></ul></code></pre>
<p>For example, the following shows the <code>GratefulDeadConcerts</code> database and one called <code>eck</code>:</p>
<pre class="code-pre "><code langs=""><div class="secondary-code-label " title="Output">Output</div>total 8
drwxr-xr-x 2 orientdb orientdb 4096 Oct 12 18:36 eck
drwxr-xr-x 2 orientdb orientdb 4096 Oct  4 06:30 GratefulDeadConcerts
</code></pre>
<p>In this step, we'll back up both databases using the backup script. And in both cases, we'll be performing the operation as the <strong>admin</strong> user, whose password is also <strong>admin</strong>. To perform a default (blocking) backup of the default database, type:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo ./backup.sh plocal:../databases/GratefulDeadConcerts admin admin ../backup/gfdc.zip
</li></ul></code></pre>
<p>For the second database, type:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo ./backup.sh plocal:../databases/eck admin admin ../backup/eck.zip
</li></ul></code></pre>
<p>Verify that the backups were created:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">ls -lh ../backup
</li></ul></code></pre>
<p>The expected output is:</p>
<pre class="code-pre "><code langs=""><div class="secondary-code-label " title="Output">Output</div>total 236K
-rw-r--r-- 1 root root  17K Oct 13 08:48 eck.zip
-rw-r--r-- 1 root root 213K Oct 13 08:47 gfdc.zip
</code></pre>
<h2 id="step-2-—-backing-up-orientdb-from-the-console">Step 2 — Backing Up OrientDB from the Console</h2>

<p>In this step, we'll back up one of the databases from the console, or the command line interface. To enter the command line interface, type:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo -u orientdb /opt/orientdb/bin/console.sh
</li></ul></code></pre>
<p>The output should be:</p>
<pre class="code-pre "><code langs=""><div class="secondary-code-label " title="Output">Output</div>OrientDB console v.2.1.3 (build UNKNOWN@r; 2015-10-04 10:56:30+0000) www.orientdb.com
Type 'help' to display all the supported commands.
Installing extensions for GREMLIN language v.2.6.0

orientdb>
</code></pre>
<p>Next, connect to the database. Here we're connecting using the database's default user  <strong>admin</strong> and its password  <strong>admin</strong>.</p>
<pre class="code-pre custom_prefix"><code langs=""><ul class="prefixed"><li class="line" prefix="orientdb>">connect plocal:/opt/orientdb/databases/eck  admin admin
</li></ul></code></pre>
<p>You should see an output like this:</p>
<pre class="code-pre "><code langs=""><div class="secondary-code-label " title="Output">Output</div>Disconnecting from the database [null]...OK
Connecting to database [plocal:/opt/orientdb/databases/eck] with user 'admin'...OK
orientdb {db=eck}>
</code></pre>
<p>Now, perform a blocking backup of the database into the same backup directory that we created in Step 1:</p>
<pre class="code-pre custom_prefix"><code langs=""><ul class="prefixed"><li class="line" prefix="orientdb {db=eck}>">backup database /opt/orientdb/backup/eckconsole.zip
</li></ul></code></pre>
<p>You should see an output like this:</p>
<pre class="code-pre "><code langs=""><div class="secondary-code-label " title="Output">Output</div>Backuping current database to: database /opt/orientdb/backup/eckconsole.zip...

- Compressing file name_id_map.cm...ok size=912b compressedSize=250 ratio=73% elapsed=1ms
- Compressing file e.pcl...ok size=65.00KB compressedSize=121 ratio=100% elapsed=13ms

...


- Compressing file orids.cpm...ok size=1024b compressedSize=15 ratio=99% elapsed=1ms
- Compressing file internal.pcl...ok size=129.00KB compressedSize=9115 ratio=94% elapsed=9ms
Backup executed in 0.33 seconds
</code></pre>
<p>Exit the OrientDB database prompt:</p>
<pre class="code-pre custom_prefix"><code langs=""><ul class="prefixed"><li class="line" prefix="orientdb {db=eck}>">exit
</li></ul></code></pre>
<p>Confirm that the backup is in place:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">ls -lh ../backup
</li></ul></code></pre>
<p>Output should be similar to this:</p>
<pre class="code-pre "><code langs=""><div class="secondary-code-label " title="Output">Output</div>total 256K
-rw-r--r-- 1 orientdb orientdb  17K Oct 13 10:39 eckconsole.zip
-rw-r--r-- 1 orientdb orientdb  17K Oct 13 08:48 eck.zip
-rw-r--r-- 1 orientdb orientdb 213K Oct 13 08:47 gfdc.zip
</code></pre>
<h2 id="step-3-— backing-up-orientdb-automatically">Step 3 — Backing Up OrientDB Automatically</h2>

<p>OrientDB has automatic backup capability, but it's off by default. In this step, we'll enable it so that the databases are backed up daily. The parameters for automatic backup have to be tweaked in the configuration file, so open it:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo nano /opt/orientdb/config/orientdb-server-config.xml
</li></ul></code></pre>
<p>Scroll to the <strong>handler</strong> element with <strong>class="com.orientechnologies.orient.server.handler.OAutomaticBackup"</strong>. When enabled, the other default settings set automatic backup to take place at 23:00:00 GMT at 4 hour intervals. With the settings shown below, automatic backup will take place at the same time, but only once daily.</p>

<p>For testing purposes, you can adjust the <strong>firsttime</strong> parameter to your liking:</p>
<pre class="code-pre "><code langs=""><div class="secondary-code-label " title="/opt/orientdb/config/orientdb-server-config.xml">/opt/orientdb/config/orientdb-server-config.xml</div>
<handler class="com.orientechnologies.orient.server.handler.OAutomaticBackup">
<parameters>
<parameter value="<span class="highlight">true</span>" name="enabled"/>
<parameter value="<span class="highlight">24h</span>" name="delay"/>
<parameter value="23:00:00" name="firstTime"/>
<parameter value="backup" name="target.directory"/>
<parameter value="${DBNAME}-${DATE:yyyyMMddHHmmss}.zip" name="target.fileName"/>
<parameter value="9" name="compressionLevel"/>
<parameter value="1048576" name="bufferSize"/>
<parameter value="" name="db.include"/>
<parameter value="" name="db.exclude"/>
</parameters>
</handler>
</code></pre>
<p>When you've finished tweaking the settings, save and close the file. To apply the changes, stop the daemon:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo service orientdb stop
</li></ul></code></pre>
<p>Then restart it:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo service orientdb start
</li></ul></code></pre>
<p>After the set time, verify that it worked by looking in the new <code>backup</code> directory:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">ls -lh /opt/orientdb/bin/backup
</li></ul></code></pre>
<p>The output should be similar to this:</p>
<pre class="code-pre "><code langs=""><div class="secondary-code-label " title="Output">Output</div>total 236K
-rw-r--r-- 1 orientdb orientdb  17K Oct 13 16:00 eck-20151013160001.zip
-rw-r--r-- 1 orientdb orientdb 213K Oct 13 16:00 gratefulnotdead-20151013160002.zip
</code></pre>
<p>Out of the box, the default database <code>GratefulDeadConcert</code> is not backed up by the automatic backup tool, so if you don't see it listed, that's a feature.</p>

<h2 id="conclusion">Conclusion</h2>

<p>You've just learned all the non-programmatic steps available for backing up an OrientDB database. For more information on this topic, visit the <a href="http://orientdb.com/docs/last/Backup-and-Restore.html">official guide</a>.</p>

    