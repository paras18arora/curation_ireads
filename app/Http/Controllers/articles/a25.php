<link rel='stylesheet' href='digital_ocean.css'><br> <br> 
      <h3 id="introduction">Introduction</h3>

<p>Django is a powerful web framework that can help you get your Python application or website off the ground.  Django includes a simplified development server for testing your code locally, but for anything even slightly production related, a more secure and powerful web server is required.</p>

<p>In this guide, we will demonstrate how to install and configure some components on Ubuntu 16.04 to support and serve Django applications.  We will be setting up a PostgreSQL database instead of using the default SQLite database.  We will configure the Gunicorn application server to interface with our applications.  We will then set up Nginx to reverse proxy to Gunicorn, giving us access to its security and performance features to serve our apps.</p>

<h2 id="prerequisites-and-goals">Prerequisites and Goals</h2>

<p>In order to complete this guide, you should have a fresh Ubuntu 16.04 server instance with a non-root user with <code>sudo</code> privileges configured.  You can learn how to set this up by running through our <a href="https://indiareads/community/tutorials/initial-server-setup-with-ubuntu-16-04">initial server setup guide</a>.</p>

<p>We will be installing Django within a virtual environment.  Installing Django into an environment specific to your project will allow your projects and their requirements to be handled separately.</p>

<p>Once we have our database and application up and running, we will install and configure the Gunicorn application server.  This will serve as an interface to our application, translating client requests in HTTP to Python calls that our application can process.  We will then set up Nginx in front of Gunicorn to take advantage of its high performance connection handling mechanisms and its easy-to-implement security features.</p>

<p>Let's get started.</p>

<h2 id="install-the-packages-from-the-ubuntu-repositories">Install the Packages from the Ubuntu Repositories</h2>

<p>To begin the process, we'll download and install all of the items we need from the Ubuntu repositories.  We will use the Python package manager <code>pip</code> to install additional components a bit later.</p>

<p>We need to update the local <code>apt</code> package index and then download and install the packages.  The packages we install depend on which version of Python your project will use.</p>

<p>If you are using <strong>Python 2</strong>, type:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo apt-get update
</li><li class="line" prefix="$">sudo apt-get install python-pip python-dev libpq-dev postgresql postgresql-contrib nginx
</li></ul></code></pre>
<p>If you are using Django with <strong>Python 3</strong>, type:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo apt-get update
</li><li class="line" prefix="$">sudo apt-get install python3-pip python3-dev libpq-dev postgresql postgresql-contrib nginx
</li></ul></code></pre>
<p>This will install <code>pip</code>, the Python development files needed to build Gunicorn later, the Postgres database system and the libraries needed to interact with it, and the Nginx web server.</p>

<h2 id="create-the-postgresql-database-and-user">Create the PostgreSQL Database and User</h2>

<p>We're going to jump right in and create a database and database user for our Django application.</p>

<p>By default, Postgres uses an authentication scheme called "peer authentication" for local connections. Basically, this means that if the user's operating system username matches a valid Postgres username, that user can login with no further authentication.</p>

<p>During the Postgres installation, an operating system user named <code>postgres</code> was created to correspond to the <code>postgres</code> PostgreSQL administrative user. We need to use this user to perform administrative tasks. We can use sudo and pass in the username with the -u option.</p>

<p>Log into an interactive Postgres session by typing:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo -u postgres psql
</li></ul></code></pre>
<p>You will be given a PostgreSQL prompt where we can set up our requirements.</p>

<p>First, create a database for your project:</p>
<pre class="code-pre custom_prefix"><code langs=""><ul class="prefixed"><li class="line" prefix="postgres=#">CREATE DATABASE <span class="highlight">myproject</span>;
</li></ul></code></pre>
<p></p><div class="code-label notes-and-warnings note" title="Note">Note</div><span class="note">
Every Postgres statement must end with a semi-colon, so make sure that your command ends with one if you are experiencing issues.<br /></span>

<p>Next, create a database user for our project.  Make sure to select a secure password:</p>
<pre class="code-pre custom_prefix"><code langs=""><ul class="prefixed"><li class="line" prefix="postgres=#">CREATE USER <span class="highlight">myprojectuser</span> WITH PASSWORD '<span class="highlight">password</span>';
</li></ul></code></pre>
<p>Afterwards, we'll modify a few of the connection parameters for the user we just created. This will speed up database operations so that the correct values do not have to be queried and set each time a connection is established.</p>

<p>We are setting the default encoding to UTF-8, which Django expects. We are also setting the default transaction isolation scheme to "read committed", which blocks reads from uncommitted transactions. Lastly, we are setting the timezone. By default, our Django projects will be set to use <code>UTC</code>. These are all recommendations from <a href="https://docs.djangoproject.com/en/1.9/ref/databases/#optimizing-postgresql-s-configuration">the Django project itself</a>:</p>
<pre class="code-pre custom_prefix"><code langs=""><ul class="prefixed"><li class="line" prefix="postgres=#">ALTER ROLE <span class="highlight">myprojectuser</span> SET client_encoding TO 'utf8';
</li><li class="line" prefix="postgres=#">ALTER ROLE <span class="highlight">myprojectuser</span> SET default_transaction_isolation TO 'read committed';
</li><li class="line" prefix="postgres=#">ALTER ROLE <span class="highlight">myprojectuser</span> SET timezone TO 'UTC';
</li></ul></code></pre>
<p>Now, we can give our new user access to administer our new database:</p>
<pre class="code-pre custom_prefix"><code langs=""><ul class="prefixed"><li class="line" prefix="postgres=#">GRANT ALL PRIVILEGES ON DATABASE <span class="highlight">myproject</span> TO <span class="highlight">myprojectuser</span>;
</li></ul></code></pre>
<p>When you are finished, exit out of the PostgreSQL prompt by typing:</p>
<pre class="code-pre custom_prefix"><code langs=""><ul class="prefixed"><li class="line" prefix="postgres=#">\q
</li></ul></code></pre>
<h2 id="create-a-python-virtual-environment-for-your-project">Create a Python Virtual Environment for your Project</h2>

<p>Now that we have our database, we can begin getting the rest of our project requirements ready.  We will be installing our Python requirements within a virtual environment for easier management.</p>

<p>To do this, we first need access to the <code>virtualenv</code> command.  We can install this with <code>pip</code>.</p>

<p>If you are using <strong>Python 2</strong>, type:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo pip install virtualenv
</li></ul></code></pre>
<p>If you are using <strong>Python 3</strong>, type:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo pip3 install virtualenv
</li></ul></code></pre>
<p>With <code>virtualenv</code> installed, we can start forming our project.  Create and move into a directory where we can keep our project files:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">mkdir ~/<span class="highlight">myproject</span>
</li><li class="line" prefix="$">cd ~/<span class="highlight">myproject</span>
</li></ul></code></pre>
<p>Within the project directory, create a Python virtual environment by typing:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">virtualenv <span class="highlight">myprojectenv</span>
</li></ul></code></pre>
<p>This will create a directory called <code><span class="highlight">myprojectenv</span></code> within your <code><span class="highlight">myproject</span></code> directory.  Inside, it will install a local version of Python and a local version of <code>pip</code>.  We can use this to install and configure an isolated Python environment for our project.</p>

<p>Before we install our project's Python requirements, we need to activate the virtual environment.  You can do that by typing:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">source <span class="highlight">myprojectenv</span>/bin/activate
</li></ul></code></pre>
<p>Your prompt should change to indicate that you are now operating within a Python virtual environment.  It will look something like this: <code>(<span class="highlight">myprojectenv</span>)<span class="highlight">user</span>@<span class="highlight">host</span>:~/<span class="highlight">myproject</span>$</code>.</p>

<p>With your virtual environment active, install Django, Gunicorn, and the <code>psycopg2</code> PostgreSQL adaptor with the local instance of <code>pip</code>:</p>

<p></p><div class="code-label notes-and-warnings note" title="Note">Note</div><span class="note">
Regardless of which version of Python you are using, when the virtual environment is activated, you should use the <code>pip</code> command (not <code>pip3</code>).<br /></span>
<pre class="code-pre custom_prefix"><code langs=""><ul class="prefixed"><li class="line" prefix="(myprojectenv) $">pip install django gunicorn psycopg2
</li></ul></code></pre>
<h2 id="create-and-configure-a-new-django-project">Create and Configure a New Django Project</h2>

<p>With our Python components installed, we can create the actual Django project files.</p>

<h3 id="create-the-django-project">Create the Django Project</h3>

<p>Since we already have a project directory, we will tell Django to install the files here.  It will create a second level directory with the actual code, which is normal, and place a management script in this directory.  The key to this is the dot at the end that tells Django to create the files in the current directory:</p>
<pre class="code-pre custom_prefix"><code langs=""><ul class="prefixed"><li class="line" prefix="(myprojectenv) $">django-admin.py startproject <span class="highlight">myproject</span> .
</li></ul></code></pre>
<h3 id="adjust-the-project-settings">Adjust the Project Settings</h3>

<p>The first thing we should do with our newly created project files is adjust the settings.  Open the settings file in your text editor:</p>
<pre class="code-pre custom_prefix"><code langs=""><ul class="prefixed"><li class="line" prefix="(myprojectenv) $">nano <span class="highlight">myproject</span>/settings.py
</li></ul></code></pre>
<p>Start by finding the section that configures database access.  It will start with <code>DATABASES</code>.  The configuration in the file is for a SQLite database.  We already created a PostgreSQL database for our project, so we need to adjust the settings.</p>

<p>Change the settings with your PostgreSQL database information.  We tell Django to use the <code>psycopg2</code> adaptor we installed with <code>pip</code>.  We need to give the database name, the database username, the database user's password, and then specify that the database is located on the local computer.  You can leave the <code>PORT</code> setting as an empty string:</p>
<div class="code-label " title="~/myproject/myproject/settings.py">~/myproject/myproject/settings.py</div><pre class="code-pre "><code langs="">. . .

DATABASES = {
    'default': {
        'ENGINE': 'django.db.backends.<span class="highlight">postgresql_psycopg2</span>',
        'NAME': '<span class="highlight">myproject</span>',
        'USER': '<span class="highlight">myprojectuser</span>',
        'PASSWORD': '<span class="highlight">password</span>',
        'HOST': 'localhost',
        'PORT': '',
    }
}

. . .
</code></pre>
<p>Next, move down to the bottom of the file and add a setting indicating where the static files should be placed.  This is necessary so that Nginx can handle requests for these items.  The following line tells Django to place them in a directory called <code>static</code> in the base project directory:</p>
<div class="code-label " title="~/myproject/myproject/settings.py">~/myproject/myproject/settings.py</div><pre class="code-pre "><code langs="">. . .

STATIC_URL = '/static/'
<span class="highlight">STATIC_ROOT = os.path.join(BASE_DIR, 'static/')</span>
</code></pre>
<p>Save and close the file when you are finished.</p>

<h3 id="complete-initial-project-setup">Complete Initial Project Setup</h3>

<p>Now, we can migrate the initial database schema to our PostgreSQL database using the management script:</p>
<pre class="code-pre custom_prefix"><code langs=""><ul class="prefixed"><li class="line" prefix="(myprojectenv) $">cd ~/<span class="highlight">myproject</span>
</li><li class="line" prefix="(myprojectenv) $">./manage.py makemigrations
</li><li class="line" prefix="(myprojectenv) $">./manage.py migrate
</li></ul></code></pre>
<p>Create an administrative user for the project by typing:</p>
<pre class="code-pre custom_prefix"><code langs=""><ul class="prefixed"><li class="line" prefix="(myprojectenv) $">./manage.py createsuperuser
</li></ul></code></pre>
<p>You will have to select a username, provide an email address, and choose and confirm a password.</p>

<p>We can collect all of the static content into the directory location we configured by typing:</p>
<pre class="code-pre custom_prefix"><code langs=""><ul class="prefixed"><li class="line" prefix="(myprojectenv) $">./manage.py collectstatic
</li></ul></code></pre>
<p>You will have to confirm the operation.  The static files will then be placed in a directory called <code>static</code> within your project directory.</p>

<p>If you followed the initial server setup guide, you should have a UFW firewall protecting your server.  In order to test the development server, we'll have to allow access to the port we'll be using.</p>

<p>Create an exception for port 8000 by typing:</p>
<pre class="code-pre custom_prefix"><code langs=""><ul class="prefixed"><li class="line" prefix="(myprojectenv) $">sudo ufw allow 8000
</li></ul></code></pre>
<p>Finally, you can test our your project by starting up the Django development server with this command:</p>
<pre class="code-pre custom_prefix"><code langs=""><ul class="prefixed"><li class="line" prefix="(myprojectenv) $">./manage.py runserver 0.0.0.0:8000
</li></ul></code></pre>
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
<pre class="code-pre custom_prefix"><code langs=""><ul class="prefixed"><li class="line" prefix="(myprojectenv) $">cd ~/<span class="highlight">myproject</span>
</li><li class="line" prefix="(myprojectenv) $">gunicorn --bind 0.0.0.0:8000 <span class="highlight">myproject</span>.wsgi:application
</li></ul></code></pre>
<p>This will start Gunicorn on the same interface that the Django development server was running on.  You can go back and test the app again.  Note that the admin interface will not have any of the styling applied since Gunicorn does not know about the static content responsible for this.</p>

<p>We passed Gunicorn a module by specifying the relative directory path to Django's <code>wsgi.py</code> file, which is the entry point to our application, using Python's module syntax.  Inside of this file, a function called <code>application</code> is defined, which is used to communicate with the application.  To learn more about the WSGI specification, click <a href="https://indiareads/community/tutorials/how-to-set-up-uwsgi-and-nginx-to-serve-python-apps-on-ubuntu-14-04#definitions-and-concepts">here</a>.</p>

<p>When you are finished testing, hit CTRL-C in the terminal window to stop Gunicorn.</p>

<p>We're now finished configuring our Django application.  We can back out of our virtual environment by typing:</p>
<pre class="code-pre custom_prefix"><code langs=""><ul class="prefixed"><li class="line" prefix="(myprojectenv) $">deactivate
</li></ul></code></pre>
<h2 id="create-a-gunicorn-systemd-service-file">Create a Gunicorn systemd Service File</h2>

<p>We have tested that Gunicorn can interact with our Django application, but we should implement a more robust way of starting and stopping the application server. To accomplish this, we'll make a systemd service file.</p>

<p>Create and open a systemd service file for Gunicorn with <code>sudo</code> privileges in your text editor:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo nano /etc/systemd/system/gunicorn.service
</li></ul></code></pre>
<p>Start with the <code>[Unit]</code> section, which is used to specify metadata and dependencies. We'll put a description of our service here and tell the init system to only start this after the networking target has been reached:</p>
<div class="code-label " title="/etc/systemd/system/gunicorn.service">/etc/systemd/system/gunicorn.service</div><pre class="code-pre "><code langs="">[Unit]
Description=gunicorn daemon
After=network.target
</code></pre>
<p>Next, we'll open up the <code>[Service]</code> section. We'll specify the user and group that we want to process to run under. We will give our regular user account ownership of the process since it owns all of the relevant files. We'll give group ownership to the <code>www-data</code> group so that Nginx can communicate easily with Gunicorn.</p>

<p>We'll then map out the working directory and specify the command to use to start the service. In this case, we'll have to specify the full path to the Gunicorn executable, which is installed within our virtual environment. We will bind it to a Unix socket within the project directory since Nginx is installed on the same computer. This is safer and faster than using a network port. We can also specify any optional Gunicorn tweaks here. For example, we specified 3 worker processes in this case:</p>
<div class="code-label " title="/etc/systemd/system/gunicorn.service">/etc/systemd/system/gunicorn.service</div><pre class="code-pre "><code langs="">[Unit]
Description=gunicorn daemon
After=networking.target

[Service]
User=<span class="highlight">sammy</span>
Group=www-data
WorkingDirectory=/home/<span class="highlight">sammy</span>/<span class="highlight">myproject</span>
ExecStart=/home/<span class="highlight">sammy</span>/<span class="highlight">myproject</span>/<span class="highlight">myprojectenv</span>/bin/gunicorn --workers 3 --bind unix:/home/<span class="highlight">sammy</span>/<span class="highlight">myproject</span>/<span class="highlight">myproject</span>.sock myproject.wsgi:application
</code></pre>
<p>Finally, we'll add an <code>[Install]</code> section. This will tell systemd what to link this service to if we enable it to start at boot. We want this service to start when the regular multi-user system is up and running:</p>
<div class="code-label " title="/etc/systemd/system/gunicorn.service">/etc/systemd/system/gunicorn.service</div><pre class="code-pre "><code langs="">[Unit]
Description=gunicorn daemon
After=network.target

[Service]
User=<span class="highlight">sammy</span>
Group=www-data
WorkingDirectory=/home/<span class="highlight">sammy</span>/<span class="highlight">myproject</span>
ExecStart=/home/<span class="highlight">sammy</span>/<span class="highlight">myproject</span>/<span class="highlight">myprojectenv</span>/bin/gunicorn --workers 3 --bind unix:/home/<span class="highlight">sammy</span>/<span class="highlight">myproject</span>/<span class="highlight">myproject</span>.sock myproject.wsgi:application

[Install]
WantedBy=multi-user.target
</code></pre>
<p>With that, our systemd service file is complete. Save and close it now.</p>

<p>We can now start the Gunicorn service we created and enable it so that it starts at boot:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo systemctl start gunicorn
</li><li class="line" prefix="$">sudo systemctl enable gunicorn
</li></ul></code></pre>
<h2 id="configure-nginx-to-proxy-pass-to-gunicorn">Configure Nginx to Proxy Pass to Gunicorn</h2>

<p>Now that Gunicorn is set up, we need to configure Nginx to pass traffic to the process.</p>

<p>Start by creating and opening a new server block in Nginx's <code>sites-available</code> directory:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo nano /etc/nginx/sites-available/<span class="highlight">myproject</span>
</li></ul></code></pre>
<p>Inside, open up a new server block.  We will start by specifying that this block should listen on the normal port 80 and that it should respond to our server's domain name or IP address:</p>
<div class="code-label " title="/etc/nginx/sites-available/myproject">/etc/nginx/sites-available/myproject</div><pre class="code-pre "><code langs="">server {
    listen 80;
    server_name <span class="highlight">server_domain_or_IP</span>;
}
</code></pre>
<p>Next, we will tell Nginx to ignore any problems with finding a favicon.  We will also tell it where to find the static assets that we collected in our <code>~/<span class="highlight">myproject</span>/static</code> directory.  All of these files have a standard URI prefix of "/static", so we can create a location block to match those requests:</p>
<div class="code-label " title="/etc/nginx/sites-available/myproject">/etc/nginx/sites-available/myproject</div><pre class="code-pre "><code langs="">server {
    listen 80;
    server_name <span class="highlight">server_domain_or_IP</span>;

    location = /favicon.ico { access_log off; log_not_found off; }
    location /static/ {
        root /home/<span class="highlight">sammy</span>/<span class="highlight">myproject</span>;
    }
}
</code></pre>
<p>Finally, we'll create a <code>location / {}</code> block to match all other requests.  Inside of this location, we'll include the standard <code>proxy_params</code> file included with the Nginx installation and then we will pass the traffic to the socket that our Gunicorn process created:</p>
<div class="code-label " title="/etc/nginx/sites-available/myproject">/etc/nginx/sites-available/myproject</div><pre class="code-pre "><code langs="">server {
    listen 80;
    server_name <span class="highlight">server_domain_or_IP</span>;

    location = /favicon.ico { access_log off; log_not_found off; }
    location /static/ {
        root /home/<span class="highlight">sammy</span>/<span class="highlight">myproject</span>;
    }

    location / {
        include proxy_params;
        proxy_pass http://unix:/home/<span class="highlight">sammy</span>/<span class="highlight">myproject</span>/<span class="highlight">myproject</span>.sock;
    }
}
</code></pre>
<p>Save and close the file when you are finished.  Now, we can enable the file by linking it to the <code>sites-enabled</code> directory:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo ln -s /etc/nginx/sites-available/<span class="highlight">myproject</span> /etc/nginx/sites-enabled
</li></ul></code></pre>
<p>Test your Nginx configuration for syntax errors by typing:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo nginx -t
</li></ul></code></pre>
<p>If no errors are reported, go ahead and restart Nginx by typing:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo systemctl restart nginx
</li></ul></code></pre>
<p>Finally, we need to open up our firewall to normal traffic on port 80.  Since we no longer need access to the development server, we can remove the rule to open port 8000 as well:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo ufw delete allow 8000
</li><li class="line" prefix="$">sudo ufw allow 'Nginx Full'
</li></ul></code></pre>
<p>You should now be able to go to your server's domain or IP address to view your application.</p>

<div class="code-label notes-and-warnings note" title="Note">Note</div><span class="note"><p>

After configuring Nginx, the next step should be securing traffic to the server using SSL/TLS.  This is important because without it, all information, including passwords are sent over the network in plain text.</p>

<p>The easiest way get an SSL certificate to secure your traffic is using Let's Encrypt.  Follow <a href="https://indiareads/community/tutorials/how-to-secure-nginx-with-let-s-encrypt-on-ubuntu-16-04">this guide</a> to set up Let's Encrypt with Nginx on Ubuntu 16.04.<br /></p></span>

<h2 id="conclusion">Conclusion</h2>

<p>In this guide, we've set up a Django project in its own virtual environment.  We've configured Gunicorn to translate client requests so that Django can handle them.  Afterwards, we set up Nginx to act as a reverse proxy to handle client connections and serve the correct project depending on the client request.</p>

<p>Django makes creating projects and applications simple by providing many of the common pieces, allowing you to focus on the unique elements.  By leveraging the general tool chain described in this article, you can easily serve the applications you create from a single server.</p>

    