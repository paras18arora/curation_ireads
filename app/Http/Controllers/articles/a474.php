<link rel='stylesheet' href='digital_ocean.css'><br> <br> 
      <h3 id="introduction">Introduction</h3>

<p>Mumble-Django is a front-end web interface for administering Murmur server instances and the users they serve. This dashboard will let you, and other admins if you choose, run your Mumble server from a graphical web interface rather than the command line.</p>

<p><img src="https://assets.digitalocean.com/articles/mumble-django-debian7/ARZG5OD.png" alt="Mumble-Django Default Page" /></p>

<p>As the name implies, the dashboard is written with Django. It makes a variety of administrative features of your Murmur server accessible through a graphical interface, once the admin user is logged in. Features include but are not limited to:</p>

<ul>
<li>Live channel viewer (CVP) with responsive version for mobile devices</li>
<li>Mumble user permissions</li>
<li>Configuration for settings like message of the day, server password, version to recommend, and much more</li>
<li>Bans</li>
<li>Logs</li>
<li>Extensibility through the Django framework and licensing as free software under the GPL</li>
</ul>

<p>Also, you can administer multiple Murmur installations from the same dashboard.</p>

<h2 id="prerequisites">Prerequisites</h2>

<p>Please complete the following prerequisites.</p>

<ul>
<li><p>A VPS with <a href="https://indiareads/features/linux-distribution/debian/">Debian 7</a> as its operating system (at the time of writing, Mumble-Django did not work well on Debian 8)</p></li>
<li><p>A user account on the VPS with <strong>sudo</strong> elevation privileges: <a href="https://indiareads/community/tutorials/how-to-add-delete-and-grant-sudo-privileges-to-users-on-a-debian-vps">How To Add, Delete, and Grant Sudo Privileges to Users on a Debian VPS</a></p></li>
<li><p>A Mumble server (Murmur) installed and running on the same VPS: <a href="https://indiareads/community/tutorials/how-to-install-and-configure-mumble-server-murmur-on-ubuntu-14-04">How To Install and Configure Mumble Server (Murmur) on Ubuntu 14.04</a></p></li>
</ul>

<p><span class="note">The IndiaReads article linked above, <strong>How To Install and Configure Mumble Server (Murmur) on Ubuntu 14.04</strong>, can also be followed for a Droplet running Debian 7.<br /></span> </p>

<h2 id="configuring-ice-middleware">Configuring ICE Middleware</h2>

<p>Before installing Mumble-Django, we have to enable the <em>ICE</em> (Internet Communications Engine) component of Murmur. This will allow us to use the extra functionality of programs like <code>mumble-django</code> and the features they provide.</p>

<p>ICE makes it possible to interaction with the Murmur server through means other than the default Mumble client, without compromising the security of the inner workings of Murmur, such as the databases, registered user details, and admin rights.</p>

<p>The alternative to ICE is <em>D-Bus</em>, which provides a similar service, but ICE is generally the preferred choice now, which is why we are choosing it over D-Bus in this tutorial.  </p>

<h3 id="step-1-—-connect-to-murmur-server">Step 1 — Connect to Murmur Server</h3>

<p>Use SSH to connect to the VPS where you installed the Murmur server from the <a href="https://indiareads/community/tutorials/how-to-install-and-configure-mumble-server-murmur-on-ubuntu-14-04">previous tutorial</a>. Use a user with <strong>sudo</strong> elevation privileges, and follow the steps below in order.  </p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">ssh <span class="highlight">sammy</span>@<span class="highlight">your_server_ip</span>
</li></ul></code></pre>
<h3 id="step-2-—-configure-ice-in-mumble-server-ini">Step 2 — Configure ICE in mumble-server.ini</h3>

<p>All of the core configuration of the Mumble server resides in one central text file. By default, this is the <code>mumble-server.ini</code> file in the <code>/etc/</code> directory on Debian. If you changed this directory during the earlier Murmur server installation, then you will find it in there instead.</p>

<p>Let's find the file and make sure it exists in <code>/etc/</code> by using <code>grep</code> :</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo ls /etc/ | grep mumble-server
</li></ul></code></pre>
<p>If the file is present, the output will be:</p>
<pre class="code-pre "><code langs=""><div class="secondary-code-label " title="Output">Output</div>mumble-server.ini
</code></pre>
<p>We're going to use the <code>nano</code> text editor to open files for writing and editing in this tutorial. Feel free to use your preferred text editor instead. </p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo nano /etc/mumble-server.ini
</li></ul></code></pre>
<p>Disable D-Bus by <em>commenting</em> out its entry line. </p>

<p>Do this by adding a hash symbol (<code>#</code>) to the beginning of the line shown here: </p>
<div class="code-label " title="/etc/mumble-server.ini">/etc/mumble-server.ini</div><pre class="code-pre "><code langs=""><span class="highlight">#</span>dbus=system
</code></pre>
<p>Further down in this file, confirm that the <code>ice</code> line exists and is <em>uncommented</em> (it should be by default):</p>
<div class="code-label " title="/etc/mumble-server.ini">/etc/mumble-server.ini</div><pre class="code-pre "><code langs="">ice="tcp -h 127.0.0.1 -p 6502"
</code></pre>
<p>This allows ICE access on the <em>localhost</em> IP address of the server through TCP port <strong>6502</strong>. Leave this line as it is.</p>

<p><strong>Set the ICE Secret</strong></p>

<p>Next we need to set a value for the <code>icesecretwrite</code> directive in the config file. <strong>If this is left blank, anyone with SSH access to your server can reconfigure or change the ICE setup.</strong></p>

<p>The two lines we are looking for in the file look like this:</p>
<div class="code-label " title="/etc/mumble-server.ini">/etc/mumble-server.ini</div><pre class="code-pre "><code langs="">#icesecretread=
icesecretwrite=
</code></pre>
<p>The first line we can ignore, as it is already commented out and disabled, which is fine. The second line is where we need to set the <em>ICE secret</em>. </p>

<p>Append your chosen phrase to the second line (all one word); make sure you set a password different from the one shown below:</p>
<div class="code-label " title="/etc/mumble-server.ini">/etc/mumble-server.ini</div><pre class="code-pre "><code langs="">#icesecretread=
icesecretwrite=<span class="highlight">example_password</span>
</code></pre>
<p>You will need this ICE secret later on, so make sure you remember it.</p>

<p>Save your changes in <code>nano</code> to the <code>mumble-server.ini</code> config file by pressing: </p>

<p><code>CTRL</code> + <code>X</code>, then <code>y</code> for yes, then the <code>ENTER</code> key.</p>

<p>If using a different text editor, then perform the equivalent save/write actions. </p>

<h3 id="step-3-—-confirm-ice-is-running">Step 3 — Confirm ICE Is Running</h3>

<p>Restart the Murmur server so the changes we made take effect.</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo service mumble-server restart
</li></ul></code></pre>
<p>Use <code>netstat</code> to determine whether ICE is indeed running and listening on port <strong>6502</strong>, just like we enabled it to:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo netstat -apn | grep 6502
</li></ul></code></pre>
<p>This previous command <em>pipes</em> the output we generate from <code>netstat</code> through <code>grep</code>, which selects only data matching the pattern we've specified for output to the terminal. In our case this pattern is the number <strong>6502</strong>.</p>

<p>The output we receive from this command will look like the next code snippet if everything is running correctly:</p>
<pre class="code-pre "><code langs=""><div class="secondary-code-label " title="Output">Output</div>tcp      0      0 127.0.0.1:6502        0.0.0.0:*      LISTEN      23629/murmurd   
</code></pre>
<p><span class="note">The final group of digits in the above output will differ from user to user.<br /></span></p>

<p>If the port is not being listened on, and you receive no output like the above, check Murmur's log file to see if you can identify any specific errors on boot, in relation to this <em>socket</em> (<code>127.0.0.1:6502</code>). </p>

<p>You can check the log file by using the <code>tail</code> command shown here: </p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo tail -n 20 /var/log/mumble-server/mumble-server.log
</li></ul></code></pre>
<p>It should state in the log file that it is enabling ICE on startup. The line that indicates this looks like the following:</p>
<pre class="code-pre "><code langs=""><div class="secondary-code-label " title="Output">Output</div>"MurmurIce: Endpoint "tcp -h 127.0.0.1 -p 6502" running"
</code></pre>
<p>The line will likely be a few lines back in the log.</p>

<p>If it does not show this in your <code>tail</code> output, then your <code>mumble-server.ini</code> file probably needs to be checked for inaccuracy or errors; the log file may have more specific details on the type of error.</p>

<p>Go back and check your settings now if needed, or proceed to the next section if everything is working as intended.</p>

<h2 id="installing-the-apache-web-server">Installing the Apache Web Server</h2>

<p>ICE is now working and listening as we need it to. </p>

<p>Let's bring Apache into the picture. </p>

<h3 id="step-1-—-update-and-upgrade-system-packages">Step 1 — Update and Upgrade System Packages</h3>

<p>This command updates the <code>apt-get</code> package manager's database.</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo apt-get update
</li></ul></code></pre>
<p>This next action will install any new updates gained from the previous command to the Debian system packages.</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo apt-get upgrade
</li></ul></code></pre>
<p>Confirm when prompted about updating new packages by entering <code>y</code> for yes.</p>

<h3 id="step-2-—-install-apache-web-server">Step 2 — Install Apache Web Server</h3>

<p>This installs the base version of Apache we need to host Mumble-Django:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo apt-get install apache2
</li></ul></code></pre>
<h3 id="step-3-—-set-servername-in-apache2-conf">Step 3 — Set ServerName in apache2.conf</h3>

<p>Open up the <code>apache2.conf</code> file with a text editor</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo nano /etc/apache2/apache2.conf
</li></ul></code></pre>
<p>Scroll down to find the <code>Global Configuration</code> section and add the entire <code>ServerName</code> line, using your own IP address:</p>
<div class="code-label " title="/etc/apache2/apache2.conf">/etc/apache2/apache2.conf</div><pre class="code-pre "><code langs=""># Global configuration
#
ServerName <span class="highlight">your_server_ip</span>         
</code></pre>
<p>Save the <code>apache2.conf</code> file changes. </p>

<p>Restart Apache so the config file changes we made are picked up.</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo service apache2 restart
</li></ul></code></pre>
<h2 id="installing-amp-configuring-postfix-for-mail">Installing & Configuring Postfix for Mail</h2>

<p>Postfix provides Mumble-Django with a local email address and a system to send out registration and other emails when needed.</p>

<p>Here's how to install and configure it for what we need.</p>

<h3 id="step-1-—-install-postfix">Step 1 — Install Postfix</h3>

<p>Use <code>apt-get</code> to install the <code>postfix</code> package:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo apt-get install postfix
</li></ul></code></pre>
<p>Select <strong>Internet Site</strong> from the installation menu. It should be selected by default, so just press <code>ENTER</code>.</p>

<p><img src="https://assets.digitalocean.com/articles/mumble-django-debian7/0Q6vUrD.png" alt="Internet Site" /></p>

<p>Then enter the name you gave your Droplet upon creation; you can find this listed in the IndiaReads Control Panel.</p>

<p><img src="https://assets.digitalocean.com/articles/mumble-django-debian7/Z31TfsO.png" alt="System Mail Name" /></p>

<p><span class="note">This mail installation provides only the barest SMTP (mail sending) functionality. You'll want to make sure your DNS settings, Postfix settings, and hostname all align in a production mail setup.<br /></span></p>

<h3 id="step-2-—-configure-postfix">Step 2 — Configure Postfix</h3>

<p>Edit the main configuration file of Postfix:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo nano /etc/postfix/main.cf
</li></ul></code></pre>
<p>Find the last line of the file that reads:</p>
<div class="code-label " title="/etc/postfix/main.cf">/etc/postfix/main.cf</div><pre class="code-pre "><code langs="">inet_interfaces = all
</code></pre>
<p>Then, change it from <code>all</code> to <code>localhost</code> so Postfix will only operate using the server's loopback address:</p>
<div class="code-label " title="/etc/postfix/main.cf">/etc/postfix/main.cf</div><pre class="code-pre "><code langs="">inet_interfaces = localhost
</code></pre>
<p>Save your changes to the <code>main.cf</code> config file. </p>

<p>Reload the configuration file changes by restarting <code>postfix</code> .</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo service postfix restart
</li></ul></code></pre>
<p>Postfix is now ready to send out emails as needed. </p>

<h2 id="installing-amp-configuring-mumble-django">Installing & Configuring Mumble-Django</h2>

<p>The vast majority of this next section is completed as part of one continual configuration process, and sets up Mumble-Django for use. </p>

<p>Bear in mind that the method we will use here is for an Apache setup <strong>without</strong> virtual hosts.</p>

<h3 id="step-1-—-install-mumble-django">Step 1 — Install Mumble-Django</h3>

<p>Install Mumble-Django itself:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo apt-get install mumble-django 
</li></ul></code></pre>
<p>When prompted, press <code>ENTER</code> to select <code><Ok></code> during the installation.</p>

<h3 id="step-2-—-configure-mumble-django">Step 2 — Configure Mumble-Django</h3>

<p>The interactive configuration process is started by entering this command:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo mumble-django-configure
</li></ul></code></pre>
<p>You should see this interactive prompt:</p>
<pre class="code-pre "><code langs=""><div class="secondary-code-label " title="Interactive">Interactive</div>What do you want to do?
 > 1) Detect a new Mumble-Server instance and make it known to Mumble-Django
   2) Create a new SuperUser for Mumble-Django's web admin interface
      Note: This will be done automatically when you run 1) for the first time.
   3) Drop to a Python shell.
   4) Drop to a Database shell.
</code></pre>
<p>In this case we want to select option <code>1</code> to make the running instance of Murmur known to the program. </p>

<p>Press <code>1</code> and then <code>ENTER</code> to continue.</p>

<p>Another prompt should now be shown that looks like this:</p>
<pre class="code-pre "><code langs=""><div class="secondary-code-label " title="Interactive">Interactive</div>If this is the first time you run this script, you might want to probe for the
Debian default configuration instead of entering the service string yourself.
Please choose what service string to use.
 > 1) Debian default (Meta:tcp -h 127.0.0.1 -p 6502)
   2) user defined
</code></pre>
<p>Once again we want to select option <code>1</code>, as this is what we enabled earlier in the <code>murmur-server.ini</code> config file.  </p>

<p>Press <code>1</code> again and then <code>ENTER</code> to continue.</p>

<p>The next set of output will look like this:</p>
<pre class="code-pre "><code langs=""><div class="secondary-code-label " title="Output">Output</div>Running as www-data: manage.py syncdb
Creating tables ...
Creating table auth_permission
Creating table auth_group_permissions
Creating table auth_group
Creating table auth_user_user_permissions
Creating table auth_user_groups
Creating table auth_user
Creating table django_admin_log
Creating table django_content_type
Creating table django_session
Creating table django_site
Creating table mumble_mumbleserver
Creating table mumble_mumble
Creating table mumble_mumbleuser
Creating table registration_registrationprofile
</code></pre>
<p>Followed by:</p>
<pre class="code-pre "><code langs=""><div class="secondary-code-label " title="Interactive">Interactive</div>You just installed Django's auth system, which means you don't have any superusers defined.
Would you like to create one now? (yes/no): 
</code></pre>
<p>This step lets us create a new administrative user for Mumble-Django. This user is for the dashboard only; it's not a Mumble or Murmur user. However, this user <strong>will</strong> have access to act as a Mumble administrator in many ways.</p>

<p>Type <code>yes</code> and press <code>ENTER</code>.  </p>

<p>Proceed by completing the information for the new Mumble-Django user.</p>

<p>The first one, <code>Username</code>, can be whatever name you choose. In this example the admin user is named <code><span class="highlight">sammy</span></code>.</p>
<pre class="code-pre "><code langs=""><div class="secondary-code-label " title="Interactive">Interactive</div>Username (leave blank to use 'www-data'): <span class="highlight">sammy</span> 
</code></pre>
<p>The <code>Email Address</code> is bound to the username and password you are about to create:</p>
<pre class="code-pre "><code langs=""><div class="secondary-code-label " title="Interactive">Interactive</div>E-mail address: <span class="highlight">sammy@emaildomain.com</span>    
</code></pre>
<p>This <code>Password</code> is used to log in to the Mumble-Django dashboard along with the username that we just created. </p>
<pre class="code-pre "><code langs=""><div class="secondary-code-label " title="Interactive">Interactive</div>     Password: 
Password (again): 
</code></pre>
<p>After the <code>Superuser created successfully</code> message, we are asked to enter the <code>Ice secret</code>. </p>

<p>We set this earlier in the first section within the <code>mumble-server.ini</code> config file. </p>

<p>Enter whatever exact value you decided on for the ICE secret now:</p>
<pre class="code-pre "><code langs=""><div class="secondary-code-label " title="Interactive">Interactive</div>Please enter the Ice secret (if any): <span class="highlight">example_password</span> 
</code></pre>
<p>We are then given the choice to restart Apache again:</p>
<pre class="code-pre "><code langs=""><div class="secondary-code-label " title="Interactive">Interactive</div>Apache2
-------------------------
If you have changed any settings in settings.py, you should reload the Web server
in order for the changes to take effect. Do you want to reload Apache2 now?
   1) Yes, reload Apache2.
 > 2) No, don't do anything.
</code></pre>
<p>Do as recommended and restart Apache by typing <code>1</code> and then pressing <code>ENTER</code> for a final time. (If you accidentally hit <code>ENTER</code> on the second option, remember to restart Apache later with <code>sudo service apache2 restart</code>)</p>

<p>Last, we are asked for a domain where Mumble-Django can be reached. </p>
<pre class="code-pre "><code langs=""><div class="secondary-code-label " title="Output">Output</div>The domain is configured as example.com, which is the default but does not make sense. Please enter the domain where Mumble-Django is reachable.
</code></pre>
<p>If you have a domain name set up for your server, you can enter it here. Otherwise, enter your server's IP address and press <code>ENTER</code>. </p>
<pre class="code-pre "><code langs=""><div class="secondary-code-label " title="Interactive">Interactive</div><span class="highlight">your_server_ip</span>
</code></pre>
<p>You should see these final lines of output, confirming that the configuration is successful:</p>
<pre class="code-pre "><code langs=""><div class="secondary-code-label " title="Output">Output</div><span class="highlight">your.vps.ip.address</span> [ OK ]
Checking Murmur instances... [ OK ]
Checking if an Admin user exists... [ OK ]
Checking SECRET_KEY... [ OK ]
Goodbye.
</code></pre>
<p>If you've reached this point in the configuration process with everything working as intended, move on to the next step below.</p>

<p>For those who received an error message during the previous configuration process reading:</p>
<pre class="code-pre "><code langs=""><div class="secondary-code-label " title="Output">Output</div>Murmur does not appear to be listening on this address.
</code></pre>
<p>If you see this error, it is likely that the localhost or <em>loopback</em> address we set ICE to run through is blocked, and conflicting with a firewall on your VPS. </p>

<p>In the case of a basic <code>iptables</code> firewall, the <code>127.0.0.1</code> loopback IP address needs to be added as a rule to allow for it to transmit properly.</p>

<p>Add the <code>iptables</code> rules with these commands:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo iptables -I INPUT 1 -i lo -j ACCEPT -m comment --comment "allow input on localhost"
</li><li class="line" prefix="$">
</li><li class="line" prefix="$">sudo iptables -I OUTPUT 1 -o lo -j ACCEPT -m comment --comment "allow output on localhost"
</li></ul></code></pre>
<p>Once this is resolved, run the configure command again to restart the process:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo mumble-django-configure
</li></ul></code></pre>
<p>Then, go through this step again from the beginning.</p>

<p><span class="note">For more help with <code>iptables</code> and how it operates, see this IndiaReads guide: <a href="https://indiareads/community/tutorials/how-to-set-up-a-firewall-using-iptables-on-ubuntu-14-04">How To Set Up a Firewall Using IPTables on Ubuntu 14.04</a><br /></span></p>

<h3 id="step-3-—-edit-settings-in-settings-py">Step 3 — Edit Settings in settings.py</h3>

<p><code>settings.py</code> is the main configuration file for Mumble-Django. Open it with <code>nano</code> or your preferred text editor:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo nano /etc/mumble-django/settings.py
</li></ul></code></pre>
<p>First provide an email address where Mumble-Django can send errors. Locate the line <code># Who will receive emails on errors?</code>, and then enter your name and email address between the two sets of parentheses: </p>

<p>Notice also that the <code>#</code> symbol needs to be removed to enable the line. </p>
<div class="code-label " title="/etc/mumble-django/settings.py">/etc/mumble-django/settings.py</div><pre class="code-pre "><code langs=""># Who will receive emails on errors?
ADMINS = (
     ('<span class="highlight">Sammy</span>', '<span class="highlight">sammy@email-domain.com</span>'),
)
</code></pre>
<p>Now set debug mode to <code>False</code> in this file by setting <code>DEBUG</code> to <code>False</code> .</p>
<div class="code-label " title="/etc/mumble-django/settings.py">/etc/mumble-django/settings.py</div><pre class="code-pre "><code langs=""># If you want to file a bug report, please enable this option.
DEBUG = <span class="highlight">False</span>
</code></pre>
<p>While debug mode is disabled, this will email the address above with the full exception error information if and when errors are generated by users.</p>

<p><span class="note">It can be helpful to leave debug mode on while you are setting things up, or if you run into errors with the dashboard. Turn it off when you go into production.<br /></span></p>

<p>Save your changes to <code>settings.py</code>. </p>

<p>Restart Apache again so the config file changes in <code>settings.py</code> become active:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo service apache2 restart
</li></ul></code></pre>
<h2 id="using-the-mumble-django-online-dashboard">Using the Mumble-Django Online Dashboard</h2>

<p>The final section of the tutorial describes using Mumble-Django's web interface. </p>

<h3 id="step-1-—-access-mumble-django">Step 1 — Access Mumble-Django</h3>

<p>You can access Mumble-Django in a web browser at either of the following addresses:</p>

<ul>
<li><p><code>http://<span class="highlight">your_server_ip</span>/mumble-django</code> </p></li>
<li><p><code>http://<span class="highlight">your_server_ip</span>/mumble-django/mumble/1</code></p></li>
</ul>

<p>You should see the <strong>Channel Viewer</strong> window in the left column, and the <strong>Server info</strong> tab in the right column. </p>

<p><img src="https://assets.digitalocean.com/articles/mumble-django-debian7/drGueaR.png" alt="Mumble-Django Default Page" /></p>

<h3 id="step-2-—-log-in-as-the-django-admin-user">Step 2 — Log in as the Django Admin User</h3>

<p>To view the rest of the interface and extra tabs, you need to log in to the dashboard with the Django administrative user you set back in <strong>Step 2 — Configure Mumble-Django</strong>. In our example, this was <strong>sammy</strong>. Remember, the dashboard user is separate from your Mumble (Murmur) user account details generated in the other tutorial. </p>

<p>The button to <strong>Login</strong> is located at the bottom right of the page. </p>

<p><img src="https://assets.digitalocean.com/articles/mumble-django-debian7/hiuPjYy.png" alt="Login Button" /></p>

<p>After signing in, read the next few sections to take a look at all the different things you can do via the dashboard! </p>

<h3 id="live-channel-viewer">Live Channel Viewer</h3>

<p>This window is static; it will always be visible, regardless of what you choose to view or change. </p>

<p>The <strong>Channel Viewer</strong> shows you which users are presently connected to the Mumble server instance in question. </p>

<p><img src="https://assets.digitalocean.com/articles/mumble-django-debian7/ZhgXhZi.png" alt="Live Channel Viewer Pane" /> </p>

<p>The viewer even tracks when a user is actively transmitting to the server (that is, the user is talking). The interval for updating this can be increased or lowered at the bottom of the screen, where you can enable/disable <strong>Auto-Refresh</strong> and set the refresh interval in seconds.</p>

<p><img src="https://assets.digitalocean.com/articles/mumble-django-debian7/d2tbgYb.png" alt="Refresh Tab" /></p>

<p>Everything here you would normally see on a Mumble client works here, too. So, you can use the channel names, descriptions, images, messages, etc. </p>

<h3 id="server-info">Server info</h3>

<p>The <strong>Server info</strong> tab shows general statistics and settings for the current Mumble server instance you are viewing. Other details you add and amend in the <strong>Administration</strong> can be added to this tab as well. </p>

<p><img src="https://assets.digitalocean.com/articles/mumble-django-debian7/ZrtOpD0.png" alt="Server Information Screenshot" /> </p>

<p>You can click links on this screen for more information.</p>

<p><img src="https://assets.digitalocean.com/articles/mumble-django-debian7/eMsqUhq.png" alt="Server Information Screenshot" /> </p>

<h3 id="registration">Registration</h3>

<p>With this <strong>Registration</strong> form, you can add <strong>Mumble users</strong> to the Murmur database for connection from the client. (These are not dashboard users; these are chat users.) Mumble accounts that belong to you and already exist can be linked to your Mumble-Django account name, and are marked with you as the owner. (Feel free to sync up the <strong>SuperUser</strong> Mumble account with your Mumble-Django user now.)</p>

<p><img src="https://assets.digitalocean.com/articles/mumble-django-debian7/epXSfzk.png" alt="Registration Tab Form" /></p>

<p>Bear in mind that the default method of authentication in Murmur is now SSL certificates, and not text-based passwords, for user accounts.</p>

<p>We'll go over how to add more dashboard users in a later section.</p>

<h3 id="administration">Administration</h3>

<p>You might recognize the settings in the <strong>Administration</strong> tab from the <code>mumble-server.ini</code> file. Setting and adding these here overwrites anything you have defined in said file, and applies it to the Mumble server instance you're administering. </p>

<p><img src="https://assets.digitalocean.com/articles/mumble-django-debian7/xqiP05B.png" alt="Administration Tab Forms" /></p>

<p>Like the <strong>Log messages</strong> tab, this is a more accessible method of changing and adding to the server's configuration. Many of the fields update without a reboot and are instantly applied.  </p>

<h3 id="user-texture">User texture</h3>

<p>Here you can add images for users. See the <a href="http://wiki.mumble.info/wiki/1.2.0#User_textures">Mumble wiki</a> for details.</p>

<h3 id="user-list">User List</h3>

<p>The <strong>User List</strong> shows any user accounts you have registered through the Mumble client or this dashboard. You can delete users, give admin rights, and change passwords if applicable. Note that Mumble now uses SSL certificates by default for authentication, and not text-based passwords, so you shouldn't need to alter any passwords here. </p>

<p><img src="https://assets.digitalocean.com/articles/mumble-django-debian7/HHwi3Nx.png" alt="User List Buttons" /></p>

<p>Use the <strong>Resync with Murmur</strong> button frequently when simultaneously changing user properties through the Mumble client and Mumble-Django. </p>

<h3 id="log-messages">Log messages</h3>

<blockquote>
<p><strong>Note:</strong> IP Addresses have been redacted where necessary in the upcoming screenshots. </p>
</blockquote>

<p>Murmur's log file includes both internal and external (incoming/outgoing) server and database events. It's possible to locate this file in  <code>/var/log/</code> on the command line to view its contents, but you can also skip doing this and view it here instead, if you want to see the last few recent entries. </p>

<p><img src="https://assets.digitalocean.com/articles/mumble-django-debian7/73qdupA.png" alt="Log Messages Tab" /></p>

<p>You can't manipulate the data here like on the command line, but it's still presentable and a lot more accessible to users who don't have command line access. It's also filterable through the input field at the bottom left of the window.     </p>

<h3 id="bans">Bans</h3>

<p>Bans on user appear here with all the details laid out into columns. </p>

<p><img src="https://assets.digitalocean.com/articles/mumble-django-debian7/Q8Jes7n.png" alt="Bans Tab Columns" /></p>

<p>A <strong>Duration</strong> value of <code>0</code> indicates a permanent ban. </p>

<p><img src="https://assets.digitalocean.com/articles/mumble-django-debian7/20Qtbfj.png" alt="Bans Tab Columns" /></p>

<p>Select a ban to lift it via the <strong>Delete</strong> button at the bottom left.</p>

<h3 id="step-3-—-access-the-django-administration-page">Step 3 — Access the Django Administration Page</h3>

<p>The previous section let us administer <strong>Mumble</strong>. In this section, we'll show you how to administer the dashboard itself.</p>

<p>To access even more of the capabilities of Mumble-Django, click the <strong>Administration</strong> button at the bottom right of the screen (only visible when logged in). </p>

<p><img src="https://assets.digitalocean.com/articles/mumble-django-debian7/XHu6ibd.png" alt="Administration Button" /></p>

<p>In this new window, there are some extra server instance details you can configure if required.</p>

<p><img src="https://assets.digitalocean.com/articles/mumble-django-debian7/3SahXtT.png" alt="Home Link" /></p>

<p>Click the <strong>Home</strong> link at the top left of the new panel. This takes you to the root Django administration page, providing access to otherwise hidden settings and further aspects of Mumble-Django. </p>

<p><img src="https://assets.digitalocean.com/articles/mumble-django-debian7/NAHcQCk.png" alt="Django Administration Window" /></p>

<ul>
<li><p><strong>Groups</strong> lets you assign dashboard users their permissions by allocating them to a group you create that has specific rights (if they aren't dashboard <em>Superusers</em>)</p></li>
<li><p><strong>Users</strong> provides another manual means of registering or approving new Mumble-Django admin accounts, and is the most basic but reliable way of adding new dashboard users. These are <strong>dashboard</strong> users, not chat users</p></li>
<li><p><strong>Mumble Servers</strong> lists separate connections that are detected through middleware such as ICE or D-Bus. There should be only one when following this tutorial</p></li>
<li><p><strong>Server instances</strong> shows all registered Murmur server instances. You can start, stop, and restart each one individually or together, and well as enable or disable their automatic boot</p></li>
<li><p><strong>Registration profiles</strong> contains options such as re-sending verification emails and manually activating user accounts</p></li>
<li><p><strong>Sites</strong> lets you change the dashboard domain name. Only change this if your new domain points to the server</p></li>
</ul>

<h3 id="step-4-—-registering-additional-dashboard-user-accounts">Step 4 — Registering Additional Dashboard User Accounts</h3>

<p>The <strong>Register</strong> button is located back on the initial home page (visible while signed out) and is where new users go to sign up for an account to use the dashboard. </p>

<p><img src="https://assets.digitalocean.com/articles/mumble-django-debian7/ZUl0H3O.png" alt="Register Button" /></p>

<p>Have new users register through the form on this button, and then click on the activation link in the email sent to their supplied email address.</p>

<p><span class="note">New users should check their account's spam folder for the activation email if needed. It will be from <strong>webmaster</strong> and have the subject <strong>Account verification</strong>.<br /></span></p>

<p>New users must be approved and given <strong>Staff status</strong> (and <strong>Superuser</strong> status if desired), before they can log in and access most of the administration features in the dashboard. </p>

<p>Access the Django Administration Window described in the previous section (<strong>Django Administration Page</strong>) and follow these steps to approve a new user:</p>

<ol>
<li>Click the <strong>Home</strong> link as shown in the previous section</li>
<li>Click the <strong>Users</strong> link on the root Django administration page</li>
<li>Click on the relevant new username in the next window
<img src="https://assets.digitalocean.com/articles/mumble-django-debian7/agzIsxr.png" alt="Click Username" /></li>
<li>Check the <strong>Staff status</strong> box, and potentially the <strong>Superuser status</strong> box, and provide any other details you see fit for the new user
<img src="https://assets.digitalocean.com/articles/mumble-django-debian7/7U4eMYP.png" alt="Permissions Check-Boxes" /></li>
<li>Click the blue <strong>Save</strong> button at the bottom right</li>
</ol>

<p><span class="note">This gives the new user the same Mumble-Django rights as the first Superuser account created initially in the configuration process (the <strong>sammy</strong> account in our example).<br /></span></p>

<p>The new user can now log in to the dashboard with full administration rights and help run your chat server from the dashboard.</p>

<h2 id="conclusion">Conclusion</h2>

<p>In this guide we configured ICE middleware to interface with a Murmur instance, installed and configured Mumble-Django and its subsidiary packages, and made it accessible online with the Apache web server. Finally we learned about some of the capabilities of the Mumble-Django dashboard. </p>

<p>Other areas covered in the tutorial:</p>

<ul>
<li>Confirm or troubleshoot if ICE is working through the use of <code>netstat</code>, <code>grep</code>, and <code>tail</code></li>
<li>Allow input on the<code>127.0.0.1</code> localhost address via <code>iptables</code> if required</li>
<li>Install <code>postfix</code> and configure it to locally send out emails</li>
</ul>

<p>Future steps could be to acquire and apply a domain name to the web server, set up Mumble-Django with a virtual hosts configuration, or add additional Murmur servers to the dashboard.</p>

    