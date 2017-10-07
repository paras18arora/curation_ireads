<link rel='stylesheet' href='digital_ocean.css'><br> <br> 
      <h3 id="introduction">Introduction</h3>

<p>A FAMP stack, which is similar to a LAMP stack on Linux, is a group of open source software that is typically installed together to enable a FreeBSD server to host dynamic websites and web apps. FAMP is an acronym that stands for <strong>F</strong>reeBSD (operating system), <strong>A</strong>pache (web server), <strong>M</strong>ySQL (database server), and <strong>P</strong>HP (to process dynamic PHP content).</p>

<p>In this guide, we'll get a FAMP stack installed on a FreeBSD 10.1 cloud server using <code>pkg</code>, the FreeBSD package manager.</p>

<h2 id="prerequisites">Prerequisites</h2>

<p>Before you begin this guide, you should have a FreeBSD 10.1 server. Also, you must connect to your FreeBSD server as a user with superuser privileges (i.e. is allowed to use <code>sudo</code> or change to the root user).</p>

<h2 id="step-one-—-install-apache">Step One — Install Apache</h2>

<p>The Apache web server is currently the most popular web server in the world, which makes it a great choice for hosting a website.</p>

<p>We can install Apache easily using FreeBSD's package manager, <code>pkg</code>. A package manager allows us to install most software pain-free from a repository maintained by FreeBSD. You can learn more about <a href="https://indiareads/community/tutorials/how-to-manage-packages-on-freebsd-10-1-with-pkg">how to use <code>pkg</code> here</a>.</p>

<p>To install Apache 2.4 using <code>pkg</code>, use this command:</p>
<pre class="code-pre "><code langs="">sudo pkg install apache24
</code></pre>
<p>Enter <code>y</code> at the confirmation prompt.</p>

<p>This installs Apache and its dependencies.</p>

<p>To enable Apache as a service, add <code>apache24_enable="YES"</code> to the <code>/etc/rc.conf</code> file. We will use this <code>sysrc</code> command to do just that:</p>
<pre class="code-pre "><code langs="">sudo sysrc apache24_enable=yes
</code></pre>
<p>Now start Apache:</p>
<pre class="code-pre "><code langs="">sudo service apache24 start
</code></pre>
<p>You can do a spot check right away to verify that everything went as planned by visiting your server's public IP address in your web browser (see the note under the next heading to find out what your public IP address is if you do not have this information already):</p>
<pre class="code-pre "><code langs="">http://<span class="highlight">your_server_IP_address</span>/
</code></pre>
<p>You will see the default FreeBSD Apache web page, which is there for testing purposes. It should say: "It Works!", which indicates that your web server is correctly installed.</p>

<h3 id="how-to-find-your-server-39-s-public-ip-address">How To find Your Server's Public IP Address</h3>

<p>If you do not know what your server's public IP address is, there are a number of ways that you can find it. Usually, this is the address you use to connect to your server through SSH.</p>

<p>If you are using IndiaReads, you may look in the Control Panel for your server's IP address. You may also use the IndiaReads Metadata service, from the server itself, with this command: <code>curl -w "\n" http://169.254.169.254/metadata/v1/interfaces/public/0/ipv4/address</code>.</p>

<p>A more universal way to look up the IP address is to use the <code>ifconfig</code> command, on the server itself. The <code>ifconfig</code> command will print out information about your network interfaces. In order to narrow down the output to only the server's public IP address, use this command (note that the highlighted part is the name of the network interface, and may vary):</p>
<pre class="code-pre "><code langs="">ifconfig <span class="highlight">vtnet0</span> | grep "inet " | awk '{ print $2 }'
</code></pre>
<p>Now that you have the public IP address, you may use it in your web browser's address bar to access your web server.</p>

<h2 id="step-two-—-install-mysql">Step Two — Install MySQL</h2>

<p>Now that we have our web server up and running, it is time to install MySQL, the relational database management system. The MySQL server will organize and provide access to databases where our server can store information.</p>

<p>Again, we can use <code>pkg</code> to acquire and install our software.</p>

<p>To install MySQL 5.6 using <code>pkg</code>, use this command:</p>
<pre class="code-pre "><code langs="">sudo pkg install mysql56-server
</code></pre>
<p>Enter <code>y</code> at the confirmation prompt.</p>

<p>This installs the MySQL server and client packages.</p>

<p>To enable MySQL server as a service, add <code>mysql_enable="YES"</code> to the <code>/etc/rc.conf</code> file. This <code>sysrc</code> command will do just that:</p>
<pre class="code-pre "><code langs="">sudo sysrc mysql_enable=yes
</code></pre>
<p>Now start the MySQL server:</p>
<pre class="code-pre "><code langs="">sudo service mysql-server start
</code></pre>
<p>Now that your MySQL database is running, you will want to run a simple security script that will remove some dangerous defaults and slightly restrict access to your database system. Start the interactive script by running this command:</p>
<pre class="code-pre "><code langs="">sudo mysql_secure_installation
</code></pre>
<p>The prompt will ask you for your current root password (the MySQL admin user, <em>root</em>). Since you just installed MySQL, you most likely won’t have one, so leave it blank by pressing <code>RETURN</code>. Then the prompt will ask you if you want to set a root password. Go ahead and enter <code>Y</code>, and follow the instructions:</p>
<pre class="code-pre "><code langs="">Enter current password for root (enter for none): <span class="highlight">[RETURN]</span>
OK, successfully used password, moving on...

Setting the root password ensures that nobody can log into the MySQL
root user without the proper authorization.

Set root password? [Y/n] <span class="highlight">Y</span>
New password: <span class="highlight">password</span>
Re-enter new password: <span class="highlight">password</span>
Password updated successfully!
</code></pre>
<p>For the rest of the questions, you should simply hit the <code>RETURN</code> key at each prompt to accept the default values. This will remove some sample users and databases, disable remote root logins, and load these new rules so that MySQL immediately respects the changes we have made.</p>

<p>At this point, your database system is now set up and we can move on.</p>

<h2 id="step-three-—-install-php">Step Three — Install PHP</h2>

<p>PHP is the component of our setup that will process code to display dynamic content. It can run scripts, connect to MySQL databases to get information, and hand the processed content over to the web server to display.</p>

<p>We can once again leverage the <code>pkg</code> system to install our components. We're going to include the <code>mod_php</code>, <code>php-mysql</code>, and <code>php-mysqli</code> package as well.</p>

<p>To install PHP 5.6 with <code>pkg</code>, run this command:</p>
<pre class="code-pre "><code langs="">sudo pkg install mod_php56 php56-mysql php56-mysqli
</code></pre>
<p>Enter <code>y</code> at the confirmation prompt. This installs the <code>php56</code>, <code>mod_php56</code>, <code>php56-mysql</code>, and <code>php56-mysqli</code> packages.</p>

<p>Now copy the sample PHP configuration file into place with this command:</p>
<pre class="code-pre "><code langs="">sudo cp /usr/local/etc/php.ini-production /usr/local/etc/php.ini
</code></pre>
<p>Now run the <code>rehash</code> command to regenerate the system's cached information about your installed executable files:</p>
<pre class="code-pre "><code langs="">rehash
</code></pre>
<p>Before using PHP, you must configure it to work with Apache.</p>

<h3 id="install-php-modules-optional">Install PHP Modules (Optional)</h3>

<p>To enhance the functionality of PHP, we can optionally install some additional modules.</p>

<p>To see the available options for PHP 5.6 modules and libraries, you can type this into your system:</p>
<pre class="code-pre "><code langs="">pkg search php56
</code></pre>
<p>The results will be mostly PHP 5.6 modules that you can install. :</p>
<pre class="code-pre "><code langs="">mod_php56-5.6.3
php56-5.6.3
php56-bcmath-5.6.3
php56-bz2-5.6.3
php56-calendar-5.6.3
php56-ctype-5.6.3
php56-curl-5.6.3
php56-dba-5.6.3
php56-dom-5.6.3
php56-exif-5.6.3
...
</code></pre>
<p>To get more information about each module does, you can either search the internet, or you can look at the long description of the package by typing:</p>
<pre class="code-pre "><code langs="">pkg search -f <span class="highlight">package_name</span>
</code></pre>
<p>There will be a lot of output, with one field called <strong>Comment</strong> which will have an explanation of the functionality that the module provides.</p>

<p>For example, to find out what the <code>php56-calendar</code> package does, we could type this:</p>
<pre class="code-pre "><code langs="">pkg search -f php56-calendar
</code></pre>
<p>Along with a large amount of other information, you'll find something that looks like this:</p>
<pre class="code-pre "><code langs="">php56-calendar-5.6.3
Name           : php56-calendar
Version        : 5.6.3
...
Comment        : The calendar shared extension for php
...
</code></pre>
<p>If, after researching, you decide that you would like to install a package, you can do so by using the <code>pkg install</code> command like we have been doing for the other software.</p>

<p>For example, if we decide that <code>php56-calendar</code> is something that we need, we could type:</p>
<pre class="code-pre "><code langs="">sudo pkg install php56-calendar
</code></pre>
<p>If you want to install more than one module at a time, you can do that by listing each one, separated by a space, following the <code>pkg install</code> command, like this:</p>
<pre class="code-pre "><code langs="">sudo pkg install <span class="highlight">package1 package2 ...</span>
</code></pre>
<h2 id="step-four-—-configure-apache-to-use-php-module">Step Four — Configure Apache to Use PHP Module</h2>

<p>Before Apache will process PHP pages, we must configure it to use <code>mod_php</code>.</p>

<p>Open the Apache configuration file:</p>
<pre class="code-pre "><code langs="">sudo vi /usr/local/etc/apache24/Includes/php.conf
</code></pre>
<p>First, we will configure Apache to load <code>index.php</code> files by default by adding the following lines:</p>
<pre class="code-pre "><code langs=""><IfModule dir_module>
    DirectoryIndex index.php index.html
</code></pre>
<p>Next, we will configure Apache to process requested PHP files with the PHP processor. Add these lines to the end of the file:</p>
<pre class="code-pre "><code langs="">    <FilesMatch "\.php$">
        SetHandler application/x-httpd-php
    </FilesMatch>
    <FilesMatch "\.phps$">
        SetHandler application/x-httpd-php-source
    </FilesMatch>
</IfModule>
</code></pre>
<p>Save and exit.</p>

<p>Now restart Apache to put the changes into effect:</p>
<pre class="code-pre "><code langs="">sudo service apache24 restart
</code></pre>
<p>At this point, your FAMP stack is installed and configured. Let's test your PHP setup now.</p>

<h2 id="step-five-—-test-php-processing">Step Five — Test PHP Processing</h2>

<p>In order to test that our system is configured properly for PHP, we can create a very basic PHP script.</p>

<p>We will call this script <code>info.php</code>. In order for Apache to find the file and serve it correctly, it must be saved under a very specific directory--<strong>DocumentRoot</strong>--which is where Apache will look for files when a user accesses the web server. The location of DocumentRoot is specified in the Apache configuration file that we modified earlier (<code>/usr/local/etc/apache24/httpd.conf</code>).</p>

<p>By default, the DocumentRoot is set to <code>/usr/local/www/apache24/data</code>. We can create the <code>info.php</code> file under that location by typing:</p>
<pre class="code-pre "><code langs="">sudo vi /usr/local/www/apache24/data/info.php
</code></pre>
<p>This will open a blank file. Insert this PHP code into the file:</p>
<pre class="code-pre "><code langs=""><?php phpinfo(); ?>
</code></pre>
<p>Save and exit.</p>

<p>Now we can test whether our web server can correctly display content generated by a PHP script. To try this out, we just have to visit this page in our web browser. You'll need your server's public IP address again.</p>

<p>The address you want to visit will be:</p>
<pre class="code-pre "><code langs="">http://<span class="highlight">your_server_IP_address</span>/info.php
</code></pre>
<p>The page that you see should look something like this:</p>

<p><img src="https://assets.digitalocean.com/articles/freebsd_lamp/freebsd_info_php.png" alt="FreeBSD info.php" /></p>

<p>This page basically gives you information about your server from the perspective of PHP. It is useful for debugging and to ensure that your settings are being applied correctly.</p>

<p>If this was successful, then your PHP is working as expected.</p>

<p>You probably want to remove this file after this test because it could actually give information about your server to unauthorized users. To do this, you can type this:</p>
<pre class="code-pre "><code langs="">sudo rm /usr/local/www/apache24/data/info.php
</code></pre>
<p>You can always recreate this page if you need to access the information again later.</p>

<h2 id="conclusion">Conclusion</h2>

<p>Now that you have a FAMP stack installed, you have many choices for what to do next. Basically, you've installed a platform that will allow you to install most kinds of websites and web software on your server.</p>

<p>If you are interested in setting up WordPress on your new FAMP stack, check out this tutorial: <a href="https://indiareads/community/tutorials/how-to-install-wordpress-with-apache-on-freebsd-10-1">How To Install WordPress with Apache on FreeBSD 10.1</a>.</p>

    