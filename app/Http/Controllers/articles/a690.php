<link rel='stylesheet' href='digital_ocean.css'><br> <br> 
      <h3 id="introduction">Introduction</h3>

<hr />

<p>When it comes to sending and receiving messages between applications and processes, there are <em>many</em> solutions you can choose from. They all have their pros and cons, therefore the best thing to do is to cross-check your requirements and match them against various available solutions.</p>

<p><strong>Apache Qpid</strong> is one of the open-source messaging systems that implements the Advanced Message Queuing Protocol (AMQP) to help you solve your needs of advanced messaging between different elements of your deployment stack.</p>

<h2 id="messaging-message-brokers-and-queues">Messaging, Message Brokers and Queues</h2>

<hr />

<p>Messaging is a way of exchanging certain data between processes, applications, and servers (virtual and physical). These messages exchanged, helping with certain engineering needs, can consist of anything from plain text messages to blobs of binary data serving to address different needs. For this to work, an interface managed by a third party program (a middleware) is needed - welcome <strong>Message Brokers</strong>.</p>

<p>Message Brokers are usually application stacks with dedicated pieces covering each stage of the exchange setup. From accepting a message to queuing it and delivering it to the requesting party, brokers handle the duty which would normally be much harder or cumbersome to do with non-dedicated solutions or simple <em>hacks</em> such as using a database, cron jobs, etc. They simply work by dealing with queues which technically constitute infinite buffers, to put messages and pop-and-deliver them later on to be processed either automatically or by polling.</p>

<h3 id="why-use-them">Why use them?</h3>

<hr />

<p>These message brooking solutions act like a middleman for various services (e.g. your web application). They can be used to greatly reduce loads and delivery times by web application servers since tasks, which would normally take quite bit of time to process, can be delegated for a third party whose sole job is to perform them (i.e. workers). They also come in handy when a more "guaranteed" persistence is needed to pass information along from one place to another. </p>

<h3 id="when-to-use-them">When to use them?</h3>

<hr />

<p>All put together, the core functionality explained expands to cover a multitude of areas, <em>including-but-not-limited-to</em>:</p>

<ul>
<li><p>Allowing web servers to respond to requests quickly instead of being forced to perform resource-heavy procedures on the spot</p></li>
<li><p>Distributing a message to multiple recipients for consumption (i.e. processing)</p></li>
<li><p>Letting offline parties (e.g. a disconnected user) fetch data at a later time instead of having it lost permanently</p></li>
<li><p>Introducing fully asynchronous functionality to the backend systems</p></li>
<li><p>Ordering and prioritising tasks</p></li>
<li><p>Balancing loads between workers</p></li>
<li><p>Greatly increase reliability and uptime of your application</p></li>
<li><p>and much more. </p></li>
</ul>

<h2 id="apache-qpid">Apache Qpid</h2>

<hr />

<p>Apache Software Foundation has several solutions when it comes to messaging and one of them is Apache Qpid: The foundation's implementation of AMQP. Unlike some more basic applications which are aimed at helping developers to craft their own solutions, Qpid, similar to RabbitMQ, offers a nice toolset capable of queuing, security and transaction management, clustering, persistence via a pluggable layer and more. Its API, by default, supports multiple programming languages and it comes with both C++ (for Perl, Python, Ruby, .NET etc.) and Java (JMS API) brokers. Alongside RabbitMQ, Qpid is probably the most popular choice.</p>

<h3 id="how-is-it-different-than-the-others">How is it different than the others?</h3>

<hr />

<p>Message brokers which are fully fledged differ only slightly on the surface. However, a deeper look into internals reveal the truth behind how things work. The following are the features which make Apache Qpid stand out compared to others:</p>

<ul>
<li><p>Client failover detection and automatic healing by connecting to a different broker</p></li>
<li><p>Easy clustering by replicating queues across different servers</p></li>
<li><p>Error handling by default in clusters</p></li>
<li><p>Easy persistence via a pluggable architecture to offer high-availability</p></li>
<li><p>and more. </p></li>
</ul>

<h2 id="advanced-message-queuing-protocol-amqp-in-brief">Advanced Message Queuing Protocol (AMQP) in Brief</h2>

<hr />

<p>AMQP is a widely accepted open-source standard for distributing and transferring messages from a source to a destination. As a protocol and standard, it sets a common ground for various applications and message broker middlewares to interoperate without encountering issues caused by individually set design decisions.</p>

<h2 id="installing-apache-qpid">Installing Apache Qpid</h2>

<hr />

<p>Getting started with Apache Qpid means installing two different sets of tools:</p>

<ul>
<li><p>An implementation of Qpid Broker depending on programming language of your choice (e.g. C++ broker for Python or Java Broker for Java)</p></li>
<li><p>Qpid Client libraries (e.g. Qpid Python)</p></li>
</ul>

<p><strong>Note:</strong> We will be performing our installations and the actions listed here on a fresh and newly created droplet for various reasons. If you are actively serving clients and might have modified your system, to not to break anything working and to not to run in to issues, you are highly advised to try the following instructions on a new system.</p>

<h3 id="installing-on-centos-6-rhel-based-systems">Installing on CentOS 6 / RHEL Based Systems</h3>

<hr />

<p>Let's update our droplet:</p>
<pre class="code-pre "><code langs="">yum -y update
</code></pre>
<p>And then let's run the following to get Qpid C++ Server and its tools (including Python bindings):</p>
<pre class="code-pre "><code langs="">yum install -y qpid-cpp-server qpid-tools    
</code></pre>
<p>If you require, continue installing Qpid's language bindings for others such as Ruby:</p>
<pre class="code-pre "><code langs="">yum install -y ruby-qpid
</code></pre>
<h3 id="installing-on-ubuntu-13-debian-7-based-systems">Installing on Ubuntu 13 / Debian 7 Based Systems</h3>

<hr />

<p>The process for downloading and installing Apache Qpid on Ubuntu and Debian will be similar to CentOS.</p>

<p>Let's begin with updating our system's default application toolset:</p>
<pre class="code-pre "><code langs="">apt-get    update 
apt-get -y upgrade
</code></pre>
<p>And then let's run the following to get Qpid C++ Server and its tools:</p>
<pre class="code-pre "><code langs="">apt-get install -y qpidd qpid-tools
apt-get install -y libqpidmessaging2-dev python-qpid ruby-qpid
</code></pre>
<p><strong>Note:</strong> During the installation process you will be prompted to enter a password <em>of your choice</em> for the Qpid daemon administrator.</p>

<h2 id="managing-apache-qpid">Managing Apache Qpid</h2>

<hr />

<h3 id="managing-on-centos-rhel-based-systems">Managing on CentOS / RHEL Based Systems</h3>

<hr />

<p>To start, stop, restart, and check the application status, use the following:</p>
<pre class="code-pre "><code langs=""># To start the service:
/sbin/service qpidd start

# To stop the service:
/sbin/service qpidd stop

# To restart the service:
/sbin/service qpidd restart

# To check the status:
/sbin/service qpidd status

# To force reload:
/sbin/service qpidd force-reload
</code></pre>
<h3 id="managing-on-ubuntu-debian-based-systems">Managing on Ubuntu / Debian Based Systems</h3>

<hr />

<p>To start, stop, restart, and check the application status on Ubuntu and Debian, use the following:</p>
<pre class="code-pre "><code langs=""># To start the service:
service qpidd start

# To stop the service:
service qpidd stop

# To restart the service:
service qpidd restart

# To check the status:
service qpidd status

# To force reload:
service qpidd force-reload
</code></pre>
<p>And that's it! You now have your own Apache Qpid message broker working on your droplet.</p>

<p>To learn more about Qpid and its vast array of configuration options, check out its documentation for <a href="http://qpid.apache.org/releases/qpid-0.20/cpp-broker/book/ch01.html">C++ Implementation</a> and <a href="http://qpid.apache.org/releases/qpid-0.20/java-broker/book/index.html">Java Implementation</a>.</p>

<h2 id="working-with-apache-qpid">Working with Apache Qpid</h2>

<hr />

<p>Following our installation of Qpid along with its Python language bindings, let's look into a simple Qpid example to understand basics of working with it.</p>

<p>Create a (sample) <code>hello_world.py</code> file using <code>nano</code>:</p>
<pre class="code-pre "><code langs="">nano hello_world.py
</code></pre>
<p>Paste the below self-explanatory module:</p>
<pre class="code-pre "><code langs=""># Import the modules we need
from qpid.messaging import *

broker     = "localhost:5672" 
address    = "amq.topic" 
connection = Connection(broker)

try:
    connection.open()

    # Define the session
    session = connection.session()

    # Define a sender *and* a receiver
    sender   = session.sender(address)
    receiver = session.receiver(address)

    # Send a simple "Hello world!" message to the queue
    sender.send(Message("Hello world!"));

    # Fetch the next message in the queue
    message = receiver.fetch()

    # Output the message
    print message.content

    # Check with the server
    session.acknowledge()

except MessagingError, err:
    print err

finally:
    connection.close()
</code></pre>
<p>Press CTRL+X and confirm with Y to save and exit.</p>

<p>When you run the above script, you should see our message (i.e. Hello world!) as the output now.</p>
<pre class="code-pre "><code langs="">python hello_world.py
# Hello world!
</code></pre>
<p>If you run into an issue, be sure that qpid is running. You can start it using the commands above.</p>

<div class="author">Submitted by: <a href="https://twitter.com/ostezer">O.S. Tezer</a></div>

    