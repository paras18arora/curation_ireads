<link rel='stylesheet' href='digital_ocean.css'><br> <br> 
      <h3 id="introduction">Introduction</h3>

<p>Naxsi is a third party Nginx module which provides web application firewall features. It brings additional security to your web server and protects you from various web attacks such as XSS and SQL injections.</p>

<p>Naxsi is flexible and powerful. You can use readily available rules for popular web applications such as WordPress. At the same time, you can also create your own rules and fine tune them by using Naxsi's learning mode.</p>

<p>Naxsi is similar to <a href="https://indiareads/community/tutorials/how-to-set-up-modsecurity-with-apache-on-ubuntu-14-04-and-debian-8">ModSecurity for Apache</a>. Thus, if you are already acquainted with ModSecurity and/or seek similar functionality for Nginx, Naxsi will certainly be of interest to you. However, you may not find all of ModSecurity's features in Naxsi.</p>

<p>This tutorial shows you how to install Naxsi, understand the rules, create a whitelist, and where to find rules already written for commonly-used web applications.</p>

<h2 id="prerequisites">Prerequisites</h2>

<p>Before following this tutorial, please make sure you complete the following prerequisites:</p>

<ul>
<li>An Ubuntu 14.04 Droplet</li>
<li>A non-root sudo user. Check out <a href="https://indiareads/community/tutorials/initial-server-setup-with-ubuntu-14-04">Initial Server Setup with Ubuntu 14.04</a> for details.</li>
</ul>

<p>Except otherwise noted, all of the commands that require root privileges in this tutorial should be run as a non-root user with sudo privileges.</p>

<h2 id="step-1-—-installing-naxsi">Step 1 — Installing Naxsi</h2>

<p>To install Naxsi you will have to install an Nginx server compiled with it. For this purpose you will need the package <code>nginx-naxsi</code>. You can install it in the usual Ubuntu way with the <code>apt-get</code> command:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo apt-get update
</li><li class="line" prefix="$">sudo apt-get install nginx-naxsi
</li></ul></code></pre>
<p>This will install Naxsi along with Nginx and all of its dependencies. It will also make sure the service starts and stops automatically on the Droplet.</p>

<span class="note"><p>
<strong>Note:</strong> If you already have Nginx installed without Naxsi, you will need to replace the package <code>nginx-core</code>, or another flavor of Nginx you might have, with the package <code>nginx-naxsi</code>. The other Nginx packages do not support loadable modules, and you cannot just load Naxsi into an existing Nginx server.</p>

<p>In most cases replacing <code>nginx-core</code> with <code>nginx-naxsi</code> is not a problem, and you can continue using your previous configuration. Still, it's always a good idea with such upgrade to create a backup of your existing <code>/etc/nginx/</code> directory first. After that, follow the instructions for a new installation, and simply confirm you agree to remove the existing Nginx package on your system.<br /></p></span>

<p>The default installation of Nginx provides a basic, working Nginx environment, which is sufficient for getting acquainted with Naxsi. We will not spend time customizing Nginx, but instead we will go straight to configuring Naxsi. However, if you have no experience with Nginx it is a good idea to check <a href="https://indiareads/community/tutorials/how-to-install-nginx-on-ubuntu-14-04-lts">How To Install Nginx on Ubuntu 14.04 LTS</a> and its related articles, especially <a href="https://indiareads/community/tutorials/how-to-set-up-nginx-server-blocks-virtual-hosts-on-ubuntu-14-04-lts">How To Set Up Nginx Server Blocks (Virtual Hosts) on Ubuntu 14.04 LTS</a>.</p>

<h2 id="step-2-—-enabling-naxsi">Step 2 — Enabling Naxsi</h2>

<p>First, to enable Naxsi we have to load its core rules found in the file <code>/etc/nginx/naxsi_core.rules</code>. This file contains generic signatures for detecting malicious attacks. We'll discuss these rules in greater details later. For now, we'll just include the rules in Nginx's main configuration file <code>/etc/nginx/nginx.conf</code> in the HTTP listener part. So, open the latter file for editing with nano:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo nano /etc/nginx/nginx.conf
</li></ul></code></pre>
<p>Then find the <code>http</code> section and uncomment the include part for Naxsi's rules by removing the <code>#</code> character at the beginning of the line. It should now look like this:</p>
<div class="code-label " title="/etc/nginx/nginx.conf">/etc/nginx/nginx.conf</div><pre class="code-pre "><code langs="">http {
...
        # nginx-naxsi config
        ##
        # Uncomment it if you installed nginx-naxsi
        ##

        <span class="highlight">include /etc/nginx/naxsi_core.rules;</span>
...
</code></pre>
<p>Save the file and exit the editor. </p>

<p>Second, we have to enable the previous rules and configure some basic options for Naxsi. By default, the basic Naxsi configuration is found in the file <code>/etc/nginx/naxsi.rules</code>. Open this file:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo nano /etc/nginx/naxsi.rules
</li></ul></code></pre>
<p>Change only the value for <code>DeniedUrl</code> to an error file that already exists by default, and leave the rest unchanged:</p>
<div class="code-label " title="/etc/nginx/naxsi.rules">/etc/nginx/naxsi.rules</div><pre class="code-pre "><code langs=""># Sample rules file for default vhost.
LearningMode;
SecRulesEnabled;
#SecRulesDisabled;
<span class="highlight">DeniedUrl "/50x.html";</span>

## check rules
CheckRule "$SQL >= 8" BLOCK;
CheckRule "$RFI >= 8" BLOCK;
CheckRule "$TRAVERSAL >= 4" BLOCK;
CheckRule "$EVADE >= 4" BLOCK;
CheckRule "$XSS >= 8" BLOCK;
</code></pre>
<p>Save the file and exit.</p>

<p>Here are the configuration directives from above with their meaning:</p>

<ul>
<li><code>LearningMode</code> - Start Naxsi in learning mode. This means that no request will actually be blocked. Only security exceptions will be raised in the Nginx error log. Such a non-blocking initial behavior is important because the default rules are rather aggressive. Later, based on these exceptions, we will create whitelist for legitimate traffic. </li>
<li><code>SecRulesEnabled</code> - Enable Naxsi for a server block / location. Similarly, you can disable Naxsi for a site or part of a site by uncommenting <code>SecRulesDisabled</code>.</li>
<li><code>DeniedUrl</code> - URL to which denied requests will be sent internally. This is the only setting you should change. You can use the readily available <code>50x.html</code> error page found inside the default document root (<code>/usr/share/nginx/html/50x.html</code>), or you can create your own custom error page.</li>
<li><code>CheckRule</code> - Set the threshold for the different counters. Once this threshold is passed (e.g. 8 points for the SQL counter) the request will be blocked. To make these rules more aggressive, decrease their values and vice versa.</li>
</ul>

<p>The file <code>naxsi.rules</code> has to be loaded on a per location basis for a server block. Let's load it for the root location (<code>/</code>) of the default server block. First open the server block's configuration file <code>/etc/nginx/sites-enabled/default</code>:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo nano /etc/nginx/sites-enabled/default
</li></ul></code></pre>
<p>Then, find the root location <code>/</code> and make sure that it looks like this:</p>
<pre class="code-pre "><code langs="">    location / {
            # First attempt to serve request as file, then
            # as directory, then fall back to displaying a 404.
            try_files $uri $uri/ =404;
            # Uncomment to enable naxsi on this location
            <span class="highlight">include /etc/nginx/naxsi.rules;</span>
    }
</code></pre>
<p><span class="warning"><strong>Warning:</strong> Make sure to add a semicolon at the end of the <code>include</code> statement for <code>naxsi.rules</code> because there is no such by default. Thus, if you only uncomment the statement, there will be a syntax error in the configuration.<br /></span></p>

<p>Once you have made the above changes you can reload Nginx for the changes to take effect:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo service nginx reload
</li></ul></code></pre>
<p>The next step explains how to check if the changes have been successful and how to read the logs. </p>

<h2 id="step-3-—-checking-the-logs">Step 3 — Checking the Logs</h2>

<p>To make sure Naxsi works, even though still in learning mode, let's access a URL that should throw an exception and watch the error log for the exception.</p>

<p>We'll see later how this rule works exactly. For now, tail Nginx's error log to find the exception (the <code>-f</code> option keeps the output open and appends new content to it:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo tail -f /var/log/nginx/error.log
</li></ul></code></pre>
<p>Try to access your Droplet at the URL <code>http://<span class="highlight">Your_Droplet_IP</span>/index.html?asd=----</code>. This should trigger a Naxsi security exception because of the dashes, which are used for comments in SQL, and thus are considered parts of SQL injections.</p>

<p>In the output of <code>sudo tail -f /var/log/nginx/error.log</code>, you should now see the following new content:</p>
<pre class="code-pre "><code langs=""><div class="secondary-code-label " title="Output of nginx's error log">Output of nginx's error log</div>2015/11/14 03:58:35 [error] 4088#0: *1 NAXSI_FMT: ip=X.X.X.X&server=Y.Y.Y.Y&uri=/index.html&learning=1&total_processed=24&total_blocked=1&<span class="highlight">zone0=ARGS&id0=1007&var_name0=asd</span>, client: X.X.X.X, server: localhost, request: "GET /index.html?asd=---- HTTP/1.1", host: "Y.Y.Y.Y"
</code></pre>
<p>The most important part of the above line is highlighted: <code>zone0=ARGS&id0=1007&var_name0=asd</code>. It gives you the zone (the part of the request), the id of the triggered rule, and the variable name of the suspicious request. </p>

<p>Furthermore, <code>X.X.X.X</code> is your local computer's IP, and <code>Y.Y.Y.Y</code> is the IP of your Droplet. The URI also contains the filename of the request (<code>index.htm</code>), the fact that Naxsi is still working in learning mode (<code>learning=1</code>), and the total number of all processed requests (<code>total_processed=24</code>).</p>

<p>Also, right after the above line, there should follow a message about the redirect to the <code>DeniedUrl</code>:</p>
<pre class="code-pre "><code langs=""><div class="secondary-code-label " title="Output of nginx's error log">Output of nginx's error log</div>2015/11/14 03:58:35 [error] 4088#0: *1 rewrite or internal redirection cycle while internally redirecting to "/50x.html" while sending response to client, client: X.X.X.X, server: localhost, request: "GET /favicon.ico HTTP/1.1", host: "Y.Y.Y.Y", referrer: "http://Y.Y.Y.Y/index.html?asd=----"
</code></pre>
<p>When Naxsi is in learning mode, this redirect will only show in the logs but will not actually happen. </p>

<p>Press <code>CTRL-C</code> to exit <code>tail</code> and stop the output of the error log file.</p>

<p>Later on, we'll learn more about Naxsi's rules, and then it'll be important to have this basic understanding of the logs.</p>

<h2 id="step-4-—-configuring-naxsi-rules">Step 4 — Configuring Naxsi Rules</h2>

<p>The most important part of Naxsi's configuration is its rules. There are two types of rules — main rules and basic rules. The main rules (identified by <code>MainRule</code>) are applied globally for the server, and thus are part of the <code>http</code> block of the main Nginx's configuration. They contain generic signatures for detecting malicious activities. </p>

<p>The basic rules (identified by <code>BasicRule</code>) are used mainly for whitelisting false positive signatures and rules. They are applied per location and thus should be part of the server block (vhost) configuration.</p>

<p>Let's start with the main rules, and take a look at the default ones provided by the <code>nginx-naxsi</code> package in the file <code>/etc/nginx/naxsi_core.rules</code>. Here is a sample line:</p>
<div class="code-label " title="/etc/nginx/naxsi_core.rules">/etc/nginx/naxsi_core.rules</div><pre class="code-pre "><code langs="">...
MainRule "str:--" "msg:mysql comment (--)" "mz:BODY|URL|ARGS|$HEADERS_VAR:Cookie" "s:$SQL:4" id:1007;
...
</code></pre>
<p>From the rule above we can outline the following parts, which are universal and present in every rule:</p>

<ul>
<li><code>MainRule</code> is the directive to begin every rule with. Similarly, every rule ends with the rule's id number. </li>
<li><code>str:</code> is found in the second part of the rule. If it is <code>str:</code> it means that the signature will be a plain string, as per the example above. Regular expressions can be also matched with the directive <code>rx:</code>.</li>
<li><code>msg:</code> gives some clarification about the rule.</li>
<li><code>mz:</code> stands for match zone, or which part of the request will be inspected. This could be the body, the URL, the arguments, etc.</li>
<li><code>s:</code> determines the score which will be assigned when the signature is found. Scores are added to different counters such as <code>SQL</code> (SQL attacks), <code>RFI</code> (remote file inclusion attacks), etc.<br /></li>
</ul>

<p>Essentially, the above rule (<code>id 1007</code>) with comment <code>mysql comments</code> means that if the string <code>--</code> is found in any part of a request (body, arguments, etc.), 4 points will be added to the SQL counter.</p>

<p>If we go back to the example URI (<code>http://<span class="highlight">Your_Droplet_IP</span>/index.html?asd=----</code>) that triggered the SQL exception in the log, you will notice that to trigger rule 1007, we needed 2 pairs of dashes (<code>--</code>). This is because for each pair we get 4 points and the SQL chain needs 8 points to block a request. Thus, only one pair of dashes would not be problematic, and in most cases legitimate traffic will not suffer.</p>

<p>One special rule directive is <code>negative</code>. It applies scores if the signature is not matched, i.e. you suspect malicious activity when something in the request is missing. </p>

<p>For example, let's look at the rule with <code>id 1402</code> from the same file <code>/etc/nginx/naxsi_core.rules</code>:</p>
<div class="code-label " title="/etc/nginx/naxsi_core.rules">/etc/nginx/naxsi_core.rules</div><pre class="code-pre "><code langs="">...
MainRule negative "rx:multipart/form-data|application/x-www-form-urlencoded" "msg:Content is neither mulipart/x-www-form.." "mz:$HEADERS_VAR:Content-type" "s:$EVADE:4" id:1402;
...
</code></pre>
<p>The above rule means that 4 points will be added to the EVADE counter if the <code>Content-type</code>  request header has neither <code>multipart/form-data</code>, nor <code>application/x-www-form-urlencoded</code> in it. This rule is also an example of how regular expressions (<code>rx:</code>) can be used for the signature description.</p>

<h2 id="step-5-—-whitelisting-rules">Step 5 — Whitelisting Rules</h2>

<p>The default Naxsi rules will almost certainly block some legitimate traffic on your site, especially if you have a complex web application supporting a wide variety of user interactions. That's why there are whitelists to resolve such problems.</p>

<p>Whitelists are created with the second type of rules, Naxsi's basic rules. With a basic rule you can whitelist either a whole rule or parts of it. </p>

<p>To demonstrate how basic rules work, let's go back to the SQL comment rule (id 1007). Imagine that you have a file with two dashes in the filename, e.g. <code>some--file.html</code> on your site. With rule 1007 in place, this file will increase the SQL counter with 4 points. This filename alone and the resulting score is not sufficient to block a request, but it is still a false positive which could cause problems. For example, if we also have an argument with two dashes in it, then the request will trigger rule 1007.</p>

<p>To test it, tail the error log like before:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo tail -f /var/log/nginx/error.log
</li></ul></code></pre>
<p>Try accessing <code>http://<span class="highlight">Your_Droplet_IP</span>/some--file.html?asd=--</code>. You don't need to have this file on your website for the test.</p>

<p>You should see a familiar exception similar to this one in the output of the error log:</p>
<pre class="code-pre "><code langs=""><div class="secondary-code-label " title="Output of nginx's error log">Output of nginx's error log</div>2015/11/14 14:43:36 [error] 5182#0: *10 NAXSI_FMT: ip=X.X.X.X&server=Y.Y.Y.Y&uri=/some--file.html&learning=1&total_processed=10&total_blocked=6&zone0=URL&id0=1007&var_name0=&zone1=ARGS&id1=1007&var_name1=asd, client: X.X.X.X, server: localhost, request: "GET /some--file.html?asd=-- HTTP/1.1", host: "Y.Y.Y.Y"
</code></pre>
<p>Press <code>CTRL-C</code> to stop showing the error log output.</p>

<p>To address this false positive trigger we'll need a whitelist which looks like this one:</p>
<pre class="code-pre "><code langs="">BasicRule <span class="highlight">wl</span>:1007 "mz:URL";
</code></pre>
<p>The important keyword is <code>wl</code> for whitelist, followed by the rule ID. To be more precise what we are whitelisting, we have also specified the match zone — the URL.</p>

<p>To apply this whitelist, first create a new file for whitelists:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo nano /etc/nginx/naxsi_whitelist.rules
</li></ul></code></pre>
<p>Then, paste the rule into the file:</p>
<div class="code-label " title="/etc/nginx/naxsi_whitelist.rules">/etc/nginx/naxsi_whitelist.rules</div><pre class="code-pre "><code langs="">BasicRule wl:1007 "mz:URL";
</code></pre>
<p>If you have other whitelists, they can go in this file too, each one on a new row.</p>

<p>The file with whitelists has to be included in your server block. To include it in the default server block, use again nano:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo nano /etc/nginx/sites-enabled/default
</li></ul></code></pre>
<p>Then add the new include right after the previous one for Naxsi like this:</p>
<div class="code-label " title="/etc/nginx/sites-enabled/default">/etc/nginx/sites-enabled/default</div><pre class="code-pre "><code langs="">
        location / {
                # First attempt to serve request as file, then
                # as directory, then fall back to displaying a 404.
                try_files $uri $uri/ =404;
                # Uncomment to enable naxsi on this location
                include /etc/nginx/naxsi.rules;
                <span class="highlight">include /etc/nginx/naxsi_whitelist.rules;</span>
        }
</code></pre>
<p>For this change to take effect, reload Nginx:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo service nginx reload
</li></ul></code></pre>
<p>Now if you try again the same request in your browser to <code><span class="highlight">Your_Droplet_IP</span>/some--file.html?asd=--</code> only the <code>asd</code> parameter equaling two dashes will trigger 4 points for the SQL counter, but the uncommon filename will not. Thus, you will not see this request in the error log as an exception.</p>

<p>Writing all the necessary whitelists can be a tedious task and a science of its own. That's why at the beginning you can use readily available <a href="https://github.com/nbs-system/naxsi-rules">Naxsi whitelists</a>. There are such for most popular web applications. You just have to download them and include them in the server block like we just did.</p>

<p>Once you have made sure you don't see any exceptions for legitimate requests in the error log, you can disable the learning mode of Naxsi. For this purpose open the file <code>/etc/nginx/naxsi.rules</code> with nano:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo nano /etc/nginx/naxsi.rules
</li></ul></code></pre>
<p>Comment out the <code>LearningMode</code> directive by adding the <code>#</code> character in front of it like this:</p>
<div class="code-label " title="/etc/nginx/naxsi.rules">/etc/nginx/naxsi.rules</div><pre class="code-pre "><code langs="">...
<span class="highlight">#</span>LearningMode;
SecRulesEnabled;
#SecRulesDisabled;
...
</code></pre>
<p>Finally, reload Nginx for the change to take effect:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo service nginx reload
</li></ul></code></pre>
<p>Now, Naxsi will block any suspicious requests, and your site will be more secure. </p>

<h2 id="conclusion">Conclusion</h2>

<p>That's how easy it is to have a web application firewall with Nginx and Naxsi. That's enough for a beginning and hopefully you will be interested in learning more of what the powerful Naxsi module has to offer. Now you can make your Nginx server not only fast, but also secure.</p>

    