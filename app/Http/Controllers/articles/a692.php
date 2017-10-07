<link rel='stylesheet' href='digital_ocean.css'><br> <br> 
      <h3 id="introduction">Introduction</h3>

<hr />

<p>There are many ways you can choose to describe ZeroMQ; nevertheless, it remains as what it really is: a truly remarkable communication library that benefits the developers greatly with its rich and mature feature set. </p>

<p>In this second installment of IndiaReads ZeroMQ articles, following our previous one on the installation of the application, we are going to dive into its usage and discover ways to actually implement this fast and powerful library. We'll make our way through various examples divided into successive sections, beginning with simple messaging between processes (i.e. using the request/response pattern).</p>

<p><strong>Note:</strong> This article constitutes our second piece on the subject. If you are interested in learning more about it (i.e. what it is and how it compares to a complete message broker), check out <a href="https://indiareads/community/articles/how-to-install-zeromq-from-source-on-a-centos-6-x64-vps">ZeroMQ Introduction and Installation How-to</a> before reading this tutorial.</p>

<h2 id="about">About</h2>

<hr />

<h3 id="zeromq">ZeroMQ</h3>

<hr />

<p>ZeroMQ is a library used to implement messaging and communication systems between applications and processes - fast and asynchronously. </p>

<p>If you have past experience with other application messaging solutions such as RabbitMQ, it might come a little bit challenging to understand the exact position of ZeroMQ.</p>

<p>When compared to some much larger projects, which offer all necessary parts of enterprise messaging, ZeroMQ remains as just a lightweight and fast tool to craft your own.</p>

<h3 id="this-article">This Article</h3>

<hr />

<p>Although technically not a framework, given its functionality and the key position it has for the tasks it solves, you can consider ZeroMQ to be the backbone for implementing the actual communication layer of your application.</p>

<p>In this article, we aim to offer you some examples to inspire you with all the things <em>you</em> can do.</p>

<blockquote>
<p><strong>Note:</strong> We will be working with the Python language and its classic interpreter (Python C interpreter) in our examples. After installing the necessary language bindings, you should be able to simply translate the code and use your favorite instead without any issues. If you would like to learn about installing Python on a CentOS VPS, check out our <a href="https://indiareads/community/articles/how-to-set-up-python-2-7-6-and-3-3-3-on-centos-6-4">How to set up Python 2.7 on CentOS 6.4</a> tutorial.</p>
</blockquote>

<h2 id="programming-with-zeromq">Programming with ZeroMQ</h2>

<hr />

<p>ZeroMQ as a library works through sockets by following certain network communication patterns. It is designed to work asynchronously, and that's where the MQ suffix to its name comes - from thread queuing messages before sending them.</p>

<h2 id="zeromq-socket-types">ZeroMQ Socket Types</h2>

<hr />

<p>ZeroMQ differs in the way its sockets work. Unlike the synchronous way the regular sockets work, ZeroMQ's socket implementation "present an abstraction of an asynchronous message queue".</p>

<p>The way these sockets work depend on the type of socket chosen. And flow of messages being sent depend on the chosen patterns, of which there are four: </p>

<ul>
<li><p><strong>Request/Reply Pattern:</strong> Used for sending a request and receiving subsequent replies for each one sent.</p></li>
<li><p><strong>Publish/Subscribe Pattern:</strong> Used for distributing data from a single process (e.g. publisher) to multiple recipients (e.g. subscribers).</p></li>
<li><p><strong>Pipeline Pattern:</strong> Used for distributing data to connected nodes.</p></li>
<li><p><strong>Exclusive Pair Pattern:</strong> Used for connecting two peers together, forming a pair.</p></li>
</ul>

<h2 id="zeromq-transport-types">ZeroMQ Transport Types</h2>

<hr />

<p>ZeroMQ offers four different types of transport for communication. These are:</p>

<ul>
<li><p><strong>In-Process (INPROC):</strong> Local (in-process) communication transport.</p></li>
<li><p><strong>Inter-Process (IPC):</strong> Local (inter-process) communication transport.</p></li>
<li><p><strong>TCP:</strong> Unicast communication transport using TCP.</p></li>
<li><p><strong>PGM:</strong> Multicast communication transport using PGM.</p></li>
</ul>

<h2 id="structuring-zeromq-applications">Structuring ZeroMQ Applications</h2>

<hr />

<p>ZeroMQ works differently than typical and traditional communication set ups. It can have either side of the link (i.e. either the server or the client) bind and wait for connections. Unlike standard sockets, ZeroMQ works by the notion of knowing that a connection might occur and hence, can wait for it perfectly well.</p>

<h2 id="client-server-structure">Client - Server Structure</h2>

<hr />

<p>For structuring your client and server code, it would be for the best to decide and elect one that is more stable as the <em>binding</em> side and the other(s) as the <em>connecting</em>.</p>

<p>Example:</p>
<pre class="code-pre "><code langs="">Server Application                           Client Application
---------------------[ < .. < .. < .. < .. ......................
Bound -> Port:8080                          Connects <- Port:8080
</code></pre>
<h2 id="client-proxy-server-structure">Client - Proxy - Server Structure</h2>

<hr />

<p>To solve the problems caused by both ends of the communication being in a dynamic (hence unstable) state, ZeroMQ provides networking devices (i.e. utensils out of the box). These devices connect to two different ports and route the connections across.</p>

<ul>
<li><strong>Streamer:</strong> A streamer device for pipelined parallel communications.</li>
<li><strong>Forwarder:</strong> A forwarding device for pub/sub communications.</li>
<li><strong>Queue:</strong> A forwarding device for request/reply communications.</li>
</ul>

<p>Example:</p>
<pre class="code-pre "><code langs="">   Server App.            Device | Forward           Client App.
  ............ > .. > . ]------------------[ < .. < .. .........
    Connects               2 Port Binding             Connects
</code></pre>
<h2 id="programming-examples">Programming Examples</h2>

<hr />

<p>Using our knowledge from the past section, we will now begin utilizing them to create simple applications.</p>

<p><strong>Note:</strong> Below examples usually consist of applications running simultaneously. For example, for a client/server setup to work, you will need to have both the client and the server application running together. One of the ways to do this is by using the tool Linux Screen. To learn more about it, check out this <a href="https://indiareads/community/articles/how-to-install-and-use-screen-on-an-ubuntu-cloud-server">IndiaReadsTutorial</a>. To install screen on a CentOS system, remember that you can simply run: <code>yum install -y screen</code>.</p>

<h2 id="simple-messaging-using-request-reply-pattern">Simple Messaging Using Request/Reply Pattern</h2>

<hr />

<p>In terms of communicating between applications, the request/reply pattern probably forms the absolute classic and gives us a good chance to start with the fundamental basics of ZeroMQ.</p>

<p>Use-cases:</p>

<ul>
<li><p>For simple communications between a server and client(s).</p></li>
<li><p>Checking information and requesting updates.</p></li>
<li><p>Sending <em>checks</em> and updates to the server. </p></li>
<li><p>Echo or ping/pong implementations.</p></li>
</ul>

<p>Socket type(s) used:</p>

<ul>
<li>zmq.REP</li>
<li>zmq.REQ</li>
</ul>

<h3 id="server-example-server-py">Server Example: server.py</h3>

<hr />

<p>Create a "server.py" using <strong>nano</strong> (<code>nano server.py</code>) and paste the below self-explanatory contents.</p>
<pre class="code-pre "><code langs="">import zmq

# ZeroMQ Context
context = zmq.Context()

# Define the socket using the "Context"
sock = context.socket(zmq.REP)
sock.bind("tcp://127.0.0.1:5678")

# Run a simple "Echo" server
while True:
    message = sock.recv()
    sock.send("Echo: " + message)
    print "Echo: " + message
</code></pre>
<p>When you are done editing, save and e xit by pressing CTRL+X followed with Y.</p>

<h3 id="client-example-client-py">Client Example: client.py</h3>

<hr />

<p>Create a "client.py" using <strong>nano</strong> (<code>nano client.py</code>) and paste the below contents.</p>
<pre class="code-pre "><code langs="">import zmq
import sys

# ZeroMQ Context
context = zmq.Context()

# Define the socket using the "Context"
sock = context.socket(zmq.REQ)
sock.connect("tcp://127.0.0.1:5678")

# Send a "message" using the socket
sock.send(" ".join(sys.argv[1:]))
print sock.recv()
</code></pre>
<p>When you are done editing, save and exit by pressing CTRL+X  followed with Y.</p>

<p><strong>Note:</strong> When working with ZeroMQ library, remember that each thread used to send a message (i.e. <code>.send(..)</code>) expects a <code>.recv(..)</code> to follow. Failing to implement the pair will cause exceptions.</p>

<h3 id="usage">Usage</h3>

<hr />

<p>Our <code>server.py</code> is set to work as an "echoing" application. Whatever we choose to send to it, it will send it back (e.g. "Echo: <em>message</em>").</p>

<p>Run the server using your Python interpreter:</p>
<pre class="code-pre "><code langs="">python server.py
</code></pre>
<p>On another window, send messages using the client application:</p>
<pre class="code-pre "><code langs="">python client.py hello world!
# Echo: hello world!
</code></pre>
<p><strong>Note:</strong> To shut down the server, you can use the key combination: Ctrl+C</p>

<h2 id="working-with-publish-subscribe-pattern">Working with Publish/Subscribe Pattern</h2>

<hr />

<p>In the case of publish/subscribe pattern, ZeroMQ is used to establish one or more subscribers, connecting to one or more publishers and receiving continuously what publisher sends (or <em>seeds</em>).</p>

<p>A choice to specify a prefix to accept only such messages beginning with it is available with this pattern.</p>

<p>Use-cases:</p>

<p>Publish/subscribe pattern is used for evenly distributing messages across various consumers. Automatic updates for scoreboards and news can be considered as possible areas to use this solution.</p>

<p>Socket type(s) used:</p>

<ul>
<li>zmq.PUB</li>
<li>zmq.SUB</li>
</ul>

<h3 id="publisher-example-pub-py">Publisher Example: pub.py</h3>

<hr />

<p>Create a "pub.py" using <strong>nano</strong> (<code>nano pub.py</code>) and paste the below contents.</p>
<pre class="code-pre "><code langs="">import zmq
import time

# ZeroMQ Context
context = zmq.Context()

# Define the socket using the "Context"
sock = context.socket(zmq.PUB)
sock.bind("tcp://127.0.0.1:5680")

id = 0

while True:
    time.sleep(1)
    id, now = id+1, time.ctime()

    # Message [prefix][message]
    message = "1-Update! >> #{id} >> {time}".format(id=id, time=now)
    sock.send(message)

    # Message [prefix][message]
    message = "2-Update! >> #{id} >> {time}".format(id=id, time=now) 
    sock.send(message)

    id += 1
</code></pre>
<p>When you are done editing, save and exit by pressing CTRL+X followed with Y.</p>

<h3 id="subscriber-example-sub-py">Subscriber Example: sub.py</h3>

<hr />

<p>Create a "sub.py" using <strong>nano</strong> (<code>nano sub.py</code>) and paste the below contents.</p>
<pre class="code-pre "><code langs="">import zmq

# ZeroMQ Context
context = zmq.Context()

# Define the socket using the "Context"
sock = context.socket(zmq.SUB)

# Define subscription and messages with prefix to accept.
sock.setsockopt(zmq.SUBSCRIBE, "1")
sock.connect("tcp://127.0.0.1:5680")

while True:
    message= sock.recv()
    print message
</code></pre>
<p>When you are done editing, save and exit by pressing CTRL+X followed with Y.</p>

<p><strong>Note:</strong> Using the <code>.setsockopt(..)</code> procedure, we are subscribing to receive messages starting with <em>string</em> <code>1</code>. To receive all, leave it not set (i.e. <code>""</code>).</p>

<h3 id="usage">Usage</h3>

<hr />

<p>Our <code>pub.py</code> is set to work as a <em>publisher</em>, sending two different messages - simultaneously - intended for different subscribers.</p>

<p>Run the publisher to send messages:</p>
<pre class="code-pre "><code langs="">python pub.py
</code></pre>
<p>On another window, see the print outs of subscribed content (i.e. <code>1</code>):</p>
<pre class="code-pre "><code langs="">python sub.py!
# 1-Update! >> 1 >> Wed Dec 25 17:23:56 2013
</code></pre>
<p><strong>Note:</strong> To shut down the subscriber and the publisher applications, you can use the key combination: Ctrl+C</p>

<h3 id="pipelining-the-pub-sub-with-pipeline-pattern-push-pull">Pipelining the Pub./Sub. with Pipeline Pattern (Push/Pull)</h3>

<hr />

<p>Very similar in the way it looks to the Publish/Subscribe pattern, the third in line Pipeline pattern comes as a solution to a different kind of problem: distributing messages upon demand.</p>

<p>Use-cases:</p>

<p>Pipelining pattern can be used in cases where are list of queued items need to be routed (i.e. <em>pushed</em> in line) for the one asking for it (i.e. those who <em>pull</em>).</p>

<p>Socket type(s) used:</p>

<ul>
<li>zmq.PUSH</li>
<li>zmq.PULL</li>
</ul>

<h3 id="push-example-manager-py">PUSH Example: manager.py</h3>

<hr />

<p>Create a "manager.py" using <strong>nano</strong> (<code>nano manager.py</code>) and paste the below contents.</p>
<pre class="code-pre "><code langs="">import zmq
import time

# ZeroMQ Context
context = zmq.Context()

# Define the socket using the "Context"
sock = context.socket(zmq.PUSH)
sock.bind("tcp://127.0.0.1:5690")

id = 0

while True:
    time.sleep(1)
    id, now = id+1, time.ctime()

    # Message [id] - [message]
    message = "{id} - {time}".format(id=id, time=now)

    sock.send(message)

    print "Sent: {msg}".format(msg=message)
</code></pre>
<p>The file <code>manager.py</code> will act as a <em>task allocator</em>.</p>

<h3 id="pull-example-worker_1-py">PULL Example: worker_1.py</h3>

<hr />

<p>Create a "worker<em>1.py" using <strong>nano</strong> (`nano worker</em>1.py`) and paste the below contents.</p>

<p>import zmq</p>
<pre class="code-pre "><code langs=""># ZeroMQ Context
context = zmq.Context()

# Define the socket using the "Context"
sock = context.socket(zmq.PULL)
sock.connect("tcp://127.0.0.1:5690")

while True:
    message = sock.recv()
    print "Received: {msg}".format(msg=message)
</code></pre>
<p>The file <code>worker_1.py</code> will act as a <em>task processes</em> (consumer/worker).</p>

<h3 id="usage">Usage</h3>

<hr />

<p>Our <code>manager.py</code> is set to have a role of an allocator of tasks (i.e. a manager), <strong><em>PUSH</em></strong>ing the items. Likewise, <code>worker_1.py</code> set to work as a <em>worker</em> instance receives these items, when it's done processing by <strong><em>PULL</em></strong>ing down the list.</p>

<p>Run the publisher to send messages:</p>
<pre class="code-pre "><code langs="">python manager.py
</code></pre>
<p>On another window, see the print outs of subscribed content (i.e. <code>1</code>):</p>
<pre class="code-pre "><code langs="">python worker_1.py!
# 1-Update! >> 1 >> Wed Dec 25 17:23:56 2013
</code></pre>
<p><strong>Note:</strong> To shut down the subscriber and the publisher applications, you can use the key combination: Ctrl+C</p>

<h2 id="exclusive-pair-pattern">Exclusive Pair Pattern</h2>

<hr />

<p>Exclusive pair pattern implies and allows establishing one-tone sort of communication channels using the <code>zmq/PAIR</code> socket type.</p>

<h3 id="bind-example-bind-py">Bind Example: bind.py</h3>

<hr />

<p>Create a "bind.py" using <strong>nano</strong> (<code>nano bind.py</code>) and paste the below contents.</p>
<pre class="code-pre "><code langs="">import zmq

# ZeroMQ Context
context = zmq.Context()

# Define the socket using the "Context"
socket = context.socket(zmq.PAIR)
socket.bind("tcp://127.0.0.1:5696")
</code></pre>
<p>When you are done editing, save and exit by pressing CTRL+X followed with Y.</p>

<h3 id="connect-example-connect-py">Connect Example: connect.py</h3>

<hr />

<p>Create a "connect.py" using <strong>nano</strong> (<code>nano connect.py</code>) and paste the below contents.</p>
<pre class="code-pre "><code langs="">import zmq

# ZeroMQ Context
context = zmq.Context()

# Define the socket using the "Context"
socket = context.socket(zmq.PAIR)
socket.connect("tcp://127.0.0.1:5696")
</code></pre>
<p>When you are done editing, save and exit by pressing CTRL+X followed with Y.</p>

<h3 id="usage">Usage</h3>

<hr />

<p>You can use the above example to create any bidirectional uni-connection communication applications. </p>

<p><strong>Note:</strong> To shut down either, you can use the key combination: Ctrl+C</p>

<div class="author">Submitted by: <a href="https://twitter.com/ostezer">O.S. Tezer</a></div>

    