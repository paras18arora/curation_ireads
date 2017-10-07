<link rel='stylesheet' href='digital_ocean.css'><br> <br> 
      <h2 id="introduction">Introduction</h2>

<p>In this tutorial, we will show how to use Node.js to connect to a MongoDB database in a VPS and do some basic data manipulations.</p>

<p>Here are the following software components that will be used:</p>

<ul>
<li>Ubuntu 12.04 x32 VPS</li>
<li>MongoDB v2.4.6</li>
<li>Node.js v0.10.20</li>
<li>The MongoDB Node.js driver </li>
</ul>

<h2 id="mongodb">MongoDB</h2>

<p>"MongoDB is an open source document-oriented database that provides high performance, high availability, and easy scalability"</p>

<p>If you are not familiar with MongoDB or don't have it installed, please check out this <a href="https://indiareads/community/articles/how-to-install-mongodb-on-ubuntu-12-04">tutorial</a> first.</p>

<p>Let's verify that the MongoDB process is running:</p>
<pre class="code-pre "><code langs="">ps -ef | grep mongo
</code></pre>
<p>The output should look something like this:</p>
<pre class="code-pre "><code langs="">mongodb   1307  1  0 02:27 ?        00:00:01 /usr/bin/mongod --config /etc/mongodb.conf 
</code></pre>
<p>If it's not running, issue the following command from the MongoDB bin directory:</p>
<pre class="code-pre "><code langs="">mongod
</code></pre>
<p>There is a console client that comes with MongoDB. To launch it, issue the following command:</p>
<pre class="code-pre "><code langs="">mongo
</code></pre>
<p>You will see an output like this (you can ignore the warnings):</p>
<pre class="code-pre "><code langs="">MongoDB shell version: 2.4.4
connecting to: test
Server has startup warnings:
Mon Oct  7 20:40:35.209 [initandlisten]
Mon Oct  7 20:40:35.209 [initandlisten] ** WARNING: soft rlimits too low. Number of files is 256, should be at least 1000
>
</code></pre>
<p>Run this command to list the existing databases:</p>
<pre class="code-pre "><code langs="">show dbs
</code></pre>
<p>Run this command to display the selected database:</p>
<pre class="code-pre "><code langs="">db
</code></pre>
<p>Run the following command to switch to the "test" database and display the collections it contains:</p>
<pre class="code-pre "><code langs="">use test
show collections 
</code></pre>
<p>Here is a list of commands that you can use in the console client, you can get the full list of commands by typing "help":</p>
<pre class="code-pre "><code langs="">show dbs                    #show database names
show collections          #show collections in current database
show users                 # show users in current database
show profile                # show most recent system.profile entries with time >= 1ms
show logs                   # show the accessible logger names
show log [name]          # prints out the last segment of log in memory, 'global' is default
use <db_name>          #  set current database
db.foo.find()                # list objects in collection foo
db.foo.find( { a : 1 } )    #list objects in foo where a == 1
it                                #result of the last line evaluated; use to further iterate
exit                            #quit the mongo shell
</code></pre>
<h2 id="node-js">Node.js</h2>

<p>"Node.js is a platform built on Chrome's JavaScript runtime for easily building fast, scalable network applications. Node.js uses an event-driven, non-blocking I/O model that makes it lightweight and efficient, perfect for data-intensive real-time applications that run across distributed devices."</p>

<p>If you don't have this installed, please take the time to follow the instructions in this tutorial first.</p>

<p>Let's verify that the Node.js process is running:</p>
<pre class="code-pre "><code langs="">node -v
</code></pre>
<p>You should see the Node.js version as the command output.</p>

<h2 id="the-mongodb-node-js-driver">The MongoDB Node.js Driver</h2>

<hr />

<p>This driver is the officially supported Node.js driver for MongoDB. It is written in pure JavaScript and provides a native asynchronous Node.js interface to MongoDB. </p>

<p>Use the node package manager "npm" to install the driver:</p>
<pre class="code-pre "><code langs="">npm install mongodb
</code></pre>
<h2 id="connecting-to-mongodb-and-performing-data-manipulation">Connecting to MongoDB and Performing Data Manipulation</h2>

<hr />

<p>Now it is time to write the code that will allow your Node.js application to connect to MongoDB. Three operations will be covered: connecting, writing, and reading from the database.</p>

<p>To be able to execute your code, we will need to create a new file, we'll call it: 'app.js'.</p>

<p>Once you have the file, use your preferred editor to add the following code:</p>
<pre class="code-pre "><code langs="">var MongoClient = require('mongodb').MongoClient
    , format = require('util').format;
MongoClient.connect('mongodb://127.0.0.1:27017/test', function (err, db) {
    if (err) {
        throw err;
    } else {
        console.log("successfully connected to the database");
    }
    db.close();
});
</code></pre>
<p>Execute the app.js file by typing the following command:</p>
<pre class="code-pre "><code langs="">node app.js
</code></pre>
<p>You should see the following string in the output: successfully connected to the database.</p>

<p>Now let's add some logic that inserts things to a new collection named “test_insert”:</p>
<pre class="code-pre "><code langs="">var MongoClient = require('mongodb').MongoClient
    , format = require('util').format;

MongoClient.connect('mongodb://127.0.0.1:27017/test', function(err, db) {
    if(err) throw err;

    var collection = db.collection('test_insert');
    collection.insert({a:2}, function(err, docs) {
        collection.count(function(err, count) {
            console.log(format("count = %s", count));
            db.close();
        });
    });
});

</code></pre>
<p>Add another block of code that verifies that the data made it to the database:</p>
<pre class="code-pre "><code langs="">var MongoClient = require('mongodb').MongoClient
    , format = require('util').format;

MongoClient.connect('mongodb://127.0.0.1:27017/test', function(err, db) {
    if(err) throw err;

    var collection = db.collection('test_insert');
    collection.insert({a:2}, function(err, docs) {
        collection.count(function(err, count) {
            console.log(format("count = %s", count));
        });
    });

    // Locate all the entries using find
    collection.find().toArray(function(err, results) {
        console.dir(results);
        // Let's close the db
        db.close();
    });
});
</code></pre>
<p>Congratulations! You now have the ability to connect, insert, and read data from your MongoDB database in a VPS using a Node.js application!</p>

<h2 id="resources">Resources</h2>

<hr />

<ul>
<li>http://www.nodejs.org/ </li>
<li>http://docs.mongodb.org/ecosystem/drivers/node-js/</li>
<li>http://www.mongodb.org/<br /></li>
<li>https://npmjs.org/</li>
</ul>

<div class="author">Submitted by: <a href="http://www.geberlabs.com/">Adil Mezghouti </a></div>

    