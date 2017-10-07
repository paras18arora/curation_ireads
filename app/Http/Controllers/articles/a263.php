<link rel='stylesheet' href='digital_ocean.css'><br><img src=https://community-cdn-digitalocean-com.global.ssl.fastly.net/assets/tutorials/images/large/Elasticsearch-twitter.png?1459198047/> <br> 
      <h3 id="introduction">Introduction</h3>

<p>Elasticsearch is a popular open source search server that is used for real-time distributed search and analysis of data. When used for anything other than development, Elasticsearch should be deployed across multiple servers as a cluster, for the best performance, stability, and scalability.</p>

<p>This tutorial will show you how to install and configure a production Elasticsearch cluster on Ubuntu 14.04, in a cloud server environment.</p>

<p>Although manually setting up an Elasticsearch cluster is useful for learning, use of a configuration management tool is highly recommended with any cluster setup. If you want to use Ansible to deploy an Elasticsearch cluster, follow this tutorial: <a href="https://indiareads/community/tutorials/how-to-use-ansible-to-set-up-a-production-elasticsearch-cluster">How To Use Ansible to Set Up a Production Elasticsearch Cluster</a>.</p>

<h2 id="prerequisites">Prerequisites</h2>

<p>You must have at least three Ubuntu 14.04 servers to complete this tutorial because an Elasticsearch cluster should have a minimum of 3 master-eligible nodes. If you want to have dedicated master and data nodes, you will need at least 3 servers for your master nodes plus additional servers for your data nodes.</p>

<p>If you would prefer to use CentOS instead, check out this tutorial: <a href="https://indiareads/community/tutorials/how-to-set-up-a-production-elasticsearch-cluster-on-centos-7">How To Set Up a Production Elasticsearch Cluster on CentOS 7</a></p>

<h3 id="assumptions">Assumptions</h3>

<p>This tutorial assumes that your servers are using a VPN like the one described here: <a href="https://indiareads/community/tutorials/how-to-use-ansible-and-tinc-vpn-to-secure-your-server-infrastructure">How To Use Ansible and Tinc VPN to Secure Your Server Infrastructure</a>. This will provide private network functionality regardless of the physical network that your servers are using.</p>

<p>If you are using a shared private network, such as IndiaReads Private Networking, you must use a VPN to protect Elasticsearch from unauthorized access. Each server must be on the same private network because Elasticsearch doesn't have security built into its HTTP interface. The private network must not be shared with any computers you don't trust.</p>

<p>We will refer to your servers' VPN IP addresses as <code>vpn_ip</code>. We will also assume that they all have a VPN interface that is named "tun0", as described in the tutorial linked above.</p>

<h2 id="install-java-8">Install Java 8</h2>

<p>Elasticsearch requires Java, so we will install that now. We will install a recent version of Oracle Java 8 because that is what Elasticsearch recommends. It should, however, work fine with OpenJDK, if you decide to go that route.</p>

<p>Complete this step on all of your Elasticsearch servers.</p>

<p>Add the Oracle Java PPA to apt:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo add-apt-repository -y ppa:webupd8team/java
</li></ul></code></pre>
<p>Update your apt package database:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo apt-get update
</li></ul></code></pre>
<p>Install the latest stable version of Oracle Java 8 with this command (and accept the license agreement that pops up):</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo apt-get -y install oracle-java8-installer
</li></ul></code></pre>
<p>Be sure to repeat this step on all of your Elasticsearch servers.</p>

<p>Now that Java 8 is installed, let's install ElasticSearch.</p>

<h2 id="install-elasticsearch">Install Elasticsearch</h2>

<p>Elasticsearch can be installed with a package manager by adding Elastic's package source list. Complete this step on all of your Elasticsearch servers.</p>

<p>Run the following command to import the Elasticsearch public GPG key into apt:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">wget -qO - https://packages.elastic.co/GPG-KEY-elasticsearch | sudo apt-key add -
</li></ul></code></pre>
<p>If your prompt is just hanging there, it is probably waiting for your user's password (to authorize the <code>sudo</code> command). If this is the case, enter your password.</p>

<p>Create the Elasticsearch source list:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">echo "deb http://packages.elastic.co/elasticsearch/2.x/debian stable main" | sudo tee -a /etc/apt/sources.list.d/elasticsearch-2.x.list
</li></ul></code></pre>
<p>Update your apt package database:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo apt-get update
</li></ul></code></pre>
<p>Install Elasticsearch with this command:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo apt-get -y install elasticsearch
</li></ul></code></pre>
<p>Be sure to repeat these steps on all of your Elasticsearch servers.</p>

<p>Elasticsearch is now installed but it needs to be configured before you can use it.</p>

<h2 id="configure-elasticsearch-cluster">Configure Elasticsearch Cluster</h2>

<p>Now it's time to edit the Elasticsearch configuration. Complete these steps on all of your Elasticsearch servers.</p>

<p>Open the Elasticsearch configuration file for editing:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo vi /etc/elasticsearch/elasticsearch.yml
</li></ul></code></pre>
<p>The subsequent sections will explain how the configuration must be modified.</p>

<h3 id="bind-to-vpn-ip-address-or-interface">Bind to VPN IP Address or Interface</h3>

<p>You will want to restrict outside access to your Elasticsearch instance, so outsiders can't access your data or shut down your Elasticsearch cluster through the HTTP API. In other words, you must configure Elasticsearch such that it only allows access to servers on your private network (VPN). To do this, we need to configure each node to bind to the VPN IP address, <code>vpn_ip</code>, or interface, "tun0".</p>

<p>Find the line that specifies <code>network.host</code>, uncomment it, and replace its value with the respective server's VPN IP address (e.g. <code>10.0.0.1</code> for node01) or interface name. Because our VPN interface is named "tun0" on all of our servers, we can configure all of our servers with the same line:</p>
<div class="code-label " title="elasticsearch.yml — network.host">elasticsearch.yml — network.host</div><pre class="code-pre "><code langs="">network.host: [_<span class="highlight">tun0</span>_, _local_]
</code></pre>
<p>Note the addition of "_local_", which configures Elasticsearch to also listen on all loopback devices. This will allow you to use the Elasticsearch HTTP API locally, from each server, by sending requests to <code>localhost</code>. If you do not include this, Elasticsearch will only respond to requests to the VPN IP address.</p>

<p><span class="warning"><strong>Warning:</strong> Because Elasticsearch doesn't have any built-in security, it is very important that you do not set this to any IP address that is accessible to any servers that you do not control or trust. Do not bind Elasticsearch to a public or <strong>shared private network</strong> IP address!<br /></span></p>

<h3 id="set-cluster-name">Set Cluster Name</h3>

<p>Next, set the name of your cluster, which will allow your Elasticsearch nodes to join and form the cluster. You will want to use a descriptive name that is unique (within your network).</p>

<p>Find the line that specifies <code>cluster.name</code>, uncomment it, and replace its value with the your desired cluster name. In this tutorial, we will name our cluster "production":</p>
<div class="code-label " title="elasticsearch.yml — cluster.name">elasticsearch.yml — cluster.name</div><pre class="code-pre "><code langs="">cluster.name: <span class="highlight">production</span>
</code></pre>
<h3 id="set-node-name">Set Node Name</h3>

<p>Next, we will set the name of each node. This should be a descriptive name that is unique within the cluster.</p>

<p>Find the line that specifies <code>node.name</code>, uncomment it, and replace its value with your desired node name. In this tutorial, we will set each node name to the hostname of server by using the <code>${HOSTNAME}</code> environment variable:</p>
<div class="code-label " title="elasticsearch.yml — node.name">elasticsearch.yml — node.name</div><pre class="code-pre "><code langs="">node.name: ${HOSTNAME}
</code></pre>
<p>If you prefer, you may name your nodes manually, but make sure that you specify unique names. You may also leave <code>node.name</code> commented out, if you don't mind having your nodes named randomly.</p>

<h3 id="set-discovery-hosts">Set Discovery Hosts</h3>

<p>Next, you will need to configure an initial list of nodes that will be contacted to discover and form a cluster. This is necessary in a unicast network.</p>

<p>Find the line that specifies <code>discovery.zen.ping.unicast.hosts</code> and uncomment it. Replace its value with an array of strings of the VPN IP addresses or hostnames (that resolve to the VPN IP addresses) of all of the other nodes.</p>

<p>For example, if you have three servers <code>node01</code>, <code>node02</code>, and <code>node03</code> with respective VPN IP addresses of <code>10.0.0.1</code>, <code>10.0.0.2</code>, and <code>10.0.0.3</code>, you could use this line:</p>
<div class="code-label " title="elasticsearch.yml — hosts by IP address">elasticsearch.yml — hosts by IP address</div><pre class="code-pre "><code langs="">discovery.zen.ping.unicast.hosts: ["<span class="highlight">10.0.0.1</span>", "<span class="highlight">10.0.0.2</span>", "<span class="highlight">10.0.0.3</span>"]
</code></pre>
<p>Alternatively, if all of your servers are configured with name-based resolution of their VPN IP addresses (via DNS or <code>/etc/hosts</code>), you could use this line:</p>
<div class="code-label " title="elasticsearch.yml — hosts by name">elasticsearch.yml — hosts by name</div><pre class="code-pre "><code langs="">discovery.zen.ping.unicast.hosts: ["<span class="highlight">node01</span>", "<span class="highlight">node02</span>", "<span class="highlight">node03</span>"]
</code></pre>
<p><span class="note"><strong>Note:</strong> The Ansible Playbook in the <a href="https://indiareads/community/tutorials/how-to-use-ansible-and-tinc-vpn-to-secure-your-server-infrastructure">prerequisite VPN tutorial</a> automatically creates <code>/etc/hosts</code> entries on each server that resolve each VPN server's <strong>inventory hostname</strong> (specified in the Ansible <code>hosts</code> file) to its VPN IP address.<br /></span></p>

<h3 id="save-and-exit">Save and Exit</h3>

<p>Your servers are now configured to form a basic Elasticsearch cluster. There are more settings that you will want to update, but we'll get to those after we verify that the cluster is working.</p>

<p>Save and exit <code>elasticsearch.yml</code>.</p>

<h3 id="start-elasticsearch">Start Elasticsearch</h3>

<p>Now start Elasticsearch:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo service elasticsearch restart
</li></ul></code></pre>
<p>Then run this command to start Elasticsearch on boot up:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo update-rc.d elasticsearch defaults 95 10
</li></ul></code></pre>
<p>Be sure to repeat these steps (<a href="https://indiareads/community/tutorials/how-to-set-up-a-production-elasticsearch-cluster-on-ubuntu-14-04#configure-elasticsearch-cluster">Configure Elasticsearch Cluster</a>) on all of your Elasticsearch servers.</p>

<h2 id="check-cluster-state">Check Cluster State</h2>

<p>If everything was configured correctly, your Elasticsearch cluster should be up and running. Before moving on, let's verify that it's working properly. You can do so by querying Elasticsearch from any of the Elasticsearch nodes.</p>

<p>From any of your Elasticsearch servers, run this command to print the state of the cluster:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">curl -XGET 'http://localhost:9200/_cluster/state?pretty'
</li></ul></code></pre>
<p>You should see output that indicates that a cluster named "production" is running. It should also indicate that all of the nodes you configured are members:</p>
<pre class="code-pre "><code langs=""><div class="secondary-code-label " title="Cluster State:">Cluster State:</div>{
  "cluster_name" : "production",
  "version" : 36,
  "state_uuid" : "MIkS5sk7TQCl31beb45kfQ",
  "master_node" : "k6k2UObVQ0S-IFoRLmDcvA",
  "blocks" : { },
  "nodes" : {
    "Jx_YC2sTQY6ayACU43_i3Q" : {
      "name" : "node02",
      "transport_address" : "10.0.0.2:9300",
      "attributes" : { }
    },
    "k6k2UObVQ0S-IFoRLmDcvA" : {
      "name" : "node01",
      "transport_address" : "10.0.0.1:9300",
      "attributes" : { }
    },
    "kQgZZUXATkSpduZxNwHfYQ" : {
      "name" : "node03",
      "transport_address" : "10.0.0.3:9300",
      "attributes" : { }
    }
  },
...
</code></pre>
<p>If you see output that is similar to this, your Elasticsearch cluster is running! If any of your nodes are missing, review the configuration for the node(s) in question before moving on.</p>

<p>Next, we'll go over some configuration settings that you should consider for your Elasticsearch cluster.</p>

<h2 id="enable-memory-locking">Enable Memory Locking</h2>

<p>Elastic recommends to avoid swapping the Elasticsearch process at all costs, due to its negative effects on performance and stability. One way avoid excessive swapping is to configure Elasticsearch to lock the memory that it needs.</p>

<p>Complete this step on all of your Elasticsearch servers.</p>

<p>Edit the Elasticsearch configuration:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo vi /etc/elasticsearch/elasticsearch.yml
</li></ul></code></pre>
<p>Find the line that specifies <code>bootstrap.mlockall</code> and uncomment it:</p>
<div class="code-label " title="elasticsearch.yml — bootstrap.mlockall">elasticsearch.yml — bootstrap.mlockall</div><pre class="code-pre "><code langs="">bootstrap.mlockall: true
</code></pre>
<p>Save and exit.</p>

<p>Next, open the <code>/etc/default/elasticsearch</code> file for editing:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo vi /etc/default/elasticsearch
</li></ul></code></pre>
<p>First, find <code>ES_HEAP_SIZE</code>, uncomment it, and set it to about 50% of your available memory. For example, if you have about 4 GB free, you should set this to 2 GB (<code>2g</code>):</p>
<div class="code-label " title="/etc/default/elasticsearch — ES_HEAP_SIZE">/etc/default/elasticsearch — ES_HEAP_SIZE</div><pre class="code-pre "><code langs="">ES_HEAP_SIZE=<span class="highlight">2g</span>
</code></pre>
<p>Next, find and uncomment <code>MAX_LOCKED_MEMORY=unlimited</code>. It should look like this when you're done:</p>
<div class="code-label " title="/etc/default/elasticsearch — MAX_LOCKED_MEMORY">/etc/default/elasticsearch — MAX_LOCKED_MEMORY</div><pre class="code-pre "><code langs="">MAX_LOCKED_MEMORY=unlimited
</code></pre>
<p>Save and exit.</p>

<p>Now restart Elasticsearch to put the changes into place:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo service elasticsearch restart
</li></ul></code></pre>
<p>Be sure to repeat this step on all of your Elasticsearch servers.</p>

<h3 id="verify-mlockall-status">Verify Mlockall Status</h3>

<p>To verify that <code>mlockall</code> is working on all of your Elasticsearch nodes, run this command from any node:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">curl http://localhost:9200/_nodes/process?pretty
</li></ul></code></pre>
<p>Each node should have a line that says <code>"mlockall" : true</code>, which indicates that memory locking is enabled and working:</p>
<pre class="code-pre "><code langs=""><div class="secondary-code-label " title="Nodes process output:">Nodes process output:</div>...
  "nodes" : {
    "kQgZZUXATkSpduZxNwHfYQ" : {
      "name" : "es03",
      "transport_address" : "10.0.0.3:9300",
      "host" : "10.0.0.3",
      "ip" : "10.0.0.3",
      "version" : "2.2.0",
      "build" : "8ff36d1",
      "http_address" : "10.0.0.3:9200",
      "process" : {
        "refresh_interval_in_millis" : 1000,
        "id" : 1650,
<span class="highlight">        "mlockall" : true</span>
      }
...
</code></pre>
<p>If <code>mlockall</code> is false for any of your nodes, review the node's settings and restart Elasticsearch. A common reason for Elasticsearch failing to start is that <code>ES_HEAP_SIZE</code> is set too high.</p>

<h2 id="configure-open-file-descriptor-limit-optional">Configure Open File Descriptor Limit (Optional)</h2>

<p>By default, your Elasticsearch node should have an "Open File Descriptor Limit" of 64k. This section will show you how to verify this and, if you want to, increase it.</p>

<h3 id="how-to-verify-maximum-open-files">How to Verify Maximum Open Files</h3>

<p>First, find the process ID (PID) of your Elasticsearch process. An easy way to do this is to use the <code>ps</code> command to list all of the processes that belong to the <code>elasticsearch</code> user:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">ps -u elasticsearch
</li></ul></code></pre>
<p>You should see output that looks like this. The number in the first column is the PID of your Elasticsearch (java) process:</p>
<pre class="code-pre "><code langs=""><div class="secondary-code-label " title="Output:">Output:</div>  PID TTY          TIME CMD
<span class="highlight">11708</span> ?        00:00:10 java
</code></pre>
<p>Then run this command to show the open file limits for the Elasticsearch process (replace the highlighted number with your own PID from the previous step):</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">cat /proc/<span class="highlight">11708</span>/limits | grep 'Max open files'
</li></ul></code></pre><pre class="code-pre "><code langs=""><div class="secondary-code-label " title="Output">Output</div>Max open files            65535                65535                files
</code></pre>
<p>The numbers in the second and third columns indicate the soft and hard limits, respectively, as 64k (65535). This is OK for many setups, but you may want to increase this setting.</p>

<h3 id="how-to-increase-max-file-descriptor-limits">How to Increase Max File Descriptor Limits</h3>

<p>To increase the maximum number of open file descriptors in Elasticsearch, you just need to change a single setting.</p>

<p>Open the <code>/etc/default/elasticsearch</code> file for editing:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo vi /etc/default/elasticsearch
</li></ul></code></pre>
<p>Find <code>MAX_OPEN_FILES</code>, uncomment it, and set it to the limit you desire. For example, if you want a limit of 128k descriptors, change it to <code>131070</code>:</p>
<div class="code-label " title="/etc/default/elasticsearch — MAX_OPEN_FILES">/etc/default/elasticsearch — MAX_OPEN_FILES</div><pre class="code-pre "><code langs="">MAX_OPEN_FILES=<span class="highlight">131070</span>
</code></pre>
<p>Save and exit.</p>

<p>Now restart Elasticsearch to put the changes into place:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo service elasticsearch restart
</li></ul></code></pre>
<p>Then follow the previous subsection to verify that the limits have been increased.</p>

<p>Be sure to repeat this step on any of your Elasticsearch servers that require higher file descriptor limits.</p>

<h2 id="configure-dedicated-master-and-data-nodes-optional">Configure Dedicated Master and Data Nodes (Optional)</h2>

<p>There are two common types of Elasticsearch nodes: <strong>master</strong> and <strong>data</strong>. Master nodes perform cluster-wide actions, such as managing indices and determining which data nodes should store particular data shards. Data nodes hold shards of your indexed documents, and handle CRUD, search, and aggregation operations. As a general rule, data nodes consume a significant amount of CPU, memory, and I/O.</p>

<p>By default, every Elasticsearch node is configured to be a "master-eligible" data node, which means they store data (and perform resource-intensive operations) and have the potential to be elected as a master node. For a small cluster, this is usually fine; a large Elasticsearch cluster, however, should be configured with <strong>dedicated master</strong> nodes so that the master node's stability can't be compromised by intensive data node work.</p>

<h3 id="how-to-configure-dedicated-master-nodes">How to Configure Dedicated Master Nodes</h3>

<p>Before configuring dedicated master nodes, ensure that your cluster will have at least 3 master-eligible nodes. This is important to avoid a split-brain situation, which can cause inconsistencies in your data in the event of a network failure.</p>

<p>To configure a dedicated master node, edit the node's Elasticsearch configuration:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo vi /etc/elasticsearch/elasticsearch.yml
</li></ul></code></pre>
<p>Add the two following lines:</p>
<div class="code-label " title="elasticsearch.yml — dedicated master">elasticsearch.yml — dedicated master</div><pre class="code-pre "><code langs="">node.master: true 
node.data: false
</code></pre>
<p>The first line, <code>node.master: true</code>, specifies that the node is master-eligible and is actually the default setting. The second line, <code>node.data: false</code>, restricts the node from becoming a data node.</p>

<p>Save and exit.</p>

<p>Now restart the Elasticsearch node to put the change into effect:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo service elasticsearch restart
</li></ul></code></pre>
<p>Be sure to repeat this step on your other dedicated master nodes.</p>

<p>You can query the cluster to see which nodes are configured as dedicated master nodes with this command: <code>curl -XGET 'http://localhost:9200/_cluster/state?pretty'</code>. Any node with <code>data: false</code> and <code>master: true</code> are dedicated master nodes.</p>

<h3 id="how-to-configure-dedicated-data-nodes">How to Configure Dedicated Data Nodes</h3>

<p>To configure a dedicated data node—a data node that is not master-eligible—edit the node's Elasticsearch configuration:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo vi /etc/elasticsearch/elasticsearch.yml
</li></ul></code></pre>
<p>Add the two following lines:</p>
<div class="code-label " title="elasticsearch.yml — dedicated data">elasticsearch.yml — dedicated data</div><pre class="code-pre "><code langs="">node.master: false 
node.data: true
</code></pre>
<p>The first line, <code>node.master: false</code>, specifies that the node is not master-eligible. The second line, <code>node.data: true</code>, is the default setting which allows the node to be a data node.</p>

<p>Save and exit.</p>

<p>Now restart the Elasticsearch node to put the change into effect:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo service elasticsearch restart
</li></ul></code></pre>
<p>Be sure to repeat this step on your other dedicated data nodes.</p>

<p>You can query the cluster to see which nodes are configured as dedicated data nodes with this command: <code>curl -XGET 'http://localhost:9200/_cluster/state?pretty'</code>. Any node that lists <code>master: false</code> and <strong>does not</strong> list <code>data: false</code> are dedicated data nodes.</p>

<h3 id="configure-minimum-master-nodes">Configure Minimum Master Nodes</h3>

<p>When running an Elasticsearch cluster, it is important to set the minimum number of master-eligible nodes that need to be running for the cluster to function normally, which is sometimes referred to as <strong>quorum</strong>. This is to ensure data consistency in the event that one or more nodes lose connectivity to the rest of the cluster, preventing what is known as a "split-brain" situation.</p>

<p>To calculate the number of minimum master nodes your cluster should have, calculate <code>n / 2 + 1</code>, where <em>n</em> is the total number of "master-eligible" nodes in your healthy cluster, then round the result down to the nearest integer. For example, for a 3-node cluster, the quorum is 2.</p>

<p><span class="note"><strong>Note:</strong> Be sure to include all master-eligible nodes in your quorum calculation, including any data nodes that are master-eligible (default setting).<br /></span></p>

<p>The minimum master nodes setting can be set dynamically, through the Elasticsearch HTTP API. To do so, run this command on any node (replace the highlighted number with your quorum):</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">curl -XPUT localhost:9200/_cluster/settings?pretty -d '{
</li><li class="line" prefix="$">    "persistent" : {
</li><li class="line" prefix="$">        "discovery.zen.minimum_master_nodes" : <span class="highlight">2</span>
</li><li class="line" prefix="$">    }
</li><li class="line" prefix="$">}'
</li></ul></code></pre><pre class="code-pre "><code langs=""><div class="secondary-code-label " title="Output:">Output:</div>{
  "acknowledged" : true,
  "persistent" : {
    "discovery" : {
      "zen" : {
        "minimum_master_nodes" : "2"
      }
    }
  },
  "transient" : { }
}
</code></pre>
<p><span class="note"><strong>Note:</strong> This command is a "persistent" setting, meaning the minimum master nodes setting will survive full cluster restarts and override the Elasticsearch configuration file. Also, this setting can be specified as <code>discovery.zen.minimum_master_nodes: <span class="highlight">2</span></code> in <code>/etc/elasticsearch.yml</code> if you have not already set it dynamically.<br /></span></p>

<p>If you want to check this setting later, you can run this command:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">curl -XGET localhost:9200/_cluster/settings?pretty
</li></ul></code></pre>
<h2 id="how-to-access-elasticsearch">How To Access Elasticsearch</h2>

<p>You may access the Elasticsearch HTTP API by sending requests to the VPN IP address any of the nodes or, as demonstrated in the tutorial, by sending requests to <code>localhost</code> from one of the nodes.</p>

<p>Your Elasticsearch cluster is accessible to client servers via the VPN IP address of any of the nodes, which means that the client servers must also be part of the VPN.</p>

<p>If you have other software that needs to connect to your cluster, such as Kibana or Logstash, you can typically configure the connection by providing your application with the VPN IP addresses of one or more of the Elasticsearch nodes.</p>

<h2 id="conclusion">Conclusion</h2>

<p>Your Elasticsearch cluster should be running in a healthy state, and configured with some basic optimizations!</p>

<p>Elasticsearch has many other configuration options that weren't covered here, such as index, shard, and replication settings. It is recommended that you revisit your configuration later, along with the official documentation, to ensure that your cluster is configured to meet your needs.</p>

    