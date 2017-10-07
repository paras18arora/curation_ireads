<link rel='stylesheet' href='digital_ocean.css'><br><img src=https://community-cdn-digitalocean-com.global.ssl.fastly.net/assets/tutorials/images/large/bacula_web_tw.jpg?1428609857/> <br> 
      <h3 id="introduction">Introduction</h3>

<p>Bacula-web is a PHP web application that provides an easy way to view summaries and graphs of Bacula backup jobs that have already run. Although it doesn't allow you to control Bacula in any way, Bacula-web provides a graphical alternative to viewing jobs from the console. Bacula-web is especially useful for users who are new to Bacula, as its reports make it easy to understand what Bacula has been operating.</p>

<p>In this tutorial, we will show you how to install Bacula-web on an Ubuntu 14.04 server that your Bacula server software is running on.</p>

<h2 id="prerequisites">Prerequisites</h2>

<p>To follow this tutorial, you must have the Bacula backup server software installed on an Ubuntu server. Instructions to install Bacula can be found here: <a href="https://indiareads/community/tutorials/how-to-install-bacula-server-on-ubuntu-14-04">How To Install Bacula Server on Ubuntu 14.04</a>.</p>

<p>This tutorial assumes that your Bacula setup is using MySQL for the catalog. If you are using a different RDBMS, such as PostgreSQL, be sure to make the proper adjustments to this tutorial. You will need to install the appropriate PHP module(s) and make adjustments to the database connection information examples.</p>

<p>Let's get started.</p>

<h2 id="install-nginx-and-php">Install Nginx and PHP</h2>

<p>Bacula-web is a PHP application, so we need to install PHP and a web server. We'll use Nginx. If you want to learn more about this particular software setup, check out this <a href="https://indiareads/community/tutorials/how-to-install-linux-nginx-mysql-php-lemp-stack-on-ubuntu-14-04">LEMP tutorial</a>.</p>

<p>Update your apt-get listings:</p>
<pre class="code-pre "><code langs="">sudo apt-get update
</code></pre>
<p>Then, install Nginx, PHP-fpm, and a few other packages with apt-get:</p>
<pre class="code-pre "><code langs="">sudo apt-get install nginx apache2-utils php5-fpm php5-mysql php5-gd
</code></pre>
<p>Now we are ready to configure PHP and Nginx.</p>

<h3 id="configure-php-fpm">Configure PHP-FPM</h3>

<p>Open the PHP-FPM configuration file in your favorite text editor. We'll use vi:</p>
<pre class="code-pre "><code langs="">sudo vi /etc/php5/fpm/php.ini
</code></pre>
<p>Find the line that specifies <code>cgi.fix_pathinfo</code>, uncomment it, and replace its value with <code>0</code>. It should look like this when you're done.</p>
<pre class="code-pre "><code langs="">cgi.fix_pathinfo=0
</code></pre>
<p>Now find the <code>date.timezone</code> setting, uncomment it, and replace its value with your time zone. We're in New York, so that's what we're setting the value to:</p>
<pre class="code-pre "><code langs="">date.timezone = <span class="highlight">America/New_York</span>
</code></pre>
<p>If you need a list of supported timezones, check out the <a href="http://php.net/manual/en/timezones.php">PHP documentation</a>.</p>

<p>Save and exit.</p>

<p>PHP-FPM is configured properly, so let's restart it to put the changes into effect:</p>
<pre class="code-pre "><code langs="">sudo service php5-fpm restart
</code></pre>
<h3 id="configure-nginx">Configure Nginx</h3>

<p>Now it's time to configure Nginx to serve PHP applications.</p>

<p>First, because we don't want unauthorized people to access Bacula-web, let's create an htpasswd file. Use htpasswd to create an admin user, called "admin" (you should use another name), that can access the Bacula-web interface:</p>
<pre class="code-pre "><code langs="">sudo htpasswd -c /etc/nginx/htpasswd.users <span class="highlight">admin</span>
</code></pre>
<p>Enter a password at the prompt. Remember this login, as you will need it to access Bacula-web.</p>

<p>Now open the Nginx default server block configuration file in a text editor. We'll use vi:</p>
<pre class="code-pre "><code langs="">sudo vi /etc/nginx/sites-available/default
</code></pre>
<p>Replace the contents of the file with the following code block. Be sure to substitute the highlighted value of <code>server_name</code> with your server's domain name or IP address:</p>
<pre class="code-pre "><code langs="">server {
    listen 80 default_server;
    listen [::]:80 default_server ipv6only=on;

    root /usr/share/nginx/html;
    index index.php index.html index.htm;

    server_name <span class="highlight">server_domain_name_or_IP</span>;

    auth_basic "Restricted Access";
    auth_basic_user_file /etc/nginx/htpasswd.users;

    location / {
        try_files $uri $uri/ =404;
    }

    error_page 404 /404.html;
    error_page 500 502 503 504 /50x.html;
    location = /50x.html {
        root /usr/share/nginx/html;
    }

    location ~ \.php$ {
        try_files $uri =404;
        fastcgi_split_path_info ^(.+\.php)(/.+)$;
        fastcgi_pass unix:/var/run/php5-fpm.sock;
        fastcgi_index index.php;
        fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
        include fastcgi_params;
    }
}
</code></pre>
<p>Save and exit. This configures Nginx to serve PHP applications, and to use the htpasswd file, that we created earlier, for authentication.</p>

<p>To put the changes into effect, restart Nginx.</p>
<pre class="code-pre "><code langs="">sudo service nginx restart
</code></pre>
<p>Now we're ready to download Bacula-web.</p>

<h2 id="download-and-configure-bacula-web">Download and Configure Bacula-web</h2>

<p>Change to your home directory, and download the latest Bacula-web archive. At the time of this writing, <code>7.0.3</code> was the latest version:</p>
<pre class="code-pre "><code langs="">cd ~
wget --content-disposition http://www.bacula-web.org/download.html?file=files/bacula-web.org/downloads/bacula-web-<span class="highlight">7.0.3</span>.tgz
</code></pre>
<p>Now create a new directory, <code>bacula-web</code>, change to it, and extract the Bacula-web archive:</p>
<pre class="code-pre "><code langs="">mkdir bacula-web
cd bacula-web
tar xvf ../bacula-web-*.tgz
</code></pre>
<p>Before copying the files to our web server's document root, we should configure it first.</p>

<p>Change to the configuration directory like this:</p>
<pre class="code-pre "><code langs="">cd application/config
</code></pre>
<p>Bacula-web provides a sample configuration. Copy it like this:</p>
<pre class="code-pre "><code langs="">cp config.php.sample config.php
</code></pre>
<p>Now edit the configuration file in a text editor. We'll use vi:</p>
<pre class="code-pre "><code langs="">vi config.php
</code></pre>
<p>Find the <code>// MySQL bacula catalog</code>, and uncomment the connection details. Also, replace the <code>password</code> value with your Bacula database password (which can be found in <code>/etc/bacula/bacula-dir.conf</code> in the "dbpassword" setting):</p>
<pre class="code-pre "><code langs="">// MySQL bacula catalog
$config[0]['label'] = 'Backup Server';
$config[0]['host'] = 'localhost';
$config[0]['login'] = 'bacula';
$config[0]['password'] = '<span class="highlight">bacula-db-pass</span>';
$config[0]['db_name'] = 'bacula';
$config[0]['db_type'] = 'mysql';
$config[0]['db_port'] = '3306';
</code></pre>
<p>Save and exit.</p>

<p>Bacula-web is now configured. The last step is to put the application files in the proper place.</p>

<h2 id="copy-bacula-web-application-to-document-root">Copy Bacula-web Application to Document Root</h2>

<p>We configured Nginx to use <code>/usr/share/nginx/html</code> as the document root. Change to it, and delete the default <code>index.html</code>, with these commands:</p>
<pre class="code-pre "><code langs="">cd /usr/share/nginx/html
sudo rm index.html
</code></pre>
<p>Now, move the Bacula-web files to your current location, the Nginx document root:</p>
<pre class="code-pre "><code langs="">sudo mv ~/bacula-web/* .
</code></pre>
<p>Change the ownership of the files to <code>www-data</code>, the daemon user that runs Nginx:</p>
<pre class="code-pre "><code langs="">sudo chown -R www-data: *
</code></pre>
<p>Now Bacula-web is fully installed.</p>

<h2 id="access-bacula-web-via-a-browser">Access Bacula-web via a Browser</h2>

<p>Bacula-web is now accessible on your server's domain name or public IP address.</p>

<p>You may want to test that everything is configured properly. Luckily, a Bacula-web test page is provided. Access it by opening this URL in a web browser (substitute the highlighted part with your server's information):</p>
<pre class="code-pre "><code langs="">http://<span class="highlight">server_public_IP</span>/test.php
</code></pre>
<p>You should see a table that shows the status of the various components of Bacula-web. They should all have a green checkmark status, except for the database modules that you don't need. For example, we're using MySQL, so we don't need the other database modules:</p>

<p><img src="https://assets.digitalocean.com/articles/bacula-web/test.png" alt="Bacula-web Test" /></p>

<p>If everything looks good, you're ready to use the dashboard. You can access it by clicking on the top-left "Bacula-web" text, or by visiting your server in a web browser:</p>
<pre class="code-pre "><code langs="">http://<span class="highlight">server_public_IP</span>/
</code></pre>
<p>It should look something like this:</p>

<p><img src="https://assets.digitalocean.com/articles/bacula-web/bacula-web-dashboard.png" alt="Bacula-web Dashboard" /></p>

<h2 id="conclusion">Conclusion</h2>

<p>Now you are ready to use Bacula-web to easily monitor your various Bacula jobs and statuses.</p>

<p>Have fun!</p>

    