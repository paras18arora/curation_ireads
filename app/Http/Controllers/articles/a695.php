<link rel='stylesheet' href='digital_ocean.css'><br> <br> 
      <h3 id="introduction">Introduction</h3>

<hr />

<p>Putting things off for a while instead of immediately doing them can be considered lazy. In fact, most of the time it probably is. However, there are times when it’s absolutely the right thing to do. Occasionally, one needs to delay a time-consuming job for a while; it needs to be queued for future execution so that something more important can be dealt with. For this to happen, you need <strong>a broker</strong>: someone who will accept messages (e.g. jobs, tasks) from various senders (i.e. a web application), queue them up, and distribute them to the relevant parties (i.e. workers) to make use of them - all asynchronously and on demand. </p>

<p>In this IndiaReads article, we aim to introduce you to the <strong><em><a href="http://www.rabbitmq.com/">RabbitMQ</a></em></strong> project: an open-source message-broker application stack which implements the Advanced Message Queuing Protocol (AMQP) to handle the entirety of the scenario we explained above.</p>

<h2 id="messaging-message-brokers-and-queues">Messaging, Message Brokers and Queues</h2>

<hr />

<p>Messaging is a way of exchanging certain data between processes, applications, and servers (virtual and physical). These messages exchanged, helping with certain engineering needs, can consist of anything from plain text messages to blobs of binary data serving to address different needs. For this to work, an interface managed by a third party program (a middleware) is needed… welcome <strong>Message Brokers</strong>.</p>

<p>Message Brokers are usually application stacks with dedicated pieces covering the each stage of the exchange setup. From accepting a message to queuing it and delivering it to the requesting party, brokers handle the duty which would normally be much more cumbersome with non-dedicated solutions or simple <em>hacks</em> such as using a database, cron jobs, etc. They simply work by dealing with queues which technically constitute infinite buffers, to put messages and pop-and-deliver them later on to be processed either automatically or by polling.</p>

<h3 id="why-use-them">Why use them?</h3>

<hr />

<p>These message brooking solutions act like a middleman for various services (e.g. your web application). They can be used to greatly reduce loads and delivery times by web application servers since tasks, which would normally take quite bit of time to process, can be delegated for a third party whose sole job is to perform them (e.g. workers). They also come in handy when a more "guaranteed" persistence is needed to pass information along from one place to another. </p>

<h3 id="when-to-use-them">When to use them?</h3>

<hr />

<p>All put together, the core functionality explained expands to cover a multitude of areas, including-but-not-limited-to:</p>

<ul>
<li><p>Allowing web servers to respond to requests quickly instead of being forced to perform resource-heavy procedures on the spot</p></li>
<li><p>Distributing a message to multiple recipients for consumption (e.g. processing)</p></li>
<li><p>Letting offline parties (i.e. a disconnected user) fetch data at a later time instead of having it lost permanently</p></li>
<li><p>Introducing fully asynchronous functionality to the backend systems</p></li>
<li><p>Ordering and prioritising tasks</p></li>
<li><p>Balancing loads between workers</p></li>
<li><p>Greatly increase reliability and uptime of your application</p></li>
<li><p>and much more</p></li>
</ul>

<h2 id="rabbitmq">RabbitMQ</h2>

<hr />

<p>RabbitMQ is one of the more popular message broker solutions in the market, offered with an open-source license (Mozilla Public License v1.1) as an implementation of Advanced Message Queuing Protocol. Developed using the Erlang language, it is actually relatively easy to use and get started. It was first published in early 2007 and has since seen an active development with its latest release being <strong>version 3.2.2</strong> (December 2013).</p>

<h3 id="how-does-it-work">How does it work?</h3>

<hr />

<p>RabbitMQ works by offering an interface, connecting message senders (Publishers) with receivers (Consumers) through an exchange (Broker) which distributes the data to relevant lists (Message Queues).</p>
<pre class="code-pre "><code langs="">APPLICATION       EXCHANGE        TASK LIST        WORKER
   [DATA] -------> [DATA] ---> [D]+[D][D][D] --->  [DATA]
 Publisher        EXCHANGE          Queue         Consumer 
</code></pre>
<h3 id="how-is-it-different-than-the-others">How is it different than the others?</h3>

<hr />

<p>RabbitMQ, unlike some other solutions, is a fully-fledged application stack (i.e. a message broker). It gives you all the tools you need to work with, instead of acting like a framework for you to implement your own. Being extremely popular, it is really easy to get going using RabbitMQ and to find answers to your questions online.</p>

<h2 id="advanced-message-queuing-protocol-amqp-in-brief">Advanced Message Queuing Protocol (AMQP) in Brief</h2>

<hr />

<p>AMQP is a widely accepted open-source standard for distributing and transferring messages from a source to a destination. As a protocol and standard, it sets a common ground for various applications and message broker middlewares to interoperate without encountering issues caused by individually set design decisions.</p>

<h2 id="installing-rabbitmq">Installing RabbitMQ</h2>

<hr />

<p>RabbitMQ packages are distributed both with CentOS / RHEL & Ubuntu / Debian based systems. However, they are - like with most applications - outdated. The recommended way to get RabbitMQ on your system is therefore to download the package online and install manually.</p>

<p><strong>Note:</strong> We will be performing our installations and perform the actions listed here on a fresh and newly created VPS due to various reasons. If you are actively serving clients and might have modified your system, in order to not break anything working and to not to run into issues, you are highly advised to try the following instructions on a new system.</p>

<h3 id="installing-on-centos-6-rhel-based-systems">Installing on CentOS 6 / RHEL Based Systems</h3>

<hr />

<p>Before installing RabbitMQ, we need to get its main dependencies such as Erlang. However, first and foremost we should update our system and its default applications.</p>

<p>Run the following to update our droplet:</p>
<pre class="code-pre "><code langs="">yum -y update
</code></pre>
<p>And let's use the below commands to get Erlang on our system:</p>
<pre class="code-pre "><code langs=""># Add and enable relevant application repositories:
# Note: We are also enabling third party remi package repositories.
wget http://dl.fedoraproject.org/pub/epel/6/x86_64/epel-release-6-8.noarch.rpm
wget http://rpms.famillecollet.com/enterprise/remi-release-6.rpm
sudo rpm -Uvh remi-release-6*.rpm epel-release-6*.rpm

# Finally, download and install Erlang:
yum install -y erlang
</code></pre>
<p>Once we have Erlang, we can continue with installing RabbitMQ:</p>
<pre class="code-pre "><code langs=""># Download the latest RabbitMQ package using wget:
wget http://www.rabbitmq.com/releases/rabbitmq-server/v3.2.2/rabbitmq-server-3.2.2-1.noarch.rpm

# Add the necessary keys for verification:
rpm --import http://www.rabbitmq.com/rabbitmq-signing-key-public.asc

# Install the .RPM package using YUM:
yum install rabbitmq-server-3.2.2-1.noarch.rpm
</code></pre>
<h3 id="installing-on-ubuntu-13-debian-7-based-systems">Installing on Ubuntu 13 / Debian 7 Based Systems</h3>

<hr />

<p>The process for downloading and installing RabbitMQ on Ubuntu and Debian will be similar to CentOS due to our desire of having a more recent version.</p>

<p>Let's begin with updating our system's default application toolset:</p>
<pre class="code-pre "><code langs="">apt-get    update 
apt-get -y upgrade
</code></pre>
<p>Enable RabbitMQ application repository:</p>
<pre class="code-pre "><code langs="">echo "deb http://www.rabbitmq.com/debian/ testing main" >> /etc/apt/sources.list
</code></pre>
<p>Add the verification key for the package:</p>
<pre class="code-pre "><code langs="">curl http://www.rabbitmq.com/rabbitmq-signing-key-public.asc | sudo apt-key add -
</code></pre>
<p>Update the sources with our new addition from above:</p>
<pre class="code-pre "><code langs="">apt-get update
</code></pre>
<p>And finally, download and install RabbitMQ:</p>
<pre class="code-pre "><code langs="">sudo apt-get install rabbitmq-server
</code></pre>
<p>In order to manage the maximum amount of connections upon launch, open up and edit the following configuration file using <code>nano</code>:</p>
<pre class="code-pre "><code langs="">sudo nano /etc/default/rabbitmq-server
</code></pre>
<p>Uncomment the <code>limit</code> line (i.e. remove <code>#</code>) before saving and exit by pressing CTRL+X followed with Y.    </p>

<h2 id="managing-rabbitmq">Managing RabbitMQ</h2>

<hr />

<p>As we have mentioned before, RabbitMQ is very simple to get started with. Using the instructions below for your system, you can quickly manage its process and have it running at the system start-up (i.e. boot).</p>

<h3 id="enabling-the-management-console">Enabling the Management Console</h3>

<hr />

<p><strong>RabbitMQ Management Console</strong> is one of the available plugins that lets you monitor the [RabbitMQ] server process through a web-based graphical user interface (GUI). </p>

<p>Using this console you can:</p>

<ul>
<li><p>Manage exchanges, queues, bindings, users</p></li>
<li><p>Monitor queues, message rates, connections</p></li>
<li><p>Send and receive messages</p></li>
<li><p>Monitor Erlang processes, memory usage</p></li>
<li><p>And much more</p></li>
</ul>

<p>To enable RabbitMQ Management Console, run the following:</p>
<pre class="code-pre "><code langs="">sudo rabbitmq-plugins enable rabbitmq_management
</code></pre>
<p>Once you've enabled the console, it can be accessed using your favourite web browser by visiting: <code>http://[your droplet's IP]:15672/</code>.</p>

<p>The default username and password are both set “guest” for the log in.</p>

<p><strong>Note</strong>: If you enable this console after running the service, you will need to restart it for the changes to come into effect. See the relevant management section below for your operating system to be able to do it.</p>

<h3 id="managing-on-centos-rhel-based-systems">Managing on CentOS / RHEL Based Systems</h3>

<hr />

<p>Upon installing the application, RabbitMQ is not set to start at system boot by default.</p>

<p>To have RabbitMQ start as a daemon by default, run the following:</p>
<pre class="code-pre "><code langs="">chkconfig rabbitmq-server on
</code></pre>
<p>To start, stop, restart and check the application status, use the following:</p>
<pre class="code-pre "><code langs=""># To start the service:
/sbin/service rabbitmq-server start

# To stop the service:
/sbin/service rabbitmq-server stop

# To restart the service:
/sbin/service rabbitmq-server restart

# To check the status:
/sbin/service rabbitmq-server status
</code></pre>
<h3 id="managing-on-ubuntu-debian-based-systems">Managing on Ubuntu / Debian Based Systems</h3>

<hr />

<p>To start, stop, restart and check the application status on Ubuntu and Debian, use the following:</p>
<pre class="code-pre "><code langs=""># To start the service:
service rabbitmq-server start

# To stop the service:
service rabbitmq-server stop

# To restart the service:
service rabbitmq-server restart

# To check the status:
service rabbitmq-server status
</code></pre>
<p>And that's it! You now have your own message queue working on your virtual server.</p>

<h2 id="configuring-rabbitmq">Configuring RabbitMQ</h2>

<hr />

<p>RabbitMQ by default runs with its standard configuration. In general, it does not require much tempering with for most needs as long as everything runs smoothly. </p>

<p>To learn about configuring it for custom needs, check out its documentation for <a href="http://www.rabbitmq.com/configure.html">Configuration</a>.</p>

<div class="author">Submitted by: <a href="https://twitter.com/ostezer">O.S. Tezer</a></div>

    