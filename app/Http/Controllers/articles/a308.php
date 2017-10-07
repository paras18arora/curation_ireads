<link rel='stylesheet' href='digital_ocean.css'><br><img src=https://community-cdn-digitalocean-com.global.ssl.fastly.net/assets/tutorials/images/large/01272014GaleraCluster_twitter.png?1426699637/> <br> 
      <h3 id="introduction">Introduction</h3>

<hr />

<p>When dealing with relational database systems in a production environment, it is often best to have some kind of replication procedures in place.  Replication allows your data to be transferred to different nodes automatically.</p>

<p>A simple master-slave replication is most common in the SQL world.  This allows you to use one "master" server to handle all of the application writes, while multiple "slave" servers can be used to read data.  It is possible to configure failover and other techniques.</p>

<p>While master-slave replication is useful, it is not as flexible as master-master replication.  In a master-master configuration, each node is able to accept writes and distribute them throughout the cluster.  MariaDB does not have a stable version of this by default, but a set of patches known as "Galera" implement synchronous master-master replication.</p>

<p>In this guide, we will be creating a Galera cluster using Ubuntu 12.04 VPS instances.  We will be using three servers for demonstration purposes (the smallest configurable cluster), but five nodes are recommended for production situations.</p>

<h2 id="add-the-mariadb-repositories">Add the MariaDB Repositories</h2>

<hr />

<p>The MariaDB and Galera packages are not available in the default Ubuntu repositories.  However, the MariaDB project maintains its own repositories for Ubuntu that contain all of the packages that we need.</p>

<p>On each of the three servers that we will be configuring for this cluster, you need to first install the <code>python-software-properties</code> package.  This will give us the commands we need to administer our repositories:</p>
<pre class="code-pre "><code langs="">sudo apt-get update
sudo apt-get install python-software-properties
</code></pre>
<p>Now, we can add the key files for the MariaDB repository.  This will tell our server that we trust the maintainers of the repositories and that we can install the packages within them without a problem.</p>
<pre class="code-pre "><code langs="">sudo apt-key adv --recv-keys --keyserver hkp://keyserver.ubuntu.com:80 0xcbcb082a1bb943db
</code></pre>
<p>This will accept the key file.  Now that we have the trusted key in the database, we can add the actual repository:</p>
<pre class="code-pre "><code langs="">sudo add-apt-repository 'deb http://mirror.jmu.edu/pub/mariadb/repo/5.5/ubuntu precise main'
</code></pre>
<h2 id="install-mariadb-with-galera-patches">Install MariaDB with Galera Patches</h2>

<hr />

<p>We can now easily install MariaDB with the Galera patches through the apt interface.  Remember to update the database first:</p>
<pre class="code-pre "><code langs="">sudo apt-get update
sudo apt-get install mariadb-galera-server galera
</code></pre>
<p>During the installation, you will be asked to set a password for the MariaDB administrative user.  You can set the same password across all of the server instances.</p>

<p>If, for some reason, you do not already have rsync installed on your machines, you should install it now by typing:</p>
<pre class="code-pre "><code langs="">sudo apt-get install rsync
</code></pre>
<p>We now have all of the pieces necessary to begin configuring our cluster.</p>

<h2 id="configure-mariadb-and-galera">Configure MariaDB and Galera</h2>

<hr />

<p>Now that we have installed the MariaDB and Galera on each of our three servers, we can begin configuration.</p>

<p>The cluster will actually need to share its configuration.  Because of this, we will do all of the configuration on our first machine, and then copy it to the other nodes.</p>

<p>On your first server, we're going to create a separate file with settings for our cluster.</p>

<p>By default, MariaDB is configured to check the <code>/etc/mysql/conf.d</code> directory for additional files to augment its behavior.  We can create a file in this directory with all of our cluster-specific directives:</p>
<pre class="code-pre "><code langs="">sudo nano /etc/mysql/conf.d/cluster.cnf
</code></pre>
<p>Copy and paste the following configuration into the file.  We will explain what you need to change and what each piece means:</p>

<pre>
[mysqld]
query_cache_size=0
binlog_format=ROW
default-storage-engine=innodb
innodb_autoinc_lock_mode=2
query_cache_type=0
bind-address=0.0.0.0

# Galera Provider Configuration
wsrep_provider=/usr/lib/galera/libgalera_smm.so
#wsrep_provider_options="gcache.size=32G"

# Galera Cluster Configuration
wsrep_cluster_name="test_cluster"
wsrep_cluster_address="gcomm://<span class="highlight">first_ip</span>,<span class="highlight">second_ip</span>,<span class="highlight">third_ip</span>"

# Galera Synchronization Congifuration
wsrep_sst_method=rsync
#wsrep_sst_auth=user:pass

# Galera Node Configuration
wsrep_node_address="<span class="highlight">this_node_ip</span>"
wsrep_node_name="<span class="highlight">this_node_name</span>"
</pre>

<p>The first section modifies or re-asserts some MariaDB/MySQL settings that will allow MySQL to function correctly.</p>

<p>The section labeled "Galera Provider Configuration" is used to to configure the MariaDB components that provide a WriteSet replication API.  This means Galera in our case, since Galera is a wsrep (WriteSet Replication) provider.</p>

<p>We can specify general parameters to configure the initial replication environment.  You can find more about <a href="http://www.codership.com/wiki/doku.php?id=galera_parameters">Galera configuration options</a> here.  Generally, you don't need to do too much to get a working set though.</p>

<p>The "Galera Cluster Configuration" section defines the cluster that we will be creating.  It defines the cluster members by IP address or resolvable domain names and it creates a name for the cluster to ensure that members join the correct group.</p>

<p>The "Galera Synchronization Configuration" section defines how the cluster will communicate and synchronize data between members.  This is used only for the state transfer that happens when a node comes online.  For our initial setup, we are simply using rsync, because it pretty much does what we want without having to use exotic components.</p>

<p>The "Galera Node Configuration" section is used simply to clarify the IP address and the name of the current server.  This is helpful when trying to diagnose problems in logs and to be able to reference each server in multiple ways.  The name can be anything you would like.</p>

<p>When you are satisfied with your cluster configuration file, you should copy the contents to each of the individual nodes.</p>

<p>Remember to change the "Galera Node Configuration" section on each individual server.</p>

<p>When you have this configuration on each server, with the "Galera Node Configuration" section customized, you should save and close the files.</p>

<h2 id="copying-debian-maintenance-configuration">Copying Debian Maintenance Configuration</h2>

<hr />

<p>Currently, Ubuntu and Debian's MariaDB servers use a special maintenance user to do routine maintenance.  Some tasks that fall outside of the maintenance category also are run as this user, including important functions like stopping MySQL.</p>

<p>With our cluster environment being shared between the individual nodes, the maintenance user, who has randomly generated login credentials on each node, will be unable to execute commands correctly.  Only the initial server will have the correct maintenance credentials, since the others will attempt to use their local settings to access the shared cluster environment.</p>

<p>We can fix this by simply copying the contents of the maintenance file to each individual node:</p>

<p>On one of your servers, open the Debian maintenance configuration file:</p>
<pre class="code-pre "><code langs="">sudo nano /etc/mysql/debian.cnf
</code></pre>
<p>You will see a file that looks like this:</p>
<pre class="code-pre "><code langs="">[client]
host     = localhost
user     = debian-sys-maint
password = 03P8rdlknkXr1upf
socket   = /var/run/mysqld/mysqld.sock
[mysql_upgrade]
host     = localhost
user     = debian-sys-maint
password = 03P8rdlknkXr1upf
socket   = /var/run/mysqld/mysqld.sock
basedir  = /usr
</code></pre>
<p>We simply need to copy this information and paste it into the same file on each node.</p>

<p>On your second and third nodes, open the same file:</p>
<pre class="code-pre "><code langs="">sudo nano /etc/mysql/debian.cnf
</code></pre>
<p>Delete the current information and paste the parameters from the first node's configuration file into these other servers' files:</p>
<pre class="code-pre "><code langs="">[client]
host     = localhost
user     = debian-sys-maint
password = 03P8rdlknkXr1upf
socket   = /var/run/mysqld/mysqld.sock
[mysql_upgrade]
host     = localhost
user     = debian-sys-maint
password = 03P8rdlknkXr1upf
socket   = /var/run/mysqld/mysqld.sock
basedir  = /usr
</code></pre>
<p>They should be exactly the same now.  Save and close the files.</p>

<h2 id="start-the-cluster">Start the Cluster</h2>

<hr />

<p>To begin, we need to stop the running MariaDB service so that our cluster can be brought online.</p>

<p>This is easily done by typing this on each of the nodes:</p>
<pre class="code-pre "><code langs="">sudo service mysql stop
</code></pre>
<p>When all processes have ceased running, you must start up your first node again with a special parameter:</p>
<pre class="code-pre "><code langs="">sudo service mysql start --wsrep-new-cluster
</code></pre>
<p>With our cluster configuration, each node that comes online tries to connect to at least one other node specified in its configuration file to get its initial state.  Without the <code>--wsrep-new-cluster</code> parameter, this command would fail because the first node is unable to connect with any other nodes.</p>

<p>On each of the other nodes, you can now start MariaDB as you normally would.  They will search for any member of the cluster list that is online.  When they find the first node, they will join the cluster.</p>
<pre class="code-pre "><code langs="">sudo service mysql start
</code></pre>
<p>Your cluster should now be online and communicating. </p>

<h2 id="test-master-master-replication">Test Master-Master Replication</h2>

<hr />

<p>We've gone through the steps up to this point so that our cluster can perform master-master replication.  We need to test this out to see if the replication is working as expected.</p>

<p>On one of our our nodes, we can create a database and table like this:</p>

<pre>
mysql -u root -p<span class="highlight">mariadb_admin_password</span> -e 'CREATE DATABASE playground;'
mysql -u root -p<span class="highlight">mariadb_admin_password</span> -e 'CREATE TABLE playground.equipment ( id INT NOT NULL AUTO_INCREMENT, type VARCHAR(50), quant INT, color VARCHAR(25), PRIMARY KEY(id));'
</pre>

<p>This will create a database called <code>playground</code> and a table inside of this called <code>equipment</code>.</p>

<p>We can then insert our first item into this table by executing:</p>

<pre>
mysql -u root -p<span class="highlight">mariadb_admin_password</span> -e 'INSERT INTO playground.equipment (type, quant, color) VALUES ("slide", 2, "blue")'
</pre>

<p>We now have one value in our table.</p>

<p>From <em>another</em> node, we can read this data by typing:</p>

<pre>
mysql -u root -p<span class="highlight">mariadb_admin_password</span> -e 'SELECT * FROM playground.equipment;'
</pre>

<hr />
<pre class="code-pre "><code langs="">+----+-------+-------+-------+
| id | type  | quant | color |
+----+-------+-------+-------+
|  1 | slide |     2 | blue  |
+----+-------+-------+-------+
</code></pre>
<p>From this same node, we can write data to the cluster:</p>

<pre>
mysql -u root -p<span class="highlight">mariadb_admin_password</span> -e 'INSERT INTO playground.equipment (type, quant, color) VALUES ("swing", 10, "yellow");'
</pre>

<p>From our third node, we can read all of this data by querying the again:</p>

<pre>
mysql -u root -p<span class="highlight">mariadb_admin_password</span> -e 'SELECT * FROM playground.equipment;'
</pre>

<hr />
<pre class="code-pre "><code langs="">+----+-------+-------+--------+
| id | type  | quant | color  |
+----+-------+-------+--------+
|  1 | slide |     2 | blue   |
|  2 | swing |    10 | yellow |
+----+-------+-------+--------+
</code></pre>
<p>Again, we can add another value from this node:</p>

<pre>
mysql -u root -p<span class="highlight">mariadb_admin_password</span> -e 'INSERT INTO playground.equipment (type, quant, color) VALUES ("seesaw", 3, "green");'
</pre>

<p>Back on the first node, we can see that our data is available everywhere:</p>

<pre>
mysql -u root -p<span class="highlight">mariadb_admin_password</span> -e 'SELECT * FROM playground.equipment;'
</pre>

<hr />
<pre class="code-pre "><code langs="">+----+--------+-------+--------+
| id | type   | quant | color  |
+----+--------+-------+--------+
|  1 | slide  |     2 | blue   |
|  2 | swing  |    10 | yellow |
|  3 | seesaw |     3 | green  |
+----+--------+-------+--------+
</code></pre>
<p>As you can see, all of our servers can be written to.  This means that we have master-master replication functioning correctly.</p>

<h2 id="conclusion">Conclusion</h2>

<hr />

<p>At this point, you should have a Galera cluster configured on your servers.  This can help quite a bit with balancing load in write-intensive application environments.</p>

<p>If you plan on using a Galera cluster in a production situation, you may want to take a look at some of the other state snapshot transfer (sst) agents like "xtrabackup".  This will allow you to set up new nodes very quickly and without large interruptions to your active nodes.  This does not affect the actual replication, but is a concern when nodes are being initialized.</p>

<div class="author">By Justin Ellingwood</div>

    