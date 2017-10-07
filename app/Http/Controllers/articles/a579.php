<link rel='stylesheet' href='digital_ocean.css'><br> <br> 
      <h2 id="introduction">Introduction</h2>

<p>Python is an excellent language for web programming due to its flexibility and high-level functionality. Web frameworks can make programming web applications much simpler because they connect many of the components necessary for a robust web interface.</p>

<p>While some web frameworks attempt to provide everything, others try to stay out of the way while taking care of the important, difficult to implement issues. <strong>Bottle</strong> is a Python framework that falls into the second category. It is extremely lightweight, but also makes it easy to develop applications quickly.</p>

<p>In this guide, we will cover how to set up and use Bottle to create simple web applications on a CentOS 7 server.</p>

<h2 id="prerequisites">Prerequisites</h2>

<p>Before you begin this guide you'll need the following:</p>

<ul>
<li>A CentOS 7 Droplet</li>
<li>A working knowledge of how to edit text files from the command line</li>
<li>A <a href="https://indiareads/community/tutorials/initial-server-setup-with-centos-7">sudo user</a></li>
</ul>

<h2 id="step-1-—-install-a-virtual-environment-for-python">Step 1 — Install a Virtual Environment for Python</h2>

<p>Python, the programming language that Bottle is built for, comes installed on CentOS by default.</p>

<p>We will install the <code>python-virtualenv</code> package to isolate our Python project from the system's Python environment. The virtualenv software allows us to create a separate, contained environment for our Python projects that will not affect the entire OS.</p>

<p>Update your package lists:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo yum update
</li></ul></code></pre>
<p>Install <code>python-virtualenv</code> from the repositories:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo yum install python-virtualenv
</li></ul></code></pre>
<p>We are going to create a <code>projects</code> folder in our home directory, and then create a virtual environment within this folder:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">mkdir ~/projects
</li><li class="line" prefix="$">cd ~/projects
</li><li class="line" prefix="$">virtualenv --no-site-packages venv
</li></ul></code></pre>
<p>This creates a directory called <code>venv</code> within the <code>projects</code> directory. It installs some Python utilities within this folder and created a directory structure to install additional tools.</p>

<h2 id="step-2-—-activate-the-virtual-environment-for-python">Step 2 — Activate the Virtual Environment for Python</h2>

<p>We must activate the virtual environment before beginning to work on our project:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">source venv/bin/activate
</li></ul></code></pre>
<p>The command prompt will change to reflect the fact that we are operating in a virtual environment now.</p>

<span class="note"><p>If you need to reconnect later, make sure you activate the environment again with these commands:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">cd ~/projects
</li><li class="line" prefix="$">source venv/bin/activate
</li></ul></code></pre>
<p>If you need to exit the virtual environment, you can type this at any time:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">deactivate
</li></ul></code></pre>
<p>Do not deactivate your virtual environment at this point.</p>

<p></p></span>

<h2 id="step-3-—-install-bottle">Step 3 — Install Bottle</h2>

<p>One of the tools that the virtualenv program installed was <code>pip</code>.</p>

<p>This tool allows us to easily install Python packages from the <a href="https://pypi.python.org/pypi">Python package index</a>, an online repository.</p>

<p>If we want to search for Python packages that have to do with Bottle, we can run:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">pip search bottle
</li></ul></code></pre>
<p>We will start by installing the Bottle package:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">pip install bottle
</li></ul></code></pre>
<p>After the process completes, we should have the ability to use the Bottle framework within our applications.</p>

<h2 id="step-4-—-create-your-first-bottle-application">Step 4 — Create Your First Bottle Application</h2>

<p>Bottle, like most frameworks, implements a version of the MVC software pattern. MVC stands for model, view, and controller, and it describes a decision to separate the different functions of a user interface.</p>

<p>The <em>model</em> is a representation of a set of data and is responsible for storing, querying, and updating data. The <em>view</em> describes how information should be rendered to the user. It is used to format and control the presentation of data. The <em>controller</em> is the main processing center of the app, which decides how to respond to user requests.</p>

<p>Bottle applications can be incredibly simple. In their most bare form, they can implement all of these components within a single file. We will create a "hello world" application to show how this is done.</p>

<p>With your favorite text editor, create a Python application called <code>hello.py</code>:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">nano ~/projects/hello.py
</li></ul></code></pre>
<p>We'll show you each line one a time, and include the final file at the end of this section.</p>

<p>Within this file, the first line we will add imports some functionality from the Bottle package. This will allow us to use the framework tools within our application:</p>
<div class="code-label " title="hello.py">hello.py</div><pre class="code-pre "><code langs="">from bottle import route, run
</code></pre>
<p>This line tells our program that we want to import the route and run modules from the Bottle package.</p>

<ul>
<li>The <code>run</code> module that we are importing can be used to run the application on a development server, which is great for quickly seeing the results of your program.</li>
<li>The <code>route</code> module that we are importing is responsible for telling the application what URL requests get handled by which Python functions. Bottle applications implement routing by calling a single Python function for each URL requested. It then returns the results of the function to the user.</li>
</ul>

<p>We can add a route right now that will match the URL pattern <code>/hello</code>. Add one new line at the bottom of the file:</p>
<div class="code-label " title="hello.py">hello.py</div><pre class="code-pre "><code langs="">from bottle import route, run

<span class="highlight">@route('/hello')</span>
</code></pre>
<p>This route decorator matches the URL <code>/hello</code>, so when that path is requested on the server, the function that directly follows will be executed. Add two more lines at the end of the file:</p>
<div class="code-label " title="hello.py">hello.py</div><pre class="code-pre "><code langs="">from bottle import route, run

<a href="https://indiareads/community/users/route" class="username-tag">@route</a>('/hello')
<span class="highlight">def hello():</span>
    <span class="highlight">return "<h1>Hello World!</h1>"</span>
</code></pre>
<p>This function is very simple, but it completes the only requirement of a routing function: it returns a value that can be displayed in the web browser. In this case, the value is a simple HTML string. We could remove the h1 header tags and the same information would be displayed in an undecorated fashion.</p>

<p>Finally, we need to run our application using the development server. Add the final line, and now your file is complete:</p>
<div class="code-label " title="hello.py">hello.py</div><pre class="code-pre "><code langs="">from bottle import route, run

<a href="https://indiareads/community/users/route" class="username-tag">@route</a>('/hello')
def hello():
    return "<h1>Hello World!</h1>"

<span class="highlight">run(host='0.0.0.0', port=8080)</span>
</code></pre>
<p>This line will run the server instance.</p>

<ul>
<li>By passing the parameter <code>host='0.0.0.0'</code>, this will serve the content to any computer, not just the local machine. This is important since our application is being hosted remotely</li>
<li>The <code>port</code> parameter specifies the port that this will be using</li>
</ul>

<p>Save and close the file.</p>

<p>We can run this application with this command:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">python ~/projects/hello.py
</li></ul></code></pre>
<p>You can visit this application in your web browser by going to your IP address, followed by the port we chose to run on (8080), followed by the route we created (/hello):</p>

<ul>
<li><code>http://<span class="highlight">your_server_ip</span>:8080/hello</code></li>
</ul>

<p>It will look like this:</p>

<p><img src="https://assets.digitalocean.com/articles/bottle/hello_world.png" alt="IndiaReads Bottle hello world" /></p>

<p>You can stop the server at any time by typing:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">CTRL-C
</li></ul></code></pre>
<h2 id="step-5-—-create-a-bottle-model">Step 5 — Create a Bottle Model</h2>

<p>We have now implemented our first application. It was simple, but it didn't really implement MVC principles, or do anything particularly interesting. Let's create a slightly more sophisticated application this time.</p>

<p>We'll start with our model. This is the portion of our program that handles the data storage. Bottle can easily implement a variety of backends for data through the use of plugins.</p>

<p>We will use an SQLite database file for our database. This is an extremely simple database designed for lightweight tasks.</p>

<p>SQLite is included in the CentOS 7 default image, but if you ever need to reinstall it, it's as simple as one command:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo yum install sqlite
</li></ul></code></pre>
<p>It should already be installed.</p>

<p>We also need to download and install the Bottle plugin that will allow us to use these databases:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">pip install bottle-sqlite
</li></ul></code></pre>
<p>Now that we have the components, we will create a Python file that will generate a SQLite database with some data. We could do this in the Python interpreter, but making a file makes it easy to repeat.</p>

<p>Create the file:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">nano ~/projects/picnic_data.py
</li></ul></code></pre>
<p>Add this content to the file. It will create a database filled with picnic inventory items:</p>
<div class="code-label " title="picnic_data.py">picnic_data.py</div><pre class="code-pre "><code langs="">import sqlite3
db = sqlite3.connect('picnic.db')
db.execute("CREATE TABLE picnic (id INTEGER PRIMARY KEY, item CHAR(100) NOT NULL, quant INTEGER NOT NULL)")
db.execute("INSERT INTO picnic (item,quant) VALUES ('bread', 4)")
db.execute("INSERT INTO picnic (item,quant) VALUES ('cheese', 2)")
db.execute("INSERT INTO picnic (item,quant) VALUES ('grapes', 30)")
db.execute("INSERT INTO picnic (item,quant) VALUES ('cake', 1)")
db.execute("INSERT INTO picnic (item,quant) VALUES ('soda', 4)")
db.commit()
</code></pre>
<p>In this file, we:</p>

<ul>
<li>Import the SQLite package</li>
<li>Execute a command that creates our table and inserts data</li>
<li>Finally, we commit the changes</li>
</ul>

<p>Save and close the file.</p>

<p>Execute the file, which will create a database file called <code>picnic.db</code> within our current directory:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">python ~/projects/picnic_data.py
</li></ul></code></pre>
<p>If you'd like, you can <code>ls</code> the directory to confirm that the database file was created.</p>

<p>The model portion of our program is now fairly complete. We can see that our model will dictate a little bit how our control portion must function to interact with our data.</p>

<h2 id="6-—-create-a-bottle-controller">6 — Create a Bottle Controller</h2>

<p>Now that we have a database, we can start to develop our main application. This will mainly implement our controller functionality. It will also be the file that most closely resembles our first application.</p>

<p>Create a file called <code>picnic.py</code> to store our main application:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">nano ~/projects/picnic.py
</li></ul></code></pre>
<p>Just like before, we'll explain the file line by line, and show the completed file at the end of the step.</p>

<p>Within this file, we need to import some things from the Bottle package, just like before. We need some additional modules that we haven't used before. In addition, we need to import the SQLite functionality:</p>
<div class="code-label " title="picnic.py">picnic.py</div><pre class="code-pre "><code langs="">import sqlite3
from bottle import route, run, template
</code></pre>
<p>Next, we'll define a route that matches the URL path <code>/picnic</code>:</p>
<div class="code-label " title="picnic.py">picnic.py</div><pre class="code-pre "><code langs="">import sqlite3
from bottle import route, run, template

<span class="highlight">@route('/picnic')</span>
</code></pre>
<p>We'll implement the function that connects to our database, gets our data from the table, and calls our view to render the page.</p>
<div class="code-label " title="picnic.py">picnic.py</div><pre class="code-pre "><code langs="">import sqlite3
from bottle import route, run, template

<a href="https://indiareads/community/users/route" class="username-tag">@route</a>('/picnic')
<span class="highlight">def show_picnic():</span>
    <span class="highlight">db = sqlite3.connect('picnic.db')</span>
    <span class="highlight">c = db.cursor()</span>
    <span class="highlight">c.execute("SELECT item,quant FROM picnic")</span>
    <span class="highlight">data = c.fetchall()</span>
    <span class="highlight">c.close()</span>
    <span class="highlight">output = template('bring_to_picnic', rows=data)</span>
    <span class="highlight">return output</span>
</code></pre>
<ul>
<li>The command that connects to the database is the <code>db = sqlite3.connect('picnic.db')</code> command</li>
<li>We query the database, and select all of our values with the next four lines</li>
<li>The line where we call the view to format our data is <code>output = template('bring_to_picnic', rows=data)</code>. This calls a template (view) called <code>bring_to_picnic.tpl</code> to format the data. It passes the <code>data</code> variable as the template variable <code>rows</code></li>
<li>Finally, it returns formatted output to our user</li>
</ul>

<p>Finally, we need to add our <code>run</code> command to run the actual server:</p>
<div class="code-label " title="picnic.py">picnic.py</div><pre class="code-pre "><code langs="">import sqlite3
from bottle import route, run, template

<a href="https://indiareads/community/users/route" class="username-tag">@route</a>('/picnic')
def show_picnic():
    db = sqlite3.connect('picnic.db')
    c = db.cursor()
    c.execute("SELECT item,quant FROM picnic")
    data = c.fetchall()
    c.close()
    output = template('bring_to_picnic', rows=data)
    return output

<span class="highlight">run(host='0.0.0.0', port=8080)</span>
</code></pre>
<p>Save and close the file.</p>

<p>We will create this template file <code>bring_to_picnic.tpl</code> in the next section.</p>

<h2 id="step-7-—-create-a-bottle-view">Step 7 — Create a Bottle View</h2>

<p>Now that we have our model and controller, the only thing left to create is our view. This is handled easily using Bottle's built-in template engine.</p>

<p>The application will search for a template matching the name given in the template function, ending with <code>.tpl</code>. This can either be in the project's main directory, or in a directory called <code>view</code>.</p>

<p>Create a file matching the one we called with the template function in the <code>output</code> line in the previous script:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">nano ~/projects/bring_to_picnic.tpl
</li></ul></code></pre>
<p>In this file, we can mix HTML and programming. Ours will be very simple. It will use a loop to create a table, which we will populate with our model data. Add all of these lines to the file:</p>
<div class="code-label " title="bring_to_picnic.tpl">bring_to_picnic.tpl</div><pre class="code-pre "><code langs=""><h1>Things to bring to our picnic</h1>

<table>
    <tbody>
        <tr><th>Item</th><th>Quantity</th></tr>
        %for row in rows:
        <tr>
        %for col in row:
            <td>{{col}}</td>
        %end
        </tr>
    %end
    <tbody>
</table>
</code></pre>
<p>This will render our page in HTML.</p>

<ul>
<li>The templating language that we see here is basically Python</li>
<li>The <code>rows</code> variable that we passed to the template is available to use when designing the output</li>
<li>We can type lines of Python by preceding them with <code>%</code></li>
<li>We can access variables within the HTML by using the <code>{{var}}</code> syntax.</li>
</ul>

<p>Save and close the file.</p>

<h2 id="step-8-—-start-the-bottle-application">Step 8 — Start the Bottle Application</h2>

<p>Our application is now complete.</p>

<p>We can start the program by calling Python on the main file:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">python ~/projects/picnic.py
</li></ul></code></pre>
<p>We can see the results by visiting our IP address and port, followed by the URL route we created:</p>

<ul>
<li><code>http://<span class="highlight">your_server_ip</span>:8080/picnic</code></li>
</ul>

<p>Your web page should look like this:</p>

<p><img src="https://assets.digitalocean.com/articles/bottle/mvc_example.png" alt="IndiaReads Bottle mvc example" /></p>

<p>Press <code>CTRL-C</code> to stop the application.</p>

<h2 id="optional-step-9-—-upload-your-own-application">(Optional) Step 9 — Upload Your Own Application</h2>

<p>To upload your own Bottle application, you'll want to copy all the project files to this directory:</p>
<pre class="code-pre "><code langs="">~/projects/
</code></pre>
<p>For example:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">scp <span class="highlight">myproject.py sammy@your_server_ip</span>:~/projects/
</li></ul></code></pre>
<p>Upload <strong>all the files</strong> associated with this project in a similar way. <a href="https://indiareads/community/tutorials/how-to-use-sftp-to-securely-transfer-files-with-a-remote-server">SFTP</a> is a different way to upload files, if you're not familiar with <code>scp</code>.</p>

<h2 id="optional-step-10-—-start-your-own-application">(Optional) Step 10 — Start Your Own Application</h2>

<p>Activate your virtual environment (if you haven't done so already).</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">cd ~/projects/
</li><li class="line" prefix="$">source venv/bin/activate
</li></ul></code></pre>
<p>We'll use the <code>python</code> command to start the application. We'll make it slightly less rudimentary by starting the process in the background, which means you can close your terminal and the app will keep running:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">nohup python ~/projects/<span class="highlight">myproject.py</span> &
</li></ul></code></pre>
<p>In the output, you should see your process ID number and the following message:</p>
<div class="code-label " title="Output">Output</div><pre class="code-pre "><code langs="">[1] <span class="highlight">20301</span>
(venv)[sammy@bottle projects]$ nohup: ignoring input and appending output to ‘nohup.out’
</code></pre>
<p>Once you're ready for production, we highly recommend making a more robust startup plan for your app. (Just starting it in the background means your app will stop after a server reboot.) CentOS 7 uses <a href="https://indiareads/community/tutorials/understanding-systemd-units-and-unit-files">systemd</a>.</p>

<h2 id="conclusion">Conclusion</h2>

<p>By now, you should be able to see how you can build complex applications using a simple, bare-bones micro-framework like Bottle. While our examples were simple, it is easy to take advantage of more advanced functionality.</p>

<p>Bottle's plugin system is also an important asset. Plugins are actively shared within the community and it is easy to implement more complex behavior through the plugin system.</p>

<p>For example, one easy way to find Bottle-compatible plugins is by using the <code>pip search bottle</code> command. This will give you an idea of some of the more popular options.</p>

    