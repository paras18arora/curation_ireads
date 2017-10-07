<link rel='stylesheet' href='digital_ocean.css'><br> <br> 
      <h2 id="introduction">Introduction</h2>

<p>Today's high traffic web applications are powered by slick and fast-response web servers, scalable, enterprise-class databases, and dynamic content served by feature-rich scripting languages. Typical Linux web application stack follows the LAMP architecture (Linux, Apache, MySQL, and PHP/Python). Widely available tutorials show us how these components can be installed and configured in one single server.</p>

<p>That's seldom the case in real life. In a professional three-tier setup, the database back-end would be secluded in its own server; the web server would send its requests to an app tier acting as a middleware between the database and the website.</p>

<p>Although Apache is still by far the most widely-used web server, Nginx has rapidly gained popularity for its small footprint and fast response time. MySQL's community edition is still a popular choice for databases, but many sites also use another open source database platform called PostgreSQL.</p>

<h3 id="goals">Goals</h3>

<p>In this tutorial, we will create a simple web application in a two-tier architecture. Our base operating system for both nodes will be CentOS 7. The site will be powered by an Nginx web server running PHP code that talks to a PostgreSQL database.</p>

<p>Instead of adopting a "top-down" approach seen in other LAMP or LEMP tutorials, we will use a "ground-up" approach: we will create a database tier first, then the web server and then see how the web server can connect to the database.</p>

<p>We will call this configuration a LEPP (Linux, Nginx, PHP, PostgreSQL) stack.</p>

<h3 id="prerequisites">Prerequisites</h3>

<p>To follow this tutorial, you will need the following:</p>

<ul>
<li>Two CentOS 7 Droplets with at least 2GB of RAM and 2 CPU cores, one each for the database server and the web server.</li>
</ul>

<p>We'll refer to the IP addresses of these machines as <code><span class="highlight">your_db_server_ip</span></code> and <code><span class="highlight">your_web_server_ip</span></code> respectively; you can find the actual IP addresses of these machines on the IndiaReads control panel.</p>

<ul>
<li>Sudo non-root users on both Droplets. To set this up, follow <a href="https://indiareads/community/tutorials/how-to-add-and-delete-users-on-a-centos-7-server">this tutorial</a>.</li>
</ul>

<h2 id="step-one-—-installing-postgresql">Step One — Installing PostgreSQL</h2>

<p>In this step, we will install PostgreSQL on the database server.</p>

<p>Connect to the empty, freshly installed CentOS 7 box where you want to install PostgreSQL. Its repository doesn't come with CentOS 7 by default, so we will need to download the yum repository RPM first.</p>
<pre class="code-pre "><code langs="">sudo wget http://yum.postgresql.org/9.4/redhat/rhel-7Server-x86_64/pgdg-centos94-9.4-1.noarch.rpm
</code></pre>
<p>Once the RPM has been saved, install the repository.</p>
<pre class="code-pre "><code langs="">sudo yum install pgdg-centos94-9.4-1.noarch.rpm -y
</code></pre>
<p>Finally, install the PostgreSQL 9.4 server and its contrib modules.</p>
<pre class="code-pre "><code langs="">sudo yum install postgresql94-server postgresql94-contrib -y
</code></pre>
<h2 id="step-two-—-configuring-postgresql">Step Two — Configuring PostgreSQL</h2>

<p>In this step, we will customize a number of post-installation configurations for PostgreSQL. </p>

<p>In CentOS 7, the default location for PostgreSQL 9.4 data and configuration files is <code>/var/lib/pgsql/9.4/data/</code> and the location for program binaries is <code>/usr/pgsql-9.4/bin/</code>. The data directory is empty at the beginning. We will need to run the <code>initdb</code> program to initialize the database cluster and create necessary files in it:</p>
<pre class="code-pre "><code langs="">sudo /usr/pgsql-9.4/bin/postgresql94-setup initdb
</code></pre>
<p>Once the database cluster has been initialized, there will be a file called <code>postgresql.conf</code> in the data folder, which is the main configuration file for PostgreSQL. We will change two parameters in this file. Using <code>vi</code> or your favorite text editor, open the file for editing.</p>
<pre class="code-pre "><code langs="">sudo vi /var/lib/pgsql/9.4/data/postgresql.conf
</code></pre>
<p>And change the following lines:</p>

<ul>
<li>Change <code><span class="highlight">#</span>listen_addresses = '<span class="highlight">localhost</span>'</code> to  <code>listen_addresses = '*'</code></li>
<li>Change <code><span class="highlight">#</span>port = 5432</code> to <code>port = 5432</code></li>
</ul>

<p>The first parameter specifies which IP address the database server will listen to. As a security measure, out-of-box Postgres installations only allow local host connections. Changing this to '*' means Postgres will listen for traffic from any source. The second parameter has been enabled by taking off the comment marker (#); it specifies the default port for Postgres.</p>

<p>Save and exit the file.</p>

<p>Next, we will edit <code>pg_hba.conf</code>, which is PostgreSQL's Host Based Access (HBA) configuration file. It specifies which hosts and IP ranges can connect to the database server. Each entry specifies whether the connection can be made locally or remotely (host), which database it can connect to, which user it can connect as, which IP block the request can come from, and what authentication mode should be used. Any connection requests not matching with any of these entries would be denied.</p>

<p>Open <code>pg_hba.conf</code> for editing.</p>
<pre class="code-pre "><code langs="">sudo vi /var/lib/pgsql/9.4/data/pg_hba.conf
</code></pre>
<p>Scroll to the bottom of the file, and add this line:</p>
<pre class="code-pre "><code langs="">host        all             all             <span class="highlight">your_web_server_ip</span>/32          md5
</code></pre>
<p>This line tells PostgreSQL to accept database connections coming only from IP address <code><span class="highlight">your_web_server_ip</span></code> using a standard md5 checksum for password authentication. The connection can be made against any database as any user. </p>

<p>Save and exit the file. </p>

<p>Next, start the Postgres service:</p>
<pre class="code-pre "><code langs="">sudo systemctl start postgresql-9.4.service
</code></pre>
<p>And then enable it:</p>
<pre class="code-pre "><code langs="">sudo systemctl enable postgresql-9.4.service
</code></pre>
<p>To check if the database server is accepting connections, we can look at the last few lines of the latest Postgres log file. The database error logs are saved in the <code>/var/lib/pgsql/9.4/data/pg_log</code> directory. Run the following command to see the files in this directory.</p>
<pre class="code-pre "><code langs="">sudo ls -l /var/lib/pgsql/9.4/data/pg_log
</code></pre>
<p>The log file names have the pattern <code>postgresql-<span class="highlight">day_of_week</span>.log</code> (for example, <code>postgresql-Wed.log</code>). Find the log file that corresponds to the current day, and look at the last few lines of the latest log file.</p>
<pre class="code-pre "><code langs="">sudo tail -f -n 20 /var/lib/pgsql/9.4/data/pg_log/postgresql-<span class="highlight">day_of_week</span>.log
</code></pre>
<p>The output should show something similar to this:</p>
<pre class="code-pre "><code langs="">...

< 2015-02-26 21:32:24.159 EST >LOG:  database system is ready to accept connections
< 2015-02-26 21:32:24.159 EST >LOG:  autovacuum launcher started
</code></pre>
<p>Press <strong>CTRL+C</strong> to stop the output from the <code>tail</code> command.</p>

<h2 id="step-three-—-updating-the-database-server-firewall">Step Three — Updating the Database Server Firewall</h2>

<p>We also need to allow Postgres database traffic to pass though the firewall. CentOS 7 implements a dynamic firewall through the <code>firewalld</code> daemon; the service doesn't need to restart for changes to take effect. The <code>firewalld</code> service should start automatically at system boot time, but it's always good to check.</p>
<pre class="code-pre "><code langs="">sudo firewall-cmd --state
</code></pre>
<p>The default state should be <code>running</code>, but if it is <code>not running</code> start it with:</p>
<pre class="code-pre "><code langs="">sudo systemctl start firewalld
</code></pre>
<p>Next, add the rules for port 5432. This is the port for PostgreSQL database traffic.</p>
<pre class="code-pre "><code langs="">sudo firewall-cmd --permanent --zone=public --add-port=5432/tcp
</code></pre>
<p>Then reload the firewall.</p>
<pre class="code-pre "><code langs="">sudo firewall-cmd --reload
</code></pre>
<h2 id="step-four-—-creating-and-populating-the-database">Step Four — Creating and Populating the Database</h2>

<p>In this step, we will create a database and add some data to it. This is the data our web application will dynamically fetch and display.</p>

<p>The first step is is to change the password of the Postgres superuser, called <strong>postgres</strong>, which is created when PostgreSQL is first installed. It's best the user's password is changed from within Postgres rather than the OS prompt. To do this, switch to the <strong>postgres</strong> user:</p>
<pre class="code-pre "><code langs="">sudo su - postgres
</code></pre>
<p>This will change the command prompt to <code>-bash-4.2$</code>. Next, start the built-in client tool.</p>
<pre class="code-pre "><code langs="">psql
</code></pre>
<p>By default this will log the postgres user to the Postgres database. Your prompt will change to <code>postgres=#</code>, the psql prompt, not an OS prompt. Issuing the <code>\password</code> command now will result in prompts asking to change the password.</p>
<pre class="code-pre "><code langs="">\password
</code></pre>
<p>Provide a secure password for the Postgres user.</p>

<p>Next, create a database called <strong>product</strong>:</p>
<pre class="code-pre "><code langs="">CREATE DATABASE product;
</code></pre>
<p>Then connect to the <strong>product</strong> database:</p>
<pre class="code-pre "><code langs="">\connect product;
</code></pre>
<p>Next, create a table in the database called <strong>product_list</strong>:</p>
<pre class="code-pre "><code langs="">CREATE TABLE product_list (id int, product_name varchar(50));
</code></pre>
<p>Run each of the following commands one at a time. Each command will add a single record to the <code>product_list</code> table.</p>
<pre class="code-pre "><code langs="">INSERT INTO product_list VALUES (1, 'Book');
INSERT INTO product_list VALUES (2, 'Computer');
INSERT INTO product_list VALUES (3, 'Desk');
</code></pre>
<p>Finally, check the data has been added correctly.</p>
<pre class="code-pre "><code langs="">SELECT * FROM product_list;
</code></pre>
<p>The output should look like this:</p>
<pre class="code-pre "><code langs=""> id | product_name
----+--------------
  1 | Book
  2 | Computer
  3 | Desk
(3 rows)
</code></pre>
<p>This is all we need to do on the database server; you can now disconnect from it.</p>

<h2 id="step-five-—-installing-nginx">Step Five — Installing Nginx</h2>

<p>Next, we will install and configure an Nginx web server in the other Droplet. Connect to the other empty, freshly installed CentOS 7 box.</p>

<p>Like PosgreSQL, the Nginx repository doesn't come with CentOS 7 by default. We will need to download the yum repository RPM first.</p>
<pre class="code-pre "><code langs="">sudo wget http://nginx.org/packages/centos/7/noarch/RPMS/nginx-release-centos-7-0.el7.ngx.noarch.rpm
</code></pre>
<p>Once the RPM has been saved, install the repository.</p>
<pre class="code-pre "><code langs="">sudo yum install nginx-release-centos-7-0.el7.ngx.noarch.rpm -y
</code></pre>
<p>Finally, install the Nginx web server.</p>
<pre class="code-pre "><code langs="">sudo yum install nginx -y
</code></pre>
<h2 id="step-six-—-updating-the-web-server-firewall">Step Six — Updating the Web Server Firewall</h2>

<p>In this step, we will configure the firewall to allow Nginx traffic, and customize some Nginx configurations.</p>

<p>We need to allow HTTP/HTTPS traffic to pass though the firewall. Like before, check that the <code>firewalld</code> service is running.</p>
<pre class="code-pre "><code langs="">sudo firewall-cmd --state
</code></pre>
<p>The default state should be <code>running</code>, but if it's <code>not running</code>, start it:</p>
<pre class="code-pre "><code langs="">sudo systemctl start firewalld
</code></pre>
<p>Now add the firewall rule for port 80 (HTTP):</p>
<pre class="code-pre "><code langs="">sudo firewall-cmd --permanent --zone=public --add-port=80/tcp
</code></pre>
<p>Add another for port 443 (HTTPS):</p>
<pre class="code-pre "><code langs="">sudo firewall-cmd --permanent --zone=public --add-port=443/tcp
</code></pre>
<p>Then, reload the firewall.</p>
<pre class="code-pre "><code langs="">sudo firewall-cmd --reload
</code></pre>
<p>Next, start Nginx.</p>
<pre class="code-pre "><code langs="">sudo systemctl start nginx.service
</code></pre>
<p>And enable it.</p>
<pre class="code-pre "><code langs="">sudo systemctl enable nginx.service
</code></pre>
<p>Pointing our browser to the server's IP address should show us the default web page:</p>

<p><img src="https://assets.digitalocean.com/articles/LEPP_CentOS7/1.jpg" alt="Default web page served by Nginx" /></p>

<h2 id="step-seven-—-configuring-nginx">Step Seven — Configuring Nginx</h2>

<p>There are two Nginx configuration files relevant in this step. The first one is the main configuration file and the second one is a site-specific one. </p>

<p>The general configuration file controls the overall server characteristics. Nginx can serve many web sites and each site is called a server block (Apache calls them virtual hosts, or vhosts). Each site's configuration is controlled by a server block configuration file.</p>

<p>Let's edit the main server configuration file.</p>
<pre class="code-pre "><code langs="">sudo vi /etc/nginx/nginx.conf
</code></pre>
<p>On the second line of the file, change the <code>worker_processes</code> parameter from 1 to 2. This tells Nginx's worker threads to use of all the CPU cores available. Save and exit the file.</p>

<p>We won't create serve -blocks here. Instead, we will create our web application in the default server block, so let's edit the default server block config file.</p>
<pre class="code-pre "><code langs="">sudo vi /etc/nginx/conf.d/default.conf
</code></pre>
<p>The contents look like this. The parts you will edit are highlighted.</p>
<pre class="code-pre "><code langs="">server {
    listen       80;
    server_name  <span class="highlight">localhost</span>;

    ...

    <span class="highlight">location / {</span>
        root   /usr/share/nginx/html;
        <span class="highlight">index  index.html index.htm</span>;
    <span class="highlight">}</span>

    #error_page  404              /404.html;

    ...

    # pass the PHP scripts to FastCGI server listening on 127.0.0.1:9000
    #
    <span class="highlight">#</span>location ~ \.php$ {
    <span class="highlight">#    root            html;</span>
    <span class="highlight">#</span>    fastcgi_pass     <span class="highlight">127.0.0.1:9000;</span>
    <span class="highlight">#</span>    fastcgi_index        index.php;
    <span class="highlight">#</span>    fastcgi_param        SCRIPT_FILENAME <span class="highlight">/scripts</span>$fastcgi_script_name;
    <span class="highlight">#</span>    include              fastcgi_params;
    <span class="highlight">#</span>}

 ...
}
</code></pre>
<p>Make the following edits:</p>

<ul>
<li><p>Set <code>server_name</code> from <code>localhost</code> to <code><span class="highlight">your_web_server_ip</span></code>.</p></li>
<li><p>Add <code>index.php</code> to the <code>index</code> directive so it reads <code>index.php index.html index.htm</code>. </p></li>
<li><p>Delete the <code>location / {</code> and <code>}</code> lines containing the <code>root</code> and <code>index</code> directives. Without this change you may find your web page not displaying in the browser and the Nginx error log recording messages like <code>"Unable to open primary script: /etc/nginx/html/index.php (No such file or directory)"</code></p></li>
<li><p>Uncomment the <code>location ~ \.php$</code> block (including the last curly bracket) under the <strong>pass the PHP scripts to FastCGI server</strong> comment.</p></li>
<li><p>Delete the root directive under the same <code>location ~ \.php$</code> block.</p></li>
<li><p>Change fastcgi_pass directive value from <code>127.0.0.1:9000</code> to <code>unix:/var/run/php-fpm/php5-fpm.sock</code>. This is to ensure the PHP FastCGI Process Manager (which we'll install in the next step) will be listening to the Unix socket.</p></li>
<li><p>Change te <code>fastcgi_param</code> directive value to <code>SCRIPT_FILENAME $document_root$fastcgi_script_name</code>. This tells the web server that PHP script files will be saved under the document root directory.</p></li>
</ul>

<p>Once you finish editing, the file should look like this:</p>
<pre class="code-pre "><code langs="">server {
    listen       80;
    server_name  <span class="highlight">your_web_server_ip</span>;

    ...

    root   /usr/share/nginx/html;
    index  <span class="highlight">index.php</span> index.html index.htm;

    ...

    # pass the PHP scripts to FastCGI server listening on 127.0.0.1:9000
    #
    location ~ \.php$ {
        fastcgi_pass   <span class="highlight">unix:/var/run/php-fpm/php5-fpm.sock;</span>
        fastcgi_index  index.php;
        fastcgi_param  SCRIPT_FILENAME <span class="highlight">$document_root</span>$fastcgi_script_name;
        include        fastcgi_params;
    }

    ...
</code></pre>
<p>Save and exit the file, then start the web server.</p>
<pre class="code-pre "><code langs="">sudo systemctl restart nginx.service
</code></pre>
<h2 id="step-eight-—-installing-php">Step Eight — Installing PHP</h2>

<p>We will now install three components of PHP in the web server: the PHP engine itself, the FastCGI Process Manager (FPM) and the PHP module for PostgreSQL.</p>

<p>First, install PHP.</p>
<pre class="code-pre "><code langs="">sudo yum install php -y
</code></pre>
<p>Next we will install the FastCGI Process Manager (FPM), which is PHP's own implementation of FastCGI. FastCGI is like an add-on on top of your web server. It runs independently and helps speed up user requests by consolidating them in one single process, thus speeding up response time.</p>
<pre class="code-pre "><code langs="">sudo yum install php-fpm -y
</code></pre>
<p>Finally, install the PHP Postgres module:</p>
<pre class="code-pre "><code langs="">sudo yum install php-pgsql -y
</code></pre>
<h2 id="step-nine-—-configuring-php">Step Nine — Configuring PHP</h2>

<p>In this step, we will configure PHP.</p>

<p>Open the PHP configuration file.</p>
<pre class="code-pre "><code langs="">sudo vi /etc/php.ini
</code></pre>
<p>Make the following changes:</p>

<ul>
<li><p>Change <code>expose_php = On</code> to <code>expose_php = Off</code>. Setting this parameter to <code>Off</code> just means PHP doesn't add its signature to the web server's header and doesn't expose the fact that the server is running PHP.</p></li>
<li><p>Change <code>;cgi.fix_pathinfo=0</code> to <code>;cgi.fix_pathinfo=1</code>.</p></li>
</ul>

<p>Save and exit the file. Next, edit the FPM config file.</p>
<pre class="code-pre "><code langs="">sudo vi /etc/php-fpm.d/www.conf
</code></pre>
<p>Make the following changes: </p>

<ul>
<li><p>Change <code>user = apache</code> to <code>user = nginx</code>.</p></li>
<li><p>Similarly, change <code>group = apache</code> to <code>group = nginx</code>.</p></li>
<li><p>Change <code>listen = 127.0.0.1:9000</code> to <code>listen = /var/run/php-fpm/php5-fpm.sock</code>.  We set this same value in the Nginx default server block's configuration file.</p></li>
</ul>

<p>Save and exit vi. Next start PHP-FPM.</p>
<pre class="code-pre "><code langs="">sudo systemctl start php-fpm.service
</code></pre>
<p>Then enable it.</p>
<pre class="code-pre "><code langs="">sudo systemctl enable php-fpm.service
</code></pre>
<h2 id="step-ten-—-creating-the-web-application">Step Ten — Creating the Web Application</h2>

<p>We have all our server components ready in both the nodes. It's now time we create our PHP application. Create a file named <code>index.php</code> in <code>/usr/share/nginx/html</code>.</p>
<pre class="code-pre "><code langs="">sudo vi /usr/share/nginx/html/index.php
</code></pre>
<p>Paste in the following contents. Make sure you replace the highlighted variables with your database server IP address and Postgres password respectively.</p>
<pre class="code-pre "><code langs=""><html>

<head>
    <title>LEPP Stack Example</title>
</head>

<body>

<h4>LEPP (Linux, Nginx, PHP, PostgreSQL) Sample Page</h4>
<hr/>
<p>Hello and welcome. This web page is dynamically showing a product list from a PostgreSQL database</p>

<?php

    $host = "<span class="highlight">your_db_server_ip</span>";
    $user = "postgres";
    $password = "<span class="highlight">your_postgres_password</span>";
    $dbname = "product";

    $con = pg_connect("host=$host dbname=$dbname user=$user password=$password")
            or die ("Could not connect to server\n");

    $query = "SELECT * FROM product_list";
    $resultset = pg_query($con, $query) or die("Cannot execute query: $query\n");
    $rowcount = pg_numrows($resultset);

    for($index = 0; $index < $rowcount; $index++) {
            $row = pg_fetch_array($resultset, $index);
            echo $row["id"], "-", $row["product_name"];
            echo "<br>";
    }
?>

</body>
</html>
</code></pre>
<p>This is a simple web page with embedded PHP code. First, it defines a number of parameters for the database connection string. Next, a connection (specified by <code>$con</code>) is made against the database server. A query is specified and it's then executed against the <strong>product_list</strong> table. It iterates through the returned results and prints the contents of each row in a new line.</p>

<p>Once the file is written and saved, open a browser window and point it to <code><span class="highlight">your_web_server_ip</span></code>. The contents should look like this:</p>

<p><img src="http://i.imgur.com/HDwygcq.jpg" alt="PHP Dynamic Web Page showing PostgreSQL Data" /></p>

<h3 id="conclusion">Conclusion</h3>

<p>We have built two boxes from scratch, installed and configured all the necessary software, and then deployed our web application in it. A production stack would have additional complexity, like adding external firewalls and load balancers, but this is a solid basic configuration you can use to get started. Enjoy!</p>

    