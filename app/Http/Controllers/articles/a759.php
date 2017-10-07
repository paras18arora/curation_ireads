<link rel='stylesheet' href='digital_ocean.css'><br> <br> 
      <h3 id="introduction">Introduction</h3>

<p>In this guide, we will be setting up a simple Python application using the Flask micro-framework on Ubuntu 14.04.  The bulk of this article will be about how to set up the Gunicorn application server to launch the application and Nginx to act as a front end reverse proxy.</p>

<h2 id="prerequisites">Prerequisites</h2>

<p>Before starting on this guide, you should have a non-root user configured on your server.  This user needs to have <code>sudo</code> privileges so that it can perform administrative functions.  To learn how to set this up, follow our <a href="https://indiareads/community/tutorials/initial-server-setup-with-ubuntu-14-04">initial server setup guide</a>.</p>

<p>To learn more about the WSGI specification that our application server will use to communicate with our Flask app, you can read the linked section of <a href="https://indiareads/community/tutorials/how-to-set-up-uwsgi-and-nginx-to-serve-python-apps-on-ubuntu-14-04#definitions-and-concepts">this guide</a>.  Understanding these concepts will make this guide easier to follow.</p>

<p>When you are ready to continue, read on.</p>

<h2 id="install-the-components-from-the-ubuntu-repositories">Install the Components from the Ubuntu Repositories</h2>

<p>Our first step will be to install all of the pieces that we need from the repositories.  We will install <code>pip</code>, the Python package manager, in order to install and manage our Python components.  We will also get the Python development files needed to build some of the Gunicorn components.  We'll install Nginx now as well.</p>

<p>Update your local package index and then install the packages by typing:</p>
<pre class="code-pre "><code langs="">sudo apt-get update
sudo apt-get install python-pip python-dev nginx
</code></pre>
<h2 id="create-a-python-virtual-environment">Create a Python Virtual Environment</h2>

<p>Next, we'll set up a virtual environment in order to isolate our Flask application from the other Python files on the system.</p>

<p>Start by installing the <code>virtualenv</code> package using <code>pip</code>:</p>
<pre class="code-pre "><code langs="">sudo pip install virtualenv
</code></pre>
<p>Now, we can make a parent directory for our Flask project.  Move into the directory after you create it:</p>
<pre class="code-pre "><code langs="">mkdir ~/<span class="highlight">myproject</span>
cd ~/<span class="highlight">myproject</span>
</code></pre>
<p>We can create a virtual environment to store our Flask project's Python requirements by typing:</p>
<pre class="code-pre "><code langs="">virtualenv <span class="highlight">myprojectenv</span>
</code></pre>
<p>This will install a local copy of Python and <code>pip</code> into a directory called <code><span class="highlight">myprojectenv</span></code> within your project directory.</p>

<p>Before we install applications within the virtual environment, we need to activate it.  You can do so by typing:</p>
<pre class="code-pre "><code langs="">source <span class="highlight">myprojectenv</span>/bin/activate
</code></pre>
<p>Your prompt will change to indicate that you are now operating within the virtual environment.  It will look something like this <code>(<span class="highlight">myprojectenv</span>)<span class="highlight">user</span>@<span class="highlight">host</span>:~/<span class="highlight">myproject</span>$</code>.</p>

<h2 id="set-up-a-flask-application">Set Up a Flask Application</h2>

<p>Now that you are in your virtual environment, we can install Flask and Gunicorn and get started on designing our application:</p>

<h3 id="install-flask-and-gunicorn">Install Flask and Gunicorn</h3>

<p>We can use the local instance of <code>pip</code> to install Flask and Gunicorn.  Type the following commands to get these two components:</p>
<pre class="code-pre "><code langs="">pip install gunicorn flask
</code></pre>
<h3 id="create-a-sample-app">Create a Sample App</h3>

<p>Now that we have Flask available, we can create a simple application.  Flask is a micro-framework.  It does not include many of the tools that more full-featured frameworks might, and exists mainly as a module that you can import into your projects to assist you in initializing a web application.</p>

<p>While your application might be more complex, we'll create our Flask app in a single file, which we will call <code>myproject.py</code>:</p>
<pre class="code-pre "><code langs="">nano ~/<span class="highlight">myproject</span>/<span class="highlight">myproject</span>.py
</code></pre>
<p>Within this file, we'll place our application code.  Basically, we need to import flask and instantiate a Flask object.  We can use this to define the functions that should be run when a specific route is requested.  We'll call our Flask application in the code <code>application</code> to replicate the examples you'd find in the WSGI specification:</p>
<pre class="code-pre "><code class="code-highlight language-python">from flask import Flask
application = Flask(__name__)

@application.route("/")
def hello():
    return "<h1 style='color:blue'>Hello There!</h1>"

if __name__ == "__main__":
    application.run(host='0.0.0.0')
</code></pre>
<p>This basically defines what content to present when the root domain is accessed.  Save and close the file when you're finished.</p>

<p>You can test your Flask app by typing:</p>
<pre class="code-pre "><code langs="">python <span class="highlight">myproject</span>.py
</code></pre>
<p>Visit your server's domain name or IP address followed by the port number specified in the terminal output (most likely <code>:5000</code>) in your web browser.  You should see something like this:</p>

<p><img src="https://assets.digitalocean.com/articles/nginx_uwsgi_wsgi_1404/test_app.png" alt="Flask sample app" /></p>

<p>When you are finished, hit CTRL-C in your terminal window a few times to stop the Flask development server.</p>

<h3 id="create-the-wsgi-entry-point">Create the WSGI Entry Point</h3>

<p>Next, we'll create a file that will serve as the entry point for our application.  This will tell our Gunicorn server how to interact with the application. </p>

<p>We will call the file <code>wsgi.py</code>:</p>
<pre class="code-pre "><code langs="">nano ~/<span class="highlight">myproject</span>/wsgi.py
</code></pre>
<p>The file is incredibly simple, we can simply import the Flask instance from our application and then run it:</p>
<pre class="code-pre "><code class="code-highlight language-python">from <span class="highlight">myproject</span> import application

if __name__ == "__main__":
    application.run()
</code></pre>
<p>Save and close the file when you are finished.</p>

<h3 id="testing-gunicorn-39-s-ability-to-serve-the-project">Testing Gunicorn's Ability to Serve the Project</h3>

<p>Before moving on, we should check that Gunicorn can correctly.</p>

<p>We can do this by simply passing it the name of our entry point.  We'll also specify the interface and port to bind to so that it will be started on a publicly available interface:</p>
<pre class="code-pre "><code langs="">cd ~/<span class="highlight">myproject</span>
gunicorn --bind 0.0.0.0:8000 wsgi
</code></pre>
<p>If you visit your server's domain name or IP address with <code>:8000</code> appended to the end in your web browser, you should see a page that looks like this:</p>

<p><img src="https://assets.digitalocean.com/articles/nginx_uwsgi_wsgi_1404/test_app.png" alt="Flask sample app" /></p>

<p>When you have confirmed that it's functioning properly, press CTRL-C in your terminal window.</p>

<p>We're now done with our virtual environment, so we can deactivate it:</p>
<pre class="code-pre "><code langs="">deactivate
</code></pre>
<p>Any operations now will be done to the system's Python environment.</p>

<h2 id="create-an-upstart-script">Create an Upstart Script</h2>

<p>The next piece we need to take care of is the Upstart script.  Creating an Upstart script will allow Ubuntu's init system to automatically start Gunicorn and serve our Flask application whenever the server boots.</p>

<p>Create a script file ending with <code>.conf</code> within the <code>/etc/init</code> directory to begin:</p>
<pre class="code-pre "><code langs="">sudo nano /etc/init/<span class="highlight">myproject</span>.conf
</code></pre>
<p>Inside, we'll start with a simple description of the script's purpose.  Immediately afterwards, we'll define the conditions where this script will be started and stopped by the system.  The normal system runtime numbers are 2, 3, 4, and 5, so we'll tell it to start our script when the system reaches one of those runlevels.  We'll tell it to stop on any other runlevel (such as when the server is rebooting, shutting down, or in single-user mode):</p>
<pre class="code-pre "><code langs="">description "Gunicorn application server running <span class="highlight">myproject</span>"

start on runlevel [2345]
stop on runlevel [!2345]
</code></pre>
<p>We'll tell the init system that it should restart the process if it ever fails.  Next, we need to define the user and group that Gunicorn should be run as.  Our project files are all owned by our own user account, so we will set ourselves as the user to run.  The Nginx server runs under the <code>www-data</code> group.  We need Nginx to be able to read from and write to the socket file, so we'll give this group ownership over the process:</p>
<pre class="code-pre "><code langs="">description "Gunicorn application server running <span class="highlight">myproject</span>"

start on runlevel [2345]
stop on runlevel [!2345]

respawn
setuid <span class="highlight">user</span>
setgid www-data
</code></pre>
<p>Next, we need to set up the process so that it can correctly find our files and process them.  We've installed all of our Python components into a virtual environment, so we need to set an environmental variable with this as our path.  We also need to change to our project directory.  Afterwards, we can simply call the Gunicorn application with the options we'd like to use.</p>

<p>We will tell it to start 3 worker processes (adjust this as necessary).  We will also tell it to create and bind to a Unix socket file within our project directory called <code><span class="highlight">myproject</span>.sock</code>.  We'll set a umask value of <code>007</code> so that the socket file is created giving access to the owner and group, while restricting other access.  Finally, we need to pass in the WSGI entry point file name:</p>
<pre class="code-pre "><code langs="">description "Gunicorn application server running <span class="highlight">myproject</span>"

start on runlevel [2345]
stop on runlevel [!2345]

respawn
setuid <span class="highlight">user</span>
setgid www-data

env PATH=/home/<span class="highlight">user</span>/<span class="highlight">myproject</span>/<span class="highlight">myprojectenv</span>/bin
chdir /home/<span class="highlight">user</span>/<span class="highlight">myproject</span>
exec gunicorn --workers 3 --bind unix:myproject.sock -m 007 wsgi
</code></pre>
<p>Save and close the file when you are finished.</p>

<p>You can start the process immediately by typing:</p>
<pre class="code-pre "><code langs="">sudo start <span class="highlight">myproject</span>
</code></pre>
<h2 id="configuring-nginx-to-proxy-requests">Configuring Nginx to Proxy Requests</h2>

<p>Our Gunicorn application server should now be up and running, waiting for requests on the socket file in the project directory.  We need to configure Nginx to pass web requests to that socket by making some small additions to its configuration file.</p>

<p>Begin by creating a new server block configuration file in Nginx's <code>sites-available</code> directory.  We'll simply call this <code>myproject</code> to keep in line with the rest of the guide:</p>
<pre class="code-pre "><code langs="">sudo nano /etc/nginx/sites-available/myproject
</code></pre>
<p>Open up a server block and tell Nginx to listen on the default port 80.  We also need to tell it to use this block for requests for our server's domain name or IP address:</p>
<pre class="code-pre "><code langs="">server {
    listen 80;
    server_name <span class="highlight">server_domain_or_IP</span>;
}
</code></pre>
<p>The only other thing that we need to add is a location block that matches every request.  Within this block, we'll include the <code>proxy_params</code> file that specifies some general proxying parameters that need to be set.  We'll then pass the requests to the socket we defined using the <code>proxy_pass</code> directive:</p>
<pre class="code-pre "><code langs="">server {
    listen 80;
    server_name <span class="highlight">server_domain_or_IP</span>;

    location / {
        include proxy_params;
        proxy_pass http://unix:/home/<span class="highlight">user</span>/<span class="highlight">myproject</span>/<span class="highlight">myproject</span>.sock;
    }
}
</code></pre>
<p>That's actually all we need to serve our application.  Save and close the file when you're finished.</p>

<p>To enable the Nginx server block configuration we've just created, link the file to the <code>sites-enabled</code> directory:</p>
<pre class="code-pre "><code langs="">sudo ln -s /etc/nginx/sites-available/<span class="highlight">myproject</span> /etc/nginx/sites-enabled
</code></pre>
<p>With the file in that directory, we can test for syntax errors by typing:</p>
<pre class="code-pre "><code langs="">sudo nginx -t
</code></pre>
<p>If this returns without indicating any issues, we can restart the Nginx process to read the our new config:</p>
<pre class="code-pre "><code langs="">sudo service nginx restart
</code></pre>
<p>You should now be able to go to your server's domain name or IP address in your web browser and see your application:</p>

<p><img src="https://assets.digitalocean.com/articles/nginx_uwsgi_wsgi_1404/test_app.png" alt="Flask sample app" /></p>

<h2 id="conclusion">Conclusion</h2>

<p>In this guide, we've created a simple Flask application within a Python virtual environment.  We create a WSGI entry point so that any WSGI-capable application server can interface with it, and then configured the Gunicorn app server to provide this function.  Afterwards, we created an Upstart script to automatically launch the application server on boot.  We created an Nginx server block that passes web client traffic to the application server, relaying external requests.</p>

<p>Flask is a very simple, but extremely flexible framework meant to provide your applications with functionality without being too restrictive about structure and design.  You can use the general stack described in this guide to serve the flask applications that you design.</p>

    