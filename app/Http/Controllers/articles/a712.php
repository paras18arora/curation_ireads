<link rel='stylesheet' href='digital_ocean.css'><br><img src=https://community-cdn-digitalocean-com.global.ssl.fastly.net/assets/tutorials/images/large/dhango_applications_tw.jpg?1426863312/> <br> 
      <h3 id="introduction">Introduction</h3>

<p>Django is a powerful web framework that can help you get your Python application or website off the ground.  Django includes a simplified development server for testing your code locally, but for anything even slightly production related, a more secure and powerful web server is required.</p>

<p>In this guide, we will demonstrate how to install and configure some components on Ubuntu 14.04 to support and serve Django applications.  We will configure the uWSGI application container server to interface with our applications.  We will then set up Nginx to reverse proxy to uWSGI, giving us access to its security and performance features to serve our apps.</p>

<h2 id="prerequisites-and-goals">Prerequisites and Goals</h2>

<p>In order to complete this guide, you should have a fresh Ubuntu 14.04 server instance with a non-root user with <code>sudo</code> privileges configured.  You can learn how to set this up by running through our <a href="https://indiareads/community/tutorials/initial-server-setup-with-ubuntu-14-04">initial server setup guide</a>.</p>

<p>We will be installing Django within two different virtual environments.  This will allow your projects and their requirements to be handled separately.  We will be creating two sample projects so that we can run through the steps in a multi-project environment.</p>

<p>Once we have our applications, we will install and configure the uWSGI application server.  This will serve as an interface to our applications which will translate client requests using HTTP to Python calls that our application can process.  We will then set up Nginx in front of uWSGI to take advantage of its high performance connection handling mechanisms and its easy-to-implement security features.</p>

<p>Let's get started.</p>

<h2 id="install-and-configure-virtualenv-and-virtualenvwrapper">Install and Configure VirtualEnv and VirtualEnvWrapper</h2>

<p>We will be installing our Django projects in their own virtual environments to isolate the requirements for each.  To do this, we will be installing <code>virtualenv</code>, which can create Python virtual environments, and <code>virtualenvwrapper</code>, which adds some usability improvements to the <code>virtualenv</code> work flow.</p>

<p>We will be installing both of these components using <code>pip</code>, the Python package manager.  This can be acquired from the Ubuntu repositories:</p>
<pre class="code-pre "><code langs="">sudo apt-get update
sudo apt-get install python-pip
</code></pre>
<p>In this guide, we are using Python version 2.  If your code uses Python 3, you can install the <code>python3-pip</code> package.  You will then have to substitute the <code>pip</code> commands in this guide with the <code>pip3</code> command when operating outside of a virtual environment.</p>

<p>Now that you have <code>pip</code> installed, we can install <code>virtualenv</code> and <code>virtualenvwrapper</code> globally by typing:</p>
<pre class="code-pre "><code langs="">sudo pip install virtualenv virtualenvwrapper
</code></pre>
<p>With these components installed, we can now configure our shell with the information it needs to work with the <code>virtualenvwrapper</code> script.  Our virtual environments will all be placed within a directory in our home folder called <code>Env</code> for easy access.  This is configured through an environmental variable called <code>WORKON_HOME</code>.  We can add this to our shell initialization script and can source the virtual environment wrapper script.</p>

<p>If you are using Python 3 and the <code>pip3</code> command, you will have to add an additional line to your shell initialization script as well:</p>
<pre class="code-pre "><code langs="">echo "export VIRTUALENVWRAPPER_PYTHON=/usr/bin/python3" >> ~/.bashrc
</code></pre>
<p>Regardless of which version of Python you are using, you need to run the following commands:</p>
<pre class="code-pre "><code langs="">echo "export WORKON_HOME=~/Env" >> ~/.bashrc
echo "source /usr/local/bin/virtualenvwrapper.sh" >> ~/.bashrc
</code></pre>
<p>Now, source your shell initialization script so that you can use this functionality in your current session:</p>
<pre class="code-pre "><code langs="">source ~/.bashrc
</code></pre>
<p>You should now have directory called <code>Env</code> in your home folder which will hold virtual environment information.</p>

<h2 id="create-django-projects">Create Django Projects</h2>

<p>Now that we have our virtual environment tools, we will create two virtual environments, install Django in each, and start two projects.</p>

<h3 id="create-the-first-project">Create the First Project</h3>

<p>We can create a virtual environment easily by using some commands that the <code>virtualenvwrapper</code> script makes available to us.</p>

<p>Create your first virtual environment with the name of your first site or project by typing:</p>
<pre class="code-pre "><code langs="">mkvirtualenv <span class="highlight">firstsite</span>
</code></pre>
<p>This will create a virtual environment, install Python and <code>pip</code> within it, and activate the environment.  Your prompt will change to indicate that you are now operating within your new virtual environment.  It will look something like this: <code>(<span class="highlight">firstsite</span>)<span class="highlight">user</span>@<span class="highlight">hostname</span>:~$</code>.  The value in the parentheses is the name of your virtual environment.  Any software installed through <code>pip</code> will now be installed into the virtual environment instead of on the global system.  This allows us to isolate our packages on a per-project basis.</p>

<p>Our first step will be to install Django itself.  We can use <code>pip</code> for this without <code>sudo</code> since we are installing this locally in our virtual environment:</p>
<pre class="code-pre "><code langs="">pip install django
</code></pre>
<p>With Django installed, we can create our first sample project by typing:</p>
<pre class="code-pre "><code langs="">cd ~
django-admin.py startproject <span class="highlight">firstsite</span>
</code></pre>
<p>This will create a directory called <code><span class="highlight">firstsite</span></code> within your home directory.  Within this is a management script used to handle various aspects of the project and another directory of the same name used to house the actual project code. </p>

<p>Move into the first level directory so that we can begin setting up the minimum requirements for our sample project.</p>
<pre class="code-pre "><code langs="">cd ~/<span class="highlight">firstsite</span>
</code></pre>
<p>Begin by migrating the database to initialize the SQLite database that our project will use.  You can set up an alternative database for your application if you wish, but this is outside of the scope of this guide:</p>
<pre class="code-pre "><code langs="">./manage.py migrate
</code></pre>
<p>You should now have a database file called <code>db.sqlite3</code> in your project directory.  Now, we can create an administrative user by typing:</p>
<pre class="code-pre "><code langs="">./manage.py createsuperuser
</code></pre>
<p>You will have to select a username, give a contact email address, and then select and confirm a password.</p>

<p>Next, open the settings file for the project with your text editor:</p>
<pre class="code-pre "><code langs="">nano <span class="highlight">firstsite</span>/settings.py
</code></pre>
<p>Since we will be setting up Nginx to serve our site, we need to configure a directory which will hold our site's static assets.  This will allow Nginx to serve these directly, which will have a positive impact on performance.  We will tell Django to place these into a directory called <code>static</code> in our project's base directory.  Add this line to the bottom of the file to configure this behavior:</p>
<pre class="code-pre "><code langs="">STATIC_ROOT = os.path.join(BASE_DIR, "static/")
</code></pre>
<p>Save and close the file when you are finished.  Now, collect our site's static elements and place them within that directory by typing:</p>
<pre class="code-pre "><code langs="">./manage.py collectstatic
</code></pre>
<p>You can type "yes" to confirm the action and collect the static content.  There will be a new directory called <code>static</code> in your project directory.</p>

<p>With all of that out of the way, we can test our project by temporarily starting the development server.  Type:</p>
<pre class="code-pre "><code langs="">./manage.py runserver 0.0.0.0:8080
</code></pre>
<p>This will start up the development server on port <code>8080</code>.  Visit your server's domain name or IP address followed by <code>8080</code> in your browser:</p>
<pre class="code-pre "><code langs="">http://<span class="highlight">server_domain_or_IP</span>:8080
</code></pre>
<p>You should see a page that looks like this:</p>

<p><img src="https://assets.digitalocean.com/articles/django_uwsgi_nginx_1404/sample_site.png" alt="Django sample site" /></p>

<p>Add <code>/admin</code> to the end of the URL in your browser's address bar and you will be taken to the admin login page:</p>

<p><img src="https://assets.digitalocean.com/articles/django_uwsgi_nginx_1404/admin_login.png" alt="Django admin login" /></p>

<p>Using the administrative login credentials you selected with the <code>createsuperuser</code> command, log into the server.  You will then have access to the administration interface:</p>

<p><img src="https://assets.digitalocean.com/articles/django_uwsgi_nginx_1404/admin_interface.png" alt="Django admin interface" /></p>

<p>After testing this functionality out, stop the development server by typing CTRL-C in your terminal.  We can now move on to our second project.</p>

<h3 id="create-the-second-project">Create the Second Project</h3>

<p>The second project will be created in exactly the same way as the first.  We will abridge the explanation in this section, seeing as how you have already completed this once.</p>

<p>Move back to your home directory and create a second virtual environment for your new project.  Install Django inside of this new environment once it is activated:</p>
<pre class="code-pre "><code langs="">cd ~
mkvirtualenv <span class="highlight">secondsite</span>
pip install django
</code></pre>
<p>The new environment will be created <em>and</em> changed to, leaving your previous virtual environment.  This Django instance is entirely separate from the other one you configured.  This allows you to manage them independently and customize as necessary.</p>

<p>Create the second project and move into the project directory:</p>
<pre class="code-pre "><code langs="">django-admin.py startproject <span class="highlight">secondsite</span>
cd ~/<span class="highlight">secondsite</span>
</code></pre>
<p>Initialize the database and create an administrative user:</p>
<pre class="code-pre "><code langs="">./manage.py migrate
./manage.py createsuperuser
</code></pre>
<p>Open the settings file:</p>
<pre class="code-pre "><code langs="">nano <span class="highlight">secondsite</span>/settings.py
</code></pre>
<p>Add the location for the static files, just as you did in the previous project:</p>
<pre class="code-pre "><code langs="">STATIC_ROOT = os.path.join(BASE_DIR, "static/")
</code></pre>
<p>Save and close the file.  Now, collect the static elements into that directory by typing:</p>
<pre class="code-pre "><code langs="">./manage.py collectstatic
</code></pre>
<p>Finally, fire up the development server to test out the site:</p>
<pre class="code-pre "><code langs="">./manage.py runserver 0.0.0.0:8080
</code></pre>
<p>You should check the regular site at:</p>
<pre class="code-pre "><code langs="">http://<span class="highlight">server_domain_or_IP</span>:8080
</code></pre>
<p>Also log into the admin site:</p>
<pre class="code-pre "><code langs="">http://<span class="highlight">server_domain_or_IP</span>:8080/admin
</code></pre>
<p>When you've confirmed that everything is working as expected, type CTRL-C in your terminal to stop the development server.</p>

<h3 id="backing-out-of-the-virtual-environment">Backing Out of the Virtual Environment</h3>

<p>Since we are now done with the Django portion of the guide, we can deactivate our second virtual environment:</p>
<pre class="code-pre "><code langs="">deactivate
</code></pre>
<p>If you need to work on either of your Django sites again, you should reactivate their respective environments.  You can do that by using the <code>workon</code> command:</p>
<pre class="code-pre "><code langs="">workon <span class="highlight">firstsite</span>
</code></pre>
<p>Or:</p>
<pre class="code-pre "><code langs="">workon <span class="highlight">secondsite</span>
</code></pre>
<p>Again, deactivate when you are finished working on your sites:</p>
<pre class="code-pre "><code langs="">deactivate
</code></pre>
<h2 id="setting-up-the-uwsgi-application-server">Setting up the uWSGI Application Server</h2>

<p>Now that we have two Django projects set up and ready to go, we can configure uWSGI.  uWSGI is an application server that can communicate with applications over a standard interface called WSGI.  To learn more about this, read <a href="https://indiareads/community/tutorials/how-to-set-up-uwsgi-and-nginx-to-serve-python-apps-on-ubuntu-14-04#definitions-and-concepts">this section</a> of our guide on setting up uWSGI and Nginx on Ubuntu 14.04.</p>

<h3 id="installing-uwsgi">Installing uWSGI</h3>

<p>Unlike the guide linked above, in this tutorial, we'll be installing uWSGI globally.  This will create less friction in handling multiple Django projects.  Before we can install uWSGI, we need the Python development files that the software relies on.  We can install this directly from Ubuntu's repositories:</p>
<pre class="code-pre "><code langs="">sudo apt-get install python-dev
</code></pre>
<p>Now that the development files are available, we can install uWSGI globally through <code>pip</code> by typing:</p>
<pre class="code-pre "><code langs="">sudo pip install uwsgi
</code></pre>
<p>We can quickly test this application server by passing it the information for one of our sites.  For instance, we can tell it to serve our first project by typing:</p>
<pre class="code-pre "><code langs="">uwsgi --http :8080 --home /home/<span class="highlight">user</span>/Env/<span class="highlight">firstsite</span> --chdir /home/<span class="highlight">user</span>/<span class="highlight">firstsite</span> -w <span class="highlight">firstsite</span>.wsgi
</code></pre>
<p>Here, we've told uWSGI to use our virtual environment located in our <code>~/Env</code> directory, to change to our project's directory, and to use the <code>wsgi.py</code> file stored within our inner <code><span class="highlight">firstsite</span></code> directory to serve the file.  For our demonstration, we told it to serve HTTP on port <code>8080</code>.  If you go to server's domain name or IP address in your browser, followed by <code>:8080</code>, you will see your site again (the static elements in the <code>/admin</code> interface won't work yet).  When you are finished testing out this functionality, type CTRL-C in the terminal.</p>

<h3 id="creating-configuration-files">Creating Configuration Files</h3>

<p>Running uWSGI from the command line is useful for testing, but isn't particularly helpful for an actual deployment.  Instead, we will run uWSGI in "Emperor mode", which allows a master process to manage separate applications automatically given a set of configuration files.</p>

<p>Create a directory that will hold your configuration files.  Since this is a global process, we will create a directory called <code>/etc/uwsgi/sites</code> to store our configuration files.  Move into the directory after you create it:</p>
<pre class="code-pre "><code langs="">sudo mkdir -p /etc/uwsgi/sites
cd /etc/uwsgi/sites
</code></pre>
<p>In this directory, we will place our configuration files.  We need a configuration file for each of the projects we are serving.  The uWSGI process can take configuration files in a variety of formats, but we will use <code>.ini</code> files due to their simplicity.</p>

<p>Create a file for your first project and open it in your text editor:</p>
<pre class="code-pre "><code langs="">sudo nano <span class="highlight">firstsite</span>.ini
</code></pre>
<p>Inside, we must begin with the <code>[uwsgi]</code> section header.  All of our information will go beneath this header.  We are also going to use variables to make our configuration file more reusable.  After the header, set a variable called <code>project</code> with the name of your first project.  Add a variable called <code>base</code> with the path to your user's home directory:</p>
<pre class="code-pre "><code langs="">[uwsgi]
project = <span class="highlight">firstsite</span>
base = /home/<span class="highlight">user</span>
</code></pre>
<p>Next, we need to configure uWSGI so that it handles our project correctly.  We need to change into the root project directory by setting the <code>chdir</code> option.  We can combine the home directory and project name setting that we set earlier by using the <code>%(<span class="highlight">variable_name</span>)</code> syntax.  This will be replaced by the value of the variable when the config is read.</p>

<p>In a similar way, we will indicate the virtual environment for our project.  By setting the module, we can indicate exactly how to interface with our project (by importing the "application" callable from the <code>wsgi.py</code> file within our project directory).  The configuration of these items will look like this:</p>
<pre class="code-pre "><code langs="">[uwsgi]
project = <span class="highlight">firstsite</span>
base = /home/<span class="highlight">user</span>

chdir = %(base)/%(project)
home = %(base)/Env/%(project)
module = %(project).wsgi:application
</code></pre>
<p>We want to create a master process with 5 workers.  We can do this by adding this:</p>
<pre class="code-pre "><code langs="">[uwsgi]
project = <span class="highlight">firstsite</span>
base = /home/<span class="highlight">user</span>

chdir = %(base)/%(project)
home = %(base)/Env/%(project)
module = %(project).wsgi:application

master = true
processes = 5
</code></pre>
<p>Next we need to specify how uWSGI should listen for connections.  In our test of uWSGI, we used HTTP and a network port.  However, since we are going to be using Nginx as a reverse proxy, we have better options. </p>

<p>Instead of using a network port, since all of the components are operating on a single server, we can use a Unix socket.  This is more secure and offers better performance.  This socket will not use HTTP, but instead will implement uWSGI's <code>uwsgi</code> protocol, which is a fast binary protocol for designed for communicating with other servers.  Nginx can natively proxy using the <code>uwsgi</code> protocol, so this is our best choice.</p>

<p>We will also modify the permissions of the socket because we will be giving the web server write access.  We'll set the <code>vacuum</code> option so that the socket file will be automatically cleaned up when the service is stopped:</p>
<pre class="code-pre "><code langs="">[uwsgi]
project = <span class="highlight">firstsite</span>
base = /home/<span class="highlight">user</span>

chdir = %(base)/%(project)
home = %(base)/Env/%(project)
module = %(project).wsgi:application

master = true
processes = 5

socket = %(base)/%(project)/%(project).sock
chmod-socket = 664
vacuum = true
</code></pre>
<p>With this, our first project's uWSGI configuration is complete.  Save and close the file.</p>

<p>The advantage of setting up the file using variables is that it makes it incredibly simple to reuse.  Copy your first project's configuration file to use as a base for your second configuration file:</p>
<pre class="code-pre "><code langs="">sudo cp /etc/uwsgi/sites/<span class="highlight">firstsite</span>.ini /etc/uwsgi/sites/<span class="highlight">secondsite</span>.ini
</code></pre>
<p>Open the second configuration file with your text editor:</p>
<pre class="code-pre "><code langs="">sudo nano /etc/uwsgi/sites/<span class="highlight">secondsite</span>.ini
</code></pre>
<p>We only need to change a single value in this file in order to make it work for our second project.  Modify the <code>project</code> variable with the name you've used for your second project:</p>
<pre class="code-pre "><code langs="">[uwsgi]
project = <span class="highlight">secondsite</span>
base = /home/<span class="highlight">user</span>

chdir = %(base)/%(project)
home = %(base)/Env/%(project)
module = %(project).wsgi:application

master = true
processes = 5

socket = %(base)/%(project)/%(project).sock
chmod-socket = 664
vacuum = true
</code></pre>
<p>Save and close the file when you are finished.  Your second project should be ready to go now.</p>

<h3 id="create-an-upstart-script-for-uwsgi">Create an Upstart Script for uWSGI</h3>

<p>We now have the configuration files we need to serve our Django projects, but we still haven't automated the process.  Next, we'll create an Upstart script to automatically start uWSGI at boot.</p>

<p>We will create an Upstart script in the <code>/etc/init</code> directory, where these files are checked:</p>
<pre class="code-pre "><code langs="">sudo nano /etc/init/uwsgi.conf
</code></pre>
<p>Start by setting a description for your uWSGI service and indicating the runlevels where it should automatically run.  We will set ours to run on runlevels 2, 3, 4, and 5, which are the conventional multi-user runlevels:</p>
<pre class="code-pre "><code langs="">description "uWSGI application server in Emperor mode"

start on runlevel [2345]
stop on runlevel [!2345]
</code></pre>
<p>Next, we need to set the username and group that the process will be run as.  We will run the process under our own username since we own all of the files.  For the group, we need to set it to the <code>www-data</code> group that Nginx will run under.  Our socket settings from the uWSGI configuration file should then allow the web server to write to the socket.  Change the username below to match your username on the server:</p>
<pre class="code-pre "><code langs="">description "uWSGI application server in Emperor mode"

start on runlevel [2345]
stop on runlevel [!2345]

setuid <span class="highlight">user</span>
setgid www-data
</code></pre>
<p>Finally, we need to specify the actual command to execute.  We need to start uWSGI in Emperor mode and pass in the directory where we stored our configuration files.  uWSGI will read the files and serve each of our projects:</p>
<pre class="code-pre "><code langs="">description "uWSGI application server in Emperor mode"

start on runlevel [2345]
stop on runlevel [!2345]

setuid <span class="highlight">user</span>
setgid www-data

exec /usr/local/bin/uwsgi --emperor /etc/uwsgi/sites
</code></pre>
<p>When you are finished, save and close the file.  We won't start uWSGI yet since we will not have the <code>www-data</code> group available until after we install Nginx.</p>

<h2 id="install-and-configure-nginx-as-a-reverse-proxy">Install and Configure Nginx as a Reverse Proxy</h2>

<p>With uWSGI configured and ready to go, we can now install and configure Nginx as our reverse proxy.  This can be downloaded from Ubuntu's default repositories:</p>
<pre class="code-pre "><code langs="">sudo apt-get install nginx
</code></pre>
<p>Once Nginx is installed, we can go ahead and create a server block configuration file for each of our projects.  Start with the first project by creating a server block configuration file:</p>
<pre class="code-pre "><code langs="">sudo nano /etc/nginx/sites-available/<span class="highlight">firstsite</span>
</code></pre>
<p>Inside, we can start our server block by indicating the port number and domain name where our first project should be accessible.  We'll assume that you have a domain name for each:</p>
<pre class="code-pre "><code langs="">server {
    listen 80;
    server_name <span class="highlight">firstsite</span>.com www.<span class="highlight">firstsite</span>.com;
}
</code></pre>
<p>Next, we can tell Nginx not to worry if it can't find a favicon.  We will also point it to the location of our static files directory where we collected our site's static elements:</p>
<pre class="code-pre "><code langs="">server {
    listen 80;
    server_name <span class="highlight">firstsite</span>.com www.<span class="highlight">firstsite</span>.com;

    location = /favicon.ico { access_log off; log_not_found off; }
    location /static/ {
        root /home/<span class="highlight">user</span>/<span class="highlight">firstsite</span>;
    }
}
</code></pre>
<p>After that, we can use the <code>uwsgi_pass</code> directive to pass the traffic to our socket file.  The socket file that we configured was called <code><span class="highlight">firstproject</span>.sock</code> and it was located in our project directory.  We will use the <code>include</code> directive to include the necessary <code>uwsgi</code> parameters to handle the connection:</p>
<pre class="code-pre "><code langs="">server {
    listen 80;
    server_name <span class="highlight">firstsite</span>.com www.<span class="highlight">firstsite</span>.com;

    location = /favicon.ico { access_log off; log_not_found off; }
    location /static/ {
        root /home/<span class="highlight">user</span>/<span class="highlight">firstsite</span>;
    }

    location / {
        include         uwsgi_params;
        uwsgi_pass      unix:/home/<span class="highlight">user</span>/<span class="highlight">firstsite</span>/<span class="highlight">firstsite</span>.sock;
    }
}
</code></pre>
<p>That is actually all the configuration we need.  Save and close the file when you are finished.</p>

<p>We will use this as a basis for our second project's Nginx configuration file.  Copy it over now:</p>
<pre class="code-pre "><code langs="">sudo cp /etc/nginx/sites-available/<span class="highlight">firstsite</span> /etc/nginx/sites-available/<span class="highlight">secondsite</span>
</code></pre>
<p>Open the new file in your text editor:</p>
<pre class="code-pre "><code langs="">sudo nano /etc/nginx/sites-available/<span class="highlight">secondsite</span>
</code></pre>
<p>Here, you'll have to change any reference to <code><span class="highlight">firstsite</span></code> with a reference to <code><span class="highlight">secondsite</span></code>.  You'll also have to modify the <code>server_name</code> so that your second project responds to a different domain name.  When you are finished, it will look something like this:</p>
<pre class="code-pre "><code langs="">server {
    listen 80;
    server_name <span class="highlight">secondsite</span>.com www.<span class="highlight">secondsite</span>.com;

    location = /favicon.ico { access_log off; log_not_found off; }
    location /static/ {
        root /home/<span class="highlight">user</span>/<span class="highlight">secondsite</span>;
    }

    location / {
        include         uwsgi_params;
        uwsgi_pass      unix:/home/<span class="highlight">user</span>/<span class="highlight">secondsite</span>/<span class="highlight">secondsite</span>.sock;
    }
}
</code></pre>
<p>Save and close the file when you are finished.</p>

<p>Next, link both of your new configuration files to Nginx's <code>sites-enabled</code> directory to enable them:</p>
<pre class="code-pre "><code langs="">sudo ln -s /etc/nginx/sites-available/<span class="highlight">firstsite</span> /etc/nginx/sites-enabled
sudo ln -s /etc/nginx/sites-available/<span class="highlight">secondsite</span> /etc/nginx/sites-enabled
</code></pre>
<p>Check the configuration syntax by typing:</p>
<pre class="code-pre "><code langs="">sudo service nginx configtest
</code></pre>
<p>If no syntax errors are detected, you can restart your Nginx service to load the new configuration:</p>
<pre class="code-pre "><code langs="">sudo service nginx restart
</code></pre>
<p>If you remember from earlier, we never actually started the uWSGI server.  Do that now by typing:</p>
<pre class="code-pre "><code langs="">sudo service uwsgi start
</code></pre>
<p>You should now be able to reach your two projects by going to their respective domain names.  Both the public and administrative interfaces should work as expected.</p>

<h2 id="conclusion">Conclusion</h2>

<p>In this guide, we've set up two Django projects, each in their own virtual environments.  We've configured uWSGI to serve each project independently using the virtual environment configured for each.  Afterwards, we set up Nginx to act as a reverse proxy to handle client connections and serve the correct project depending on the client request.</p>

<p>Django makes creating projects and applications simple by providing many of the common pieces, allowing you to focus on the unique elements.  By leveraging the general tool chain described in this article, you can easily serve the applications you create from a single server.</p>

    