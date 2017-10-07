<link rel='stylesheet' href='digital_ocean.css'><br> <br> 
      <h3 id="introduction">Introduction</h3>

<p>Redis is an in-memory, key-value cache and store (a database, that is) that can also be persisted (saved permanently) to disk. In this article, you'll read how to back up a Redis database on an Ubuntu 14.04 server.</p>

<p>Redis data, by default, are saved to disk in a <code>.rdb</code> file, which is a point-in-time snapshot of your Redis dataset. The snapshot is made at specified intervals, and so is perfect for your backups.  </p>

<h2 id="prerequisites">Prerequisites</h2>

<p>To complete the steps in this tutorial, you'll need:</p>

<ul>
<li>An Ubuntu 14.04 server</li>
<li>Install Redis. You can follow just the <strong>master</strong> setup from <a href="https://indiareads/community/tutorials/how-to-configure-a-redis-cluster-on-ubuntu-14-04">this Redis setup tutorial</a> (although it will work just as well with a master-slave cluster)</li>
<li>Make sure that your Redis server is running</li>
<li>If a Redis password was set, which is highly recommended, have it handy. The password is in the Redis configuration file - <code>/etc/redis/redis.conf</code></li>
</ul>

<h2 id="step-1-—-locating-the-redis-data-directory">Step 1 — Locating the Redis Data Directory</h2>

<p>Redis stores its data in a directory on your server, which is what we want to back up. First we need to know where it is.</p>

<p>In Ubuntu and other Linux distributions, the Redis database directory is <code>/var/lib/redis</code>. But if you're managing a server that you inherited and the Redis data location was changed, you can locate it by typing:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo locate *rdb
</li></ul></code></pre>
<p>Alternatively, you may also find it from the <code>redis-cli</code> prompt. To do that, type:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">redis-cli
</li></ul></code></pre>
<p>If the Redis server is not running, the response will be:</p>
<div class="code-label " title="Output">Output</div><pre class="code-pre "><code langs="">Could not connect to Redis at 127.0.0.1:6379: Connection refused
not connected>
</code></pre>
<p>In that case, start Redis and reconnect using the following commands:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo service redis-server start
</li><li class="line" prefix="$">
</li><li class="line" prefix="$">redis-cli
</li></ul></code></pre>
<p>The shell prompt should now change to:</p>
<pre class="code-pre "><code langs="">127.0.0.1:6379>
</code></pre>
<p>While connected to Redis, the next two commands will authenticate to it and get the data directory:</p>
<pre class="code-pre custom_prefix"><code langs=""><ul class="prefixed"><li class="line" prefix="127.0.0.1:6379>">auth <span class="highlight">insert-redis-password-here</span>
</li><li class="line" prefix="127.0.0.1:6379>">
</li><li class="line" prefix="127.0.0.1:6379>">config get dir
</li></ul></code></pre>
<p>The output of the last command should be your Redis data directory:</p>
<div class="code-label " title="Output">Output</div><pre class="code-pre "><code langs="">1) "dir"
2) "<span class="highlight">/var/lib/redis</span>"
</code></pre>
<p>Make note of your Redis directory. If it's different than the directory shown, make sure you use this directory throughout the tutorial.</p>

<p>You can exit the database command line interface now:</p>
<pre class="code-pre custom_prefix"><code langs=""><ul class="prefixed"><li class="line" prefix="127.0.0.1:6379>">exit
</li></ul></code></pre>
<p>Check that this is the correct directory:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">ls /var/lib/redis
</li></ul></code></pre>
<p>You should see a <code>dump.rdb</code> file. That's the Redis data. If <code>appendonly</code> is also enabled, you will also see an <code>appendonly.aof</code> or another <code>.aof</code> file, which contains a log of all write operations received by the server.</p>

<p>See <a href="http://redis.io/topics/persistence">this post about Redis persistence</a> for a discussion of the differences between these two files. Basically, the <code>.rdb</code> file is a current snapshot, and the <code>.aof</code> file preserves your Redis history. Both are worth backing up.</p>

<p>We'll start with just the <code>.rdb</code> file, and end with an automated backup of both files.</p>

<h2 id="optional-step-2-—-adding-sample-data">(Optional) Step 2 — Adding Sample Data</h2>

<p>In this section you can create some sample data to store in your Redis database. If you already have data on your server, you can just back up your existing content.</p>

<p>Log in to the database command line interface:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">redis-cli
</li></ul></code></pre>
<p>Authenticate:</p>
<pre class="code-pre custom_prefix"><code langs=""><ul class="prefixed"><li class="line" prefix="127.0.0.1:6379>">auth <span class="highlight">insert-redis-password-here</span>
</li></ul></code></pre>
<p>Let's add some sample data. You should get a response of <code>OK</code> after each step.</p>
<pre class="code-pre custom_prefix"><code langs=""><ul class="prefixed"><li class="line" prefix="127.0.0.1:6379>">SET shapes:triangles "3 sides"
</li><li class="line" prefix="127.0.0.1:6379>">
</li><li class="line" prefix="127.0.0.1:6379>">SET shapes:squares "4 sides"
</li></ul></code></pre>
<p>Confirm that the data was added.</p>
<pre class="code-pre custom_prefix"><code langs=""><ul class="prefixed"><li class="line" prefix="127.0.0.1:6379>">GET shapes:triangles
</li><li class="line" prefix="127.0.0.1:6379>">
</li><li class="line" prefix="127.0.0.1:6379>">GET shapes:squares
</li></ul></code></pre>
<p>The output is included below:</p>
<div class="code-label " title="Output">Output</div><pre class="code-pre "><code langs="">"3 sides"

"4 sides"
</code></pre>
<p>To commit these changes to the <code>/var/lib/redis/dump.rdb</code> file, save them:</p>
<pre class="code-pre custom_prefix"><code langs=""><ul class="prefixed"><li class="line" prefix="127.0.0.1:6379>">save
</li></ul></code></pre>
<p>You can exit:</p>
<pre class="code-pre custom_prefix"><code langs=""><ul class="prefixed"><li class="line" prefix="127.0.0.1:6379>">exit
</li></ul></code></pre>
<p>If you'd like, you can check the contents of the dump file now. It should have your data, albeit in a machine-friendly form:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo cat /var/lib/redis/dump.rdb
</li></ul></code></pre><div class="code-label " title="/var/lib/redis/dump.rdb">/var/lib/redis/dump.rdb</div><pre class="code-pre "><code langs="">REDIS0006?shapes:squares4 sidesshapes:triangles3 sides??o????C
</code></pre>
<h2 id="step-3-—-backing-up-the-redis-data">Step 3 — Backing Up the Redis Data</h2>

<p>Now that you know where your Redis data are located, it's time to make the backup. From the official <a href="http://redis.io/topics/persistence">Redis website</a> comes this quote:</p>

<blockquote>
<p>Redis is very data backup friendly since you can copy RDB files while the database is running: the RDB is never modified once produced, and while it gets produced it uses a temporary name and is renamed into its final destination atomically using rename(2) only when the new snapshot is complete.</p>
</blockquote>

<p>So, you can back up or copy the database file while the Redis server is running. Assuming that you're backing it up to a directory under your home folder, performing that backup is as simple as typing:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo cp /var/lib/redis/dump.rdb <span class="highlight">/home/sammy/redis-backup-001</span>
</li></ul></code></pre>
<p><strong>Redis saves content here <em>periodically</em>, meaning that you aren't guaranteed an up-to-the-minute backup if the above command is all you run.</strong> You need to save your data first.</p>

<p>However, if a potentially small amount of data loss is acceptable, just backing up this one file will work.</p>

<p><strong>Saving the Database State</strong></p>

<p>To get a much more recent copy of the Redis data, a better route is to access <code>redis-cli</code>, the Redis command line.</p>

<p>Authenticate as explained in Step 1.</p>

<p>Then, issue the <code>save</code> command like so:</p>
<pre class="code-pre custom_prefix"><code langs=""><ul class="prefixed"><li class="line" prefix="127.0.0.1:6379>">save
</li></ul></code></pre>
<p>The output should be similar to this:</p>
<div class="code-label " title="Output">Output</div><pre class="code-pre "><code langs="">OK
(1.08s)
</code></pre>
<p>Exit the database.</p>

<p>Now you may run the <code>cp</code> command given above, confident that your backup is fully up to date.</p>

<p>While the <code>cp</code> command will provide a one-time backup of the database, the best solution is to set up a cron job that will automate the process, and to use a tool that can perform incremental updates and, if needed, restore the data.</p>

<h2 id="step-4-—-configuring-automatic-updates-with-rdiff-backup-and-cron">Step 4 — Configuring Automatic Updates with rdiff-backup and Cron</h2>

<p>In this section, we'll configure an automatic backup that backs up your entire Redis data directory, including both data files.</p>

<p>There are several automated backup tools available. In this tutorial, we'll use a newer, user-friendly tool called <code>rdiff-backup</code>.</p>

<p><code>rdiff-backup</code> a command line backup tool. It's likely that <code>rdiff-backup</code> is not installed on your server, so you'll first have to install it:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo apt-get install -y rdiff-backup
</li></ul></code></pre>
<p>Now that it's installed, you can test it by backing up your Redis data to a folder in your home directory. In this example, we assume that your home directory is <code>/home/<span class="highlight">sammy</span></code>:</p>

<p>Note that the target directory will be created by the script if it does not exist. In other words, you don't have to create it yourself.</p>

<p>With the <strong>--preserve-numerical-ids</strong>, the ownerships of the source and destination folders will be the same.</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo rdiff-backup --preserve-numerical-ids /var/lib/redis /home/<span class="highlight">sammy</span>/redis
</li></ul></code></pre>
<p>Like the <code>cp</code> command earlier, this is a one-time backup. What's changed is that we're backing up the entire <code>/var/lib/redis</code> directory now, and using <code>rdiff-backup</code>.</p>

<p>Now we'll automate the backup using cron, so that the backup takes place at a set time. To accomplish that, open the system crontab:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo crontab -e
</li></ul></code></pre>
<p>(If you haven't used crontab before on this server, select your favorite text editor at the prompt.)</p>

<p>At the bottom of the filek append the entry shown below.</p>
<div class="code-label " title="crontab">crontab</div><pre class="code-pre "><code langs=""><span class="highlight">0 0 * * * rdiff-backup --preserve-numerical-ids --no-file-statistics /var/lib/redis /home/sammy/redis</span>
</code></pre>
<p>This Cron entry will perform a Redis backup every day at midnight. The <strong>--no-file-statistics</strong> switch will disable writing to the <code>file_statistics</code> file in the <code>rdiff-backup-data</code>  directory, which will make <code>rdiff-backup</code> run more quickly and use up a bit less disk space.</p>

<p>Alternately, you can use this entry to make a daily backup:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$"><span class="highlight">@daily rdiff-backup --preserve-numerical-ids --no-file-statistics /var/lib/redis /home/sammy/redis</span>
</li></ul></code></pre>
<p>For more about Cron in general, read this <a href="https://indiareads/community/tutorials/how-to-schedule-routine-tasks-with-cron-and-anacron-on-a-vps">article about Cron</a>.</p>

<p>As it stands, the backup will be made once a day, so you can come back tomorrow for the final test. Or, you can temporarily increase the backup frequency to make sure it's working.</p>

<p>Because the files are owned by the <strong>redis</strong> system user, you can verify that they are in place using this command. (Make sure you wait until the backup has actually triggered):</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">ls -l /home/<span class="highlight">sammy</span>/redis
</li></ul></code></pre>
<p>Your output should look similar to this:</p>
<div class="code-label " title="Output">Output</div><pre class="code-pre "><code langs="">total 20
-rw-rw---- 1 redis redis    70 Sep 14 13:13 dump.rdb
drwx------ 3 root  root  12288 Sep 14 13:49 rdiff-backup-data
-rw-r----- 1 redis redis   119 Sep 14 13:09 redis-staging-ao.aof
</code></pre>
<p>You'll now have daily backups of your Redis data, stored in your home directory on the same server.</p>

<h2 id="step-5-—-restoring-redis-database-from-backup">Step 5 — Restoring Redis Database from Backup</h2>

<p>Now that you've seen how to back up a Redis database, this step will show you how to restore your database from a <code>dump.rdb</code> backup file.</p>

<p>Restoring a backup requires you to replace the active Redis database file with the restoration file. <strong>Since this is potentially destructive, we recommend restoring to a fresh Redis server if possible.</strong></p>

<p>You wouldn't want to overwrite your live database with a more problematic restoration. However, renaming rather than deleting the current file minimizes risk even if restoring to the same server, which is the tactic this tutorial shows.</p>

<h3 id="checking-restoration-file-contents">Checking Restoration File Contents</h3>

<p>First, check the contents of your <code>dump.rdb</code> file. Make sure it has the data you want.</p>

<p>You can check the contents of the dump file directly, although keep in mind it uses Redis-friendly rather than human-friendly formatting:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo cat /home/gilly/redis/dump.rdb
</li></ul></code></pre>
<p>This is for a small database; your output should look somewhat like this:</p>
<div class="code-label " title="Output">Output</div><pre class="code-pre "><code langs="">REDIS0006?shapes:triangles3 sidesshapes:squares4 sides??!^?\?,?
</code></pre>
<p>If your most recent backup doesn't have the data, you should not continue with the restoration. If the content is there, keep going.</p>

<h3 id="optional-simulating-data-loss">Optional: Simulating Data Loss</h3>

<p>Let's simulate data loss, which would be a reason to restore from your backup.</p>

<p>Log in to Redis:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">redis-cli
</li></ul></code></pre>
<p>In this sequence of commands we'll authorize with Redis and delete the <code>shapes:triangles</code> entry:</p>
<pre class="code-pre custom_prefix"><code langs=""><ul class="prefixed"><li class="line" prefix="127.0.0.1:6379>">auth <span class="highlight">insert-redis-password-here</span>
</li><li class="line" prefix="127.0.0.1:6379>">
</li><li class="line" prefix="127.0.0.1:6379>">DEL shapes:triangles
</li></ul></code></pre>
<p>Now let's make sure the entry was removed:</p>
<pre class="code-pre custom_prefix"><code langs=""><ul class="prefixed"><li class="line" prefix="127.0.0.1:6379>">GET shapes:triangles
</li></ul></code></pre>
<p>The output should be:</p>
<div class="code-label " title="Output">Output</div><pre class="code-pre "><code langs="">(nil)
</code></pre>
<p>Save and exit:</p>
<pre class="code-pre custom_prefix"><code langs=""><ul class="prefixed"><li class="line" prefix="127.0.0.1:6379>">save
</li><li class="line" prefix="127.0.0.1:6379>">
</li><li class="line" prefix="127.0.0.1:6379>">exit
</li></ul></code></pre>
<h3 id="optional-setting-up-new-redis-server">Optional: Setting Up New Redis Server</h3>

<p>Now, if you plan to restore to a new Redis server, make sure that new Redis server is up and running.</p>

<p>For the purposes of this tutorial we'll follow just <strong>Step 1</strong> of this <a href="https://indiareads/community/tutorials/how-to-configure-a-redis-cluster-on-ubuntu-14-04">Redis Cluster tutorial</a>, although you can follow the whole article if you want a more sophisticated setup.</p>

<p>If you follow <strong>Step 2</strong>, where you add a password and enable AOF, make sure you account for that in the restoration process.</p>

<p>Once you've verified that Redis is up on the new server by running <code>redis-benchmark -q -n 1000 -c 10 -P 5</code>, you can proceed.</p>

<h3 id="stopping-redis">Stopping Redis</h3>

<p>Before we can replace the Redis dump file, we need to stop the currently running instance of Redis. <strong>Your database will be offline once you stop Redis.</strong></p>
<pre class="code-pre "><code langs="">sudo service redis-server stop
</code></pre>
<p>The output should be:</p>
<div class="code-label " title="Output">Output</div><pre class="code-pre "><code langs="">Stopping redis-server: redis-server
</code></pre>
<p>Check that it's actually stopped:</p>
<pre class="code-pre "><code langs="">sudo service redis-server status
</code></pre><div class="code-label " title="Output">Output</div><pre class="code-pre "><code langs="">redis-server is not running
</code></pre>
<p>Next we'll rename the current database file.</p>

<h3 id="renaming-current-dump-rdb">Renaming Current dump.rdb</h3>

<p>Redis reads its contents from the <code>dump.rdb</code> file. Let's rename the current one, to make way for our restoration file.</p>
<pre class="code-pre "><code langs="">sudo mv /var/lib/redis/dump.rdb /var/lib/redis/dump.rdb.old
</code></pre>
<p>Note that you can restore <code>dump.rdb.old</code> if you decide the current version was better than your backup file.</p>

<h3 id="if-aof-is-enabled-turn-it-off">If AOF Is Enabled, Turn It Off</h3>

<p>AOF tracks every write operation to the Redis database. Since we're trying to restore from a point-in-time backup, though, we don't want Redis to recreate the operations stored in its AOF file.</p>

<p>If you set up your Redis server from the instructions in the <a href="https://indiareads/community/tutorials/how-to-configure-a-redis-cluster-on-ubuntu-14-04">Redis Cluster tutorial</a>, then AOF is enabled.</p>

<p>You can also list the contents of the <code>/var/lib/redis/</code> directory. If you see a <code>.aof</code> file there, you have AOF enabled.</p>

<p>Let's rename the <code>.aof</code> file to get it out of the way temporarily. This renames every file that ends with <code>.aof</code>, so if you have more than one AOF file you should rename the files individually, and NOT run this command:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo mv /var/lib/redis/*.aof /var/lib/redis/appendonly.aof.old
</li></ul></code></pre>
<p>Edit your Redis configuration file to temporarily turn off AOF:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo nano /etc/redis/redis.conf
</li></ul></code></pre>
<p>In the <code>AOF</code> section, look for the <code>appendonly</code> directive and change it from <code><span class="highlight">yes</span></code> to <code><span class="highlight">no</span></code>. That disables it:</p>
<div class="code-label " title="/etc/redis/redis.conf">/etc/redis/redis.conf</div><pre class="code-pre "><code langs="">appendonly <span class="highlight">no</span>
</code></pre>
<h3 id="restoring-the-dump-rdb-file">Restoring the dump.rdb File</h3>

<p>Now we'll use our restoration file, which should be saved at <code>/home/<span class="highlight">sammy</span>/redis/dump.rdb</code> if you followed the previous steps in this tutorial.</p>

<p>If you are restoring to a new server, now's the time to upload the file from your backup server to the new server:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">scp /home/<span class="highlight">sammy</span>/redis/dump.rdb <span class="highlight">sammy</span>@<span class="highlight">your_new_redis_server_ip</span>:/home/<span class="highlight">sammy</span>/dump.rdb
</li></ul></code></pre>
<p>Now, <strong>on the restoration server</strong>, which can be the original Redis server or a new one, you can use <code>cp</code> to copy the file to the <code>/var/lib/redis</code> folder:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo cp -p /home/<span class="highlight">sammy/redis</span>/dump.rdb /var/lib/redis
</li></ul></code></pre>
<p>(If you uploaded the file to <code>/home/<span class="highlight">sammy</span>/dump.rdb</code>, use the command <code>sudo cp -p /home/<span class="highlight">sammy</span>/dump.rdb /var/lib/redis</code> instead to copy the file.)</p>

<p>Alternately, if you want to use <code>rdiff-backup</code>, run the command shown below. Note this will only work if you are restoring from the folder you set up with <code>rdiff-backup</code> originally. With <code>rdiff-backup</code>, you have to specify the name of the file in the destination folder:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo rdiff-backup -r now /home/<span class="highlight">sammy</span>/redis/dump.rdb /var/lib/redis/dump.rdb
</li></ul></code></pre>
<p>Details about the <code>-r</code> option are available on the project's website given at the end of this article.</p>

<h3 id="setting-permissions-for-the-dump-rdb-file">Setting Permissions for the dump.rdb File</h3>

<p>You probably have the correct permissions already if you're restoring to the same server where you made the backup.</p>

<p>If you copied the backup file to a new server, you'll likely have to update the file permissions.</p>

<p>Let's view the permissions of the <code>dump.rdb</code> file in the <code>/var/lib/redis/</code> directory.</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">ls -la /var/lib/redis/
</li></ul></code></pre>
<p>If you see something like this:</p>
<div class="code-label " title="Output">Output</div><pre class="code-pre "><code langs=""><span class="highlight">-rw-r-----  1 sammy sammy   70 Feb 25 15:38 dump.rdb</span>
-rw-rw----  1 redis redis 4137 Feb 25 15:36 dump.rdb.old
</code></pre>
<p>You'll want to update the permissions so the file is owned by the <strong>redis</strong> user and group:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo chown redis:redis /var/lib/redis/dump.rdb
</li></ul></code></pre>
<p>Update the file to be writeable by the group as well:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo chmod 660 /var/lib/redis/dump.rdb
</li></ul></code></pre>
<p>Now list the contents of the <code>/var/lib/redis/</code> directory again:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">ls -la /var/lib/redis/
</li></ul></code></pre>
<p>Now your restored <code>dump.rdb</code> file has the correct permissions:</p>
<div class="code-label " title="Output">Output</div><pre class="code-pre "><code langs=""><span class="highlight">-rw-rw----  1 redis redis   70 Feb 25 15:38 dump.rdb</span>
-rw-rw----  1 redis redis 4137 Feb 25 15:36 dump.rdb.old
</code></pre>
<span class="note"><p>
If your Redis server daemon was running before you restored the file, and now won't start — it will show a message like <code>Could not connect to Redis at 127.0.0.1:6379: Connection refused</code> — check Redis's logs.</p>

<ul>
<li><a href="https://indiareads/community/tutorials/how-to-find-redis-logs-on-ubuntu">How To Find Redis Logs on Ubuntu</a></li>
</ul>

<p>If you see a line in the logs like <code>Fatal error loading the DB: Permission denied. Exiting.</code>, then you need to check the permissions of the <code>dump.rdb</code> file, as explained in this step.<br /></p></span>

<h3 id="starting-redis">Starting Redis</h3>

<p>Now we need to start the Redis server again.</p>
<pre class="code-pre "><code langs="">sudo service redis-server start
</code></pre>
<h3 id="checking-database-contents">Checking Database Contents</h3>

<p>Let's see if the restoration worked.</p>

<p>Log in to Redis:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">redis-cli
</li></ul></code></pre>
<p>Check the <code>shapes:triangles</code> entry:</p>
<pre class="code-pre custom_prefix"><code langs=""><ul class="prefixed"><li class="line" prefix="127.0.0.1:6379>">GET shapes:triangles
</li></ul></code></pre>
<p>The output should be:</p>
<div class="code-label " title="Output">Output</div><pre class="code-pre "><code langs="">"3 sides"
</code></pre>
<p>Great! Our restoration worked.</p>

<p>Exit:</p>
<pre class="code-pre custom_prefix"><code langs=""><ul class="prefixed"><li class="line" prefix="127.0.0.1:6379>">exit
</li></ul></code></pre>
<p>If you're not using AOF, you're done! Your restored Redis instance should be back to normal.</p>

<h3 id="optional-enabling-aof">(Optional) Enabling AOF</h3>

<p>If you want to resume or start using AOF to track all the writes to your database, follow these instructions. The AOF file has to be recreated from the Redis command line.</p>

<p>Log in to Redis:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">redis-cli
</li></ul></code></pre>
<p>Turn on AOF:</p>
<pre class="code-pre custom_prefix"><code langs=""><ul class="prefixed"><li class="line" prefix="127.0.0.1:6379>">BGREWRITEAOF
</li></ul></code></pre>
<p>You should get the output:</p>
<div class="code-label " title="Output">Output</div><pre class="code-pre "><code langs="">Background append only file rewriting started
</code></pre>
<p>Run the <code>info</code> command. This will generate quite a bit of output:</p>
<pre class="code-pre custom_prefix"><code langs=""><ul class="prefixed"><li class="line" prefix="127.0.0.1:6379>">info
</li></ul></code></pre>
<p>Scroll to the <strong>Persistence</strong> section, and check that the <strong>aof</strong> entries match what's shown here. If <strong>aof_rewrite_in_progress</strong> is <strong>0</strong>, then the recreation of the AOF file has completed.</p>
<div class="code-label " title="Output">Output</div><pre class="code-pre "><code langs=""># Persistence

. . .

aof_enabled:0
aof_rewrite_in_progress:0
aof_rewrite_scheduled:0
aof_last_rewrite_time_sec:0
aof_current_rewrite_time_sec:-1
aof_last_bgrewrite_status:ok
aof_last_write_status:ok
</code></pre>
<p>If it's confirmed that recreation of the AOF file has completed, you may now exit the Redis command line:</p>
<pre class="code-pre custom_prefix"><code langs=""><ul class="prefixed"><li class="line" prefix="127.0.0.1:6379>">exit
</li></ul></code></pre>
<p>You can list the files in <code>/var/lib/redis</code> again:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">ls /var/lib/redis
</li></ul></code></pre>
<p>You should see a live <code>.aof</code> file again, such as <code>appendonly.aof</code> or <code>redis-staging-ao.aof</code>, along with the <code>dump.rdb</code> file and other backup files.</p>

<p>Once that's confirmed, stop the Redis server:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo service redis-server stop
</li></ul></code></pre>
<p>Now, turn on AOF again in the <code>redis.conf</code> file:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo nano /etc/redis/redis.conf
</li></ul></code></pre>
<p>Then re-enable AOF by changing the value of <code>appendonly</code> to <code>yes</code>:</p>
<div class="code-label " title="/etc/redis/redis.conf">/etc/redis/redis.conf</div><pre class="code-pre "><code langs="">appendonly <span class="highlight">yes</span>
</code></pre>
<p>Start Redis:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo service redis-server start
</li></ul></code></pre>
<p>If you'd like to verify the contents of the database one more time, just run through the <strong>Checking Database Contents</strong> section once more.</p>

<p>That's it! Your restored Redis instance should be back to normal.</p>

<h2 id="conclusion">Conclusion</h2>

<p>Backing up your Redis data in the manner given in this article is good for when you don't mind backing up the data to a directory on the same server.</p>

<p>The most secure approach is, of course, to back up to a different machine. You can explore more backup options by reading this article about backups:</p>

<ul>
<li><a href="https://indiareads/community/tutorials/how-to-choose-an-effective-backup-strategy-for-your-vps">How To Choose an Effective Backup Strategy for your VPS</a></li>
</ul>

<p>You can use many of these backup methods with the same files in the <code>/var/lib/redis</code> directory.</p>

<p>Keep an eye out for our future article about Redis migrations and restorations. You may also want to reference the <code>rdiff-backup</code> documentation's examples for how to use <code>rdiff-backup</code> effectively:</p>

<ul>
<li><a href="http://www.nongnu.org/rdiff-backup/examples.html">rdiff-backup Examples</a></li>
</ul>

    