<link rel='stylesheet' href='digital_ocean.css'><br> <br> 
      <h3 id="introduction">Introduction</h3>

<p>Zope 2 is a powerful and easy-to-use web-based development framework. This is especially the case when used as a front-end for PostgreSQL.</p>

<p>When used together, high quality database applications with HTML / XML interface can be constructed quickly in a highly scalable, secure, and maintainable fashion.</p>

<p>Compared to the ease-of-use of the resulting system, the installation process is often non-trivial, as there is no single Debian/Ubuntu package that contains and installs all the necessary components. <strong>That is the gap that this tutorial aims to fill.</strong></p>

<p>A major challenge to installing Zope + PostgreSQL has been the need to use an adapter that connects between these two. Psycopg2 has been a fast and reliable database adapter but can take a bit of custom trouble-shooting during the installation process from time to time.</p>

<p>The installation procedure documented below is confirmed to work for the versions of Zope, PostgreSQL, PsycopgDA, ZPsycopgDA listed below as examples.  If you encounter any difficulties after following these steps, please post a comment and we will all work together to keep the tutorial fresh and relevant.</p>

<h2 id="install-postgresql">Install PostgreSQL</h2>

<p>Before installing Debian or Ubuntu packages, it is best to change to superuser and perform an update of the package repository:</p>
<pre class="code-pre "><code langs="">   sudo su
   apt-get update
</code></pre>
<p>in case you want to unzip some of the zope related packages<br />
       apt-get install zip</p>

<p>After that, simply install PostgreSQL (9.1.12 is the version in this example):</p>
<pre class="code-pre "><code langs="">   apt-get install postgresql
</code></pre>
<h2 id="zope-installation">Zope Installation</h2>

<p>Install virtualenv, which is helpful to isolate the zope installation from rest of the Python environment on the VPS.</p>
<pre class="code-pre "><code langs="">   apt-get install python-virtualenv
</code></pre>
<p>make a directory in /home</p>
<pre class="code-pre "><code langs="">   mkdir /home/server
   cd /home/server
</code></pre>
<p>create a virtual python environment for zope installation</p>
<pre class="code-pre "><code langs="">   virtualenv --no-site-packages my_zope
   cd my_zope
</code></pre>
<p>activate the virtual environment</p>
<pre class="code-pre "><code langs="">   source bin/activate
</code></pre>
<p>Install python-dev, which are needed to build Zope from source</p>
<pre class="code-pre "><code langs="">   apt-get install python-dev
</code></pre>
<p>Find out the newest Zope version number by using a web browser.  You don't have to download it, just note the version number for the next step:</p>
<pre class="code-pre "><code langs="">   http://download.zope.org/Zope2/index/
</code></pre>
<p>Then install Zope (change 2.13.21 to a different version number as appropriate):</p>
<pre class="code-pre "><code langs="">   pip install --pre --index-url=http://download.zope.org/Zope2/index/2.13.21/ Zope2
</code></pre>
<p>We are ready to make a zope instance.  For this example, we will assume that the zope instance directory will be /home/server/zope</p>
<pre class="code-pre "><code langs="">   mkzopeinstance
</code></pre>
<p>Change into that zope instance directory</p>
<pre class="code-pre "><code langs="">   cd /home/server/zope
</code></pre>
<p>change ownership to the postgres user; this makes it easier for Zope to access PostgreSQL.</p>
<pre class="code-pre "><code langs="">   chown -R postgres:postgres *
</code></pre>
<p>change the zope configuration file to run Zope as postgres user (use any editor you like, I am just using vi as example)</p>
<pre class="code-pre "><code langs="">   vi etc/zope.conf
</code></pre>
<p>find the "effective-user" directive, uncomment, and type in "postgres". The line should look like this when done</p>
<pre class="code-pre "><code langs="">   effective-user postgres
</code></pre>
<h2 id="install-psycopg2-and-zpsycopgda">Install Psycopg2 and ZPsycopgDA</h2>

<p>Install the pre-requisite packages</p>
<pre class="code-pre "><code langs="">   apt-get install libpq-dev
</code></pre>
<p>Download the Psycopg package and find the most recent version by going to <code>http://www.init.d.org</code>.  Change the version number as appropriate:</p>
<pre class="code-pre "><code langs="">   wget http://initd.org/psycopg/tarballs/PSYCOPG-2-5/psycopg2-2.5.2.tar.gz
</code></pre>
<p>uncompress</p>
<pre class="code-pre "><code langs="">   tar xvfz psycopg*gz
</code></pre>
<p>install psycopg2</p>
<pre class="code-pre "><code langs="">   cd psycopg2*
   python setup.py build
   python setup.py install
</code></pre>
<p>Next step is to add ZPsycopgDA to the Zope Products directory.  This will link Zope to the Psycopg2 library.</p>

<p>Download the latest ZPsycopgDA (please change the file name asappropriate) from this site: <code>https://pypi.python.org/pypi/ZPsycopgDA/</code></p>
<pre class="code-pre "><code langs="">   https://pypi.python.org/packages/source/Z/ZPsycopgDA/ZPsycopgDA-2.4.6.zip#md5=c76a0e1c8708154dcf07d1362ea8c432
</code></pre>
<p>Install by unzipping and then moving the ZPsycopgDA directory into the Zope instance directory (e.g. /home/server/zope/Products)</p>
<pre class="code-pre "><code langs="">   unzip ZPsycopgDA*zip
   cd ZPsycopgDA*
   mv ZPsycopgDA /home/server/zope/Products
</code></pre>
<p>install the Zope ZSQLMethods product</p>
<pre class="code-pre "><code langs="">   easy_install Products.ZSQLMethods
</code></pre>
<h3 id="start-zope">Start Zope</h3>
<pre class="code-pre "><code langs="">   /home/server/zope/bin/zopectl start
</code></pre>
<h3 id="create-database">Create Database</h3>

<p>Of course, to use the database, it must be created first.  To do that, change into the postgres user.</p>
<pre class="code-pre "><code langs="">   su postgres
   createdb my_first_database
</code></pre>
<p>Now, you can use a web-browser to connect to Zope and use your database as well.</p>
<pre class="code-pre "><code langs="">   point your web browser to ip.address.of.server:8080, the Zope management interface will be displayed
   Log-in using the credentials your provided during mkzopeinstance
   select Z Psycopg 2 Database Connection from the drop-down menu
   for connection string, use the following: dbname=my_first_database user=postgres
</code></pre>
<h3 id="all-done">All done!</h3>

<p>If you are new to Zope and PostgreSQL, the next thing to do is create SQL methods to create tables, run queries, etc.</p>

<p>Write SQL by adding Z SQL Method objects (from drop down menu) in the Zope management interface.</p>

<div class="author">Submitted by: <a href="http://www.ExoMachina.com">Andrew Ho</a></div>

    