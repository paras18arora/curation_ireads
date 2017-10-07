<link rel='stylesheet' href='digital_ocean.css'><br><img src=https://community-cdn-digitalocean-com.global.ssl.fastly.net/assets/tutorials/images/large/12192013RabbitCelery_twitter.png?1426699619/> <br> 
      <h3 id="introduction">Introduction</h3>

<hr />

<p>Asynchronous, or non-blocking, processing is a method of separating the execution of certain tasks from the main flow of a program.  This provides you with several advantages, including allowing your user-facing code to run without interruption.</p>

<p>Message passing is a method which program components can use to communicate and exchange information.  It can be implemented synchronously or asynchronously and can allow discrete processes to communicate without problems.  Message passing is often implemented as an alternative to traditional databases for this type of usage because message queues often implement additional features, provide increased performance, and can reside completely in-memory.</p>

<p><strong>Celery</strong> is a task queue that is built on an asynchronous message passing system.  It can be used as a bucket where programming tasks can be dumped.  The program that passed the task can continue to execute and function responsively, and then later on, it can poll celery to see if the computation is complete and retrieve the data.</p>

<p>While celery is written in Python, its protocol can be implemented in any language.  It can even function with other languages through webhooks.</p>

<p>By implementing a job queue into your program's environment, you can easily offload tasks and continue to handle interactions from your users.  This is a simple way to increase the responsiveness of your applications and not get locked up while performing long-running computations.</p>

<p>In this guide, we will install and implement a celery job queue using RabbitMQ as the messaging system on an Ubuntu 12.04 VPS.</p>

<h2 id="install-the-components">Install the Components</h2>

<hr />

<h3 id="install-celery">Install Celery</h3>

<hr />

<p>Celery is written in Python, and as such, it is easy to install in the same way that we handle regular Python packages.</p>

<p>We will follow the recommended procedures for handling Python packages by creating a virtual environment to install our messaging system.  This helps us keep our environment stable and not effect the larger system.</p>

<p>Install the Python virtual environment package from Ubuntu's default repositories:</p>
<pre class="code-pre "><code langs="">sudo apt-get update
sudo apt-get install python-virtualenv
</code></pre>
<p>We will create a messaging directory where we will implement our system:</p>
<pre class="code-pre "><code langs="">mkdir ~/messaging
cd ~/messaging
</code></pre>
<p>We can now create a virtual environment where we can install celery by using the following command:</p>
<pre class="code-pre "><code langs="">virtualenv --no-site-packages venv
</code></pre>
<p>With the virtual environment configured, we can activate it by typing:</p>
<pre class="code-pre "><code langs="">source venv/bin/activate
</code></pre>
<p>Your prompt will change to reflect that you are now operating in the virtual environment we made above.  This will ensure that our Python packages are installed locally instead of globally.</p>

<p>If at any time we need to deactivate the environment (not now), you can type:</p>
<pre class="code-pre "><code langs="">deactivate
</code></pre>
<p>Now that we have activated the environment, we can install celery with pip:</p>
<pre class="code-pre "><code langs="">pip install celery
</code></pre>
<h3 id="install-rabbitmq">Install RabbitMQ</h3>

<hr />

<p>Celery requires a messaging agent in order to handle requests from an external source.  This agent is referred to as a "broker".</p>

<p>There are quite a few options for brokers available to choose from, including relational databases, NoSQL databases, key-value stores, and actual messaging systems.</p>

<p>We will be configuring celery to use the RabbitMQ messaging system, as it provides robust, stable performance and interacts well with celery.  It is a great solution because it includes features that mesh well with our intended use.</p>

<p>We can install RabbitMQ through Ubuntu's repositories:</p>
<pre class="code-pre "><code langs="">sudo apt-get install rabbitmq-server
</code></pre>
<p>The RabbitMQ service is started automatically on our server upon installation.</p>

<h2 id="create-a-celery-instance">Create a Celery Instance</h2>

<hr />

<p>In order to use celery's task queuing capabilities, our first step after installation must be to create a celery instance.  This is a simple process of importing the package, creating an "app", and then setting up the tasks that celery will be able to execute in the background.</p>

<p>Let's create a Python script inside our messaging directory called <code>tasks.py</code> where we can define tasks that our workers can perform.</p>
<pre class="code-pre "><code langs="">sudo nano ~/messaging/tasks.py
</code></pre>
<p>The first thing we should do is import the Celery function from the celery package:</p>
<pre class="code-pre "><code langs="">from celery import Celery
</code></pre>
<p>After that, we can create a celery application instance that connects to the default RabbitMQ service:</p>
<pre class="code-pre "><code langs="">from celery import Celery

app = Celery('tasks', backend='amqp', broker='amqp://')
</code></pre>
<p>The first argument to the <code>Celery</code> function is the name that will be prepended to tasks to identify them.</p>

<p>The <code>backend</code> parameter is an optional parameter that is necessary if you wish to query the status of a background task, or retrieve its results.</p>

<p>If your tasks are simply functions that do some work and then quit, without returning a useful value to use in your program, you can leave this parameter out.  If only some of your tasks require this functionality, enable it here and we can disable it on a case-by-case basis further on.</p>

<p>The <code>broker</code> parameter specifies the URL needed to connect to our broker.  In our case, this is the RabbitMQ service that is running on our server.  RabbitMQ operates using a protocol called "amqp".  If RabbitMQ is operating under its default configuration, celery can connect with no other information other than the <code>amqp://</code> scheme.</p>

<h3 id="build-celery-tasks">Build Celery Tasks</h3>

<hr />

<p>Still in this file, we now need to add our tasks.</p>

<p>Each celery task must be introduced with the decorator <code>@app.task</code>.  This allows celery to identify functions that it can add its queuing functions to.  After each decorator, we simply create a function that our workers can run.</p>

<p>Our first task will be a simple function that prints out a string to console.</p>
<pre class="code-pre "><code langs="">from celery import Celery

app = Celery('tasks', backend='amqp', broker='amqp://')

@app.task
def print_hello():
    print 'hello there'
</code></pre>
<p>Because this function does not return any useful information (it instead prints it to the console), we can tell celery to not use the backend to store state information about this task.  This is less complicated under the hood and requires fewer resources.</p>

<pre>
from celery import Celery

app = Celery('tasks', backend='amqp', broker='amqp://')

<a href="https://indiareads/community/users/app" class="username-tag">@app</a>.task<span class="highlight">(ignore_result=True)</span>
def print_hello():
    print 'hello there'
</pre>

<p>Next, we will add another function that will generate prime numbers (taken from <a href="http://rosettacode.org/wiki/Sieve_of_Eratosthenes">RosettaCode</a>).  This can be a long-running process, so it is a good example for how we can deal with asynchronous worker processes when we are waiting for a result.</p>
<pre class="code-pre "><code langs="">from celery import Celery

app = Celery('tasks', backend='amqp', broker='amqp://')

@app.task(ignore_result=True)
def print_hello():
    print 'hello there'

@app.task
def gen_prime(x):
    multiples = []
    results = []
    for i in xrange(2, x+1):
        if i not in multiples:
            results.append(i)
            for j in xrange(i*i, x+1, i):
                multiples.append(j)
    return results
</code></pre>
<p>Because we care about what the return value of this function is, and because we want to know when it has completed (so that we may use the results, etc), we do not add the <code>ignore_result</code> parameter to this second task.</p>

<p>Save and close the file.</p>

<h2 id="start-celery-worker-processes">Start Celery Worker Processes</h2>

<hr />

<p>We can now start a worker processes that will be able to accept connections from applications.  It will use the file we just created to learn about the tasks it can perform.</p>

<p>Starting a worker instance is as easy as calling out the application name with the celery command.  We will include a "&" character at the end of our string to put our worker process in the background:</p>
<pre class="code-pre "><code langs="">celery worker -A tasks &
</code></pre>
<p>This will start up an application, and then detach it from the terminal, allowing you to continue to use it for other tasks.</p>

<p>If you want to start multiple workers, you can do so by naming each one with the <code>-n</code> argument:</p>
<pre class="code-pre "><code langs="">celery worker -A tasks -n one.%h &
celery worker -A tasks -n two.%h &
</code></pre>
<p>The <code>%h</code> will be replaced by the hostname when the worker is named.</p>

<p>To stop workers, you can use the kill command.  We can query for the process id and then eliminate the workers based on this information.</p>
<pre class="code-pre "><code langs="">ps auxww | grep 'celery worker' | awk '{print $2}' | xargs kill
</code></pre>
<p>This will allow the worker to complete its current task before exiting.</p>

<p>If you wish to shut down all workers without waiting for them to complete their tasks, you can execute:</p>
<pre class="code-pre "><code langs="">ps auxww | grep 'celery worker' | awk '{print $2}' | xargs kill -9
</code></pre>
<h2 id="use-the-queue-to-handle-work">Use the Queue to Handle Work</h2>

<hr />

<p>We can use the worker process(es) we spawned to complete work in the background for our programs.</p>

<p>Instead of creating an entire program to demonstrate how this works, we will explore the different options in a Python interpreter:</p>
<pre class="code-pre "><code langs="">python
</code></pre>
<p>At the prompt, we can import our functions into the environment:</p>
<pre class="code-pre "><code langs="">from tasks import print_hello
from tasks import gen_prime
</code></pre>
<p>If you test these functions, they appear to not have any special functionality.  The first function prints a line as expected:</p>
<pre class="code-pre "><code langs="">print_hello()
</code></pre>
<hr />
<pre class="code-pre "><code langs="">hello there
</code></pre>
<p>The second function returns a list of prime numbers:</p>
<pre class="code-pre "><code langs="">primes = gen_prime(1000)
print primes
</code></pre>
<p>If we give the second function a larger range of numbers to check, the execution hangs while it calculates:</p>
<pre class="code-pre "><code langs="">primes = gen_prime(50000)
</code></pre>
<p>Stop the execution by typing "CTRL-C".  This process is clearly not computing in the background.</p>

<p>To access the background worker, we need to use the <code>.delay</code> method.  Celery wraps our functions with additional capabilities.  This method is used to pass the function to a worker to execute.  It should return immediately:</p>
<pre class="code-pre "><code langs="">primes = gen_prime.delay(50000)
</code></pre>
<p>This task is now being executed by the workers we started earlier.  Because we configured a <code>backend</code> parameter for our application, we can check the status of the computation and get access to the result.</p>

<p>To check whether the task is complete, we can use the <code>.ready</code> method:</p>
<pre class="code-pre "><code langs="">primes.ready()
</code></pre>
<hr />
<pre class="code-pre "><code langs="">False
</code></pre>
<p>A value of "False" means that the task is still running and a result is not available yet.  When we get a value of "True", we can do something with the answer.</p>
<pre class="code-pre "><code langs="">primes.ready()
</code></pre>
<hr />
<pre class="code-pre "><code langs="">True
</code></pre>
<p>We can get the value by using the <code>.get</code> method.</p>

<p>If we have already verified that the value is computed with the <code>.ready</code> method, then we can use that method like this:</p>
<pre class="code-pre "><code langs="">print primes.get()
</code></pre>
<hr />
<pre class="code-pre "><code langs="">[2, 3, 5, 7, 11, 13, 17, 19, 23, 29, 31, 37, 41, 43, 47, 53, 59, 61, 67, 71, 73, 79, 83, 89, 97, 101, 103, 107, 109, 113, 127, 131, 137, 139, 149, 151, 157, 163, 167, 173, 179, 181, 191, 193, 197, 199, 211, 223, 227, 229, 233, 239, 241, 251, 257, 263, 269, 271, 277, 281, 283, 293, 307, 311, 313, 317, 331, 337, 347, 349, 353, 359, 367, 373, 379, 383, 389, 397, 401, 409, 419, 421, 431, 433, 439, 443, 449, 457, 461, 463, 467, 479, 487, 491, 499, 503, 509, 521, 523,
. . .
</code></pre>
<p>If, however, you have not used the <code>.ready</code> method prior to calling <code>.get</code>, you most likely want to add a "timeout" option so that your program isn't forced to wait for the result, which would defeat the purpose of our implementation:</p>
<pre class="code-pre "><code langs="">print primes.get(timeout=2)
</code></pre>
<p>This will raise an exception if it times out, which you can handle in your program.</p>

<h2 id="conclusion">Conclusion</h2>

<hr />

<p>Although this is enough information to get you started on using celery within your programs, it is only scratching the surface on the full functionality of this library.  Celery allows you to string background tasks together, group tasks, and combine functions in interesting ways.</p>

<p>Although celery is written in Python, it can be used with other languages through webhooks.  This makes it incredibly flexible for moving tasks into the background, regardless of your chosen language.</p>

<div class="author">By Justin Ellingwood</div>

    