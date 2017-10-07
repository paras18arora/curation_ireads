<link rel='stylesheet' href='digital_ocean.css'><br> <br> 
      <h3 id="introduction">Introduction</h3>

<p>One of the technologies that makes CoreOS possible is <code>etcd</code>, a globally distributed key-value store.  This service is used by the individual CoreOS machines to form a cluster and as a platform to store globally-accessible data.</p>

<p>In this guide, we will explore the <code>etcd</code> daemon as well as the <code>etcdctl</code> utility and the HTTP/JSON API that can be used to control it.</p>

<h2 id="prerequisites">Prerequisites</h2>

<p>To follow along with this guide, we assume that you have a cluster of CoreOS machines as our guide on <a href="https://indiareads/community/tutorials/how-to-set-up-a-coreos-cluster-on-digitalocean">getting a CoreOS cluster set up on IndiaReads</a> outlines.  This will leave you with three servers in a single cluster:</p>

<ul>
<li>coreos-1</li>
<li>coreos-2</li>
<li>coreos-3</li>
</ul>

<p>Once you have these machines up and running, you can continue with this guide.</p>

<h2 id="etcd-cluster-discovery-model">Etcd Cluster Discovery Model</h2>

<p>One of the most fundamental tasks that <code>etcd</code> is responsible for is organizing individual machines into a cluster.  This is done when CoreOS is booted by checking in at the discovery address supplied in the <code>cloud-config</code> file which is passed in upon creation.</p>

<p>The discovery service run by CoreOS is accessible at <code>https://discovery.etcd.io</code>.  You can get a new token by visiting the <code>/new</code> page.  There, you will get a token which your machines can use to discover their companion nodes.  It will look like something like this:</p>
<pre class="code-pre "><code class="code-highlight language-bash">https://discovery.etcd.io/<span class="highlight">dcadc5d4d42328488ecdcd7afae5f57c</span>
</code></pre>
<p>You <em>must</em> supply a fresh token for every new cluster.  This includes when you have to rebuild the cluster using nodes that may have the same IP address.  The <code>etcd</code> instances will be confused by this and will not function correctly to build the cluster if you reuse the discovery address.</p>

<p>Visiting the discovery address in your web browser, you will get back a JSON object that describes the known machines.  This won't have any nodes when you first start out:</p>
<pre class="code-pre "><code class="code-highlight language-json">{"action":"get","node":{"key":"/_etcd/registry/dcadc5d4d42328488ecdcd7afae5f57c","dir":true,"modifiedIndex":102511104,"createdIndex":102511104}}
</code></pre>
<p>After bootstrapping your cluster, you will be able to see more information here:</p>
<pre class="code-pre "><code class="code-highlight language-json">{"action":"get","node":{"key":"/_etcd/registry/1edee33e6b03e75d9428eacf0ff94fda","dir":true,"nodes":[{"key":"/_etcd/registry/1edee33e6b03e75d9428eacf0ff94fda/2ddbdb7c872b4bc59dd1969ac166501e","value":"http://10.132.252.38:7001","expiration":"2014-09-19T13:41:26.912303668Z","ttl":598881,"modifiedIndex":102453704,"createdIndex":102453704},{"key":"/_etcd/registry/1edee33e6b03e75d9428eacf0ff94fda/921a7241c31a499a97d43f785108b17c","value":"http://10.132.248.118:7001","expiration":"2014-09-19T13:41:29.602508981Z","ttl":598884,"modifiedIndex":102453736,"createdIndex":102453736},{"key":"/_etcd/registry/1edee33e6b03e75d9428eacf0ff94fda/27987f5eaac243f88ca6823b47012c5b","value":"http://10.132.248.121:7001","expiration":"2014-09-19T13:41:41.817958205Z","ttl":598896,"modifiedIndex":102453860,"createdIndex":102453860}],"modifiedIndex":101632353,"createdIndex":101632353}}
</code></pre>
<p>If you need to find the discovery URL of a cluster, you can do so from any one of the machines that is a member.  This information can be retrieved from within the <code>/run</code> hierarchy:</p>
<pre class="code-pre "><code class="code-highlight language-bash">cat /run/systemd/system/etcd.service.d/20-cloudinit.conf
</code></pre><pre class="code-pre "><code class="code-highlight language-ini">[Service]
Environment="ETCD_ADDR=10.132.248.118:4001"
Environment="ETCD_DISCOVERY=https://discovery.etcd.io/dcadc5d4d42328488ecdcd7afae5f57c"
Environment="ETCD_NAME=921a7241c31a499a97d43f785108b17c"
Environment="ETCD_PEER_ADDR=10.132.248.118:7001"
</code></pre>
<p>The URL is stored within the <code>ETCD_DISCOVERY</code> entry.</p>

<p>When the machines running <code>etcd</code> boot up, they will check the information at this URL.  It will submit its own information and query about other members.  The first node in the cluster will obviously not find information about other nodes, so it will designate itself as the cluster leader.</p>

<p>The subsequent machines will also contact the discovery URL with their information.  They will receive information back about the machines that have already checked in.  They will then choose one of these machines and connect directly, where they will get the full list of healthy cluster members.  The replication and distribution of data is accomplished through the <a href="http://raftconsensus.github.io/">Raft consensus algorithm</a>.</p>

<p>The data about each of the machines is stored within a hidden directory structure within <code>etcd</code>.  You can see the information about the machines that <code>etcd</code> knows about by typing:</p>
<pre class="code-pre "><code class="code-highlight language-bash">etcdctl ls /_etcd/machines --recursive
</code></pre><pre class="code-pre "><code langs="">/_etcd/machines/2ddbdb7c872b4bc59dd1969ac166501e
/_etcd/machines/921a7241c31a499a97d43f785108b17c
/_etcd/machines/27987f5eaac243f88ca6823b47012c5b
</code></pre>
<p>The details that <code>etcd</code> pass to new cluster members are contained within these keys.  You can see the individual values by requesting those with <code>etcdctl</code>:</p>
<pre class="code-pre "><code class="code-highlight language-bash">etcdctl get /_etcd/machines/2ddbdb7c872b4bc59dd1969ac166501e
</code></pre><pre class="code-pre "><code langs="">etcd=http%3A%2F%2F10.132.252.38%3A4001&raft=http%3A%2F%2F10.132.252.38%3A7001
</code></pre>
<p>We will go over the <code>etcdctl</code> commands in more depth later on.</p>

<h2 id="etcdctl-usage">Etcdctl Usage</h2>

<p>There are two basic ways of interacting with <code>etcd</code>.  Through the HTTP/JSON API and through a client, like the included <code>etcdctl</code> utility.  We will go over <code>etcdctl</code> first.</p>

<h3 id="viewing-keys-and-directories">Viewing Keys and Directories</h3>

<p>To get started, let's look a what <code>etcdctl</code> is currently storing.  We can see the top-level keys by typing:</p>
<pre class="code-pre "><code class="code-highlight language-bash">etcdctl ls /
</code></pre><pre class="code-pre "><code langs="">/coreos.com
</code></pre>
<p>As you can see, we have one result.  At this point, it is unclear whether this is a directory or a key.  We can attempt to <code>get</code> the node to see either the key's value or to see that it is a directory:</p>
<pre class="code-pre "><code class="code-highlight language-bash">etcdctl get /coreos.com
</code></pre><pre class="code-pre "><code langs="">/coreos.com: is a directory
</code></pre>
<p>In order to avoid this manual recursive process, we can tell <code>etcdctl</code> to list its entire hierarchy of visible information by typing:</p>
<pre class="code-pre "><code class="code-highlight language-bash">etcdctl ls / --recursive
</code></pre><pre class="code-pre "><code langs="">/coreos.com
/coreos.com/updateengine
/coreos.com/updateengine/rebootlock
/coreos.com/updateengine/rebootlock/semaphore
</code></pre>
<p>As you can see, there were quite a few directories under the initial <code>/coreos.com</code> node.  We can see what it looks like to get actual data out of a node by asking for the information at the final endpoint:</p>
<pre class="code-pre "><code class="code-highlight language-bash">etcdctl get /coreos.com/updateengine/rebootlock/semaphore
</code></pre><pre class="code-pre "><code langs="">{"semaphore":1,"max":1,"holders":null}
</code></pre>
<p>This does not contain information that is very useful for us.  We can get some additional metadata about this entry by passing in the <code>-o extended</code> option.  This is a global option, so it must come before the <code>get</code> command:</p>
<pre class="code-pre "><code class="code-highlight language-bash">etcdctl -o extended get /coreos.com/updateengine/rebootlock/semaphore
</code></pre><pre class="code-pre "><code langs="">Key: /coreos.com/updateengine/rebootlock/semaphore
Created-Index: 6
Modified-Index: 6
TTL: 0
Etcd-Index: 170387
Raft-Index: 444099
Raft-Term: 8

{"semaphore":1,"max":1,"holders":null}
</code></pre>
<h3 id="setting-keys-and-creating-nodes">Setting Keys and Creating Nodes</h3>

<p>To create a new directory, you can use the <code>mkdir</code> command like so:</p>
<pre class="code-pre "><code class="code-highlight language-bash">etcdctl mkdir /example
</code></pre>
<p>To make a key, you can  use the <code>mk</code> command:</p>
<pre class="code-pre "><code class="code-highlight language-bash">etcdctl mk /example/key data
</code></pre><pre class="code-pre "><code langs="">data
</code></pre>
<p>This will only work if the key does not already exist.  If we ask for the value of the key we created, we can retrieve the data we set:</p>
<pre class="code-pre "><code class="code-highlight language-bash">etcdctl get /example/key
</code></pre><pre class="code-pre "><code langs="">data
</code></pre>
<p>To update an existing key, use the <code>update</code> command:</p>
<pre class="code-pre "><code class="code-highlight language-bash">etcdctl update /example/key turtles
</code></pre><pre class="code-pre "><code langs="">turtles
</code></pre>
<p>The companion <code>updatedir</code> command for directories is probably only useful if you have set a TTL, or time-to-live on a directory.  This will update the TTL time with the one passed.  You can set TTLs for directories or keys by passing the <code>--ttl #</code> argument, where "#" is the number of seconds to keep:</p>
<pre class="code-pre "><code class="code-highlight language-bash">etcdctl mkdir /here/you/go --ttl 120
</code></pre>
<p>You can then update the TTL with <code>updatedir</code>:</p>
<pre class="code-pre "><code class="code-highlight language-bash">etcdctl updatedir /here/you/go --ttl 500
</code></pre>
<p>To change the value of an existing key, or to create a key if it does not exist, use the <code>set</code> command.  Think of this as a combination of the <code>mk</code> and <code>update</code> command:</p>
<pre class="code-pre "><code class="code-highlight language-bash">etcdctl set /example/key new
</code></pre><pre class="code-pre "><code langs="">new
</code></pre>
<p>This can include non-existent paths.  The path components will be created dynamically:</p>
<pre class="code-pre "><code class="code-highlight language-bash">etcdctl set /a/b/c here
</code></pre><pre class="code-pre "><code langs="">here
</code></pre>
<p>To get this same create-if-does-not-exist functionality for directories, you can use the <code>setdir</code> command:</p>
<pre class="code-pre "><code class="code-highlight language-bash">etcdctl setdir /x/y/z
</code></pre>
<p><strong>Note</strong>: the <code>setdir</code> command does not currently function as stated.  In the current build, its usage mirrors the <code>updatedir</code> command and will fail if the directory already exists.  There is an open issue on the GitHub repository to address this.</p>

<h3 id="removing-entries">Removing Entries</h3>

<p>To remove existing keys, you can use the <code>rm</code> or <code>rmdir</code> command.</p>

<p>The <code>rm</code> command can be used to remove a key:</p>
<pre class="code-pre "><code class="code-highlight language-bash">etcdctl rm /a/b/c
</code></pre>
<p>It can also be used recursively to remove a directory and every subdirectory:</p>
<pre class="code-pre "><code class="code-highlight language-bash">etcdctl rm /a --recursive
</code></pre>
<p>To remove only an empty directory <em>or</em> a key, use the <code>rmdir</code> command:</p>
<pre class="code-pre "><code class="code-highlight language-bash">etcdctl rmdir /x/y/z
</code></pre>
<p>This can be used to make sure you are only removing the endpoints of the hierarchies.</p>

<h3 id="watching-for-changes">Watching for Changes</h3>

<p>You can watch either a specific key or an entire directory for changes.  Watching these with <code>etcdctl</code> will cause the operation to hang until some event happens to whatever is being watched.</p>

<p>To watch a key, use it without any flags:</p>
<pre class="code-pre "><code class="code-highlight language-bash">etcdctl watch /example/hello
</code></pre>
<p>To stop watching, you can press <code>CTRL-C</code>.  If a change is detected during the watch, the new value will be returned.</p>

<p>To watch an entire directory structure, use the <code>--recursive</code> flag:</p>
<pre class="code-pre "><code class="code-highlight language-bash">etcdctl watch --recursive /example
</code></pre>
<p>You can see how this would be useful by placing it in a simple looping construct to constantly monitor the state of the values:</p>
<pre class="code-pre "><code class="code-highlight language-bash">while true; do etcdctl watch --recursive /example; done
</code></pre>
<p>If you would like to execute a command whenever a change is detected, use the <code>exec-watch</code> command:</p>
<pre class="code-pre "><code class="code-highlight language-bash">etcdctl exec-watch --recursive  /example -- echo "hello"
</code></pre>
<p>This will echo "hello" to the screen whenever a value in that directory changes.</p>

<h3 id="hidden-values">Hidden Values</h3>

<p>One thing that is not immediately apparent is that there are hidden directory structures within <code>etcd</code>.  These are directories or keys that begin with an underscore. </p>

<p>These are not listed by the conventional <code>etcdctl</code> tools and you must know what you are looking for in order to find them.</p>

<p>For instance, there is a hidden directory called <code>/_coreos.com</code> that holds some internal information about <code>fleet</code>.  You can see the hierarchy by explicitly asking for it:</p>
<pre class="code-pre "><code class="code-highlight language-bash">etcdctl ls --recursive /_coreos.com 
</code></pre><pre class="code-pre "><code langs="">/_coreos.com/fleet
/_coreos.com/fleet/states
/_coreos.com/fleet/states/apache@6666.service
/_coreos.com/fleet/states/apache@6666.service/2ddbdb7c872b4bc59dd1969ac166501e
/_coreos.com/fleet/states/apache@7777.service
/_coreos.com/fleet/states/apache@7777.service/921a7241c31a499a97d43f785108b17c
. . .
</code></pre>
<p>Another such directory structure is located within <code>/_etcd</code>:</p>
<pre class="code-pre "><code class="code-highlight language-bash">etcdctl ls --recursive /_etcd
</code></pre><pre class="code-pre "><code langs="">/_etcd/machines
/_etcd/machines/27987f5eaac243f88ca6823b47012c5b
/_etcd/machines/2ddbdb7c872b4bc59dd1969ac166501e
/_etcd/machines/921a7241c31a499a97d43f785108b17c
/_etcd/config
</code></pre>
<p>These function exactly like any other entry, with the only difference being that they do not show up in general listings.  You can create them by simply starting  your key or directory name with an underscore.</p>

<h2 id="etcd-http-json-api-usage">Etcd HTTP/JSON API Usage</h2>

<p>The other way to interacting with <code>etcd</code> is with the simple HTTP/JSON API.</p>

<p>To access the API, you can use a simple HTTP program like <code>curl</code>.  You must supply the <code>-L</code> flag to follow any redirects that are passed back.  From within your cluster, you can use the local <code>127.0.0.1</code> interface and port <code>4001</code> for most queries.</p>

<p><strong>Note</strong>: To connect to <code>etcd</code> from within a Docker container, the address <code>http://172.17.42.1:4001</code> can be used.  This can be useful for applications to update their configurations based on registered information.</p>

<p>The normal keyspace can be reached by going to <code>http://127.0.0.1:4001/v2/keys/</code> on any of the host machines.  For instance, to get a listing of the top-level keys/directories, type:</p>
<pre class="code-pre "><code class="code-highlight language-bash">curl -L http://127.0.0.1:4001/v2/keys/
</code></pre><pre class="code-pre "><code class="code-highlight language-json">{"action":"get","node":{"key":"/","dir":true,"nodes":[{"key":"/coreos.com","dir":true,"modifiedIndex":6,"createdIndex":6},{"key":"/services","dir":true,"modifiedIndex":333,"createdIndex":333}]}}
</code></pre>
<p>The trailing slash in the request is mandatory.  It will not resolve correctly without it.</p>

<p>You can set or retrieve values using normal HTTP verbs.</p>

<p>To modify the behavior of these operations, you can pass in flags at the end of your request using the <code>?flag=value</code> syntax.  Multiple flags can be separated by a <code>&</code> character.</p>

<p>For instance, to recursively list all of the keys, we could type:</p>
<pre class="code-pre "><code class="code-highlight language-bash">curl -L http://127.0.0.1:4001/v2/keys/?recursive=true
</code></pre><pre class="code-pre "><code class="code-highlight language-json">{"action":"get","node":{"key":"/","dir":true,"nodes":[{"key":"/coreos.com","dir":true,"nodes":[{"key":"/coreos.com/updateengine","dir":true,"nodes":[{"key":"/coreos.com/updateengine/rebootlock","dir":true,"nodes":[{"key":"/coreos.com/updateengine/rebootlock/semaphore","value":"{\"semaphore\":1,\"max\":1,\"holders\":null}","modifiedIndex":6,"createdIndex":6}],"modifiedIndex":6,"createdIndex":6}],"modifiedIndex":6,"createdIndex":6}],"modifiedIndex":6,"createdIndex":6}. . .
</code></pre>
<p>Another useful piece of information that is accessible outside of the normal keyspace is version info, accessible here:</p>
<pre class="code-pre "><code class="code-highlight language-bash">curl -L http://127.0.0.1:4001/version
</code></pre><pre class="code-pre "><code langs="">etcd 0.4.6
</code></pre>
<p>You can view stats about each of the cluster leader's relationship with each follower by visiting this endpoint:</p>
<pre class="code-pre "><code class="code-highlight language-bash">curl -L http://127.0.0.1:4001/v2/stats/leader
</code></pre><pre class="code-pre "><code class="code-highlight language-json">{"leader":"921a7241c31a499a97d43f785108b17c","followers":{"27987f5eaac243f88ca6823b47012c5b":{"latency":{"current":1.607038,"average":1.3762888642395448,"standardDeviation":1.4404313533578545,"minimum":0.471432,"maximum":322.728852},"counts":{"fail":0,"success":98718}},"2ddbdb7c872b4bc59dd1969ac166501e":{"latency":{"current":1.584985,"average":1.1554367141497013,"standardDeviation":0.6872303198242179,"minimum":0.427485,"maximum":31.959235},"counts":{"fail":0,"success":98723}}}}
</code></pre>
<p>A similar operation can be used to detect stats about the machine you are currently on:</p>
<pre class="code-pre "><code class="code-highlight language-bash">curl -L http://127.0.0.1:4001/v2/stats/self
</code></pre><pre class="code-pre "><code class="code-highlight language-json">{"name":"921a7241c31a499a97d43f785108b17c","state":"leader","startTime":"2014-09-11T16:42:03.035382298Z","leaderInfo":{"leader":"921a7241c31a499a97d43f785108b17c","uptime":"1h19m11.469872568s","startTime":"2014-09-12T19:47:25.242151859Z"},"recvAppendRequestCnt":1944480,"sendAppendRequestCnt":201817,"sendPkgRate":40.403374523779064,"sendBandwidthRate":3315.096879676072}
</code></pre>
<p>To see stats about operations that have been preformed, type:</p>
<pre class="code-pre "><code class="code-highlight language-bash">curl -L http://127.0.0.1:4001/v2/stats/store
</code></pre><pre class="code-pre "><code class="code-highlight language-json">{"getsSuccess":78823,"getsFail":14,"setsSuccess":121370,"setsFail":4,"deleteSuccess":28,"deleteFail":32,"updateSuccess":20468,"updateFail":4,"createSuccess":39,"createFail":102340,"compareAndSwapSuccess":51169,"compareAndSwapFail":0,"compareAndDeleteSuccess":0,"compareAndDeleteFail":0,"expireCount":3,"watchers":6}
</code></pre>
<p>These are just a few of the operations that can be used to control <code>etcd</code> through the API.</p>

<h2 id="etcd-configuration">Etcd Configuration</h2>

<p>The <code>etcd</code> service can be configured in a few different ways.</p>

<p>The first way is to pass in parameters with your <code>cloud-config</code> file that you use to bootstrap your nodes.  In the bootstrapping guide, you saw a bit about how to do this:</p>
<pre class="code-pre yaml"><code langs="">#cloud-config

coreos:
  etcd:
    discovery: https://discovery.etcd.io/<span class="highlight"><token></span>
    addr: $private_ipv4:4001
    peer-addr: $private_ipv4:7001
. . .
</code></pre>
<p>To see the options that you have available, use the <code>-h</code> flag with <code>etcd</code>:</p>
<pre class="code-pre "><code class="code-highlight language-bash">etcd -h
</code></pre>
<p>To include these options in your <code>cloud-config</code>, simply take off the leading dash and separate keys from values with a colon instead of an equal sign.  So <code>-peer-addr=<host:port></code> becomes <code>peer-addr: <host:port></code>.</p>

<p>Upon reading the <code>cloud-config</code> file, CoreOS will translate these into environmental variables in a stub unit file, which is used to start the service.</p>

<p>Another way to adjust the settings for <code>etcd</code> is through the API.  This is generally done using the <code>7001</code> port instead of the standard <code>4001</code> that is used for key queries.</p>

<p>For instance, you can get some of the current configuration values by typing:</p>
<pre class="code-pre "><code class="code-highlight language-bash">curl -L http://127.0.0.1:7001/v2/admin/config
</code></pre><pre class="code-pre "><code class="code-highlight language-json">{"activeSize":9,"removeDelay":1800,"syncInterval":5}
</code></pre>
<p>You can change these values by passing in the new JSON as the data payload with a PUT operation:</p>
<pre class="code-pre "><code class="code-highlight language-bash">curl -L http://127.0.0.1:7001/v2/admin/config -XPUT -d '{"activeSize":9,"removeDelay":1800,"syncInterval":5}'
</code></pre><pre class="code-pre "><code class="code-highlight language-json">{"activeSize":9,"removeDelay":1800,"syncInterval":5}
</code></pre>
<p>To get a list of machines, you can go to the <code>/v2/admin/machines</code> endpoint:</p>
<pre class="code-pre "><code class="code-highlight language-bash">curl -L http://127.0.0.1:7001/v2/admin/machines
</code></pre><pre class="code-pre "><code class="code-highlight language-json">[{"name":"27987f5eaac243f88ca6823b47012c5b","state":"follower","clientURL":"http://10.132.248.121:4001","peerURL":"http://10.132.248.121:7001"},{"name":"2ddbdb7c872b4bc59dd1969ac166501e","state":"follower","clientURL":"http://10.132.252.38:4001","peerURL":"http://10.132.252.38:7001"},{"name":"921a7241c31a499a97d43f785108b17c","state":"leader","clientURL":"http://10.132.248.118:4001","peerURL":"http://10.132.248.118:7001"}]
</code></pre>
<p>This can be used to remove machines forcefully from the cluster with the DELETE method.</p>

<h2 id="conclusion">Conclusion</h2>

<p>As you can see, <code>etcd</code> can be used to store or retrieve information from any machine in your cluster.  This allows you to synchronize data and provides a location for services to look for configuration data and connection details.</p>

<p>This is especially useful when building distributed systems because you can provide a simple endpoint that will be valid from any location within the cluster.  By taking advantage of this resource, your services can dynamically configure themselves.</p>

    