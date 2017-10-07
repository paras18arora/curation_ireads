<link rel='stylesheet' href='digital_ocean.css'><br><img src=https://community-cdn-digitalocean-com.global.ssl.fastly.net/assets/tutorials/images/large/Apache-Cassandra-TW-V3.png?1436369696/> <br> 
      <p><span class="highlight">NOTE</span>: Due to Cassandra's memory requirements, the Cassandra One-Click Application image can only be used on droplets with 1GB or more of RAM.</p>

<h2 id="introduction">Introduction</h2>

<p><a href="http://cassandra.apache.org/">Apache Cassandra</a> is an open source distributed noSQL database system which can handle massive data sets across many nodes.  This tutorial will guide you in using the IndiaReads Cassandra One-Click Application image to create a single or multi-node cluster as well as methods to automate scaling your Cassandra cluster using <a href="https://indiareads/company/blog/automating-application-deployments-with-user-data/">user-data</a>.</p>

<h2 id="creating-a-cassandra-droplet">Creating a Cassandra Droplet</h2>

<p>To create your first Cassandra droplet navigate to the <strong>Create Droplet</strong> page in the control panel, select a size, name and region for your droplet and then choose the <code>Cassandra on 14.04</code> image from the Applications tab before clicking <strong>Create Droplet</strong></p>

<p>Once your droplet has been created you will have a single-node Cassandra cluster ready to use locally on your droplet.</p>

<h2 id="configuring-cassandra">Configuring Cassandra</h2>

<p>This local, single-node cluster has some limitations.  When it is first launched the Cassandra service will only be listening on <strong>localhost</strong> meaning that the service will not be accessible by clients outside your Cassandra droplet.  Additionally there is no Authentication service enabled by default which means that the service will not prompt for a username and password.  The first thing we will do is to adjust some of these configuration settings to something more ideal.</p>

<p>First, stop the Cassandra service.</p>
<pre class="code-pre "><code langs="">service cassandra stop
</code></pre>
<p>Then we will clear any data that the Cassandra service generated when it first launched so we can start with a clean setup.</p>
<pre class="code-pre "><code langs="">rm -rf /var/lib/cassandra/*;
</code></pre>
<p>Now we're ready to start modifying the Cassandra configuration file.  Open the file <code>/etc/cassandra/cassandra.yaml</code> using the editor of your choice.</p>

<p>First we will give our Cluster a name.  Find the line</p>
<pre class="code-pre "><code langs="">cluster_name: 'Test Cluster'
</code></pre>
<p>in cassandra.yaml and change <strong>Test Cluster</strong> to a name of your choice.  Note: The name you select here must be included in the configuration for each node in your cluster.</p>

<p>Next we will allow cassandra to listen on the public network.  To do this, locate the line:</p>
<pre class="code-pre "><code langs="">listen_address: localhost
</code></pre>
<p>and change <strong>localhost</strong> to your droplet's IP address.</p>
<pre class="code-pre "><code langs="">listen_address: <span class="highlight">12.34.56.78</span>
</code></pre>
<p>We can't have our database listening for requests on the public interface without ensuring we have some security set up so next we will enable password authentication.  To do this, locate the line:</p>
<pre class="code-pre "><code langs="">authenticator: AllowAllAuthenticator
</code></pre>
<p>and change it to:</p>
<pre class="code-pre "><code langs="">authenticator: PasswordAuthenticator
</code></pre>
<p>Finally we need to specify a seed IP address.  Since this is the only node in our cluster we will use our droplet's public IP address again here.  Find the line:</p>
<pre class="code-pre "><code langs="">seeds: "127.0.0.1"
</code></pre>
<p>and change it to your droplet's IP address.</p>
<pre class="code-pre "><code langs="">seeds: "<span class="highlight">12.34.56.78</span>"
</code></pre>
<p>Now that we've completed our changes to the Cassandra configuration you can save your changes and exit your editor.</p>

<p>We can now start the cassandra service back up with the following command:</p>
<pre class="code-pre "><code langs="">service cassandra start
</code></pre>
<p>After we allow a couple minutes for the service to complete it's start-up routine we can connect to our cassandra service using <code>cqlsh</code>, the CQL shell.  Since we have enabled password authentication but have not yet created a new user account we will use the default user <code>cassandra</code> with the password <code>cassandra</code>.</p>
<pre class="code-pre "><code langs="">cqlsh -u cassandra -p cassandra
</code></pre>
<p>You should see something like the following displayed:</p>
<pre class="code-pre "><code langs="">Connected to testCluster at 127.0.0.1:9042.
[cqlsh 5.0.1 | Cassandra 2.1.3 | CQL spec 3.2.0 | Native protocol v3]
Use HELP for help.
cassandra@cqlsh>
</code></pre>
<p>Obviously our current username and password are not very secure so we will create a new user account to administer our cluster and remove permissions from the default <code>cassandra</code> user.  First create the new user as a SUPERUSER:</p>
<pre class="code-pre "><code langs="">CREATE USER <span class="highlight">newadminuser</span> WITH PASSWORD '<span class="highlight">mypassword</span>' SUPERUSER;
</code></pre>
<p>Next we will change the cassandra user's password to something hard to guess and remove it's super-user status:</p>
<pre class="code-pre "><code langs="">ALTER USER cassandra WITH PASSWORD '<span class="highlight">89asd9f87as9f879sf</span>' NOSUPERUSER;
</code></pre>
<p>Now we have our single-node cluster up and running and we have created a user account to allow us to manage it.  Next lets add some data to our cluster.</p>

<p>We will start by creating a <em>keyspace</em>.  If you are familiar with other database platforms a keyspace in Cassandra serves much the same role as a database in MySQL.  Each keyspace can include many tables of data.  Options can be passed when creating a new keyspace, for this example we will use a very basic set of options to create a keyspace called <code>Test</code>:</p>
<pre class="code-pre "><code langs="">CREATE KEYSPACE Test WITH REPLICATION = { 'class' : 'SimpleStrategy', 'replication_factor' : 3 };
</code></pre>
<p>Next we will add a very basic table called <code>users</code> to our new keyspace.  </p>
<pre class="code-pre "><code langs="">CREATE TABLE Test.users (user_name varchar PRIMARY KEY,password varchar,info varchar);
</code></pre>
<p>This will create a table with 3 varchar columns that can accept text with <code>user_name</code> set as the PRIMARY KEY.</p>

<p>Next, lets add a record to this new table.</p>
<pre class="code-pre "><code langs="">INSERT INTO Test.users (user_name,password,info) VALUES ('JohnDoe','1234','user information goes here');
</code></pre>
<p>Now we can query this information with a CQL query:</p>
<pre class="code-pre "><code langs="">SELECT * from Test.users;
</code></pre>
<p>And we should see our record returned:</p>
<pre class="code-pre "><code langs="">user_name | info                       | password
-----------+----------------------------+----------
JohnDoe | user information goes here |     1234

(1 rows)
</code></pre>
<h2 id="multi-node-clusters">Multi-Node Clusters</h2>

<p>Now that we have Cassanrda running as a single node cluster lets add some more nodes.  As with our first node we will start by creating a new droplet using the Cassandra One-Click image.</p>

<p>Once our new node is created we can connect to it via ssh and perform our initial setup.</p>

<p>First, stop the Cassandra service:</p>
<pre class="code-pre "><code langs="">service cassandra stop;
</code></pre>
<p>and clear out any data created so far on this new node:</p>
<pre class="code-pre "><code langs="">rm -rf /var/lib/cassandra/*;
</code></pre>
<p>Now we are ready to begin editing our configuration.  Open <code>/etc/cassandra/cassandra.yaml</code> in the editor of your choice.</p>

<p>Locate the cluster_name line and set it to the same value you used for your first node.</p>
<pre class="code-pre "><code langs="">cluster_name: '<span class="highlight">myCluster</span>'
</code></pre>
<p>Next we will ensure this new node is listening on the public network interface by changing the <code>listen_address</code> to our droplet's IP address.</p>
<pre class="code-pre "><code langs="">listen_address: <span class="highlight">12.34.56.90</span>
</code></pre>
<p>Now we will update the seed IP.  We will set this to our first node's IP address so our new node can sync with it and join the cluster.</p>
<pre class="code-pre "><code langs="">seeds: "<span class="highlight">12.34.56.78</span>"
</code></pre>
<p>Now save and close the configuration file.</p>

<p>Now that we have configured our new droplet to join our cluster we can start the Cassandra service.</p>
<pre class="code-pre "><code langs="">service cassandra start
</code></pre>
<p>It will take a couple minutes for this new node to come online and join our cluster.  After 5 minutes or so we can try using our new node.</p>
<pre class="code-pre "><code langs="">cqlsh -u <span class="highlight">newadminuser</span> -p <span class="highlight">mypassword</span>
</code></pre>
<p>As with our first node we should now see a successful connection reported:</p>
<pre class="code-pre "><code langs="">Connected to testCluster at 127.0.0.1:9042.
[cqlsh 5.0.1 | Cassandra 2.1.3 | CQL spec 3.2.0 | Native protocol v3]
Use HELP for help.
cassandra@cqlsh>
</code></pre>
<p>If you see an error messsage saying that cqlsh was not able to connect you may need to allow a bit more time for the new node to come online.</p>

<p>Now that we're connected to our new node, lets test it out by running a query on the data we added on our first node.</p>
<pre class="code-pre "><code langs="">SELECT * from Test.users;
</code></pre>
<p>We should see our record returned just as it was with our first node.</p>
<pre class="code-pre "><code langs="">user_name | info                       | password
-----------+----------------------------+----------
JohnDoe | user information goes here |     1234

(1 rows)
</code></pre>
<p>We now have a functional multi-node Cassandra cluster.</p>

<h2 id="using-user-data-to-deploy-nodes">Using User-Data to Deploy Nodes</h2>

<p>It would be quite time consuming to perform each of these steps for every droplet we want to add to our cluster.  Luckily with user-data we can automate this process.  By passing the important variables to our droplet when it is created we can have it join our cluster immediately.  For this example we will check the user-data checkbox on the droplet creation page and pate in the following script (modifying the values in red to those of our cluster and first node).</p>
<pre class="code-pre "><code langs="">#!/bin/bash
export CLUSTER_NAME='<span class="highlight">myCluster</span>';
export SEED_ADDRESS='<span class="highlight">12.34.56.78</span>';
export IP_ADDRESS=$(curl -s http://169.254.169.254/metadata/v1/interfaces/public/0/ipv4/address);
service cassandra stop;
rm -rf /var/lib/cassandra/*;
sed -i.bak "s/cluster\_name\:\ 'Test Cluster'/cluster\_name\:\ '${CLUSTER_NAME}'/g" /etc/cassandra/cassandra.yaml
sed -i.bak s/authenticator\:\ AllowAllAuthenticator/authenticator\:\ PasswordAuthenticator/g /etc/cassandra/cassandra.yaml;
sed -i.bak s/listen\_address\:\ localhost/listen_address\:\ ${IP_ADDRESS}/g /etc/cassandra/cassandra.yaml;
sed -i.bak s/\-\ seeds\:\ \"127.0.0.1\"/\-\ seeds\:\ \"${SEED_ADDRESS}\"/g /etc/cassandra/cassandra.yaml;
service cassandra start;
</code></pre>
<p>Let's break down what this user-data script does.  Most of it should be familiar.</p>

<p>First we have the two variables we will need to set for our new droplet, cluster name and seed ip address (the IP of our first node).</p>
<pre class="code-pre "><code langs="">export CLUSTER_NAME='<span class="highlight">myCluster</span>';
export SEED_ADDRESS='<span class="highlight">12.34.56.78</span>';
</code></pre>
<p>Then we can use droplet meta-data to get the IP address of our newly created droplet and assign it to a variable.</p>
<pre class="code-pre "><code langs="">export IP_ADDRESS=$(curl -s http://169.254.169.254/metadata/v1/interfaces/public/0/ipv4/address);
</code></pre>
<p>Next we will stop the Cassandra service and clear our any existing data.</p>
<pre class="code-pre "><code langs="">service cassandra stop;
rm -rf /var/lib/cassandra/*;
</code></pre>
<p>We then use the sed command to find and replace the coniguration values we need to change.</p>
<pre class="code-pre "><code langs="">sed -i.bak "s/cluster\_name\:\ 'Test Cluster'/cluster\_name\:\ '${cluster_name}'/g" /etc/cassandra/cassandra.yaml
sed -i.bak s/authenticator\:\ AllowAllAuthenticator/authenticator\:\ PasswordAuthenticator/g /etc/cassandra/cassandra.yaml;
sed -i.bak s/listen\_address\:\ localhost/listen_address\:\ ${IP_ADDRESS}/g /etc/cassandra/cassandra.yaml;
sed -i.bak s/\-\ seeds\:\ \"127.0.0.1\"/\-\ seeds\:\ \"${SEED_ADDRESS}\"/g /etc/cassandra/cassandra.yaml;
</code></pre>
<p>Finally we start the Cassandra service with our new configuration.</p>
<pre class="code-pre "><code langs="">service cassandra start;
</code></pre>
<p>As with the manual setup of an additional node it may take several minutes for the cql service to be available on our new droplet but once it is up and running this new node should allow us to query our test keyspace and table just as the one we manually configured did.</p>

<h2 id="next-steps">Next Steps</h2>

<p>We can take this tutorial one step further and automate the entire process.  We have created a Ruby script, <a href="http://github.com/ryanpq/do-ccc">do-ccc</a> based on the steps in this tutorial which utilizes the IndiaReads API along with user-data and droplet meta-data to deploy a complete Cassandra cluster automatically. The script will prompt you for a cluster name, a region where you want to deploy your droplets, the number of nodes to create and the size of each node and will then create your cluster for you.</p>

<p>This guide provides steps to create a very basic Cassandra cluster.  There are many ways that your configuration can be adjusted and optimized and it is strongly recommended to review the <a href="http://wiki.apache.org/cassandra/GettingStarted">Apache Cassandra Documentation</a> and other sources for more information on how to tune your cluster to best meet your needs.</p>

    