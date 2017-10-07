<link rel='stylesheet' href='digital_ocean.css'><br> <br> 
      <h3 id="about-contao">About Contao</h3>

<p><a href="https://contao.org/en/">Contao</a> is an open source content management system (CMS) for websites of any size. It is a flexible and scalable system that implements high security, accessibility, and SEO standards. Contao is modular as you can use hundreds of additional extensions to add functionality to your site.</p>

<p>It is built using modern PHP object-oriented programming and the MooTools JavaScript framework. Additionally, Contao has an intuitive interface that uses Ajax for a great user experience. </p>

<p>In this article we will install Contao on our VPS running Ubuntu 12.04. For this, I assume you already have your VPS set up and that you are running the LAMP stack (Linux, Apache, MySQL, PHP). If you don't already, there's a <a href="https://indiareads/community/articles/how-to-install-linux-apache-mysql-php-lamp-stack-on-ubuntu">great tutorial</a> on IndiaReads that can get you set up.</p>

<h2 id="prerequistes">Prerequistes</h2>

<p>Before we download Contao, we'll need to make sure that our Apache server will allow it to use the <code>.htaccess</code> file to rewrite its URLs. This is important for creating pretty and search engine friendly URLs. The following steps are necessary only if your virtual server has yet to be configured in this way.</p>

<p>In this tutorial, we will install Contao into the root folder of our Apache server (<code>/var/www</code>). Edit the virtual host file that is responsible for this folder:</p>
<pre class="code-pre "><code langs="">sudo nano /etc/apache2/sites-available/default
</code></pre>
<p>Inside the block marked with this beginning:</p>
<pre class="code-pre "><code langs=""><Directory /var/www/>
</code></pre>
<p>Make sure that instead of <code>AllowOverride None</code> you have <code>AllowOverride All</code>. </p>

<p>The next thing we need to do is enable <code>mod_rewrite</code> (again if you don't already have it enabled). To check if it's already enabled, use the following command:</p>
<pre class="code-pre "><code langs="">apache2ctl -M
</code></pre>
<p>If you see "rewrite_module" in the list, you are fine. If not, use the following command to enable the module:</p>
<pre class="code-pre "><code langs="">a2enmod rewrite 
</code></pre>
<p>After making any changes to either the virtual host file or enabling an Apache module, you have to restart Apache:</p>
<pre class="code-pre "><code langs="">sudo service apache2 restart
</code></pre>
<h2 id="download">Download</h2>

<p>Before we download, let's switch to a user that is not <code>root</code>. If you don't already have another user, go ahead and create one. </p>

<p>Let's create a user called <code>contao</code>:</p>
<pre class="code-pre "><code langs="">useradd contao
passwd contao
</code></pre>
<p>And then specify the password. Go ahead also and create the home folder for this user if it doesn't already exist:</p>
<pre class="code-pre "><code langs="">mkdir /home/contao
</code></pre>
<p>And add the user to the sudo group:</p>
<pre class="code-pre "><code langs="">sudo adduser contao sudo
</code></pre>
<p>Now log out of your box and ssh back into it using this new user.</p>

<p>Now we can proceed to the downloading of the Contao source file. Let's first navigate to the folder we want to install it in:</p>
<pre class="code-pre "><code langs="">cd /var/www
</code></pre>
<p>Next, we can run a command to automatically download the tarball containing the latest version of Contao and untar it:</p>
<pre class="code-pre "><code langs="">sudo curl -L http://download.contao.org | sudo tar -xzp
</code></pre>
<p>Now if you look in the <code>/var/www</code> folder you should see a directory called <code>core-master</code>. Change its owner to the <code>contao</code> user:</p>
<pre class="code-pre "><code langs="">sudo chown -R contao core-master
</code></pre>
<p>We'll move its contents one folder up to the web server root folder:</p>
<pre class="code-pre "><code langs="">sudo mv core-master/* /var/www
sudo mv core-master/.gitignore /var/www
sudo mv core-master/.gitattributes /var/www
sudo mv core-master/.htaccess.default /var/www
</code></pre>
<p>And then delete the superfluous <code>core-master</code> folder:</p>
<pre class="code-pre "><code langs="">sudo rmdir core-master
</code></pre>
<h2 id="database">Database</h2>

<p>Before proceeding with the installation itself, we'll need to create a new database for Contao to use. I will guide you through some quick steps to set up your MySQL database, but there is a <a href="https://indiareads/community/articles/how-to-install-linux-apache-mysql-php-lamp-stack-on-ubuntu">good tutorial</a> for more information.</p>

<p>The first thing you need to do is log in to MySQL from your terminal (you can use PHPMyAdmin as well if you want but I will show you how to do it from the command line):</p>
<pre class="code-pre "><code langs="">mysql -u `username` -p`password`
</code></pre>
<p>From there, run the follwing command to create a database called <code>contao</code>:</p>
<pre class="code-pre "><code langs="">create database contao;
</code></pre>
<p>You can change its name to something else if you want. And that's pretty much it. When we soon run the installer for Contao, you'll specify the information to connect to this database. </p>

<h2 id="install">Install</h2>

<p>Contao has a nice web installation tool that we can use to install it. But let's take care of some permissions first.</p>

<p>Change the ownership of the following folders to your user and the <code>www-data</code> group:</p>
<pre class="code-pre "><code langs="">sudo chown -R contao:www-data assets/images
sudo chown -R contao:www-data system/logs
sudo chown -R contao:www-data system/tmp
</code></pre>
<p>Next, set the permissions to the www-data group to be able to write to these folders:</p>
<pre class="code-pre "><code langs="">sudo chmod -R 775 assets/images
sudo chmod -R 775 system/logs
sudo chmod -R 775 system/tmp
</code></pre>
<p>Now you can proceed to the following URL to access the installer.</p>
<pre class="code-pre "><code langs="">http://your-ip/contao/install.php
</code></pre>
<p>The first screen on the installer will ask for the FTP credentials, which it will use to write in the <code>system/config</code> folder. You can provide them there. </p>

<p>If you don't already have FTP set up on your virtual server, you can read <a href="https://indiareads/community/articles/what-is-ftp-and-how-is-it-used">this tutorial</a> to get you started. I will quickly show you how to set up VSFTPD.</p>

<p>Run the following commands to install VSFTPD:</p>
<pre class="code-pre "><code langs="">sudo apt-get update
sudo apt-get install vsftpd
</code></pre>
<p>Now you have FTP on your VPS. Don't forget to disable access to anonymous user. (You can find more information in the linked article on how to do that).</p>

<p>But one thing you'll need to do is edit the configuration file:</p>
<pre class="code-pre "><code langs="">sudo nano /etc/vsftpd.conf
</code></pre>
<p>And uncomment these lines to allow local users to access FTP:</p>
<pre class="code-pre "><code langs="">local_enable=YES
write_enable=YES
</code></pre>
<p>Then restart VSFTPD:</p>
<pre class="code-pre "><code langs="">sudo service vsftpd restart 
</code></pre>
<p>Once you successfully passed the screen with the FTP credentials, read and accept the license. On the next screen you have to specify a password (make sure it is 8 letters long). Following that, you'll have to specify the database credentials. If the connection is successful, you can click on the <code>update database</code> button for the installer to create the necessary tables in your database. Then create an administrator user account to finalize the installation process. </p>

<p>You can then proceed to your Contao backend at <code>http://your-ip/contao/</code> and log in with the account you just created and make sure everything works normally. You'll probably notice a <code>Build Cache</code> button once you're logged in that you should click for Contao to build up its cache. </p>

<p>One final thing we need to do is rename the <code>.htaccess.default</code> file in the Contao root folder to simply <code>.htaccess</code>:</p>
<pre class="code-pre "><code langs="">mv /var/www/.htaccess.default /var/www/.htaccess
</code></pre>
<p>Congratulations! You have installed Contao onto your cloud server.</p>

<div class="author">Submitted by: <a href="http://www.webomelette.com/">Danny Sipos</a></div>

    