<link rel='stylesheet' href='digital_ocean.css'><br> <br> 
      <div><p>MongoDB is a NoSQL database with great features like replication and sharding built in. This allows you to scale your database to as many servers as you would like by distributing content among them.</p>

<p>Before anything MongoDB related is installed, we need to ensure our hardware is correctly chosen and software is fine tuned.</p>

<h2>1. Hard Drives</h2>
<p>If you have a choice of selecting which hard drives you will have, go with enterprise grade dual SSD drives in RAID1.  As we have covered before, they are great on performance and actually save you money.</p> 

<p>Edit your /etc/fstab file in Linux and make sure to disable access time logging on your mount that will be used with MongoDB.  Add noatime in 4th column:</p>

<img src="https://assets.digitalocean.com/articles/scalable_mongodb/img1.png" />
 
<p>Re-mount the partition:</p>

<pre>[root@mongodb1 ~]# mount -o remount /</pre>

<p>Verify that the new settings took effect:</p>

<pre>[root@mongodb1 ~]# mount</pre>

<pre>/dev/sda on / type ext4 (rw,noatime)</pre>

<h2>2. CPU and Memory</h2>

<p>Setting MongoDB as a VM on a hypervisor would let you scale up RAM and CPU cores later on.  Amount of CPU cores and RAM that should be assigned depends on your infrastructure needs and budget.</p>

<h2>3. Optimization</h2>

<p>The most useful tip is to optimize your database queries:</p>

<ul><li>Add indexes for commonly searched or sorted queries. </li>
<li>Use MongoDB’s explain() command.</li>
<li>Limit search results and limit fields that are being returned.</li></ul>

<p>For testing purposes, we’ll spin up 3 droplets:</p>

<img src="https://assets.digitalocean.com/articles/scalable_mongodb/img2.png" />

<h3>Installation</h3>

<p>This procedure will be the same on mongodb1, mongodb2, and mongodb3. Installing MongoDB on CentOS is very simple. Add the following repository by editing</p> 

<pre>/etc/yum.repos.d/10gen.repo</pre>
<pre>[10gen]
name=10gen
baseurl=http://downloads-distro.mongodb.org/repo/redhat/os/x86_64
gpgcheck=0
enabled=1</pre>

<p>Now install the packages:</p>

<pre>[root@mongodb1 ~]# yum -y install mongo-10gen mongo-10gen-server</pre>

<p>Enable MongoDB to start on reboot, and start the service:</p>

<pre>[root@mongodb1 ~]# chkconfig mongod on && service mongod start</pre>

<pre>Starting mongod: forked process: 1387
all output going to: /var/log/mongo/mongod.log
child process started successfully, parent exiting
                                                           [  OK  ]</pre>

<p>Now you should be able to see statistics on http://SERVER:28017/</p>

<h3>Setting up Master-Slave replica set</h3>

<p>We’ll assign mongodb1 as a master server.  Add “master = true” to /etc/mongod.conf and do</p>
<pre>service mongod restart</pre>

<img src="https://assets.digitalocean.com/articles/scalable_mongodb/img3.png" />
 
<p>While mongodb2 and mongodb3 will be setup as slaves.
Add “slave=true”, “source = mongodb1” to /etc/mongod.conf and do</p>

 <pre>service mongod restart</pre>

<img src="https://assets.digitalocean.com/articles/scalable_mongodb/img4.png" />
 
<p>Now we should secure this database with a password or add iptables rules to ports 27017 (MongoDB) and 28017 (Web interface).</p>

<p>To create a user with password:</p>

<pre>> use test</pre>
<pre>> db.addUser('admin', 'password');
{
        "user" : "admin",
        "readOnly" : false,
        "pwd" : "90f500568434c37b61c8c1ce05fdf3ae",
        "_id" : ObjectId("50eaae88790af41ffffdcc58")
}</pre>

<p>We should also add firewall rules to restrict to other MongoDB servers, our IP, and save:</p>

<pre>[root@mongodb1 ~]# iptables -N MongoDB
[root@mongodb1 ~]# iptables -I INPUT -s 0/0 -p tcp --dport 27017 -j MongoDB
[root@mongodb1 ~]# iptables -I INPUT -s 0/0 -p tcp --dport 28017 -j MongoDB
[root@mongodb1 ~]# iptables -I MongoDB -s 127.0.0.1 -j ACCEPT
[root@mongodb1 ~]# iptables -I MongoDB -s 192.34.57.64 -j ACCEPT
[root@mongodb1 ~]# iptables -I MongoDB -s 192.34.56.123 -j ACCEPT
[root@mongodb1 ~]# iptables -I MongoDB -s 192.34.57.162 -j ACCEPT
[root@mongodb1 ~]# iptables -A MongoDB -s 0/0 -j DROP
[root@mongodb1 ~]# /etc/init.d/iptables save</pre>
<pre>iptables: Saving firewall rules to /etc/sysconfig/iptables:[  OK  ]</pre>

<p>Repeat this procedure on your other MongoDB servers (mongodb2, mongodb3).</p>

<p>If you are using PHP for your frontend, you would need to install MongoDB module for PHP:</p>

<pre>[root@webserver ~]# pecl install mongo
[root@webserver ~]# echo extension=mongo.so >> `php -i | grep /php.ini | awk '{print $5}'`
[root@webserver ~]# service httpd restart</pre>

<h3>Populate your database with data</h3>

<p>Now we can begin testing our new setup.  You can access the database from command shell by typing mongo :</p>

<pre>[root@mongodb1 ~]# mongo</pre>
<pre>MongoDB shell version: 2.2.2
connecting to: test</pre>


<p>Lets enter New York Times Bestsellers list into the database for testing:</p>

<pre>> db.books.save( { title: 'Safe Haven', author: 'Nicholas Sparks' } )
> db.books.save( { title: 'Gone Girl', author: 'Gillian Flynn' } )
> db.books.save( { title: 'The Coincidence Of Callie And Kayden', author: 'Jessica Sorensen' } )
> db.books.save( { title: 'Fifty Shades of Grey', author: 'E.L. James' } )
> db.books.save( { title: 'Hopeless', author: 'Colleen Hoover' } )</pre>

<p>To display all results:</p>

<pre>> db.books.find()
{ "_id" : ObjectId("50eaaa4b633625147f205994"), "title" : "Safe Haven", "author" : "Nicholas Sparks" }
{ "_id" : ObjectId("50eaaa62633625147f205995"), "title" : "Gone Girl", "author" : "Gillian Flynn" }
{ "_id" : ObjectId("50eaaa8d633625147f205996"), "title" : "The Coincidence Of Callie And Kayden", "author" : "Jessica Sorensen" }
{ "_id" : ObjectId("50eaaaa0633625147f205997"), "title" : "Fifty Shades of Grey", "author" : "E.L. James" }
{ "_id" : ObjectId("50eaaab3633625147f205998"), "title" : "Hopeless", "author" : "Colleen Hoover" }</pre>

<p><b>You should be able to see the same entries on mongodb2 and mongodb3 since they are a replica sets:</b></p>

<img src="https://assets.digitalocean.com/articles/scalable_mongodb/img5.png" />

<img src="assets.digitalocean.com/articles/scalable_mongodb/img6.png" />
 

<p>You could’ve entered all kinds of values for these books, such as publisher’s name, ISBN number, average customer rating, written language, and so on.  In order to optimize your queries, however, it is best to limit number of results, and number of fields being returned.</p>  

<p>For example, to return only 2 results we would use limit() at the end:</p>

<pre>> db.books.find( {}, { title : 1 , author: 1 } ).sort( { timestamp : -1 } ).limit(2)
{ "_id" : ObjectId("50eaaa4b633625147f205994"), "title" : "Safe Haven", "author" : "Nicholas Sparks" }
{ "_id" : ObjectId("50eaaa62633625147f205995"), "title" : "Gone Girl", "author" : "Gillian Flynn" }</pre>

<p>Once you have reached maximum capacity for your current setup, you can begin sharding your database.  We will cover this in a future post.</p>

<div class="author">By Bulat Khamitov</div></div>
    