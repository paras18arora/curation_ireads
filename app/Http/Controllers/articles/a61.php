<link rel='stylesheet' href='digital_ocean.css'><br> <br> 
      <h3 id="introduction">Introduction</h3>

<p>This article will walk you through setting up a server with Python 3, MySQL, and Apache2, sans the help of a framework. By the end of this tutorial, you will be fully capable of launching a barebones system into production.</p>

<p>Django is often the one-shop-stop for all things Python; it's compatible with nearly all versions of Python, comes prepackaged with a custom server, and even features a one-click-install database. Setting up a vanilla system without this powerful tool can be tricky, but earns you invaluable insight into server structure from the ground up.</p>

<p>This tutorial uses only package installers, namely apt-get and Pip. <em>Package installers</em> are simply small programs that make code installations much more convenient and manageable. Without them, maintaining libraries, modules, and other code bits can become an extremely messy business.</p>

<h2 id="prerequisites">Prerequisites</h2>

<p>To follow this tutorial, you will need:</p>

<ul>
<li>One Ubuntu 14.04 Droplet.</li>
<li>A sudo non-root user, which you can set up by following <a href="https://indiareads/community/tutorials/how-to-add-and-delete-users-on-an-ubuntu-14-04-vps">this tutorial</a>.</li>
</ul>

<h2 id="step-1-—-making-python-3-the-default">Step 1 — Making Python 3 the Default</h2>

<p>In this step, we will set Python 3 as the default for our <code>python</code> command.</p>

<p>First, check your current Python version.</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">python --version
</li></ul></code></pre>
<p>On a fresh Ubuntu 14.04 server, this will output:</p>
<pre class="code-pre "><code langs="">Python 2.7.6
</code></pre>
<p>We would like to have <code>python</code> run Python 3. So first, let's remove the old 2.7 binary.</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo rm /usr/bin/python
</li></ul></code></pre>
<p>Next, create a symbolic link to the Python 3 binary in its place.</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo ln -s /usr/bin/python3 /usr/bin/python
</li></ul></code></pre>
<p>If you run <code>python --version</code> again, you will now see <code>Python 3.4.0</code>.</p>

<h2 id="step-2-—-installing-pip">Step 2 — Installing Pip</h2>

<p>In this section, we will install Pip, the recommended package installer for Python.</p>

<p>First, update the system's package index. This will ensure that old or outdated packages do not interfere with the installation.</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo apt-get update
</li></ul></code></pre>
<p>Pip allows us to easily manage any Python 3 package we would like to have. To install it, simply run the following:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo apt-get install python3-pip
</li></ul></code></pre>
<p>For an overview of Pip, you can read <a href="https://indiareads/community/tutorials/common-python-tools-using-virtualenv-installing-with-pip-and-managing-packages" title="Installing with Pip and Virtualenv">this tutorial</a>.</p>

<h2 id="step-3-—-installing-mysql">Step 3 — Installing MySQL</h2>

<p>In this section, we will install and configure MySQL.</p>

<p>Installing SQL is simple:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo apt-get install mysql-server
</li></ul></code></pre>
<p>Enter a strong password for the MySQL root user when prompted, and remember it, because we will need it later.</p>

<p>The MySQL server will start once installation completes. After installation, run:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">mysql_secure_installation
</li></ul></code></pre>
<p>This setup will take you through a series of self-explanatory steps. First, you'll need to enter the root password you picked a moment ago. The first question will ask if you want to change the root password, but because you just set it, enter <strong>n</strong>. For all other questions, press <strong>ENTER</strong> to accept the default response.</p>

<p>Python 3 requires a way to connect with MySQL, however. There are a number of options, like MySQLclient, but for the module's simplicity, this tutorial will use <code>pymysql</code>. Install it using Pip:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo pip3 install pymysql
</li></ul></code></pre>
<h2 id="step-4-—-installing-apache-2">Step 4 — Installing Apache 2</h2>

<p>In this section, we will install Apache 2, and ensure that it recognizes Python files as executables.</p>

<p>Install Apache using apt-get:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo apt-get install apache2
</li></ul></code></pre>
<p>Like MySQL, the Apache server will start once the installation completes. </p>

<p><strong>Note</strong>: After installation, several ports are open to the internet. Make sure to see the conclusion of this tutorial for resources on security.</p>

<p>We want to place our website's root directory in a safe location. The server is by default at <code>/var/www/html</code>. To keep convention, we will create a new directory for testing purposes, called <code>test</code>, in the same location.</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo mkdir /var/www/test
</li></ul></code></pre>
<p>Finally, we must register Python with Apache. To start, we disable multithreading processes.</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo a2dismod mpm_event
</li></ul></code></pre>
<p>Then, we give Apache explicit permission to run scripts.</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo a2enmod mpm_prefork cgi
</li></ul></code></pre>
<p>Next, we modify the actual Apache configuration, to explicitly declare Python files as runnable file and allow such executables. Open the configuration file using nano or your favorite text editor.</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo nano /etc/apache2/sites-enabled/000-default.conf
</li></ul></code></pre>
<p>Add the following right after the first line, which reads <code><VirtualHost *:80\></code>.</p>
<pre class="code-pre "><code langs=""><Directory /var/www/<span class="highlight">test</span>>
    Options +ExecCGI
    DirectoryIndex index.py
</Directory>
AddHandler cgi-script .py
</code></pre>
<p>Make sure that your <code><Directory></code> block is nested inside the <code><VirtualHost></code> block, like so. Make sure to indent correctly with tabs, too.</p>
<div class="code-label " title="/etc/apache2/sites-enabled/000-default.conf">/etc/apache2/sites-enabled/000-default.conf</div><pre class="code-pre "><code class="code-highlight language-apache">
<VirtualHost *:80>
    <Directory /var/www/<span class="highlight">test</span>>
        Options +ExecCGI
        DirectoryIndex index.py
    </Directory>
    AddHandler cgi-script .py

    ...
</code></pre>
<p>This Directory block allows us to specify how Apache treats that directory. It tells Apache that the <code>/var/www/test</code> directory contains executables, considers <code>index.py</code> to be the default file, then defines the executables.</p>

<p>We also want to allow executables in our website directory, so we need to change the path for <code>DocumentRoot</code>, too. Look for the line that reads <code>DocumentRoot /var/www/html</code>, a few lines below the long comment at the top of the file, and modify it to read <code>/var/www/test</code> instead.</p>
<pre class="code-pre "><code langs="">DocumentRoot /var/www/<span class="highlight">test</span>
</code></pre>
<p>Your file should now resemble the following.</p>
<div class="code-label " title="/etc/apache2/sites-enabled/000-default.conf">/etc/apache2/sites-enabled/000-default.conf</div><pre class="code-pre "><code class="code-highlight language-apache">
<VirtualHost *:80>
        <Directory /var/www/<span class="highlight">test</span>>
                Options +ExecCGI
                DirectoryIndex index.py
        </Directory>
        AddHandler cgi-script .py

        ...

        DocumentRoot /var/www/<span class="highlight">test</span>

        ...
</code></pre>
<p>Save and exit the file. To put these changes into effect, restart Apache.</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo service apache2 restart
</li></ul></code></pre>
<p><strong>Note</strong>: Apache 2 may throw a warning which says about the server's fully qualified domain name; this can be ignored as the ServerName directive has little application as of this moment. They are ultimately used to determine subdomain hosting, after the <a href="https://indiareads/community/tutorials/how-to-set-up-a-host-name-with-digitalocean">necessary records</a> are created. </p>

<p>If the last line of the output reads <code>[ OK ]</code>, Apache has restarted successfully.</p>

<h2 id="step-5-—-testing-the-final-product">Step 5 — Testing the Final Product</h2>

<p>In this section, we will confirm that individual components (Python, MySQL, and Apache) can interact with one another by creating an example webpage and database.</p>

<p>First, let's create a database. Log in to MySQL. You'll need to enter the MySQL root password you set earlier.</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">mysql -u root -p
</li></ul></code></pre>
<p>Add an example database called <strong>example</strong>.</p>
<pre class="code-pre custom_prefix"><code langs=""><ul class="prefixed"><li class="line" prefix="mysql>">CREATE DATABASE example;
</li></ul></code></pre>
<p>Switch to the new database. </p>
<pre class="code-pre custom_prefix"><code langs=""><ul class="prefixed"><li class="line" prefix="mysql>">USE example;
</li></ul></code></pre>
<p>Add a table for some example data that we'll have the Python app add.</p>
<pre class="code-pre custom_prefix"><code langs=""><ul class="prefixed"><li class="line" prefix="mysql>">CREATE TABLE numbers (num INT, word VARCHAR(20));
</li></ul></code></pre>
<p>Press <strong>CTRL+D</strong> to exit. For more background on SQL, you can read this <a href="https://indiareads/community/tutorials/a-basic-MySQL-tutorial" title="how to use MySQL">MySQL tutorial</a>.</p>

<p>Now, create a new file for our simple Python app.</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo nano /var/www/test/index.py
</li></ul></code></pre>
<p>Copy and paste the following code in. The in-line comments describe what each piece of the code does. Make sure to replace the <code>passwd</code> value with the root MySQL password you chose earlier.</p>
<pre class="code-pre "><code class="code-highlight language-python">#!/usr/bin/python

# Turn on debug mode.
import cgitb
cgitb.enable()

# Print necessary headers.
print("Content-Type: text/html")
print()

# Connect to the database.
import pymysql
conn = pymysql.connect(
    db='example',
    user='root',
    passwd='<span class="highlight">your_root_mysql_password</span>',
    host='localhost')
c = conn.cursor()

# Insert some example data.
c.execute("INSERT INTO numbers VALUES (1, 'One!')")
c.execute("INSERT INTO numbers VALUES (2, 'Two!')")
c.execute("INSERT INTO numbers VALUES (3, 'Three!')")
conn.commit()

# Print the contents of the database.
c.execute("SELECT * FROM numbers")
print([(r[0], r[1]) for r in c.fetchall()])

</code></pre>
<p>Save and exit.</p>

<p>Next, fix permissions on the newly-created file. For more information on the three-digit permissions code, see the tutorial on <a href="https://indiareads/community/tutorials/linux-permissions-basics-and-how-to-use-umask-on-a-vps">Linux permissions</a>.</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo chmod 755 /var/www/test/index.py
</li></ul></code></pre>
<p>Now, access your server's by going to <code>http://<span class="highlight">your_server_ip</span></code> using your favorite browser. You should see the following:</p>
<div class="code-label " title="http://<span class=" highlight>your_server_ip'>http://<span class="highlight">your_server_ip</span></div><pre class="code-pre "><code langs="">[(1, 'One!'), (2, 'Two!'), (3, 'Three!')]
</code></pre>
<p>Congratulations! Your server is now online.</p>

<h2 id="conclusion">Conclusion</h2>

<p>You now have a working server that can run Python 3 with a robust, SQL database. The server is now also configured for easy maintenance, via well-documented and established package installers.</p>

<p>However, in its current state, the server is vulnerable to outsiders. Whereas elements like SSL encryption are not essential to your server's function, they are indispensable resources for a reliable, safe server. Learn more by reading about <a href="https://indiareads/community/tutorials/how-to-configure-the-apache-web-server-on-an-ubuntu-or-debian-vps">how to configure Apache</a>, <a href="https://indiareads/community/tutorials/how-to-create-a-ssl-certificate-on-apache-for-ubuntu-14-04">how to create an Apache SSL certificate</a> and <a href="https://indiareads/community/tutorials/7-security-measures-to-protect-your-servers">how to secure your Linux server</a>.</p>

    