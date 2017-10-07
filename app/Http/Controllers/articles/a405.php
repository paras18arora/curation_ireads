<link rel='stylesheet' href='digital_ocean.css'><br> <br> 
      <p>Django is a high-level Python framework for developing web applications rapidly. IndiaReads's Django One-Click app quickly deploys a preconfigured development environment to your VPS employing Django, Nginx, Gunicorn, and Postgres.</p>

<h2 id="creating-the-django-droplet">Creating the Django Droplet</h2>

<p>To use the image, select <strong>Django on Ubuntu 14.04</strong> from the Applications menu during droplet creation:</p>

<p><img src="https://assets.digitalocean.com/articles/django_one_click/QOa9uI4.png" alt="One-Click Apps" /></p>

<p>Once you create the droplet, navigate to your droplet's IP address (http://your.ip.address) in a browser, and verify that Django is running:</p>

<p><img src="https://assets.digitalocean.com/articles/django_one_click/3wccDEC.png" alt="It worked!" /></p>

<p>You can now login to your droplet as root and read the Message of the Day, which contains important information about your installation:</p>

<p><img src="https://assets.digitalocean.com/articles/django_one_click/AZCzNNV.png" alt="MOTD" /></p>

<p>This information includes the username and password for both the Django user and the Postgres database. If you need to refer back to this latter, the information can be found in the file <code>/etc/motd.tail</code></p>

<h2 id="configuration-details">Configuration Details</h2>

<p>The Django project is served by Gunicorn which listens on port 9000 and is proxied by Nginx which listens on port 80.</p>

<h3 id="nginx">Nginx</h3>

<p>The Nginx configuration is located at <code>/etc/nginx/sites-enabled/django</code>:</p>
<pre class="code-pre "><code langs="">upstream app_server {
    server 127.0.0.1:9000 fail_timeout=0;
}

server {
    listen 80 default_server;
    listen [::]:80 default_server ipv6only=on;

    root /usr/share/nginx/html;
    index index.html index.htm;

    client_max_body_size 4G;
    server_name _;

    keepalive_timeout 5;

    # Your Django project's media files - amend as required
    location /media  {
        alias /home/django/django_project/django_project/media;
    }

    # your Django project's static files - amend as required
    location /static {
        alias /home/django/django_project/django_project/static; 
    }

    location / {
        proxy_set_header X-Forwarded-For $proxy_add_x_forwarded_for;
        proxy_set_header Host $http_host;
        proxy_redirect off;
        proxy_pass http://app_server;
    }
}
</code></pre>
<p>If you rename the project folder, remember to change the path to your static files.</p>

<h3 id="gunicorn">Gunicorn</h3>

<p>Gunicorn is started on boot by an Upstart script located at <code>/etc/init/gunicorn.conf</code> which looks like:</p>
<pre class="code-pre "><code langs="">description "Gunicorn daemon for Django project"

start on (local-filesystems and net-device-up IFACE=eth0)
stop on runlevel [!12345]

# If the process quits unexpectedly trigger a respawn
respawn

setuid django
setgid django
chdir /home/django

exec gunicorn \
    --name=django_project \
    --pythonpath=django_project \
    --bind=0.0.0.0:9000 \
    --config /etc/gunicorn.d/gunicorn.py \
    django_project.wsgi:application
</code></pre>
<p>Again, if you rename the project folder, remember to update the <code>name</code> and <code>pythonpath</code> in this file as well.</p>

<p>The Upstart script also sources a configuration file located in <code>/etc/gunicorn.d/gunicorn.py</code> that sets the number of worker processes:</p>
<pre class="code-pre "><code langs="">"""gunicorn WSGI server configuration."""
from multiprocessing import cpu_count
from os import environ


def max_workers():
    return cpu_count() * 2 + 1

max_requests = 1000
worker_class = 'gevent'
workers = max_workers()
</code></pre>
<p>More information on configuring Gunicorn can be found in <a href="http://gunicorn-docs.readthedocs.org/en/develop/configure.html#configuration-file">the project's documentation.</a></p>

<h3 id="django">Django</h3>

<p>The Django project itself is located at <code>/home/django/django_project</code> It can be started, restarted, or stopped using the Gunicorn service. For instance, to restart the project after having made changes run:</p>
<pre class="code-pre "><code langs="">service gunicorn restart
</code></pre>
<p>While developing, it can be annoying to restart the server every time you make a change. So you might want to use Django's built in development server which automatically detects changes:</p>
<pre class="code-pre "><code langs="">service gunicorn stop
python manage.py runserver localhost:9000
</code></pre>
<p>While convenient, the built in server does not offer the best performance. So use the Gunicorn service for production.</p>

<h2 id="writing-your-first-django-app">Writing Your First Django App</h2>

<p>There are many resources that can provide you with an in-depth introduction to writing Django applications, but for now let's just quickly demonstrate how to get started. Log into your server and switch to the django user. Now let's create a new app in the project:</p>
<pre class="code-pre "><code langs="">cd /home/django/django_project
python manage.py startapp hello
</code></pre>
<p>Your directory structure should now look like:</p>
<pre class="code-pre "><code langs="">.
├── django_project
│   ├── __init__.py
│   ├── settings.py
│   ├── urls.py
│   └── wsgi.py
├── hello
│   ├── admin.py
│   ├── __init__.py
│   ├── models.py
│   ├── tests.py
│   └── views.py
└── manage.py
</code></pre>
<p>Next, we'll create our first view. Edit the file <code>hello/views.py</code> to look like:</p>
<pre class="code-pre "><code langs="">from django.shortcuts import render
from django.http import HttpResponse

def index(request):
    return HttpResponse("Hello, world! This is our first view.")
</code></pre>
<p>Then, we can connect that view to a URL by editing <code>django_project/urls.py</code></p>
<pre class="code-pre "><code langs="">from django.conf.urls import patterns, include, url
from hello import views
from django.contrib import admin
admin.autodiscover()

urlpatterns = patterns('',
    url(r'^$', views.index, name='index'),
    url(r'^admin/', include(admin.site.urls)),
)
</code></pre>
<p>After that, we can restart the project as root: <code>service gunicorn restart</code></p>

<p>If you reload the page, you'll now see:</p>

<p><img src="https://assets.digitalocean.com/articles/django_one_click/O75cYE1.png" alt="Hello, world!" /></p>

<h2 id="next-steps">Next Steps</h2>

<ul>
<li>Follow our <a href="https://indiareads/community/articles/initial-server-setup-with-ubuntu-14-04">Initial Server Setup</a> guide to give <code>sudo</code> privileges to your user, lock down root login, and take other steps to make your VPS ready for production.</li>
<li><a href="https://indiareads/community/articles/how-to-use-fabric-to-automate-administration-tasks-and-deployments">Use Fabric</a> to automate deployment and other administration tasks.</li>
<li>Check out the official <a href="https://docs.djangoproject.com/en/1.6/intro/overview/">Django project documentation</a>.</li>
</ul>

<div class="author">By Andrew Starr-Bochicchio</div>

    