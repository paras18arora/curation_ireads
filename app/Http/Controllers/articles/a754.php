<link rel='stylesheet' href='digital_ocean.css'><br> <br> 
      <h3 id="introduction">Introduction</h3>

<p>Django is a flexible framework for quickly creating Python applications.  By default, Django applications are configured to store data into a lightweight SQLite database file.  While this works well under some loads, a more traditional DBMS can improve performance in production.</p>

<p>In this guide, we'll demonstrate how to install and configure PostgreSQL to use with your Django applications.  We will install the necessary software, create database credentials for our application, and then start and configure a new Django project to use this backend.</p>

<h2 id="prerequisites">Prerequisites</h2>

<p>To get started, you will need a clean CentOS 7 server instance with a non-root user set up.  The non-root user must be configured with <code>sudo</code> privileges.  Learn how to set this up by following our <a href="https://indiareads/community/tutorials/initial-server-setup-with-centos-7">initial server setup guide</a>.</p>

<p>When you are ready to continue, read on.</p>

<h2 id="install-the-components-from-the-centos-and-epel-repositories">Install the Components from the CentOS and EPEL Repositories</h2>

<p>Our first step will be install all of the pieces that we need from the repositories.  We will install <code>pip</code>, the Python package manager, in order to install and manage our Python components.  We will also install the database software and the associated libraries required to interact with them.</p>

<p>Some of the software we need is in the EPEL repository, which contains extra packages.  We can enable this repository easily by tying:</p>
<pre class="code-pre "><code langs="">sudo yum install epel-release
</code></pre>
<p>With EPEL enabled, we can install the necessary components by typing:</p>
<pre class="code-pre "><code langs="">sudo yum install python-pip python-devel gcc postgresql-server postgresql-devel postgresql-contrib
</code></pre>
<h2 id="perform-initial-postgresql-configuration">Perform Initial PostgreSQL Configuration</h2>

<p>After the installation, you need to initialize the PostgreSQL database by typing:</p>
<pre class="code-pre "><code langs="">sudo postgresql-setup initdb
</code></pre>
<p>After the database has been initialized, we can start the PostgreSQL service by typing:</p>
<pre class="code-pre "><code langs="">sudo systemctl start postgresql
</code></pre>
<p>With the database started, we actually need to adjust the values in one of the configuration files that has been populated.  Use your editor and the <code>sudo</code> command to open the file now:</p>
<pre class="code-pre "><code langs="">sudo nano /var/lib/pgsql/data/pg_hba.conf
</code></pre>
<p>This file is responsible for configuring authentication methods for the database system. Currently, it is configured to allow connections only when the system user matches the database user. This is okay for local maintenance tasks, but our Django instance will have another user configured with a password.</p>

<p>We can configure this by modifying the two <code>host</code> lines at the bottom of the file. Change the last column (the authentication method) to <code>md5</code>. This will allow password authentication:</p>
<pre class="code-pre "><code langs="">. . .

# TYPE  DATABASE        USER            ADDRESS                 METHOD

# "local" is for Unix domain socket connections only
local   all             all                                     peer
# IPv4 local connections:
<span class="highlight">#</span>host    all             all             127.0.0.1/32            ident
host    all             all             127.0.0.1/32            <span class="highlight">md5</span>
<span class="highlight">#</span> IPv6 local connections:
#host    all             all             ::1/128                 ident
host    all             all             ::1/128                 <span class="highlight">md5</span>
</code></pre>
<p>When you are finished, save and close the file.</p>

<p>With our new configuration changes, we need to restart the service.  We will also enable PostgreSQL so that it starts automatically at boot:</p>
<pre class="code-pre "><code langs="">sudo systemctl restart postgresql
sudo systemctl enable postgresql
</code></pre>
<h2 id="create-a-database-and-database-user">Create a Database and Database User</h2>

<p>By default, Postgres uses an authentication scheme called "peer authentication" for local connections.  We could see this for the <code>local</code> entry in the <code>pg_hba.conf</code> file we edited. Basically, this means that if the user's operating system username matches a valid Postgres username, that user can login with no further authentication.</p>

<p>During the Postgres installation, an operating system user named <code>postgres</code> was created to correspond to the <code>postgres</code> PostgreSQL administrative user. We need to change to this user to perform administrative tasks:</p>
<pre class="code-pre "><code langs="">sudo su - postgres
</code></pre>
<p>You should now be in a shell session for the <code>postgres</code> user. Log into a Postgres session by typing:</p>
<pre class="code-pre "><code langs="">psql
</code></pre>
<p>First, we will create a database for our Django project. Each project should have its own isolated database for security reasons. We will call our database <code><span class="highlight">myproject</span></code> in this guide, but it's always better to select something more descriptive:</p>
<pre class="code-pre "><code langs="">CREATE DATABASE <span class="highlight">myproject</span>;
</code></pre>
<p>Remember to end all commands at an SQL prompt with a semicolon.</p>

<p>Next, we will create a database user which we will use to connect to and interact with the database. Set the password to something strong and secure:</p>
<pre class="code-pre "><code langs="">CREATE USER <span class="highlight">myprojectuser</span> WITH PASSWORD '<span class="highlight">password</span>';
</code></pre>
<p>Afterwards, we'll modify a few of the connection parameters for the user we just created. This will speed up database operations so that the correct values do not have to be queried and set each time a connection is established.</p>

<p>We are setting the default encoding to UTF-8, which Django expects. We are also setting the default transaction isolation scheme to "read committed", which blocks reads from uncommitted transactions. Lastly, we are setting the timezone. By default, our Django projects will be set to use <code>UTC</code>:</p>
<pre class="code-pre "><code langs="">ALTER ROLE <span class="highlight">myprojectuser</span> SET client_encoding TO 'utf8';
ALTER ROLE <span class="highlight">myprojectuser</span> SET default_transaction_isolation TO 'read committed';
ALTER ROLE <span class="highlight">myprojectuser</span> SET timezone TO 'UTC';
</code></pre>
<p>Now, all we need to do is give our database user access rights to the database we created:</p>
<pre class="code-pre "><code langs="">GRANT ALL PRIVILEGES ON DATABASE <span class="highlight">myproject</span> TO <span class="highlight">myprojectuser</span>;
</code></pre>
<p>Exit the SQL prompt to get back to the postgres user's shell session:</p>
<pre class="code-pre "><code langs="">\q
</code></pre>
<p>Exit out of the <code>postgres</code> user's shell session to get back to your regular user's shell session:</p>
<pre class="code-pre "><code langs="">exit
</code></pre>
<h2 id="install-django-within-a-virtual-environment">Install Django within a Virtual Environment</h2>

<p>Now that our database is set up, we can install Django.  For better flexibility, we will install Django and all of its dependencies within a Python virtual environment.</p>

<p>You can get the <code>virtualenv</code> package that allows you to create these environments by typing:</p>
<pre class="code-pre "><code langs="">sudo pip install virtualenv
</code></pre>
<p>Make a directory to hold your Django project.  Move into the directory afterwards:</p>
<pre class="code-pre "><code langs="">mkdir ~/<span class="highlight">myproject</span>
cd ~/<span class="highlight">myproject</span>
</code></pre>
<p>We can create a virtual environment to store our Django project's Python requirements by typing:</p>
<pre class="code-pre "><code langs="">virtualenv <span class="highlight">myprojectenv</span>
</code></pre>
<p>This will install a local copy of Python and <code>pip</code> into a directory called <code><span class="highlight">myprojectenv</span></code> within your project directory.</p>

<p>Before we install applications within the virtual environment, we need to activate it. You can do so by typing:</p>
<pre class="code-pre "><code langs="">source <span class="highlight">myprojectenv</span>/bin/activate
</code></pre>
<p>Your prompt will change to indicate that you are now operating within the virtual environment. It will look something like this <code>(<span class="highlight">myprojectenv</span>)<span class="highlight">user</span>@<span class="highlight">host</span>:~/<span class="highlight">myproject</span>$</code>.</p>

<p>Once your virtual environment is active, you can install Django with <code>pip</code>.  We will also install the <code>psycopg2</code> package that will allow us to use the database we configured:</p>
<pre class="code-pre "><code langs="">pip install django psycopg2
</code></pre>
<p>We can now start a Django project within our <code>myproject</code> directory.  This will create a child directory of the same name to hold the code itself, and will create a management script within the current directory.  Make sure to add the dot at the end of the command so that this is set up correctly:</p>
<pre class="code-pre "><code langs="">django-admin.py startproject <span class="highlight">myproject</span> .
</code></pre>
<h2 id="configure-the-django-database-settings">Configure the Django Database Settings</h2>

<p>Now that we have a project, we need to configure it to use the database we created.</p>

<p>Open the main Django project settings file located within the child project directory:</p>
<pre class="code-pre "><code langs="">nano ~/<span class="highlight">myproject</span>/<span class="highlight">myproject</span>/settings.py
</code></pre>
<p>Towards the bottom of the file, you will see a <code>DATABASES</code> section that looks like this:</p>
<pre class="code-pre "><code langs="">. . .

DATABASES = {
    'default': {
        'ENGINE': 'django.db.backends.sqlite3',
        'NAME': os.path.join(BASE_DIR, 'db.sqlite3'),
    }
}

. . .
</code></pre>
<p>This is currently configured to use SQLite as a database.  We need to change this so that our PostgreSQL database is used instead.</p>

<p>First, change the engine so that it points to the <code>postgresql_psycopg2</code> backend instead of the <code>sqlite3</code> backend.  For the <code>NAME</code>, use the name of your database (<code><span class="highlight">myproject</span></code> in our example).  We also need to add login credentials.  We need the username, password, and host to connect to.  We'll add and leave blank the port option so that the default is selected:</p>
<pre class="code-pre "><code langs="">. . .

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
<pre class="code-pre "><code langs="">cd ~/<span class="highlight">myproject</span>
python manage.py makemigrations
python manage.py migrate
</code></pre>
<p>After creating the database structure, we can create an administrative account by typing:</p>
<pre class="code-pre "><code langs="">python manage.py createsuperuser
</code></pre>
<p>You will be asked to select a username, provide an email address, and choose and confirm a password for the account.</p>

<p>Once you have an admin account set up, you can test that your database is performing correctly by starting up the Django development server:</p>
<pre class="code-pre "><code langs="">python manage.py runserver 0.0.0.0:8000
</code></pre>
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

    