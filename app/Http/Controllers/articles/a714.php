<link rel='stylesheet' href='digital_ocean.css'><br><img src=https://community-cdn-digitalocean-com.global.ssl.fastly.net/assets/tutorials/images/large/B_rnHp0UwAAPoo1-1.jpg?1426699828/> <br> 
      <h3 id="introduction">Introduction</h3>

<p>Django is a full-featured Python web framework for developing dynamic websites and applications.  Using Django, you can quickly create Python web applications and rely on the framework to do a good deal of the heavy lifting.</p>

<p>In this guide, we will show you how to get Django up and running on an Ubuntu 14.04 server.  After installation, we'll show you how to start a new project to use as the basis for your site.</p>

<h2 id="different-methods">Different Methods</h2>

<p>There are a number of different ways in which you can install Django depending upon your needs and how you want to configure your development environment.  These have different advantages and one method may lend itself better to your specific situation than others.</p>

<p>Some of the different methods are below:</p>

<ul>
<li><strong>Global Install from Packages</strong>:  The official Ubuntu repositories contain Django packages that can be installed easily with the conventional <code>apt</code> package manager.  This is very simple, but not as flexible as some other methods.  Also, the version contained in the repositories may lag behind the official versions available from the project.</li>
<li><strong>Global Install through pip</strong>: The <code>pip</code> tool is a package manager for Python packages.  If you install <code>pip</code>, you can easily install Django on the system level for use by any user.  This should always contain the latest stable release.  Even so, global installations are inherently less flexible.</li>
<li><strong>Install through pip in a Virtualenv</strong>: The Python <code>virtualenv</code> package allows you to create self-contained environments for various projects.  Using this technology, you can install Django in a project directory without affecting the greater system.  This allows you to provide per-project customizations and packages easily.  Virtual environments add some slight mental and process overhead in comparison to globally accessible installation, but provide the most flexibility.</li>
<li><strong>Development Version Install through git</strong>: If you wish to install the latest development version instead of the stable release, you will have to acquire the code from the <code>git</code> repo.  This is necessary to get the latest features/fixes and can be done globally or locally.  Development versions do not have the same stability guarantees, however.</li>
</ul>

<p>With the above caveats and qualities in mind, select the installation method that best suites your needs out of the below instructions.</p>

<h2 id="global-install-from-packages">Global Install from Packages</h2>

<p>If you wish to install Django using the Ubuntu repositories, the process is very straight forward.</p>

<p>First, update your local package index with <code>apt</code>, and then install the <code>python-django</code> package:</p>
<pre class="code-pre "><code langs="">sudo apt-get update
sudo apt-get install python-django
</code></pre>
<p>You can test that the installation was successful by typing:</p>
<pre class="code-pre "><code langs="">django-admin --version
</code></pre><pre class="code-pre "><code langs="">1.6.1
</code></pre>
<p>This means that the software was successfully installed.  You may also notice that the Django version is not the latest stable.  To learn a bit about how to use the software, skip ahead to learn <a href="https://indiareads/community/tutorials/how-to-install-the-django-web-framework-on-ubuntu-14-04#creating-a-sample-project">how to create sample project</a>.</p>

<h2 id="global-install-through-pip">Global Install through pip</h2>

<p>If you wish to install the latest version of Django globally, a better option is to use <code>pip</code>, the Python package manager.  First, we need to install the <code>pip</code> package manager.  Refresh your <code>apt</code> package index:</p>
<pre class="code-pre "><code langs="">sudo apt-get update
</code></pre>
<p>Now you can install <code>pip</code>.  If you plan on using Python version 2, install using the following commands:</p>
<pre class="code-pre "><code langs="">sudo apt-get install python-pip
</code></pre>
<p>If, instead, you plan on using Python 3, use this command:</p>
<pre class="code-pre "><code langs="">sudo apt-get install python3-pip
</code></pre>
<p>Now that you have <code>pip</code>, we can easily install Django.  If you are using Python 2, you can type:</p>
<pre class="code-pre "><code langs="">sudo pip install django
</code></pre>
<p>If you are using Python 3, use the <code>pip3</code> command instead:</p>
<pre class="code-pre "><code langs="">sudo pip3 install django
</code></pre>
<p>You can verify that the installation was successful by typing:</p>
<pre class="code-pre "><code langs="">django-admin --version
</code></pre><pre class="code-pre "><code langs="">1.7.5
</code></pre>
<p>As you can see, the version available through <code>pip</code> is more up-to-date than the one from the Ubuntu repositories (yours will likely be different from the above).</p>

<h2 id="install-through-pip-in-a-virtualenv">Install through pip in a Virtualenv</h2>

<p>Perhaps the most flexible way to install Django on your system is with the <code>virtualenv</code> tool.  This tool allows you to create virtual Python environments where you can install any Python packages you want without affecting the rest of the system.  This allows you to select Python packages on a per-project basis regardless of conflicts with other project's requirements.</p>

<p>We will begin by installing <code>pip</code> from the Ubuntu repositories.  Refresh your local package index before starting:</p>
<pre class="code-pre "><code langs="">sudo apt-get update
</code></pre>
<p>If you plan on using version 2 of Python, you can install <code>pip</code> by typing:</p>
<pre class="code-pre "><code langs="">sudo apt-get install python-pip
</code></pre>
<p>If, instead, you plan on using version 3 of Python, you can install <code>pip</code> by typing:</p>
<pre class="code-pre "><code langs="">sudo apt-get install python3-pip
</code></pre>
<p>Once <code>pip</code> is installed, you can use it to install the <code>virtualenv</code> package.  If you installed the Python 2 <code>pip</code>, you can type:</p>
<pre class="code-pre "><code langs="">sudo pip install virtualenv
</code></pre>
<p>If you installed the Python 3 version of <code>pip</code>, you should type this instead:</p>
<pre class="code-pre "><code langs="">sudo pip3 install virtualenv
</code></pre>
<p>Now, whenever you start a new project, you can create a virtual environment for it.  Start by creating and moving into a new project directory:</p>
<pre class="code-pre "><code langs="">mkdir ~/<span class="highlight">newproject</span>
cd ~/<span class="highlight">newproject</span>
</code></pre>
<p>Now, create a virtual environment within the project directory by typing:</p>
<pre class="code-pre "><code langs="">virtualenv <span class="highlight">newenv</span>
</code></pre>
<p>This will install a standalone version of Python, as well as <code>pip</code>, into an isolated directory structure within your project directory.  We chose to call our virtual environment <code><span class="highlight">newenv</span></code>, but you should name it something descriptive.  A directory will be created with the name you select, which will hold the file hierarchy where your packages will be installed.</p>

<p>To install packages into the isolated environment, you must activate it by typing:</p>
<pre class="code-pre "><code langs="">source <span class="highlight">newenv</span>/bin/activate
</code></pre>
<p>Your prompt should change to reflect that you are now in your virtual environment.  It will look something like <code>(<span class="highlight">newenv</span>)username@hostname:~/newproject$</code>.</p>

<p>In your new environment, you can use <code>pip</code> to install Django.  Regardless of whether you are using version 2 or 3 of Python, it should be called just <code>pip</code> when you are in your virtual environment.  Also note that you <em>do not</em> need to use <code>sudo</code> since you are installing locally:</p>
<pre class="code-pre "><code langs="">pip install django
</code></pre>
<p>You can verify the installation by typing:</p>
<pre class="code-pre "><code langs="">django-admin --version
</code></pre><pre class="code-pre "><code langs="">1.7.5
</code></pre>
<p>To leave your virtual environment, you need to issue the <code>deactivate</code> command from anywhere on the system:</p>
<pre class="code-pre "><code langs="">deactivate
</code></pre>
<p>Your prompt should revert to the conventional display.  When you wish to work on your project again, you should re-activate your virtual environment by moving back into your project directory and activating:</p>
<pre class="code-pre "><code langs="">cd ~/<span class="highlight">newproject</span>
source <span class="highlight">newenv</span>/bin/activate
</code></pre>
<h2 id="development-version-install-through-git">Development Version Install through git</h2>

<p>If you need a development version of Django, you will have to download and install Django from its <code>git</code> repository.</p>

<p>To do so, you will need to install <code>git</code> on your system with <code>apt</code>.  Refresh your local package index by typing:</p>
<pre class="code-pre "><code langs="">sudo apt-get update
</code></pre>
<p>Now, we can install <code>git</code>.  We will also install the <code>pip</code> Python package manager.  We will use this to handle the installation of Django after it has been downloaded.  If you are using Python 2, you can type:</p>
<pre class="code-pre "><code langs="">sudo apt-get install git python-pip
</code></pre>
<p>If you are using Python 3 instead, you should type this:</p>
<pre class="code-pre "><code langs="">sudo apt-get install git python3-pip
</code></pre>
<p>Once you have <code>git</code>, you can clone the Django repository.  Between releases, this repository will have more up-to-date features and bug fixes at the possible expense of stability.  You can clone the repository to a directory called <code>django-dev</code> within your home directory by typing:</p>
<pre class="code-pre "><code langs="">git clone git://github.com/django/django ~/django-dev
</code></pre>
<p>Once the repository is cloned, you can install it using <code>pip</code>.  We will use the <code>-e</code> option to install in "editable" mode, which is needed when installing from version control.  If you are using version 2 of Python, type:</p>
<pre class="code-pre "><code langs="">sudo pip install -e ~/django-dev
</code></pre>
<p>If you are using Python 3, type:</p>
<pre class="code-pre "><code langs="">sudo pip3 install -e ~/django-dev
</code></pre>
<p>You can verify that the installation was successful by typing:</p>
<pre class="code-pre "><code langs="">django-admin --version
</code></pre><pre class="code-pre "><code langs="">1.9.dev20150305171756
</code></pre>
<p>Note that you can also combine this strategy with the use of <code>virtualenv</code> above if you wish to install a development version of Django in a single environment.</p>

<h2 id="creating-a-sample-project">Creating a Sample Project</h2>

<p>Now that you have Django installed, we can show you briefly how to get started on a project.</p>

<p>You can use the <code>django-admin</code> command to create a project:</p>
<pre class="code-pre "><code langs="">django-admin startproject <span class="highlight">projectname</span>
cd <span class="highlight">projectname</span>
</code></pre>
<p>This will create a directory called <code><span class="highlight">projectname</span></code> within your current directory.  Within this, a management script will be created and another directory called <code><span class="highlight">projectname</span></code> will be created with the actual code.</p>

<p><strong>Note</strong>: If you were already in a project directory that you created for use with the <code>virtualenv</code> command, you can tell Django to place the management script and inner directory into the current directory without the extra layer by typing this (notice the ending dot):</p>
<pre class="code-pre "><code langs="">django-admin startproject <span class="highlight">projectname</span> .
</code></pre>
<p>To bootstrap the database (this uses SQLite by default) on more recent versions of Django, you can type:</p>
<pre class="code-pre "><code langs="">python manage.py migrate
</code></pre>
<p>If the <code>migrate</code> command doesn't work, you likely are using an older version of Django.  Instead, you can type:</p>
<pre class="code-pre "><code langs="">python manage.py syncdb
</code></pre>
<p>You will be asked to create an administrative user as part of this process.  Select a username, email address, and password for the user.</p>

<p>If you used the <code>migrate</code> command above, you'll need to create the administrative user manually.  You can create an administrative user by typing:</p>
<pre class="code-pre "><code langs="">python manage.py createsuperuser
</code></pre>
<p>You will be prompted for a username, an email address, and a password for the user.</p>

<p>Once you have a user, you can start up the Django development server to see what a fresh Django project looks like.  You should only use this for development purposes.  Run:</p>
<pre class="code-pre "><code langs="">python manage.py runserver 0.0.0.0:8000
</code></pre>
<p>Visit your server's IP address followed by <code>:8000</code> in your web browser</p>
<pre class="code-pre "><code langs=""><span class="highlight">server_ip_address</span>:8000
</code></pre>
<p>You should see something that looks like this:</p>

<p><img src="https://assets.digitalocean.com/articles/django_1404/django_default.png" alt="Django public page" /></p>

<p>Now, append <code>/admin</code> to the end of your URL to get to the admin login page:</p>
<pre class="code-pre "><code langs=""><span class="highlight">server_ip_address</span>:8000/admin
</code></pre>
<p><img src="https://assets.digitalocean.com/articles/django_1404/django_admin_login.png" alt="Django admin login" /></p>

<p>If you enter the admin username and password that you just created, you should be taken to the admin section of the site:</p>

<p><img src="https://assets.digitalocean.com/articles/django_1404/django_admin_page.png" alt="Django admin page" /></p>

<p>When you are finished looking through the default site, you can stop the development server by typing <code>CTRL-C</code> in your terminal.</p>

<p>The Django project you've created provides the structural basis for designing a more complete site.  Check out the Django documentation for more information about how to build your applications and customize your site.</p>

<h2 id="conclusion">Conclusion</h2>

<p>You should now have Django installed on your Ubuntu 14.04 server, providing the main tools you need to create powerful web applications.  You should also know how to start a new project and launch the developer server.  Leveraging a complete web framework like Django can help make development faster, allowing you to concentrate only on the unique aspects of your applications.</p>

    