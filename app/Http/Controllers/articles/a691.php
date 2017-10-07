<link rel='stylesheet' href='digital_ocean.css'><br> <br> 
      <h3 id="introduction">Introduction</h3>

<hr />

<p>Carefully declaring the duties of each and every element of an application deployment stack brings along a lot of benefits with it, including simpler diagnosis of problems when they occur, capacity to scale rapidly, as well as a more clear scope of management for the components involved.</p>

<p>In today's world of web services engineering, a key component for achieving the above scenario involves making use of messaging and work (or task) queues. These usually resilient and flexible applications are easy to implement and set up. They are perfect for splitting the business logic between different parts of your application bundle when it comes to production.</p>

<p>In this IndiaReads article, continuing our series on application level communication solutions, we will be looking at <strong>Beanstalkd</strong> to create this separation of pieces.</p>

<h2 id="beanstalkd">Beanstalkd</h2>

<hr />

<p>Beanstalkd was first developed to solve the needs of a popular web application (Causes on Facebook). Currently, it is an absolutely reliable, easy to install messaging service which is perfect to get started with and use.</p>

<p>As mentioned earlier, Beanstalkd's main use case is to manage the workflow between different parts and workers of your application deployment stack through work queues and messages, similar to other popular solutions such as RabbitMQ. However, the way Beanstalkd is created to work sets it apart from the rest.</p>

<p>Since its inception, unlike other solutions, Beanstalkd was intended to be a work queue and not an umbrella tool to cover many needs. To achieve this purpose, it was built as a lightweight and rapidly functioning application based on C programming language. Its lean architecture also allows it to be installed and used very simply, making it perfect for a majority of use cases.</p>

<h3 id="features">Features</h3>

<hr />

<p>Being able to monitor jobs with a returned ID, returned upon creation, is only one of the features of Beanstalkd that sets it apart from the rest. Some other interesting features offered are:</p>

<ul>
<li><p><strong>Persistence</strong> - Beanstalkd operates in-memory but offers persistence support as well.</p></li>
<li><p><strong>Prioritisation</strong> - unlike most alternatives, Beanstalkd offers prioritisation for different tasks to handle urgent things when they are needed to.</p></li>
<li><p><strong>Distribution</strong> - different server instances can be distributed similarly to how Memcached works.</p></li>
<li><p><strong>Burying</strong> - it is possible to indefinitely postpone a job (i.e. a task) by burying it.</p></li>
<li><p><strong>Third party tools</strong> - Beanstalkd comes with a variety of third-party tools including CLIs and web-based management consoles.</p></li>
<li><p><strong>Expiry</strong> - jobs can be set to expire and auto-queue later (TTR - Time To Run).</p></li>
</ul>

<h3 id="beanstalkd-use-case-examples">Beanstalkd Use-case Examples</h3>

<hr />

<p>Some exemplary use-cases for Banstalkd are:</p>

<ul>
<li><p>Allowing web servers to respond to requests quickly instead of being forced to perform resource-heavy procedures on the spot</p></li>
<li><p>Performing certain jobs at certain intervals (i.e. crawling the web)</p></li>
<li><p>Distributing a job to multiple workers for processing</p></li>
<li><p>Letting offline clients (e.g. a disconnected user) fetch data at a later time instead of having it lost permanently through a worker</p></li>
<li><p>Introducing fully asynchronous functionality to the backend systems</p></li>
<li><p>Ordering and prioritising tasks</p></li>
<li><p>Balancing application load between different workers</p></li>
<li><p>Greatly increase reliability and uptime of your application</p></li>
<li><p>Processing CPU intensive jobs (videos, images etc.) later</p></li>
<li><p>Sending e-mails to your lists</p></li>
<li><p>and more.</p></li>
</ul>

<h2 id="beanstalkd-elements">Beanstalkd Elements</h2>

<hr />

<p>Just like most applications, Beanstalkd comes with its own <em>jargon</em> to explain its parts.</p>

<h3 id="tubes-queues">Tubes / Queues</h3>

<hr />

<p>Beanstalkd Tubes translate to <em>queues</em> from other messaging applications. They are through where jobs (or messages) are transferred to consumers (i.e. workers).</p>

<h3 id="jobs-messages">Jobs / Messages</h3>

<hr />

<p>Since Beanstalkd is a "work queue", what's transferred through <em>tubes</em> are referred as jobs - which are similar to messages being sent.</p>

<h3 id="producers-senders">Producers / Senders</h3>

<hr />

<p>Producers, similar to Advanced Message Queuing Protocol's definition, are applications which create and send a job (or a message). They are to be used by the <em>consumers</em>.</p>

<h3 id="consumers-receivers">Consumers / Receivers</h3>

<hr />

<p>Receivers are different applications of the stack which get a <em>job</em> from the <em>tube</em>, created by a producer for processing.</p>

<h2 id="installing-beanstalkd-on-ubuntu-13">Installing Beanstalkd on Ubuntu 13</h2>

<hr />

<p>It is possible to very simply obtain Beanstalkd through package manager <code>aptitude</code> and get started. However, in a few commands, you can also download it and install it from the source.</p>

<p><strong>Note:</strong> We will be performing our installations and perform the actions listed here on a fresh and newly created droplet for various reasons. If you are actively serving clients and might have modified your system, to not to break anything working and to not to run in to issues, you are highly advised to try the following instructions on a new system.</p>

<h3 id="installing-using-aptitude">Installing Using aptitude</h3>

<hr />

<p>Run the following command to download and install Beanstalkd:</p>
<pre class="code-pre "><code langs="">aptitude install -y beanstalkd
</code></pre>
<p>Edit the default configuration using <code>nano</code> for launch at system boot:</p>
<pre class="code-pre "><code langs="">nano /etc/default/beanstalkd
</code></pre>
<p>After opening the file, scroll down to the bottom and find the line <code>#START=yes</code>. Change it to:</p>
<pre class="code-pre "><code langs="">START=yes
</code></pre>
<p>Press CTRL+X and confirm with Y to save and exit.</p>

<p>To start using the application, please skip to the next section or follow along to see how to install Beanstalkd from source.</p>

<h3 id="installing-from-source">Installing from Source</h3>

<hr />

<p>We are going to need a key tool for the installation process from source - Git.</p>

<p>Run the following to get Git on your droplet:</p>
<pre class="code-pre "><code langs="">aptitude install -y git
</code></pre>
<p>Download the essential development tools package:</p>
<pre class="code-pre "><code langs="">aptitude install -y build-essential
</code></pre>
<p>Using Git let's clone (download) the official repository:</p>
<pre class="code-pre "><code langs="">git clone https://github.com/kr/beanstalkd
</code></pre>
<p>Enter the downloaded directory:</p>
<pre class="code-pre "><code langs="">cd beanstalkd
</code></pre>
<p>Build the application from source:</p>
<pre class="code-pre "><code langs="">make
</code></pre>
<p>Install:</p>
<pre class="code-pre "><code langs="">make install
</code></pre>
<h2 id="using-beanstalkd">Using Beanstalkd</h2>

<hr />

<p>Upon installing, you can start working with the Beanstalkd server. Here are the options for running the daemon:</p>
<pre class="code-pre "><code langs=""> -b DIR   wal directory
 -f MS    fsync at most once every MS milliseconds (use -f0 for "always fsync")
 -F       never fsync (default)
 -l ADDR  listen on address (default is 0.0.0.0)
 -p PORT  listen on port (default is 11300)
 -u USER  become user and group
 -z BYTES set the maximum job size in bytes (default is 65535)
 -s BYTES set the size of each wal file (default is 10485760)
            (will be rounded up to a multiple of 512 bytes)
 -c       compact the binlog (default)
 -n       do not compact the binlog
 -v       show version information
 -V       increase verbosity
 -h       show this help
</code></pre>
<h3 id="example-usage">Example Usage:</h3>

<hr />
<pre class="code-pre "><code langs=""># Usage: beanstalkd -l [ip address] -p [port #]
# For local only access:
beanstalkd -l 127.0.0.1 -p 11301 &
</code></pre>
<h3 id="managing-the-service">Managing The Service:</h3>

<hr />

<p>If installed through the package manager (i.e. aptitude), you will be able to manage the Beanstalkd daemon as a service.</p>
<pre class="code-pre "><code langs=""># To start the service:
service beanstalkd start

# To stop the service:
service beanstalkd stop

# To restart the service:
service beanstalkd restart

# To check the status:
service beanstalkd status
</code></pre>
<h2 id="obtaining-beanstalkd-client-libraries">Obtaining Beanstalkd Client Libraries</h2>

<hr />

<p>Beanstalkd comes with a long list of support client libraries to work with many different application deployments. This list of support languages - <em>and frameworks</em> - include:</p>

<ul>
<li><p>Python</p></li>
<li><p>Django</p></li>
<li><p>Go</p></li>
<li><p>Java</p></li>
<li><p>Node.js</p></li>
<li><p>Perl</p></li>
<li><p>PHP</p></li>
<li><p>Ruby</p></li>
<li><p>and more.</p></li>
</ul>

<p>For a full list of support languages and installation instructions for your favourite, check out the <a href="https://github.com/kr/beanstalkd/wiki/client-libraries">client libraries</a> page on Github for Beanstalkd.</p>

<h2 id="working-with-beanstalkd">Working with Beanstalkd</h2>

<hr />

<p>In this section - before completing the article - let's quickly go over basic usage of Beanstalkd. In our examples, we will be working with the Python language and Beanstald's Python bindings - <strong><em>beanstalkc</em></strong>.</p>

<p>To install beanstalkc, run the following commands:</p>
<pre class="code-pre "><code langs="">pip install pyyaml
pip install beanstalkc
</code></pre>
<h3 id="basic-operations">Basic Operations</h3>

<hr />

<p>In all your Python files in which you are thinking of working with Beanstalkd, you need to import beanstalkc and connect:</p>
<pre class="code-pre "><code langs="">import beanstalkc

# Connection
beanstalk = beanstalkc.Connection(host='localhost', port=11301)
</code></pre>
<p>To enqueue a job:</p>
<pre class="code-pre "><code langs="">beanstalk.put('job_one')
</code></pre>
<p>To receive a job:</p>
<pre class="code-pre "><code langs="">job = beanstalk.reserve()
# job.body == 'job_one'
</code></pre>
<p>To delete a job after processing it:</p>
<pre class="code-pre "><code langs="">job.delete()
</code></pre>
<p>To use a specific tube (i.e. queue / list):</p>
<pre class="code-pre "><code langs="">beanstalk.use('tube_a')
</code></pre>
<p>To list all available tubes:</p>
<pre class="code-pre "><code langs="">beanstalk.tubes()
# ['default', 'tube_a']
</code></pre>
<p>Final example (<code>nano btc_ex.py</code>):</p>
<pre class="code-pre "><code langs="">import beanstalkc

# Connect
beanstalk = beanstalkc.Connection(host='localhost', port=11301)

# See all tubes:
beanstalk.tubes()

# Switch to the default (tube):
beanstalk.use('default')

# To enqueue a job:
beanstalk.put('job_one')

# To receive a job:
job = beanstalk.reserve()

# Work with the job:
print job.body

# Delete the job: 
job.delete()
</code></pre>
<p>Press CTRL+X and confirm with Y to save and exit.</p>

<p>When you run the above script, you should see the job's body being printed:</p>
<pre class="code-pre "><code langs="">python btc_ex.py
# job_one
</code></pre>
<p>To see more about beanstalkd (and beanstalkc) operations, check out its <a href="http://beanstalkc.readthedocs.org/en/latest/tutorial.html">Getting Started</a> tutorial.</p>

<div class="author">Submitted by: <a href="https://twitter.com/ostezer">O.S. Tezer</a></div>

    