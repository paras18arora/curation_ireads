<link rel='stylesheet' href='digital_ocean.css'><br> <br> 
      <h3 id="introduction">Introduction</h3>

<p>Redis is an in-memory key-value store known for its flexibility, performance, and wide language support.  In this guide, we will demonstrate how to install and configure Redis on an Ubuntu 16.04 server.</p>

<h2 id="prerequisites">Prerequisites</h2>

<p>To complete this guide, you will need access to an Ubuntu 16.04 server.  You will need a non-root user with <code>sudo</code> privileges to perform the administrative functions required for this process.  You can learn how to set up an account with these privileges by following our <a href="https://indiareads/community/tutorials/initial-server-setup-with-ubuntu-16-04">Ubuntu 16.04 initial server setup guide</a>.</p>

<p>When you are ready to begin, log in to your Ubuntu 16.04 server with your <code>sudo</code> user and continue below.</p>

<h2 id="install-the-build-and-test-dependencies">Install the Build and Test Dependencies</h2>

<p>In order to get the latest version of Redis, we will be compiling and installing the software from source.  Before we download the code, we need to satisfy the build dependencies so that we can compile the software.</p>

<p>To do this, we can install the <code>build-essential</code> meta-package from the Ubuntu repositories.  We will also be downloading the <code>tcl</code> package, which we can use to test our binaries.</p>

<p>We can update our local <code>apt</code> package cache and install the dependencies by typing:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo apt-get update
</li><li class="line" prefix="$">sudo apt-get install build-essential tcl
</li></ul></code></pre>
<h2 id="download-compile-and-install-redis">Download, Compile, and Install Redis</h2>

<p>Next, we can begin to build Redis.</p>

<h3 id="download-and-extract-the-source-code">Download and Extract the Source Code</h3>

<p>Since we won't need to keep the source code that we'll compile long term (we can always re-download it), we will build in the <code>/tmp</code> directory.  Let's move there now:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">cd /tmp
</li></ul></code></pre>
<p>Now, download the latest stable version of Redis.  This is always available at <a href="http://download.redis.io/redis-stable.tar.gz">a stable download URL</a>:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">curl -O http://download.redis.io/redis-stable.tar.gz
</li></ul></code></pre>
<p>Unpack the tarball by typing:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">tar xzvf redis-stable.tar.gz
</li></ul></code></pre>
<p>Move into the Redis source directory structure that was just extracted:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">cd redis-stable
</li></ul></code></pre>
<h3 id="build-and-install-redis">Build and Install Redis</h3>

<p>Now, we can compile the Redis binaries by typing:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">make
</li></ul></code></pre>
<p>After the binaries are compiled, run the test suite to make sure everything was built correctly.  You can do this by typing:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">make test
</li></ul></code></pre>
<p>This will typically take a few minutes to run.  Once it is complete, you can install the binaries onto the system by typing:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo make install
</li></ul></code></pre>
<h2 id="configure-redis">Configure Redis</h2>

<p>Now that Redis is installed, we can begin to configure it.</p>

<p>To start off, we need to create a configuration directory.  We will use the conventional <code>/etc/redis</code> directory, which can be created by typing:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo mkdir /etc/redis
</li></ul></code></pre>
<p>Now, copy over the sample Redis configuration file included in the Redis source archive:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo cp /tmp/redis-stable/redis.conf /etc/redis
</li></ul></code></pre>
<p>Next, we can open the file to adjust a few items in the configuration:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo nano /etc/redis/redis.conf
</li></ul></code></pre>
<p>In the file, find the <code>supervised</code> directive.  Currently, this is set to <code>no</code>.  Since we are running an operating system that uses the systemd init system, we can change this to <code>systemd</code>:</p>
<div class="code-label " title="/etc/redis/redis.conf">/etc/redis/redis.conf</div><pre class="code-pre "><code langs="">. . .

# If you run Redis from upstart or systemd, Redis can interact with your
# supervision tree. Options:
#   supervised no      - no supervision interaction
#   supervised upstart - signal upstart by putting Redis into SIGSTOP mode
#   supervised systemd - signal systemd by writing READY=1 to $NOTIFY_SOCKET
#   supervised auto    - detect upstart or systemd method based on
#                        UPSTART_JOB or NOTIFY_SOCKET environment variables
# Note: these supervision methods only signal "process is ready."
#       They do not enable continuous liveness pings back to your supervisor.
supervised <span class="highlight">systemd</span>

. . .
</code></pre>
<p>Next, find the <code>dir</code> directory.  This option specifies the directory that Redis will use to dump persistent data.  We need to pick a location that Redis will have write permission and that isn't viewable by normal users.</p>

<p>We will use the <code>/var/lib/redis</code> directory for this, which we will create in a moment:</p>
<div class="code-label " title="/etc/redis/redis.conf">/etc/redis/redis.conf</div><pre class="code-pre "><code langs="">. . .

# The working directory.
#
# The DB will be written inside this directory, with the filename specified
# above using the 'dbfilename' configuration directive.
#
# The Append Only File will also be created inside this directory.
#
# Note that you must specify a directory here, not a file name.
dir <span class="highlight">/var/lib/redis</span>

. . .
</code></pre>
<p>Save and close the file when you are finished.</p>

<h2 id="create-a-redis-systemd-unit-file">Create a Redis systemd Unit File</h2>

<p>Next, we can create a systemd unit file so that the init system can manage the Redis process.</p>

<p>Create and open the <code>/etc/systemd/system/redis.service</code> file to get started:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo nano /etc/systemd/system/redis.service
</li></ul></code></pre>
<p>Inside, we can begin the <code>[Unit]</code> section by adding a description and defining a requirement that networking be available before starting this service:</p>
<div class="code-label " title="/etc/systemd/system/redis.service">/etc/systemd/system/redis.service</div><pre class="code-pre "><code langs="">[Unit]
Description=Redis In-Memory Data Store
After=network.target
</code></pre>
<p>In the <code>[Service]</code> section, we need to specify the service's behavior.  For security purposes, we should not run our service as <code>root</code>.  We should use a dedicated user and group, which we will call <code>redis</code> for simplicity.  We will create these momentarily.</p>

<p>To start the service, we just need to call the <code>redis-server</code> binary, pointed at our configuration.  To stop it, we can use the Redis <code>shutdown</code> command, which can be executed with the <code>redis-cli</code> binary.  Also, since we want Redis to recover from failures when possible, we will set the <code>Restart</code> directive to "always":</p>
<div class="code-label " title="/etc/systemd/system/redis.service">/etc/systemd/system/redis.service</div><pre class="code-pre "><code langs="">[Unit]
Description=Redis In-Memory Data Store
After=network.target

<span class="highlight">[Service]</span>
<span class="highlight">User=redis</span>
<span class="highlight">Group=redis</span>
<span class="highlight">ExecStart=/usr/local/bin/redis-server /etc/redis/redis.conf</span>
<span class="highlight">ExecStop=/usr/local/bin/redis-cli shutdown</span>
<span class="highlight">Restart=always</span>
</code></pre>
<p>Finally, in the <code>[Install]</code> section, we can define the systemd target that the service should attach to if enabled (configured to start at boot):</p>
<div class="code-label " title="/etc/systemd/system/redis.service">/etc/systemd/system/redis.service</div><pre class="code-pre "><code langs="">[Unit]
Description=Redis In-Memory Data Store
After=network.target

[Service]
User=redis
Group=redis
ExecStart=/usr/local/bin/redis-server /etc/redis/redis.conf
ExecStop=/usr/local/bin/redis-cli shutdown
Restart=always

<span class="highlight">[Install]</span>
<span class="highlight">WantedBy=multi-user.target</span>
</code></pre>
<p>Save and close the file when you are finished.</p>

<h2 id="create-the-redis-user-group-and-directories">Create the Redis User, Group and Directories</h2>

<p>Now, we just have to create the user, group, and directory that we referenced in the previous two files.</p>

<p>Begin by creating the <code>redis</code> user and group.  This can be done in a single command by typing:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo adduser --system --group --no-create-home redis
</li></ul></code></pre>
<p>Now, we can create the <code>/var/lib/redis</code> directory by typing:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo mkdir /var/lib/redis
</li></ul></code></pre>
<p>We should give the <code>redis</code> user and group ownership over this directory:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo chown redis:redis /var/lib/redis
</li></ul></code></pre>
<p>Adjust the permissions so that regular users cannot access this location:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo chmod 770 /var/lib/redis
</li></ul></code></pre>
<h2 id="start-and-test-redis">Start and Test Redis</h2>

<p>Now, we are ready to start the Redis server.</p>

<h3 id="start-the-redis-service">Start the Redis Service</h3>

<p>Start up the systemd service by typing:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo systemctl start redis
</li></ul></code></pre>
<p>Check that the service had no errors by running:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo systemctl status redis
</li></ul></code></pre>
<p>You should see something that looks like this:</p>
<pre class="code-pre "><code langs=""><div class="secondary-code-label " title="Output">Output</div>● redis.service - Redis Server
   Loaded: loaded (/etc/systemd/system/redis.service; enabled; vendor preset: enabled)
   Active: <span class="highlight">active (running)</span> since Wed 2016-05-11 14:38:08 EDT; 1min 43s ago
  Process: 3115 ExecStop=/usr/local/bin/redis-cli shutdown (code=exited, status=0/SUCCESS)
 Main PID: 3124 (redis-server)
    Tasks: 3 (limit: 512)
   Memory: 864.0K
      CPU: 179ms
   CGroup: /system.slice/redis.service
           └─3124 /usr/local/bin/redis-server 127.0.0.1:6379       

. . .
</code></pre>
<h3 id="test-the-redis-instance-functionality">Test the Redis Instance Functionality</h3>

<p>To test that your service is functioning correctly, connect to the Redis server with the command-line client:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">redis-cli
</li></ul></code></pre>
<p>In the prompt that follows, test connectivity by typing:</p>
<pre class="code-pre custom_prefix"><code langs=""><ul class="prefixed"><li class="line" prefix="127.0.0.1:6379>">ping
</li></ul></code></pre>
<p>You should see:</p>
<pre class="code-pre "><code langs=""><div class="secondary-code-label " title="Output">Output</div>PONG
</code></pre>
<p>Check that you can set keys by typing:</p>
<pre class="code-pre custom_prefix"><code langs=""><ul class="prefixed"><li class="line" prefix="127.0.0.1:6379>">set test "It's working!"
</li></ul></code></pre><pre class="code-pre "><code langs=""><div class="secondary-code-label " title="Output">Output</div>OK
</code></pre>
<p>Now, retrieve the value by typing:</p>
<pre class="code-pre custom_prefix"><code langs=""><ul class="prefixed"><li class="line" prefix="127.0.0.1:6379>">get test
</li></ul></code></pre>
<p>You should be able to retrieve the value we stored:</p>
<pre class="code-pre "><code langs=""><div class="secondary-code-label " title="Output">Output</div>"It's working!"
</code></pre>
<p>Exit the Redis prompt to get back to the shell:</p>
<pre class="code-pre custom_prefix"><code langs=""><ul class="prefixed"><li class="line" prefix="127.0.0.1:6379>">exit
</li></ul></code></pre>
<p>As a final test, let's restart the Redis instance:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo systemctl restart redis
</li></ul></code></pre>
<p>Now, connect with the client again and confirm that your test value is still available:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">redis-cli
</li></ul></code></pre><pre class="code-pre custom_prefix"><code langs=""><ul class="prefixed"><li class="line" prefix="127.0.0.1:6379>">get test
</li></ul></code></pre>
<p>The value of your key should still be accessible:</p>
<pre class="code-pre "><code langs=""><div class="secondary-code-label " title="Output">Output</div>"It's working!"
</code></pre>
<p>Back out into the shell again when you are finished:</p>
<pre class="code-pre custom_prefix"><code langs=""><ul class="prefixed"><li class="line" prefix="127.0.0.1:6379>">exit
</li></ul></code></pre>
<h3 id="enable-redis-to-start-at-boot">Enable Redis to Start at Boot</h3>

<p>If all of your tests worked, and you would like to start Redis automatically when your server boots, you can enable the systemd service.</p>

<p>To do so, type:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo systemctl enable redis
</li></ul></code></pre><pre class="code-pre "><code langs=""><div class="secondary-code-label " title="Output">Output</div>Created symlink from /etc/systemd/system/multi-user.target.wants/redis.service to /etc/systemd/system/redis.service.
</code></pre>
<h2 id="conclusion">Conclusion</h2>

<p>You should now have a Redis instance installed and configured on your Ubuntu 16.04 server.  To learn more about how to secure your Redis installation, take a look at our <a href="https://indiareads/community/tutorials/how-to-secure-your-redis-installation-on-ubuntu-14-04">How To Secure Your Redis Installation on Ubuntu 14.04</a> (from step 3 onward).  Although it was written with Ubuntu 14.04 in mind, it should mostly work for 16.04 as well.</p>

    