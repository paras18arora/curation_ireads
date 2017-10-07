<link rel='stylesheet' href='digital_ocean.css'><br> <br> 
      <h3 id="introduction">Introduction</h3>

<p>Django is a flexible framework for quickly creating Python applications.  By default, Django applications are configured to store data into a lightweight SQLite database file.  While this works well under some loads, a more traditional DBMS can improve performance in production.</p>

<p>In this guide, we'll demonstrate how to install and configure MariaDB to use with your Django applications.  We will install the necessary software, create database credentials for our application, and then start and configure a new Django project to use this backend.</p>

<h2 id="prerequisites">Prerequisites</h2>

<p>To get started, you will need a clean CentOS 7 server instance with a non-root user set up.  The non-root user must be configured with <code>sudo</code> privileges.  Learn how to set this up by following our <a href="https://indiareads/community/tutorials/initial-server-setup-with-centos-7">initial server setup guide</a>.</p>

<p>When you are ready to continue, read on.</p>

<h2 id="install-the-components-from-the-centos-and-epel-repositories">Install the Components from the CentOS and EPEL Repositories</h2>

<p>Our first step will be install all of the pieces that we need from the repositories.  We will install <code>pip</code>, the Python package manager, in order to install and manage our Python components.  We will also install the database software and the associated libraries required to interact with them.</p>

<p>Some of the software we need is in the EPEL repository, which contains extra packages.  We can enable this repository easily by tying:</p>
<pre class="code-pre "><code langs="">sudo yum install epel-release
</code></pre>
<p>With EPEL enabled, we can install the necessary components by typing:</p>
<pre class="code-pre "><code langs="">sudo yum install python-pip python-devel gcc mariadb-server mariadb-devel
</code></pre>
<p>After the installation, you can start and enable the MariaDB service by typing:</p>
<pre class="code-pre "><code langs="">sudo systemctl start mariadb
sudo systemctl enable mariadb
</code></pre>
<p>You can then run through a simple security script by running:</p>
<pre class="code-pre "><code langs="">sudo mysql_secure_installation
</code></pre>
<p>You'll be asked for an administrative password, which will be blank by default.  Just hit ENTER to continue.  Afterwards, you will be asked to change the root password, which you should do.  You'll then be asked a series of questions which you should hit ENTER through to accept the default options.</p>

<p>With the installation and initial database configuration out of the way, we can move on to create our database and database user.</p>

<h2 id="create-a-database-and-database-user">Create a Database and Database User</h2>

<p>We can start by logging into an interactive session with our database software by typing the following:</p>
<pre class="code-pre "><code langs="">mysql -u root -p
</code></pre>
<p>You will be prompted for the administrative password you selected during the last step.  Afterwards, you will be given a prompt.</p>

<p>First, we will create a database for our Django project.  Each project should have its own isolated database for security reasons.  We will call our database <code><span class="highlight">myproject</span></code> in this guide, but it's always better to select something more descriptive.  We'll set the default type for the database to UTF-8, which is what Django expects:</p>
<pre class="code-pre "><code langs="">CREATE DATABASE <span class="highlight">myproject</span> CHARACTER SET UTF8;
</code></pre>
<p>Remember to end all commands at an SQL prompt with a semicolon.</p>

<p>Next, we will create a database user which we will use to connect to and interact with the database.  Set the password to something strong and secure:</p>
<pre class="code-pre "><code langs="">CREATE USER <span class="highlight">myprojectuser</span>@localhost IDENTIFIED BY '<span class="highlight">password</span>';
</code></pre>
<p>Now, all we need to do is give our database user access rights to the database we created:</p>
<pre class="code-pre "><code langs="">GRANT ALL PRIVILEGES ON <span class="highlight">myproject</span>.* TO <span class="highlight">myprojectuser</span>@localhost;
</code></pre>
<p>Flush the changes so that they will be available during the current session:</p>
<pre class="code-pre "><code langs="">FLUSH PRIVILEGES;
</code></pre>
<p>Exit the SQL prompt to get back to your regular shell session:</p>
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

<p>Once your virtual environment is active, you can install Django with <code>pip</code>.  We will also install the <code>mysqlclient</code> package that will allow us to use the database we configured:</p>
<pre class="code-pre "><code langs="">pip install django mysqlclient
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
<p>This is currently configured to use SQLite as a database.  We need to change this so that our MariaDB database is used instead.</p>

<p>First, change the engine so that it points to the <code>mysql</code> backend instead of the <code>sqlite3</code> backend.  For the <code>NAME</code>, use the name of your database (<code><span class="highlight">myproject</span></code> in our example).  We also need to add login credentials.  We need the username, password, and host to connect to.  We'll add and leave blank the port option so that the default is selected:</p>
<pre class="code-pre "><code langs="">. . .

DATABASES = {
    'default': {
        'ENGINE': 'django.db.backends.<span class="highlight">mysql</span>',
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

<p>In this guide, we've demonstrated how to install and configure MariaDB as the backend database for a Django project.  While SQLite can easily handle the load during development and light production use, most projects benefit from implementing a more full-featured DBMS.</p>

    