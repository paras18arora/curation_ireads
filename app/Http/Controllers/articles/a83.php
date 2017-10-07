<link rel='stylesheet' href='digital_ocean.css'><br> <br> 
      <h3 id="introduction">Introduction</h3>

<p>In this tutorial, we will teach you how to optimize WordPress performance by using the WP Super Cache and Jetpack Photon plugins, and Nginx as the web server. With this setup, your WordPress site can greatly increase its concurrent user capacity by taking advantage of caching techniques that the aforementioned plugins provide.</p>

<p>WP Super Cache works by caching your WordPress pages as static HTML pages so that page requests, for an already cached page, do not need to be processed by the WordPress PHP scripts. Typically, most visitors of your site will view cached versions of the WordPress pages, so your server will have more processing power to serve an increased number of users. The WP Super Cache plugin is developed by Donncha O Caoimh.</p>

<p>Jetpack Photon is an image acceleration service that works by caching and serving your WordPress images via its own Content Delivery Network (CDN). Photon is one of the modules included in the Jetpack plugin, which is developed by the Jetpack Team of Automattic.</p>

<h2 id="prerequisites">Prerequisites</h2>

<p>To follow this tutorial, you will need a WordPress server that uses Nginx as its web server. If you do not have that, you may use these tutorials to create one:</p>

<ul>
<li><a href="https://indiareads/community/tutorials/how-to-install-linux-nginx-mysql-php-lemp-stack-on-ubuntu-14-04">How To Install Linux, nginx, MySQL, PHP (LEMP) stack on Ubuntu 14.04</a></li>
<li><a href="https://indiareads/community/tutorials/how-to-install-wordpress-with-nginx-on-ubuntu-14-04">How To Install WordPress with Nginx on Ubuntu 14.04</a></li>
</ul>

<h3 id="plugin-requirements-or-limitations">Plugin Requirements or Limitations</h3>

<p>WP Super Cache <strong>does not</strong> work with plugins that use query arguments because it does not work if you pass query arguments to Nginx. Also, because of this, you <strong>must not</strong> use the WordPress <em>Default Permalink</em> settings (which uses WordPress page numbers as arguments).</p>

<p>Jetpack Photon Limitations:</p>

<ul>
<li>You must connect your site to WordPress.com to enable Jetpack, which requires a free WordPress.com account</li>
<li>Your WordPress site must listen on port 80 (Photon will not work with HTTPS-only sites)</li>
<li>Once an gif, jpg, or png image is cached, it cannot be updated. The only workaround is to re-upload a renamed image to your site.</li>
<li>Images that take too long to copy to the Photon CDN (more than 10 seconds) must be renamed and re-uploaded</li>
</ul>

<p>If you do not want to use Photon, feel free to skip that section of the tutorial.</p>

<p>Now that we have the prerequisites out of the way, let's start installing WP Super Cache!</p>

<h2 id="install-and-configure-wp-super-cache-plugin">Install and Configure WP Super Cache Plugin</h2>

<p>The first step to installing the WP Super Cache Plugin is to download it from wordpress.org to your home directory:</p>
<pre class="code-pre "><code langs="">cd ~; wget http://downloads.wordpress.org/plugin/wp-super-cache.1.4.zip
</code></pre>
<p>If you do not have the unzip package installed, do it now:</p>
<pre class="code-pre "><code langs="">sudo apt-get install unzip
</code></pre>
<p>Then unzip the WP Super Cache plugin to your WordPress plugins directory (replace the highlighted path with your own, if you installed WordPress somewhere else):</p>
<pre class="code-pre "><code langs="">cd <span class="highlight">/var/www/html</span>/wp-content/plugins
unzip ~/wp-super-cache.1.4.zip
</code></pre>
<p>Next, we will change the group ownership of the plugin:</p>
<pre class="code-pre "><code langs="">sudo chgrp -R www-data wp-super-cache
</code></pre>
<p>And we will allow the plugin to write to the <code>wp-content</code> directory and the <code>wp-config.php</code> file:</p>
<pre class="code-pre "><code langs="">chmod g+w <span class="highlight">/var/www/html</span>/wp-content
chmod g+w <span class="highlight">/var/www/html</span>/wp-config.php
</code></pre>
<p>Now that the WordPress files are set up properly, let's activate the plugin.</p>

<h3 id="activate-wp-super-cache-plugin">Activate WP Super Cache Plugin</h3>

<p>Log in to your WordPress site as your administrator user, and go to Dashboard (http://example.com/wp-admin/). Activate the WP Super Cache plugin, then go into its settings window, by following these steps:</p>

<ol>
<li>Click on <em>Plugins</em> (left bar)</li>
<li>Click on <em>Activate</em> directly beneath <em>WP Super Cache</em></li>
<li>Click on WP Super Cache <em>Settings</em></li>
</ol>

<h3 id="enable-caching">Enable Caching</h3>

<p>Now we will enable caching and configure WP Super Cache with some reasonable settings:</p>

<ol>
<li>Click the Advanced tab</li>
<li>Check <em>Cache hits to this website for quick access.</em></li>
<li>Select <em>Use mod_rewrite to serve cache files.</em></li>
</ol>

<p>This configures WP Super Cache to cache files that are accessed, and the <em>mod_rewrite</em> setting leaves it up to Nginx to serve the cached files. We are not actually going to use <em>mod_rewrite</em> because it is an Apache plugin, and we are using Nginx as our web server, but we will need to update our Nginx server block configuration so that Nginx appropriately serves the cached files. We will get to that after we tweak a few more WP Super Cache settings (note: the following settings are optional):</p>

<ol>
<li>Check <em>Compress pages so they're served more quickly to visitors.</em></li>
<li>Check <em>Don't cache pages for known users.</em></li>
<li>Check <em>Cache rebuild.</em></li>
<li>Check <em>Extra homepage checks.</em></li>
</ol>

<p>Next, you need to save your settings by clicking the <em>Update Status</em> button, which should be below the settings you just changed:</p>

<p><img src="https://assets.digitalocean.com/articles/wordpress_supercache/update_status.png" alt="Update Status Button" /></p>

<p>WP Super Cache is now configured to cache your WordPress pages. We still need to configure Nginx to <em>serve</em> the cached files, but let's look at a few other things in the WP Super Cache settings window.</p>

<h3 id="warnings-about-mod-rewrite-and-garbage-collection">Warnings About Mod Rewrite and Garbage Collection</h3>

<p>At this point, you will see some warning banners at the top of the WP Super Cache configuration window. There will be two warnings about Mod Rewrite rules (here is the first one):</p>

<p><img src="https://assets.digitalocean.com/articles/wordpress_supercache/mod_rewrite_warning.png" alt="Mod Rewrite Warning" /></p>

<p>You may ignore this because we are going to use Nginx instead of Apache.</p>

<p>Next, you will see a warning about Garbage Collection settings:</p>

<p><img src="https://assets.digitalocean.com/articles/wordpress_supercache/garbage_collection.png" alt="Garbage Collection Warning" /></p>

<p>This warning can be removed by dismissing it (i.e. click the "Dismiss" button) or by configuring garbage collection. To configure garbage collection, go to the <em>Expiry Time & Garbage Collection</em> section in the Advanced tab, then configure it to your liking, then click the <em>Change Expiration</em> button.</p>

<h3 id="viewing-cache-contents">Viewing Cache Contents</h3>

<p>You can see the list of all of the cached pages by going to the <em>Contents</em> tab of the WP Super Cache settings. Here you will see the "Cache stats", which shows how many files are cached (and which files are cached). You may also delete the current cache from here.</p>

<p>WP Super Cache only caches pages visited by users who aren't logged in, haven't left a comment, or haven't viewed a password protected post. So if you are wondering why pages that you are visiting aren't being cached, try viewing your WordPress site in private browsing mode. Also, Nginx is not yet configured to serve cached files, so you will not see any improvements in access times.</p>

<h3 id="additional-wp-super-cache-configuration">Additional WP Super Cache Configuration</h3>

<p>In addition to the settings discussed above, there are many others that you might find to be useful or interesting. We will briefly go over the CDN and Preloading tabs.</p>

<p><strong>Using a CDN</strong> -- Skip if you are going to use Jetpack Photon</p>

<p>If you use a CDN, be sure to enable CDN support in the <em>CDN</em> tab. All the settings that you need to offload your static assets are located here.</p>

<p><strong>Preloading Cache</strong></p>

<p>In the <em>Preload</em> tab, you can configure WP Super Cache to automatically cache pages. This can be configured to preload your entire site or a fixed number of your recent posts on a time interval that you specify. Preloading pages takes system resources (CPU to retrieve pages, and disk space to store the static pages), so keep that in consideration when deciding if you want to enable it.</p>

<h2 id="configure-nginx-to-serve-cached-files">Configure Nginx To Serve Cached Files</h2>

<p>Now that your WordPress site is caching pages with WP Super Cache, you must configure Nginx to serve the cached files. Edit the Nginx server block configuration:</p>
<pre class="code-pre "><code langs="">sudo vi /etc/nginx/sites-enabled/wordpress
</code></pre>
<p>If you followed the prerequisite tutorials, place the following configuration lines directly beneath the <code>server_name</code> line:</p>
<pre class="code-pre "><code langs="">    set $cache_uri $request_uri;

    # POST requests and urls with a query string should always go to PHP
    if ($request_method = POST) {
        set $cache_uri 'null cache';
    }   
    if ($query_string != "") {
        set $cache_uri 'null cache';
    }   

    # Don't cache uris containing the following segments
    if ($request_uri ~* "(/wp-admin/|/xmlrpc.php|/wp-(app|cron|login|register|mail).php|wp-.*.php|/feed/|index.php|wp-comments-popup.php|wp-links-opml.php|wp-locations.php|sitemap(_index)?.xml|[a-z0-9_-]+-sitemap([0-9]+)?.xml)") {
        set $cache_uri 'null cache';
    }   

    # Don't use the cache for logged in users or recent commenters
    if ($http_cookie ~* "comment_author|wordpress_[a-f0-9]+|wp-postpass|wordpress_logged_in") {
        set $cache_uri 'null cache';
    }

    # Use cached or actual file if they exists, otherwise pass request to WordPress
    location / {
        try_files /wp-content/cache/supercache/$http_host/$cache_uri/index.html $uri $uri/ /index.php ;
    }    
</code></pre>
<p>Then delete the lines that follow until <code>location ~ \.php$ {</code>.</p>

<p>Restart Nginx to put the configuration changes into effect:</p>
<pre class="code-pre "><code langs="">sudo service nginx restart
</code></pre>
<p>Now your WordPress site's pages will be cached via WP Super Cache! If you want to also cache your images, using Jetpack Photon, continue on to the next section.</p>

<h2 id="install-and-enable-jetpack-photon">Install and Enable Jetpack Photon</h2>

<p>Download the Jetpack plugin to your home directory:</p>
<pre class="code-pre "><code langs="">cd ~; wget http://downloads.wordpress.org/plugin/jetpack.latest-stable.zip
</code></pre>
<p>Then unzip the Jetpack archive in your WordPress plugins directory:</p>
<pre class="code-pre "><code langs="">cd <span class="highlight">/var/www/html</span>/wp-content/plugins
unzip ~/jetpack.latest-stable.zip
sudo chgrp -R www-data jetpack
</code></pre>
<p>Jetpack comes with several modules other than Photon, many of which are enabled by default. If you want to use the other Jetpack modules, in addition to Jetpack, skip the following edit, and activate the Photon module through the Jetpack plugin settings on your WordPress administrator dashboard. Otherwise, we can disable the other modules by adding a few lines of code to the plugin's PHP files.</p>

<p>Open <code>wp-config.php</code> for editing:</p>
<pre class="code-pre "><code langs="">vi <span class="highlight">/var/www/html</span>/wp-config.php
</code></pre>
<p>Go to the end of the file and add the following lines of code:</p>
<pre class="code-pre "><code langs="">function change_default_modules() {
    return array( 'photon' );  // activate these modules by default
}
add_filter( 'jetpack_get_default_modules', 'change_default_modules' );

function activate_specific_jetpack_modules( $modules ) {
        $active_modules = array( 'photon' );  // enable these modules
        $modules = array_intersect_key( $modules, array_flip( $active_modules ) );  // deactivate other modules
        return $modules;
}
add_filter( 'jetpack_get_available_modules', 'activate_specific_jetpack_modules' );
</code></pre>
<p>Save and quit. Now when you activate the Jetpack plugin, it will only load the Photon module and disable the use of all of the other Jetpack modules.</p>

<h2 id="activate-jetpack-plugin">Activate Jetpack Plugin</h2>

<p>Now log in to your WordPress site as your administrator user, and go to Dashboard (http://example.com/wp-admin/). Activate the Jetpack plugin, then go into its settings, by following these steps:</p>

<ol>
<li>Click on <em>Plugins</em> (left bar)</li>
<li>Click on <em>Activate</em> directly beneath <em>Jetpack</em></li>
<li>Click <em>Connect to WordPress.com</em>, in the green banner near the top of Plugins window</li>
<li>Enter your WordPress.com login and click <em>Authorize Jetpack</em></li>
</ol>

<p><img src="https://assets.digitalocean.com/articles/wordpress_supercache/authorize_jetpack.png" alt="Authorize Jetpack" /></p>

<p>Now all of the images on your WordPress site (.png, .jpg, .gif) will be served from Jetpack's Photon CDN. Here are a few ways your server will be affected:</p>

<ul>
<li><strong>Less bandwidth consumption</strong>: Your server will use less outgoing bandwidth because the Photon CDN, which is provided by WordPress.com, will serve your site's images</li>
<li><strong>Less resource consumption</strong>: It will consume less CPU and memory because it no longer serves images to users, and mostly only static pages</li>
<li><strong>More user capacity</strong>: It will be able to handle more concurrent users because it is using less resources per request</li>
</ul>

<p>That's it! The Photon CDN will cache and serve your images as they are requested. Note that you can disable Photon in the Jetpack plugin settings at any time, if you decide that you do not want to use it.</p>

<h2 id="performance-comparison">Performance Comparison</h2>

<p>To show you an idea of the potential performance benefit of this setup, we set up two 1 CPU / 1GB RAM VPSs (one without WP Super Cache, one with it) and we used Apache JMeter to perform a load test against them (multiple users accessing 5 WordPress pages over 10 seconds in a loop).</p>

<p>The non-cached server was able to handle about 3 simulated users per second before showing performance issues due to CPU utilization.</p>

<p>The cached server, with WP Super Cache installed, was able to serve over 50 simulated users per second (<em>millions a day</em>) without showing any performance degradation--in fact, it returned the requests more quickly because the requested pages were cached!</p>

<p>A tutorial on how to use Apache JMeter to perform your own load testing is available here: <a href="https://indiareads/community/tutorials/how-to-use-apache-jmeter-to-perform-load-testing-on-a-web-server">How To Use Apache JMeter To Perform Load Testing on a Web Server</a></p>

<h2 id="conclusion">Conclusion</h2>

<p>Now that you have WP Super Cache and Jetpack Photon installed, you should be able to serve many more users than before. You may want to play with the WP Super Cache settings until you feel like you have a configuration that best suits your needs.</p>

<p>Feel free to post questions or your own performance comparisons!</p>

    