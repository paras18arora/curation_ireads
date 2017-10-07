<link rel='stylesheet' href='digital_ocean.css'><br> <br> 
      <h3 id="introduction">Introduction</h3>

<p><a href="http://trafficserver.apache.org/">Apache Traffic Server</a> is a highly scalable caching proxy server capable of handling large volumes of concurrent requests while maintaining a very low latency. Compared to other popular proxy servers, such as Varnish or Squid, it usually consumes less memory and responds faster. It is also designed to make the most of modern multi-core processors. Depending on your requirements, you can use it as a reverse proxy or as a forward proxy.</p>

<p>This tutorial will cover how to install Apache Traffic Server on Ubuntu 14.04 and configure it to behave as a caching reverse proxy.</p>

<h2 id="prerequisites">Prerequisites</h2>

<p>To follow this tutorial, you will need:</p>

<ul>
<li><p>One Ubuntu 14.04 Droplet</p></li>
<li><p>A <a href="https://indiareads/community/tutorials/initial-server-setup-with-ubuntu-14-04">non-root sudo user</a></p></li>
<li><p>At least 1 GB of <a href="https://indiareads/community/tutorials/how-to-add-swap-on-ubuntu-14-04">swap space</a></p></li>
</ul>

<h2 id="step-1-—-installing-traffic-server">Step 1 — Installing Traffic Server</h2>

<p>Because Traffic Server is available on Ubuntu 14.04's default repositories, you can install it using <code>apt-get</code>. Make sure you update your package index files before you do so.</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo apt-get update && sudo apt-get install trafficserver
</li></ul></code></pre>
<p>Traffic Server listens on port 8080 by default. You can use a browser to visit <code>http://<span class="highlight">your_server_ip</span>:8080/</code> now. However, you will see an error because you haven't configured it yet.</p>

<h2 id="step-2-—-installing-a-web-server">Step 2 — Installing a Web Server</h2>

<p>By definition, a proxy server acts as an intermediary between external users and a web server. Therefore, before you begin configuring Traffic Server, you should install a web server such as Apache HTTP Server on your machine.</p>

<p>Install and start Apache using <code>apt-get</code>.</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo apt-get install apache2
</li></ul></code></pre>
<p>You can now use a browser and visit <code>http://<span class="highlight">your_server_ip</span>/</code> to see Apache's welcome page.</p>

<h2 id="step-3-—-disabling-remote-access-to-the-web-server">Step 3 — Disabling Remote Access to the Web Server</h2>

<p>Apache accepts connections on all network interfaces by default. By configuring it to accept connections only on the loopback interface, you can make sure that it is inaccessible to remote users.</p>

<p>Open <code>ports.conf</code> using <code>nano</code> or your favorite text editor.</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo nano /etc/apache2/ports.conf
</li></ul></code></pre>
<p>Search for the line containing the <code>Listen 80</code> directive and change it to:</p>
<div class="code-label " title="ports.conf">ports.conf</div><pre class="code-pre "><code langs="">Listen  <span class="highlight">127.0.0.1:80</span>
</code></pre>
<p>Save and exit the file.</p>

<p>Next, open <code>apache2.conf</code>.</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo nano /etc/apache2/apache2.conf
</li></ul></code></pre>
<p>Add the following line at the end of the file:</p>
<div class="code-label " title="apache2.conf">apache2.conf</div><pre class="code-pre "><code langs="">ServerName localhost
</code></pre>
<p>Save and close the file.</p>

<p>To apply the configuration changes, restart Apache using the following command:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo service apache2 restart
</li></ul></code></pre>
<p>Try using a browser to visit <code>http://<span class="highlight">your_server_ip</span>/</code> again. Your browser should show an error now, because you blocked remote access to the server.</p>

<h2 id="step-4-—-configuring-traffic-server-as-a-reverse-proxy">Step 4 — Configuring Traffic Server as a Reverse Proxy</h2>

<p>In this step, we will configure Traffic Server as a reverse proxy. To do so, open <code>remap.config</code>, which is the file you should edit to define Traffic Server's mapping rules.</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo nano /etc/trafficserver/remap.config
</li></ul></code></pre>
<p>Let's create a simple rule that says all requests to the server's IP address on port 8080 are mapped to the web server's local address and port. You can do so by adding the following line to the end of the file:</p>
<div class="code-label " title="remap.config">remap.config</div><pre class="code-pre "><code langs="">map http://<span class="highlight">your_server_ip</span>:8080/ http://127.0.0.1:80/
</code></pre>
<p>Save the file and exit.</p>

<p>To activate the new mapping rule, use the <code>reread_config</code> command of <code>traffic_line</code>:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo traffic_line --reread_config
</li></ul></code></pre>
<p>Open a browser and visit <code>http://<span class="highlight">your_server_ip</span>:8080/</code>. If you are able to see Apache's welcome page now, you have successfully configured Traffic Server as a reverse proxy.</p>

<h2 id="step-5-—-configuring-traffic-server-to-cache-everything">Step 5 — Configuring Traffic Server to Cache Everything</h2>

<p>By default, Traffic Server will cache an HTTP response only if it contains a <code>Cache-Control</code> or <code>Expires</code> header explicitly specifying how long the item should be stored in the cache. However, as our web server is only serving static files, it is safe to cache all its responses.</p>

<p>To configure Traffic Server such that it caches all HTTP responses, you should change the value of a config variable called <code>proxy.config.http.cache.required_headers</code> to <strong>0</strong>. This can be done using the <code>set_var</code> command of <code>traffic_line</code>.</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo traffic_line --set_var proxy.config.http.cache.required_headers --value 0
</li></ul></code></pre>
<p>Apply the change using the <code>reread_config</code> flag.</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo traffic_line --reread_config
</li></ul></code></pre>
<p>Open a browser and visit <code>http://<span class="highlight">your_server_ip</span>:8080/</code> again. This will store the Apache welcome page in Traffic Server's cache.</p>

<h2 id="step-6-—-inspecting-the-cache">Step 6 — Inspecting the Cache</h2>

<p>To view the contents of Traffic Server's cache, you can use a tool called Cache Inspector, which has a web-based interface.</p>

<p>To activate the tool, set the value of the <code>proxy.config.http_ui_enabled</code> config variable to <strong>1</strong>.</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo traffic_line --set_var proxy.config.http_ui_enabled --value 1
</li></ul></code></pre>
<p>Next, create a mapping rule specifying the path you want to use to access it. Open <code>remap.config</code> again using <code>nano</code>.</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo nano /etc/trafficserver/remap.config
</li></ul></code></pre>
<p>Let's make the Cache Inspector available on <code>/inspect</code>. To do so, add the following line at the <strong>top</strong> of the file:</p>
<div class="code-label " title="remap.config">remap.config</div><pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">map http://<span class="highlight">your_server_ip</span>:8080/inspect http://{cache}
</li></ul></code></pre>
<p>Save the file and exit.</p>

<p>To apply the changes, restart Traffic Server.</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo service trafficserver restart
</li></ul></code></pre>
<p>The Cache Inspector is now ready to be used. Open a browser, and visit <code>http://<span class="highlight">your_server_ip</span>:8080/inspect/</code>. You will see a page which looks like this:</p>

<p><img src="https://assets.digitalocean.com/articles/traffic_server/ajEXtig.png" alt="Cache Inspector's home page" /></p>

<p>Next, click on the <strong>Lookup url</strong> link.</p>

<p>You can now type in a URL in the text field and click on the <strong>Lookup</strong> button to check if it is stored in the cache.</p>

<p>For example, you can type in <code>http://<span class="highlight">your_server_ip</span>:8080/</code> to check if your web server's homepage is being served from the cache. If it is, you will see a page which looks like this:</p>

<p><img src="https://assets.digitalocean.com/articles/traffic_server/VFzMcc3.png" alt="Cached document details" /></p>

<h2 id="conclusion">Conclusion</h2>

<p>You now know how to install Apache Traffic Server on Ubuntu 14.04 and configure it as a caching reverse-proxy. Though we used Apache as the web server in this tutorial, you can just as easily use any other web server. To learn more about Traffic Server, you can go through its <a href="https://docs.trafficserver.apache.org/en/latest/admin/index.en.html">Administrator's Guide</a>.</p>

    