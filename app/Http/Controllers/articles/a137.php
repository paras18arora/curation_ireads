<link rel='stylesheet' href='digital_ocean.css'><br> <br> 
      <h3 id="introduction">Introduction</h3>

<hr />

<p><strong>Django CMS</strong> is one of the content management systems that is geared towards working with Django framework based web applications. It is perhaps the most popular one out of almost three dozen other options available today. Being a mature, production ready system trusted by some important brands from around the world and having a professional company backing its development surely makes it an attractive choice for projects.</p>

<p>In this IndiaReads article, we will walk you through installing <strong>Django CMS</strong> on a Debian 7/Ubuntu 13 VPS, ready to be configured and used. If you are interested in using Django [CMS] but do not have much experience with the framework or the Python language itself, do not worry: Django CMS comes with a relatively straight forward and quite rich documentation for development which will be more than enough to get you going after completing this tutorial. You can access the latest version by clicking <a href="http://docs.django-cms.org/en/2.4.2/getting_started/tutorial.html">here</a>.</p>

<h2 id="installing-django-cms">Installing Django CMS</h2>

<hr />

<h3 id="python-package-manager-pip">Python Package Manager: <strong>pip</strong></h3>

<hr />

<p>pip is a package manager which will help us to install the software packages (tools, libraries, applications et alia) that we need in a very easy fashion.</p>

<h3 id="python-package-distribution-utility-setuptools">Python Package Distribution Utility: <strong>setuptools</strong></h3>

<hr />

<p>A dependency of pip, setuptools library builds on the functionality of Python's standard software distribution utilities toolset <em>distutils</em>.</p>

<h3 id="virtual-python-environment-builder-virtualenv">Virtual Python Environment Builder: <strong>virtualenv</strong></h3>

<hr />

<p>Many things are extremely simple to achieve in Python and installing packages and applications is no exception. However, a significant amount of these packages come depending on others as well. Upon installation, together with the rest, they become available system-wide: any Python application can link to those libraries and use them.</p>

<p>In certain circumstances, this might cause serious headache with an already configured and stable application ceasing to work due to some dependency issue. As anything you install or remove affects the entire system altogether, the wrong version of a library or a module can break everything. At another time, you might start developing a new project and find yourself in need of a clean working environment.</p>

<p>This is what the virtualenv tool is there for and exactly why we will make use of it: to isolate our Django CMS application repository and its complex dependencies from the rest of the system, allowing us to have everything in order, easing maintenance at the same time.</p>

<h2 id="our-5-step-installation-process">Our 5-Step Installation Process</h2>

<hr />

<h3 id="1-prepare-the-operating-system">1 - Prepare the Operating System</h3>

<hr />

<p><strong>Please Note:</strong> We will be using a freshly created VPS for this article. This prevents any possible software related or security issues from the past installations.</p>

<p>First, we need to bring our operating system up to date. Let's start with updating the software repository list followed by upgrading the installed tools on our machine to more recent versions:</p>
<pre class="code-pre "><code langs="">aptitude update
aptitude upgrade
</code></pre>
<p>We can now continue with installing other software tools and libraries that we will need along the way.</p>

<p>Here is what we will need:</p>

<ul>
<li><p><strong>python-dev</strong>: This package extends the default Python installation on our system.</p></li>
<li><p><strong>libjpeg-dev/libpng-dev</strong>: These libraries will be  needed for image processing with Python imaging library.</p></li>
<li><p><strong>libpq-dev</strong>: The libpq's (PostgreSQL) development version, which we will need further later on in the tutorial.</p></li>
</ul>

<p>To download and install, run the following command:</p>
<pre class="code-pre "><code langs="">aptitude install libpq-dev python-dev libjpeg-dev libpng-dev
</code></pre>
<h2 id="2-install-the-virtual-environment">2 - Install the Virtual Environment</h2>

<hr />

<p>Everything we need runs on Python. Default Debian 7 installation comes with Python <code>version</code> <code>2.7</code>. As this suits our requirements, we can continue with installing pip, which we need for the virtualenv (and other packages') installation.</p>

<p>Before getting pip, we first need to install its dependency: <strong><em>setuptools</em></strong>.</p>

<h3 id="2-1-install-setuptools">2.1 - Install <strong>setuptools</strong>:</h3>

<hr />

<p>We are going to securely download the setup files using a tool called <em>curl</em>. These setup files will not only allow us to have the installation process automated, but also ensures that we have the latest stable versions running on our system. curl here will verify the SSL certificates from the source and pass the data to the Python interpreter.</p>

<p>Execute the following command:</p>
<pre class="code-pre "><code langs="">$ curl https://bitbucket.org/pypa/setuptools/raw/bootstrap/ez_setup.py | python -
</code></pre>
<blockquote>
<p>This will install it system-wide.</p>
</blockquote>

<p>We can now install and set up <strong><em>pip</em></strong> on our system.</p>

<h3 id="2-2-install-pip">2.2 - Install <strong>pip</strong>:</h3>

<hr />

<p>Let's use curl again to have it securely downloaded and installed. Run the following:</p>
<pre class="code-pre "><code langs="">$ curl https://raw.github.com/pypa/pip/master/contrib/get-pip.py | python -
</code></pre>
<blockquote>
<p>This will install it system-wide.</p>
</blockquote>

<p>By default, pip installs its files under <code>/usr/local/bin</code> location. We need to append it to our <code>PATH</code> so that we will be able to run it with calling pip command directly. </p>

<p>Let's have it updated:</p>
<pre class="code-pre "><code langs="">export PATH="/usr/local/bin:$PATH"
</code></pre>
<p>As we have pip the package manager, all installations from now on are as easy as <code>pip install package-name</code>. However, as we want the latest stable release of virtualenv, we are going to provide pip with the address.</p>

<h3 id="2-3-install-virtualenv">2.3 - Install <strong>virtualenv</strong>:</h3>

<p>Run the following to have pip install virtualenv:</p>
<pre class="code-pre "><code langs="">pip install https://github.com/pypa/virtualenv/tarball/1.9.X
</code></pre>
<blockquote>
<p>This will install it system-wide.</p>
</blockquote>

<p>In case you were wondering, the standard way of installing would have been:</p>
<pre class="code-pre "><code langs="">pip install virtualenv
</code></pre>
<blockquote>
<p>This would also install it system-wide.</p>
</blockquote>

<h3 id="3-preparing-our-virtual-environment-venv-for-django-cms">3 - Preparing our Virtual Environment (<strong>venv</strong>) for Django CMS</h3>

<hr />

<p>All the tools we need are ready and we can begin preparing the virtual environment where our Django CMS project is going to reside.</p>

<p>Let's start with initiating a venv (virtual environment) called "django_cms" using virtualenv and go to the project's folder:</p>
<pre class="code-pre "><code langs="">virtualenv django_cms
cd django_cms
</code></pre>
<blockquote>
<p>We chose "django_cms" as the project repository's folder name. You can change it as you wish. Keep in mind that choosing an unrelated name could cause trouble in the future with maintenance.</p>
</blockquote>

<p>Upon creating a virtualenv, you need to activate it in order to use it.</p>
<pre class="code-pre "><code langs="">source bin/activate
</code></pre>
<blockquote>
<p>You can learn more about virtualenv's activation by clicking <a href="https://pypi.python.org/pypi/virtualenv">here</a>.</p>

<p>Upon activation, in order to deactivate, simply run the command <code>deactivate</code> when needed.</p>
</blockquote>

<h2 id="4-setting-up-django-cms-dependencies">4 - Setting up Django CMS Dependencies</h2>

<hr />

<h3 id="4-1-install-pillow-drop-in-pil-replacement">4.1 - Install pillow (drop-in <strong>pil</strong> replacement):</h3>

<hr />

<p>One of the dependencies that we need to have is called Python Imaging Library (PIL). Together with some other libraries we have installed earlier, PIL is used by Django [CMS] to process images.</p>

<p>That being said, we will abstain from PIL and use a more accommodating fork of PIL called <em>pillow</em>. This package is setuptools compatible and automatically solves several issues that would arise if we were to try and use pil inside a venv.</p>

<p>Run the following to have pillow downloaded and installed:</p>
<pre class="code-pre "><code langs="">django_cms$ pip install pillow
</code></pre>
<blockquote>
<p>As we have our venv activated, this will not be a system-wide installation. </p>
</blockquote>

<h3 id="4-2-installing-database-drivers">4.2 - Installing database drivers</h3>

<hr />

<p>Django [CMS] allows you to choose several database engines to power your application. PostgreSQL, MySQL, Oracle and SQLite are all currently supported. As recommended by Django project, we are going to opt for PostgreSQL and install necessary libraries and drivers that will allow us to use it as the backend of our application.</p>

<p>The PostgreSQL database adapter which is used by Django is called <strong>psycopg2</strong>. It needs libpq-dev library installed and we have installed it at the beginning. Therefore, we can continue with executing the following command to install psycopg2 in our venv:</p>
<pre class="code-pre "><code langs="">django_cms$ pip install psycopg2
</code></pre>
<blockquote>
<p>As we have our venv activated, this will not be a system-wide installation. </p>

<p>For more on psycopg2, you can visit <a href="http://initd.org/psycopg/docs/faq.html">http://initd.org/psycopg/docs/faq.html</a>.</p>
</blockquote>

<p><strong>Please note:</strong> These commands ready PostgreSQL for Django but does not give you a fully configured installation. If you choose to work with PostgreSQL and you need further instructions on Django, you may wish to visit the following IndiaReads tutorial on the exact subject by clicking <a href="https://indiareads/community/articles/how-to-install-and-configure-django-with-postgres-nginx-and-gunicorn">here</a>. </p>

<p>Below we are using an SQLite database. You should also modify that setting to work with your PostgreSQL installation if you decide to use it.</p>

<h2 id="5-installing-and-setting-up-django-cms-inside-our-python-virtual-environment">5 - Installing and Setting up Django CMS inside our Python Virtual Environment</h2>

<hr />

<h3 id="5-1-installing-django-cms">5.1 - Installing Django CMS</h3>

<hr />

<p>Django CMS comes with a number of other dependencies we yet need to install. However, thanks to pip, we can have the remaining automatically installed and set up with the Django CMS package: <strong>django-cms</strong>.</p>

<p>Simply run the following to conclude the installations:</p>
<pre class="code-pre "><code langs="">django_cms$ pip install django-cms
</code></pre>
<blockquote>
<p>As we have our venv activated, this will not be a system-wide installation.</p>
</blockquote>

<p>We now have everything installed: Django, django-classy-tags, south, html5lib, django-mptt, django-sekizai.</p>

<blockquote>
<p>To learn more about these packages click <a href="http://docs.django-cms.org/en/2.4.2/getting_started/installation.html#requirements">here</a>.</p>
</blockquote>

<h3 id="5-2-setting-up-django-cms">5.2 - Setting up Django CMS</h3>

<hr />

<p>Creating a Django CMS project consists of two parts. First, we will start a regular Django project in our virtual environment and then continue with setting it up to have it working as Django CMS.</p>

<p>Let's begin with creating the Django project. We will name it <strong>dcms</strong>, you can choose it to suit your needs.</p>

<p>Simply run the following:</p>
<pre class="code-pre "><code langs="">django_cms$ django-admin.py startproject dcms
django_cms$ cd dcms
</code></pre>
<p>You will see that our project is created. In order to test the installation before continuing with the configuration part, let's run the following to start a simple development server which we can access from the outside:</p>
<pre class="code-pre "><code langs="">django_cms$ python manage.py runserver 0.0.0.0:8000
</code></pre>
<p>Visit the URL from your browser, replacing <code>0.0.0.0</code> with your server's IP address.</p>

<p>We can now follow the instructions listed at <a href="http://docs.django-cms.org/en/2.4.2/getting_started/tutorial.html">Django CMS Introductory Tutorial</a> to finalize everything.</p>

<h3 id="5-3-finalizing-setup-as-per-the-introductory-tutorial">5.3 -  Finalizing setup as per the <a href="http://docs.django-cms.org/en/2.4.2/getting_started/tutorial.html">introductory tutorial</a></h3>

<hr />

<p>Most of the configurations for Django CMS takes place inside the <code>settings.py</code> file located inside the project folder. </p>

<p>Open it with your favourite editor. In this tutorial, we will be using <code>nano</code>.</p>
<pre class="code-pre "><code langs="">django_cms$ nano dcms/settings.py
</code></pre>
<p>Add the following lines to the top of the file:</p>
<pre class="code-pre "><code langs=""># -*- coding: utf-8 -*-
import os
gettext = lambda s: s
PROJECT_PATH = os.path.split(os.path.abspath(os.path.dirname(__file__)))[0]
</code></pre>
<p>To begin with the first batch of settings, scroll down the file and find <strong>INSTALLED_APPS</strong> section. Here, to the end of the currently existing list of modules, we are going to append the names of a few more which we've already installed, including the Django CMS module itself.</p>

<p>As stated on the Django CMS documentation:</p>

<blockquote>
<p><strong>Add</strong> the following apps to your INSTALLED_APPS. This includes django CMS itself as well as its dependenices and other highly recommended applications/libraries:</p>
</blockquote>
<pre class="code-pre "><code langs="">'cms',     # django CMS itself
'mptt',    # utilities for implementing a modified pre-order traversal tree
'menus',   # helper for model independent hierarchical website navigation
'south',   # intelligent schema and data migrations
'sekizai', # for javascript and css management
</code></pre>
<p><strong>Please note:</strong> Before moving on, make sure to uncomment <code>django.contrib.admin</code> from the list as well. That module is needed for the setup procedure.</p>

<p>Next, let's find <code>MIDDLEWARE_CLASSES</code> and add the following to the bottom of the list:</p>
<pre class="code-pre "><code langs="">'cms.middleware.page.CurrentPageMiddleware',
'cms.middleware.user.CurrentUserMiddleware',
'cms.middleware.toolbar.ToolbarMiddleware',
'cms.middleware.language.LanguageCookieMiddleware',
</code></pre>
<p>Afterwards your MIDDLEWARE_CLASSES should look similar to:</p>
<pre class="code-pre "><code langs="">MIDDLEWARE_CLASSES = (
    'django.middleware.common.CommonMiddleware',
    'django.contrib.sessions.middleware.SessionMiddleware',
    'django.middleware.csrf.CsrfViewMiddleware',
    'django.contrib.auth.middleware.AuthenticationMiddleware',
    'django.contrib.messages.middleware.MessageMiddleware',
    # Uncomment the next line for simple clickjacking protection:
    # 'django.middleware.clickjacking.XFrameOptionsMiddleware',
    'cms.middleware.page.CurrentPageMiddleware',
    'cms.middleware.user.CurrentUserMiddleware',
    'cms.middleware.toolbar.ToolbarMiddleware',
    'cms.middleware.language.LanguageCookieMiddleware',
)
</code></pre>
<p>As stated in the Django CMS documentation, we need to add a missing piece of settings code block to the file. It does not exist in <code>settings.py</code>. Copy-and-paste the block to a free location in the file:</p>
<pre class="code-pre "><code langs="">TEMPLATE_CONTEXT_PROCESSORS = (
    'django.contrib.auth.context_processors.auth',
    'django.core.context_processors.i18n',
    'django.core.context_processors.request',
    'django.core.context_processors.media',
    'django.core.context_processors.static',
    'cms.context_processors.media',
    'sekizai.context_processors.sekizai',
)
</code></pre>
<p>Now let's find and modify the <code>STATIC_ROOT</code> and <code>MEDIA_ROOT</code> directives similar to the following:</p>
<pre class="code-pre "><code langs="">MEDIA_ROOT = os.path.join(PROJECT_PATH, "media")
MEDIA_URL = "/media/"

STATIC_ROOT = os.path.join(PROJECT_PATH, "static")
STATIC_URL = "/static/"
</code></pre>
<p>Continue with modifying the <code>TEMPLATE_DIRS</code> directive to:</p>
<pre class="code-pre "><code langs="">TEMPLATE_DIRS = (
    os.path.join(PROJECT_PATH, "templates"),
)
</code></pre>
<p>Django CMS requires definition of at least one template which needs to be set under <code>CMS_TEMPLATES</code>. Add the following code block to the file, amending it as necessary to suit your needs:</p>
<pre class="code-pre "><code langs="">CMS_TEMPLATES = (
    ('template_1.html', 'Template One'),
)
</code></pre>
<p>We need to set the translation languages as well. Add the following code block:</p>
<pre class="code-pre "><code langs="">LANGUAGES = [ 
('en-us', 'English'),
]
</code></pre>
<p>Finally let's define a database engine. You can modify the <strong>DATABASES</strong> setting to work with PostgreSQL as shown or use the following to have SQLite database set up temporarily:</p>
<pre class="code-pre "><code langs="">DATABASES = {
    'default': {
        'ENGINE': 'django.db.backends.sqlite3',
        'NAME': os.path.join(PROJECT_PATH, 'database.sqlite'),
    }
}
</code></pre>
<p>We are done with the <code>settings.py</code>. We can save and close it. (Press CTRL+X and type Y to save and close).</p>

<p>We need to define routes for our project.</p>

<p>We will do this by editing <strong>urls.py</strong> file:</p>
<pre class="code-pre "><code langs="">django_cms$ nano dcms/urls.py
</code></pre>
<p>Replace the document with the following code snippet:</p>
<pre class="code-pre "><code langs="">from django.conf.urls.defaults import *
from django.conf.urls.i18n import i18n_patterns
from django.contrib import admin
from django.conf import settings

admin.autodiscover()

urlpatterns = i18n_patterns('',
    url(r'^admin/', include(admin.site.urls)),
    url(r'^', include('cms.urls')),
)

if settings.DEBUG:
    urlpatterns += patterns('',
    url(r'^media/(?P<path>.*)$', 'django.views.static.serve',
        {'document_root': settings.MEDIA_ROOT, 'show_indexes': True}),
    url(r'', include('django.contrib.staticfiles.urls')),
) + urlpatterns
</code></pre>
<blockquote>
<p>Please note that the last conditional created in the above snippet slightly differs from the Django CMS introductory settings, whereby <code>urlpatterns = patterns(</code> is replaced with <code>urlpatterns += patterns(</code> to fix the problem of overriding <code>urlpatterns</code> set above.</p>
</blockquote>

<p>Again press CTRL+X and type Y to save and close.</p>

<p>We will continue with preparing templates.</p>

<p>Create the <strong>templates</strong> folder:</p>
<pre class="code-pre "><code langs="">django_cms$ mkdir templates
</code></pre>
<p>Create an exemplary <strong>base</strong> template to extend others to come:</p>
<pre class="code-pre "><code langs="">django_cms$ nano templates/base.html
</code></pre>
<p>And fill it with the below code snippet:</p>
<pre class="code-pre "><code langs="">{% load cms_tags sekizai_tags %}
<html>
  <head>
      {% render_block "css" %}
  </head>
  <body>
      {% cms_toolbar %}
      {% placeholder base_content %}
      {% block base_content %}{% endblock %}
      {% render_block "js" %}
  </body>
</html>
</code></pre>
<p>Let's save and close and continue with creating our first template: <code>template_1.html</code> based on <code>base.html</code>.</p>
<pre class="code-pre "><code langs="">django_cms$ nano templates/template_1.html
</code></pre>
<p>Fill this one with the following short snippet:</p>
<pre class="code-pre "><code langs="">{% extends "base.html" %}
{% load cms_tags %}

{% block base_content %}
  {% placeholder template_1_content %}
{% endblock %}
</code></pre>
<p>Let's save and close this one as well.</p>

<p>Execute the following commands to synchronise database according to our settings:</p>
<pre class="code-pre "><code langs="">django_cms$ python manage.py syncdb --all
django_cms$ python manage.py migrate --fake
</code></pre>
<p>To finish everything, we should check if we set it all correctly using <strong>cms check</strong>:</p>
<pre class="code-pre "><code langs="">django_cms$ python manage.py cms check
</code></pre>
<p>If you see "Installation okay", it means everything is fine and we can try it on the test server before continuing with building on our Django CMS project.</p>

<p>Let's run the server again:</p>
<pre class="code-pre "><code langs="">django_cms$ python manage.py runserver 0.0.0.0:8000
</code></pre>
<ul>
<li><p>To see the Django CMS welcome screen go to:</p>

<p>http://your<em>servers</em>ip_addr:8000/en-us</p></li>
<li><p>To use the admin panel go to:</p>

<p>http://your<em>servers</em>ip:8000/en-us/admin</p></li>
</ul>

<p>You will need to login with the user you have created during database synchronization; you can continue customizing your CMS from there.</p>

<p>For further instructions, tutorials and documentation you can visit <a href="http://docs.django-cms.org/en/2.4.2/">http://docs.django-cms.org/en/2.4.2/</a>. To get more support on Django CMS, you can visit the support page located at <a href="https://www.django-cms.org/en/support/">https://www.django-cms.org/en/support/</a>.</p>

<div class="author">Submitted by: <a href="https://twitter.com/ostezer">O.S. Tezer</a></div>

    