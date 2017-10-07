<link rel='stylesheet' href='digital_ocean.css'><br> <br> 
      <h2 id="introduction">Introduction</h2>

<p>A LAMP stack is a group of open source software used to get web servers up and running. The acronym stands for Linux, Apache, MySQL, and PHP. Since the server is already running Fedora, the Linux part is taken care of. Here is how to install the rest. </p>

<h2 id="prerequisites">Prerequisites</h2>

<p>Before beginning this tutorail you should have a running Fedora 22 droplet and be logged in via SSH.</p>

<h2 id="setup">Setup</h2>

<p>Before you start installing the LAMP programs, you should first download and install all of the updates with dnf update dnf replaced yum as the default package manager for Fedora in version 22:</p>
<pre class="code-pre "><code langs="">sudo dnf update
</code></pre>
<h2 id="step-one—install-apache">Step One—Install Apache</h2>

<p>Apache is a free open source software which runs over 50% of the world’s web servers.</p>

<p>To install apache, open terminal and type in this command:</p>
<pre class="code-pre "><code langs="">sudo dnf install httpd
</code></pre>
<p>Once it installs, you can start apache running on your VPS:</p>
<pre class="code-pre "><code langs="">sudo systemctl start httpd.service
</code></pre>
<p>That’s it. To check if Apache is installed, direct your browser to your server’s IP address (eg. http://12.34.56.789). You should see the default Fedora page<br />
<img src="https://assets.digitalocean.com/articles/fedora-lamp/default.png" alt="Fedora Default" /></p>

<h3 id="how-to-find-your-droplet’s-ip-address">How to find your Droplet’s IP address</h3>

<p>You can run the following command to reveal your server’s IP address.</p>
<pre class="code-pre "><code langs="">ifconfig eth0 | grep inet | awk '{ print $2 }'
</code></pre>
<h2 id="step-two—install-mysql">Step Two—Install MySQL</h2>

<p>MySQL/MariaDB is a powerful database management system used for organizing and retrieving data on a virtual server</p>

<p>To install MySQL, open terminal and type in these commands:</p>
<pre class="code-pre "><code langs="">sudo dnf install mysql mysql-server
sudo systemctl start mariadb.service
</code></pre>
<p>Once it is done installing, you can set a root MySQL password:</p>
<pre class="code-pre "><code langs="">sudo /usr/bin/mysql_secure_installation
</code></pre>
<p>The prompt will ask you for your current root password. </p>

<p>Since you just installed MySQL, you most likely won’t have one, so leave it blank by pressing enter.</p>
<pre class="code-pre "><code langs="">Enter current password for root (enter for none): 
OK, successfully used password, moving on...
</code></pre>
<p>Then the prompt will ask you if you want to set a root password. Go ahead and choose Y and follow the instructions. </p>

<p>Fedora automates the process of setting up MySQL, asking you a series of yes or no questions.   </p>

<p>It’s easiest just to say Yes to all the options. At the end, MySQL will reload and implement the new changes.</p>
<pre class="code-pre "><code langs=""><pre>By default, a MariaDB installation has an anonymous user, allowing anyone
to log into MariaDB without having to have a user account created for
them.  This is intended only for testing, and to make the installation
go a bit smoother.  You should remove them before moving into a
production environment.

Remove anonymous users? [Y/n] Y
 ... Success!

Normally, root should only be allowed to connect from 'localhost'.  This
ensures that someone cannot guess at the root password from the network.

Disallow root login remotely? [Y/n] Y
 ... Success!

By default, MariaDB comes with a database named 'test' that anyone can
access.  This is also intended only for testing, and should be removed
before moving into a production environment.

Remove test database and access to it? [Y/n] Y
 - Dropping test database...
 ... Success!
 - Removing privileges on test database...
 ... Success!

Reloading the privilege tables will ensure that all changes made so far
will take effect immediately.

Reload privilege tables now? [Y/n] Y
 ... Success!

Cleaning up...

All done!  If you've completed all of the above steps, your MariaDB
installation should now be secure.

Thanks for using MariaDB!

</code></pre>
<h2 id="step-three—install-php">Step Three—Install PHP</h2>

<p>PHP is an open source web scripting language that is widely used to build dynamic web pages.</p>

<p>To install PHP on your virtual private server, open terminal and type in this command:</p>
<pre class="code-pre "><code langs="">sudo dnf install php php-mysql
</code></pre>
<p>Once you answer yes to the PHP prompt, PHP will install itself.</p>

<h3 id="php-modules">PHP Modules</h3>

<p>PHP also has a variety of useful libraries and modules that you can add onto your server. You can see the libraries that are available by typing:</p>
<pre class="code-pre "><code langs="">dnf search php-
</code></pre>
<p>The terminal then will display the list of possible modules.  The beginning looks like this:</p>
<pre class="code-pre "><code langs="">php-fpdf-doc.noarch : Documentation for php-fpdf
php-libvirt-doc.noarch : Document of php-libvirt
php-pear-Auth-radius.noarch : RADIUS support for php-pear-Auth
php-pear-Auth-samba.noarch : Samba support for php-pear-Auth
ice-php-devel.i686 : PHP tools for developping Ice applications
ice-php-devel.x86_64 : PHP tools for developping Ice applications
perl-PHP-Serialization.noarch : Converts between PHP's serialize() output and
                              : the equivalent Perl structure
php-IDNA_Convert.noarch : Provides conversion of internationalized strings to
                        : UTF8
php-Kohana.noarch : The Swift PHP Framework
php-LightweightPicasaAPI.noarch : A lightweight API for Picasa in PHP
php-PHPMailer.noarch : PHP email transport class with a lot of features
php-Smarty.noarch : Template/Presentation Framework for PHP
php-ZendFramework.noarch : Leading open-source PHP framework
php-ZendFramework-Auth-Adapter-Ldap.noarch : Zend Framework LDAP
                                           : Authentication Adapter
php-ZendFramework-Cache-Backend-Apc.noarch : Zend Framework APC cache backend
</code></pre>
<p>To see more details about what each module does, type the following command into terminal, replacing the name of the module with whatever library you want to learn about. </p>
<pre class="code-pre "><code langs="">dnf info <span class="highlight">name of the module</span>
</code></pre>
<p>Once you decide to install the module, type:</p>
<pre class="code-pre "><code langs="">sudo dnf install <span class="highlight">name of the module</span>
</code></pre>
<p>You can install multiple libraries at once by separating the name of each module with a space.</p>

<p>Congratulations! You now have LAMP stack on your droplet!</p>

<p>We should also set the processes to run automatically when the server boots (php will run automatically once Apache starts):</p>
<pre class="code-pre "><code langs="">sudo chkconfig httpd on
sudo chkconfig mariadb on
</code></pre>
<h2 id="step-four—results-see-php-on-your-server">Step Four—RESULTS: See PHP on your Server</h2>

<p>Although LAMP is installed on your virtual server, we can still take a look and see the components online by creating a quick php info page</p>

<p>To set this up, first install the nano text editor and create a new file:</p>
<pre class="code-pre "><code langs="">sudo dnf install nano
sudo nano /var/www/html/info.php
</code></pre>
<p>Add in the following line:</p>
<pre class="code-pre "><code langs=""><?php
phpinfo();
?>
</code></pre>
<p>Then Save and Exit. </p>

<p>Restart apache so that all of the changes take effect on your virtual server:</p>
<pre class="code-pre "><code langs="">sudo systemctl restart httpd.service
</code></pre>
<p>Finish up by visiting your php info page (make sure you replace the example ip address with your correct one): http://12.34.56.789/info.php</p>

<p>It should look similar to this:</p>

<p><img src="https://assets.digitalocean.com/articles/fedora-lamp/phpinfo.png" alt="PHP Info" /></p>

    