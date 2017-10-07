<link rel='stylesheet' href='digital_ocean.css'><br> <br> 
      <h3 id="introduction">Introduction</h3>

<p>Redis is an in-memory, NoSQL, key-value cache and store that can also be persisted to disk.</p>

<p>This tutorial shows how to implement basic security for a Redis server.</p>

<p>However, keep in mind that Redis was designed for use by <em>trusted clients</em> in a <em>trusted environment</em>, with no robust security features of its own. To underscore that point, here's a quote from the <a href="http://redis.io/topics/security">official Redis website</a>:</p>

<blockquote>
<p>Redis is designed to be accessed by trusted clients inside trusted environments. This means that usually it is not a good idea to expose the Redis instance directly to the internet or, in general, to an environment where untrusted clients can directly access the Redis TCP port or UNIX socket.</p>

<p>. . .</p>

<p>In general, Redis is not optimized for maximum security but for maximum performance and simplicity.</p>
</blockquote>

<p>Performance and simplicity without security is a recipe for disaster. Even the few security features Redis has are really nothing to rave about. Those include: a basic unencrypted password, and command renaming and disabling. It lacks a true access control system.</p>

<p>However, configuring the existing security features is still a big step up from leaving your database unsecured.</p>

<p>In this tutorial, you'll read how to configure the few security features Redis has, and a few other system security features that will boost the security posture of a standalone Redis installation on Ubuntu 14.04.</p>

<p>Note that this guide does not address situations where the Redis server and the client applications are on different hosts or in different data centers. Installations where Redis traffic has to traverse an insecure or untrusted network require an entirely different set of configurations, such as setting up an SSL proxy or a <a href="https://indiareads/community/tutorials/how-to-set-up-an-openvpn-server-on-ubuntu-14-04">VPN</a> between the Redis machines, in addition to the ones given here.</p>

<h2 id="prerequisites">Prerequisites</h2>

<p>For this tutorial, you'll need:</p>

<ul>
<li><p>An Ubuntu 14.04 server with a sudo user added, from the <a href="https://indiareads/community/tutorials/initial-server-setup-with-ubuntu-14-04">initial server setup</a></p></li>
<li><p>iptables configured using <a href="https://indiareads/community/tutorials/how-to-implement-a-basic-firewall-template-with-iptables-on-ubuntu-14-04">this iptables guide</a>, up through the <strong>(Optional) Update Nameservers</strong> step (if you don't do the nameserver configuration part, APT won't work). After configuring the nameservers, you're done</p></li>
<li><p>Redis installed and working using instructions from the master-only installation from <a href="https://indiareads/community/tutorials/how-to-configure-a-redis-cluster-on-ubuntu-14-04">this Redis guide</a>, up through the <strong>Step 2 — Configure Redis Master</strong> step</p></li>
</ul>

<h2 id="step-1-—-verifying-that-redis-is-running">Step 1 — Verifying that Redis is Running</h2>

<p>First log in to your server using SSH:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">ssh <span class="highlight">username</span>@<span class="highlight">server-ip-address</span>
</li></ul></code></pre>
<p>To check that Redis is working, use the Redis command line. The <code>redis-cli</code> command is used to access the Redis command line.</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">redis-cli
</li></ul></code></pre>
<span class="note"><p>
If you already set a password for Redis, you have to <code>auth</code> after connecting.</p>
<pre class="code-pre custom_prefix"><code langs=""><ul class="prefixed"><li class="line" prefix="127.0.0.1:6379>">auth <span class="highlight">your_redis_password</span>
</li></ul></code></pre><div class="code-label " title="Output">Output</div><pre class="code-pre "><code langs="">OK
</code></pre>
<p></p></span>

<p>Test the database server:</p>
<pre class="code-pre custom_prefix"><code langs=""><ul class="prefixed"><li class="line" prefix="127.0.0.1:6379>">ping
</li></ul></code></pre>
<p>Response:</p>
<div class="code-label " title="Output">Output</div><pre class="code-pre "><code langs="">PONG
</code></pre>
<p>Exit:</p>
<pre class="code-pre custom_prefix"><code langs=""><ul class="prefixed"><li class="line" prefix="127.0.0.1:6379>">quit
</li></ul></code></pre>
<h2 id="step-2-—-securing-the-server-with-iptables">Step 2 — Securing the Server with iptables</h2>

<p>If you followed the prerequisites for iptables, feel free to skip this step. Or, you can do it now.</p>

<p>Redis is just an application that's running on your server, and because it has no real security features of its own, the first step to truly securing it is to first secure the server it is running on.</p>

<p>In the case of a public-facing server like your Ubuntu 14.04 server, configuring a firewall as given in <a href="https://indiareads/community/tutorials/how-to-implement-a-basic-firewall-template-with-iptables-on-ubuntu-14-04">this iptables guide</a> is that first step. <strong>Follow that link and set up your firewall now.</strong></p>

<p>If you've implemented the firewall rules using that guide, then you do not need to add an extra rule for Redis, because by default, all incoming traffic is dropped unless explicitly allowed. Since a default standalone installation of Redis server is listening only on the loopback interface (127.0.0.1 or localhost), there should be no concern for incoming traffic on its default port.</p>

<p>If you need to specifically allow an IP address for Redis, you can check what IP address Redis is listening on, and what port it is bound to by <code>grep</code>-ing the output of the <code>netstat</code> command. The fourth column — <strong>127.0.0.1:6379</strong> here — indicates the IP address and port combination associated with Redis:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo netstat -plunt | grep -i redis
</li></ul></code></pre><div class="code-label " title="Output">Output</div><pre class="code-pre "><code langs="">tcp   0      0 127.0.0.1:6379          0.0.0.0:*               LISTEN      8562/redis-server 1
</code></pre>
<p>Make sure this IP address is allowed in your Firewall policy. For more information on how to add rules, please see this <a href="https://indiareads/community/tutorials/iptables-essentials-common-firewall-rules-and-commands">iptables basics article</a>.</p>

<h2 id="step-3-—-binding-to-localhost">Step 3 — Binding to localhost</h2>

<p>By default, Redis server is only accessible from localhost. However, if you followed the tutorial to set up a Redis master server, you updated the configuration file to allow connections from anywhere. This is not as secure as binding to localhost.</p>

<p>Open the Redis configuration file for editing:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo nano /etc/redis/redis.conf
</li></ul></code></pre>
<p>Locate this line and make sure it is uncommented (remove the <code>#</code> if it exists):</p>
<div class="code-label " title="/etc/redis/redis.conf">/etc/redis/redis.conf</div><pre class="code-pre "><code langs="">bind 127.0.0.1
</code></pre>
<p>We'll keep using this file, so keep it open for now.</p>

<h2 id="step-4-—-configuring-a-redis-password">Step 4 — Configuring a Redis Password</h2>

<p>If you installed Redis using the <a href="https://indiareads/community/tutorials/how-to-configure-a-redis-cluster-on-ubuntu-14-04">How To Configure a Redis Cluster on Ubuntu 14.04</a> article, you should have configured a password for it. At your discretion, you can make a more secure password now by following this section. If not, instructions in this section show how to set the database server password.</p>

<p>Configuring a Redis password enables one of its two built-in security feature - the <code>auth</code> command, which requires clients to authenticate to access the database. The password is configured directly in Redis's configuration file, <code>/etc/redis/redis.conf</code>, which you should still have open from the previous step.</p>

<p>Scroll to the <code>SECURITY</code> section and look for a commented directive that reads:</p>
<div class="code-label " title="/etc/redis/redis.conf">/etc/redis/redis.conf</div><pre class="code-pre "><code langs=""># requirepass foobared
</code></pre>
<p>Uncomment it by removing the <code>#</code>, and change <code>foobared</code> to a very strong and very long value.</p>

<p>Rather than make up a password yourself, you may use a tool like <code>apg</code> or <code>pwgen</code> to generate one. If you don't want to install an application just to generate a password, you may use the one-liner below. To generate a password different from the one that this would generate, change the word in quotes.</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">echo "<span class="highlight">digital-ocean</span>" | sha256sum
</li></ul></code></pre>
<p>Your output should look something like:</p>
<div class="code-label " title="Output">Output</div><pre class="code-pre "><code langs="">960c3dac4fa81b4204779fd16ad7c954f95942876b9c4fb1a255667a9dbe389d
</code></pre>
<p>Though the generated password will not be pronounceable, it gives you a very strong and very long one, which is exactly the type of password required for Redis. After copying and pasting the output of that command as the new value for <code>requirepass</code>, it should read:</p>
<div class="code-label " title="/etc/redis/redis.conf">/etc/redis/redis.conf</div><pre class="code-pre "><code langs="">requirepass <span class="highlight">960c3dac4fa81b4204779fd16ad7c954f95942876b9c4fb1a255667a9dbe389d</span>
</code></pre>
<p>If you prefer a shorter password, use the output of the command below instead. Again, change the word in quotes so it will not generate the same password as this one:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">echo "<span class="highlight">digital-ocean</span>" | sha1sum
</li></ul></code></pre>
<p>You'll get somewhat shorter output this time:</p>
<div class="code-label " title="Output">Output</div><pre class="code-pre "><code langs="">10d9a99851a411cdae8c3fa09d7290df192441a9
</code></pre>
<p>After setting the password, save the file, and restart Redis:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo service redis-server restart
</li></ul></code></pre>
<p>To test that the password works, access the Redis command line:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">redis-cli
</li></ul></code></pre>
<p>The following output shows a sequence of commands used to test whether the Redis password works. The first command tries to set a key to a value before authentication.</p>
<pre class="code-pre custom_prefix"><code langs=""><ul class="prefixed"><li class="line" prefix="127.0.0.1:6379>">set key1 10
</li></ul></code></pre>
<p>That won't work, so Redis returns an error.</p>
<div class="code-label " title="Output">Output</div><pre class="code-pre "><code langs="">(error) NOAUTH Authentication required.
</code></pre>
<p>The second command authenticates with the password specified in the Redis configuration file.</p>
<pre class="code-pre custom_prefix"><code langs=""><ul class="prefixed"><li class="line" prefix="127.0.0.1:6379>">auth <span class="highlight">your_redis_password</span>
</li></ul></code></pre>
<p>Redis acknowledges.</p>
<div class="code-label " title="Output">Output</div><pre class="code-pre "><code langs="">OK
</code></pre>
<p>After that, re-running the previous command succeeds.</p>
<pre class="code-pre custom_prefix"><code langs=""><ul class="prefixed"><li class="line" prefix="127.0.0.1:6379>">set key1 10
</li></ul></code></pre><div class="code-label " title="Output">Output</div><pre class="code-pre "><code langs="">OK
</code></pre>
<p><code>get key1</code> queries Redis for the value of the new key.</p>
<pre class="code-pre custom_prefix"><code langs=""><ul class="prefixed"><li class="line" prefix="127.0.0.1:6379>">get key1
</li></ul></code></pre><div class="code-label " title="Output">Output</div><pre class="code-pre "><code langs="">"10"
</code></pre>
<p>The last command exits <code>redis-cli</code>. You may also use <code>exit</code>:</p>
<pre class="code-pre custom_prefix"><code langs=""><ul class="prefixed"><li class="line" prefix="127.0.0.1:6379>">quit
</li></ul></code></pre>
<p>Next, we'll look at renaming Redis commands.</p>

<h2 id="step-5-—-renaming-dangerous-commands">Step 5 — Renaming Dangerous Commands</h2>

<p>The other security feature built into Redis allows you to rename or completely disable certain commands that are considered dangerous.</p>

<p>When run by unauthorized users, such commands can be used to reconfigure, destroy, or otherwise wipe your data. Like the authentication password, renaming or disabling commands is configured in the same <code>SECURITY</code> section of the <code>/etc/redis/redis.conf</code> file.</p>

<p>Some of the commands that are known to be dangerous include: <strong>FLUSHDB</strong>, <strong>FLUSHALL</strong>, <strong>KEYS</strong>, <strong>PEXPIRE</strong>, <strong>DEL</strong>, <strong>CONFIG</strong>, <strong>SHUTDOWN</strong>, <strong>BGREWRITEAOF</strong>, <strong>BGSAVE</strong>, <strong>SAVE</strong>, <strong>SPOP</strong>, <strong>SREM</strong>, <strong>RENAME</strong> and <strong>DEBUG</strong>. That's not a comprehensive list, but renaming or disabling all of the commands in that list is a good starting point.</p>

<p>Whether you disable or rename a command is site-specific. If you know you will never use a command that can be abused, then you may disable it. Otherwise, rename it.</p>

<p>To enable or disable Redis commands, open the configuration file for editing one more time:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo nano  /etc/redis/redis.conf
</li></ul></code></pre>
<p><strong>These are examples. You should choose to disable or rename the commands that make sense for you.</strong> You can check the commands for yourself and determine how they might be misused at <a href="http://redis.io/commands">redis.io/commands</a>.</p>

<p>To disable or kill a command, simply rename it to an empty string, as shown below:</p>
<div class="code-label " title="/etc/redis/redis.conf">/etc/redis/redis.conf</div><pre class="code-pre "><code langs=""># It is also possible to completely kill a command by renaming it into
# an empty string:
#
<span class="highlight">rename-command FLUSHDB ""</span>
<span class="highlight">rename-command FLUSHALL ""</span>
<span class="highlight">rename-command DEBUG ""</span>
</code></pre>
<p>And to rename a command, give it another name, as in the examples below. Renamed commands should be difficult for others to guess, but easy for you to remember. Don't make life difficult for yourself.</p>
<div class="code-label " title="/etc/redis/redis.conf">/etc/redis/redis.conf</div><pre class="code-pre "><code langs="">rename-command CONFIG ""
<span class="highlight">rename-command SHUTDOWN SHUTDOWN_MENOT</span>
<span class="highlight">rename-command CONFIG ASC12_CONFIG</span>
</code></pre>
<p>Save your changes.</p>

<p>After renaming a command, apply the change by restarting Redis:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo service redis-server restart
</li></ul></code></pre>
<p>To test the new command, enter the Redis command line:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">redis-cli
</li></ul></code></pre>
<p>Then, assuming that you renamed the <strong>CONFIG</strong> command to <strong>ASC12_CONFIG</strong>, the following output shows how to test that the new command has been applied.</p>

<p>After authenticating:</p>
<pre class="code-pre custom_prefix"><code langs=""><ul class="prefixed"><li class="line" prefix="127.0.0.1:6379>">auth <span class="highlight">your_redis_password</span>
</li></ul></code></pre><div class="code-label " title="Output">Output</div><pre class="code-pre "><code langs="">OK
</code></pre>
<p>The first attempt to use the <code>config</code> command should fail, because it has been renamed.</p>
<pre class="code-pre custom_prefix"><code langs=""><ul class="prefixed"><li class="line" prefix="127.0.0.1:6379>">config get requirepass
</li></ul></code></pre><div class="code-label " title="Output">Output</div><pre class="code-pre "><code langs="">(error) ERR unknown command 'config'
</code></pre>
<p>Calling the renamed command should be successful (it's case-insensitive):</p>
<pre class="code-pre custom_prefix"><code langs=""><ul class="prefixed"><li class="line" prefix="127.0.0.1:6379>">asc12_config get requirepass
</li></ul></code></pre><div class="code-label " title="Output">Output</div><pre class="code-pre "><code langs="">1) "requirepass"
2) "your_redis_password"
</code></pre>
<p>Finally, you can exit from <code>redis-cli</code>:</p>
<pre class="code-pre custom_prefix"><code langs=""><ul class="prefixed"><li class="line" prefix="127.0.0.1:6379>">exit
</li></ul></code></pre>
<p>Note: If you're already using the Redis command line and then restart Redis, you'll need to re-authenticate. Otherwise, you'll get this error if you type a command:</p>
<div class="code-label " title="Output">Output</div><pre class="code-pre "><code langs="">NOAUTH Authentication required.
</code></pre>
<span class="warning"><p>
Regarding renaming commands, there's a cautionary statement at the end of the <code>SECURITY</code> section in <code>/etc/redis/redis.conf</code> which reads:</p>

<blockquote>
<p><code>Please note that changing the name of commands that are logged into the AOF file or transmitted to slaves may cause problems.</code></p>
</blockquote>

<p>That means if the renamed command is not in the AOF file, or if it is but the AOF file has not beeen transmitted to slaves, then there should be no problem.</p>

<p>So, keep that in mind when you're trying to rename commands. The best time to rename a command is when you're not using AOF persistence, or right after installation, that is, before your Redis-using application has been deployed.</p>

<p>When you're using AOF and dealing with a master-slave installation, consider this answer from the project's GitHub issue page. The following is a reply to the author's question:</p>

<blockquote>
<p>The commands are logged to the AOF and replicated to the slave the same way they are sent, so if you try to replay the AOF on an instance that doesn't have the same renaming, you may face inconsistencies as the command cannot be executed (same for slaves).</p>
</blockquote>

<p>So, the best way to handle renaming in cases like that is to make sure that renamed commands are applied to all instances in master-slave installations. <br /></p></span>

<h2 id="step-6-—-setting-data-directory-ownership-and-file-permissions">Step 6 — Setting Data Directory Ownership and File Permissions</h2>

<p>In this step, we'll consider a couple of ownership and permissions changes you can make to improve the security profile of your Redis installation. This involves making sure that only the user that needs to access Redis has permission to read its data. That user is, by default, the <strong>redis</strong> user.</p>

<p>You can verify this by <code>grep</code>-ing for the Redis data directory in a long listing of its parent directory. The command and its output are given below.</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">ls -l /var/lib | grep redis
</li></ul></code></pre><div class="code-label " title="Output">Output</div><pre class="code-pre "><code langs=""><span class="highlight">drwxr-xr-x</span> 2 <span class="highlight">redis   redis</span>   4096 Aug  6 09:32 redis
</code></pre>
<p>You can see that the Redis data directory is owned by the <strong>redis</strong> user, with secondary access granted to the <strong>redis</strong> group. That part is good.</p>

<p>The part that's not is the folder's permissions, which is 755. To ensure that only the Redis user has access to the folder and its contents, change the permission to 700:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo chmod 700 /var/lib/redis
</li></ul></code></pre>
<p>The other permission you should change is that of the Redis configuration file. By default, it has a file permission of 644 and is owned by <strong>root</strong>, with secondary ownership by the <strong>root</strong> group:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">ls -l /etc/redis/redis.conf
</li></ul></code></pre><div class="code-label " title="Output">Output</div><pre class="code-pre "><code langs=""><span class="highlight">-rw-r--r--</span> 1 <span class="highlight">root root</span> 30176 Jan 14  2014 /etc/redis/redis.conf
</code></pre>
<p>That permission (644) is world-readable, which is not a good idea, because it contains the unencrypted password configured in Step 4.</p>

<p>We need to change the ownership and permissions. Ideally, it should be owned by the <strong>redis</strong> user, with secondary ownership by the <strong>root</strong> user. To do that, run the following command:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo chown redis:root /etc/redis/redis.conf
</li></ul></code></pre>
<p>Then change the ownership so that only the owner of the file can read and/or write to it:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo chmod 600 /etc/redis/redis.conf
</li></ul></code></pre>
<p>You may verify the new ownership and permission using:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">ls -l /etc/redis/redis.conf
</li></ul></code></pre><div class="code-label " title="Output">Output</div><pre class="code-pre "><code langs="">total 40
<span class="highlight">-rw-------</span> 1 <span class="highlight">redis root</span> 29716 Sep 22 18:32 /etc/redis/redis.conf
</code></pre>
<p>Finally, restart Redis:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo service redis-server restart
</li></ul></code></pre>
<h2 id="conclusion">Conclusion</h2>

<p>Keep in mind that once someone is logged in to your server, it's very easy to circumvent the Redis-specific security features we've put in place. So, the most important security feature is one that makes it extremely difficult to jump that fence.</p>

<p>That should be your firewall.</p>

<p>To take your server security to the next level, you could configure an intrusion detection system like OSSEC. To configure OSSEC on Ubuntu 14.04, see <a href="https://indiareads/community/tutorials/how-to-install-and-configure-ossec-security-notifications-on-ubuntu-14-04">this OSSEC guide</a>.</p>

<p>If you're attempting to secure Redis communication across an untrusted network you'll have to employ an SSL proxy, as recommeded by Redis developers in the <a href="http://redis.io/topics/security">official Redis security guide</a>. Setting up an SSL proxy to secure Redis commmunication is a separate topic.</p>

<p>We didn't include a full list of Redis commands in the renaming section.  However, you can check this for yourself and determine how they might be misused at <a href="http://redis.io/commands">redis.io/commands</a>.</p>

    