<link rel='stylesheet' href='digital_ocean.css'><br><img src=https://community-cdn-digitalocean-com.global.ssl.fastly.net/assets/tutorials/images/large/apache_caching_tw.jpg?1429024957/> <br> 
      <h3 id="what-is-caching">What is Caching?</h3>

<p>Caching is a method of improving server performance by allowing commonly requested content to be temporarily stored in a way that allows for faster access.  This speeds up processing and delivery by cutting out some resource intensive operations.</p>

<p>By creating effective caching rules, content that is suitable for caching will be stored to improve response times, conserve resources, and minimize load.  Apache provides a variety of caches suitable for speeding up different types of operations.  In this guide, we will be discussing how to configure Apache 2.4 on Ubuntu 14.04 using its various caching modules.</p>

<p>To learn more about developing general caching strategies, check out <a href="https://indiareads/community/tutorials/web-caching-basics-terminology-http-headers-and-caching-strategies">this article</a>.</p>

<h2 id="an-introduction-to-caching-in-apache">An Introduction to Caching in Apache</h2>

<p>Apache can cache content with varying levels of sophistication and scalability.  The project divides these into three groups according to the method in which the content is cached.  The general breakdown is:</p>

<ul>
<li><strong>File Caching</strong>: The most basic caching strategy, this simply opens files or file descriptors when the server starts and keeps them available to speed up access.</li>
<li><strong>Key-Value Caching</strong>: Mainly used for SSL and authentication caching, key-value caching uses a shared object model that can store items which are costly to compute repeatedly.</li>
<li><strong>Standard HTTP caching</strong>: The most flexible and generally useful caching mechanism, this three-state system can store responses and validate them when they expire.  This can be configured for performance or flexibility depending on your specific needs.</li>
</ul>

<p>A quick look at the above descriptions may reveal that the above methods have some overlap, but also that it may be helpful to use more than one strategy at the same time.  For instance, using a key-value store for your SSL sessions and enabling a standard HTTP cache for responses could allow you to take significant load off of your data sources and speed up many content delivery operations for your clients.</p>

<p>Now that you have a broad understanding of each of Apache's caching mechanisms, let's look at these systems in more detail.</p>

<h2 id="file-caching">File Caching</h2>

<h3 id="general-overview">General Overview</h3>

<ul>
<li><strong>Primary modules involved</strong>: <code>mod_file_cache</code></li>
<li><strong>Main use cases</strong>: storing either file contents or file descriptors when the server starts.  These are static representations that cannot reliably be changed until the server is restarted.</li>
<li><strong>Features</strong>: simple, improves performance of slow filesystems</li>
<li><strong>Drawbacks</strong>: experimental feature, does not respond to updates on the filesystem, must be used sparingly to fit within operating system's limitations, can only be used on static files</li>
</ul>

<h3 id="the-details">The Details</h3>

<p>The <code>mod_file_cache</code> module is mainly used to speed up file access on servers with slow filesystems.  It provides a choice of two configuration directives, both of which aim to accelerate the process of serving static files by performing some of the work when the server is started rather than when the files are requested.</p>

<p>The <code>CacheFile</code> directive is used to specify the path to files on disk that you would like to accelerate access to.  When Apache is started, Apache will open the static files that were specified and cache the file handle, avoiding the need to open the file when it is requested.  The number of files that can be opened in this way is subject to the limitations set by your operating system.</p>

<p>The <code>MMapFile</code> directive also opens files when Apache is first started.  However, <code>MMapFile</code> caches the file's contents in memory rather than just the file handler.  This allows for faster performance for those pages, but it has some serious limitations.  It maintains no record of the amount of memory it has used, so it is possible to run out of memory.  Also note that child processes will copy any of the allocated memory, which can result in faster resource depletion than you initially may anticipate.  Only use this directive sparingly.</p>

<p>These directives are evaluated only when Apache starts.  This means that you cannot rely on Apache to pick up changes made after it has started.  Only use these on static files that will not change for the lifetime of the Apache session.  Depending on how the files are modified, the server may be notified of changes, but this is not expected behavior and will not always work correctly.  If changes must be made to files passed to these directives, restart Apache after the changes have been made.</p>

<h3 id="how-to-enable-file-caching">How To Enable File Caching</h3>

<p>File caching is provided by the <code>mod_file_cache</code> module.  To use this functionality, you'll need to enable the module.</p>

<p>When running Ubuntu 14.04, the module will be installed but disabled when you install Apache.  You can enable the module by typing:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo a2enmod file_cache
</li></ul></code></pre>
<p>Afterwards, you should edit the main configuration file to set up your file caching directives.  Open the file by typing:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo nano /etc/apache2/apache2.conf
</li></ul></code></pre>
<p>To set up file handle caching, use the <code>CacheFile</code> directive.  This directive takes a list of file paths, separated by spaces, like this:</p>
<div class="code-label " title="/etc/apache2/apache2.conf">/etc/apache2/apache2.conf</div><pre class="code-pre "><code class="code-highlight language-apache">CacheFile /var/www/html/index.html /var/www/html/somefile.index
</code></pre>
<p>When the server is restarted, Apache will open the files listed and store their file handles in the cache for faster access.</p>

<p>If, instead, you wish to map a few files directly into memory, you can use the <code>MMapFile</code> directive.  Its syntax is basically the same as the last directive, in that it simply takes a list of file paths:</p>
<div class="code-label " title="/etc/apache2/apache2.conf">/etc/apache2/apache2.conf</div><pre class="code-pre "><code class="code-highlight language-apache">MMapFile /var/www/html/index.html /var/www/html/somefile.index
</code></pre>
<p>In practice, there would be no reason to configure <em>both</em> <code>CacheFile</code> and <code>MMapFile</code> for the same set of files, but you could use both on different sets of files.</p>

<p>When you are finished, you can save and close the files.  Check the configuration file syntax by typing:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo apachectl configtest
</li></ul></code></pre>
<p>If the last line reads <code>Syntax OK</code>, you can safely restart your Apache instance:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo service apache2 restart
</li></ul></code></pre>
<p>Apache will restart, caching the file contents or handlers depending on the directives you used.</p>

<h2 id="key-value-caching">Key-Value Caching</h2>

<h3 id="general-overview">General Overview</h3>

<ul>
<li><strong>Primary modules involved</strong>: <code>mod_socache_dbm</code>, <code>mod_socache_dc</code>, <code>mod_socache_memcache</code>, <code>mod_socache_shmcb</code></li>
<li><strong>Supporting modules involved</strong>: <code>mod_authn_socache</code>, <code>mod_ssl</code></li>
<li><strong>Main use cases</strong>: storing SSL sessions or authentication details, SSL stapling</li>
<li><strong>Features</strong>: shared object cache to store complex resources, can assist in SSL session caching and stapling, flexible backends</li>
<li><strong>Drawbacks</strong>: has no validation mechanisms, need to configure separate software for more performant/flexible backends, some bugs in code</li>
</ul>

<h3 id="the-details">The Details</h3>

<p>Key-value caching is more complex than file caching and has more focused benefits.  Also known as a shared object cache, Apache's key-value cache is mainly used to avoid repeating expensive operations involved with setting up a client's access to content, as opposed to the content itself.  Specifically, it can be used to cache authentication details, SSL sessions, and to provide SSL stapling.</p>

<p></p><div class="code-label notes-and-warnings note" title="Note">Note</div><span class="note">
Currently, there are some issues with <em>every</em> shared object cache provider.  References to the issues will be outlined below.  Take these into consideration when evaluating whether to enable this feature.<br /></span>

<p>The actual caching is accomplished through the use of one of the shared object caching provider modules.  These are:</p>

<ul>
<li><strong><code>mod_socache_dbm</code></strong>: This backend uses the simple <code>dbm</code> database engine, which is a file-based key-value store that makes use of hashing and fixed-size buckets.  This provider suffers from some memory leaks, so for most cases it is recommended to use <code>mod_socache_shmcb</code> instead.</li>
<li><strong><code>mod_socache_dc</code></strong>: This provider uses the distcache session caching software.  This project has not been updated since 2004 and is not even packaged for some distributions, so use with a healthy dose of caution.</li>
<li><strong><code>mod_socache_memcache</code></strong>: This uses the memcache distributed memory object cache for storing items.  This is the best option for a distributed cache among multiple servers.  Currently, it does not properly expire entries, but a <a href="https://bz.apache.org/bugzilla/show_bug.cgi?id=55445">patch</a> was committed to the trunk of Apache's version control that fixes the issue.</li>
<li><strong><code>mod_socache_shmcb</code></strong>: Currently, this is the best option for key-value caching.  This caches to a cyclic buffer in shared memory, which will remove entries as it becomes full.  It currently chokes on <a href="https://bz.apache.org/bugzilla/show_bug.cgi?id=57023">entries over 11k in size</a>.</li>
</ul>

<p>Along with the above provider modules, additional modules will be required depending on the objects being cached.  For instance, to cache SSL sessions or to configure SSL stapling, <code>mod_ssl</code> must be enabled, which will provide the <code>SSLSessionCache</code> and <code>SSLStaplingCache</code> directives respectively.  Similarly, to set up authentication caching, the <code>mod_authn_socache</code> module must be enabled so that the <code>AuthnCacheSOCache</code> directive can be set.</p>

<h3 id="how-to-enable-key-value-caching">How To Enable Key-Value Caching</h3>

<p>With the above bugs and caveats in mind, if you still wish to configure this type of caching in Apache, follow along below.</p>

<p>The method used to set up the key-value cache will depend on what it will be used for and what provider you are using.  We'll go over the basics of both authentication caching and SSL session caching below.</p>

<p>Currently, there is <a href="https://bz.apache.org/bugzilla/show_bug.cgi?id=54342">a bug with authentication caching</a> that prevents passing arguments to the cache provider.  So any providers that do not provide default settings to fall back on will have issues.</p>

<h4 id="authentication-caching">Authentication Caching</h4>

<p>Authentication caching is useful if you are using an expensive authentication method, such as LDAP or database authentication.  These types of operations can have a significant impact on performance if the backend must be hit every time an authentication request is made.</p>

<p>Setting up caching involves modifying your existing authentication configuration (we will not cover how to set up authentication in this guide).  The modifications themselves will be much the same regardless of the backend authentication method.  We'll use <code>mod_socache_shmcb</code> for our demonstration.</p>

<p>First, enable the <code>authn_socache</code> module and the <code>mod_socache_shmcb</code> provider module by typing:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo a2enmod authn_socache
</li><li class="line" prefix="$">sudo a2enmod socache_shmcb
</li></ul></code></pre>
<p>Open your main Apache configuration file so that you can specify this shared cache backend for use with authentication:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo nano /etc/apache2/apache2.conf
</li></ul></code></pre>
<p>Inside, towards the top of the file, add the <code>AuthnCacheSOCache</code> directive.  Specify that <code>shmcb</code> should be used as the provider.  If the bug discussed earlier preventing option passing is fixed by the time you read this, you can specify a location and size for the cache.  The number is in bytes, so the commented example will result in a 512 kilobyte cache:</p>
<div class="code-label " title="/etc/apache2/apache2.conf">/etc/apache2/apache2.conf</div><pre class="code-pre "><code class="code-highlight language-apache">AuthnCacheSOCache shmcb

# If the bug preventing passed arguments to the provider gets fixed,
# you can customize the location and size like this
#AuthnCacheSOCache shmcb:${APACHE_RUN_DIR}/auth_cache(512000)
</code></pre>
<p>Save and close the file when you are finished.</p>

<p>Next, open your virtual host configuration page that has authentication configured.  We'll assume you're using the <code>000-default.conf</code> virtual host config, but you should modify it to reflect your environment:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo nano /etc/apache2/sites-enabled/000-default.conf
</li></ul></code></pre>
<p>In the location where you've configured authentication, modify the block to add caching.  Specifically, you need to add the <code>AuthnCacheProvideFor</code> to tell it which authentication sources to cache, add a cache timeout with <code>AuthnCacheTimeout</code>, and add <code>socache</code> to the <code>AuthBasicProvider</code> list ahead of your conventional authentication method.  The results will look something like this:</p>
<div class="code-label " title="/etc/apache2/sites-enabled/000-default.conf">/etc/apache2/sites-enabled/000-default.conf</div><pre class="code-pre "><code class="code-highlight language-apache"><VirtualHost *:80>

    . . .

    <Directory /var/www/html/private>
        AuthType Basic
        AuthName "Restricted Files"
        AuthBasicProvider <span class="highlight">socache</span> file
        AuthUserFile /etc/apache/.htpasswd
        <span class="highlight">AuthnCacheProvideFor file</span>
        <span class="highlight">AuthnCacheTimeout 300</span>
        Require valid-user
    </Directory>
</VirtualHost>
</code></pre>
<p>The above example is for file authentication, which probably won't benefit from caching very much.  However, the implmentation should be very similar when using other authentiation methods.  The only substantial difference would be where the "file" specification is in the above example, the other authentication method would be used instead.</p>

<p>Save and close the file.  Restart Apache to implement your caching changes:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo service apache2 restart
</li></ul></code></pre>
<h4 id="ssl-session-caching">SSL Session Caching</h4>

<p>The handshake that must be performed to establish an SSL connection carries significant overhead.  As such, caching the session data to avoid this initialization step for further requests can potentially skirt this penalty.  The shared object cache is a perfect place for this.</p>

<p>If you have SSL already configured for your Apache server, <code>mod_ssl</code> will be enabled.  On Ubuntu, this means that an <code>ssl.conf</code> file has been moved to the <code>/etc/apache2/mods-enabled</code> directory.  This actually already sets up caching.  Inside, you will see some lines like this:</p>
<div class="code-label " title="/etc/apache2/mods-enabled/ssl.conf">/etc/apache2/mods-enabled/ssl.conf</div><pre class="code-pre "><code class="code-highlight language-apache">. . .

SSLSessionCache         shmcb:${APACHE_RUN_DIR}/ssl_scache(512000)
SSLSessionCacheTimeout  300

. . .
</code></pre>
<p>This is actually enough to set up session caching.  To test this, you can use OpenSSL's connection client.  Type:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">openssl s_client -connect 127.0.0.1:443 -reconnect -no_ticket | grep Session-ID
</li></ul></code></pre>
<p>If the session ID is the same in all of the results, your session cache is working correctly.  Press CTRL-C to exit back to the terminal.</p>

<h2 id="standard-http-caching">Standard HTTP Caching</h2>

<h3 id="general-overview">General Overview</h3>

<ul>
<li><strong>Primary modules involved</strong>: <code>mod_cache</code></li>
<li><strong>Supporting modules involved</strong>: <code>mod_cache_disk</code>, <code>mod_cache_socache</code></li>
<li><strong>Main use cases</strong>: Caching general content</li>
<li><strong>Features</strong>: Can correctly interpret HTTP caching headers, can revalidate stale entries, can be deployed for maximum speed or flexibility depending on your needs</li>
<li><strong>Drawbacks</strong>: Can leak sensitive data if incorrectly configured, must use additional modules to correctly set the caching policy</li>
</ul>

<h3 id="the-details">The Details</h3>

<p>The HTTP protocol encourages and provides the mechanisms for caching responses all along the content delivery path.  Any computer that touches the content can potentially cache each item for a certain amount of time depending on the caching policies set forth at the content's origins and the computer's own caching rules.</p>

<p>The Apache HTTP caching mechanism caches responses according to the HTTP caching policies it sees.  This is a general purpose caching system that adheres to the same rules that any intermediary server would follow that has a hand in the delivery.  This makes this system very flexible and powerful and allows you to leverage the headers that you should already be setting on your content (we'll cover how to do this below).</p>

<p>Apache's HTTP cache is also known as a "three state" cache.  This is because the content it has stored can be in one of three states.  It can be fresh, meaning it is allowed to be served to clients with no further checking, it can be stale, meaning that the TTL on the content has expired, or it can be non-existent if the content is not found in the cache.</p>

<p>If the content becomes stale, at the next request, the cache can revalidate it by checking the content at the origin.  If it hasn't changed, it can reset the freshness date and serve the current content.  Otherwise, it fetches the changed content and stores that for the length of time allowed by its caching policy.</p>

<h4 id="module-overview">Module Overview</h4>

<p>The HTTP caching logic is available through the <code>mod_cache</code> module.  The actual caching is done with one of the caching providers.  Typically, the cache is stored on disk using the <code>mod_cache_disk</code> module, but shared object caching is also available through the <code>mod_cache_socache</code> module.</p>

<p>The <code>mod_cache_disk</code> module caches on disk, so it can be useful if you are proxying content from a remote location, generating it from a dynamic process, or just trying to speed things up by caching on a faster disk than your content typically resides on.  This is the most well-tested provider and should probably be your first choice in most cases.  The cache is not cleaned automatically, so a tool called <code>htcacheclean</code> must be run occasionally to slim down the cache.  This can be run manually, set up as a regular <code>cron</code> job, or run as a daemon.</p>

<p>The <code>mod_cache_socache</code> module caches to one of the shared object providers (the same ones discussed in the last section).  This can potentially have better performance than <code>mod_cache_disk</code> (depending on which shared cache provider is selected).  However, it is much newer and relies on the shared object providers, which have the bugs discussed earlier.  Comprehensive testing is recommended before implementing the <code>mod_cache_socache</code> option.</p>

<h4 id="http-cache-placement">HTTP Cache Placement</h4>

<p>Apache's HTTP cache can be deployed in two different configurations depending on your needs.</p>

<p>If the <code>CacheQuickHandler</code> is set to "on", the cache will be checked very early in the request handling process.  If content is found, it will be served directly without any further handling.  This means that it is incredibly quick, but it also means that it does not allow for processes like authentication for content.  If there is content in your cache that normally requires authentication or access control, it will be accessible to <strong>anyone</strong> without authentication if the <code>CacheQuickHandler</code> is set to "on". </p>

<p>Basically, this emulates a separate cache in front of your web server.  If your web server needs to do any kind of conditional checking, authentication, or authorization, this will not happen.  Apache will not even evaluate directives within <code><Location></code> or <code><Directory></code> blocks.  Note that <code>CacheQuickHandler</code> is set to "on" by <strong>default</strong>!</p>

<p>If the <code>CacheQuickHandler</code> is set to "off", the cache will be checked significantly later in the request processing sequence.  Think of this configuration as placing the cache between your Apache processing logic and your actual content.  This will allow the conventional processing directives to be run prior to retrieving content from the cache.  Setting this to "off" trades a bit of speed for the ability to process requests more deeply.</p>

<h3 id="how-to-configure-standard-http-caching">How To Configure Standard HTTP Caching</h3>

<p>In order to enable caching, you'll need to enable the <code>mod_cache</code> module as well as one of its caching providers.  As we stated above, <code>mod_cache_disk</code> is well tested, so we will rely on that.</p>

<h4 id="enabling-the-modules">Enabling the Modules</h4>

<p>On an Ubuntu system, you can enable these modules by typing:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo a2enmod cache
</li><li class="line" prefix="$">sudo a2enmod cache_disk
</li></ul></code></pre>
<p>This will enable the caching functionality the next time the server is restarted.</p>

<p>You will also need to install the <code>apache2-utils</code> package, which contains the <code>htcacheclean</code> utility used to pare down the cache when necessary.  You can install this by typing:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo apt-get update
</li><li class="line" prefix="$">sudo apt-get install apache2-utils
</li></ul></code></pre>
<h4 id="modifying-the-global-configuration">Modifying the Global Configuration</h4>

<p>Most of the configuration for caching will take place within individual virtual host definitions or location blocks.  However, enabling <code>mod_cache_disk</code> also enables a global configuration that can be used to specify some general attributes.  Open that file now to take a look:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo nano /etc/apache/mods-enabled/cache_disk.conf
</li></ul></code></pre>
<p>With the comments removed, the file should look like this:</p>
<div class="code-label " title="/etc/apache2/mods-enabled/cache_disk.conf">/etc/apache2/mods-enabled/cache_disk.conf</div><pre class="code-pre "><code class="code-highlight language-apache"><IfModule mod_cache_disk.c>
    CacheRoot /var/cache/apache2/mod_cache_disk
    CacheDirLevels 2
    CacheDirLength 1
</IfModule>
</code></pre>
<p>The <code>IfModule</code> wrapper tells Apache to only worry about these directives if the <code>mod_cache_disk</code> module is enabled.  The <code>CacheRoot</code> directive specifies the location on disk where the cache will be maintained.  The <code>CacheDirLevels</code> and <code>CacheDirLength</code> both contribute towards defining how the cache directory structure will be built.</p>

<p>An <code>md5</code> hash of the URL being served will be created as the key used to store the data.  The data will be organized into directories derived from the beginning characters of each hash.  <code>CacheDirLevels</code> specifies the number of subdirectories to create and <code>CacheDirLength</code> specifies how many characters to use as the name of each directory.  So a hash of <code>b1946ac92492d2347c6235b4d2611184</code> with the default values shown above would be filed in a directory structure of <code>b/1/946ac92492d2347c6235b4d2611184</code>.  Usually, you won't need to modify these values, but it's good to know what they're used for.</p>

<p></p><div class="code-label notes-and-warnings note" title="Note">Note</div><span class="note">
If you choose to modify the <code>CacheRoot</code> value, you'll have to open the <code>/etc/default/apache2</code> file and modify the value of the <code>HTCACHECLEAN_PATH</code> to match your selection.  This is used to clean the cache at regular intervals, so it must have the correct location of the cache.<br /></span>

<p>Some other values you can set in this file are <code>CacheMaxFileSize</code> and <code>CacheMinFileSize</code> which set the ranges of file sizes in bytes that Apache will commit to the cache, as well as <code>CacheReadSize</code> and <code>CacheReadTime</code>, which allows you to wait and buffer content before sending to the client.  This can be useful if the content resides somewhere other than this server.</p>

<h4 id="modifying-the-virtual-server">Modifying the Virtual Server</h4>

<p>Most of the configuration for caching will happen on a more granular level, either in the virtual host definition or in a specific location block.</p>

<p>Open one of your virtual host files to follow along.  We'll assume you're using the default file in this guide:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo nano /etc/apache2/sites-enabled
</li></ul></code></pre>
<p>In the virtual host block, outside of any location block, we can begin configuring some of the caching properties.  In this guide, we'll assume that we want to turn the <code>CacheQuickHandler</code> off so that more processing is done.  This allows us up more complete caching rules.</p>

<p>We will also take this opportunity to configure cache locking.  This is a system of file locks that Apache will use when it is checking in with the content origin to see whether content is still valid.  During the time when this query is being satisfied, if additional requests for the same content come in, it would result in additional requests to the backend resource, which could cause load spikes.</p>

<p>Setting a cache lock for a resource during validation tells Apache that the resource is currently being refreshed.  During this time, the stale resource can be served with a warning header indicating its state.  We'll set this up with a cache lock directory in the <code>/tmp</code> folder.  We'll allow a maximum of 5 seconds for a lock to be considered valid.  These examples are taken directly from Apache's documentation, so they should work well for our purposes.</p>

<p>We will also tell Apache to ignore the <code>Set-Cookie</code> headers and not store them in the cache.  Doing so will prevent Apache from accidentally leaking user-specific cookies out to other parties.  The <code>Set-Cookie</code> header will be stripped before the headers are cached.</p>
<div class="code-label " title="/etc/apache2/sites-enabled/000-default.conf">/etc/apache2/sites-enabled/000-default.conf</div><pre class="code-pre "><code class="code-highlight language-apache"><VirtualHost *:80>
    ServerAdmin webmaster@localhost
    DocumentRoot /var/www/html
    ErrorLog ${APACHE_LOG_DIR}/error.log
    CustomLog ${APACHE_LOG_DIR}/access.log combined

    <span class="highlight">CacheQuickHandler off</span>

    <span class="highlight">CacheLock on</span>
    <span class="highlight">CacheLockPath /tmp/mod_cache-lock</span>
    <span class="highlight">CacheLockMaxAge 5</span>

    <span class="highlight">CacheIgnoreHeaders Set-Cookie</span>
</VirtualHost>
</code></pre>
<p>We still need to actually enable caching for this virtual host.  We can do this with the <code>CacheEnable</code> directive.  If this is set in a virtual host block, we would need to provide the caching method (<code>disk</code> or <code>socache</code>) as well as the requested URIs that should be cached.  For example, to cache all responses, this could be set to <code>CacheEnable disk /</code>, but if you only wanted to cache responses under the <code>/public</code> URI, you could set this to <code>CacheEnable disk /public</code>.</p>

<p>We will take a different route by enabling our cache within a specific location block.  Doing so means we don't have to provide a URI path to the <code>CacheEnable</code> command.  Any URI that would be served from that location will be cached.  We will also turn on the <code>CacheHeader</code> directive so that our response headers will indicate whether the cache was used to serve the request or not.</p>

<p>Another directive we'll set is <code>CacheDefaultExpire</code> so that we can set an expiration (in seconds) if neither the <code>Expires</code> nor the <code>Last-Modified</code> headers are set on the content.  Similarly, we'll set <code>CacheMaxExpire</code> to cap the amount of time items will be saved.  We'll set the <code>CacheLastModifiedFactor</code> so that Apache can create an expiration date if it has a <code>Last-Modified</code> date, but no expiration.  The factor is multiplied by the time since modification to set a reasonable expiration.</p>
<div class="code-label " title="/etc/apache2/sites-enabled/000-default.conf">/etc/apache2/sites-enabled/000-default.conf</div><pre class="code-pre "><code class="code-highlight language-apache"><VirtualHost *:80>
    ServerAdmin webmaster@localhost
    DocumentRoot /var/www/html
    ErrorLog ${APACHE_LOG_DIR}/error.log
    CustomLog ${APACHE_LOG_DIR}/access.log combined

    CacheQuickHandler off

    CacheLock on
    CacheLockPath /tmp/mod_cache-lock
    CacheLockMaxAge 5

    CacheIgnoreHeaders Set-Cookie

    <span class="highlight"><Location /></span>
        <span class="highlight">CacheEnable disk</span>
        <span class="highlight">CacheHeader on</span>

        <span class="highlight">CacheDefaultExpire 600</span>
        <span class="highlight">CacheMaxExpire 86400</span>
        <span class="highlight">CacheLastModifiedFactor 0.5</span>
    <span class="highlight"></Location></span>
</VirtualHost>
</code></pre>
<p>Save and close your file when you've configured everything that you need.</p>

<p>Check your entire configuration for syntax errors by typing:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo apachectl configtest
</li></ul></code></pre>
<p>If no errors are reported, restart your service by typing:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo service apache2 restart
</li></ul></code></pre>
<h2 id="setting-expires-and-caching-headers-on-content">Setting Expires and Caching Headers on Content</h2>

<p>In the above configuration, we configured HTTP caching, which relies on HTTP headers.  However, none of the content we're serving actually has the <code>Expires</code> or <code>Cache-Control</code> headers needed to make intelligent caching decisions.  To set these headers, we need to take advantage of a few more modules.</p>

<p>The <code>mod_expires</code> module can set both the <code>Expires</code> header and the <code>max-age</code> option in the <code>Cache-Control</code> header.  The <code>mod_headers</code> module can be used to add more specific <code>Cache-Control</code> options to tune the caching policy further.</p>

<p>We can enable both of these modules by typing:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo a2enmod expires
</li><li class="line" prefix="$">sudo a2enmod headers
</li></ul></code></pre>
<p>After enabling these modules, we can go straight to modifying our virtual host file again:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo nano /etc/apache/sites-enabled/000-default.conf
</li></ul></code></pre>
<p>The <code>mod_expires</code> module provides just three directives.  The <code>ExpiresActive</code> turns expiration processing on in a certain context by setting it to "on".  The other two directives are very similar to each other.  The <code>ExpiresDefault</code> directive sets the default expiration time, and the <code>ExpiresByType</code> sets the expiration time according to the MIME type of the content.  Both of these will set the <code>Expires</code> and the <code>Cache-Control</code> "max-age" to the correct values.</p>

<p>These two settings can take two different syntaxes.  The first is simply "A" or "M" followed by a number of seconds.  This sets the expiration in relation to the last time the content was "accessed" or "modified" respectively.  For example, these both would expire content 30 seconds after it was accessed.</p>
<pre class="code-pre "><code langs="">ExpiresDefault A30
ExpireByType text/html A30
</code></pre>
<p>The other syntax allows for more verbose configuration.  It allows you to use units other than seconds that are easier for humans to calculate.  It also uses the full word "access" or "modification".  The entire expiration configuration should be kept in quotes, like this:</p>
<pre class="code-pre "><code langs="">ExpiresDefault "modification plus 2 weeks 3 days 1 hour"
ExpiresByType text/html "modification plus 2 weeks 3 days 1 hour"
</code></pre>
<p>For our purposes, we'll just set a default expiration.  We will start by setting it to 5 minutes so that if we make a mistake while getting familiar, it won't be stored on our clients' computers for an extremely long time.  When we're more confident in our ability to select policies appropriate for our content, we can adjust this to something more aggressive:</p>
<div class="code-label " title="/etc/apache2/sites-enabled/000-default.conf">/etc/apache2/sites-enabled/000-default.conf</div><pre class="code-pre "><code class="code-highlight language-apache"><VirtualHost *:80>
    ServerAdmin webmaster@localhost
    DocumentRoot /var/www/html
    ErrorLog ${APACHE_LOG_DIR}/error.log
    CustomLog ${APACHE_LOG_DIR}/access.log combined

    CacheQuickHandler off

    CacheLock on
    CacheLockPath /tmp/mod_cache-lock
    CacheLockMaxAge 5

    CacheIgnoreHeaders Set-Cookie

    <Location />
        CacheEnable disk
        CacheHeader on

        CacheDefaultExpire 600
        CacheMaxExpire 86400
        CacheLastModifiedFactor 0.5

        <span class="highlight">ExpiresActive on</span>
        <span class="highlight">ExpiresDefault "access plus 5 minutes"</span>
    </Location>
</VirtualHost>
</code></pre>
<p>This will set our <code>Expires</code> header to five minutes in the future and set <code>Cache-Control max-age=300</code>.  In order to refine our caching policy further, we can use the <code>Header</code> directive.  We can use the <code>merge</code> option to add additional <code>Cache-Control</code> options.  You can call this multiple times and add whichever additional policies you'd like.  Check out <a href="https://indiareads/community/tutorials/web-caching-basics-terminology-http-headers-and-caching-strategies">this guide</a> to get an idea about the caching policies you'd like to set for your content.  For our example, we'll just set "public" so that other caches can be sure that they're allowed to store copies.</p>

<p>To set <code>ETags</code> for static content on our site (to use for validation), we can use the <code>FileETag</code> directive.  This will work for static content.  For dynamically generated content, you're application will be responsible for correctly generating <code>ETags</code>.</p>

<p>We use the directive to set the attributes that Apache will use to calculate the <code>Etag</code>.  This can be <code>INode</code>, <code>MTime</code>, <code>Size</code>, or <code>All</code> depending on if we want to modify the <code>ETag</code> whenever the file's <code>inode</code> changes, its modification time changes, its size changes, or all of the above.  You can provide more than one value, and you can modify the inherited setting in child contexts by preceding the new settings with a <code>+</code> or <code>-</code>.  For our purposes, we'll just use "all" so that all changes are registered:</p>
<div class="code-label " title="/etc/apache2/sites-enabled/000-default.conf">/etc/apache2/sites-enabled/000-default.conf</div><pre class="code-pre "><code class="code-highlight language-apache"><VirtualHost *:80>
    ServerAdmin webmaster@localhost
    DocumentRoot /var/www/html
    ErrorLog ${APACHE_LOG_DIR}/error.log
    CustomLog ${APACHE_LOG_DIR}/access.log combined

    CacheQuickHandler off

    CacheLock on
    CacheLockPath /tmp/mod_cache-lock
    CacheLockMaxAge 5

    CacheIgnoreHeaders Set-Cookie

    <Location />
        CacheEnable disk
        CacheHeader on

        CacheDefaultExpire 600
        CacheMaxExpire 86400
        CacheLastModifiedFactor 0.5

        ExpiresActive on
        ExpiresDefault "access plus 5 minutes"

        <span class="highlight">Header merge Cache-Control public</span>
        <span class="highlight">FileETag</span> All
    </Location>
</VirtualHost>
</code></pre>
<p>This will add "public" (separated by a comma) to whatever value <code>Cache-Control</code> already has and will include an <code>ETag</code> for our static content.</p>

<p>When you are finished, save and close the file.  Check the syntax of your changes by typing:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo apachectl configtest
</li></ul></code></pre>
<p>If no errors were found, restart your service to implement your caching policies:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo service apache2 restart
</li></ul></code></pre>
<h2 id="conclusion">Conclusion</h2>

<p>Configuring caching with Apache can seem like a daunting job due to how many options there are.  Luckily, it is easy to start simple and then grow as you require more complexity.  Most administrators will not require each of the caching types.</p>

<p>When configuring caching, keep in mind the specific problems that you're trying to solve to avoid getting lost in the different implementation choices.  Most users will benefit from at least setting up headers.  If you are proxying or generating content, setting an HTTP cache may be helpful.  Shared object caching is useful for specific tasks like storing SSL sessions or authentication details if you are using a backend provider.  File caching can probably be limited to those with slow systems.</p>

    