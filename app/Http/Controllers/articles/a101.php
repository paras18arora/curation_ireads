<link rel='stylesheet' href='digital_ocean.css'><br> <br> 
      <h3 id="introduction">Introduction</h3>

<p><a href="http://ajenti.org">Ajenti</a> is an open source, web-based control panel that can be used for a large variety of server management tasks. It can install packages and run commands, and you can view basic server information such as RAM in use, free disk space, etc. All this can be accessed from a web browser. Optionally, an add-on package called Ajenti V allows you to manage multiple websites from the same control panel.</p>

<p>In this tutorial we will be installing the Ajenti control panel for server management and the Ajenti V add-on module that allows the creations of websites and email accounts from inside the panel.</p>

<p><img src="https://assets.digitalocean.com/articles/ajenti_ajenti_v_ubuntu1404/ajenti-control-panel.png" alt="Ajenti Control Panel Homepage" /></p>

<h3 id="prerequisites">Prerequisites</h3>

<p>Before you can install Ajenti, you need:</p>

<ul>
<li>Registered domain name</li>
<li>Clean Ubuntu 14.04 Droplet configured with the host name <code>panel.<span class="highlight">example.com</span></code> (<a href="https://indiareads/community/tutorials/how-to-set-up-a-host-name-with-digitalocean">How To Set Up a Host Name with IndiaReads</a> explains how to set this up.)</li>
<li>A non-root user with sudo privileges (<a href="https://indiareads/community/tutorials/initial-server-setup-with-ubuntu-14-04">Initial Server Setup with Ubuntu 14.04</a> explains how to set this up.)</li>
</ul>

<p>All the commands in this tutorial should be run as a non-root user. If root access is required for the command, it will be preceded by <code>sudo</code>. <a href="https://indiareads/community/tutorials/initial-server-setup-with-ubuntu-14-04">Initial Server Setup with Ubuntu 14.04</a> explains how to add users and give them sudo access.</p>

<h2 id="installing-ajenti">Installing Ajenti</h2>

<p>In this step, we will install the Ajenti core panel. To begin, <a href="https://indiareads/community/tutorials/how-to-connect-to-your-droplet-with-ssh">connect to your server with SSH</a>. </p>

<p>On your server, as a user with sudo access, first add the repository key. This is used to validate the sources of the Ajenti packages you will be installing.</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">wget http://repo.ajenti.org/debian/key -O- | sudo apt-key add -
</li></ul></code></pre>
<p>Then add the actual repository to your sources list:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">echo "deb http://repo.ajenti.org/ng/debian main main ubuntu" | sudo tee -a /etc/apt/sources.list
</li></ul></code></pre>
<p>Now you can update your packages and begin the install process by running:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo apt-get update && sudo apt-get install ajenti
</li></ul></code></pre>
<p>When it prompts you to continue, type <code>Y</code> and press <code>ENTER</code>. The install process may take a few minutes. After the process is over, start the Ajenti server:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo service ajenti restart
</li></ul></code></pre>
<p>If all goes well, the last line in your console should say <code>* started</code>. You can continue to the next step.</p>

<h2 id="configuring-ajenti">Configuring Ajenti</h2>

<p>Here we will make a few important starting modifications to your control panel. Open a web browser and browse to <code>https://panel.<span class="highlight">your_domain_name</span>:8000/</code>. If you did not configure a registered domain name to point to your Droplet as panel.<span class="highlight">your<em>domain</em>name</span>, you will need to go to <code>https://<span class="highlight">your_server_ip</span>:8000/</code> instead.</p>

<p><span class="note"><strong>Note</strong>: You will get a privacy error (a red lock in Chrome). This is completely normal because Ajenti uses a self-signed certificate by default. There is no reason to change this, and your connection is still secure.<br /></span></p>

<p>In Google Chrome, click the <strong>Advanced</strong> link on the Privacy error page, and then click <code>Proceed to panel.<span class="highlight">example.com</span>.</code>. Once again, this is not unsafe.</p>

<p>Log in with these default credentials:</p>

<p><strong>Username</strong>: root</p>

<p><strong>Password</strong>: admin</p>

<p>You will now be inside your new Ajenti control panel. </p>

<p><img src="https://assets.digitalocean.com/articles/ajenti_ajenti_v_ubuntu1404/ajenti-control-panel-2.png" alt="Ajenti Control Panel" /></p>

<p>Before we do anything else, click the <strong>Password</strong> option in the sidebar. Under old password type <code>admin</code> and then set a new password. From this moment on, to log into your control panel you will use:</p>

<p><strong>Username</strong>: root</p>

<p><strong>Password</strong>: <span class="highlight">your<em>new</em>password</span></p>

<p>Now click the <strong>Configure</strong> option in the left sidebar, it will be right above <strong>Password</strong>. Scroll down to the bottom, and click the <strong>Restart</strong> button. When it prompts you to restart Ajenti, click <strong>OK</strong>. This is restarting the Ajenti service. While it is doing so, your browser will display <code>Reconnecting...</code>. If this goes on for more than a minute, refresh your browser page.</p>

<p>After it restarts, log in with your new credentials and proceed to the next step.</p>

<h2 id="customizing-ajenti">Customizing Ajenti</h2>

<p>The main page in Ajenti is the dashboard, and it can be customized to serve lots of useful and relevant information in an easy to read manner. By default a few widgets are already shown such as CPU Usage and Uptime. More widgets can easily be added by clicking the <strong>Add Widget</strong> button in the top right corner of the screen.</p>

<p><img src="https://assets.digitalocean.com/articles/ajenti_ajenti_v_ubuntu1404/ajenti-add-widgets.png" alt="Add Widgets Menu" /></p>

<h2 id="plugins">Plugins</h2>

<p>Ajenti already has a lot of functionality built in by default, but if you want even more settings and configurable items in your panel, you can check out the <strong>Plugins</strong> section. Some plugins are enabled by default, while others aren't, usually due to unsatisfied dependancies. </p>

<p>You can install disabled plugins by clicking on them in the <strong>Plugins</strong> menu and pressing the button next to the dependency it requires. Otherwise, if you later install an application manually and Ajenti has a plugin for, you can restart Ajenti and the corresponding menu should appear next time you log in.</p>

<h2 id="system-management">System Management</h2>

<p>Under the <strong>System</strong> section in the sidebar, there's a plethora of configurable items to choose from. You can manage hard drives with the <strong>Filesystems</strong> menu, change the nameservers of your Droplet in <strong>Nameservers</strong>, add any packages and applications you'd need in the <strong>Packages</strong> section, and much more.</p>

<p>Filesystems menu:<br />
<img src="https://assets.digitalocean.com/articles/ajenti_ajenti_v_ubuntu1404/ajenti-filesystems.png" alt="Filesystems" /></p>

<p>Nameservers menu:<br />
<img src="https://assets.digitalocean.com/articles/ajenti_ajenti_v_ubuntu1404/ajenti-nameservers.png" alt="Nameservers" /></p>

<p>Users menu:<br />
<img src="https://assets.digitalocean.com/articles/ajenti_ajenti_v_ubuntu1404/ajenti-users.png" alt="Users" /></p>

<p>Packages menu:<br />
<img src="https://assets.digitalocean.com/articles/ajenti_ajenti_v_ubuntu1404/ajenti-packages.png" alt="Packages" /></p>

<h2 id="installing-ajenti-v-to-setup-a-website">Installing Ajenti V to Setup a Website</h2>

<p>Optionally, you can now install Ajenti V, which will allow you to make a website. In your Ajenti control panel on the left sidebar, click the <strong>Terminal</strong> option. It's located under the <strong>Tools</strong> section. This terminal functions as a terminal emulator in your browser for direct access to your server. Click <strong>+ New</strong> at the top of the screen, and click the middle of the empty black box that appears. This will open up the terminal. It may take a moment to load.</p>

<p><span class="note"><strong>Note:</strong> Commands run in the Ajenti terminal will be run as the root user.<br /></span></p>

<p>Towards the bottom of the screen there is a box labeled <strong>Paste here</strong>. Click inside that and paste the following command into that box:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">apt-get install ajenti-v ajenti-v-nginx ajenti-v-mysql ajenti-v-php-fpm ajenti-v-mail ajenti-v-nodejs php5-mysql
</li></ul></code></pre>
<p>Then press <strong>ENTER</strong>. When prompted whether or not to install the packages, type <strong>Y</strong>, and then press <strong>ENTER</strong> again. Some popups may appear on the screen such as this:</p>

<p><img src="https://assets.digitalocean.com/articles/ajenti_ajenti_v_ubuntu1404/ajenti-v-installation.png" alt="Ajenti V Installation" /></p>

<p>For prompts such as these, just press <strong>ENTER</strong> for the options that are preselected. No configuration is required. MySQL may ask you multiple times to enter a root password. Press <strong>ENTER</strong> when it asks to keep the current password, which is fine for the purposes of this tutorial.</p>

<p>When it finishes, click the <strong>X</strong> next to <strong>Terminal 0</strong> at the top of the screen to return to the home. Go back to the <strong>Configure</strong> menu from the sidebar, and restart Ajenti with the button at the bottom of the screen. You will need to log in again.</p>

<p>When Ajenti restarts, you should see a <strong>Web</strong> section in the sidebar and a <strong>Websites</strong> option immediately below that. From the <strong>Websites</strong> view you can add and manage websites as well as monitor your configuration to make sure everything is working properly.</p>

<p><img src="https://assets.digitalocean.com/articles/ajenti_ajenti_v_ubuntu1404/ajenti-websites.png" alt="The Websites menu" /></p>

<h2 id="conclusion">Conclusion</h2>

<p>Ajenti and Ajenti V are now installed on your server. To learn how to use some of their features, check out the following tutorials:</p>

<ul>
<li><a href="https://indiareads/community/tutorials/creating-a-website-and-an-email-account-on-ajenti-v">Creating a Website and an Email Account on Ajenti V</a></li>
<li><a href="https://indiareads/community/tutorials/installing-the-rainloop-email-client-on-ajenti-v">Installing the RainLoop Email Client on Ajenti V</a></li>
<li><a href="https://indiareads/community/tutorials/installing-wordpress-on-ajenti-v">Installing WordPress on Ajenti V</a></li>
</ul>

    