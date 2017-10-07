<link rel='stylesheet' href='digital_ocean.css'><br> <br> 
      <h3 id="introduction">Introduction</h3>

<p>This article shows you how to deploy a web application using Django, the popular Python framework. The beauty of developing with popular web frameworks is that a lot of repetitious work has been done for you, so you can focus on building your site.</p>

<p>Whether you're a developer or not, it's great to know that the core of what you're running on your Droplet has undergone the scrutiny of a large open-source community and should be less susceptible to large security holes.</p>

<p>One thing that is not inherently simple is knowing how to get these web frameworks up and running outside of your own development or testing environment. In this article we'll show you how to do just that, using a standard Apache, mod_wsgi, and MySQL stack running on top of FreeBSD 10.1.</p>

<h2 id="goals">Goals</h2>

<ul>
<li>Install and configure a Python virtual environment  for your Django site</li>
<li>Create and configure a  sample Django site for testing</li>
<li>Configure a simple and secure MySQL server</li>
<li>Configure a simple Apache virtual host that will serve your Django site</li>
<li>Test that the newly minted site works properly</li>
</ul>

<h2 id="prerequisites">Prerequisites</h2>

<p>Before you begin this guide you'll need the following:</p>

<ul>
<li>A FreeBSD 10.1 Droplet</li>
<li>Access to your root account or an account with sudo privileges following this <a href="https://indiareads/community/tutorials/how-to-add-and-remove-users-on-freebsd">tutorial</a></li>
<li>A working knowledge of how to edit text files from the command line</li>
<li>The <strong>Bash</strong> shell environment, since we'll be using Virtualenv later on this tutorial. Follow the instructions in the <a href="https://indiareads/community/tutorials/how-to-get-started-with-freebsd-10-1#changing-the-default-shell-(optional)">Changing the Default Shell</a> section of the <strong>How To Get Started with FreeBSD 10.1</strong> tutorial. You may need to log out and log in again to get the Bash shell for your <strong>freebsd</strong> user</li>
</ul>

<h2 id="step-1-—-install-and-configure-a-python-virtual-environment">Step 1 — Install and Configure a Python Virtual Environment</h2>

<p>First things first; ensure your current packages are up to date.</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo pkg update && sudo pkg upgrade -y
</li></ul></code></pre>
<p>Now install all the packages you need by running the following command and saying yes to the resulting prompt. You'll notice that far more than what we typed is installed, as the <code>pkg</code> system calculates and selects all the right dependencies.</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo pkg install bash ap24-mod_wsgi3 py27-virtualenv mysql56-server
</li></ul></code></pre>
<p>The <code>virtualenv</code> command you'll be using soon doesn't play nicely with the default user <code>tcsh</code> shell in FreeBSD. We need <code>bash</code> instead. If you're coming from a Linux environment you'll feel right at home. If you didn't do this already in the prerequisites, please follow the instructions <a href="https://indiareads/community/tutorials/how-to-get-started-with-freebsd-10-1#changing-the-default-shell-(optional)">here</a> now.</p>

<p><strong>Are you running the Bash shell now?</strong> Remember to log out and log in again. Great!</p>

<p>Now let's get started on our Python environment.</p>

<p>To make things easy to find, create a directory for your site by issuing this command.  </p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo mkdir -p /www/data/<span class="highlight">www.example.com</span>
</li></ul></code></pre>
<p>Give your user account access to work in your new project directory. The <code>whoami</code> portion of the command automatically fills in your current username.</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo chown -R `whoami` /www/data/<span class="highlight">www.example.com</span>
</li></ul></code></pre>
<p>Change to your newly created directory.</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">cd /www/data/<span class="highlight">www.example.com</span>
</li></ul></code></pre>
<p>Using per site or per application virtual environments allows customizing which Python packages and versions you install, rather than having things installed on a system-wide level.</p>

<p>Now create the python virtual environment using the <code>virtualenv</code> utility.</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">virtualenv venv
</li></ul></code></pre>
<p>Activate that environment to make sure you're installing requirements for your Django site in that environment rather than at a system-wide level.</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">source venv/bin/activate
</li></ul></code></pre>
<p>You'll know that you're in the right environment when you see your command prompt prefaced with <code>(venv)</code>.</p>

<p>If everything looks in order, make sure Python's tools are up to date to complete this step.</p>
<pre class="code-pre custom_prefix"><code langs=""><ul class="prefixed"><li class="line" prefix="(venv)[]$">pip install -U setuptools pip
</li></ul></code></pre>
<h2 id="step-2-—-create-and-configure-a-sample-django-site">Step 2 — Create and Configure a Sample Django Site</h2>

<p>Now you can create the beginnings of a Django site. You'll begin by installing the python requirements that are needed to run the site. Make sure you're still in the <code>/www/data/<span class="highlight">www.example.com</span></code> directory and in your virtual environment.</p>
<pre class="code-pre custom_prefix"><code langs=""><ul class="prefixed"><li class="line" prefix="(venv)[]$">pip install django mysql-python
</li></ul></code></pre>
<p>With Django and MySQL support for Python installed, it's time to create the project layout using Django's <code>django-admin</code> utility.</p>
<pre class="code-pre custom_prefix"><code langs=""><ul class="prefixed"><li class="line" prefix="(venv)[]$">django-admin.py startproject mysite .
</li></ul></code></pre>
<p>Using your favorite editor, open the <code>mysite/settings.py</code> file.</p>
<pre class="code-pre custom_prefix"><code langs=""><ul class="prefixed"><li class="line" prefix="(venv)[]$">vi /www/data/www.example.com/mysite/settings.py
</li></ul></code></pre>
<p>Change the <code>DATABASES</code> section to look like this. Make sure you replace <code><span class="highlight">password</span></code> with something more secure.</p>
<div class="code-label " title="/www/data/www.example.com/mysite/settings.py">/www/data/www.example.com/mysite/settings.py</div><pre class="code-pre "><code langs="">DATABASES = {
    'default': {
        'ENGINE': 'django.db.backends.mysql',
        'NAME': 'mysite',
        'USER': 'mysite',
        'PASSWORD': '<span class="highlight">password</span>',
        'HOST': '/tmp/mysql.sock',
    }
</code></pre>
<p>Save your changes.</p>

<h2 id="step-3-—-configure-a-simple-and-secure-mysql-server">Step 3 — Configure a Simple and Secure MySQL Server</h2>

<p>You've already installed MySQL, so now it just needs to be configured.</p>

<p>First, a bit of housekeeping. Open the <code>/etc/rc.conf</code> file for editing.</p>
<pre class="code-pre custom_prefix"><code langs=""><ul class="prefixed"><li class="line" prefix="(venv)[]$">sudo vi /etc/rc.conf
</li></ul></code></pre>
<p>Add these two lines to the bottom of the file. This makes sure MySQL and Apache will start when the server is started.</p>
<div class="code-label " title="/etc/rc.conf">/etc/rc.conf</div><pre class="code-pre "><code langs="">mysql_enable="YES"
apache24_enable="YES"
</code></pre>
<p>We haven't configured Apache just yet, but it's easier to add both lines now so you don't forget later.</p>

<p>Start up the MySQL server instance.</p>
<pre class="code-pre custom_prefix"><code langs=""><ul class="prefixed"><li class="line" prefix="(venv)[]$">sudo service mysql-server start
</li></ul></code></pre>
<p>Run this command to secure your database server.</p>
<pre class="code-pre custom_prefix"><code langs=""><ul class="prefixed"><li class="line" prefix="(venv)[]$">mysql_secure_installation
</li></ul></code></pre>
<p>Read all the prompts (for your own knowledge), and answer <code>Y</code> to all except the password selection. For the password prompts, please set the <strong>root</strong> MySQL password to something secure.</p>

<p>Log in to your new and reasonably secure MySQL instance, using the password you just set.</p>
<pre class="code-pre custom_prefix"><code langs=""><ul class="prefixed"><li class="line" prefix="(venv)[]$">mysql -u root -p
</li></ul></code></pre>
<p>Execute this command to create the sample site database, called <strong>mysite</strong>.</p>
<pre class="code-pre custom_prefix"><code langs=""><ul class="prefixed"><li class="line" prefix="mysql>">create database mysite character set utf8;
</li></ul></code></pre>
<p>Now use this command to create the <strong>mysite</strong> database user and grant it permissions to the database you just created. Make sure <code><span class="highlight">password</span></code> matches what you set in the <code>settings.py</code> file earlier. In fact, you can change the database name and username too if you want; just make sure the database and user you create match the settings in the Python configuration file.</p>
<pre class="code-pre custom_prefix"><code langs=""><ul class="prefixed"><li class="line" prefix="mysql>">grant all privileges on mysite.* to 'mysite'@'localhost' identified by '<span class="highlight">password</span>';
</li></ul></code></pre>
<p>If you don't see any errors, you can quit the MySQL shell like this:</p>
<pre class="code-pre custom_prefix"><code langs=""><ul class="prefixed"><li class="line" prefix="mysql>">quit
</li></ul></code></pre>
<p>Now that  the database is ready, you'll use the Django <code>manage.py</code> utility to populate it with your sample site's initial data. Again, this has to be done from the site's directory, <code>/www/data/<span class="highlight">www.example.com</span></code>.</p>
<pre class="code-pre custom_prefix"><code langs=""><ul class="prefixed"><li class="line" prefix="(venv)[]$">./manage.py migrate
</li></ul></code></pre>
<h2 id="optional-step-4-—-test-the-new-django-application">(Optional) Step 4 — Test the New Django Application</h2>

<p>Before we go further let's make sure that the sample Django site is in working order.</p>

<p>Start the Django development server, allowing it to listen on your Droplet's public network interface.</p>
<pre class="code-pre custom_prefix"><code langs=""><ul class="prefixed"><li class="line" prefix="(venv)[]$">./manage.py runserver 0.0.0.0:8000
</li></ul></code></pre>
<p>In your IndiaReads Droplet panel, find the external IP address of your Droplet. Now, in your browser visit <code>http://<your ip here>:8000</code>.  You should see the default page for a new Django installation.</p>

<p>It's <strong>very important</strong> that you press <code>CTRL+C</code> on your keyboard to quit the Django development server, as it is not to be used in production.</p>

<h3 id="a-note-on-security">A Note on Security</h3>

<p>There's a lot to read about security and best practices when running any site. To run something real in production, it's highly recommended that you do additional reading on how to properly secure a public facing web server and database server.</p>

<p>With that public service announcement out of the way, it's time to configure Apache to serve our Django site.</p>

<h2 id="step-5-—-configure-a-simple-apache-virtual-host-for-your-django-site">Step 5 — Configure a Simple Apache Virtual Host For Your Django Site</h2>

<p>Using your favorite editor, create and edit the <code>/usr/local/etc/apache24/Includes/httpd.conf</code> file:</p>
<pre class="code-pre custom_prefix"><code langs=""><ul class="prefixed"><li class="line" prefix="(venv)[]$">sudo vi /usr/local/etc/apache24/Includes/httpd.conf
</li></ul></code></pre>
<p>Add all of the following configuration settings to create your virtual host. Please replace <code><span class="highlight">www.example.com</span></code> with your website name to match the directories we created earlier.</p>
<div class="code-label " title="/usr/local/etc/apache24/Includes/httpd.conf">/usr/local/etc/apache24/Includes/httpd.conf</div><pre class="code-pre "><code langs=""># Settings
ServerName mysite

## Default Overrides
ServerSignature Off
ServerTokens Prod
Timeout 30

## Virtual Hosts
<VirtualHost *:80>

    WSGIDaemonProcess mysite python-path=/www/data/<span class="highlight">www.example.com</span>:/www/data/<span class="highlight">www.example.com</span>/venv/lib/python2.7/site-packages/
    WSGIProcessGroup mysite
    WSGIScriptAlias / /www/data/<span class="highlight">www.example.com</span>/mysite/wsgi.py

    <Directory /www/data/<span class="highlight">www.example.com</span>/mysite>
        <Files wsgi.py>
        Require all granted
        </Files>
    </Directory>

</VirtualHost>
</code></pre>
<p>Here you're simply telling Apache to set a couple of sane default configuration options and where to find the Python code to launch the Django site using Apache's <code>mod_wsgi</code> module. <a href="https://indiareads/community/tutorials/how-to-set-up-apache-virtual-hosts-on-ubuntu-14-04-lts">This article</a> has a greater level of detail about Apache virtual hosts, if you want to read more.</p>

<p>Start up the Apache web server.</p>
<pre class="code-pre custom_prefix"><code langs=""><ul class="prefixed"><li class="line" prefix="(venv)[]$">sudo service apache24 restart
</li></ul></code></pre>
<h3 id="testing-the-sample-django-site">Testing the Sample Django Site</h3>

<p>You should now be able to visit <code>http://<span class="highlight">your_server_ip</span></code> from your browser and see the default Django page. If you've set up DNS, you can also use your domain.</p>

<h2 id="conclusion">Conclusion</h2>

<p>As you can see, there is a lot to learn around the topic of deploying even the simplest websites and applications. The next step is to deploy your custom application, instead of the demo application we used in the tutorial.</p>

<p>If you have any questions or comments, please leave them below.</p>

<h3 id="recommended-reading">Recommended Reading</h3>

<p>The following links can help you learn more about building and deploying a simple Django site.</p>

<ul>
<li><a href="https://docs.djangoproject.com/en/1.8/intro/tutorial01/">Your First Django Application</a></li>
<li><a href="https://docs.djangoproject.com/en/1.8/ref/settings/#std:setting-DATABASES">Databases</a></li>
<li><a href="https://docs.djangoproject.com/en/1.8/howto/deployment/checklist/">Deployment Checklist</a></li>
<li><a href="https://docs.djangoproject.com/en/1.8/howto/deployment/wsgi/modwsgi/">Apache and mod_wsgi</a></li>
</ul>

    