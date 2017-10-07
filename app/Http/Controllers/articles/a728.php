<link rel='stylesheet' href='digital_ocean.css'><br> <br> 
      <h3 id="about-octobercms">About OctoberCMS</h3>

<p><a href="http://octobercms.com/">OctoberCMS</a> is a relatively new open-source CMS based on the Laravel PHP framework. It has a number of attractive features – especially for developers – that can be explored by visiting <a href="http://octobercms.com/features">this page</a>.</p>

<p>In this tutorial we are going to install OctoberCMS on a VPS running Ubuntu 14.04. There are two ways you can install OctoberCMS: via the wizard and via the command line. We'll look at installing it using the second method. </p>

<h2 id="requirements">Requirements</h2>

<p>To install OctoberCMS, you'll need to meet a few system requirements. You'll need to have the LAMP stack (Linux, Apache, MySQP, PHP) installed, but Nginx and Lighttpd are also acceptable web servers. The PHP version needs to be 5.4+ with <code>safe_mode</code> restrictions disabled. Ubuntu 14.04 comes with a version of PHP 5.5 so you shouldn't have any problems with that. </p>

<p>You can read this <a href="https://indiareads/community/articles/how-to-install-linux-apache-mysql-php-lamp-stack-on-ubuntu-14-04">great tutorial</a> on how to install LAMP on Ubuntu 14.04 if you don't already have it set up.</p>

<p>Since we are using Apache as a webserver and October can make use of URL rewriting, we'll need to also make sure that Apache will in fact let it do that. If you haven't already done the following steps, you'll need to do them now. </p>

<p>Edit the virtual host file that is responsible for the folder where October will be installed (in our case, the default Apache document root: /var/www/html):</p>
<pre class="code-pre "><code langs="">sudo nano /etc/apache2/sites-available/000-default.conf
</code></pre>
<p>Within the block contained by the starting:</p>
<pre class="code-pre "><code langs=""><VirtualHost *:80>
</code></pre>
<p>Add the following block:</p>
<pre class="code-pre "><code langs=""><Directory "/var/www/html">
    AllowOverride All
</Directory>
</code></pre>
<p>Next thing we need to do is enable <code>mod_rewrite</code> (again, if you don't already have it enabled). To check if it's already enabled, use the following command:</p>
<pre class="code-pre "><code langs="">apache2ctl -M
</code></pre>
<p>If you see "rewrite_module" in the list, you are fine. If not, use the following command to enable the module:</p>
<pre class="code-pre "><code langs="">a2enmod rewrite 
</code></pre>
<p>OctoberCMS also needs the cURL extension installed, so run the following command to do that:</p>
<pre class="code-pre "><code langs="">sudo apt-get install curl php5-curl
</code></pre>
<p>Then you should restart the Apache server in order for the changes to take effect:</p>
<pre class="code-pre "><code langs="">sudo service apache2 restart
</code></pre>
<h2 id="installation">Installation</h2>

<p>For installing via the command line, we will need Composer. If you don't know how to work with it, or have not yet set it up, consult <a href="https://indiareads/community/articles/how-to-install-and-use-composer-on-your-vps-running-ubuntu">this tutorial</a> that will get you going. Additionally, you'll need Git installed on the system; if you don't already, go ahead and run this command:</p>
<pre class="code-pre "><code langs="">sudo apt-get install git-core
</code></pre>
<p>Now we can proceed with the installation. I said above that we will install October in the Apache web root (<code>/var/www/html</code>). So first, remove all the files in that folder. This is of course only if OctoberCMS is the only application you want in the web server's root folder. After you made sure you have the Composer.phar file in the <code>/var/www</code> folder and you navigate to it, run the following command:</p>
<pre class="code-pre "><code langs="">php composer.phar create-project october/october html dev-master
</code></pre>
<p>What this will do is clone October from the repository and create a new project in the <code>html/</code> folder. </p>

<h2 id="setup">Setup</h2>

<p>The next thing we need to do is modify a few files. Open the <code>app/config/app.php</code> file and where you find this line:</p>
<pre class="code-pre "><code langs="">'url' => 'http://yourwebsite.com'
</code></pre>
<p>Change the path to your own site. Let's say <code>http://example.com</code> (for later referencing in this tutorial). </p>

<p>Additionally, you should also modify this line:</p>
<pre class="code-pre "><code langs="">'key' => 'UNIQUE_ENCRYPTION_KEY'
</code></pre>
<p>In order to pick an encryption key October will use. </p>

<p>Optionally, editing the <code>app/config/cms.php</code> file will allow you to change what the theme of the site is, which modules are loaded, and even customize the URI of the backend. </p>

<h2 id="database">Database</h2>

<p>Next, let's set up a database for October to use. I will guide you through some quick steps to set up your MySQL database, but there is a <a href="https://indiareads/community/articles/how-to-install-linux-apache-mysql-php-lamp-stack-on-ubuntu">great tutorial here</a> for more information.</p>

<p>The first thing you need to do is log in to mysql from your terminal (you can use PHPMyAdmin as well, but I will show you how to do it from the command line):</p>
<pre class="code-pre "><code langs="">mysql -u username -ppassword
</code></pre>
<p>From there, run the following command to create a database called <code>october</code>:</p>
<pre class="code-pre "><code langs="">create database october;
</code></pre>
<p>You can of course change its name to something else if you want. And that's pretty much it. Next, edit the <code>app/config/database.php</code> file and under the MySQL connection block specify your database credentials where appropriate. Finally, it's time to run the console command that will set up the October database:</p>
<pre class="code-pre "><code langs="">php artisan october:up
</code></pre>
<p>Make sure you run this command from within the October root folder and if you get the following notice:</p>
<pre class="code-pre "><code langs="">Mcrypt PHP extension required
</code></pre>
<p>Run the following command to install it:</p>
<pre class="code-pre "><code langs="">sudo apt-get install php5-mcrypt
</code></pre>
<p>Then you'll need to enable this extension manually. Edit the php.ini file:</p>
<pre class="code-pre "><code langs="">vi /etc/php5/apache2/php.ini
</code></pre>
<p>And inside at the following line:</p>
<pre class="code-pre "><code langs="">extension=mcrypt.so
</code></pre>
<p>Then navigate to <code>/etc/php5/apache2</code> and if you do not have a <code>conf.d</code> folder in there, create one:</p>
<pre class="code-pre "><code langs="">sudo mkdir conf.d
</code></pre>
<p>And inside that folder create a file called <code>mcrypt.ini</code> with the following content in it:</p>
<pre class="code-pre "><code langs="">extension=mcrypt.so
</code></pre>
<p>Then create a link between that file and the available PHP modules by running this command:</p>
<pre class="code-pre "><code langs="">sudo ln -s /etc/php5/apache2/conf.d/mcrypt.ini /etc/php5/mods-available
</code></pre>
<p>And enable the module:</p>
<pre class="code-pre "><code langs="">sudo php5enmod mcrypt
</code></pre>
<p>Followed by restarting Apache:</p>
<pre class="code-pre "><code langs="">sudo service apache2 restart
</code></pre>
<p>Creating the <code>conf.d</code> folder is necessary only if it's not there already with the <code>mcrypt.ini</code> file (that you have to link to the available modules folder). If it's already there, skip the step and perform the linking directly. </p>

<p>And now you can run the <code>php artisan october:up</code> command again to set up the database which should be successful.</p>

<h2 id="permissions">Permissions</h2>

<p>In order for OctoberCMS to run, some folders need to be writable by the web server. So let's change their ownership to the <code>www-data</code> group which includes the <code>www-data</code> user (Apache) and make it so that this group can write in these folders.</p>
<pre class="code-pre "><code langs="">sudo chown -R root:www-data app/storage
sudo chown -R root:www-data themes
sudo chown -R root:www-data uploads

sudo chmod -R 775 app/storage/
sudo chmod -R 775 themes
sudo chmod -R 775 uploads
</code></pre>
<p>Make sure you run these commands from within the OctoberCMS root folder and keep in mind that with this command we are making the owner of the files the <code>root</code> user. If you are using another user, just replace that username. </p>

<p>And that should be it. You can now navigate to <code>http://example.com</code> where you should see your brand new installation of OctoberCMS. To log in the backed at <code>http://example.com/backend</code> (by default), you can use the username <code>admin</code> and password <code>admin</code>. </p>

<div class="author">Submitted by: <a href="http://www.webomelette.com/">Danny Sipos</a></div>

    