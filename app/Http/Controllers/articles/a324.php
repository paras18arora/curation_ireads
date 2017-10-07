<link rel='stylesheet' href='digital_ocean.css'><br><img src=https://community-cdn-digitalocean-com.global.ssl.fastly.net/assets/tutorials/images/large/mongodb_tw.jpg?1429195247/> <br> 
      <h2 id="introduction">Introduction</h2>

<p>Elasticsearch facilitates full text search of your data, while MongoDB excels at storing it. Using MongoDB to store your data and Elasticsearch for search is a common architecture.</p>

<p>Many times, you might find the need to migrate data from MongoDB to Elasticsearch in bulk. Writing your own program for this, although a good exercise, can be a tedious task. There is a wonderful open source utility called Transporter, developed by <a href="https://www.compose.io/">Compose</a> (a cloud platform for databases), that takes care of this task very efficiently.</p>

<p>This tutorial shows you how to use the open-source utility Transporter to quickly copy data from MongoDB to Elasticsearch with custom transformations.</p>

<h3 id="goals">Goals</h3>

<p>In this article, we are going to cover how to copy data from MongoDB to Elasticsearch on <strong>Ubuntu 14.04</strong>, using the Transporter utility.</p>

<p>We'll start with a quick overview showing you how to install MongoDB and Elasticsearch, although we won't go into detail about data modeling in the two systems. Feel free to skim through the installation steps quickly if you have already installed both of them.</p>

<p>Then we'll move on to Transporter.</p>

<p>The instructions are similar for other versions of Ubuntu, as well as other Linux distributions.</p>

<h3 id="prerequisites">Prerequisites</h3>

<p>Please complete the following prerequisites.</p>

<ul>
<li>Ubuntu 14.04 Droplet</li>
<li><a href="https://indiareads/community/tutorials/how-to-add-and-delete-users-on-an-ubuntu-14-04-vps">sudo</a> user</li>
</ul>

<h2 id="step-1-—-installing-mongodb">Step 1 — Installing MongoDB</h2>

<p>Import the MongoDB repository's public key.</p>
<pre class="code-pre "><code langs="">sudo apt-key adv --keyserver hkp://keyserver.ubuntu.com:80 --recv 7F0CEB10
</code></pre>
<p>Create a list file for MongoDB.</p>
<pre class="code-pre "><code langs="">echo 'deb http://downloads-distro.mongodb.org/repo/ubuntu-upstart dist 10gen' | sudo tee /etc/apt/sources.list.d/mongodb.list
</code></pre>
<p>Reload the local package database.</p>
<pre class="code-pre "><code langs="">sudo apt-get update
</code></pre>
<p>Install the MongoDB packages:</p>
<pre class="code-pre "><code langs="">sudo apt-get install -y mongodb-org
</code></pre>
<p>Notice that each package contains the associated version number.</p>

<p>Once the installation completes you can start, stop, and check the status of the service. It will start automatically after installation.</p>

<p>Try to connect to the MongoDB instance running as service:</p>
<pre class="code-pre "><code langs="">mongo
</code></pre>
<p>If it is up and running, you will see something like this:</p>
<pre class="code-pre "><code langs="">MongoDB shell version: 2.6.9
connecting to: test
Welcome to the MongoDB shell.
For interactive help, type "help".
For more comprehensive documentation, see
    http://docs.mongodb.org/
Questions? Try the support group
    http://groups.google.com/group/mongodb-user
</code></pre>
<p>This means the database server is running! You can exit now:</p>
<pre class="code-pre "><code langs="">exit
</code></pre>
<h2 id="step-2-—-installing-java">Step 2 — Installing Java</h2>

<p>Java is a prerequisite for Elasticsearch. Let's install it now.</p>

<p>First, add the repository:</p>
<pre class="code-pre "><code langs="">sudo apt-add-repository ppa:webupd8team/java
</code></pre>
<p>Update your package lists again:</p>
<pre class="code-pre "><code langs="">sudo apt-get update
</code></pre>
<p>Install Java:</p>
<pre class="code-pre "><code langs="">sudo apt-get install oracle-java8-installer
</code></pre>
<p>When prompted to accept the license, select <code><Ok></code> and then <code><Yes></code>.</p>

<h2 id="step-3-—-installing-elasticsearch">Step 3 — Installing Elasticsearch</h2>

<p>Now we'll install Elasticsearch.</p>

<p>First, create a new directory where you will install the search software, and move into it.</p>
<pre class="code-pre "><code langs="">mkdir ~/utils
cd ~/utils
</code></pre>
<p>Visit Elasticsearch's <a href="https://www.elastic.co/downloads/elasticsearch">download page</a> to see the latest version.</p>

<p>Now download the latest version of Elasticsearch. At the time of writing this article, the latest version was 1.5.0. </p>
<pre class="code-pre "><code langs="">wget https://download.elasticsearch.org/elasticsearch/elasticsearch/elasticsearch-<span class="highlight">1.5.0</span>.zip
</code></pre>
<p>Install unzip:</p>
<pre class="code-pre "><code langs="">sudo apt-get install unzip
</code></pre>
<p>Unzip the archive:</p>
<pre class="code-pre "><code langs="">unzip elasticsearch-<span class="highlight">1.5.0</span>.zip
</code></pre>
<p>Navigate to the directory where you extracted it:</p>
<pre class="code-pre "><code langs="">cd elasticsearch-<span class="highlight">1.5.0</span>
</code></pre>
<p>Launch Elasticsearch by issuing the following command:</p>
<pre class="code-pre "><code langs="">bin/elasticsearch
</code></pre>
<p>It will take a few seconds for Elasticsearch to start up. You'll see some startup logs as it does. Elasticsearch will now be running in the terminal window.</p>

<blockquote>
<p><strong>Note:</strong> At some point you may want to run Elasticsearch as a service so you can control it with <code>sudo service elasticsearch restart</code> and similar commands; see this tutorial about <a href="https://indiareads/community/tutorials/the-upstart-event-system-what-it-is-and-how-to-use-it">Upstart</a> for tips. Alternately, you can install Elasticsearch from Ubuntu's repositories, although you'll probably get an older version.</p>
</blockquote>

<p>Keep this terminal open. Make another SSH connection to your server in <strong>another terminal window</strong> and check if your instance is up and running:</p>
<pre class="code-pre "><code langs="">curl -XGET http://localhost:9200
</code></pre>
<p>9200 is the default port for Elasticsearch. If everything goes well, you will see output similar to that shown below:</p>
<pre class="code-pre "><code langs="">{
  "status" : 200,
  "name" : "Northstar",
  "cluster_name" : "elasticsearch",
  "version" : {
    "number" : "1.5.0",
    "build_hash" : "927caff6f05403e936c20bf4529f144f0c89fd8c",
    "build_timestamp" : "2015-03-23T14:30:58Z",
    "build_snapshot" : false,
    "lucene_version" : "4.10.4"
  },
  "tagline" : "You Know, for Search"
}
</code></pre>
<blockquote>
<p><strong>Note</strong>: For the later part of this article, when you will be copying data, make sure that Elasticsearch is running (and on port 9200).</p>
</blockquote>

<h2 id="step-4-—-installing-mercurial">Step 4 — Installing Mercurial</h2>

<p>Next we'll install the revision control tool Mercurial.</p>
<pre class="code-pre "><code langs="">sudo apt-get install mercurial
</code></pre>
<p>Verify that Mercurial is installed correctly:</p>
<pre class="code-pre "><code langs="">hg
</code></pre>
<p>You will get the following output if it is installed correctly:</p>
<pre class="code-pre "><code langs="">Mercurial Distributed SCM

basic commands:

. . .

</code></pre>
<h2 id="step-5-—-installing-go">Step 5 — Installing Go</h2>

<p>Transporter is written in the Go language. So, you need to install <code>golang</code> on your system.</p>
<pre class="code-pre "><code langs="">sudo apt-get install golang
</code></pre>
<p>For Go to work properly, you need to set the following environment variables:</p>

<p>Create a folder for Go from your <code>$HOME</code> directory:</p>
<pre class="code-pre "><code langs="">mkdir ~/go; echo "export GOPATH=$HOME/go" >> ~/.bashrc
</code></pre>
<p>Update your path:</p>
<pre class="code-pre "><code langs="">echo "export PATH=$PATH:$HOME/go/bin:/usr/local/go/bin" >> ~/.bashrc
</code></pre>
<p><strong>Log out of your current SSH session and log in again.</strong> You can close just the session where you've been working and keep the Elasticsearch session running. This step is crucial for your environment variables to get updated. Log in again, and verify that your variable has been added:</p>
<pre class="code-pre "><code langs="">echo $GOPATH
</code></pre>
<p>This should display the new path for Go. In our case, it will be:</p>
<pre class="code-pre "><code langs="">/home/<span class="highlight">sammy</span>/go
</code></pre>
<p>If it does not display the path correctly, please double-check the steps in this section. </p>

<p>Once our <code>$GOPATH</code> is set correctly, we need to check that Go is installed correctly by building a simple program.</p>

<p>Create a file named <code>hello.go</code> and put the following program in it. You can use any text editor you want. We are going to use the nano text editor in this article. Type the following command to create a new file:</p>
<pre class="code-pre "><code langs="">nano ~/hello.go
</code></pre>
<p>Now copy this brief "Hello, world" program below to the newly opened file. The entire point of this file is to help us verify that Go is working.</p>
<pre class="code-pre "><code langs="">package main;
import "fmt"

func main() {
    fmt.Printf("Hello, world\n")
}
</code></pre>
<p>Once done, press <code>CTRL+X</code> to exit the file. It will prompt you to save the file. Press <code>Y</code> and then press <code>ENTER</code>. it will ask you if you want to change the file name. Press <code>ENTER</code> again to save the current file.</p>

<p>Then, from your home directory, run the file with Go:</p>
<pre class="code-pre "><code langs="">go run hello.go
</code></pre>
<p>You should see this output:</p>
<pre class="code-pre "><code langs="">Hello, world
</code></pre>
<p>If you see the "Hello, world" message, then Go is installed correctly.</p>

<p>Now go to the <code>$GOPATH</code> directory and create the subdirectories <code>src</code>, <code>pkg</code> and <code>bin</code>. These directories constitute a workspace for Go.</p>
<pre class="code-pre "><code langs="">cd $GOPATH
mkdir src pkg bin
</code></pre>
<ul>
<li><code>src</code> contains Go source files organized into packages (one package per directory)</li>
<li><code>pkg</code> contains package objects</li>
<li><code>bin</code> contains executable commands</li>
</ul>

<h2 id="step-6-—-installing-git">Step 6 — Installing Git</h2>

<p>We'll use Git to install Transporter. Install Git with the following command:</p>
<pre class="code-pre "><code langs="">sudo apt-get install git
</code></pre>
<h2 id="step-7-—-installing-transporter">Step 7 — Installing Transporter</h2>

<p>Now create and move into a new directory for Transporter. Since the utility was developed by Compose, we'll call the directory <code>compose</code>.</p>
<pre class="code-pre "><code langs="">mkdir -p $GOPATH/src/github.com/compose
cd $GOPATH/src/github.com/compose
</code></pre>
<p>This is where <code>compose/transporter</code> will be installed.</p>

<p>Clone the Transporter GitHub repository:</p>
<pre class="code-pre "><code langs="">git clone https://github.com/compose/transporter.git
</code></pre>
<p>Move into the new directory:</p>
<pre class="code-pre "><code langs="">cd transporter
</code></pre>
<p>Take ownership of the <code>/usr/lib/go</code> directory:</p>
<pre class="code-pre "><code langs="">sudo chown -R $USER /usr/lib/go
</code></pre>
<p>Make sure <code>build-essential</code> is installed for GCC:</p>
<pre class="code-pre "><code langs="">sudo apt-get install build-essential
</code></pre>
<p>Run the <code>go get</code> command to get all the dependencies:</p>
<pre class="code-pre "><code langs="">go get -a ./cmd/...
</code></pre>
<p>This step might take a while, so be patient. Once it's done you can build Transporter.</p>
<pre class="code-pre "><code langs="">go build -a ./cmd/...
</code></pre>
<p>If all goes well, it will complete without any errors or warnings. Check that Transporter is installed correctly by running this command:</p>
<pre class="code-pre "><code langs="">transporter
</code></pre>
<p>You should see output like this:</p>
<pre class="code-pre "><code langs="">usage: transporter [--version] [--help] <command> [<args>]

Available commands are:
    about    Show information about database adaptors
    eval     Eval javascript to build and run a transporter application

. . .

</code></pre>
<p>So the installation is complete. Now, we need some test data in MongoDB that we want to sync to Elasticsearch.</p>

<p><strong>Troubleshooting:</strong></p>

<p>If you get the following error:</p>
<pre class="code-pre "><code langs="">transporter: command not found
</code></pre>
<p>This means that your <code>$GOPATH</code> was not added to your <code>PATH</code> variable. Check that you correctly executed the command:</p>
<pre class="code-pre "><code langs="">echo "export PATH=$PATH:$HOME/go/bin:/usr/local/go/bin" >> ~/.bashrc
</code></pre>
<p>Try logging out and logging in again. If the error still persists, use the following command instead:</p>
<pre class="code-pre "><code langs="">$GOPATH/bin/transporter
</code></pre>
<h2 id="step-8-—-creating-sample-data">Step 8 — Creating Sample Data</h2>

<p>Now that we have everything installed, we can proceed to the data syncing part.</p>

<p>Connect to MongoDB:</p>
<pre class="code-pre "><code langs="">mongo
</code></pre>
<p>You should now see the MongoDB prompt, <code>></code>. Create a database named <code>foo</code>.</p>
<pre class="code-pre "><code langs="">use foo
</code></pre>
<p>Insert some sample documents into a collection named <code>bar</code>:</p>
<pre class="code-pre "><code langs="">db.bar.save({"firstName": "Robert", "lastName": "Baratheon"});
db.bar.save({"firstName": "John", "lastName": "Snow"});
</code></pre>
<p>Select the contents you just entered:</p>
<pre class="code-pre "><code langs="">db.bar.find().pretty();
</code></pre>
<p>This should display the results shown below (<code>ObjectId</code> will be different on your machine):</p>
<pre class="code-pre "><code langs="">{
    "_id" : ObjectId("549c3ef5a0152464dde10bc4"),
    "firstName" : "Robert",
    "lastName" : "Baratheon"
}
{
    "_id" : ObjectId("549c3f03a0152464dde10bc5"),
    "firstName" : "John",
    "lastName" : "Snow"
}
</code></pre>
<p>Now you can exit from the database:</p>
<pre class="code-pre "><code langs="">exit
</code></pre>
<p>A bit of terminology:</p>

<ul>
<li>A <em>database</em> in MongoDB is analogous to an <em>index</em> in Elasticsearch</li>
<li>A <em>collection</em> in MongoDB is analogous to a <em>type</em> in Elasticsearch</li>
</ul>

<p>Our ultimate goal is to sync the data from the <strong>bar</strong> collection of the <strong>foo</strong> database from MongoDB to the <strong>bar</strong> type of the <strong>foo</strong> index in Elasticsearch. </p>

<h2 id="step-9-—-configuring-transporter">Step 9 — Configuring Transporter</h2>

<p>Now, we can move on to the configuration changes to migrate our data from MongoDB to Elasticsearch. Transporter requires a config file (<code>config.yaml</code>), a transform file (<code><span class="highlight">myTransformation</span>.js</code>), and an application file (<code>application.js</code>)</p>

<ul>
<li>The config file specifies the nodes, types, and URIs</li>
<li>The application file specifies the data flow from source to destination and optional transformation steps</li>
<li>The transform file applies transformations to the data</li>
</ul>

<blockquote>
<p><strong>Note:</strong>  All the commands in this section assume that you are executing the commands from the transporter directory.</p>
</blockquote>

<p>Move to the <code>transporter</code> directory:</p>
<pre class="code-pre "><code langs="">cd ~/go/src/github.com/compose/transporter
</code></pre>
<h3 id="config-file">Config File</h3>

<p>You can take a look at the example <code>config.yaml</code> file if you like. We're going to back up the original and then replace it with our own contents.</p>
<pre class="code-pre "><code langs="">mv test/config.yaml test/config.yaml.00
</code></pre>
<p>The new file is similar but updates some of the URIs and a few of the other settings to match what's on our server. Let's copy the contents from here and paste into the new <code>config.yaml</code> file. Use nano editor again.</p>
<pre class="code-pre "><code langs="">nano test/config.yaml
</code></pre>
<p>Copy the contents below into the file. Once done, save the file as described earlier.   </p>
<pre class="code-pre "><code langs=""># api:
#   interval: 60s
#   uri: "http://requestb.in/13gerls1"
#   key: "48593282-b38d-4bf5-af58-f7327271e73d"
#   pid: "something-static"
nodes:
  localmongo:
    type: mongo
    uri: mongodb://localhost/foo
  es:
    type: elasticsearch
    uri: http://localhost:9200/
  timeseries:
    type: influx
    uri: influxdb://root:root@localhost:8086/compose
  debug:
    type: file
    uri: stdout://
  foofile:
    type: file
    uri: file:///tmp/foo
</code></pre>
<p>Notice the <code>nodes</code> section. We have tweaked the <code>localmongo</code> and <code>es</code> nodes slightly compared to the original file. <em>Nodes</em> are the various data sources and destinations. <em>Type</em> defines the type of node. E.g.,</p>

<ul>
<li><code>mongo</code> means it’s a MongoDB instance/cluster</li>
<li><code>elasticsearch</code> means it's an Elasticsearch node</li>
<li><code>file</code> means it’s a plain text file</li>
</ul>

<p><code>uri</code> will give the API endpoint to connect with the node. The default port will be used for MongoDB (27017) if not specified. Since we need to capture data from the <strong>foo</strong> database of MongoDB, the URI should look like this:</p>
<pre class="code-pre "><code langs="">mongodb://localhost/foo
</code></pre>
<p>Similarly, the URI for Elasticsearch will look like:</p>
<pre class="code-pre "><code langs="">http://localhost:9200/
</code></pre>
<p>Save the <code>config.yaml</code> file. You don't need to make any other changes.</p>

<h3 id="application-file">Application File</h3>

<p>Now, open the <code>application.js</code> file in the <code>test</code> directory. </p>
<pre class="code-pre "><code langs="">nano test/application.js
</code></pre>
<p>Replace the sample contents of the file with the contents shown below:</p>
<pre class="code-pre "><code langs="">Source({name:"localmongo", namespace:"foo.bar"})
.transform({filename: "transformers/addFullName.js"})
.save({name:"es", namespace:"foo.bar"});
</code></pre>
<p>Save the file and exit. Here's a brief explanation of our pipeline.</p>

<ul>
<li><code>Source(<span class="highlight">options</span>)</code> identifies the source from which to fetch data</li>
<li><code>transform</code> specifies what transformation to apply on each record</li>
<li><code>save(<span class="highlight">options</span>)</code> identifies where to save data</li>
</ul>

<p>Options include:</p>

<ul>
<li><code>name:</code> name of the node as it appears in the <code>config.yaml</code> file</li>
<li><code>namespace:</code> identifies the database and table name; it must be qualified by a dot ( <strong>.</strong> )</li>
</ul>

<h3 id="transformation-file">Transformation File</h3>

<p>Now, the last piece of the puzzle is the transformation. If you recall, we stored two records in MongoDB with <code>firstName</code> and <code>lastName</code>. This is where you can see the real power of transforming data as you sync it from MongoDB to Elasticsearch.</p>

<p>Let's say we want the documents being stored in Elasticsearch to have another field called <code>fullName</code>. For that, we need to create a new transform file, <code>test/transformers/addFullName.js</code>.</p>
<pre class="code-pre "><code langs="">nano test/transformers/addFullName.js
</code></pre>
<p>Paste the contents below into the file. Save and exit as described earlier.</p>
<pre class="code-pre "><code langs="">module.exports = function(doc) {
  doc._id = doc._id['$oid']; 
  doc["fullName"] = doc["firstName"] + " " + doc["lastName"];
  return doc
}
</code></pre>
<p>The first line is necessary to tackle the way Transporter handles MongoDB's <code>ObjectId()</code> field. The second line tells Transporter to concatenate <code>firstName</code> and <code>lastName</code> to form <code>fullName</code>.</p>

<p>This is a simple transformation for the example, but with a little JavaScript you can do more complex data manipulation as you prepare your data for searching.</p>

<h2 id="step-10-—-executing-the-transformation">Step 10 — Executing the Transformation</h2>

<p>Now that we are done with the setup, it's time to sync and transform our data.</p>

<p><strong>Make sure Elasticsearch is running!</strong> If it isn't, start it again in a <strong>new terminal</strong> window:</p>
<pre class="code-pre "><code langs="">~/utils/elasticsearch-<span class="highlight">1.5.0</span>/bin/elasticsearch
</code></pre>
<p>In your <strong>original terminal</strong>, make sure you are in the <code>transporter</code> directory:</p>
<pre class="code-pre "><code langs="">cd ~/go/src/github.com/compose/transporter
</code></pre>
<p>Execute the following command to copy the data:</p>
<pre class="code-pre "><code langs="">transporter run --config ./test/config.yaml ./test/application.js
</code></pre>
<p>The <code>run</code> command of Transporter expects two arguments. First is the config file and second is the application file. If all goes well, the command will complete without any errors.</p>

<p>Check Elasticsearch to verify that the data got copied, with our transformation:</p>
<pre class="code-pre "><code langs="">curl -XGET localhost:9200/foo/bar/_search?pretty=true
</code></pre>
<p>You will get a result like this:</p>
<pre class="code-pre "><code langs="">{
  "took" : 10,
  "timed_out" : false,
  "_shards" : {
    "total" : 5,
    "successful" : 5,
    "failed" : 0
  },
  "hits" : {
    "total" : 2,
    "max_score" : 1.0,
    "hits" : [ {
      "_index" : "foo",
      "_type" : "bar_full_name",
      "_id" : "549c3ef5a0152464dde10bc4",
      "_score" : 1.0,
      "_source":{"_id":"549c3ef5a0152464dde10bc4","firstName":"Robert","fullName":"Robert Baratheon","lastName":"Baratheon"}
    }, {
      "_index" : "foo",
      "_type" : "bar_full_name",
      "_id" : "549c3f03a0152464dde10bc5",
      "_score" : 1.0,
      "_source":{"_id":"549c3f03a0152464dde10bc5","firstName":"John","fullName":"John Snow","lastName":"Snow"}
    } ]
  }
}
</code></pre>
<p>Notice the field <code>fullName</code>, which contains the <code>firstName</code> and <code>lastName</code> concatenated by a space in between — our transformation worked.</p>

<h3 id="conclusion">Conclusion</h3>

<p>Now we know how to use Transporter to copy data from MongoDB to Elasticsearch, and how to apply transformations to our data while syncing. You can apply much more complex transformations in the same way. Also, you can chain multiple transformations in the pipeline.</p>

<p>It's a good practice that if you are doing multiple transformations, keep them in separate files, and chain them. This way, you are making each one of your transformations usable independently for the future.</p>

<p>So, that's pretty much it. You can check out the <a href="https://github.com/compose/transporter">Transporter project on GitHub</a> to stay updated for the latest changes in the API.</p>

<p>You might also want to check out this tutorial about basic <a href="https://indiareads/community/tutorials/how-to-interact-with-data-in-elasticsearch-using-crud-operations">CRUD operations in Elasticsearch</a>.</p>

    