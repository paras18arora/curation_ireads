<link rel='stylesheet' href='digital_ocean.css'><br> <br> 
      <h3 id="introduction">Introduction</h3>

<p>Cassandra, or Apache Cassandra, is a highly scalable open source NoSQL database system, achieving great performance on multi-node setups.</p>

<p>In this tutorial, you’ll learn how to install and use it to run a single-node cluster on Ubuntu 14.04.</p>

<h2 id="prerequisite">Prerequisite</h2>

<p>To complete this tutorial, you will need the following:</p>

<ul>
<li>Ubuntu 14.04 Droplet</li>
<li>A non-root user with sudo privileges (<a href="https://indiareads/community/tutorials/initial-server-setup-with-ubuntu-14-04">Initial Server Setup with Ubuntu 14.04</a> explains how to set this up.)</li>
</ul>

<h2 id="step-1-—-installing-the-oracle-java-virtual-machine">Step 1 — Installing the Oracle Java Virtual Machine</h2>

<p>Cassandra requires that the Oracle Java SE Runtime Environment (JRE) be installed. So, in this step, you'll install and verify that it's the default JRE.</p>

<p>To make the Oracle JRE package available, you'll have to add a Personal Package Archives (PPA) using this command:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo add-apt-repository ppa:webupd8team/java
</li></ul></code></pre>
<p>Update the package database:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo apt-get update
</li></ul></code></pre>
<p>Then install the Oracle JRE. Installing this particular package not only installs it but also makes it the default JRE. When prompted, accept the license agreement:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo apt-get install oracle-java8-set-default
</li></ul></code></pre>
<p>After installing it, verify that it's now the default JRE:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">java -version
</li></ul></code></pre>
<p>You should see output similar to the following:</p>
<pre class="code-pre "><code langs=""><div class="secondary-code-label " title="Output">Output</div>java version "1.8.0_60"
Java(TM) SE Runtime Environment (build 1.8.0_60-b27)
Java HotSpot(TM) 64-Bit Server VM (build 25.60-b23, mixed mode)
</code></pre>
<h2 id="step-2-—-installing-cassandra">Step 2  — Installing Cassandra</h2>

<p>We'll install Cassandra using packages from the official Apache Software Foundation repositories, so start by adding the repo so that the packages are available to your system. Note that Cassandra 2.2.2 is the latest version at the time of this publication. Change the <code>22x</code> to match the latest version. For example, use <code>23x</code> if Cassandra 2.3 is the latest version:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">echo "deb http://www.apache.org/dist/cassandra/debian 22x main" | sudo tee -a /etc/apt/sources.list.d/cassandra.sources.list
</li></ul></code></pre>
<p>The add the repo's source:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">echo "deb-src http://www.apache.org/dist/cassandra/debian 22x main" | sudo tee -a /etc/apt/sources.list.d/cassandra.sources.list
</li></ul></code></pre>
<p>To avoid package signature warnings during package updates, we need to add three public keys from the Apache Software Foundation associated with the package repositories.</p>

<p>Add the first one using this pair of commands, which must be run one after the other:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">gpg --keyserver pgp.mit.edu --recv-keys F758CE318D77295D
</li><li class="line" prefix="$">gpg --export --armor F758CE318D77295D | sudo apt-key add -
</li></ul></code></pre>
<p>Then add the second key:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">gpg --keyserver pgp.mit.edu --recv-keys 2B5C1B00
</li><li class="line" prefix="$">gpg --export --armor 2B5C1B00 | sudo apt-key add -
</li></ul></code></pre>
<p>Then add the third:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">gpg --keyserver pgp.mit.edu --recv-keys 0353B12C
</li><li class="line" prefix="$">gpg --export --armor 0353B12C | sudo apt-key add -
</li></ul></code></pre>
<p>Update the package database once again:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo apt-get update
</li></ul></code></pre>
<p>Finally, install Cassandra:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo apt-get install cassandra
</li></ul></code></pre>
<h2 id="step-3-—-troubleshooting-and-starting-cassandra">Step 3 — Troubleshooting and Starting Cassandra</h2>

<p>Ordinarily, Cassandra should have been started automatically at this point. However, because of a bug, it does not. To confirm that it's not running, type:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo service cassandra status
</li></ul></code></pre>
<p>If it is not running, the following output will be displayed:</p>
<pre class="code-pre "><code langs=""><div class="secondary-code-label " title="Output">Output</div>* could not access pidfile for Cassandra
</code></pre>
<p>This is a well-known issue with the latest versions of Cassandra on Ubuntu. We'll try a few fixes. First, start by editing its init script. The parameter we're going to modify is on line 60 of that script, so open it using:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo nano +60 /etc/init.d/cassandra
</li></ul></code></pre>
<p>That line should read:</p>
<pre class="code-pre "><code langs=""><div class="secondary-code-label " title="/etc/init.d/cassandra">/etc/init.d/cassandra</div>CMD_PATT="<span class="highlight">cassandra.+CassandraDaemon</span>"
</code></pre>
<p>Change it to:</p>
<pre class="code-pre "><code langs=""><div class="secondary-code-label " title="/etc/init.d/cassandra">/etc/init.d/cassandra</div>
CMD_PATT="<span class="highlight">cassandra</span>"
</code></pre>
<p>Close and save the file, then reboot the server:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo reboot
</li></ul></code></pre>
<p>Or:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo shutdown -r now
</li></ul></code></pre>
<p>After logging back in, Cassandra should now be running. Verify:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo service cassandra status
</li></ul></code></pre>
<p>If you are successful, you will see:</p>
<pre class="code-pre "><code langs=""><div class="secondary-code-label " title="Output">Output</div>* Cassandra is running
</code></pre>
<h2 id="step-4-—-connecting-to-the-cluster">Step 4 — Connecting to the Cluster</h2>

<p>If you were able to successfully start Cassandra, check the status of the cluster:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo nodetool status
</li></ul></code></pre>
<p>In the output, <strong>UN</strong> means it's <strong>U</strong>p and <strong>N</strong>ormal:</p>
<pre class="code-pre "><code langs=""><div class="secondary-code-label " title="Output">Output</div>Datacenter: datacenter1
=======================
Status=Up/Down
|/ State=Normal/Leaving/Joining/Moving
--  Address    Load       Tokens       Owns    Host ID                               Rack
<span class="highlight">UN</span>  127.0.0.1  142.02 KB  256          ?       2053956d-7461-41e6-8dd2-0af59436f736  rack1

Note: Non-system keyspaces don't have the same replication settings, effective ownership information is meaningless
</code></pre>
<p>Then connect to it using its interactive command line interface <code>cqlsh</code>.</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">cqlsh
</li></ul></code></pre>
<p>You will see it connect:</p>
<pre class="code-pre "><code langs=""><div class="secondary-code-label " title="Output">Output</div>Connected to Test Cluster at 127.0.0.1:9042.
[cqlsh 5.0.1 | Cassandra 2.2.2 | CQL spec 3.3.1 | Native protocol v4]
Use HELP for help.
cqlsh>
</code></pre>
<p>Type <code>exit</code> to quit:</p>
<pre class="code-pre custom_prefix"><code langs=""><ul class="prefixed"><li class="line" prefix="cqlsh>">exit
</li></ul></code></pre>
<h2 id="conclusion">Conclusion</h2>

<p>Congratulations! You now have a single-node Cassandra cluster running on Ubuntu 14.04. More information about Cassandra is available at the <a href="http://wiki.apache.org/cassandra/GettingStarted">project's website</a>.</p>

    