<link rel='stylesheet' href='digital_ocean.css'><br><img src=https://community-cdn-digitalocean-com.global.ssl.fastly.net/assets/tutorials/images/large/NGINX_tw.png?1426699777/> <br> 
      <h3 id="introduction">Introduction</h3>

<p>In this guide, we will discuss Nginx's http proxying capabilities, which allow Nginx to pass requests off to backend http servers for further processing. Nginx is often set up as a reverse proxy solution to help scale out infrastructure or to pass requests to other servers that are not designed to handle large client loads.</p>

<p>Along the way, we will discuss how to scale out using Nginx's built-in load balancing capabilities.  We will also explore buffering and caching to improve the performance of proxying operations for clients.</p>

<h2 id="general-proxying-information">General Proxying Information</h2>

<p>If you have only used web servers in the past for simple, single server configurations, you may be wondering why you would need to proxy requests.</p>

<p>One reason to proxy to other servers from Nginx is the ability to scale out your infrastructure.  Nginx is built to handle many concurrent connections at the same time.  This makes it ideal for being the point-of-contact for clients.  The server can pass requests to any number of backend servers to handle the bulk of the work, which spreads the load across your infrastructure.  This design also provides you with flexibility in easily adding backend servers or taking them down as needed for maintenance.</p>

<p>Another instance where an http proxy might be useful is when using an application servers that might not be built to handle requests directly from clients in production environments.  Many frameworks include web servers, but most of them are not as robust as servers designed for high performance like Nginx.  Putting Nginx in front of these servers can lead to a better experience for users and increased security.</p>

<p>Proxying in Nginx is accomplished by manipulating a request aimed at the Nginx server and passing it to other servers for the actual processing.  The result of the request is passed back to Nginx, which then relays the information to the client.  The other servers in this instance can be remote machines, local servers, or even other virtual servers defined within Nginx.  The servers that Nginx proxies requests to are known as <em>upstream servers</em>.</p>

<p>Nginx can proxy requests to servers that communicate using the http(s), FastCGI, SCGI, and uwsgi, or memcached protocols through separate sets of directives for each type of proxy.  In this guide, we will be focusing on the http protocol.  The Nginx instance is responsible for passing on the request and massaging any message components into a format that the upstream server can understand.</p>

<h2 id="deconstructing-a-basic-http-proxy-pass">Deconstructing a Basic HTTP Proxy Pass</h2>

<p>The most straight-forward type of proxy involves handing off a request to a single server that can communicate using http.  This type of proxy is known as a generic "proxy pass" and is handled by the aptly named <code>proxy_pass</code> directive.</p>

<p>The <code>proxy_pass</code> directive is mainly found in location contexts.  It is also valid in <code>if</code> blocks within a location context and in <code>limit_except</code> contexts.  When a request matches a location with a <code>proxy_pass</code> directive inside, the request is forwarded to the URL given by the directive.</p>

<p>Let's take a look at an example:</p>
<pre class="code-pre "><code class="code-highlight language-nginx"># server context

location /match/here {
    proxy_pass http://example.com;
}

. . .

</code></pre>
<p>In the above configuration snippet, no URI is given at the end of the server in the <code>proxy_pass</code> definition.  For definitions that fit this pattern, the URI requested by the client will be passed to the upstream server as-is.</p>

<p>For example, when a request for <code>/match/here/please</code> is handled by this block, the request URI will be sent to the <code>example.com</code> server as <code>http://example.com/match/here/please</code>.</p>

<p>Let's take a look at the alternative scenario:</p>
<pre class="code-pre "><code class="code-highlight language-nginx"># server context

location /match/here {
    proxy_pass http://example.com/new/prefix;
}

. . .
</code></pre>
<p>In the above example, the proxy server is defined with a URI segment on the end (<code>/new/prefix</code>).  When a URI is given in the <code>proxy_pass</code> definition, the portion of the request that matches the <em>location</em> definition is replaced by this URI during the pass.</p>

<p>For example, a request for <code>/match/here/please</code> on the Nginx server will be passed to the upstream server as <code>http://example.com/new/prefix/please</code>.  The <code>/match/here</code> is replaced by <code>/new/prefix</code>.  This is an important point to keep in mind.</p>

<p>Sometimes, this kind of replacement is impossible.  In these cases, the URI at the end of the <code>proxy_pass</code> definition is ignored and either the original URI from the client or the URI as modified by other directives will be passed to the upstream server.</p>

<p>For instance, when the location is matched using regular expressions, Nginx cannot determine which part of the URI matched the expression, so it sends the original client request URI.  Another example is when a rewrite directive is used within the same location, causing the client URI to be rewritten, but still handled in the same block.  In this case, the rewritten URI will be passed.</p>

<h2 id="understanding-how-nginx-processes-headers">Understanding How Nginx Processes Headers</h2>

<p>One thing that might not be immediately clear is that it is important to pass more than just the URI if you expect the upstream server handle the request properly.  The request coming from Nginx on behalf of a client will look different than a request coming directly from a client.  A big part of this is the headers that go along with the request.</p>

<p>When Nginx proxies a request, it automatically makes some adjustments to the request headers it receives from the client:</p>

<ul>
<li>Nginx gets rid of any empty headers.  There is no point of passing along empty values to another server; it would only serve to bloat the request.</li>
<li>Nginx, by default, will consider any header that contains underscores as invalid.  It will remove these from the proxied request.  If you wish to have Nginx interpret these as valid, you can set the <code>underscores_in_headers</code> directive to "on", otherwise your headers will never make it to the backend server.</li>
<li>The "Host" header is re-written to the value defined by the <code>$proxy_host</code> variable.  This will be the IP address or name and port number of the upstream, directly as defined by the <code>proxy_pass</code> directive.</li>
<li>The "Connection" header is changed to "close".  This header is used to signal information about the particular connection established between two parties.  In this instance, Nginx sets this to "close" to indicate to the upstream server that this connection will be closed once the original request is responded to.  The upstream should not expect this connection to be persistent.</li>
</ul>

<p>The first point that we can extrapolate from the above is that any header that you <em>do not</em> want passed should be set to an empty string.  Headers with empty values are completely removed from the passed request.</p>

<p>The next point to glean from the above information is that if your backend application will be processing non-standard headers, you must make sure that they <em>do not</em> have underscores.  If you need headers that use an underscore, you can set the <code>underscores_in_headers</code> directive to "on" further up in your configuration (valid either in the http context or in the context of the default server declaration for the IP address/port combination).  If you do not do this, Nginx will flag these headers as invalid and silently drop them before passing to your upstream.</p>

<p>The "Host" header is of particular importance in most proxying scenarios.  As stated above, by default, this will be set to the value of <code>$proxy_host</code>, a variable that will contain the domain name or IP address and port taken directly from the <code>proxy_pass</code> definition.  This is selected by default as it is the only address Nginx can be sure the upstream server responds to (as it is pulled directly from the connection info).</p>

<p>The most common values for the "Host" header are below:</p>

<ul>
<li><code>$proxy_host</code>: This sets the "Host" header to the domain name or IP address and port combo taken from the <code>proxy_pass</code> definition.  This is the default and "safe" from Nginx's perspective, but not usually what is needed by the proxied server to correctly handle the request.</li>
<li><code>$http_host</code>: Sets the "Host" header to the "Host" header from the client request.  The headers sent by the client are always available in Nginx as variables.  The variables will start with an <code>$http_</code> prefix, followed by the header name in lowercase, with any dashes replaced by underscores.  Although the <code>$http_host</code> variable works most of the time, when the client request does not have a valid "Host" header, this can cause the pass to fail.</li>
<li><code>$host</code>: This variable is set, in order of preference to: the host name from the request line itself, the "Host" header from the client request, or the server name matching the request.</li>
</ul>

<p>In most cases, you will want to set the "Host" header to the <code>$host</code> variable.  It is the most flexible and will usually provide the proxied servers with a "Host" header filled in as accurately as possible.</p>

<h2 id="setting-or-resetting-headers">Setting or Resetting Headers</h2>

<p>To adjust or set headers for proxy connections, we can use the <code>proxy_set_header</code> directive.  For instance, to change the "Host" header as we have discussed, and add some additional headers common with proxied requests, we could use something like this:</p>
<pre class="code-pre "><code class="code-highlight language-nginx"># server context

location /match/here {
    proxy_set_header HOST $host;
    proxy_set_header X-Forwarded-Proto $scheme;
    proxy_set_header X-Real-IP $remote_addr;
    proxy_set_header X-Forwarded-For $proxy_add_x_forwarded_for;

    proxy_pass http://example.com/new/prefix;
}

. . .
</code></pre>
<p>The above request sets the "Host" header to the <code>$host</code> variable, which should contain information about the original host being requested.  The <code>X-Forwarded-Proto</code> header gives the proxied server information about the schema of the original client request (whether it was an http or an https request).</p>

<p>The <code>X-Real-IP</code> is set to the IP address of the client so that the proxy can correctly make decisions or log based on this information.  The <code>X-Forwarded-For</code> header is a list containing the IP addresses of every server the client has been proxied through up to this point.  In the example above, we set this to the <code>$proxy_add_x_forwarded_for</code> variable.  This variable takes the value of the original <code>X-Forwarded-For</code> header retrieved from the client and adds the Nginx server's IP address to the end.</p>

<p>Of course, we could move the <code>proxy_set_header</code> directives out to the server or http context, allowing it to be referenced in more than one location:</p>
<pre class="code-pre "><code class="code-highlight language-nginx"># server context

proxy_set_header HOST $host;
proxy_set_header X-Forwarded-Proto $scheme;
proxy_set_Header X-Real-IP $remote_addr;
proxy_set_header X-Forwarded-For $proxy_add_x_forwarded_for;

location /match/here {
    proxy_pass http://example.com/new/prefix;
}

location /different/match {
    proxy_pass http://example.com;
}
</code></pre>
<h2 id="defining-an-upstream-context-for-load-balancing-proxied-connections">Defining an Upstream Context for Load Balancing Proxied Connections</h2>

<p>In the previous examples, we demonstrated how to do a simple http proxy to a single backend server.  Nginx allows us to easily scale this configuration out by specifying entire pools of backend servers that we can pass requests to.</p>

<p>We can do this by using the <code>upstream</code> directive to define a pool of servers.  This configuration assumes that any one of the listed servers is capable of handling a client's request.  This allows us to scale out our infrastructure with almost no effort.  The <code>upstream</code> directive must be set in the http context of your Nginx configuration.</p>

<p>Let's look at a simple example:</p>
<pre class="code-pre "><code class="code-highlight language-nginx"># http context

upstream <span class="highlight">backend_hosts</span> {
    server host1.example.com;
    server host2.example.com;
    server host3.example.com;
}

server {
    listen 80;
    server_name example.com;

    location /proxy-me {
        proxy_pass http://<span class="highlight">backend_hosts</span>;
    }
}
</code></pre>
<p>In the above example, we've set up an upstream context called <code>backend_hosts</code>.  Once defined, this name will be available for use within proxy passes as if it were a regular domain name.  As you can see, within our server block we pass any request made to <code>example.com/proxy-me/...</code> to the pool we defined above.  Within that pool, a host is selected by applying a configurable algorithm.  By default, this is just a simple round-robin selection process (each request will be routed to a different host in turn).</p>

<h3 id="changing-the-upstream-balancing-algorithm">Changing the Upstream Balancing Algorithm</h3>

<p>You can modify the balancing algorithm used by the upstream pool by including directives or flags within the upstream context:</p>

<ul>
<li><strong>(round robin)</strong>: The default load balancing algorithm that is used if no other balancing directives are present.  Each server defined in the upstream context is passed requests sequentially in turn.</li>
<li><strong><code>least_conn</code></strong>: Specifies that new connections should always be given to the backend that has the least number of active connections.  This can be especially useful in situations where connections to the backend may persist for some time.</li>
<li><strong><code>ip_hash</code></strong>: This balancing algorithm distributes requests to different servers based on the client's IP address.  The first three octets are used as a key to decide on the server to handle the request.  The result is that clients tend to be served by the same server each time, which can assist in session consistency.</li>
<li><strong><code>hash</code></strong>: This balancing algorithm is mainly used with memcached proxying.  The servers are divided based on the value of an arbitrarily provided hash key.  This can be text, variables, or a combination.  This is the only balancing method that requires the user to provide data, which is the key that should be used for the hash.</li>
</ul>

<p>When changing the balancing algorithm, the block may look something like this:</p>
<pre class="code-pre "><code class="code-highlight language-nginx"># http context

upstream <span class="highlight">backend_hosts</span> {

    least_conn;

    server host1.example.com;
    server host2.example.com;
    server host3.example.com;
}

. . .

</code></pre>
<p>In the above example, the server will be selected based on which one has the least connections.  The <code>ip_hash</code> directive could be set in the same way to get a certain amount of session "stickiness".</p>

<p>As for the <code>hash</code> method, you must provide the key to hash against.  This can be whatever you wish:</p>
<pre class="code-pre "><code class="code-highlight language-nginx"># http context

upstream <span class="highlight">backend_hosts</span> {

    hash $remote_addr$remote_port consistent;

    server host1.example.com;
    server host2.example.com;
    server host3.example.com;
}

. . .

</code></pre>
<p>The above example will distribute requests based on the value of the client ip address and port.  We also added the optional parameter <code>consistent</code>, which implements the ketama consistent hashing algorithm.  Basically, this means that if your upstream servers change, there will be minimal impact on your cache.</p>

<h3 id="setting-server-weight-for-balancing">Setting Server Weight for Balancing</h3>

<p>In declarations of the backend servers, by default, each servers is equally "weighted".  This assumes that each server can and should handle the same amount of load (taking into account the effects of the balancing algorithms).  However, you can also set an alternative weight to servers during the declaration:</p>
<pre class="code-pre "><code class="code-highlight language-nginx"># http context

upstream <span class="highlight">backend_hosts</span> {
    server host1.example.com weight=3;
    server host2.example.com;
    server host3.example.com;
}

. . .

</code></pre>
<p>In the above example, <code>host1.example.com</code> will receive three times the traffic as the other two servers.  By default, each server is assigned a weight of one.</p>

<h2 id="using-buffers-to-free-up-backend-servers">Using Buffers to Free Up Backend Servers</h2>

<p>One issue with proxying that concerns many users is the performance impact of adding an additional server to the process.  In most cases, this can be largely mitigated by taking advantage of Nginx's buffering and caching capabilities.</p>

<p>When proxying to another server, the speed of two different connections will affect the client's experience:</p>

<ul>
<li>The connection from the client to the Nginx proxy.</li>
<li>The connection from the Nginx proxy to the backend server.</li>
</ul>

<p>Nginx has the ability to adjust its behavior based on whichever one of these connections you wish to optimize. </p>

<p>Without buffers, data is sent from the proxied server and immediately begins to be transmitted to the client.  If the clients are assumed to be fast, buffering can be turned off in order to get the data to the client as soon as possible.  With buffers, the Nginx proxy will temporarily store the backend's response and then feed this data to the client.  If the client is slow, this allows the Nginx server to close the connection to the backend sooner.  It can then handle distributing the data to the client at whatever pace is possible.</p>

<p>Nginx defaults to a buffering design since clients tend to have vastly different connection speeds.  We can adjust the buffering behavior with the following directives.  These can be set in the http, server, or location contexts.  It is important to keep in mind that the sizing directives are configured <em>per request</em>, so increasing them beyond your need can affect your performance when there are many client requests:</p>

<ul>
<li><strong><code>proxy_buffering</code></strong>: This directive controls whether buffering for this context and child contexts is enabled.  By default, this is "on".</li>
<li><strong><code>proxy_buffers</code></strong>: This directive controls the number (first argument) and size (second argument) of buffers for proxied responses.  The default is to configure 8 buffers of a size equal to one memory page (either <code>4k</code> or <code>8k</code>).  Increasing the number of buffers can allow you to buffer more information.</li>
<li><strong><code>proxy_buffer_size</code></strong>: The initial portion of the response from a backend server, which contains headers, is buffered separately from the rest of the response.  This directive sets the size of the buffer for this portion of the response.  By default, this will be the same size as <code>proxy_buffers</code>, but since this is used for header information, this can usually be set to a lower value.</li>
<li><strong><code>proxy_busy_buffers_size</code></strong>: This directive sets the maximum size of buffers that can be marked "client-ready" and thus busy.  While a client can only read the data from one buffer at a time, buffers are placed in a queue to send to the client in bunches.  This directive controls the size of the buffer space allowed to be in this state.</li>
<li><strong><code>proxy_max_temp_file_size</code></strong>: This is the maximum size, per request, for a temporary file on disk. These are created when the upstream response is too large to fit into a buffer.</li>
<li><strong><code>proxy_temp_file_write_size</code></strong>: This is the amount of data Nginx will write to the temporary file at one time when the proxied server's response is too large for the configured buffers.</li>
<li><strong><code>proxy_temp_path</code></strong>: This is the path to the area on disk where Nginx should store any temporary files when the response from the upstream server cannot fit into the configured buffers.</li>
</ul>

<p>As you can see, Nginx provides quite a few different directives to tweak the buffering behavior.  Most of the time, you will not have to worry about the majority of these, but it can be useful to adjust some of these values.  Probably the most useful to adjust are the <code>proxy_buffers</code> and <code>proxy_buffer_size</code> directives.</p>

<p>An example that increases the number of available proxy buffers for each upstream request, while trimming down the buffer that likely stores the headers would look like this:</p>
<pre class="code-pre "><code class="code-highlight language-nginx"># server context

proxy_buffering on;
proxy_buffer_size 1k;
proxy_buffers 24 4k;
proxy_busy_buffers_size 8k;
proxy_max_temp_file_size 2048m;
proxy_temp_file_write_size 32k;

location / {
    proxy_pass http://example.com;
}
</code></pre>
<p>In contrast, if you have fast clients that you want to immediately serve data to, you can turn buffering off completely.  Nginx will actually still use buffers if the upstream is faster than the client, but it will immediately try to flush data to the client instead of waiting for the buffer to pool.  If the client is slow, this can cause the upstream connection to remain open until the client can catch up.  When buffering is "off" only the buffer defined by the <code>proxy_buffer_size</code> directive will be used:</p>
<pre class="code-pre "><code class="code-highlight language-nginx"># server context

proxy_buffering off;
proxy_buffer_size 4k;

location / {
    proxy_pass http://example.com;
}
</code></pre>
<h3 id="high-availability-optional">High Availability (Optional)</h3>

<p>Nginx proxying can be made more robust by adding in a redundant set of load balancers, creating a high availability infrastructure. </p>

<p>A <em>high availability</em> (HA) setup is an infrastructure without a single point of failure, and your load balancers are a part of this configuration. By having more than one load balancer, you prevent potential downtime if your load balancer is unavailable or if you need to take them down for maintenance.</p>

<p>Here is a diagram of a basic high availability setup:</p>

<p><img src="https://assets.digitalocean.com/articles/high_availability/ha-diagram-animated.gif" alt="HA Setup" /></p>

<p>In this example, you have multiple load balancers (one active and one or more passive) behind a static IP address that can be remapped from one server to another. Client requests are routed from the static IP to the active load balancer, then on to your backend servers. To learn more, read <a href="https://indiareads/community/tutorials/how-to-use-floating-ips-on-digitalocean#how-to-implement-an-ha-setup">this section of How To Use Floating IPs</a>.</p>

<h2 id="configuring-proxy-caching-to-decrease-response-times">Configuring Proxy Caching to Decrease Response Times</h2>

<p>While buffering can help free up the backend server to handle more requests, Nginx also provides a way to cache content from backend servers, eliminating the need to connect to the upstream at all for many requests.</p>

<h3 id="configuring-a-proxy-cache">Configuring a Proxy Cache</h3>

<p>To set up a cache to use for proxied content, we can use the <code>proxy_cache_path</code> directive.  This will create an area where data returned from the proxied servers can be kept.  The <code>proxy_cache_path</code> directive must be set in the http context.</p>

<p>In the example below, we will configure this and some related directives to set up our caching system.</p>
<pre class="code-pre "><code class="code-highlight language-nginx"># http context

proxy_cache_path /var/lib/nginx/cache levels=1:2 keys_zone=backcache:8m max_size=50m;
proxy_cache_key "$scheme$request_method$host$request_uri$is_args$args";
proxy_cache_valid 200 302 10m;
proxy_cache_valid 404 1m;
</code></pre>
<p>With the <code>proxy_cache_path</code> directive, we have have defined a directory on the filesystem where we would like to store our cache.  In this example, we've chosen the <code>/var/lib/nginx/cache</code> directory.  If this directory does not exist, you can create it with the correct permission and ownership by typing:</p>
<pre class="code-pre "><code langs="">sudo mkdir -p /var/lib/nginx/cache
sudo chown www-data /var/lib/nginx/cache
sudo chmod 700 /var/lib/nginx/cache
</code></pre>
<p>The <code>levels=</code> parameter specifies how the cache will be organized.  Nginx will create a cache key by hashing the value of a key (configured below).  The levels we selected above dictate that a single character directory (this will be the last character of the hashed value) with a two character subdirectory (taken from the next two characters from the end of the hashed value) will be created.  You usually won't have to be concerned with the specifics of this, but it helps Nginx quickly find the relevant values.</p>

<p>The <code>keys_zone=</code> parameter defines the name for this cache zone, which we have called <code>backcache</code>.  This is also where we define how much metadata to store.  In this case, we are storing 8 MB of keys.  For each megabyte, Nginx can store around 8000 entries.  The <code>max_size</code> parameter sets the maximum size of the actual cached data.</p>

<p>Another directive we use above is <code>proxy_cache_key</code>.  This is used to set the key that will be used to store cached values.  This same key is used to check whether a request can be served from the cache.  We are setting this to a combination of the scheme (http or https), the HTTP request method, as well as the requested host and URI.</p>

<p>The <code>proxy_cache_valid</code> directive can be specified multiple times.  It allows us to configure how long to store values depending on the status code.  In our example, we store successes and redirects for 10 minutes, and expire the cache for 404 responses every minute.</p>

<p>Now, we have configured the cache zone, but we still need to tell Nginx when to use the cache.</p>

<p>In locations where we proxy to a backend, we can configure the use of this cache:</p>
<pre class="code-pre "><code class="code-highlight language-nginx"># server context

location /proxy-me {
    proxy_cache backcache;
    proxy_cache_bypass $http_cache_control;
    add_header X-Proxy-Cache $upstream_cache_status;

    proxy_pass http://backend;
}

. . .

</code></pre>
<p>Using the <code>proxy_cache</code> directive, we can specify that the <code>backcache</code> cache zone should be used for this context.  Nginx will check here for a valid entry before passing to the backend.</p>

<p>The <code>proxy_cache_bypass</code> directive is set to the <code>$http_cache_control</code> variable.  This will contain an indicator as to whether the client is explicitly requesting a fresh, non-cached version of the resource.  Setting this directive allows Nginx to correctly handle these types of client requests.  No further configuration is required.</p>

<p>We also added an extra header called <code>X-Proxy-Cache</code>.  We set this header to the value of the <code>$upstream_cache_status</code> variable.  Basically, this sets a header that allows us to see if the request resulted in a cache hit, a cache miss, or if the cache was explicitly bypassed.  This is especially valuable for debugging, but is also useful information for the client.</p>

<h3 id="notes-about-caching-results">Notes about Caching Results</h3>

<p>Caching can improve the performance of your proxy enormously.  However, there are definitely considerations to keep in mind when configuring cache.</p>

<p>First, any user-related data should <em>not</em> be cached.  This could result in one user's data being presented to another user.  If your site is completely static, this is probably not an issue. </p>

<p>If your site has some dynamic elements, you will have to account for this in the backend servers.  How you handle this depends on what application or server is handling the backend processing.  For private content, you should set the <code>Cache-Control</code> header to "no-cache", "no-store", or "private" depending on the nature of the data:</p>

<ul>
<li><strong>no-cache</strong>: Indicates that the response shouldn't be served again without first checking that the data hasn't changed on the backend.  This can be used if the data is dynamic and important.  An ETag hashed metadata header is checked on each request and the previous value can be served if the backend returns the same hash value.</li>
<li><strong>no-store</strong>: Indicates that at no point should the data received ever be cached.  This is the safest option for private data, as it means that the data must be retrieved from the server every time.</li>
<li><strong>private</strong>: This indicates that no shared cache space should cache this data.  This can be useful for indicating that a user's browser can cache the data, but the proxy server shouldn't consider this data valid for subsequent requests.</li>
<li><strong>public</strong>: This indicates that the response is public data that can be cached at any point in the connection.</li>
</ul>

<p>A related header that can control this behavior is the <code>max-age</code> header, which indicates the number of seconds that any resource should be cached.</p>

<p>Setting these headers correctly, depending on the sensitivity of the content, will help you take advantage of cache while keeping your private data safe and your dynamic data fresh.</p>

<p>If your backend also uses Nginx, you can set some of this using the <code>expires</code> directive, which will set the <code>max-age</code> for <code>Cache-Control</code>:</p>
<pre class="code-pre "><code class="code-highlight language-nginx">location / {
    expires 60m;
}

location /check-me {
    expires -1;
}
</code></pre>
<p>In the above example, the first block allows content to be cached for an hour.  The second block sets the <code>Cache-Control</code> header to "no-cache".  To set other values, you can use the <code>add_header</code> directive, like this:</p>
<pre class="code-pre "><code class="code-highlight language-nginx">location /private {
    expires -1;
    add_header Cache-Control "no-store";
}
</code></pre>
<h2 id="conclusion">Conclusion</h2>

<p>Nginx is first and foremost a reverse proxy, which also happens to have the ability to work as a web server.  Because of this design decision, proxying requests to other servers is fairly straight forward.  Nginx is very flexible though, allowing for more complex control over your proxying configuration if desired.</p>

    