<link rel='stylesheet' href='digital_ocean.css'><br><img src=https://community-cdn-digitalocean-com.global.ssl.fastly.net/assets/tutorials/images/large/ApacheVirtualHosts-twitter.png?1461607642/> <br> 
      <h3 id="introduction">Introduction</h3>

<p>The Apache web server is the most popular way of serving web content on the internet.  It accounts for more than half of all active websites on the internet and is extremely powerful and flexible.</p>

<p>Apache breaks its functionality and components into individual units that can be customized and configured independently.  The basic unit that describes an individual site or domain is called a <code>virtual host</code>.</p>

<p>These designations allow the administrator to use one server to host multiple domains or sites off of a single interface or IP by using a matching mechanism.  This is relevant to anyone looking to host more than one site off of a single VPS.</p>

<p>Each domain that is configured will direct the visitor to a specific directory holding that site's information, never indicating that the same server is also responsible for other sites.  This scheme is expandable without any software limit as long as your server can handle the load.</p>

<p>In this guide, we will walk you through how to set up Apache virtual hosts on an Ubuntu 16.04 VPS.  During this process, you'll learn how to serve different content to different visitors depending on which domains they are requesting.</p>

<h2 id="prerequisites">Prerequisites</h2>

<p>Before you begin this tutorial, you should <a href="https://indiareads/community/articles/initial-server-setup-with-ubuntu-16-04">create a non-root user</a> as described in steps 1-4 here.</p>

<p>You will also need to have Apache installed in order to work through these steps.  If you haven't already done so, you can get Apache installed on your server through <code>apt-get</code>:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo apt-get update
</li><li class="line" prefix="$">sudo apt-get install apache2
</li></ul></code></pre>
<p>After these steps are complete, we can get started.</p>

<p>For the purposes of this guide, our configuration will make a virtual host for <code>example.com</code> and another for <code>test.com</code>.  These will be referenced throughout the guide, but you should substitute your own domains or values while following along.</p>

<p>To learn <a href="https://indiareads/community/articles/how-to-set-up-a-host-name-with-digitalocean">how to set up your domain names with IndiaReads</a>, follow this link.  If you do <em>not</em> have domains available to play with, you can use dummy values.</p>

<p>We will show how to edit your local hosts file later on to test the configuration if you are using dummy values.  This will allow you to test your configuration from your home computer, even though your content won't be available through the domain name to other visitors.</p>

<h2 id="step-one-—-create-the-directory-structure">Step One — Create the Directory Structure</h2>

<p>The first step that we are going to take is to make a directory structure that will hold the site data that we will be serving to visitors.</p>

<p>Our <code>document root</code> (the top-level directory that Apache looks at to find content to serve) will be set to individual directories under the <code>/var/www</code> directory.  We will create a directory here for both of the virtual hosts we plan on making.</p>

<p>Within each of <em>these</em> directories, we will create a <code>public_html</code> folder that will hold our actual files.  This gives us some flexibility in our hosting.</p>

<p>For instance, for our sites, we're going to make our directories like this:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo mkdir -p /var/www/<span class="highlight">example.com</span>/public_html
</li><li class="line" prefix="$">sudo mkdir -p /var/www/<span class="highlight">test.com</span>/public_html
</li></ul></code></pre>
<p>The portions in red represent the domain names that we are wanting to serve from our VPS.</p>

<h2 id="step-two-—-grant-permissions">Step Two — Grant Permissions</h2>

<p>Now we have the directory structure for our files, but they are owned by our root user.  If we want our regular user to be able to modify files in our web directories, we can change the ownership by doing this:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo chown -R $USER:$USER /var/www/<span class="highlight">example.com</span>/public_html
</li><li class="line" prefix="$">sudo chown -R $USER:$USER /var/www/<span class="highlight">test.com</span>/public_html
</li></ul></code></pre>
<p>The <code>$USER</code> variable will take the value of the user you are currently logged in as when you press <strong>Enter</strong>.  By doing this, our regular user now owns the <code>public_html</code> subdirectories where we will be storing our content.</p>

<p>We should also modify our permissions a little bit to ensure that read access is permitted to the general web directory and all of the files and folders it contains so that pages can be served correctly:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo chmod -R 755 /var/www
</li></ul></code></pre>
<p>Your web server should now have the permissions it needs to serve content, and your user should be able to create content within the necessary folders.</p>

<h2 id="step-three-—-create-demo-pages-for-each-virtual-host">Step Three — Create Demo Pages for Each Virtual Host</h2>

<p>We have our directory structure in place.  Let's create some content to serve.</p>

<p>We're just going for a demonstration, so our pages will be very simple.  We're just going to make an <code>index.html</code> page for each site.</p>

<p>Let's start with <code>example.com</code>.  We can open up an <code>index.html</code> file in our editor by typing:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">nano /var/www/<span class="highlight">example.com</span>/public_html/index.html
</li></ul></code></pre>
<p>In this file, create a simple HTML document that indicates the site it is connected to.  My file looks like this:</p>
<div class="code-label " title="/var/www/example.com/public_html/index.html">/var/www/example.com/public_html/index.html</div><pre class="code-pre "><code langs=""><html>
  <head>
    <title>Welcome to <span class="highlight">Example.com</span>!</title>
  </head>
  <body>
    <h1>Success!  The <span class="highlight">example.com</span> virtual host is working!</h1>
  </body>
</html>
</code></pre>
<p>Save and close the file when you are finished.</p>

<p>We can copy this file to use as the basis for our second site by typing:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">cp /var/www/<span class="highlight">example.com</span>/public_html/index.html /var/www/<span class="highlight">test.com</span>/public_html/index.html
</li></ul></code></pre>
<p>We can then open the file and modify the relevant pieces of information:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">nano /var/www/<span class="highlight">test.com</span>/public_html/index.html
</li></ul></code></pre><div class="code-label " title="/var/www/test.com/public_html/index.html">/var/www/test.com/public_html/index.html</div><pre class="code-pre "><code langs=""><html>
  <head>
    <title>Welcome to <span class="highlight">Test.com</span>!</title>
  </head>
  <body> <h1>Success!  The <span class="highlight">test.com</span> virtual host is working!</h1>
  </body>
</html>
</code></pre>
<p>Save and close this file as well.  You now have the pages necessary to test the virtual host configuration.</p>

<h2 id="step-four-—-create-new-virtual-host-files">Step Four — Create New Virtual Host Files</h2>

<p>Virtual host files are the files that specify the actual configuration of our virtual hosts and dictate how the Apache web server will respond to various domain requests.</p>

<p>Apache comes with a default virtual host file called <code>000-default.conf</code> that we can use as a jumping off point.  We are going to copy it over to create a virtual host file for each of our domains.</p>

<p>We will start with one domain, configure it, copy it for our second domain, and then make the few further adjustments needed.  The default Ubuntu configuration requires that each virtual host file end in <code>.conf</code>.</p>

<h3 id="create-the-first-virtual-host-file">Create the First Virtual Host File</h3>

<p>Start by copying the file for the first domain:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo cp /etc/apache2/sites-available/000-default.conf /etc/apache2/sites-available/<span class="highlight">example.com</span>.conf
</li></ul></code></pre>
<p>Open the new file in your editor with root privileges:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo nano /etc/apache2/sites-available/<span class="highlight">example.com</span>.conf
</li></ul></code></pre>
<p>The file will look something like this (I've removed the comments here to make the file more approachable):</p>
<div class="code-label " title="/etc/apache2/sites-available/example.com.conf">/etc/apache2/sites-available/example.com.conf</div><pre class="code-pre "><code langs=""><VirtualHost *:80>
    ServerAdmin webmaster@localhost
    DocumentRoot /var/www/html
    ErrorLog ${APACHE_LOG_DIR}/error.log
    CustomLog ${APACHE_LOG_DIR}/access.log combined
</VirtualHost>
</code></pre>
<p>As you can see, there's not much here.  We will customize the items here for our first domain and add some additional directives.  This virtual host section matches <em>any</em> requests that are made on port 80, the default HTTP port.</p>

<p>First, we need to change the <code>ServerAdmin</code> directive to an email that the site administrator can receive emails through.</p>
<pre class="code-pre "><code langs="">ServerAdmin <span class="highlight">admin@example.com</span>
</code></pre>
<p>After this, we need to <em>add</em> two directives.  The first, called <code>ServerName</code>, establishes the base domain that should match for this virtual host definition.  This will most likely be your domain.  The second, called <code>ServerAlias</code>, defines further names that should match as if they were the base name.  This is useful for matching hosts you defined, like <code>www</code>:</p>
<pre class="code-pre "><code langs="">ServerName <span class="highlight">example.com</span>
ServerAlias <span class="highlight">www.example.com</span>
</code></pre>
<p>The only other thing we need to change for a basic virtual host file is the location of the document root for this domain.  We already created the directory we need, so we just need to alter the <code>DocumentRoot</code> directive to reflect the directory we created:</p>
<pre class="code-pre "><code langs="">DocumentRoot /var/www/<span class="highlight">example.com</span>/public_html
</code></pre>
<p>In total, our virtualhost file should look like this:</p>
<div class="code-label " title="/etc/apache2/sites-available/example.com.conf">/etc/apache2/sites-available/example.com.conf</div><pre class="code-pre "><code langs=""><VirtualHost *:80>
    ServerAdmin <span class="highlight">admin@example.com</span>
    ServerName <span class="highlight">example.com</span>
    ServerAlias <span class="highlight">www.example.com</span>
    DocumentRoot /var/www/<span class="highlight">example.com</span>/public_html
    ErrorLog ${APACHE_LOG_DIR}/error.log
    CustomLog ${APACHE_LOG_DIR}/access.log combined
</VirtualHost>
</code></pre>
<p>Save and close the file.</p>

<h3 id="copy-first-virtual-host-and-customize-for-second-domain">Copy First Virtual Host and Customize for Second Domain</h3>

<p>Now that we have our first virtual host file established, we can create our second one by copying that file and adjusting it as needed.</p>

<p>Start by copying it:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo cp /etc/apache2/sites-available/<span class="highlight">example.com</span>.conf /etc/apache2/sites-available/<span class="highlight">test.com</span>.conf
</li></ul></code></pre>
<p>Open the new file with root privileges in your editor:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo nano /etc/apache2/sites-available/<span class="highlight">test.com</span>.conf
</li></ul></code></pre>
<p>You now need to modify all of the pieces of information to reference your second domain.  When you are finished, it may look something like this:</p>
<div class="code-label " title="/etc/apache2/sites-available/test.com.conf">/etc/apache2/sites-available/test.com.conf</div><pre class="code-pre "><code langs=""><VirtualHost *:80>
    ServerAdmin <span class="highlight">admin@test.com</span>
    ServerName <span class="highlight">test.com</span>
    ServerAlias <span class="highlight">www.test.com</span>
    DocumentRoot /var/www/<span class="highlight">test.com</span>/public_html
    ErrorLog ${APACHE_LOG_DIR}/error.log
    CustomLog ${APACHE_LOG_DIR}/access.log combined
</VirtualHost>
</code></pre>
<p>Save and close the file when you are finished.</p>

<h2 id="step-five-—-enable-the-new-virtual-host-files">Step Five — Enable the New Virtual Host Files</h2>

<p>Now that we have created our virtual host files, we must enable them.  Apache includes some tools that allow us to do this.</p>

<p>We can use the <code>a2ensite</code> tool to enable each of our sites like this:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo a2ensite <span class="highlight">example.com</span>.conf
</li><li class="line" prefix="$">sudo a2ensite <span class="highlight">test.com</span>.conf
</li></ul></code></pre>
<p>Next, disable the default site defined in <code>000-default.conf</code>:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo a2dissite 000-default.conf
</li></ul></code></pre>
<p>When you are finished, you need to restart Apache to make these changes take effect:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo systemctl restart apache2
</li></ul></code></pre>
<p>In other documentation, you may also see an example using the <code>service</code> command:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo service apache2 restart
</li></ul></code></pre>
<p>This command will still work, but it may not give the output you're used to seeing on other systems, since it's now a wrapper around systemd's <code>systemctl</code>.</p>

<h2 id="step-six-—-set-up-local-hosts-file-optional">Step Six — Set Up Local Hosts File (Optional)</h2>

<p>If you haven't been using actual domain names that you own to test this procedure and have been using some example domains instead, you can at least test the functionality of this process by temporarily modifying the <code>hosts</code> file on your local computer.</p>

<p>This will intercept any requests for the domains that you configured and point them to your VPS server, just as the DNS system would do if you were using registered domains.  This will only work from your computer though, and is simply useful for testing purposes.</p>

<p>Make sure you are operating on your local computer for these steps and not your VPS server.  You will need to know the computer's administrative password or otherwise be a member of the administrative group.</p>

<p>If you are on a Mac or Linux computer, edit your local file with administrative privileges by typing:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo nano /etc/hosts
</li></ul></code></pre>
<p>If you are on a Windows machine, you can <a href="http://support.microsoft.com/kb/923947">find instructions on altering your hosts file</a> here.</p>

<p>The details that you need to add are the public IP address of your VPS server followed by the domain you want to use to reach that VPS.</p>

<p>For the domains that I used in this guide, assuming that my VPS IP address is <code>111.111.111.111</code>, I could add the following lines to the bottom of my hosts file:</p>
<div class="code-label " title="/etc/hosts">/etc/hosts</div><pre class="code-pre "><code langs="">127.0.0.1   localhost
127.0.1.1   guest-desktop
<span class="highlight">111.111.111.111 example.com</span>
<span class="highlight">111.111.111.111 test.com</span>
</code></pre>
<p>This will direct any requests for <code>example.com</code> and <code>test.com</code> on our computer and send them to our server at <code>111.111.111.111</code>.  This is what we want if we are not actually the owners of these domains in order to test our virtual hosts.</p>

<p>Save and close the file.</p>

<h2 id="step-seven-—-test-your-results">Step Seven — Test your Results</h2>

<p>Now that you have your virtual hosts configured, you can test your setup easily by going to the domains that you configured in your web browser:</p>
<pre class="code-pre "><code langs="">http://<span class="highlight">example.com</span>
</code></pre>
<p>You should see a page that looks like this:</p>

<p><img src="https://assets.digitalocean.com/articles/apache_virt_hosts_1404/example.png" alt="Apache virt host example" /></p>

<p>Likewise, if you can visit your second page:</p>
<pre class="code-pre "><code langs="">http://<span class="highlight">test.com</span>
</code></pre>
<p>You will see the file you created for your second site:</p>

<p><img src="https://assets.digitalocean.com/articles/apache_virt_hosts_1404/test.png" alt="Apache virt host test" /></p>

<p>If both of these sites work well, you've successfully configured <strong>two</strong> virtual hosts on the same server.</p>

<p>If you adjusted your home computer's hosts file, you may want to delete the lines you added now that you verified that your configuration works.  This will prevent your hosts file from being filled with entries that are not actually necessary. </p>

<p>If you need to access this long term, consider purchasing a domain name for each site you need and <a href="https://indiareads/community/articles/how-to-set-up-a-host-name-with-digitalocean">setting it up to point to your VPS server</a>.</p>

<h2 id="conclusion">Conclusion</h2>

<p>If you followed along, you should now have a single server handling two separate domain names.  You can expand this process by following the steps we outlined above to make additional virtual hosts.</p>

<p>There is no software limit on the number of domain names Apache can handle, so feel free to make as many as your server is capable of handling.</p>

    