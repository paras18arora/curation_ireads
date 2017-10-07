<link rel='stylesheet' href='digital_ocean.css'><br> <br> 
      <h3 id="introduction">Introduction</h3>

<p>Django is a powerful web framework that can help you get your Python application or website off the ground.  Django includes a simplified development server for testing your code locally, but for anything even slightly production related, a more secure and powerful web server is required.</p>

<p>In this guide, we will demonstrate how to install and configure some components on Ubuntu 14.04 to support and serve Django applications.  We will be setting up a PostgreSQL database instead of using the default SQLite database.  We will configure the Gunicorn application server to interface with our applications.  We will then set up Nginx to reverse proxy to Gunicorn, giving us access to its security and performance features to serve our apps.</p>

<h2 id="prerequisites-and-goals">Prerequisites and Goals</h2>

<p>In order to complete this guide, you should have a fresh Ubuntu 14.04 server instance with a non-root user with <code>sudo</code> privileges configured.  You can learn how to set this up by running through our <a href="https://indiareads/community/tutorials/initial-server-setup-with-ubuntu-14-04">initial server setup guide</a>.</p>

<p>We will be installing Django within a virtual environment.  Installing Django into an environment specific to your project will allow your projects and their requirements to be handled separately.</p>

<p>Once we have our database and application up and running, we will install and configure the Gunicorn application server.  This will serve as an interface to our application, translating client requests in HTTP to Python calls that our application can process.  We will then set up Nginx in front of Gunicorn to take advantage of its high performance connection handling mechanisms and its easy-to-implement security features.</p>

<p>Let's get started.</p>

<h2 id="install-the-packages-from-the-ubuntu-repositories">Install the Packages from the Ubuntu Repositories</h2>

<p>To begin the process, we'll download and install all of the items we need from the Ubuntu repositories.  We will use the Python package manager <code>pip</code> to install additional components a bit later.</p>

<p>First, update the local package index and then download and install the packages:</p>
<pre class="code-pre "><code langs="">sudo apt-get update
sudo apt-get install python-pip python-dev libpq-dev postgresql postgresql-contrib nginx
</code></pre>
<p>This will install <code>pip</code>, the Python development files needed to build Gunicorn later, the Postgres database system and the libraries needed to interact with it, and the Nginx web server.</p>

<h2 id="create-the-postgresql-database-and-user">Create the PostgreSQL Database and User</h2>

<p>We're going to jump right in and create a database and database user for our Django application.</p>

<p>To work with Postgres in its default configuration, it is best to change to the <code>postgres</code> system user temporarily.  Do that now by typing:</p>
<pre class="code-pre "><code langs="">sudo su - postgres
</code></pre>
<p>When operating as the <code>postgres</code> user, you can log right into a PostgreSQL interactive session with no further authentication by typing:</p>
<pre class="code-pre "><code langs="">psql
</code></pre>
<p>You will be given a PostgreSQL prompt where we can set up our requirements.</p>

<p>First, create a database for your project:</p>
<pre class="code-pre "><code langs="">CREATE DATABASE <span class="highlight">myproject</span>;
</code></pre>
<p>Every command must end with a semi-colon, so check that your command ends with one if you are experiencing issues.</p>

<p>Next, create a database user for our project.  Make sure to select a secure password:</p>
<pre class="code-pre "><code langs="">CREATE USER <span class="highlight">myprojectuser</span> WITH PASSWORD '<span class="highlight">password</span>';
</code></pre>
<p>Now, we can give our new user access to administer our new database:</p>
<pre class="code-pre "><code langs="">GRANT ALL PRIVILEGES ON DATABASE <span class="highlight">myproject</span> TO <span class="highlight">myprojectuser</span>;
</code></pre>
<p>When you are finished, exit out of the PostgreSQL prompt by typing:</p>
<pre class="code-pre "><code langs="">\q
</code></pre>
<p>Now, exit out of the <code>postgres</code> user's shell session to get back to your normal user's shell session by typing:</p>
<pre class="code-pre "><code langs="">exit
</code></pre>
<h2 id="create-a-python-virtual-environment-for-your-project">Create a Python Virtual Environment for your Project</h2>

<p>Now that we have our database ready, we can begin getting the rest of our project requirements ready.  We will be installing our Python requirements within a virtual environment for easier management.</p>

<p>To do this, we first need access to the <code>virtualenv</code> command.  We can install this with <code>pip</code>:</p>
<pre class="code-pre "><code langs="">sudo pip install virtualenv
</code></pre>
<p>With <code>virtualenv</code> installed, we can start forming our project.  Create a directory where you wish to keep your project and move into the directory afterwards:</p>
<pre class="code-pre "><code langs="">mkdir ~/<span class="highlight">myproject</span>
cd ~/<span class="highlight">myproject</span>
</code></pre>
<p>Within the project directory, create a Python virtual environment by typing:</p>
<pre class="code-pre "><code langs="">virtualenv <span class="highlight">myprojectenv</span>
</code></pre>
<p>This will create a directory called <code><span class="highlight">myprojectenv</span></code> within your <code><span class="highlight">myproject</span></code> directory.  Inside, it will install a local version of Python and a local version of <code>pip</code>.  We can use this to install and configure an isolated Python environment for our project.</p>

<p>Before we install our project's Python requirements, we need to activate the virtual environment.  You can do that by typing:</p>
<pre class="code-pre "><code langs="">source <span class="highlight">myprojectenv</span>/bin/activate
</code></pre>
<p>Your prompt should change to indicate that you are now operating within a Python virtual environment.  It will look something like this: <code>(<span class="highlight">myprojectenv</span>)<span class="highlight">user</span>@<span class="highlight">host</span>:~/<span class="highlight">myproject</span>$</code>.</p>

<p>With your virtual environment active, install Django, Gunicorn, and the <code>psycopg2</code> PostgreSQL adaptor with the local instance of <code>pip</code>:</p>
<pre class="code-pre "><code langs="">pip install django gunicorn psycopg2
</code></pre>
<h2 id="create-and-configure-a-new-django-project">Create and Configure a New Django Project</h2>

<p>With our Python components installed, we can create the actual Django project files.</p>

<h3 id="create-the-django-project">Create the Django Project</h3>

<p>Since we already have a project directory, we will tell Django to install the files here.  It will create a second level directory with the actual code, which is normal, and place a management script in this directory.  The key to this is the dot at the end that tells Django to create the files in the current directory:</p>
<pre class="code-pre "><code langs="">django-admin.py startproject <span class="highlight">myproject</span> .
</code></pre>
<h3 id="adjust-the-project-settings">Adjust the Project Settings</h3>

<p>The first thing we should do with our newly created project files is adjust the settings.  Open the settings file in your text editor:</p>
<pre class="code-pre "><code langs="">nano <span class="highlight">myproject</span>/settings.py
</code></pre>
<p>Start by finding the section that configures database access.  It will start with <code>DATABASES</code>.  The configuration in the file is for a SQLite database.  We already created a PostgreSQL database for our project, so we need to adjust the settings.</p>

<p>Change the settings with your PostgreSQL database information.  We tell Django to use the <code>psycopg2</code> adaptor we installed with <code>pip</code>.  We need to give the database name, the database username, the database username's password, and then specify that the database is located on the local computer.  You can leave the <code>PORT</code> setting as an empty string:</p>
<pre class="code-pre "><code langs="">DATABASES = {
    'default': {
        'ENGINE': '<span class="highlight">django.db.backends.postgresql_psycopg2</span>',
        'NAME': '<span class="highlight">myproject</span>',
        'USER': '<span class="highlight">myprojectuser</span>',
        'PASSWORD': '<span class="highlight">password</span>',
        'HOST': 'localhost',
        'PORT': '',
    }
}

</code></pre>
<p>Next, move down to the bottom of the file and add a setting indicating where the static files should be placed.  This is necessary so that Nginx can handle requests for these items.  The following line tells Django to place them in a directory called <code>static</code> in the base project directory:</p>
<pre class="code-pre "><code langs="">STATIC_ROOT = os.path.join(BASE_DIR, "static/")
</code></pre>
<p>Save and close the file when you are finished.</p>

<h3 id="complete-initial-project-setup">Complete Initial Project Setup</h3>

<p>Now, we can migrate the initial database schema to our PostgreSQL database using the management script:</p>
<pre class="code-pre "><code langs="">cd ~/<span class="highlight">myproject</span>
./manage.py makemigrations
./manage.py migrate
</code></pre>
<p>Create an administrative user for the project by typing:</p>
<pre class="code-pre "><code langs="">./manage.py createsuperuser
</code></pre>
<p>You will have to select a username, provide an email address, and choose and confirm a password.</p>

<p>We can collect all of the static content into the directory location we configured by typing:</p>
<pre class="code-pre "><code langs="">./manage.py collectstatic
</code></pre>
<p>You will have to confirm the operation.  The static files will then be placed in a directory called <code>static</code> within your project directory.</p>

<p>Finally, you can test our your project by starting up the Django development server with this command:</p>
<pre class="code-pre "><code langs="">./manage.py runserver 0.0.0.0:8000
</code></pre>
<p>In your web browser, visit your server's domain name or IP address followed by <code>:8000</code>:</p>
<pre class="code-pre "><code langs="">http://<span class="highlight">server_domain_or_IP</span>:8000
</code></pre>
<p>You should see the default Django index page:</p>

<p><img src="https://assets.digitalocean.com/articles/django_gunicorn_nginx_1404/django_index.png" alt="Django index page" /></p>

<p>If you append <code>/admin</code> to the end of the URL in the address bar, you will be prompted for the administrative username and password you created with the <code>createsuperuser</code> command:</p>

<p><img src="https://assets.digitalocean.com/articles/django_gunicorn_nginx_1404/admin_login.png" alt="Django admin login" /></p>

<p>After authenticating, you can access the default Django admin interface:</p>

<p><img src="https://assets.digitalocean.com/articles/django_gunicorn_nginx_1404/admin_interface.png" alt="Django admin interface" /></p>

<p>When you are finished exploring, hit CTRL-C in the terminal window to shut down the development server.</p>

<h3 id="testing-gunicorn-39-s-ability-to-serve-the-project">Testing Gunicorn's Ability to Serve the Project</h3>

<p>The last thing we want to do before leaving our virtual environment is test Gunicorn to make sure that it can serve the application.  We can do this easily by typing:</p>
<pre class="code-pre "><code langs="">cd ~/<span class="highlight">myproject</span>
gunicorn --bind 0.0.0.0:8000 <span class="highlight">myproject</span>.wsgi:application
</code></pre>
<p>This will start Gunicorn on the same interface that the Django development server was running on.  You can go back and test the app again.  Note that the admin interface will not have any of the styling applied since Gunicorn does not know about the static content responsible for this.</p>

<p>We passed Gunicorn a module by specifying the relative directory path to Django's <code>wsgi.py</code> file, which is the entry point to our application, using Python's module syntax.  Inside of this file, a function called <code>application</code> is defined, which is used to communicate with the application.  To learn more about the WSGI specification, click <a href="https://indiareads/community/tutorials/how-to-set-up-uwsgi-and-nginx-to-serve-python-apps-on-ubuntu-14-04#definitions-and-concepts">here</a>.</p>

<p>When you are finished testing, hit CTRL-C in the terminal window to stop Gunicorn.</p>

<p>We're now finished configuring our Django application.  We can back out of our virtual environment by typing:</p>
<pre class="code-pre "><code langs="">deactivate
</code></pre>
<h2 id="create-a-gunicorn-upstart-file">Create a Gunicorn Upstart File</h2>

<p>We have tested that Gunicorn can interact with our Django application, but we should implement a more robust way of starting and stopping the application server.  To accomplish this, we'll make an Upstart script.</p>

<p>Create and open an Upstart file for Gunicorn with <code>sudo</code> privileges in your text editor:</p>
<pre class="code-pre "><code langs="">sudo nano /etc/init/gunicorn.conf
</code></pre>
<p>We'll start with a simple description string to state what our service file is for.  We'll then move on to defining the system runlevels where this service should be automatically started.  The normal runlevels to run services are 2, 3, 4, and 5.  We'll run our service when the system is in any of those.  We'll tell it to stop when its in any other runlevel (such as when the system is rebooting, shutting down, or in single-user mode):</p>
<pre class="code-pre "><code langs="">description "Gunicorn application server handling <span class="highlight">myproject</span>"

start on runlevel [2345]
stop on runlevel [!2345]
</code></pre>
<p>Next, we'll tell Upstart to automatically restart the service if it fails.  We also want to specify the user and group to run under.  We'll use our normal user since all of our files are owned by that user.  We'll let the <code>www-data</code> group which Nginx belongs to be the group owners.  We also need to change to our project's directory so that the Gunicorn commands execute correctly:</p>
<pre class="code-pre "><code langs="">description "Gunicorn application server handling <span class="highlight">myproject</span>"

start on runlevel [2345]
stop on runlevel [!2345]

respawn
setuid <span class="highlight">user</span>
setgid www-data
chdir /home/<span class="highlight">user</span>/<span class="highlight">myproject</span>
</code></pre>
<p>Now, we just need to give the command that will start the Gunicorn process.  We need to give the path to the Gunicorn executable, which is stored within our virtual environment.  We will tell it to use a Unix socket instead of a network port to communicate with Nginx, since both services will be running on this server.  This is more secure and faster.  You can add any other configuration for Gunicorn here as well.  For instance, we'll specify that we want 3 worker processses:</p>
<pre class="code-pre "><code langs="">description "Gunicorn application server handling <span class="highlight">myproject</span>"

start on runlevel [2345]
stop on runlevel [!2345]

respawn
setuid <span class="highlight">user</span>
setgid www-data
chdir /home/<span class="highlight">user</span>/<span class="highlight">myproject</span>

exec <span class="highlight">myprojectenv</span>/bin/gunicorn --workers 3 --bind unix:/home/<span class="highlight">user</span>/<span class="highlight">myproject</span>/<span class="highlight">myproject</span>.sock <span class="highlight">myproject</span>.wsgi:application
</code></pre>
<p>When you are finished, save and close the file.</p>

<p>Start the Gunicorn service by typing:</p>
<pre class="code-pre "><code langs="">sudo service gunicorn start
</code></pre>
<h2 id="configure-nginx-to-proxy-pass-to-gunicorn">Configure Nginx to Proxy Pass to Gunicorn</h2>

<p>Now that Gunicorn is set up, we need to configure Nginx to pass traffic to the process.</p>

<p>Start by creating and opening a new server block in Nginx's <code>sites-available</code> directory:</p>
<pre class="code-pre "><code langs="">sudo nano /etc/nginx/sites-available/<span class="highlight">myproject</span>
</code></pre>
<p>Inside, open up a new server block.  We will start by specifying that this block should listen on the normal port 80 and that it should respond to our server's domain name or IP address:</p>
<pre class="code-pre "><code langs="">server {
    listen 80;
    server_name <span class="highlight">server_domain_or_IP</span>;
}
</code></pre>
<p>Next, we will tell Nginx to ignore any problems with finding a favicon.  We will also tell it where to find the static assets that we collected in our <code>~/<span class="highlight">myproject</span>/static</code> directory.  All of these files have a standard URI prefix of "/static", so we can create a location block to match those requests:</p>
<pre class="code-pre "><code langs="">server {
    listen 80;
    server_name <span class="highlight">server_domain_or_IP</span>;

    location = /favicon.ico { access_log off; log_not_found off; }
    location /static/ {
        root /home/<span class="highlight">user</span>/<span class="highlight">myproject</span>;
    }
}
</code></pre>
<p>Finally, we'll create a <code>location / {}</code> block to match all other requests.  Inside of this location, we'll include the standard <code>proxy_params</code> file included with the Nginx installation and then we will pass the traffic to the socket that our Gunicorn process created:</p>
<pre class="code-pre "><code langs="">server {
    listen 80;
    server_name <span class="highlight">server_domain_or_IP</span>;

    location = /favicon.ico { access_log off; log_not_found off; }
    location /static/ {
        root /home/<span class="highlight">user</span>/<span class="highlight">myproject</span>;
    }

    location / {
        include proxy_params;
        proxy_pass http://unix:/home/<span class="highlight">user</span>/<span class="highlight">myproject</span>/<span class="highlight">myproject</span>.sock;
    }
}
</code></pre>
<p>Save and close the file when you are finished.  Now, we can enable the file by linking it to the <code>sites-enabled</code> directory:</p>
<pre class="code-pre "><code langs="">sudo ln -s /etc/nginx/sites-available/<span class="highlight">myproject</span> /etc/nginx/sites-enabled
</code></pre>
<p>Test your Nginx configuration for syntax errors by typing:</p>
<pre class="code-pre "><code langs="">sudo nginx -t
</code></pre>
<p>If no errors are reported, go ahead and restart Nginx by typing:</p>
<pre class="code-pre "><code langs="">sudo service nginx restart
</code></pre>
<p>You should now be able to go to your server's domain or IP address to view your application.</p>

<h2 id="conclusion">Conclusion</h2>

<p>In this guide, we've set up a Django project in its own virtual environment.  We've configured Gunicorn to translate client requests so that Django can handle them.  Afterwards, we set up Nginx to act as a reverse proxy to handle client connections and serve the correct project depending on the client request.</p>

<p>Django makes creating projects and applications simple by providing many of the common pieces, allowing you to focus on the unique elements.  By leveraging the general tool chain described in this article, you can easily serve the applications you create from a single server.</p>

    