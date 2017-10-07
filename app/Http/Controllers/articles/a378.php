<link rel='stylesheet' href='digital_ocean.css'><br><img src=https://community-cdn-digitalocean-com.global.ssl.fastly.net/assets/tutorials/images/large/couchDB_tw.png?1442592740/> <br> 
      <h3 id="introduction">Introduction</h3>

<p><a href="http://couchdb.apache.org/">Apache CouchDB</a>, like Redis, Cassandra, and MongoDB, is a <em>NoSQL database</em>. CouchDB stores data as JSON documents which are non-relational in nature. This allows users of CouchDB to store data in ways that look very similar to their real world counterparts.</p>

<p>You can manage CouchDB from the command line or from a web interface called Futon. Futon can be used to perform administrative tasks like creating and manipulating databases, documents, and users for CouchDB.</p>

<h3 id="goals">Goals</h3>

<p>By the end of this article, you will:</p>

<ul>
<li>Have CouchDB installed on a Droplet running Ubuntu 14.04</li>
<li>Have Futon installed on the same server</li>
<li>Have secured the CouchDB installation</li>
<li>Access CouchDB using Futon from your local machine, using a secure tunnel</li>
<li>Know how to add an admin user to CouchDB</li>
<li>Perform CRUD operations with CouchDB using Futon</li>
<li>Perform CRUD operations with CouchDB from the command line</li>
</ul>

<h2 id="prerequisites">Prerequisites</h2>

<p>Please complete the following prerequisites:</p>

<ul>
<li>Ubuntu 14.04 Droplet</li>
<li>You are logged in to your server as a non-root user with administrative privileges  (<a href="https://indiareads/community/tutorials/initial-server-setup-with-ubuntu-14-04">Initial Server Setup with Ubuntu 14.04</a>)</li>
</ul>

<p><span class="note"><strong>Note:</strong> While this tutorial assumes a non-root user, you can execute the steps below as the <code>root</code> user as well, in case you don't want to create a sudo user. Please note that if you do use a non-root user, you'll be asked for your password the first time you execute a command with <code>sudo</code>.<br /></span></p>

<h2 id="step-1-—-preparing-the-server">Step 1 — Preparing the Server</h2>

<p>Before we can install CouchDB, we need to ensure that the server is set up for it.</p>

<p>Begin by updating the system:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo apt-get update
</li></ul></code></pre>
<p>Install the software that allows you to manage the source repositories:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo apt-get install software-properties-common -y
</li></ul></code></pre>
<p><span class="note"><strong>Note:</strong> The <code>-y</code> flag tells the <code>apt-get</code> command to assume a <code>Yes</code> response to all the prompts that might come up during the installation process. You can drop this flag if you prefer responding manually to the prompts.<br /></span></p>

<p>Add the PPA that will help us fetch the latest CouchDB version from the appropriate repository:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo add-apt-repository ppa:couchdb/stable -y
</li></ul></code></pre>
<p><span class="warning"><strong>Warning:</strong> Great care should be taken while adding a new Personal Package Archive (PPA) to your server. Since anyone can create a PPA, there's no guarantee that it can be trusted or that it is secure. In this case, the above PPA is an official one, maintained by the Apache CouchDB team.<br /></span></p>

<p>Now that we have added a new PPA, let's update the system so that it has the latest package information:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo apt-get update
</li></ul></code></pre>
<p>We are now ready to install CouchDB and Futon.</p>

<h2 id="step-2-—-installing-couchdb">Step 2 — Installing CouchDB</h2>

<p>If you previously had CouchDB installed on this server, begin by removing the existing version:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo apt-get remove couchdb couchdb-bin couchdb-common -yf
</li></ul></code></pre>
<p><span class="note"><strong>Note:</strong> If you have a fresh Droplet, you can ignore this step.<br /></span></p>

<p>Now install CouchDB:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo apt-get install couchdb -y
</li></ul></code></pre>
<p>This will install CouchDB and Futon on your server.</p>

<p>By default, CouchDB runs on <strong>localhost</strong> and uses the port <strong>5984</strong>. You can retrieve this basic information by running <code>curl</code> from the command line:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">curl localhost:5984
</li></ul></code></pre>
<p><span class="note"><strong>Note:</strong> If you don't have <code>curl</code> installed, you can use the <code>sudo apt-get install curl</code> command to install it.<br /></span></p>

<p>You should get something similar to the following:</p>
<pre class="code-pre "><code langs=""><div class="secondary-code-label " title="Output">Output</div>{"couchdb":"Welcome","uuid":"b9f278c743b5fc0b971c4e587d77582e","version":"1.6.1","vendor":{"name":"Ubuntu","version":"14.04"}}
</code></pre>
<p>You can now create a new database with the <code>curl -X PUT</code> command:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">curl -X PUT localhost:5984/new_database
</li></ul></code></pre>
<p>The results should look like this:</p>
<pre class="code-pre "><code langs=""><div class="secondary-code-label " title="Output">Output</div>{"ok":true}
</code></pre>
<h2 id="step-3-—-securing-the-couchdb-installation">Step 3 — Securing the CouchDB Installation</h2>

<p>By default, some of the files and directories created when CouchDB is installed belong to the <strong>root</strong> user and group. While this is fine (albeit not advisable) during development, it could be a security risk in production.</p>

<p>When CouchDB is installed, it creates a user and a group named <strong>couchdb</strong>. In this section we will change the ownership and permission of the CouchDB files to the <strong>couchdb</strong> user and group.</p>

<p>Changing the ownership controls <em>what</em> the CouchDB process can access, and changing the permissions controls <em>who</em> can access the CouchDB files and directories.</p>

<p>Before changing the ownership and permissions, stop CouchDB:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo stop couchdb
</li></ul></code></pre>
<p>Change the ownership of the <code>/usr/lib/couchdb</code>, <code>/usr/share/couchdb</code>, and <code>/etc/couchdb</code> directories, and the <code>/usr/bin/couchdb</code> executable file, such that their owner is <strong>couchdb</strong> and they belong to the <strong>couchdb</strong> group.</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo chown -R couchdb:couchdb /usr/lib/couchdb /usr/share/couchdb /etc/couchdb /usr/bin/couchdb
</li></ul></code></pre>
<p>Now, change the permissions of the <code>/usr/lib/couchdb</code>, <code>/usr/share/couchdb</code>, and <code>/etc/couchdb</code> directories, and the <code>/usr/bin/couchdb</code> executable file, such that the <strong>couchdb</strong> user and the <strong>couchdb</strong> group have complete access (to the CouchDB installation) while no other user has access to these files and directories.</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo chmod -R 0770 /usr/lib/couchdb /usr/share/couchdb /etc/couchdb /usr/bin/couchdb
</li></ul></code></pre>
<p>All that's left to do is restart CouchDB:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo start couchdb
</li></ul></code></pre>
<p>CouchDB should now be up and running without any of its files or directories belonging to either the <strong>root</strong> user or the <strong>root</strong> group.</p>

<h2 id="step-4-—-accessing-futon">Step 4 — Accessing Futon</h2>

<p>CouchDB offers a convenient web-based control panel called Futon. We're going to access it from your <strong>local workstation</strong>, tunneling the traffic through an SSH connection to your server. This means that only users with an SSH login to your server can access the Futon control panel.</p>

<p>To connect securely to CouchDB, without making it publicly available, you can create an SSH tunnel from your local port 5984 to the remote server's port 5984.</p>

<p>You can use the following command, run from your <strong>local computer</strong>, to set up the tunnel:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">ssh -L5984:127.0.0.1:5984 <span class="highlight">sammy</span>@<span class="highlight">your_server_ip</span>
</li></ul></code></pre>
<p><span class="note"><strong>Note:</strong> Remember to replace <span class="highlight">sammy</span> with your username and <span class="highlight">your_server_ip</span> with the IP address of your Droplet.<br /></span></p>

<p>While the connection is open, you can access Futon from your favorite web browser, using port 5984. Visit this URL to display the helpful Futon page:</p>
<pre class="code-pre "><code langs="">http://localhost:5984/_utils
</code></pre>
<p><img src="https://assets.digitalocean.com/articles/couchdb-ubuntu1404/CYxH1GC.png" alt="Futon Home Page Screenshot" /></p>

<p>By default, all CouchDB users who access Futon do so with administrative rights. This is announced in the bottom right corner:</p>

<p><img src="https://assets.digitalocean.com/articles/couchdb-ubuntu1404/hD4Pmx4.png" alt="Welcome to Admin Party! Everyone is admin. Fix this" /></p>

<p>You can change this by clicking on the little <strong>Fix this</strong> link and creating new administrators.</p>

<h2 id="step-5-—-adding-an-admin-user">Step 5 — Adding an Admin User</h2>

<p>Now that we have CouchDB up and running, let's start using it.</p>

<p>Before an admin user is created, all users can access CouchDB with administrative privileges (although they require SSH access to the server first).</p>

<p>It's a good practice to create an admin account for CouchDB, to prevent accidental or unauthorized data loss.</p>

<p>To do this, click the <strong>Fix this</strong> link that appears in the bottom right corner of Futon. This will bring up a screen that allows you to create a CouchDB admin user, as follows:</p>

<p><img src="https://assets.digitalocean.com/articles/couchdb-ubuntu1404/VemRmsp.png" alt="Admin User Creation Screen" /></p>

<p>Enter the desired username and password:</p>

<p><img src="https://assets.digitalocean.com/articles/couchdb-ubuntu1404/bj6fHXw.png" alt="Enter a username, such as "admin", and a password" /></p>

<p>After entering your new CouchDB username and a secure password, click the <strong>Create</strong> button. This will create the new admin user. The message in the bottom right corner of Futon will confirm this by showing a message similar to the following:</p>

<p><img src="https://assets.digitalocean.com/articles/couchdb-ubuntu1404/q91ytKn.png" alt="Welcome admin! Setup more admins or Change password or Logout" /></p>

<span class="note"><p>
<strong>Note:</strong> The creation of an admin user prevents unauthorized users from deleting and modifying databases, design documents, and the CouchDB configuration. However, it doesn't prevent them from creating or accessing documents.</p>

<p>Be careful about handing out SSH access to your server.</p></span>

<p>That's it! Our CouchDB server is now fully configured.</p>

<p>To learn more about using the database, keep reading.</p>

<h2 id="performing-crud-operations-from-futon">Performing CRUD Operations from Futon</h2>

<p>Futon has a very simple but useful user interface which allows you to perform basic CRUD operations (create, read, update, and delete).</p>

<p>In this section, we will create a new database named <code>todos</code>, add a new document to it, and then retrieve, update, and delete this document.</p>

<p><span class="note"><strong>Note:</strong> If you have created an admin user, you will have to be logged in as the administrator to create a new database.<br /></span></p>

<p>Make sure you still have your SSH tunnel open. If not, open your connection to the server from your <strong>local computer</strong> with this command:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">ssh -L5984:127.0.0.1:5984 <span class="highlight">sammy</span>@<span class="highlight">your_server_ip</span>
</li></ul></code></pre>
<p>Let's begin by visiting the Futon page at <code>http://localhost:5984/_utils/</code>.</p>

<p><span class="note"><strong>Note:</strong> This section assumes that CouchDB is being accessed using an SSH tunnel that was set up as described in in the <strong>Accessing Futon</strong> section above. If your setup is different, make sure you access Futon at the correct URL.<br /></span></p>

<h3 id="create-a-database-and-document">Create a Database and Document</h3>

<p><img src="https://assets.digitalocean.com/articles/couchdb-ubuntu1404/a2mVsjd.png" alt="Futon Homepage" /></p>

<p>To create a new database called <code>todos</code>, click the <strong>Create Database</strong> link on the screen. This will bring up a dialog as follows:</p>

<p><img src="https://assets.digitalocean.com/articles/couchdb-ubuntu1404/b7tzN2C.png" alt="New Database Dialog; enter Database Name" /></p>

<p>Enter the name of the database and click the <strong>Create</strong> button.</p>

<p>This will create a new database named <code>todos</code> and take you to a page where you can start creating and modifying documents in the newly created database.</p>

<p><img src="https://assets.digitalocean.com/articles/couchdb-ubuntu1404/CJQo6up.png" alt=""todos" Database Page" /></p>

<p><strong>Create Document</strong></p>

<p>To create a new document, click the <strong>New Document</strong> link on the page.</p>

<p>This will open up a screen with a new document. This document will have just the <code>_id</code> field. You can change the value of this field if you need to or you can leave it as is.</p>

<p><img src="https://assets.digitalocean.com/articles/couchdb-ubuntu1404/ktFDUuS.png" alt="Click the Add Field link; double-click the null value to update it" /></p>

<p>Click the <strong>Add Field</strong> link to add a new field to this document.</p>

<p>As can be seen above, we have added two fields named <code>todo</code> and <code>done</code>. By default, new fields have a <code>null</code> value.</p>

<p>Double-click the value to change it.</p>

<p>In this example, we have double-clicked the value fields of <code>todo</code> and <code>done</code> and have entered the values <code>Task 1</code> and <code>false</code> respectively.</p>

<p>Once you have entered the values, either press the <code>ENTER</code> key or click the little green check mark next the field to save its contents. (Failing to do this will leave the value of the field as <code>null</code>.) This should look as follows:</p>

<p><img src="https://assets.digitalocean.com/articles/couchdb-ubuntu1404/yBiUNZK.png" alt="Create and Save Field and Values" /></p>

<p>To save the document, click the <strong>Save Document</strong> link. After the document is saved, you will see that a <code>_rev</code> field has been added to it as follows:</p>

<p><img src="https://assets.digitalocean.com/articles/couchdb-ubuntu1404/820r4Jb.png" alt="_rev field" /></p>

<h3 id="read-a-document">Read a Document</h3>

<p>Click on the <code>todos</code> link (in the top bar next to the <code>Overview</code> link) to view the newly-created document, as the only document in the <code>todos</code> database.</p>

<p><img src="https://assets.digitalocean.com/articles/couchdb-ubuntu1404/Bzq3136.png" alt="Todos Database Page" /></p>

<p>Click on the key of the document (the ID) in the table to access the document details page.</p>

<h3 id="edit-a-document">Edit a Document</h3>

<p>On this page, you can edit and update the document fields as follows:</p>

<p><img src="https://assets.digitalocean.com/articles/couchdb-ubuntu1404/9itT5Dt.png" alt="Document Details Page" /></p>

<p>To edit a field value, double-click it and start editing.</p>

<p>You can delete any field (apart from the <code>_id</code> and <code>_rev</code> fields), add new fields, or change the values of existing fields. In this example, we have changed the value of the <code>done</code> field from <code>false</code> to <code>true</code> as follows:</p>

<p><img src="https://assets.digitalocean.com/articles/couchdb-ubuntu1404/hrQk7aj.png" alt="Change Field Value: "done" field from "false" to "true"" /></p>

<p>After you are satisfied with the changes, click the <strong>Save Document</strong> link to update the document. Once you do so, you'll notice that the value of the <code>_rev</code> field has been updated as well.</p>

<p><img src="https://assets.digitalocean.com/articles/couchdb-ubuntu1404/A95Xce2.png" alt="Save the changes" /></p>

<h3 id="delete-a-document">Delete a Document</h3>

<p>To delete a document, you can click the <strong>Delete Document</strong> link which will prompt you for confirmation:</p>

<p><img src="https://assets.digitalocean.com/articles/couchdb-ubuntu1404/EHbvpVj.png" alt="Are you sure you want to delete this document?" /></p>

<p>Confirm by pressing the <strong>Delete</strong> button.</p>

<p>Futon will delete the document and take you to the <code>todos</code> database page, which should now be empty, confirming that the document has indeed been deleted.</p>

<p><img src="https://assets.digitalocean.com/articles/couchdb-ubuntu1404/CJQo6up.png" alt="Todos Database Page" /></p>

<h2 id="performing-crud-operations-from-the-command-line">Performing CRUD Operations from the Command Line</h2>

<p>This section will illustrate how we can perform basic CRUD (create, read, update, and delete) operations on a CouchDB database from the command line using <code>curl</code>.</p>

<p>Make sure you still have your SSH tunnel open. If not, open your connection to the server from your <strong>local computer</strong> with this command:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">ssh -L5984:127.0.0.1:5984 <span class="highlight">sammy</span>@<span class="highlight">your_server_ip</span>
</li></ul></code></pre>
<p><span class="note"><strong>Note:</strong> This section will use the database <code>new_database</code> that was created in Step 2 (<strong>Installing CouchDB</strong>) above. This section will also assume that we are accessing CouchDB using an SSH tunnel as described in the <strong>Accessing Futon</strong> step above. If your setup is different, make sure you replace the URL, PORT, and the database names appropriately while executing the commands used below.<br /></span></p>

<h3 id="create-a-database">Create a Database</h3>

<p>If you didn't already create the database <code>new_database</code>, please do so now. This command should be executed from your <strong>local workstation</strong>:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$"> curl -X PUT http://localhost:5984/new_database -u "admin:password"
</li><li class="line" prefix="$">{"ok":true}
</li></ul></code></pre>
<p>Since we added an admin user to CouchDB, we now have to send the admin username and password when creating a new database.</p>

<p>The results should look like this:</p>
<pre class="code-pre "><code langs=""><div class="secondary-code-label " title="Output">Output</div>{"ok":true}
</code></pre>
<h3 id="create-a-document">Create a Document</h3>

<p>Let's begin by creating a new document.</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">curl -X POST -d '{"todo":"task 1", "done":false}' http://localhost:5984/new_database -H "Content-Type:application/json"
</li></ul></code></pre>
<p>This command creates a new document in the <code>new_database</code> database.</p>

<p>The <code>-X</code> flag indicates that we are performing an HTTP POST operation. The <code>-H</code> flag followed by the header sets the content type of this request as <code>application/json</code> since we are POSTing a JSON document. Finally, the JSON document itself is included, along with the <code>-d</code> flag.</p>

<p>The response of this operation is as follows:</p>
<pre class="code-pre "><code langs=""><div class="secondary-code-label " title="Output">Output</div>{"ok":true,"id":"803da996e1524591ce773d24400004ff","rev":"1-2fc1d70532433c39c9f61480607e3681"}
</code></pre>
<p>The <code>"ok":true</code> part of this response indicates that the operation was successful. The response includes the fields <code>id</code> and <code>rev</code>, which represent the document ID and the document revision respectively. Both these fields will be required in case this document needs to be modified or deleted.</p>

<p>In this example, the document ID was generated by CouchDB because we didn't supply it with the command. If required, we can create a document with a unique ID that we have generated.</p>

<p><strong>Create with Specified ID</strong></p>

<p>Create a document with the ID <code>random_task</code>:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">curl -X POST -d '{"_id":"random_task", "todo":"task 2", "done":false}' http://localhost:5984/new_database -H "Content-Type:application/json"
</li></ul></code></pre>
<p>This command creates a new document with the ID set to <code>random_task</code>. The response to this command is as follows:</p>
<pre class="code-pre "><code langs=""><div class="secondary-code-label " title="Output">Output</div>{"ok":true,"id":"random_task","rev":"<span class="highlight">1-bceeae3c4a9154c87db1649473316e44</span>"}
</code></pre>
<p><strong>Create Multiple Documents</strong></p>

<p>In addition to creating single documents, we can also create documents in bulk.</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">curl -X POST -d '{"docs": [{"todo":"task 3", "done":false}, {"todo":"task 4", "done":false}]}' http://localhost:5984/new_database/_bulk_docs -H "Content-Type:application/json"
</li></ul></code></pre>
<p>This command will create two documents as specified in the POST body. There are two slight differences, as compared to the single-document inserts:</p>

<ol>
<li>While inserting a single document, the POST body was just a standard JSON object. In case of bulk inserts, the POST body comprises an object with a <code>docs</code> field. This field holds the array of documents that are to be inserted.</li>
<li>While inserting a single document, the POST request was sent to the URL pointing to the database (<code>http://localhost:5984/new_database</code>). The request for bulk inserts, however, POSTs to the <code>http://localhost:5984/new_database/_bulk_docs</code> URL.</li>
</ol>

<p>The response of the bulk insert operation is as follows:</p>
<pre class="code-pre "><code langs=""><div class="secondary-code-label " title="Output">Output</div>[{"ok":true,"id":"803da996e1524591ce773d24400007df","rev":"1-778fd61f8f460d0c1df1bb174279489d"},{"ok":true,"id":"803da996e1524591ce773d2440001723","rev":"1-dc9e84861bba58e5cfefeed8f5133636"}]
</code></pre>
<h3 id="read-a-document">Read a Document</h3>

<p>Retrieving a document from a CouchDB database is a simple matter of issuing an HTTP GET command. Let's try to retrieve one of the documents we created above: the one called <code>random_task</code>.</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">curl -X GET http://localhost:5984/new_database/random_task
</li></ul></code></pre>
<p>Note that the URL includes the ID (<code>random_task</code>) of the document which is being retrieved. The response to this GET request, as shown below, contains the entire document along with the <code>_id</code> and the <code>_rev</code> fields, which can be used to update or delete this document.</p>
<pre class="code-pre "><code langs=""><div class="secondary-code-label " title="Output">Output</div>{"_id":"random_task","_rev":"<span class="highlight">1-bceeae3c4a9154c87db1649473316e44</span>","todo":"task 2","done":false}
</code></pre>
<h3 id="edit-a-document">Edit a Document</h3>

<p>While trying to update a document, it is important to include the <code>_rev</code> field. CouchDB will reject any update request which doesn't include a <code>_rev</code> field. Since CouchDB updates the entire document, and not just parts of it, the entire document must be sent in the request body during an update operation.</p>

<p>To update the document that was created with the ID <code>random_task</code>, we need to issue an HTTP PUT request as follows:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">curl -X PUT -d '{"_rev":"<span class="highlight">1-bceeae3c4a9154c87db1649473316e44</span>", "todo":"task 2", "done":true}' http://localhost:5984/new_database/random_task
</li></ul></code></pre>
<p>Be sure to replace the <code>_rev</code> value with the string you received in the previous output.</p>

<p>This modifies the document and updates the <code>done</code> field to <code>true</code>. The response to this request is as follows:</p>
<pre class="code-pre "><code langs=""><div class="secondary-code-label " title="Output">Output</div>{"ok":true,"id":"random_task","rev":"<span class="highlight">2-4cc3dfb6e76befd665faf124b36b7f1c</span>"}
</code></pre>
<p>As can be seen in the response, the <code>rev</code> field for this particular document changes after it has been updated. Any future request to update or delete this document will now have to use the newest <code>rev</code> value.</p>

<h3 id="delete-a-document">Delete a Document</h3>

<p>Let's use this new <code>rev</code> value to delete this document using an HTTP DELETE request as follows:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">curl -X DELETE http://localhost:5984/new_database/random_task?rev=<span class="highlight">2-4cc3dfb6e76befd665faf124b36b7f1c</span>
</li></ul></code></pre>
<p>Just like the GET & PUT requests above, the DELETE request uses the URL that points to the document. However, it also includes an additional query parameter in the URL. This parameter, <code>rev</code>, should have the latest <code>_rev</code> value for the delete operation to be successful.</p>

<p>In this particular case, we use the value that was returned after the update operation in the previous step. The response to the above request is shown below.</p>
<pre class="code-pre "><code langs=""><div class="secondary-code-label " title="Output">Output</div>{"ok":true,"id":"random_task","rev":"<span class="highlight">3-07d6cde68be2a559497ec263045edc9d</span>"}
</code></pre>
<h2 id="restarting-stopping-and-starting-the-couchdb-service">Restarting, Stopping, and Starting the CouchDB Service</h2>

<p>Starting, stopping and restarting the CouchDB service is quite straightforward. Complete these steps from the <strong>server</strong>.</p>

<h3 id="restart">Restart</h3>

<p>To restart a running CouchDB instance, execute the following command:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo restart couchdb
</li></ul></code></pre>
<p>This command will restart a running CouchDB instance and display the process ID of the new instance. In case there is no instance of CouchDB running, executing this command will give a message like</p>
<pre class="code-pre "><code langs=""><div class="secondary-code-label " title="Output">Output</div>restart: Unknown instance:
</code></pre>
<h3 id="stop">Stop</h3>

<p>To stop a running CouchDB instance, execute the following command:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo stop couchdb
</li></ul></code></pre>
<p>Executing this command will stop any running CouchDB instance and provide a confirmation message like this:</p>
<pre class="code-pre "><code langs=""><div class="secondary-code-label " title="Output">Output</div>couchdb stop/waiting
</code></pre>
<h3 id="start">Start</h3>

<p>To start CouchDB, execute the following command:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo start couchdb
</li></ul></code></pre>
<p>If CouchDB wasn't already running, executing this command will start CouchDB and provide a confirmation message like this:</p>
<pre class="code-pre "><code langs=""><div class="secondary-code-label " title="Output">Output</div>couchdb start/running, process 12345
</code></pre>
<p>On the other hand, if there was a CouchDB instance already running then executing the above command will result in a message like this:</p>
<pre class="code-pre "><code langs=""><div class="secondary-code-label " title="Output">Output</div>start: Job is already running: couchdb
</code></pre>
<h3 id="status">Status</h3>

<p>In case you want to check the status of CouchDB, you can do so using the following command:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo status couchdb
</li></ul></code></pre>
<p>If CouchDB is running, this will give a message similar to the following:</p>
<pre class="code-pre "><code langs=""><div class="secondary-code-label " title="Output">Output</div>couchdb start/running, process 12345
</code></pre>
<p>If CouchDB is not running, checking the status will result in something like:</p>
<pre class="code-pre "><code langs=""><div class="secondary-code-label " title="Output">Output</div>couchdb stop/waiting
</code></pre>
<h2 id="conclusion">Conclusion</h2>

<p>You now have a fully functional setup of CouchDB on your Droplet, which you can securely administer from your local machine using Futon or the command line.</p>

    