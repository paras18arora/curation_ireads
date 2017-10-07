<link rel='stylesheet' href='digital_ocean.css'><br> <br> 
      <h3 id="introduction">Introduction</h3>

<p>MySQL cluster is a software technology which provides high availability and throughput. If you are already familiar with other cluster technologies, you will find MySQL cluster similar to them. In short, there is one or more management nodes which control the data nodes (where data is stored). After consulting with the management node, clients (MySQL clients, servers, or native APIs) connect directly to the data nodes.</p>

<p>You may wonder how MySQL replication is related to MySQL cluster. With the cluster there is no typical replication of data, but instead there is synchronization of the data nodes. For this purpose a special data engine must be used — NDBCluster (NDB). Think of the cluster as a single logical MySQL environment with redundant components. Thus, a MySQL cluster can participate in replication with other MySQL clusters.</p>

<p>MySQL cluster works best in a shared-nothing environment. Ideally, no two components should share the same hardware. For simplicity, and demonstration purposes, we'll limit ourselves to using only three Droplets. There will be two Droplets acting as data nodes which are syncing data between themselves. The third Droplet will be used for the cluster manager and at the same time for the MySQL server/client. If you have more Droplets, you can add more data nodes, separate the cluster manager from the MySQL server/client, and even add more Droplets as cluster managers and MySQL servers/clients.</p>

<p><img src="https://assets.digitalocean.com/articles/mysql_cluster/simple_mysql_cluster.png" alt="A simple MySQL cluster" /></p>

<h2 id="prerequisites">Prerequisites</h2>

<p>You will need a total of three Droplets — one Droplet for the MySQL cluster manager and the MySQL server/client and two Droplets for the redundant MySQL data nodes.</p>

<p>In the <strong>same IndiaReads data center</strong>, create the following Droplets with <strong>private networking enabled</strong>:</p>

<ul>
<li>Three Ubuntu 16.04 Droplets with a minimum of 1 GB RAM and <a href="https://indiareads/community/tutorials/how-to-set-up-and-use-digitalocean-private-networking">private networking</a> enabled</li>
<li>Non-root user with sudo privileges for each Droplet (<a href="https://indiareads/community/tutorials/initial-server-setup-with-ubuntu-16-04">Initial Server Setup with Ubuntu 16.04</a> explains how to set this up.)</li>
</ul>

<p>MySQL cluster stores a lot of information in RAM. Each Droplet should have at least 1GB of RAM.</p>

<p>As mentioned in the <a href="https://indiareads/community/tutorials/how-to-set-up-and-use-digitalocean-private-networking">private networking tutorial</a>, be sure to setup custom records for the 3 Droplets. For the sake of simplicity and convenience, we'll use the following custom records for each Droplet in the <code>/etc/hosts</code> file:</p>

<p><span class="highlight">10.XXX.XX.X</span>       node1.mysql.cluster<br />
<span class="highlight">10.YYY.YY.Y</span>       node2.mysql.cluster<br />
<span class="highlight">10.ZZZ.ZZ.Z</span>      manager.mysql.cluster</p>

<p>Please replace the highlighted IPs with the private IPs of your Droplets correspondingly. </p>

<p>Except otherwise noted, all of the commands that require root privileges in this tutorial should be run as a non-root user with sudo privileges.</p>

<h2 id="step-1-—-downloading-and-installing-mysql-cluster">Step 1 — Downloading and Installing MySQL Cluster</h2>

<p>At the time of writing this tutorial, the latest GPL version of the MySQL cluster is 7.4.11. The product is built on top of MySQL 5.6 and it includes:</p>

<ul>
<li>Cluster manager software</li>
<li>Data node manager software</li>
<li>MySQL 5.6 server and client binaries</li>
</ul>

<p>You can download the free, Generally Available (GA) MySQL cluster release from the <a href="http://dev.mysql.com/downloads/cluster/">official MySQL cluster download page</a>. From this page, choose the Debian Linux platform package, which is also suitable for Ubuntu. Also make sure to select the 32-bit or the 64-bit version depending on the architecture of your Droplets. Upload the installation package to each of your Droplets. </p>

<p>The installation instructions will be the same for all Droplets, so complete these steps on all 3 Droplets.</p>

<p>Before you start the installation, the <code>libaio1</code> package must be installed since it is a dependency:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo apt-get install libaio1
</li></ul></code></pre>
<p>After that, install the MySQL cluster package:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo dpkg -i mysql-cluster-gpl-7.4.11-debian7-x86_64.deb
</li></ul></code></pre>
<p>Now you can find the MySQL cluster installation in the directory <code>/opt/mysql/server-5.6/</code>. We'll be working especially with the bin directory (<code>/opt/mysql/server-5.6/bin/</code>) where all the binaries are.</p>

<p>The same installation steps should be performed on all three Droplets regardless of the fact that each will have different function — manager or data node.</p>

<p>Next, we will configure the MySQL cluster manager on each Droplet.</p>

<h2 id="step-2-—-configuring-and-starting-the-cluster-manager">Step 2 — Configuring and Starting the Cluster Manager</h2>

<p>In this step we'll configure the MySQL cluster manager (<code>manager.mysql.cluster</code>). Its proper configuration will ensure correct synchronization and load distribution among the data nodes. All commands should be executed on Droplet <code>manager.mysql.cluster</code>.</p>

<p>The cluster manager is the first component which has to be started in any cluster. It needs a configuration file which is passed as an argument to its binary file. For convenience, we'll use the file <code>/var/lib/mysql-cluster/config.ini</code> for its configuration. </p>

<p>On the <code>manager.mysql.cluster</code> Droplet, first create the directory where this file will reside (<code>/var/lib/mysql-cluster</code>):</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo mkdir /var/lib/mysql-cluster 
</li></ul></code></pre>
<p>Then create a file and start editing it with nano:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo nano /var/lib/mysql-cluster/config.ini
</li></ul></code></pre>
<p>This file should contain the following code:</p>
<div class="code-label " title=" /var/lib/mysql-cluster/config.ini"> /var/lib/mysql-cluster/config.ini</div><pre class="code-pre "><code langs="">[ndb_mgmd]
# Management process options:
hostname=manager.mysql.cluster  # Hostname of the manager
datadir=/var/lib/mysql-cluster  # Directory for the log files

[ndbd]
hostname=node1.mysql.cluster    # Hostname of the first data node
datadir=/usr/local/mysql/data   # Remote directory for the data files

[ndbd]
hostname=node2.mysql.cluster    # Hostname of the second data node
datadir=/usr/local/mysql/data   # Remote directory for the data files

[mysqld]
# SQL node options:
hostname=manager.mysql.cluster  # In our case the MySQL server/client is on the same Droplet as the cluster manager
</code></pre>
<p>For each of the above components we have defined a <code>hostname</code> parameter. This is an important security measure because only the specified hostname will be allowed to connect to the manager and participate in the cluster as per their designated role. </p>

<p>Furthermore, the <code>hostname</code> parameters specify on which interface the service will run. This matters, and is important for security, because in our case the above hostnames point to private IPs which we have specified in the <code>/etc/hosts</code> files. Thus, you cannot access any of the above services from outside of the private network.</p>

<p>In the above file you can add more redundant components such as data nodes (ndbd) or MySQL servers (mysqld) by just defining additional instances in the exactly the same manner.</p>

<p>Now you can start the manager for the first time by executing the <code>ndb_mgmd</code> binary and specifying the config file with the <code>-f</code> argument like this:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo /opt/mysql/server-5.6/bin/ndb_mgmd -f /var/lib/mysql-cluster/config.ini
</li></ul></code></pre>
<p>You should see a message about successful start similar to this:</p>
<pre class="code-pre "><code langs=""><div class="secondary-code-label " title="Output of ndb_mgmd">Output of ndb_mgmd</div>MySQL Cluster Management Server mysql-5.6.29 ndb-7.4.11
</code></pre>
<p>You would probably like to have the management service started automatically with the server. The GA cluster release doesn't come with a suitable startup script, but there are a few available online. For the beginning you can just add the start command to the <code>/etc/rc.local</code> file and the service will be automatically started during boot. First, though, you will have to make sure that <code>/etc/rc.local</code> is executed during the server startup. In Ubuntu 16.04 this requires running an additional command:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo systemctl enable rc-local.service
</li></ul></code></pre>
<p>Then open the file <code>/etc/rc.local</code> for editing:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo nano /etc/rc.local
</li></ul></code></pre>
<p>There add the start command before the <code>exit</code> line like this:</p>
<div class="code-label " title=" /etc/rc.local"> /etc/rc.local</div><pre class="code-pre "><code langs="">...
<span class="highlight">/opt/mysql/server-5.6/bin/ndb_mgmd -f /var/lib/mysql-cluster/config.ini</span>
exit 0
</code></pre>
<p>Save and exit the file.</p>

<p>The cluster manager does not have to run all the time. It can be started, stopped, and restarted without downtime for the cluster. It is required only during the initial startup of the cluster nodes and the MySQL server/client.</p>

<h2 id="step-3-—-configuring-and-starting-the-data-nodes">Step 3 — Configuring and Starting the Data Nodes</h2>

<p>Next we'll configure the data nodes (<code>node1.mysql.cluster</code> and <code>node2.mysql.cluster</code>) to store the data files and support properly the NDB engine. All commands should be executed on both nodes. You can start first with <code>node1.mysql.cluster</code> and then repeat exactly the same steps on <code>node2.mysql.cluster</code>.</p>

<p>The data nodes read the configuration from the standard MySQL configuration file <code>/etc/my.cnf</code> and more specifically the part after the line <code>[mysql_cluster]</code>. Create this file with nano and start editing it:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo nano /etc/my.cnf
</li></ul></code></pre>
<p>Specify the hostname of the manager like this:</p>
<div class="code-label " title=" /etc/my.cnf"> /etc/my.cnf</div><pre class="code-pre "><code langs="">[mysql_cluster]
ndb-connectstring=manager.mysql.cluster
</code></pre>
<p>Save and exit the file.</p>

<p>Specifying the location of the manager is the only configuration needed for the node engine to start. The rest of the configuration will be taken from manager directly. In our example the data node will find out that its data directory is <code>/usr/local/mysql/data</code> as per the manager's configuration. This directory has to be created on the node. You can do it with the command:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo mkdir -p /usr/local/mysql/data
</li></ul></code></pre>
<p>After that you can start the data node for the first time with the command:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo /opt/mysql/server-5.6/bin/ndbd
</li></ul></code></pre>
<p>After a successful start you should see a similar output:</p>
<pre class="code-pre "><code langs=""><div class="secondary-code-label " title="Output of ndbd">Output of ndbd</div>2016-05-11 16:12:23 [ndbd] INFO     -- Angel connected to 'manager.mysql.cluster:1186'
2016-05-11 16:12:23 [ndbd] INFO     -- Angel allocated nodeid: 2
</code></pre>
<p>You should have the ndbd service started automatically with the server. The GA cluster release doesn't come with a suitable startup script for this either. Just as we did for the cluster manager, let's add the startup command to the <code>/etc/rc.local</code> file. Again, you will have to make sure that <code>/etc/rc.local</code> is executed during the server startup with the command:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo systemctl enable rc-local.service
</li></ul></code></pre>
<p>Then open the file <code>/etc/rc.local</code> for editing:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo nano /etc/rc.local
</li></ul></code></pre>
<p>Add the start command before the <code>exit</code> line like this:</p>
<div class="code-label " title=" /etc/rc.local"> /etc/rc.local</div><pre class="code-pre "><code langs="">...
<span class="highlight">/opt/mysql/server-5.6/bin/ndbd</span>
exit 0
</code></pre>
<p>Save and exit the file.</p>

<p>Once you are finished with the first node, repeat exactly the same steps on the other node , which is <code>node2.mysql.cluster</code> in our example.</p>

<h2 id="step-4-—-configuring-and-starting-the-mysql-server-and-client">Step 4 — Configuring and Starting the MySQL Server and Client</h2>

<p>A standard MySQL server, such as the one that is available in Ubuntu's default apt repository, does not support the MySQL cluster engine NDB. That's why you need a custom MySQL server installation. The cluster package which we already installed on the three Droplets comes with a MySQL server and a client too. As already mentioned, we'll use the MySQL server and client on the management node (<code>manager.mysql.cluster</code>).</p>

<p>The configuration is stored again the default <code>/etc/my.cnf</code> file. On <code>manager.mysql.cluster</code>, open the configuration file:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo nano /etc/my.cnf
</li></ul></code></pre>
<p>Then add the following to it:</p>
<div class="code-label " title=" /etc/my.cnf"> /etc/my.cnf</div><pre class="code-pre "><code langs="">[mysqld]
<span class="highlight">ndbcluster</span> # run NDB storage engine
...
</code></pre>
<p>Save and exit the file.</p>

<p>As per the best practices, the MySQL server should run under its own user (<code>mysql</code>) which belongs to its own group (again <code>mysql</code>). So let's create first the group:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo groupadd mysql
</li></ul></code></pre>
<p>Then create the <code>mysql</code> user belonging to this group and make sure it cannot use shell by setting its shell path to <code>/bin/false</code> like this:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo useradd -r -g mysql -s /bin/false mysql
</li></ul></code></pre>
<p>The last requirement for the custom MySQL server installation is to create the default database. You can do it with the command:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo /opt/mysql/server-5.6/scripts/mysql_install_db --user=mysql
</li></ul></code></pre>
<p>For starting the MySQL server we'll use the startup script from <code>/opt/mysql/server-5.6/support-files/mysql.server</code>. Copy it to the default init scripts directory under the name <code>mysqld</code> like this:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo cp /opt/mysql/server-5.6/support-files/mysql.server /etc/init.d/mysqld
</li></ul></code></pre>
<p>Enable the startup script and add it to the default runlevels with the command:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo systemctl enable mysqld.service
</li></ul></code></pre>
<p>Now we can start the MySQL server for the first time manually with the command:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo systemctl start mysqld
</li></ul></code></pre>
<p>As a MySQL client we'll use again the custom binary which comes with the cluster installation. It has the following path: <code>/opt/mysql/server-5.6/bin/mysql</code>. For convenience let's create a symbolic link to it in the default <code>/usr/bin</code> path:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo ln -s /opt/mysql/server-5.6/bin/mysql /usr/bin/
</li></ul></code></pre>
<p>Now you can start the client from the command line by simply typing <code>mysql</code> like this:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">mysql
</li></ul></code></pre>
<p>You should see an output similar to:</p>
<pre class="code-pre "><code langs=""><div class="secondary-code-label " title="Output of ndb_mgmd">Output of ndb_mgmd</div>Welcome to the MySQL monitor.  Commands end with ; or \g.
Your MySQL connection id is 3
Server version: 5.6.29-<span class="highlight">ndb-7.4.11-cluster-gpl</span> MySQL Cluster Community Server (GPL)
</code></pre>
<p>To exit the MySQL prompt, simply type <code>quit</code> or press simultaneously <code>CTRL-D</code>.</p>

<p>The above is the first check to show that the MySQL cluster, server, and client are working. Next we'll go through more detailed tests to confirm the cluster is working properly.</p>

<h3 id="testing-the-cluster">Testing the Cluster</h3>

<p>At this point our simple MySQL cluster with one client, one server, one manager, and two data nodes should be complete. From the cluster manager Droplet (<code>manager.mysql.cluster</code>) open the management console with the command:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo /opt/mysql/server-5.6/bin/ndb_mgm
</li></ul></code></pre>
<p>Now the prompt should change to the cluster management console. It looks like this:</p>
<pre class="code-pre "><code langs=""><div class="secondary-code-label " title="Inside the ndb_mgm console">Inside the ndb_mgm console</div>-- NDB Cluster -- Management Client --
ndb_mgm>
</code></pre>
<p>Once inside the console execute the command <code>SHOW</code> like this:</p>
<pre class="code-pre custom_prefix"><code langs=""><ul class="prefixed"><li class="line" prefix="ndb_mgm>">SHOW
</li></ul></code></pre>
<p>You should see output similar to this one:</p>
<pre class="code-pre "><code langs=""><div class="secondary-code-label " title="Output of ndb_mgm">Output of ndb_mgm</div>Connected to Management Server at: manager.mysql.cluster:1186
Cluster Configuration
---------------------
[ndbd(NDB)]     2 node(s)
id=2    @10.135.27.42  (mysql-5.6.29 ndb-7.4.11, Nodegroup: 0, *)
id=3    @10.135.27.43  (mysql-5.6.29 ndb-7.4.11, Nodegroup: 0)

[ndb_mgmd(MGM)] 1 node(s)
id=1    @10.135.27.51  (mysql-5.6.29 ndb-7.4.11)

[mysqld(API)]   1 node(s)
id=4    @10.135.27.51  (mysql-5.6.29 ndb-7.4.11)
</code></pre>
<p>The above shows that there are two data nodes with ids 2 and 3. They are active and connected. There is also one management node with id 1 and one MySQL server with id 4. You can find more information about each id by typing its number with the command <code>STATUS</code> like this:</p>
<pre class="code-pre custom_prefix"><code langs=""><ul class="prefixed"><li class="line" prefix="ndb_mgm>">2 STATUS
</li></ul></code></pre>
<p>The above command would show you the status of node 2 along with its MySQL and NDB versions:</p>
<pre class="code-pre "><code langs=""><div class="secondary-code-label " title="Output of ndb_mgm">Output of ndb_mgm</div>Node 2: started (mysql-5.6.29 ndb-7.4.11)
</code></pre>
<p>To exit the management console type <code>quit</code>.</p>

<p>The management console is very powerful and gives you many other options for managing the cluster and its data, including creating an online backup. For more information check the <a href="http://dev.mysql.com/doc/refman/5.6/en/mysql-cluster-management.html" title="here">official documentation</a>.</p>

<p>Let's have a test with the MySQL client now. From the same Droplet, start the client with the <code>mysql</code> command for the MySQL root user. Please recall that we have created a symlink to it earlier.</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">mysql -u root
</li></ul></code></pre>
<p>\Your console will change to the MySQL client console. Once inside the MySQL client, run the command:</p>
<pre class="code-pre custom_prefix"><code langs=""><ul class="prefixed"><li class="line" prefix="mysql>">SHOW ENGINE NDB STATUS \G
</li></ul></code></pre>
<p>Now you should see all the information about the NDB cluster engine starting with the connection details:</p>
<pre class="code-pre "><code langs=""><div class="secondary-code-label " title="Output of mysql">Output of mysql</div>
*************************** 1. row ***************************
  Type: ndbcluster
  Name: connection
Status: cluster_node_id=4, connected_host=manager.mysql.cluster, connected_port=1186, number_of_data_nodes=2, <span class="highlight">number_of_ready_data_nodes=2</span>, connect_count=0
...
</code></pre>
<p>The most important information from above is the number of ready nodes — 2. This redundancy will allow your MySQL cluster to continue operating even if one of the data nodes fails while. At the same time your SQL queries will be load balanced to the two nodes.</p>

<p>You can try shutting down one of the data nodes in order to test the cluster stability. The simplest thing would be just to restart the whole Droplet in order to have a full test of the recovery process. You will see the value of <code>number_of_ready_data_nodes</code> change to <code>1</code> and back to <code>2</code> again as the node is restarted.</p>

<h3 id="working-with-the-ndb-engine">Working with the NDB Engine</h3>

<p>To see how the cluster really works, let's create a new table with the NDB engine and insert some data into it. Please note that in order to use the cluster functionality, the engine must be NDB. If you use InnoDB (default) or any other engine other than NDB, you will not make use of the cluster. </p>

<p>First, let's create a database called <code>cluster</code> with the command:</p>
<pre class="code-pre custom_prefix"><code langs=""><ul class="prefixed"><li class="line" prefix="mysql>">CREATE DATABASE cluster;
</li></ul></code></pre>
<p>Next, switch to the new database:</p>
<pre class="code-pre custom_prefix"><code langs=""><ul class="prefixed"><li class="line" prefix="mysql>">USE cluster;
</li></ul></code></pre>
<p>Now, create a simple table called <code>cluster_test</code> like this:</p>
<pre class="code-pre custom_prefix"><code langs=""><ul class="prefixed"><li class="line" prefix="mysql>">CREATE TABLE cluster_test (name VARCHAR(20), value VARCHAR(20)) ENGINE=<span class="highlight">ndbcluster</span>;
</li></ul></code></pre>
<p>We have explicitly specified above the engine <code>ndbcluster</code> in order to make use of the cluster. Next, we can start inserting data with a query like this:</p>
<pre class="code-pre custom_prefix"><code langs=""><ul class="prefixed"><li class="line" prefix="mysql>">INSERT INTO cluster_test (name,value) VALUES('some_name','some_value');
</li></ul></code></pre>
<p>To verify the data has been inserted, run a select query like this:</p>
<pre class="code-pre custom_prefix"><code langs=""><ul class="prefixed"><li class="line" prefix="mysql>">SELECT * FROM cluster_test;
</li></ul></code></pre>
<p>When you are inserting and selecting data like this, you are load-balancing your queries between all the available data node, which are two in our example. With this scaling out you benefit both in terms of stability and performance.</p>

<h2 id="conclusion">Conclusion</h2>

<p>As we have seen in this article, setting up a MySQL cluster can be simple and easy. Of course, there are many more advanced options and features which are worth mastering before bringing the cluster to your production environment. As always, make sure to have an adequate testing process because some problems could be very hard to solve later. For more information and further reading please go to the official documentation for <a href="http://dev.mysql.com/doc/refman/5.6/en/mysql-cluster.html">MySQL cluster</a>.</p>

    