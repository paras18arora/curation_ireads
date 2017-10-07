<link rel='stylesheet' href='digital_ocean.css'><br><img src=https://community-cdn-digitalocean-com.global.ssl.fastly.net/assets/tutorials/images/large/rediscluster.png?1440439034/> <br> 
      <h3 id="introduction">Introduction</h3>

<p>Redis is an open source key-value data store, using an in-memory storage model with optional disk writes for persistence. It features transactions, pub/sub, and automatic failover, among other functionality. It is recommended to use Redis with Linux for production environments, but the developers also mention OS X as a platform on which they develop and test. Redis has clients written in most languages, with recommended ones featured on <a href="http://redis.io/clients">their website</a>.</p>

<p>For production environments, replicating your data across at least two nodes is considered the best practice. Redundancy allows for recovery in case of environment failure, which is especially important when the user base of your application grows.</p>

<p>By the end of this guide, we will have set up two Redis Droplets on IndiaReads, as follows:</p>

<ul>
<li>one Droplet for the Redis master server</li>
<li>one Droplet for the Redis slave server</li>
</ul>

<p>We will also demonstrate how to switch to the slave server and set it up as a temporary master.  </p>

<p>Feel free to set up more than one slave server.</p>

<p>This article focuses on setting up a master-slave Redis cluster; to learn more about Redis in general and its basic usage as a database, see <a href="https://indiareads/community/tutorials/how-to-install-and-use-redis">this usage tutorial</a>.</p>

<h2 id="prerequisites">Prerequisites</h2>

<p>While this may work on earlier releases and other Linux distributions, we recommend Ubuntu 14.04. </p>

<p>For testing purposes, we will use small instances as there is no real workload to be handled, but production environments may require larger servers.</p>

<ul>
<li>Ubuntu 14.04 LTS</li>
<li>Two Droplets, of any size you need; one <strong>master</strong> and one or more <strong>slave(s)</strong></li>
<li>Access to your machines via SSH with a sudo non-root user as explained in <a href="https://indiareads/community/tutorials/initial-server-setup-with-ubuntu-14-04">Initial Server Setup with Ubuntu 14.04</a></li>
</ul>

<h2 id="step-1-—-install-redis">Step 1 — Install Redis</h2>

<p>Starting with the Droplet that will host our <strong>master server</strong>, our first step is to install Redis. First we need to add Chris Lea's Redis repository (as always, take extreme caution when adding third party repositories; we are using this one because its maintainer is a reputable figure):</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo add-apt-repository ppa:chris-lea/redis-server
</li></ul></code></pre>
<p>Press <code>ENTER</code> to accept the repository.</p>

<p>Run the following command to update our packages:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo apt-get update
</li></ul></code></pre>
<p>Install the Redis server:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo apt-get install redis-server
</li></ul></code></pre>
<p>Check that Redis is up and running: </p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">redis-benchmark -q -n 1000 -c 10 -P 5
</li></ul></code></pre>
<p>The above command is saying that we want <code>redis-benchmark</code> to run in quiet mode, with 1000 total requests, 10 parallel connections and pipeline 5 requests. For more information on running benchmarks for Redis, typing <code>redis-benchmark --help</code> in your terminal will print useful information with examples.</p>

<p>Let the benchmark run. After it's finished, you should see output similar to the following:</p>
<pre class="code-pre "><code langs=""><div class="secondary-code-label " title="Output">Output</div><span class="highlight">PING_INLINE: 166666.67 requests per second</span>
<span class="highlight">PING_BULK: 249999.98 requests per second</span>
<span class="highlight">SET: 249999.98 requests per second</span>
<span class="highlight">GET: 499999.97 requests per second</span>
<span class="highlight">INCR: 333333.34 requests per second</span>
<span class="highlight">LPUSH: 499999.97 requests per second</span>
<span class="highlight">LPOP: 499999.97 requests per second</span>
<span class="highlight">SADD: 499999.97 requests per second</span>
<span class="highlight">SPOP: 499999.97 requests per second</span>
<span class="highlight">LPUSH (needed to benchmark LRANGE): 499999.97 requests per second</span>
<span class="highlight">LRANGE_100 (first 100 elements): 111111.12 requests per second</span>
<span class="highlight">LRANGE_300 (first 300 elements): 27777.78 requests per second</span>
<span class="highlight">LRANGE_500 (first 450 elements): 8333.33 requests per second</span>
<span class="highlight">LRANGE_600 (first 600 elements): 6369.43 requests per second</span>
<span class="highlight">MSET (10 keys): 142857.14 requests per second</span>
</code></pre>
<p>Now repeat this section for the Redis <strong>slave server</strong>. If you are configuring more Droplets, you may set up as many slave servers as necessary.</p>

<p>At this point, Redis is installed and running on our two nodes. If the output of any node is not similar to what is shown above, repeat the setup process carefully and check that all prerequisites are met</p>

<h2 id="step-2-—-configure-redis-master">Step 2 — Configure Redis Master</h2>

<p>Now that Redis is up and running on our two-Droplet cluster, we have to edit their configuration files. As we will see, there are minor differences between configuring the master server and the slave.</p>

<p>Let's first start with our <strong>master</strong>.</p>

<p>Open <code>/etc/redis/redis.conf</code> with your favorite text editor:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo nano /etc/redis/redis.conf
</li></ul></code></pre>
<p>Edit the following lines.</p>

<p>Set a sensible value to the keepalive timer for TCP:</p>
<div class="code-label " title="/etc/redis/redis.conf">/etc/redis/redis.conf</div><pre class="code-pre "><code langs="">tcp-keepalive <span class="highlight">60</span>
</code></pre>
<p>Make the server accessible to anyone on the web by commenting out this line:</p>
<div class="code-label " title="/etc/redis/redis.conf">/etc/redis/redis.conf</div><pre class="code-pre "><code langs=""><span class="highlight">#</span>bind 127.0.0.1
</code></pre>
<p>Given the nature of Redis, and its very high speeds, an attacker may brute force the password without many issues. That is why we recommend uncommenting the <code>requirepass</code> line and adding a complex password (or a complex passphrase, preferably):</p>
<div class="code-label " title="/etc/redis/redis.conf">/etc/redis/redis.conf</div><pre class="code-pre "><code langs="">requirepass <span class="highlight">your_redis_master_password</span>
</code></pre>
<p>Depending on your usage scenario, you may change the following line or not. For the purpose of this tutorial, we assume that no key deletion must occur. Uncomment this line and set it as follows:</p>
<div class="code-label " title="/etc/redis/redis.conf">/etc/redis/redis.conf</div><pre class="code-pre "><code langs="">maxmemory-policy <span class="highlight">noeviction</span>
</code></pre>
<p>Finally, we want to make the following changes, required for backing up data. Uncomment and/or set these lines as shown:</p>
<div class="code-label " title="/etc/redis/redis.conf">/etc/redis/redis.conf</div><pre class="code-pre "><code langs="">appendonly <span class="highlight">yes</span>
appendfilename <span class="highlight">redis-staging-ao.aof</span>
</code></pre>
<p>Save your changes.</p>

<p>Restart the Redis service to reload our configuration changes:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo service redis-server restart
</li></ul></code></pre>
<p>If you want to go the extra mile, you can add some unique content to the master database by following the <strong>Redis Operations</strong> sections in <a href="https://indiareads/community/tutorials/how-to-install-and-use-redis">this tutorial</a>, so we can later see how it gets replicated to the slave server.</p>

<p>Now that we have the master server ready, let's move on to our slave machine.</p>

<h2 id="step-3-—-configure-redis-slave">Step 3 — Configure Redis Slave</h2>

<p>We need to make some changes that allow our <strong>slave server</strong> to connect to our master instance:</p>

<p>Open <code>/etc/redis/redis.conf</code> with your favorite text editor:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo nano /etc/redis/redis.conf
</li></ul></code></pre>
<p>Edit the following lines; some settings will be similar to the master's.</p>

<p>Make the server accessible to anyone on the web by commenting out this line:</p>
<div class="code-label " title="/etc/redis/redis.conf">/etc/redis/redis.conf</div><pre class="code-pre "><code langs=""><span class="highlight">#</span>bind 127.0.0.1
</code></pre>
<p>The slave server needs a password as well so we can give it commands (such as <code>INFO</code>). Uncomment this line and set a server password:</p>
<div class="code-label " title="/etc/redis/redis.conf">/etc/redis/redis.conf</div><pre class="code-pre "><code langs="">requirepass <span class="highlight">your_redis_slave_password</span>
</code></pre>
<p>Uncomment this line and indicate the IP address where the <strong>master server</strong> can be reached, followed by the port set on that machine. By default, the port is 6379:</p>
<div class="code-label " title="/etc/redis/redis.conf">/etc/redis/redis.conf</div><pre class="code-pre "><code langs="">slaveof <span class="highlight">your_redis_master_ip 6379</span>
</code></pre>
<p>Uncomment the <code>masterauth</code> line and provide the password/passphrase you set up earlier on the <strong>master server</strong>:</p>
<div class="code-label " title="/etc/redis/redis.conf">/etc/redis/redis.conf</div><pre class="code-pre "><code langs="">masterauth <span class="highlight">your_redis_master_password</span>
</code></pre>
<p>Now save these changes, and exit the file. Next, restart the service like we did on our master server:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo service redis-server restart
</li></ul></code></pre>
<p>This will reinitialize Redis and load our modified files.</p>

<p>Connect to Redis:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">redis-cli -h 127.0.0.1 -p <span class="highlight">6379</span> 
</li></ul></code></pre>
<p>Authorize with the <strong>slave server's password</strong>:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">AUTH <span class="highlight">your_redis_slave_password</span>
</li></ul></code></pre>
<p>At this point we are running a functional master-slave Redis cluster, with both machines properly configured.</p>

<h2 id="step-4-—-verify-the-master-slave-replication">Step 4 — Verify the Master-Slave Replication</h2>

<p>Testing our setup will allow us to better understand the behavior of our Redis Droplets, once we want to start scripting failover behavior. What we want to do now is make sure that our configuration is working correctly, and our master is talking with the slave Redis instances.</p>

<p>First, we connect to Redis via our terminal, on the <strong>master server</strong>:</p>

<p>First connect to the local instance, running by default on port 6379. In case you've changed the port, modify the command accordingly.</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">redis-cli -h 127.0.0.1 -p <span class="highlight">6379</span>
</li></ul></code></pre>
<p>Now authenticate with Redis with the password you set when configuring the master:</p>
<pre class="code-pre custom_prefix"><code langs=""><ul class="prefixed"><li class="line" prefix="127.0.0.1:6379>">AUTH <span class="highlight">your_redis_master_password</span>
</li></ul></code></pre>
<p>And you should get an <code>OK</code> as a response. Now, you only have to run:</p>
<pre class="code-pre custom_prefix"><code langs=""><ul class="prefixed"><li class="line" prefix="127.0.0.1:6379>">INFO
</li></ul></code></pre>
<p>You will see everything you need to know about the master Redis server. We are especially interested in the <code>#Replication</code> section, which should look like the following output:</p>
<pre class="code-pre "><code langs=""><div class="secondary-code-label " title="Output">Output</div>. . .

# Replication
role:master
connected_slaves:1
slave0:ip=<span class="highlight">111.111.111.222</span>,port=<span class="highlight">6379</span>,state=online,offset=407,lag=1
master_repl_offset:407
repl_backlog_active:1
repl_backlog_size:1048576
repl_backlog_first_byte_offset:2
repl_backlog_histlen:406

. . .
</code></pre>
<p>Notice the <code>connected_slaves:1</code> line, which indicates our other instance is talking with the master Droplet. You can also see that we get the slave IP address, along with port, state, and other info.</p>

<p>Let's now take a look at the <code>#Replication</code> section on our slave machine. The process is the same as for our master server. Log in to the Redis instance, issue the <code>INFO</code> command, and view the output:</p>
<pre class="code-pre "><code langs=""><div class="secondary-code-label " title="Output">Output</div>. . .

# Replication
role:slave
master_host:<span class="highlight">111.111.111.111</span>
master_port:<span class="highlight">6379</span>
master_link_status:up
master_last_io_seconds_ago:3
master_sync_in_progress:0
slave_repl_offset:1401
slave_priority:100
slave_read_only:1
connected_slaves:0
master_repl_offset:0
repl_backlog_active:0
repl_backlog_size:1048576
repl_backlog_first_byte_offset:0
repl_backlog_histlen:0

. . .
</code></pre>
<p>We can see that this machine has the role of slave, is communicating with the master Redis server, and has no slaves of its own.</p>

<h2 id="step-5-—-switch-to-the-slave">Step 5 — Switch to the Slave</h2>

<p>Building this architecture means that we also want failures to be handled in such a way that we ensure data integrity and as little downtime as possible for our application. Any slave can be promoted to be a master. First, let's test switching manually.</p>

<p>On a <strong>slave machine</strong>, we should connect to the Redis instance:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">redis-cli -h 127.0.0.1 -p <span class="highlight">6379</span>
</li></ul></code></pre>
<p>Now authenticate with Redis with the password you set when configuring the slave</p>
<pre class="code-pre custom_prefix"><code langs=""><ul class="prefixed"><li class="line" prefix="127.0.0.1:6379>">AUTH <span class="highlight">your_redis_slave_password</span>
</li></ul></code></pre>
<p>Turn off slave behavior:</p>
<pre class="code-pre custom_prefix"><code langs=""><ul class="prefixed"><li class="line" prefix="127.0.0.1:6379>">SLAVEOF NO ONE
</li></ul></code></pre>
<p>The response should be <code>OK</code>. Now type:</p>
<pre class="code-pre custom_prefix"><code langs=""><ul class="prefixed"><li class="line" prefix="127.0.0.1:6379>">INFO
</li></ul></code></pre>
<p>Look for the <code># Replication</code> section to find the following output:</p>
<pre class="code-pre "><code langs=""><div class="secondary-code-label " title="Output">Output</div>. . .

# Replication
role:master
connected_slaves:0
master_repl_offset:1737
repl_backlog_active:0
repl_backlog_size:1048576
repl_backlog_first_byte_offset:0
repl_backlog_histlen:0

. . .
</code></pre>
<p>As we expected, the slave has turned into a master, and is now ready to accept connections from other machines (if any). We can use it as a temporary backup while we debug our main master server.</p>

<p><span class="note">If you have multiple slaves that depended on the initial master, they all have to be pointed towards the newly promoted master.<br /></span></p>

<p>This can be scripted easily, with the following steps needing to be implemented once a failure is detected:</p>

<ul>
<li>From the application, send all requests for Redis to a slave machine</li>
<li>On that slave, execute the <code>SLAVEOF NO ONE</code> command. Starting with Redis version 1.0.0, this command tells the slave to stop replicating data, and start acting as a master server</li>
<li>On all remaining slaves (if any), running <code>SLAVEOF <span class="highlight">hostname</span> <span class="highlight">port</span></code> will instruct them to stop replicating from the old master, discard the now deprecated data completely, and start replicating from the new master. Make sure to replace <code><span class="highlight">hostname</span></code> and <code><span class="highlight">port</span></code> with the correct values, from your newly promoted master</li>
<li>After analyzing the issue, you may return to having your initial server as master, if your particular setup requires it</li>
</ul>

<p><span class="note">There are many ways of accomplishing the steps explained above. However, it is up to you to implement an adequate solution for your environment, and make sure to test it thoroughly before any actual failures occur.<br /></span></p>

<h2 id="step-6-—-reconnect-to-the-master">Step 6 — Reconnect to the Master</h2>

<p>Let's reconnect to the original master server. On the <strong>slave server</strong>, log in to Redis and execute the following: </p>
<pre class="code-pre custom_prefix"><code langs=""><ul class="prefixed"><li class="line" prefix="127.0.0.1:6379>">SLAVEOF <span class="highlight">your_redis_master_ip 6379</span>
</li></ul></code></pre>
<p>If you run the <code>INFO</code> command again, you should see we have returned to the original setup.</p>

<h2 id="conclusion">Conclusion</h2>

<p>We have properly set up an enviroment consisting of two servers, one acting as Redis master, and the other replicating data as a slave. This way, if the master server ever goes offline or loses our data, we know how to switch to one of our slaves for recovery until the issue is taken care of.</p>

<p>Next steps might include scripting the automated failover procedure, or ensuring secure communications between all your Droplets by the use of VPN solutions such as <a href="https://indiareads/community/tutorials/how-to-set-up-an-openvpn-server-on-ubuntu-14-04">OpenVPN</a> or <a href="https://indiareads/community/tutorials/how-to-install-tinc-and-set-up-a-basic-vpn-on-ubuntu-14-04">Tinc</a>. Also, testing procedures and scripts are vital for validating your configurations.</p>

<p>Additionally, you should take precautions when deploying this kind of setup in production environments. The <a href="http://redis.io/documentation">Redis Documentation</a> page should be studied and you must have a clear understanding of what security model is adequate for your application. We often use Redis as a session store, and the information it contains can be valuable to an attacker. Common practice is to have these machines accessible only via private network, and place them behind multiple layers of security.</p>

<p>This is a simple starting point on which your data store may be built; by no means an exhaustive guide on setting up Redis to use master-slave architecture. If there is anything that you consider this guide should cover, please leave comments below. For more information and help on this topic, the <a href="https://indiareads/community/questions">IndiaReads Q&A</a> is a good place to start.</p>

    