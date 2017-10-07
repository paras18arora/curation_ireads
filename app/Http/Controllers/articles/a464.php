<link rel='stylesheet' href='digital_ocean.css'><br><img src=https://community-cdn-digitalocean-com.global.ssl.fastly.net/assets/tutorials/images/large/01212014BitTorrentSync_twitter.png?1426699634/> <br> 
      <h3 id="introduction">Introduction</h3>

<hr />

<p>Syncing folders and files between computers and devices can be done in many different ways.  One method for automatically syncing content is <strong>BitTorrent Sync</strong>.  BitTorrent Sync is a method of synchronizing content based on the popular BitTorrent protocol for file sharing.</p>

<p>Unlike traditional BitTorrent, files shared using BitTorrent Sync are encrypted and access is restricted based on a shared secret that is auto-generated.  While BitTorrent proper is often used to distribute files in a public way, BitTorrent Sync is often used as a private method to sync and share files between devices due to its added security measures.</p>

<p>In this guide, we will discuss how to install and configure BitTorrent Sync on two Ubuntu 12.04 VPS instances.</p>

<h2 id="install-bittorrent-sync">Install BitTorrent Sync</h2>

<hr />

<p>To begin, we will need to install BitTorrent Sync on both of our Ubuntu 12.04 instances.  If you would like to install BitTorrent Sync on your local computer to allow you to sync with your server, you can find the binary packages <a href="http://www.bittorrent.com/sync/downloads">here</a>.</p>

<p>BitTorrent Sync is relatively easy to install on Ubuntu 12.04, but it is not included in the default repositories.  We can use a PPA (personal package archive) so that we can have access to a maintained BitTorrent Sync repository and manage it with our normal apt tools.</p>

<p>Ubuntu 12.04 includes the PPA tools in a package called <code>python-software-properties</code>, which we can download through apt:</p>
<pre class="code-pre "><code langs="">sudo apt-get update
sudo apt-get install python-software-properties
</code></pre>
<p>After this is installed, we can add the PPA that contains updated Ubuntu packages:</p>
<pre class="code-pre "><code langs="">sudo add-apt-repository ppa:tuxpoldo/btsync
</code></pre>
<p>Press "enter" to add the new PPA.</p>

<p>Once the new repository is added, we should update apt to build a package index for the new source, and then install the BitTorrent Sync software:</p>
<pre class="code-pre "><code langs="">sudo apt-get update
sudo apt-get install btsync
</code></pre>
<h3 id="initial-configuration-during-installation">Initial Configuration During Installation</h3>

<hr />

<p>During the installation phase, you will be asked a number of questions that can assist you in configuring the service.  The first question asks if you'd like to do this configuration to define a default BitTorrent Sync instance.  Select "Yes".</p>

<p>We want operate BitTorrent Sync with its own user and group for security purposes.  Select <code>btsync</code> for the next question.</p>

<p>The next question will be about the port you wish to use to communicate between instances.  You can leave the selection at <code>0</code> to have btsync choose a random port each time it starts.  If you are configuring a firewall for your server (which is highly recommended), you probably want to define a specific port.</p>

<p>The next question asks about configuring a UPNP request, which we don't need.  Select "No".</p>

<p>Next, define your download and upload limits.  If you do not wish to limit either of these, leave the default of <code>0</code> to allow maximum throughput.</p>

<p>Next, you'll be asked which interface you wish to configure the service for.  If you leave it at <code>0.0.0.0</code>, the BitTorrent Sync service will use any available interface.  If you wish to restrict it to one network, such as the IndiaReads private network, you can specify the appropriate IP address here.  Note that you will not be able to sync to your home computer using the private network.</p>

<p>Next, select a port to access the web interface.  The default value is <code>8888</code>, but you can change that to any open port.</p>

<p>Finally, select a username and password to secure the web interface.</p>

<p>The installation will complete and your service will be started.</p>

<p>If you need to change the configuration at some point in the future, you can run through the configuration menus at any time by issuing:</p>
<pre class="code-pre "><code langs="">sudo dpkg-reconfigure btsync
</code></pre>
<p>The configuration directory for the service is:</p>
<pre class="code-pre "><code langs="">/etc/btsync
</code></pre>
<p>Do not edit the config file generated by the menu system by hand.  You can, however, copy the configuration to use as a template for another configuration if you'd like to adjust details not covered in the menu configuration.</p>

<h2 id="how-to-configure-shared-folders">How To Configure Shared Folders</h2>

<hr />

<p>In order to sync folders with BitTorrent Sync, the <code>btsync</code> user or group needs write access to the folders.  There are a few different ways to achieve this.</p>

<p>First, let's create the folder:</p>
<pre class="code-pre "><code langs="">sudo mkdir /shared
</code></pre>
<p>We need to complete these steps on both of your VPS instances that will be syncing.</p>

<h3 id="giving-the-btsync-process-full-ownership">Giving the btsync Process Full Ownership</h3>

<hr />

<p>One way to give the btsync user access is to simply give ownership of the folder to the btsync user:</p>
<pre class="code-pre "><code langs="">sudo chown btsync:btsync /shared
</code></pre>
<p>This will allow the BitTorrent Sync service to correctly serve the contents of this directory, but we are not able to write to this as a normal user.  This may be what you want, but it usually is not.</p>

<h3 id="give-your-normal-user-ownership-and-the-btsync-process-group-ownership">Give Your Normal User Ownership and the btsync Process Group Ownership</h3>

<hr />

<p>If you have only one normal user on the system, you can give that user ownership of the folder and give the btsync group ownership of the folder:</p>

<pre>
sudo chown <span class="highlight">your_user</span>:btsync /shared
</pre>

<p>You would then have to give the group write permissions:</p>
<pre class="code-pre "><code langs="">sudo chmod 775 /shared
</code></pre>
<p>This will allow the btsync service to access the folder.  However, any files created inside the directory will be owned by your user and group.</p>

<p>For instance, if we add a file to this folder called <code>test</code>, it would be completely owned by our user:</p>

<pre>
cd /shared
touch test
ls -l
</pre>

<pre>
-rw-rw-r-- 1 <span class="highlight">your_user your_user</span> 6 Jan 16 14:36 test
</pre>

<p>This will cause problems for the syncing, because the btsync process cannot modify the file.  We want to give it the same group permissions as the folder it is in so that the process has write access.</p>

<p>We can do this by setting the SGID bit on the directory.  This will set the group on all new files created inside of the directory to the group of the directory itself.  This will allow proper write access to modify things:</p>
<pre class="code-pre "><code langs="">sudo chmod g+s /shared
</code></pre>
<p>Now, when we create a file, it will be given the permissions of the directory:</p>

<pre>
cd /shared
touch test2
ls -l
</pre>

<pre>
-rw-rw-r-- 1 your_user your_user 6 Jan 16 14:36 test
-rw-rw-r-- 1 <span class="highlight">your_user btsync</span> 0 Jan 16 14:41 test2
</pre>

<p>This goes a long way towards getting the appropriate functionality, but it isn't quite right yet.</p>

<p>Delete the test files we created before continuing:</p>
<pre class="code-pre "><code langs="">rm /shared/test*
</code></pre>
<h3 id="add-your-user-to-the-btsync-group-and-give-the-root-user-ownership">Add Your User to the btsync Group and Give the Root User Ownership</h3>

<hr />

<p>The method above works somewhat, but files that are transferred with BitTorrent Sync are owned by the btsync user and group.  This means that currently, any files synced by the service will not be editable by us.</p>

<p>We can change this by adding our user to the btsync group.  This will allow us to modify files that are writeable by the btsync group, which is what we want.</p>

<p>Add any username that you wish to be able to use btsync to the btsync group:</p>

<pre>
sudo usermod -a -G btsync <span class="highlight">your_user</span>
</pre>

<p>This will append the btsync group to your user's group definition.  This will allow you to edit files created in the shared folder by the btsync process.</p>

<p>However, the directory is still owned by our user, which is not a good way of going about things if we have multiple users on the system.  We should transfer ownership to the root user to avoid regular users changing folder settings.  We should also allow group write permissions so that <em>anyone</em> in the btsync group can add content:</p>
<pre class="code-pre "><code langs="">sudo chown root:btsync /shared
sudo chmod g+w /shared
</code></pre>
<p>You may have to log out and log back in for these changes to take affect.</p>

<p>In the end, the process for creating a shared folder that works well for BitTorrent Sync goes something like this:</p>

<pre>
sudo mkdir <span class="highlight">shared_folder</span>
sudo chown root:btsync <span class="highlight">shared_folder</span>
sudo chmod 2775 <span class="highlight">shared_folder</span>
sudo usermod -a -G btsync <span class="highlight">your_user</span>
</pre>

<p>The first "2" in the <code>chmod</code> command sets the SGID bit in the same way that the "g+s" did previously.  This is just a more succinct way of combining these commands.</p>

<h2 id="accessing-the-bittorrent-sync-web-interface">Accessing the BitTorrent Sync Web Interface</h2>

<hr />

<p>Now that we have a folder that is configured appropriately for BitTorrent Sync sharing, we can access the web interface to add our folder to begin syncing.</p>

<p>Again, we will have to do this on each of the servers that we wish to configure syncing on.</p>

<p>Access the web interface by going to your droplet's IP address, followed by the port you configured during install.  By default, this is <code>8888</code>:</p>

<pre>
<span class="highlight">your_ip_or_domain</span>:8888
</pre>

<p><img src="https://assets.digitalocean.com/articles/btsync/login.png" alt="BitTorrent Sync login" /></p>

<p>You will have to sign in using the credentials you configured during installation.  The default username is <code>admin</code> if you didn't change it.</p>

<p>You will be presented with a rather simple interface to start:</p>

<p><img src="https://assets.digitalocean.com/articles/btsync/main.png" alt="BitTorrent Sync main page" /></p>

<h3 id="adding-the-shared-folder-to-the-first-droplet">Adding the Shared Folder to the First Droplet</h3>

<hr />

<p>Now that we are in our web interface, we can add our shared folder so that the btsync process can register it.</p>

<p>On your first machine, click on the "Add Folder" button in the upper-right corner.  This will bring up a box that allows you to select a directory to share:</p>

<p><img src="https://assets.digitalocean.com/articles/btsync/add_folder.png" alt="BitTorrent Sync add folder" /></p>

<p>Find the folder that you configured for sharing.  In our case, this is the <code>/shared</code> folder.  Once you have a folder selected, you should click on the "Generate" button to generate a secret for the folder.</p>

<p><img src="https://assets.digitalocean.com/articles/btsync/generate.png" alt="BitTorrent Sync generate secret" /></p>

<p>The secret that is generated allows you to sync this folder with another instance of BitTorrent Sync.  This unique value is basically a password to allow the two services to connect to each other.</p>

<p>Click the "Add" button when you have completed these steps.  This will add our folder to the interface and give you some buttons on the side to manage this folder.</p>

<p><img src="https://assets.digitalocean.com/articles/btsync/buttons.png" alt="BitTorrent Sync buttons" /></p>

<p>Right now, we are only interested in the "Secret/QR" button.  Click this to bring up a box that will allow you to choose how you want to share this folder.</p>

<p>We can grant access to the folder with read/write permissions through "Full access".  If we only want to sync one way, like a backup, we can allow only read access.  The secrets that are provided for each kind of access differ.</p>

<p>Copy the secret for the type of access you want.  We will be using full access in this tutorial.</p>

<h3 id="adding-the-shared-folder-and-secret-to-the-second-droplet">Adding the Shared Folder and Secret to the Second Droplet</h3>

<hr />

<p>Now that we have our secret from our first VPS, we can add the shared folder that we created on our second VPS and use the secret to sync our files.</p>

<p>First, you must log into the web interface just like you did with the first server:</p>

<pre>
<span class="highlight">second_ip_or_domain</span>:8888
</pre>

<p>Once you are to the interface for the second server, click the "Add Folder" button again.</p>

<p>Add the locally created shared folder.</p>

<p>This time, instead of clicking the "Generate" button, we will paste the secret from the other instance into the "Secret" box:</p>

<p><img src="https://assets.digitalocean.com/articles/btsync/paste.png" alt="BitTorrent Sync pasted secret" /></p>

<p>Click the "Add" button to create the share.</p>

<p>After a moment, in both web interfaces, you should see some new information in the "Connected devices and status" section:</p>

<p><img src="https://assets.digitalocean.com/articles/btsync/connected.png" alt="BitTorrent Sync connected devices" /></p>

<p>This means that our two instances of BitTorrent Sync have found each other!  The icon in front means that we have given full access and files will be synced in both directions.</p>

<h2 id="test-syncing">Test Syncing</h2>

<hr />

<p>Now that we have configured syncing, let's test to see if it works.</p>

<p>On one of your servers (it doesn't matter which one if you configured full access), we will add some files to our shared folder.</p>

<p>As a user that has been given access to the btsync group, create some files in the shared directory:</p>
<pre class="code-pre "><code langs="">cd /shared
touch file {1..10}
</code></pre>
<p>This will create 10 files in the shared directory. We can check that these have been given the appropriate permissions by typing:</p>
<pre class="code-pre "><code langs="">ls -l
</code></pre>
<hr />
<pre class="code-pre "><code langs="">-rw-rw-r-- 1 your_user btsync 0 Jan 16 16:16 file1
-rw-rw-r-- 1 your_user btsync 0 Jan 16 16:16 file10
-rw-rw-r-- 1 your_user btsync 0 Jan 16 16:16 file2
-rw-rw-r-- 1 your_user btsync 0 Jan 16 16:16 file3
-rw-rw-r-- 1 your_user btsync 0 Jan 16 16:16 file4
. . .
</code></pre>
<p>As you can see, the files are owned by your user, but the group owner is btsync.  This is exactly what we want.</p>

<p>If we check our other server after a few seconds, we should see our files in our shared directory!</p>
<pre class="code-pre "><code langs="">cd /shared
ls -l
</code></pre>
<hr />
<pre class="code-pre "><code langs="">-rw-r--r-- 1 btsync btsync 0 Jan 16 16:16 file1
-rw-r--r-- 1 btsync btsync 0 Jan 16 16:16 file10
-rw-r--r-- 1 btsync btsync 0 Jan 16 16:16 file2
-rw-r--r-- 1 btsync btsync 0 Jan 16 16:16 file3
-rw-r--r-- 1 btsync btsync 0 Jan 16 16:16 file4
</code></pre>
<p>As you can see, the files are given to the btuser and group.  This is because the service can't be sure that the original username exists on the second system.</p>

<p>The last step is to get the btsync daemon to automatically set the file permissions of the files that it syncs to be writeable by the btsync group.  This is necessary if you are providing full access in order for your user to edit the files that it has synced.</p>

<p>We can do this in by reconfiguring the btsync daemon.  This will open up a lot more options than we were given when we originally went through the configuration.  Begin to reconfigure on both of your syncing machines by typing:</p>

<pre>
sudo dpkg-reconfigure btsync
</pre>

<p>You will run through the configuration menu, this time with many more questions.  For the most part, it should automatically select either your previous selection, or the default choice for any previously unset parameters.  One question that you'll have to remember not to skip is the password prompt.</p>

<p>The option that you are looking is the default <code>umask</code> for files synced by the daemon.</p>

<p>We can set the appropriate umask to create files that are writeable by both the owner and the group (which our user is a part of), by typing this:</p>

<pre>
002
</pre>

<p>Finish up the configuration and the daemon should restart automatically with the new settings.  Once you have completed this task on both servers, you should be able to create a new file on one server, and it will be given correct writeable permissions on the second host:</p>

<pre>
touch /shared/write_test
</pre>

<p>On the second host once the file syncs, you'll see somethings like this:</p>

<pre>
-rw-rw-r-- 1 btsync btsync  0 Jan 30 10:44 write_test
</pre>

<p>In the web interface, you will not see that your files have been synced, because the files don't contain any actual data.  If we add some content to our files, the interface will update to show how many files we have synced:</p>
<pre class="code-pre "><code langs="">for item in /shared/file{1..10}; do echo "some content" > $item; done
</code></pre>
<p><img src="https://assets.digitalocean.com/articles/btsync/file_size.png" alt="BitTorrent Sync file size" /></p>

<h2 id="conclusion">Conclusion</h2>

<hr />

<p>Now that you have your servers configured, you can easily transfer files between them.  You can also configure multiple folders to automatically sync.  This can provide some interesting options for dealing with configuration files and such.</p>

<p>The application is fairly flexible in how to sync between multiple computers.  You can create one-time secrets to ensure that no one shares access to your directories, you can share only with specific hosts, and sync between your mobile device.  BitTorrent Sync provides an archive version control system through the <code>.SyncArchive</code> file in directories and can rate limit to ensure that you have bandwidth available for other applications.</p>

<div class="author">By Justin Ellingwood</div>

    