<link rel='stylesheet' href='digital_ocean.css'><br> <br> 
      <h3 id="introduction">Introduction</h3>

<p>Django is a flexible framework for quickly creating Python applications.  By default, Django applications are configured to store data into a lightweight SQLite database file.  While this works well under some loads, a more traditional DBMS can improve performance in production.</p>

<p>In this guide, we'll demonstrate how to install and configure PostgreSQL to use with your Django applications.  We will install the necessary software, create database credentials for our application, and then start and configure a new Django project to use this backend.</p>

<h2 id="prerequisites">Prerequisites</h2>

<p>To get started, you will need a clean Ubuntu 16.04 server instance with a non-root user set up.  The non-root user must be configured with <code>sudo</code> privileges.  Learn how to set this up by following our <a href="https://indiareads/community/tutorials/initial-server-setup-with-ubuntu-16-04">initial server setup guide</a>.</p>

<p>When you are ready to continue, log in as your <code>sudo</code> user and read on.</p>

<h2 id="install-the-components-from-the-ubuntu-repositories">Install the Components from the Ubuntu Repositories</h2>

<p>Our first step will be install all of the pieces that we need from the repositories.  We will install <code>pip</code>, the Python package manager, in order to install and manage our Python components.  We will also install the database software and the associated libraries required to interact with them.</p>

<p>Python 2 and Python 3 require slightly different packages, so choose the commands below that match the Python version of your project.</p>

<p>If you are using <strong>Python 2</strong>, type:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo apt-get update
</li><li class="line" prefix="$">sudo apt-get install python-pip python-dev libpq-dev postgresql postgresql-contrib
</li></ul></code></pre>
<p>If, instead, you are using <strong>Python 3</strong>, type:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo apt-get update
</li><li class="line" prefix="$">sudo apt-get install python3-pip python3-dev libpq-dev postgresql postgresql-contrib
</li></ul></code></pre>
<p>With the installation out of the way, we can move on to create our database and database user.</p>

<h2 id="create-a-database-and-database-user">Create a Database and Database User</h2>

<p>By default, Postgres uses an authentication scheme called "peer authentication" for local connections.  Basically, this means that if the user's operating system username matches a valid Postgres username, that user can login with no further authentication.</p>

<p>During the Postgres installation, an operating system user named <code>postgres</code> was created to correspond to the <code>postgres</code> PostgreSQL administrative user.  We need to use this user to perform administrative tasks.  We can use <code>sudo</code> and pass in the username with the <code>-u</code> option.</p>

<p>Log into an interactive Postgres session by typing:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo -u postgres psql
</li></ul></code></pre>
<p>First, we will create a database for our Django project.  Each project should have its own isolated database for security reasons.  We will call our database <code><span class="highlight">myproject</span></code> in this guide, but it's always better to select something more descriptive:</p>
<pre class="code-pre custom_prefix"><code langs=""><ul class="prefixed"><li class="line" prefix="postgres=#">CREATE DATABASE <span class="highlight">myproject</span>;
</li></ul></code></pre>
<p></p><div class="code-label notes-and-warnings note" title="note">note</div><span class="note">
Remember to end all commands at an SQL prompt with a semicolon.<br /></span>

<p>Next, we will create a database user which we will use to connect to and interact with the database.  Set the password to something strong and secure:</p>
<pre class="code-pre custom_prefix"><code langs=""><ul class="prefixed"><li class="line" prefix="postgres=#">CREATE USER <span class="highlight">myprojectuser</span> WITH PASSWORD '<span class="highlight">password</span>';
</li></ul></code></pre>
<p>Afterwards, we'll modify a few of the connection parameters for the user we just created.  This will speed up database operations so that the correct values do not have to be queried and set each time a connection is established.</p>

<p>We are setting the default encoding to UTF-8, which Django expects.  We are also setting the default transaction isolation scheme to "read committed", which blocks reads from uncommitted transactions.  Lastly, we are setting the timezone.  By default, our Django projects will be set to use <code>UTC</code>.  These are all recommendations from <a href="https://docs.djangoproject.com/en/1.9/ref/databases/#optimizing-postgresql-s-configuration">the Django project itself</a>.</p>
<pre class="code-pre custom_prefix"><code langs=""><ul class="prefixed"><li class="line" prefix="postgres=#">ALTER ROLE <span class="highlight">myprojectuser</span> SET client_encoding TO 'utf8';
</li><li class="line" prefix="postgres=#">ALTER ROLE <span class="highlight">myprojectuser</span> SET default_transaction_isolation TO 'read committed';
</li><li class="line" prefix="postgres=#">ALTER ROLE <span class="highlight">myprojectuser</span> SET timezone TO 'UTC';
</li></ul></code></pre>
<p>Now, all we need to do is give our database user access rights to the database we created:</p>
<pre class="code-pre custom_prefix"><code langs=""><ul class="prefixed"><li class="line" prefix="postgres=#">GRANT ALL PRIVILEGES ON DATABASE <span class="highlight">myproject</span> TO <span class="highlight">myprojectuser</span>;
</li></ul></code></pre>
<p>Exit the SQL prompt to get back to the <code>postgres</code> user's shell session:</p>
<pre class="code-pre custom_prefix"><code langs=""><ul class="prefixed"><li class="line" prefix="postgres=#">\q
</li></ul></code></pre>
<h2 id="install-django-within-a-virtual-environment">Install Django within a Virtual Environment</h2>

<p>Now that our database is set up, we can install Django.  For better flexibility, we will install Django and all of its dependencies within a Python virtual environment.  The <code>virtualenv</code> package allows you to create these environments easily.</p>

<p>If you are using <strong>Python 2</strong>, you can install the correct package by typing:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo pip install virtualenv
</li></ul></code></pre>
<p>If you are using <strong>Python 3</strong>, type:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo pip3 install virtualenv
</li></ul></code></pre>
<p>Make and move into a directory to hold your Django project:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">mkdir ~/<span class="highlight">myproject</span>
</li><li class="line" prefix="$">cd ~/<span class="highlight">myproject</span>
</li></ul></code></pre>
<p>We can create a virtual environment to store our Django project's Python requirements by typing:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">virtualenv <span class="highlight">myprojectenv</span>
</li></ul></code></pre>
<p>This will install a local copy of Python and a local <code>pip</code> command into a directory called <code><span class="highlight">myprojectenv</span></code> within your project directory.</p>

<p>Before we install applications within the virtual environment, we need to activate it. You can do so by typing:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">source <span class="highlight">myprojectenv</span>/bin/activate
</li></ul></code></pre>
<p>Your prompt will change to indicate that you are now operating within the virtual environment. It will look something like this <code>(<span class="highlight">myprojectenv</span>)<span class="highlight">user</span>@<span class="highlight">host</span>:~/<span class="highlight">myproject</span>$</code>.</p>

<p>Once your virtual environment is active, you can install Django with <code>pip</code>.  We will also install the <code>psycopg2</code> package that will allow us to use the database we configured:</p>

<p></p><div class="code-label notes-and-warnings note" title="Note">Note</div><span class="note">
Regardless of which version of Python you are using, when the virtual environment is activated, you should use the <code>pip</code> command (not <code>pip3</code>).<br /></span>
<pre class="code-pre custom_prefix"><code langs=""><ul class="prefixed"><li class="line" prefix="(myprojectenv) $">pip install django psycopg2
</li></ul></code></pre>
<p>We can now start a Django project within our <code>myproject</code> directory.  This will create a child directory of the same name to hold the code itself, and will create a management script within the current directory.  Make sure to add the dot at the end of the command so that this is set up correctly:</p>
<pre class="code-pre custom_prefix"><code langs=""><ul class="prefixed"><li class="line" prefix="(myprojectenv) $">django-admin.py startproject <span class="highlight">myproject</span> .
</li></ul></code></pre>
<h2 id="configure-the-django-database-settings">Configure the Django Database Settings</h2>

<p>Now that we have a project, we need to configure it to use the database we created.</p>

<p>Open the main Django project settings file located within the child project directory:</p>
<pre class="code-pre custom_prefix"><code langs=""><ul class="prefixed"><li class="line" prefix="(myprojectenv) $">nano ~/<span class="highlight">myproject</span>/<span class="highlight">myproject</span>/settings.py
</li></ul></code></pre>
<p>Towards the bottom of the file, you will see a <code>DATABASES</code> section that looks like this:</p>
<div class="code-label " title="~/myproject/myproject/settings.py">~/myproject/myproject/settings.py</div><pre class="code-pre "><code langs="">. . .

DATABASES = {
    'default': {
        'ENGINE': 'django.db.backends.sqlite3',
        'NAME': os.path.join(BASE_DIR, 'db.sqlite3'),
    }
}

. . .
</code></pre>
<p>This is currently configured to use SQLite as a database.  We need to change this so that our PostgreSQL database is used instead.</p>

<p>First, change the engine so that it uses the <code>postgresql_psycopg2</code> adaptor instead of the <code>sqlite3</code> adaptor.  For the <code>NAME</code>, use the name of your database (<code><span class="highlight">myproject</span></code> in our example).  We also need to add login credentials.  We need the username, password, and host to connect to.  We'll add and leave blank the port option so that the default is selected:</p>
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
<p>When you are finished, save and close the file.</p>

<h2 id="migrate-the-database-and-test-your-project">Migrate the Database and Test your Project</h2>

<p>Now that the Django settings are configured, we can migrate our data structures to our database and test out the server.</p>

<p>We can begin by creating and applying migrations to our database.  Since we don't have any actual data yet, this will simply set up the initial database structure:</p>
<pre class="code-pre custom_prefix"><code langs=""><ul class="prefixed"><li class="line" prefix="(myprojectenv) $">cd ~/<span class="highlight">myproject</span>
</li><li class="line" prefix="(myprojectenv) $">python manage.py makemigrations
</li><li class="line" prefix="(myprojectenv) $">python manage.py migrate
</li></ul></code></pre>
<p>After creating the database structure, we can create an administrative account by typing:</p>
<pre class="code-pre custom_prefix"><code langs=""><ul class="prefixed"><li class="line" prefix="(myprojectenv) $">python manage.py createsuperuser
</li></ul></code></pre>
<p>You will be asked to select a username, provide an email address, and choose and confirm a password for the account.</p>

<p>If you followed the initial server setup guide, you should have a UFW firewall in place.  Before we can access the Django development server to test our database, we need open the port we will be using in our firewall.</p>

<p>Allow external connections to the port by typing:</p>
<pre class="code-pre custom_prefix"><code langs=""><ul class="prefixed"><li class="line" prefix="(myprojectenv) $">sudo ufw allow 8000
</li></ul></code></pre>
<p>Once you have the port open, you can test that your database is performing correctly by starting up the Django development server:</p>
<pre class="code-pre custom_prefix"><code langs=""><ul class="prefixed"><li class="line" prefix="(myprojectenv) $">python manage.py runserver 0.0.0.0:8000
</li></ul></code></pre>
<p>In your web browser, visit your server's domain name or IP address followed by <code>:8000</code> to reach default Django root page:</p>
<pre class="code-pre "><code langs="">http://<span class="highlight">server_domain_or_IP</span>:8000
</code></pre>
<p>You should see the default index page:</p>

<p><img src="https://assets.digitalocean.com/articles/django_mysql_1404/django_index.png" alt="Django index" /></p>

<p>Append <code>/admin</code> to the end of the URL and you should be able to access the login screen to the admin interface:</p>

<p><img src="https://assets.digitalocean.com/articles/django_mysql_1404/admin_login.png" alt="Django admin login" /></p>

<p>Enter the username and password you just created using the <code>createsuperuser</code> command.  You will then be taken to the admin interface:</p>

<p><img src="https://assets.digitalocean.com/articles/django_mysql_1404/admin_interface.png" alt="Django admin interface" /></p>

<p>When you're done investigating, you can stop the development server by hitting CTRL-C in your terminal window.</p>

<p>By accessing the admin interface, we have confirmed that our database has stored our user account information and that it can be appropriately accessed.</p>

<h2 id="conclusion">Conclusion</h2>

<p>In this guide, we've demonstrated how to install and configure PostgreSQL as the backend database for a Django project.  While SQLite can easily handle the load during development and light production use, most projects benefit from implementing a more full-featured DBMS.</p>

    