<link rel='stylesheet' href='digital_ocean.css'><br> <br> 
      <h3 id="introduction">Introduction</h3>

<p>Nginx, MySQL, and PHP can be combined together easily as a powerful solution for serving dynamic content on the web.  These three pieces of software can be installed and configured on a FreeBSD machine to create what is known as a <strong>FEMP stack</strong>.</p>

<p>In this guide, we will demonstrate how to install a FEMP stack on a FreeBSD 10.1 server.  We will be installing the software using packages in order to get up and running more quickly.  These packages provide reasonable defaults that work well for most servers.</p>

<h2 id="install-the-components">Install the Components</h2>

<p>To begin, we will install all of the software we need using FreeBSD packages system.  The "install" command will update our local copy of the available packages and then install the packages we have requested:</p>
<pre class="code-pre "><code langs="">sudo pkg install nginx mysql56-server php56 php56-mysql
</code></pre>
<p>This will download and install an Nginx web server to serve our content, a MySQL database server used to store information, and the PHP processing language to process dynamic content.</p>

<p>Once the installation is complete, make sure to run the <code>rehash</code> command if you are running the default <code>tcsh</code> shell.  This makes the shell aware of the new applications you installed:</p>
<pre class="code-pre "><code langs="">rehash
</code></pre>
<p>When you are finished, you can move on to begin enabling and configuring your components.</p>

<h2 id="enable-all-of-the-services">Enable All of the Services</h2>

<p>In the last section, we downloaded three separate services that will need to run on our server.</p>

<p>In order for FreeBSD to start these as conventional services, we need to tell FreeBSD that we want to enable them.  This will allow us to handle them as services instead of one-time applications and it will also configure FreeBSD to automatically start them at boot.</p>

<p>First, we need to know the correct rc parameter to set for each service.  The service scripts, which are located in the <code>/usr/local/etc/rc.d</code> directory, define the parameter that should be used to enable each server using the <code>rcvar</code> variable.  We can see what each service's <code>rcvar</code> is set to by typing:</p>
<pre class="code-pre "><code langs="">grep rcvar /usr/local/etc/rc.d/*
</code></pre>
<p>You should get a listing like this:</p>
<pre class="code-pre "><code langs="">/usr/local/etc/rc.d/avahi-daemon:rcvar=avahi_daemon_enable
/usr/local/etc/rc.d/avahi-dnsconfd:rcvar=avahi_dnsconfd_enable
/usr/local/etc/rc.d/dbus:rcvar=dbus_enable
/usr/local/etc/rc.d/mysql-server:<span class="highlight">rcvar=mysql_enable</span>
/usr/local/etc/rc.d/nginx:rcvar=<span class="highlight">nginx_enable</span>
/usr/local/etc/rc.d/php-fpm:rcvar=<span class="highlight">php_fpm_enable</span>
/usr/local/etc/rc.d/rsyncd:rcvar=rsyncd_enable
</code></pre>
<p>As you can see, this allows us to easily output the parameter that we need to set for each of our services.  The name of the script itself (the last component of the path until the colon character) is also notable as it tells us the actual name that FreeBSD uses for the service.</p>

<p>To enable these services, we will edit the <code>/etc/rc.conf</code> file with sudo privileges:</p>
<pre class="code-pre "><code langs="">sudo vi /etc/rc.conf
</code></pre>
<p>Inside, we will add a line for each of the services that we wish to start.  We can use the <code>rcvar</code> parameter we discovered for each service and set it to "YES" to enable each one:</p>
<pre class="code-pre "><code langs="">mysql_enable="YES"
nginx_enable="YES"
php_fpm_enable="YES"
</code></pre>
<p>Save and close the file when you are finished.</p>

<h2 id="configure-php">Configure PHP</h2>

<p>Next, we will configure our PHP-FPM service, which will be responsible for processing PHP requests sent from our web server.</p>

<p>To start, change to the <code>/usr/local/etc</code> directory, where configuration files for our optional programs are stored:</p>
<pre class="code-pre "><code langs="">cd /usr/local/etc
</code></pre>
<p>There are a number of PHP configuration files in this directory that we will want to modify.  We will start with the PHP-FPM configuration file itself.  Open this with <code>sudo</code> privileges:</p>
<pre class="code-pre "><code langs="">sudo vi php-fpm.conf
</code></pre>
<p>Inside, we want to adjust a few different options.  First, we want to configure PHP-FPM to use a Unix socket instead of a network port for communication.  This is more secure for services communicating within a single server.</p>

<p>Find the line that looks like this:</p>
<pre class="code-pre "><code langs="">listen = 127.0.0.1:9000
</code></pre>
<p>Change this to use a socket within the <code>/var/run</code> directory:</p>
<pre class="code-pre "><code langs="">listen = <span class="highlight">/var/run/php-fpm.sock</span>
</code></pre>
<p>Next, we will configure the owner, group, and permissions set of the socket that will be created.  There is a commented-out group of options that handle this configuration that looks like this:</p>
<pre class="code-pre "><code langs="">;listen.owner = www
;listen.group = www
;listen.mode = 0660
</code></pre>
<p>Enable these by removing the comment marker at the beginning:</p>
<pre class="code-pre "><code langs="">listen.owner = www
listen.group = www
listen.mode = 0660
</code></pre>
<p>Save and close the file when you are finished.</p>

<p>Next, we need to create a <code>php.ini</code> file that will configure the general behavior of PHP.  Two sample files were included that we can choose to copy to the <code>php.ini</code> file that PHP reads.</p>

<p>The <code>php.ini-production</code> file will be closer to what we need, so we will use that one.  Copy the production version over to the file PHP checks for:</p>
<pre class="code-pre "><code langs="">sudo cp php.ini-production php.ini
</code></pre>
<p>Open the file for editing with <code>sudo</code> privileges:</p>
<pre class="code-pre "><code langs="">sudo vi php.ini
</code></pre>
<p>Inside, we need to find a section that configures the <code>cgi.fix_pathinfo</code> behavior.  It will be commented out and set to "1" by default.  We need to uncomment this and set it to "0".  This will prevent PHP from trying to execute parts of the path if the file that was passed in to process is not found.  This could be used by malicious users to execute arbitrary code if we do not prevent this behavior.</p>

<p>Uncomment the <code>cig.fix_pathinfo</code> line and set it to "0":</p>
<pre class="code-pre "><code langs="">cgi.fix_pathinfo=0
</code></pre>
<p>Save and close the file when you are finished.</p>

<p>Now that we have PHP-FPM completely configured, we can start the service by typing:</p>
<pre class="code-pre "><code langs="">sudo service php-fpm start
</code></pre>
<p>We can now move on to configuring our MySQL instance.</p>

<h2 id="configure-mysql">Configure MySQL</h2>

<p>To get started configuring MySQL, we need to start the MySQL service:</p>
<pre class="code-pre "><code langs="">sudo service mysql-server start
</code></pre>
<p>The first time you run this command, it will create the required directory structure in the filesystem and install the database files it needs.  It will then start the MySQL server process.</p>

<p>After the service is started, we need to secure the installation.  This can be accomplished through a script called <code>mysql_secure_installation</code>.  Run this with <code>sudo</code> privileges to lock down some insecure defaults:</p>
<pre class="code-pre "><code langs="">sudo mysql_secure_installation
</code></pre><pre class="code-pre "><code langs="">. . .

Enter current password for root (enter for none):
</code></pre>
<p>The script will start by asking you for the current password for the MySQL root account.  Since we have not set a password for this user yet, we can press "ENTER" to bypass this prompt.</p>
<pre class="code-pre "><code langs="">Set root password? [Y/n]
</code></pre>
<p>Next, it will ask you if you would like to set the MySQL root account's password.  Press "ENTER" to accept this suggestion.  Choose and confirm an administrative password.</p>

<p>The script will then proceed with additional suggestions that will help reverse some insecure conditions in the default MySQL installation.  Simply press "ENTER" through all of these prompts to complete all of the suggested actions.</p>

<p>We can restart the MySQL service to ensure that our instance immediately implements the security changes:</p>
<pre class="code-pre "><code langs="">sudo service mysql-server restart
</code></pre>
<p>Our MySQL instance is now up and running how we want it, so we can move on.</p>

<h2 id="configure-nginx">Configure Nginx</h2>

<p>Our next task is to set up Nginx.  To get started, we need to start the web server:</p>
<pre class="code-pre "><code langs="">sudo service nginx start
</code></pre>
<p>Now, we can begin configuring Nginx by going to the <code>nginx</code> directory in the <code>/usr/local/etc</code> directory:</p>
<pre class="code-pre "><code langs="">cd /usr/local/etc/nginx
</code></pre>
<p>Here, we need to open the main Nginx configuration file with <code>sudo</code> privileges:</p>
<pre class="code-pre "><code langs="">sudo vi nginx.conf
</code></pre>
<p>Inside, we can begin to make changes so that our Nginx instance can work with our other components.</p>

<p>To start, uncomment and modify the <code>user</code> directive at the top of the file.  We need the web server to operate as the <code>www</code> user, since that is what our PHP-FPM instance is looking for:</p>
<pre class="code-pre "><code class="code-highlight language-nginx">user www;
</code></pre>
<p>We should also set the <code>worker_processes</code> to the number of CPUs or cores that your system has. (To find out how many CPUs your server has, type <code>sysctl hw.ncpu</code> from the command line):</p>
<pre class="code-pre "><code class="code-highlight language-nginx">worker_processes <span class="highlight">2</span>;
</code></pre>
<p>Next, we will set the error verbosity and location using the <code>error_log</code> directive.  We will log to a location at <code>/var/log/nginx/error.log</code> at the <code>info</code> log level:</p>
<pre class="code-pre "><code class="code-highlight language-nginx">error_log <span class="highlight">/var/log/nginx/error.log info</span>;
</code></pre>
<p>In the <code>http</code> block, we will also set up an access log.  This will be located at <code>/var/log/nginx/access.log</code>:</p>
<pre class="code-pre "><code class="code-highlight language-nginx">access_log /var/log/nginx/access.log;
</code></pre>
<p>In the <code>server</code> block, we need to modify the <code>server_name</code> directive to use the domain name or IP address of our server.  We can make our server respond to the <code>www</code> hostname as well by adding that after the main domain:</p>
<pre class="code-pre "><code class="code-highlight language-nginx">server {
    listen          80;
    server_name     <span class="highlight">example.com</span> www.<span class="highlight">example.com</span>;

    . . .
</code></pre>
<p>Configure the <code>root</code> and <code>index</code> directives in the main <code>server</code> block.  Our document root will be <code>/usr/local/www/nginx</code> and our index directive should attempt to serve <code>index.php</code> files before falling back on <code>index.html</code> or <code>index.htm</code> files.</p>

<p>Since we defined these directives within the <code>server</code> context, we do not need them within the <code>location /</code> block.  In this block, we will instead configure a <code>try_files</code> directive to try to serve user requests as a file and then a directory before falling back with a 404 error:</p>
<pre class="code-pre "><code class="code-highlight language-nginx">server {

    . . .

    <span class="highlight">root /usr/local/www/nginx;</span>
    <span class="highlight">index index.php index.html index.htm;</span>

    location / {
        <span class="highlight">try_files $uri $uri/ =404;</span>
    }

    . . .
</code></pre>
<p>Finally, we need to configure a location block that will handle PHP files.  This block will match any request ending in <code>.php</code>.  It will only process the files themselves, throwing back a 404 error if the file cannot be found. </p>

<p>We will use the socket we configured in the <code>php-fpm.conf</code> file earlier.  We will configure some other FastCGI proxying options as well, partly by reading in parameters from the <code>fastcgi_params</code> file.  We need to explicitly set the <code>SCRIPT_FILENAME</code> parameter so that PHP knows what files to execute:</p>
<pre class="code-pre "><code class="code-highlight language-nginx">server {

    . . .

    location ~ \.php$ {
        try_files $uri =404;
        fastcgi_split_path_info ^(.+\.php)(/.+)$;
        fastcgi_pass unix:/var/run/php-fpm.sock;
        fastcgi_index index.php;
        fastcgi_param SCRIPT_FILENAME $request_filename;
        include fastcgi_params;
    }
</code></pre>
<p>All together, with comments removed, the file should look something like this:</p>
<pre class="code-pre "><code class="code-highlight language-nginx"><span class="highlight">user  www;</span>
worker_processes  <span class="highlight">2</span>;
<span class="highlight">error_log /var/log/nginx/error.log info;</span>

events {
    worker_connections  1024;
}

http {
    include       mime.types;
    default_type  application/octet-stream;

    <span class="highlight">access_log /var/log/nginx/access.log;</span>

    sendfile        on;
    keepalive_timeout  65;

    server {
        listen       80;
        server_name  <span class="highlight">example.com</span> www.<span class="highlight">example.com</span>;
        <span class="highlight">root /usr/local/www/nginx;</span>
        <span class="highlight">index index.php index.html index.htm;</span>

        location / {
            <span class="highlight">try_files $uri $uri/ =404;</span>
        }

        error_page      500 502 503 504  /50x.html;
        location = /50x.html {
            root /usr/local/www/nginx-dist;
        }

        <span class="highlight">location ~ \.php$ {</span>
                <span class="highlight">try_files $uri =404;</span>
                <span class="highlight">fastcgi_split_path_info ^(.+\.php)(/.+)$;</span>
                <span class="highlight">fastcgi_pass unix:/var/run/php-fpm.sock;</span>
                <span class="highlight">fastcgi_index index.php;</span>
                <span class="highlight">fastcgi_param SCRIPT_FILENAME $request_filename;</span>
                <span class="highlight">include fastcgi_params;</span>
        <span class="highlight">}</span>
    }
}
</code></pre>
<p>Save and close the file when you are finished.</p>

<p>We now need to create the log directory and files that we referenced in our file.  First, create the <code>/var/log/nginx</code> directory:</p>
<pre class="code-pre "><code langs="">sudo mkdir -p /var/log/nginx
</code></pre>
<p>Next, we can create the empty log files:</p>
<pre class="code-pre "><code langs="">sudo touch /var/log/nginx/access.log
sudo touch /var/log/nginx/error.log
</code></pre>
<p>Now, we are ready to configure our document root.  We have configured our root to be <code>/usr/local/www/nginx</code>, but currently, this is a symbolic link to the <code>/usr/local/www/nginx-dist</code> directory which could be updated by a package operation in the future.</p>

<p>We should destroy the link and create the unlinked directory again:</p>
<pre class="code-pre "><code langs="">sudo rm /usr/local/www/nginx
sudo mkdir /usr/local/www/nginx
</code></pre>
<p>Since we still need to test our web server, we can copy the <code>index.html</code> file into our new web root:</p>
<pre class="code-pre "><code langs="">sudo cp /usr/local/www/nginx-dist/index.html /usr/local/www/nginx
</code></pre>
<p>While we are here, we should also create a temporary <code>info.php</code> file that we can use to test Nginx's ability to pass requests to PHP-FPM.  Create the file within the document root with <code>sudo</code> privileges:</p>
<pre class="code-pre "><code langs="">sudo vi /usr/local/www/nginx/info.php
</code></pre>
<p>In the file, type the following contents.  This will generate an HTML page with information about our PHP configuration:</p>
<pre class="code-pre "><code class="code-highlight language-php"><?php phpinfo(); ?>
</code></pre>
<p>Save and close the file when you are finished.</p>

<p>We are now ready to restart Nginx to take advantage of our new configuration.  First, test your configuration file for syntax errors by typing:</p>
<pre class="code-pre "><code langs="">sudo nginx -t
</code></pre>
<p>If your configuration file has no detectable syntax errors, you should see something that looks like this:</p>
<pre class="code-pre "><code langs="">nginx: the configuration file /usr/local/etc/nginx/nginx.conf syntax is ok
nginx: configuration file /usr/local/etc/nginx/nginx.conf test is successful
</code></pre>
<p>If the above command returns with errors, re-open the Nginx configuration file to the location where the error was found and try to fix the problem.</p>

<p>When your configuration checks out correctly, we can restart Nginx:</p>
<pre class="code-pre "><code langs="">sudo service nginx restart
</code></pre>
<h2 id="testing-the-results">Testing the Results</h2>

<p>Our web stack is now complete.  All that we have left to do is test it out.</p>

<p>In your web browser, begin by going to your base domain name or the server's IP address:</p>
<pre class="code-pre "><code langs="">http://<span class="highlight">example.com</span>
</code></pre>
<p>You should see the contents of the <code>index.html</code> file we copied over.  It will look something like this:</p>

<p><img src="https://assets.digitalocean.com/articles/freebsd_lemp/default_index.png" alt="FreeBSD Nginx default index" /></p>

<p>This indicates that Nginx is up and running and capable of serving simple HTML pages.</p>

<p>Next, we should check out the <code>info.php</code> file we created.  In your browser, visit your domain name or server IP address, followed by <code>/info.php</code>:</p>
<pre class="code-pre "><code langs="">http://<span class="highlight">example.com</span>/info.php
</code></pre>
<p>You should see a generated PHP information page that looks something like this:</p>

<p><img src="https://assets.digitalocean.com/articles/freebsd_lemp/php_info.png" alt="FreeBSD php info page" /></p>

<p>If you can see this page, you have successfully configured a FEMP stack on your FreeBSD server.</p>

<p>After testing your configuration, it is a good idea to remove the <code>info.php</code> file from your document root since it can give away some sensitive information about your installation:</p>
<pre class="code-pre "><code langs="">sudo rm /usr/local/www/nginx/info.php
</code></pre>
<p>You can always recreate this file easily at a later time.</p>

<h2 id="conclusion">Conclusion</h2>

<p>You should now have a fully functional web server powered by Nginx which can process dynamic PHP content and use MySQL to store data.  This configuration can be used as a base for a variety of other configurations and web applications.</p>

    