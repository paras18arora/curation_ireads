<link rel='stylesheet' href='digital_ocean.css'><br> <br> 
      <h3 id="introduction">Introduction</h3>

<hr />

<p>Kohana comes as a self-contained package, with each copy forming a new base for a new web application, making things quite easy for deployment. </p>

<p>In this IndiaReads article, following our previous ones on installing and getting started with Kohana, we'll see how to prepare a VPS to deploy a Kohana based PHP web application - using Debian 7 / Ubuntu 13 as our host operating system.</p>

<p><strong>Note:</strong> This is the third article in our Kohana series, focused on deploying applications built using the framework. To see the first part and learn about installing it, check out <a href="https://indiareads/community/articles/how-to-install-and-setup-kohana-a-php-web-application-development-framework">Getting Started with Kohana</a>. To see about understanding the framework's modules to build a web application, check out <a href="https://indiareads/community/articles/how-to-build-web-applications-with-hmvc-php5-framework-kohana">Building Web Applications with HMVC PHP5 Framework Kohana</a>.</p>

<h2 id="glossary">Glossary</h2>

<hr />

<h3 id="1-php-based-web-application-deployment">1. PHP Based Web-Application Deployment</h3>

<hr />

<h3 id="2-web-servers">2. Web Servers</h3>

<hr />
<pre class="code-pre "><code langs="">1. Nginx HTTP Server and Reverse-Proxy
2. Lighttpd
3. Apache
</code></pre>
<h3 id="3-php-processors">3. PHP Processors</h3>

<hr />
<pre class="code-pre "><code langs="">1. mod_php
2. FastCGI
3. PHP-FPM
</code></pre>
<h3 id="4-kohana-in-brief">4. Kohana in Brief</h3>

<hr />

<h3 id="5-about-our-deployment">5. About Our Deployment</h3>

<hr />

<h3 id="6-preparing-the-system-for-kohana-application-deployment">6. Preparing the System For Kohana Application Deployment</h3>

<hr />
<pre class="code-pre "><code langs="">1. Updating the System
2. Installing Nginx
3. Installing MySQL 5
4. Installing PHP (PHP-FPM)
</code></pre>
<h3 id="7-configuring-the-system">7. Configuring the System</h3>

<hr />
<pre class="code-pre "><code langs="">1. Configuring PHP
2. Configuring Nginx
</code></pre>
<h3 id="8-deploying-a-kohana-web-application">8. Deploying A Kohana Web-Application</h3>

<hr />
<pre class="code-pre "><code langs="">1. Uploading the Code Base To The Server
2. Bootstrapping the Deployment (Installation)
</code></pre>
<h2 id="php-based-web-application-deployment">PHP Based Web-Application Deployment</h2>

<hr />

<p>There are a few different methods to deploy a PHP based web-application, with many more sub-configuration options being available.</p>

<p>The major factor and differentiator is the choice of web server. Some of the most popular ones are:</p>

<ul>
<li><p><strong>Nginx</strong> HTTP Server and Reverse-Proxy</p></li>
<li><p><strong>Lighttpd</strong> (or lighty)</p></li>
<li><p><strong>Apache</strong></p></li>
</ul>

<p>There are also several different PHP processors that can be used to have the above web servers <em>process</em> and <em>serve</em> PHP files:</p>

<ul>
<li><p><strong>mod_php</strong></p></li>
<li><p><strong>FastCGI</strong></p></li>
<li><p><strong>PHP-FPM</strong></p></li>
</ul>

<h2 id="web-servers">Web Servers</h2>

<hr />

<h3 id="nginx-http-server-and-reverse-proxy">Nginx HTTP Server and Reverse-Proxy</h3>

<hr />

<p>Nginx is a very high performant web server / (reverse)-proxy. It has reached its popularity due to being light weight, relatively easy to work with, and easy to extend (with add-ons / plug-ins). Thanks to its architecture, it is capable of handling <em>a lot</em> of requests (virtually unlimited), which - depending on your application or website load - could be really hard to tackle using some other, older alternatives. It can be considered <em>the</em> tool to choose for serving static files such as images, scripts or style-sheets.</p>

<h3 id="lighttpd">Lighttpd</h3>

<hr />

<p>Lighttpd is a very speedy web server that is licensed under the permissive <strong>BSD License</strong>. It works and operates in a way that is closer to Nginx than Apache. The way it handles requests is very low on memory and CPU foot print.</p>

<h3 id="apache">Apache</h3>

<hr />

<p>Apache is a long tried and tested, extremely powerful web server. Although it might not have its old popularity, Apache still offers many things that its new competitors do not. It also comes with a lot of modules that can be used to expand the default functionality and have Apache suit your specific deployment needs.</p>

<h2 id="php-processors">PHP Processors</h2>

<hr />

<p>Web servers (mostly) are not set out to process PHP scripts - nor others based on different programming languages. To do this, they depend on external libraries, each operating in a similar-looking but in actuality very different ways. Different web servers offer different level of integrations with each - and it is highly recommended that as a person responsible of deploying an application, you perform a thorough research to have a better idea of what they do and how they do it.</p>

<h3 id="mod_php">mod_php</h3>

<hr />

<p>For a long time, mod_php remained the most popular Apache module and the way-to-go choice for deploying PHP web applications. It works by embedding the PHP processor inside Apache to run PHP scripts.</p>

<p><b>Advantages:</b></p>

<ul>
<li><p>Extremely stable and well tested.</p></li>
<li><p>No external dependencies are involved for processing.</p></li>
<li><p>Extremely performant.</p></li>
<li><p>Loads <code>php.ini</code> once.</p></li>
<li><p>Supports <code>.htaccess</code> configurations.</p></li>
</ul>

<h3 id="fastcgi">FastCGI</h3>

<hr />

<p>FastCGI works by connecting the external PHP processor installation with the web server through sockets. It is a more advanced way of doing the same thing with <em>ye old</em> CGI. FastCGI can be considered more secure than working with <em>mod_php</em> as it separates the processor from the web server <em>process</em> (and isolating each from possible harmful exploits).</p>

<p><b>Advantages:</b></p>

<ul>
<li><p>Eliminates the need to involve PHP processor for static content.</p></li>
<li><p>Eliminates the memory overhead of using a PHP processor per Apache process.</p></li>
<li><p>Brings in an additional security layer by dividing the server and the processor.</p></li>
</ul>

<h3 id="php-fpm">PHP-FPM</h3>

<hr />

<p>PHP-FPM consists of an upgrade to the FastCGI way of using PHP. It brings certain new features and a whole new way of handling requests - for the benefit of (especially) larger web sites.</p>

<p><b>Advantages:</b></p>

<ul>
<li><p>Adaptive process spawning.</p></li>
<li><p>Graceful processor management.</p></li>
<li><p>Smaller memory footprint compared to FastCGI.</p></li>
<li><p>More configurable than FastCGI.</p></li>
</ul>

<h2 id="kohana-in-brief">Kohana in Brief</h2>

<hr />

<p>Kohana is an HMVC (Hierarchical Model View Controller) framework that offers almost all the necessary tools out-of-the-box in order to build a modern web application that can be developed rapidly, deployed and maintained easily.</p>

<p>As a "light" framework, Kohana is made of files that are scattered across carefully structured directories inside a single (application) one - which means each kohana package can be considered as a web application (minus the possible external dependencies).</p>

<p>The way Kohana is built, by design, makes this framework extremely friendly for deployment.</p>

<h2 id="about-our-deployment">About Our Deployment</h2>

<hr />

<p>In this article, we'll be using Nginx coupled with PHP-FPM for deployment because of the features offered - and their popularity. Our database of choice here is MySQL; however, you should opt for and use another (e.g. MariaDB). Do not forget that you will need to migrate your database - see the end section for details.</p>

<h2 id="preparing-the-system-for-kohana-application-deployment">Preparing the System For Kohana Application Deployment</h2>

<hr />

<p>We will be working with a newly instantiated Ubuntu 13 VPS. For various reasons, you are highly advised to do the same and try everything out before performing them on an already active and working server setup.</p>

<h3 id="updating-the-system">Updating the System</h3>

<hr />

<p>We will begin with updating our virtual server's default application toolset to their latest available.</p>

<p>Run the following to perform this task:</p>
<pre class="code-pre "><code langs="">aptitude    update
aptitude -y upgrade
</code></pre>
<h3 id="installing-nginx">Installing Nginx</h3>

<hr />

<p>Execute the following to install Nginx using the package manager <code>aptitude</code>:</p>
<pre class="code-pre "><code langs="">aptitude -y install nginx
</code></pre>
<p>Run the below command to start the server:</p>
<pre class="code-pre "><code langs="">service nginx start
</code></pre>
<p>You can visit <code>http://[your droplet's IP adde.]/</code> to test the Nginx installation. You should see the default welcome screen.</p>

<h3 id="installing-mysql-5">Installing MySQL 5</h3>

<hr />

<p>We will again be using the <code>aptitude</code> package manager to install MySQL and the other applications. Run the below command to download and install MySQL.</p>

<p><strong>Remember:</strong> During the installation process you will be prompted with a couple of questions regarding the <em>root password</em>.</p>
<pre class="code-pre "><code langs="">aptitude -y install mysql-server mysql-client
</code></pre>
<p>Bootstrap everything using <code>mysql_secure_installation</code>:</p>
<pre class="code-pre "><code langs=""># Run the following to start the process
mysql_secure_installation

# Enter the root password you have chosen during installation    
# Continue with answering the questions, confirming all.
# Ex.:
# Remove anonymous users? [Y/n] Y
# Disallow root login remotely? [Y/n] Y
# ..
# .     
</code></pre>
<p>Run the below command to restart and check the status of your MySQL installation:</p>
<pre class="code-pre "><code langs="">service mysql restart
# mysql stop/waiting
# mysql start/running, process 25012

service mysql status
# mysql start/running, process 25012
</code></pre>
<h3 id="installing-php-php-fpm">Installing PHP (PHP-FPM)</h3>

<hr />

<p>To process PHP pages with Nginx, we are going to use PHP-FPM. Run the following to download and install the application package:</p>
<pre class="code-pre "><code langs="">aptitude -y install php5 php5-fpm php5-mysql
</code></pre>
<p>This will also install <strong>PHP5 commons</strong>.</p>

<h2 id="configuring-the-system">Configuring the System</h2>

<hr />

<p>After installing the necessary tools, it is time to get our server ready for deploying PHP web application by making the final configuration amendments.</p>

<h3 id="configuring-php">Configuring PHP</h3>

<hr />

<p>We will be working with the default PHP configuration file <code>php.ini</code> and edit it using the text editor <code>nano</code> to make a few small changes.</p>

<p>Run the following to open the file using nano:</p>
<pre class="code-pre "><code langs="">nano /etc/php5/fpm/php.ini
</code></pre>
<p>Scroll down the document and find the line <code>;cgi.fix_pathinfo=1</code>. Modify it similar to the following to have application paths processed securely:</p>
<pre class="code-pre "><code langs=""># Replace ;cgi.fix_pathinfo=1 with:
cgi.fix_pathinfo=0
</code></pre>
<p>Save and exit by pressing CTRL+X and confirm with Y.</p>

<h3 id="configuring-nginx">Configuring Nginx</h3>

<hr />

<p>Since in this article we are looking at deploying a single web application, our modification and configuration choices are made accordingly. In order to host more than one application using Nginx, you will need to make user of <em>server blocks</em>. To learn more about them, check out <a href="https://indiareads/community/articles/how-to-set-up-nginx-virtual-hosts-server-blocks-on-ubuntu-12-04-lts--3">Setting up Nginx Virtual Hosts (Server Blocks)</a>.</p>

<p>Let's edit the default Nginx website configuration. Run the following to open up the file using nano:</p>
<pre class="code-pre "><code langs="">nano /etc/nginx/sites-available/default
</code></pre>
<p>Copy and paste the below content:</p>
<pre class="code-pre "><code langs="">server {

    listen 80 default_server;
    listen [::]:80 default_server ipv6only=on;

    # Set the default application path root
    # We are using my_app to host the application
    root /var/www/my_app;
    index index.php;

    # Replace the server_name with your own
    server_name localhost;

    location /
    {
        try_files $uri /index.php?$args;

        if (!-e $request_filename)
        {
            rewrite ^/(.*)$ /index.php/$1 last;
        }
    }

    location ~ /\.
    {
        deny  all;
    }

    location ~ \.php$
    {
        try_files $uri = 404;
        fastcgi_pass unix:/var/run/php5-fpm.sock;
        fastcgi_index index.php;
        fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
        include fastcgi_params;
    }

}
</code></pre>
<p>Save and exit by pressing CTRL+X and confirm with Y.</p>

<p>Create and enter the application deployment directory <code>/var/www</code>:</p>
<pre class="code-pre "><code langs="">mkdir /var/www
cd /var/www
</code></pre>
<p>Test the configurations and restart Nginx for the chances to take effect:</p>
<pre class="code-pre "><code langs=""># Test the configurations
nginx -t

# Restart the server
service nginx restart
</code></pre>
<h2 id="deploying-a-kohana-web-application">Deploying A Kohana Web-Application</h2>

<hr />

<p>Following the configurations made, we are ready to deploy our application. The process will consist of two main steps:</p>

<ol>
<li><p>Uploading the code base to the server</p></li>
<li><p>Modifying <code>bootstrap.php</code> to ensure it is set correctly.</p></li>
</ol>

<h3 id="uploading-the-code-base-to-the-server">Uploading the Code Base To The Server</h3>

<hr />

<p>You can use SFTP or a graphical tool, such as FileZilla, to transfer and manage remote files securely.</p>

<p>To learn about working with SFTP, check out the article: <a href="https://indiareads/community/articles/how-to-use-sftp-to-securely-transfer-files-with-a-remote-server">How To Use SFTP</a>.</p>

<p>To learn about FileZilla, check out the article on the subject: <a href="https://indiareads/community/articles/how-to-use-filezilla-to-transfer-and-manage-files-securely-on-your-vps">How To Use FileZilla</a>. </p>

<p><strong>Note:</strong> If you choose to use FileZilla, make sure to set <code>/var/www</code> as the default directory (this should save you some time).</p>

<p>In this example, we will work with the default (sample) Kohana application.</p>

<p>Download and prepare Kohana:</p>
<pre class="code-pre "><code langs=""># Remember: The following commands assume that your current
#           working directory is set as /var/www/

wget https://github.com/kohana/kohana/releases/download/v3.3.1/kohana-v3.3.1.zip

# You might need to install *unzip* before extracting the files    
aptitude install -y unzip 

# Unzip and extract the files
unzip kohana-v3.3.1.zip -d my_app

# Remove the zip package
rm -v kohana-v3.3.1.zip

# Enter the application directory
cd my_app
</code></pre>
<h3 id="bootstrapping-the-deployment-installation">Bootstrapping the Deployment (Installation)</h3>

<hr />

<p>Run the following to start editing the configuration file using nano: </p>
<pre class="code-pre "><code langs="">nano application/bootstrap.php
</code></pre>
<p>Edit your timezone:</p>
<pre class="code-pre "><code langs=""># Find date_default_timezone_set and set your timezone
date_default_timezone_set('Europe/London');
</code></pre>
<p>Set your locale:</p>
<pre class="code-pre "><code langs=""># Find setlocale and set your locale
setlocale(LC_ALL, 'en_UK.utf-8');
</code></pre>
<p>Find end edit the <code>base_url</code> to match the installation</p>
<pre class="code-pre "><code langs=""># Find base_url and set the base application directory
# Relative to the base Apache directory (i.e. /var/www/my_app)

Kohana::init(array(
    'base_url' => '/',
));
</code></pre>
<p>Make sure to enable all necessary modules:</p>
<pre class="code-pre "><code langs=""># Find Kohana::modules and uncomment them

Kohana::modules(array(
    'auth'       => MODPATH.'auth',       // Basic authentication
    'cache'      => MODPATH.'cache',      // Caching with multiple backends
    'codebench'  => MODPATH.'codebench',  // Benchmarking tool
    'database'   => MODPATH.'database',   // Database access
    'image'      => MODPATH.'image',      // Image manipulation
    'orm'        => MODPATH.'orm',        // Object Relationship Mapping
    'oauth'      => MODPATH.'oauth',      // OAuth authentication
    'pagination' => MODPATH.'pagination', // Paging of results
    'unittest'   => MODPATH.'unittest',   // Unit testing
    'userguide'  => MODPATH.'userguide',  // User guide and API documentation
));
</code></pre>
<p>Save and exit by pressing CTRL+X and confirm with Y.</p>

<p>Set cache and log directories writable:</p>
<pre class="code-pre "><code langs="">sudo chmod -R a+rwx application/cache
sudo chmod -R a+rwx application/logs
</code></pre>
<p>And that's it! Now you should have your Kohana web application ready to run.</p>

<p><strong>Remember:</strong> You should not forget about migrating your application data (e.g. MySQL server database) to your droplet from your development machine or another server. To learn about doing this, check out <a href="https://indiareads/community/articles/how-to-migrate-a-mysql-database-between-two-servers">How To Migrate a MySQL Database Between Two Servers</a> and related comments for further information.</p>

<div class="author">Submitted by: <a href="https://twitter.com/ostezer">O.S. Tezer</a></div>

    