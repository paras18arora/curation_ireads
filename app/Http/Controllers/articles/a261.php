<link rel='stylesheet' href='digital_ocean.css'><br><img src=https://community-cdn-digitalocean-com.global.ssl.fastly.net/assets/tutorials/images/large/Elasticsearch-twitter.png?1461950862/> <br> 
      <h3 id="introduction">Introduction</h3>

<p>In this tutorial, we'll show you how to use Ansible, a configuration management tool, to install a production Elasticsearch cluster on Ubuntu 14.04 or CentOS 7 in a cloud server environment. We will build upon the <a href="https://indiareads/community/tutorials/how-to-use-ansible-and-tinc-vpn-to-secure-your-server-infrastructure">How To Use Ansible and Tinc VPN to Secure Your Server Infrastructure</a> tutorial to ensure that your Elasticsearch nodes will be secure from computers outside of your own network.</p>

<p>Elasticsearch is a popular open source search server that is used for real-time distributed search and analysis of data. When used for anything other than development, Elasticsearch should be deployed across multiple servers as a cluster, for the best performance, stability, and scalability.</p>

<h2 id="prerequisites">Prerequisites</h2>

<p>You must have at least three Ubuntu 14.04 or CentOS 7 servers, with private networking, to complete this tutorial because an Elasticsearch cluster should have a minimum of 3 master-eligible nodes. If you want to have dedicated master and data nodes, you will need at least 3 servers for master nodes plus additional servers for any data nodes. Also note that, if you plan on using the default Elasticsearch heap size of 2 GB, your servers should be allocated at least 4 GB of memory.</p>

<p>After obtaining your servers, configure them to use a mesh VPN with this tutorial: <a href="https://indiareads/community/tutorials/how-to-use-ansible-and-tinc-vpn-to-secure-your-server-infrastructure">How To Use Ansible and Tinc VPN to Secure Your Server Infrastructure</a>. Make sure that each server has a unique Ansible inventory hostname.</p>

<p>If you are using a shared private network, such as IndiaReads Private Networking, you must use a VPN to protect Elasticsearch from unauthorized access. Each server must be on the same private network because Elasticsearch doesn't have security built into its HTTP interface. The private network must not be shared with any computers you don't trust.</p>

<h3 id="assumptions">Assumptions</h3>

<p>We will assume that all of the servers that you want to use as Elasticsearch nodes have a VPN interface that is named "tun0", as described in the tutorial linked above. If they don't, and you would rather have your ES nodes listen on a different interface, you will have to make the appropriate changes in <code>site.yml</code> file of the Playbook.</p>

<p>We will also assume that your Playbook is located in a directory called <code>ansible-tinc</code> in the home directory of your local computer.</p>

<h2 id="download-the-ansible-elasticsearch-playbook">Download the ansible-elasticsearch Playbook</h2>

<p>Elastic provides an Ansible role that can be used to easily set up an Elasticsearch cluster. To use it, we simply need to add it to our <code>ansible-tinc</code> playbook and define a few host groups and assign the appropriate roles to the groups. Again, if you haven't already followed the prerequisite VPN tutorial, it can be found <a href="https://indiareads/community/tutorials/how-to-use-ansible-and-tinc-vpn-to-secure-your-server-infrastructure">here</a>.</p>

<p>First, change to the directory that your Tinc Ansible Playbook is in:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">cd ~/ansible-tinc
</li></ul></code></pre>
<p>Then clone the <code>ansible-elasticsearch</code> role, which is available on Elastic's GitHub account, to the Playbook's <code>roles</code> directory:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">cd roles
</li><li class="line" prefix="$">git clone https://github.com/elastic/ansible-elasticsearch
</li></ul></code></pre>
<p>Rename the role to "elasticsearch":</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">mv ansible-elasticsearch elasticsearch
</li></ul></code></pre>
<h2 id="update-site-yml">Update site.yml</h2>

<p>Let's edit the master Playbook file, <code>site.yml</code>, to map three different Elasticsearch roles to three different Ansible host groups. This will allow us to create dedicated master, dedicated data, and master-eligible/data Elasticsearch nodes by simply adding hosts to the appropriate groups. </p>

<p>Change back to the Ansible Playbook's directory:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">cd ~/ansible-playbook
</li></ul></code></pre>
<p>In your favorite editor, edit a new file called <code>elasticsearch.yml</code>. We'll use <code>vi</code>:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">vi site.yml
</li></ul></code></pre>
<h3 id="map-elasticsearch-dedicated-master-role-to-group">Map Elasticsearch Dedicated Master Role to Group</h3>

<p>At the bottom of the file, map the dedicated master <code>elasticsearch</code> role to the <code>elasticsearch_master_nodes</code> group by adding these lines:</p>
<div class="code-label " title="site.yml — Dedicated master nodes">site.yml — Dedicated master nodes</div><pre class="code-pre "><code langs="">- hosts: elasticsearch_master_nodes
  roles:
    - { role: elasticsearch, es_instance_name: "node1", es_config: { discovery.zen.ping.unicast.hosts: "<span class="highlight">node01</span>, <span class="highlight">node02</span>, <span class="highlight">node03</span>", network.host: "_<span class="highlight">tun0</span>_, _local_", cluster.name: "production", discovery.zen.ping.multicast.enabled: false,  http.port: 9200, transport.tcp.port: 9300, node.data: false, node.master: true, bootstrap.mlockall: true } }
  vars:
    es_major_version: "2.x"
    es_version: "2.2.1"
    es_heap_size: "<span class="highlight">2g</span>"
    es_cluster_name: "production"
</code></pre>
<p>This role will create dedicated master nodes because it configures the nodes with these values: <code>node.master: true</code> and <code>node.data: false</code>.</p>

<p>Be sure to update the highlighted hostnames in the <code>discovery.zen.ping.unicast.hosts</code> variable to match the Ansible inventory hostnames (or VPN IP addresses) of a few of your Elasticsearch servers. This will allow these nodes to discover the Elasticsearch cluster. In the example, we are using <code>node01</code>, <code>node02</code>, and <code>node03</code> because those were the hostnames used in the prerequisite VPN tutorial. Also, if your VPN interface is named something other than "tun0", update the <code>network.host</code> variable accordingly.</p>

<p>If you want to use a different version of Elasticsearch, update <code>es_version</code>. Note that this configuration won't work for versions prior to 2.2 because older versions do not accept comma-delimited lists for the <code>network.host</code> variable.</p>

<p>Update <code>es_heap_size</code> to a value that is roughly half of the free memory on your dedicated master servers. For example, if your server has about 4 GB free, set the heap size to "2g".</p>

<p>Now any hosts that belong to the <code>elasticsearch_master_nodes</code> Ansible host group will be configured as dedicated master Elasticsearch nodes.</p>

<h3 id="map-elasticsearch-master-data-role-to-group">Map Elasticsearch Master/Data Role to Group</h3>

<p>At the bottom of the file, map the master-eligible and data <code>elasticsearch</code> role to the <code>elasticsearch_master_data_nodes</code> group by adding these lines:</p>
<div class="code-label " title="site.yml — Master-eligible/data nodes">site.yml — Master-eligible/data nodes</div><pre class="code-pre "><code langs="">- hosts: elasticsearch_master_data_nodes
  roles:
    - { role: elasticsearch, es_instance_name: "node1", es_config: { discovery.zen.ping.unicast.hosts: "<span class="highlight">node01</span>, <span class="highlight">node02</span>, <span class="highlight">node03</span>", network.host: "_<span class="highlight">tun0</span>_, _local_", cluster.name: "production", discovery.zen.ping.multicast.enabled: false, http.port: 9200, transport.tcp.port: 9300, node.data: true, node.master: true, bootstrap.mlockall: true } }
  vars:
    es_major_version: "2.x"
    es_version: "2.2.1"
    es_heap_size: "<span class="highlight">2g</span>"
    es_cluster_name: "production"
</code></pre>
<p>This role will create data nodes that are master-eligible because it configures the nodes with these values: <code>node.master: true</code> and <code>node.data: true</code>.</p>

<p>Be sure to update the highlighted hostnames in the <code>discovery.zen.ping.unicast.hosts</code> variable to match the Ansible inventory hostnames (or VPN IP addresses) of a few of your Elasticsearch servers. Also, if your VPN interface is named something other than "tun0", update the <code>network.host</code> variable accordingly.</p>

<p>Set <code>es_version</code> to the same value that you used for the dedicated master role.</p>

<p>Update <code>es_heap_size</code> to a value that is roughly half of the free memory on your master-eligible/data servers.</p>

<p>Now any hosts that belong to the <code>elasticsearch_master_data_nodes</code> Ansible host group will be configured as data nodes that are master-eligible.</p>

<h3 id="map-elasticsearch-dedicated-data-role-to-group">Map Elasticsearch Dedicated Data Role to Group</h3>

<p>At the bottom of the file, map the dedicated data <code>elasticsearch</code> role to the <code>elasticsearch_data_nodes</code> group by adding these lines:</p>
<div class="code-label " title="site.yml — Dedicated data nodes">site.yml — Dedicated data nodes</div><pre class="code-pre "><code langs="">- hosts: elasticsearch_data_nodes
  roles:
    - { role: elasticsearch, es_instance_name: "node1", es_config: { discovery.zen.ping.unicast.hosts: "<span class="highlight">node01</span>, <span class="highlight">node02</span>, <span class="highlight">node03</span>", network.host: "_<span class="highlight">tun0</span>_, _local_", cluster.name: "production", discovery.zen.ping.multicast.enabled: false, http.port: 9200, transport.tcp.port: 9300, node.data: true, node.master: false, bootstrap.mlockall: true } }
  vars:
    es_major_version: "2.x"
    es_version: "2.2.1"
    es_heap_size: "<span class="highlight">2g</span>"
    es_cluster_name: "production"
</code></pre>
<p>This role will create dedicated data nodes because it configures the nodes with these values: <code>node.master: false</code> and <code>node.data: true</code>.</p>

<p>Be sure to update the highlighted hostnames in the <code>discovery.zen.ping.unicast.hosts</code> variable to match the Ansible inventory hostnames (or VPN IP addresses) of a few of your Elasticsearch servers. Also, if your VPN interface is named something other than "tun0", update the <code>network.host</code> variable accordingly.</p>

<p>Set <code>es_version</code> to the same value that you used in the previous roles.</p>

<p>Update <code>es_heap_size</code> to a value that is roughly half of the free memory on your dedicated data servers.</p>

<p>Now any hosts that belong to the <code>elasticsearch_data_nodes</code> Ansible host group will be configured as dedicated data Elasticsearch nodes.</p>

<h3 id="save-and-exit">Save and Exit</h3>

<p>Now that you've defined the three roles, and mapped them to host groups, you can save and exit <code>site.yml</code>.</p>

<p>Feel free to add more Elasticsearch roles and host group mappings later.</p>

<h2 id="update-host-inventory-file">Update Host Inventory File</h2>

<p>Now that the new Elasticsearch roles have been mapped to host groups, you can create different types of Elasticsearch nodes by simply adding the hosts to the appropriate host groups.</p>

<p>Edit the Ansible <code>hosts</code> inventory file:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">vi hosts
</li></ul></code></pre>
<p>If you followed the prerequisite tutorial, your file should look something like this (with your server hostnames and IP addresses):</p>
<div class="code-label " title="Ansible hosts inventory — Original file">Ansible hosts inventory — Original file</div><pre class="code-pre "><code langs="">[vpn]
node01 vpn_ip=10.0.0.1 ansible_host=45.55.41.106
node02 vpn_ip=10.0.0.2 ansible_host=159.203.104.93
node03 vpn_ip=10.0.0.3 ansible_host=159.203.104.127
node04 vpn_ip=10.0.0.4 ansible_host=159.203.104.129

[removevpn]
</code></pre>
<p>Now add three groups that correspond to the mappings that we defined in <code>site.yml</code>.</p>
<div class="code-label " title="Ansible hosts inventory — Elasticsearch groups">Ansible hosts inventory — Elasticsearch groups</div><pre class="code-pre "><code langs="">[elasticsearch_master_nodes]

[elasticsearch_master_data_nodes]

[elasticsearch_data_nodes]
</code></pre>
<p>Now distribute your Elasticsearch hosts among the new host groups, depending on the types of Elasticsearch nodes that you want your cluster to consist of. For example, if you want three dedicated master nodes and a single dedicated data node, your inventory file would look something like this:</p>
<div class="code-label " title="Ansible hosts inventory — Complete example">Ansible hosts inventory — Complete example</div><pre class="code-pre "><code langs="">[vpn]
node01 vpn_ip=10.0.0.1 ansible_host=45.55.41.106
node02 vpn_ip=10.0.0.2 ansible_host=159.203.104.93
node03 vpn_ip=10.0.0.3 ansible_host=159.203.104.127
node04 vpn_ip=10.0.0.4 ansible_host=159.203.104.129

[removevpn]

[elasticsearch_master_nodes]
node01
node02
node03

[elasticsearch_master_data_nodes]

[elasticsearch_data_nodes]
node04
</code></pre>
<p><span class="note"><strong>Note:</strong> Each Elasticsearch node must also be defined in the <code>[vpn]</code> host group so that all nodes can communicate with each other over the VPN. Also, any server that needs to connect to the Elasticsearch cluster must also be defined in the <code>[vpn]</code> host group.<br /></span></p>

<p>Once your inventory file reflects your desired Elasticsearch (and VPN) setup, save and exit.</p>

<h2 id="create-elasticsearch-cluster">Create Elasticsearch Cluster</h2>

<p>Now that <code>site.yml</code> and <code>hosts</code> are set up, you are ready to create your Elasticsearch cluster by running the Playbook.</p>

<p>Run the Playbook with this command:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">ansible-playbook site.yml
</li></ul></code></pre>
<p>After the Playbook completes its run, your Elasticsearch cluster should be up and running. The next step is to verify that everything is working properly.</p>

<h2 id="verify-elasticsearch-cluster-status">Verify Elasticsearch Cluster Status</h2>

<p>From any of your Elasticsearch servers, run this command to print the state of the cluster:</p>
<pre class="code-pre custom_prefix"><code langs=""><ul class="prefixed"><li class="line" prefix="node01$">curl -XGET 'http://localhost:9200/_cluster/state?pretty'
</li></ul></code></pre>
<p>You should see output that indicates that a cluster named "production" is running. It should also indicate that all of the nodes you configured are members:</p>
<pre class="code-pre "><code langs=""><div class="secondary-code-label " title="Cluster State:">Cluster State:</div>{
  "cluster_name" : "production",
  "version" : 8,
  "state_uuid" : "SgTyn0vNTTu2rdKPrc6tkQ",
  "master_node" : "OzqMzte9RYWSXS6OkGhveA",
  "blocks" : { },
  "nodes" : {
    "OzqMzte9RYWSXS6OkGhveA" : {
      "name" : "node02-node1",
      "transport_address" : "10.0.0.2:9300",
      "attributes" : {
        "data" : "false",
        "master" : "true"
      }
    },
    "7bohaaYVTeeOHvSgBFp-2g" : {
      "name" : "node04-node1",
      "transport_address" : "10.0.0.4:9300",
      "attributes" : {
        "master" : "false"
      }
    },
    "cBat9IgPQwKU_DPF8L3Y1g" : {
      "name" : "node03-node1",
      "transport_address" : "10.0.0.3:9300",
      "attributes" : {
        "master" : "false"
      }
    },
...
</code></pre>
<p>If you see output that is similar to this, your Elasticsearch cluster is running! If some of your nodes are missing, review your Ansible <code>hosts</code> inventory to make sure that your host groups are defined properly.</p>

<h3 id="troubleshooting">Troubleshooting</h3>

<p>If you get <code>curl: (7) Failed to connect to localhost port 9200: Connection refused</code>, Elasticsearch isn't running on that server. This is usually caused by Elasticsearch configuration errors in the <code>site.yml</code> file, such as incorrect <code>network.host</code> or <code>discovery.zen.ping.unicast.hosts</code> entries. In addition to reviewing that file, also check the Elasticsearch logs on your servers (<code>/var/log/elasticsearch/<span class="highlight">node01</span>-node1/production.log</code>) for clues.</p>

<p>If you want to see an example of the Playbook produced by following this tutorial, check out <a href="https://github.com/thisismitch/ansible-tinc-elasticsearch-example">this GitHub repository</a>. This should help you see what a working <code>site.yml</code> and <code>hosts</code> file looks like.</p>

<h2 id="conclusion">Conclusion</h2>

<p>Your Elasticsearch cluster should be running in a healthy state, and configured with some basic optimizations!</p>

<p>Elasticsearch has many other configuration options that weren't covered here, such as index, shard, and replication settings. It is recommended that you revisit your configuration later, along with the official documentation, to ensure that your cluster is configured to meet your needs.</p>

    