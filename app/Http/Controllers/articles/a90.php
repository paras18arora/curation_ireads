<link rel='stylesheet' href='digital_ocean.css'><br> <br> 
      <div><h3>About APC</h3>

<p>APC is a great operation code caching system for PHP that can help speed up your site. PHP is a dynamic server-side scripting language that needs to be parsed, compiled and executed by the server with every page request. In many cases though, the requests produce exactly the same results which means that the cloud server has to unnecessarily repeat all these steps for each of them.</p>

<p>This is where APC comes into play. What it does is save the PHP opcode (operation code) in the RAM memory and if requested again, executes it from there. In essence, it bypasses the parsing and compiling steps and minimizes some unnecessary loads on the cloud server.</p> 

<p>This tutorial will show you how to install and configure APC. It assumes you are already running your own VPS with root privileges and have LAMP stack installed on it. If you need help with getting you going on those, you can read <a target="_blank" href="https://indiareads/community/articles/how-to-install-linux-apache-mysql-php-lamp-stack-on-ubuntu">this tutorial</a>.</p>

<h2>Installing APC</h2>

<p>To install APC, you first need to take care of a couple of dependencies. Install the these packages with the following command:</p>

<pre>sudo apt-get install php-pear php5-dev make libpcre3-dev</pre>

<p>Next up, you can install APC using the <em>pecl</em> command:</p>

<pre>sudo pecl install apc</pre>

<p>You will be asked a number of questions but unless you know exactly what you are enabling, go with the defaults by hitting <em>Enter</em>.</p> 

<p>The next and final step of the installation is also mentioned in the terminal window. You need to edit the <em>php.ini</em> file and add a line at the end. Open and edit the file:</p>

<pre>sudo nano /etc/php5/apache2/php.ini</pre>

<p>Add the following line to the bottom of it:</p>

<pre>extension = apc.so</pre>

<p>Save, exit the file, and restart Apache:</p>

<pre>sudo service apache2 restart</pre>

<p>To see if APC is now enabled, you can check on the PHP info page. If you don't have one, you can create an empty php file in your <em>/var/www</em> folder:</p>

<pre>nano /var/www/info.php</pre>

<p>And paste in the following code:</p>

<pre>&lt?php
phpinfo();
?></pre>

<p>Save, exit, and open that file in the browser. There you will find all sorts of information regarding the PHP installed on your cloud server, and if APC is enabled, it should show up there. It's probably not a good idea to leave that file there in production, so make sure you delete it after you are done checking.</p>

<h2>Configuring APC</h2>

<p>You now have installed APC and it's running with the default options. There are at least two main configuration settings that you should know about. First, reopen the <em>php.ini</em> file you edited earlier:</p>

<pre>sudo nano /etc/php5/apache2/php.ini</pre>

<p>Below the line you pasted to enable APC, paste the following line:</p>

<pre>apc.shm_size = 64</pre>

<p>This will allocate 64MB from the RAM to APC for its caching purposes. Depending on your VPS's requirements but also limitations, you can increase or decrease this number.</p>

<p>Another line that you can paste below is the following:</p>

<pre>apc.stat = 0</pre>

<p>The <em>apc.stat</em> setting checks the script on each request to see if it was modified. If it has been modified, it will recompile it and cache the new version. This is the default behavior that comes with every APC installation. Setting it to 0 will tell APC not to check for changes in the script. It improves performance but it also means that if there are changes to the PHP script, they will not be reflected until the cloud server is restarted. Therefore setting it to 0 is only recommended on production sites where you are certain this is something you want.</p> 

<p>Now that APC is up and running, there is a nifty little page you can use to check its status and performance. You can locate an <em>apc.php</em> file in the <em>/usr/share/php/</em> folder. You have to move this file somewhere accessible from the browser - let's say the <em>www</em> folder:</p>

<pre>cp /usr/share/php/apc.php /var/www</pre>

<p>Now navigate to that file in the browser:</p>

<pre>http://&ltIP_Address>/apc.php</pre>

<p>You'll get some interesting statistics about APC. What you need to pay attention to is that APC has enough memory to store its information and that there is not too much fragmentation.</p> 

<p>Additionally, a good indicator that APC it's doing its job is that the <strong>Hits rate</strong> is significantly higher than the <strong>Misses rate</strong>; the first should be somewhere over 95% after a few requests already.</p> 

<h2>Conclusion</h2>

<p>APC is a very easy to install and manage caching system for your sites hosted on cloud servers. If you want to continue improving site performance, you can look into <a href="https://indiareads/community/articles/how-to-install-and-use-memcache-on-ubuntu-12-04" target="_blank">installing Memcache</a> and even <a href="https://indiareads/community/articles/how-to-install-and-configure-varnish-with-apache-on-ubuntu-12-04--3" target="_blank">installing Varnish</a> for an even greater performance. </p>

<div class="author">Article Submitted by: <a href="http://www.webomelette.com/">Danny</a></div></div>
    