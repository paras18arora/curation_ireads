<link rel='stylesheet' href='digital_ocean.css'><br><img src=https://community-cdn-digitalocean-com.global.ssl.fastly.net/assets/tutorials/images/large/WebApplication.backups-twitter.png?1436558031/> <br> 
      <h3 id="introduction">Introduction</h3>

<p>After coming up with a recovery plan for the various components of your application, you should set up the backup system that is required to support it. This tutorial will focus on using Bacula as a backups solution. The benefits of using a full-fledged backup system, such as Bacula, is that it gives you full control over what you back up and restore at the individual file level, and you can schedule backups and restores according to what is best for you.</p>

<p><img src="https://assets.digitalocean.com/articles/architecture/production/backup_system.png" alt="Backup Diagram" /></p>

<p>Solutions such as <a href="https://indiareads/community/tutorials/understanding-digitalocean-droplet-backups">IndiaReads Droplet Backups</a> (snapshot backups of your entire Droplet) are easy to set up and may be sufficient for your needs, if you only require weekly backups. If you opt for IndiaReads Backups, be sure to set up hot backups of your database by following the <strong>Create Hot Backups of Your Database</strong> section.</p>

<p>In this part of the tutorial, we will set up a Bacula to maintain daily backups of the <strong>required backups</strong> of the servers that comprise your application setup (db1, app1, app2, and lb1), defined previously in our recovery plan—essentially, this is a tutorial that shows you how to use Bacula to create backups of a LAMP stack. We will also use Percona XtraBackup to create hot backups of your MySQL database. Lastly, we will use rsync to create a copy of your backups, on a server in a remote data center. This will add two servers to your setup: <strong>backups</strong> and <strong>remotebackups</strong> (located in a separate data center).</p>

<p>Let's get started.</p>

<h2 id="install-bacula-on-backups-server">Install Bacula on Backups Server</h2>

<p>Set up Bacula on your <strong>backups</strong> server by following this tutorial: <a href="https://indiareads/community/tutorials/how-to-install-bacula-server-on-ubuntu-14-04">How To Install Bacula Server on Ubuntu 14.04</a>.</p>

<p>Then follow the <strong>Organize Bacula Director Configuration (Server)</strong> section of this tutorial: <a href="https://indiareads/community/tutorials/how-to-back-up-an-ubuntu-14-04-server-with-bacula#organize-bacula-director-configuration-(server)">How To Back Up an Ubuntu 14.04 Server with Bacula</a>. You will need the Director Name when setting up the Bacula clients (on the servers you want to back up). Stop when you reach the <strong>Install and Configure Bacula Client</strong> section.</p>

<p>Note that we will be using the RemoteFile pool for all of the backups jobs that we will be setting up. With that said, you may want to change some of the settings before proceeding.</p>

<h2 id="install-bacula-client-on-each-server">Install Bacula Client on Each Server</h2>

<p>Install the Bacula client on each server that you want to back up (db1, app1, app2, and lb1) by following the <strong>Install and Configure Bacula Client</strong> section of this tutorial: <a href="https://indiareads/community/tutorials/how-to-back-up-an-ubuntu-14-04-server-with-bacula#install-and-configure-bacula-client">How To Back Up an Ubuntu 14.04 Server with Bacula</a>. Stop when you reach the <strong>Add FileSets (Server)</strong> section.</p>

<p>Note that you will need the <strong>FileDaemon Name</strong> (usually the hostname appended by "-fd") and the <strong>Director Password</strong> (the password that the Bacula server will use to connect to each client) from the <code>bacula-fd.conf</code> file on each server.</p>

<h2 id="add-bacula-clients-to-backups-server">Add Bacula Clients to Backups Server</h2>

<p>On <strong>backups</strong>, the Bacula server, add a <strong>Client resource</strong> to the <code>/etc/bacula/conf.d/clients.conf</code> file for each server that you installed the Bacula client on.</p>

<p>Open the <code>clients.conf</code> file:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo vi /etc/bacula/conf.d/clients.conf
</li></ul></code></pre>
<p>Here is an example of the Client resource definition for the database server, <strong>db1</strong>. Note that the value of <strong>Name</strong> should match the the Name of the <strong>FileDaemon</strong> resource and the <strong>Password</strong> should match the Password of the <strong>Director</strong> resource, on the client server—these values can be found in <code>/etc/bacula/bacula-fd.conf</code> on each Bacula client server:</p>
<div class="code-label " title="clients.conf — Example Client resource definition">clients.conf — Example Client resource definition</div><pre class="code-pre "><code langs="">Client {
  Name = <span class="highlight">db1</span>-fd
  Address = <span class="highlight">db1.nyc3.example.com</span>
  FDPort = 9102
  Catalog = MyCatalog
  Password = "<span class="highlight">PDL47XPnjI0QzRpZVJKCDJ_xqlMOp4k46</span>"          # password for Remote FileDaemon
  File Retention = 30 days            # 30 days
  Job Retention = 6 months            # six months
  AutoPrune = yes                     # Prune expired Jobs/Files
}
</code></pre>
<p>Create a similar Client resource for each of the remaining Bacula client servers. In our example, there should be four Client resources when we are finished: <strong>db1-fd</strong>, <strong>app1-fd</strong>, <strong>app2-fd</strong>, and <strong>lb1-fd</strong>. This configures the Bacula Director, on the <strong>backups</strong> server, to be able to connect to the Bacula client on each server..</p>

<p>Save and exit.</p>

<p>More details about this section can be found in the <strong>Install and Configure Bacula Client</strong> in the <a href="https://indiareads/community/tutorials/how-to-back-up-an-ubuntu-14-04-server-with-bacula#install-and-configure-bacula-client">How To Back Up an Ubuntu Server with Bacula tutorial</a>.</p>

<h2 id="create-hot-backups-of-your-database">Create Hot Backups of Your Database</h2>

<p>To ensure that we produce consistent (i.e. usable) backups of our active database, special care must be taken. A simple and effective way to create hot backups with MySQL is to use Percona XtraBackup.</p>

<h3 id="install-percona-xtrabackup">Install Percona XtraBackup</h3>

<p>On your database server, <strong>db1</strong>, install and configure Percona XtraBackup by following this tutorial: <a href="https://indiareads/community/tutorials/how-to-create-hot-backups-of-mysql-databases-with-percona-xtrabackup-on-ubuntu-14-04">How To Create Hot Backups of MySQL Databases with Percona XtraBackup on Ubuntu 14.04</a>. Stop when you reach the <strong>Perform Full Hot Backup</strong> section.</p>

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
<p>Save and exit. Running this script (with superuser privileges) will delete the existing XtraBackup backup at <code>/data/backups/full</code> and create a new full backup. More details about creating backups with XtraBackup can be found in the <a href="https://indiareads/community/tutorials/how-to-create-hot-backups-of-mysql-databases-with-percona-xtrabackup-on-ubuntu-14-04#perform-full-hot-backup">Perform Full Hot Backup</a> section of the of the XtraBackup tutorial.</p>

<p>Make the script executable:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo chmod +x /usr/local/bin/run_xtrabackup.sh
</li></ul></code></pre>
<p>In order to properly backup our database, we must run (and complete) the XtraBackup script before Bacula tries to backup the database server.  A good solution is to configure your Bacula backup job to run the script as a "pre-backup script", but we will opt to use a <a href="https://indiareads/community/tutorials/how-to-schedule-routine-tasks-with-cron-and-anacron-on-a-vps">cron job</a> to keep it simple.</p>

<p>Create a cron configuration file (files in <code>/etc/cron.d</code> get added to root's crontab):</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo vi /etc/cron.d/xtrabackup
</li></ul></code></pre>
<p>Add the following cron job:</p>
<div class="code-label " title="/etc/cron.d/xtrabackup">/etc/cron.d/xtrabackup</div><pre class="code-pre "><code langs=""><span class="highlight">30 22</span>    * * *   root    /usr/local/bin/run_xtrabackup.sh
</code></pre>
<p>This schedules the script to run as root every day at 10:30pm (22nd hour, 30th minute). We chose this time because Bacula is currently scheduled to run its backup jobs at 11:05pm daily—we will discuss adjusting this later. This allows 35 minutes for the XtraBackup script to complete.</p>

<p>Now that the database hot backups are set up, let's look at the Bacula backup FileSets.</p>

<h2 id="configure-bacula-filesets">Configure Bacula FileSets</h2>

<p>Bacula will create backups of files that are specified in the FileSets that are associated with the backup Jobs that will be executed. This section will cover creating FileSets that include the <strong>required backups</strong> that we identified in our recovery plans. More details about adding FileSets to Bacula can be found in the <a href="https://indiareads/community/tutorials/how-to-back-up-an-ubuntu-14-04-server-with-bacula#add-filesets-(server)">Add FileSets (Server)</a> section of the Bacula tutorial.</p>

<p>On your <strong>backups</strong> server, open the <code>filesets.conf</code> file:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo vi /etc/bacula/conf.d/filesets.conf
</li></ul></code></pre>
<h3 id="database-server-fileset">Database Server FileSet</h3>

<p>The required backups for our database server, according to our database server recovery plan, include:</p>

<ul>
<li><strong>MySQL database:</strong> a backup copy is created by our XtraBackup script in <code>/data/backups/full</code>, daily at 10:30pm</li>
<li><strong>MySQL configuration:</strong> located in <code>/etc/mysql</code></li>
</ul>

<p>We also will include the XtraBackup script: <code>/usr/local/bin/run_xtrabackup.sh</code>, and the associated cron file.</p>

<p>With our required backups in mind, we will add this "MySQL Database" FileSet to our Bacula configuration:</p>
<div class="code-label " title="filesets.conf — MySQL Database">filesets.conf — MySQL Database</div><pre class="code-pre "><code langs="">FileSet {
  Name = "MySQL Database"
  Include {
    Options {
      signature = MD5
      compression = GZIP
    }
    <span class="highlight">File = /data/backups</span>
    <span class="highlight">File = /etc/mysql/my.cnf</span>
    <span class="highlight">File = /usr/local/bin/run_xtrabackup.sh</span>
    <span class="highlight">File = /etc/cron.d/xtrabackup</span>
  }
  Exclude {
    File = /data/backups/exclude
  }
}
</code></pre>
<p>Now let's move on to the application server FileSet.</p>

<h3 id="application-server-fileset">Application Server FileSet</h3>

<p>The required backups for our application servers, according to our application server recovery plan, include:</p>

<ul>
<li><strong>Application Files:</strong> located in <code>/var/www/html</code> in our example</li>
</ul>

<p>With our required backups in mind, we will add this "Apache DocumentRoot" FileSet to our Bacula configuration:</p>
<div class="code-label " title="filesets.conf — Apache DocumentRoot">filesets.conf — Apache DocumentRoot</div><pre class="code-pre "><code langs="">FileSet {
  Name = "Apache DocumentRoot"
  Include {
    Options {
      signature = MD5
      compression = GZIP
    }
    <span class="highlight">File = /var/www/html</span>
  }
  Exclude {
    File = /var/www/html/exclude
  }
}
</code></pre>
<p>You may want to also include the Apache ports configuration file, but that is easily replaceable.</p>

<p>Now let's move on to the load balancer server FileSet.</p>

<h3 id="load-balancer-server-fileset">Load Balancer Server FileSet</h3>

<p>The required backups for our load balancer servers, according to our load balancer server recovery plan, include:</p>

<ul>
<li><strong>SSL Certificate (PEM) and related files:</strong> located in <code>/root/certs</code> in our example</li>
<li><strong>HAProxy configuration file:</strong> located in <code>/etc/haproxy</code></li>
</ul>

<p>With our required backups in mind, we will add this "Apache DocumentRoot" FileSet to our Bacula configuration:</p>
<div class="code-label " title="filesets.conf — SSL Certs and HAProxy Config">filesets.conf — SSL Certs and HAProxy Config</div><pre class="code-pre "><code langs="">FileSet {
  Name = "SSL Certs and HAProxy Config"
  Include {
    Options {
      signature = MD5
      compression = GZIP
    }
    <span class="highlight">File = /root/certs</span>
    <span class="highlight">File = /etc/haproxy</span>
  }
  Exclude {
    File = /root/exclude
  }
}
</code></pre>
<p>Save and exit.</p>

<p>Now our FileSets are configured. Let's move on to the creating the Bacula backup Jobs that will use these FileSets.</p>

<h2 id="create-bacula-backup-jobs">Create Bacula Backup Jobs</h2>

<p>We will create Bacula backup Jobs that will run and create backups of our servers.</p>

<p>Create a <code>jobs.conf</code> file in <code>/etc/bacula/conf.d</code>:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo vi /etc/bacula/conf.d/jobs.conf
</li></ul></code></pre>
<h3 id="database-server-backup-job">Database Server Backup Job</h3>

<p>For our database server backup job, we will create a new job named "Backup db1". The important thing here is that we specify the correct <strong>Client</strong> (db1-fd) and <strong>FileSet</strong> (MySQL Database):</p>
<div class="code-label " title="jobs.conf — Backup db1">jobs.conf — Backup db1</div><pre class="code-pre "><code langs="">Job {
  Name = "Backup db1"
  JobDefs = "DefaultJob"
  Client = <span class="highlight">db1-fd</span>
  Pool = RemoteFile
  FileSet="<span class="highlight">MySQL Database</span>"
}
</code></pre>
<p>Now we will set up the application server backup jobs.</p>

<h3 id="application-server-backup-jobs">Application Server Backup Jobs</h3>

<p>For our application servers, we will create two backup jobs named "Backup app1" and "Backup app2". The important thing here is that we specify the correct <strong>Clients</strong> (app1-fd and app2-fd) and <strong>FileSet</strong> (Apache DocumentRoot).</p>

<p>App1 job:</p>
<div class="code-label " title="jobs.conf — Backup app1">jobs.conf — Backup app1</div><pre class="code-pre "><code langs="">Job {
  Name = "Backup app1"
  JobDefs = "DefaultJob"
  Client = <span class="highlight">app1-fd</span>
  Pool = RemoteFile
  FileSet="<span class="highlight">Apache DocumentRoot</span>"
}
</code></pre>
<p>App2 job:</p>
<div class="code-label " title="jobs.conf — Backup app2">jobs.conf — Backup app2</div><pre class="code-pre "><code langs="">Job {
  Name = "Backup app2"
  JobDefs = "DefaultJob"
  Client = <span class="highlight">app2-fd</span>
  Pool = RemoteFile
  FileSet="<span class="highlight">Apache DocumentRoot</span>"
}
</code></pre>
<p>Now we will set up the load balancer server backup job.</p>

<h3 id="load-balancer-server-backup-job">Load Balancer Server Backup Job</h3>

<p>For our load balancer server backup job, we will create a new job named "Backup lb1". The important thing here is that we specify the correct <strong>Client</strong> (lb1-fd) and <strong>FileSet</strong> (SSL Certs and HAProxy Config):</p>
<div class="code-label " title="jobs.conf — Backup lb1">jobs.conf — Backup lb1</div><pre class="code-pre "><code langs="">Job {
  Name = "Backup lb1"
  JobDefs = "DefaultJob"
  Client = <span class="highlight">lb1-fd</span>
  Pool = RemoteFile
  FileSet="<span class="highlight">SSL Certs and HAProxy Config</span>"
}
</code></pre>
<p>Save and exit.</p>

<p>Now our backup Jobs are configured. The last step is to restart the Bacula Director.</p>

<h2 id="restart-bacula-director">Restart Bacula Director</h2>

<p>On the <strong>backups</strong> server, restart the Bacula Director to put all of our changes into effect:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo service bacula-director restart
</li></ul></code></pre>
<p>At this point, you will want to test your client connections and backup jobs, both of which are covered in the <a href="https://indiareads/community/tutorials/how-to-back-up-an-ubuntu-14-04-server-with-bacula#test-client-connection">How To Back Up a Server with Bacula tutorial</a>. That tutorial also covers how to restore Bacula backups. Note that restoring the MySQL database will require you to follow the <a href="https://indiareads/community/tutorials/how-to-create-hot-backups-of-mysql-databases-with-percona-xtrabackup-on-ubuntu-14-04#perform-backup-restoration">Perform Backup Restoration</a> step in the Percona XtraBackup Tutorial.</p>

<h2 id="review-backups-schedule">Review Backups Schedule</h2>

<p>The Bacula backups schedule can be adjusted by modifying the Bacula Director configuration (<code>/etc/bacula/bacula-dir.conf</code>). All of the backup Jobs that we created use the "DefaultJob" JobDef, which uses the "WeeklyCycle" schedule, which is defined as:</p>

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
Incremental    Backup    10  20-May-15 23:05    Backup lb1         Remote-0002
Incremental    Backup    10  20-May-15 23:05    Backup app2        Remote-0002
Incremental    Backup    10  20-May-15 23:05    Backup app1        Remote-0002
Incremental    Backup    10  20-May-15 23:05    Backup db1         Remote-0002
</code></pre>
<p>Feel free to add or adjust the schedule of any of your backup jobs. It would make sense to modify the schedule of the application servers to occur at the same time that the Percona XtraBackup script is executed (10:30pm). This will prevent the application and database backups from being inconsistent with each other.</p>

<h2 id="set-up-remote-backups">Set Up Remote Backups</h2>

<p>Now we're ready to set up a remote server that will store copies of our Bacula backups. This remote server should be in a geographically separate region so you will have a copy of your critical backups even if there is a disaster in your production data center. In our example, we will use IndiaReads's San Francisco (SFO1) region for our <strong>remotebackups</strong> server.</p>

<p>We will explain a simple method to send our backups from our <strong>backups</strong> server to our <strong>remotebackups</strong> server using public SSH keys, rsync, and cron.</p>

<p>On the <strong>remotebackups</strong> server, <a href="https://indiareads/community/tutorials/how-to-add-and-delete-users-on-an-ubuntu-14-04-vps">create a user</a> that will be used for the rsync login.</p>

<p>Next, on the <strong>backups</strong> server, generate a password-less SSH key pair as root. Install the public key on the <strong>remotebackups</strong> user that you just created. This is covered in our <a href="https://indiareads/community/tutorials/how-to-set-up-ssh-keys--2">How To Set Up SSH Keys</a> tutorial.</p>

<p>On the <strong>backups</strong> server, write up an rsync command that copies the Bacula backup data (<code>/bacula/backup</code>) to somewhere on the <strong>remotebackups</strong> server. Rsync usage is covered in our <a href="https://indiareads/community/tutorials/how-to-use-rsync-to-sync-local-and-remote-directories-on-a-vps">How To Use Rsync tutorial</a>. The command will probably look something like this: </p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">rsync -az /bacula/backup <span class="highlight">remoteuser</span>@<span class="highlight">remotebackups_public_hostname_or_IP</span>:/<span class="highlight">path/to/remote/backup</span>
</li></ul></code></pre>
<p>Add the command to a script, such as <code>/usr/local/bin/rsync_backups.sh</code> and make it executable.</p>

<p>Lastly, you will want to set up a cron job that runs the <code>rsync_backups.sh</code> script as root, after the Bacula backups jobs usually complete. This is covered in our <a href="https://indiareads/community/tutorials/how-to-schedule-routine-tasks-with-cron-and-anacron-on-a-vps">How To Schedule Routine Tasks With Cron tutorial</a>.</p>

<p>After you set all of this up, verify that there is a copy of your backups on the <strong>remotebackups</strong> server the next day.</p>

<h2 id="other-considerations">Other Considerations</h2>

<p>We didn't talk about the disk requirements for your backups. You will definitely want to review how much disk space your backups are using, and revise your setup and backups schedule based on your needs and resources.</p>

<p>In addition to creating backups of your application servers, you will probably want to set up backups for any other servers that are added to your setup. For example, you should configure Bacula to create backups of your monitoring and centralized logging servers once you get them up and running.</p>

<h2 id="conclusion">Conclusion</h2>

<p>You should now have daily backups, and a remote copy of those backups, of your production application servers. Be sure to verify that you are able to restore the files, and add the steps of restoring your data to your recovery plans.</p>

<p>Continue to the next tutorial to start setting up the monitoring for your production server setup: <a href="https://indiareads/community/tutorials/building-for-production-web-applications-monitoring">Building for Production: Web Applications — Monitoring</a>.</p>

    