<link rel='stylesheet' href='digital_ocean.css'><br> <br> 
      <p>MongoDB is one of the most popular NoSQL database engines. It is famous for being scalable, powerful, reliable and easy to use. In this article we'll show you how to import and export your MongoDB databases.</p>

<p>We should make clear that by import and export in this article we mean dealing with data in a human-readable format, compatible with other software products. In contrast, the backup and restore operations create or use MongoDB specific binary data, which preserves not only the consistency and integrity of your data but also its specific MongoDB attributes. Thus, for migration its usually preferable to use backup and restore as long as the source and target systems are compatible. Backup, restore, and migration are beyond the scope of this article — refer to <a href="https://indiareads/community/tutorials/how-to-back-up-restore-and-migrate-a-mongodb-database-on-ubuntu-14-04">How To Back Up, Restore, and Migrate a MongoDB Database on Ubuntu 14.04</a>.</p>

<h2 id="prerequisites">Prerequisites</h2>

<p>Before following this tutorial, please make sure you complete the following prerequisites:</p>

<ul>
<li>Ubuntu 14.04 Droplet</li>
<li>Non-root sudo user. Check out <a href="https://indiareads/community/tutorials/initial-server-setup-with-ubuntu-14-04">Initial Server Setup with Ubuntu 14.04</a> for details.</li>
<li>MongoDB installed and configured using the article <a href="https://indiareads/community/tutorials/how-to-install-mongodb-on-ubuntu-14-04">How to install MongoDB on Ubuntu 14.04</a>.</li>
</ul>

<p>Except otherwise noted, all of the commands that require root privileges in this tutorial should be run as a non-root user with sudo privileges.</p>

<h2 id="understanding-the-basics">Understanding the Basics</h2>

<p>Before continuing further with this article some basic understanding on the matter is needed. If you have experience with popular relational database systems such as MySQL, you may find some similarities when working with MongoDB.</p>

<p>The first thing you should know is that MongoDB uses <a href="http://json.org/">json</a> and bson (binary json) formats for storing its information. Json is the human readable format which is perfect for exporting and, eventually, importing your data. You can further manage your exported data with any tool which supports json, including a simple text editor. </p>

<p>An example json document looks like this:</p>
<div class="code-label " title="Example of json Format">Example of json Format</div><pre class="code-pre "><code langs="">{"address":[
    {"building":"1007", "street":"Park Ave"},
    {"building":"1008", "street":"New Ave"},
]}
</code></pre>
<p>Json is very convenient to work with, but it does not support all the data types available in bson. This means that there will be the so called 'loss of fidelity' of the information if you use json. That's why for backup / restore it's better to use the binary bson which would be able to better restore your MongoDB database.</p>

<p>Second, you don't have to worry about explicitly creating a MongoDB database. If the database you specify for import doesn't already exist, it is automatically created. Even better is the case with the collections' (database tables) structure. In contrast to other database engines, in MongoDB the structure is again automatically created upon the first document (database row) insert.</p>

<p>Third, in MongoDB reading or inserting large amounts of data, such as for the tasks of this article, can be resource intensive and consume much of the CPU, memory, and disk space. This is something critical considering that MongoDB is frequently used for large databases and Big Data. The simplest solution to this problem is to run the exports / backups during the night.</p>

<p>Fourth, information consistency could be problematic if you have a busy MongoDB server where the information changes during the database export process. There is no simple solution to this problem, but at the end of this article, you will see recommendations to further read about replication.</p>

<h2 id="importing-information-into-mongodb">Importing Information Into MongoDB</h2>

<p>To learn how importing information into MongoDB works let's use a popular sample MongoDB database about restaurants. It's in .json format and can be downloaded using <code>wget</code> like this:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">wget https://raw.githubusercontent.com/mongodb/docs-assets/primer-dataset/primer-dataset.json
</li></ul></code></pre>
<p>Once the download completes you should have a file called <code>primer-dataset.json</code> (12 MB size) in the current directory. Let's import the data from this file into a new database called <code>newdb</code> and into a collection called <code>restaurants</code>. For importing we'll use the command <code>mongoimport</code> like this:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo mongoimport --db newdb --collection restaurants --file primer-dataset.json
</li></ul></code></pre>
<p>The result should look like this:</p>
<div class="code-label " title="Output of mongoimport">Output of mongoimport</div><pre class="code-pre "><code langs="">2016-01-17T14:27:04.806-0500    connected to: localhost
2016-01-17T14:27:07.315-0500    imported 25359 documents
</code></pre>
<p>As the above command shows, 25359 documents have been imported. Because we didn't have a database called <code>newdb</code>, MongoDB created it automatically.</p>

<p>Let's verify the import by connecting to the newly created MongoDB database called <code>newdb</code> like this:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo mongo newdb
</li></ul></code></pre>
<p>You are now connected to the newly created <code>newdb</code> database instance. Notice that your prompt has changed, indicating that you are connected to the database.</p>

<p>Count the documents in the restaurants collection with the command:</p>
<pre class="code-pre custom_prefix"><code langs=""><ul class="prefixed"><li class="line" prefix=">">db.restaurants.count()
</li></ul></code></pre>
<p>The result should show be <code>25359</code>, exactly the number of the imported documents. For an even better check you can select the first document from the restaurants collection like this:</p>
<pre class="code-pre custom_prefix"><code langs=""><ul class="prefixed"><li class="line" prefix=">">db.restaurants.findOne() 
</li></ul></code></pre>
<p>The result should look like this:</p>
<div class="code-label " title="Output of db.restaurants.findOne()">Output of db.restaurants.findOne()</div><pre class="code-pre "><code langs="">{
        "_id" : ObjectId("569beb098106480d3ed99926"),
        "address" : {
                "building" : "1007",
                "coord" : [
                        -73.856077,
                        40.848447
                ],
                "street" : "Morris Park Ave",
                "zipcode" : "10462"
        },
        "borough" : "Bronx",
        "cuisine" : "Bakery",
        "grades" : [
                {
                        "date" : ISODate("2014-03-03T00:00:00Z"),
                        "grade" : "A",
                        "score" : 2
                },
...
        ],
        "name" : "Morris Park Bake Shop",
        "restaurant_id" : "30075445"
}
</code></pre>
<p>Such a detailed check could reveal problems with the documents such as their content, encoding, etc. The json format uses <code>UTF-8</code> encoding and your exports and imports should be in that encoding. Have this in mind if you edit manually the json files. Otherwise, MongoDB will automatically handle it for you.</p>

<p>To exit the MongoDB prompt, type <code>exit</code> at the prompt:</p>
<pre class="code-pre custom_prefix"><code langs=""><ul class="prefixed"><li class="line" prefix=">">exit
</li></ul></code></pre>
<p>You will be returned to the normal command line prompt as your non-root user.</p>

<h2 id="exporting-information-from-mongodb">Exporting Information From MongoDB</h2>

<p>As we have previously mentioned, by exporting MongoDB information you can acquire a human readable text file with your data. By default, information is exported in json format but you can also export to csv (comma separated value). </p>

<p>To export information from MongoDB, use the command <code>mongoexport</code>. It allows you to export a very fine-grained export so that you can specify a database, a collection, a field, and even use a query for the export.</p>

<p>A simple <code>mongoexport</code> example would be to export the restaurants collection from the <code>newdb</code> database which we have previously imported. It can be done like this:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo mongoexport --db newdb -c restaurants --out newdbexport.json
</li></ul></code></pre>
<p>In the above command, we use <code>--db</code> to specify the database, <code>-c</code> for the collection and <code>--out</code> for the file in which the data will be saved.</p>

<p>The output of a successful <code>mongoexport</code> should look like this:</p>
<div class="code-label " title="Output of mongoexport">Output of mongoexport</div><pre class="code-pre "><code langs="">2016-01-20T03:39:00.143-0500    connected to: localhost
2016-01-20T03:39:03.145-0500    exported 25359 records
</code></pre>
<p>The above output shows that 25359 documents have been imported — the same number as of the imported ones.</p>

<p>In some cases you might need to export only a part of your collection. Considering the structure and content of the restaurants json file, let's export all the restaurants which satisfy the criteria to be situated in the Bronx borough and to have Chinese cuisine. If we want to get this information directly while connected to MongoDB, connect to the database again:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo mongo newdb
</li></ul></code></pre>
<p>Then, use this query:</p>
<pre class="code-pre custom_prefix"><code langs=""><ul class="prefixed"><li class="line" prefix=">">db.restaurants.find( { borough: "Bronx", cuisine: "Chinese" } )
</li></ul></code></pre>
<p>The results are displayed to the terminal. To exit the MongoDB prompt, type <code>exit</code> at the prompt:</p>
<pre class="code-pre custom_prefix"><code langs=""><ul class="prefixed"><li class="line" prefix=">">exit
</li></ul></code></pre>
<p>If you want to export the data from a sudo command line instead of while connected to the database, make the previous query part of the <code>mongoexport</code> command by specifying it for the <code>-q</code> argument like this:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo mongoexport --db newdb -c restaurants -q "{ borough: 'Bronx', cuisine: 'Chinese' }" --out Bronx_Chinese_retaurants.json
</li></ul></code></pre>
<p>Note that we are using single quotes inside the double quotes for the query conditions. If you use double quotes or special characters like <code>$</code> you will have to escape them with backslash (<code>\</code>) in the query. </p>

<p>If the export has been successful, the result should look like this:</p>
<div class="code-label " title="Output of mongoexport">Output of mongoexport</div><pre class="code-pre "><code langs="">2016-01-20T04:16:28.381-0500    connected to: localhost
2016-01-20T04:16:28.461-0500    exported 323 records
</code></pre>
<p>The above shows that 323 records have been exported, and you can find them in the <code>Bronx_Chinese_retaurants.json</code> file which we have specified. </p>

<h2 id="conclusion">Conclusion</h2>

<p>This article has introduced you to the essentials of importing and exporting information to and from a MongoDB database. You can continue further reading on <a href="https://indiareads/community/tutorials/how-to-back-up-restore-and-migrate-a-mongodb-database-on-ubuntu-14-04">How To Back Up, Restore, and Migrate a MongoDB Database on Ubuntu 14.04</a> and <a href="https://indiareads/community/tutorials/how-to-set-up-a-scalable-mongodb-database">How To Set Up a Scalable MongoDB Database</a>. </p>

<p>Replication is not only useful for scalability, but it's also important for the current topics. Replication allows you to continue running your MongoDB service uninterrupted from a slave MongoDB server while you are restoring the master one from a failure. Part of the replication is also the <a href="https://docs.mongodb.org/manual/core/replica-set-oplog/">operations log (oplog)</a>, which records all the operations that modify your data. You can use this log, just as you would use the binary log in MySQL, to restore your data after the last backup has taken place. Recall that backups usually take place during the night, and if you decide to restore a backup in the evening you will be missing all the updates since the last backup.</p>

    