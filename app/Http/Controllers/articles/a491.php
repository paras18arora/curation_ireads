<link rel='stylesheet' href='digital_ocean.css'><br><img src=https://community-cdn-digitalocean-com.global.ssl.fastly.net/assets/tutorials/images/large/GZip_tutorial.png?1460397268/> <br> 
      <h3 id="introduction">Introduction</h3>

<p>How fast a website will load depends on the size of all of the files that have to be downloaded by the browser. Reducing the size of files to be transmitted can make the website not only load faster, but also cheaper to those who have to pay for their bandwidth usage.</p>

<p><a href="http://www.gzip.org/"><code>gzip</code></a> is a popular data compression program. You can configure Nginx to use <code>gzip</code> to compress files it serves on the fly. Those files are then decompressed by the browsers that support it upon retrieval with no loss whatsoever, but with the benefit of smaller amount of data being transferred between the web server and browser.</p>

<p>Because of the way compression works in general, but also how <code>gzip</code> works, certain files compress better than others. For example, text files compress very well, often ending up over two times smaller in result. On the other hand, images such as JPEG or PNG files are already compressed by their nature and second compression using <code>gzip</code> yields little or no results. Compressing files use up server resources, so it is best to compress only those files that will reduce its size considerably in result.</p>

<p>In this guide, we'll discuss how to configure Nginx installed on your CentOS 7 server to utilize <code>gzip</code> compression to reduce the size of content sent to website visitors.</p>

<h2 id="prerequisites">Prerequisites</h2>

<p>To follow this tutorial, you will need:</p>

<ul>
<li><p>One CentOS 7 server with a <a href="https://indiareads/community/tutorials/initial-server-setup-with-centos-7">sudo non-root user</a></p></li>
<li><p>Nginx installed on your server by following the <a href="https://indiareads/community/tutorials/how-to-install-nginx-on-centos-7">How To Install Nginx on CentOS 7 tutorial</a></p></li>
</ul>

<h2 id="step-1-—-creating-test-files">Step 1 — Creating Test Files</h2>

<p>In this step, we will create several test files in the default Nginx directory to text <code>gzip</code>'s compression.</p>

<p>To make a decision what kind of file is served over the network, Nginx does not analyze the file contents because it wouldn't be fast enough. Instead, it just looks up the file extension to determine its <em>MIME type</em>, which denotes the purpose of the file.</p>

<p>Because of this behavior, the contents of the test files is irrelevant. By naming the files appropriately, we can trick Nginx into thinking that one entirely empty file is an image and the another, for example, is a stylesheet.</p>

<p>In our configuration, Nginx will not compress very small files, so we're are going to create test files that are exactly 1 kilobyte in size. This will allow us to verify whether Nginx uses compression where it should, compressing one type of files and not doing so with the others.</p>

<p>Create a 1 kilobyte file named <code>test.html</code> in the default Nginx directory using <code>truncate</code>. The extension denotes that it's an HTML page.</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo truncate -s 1k /usr/share/nginx/html/test.html
</li></ul></code></pre>
<p>Let's create a few more test files in the same manner: one <code>jpg</code> image file, one <code>css</code> stylesheet, and one <code>js</code> JavaScript file.</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo truncate -s 1k /usr/share/nginx/html/test.jpg
</li><li class="line" prefix="$">sudo truncate -s 1k /usr/share/nginx/html/test.css
</li><li class="line" prefix="$">sudo truncate -s 1k /usr/share/nginx/html/test.js
</li></ul></code></pre>
<h2 id="step-2-—-checking-the-default-behavior">Step 2 — Checking the Default Behavior</h2>

<p>The next step is to check how Nginx behaves in respect to compression on a fresh installation with the files we have just created.</p>

<p>Let's check if HTML file named <code>test.html</code> is served with compression. The command requests a file from our Nginx server, and specifies that it is fine to serve <code>gzip</code> compressed content by using an HTTP header (<code>Accept-Encoding: gzip</code>).</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">curl -H "Accept-Encoding: gzip" -I http://localhost/test.html
</li></ul></code></pre>
<p>In response, you should see several HTTP response headers:</p>
<div class="code-label " title="Nginx response headers">Nginx response headers</div><pre class="code-pre "><code langs="">HTTP/1.1 200 OK
Server: nginx/1.6.3
Date: Fri, 11 Mar 2016 12:53:06 GMT
Content-Type: text/html
Content-Length: 1024
Last-Modified: Fri, 11 Mar 2016 12:48:02 GMT
Connection: keep-alive
ETag: "56e2be82-400"
Accept-Ranges: bytes
</code></pre>
<p>In the response, there is no mention of <code>gzip</code> whatsoever. This tells us that <code>gzip</code> compression is not enabled on the server. That's beause on CentOS 7 the support for <code>gzip</code> is entirely disabled in default Nginx configuration. If the compression was enabled, we would see additional header in the output saying <code>Content-Encoding: gzip</code>.</p>

<p>Not only HTML pages, but also every other file on a fresh installation will be served uncompressed. To verify that, you can request our test image named <code>test.jpg</code> in the same way.</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">curl -H "Accept-Encoding: gzip" -I http://localhost/test.jpg
</li></ul></code></pre>
<p>The result should be virtually identical as before:</p>
<div class="code-label " title="Nginx response headers">Nginx response headers</div><pre class="code-pre "><code langs="">HTTP/1.1 200 OK
Server: nginx/1.6.3
Date: Fri, 11 Mar 2016 12:58:03 GMT
Content-Type: image/jpeg
Content-Length: 1024
Last-Modified: Fri, 11 Mar 2016 12:48:05 GMT
Connection: keep-alive
ETag: "56e2be85-400"
Accept-Ranges: bytes
</code></pre>
<p>There is no <code>Content-Encoding: gzip</code> header in the output either, which means the file was served without compression.</p>

<p>You can repeat the test with test CSS stylesheet.</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">curl -H "Accept-Encoding: gzip" -I http://localhost/test.css
</li></ul></code></pre>
<p>Once again, there is no mention of compression in the output.</p>
<div class="code-label " title="Nginx response headers for CSS file">Nginx response headers for CSS file</div><pre class="code-pre "><code langs="">HTTP/1.1 200 OK
Server: nginx/1.6.3
Date: Fri, 11 Mar 2016 12:59:04 GMT
Content-Type: text/css
Content-Length: 1024
Last-Modified: Fri, 11 Mar 2016 12:48:05 GMT
Connection: keep-alive
ETag: "56e2be85-400"
Accept-Ranges: bytes
</code></pre>
<h2 id="step-3-—-enabling-and-configuring-nginx-39-s-gzip-module">Step 3 — Enabling and Configuring Nginx's gzip Module</h2>

<p>The next step is to configure Nginx to to enable compression for all file formats that can benefit from compression.</p>

<p>The <code>gzip</code> module is a core module in Nginx, which means it is already installed but must be enabled and configured.  In a fresh Nginx installation on CentOS 7, all files with the <code>.conf</code> extension from from the <code>/etc/nginx/conf.d</code> directory are automatically loaded. This allows for easy configuration of additional modules.</p>

<p>To enable the Nginx <code>gzip</code> module, create the configuration file named <code>gzip.conf</code> using <code>nano</code> or your favorite text editor.</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo nano /etc/nginx/conf.d/gzip.conf
</li></ul></code></pre>
<p>Paste in the following contents.</p>
<div class="code-label " title="/etc/nginx/nginx.conf">/etc/nginx/nginx.conf</div><pre class="code-pre "><code langs="">##
# `gzip` Settings
#
#
gzip on;
gzip_disable "msie6";

gzip_vary on;
gzip_proxied any;
gzip_comp_level 6;
gzip_http_version 1.1;
gzip_min_length 256;
gzip_types text/plain text/css application/json application/x-javascript text/xml application/xml application/xml+rss text/javascript application/javascript application/vnd.ms-fontobject application/x-font-ttf font/opentype image/svg+xml image/x-icon;
</code></pre>
<p>Save and close the file to exit.</p>

<p>Let's walk through the configuration settings applied here:</p>

<ul>
<li><p><code>gzip on</code> directive enables the Gzip compression.</p></li>
<li><p><code>gzip_disable "msie6"</code> excludes Internet Explorer 6 from the browsers that will receive compressed files, because IE6 does not support <code>gzip</code> at all.</p></li>
<li><p><code>gzip_vary</code> and <code>gzip_proxied</code> settings make sure that proxy servers between the browser and the server will recognize compression correctly.</p></li>
<li><p><code>gzip_comp_level 6</code> sets how much files will be compressed. The higher the number, the higher the compression level and the resources usage. 6 is a reasonable middle ground.</p></li>
<li><p><code>gzip_http_version 1.1</code> is used to limit <code>gzip</code> compression to browsers supporting the HTTP/1.1 protocol. If the browser does not support it, there is a good chance it does not support <code>gzip</code> either.</p></li>
<li><p><code>gzip_min_length 256</code> tells Nginx not to compress files smaller than 256 bytes. Very small files barely benefit from compression.</p></li>
<li><p><code>gzip_types</code> lists all of the MIME types that will be compressed. In this case, the list includes HTML pages, CSS stylesheets, Javascript and JSON files, XML files, icons, SVG images, and web fonts.</p></li>
</ul>

<p>To enable the new configuration, restart Nginx.</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo systemctl restart nginx
</li></ul></code></pre>
<h2 id="step-4-—-verifying-the-new-configuration">Step 4 — Verifying the New Configuration</h2>

<p>The next step is to check whether changes to the configuration have worked as expected.</p>

<p>We can test this just like we did in step 2, by using <code>curl</code> on each of the test files and examining the output for the <code>Content-Encoding: gzip</code> header.</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">curl -H "Accept-Encoding: gzip" -I http://localhost/test.html
</li></ul></code></pre>
<p>In response, you should see <code>Content-Encoding: gzip</code> header that was not there before:</p>
<div class="code-label " title="Nginx response headers">Nginx response headers</div><pre class="code-pre "><code langs="">HTTP/1.1 200 OK
Server: nginx/1.6.3
Date: Fri, 11 Mar 2016 13:19:16 GMT
Content-Type: text/html
Last-Modified: Fri, 11 Mar 2016 12:48:02 GMT
Connection: keep-alive
Vary: Accept-Encoding
<span class="highlight">Content-Encoding: gzip</span>
</code></pre>
<p>You can test all other files the same way:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">curl -H "Accept-Encoding: gzip" -I http://localhost/test.jpg
</li><li class="line" prefix="$">curl -H "Accept-Encoding: gzip" -I http://localhost/test.css
</li><li class="line" prefix="$">curl -H "Accept-Encoding: gzip" -I http://localhost/test.js
</li></ul></code></pre>
<p>Now only <code>test.jpg</code>, which is an image file, should stay uncompressed. In both other examples, you should be able to find <code>Content-Encoding: gzip</code> header in the output.</p>

<p>If that is the case, you have configured <code>gzip</code> compression in Nginx successfully!</p>

<h2 id="conclusion">Conclusion</h2>

<p>Changing Nginx configuration to fully use <code>gzip</code> compression is easy, but the benefits can be immense. Not only visitors with limited bandwidth will receive the site faster but also Google will be happy about the site loading faster. Speed is gaining traction as an important part of modern web and using <code>gzip</code> is one big step to improve it. </p>

    