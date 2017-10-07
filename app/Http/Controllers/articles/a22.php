<link rel='stylesheet' href='digital_ocean.css'><br> <br> 
      <h3 id="introduction">Introduction</h3>

<p>When using the Nginx web server, <strong>server blocks</strong> (similar to the virtual hosts in Apache) can be used to encapsulate configuration details and host more than one domain off of a single server.</p>

<p>In this guide, we'll discuss how to configure server blocks in Nginx on an Ubuntu 16.04 server.</p>

<h2 id="prerequisites">Prerequisites</h2>

<p>We're going to be using a non-root user with <code>sudo</code> privileges throughout this tutorial.  If you do not have a user like this configured, you can create one by following our <a href="https://indiareads/community/tutorials/initial-server-setup-with-ubuntu-16-04">Ubuntu 16.04 initial server setup</a> guide.</p>

<p>You will also need to have Nginx installed on your server.  The following guides cover this procedure:</p>

<ul>
<li><a href="https://indiareads/community/tutorials/how-to-install-nginx-on-ubuntu-16-04">How To Install Nginx on Ubuntu 16.04</a>: Use this guide to set up Nginx on its own.</li>
<li><a href="https://indiareads/community/tutorials/how-to-install-linux-nginx-mysql-php-lemp-stack-in-ubuntu-16-04">How To Install Linux, Nginx, MySQL, PHP (LEMP stack) in Ubuntu 16.04</a>: Use this guide if you will be using Nginx in conjunction with MySQL and PHP.</li>
</ul>

<p>When you have fulfilled these requirements, you can continue on with this guide.</p>

<h2 id="example-configuration">Example Configuration</h2>

<p>For demonstration purposes, we're going to set up two domains with our Nginx server.  The domain names we'll use in this guide are <strong>example.com</strong> and <strong>test.com</strong>.</p>

<p>You can find a guide on how to set up domain names with IndiaReads <a href="https://indiareads/community/articles/how-to-set-up-a-host-name-with-digitalocean">here</a>.  If you do not have two spare domain names to play with, use dummy names for now and we'll show you later how to configure your local computer to test your configuration.</p>

<h2 id="step-one-set-up-new-document-root-directories">Step One: Set Up New Document Root Directories</h2>

<p>By default, Nginx on Ubuntu 16.04 has one server block enabled by default.  It is configured to serve documents out of a directory at <code>/var/www/html</code>.</p>

<p>While this works well for a single site, we need additional directories if we're going to serve multiple sites.  We can consider the <code>/var/www/html</code> directory the default directory that will be served if the client request doesn't match any of our other sites.</p>

<p>We will create a directory structure within <code>/var/www</code> for each of our sites.  The actual web content will be placed in an <code>html</code> directory within these site-specific directories.  This gives us some additional flexibility to create other directories associated with our sites as siblings to the <code>html</code> directory if necessary.</p>

<p>We need to create these directories for each of our sites.  The <code>-p</code> flag tells <code>mkdir</code> to create any necessary parent directories along the way:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo mkdir -p /var/www/<span class="highlight">example.com</span>/html
</li><li class="line" prefix="$">sudo mkdir -p /var/www/<span class="highlight">test.com</span>/html
</li></ul></code></pre>
<p>Now that we have our directories, we will reassign ownership of the web directories to our normal user account.  This will let us write to them without <code>sudo</code>.</p>

<p></p><div class="code-label notes-and-warnings note" title="Note">Note</div><span class="note">
Depending on your needs, you might need to adjust the permissions or ownership of the folders again to allow certain access to the <code>www-data</code> user.  For instance, dynamic sites will often need this.  The specific permissions and ownership requirements entirely depend on what your configuration.  Follow the recommendations for the specific technology you're using.<br /></span>

<p>We can use the <code>$USER</code> environmental variable to assign ownership to the account that we are currently signed in on (make sure you're not logged in as <code>root</code>).  This will allow us to easily create or edit the content in this directory:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo chown -R $USER:$USER /var/www/<span class="highlight">example.com</span>/html
</li><li class="line" prefix="$">sudo chown -R $USER:$USER /var/www/<span class="highlight">test.com</span>/html
</li></ul></code></pre>
<p>The permissions of our web roots should be correct already if you have not modified your <code>umask</code> value, but we can make sure by typing:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo chmod -R 755 /var/www
</li></ul></code></pre>
<p>Our directory structure is now configured and we can move on.</p>

<h2 id="step-two-create-sample-pages-for-each-site">Step Two: Create Sample Pages for Each Site</h2>

<p>Now that we have our directory structure set up, let's create a default page for each of our sites so that we will have something to display.</p>

<p>Create an <code>index.html</code> file in your first domain:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">nano /var/www/<span class="highlight">example.com</span>/html/index.html
</li></ul></code></pre>
<p>Inside the file, we'll create a really basic file that indicates what site we are currently accessing.  It will look like this:</p>
<div class="code-label " title="/var/www/example.com/html/index.html">/var/www/example.com/html/index.html</div><pre class="code-pre "><code langs=""><html>
    <head>
        <title>Welcome to <span class="highlight">Example.com</span>!</title>
    </head>
    <body>
        <h1>Success!  The <span class="highlight">example.com</span> server block is working!</h1>
    </body>
</html>
</code></pre>
<p>Save and close the file when you are finished.</p>

<p>Since the file for our second site is basically going to be the same, we can copy it over to our second document root like this:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">cp /var/www/<span class="highlight">example.com</span>/html/index.html /var/www/<span class="highlight">test.com</span>/html/
</li></ul></code></pre>
<p>Now, we can open the new file in our editor:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">nano /var/www/<span class="highlight">test.com</span>/html/index.html
</li></ul></code></pre>
<p>Modify it so that it refers to our second domain:</p>
<div class="code-label " title="/var/www/test.com/html/index.html">/var/www/test.com/html/index.html</div><pre class="code-pre "><code langs=""><html>
    <head>
        <title>Welcome to <span class="highlight">Test.com</span>!</title>
    </head>
    <body>
        <h1>Success!  The <span class="highlight">test.com</span> server block is working!</h1>
    </body>
</html>
</code></pre>
<p>Save and close this file when you are finished.  We now have some pages to display to visitors of our two domains.</p>

<h2 id="step-three-create-server-block-files-for-each-domain">Step Three: Create Server Block Files for Each Domain</h2>

<p>Now that we have the content we wish to serve, we need to actually create the server blocks that will tell Nginx how to do this.</p>

<p>By default, Nginx contains one server block called <code>default</code> which we can use as a template for our own configurations.  We will begin by designing our first domain's server block, which we will then copy over for our second domain and make the necessary modifications.</p>

<h3 id="create-the-first-server-block-file">Create the First Server Block File</h3>

<p>As mentioned above, we will create our first server block config file by copying over the default file:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo cp /etc/nginx/sites-available/default /etc/nginx/sites-available/<span class="highlight">example.com</span>
</li></ul></code></pre>
<p>Now, open the new file you created in your text editor with <code>sudo</code> privileges:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo nano /etc/nginx/sites-available/<span class="highlight">example.com</span>
</li></ul></code></pre>
<p>Ignoring the commented lines, the file will look similar to this:</p>
<div class="code-label " title="/etc/nginx/sites-available/example.com">/etc/nginx/sites-available/example.com</div><pre class="code-pre "><code langs="">server {
        listen 80 default_server;
        listen [::]:80 default_server;

        root /var/www/html;
        index index.html index.htm index.nginx-debian.html;

        server_name _;

        location / {
                try_files $uri $uri/ =404;
        }
}
</code></pre>
<p>First, we need to look at the listen directives.  <strong>Only one of our server blocks on the server can have the <code>default_server</code> option enabled.</strong>  This specifies which block should serve a request if the <code>server_name</code> requested does not match any of the available server blocks.  This shouldn't happen very frequently in real world scenarios since visitors will be accessing your site through your domain name.</p>

<p>You can choose to designate one of your sites as the "default" by including the <code>default_server</code> option in the <code>listen</code> directive, or you can leave the default server block enabled, which will serve the content of the <code>/var/www/html</code> directory if the requested host cannot be found.</p>

<p>In this guide, we'll leave the default server block in place to server non-matching requests, so we'll remove the <code>default_server</code> from this and the next server block.  You can choose to add the option to whichever of your server blocks makes sense to you.</p>
<div class="code-label " title="/etc/nginx/sites-available/example.com">/etc/nginx/sites-available/example.com</div><pre class="code-pre "><code langs="">server {
        listen 80;
        listen [::]:80;

        . . .
}
</code></pre>
<div class="code-label notes-and-warnings note" title="Note">Note</div><span class="note"><p>

You can check that the <code>default_server</code> option is only enabled in a single active file by typing:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">grep -R default_server /etc/nginx/sites-enabled/
</li></ul></code></pre>
<p>If matches are found uncommented in more than on file (shown in the leftmost column), Nginx will complain about an invalid configuration.<br /></p></span>

<p>The next thing we're going to have to adjust is the document root, specified by the <code>root</code> directive.  Point it to the site's document root that you created:</p>
<div class="code-label " title="/etc/nginx/sites-available/example.com">/etc/nginx/sites-available/example.com</div><pre class="code-pre "><code langs="">server {
        listen 80;
        listen [::]:80;

        root /var/www/<span class="highlight">example.com</span>/html;

}
</code></pre>
<p>Next, we need to modify the <code>server_name</code> to match requests for our first domain.  We can additionally add any aliases that we want to match.  We will add a <code>www.example.com</code> alias to demonstrate.</p>

<p>When you are finished, your file will look something like this:</p>
<div class="code-label " title="/etc/nginx/sites-available/example.com">/etc/nginx/sites-available/example.com</div><pre class="code-pre "><code langs="">server {
        listen 80;
        listen [::]:80;

        root /var/www/<span class="highlight">example.com</span>/html;
        index index.html index.htm index.nginx-debian.html;

        server_name <span class="highlight">example.com</span> www.<span class="highlight">example.com</span>;

        location / {
                try_files $uri $uri/ =404;
        }
}
</code></pre>
<p>That is all we need for a basic configuration.  Save and close the file to exit.</p>

<h3 id="create-the-second-server-block-file">Create the Second Server Block File</h3>

<p>Now that we have our initial server block configuration, we can use that as a basis for our second file.  Copy it over to create a new file:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo cp /etc/nginx/sites-available/<span class="highlight">example.com</span> /etc/nginx/sites-available/<span class="highlight">test.com</span>
</li></ul></code></pre>
<p>Open the new file with <code>sudo</code> privileges in your editor:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo nano /etc/nginx/sites-available/<span class="highlight">test.com</span>
</li></ul></code></pre>
<p>Again, make sure that you do not use the <code>default_server</code> option for the <code>listen</code> directive in this file if you've already used it elsewhere.  Adjust the <code>root</code> directive to point to your second domain's document root and adjust the <code>server_name</code> to match your second site's domain name (make sure to include any aliases).</p>

<p>When you are finished, your file will likely look something like this:</p>
<div class="code-label " title="/etc/nginx/sites-available/test.com">/etc/nginx/sites-available/test.com</div><pre class="code-pre "><code langs="">server {
        listen 80;
        listen [::]:80;

        root /var/www/<span class="highlight">test.com</span>/html;
        index index.html index.htm index.nginx-debian.html;

        server_name <span class="highlight">test.com</span> www.<span class="highlight">test.com</span>;

        location / {
                try_files $uri $uri/ =404;
        }
}
</code></pre>
<p>When you are finished, save and close the file.</p>

<h2 id="step-four-enable-your-server-blocks-and-restart-nginx">Step Four: Enable your Server Blocks and Restart Nginx</h2>

<p>Now that we have our server block files, we need to enable them.  We can do this by creating symbolic links from these files to the <code>sites-enabled</code> directory, which Nginx reads from during startup.</p>

<p>We can create these links by typing:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo ln -s /etc/nginx/sites-available/<span class="highlight">example.com</span> /etc/nginx/sites-enabled/
</li><li class="line" prefix="$">sudo ln -s /etc/nginx/sites-available/<span class="highlight">test.com</span> /etc/nginx/sites-enabled/
</li></ul></code></pre>
<p>These files are now in the enabled directory.  We now have three server blocks enabled, which are configured to respond based on their <code>listen</code> directive and the <code>server_name</code> (you can read more about how Nginx processes these directives <a href="https://indiareads/community/tutorials/understanding-nginx-server-and-location-block-selection-algorithms">here</a>):</p>

<ul>
<li><code>example.com</code>: Will respond to requests for <code>example.com</code> and <code>www.example.com</code></li>
<li><code>test.com</code>: Will respond to requests for <code>test.com</code> and <code>www.test.com</code></li>
<li><code>default</code>: Will respond to any requests on port 80 that do not match the other two blocks.</li>
</ul>

<p>In order to avoid a possible hash bucket memory problem that can arise from adding additional server names, we will go ahead and adjust a single value within our <code>/etc/nginx/nginx.conf</code> file.  Open the file now:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo nano /etc/nginx/nginx.conf
</li></ul></code></pre>
<p>Within the file, find the <code>server_names_hash_bucket_size</code> directive.  Remove the <code>#</code> symbol to uncomment the line:</p>
<div class="code-label " title="/etc/nginx/nginx.conf">/etc/nginx/nginx.conf</div><pre class="code-pre "><code langs="">http {
    . . .

    server_names_hash_bucket_size 64;

    . . .
}
</code></pre>
<p>Save and close the file when you are finished.</p>

<p>Next, test to make sure that there are no syntax errors in any of your Nginx files:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo nginx -t
</li></ul></code></pre>
<p>If no problems were found, restart Nginx to enable your changes:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo systemctl restart nginx
</li></ul></code></pre>
<p>Nginx should now be serving both of your domain names.</p>

<h2 id="step-five-modify-your-local-hosts-file-for-testing-optional">Step Five: Modify Your Local Hosts File for Testing(Optional)</h2>

<p>If you have not been using domain names that you own and instead have been using dummy values, you can modify your local computer's configuration to let you to temporarily test your Nginx server block configuration.</p>

<p>This will not allow other visitors to view your site correctly, but it will give you the ability to reach each site independently and test your configuration.  This basically works by intercepting requests that would usually go to DNS to resolve domain names.  Instead, we can set the IP addresses we want our local computer to go to when we request the domain names.</p>

<p></p><div class="code-label notes-and-warnings note" title="Note">Note</div><span class="note">
Make sure you are operating on your local computer during these steps and not your VPS server.  You will need to have root access, be a member of the administrative group, or otherwise be able to edit system files to do this.<br /></span>

<p>If you are on a Mac or Linux computer at home, you can edit the file needed by typing:</p>
<pre class="code-pre custom_prefix"><code langs=""><ul class="prefixed"><li class="line" prefix="local $">sudo nano /etc/hosts
</li></ul></code></pre>
<p>If you are on Windows, you can <a href="http://www.thewindowsclub.com/hosts-file-in-windows">find instructions for altering your hosts file</a> here.</p>

<p>You need to know your server's public IP address and the domains you want to route to the server.  Assuming that my server's public IP address is <code>203.0.113.5</code>, the lines I would add to my file would look something like this:</p>
<div class="code-label " title="/etc/hosts">/etc/hosts</div><pre class="code-pre "><code langs="">127.0.0.1   localhost
. . .

<span class="highlight">203.0.113.5 example.com www.example.com</span>
<span class="highlight">203.0.113.5 test.com www.test.com</span>
</code></pre>
<p>This will intercept any requests for <code>example.com</code> and <code>test.com</code> and send them to your server, which is what we want if we don't actually own the domains that we are using.</p>

<p>Save and close the file when you are finished.</p>

<h2 id="step-six-test-your-results">Step Six: Test your Results</h2>

<p>Now that you are all set up, you should test that your server blocks are functioning correctly.  You can do that by visiting the domains in your web browser:</p>
<pre class="code-pre "><code langs="">http://<span class="highlight">example.com</span>
</code></pre>
<p>You should see a page that looks like this:</p>

<p><img src="https://assets.digitalocean.com/articles/nginx_server_block_1404/first_block.png" alt="Nginx first server block" /></p>

<p>If you visit your second domain name, you should see a slightly different site:</p>
<pre class="code-pre "><code langs="">http://<span class="highlight">test.com</span>
</code></pre>
<p><img src="https://assets.digitalocean.com/articles/nginx_server_block_1404/second_block.png" alt="Nginx second server block" /></p>

<p>If both of these sites work, you have successfully configured two independent server blocks with Nginx.</p>

<p>At this point, if you adjusted your <code>hosts</code> file on your local computer in order to test, you'll probably want to remove the lines you added.</p>

<p>If you need domain name access to your server for a public-facing site, you will probably want to purchase a domain name for each of your sites.  You can learn how to <a href="https://indiareads/community/articles/how-to-set-up-a-host-name-with-digitalocean">set them up to point to your server</a> here.</p>

<h2 id="conclusion">Conclusion</h2>

<p>You should now have the ability to create server blocks for each domain you wish to host from the same server.  There aren't any real limits on the number of server blocks you can create, so long as your hardware can handle the traffic.</p>

    