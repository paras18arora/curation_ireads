<link rel='stylesheet' href='digital_ocean.css'><br><img src=https://community-cdn-digitalocean-com.global.ssl.fastly.net/assets/tutorials/images/large/phpMyAdmin-OneClick-TW.png?1431627721/> <br> 
      <h2 id="introduction">Introduction</h2>

<p>While it is possible to manage a MySQL database server completely from the command line, many users prefer to use a GUI interface and PHPMyAdmin is the most popular web-based MySQL management tool available.</p>

<p>PHPMyAdmin provides an easy to use interface to manage databases, users and tables for your MySQL instance and the PHPMyAdmin One-Click Application image allows you to save time by starting with a LAMP configuration and PHPMyAdmin already in place.</p>

<h2 id="create-your-droplet">Create your Droplet</h2>

<p>To get started with the PHPMyAdmin Application Image create a new droplet from the control panel specifying a hostname and plan.</p>

<p><img src="https://assets.digitalocean.com/articles/phpmyadmin-1-click/create_droplet.png" alt="" /></p>

<p>Select the region where you want to create your new droplet...</p>

<p><img src="https://assets.digitalocean.com/articles/phpmyadmin-1-click/select-region.png" alt="" /></p>

<p>and select <strong>PHPMyAdmin on 14.04</strong> under the Applications tab.</p>

<p><img src="https://assets.digitalocean.com/articles/phpmyadmin-1-click/choose-image.png" alt="" /></p>

<p>If you use ssh keys to log into your droplets you can specify a key here.</p>

<p><img src="https://assets.digitalocean.com/articles/phpmyadmin-1-click/choose-key.png" alt="" /></p>

<p>Now click <code>create</code> to start your droplet creation.</p>

<h2 id="log-into-phpmyadmin">Log into PHPMyAdmin</h2>

<p>Your new PHPMyAdmin droplet is ready to use as soon as it is launched but you will need to log in once via SSH or by using the web console in order to access your MySQL root password.</p>

<p>When you log in you will see a message like the one below:</p>
<pre class="code-pre "><code langs="">-------------------------------------------------------------------------------------
Thank you for using IndiaReads's LAMP/PHPMyAdmin Application.
Your PHPMyAdmin installation can be accessed at http://0.0.0.0/phpmyadmin 
The details of your PHP installation can be seen at http://0.0.0.0/info.php
Your MySQL root user's password is <span class="highlight">gNv8yafpVZ</span>
You are encouraged to run mysql_secure_installation to ready your server for production.
-------------------------------------------------------------------------------------
To delete this message of the day: rm -rf /etc/motd.tail

</code></pre>
<p>Make a note of your MySQL root password and then open a web browser and browse to http://<span class="highlight">DROPLET_IP</span>/phpmyadmin.  Log in with the username <strong>root</strong> and your MySQL root password.  You should then see a page like the one below:</p>

<p><img src="https://assets.digitalocean.com/articles/phpmyadmin-1-click/phpmyadmin-main.png" alt="" /></p>

<h2 id="creating-a-database">Creating a Database</h2>

<p>To create a new database you can click on the "Databases" tab where you will see a form that will allow you to specify the name of your new database.</p>

<p><img src="https://assets.digitalocean.com/articles/phpmyadmin-1-click/databases.png" alt="" /></p>

<h2 id="creating-a-user">Creating a User</h2>

<p>It is not always advisable to use your root user for your web apps.  For this reason you may want to create a new user on your MySQL server.  If you click on the <strong>Users</strong> tab you will see the option to Add a user.</p>

<p><img src="https://assets.digitalocean.com/articles/phpmyadmin-1-click/adduser.png" alt="" /></p>

<p>This link will take you to a form where you can add a user and select their privileges.</p>

<h2 id="secure-phpmyadmin">Secure PHPMyAdmin</h2>

<p>We were able to get our phpMyAdmin interface up and running fairly easily.  However, we are not done yet.  Because of its ubiquity, phpMyAdmin is a popular target for attackers.  We need to secure the application to help prevent unauthorized use.</p>

<p>One of the easiest way of doing this is to place a gateway in front of the entire application.  We can do this using Apache's built-in <code>.htaccess</code> authentication and authorization functionalities.</p>

<h3 id="configure-apache-to-allow-htaccess-overrides">Configure Apache to Allow .htaccess Overrides</h3>

<p>First, we need to enable the use of <code>.htaccess</code> file overrides by editing our Apache configuration file.</p>

<p>We will edit the linked file that has been placed in our Apache configuration directory:</p>
<pre class="code-pre "><code langs="">sudo nano /etc/apache2/conf-available/phpmyadmin.conf
</code></pre>
<p>We need to add an <code>AllowOverride All</code> directive within the <code><Directory /usr/share/phpmyadmin></code> section of the configuration file, like this:</p>
<pre class="code-pre "><code langs=""><Directory /usr/share/phpmyadmin>
    Options FollowSymLinks
    DirectoryIndex index.php
    <span class="highlight">AllowOverride All</span>
    . . .
</code></pre>
<p>When you have added this line, save and close the file.</p>

<p>To implement the changes you made, restart Apache:</p>
<pre class="code-pre "><code langs="">sudo service apache2 restart
</code></pre>
<h3 id="create-an-htaccess-file">Create an .htaccess File</h3>

<p>Now that we have enabled <code>.htaccess</code> use for our application, we need to create one to actually implement some security.</p>

<p>In order for this to be successful, the file must be created within the application directory.  We can create the necessary file and open it in our text editor with root privileges by typing:</p>
<pre class="code-pre "><code langs="">sudo nano /usr/share/phpmyadmin/.htaccess
</code></pre>
<p>Within this file, we need to enter the following information:</p>
<pre class="code-pre "><code langs="">AuthType Basic
AuthName "Restricted Files"
AuthUserFile /etc/phpmyadmin/.htpasswd
Require valid-user
</code></pre>
<p>Let's go over what each of these lines mean:</p>

<ul>
<li><strong>AuthType Basic</strong>: This line specifies the authentication type that we are implementing.  This type will implement password authentication using a password file.</li>
<li><strong>AuthName</strong>: This sets the message for the authentication dialog box.  You should keep this generic so that unauthorized users won't gain any information about what is being protected.</li>
<li><strong>AuthUserFile</strong>: This sets the location of the password file that will be used for authentication.  This should be outside of the directories that are being served.  We will create this file shortly.</li>
<li><strong>Require valid-user</strong>: This specifies that only authenticated users should be given access to this resource.  This is what actually stops unauthorized users from entering.</li>
</ul>

<p>When you are finished, save and close the file.</p>

<h3 id="create-the-htpasswd-file-for-authentication">Create the .htpasswd file for Authentication</h3>

<p>Now that we have specified a location for our password file through the use of the <code>AuthUserFile</code> directive within our <code>.htaccess</code> file, we need to create this file.</p>

<p>We actually need an additional package to complete this process.  We can install it from our default repositories:</p>
<pre class="code-pre "><code langs="">sudo apt-get update
sudo apt-get install apache2-utils
</code></pre>
<p>Afterward, we will have the <code>htpasswd</code> utility available.</p>

<p>The location that we selected for the password file was "<code>/etc/phpmyadmin/.htpasswd</code>".  Let's create this file and pass it an initial user by typing:</p>
<pre class="code-pre "><code langs="">sudo htpasswd -c /etc/phpmyadmin/.htpasswd <span class="highlight">username</span>
</code></pre>
<p>You will be prompted to select and confirm a password for the user you are creating.  Afterwards, the file is created with the hashed password that you entered.</p>

<p>If you want to enter an additional user, you need to do so <strong>without</strong> the <code>-c</code> flag, like this:</p>
<pre class="code-pre "><code langs="">sudo htpasswd /etc/phpmyadmin/.htpasswd <span class="highlight">additionaluser</span>
</code></pre>
<p>Now, when you access your phpMyAdmin subdirectory, you will be prompted for the additional account name and password that you just configured:</p>
<pre class="code-pre "><code langs="">http://<span class="highlight">domain_name_or_IP</span>/phpmyadmin
</code></pre>
<p><img src="https://assets.digitalocean.com/articles/phpmyadmin_1404/apache_auth.png" alt="phpMyAdmin apache password" /></p>

<p>After entering the Apache authentication, you'll be taken to the regular phpMyAdmin authentication page to enter your other credentials.  This will add an additional layer of security since phpMyAdmin has suffered from vulnerabilities in the past.</p>

<h2 id="conclusion">Conclusion</h2>

<p>You should now have a configured and secured PHPMyAdmin instance and MySQL database ready to use.  Since your droplet includes a full LAMP stack you can deploy web content to your droplet easily by placing files in your <code>/var/www/html</code> directory.  More information on using PHPMyAdmin can be found in the official documenation from the PHPMyAdmin Project.</p>

<ul>
<li><a href="http://docs.phpmyadmin.net/en/latest/">Official PHPMyAdmin Documentation</a></li>
</ul>

    