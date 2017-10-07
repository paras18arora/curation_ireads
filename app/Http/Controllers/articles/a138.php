<link rel='stylesheet' href='digital_ocean.css'><br> <br> 
      <div><h3>About Bolt</h3>

<p><a href="http://bolt.cm/" target="_blank">Bolt</a> is an open source Content Management System (CMS) built in PHP that uses modern markup and libraries for outputting its pages. It is easy to configure and use and its target users are content editors, frontend designers and backend developers.</p> 

<p>In this tutorial, we will see how to install Bolt on a VPS running Ubuntu 12.04 with the LAMP stack (Linux, Apache, MySQL and PHP) installed. If you need help setting up LAMP, check out this <a href="https://indiareads/community/articles/how-to-install-linux-apache-mysql-php-lamp-stack-on-ubuntu" target="_blank">tutorial</a>.</p> 

<p>To work properly, Bolt requires PHP 5.3.2 or higher, a MySQL, SQLite or PostgreSQL database, and Apache or Nginx as a web server (these are the only two currently supported web servers Bolt can run on at the moment). There are also a couple of other settings and extensions that we will see during the installation and configuration phases.</p>

<h2>Installation</h2>

<p>There are a few ways you can install Bolt; however, since we have access to our server through the command line,  this will be the utilized method. First, create a folder where you’d like Bolt installed and navigate in it:</p>

<pre>cd /var/www
mkdir bolt
cd bolt</pre>

<p>Now download the latest distribution of Bolt:</p>

<pre>wget <a href="http://bolt.cm/distribution/bolt_latest.tgz" target="_blank">http://bolt.cm/distribution/<WBR />bolt_latest.tgz</a></pre>

<p>Then use the following command to untar the downloaded archive file:</p>

<pre>tar -xzf bolt_latest.tgz</pre>

<p>You can then go ahead and delete the archive file:</p>

<pre>rm bolt_latest.tgz</pre>

<p>Finally, you’ll need to set some permissions to some of the folders. Run the following command to take care of all of them in one big swoop:</p>

<pre>chmod -R 777 files/ app/database/ app/cache/ app/config/ theme/</pre>

<h2>Configuration</h2>

<p>By default, Bolt is set up to use an SQLite database. Let’s see how we can change this and have it use MySQL. First, create a database-- quickly jump into your MySQL command line and run the following command:</p>

<pre>create database bolt;</pre>

<p>For more information about using MySQL from your command line you can read <a href="https://indiareads/community/articles/a-basic-mysql-tutorial" target="_blank">this tutorial</a>.</p> 

<p>Now that we have our database (<strong>bolt</strong>), we need to configure our Bolt to use it. But right before that, navigate in your browser to where you installed the Bolt files: <strong>your-ip/bolt</strong>. Depending on whether or not you have the sqlite pdo extension, you should get an error.</p> 

<p><strong>Note:</strong> You have to make this browser request so that the configuration files get renamed properly. Now, we can edit the config file to specify our database and credentials:</p>

<pre>nano app/config/config.yml</pre>

<p>And replace the SQLite configuration with this one:</p>

<pre>database:
  driver: mysql
  username: your username
  password: your password
  databasename: bolt</pre>

<p>Save the file and exit. Now if you refresh the page in the browser, you should be directed to a page to set up your first user account. If you get an Apache error (404) it means that your .htaccess file directives are not overriding the Apache instructions and/or <strong>mod_rewrite</strong> is not enabled. So let’s quickly take care of that.</p>

<p>First, to check if <strong>mod_rewrite</strong> is already enabled, use the following command:</p>

<pre>apache2ctl -M</pre>

<p>If you see "rewrite_module" in the list, you are fine. If not, use the following command to enable the module:</p>

<pre>a2enmod rewrite</pre>

<p>Then edit the Apache default virtual host file and make sure that <strong>Allow Overrides</strong> is set to <strong>All</strong> under the <strong>/var/www</strong> directory. Edit the file with the following command:</p>

<pre>nano /etc/apache2/sites-available/<WBR />default</pre>

<p>And where you see this block, make the changes to correspond to the following:</p>

<pre>Options Indexes FollowSymLinks MultiViews
AllowOverride All
Order allow,deny
allow from all</pre>

<p>This will make sure that .htaccess files can override the default Apache instructions.</p> 

<p>In order for any of these two changes to take effect, you’ll need to restart your Apache server. But for the sake of efficiency, let’s also install a couple of required PHP extensions that Bolt makes use of prior to restarting. Run the following commands for this:</p>

<pre>sudo apt-get update
sudo apt-get install php5-gd
sudo apt-get install php5-curl</pre>

<p>And now we can restart Apache:</p>

<pre>sudo service apache2 restart</pre>


<h2>Accessing Bolt</h2>

<p>If you refresh your browser again, you should be able to see the form for creating the first user account. You can go ahead and do so and then login with that information.</p>

<p>To access the site's home page, you have to go directly to its folder in the browser (<strong>your-ip/bolt</strong>) as that's where it resides. Some of the sample links may not work, as there hasn't been any content created to fill them.</p>

<p>If you were following this tutorial, you should be able to access the <b>bolt dashboard</b> at <strong>your-ip/bolt/bolt</strong>.</p>

<p>But what if you want the site to be available directly at your IP address which is set to point to your web server's root folder (<em>/var/www</em>)? You have three choices, One: you can move all the Bolt related files to this folder; Two: you can change the webserver's root directory to point to the <em>/var/www/bolt</em> folder instead of the default one; or Three: you can create a new virtual host with a specific domain name with that directory as its document root. The choice depends on your setup.</p> 

<p>If you want to create a virtual host for a domain name, follow the instructions in this <a href="https://indiareads/community/articles/how-to-set-up-apache-virtual-hosts-on-ubuntu-12-04-lts" target="_blank">tutorial</a>. But if you want to quickly change the default web server document root, open up again the file you edited before to allow the .htaccess overrides:</p>

<pre>nano /etc/apache2/sites-available/<WBR />default</pre>

<p>And change this line from this:</p>

<pre>DocumentRoot /var/www/</pre>

<p>To this:</p>

<pre>DocumentRoot /var/www/bolt/</pre>

<p>Restart Apache:</p>

<pre>sudo service apache2 restart</pre>

<p>And you'll see that if you point your browser to the the IP, it should bring up your Bolt site directly.</p>

<p>Once these changes are in place, you can access the dashboard at <strong>your-ip/bolt</strong>.</p>

<p>Good luck building your site with Bolt.</p></div>
    