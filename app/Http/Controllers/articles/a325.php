<link rel='stylesheet' href='digital_ocean.css'><br><img src=https://community-cdn-digitalocean-com.global.ssl.fastly.net/assets/tutorials/images/large/MongoDB_OneClick.png?1426699822/> <br> 
      <p>MongoDB is a highly-scalable NoSQL database with a document-based data model and an expressive query language. IndiaReads's MongoDB One-Click application allows you to quickly spin up a droplet with MongoDB pre-installed. It aims to help get your application off the ground quickly.</p>

<h2 id="creating-your-mongodb-droplet">Creating Your MongoDB Droplet</h2>

<p>You can launch a new MongoDB instance by selecting <strong>MongoDB on Ubuntu 14.04</strong> from the Applications menu during droplet creation:</p>

<p><img src="https://assets.digitalocean.com/articles/MongoDB-1-Click/mongo_db_one-click.png" alt="" /></p>

<p>Once you have created the droplet, connect to it via the web-based console in the IndiaReads control panel or SSH:</p>
<pre class="code-pre "><code langs="">ssh root@<span class="highlight">your.ip.address</span>
</code></pre>
<h2 id="accessing-mongodb">Accessing MongoDB</h2>

<p>Your MongoDB instance will be available at <code>127.0.0.1:27017</code> It is bound to the localhost by default and its configuration details can be found in <code>/etc/mongod.conf</code>. To connect to the test database with the MongoDB shell, simply run:</p>
<pre class="code-pre "><code langs="">mongo
</code></pre>
<h2 id="accessing-remotely">Accessing Remotely</h2>

<p>You can access your  MongoDB instance remotely via an SSH tunnel using:</p>
<pre class="code-pre "><code langs="">ssh -L 4321:localhost:27017 <span class="highlight">user</span>@<span class="highlight">your.ip.address</span> -f -N
mongo --port 4321
</code></pre>
<p>This opens an SSH connection which allows you to access port 27017 of the remote server locally on port 4321. This can be useful for securely accessing your MongoDB instance without opening it up to accept connections via the wider internet. </p>

<p>In order to enable access over the internet, modify the value of <code>bind_ip</code> in <code>/etc/mongod.conf</code> If you do so, you are highly advised to first review the <a href="http://docs.mongodb.org/manual/administration/security-checklist/">security checklist from the MongoDB documentation.</a> In addition to enabling one of the forms of authentication supported by MongoDB, setting up a firewall that only allows remote connections from specific IP addresses is a good security measure to implement.</p>

<p>Managing an IP Tables firewall is <a href="https://indiareads/community/tutorials/how-to-setup-a-firewall-with-ufw-on-an-ubuntu-and-debian-cloud-server">made easy using UFW on Ubuntu</a>. The following commands will erect a firewall which will allow all outgoing connections from your server but only allow incoming connections via SSH or from the specified IP address (<span class="highlight">ip.address.to.allow</span>).</p>
<pre class="code-pre "><code langs="">sudo apt-get install ufw
sudo ufw default deny incoming
sudo ufw default allow outgoing
sudo ufw allow ssh
sudo ufw allow from <span class="highlight">ip.address.to.allow</span>
sudo ufw enable
</code></pre>
<h2 id="further-information">Further information</h2>

<p>The One-Click application simply provides you with MongoDB as a pre-installed base. It's up to you how you want to use it. Whether you  are building out a sharded cluster or simply want to connect it to an app on the same host, we have a number of tutorials which should point you in the right direction:</p>

<ul>
<li><a href="https://indiareads/community/tutorials/how-to-implement-replication-sets-in-mongodb-on-an-ubuntu-vps">How To Implement Replication Sets in MongoDB</a></li>
<li><a href="https://indiareads/community/tutorials/how-to-create-a-sharded-cluster-in-mongodb-using-an-ubuntu-12-04-vps">How To Create a Sharded Cluster in MongoDB</a></li>
<li><a href="https://indiareads/community/tutorials/how-to-connect-node-js-to-a-mongodb-database-on-a-vps">How To Connect Node.js to a MongoDB Database</a></li>
</ul>

<p>For more ideas one what is possible, check out the rest of the tutorials under <a href="https://indiareads/community/tags/mongodb?primary_filter=tutorials">the MongoDB tag here at IndiaReads</a>.</p>

    