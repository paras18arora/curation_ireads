<link rel='stylesheet' href='digital_ocean.css'><br> <br> 
      <h3 id="about-pligg-cms">About Pligg CMS</h3>

<hr />

<p>This tutorial will show how to install and configure Pligg CMS 2.0.x on an Ubuntu Server 12.04.x. Pligg CMS is an open source content management system that allows you to create an online community where users can submit articles, vote on them, and leave comments. </p>

<p>Pligg CMS was designed from the ground up as a way for a large number of contributors to submit and moderate content. Registered users, as well as visitors in some circumstances, are in control of the website's content. Pligg CMS 2.0.x requires Linux, Apache, MySQL 5+, and PHP 5+. </p>

<p>The following is a step-by-step guide to installing and configure pligg CMS 2.0.x on an Ubuntu Server 12.04</p>

<h2 id="step-1-enable-lamp-server">Step 1 - Enable LAMP Server</h2>

<hr />

<p>First, Login to your VPS server using the ssh command</p>
<pre class="code-pre "><code langs="">ssh username@ip_or_hostname
</code></pre>
<p>Before installing Pligg CMS 2.0.x, make sure you have installed LAMP server ( Linux, Apache, Mysql, PHP) on your virtual private server. If don't have the LAMP server, you can install it by typing the following command in terminal:</p>
<pre class="code-pre "><code langs="">sudo apt-get install lamp-server^
</code></pre>
<p>Or, you can read the tutorial on how to install LAMP on an Ubuntu Server here.</p>

<h2 id="step-2-download-pligg-cms-2-0-x">Step 2 - Download Pligg CMS 2.0.x</h2>

<p>You can download Pligg CMS 2.0.x straight from githup with the wget command:</p>
<pre class="code-pre "><code langs="">cd /var/www/
sudo wget https://github.com/Pligg/pligg-cms/releases/download/2.0.1/2.0.1.zip
</code></pre>
<h2 id="step-3-create-the-pligg-cms-database-and-user">Step 3 - Create the Pligg CMS Database and User</h2>

<hr />

<p>Now you need to switch gears for a moment and create a new MySQL directory for Pligg CMS.<br />
Go ahead and log into the MySQL Shell with the following command:</p>
<pre class="code-pre "><code langs="">mysql -u root -p
</code></pre>
<p>Login using your MySQL root password, and then you need to create a pligg CMS database, a user in that database, and give that user a new password. Keep in mind that all MySQL commands must end with semi-colon (;)</p>

<p>Create database for pligg CMS. For my example, I'll create the db name "dbpligg"</p>
<pre class="code-pre "><code langs="">CREATE DATABASE dbpligg;
</code></pre>
<p>Create the new user. You can replace the database, name, and password with whatever you prefer:</p>
<pre class="code-pre "><code langs="">CREATE USER pligguser@localhost;
</code></pre>
<p>Now, set password for username "pligguser"</p>
<pre class="code-pre "><code langs="">SET PASSWORD FOR pligguser@localhost= PASSWORD("pL!g9p45sw0rd");
</code></pre>
<p>Finish up by granting all privileges to the new user ("pligguser") with the following command:</p>
<pre class="code-pre "><code langs="">GRANT ALL PRIVILEGES ON dbpligg.* TO pligguser@localhost IDENTIFIED BY 'pL!g9p45sw0rd';
</code></pre>
<p>Then refresh MySQL and sign out from MySQL shell</p>
<pre class="code-pre "><code langs="">FLUSH PRIVILEGES;

exit;
</code></pre>
<h2 id="step-4-setup-the-pligg-cms-2-0-x-configuration">Step 4 - Setup the Pligg CMS 2.0.x Configuration</h2>

<hr />

<p>If you installing Pligg CMS 2.0.x on a fresh LAMP VPS, you need to rename/change file index.html to other name file. In example: I'll change file index.html to index.html.origin</p>
<pre class="code-pre "><code langs="">sudo mv /var/www/index.html /var/www/index.html.origin
</code></pre>
<p>Extract archive pligg 2.0.x using unzip command:</p>
<pre class="code-pre "><code langs="">sudo unzip /var/www/2.0.1.zip -d /var/www/
</code></pre>
<p>Rename the /favicon.ico.default to /favicon.ico</p>
<pre class="code-pre "><code langs="">sudo mv /var/www/favicon.ico.default /var/www/favicon.ico
</code></pre>
<p>Rename the /settings.php.default to /settings.php</p>
<pre class="code-pre "><code langs="">sudo mv /var/www/settings.php.default /var/www/settings.php
</code></pre>
<p>Rename the /languages/lang<em>english.conf.default file to lang</em>english.conf.</p>
<pre class="code-pre "><code langs="">sudo mv /var/www/languages/lang_english.conf.default /var/www/languages/lang_english.conf
</code></pre>
<p>Note: Apply to any other language file that you might use that are located in the /languages directory. </p>

<p>Rename the /libs/dbconnect.php.default file to dbconnect.php</p>
<pre class="code-pre "><code langs="">sudo mv /var/www/libs/dbconnect.php.default /var/www/libs/dbconnect.php
</code></pre>
<p>Rename the directory /logs.default to /logs</p>
<pre class="code-pre "><code langs="">sudo mv /var/www/logs.default /logs
</code></pre>
<p>Change permision to the following directories and files: admin/backup/, avatars/groups<em>uploaded/, avatars/user</em>uploaded/, cache/, languages/) to 777 <br />
cd /var/www</p>
<pre class="code-pre "><code langs="">sudo chmod 777 admin/backup/ avatars/groups_uploaded/ avatars/user_uploaded/ cache/ languages/
</code></pre>
<p>Change permision the following file (/libs/dbconnect.php, /settings.php) to 666</p>
<pre class="code-pre "><code langs="">sudo chmod 666 libs/dbconnect.php settings.php
</code></pre>
<p>Edit file settings.php, change $my<em>base</em>url = 'http://localhost'; to $my<em>base</em>url = 'http://your-domain.com';</p>
<pre class="code-pre "><code langs="">sudo nano /var/www/settings.php
</code></pre>
<p>Give ownership of the file and directory to the apache user.</p>
<pre class="code-pre "><code langs="">sudo chown -R www-data:www-data /var/www/*
sudo usermod -a -G www-data username
</code></pre>
<h2 id="step-5-finish-installation-pliggcms-from-web-browser">Step 5 - Finish Installation, PliggCMS from Web Browser</h2>

<hr />

<p>Now from PC or laptop, open the your favorite web browser and navigate to: http://ip<em>or</em>domain/install/install.php. You can see the following screenshot is a step-by-step installation of Pligg CMS from web browser. </p>

<p>Select language </p>

<p><img src="https://assets.digitalocean.com/articles/Pligg_Ubuntu/1.png" /></p>
 

<p>Click next step if you have already completed this in step 4</p>

<p><img src="https://assets.digitalocean.com/articles/Pligg_Ubuntu/2.png" /></p> 

<p>Enter your MySQL database settings </p>

<p><img src="https://assets.digitalocean.com/articles/Pligg_Ubuntu/3.png" /></p>

<p>Checking database connections </p>

<p></p><p><img src="https://assets.digitalocean.com/articles/Pligg_Ubuntu/4.png" /></p>

<p>Enter your admin account details </p>

<p></p><p><img src="https://assets.digitalocean.com/articles/Pligg_Ubuntu/5.png" /></p>

<p>Congratulations, Pligg CMS Installation is Complete! </p>

<p></p><p><img src="https://assets.digitalocean.com/articles/Pligg_Ubuntu/6.png" /></p>

<p>Login again to your VPS using the ssh command, then change permisision file "/libs/dbconnect.php" to 644</p>
<pre class="code-pre "><code langs="">sudo chmod 644 /var/www/libs/dbconnect.php
</code></pre>
<p>Delete pligg installation folder with following command:</p>
<pre class="code-pre "><code langs="">sudo rm -rf /var/www/install
</code></pre>
<p>Pligg CMS frontpage </p>

<p></p><p><img src="https://assets.digitalocean.com/articles/Pligg_Ubuntu/7.png" /></p>

<p>Now, Login to the admin area (yourdomain.com/admin/admin_index.php) using the username and password information you entered from the previous step. </p>

<p></p><p><img src="https://assets.digitalocean.com/articles/Pligg_Ubuntu/8.png" /></p>

<p>Once you log in you should be presented with more information about how to use Pligg CMS. </p>

<div class="author">Submitted by: <a href="abdul.khois@gmail.com">Linux Scoop</a></div>

    