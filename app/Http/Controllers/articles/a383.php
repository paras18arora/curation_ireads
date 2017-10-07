<link rel='stylesheet' href='digital_ocean.css'><br><img src=https://community-cdn-digitalocean-com.global.ssl.fastly.net/assets/tutorials/images/large/arangodb.jpg?1435600572/> <br> 
      <h3 id="introduction">Introduction</h3>

<p>ArangoDB is a NoSQL database. It was created in 2011, when many NoSQL database were already around, with the goal of being a comprehensive database solution that could cover a variety of use cases.</p>

<p>At its core ArangoDB is a <a href="https://en.wikipedia.org/wiki/Document-oriented_database">document store</a> but that is only the beginning. You can query data with a full-fledged query language (named AQL), make <a href="https://en.wikipedia.org/wiki/ACID">ACID compliant</a> transactions, add custom HTTP endpoints in the form of JavaScript applications with its <a href="https://developers.google.com/v8/">embedded V8</a>, and more.</p>

<p>Since ArangoDB has a lot of features it could be intimidating at first, but after a second look it is not complicated at all. This article will help you to install ArangoDB and will give a short introduction to how some of its core features can be used.</p>

<p>After completing this tutorial, you should be able to:</p>

<ul>
<li>Install ArangoDB on Ubuntu 14.04</li>
<li>Configure ArangoDB for basic usage</li>
<li>Insert, modify, and query for data</li>
</ul>

<h3 id="core-concepts">Core Concepts</h3>

<p>Throughout the article we will use some core concepts. You'll probably want to familiarize yourself with them before building a project on ArangoDB:</p>

<ul>
<li><p><em>Document Store</em>: ArangoDB stores data in documents, in contrast to how relational databases store data. Documents are arbitrary data structures consisting of <em>key-value pairs</em>. The <em>key</em> is a string that names the <em>value</em> (like a column in a relational database). The <em>value</em> can be any data type, even another document. Documents are not bound to any schema.</p></li>
<li><p><em>Query Language</em>: Interact with your data using either an API or a query language. While the former leaves a lot of details to the API user, a query language hands over the details to the database. In a relational database, SQL is an exampel of a query language.</p></li>
<li><p><em>ACID</em>: The four properties <strong>A</strong>tomicity, <strong>C</strong>onsistency, <strong>I</strong>solation, and <strong>D</strong>urability describe the guarantees of database transactions. ArangoDB supports ACID-compliant transactions.</p></li>
<li><p><em>V8</em>: Google's JavaScript engine that powers Chrome can be easily embedded in other software too. Using it in ArangoDB enables using JavaScript inside the database. Much of ArangoDB's internal functionality is built with JavaScript.</p></li>
<li><p><em>HTTP API</em>: ArangoDB provides an HTTP API to allow clients to interact with the database. The API is <a href="https://en.wikipedia.org/wiki/Representational_state_transfer">resource oriented</a> and can be extended with JavaScript.</p></li>
</ul>

<h2 id="prerequisites">Prerequisites</h2>

<p>Before we start, make sure to have your Droplet set up correctly:</p>

<ul>
<li><p>Create a Droplet with Ubuntu 14.04 x64</p></li>
<li><p>Add a <a href="https://indiareads/community/tutorials/how-to-add-and-delete-users-on-an-ubuntu-14-04-vps">sudo user</a></p></li>
</ul>

<p>Now you should log in to your server using the newly created user. All of the examples in the tutorial can be performed from the user's home directory:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">cd ~
</li></ul></code></pre>
<h2 id="step-1-—-installing-arangodb">Step 1 — Installing ArangoDB</h2>

<p>ArangoDB comes pre-built for many operating systems and distributions. The chance is high you don't need to build it from source. For more details, please refer to the ArangoDB <a href="https://www.arangodb.com/download">documentation</a>. For this tutorial we will use Ubuntu 14.04 x64.</p>

<p>Since ArangoDB uses OpenSUSE's <a href="https://build.opensuse.org/">build service</a>, the first thing is to download the public key for its repositories:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">wget https://www.arangodb.com/repositories/arangodb2/xUbuntu_14.04/Release.key
</li></ul></code></pre>
<p>You need <code>sudo</code> to install the key:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo apt-key add Release.key
</li></ul></code></pre>
<p>Next add the apt repository and update the index:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo apt-add-repository 'deb https://www.arangodb.com/repositories/arangodb2/xUbuntu_14.04/ /'
</li><li class="line" prefix="$">sudo apt-get update
</li></ul></code></pre>
<p>Install ArangoDB:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo apt-get install arangodb
</li></ul></code></pre>
<p>We can check if everything went well by querying the HTTP API:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">curl http://localhost:8529/_api/version
</li></ul></code></pre>
<p>The following output indicates ArangoDB is up and running:</p>
<pre class="code-pre "><code langs=""><div class="secondary-code-label " title="Output">Output</div>{"server":"arango","version":"2.5.5"}
</code></pre>
<h2 id="step-2-—-accessing-the-command-line-with-arangosh">Step 2 — Accessing the Command Line with arangosh</h2>

<p>ArangoDB ships with <code>arangosh</code>, a command-line client which gives you full access to the database through its JavaScript runtime. You can use it to run administrative tasks or scripts in production.</p>

<p>It's also well suited to getting started with ArangoDB and its core functionality. To follow along, start an <code>arangosh</code> session like this:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">arangosh
</li></ul></code></pre>
<p>The result is basically a JavaScript shell where you can run arbitrary JavaScript code. For example, add two numbers:</p>
<pre class="code-pre custom_prefix"><code langs=""><ul class="prefixed"><li class="line" prefix="arangosh [_system]>">23 + 19
</li></ul></code></pre>
<p>You'll get this result:</p>
<pre class="code-pre "><code langs=""><div class="secondary-code-label " title="Output">Output</div>42
</code></pre>
<p>If you want to dive deeper in this topic, type <code>tutorial</code> into the shell.</p>

<h2 id="step-3-—-adding-a-database-user">Step 3 — Adding a Database User</h2>

<p>For security reasons, it is only possible to add users from the <code>arangosh</code> command line interface. You should still be in the <code>arangosh</code> shell from the previous step.</p>

<p>Now let's add a new user, <strong>sammy</strong>. This user will have access to the entire database. This is OK for now, but you might want to create more limited users in a production environment. Use a secure <code><span class="highlight">password</span></code>.</p>
<pre class="code-pre custom_prefix"><code langs=""><ul class="prefixed"><li class="line" prefix="arangosh [_system]>">require("org/arangodb/users").save("<span class="highlight">sammy</span>", "<span class="highlight">password</span>");
</li></ul></code></pre>
<p>Now exit the <code>arangosh</code> shell:</p>
<pre class="code-pre custom_prefix"><code langs=""><ul class="prefixed"><li class="line" prefix="arangosh [_system]>">exit
</li></ul></code></pre>
<h2 id="step-4-—-configuring-the-web-interface">Step 4 — Configuring the Web Interface</h2>

<p>ArangoDB ships with a very powerful web interface. It offers monitoring capabilities, data browsing, interactive API documentation, a powerful query editor, and even an integrated <code>arangosh</code>. We will focus on the usage of the web interface for the reminder of this tutorial.</p>

<p>To make the web interface easily accessible we need to undertake some preparations:</p>

<ol>
<li><p>Enable authentication</p></li>
<li><p>Bind ArangoDB to the public network interface</p></li>
</ol>

<h3 id="enable-authentication">Enable Authentication</h3>

<p>ArangoDB, like many other NoSQL databases, ships with authentication disabled. It is <strong>highly recommended to enable authentication</strong> if you run ArangoDB in a shared environment and/or want to use the web interface. For more details on this topic please refer to the <a href="https://docs.arangodb.com/ConfigureArango/Authentication.html">ArangoDB documentation</a>.</p>

<p>Activate authentication in the <code>/etc/arangodb/arangod.conf</code> file. You can run this command to create a backup file and set the <code>disable-authentication</code> parameter to <code>no</code>:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo sed -i.bak 's/disable-authentication = yes/disable-authentication = no/g' /etc/arangodb/arangod.conf
</li></ul></code></pre>
<p>Alternately, use a text editor to set the <code>disable-authentication</code> parameter to <code>no</code>.</p>

<p>Restart the database:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo service arangodb restart
</li></ul></code></pre>
<h3 id="bind-arangodb-to-the-public-network-interface">Bind ArangoDB to the Public Network Interface</h3>

<p>Configure ArangoDB to listen on the public network interface. First, open the <code>/etc/arangodb/arangod.conf</code> file for editing:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo nano /etc/arangodb/arangod.conf
</li></ul></code></pre>
<p>Locate the active <code>endpoint</code> line, which should be at the end of the <code>[server]</code> block below a section of examples. Update the setting as shown below, using your own server's IP address, and port <code>8529</code>.</p>
<div class="code-label " title="/etc/arangodb/arangod.conf">/etc/arangodb/arangod.conf</div><pre class="code-pre "><code langs="">
. . .

endpoint = tcp://<span class="highlight">your_server_ip</span>:8529
</code></pre>
<p>Since <code>arangosh</code> uses its own default configuration, we need to change the endpoint in the <code>/etc/arangodb/arangosh.conf</code> file too:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo nano /etc/arangodb/arangosh.conf
</li></ul></code></pre>
<p>Again, make sure the <code>endpoint</code> line is set to <code>tcp://<span class="highlight">your_server_ip</span>:8529</code>.</p>
<div class="code-label " title="/etc/arangodb/arangosh.conf">/etc/arangodb/arangosh.conf</div><pre class="code-pre "><code langs="">pretty-print = true

[server]
endpoint = tcp://<span class="highlight">your_server_ip</span>:8529
disable-authentication = true

. . .

</code></pre>
<span class="note"><p>
If you'd rather run two multipart, one-line commands to update these two files, you can run these commands instead:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo sed -i.bak "s/^endpoint = .*/endpoint = tcp:\/\/$(sudo ifconfig eth0 | grep "inet " | cut -d: -f 2 | awk '{print $1}'):8529/g" /etc/arangodb/arangod.conf
</li></ul></code></pre><pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo sed -i.bak "s/^endpoint = .*/endpoint = tcp:\/\/$(sudo ifconfig eth0 | grep "inet " | cut -d: -f 2 | awk '{print $1}'):8529/g" /etc/arangodb/arangosh.conf
</li></ul></code></pre>
<p>These arcane looking commands will extract the current public IP address and replace the default bind addresses (<code>127.0.0.1</code>). Don't worry, the <code>-i.bak</code> option creates updates before changing the configuration.<br /></p></span>

<p>Now restart ArangoDB once more:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo service arangodb restart
</li></ul></code></pre>
<h2 id="step-5-—-accessing-the-arangodb-web-interface">Step 5 — Accessing the ArangoDB Web Interface</h2>

<p>Now you should be able to access the web interface in your browser:</p>
<pre class="code-pre "><code langs="">http://<span class="highlight">your_server_ip</span>:8529
</code></pre>
<p>Please log in with the username and password you created for the database in Step 3.</p>

<p><span class="warning"><em>Warning:</em> Although we set up authentication, the transport is not secured yet. In production you should set up TLS encryption if you make ArangoDB accessible from another host.<br /></span></p>

<p>The first screen you should see is the dashboard with basic metrics about the database server:</p>

<p><img src="https://assets.digitalocean.com/articles/arangodb-ubuntu1404/iKDjIgx.png" alt="The ArangoDB web interface dashboard" /></p>

<p>In the center of the top navigation you will see <strong>DB: _system</strong>. This indicates the currently-selected database. The default is the <code>_system</code> database. Certain administrative tasks can only be performed in the <code>_system</code> database.</p>

<p>For the following sections, we will create a database to work with. Hover over the <strong>DB: _system</strong> menu item, and click the <strong>Manage DBs</strong> link.</p>

<p>On the following page click the <strong>Add Database</strong> button. Fill out the form to create a database named <code>music_library</code>. You must enter the same username and password as before in this dialog, or you will not be able to access the new database later:</p>

<p><img src="https://assets.digitalocean.com/articles/arangodb-ubuntu1404/DA8kRTO.png" alt="Create a new DB in the web interface" /></p>

<p>We are now set to start actually do something with ArangoDB.</p>

<h2 id="step-6-—-performing-crud-operations-with-arangosh">Step 6 — Performing CRUD Operations with arangosh</h2>

<p>We will leave the web interface for now and return to the <code>arangosh</code> command line interface to cover basic CRUD operations in ArangoDB. Later on we will cover the same operations in the web interface again, but doing it in the shell helps us get a better understanding of how things work.</p>

<p>To follow along, go back to the command line for your server. Connect to the new <code>music_library</code> database using your user and password:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">arangosh --server.database music_library --server.username <span class="highlight">sammy</span> --server.password <span class="highlight">password</span>
</li></ul></code></pre>
<h3 id="create-a-document-collection">Create a Document Collection</h3>

<p>If you come from a relational database background, a Collection is the ArangoDB equivalent of a table in an SQL database. We will create a collection to store songs in our music library:</p>
<pre class="code-pre custom_prefix"><code langs=""><ul class="prefixed"><li class="line" prefix="[music_library]>">db._createDocumentCollection('songs')
</li></ul></code></pre>
<p>ArangoDB provides a series of <a href="https://docs.arangodb.com/Collections/CollectionMethods.html">methods to manage Collections</a>. Most of them are not of interest at this point, but please take a look at them as you get further into ArangoDB. For now, we'll focus on <em>CRUD</em> operations (create, read, update and delete) - that is, how to get actual data in and out of the database.</p>

<h3 id="creating-documents">Creating Documents</h3>

<p>Where you would have rows in an SQL-based database, ArangoDB has <a href="https://docs.arangodb.com/Documents">Documents</a>. Documents in ArangoDB are JSON objects. Each document is associated with a collection and has three core attributes: <code>_id</code>, <code>_rev</code>, and <code>_key</code>.</p>

<p>A document is uniquely identified inside a database by its <a href="https://docs.arangodb.com/Glossary#document_handle">document handle</a> which consists of the collection name and the <code>_key</code>, separated by a <code>/</code>. The document handle is stored in the <code>_id</code> field of a document. Both the <code>_key</code> and the <code>_id</code> are similar to the primary key in a relation database.</p>

<p><span class="note"><em>Note:</em> If you don't specify something yourself, ArangoDB will create a <code>_key</code> for each document. You can specify a custom <code>_key</code> if you wish, but you need to make sure it is unique. Throughout this tutorial we will set the <code>_key</code> explicitly to make it easier to copy and paste the examples.<br /></span></p>

<p>Let's add our first document to the <code>songs</code> collection:</p>
<pre class="code-pre custom_prefix"><code langs=""><ul class="prefixed"><li class="line" prefix="[music_library]>">db.songs.save(
</li><li class="line" prefix="[music_library]>">{ title: "Immigrant Song", album: "Led Zeppelin III", artist: "Led Zeppelin", year: 1970, length: 143, _key: "immigrant_song" }
</li><li class="line" prefix="[music_library]>">)
</li></ul></code></pre><pre class="code-pre "><code class="code-highlight language-json"><div class="secondary-code-label " title="Output">Output</div>{ 
  "error" : false, 
  "_id" : "songs/immigrant_song", 
  "_rev" : "11295857653", 
  "_key" : "immigrant_song" 
}
</code></pre>
<p>The <code>db</code> object holds all collections as properties. Each collection provides functions to interact with the documents in that collection. The <code>save</code> function takes any JSON object and stores it as a document in the collection, returning the aforementioned core attributes and whether an error has occurred. The return from each operation is again a JSON object.</p>

<p>To have something to play with we need some more documents. Just copy and paste the next snippet to add several more entries to the database:</p>
<pre class="code-pre custom_prefix"><code langs=""><ul class="prefixed"><li class="line" prefix="[music_library]>">db.songs.save(
</li><li class="line" prefix="[music_library]>">{album: "Led Zeppelin III", title: "Friends", artist: "Led Zeppelin", year: 1970, length: 235, _key: "friends"}
</li><li class="line" prefix="[music_library]>">);
</li><li class="line" prefix="[music_library]>">
</li><li class="line" prefix="[music_library]>">db.songs.save(
</li><li class="line" prefix="[music_library]>">{album: "Led Zeppelin III", title: "Celebration Day", artist: "Led Zeppelin", year: 1970, length: 209, _key: "celebration_day"}
</li><li class="line" prefix="[music_library]>">);
</li><li class="line" prefix="[music_library]>">
</li><li class="line" prefix="[music_library]>">db.songs.save(
</li><li class="line" prefix="[music_library]>">{album: "Led Zeppelin III", title: "Since I've Been Loving You", artist: "Led Zeppelin", year: 1970, length: 445, _key: "since_i_ve_been_loving_you"}
</li><li class="line" prefix="[music_library]>">);
</li><li class="line" prefix="[music_library]>">
</li><li class="line" prefix="[music_library]>">db.songs.save(
</li><li class="line" prefix="[music_library]>">{album: "Led Zeppelin III", title: "Out On the Tiles", artist: "Led Zeppelin", year: 1970, length: 244, _key: "out_on_the_tiles"}
</li><li class="line" prefix="[music_library]>">);
</li><li class="line" prefix="[music_library]>">
</li><li class="line" prefix="[music_library]>">db.songs.save(
</li><li class="line" prefix="[music_library]>">{album: "Led Zeppelin III", title: "Gallows Pole", artist: "Led Zeppelin", year: 1970, length: 298, _key: "gallows_pole"}
</li><li class="line" prefix="[music_library]>">);
</li><li class="line" prefix="[music_library]>">
</li><li class="line" prefix="[music_library]>">db.songs.save(
</li><li class="line" prefix="[music_library]>">{album: "Led Zeppelin III", title: "Tangerine", artist: "Led Zeppelin", year: 1970, length: 192, _key: "tangerine"}
</li><li class="line" prefix="[music_library]>">);
</li><li class="line" prefix="[music_library]>">
</li><li class="line" prefix="[music_library]>">db.songs.save(
</li><li class="line" prefix="[music_library]>">{album: "Led Zeppelin III", title: "That's the Way", artist: "Led Zeppelin", year: 1970, length: 338, _key: "that_s_the_way"}
</li><li class="line" prefix="[music_library]>">);
</li><li class="line" prefix="[music_library]>">
</li><li class="line" prefix="[music_library]>">db.songs.save(
</li><li class="line" prefix="[music_library]>">{album: "Led Zeppelin III", title: "Bron-Y-Aur Stomp", artist: "Led Zeppelin", year: 1970, length: 260, _key: "bron_y_aur_stomp"}
</li><li class="line" prefix="[music_library]>">);
</li><li class="line" prefix="[music_library]>">
</li><li class="line" prefix="[music_library]>">db.songs.save(
</li><li class="line" prefix="[music_library]>">{album: "Led Zeppelin III", title: "Hats Off to (Roy) Harper", artist: "Led Zeppelin", year: 1970, length: 221, _key: "hats_off_to_roy_harper"}
</li><li class="line" prefix="[music_library]>">);
</li></ul></code></pre>
<h3 id="reading-documents">Reading Documents</h3>

<p>To retrieve a document you can use either the document handle or the <code>_key</code>. Using the document handle is only required if you don't go over the collection itself. Having a collection, you can use the <code>document</code> function:</p>
<pre class="code-pre custom_prefix"><code langs=""><ul class="prefixed"><li class="line" prefix="[music_library]>">db.songs.document('immigrant_song');
</li></ul></code></pre><pre class="code-pre "><code class="code-highlight language-json"><div class="secondary-code-label " title="Output">Output</div>{ 
  "year" : 1970, 
  "length" : 143, 
  "title" : "Immigrant Song", 
  "album" : "Led Zeppelin III", 
  "artist" : "Led Zeppelin", 
  "_id" : "songs/immigrant_song", 
  "_rev" : "11295857653", 
  "_key" : "immigrant_song" 
}
</code></pre>
<p>Now that we can create and read documents, we will look into how to change them:</p>

<h3 id="updating-documents">Updating Documents</h3>

<p>When it comes to updating your data, you have two options: <a href="https://docs.arangodb.com/Documents/DocumentMethods.html#replace"><code>replace</code></a> and <a href="https://docs.arangodb.com/Documents/DocumentMethods.html#update"><code>update</code></a>.</p>

<p>The <code>replace</code> function will replace the entire document with a new one, even if you provide completely different attributes.</p>

<p>The <code>update</code> function, on the other hand, will just patch a document by merging it with the given attributes. Let's try a less-destructive <code>update</code> first, where we update the <code>genre</code> of one of our songs:</p>
<pre class="code-pre custom_prefix"><code langs=""><ul class="prefixed"><li class="line" prefix="[music_library]>">db.songs.update("songs/immigrant_song",
</li><li class="line" prefix="[music_library]>">
</li><li class="line" prefix="[music_library]>">{ genre: "Hard Rock" }
</li><li class="line" prefix="[music_library]>">
</li><li class="line" prefix="[music_library]>">);
</li></ul></code></pre>
<p>Let's take a look at the updated song entry:</p>
<pre class="code-pre custom_prefix"><code langs=""><ul class="prefixed"><li class="line" prefix="[music_library]>">db.songs.document("songs/immigrant_song");
</li></ul></code></pre><pre class="code-pre "><code class="code-highlight language-json"><div class="secondary-code-label " title="Output">Output</div>{ 
  "year" : 1970, 
  "length" : 143, 
  "title" : "Immigrant Song", 
  "album" : "Led Zeppelin III", 
  "artist" : "Led Zeppelin", 
  "genre" : "Hard Rock", 
  "_id" : "songs/immigrant_song", 
  "_rev" : "11421424629", 
  "_key" : "immigrant_song" 
}
</code></pre>
<p>The <code>update</code> function is especially helpful when you have a large document and need to update only a small subset of its attributes.</p>

<p>In contrast, using the same JSON with the <code>replace</code> function will destroy your data.</p>
<pre class="code-pre custom_prefix"><code langs=""><ul class="prefixed"><li class="line" prefix="[music_library]>">db.songs.replace("songs/immigrant_song",
</li><li class="line" prefix="[music_library]>">
</li><li class="line" prefix="[music_library]>">{ genre: "Hard Rock" }
</li><li class="line" prefix="[music_library]>">
</li><li class="line" prefix="[music_library]>">);
</li></ul></code></pre>
<p>View the updated song now:</p>
<pre class="code-pre custom_prefix"><code langs=""><ul class="prefixed"><li class="line" prefix="[music_library]>">db.songs.document("songs/immigrant_song")
</li></ul></code></pre>
<p>As you can see, the original data has been removed from the document:</p>
<pre class="code-pre "><code class="code-highlight language-json"><div class="secondary-code-label " title="Output">Output</div>{ 
  "genre" : "Hard Rock", 
  "_id" : "songs/immigrant_song", 
  "_rev" : "11495939061", 
  "_key" : "immigrant_song" 
}
</code></pre>
<h3 id="removing-documents">Removing Documents</h3>

<p>To remove a document from a collection, call the <code>remove</code> function with the document handle:</p>
<pre class="code-pre custom_prefix"><code langs=""><ul class="prefixed"><li class="line" prefix="[music_library]>">db.songs.remove("songs/immigrant_song")
</li></ul></code></pre>
<p>While the <code>arangosh</code> shell is a great tool, it's cumbersome for exploring the other features of ArangoDB. Next we will look into the built in web interface to dig further into its capabilities.</p>

<h2 id="step-7-—-performing-crud-operations-with-the-web-interface">Step 7 — Performing CRUD Operations with the Web Interface</h2>

<p>We've seen how to handle documents on the <code>arangosh</code>, and now we return to the web interface. Visit <code>http://<span class="highlight">your_server_ip</span>:8529/_db/music_library</code> in your browser. </p>

<h3 id="create-a-document-collection">Create a Document Collection</h3>

<p>Click the <strong>Collections</strong> tab in the top navigation bar.</p>

<p>You can see the existing <code>songs</code> collection that we added from the command line; feel free to click on it and view the entries, if you like.</p>

<p>From the main <strong>Collections</strong> page, click on the <strong>Add Collection</strong> button.</p>

<p><img src="https://assets.digitalocean.com/articles/arangodb-ubuntu1404/V05LxrP.png" alt="Add Collection in Web Interface" /></p>

<p>Since we already have <code>songs</code>, we will add an <code>albums</code> collection. Enter <code>albums</code> as the <strong>Name</strong> in the <strong>New Collection</strong> dialog that popped up. The default type, <strong>Document</strong>, is fine.</p>

<p>Click <strong>Save</strong> and you should see now two collections on the page.</p>

<p>Click on the <code>albums</code> collection. You are presented with an empty collection:</p>

<p><img src="https://assets.digitalocean.com/articles/arangodb-ubuntu1404/d8qIcmh.png" alt="Collection View" /></p>

<h3 id="creating-documents">Creating Documents</h3>

<p>Click the <strong>+</strong> sign in the upper right corner to add a document. You will first get asked for a <code>_key</code>. Enter <code>led_zeppelin_III</code> as the key.</p>

<p>Next there's a form where you can edit the contents of a document. There is a graphical way of adding attributes called <strong>Tree</strong>, but for now, switch to the <strong>Code</strong> view by selecting it from the <strong>Tree</strong> dropdown menu:</p>

<p><img src="https://assets.digitalocean.com/articles/arangodb-ubuntu1404/0DfPNao.png" alt="Create a Document" /></p>

<p>Please copy and paste the following JSON into the editor area (make sure you use only one set of curly braces):</p>
<pre class="code-pre "><code class="code-highlight language-json">{
"name": "Led Zeppelin III",
"release_date": "1970-10-05",
"producer": "Jimmy Page",
"label": "Atlantic",
"length": 2584
}
</code></pre>
<p>Be aware that is required to quote the keys in this mode. After you're done, hit the <strong>Save</strong> button. The page should flash green for a moment to indicate a successful save.</p>

<h3 id="reading-documents">Reading Documents</h3>

<p>You need to manually navigate back to the <strong>Collections</strong> page after saving the new document.</p>

<p>If you click on the <code>albums</code> collection, you'll see the new entry.</p>

<h3 id="updating-documents">Updating Documents</h3>

<p>To edit the contents of a document, just click on the row you want to edit in the document overview. You will be presented with the same editor as when creating new documents.</p>

<h3 id="removing-documents">Removing Documents</h3>

<p>Deleting documents is as simple as pressing the <strong>-</strong> icon at the end of each document row. Confirm the deletion when prompted.</p>

<p>Additionally, the <strong>Collections</strong> overview page for a specific collection lets you export and import data, manage indexes, and filter the documents.</p>

<p>As mentioned before, the web interface has a lot to offer. Covering every feature is beyond this tutorial, so you're invited to explore the other features on your own. We will dig just into one more feature in this tutorial: The AQL Editor.</p>

<h2 id="step-8-—-querying-the-data-with-aql">Step 8 — Querying the Data with AQL</h2>

<p>As mentioned in the introduction, ArangoDB comes with a full-fledged query language called AQL.</p>

<p>To interact with AQL in the web interface, click on the <strong>AQL Editor</strong> tab in the top navigation. You will be presented with a blank editor.</p>

<p>To switch between the editor and the result view, use the <strong>Query</strong> and <strong>Result</strong> tabs in the upper right corner:</p>

<p><img src="https://assets.digitalocean.com/articles/arangodb-ubuntu1404/eKTShDh.png" alt="The AQL Editor" /></p>

<p>The editor has syntax highlighting, undo/redo functionality, and and query saving. The following section will explore some of the features of AQL. For a complete reference, visit the <a href="https://docs.arangodb.com/Aql">comprehensive documentation</a>.</p>

<h3 id="aql-basics">AQL Basics</h3>

<p>AQL is a declarative language, meaning that a query expresses what result should be achieved but not how it should be achieved. It allows querying for data but also modifying the data. Both approaches can be combined to achieve complex tasks.</p>

<p>Reading and modifying queries in AQL is fully ACID-compliant. Operations will either finish in whole or not at all. Even reading data will happen on a consistent snapshot of the data.</p>

<p>We begin again with creating data. Let's add more songs to our <code>songs</code> collection. Just copy and paste the following query:</p>
<pre class="code-pre "><code langs="">FOR song IN [

{ album: "Led Zeppelin", title: "Good Times Bad Times", artist: "Led Zeppelin", length: 166, year: 1969, _key: "good_times_bad_times" }

,

{ album: "Led Zeppelin", title: "Dazed and Confused", artist: "Led Zeppelin", length: 388, year: 1969, _key: "dazed_and_confused" }

,

{ album: "Led Zeppelin", title: "Communication Breakdown", artist: "Led Zeppelin", length: 150, year: 1969, _key: "communication_breakdown" }

]

INSERT song IN songs
</code></pre>
<p>Click the <strong>Submit</strong> button.</p>

<p>This query is already a good example of how AQL works: You iterate over a list of documents with <code>FOR</code> and perform an operation on each of the documents. The list could be an array with JSON objects or any collection in your database. Operations include filtering, modifying, selecting more documents, creating new structures, or (as in this example)inserting documents into the database. In fact, AQL supports all CRUD operations too.</p>

<p>To get an overview of all the songs in the database, run the following query. It is the equivalent of a <code>SELECT * FROM songs</code> in an SQL-based database (since the editor remembers the last query, you should click on the <strong>trash can</strong> icon to clear the editor):</p>
<pre class="code-pre "><code langs="">FOR song IN songs RETURN song
</code></pre>
<p>Now you'll see all the entries from the song database in the text field. Go back to the <strong>Query</strong> tab and clear the editor again.</p>

<p>Another example involves basic filtering for songs above a playtime of three minutes:</p>
<pre class="code-pre "><code langs="">FOR song IN songs

FILTER song.length > 180

RETURN song
</code></pre>
<p>The result is presented in the <strong>Result</strong> tab of the editor:</p>

<p><img src="https://assets.digitalocean.com/articles/arangodb-ubuntu1404/tFZRf5R.png" alt="Query Result" /></p>

<h3 id="complex-aql-example">Complex AQL Example</h3>

<p>AQL comes with a <a href="https://docs.arangodb.com/Aql/Functions.html">set of functions</a> for all supported data types and even allows the <a href="https://docs.arangodb.com/AqlExtending">addition of new functions</a>. Combined with ability to assign variables within a query, you can build very complex constructs. This allows you to move data-intensive operations closer to the data itself rather then executing them on the client. To illustrate this, we will format a song's duration as <code>mm:ss</code> to make it read nicely for the user:</p>
<pre class="code-pre "><code langs="">FOR song IN songs

FILTER song.length > 180

LET minutes = FLOOR(song.length / 60)

LET seconds = song.length % 60

RETURN

{ title: song.title, duration: CONCAT_SEPARATOR(':', minutes, seconds) }
</code></pre>
<p>This time we will just return the song title together with the duration. The <code>RETURN</code> lets you create a new JSON object to return for each input document.</p>

<p>AQL is complex language with a lot of features. But there is one more feature worth mentioning, especially in the context of NoSQL databases: Joins.</p>

<h3 id="joins-in-aql">Joins in AQL</h3>

<p>Using a document store as your database has several implications. You should model your data in a different way than you would when using a relational database.</p>

<p>In a document store, you have the ability to embed data that would otherwise be modeled as a relation, but this approach is not always feasible. There are cases when a relation makes much more sense. Without the ability to let the database perform the required joins, you would end up joining the data on the client, or denormalizing your data model and embedding sub-documents. This becomes especially problematic for complex and large data sets.</p>

<p>So, let's do a join.</p>

<p>To illustrate this feature we will replace the <code>album</code> attribute of the songs with a reference to the <code>albums</code> collection. We already created the album <em>Led Zeppelin III</em> as a document before. Please go back and re-add the album if you deleted it during the earlier example.</p>

<p>This query will to the trick:</p>
<pre class="code-pre "><code langs="">FOR album IN albums

FOR song IN songs

FILTER song.album == album.name

LET song_with_album_ref = MERGE(UNSET(song, 'album'),

{ album_key: album._key }

)

REPLACE song WITH song_with_album_ref IN songs
</code></pre>
<p>We first iterate over all the albums and then look up all the songs that this album is associated with. The next step is to create a new document which contains the <code>album_key</code> attribute and <code>UNSET</code> the <code>album</code> attribute. We will use <code>REPLACE</code> and not <code>UPDATE</code> to update the song documents. This is possible because we created a new song document before.</p>

<p>After this data migration, we can now maintain the album document in one place. When fetching the song data, we can use a join to add the album name again to the song documents:</p>
<pre class="code-pre "><code langs="">FOR song IN songs

FOR album IN albums

FILTER album._key == song.album_key

RETURN MERGE(song,

{ album: album.name }

)
</code></pre>
<p>We've barely scraped the surface of what can be accomplished with AQL, but you should have a good impression of what is possible. For a complete language reference and more examples, refer to the extensive <a href="https://docs.arangodb.com/Aql">documentation</a>.</p>

<h2 id="optional-step-9-—-making-backups">(Optional) Step 9 — Making Backups</h2>

<p>You should start thinking about backups once you put an ArangoDB database into production. It is a good practice to establish backups before that, though.</p>

<p>Using the <a href="https://indiareads/community/tutorials/digitalocean-backups-and-snapshots-explained">Backup feature from IndiaReads</a> is a good start. Additionally, you may want to look into using <a href="https://docs.arangodb.com/Arangodump"><code>arangodump</code></a> and <a href="https://docs.arangodb.com/Arangorestore"><code>arangorestore</code></a> to have more fine-grained control about what to back up and where to store the backups.</p>

<h2 id="optional-step-10-—-upgrading">(Optional) Step 10 — Upgrading</h2>

<p>When a new version of ArangoDB is released it will be published through the configured package repository. To install the latest version, you first need to update the repository index:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo apt-get update
</li></ul></code></pre>
<p>Now stop the database:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo service arangodb stop
</li></ul></code></pre>
<p>Update it to the latest version:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo apt-get install arangodb
</li></ul></code></pre>
<p><span class="note"><em>Note:</em> After installing the update, the system tries to start the <code>arangodb</code> service. This may fail because the database files need to be upgraded. This is to be expected.<br /></span></p>

<p>You may need to upgrade the database files themselves:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo service arangodb upgrade
</li></ul></code></pre>
<p>After that, start the server as usual:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo service arangodb start
</li></ul></code></pre>
<h2 id="extending-arangodb-with-foxx-applications">Extending ArangoDB with Foxx Applications</h2>

<p>Before we finish, there is one more thing worth mentioning: Since ArangoDB has an integrated V8 engine to handle all the JavaScript and it has an HTTP server built in, we could extend the existing HTTP API with custom endpoints. This functionality is called <a href="https://docs.arangodb.com/Foxx">Foxx</a>.</p>

<p>Foxx is a framework to use ArangoDB to build custom microservices with persistent data. Foxx apps are written in JavaScript and run in ArangoDB's V8 context. The app has direct access to the native JavaScript interface and thus can access the data without any HTTP round trips. Foxx provides a minimal framework much in the sense of Sinatra for Ruby or Flask for Python. You write controllers to handle incoming requests and implement the business logic inside models.</p>

<p>Foxx apps can be managed via the web interface and can be developed like any other app. You can put them under version control and even deploy them directly out of a Git repository. Since they are just JavaScript, unit testing them is straightforward. For simple use cases, they are much like stored procedures in a relational database system, but Foxx code is much easier to maintain and test.</p>

<p>Using Foxx apps as stored procedures is just the beginning. Imagine you have multiple applications which share certain business logic. With Foxx, you can move this business logic closer to the data to make processing faster and reduce the complexity of distributing the shared implementation among components. Running ArangoDB as a cluster even takes care of making Foxx apps available on each member in the cluster.</p>

<p>Even entire web applications are possible with Foxx. Using frontend frameworks such as Angular or Ember allows you to run applications entirely off the database. No additional infrastructure is needed for this. In a production environment, you would eventually put Nginx or similar in front of ArangoDB. ArangoDB ships with some Foxx apps that provide common functionality, such as authentication and a session store. You can even use npm packages if they do not rely on the HTTP functionality.</p>

<p>For a good introduction to Foxx, please refer to this <a href="https://docs.arangodb.com/cookbook/FoxxFirstSteps.html">cookbook</a>.</p>

<h2 id="conclusion">Conclusion</h2>

<p>ArangoDB is a powerful database with a wide range of supported use cases. It is well maintained and has very good documentation. Getting started with it is easy since there are packages for every major operating system. The web interface lowers the burden of exploring the features, and if you come from a relational background, using AQL is not that different from using SQL.</p>

<p>Having the option to extend the database with JavaScript applications, and the graph features, make ArangoDB a complete package to get an application started and growing.</p>

<p>So far we've shared the big picture of ArangoDB.</p>

<p>As next steps, we suggest the following:</p>

<ul>
<li><p>For any real application you will interact with the HTTP API. We didn't cover it here, because you most likely won't use it directly, but through one of the many <a href="https://www.arangodb.com/download">native language drivers</a>.</p></li>
<li><p>Interacting with the data in ArangoDB is done through AQL most of the time. Getting used to it is a must if you want to use ArangoDB in a production environment.</p></li>
<li><p>ArangoDB is not only a document store, but has very powerful graph features as well. It allows you to model your data as vertices in a directed graph. Relations can be modeled as edges between those vertices instead of using <code>_key</code> references. Modeling your data in this way <a href="https://www.youtube.com/watch?v=2_9Fd0Rqb5k">can have benefits</a> over the relational approach used in SQL databases.</p></li>
</ul>

    