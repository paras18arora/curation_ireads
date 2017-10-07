<link rel='stylesheet' href='digital_ocean.css'><br><img src=https://community-cdn-digitalocean-com.global.ssl.fastly.net/assets/tutorials/images/large/Speed_UP_tw.png?1426699766/> <br> 
      <h2 id="introduction">Introduction</h2>

<h3 id="background">Background</h3>

<p><strong>Drupal</strong> is one of the most popular free open-source content management systems.</p>

<p>Since it uses an underlying database to store and retrieve data such as content pages, news items, comments, and blog posts, Drupal needs a considerable amount of processing power to serve a single page view. Each page impression involves launching the PHP interpreter, processing all Drupal elements, accessing the database to get the information, preparing the visual layout, and serving the ready content to the user. </p>

<p>This intensive process makes it difficult to cope with growing numbers of people viewing the website simultaneously. Since each visitor needs a non-negligible amount of processing power to be served, your server resources can quickly become a bottleneck.</p>

<p>There are many ways to accommodate growth and cope with performance problems, most of which can be considered a method of <em>scaling</em>. Scaling in terms of software is considered the system's ability to accommodate increased load like increased numbers of simultaneous visitors.</p>

<p><strong>Varnish</strong> helps with scaling on a software level, by adding additional software that can help with the bottlenecks.</p>

<p>This article was tested on <strong>Ubuntu 14.04</strong> but should work with minor path changes on <strong>Debian 7</strong>. It may work on other distributions as well with minor changes.</p>

<h3 id="varnish-cache">Varnish Cache</h3>

<p><strong>Varnish</strong> is a cache, which means its role is to store and remember what a web application serves to the user the first time the content is accessed. Then it can serve the same content <strong>again</strong> for subsequent requests without asking the web application again.</p>

<p>It can be used to serve static content, such as images, scripts, or stylesheets, because Varnish is blazingly fast and copes with traffic much better than <strong>Apache</strong> does. It can also be used to cache <em>quasi-static</em> content; that is, content that is generated dynamically by the application (using the database and taking a considerable amount of time to prepare), but that stays unchanged for a period of time, making the content suitable for caching. </p>

<p>For example, when an article on the website gets published, it is rarely updated. It is then completely unnecessary to engage all the processing bits and pieces of Drupal to compute and show the same article every time it is requested. It will be perfectly fine for Varnish to remember serve the same page again without contacting Drupal at all. This makes it easy for Varnish to serve the same content to 10, 100, or even 1000 people at once - since serving a cached page requires very little processing power.</p>

<p>In most scenarios using <strong>Varnish</strong> makes almost any website unbelievably faster. It also make it easier to cope with sudden spikes of interest (for example, when a very popular article gets published). All this translates to happier visitors who get their content delivered faster and more reliably.</p>

<h3 id="prerequisites">Prerequisites</h3>

<p>The article assumes you have a working <strong>Drupal</strong>-based website on LAMP already up and running. Here are the requirements:</p>

<ul>
<li>A <strong>Ubuntu 14.04</strong> or <strong>Debian 7</strong> Droplet (tested on Ubuntu 14.04)</li>
<li>A <a href="https://indiareads/community/tutorials/how-to-add-and-delete-users-on-an-ubuntu-14-04-vps">sudo</a> user</li>
<li><a href="https://indiareads/community/tutorials/how-to-install-linux-apache-mysql-php-lamp-stack-on-ubuntu-14-04">LAMP</a></li>
<li><a href="https://indiareads/community/tutorials/how-to-install-drupal-on-an-ubuntu-14-04-server-with-apache">Drupal</a></li>
</ul>

<h2 id="step-1-—-reconfiguring-apache">Step 1 — Reconfiguring Apache</h2>

<p>By default, <strong>Apache</strong> listens on port 80. This lets Apache handle web requests like a browser URL request for <strong>http://example.com</strong>. To use <strong>Varnish</strong>, it must be able to handle those requests instead. First we have to tell Apache not to handle requests on port 80 anymore.</p>

<h3 id="changing-the-port-apache-listens-on">Changing the Port Apache Listens on</h3>

<p>The port on which <strong>Apache</strong> listens by default is set in a file called <span class="highlight">ports.conf</span>, which on both <strong>Debian</strong> and <strong>Ubuntu</strong> is located in <code>/etc/apache2</code>.</p>

<p>Edit the file:</p>
<pre class="code-pre "><code langs="">sudo nano /etc/apache2/ports.conf
</code></pre>
<p>This will run a <strong>nano</strong> text editor showing the default contents of that file, which should be similar to the following. Update the <code>NameVirtualHost</code> and <span class="highlight">Listen</span> lines to use port <strong>81</strong>:</p>
<pre class="code-pre "><code langs=""># If you just change the port or add more ports here, you will likely also
# have to change the VirtualHost statement in
# /etc/apache2/sites-enabled/000-default
# This is also true if you have upgraded from before 2.2.9-3 (i.e. from
# Debian etch). See /usr/share/doc/apache2.2-common/NEWS.Debian.gz and
# README.Debian.gz

NameVirtualHost *:<span class="highlight">81</span>
Listen <span class="highlight">81</span>

<IfModule mod_ssl.c>
    # If you add NameVirtualHost *:443 here, you will also have to change
    # the VirtualHost statement in /etc/apache2/sites-available/default-ssl
    # to <VirtualHost *:443>
    # Server Name Indication for SSL named virtual hosts is currently not
    # supported by MSIE on Windows XP.
    Listen 443
</IfModule>
</code></pre>
<p>Let's save the file by pressing <strong>CTRL+x</strong>, then <strong>y</strong>, then <strong>Enter</strong>.</p>

<h3 id="changing-the-port-for-the-virtual-host">Changing the Port for the Virtual Host</h3>

<p>By default, a fresh <strong>Apache</strong> installation has one virtual host specified in a configuration file located in <code>/etc/apache2/sites-enabled/000-default</code>. If you have more than one virtual host configured, you will have to modify <strong>all</strong> of them.</p>

<p>To modify the configuration of the default Apache virtual host, let's type:</p>
<pre class="code-pre "><code langs="">sudo nano /etc/apache2/sites-enabled/000-default.conf
</code></pre>
<p>The file contents begin with lines like following:</p>
<pre class="code-pre "><code langs=""><VirtualHost *:80>
        ServerAdmin webmaster@localhost
</code></pre>
<p>Just like before, we have to change the number from <strong>80</strong> to <strong>81</strong>:</p>
<pre class="code-pre "><code langs=""><VirtualHost *:<span class="highlight">81</span>>
        ServerAdmin webmaster@localhost
</code></pre>
<p>Save the file using <strong>CTRL-x</strong> followed by <strong>y</strong> and <strong>Enter</strong>.</p>

<h3 id="reloading-the-apache-configuration">Reloading the Apache Configuration</h3>

<p>After those changes, the Apache configuration needs to be reloaded:</p>
<pre class="code-pre "><code langs="">sudo service apache2 reload
</code></pre>
<p>Now Apache will accept incoming requests on the new port <strong>81</strong> and not on <strong>80</strong> as before.</p>

<p>We can confirm that by opening our website in the browser – it should fail to open without specifying the port (like <strong>http://example.com</strong>) but show correctly after adding the new port to the address (like <strong>http://example.com:81</strong>).</p>

<p>We are now ready to install and configure <strong>Varnish</strong> to help us make the site faster.</p>

<h2 id="step-2-—-installing-varnish">Step 2 — Installing Varnish</h2>

<p>Both Debian and Ubuntu have system packages with <strong>Varnish</strong>, but we recommend using prebuilt packages made by the authors of Varnish. It will ensure that Varnish is up to date, which will not be true for the system packages.</p>

<p>First, make sure the <strong>apt-transport-https</strong> package is installed, which allows the system to install packages over a secure connection:</p>
<pre class="code-pre "><code langs="">sudo apt-get install apt-transport-https
</code></pre>
<p>This will either install the necessary package or tell us it has already been installed.</p>

<p>The public key of the Varnish package server needs to be installed in order to verify the authenticity of the installed packages. First, switch to <strong>root</strong>:</p>
<pre class="code-pre "><code langs="">sudo su
</code></pre>
<p>Add the key:</p>
<pre class="code-pre "><code langs="">curl https://repo.varnish-cache.org/ubuntu/GPG-key.txt | apt-key add -
</code></pre>
<p>For <strong>Debian</strong>:</p>
<pre class="code-pre "><code langs="">echo "deb https://repo.varnish-cache.org/debian/ wheezy varnish-4.0" >> /etc/apt/sources.list.d/varnish-cache.list
</code></pre>
<p>For <strong>Ubuntu</strong>:</p>
<pre class="code-pre "><code langs="">echo "deb https://repo.varnish-cache.org/ubuntu/ trusty varnish-4.0" >> /etc/apt/sources.list.d/varnish-cache.list
</code></pre>
<p><strong>You can switch back to your sudo user now.</strong></p>

<p>Update your system:</p>
<pre class="code-pre "><code langs="">sudo apt-get update
</code></pre>
<p>Install Varnish:</p>
<pre class="code-pre "><code langs="">sudo apt-get install varnish
</code></pre>
<p>This installs and runs Varnish!</p>

<h2 id="step-3-—-making-varnish-listen-on-port-80">Step 3 — Making Varnish Listen on Port 80</h2>

<p>By default Varnish listens on port <strong>6081</strong>. We will make Varnish listen on port <strong>80</strong> instead, taking all the incoming requests from our web users, just like <strong>Apache</strong> did before.</p>

<p>Let's open the Varnish configuration file using:</p>
<pre class="code-pre "><code langs="">sudo nano /etc/default/varnish
</code></pre>
<p>Locate the uncommented section shown below:</p>
<pre class="code-pre "><code langs="">. . .

## Alternative 2, Configuration with VCL
#
# Listen on port 6081, administration on localhost:6082, and forward to
# one content server selected by the vcl file, based on the request.
# Use a 256MB memory based cache.
#
DAEMON_OPTS="-a :6081 \
             -T localhost:6082 \
             -f /etc/varnish/default.vcl \
             -S /etc/varnish/secret \
             -s malloc,256m"

. . .
</code></pre>
<p>Update the <span class="highlight">DAEMON_OPTS="-a:</span> line to use port <strong>80</strong> (remember to keep the <code>\</code> as well):</p>
<pre class="code-pre "><code langs="">. . .

DAEMON_OPTS="-a :<span class="highlight">80</span> \
             -T localhost:6082 \
             -f /etc/varnish/default.vcl \
             -S /etc/varnish/secret \
             -s malloc,256m"

. . .
</code></pre>
<p>Save the file using <strong>CTRL-x</strong> and <strong>y</strong> followed by <strong>Enter</strong>. </p>

<p>Restart <strong>Varnish</strong> for the changes to take effect:</p>
<pre class="code-pre "><code langs="">sudo service varnish restart
</code></pre>
<p>We should see messages like the following with no errors:</p>
<pre class="code-pre "><code langs="">[ ok ] Stopping HTTP accelerator: varnishd.
[ ok ] Starting HTTP accelerator: varnishd.
</code></pre>
<p>Now check your website in the browser. Instead of your Drupal site that was previously available, you will see a white page with an error message saying:</p>
<pre class="code-pre "><code langs="">Error 503 Backend fetch failed

Backend fetch failed

Guru Meditation:
XID: 131081

Varnish cache server
</code></pre>
<p>That means that Varnish has been properly configured to accept incoming connections, but is not yet available to serve our Drupal site. We will make changes to the configuration to bring the former Drupal site back online in the following steps.</p>

<h2 id="how-varnish-works">How Varnish Works</h2>

<p>A great resource to get a solid understanding of <strong>Varnish</strong> is the official <a href="https://www.varnish-software.com/static/book/index.html">Varnish Book</a>, but we'll cover a few basic facts about how <strong>Varnish</strong> works here.</p>

<p>You can also skip ahead to the next step if you want to finish the installation now and learn more later. However, if you learn how Varnish works, you'll have a greater understanding of the next steps.</p>

<h3 id="vcl-language">VCL Language</h3>

<p>Varnish's configuration is written in a language called <strong>VCL</strong> (Varnish Configuration Language). It's a simple programming language that gets compiled to native <strong>C</strong> code by Varnish itself.</p>

<p>The configuration consists of <em>methods</em> that get executed during different moments of handling incoming web requests, along with the rest of the configuration contents.</p>

<p>Some instructions get executed by Varnish when it receives the request from the browser, but before the request gets handled, which tell it whether to forward the request to the actual application, or to serve cached content. In these instructions it is possible to manipulate the incoming request, change its contents, or make decisions based on the request (the URL, the file name, the headers, or the cookies).  </p>

<p>Other instructions are executed when Varnish decides to get contents from the actual application (in our case, the Drupal website). Those instructions can be used to manipulate the contents received from the application.</p>

<p>Still other instructions are executed when Varnish serves the cached content without retrieving it fresh from the application.</p>

<p>Using <strong>VCL</strong>, it is possible to build a complex logic making different caching decisions based upon many factors. It is also possible to build a very simple set of instructions.</p>

<p>Varnish comes with a sensible default implementation for <strong>all</strong> its methods that can be changed if needed. That means that it is possible to specify in the configuration only <strong>some</strong> methods, and even then, only <strong>some</strong> instructions, still relying on the defaults for the rest. That makes it very easy to use basic Varnish abilities, while making it possible to create very complex configurations as you add custom instructions.</p>

<h3 id="what-gets-cached-and-what-doesn-39-t">What gets cached and what doesn't?</h3>

<p>Perhaps the most difficult thing about configuring Varnish or any other caching mechanism is to decide <strong>when</strong> and <strong>what</strong> to cache. Most problems come from improper decisions – by caching either too much or not enough.</p>

<p>With a typical Drupal installation, this may lead to two different problem scenarios.</p>

<p>The first one is when not enough pages are cached, which renders Varnish almost unnecessary. It does not speed up things at all, since most of pages are fetched directly from the Drupal application every single time. This doesn't help with performance problems, but it also doesn't break anything.</p>

<p>The second one is when too many pages get cached. In this case it might be impossible to log in to the administrative panel at all. Visitors may get old, invalid content, or even mixed-up content when different content is served for anonymous and logged in users. In this scenario it is possible to break things that did work fine without Varnish.</p>

<p>Let's go through some common factors to help us decide whether <strong>Varnish</strong> will or will not cache content.</p>

<h3 id="varnish-caches-everything">Varnish caches everything</h3>

<p>In default a scenario, the basic premise is that Varnish caches <strong>everything</strong>. The caching in Varnish is <em>exclusive</em>, not <em>inclusive</em>, which means that everything gets cached unless you make a rule otherwise.</p>

<h3 id="request-method">Request method</h3>

<p>The request method is the basic definition of a request. Varnish by default caches <strong>only</strong> <em>GET</em> and <em>HEAD</em> requests, <strong>never</strong> caching others like <em>POST</em>, <em>PUT</em>, and <em>DELETE</em>. This makes sure that requests that are intended to make some changes to the data get through to the application intact without being cached.</p>

<h3 id="authorization">Authorization</h3>

<p>By default, requests to password-protected pages are not cached at all. <strong>This holds true only for pages protected using HTTP Basic Authorization</strong>. Varnish is not aware of application-specific mechanisms, such as Drupal login pages. We will have to add our own rules to make sure login pages aren't cached.</p>

<h3 id="cache-headers">Cache headers</h3>

<p>Sometimes web applications return their own caching information in headers. Varnish takes those headers into account, so when a web application such as Drupal tells Varnish to never cache its response, that's exactly what will happen unless we program another behaviour in the <strong>VCL</strong> file. Since Drupal does send its own caching information, this will become important further on.</p>

<h3 id="cookies">Cookies</h3>

<p>Cookies are perhaps the most important and most difficult part of making caching decisions with web applications.</p>

<p>By default, if there are request <strong>or</strong> response cookies set, the page will <strong>not</strong> be cached. It is a sensible decision, since, for example, logged-in users are identified by a session cookie. If pages with cookies were cached, all logged in users would get the same content, and the application would be unable to discern between users. It is also, however, one of the biggest source of problems, since the use of cookies is widespread. Often harmless cookies are present in the requests, such as a <strong>Google Analytics</strong> tokens, which are not used by the application at all, but will make content uncacheable as well. Without careful decisions on which cookies should prohibit caching and which should be ignored, with today's web applications we would end up with almost no caching at all, since there are so many cookies floating around.</p>

<p>Most fragments of Drupal-specific configuration for Varnish will deal with proper cookie handling to remove unnecessary cookies and allow caching, but retain those necessary to, for example, maintain administration page functionality.</p>

<h2 id="step-4-—-configuring-varnish-for-drupal">Step 4 — Configuring Varnish for Drupal</h2>

<p>Having the basic understanding of how caching with Varnish works, we can proceed to configure Varnish to work with our Drupal site.</p>

<p>Let's open the Varnish VCL configuration file:</p>
<pre class="code-pre "><code langs="">sudo nano /etc/varnish/default.vcl
</code></pre>
<p>The default contents shows all the Varnish methods empty, which means the defaults are in use.</p>

<p>We can add our own configuration instructions to achieve the necessary caching policy. </p>

<p>The first block instructs Varnish how to contact the backend web server, which in our case is <strong>Apache</strong> with a <strong>Drupal</strong> installation. We will change the code to reflect the port <strong>81</strong> we have used to configure <strong>Apache</strong>.</p>
<pre class="code-pre "><code langs="">. . .

# Default backend definition. Set this to point to your content server.
backend default {
    .host = "127.0.0.1";
    .port = "<span class="highlight">81</span>";
}

. . .
</code></pre>
<p>Now locate the empty placeholder method <code>vcl_recv</code>:</p>
<pre class="code-pre "><code langs="">. . .

sub vcl_recv {
    # Happens before we check if we have this in cache already.
    #
    # Typically you clean up the request here, removing cookies you don't need,
    # rewriting the request, etc.
}

. . .
</code></pre>
<p>The code in this method is executed <em>before</em> <strong>Varnish</strong> contacts our <strong>Drupal</strong> site. It's the place when we can strip some cookies coming from the browser, force caching (or not) for certain addresses, and make the first caching decision. We will add several rules that accomplish the following:</p>

<ol>
<li>Allow Varnish to serve stale (old) cache content in case of a Drupal failure. It will make the site partially available even if Drupal fails to respond</li>
<li>Ensure that no administrative pages are cached at all, forcing Varnish to skip the cache for certain URLs</li>
<li>Ensure caching of static files – images, scripts, stylesheets</li>
<li>Strip all cookies other than several cookies needed by Drupal to work properly, including user login features</li>
</ol>

<p>To achieve this let's replace the default block with the following one. Lines preceded with <strong>#</strong> are comments and will not be taken into account by Varnish, but are here to help make the configuration file easy to understand. This entire block is new and can be pasted in as-is:</p>
<pre class="code-pre "><code langs="">. . .

sub vcl_recv {

    # Return (pass) instructs Varnish not to cache the request
    # when the condition is met.

    ## ADMIN PAGES ##

    # Here we filter out all URLs containing Drupal administrative sections
    if (req.url ~ "^/status\.php$" ||
        req.url ~ "^/update\.php$" ||
        req.url ~ "^/admin$" ||
        req.url ~ "^/admin/.*$" ||
        req.url ~ "^/user$" ||
        req.url ~ "^/user/.*$" ||
        req.url ~ "^/flag/.*$" ||
        req.url ~ "^.*/ajax/.*$" ||
        req.url ~ "^.*/ahah/.*$") {
           return (pass);
    }


    ## BACKUP AND MIGRATE MODULE ##

    # Backup and Migrate is a very popular Drupal module that needs to be excluded
    # It won't work with Varnish
    if (req.url ~ "^/admin/content/backup_migrate/export") {
        return (pipe);
    }

    ## COOKIES ##

    # Remove cookies for stylesheets, scripts, and images used throughout the site.
    # Removing cookies will allow Varnish to cache those files.
    if (req.url ~ "(?i)\.(css|js|jpg|jpeg|gif|png|ico)(\?.*)?$") {
        unset req.http.Cookie;
    }

    # Remove all cookies that are not necessary for Drupal to work properly.
    # Since it would be cumbersome to REMOVE certain cookies, we specify
    # which ones are of interest to us, and remove all others. In this particular
    # case we leave SESS, SSESS and NO_CACHE cookies used by Drupal's administrative
    # interface. Cookies in cookie header are delimited with ";", so when there are
    # many cookies, the header looks like "Cookie1=value1; Cookie2=value2; Cookie3..." 
    # and so on. That allows us to work with ";" to split cookies into individual
    # ones.
    #
    # The method for filtering unnecessary cookies has been adopted from:
    # https://fourkitchens.atlassian.net/wiki/display/TECH/Configure+Varnish+3+for+Drupal+7
    if (req.http.Cookie) {
        # 1. We add ; to the beginning of cookie header
        set req.http.Cookie = ";" + req.http.Cookie;
        # 2. We remove spaces following each occurence of ";". After this operation
        # all cookies are delimited with no spaces.
        set req.http.Cookie = regsuball(req.http.Cookie, "; +", ";");
        # 3. We replace ";" INTO "; " (adding the space we have previously removed) in cookies
        # named SESS..., SSESS... and NO_CACHE. After this operation those cookies will be 
        # easy to differentiate from the others, because those will be the only one with space
        # after ";"   
        set req.http.Cookie = regsuball(req.http.Cookie, ";(SESS[a-z0-9]+|SSESS[a-z0-9]+|NO_CACHE)=", "; \1=");
        # 4. We remove all cookies with no space after ";", so basically we remove all cookies other
        # than those above.
        set req.http.Cookie = regsuball(req.http.Cookie, ";[^ ][^;]*", "");
        # 5. We strip leading and trailing whitespace and semicolons.
        set req.http.Cookie = regsuball(req.http.Cookie, "^[; ]+|[; ]+$", "");

        # If there are no cookies after our striping procedure, we remove the header altogether,
        # thus allowing Varnish to cache this page
        if (req.http.Cookie == "") {
            unset req.http.Cookie;
        }
        # if any of our cookies of interest are still there, we disable caching and pass the request
        # straight to Apache and Drupal
        else {
            return (pass);
        }
    }
}

. . .
</code></pre>
<p>The next method is the <span class="highlight">vcl<em>backend</em>response</span>. This method is responsible for processing the response from Apache and Drupal before putting it into cache or discarding it from cache. We can change what Drupal sends to fit into our caching strategy.</p>

<p>The default method looks like this:</p>
<pre class="code-pre "><code langs="">. . .

sub vcl_backend_response {
    # Happens after we have read the response headers from the backend.
    #
    # Here you clean the response headers, removing silly Set-Cookie headers
    # and other mistakes your backend does.
}

. . .
</code></pre>
<p>Let's replace it with this entirely new block as-is. Comments are included:</p>
<pre class="code-pre "><code langs="">. . .

sub vcl_backend_response {
    # Remove cookies for stylesheets, scripts and images used throughout the site.
    # Removing cookies will allow Varnish to cache those files. It is uncommon for
    # static files to contain cookies, but it is possible for files generated
    # dynamically by Drupal. Those cookies are unnecessary, but could prevent files
    # from being cached.
    if (bereq.url ~ "(?i)\.(css|js|jpg|jpeg|gif|png|ico)(\?.*)?$") {
        unset beresp.http.set-cookie;
    }
}

. . .
</code></pre>
<p>The code removes cookies for static files using the same method of choosing the files as before, so cookies get removed for the same files both in <span class="highlight">vcl<em>recv</em></span> and <span class="highlight">vclbackend_response</span>.</p>

<p>Let's save the configuration file with <strong>CTRL+x</strong>, then <strong>y</strong> followed by <strong>Enter</strong>. Changing other methods is unnecessary.</p>

<h2 id="step-5-—-restarting-varnish">Step 5 — Restarting Varnish</h2>

<p>Restart <strong>Varnish</strong> for the changes to take effect:</p>
<pre class="code-pre "><code langs="">sudo service varnish restart
</code></pre>
<p>The Varnish server should restart without errors.</p>

<p>Now you should be able to view your Drupal website again in your browser.</p>

<p>There is, however, one more step than we should take care of before our Drupal site will be properly cached. We need to enable caching in Drupal itself.</p>

<h2 id="step-6-—-enabling-cache-in-drupal">Step 6 — Enabling Cache in Drupal</h2>

<p>By default Drupal has its cache mechanisms disabled. This results in headers being sent to Varnish which force pages not to be cached at all. So, a disabled Drupal cache will automatically block Varnish from helping us speed up the site.</p>

<p>To enable Drupal caching, log in to your Drupal site as an administrator.</p>

<p>Choose the <strong>Configuration</strong> menu and then <strong>Performance</strong>.</p>

<p><img src="https://assets.digitalocean.com/articles/drupal_varnish/1.png" alt="Drupal Configuration menu" /></p>

<p>In the <strong>Performance</strong> section, locate and check the <strong>Cache pages for anonymous users</strong> and <strong>Cache blocks</strong> settings.</p>

<p>Set the <strong>Minimum cache lifetime</strong> and <strong>Expiration of cached pages</strong> to a sensible value, like <strong>30 minutes</strong>. This value gives a considerable performance gain, and still makes sure that the cache is not stale for too long. The best setting for the cache lifetime depends on the individual site and how often it gets updated. After changing the values click <strong>Save configuration</strong>.</p>

<p><img src="https://assets.digitalocean.com/articles/drupal_varnish/2.png" alt="Cache settings" /></p>

<p>This completes the necessary configuration to make Varnish cache our Drupal site.</p>

<h2 id="step-7-—-verifying-varnish-configuration">Step 7 — Verifying Varnish Configuration</h2>

<p>To make sure Varnish is caching the site, we can use the simple tool called <a href="http://www.isvarnishworking.com/">Is Varnish Working?</a>. Enter your website's address in the form. You should see a response like the one below:</p>

<p><img src="https://assets.digitalocean.com/articles/drupal_varnish/3.png" alt="Working Varnish" /></p>

<p>You might want to check twice if you get the "sort of" message the first time.</p>

<h3 id="further-reading">Further Reading</h3>

<p>Topics covered in this article are just the tip of the iceberg. <strong>Varnish</strong> is very powerful software and can help with much more than just simple caching. The official <a href="https://www.varnish-cache.org/docs/4.0/">Varnish documentation</a> is a vast resource about Varnish possibilities and VCL syntax. To get the most out of Varnish and Drupal, it is also best to get to know Drupal's own possibilities in terms of improving performance. The official <a href="https://www.drupal.org/node/627252">Drupal performance documentation</a> is a good starting point. </p>

<p>Varnish is a tool that can help your site's performance immensely, but ultimately it is not a magical solution to all performance bottlenecks, and best results are achieved by careful planning at all stages. Having said that, even the simplest <strong>Varnish</strong> configuration can make your site snappy in a matter of minutes.</p>

    