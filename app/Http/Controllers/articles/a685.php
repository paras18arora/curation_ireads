<link rel='stylesheet' href='digital_ocean.css'><br><img src=https://community-cdn-digitalocean-com.global.ssl.fastly.net/assets/tutorials/images/large/mumble-oneclick.png?1436801169/> <br> 
      <h1 id="introduction">Introduction</h1>

<p>Mumble is an open source, low-latency, high quality voice chat tool primarily intended for use while gaming. This tutorial will guide you through setting up your own mumble server using the Mumble-Server (Murmur) One-Click Application.</p>

<h1 id="step-one-create-a-mumble-server-droplet">Step One - Create a Mumble-Server Droplet</h1>

<p>To get started, navigate to your droplet's <code>create</code> page and select a hostname for your new droplet and a plan.  Mumble-Server can run on any size droplet but the larger the droplet you select, the more simultaneous users will be supported.</p>

<p><img src="http://i.imgur.com/9xrTmDB.png" alt="" /></p>

<p>Select the region where you would like to create your droplet. For the best results, select a region closest to the location where your users are located.</p>

<p><img src="http://i.imgur.com/hQW3TDs.png" alt="" /></p>

<p>Then select the <strong>Mumble-Server on 14.04</strong> image in the Applications tab.</p>

<p>If you wish to use SSH keys for your droplet you can select them here:</p>

<p><img src="https://assets.digitalocean.com/articles/drupal_one_click/sshkey.png" alt="" /></p>

<p>Now click create and your new droplet will be created for you.</p>

<h1 id="step-two-connect-to-your-mumble-server">Step Two - Connect to your Mumble-Server</h1>

<p>Once the creation of your droplet is completed it will be up and running with a default configuration.  A SuperUser password is created for you automatically and can be accessed by making an SSH connection to your droplet.  Once connected via SSH your SuperUser password will be displayed in the MOTD shown on screen.</p>

<p>These steps will help you connect to your new Mumble server as the SuperUser. Other users can connect using the same steps. Other users can pick their own usernames, and do not need a password - just the IP address and port number.</p>

<p>Download the <a href="http://www.mumble.com/mumble-download.php">Mumble client</a>.</p>

<p>Open the Mumble client on your computer.</p>

<p><img src="https://assets.digitalocean.com/articles/mumble_server_murmur/4.png" alt="" /></p>

<p>Click the <strong>Connect</strong> button.</p>

<p><img src="https://assets.digitalocean.com/articles/mumble_server_murmur/5.png" alt="" /></p>

<p>Click the <strong>Add New</strong> button.</p>

<p><img src="https://assets.digitalocean.com/articles/mumble_server_murmur/6.png" alt="" /></p>

<p>Enter the information for your Mumble server. The address can be a host name or the IP address of your server. If you did not use a custom port, it will be the default port of <span class="highlight">64738</span>. Click <strong>OK</strong> to save settings.</p>

<p><img src="https://assets.digitalocean.com/articles/mumble_server_murmur/7.png" alt="" /></p>

<p>The server will be saved to your favorites list. Click on your server then click <strong>Connect</strong>.</p>

<p>If you did not set up a signed certificate for this server, you will have to accept the certificate.</p>

<p>You should get the message <strong>Connected</strong> once you are successfully connected.</p>

<h1 id="step-three-advanced-configuration">Step Three - Advanced Configuration</h1>

<p>While the server will work with the default settings you may wish to better secure or customize your server by changing your configuration settings.</p>

<p>If you would like to customize your server even further we will need to edit the configuration file located at <code>/etc/mumble-server.ini</code>.</p>

<p>To open the file:</p>
<pre class="code-pre "><code langs="">nano /etc/mumble-server.ini
</code></pre>
<p>Murmur has several configuration options, and the file is well commented if you have any questions about what a particular setting is for.</p>

<p>There are some commonly changed values listed below as a reference. To enable some of these settings, you will have to remove the preceding <span class="highlight">#</span> character. This is referred to as un-commenting the line. Lines that start with <span class="highlight">#</span> are not processed by the server.</p>

<ul>
<li><strong>autobanAttempts</strong>, <strong>autobanTimeframe</strong>, <strong>autobanTime</strong> - These three values are used to prevent bruteforcing attempts, and will ban the IP for the specified amount of time after the other two conditions are met.</li>
<li><strong>welcometext</strong> - This is the welcome message every user receives when connecting to the server. It is useful for informing users of rules, linking to your website, etc. You can use most HTML characters and tags; just make sure the entire entry is encapsulated in quotes.</li>
<li><strong>port</strong> - The default Mumble port is <strong>64738</strong>. You can change this value, but make sure to inform your users to enter the correct port when connecting to the server manually.</li>
<li><strong>host</strong> - By default Mumble will bind to any host name automatically. If you want it to use a single host name, you enter that host name here.</li>
<li><strong>bandwidth</strong> - This is the amount of bandwidth each user is allowed to consume. Keep in mind that if you set a higher value, each user will consume more RAM. Values between 60000-72000 are good for hosting 50 users on 512 MB of RAM.</li>
<li><strong>users</strong> - The maximum number of simultaneous users that can connect to the server. For 512 MB of RAM, the recommended setting is 50 users for 60Kbps-72Kbps bandwidth per user.</li>
<li><strong>textmessagelength</strong> - Not enabled by default; will limit the number of characters a user can send per message.</li>
<li><strong>imagemessagelength</strong> - Not enabled by default; will set the maximum file size for images a user is allowed to send.</li>
<li><strong>allowhtml</strong> - Not enabled by default; allows HTML in messages, comments, and channel descriptions.</li>
<li><strong>registerName</strong>, <strong>RegisterUrl</strong> - If enabled, this will allow your server to be found on the public list available in the Mumble Client. Otherwise the user will need to know the host name or IP address of the server to be able to connect.</li>
</ul>

<p>When you are happy with your configuration changes, press <span class="highlight">Ctrl+X</span>, press <strong>Y</strong> to save, and press <strong>Enter</strong> to overwrite the existing file. You will need to restart Murmur before your settings will take effect. Any Mumble users on the server will be disconnected.</p>

<p>To restart Murmur:</p>

<p>service mumble-server restart</p>

<p>If you need help configuring other server settings, it is recommend you read the <a href="http://wiki.mumble.info/wiki/Main_Page">Mumble Wiki</a>.</p>

    