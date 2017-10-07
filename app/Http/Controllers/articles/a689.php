<link rel='stylesheet' href='digital_ocean.css'><br> <br> 
      <h2 id="prerequisites">Prerequisites</h2>

<hr />

<h3 id="rabbitmq">RabbitMQ</h3>

<hr />

<p>Working with RabbitMQ to send and receive messages is possible only after installing and configuring the software itself. <a href="https://indiareads/community/articles/how-to-install-and-manage-rabbitmq">How To Install and Manage RabbitMQ</a> explains in detail how to get RabbitMQ working and is a good starting point for using this message broker.</p>

<h3 id="puka-python-library">Puka Python Library</h3>

<hr />

<p>All examples in this article are presented using Python language backed up with puka library handling the AMQP messaging protocol. Python has been chosen as a clean and easy to understand language for the sake of straightforward presentation, but since AMQP is a widely adopted protocol, any other programming language can be freely used to achieve similar goals.</p>

<p><strong>puka</strong> can be quickly installed using <code>pip</code> -- a Python package manager.</p>
<pre class="code-pre "><code langs="">pip install puka
</code></pre>
<p>pip is not always bundled with Linux distributions. On Debian based distributions (including Ubuntu) it can be easily installed using:</p>
<pre class="code-pre "><code langs="">apt-get install python-pip
</code></pre>
<p>On RHEL based, like CentOS:</p>
<pre class="code-pre "><code langs="">yum install python-setuptools
easy_install pip
</code></pre>
<h2 id="introduction-to-rabbitmq-and-its-terminology">Introduction to RabbitMQ and Its Terminology</h2>

<hr />

<p>Messaging [<strong>RabbitMQ</strong> in particular] introduces a few terms that describe basic principles of the message broker and its mechanics.</p>

<ul>
<li><p><strong>Producer</strong> is a party that <em>sends</em> messages, hence creating a message is producing.</p></li>
<li><p><strong>Consumer</strong> is a party that <em>receives</em> messages, hence receiving a message is consuming.</p></li>
<li><p><strong>Queue</strong> is a buffer in which sent messages are stored and ready to be received. There is no limitation to how many messages a single queue can hold. There is also no limitation as to how many producers can send a message to a queue, nor how many consumers can try to access it. When a message hits the existing queue, it waits there until consumed by a consumer accessing that particular queue. When a message hits a non-existent queue, it gets discarded.</p></li>
<li><p><strong>Exchange</strong> is an entity that resides between producers and queues. The producer never sends a message directly to a queue. It sends messages to an exchange, which - in turn - places the message to one or more queues, depending on the exchange used. To use a real life metaphor, exchange is like a mailman: It handles messages so they get delivered to proper queues (mailboxes), from which consumers can gather them.</p></li>
<li><p><strong>Binding</strong> is a connection between queues and exchanges. Queues bound to a certain exchange are served by the exchange. How exactly depends on the exchange itself.</p></li>
</ul>

<p>All five terms will be used throughout this text. There is one more, strictly related to puka python library, which was chosen as the library of choice for its clarity. It is a <strong>promise</strong>, which may be understood as a synchronous request to the AMQP server that guarantees execution (successful or not) of the request and on which the client waits until it is completed. </p>

<p>While puka can work asynchronously, in our examples puka will be used as a synchronous library. That means after each request (promise) puka will wait until it gets executed before going to the next step.</p>

<h2 id="testing-rabbitmq-and-puka-with-a-simple-example">Testing RabbitMQ and Puka with a Simple Example</h2>

<hr />

<p>To test whether the message broker and puka works perfectly, and to get a grip on how the sending and receiving messages work in practice, create a sample python script named <code>rabbit_test.py</code></p>
<pre class="code-pre "><code langs="">vim rabbit_test.py
</code></pre>
<p>and paste the script contents:</p>
<pre class="code-pre "><code langs="">import puka

# declare send and receive clients, both connecting to the same server on local machine
producer = puka.Client("amqp://localhost/")
consumer = puka.Client("amqp://localhost/")

# connect sending party
send_promise = producer.connect()
producer.wait(send_promise)

# connect receiving party
receive_promise = consumer.connect()
consumer.wait(receive_promise)

# declare queue (queue must exist before it is being used - otherwise messages sent to that queue will be discarded)
send_promise = producer.queue_declare(queue='rabbit')
producer.wait(send_promise)

# send message to the queue named rabbit
send_promise = producer.basic_publish(exchange='', routing_key='rabbit', body='Droplet test!')
producer.wait(send_promise)

print "Message sent!"

# start waiting for messages, also those sent before (!), on the queue named rabbit
receive_promise = consumer.basic_consume(queue='rabbit', no_ack=True)

print "Starting receiving!"

while True:
    received_message = consumer.wait(receive_promise)
    print "GOT: %r" % (received_message['body'],)
    break
</code></pre>
<p>Press <strong>:wq</strong> to save the file and quit.</p>

<p>Running the script should print the message that was sent by the script to the <strong>RabbitMQ</strong> queue, since the test program receives the message immediately afterwards. <br />
The output should look like:</p>
<pre class="code-pre "><code langs="">root@rabbitmq:~# python rabbit_test.py
Message sent!
Starting receiving!
GOT: 'Droplet test!'
root@rabbitmq:~#
</code></pre>
<p>To explain what happens in this code, let's go step by step:</p>

<ol>
<li><p>Both consumer and producer are created and connected to the same RabbitMQ server, residing on <code>localhost</code></p></li>
<li><p>Producer declares a queue, to make sure it exists when the message will be produced. If it weren't for this step, a queue could be non-existent, and therefore messages could get discarded immediately.</p></li>
<li><p>Producer sends the message to a <em>nameless_ exchange</em> (more on exchanges comes later) with a routing key specifying the queue created beforehand. After that the message hits the exchange, which in turn places it in the "rabbit" queue. The message then sits there until someone will consume it.</p></li>
<li><p>Consumer accesses the "rabbit" queue and starts receiving messages stored there. Because there is one message waiting, it will get delivered immediately. It is consumed, which means it will no longer stay in the queue.</p></li>
<li><p>The consumed message gets printed on screen.</p></li>
</ol>

<h2 id="fanout-exchange">Fanout Exchange</h2>

<hr />

<p>In the previous example, a nameless exchange has been used to deliver the message to a particular queue named "rabbit". The nameless exchange needs a queue name to work, which means it can deliver the message only to a single queue.</p>

<p>There are also other types of exchanges in <strong>RabbitMQ</strong>, one of which is <strong>fanout</strong>, our primary concern in this text. Fanout exchange is a simple, blind tool that delivers messages to <strong>ALL</strong> queues it is aware of. With fanout exchange there is no need (in fact - it is impossible) to provide a particular queue name. Messages hitting that kind of exchange are delivered to all queues that are bound to the exchange before the message has been produced. There is no limit to how many queues can be connected to the exchange.</p>

<h2 id="publish-subscribe-pattern">Publish/Subscribe Pattern</h2>

<hr />

<p>With fanout exchange, we can easily create a <em>publish/subscribe</em> pattern, working like an open to all newsletter. Producer, a newsletter broadcaster, sends periodic messages to the audience it may not even know (produces message and sends it to newsletter fanout exchange). New subscribers apply for the newsletter (binds own queue to the same newsletter fanout). From that moment the newsletter fanout exchange will deliver the message to all registered subscribers (queues).</p>

<p>While one-to-one messaging is pretty straightforward and developers often use other means of communication, one-to-many (where "many" is unspecified and can be anything between <em>few</em> and <em>lots</em>) is a very popular scenario in which a message broker can be of immense help.</p>

<h2 id="writing-producer-application">Writing Producer Application</h2>

<hr />

<p>The sole role of producer application is to create a named fanout exchange and produce periodic messages (every few seconds) to that exchange. In a real life scenario, messages would be produced for a reason. To simplify the example, messages will be auto-generated. This application will act as a newsletter publisher.</p>

<p>Create a python script named <code>newsletter_produce.py</code></p>
<pre class="code-pre "><code langs="">vim newsletter_produce.py
</code></pre>
<p>and paste the script contents:</p>
<pre class="code-pre "><code langs="">import puka
import datetime
import time

# declare and connect a producer
producer = puka.Client("amqp://localhost/")
connect_promise = producer.connect()
producer.wait(connect_promise)

# create a fanout exchange
exchange_promise = producer.exchange_declare(exchange='newsletter', type='fanout')
producer.wait(exchange_promise)

# send current time in a loop
while True:
    message = "%s" % datetime.datetime.now()

    message_promise = producer.basic_publish(exchange='newsletter', routing_key='', body=message)
    producer.wait(message_promise)

    print "SENT: %s" % message

    time.sleep(1)

producer.close()
</code></pre>
<p>Let's go step by step with the example to explain what happens in the code.</p>

<ol>
<li><p>Producer client is created and connected to local RabbitMQ instance. From now on it can communicate with RabbitMQ freely.</p></li>
<li><p>A named <code>newsletter</code> fanout exchange is created. After that step, the exchange exists on the RabbitMQ server and can be used to bind queues to it and send messages through it.</p></li>
<li><p>In an endless loop, messages with current time are produced to the <code>newsletter</code> exchange. Note that <code>routing_key</code> is empty, which means there is no particular queue specified. It is the exchange that will deliver message to proper queues further on.</p></li>
</ol>

<p>The application, when running, notifies the current time to all newsletter subscribers.</p>

<h2 id="writing-consumer-application">Writing Consumer Application</h2>

<hr />

<p>Consumer application will create a temporary queue and bind it to a named fanout exchange. After that, it will start waiting for messages. After binding the queue to the exchange, every message sent by the producer created before shall be received by this consumer. This application will act as a newsletter subscriber-- it will be possible to run the application multiple times at once and still all the instances will receive broadcast messages.</p>

<p>Create a python script named <code>newsletter_consume.py</code></p>
<pre class="code-pre "><code langs="">vim newsletter_consume.py
</code></pre>
<p>and paste the script contents:</p>
<pre class="code-pre "><code langs="">import puka

# declare and connect a consumer
consumer = puka.Client("amqp://localhost/")
connect_promise = consumer.connect()
consumer.wait(connect_promise)

# create temporary queue
queue_promise = consumer.queue_declare(exclusive=True)
queue = consumer.wait(queue_promise)['queue']

# bind the queue to newsletter exchange
bind_promise = consumer.queue_bind(exchange='newsletter', queue=queue)
consumer.wait(bind_promise)

# start waiting for messages on the queue created beforehand and print them out
message_promise = consumer.basic_consume(queue=queue, no_ack=True)

while True:
    message = consumer.wait(message_promise)
    print "GOT: %r" % message['body']

consumer.close()
</code></pre>
<p>The consumer code is a bit more complicated than the producer's. Let's look into it step by step:</p>

<ol>
<li><p>Consumer client is created and connected to the local RabbitMQ instance.</p></li>
<li><p>A temporary queue is created. Temporary means that no name is supplied - queue name will be <em>auto-generated</em> by RabbitMQ. Also, such queue will be destroyed after the client disconnects. It is a common way of creating queues that exist only to be bound to one of the exchanges and have no other special purposes. Since it is necessary to create a queue to receive anything, it is a convenient method to avoid thinking about the queue name.</p></li>
<li><p>The created queue is bound to the <code>newsletter</code> exchange. From that moment, the fanout exchange will deliver every message to that queue.</p></li>
<li><p>In an endless loop the consumer waits on the queue, receiving every message that hits the queue and printing it on the screen.</p></li>
</ol>

<p>The application, when running, receives time notifications from the newsletter publisher. It can be executed multiple times at once, and every single instance of this application will get the current time.</p>

<h2 id="testing-both-applications">Testing Both Applications</h2>

<hr />

<p>To test the newsletter publisher and its consumers, open multiple SSH sessions to the virtual server (or open multiple terminal windows, if working on local computer). <br />
In one of the windows run the producer application.</p>
<pre class="code-pre "><code langs="">root@rabbitmq:~# python newsletter_produce.py
</code></pre>
<p>It will start displaying every second the current time:</p>
<pre class="code-pre "><code langs="">SENT: 2014-02-11 17:24:47.309000
SENT: 2014-02-11 17:24:48.310000
SENT: 2014-02-11 17:24:49.312000
SENT: 2014-02-11 17:24:50.316000
...
</code></pre>
<p>In every other window run the consumer application:</p>
<pre class="code-pre "><code langs="">root@rabbitmq:~# python newsletter_consume.py
</code></pre>
<p>Every instance of this application will receive time notifications broadcast by the producer:</p>
<pre class="code-pre "><code langs="">GOT: 2014-02-11 17:24:47.309000
GOT: 2014-02-11 17:24:48.310000
GOT: 2014-02-11 17:24:49.312000
GOT: 2014-02-11 17:24:50.316000
...
</code></pre>
<p>It means that RabbitMQ properly registered the fanout exchange, bound the subscriber queues to this exchange, and delivered sent messages to proper queues. In other words, RabbitMQ worked as expected.</p>

<h2 id="further-reading">Further Reading</h2>

<hr />

<p>Publish/subscribe is a simple (both in concept and to implement) messaging pattern that often may come in handy; it is nowhere near RabbitMQ limits though. There are countless ways to use RabbitMQ to solve messaging problems, including advanced message routing, message acknowledgements, security or persistence. </p>

<p>The primary goal of this text was to introduce basic messaging concepts using simple examples. Many other uses are covered in detail in official <a href="http://www.rabbitmq.com/documentation.html">RabbitMQ documentation</a> which is a great resource for RabbitMQ users and administrators.</p>

<div class="author">Article Submitted by: <a href="http://maticomp.net">Mateusz Papiernik</a></div>

    