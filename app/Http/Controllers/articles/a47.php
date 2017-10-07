<link rel='stylesheet' href='digital_ocean.css'><br> <br> 
      <h3 id="introduction">Introduction</h3>

<p>The LAMP stack (Linux, Apache, MySQL, PHP) is a group of open source software that is typically installed together to enable a server to host dynamic PHP websites and web apps. This guide includes the steps to set up a LAMP stack on Ubuntu 14.04, on a single server, so you can quickly get your PHP application up and running.</p>

<p>A more detailed version of this tutorial, with better explanations of each step, can be found <a href="https://indiareads/community/tutorials/how-to-install-linux-apache-mysql-php-lamp-stack-on-ubuntu-14-04">here</a>.</p>

<h2 id="step-1-update-apt-get-package-lists">Step 1: Update apt-get package lists</h2>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo apt-get update
</li></ul></code></pre>
<h2 id="step-2-install-apache-mysql-and-php-packages">Step 2: Install Apache, MySQL, and PHP packages</h2>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo apt-get -y install apache2 mysql-server php5-mysql php5 libapache2-mod-php5 php5-mcrypt
</li></ul></code></pre>
<p>When prompted, set and confirm a new password for the MySQL "root" user:</p>

<p><img src="https://assets.digitalocean.com/articles/lamp_1404/mysql_password.png" alt="Set MySQL root password" /></p>

<h2 id="step-3-create-mysql-database-directory-structure">Step 3: Create MySQL database directory structure</h2>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo mysql_install_db
</li></ul></code></pre>
<h2 id="step-4-run-basic-mysql-security-script">Step 4: Run basic MySQL security script</h2>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo mysql_secure_installation
</li></ul></code></pre>
<p>At the prompt, enter the password you set for the MySQL root account:</p>
<pre class="code-pre "><code langs=""><div class="secondary-code-label " title="MySQL root password prompt:">MySQL root password prompt:</div>Enter current password for root (enter for none):
OK, successfully used password, moving on...
</code></pre>
<p>At the next prompt, if you are happy with your current MySQL root password, type "n" for "no":</p>
<pre class="code-pre "><code langs=""><div class="secondary-code-label " title="MySQL root password prompt:">MySQL root password prompt:</div>Change the root password? [Y/n] <span class="highlight">n</span>
</code></pre>
<p>For the remaining prompts, simply hit the "ENTER" key to accept the default values.</p>

<h2 id="step-5-configure-apache-to-prioritize-php-files-optional">Step 5: Configure Apache to prioritize PHP files (optional)</h2>

<p>Open Apache's <code>dir.conf</code> file in a text editor:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo nano /etc/apache2/mods-enabled/dir.conf
</li></ul></code></pre>
<p>Edit the <code>DirectoryIndex</code> directive by moving <code>index.php</code> to the first item in the list, so it looks like this:</p>
<div class="code-label " title="dir.conf — updated DirectoryIndex">dir.conf — updated DirectoryIndex</div><pre class="code-pre "><code langs="">DirectoryIndex <span class="highlight">index.php</span> index.html index.cgi index.pl index.xhtml index.htm
</code></pre>
<p>Save and exit.</p>

<p>Restart Apache to put the change into place:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo service apache2 restart
</li></ul></code></pre>
<h2 id="step-6-test-php-processing-optional">Step 6: Test PHP processing (optional)</h2>

<p>Create a basic test PHP script in <code>/var/www/html</code>:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">echo '<?php phpinfo(); ?>' | sudo tee /var/www/html/info.php
</li></ul></code></pre>
<p>Open the PHP script in a web browser. Replace <span class="highlight">your_server_IP_address</span> with your server's public IP address:</p>
<pre class="code-pre "><code langs=""><div class="secondary-code-label " title="Visit in a web browser:">Visit in a web browser:</div>http://<span class="highlight">your_server_IP_address</span>/info.php
</code></pre>
<p>If you see a PHP info page, PHP processing is working:</p>

<p><img src="https://assets.digitalocean.com/articles/lamp_1404/phpinfo.png" alt="Example PHP info page" /></p>

<p>Delete the test PHP script:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo rm /var/www/html/info.php
</li></ul></code></pre>
<h2 id="related-tutorials">Related Tutorials</h2>

<p>Here are links to more detailed tutorials that are related to this guide:</p>

<ul>
<li><a href="https://indiareads/community/tutorials/how-to-install-linux-apache-mysql-php-lamp-stack-on-ubuntu-14-04">How To Install Linux, Apache, MySQL, PHP (LAMP) stack on Ubuntu 14.04</a></li>
<li><a href="https://indiareads/community/tutorials/how-to-install-linux-nginx-mysql-php-lemp-stack-on-ubuntu-14-04">How To Install Linux, nginx, MySQL, PHP (LEMP) stack on Ubuntu 14.04</a></li>
</ul>

    