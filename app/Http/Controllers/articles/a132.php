<link rel='stylesheet' href='digital_ocean.css'><br> <br> 
      <h3 id="introduction">Introduction</h3>

<p><a href="http://processwire.com/">ProcessWire</a> is a flexible, open-source PHP Content Management System. It is <a href="http://processwire.com/videos/managing-portfolio-site/">easy to update</a> for clients and a pleasure to work with for developers.</p>

<h2 id="system-requirements">System requirements</h2>

<p>You will need a standard LAMP stack to run ProcessWire. When creating your droplet, under Applications, choose <strong>LAMP on Ubuntu</strong>.</p>

<p>A full list of requirements is available <a href="http://processwire.com/about/requirements/">here</a>.</p>

<h3 id="update-ubuntu">Update Ubuntu</h3>

<p>To ensure that all of your modules install correctly, be sure to run the following command before installing any additional modules:</p>
<pre class="code-pre "><code langs="">apt-get update
</code></pre>
<h3 id="enable-mod_rewrite">Enable mod_rewrite</h3>

<p>ProcessWire requires that the <strong>mod_rewrite</strong> PHP module be enabled. If you use Digital Ocean's LAMP Application droplet configuration, it should already be installed and will only need to be enabled. To enable it and restart Apache, run the following commands:</p>
<pre class="code-pre "><code langs="">a2enmod rewrite
service apache2 restart
</code></pre>
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
<h3 id="enable-sending-email">Enable sending email</h3>

<p>If you wish to have a contact form on your website, you will also need to ensure that <strong>sendmail</strong> is installed and configured so that you can use features that send email including password recovery and contact forms.</p>
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

<h2 id="download-processwire">Download ProcessWire</h2>

<p>Navigate to your web root folder:</p>
<pre class="code-pre "><code langs="">cd /var/www
</code></pre>
<p>The easiest way to download ProcessWire is using <code>wget</code> to fetch one of the stable ProcessWire releases:</p>
<pre class="code-pre "><code langs="">wget https://github.com/ryancramerdesign/ProcessWire/archive/2.4.0.tar.gz
</code></pre>
<p>The above link refers to the latest release at the time this article was written but you can see and choose from all available releases <a href="https://github.com/ryancramerdesign/ProcessWire/releases">here</a>.</p>

<p>The above command downloaded the file <code>2.4.0.tar.gz</code>. To extract these files and move them to your web root, use the following commands.</p>
<pre class="code-pre "><code langs="">tar -zxf 2.4.0.tar.gz
cd ProcessWire-2.4.0
mv * ..
cd .. # go back to /var/www
rm -r ProcessWire-2.4.0 # deletes unneeded directory
rm index.html # the default index.html needs to be removed to use ProcessWire
</code></pre>
<p><em>Please note that the version number specified here may be different from the one you downloaded.</em></p>

<h2 id="install-processwire-in-your-browser">Install ProcessWire in your browser</h2>

<p>Navigate to your website in your browser. From there, you should see the installation screen:</p>

<p><img src="https://assets.digitalocean.com/articles/Ubuntu_InstallProcessWire/1.png" alt="" /></p>

<p>You may be prompted to rename <code>site-default</code> to <code>site</code>. You can do this by running the following command:</p>
<pre class="code-pre "><code langs="">mv site-default site
</code></pre>
<p>Reload the page and that warning bar should go away. Click "Get Started."</p>

<h3 id="checking-requirements">Checking requirements</h3>

<p>In order to make sure that ProcessWire will install and run smoothly, you need to make sure that all of the items in the compatibility check show up green:</p>

<p><img src="https://assets.digitalocean.com/articles/Ubuntu_InstallProcessWire/2.png" alt="" /></p>

<p>If you get some error messages, make sure that the <code>site</code> folder is writeable by the application. One way of doing this is with the following commands, which we will make secure after the installation.</p>
<pre class="code-pre "><code langs="">chmod -R 777 site/assets
chmod 666 site/config.php
</code></pre>
<p>Also rename the <code>htaccess.txt</code> to <code>.htaccess</code>:</p>
<pre class="code-pre "><code langs="">mv htaccess.txt .htaccess
</code></pre>
<p>If everything is green, it's safe to continue.</p>

<h3 id="input-your-database-credentials-and-modify-file-permissions">Input your database credentials and modify file permissions</h3>

<p>The next screen will ask you for your database credentials and the database you would like to use. </p>

<p>You also have the option to set the file permissions if you would like to change the defaults—by default, directories are 755 and files are 644. This can be changed later in the <code>site/config.php</code> file.</p>

<p>You will need to create a user and a database in MySQL and give permissions to that user.</p>

<h2 id="create-a-database">Create a database</h2>

<p>Assuming you already have a MySQL username and password created, you will need to login to MySQL and create a database:</p>
<pre class="code-pre "><code langs="">mysql -u username -ppassword
</code></pre>
<p>Upon successful login, you should see <code>mysql ></code>. </p>
<pre class="code-pre "><code langs="">CREATE USER 'username'@'localhost' IDENTIFIED BY 'password';
create database pwtest;
grant all privileges on pwtest.* to username@localhost identified by 'password';
</code></pre>
<p>If your database was accessed successfully and the permissions applied to your files and directories, the following page should show all green.</p>

<p><img src="https://assets.digitalocean.com/articles/Ubuntu_InstallProcessWire/3.png" alt="" /></p>

<p>On this page you can also select which admin theme you would like to use and what you would like your login URL to be. The default is <code>http://yourdomain.com/processwire</code>.</p>

<h3 id="choose-your-username-and-password">Choose your username and password</h3>

<p>At this time, you also have the chance to create a username and password. The default username is <code>admin</code> but it is advised to use a username other than the default. Your password must be at least 6 characters long.</p>

<p><img src="https://assets.digitalocean.com/articles/Ubuntu_InstallProcessWire/4.png" alt="" /></p>

<h2 id="finishing-up">Finishing Up</h2>

<p>If everything has been done correctly, the next screen should give you some information about what ProcessWire did to secure your installation. </p>

<p>Remove the installation script:</p>
<pre class="code-pre "><code langs="">rm install.php
</code></pre>
<p>Remove the installation files:</p>
<pre class="code-pre "><code langs="">rm -r /var/www/site/install/
</code></pre>
<p>Make the config file read only:</p>
<pre class="code-pre "><code langs="">chmod 444 /var/www/html/site/config.php
</code></pre>
<p><strong>At this point, you can view your installation or login to your website!</strong></p>

<p><img src="https://assets.digitalocean.com/articles/Ubuntu_InstallProcessWire/5.png" alt="" /></p>

<h3 id="further-reading">Further reading</h3>

<p>If you have any questions about ProcessWire, you can reach out to the friendly ProcessWire community in the <a href="http://processwire.com/talk">forums</a>.</p>

<div class="author">Submitted by: <a href="http://tinaciousdesign.com">Tina Holly </a></div>

    