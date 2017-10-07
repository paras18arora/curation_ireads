<link rel='stylesheet' href='digital_ocean.css'><br> <br> 
      <div><h2>Securely Configure a Production MongoDB Server</h2>

<p>If MongoDB is your document store of choice, then this article should help you configure everything securely and properly for a production-ready environment.</p>

<p><a href="https://indiareads/community/articles/how-to-install-mongodb-on-ubuntu-12-04" target="_blank">The MongoDB Installation Tutorial</a> covers how to install MongoDB on a droplet.</p>

<p>As always, please read the official documentation on <a href="http://www.mongodb.org/display/DOCS/Security+and+Authentication" target="_blank">Security and Authentication</a>.</p>

<h3>Steps</h3>

<p>There are two differently recommended paths that are available. The first is to connect securely to your database through an SSH tunnel. The alternative is to allow connections to your database over the internet. Of the two choices, the former is recommended.</p>

<h3>Connect Over SSH Tunnel</h3>

<p>By connecting to your Mongo VIrtual Private Server through an SSH tunnel, you can avoid a lot of potential security issues. The caveat is that your VPS must otherwise be totally locked down with few to no other ports open. A recommended SSH configuration is key-only or key+password.</p>

<p>To setup an SSH tunnel, you'll need to ensure that:</p>

<ul><li>You can SSH into your Mongo Droplet</li>
<li>Your Mongo instance(s) are bound to localhost</li></ul>

<p>Next, run the following command to initialize the connection:</p>

<pre>
# The \s are just to multiline the command and make it more readable
ssh \
-L 4321:localhost:27017 \
-i ~/.ssh/my_secure_key \
ssh_user@mongo_db_droplet_host_or_ip
</pre>

<p>Let's run through this step-by-step:</p>

<ol><li>SSH tunneling simply requires SSH - there are no special other programs/binaries you'll need</li>
<li>The `-L` option is telling SSH to setup a tunnel where port 4321 on your current machine will forward to the host `localhost` on port `27017` on Mongo Droplet being SSH'ed into</li>
<li>The `-i` option simply represents the recommendation made above to connect with an SSH key and not a password</li>
<li>The `ssh_user@mongo_db_droplet_host_or_ip` is standard for establishing an SSH connection</li></ol>

<p>Number 2 is really the meat of the instruction. This will determine how you tell your applications or services to connect to your MongoDB Droplets.</p>

<h3>Connect Over the Internet</h3>

<p>If connecting over an SSH tunnel is not necessarily an option, you can always connect over the internet. There are a few security strategies to consider here.</p>

<p>The first is to use a non-standard port. This is more of an obfuscation technique and simply means that default connection adapters will not work.</p>

<pre>
# In your MongoDB configuration file, change the following line to something other than 27017
port = 27017
</pre>

<p>Secondly, you'll want to bind Mongo directly to your application server's IP address. Setting this to 127.0.0.1 will ensure that Mongo only accepts connections locally.</p>

<pre>
# In your MongoDB configuration file, change the following line to your application server's IP address
bind_ip = 127.0.0.1
</pre>

<p>Lastly, consider using MongoDB's authentication feature and set a username and password. To set this up, connect to the MongoDB shell as an admin with the `mongo` command and add a user. Once that's done, make sure you're adding the newly added username/password in your MongoDB connection strings.</p>

<h3>Conclusion</h3>

<p>Please consider the above a starting point and not the be-all-end-all for MongoDB security. A key factor NOT mentioned here are server firewall rules. To see the 10gen firewall recommendations for MongoDB, head to their <a href="http://www.mongodb.org/display/DOCS/Security+and+Authentication#SecurityandAuthentication-FirewallRules" target="_blank">security documentation</a>.</p>

<h3>Resources</h3>

<ul><li><a href="http://serverfault.com/questions/237762/how-to-make-a-secure-mongodb-server" target="_blank">http://serverfault.com/questions/237762/how-to-make-a-secure-mongodb-server</a></li>
<li><a href="http://security.stackexchange.com/questions/7610/how-to-secure-a-mongodb-instance/7655#7655" target="_blank">http://security.stackexchange.com/questions/7610/how-to-secure-a-mongodb-instance/7655#7655</a></li>
<li><a href="http://www.mongodb.org/display/DOCS/Home" target="_blank">http://www.mongodb.org/display/DOCS/Home</a></li>
<li><a href="http://www.mongodb.org/display/DOCS/Security+and+Authentication" target="_blank">http://www.mongodb.org/display/DOCS/Security+and+Authentication</a></li>
<li><a href="http://hamant.net/2011/05/09/ssh-tricks/" target="_blank">http://hamant.net/2011/05/09/ssh-tricks/</a></li></ul>

<div class="author">By Etel Sverdlov</div></div>
    