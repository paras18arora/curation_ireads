<link rel='stylesheet' href='digital_ocean.css'><br> <br> 
      <h3 id="introduction">Introduction</h3>

<p><strong>PostgreSQL</strong> is an open-source database platform quite popular with web and mobile application developers for its ease of maintenance, cost effectiveness, and simple integration with other open-source technologies.</p>

<p>One critical task of maintaining a PostgreSQL environment is to back up its databases regularly. Backups form part of the Disaster Recovery (DR) process for any organization. This is important for a few reasons:</p>

<ul>
<li>Safeguarding against data loss due to failure of underlying infrastructure components like storage or the server itself</li>
<li>Safeguarding against data corruption and unwanted or malicious loss of data</li>
<li>Migrating production databases into development or test environments</li>
</ul>

<p>Usually the responsibility of database backup and restoration falls on the shoulder of a DBA. In smaller organizations or startups, however, system administrators, DevOps engineers, or programmers often have to create their own database backends. So, it's important for everyone using PostgreSQL to understand how backups work and how to restore from a backup.</p>

<p>In this tutorial you'll set up the Barman backup server, make a backup from a primary database server, and restore to a standby server.</p>

<h3 id="a-brief-introduction-to-postgresql-backup-methods">A Brief Introduction to PostgreSQL Backup Methods</h3>

<p>Before launching into your Barman setup, let's take a moment to review the types of backups available for PostgreSQL, and their uses. (For an even broader overview of backup strategies, read our article about <a href="https://indiareads/community/tutorials/how-to-choose-an-effective-backup-strategy-for-your-vps">effective backups</a>.)</p>

<p>PostgreSQL offers two types of backup methods:</p>

<ul>
<li>Logical backups</li>
<li>Physical backups</li>
</ul>

<p>Logical backups are like snapshots of a database. These are created using the <code>pg_dump</code> or <code>pg_dumpall</code> utility that ships with PostgreSQL. Logical backups:</p>

<ul>
<li>Back up individual databases or all databases</li>
<li>Back up just the schemas, just the data, individual tables, or the whole database (schemas and data)</li>
<li>Create the backup file in proprietary binary format or in plain SQL script</li>
<li>Can be restored using the <code>pg_restore</code> utility which also ships with PostgreSQL</li>
<li>Do not offer <strong>point-in-time recovery (PITR)</strong></li>
</ul>

<p>This means if you make a logical backup of your database(s) at 2:00 AM in the morning, when you restore from it, the restored database will be as it was at 2:00 AM. There is no way to stop the restore at a particular point in time, say at 1:30 AM. If you are restoring the backup at 10:00 AM, you have lost eight hours' worth of data.</p>

<p>Physical backups are different from logical backups because they deal with binary format only and makes file-level backups. Physical backups:</p>

<ul>
<li>Offer point-in-time recovery</li>
<li>Back up the contents of the PostgreSQL <em>data directory</em> and the <em>WAL</em> (Write Ahead Log) files</li>
<li>Take larger amounts of disk space</li>
<li>Use the PostgreSQL <code>pg_start_backup</code> and <code>pg_stop_backup</code> commands. However, these commands need to be scripted, which makes physical backups a more complex process</li>
<li>Do not back up individual databases, schemas only, etc. It's an all-or-nothing approach</li>
</ul>

<p>WAL files contain lists of <em>transactions</em> (INSERT, UPDATE or DELETE) that happen to a database. The actual database files containing the data are located within the data directory. So when it comes to restoring to a point in time from a physical backup, PostgreSQL restores the contents of the data directory first, and then plays the transactions on top of it from the WAL files. This brings the databases to a consistent state in time.</p>

<p><strong>How Barman Backups Work</strong></p>

<p>Traditionally, PostgreSQL DBAs would write their own backup scripts and scheduled <code>cron</code> jobs to implement physical backups. Barman does this in a standardized way.</p>

<p><strong>Barman</strong> or <strong>Backup and Recovery Manager</strong> is a free, open-source PostgreSQL backup tool from <a href="http://www.pgbarman.org/">2ndQuadrant</a> - a professional Postgres solutions company. Barman was written in Python and offers a simple, intuitive method of physical backup and restoration for your PostgreSQL instance. Some benefits of using Barman are:</p>

<ul>
<li>It's totally free</li>
<li>It's a well-maintained application and has professional support available from the vendor</li>
<li>Frees up the DBA / Sysadmin from writing and testing complex scripts and <code>cron</code> jobs</li>
<li>Can back up multiple PostgreSQL instances into one central location</li>
<li>Can restore to the same PostgreSQL instance or a different instance</li>
<li>Offers compression mechanisms to minimize network traffic and disk space</li>
</ul>

<h3 id="goals">Goals</h3>

<p>In this tutorial we will create three IndiaReads Droplets, install PostgreSQL 9.4 on two of these machines, and install Barman on the third.</p>

<p>One of the PostgreSQL servers will be our main database server: this is where we will create our production database. The second PostgreSQL instance will be empty and treated as a standby machine where we can restore from the backup.</p>

<p>The Barman server will communicate with the main database server and perform physical backups and WAL archiving.</p>

<p>We will then emulate a "disaster" by dropping a table from our live database.</p>

<p>Finally, we will restore the backed up PostgreSQL instance from the Barman server to the standby server.</p>

<h3 id="prerequisites">Prerequisites</h3>

<p>To follow this tutorial, you will need to create three IndiaReads Droplets (or your own Linux servers), each with at least <strong>2 GB of RAM</strong> and 2 CPU cores. We won't go into the details of creating a Droplet; you can find more information <a href="https://indiareads/community/tutorials/how-to-create-your-first-digitalocean-droplet-virtual-server">here</a>.</p>

<p>All three servers should have the same OS (<strong>CentOS 7</strong> x64 bit).</p>

<p>We will name the machines as follows:</p>

<ul>
<li><strong>main-db-server</strong> (we will denote its IP address as <span class="highlight">main-db-server-ip</span>)</li>
<li><strong>standby-db-server</strong> (we will denote its IP address as <span class="highlight">standby-db-server-ip</span>)</li>
<li><strong>barman-backup-server</strong> (we will denote its IP address as <span class="highlight">barman-backup-server-ip</span>)</li>
</ul>

<p>The actual IP addresses of the machines can be found from the IndiaReads control panel.</p>

<p>You should also set up a sudo user on each server and use that for general access. Most of the commands will be executed as two different users (<strong>postgres</strong> and <strong>barman</strong>), but you will need a sudo user on each server as well so you can switch to those accounts. To understand how sudo privileges work, see this <a href="https://indiareads/community/tutorials/how-to-edit-the-sudoers-file-on-ubuntu-and-centos">IndiaReads tutorial about enabling sudo access</a>.</p>

<p><span class="note"><strong>Note:</strong> This tutorial will be use the default Barman installation directory as the backup location. In CentOS, this location is: <code>/var/lib/barman/</code>. 2ndQuadrant recommends it's best to keep the default path. In real-life use cases, depending on the size of your databases and the number of instances being backed up, you should check that there is enough space in the file system hosting this directory.<br /></span></p>

<p><span class="warning"><strong>Warning:</strong> <strong>You should not run any commands, queries, or configurations from this tutorial on a production server</strong>. This tutorial will involve changing configurations and restarting PostgreSQL instances. Doing so in a live environment without proper planning and authorization would mean an outage for your application.<br /></span></p>

<h2 id="step-1-—-installing-postgresql-database-servers">Step 1 — Installing PostgreSQL Database Servers</h2>

<p>We will first set up our database environment by installing PostgreSQL 9.4 on the <strong>main-db-server</strong> and the <strong>standby-db-server</strong>.</p>

<p>Please complete the PostgreSQL installation steps from <a href="https://indiareads/community/tutorials/how-to-set-up-a-two-node-lepp-stack-on-centos-7">this LEPP stack tutorial</a>. From this tutorial, you will need to:</p>

<ul>
<li>Follow the section <strong>Step One — Installing PostgreSQL</strong></li>
<li>Follow the section <strong>Step Two — Configuring PostgreSQL</strong></li>
</ul>

<p>In <strong>Step Two — Configuring PostgreSQL</strong>, instead of making changes to the <code>pg_hba.conf</code> file to allow access to the database for a web server, add this line so the Barman server can connect, using the <strong>barman-backup-server</strong> IP address, followed by <code>/32</code>:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">host    all     all     <span class="highlight">barman-backup-server-ip</span>/32        trust
</li></ul></code></pre>
<p>This configures PostgreSQL to accept any connection coming from the Barman server.</p>

<p>The rest of the instructions in that section can be followed as they are.</p>

<p><span class="note"><strong>Note:</strong> Installing PostgreSQL will create an operating system user called <strong>postgres</strong> on the database server. This account does not have a password; you'll switch to it from your sudo user.<br /></span></p>

<p>Make sure you have installed PostgreSQL on both the <strong>main-db-server</strong> and the <strong>standby-db-server</strong>, and that you have allowed access on both of them from the <strong>barman-backup-server</strong>.</p>

<p>Next we'll add some sample data to the main database server.</p>

<h2 id="step-2-—-creating-postgresql-database-and-tables">Step 2 — Creating PostgreSQL Database and Tables</h2>

<p>Once PostgreSQL is installed and configured on both the machines, we'll add some sample data to the <strong>main-db-server</strong> to simulate a production environment.</p>

<p>On the <strong>main-db-server</strong>, switch to the user <strong>postgres</strong>:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo su - postgres
</li></ul></code></pre>
<p>Start the <code>psql</code> utility to access the database server:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">psql
</li></ul></code></pre>
<p>From the <code>psql</code> prompt, run the following commands to create a database and switch to it:</p>
<pre class="code-pre custom_prefix"><code langs=""><ul class="prefixed"><li class="line" prefix="postgres=#">CREATE DATABASE mytestdb;
</li><li class="line" prefix="postgres=#">\connect mytestdb;
</li></ul></code></pre>
<p>An output message will tell you that you are now connected to database <code>mytestdb</code> as user <code>postgres</code>.</p>

<p>Next, add two tables in the database:</p>
<pre class="code-pre custom_prefix"><code langs=""><ul class="prefixed"><li class="line" prefix="mytestdb=#">CREATE TABLE mytesttable1 (id integer NULL);
</li><li class="line" prefix="mytestdb=#">CREATE TABLE mytesttable2 (id integer NULL);
</li></ul></code></pre>
<p>These are named <code>mytesttable1</code> and <code>mytesttable2</code>.</p>

<p>Quit the client tool by typing <code>\q</code> and pressing <code>ENTER</code>.</p>

<h2 id="step-3-—-installing-barman">Step 3 — Installing Barman</h2>

<p>Now we'll install Barman on the backup server, which will both control and store our backups.</p>

<p>Complete this step on the <strong>barman-backup-server</strong>. </p>

<p>To do this, you will first need to install the following repositories:</p>

<ul>
<li>Extra Packages for Enterprise Linux (EPEL) repository</li>
<li>PostgreSQL Global Development Group RPM repository</li>
</ul>

<p>Run the following command to install EPEL:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo yum -y install epel-release
</li></ul></code></pre>
<p>Run these commands to install the PostgreSQL repo:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo wget http://yum.postgresql.org/9.4/redhat/rhel-7Server-x86_64/pgdg-centos94-9.4-1.noarch.rpm
</li><li class="line" prefix="$">sudo rpm -ivh pgdg-centos94-9.4-1.noarch.rpm
</li></ul></code></pre>
<p>Finally, run this command to install Barman:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo yum -y install barman
</li></ul></code></pre>
<p><span class="note"><strong>Note:</strong> Installing Barman will create an operating system user called <strong>barman</strong>. This account does not have a password; you can switch to this user from your sudo user account.<br /></span></p>

<p>Barman is installed! Now, let's make sure the servers can connect to each other securely.</p>

<h2 id="step-4-—-configuring-ssh-connectivity-between-servers">Step 4 — Configuring SSH Connectivity Between Servers</h2>

<p>In this section, we'll establish SSH keys for a secure passwordless connection between the <strong>main-db-server</strong> and the <strong>barman-backup-server</strong>, and vice versa.</p>

<p>Likewise, we'll establish SSH keys between the <strong>standby-db-server</strong> and the <strong>barman-backup-server</strong>, and vice versa. </p>

<p>This is to ensure PostgreSQL (on both database servers) and Barman can "talk" to each other during backups and restores.</p>

<p>For this tutorial you will need to make sure:</p>

<ul>
<li>User <strong>postgres</strong> can connect remotely from the <strong>main-db-server</strong> to the <strong>barman-backup-server</strong></li>
<li>User <strong>postgres</strong> can connect remotely from the <strong>standby-db-server</strong> to the <strong>barman-backup-server</strong></li>
<li>User <strong>barman</strong> can connect remotely from the <strong>barman-backup-server</strong> to the <strong>main-db-server</strong></li>
<li>User <strong>barman</strong> can connect remotely from the <strong>barman-backup-server</strong> to the <strong>standby-db-server</strong></li>
</ul>

<p>We will not go into the details of how SSH works. There's a <a href="https://indiareads/community/tutorials/ssh-essentials-working-with-ssh-servers-clients-and-keys">very good article on IndiaReads</a> about SSH essentials which you can refer to.</p>

<p>All the commands you'll need are included here, though.</p>

<p>We'll show you how to do this once for setting up the connection for the user <strong>postgres</strong> to connect from the <strong>main-db-server</strong> to the <strong>barman-backup-server</strong>.</p>

<p>From the <strong>main-db-server</strong>, switch to user <strong>postgres</strong> if it's not already the current user:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo su - postgres
</li></ul></code></pre>
<p>Run the following command to generate an SSH key pair:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">ssh-keygen -t rsa
</li></ul></code></pre>
<p>Accept the default location and name for the key files by pressing <code>ENTER</code>.</p>

<p>Press <code>ENTER</code> twice to create the private key without any passphrase.</p>

<p>Once the keys are generated, there will be a <code>.ssh</code> directory created under the <strong>postgres</strong> user's home directory, with the keys in it.</p>

<p>You will now need to copy the SSH public key to the <code>authorized_keys</code> file under the <strong>barman</strong> user's <code>.ssh</code> directory on the <strong>barman-backup-server</strong>. </p>

<p><span class="note"><strong>Note:</strong> Unfortunately you can't use the <code>ssh-copy-id barman@<span class="highlight">barman-backup-server-ip</span></code> command here. That's because this command will ask for the <strong>barman</strong> user's password, which is not set by default. You will therefore need to copy the public key contents manually.<br /></span></p>

<p>Run the following command to output the <strong>postgres</strong> user's public key contents:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">cat ~/.ssh/id_rsa.pub
</li></ul></code></pre>
<p>Copy the contents of the output.</p>

<p>Switch to the console connected to the <strong>barman-backup-server</strong> server and switch to the user <strong>barman</strong>:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo su - barman
</li></ul></code></pre>
<p>Run the following commands to create a <code>.ssh</code> directory, set its permissions, copy the public key contents to the <code>authorized_keys</code> file, and finally make that file readable and writable:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">mkdir -p ~/.ssh
</li><li class="line" prefix="$">chmod 700 ~/.ssh
</li><li class="line" prefix="$">echo "<span class="highlight">public_key_string</span>" >> ~/.ssh/authorized_keys
</li><li class="line" prefix="$">chmod 600 ~/.ssh/authorized_keys
</li></ul></code></pre>
<p>Make sure you put the long public key string starting with <code>ssh-rsa</code> between the quotation marks, instead of <code><span class="highlight">public_key_string</span></code>.</p>

<p>You've copied the key to the remote server.</p>

<p>Now, to test the connection, switch back to the <strong>main-db-server</strong> and test the connectivity from there:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">ssh barman@<span class="highlight">barman-backup-server-ip</span>
</li></ul></code></pre>
<p>After the initial warning about the authenticity of the remote server not being known and you accepting the prompt, a connection should be established from the <strong>main-db-server</strong> server to the <strong>barman-backup-server</strong>. If successful, log out of the session by executing the <code>exit</code> command.</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">exit
</li></ul></code></pre>
<p><strong>You need to set up SSH key connections three more times.</strong> You can skip making the <code>.ssh</code> directory if it's already made (although this isn't necessary).</p>

<ul>
<li>Run the same commands again, this time from the <strong>standby-db-server</strong> to the <strong>barman-backup-server</strong></li>
<li>Run them a third time, this time originating from the <strong>barman</strong> user on the <strong>barman-backup-server</strong>, and going to the <strong>postgres</strong> user on the <strong>main-db-server</strong></li>
<li>Finally, run the commands to copy the key from the <strong>barman</strong> user on the <strong>barman-backup-server</strong> to the <strong>postgres</strong> user on the <strong>standby-db-server</strong></li>
</ul>

<p>Make sure you test the connection each way so that you can accept the initial warning about the new connection.</p>

<p>From <strong>standby-db-server</strong>:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">ssh barman@<span class="highlight">barman-backup-server-ip</span>
</li></ul></code></pre>
<p>From <strong>barman-backup-server</strong>:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">ssh postgres@<span class="highlight">main-db-server-ip</span>
</li></ul></code></pre>
<p>From <strong>barman-backup-server</strong>:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">ssh postgres@<span class="highlight">standby-db-server-ip</span>
</li></ul></code></pre>
<p><span class="note"><strong>Note:</strong> Ensuring SSH connectivity between all three servers is a requirement for backups to work.<br /></span></p>

<h2 id="step-5-—-configuring-barman-for-backups">Step 5 — Configuring Barman for Backups</h2>

<p>You will now configure Barman to back up your main PostgreSQL server.</p>

<p>The main configuration file for BARMAN is <code>/etc/barman.conf</code>. The file contains a section for global parameters, and separate sections for each server that you want to back up. The default file contains a section for a sample PostgreSQL server called <strong>main</strong>, which is commented out. You can use it as a guide to set up other servers you want to back up.</p>

<span class="note"><p>
A semicolon (<code><span class="highlight">;</span></code>) at the beginning of a line means that line is commented out. Just like with most Linux-based applications, a commented-out configuration parameter for Barman means the system will use the default value unless you uncomment it and enter a different value.</p>

<p>One such parameter is the <code>configuration_files_directory</code>, which has a default value of <code>/etc/barman.d</code>. What this means is, when enabled, Barman will use the <code>.conf</code> files in that directory for different Postgres servers' backup configurations. If you find the main file is getting too lengthy, feel free to make separate files for each server you want to back up.</p>

<p>For the sake of simplicity in this tutorial, we will put everything in the default configuration file.</p></span>

<p>Open <code>/etc/barman.conf</code> in a text editor as your <strong>sudo user</strong> (user <strong>barman</strong> has only read access to it):</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo vi /etc/barman.conf
</li></ul></code></pre>
<p>The global parameters are defined under the <code>[barman]</code> section. Under this section, make the following changes. The finished values are shown below the bullet points:</p>

<ul>
<li>Uncomment the line for <code>compression</code> and keep the default value of <code>gzip.</code> This means the PostgreSQL WAL files - when copied under the backup directory - will be saved in gzip compressed form</li>
<li>Uncomment the line for <code>reuse_backup</code> and keep the default value of <code>link</code>. When creating full backups of the PostgreSQL server, Barman will try to save space in the backup directory by creating file-level incremental backups. This uses rsync and hard links. Creating incremental full backups has the same benefit of any data de-duplication method: savings in time and disk space</li>
<li>Uncomment the line for <code>immediate_checkpoint</code> and set its value to <code>true</code>. This parameter setting ensures that when Barman starts a full backup, it will request PostgreSQL to perform a <code>CHECKPOINT</code>. Checkpointing ensures any modified data in PostgreSQL's memory cache are written to data files. From a backup perspective, this can add some value because BARMAN would be able to back up the latest data changes</li>
<li>Uncomment the line for <code>basebackup_retry_times</code> and set a value of <code>3</code>. When creating a full backup, Barman will try to connect to the PostgreSQL server three times if the copy operation fails for some reason</li>
<li>Uncomment the line for <code>basebackup_retry_sleep</code> and keep the default value of <code>30</code>.  There will be a 30-second delay between each retry</li>
<li>Uncomment the line for <code>last_backup_maximum_age</code> and set its value to <code>1 DAYS</code></li>
</ul>

<p>The new settings should look like this exactly:</p>
<div class="code-label " title="Excerpts from /etc/barman.conf">Excerpts from /etc/barman.conf</div><pre class="code-pre "><code langs="">[barman]
barman_home = /var/lib/barman

. . .

barman_user = barman
log_file = /var/log/barman/barman.log
compression = gzip
reuse_backup = link

. . .

immediate_checkpoint = true

. . .

basebackup_retry_times = 3
basebackup_retry_sleep = 30
last_backup_maximum_age = 1 DAYS
</code></pre>
<p>What we are doing here is this:</p>

<ul>
<li>Keeping the default backup location</li>
<li>Specifying that backup space should be saved. WAL logs will be compressed and base backups will use incremental data copying</li>
<li>Barman will retry three times if the full backup fails halfway through for some reason</li>
<li>The age of the last full backup for a PostgreSQL server should not be older than 1 day</li>
</ul>

<p>At the end of the file, add a new section. Its header should say <code>[main-db-server]</code> in square brackets. (If you want to back up more database servers with Barman, you can make a block like this for each server and use a unique header name for each.)</p>

<p>This section contains the connection information for the database server, and a few unique backup settings.</p>

<p>Add these parameters in the new block:</p>
<div class="code-label " title="Excerpt from /etc/barman.conf">Excerpt from /etc/barman.conf</div><pre class="code-pre "><code langs="">[main-db-server]
description = "Main DB Server"
ssh_command = ssh postgres@<span class="highlight">main-db-server-ip</span>
conninfo = host=<span class="highlight">main-db-server-ip</span> user=postgres
retention_policy_mode = auto
retention_policy = RECOVERY WINDOW OF <span class="highlight">7</span> days
wal_retention_policy = main
</code></pre>
<p>The <code>retention_policy</code> settings mean that Barman will overwrite older full backup files and WAL logs automatically, while keeping enough backups for a recovery window of 7 days. That means we can restore the entire database server to any point in time in the last seven days. <strong>For a production system, you should probably set this value higher so you have older backups on hand.</strong></p>

<p>You'll need to use the IP address of the <strong>main-db-server</strong> in the <code>ssh_command</code> and <code>conninfo</code> parameters. Otherwise, you can copy the above settings exactly.</p>

<p>The final version of the modified file should look like this, minus all the comments and unmodified settings:</p>
<div class="code-label " title="Excerpts from /etc/barman.conf">Excerpts from /etc/barman.conf</div><pre class="code-pre "><code langs="">[barman]
barman_home = /var/lib/barman

. . .

barman_user = barman
log_file = /var/log/barman/barman.log
compression = gzip
reuse_backup = link

. . .

immediate_checkpoint = true

. . .

basebackup_retry_times = 3
basebackup_retry_sleep = 30
last_backup_maximum_age = 1 DAYS

. . .

[main-db-server]
description = "Main DB Server"
ssh_command = ssh postgres@<span class="highlight">main-db-server-ip</span>
conninfo = host=<span class="highlight">main-db-server-ip</span> user=postgres
retention_policy_mode = auto
retention_policy = RECOVERY WINDOW OF 7 days
wal_retention_policy = main
</code></pre>
<p>Save and close the file.</p>

<p>Next, we'll make sure our <strong>main-db-server</strong> is configured to make backups.</p>

<h2 id="step-6-—-configuring-the-postgresql-conf-file">Step 6 — Configuring the postgresql.conf File</h2>

<p>There is one last configuration to be made on the <strong>main-db-server</strong>, to switch on backup (or archive) mode.</p>

<p>First, we need to locate the value of the incoming backup directory from the <strong>barman-backup-server</strong>. On the Barman server, switch to the user <strong>barman</strong>:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo su - barman
</li></ul></code></pre>
<p>Run this command to locate the incoming backup directory:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">barman show-server main-db-server | grep incoming_wals_directory
</li></ul></code></pre>
<p>This should output something like this:</p>
<pre class="code-pre "><code langs=""><div class="secondary-code-label " title="barman show-server command output">barman show-server command output</div>incoming_wals_directory: /var/lib/barman/main-db-server/incoming
</code></pre>
<p>Note down the value of <code>incoming_wals_directory</code>; in this example, it's <code>/var/lib/barman/main-db-server/incoming</code>.</p>

<p>Now switch to the <strong>main-db-server</strong> console.</p>

<p>Switch to the user <strong>postgres</strong> if it's not the current user already.</p>

<p>Open the <code>postgresql.conf</code> file in a text editor:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">vi $PGDATA/postgresql.conf
</li></ul></code></pre>
<p>Make the following changes to the file:</p>

<ul>
<li>Uncomment the <code>wal_level</code> parameter and set its value to <code>archive</code> instead of <code>minimal</code></li>
<li>Uncomment the <code>archive_mode</code> parameter and set its value to <code>on</code> instead of <code>off</code></li>
<li>Uncomment the <code>archive_command</code> parameter and set its value to <code>'rsync -a %p barman@<span class="highlight">barman-backup-server-ip</span>:<span class="highlight">/var/lib/barman/main-db-server/incoming</span>/%f'</code> instead of <code>''</code>. Use the IP address of the Barman server. If you got a different value for <code>incoming_wals_directory</code>, use that one instead</li>
</ul>
<div class="code-label " title="Excerpts from postgresql.conf">Excerpts from postgresql.conf</div><pre class="code-pre "><code langs="">wal_level = archive                     # minimal, archive, hot_standby, or logical

. . .

archive_mode = on               # allows archiving to be done

. . .

archive_command = 'rsync -a %p barman@<span class="highlight">barman-backup-server-ip</span>:<span class="highlight">/var/lib/barman/main-db-server/incoming</span>/%f'                # command to use to archive a logfile segment

</code></pre>
<p>Switch back to your <strong>sudo user</strong>.</p>

<p>Restart PostgreSQL:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo systemctl restart postgresql-9.4.service
</li></ul></code></pre>
<p><span class="note"><strong>Note:</strong> If you are configuring an existing production PostgreSQL instance, there's a good chance these three parameters will be set already. You will then have to add/modify only the <code>archive_command</code> parameter so PostgreSQL sends its WAL files to the backup server.<br /></span></p>

<h2 id="step-7-—-testing-barman">Step 7 — Testing Barman</h2>

<p>It's now time to check if Barman has all the configurations set correctly and can connect to the <strong>main-db-server</strong>.</p>

<p>On the <strong>barman-backup-server</strong>, switch to the user <strong>barman</strong> if it's not the current user. Run the following command to test the connection to your main database server:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">barman check <span class="highlight">main-db-server</span>
</li></ul></code></pre>
<p>Note that if you entered a different name between the square brackets for the server block in the <code>/etc/barman.conf</code> file in Step 5, you should use that name instead.</p>

<p>If everything is okay, the output should look like this:</p>
<pre class="code-pre "><code langs=""><div class="secondary-code-label " title="barman check command output">barman check command output</div>Server main-db-server:
        PostgreSQL: OK
        archive_mode: OK
        wal_level: OK
        archive_command: OK
        continuous archiving: OK
        directories: OK
        retention policy settings: OK
        backup maximum age: FAILED (interval provided: 1 day, latest backup age: No available backups)
        compression settings: OK
        minimum redundancy requirements: OK (have 0 backups, expected at least 0)
        ssh: OK (PostgreSQL server)
        not in recovery: OK
</code></pre>
<p>Don't worry about the backup maximum age <code>FAILED</code> state. This is happening because we have configured Barman so that the latest backup should not be older than 1 day. There is no backup made yet, so the check fails.</p>

<p>If any of the other parameters are in a <code>FAILED</code> state, you should investigate further and fix the issue before proceeding.</p>

<p>There can be multiple reasons for a check to fail: for example, Barman not being able to log into the Postgres instance, Postgres not being configured for WAL archiving, SSH not working between the servers, etc. Whatever the cause, it needs to be fixed before backups can happen. Run through the previous steps and make sure all the connections work.</p>

<p>To get a list of PostgreSQL servers configured with Barman, run this command:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">barman list-server
</li></ul></code></pre>
<p>Right now it should just show:</p>
<div class="code-label " title="Output">Output</div><pre class="code-pre "><code langs="">main-db-server - Main DB Server
</code></pre>
<h2 id="step-8-—-creating-the-first-backup">Step 8 — Creating the First Backup</h2>

<p>Now that you have Barman ready, let's create a backup manually.</p>

<p>Run the following command as the <strong>barman</strong> user on the <strong>barman-backup-server</strong> to make your first backup:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">barman backup <span class="highlight">main-db-server</span>
</li></ul></code></pre>
<p>Again, the <code><span class="highlight">main-db-server</span></code> value is what you entered as the head of the server block in the <code>/etc/barman.conf</code> file in Step 5.</p>

<p>This will initiate a full backup of the PostgreSQL data directory. Since our instance has only one small database with two tables, it should finish very quickly.</p>
<div class="code-label " title="Output">Output</div><pre class="code-pre "><code langs="">Starting backup for server <span class="highlight">main-db-server</span> in /var/lib/barman/main-db-server/base/20151111T051954
Backup start at xlog location: 0/2000028 (000000010000000000000002, 00000028)
Copying files.
Copy done.
Asking PostgreSQL server to finalize the backup.
Backup size: 26.9 MiB. Actual size on disk: 26.9 MiB (-0.00% deduplication ratio).
Backup end at xlog location: 0/20000B8 (000000010000000000000002, 000000B8)
Backup completed
Processing xlog segments for <span class="highlight">main-db-server</span>
        Older than first backup. Trashing file 000000010000000000000001 from server<span class="highlight"> main-db-server</span>
        000000010000000000000002
        000000010000000000000002.00000028.backup
</code></pre>
<h3 id="backup-file-location">Backup File Location</h3>

<p>So where does the backup get saved? To find the answer, list the contents of the <code>/var/lib/barman</code> directory:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">ls -l /var/lib/barman
</li></ul></code></pre>
<p>There will be one directory there: <code>main-db-server</code>. That's the server Barman is currently configured to back up, and its backups live there. (If you configure Barman to back up other servers, there will be one directory created per server.) Under the <code>main-db-server</code> directory, there will be three sub-directories:</p>

<ul>
<li><code>base</code>: This is where the base backup files are saved</li>
<li><code>incoming</code>: PostgreSQL sends its completed WAL files to this directory for archiving</li>
<li><code>wals</code>: Barman copies the contents of the <code>incoming</code> directory to the <code>wals</code> directory</li>
</ul>

<p>During a restoration, Barman will recover contents from the <code>base</code> directory into the target server's data directory. It will then use files from the <code>wals</code> directory to apply transaction changes and bring the target server to a consistent state.</p>

<h3 id="listing-backups">Listing Backups</h3>

<p>There is a specific Barman command to list all the backups for a server. That command is <code>barman list-backup</code>. Run the following command to see what it returns for our <code><span class="highlight">main-db-server</span></code>:</p>
<pre class="code-pre commaand"><code langs="">barman list-backup <span class="highlight">main-db-server</span>
</code></pre><pre class="code-pre "><code langs=""><div class="secondary-code-label " title="Output">Output</div>main-db-server 20151111T051954 - Wed Nov 11 05:19:46 2015 - Size: 26.9 MiB - WAL Size: 0 B
</code></pre>
<ul>
<li>The first part of the output is the name of the server. In this case, <code>main-db-server</code></li>
<li>The second part - a long alphanumeric value - is the backup ID for the backup. A backup ID is used to uniquely identify any backup Barman makes. In this case, it's <code><span class="highlight">20151111T051954</span></code>. <strong>You will need the backup ID for the next steps</strong></li>
<li>The third piece of information tells you when the backup was made</li>
<li>The fourth part is the size of the base backup (26.9 MB in this case)</li>
<li>The fifth and final part of the string gives the size of the the WAL archive backed up</li>
</ul>

<p>To see more details about the backup, execute this command using the name of the server, and the backup ID (<code><span class="highlight">20151111T051954</span></code> in our example) from the previous command:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">barman show-backup <span class="highlight">main-db-server</span> <span class="highlight">backup-id</span>
</li></ul></code></pre>
<p>A detailed set of information will be shown:</p>
<pre class="code-pre "><code langs=""><div class="secondary-code-label " title="Output">Output</div>Backup <span class="highlight">20151111T051954</span>:
  Server Name            : <span class="highlight">main-db-server</span>
  Status                 : DONE
  PostgreSQL Version     : 90405
  PGDATA directory       : /var/lib/pgsql/9.4/data

  Base backup information:
    Disk usage           : 26.9 MiB (26.9 MiB with WALs)
    Incremental size     : 26.9 MiB (-0.00%)
    Timeline             : 1
    Begin WAL            : 000000010000000000000002
    End WAL              : 000000010000000000000002
    WAL number           : 1
    WAL compression ratio: 99.84%
    Begin time           : 2015-11-11 05:19:44.438072-05:00
    End time             : 2015-11-11 05:19:46.839589-05:00
    Begin Offset         : 40
    End Offset           : 184
    Begin XLOG           : 0/2000028
    End XLOG             : 0/20000B8

  WAL information:
    No of files          : 0
    Disk usage           : 0 B
    Last available       : 000000010000000000000002

  Catalog information:
    Retention Policy     : VALID
    Previous Backup      : - (this is the oldest base backup)
    Next Backup          : - (this is the latest base backup)
</code></pre>
<p>To drill down more to see what files go into the backup, run this command:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">barman list-files <span class="highlight">main-db-server</span> <span class="highlight">backup-id</span>
</li></ul></code></pre>
<p>This will give a list of the base backup and WAL log files required to restore from that particular backup.</p>

<h2 id="step-9-—-scheduling-backups">Step 9 — Scheduling Backups</h2>

<p>Ideally your backups should happen automatically on a schedule. </p>

<p>In this step we'll automate our backups, and we'll tell Barman to perform maintenance on the backups so files older than the retention policy are deleted. To enable scheduling, run this command as the <strong>barman</strong> user on the <strong>barman-backup-server</strong> (switch to this user if necessary):</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">crontab -e
</li></ul></code></pre>
<p>This will open a <code>crontab</code> file for the user <strong>barman</strong>. Edit the file, add these lines, then save and exit:</p>
<div class="code-label " title="cron">cron</div><pre class="code-pre "><code langs="">30 23 * * * /usr/bin/barman backup <span class="highlight">main-db-server</span>
* * * * * /usr/bin/barman cron
</code></pre>
<p>The first command will run a full backup of the <strong>main-db-server</strong> every night at 11:30 PM. (If you used a different name for the server in the <code>/etc/barman.conf</code> file, use that name instead.)</p>

<p>The second command will run every minute and perform maintenance operations on both WAL files and base backup files. </p>

<h2 id="step-10-—-simulating-a-quot-disaster-quot">Step 10 — Simulating a "Disaster"</h2>

<p>You will now see how you can restore from the backup you just created. To test the restoration, let's first simulate a "disaster" scenario where you have lost some data. </p>

<p><strong>We're dropping a table here. Don't do this on a production database!</strong></p>

<p>Go back to the <strong>main-db-server</strong> console and switch to the user <strong>postgres</strong> if it's not already the current user.</p>

<p>Start the <code>psql</code> utility:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">psql
</li></ul></code></pre>
<p>From the <code>psql</code> prompt, execute the following command to switch the database context to <code>mytestdb</code>:</p>
<pre class="code-pre custom_prefix"><code langs=""><ul class="prefixed"><li class="line" prefix="postgres=#">\connect mytestdb;
</li></ul></code></pre>
<p>Next, list the tables in the database:</p>
<pre class="code-pre custom_prefix"><code langs=""><ul class="prefixed"><li class="line" prefix="mytestdb=#">\dt
</li></ul></code></pre>
<p>The output will show the tables you created at the beginning of this tutorial:</p>
<pre class="code-pre "><code langs=""><div class="secondary-code-label " title="Output">Output</div>            List of relations
 Schema |     Name     | Type  |  Owner
--------+--------------+-------+----------
 public | mytesttable1 | table | postgres
 public | mytesttable2 | table | postgres
</code></pre>
<p>Now, run this command to drop one of the tables:</p>
<pre class="code-pre custom_prefix"><code langs=""><ul class="prefixed"><li class="line" prefix="mytestdb=#">drop table mytesttable2;
</li></ul></code></pre>
<p>If you now execute the <code>\dt</code> command again:</p>
<pre class="code-pre custom_prefix"><code langs=""><ul class="prefixed"><li class="line" prefix="mytestdb=#">\dt
</li></ul></code></pre>
<p>You will see that only <code>mytesttable1</code> remains.</p>

<p>This is the type of data loss situation where you would want to restore from a backup. In this case, you will restore the backup to a separate server: the <strong>standby-db-server</strong>.</p>

<h2 id="step-11-—-restoring-or-migrating-to-a-remote-server">Step 11 — Restoring or Migrating to a Remote Server</h2>

<p>You can follow this section to restore a backup, or to migrate your latest PostgreSQL backup to a new server.</p>

<p>Go to the <strong>standby-db-server</strong>.</p>

<p>First, stop the PostgreSQL service as the sudo user. (The restart will choke if you try to run the restoration while the service is running.)</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo systemctl stop postgresql-9.4.service
</li></ul></code></pre>
<p>Once the service stops, go to the <strong>barman-backup-server</strong>. Switch to the user <strong>barman</strong> if it's not already the current user.</p>

<p>Let's locate the details for the latest backup:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">barman show-backup <span class="highlight">main-db-server</span> latest
</li></ul></code></pre><div class="code-label " title="Output">Output</div><pre class="code-pre "><code langs="">Backup <span class="highlight">20160114T173552</span>:
  Server Name            : <span class="highlight">main-db-server</span>
  Status                 : DONE
  PostgreSQL Version     : 90405
  PGDATA directory       : /var/lib/pgsql/9.4/data

  Base backup information:

. . .

    Begin time           : <span class="highlight">2016-01-14 17:35:53.164222-05:00</span>
    End time             : 2016-01-14 17:35:55.054673-05:00
</code></pre>
<p>From the output, note down the backup ID printed on the first line (<code><span class="highlight">20160114T173552</span></code> above). If the <code>latest</code> backup has the data you want, you can use <code>latest</code> as the backup ID.</p>

<p>Also check when the backup was made, from the <code>Begin time</code> field (<code><span class="highlight">2016-01-14 17:35:53.164222-05:00</span></code> above).</p>

<p>Next, run this command to restore the specified backup from the <strong>barman-backup-server</strong> to the <strong>standby-db-server</strong>:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">barman recover --target-time "<span class="highlight">Begin time</span>"  --remote-ssh-command "ssh postgres@<span class="highlight">standby-db-server-ip</span>"   <span class="highlight">main-db-server</span>   <span class="highlight">backup-id</span>   /var/lib/pgsql/9.4/data
</li></ul></code></pre>
<p>There are quite a few options, arguments, and variables here, so let's explain them.</p>

<ul>
<li><code>--target-time "<span class="highlight">Begin time</span>"</code>: Use the begin time from the <code>show-backup</code> command</li>
<li><code>--remote-ssh-command "ssh postgres@<span class="highlight">standby-db-server-ip</span>"</code>: Use the IP address of the <strong>standby-db-server</strong></li>
<li><code><span class="highlight">main-db-server</span></code>: Use the name of the database server from your <code>/etc/barman.conf</code> file</li>
<li><code><span class="highlight">backup-id</span></code>: Use the backup ID from the <code>show-backup</code> command, or use <code>latest</code> if that's the one you want</li>
<li><code>/var/lib/pgsql/9.4/data</code>: The path where you want the backup to be restored. This path will become the new data directory for Postgres on the standby server. Here, we have chosen the default data directory for Postgres in CentOS. For real-life use cases, choose the appropriate path</li>
</ul>

<p>For a successful restore operation, you should receive output like this:</p>
<div class="code-label " title="Output from Barman Recovery">Output from Barman Recovery</div><pre class="code-pre "><code langs="">Starting remote restore for server  <span class="highlight">main-db-server</span> using backup <span class="highlight">backup-id</span>
Destination directory: /var/lib/pgsql/9.4/data
Doing PITR. Recovery target time: <span class="highlight">Begin time</span>
Copying the base backup.
Copying required WAL segments.
Generating recovery.conf
Identify dangerous settings in destination directory.

IMPORTANT
These settings have been modified to prevent data losses

postgresql.conf line 207: archive_command = false

Your PostgreSQL server has been successfully prepared for recovery!
</code></pre>
<p>Now switch to the <strong>standby-db-server</strong> console again. As the <strong>sudo user</strong>, start the PostgreSQL service:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo systemctl start postgresql-9.4.service
</li></ul></code></pre>
<p>That should be it!</p>

<p>Let's verify that our database is up. Switch to user <strong>postgres</strong> and start the <code>psql</code> utility:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo su - postgres
</li><li class="line" prefix="$">psql
</li></ul></code></pre>
<p>Switch the database context to <code>mytestdb</code> and list the tables in it:</p>
<pre class="code-pre custom_prefix"><code langs=""><ul class="prefixed"><li class="line" prefix="postgres=#">\connect mytestdb;
</li><li class="line" prefix="postgres=#">\dt
</li></ul></code></pre><div class="code-label " title="Output">Output</div><pre class="code-pre "><code langs="">            List of relations
 Schema |     Name     | Type  |  Owner   
--------+--------------+-------+----------
 public | mytesttable1 | table | postgres
 public | mytesttable2 | table | postgres
(2 rows)
</code></pre>
<p>The list should show two tables in the database. In other words, you have just recovered the dropped table.</p>

<p>Depending on your larger recovery strategy, you may now want to fail over to the <strong>standby-db-server</strong>, or you may want to check that the restored database is working, and then run through this section again to restore to the <strong>main-db-server</strong>.</p>

<p>To restore to any other server, just make sure you've installed PostgreSQL and made the appropriate connections to the Barman server, and then follow this section using the IP address of your target recovery server.</p>

<h2 id="conclusion">Conclusion</h2>

<p>In this tutorial we have seen how to install and configure Barman to back up a PostgreSQL server. We have also learned how to restore or migrate from these backups.</p>

<p>With careful consideration, Barman can become the central repository for all your PostgresSQL databases. It offers a robust backup mechanism and a simple command set. However, creating backups is only half the story. You should always validate your backups by restoring them to a different location. This exercise should be done periodically. </p>

<p>Some questions for fitting Barman into your backup strategy:</p>

<ul>
<li>How many PostgreSQL instances will be backed up?</li>
<li>Is there enough disk space on the Barman server for hosting all the backups for a specified retention period? How can you monitor the server for space usage?</li>
<li>Should all the backups for different servers start at the same time or can they be staggered throughout off-peak period? Starting backups of all servers at the same time can put unnecessary strain on the Barman server and the network</li>
<li>Is the network speed between the Barman server and Postgres servers reliable?</li>
</ul>

<p>Another point to be mindful of is that Barman cannot backup and restore individual databases. It works on the file system level and uses an all-or-nothing approach. During a backup, the whole instance with all its data files are backed up; when restoring, all those files are restored. Similarly, you can't  do schema-only or data-only backups with Barman. </p>

<p>We therefore recommend you design your backup strategy so it makes use of both logical backups with <code>pg_dump</code> or <code>pg_dumpall</code> and physical backups with Barman. That way, if you need to restore individual databases quickly, you can use <code>pg_dump</code> backups. For point-in-time recovery, use Barman backups.</p>

    