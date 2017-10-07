<link rel='stylesheet' href='digital_ocean.css'><br> <br> 
      <h3 id="introduction">Introduction</h3>

<p>Apache Kafka is a popular distributed message broker designed to handle large volumes of real-time data efficiently. A Kafka cluster is not only highly scalable and fault-tolerant, but it also has a much higher throughput compared to other message brokers such as ActiveMQ and RabbitMQ. Though it is generally used as a pub/sub messaging system, a lot of organizations also use it for log aggregation because it offers persistent storage for published messages.</p>

<p>In this tutorial, you will learn how to install and use Apache Kafka 0.8.2.1 on Ubuntu 14.04.</p>

<h2 id="prerequisites">Prerequisites</h2>

<p>To follow along, you will need:</p>

<ul>
<li>Ubuntu 14.04 Droplet</li>
<li>At least 4GB of <a href="https://indiareads/community/tutorials/how-to-add-swap-on-ubuntu-14-04">swap space</a></li>
</ul>

<h2 id="step-1-—-create-a-user-for-kafka">Step 1 — Create a User for Kafka</h2>

<p>As Kafka can handle requests over a network, you should create a dedicated user for it. This minimizes damage to your Ubuntu machine should the Kafka server be comprised.</p>

<p><span class="note"><strong>Note:</strong> After setting up Apache Kafka, it is recommended that you create a different non-root user to perform other tasks on this server.<br /></span></p>

<p>As root, create a user called <strong>kafka</strong> using the <code>useradd</code> command:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">useradd kafka -m
</li></ul></code></pre>
<p>Set its password using <code>passwd</code>:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">passwd kafka
</li></ul></code></pre>
<p>Add it to the <code>sudo</code> group so that it has the privileges required to install Kafka's dependencies. This can be done using the <code>adduser</code> command:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">adduser kafka sudo
</li></ul></code></pre>
<p>Your Kafka user is now ready. Log into it using <code>su</code>:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">su - kafka
</li></ul></code></pre>
<h2 id="step-2-—-install-java">Step 2 — Install Java</h2>

<p>Before installing additional packages, update the list of available packages so you are installing the latest versions available in the repository:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo apt-get update
</li></ul></code></pre>
<p>As Apache Kafka needs a Java runtime environment, use <code>apt-get</code> to install the <code>default-jre</code> package:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo apt-get install default-jre
</li></ul></code></pre>
<h2 id="step-3-—-install-zookeeper">Step 3 — Install ZooKeeper</h2>

<p>Apache ZooKeeper is an open source service built to coordinate and synchronize configuration information of nodes that belong to a distributed system. A Kafka cluster depends on ZooKeeper to perform—among other things—operations such as detecting failed nodes and electing leaders.</p>

<p>Since the ZooKeeper package is available in Ubuntu's default repositories, install it using <code>apt-get</code>.</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo apt-get install zookeeperd
</li></ul></code></pre>
<p>After the installation completes, ZooKeeper will be started as a daemon automatically. By default, it will listen on port <strong>2181</strong>.</p>

<p>To make sure that it is working, connect to it via Telnet:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">telnet localhost 2181
</li></ul></code></pre>
<p>At the Telnet prompt, type in <code>ruok</code> and press <code>ENTER</code>.</p>

<p>If everything's fine, ZooKeeper will say <code>imok</code> and end the Telnet session.</p>

<h2 id="step-4-—-download-and-extract-kafka-binaries">Step 4 — Download and Extract Kafka Binaries</h2>

<p>Now that Java and ZooKeeper are installed, it is time to download and extract Kafka.</p>

<p>To start, create a directory called <code>Downloads</code> to store all your downloads.</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">mkdir -p ~/Downloads
</li></ul></code></pre>
<p>Use <code>wget</code> to download the Kafka binaries.</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">wget "http://mirror.cc.columbia.edu/pub/software/apache/kafka/0.8.2.1/kafka_2.11-0.8.2.1.tgz" -O ~/Downloads/kafka.tgz
</li></ul></code></pre>
<p>Create a directory called <code>kafka</code> and change to this directory. This will be the base directory of the Kafka installation.</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">mkdir -p ~/kafka && cd ~/kafka
</li></ul></code></pre>
<p>Extract the archive you downloaded using the <code>tar</code> command.</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">tar -xvzf ~/Downloads/kafka.tgz --strip 1
</li></ul></code></pre>
<h2 id="step-5-—-configure-the-kafka-server">Step 5 — Configure the Kafka Server</h2>

<p>The next step is to configure the Kakfa server.</p>

<p>Open <code>server.properties</code> using <code>vi</code>:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">vi ~/kafka/config/server.properties
</li></ul></code></pre>
<p>By default, Kafka doesn't allow you to delete topics. To be able to delete topics, add the following line at the end of the file:</p>
<div class="code-label " title="~/kafka/config/server.properties">~/kafka/config/server.properties</div><pre class="code-pre "><code langs="">delete.topic.enable = true
</code></pre>
<p>Save the file, and exit <code>vi</code>.</p>

<h2 id="step-6-—-start-the-kafka-server">Step 6 — Start the Kafka Server</h2>

<p>Run the <code>kafka-server-start.sh</code> script using <code>nohup</code> to start the Kafka server (also called Kafka broker) as a background process that is independent of your shell session.</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">nohup ~/kafka/bin/kafka-server-start.sh ~/kafka/config/server.properties > ~/kafka/kafka.log 2>&1 &
</li></ul></code></pre>
<p>Wait for a few seconds for it to start. You can be sure that the server has started successfully when you see the following messages in <code>~/kafka/kafka.log</code>:</p>
<div class="code-label " title="excerpt from ~/kafka/kafka.log">excerpt from ~/kafka/kafka.log</div><pre class="code-pre "><code langs="">
...

[2015-07-29 06:02:41,736] INFO New leader is 0 (kafka.server.ZookeeperLeaderElector$LeaderChangeListener)
[2015-07-29 06:02:41,776] INFO [Kafka Server 0], started (kafka.server.KafkaServer)
</code></pre>
<p>You now have a Kafka server which is listening on port <strong>9092</strong>.</p>

<h2 id="step-7-—-test-the-installation">Step 7 — Test the Installation</h2>

<p>Let us now publish and consume a <strong>"Hello World"</strong> message to make sure that the Kafka server is behaving correctly.</p>

<p>To publish messages, you should create a Kafka producer. You can easily create one from the command line using the <code>kafka-console-producer.sh</code> script. It expects the Kafka server's hostname and port, along with a topic name as its arguments.</p>

<p>Publish the string <strong>"Hello, World"</strong> to a topic called <strong>TutorialTopic</strong> by typing in the following:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">echo "Hello, World" | ~/kafka/bin/kafka-console-producer.sh --broker-list localhost:9092 --topic TutorialTopic > /dev/null
</li></ul></code></pre>
<p>As the topic doesn't exist, Kafka will create it automatically.</p>

<p>To consume messages, you can create a Kafka consumer using the <code>kafka-console-consumer.sh</code> script. It expects the ZooKeeper server's hostname and port, along with a topic name as its arguments.</p>

<p>The following command consumes messages from the topic we published to. Note the use of the <code>--from-beginning</code> flag, which is present because we want to consume a message that was published before the consumer was started.</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">~/kafka/bin/kafka-console-consumer.sh --zookeeper localhost:2181 --topic TutorialTopic --from-beginning
</li></ul></code></pre>
<p>If there are no configuration issues, you should see <code>Hello, World</code> in the output now.</p>

<p>The script will continue to run, waiting for more messages to be published to the topic. Feel free to open a new terminal and start a producer to publish a few more messages. You should be able to see them all in the consumer's output instantly.</p>

<p>When you are done testing, press CTRL+C to stop the consumer script.</p>

<h2 id="step-8-—-install-kafkat-optional">Step 8 — Install KafkaT (Optional)</h2>

<p>KafkaT is a handy little tool from Airbnb which makes it easier for you to view details about your Kafka cluster and also perform a few administrative tasks from the command line. As it is a Ruby gem, you will need Ruby to use it. You will also need the <code>build-essential</code> package to be able to build the other gems it depends on. Install them using <code>apt-get</code>:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo apt-get install ruby ruby-dev build-essential
</li></ul></code></pre>
<p>You can now install KafkaT using the <code>gem</code> command:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo gem install kafkat --source https://rubygems.org --no-ri --no-rdoc
</li></ul></code></pre>
<p>Use <code>vi</code> to create a new file called <code>.kafkatcfg</code>.</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">vi ~/.kafkatcfg
</li></ul></code></pre>
<p>This is a configuration file which KafkaT uses to determine the installation and log directories of your Kafka server. It should also point KafkaT to your ZooKeeper instance. Accordingly, add the following lines to it:</p>
<div class="code-label " title="~/.kafkatcfg">~/.kafkatcfg</div><pre class="code-pre "><code langs="">{
  "kafka_path": "~/kafka",
  "log_path": "/tmp/kafka-logs",
  "zk_path": "localhost:2181"
}
</code></pre>
<p>You are now ready to use KafkaT. For a start, here's how you would use it to view details about all Kafka partitions:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">kafkat partitions
</li></ul></code></pre>
<p>You should see the following output:</p>
<div class="code-label " title="output of kafkat partitions">output of kafkat partitions</div><pre class="code-pre "><code langs="">Topic           Partition   Leader      Replicas        ISRs    
TutorialTopic   0             0           [0]           [0]
</code></pre>
<p>To learn more about KafkaT, refer to its <a href="https://github.com/airbnb/kafkat">GitHub repository</a>.</p>

<h2 id="step-9-—-set-up-a-multi-node-cluster-optional">Step 9 — Set Up a Multi-Node Cluster (Optional)</h2>

<p>If you want to create a multi-broker cluster using more Ubuntu 14.04 machines, you should repeat Step 1, Step 3, Step 4 and Step 5 on each of the new machines. Additionally, you should make the following changes in the <code>server.properties</code> file in each of them:</p>

<ul>
<li>the value of the <code>broker.id</code> property should be changed such that it is unique throughout the cluster</li>
<li>the value of the <code>zookeeper.connect</code> property should be changed such that all nodes point to the same ZooKeeper instance</li>
</ul>

<p>If you want to have multiple ZooKeeper instances for your cluster, the value of the <code>zookeeper.connect</code> property on each node should be an identical, comma-separated string listing the IP addresses and port numbers of all the ZooKeeper instances.</p>

<h2 id="step-10-—-restrict-the-kafka-user">Step 10 — Restrict the Kafka User</h2>

<p>Now that all installations are done, you can remove the <code>kafka</code> user's admin privileges. Before you do so, log out and log back in as any other non-root sudo user. If you are still running the same shell session you started this tutorial with, simply type <code>exit</code>.</p>

<p>To remove the <code>kafka</code> user's admin privileges, remove it from the <code>sudo</code> group.</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo deluser kafka sudo
</li></ul></code></pre>
<p>To further improve your Kafka server's security, lock the <code>kafka</code> user's password using the <code>passwd</code> command. This makes sure that nobody can directly log into it.</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo passwd kafka -l
</li></ul></code></pre>
<p>At this point, only root or a sudo user can log in as <code>kafka</code> by typing in the following command:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo su - kafka
</li></ul></code></pre>
<p>In the future, if you want to unlock it, use <code>passwd</code> with the <code>-u</code> option:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo passwd kafka -u
</li></ul></code></pre>
<h2 id="conclusion">Conclusion</h2>

<p>You now have a secure Apache Kafka running on your Ubuntu server. You can easily make use of it in your projects by creating Kafka producers and consumers using <a href="https://cwiki.apache.org/confluence/display/KAFKA/Clients#Clients-For0.8.x">Kafka clients</a> which are available for most programming languages. To learn more about Kafka, do go through its <a href="http://kafka.apache.org/documentation.html">documentation</a>.</p>

    