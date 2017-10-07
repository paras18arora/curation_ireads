<link rel='stylesheet' href='digital_ocean.css'><br><img src=https://community-cdn-digitalocean-com.global.ssl.fastly.net/assets/tutorials/images/large/big_data_tw.jpg?1427297016/> <br> 
      <h3 id="introduction">Introduction</h3>

<p>Apache Accumulo is an open-source distributed NoSQL database based on Google's <a href="http://research.google.com/archive/bigtable.html">BigTable</a>. It is used to efficiently perform CRUD (Create Read Update Delete) operations on extremely large data sets (often referred to as Big Data). Accumulo is preferred over other similar distributed databases (such as HBase or CouchDB) if a project requires fine-grained security in the form of cell-level access control.</p>

<p>Accumulo is built on top of other Apache software. Accumulo represents its data in the form of key-value pairs and stores that data as files on HDFS (Apache's Hadoop Distributed File System). It also uses Apache ZooKeeper to synchronize settings between all its processes.</p>

<p>In this tutorial you will learn how to:</p>

<ul>
<li>Install and configure Apache HDFS and ZooKeeper: These systems must be active before Accumulo is started</li>
<li>Install and configure a standalone instance of Accumulo</li>
</ul>

<h2 id="prerequisites">Prerequisites</h2>

<p>You will need the following:</p>

<ul>
<li>Ubuntu 14.04 server (preferably 32-bit)</li>
<li>A <a href="https://indiareads/community/tutorials/how-to-add-and-delete-users-on-an-ubuntu-14-04-vps">sudo</a> user</li>
<li>At least 2 GB of <a href="https://indiareads/community/tutorials/how-to-add-swap-on-ubuntu-14-04">swap space</a></li>
</ul>

<h2 id="step-1-—-install-and-configure-jdk-7">Step 1 — Install and Configure JDK 7</h2>

<p>Accumulo, HDFS, and ZooKeeper are all written in Java and need a JVM (Java Virtual Machine) to run. So, let's start by installing the JDK.</p>

<p>Update the package list index.</p>
<pre class="code-pre "><code langs="">sudo apt-get update
</code></pre>
<p>Install OpenJDK 7 using <code>apt-get</code>.</p>
<pre class="code-pre "><code langs="">sudo apt-get install openjdk-7-jdk
</code></pre>
<p>Use <code>nano</code> to edit your shell environment file, <code>.bashrc</code>.</p>
<pre class="code-pre "><code langs="">nano ~/.bashrc
</code></pre>
<p>Add <code>JAVA_HOME</code> as an environment variable at the end of the file.</p>
<pre class="code-pre "><code langs="">export JAVA_HOME=/usr/lib/jvm/java-7-openjdk-i386
</code></pre>
<blockquote>
<p><strong>Note</strong>: The value of <code>JAVA_HOME</code> can be different depending on your server's architecture. For example, on a 64-bit server, the value would be <code>/usr/lib/jvm/java-7-openjdk-amd64</code>. You can see the exact path by listing the contents of the <code>/usr/lib/jvm/</code> directory. If your path is different from what's shown here, make sure you make the appropriate changes here and elsewhere.</p>
</blockquote>

<p>Save the file and exit <code>nano</code>. Update the environment variables of the current session by typing:</p>
<pre class="code-pre "><code langs="">. ~/.bashrc
</code></pre>
<p>Edit the JVM's <code>java.security</code> configuration file using <code>nano</code>.</p>
<pre class="code-pre "><code langs="">sudo nano $JAVA_HOME/jre/lib/security/java.security
</code></pre>
<p>Search for the parameter <code>securerandom.source</code> and change the line so that it looks like this:</p>
<pre class="code-pre "><code langs="">securerandom.source=file:/dev/./urandom
</code></pre>
<p>Save the file and exit <code>nano</code>. This change is necessary to decrease the JVM's startup time. Not making this change can lead to very long startup times on most virtual servers.</p>

<h2 id="step-2-—-install-ssh">Step 2 — Install SSH</h2>

<p>Hadoop needs SSH and Rsync to manage its daemons. Install them using the following command:</p>
<pre class="code-pre "><code langs="">sudo apt-get install ssh rsync
</code></pre>
<h2 id="step-3-—-enable-passwordless-ssh-connectivity">Step 3 — Enable Passwordless SSH Connectivity</h2>

<p>Hadoop should be able to connect to your server over SSH without being prompted for a password.</p>

<p>Generate an RSA key using <code>ssh-keygen</code>.</p>
<pre class="code-pre "><code langs="">ssh-keygen -P ''
</code></pre>
<p>Press <strong>ENTER</strong> when prompted, to choose the default values.</p>

<p>Add the generated key to the <code>authorized_keys</code> file.</p>
<pre class="code-pre "><code langs="">cat ~/.ssh/id_rsa.pub >> ~/.ssh/authorized_keys
</code></pre>
<p>The values <code>localhost</code> and <code>0.0.0.0</code> should be added to the list of known hosts. The easiest way to do this is by running the <code>ssh</code> command.</p>

<p>Let us add <code>localhost</code> first.</p>
<pre class="code-pre "><code langs="">ssh localhost
</code></pre>
<p>You will be prompted by a message that looks like this:</p>
<pre class="code-pre "><code langs="">The authenticity of host 'localhost (127.0.0.1)' can't be established.
ECDSA key fingerprint is bf:01:63:5b:91:aa:35:db:ee:f4:7e:2d:36:e7:de:42.
Are you sure you want to continue connecting (yes/no)?
</code></pre>
<p>Type in <code>yes</code> and press <code>ENTER</code>.</p>

<p>Once the login is complete, exit the child SSH session by typing in:</p>
<pre class="code-pre "><code langs="">exit
</code></pre>
<p>Let us add <code>0.0.0.0</code> now.</p>
<pre class="code-pre "><code langs="">ssh 0.0.0.0
</code></pre>
<p>Type in <code>yes</code> and press <code>ENTER</code> when prompted.</p>

<p>Once again, exit the child SSH session by typing in:</p>
<pre class="code-pre "><code langs="">exit
</code></pre>
<p>SSH setup is now complete.</p>

<h2 id="step-4-—-create-a-downloads-directory">Step 4 — Create a Downloads Directory</h2>

<p>You will be downloading a couple of files for this tutorial. Though not really necessary, it is a good idea to store all your downloads in a separate directory.</p>
<pre class="code-pre "><code langs="">mkdir -p ~/Downloads
</code></pre>
<p>Enter the directory.</p>
<pre class="code-pre "><code langs="">cd ~/Downloads
</code></pre>
<h2 id="step-5-—-download-apache-hadoop">Step 5 — Download Apache Hadoop</h2>

<p>At the time of writing, the latest stable version of Hadoop is <strong>2.6.0</strong>. Download it using <code>wget</code>.</p>
<pre class="code-pre "><code langs="">wget "http://www.eu.apache.org/dist/hadoop/common/stable/<span class="highlight">hadoop-2.6.0.tar.gz</span>"
</code></pre>
<h2 id="step-6-—-download-apache-zookeeper">Step 6 — Download Apache ZooKeeper</h2>

<p>The latest stable version of ZooKeeper is <strong>3.4.6</strong>. Download it using <code>wget</code>.</p>
<pre class="code-pre "><code langs="">wget "http://www.eu.apache.org/dist/zookeeper/stable/<span class="highlight">zookeeper-3.4.6.tar.gz</span>"
</code></pre>
<h2 id="step-7-—-download-apache-accumulo">Step 7 — Download Apache Accumulo</h2>

<p>The latest stable version of Accumulo is <strong>1.6.1</strong>. Download it using <code>wget</code>.</p>
<pre class="code-pre "><code langs="">wget "http://www.eu.apache.org/dist/accumulo/1.6.1/<span class="highlight">accumulo-1.6.1-bin.tar.gz</span>"
</code></pre>
<h2 id="step-8-—-create-an-installs-directory">Step 8 — Create an Installs Directory</h2>

<p>Create a directory to store all of our Accumulo-related installations.</p>
<pre class="code-pre "><code langs="">mkdir -p ~/Installs
</code></pre>
<p>Enter the directory.</p>
<pre class="code-pre "><code langs="">cd ~/Installs
</code></pre>
<h2 id="step-9-—-install-and-configure-hadoop">Step 9 — Install and Configure Hadoop</h2>

<p>Use the <code>tar</code> command to extract the contents of <code>hadoop-2.6.0-src.tar.gz</code>.</p>
<pre class="code-pre "><code langs="">tar -xvzf ~/Downloads/hadoop-2.6.0.tar.gz
</code></pre>
<blockquote>
<p><strong>Note:</strong> If you installed a different version of any of this software, please use the appropriate version in your file name.</p>
</blockquote>

<p>Use <code>nano</code> to open <code>hadoop-env.sh</code>.</p>
<pre class="code-pre "><code langs="">nano ~/Installs/hadoop-2.6.0/etc/hadoop/hadoop-env.sh
</code></pre>
<p>Look for the line that starts with <code>export JAVA_HOME</code> and change it to:</p>
<pre class="code-pre "><code langs="">export JAVA_HOME=/usr/lib/jvm/java-7-openjdk-i386
</code></pre>
<p>Make sure this value is identical to the value you set in <code>.bashrc</code>.</p>

<p>By default Hadoop generates a lot of debug logs. To stop this behavior, look for the line that starts with <code>export HADOOP_OPTS</code> and change it to:</p>
<pre class="code-pre "><code langs="">export HADOOP_OPTS="$HADOOP_OPTS -XX:-PrintWarnings -Djava.net.preferIPv4Stack=true"
</code></pre>
<p>Save and exit.</p>

<p>Use <code>nano</code> to open <code>core-site.xml</code>.</p>
<pre class="code-pre "><code langs="">nano ~/Installs/hadoop-2.6.0/etc/hadoop/core-site.xml
</code></pre>
<p>Add a <code><property></code> block named <code>fs.defaultFS</code>. Its value should point to the namenode's hostname and port (in our case, it is <code>localhost</code> and the default port <code>9000</code>). Ignoring the comments, edit your file so that it looks like this:</p>
<pre class="code-pre "><code langs=""><?xml version="1.0" encoding="UTF-8"?>
<?xml-stylesheet type="text/xsl" href="configuration.xsl"?>
<configuration>
    <property>
        <name>fs.defaultFS</name>
        <value>hdfs://localhost:9000</value>
    </property>
</configuration>
</code></pre>
<p>Save and exit.</p>

<p>Use <code>nano</code> to open <code>hdfs-site.xml</code>.</p>
<pre class="code-pre "><code langs="">nano ~/Installs/hadoop-2.6.0/etc/hadoop/hdfs-site.xml
</code></pre>
<p>The following properties need to be added to this file:</p>

<ul>
<li><p><code>dfs.replication</code>: This number specifies how many times a block is replicated by Hadoop. By default, Hadoop creates <code>3</code> replicas for each block. In this tutorial, use the value <code>1</code>, as we are not creating a cluster.</p></li>
<li><p><code>dfs.name.dir</code>: This points to a location in the filesystem where the namenode can store the name table. You need to change this because Hadoop uses <code>/tmp</code> by default. Let us use <code>hdfs_storage/name</code> to store the name table.</p></li>
<li><p><code>dfs.data.dir</code>: This points to a location in the filesystem where the datanode should store its blocks. You need to change this because Hadoop uses <code>/tmp</code> by default. Let us use <code>hdfs_storage/data</code> to store the data blocks.</p></li>
</ul>

<p>Ignoring the comments, after adding these properties, your file should look like this:</p>
<pre class="code-pre "><code langs=""><?xml version="1.0" encoding="UTF-8"?>
<?xml-stylesheet type="text/xsl" href="configuration.xsl"?>
<configuration>
    <property>
        <name>dfs.replication</name>
        <value>1</value>
    </property>
    <property>
        <name>dfs.name.dir</name>
        <value>hdfs_storage/name</value>
    </property>
    <property>
        <name>dfs.data.dir</name>
        <value>hdfs_storage/data</value>
    </property>
</configuration>
</code></pre>
<p>Use <code>nano</code> to create a new file named <code>mapred-site.xml</code>.</p>
<pre class="code-pre "><code langs="">nano ~/Installs/hadoop-2.6.0/etc/hadoop/mapred-site.xml
</code></pre>
<p>Add a property named <code>mapred.job.tracker</code> to this file. This property contains the hostname and port number on which the MapReduce job tracker runs. For our setup, use <code>localhost</code> and the default port <code>9001</code>.</p>

<p>Add the following content to the file:</p>
<pre class="code-pre "><code langs=""><?xml version="1.0"?>
<?xml-stylesheet type="text/xsl" href="configuration.xsl"?>
<configuration>
     <property>
         <name>mapred.job.tracker</name>
         <value>localhost:9001</value>
     </property>
</configuration>
</code></pre>
<p>Enter Hadoop's base directory (this is important because Hadoop creates the <code>hdfs_storage</code> directory in the current directory).</p>
<pre class="code-pre "><code langs="">cd ~/Installs/hadoop-2.6.0/
</code></pre>
<p>The NameNode can now be initialized by typing in:</p>
<pre class="code-pre "><code langs="">~/Installs/hadoop-2.6.0/bin/hdfs namenode -format
</code></pre>
<p>You should see quite a bit of output.</p>

<p>Next, start the NameNode by typing in:</p>
<pre class="code-pre "><code langs="">~/Installs/hadoop-2.6.0/sbin/start-dfs.sh
</code></pre>
<p>Wait a minute or two for it to start. Once started, you can use a browser to visit <code>http://<your-ip>:50070/</code> and browse through the web interface of the NameNode.</p>

<p><img src="https://assets.digitalocean.com/articles/accumulo_nosqldatabase/1.png" alt="Hadoop NameNode Web Interface" /></p>

<p><strong>Troubleshooting</strong></p>

<p>If you are not able to access the web interface, check if the NameNode is active by using the following command:</p>
<pre class="code-pre "><code langs="">jps
</code></pre>
<p>Your output should contain the following three processes along with the <code>Jps</code> process:</p>

<ul>
<li><code>DataNode</code></li>
<li><code>NameNode</code></li>
<li><code>SecondaryNameNode</code></li>
</ul>

<p>If you see that <code>NameNode</code> is not present in the output, perform the following steps. If they don't execute in a block, you may have to run them separately. Comments are inluded in-line.</p>
<pre class="code-pre "><code langs="">cd ~/Installs/hadoop-2.6.0/
~/Installs/hadoop-2.6.0/sbin/stop-dfs.sh # Stop Hadoop's nodes
rm -rf hdfs_storage # Delete the namenode data
rm -rf /tmp/hadoop-* # Delete the temporary directories
~/Installs/hadoop-2.6.0/bin/hdfs namenode -format # Reformat the namenode
</code></pre>
<p>Restart Hadoop using <code>start-dfs.sh</code>:</p>
<pre class="code-pre "><code langs="">~/Installs/hadoop-2.6.0/sbin/start-dfs.sh
</code></pre>
<p>You should be able to access the web interface now.</p>

<h2 id="step-10-—-install-and-configure-zookeeper">Step 10 — Install and Configure ZooKeeper</h2>

<p>Enter the <code>Installs</code> directory.</p>
<pre class="code-pre "><code langs="">cd ~/Installs
</code></pre>
<p>Use <code>tar</code> to extract <code>zookeeper-3.4.6.tar.gz</code>.</p>
<pre class="code-pre "><code langs="">tar -xvzf ~/Downloads/zookeeper-3.4.6.tar.gz
</code></pre>
<p>Copy the example file <code>zoo_sample.cfg</code> to <code>zoo.cfg</code>.</p>
<pre class="code-pre "><code langs="">cp ~/Installs/zookeeper-3.4.6/conf/zoo_sample.cfg ~/Installs/zookeeper-3.4.6/conf/zoo.cfg
</code></pre>
<p>Configuration of ZooKeeper is now complete. Start ZooKeeper by typing in:</p>
<pre class="code-pre "><code langs="">~/Installs/zookeeper-3.4.6/bin/zkServer.sh start
</code></pre>
<p>You should see output that looks like this:</p>
<pre class="code-pre "><code langs="">JMX enabled by default
Using config: ~/Installs/zookeeper-3.4.6/bin/../conf/zoo.cfg
Starting zookeeper ... STARTED
</code></pre>
<h2 id="step-11-—-install-and-configure-accumulo">Step 11 — Install and Configure Accumulo</h2>

<p>Now that all its dependencies are satisfied, it is time to work on the installation of Accumulo itself.</p>

<p>Enter the <code>Installs</code> directory.</p>
<pre class="code-pre "><code langs="">cd ~/Installs
</code></pre>
<p>Extract <code>accumulo-1.6.1-bin.tar.gz</code> using <code>tar</code>:</p>
<pre class="code-pre "><code langs="">tar -xvzf ~/Downloads/accumulo-1.6.1-bin.tar.gz
</code></pre>
<p>Accumulo comes with sample configurations for servers with various memory sizes: 512 MB, 1 GB, 2 GB and 3 GB. I'm going to use the configuration for 512 MB in this tutorial. You can pick another configurations if your server has more memory.</p>

<p>Copy the 512 MB configuration files to the <code>conf</code> directory.</p>
<pre class="code-pre "><code langs="">cp ~/Installs/accumulo-1.6.1/conf/examples/512MB/standalone/* ~/Installs/accumulo-1.6.1/conf/
</code></pre>
<p>Use <code>nano</code> to edit your shell environment again, using the <code>.bashrc</code> file.</p>
<pre class="code-pre "><code langs="">nano ~/.bashrc
</code></pre>
<p>Add the following environment variables to this file:</p>

<ul>
<li>HADOOP_HOME: The path to the Hadoop installation</li>
<li>ZOOKEEPER_HOME: The path to the ZooKeeper installation</li>
</ul>

<p>Add the following lines to your file:</p>
<pre class="code-pre "><code langs="">export HADOOP_HOME=~/Installs/hadoop-2.6.0/
export ZOOKEEPER_HOME=~/Installs/zookeeper-3.4.6/
</code></pre>
<p>Save and exit.</p>

<p>Update the environment so that the variables you added to <code>.bashrc</code> are available in the current session.</p>
<pre class="code-pre "><code langs="">. ~/.bashrc
</code></pre>
<p>Use <code>nano</code> to edit <code>accumulo-env.sh</code>.</p>
<pre class="code-pre "><code langs="">nano ~/Installs/accumulo-1.6.1/conf/accumulo-env.sh
</code></pre>
<p>By default, Accumulo's HTTP monitor binds only to the local network interface. To be able to access it over the Internet, you have to set the value of <code>ACCUMULO_MONITOR_BIND_ALL</code> to <code>true</code>.</p>

<p>Find the line that starts with <code>export ACCUMULO_MONITOR_BIND_ALL</code> and uncomment it. It should look like this:</p>
<pre class="code-pre "><code langs="">export ACCUMULO_MONITOR_BIND_ALL="true"
</code></pre>
<p>Save and exit.</p>

<p>Use <code>nano</code> to edit <code>accumulo-site.xml</code>.</p>
<pre class="code-pre "><code langs="">nano ~/Installs/accumulo-1.6.1/conf/accumulo-site.xml
</code></pre>
<p>Accumulo's worker processes communicate with each other using a secret key. This should be changed to a string which is secure. Search for the property <code>instance.secret</code> and change its value. I'm going to use this string: <code><span class="highlight">PASS1234</span></code>. The XML for the property should look like this:</p>
<pre class="code-pre "><code langs=""><property>
    <name>instance.secret</name>
    <value><span class="highlight">PASS1234</span></value>
    <description>A secret unique to a given instance that all servers must know in order to communicate with one another.
      Change it before initialization. To
      change it later use ./bin/accumulo org.apache.accumulo.server.util.ChangeSecret --old [oldpasswd] --new [newpasswd],
      and then update this file.
    </description>
</property>
</code></pre>
<p>Next, add a new property named <code>instance.volumes</code>. The value of this property specifies where Accumulo should store its data in the HDFS. Let us store the data in the directory <code>/accumulo</code>.</p>
<pre class="code-pre "><code langs=""><property>
    <name>instance.volumes</name>
    <value>hdfs://localhost:9000/accumulo</value>
</property>
</code></pre>
<p>Find the property <code>trace.token.property.password</code>, and set its value to something secure. Remember this value as you will need it in the next step. I am going to set this to <code><span class="highlight">mypassw</span></code>.</p>
<pre class="code-pre "><code langs="">  <property>
    <name>trace.token.property.password</name>
    <value><span class="highlight">mypassw</span></value>
  </property>
</code></pre>
<p>Save and exit.</p>

<p>Initialize Accumulo.</p>
<pre class="code-pre "><code langs="">~/Installs/accumulo-1.6.1/bin/accumulo init
</code></pre>
<p>You will be prompted to enter an <strong>Instance name</strong>. Use any name of your choice. I choose <code>DIGITAL_OCEAN</code>.</p>

<p>Next, you will be prompted for a password. Type in the same password that you used for the property <code>trace.token.property.password</code>.</p>

<p>Once the command completes, you can start Accumulo.</p>
<pre class="code-pre "><code langs="">~/Installs/accumulo-1.6.1/bin/start-all.sh
</code></pre>
<p>You might see a few warnings recommending higher values for certain system parameters. As we are creating a very small instance in this tutorial, you can ignore those warnings.</p>

<p>Once the startup is complete, you can use a browser to visit Accumulo's web interface at <code>http://<your-server-ip>:50095</code>.</p>

<p><img src="https://assets.digitalocean.com/articles/accumulo_nosqldatabase/2.png" alt="Accumulo Web Interface" /></p>

<h3 id="conclusion">Conclusion</h3>

<p>In this tutorial, you have learned how to set up Apache Accumulo and all the other components it depends on. We have created a very simple setup today using HDFS in pseudo-distributed mode that can run on a single small server. For optimal performance in a production scenario, HDFS should be running in the fully-distributed mode.</p>

<p>In production scenarios, it is also highly recommended that these processes be deployed on servers that have at least 8GB of RAM and 4 or more processor cores so that each process can use over 2GB of memory and a separate core. Alternately, you could deploy the components separately on different servers.</p>

<p>For more information, refer to Apache Accumulo's <a href="http://accumulo.apache.org/index.html">user manual</a>.</p>

    