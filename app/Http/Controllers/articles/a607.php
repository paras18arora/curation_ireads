<link rel='stylesheet' href='digital_ocean.css'><br> <br> 
      <h3 id="introduction">Introduction</h3>

<p>It's well known that the LEMP stack (Linux, nginx, MySQL, PHP) provides unmatched speed and reliability for running PHP sites. Other benefits of this popular stack such as security and isolation are less popular, though. </p>

<p>In this article we'll show you the security and isolation benefits of running sites on LEMP with different Linux users. This will be done by creating different php-fpm pools for each nginx server block (site or virtual host).</p>

<h2 id="prerequisites">Prerequisites</h2>

<p>This guide has been tested on Ubuntu 14.04. The described installation and configuration would be similar on other OS or OS versions, but the commands and location of configuration files may vary.</p>

<p>It also assumes you already have nginx and php-fpm set up. If not, please follow step one and step three from the article <a href="https://indiareads/community/tutorials/how-to-install-linux-nginx-mysql-php-lemp-stack-on-ubuntu-14-04">How To Install Linux, nginx, MySQL, PHP (LEMP) stack on Ubuntu 14.04</a>. </p>

<p>All the commands in this tutorial should be run as a non-root user. If root access is required for the command, it will be preceded by <code>sudo</code>. If you don't already have that set up, follow this tutorial: <a href="https://indiareads/community/tutorials/initial-server-setup-with-ubuntu-14-04">Initial Server Setup with Ubuntu 14.04</a>.</p>

<p>You will also need a fully qualified domain name (fqdn) that points to the Droplet for testing in addition to the default <code>localhost</code>. If you don't have one at hand, you can use <code>site1.example.org</code>. Edit the <code>/etc/hosts</code> file with your favorite editor like this <code>sudo vim /etc/hosts</code> and add this line (replace <code>site1.example.org</code> with your fqdn if you are using it):</p>
<div class="code-label " title="/etc/hosts">/etc/hosts</div><pre class="code-pre "><code langs="">...
127.0.0.1 site1.example.org
... 
</code></pre>
<h2 id="reasons-to-secure-lemp-additionally">Reasons to Secure LEMP Additionally</h2>

<p>Under a common LEMP setup there is only one php-fpm pool which runs all PHP scripts for all sites under the same user. This poses two major problems:</p>

<ul>
<li>If a web application on one nginx server block, i.e. subdomain or separate site, gets compromised, all of the sites on this Droplet will be affected too. The attacker is able to read the configuration files, including database details, of the other sites or even alter their files. </li>
<li>If you want to give a user access to a site on your Droplet, you will be practically giving him access to all sites. For example, your developer needs to work on the staging environment. However, even with very strict file permissions you will be still giving him access to all the sites, including your main site, on the same Droplet.</li>
</ul>

<p>The above problems are solved in php-fpm by creating a different pool which runs under a different user for each site.</p>

<h2 id="step-1-— configuring-php-fpm">Step 1 — Configuring php-fpm</h2>

<p>If you have covered the prerequisites, then you should already have one functional website on the Droplet. Unless you have specified a custom fqdn for it, you should be able to access it under the fqdn <code>localhost</code> locally or by the IP of the droplet remotely. </p>

<p>Now we'll create a second site (site1.example.org) with its own php-fpm pool and Linux user.  </p>

<p>Let's start with creating the necessary user. For best isolation, the new user should have its own group. So first create the user group <code>site1</code>:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo groupadd site1
</li></ul></code></pre>
<p>Then please create an user site1 belonging to this group:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo useradd -g site1 site1
</li></ul></code></pre>
<p>So far the new user site1 does not have a password and cannot log in the Droplet. If you need to provide someone with direct access to the files of this site, then you should create a password for this user with the command <code>sudo passwd site1</code>. With the new user/password combination a user can log in remotely by ssh or sftp. For more info and security details check the article <a href="https://indiareads/community/questions/setup-a-secondary-ssh-sftp-user-with-limited-directory-access">Setup a secondary SSH/SFTP user with limited directory access</a>.</p>

<p>Next, create a new php-fpm pool for site1. A php-fpm pool in its very essence is just an ordinary Linux process which runs under certain user/group and listens on a Linux socket. It could also listen on an IP:port combination too but this would require more Droplet resources, and it's not the preferred method.</p>

<p>By default, in Ubuntu 14.04 every php-fpm pool should be configured in a file inside the directory <code>/etc/php5/fpm/pool.d</code>. Every file with the extensions <code>.conf</code> in this directory is automatically loaded in the php-fpm global configuration.</p>

<p>So for our new site let's create a new file <code>/etc/php5/fpm/pool.d/site1.conf</code>. You can do this with your favorite editor like this:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo vim /etc/php5/fpm/pool.d/site1.conf
</li></ul></code></pre>
<p>This file should contain:</p>
<div class="code-label " title="/etc/php5/fpm/pool.d/site1.conf">/etc/php5/fpm/pool.d/site1.conf</div><pre class="code-pre "><code langs="">[site1]
<span class="highlight">user = site1</span>
<span class="highlight">group = site1</span>
<span class="highlight">listen = /var/run/php5-fpm-site1.sock</span>
<span class="highlight">listen.owner = www-data</span>
<span class="highlight">listen.group = www-data</span>
<span class="highlight">php_admin_value[disable_functions] = exec,passthru,shell_exec,system</span>
<span class="highlight">php_admin_flag[allow_url_fopen] = off</span>
pm = dynamic
pm.max_children = 5
pm.start_servers = 2
pm.min_spare_servers = 1
pm.max_spare_servers = 3
chdir = /
</code></pre>
<p>In the above configuration note these specific options:</p>

<ul>
<li><code>[site1]</code> is the name of the pool. For each pool you have to specify a unique name.</li>
<li><code>user</code> and <code>group</code> stand for the Linux user and the group under which the new pool will be running.</li>
<li><code>listen</code> should point to a unique location for each pool. </li>
<li><code>listen.owner</code> and <code>listen.group</code> define the ownership of the listener, i.e. the socket of the new php-fpm pool. Nginx must be able to read this socket. That's why the socket is created with the user and group under which nginx runs - <code>www-data</code>.</li>
<li><code>php_admin_value</code> allows you to set custom php configuration values. We have used it to disable functions which can run Linux commands - <code>exec,passthru,shell_exec,system</code>. </li>
<li> <code>php_admin_flag</code> is similar to <code>php_admin_value</code>, but it is just a switch for boolean values, i.e. on and off. We'll disable the PHP function <code>allow_url_fopen</code> which allows a PHP script to open remote files and could be used by attacker. </li>
</ul>

<p><span class="note"><strong>Note:</strong> The above <code>php_admin_value</code> and <code>php_admin_flag</code> values could be also applied globally. However, a site may need them, and that's why by default they are not configured. The beauty of php-fpm pools is that it allows you to fine tune the security settings of each site. Furthermore, these options can be used for any other php settings, outside of the security scope, to further customize the environment of a site. <br /></span></p>

<p>The <code>pm</code> options are outside of the current security topic, but you should know that they allow you to configure the performance of the pool. </p>

<p>The <code>chdir</code> option should be <code>/</code> which is the root of the filesystem. This shouldn't be changed unless you use another important option <code>chroot</code>.</p>

<p>The option <code>chroot</code> is not included in the above configuration on purpose. It would allow you to run a pool in a jailed environment, i.e. locked inside a directory. This is great for security because you can lock the pool inside the web root of the site. However, this ultimate security will cause serious problems for any decent PHP application which relies on system binaries and applications such as Imagemagick, which will not be available. If you are further interested in this topic please read the article <a href="https://indiareads/community/tutorials/how-to-use-firejail-to-set-up-a-wordpress-installation-in-a-jailed-environment">How To Use Firejail to Set Up a WordPress Installation in a Jailed Environment</a>.</p>

<p>Once you have finished with the above configuration restart php-fpm for the new settings to take effect with the command:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo service php5-fpm restart
</li></ul></code></pre>
<p>Verify that the new pool is properly running by searching for its processes like this:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">ps aux |grep site1
</li></ul></code></pre>
<p>If you have followed the exact instructions up to here you should see output similar to:</p>
<pre class="code-pre "><code langs=""><span class="highlight">site1</span>   14042  0.0  0.8 133620  4208 ?        S    14:45   0:00 php-fpm: pool site1
<span class="highlight">site1</span>   14043  0.0  1.1 133760  5892 ?        S    14:45   0:00 php-fpm: pool site1
</code></pre>
<p>In red is the user under which the process or the php-fpm pool runs - site1.</p>

<p>In addition, we'll disable the default php caching provided by opcache. This particular caching extension might be great for performance, but it's not for security as we'll see later. To disable it edit the file <code>/etc/php5/fpm/conf.d/05-opcache.ini</code> with super user privileges and add the line:</p>
<div class="code-label " title="/etc/php5/fpm/conf.d/05-opcache.ini">/etc/php5/fpm/conf.d/05-opcache.ini</div><pre class="code-pre "><code langs="">opcache.enable=0

</code></pre>
<p>Then restart again php-fpm (<code>sudo service php5-fpm restart</code>) for the setting to take effect.</p>

<h2 id="step-2-— configuring-nginx">Step 2 — Configuring nginx</h2>

<p>Once we have configured the php-fpm pool for our site we'll configure the server block in nginx. For this purpose please create a new file <code>/etc/nginx/sites-available/site1</code> with your favorite editor like this:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo vim /etc/nginx/sites-available/site1
</li></ul></code></pre>
<p>This file should contain:</p>
<div class="code-label " title="/etc/nginx/sites-available/site1">/etc/nginx/sites-available/site1</div><pre class="code-pre "><code langs="">server {
    listen 80;

    <span class="highlight">root /usr/share/nginx/sites/site1;</span>
    index index.php index.html index.htm;

    <span class="highlight">server_name site1.example.org;</span>

    location / {
        try_files $uri $uri/ =404;
    }

    location ~ \.php$ {
        try_files $uri =404;
        fastcgi_split_path_info ^(.+\.php)(/.+)$;
        <span class="highlight">fastcgi_pass unix:/var/run/php5-fpm-site1.sock;</span>
        fastcgi_index index.php;
        fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
        include fastcgi_params;
    }
}
</code></pre>
<p>The above code shows a common configuration for a server block in nginx. Note the interesting highlighted parts:</p>

<ul>
<li>Web root is <code>/usr/share/nginx/sites/site1</code>. </li>
<li> The server name uses the fqdn <code>site1.example.org</code> which is the one mentioned in the prerequisites of this article.</li>
<li> <code>fastcgi_pass</code> specifies the handler for the php files. For every site you should use a different unix socket such as <code>/var/run/php5-fpm-site1.sock</code>. </li>
</ul>

<p>Create the web root directory:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo mkdir /usr/share/nginx/sites
</li><li class="line" prefix="$">sudo mkdir /usr/share/nginx/sites/site1
</li></ul></code></pre>
<p>To enable the above site you have to create a symlink to it in the directory <code>/etc/nginx/sites-enabled/</code>. This can be done with the command:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo ln -s /etc/nginx/sites-available/site1 /etc/nginx/sites-enabled/site1
</li></ul></code></pre>
<p>Finally, restart nginx for the change to take effect like this:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo service nginx restart
</li></ul></code></pre>
<h2 id="step-3-— testing">Step 3 — Testing</h2>

<p>For running the tests we'll use the well-known phpinfo function which provides detailed information about the php environment. Create a new file under the name <code>info.php</code> which contains only the line <code><?php phpinfo(); ?></code>. You will need this file first in the the default nginx site and its web root <code>/usr/share/nginx/html/</code>. For this purpose you can use an editor like this:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo vim /usr/share/nginx/html/info.php
</li></ul></code></pre>
<p>After that copy the file to to the web root of the other site (site1.example.org) like this:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo cp /usr/share/nginx/html/info.php /usr/share/nginx/sites/site1/
</li></ul></code></pre>
<p>Now you are ready to run the most basic test to verify the server user. You can perform the test with a browser or from the Droplet terminal and lynx, the command line browser. If you don't have lynx on your Droplet yet, install it with the command <code>sudo apt-get install lynx</code>.</p>

<p>First check the <code>info.php</code> file from your default site. It should be accessible under localhost like this:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">lynx --dump http://localhost/info.php |grep 'SERVER\["USER"\]' 
</li></ul></code></pre>
<p>In the above command we filter the output with grep only for the variable <code>SERVER["USER"]</code> which stands for the server user. For the default site the output should show the default <code>www-data</code> user like this:</p>
<pre class="code-pre "><code langs="">_SERVER["USER"]                 www-data
</code></pre>
<p>Similarly, next check the server user for site1.example.org:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">lynx --dump http://site1.example.org/info.php |grep 'SERVER\["USER"\]' 
</li></ul></code></pre>
<p>You should see this time in the output the <code>site1</code> user:</p>
<pre class="code-pre "><code langs="">_SERVER["USER"]                 site1
</code></pre>
<p>If you have made any custom php settings on a per php-fpm pool basis, then you can also check their corresponding values in the above manner by filtering the output that interests you.</p>

<p>So far, we know that our two sites run under different users, but now let's see how to secure a connection. To demonstrate the security problem we are solving in this article, we'll create a file with sensitive information. Usually such a file contains the connection string to the database and include the user and password details of the database user. If anyone finds out that information, the person is able to do anything with the related site.</p>

<p>With your favorite editor create a new file in your main site <code>/usr/share/nginx/html/config.php</code>. That file should contain:</p>
<div class="code-label " title="/usr/share/nginx/html/config.php">/usr/share/nginx/html/config.php</div><pre class="code-pre "><code langs=""><?php
$pass = 'secret';
?>
</code></pre>
<p>In the above file we define a variable called <code>pass</code> which holds the value <code>secret</code>. Naturally, we want to restrict the access to this file, so we'll set its permissions to 400, which give read only access to the owner of the file. </p>

<p>To change the permissions to 400 run the command:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo chmod 400 /usr/share/nginx/html/config.php
</li></ul></code></pre>
<p>Also, our main site runs under the user <code>www-data</code> who should be able to read this file. Thus, change the ownership of the file to that user like this:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo chown www-data:www-data /usr/share/nginx/html/config.php
</li></ul></code></pre>
<p>In our example we'll use another file called <code>/usr/share/nginx/html/readfile.php</code> to read the secret information and print it. This file should contain the following code:</p>
<div class="code-label " title="/usr/share/nginx/html/readfile.php">/usr/share/nginx/html/readfile.php</div><pre class="code-pre "><code langs=""><?php
include('/usr/share/nginx/html/config.php');
print($pass);
?>
</code></pre>
<p>Change the ownership of this file to <code>www-data</code> as well:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo chown www-data:www-data /usr/share/nginx/html/readfile.php
</li></ul></code></pre>
<p>To confirm all permissions and ownerships are correct in the web root run the command <code>ls -l /usr/share/nginx/html/</code>. You should see output similar to:</p>
<pre class="code-pre "><code langs="">-r-------- 1 www-data www-data  27 Jun 19 05:35 config.php
-rw-r--r-- 1 www-data www-data  68 Jun 21 16:31 readfile.php
</code></pre>
<p>Now access the latter file on your default site with the command <code>lynx --dump http://localhost/readfile.php</code>. You should be able to see printed in the output <code>secret</code> which shows that the file with sensitive information is accessible within the same site, which is the expected correct behavior.</p>

<p>Now copy the file <code>/usr/share/nginx/html/readfile.php</code> to your second site, site1.example.org like this: </p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo cp /usr/share/nginx/html/readfile.php /usr/share/nginx/sites/site1/
</li></ul></code></pre>
<p>To keep the site/user relations in order, make sure that within each site the files are owned by the respective site user. Do this by changing the ownership of the newly copied file to site1 with the command:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo chown site1:site1 /usr/share/nginx/sites/site1/readfile.php
</li></ul></code></pre>
<p>To confirm you have set the correct permissions and ownership of the file, please list the contents of the site1 web root with the command <code>ls -l /usr/share/nginx/sites/site1/</code>. You should see:</p>
<pre class="code-pre "><code langs="">-rw-r--r-- 1 site1 site1  80 Jun 21 16:44 readfile.php
</code></pre>
<p>Then try to access the same file from site1.example.com with the command <code>lynx --dump http://site1.example.org/readfile.php</code>. You will only see empty space returned. Furthermore, if you search for errors in the error log of nginx with the grep command <code>sudo grep error /var/log/nginx/error.log</code> you will see:</p>
<pre class="code-pre "><code langs="">2015/06/30 15:15:13 [error] 894#0: *242 FastCGI sent in stderr: "PHP message: PHP Warning:  include(/usr/share/nginx/html/config.php): failed to open stream: Permission denied in /usr/share/nginx/sites/site1/readfile.php on line 2

</code></pre>
<p><span class="note"><strong>Note:</strong> You would also see a similar error in the lynx output if you have <code>display_errors</code> set to <code>On</code> in php-fpm configuration file <code>/etc/php5/fpm/php.ini</code>.<br /></span></p>

<p>The warning shows that a script from the site1.example.org site cannot read the sensitive file <code>config.php</code> from the main site. Thus, sites which run under different users cannot compromise the security of each other.</p>

<p>If you go back to the end of configuration part of this article, you will see that we have disabled the default caching provided by opcache. If you are curious why, try to enable again opcache by setting with super user privileges <code>opcache.enable=1</code> in the file <code>/etc/php5/fpm/conf.d/05-opcache.ini</code> and restart php5-fpm with the command <code>sudo service php5-fpm restart</code>. </p>

<p>Amazingly, if you run again the test steps in the exactly the same order, you'll be able to read the sensitive file regardless of its ownership and permission. This problem in opcache has been reported for a long time, but by the time of this article it has not been fixed yet. </p>

<h2 id="conclusion">Conclusion</h2>

<p>From a security point of view it's essential to use php-fpm pools with a different user for every site on the same Nginx web server. Even if it comes with a small performance penalty, the benefit of such isolation could prevent serious security breaches.</p>

<p>The idea described in this article is not unique, and it's present in other similar PHP isolation technologies such as SuPHP. However, the performance of all other alternatives is much worse than that of php-fpm.</p>

    