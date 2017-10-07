<link rel='stylesheet' href='digital_ocean.css'><br> <br> 
      <h3 id="introduction">Introduction</h3>

<hr />

<p>This article will show you how to setup WordPress on the Lighttpd web server with the popular caching plugin W3 Total Cache. It is assumed that you already have setup a <a href="https://indiareads/community/articles/how-to-install-the-llmp-stack-linux-lighttpd-mysql-and-php-on-ubuntu-12-04">LLMP stack</a> and have PHP functioning with Lighttpd. We'll be doing a lot of manual configuration to make minify and page cache work with Lighttpd, as <a href="http://wordpress.org/plugins/w3-total-cache/">W3 Total Cache</a> doesn't support this web server out of the box (as it does with Apache and Nginx).</p>

<h2 id="enable-required-modules">Enable required modules</h2>

<hr />

<p>WordPress requires <code>mod_rewrite</code> for permalinks but this module is commented out in the Lighttpd configurationp file. So edit the file and remove the <strong>#</strong></p>
<pre class="code-pre "><code langs="">nano /etc/lighttpd/lighttpd.conf

server.modules = (
        "mod_access",
        "mod_alias",
        "mod_compress",
        "mod_redirect",
#       "mod_rewrite",
)
</code></pre>
<p>Change</p>
<pre class="code-pre "><code langs="">#       "mod_rewrite",
</code></pre>
<p>to</p>
<pre class="code-pre "><code langs="">        "mod_rewrite",
</code></pre>
<p>We also need access logs for our site, so enable the <strong>accesslog</strong> module.</p>
<pre class="code-pre "><code langs="">lighttpd-enable-mod accesslog
</code></pre>
<h2 id="create-a-virtual-host">Create a Virtual Host</h2>

<hr />

<p>Add a virtual host for the WordPress site-- make sure to replace <strong>example.com</strong> with your own domain name.</p>
<pre class="code-pre "><code langs="">nano /etc/lighttpd/lighttpd.conf

$HTTP["host"] =~ "(^|www\.)example.com$" {
    server.document-root = "/var/www/example.com"
    accesslog.filename = "/var/log/lighttpd/example.com-access.log"
    server.error-handler-404 = "/index.php"
}
</code></pre>
<p>Setting the "error-handler-404" to "index.php" is enough to get permalinks working. Reload the lighttpd service.</p>
<pre class="code-pre "><code langs="">service lighttpd force-reload
</code></pre>
<h2 id="download-and-install-wordpress">Download and Install WordPress</h2>

<hr />

<p>Before downloading WordPress onto your VPS, you'll create a MySQL database and a user with privileges for that database.</p>
<pre class="code-pre "><code langs="">echo "CREATE DATABASE wordpress" | mysql -u root -p
echo "GRANT ALL PRIVILEGES ON wordpress.* TO 'wpuser'@'localhost' IDENTIFIED BY 'S3cRet_pass'" | mysql -u root -p
</code></pre>
<p>Replace <strong>wpuser</strong> and <strong>S3cRet_pass</strong> with your own values. Download the latest version of WordPress.</p>
<pre class="code-pre "><code langs="">wget http://wordpress.org/latest.tar.gz
</code></pre>
<p>Extract the files inside the <strong>document-root</strong> of the virtual host. You'll now have a directory named "wordpress" in this location. Rename this to the name of your domain to match value of the <strong>server.document-root</strong> directive.</p>
<pre class="code-pre "><code langs="">cd /var/www
tar -xf ~/latest.tar.gz
mv wordpress example.com
</code></pre>
<p>Change the ownership of all files inside this directory to "www-data"</p>
<pre class="code-pre "><code langs="">chown www-data:www-data -R example.com/
</code></pre>
<p>Open the browser, enter your domain name and complete the installation of WordPress. We have to enable and choose a permalink structure. But before that we have a problem to solve: WordPress performs checks for Apache mod_rewrite before enabling permalinks which will fail on lighttpd. So we have to force WordPress to enable permalinks.</p>

<p>Create a <a href="http://codex.wordpress.org/Must_Use_Plugins">"Must Use" Plugin</a> directory.</p>
<pre class="code-pre "><code langs="">mkdir /var/www/example.com/wp-content/mu-plugins
</code></pre>
<p>Create a file inside it</p>
<pre class="code-pre "><code langs="">nano /var/www/example.com/wp-content/mu-plugins/rewrite.php
</code></pre>
<p>with the following code.</p>
<pre class="code-pre "><code langs=""><?php
add_filter( 'got_rewrite', '__return_true' );
</code></pre>
<p>The closing PHP tag <code>?></code> has been deliberately omitted.</p>

<p><img src="https://assets.digitalocean.com/articles/wordpress_w3/img1.png" alt="WordPress Permalinks" /></p>

<p>Login to <em>wp-admin</em>, navigate to <strong>Settings > Permalinks</strong> and choose a permalink structure.</p>

<h2 id="setting-up-w3-total-cache-plugin">Setting up W3 Total Cache Plugin</h2>

<hr />

<p>Configuring this plugin in lighttpd is different, as W3TC supports only Apache and Nginx, so we'll be doing much of the configuration manually. Install this plugin from <strong>Plugins > Add New</strong>, activate it and stop here. We won't be using this plugin's options page to set it up initially.</p>

<p>Edit the lighttpd configuration file and add rewrite directives for <a href="http://jesin.tk/how-to-use-php-to-minify-html-output/">minification</a> and page caching. The additional configuration will go inside the virtual host block after the <strong>error-handler-404</strong>.</p>
<pre class="code-pre "><code langs="">nano /etc/lighttpd/lighttpd.conf

$HTTP["host"] =~ "(^|www\.)example.com$" {
    server.document-root = "/var/www/example.com"
    accesslog.filename = "/var/log/lighttpd/example.com-access.log"
    server.error-handler-404 = "/index.php"

    #Rewrite rules for minified files
    url.rewrite-if-not-file = (
        "^/wp-content/cache/minify/(.+\.(css|js))$" => "/wp-content/plugins/w3-total-cache/pub/minify.php?file=$1"
    )

    #Rewrite rules for page cache enhanced
    #This is to prevent page cache rules from messing up minify rules
    $HTTP["url"] !~ "(.+\.(css|js|xml|html))" {

    #Bypass cache if the request contains any of these cookies
    $HTTP["cookie"] !~ "(comment_author|wp\-postpass|w3tc_logged_out|wordpress_logged_in|wptouch_switch_toggle)" {

    #Bypass cache for POST requests
    $HTTP["request-method"] != "POST" {

    #Bypass cache if query string isn't empty
    $HTTP["querystring"] !~ "(.*)" {
        url.rewrite-if-not-file = (
            "(.*)" => "/wp-content/cache/page_enhanced/example.com/$1/_index.html"
        )
    }
    }
    }
    }
}
</code></pre>
<p>Replace <em>example.com</em> in the <code>/wp-content/cache/page_enhanced/example.com/$1/_index.html</code> path with the exact domain using which you installed WordPress.</p>

<p>Save the file and reload lighttpd</p>
<pre class="code-pre "><code langs="">service lighttpd force-reload
</code></pre>
<p>Go to the WordPress administration panel (wp-admin), navigate to <strong>Plugins > Add New</strong>, search and install the plugin.</p>

<p><img src="https://assets.digitalocean.com/articles/wordpress_w3/img2.png" alt="Installing W3 Total Cache" /></p>

<p>Activate the plugin and come back to the SSH console without changing any settings.</p>

<p>The browser cache module of the W3 Total Cache plugin creates gzipped files from the page cache and minify cache, which will be served to users based on the "Accept-Encoding" request header. But lighttpd can compress content on the fly with the <strong>mod_compress</strong> module so we'll disable this option.</p>

<p>Using the <a href="https://indiareads/community/articles/intermediate-sed-manipulating-streams-of-text-in-a-linux-environment">sed</a> command, find and replace configuration entries.</p>
<pre class="code-pre "><code langs="">cd /var/www/example.com/
sed -i "s/'browsercache.cssjs.compression' => true/'browsercache.cssjs.compression' => false/" wp-content/w3tc-config/master.php
sed -i "s/'browsercache.html.compression' => true/'browsercache.html.compression' => false/" wp-content/w3tc-config/master.php
sed -i "s/'browsercache.other.compression' => true/'browsercache.other.compression' => false/" wp-content/w3tc-config/master.php
sed -i "s/'browsercache.cssjs.compression' => true/'browsercache.cssjs.compression' => false/" wp-content/cache/config/master.php
sed -i "s/'browsercache.html.compression' => true/'browsercache.html.compression' => false/" wp-content/cache/config/master.php
sed -i "s/'browsercache.other.compression' => true/'browsercache.other.compression' => false/" wp-content/cache/config/master.php
</code></pre>
<h3 id="minification">Minification</h3>

<hr />

<p>This option is known to break the design of themes and plugins, so use it only if you know it'll work for you. For this article the <a href="http://wordpress.org/themes/twentythirteen">Twenty Thirteen</a> theme was used, which works fine with minification.</p>

<p>Before enabling this option the "Auto Minify Test" has to be disabled. This is because the plugin requires a set of rewrite rules for the <em>Minify Auto</em> test to complete successfully. While we can try to convert them for lighttpd (from .htaccess or nginx.conf) it isn't worth the time, as auto minification works on the site even without these tests.</p>
<pre class="code-pre "><code langs="">cd /var/www/example.com/
sed -i "s/'minify.auto.disable_filename_length_test' => false/'minify.auto.disable_filename_length_test' => true/" wp-content/w3tc-config/master.php
sed -i "s/'minify.auto.disable_filename_length_test' => false/'minify.auto.disable_filename_length_test' => true/" wp-content/cache/config/master.php
</code></pre>
<p>Back in wp-admin navigate to <strong>Performance > General Settings</strong> and enable minify.</p>

<p><img src="https://assets.digitalocean.com/articles/wordpress_w3/img3.png" alt="Enable Minify" /></p>

<p>Open your WordPress site on the browser and look in the <strong></strong> section. You'll find minified CSS and JS like this:</p>
<pre class="code-pre "><code langs=""><!DOCTYPE html>
<!--[if IE 7]>
<html class="ie ie7" lang="en-US">
<![endif]-->
<!--[if IE 8]>
<html class="ie ie8" lang="en-US">
<![endif]-->
***
<html lang="en-US">
***
<head><link rel="stylesheet" type="text/css" href="http://example.com/wp-content/cache/minify/000000/M9AvKU_NK6ksycgsKklNzdNPy88rKdZPT81LLcpMzs8r1jFAV1FcUpmTCgA.css"media="all" />
<script type="text/javascript" src="http://example.com/wp-content/cache/minify/000000/M9bPKixNLarUMYYydHMz04sSS1L1cjPz4IJ6uYnF-XkgGihooF9SnppXUlmSkVlUkpqap59VrJ9WmpdckpmfVwwA.js"></script>
</code></pre>
<p>If the design of the site looks broken, it could mean incorrect minify rewrite rules in <code>lighttpd.conf</code> or you've forgotten to reload lighttpd.</p>

<h3 id="page-caching">Page Caching</h3>

<hr />

<p>Page caching creates static HTML files of your content and serves them to users with a rewrite rule. As we've already added rewrite rules we can enable page caching by going to <strong>wp-admin > Performance > General Settings</strong>.</p>

<p><img src="https://assets.digitalocean.com/articles/wordpress_w3/img4.png" alt="Page Cache" /></p>

<p>The default <em>Page Cache Method</em> is <em>Disk: Enhanced</em> however if you've accidentally changed it to something else you can't change it back as this option is disabled.</p>

<p>So we need to manually modify this by editing files.</p>
<pre class="code-pre "><code langs="">cd /var/www/example.com
sed -i "s/'pgcache.engine' => '\([a-z]\+\)'/'pgcache.engine' => 'file_generic'/" wp-content/w3tc-config/master.php
sed -i "s/'pgcache.engine' => '\([a-z]\+\)'/'pgcache.engine' => 'file_generic'/" wp-content/cache/config/master.php
</code></pre>
<p>This enables Page caching with <strong>Disk Enhanced</strong>. To test if it works use <code>curl</code> to request a page which is not cached.</p>
<pre class="code-pre "><code langs=""># curl -v -s -o /dev/null http://example.com/about/
* About to connect() to example.com port 80 (#0)
*   Trying 1.1.1.1... connected
> GET /about/ HTTP/1.1
> User-Agent: curl/7.23.1
> Host: example.com
> Accept: */*
>
< HTTP/1.1 200 OK
< Link: <http://example.com/?p=28>; rel=shortlink
< Last-Modified: Tue, 05 Nov 2013 15:55:53 GMT
< Vary:
< X-Pingback: http://example.com/xmlrpc.php
< Content-Type: text/html; charset=UTF-8
< Transfer-Encoding: chunked
< Date: Tue, 05 Nov 2013 15:55:53 GMT
< Server: lighttpd/1.4.31
<
{ [data not shown]
* Connection #0 to host example.com left intact
* Closing connection #0
</code></pre>
<p>Execute the same request again.</p>
<pre class="code-pre "><code langs=""># curl -v -s -o /dev/null http://example.com/about/
* About to connect() to example.com port 80 (#0)
*   Trying 1.1.1.1... connected
> GET /about/ HTTP/1.1
> User-Agent: curl/7.23.1
> Host: example.com
> Accept: */*
>
< HTTP/1.1 200 OK
< Vary: Accept-Encoding
< Content-Type: text/html
< Accept-Ranges: bytes
< ETag: "94995388"
< Last-Modified: Tue, 05 Nov 2013 15:55:53 GMT
< Content-Length: 23659
< Date: Tue, 05 Nov 2013 15:55:55 GMT
< Server: lighttpd/1.4.31
<
{ [data not shown]
* Connection #0 to host example.com left intact
* Closing connection #0
</code></pre>
<p>Notice the difference between both the headers. The first response has headers <strong>Link:</strong> and <strong>X-Pingback</strong> which are added by PHP. The second response was purely HTML so it doesn't have those headers. You can also view the cached pages on disk.</p>
<pre class="code-pre "><code langs="">root@wp-lighttpd:~# ls -lR /var/www/example.com/wp-content/cache/page_enhanced/www.example.com/
/var/www/example.com/wp-content/cache/page_enhanced/www.example.com/:
total 12
drwxr-xr-x 2 www-data www-data 4096 Nov  5 21:25 about
drwxr-xr-x 2 www-data www-data 4096 Nov  5 21:21 front-page
drwxr-xr-x 2 www-data www-data 4096 Nov  5 21:23 sample-page

/var/www/example.com/wp-content/cache/page_enhanced/www.example.com/about:
total 24
-rw-r--r-- 1 www-data www-data 23659 Nov  5 21:25 _index.html

/var/www/example.com/wp-content/cache/page_enhanced/www.example.com/front-page:
total 28
-rw-r--r-- 1 www-data www-data 25100 Nov  5 21:21 _index.html

/var/www/example.com/wp-content/cache/page_enhanced/www.example.com/sample-page:
total 28
-rw-r--r-- 1 www-data www-data 25837 Nov  5 21:23 _index.html
</code></pre>
<p>To check if the site supports compression use the <strong>--compressed</strong> option of curl.</p>
<pre class="code-pre "><code langs=""># curl -v -s -o /dev/null/ --compressed http://example.com/about/
* About to connect() to example.com port 80 (#0)
*   Trying 1.1.1.1... connected
> GET /about/ HTTP/1.1
> User-Agent: curl/7.23.1
> Host: www.example.com
> Accept: */*
> Accept-Encoding: deflate, gzip
>
< HTTP/1.1 200 OK
< Vary: Accept-Encoding
< Content-Encoding: gzip
< Last-Modified: Tue, 05 Nov 2013 15:55:53 GMT
< ETag: "2062104151"
< Content-Type: text/html
< Accept-Ranges: bytes
< Content-Length: 4819
< Date: Tue, 05 Nov 2013 16:01:03 GMT
< Server: lighttpd/1.4.31
<
{ [data not shown]
* Connection #0 to host www.example.com left intact
* Closing connection #0
</code></pre>
<p>Notice the <strong>Content-Length</strong> and <strong>Content-Encoding</strong> headers: the content length was previously 23659, but now it is only 4819.</p>

<h3 id="browser-cache">Browser Cache</h3>

<hr />

<p>This type of cache tells the browser how much time it can keep objects (like images, CSS and JS) in its own cache. This requires adding the <strong>Expires</strong> and <strong>Cache-Control</strong> headers which is done using the <code>mod_expire</code> module.</p>

<p>Enable this module</p>
<pre class="code-pre "><code langs="">lighttpd-enable-mod expire
</code></pre>
<p>and add the neccessary configuration inside the virtual host.</p>

<p><code>nano /etc/lighttpd/lighttpd.conf</code></p>
<pre class="code-pre "><code langs="">#Browser Cache
$HTTP["cookie"] !~ "(wordpress_logged_in)" {
$HTTP["url"] =~ "^/(.+\.(css|js|png|jpg|bmp|ico)\??.*)$" {
    expire.url = ( "" => "access plus 365 days" )
}
}
</code></pre>
<p>In this block, we check if the user isn't logged in first and then match the files with extensions mentioned. You can also add more extensions inside the brackets separated by "|". Reload lighttpd</p>
<pre class="code-pre "><code langs="">service lighttpd force-reload
</code></pre>
<p>and check for the new headers with curl.</p>
<pre class="code-pre "><code langs=""># curl -I example.com/wp-content/themes/twentythirteen/style.css

HTTP/1.1 200 OK
Expires: Wed, 05 Nov 2014 16:31:33 GMT
Cache-Control: max-age=31536000
Content-Type: text/css
Accept-Ranges: bytes
ETag: "2905279475"
Last-Modified: Thu, 24 Oct 2013 19:39:10 GMT
Content-Length: 52290
Date: Tue, 05 Nov 2013 16:31:33 GMT
Server: lighttpd/1.4.31
</code></pre>
<h2 id="updating-the-plugin">Updating the Plugin</h2>

<hr />

<p>This configuration was done on WordPress 3.7.1 with W3 Total Cache 0.9.3 and works well with these. Any future updates to W3 Total cache which changes the rewrite directory structure will break minifcation and page caching (though the latter isn't visible). So before upgrading this plugin, spin up a droplet with the <strong>WordPress on Ubuntu 12.10</strong> image, install W3 Total Cache and check out the .htaccess file for new rewrite rules.</p>

<p>It is easier to migrate them to lighttpd. Currently Apache uses the following rule for minify</p>
<pre class="code-pre "><code langs="">RewriteBase /wp-content/cache/minify/
RewriteRule ^(.+\.(css|js))$ ../../plugins/w3-total-cache/pub/minify.php?file=$1 [L]
</code></pre>
<p>In lighttpd this rule becomes</p>
<pre class="code-pre "><code langs="">url.rewrite-final = (
        "^/wp-content/cache/minify/(.+\.(css|js))$" => "/wp-content/plugins/w3-total-cache/pub/minify.php?file=$1"
    )
</code></pre>
<p>As for page caching Apache has</p>
<pre class="code-pre "><code langs="">RewriteRule .* "/wp-content/cache/page_enhanced/%{HTTP_HOST}/%{REQUEST_URI}/_index.html" [L]
</code></pre>
<p>Lighttpd doesn't allow server variables in the rewrite location so we have hard coded it.</p>
<pre class="code-pre "><code langs="">url.rewrite-if-not-file = (
    "(.*)" => "/wp-content/cache/page_enhanced/example.com/$1/_index.html"
)
</code></pre>
<p></p><div class="author">Submitted by: <a rel="author" href="http://jesin.tk/">Jesin A</a></div>

    