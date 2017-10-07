<link rel='stylesheet' href='digital_ocean.css'><br> <br> 
      <h2 id="introduction">Introduction</h2>

<hr />

<p>Laravel is a framework for websites in the PHP programming language. It allows developers to rapidly develop a website by abstracting common tasks used in most web projects, such as authentication, sessions, and caching. Laravel 4, the newest version of Laravel is based on an older framework called Symfony, but with a more expressive syntax. It is installed using Composer, a Dependency Manager, allowing developers to integrate even more open source PHP projects in a web project. If you want to want to read a quick introduction to Laravel, read the <a href="http://laravel.com/docs/introduction">introduction</a>. If you want to learn more about Composer, visit the <a href="http://www.getcomposer.org/">website</a>.</p>

<h3 id="preparation">Preparation</h3>

<p>Let's start off by updating the packages installed on your VPS. This makes sure no issues will arise on incompatible versions of the software. Also, make sure you run everything in this tutorial as root, and if you don't, make sure you add <code>sudo</code> before every command!</p>
<pre class="code-pre "><code langs="">apt-get update && apt-get upgrade
</code></pre>
<p>Hit Enter when you're asked to confirm.</p>

<h2 id="installation">Installation</h2>

<hr />

<p>Now we need to install the actual packages required to install Laravel. This will basically be Nginx and PHP. Because Composer is run from the command line, we do need <code>php5-cli</code>, and because we want to manage the connection between Nginx and PHP using the FastCGI Process Manager, we will need <code>php5-fpm</code> as well. Besides, Laravel requires <code>php5-mcrypt</code> and Composer requires <code>git</code>.</p>
<pre class="code-pre "><code langs="">apt-get install nginx php5-fpm php5-cli php5-mcrypt git
</code></pre>
<p>This should take a while to install, but you are now ready to configure Nginx and PHP.</p>

<h3 id="configuring-nginx">Configuring Nginx</h3>

<p>We will configure Nginx like Laravel is the only website you will run on it, basically accepting every HTTP request, no matter what the Host header contains. If you want more than one website on your VPS, please refer to <a href="https://indiareads/community/articles/how-to-set-up-nginx-virtual-hosts-server-blocks-on-ubuntu-12-04-lts--3">this tutorial</a>.</p>

<p>Make a dedicated folder for your Laravel website:</p>
<pre class="code-pre "><code langs="">mkdir /var/www
mkdir /var/www/laravel
</code></pre>
<p>Open up the default virtual host file.</p>
<pre class="code-pre "><code langs="">nano /etc/nginx/sites-available/default
</code></pre>
<p>The configuration should look like below:</p>
<pre class="code-pre "><code langs="">server {
        listen   80 default_server;

        root /var/www/laravel/public/;
        index index.php index.html index.htm;

        location / {
             try_files $uri $uri/ /index.php$is_args$args;
        }

        # pass the PHP scripts to FastCGI server listening on /var/run/php5-fpm.sock
        location ~ \.php$ {
                try_files $uri /index.php =404;
                fastcgi_pass unix:/var/run/php5-fpm.sock;
                fastcgi_index index.php;
                fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
                include fastcgi_params;
        }
}
</code></pre>
<p>Now save and exit!</p>

<h3 id="configuring-php">Configuring PHP</h3>

<p>We need to make a small change in the PHP configuration. Open the php.ini file:</p>
<pre class="code-pre "><code langs="">nano /etc/php5/fpm/php.ini
</code></pre>
<p>Find the line, cgi.fix_pathinfo=1, and change the 1 to 0.</p>
<pre class="code-pre "><code langs="">cgi.fix_pathinfo=0
</code></pre>
<p>If this number is kept as 1, the PHP interpreter will do its best to process the file that is as near to the requested file as possible. This is a possible security risk. If this number is set to 0, conversely, the interpreter will only process the exact file path — a much safer alternative. Now save it and exit nano. </p>

<p>We need to make another small change in the php5-fpm configuration. Open up www.conf:</p>
<pre class="code-pre "><code langs="">nano /etc/php5/fpm/pool.d/www.conf
</code></pre>
<p>Find the line, listen = 127.0.0.1:9000, and change the 127.0.0.1:9000 to /var/run/php5-fpm.sock.</p>
<pre class="code-pre "><code langs="">listen = /var/run/php5-fpm.sock
</code></pre>
<p>Again: save and exit!</p>

<h3 id="re-starting-php-and-nginx">(Re)Starting PHP and Nginx</h3>

<p>Now make sure that both services are restarted.</p>
<pre class="code-pre "><code langs="">service php5-fpm restart
service nginx restart
</code></pre>
<h3 id="installing-composer">Installing Composer</h3>

<p>It is now time to install Composer, this process is quite straightforward. Let's start off by downloading Composer:</p>
<pre class="code-pre "><code langs="">curl -sS https://getcomposer.org/installer | php
</code></pre>
<p>Now install it globally:</p>
<pre class="code-pre "><code langs="">mv composer.phar /usr/local/bin/composer
</code></pre>
<h3 id="installing-laravel">Installing Laravel</h3>

<p><strong>Heads Up:</strong> If you're installing Laravel on IndiaReads's 512MB VPS, make sure you add a swapfile to Ubuntu to prevent it from running out of memory. You can quickly do this by issuing the following commands. This will only work during 1 session, so if you reboot during this tutorial, add the swapfile again.</p>
<pre class="code-pre "><code langs="">dd if=/dev/zero of=/swapfile bs=1024 count=512k
mkswap /swapfile
swapon /swapfile
</code></pre>
<p>Finally, let's install Laravel.</p>
<pre class="code-pre "><code langs="">composer create-project laravel/laravel /var/www/laravel/ 4.1
</code></pre>
<h2 id="testing">Testing</h2>

<hr />

<p>Now browse to your cloud server's IP. You can find using:</p>
<pre class="code-pre "><code langs="">/sbin/ifconfig|grep inet|head -1|sed 's/\:/ /'|awk '{print $3}'
</code></pre>
<p>It will now show you an error! What? The permissions still need to be set on the folders used for caching. Ah! Let's do that now:</p>

<h3 id="fixing-permissions">Fixing permissions</h3>

<p>This is really quite a easy fix:</p>
<pre class="code-pre "><code langs="">chgrp -R www-data /var/www/laravel
chmod -R 775 /var/www/laravel/app/storage
</code></pre>
<h2 id="wrap-up">Wrap Up</h2>

<hr />

<p>So that it, you can now enjoy Laravel running on a fast Nginx backend! If you want to use MySQL on your Laravel installation, it is extremely easy: just issue <code>apt-get install mysql-server</code> and MySQL will be installed right away. For more information on using Laravel visit the <a href="http://laravel.com/">website</a>. Happy developing!</p>

<div class="author">Submitted by: Wouter ten Bosch</div>

    