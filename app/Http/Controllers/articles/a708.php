<link rel='stylesheet' href='digital_ocean.css'><br> <br> 
      <h3 id="introduction">Introduction</h3>

<p>Django is a powerful web framework that can help you get your Python application or website off the ground quickly.  Django includes a simplified development server for testing your code locally, but for anything even slightly production related, a more secure and powerful web server is required.</p>

<p>In this guide, we will demonstrate how to install and configure Django in a Python virtual environment.  We'll then set up Apache in front of our application so that it can handle client requests directly before passing requests that require application logic to the Django app.  We will do this using the <code>mod_wsgi</code> Apache module that can communicate with Django over the WSGI interface specification.</p>

<h2 id="prerequisites-and-goals">Prerequisites and Goals</h2>

<p>In order to complete this guide, you should have a fresh Ubuntu 14.04 server instance with a non-root user with <code>sudo</code> privileges configured.  You can learn how to set this up by running thorugh our <a href="https://indiareads/community/tutorials/initial-server-setup-with-ubuntu-14-04">initial server setup guide</a>.</p>

<p>We will be installing Django within a Python virtual environment.  Installing Django into an environment specific to your project will allow your projects and their requirements to be handled separately.</p>

<p>Once we have our application up and running, we will configure Apache to interface with the Django app.  It will do this with the <code>mod_wsgi</code> Apache module, which can translate HTTP requests into a predictable application format defined by a specification called WSGI.  You can find out more about WSGI by reading the linked section on <a href="https://indiareads/community/tutorials/how-to-set-up-uwsgi-and-nginx-to-serve-python-apps-on-ubuntu-14-04#definitions-and-concepts">this guide</a>.</p>

<p>Let's get started.</p>

<h2 id="install-packages-from-the-ubuntu-repositories">Install Packages from the Ubuntu Repositories</h2>

<p>To begin the process, we'll download and install all of the items we need from the Ubuntu repositories.  This will include the Apache web server, the <code>mod_wsgi</code> module used to interface with our Django app, and <code>pip</code>, the Python package manager that can be used to download our Python-related tools.</p>

<p>To get everything we need, update your server's local package index and then install the appropriate packages.</p>

<p>If you are using Django with Python 2, the commands you need are:</p>
<pre class="code-pre "><code langs="">sudo apt-get update
sudo apt-get install python-pip apache2 libapache2-mod-wsgi
</code></pre>
<p>If, instead, you are using Django with Python 3, you will need an alternative Apache module.  The appropriate commands in this case are:</p>
<pre class="code-pre "><code langs="">sudo apt-get update
sudo apt-get install python3-pip apache2 libapache2-mod-wsgi-py3
</code></pre>
<p>When operating <em>outside</em> of a virtual environment for the remainder of the tutorial, if you are using Python 3, replace <code>pip</code> with <code>pip3</code>.</p>

<h2 id="configure-a-python-virtual-environment">Configure a Python Virtual Environment</h2>

<p>Now that we have the components from the Ubuntu repositories, we can start working on our Django project.  The first step is to create a Python virtual environment so that our Django project will be separate from the system's tools and any other Python projects we may be working on.</p>

<p>We need to install the <code>virtualenv</code> command to create these environments.  We can get this using <code>pip</code>:</p>
<pre class="code-pre "><code langs="">sudo pip install virtualenv
</code></pre>
<p>With <code>virtualenv</code> installed, we can start forming our project.  Create a directory where you wish to keep your project and move into the directory:</p>
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

<p>With your virtual environment active, install Django with the local instance of <code>pip</code> by typing:</p>
<pre class="code-pre "><code langs="">pip install django
</code></pre>
<h2 id="create-and-configure-a-new-django-project">Create and Configure a New Django Project</h2>

<p>Now that Django is installed in our virtual environment, we can create the actual Django project files.</p>

<h3 id="create-the-django-project">Create the Django Project</h3>

<p>Since we already have a project directory, we will tell Django to install the files here.  It will create a second level directory with the actual code, which is normal, and place a management script in this directory.  The key to this is the dot at the end that tells Django to create the files in the current directory:</p>
<pre class="code-pre "><code langs="">django-admin.py startproject <span class="highlight">myproject</span> .
</code></pre>
<h3 id="adjust-the-project-settings">Adjust the Project Settings</h3>

<p>The first thing we should do with our newly created project files is adjust the settings.  Open the settings file with your text editor:</p>
<pre class="code-pre "><code langs="">nano <span class="highlight">myproject</span>/settings.py
</code></pre>
<p>We are going to be using the default SQLite database in this guide for simplicity's sake, so we don't actually need to change too much.  We will focus on configuring the static files directory, where Django will place static files so that the web server can serve these easily.</p>

<p>At the bottom of the file, we will add a line to configure this directory.  Django uses the <code>STATIC_ROOT</code> setting to determine the directory where these files should go.  We'll use a bit of Python to tell it to use a directory called "static" in our project's main directory:</p>
<pre class="code-pre "><code langs="">STATIC_ROOT = os.path.join(BASE_DIR, "static/")
</code></pre>
<p>Save and close the file when you are finished.</p>

<h3 id="complete-initial-project-setup">Complete Initial Project Setup</h3>

<p>Now, we can migrate the initial database schema to our SQLite database using the management script:</p>
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
<p>You will have to confirm the operation.  The static files will be placed in a directory called <code>static</code> within your project directory.</p>

<p>Finally, you can test your project by starting up the Django development server with this command:</p>
<pre class="code-pre "><code langs="">./manage.py runserver 0.0.0.0:8000
</code></pre>
<p>In your web browser, visit your server's domain name or IP address followed by <code>:8000</code>:</p>
<pre class="code-pre "><code langs="">http://<span class="highlight">server_domain_or_IP</span>:8000
</code></pre>
<p>You should see the default Django index page:</p>

<p><img src="https://assets.digitalocean.com/articles/django_uwsgi_nginx_1404/sample_site.png" alt="Django default index" /></p>

<p>If you append <code>/admin</code> to the end of the URL in the address bar, you will be prompted for the administrative username and password you created with the <code>createsuperuser</code> command:</p>

<p><img src="https://assets.digitalocean.com/articles/django_uwsgi_nginx_1404/admin_login.png" alt="Django admin login" /></p>

<p>After authenticating, you can access the default Django admin interface:</p>

<p><img src="https://assets.digitalocean.com/articles/django_uwsgi_nginx_1404/admin_interface.png" alt="Django admin interface" /></p>

<p>When you are finished exploring, hit CTRL-C in the terminal window to shut down the development server.</p>

<p>We're now done with Django for the time being, so we can back out of our virtual environment by typing:</p>
<pre class="code-pre "><code langs="">deactivate
</code></pre>
<h2 id="configure-apache">Configure Apache</h2>

<p>Now that your Django project is working, we can configure Apache as a front end.  Client connections that it receives will be translated into the WSGI format that the Django application expects using the <code>mod_wsgi</code> module.  This should have been automatically enabled upon installation earlier.</p>

<p>To configure the WSGI pass, we'll need to edit the default virtual host file:</p>
<pre class="code-pre "><code langs="">sudo nano /etc/apache2/sites-available/000-default.conf
</code></pre>
<p>We can keep the directives that are already present in the file.  We just need to add some additional items.</p>

<p>To start, let's configure the static files.  We will use an alias to tell Apache to map any requests starting with <code>/static</code> to the "static" directory within our project folder.  We collected the static assets there earlier.  We will set up the alias and then grant access to the directory in question with a directory block:</p>
<pre class="code-pre "><code langs=""><VirtualHost *:80>
    . . .

    Alias /static /home/<span class="highlight">user</span>/<span class="highlight">myproject</span>/static
    <Directory /home/<span class="highlight">user</span>/<span class="highlight">myproject</span>/static>
        Require all granted
    </Directory>

</VirtualHost>
</code></pre>
<p>Next, we'll grant access to the <code>wsgi.py</code> file within the second level project directory where the Django code is stored.  To do this, we'll use a directory section with a file section inside.  We will grant access to the file inside of this nested construct:</p>
<pre class="code-pre "><code langs=""><VirtualHost *:80>
    . . .

    Alias /static /home/<span class="highlight">user</span>/<span class="highlight">myproject</span>/static
    <Directory /home/<span class="highlight">user</span>/<span class="highlight">myproject</span>/static>
        Require all granted
    </Directory>

    <Directory /home/<span class="highlight">user</span>/<span class="highlight">myproject</span>/<span class="highlight">myproject</span>>
        <Files wsgi.py>
            Require all granted
        </Files>
    </Directory>

</VirtualHost>
</code></pre>
<p>After this is configured, we are ready to construct the portion of the file that actually handles the WSGI pass.  We'll use daemon mode to run the WSGI process, which is the recommended configuration.  We can use the <code>WSGIDaemonProcess</code> directive to set this up.</p>

<p>This directive takes an arbitrary name for the process.  We'll use <code><span class="highlight">myproject</span></code> to stay consistent.  Afterwards, we set up the Python path where Apache can find all of the components that may be required.  Since we used a virtual environment, we will have to set up two path components.  The first is our project's parent directory, where the project files can be found.  The second is the <code>lib/python<span class="highlight">x</span>.<span class="highlight">x</span>/site-packages</code> path within our virtual environment folder (where the Xs are replaced by the Python version number components).  This way, Apache can find all of the other Python code needed to run our project.</p>

<p>Afterwards, we need to specify the process group.  This should point to the same name we selected for the <code>WSGIDaemonProcess</code> directive (<code>myproject</code> in our case).  Finally, we need to set the script alias so that Apache will pass requests for the root domain to the <code>wsgi.py</code> file:</p>
<pre class="code-pre "><code langs=""><VirtualHost *:80>
    . . .

    Alias /static /home/<span class="highlight">user</span>/<span class="highlight">myproject</span>/static
    <Directory /home/<span class="highlight">user</span>/<span class="highlight">myproject</span>/static>
        Require all granted
    </Directory>

    <Directory /home/<span class="highlight">user</span>/<span class="highlight">myproject</span>/<span class="highlight">myproject</span>>
        <Files wsgi.py>
            Require all granted
        </Files>
    </Directory>

    WSGIDaemonProcess <span class="highlight">myproject</span> python-path=/home/<span class="highlight">user</span>/<span class="highlight">myproject</span>:/home/<span class="highlight">user</span>/<span class="highlight">myproject</span>/<span class="highlight">myprojectenv</span>/lib/python<span class="highlight">2</span>.<span class="highlight">7</span>/site-packages
    WSGIProcessGroup <span class="highlight">myproject</span>
    WSGIScriptAlias / /home/<span class="highlight">user</span>/<span class="highlight">myproject</span>/<span class="highlight">myproject</span>/wsgi.py

</VirtualHost>
</code></pre>
<p>When you are finished making these changes, save and close the file.</p>

<h3 id="wrapping-up-some-permissions-issues">Wrapping Up Some Permissions Issues</h3>

<p>If you are using the SQLite database, which is the default used in this article, you need to allow the Apache process access to this file.</p>

<p>To do so, the first step is to change the permissions so that the group owner of the database can read and write.  The database file is called <code>db.sqlite3</code> by default and it should be located in your base project directory:</p>
<pre class="code-pre "><code langs="">chmod 664 ~/<span class="highlight">myproject</span>/db.sqlite3
</code></pre>
<p>Afterwards, we need to give the group Apache runs under, the <code>www-data</code> group, group ownership of the file:</p>
<pre class="code-pre "><code langs="">sudo chown :www-data ~/<span class="highlight">myproject</span>/db.sqlite3
</code></pre>
<p>In order to write to the file, we also need to give the Apache group ownership over the database's parent directory:</p>
<pre class="code-pre "><code langs="">sudo chown :www-data ~/<span class="highlight">myproject</span>
</code></pre>
<p>Once these steps are done, you are ready to restart the Apache service to implement the changes you made.  Restart Apache by typing:</p>
<pre class="code-pre "><code langs="">sudo service apache2 restart
</code></pre>
<p>You should now be able to access your Django site by going to your server's domain name or IP address without specifying a port.  The regular site and the admin interface should function as expected.</p>

<h2 id="conclusion">Conclusion</h2>

<p>In this guide, we've set up a Django project in its own virtual environment. We've configured Apache with <code>mod_wsgi</code> to handle client requests and interface with the Django app.</p>

<p>Django makes creating projects and applications simple by providing many of the common pieces, allowing you to focus on the unique elements. By leveraging the general tool chain described in this article, you can easily serve the applications you create from a single server.</p>

    