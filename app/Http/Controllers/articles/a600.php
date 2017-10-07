<link rel='stylesheet' href='digital_ocean.css'><br> <br> 
      <h3 id="introduction">Introduction</h3>

<p>Symfony is an open source web framework written in PHP, suitable for building projects of any size. It provides a well-designed structure, based on reusable components, on top of which you can build your own PHP application.</p>

<p>This tutorial will cover the steps necessary to manually deploy a basic Symfony application on a Ubuntu 14.04 server. We'll see how to properly configure the server, taking security and performance measures into consideration, in order to accomplish a setup that is ready for production.</p>

<p>If you are looking for an introductory tutorial on Symfony, you can read <a href="https://indiareads/community/tutorials/how-to-install-and-get-started-with-symfony-2-on-ubuntu-14-04">how to install and get started with Symfony on Ubuntu 14.04</a>.</p>

<h2 id="prerequisites">Prerequisites</h2>

<p>For this tutorial, you will need:</p>

<ul>
<li>A fresh Ubuntu 14.04 Droplet running <a href="https://indiareads/community/tutorials/how-to-install-linux-apache-mysql-php-lamp-stack-on-ubuntu-14-04">LAMP</a> or <a href="https://indiareads/community/tutorials/how-to-install-linux-nginx-mysql-php-lemp-stack-on-ubuntu-14-04">LEMP</a></li>
<li>A sudo non-root user, which you can set up by following the <a href="https://indiareads/community/tutorials/initial-server-setup-with-ubuntu-14-04">Initial Server Setup</a> tutorial</li>
</ul>

<p>It's important to keep in mind that deployment is a very extensive subject because each application will have its own specific needs. To keep things simple, we are going to use a sample to-do application built with Symfony. You can find its source code on <a href="https://github.com/php-demos/symfony">GitHub</a>.</p>

<h2 id="step-1-—-installing-the-server-dependencies">Step 1 — Installing the Server Dependencies</h2>

<p>In this step, we’ll install the server dependencies. </p>

<p>Start by updating the package manager cache.</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo apt-get update
</li></ul></code></pre>
<p>We'll need <code>git</code> to check out the application files, <code>acl</code> to set the right directory permissions when installing the application, and two PHP extensions (<code>php5-cli</code> to run PHP on the command line and <code>php5-curl</code> for Symfony). Install the required packages.</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo apt-get install git php5-cli php5-curl acl
</li></ul></code></pre>
<p>Lastly, we’ll need <code>composer</code> to download the application dependencies. To install <code>composer</code> system-wide, run:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo curl -sS https://getcomposer.org/installer | sudo php -- --install-dir=/usr/local/bin --filename=composer
</li></ul></code></pre>
<p>You should now be ready to go.</p>

<h2 id="step-2-—-configuring-mysql">Step 2 — Configuring MySQL</h2>

<p>Let's start by getting your MySQL setup ready for production. For the next step, you will need the password for the <strong>root</strong> MySQL account. Make sure you've set up MySQL securely (as detailed in step 2 of the <a href="https://indiareads/community/tutorials/how-to-install-linux-apache-mysql-php-lamp-stack-on-ubuntu-14-04#step-two-%E2%80%94-install-mysql">LAMP</a> and <a href="https://indiareads/community/tutorials/how-to-install-linux-nginx-mysql-php-lemp-stack-on-ubuntu-14-04#step-two-%E2%80%94-install-mysql-to-manage-site-data">LEMP</a> tutorials).</p>

<p><span class="note">If you are using one of our one-clicks (LAMP / LEMP), you will find the MySQL root password in the message of the day text that is printed when you log on to your server. The contents of the message of the day can also be found in the file <code>/etc/motd.tail</code>.<br /></span></p>

<h3 id="setting-the-default-collation-and-charset">Setting the Default Collation and Charset</h3>

<p>Symfony recommends setting up the charset and collation of your database to <code>utf8</code>. Most databases will use Latin type collations by default, which will cause unexpected results when retrieving data previously stored in the database, like weird characters and unreadable text. There's no way to configure this at application level, so we need to edit the MySQL configuration file to include a couple definitions.</p>

<p>Open the file <code>/etc/mysql/my.cnf</code> with your favorite command line editor.</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo nano /etc/mysql/my.cnf
</li></ul></code></pre>
<p>Now, find the <strong>[mysqld]</strong> block. Add the <code>collation-server</code> and <code>character-set-server</code> options under <strong>Basic Settings</strong>.</p>
<div class="code-label " title="/etc/mysql/my.cnf">/etc/mysql/my.cnf</div><pre class="code-pre "><code langs="">[mysqld]
#
# * Basic Settings
#
<span class="highlight">collation-server     = utf8mb4_general_ci # Replaces utf8_general_ci</span>
<span class="highlight">character-set-server = utf8mb4            # Replaces utf8</span>
user            = mysql
pid-file        = /var/run/mysqld/mysqld.pid
socket          = /var/run/mysqld/mysqld.sock

. . .
</code></pre>
<p>Save and exit. Restart MySQL so the changes take effect.</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo service mysql restart
</li></ul></code></pre>
<h3 id="creating-a-user-and-a-database-for-the-application">Creating a User and a Database for the Application</h3>

<p>Now we need to create a MySQL database and a user for our application. </p>

<p>First, access the MySQL client using the MySQL <strong>root</strong> account.</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">mysql -u root -p
</li></ul></code></pre>
<p>You will be asked for a password. This should be the same password you used when running <code>mysql_secure_installation</code>.</p>

<p>Now, create the application database.</p>
<pre class="code-pre custom_prefix"><code langs=""><ul class="prefixed"><li class="line" prefix="mysql>">CREATE DATABASE <span class="highlight">todo</span>;
</li></ul></code></pre><pre class="code-pre "><code langs=""><div class="secondary-code-label " title="Output">Output</div>Query OK, 1 row affected (0.00 sec)
</code></pre>
<p>The database is now created. The next step is to create a MySQL user and provide them access to our newly created database.</p>
<pre class="code-pre custom_prefix"><code langs=""><ul class="prefixed"><li class="line" prefix="mysql>">CREATE USER '<span class="highlight">todo-user</span>'@'localhost' IDENTIFIED BY '<span class="highlight">todo-password</span>';
</li></ul></code></pre><pre class="code-pre "><code langs=""><div class="secondary-code-label " title="Output">Output</div>Query OK, 0 rows affected (0.00 sec)
</code></pre>
<p>This will create a user named <strong>todo-user</strong>, with the password <strong>todo-password</strong>. It’s important to notice that these are simple example values that should be changed, and you should use a more complex password for your MySQL user for improved security.</p>

<p>We still need to grant this user the right permissions over our application database. This can be done with:</p>
<pre class="code-pre custom_prefix"><code langs=""><ul class="prefixed"><li class="line" prefix="mysql>">GRANT ALL PRIVILEGES ON <span class="highlight">todo</span>.* TO '<span class="highlight">todo-user</span>'@'localhost';
</li></ul></code></pre><pre class="code-pre "><code langs=""><div class="secondary-code-label " title="Output">Output</div>Query OK, 0 rows affected (0.00 sec)
</code></pre>
<p>This will grant the <strong>todo-user</strong> user all privileges on all tables inside the <code>todo</code> database. To apply the changes, run:</p>
<pre class="code-pre custom_prefix"><code langs=""><ul class="prefixed"><li class="line" prefix="mysql>">FLUSH PRIVILEGES;
</li></ul></code></pre><pre class="code-pre "><code langs=""><div class="secondary-code-label " title="Output">Output</div>Query OK, 0 rows affected (0.00 sec)
</code></pre>
<p>To test if everything is working as expected, exit the MySQL client.</p>
<pre class="code-pre custom_prefix"><code langs=""><ul class="prefixed"><li class="line" prefix="mysql>">quit;
</li></ul></code></pre>
<p>Now log in again, this time using the new MySQL user and password you just created. In this example, we are using the username <strong>todo-user</strong>, with the password <strong>todo-password</strong>.</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">mysql -u <span class="highlight">todo-user</span> -p
</li></ul></code></pre>
<p>You can check which databases this user has access to with:</p>
<pre class="code-pre custom_prefix"><code langs=""><ul class="prefixed"><li class="line" prefix="mysql>">SHOW DATABASES;
</li></ul></code></pre>
<p>The output should look like this:</p>
<pre class="code-pre "><code langs=""><div class="secondary-code-label " title="Output">Output</div>+--------------------+
| Database           |
+--------------------+
| information_schema |
| <span class="highlight">todo</span>               |
+--------------------+
2 rows in set (0.00 sec)
</code></pre>
<p>This means the new user was successfully created with the right privileges. You should only see two databases: <code>information_schema</code> and <code>todo</code>.</p>

<p>You can now exit the MySQL client.</p>
<pre class="code-pre custom_prefix"><code langs=""><ul class="prefixed"><li class="line" prefix="mysql>">quit;
</li></ul></code></pre>
<h2 id="step-3-—-checking-out-the-application-code">Step 3 — Checking Out the Application Code</h2>

<p>Deployment is an extensive subject due to the unique nature of most applications, even if we only consider Symfony projects. It's hard to generalize because each use case can require very specific deployment steps, like migrating a database or running extra setup commands. </p>

<p>In order to simplify the tutorial flow, we are going to use a basic demo application built with Symfony. You can also use your own Symfony application, but keep in mind that you might have to execute extra steps depending on your application needs.</p>

<p>Our application is a simple to-do list which allows you to add and remove items, and change each item’s status. The to-do items are stored in a MySQL database. The source code is available on <a href="https://github.com/php-demos/symfony">GitHub</a>.</p>

<p>We are going to use <a href="https://indiareads/community/tutorials/how-to-use-git-effectively">Git</a> to check out the application code. The next step is to choose a location that will serve as our application root directory. Later on, we will configure the web server accordingly. For this tutorial, we are going to use <code>/var/www/todo-symfony</code>, so create that directory now.</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo mkdir -p /var/www/<span class="highlight">todo-symfony</span>
</li></ul></code></pre>
<p>Before cloning the repository, let's change the folder owner and group so we are able to work with the project files using our regular user account. Replace<strong>sammy</strong> with your sudo non-root username.</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo chown <span class="highlight">sammy:sammy</span> /var/www/<span class="highlight">todo-symfony</span>
</li></ul></code></pre>
<p>Now, move to the parent directory and clone the application.</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">cd /var/www
</li><li class="line" prefix="$">git clone <span class="highlight">https://github.com/php-demos/todo-symfony.git todo-symfony</span>
</li></ul></code></pre><pre class="code-pre "><code langs=""><div class="secondary-code-label " title="Output">Output</div>Cloning into 'todo-symfony'...
remote: Counting objects: 76, done.
remote: Compressing objects: 100% (61/61), done.
remote: Total 76 (delta 6), reused 76 (delta 6), pack-reused 0
Unpacking objects: 100% (76/76), done.
Checking connectivity... done.
</code></pre>
<h2 id="step-4-—-fixing-the-folder-permissions">Step 4 — Fixing the Folder Permissions</h2>

<p>The application files are now located at <code>/var/www/todo-symfony</code>, a directory owned by our <em>system user</em> (in this tutorial, we are using <strong>sammy</strong> as example). However, the <em>web server user</em> (usually <strong>www-data</strong>) also needs access to those files. Otherwise, the web server will be unable to serve the application. Apart from that, there are two directories that require a special permissions arrangement: <code>app/cache</code> and <code>app/logs</code>. These directories should be writable by both the system user and the web server user.</p>

<p>We'll use ACL (Access Control Lists) for configuring these special permissions. ACLs enable more fine-grained access rights for files and directories, which is what we need to set up the correct permissions while avoiding too permissive arrangements.</p>

<p>First, we need to allow the user <strong>www-data</strong> access to the files inside the application folder. Give this user a <em>read + execute</em> permission (rX) in the whole directory.</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo setfacl -R -m u:www-data:rX <span class="highlight">todo-symfony</span>
</li></ul></code></pre>
<p>Next, we need to set up special permissions for the <code>cache</code> and <code>logs</code> folders. Give <em>read + write + execute</em> permissions (rwX) to the user <strong>www-data</strong> in order to enable the web server to write only in these directories.</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo setfacl -R -m u:www-data:rwX <span class="highlight">todo-symfony</span>/app/cache <span class="highlight">todo-symfony</span>/app/logs
</li></ul></code></pre>
<p>Finally, we will define that all new files created inside the <code>app/cache</code> and <code>app/logs</code> folders follow the same permission schema we just defined, with read, write, and execute permissions to the web server user. This is done by repeating the <code>setfacl</code> command we just ran, but this time adding the <code>-d</code> option.</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo setfacl -dR -m u:www-data:rwX <span class="highlight">todo-symfony</span>/app/cache <span class="highlight">todo-symfony</span>/app/logs
</li></ul></code></pre>
<p>If you want to check which permissions are currently in place in a given directory, you can use <code>getfacl</code> .</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">getfacl <span class="highlight">todo-symfony</span>/app/cache
</li></ul></code></pre>
<p>You should get output similar to this:</p>
<pre class="code-pre "><code langs=""><div class="secondary-code-label " title="Output">Output</div># file: todo-symfony/app/cache
# owner: sammy
# group: sammy
user::rwx
user:www-data:rwx
group::rwx
mask::rwx
other::r-x
default:user::rwx
default:user:www-data:rwx
default:group::rwx
default:mask::rwx
default:other::r-x
</code></pre>
<p>From this output, you can see that even though the directory <code>app/cache</code> is owned by the user <strong>sammy</strong>, there's an additional set of permissions for the user <strong>www-data</strong>. The default directives show which permissions new files created inside this directory will have.</p>

<h2 id="step-5-—-setting-up-the-application">Step 5 — Setting Up the Application</h2>

<p>We have now the application files in place, but we still need to install the project dependencies and configure the application parameters.</p>

<p>Symfony is built to work well across different environments. By default, it will use development settings, which influences the way it handles cache and errors. Development environments have more extensive and detailed logs, less cached content, and errors are exhibited in a prominent way to simplify debugging. This is useful for developing the application, but it's not a good practice for production environments.</p>

<p>To tune up the application for production, we need to define an environment variable that tells Symfony we’re running the application on a production environment.</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">export SYMFONY_ENV=prod
</li></ul></code></pre>
<p>Next, we need to install the project dependencies. Access the application folder and run <code>composer install</code>.</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">cd <span class="highlight">todo-symfony</span>
</li><li class="line" prefix="$">composer install --no-dev --optimize-autoloader
</li></ul></code></pre>
<p>At the end of the installation process, you should be prompted to provide some information that will populate the <code>parameters.yml</code> file. This file contains important information for the application, like the database connection settings. You can press <code>ENTER</code> to accept the default values for all of these, except for the database name, username, and password. For those, use the values you created in <a href="#step-2-%E2%80%94-configure-mysql">step 2</a>.</p>
<pre class="code-pre "><code langs=""><div class="secondary-code-label " title="Output">Output</div>Creating the "app/config/parameters.yml" file
Some parameters are missing. Please provide them.
database_host (127.0.0.1): 
database_port (null): 
database_name (symfony): <span class="highlight">todo</span>
database_user (root): <span class="highlight">todo-user</span>
database_password (null): <span class="highlight">todo-password</span>
. . .
</code></pre>
<p>When the installation finishes, we can check the database connection with the <code>doctrine:schema:validate</code> console command.</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">php app/console doctrine:schema:validate
</li></ul></code></pre><pre class="code-pre "><code langs=""><div class="secondary-code-label " title="Output">Output</div>[Mapping]  OK - The mapping files are correct.
[Database] FAIL - The database schema is not in sync with the current mapping file.
</code></pre>
<p>The OK line means that the database connection is working. The FAIL line is expected because we haven't created the database schema yet, so let's do that next:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">php app/console doctrine:schema:create
</li></ul></code></pre><pre class="code-pre "><code langs=""><div class="secondary-code-label " title="Output">Output</div>ATTENTION: This operation should not be executed in a production environment.

Creating database schema...
Database schema created successfully!
</code></pre>
<p>This will create all the application tables in the configured database, according to the metadata information obtained from the application entities. </p>

<p><span class="note">When migrating an existing application, you should avoid using <code>doctrine:schema:create</code> and <code>doctrine:schema:update</code> commands directly, and do a <a href="http://symfony.com/doc/current/bundles/DoctrineMigrationsBundle/index.html"><strong>database migration</strong></a> instead. In our case, a migration is not necessary because the application is supposed to be installed with a clean, empty database.<br /></span></p>

<p>Now you should clear the cache.</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">php app/console cache:clear --env=prod --no-debug
</li></ul></code></pre><pre class="code-pre "><code langs=""><div class="secondary-code-label " title="Output">Output</div>Clearing the cache for the prod environment with debug false
</code></pre>
<p>And lastly, generate the application assets.</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">php app/console assetic:dump --env=prod --no-debug
</li></ul></code></pre><pre class="code-pre "><code langs=""><div class="secondary-code-label " title="Output">Output</div>Dumping all prod assets.
Debug mode is off.

14:02:39 [file+] /var/www/todo-symfony/app/../web/css/app.css
14:02:39 [dir+] /var/www/todo-symfony/app/../web/js
14:02:39 [file+] /var/www/todo-symfony/app/../web/js/app.js
</code></pre>
<h2 id="step-6-—-setting-up-the-web-server">Step 6 — Setting Up the Web Server</h2>

<p>The only thing left to do is to configure the web server. This will involve 2 steps: setting the <code>date.timezone</code> directive in <code>php.ini</code>, and updating the default website config file (either on Apache or Nginx) for serving our application.</p>

<p>We'll see how to accomplish these steps on both LEMP and LAMP environments.</p>

<h3 id="configuration-steps-for-nginx-php-fpm">Configuration Steps for Nginx + PHP-FPM</h3>

<p>Let's start by editing the default <code>php.ini</code> file to define the server's timezone. This is a requirement for running Symfony applications, and it's usually commented out on fresh server installations.</p>

<p>Open the file <code>/etc/php5/fpm/php.ini</code>.</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo nano /etc/php5/fpm/php.ini 
</li></ul></code></pre>
<p>Search for the line containing <code>date.timezone</code>. Uncomment the directive by removing the <code>;</code> sign at the beginning of the line, and add the appropriate timezone for your application. In this example we'll use <code>Europe/Amsterdam</code>, but you can choose any <a href="http://php.net/manual/en/timezones.php">supported timezone</a>.</p>
<div class="code-label " title="modified /etc/php5/fpm/php.ini">modified /etc/php5/fpm/php.ini</div><pre class="code-pre "><code langs="">[Date]
; Defines the default timezone used by the date functions
; http://php.net/date.timezone
date.timezone = <span class="highlight">Europe/Amsterdam</span>
</code></pre>
<p>Save the file and exit. To apply the changes, restart PHP.</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo service php5-fpm restart
</li></ul></code></pre>
<p>Next, we need to replace the default website config file with a one customized for serving a Symfony application. Create a backup of the current default website config first.</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">cd /etc/nginx/sites-available
</li><li class="line" prefix="$">sudo mv default default-bkp
</li></ul></code></pre>
<p>Create a new file to replace the old one.</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo nano /etc/nginx/sites-available/default
</li></ul></code></pre>
<p>Paste the following content in the file. Don't forget to replace the <code>server_name</code> values to reflect your server domain name or IP address.</p>
<div class="code-label " title="/etc/nginx/sites-available/default">/etc/nginx/sites-available/default</div><pre class="code-pre line_numbers"><code langs=""><ul class="prefixed"><li class="line" prefix="1">server {
</li><li class="line" prefix="2">    server_name <span class="highlight">example.com www.example.com your_server_ip</span>;
</li><li class="line" prefix="3">    root /var/www/<span class="highlight">todo-symfony</span>/web;
</li><li class="line" prefix="4">
</li><li class="line" prefix="5">    location / {
</li><li class="line" prefix="6">        # try to serve file directly, fallback to app.php
</li><li class="line" prefix="7">        try_files $uri /app.php$is_args$args;
</li><li class="line" prefix="8">    }
</li><li class="line" prefix="9">
</li><li class="line" prefix="10">    location ~ ^/app\.php(/|$) {
</li><li class="line" prefix="11">        fastcgi_pass unix:/var/run/php5-fpm.sock;
</li><li class="line" prefix="12">        fastcgi_split_path_info ^(.+\.php)(/.*)$;
</li><li class="line" prefix="13">        include fastcgi_params;
</li><li class="line" prefix="14">        fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
</li><li class="line" prefix="15">        # Prevents URIs that include the front controller. This will 404:
</li><li class="line" prefix="16">        # http://domain.tld/app.php/some-path
</li><li class="line" prefix="17">        # Remove the internal directive to allow URIs like this
</li><li class="line" prefix="18">        internal;
</li><li class="line" prefix="19">    }
</li><li class="line" prefix="20">
</li><li class="line" prefix="21">    error_log /var/log/nginx/symfony_error.log;
</li><li class="line" prefix="22">    access_log /var/log/nginx/symfony_access.log;
</li><li class="line" prefix="23">}
</li></ul></code></pre>
<p>Save the file and exit. To apply the changes, restart Nginx.</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo service nginx restart
</li></ul></code></pre>
<h3 id="configuration-steps-for-apache-php5-web-server">Configuration Steps for Apache + PHP5 Web Server</h3>

<p>Let's start by editing the default <code>php.ini</code> file to define the server's timezone. This is a requirement for running Symfony applications, and it's usually commented out on fresh server installations.</p>

<p>Open the file <code>/etc/php5/apache2/php.ini</code>:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo nano /etc/php5/apache2/php.ini 
</li></ul></code></pre>
<p>Search for the line containing <code>date.timezone</code>. Uncomment the directive by removing the <code>;</code> sign at the beginning of the line, and add the appropriate timezone for your application. In this example we'll use <code>Europe/Amsterdam</code>, but you can choose any <a href="http://php.net/manual/en/timezones.php">supported timezone</a>.</p>
<div class="code-label " title="modified /etc/php5/fpm/php.ini">modified /etc/php5/fpm/php.ini</div><pre class="code-pre "><code langs="">[Date]
; Defines the default timezone used by the date functions
; http://php.net/date.timezone
date.timezone = <span class="highlight">Europe/Amsterdam</span>
</code></pre>
<p>Save the file and exit. Now we need to replace the default website config file with a custom one, tailored for serving a Symfony application. Create a backup of the current default website config.</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">cd /etc/apache2/sites-available
</li><li class="line" prefix="$">sudo mv 000-default.conf default-bkp.conf
</li></ul></code></pre>
<p>Create a new file to replace the old one.</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo nano /etc/apache2/sites-available/000-default.conf
</li></ul></code></pre>
<p>Paste the following content in the file.</p>
<div class="code-label " title="/etc/apache2/sites-available/000-default.conf">/etc/apache2/sites-available/000-default.conf</div><pre class="code-pre "><code class="code-highlight language-apache">
<VirtualHost *:80>

    DocumentRoot /var/www/<span class="highlight">todo-symfony</span>/web
    <Directory /var/www/<span class="highlight">todo-symfony</span>/web>
        AllowOverride None
        Order Allow,Deny
        Allow from All

        <IfModule mod_rewrite.c>
            Options -MultiViews
            RewriteEngine On
            RewriteCond %{REQUEST_FILENAME} !-f
            RewriteRule ^(.*)$ app.php [QSA,L]
        </IfModule>
    </Directory>

    # uncomment the following lines if you install assets as symlinks
    # or run into problems when compiling LESS/Sass/CoffeScript assets
    # <Directory /var/www/project>
    #     Options FollowSymlinks
    # </Directory>

    ErrorLog /var/log/apache2/symfony_error.log
    CustomLog /var/log/apache2/symfony_access.log combined
</VirtualHost>

</code></pre>
<p>If you're using a domain name to access your server instead of just the IP address, you can optionally define the <code>ServerName</code> and <code>ServerAlias</code> values, as shown below. If not, you can omit them.</p>
<div class="code-label " title="/etc/apache2/sites-available/000-default.conf">/etc/apache2/sites-available/000-default.conf</div><pre class="code-pre "><code class="code-highlight language-apache">
<VirtualHost *:80>
    <span class="highlight">ServerName example.com</span>
    <span class="highlight">ServerAlias www.example.com</span>

    DocumentRoot /var/www/todo-symfony/web
. . .
</code></pre>
<p>Save the file and exit. We also need to enable <code>mod_rewrite</code> for Apache.</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo a2enmod rewrite
</li></ul></code></pre>
<p>To apply all the changes, restart Apache.</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo service apache2 restart
</li></ul></code></pre>
<h2 id="step-7-—-accessing-the-application">Step 7 — Accessing the Application</h2>

<p>Your server should be ready to serve the demo Symfony application. Visit <code>http://<span class="highlight">your_server_ip</span></code>  in your browser, and you should see a page like this:</p>

<p><img src="https://assets.digitalocean.com/articles/symfony_1404/todo-symfony.png" alt="Symfony To-Do App Preview" /></p>

<p>You can use the form to create new tasks and test the application’s functionality.</p>

<h2 id="conclusion">Conclusion</h2>

<p>Deploying any application to production requires a special attention to details, such as creating a dedicated database user with limited access and setting the right directory permissions on the application folder. These steps are necessary for increasing server and application security on production environments. In this tutorial, we saw the specific steps that should be taken in order to manually deploy a basic Symfony application to production on a Ubuntu 14.04 server.</p>

    