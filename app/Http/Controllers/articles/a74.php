<link rel='stylesheet' href='digital_ocean.css'><br><img src=https://community-cdn-digitalocean-com.global.ssl.fastly.net/assets/tutorials/images/large/Redis-OneClick-TW.png?1442512736/> <br> 
      <p>Redis is a scalable in-memory key-value store that excels at caching. IndiaReads's Redis One-Click application allows you to quickly spin up a Droplet with Redis pre-installed. It aims to help get your application off the ground quickly.</p>

<h2 id="creating-your-redis-droplet">Creating Your Redis Droplet</h2>

<p>You can launch a new Redis instance by selecting <strong>Redis on 14.04</strong> from the Applications menu during Droplet creation:</p>

<p><img src="https://assets.digitalocean.com/articles/redis-one-click/redis-one-click.png" alt="" /></p>

<p>Once you have created the Droplet, connect to it via the web-based console in the IndiaReads control panel or SSH:</p>
<pre class="code-pre "><code langs="">ssh root@<span class="highlight">your.ip.address</span>
</code></pre>
<h2 id="accessing-redis">Accessing Redis</h2>

<p>Your Redis instance will be available at <code>127.0.0.1:6379</code> It is bound to the localhost by default and its configuration details can be found in <code>/etc/redis/redis.conf</code>. To connect to Redis's interactive shell, simply run:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">redis-cli
</li></ul></code></pre>
<h2 id="securing-and-accessing-remotely">Securing And Accessing Remotely</h2>

<h3 id="enabling-authentication">Enabling Authentication</h3>

<p>Before allowing remote access to your Redis database, it is recommended to enable password authentication. To do so, open its configuration file located in <code>/etc/redis/redis.conf</code> and append a line beginning with "requirepass" and followed by your password. For example:</p>
<pre class="code-pre "><code langs="">requirepass <span class="highlight">your_redis_password</span>
</code></pre>
<p>In order for this to take effect, you must first restart Redis with:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo service redis restart
</li></ul></code></pre>
<p>To verify that authentication has been enabled, enter the shell with <code>redis-cli</code> and attempt to run a query. You should be presented with an error:</p>
<pre class="code-pre custom_prefix"><code langs=""><ul class="prefixed"><li class="line" prefix="127.0.0.1:6379>">CONFIG GET databases
</li><li class="line" prefix="127.0.0.1:6379>">(error) NOAUTH Authentication required.
</li></ul></code></pre>
<p>To provide the password you set, run:</p>
<pre class="code-pre custom_prefix"><code langs=""><ul class="prefixed"><li class="line" prefix="127.0.0.1:6379>">AUTH <span class="highlight">your_redis_password</span>
</li><li class="line" prefix="127.0.0.1:6379>">OK
</li></ul></code></pre>
<p>Your can also provide you password directly on the command line when starting the redis client:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">redis-cli -a <span class="highlight">your_redis_password</span>"
</li></ul></code></pre>
<h3 id="enabling-remote-access">Enabling Remote Access</h3>

<p>In order to enable access over the internet, comment out the line beginning with <code>bind</code> in <code>/etc/redis/redis.conf</code>. Find this:</p>
<pre class="code-pre "><code langs="">bind 127.0.0.1
</code></pre>
<p>and change it to this:</p>
<pre class="code-pre "><code langs=""># bind 127.0.0.1
</code></pre>
<p>Then restart Redis to enable the change.</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo service redis restart
</li></ul></code></pre>
<p>Now you can connect to your Redis instance from a remote host using the command:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">redis-cli -h <span class="highlight">redis_ip_address</span> -p 6379 -a <span class="highlight">your_redis_password</span>
</li></ul></code></pre>
<h3 id="additional-security-steps">Additional Security Steps</h3>

<p>In addition to enabling authentication, setting up a firewall that only allows remote connections from specific IP addresses is a good security measure to implement. Managing an IP Tables firewall is <a href="https://indiareads/community/tutorials/how-to-setup-a-firewall-with-ufw-on-an-ubuntu-and-debian-cloud-server">made easy using UFW on Ubuntu</a>. The following commands will erect a firewall which allows all outgoing connections from your server but only allow incoming connections via SSH or from the specified IP address (<span class="highlight">ip.address.to.allow</span>).</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo apt-get install ufw
</li><li class="line" prefix="$">sudo ufw default deny incoming
</li><li class="line" prefix="$">sudo ufw default allow outgoing
</li><li class="line" prefix="$">sudo ufw allow ssh
</li><li class="line" prefix="$">sudo ufw allow from <span class="highlight">ip.address.to.allow</span>
</li><li class="line" prefix="$">sudo ufw enable
</li></ul></code></pre>
<p>For additional security recommendations, see <a href="http://redis.io/topics/security">Redis's security docs</a>.</p>

<h2 id="further-information">Further Information</h2>

<p>The One-Click application simply provides you with Redis as a pre-installed base. It's up to you how you want to use it. Whether you  are building out a cluster or you simply want to use it as a local cache for an app on the same host, we have a number of tutorials which should point you in the right direction:</p>

<ul>
<li><p><a href="https://indiareads/community/tutorials/how-to-configure-a-redis-cluster-on-ubuntu-14-04">How To Configure a Redis Cluster on Ubuntu 14.04</a></p></li>
<li><p><a href="https://indiareads/community/tutorials/how-to-configure-redis-caching-to-speed-up-wordpress-on-ubuntu-14-04">How To Configure Redis Caching to Speed Up WordPress on Ubuntu 14.04</a></p></li>
</ul>

    