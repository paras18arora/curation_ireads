<link rel='stylesheet' href='digital_ocean.css'><br> <br> 
      <h3 id="introduction">Introduction</h3>

<hr />

<p>MongoDB is a NoSQL document database system that scales well horizontally and implements data storage through a key-value system.  A popular choice for web applications and websites, MongoDB is easy to implement and access programmatically.</p>

<p>MongoDB achieves scaling through a technique known as "sharding".  Sharding is the process of writing data across different servers to distribute the read and write load and data storage requirements.</p>

<p>In a previous tutorial, we covered <a href="https://indiareads/community/articles/how-to-install-mongodb-on-ubuntu-12-04">how to install MongoDB on an Ubuntu 12.04 VPS</a>.  We will use this as a jumping off point to talk about how to implement sharding across a number of different nodes.</p>

<h2 id="mongodb-sharding-topology">MongoDB Sharding Topology</h2>

<hr />

<p>Sharding is implemented through three separate components.  Each part performs a specific function:</p>

<ul>
<li><strong>Config Server</strong>: Each production sharding implementation must contain exactly three configuration servers.  This is to ensure redundancy and high availability.</li>
</ul>

<p>Config servers are used to store the metadata that links requested data with the shard that contains it.  It organizes the data so that information can be retrieved reliably and consistently.</p>

<ul>
<li><strong>Query Routers</strong>: The query routers are the machines that your application actually connects to.  These machines are responsible for communicating to the config servers to figure out where the requested data is stored.  It then accesses and returns the data from the appropriate shard(s).</li>
</ul>

<p>Each query router runs the "mongos" command.</p>

<ul>
<li><strong>Shard Servers</strong>: Shards are responsible for the actual data storage operations.  In production environments, a single shard is usually composed of a replica set instead of a single machine.  This is to ensure that data will still be accessible in the event that a primary shard server goes offline.</li>
</ul>

<p>Implementing replicating sets is outside of the scope of this tutorial, so we will configure our shards to be single machines instead of replica sets.  You can easily modify this if you would like to <a href="https://indiareads/community/articles/how-to-implement-replication-sets-in-mongodb-on-an-ubuntu-vps">configure replica sets</a> for your own configuration.</p>

<h2 id="initial-set-up">Initial Set Up</h2>

<hr />

<p>If you were paying attention above, you probably noticed that this configuration requires quite a few machines.  In this tutorial, we will configure an example sharding cluster that contains:</p>

<ul>
<li>3 Config Servers (Required in production environments)</li>
<li>2 Query Routers (Minimum of 1 necessary)</li>
<li>4 Shard Servers (Minimum of 2 necessary) </li>
</ul>

<p>This means that you will need nine VPS instances to follow along exactly.  In reality, some of these functions can overlap (for instance, you can run a query router on the same VPS you use as a config server) and you only need one query router and a minimum of 2 shard servers.</p>

<p>We will go above this minimum in order to demonstrate adding multiple components of each type.  We will also treat all of these components as discrete machines for clarity and simplicity.</p>

<h3 id="set-up-initial-base-image">Set Up Initial Base Image</h3>

<hr />

<p>To get started, <a href="https://indiareads/community/articles/how-to-install-mongodb-on-ubuntu-12-04">install and configure an initial MongoDB server on Ubuntu</a> using this guide.  We will use this to bootstrap the rest of our sharding components.</p>

<p>When you have finished that tutorial for your first server, shut down the instance with this command:</p>
<pre class="code-pre "><code langs="">sudo shutdown -h now
</code></pre>
<p>Now, we are going to take a snapshot of this configured droplet and use it to spin up our other VPS instances.</p>

<p>In your IndiaReads control panel, select the droplet.  Click on the "Snapshots" tab.  Enter a snapshot name and click "Take Snapshot":</p>

<p><img src="https://assets.digitalocean.com/articles/mongodb_sharding/take_snapshot.png" alt="IndiaReads take snapshot" /></p>

<p>Your snapshot will be taken and the initial server will be rebooted.</p>

<h3 id="spin-up-vps-instances-based-on-snapshot">Spin Up VPS Instances Based on Snapshot</h3>

<hr />

<p>Now that we have an image saved through the snapshot process, we can use this as a base for the rest of our MongoDB components.</p>

<p>From the control panel, click on the "Create" button.  Enter a name that describes the purpose that your droplet will have in the sharding configuration:</p>

<p><img src="https://assets.digitalocean.com/articles/mongodb_sharding/name_droplet.png" alt="IndiaReads name droplet" /></p>

<p>Select the droplet size and the region.  It is best to choose the same region for all of your components.</p>

<p>Under the "Select Image" section, click on the "My Images" tab and select the MongoDB snapshot you just created.</p>

<p><img src="https://assets.digitalocean.com/articles/mongodb_sharding/mongodb_image.png" alt="IndiaReads select image" /></p>

<p>Add any SSH keys you need and select the settings you would like to use.  Click "Create Droplet" to spin up your new VPS instance.</p>

<p>Repeat this step for each of the sharding components.  Remember, to follow along with this tutorial exactly (not necessary, but demonstrative), you need 3 config servers, 2 query servers, and 4 shard servers.</p>

<h3 id="configure-dns-subdomain-entries-for-each-component-optional">Configure DNS Subdomain Entries for Each Component (Optional)</h3>

<hr />

<p>The MongoDB documentation recommends that you refer to all of your components by a DNS resolvable name instead of by a specific IP address.  This is important because it allows you to change servers or redeploy certain components without having to restart every server that is associated with it.</p>

<p>For ease of use, I recommend that you give each server its own subdomain on the domain that you wish to use.  You can use this guide to learn <a href="https://indiareads/community/articles/how-to-set-up-and-test-dns-subdomains-with-digitalocean-s-dns-panel">how to set up DNS subdomains using IndiaReads's control panel</a>.</p>

<p>For the purposes of this tutorial, we will refer to the components as being accessible at these subdomain:</p>

<ul>
<li><p><strong>Config Servers</strong></p>

<ul>
<li><span class="highlight">config0.example.com</span></li>
<li><span class="highlight">config1.example.com</span></li>
<li><span class="highlight">config2.example.com</span></li>
</ul></li>
<li><p><strong>Query Routers</strong></p>

<ul>
<li><span class="highlight">query0.example.com</span></li>
<li><span class="highlight">query1.example.com</span></li>
</ul></li>
<li><p><strong>Shard Servers</strong></p>

<ul>
<li><span class="highlight">shard0.example.com</span></li>
<li><span class="highlight">shard1.example.com</span></li>
<li><span class="highlight">shard2.example.com</span></li>
<li><span class="highlight">shard3.example.com</span></li>
</ul></li>
</ul>

<p>If you do not set up subdomains, you can still follow along, but your configuration will not be as robust.  If you wish to go this route, simply substitute the subdomain specifications with your droplet's IP address.</p>

<h2 id="initialize-the-config-servers">Initialize the Config Servers</h2>

<hr />

<p>The first components that must be set up are the configuration servers.  These must be online and operational before the query routers or shards can be configured.</p>

<p>Log into your first configuration server as root.</p>

<p>The first thing we need to do is create a data directory, which is where the configuration server will store the metadata that associates location and content:</p>
<pre class="code-pre "><code langs="">mkdir /mongo-metadata
</code></pre>
<p>Now, we simply have to start up the configuration server with the appropriate parameters.  The service that provides the configuration server is called <code>mongod</code>.  The default port number for this component is <code>27019</code>.</p>

<p>We can start the configuration server with the following command:</p>
<pre class="code-pre "><code langs="">mongod --configsvr --dbpath /mongo-metadata --port 27019
</code></pre>
<p>The server will start outputting information and will begin listening for connections from other components.</p>

<p>Repeat this process exactly on the other two configuration servers.  The port number should be the same across all three servers.</p>

<h2 id="configure-query-router-instances">Configure Query Router Instances</h2>

<hr />

<p>At this point, you should have all three of your configuration servers running and listening for connections.  They must be operational before continuing.</p>

<p>Log into your first query router as root.</p>

<p>The first thing we need to do is stop the <code>mongodb</code> process on this instance if it is already running.  The query routers use data locks that conflict with the main MongoDB process:</p>
<pre class="code-pre "><code langs="">service mongodb stop
</code></pre>
<p>Next, we need to start the query router service with a specific configuration string.  The configuration string must be exactly the same for every query router you configure (including the order of arguments).  It is composed of the address of each configuration server and the port number it is operating on, separated by a comma.</p>

<p>They query router service is called <code>mongos</code>.  The default port number for this process is <code>27017</code> (but the port number in the configuration refers to the configuration server port number, which is <code>27019</code> by default).</p>

<p>The end result is that the query router service is started with a string like this:</p>

<pre>
mongos --configdb <span class="highlight">config0.example.com</span>:27019,<span class="highlight">config1.example.com</span>:27019,<span class="highlight">config2.example.com</span>:27019
</pre>

<p>Your first query router should begin to connect to the three configuration servers.  Repeat these steps on the other query router.  Remember that the <code>mongodb</code> service must be stopped prior to typing in the command.</p>

<p>Also, keep in mind that the <strong>exact</strong> same command must be used to start each query router.  Failure to do so will result in an error.</p>

<h2 id="add-shards-to-the-cluster">Add Shards to the Cluster</h2>

<hr />

<p>Now that we have our configuration servers and query routers configured, we can begin adding the actual shard servers to our cluster.  These shards will each hold a portion of the total data.</p>

<p>Log into one of your shard servers as root.</p>

<p>As we mentioned in the beginning, in this guide we will only be using single machine shards instead of replica sets.  This is for the sake of brevity and simplicity of demonstration.  In production environments, a replica set is very highly recommended in order to ensure the integrity and availability of the data.  To <a href="https://indiareads/community/articles/how-to-implement-replication-sets-in-mongodb-on-an-ubuntu-vps">configure replica sets in MongoDB</a>, follow this guide.</p>

<p>To actually add the shards to the cluster, we will need to go through the query routers, which are now configured to act as our interface with the cluster.  We can do this by connecting to <em>any</em> of the query routers like this:</p>

<pre>
mongo --host <span class="highlight">query0.example.com</span> --port 27017
</pre>

<p>This will connect to the appropriate query router and open a mongo prompt.  We will add all of our shard servers from this prompt.</p>

<p>To add our first shard, type:</p>

<pre>
sh.addShard( "<span class="highlight">shard0.example.com</span>:27017" )
</pre>

<p>You can then add your remaining shard droplets in this same interface.  You do not need to log into each shard server individually.</p>

<pre>
sh.addShard( "<span class="highlight">shard1.example.com</span>:27017" )
sh.addShard( "<span class="highlight">shard2.example.com</span>:27017" )
sh.addShard( "<span class="highlight">shard3.example.com</span>:27017" )
</pre>

<p>If you are configuring a production cluster, complete with replication sets, you have to instead specify the replication set name and a replication set member to establish each set as a distinct shard.  The syntax would look something like this:</p>

<pre>
sh.addShard( "<span class="highlight">rep_set_name</span>/<span class="highlight">rep_set_member</span>:27017" )
</pre>

<h2 id="how-to-enable-sharding-for-a-database-collection">How to Enable Sharding for a Database Collection</h2>

<hr />

<p>MongoDB organizes information into databases.  Inside each database, data is further compartmentalized through "collections".  A collection is akin to a table in traditional relational database models.</p>

<p>In this section, we will be operating using the querying routers again.  If you are not still connected to the query router, you can access it again using the same mongo command you used in the last section:</p>

<pre>
mongo --host <span class="highlight">config0.example.com</span> --port 27017
</pre>

<h3 id="enable-sharding-on-the-database-level">Enable Sharding on the Database Level</h3>

<hr />

<p>We will enable sharding first on the database level.  To do this, we will create a test database called (appropriately) <code>test_db</code>.</p>

<p>To create this database, we simply need to change to it.  It will be marked as our current database and created dynamically when we first enter data into it:</p>
<pre class="code-pre "><code langs="">use test_db
</code></pre>
<p>We can check that we are currently using the database we just created by typing:</p>
<pre class="code-pre "><code langs="">db
</code></pre>
<hr />
<pre class="code-pre "><code langs="">test_db
</code></pre>
<p>We can see all of the available databases by typing:</p>
<pre class="code-pre "><code langs="">show dbs
</code></pre>
<p>You may notice that the database that we just created does not show up.  This is because it holds no data so it is not quite real yet.</p>

<p>We can enable sharding on this database by issuing this command:</p>
<pre class="code-pre "><code langs="">sh.enableSharding("test_db")
</code></pre>
<p>Again, if we enter the <code>show dbs</code> command, we will not see our new database.  However, if we switch to the <code>config</code> database which is generated automatically, and issue a <code>find()</code> command, our new database will be returned:</p>
<pre class="code-pre "><code langs="">use config
db.databases.find()
</code></pre>
<hr />
<pre class="code-pre "><code langs="">{ "_id" : "admin", "partitioned" : false, "primary" : "config" }
{ "_id" : "test_db", "partitioned" : true, "primary" : "shard0003" }
</code></pre>
<p>Your database will show up with the <code>show dbs</code> command when MongoDB has added some data to the new database.</p>

<h3 id="enable-sharding-on-the-collections-level">Enable Sharding on the Collections Level</h3>

<hr />

<p>Now that our database is marked as being available for sharding, we can enable sharding on a specific collection.</p>

<p>At this point, we need to decide on a sharding strategy.  Sharding works by organizing data into different categories based on a specific field designated as the <code>shard key</code> in the documents it is storing.  It puts all of the documents that have a matching shard key on the same shard.</p>

<p>For instance, if your database is storing employees at a company and your shard key is based on favorite color, MongoDB will put all of the employees with <code>blue</code> in the favorite color field on a single shard.  This can lead to disproportional storage if everybody likes a few colors.</p>

<p>A better choice for a shard key would be something that's guaranteed to be more evenly distributed.  For instance, in a large company, a birthday (month and day) field would probably be fairly evenly distributed.</p>

<p>In cases where you're unsure about how things will be distributed, or there is no appropriate field, you can create a "hashed" shard key based on an existing field.  This is what we will be doing for our data.</p>

<p>We can create a collection called <code>test_collection</code> and hash its "<em>id" field.  Make sure we're using our test</em>db database and then issue the command:</p>
<pre class="code-pre "><code langs="">use test_db
db.test_collection.ensureIndex( { _id : "hashed" } )
</code></pre>
<p>We can then shard the collection by issuing this command:</p>
<pre class="code-pre "><code langs="">sh.shardCollection("test_db.test_collection", { "_id": "hashed" } )
</code></pre>
<p>This will shard the collection across all of the available shards.</p>

<h3 id="insert-test-data-into-the-collection">Insert Test Data into the Collection</h3>

<hr />

<p>We can see our sharding in action by using a loop to create some objects.  This <a href="http://docs.mongodb.org/manual/tutorial/generate-test-data/">loop comes directly from the MongoDB website</a> for generating test data.</p>

<p>We can insert data into the collection using a simple loop like this:</p>
<pre class="code-pre "><code langs="">use test_db
for (var i = 1; i <= 500; i++) db.test_collection.insert( { x : i } )
</code></pre>
<p>This will create 500 simple documents ( only an ID field and an "x" field containing a number) and distribute them among the different shards.  You can see the results by typing:</p>
<pre class="code-pre "><code langs="">db.test_collection.find()
</code></pre>
<hr />
<pre class="code-pre "><code langs="">{ "_id" : ObjectId("529d082c488a806798cc30d3"), "x" : 6 }
{ "_id" : ObjectId("529d082c488a806798cc30d0"), "x" : 3 }
{ "_id" : ObjectId("529d082c488a806798cc30d2"), "x" : 5 }
{ "_id" : ObjectId("529d082c488a806798cc30ce"), "x" : 1 }
{ "_id" : ObjectId("529d082c488a806798cc30d6"), "x" : 9 }
{ "_id" : ObjectId("529d082c488a806798cc30d1"), "x" : 4 }
{ "_id" : ObjectId("529d082c488a806798cc30d8"), "x" : 11 }
. . .
</code></pre>
<p>To get more values, type:</p>
<pre class="code-pre "><code langs="">it
</code></pre>
<hr />
<pre class="code-pre "><code langs="">{ "_id" : ObjectId("529d082c488a806798cc30cf"), "x" : 2 }
{ "_id" : ObjectId("529d082c488a806798cc30dd"), "x" : 16 }
{ "_id" : ObjectId("529d082c488a806798cc30d4"), "x" : 7 }
{ "_id" : ObjectId("529d082c488a806798cc30da"), "x" : 13 }
{ "_id" : ObjectId("529d082c488a806798cc30d5"), "x" : 8 }
{ "_id" : ObjectId("529d082c488a806798cc30de"), "x" : 17 }
{ "_id" : ObjectId("529d082c488a806798cc30db"), "x" : 14 }
{ "_id" : ObjectId("529d082c488a806798cc30e1"), "x" : 20 }
. . .
</code></pre>
<p>To get information about the specific shards, you can type:</p>
<pre class="code-pre "><code langs="">sh.status()
</code></pre>
<hr />
<pre class="code-pre "><code langs="">--- Sharding Status --- 
  sharding version: {
    "_id" : 1,
    "version" : 3,
    "minCompatibleVersion" : 3,
    "currentVersion" : 4,
    "clusterId" : ObjectId("529cae0691365bef9308cd75")
}
  shards:
    {  "_id" : "shard0000",  "host" : "162.243.243.156:27017" }
    {  "_id" : "shard0001",  "host" : "162.243.243.155:27017" }
. . .
</code></pre>
<p>This will provide information about the chunks that MongoDB distributed between the shards.</p>

<h2 id="conclusion">Conclusion</h2>

<hr />

<p>By the end of this guide, you should be able to implement your own MongoDB sharding configuration.  The specific configuration of your servers and the shard key that you choose for each collection will have a big impact on the performance of your cluster.</p>

<p>Choose the field or fields that have the best distribution properties and most closely represent the logical groupings that will be reflected in your database queries.  If MongoDB only has to go to a single shard to retrieve your data, it will return faster.</p>

<div class="author">By Justin Ellingwood</div>

    