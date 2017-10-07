<link rel='stylesheet' href='digital_ocean.css'><br> <br> 
      <h3 id="introduction">Introduction</h3>

<p>Nginx has become one of the most flexible and powerful web server solutions available.  However, in terms of design, it is first and foremost a proxy server.  This focus means that Nginx is very performant when working to handle requests with other servers.</p>

<p>Nginx can proxy requests using http, FastCGI, uwsgi, SCGI, or memcached.  In this guide, we will discuss FastCGI proxying, which is one of the most common proxying protocols.</p>

<h2 id="why-use-fastcgi-proxying">Why Use FastCGI Proxying?</h2>

<p>FastCGI proxying within Nginx is generally used to translate client requests for an application server that does not or should not handle client requests directly.  FastCGI is a protocol based on the earlier CGI, or common gateway interface, protocol meant to improve performance by not running each request as a separate process.  It is used to efficiently interface with a server that processes requests for dynamic content.</p>

<p>One of the main use-cases of FastCGI proxying within Nginx is for PHP processing.  Unlike Apache, which can handle PHP processing directly with the use of the <code>mod_php</code> module, Nginx must rely on a separate PHP processor to handle PHP requests.  Most often, this processing is handled with <code>php-fpm</code>, a PHP processor that has been extensively tested to work with Nginx.</p>

<p>Nginx with FastCGI can be used with applications using other languages so long as there is an accessible component configured to respond to FastCGI requests.</p>

<h2 id="fastcgi-proxying-basics">FastCGI Proxying Basics</h2>

<p>In general, proxying requests involves the proxy server, in this case Nginx, forwarding requests from clients to a backend server.  The directive that Nginx uses to define the actual server to proxy to using the FastCGI protocol is <code>fastcgi_pass</code>.</p>

<p>For example, to forward any matching requests for PHP to a backend devoted to handling PHP processing using the FastCGI protocol, a basic location block may look something like this:</p>
<pre class="code-pre "><code class="code-highlight language-nginx"># server context

location ~ \.php$ {
    fastcgi_pass 127.0.0.1:9000;
}

. . .

</code></pre>
<p>The above snippet won't actually work out of the box, because it gives too little information.  Any time that a proxy connection is made, the original request must be translated to ensure that the proxied request makes sense to the backend server.  Since we are changing protocols with a FastCGI pass, this involves some additional work.</p>

<p>While http-to-http proxying mainly involves augmenting http headers to ensure that the backend has the information it needs to respond to the proxy server on behalf of the client, FastCGI is a separate protocol that cannot read http headers.  Due to this consideration, any pertinent information must be passed to the backend through other means.</p>

<p>The primary method of passing extra information when using the FastCGI protocol is with parameters.  The background server should be configured to read and process these, modifying its behavior depending on what it finds.  Nginx can set FastCGI parameters using the <code>fastcgi_param</code> directive.</p>

<p>The bare minimum configuration that will actually work in a FastCGI proxying scenario for PHP is something like this:</p>
<pre class="code-pre "><code class="code-highlight language-nginx"># server context

location ~ \.php$ {
    fastcgi_param REQUEST_METHOD $request_method;
    fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
    fastcgi_pass 127.0.0.1:9000;
}

. . .

</code></pre>
<p>In the above configuration, we set two FastCGI parameters, called <code>REQUEST_METHOD</code> and <code>SCRIPT_FILENAME</code>.  These are both required in order for the backend server to understand the nature of the request.  The former tells it what type of operation it should be performing, while the latter tell the upstream which file to execute.</p>

<p>In the example, we used some Nginx variables to set the values of these parameters.  The <code>$request_method</code> variable will always contain the http method requested by the client.</p>

<p>The <code>SCRIPT_FILENAME</code> parameter is set to a combination of the <code>$document_root</code> variable and the <code>$fastcgi_script_name</code> variable.  The <code>$document_root</code> will contain the path to the base directory, as set by the <code>root</code> directive.  The <code>$fastcgi_script_name</code> variable will be set to the request URI.  If the request URI ends with a slash (/), the value of the <code>fastcgi_index</code> directive will be appended onto the end.  This type of self-referential location definitions are possible because we are running the FastCGI processor on the same machine as our Nginx instance.</p>

<p>Let's look at another example:</p>
<pre class="code-pre "><code class="code-highlight language-nginx"># server context
root /var/www/html;

location /scripts {
    fastcgi_param REQUEST_METHOD $request_method;
    fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
    fastcgi_index index.php;
    fastcgi_pass unix:/var/run/php5-fpm.sock;
}

. . .

</code></pre>
<p>If the location above is selected to handle a request for <code>/scripts/test/</code>, the value of the <code>SCRIPT_FILENAME</code> will be a combination of the values of the <code>root</code> directive, the request URI, and the <code>fastcgi_index</code> directive.  In this example, the parameter will be set to <code>/var/www/html/scripts/test/index.php</code>.</p>

<p>We made one other significant change in the configuration above in that we specified the FastCGI backend using a Unix socket instead of a network socket.  Nginx can use either type of interface to connect to the FastCGI upstream.  If the FastCGI processor lives on the same host, typically a Unix socket is recommended for security.</p>

<h2 id="breaking-out-fastcgi-configuration">Breaking Out FastCGI Configuration</h2>

<p>A key rule for maintainable code is to try to follow the DRY ("Don't Repeat Yourself") principle.  This helps reduce errors, increase reusability, and allows for better organization.  Considering that one of the core recommendations for administering Nginx is to always set directives at their broadest applicable scope, these fundamental goals also apply to Nginx configuration.</p>

<p>When dealing with FastCGI proxy configurations, most instances of use will share a large majority of the configuration.  Because of this and because of the way that the Nginx inheritance model works, it is almost always advantageous to declare parameters in a general scope.</p>

<h3 id="declaring-fastcgi-configuration-details-in-parent-contexts">Declaring FastCGI Configuration Details in Parent Contexts</h3>

<p>One way to reduce repetition is to declare the configuration details in a higher, parent context.  All parameters outside of the actual <code>fastcgi_pass</code> can be specified at higher levels.  They will cascade downwards into the location where the pass occurs.  This means that multiple locations can use the same config.</p>

<p>For instance, we could modify the last configuration snippet from the above section to make it useful in more than one location:</p>
<pre class="code-pre "><code class="code-highlight language-nginx"># server context
root /var/www/html;

fastcgi_param REQUEST_METHOD $request_method;
fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
fastcgi_index index.php;

location /scripts {
    fastcgi_pass unix:/var/run/php5-fpm.sock;
}

location ~ \.php$ {
    fastcgi_pass 127.0.0.1:9000;
}

. . .

</code></pre>
<p>In the above example, both of the <code>fastcgi_param</code> declarations and the <code>fastcgi_index</code> directive are available in both of the location blocks that come after.  This is one way to remove repetitive declarations.</p>

<p>However, the configuration above has one serious disadvantage.  If any <code>fastcgi_param</code> is declared in the lower context, <em>none</em> of the <code>fastcgi_param</code> values from the parent context will be inherited.  You either use <em>only</em> the inherited values, or you use none of them.</p>

<p>The <code>fastcgi_param</code> directive is an <em>array</em> directive in Nginx parlance.  From a users perspective, an array directive is basically any directive that can be used more than once in a single context.  Each subsequent declaration will append the new information to what Nginx knows from the previous declarations.  The <code>fastcgi_param</code> directive was designed as an array directive in order to allow users to set multiple parameters.</p>

<p>Array directives inherit to child contexts in a different way than some other directives.  The information from array directives will inherit to child contexts <em>only if they are not present at any place in the child context</em>.  This means that if you use <code>fastcgi_param</code> within your location, it will effectively clear out the values inherited from the parent context completely.</p>

<p>For example, we could modify the above configuration slightly:</p>
<pre class="code-pre "><code class="code-highlight language-nginx"># server context
root /var/www/html;

fastcgi_param REQUEST_METHOD $request_method;
fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
fastcgi_index index.php;

location /scripts {
    fastcgi_pass unix:/var/run/php5-fpm.sock;
}

location ~ \.php$ {
    fastcgi_param QUERY_STRING $query_string;
    fastcgi_pass 127.0.0.1:9000;
}

. . .

</code></pre>
<p>At first glance, you may think that the <code>REQUEST_METHOD</code> and <code>SCRIPT_FILENAME</code> parameters will be inherited into the second location block, with the <code>QUERY_STRING</code> parameter being additionally available for that specific context.</p>

<p>What actually happens is that <em>all</em> of the parent <code>fastcgi_param</code> values are wiped out in the second context, and only the <code>QUERY_STRING</code> parameter is set.  The <code>REQUEST_METHOD</code> and <code>SCRIPT_FILENAME</code> parameters will remain unset.</p>

<h4 id="a-note-about-multiple-values-for-parameters-in-the-same-context">A Note About Multiple Values for Parameters in the Same Context</h4>

<p>One thing that is definitely worth mentioning at this point is the implications of setting multiple values for the same parameters within a single context.  Let's take the following example as a discussion point:</p>
<pre class="code-pre "><code class="code-highlight language-nginx"># server context

location ~ \.php$ {
    fastcgi_param REQUEST_METHOD $request_method;
    fastcgi_param SCRIPT_FILENAME $request_uri;

    fastcgi_param DOCUMENT_ROOT initial;
    fastcgi_param DOCUMENT_ROOT override;

    fastcgi_param TEST one;
    fastcgi_param TEST two;
    fastcgi_param TEST three;

    fastcgi_pass 127.0.0.1:9000;
}

. . .

</code></pre>
<p>In the above example, we have set the <code>TEST</code> and <code>DOCUMENT_ROOT</code> parameters multiple times within a single context.  Since <code>fastcgi_param</code> is an array directive, each subsequent declaration is added to Nginx's internal records.  The <code>TEST</code> parameter will have declarations in the array setting it to <code>one</code>, <code>two</code>, and <code>three</code>.</p>

<p>What is important to realize at this point is that all of these will be passed to the FastCGI backend without any further processing from Nginx.  This means that it is completely up to the chosen FastCGI processor to decide how to handle these values.  Unfortunately, different FastCGI processors handle the passed values <a href="http://serverfault.com/questions/512028/nginx-fcgiwrap-how-come-order-of-fastcgi-param-matters">completely differently</a>.</p>

<p>For instance, if the above parameters were received by PHP-FPM, the <em>final</em> value would be interpreted to override any of the previous values.  So in this case, the <code>TEST</code> parameter would be set to <code>three</code>.  Similarly, the <code>DOCUMENT_ROOT</code> parameter would be set to <code>override</code>.</p>

<p>However, if the above value is passed to something like FsgiWrap, the values are interpreted very differently.  First, it makes an initial pass to decide which values to use to run the script.  It will use the <code>DOCUMENT_ROOT</code> value of <code>initial</code> to look for the script.  However, when it passes the actual parameters to the script, it will pass the final values, just like PHP-FPM.</p>

<p>This inconsistency and unpredictability means that you cannot and should not rely on the backend to correctly interpret your intentions when setting the same parameter more than one time.  The only safe solution is to only declare each parameter once.  This also means that there is no such thing as safely overriding a default value with the <code>fastcgi_param</code> directive.</p>

<h3 id="using-include-to-source-fastcgi-configuration-from-a-separate-file">Using Include to Source FastCGI Configuration from a Separate File</h3>

<p>There is another way to separate out your common configuration items.  We can use the <code>include</code> directive to read in the contents of a separate file to the location of the directive declaration.</p>

<p>This means that we can keep all of our common configuration items in a single file and include it anywhere in our configuration where we need it.  Since Nginx will place the actual file contents where the <code>include</code> is called, we will not be inheriting downward from a parent context to a child.  This will prevent the <code>fastcgi_param</code> values from being wiped out, allowing us to set additional parameters as necessary.</p>

<p>First, we can set our common FastCGI configuration values in a separate file in our configuration directory.  We will call this file <code>fastcgi_common</code>:</p>
<pre class="code-pre "><code class="code-highlight language-nginx">fastcgi_param REQUEST_METHOD $request_method;
fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
</code></pre>
<p>Now, we can read in this file wherever we wish to use those configuration values:</p>
<pre class="code-pre "><code class="code-highlight language-nginx"># server context
root /var/www/html;

location /scripts {
    include fastcgi_common;

    fastcgi_index index.php;
    fastcgi_pass unix:/var/run/php5-fpm.sock;
}

location ~ \.php$ {
    include fastcgi_common;
    fastcgi_param QUERY_STRING $query_string;
    fastcgi_param CONTENT_TYPE $content_type;
    fastcgi_param CONTENT_LENGTH $content_length;

    fastcgi_index index.php;
    fastcgi_pass 127.0.0.1:9000;
}

. . .

</code></pre>
<p>Here, we have moved some common <code>fastcgi_param</code> values to a file called <code>fastcgi_common</code> in our default Nginx configuration directory.  We then source that file when we want to insert the values declared within.</p>

<p>There are a few things to note about this configuration. </p>

<p>The first thing is that we did not place any values that we may wish to customize on a per-location basis in the file we plan to source.  Because of the problem with interpretation we mentioned above that occurs when setting multiple values for the same parameter, and because non-array directives can only be set once per context, only place items the common file that you will not want to change.  Every directive (or parameter key) that we may wish to customize on a per-context basis should be left out of the common file.</p>

<p>The other thing that you may have noticed is that we set some additional FastCGI parameters in the second location block.  This is the ability we were hoping to achieve.  We were able to set additional <code>fastcgi_param</code> parameters as needed, without wiping out the common values.</p>

<h4 id="using-the-fastcgi_params-file-or-the-fastcgi-conf-file">Using the fastcgi_params File or the fastcgi.conf File</h4>

<p>With the above strategy in mind, the Nginx developers and many distribution packaging teams have worked towards providing a sane set of common parameters that you can include in your FastCGI pass locations.  These are called <code>fastcgi_params</code> or <code>fastcgi.conf</code>.</p>

<p>These two files are largely the same, with the only difference actually being a consequence of the issue we discussed earlier about passing multiple values for a single parameter.  The <code>fastcgi_params</code> file does not contain a declaration for the <code>SCRIPT_FILENAME</code> parameter, while the <code>fastcgi.conf</code> file does.</p>

<p>The <code>fastcgi_params</code> file has been available for a much longer period of time.  In order avoid breaking configurations that relied on <code>fastcgi_params</code>, when the decision was made to provide a default value for <code>SCRIPT_FILENAME</code>, a new file needed to be created.  Not doing so may have resulted in that parameter being set in both the common file and FastCGI pass location.  This is described in great detail in <a href="http://blog.martinfjordvald.com/2013/04/nginx-config-history-fastcgi_params-versus-fastcgi-conf/">Martin Fjordvald's excellent post on the history of these two files</a>.</p>

<p>Many package maintainers for popular distributions have elected to include only one of these files or to mirror their content exactly.  If you only have one of these available, use the one that you have.  Feel free to modify it to suit your needs as well.</p>

<p>If you have both of these files available to you, for most FastCGI pass locations, it is probably better to include the <code>fastcgi.conf</code> file, as it includes a declaration for the <code>SCRIPT_FILENAME</code> parameter.  This is usually desirable, but there are some instances where you may wish to customize this value.</p>

<p>These can be included by referencing their location relative to the root Nginx configuration directory.  The root Nginx configuration directory is usually something like <code>/etc/nginx</code> when Nginx has been installed with a package manager.</p>

<p>You can include the files like this:</p>
<pre class="code-pre "><code class="code-highlight language-nginx"># server context

location ~ \.php$ {
    include fastcgi_params;
    # You would use "fastcgi_param SCRIPT_FILENAME . . ." here afterwards

    . . .

}
</code></pre>
<p>Or like this:</p>
<pre class="code-pre "><code class="code-highlight language-nginx"># server context

location ~ \.php$ {
    include fastcgi.conf;

    . . .

}
</code></pre>
<h2 id="important-fastcgi-directives-parameters-and-variables">Important FastCGI Directives, Parameters, and Variables</h2>

<p>In the above sections, we've set a fair number of parameters, often to Nginx variables, as a means of demonstrating other concepts.  We have also introduced some FastCGI directives without too much explanation.  In this section, we'll discuss some of the common directives to set, parameters that you might need to modify, and some variables that might contain the information you need.</p>

<h3 id="common-fastcgi-directives">Common FastCGI Directives</h3>

<p>The following represent some of the most useful directives for working with FastCGI passes:</p>

<ul>
<li><strong><code>fastcgi_pass</code></strong>: The actual directive that passes requests in the current context to the backend.  This defines the location where the FastCGI processor can be reached.</li>
<li><strong><code>fastcgi_param</code></strong>: The array directive that can be used to set parameters to values.  Most often, this is used in conjunction with Nginx variables to set FastCGI parameters to values specific to the request.</li>
<li><strong><code>try_files</code></strong>: Not a FastCGI-specific directive, but a common directive used within FastCGI pass locations.  This is often used as part of a request sanitation routine to make sure that the requested file exists before passing it to the FastCGI processor.</li>
<li><strong><code>include</code></strong>: Again, not a FastCGI-specific directive, but one that gets heavy usage in FastCGI pass contexts.  Most often, this is used to include common, shared configuration details in multiple locations.</li>
<li><strong><code>fastcgi_split_path_info</code></strong>: This directive defines a regular expression with two captured groups.  The first captured group is used as the value for the <code>$fastcgi_script_name</code> variable.  The second captured group is used as the value for the <code>$fastcgi_path_info</code> variable.  Both of these are often used to correctly parse the request so that the processor knows which pieces of the request are the files to run and which portions are additional information to pass to the script.</li>
<li><strong><code>fastcgi_index</code></strong>: This defines the index file that should be appended to <code>$fastcgi_script_name</code> values that end with a slash (<code>/</code>).  This is often useful if the <code>SCRIPT_FILENAME</code> parameter is set to <code>$document_root$fastcgi_script_name</code> and the location block is configured to accept requests with info after the file.</li>
<li><strong><code>fastcgi_intercept_errors</code></strong>: This directive defines whether errors received from the FastCGI server should be handled by Nginx or passed directly to the client.</li>
</ul>

<p>The above directives represent most of what you will be using when designing a typical FastCGI pass.  You may not use all of these all of the time, but we can begin to see that they interact quite intimately with the FastCGI parameters and variables that we will talk about next.</p>

<h3 id="common-variables-used-with-fastcgi">Common Variables Used with FastCGI</h3>

<p>Before we can talk about the parameters that you are likely to use with FastCGI passes, we should talk a bit about some common Nginx variables that we will take advantage of in setting those parameters.  Some of these are defined by Nginx's FastCGI module, but most are from the Core module.</p>

<ul>
<li><strong><code>$query_string</code> or <code>$args</code></strong>: The arguments given in the original client request.</li>
<li><strong><code>$is_args</code></strong>: Will equal "?" if there are arguments in the request and will be set to an empty string otherwise.  This is useful when constructing parameters that may or may not have arguments.</li>
<li><strong><code>$request_method</code></strong>: This indicates the original client request method.  This can be useful in determining whether an operation should be permitted within the current context.</li>
<li><strong><code>$content_type</code></strong>: This is set to the <code>Content-Type</code> request header.  This information is needed by the proxy if the user's request is a POST in order to correctly handle the content that follows.</li>
<li><strong><code>$content_length</code></strong>: This is set to the value of the <code>Content-Length</code> header from the client.  This information is required for any client POST requests.</li>
<li><strong><code>$fastcgi_script_name</code></strong>: This will contain the script file to be run.  If the request ends in a slash (/), the value of the <code>fastcgi_index</code> directive will be appended to the end.  In the event that the <code>fastcgi_split_path_info</code> directive is used, this variable will be set to the first captured group defined by that directive.  The value of this variable should indicate the actual script to be run.</li>
<li><strong><code>$request_filename</code></strong>: This variable will contain the file path for the requested file.  It gets this value by taking the value of the current document root, taking into account both the <code>root</code> and <code>alias</code> directives, and the value of <code>$fastcgi_script_name</code>.  This is a very flexible way of assigning the <code>SCRIPT_FILENAME</code> parameter.</li>
<li><strong><code>$request_uri</code></strong>: The entire request as received from the client.  This includes the script, any additional path info, plus any query strings.</li>
<li><strong><code>$fastcgi_path_info</code></strong>: This variable contains additional path info that may be available after the script name in the request.  This value sometimes contains another location that the script to execute should know about.  This variable gets its value from the second captured regex group when using the <code>fastcgi_split_path_info</code> directive.</li>
<li><strong><code>$document_root</code></strong>: This variable contains the current document root value.  This will be set according to the <code>root</code> or <code>alias</code> directives.</li>
<li><strong><code>$uri</code></strong>: This variable contains the current URI with normalization applied.  Since certain directives that rewrite or internally redirect can have an impact on the URI, this variable will express those changes.</li>
</ul>

<p>As you can see, there are quite a few variables available to you when deciding how to set the FastCGI parameters.  Many of these are similar, but have some subtle differences that will impact the execution of your scripts.</p>

<h3 id="common-fastcgi-parameters">Common FastCGI Parameters</h3>

<p>FastCGI parameters represent key-value information that we wish to make available to the FastCGI processor we are sending the request to.  Not every application will need the same parameters, so you will often need to consult the app's documentation.</p>

<p>Some of these parameters are necessary for the processor to correctly identify the script to run.  Others are made available to the script, possibly modifying its behavior if it is configured to rely on the set parameters.</p>

<ul>
<li><strong><code>QUERY_STRING</code></strong>: This parameter should be set to any query string supplied by the client.  This will typically be key-value pairs supplied after a "?" in the URI.  Typically, this parameter is set to either the <code>$query_string</code> or <code>$args</code> variables, both of which should contain the same data.</li>
<li><strong><code>REQUEST_METHOD</code></strong>: This parameter indicates to the FastCGI processor which type of action was requested by the client.  This is one of the few parameters required to be set in order for the pass to function correctly.</li>
<li><strong><code>CONTENT_TYPE</code></strong>: If the request method set above is "POST", this parameter must be set.  It indicates the type of content that the FastCGI processor should expect.  This is almost always just set to the <code>$content_type</code> variable, which is set according to info in the original request.</li>
<li><strong><code>CONTENT_LENGTH</code></strong>: If the request method is "POST", this parameter must be set.  This indicates the content length.  This is almost always just set to <code>$content_length</code>, a variable that gets its value from information in the original client request.</li>
<li><strong><code>SCRIPT_NAME</code></strong>: This parameter is used to indicate the name of the main script that will be run.  This is an extremely important parameter that can be set in a variety of ways according to your needs.  Often, this is set to <code>$fastcgi_script_name</code>, which should be the request URI, the request URI with the <code>fastcgi_index</code> appended if it ends with a slash, or the first captured group if using <code>fastcgi_fix_path_info</code>.</li>
<li><strong><code>SCRIPT_FILENAME</code></strong>: This parameter specifies the actual location on disk of the script to run.  Because of its relation to the <code>SCRIPT_NAME</code> parameter, some guides suggest that you use <code>$document_root$fastcgi_script_name</code>.  Another alternative that has many advantages is to use <code>$request_filename</code>.</li>
<li><strong><code>REQUEST_URI</code></strong>: This should contain the full, unmodified request URI, complete with the script to run, additional path info, and any arguments.  Some applications prefer to parse this info themselves.  This parameter gives them the information necessary to do that.</li>
<li><strong><code>PATH_INFO</code></strong>: If <code>cgi.fix_pathinfo</code> is set to "1" in the PHP configuration file, this will contain any additional path information added after the script name.  This is often used to define a file argument that the script should act upon.  Setting <code>cgi.fix_pathinfo</code> to "1" can have security implications if the script requests are not sanitized through other means (we will discuss this later).  Sometimes this is set to the <code>$fastcgi_path_info</code> variable, which contains the second captured group from the <code>fastcgi_split_path_info</code> directive.  Other times, a temporary variable will need to be used as that value is sometimes clobbered by other processing.</li>
<li><strong><code>PATH_TRANSLATED</code></strong>: This parameter maps the path information contained within <code>PATH_INFO</code> into an actual filesystem path.  Usually, this will be set to something like <code>$document_root$fastcgi_path_info</code>, but sometimes the later variable must be replaced by the temporary saved variable as indicated above.</li>
</ul>

<h2 id="checking-requests-before-passing-to-fastcgi">Checking Requests Before Passing to FastCGI</h2>

<p>One very essential topic that we have not covered yet is how to safely pass dynamic requests to your application server.  Passing all requests to the backend application, regardless of their validity, is not only inefficient, but also dangerous.  It is possible for attackers to craft malicious requests in an attempt to get your server to run arbitrary code.</p>

<p>In order to address this issue, we should make sure that we are only sending legitimate requests to our FastCGI processors.  We can do this in a variety of ways depending on the needs of our particular set up and whether the FastCGI processor lives on the same system as our Nginx instance.</p>

<p>One basic rule that should inform how we design our configuration is that we should never allow any processing and interpretation of user files.  It is relatively easy for malicious users to embed valid code within seemingly innocent files, such as images.  Once a file like this is uploaded to our server, we must ensure that it never makes its way to our FastCGI processor.</p>

<p>The major issue we are trying to solve here is one that is actually specified in the CGI specification.  The spec allows for you to specify a script file to run, followed by additional path information that can be used by the script.  This model of execution allows users to request a URI that may look like a legitimate script, while the actual portion that will be executed will be earlier in the path.</p>

<p>Consider a request for <code>/test.jpg/index.php</code>.  If your configuration simply passes every request ending in <code>.php</code> to your processor without testing its legitimacy, the processor, if following the spec, will check for that location and execute it if possible.  If it <em>does not</em> find the file, it will then follow the spec and attempt to execute the <code>/test.jpg</code> file, marking <code>/index.php</code> as the additional path information for the script.  As you can see, this could allow for some very undesirable consequences when combined with the idea of user uploads.</p>

<p>There are a number of different ways to resolve this issue.  The easiest, if your application does not rely on this extra path info for processing, is to simply turn it off in your processor.  For PHP-FPM, you can turn this off in your <code>php.ini</code> file.  For an example, on Ubuntu systems, you could edit this file:</p>
<pre class="code-pre "><code langs="">sudo nano /etc/php5/fpm/php.ini
</code></pre>
<p>Simply search for the <code>cgi.fix_pathinfo</code> option, uncomment it and set it to "0" to disable this "feature":</p>
<pre class="code-pre "><code langs="">cgi.fix_pathinfo=0
</code></pre>
<p>Restart your PHP-FPM process to make the change:</p>
<pre class="code-pre "><code langs="">sudo service php5-fpm restart
</code></pre>
<p>This will cause PHP to only ever attempt execution on the last component of a path.  So in our example above, if the <code>/test.jpg/index.php</code> file did not exist, PHP would correctly error instead of trying to execute <code>/test.jpg</code>.</p>

<p>Another option, if our FastCGI processor is on the same machine as our Nginx instance, is to simply check the existence of the files on disk before passing them to the processor.  If the <code>/test.jgp/index.php</code> file doesn't exist, error out.  If it does, then send it to the backend for processing.  This will, in practice, result in much of the same behavior as we have above:</p>
<pre class="code-pre "><code class="code-highlight language-nginx">location ~ \.php$ {
        try_files $uri =404;

        . . .

}
</code></pre>
<p>If your application <em>does</em> rely on the path info behavior for correct interpretation, you can still safely allow this behavior by doing checks before deciding whether to send the request to the backend.</p>

<p>For instance, we could specifically match the directories where we allow untrusted uploads and ensure that they are not passed to our processor.  For instance, if our application's uploads directory is <code>/uploads/</code>, we could create a location block like this that matches before any regular expressions are evaluated:</p>
<pre class="code-pre "><code class="code-highlight language-nginx">location ^~ /uploads {
}
</code></pre>
<p>Inside, we can disable any kind of processing for PHP files:</p>
<pre class="code-pre "><code class="code-highlight language-nginx">location ^~ /uploads {
    location ~* \.php$ { return 403; }
}
</code></pre>
<p>The parent location will match for any request starting with <code>/uploads</code> and any request dealing with PHP files will return a 403 error instead of sending it along to a backend.</p>

<p>You can also use the <code>fastcgi_split_path_info</code> directive to manually define the portion of the request that should be interpreted as the script and the portion that should be defined as the extra path info using regular expressions.  This allows you to still rely on the path info functionality, but to define exactly what you consider the script and what you consider the path.</p>

<p>For instance, we can set up a location block that considers the first instance of a path component ending in <code>.php</code> as the script to run.  The rest will be considered the extra path info.  This will mean that in the instance of a request for <code>/test.jpg/index.php</code>, the entire path can be sent to the processor as the script name with no extra path info.</p>

<p>This location may look something like this:</p>
<pre class="code-pre "><code class="code-highlight language-nginx">location ~ [^/]\.php(/|$) {

    fastcgi_split_path_info ^(.+?\.php)(.*)$;
    set $orig_path $fastcgi_path_info;

    try_files $fastcgi_script_name =404;

    fastcgi_pass unix:/var/run/php5-fpm.sock;
    fastcgi_index index.php;
    include fastcgi_params;

    fastcgi_param SCRIPT_FILENAME $request_filename;
    fastcgi_param PATH_INFO $orig_path;
    fastcgi_param PATH_TRANSLATED $document_root$orig_path;
}
</code></pre>
<p>The block above should work for PHP configurations where <code>cgi.fix_pathinfo</code> is set to "1" to allow extra path info.  Here, our location block matches not only requests that end with <code>.php</code>, but also those with <code>.php</code> just before a slash (/) indicating an additional directory component follows.</p>

<p>Inside the block, the <code>fastcgi_split_path_info</code> directive defines two captured groups with regular expressions.  The first group matches the portion of the URI from the beginning to the first instance of <code>.php</code> and places that in the <code>$fastcgi_script_name</code> variable.  It then places any info from that point onward into a second captured group, which it stores in a variable called <code>$fastcgi_path_info</code>.</p>

<p>We use the <code>set</code> directive to store the value held in <code>$fastcgi_path_info</code> at this point into a variable called <code>$orig_path</code>.  This is because the <code>$fastcgi_path_info</code> variable will be wiped out in a moment by our <code>try_files</code> directive.</p>

<p>We test for the script name that we captured above using <code>try_files</code>.  This is a file operation that will ensure that the script that we are trying to run is on disk.  However, this also has a side-effect of clearing the <code>$fastcgi_path_info</code> variable.</p>

<p>After doing the conventional FastCGI pass, we set the <code>SCRIPT_FILENAME</code> as usual.  We also set the <code>PATH_INFO</code> to the value we offloaded into the <code>$orig_path</code> variable.  Although our <code>$fastcgi_path_info</code> was cleared, its original value is retained in this variable.  We also set the <code>PATH_TRANSLATED</code> parameter to map the extra path info to the location where it exists on disk.  We do this by combining the <code>$document_root</code> variable with the <code>$orig_path</code> variable.</p>

<p>This allows us to construct requests like <code>/index.php/users/view</code> so that our <code>/index.php</code> file can process information about the <code>/users/view</code> directory, while avoiding situations where <code>/test.jpg/index.php</code> will be run.  It will always set the script to the shortest component ending in <code>.php</code>, thus avoiding this issue.</p>

<p>We could even make this work with an alias directive if we need to change the location of our script files.  We would just have to account for this in both our location header and the <code>fastcgi_split_path_info</code> definition:</p>
<pre class="code-pre "><code class="code-highlight language-nginx">location ~ /test/.+[^/]\.php(/|$) {

    alias /var/www/html;

    fastcgi_split_path_info ^/test(.+?\.php)(.*)$;
    set $orig_path $fastcgi_path_info;

    try_files $fastcgi_script_name =404;

    fastcgi_pass unix:/var/run/php5-fpm.sock;
    fastcgi_index index.php;
    include fastcgi_params;

    fastcgi_param SCRIPT_FILENAME $request_filename;
    fastcgi_param PATH_INFO $orig_path;
    fastcgi_param PATH_TRANSLATED $document_root$orig_path;
}
</code></pre>
<p>These will allow you to run your applications that utilize the <code>PATH_INFO</code> parameter safely.  Remember, you'll have to change the <code>cgi.fix_pathinfo</code> option in your <code>php.ini</code> file to "1" to make this work correctly.  You may also have to turn off the <code>security.limit_extensions</code> in your <code>php-fpm.conf</code> file.</p>

<h2 id="conclusion">Conclusion</h2>

<p>Hopefully, by now you have a better understanding of Nginx's FastCGI proxying capabilities.  This ability allows Nginx to exercise its strengths in fast connection handling and serving static content, while offloading the responsibilities for dynamic content to better suited software. FastCGI allows Nginx to work with a great number of applications, in configurations that are performant and secure.</p>

    