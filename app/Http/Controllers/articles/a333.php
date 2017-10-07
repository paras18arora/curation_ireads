<link rel='stylesheet' href='digital_ocean.css'><br> <br> 
      <h3 id="introduction">Introduction</h3>

<hr />

<p>MongoDB is an extremely popular NoSQL database.  It is often used to store and manage application data and website information.  MongoDB boasts a dynamic schema design, easy scalability, and a data format that is easily accessible programmatically.</p>

<p>In this guide, we will discuss how to set up data replication in order to ensure high availability of data and create a robust failover system.  This is important in any production environment where a database going down would have a negative impact on your organization or business.</p>

<p>We will assume that you have already installed MongoDB on your system.  For information on <a href="https://indiareads/community/articles/how-to-install-mongodb-on-ubuntu-12-04">how to install MongoDB on Ubuntu 12.04</a>, click here.</p>

<h2 id="what-is-a-mongodb-replication-set">What is a MongoDB Replication Set?</h2>

<hr />

<p>MongoDB handles replication through an implementation called "replication sets".  Replication sets in their basic form are somewhat similar to nodes in a master-slave configuration.  A single primary member is used as the base for applying changes to secondary members.</p>

<p>The difference between a replication set and master-slave replication is that a replication set has an intrinsic automatic failover mechanism in case the primary member becomes unavailable.</p>

<ul>
<li><strong>Primary member</strong>: The primary member is the default access point for transactions with the replication set.  It is the only member that can accept write operations.</li>
</ul>

<p>Each replication set can have only one primary member at a time.  This is because replication happens by copying the primary's "oplog" (operations log) and repeating the changes on the secondary's dataset.  Multiple primaries accepting write operations would lead to data conflicts.</p>

<ul>
<li><strong>Secondary members</strong>: A replication set can contain multiple secondary members.  Secondary members reproduce changes from the oplog on their own data.</li>
</ul>

<p>Although by default applications will query the primary member for both read and write operations, you can configure your setup to read from one or more of the secondary members.  A secondary member can become the primary if the primary goes offline or steps down.</p>

<p><em>Note: Due to the fact that data is transfered asynchronously, reads from secondary nodes can result in old data being served.  If this is a concern for your use-case, you should not enable this functionality.</em></p>

<ul>
<li><strong>Arbiter</strong>: An arbiter is an optional member of a replication set that does not take part in the actual replication process.  It is added to the replication set to participate in only a single, limited function: to act as a tie-breaker in elections.</li>
</ul>

<p>In the event that the primary member becomes unavailable, an automated election process happens among the secondary nodes to choose a new primary.  If the secondary member pool contains an even number of nodes, this could result in an inability to elect a new primary due to a voting impasse.  The arbiter votes in these situations to ensure a decision is reached.</p>

<p>If a replication set has only one secondary member, an arbiter is required.</p>

<h2 id="secondary-member-customization-options">Secondary Member Customization Options</h2>

<hr />

<p>There are instances where you may not want all of your secondary members to be beholden to the standard rules for a replication set.  A replication set can have up to 12 members and up to 7 will vote in an election situation.</p>

<h3 id="priority-0-replication-members">Priority 0 Replication Members</h3>

<hr />

<p>There are some situations where the election of certain set members to the primary position could have a negative impact on your application's performance.</p>

<p>For instance, if you are replicating data to a remote datacenter or a specific member's hardware is inadequate to perform as the main access point for the set, setting priority 0 can ensure that this member will not become a primary but can continue copying data.</p>

<h3 id="hidden-replication-members">Hidden Replication Members</h3>

<hr />

<p>Some situations require you to separate the main set of members accessible and visible to your clients from the background members that have separate purposes and should not interfere.</p>

<p>For instance, you may need a secondary member to be the base for analytics work, which would benefit from an up-to-date dataset but would cause a strain on working members.  By setting this member to hidden, it will not interfere with the general operations of the replication set.</p>

<p>Hidden members are necessarily set to priority 0 to avoid becoming the primary member, but they do vote in elections.</p>

<h3 id="delayed-replication-members">Delayed Replication Members</h3>

<hr />

<p>By setting the delay option for a secondary member, you can control how long the secondary waits to perform each action it copies from the primary's oplog.</p>

<p>This is useful if you would like to safeguard against accidental deletions or recover from destructive operations.  For instance, if you delay a secondary by a half-day, it would not immediately perform accidental operations on its own set of data and could be used to revert changes.</p>

<p>Delayed members cannot become primary members, but can vote in elections.  In the vast majority of situations, they should be hidden to prevent processes from reading data that is out-of-date.</p>

<h2 id="how-to-configure-a-replication-set">How to Configure a Replication set</h2>

<hr />

<p>To demonstrate how to configure replication sets, we will configure a simple set with a primary and two secondaries.  This means that you will need three VPS instances to follow along.  We will be using Ubuntu 12.04 machines.</p>

<p>You will need to install MongoDB on each of the machines that will be members of the set.  You can follow this tutorial to learn <a href="https://indiareads/community/articles/how-to-install-mongodb-on-ubuntu-12-04">how to install MongoDB on Ubuntu 12.04</a>.</p>

<p>Once you have installed MongoDB on all three of the server instances, we need to configure some things that will allow our droplets to communicate with each other.</p>

<p>The following steps assume that you are logged in as the root user.</p>

<h3 id="set-up-dns-resolution">Set Up DNS Resolution</h3>

<hr />

<p>In order for our MongoDB instances to communicate with each other effectively, we will need to configure our machines to resolve the proper hostname for each member.  You can either do this by <a href="https://indiareads/community/articles/how-to-set-up-and-test-dns-subdomains-with-digitalocean-s-dns-panel">configuring subdomains for each replication member</a> or through editing the <code>/etc/hosts</code> file on each computer.</p>

<p>It is probably better to use subdomains in the long run, but for the sake of getting off the ground quickly, we will do this through the hosts file.</p>

<p>On each of the soon-to-be replication members, edit the <code>/etc/hosts</code> file:</p>
<pre class="code-pre "><code langs="">nano /etc/hosts
</code></pre>
<p>After the first line that configures the localhost, you should add an entry for each of the replication sets members.  These entries take the form of:</p>

<pre>
<span class="highlight">ip_address</span>   mongohost0.example.com
</pre>

<p>You can get the IP addresses of the members of your set in the IndiaReads control panel.  The name you choose as a hostname for that computer is arbitrary, but should be descriptive.</p>

<p>For our example, our <code>/etc/hosts</code> would look something like this:</p>

<pre>127.0.0.1           localhost <span class="highlight">mongo0</span>
123.456.789.111     mongo0.example.com
123.456.789.222     mongo1.example.com
123.456.789.333     mongo2.example.com</pre>

This file should (mostly) be the same across all of the hosts in your set.  Save and close the file on each of your members.

Next, you need to set the hostname of your droplet to reflect these new changes.  The command on each VPS will reflect the name you gave that specific machine in the `/etc/hosts` file.  You should issue a command on each server that looks like:

<pre>
hostname <span class="highlight">mongo0.example.com</span>
</pre>

<p>Modify this command on each server to reflect the name you selected for it in the file.</p>

<p>Edit the <code>/etc/hostname</code> file to reflect this as well:</p>

<pre>
nano /etc/hostname
</pre>

<pre>
<span class="highlight">mongo0.example.com</span>
</pre>

<p>These steps should be performed on each node.</p>

<h3 id="prepare-for-replication-in-the-mongodb-configuration-file">Prepare for Replication in the MongoDB Configuration File</h3>

<hr />

<p>The first thing we need to do to begin the MongoDB configuration is stop the MongoDB process on each server.</p>

<p>On each sever, type:</p>
<pre class="code-pre "><code langs="">service mongodb stop
</code></pre>
<p>Now, we need to configure a directory that will be used to store our data.  Create a directory with the following command:</p>
<pre class="code-pre "><code langs="">mkdir /mongo-metadata
</code></pre>
<p>Now that we have the data directory created, we can modify the configuration file to reflect our new replication set configuration:</p>
<pre class="code-pre "><code langs="">nano /etc/mongodb.conf
</code></pre>
<p>In this file, we need to specify a few parameters.  First, adjust the <code>dbpath</code> variable to point to the directory we just created:</p>
<pre class="code-pre "><code langs="">dbpath=/mongo-metadata
</code></pre>
<p>Remove the comment from in front of the port number specification to ensure that it is started on the default port:</p>
<pre class="code-pre "><code langs="">port = 27017
</code></pre>
<p>Towards the bottom of the file, remove the comment form in front of the <code>replSet</code> parameter.  Change the value of this variable to something that will be easy to recognize for you.</p>
<pre class="code-pre "><code langs="">replSet = rs0
</code></pre>
<p>Finally, you should make the process fork so that you can use your shell after spawning the server instance.  Add this to the bottom of the file:</p>
<pre class="code-pre "><code langs="">fork = true
</code></pre>
<p>Save and close the file.  Start the replication member by issuing the following command:</p>
<pre class="code-pre "><code langs="">mongod --config /etc/mongodb.conf
</code></pre>
<p>These steps must be repeated on each member of the replication set.</p>

<h3 id="start-the-replication-set-and-add-members">Start the Replication Set and Add Members</h3>

<hr />

<p>Now that you have configured each member of the replication set and started the <code>mongod</code> process on each machine, you can initiate the replication and add each member.</p>

<p>On one of your members, type:</p>
<pre class="code-pre "><code langs="">mongo
</code></pre>
<p>This will give you a MongoDB prompt for the current member.</p>

<p>Start the replication set by entering:</p>
<pre class="code-pre "><code langs="">rs.initiate()
</code></pre>
<p>This will initiate the replication set and add the server you are currently connected to as the first member of the set.  You can see this by typing:</p>
<pre class="code-pre "><code langs="">rs.conf()
</code></pre>
<hr />
<pre class="code-pre "><code langs="">{
    "_id" : "rs0"
    "version" : 1,
    "members" : [
        {
            "_id" : 0,
            "host" "mongo0.example.com:27017"
        }
    ]
}
</code></pre>
<p>Now, you can add the additional nodes to the replication set by referencing the hostname you gave them in the <code>/etc/hosts</code> file:</p>
<pre class="code-pre "><code langs="">rs.add("mongo1.example.com")
</code></pre>
<hr />
<pre class="code-pre "><code langs="">{ "ok" : 1 }
</code></pre>
<p>Do this for each of your remaining replication members.  Your replication set should now be up and running.</p>

<h2 id="conclusion">Conclusion</h2>

<hr />

<p>By properly configuring replication sets for each of your data storage targets, your databases will be protected in some degree from unavailability and hardware failure.  This is essential for any production system.</p>

<p>Replication sets provide a seamless interface with applications because they are essentially invisible to the outside.  All replication mechanics are handled internally.  If you plan on implementing <a href="https://indiareads/community/articles/how-to-create-a-sharded-cluster-in-mongodb-using-an-ubuntu-12-04-vps">MongoDB sharding</a>, it is a good idea to implement replication sets for each of the shard server components.</p>

    