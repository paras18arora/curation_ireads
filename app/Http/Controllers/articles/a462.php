<link rel='stylesheet' href='digital_ocean.css'><br> <br> 
      <h3 id="introduction">Introduction</h3>

<p>Syncing files and directories between servers and local machines is a very common requirement when dealing with networked computers.  One method of automatically syncing the contents of directories is with a technology called <strong>BitTorrent Sync</strong>.  This software leverages the BitTorrent protocol that is commonly used for file sharing as a synchronization tool.</p>

<p>Communication through BitTorrent Sync is encrypted end-to-end based on a unique shared secret that is auto-generated.  While BitTorrent as a file sharing mechanism is a public service, the way that BitTorrent Sync uses the protocol is private, meaning that files can be transferred securely.</p>

<p>In this guide, we will demonstrate how to install and use BitTorrent Sync on two Ubuntu 14.04 servers.  We will show you how to set up your shared directories, and how to set up SSL encryption for the web interface to securely administer your servers.</p>

<h2 id="install-bittorrent-sync">Install BitTorrent Sync</h2>

<p>The first step that we need to get started is to install the BitTorrent Sync software on both of our server instances.  Many of the procedures in this guide will be mirrored across both machines, so make sure you duplicate your commands for each machine.</p>

<p>There is no official BitTorrent Sync package available in Ubuntu's default repositories.  However, there is a well-maintained PPA (personal package archive) created by Leo Moll (known as tuxpoldo) that we can use to get up-to-date packages.</p>

<p>On both of your servers, add this PPA so that our systems can pull down the packages:</p>
<pre class="code-pre "><code langs="">sudo add-apt-repository ppa:tuxpoldo/btsync
</code></pre>
<p>Now, we need to update our local package index so that our systems know about the newly available software.  We'll then install BitTorrent Sync, as well as nginx to add SSL encryption to our web interface later on:</p>
<pre class="code-pre "><code langs="">sudo apt-get update
sudo apt-get install btsync nginx
</code></pre>
<p>You will be asked quite a few questions in prompts when you attempt to install.  For now, press ENTER through all of the prompts.  We will be reconfiguring our services momentarily in a more in-depth manner.</p>

<h2 id="configure-bittorrent-sync">Configure BitTorrent Sync</h2>

<p>Now that the software is installed, we're actually going to run the configuration script that prompts us for values a second time.  This time, however, we will have access to additional options that we require for our purposes.</p>

<p>To run the script again, this time choosing our settings, type this on each server:</p>
<pre class="code-pre "><code langs="">sudo dpkg-reconfigure btsync
</code></pre>
<p>This will run you through even <em>more</em> prompts than during the initial installation.  For the most part, we will be going with the default values and you can just press ENTER.</p>

<p>Below, I've outlined the values that you <strong>need</strong> to configure:</p>

<ul>
<li><strong>Web Interface Bind IP Address</strong>: <code>127.0.0.1</code></li>
<li><strong>The username for accessing the web interface</strong>: [Choose whatever you would like.  We will keep the <code>admin</code> account in this example.]</li>
<li><strong>The password for accessing the web interface</strong>: [Choose whatever you would like.  We will be using <code>password</code> for demonstration purposes.]</li>
<li><strong>Umask value to set for the daemon</strong>: <code>002</code></li>
</ul>

<p>As you can see, for the vast majority of settings, we can accept the defaults.  The above choices though are very important.  If you mis-configure these, run the command again to correct your selections.</p>

<h2 id="configure-ssl-front-end-to-the-bittorrent-sync-web-interface">Configure SSL Front-end to the BitTorrent Sync Web Interface</h2>

<p>Now, we have BitTorrent Sync set up for the most part.  We will set up our sync directories in a bit.  But for now, we need to set up our nginx web server with SSL.</p>

<p>You may have noticed that we configured our web interface to only be available on the local loopback interface (<code>127.0.0.1</code>).  This would normally mean that we would not have access to this when running BitTorrent Sync on a remote server.</p>

<p>We restricted access like this because, although the BitTorrent Sync traffic itself is encrypted, the traffic to the web interface is transmitted in plain text.  This could allow anyone watching traffic between our server and local computer to see any communication sent between our machines.</p>

<p>We are going to set up nginx with SSL to proxy connections through SSL to our BitTorrent web interface.  This will allow us to securely administer our BitTorrent Sync instance remotely.</p>

<p>Again, we will need to do all of these steps on both of our hosts.</p>

<h3 id="generate-the-ssl-certificate-and-key">Generate the SSL Certificate and Key</h3>

<p>The first step towards getting this set up is to create a directory to hold our SSL certificate and key.  We'll do this under the nginx configuration directory hierarchy:</p>
<pre class="code-pre "><code langs="">sudo mkdir /etc/nginx/ssl
</code></pre>
<p>Now, we can create our SSL certificate and key in a single motion by issuing this command:</p>
<pre class="code-pre "><code langs="">sudo openssl req -x509 -nodes -days 365 -newkey rsa:2048 -keyout /etc/nginx/ssl/nginx.key -out /etc/nginx/ssl/nginx.crt
</code></pre>
<p>You will be asked to fill out some information for your certificate.  Fill out the fields as best as you can.  The only one that <em>really</em> matters is this one:</p>
<pre class="code-pre "><code langs="">Common Name (e.g. server FQDN or YOUR name) []:
</code></pre>
<p>In this field, enter your server's domain name or public IP address.</p>

<h3 id="configure-nginx-to-encrypt-traffic-with-ssl-and-pass-to-bittorrent-sync">Configure Nginx to Encrypt Traffic with SSL and Pass to BitTorrent Sync</h3>

<p>Now, we can configure our nginx server blocks to use our SSL certificates when communicating with remote clients.  It will then the information to our BitTorrent Sync web interface listening on the local interface.</p>

<p>We will leave the default nginx server block file intact in case you need to use this in the future.  Since BitTorrent Sync operates on port "8888" by default, we will use this as the front-end SSL port as well.</p>

<p>Create a new server block file by opening a new file with sudo privileges in your editor:</p>
<pre class="code-pre "><code langs="">sudo nano /etc/nginx/sites-available/btsync
</code></pre>
<p>Inside, we need the to add the following lines:</p>

<pre>
server {
    listen <span class="highlight">server_domain_or_IP</span>:8888 ssl;
    server_name <span class="highlight">server_domain_or_IP</span>;

    access_log /var/log/nginx/access.log;

    ssl_certificate /etc/nginx/ssl/nginx.crt;
    ssl_certificate_key /etc/nginx/ssl/nginx.key;

    location / {
        proxy_pass http://127.0.0.1:8888;
    }
}
</pre>

<p>Make sure you change the red text to your server's domain name or public IP address.  This will tell nginx to bind to the same port that the BitTorrent Sync web interface is using on the local interface. The difference is that nginx will use the public address and require SSL.</p>

<p>It will use the SSL certificate that we created to encrypt the traffic to the client.  It will then pass it to the BitTorrent Sync interface.  In this way, the traffic between the server and the client will be encrypted, but the BitTorrent Sync interface will operate as if we were accessing it from the server itself.</p>

<p>When you are finished, save and close the file.</p>

<p>Now, we just need to link the file so that it will be enabled:</p>
<pre class="code-pre "><code langs="">sudo ln -s /etc/nginx/sites-available/btsync /etc/nginx/sites-enabled/
</code></pre>
<p>We can now restart the service to implement our changes:</p>
<pre class="code-pre "><code langs="">sudo service nginx restart
</code></pre>
<p>Make sure you go through these procedures on each of your two servers.</p>

<h2 id="create-a-shared-directory">Create a Shared Directory</h2>

<p>We now have BitTorrent Sync configured, and have set up SSL and nginx to encrypt our sessions with the web interface.</p>

<p>Before we begin to use the web interface, we should set up the directories that we want to sync.  Because of the way that BitTorrent Sync creates files that it has mirrored from a remote host, our configuration for this portion is pretty important.</p>

<p>First, in this guide, we will be syncing directories located at <code>/shared</code> on both servers.  Let's create these directories now:</p>
<pre class="code-pre "><code langs="">sudo mkdir /shared
</code></pre>
<p>Once you have the directory, we are going to give our root account user ownership over the directory.  At the same time, we will give the "btsync" group (this was created during the installation) group ownership of the directory:</p>
<pre class="code-pre "><code langs="">sudo chown root:btsync /shared
</code></pre>
<p>There are many different ways you can configure this access, each with implications.  We are demonstrating a fairly flexible system here that will minimize the permissions and ownership conflicts.  To find out other alternatives, and their trade-offs, check out the shared folders configuration of <a href="https://digitalocean.com/community/articles/how-to-use-bittorrent-sync-to-synchronize-directories-in-ubuntu-12-04#HowToConfigureSharedFolders">this article</a>.</p>

<p>After we assign ownership, we should adjust permissions.  We will set the <code>setgid</code> bit on the directory so that the <code>btsync</code> group will be given group ownership to any files created in the directory.  To make this work correctly, we'll also need to give the group write permissions:</p>
<pre class="code-pre "><code langs="">sudo chmod 2775 /shared
</code></pre>
<p>Finally, since our regular system account is not the user owner or group owner of the directory, we will need to add our regular account to the <code>btsync</code> group.  This will allow us to access and interact with the content in this directory as our regular user:</p>

<pre>
sudo usermod -a -G btsync <span class="highlight">your_user</span>
</pre>

<p><strong>Note</strong>: At this point, you must log out and log back in for these changes to register in your current environment.  Exit by typing:</p>
<pre class="code-pre "><code langs="">exit
</code></pre>
<p>Now log back in.</p>

<h2 id="access-the-bittorrent-sync-web-interface">Access the BitTorrent Sync Web Interface</h2>

<p>Now that we have everything set up, we can begin taking a look at the administrative web interface to pull the pieces together.</p>

<p>To begin, you will need to access both servers in a web browser on port "8888" using the "https" protocol.  This should look something like this:</p>

<pre>
https://<span class="highlight">server_domain_or_IP</span>:8888
</pre>

<p>You will most likely see a warning displayed that looks like this:</p>

<p><img src="https://assets.digitalocean.com/articles/bittorrent_sync_1404/ssl_warning.png" alt="SSL non-trust warning" /></p>

<p>This is only a warning telling you that your browser does not recognize the party that signed your SSL certificate.  Since we generated self-signed SSL certificates, this makes perfect sense and is expected, and we can safely click "Proceed anyways" or whatever similar button your browser gives you.</p>

<p>You will be prompted for the username and password that you selected while configuring BitTorrent Sync.  In our example, the credentials were <code>admin</code> and <code>password</code>, but yours (especially the password) may be different.</p>

<p>Once you authenticate, you should see the main BitTorrent Sync Web interface:</p>

<p><img src="https://assets.digitalocean.com/articles/bittorrent_sync_1404/main_interface.png" alt="BitTorrent Sync main web interface" /></p>

<h3 id="add-the-shared-directory-to-your-first-server">Add the Shared Directory to your First Server</h3>

<p>We can not begin to add the directory we configured to the web interface.</p>

<p>Click on the "Add Folder" button in the upper-right corner.  You will be given a dialog box for adding a directory to the BitTorrent Sync interface:</p>

<p><img src="https://assets.digitalocean.com/articles/bittorrent_sync_1404/add_folder.png" alt="Add folder interface" /></p>

<p>Scroll to the <code>/shared</code> directory that we created and click on it.  It should populate the "Path" field with the correct value.</p>

<p>Next to the "Secret" field, click on the "Generate" button to create a secret key for the directory:</p>

<p><img src="https://assets.digitalocean.com/articles/bittorrent_sync_1404/gen_secret.png" alt="Generate secret" /></p>

<p>Click on the "Add" button in the lower-right corner.  Your directory will be added to the BitTorrent Sync web UI.</p>

<p>Now, we have a new button available.  Click on the "Secret/QR" button associated with the <code>/shared</code> directory that you just added:</p>

<p><img src="https://assets.digitalocean.com/articles/bittorrent_sync_1404/secret_button.png" alt="secret button" /></p>

<p>You will be presented with a dialog box that gives you the secret for this directory.  This is the way to sync this directory with another instance of BitTorrent Sync.</p>

<p>The software allows you to set up full access to the directory (read and write access), or read-only access.  For our guide, we will be configuring full access to allow two-way syncing, but this is simply a preference.</p>

<p>You will need to copy the "Full access" secret from this interface to set up the syncing with your second server.</p>

<h3 id="add-the-shared-directory-and-secret-to-the-second-server">Add the Shared Directory and Secret to the Second Server</h3>

<p>Now that we have the first server configured to share its directory, we need to set up our second server.</p>

<p>We will go through most of the same steps, with some slight variations.</p>

<p>Once again, sign into the web interface, this time, using the second server's domain name or IP address.  Remember to use "https" and port "8888":</p>

<pre>
https://<span class="highlight">second_server_domain_or_IP</span>:8888
</pre>

<p>You will see the SSL warning again, and you will need to authenticate.  You will come to the same empty interface that we saw before.</p>

<p>Click on the "Add Folder" button, as we did before.  Select the <code>/shared</code> directory that we created.</p>

<p>At this point, instead of <em>generating</em> a new secret, we want to use the secret that was generated on the first server.  This will allow these two instances to communicate, as each secret is unique and randomly generated.  Enter the secret from the first server:</p>

<p><img src="https://assets.digitalocean.com/articles/bittorrent_sync_1404/add_first_secret.png" alt="add secret from first" /></p>

<p>Click on the "Add" button in the lower right corner when you are finished.</p>

<p>In a few moments, the "Connected devices and status" column in the main interface will populate with the information about the companion server:</p>

<p><img src="https://assets.digitalocean.com/articles/bittorrent_sync_1404/connected_device.png" alt="connected device display" /></p>

<p>This means that your servers are communicating with each other and can sync content.</p>

<h2 id="test-bittorrent-syncing">Test BitTorrent Syncing</h2>

<p>Let's test our current setup.</p>

<p>On either of your servers (it does not matter which one if you configured full access), move into the <code>/shared</code> directory:</p>
<pre class="code-pre "><code langs="">cd /shared
</code></pre>
<p>We can will create 10 sample files by typing:</p>
<pre class="code-pre "><code langs="">touch file{1..10}
</code></pre>
<p>After a moment, on your other server, you should be able to see the files you created:</p>
<pre class="code-pre "><code langs=""># On the second server
cd /shared
ls -l
</code></pre>
<hr />
<pre class="code-pre "><code langs="">total 0
-rw-rw-r-- 1 btsync btsync 0 May 19 17:07 file1
-rw-rw-r-- 1 btsync btsync 0 May 19 17:07 file10
-rw-rw-r-- 1 btsync btsync 0 May 19 17:07 file2
-rw-rw-r-- 1 btsync btsync 0 May 19 17:07 file3
. . .
</code></pre>
<p>As you can see, our files were synced over.  If you look at the web interface though, this sync has not registered.  This is because these files don't contain any actual data.</p>

<p>We will test whether it can detect when we transfer files with content by writing data to those files from our second server.  This will also allow us to test that we can sync changes back to the first server.</p>

<p>On the second server, you can write the phrase "some content" to each of the files you created by typing:</p>
<pre class="code-pre "><code langs="">for item in /shared/file{1..10}; do echo "some content" > $item; done
</code></pre>
<p>After a few seconds, the files on the first server should show the content you added:</p>
<pre class="code-pre "><code langs=""># On first server
cat /shared/file1
</code></pre>
<hr />
<pre class="code-pre "><code langs="">some content
</code></pre>
<p>You should also see that the web interface has also been updated to reflect the number of files and the amount of space that has been synced across the servers:</p>

<p><img src="https://assets.digitalocean.com/articles/bittorrent_sync_1404/size_info.png" alt="BitTorrent Sync size info" /></p>

<p>If this is working, you have successfully configured BitTorrent Sync to mirror your changes between servers.</p>

<h2 id="conclusion">Conclusion</h2>

<p>You should now have a flexible setup that allows you to securely transfer files between remote servers.  Furthermore, this configuration allows you to administer the service through a secure connection by leveraging SSL.</p>

<p>The application itself is quite flexible and can be used in a variety of ways.  Some useful features are the ability to scan secrets as QR codes on your mobile device, the ability to configure read-only access to content, and the ability to provide clients with one-time use secrets.  You can also configure your servers to only communicate with certain hosts.</p>

<p>The BitTorrent Sync service also provides a simple version control system, which utilizes a hidden <code>./SyncArchive</code> directory in shared directory to keep old versions of files.  You can also implement restrictions like rate limiting if you want to make sure that your files are synced without affecting other services.</p>

<div class="author">By Justin Ellingwood</div>

    