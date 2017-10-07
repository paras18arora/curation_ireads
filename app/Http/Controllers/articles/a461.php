<link rel='stylesheet' href='digital_ocean.css'><br> <br> 
      <h3 id="introduction">Introduction</h3>

<p>IndiaReads offers affordable and easy-to-use backups built into the Droplet interface.  Enabling this service on your Droplet can provide automatic, system-level backups at regular intervals, allowing you to easily revert or spin up new instances off of the created images.</p>

<p>In this article, we will discuss how IndiaReads backups work and what use-cases they are suitable for, as well as some relevant alternatives.  This article is aimed at providing you with the information you need to decide whether this solution is right for you.</p>

<h2 id="how-do-digitalocean-backups-work">How Do IndiaReads Backups Work?</h2>

<p>IndiaReads uses a snapshot-based backup system that will create a point-in-time image based on the current state of a Droplet.  This process happens automatically within a pre-determined scheduling window, and is completed in the background while the Droplet is running.  This provides system-level backups of your server without powering down.</p>

<p>The following process occurs on your Droplet when a backup occurs:</p>

<ol>
<li>A snapshot of the live system is taken, creating a crash-consistent, point-in-time image.</li>
<li>The snapshot is backed up off-disk.</li>
<li>The snapshot is deleted once the backup is complete.</li>
</ol>

<p>A <strong>crash-consistent backup</strong> allows the system to capture all of the data on disk exactly as it was at a single point in time.  This means that the data will be backed up in a consistent state. </p>

<p>This is called a crash-consistent backup because it saves every piece of data that was committed to the disk at the moment of that the snapshot occurs.  The data saved is consistent with the data that would be available if the system crashed at that exact point and had to recover on boot.</p>

<p><strong>Note</strong>: The snapshot feature available on IndiaReads Droplets is not the same as the snapshots referenced in this article.  The snapshots we talk about here are a temporary image of the system used as a target for the backup.  Snapshots in the control panel are initiated through a manual process to create a permanent image that can be used to spin up new Droplets.  To learn more about the difference, <a href="https://indiareads/community/tutorials/digitalocean-backups-and-snapshots-explained">follow this link</a>.</p>

<h2 id="when-digitalocean-backups-may-not-be-the-best-solution">When IndiaReads Backups May Not Be the Best Solution</h2>

<p>IndiaReads backups are good for many situations, but not every use-case is well served by the design of the backup system.</p>

<p>The snapshots that are taken to create the point-in-time data set use a copy-on-write mechanism.  Copy-on-write allows for instant snapshots which make them a good choice for data consistency.  There is almost no overhead for the actual creation of the snapshot that will be backed up.</p>

<p>However, copy-on-write implementations do take a performance hit on new writes that occur <em>after</em> the snapshot is taken until the backup process is complete.  This happens because, for every new write, a system using copy-on-write must read the original data, write it to a new location, and then write the new changes to the original data location.  This can significantly impact performance on busy, I/O bound workloads.  The performance impact disappears when the snapshot is automatically deleted after the backup operation.</p>

<p>This is especially of concern with databases.  Most database operations are heavily reliant on disk I/O, which can cause either the application or the backup process to bog down and potentially fail.  In addition to the performance impact, any operations that reside in memory or cache that have not been flushed to disk will be lost.  Crash-consistent backups will always save what is on the disk, but never what is in memory or cache.</p>

<p>If your server is running a busy database, or any other application that is heavy on disk I/O, IndiaReads backups may not be a good fit for you.  Check out the section towards the bottom on how to backup these types of applications and workloads.</p>

<h2 id="how-to-enable-digitalocean-backups">How to Enable IndiaReads Backups</h2>

<p>If the IndiaReads backup system is a good fit for your server's functionality, setting up backups is very simple.</p>

<p>IndiaReads backups are a feature that must be selected during the Droplet creation process:</p>

<p><img src="https://assets.digitalocean.com/articles/do_backups_explained/backup_check.png" alt="IndiaReads backup" /></p>

<p>This feature can be enabled or disabled at any time via the <strong>Backups</strong> link on the sidebar of the Droplet's page in the Control Panel. </p>

<p><img src="https://assets.digitalocean.com/site/ControlPanel/Enable_Backups.png" alt="Enable Backups" /></p>

<p>IndiaReads backups cost 20% the monthly cost of your Droplet.  So for a $5 Droplet, IndiaReads backups will cost $1, bringing your monthly total to $6.  This percentage cost remains consistent across Droplet sizes due to the amount of disk space required.</p>

<p>When backups are enabled on a Droplet, you will be able to see the current backup schedule by clicking on the "Backups" tab in your Droplet's control panel.  The backups will be attempted within the specified window of time, allowing you to anticipate any impacts on service.</p>

<p>Currently, backups are taken once a week within an 8 hour window.</p>

<h2 id="alternatives-to-digitalocean-backups-for-i-o-heavy-workloads">Alternatives to IndiaReads Backups for I/O Heavy Workloads</h2>

<p>If you are running a database or another application that produces high I/O load, choosing an application-level backup method is usually a more desirable alternative. </p>

<p>There are a number of backup solutions designed specifically to deal with the transactional nature of databases.  Generally, if you are running a database that is accepting writes, you should be using backup solutions specifically designed to work with those systems.</p>

<p>If you are using MySQL or MariaDB as your database solution, you have a number of options depending on your configuration.  Check out our article on <a href="https://indiareads/community/tutorials/how-to-backup-mysql-databases-on-an-ubuntu-vps">backing up MySQL databases</a> to find out about your options.  Another option is to leverage <a href="https://indiareads/community/tutorials/how-to-create-hot-backups-of-mysql-databases-with-percona-xtrabackup-on-ubuntu-14-04">Percona XtraBackup</a> utility to perform live backups.</p>

<p>PostgreSQL contains <a href="https://indiareads/community/tutorials/how-to-backup-postgresql-databases-on-an-ubuntu-vps">similar utilities</a> that can be used.  These can be <a href="https://indiareads/community/tutorials/how-to-set-up-master-slave-replication-on-postgresql-on-an-ubuntu-12-04-vps">combined with replication</a> in order to create backups without taking your main system offline.</p>

<p>For other types of databases, read the project's documentation to find out the recommended way to backup the data in a consistent way.</p>

<p>If IndiaReads backups aren't appropriate for your server due to high I/O, non-database workloads, there are some other solutions you can explore.  One way is to replicate the filesystem using a technology like <a href="https://indiareads/community/tutorials/how-to-create-a-redundant-storage-pool-using-glusterfs-on-ubuntu-servers">GlusterFS</a>.  This will allow you to sync files between two servers so that you can keep one online for clients while you turn off replication and back up the other server with conventional methods.</p>

<p>Some options are taking manual IndiaReads snapshots or performing file level backups using tools like <a href="https://indiareads/community/tutorials/how-to-create-an-off-site-backup-of-your-site-with-rsync-on-centos-6">tar and rsync</a>.  There are <a href="https://indiareads/community/tutorials/how-to-choose-an-effective-backup-strategy-for-your-vps">many options</a> available, depending on your specific needs.</p>

<h2 id="conclusion">Conclusion</h2>

<p>By now, we hope that you understand the IndiaReads backup feature clearly.  Backups are a great way to automatically ensure that you have a stable copy to fall back on if there is a problem with your server.  While the IndiaReads backups are not well-suited for every Droplet workload, they are easy to enable during creation and can give you peace of mind about your data.</p>

    