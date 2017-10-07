<link rel='stylesheet' href='digital_ocean.css'><br> <br> 
      <h3 id="introduction">Introduction</h3>

<p>Redis is an in-memory, NoSQL, key-value cache and store that can also be persisted to disk. It's been growing in popularity and it's being used as a datastore in both big and small projects. For any number of reasons, like transitioning to a more powerful server, sometimes it becomes necessary to migrate that data from one server to another.</p>

<p>Though it's possible to just copy the database files from the current server to the new one, the recommended method of migrating a Redis database is to use a replication setup in a master-slave fashion. Such a setup is much faster than copying files and involves very little or no downtime.</p>

<p>This article will show how to migrate Redis data from an Ubuntu 14.04 server to a similar server using master-slave replication. This involves setting up a new Redis server, configuring it to be the slave of the current server (i.e. the master), then promoting the slave to master after the migration is completed.</p>

<h2 id="prerequisites">Prerequisites</h2>

<p>To follow this article, you will need one Redis master server with the data you want to export or migrate, and a second new Redis server which will be the slave.</p>

<p>Specifically, these are the prerequisites for the Redis master.</p>

<ul>
<li><p>One Ubuntu 14.04 server with:</p>

<ul>
<li>A sudo non-root user, set up via the <a href="https://indiareads/community/tutorials/initial-server-setup-with-ubuntu-14-04">initial server setup guide</a></li>
<li>A firewall configured via <a href="https://indiareads/community/tutorials/how-to-implement-a-basic-firewall-template-with-iptables-on-ubuntu-14-04">this IPTables tutorial</a>, up through the <strong>(Optional) Update Nameservers</strong> step</li>
<li>Redis installed and set up as master by following steps 1 and 2 from <a href="https://indiareads/community/tutorials/how-to-configure-a-redis-cluster-on-ubuntu-14-04">this Redis cluster tutorial</a></li>
<li>Some data to migrate, which you can set up by following steps 1 and 2 from <a href="https://indiareads/community/tutorials/how-to-back-up-and-restore-your-redis-data-on-ubuntu-14-04">this Redis restoration article</a></li>
</ul></li>
</ul>

<p>And these are the prerequisites for the Redis slave.</p>

<ul>
<li><p>A second Ubuntu 14.04 server with:</p>

<ul>
<li>A sudo non-root user, set up via the <a href="https://indiareads/community/tutorials/initial-server-setup-with-ubuntu-14-04">initial server setup guide</a></li>
<li>A firewall configured via <a href="https://indiareads/community/tutorials/how-to-implement-a-basic-firewall-template-with-iptables-on-ubuntu-14-04">this IPTables tutorial</a>, up through the <strong>(Optional) Update Nameservers</strong> step</li>
<li>Redis installed and set up as slave by following steps 1 and 3 from <a href="https://indiareads/community/tutorials/how-to-configure-a-redis-cluster-on-ubuntu-14-04">this Redis cluster tutorial</a></li>
</ul></li>
</ul>

<p>Make sure to following the nameserver configuration section in the IPTables tutorial on both servers; without it, <code>apt</code> won't work.</p>

<h2 id="step-1-—-updating-the-redis-master-firewall">Step 1 — Updating the Redis Master Firewall</h2>

<p>After installing and configuring the Redis slave, what you have are two independent servers that are not communicating because of the firewall rules. In this step, we'll fix that</p>

<p>The fix involves adding an exception to the TCP rules on the master to allow Redis traffic on port 6379. So, on the master, open the IPTables configuration file for IPv4 rules.</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo nano /etc/iptables/rules.v4
</li></ul></code></pre>
<p>Right below the rule that allows SSH traffic, add a rule for Redis that allows traffic on the Redis port <em>only</em> from the slave's IP address. Make sure to update <code><span class="highlight">your_slave_ip_address</span></code> to the IP address of the slave server.</p>
<div class="code-label " title="/etc/iptables/rules.v4">/etc/iptables/rules.v4</div><pre class="code-pre "><code langs="">. . .
# Acceptable TCP traffic
-A TCP -p tcp --dport 22 -j ACCEPT
<span class="highlight">-A TCP -p tcp -s your_slave_ip_address --dport 6379 -j ACCEPT</span>
. . .
</code></pre>
<p>This is being very restrictive and more secure. Otherwise, the server would accept traffic from any host on the Redis port.</p>

<p>Restart IPTables to apply the new rule.</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo service iptables-persistent restart
</li></ul></code></pre>
<p>Now that the replication system is up and the firewall on the master has been configured to allow Redis traffic, we can verify that both servers can communicate. That can be done with the instructions given in Step 4 of <a href="https://indiareads/community/tutorials/how-to-configure-a-redis-cluster-on-ubuntu-14-04">this Redis cluster tutorial</a>.</p>

<h2 id="step-2-—-verifying-the-data-import">Step 2 — Verifying the Data Import</h2>

<p>If both servers have established contact, data import from the server to the slave should start automatically. You now only have to verify that it has, and has completed successfully. There are multiple ways of verifying that.</p>

<h3 id="the-redis-data-directory">The Redis Data Directory</h3>

<p>One way to verify a successful data import is to look in the Redis data directory. The same files that are on the master should now be on the slave. If you do a long listing of the files in the Redis data directory of the slave server using this command:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">ls -lh /var/lib/redis
</li></ul></code></pre>
<p>You should get an output of this sort:</p>
<div class="code-label " title="Output">Output</div><pre class="code-pre "><code langs="">
total 32M
-rw-r----- 1 redis redis 19M Oct  6 22:53 appendonly.aof
-rw-rw---- 1 redis redis 13M Oct  6 22:53 dump.rdb
</code></pre>
<h3 id="the-redis-command-line">The Redis Command Line</h3>

<p>Another method of verifying data import is from the Redis command line. Enter the command line on the slave server.</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">redis-cli
</li></ul></code></pre>
<p>Then authenticate and issue the <code>info</code> command</p>
<pre class="code-pre custom_prefix"><code langs=""><ul class="prefixed"><li class="line" prefix="127.0.0.1:6379>">auth <span class="highlight">insert-redis-password-here</span>
</li><li class="line" prefix="127.0.0.1:6379>">
</li><li class="line" prefix="127.0.0.1:6379>">info
</li></ul></code></pre>
<p>In the output, the number of keys in the <strong># Keyspace</strong> should be the same on both servers. The output below was taken from the slave server, which was exactly the same as the output on the master server.</p>
<div class="code-label " title="Output">Output</div><pre class="code-pre "><code langs=""># Keyspace
db0:keys=26378,expires=0,avg_ttl=0
</code></pre>
<h3 id="scan-the-keys">Scan the Keys</h3>

<p>Yet another method of verifying that the slave now has the same data that's on the master is to use the <code>scan</code> command from the Redis command line. Though the output from that command will not always be the same across both server's, when issued on the slave, it will at least let you confirm that the slave has the data that you expect to find on it.</p>

<p>An example output from the test server used for this article is shown below. Note that the argument to the <code>scan</code> command is just any number and acts as a cursor:</p>
<pre class="code-pre custom_prefix"><code langs=""><ul class="prefixed"><li class="line" prefix="127.0.0.1:6379>">scan 0
</li></ul></code></pre>
<p>The output should be similar to this:</p>
<div class="code-label " title="Output">Output</div><pre class="code-pre "><code langs="">1) "17408"
2)  1) "uid:5358:ip"
    2) "nodebbpostsearch:object:422"
    3) "uid:4163:ip"
    4) "user:15682"
    5) "user:1635"
    6) "nodebbpostsearch:word:HRT"
    7) "uid:6970:ip"
    8) "user:15641"
    9) "tid:10:posts"
   10) "nodebbpostsearch:word:AKL"
   11) "user:4648"
127.0.0.1:6379>
</code></pre>
<h2 id="step-3-—-promoting-the-slave-to-master">Step 3 — Promoting the Slave to Master</h2>

<p>Once you've confirmed that the slave has all the data, you can promote it to master. This is also covered in <a href="https://indiareads/community/tutorials/how-to-configure-a-redis-cluster-on-ubuntu-14-04#step-5-%E2%80%94-switch-to-the-slave">Step 5 of the Redis cluster tutorial</a>, but for simplicity, the instructions are here, too.</p>

<p>First, enter the Redis command line on the slave.</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">redis-cli
</li></ul></code></pre>
<p>After authenticating, issue the <code>slaveof no one</code> command to promote it to master.</p>
<pre class="code-pre custom_prefix"><code langs=""><ul class="prefixed"><li class="line" prefix="127.0.0.1:6379>">auth <span class="highlight">your_redis_password</span>
</li><li class="line" prefix="127.0.0.1:6379>">slaveof no one
</li></ul></code></pre>
<p>You should get this output:</p>
<div class="code-label " title="Output">Output</div><pre class="code-pre "><code langs="">OK
</code></pre>
<p>Then use the <code>info</code> command to verify.</p>
<pre class="code-pre custom_prefix"><code langs=""><ul class="prefixed"><li class="line" prefix="127.0.0.1:6379>">info
</li></ul></code></pre>
<p>The relevant output in the <strong>Replication</strong> section should look like this. In particular, <strong>role:master</strong> line shows that the slave is now the master.</p>
<div class="code-label " title="Output">Output</div><pre class="code-pre "><code langs=""># Replication
<span class="highlight">role:master</span>
connected_slaves:0
master_repl_offset:11705
repl_backlog_active:0
repl_backlog_size:1048576
repl_backlog_first_byte_offset:0
repl_backlog_histlen:0
</code></pre>
<p>Afterwards, a single entry in the former master's log file should also confirm that.</p>
<div class="code-label " title="/var/log/redis/redis-server.log">/var/log/redis/redis-server.log</div><pre class="code-pre "><code langs="">
14613:M 07 Oct 14:03:44.159 # Connection with slave 192.168.1.8:6379 lost.
</code></pre>
<p>And on the new master (formerly the slave), you should see:</p>
<div class="code-label " title="/var/log/redis/redis-server.log">/var/log/redis/redis-server.log</div><pre class="code-pre "><code langs="">14573:M 07 Oct 14:03:44.150 # Connection with master lost.
14573:M 07 Oct 14:03:44.150 * Caching the disconnected master state.
14573:M 07 Oct 14:03:44.151 * Discarding previously cached master state.
14573:M 07 Oct 14:03:44.151 * MASTER MODE enabled (user request from 'id=4 addr=127.0.0.1:52055 fd=6 name= age=2225 idle=0 flags=N db=0 sub=0 psub=0 multi=-1 qbuf=0 qbuf-free=32768 obl=0 oll=0 omem=0 events=r cmd=slaveof')
</code></pre>
<p>At this point, you may now connect the applications to the database, and you may delete or destroy the original master.</p>

<h2 id="conclusion">Conclusion</h2>

<p>When done correctly, migrating Redis data in this fashion is a straightforward task. The main source of error is typically forgetting to modify the firewall of the master server to allow Redis traffic.</p>

<p>You can learn how to do more with Redis by browsing <a href="https://indiareads/community/tags/redis?type=tutorials">more Redis tutorials</a>.</p>

    