<link rel='stylesheet' href='digital_ocean.css'><br><img src=https://community-cdn-digitalocean-com.global.ssl.fastly.net/assets/tutorials/images/large/bacula_twitter.png?1433958927/> <br> 
      <h3 id="introduction">Introduction</h3>

<p>Bacula is an open source network backup solution that allows you create backups and perform data recovery of your computer systems. It is very flexible and robust, which makes it, while slightly cumbersome to configure, suitable for backups in many situations. A backup system is an <a href="https://indiareads/community/tutorials/5-ways-to-improve-your-production-web-application-server-setup">important component in most server infrastructures</a>, as recovering from data loss is often a critical part of disaster recovery plans.</p>

<p>In this tutorial, we will show you how to install and configure the server components of Bacula on an Ubuntu 14.04 server. We will configure Bacula to perform a weekly job that creates a local backup (i.e. a backup of its own host). This, by itself, is not a particularly compelling use of Bacula, but it will provide you with a good starting point for creating backups of your other servers, i.e. the backup clients. The next tutorial in this series will cover creating backups of your other, remote, servers by installing and configuring the Bacula client, and configuring the Bacula server.</p>

<p>If you'd rather use CentOS 7 instead, follow this link: <a href="https://indiareads/community/tutorials/how-to-install-bacula-server-on-centos-7">How To Install Bacula Server on CentOS 7</a>.</p>

<h2 id="prerequisites">Prerequisites</h2>

<p>You must have superuser (sudo) access on an Ubuntu 14.04 server. Also, the server will require adequate disk space for all of the backups that you plan on retaining at any given time.</p>

<p>If you are using IndiaReads, you should enable <strong>Private Networking</strong> on your Bacula server, and all of your client servers that are in the same datacenter region. This will allow your servers to use private networking when performing backups, reducing network overhead. </p>

<p>We will configure Bacula to use the private FQDN of our servers, e.g. <code>bacula.private.example.com</code>. If you don't have a DNS setup, use the appropriate IP addresses instead. If you don't have private networking enabled, replace all network connection information in this tutorial with network addresses that are reachable by servers in question (e.g. public IP addresses or VPN tunnels).</p>

<p>Let's get started by looking at an overview of Bacula's components.</p>

<h2 id="bacula-component-overview">Bacula Component Overview</h2>

<p>Although Bacula is composed of several software components, it follows the server-client backup model; to simplify the discussion, we will focus more on the <strong>backup server</strong> and the <strong>backup clients</strong> than the individual Bacula components. Still, it is important to have cursory knowledge of the various Bacula components, so we will go over them now.</p>

<p>A Bacula <strong>server</strong>, which we will also refer to as the "backup server", has these components:</p>

<ul>
<li><strong>Bacula Director (DIR):</strong> Software that controls the backup and restore operations that are performed by the File and Storage daemons</li>
<li><strong>Storage Daemon (SD):</strong>  Software that performs reads and writes on the storage devices used for backups</li>
<li><strong>Catalog:</strong> Services that maintain a database of files that are backed up. The database is stored in an SQL database such as MySQL or PostgreSQL</li>
<li><strong>Bacula Console:</strong> A command-line interface that allows the backup administrator to interact with, and control, Bacula Director</li>
</ul>
<pre class="code-pre note"><code langs="">Note: The Bacula server components don't need to run on the same server, but they all work together to provide the backup server functionality.
</code></pre>
<p>A Bacula <strong>client</strong>, i.e. a server that will be backed up, runs the <strong>File Daemon (FD)</strong> component. The File Daemon is software that provides the Bacula server (the Director, specifically) access to the data that will be backed up. We will also refer to these servers as "backup clients" or "clients".</p>

<p>As we noted in the introduction, we will configure the backup server to create a backup of its own filesystem. This means that the backup server will also be a backup client, and will run the File Daemon component.</p>

<p>Let's get started with the installation.</p>

<h2 id="install-mysql">Install MySQL</h2>

<p>Bacula uses an SQL database, such as MySQL or PostreSQL, to manage its backups catalog. We will use MySQL in this tutorial.</p>

<p>First, update apt-get:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo apt-get update
</li></ul></code></pre>
<p>Now install MySQL Server with apt-get:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo apt-get install mysql-server
</li></ul></code></pre>
<p>You will be prompted for a password for the MySQL database administrative user, root. Enter a password, then confirm it.</p>

<p>Remember this password, as it will be used in the Bacula installation process.</p>

<h2 id="install-bacula">Install Bacula</h2>

<p>Install the Bacula server and client components, using apt-get:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo apt-get install bacula-server bacula-client
</li></ul></code></pre>
<p>You will be prompted for some information that will be used to configure Postfix, which Bacula uses:</p>

<ul>
<li><strong>General Type of Mail Configuration:</strong> Choose "Internet Site"</li>
<li><strong>System Mail Name:</strong> Enter your server's FQDN or hostname</li>
</ul>

<p>Next, you will be prompted for information that will be used to set up the Bacula database:</p>

<ul>
<li><strong>Configure database for bacula-director-mysql with dbconfig-common?:</strong> Select "Yes"</li>
<li><strong>Password of the database's administrative user:</strong> Enter your MySQL root password (set during MySQL installation)</li>
<li><strong>MySQL application password for bacula-director-mysql</strong>: Enter a new password and confirm it, or leave the prompt blank to generate a random password</li>
</ul>

<p>The last step in the installation is to update the permissions of a script that Bacula uses during its catalog backup job:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo chmod 755 /etc/bacula/scripts/delete_catalog_backup
</li></ul></code></pre>
<p>The Bacula server (and client) components are now installed. Let's create the backup and restore directories.</p>

<h2 id="create-backup-and-restore-directories">Create Backup and Restore Directories</h2>

<p>Bacula needs a <strong>backup</strong> directory—for storing backup archives—and <strong>restore</strong> directory—where restored files will be placed. If your system has multiple partitions, make sure to create the directories on one that has sufficient space.</p>

<p>Let's create new directories for both of these purposes:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo mkdir -p /bacula/backup /bacula/restore
</li></ul></code></pre>
<p>We need to change the file permissions so that only the bacula process (and a superuser) can access these locations:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo chown -R bacula:bacula /bacula
</li><li class="line" prefix="$">sudo chmod -R 700 /bacula
</li></ul></code></pre>
<p>Now we're ready to configure the Bacula Director.</p>

<h2 id="configure-bacula-director">Configure Bacula Director</h2>

<p>Bacula has several components that must be configured independently in order to function correctly. The configuration files can all be found in the <code>/etc/bacula</code> directory.</p>

<p>We'll start with the Bacula Director.</p>

<p>Open the Bacula Director configuration file in your favorite text editor. We'll use vi:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo vi /etc/bacula/bacula-dir.conf
</li></ul></code></pre>
<h3 id="configure-local-jobs">Configure Local Jobs</h3>

<p>A Bacula job is used to perform backup and restore actions. Job resources define the details of what a particular job will do, including the name of the Client, the FileSet to back up or restore, among other things.</p>

<p>Here, we will configure the jobs that will be used to perform backups of the local filesystem.</p>

<p>In the Director configuration, find the <strong>Job</strong> resource with a name of "BackupClient1" (search for "BackupClient1"). Change the value of <code>Name</code> to "BackupLocalFiles", so it looks like this:</p>
<div class="code-label " title="bacula-dir.conf —  Rename BackupClient1 job">bacula-dir.conf —  Rename BackupClient1 job</div><pre class="code-pre "><code langs="">Job {
  Name = "<span class="highlight">BackupLocalFiles</span>"
  JobDefs = "DefaultJob"
}
</code></pre>
<p>Next, find the <strong>Job</strong> resource that is named "RestoreFiles" (search for "RestoreFiles"). In this job, you want to change two things: update the value of <code>Name</code> to "RestoreLocalFiles", and the value of <code>Where</code> to "/bacula/restore". It should look like this:</p>
<div class="code-label " title="bacula-dir.conf — Rename RestoreFiles job">bacula-dir.conf — Rename RestoreFiles job</div><pre class="code-pre "><code langs="">Job {
  Name = "<span class="highlight">RestoreLocalFiles</span>"
  Type = Restore
  Client=BackupServer-fd
  FileSet="Full Set"
  Storage = File
  Pool = Default
  Messages = Standard
  Where = <span class="highlight">/bacula/restore</span>
}
</code></pre>
<p>This configures the RestoreLocalFiles job to restore files to <code>/bacula/restore</code>, the directory we created earlier.</p>

<h3 id="configure-file-set">Configure File Set</h3>

<p>A Bacula FileSet defines a set of files or directories to <strong>include</strong> or <strong>exclude</strong> files from a backup selection, and are used by jobs.</p>

<p>Find the FileSet resource named "Full Set" (it's under a comment that says, "# List of files to be backed up"). Here we will make three changes: (1) Add the option to use gzip to compress our backups, (2) change the include File from <code>/usr/sbin</code> to <code>/</code>, and (3) change the second exclude File to <code>/bacula</code>. With the comments removed, it should look like this:</p>
<div class="code-label " title="bacula-dir.conf — Update "Full Set" FileSet">bacula-dir.conf — Update "Full Set" FileSet</div><pre class="code-pre "><code langs="">FileSet {
  Name = "Full Set"
  Include {
    Options {
      signature = MD5
      <span class="highlight">compression = GZIP</span>
    }    
File = <span class="highlight">/</span>
}
  Exclude {
    File = /var/lib/bacula
    File = <span class="highlight">/bacula</span>
    File = /proc
    File = /tmp
    File = /.journal
    File = /.fsck
  }
}
</code></pre>
<p>Let's go over the changes that we made to the "Full Set" FileSet. First, we enabled gzip compression when creating a backup archive. Second, we are including <code>/</code>, i.e. the root partition, to be backed up. Third, we are excluding <code>/bacula</code> because we don't want to redundantly back up our Bacula backups and restored files.</p>
<pre class="code-pre note"><code langs="">Note: If you have partitions that are mounted within /, and you want to include those in the FileSet, you will need to include additional File records for each of them.
</code></pre>
<p>Keep in mind that if you always use broad FileSets, like "Full Set", in your backup jobs, your backups will require more disk space than if your backup selections are more specific. For example, a FileSet that only includes your customized configuration files and databases might be sufficient for your needs, if you have a clear recovery plan that details installing required software packages and placing the restored files in the proper locations, while only using a fraction of the disk space for backup archives.</p>

<h3 id="configure-storage-daemon-connection">Configure Storage Daemon Connection</h3>

<p>In the Bacula Director configuration file, the Storage resource defines the Storage Daemon that the Director should connect to. We'll configure the actual Storage Daemon in just a moment.</p>

<p>Find the Storage resource, and replace the value of Address, <code>localhost</code>, with the private FQDN (or private IP address) of your backup server. It should look like this (substitute the highlighted word):</p>
<div class="code-label " title="bacula-dir.conf — Update Storage Address">bacula-dir.conf — Update Storage Address</div><pre class="code-pre "><code langs="">Storage {
  Name = File
# Do not use "localhost" here
  Address = <span class="highlight">backup_server_private_FQDN</span>                # N.B. Use a fully qualified name here
  SDPort = 9103
  Password = "ITXAsuVLi1LZaSfihQ6Q6yUCYMUssdmu_"
  Device = FileStorage
  Media Type = File
}
</code></pre>
<p>This is necessary because we are going to configure the Storage Daemon to listen on the private network interface, so remote clients can connect to it.</p>

<h3 id="configure-pool">Configure Pool</h3>

<p>A Pool resource defines the set of storage used by Bacula to write backups. We will use files as our storage volumes, and we will simply update the label so our local backups get labeled properly.</p>

<p>Find the Pool resource named "File" (it's under a comment that says "# File Pool definition"), and add a line that specifies a Label Format. It should look like this when you're done:</p>
<div class="code-label " title="bacula-dir.conf — Update Pool:">bacula-dir.conf — Update Pool:</div><pre class="code-pre "><code langs=""># File Pool definition
Pool {
  Name = File
  Pool Type = Backup
  <span class="highlight">Label Format = Local-</span>
  Recycle = yes                       # Bacula can automatically recycle Volumes
  AutoPrune = yes                     # Prune expired volumes
  Volume Retention = 365 days         # one year
  Maximum Volume Bytes = 50G          # Limit Volume size to something reasonable
  Maximum Volumes = 100               # Limit number of Volumes in Pool
}
</code></pre>
<p>Save and exit. You're finally done configuring the Bacula Director.</p>

<h3 id="check-director-configuration">Check Director Configuration:</h3>

<p>Let's verify that there are no syntax errors in your Director configuration file:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo bacula-dir -tc /etc/bacula/bacula-dir.conf
</li></ul></code></pre>
<p>If there are no error messages, your <code>bacula-dir.conf</code> file has no syntax errors.</p>

<p>Next, we'll configure the Storage Daemon.</p>

<h2 id="configure-storage-daemon">Configure Storage Daemon</h2>

<p>Our Bacula server is almost set up, but we still need to configure the Storage Daemon, so Bacula knows where to store backups.</p>

<p>Open the SD configuration in your favorite text editor. We'll use vi:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo vi /etc/bacula/bacula-sd.conf
</li></ul></code></pre>
<h3 id="configure-storage-resource">Configure Storage Resource</h3>

<p>Find the Storage resource. This defines where the SD process will listen for connections. Add the <code>SDAddress</code> parameter, and assign it to the private FQDN (or private IP address) of your backup server:</p>
<div class="code-label " title="bacula-sd.conf — update SDAddress">bacula-sd.conf — update SDAddress</div><pre class="code-pre "><code langs="">Storage {                             # definition of myself
  Name = BackupServer-sd
  SDPort = 9103                  # Director's port
  WorkingDirectory = "/var/lib/bacula"
  Pid Directory = "/var/run/bacula"
  Maximum Concurrent Jobs = 20
  <span class="highlight">SDAddress = backup_server_private_FQDN</span>
}
</code></pre>
<h3 id="configure-storage-device">Configure Storage Device</h3>

<p>Next, find the Device resource named "FileStorage" (search for "FileStorage"), and update the value of <code>Archive Device</code> to match your backups directory:</p>
<div class="code-label " title="bacula-sd.conf — update Archive Device">bacula-sd.conf — update Archive Device</div><pre class="code-pre "><code langs="">Device {
  Name = FileStorage
  Media Type = File
  Archive Device = <span class="highlight">/bacula/backup</span> 
  LabelMedia = yes;                   # lets Bacula label unlabeled media
  Random Access = Yes;
  AutomaticMount = yes;               # when device opened, read it
  RemovableMedia = no;
  AlwaysOpen = no;
}
</code></pre>
<p>Save and exit.</p>

<h3 id="verify-storage-daemon-configuration">Verify Storage Daemon Configuration</h3>

<p>Let's verify that there are no syntax errors in your Storage Daemon configuration file:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo bacula-sd -tc /etc/bacula/bacula-sd.conf
</li></ul></code></pre>
<p>If there are no error messages, your <code>bacula-sd.conf</code> file has no syntax errors.</p>

<p>We've completed the Bacula configuration. We're ready to restart the Bacula server components.</p>

<h2 id="restart-bacula-director-and-storage-daemon">Restart Bacula Director and Storage Daemon</h2>

<p>To put the configuration changes that you made into effect, restart Bacula Director and Storage Daemon with these commands:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo service bacula-director restart
</li><li class="line" prefix="$">sudo service bacula-sd restart
</li></ul></code></pre>
<p>Now that both services have been restarted, let's test that it works by running a backup job.</p>

<h2 id="test-backup-job">Test Backup Job</h2>

<p>We will use the Bacula Console to run our first backup job. If it runs without any issues, we will know that Bacula is configured properly.</p>

<p>Now enter the Console with this command:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo bconsole
</li></ul></code></pre>
<p>This will take you to the Bacula Console prompt, denoted by a <code>*</code> prompt.</p>

<h3 id="create-a-label">Create a Label</h3>

<p>Begin by issuing a <code>label</code> command:</p>
<pre class="code-pre custom_prefix"><code langs=""><ul class="prefixed"><li class="line" prefix="*">label
</li></ul></code></pre>
<p>You will be prompted to enter a volume name. Enter any name that you want:</p>
<pre class="code-pre "><code langs=""><div class="secondary-code-label " title="Enter new Volume name:">Enter new Volume name:</div><span class="highlight">MyVolume</span>
</code></pre>
<p>Then select the pool that the backup should use. We'll use the "File" pool that we configured earlier, by entering "2":</p>
<pre class="code-pre "><code langs=""><div class="secondary-code-label " title="Select the Pool (1-3):">Select the Pool (1-3):</div>2
</code></pre>
<h3 id="manually-run-backup-job">Manually Run Backup Job</h3>

<p>Bacula now knows how we want to write the data for our backup. We can now run our backup to test that it works correctly:</p>
<pre class="code-pre custom_prefix"><code langs=""><ul class="prefixed"><li class="line" prefix="*">run
</li></ul></code></pre>
<p>You will be prompted to select which job to run. We want to run the "BackupLocalFiles" job, so enter "1" at the prompt:</p>
<pre class="code-pre "><code langs=""><div class="secondary-code-label " title="Select Job resource (1-3):">Select Job resource (1-3):</div>1
</code></pre>
<p>At the "Run Backup job" confirmation prompt, review the details, then enter "yes" to run the job:</p>
<pre class="code-pre custom_prefix"><code langs=""><ul class="prefixed"><li class="line" prefix="*">yes
</li></ul></code></pre>
<h3 id="check-messages-and-status">Check Messages and Status</h3>

<p>After running a job, Bacula will tell you that you have messages. The messages are output generated by running jobs.</p>

<p>Check the messages by typing:</p>
<pre class="code-pre custom_prefix"><code langs=""><ul class="prefixed"><li class="line" prefix="*">messages
</li></ul></code></pre>
<p>The messages should say "No prior Full backup Job record found", and that the backup job started. If there are any errors, something is wrong, and they should give you a hint as to why the job did not run.</p>

<p>Another way to see the status of the job is to check the status of the Director. To do this, enter this command at the bconsole prompt:</p>
<pre class="code-pre custom_prefix"><code langs=""><ul class="prefixed"><li class="line" prefix="*">status director
</li></ul></code></pre>
<p>If everything is working properly, you should see that your job is running. Something like this:</p>
<pre class="code-pre "><code langs=""><div class="secondary-code-label " title="Output — status director (Running Jobs)">Output — status director (Running Jobs)</div>Running Jobs:
Console connected at 09-Apr-15 12:16
 JobId Level   Name                       Status
======================================================================
     3 Full    BackupLocalFiles.2015-04-09_12.31.41_06 is <span class="highlight">running</span>
====
</code></pre>
<p>When your job completes, it will move to the "Terminated Jobs" section of the status report, like this:</p>
<pre class="code-pre "><code langs=""><div class="secondary-code-label " title="Output — status director (Terminated Jobs)">Output — status director (Terminated Jobs)</div>Terminated Jobs:
 JobId  Level    Files      Bytes   Status   Finished        Name
====================================================================
     3  Full    161,124    877.5 M  <span class="highlight">OK</span>       09-Apr-15 12:34 BackupLocalFiles
</code></pre>
<p>The "OK" status indicates that the backup job ran without any problems. Congratulations! You have a backup of the "Full Set" of your Bacula server.</p>

<p>The next step is to test the restore job.</p>

<h2 id="test-restore-job">Test Restore Job</h2>

<p>Now that a backup has been created, it is important to check that it can be restored properly. The <code>restore</code> command will allow us restore files that were backed up.</p>

<h3 id="run-restore-all-job">Run Restore All Job</h3>

<p>To demonstrate, we'll restore all of the files in our last backup:</p>
<pre class="code-pre custom_prefix"><code langs=""><ul class="prefixed"><li class="line" prefix="*">restore all
</li></ul></code></pre>
<p>A selection menu will appear with many different options, which are used to identify which backup set to restore from. Since we only have a single backup, let's "Select the most recent backup"—select option 5:</p>
<pre class="code-pre "><code langs=""><div class="secondary-code-label " title="Select item (1-13):">Select item (1-13):</div>5
</code></pre>
<p>Because there is only one client, the Bacula server, it will automatically be selected.</p>

<p>The next prompt will ask which FileSet you want to use. Select "Full Set", which should be 2:</p>
<pre class="code-pre "><code langs=""><div class="secondary-code-label " title="Select FileSet resource (1-2):">Select FileSet resource (1-2):</div>2
</code></pre>
<p>This will drop you into a virtual file tree with the entire directory structure that you backed up. This shell-like interface allows for simple commands to mark and unmark files to be restored.</p>

<p>Because we specified that we wanted to "restore all", every backed up file is already marked for restoration. Marked files are denoted by a leading <code>*</code> character.</p>

<p>If you would like to fine-tune your selection, you can navigate and list files with the "ls" and "cd" commands, mark files for restoration with "mark", and unmark files with "unmark". A full list of commands is available by typing "help" into the console.</p>

<p>When you are finished making your restore selection, proceed by typing:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">done
</li></ul></code></pre>
<p>Confirm that you would like to run the restore job:</p>
<pre class="code-pre "><code langs=""><div class="secondary-code-label " title="OK to run? (yes/mod/no):">OK to run? (yes/mod/no):</div>yes
</code></pre>
<h3 id="check-messages-and-status">Check Messages and Status</h3>

<p>As with backup jobs, you should check the messages and Director status after running a restore job.</p>

<p>Check the messages by typing:</p>
<pre class="code-pre custom_prefix"><code langs=""><ul class="prefixed"><li class="line" prefix="*">messages
</li></ul></code></pre>
<p>There should be a message that says the restore job has started or was terminated with an "Restore OK" status. If there are any errors, something is wrong, and they should give you a hint as to why the job did not run.</p>

<p>Again, checking the Director status is a great way to see the state of a restore job:</p>
<pre class="code-pre custom_prefix"><code langs=""><ul class="prefixed"><li class="line" prefix="*">status director
</li></ul></code></pre>
<p>When you are finished with the restore, type <code>exit</code> to leave the Bacula Console:</p>
<pre class="code-pre custom_prefix"><code langs=""><ul class="prefixed"><li class="line" prefix="*">exit
</li></ul></code></pre>
<h3 id="verify-restore">Verify Restore</h3>

<p>To verify that the restore job actually restored the selected files, you can look in the <code>/bacula/restore</code> directory (which was defined in the "RestoreLocalFiles" job in the Director configuration):</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo ls -la /bacula/restore
</li></ul></code></pre>
<p>You should see restored copies of the files in your root file system, excluding the files and directories that were listed in the "Exclude" section of the "RestoreLocalFiles" job. If you were trying to recover from data loss, you could copy the restored files to their appropriate locations.</p>

<h3 id="delete-restored-files">Delete Restored Files</h3>

<p>You may want to delete the restored files to free up disk space. To do so, use this command:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo -u root bash -c "rm -rf /bacula/restore/*"
</li></ul></code></pre>
<p>Note that you have to run this <code>rm</code> command as root, as many of the restored files are owned by root.</p>

<h2 id="conclusion">Conclusion</h2>

<p>You now have a basic Bacula setup that can backup and restore your local file system. The next step is to add your other servers as backup clients so you can recover them, in case of data loss.</p>

<p>The next tutorial will show you how to add your other, remote servers as Bacula clients: <a href="https://indiareads/community/tutorials/how-to-back-up-an-ubuntu-14-04-server-with-bacula">How To Back Up an Ubuntu 14.04 Server with Bacula</a>.</p>

    