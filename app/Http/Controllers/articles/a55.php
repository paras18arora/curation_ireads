<link rel='stylesheet' href='digital_ocean.css'><br> <br> 
      <h3 id="introduction">Introduction</h3>

<p>After getting your application server up and running, an important next step is to set up a backup system. A backup system will allow you to create periodic backup copies of your data, and restore data from those backups. As data can be lost due to user error or the eventual hardware failure that any computer system is prone to, you will want set up backups as a safety net.</p>

<p>This tutorial will show you how to create proper backups of a PHP application, running a LAMP stack on a single Ubuntu 14.04 server, by using a separate backups server that is running Bacula. One of the benefits of using a backup system like Bacula is that it gives you full control of what should be backed up and restored, at the individual file level, and the schedule of when the backups should be created. Having file-level granularity when creating backups allows us to limit our backup selections to only the files that are needed, which will save disk space compared to backing up the entire filesystem.</p>

<p><img src="https://assets.digitalocean.com/articles/architecture/production/backup_system.png" alt="Backup Diagram" /></p>

<p>If this seems excessive to you, you may want to consider <a href="https://indiareads/community/tutorials/understanding-digitalocean-droplet-backups">IndiaReads Droplet Backups</a> (snapshot backups of your entire Droplet), which must be enabled when you create your Droplet. These backups are easy to set up and may be sufficient for your needs if you only require weekly backups. If you opt for IndiaReads Backups, be sure to set up hot backups of your database by following the <strong>Create Hot Backups of Your Database</strong> section—this is necessary to ensure that your database backups will be consistent (usable).</p>

<h2 id="prerequisites">Prerequisites</h2>

<p>This tutorial assumes that you are running a PHP application, such as WordPress, that is running on a LAMP (Linux, Apache, MySQL/MariaDB, and PHP) stack on a single Ubuntu 14.04 server, with private networking enabled. We will refer to this as the <strong>LAMP</strong> server. For our example, we will be creating backups of a WordPress server that was created by following these tutorials:</p>

<ul>
<li><a href="https://indiareads/community/tutorials/how-to-install-linux-apache-mysql-php-lamp-stack-on-ubuntu-14-04">How To Install Linux, Apache, MySQL, PHP (LAMP) stack</a></li>
<li><a href="https://indiareads/community/tutorials/how-to-install-wordpress-on-ubuntu-14-04">How To Install Wordpress</a></li>
</ul>

<p>If you are running a different PHP application, or using Nginx instead of Apache, this tutorial will still work fine assuming that you make any necessary adjustments to your backup selection.</p>

<p>Of course, you will need sudo access to a server that the Bacula server software will be installed on, which we'll refer to as the <strong>backups</strong> server. Ideally, it will be in the same data center as your LAMP server, and have private networking enabled. The backups that are created will live on this server, so it will need enough disk space to store multiple copies of your backup selection.</p>

<h2 id="backup-selection">Backup Selection</h2>

<p>As mentioned in the introduction, our backup selection—the files that will be copied every time a backup is created—will consist only of the files that are necessary to restore your application to a previous state. In short, this means we will backup the following data:</p>

<ul>
<li><strong>PHP Application Files:</strong> This will be the DocumentRoot of your web server. On Ubuntu, this will be <code>/var/www/html</code> by default</li>
<li><strong>MySQL Database:</strong> While the MySQL data files are typically stored in <code>/var/lib/mysql</code>, we must create a hot backup of the database in another location. The hot backups will be part of our backup selection</li>
</ul>

<p>As a matter of convenience, we will also include the Apache and MySQL configuration files in our backup selection. If you have any other important files, such as SSL key and certificate files, be sure to include those too.</p>

<p>The rest of the files on the server can be replaced by following the software installation steps of the initial setup. In the case of a server failure, we could create a replacement LAMP server by following the prerequisite tutorials then restoring the backups, and restarting the appropriate services.</p>

<p>If you are not sure why we are including the aforementioned files in the backup selection, check out the <a href="https://indiareads/community/tutorials/building-for-production-web-applications-recovery-planning">Recovery Planning</a> segment of the multi-part <strong>Building for Production: Web Applications</strong> tutorial series. It describes how a recovery plan can be developed for a web application, using a multi-server setup as its example.</p>

<p>Let's set up the hot backups of our database.</p>

<h2 id="create-hot-backups-of-database">Create Hot Backups of Database</h2>

<p>To ensure that we produce consistent (i.e. usable) backups of our active database, special care must be taken. A simple and effective way to create hot backups with MySQL is to use Percona XtraBackup.</p>

<h3 id="install-percona-xtrabackup">Install Percona XtraBackup</h3>

<p>On your <strong>LAMP</strong> server, install and configure Percona XtraBackup by following this tutorial: <a href="https://indiareads/community/tutorials/how-to-create-hot-backups-of-mysql-databases-with-percona-xtrabackup-on-ubuntu-14-04">How To Create Hot Backups of MySQL Databases with Percona XtraBackup on Ubuntu 14.04</a>. Stop when you reach the <strong>Perform Full Hot Backup</strong> section.</p>

<h3 id="create-xtrabackup-script">Create XtraBackup Script</h3>

<p>Percona XtraBackup is ready to create hot backups of your MySQL database, which will ultimately be backed up by Bacula (or IndiaReads Backups), but the hot backups must be scheduled somehow. We will set up the simplest solution: a bash script and a cron job.</p>

<p>Create a bash script called <code>run_extra_backup.sh</code> in <code>/usr/local/bin</code>:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo vi /usr/local/bin/run_xtrabackup.sh
</li></ul></code></pre>
<p>Add the following script. Be sure to substitute the user and password with whatever you set up when you installed XtraBackup:</p>
<div class="code-label " title="/usr/local/bin/run_xtrabackup.sh">/usr/local/bin/run_xtrabackup.sh</div><pre class="code-pre "><code langs="">#!/bin/bash

# pre xtrabackup
chown -R mysql: /var/lib/mysql
find /var/lib/mysql -type d -exec chmod 770 "{}" \;

# delete existing full backup
rm -r /data/backups/full

# xtrabackup create backup
innobackupex --user=<span class="highlight">bkpuser</span>  --password=<span class="highlight">bkppassword</span> --no-timestamp /data/backups/full

# xtrabackup prepare backup
innobackupex --apply-log /data/backups/full
</code></pre>
<p>Save and exit. Running this script (with superuser privileges) will delete the existing XtraBackup backup at <code>/data/backups/full</code> and create a new full backup. In short, this script will maintain a single copy of the hot backup of the database. More details about creating backups with XtraBackup can be found in the <a href="https://indiareads/community/tutorials/how-to-create-hot-backups-of-mysql-databases-with-percona-xtrabackup-on-ubuntu-14-04#perform-full-hot-backup">Perform Full Hot Backup</a> section of the of the XtraBackup tutorial.</p>

<p>Make the script executable:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo chmod +x /usr/local/bin/run_xtrabackup.sh
</li></ul></code></pre>
<p>In order to properly backup our database, we must run (and complete) the XtraBackup script before Bacula tries to backup the database.  A good solution is to configure your Bacula backup job to run the script as a "pre-backup script", but we will opt to use a <a href="https://indiareads/community/tutorials/how-to-schedule-routine-tasks-with-cron-and-anacron-on-a-vps">cron job</a> to keep it simple.</p>

<p>Create a cron configuration file (files in <code>/etc/cron.d</code> get added to root's crontab):</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo vi /etc/cron.d/xtrabackup
</li></ul></code></pre>
<p>Add the following cron job:</p>
<div class="code-label " title="/etc/cron.d/xtrabackup">/etc/cron.d/xtrabackup</div><pre class="code-pre "><code langs=""><span class="highlight">30 22</span>    * * *   root    /usr/local/bin/run_xtrabackup.sh
</code></pre>
<p>This schedules the script to run as root every day at 10:30pm (22nd hour, 30th minute). We chose this time because Bacula's default backup job is scheduled to run at 11:05pm daily—we will discuss adjusting this later. This allows 35 minutes for the XtraBackup script to complete.</p>

<p>Now that the database hot backups are set up, let's install Bacula on our backups server.</p>

<h2 id="install-bacula-on-backups-server">Install Bacula on Backups Server</h2>

<p>On your <strong>backups</strong> server, set up Bacula  server by following this tutorial: <a href="https://indiareads/community/tutorials/how-to-install-bacula-server-on-ubuntu-14-04">How To Install Bacula Server on Ubuntu 14.04</a>.</p>

<p>Then follow the <strong>Organize Bacula Director Configuration (Server)</strong> section of this tutorial: <a href="https://indiareads/community/tutorials/how-to-back-up-an-ubuntu-14-04-server-with-bacula#organize-bacula-director-configuration-(server)">How To Back Up an Ubuntu 14.04 Server with Bacula</a>. You will need the Director Name when setting up the Bacula clients (on the servers you want to back up). Stop when you reach the <strong>Install and Configure Bacula Client</strong> section.</p>

<p>Note that we will be using the RemoteFile pool for all of the backups jobs that we will be setting up. With that said, you may want to change some of the settings before proceeding.</p>

<h2 id="install-bacula-client-on-lamp-server">Install Bacula Client on LAMP Server</h2>

<p>On your <strong>LAMP</strong> server, install the Bacula client  by following the <strong>Install and Configure Bacula Client</strong> section of this tutorial: <a href="https://indiareads/community/tutorials/how-to-back-up-an-ubuntu-14-04-server-with-bacula#install-and-configure-bacula-client">How To Back Up an Ubuntu 14.04 Server with Bacula</a>. Stop when you reach the <strong>Add FileSets (Server)</strong> section.</p>

<p>Note that you will need the <strong>FileDaemon Name</strong> (usually the hostname appended by "-fd") and the <strong>Director Password</strong> (the password that the Bacula server will use to connect to the Bacula client) from the <code>bacula-fd.conf</code> file on the LAMP server.</p>

<h2 id="add-backup-client-to-backups-server">Add Backup Client to Backups Server</h2>

<p>On your <strong>backups</strong> server, the Bacula server, add a <strong>Client resource</strong> for the LAMP server to the <code>/etc/bacula/conf.d/clients.conf</code> file.</p>

<p>Open the <code>clients.conf</code> file:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo vi /etc/bacula/conf.d/clients.conf
</li></ul></code></pre>
<p>The Client resource definition for the LAMP server should look something like the following code block. Note that the value of <strong>Name</strong> should match the the Name of the <strong>FileDaemon</strong> resource and the <strong>Password</strong> should match the Password of the <strong>Director</strong> resource, on the LAMP server—these values can be found in <code>/etc/bacula/bacula-fd.conf</code> on the LAMP server:</p>
<div class="code-label " title="clients.conf — Example Client resource definition">clients.conf — Example Client resource definition</div><pre class="code-pre "><code langs="">Client {
  Name = <span class="highlight">lamp</span>-fd
  Address = <span class="highlight">lamp_private_IP_or_hostname</span>
  FDPort = 9102
  Catalog = MyCatalog
  Password = "<span class="highlight">PDL47XPnjI0QzRpZVJKCDJ_xqlMOp4k46</span>"          # password for Remote FileDaemon
  File Retention = 30 days            # 30 days
  Job Retention = 6 months            # six months
  AutoPrune = yes                     # Prune expired Jobs/Files
}
</code></pre>
<p>Save and exit. This configures the Bacula Director, on the <strong>backups</strong> server, to be able to connect to the Bacula client on each server..</p>

<p>More details about this section can be found in the <strong>Install and Configure Bacula Client</strong> in the <a href="https://indiareads/community/tutorials/how-to-back-up-an-ubuntu-14-04-server-with-bacula#install-and-configure-bacula-client">How To Back Up an Ubuntu Server with Bacula tutorial</a>.</p>

<p>Now let's configure the Bacula backup FileSets.</p>

<h2 id="configure-bacula-filesets">Configure Bacula FileSets</h2>

<p>Bacula will create backups of files that are specified in the FileSets that are associated with the backup Jobs that will be executed. This section will cover creating FileSets that include the files that we determined to be part of our <strong>backup selection</strong>, earlier. More details about adding FileSets to Bacula can be found in the <a href="https://indiareads/community/tutorials/how-to-back-up-an-ubuntu-14-04-server-with-bacula#add-filesets-(server)">Add FileSets (Server)</a> section of the Bacula tutorial.</p>

<p>On your <strong>backups</strong> server, open the <code>filesets.conf</code> file:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo vi /etc/bacula/conf.d/filesets.conf
</li></ul></code></pre>
<p>The required backups for our LAMP server, according to our backup selection, include:</p>

<ul>
<li><strong>PHP Application Files:</strong> <code>/var/www/html</code></li>
<li><strong>MySQL database:</strong> <code>/data/backups/full</code> — full hot backup is created daily at 10:30pm by our XtraBackup script</li>
</ul>

<p>We will also include the following files, for convenience:</p>

<ul>
<li><strong>MySQL configuration:</strong> <code>/etc/mysql</code></li>
<li><strong>Apache configuration:</strong> <code>/etc/apache2</code></li>
<li><strong>XtraBackup script:</strong> <code>/usr/local/bin/run_xtrabackup.sh</code></li>
<li><strong>XtraBackup cron file:</strong> <code>/etc/cron.d/xtrabackup</code></li>
</ul>

<p>With our backup selection in mind, we will add the following FileSet to our Bacula configuration:</p>
<div class="code-label " title="filesets.conf — MySQL Database">filesets.conf — MySQL Database</div><pre class="code-pre "><code langs="">FileSet {
  Name = "LAMP Files"
  Include {
    Options {
      signature = MD5
      compression = GZIP
    }
    <span class="highlight">File = /var/www/html</span>
    <span class="highlight">File = /data/backups</span>
    <span class="highlight">File = /etc/mysql</span>
    <span class="highlight">File = /etc/apache2</span>    
    <span class="highlight">File = /usr/local/bin/run_xtrabackup.sh</span>
    <span class="highlight">File = /etc/cron.d/xtrabackup</span>
  }
  Exclude {
    <span class="highlight">File = /data/backups/exclude</span>
  }
}
</code></pre>
<p>Save and exit. Note that all of the highlighted <strong>File</strong> directives are in the <strong>Include</strong> block. Those are the all of the files that we want to back up. If you want to exclude any files from the backup job, ones that exist within included directories, add them to the <strong>Exclude</strong> block.</p>

<p>Now our FileSet is configured. Let's move on to the creating the Bacula backup job that will use this FileSet.</p>

<h2 id="create-bacula-backup-job">Create Bacula Backup Job</h2>

<p>We will create Bacula backup job that will run and create backups of our LAMP server.</p>

<p>Create a <code>jobs.conf</code> file in <code>/etc/bacula/conf.d</code>:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo vi /etc/bacula/conf.d/jobs.conf
</li></ul></code></pre>
<h3 id="lamp-server-backup-job">LAMP Server Backup Job</h3>

<p>For our LAMP server backup job, we will create a new job named "Backup LAMP". The important thing here is that we specify the correct <strong>Client</strong> (lamp-fd) and <strong>FileSet</strong> (LAMP Files):</p>
<div class="code-label " title="jobs.conf — Backup db1">jobs.conf — Backup db1</div><pre class="code-pre "><code langs="">Job {
  Name = "Backup LAMP"
  JobDefs = "DefaultJob"
  Client = <span class="highlight">lamp-fd</span>
  Pool = RemoteFile
  FileSet="<span class="highlight">LAMP Files</span>"
}
</code></pre>
<p>Save and exit.</p>

<p>Now our backup job is configured. The last step is to restart the Bacula Director.</p>

<h2 id="restart-bacula-director">Restart Bacula Director</h2>

<p>On the <strong>backups</strong> server, restart the Bacula Director to put all of our changes into effect:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo service bacula-director restart
</li></ul></code></pre>
<p>At this point, you will want to test your client connection and backup job, both of which are covered in the <a href="https://indiareads/community/tutorials/how-to-back-up-an-ubuntu-14-04-server-with-bacula#test-client-connection">How To Back Up a Server with Bacula tutorial</a>. That tutorial also covers how to restore Bacula backups. Note that restoring the MySQL database will require you to follow the <a href="https://indiareads/community/tutorials/how-to-create-hot-backups-of-mysql-databases-with-percona-xtrabackup-on-ubuntu-14-04#perform-backup-restoration">Perform Backup Restoration</a> step in the Percona XtraBackup Tutorial.</p>

<h2 id="review-backups-schedule">Review Backups Schedule</h2>

<p>The Bacula backups schedule can be adjusted by modifying the Bacula Director configuration (<code>/etc/bacula/bacula-dir.conf</code>). The backup job that we created uses the "DefaultJob" JobDef, which uses the "WeeklyCycle" schedule, which is defined as:</p>

<ul>
<li>Full backup on the first Sunday of a month at 11:05pm</li>
<li>Differential backups on all other Sundays at 11:05pm</li>
<li>Incremental backups on other days, Monday through Saturday, at at 11:05pm</li>
</ul>

<p>You can verify this by using the Bacula console to check the status of the Director. It should output all of your scheduled jobs:</p>
<pre class="code-pre "><code langs=""><div class="secondary-code-label " title="Director Status — Scheduled Jobs">Director Status — Scheduled Jobs</div>Scheduled Jobs:
Level          Type     Pri  Scheduled          Name               Volume
===================================================================================
Incremental    Backup    10  20-May-15 23:05    BackupLocalFiles   MyVolume
Incremental    Backup    10  20-May-15 23:05    Backup lamp         Remote-0002
</code></pre>
<p>Feel free to add or adjust the schedule of any of your backup jobs. If you want your backups to be a bit more flexible, it would be prudent to separate the database backups from everything else. This way, you could modify the schedule of the application files backup job to occur at the same time that the Percona XtraBackup script is executed (10:30pm), and backup the hot backup of the database (produced by XtraBackup) when it is finished being prepared. This will reduce the chances of the application and database backups from being inconsistent with each other.</p>

<h2 id="set-up-remote-backups-optional">Set Up Remote Backups (Optional)</h2>

<p>If you want to, you can create a remote server that will store copies of your Bacula backups. This remote server should be in a geographically separate region so you will have a copy of your critical backups even if there is a disaster in your production data center. For example, if your LAMP and backups servers are in New York, you could use IndiaReads's San Francisco (SFO1) region for your <strong>remotebackups</strong> server.</p>

<p>We will explain a simple method to send our backups from our <strong>backups</strong> server to our <strong>remotebackups</strong> server using public SSH keys, rsync, and cron.</p>

<p>On the <strong>remotebackups</strong> server, <a href="https://indiareads/community/tutorials/how-to-add-and-delete-users-on-an-ubuntu-14-04-vps">create a user</a> that will be used for the rsync login.</p>

<p>Next, on the <strong>backups</strong> server, generate a password-less SSH key pair as root. Install the public key on the <strong>remotebackups</strong> user that you just created. This is covered in our <a href="https://indiareads/community/tutorials/how-to-set-up-ssh-keys--2">How To Set Up SSH Keys</a> tutorial.</p>

<p>On the <strong>backups</strong> server, write up an rsync command that copies the Bacula backup data (<code>/bacula/backup</code>) to somewhere on the <strong>remotebackups</strong> server. Rsync usage is covered in our <a href="https://indiareads/community/tutorials/how-to-use-rsync-to-sync-local-and-remote-directories-on-a-vps">How To Use Rsync tutorial</a>. The command will probably look something like this: </p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">rsync -az /bacula/backup <span class="highlight">remoteuser</span>@<span class="highlight">remotebackups_public_hostname_or_IP</span>:/<span class="highlight">path/to/remote/backup</span>
</li></ul></code></pre>
<p>Add the command to a script, such as <code>/usr/local/bin/rsync_backups.sh</code> and make it executable.</p>

<p>Lastly, you will want to set up a cron job that runs the <code>rsync_backups.sh</code> script as root, after the Bacula backups jobs usually complete. This is covered in our <a href="https://indiareads/community/tutorials/how-to-schedule-routine-tasks-with-cron-and-anacron-on-a-vps">How To Schedule Routine Tasks With Cron tutorial</a>.</p>

<p>After you set all of this up, verify that there is a copy of your backups on the <strong>remotebackups</strong> server the next day.</p>

<h2 id="review-backup-disk-requirements">Review Backup Disk Requirements</h2>

<p>We didn't talk about the disk requirements for your backups. You will definitely want to review how much disk space your backups are using, and revise your setup and backups schedule based on your needs and resources.</p>

<p>In our example, unless your PHP application has a fairly high volume of content and media, the backups will probably consume a relatively low amount of disk space. This is because our backup selection is very conservative, and the default backup job creates <strong>incremental</strong> backups when possible.</p>

<h2 id="conclusion">Conclusion</h2>

<p>You should now have daily backups, and (if you set it up) a remote copy of those backups, of your LAMP server. Be sure to verify that you are able to restore the backed up files by quickly running through the restoration process.</p>

    