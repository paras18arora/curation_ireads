<link rel='stylesheet' href='digital_ocean.css'><br> <br> 
      <h2 id="getting-started">Getting Started</h2>

<hr />

<h3 id="prerequisites">Prerequisites</h3>

<hr />

<p>This text is a continuation of <a href="https://indiareads/community/articles/how-to-use-rabbitmq-and-python-s-puka-to-deliver-messages-to-multiple-consumers">How To Use RabbitMQ and Python's Puka to Deliver Messages to Multiple Consumers</a> and requires the same software bundle up and running properly. Also, the same definitions are used throughout the article and we assume that the reader is acquainted  with subjects from the former text.</p>

<h3 id="exchanges">Exchanges</h3>

<hr />

<p>We have already described the <code>fanout</code> exchange, that delivers messages to every queue bound to that exchange with no additional rules in place. It is a very useful mechanism, but lacks flexibility. It is often undesirable to receive everything a producer emits to the exchange. <strong>RabbitMQ</strong> offers two different exchange types that can be used to implement more complex scenarios. One of these is <code>direct</code> exchange.</p>

<h2 id="direct-exchange">Direct exchange</h2>

<hr />

<h3 id="introduction">Introduction</h3>

<hr />

<p>Direct exchange offers a simple, key-based routing mechanism in <strong>RabbitMQ</strong>. It is somewhat similar to the nameless exchange used in the very first example, in which a message was delivered to the queue of name equal to the routing key of the message. However, whereas with nameless exchange there was no need to define explicit queue bindings, in direct exchange the bindings are crucial and mandatory.</p>

<p>When using direct exchange, each message produced to that exchange must have a routing key specified, which is an arbitrary name string, e.g. <em>Texas</em>. The message will then be delivered to all queues that have been bound to this exchange with the same routing key  (all queues that were explicitly declared as interested in messages with <em>Texas</em> routing key).</p>

<p>The biggest difference between basic nameless exchange and <code>direct</code> exchange is that the latter needs bindings and no queue listens to messages on that exchange before that. That in turn results in three great advantages.</p>

<ol>
<li><p><strong>One</strong> queue can be bound to listen to <strong>many</strong> different routing keys on the same exchange</p></li>
<li><p><strong>One</strong> queue can be bound to listen on <strong>many</strong> different exchanges at once</p></li>
<li><p><strong>Many</strong> queues can be bound to listen to the <strong>same</strong> routing key on an exchange</p></li>
</ol>

<p>Let's imagine a big city hub: a rail and bus station in one, with many destinations reachable by both means of transportation. And let's imagine that the station wants to dispatch departure notifications using <strong>RabbitMQ</strong>. The task is to inform everyone interested that a bus or train to <em>Seattle</em>, <em>Tooele</em>, or <em>Boston</em> departs soon.</p>

<p>Such a program would define a direct <code>departures</code> exchange to which all interested customers could subscribe their queues. Then messages containing departure time would be produced to that exchange with the routing key containing the destination. For example:</p>

<ol>
<li><p>Message to <code>departures</code> exchange with routing key <code>Tooele</code> and body <code>2014-01-03 15:23</code></p></li>
<li><p>Message to <code>departures</code> exchange with routing key <code>Boston</code> and body <code>2014-01-03 15:41</code></p></li>
<li><p>Message to <code>departures</code> exchange with routing key <code>Seattle</code> and body <code>2014-01-03 15:55</code></p></li>
</ol>

<p>Since one queue may be bound to many routing keys at once, and many queues can be bound to the same key, we could easily have:</p>

<ol>
<li><p>One customer interested in <em>Tooele</em> only</p></li>
<li><p>One customer interested in <em>Boston</em> only</p></li>
<li><p>Another customer interested in <em>Tooele</em> and <em>Boston</em> at the same time</p></li>
</ol>

<p>All waiting for information at the same time. They would receive proper messages using our <strong>direct</strong> exchange.</p>

<h3 id="producer">Producer</h3>

<hr />

<p>To simplify the task slightly for the example, let's write a basic notification dispatcher that will accept one command line parameter. It will specify the destination and the application will send the current time to all interested consumers.</p>

<p>Create a sample python script named <code>direct_notify.py</code></p>
<pre class="code-pre "><code langs="">vim direct_notify.py
</code></pre>
<p>and paste the script contents:</p>
<pre class="code-pre "><code langs="">import puka
import datetime
import time
import sys

# declare and connect a producer
producer = puka.Client("amqp://localhost/")
connect_promise = producer.connect()
producer.wait(connect_promise)

# create a direct exchange named departures
exchange_promise = producer.exchange_declare(exchange='departures', type='direct')
producer.wait(exchange_promise)

# send current time to destination specified with command line argument
message = "%s" % datetime.datetime.now()

message_promise = producer.basic_publish(exchange='departures', routing_key=sys.argv[1], body=message)
producer.wait(message_promise)

print "Departure to %s at %s" % (sys.argv[1], message)

producer.close()
</code></pre>
<p>Press <strong>:wq</strong> to save the file and quit.</p>

<p>Running the script with one parameter should print the current time and used destination. The output should look like:</p>
<pre class="code-pre "><code langs="">root@rabbitmq:~# python direct_notify.py Tooele
Departure to Tooele at 2014-02-18 15:57:29.035000
root@rabbitmq:~#
</code></pre>
<p><strong>Let's go through the script step by step:</strong></p>

<ol>
<li><p><strong>Producer</strong> client is created and connected to local RabbitMQ instance. From now on it can communicate with RabbitMQ freely.</p></li>
<li><p>A named <code>departures</code> direct exchange is created. It does not need routing key specified at creation, as any message published to that exchange can have different key assigned to it. After that step the exchange exists on the RabbitMQ server and can be used to bind queues to it and send messages through it.</p></li>
<li><p>A message containing current time is published to that exchange, using the command line parameter as the routing key. In the sample run Tooele is used as the parameter, and hence as the departure destination - routing key.</p></li>
</ol>

<p><strong>Note:</strong> for simplicity, the script does not check whether the mandatory command line argument is supplied! It will not work properly if executed without parameters.</p>

<h3 id="consumer">Consumer</h3>

<hr />

<p>This example consumer application will act as a public transport customer interested in one or more of the destinations reachable from the station.</p>

<p>Create a sample python script named <code>direct_watch.py</code></p>
<pre class="code-pre "><code langs="">vim direct_watch.py
</code></pre>
<p>and paste the script contents:</p>
<pre class="code-pre "><code langs="">import puka
import sys

# declare and connect a consumer
consumer = puka.Client("amqp://localhost/")
connect_promise = consumer.connect()
consumer.wait(connect_promise)

# create temporary queue
queue_promise = consumer.queue_declare(exclusive=True)
queue = consumer.wait(queue_promise)['queue']

# bind the queue to all routing keys specified by command line arguments
for destination in sys.argv[1:]:
    print "Watching departure times for %s" % destination
    bind_promise = consumer.queue_bind(exchange='departures', queue=queue, routing_key=destination)
    consumer.wait(bind_promise)

# start waiting for messages on the queue created beforehand and print them out
message_promise = consumer.basic_consume(queue=queue, no_ack=True)

while True:
    message = consumer.wait(message_promise)
    print "Departure for %s at %s" % (message['routing_key'], message['body'])

consumer.close()
</code></pre>
<p>Press <strong>:wq</strong> to save the file and quit.</p>

<p>Running the script with one parameter <em>Tooele</em> should announce that the script watches departure times for <em>Tooele</em>, whereas running it with more than one parameter should announce watching departure times for many destinations.</p>
<pre class="code-pre "><code langs="">root@rabbitmq:~# python direct_watch.py Tooele
Watching departure times for Tooele
(...)
root@rabbitmq:~# python direct_watch.py Tooele Boston
Watching departure times for Tooele
Watching departure times for Boston
(...)
root@rabbitmq:~#
</code></pre>
<p><strong>Let's go through the script step by step to explain what it does:</strong></p>

<ol>
<li><p><strong>Consumer</strong> client is created and connected to local RabbitMQ instance. From now on it can communicate with RabbitMQ freely.</p></li>
<li><p>A temporary queue for this particular consumer is created, with auto-generated name by RabbitMQ. The queue will be destroyed after the script ends.</p></li>
<li><p>The queue is bound to all <code>departures</code> exchange on all routing keys (destinations) specified using command line parameters, printing on the screen each destination for information.</p></li>
<li><p>The script starts waiting for messages on the queue. It shall receive all messages matching the bound routing keys. When running with <em>Tooele</em> as a single parameter - only those, when running with both <em>Tooele</em> and <em>Boston</em> - on both of them. Each departure time will be printed on the screen.</p></li>
</ol>

<h3 id="testing">Testing</h3>

<hr />

<p>To check whether both scripts work as expected, open three terminal windows to the server. One will be used as a public transport station to send notifications. Another two will serve as customers waiting for departures.</p>

<p>In the first terminal, run the <code>direct_notify.py</code> script once with any parameter:</p>
<pre class="code-pre "><code langs="">root@rabbitmq:~# python direct_notify.py Tooele
Departure to Tooele at 2014-02-18 15:57:29.035000
root@rabbitmq:~#
</code></pre>
<p><strong>Important:</strong> the <code>direct_notify.py</code> script must be executed at least once before any consumers, as the exchange must be created before binding queues to it. After execution the exchange stays on the RabbitMQ server and can be used freely.</p>

<p>In the second terminal, run the <code>direct_watch.py</code> script with one parameter - <em>Tooele</em>:</p>
<pre class="code-pre "><code langs="">root@rabbitmq:~# python direct_watch.py Tooele
Watching departure times for Tooele
(...)
root@rabbitmq:~#
</code></pre>
<p>In the third terminal, run the <code>direct_watch.py</code> script with two parameters - <em>Tooele</em> and <em>Boston</em>:</p>
<pre class="code-pre "><code langs="">root@rabbitmq:~# python direct_watch.py Tooele Boston
Watching departure times for Tooele
Watching departure times for Boston
(...)
root@rabbitmq:~#
</code></pre>
<p>Then, back in the first terminal, send three departure notifications. One to <em>Tooele</em>, one to <em>Boston</em> and one to <em>Chicago</em>:</p>
<pre class="code-pre "><code langs="">root@rabbitmq:~# python direct_notify.py Tooele
Departure to Tooele at 2014-02-18 15:57:29.035000
root@rabbitmq:~# python direct_notify.py Boston
Departure to Tooele at 2014-02-18 15:57:31.035000
root@rabbitmq:~# python direct_notify.py Chicago
Departure to Tooele at 2014-02-18 15:57:35.035000
root@rabbitmq:~#
</code></pre>
<p>The first notification should be received only by both consumers waiting for departures to Tooele. The second one should get only to the consumer waiting for departures to Boston. The third one should not be received by any of these consumers, since none of them wait for departures to Chicago.</p>

<p>This is the expected behaviour. Those simple examples illustrate how to dispatch messages that only certain consumers specified by a routing key will receive.</p>

<h2 id="further-reading">Further reading</h2>

<hr />

<p>Direct routing does not offer complete control over where the messages will be delivered, but is a big step up from <code>fanout</code> exchange used in previous exchanges that blindly delivers messages everywhere. With <code>direct</code> exchange many real world messaging scenarios can be served and the process is not terribly difficult.</p>

<p>The primary goal of this text was to introduce basic direct routing using a simple, real world situation. Many other uses are covered in detail in official <a href="http://www.rabbitmq.com/documentation.html">RabbitMQ documentation</a> which is a great resource for RabbitMQ users and administrators.</p>

<div class="author">Article Submitted by: <a href="http://maticomp.net">Mateusz Papiernik</a></div>

    