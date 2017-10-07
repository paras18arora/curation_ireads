<link rel='stylesheet' href='digital_ocean.css'><br> <br> 
      <h3 id="introduction">Introduction</h3>

<p>In this guide, we will be setting up a simple Python application using the Flask micro-framework on Ubuntu 16.04.  The bulk of this article will be about how to set up the Gunicorn application server to launch the application and Nginx to act as a front end reverse proxy.</p>

<h2 id="prerequisites">Prerequisites</h2>

<p>Before starting on this guide, you should have a non-root user configured on your server.  This user needs to have <code>sudo</code> privileges so that it can perform administrative functions.  To learn how to set this up, follow our <a href="https://indiareads/community/tutorials/initial-server-setup-with-ubuntu-16-04">initial server setup guide</a>.</p>

<p>To learn more about the WSGI specification that our application server will use to communicate with our Flask app, you can read the linked section of <a href="https://indiareads/community/tutorials/how-to-set-up-uwsgi-and-nginx-to-serve-python-apps-on-ubuntu-14-04#definitions-and-concepts">this guide</a>.  Understanding these concepts will make this guide easier to follow.</p>

<p>When you are ready to continue, read on.</p>

<h2 id="install-the-components-from-the-ubuntu-repositories">Install the Components from the Ubuntu Repositories</h2>

<p>Our first step will be to install all of the pieces that we need from the repositories.  We will install <code>pip</code>, the Python package manager, in order to install and manage our Python components.  We will also get the Python development files needed to build some of the Gunicorn components.  We'll install Nginx now as well.</p>

<p>Update your local package index and then install the packages.  The specific packages you need will depend on the version of Python you are using for your project.</p>

<p>If you are using <strong>Python 2</strong>, type:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo apt-get update
</li><li class="line" prefix="$">sudo apt-get install python-pip python-dev nginx
</li></ul></code></pre>
<p>If, instead, you are using <strong>Python 3</strong>, type:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo apt-get update
</li><li class="line" prefix="$">sudo apt-get install python3-pip python3-dev nginx
</li></ul></code></pre>
<h2 id="create-a-python-virtual-environment">Create a Python Virtual Environment</h2>

<p>Next, we'll set up a virtual environment in order to isolate our Flask application from the other Python files on the system.</p>

<p>Start by installing the <code>virtualenv</code> package using <code>pip</code>.</p>

<p>If you are using <strong>Python 2</strong>, type:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo pip install virtualenv
</li></ul></code></pre>
<p>If you are using <strong>Python 3</strong>, type:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo pip3 install virtualenv
</li></ul></code></pre>
<p>Now, we can make a parent directory for our Flask project.  Move into the directory after you create it:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">mkdir ~/<span class="highlight">myproject</span>
</li><li class="line" prefix="$">cd ~/<span class="highlight">myproject</span>
</li></ul></code></pre>
<p>We can create a virtual environment to store our Flask project's Python requirements by typing:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">virtualenv <span class="highlight">myprojectenv</span>
</li></ul></code></pre>
<p>This will install a local copy of Python and <code>pip</code> into a directory called <code><span class="highlight">myprojectenv</span></code> within your project directory.</p>

<p>Before we install applications within the virtual environment, we need to activate it.  You can do so by typing:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">source <span class="highlight">myprojectenv</span>/bin/activate
</li></ul></code></pre>
<p>Your prompt will change to indicate that you are now operating within the virtual environment.  It will look something like this <code>(<span class="highlight">myprojectenv</span>)<span class="highlight">user</span>@<span class="highlight">host</span>:~/<span class="highlight">myproject</span>$</code>.</p>

<h2 id="set-up-a-flask-application">Set Up a Flask Application</h2>

<p>Now that you are in your virtual environment, we can install Flask and Gunicorn and get started on designing our application:</p>

<h3 id="install-flask-and-gunicorn">Install Flask and Gunicorn</h3>

<p>We can use the local instance of <code>pip</code> to install Flask and Gunicorn.  Type the following commands to get these two components:</p>

<p></p><div class="code-label notes-and-warnings note" title="Note">Note</div><span class="note">
Regardless of which version of Python you are using, when the virtual environment is activated, you should use the <code>pip</code> command (not <code>pip3</code>).<br /></span>
<pre class="code-pre custom_prefix"><code langs=""><ul class="prefixed"><li class="line" prefix="(myprojectenv) $">pip install gunicorn flask
</li></ul></code></pre>
<h3 id="create-a-sample-app">Create a Sample App</h3>

<p>Now that we have Flask available, we can create a simple application.  Flask is a micro-framework.  It does not include many of the tools that more full-featured frameworks might, and exists mainly as a module that you can import into your projects to assist you in initializing a web application.</p>

<p>While your application might be more complex, we'll create our Flask app in a single file, which we will call <code>myproject.py</code>:</p>
<pre class="code-pre custom_prefix"><code langs=""><ul class="prefixed"><li class="line" prefix="(myprojectenv) $">nano ~/<span class="highlight">myproject</span>/<span class="highlight">myproject</span>.py
</li></ul></code></pre>
<p>Within this file, we'll place our application code.  Basically, we need to import flask and instantiate a Flask object.  We can use this to define the functions that should be run when a specific route is requested:</p>
<div class="code-label " title="~/myproject/myproject.py">~/myproject/myproject.py</div><pre class="code-pre "><code class="code-highlight language-python">from flask import Flask
app = Flask(__name__)

@app.route("/")
def hello():
    return "<h1 style='color:blue'>Hello There!</h1>"

if __name__ == "__main__":
    app.run(host='0.0.0.0')
</code></pre>
<p>This basically defines what content to present when the root domain is accessed.  Save and close the file when you're finished.</p>

<p>If you followed the initial server setup guide, you should have a UFW firewall enabled. In order to test our application, we need to allow access to port 5000.</p>

<p>Open up port 5000 by typing:</p>
<pre class="code-pre custom_prefix"><code langs=""><ul class="prefixed"><li class="line" prefix="(myprojectenv) $">sudo ufw allow 5000
</li></ul></code></pre>
<p>Now, you can test your Flask app by typing:</p>
<pre class="code-pre custom_prefix"><code langs=""><ul class="prefixed"><li class="line" prefix="(myprojectenv) $">python <span class="highlight">myproject</span>.py
</li></ul></code></pre>
<p>Visit your server's domain name or IP address followed by <code>:5000</code> in your web browser:</p>
<pre class="code-pre "><code langs="">http://<span class="highlight">server_domain_or_IP</span>:5000
</code></pre>
<p>You should see something like this:</p>

<p><img src="https://assets.digitalocean.com/articles/nginx_uwsgi_wsgi_1404/test_app.png" alt="Flask sample app" /></p>

<p>When you are finished, hit CTRL-C in your terminal window a few times to stop the Flask development server.</p>

<h3 id="create-the-wsgi-entry-point">Create the WSGI Entry Point</h3>

<p>Next, we'll create a file that will serve as the entry point for our application.  This will tell our Gunicorn server how to interact with the application. </p>

<p>We will call the file <code>wsgi.py</code>:</p>
<pre class="code-pre custom_prefix"><code langs=""><ul class="prefixed"><li class="line" prefix="(myprojectenv) $">nano ~/<span class="highlight">myproject</span>/wsgi.py
</li></ul></code></pre>
<p>The file is incredibly simple, we can simply import the Flask instance from our application and then run it:</p>
<div class="code-label " title="~/myproject/wsgi.py">~/myproject/wsgi.py</div><pre class="code-pre "><code class="code-highlight language-python">from myproject import app

if __name__ == "__main__":
    app.run()
</code></pre>
<p>Save and close the file when you are finished.</p>

<h3 id="testing-gunicorn-39-s-ability-to-serve-the-project">Testing Gunicorn's Ability to Serve the Project</h3>

<p>Before moving on, we should check that Gunicorn can correctly.</p>

<p>We can do this by simply passing it the name of our entry point.  This is constructed by the name of the module (minus the <code>.py</code> extension, as usual) plus the name of the callable within the application. In our case, this would be <code>wsgi:app</code>.</p>

<p>We'll also specify the interface and port to bind to so that it will be started on a publicly available interface:</p>
<pre class="code-pre custom_prefix"><code langs=""><ul class="prefixed"><li class="line" prefix="(myprojectenv) $">cd ~/<span class="highlight">myproject</span>
</li><li class="line" prefix="(myprojectenv) $">gunicorn --bind 0.0.0.0:5000 wsgi:app
</li></ul></code></pre>
<p>Visit your server's domain name or IP address with :5000 appended to the end in your web browser again:</p>
<pre class="code-pre "><code langs="">http://<span class="highlight">server_domain_or_IP</span>:5000
</code></pre>
<p>You should see your application's output again:</p>

<p><img src="https://assets.digitalocean.com/articles/nginx_uwsgi_wsgi_1404/test_app.png" alt="Flask sample app" /></p>

<p>When you have confirmed that it's functioning properly, press CTRL-C in your terminal window.</p>

<p>We're now done with our virtual environment, so we can deactivate it:</p>
<pre class="code-pre custom_prefix"><code langs=""><ul class="prefixed"><li class="line" prefix="(myprojectenv) $">deactivate
</li></ul></code></pre>
<p>Any Python commands will now use the system's Python environment again.</p>

<h2 id="create-a-systemd-unit-file">Create a systemd Unit File</h2>

<p>The next piece we need to take care of is the systemd service unit file. Creating a systemd unit file will allow Ubuntu's init system to automatically start Gunicorn and serve our Flask application whenever the server boots.</p>

<p>Create a unit file ending in .service within the /etc/systemd/system directory to begin:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo nano /etc/systemd/system/<span class="highlight">myproject</span>.service
</li></ul></code></pre>
<p>Inside, we'll start with the <code>[Unit]</code> section, which is used to specify metadata and dependencies. We'll put a description of our service here and tell the init system to only start this after the networking target has been reached:</p>
<div class="code-label " title="/etc/systemd/system/myproject.service">/etc/systemd/system/myproject.service</div><pre class="code-pre "><code langs="">[Unit]
Description=Gunicorn instance to serve <span class="highlight">myproject</span>
After=network.target
</code></pre>
<p>Next, we'll open up the <code>[Service]</code> section. We'll specify the user and group that we want the process to run under. We will give our regular user account ownership of the process since it owns all of the relevant files. We'll give group ownership to the <code>www-data</code> group so that Nginx can communicate easily with the Gunicorn processes.</p>

<p>We'll then map out the working directory and set the <code>PATH</code> environmental variable so that the init system knows where our the executables for the process are located (within our virtual environment). We'll then specify the commanded to start the service. Systemd requires that we give the full path to the Gunicorn executable, which is installed within our virtual environment.</p>

<p>We will tell it to start 3 worker processes (adjust this as necessary). We will also tell it to create and bind to a Unix socket file within our project directory called <code><span class="highlight">myproject</span>.sock</code>. We'll set a umask value of <code>007</code> so that the socket file is created giving access to the owner and group, while restricting other access. Finally, we need to pass in the WSGI entry point file name and the Python callable within:</p>
<div class="code-label " title="/etc/systemd/system/myproject.service">/etc/systemd/system/myproject.service</div><pre class="code-pre "><code langs="">[Unit]
Description=Gunicorn instance to serve <span class="highlight">myproject</span>
After=network.target

[Service]
User=sammy
Group=www-data
WorkingDirectory=/home/<span class="highlight">sammy</span>/<span class="highlight">myproject</span>
Environment="PATH=/home/<span class="highlight">sammy</span>/<span class="highlight">myproject</span>/<span class="highlight">myprojectenv</span>/bin"
ExecStart=/home/<span class="highlight">sammy</span>/<span class="highlight">myproject</span>/<span class="highlight">myprojectenv</span>/bin/gunicorn --workers 3 --bind unix:<span class="highlight">myproject</span>.sock -m 007 <span class="highlight">wsgi</span>:<span class="highlight">app</span>
</code></pre>
<p>Finally, we'll add an [Install] section. This will tell systemd what to link this service to if we enable it to start at boot. We want this service to start when the regular multi-user system is up and running:</p>
<div class="code-label " title="/etc/systemd/system/myproject.service">/etc/systemd/system/myproject.service</div><pre class="code-pre "><code langs="">[Unit]
Description=Gunicorn instance to serve <span class="highlight">myproject</span>
After=network.target

[Service]
User=sammy
Group=www-data
WorkingDirectory=/home/<span class="highlight">sammy</span>/<span class="highlight">myproject</span>
Environment="PATH=/home/<span class="highlight">sammy</span>/<span class="highlight">myproject</span>/<span class="highlight">myprojectenv</span>/bin"
ExecStart=/home/<span class="highlight">sammy</span>/<span class="highlight">myproject</span>/<span class="highlight">myprojectenv</span>/bin/gunicorn --workers 3 --bind unix:<span class="highlight">myproject</span>.sock -m 007 <span class="highlight">wsgi</span>:<span class="highlight">app</span>

[Install]
WantedBy=multi-user.target
</code></pre>
<p>With that, our systemd service file is complete. Save and close it now.</p>

<p>We can now start the Gunicorn service we created and enable it so that it starts at boot:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo systemctl start <span class="highlight">myproject</span>
</li><li class="line" prefix="$">sudo systemctl enable <span class="highlight">myproject</span>
</li></ul></code></pre>
<h2 id="configuring-nginx-to-proxy-requests">Configuring Nginx to Proxy Requests</h2>

<p>Our Gunicorn application server should now be up and running, waiting for requests on the socket file in the project directory.  We need to configure Nginx to pass web requests to that socket by making some small additions to its configuration file.</p>

<p>Begin by creating a new server block configuration file in Nginx's <code>sites-available</code> directory.  We'll simply call this <code>myproject</code> to keep in line with the rest of the guide:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo nano /etc/nginx/sites-available/<span class="highlight">myproject</span>
</li></ul></code></pre>
<p>Open up a server block and tell Nginx to listen on the default port 80.  We also need to tell it to use this block for requests for our server's domain name or IP address:</p>
<div class="code-label " title="/etc/nginx/sites-available/myproject">/etc/nginx/sites-available/myproject</div><pre class="code-pre "><code langs="">server {
    listen 80;
    server_name <span class="highlight">server_domain_or_IP</span>;
}
</code></pre>
<p>The only other thing that we need to add is a location block that matches every request.  Within this block, we'll include the <code>proxy_params</code> file that specifies some general proxying parameters that need to be set.  We'll then pass the requests to the socket we defined using the <code>proxy_pass</code> directive:</p>
<div class="code-label " title="/etc/nginx/sites-available/myproject">/etc/nginx/sites-available/myproject</div><pre class="code-pre "><code langs="">server {
    listen 80;
    server_name <span class="highlight">server_domain_or_IP</span>;

    location / {
        include proxy_params;
        proxy_pass http://unix:/home/<span class="highlight">sammy</span>/<span class="highlight">myproject</span>/<span class="highlight">myproject</span>.sock;
    }
}
</code></pre>
<p>That's actually all we need to serve our application.  Save and close the file when you're finished.</p>

<p>To enable the Nginx server block configuration we've just created, link the file to the <code>sites-enabled</code> directory:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo ln -s /etc/nginx/sites-available/<span class="highlight">myproject</span> /etc/nginx/sites-enabled
</li></ul></code></pre>
<p>With the file in that directory, we can test for syntax errors by typing:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo nginx -t
</li></ul></code></pre>
<p>If this returns without indicating any issues, we can restart the Nginx process to read the our new config:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo systemctl restart nginx
</li></ul></code></pre>
<p>The last thing we need to do is adjust our firewall again. We no longer need access through port 5000, so we can remove that rule. We can then allow access to the Nginx server:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo ufw delete allow 5000
</li><li class="line" prefix="$">sudo ufw allow 'Nginx Full'
</li></ul></code></pre>
<p>You should now be able to go to your server's domain name or IP address in your web browser:</p>
<pre class="code-pre "><code langs="">http://<span class="highlight">server_domain_or_IP</span>
</code></pre>
<p>You should see your application's output:</p>

<p><img src="https://assets.digitalocean.com/articles/nginx_uwsgi_wsgi_1404/test_app.png" alt="Flask sample app" /></p>

<div class="code-label notes-and-warnings note" title="Note">Note</div><span class="note"><p>

After configuring Nginx, the next step should be securing traffic to the server using SSL/TLS.  This is important because without it, all information, including passwords are sent over the network in plain text.</p>

<p>The easiest way get an SSL certificate to secure your traffic is using Let's Encrypt.  Follow <a href="https://indiareads/community/tutorials/how-to-secure-nginx-with-let-s-encrypt-on-ubuntu-16-04">this guide</a> to set up Let's Encrypt with Nginx on Ubuntu 16.04.<br /></p></span>

<h2 id="conclusion">Conclusion</h2>

<p>In this guide, we've created a simple Flask application within a Python virtual environment.  We create a WSGI entry point so that any WSGI-capable application server can interface with it, and then configured the Gunicorn app server to provide this function.  Afterwards, we created a systemd unit file to automatically launch the application server on boot.  We created an Nginx server block that passes web client traffic to the application server, relaying external requests.</p>

<p>Flask is a very simple, but extremely flexible framework meant to provide your applications with functionality without being too restrictive about structure and design.  You can use the general stack described in this guide to serve the flask applications that you design.</p>

    