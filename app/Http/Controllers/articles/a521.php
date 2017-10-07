<link rel='stylesheet' href='digital_ocean.css'><br> <br> 
      <p>Logs are essential to troubleshooting your Redis installation. You may ask yourself "Where are my Redis logs?" or "Where does Redis store log files on Ubuntu 14.04?"</p>

<p>With a default <code>apt-get</code> installation on Ubuntu 14.04, Redis log files are located at <code>/var/log/redis/redis-server.log</code>.</p>

<p>To view the last 10 lines:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo tail /var/log/redis/redis-server.log
</li></ul></code></pre>
<p>With a default from-source installation on Ubuntu 14.04, Redis log files are located at <code>/var/log/redis_6379.log</code>.</p>

<p>To view the last 10 lines:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo tail /var/log/redis_6379.log
</li></ul></code></pre>
<p>The <a href="https://indiareads/community/tutorials/how-to-use-the-redis-one-click-application">IndiaReads Redis one-click</a> log files are located at <code>/var/log/redis/redis_6379.log</code>.</p>

<p>To view the last 10 lines:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo tail /var/log/redis/redis_6379.log
</li></ul></code></pre>
<h2 id="checking-archived-log-files">Checking Archived Log Files</h2>

<p>Redis also archives older log files. See a list of the archived logs with:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">ls /var/log/redis
</li></ul></code></pre><div class="code-label " title="Output">Output</div><pre class="code-pre "><code langs="">redis-server.log  redis-server.log.1.gz
</code></pre>
<p>You can gunzip an older file:</p>
<pre class="code-pre "><code langs="">sudo gunzip /var/log/redis/redis-server.log.<span class="highlight">1</span>.gz
</code></pre>
<p>Then view its last 10 lines:</p>
<pre class="code-pre "><code langs="">sudo tail /var/log/redis/redis-server.log.<span class="highlight">1</span>
</code></pre>
<h2 id="using-find-to-search-for-logs">Using find to Search for Logs</h2>

<p>If your logs aren't in either of those locations, you can conduct a more general search using <code>find</code> in the <code>/var/logs</code> directory:</p>
<pre class="code-pre "><code langs="">find /var/log/* -name *redis*
</code></pre>
<p>Or, search your entire system. This might take a while if you have a lot of files. It will turn up a few permission warnings, which is normal, although we're avoiding the worst of them in <code>/proc</code> and <code>/sys</code> with the two <code>-prune</code> flags. It will also turn up every file with <code>redis</code> in the name, which includes installation files:</p>
<pre class="code-pre "><code langs="">find / -path /sys -prune -o -path /proc -prune -o -name *redis*
</code></pre>
<h2 id="setting-the-log-location-in-redis-conf">Setting the Log Location in redis.conf</h2>

<p>The Redis log location is specified in Redis's configuration file, <code>redis.conf</code>, often located at <code>/etc/redis/redis.conf</code>.</p>

<p>Open that file for editing:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo nano /etc/redis/redis.conf
</li></ul></code></pre>
<p>Locate the <code>logfile</code> line:</p>
<div class="code-label " title="/etc/redis/redis.conf">/etc/redis/redis.conf</div><pre class="code-pre "><code langs="">logfile <span class="highlight">/var/log/redis/redis-server.log</span>
</code></pre>
<p>Note the location of the log files. You can edit this file path if you want to rename the log file or change its location.</p>

<h2 id="ubuntu-15-04-and-higher-checking-systemd-logs-with-journalctl">Ubuntu 15.04 and Higher: Checking systemd Logs with journalctl</h2>

<p>You may also want to check the logs collected for Redis by systemd. (Ubuntu 15.04 and higher use systemd, although Ubuntu 14.04 defaults to Upstart.) To learn how to use the <code>journalctl</code> command for this purpose, please read this <a href="https://indiareads/community/tutorials/how-to-use-journalctl-to-view-and-manipulate-systemd-logs">article about journalctl</a>.</p>

<h2 id="conclusion">Conclusion</h2>

<p>If you want to learn more about setting up Redis, please read this article about <a href="https://indiareads/community/tutorials/how-to-configure-a-redis-cluster-on-ubuntu-14-04">setting up a Redis cluster</a>.</p>

    