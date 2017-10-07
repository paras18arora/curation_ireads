<link rel='stylesheet' href='digital_ocean.css'><br><img src=https://community-cdn-digitalocean-com.global.ssl.fastly.net/assets/tutorials/images/large/mysql_databases_tw.jpg?1430323745/> <br> 
      <h3 id="introduction">Introduction</h3>

<p>A very common challenge encountered when working with active database systems is performing hot backups—that is, creating backups without stopping the database service or making it read-only. Simply copying the data files of an active database will often result in a copy of the database that is internally inconsistent, i.e. it will not be usable or it will be missing transactions that occurred during the copy. On the other hand, stopping the database for scheduled backups renders database-dependent portions of your application to become unavailable. Percona XtraBackup is an open source utility that can be used to circumvent this issue, and create consistent full or incremental backups of running MySQL, MariaDB, and Percona Server databases, also known as hot backups.</p>

<p>As opposed to the <em>logical backups</em> that utilities like mysqldump produce, XtraBackup creates <em>physical backups</em> of the database files—it makes a copy of the data files. Then it applies the transaction log (a.k.a. redo log) to the physical backups, to backfill any active transactions that did not finish during the creation of the backups, resulting in consistent backups of a running database. The resulting database backup can then be backed up to a remote location using <a href="https://indiareads/community/tutorials/how-to-use-rsync-to-sync-local-and-remote-directories-on-a-vps">rsync</a>, a backup system like <a href="https://indiareads/community/tutorials/how-to-install-bacula-server-on-ubuntu-14-04">Bacula</a>, or <a href="https://indiareads/community/tutorials/understanding-digitalocean-droplet-backups">IndiaReads backups</a>.</p>

<p>This tutorial will show you how to perform a full hot backup of your MySQL or MariaDB databases using Percona XtraBackup on Ubuntu 14.04. The process of restoring the database from a backup is also covered. The CentOS 7 version of this guide can be found <a href="https://indiareads/community/tutorials/how-to-create-hot-backups-of-mysql-databases-with-percona-xtrabackup-on-centos-7">here</a>.</p>

<h2 id="prerequisites">Prerequisites</h2>

<p>To follow this tutorial, you must have the following:</p>

<ul>
<li>Superuser privileges on an Ubuntu 14.04 system</li>
<li>A running MySQL or MariaDB database</li>
<li>Access to the admin user (root) of your database</li>
</ul>

<p>Also, to perform a hot backup of your database, your database system must be using the <strong>InnoDB</strong> storage engine. This is because XtraBackup relies on the transaction log that InnoDB maintains. If your databases are using the MyISAM storage engine, you can still use XtraBackup but the database will be locked for a short period towards the end of the backup.</p>

<h3 id="check-storage-engine">Check Storage Engine</h3>

<p>If you are unsure of which storage engine your databases use, you can look it up through a variety of methods. One way is to use the MySQL console to select the database in question, then output the status of each table.</p>

<p>First, enter the MySQL console:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">mysql -u root -p
</li></ul></code></pre>
<p>Then enter your MySQL root password.</p>

<p>At the MySQL prompt, select the database that you want to check. Be sure to substitute your own database name here:</p>
<pre class="code-pre custom_prefix"><code langs=""><ul class="prefixed"><li class="line" prefix="mysql>">USE <span class="highlight">database_name</span>;
</li></ul></code></pre>
<p>Then print its table statuses:</p>
<pre class="code-pre custom_prefix"><code langs=""><ul class="prefixed"><li class="line" prefix="mysql>">SHOW TABLE STATUS\G;
</li></ul></code></pre>
<p>The engine should be indicated for each row in the database:</p>
<pre class="code-pre "><code langs=""><div class="secondary-code-label " title="Example Output:">Example Output:</div>...
*************************** 11. row ***************************
           Name: wp_users
         Engine: <span class="highlight">InnoDB</span>
...
</code></pre>
<p>Once you are done, leave the console:</p>
<pre class="code-pre custom_prefix"><code langs=""><ul class="prefixed"><li class="line" prefix="mysql>">exit
</li></ul></code></pre>
<p>Let's install Percona XtraBackup.</p>

<h2 id="install-percona-xtrabackup">Install Percona XtraBackup</h2>

<p>The easiest way to install Percona XtraBackup is to use apt-get.</p>

<p>Add the Percona repository key with this command:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo apt-key adv --keyserver keys.gnupg.net --recv-keys 1C4CBDCDCD2EFD2A
</li></ul></code></pre>
<p>Then add the Percona repository to your apt sources:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo sh -c "echo 'deb http://repo.percona.com/apt trusty main' > /etc/apt/sources.list.d/percona.list"
</li><li class="line" prefix="$">sudo sh -c "echo 'deb-src http://repo.percona.com/apt trusty main' >> /etc/apt/sources.list.d/percona.list"
</li></ul></code></pre>
<p>Run this command to update your apt sources:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo apt-get update
</li></ul></code></pre>
<p>Finally, you can run this command to install XtraBackup:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo apt-get install percona-xtrabackup
</li></ul></code></pre>
<p>XtraBackup consists primarily of the XtraBackup program, and the <code>innobackupex</code> Perl script, which we will use to create our database backups.</p>

<h2 id="first-time-preparations">First Time Preparations</h2>

<p>Before using XtraBackup for the first time, we need to prepare system and MySQL user that XtraBackup will use. This section covers the initial preparation.</p>

<h3 id="system-user">System User</h3>

<p>Unless you plan on using the system root user, you must perform some basic preparations to ensure that XtraBackup can be executed properly. We will assume that you are logged in as the user that will run XtraBackup, and that it has superuser privileges.</p>

<p>Add your system user to the "mysql" group (substitute in your actual username):</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo gpasswd -a <span class="highlight">username</span> mysql
</li></ul></code></pre>
<p>While we're at it, let's create the directory that will be used for storing the backups that XtraBackup creates:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo mkdir -p /data/backups
</li><li class="line" prefix="$">sudo chown -R <span class="highlight">username</span>: /data
</li></ul></code></pre>
<p>The <code>chown</code> command ensures that the user will be able to write to the backups directory.</p>

<h3 id="mysql-user">MySQL User</h3>

<p>XtraBackup requires a MySQL user that it will use when creating backups. Let's create one now.</p>

<p>Enter the MySQL console with this command:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">mysql -u root -p
</li></ul></code></pre>
<p>Supply the MySQL root password.</p>

<p>At the MySQL prompt, create a new MySQL user and assign it a password. In this example, the user is called "bkpuser" and the password is "bkppassword". Change both of these to something secure:</p>
<pre class="code-pre custom_prefix"><code langs=""><ul class="prefixed"><li class="line" prefix="mysql>">CREATE USER '<span class="highlight">bkpuser</span>'@'localhost' IDENTIFIED BY '<span class="highlight">bkppassword</span>';
</li></ul></code></pre>
<p>Next, grant the new MySQL user reload, lock, and replication privileges to all of the databases:</p>
<pre class="code-pre custom_prefix"><code langs=""><ul class="prefixed"><li class="line" prefix="mysql>">GRANT RELOAD, LOCK TABLES, REPLICATION CLIENT ON *.* TO '<span class="highlight">bkpuser</span>'@'localhost';
</li><li class="line" prefix="mysql>">FLUSH PRIVILEGES;
</li></ul></code></pre>
<p>These are the minimum required privileges that XtraBackup needs to create full backups of databases.</p>

<p>When you are finished, exit the MySQL console:</p>
<pre class="code-pre custom_prefix"><code langs=""><ul class="prefixed"><li class="line" prefix="mysql>">exit
</li></ul></code></pre>
<p>Now we're ready to create a full backup of our databases.</p>

<h2 id="perform-full-hot-backup">Perform Full Hot Backup</h2>

<p>This section covers the steps that are necessary to create a full hot backup of a MySQL database using XtraBackup. After ensuring that the database file permissions are correct, we will use XtraBackup to <strong>create</strong> a backup, then <strong>prepare</strong> it.</p>

<h3 id="update-datadir-permissions">Update Datadir Permissions</h3>

<p>On Ubuntu 14.04, MySQL's data files are stored in <code>/var/lib/mysql</code>, which is sometimes referred to as a <strong>datadir</strong>. By default, access to the datadir is restricted to the <code>mysql</code> user. XtraBackup requires access to this directory to create its backups, so let's run a few commands to ensure that the system user we set up earlier—as a member of the mysql group—has the proper permissions:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo chown -R mysql: /var/lib/mysql
</li><li class="line" prefix="$">sudo find /var/lib/mysql -type d -exec chmod 770 "{}" \;
</li></ul></code></pre>
<p>These commands ensure that all of the directories in the datadir are accessible to the mysql group, and should be run prior to each backup.</p>
<pre class="code-pre note"><code langs="">If you added your user to the mysql group in the same session, you will need to login again for the group membership changes to take effect.
</code></pre>
<h3 id="create-backup">Create Backup</h3>

<p>Now we're ready to create the backup. With the MySQL database running, use the <code>innobackupex</code> utility to do so. Run this command after updating the user and password to match your MySQL user's login:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">innobackupex --user=<span class="highlight">bkpuser</span>  --password=<span class="highlight">bkppassword</span> --no-timestamp /data/backups/new_backup
</li></ul></code></pre>
<p>This will create a backup of the database at the location specified, <code>/data/backups/new_backup</code>:</p>
<pre class="code-pre "><code langs=""><div class="secondary-code-label " title="innobackupex output">innobackupex output</div>innobackupex: Backup created in directory '/data/backups/new_backup'
150420 13:50:10  innobackupex: Connection to database server closed
150420 13:50:10  innobackupex: completed OK!
</code></pre>
<p><strong>Alternatively</strong>, you may omit the <code>--no-timestamp</code> to have XtraBackup create a backup directory based on the current timestamp, like so:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">innobackupex --user=<span class="highlight">bkpuser</span>  --password=<span class="highlight">bkppassword</span> /data/backups
</li></ul></code></pre>
<p>This will create a backup of the database in an automatically generated subdirectory, like so:</p>
<pre class="code-pre "><code langs=""><div class="secondary-code-label " title="innobackupex output — no timestamp">innobackupex output — no timestamp</div>innobackupex: Backup created in directory '/data/backups/<span class="highlight">2015-04-20_13-50-07</span>'
150420 13:50:10  innobackupex: Connection to database server closed
150420 13:50:10  innobackupex: completed OK!
</code></pre>
<p>Either method that you decide on should output "innobackupex: completed OK!" on the last line of its output. A successful backup will result in a copy of the database datadir, which must be <strong>prepared</strong> before it can be used.</p>

<h2 id="prepare-backup">Prepare Backup</h2>

<p>The last step in creating a hot backup with XtraBackup is to <strong>prepare</strong> it. This involves "replaying" the transaction log to apply any uncommitted transaction to the backup. Preparing the backup will make its data consistent, and usable for a restore.</p>

<p>Following our example, we will prepare the backup that was created in <code>/data/backups/new_backup</code>. Substitute this with the path to your actual backup:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">innobackupex --apply-log /data/backups/<span class="highlight">new_backup</span>
</li></ul></code></pre>
<p>Again, you should see "innobackupex: completed OK!" as the last line of output.</p>

<p>Your database backup has been created and is ready to be used to restore your database. Also, if you have a file backup system, such as <a href="https://indiareads/community/tutorial_series/how-to-use-bacula-on-ubuntu-14-04">Bacula</a>, this database backup should be included as part of your backup selection.</p>

<p>The next section will cover how to restore your database from the backup we just created.</p>

<h2 id="perform-backup-restoration">Perform Backup Restoration</h2>

<p>Restoring a database with XtraBackup requires that the database is stopped, and that its datadir is empty.</p>

<p>Stop the MySQL service with this command:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo service mysql stop
</li></ul></code></pre>
<p>Then move or delete the contents of the datadir (<code>/var/lib/mysql</code>). In our example, we'll simply move it to a temporary location:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">mkdir /tmp/mysql
</li><li class="line" prefix="$">mv /var/lib/mysql/* /tmp/mysql/
</li></ul></code></pre>
<p>Now we can restore the database from our backup, "new_backup":</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">innobackupex --copy-back /data/backups/<span class="highlight">new_backup</span>
</li></ul></code></pre>
<p>If it was successful, the last line of output should say "innobackupex: completed OK!"</p>

<p>The restored files in datadir will probably belong to the user you ran the restore process as. Change the ownership back to mysql, so MySQL can read and write the files:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo chown -R mysql: /var/lib/mysql
</li></ul></code></pre>
<p>Now we're ready to start MySQL:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo service mysql start
</li></ul></code></pre>
<p>That's it! Your restored MySQL database should be up and running.</p>

<h2 id="conclusion">Conclusion</h2>

<p>Now that you are able to create hot backups of your MySQL database using Percona XtraBackup, there are several things that you should consider setting up.</p>

<p>First of all, it is advisable to automate the process so you will have backups created according to a schedule. Second, you should make remote copies of the backups, in case your database server has problems, by using something like <a href="https://indiareads/community/tutorials/how-to-use-rsync-to-sync-local-and-remote-directories-on-a-vps">rsync</a>, a network file backup system like <a href="https://indiareads/community/tutorials/how-to-install-bacula-server-on-ubuntu-14-04">Bacula</a>, or <a href="https://indiareads/community/tutorials/understanding-digitalocean-droplet-backups">IndiaReads backups</a>. After that, you will want to look into <strong>rotating</strong> your backups (deleting old backups on a schedule) and creating incremental backups (with XtraBackup) to save disk space.</p>

<p>Good luck!</p>

    