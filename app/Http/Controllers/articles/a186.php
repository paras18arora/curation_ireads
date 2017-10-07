<link rel='stylesheet' href='digital_ocean.css'><br> <br> 
      <h3 id="introduction">Introduction</h3>

<p>When operating a web server, it is important to implement security measures to protect your site and users.  Protecting your web sites and applications with firewall policies and restricting access to certain areas with password authentication is a great starting point to securing your system.  However, any publicly accessible password prompt is likely to attract brute force attempts from malicious users and bots.</p>

<p>Setting up <code>fail2ban</code> can help alleviate this problem.  When users repeatedly fail to authenticate to a service (or engage in other suspicious activity), <code>fail2ban</code> can issue a temporary bans on the offending IP address by dynamically modifying the running firewall policy.  Each <code>fail2ban</code> "jail" operates by checking the logs written by a service for patterns which indicate failed attempts.  Setting up <code>fail2ban</code> to monitor Nginx logs is fairly easy using the some of included configuration filters and some we will create ourselves.</p>

<p>In this guide, we will demonstrate how to install <code>fail2ban</code> and configure it to monitor your Nginx logs for intrusion attempts.  We will use an Ubuntu 14.04 server.</p>

<h2 id="prerequisites">Prerequisites</h2>

<p>Before you begin, you should have an Ubuntu 14.04 server set up with a non-root account.  This account should be configured with <code>sudo</code> privileges in order to issue administrative commands.  To learn how to set up a user with <code>sudo</code> privileges, follow our <a href="https://indiareads/community/tutorials/initial-server-setup-with-ubuntu-14-04">initial server setup guide for Ubuntu 14.04</a>.</p>

<h2 id="installing-nginx-and-configuring-password-authentication">Installing Nginx and Configuring Password Authentication</h2>

<p>If you are interested in protecting your Nginx server with <code>fail2ban</code>, you might already have a server set up and running.  If not, you can install Nginx from Ubuntu's default repositories using <code>apt</code>.</p>

<p>Update the local package index and install by typing:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo apt-get update
</li><li class="line" prefix="$">sudo apt-get install nginx
</li></ul></code></pre>
<p>The <code>fail2ban</code> service is useful for protecting login entry points.  In order for this to be useful for an Nginx installation, password authentication must be implemented for at least a subset of the content on the server.  You can follow <a href="https://indiareads/community/tutorials/how-to-set-up-password-authentication-with-nginx-on-ubuntu-14-04">this guide</a> to configure password protection for your Nginx server.</p>

<h2 id="install-fail2ban">Install Fail2Ban</h2>

<p>Once your Nginx server is running and password authentication is enabled, you can go ahead and install <code>fail2ban</code> (we include another repository re-fetch here in case you already had Nginx set up in the previous steps):</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo apt-get update
</li><li class="line" prefix="$">sudo apt-get install fail2ban
</li></ul></code></pre>
<p>This will install the software.  By default, <code>fail2ban</code> is configured to only ban failed SSH login attempts.  We need to enable some rules that will configure it to check our Nginx logs for patterns that indicate malicious activity.</p>

<h2 id="adjusting-the-general-settings-within-fail2ban">Adjusting the General Settings within Fail2Ban</h2>

<p>To get started, we need to adjust the configuration file that <code>fail2ban</code> uses to determine what application logs to monitor and what actions to take when offending entries are found.  The supplied <code>/etc/fail2ban/jail.conf</code> file is the main provided resource for this.</p>

<p>To make modifications, we need to copy this file to <code>/etc/fail2ban/jail.local</code>.  This will prevent our changes from being overwritten if a package update provides a new default file:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo cp /etc/fail2ban/jail.conf /etc/fail2ban/jail.local
</li></ul></code></pre>
<p>Open the newly copied file so that we can set up our Nginx log monitoring:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo nano /etc/fail2ban/jail.local
</li></ul></code></pre>
<h3 id="changing-defaults">Changing Defaults</h3>

<p>We should start by evaluating the defaults set within the file to see if they suit our needs.  These will be found under the <code>[DEFAULT]</code> section within the file.  These items set the general policy and can each be overridden in specific jails.</p>

<p>One of the first items to look at is the list of clients that are not subject to the <code>fail2ban</code> policies.  This is set by the <code>ignoreip</code> directive.  It is sometimes a good idea to add your own IP address or network to the list of exceptions to avoid locking yourself out.  This is less of an issue with web server logins though if you are able to maintain shell access, since you can always manually reverse the ban.  You can add additional IP addresses or networks delimited by a space, to the existing list:</p>
<div class="code-label " title="/etc/fail2ban/jail.local">/etc/fail2ban/jail.local</div><pre class="code-pre "><code langs="">[DEFAULT]

. . .
ignoreip = 127.0.0.1/8 <span class="highlight">your_home_IP</span>
</code></pre>
<p>Another item that you may want to adjust is the <code>bantime</code>, which controls how many seconds an offending member is banned for.  It is ideal to set this to a long enough time to be disruptive to a malicious actor's efforts, while short enough to allow legitimate users to rectify mistakes.  By default, this is set to 600 seconds (10 minutes).  Increase or decrease this value as you see fit:</p>
<div class="code-label " title="/etc/fail2ban/jail.local">/etc/fail2ban/jail.local</div><pre class="code-pre "><code langs="">[DEFAULT]

. . .
bantime = <span class="highlight">3600</span>
</code></pre>
<p>The next two items determine the scope of log lines used to determine an offending client.  The <code>findtime</code> specifies an amount of time in seconds and the <code>maxretry</code> directive indicates the number of attempts to be tolerated within that time.  If a client makes more than <code>maxretry</code> attempts within the amount of time set by <code>findtime</code>, they will be banned:</p>
<div class="code-label " title="/etc/fail2ban/jail.local">/etc/fail2ban/jail.local</div><pre class="code-pre "><code langs="">[DEFAULT]

. . .
findtime = <span class="highlight">3600</span>   # These lines combine to ban clients that fail
maxretry = <span class="highlight">6</span>      # to authenticate 6 times within a half hour.
</code></pre>
<h3 id="setting-up-mail-notifications-optional">Setting Up Mail Notifications (Optional)</h3>

<p>You can enable email notifications if you wish to receive mail whenever a ban takes place.  To do so, you will have to first set up an MTA on your server so that it can send out email.  To learn how to use Postfix for this task, follow <a href="https://indiareads/community/tutorials/how-to-install-and-configure-postfix-as-a-send-only-smtp-server-on-ubuntu-14-04">this guide</a>.</p>

<p>Once you have your MTA set up, you will have to adjust some additional settings within the <code>[DEFAULT]</code> section of the <code>/etc/fail2ban/jail.local</code> file.  Start by setting the <code>mta</code> directive.  If you set up Postfix, like the above tutorial demonstrates, change this value to "mail":</p>
<div class="code-label " title="/etc/fail2ban/jail.local">/etc/fail2ban/jail.local</div><pre class="code-pre "><code langs="">[DEFAULT]

. . .
mta = <span class="highlight">mail</span>
</code></pre>
<p>You need to select the email address that will be sent notifications.  Modify the <code>destemail</code> directive with this value.  The <code>sendername</code> directive can be used to modify the "Sender" field in the notification emails:</p>
<div class="code-label " title="/etc/fail2ban/jail.local">/etc/fail2ban/jail.local</div><pre class="code-pre "><code langs="">[DEFAULT]

. . .
destemail = <span class="highlight">youraccount@email.com</span>
sendername = <span class="highlight">Fail2BanAlerts</span>
</code></pre>
<p>In <code>fail2ban</code> parlance, an "action" is the procedure followed when a client fails authentication too many times.  The default action (called <code>action_</code>) is to simply ban the IP address from the port in question.  However, there are two other pre-made actions that can be used if you have mail set up.</p>

<p>You can use the <code>action_mw</code> action to ban the client and send an email notification to your configured account with a "whois" report on the offending address.  You could also use the <code>action_mwl</code> action, which does the same thing, but also includes the offending log lines that triggered the ban:</p>
<div class="code-label " title="/etc/fail2ban/jail.local">/etc/fail2ban/jail.local</div><pre class="code-pre "><code langs="">[DEFAULT]

. . .
action = %(<span class="highlight">action_mwl</span>)s
</code></pre>
<h2 id="configuring-fail2ban-to-monitor-nginx-logs">Configuring Fail2Ban to Monitor Nginx Logs</h2>

<p>Now that you have some of the general <code>fail2ban</code> settings in place, we can concentrate on enabling some Nginx-specific jails that will monitor our web server logs for specific behavior patterns.</p>

<p>Each jail within the configuration file is marked by a header containing the jail name in square brackets (every section but the <code>[DEFAULT]</code> section indicates a specific jail's configuration).  By default, only the <code>[ssh]</code> jail is enabled.</p>

<p>To enable log monitoring for Nginx login attempts, we will enable the <code>[nginx-http-auth]</code> jail.  Edit the <code>enabled</code> directive within this section so that it reads "true":</p>
<div class="code-label " title="/etc/fail2ban/jail.local">/etc/fail2ban/jail.local</div><pre class="code-pre "><code langs="">[nginx-http-auth]

enabled  = <span class="highlight">true</span>
filter   = nginx-http-auth
port     = http,https
logpath  = /var/log/nginx/error.log
. . .
</code></pre>
<p>This is the only Nginx-specific jail included with Ubuntu's <code>fail2ban</code> package.  However, we can create our own jails to add additional functionality.  The inspiration for and some of the implementation details of these additional jails came from <a href="http://www.fail2ban.org/wiki/index.php/NginX">here</a> and <a href="http://snippets.aktagon.com/snippets/554-how-to-secure-an-nginx-server-with-fail2ban">here</a>.</p>

<p>We can create an <code>[nginx-noscript]</code> jail to ban clients that are searching for scripts on the website to execute and exploit.  If you do not use PHP or any other language in conjunction with your web server, you can add this jail to ban those who request these types of resources:</p>
<div class="code-label " title="/etc/fail2ban/jail.local">/etc/fail2ban/jail.local</div><pre class="code-pre "><code langs="">[nginx-noscript]

enabled  = true
port     = http,https
filter   = nginx-noscript
logpath  = /var/log/nginx/access.log
maxretry = 6
. . .
</code></pre>
<p>We can add a section called <code>[nginx-badbots]</code> to stop some known malicious bot request patterns:</p>
<div class="code-label " title="/etc/fail2ban/jail.local">/etc/fail2ban/jail.local</div><pre class="code-pre "><code langs="">[nginx-badbots]

enabled  = true
port     = http,https
filter   = nginx-badbots
logpath  = /var/log/nginx/access.log
maxretry = 2
</code></pre>
<p>If you do not use Nginx to provide access to web content within users' home directories, you can ban users who request these resources by adding an <code>[nginx-nohome]</code> jail:</p>
<div class="code-label " title="/etc/fail2ban/jail.local">/etc/fail2ban/jail.local</div><pre class="code-pre "><code langs="">[nginx-nohome]

enabled  = true
port     = http,https
filter   = nginx-nohome
logpath  = /var/log/nginx/access.log
maxretry = 2
</code></pre>
<p>We should ban clients attempting to use our Nginx server as an open proxy.  We can add an <code>[nginx-noproxy]</code> jail to match these requests:</p>
<div class="code-label " title="/etc/fail2ban/jail.local">/etc/fail2ban/jail.local</div><pre class="code-pre "><code langs="">[nginx-noproxy]

enabled  = true
port     = http,https
filter   = nginx-noproxy
logpath  = /var/log/nginx/access.log
maxretry = 2
</code></pre>
<p>When you are finished making the modifications you need, save and close the file.  We now have to add the filters for the jails that we have created.</p>

<h2 id="adding-the-filters-for-additional-nginx-jails">Adding the Filters for Additional Nginx Jails</h2>

<p>We've updated the <code>/etc/fail2ban/jail.local</code> file with some additional jail specifications to match and ban a larger range of bad behavior.  We need to create the filter files for the jails we've created.  These filter files will specify the patterns to look for within the Nginx logs.</p>

<p>Begin by changing to the filters directory:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">cd /etc/fail2ban/filter.d
</li></ul></code></pre>
<p>We actually want to start by adjusting the pre-supplied Nginx authentication filter to match an additional failed login log pattern.  Open the file for editing:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo nano nginx-http-auth.conf
</li></ul></code></pre>
<p>Below the <code>failregex</code> specification, add an additional pattern.  This will match lines where the user has entered no username or password:</p>
<div class="code-label " title="/etc/fail2ban/filter.d/nginx-http-auth.conf">/etc/fail2ban/filter.d/nginx-http-auth.conf</div><pre class="code-pre "><code langs="">[Definition]


failregex = ^ \[error\] \d+#\d+: \*\d+ user "\S+":? (password mismatch|was not found in ".*"), client: <HOST>, server: \S+, request: "\S+ \S+ HTTP/\d+\.\d+", host: "\S+"\s*$
            <span class="highlight">^ \[error\] \d+#\d+: \*\d+ no user/password was provided for basic authentication, client: <HOST>, server: \S+, request: "\S+ \S+ HTTP/\d+\.\d+", host: "\S+"\s*$</span>

ignoreregex =
</code></pre>
<p>Save and close the file when you are finished.</p>

<p>Next, we can copy the <code>apache-badbots.conf</code> file to use with Nginx.  We can use this file as-is, but we will copy it to a new name for clarity.  This matches how we referenced the filter within the jail configuration:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo cp apache-badbots.conf nginx-badbots.conf
</li></ul></code></pre>
<p>Next, we'll create a filter for our <code>[nginx-noscript]</code> jail:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo nano nginx-noscript.conf
</li></ul></code></pre>
<p>Paste the following definition inside.  Feel free to adjust the script suffixes to remove language files that your server uses legitimately or to add additional suffixes:</p>
<div class="code-label " title="/etc/fail2ban/filter.d/nginx-noscript.conf">/etc/fail2ban/filter.d/nginx-noscript.conf</div><pre class="code-pre "><code langs="">[Definition]

failregex = ^<HOST> -.*GET.*(\.php|\.asp|\.exe|\.pl|\.cgi|\.scgi)

ignoreregex =
</code></pre>
<p>Save and close the file.</p>

<p>Next, create a filter for the <code>[nginx-nohome]</code> jail:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo nano nginx-nohome.conf
</li></ul></code></pre>
<p>Place the following filter information in the file:</p>
<div class="code-label " title="/etc/fail2ban/filter.d/nginx-nohome.conf">/etc/fail2ban/filter.d/nginx-nohome.conf</div><pre class="code-pre "><code langs="">[Definition]

failregex = ^<HOST> -.*GET .*/~.*

ignoreregex =
</code></pre>
<p>Save and close the file when finished.</p>

<p>Finally, we can create the filter for the <code>[nginx-noproxy]</code> jail:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo nano nginx-noproxy.conf
</li></ul></code></pre>
<p>This filter definition will match attempts to use your server as a proxy:</p>
<div class="code-label " title="/etc/fail2ban/filter.d/nginx-noproxy.conf">/etc/fail2ban/filter.d/nginx-noproxy.conf</div><pre class="code-pre "><code langs="">[Definition]

failregex = ^<HOST> -.*GET http.*

ignoreregex =
</code></pre>
<p>Save and close the file when you are finished.</p>

<h2 id="activating-your-nginx-jails">Activating your Nginx Jails</h2>

<p>To implement your configuration changes, you'll need to restart the <code>fail2ban</code> service.  You can do that by typing:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo service fail2ban restart
</li></ul></code></pre>
<p>The service should restart, implementing the different banning policies you've configured.</p>

<h2 id="getting-info-about-enabled-jails">Getting Info About Enabled Jails</h2>

<p>You can see all of your enabled jails by using the <code>fail2ban-client</code> command:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo fail2ban-client status
</li></ul></code></pre>
<p>You should see a list of all of the jails you enabled:</p>
<pre class="code-pre "><code langs=""><div class="secondary-code-label " title="Output">Output</div>Status
|- Number of jail:      6
`- Jail list:           nginx-noproxy, nginx-noscript, nginx-nohome, nginx-http-auth, nginx-badbots, ssh
</code></pre>
<p>You can look at <code>iptables</code> to see that <code>fail2ban</code> has modified your firewall rules to create a framework for banning clients.  Even with no previous firewall rules, you would now have a framework enabled that allows <code>fail2ban</code> to selectively ban clients by adding them to purpose-built chains:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo iptables -S
</li></ul></code></pre><pre class="code-pre "><code langs=""><div class="secondary-code-label " title="Output">Output</div>-P INPUT ACCEPT
-P FORWARD ACCEPT
-P OUTPUT ACCEPT
-N fail2ban-nginx-badbots
-N fail2ban-nginx-http-auth
-N fail2ban-nginx-nohome
-N fail2ban-nginx-noproxy
-N fail2ban-nginx-noscript
-N fail2ban-ssh
-A INPUT -p tcp -m multiport --dports 80,443 -j fail2ban-nginx-noproxy
-A INPUT -p tcp -m multiport --dports 80,443 -j fail2ban-nginx-nohome
-A INPUT -p tcp -m multiport --dports 80,443 -j fail2ban-nginx-badbots
-A INPUT -p tcp -m multiport --dports 80,443 -j fail2ban-nginx-noscript
-A INPUT -p tcp -m multiport --dports 80,443 -j fail2ban-nginx-http-auth
-A INPUT -p tcp -m multiport --dports 22 -j fail2ban-ssh
-A fail2ban-nginx-badbots -j RETURN
-A fail2ban-nginx-http-auth -j RETURN
-A fail2ban-nginx-nohome -j RETURN
-A fail2ban-nginx-noproxy -j RETURN
-A fail2ban-nginx-noscript -j RETURN
-A fail2ban-ssh -j RETURN
</code></pre>
<p>If you want to see the details of the bans being enforced by any one jail, it is probably easier to use the <code>fail2ban-client</code> again:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo fail2ban-client status <span class="highlight">nginx-http-auth</span>
</li></ul></code></pre><pre class="code-pre "><code langs=""><div class="secondary-code-label " title="Output">Output</div>Status for the jail: nginx-http-auth
|- filter
|  |- File list:        /var/log/nginx/error.log 
|  |- Currently failed: 0
|  `- Total failed:     0
`- action
   |- Currently banned: 0
   |  `- IP list:
   `- Total banned:     0
</code></pre>
<h2 id="testing-fail2ban-policies">Testing Fail2Ban Policies</h2>

<p>It is important to test your <code>fail2ban</code> policies to ensure they block traffic as expected.  For instance, for the Nginx authentication prompt, you can give incorrect credentials a number of times.  After you have surpassed the limit, you should be banned and unable to access the site.  If you set up email notifications, you should see messages regarding the ban in the email account you provided.</p>

<p>If you look at the status with the <code>fail2ban-client</code> command, you will see your IP address being banned from the site:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo fail2ban-client status nginx-http-auth
</li></ul></code></pre><pre class="code-pre "><code langs=""><div class="secondary-code-label " title="Output">Output</div>Status for the jail: nginx-http-auth
|- filter
|  |- File list:        /var/log/nginx/error.log 
|  |- Currently failed: 0
|  `- Total failed:     12
`- action
   |- Currently banned: 1
   |  `- IP list:       <span class="highlight">111.111.111.111</span>
   `- Total banned:     1
</code></pre>
<p>When you are satisfied that your rules are working, you can manually un-ban your IP address with the <code>fail2ban-client</code> by typing:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo fail2ban-client set nginx-http-auth unbanip <span class="highlight">111.111.111.111</span>
</li></ul></code></pre>
<p>You should now be able to attempt authentication again.</p>

<h2 id="conclusion">Conclusion</h2>

<p>Setting up <code>fail2ban</code> to protect your Nginx server is fairly straight forward in the simplest case.  However, <code>fail2ban</code> provides a great deal of flexibility to construct policies that will suit your specific security needs.  By taking a look at the variables and patterns within the <code>/etc/fail2ban/jail.local</code> file, and the files it depends on within the <code>/etc/fail2ban/filter.d</code> and <code>/etc/fail2ban/action.d</code> directories, you can find many pieces to tweak and change as your needs evolve.  Learning the basics of how to protect your server with <code>fail2ban</code> can provide you with a great deal of security with minimal effort.</p>

<p>If you'd like to learn more about <code>fail2ban</code>, check out the following links:</p>

<ul>
<li><a href="https://indiareads/community/tutorials/how-fail2ban-works-to-protect-services-on-a-linux-server">How Fail2Ban Works to Protect Services on a Linux Server</a></li>
<li><a href="https://indiareads/community/tutorials/how-to-protect-ssh-with-fail2ban-on-ubuntu-14-04">How To Protect SSH with Fail2Ban on Ubuntu 14.04</a></li>
<li><a href="https://indiareads/community/tutorials/how-to-protect-an-apache-server-with-fail2ban-on-ubuntu-14-04">How To Protect an Apache Server with Fail2Ban on Ubuntu 14.04</a></li>
</ul>

    