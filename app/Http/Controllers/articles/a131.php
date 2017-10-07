<link rel='stylesheet' href='digital_ocean.css'><br> <br> 
      <h3 id="introduction">Introduction</h3>

<p><a href="http://processwire.com/">ProcessWire</a> is a flexible, open-source PHP Content Management System. It is <a href="http://processwire.com/videos/managing-portfolio-site/">easy to update</a> for clients and a pleasure to work with for developers.</p>

<h2 id="system-requirements">System requirements</h2>

<p>You will need a standard LAMP stack to run ProcessWire. When creating your droplet, under Applications, choose <strong>LAMP on Ubuntu</strong>. A full list of requirements is available <a href="http://processwire.com/about/requirements/">here</a>.</p>

<h3 id="enable-htaccess">Enable htaccess</h3>

<p>To use mod_rewrite, htaccess overrides have to be enabled. You will need to modify the default host configuration file. This can be found at:</p>
<pre class="code-pre "><code langs="">/etc/apache2/sites-available/default
</code></pre>
<p>Open it in either <code>vim</code> or <code>nano</code>. Look for the following section (it starts with <code>Directory /var/www</code>):</p>
<pre class="code-pre "><code langs=""><Directory /var/www/>
  Options Indexes FollowSymLinks MultiViews
  AllowOverride None 
  Order allow,deny
  allow from all
</Directory>
</code></pre>
<p>Where it says <code>AllowOverride None</code> change it to <code>AllowOverride All</code>.</p>

<h3 id="enable-gd">Enable GD</h3>

<p>ProcessWire requires GD to be installed in order to resize and crop images uploaded through the CMS. To install GD and run it, use the following commands.</p>
<pre class="code-pre "><code langs="">apt-get install php5-gd
service apache2 restart
</code></pre>
<h3 id="enable-mod_rewrite">Enable mod_rewrite</h3>

<p>ProcessWire requires that the <strong>mod_rewrite</strong> PHP module be enabled. If you use Digital Ocean's LAMP Application droplet configuration, it should already be installed and will only need to be enabled. To enable it and restart Apache, run the following commands:</p>
<pre class="code-pre "><code langs="">a2enmod rewrite
service apache2 restart
</code></pre>
<h3 id="enable-sending-email">Enable sending email</h3>

<p>If you wish to have a contact form on your website, you will also need to ensure that <strong>sendmail</strong> is installed and configured so that you can use features that send email.</p>
<pre class="code-pre "><code langs="">apt-get install sendmail
sendmailconfig
service sendmail restart
service apache2 restart
</code></pre>
<p>To speed up PHP mail, add the following line to your host file, which can be found at <code>/etc/hosts</code>, replacing <code>yourhostnamehere</code> with your host name.</p>
<pre class="code-pre "><code langs="">127.0.0.1 localhost localhost.localdomain yourhostnamehere
</code></pre>
<h3 id="check-that-all-modules-were-installed-correctly">Check that all modules were installed correctly</h3>

<p>Visit your site URL's PHP Info page to see that all modules have been installed correctly at <code>http://yourhostname/info.php</code> where you replace <code>yourhostname</code> with your actual host name.</p>

<ul>
<li><p>GD</p></li>
<li><p>sendmail</p></li>
<li><p>mod_rewrite</p></li>
</ul>

<p>Once those are there, we are ready to download and install ProcessWire.</p>

<h2 id="compress-the-files-of-your-site">Compress the files of your site</h2>

<p>Create a compressed archive of your website for faster upload.</p>

<p>Be sure to include a MySQL dump for your website as well.</p>

<p>Don't forget about your <code>.htaccess</code> file, which is invisible by default. This file is required.</p>
<pre class="code-pre "><code langs="">tar cvf site_name.tar directory/
</code></pre>
<h2 id="uploading-your-processwire-website">Uploading your ProcessWire website</h2>

<p>Log in to your website by typing the following command in the command prompt: </p>
<pre class="code-pre "><code langs="">ssh user@yourdomain
</code></pre>
<p>Go to your public web directory: </p>
<pre class="code-pre "><code langs="">cd /var/www
</code></pre>
<p>Upload the archive of your website to your droplet using secure copy.</p>

<p>Make sure to also upload a copy of your MySQL dump file.</p>

<h2 id="unarchive-your-website">Unarchive your website</h2>

<p>Once your website has uploaded, you can extract your website using the following command:</p>
<pre class="code-pre "><code langs="">tar xvf website.tar 
</code></pre>
<p>This may unarchive your website into a folder called <code>website/</code>. If this is the case, you will need to move all of the contained files back one directory to <code>/var/www</code>. This can be done with the following commands:</p>
<pre class="code-pre "><code langs="">cd website
mv * ..
</code></pre>
<h2 id="mysql-import">MySQL import</h2>

<h3 id="create-a-database">Create a database</h3>

<p>Assuming you already have a MySQL username and password created, you will need to login to MySQL and create a database:</p>
<pre class="code-pre "><code langs="">mysql -u username -ppassword
</code></pre>
<p>Upon successful login, you should see <code>mysql ></code>. Run the following command to create a new database:</p>
<pre class="code-pre "><code langs="">create database dbname;
</code></pre>
<p>To verify that a database has been properly created, you can run the following command:</p>
<pre class="code-pre "><code langs="">show databases;
</code></pre>
<h3 id="import-your-mysql-dump">Import your MySQL dump</h3>

<p>Now that you have a database, you can import your MySQL dump file to it using the following command:</p>
<pre class="code-pre "><code langs="">mysql -u username -ppassword dbname < path/to/mysqldump.sql
</code></pre>
<h3 id="update-config-php">Update <code>config.php</code></h3>

<p>Now that you have uploaded your database, you will need to update your <code>site/config.php</code> file with your new database credentials:</p>
<pre class="code-pre "><code langs="">$config->dbHost = 'localhost';
$config->dbName = 'dbname';
$config->dbUser = 'username';
$config->dbPass = 'password';
$config->dbPort = '3306';
</code></pre>
<h2 id="finishing-up">Finishing up</h2>

<p>Go check out your website. To make sure everything is working properly, visit some of your pages and login to the dashboard.</p>

<p>If you are unable to successfully login, it may be because the <code>/site/assets/sessions</code> directory doesn't exist or is not writeable.</p>

<p>You will need to ensure that the <code>/site/assets</code> folder is writable by the server so that you can upload files and login.</p>

<p>Also be sure to delete your MySQL dump file once your website is working properly.</p>

<h3 id="further-reading">Further reading</h3>

<p>If you have any questions about ProcessWire, you can try the <a href="http://processwire.com/talk">forums</a>.</p>

<div class="author">Submitted by: <a href="http://tinaciousdesign.com">Tina Holly</a></div>

    