<link rel='stylesheet' href='digital_ocean.css'><br> <br> 
      <h3 id="introduction">Introduction</h3>

<p>Riak is a noSQL, non-relational datastore with a focus on distributed database architecture. With the newest Riak 2.1.1 edition, all data can be made <em>strongly consistent</em>, in which data is up-to-date upon retrieval, as opposed to <em>eventually consistent</em>, in which data is more accessible but not up-to-date.</p>

<p>Riak is one choice in a family of key-value noSQL implementations, with competitors including Redis, MemcacheDB, and Aerospike. As a key-value database, it consequently is not optimized for SQL-esque queries that grab an entire dataset.</p>

<h2 id="prerequisites">Prerequisites</h2>

<p>To follow this tutorial, you will need:</p>

<ul>
<li><p>One fresh Ubuntu 14.04 Droplet</p></li>
<li><p>A sudo non-root user, which you can setup by following steps 2 and 3 of <a href="https://indiareads/community/tutorials/how-to-add-and-delete-users-on-an-ubuntu-14-04-vps">this tutorial</a></p></li>
</ul>

<h2 id="step-1-—-installing-riak">Step 1 — Installing Riak</h2>

<p>In this section, we will install Riak itself.</p>

<p>With the release of 2.0, Riak has migrated its packages from a self-hosted apt repository to the packagecloud.io service, so we'll need to populate the <code>apt</code> index with Riak. Luckily, Riak provides a custom script which does just that.</p>

<p>First, we'll download the script.</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">curl -O https://packagecloud.io/install/repositories/basho/riak/script.deb.sh
</li></ul></code></pre>
<p>Instead of directly executing it, first open the script to verify that it contains what we expect.</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">less script.deb.sh
</li></ul></code></pre>
<p>To fetch packages over HTTPS, the script needs to install <code>apt-transport-https</code> package. It also checks for a Certificate Authority, imports a public key, and updates your package index.</p>

<p>Press <code>q</code> to close the file, then execute the script.</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo bash script.deb.sh
</li></ul></code></pre>
<p>Finally, install Riak.</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo apt-get install riak=2.1.1-1
</li></ul></code></pre>
<h2 id="step-2-—-configuring-and-launching-riak">Step 2 — Configuring and Launching Riak</h2>

<p>In this section, we will configure and launch a Riak node.</p>

<p>To start, we will need to optimize Riak's Erlang VM with some recommended settings. We will make two modifications: setting queue scan intervals and disabling scheduler compaction of load.</p>

<p>Open the new Riak 2.0's configuration file using <code>nano</code> or your favorite text editor.</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo nano /etc/riak/riak.conf
</li></ul></code></pre>
<p>Uncomment the <code>erlang.schedulers.force_wakeup_interval = 500</code> line, highlighted below. Make sure that the leading space is also deleted, so that the <code>e</code> is the first character of the line.</p>
<div class="code-label " title="/etc/riak/riak.conf">/etc/riak/riak.conf</div><pre class="code-pre "><code langs="">. . .

## Set scheduler forced wakeup interval. All run queues will be
## scanned each Interval milliseconds. While there are sleeping
## schedulers in the system, one scheduler will be woken for each
...
## Default: 500
##
## Acceptable values:
##   - an integer
<span class="highlight">## erlang.schedulers.force_wakeup_interval = 500</span>

. . .
</code></pre>
<p>Repeat this process for the <code>erlang.schedulers.compaction_of_load = false</code> in the block directly after:</p>
<div class="code-label " title="/etc/riak/riak.conf">/etc/riak/riak.conf</div><pre class="code-pre "><code langs="">. . .

## Enable or disable scheduler compaction of load. By default
## scheduler compaction of load is enabled. When enabled, load
## balancing will strive for a load distribution which causes as many
...
## Default: false
##
## Acceptable values:
##   - one of: true, false
<span class="highlight">## erlang.schedulers.compaction_of_load = false</span>

. . .
</code></pre>
<p>Save and exit the file.</p>

<p>To start a Riak node, run:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo riak start
</li></ul></code></pre>
<p>You will see the following.</p>
<div class="code-label " title="Output">Output</div><pre class="code-pre "><code langs="">!!!!
!!!! WARNING: ulimit -n is 1024; 65536 is the recommended minimum.
!!!!
</code></pre>
<p>The above message warns that our system has a low open file limit, which restricts the number of open file handles at any given moment. Think of each handle as a writing tool that we own. Every computer process requires a writing tool, to write</p>

<p>By default, the system's limit on available writing tools is 1024; Riak recommends raising that limit to 65536. To raise this limit, see <a href="http://docs.basho.com/riak/latest/ops/tuning/open-files-limit/">official Riak Open Files Limit documentation</a>.</p>

<p>To double-check that your node is running, use the following.</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo riak ping
</li></ul></code></pre>
<p>The command will output <code>pong</code> if the node is running and will return an error otherwise.</p>

<p>To run a sequence of pre-built Riak tests, run:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo riak-admin test
</li></ul></code></pre>
<p>This above command will output the following.</p>
<div class="code-label " title="Output">Output</div><pre class="code-pre "><code langs="">Successfully completed 1 read/write cycle to 'riak@127.0.0.1'
</code></pre>
<p>Your Riak node is now up and running.</p>

<h2 id="step-3-—-building-an-example-python-applcation-optional">Step 3 — Building an Example Python Applcation (Optional)</h2>

<p>The following is an optional series of steps that setup a sample Python-Riak application. The above instructions are language-agnostic and do not depend on the following to function normally. If you are not interested in a sample Python application, you can skip down to the Conclusion section.</p>

<p>First, check your current Python version.</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">python --version
</li></ul></code></pre>
<p>You should see the output:</p>
<pre class="code-pre "><code langs=""><div class="secondary-code-label " title="Output">Output</div>Python 2.7.6
</code></pre>
<p>We would like to have <code>python</code> run Python 3. So, let's remove the old binary.</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo rm /usr/bin/python
</li></ul></code></pre>
<p>Next, create a symbolic link to the Python 3 binary in its place.</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo ln -s /usr/bin/python3 /usr/bin/python
</li></ul></code></pre>
<p>If you run <code>python --version</code> again now, you'll see the output <code>Python 3.4.0</code>.</p>

<p>Next, we will install Pip, the recommended package installer for Python packages. Pip allows us to easily manage any Python3 package we would like to have. For an overview of Pip, you can read out <a href="https://indiareads/community/tutorials/common-python-tools-using-virtualenv-installing-with-pip-and-managing-packages" title="Installing with Pip and Virtualenv">this tutorial</a>.</p>

<p>To install it, simply run the following:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo apt-get install python3-pip
</li></ul></code></pre>
<p>Now, we need to install the Python-Riak client. Several dependencies need to be satisfied first:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo apt-get install python3-dev libffi-dev libssl-dev
</li></ul></code></pre>
<p>Install the client.</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo pip3 install riak
</li></ul></code></pre>
<p>Finally, we will code a sample application to test the Python-Riak combination. Create a new folder to house the application and create a new file in it.</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">mkdir ~/test
</li><li class="line" prefix="$">sudo nano ~/test/app.py
</li></ul></code></pre>
<p>Paste the following inside.  This is sample code from the <a href="http://docs.basho.com/riak/latest/dev/taste-of-riak/python/">official Riak documentation</a>.</p>
<div class="code-label " title="~/test/app.py">~/test/app.py</div><pre class="code-pre "><code langs="">import riak

# connect to Riak
myClient = riak.RiakClient(pb_port=8087, protocol='pbc')

# create new Bucket
myBucket = myClient.bucket('test')

# store key-value pairs
val1 = 1
key1 = myBucket.new('one', data=val1)
key1.store()

val2 = "two"
key2 = myBucket.new('two', data=val2)
key2.store()

val3 = {"myValue": 3}
key3 = myBucket.new('three', data=val3)
key3.store()

# fetch the data
fetched1 = myBucket.get('one')
fetched2 = myBucket.get('two')
fetched3 = myBucket.get('three')

print('Value 1 correct: '+str(val1 == fetched1.data))
print('Value 2 correct: '+str(val2 == fetched2.data))
print('Value 3 correct: '+str(val3 == fetched3.data))
</code></pre>
<p>Now, run the following to test this application.</p>
<pre class="code-pre "><code langs="">python ~/test/app.py
</code></pre>
<p>It will output the following warning, but this can be disregarded.</p>
<pre class="code-pre "><code langs=""><div class="secondary-code-label " title="Python application warning">Python application warning</div>/usr/local/lib/python3.4/dist-packages/riak/security.py:54: UserWarning: Found OpenSSL 1.0.1f 6 Jan 2014 version, but expected at least OpenSSL 1.0.1g.  Security may not support TLS 1.2.
  warnings.warn(msg, UserWarning)
</code></pre>
<p>Transport Layer Security (TLS) 1.2 simply a tighter security protocol built on top of TLS 1.1, and TLS in turn is generally an upgrade from SSL. However, Internet Explorer does not universally support TLS 1.1 and 1.2, and TLS 1.2 is disabled in early versions of all popular browsers. As a consequence, we can settle for SSL to govern connections between the application and the Riak datastore safely.</p>

<p>It should output the following:</p>
<div class="code-label " title="output">output</div><pre class="code-pre "><code langs="">Value 1 correct: True
Value 2 correct: True
Value 3 correct: True
</code></pre>
<p>That's it!</p>

<h2 id="conclusion">Conclusion</h2>

<p>You have now configured Riak 2 and successfully connected it to Python3. This Riak 2 installation is not specific to Python, however, and may be easily adapted to other languages. For more information on securing Riak, see official <a href="http://docs.basho.com/riak/latest/ops/running/authz/">Riak 2 recommendations</a>.</p>

    