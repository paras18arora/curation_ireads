<link rel='stylesheet' href='digital_ocean.css'><br> <br> 
      <h3 id="introduction">Introduction</h3>

<p>In this guide, we will be setting up a simple Python application using the Flask micro-framework on CentOS 7.  The bulk of this article will be about how to set up the Gunicorn application server to launch the application and Nginx to act as a front end reverse proxy.</p>

<h2 id="prerequisites">Prerequisites</h2>

<p>Before starting on this guide, you should have a non-root user configured on your server.  This user needs to have <code>sudo</code> privileges so that it can perform administrative functions.  To learn how to set this up, follow our <a href="https://indiareads/community/tutorials/initial-server-setup-with-centos-7">initial server setup guide</a>.</p>

<p>To learn more about the WSGI specification that our application server will use to communicate with our Flask app, you can read the linked section of <a href="https://indiareads/community/tutorials/how-to-set-up-uwsgi-and-nginx-to-serve-python-apps-on-ubuntu-14-04#definitions-and-concepts">this guide</a>.  Understanding these concepts will make this guide easier to follow.</p>

<p>When you are ready to continue, read on.</p>

<h2 id="install-the-components-from-the-centos-and-epel-repositories">Install the Components from the CentOS and EPEL Repositories</h2>

<p>Our first step will be to install all of the pieces that we need from the repositories. We will need to add the EPEL repository, which contains some extra packages, in order to install some of the components we need.</p>

<p>You can enable the EPEL repo by typing:</p>
<pre class="code-pre "><code langs="">sudo yum install epel-release
</code></pre>
<p>Once access to the EPEL repository is configured on our system, we can begin installing the packages we need. We will install pip, the Python package manager, in order to install and manage our Python components. We will also get a compiler and the Python development files needed by Gunicorn. We'll install Nginx now as well.</p>

<p>You can install all of these components by typing:</p>
<pre class="code-pre "><code langs="">sudo yum install python-pip python-devel gcc nginx
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

<h2 id="create-a-systemd-unit-file">Create a Systemd Unit File</h2>

<p>The next piece we need to take care of is the Systemd service unit file.  Creating a Systemd unit file will allow CentOS's init system to automatically start Gunicorn and serve our Flask application whenever the server boots.</p>

<p>Create a file ending with <code>.service</code> within the <code>/etc/systemd/system</code> directory to begin:</p>
<pre class="code-pre "><code langs="">sudo nano /etc/systemd/system/<span class="highlight">myproject</span>.service
</code></pre>
<p>Inside, we'll start with a <code>[Unit]</code> section, which is used to specify metadata and dependencies.  We'll put a description of our service here and tell the init system to only start this after the networking target has been reached:</p>
<pre class="code-pre "><code langs="">[Unit]
Description=Gunicorn instance to serve <span class="highlight">myproject</span>
After=network.target
</code></pre>
<p>Next, we'll open up the <code>[Service]</code> section. We'll specify the user and group that we want the process to run under. We will give our regular user account ownership of the process since it owns all of the relevant files. We'll give the Nginx user group ownership so that it can communicate easily with the Gunicorn processes.</p>

<p>We'll then map out the working directory and set the <code>PATH</code> environmental variable so that the init system knows where our the executables for the process are located (within our virtual environmment). We'll then specify the commanded to start the service. Systemd requires that we give the full path to the Gunicorn executable, which is installed within our virtual environment. </p>

<p>We will tell it to start 3 worker processes (adjust this as necessary).  We will also tell it to create and bind to a Unix socket file within our project directory called <code><span class="highlight">myproject</span>.sock</code>.  We'll set a umask value of <code>007</code> so that the socket file is created giving access to the owner and group, while restricting other access.  Finally, we need to pass in the WSGI entry point file name:</p>
<pre class="code-pre "><code langs="">[Unit]
Description=Gunicorn instance to serve <span class="highlight">myproject</span>
After=network.target

[Service]
User=user
Group=nginx
WorkingDirectory=/home/<span class="highlight">user</span>/<span class="highlight">myproject</span>
Environment="PATH=/home/<span class="highlight">user</span>/<span class="highlight">myproject</span>/<span class="highlight">myprojectenv</span>/bin"
ExecStart=/home/<span class="highlight">user</span>/<span class="highlight">myproject</span>/<span class="highlight">myprojectenv</span>/bin/gunicorn --workers 3 --bind unix:myproject.sock -m 007 wsgi
</code></pre>
<p>The last piece we need to add to the file is an <code>[Install]</code> section.  This will tell Systemd what to link this service to if we enable it to start at boot.  We want this service to start when the regular multi-user system is up and running:</p>
<pre class="code-pre "><code langs="">[Unit]
Description=Gunicorn instance to serve <span class="highlight">myproject</span>
After=network.target

[Service]
User=user
Group=nginx
WorkingDirectory=/home/<span class="highlight">user</span>/<span class="highlight">myproject</span>
Environment="PATH=/home/<span class="highlight">user</span>/<span class="highlight">myproject</span>/<span class="highlight">myprojectenv</span>/bin"
ExecStart=/home/<span class="highlight">user</span>/<span class="highlight">myproject</span>/<span class="highlight">myprojectenv</span>/bin/gunicorn --workers 3 --bind unix:myproject.sock -m 007 wsgi

[Install]
WantedBy=multi-user.target
</code></pre>
<p>With that, our Systemd service file is complete.  Save and close it now.</p>

<p>We can now start the Gunicorn service we created and enable it so that it starts at boot:</p>
<pre class="code-pre "><code langs="">sudo systemctl start <span class="highlight">myproject</span>
sudo systemctl enable <span class="highlight">myproject</span>
</code></pre>
<h2 id="configuring-nginx-to-proxy-requests">Configuring Nginx to Proxy Requests</h2>

<p>Our Gunicorn application server should now be up and running, waiting for requests on the socket file in the project directory.  We need to configure Nginx to pass web requests to that socket by making some small additions to its configuration file.</p>

<p>Begin by opening up Nginx's default configuration file:</p>
<pre class="code-pre "><code langs="">sudo nano /etc/nginx/nginx.conf
</code></pre>
<p>Open up a server block just above the other <code>server {}</code> block that is already in the file:</p>
<pre class="code-pre "><code langs="">http {
    . . .

    include /etc/nginx/conf.d/*.conf;

    <span class="highlight">server {</span>
    <span class="highlight">}</span>

    server {
        listen 80 default_server;

        . . .
</code></pre>
<p>We will put all of the configuration for our Flask application inside of this new block.  We will start by specifying that this block should listen on the default port 80 and that it should respond to our server's domain name or IP address:</p>
<pre class="code-pre "><code langs="">server {
    listen 80;
    server_name <span class="highlight">server_domain_or_IP</span>;
}
</code></pre>
<p>The only other thing that we need to add is a location block that matches every request.  Within this block, we'll set some standard proxying HTTP headers so that Gunicorn can have some information about the remote client connection.  We will then pass the traffic to the socket we specified in our Systemd unit file:</p>
<pre class="code-pre "><code langs="">server {
    listen 80;
    server_name <span class="highlight">server_domain_or_IP</span>;

    location / {
        proxy_set_header Host $http_host;
        proxy_set_header X-Real-IP $remote_addr;
        proxy_set_header X-Forwarded-For $proxy_add_x_forwarded_for;
        proxy_set_header X-Forwarded-Proto $scheme;
        proxy_pass http://unix:/home/<span class="highlight">user</span>/<span class="highlight">myproject</span>/<span class="highlight">myproject</span>.sock;
    }
}
</code></pre>
<p>Save and close the file when you are finished.</p>

<p>The <code>nginx</code> user must have access to our application directory in order to access the socket file there.  By default, CentOS locks down each user's home directory very restrictively, so we will add the <code>nginx</code> user to our user's group so that we can then open up the minimum permissions necessary to grant access.</p>

<p>You can add the <code>nginx</code> user to your user group with the following command.  Substitute your own username for the <code><span class="highlight">user</span></code> in the command:</p>
<pre class="code-pre "><code langs="">sudo usermod -a -G <span class="highlight">user</span> nginx
</code></pre>
<p>Now, we can give our user group execute permissions on our home directory.  This will allow the Nginx process to enter and access content within:</p>
<pre class="code-pre "><code langs="">chmod 710 /home/<span class="highlight">user</span>
</code></pre>
<p>With the permissions set up, we can test our Nginx configuration file for syntax errors:</p>
<pre class="code-pre "><code langs="">sudo nginx -t
</code></pre>
<p>If this returns without indicating any issues, we can start and enable the Nginx process so that it starts automatically at boot:</p>
<pre class="code-pre "><code langs="">sudo systemctl start nginx
sudo systemctl enable nginx
</code></pre>
<p>You should now be able to go to your server's domain name or IP address in your web browser and see your application:</p>

<p><img src="https://assets.digitalocean.com/articles/nginx_uwsgi_wsgi_1404/test_app.png" alt="Flask sample app" /></p>

<h2 id="conclusion">Conclusion</h2>

<p>In this guide, we've created a simple Flask application within a Python virtual environment.  We create a WSGI entry point so that any WSGI-capable application server can interface with it, and then configured the Gunicorn app server to provide this function.  Afterwards, we created a Systemd unit file to automatically launch the application server on boot.  We created an Nginx server block that passes web client traffic to the application server, relaying external requests.</p>

<p>Flask is a very simple, but extremely flexible framework meant to provide your applications with functionality without being too restrictive about structure and design.  You can use the general stack described in this guide to serve the flask applications that you design.</p>

    