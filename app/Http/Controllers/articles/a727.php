<link rel='stylesheet' href='digital_ocean.css'><br> <br> 
      <h3 id="introduction">Introduction</h3>

<p>Laravel is a modern, open source PHP framework for web developers.  It aims to provide an easy, elegant way for developers to get a fully functional web application running quickly.</p>

<p>In this guide, we will discuss how to install Laravel on Ubuntu 14.04.  We will be using Nginx as our web server and will be working with the most recent version of Laravel at the time of this writing, version 4.2.</p>

<h2 id="install-the-backend-components">Install the Backend Components</h2>

<p>The first thing that we need to do to get started with Laravel is install the stack that will support it.  We can do this through Ubuntu's default repositories.</p>

<p>First, we need to update our local package index to make sure we have a fresh list of the available packages.  Then we can install the necessary components:</p>
<pre class="code-pre "><code langs="">sudo apt-get update
sudo apt-get install nginx php5-fpm php5-cli php5-mcrypt git
</code></pre>
<p>This will install Nginx as our web server along with the PHP tools needed to actually run the Laravel code.  We also install <code>git</code> because the <code>composer</code> tool, the dependency manager for PHP that we will use to install Laravel, will use it to pull down packages.</p>

<h2 id="modify-the-php-configuration">Modify the PHP Configuration</h2>

<p>Now that we have our components installed, we can start to configure them.  We will start with PHP, which is fairly straight forward.</p>

<p>The first thing that we need to do is open the main PHP configuration file for the PHP-fpm processor that Nginx uses.  Open this with sudo privileges in your text editor:</p>
<pre class="code-pre "><code langs="">sudo nano /etc/php5/fpm/php.ini
</code></pre>
<p>We only need to modify one value in this file.  Search for the <code>cgi.fix_pathinfo</code> parameter.  This will be commented out and set to "1".  We need to uncomment this and set it to "0":</p>
<pre class="code-pre "><code langs="">cgi.fix_pathinfo=0
</code></pre>
<p>This tells PHP not to try to execute a similar named script if the requested file name cannot be found.  This is very important because allowing this type of behavior could allow an attacker to craft a specially designed request to try to trick PHP into executing code that it should not.</p>

<p>When you are finished, save and close the file.</p>

<p>The last piece of PHP administration that we need to do is explicitly enable the MCrypt extension, which Laravel depends on.  We can do this by using the <code>php5enmod</code> command, which lets us easily enable optional modules:</p>
<pre class="code-pre "><code langs="">sudo php5enmod mcrypt
</code></pre>
<p>Now, we can restart the <code>php5-fpm</code> service in order to implement the changes that we've made:</p>
<pre class="code-pre "><code langs="">sudo service php5-fpm restart
</code></pre>
<p>Our PHP is now completely configured and we can move on.</p>

<h2 id="configure-nginx-and-the-web-root">Configure Nginx and the Web Root</h2>

<p>The next item that we should address is the web server.  This will actually involve two distinct steps.</p>

<p>The first step is configuring the document root and directory structure that we will use to hold the Laravel files.  We are going to place our files in a directory called <code>/var/www/laravel</code>.</p>

<p>At this time, only the top-level of this path (<code>/var</code>) is created.  We can create the entire path in one step by passing the <code>-p</code> flag to our <code>mkdir</code> command.  This instructs the utility to create any necessary parent path elements needed to construct a given path:</p>
<pre class="code-pre "><code langs="">sudo mkdir -p /var/www/laravel
</code></pre>
<p>Now that we have a location set aside for the Laravel components, we can move on to editing the Nginx server blocks.</p>

<p>Open the default server block configuration file with sudo privileges:</p>
<pre class="code-pre "><code langs="">sudo nano /etc/nginx/sites-available/default
</code></pre>
<p>Upon installation, this file will have quite a few explanatory comments, but the basic structure will look like this:</p>
<pre class="code-pre "><code langs="">server {
        listen 80 default_server;
        listen [::]:80 default_server ipv6only=on;

        root /usr/share/nginx/html;
        index index.html index.htm;

        server_name localhost;

        location / {
                try_files $uri $uri/ =404;
        }
}
</code></pre>
<p>This provides a good basis for the changes that we will be making.</p>

<p>The first thing we need to change is the location of the document root.  Laravel will be installed in the <code>/var/www/laravel</code> directory that we created.</p>

<p>However, the base files that are used to drive the app are kept in a subdirectory within this called <code>public</code>.  This is where we will set our document root.  In addition, we will tell Nginx to serve any <code>index.php</code> files before looking for their HTML counterparts when requesting a directory location:</p>
<pre class="code-pre "><code langs="">server {
    listen 80 default_server;
    listen [::]:80 default_server ipv6only=on;

    root <span class="highlight">/var/www/laravel/public</span>;
    index <span class="highlight">index.php</span> index.html index.htm;

    server_name localhost;

    location / {
            try_files $uri $uri/ =404;
    }
}
</code></pre>
<p>Next, we should set the <code>server_name</code> directive to reference the actual domain name of our server.  If you do not have a domain name, feel free to use your server's IP address.</p>

<p>We also need to modify the way that Nginx will handle requests.  This is done through the <code>try_files</code> directive.  We want it to try to serve the request as a file first.  If it cannot find a file of the correct name, it should attempt to serve the default index file for a directory that matches the request.  Failing this, it should pass the request to the <code>index.php</code> file as a query parameter.</p>

<p>The changes described above can be implemented like this:</p>
<pre class="code-pre "><code langs="">server {
        listen 80 default_server;
        listen [::]:80 default_server ipv6only=on;

        root /var/www/laravel/public;
        index index.php index.html index.htm;

        server_name <span class="highlight">server_domain_or_IP</span>;

        location / {
                try_files $uri $uri/ <span class="highlight">/index.php?$query_string</span>;
        }
}
</code></pre>
<p>Finally, we need to create a block that handles the actual execution of any PHP files.  This will apply to any files that end in <code>.php</code>.  It will try the file itself and then try to pass it as a parameter to the <code>index.php</code> file.</p>

<p>We will set the <code>fastcgi_*</code> directives so that the path of requests are correctly split for execution, and make sure that Nginx uses the socket that <code>php5-fpm</code> is using for communication and that the <code>index.php</code> file is used as the index for these operations.</p>

<p>We will then set the <code>SCRIPT_FILENAME</code> parameter so that PHP can locate the requested files correctly.  When we are finished, the completed file should look like this:</p>
<pre class="code-pre "><code langs="">server {
    listen 80 default_server;
    listen [::]:80 default_server ipv6only=on;

    root /var/www/laravel/public;
    index index.php index.html index.htm;

    server_name server_domain_or_IP;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    <span class="highlight">location ~ \.php$ {</span>
        <span class="highlight">try_files $uri /index.php =404;</span>
        <span class="highlight">fastcgi_split_path_info ^(.+\.php)(/.+)$;</span>
        <span class="highlight">fastcgi_pass unix:/var/run/php5-fpm.sock;</span>
        <span class="highlight">fastcgi_index index.php;</span>
        <span class="highlight">fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;</span>
        <span class="highlight">include fastcgi_params;</span>
    <span class="highlight">}</span>
}
</code></pre>
<p>Save and close the file when you are finished.</p>

<p>Because we modified the <code>default</code> server block file, which is already enabled, we simply need to restart Nginx for our configuration changes to be picked up:</p>
<pre class="code-pre "><code langs="">sudo service nginx restart
</code></pre>
<h2 id="create-swap-file-optional">Create Swap File (Optional)</h2>

<p>Before we go about installing Composer and Laravel, it might be a good idea to enable some swap on your server so that the build completes correctly.  This is generally only necessary if you are operating on a server without much memory (like a 512mb Droplet).</p>

<p>Swap space will allow the operating system to temporarily move data from memory onto the disk when the amount of information in memory exceeds the physical memory space available.  This will prevent your applications or system from crashing with an out of memory (OOM) exception when doing memory intensive tasks.</p>

<p>We can very easily set up some swap space to let our operating system shuffle some of this to the disk when necessary.  As mentioned above, this is probably only necessary if you have less than 1GB of ram available.</p>

<p>First, we can create an empty 1GB file by typing:</p>
<pre class="code-pre "><code langs="">sudo fallocate -l 1G /swapfile
</code></pre>
<p>We can format it as swap space by typing:</p>
<pre class="code-pre "><code langs="">sudo mkswap /swapfile
</code></pre>
<p>Finally, we can enable this space so that the kernel begins to use it by typing:</p>
<pre class="code-pre "><code langs="">sudo swapon /swapfile
</code></pre>
<p>The system will only use this space until the next reboot, but the only time that the server is likely to exceed its available memory is during the build processes, so this shouldn't be a problem.</p>

<h2 id="install-composer-and-laravel">Install Composer and Laravel</h2>

<p>Now, we are finally ready to install Composer and Laravel.  We will set up Composer first.  We will then use this tool to handle the Laravel installation.</p>

<p>Move to a directory where you have write access (like your home directory) and then download and run the installer script from the Composer project:</p>
<pre class="code-pre "><code langs="">cd ~
curl -sS https://getcomposer.org/installer | php
</code></pre>
<p>This will create a file called <code>composer.phar</code> in your home directory.  This is a PHP archive, and it can be run from the command line.</p>

<p>We want to install it in a globally accessible location though.  Also, we want to change the name to <code>composer</code> (without the file extension).  We can do this in one step by typing:</p>
<pre class="code-pre "><code langs="">sudo mv composer.phar /usr/local/bin/composer
</code></pre>
<p>Now that you have Composer installed, we can use it to install Laravel.</p>

<p>Remember, we want to install Laravel into the <code>/var/www/laravel</code> directory.  To install the latest version of Laravel, you can type:</p>
<pre class="code-pre "><code langs="">sudo composer create-project laravel/laravel /var/www/laravel
</code></pre>
<p>At the time of this writing, the latest version is 4.2.  In the event that future changes to the project prevent this installation procedure from correctly completing, you can force the version we're using in this guide by instead typing:</p>
<pre class="code-pre "><code langs="">sudo composer create-project laravel/laravel /var/www/laravel 4.2
</code></pre>
<p>Now, the files are all installed within our <code>/var/www/laravel</code> directory, but they are entirely owned by our <code>root</code> account.  The web user needs partial ownership and permissions in order to correctly serve the content.</p>

<p>We can give group ownership of our Laravel directory structure to the web group by typing:</p>
<pre class="code-pre "><code langs="">sudo chown -R :www-data /var/www/laravel
</code></pre>
<p>Next, we can change the permissions of the <code>/var/www/laravel/app/storage</code> directory to allow the web group write permissions.  This is necessary for the application to function correctly:</p>
<pre class="code-pre "><code langs="">sudo chmod -R 775 /var/www/laravel/app/storage
</code></pre>
<p>You now have Laravel completely installed and ready to go.  You can see the default landing page by visiting your server's domain or IP address in your web browser:</p>
<pre class="code-pre "><code langs="">http://<span class="highlight">server_domain_or_IP</span>
</code></pre>
<p><img src="https://assets.digitalocean.com/articles/laravel_nginx_1404/laravel_default.png" alt="Laravel default landing page" /></p>

<p>You now have everything you need to start building applications with the Laravel framework.</p>

<h2 id="conclusion">Conclusion</h2>

<p>You should now have Laravel up and running on your server.  Laravel is quite a flexible framework and it includes many tools that can help you build out an application in a structured way.</p>

<p>To learn how to use Laravel to build an application, check out the <a href="http://laravel.com/docs">Laravel documentation</a>.</p>

    