<link rel='stylesheet' href='digital_ocean.css'><br> <br> 
      <h3 id="introduction">Introduction</h3>

<p>This tutorial will show you how to set up Bacula to create backups of a remote Ubuntu 14.04 host, over a network connection. This involves installing and configuring the Bacula Client software on a remote host, and making some additions to the configuration of an existing Bacula Server (covered in the prerequisites).</p>

<p>If you are trying to create backups of CentOS 7 hosts, follow this link instead: <a href="https://indiareads/community/tutorials/how-to-back-up-a-centos-7-server-with-bacula">How To Back Up a CentOS 7 Server with Bacula</a>.</p>

<h2 id="prerequisites">Prerequisites</h2>

<p>This tutorial assumes that you have a server running the Bacula Server components, as described in this link: <a href="https://indiareads/community/tutorials/how-to-install-bacula-server-on-ubuntu-14-04">How To Install Bacula Server on Ubuntu 14.04</a>.</p>

<p>We are also assuming that you are using private network interfaces for backup server-client communications. We will refer to the private FQDN of the servers (FQDNs that point to the private IP addresses). If you are using IP addresses, simply substitute the connection information where appropriate.</p>

<p>For the rest of this tutorial, we will refer to the Bacula Server as "BaculaServer", "Bacula Server", or "Backup Server". We will refer to the remote host, that is being backed up, as "ClientHost", "Client Host", or "Client".</p>

<p>Let's get started by making some quick changes to the Bacula Server configuration.</p>

<h2 id="organize-bacula-director-configuration-server">Organize Bacula Director Configuration (Server)</h2>

<p>On your <strong>Bacula Server</strong>, perform this section once.</p>

<p>When setting up your Bacula Server, you may have noticed that the configuration files are excessively long. We'll try and organize the Bacula Director configuration a bit, so it uses separate files to add new configuration such as jobs, file sets, and pools.</p>

<p>Let's create a directory to help organize the Bacula configuration files:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo mkdir /etc/bacula/conf.d
</li></ul></code></pre>
<p>Then open the Bacula Director configuration file:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo vi /etc/bacula/bacula-dir.conf
</li></ul></code></pre>
<p>At the end of the file add, this line:</p>
<div class="code-label " title="bacula-dir.conf — Add to end of file">bacula-dir.conf — Add to end of file</div><pre class="code-pre "><code langs="">@|"find /etc/bacula/conf.d -name '*.conf' -type f -exec echo @{} \;"
</code></pre>
<p>Save and exit. This line makes the Director look in the <code>/etc/bacula/conf.d</code> directory for additional configuration files to append. That is, any <code>.conf</code> file added in there will be loaded as part of the configuration.</p>

<h3 id="add-remotefile-pool">Add RemoteFile Pool</h3>

<p>We want to add an additional Pool to our Bacula Director configuration, which we'll use to configure our remote backup jobs.</p>

<p>Open the <code>conf.d/pools.conf</code> file:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo vi /etc/bacula/conf.d/pools.conf
</li></ul></code></pre>
<p>Add the following Pool resource:</p>
<div class="code-label " title="conf.d/pools.conf — Add Pool resource">conf.d/pools.conf — Add Pool resource</div><pre class="code-pre "><code langs="">Pool {
  Name = RemoteFile
  Pool Type = Backup
  Label Format = Remote-
  Recycle = yes                       # Bacula can automatically recycle Volumes
  AutoPrune = yes                     # Prune expired volumes
  Volume Retention = 365 days         # one year
    Maximum Volume Bytes = 50G          # Limit Volume size to something reasonable
  Maximum Volumes = 100               # Limit number of Volumes in Pool
}
</code></pre>
<p>Save and exit.  This defines a "RemoteFile" pool, which we will use by the backup job that we'll create later. Feel free to change any of the parameters to meet your own needs.</p>

<p>We don't need to restart Bacula Director just yet, but let's verify that its configuration doesn't have any errors in it:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo bacula-dir -tc /etc/bacula/bacula-dir.conf
</li></ul></code></pre>
<p>If there are no errors, you're ready to continue on to the Bacula Client setup.</p>

<h2 id="install-and-configure-bacula-client">Install and Configure Bacula Client</h2>

<p>Perform this section on any <strong>Client Host</strong> that you are adding to your Bacula setup.</p>

<p>First, update apt-get:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo apt-get update
</li></ul></code></pre>
<p>Then install the <code>bacula-client</code> package:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo apt-get install bacula-client
</li></ul></code></pre>
<p>This installs the Bacula File Daemon (FD), which is often referred to as the "Bacula client".</p>

<h3 id="configure-client">Configure Client</h3>

<p>Before configuring the client File Daemon, you will want to look up the following information, which will be used throughout the remainder of this tutorial:</p>

<ul>
<li><strong>Client hostname:</strong>: Our example will use "ClientHost"</li>
<li><strong>Client Private FQDN:</strong> We'll refer to this as "client_private_FQDN", which may look like <code>clienthost.private.example.com</code></li>
<li><strong>Bacula Server hostname:</strong> Our example will use "BackupServer"</li>
</ul>

<p>Your actual setup will vary from the example, so be sure to make substitutions where appropriate.</p>

<p>Open the File Daemon configuration:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo vi /etc/bacula/bacula-fd.conf
</li></ul></code></pre>
<p>We need to change a few items and save some information that we will need for our server configuration.</p>

<p>Begin by finding the Director resource that is named after your client hostname (e.g. "ClientHost-dir"). As the Bacula Director that we want to control this Client is located on the Bacula Server, change the "Name" parameter to the hostname of your backup server followed by "-dir". Following our example, with "BackupServer" as the Bacula Server's hostname, it should look something like this after being updated:</p>
<div class="code-label " title="bacula-fd.conf — Update Director Name">bacula-fd.conf — Update Director Name</div><pre class="code-pre "><code langs="">Director {
  Name = <span class="highlight">BackupServer</span>-dir
  Password = "IrIK4BHRA2o5JUvw2C_YNmBX_70oqfaUi"
}
</code></pre>
<p>You also need to copy the <code>Password</code>, which is the automatically generated password used for connections to File Daemon, and save it for future reference. This will be used in the Backup Server's Director configuration, which we will set in an upcoming step, to connect to your Client's File Daemon.</p>

<p>Next, we need to adjust one parameter in the FileDaemon resource. We will change the <code>FDAddress</code> parameter to match the private FQDN of our client machine. The <code>Name</code> parameter should already be populated correctly with the client file daemon name. The resource should looks something like this (substitute the actual FQDN or IP address):</p>
<div class="code-label " title="bacula-fd.conf — Update FDAddress">bacula-fd.conf — Update FDAddress</div><pre class="code-pre "><code langs="">FileDaemon {                          # this is me
  Name = <span class="highlight">ClientHost</span>-fd
  FDport = 9102                  # where we listen for the director
  WorkingDirectory = /var/lib/bacula
  Pid Directory = /var/run/bacula
  Maximum Concurrent Jobs = 20
  FDAddress = <span class="highlight">client_private_FQDN</span>
}
</code></pre>
<p>We also need to configure this daemon to pass its log messages to the Backup Server. Find the Messages resource and change the <code>director</code> parameter to match your backup server's hostname with a "-dir" suffix. It should look something like this:</p>
<div class="code-label " title="bacula-fd.conf — Update director">bacula-fd.conf — Update director</div><pre class="code-pre "><code langs="">Messages {
  Name = Standard
  director =  <span class="highlight">BackupServer</span>-dir = all, !skipped, !restored
}
</code></pre>
<p>Save the file and exit. Your File Daemon (Bacula Client) is now configured to listen for connections over the private network.</p>

<p>Check that your configuration file has the correct syntax with the following command:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo bacula-fd -tc /etc/bacula/bacula-fd.conf
</li></ul></code></pre>
<p>If the command returns no output, the configuration file has valid syntax. Restart the file daemon to use the new settings:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo service bacula-fd restart
</li></ul></code></pre>
<p>Let's set up a directory that the Bacula Server can restore files to. Create the file structure and lock down the permissions and ownership for security with the following commands:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo mkdir -p /bacula/restore
</li><li class="line" prefix="$">sudo chown -R bacula:bacula /bacula
</li><li class="line" prefix="$">sudo chmod -R 700 /bacula
</li></ul></code></pre>
<p>The client machine is now configured correctly. Next, we will configure the Backup Server to be able to connect to the Bacula Client.</p>

<h2 id="add-filesets-server">Add FileSets (Server)</h2>

<p>A Bacula FileSet defines a set of files or directories to include or exclude files from a backup selection, and are used by backup jobs on the Bacula Server.</p>

<p>If you followed the prerequisite tutorial, which sets up the Bacula Server components, you already have a FileSet called "Full Set". If you want to run Backup jobs that include almost every file on your Backup Clients, you can use that FileSet in your jobs. You may find, however, that you often don't want or need to have backups of everything on a server, and that a subset of data will suffice.</p>

<p>Being more selective in which files are included in a FileSet will decrease the amount of disk space and time, required by your Backup Server, to run a backup job. It can also make restoration simpler, as you won't need to sift through the "Full Set" to find which files you want to restore.</p>

<p>We will show you how to create new FileSet resources, so that you can be more selective in what you back up.</p>

<p>On your <strong>Bacula Server</strong>, open a file called <code>filesets.conf</code>, in the Bacula Director configuration directory we created earlier:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo vi /etc/bacula/conf.d/filesets.conf
</li></ul></code></pre>
<p>Create a FileSet resource for each particular set of files that you want to use in your backup jobs. In this example, we'll create a FileSet that only includes the home and etc directories:</p>
<div class="code-label " title="filesets.conf — Add Home and Etc FileSet">filesets.conf — Add Home and Etc FileSet</div><pre class="code-pre "><code langs="">FileSet {
  Name = "<span class="highlight">Home and Etc</span>"
  Include {
    Options {
      signature = MD5
      compression = GZIP
    }
    <span class="highlight">File = /home</span>
    <span class="highlight">File = /etc</span>
  }
  Exclude {
    <span class="highlight">File = /home/bacula/not_important</span>
  }
}
</code></pre>
<p>There are a lot of things going on in this file, but here are a few details to keep in mind:</p>

<ul>
<li>The FileSet Name must be unique</li>
<li>Include any files or partitions that you want to have backups of</li>
<li>Exclude any files that you don't want to back up, but were selected as a result of existing within an included file</li>
</ul>

<p>You can create multiple FileSets if you wish. Save and exit, when you are finished.</p>

<p>Now we're ready to create backup job that will use our new FileSet.</p>

<h2 id="add-client-and-backup-job-to-bacula-server">Add Client and Backup Job to Bacula Server</h2>

<p>Now we're ready to add our Client to the Bacula Server. To do this, we must configure the Bacula Director with new Client and Job resources.</p>

<p>Open the <code>conf.d/clients.conf</code> file:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo vi /etc/bacula/conf.d/clients.conf
</li></ul></code></pre>
<h3 id="add-client-resource">Add Client Resource</h3>

<p>A Client resource configures the Director with the information it needs to connect to the Client Host. This includes the name, address, and password of the Client's File Daemon.</p>

<p>Paste this Client resource definition into the file. Be sure to substitute in your Client hostname, private FQDN, and password (from the Client's <code>bacula-fd.conf</code>), where highlighted:</p>
<div class="code-label " title="conf.d/clients.conf — Add Client resource">conf.d/clients.conf — Add Client resource</div><pre class="code-pre "><code langs="">Client {
  Name = <span class="highlight">ClientHost</span>-fd
  Address = <span class="highlight">client_private_FQDN</span>
  FDPort = 9102 
  Catalog = MyCatalog
  Password = "<span class="highlight">IrIK4BHRA2o5JUvw2C_YNmBX_70oqfaUi</span>"          # password for Remote FileDaemon
  File Retention = 30 days            # 30 days
  Job Retention = 6 months            # six months
  AutoPrune = yes                     # Prune expired Jobs/Files
}
</code></pre>
<p>You only need to do this once for each Client.</p>

<h3 id="create-a-backup-job">Create a backup job:</h3>

<p>A Backup job, which must have a unique name, defines the details of which Client and which data should be backed up.</p>

<p>Next, paste this backup job into the file, substituting the Client hostname for the highlighted text:</p>
<div class="code-label " title="conf.d/clients.conf — Add Backup job resource">conf.d/clients.conf — Add Backup job resource</div><pre class="code-pre "><code langs="">Job {
  Name = "Backup<span class="highlight">ClientHost</span>"
  JobDefs = "DefaultJob"
  Client = <span class="highlight">ClientHost</span>-fd
  Pool = RemoteFile
  FileSet="Home and Etc"
}
</code></pre>
<p>This creates a backup job called "BackupClientHost", which will back up the home and etc directories of the Client Host, as defined in the "Home and Etc" FileSet. It will use the settings specified in the "DefaultJob" JobDefs and "RemoteFile" Pool resources, which are both defined in the main <code>bacula-dir.conf</code> file. By default, jobs that specify <code>JobDefs = "DefaultJob"</code> will run weekly.</p>

<p>Save and exit when you are done.</p>

<h3 id="verify-director-configuration">Verify Director Configuration</h3>

<p>Let's verify that there are no syntax errors in your Director configuration file:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo bacula-dir -tc /etc/bacula/bacula-dir.conf
</li></ul></code></pre>
<p>If you are returned to the shell prompt, there are no syntax errors in your Bacula Director's configuration files.</p>

<h3 id="restart-bacula-director">Restart Bacula Director</h3>

<p>To put the configuration changes that you made into effect, restart Bacula Director:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo service bacula-director restart
</li></ul></code></pre>
<p>Now your Client, or remote host, is configured to be backed up by your Bacula Server.</p>

<h2 id="test-client-connection">Test Client Connection</h2>

<p>We should verify that the Bacula Director can connect to the Bacula Client.</p>

<p>On your Bacula Server, enter the Bacula Console:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo bconsole
</li></ul></code></pre><pre class="code-pre custom_prefix"><code langs=""><ul class="prefixed"><li class="line" prefix="*">status client
</li></ul></code></pre><pre class="code-pre "><code langs=""><div class="secondary-code-label " title="Select Client resource: ClientHost-fd">Select Client resource: ClientHost-fd</div>The defined Client resources are:
     1: BackupServer-fd
     2: ClientHost-fd
Select Client (File daemon) resource (1-2): <span class="highlight">2</span>
</code></pre>
<p>The Client's File Daemon status should return immediately. If it doesn't, and there is a connection error, there is something wrong with the configuration of the Bacula Server or of the Client's File Daemon.</p>

<h2 id="test-backup-job">Test Backup Job</h2>

<p>Let's run the backup job to make sure it works.</p>

<p>On the <strong>Bacula Server</strong>, while still in the Console, use this command:</p>
<pre class="code-pre custom_prefix"><code langs=""><ul class="prefixed"><li class="line" prefix="*">run
</li></ul></code></pre>
<p>You will be prompted to select which Job to run. Select the one we created earlier, e.g. "4. BackupClientHost":</p>
<pre class="code-pre "><code langs=""><div class="secondary-code-label " title="Select Job resource: BackupClientHost">Select Job resource: BackupClientHost</div>The defined Job resources are:
     1: BackupLocalFiles
     2: BackupCatalog
     3: RestoreLocalFiles
     4: BackupClientHost
Select Job resource (1-4): <span class="highlight">4</span>
</code></pre>
<p>At the confirmation prompt, enter "yes":</p>
<pre class="code-pre "><code langs="">Confirmation prompt:
OK to run? (yes/mod/no): <span class="highlight">yes</span>
</code></pre>
<h3 id="check-messages-and-status">Check Messages and Status</h3>

<p>After running a job, Bacula will tell you that you have messages. The messages are output generated by running jobs.</p>

<p>Check the messages by typing:</p>
<pre class="code-pre custom_prefix"><code langs=""><ul class="prefixed"><li class="line" prefix="*">messages
</li></ul></code></pre>
<p>The messages should say "No prior Full backup Job record found", and that the backup job started. If there are any errors, something is wrong, and they should give you a hint as to why the job did not run.</p>

<p>Another way to see the status of the job is to check the status of the Director. To do this, enter this command at the bconsole prompt:</p>
<pre class="code-pre custom_prefix"><code langs=""><ul class="prefixed"><li class="line" prefix="*">status director
</li></ul></code></pre>
<p>If everything is working properly, you should see that your job is running or terminated with an "OK" status.</p>

<h2 id="perform-restore">Perform Restore</h2>

<p>The first time you set up a new Bacula Client, you should test that the restore works properly.</p>

<p>If you want to perform a restore, use the <code>restore</code> command at the Bacula Console:</p>
<pre class="code-pre custom_prefix"><code langs=""><ul class="prefixed"><li class="line" prefix="*">restore all
</li></ul></code></pre>
<p>A selection menu will appear with many different options, which are used to identify which backup set to restore from. Since we only have a single backup, let's "Select the most recent backup"—select option 5:</p>
<pre class="code-pre "><code langs=""><div class="secondary-code-label " title="Select item (1-13):">Select item (1-13):</div>5
</code></pre>
<p>Then you must specify which Client to restore. We want to restore the remote host that we just set up, e.g. "ClientHost-fd":</p>
<pre class="code-pre "><code langs=""><div class="secondary-code-label " title="Select the Client: ClientHost-fd">Select the Client: ClientHost-fd</div>Defined Clients:
     1: BackupServer-fd
     2: ClientHost-fd
Select the Client (1-2): <span class="highlight">2</span>
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
<p>If everything worked properly, your restored files will be on your Client host, in the <code>/bacula/restore</code> directory. If you were simply testing the restore process, you should delete the contents of that directory.</p>

<h2 id="conclusion">Conclusion</h2>

<p>You now have a Bacula Server that is backing up files from a remote Bacula Client. Be sure to review and revise your configuration until you are certain that you are backing up the correct FileSets, on a schedule that meets your needs. If you are trying to create backups of CentOS 7 hosts, follow this link: <a href="https://indiareads/community/tutorials/how-to-back-up-a-centos-7-server-with-bacula">How To Back Up a CentOS 7 Server with Bacula</a>.</p>

<p>The next thing you should do is repeat the relevant sections of this tutorial for any additional Ubuntu 14.04 servers that you want to back up.</p>

    