<link rel='stylesheet' href='digital_ocean.css'><br> <br> 
      <h3 id="introduction">Introduction</h3>

<p><a href="http://cassandra.apache.org/">Apache Cassandra</a> is a highly scalable open source database system, achieving great performance on multi-node setups.</p>

<p>Previously, we went over <a href="https://indiareads/community/tutorials/how-to-install-cassandra-and-run-a-single-node-cluster-on-ubuntu-14-04">how to run a single-node Cassandra cluster</a>. In this tutorial, you’ll learn how to install and use Cassandra to run a multi-node cluster on Ubuntu 14.04.</p>

<h2 id="prerequisites">Prerequisites</h2>

<p>Because you're about to build a multi-node Cassandra cluster, you must determine how many servers you'd like to have in your cluster and configure each of them. It is recommended, but not required, that they have the same or similar specifications.</p>

<p>To complete this tutorial, you'll need the following:</p>

<ul>
<li><p>At least two Ubuntu 14.04 servers configured using <a href="https://indiareads/community/tutorials/initial-server-setup-with-ubuntu-14-04">this initial setup guide</a>.</p></li>
<li><p>Each server must be secured with a firewall using <a href="https://indiareads/community/tutorials/how-to-implement-a-basic-firewall-template-with-iptables-on-ubuntu-14-04">this IPTables guide</a>.</p></li>
<li><p>Each server must also have Cassandra installed by following <a href="https://indiareads/community/tutorials/how-to-install-cassandra-and-run-a-single-node-cluster-on-ubuntu-14-04">this Cassandra installation guide</a>.</p></li>
</ul>

<h2 id="step-1-—-deleting-default-data">Step 1 — Deleting Default Data</h2>

<p>Servers in a Cassandra cluster are known as <em>nodes</em>. What you have on each server right now is a single-node Cassandra cluster. In this step, we'll set up the nodes to function as a multi-node Cassandra cluster.</p>

<p>All the commands in this and subsequent steps must be repeated on each node in the cluster, so be sure to have as many terminals open as you have nodes in the cluster.</p>

<p>The first command you'll run on each node will stop the Cassandra daemon.</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo service cassandra stop
</li></ul></code></pre>
<p>When that's completed, delete the default dataset.</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo rm -rf /var/lib/cassandra/data/system/*
</li></ul></code></pre>
<h2 id="step-2-—-configuring-the-cluster">Step 2 — Configuring the Cluster</h2>

<p>Cassandra's configuration file is located in the <code>/etc/cassandra</code> directory. That configuration file, <code>cassandra.yaml</code>, contains many directives and is very well commented. In this step, we'll modify that file to set up the cluster.</p>

<p>Only the following directives need to be modified to set up a multi-node Cassandra cluster:</p>

<ul>
<li><p><code>cluster_name</code>: This is the name of your cluster.</p></li>
<li><p><code>-seeds</code>: This is a comma-delimited list of the IP address of each node in the cluster.</p></li>
<li><p><code>listen_address</code>: This is IP address that other nodes in the cluster will use to connect to this one. It defaults to <strong>localhost</strong> and needs changed to the IP address of the node.</p></li>
<li><p><code>rpc_address</code>: This is the IP address for remote procedure calls. It defaults to <strong>localhost</strong>. If the server's hostname is properly configured, leave this as is. Otherwise, change to server's IP address or the loopback address (<code>127.0.0.1</code>).</p></li>
<li><p><code>endpoint_snitch</code>: Name of the snitch, which is what tells Cassandra about what its network looks like. This defaults to <strong>SimpleSnitch</strong>, which is used for networks in one datacenter. In our case, we'll change it to <strong>GossipingPropertyFileSnitch</strong>, which is preferred for production setups. </p></li>
<li><p><code>auto_bootstrap</code>: This directive is not in the configuration file, so it has to be added and set to <strong>false</strong>. This makes new nodes automatically use the right data. It is optional if you're adding nodes to an existing cluster, but required when you're initializing a fresh cluster, that is, one with no data.</p></li>
</ul>

<p>Open the configuration file for editing using <code>nano</code> or your favorite text editor.</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo nano /etc/cassandra/cassandra.yaml
</li></ul></code></pre>
<p>Search the file for the following directives and modify them as below to match your cluster. Replace <code><span class="highlight">your_server_ip</span></code> with the IP address of the server you're currently working on. The <code>- seeds:</code> list should be the same on every server, and will contain each server's IP address separated by commas.</p>
<div class="code-label " title="/etc/cassandra/cassandra.yaml">/etc/cassandra/cassandra.yaml</div><pre class="code-pre "><code langs="">. . .

cluster_name: '<span class="highlight">CassandraDOCluster</span>'

. . .

seed_provider:
  - class_name: org.apache.cassandra.locator.SimpleSeedProvider
    parameters:
         - seeds: "<span class="highlight">your_server_ip</span>,<span class="highlight">your_server_ip_2</span>,...<span class="highlight">your_server_ip_n</span>"

. . .

listen_address: <span class="highlight">your_server_ip</span>

. . .

rpc_address: <span class="highlight">your_server_ip</span>

. . .

endpoint_snitch: <span class="highlight">GossipingPropertyFileSnitch</span>

. . .
</code></pre>
<p>At the bottom of the file, add in the <code>auto_bootstrap</code> directive by pasting in this line:</p>
<div class="code-label " title="/etc/cassandra/cassandra.yaml">/etc/cassandra/cassandra.yaml</div><pre class="code-pre "><code langs=""><span class="highlight">auto_bootstrap: false</span>
</code></pre>
<p>When you're finished modifying the file, save and close it. Repeat this step for all the servers you want to include in the cluster.</p>

<h2 id="step-3-—-configuring-the-firewall">Step 3 — Configuring the Firewall</h2>

<p>At this point, the cluster has been configured, but the nodes are not communicating. In this step, we'll configure the firewall to allow Cassandra traffic.</p>

<p>First, restart the Cassandra daemon on each.</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo service cassandra start
</li></ul></code></pre>
<p>If you check the status of the cluster, you'll find that only the local node is listed, because it's not yet able to communicate with the other nodes.</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo nodetool status
</li></ul></code></pre><div class="code-label " title="Output">Output</div><pre class="code-pre "><code langs="">Datacenter: datacenter1
=======================
Status=Up/Down
|/ State=Normal/Leaving/Joining/Moving
--  Address      Load       Tokens       Owns    Host ID                               Rack
UN  192.168.1.4  147.48 KB  256          ?       f50799ee-8589-4eb8-a0c8-241cd254e424  rack1

Note: Non-system keyspaces don't have the same replication settings, effective ownership information is meaningless
</code></pre>
<p>To allow communication, we'll need to open the following network ports for each node:</p>

<ul>
<li><p><code>7000</code>, which is the TCP port for commands and data.</p></li>
<li><p><code>9042</code>, which is the TCP port for the native transport server. <code>cqlsh</code>, the Cassandra command line utility, will connect to the cluster through this port.</p></li>
</ul>

<p>To modify the firewall rules, open the rules file for IPv4.</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo nano /etc/iptables/rules.v4
</li></ul></code></pre>
<p>Copy and paste the following line within the INPUT chain, which will allow traffic on the aforementioned ports. If you're using the <code>rules.v4</code> file from the firewall tutorial, you can insert the following line just before the <code># Reject anything that's fallen through to this point</code> comment.</p>

<p>The IP address specified by<code>-s</code> should be the IP address of another node in the cluster. If you have two nodes with IP addresses <code>111.111.111.111</code> and <code>222.222.222.222</code>, the rule on the <code>111.111.111.111</code> machine should use the IP address <code>222.222.222.222</code>.</p>
<div class="code-label " title="New firewall rule">New firewall rule</div><pre class="code-pre "><code langs="">-A INPUT -p tcp -s <span class="highlight">your_other_server_ip</span> -m multiport --dports 7000,9042 -m state --state NEW,ESTABLISHED -j ACCEPT
</code></pre>
<p>After adding the rule, save and close the file, then restart IPTables.</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo service iptables-persistent restart
</li></ul></code></pre>
<h2 id="step-4-—-check-the-cluster-status">Step 4 — Check the Cluster Status</h2>

<p>We've now completed all the steps needed to make the nodes into a multi-node cluster. You can verify that they're all communicating by checking their status.</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo nodetool status
</li></ul></code></pre><div class="code-label " title="Output">Output</div><pre class="code-pre "><code langs="">Datacenter: datacenter1
=======================
Status=Up/Down
|/ State=Normal/Leaving/Joining/Moving
--  Address      Load       Tokens       Owns    Host ID                               Rack
UN  192.168.1.4  147.48 KB  256          ?       f50799ee-8589-4eb8-a0c8-241cd254e424  rack1
UN  192.168.1.6  139.04 KB  256          ?       54b16af1-ad0a-4288-b34e-cacab39caeec  rack1

Note: Non-system keyspaces don't have the same replication settings, effective ownership information is meaningless
</code></pre>
<p>If you can see all the nodes you configured, you've just successfully set up a multi-node Cassandra cluster.</p>

<p>You can also check if you can connect to the cluster using <code>cqlsh</code>, the Cassandra command line client. Note that you can specify the IP address of any node in the cluster for this command.</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">cqlsh <span class="highlight">your_server_ip</span> 9042
</li></ul></code></pre>
<p>You will see it connect:</p>
<div class="code-label " title="Output">Output</div><pre class="code-pre "><code langs="">Connected to My DO Cluster at 192.168.1.6:9042.
[cqlsh 5.0.1 | Cassandra 2.2.3 | CQL spec 3.3.1 | Native protocol v4]
Use HELP for help.
cqlsh>
</code></pre>
<p>Then you can exit the CQL terminal.</p>
<pre class="code-pre custom_prefix"><code langs=""><ul class="prefixed"><li class="line" prefix="cqlsh>">exit
</li></ul></code></pre>
<h2 id="conclusion">Conclusion</h2>

<p>Congratulations! You now have a multi-node Cassandra cluster running on Ubuntu 14.04. More information about Cassandra is available at the <a href="http://wiki.apache.org/cassandra/GettingStarted">project's website</a>. If you need to troubleshoot the cluster, the first place to look for clues are in the log files, which are located in the <code>/var/log/cassandra</code> directory.</p>

    