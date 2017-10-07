<link rel='stylesheet' href='digital_ocean.css'><br> <br> 
      <h3 id="introduction">Introduction</h3>

<hr />

<p>This tutorial explains how to recover files from your Droplet after an attack.</p>

<p>Let's say that someone has gained access to your Droplet and launched an attack. Nobody wants to be in this situation.</p>

<p>But, by using the recovery environment, you can quickly transfer your important files off your compromised droplet. You can either provision a new Droplet, or transfer your files to your local computer.</p>

<h3 id="step-1-—-backing-up-databases">Step 1 — Backing Up Databases</h3>

<hr />

<p>If you are running a MySQL or MariaDB database server, you will want to create a backup of your databases before entering the recovery environment.</p>

<p>This can be done via the web console, which you can access from your IndiaReads control panel.</p>

<p>Log into your server, and use the <code>mysqldump</code> utility. <code>mysqldump</code> can be used to create a <code>.sql</code> file containing the contents of your database. You will then be able to import it easily on another server running MySQL or MariaDB.</p>

<p>There is a detailed article on how to export databases using <code>mysqldump</code> <a href="https://indiareads/community/tutorials/how-to-backup-mysql-databases-on-an-ubuntu-vps">here</a>.</p>

<p>As a quick reference, the basic syntax of the command is:</p>
<pre class="code-pre "><code langs="">mysqldump -u username -p database_to_backup > backup_name.sql
</code></pre>
<h3 id="step-2-—-requesting-the-recovery-environment">Step 2 — Requesting the Recovery Environment</h3>

<hr />

<p>Once you have your database backups, you will need to let a IndiaReads support tech know you require the recovery environment. If your Droplet has been locked, you should already have a support ticket open on your account which you can update with this request.</p>

<h3 id="step-3-mounting-your-filesystem">Step 3 - Mounting your filesystem</h3>

<hr />

<p>Once your droplet has been booted to the recovery environment you can connect to it via the web console in the control panel.  When you do you will be presented with a menu screen like the following one:<br />
<img src="https://assets.digitalocean.com/articles/recovery_ISO/menu.png" alt="" /></p>

<p>To get started you will need to mount your filesystem by entering <code>1</code> and then pressing <code>Enter</code>.  You will be returned to the menu and if the mount was successful the device name will now be displayed.</p>

<h3 id="step-4-enabling-networking">Step 4 - Enabling Networking</h3>

<hr />

<p>Since networking is not enabled by default in the recovery environment we now need to enable the network interface.  Select <code>2</code> and press <code>Enter</code> to continue.</p>

<p>If your droplet is in a region which supports <a href="https://indiareads/community/tutorials/an-introduction-to-droplet-metadata">droplet meta-data</a> your network will be automatically enabled.</p>

<p>In regions that do not yet support meta-data you will now be prompted to enter the network information displayed below the console window.  Enter the <code>IP Address</code>, <code>Gateway</code>, and <code>Netmask</code> as they are shown.</p>

<p><img src="https://assets.digitalocean.com/articles/recover_compromised/recovery_network_updated.png" alt="" /></p>

<p>After you enter these details the recovery environment will configure your network interface, set up DNS, and check that the network is now up and running.  If everything goes well you will be returned to the menu.</p>

<h3 id="step-5-starting-an-ssh-server">Step 5 - Starting an SSH Server</h3>

<hr />

<p>Now that we have our filesystem mounted and our droplet can talk to the Internet we just need to enable a service to allow us to access our files.  The recovery environment can configure and enable an SSH/SFTP server on your droplet to allow you access.  To enable the SSH server you will need to select <code>4</code> from the menu and press <code>Enter</code>.</p>

<p><img src="https://assets.digitalocean.com/articles/recovery_ISO/ssh.png" alt="" /></p>

<p>When selecting this option the ssh server components will be automatically downloaded and installed and you will be shown a temporary password and connection details you can use to reach the recovery environment's ssh/sftp service.</p>

<h3 id="step-6-connecting-via-sftp">Step 6 - Connecting via SFTP</h3>

<hr />

<p>Now that the SSH service has been enabled it can be reached using an SSH or SFTP client.  Using the SFTP client <a href="https://filezilla-project.org/">Filezilla</a> you can create a new connection with the following details substituting your droplet's IP address and the temporary password you created with the values shown:</p>
<pre class="code-pre "><code langs="">Host: <span class="highlight">your_droplets_IP</span>
Port: 22
Protocol: SFTP - SSH File Transfer Protocol
Login Type: Normal
User: root
Password: <span class="highlight">TEMPORARY_PASSWORD</span>
</code></pre>
<p><img src="https://assets.digitalocean.com/articles/recover_compromised/filezilla.png" alt="" /></p>

<p>When you connect you will start out in the directory <code>/root</code> and your droplet's filesystem will be located in <code>/mnt</code></p>

<p>More information about using Filezilla can be found <a href="https://indiareads/community/tutorials/how-to-use-filezilla-to-transfer-and-manage-files-securely-on-your-vps">here</a>.</p>

<h3 id="next-steps">Next Steps</h3>

<p>Now that you have recovered your files and database it is important to ensure that your new Droplet is secure. The following tutorial will walk you through some recommended first steps:</p>

<ul>
<li><a href="https://indiareads/community/tutorials/an-introduction-to-securing-your-linux-vps">An Introduction to Securing your Linux VPS</a></li>
</ul>

<p>We also have many other security related articles and tutorials that you can find here, many of which relate to specific software and services:</p>

<ul>
<li><a href="https://indiareads/community/tags/explore/security">Security articles</a></li>
</ul>

    