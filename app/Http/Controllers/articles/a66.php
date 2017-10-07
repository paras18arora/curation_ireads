<link rel='stylesheet' href='digital_ocean.css'><br><img src=https://community-cdn-digitalocean-com.global.ssl.fastly.net/assets/tutorials/images/large/redis_cashing_tw.png?1426699789/> <br> 
      <h2 id="introduction">Introduction</h2>

<p>Redis is an open-source key value store that can operate as both an in-memory store and as cache. Redis is a data structure server that can be used as a database server on its own, or paired with a relational database like MySQL to speed things up, as we're doing in this tutorial.</p>

<p>For this tutorial, Redis will be configured as a cache for WordPress to alleviate the redundant and time-consuming database queries used to render a WordPress page. The result is a WordPress site which is much faster, uses less database resources, and provides a tunable persistent cache. This guide applies to Ubuntu 14.04.</p>

<p>While every site is different, below is an example benchmark of a default Wordpress installation home page with and without Redis, as configured from this guide. Chrome developer tools were used to test with browser caching disabled.</p>

<p>Default WordPress home page without Redis:</p>

<p>804ms page load time</p>

<p>Default WordPress home page with Redis:</p>

<p>449ms page load time</p>

<p><span class="note"><strong>Note:</strong> This implementation of Redis caching for WordPress relies on a well-commented but third-party script. The script is hosted on IndiaReads's asset server, but was developed externally. If you would like to make your own implementation of Redis caching for WordPress, you will need to do some more work based on the concepts presented here.<br /></span></p>

<h3 id="redis-vs-memcached">Redis vs. Memcached</h3>

<p>Memcached is also a popular cache choice. However, at this point, Redis does everything Memcached can do, with a much larger feature set. This <a href="http://stackoverflow.com/questions/10558465/memcache-vs-redis">Stack Overflow page</a> has some general information as an overview or introduction to persons new to Redis.</p>

<h3 id="how-does-the-caching-work">How does the caching work?</h3>

<p>The first time a WordPress page is loaded, a database query is performed on the server. Redis remembers, or <em>caches</em>, this query. So, when another user loads the Wordpress page, the results are provided from Redis and from memory without needing to query the database.</p>

<p>The Redis implementation used in this guide works as a persistent object cache for WordPress (no expiration). An object cache works by caching the SQL queries in memory which are needed to load a WordPress page.</p>

<p>When a page loads, the resulting SQL query results are provided from memory by Redis, so the query does not have to hit the database. The result is much faster page load times, and less server impact on database resources. If a query is not available in Redis, the database provides the result and Redis adds the result to its cache.</p>

<p>If a value is updated in the database (for example, a new post or page is created in WordPress) the Redis value for that query is invalidated to prevent bad cached data from being presented.</p>

<p>If you run into problems with caching, the Redis cache can be purged by using the <code>flushall</code> command from the Redis command line:</p>
<pre class="code-pre "><code langs="">redis-cli
</code></pre>
<p>Once you see the prompt, type:</p>
<pre class="code-pre "><code langs="">flushall
</code></pre>
<p>Additional Reference: <a href="http://codex.wordpress.org/Class_Reference/WP_Object_Cache">WordPress Object Cache Documentation</a></p>

<h3 id="prerequisites">Prerequisites</h3>

<p>Before starting this guide, you'll need to set up a sudo user and install WordPress.</p>

<ul>
<li>Ubuntu 14.04 Droplet (1 GB or higher recommended)</li>
<li>Add a <a href="https://indiareads/community/tutorials/how-to-add-and-delete-users-on-an-ubuntu-14-04-vps">sudo user</a></li>
<li>Install WordPress. This guide has been tested with <a href="https://indiareads/community/tutorials/how-to-install-wordpress-on-ubuntu-14-04">these instructions</a>, although there are many ways to install WordPress</li>
</ul>

<h2 id="step-1-—-install-redis">Step 1 — Install Redis</h2>

<p>In order to use Redis with WordPress, two packages need to be installed: <code>redis-server</code> and <code>php5-redis</code>. The <code>redis-server</code> package provides Redis itself, while the <code>php5-redis</code> package provides a PHP extension for PHP applications like WordPress to communicate with Redis.</p>

<p>Install the softare:</p>
<pre class="code-pre "><code langs="">sudo apt-get install redis-server php5-redis
</code></pre>
<h2 id="step-2-—-configure-redis-as-a-cache">Step 2 — Configure Redis as a Cache</h2>

<p>Redis can operate both as a NoSQL database store as well as a cache. For this guide and use case, Redis will be configured as a cache. In order to do this, the following settings are required.</p>

<p>Edit the file <code>/etc/redis/redis.conf</code> and add the following lines at the bottom:</p>
<pre class="code-pre "><code langs="">sudo nano /etc/redis/redis.conf
</code></pre>
<p>Add these lines at the end of the file:</p>
<pre class="code-pre "><code langs="">maxmemory 256mb
maxmemory-policy allkeys-lru
</code></pre>
<p>When changes are complete, save and close the file.</p>

<h2 id="step-3-—-obtain-redis-cache-backend-script">Step 3 — Obtain Redis Cache Backend Script</h2>

<p>This PHP script for WordPress was originally developed by <a href="https://github.com/ericmann/Redis-Object-Cache/raw/master/object-cache.php">Eric Mann</a>. It is a Redis object cache backend for WordPress.</p>

<p>Download the <code>object-cache.php</code> script. This download is from IndiaReads's asset server, but <strong>this is a third-party script</strong>. You should read the comments in the script to see how it works.</p>

<p>Download the PHP script:</p>
<pre class="code-pre "><code langs="">wget https://assets.digitalocean.com/articles/wordpress_redis/object-cache.php
</code></pre>
<p>Move the file to the <code>/wp-content</code> directory of your WordPress installation:</p>
<pre class="code-pre "><code langs="">sudo mv object-cache.php <span class="highlight">/var/www/html</span>/wp-content/
</code></pre>
<p>Depending on your WordPress installation, your location may be different.</p>

<h2 id="step-4-—-enable-cache-settings-in-wp-config-php">Step 4 — Enable Cache Settings in wp-config.php</h2>

<p>Next, edit the <code>wp-config.php</code> file to add a cache key salt with the name of your site (or any string you would like). </p>
<pre class="code-pre "><code langs="">nano /var/www/html/wp-config.php
</code></pre>
<p>Add this line at the end of the <code>* Authentication Unique Keys and Salts.</code> section:</p>
<pre class="code-pre "><code langs="">define('WP_CACHE_KEY_SALT', '<span class="highlight">example.com</span>');
</code></pre>
<p>You can use your domain name or another string as the salt.</p>

<blockquote>
<p><strong>Note:</strong> For users hosting more than one WordPress site, each site can share the same Redis installation as long as it has its own unique cache key salt.</p>
</blockquote>

<p>Also, add the following line after the <code>WP_CACHE_KEY_SALT</code> line to create a persistent cache with the Redis object cache plugin:</p>
<pre class="code-pre "><code langs="">define('WP_CACHE', true);
</code></pre>
<p>All together, your file should look like this:</p>
<pre class="code-pre "><code langs=""> * Authentication Unique Keys and Salts.

. . .

define('NONCE_SALT',       'put your unique phrase here');

define('WP_CACHE_KEY_SALT', '<span class="highlight">example.com</span>');
define('WP_CACHE', true);
</code></pre>
<p>Save and close the file.</p>

<h2 id="step-5-—-restart-redis-and-apache">Step 5 — Restart Redis and Apache</h2>

<p>Finally, restart <code>redis-service</code> and <code>apache2</code>.</p>

<p>Restart Redis:</p>
<pre class="code-pre "><code langs="">sudo service redis-server restart
</code></pre>
<p>Restart Apache:</p>
<pre class="code-pre "><code langs="">sudo service apache2 restart
</code></pre>
<p>Restart <code>php5-fpm</code> if you are using it; this is not part of the basic installation on IndiaReads:</p>
<pre class="code-pre "><code langs="">sudo service php5-fpm restart 
</code></pre>
<p>That's it! Your WordPress site is now using Redis caching. If you check your page load speeds and resource use, you should notice improvements.</p>

<h2 id="monitor-redis-with-redis-cli">Monitor Redis with redis-cli</h2>

<p>To monitor Redis, use the <code>redis-cli</code> command like so:</p>
<pre class="code-pre "><code langs="">redis-cli monitor
</code></pre>
<p>When you run this command, you will see the real-time output of Redis serving cached queries. If you don't see anything, visit your website and reload a page.</p>

<p>Below is example output from a WordPress site configured per this guide using Redis:</p>
<pre class="code-pre "><code langs="">OK
1412273195.815838 "monitor"
1412273198.428472 "EXISTS" "example.comwp_:default:is_blog_installed"
1412273198.428650 "GET" "example.comwp_:default:is_blog_installed"
1412273198.432252 "EXISTS" "example.comwp_:options:notoptions"
1412273198.432443 "GET" "example.comwp_:options:notoptions"
1412273198.432626 "EXISTS" "example.comwp_:options:alloptions"
1412273198.432799 "GET" "example.comwp_:options:alloptions"
1412273198.433572 "EXISTS" "example.comwp_site-options:0:notoptions"
1412273198.433729 "EXISTS" "example.comwp_:options:notoptions"
1412273198.433876 "GET" "example.comwp_:options:notoptions"
1412273198.434018 "EXISTS" "example.comwp_:options:alloptions"
1412273198.434161 "GET" "example.comwp_:options:alloptions"
1412273198.434745 "EXISTS" "example.comwp_:options:notoptions"
1412273198.434921 "GET" "example.comwp_:options:notoptions"
1412273198.435058 "EXISTS" "example.comwp_:options:alloptions"
1412273198.435193 "GET" "example.comwp_:options:alloptions"
1412273198.435737 "EXISTS" "example.comwp_:options:notoptions"
1412273198.435885 "GET" "example.comwp_:options:notoptions"
1412273198.436022 "EXISTS" "example.comwp_:options:alloptions"
1412273198.436157 "GET" "example.comwp_:options:alloptions"
1412273198.438298 "EXISTS" "example.comwp_:options:notoptions"
1412273198.438418 "GET" "example.comwp_:options:notoptions"
1412273198.438598 "EXISTS" "example.comwp_:options:alloptions"
1412273198.438700 "GET" "example.comwp_:options:alloptions"
1412273198.439449 "EXISTS" "example.comwp_:options:notoptions"
1412273198.439560 "GET" "example.comwp_:options:notoptions"
1412273198.439746 "EXISTS" "example.comwp_:options:alloptions"
1412273198.439844 "GET" "example.comwp_:options:alloptions"
1412273198.440764 "EXISTS" "example.comwp_:options:notoptions"
1412273198.440868 "GET" "example.comwp_:options:notoptions"
1412273198.441035 "EXISTS" "example.comwp_:options:alloptions"
1412273198.441149 "GET" "example.comwp_:options:alloptions"
1412273198.441813 "EXISTS" "example.comwp_:options:notoptions"
1412273198.441913 "GET" "example.comwp_:options:notoptions"
1412273198.442023 "EXISTS" "example.comwp_:options:alloptions"
1412273198.442121 "GET" "example.comwp_:options:alloptions"
1412273198.442652 "EXISTS" "example.comwp_:options:notoptions"
1412273198.442773 "GET" "example.comwp_:options:notoptions"
1412273198.442874 "EXISTS" "example.comwp_:options:alloptions"
1412273198.442974 "GET" "example.comwp_:options:alloptions"
</code></pre>
<p>Press <code>CTRL-C</code> to stop the output.</p>

<p>This is useful for seeing exactly what queries Redis is processing.</p>

<h3 id="conclusion">Conclusion</h3>

<p>After following this guide, WordPress will now be configured to use Redis as a cache on Ubuntu 14.04.</p>

<p>Below are some additional security and administration guides for WordPress that may be of interest:</p>

<p><a href="https://indiareads/community/tutorials/how-to-configure-secure-updates-and-installations-in-wordpress-on-ubuntu">How To Configure Secure Updates and Installations in WordPress on Ubuntu</a></p>

<p><a href="https://indiareads/community/tutorials/how-to-use-wpscan-to-test-for-vulnerable-plugins-and-themes-in-wordpress">How To Use WPScan to Test for Vulnerable Plugins and Themes in Wordpress</a></p>

<p><a href="https://indiareads/community/tutorials/how-to-use-wp-cli-to-manage-your-wordpress-site-from-the-command-line">How To Use WP-CLI to Manage your WordPress Site from the Command Line</a></p>

    