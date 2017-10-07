<link rel='stylesheet' href='digital_ocean.css'><br> <br> 
      <h3 id="introduction">Introduction</h3>

<p>While connecting to your server through SSH can be very secure, the SSH daemon itself is a service that must be exposed to the Internet to function properly.  This comes with some inherent risk and offers a vector of attack for would-be assailants.</p>

<p>Any service that is exposed to the network is a potential target in this way.  If you pay attention to application logs for these services, you will often see repeated, systematic login attempts that represent brute-force attacks by users and bots alike.</p>

<p>A service called <strong>Fail2ban</strong> can mitigate this problem by creating rules that automatically alter your iptables firewall configuration based on a predefined number of unsuccessful login attempts.  This will allow your server to respond to illegitimate access attempts without intervention from you.</p>

<p>In this guide, we'll cover how to install and use Fail2ban on a CentOS 7 server.</p>

<h2 id="install-fail2ban-on-centos-7">Install Fail2ban on CentOS 7</h2>

<p>While Fail2ban is not available in the official CentOS package repository, it is packaged for the <a href="https://fedoraproject.org/wiki/EPEL">EPEL project</a>.  EPEL, standing for Extra Packages for Enterprise Linux, can be installed with a release package that <em>is</em> available from CentOS:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo yum install epel-release
</li></ul></code></pre>
<p>You will be prompted to continue---press <strong>y</strong>, followed by <strong>Enter</strong>:</p>
<pre class="code-pre "><code langs=""><div class="secondary-code-label " title="yum prompt">yum prompt</div>Transaction Summary
============================================================================
Install  1 Package

Total download size: 14 k
Installed size: 24 k
Is this ok [y/d/N]: <span class="highlight">y</span>
</code></pre>
<p>Now we should be able to install the <code>fail2ban</code> package:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo yum install fail2ban
</li></ul></code></pre>
<p>Again, press <strong>y</strong> and <strong>Enter</strong> when prompted to continue.</p>

<p>Once the installation has finished, use <code>systemctl</code> to enable the <code>fail2ban</code> service:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo systemctl enable fail2ban
</li></ul></code></pre>
<h2 id="configure-local-settings">Configure Local Settings</h2>

<p>The Fail2ban service keeps its configuration files in the <code>/etc/fail2ban</code> directory.  There, you can find a file with default values called <code>jail.conf</code>.  Since this file may be overwritten by package upgrades, we shouldn't edit it in-place.  Instead, we'll write a new file called <code>jail.local</code>.  Any values defined in <code>jail.local</code> will override those in <code>jail.conf</code>.</p>

<p><code>jail.conf</code> contains a <code>[DEFAULT]</code> section, followed by sections for individual services.  <code>jail.local</code> may override any of these values.  Additionally, files in <code>/etc/fail2ban/jail.d/</code> can be used to override settings in both of these files.  Files are applied in the following order:</p>

<ol>
<li><code>/etc/fail2ban/jail.conf</code></li>
<li><code>/etc/fail2ban/jail.d/*.conf</code>, alphabetically</li>
<li><code>/etc/fail2ban/jail.local</code></li>
<li><code>/etc/fail2ban/jail.d/*.local</code>, alphabetically</li>
</ol>

<p>Any file may contain a <code>[DEFAULT]</code> section, executed first, and may also contain sections for individual jails.  The last vavalue set for a given parameter takes precedence.</p>

<p>Let's begin by writing a very simple version of <code>jail.local</code>.  Open a new file using <code>nano</code> (or your editor of choice):</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo nano /etc/fail2ban/jail.local
</li></ul></code></pre>
<p>Paste the following:</p>
<div class="code-label " title="/etc/fail2ban/jail.local">/etc/fail2ban/jail.local</div><pre class="code-pre "><code langs="">[DEFAULT]
# Ban hosts for one hour:
bantime = 3600

# Override /etc/fail2ban/jail.d/00-firewalld.conf:
banaction = iptables-multiport

[sshd]
enabled = true
</code></pre>
<p>This overrides three settings:  It sets a new default <code>bantime</code> for all services, makes sure we're using <code>iptables</code> for firewall configuration, and enables the <code>sshd</code> jail.</p>

<p>Exit and save the new file (in <code>nano</code>, press <strong>Ctrl-X</strong> to exit, <strong>y</strong> to save, and <strong>Enter</strong> to confirm the filename).  Now we can restart the <code>fail2ban</code> service using <code>systemctl</code>:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo systemctl restart fail2ban
</li></ul></code></pre>
<p>The <code>systemctl</code> command should finish without any output.  In order to check that the service is running, we can use <code>fail2ban-client</code>:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo fail2ban-client status
</li></ul></code></pre><pre class="code-pre "><code langs=""><div class="secondary-code-label " title="Output">Output</div>Status
|- Number of jail:      1
`- Jail list:   sshd
</code></pre>
<p>You can also get more detailed information about a specific jail:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo fail2ban-client status sshd
</li></ul></code></pre>
<h2 id="explore-available-settings">Explore Available Settings</h2>

<p>The version of <code>jail.local</code> we defined above is a good start, but you may want to adjust a number of other settings.  Open <code>jail.conf</code>, and we'll examine some of the defaults.  If you decide to change any of these values, remember that they should be copied to the appropriate section of <code>jail.local</code> and adjusted there, rather than modified in-place.</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo nano /etc/fail2ban/jail.conf
</li></ul></code></pre>
<h3 id="default-settings-for-all-jails">Default Settings for All Jails</h3>

<p>First, scroll through the <code>[DEFAULT]</code> section.</p>
<pre class="code-pre "><code langs="">ignoreip = 127.0.0.1/8
</code></pre>
<p>You can adjust the source addresses that Fail2ban ignores by adding a value to the <code>ignoreip</code> parameter.  Currently, it is configured not to ban any traffic coming from the local machine.  You can include additional addresses to ignore by appending them to the end of the parameter, separated by a space.</p>
<pre class="code-pre "><code langs="">bantime = 600
</code></pre>
<p>The <code>bantime</code> parameter sets the length of time that a client will be banned when they have failed to authenticate correctly.  This is measured in seconds.  By default, this is set to 600 seconds, or 10 minutes.</p>
<pre class="code-pre "><code langs="">findtime = 600
maxretry = 3
</code></pre>
<p>The next two parameters that you want to pay attention to are <code>findtime</code> and <code>maxretry</code>.  These work together to establish the conditions under which a client should be banned.</p>

<p>The <code>maxretry</code> variable sets the number of tries a client has to authenticate within a window of time defined by <code>findtime</code>, before being banned.  With the default settings, Fail2ban will ban a client that unsuccessfully attempts to log in 3 times within a 10 minute window.</p>
<pre class="code-pre "><code langs="">destemail = root@localhost
sendername = Fail2Ban
mta = sendmail
</code></pre>
<p>If you wish to configure email alerts, you may need to override the <code>destemail</code>, <code>sendername</code>, and <code>mta</code> settings.  The <code>destemail</code> parameter sets the email address that should receive ban messages.  The <code>sendername</code> sets the value of the "From" field in the email.  The <code>mta</code> parameter configures what mail service will be used to send mail.</p>
<pre class="code-pre "><code langs="">action = $(action_)s
</code></pre>
<p>This parameter configures the action that Fail2ban takes when it wants to institute a ban.  The value <code>action_</code> is defined in the file shortly before this parameter.  The default action is to simply configure the firewall to reject traffic from the offending host until the ban time elapses.</p>

<p>If you would like to configure email alerts, you can override this value from <code>action_</code> to <code>action_mw</code>.  If you want the email to include the relevant log lines, you can change it to <code>action_mwl</code>.  You'll want to make sure you have the appropriate mail settings configured if you choose to use mail alerts.</p>

<h3 id="settings-for-individual-jails">Settings for Individual Jails</h3>

<p>After <code>[DEFAULT]</code>, we'll encounter sections configuring individual jails for different services.  These will typically include a <code>port</code> to be banned and a <code>logpath</code> to monitor for malicious access attempts.  For example, the SSH jail we already enabled in <code>jail.local</code> has the following settings:</p>
<div class="code-label " title="/etc/fail2ban/jail.local">/etc/fail2ban/jail.local</div><pre class="code-pre "><code langs="">[sshd]

port    = ssh
logpath = %(sshd_log)s
</code></pre>
<p>In this case, <code>ssh</code> is a pre-defined variable for the standard SSH port, and <code>%(sshd_log)s</code> uses a value defined elsewhere in Fail2ban's standard configuration (this helps keep <code>jail.conf</code> portable between different operating systems).</p>

<p>Another setting you may encounter is the <code>filter</code> that will be used to decide whether a line in a log indicates a failed authentication.</p>

<p>The <code>filter</code> value is actually a reference to a file located in the <code>/etc/fail2ban/filter.d</code> directory, with its <code>.conf</code> extension removed.  This file contains the regular expressions that determine whether a line in the log is bad.  We won't be covering this file in-depth in this guide, because it is fairly complex and the predefined settings match appropriate lines well.</p>

<p>However, you can see what kind of filters are available by looking into that directory:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">ls /etc/fail2ban/filter.d
</li></ul></code></pre>
<p>If you see a file that looks to be related to a service you are using, you should open it with a text editor.  Most of the files are fairly well commented and you should be able to tell what type of condition the script was designed to guard against.  Most of these filters have appropriate (disabled) sections in <code>jail.conf</code> that we can enable in <code>jail.local</code> if desired.</p>

<p>For instance, pretend that we are serving a website using Nginx and realize that a password-protected portion of our site is getting slammed with login attempts.  We can tell Fail2ban to use the <code>nginx-http-auth.conf</code> file to check for this condition within the <code>/var/log/nginx/error.log</code> file.</p>

<p>This is actually already set up in a section called <code>[nginx-http-auth]</code> in our <code>/etc/fail2ban/jail.conf</code> file.  We would just need to add an <code>enabled</code> parameter for the <code>nginx-http-auth</code> jail to <code>jail.local</code>:</p>
<div class="code-label " title="/etc/fail2ban/jail.local">/etc/fail2ban/jail.local</div><pre class="code-pre "><code langs="">[DEFAULT]
# Ban hosts for one hour:
bantime = 3600

# Override /etc/fail2ban/jail.d/00-firewalld.conf:
banaction = iptables-multiport

[sshd]
enabled = true

<span class="highlight">[nginx-http-auth]</span>
<span class="highlight">enabled = true</span>
</code></pre>
<p>And restart the <code>fail2ban</code> service:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo systemctl restart fail2ban
</li></ul></code></pre>
<h2 id="monitor-fail2ban-logs-and-firewall-configuration">Monitor Fail2ban Logs and Firewall Configuration</h2>

<p>It's important to know that a service like Fail2ban is working as-intended.  Start by using <code>systemctl</code> to check the status of the service:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo systemctl status fail2ban
</li></ul></code></pre>
<p>If something seems amiss here, you can troubleshoot by checking logs for the <code>fail2ban</code> unit since the last boot:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo journalctl -b -u fail2ban
</li></ul></code></pre>
<p>Next, use <code>fail2ban-client</code> to query the overall status of <code>fail2ban-server</code>, or any individual jail:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo fail2ban-client status
</li><li class="line" prefix="$">sudo fail2ban-client status <span class="highlight">jail_name</span>
</li></ul></code></pre>
<p>Follow Fail2ban's log for a record of recent actions (press <strong>Ctrl-C</strong> to exit):</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo tail -F /var/log/fail2ban.log
</li></ul></code></pre>
<p>List the current rules configured for iptables:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo iptables -L
</li></ul></code></pre>
<p>Show iptables rules in a format that reflects the commands necessary to enable each rule:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo iptables -S
</li></ul></code></pre>
<h2 id="conclusion">Conclusion</h2>

<p>You should now be able to configure some basic banning policies for your services.  Fail2ban is very easy to set up, and is a great way to protect any kind of service that uses authentication.</p>

<p>If you want to learn more about how Fail2ban works, you can check out our tutorial on <a href="https://indiareads/community/articles/how-fail2ban-works-to-protect-services-on-a-linux-server">how fail2ban rules and files work</a>.</p>

    