<link rel='stylesheet' href='digital_ocean.css'><br> <br> 
      <h3 id="prelude">Prelude</h3>

<hr />

<p>Nginx includes a FastCGI module which has directives for caching dynamic content that are served from the PHP backend. Setting this up removes the need for additional page caching solutions like reverse proxies (think <a href="https://indiareads/community/articles/how-to-install-and-configure-varnish-with-apache-on-ubuntu-12-04--3">Varnish</a>) or application specific plugins. Content can also be excluded from caching based on the request method, URL, cookies, or any other server variable.</p>

<h2 id="enabling-fastcgi-caching-on-your-vps">Enabling FastCGI Caching on your VPS</h2>

<hr />

<p>This article assumes that you've already setup and configured <a href="https://indiareads/community/articles/how-to-install-linux-nginx-mysql-php-lemp-stack-on-ubuntu-12-04">Nginx with PHP</a> on your droplet. Edit the <a href="https://indiareads/community/articles/how-to-set-up-nginx-virtual-hosts-server-blocks-on-ubuntu-12-04-lts--3">Virtual Host</a> configuration file for which caching has to be enabled.</p>
<pre class="code-pre "><code langs="">nano /etc/nginx/sites-enabled/vhost
</code></pre>
<p>Add the following lines to the top of the file outside the <strong>server { }</strong> directive:</p>
<pre class="code-pre "><code langs="">fastcgi_cache_path /etc/nginx/cache levels=1:2 keys_zone=MYAPP:100m inactive=60m;
fastcgi_cache_key "$scheme$request_method$host$request_uri";
</code></pre>
<p>The "fastcgi<em>cache</em>path" directive specifies the location of the cache (/etc/nginx/cache), its size (100m), memory zone name (MYAPP), the subdirectory levels, and the <em>inactive` timer.</em></p>

<p>The location can be anywhere on the hard disk; however, the size must be less than your droplet's RAM + <a href="https://indiareads/community/articles/how-to-add-swap-on-ubuntu-12-04">Swap</a> or else you'll receive an error that reads "Cannot allocate memory." We will look at the "levels" option in the purging section-- if a cache isn't accessed for a particular amount of time specified by the "inactive" option (60 minutes here), then Nginx removes it.</p>

<p>The "fastcgi<em>cache</em>key" directive specifies how the the cache filenames will be hashed. Nginx encrypts an accessed file with MD5 based on this directive.</p>

<p>Next, move the location directive that passes PHP requests to php5-fpm. Inside "location ~ .php$ { }" add the following lines.</p>
<pre class="code-pre "><code langs="">fastcgi_cache MYAPP;
fastcgi_cache_valid 200 60m;
</code></pre>
<p>The "fastcgi<em>cache" directive references to the memory zone name which we specified in the "fastcgi</em>cache_path" directive and stores the cache in this area.</p>

<p>By default Nginx stores the cached objects for a duration specified by any of these headers: <strong>X-Accel-Expires/Expires/Cache-Control.</strong> </p>

<p>The "fastcgi<em>cache</em>valid" directive is used to specify the default cache lifetime if these headers are missing. In the statement we entered above, only responses with a status code of 200 is cached. Other response codes can also be specified.</p>

<p><strong>Do a configuration test</strong></p>
<pre class="code-pre "><code langs="">service nginx configtest
</code></pre>
<p><strong>Reload Nginx if everything is OK</strong></p>
<pre class="code-pre "><code langs="">service nginx reload
</code></pre>
<p>The complete vhost file will look like this:</p>
<pre class="code-pre "><code langs="">fastcgi_cache_path /etc/nginx/cache levels=1:2 keys_zone=MYAPP:100m inactive=60m;
fastcgi_cache_key "$scheme$request_method$host$request_uri";

server {
    listen   80;

    root /usr/share/nginx/html;
    index index.php index.html index.htm;

    server_name example.com;

    location / {
        try_files $uri $uri/ /index.html;
    }

    location ~ \.php$ {
        try_files $uri =404;
        fastcgi_pass unix:/var/run/php5-fpm.sock;
        fastcgi_index index.php;
        include fastcgi_params;
        fastcgi_cache MYAPP;
        fastcgi_cache_valid 200 60m;
    }
}
</code></pre>
<p>Next we will do a test to see if caching works.</p>

<h2 id="testing-fastcgi-caching-on-your-vps">Testing FastCGI Caching on your VPS</h2>

<hr />

<p>Create a PHP file which outputs a UNIX timestamp.</p>
<pre class="code-pre "><code langs=""> /usr/share/nginx/html/time.php
</code></pre>
<p>Insert</p>
<pre class="code-pre "><code langs=""><?php
echo time();
?>
</code></pre>
<p>Request this file multiple times using <strong>curl</strong> or your web browser.</p>
<pre class="code-pre "><code langs="">root@droplet:~# curl http://localhost/time.php;echo
1382986152
root@droplet:~# curl http://localhost/time.php;echo
1382986152
root@droplet:~# curl http://localhost/time.php;echo
1382986152
</code></pre>
<p>If caching works properly, you should see the same timestamp on all requests as the response is cached. </p>

<p>Do a <em>recursive</em> listing of the cache location to find the cache of this request.</p>
<pre class="code-pre "><code langs="">root@droplet:~# ls -lR /etc/nginx/cache/
/etc/nginx/cache/:
total 0
drwx------ 3 www-data www-data 60 Oct 28 18:53 e

/etc/nginx/cache/e:
total 0
drwx------ 2 www-data www-data 60 Oct 28 18:53 18

/etc/nginx/cache/e/18:
total 4
-rw------- 1 www-data www-data 117 Oct 28 18:53 b777c8adab3ec92cd43756226caf618e
</code></pre>
<p>The naming convention will be explained in the purging section.</p>

<p>We can also make Nginx add a "X-Cache" header to the response, indicating if the cache was missed or hit.</p>

<p>Add the following above the <strong>server { }</strong> directive:</p>
<pre class="code-pre "><code langs="">add_header X-Cache $upstream_cache_status;
</code></pre>
<p>Reload the Nginx service and do a verbose request with curl to see the new header.</p>
<pre class="code-pre "><code langs="">root@droplet:~# curl -v http://localhost/time.php
* About to connect() to localhost port 80 (#0)
*   Trying 127.0.0.1...
* connected
* Connected to localhost (127.0.0.1) port 80 (#0)
> GET /time.php HTTP/1.1
> User-Agent: curl/7.26.0
> Host: localhost
> Accept: */*
>
* HTTP 1.1 or later with persistent connection, pipelining supported
< HTTP/1.1 200 OK
< Server: nginx
< Date: Tue, 29 Oct 2013 11:24:04 GMT
< Content-Type: text/html
< Transfer-Encoding: chunked
< Connection: keep-alive
< X-Cache: HIT
<
* Connection #0 to host localhost left intact
1383045828* Closing connection #0
</code></pre>
<h2 id="setting-cache-exceptions">Setting Cache Exceptions</h2>

<hr />

<p>Some dynamic content such as authentication required pages shouldn't be cached. Such content can be excluded from being cached based on server variables like "request<em>uri," "request</em>method," and "http_cookie." </p>

<p>Here is a sample configuration which must be used in the <strong>server{ }</strong> context.</p>
<pre class="code-pre "><code langs="">#Cache everything by default
set $no_cache 0;

#Don't cache POST requests
if ($request_method = POST)
{
    set $no_cache 1;
}

#Don't cache if the URL contains a query string
if ($query_string != "")
{
    set $no_cache 1;
}

#Don't cache the following URLs
if ($request_uri ~* "/(administrator/|login.php)")
{
    set $no_cache 1;
}

#Don't cache if there is a cookie called PHPSESSID
if ($http_cookie = "PHPSESSID")
{
    set $no_cache 1;
}
</code></pre>
<p>To apply the "$no_cache" variable to the appropriate directives, place the following lines inside <strong>location ~ .php$ { }</strong></p>
<pre class="code-pre "><code langs="">fastcgi_cache_bypass $no_cache;
fastcgi_no_cache $no_cache;
</code></pre>
<p>The "fasctcgi<em>cache</em>bypass" directive ignores existing cache for requests related to the conditions set by us previously. The "fastcgi<em>no</em>cache" directive doesn't cache the request at all if the specified conditions are met.</p>

<h2 id="purging-the-cache">Purging the Cache</h2>

<hr />

<p>The naming convention of the cache is based on the variables we set for the "fastcgi<em>cache</em>key" directive.</p>
<pre class="code-pre "><code langs="">fastcgi_cache_key "$scheme$request_method$host$request_uri";
</code></pre>
<p>According to these variables, when we requested "http://localhost/time.php" the following would've been the actual values:</p>
<pre class="code-pre "><code langs="">fastcgi_cache_key "httpGETlocalhost/time.php";
</code></pre>
<p>Passing this string through <a href="http://jesin.tk/tools/md5-encryption-tool/">MD5 hashing</a> would output the following string:</p>
<pre class="code-pre "><code langs="">b777c8adab3ec92cd43756226caf618e
</code></pre>
<p>This will form the filename of the cache as for the subdirectories we entered "levels=1:2."  Therefore, the first level of the directory will be named with <strong>1</strong> character from the last of this MD5 string which is <strong>e</strong>; the second level will have the last <strong>2</strong> characters after the first level i.e. <strong>18</strong>. Hence, the entire directory structure of this cache is as follows:</p>
<pre class="code-pre "><code langs="">/etc/nginx/cache/e/18/b777c8adab3ec92cd43756226caf618e
</code></pre>
<p>Based on this cache naming format you can develop a purging script in your favorite language. For this tutorial, I'll provide a simple PHP script which purges the cache of a <strong>POST</strong>ed URL.</p>

<p><code>/usr/share/nginx/html/purge.php</code></p>

<p><strong>Insert</strong></p>
<pre class="code-pre "><code langs=""><?php
$cache_path = '/etc/nginx/cache/';
$url = parse_url($_POST['url']);
if(!$url)
{
    echo 'Invalid URL entered';
    die();
}
$scheme = $url['scheme'];
$host = $url['host'];
$requesturi = $url['path'];
$hash = md5($scheme.'GET'.$host.$requesturi);
var_dump(unlink($cache_path . substr($hash, -1) . '/' . substr($hash,-3,2) . '/' . $hash));
?>
</code></pre>
<p>Send a POST request to this file with the URL to purge.</p>
<pre class="code-pre "><code langs="">curl -d 'url=http://www.example.com/time.php' http://localhost/purge.php
</code></pre>
<p>The script will output <em>true</em> or <em>false</em> based on whether the cache was purged to not. Make sure to exclude this script from being cached and also restrict access.</p>

<p>Submitted by: <a rel="author" href="http://jesin.tk/">Jesin A</a></p>

    