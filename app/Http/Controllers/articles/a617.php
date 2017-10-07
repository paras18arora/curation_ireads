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
<p>Add it to the <code>sudo</code> group</p>