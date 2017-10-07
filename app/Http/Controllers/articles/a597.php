<link rel='stylesheet' href='digital_ocean.css'><br><img src=https://community-cdn-digitalocean-com.global.ssl.fastly.net/assets/tutorials/images/large/xhprof_xhgui_tutorial_small.png?1461703920/> <br> 
      <h3 id="introduction">Introduction</h3>

<p>In software engineering, profiling is a technique used to analyze applications at run-time, in order to identify possible bottlenecks and performance issues within an application. It is an essential resource for software optimization. Profiling differs from benchmarking because it analyzes the application at code level, while benchmarking is intended to analyze the overall application performance as experienced by the end user.  </p>

<p>A <strong>profiler</strong> is a software that will gather detailed information about the application in order to generate statistics and insightful data about memory usage, frequency and duration of function calls, time to respond to a request, amongst other things.</p>

<p>XHProf is a profiler designed to analyze PHP applications. Created and open sourced by Facebook, XHProf works as a passive profiler, which means it will work in the background while having minimum impact on the application's performance, making it suitable to be used on production environments. </p>

<p>XHGui offers a rich interface for visualizing data collected via XHProf.</p>

<p>This tutorial will show you how to install XHProf and XHGui for profiling a PHP application running on Ubuntu 14.04. </p>

<p><span class="note">XHProf currently does not support PHP 7. If you are using PHP 7 on your server, you can try the <a href="https://github.com/tideways/php-profiler-extension">tideways/php-profiler-extension</a> instead, which works as a drop-in replacement for XHProf. <br /></span></p>

<h2 id="prerequisites">Prerequisites</h2>

<p>In order to follow this guide, you will need:</p>

<ul>
<li>An Ubuntu 14.04 server with a non-root sudo user, which you can set up by following our <a href="https://indiareads/community/tutorials/initial-server-setup-with-ubuntu-14-04">Initial Server Setup</a> guide </li>
<li>A functional PHP web server environment running a PHP application that will be analyzed</li>
</ul>

<p>When you are ready to move on, log into your server using your sudo account.</p>

<h2 id="step-1-—-install-the-server-dependencies">Step 1 — Install the Server Dependencies</h2>

<p>In case you don't have <code>pecl</code> installed on your server, you should get it installed now. We'll need it to get both <code>xhprof</code> and the <code>mongo</code> PHP extension set up.</p>

<p>First, update the package manager cache with:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo apt-get update
</li></ul></code></pre>
<p>Next, we'll install <code>pecl</code> with the <code>php-pear</code> package. We'll also need <code>php5-dev</code> in order to install PHP modules via <code>pecl</code>, and <code>php5-mcrypt</code> for setting up XHGui:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo apt-get install php-pear php5-dev php5-mcrypt
</li></ul></code></pre>
<p>To enable the <code>mcrypt</code> extension, run:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo php5enmod mcrypt
</li></ul></code></pre>
<p>Lastly, we will need Git to install XHGui. If Git isn’t already installed on your server, you can install it now with:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo apt-get install git
</li></ul></code></pre>
<h2 id="step-2-—-install-xhprof">Step 2 — Install XHProf</h2>

<p>Now we should get XHProf installed and enabled. To install it via <code>pecl</code>, run:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo pecl install xhprof-beta
</li></ul></code></pre>
<p>Next, we need to activate the <code>xhprof</code> extension. To facilitate this process while also keeping Ubuntu/Debian standards, we are going to create a separate <code>ini</code> configuration file and enable it using the command <code>php5enmod</code>. </p>

<p>Create a new <code>ini</code> configuration file inside <code>/etc/php5/mods-available</code>:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo nano /etc/php5/mods-available/xhprof.ini
</li></ul></code></pre>
<p>Include the following contents in this file:</p>
<div class="code-label " title="/etc/php5/mods-available/xhprof.ini">/etc/php5/mods-available/xhprof.ini</div><pre class="code-pre "><code langs="">extension=xhprof.so
</code></pre>
<p>To enable the module configuration file, run: </p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo php5enmod xhprof
</li></ul></code></pre>
<p>Now the only thing left to do is to restart the web server in order to apply the changes. On <strong>LAMP</strong> environments (Apache), you can do this with:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo service apache2 restart
</li></ul></code></pre>
<p>On <strong>LEMP</strong> environments(Nginx + PHP5-FPM), you should restart the <code>php5-fpm</code> service with:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo service php5-fpm restart
</li></ul></code></pre>
<p>The <code>xhprof</code> extension should now be installed and activated. To confirm, you can run:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">php --ri xhprof
</li></ul></code></pre>
<p>The output should be similar to this:</p>
<pre class="code-pre "><code langs=""><div class="secondary-code-label " title="Output">Output</div>xhprof

xhprof => 0.9.2
CPU num => 1
</code></pre>
<h2 id="step-3-—-install-mongodb">Step 3 — Install MongoDB</h2>

<p>The next step is to get MongoDB and the <code>mongo</code> PHP extension installed on the server. MongoDB is used by XHGui to store the data obtained via XHProf's application analysis. </p>

<p>To install MongoDB, run:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo apt-get install mongodb
</li></ul></code></pre>
<p>To install the MongoDB PHP extension, run:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo pecl install mongo
</li></ul></code></pre>
<p>The installation will ask for your input at some point, to choose if you want to enable enterprise authentication for MongoDB. You can leave the default value (no) and just press enter to continue with the installation.</p>

<p>Now we need to activate the <code>mongo</code> PHP extension, following the same procedure we used for the <code>xhprof</code> extension. Create a new configuration file at <code>/etc/php5/mods-available/mongo.ini</code>:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo nano /etc/php5/mods-available/mongo.ini
</li></ul></code></pre>
<p>Include the following contents in the file:</p>
<div class="code-label " title="/etc/php5/mods-available/mongo.ini">/etc/php5/mods-available/mongo.ini</div><pre class="code-pre "><code langs="">extension=mongo.so
</code></pre>
<p>To enable the module configuration file, run: </p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo php5enmod mongo
</li></ul></code></pre>
<p>Now restart the web server to apply the changes.  On <strong>LAMP</strong> environments (Apache), you can do this with:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo service apache2 restart
</li></ul></code></pre>
<p>On <strong>LEMP</strong> environments(Nginx + PHP5-FPM), you should restart the <code>php5-fpm</code> service with:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo service php5-fpm restart
</li></ul></code></pre>
<p>The <code>mongo</code> extension should now be installed and activated. To confirm, you can run:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">php --ri mongo
</li></ul></code></pre>
<p>The output should be similar to this:</p>
<pre class="code-pre "><code langs=""><div class="secondary-code-label " title="Output">Output</div>mongo

MongoDB Support => enabled
Version => 1.6.12
Streams Support => enabled
SSL Support => enabled
                   Supported Authentication Mechanisms                   
MONGODB-CR => enabled
SCRAM-SHA-1 => enabled
MONGODB-X509 => enabled
GSSAPI (Kerberos) => disabled
PLAIN => disabled
...
</code></pre>
<h2 id="step-4-—-set-up-mongodb-indexes-optional">Step 4 — Set Up MongoDB Indexes (Optional)</h2>

<p>This is an optional but recommended step that will improve the overall performance of XHGui when storing and accessing data from MongoDB.</p>

<p>Access the MongoDB client via command line with:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">mongo
</li></ul></code></pre>
<p>Now, run the following sequence of commands in order to create the indexes for XHGui:</p>
<pre class="code-pre custom_prefix"><code langs=""><ul class="prefixed"><li class="line" prefix=">">use xhprof
</li><li class="line" prefix=">">db.results.ensureIndex( { 'meta.SERVER.REQUEST_TIME' : -1 } )
</li><li class="line" prefix=">">db.results.ensureIndex( { 'profile.main().wt' : -1 } )
</li><li class="line" prefix=">">db.results.ensureIndex( { 'profile.main().mu' : -1 } )
</li><li class="line" prefix=">">db.results.ensureIndex( { 'profile.main().cpu' : -1 } )
</li><li class="line" prefix=">">db.results.ensureIndex( { 'meta.url' : 1 } )
</li></ul></code></pre>
<p>To exit the MongoDB client, run:</p>
<pre class="code-pre custom_prefix"><code langs=""><ul class="prefixed"><li class="line" prefix=">">exit
</li></ul></code></pre>
<h2 id="step-5-—-install-xhgui">Step 5 — Install XHGui</h2>

<p>The next step is to install XHGui and set it up as a virtual host on your web server. </p>

<p>We will start by cloning the XHGui repository from Github. Because we need to serve XHGui's contents as a virtual host on the web server, we will place the cloned repository inside <code>/var/www</code>.</p>

<p>It is recommended that you set up the XHGui directory to be owned by your regular user. In this example, we are going to use <code>sammy</code> as username and group, but you should replace these values with your own username and group.</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo mkdir -p /var/www/xhgui
</li><li class="line" prefix="$">sudo chown -R <span class="highlight">sammy</span>.<span class="highlight">sammy</span> /var/www/xhgui
</li><li class="line" prefix="$">cd /var/www
</li><li class="line" prefix="$">git clone https://github.com/perftools/xhgui.git xhgui
</li></ul></code></pre>
<p>To install XHGui's dependencies, execute the included installer:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">cd xhgui
</li><li class="line" prefix="$">php install.php
</li></ul></code></pre>
<p>After the dependencies are successfully installed, we need to configure a virtual host to serve the contents of <code>xhgui</code>. The next sections cover how to create a virtual host for <code>xhgui</code> on both LAMP and LEMP environments.</p>

<h3 id="setting-up-xhgui-39-s-virtual-host-on-lamp">Setting Up XHGui's Virtual Host on LAMP</h3>

<p>When using Apache as the web server, we first need to make sure <code>mod_rewrite</code> is enabled. To enable it, run:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo a2enmod rewrite
</li></ul></code></pre>
<p>Create a new virtual host file under <code>/etc/apache2/sites-available</code>:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo nano /etc/apache2/sites-available/xhgui.conf
</li></ul></code></pre>
<p>Place the following contents inside this file:</p>
<div class="code-label " title="/etc/apache2/sites-available/xhgui.conf">/etc/apache2/sites-available/xhgui.conf</div><pre class="code-pre "><code langs=""> <VirtualHost *:80>
    DocumentRoot /var/www/xhgui/webroot
    ServerName <span class="highlight">xhgui.example.com</span>

    <Directory "/var/www/xhgui/webroot">
        Options Indexes MultiViews FollowSymLinks
        AllowOverride All
        Require all granted
    </Directory>
</VirtualHost>
</code></pre>
<p>Notice that the document root should point to the subdirectory <code>webroot</code> inside XHGui's main directory.</p>

<p><span class="note">If you currently don't have a subdomain you can use for this virtual host, you can use a dummy domain name and create an entry in your local <code>/etc/hosts</code> file pointing the <code>ServerName</code> that you set to the server's IP address. For more information on how to create Apache virtual hosts, you can check our <a href="https://indiareads/community/tutorials/how-to-set-up-apache-virtual-hosts-on-ubuntu-14-04-lts">How to Set Up Apache Virtual Hosts on Ubuntu 14.04</a> guide.</span></p>

<p>Enable the virtual host with:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo a2ensite xhgui
</li></ul></code></pre>
<p>To apply the changes, reload Apache with:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo service apache2 reload
</li></ul></code></pre>
<h3 id="setting-up-xhgui-39-s-virtual-host-on-lemp">Setting Up XHGui's Virtual Host on LEMP</h3>

<p>Start by creating a new virtual host file on <code>/etc/nginx/sites-available</code>:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo nano /etc/nginx/sites-available/xhgui
</li></ul></code></pre>
<p>Place the following contents inside this file:</p>
<div class="code-label " title="/etc/nginx/sites-available/xhgui">/etc/nginx/sites-available/xhgui</div><pre class="code-pre "><code langs="">server {
    listen   80;
    server_name <span class="highlight">xhgui.example.com</span>;
    root   /var/www/xhgui/webroot/;
    index  index.php;

    location / {
        try_files $uri $uri/ /index.php?$uri&$args;
    }

    location ~ \.php$ {
                try_files $uri =404;
                fastcgi_split_path_info ^(.+\.php)(/.+)$;
                fastcgi_pass unix:/var/run/php5-fpm.sock;
                fastcgi_index index.php;
                include fastcgi_params;
    }
}
</code></pre>
<p>Notice that the document root should point to the subdirectory <code>webroot</code> inside XHGui's main directory.</p>

<p><span class="note">If you currently don't have a subdomain you can use for this virtual host, you can use a dummy domain name and create an entry in your local <code>/etc/hosts</code> file pointing the <code>server_name</code> that you set to the server's IP address. For more information on how to create Nginx virtual hosts, you can check our <a href="https://indiareads/community/tutorials/how-to-set-up-nginx-server-blocks-virtual-hosts-on-ubuntu-14-04-lts">How to Set Up Nginx Server Blocks on Ubuntu 14.04</a> guide.</span></p>

<p>To enable the new virtual host, run:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo ln -s /etc/nginx/sites-available/xhgui /etc/nginx/sites-enabled/xhgui
</li></ul></code></pre>
<p>Now, restart Nginx to apply the changes:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo service nginx restart
</li></ul></code></pre>
<h2 id="step-6-—-set-up-xhprof">Step 6 — Set Up XHProf</h2>

<p>At this point, you should be able to access XHGui's interface from your browser by visiting the server name that you specified in your web server configuration. As we didn't start collecting profiling data yet, you should see a page like this:</p>

<p><img src="http://assets.digitalocean.com/articles/xhprof-ubuntu/01-empty.png" alt="Image 01: XHGui First Run" /></p>

<p>The XHProf extension is already installed on the server, but we still need to activate the profiling process for your application. This is typically done by including a PHP directive on your web server that automatically prepends a piece of code to all PHP scripts being executed. It's important to point out that by default, XHProf will only profile 1 out of 100 requests made to the application. </p>

<p>XHGui provides a default PHP header that you can prepend to your scripts in order to initialize profiling for your application. If you followed along with all steps in this tutorial, the header file should be located at <code>/var/www/xhgui/external/header.php</code>. </p>

<p>The next sections will show you how to automatically prepend this header file to all your PHP scripts on both Apache and Nginx environments. For this example, we will enable profiling for a WordPress application that is hosted as the main website on this server.</p>

<h3 id="enabling-profiling-on-apache">Enabling Profiling on Apache</h3>

<p>Let's edit the Apache configuration file for the website that we want to profile. In this example, we will enable profiling for the main Apache website hosted on this server, defined at  <code>/etc/apache2/sites-available/000-default.conf</code>. Open this file with your command line editor of choice:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo nano /etc/apache2/sites-available/000-default.conf
</li></ul></code></pre>
<p>Include the highlighted line inside the existing <code><VirtualHost></code> block:</p>
<div class="code-label " title="/etc/apache2/sites-available/000-default.conf">/etc/apache2/sites-available/000-default.conf</div><pre class="code-pre "><code langs=""><VirtualHost *:80>
  ...
  <span class="highlight">php_admin_value auto_prepend_file "/var/www/xhgui/external/header.php"</span>
  ...
</VirtualHost>
</code></pre>
<p>Save the file and exit. Restart Apache to apply the changes:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo service apache2 restart
</li></ul></code></pre>
<h3 id="enabling-profiling-on-nginx">Enabling Profiling on Nginx</h3>

<p>Let's edit the Nginx configuration file for the website that we want to profile. In this example, we will enable profiling for the <code>default</code> website hosted on this server, defined at <code>/etc/nginx/sites-available/default</code>. Open this file with your command line editor of choice:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo nano /etc/nginx/sites-available/default
</li></ul></code></pre>
<p>Now look for the block that defines how <code>.php</code> scripts are handled. Include the highlighted line inside this block:</p>
<div class="code-label " title="/etc/nginx/sites-available/default">/etc/nginx/sites-available/default</div><pre class="code-pre "><code langs=""> location ~ \.php$ {
   ...
   <span class="highlight">fastcgi_param PHP_VALUE "auto_prepend_file=/var/www/xhgui/external/header.php";</span>
   ...
}
</code></pre>
<p>Save the file and exit. Restart Nginx to apply the changes:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo service nginx restart
</li></ul></code></pre>
<h2 id="step-7-—-getting-started-with-xhgui">Step 7 — Getting Started with XHGui</h2>

<p>Everything is now properly set up, but dependending on the amount of views your website usually gets, it might take some time before the first profiling data shows up in XHGui. This is due to the fact that by default XHProf will only profile 1 out of 100 requests received. You might need to navigate through your website and reload it a few times before any profile data will be available.</p>

<h3 id="xhgui-overview">XHGui Overview</h3>

<p>When profiling information is available, you should see a page like this:</p>

<p><img src="http://assets.digitalocean.com/articles/xhprof-ubuntu/02-overview.png" alt="Image 02: XHGui Overview" /></p>

<p>Below you can find a quick description of each field in this overview table:</p>

<ul>
<li><strong>Method:</strong> The Method used in the analyzed request</li>
<li><strong>URL:</strong> The URL that was profiled</li>
<li><strong>Time:</strong> The time when this profiling data was collected</li>
<li><strong>wt (Wall Time):</strong> How long this request took to be completed</li>
<li><strong>cpu:</strong> The time spent by the CPU to perform this request</li>
<li><strong>mu (Memory Usage):</strong> Average memory used during this request </li>
<li><strong>pmu (Peak Memory Usage):</strong> Peak of memory usage during this request </li>
</ul>

<p>To see the details of a profiling run, use the link in the <em>time</em> field. You should see a page like this:</p>

<p><img src="http://assets.digitalocean.com/articles/xhprof-ubuntu/03-profile.png" alt="Image 03: Profiling Data" /></p>

<p>On the left side you can see information about the request that was analyzed, such as the method used, the script name and URL, request parameters, among other things. On the main page content, you can identify the functions or methods that took most time to be executed, as well as the functions or methods that had the higher memory consumption. All this information is related to a specific profiling run and request.</p>

<h3 id="inspecting-function-calls">Inspecting Function Calls</h3>

<p>If you scroll down to the bottom of the page, you will have access to a table with detailed information about all function calls executed during this request, including how many times the function or method was executed, how long it took to run, how much memory it used, and many other interesting details. You can use the table header to order the listing by any of these parameters. You can also use the search box on the right to search for a specific function or method name.</p>

<p><img src="http://assets.digitalocean.com/articles/xhprof-ubuntu/04-functioncalls.png" alt="Image 04: XHGui Function Calls" /></p>

<h3 id="comparing-runs">Comparing Runs</h3>

<p>One of the most useful features of XHGui is the comparison tool that you can use to compare two different profiling runs. This gives you the ability to make changes to your code and compare multiple runs to see if your changes resulted in any performance gains to the application.</p>

<p>While looking at a set of profile data, to the right of  the <em>Watch Functions</em> section, you can see a button named <strong>Compare This Run</strong>. Clicking on this button will show you a list of all profiling runs executed for that specific URL, where you can choose one of the items in the list to generate a comparison view. Just choose which run you want to compare to, and click on the <em>Compare</em> button.</p>

<p>This is how the comparison view looks like:</p>

<p><img src="http://assets.digitalocean.com/articles/xhprof-ubuntu/05-compare.png" alt="Image 05: XHGui Comparing Runs" /></p>

<h2 id="conclusion">Conclusion</h2>

<p>Profiling is an important technique for software optimization, giving you detailed insights about your application at code level. With the help of tools like XHProf and XHGui you can effectively identify problematic portions of your code and monitor the impact of code changes in the application's performance. </p>

<p>For more information about the configuration options available for XHGui, check the official <a href="https://github.com/perftools/xhgui">Github repository</a>.</p>

    