<link rel='stylesheet' href='digital_ocean.css'><br> <br> 
      <h3 id="introduction">Introduction</h3>

<p>Sometimes you're on a network that's insecure or has an overly restrictive firewall, and you need to access a website. You want to make sure no one in the middle is watching the traffic.</p>

<p>One solution is a <a href="https://indiareads/community/tutorials/how-to-set-up-an-openvpn-server-on-ubuntu-14-04">VPN</a>, but many VPNs require special client software on your machine, which you may not have rights to install.</p>

<p>If all you need to secure is your web browsing, there is a simple alternative: a SOCKS 5 proxy tunnel.</p>

<p>A SOCKS proxy is basically an SSH tunnel in which specific applications forward their traffic down the tunnel to the server, and then on the server end, the proxy forwards the traffic out to the general Internet. Unlike a VPN, a SOCKS proxy has to be configured on an app by app basis on the client machine, but can be set up without any specialty client agents.</p>

<p>As long as you have a Droplet with SSH access, you can use it as a SOCKS proxy end point. In this tutorial we'll use a Ubuntu 14.04 Droplet as the proxy, and the Firefox web browser as the client application. By the end of this tutorial you should be able to browse websites securely through the tunnel.</p>

<h2 id="prerequisites">Prerequisites</h2>

<p>As mentioned above, the first thing needed is a server running any flavor of Linux, like Ubuntu 14.04, with <a href="https://indiareads/community/tutorials/how-to-connect-to-your-droplet-with-ssh">SSH access</a>.</p>

<ul>
<li>Deploy a server (this example uses Ubuntu 14.04)</li>
</ul>

<p>A little more setup is required on your own local machine. For this you'll need to download one or two pieces of software.</p>

<ul>
<li><a href="https://www.mozilla.org/en-US/firefox/new/">Firefox</a> web browser (everyone)</li>
<li><a href="http://www.chiark.greenend.org.uk/%7Esgtatham/putty/download.html">PuTTY</a> (Windows users)</li>
</ul>

<p>Firefox allows you to set the proxy for just Firefox instead of setting a system-wide proxy.</p>

<p>PuTTY is used to set up the proxy tunnel for Windows users. Users of Mac OS X or Linux have the tools to set up the tunnel pre-installed.</p>

<h2 id="step-1-mac-os-x-linux-—-setting-up-the-tunnel">Step 1 (Mac OS X/Linux) — Setting Up the Tunnel</h2>

<p>On your <strong>local computer</strong>, create an <a href="https://indiareads/community/tutorials/how-to-set-up-ssh-keys--2">SSH key</a>. If you already have an SSH key, you can use that one.</p>

<p>Though it’s good practice to give your SSH key a passphrase, for this tutorial we will actually leave the passphrase blank to avoid issues later on.</p>

<p>As you set up the key, make sure you add it to the authorized keys for the sudo user on the server (in this example, that's the <strong>sammy</strong> user).</p>

<p>Open a terminal program on your computer. On Mac OS X, this is Terminal in Applications > Utilities.</p>

<p>Set up the tunnel with this command:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">ssh -D <span class="highlight">8123</span> -f -C -q -N <span class="highlight">sammy</span>@<span class="highlight">example.com</span>
</li></ul></code></pre>
<p><strong>Explanation of arguments</strong></p>

<ul>
<li><code>-D</code>: Tells SSH that we want a SOCKS tunnel on the specified port number (you can choose a number between 1025-65536)</li>
<li><code>-f</code>: Forks the process to the background</li>
<li><code>-C</code>: Compresses the data before sending it</li>
<li><code>-q</code>: Uses quiet mode</li>
<li><code>-N</code>: Tells SSH that no command will be sent once the tunnel is up</li>
</ul>

<p>Be sure to replace <code><span class="highlight">sammy</span>@<span class="highlight">example.com</span></code> with your own sudo user and server IP address or domain name.</p>

<p>Once you enter the command, you'll immediately be brought to the command prompt again with no sign of success or failure; that's normal.</p>

<p>Verify that the tunnel is up and running with this command:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">ps aux | grep ssh
</li></ul></code></pre>
<p>You should see a line in the output like:</p>
<div class="code-label " title="Output">Output</div><pre class="code-pre "><code langs="">sammy    <span class="highlight">14345</span>   0.0  0.0  2462228    452   ??  Ss    6:43AM   0:00.00 ssh -D 8123 -f -C -q -N sammy@example.com
</code></pre>
<p>You can quit your terminal application and the tunnel will stay up. That is because we used the <code>-f</code> argument which put the SSH session into the background.</p>

<p><strong>Note:</strong> If you want to terminate the tunnel you'll have to grab the PID via <code>ps</code> and use the <code>kill</code> command, which we'll show you how to do later.</p>

<h2 id="step-1-windows-—-setting-up-the-tunnel">Step 1 (Windows) — Setting Up the Tunnel</h2>

<p>Open <a href="http://www.chiark.greenend.org.uk/%7Esgtatham/putty/download.html">PuTTY</a>.</p>

<p>If you haven't installed it yet, download PuTTY and save it where you like. PuTTY doesn't require admin rights to install; just download the <code>.exe</code> and run it.</p>

<p>Complete the following steps to set up the tunnel:</p>

<ol>
<li>From the <strong>Session</strong> section, add the <strong>Host Name (or IP address)</strong> of your server, and the SSH <strong>Port</strong> (typically 22) <img src="https://assets.digitalocean.com/articles/socks5/wXDz8J7.png" alt="Putty Sessions" /></li>
<li>On the left, navigate to: <strong>Connection > SSH > Tunnels</strong></li>
<li>Enter any <strong>Source port</strong> number between 1025-65536. In this example we've used port <span class="highlight">1337</span><img src="https://assets.digitalocean.com/articles/socks5/ZLPgf4V.png" alt="Putty Connection>SSH>Tunnel" /></li>
<li>Select the <strong>Dynamic</strong> radio button</li>
<li>Click the <strong>Add</strong> button</li>
<li>Go back to <strong>Session</strong> on the left</li>
<li>Add a name under <strong>Saved Sessions</strong> and click the <strong>Save</strong> button</li>
<li>Now click the <strong>Open</strong> button to make the connection</li>
<li>Enter your sudo username and server password to log in</li>
</ol>

<p>You can minimize the PuTTY window now, but don't close it. Your SSH connection should be open.</p>

<p><strong>Tip:</strong> You can save your sudo username (<strong>sammy</strong>) and SSH key for this same session by following the <a href="https://indiareads/community/tutorials/how-to-use-ssh-keys-with-putty-on-digitalocean-droplets-windows-users">PuTTY SSH Key instructions</a>. Then you won't have to enter your username and password every time you open the connection.</p>

<h2 id="step-2-configuring-firefox-to-use-the-tunnel">Step 2 - Configuring Firefox to Use the Tunnel</h2>

<p>Now that you have an SSH tunnel, it's time to configure Firefox to use that tunnel. Remember that for a SOCKS 5 tunnel to work, you have to use a local application that can take advantage of the tunnel; Firefox does the trick.</p>

<p>This step is the same for Windows, Mac OS X, and Linux.</p>

<p>Make sure you have the <strong>port number</strong> that you used in your SSH command or in PuTTY noted for this example. We've used <strong><span class="highlight">8123</span></strong> in the OS X / Linux example, and <strong><span class="highlight">1337</span></strong> in the Windows example so far, or you may have used a different port.</p>

<p>(The following steps were performed with Firefox version 39 but should work on other versions, though the locations of the options may be different.)</p>

<ol>
<li>In the upper right hand corner, click on the hamburger icon to access Firefox's menu: <img src="https://assets.digitalocean.com/articles/socks5/bjh8Dh1.png" alt="Firefox Preferences" /> </li>
<li>Click on the <strong>Preferences</strong> or <strong>Options</strong> icon</li>
<li>Navigate to the <strong>Advanced</strong> section</li>
<li>Click on the <strong>Network</strong> tab <img src="https://assets.digitalocean.com/articles/socks5/k4DKcdA.png" alt="Firefox Advanced Preferences" /></li>
<li>Click on the <strong>Settings</strong> button under the <strong>Connection</strong> heading. A new window will open</li>
<li>Select the radio button for <strong>Manual proxy configuration:</strong> <img src="https://assets.digitalocean.com/articles/socks5/70cwU1N.png" alt="Firefox Proxy Settings" /></li>
<li>Enter <strong>localhost</strong> for the <strong>SOCKS Host</strong></li>
<li>Enter the same <strong>Port</strong> number from your SSH connection; in the image you can see we have entered <strong><span class="highlight">1337</span></strong> to match the Windows instructions</li>
<li>Click the <strong>OK</strong> button to save and close your configuration</li>
</ol>

<p>Now, open another tab in Firefox and start browsing the web! You should be all set for secure browsing through your SSH tunnel.</p>

<p><strong>Optional:</strong> To verify that you are using the proxy, go back to the <strong>Network</strong> settings in Firefox. Try entering a different port number. Click <strong>OK</strong> to save the settings. Now if you try to browse the web, you should get an error message <strong>The proxy server is refusing connections</strong>.  This proves that Firefox is using the proxy and not just the default connection. Revert to the correct port number, and you should be able to browse again.</p>

<p><strong>Reverting to normal unsecured browsing in Firefox:</strong></p>

<p>When you are done needing the privacy of the SSH tunnel, go back to the <strong>Network</strong> proxy settings (<strong>Preferences > Advanced > Network > Settings</strong>) in Firefox.</p>

<p>Click on the radio button for <strong>Use system proxy settings</strong> and click <strong>OK</strong>. Firefox will now browse over your normal connection settings, which are likely unsecured.</p>

<p>If you are done using the tunnel you'll have to terminate the tunnel as well, which we cover in the next section.</p>

<p>If you plan on using the tunnel often you can leave it open for later use, but note that it might terminate on its own if it’s idle for too long, or if your computer goes to sleep or powers off.</p>

<h2 id="step-3-mac-os-x-linux-—-closing-the-tunnel">Step 3 (Mac OS X/Linux) — Closing the Tunnel</h2>

<p>Closing the tunnel will stop Firefox's ability to browse over the proxy.</p>

<p>The tunnel we created earlier on our local machine was sent to the background, so closing the terminal window you used to open the tunnel won't terminate it. </p>

<p>To terminate the tunnel we need to identify the process ID (PID) using the <code>ps</code> command, and then kill it using the <code>kill</code> command.</p>

<p>Let's search for all active <code>ssh</code> processes on our machine:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">ps aux |grep ssh
</li></ul></code></pre>
<p>Find the line that looks like the command you entered earlier to create the tunnel. Here's some sample output:</p>
<div class="code-label " title="Output">Output</div><pre class="code-pre "><code langs="">sammy    <span class="highlight">14345</span>   0.0  0.0  2462228    452   ??  Ss    6:43AM   0:00.00 ssh -D 8123 -f -C -q -N sammy@example.com
</code></pre>
<p>From the beginning of the line, in one of the first two columns, is a 3-5 digit number. This is the PID. Above, the sample PID of <span class="highlight">14345</span> is highlighted.</p>

<p>Now that you know what the PID is, you can use the <code>kill</code> command to bring the tunnel down. Use your own PID when you kill the process.</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo kill <span class="highlight">14345</span>
</li></ul></code></pre>
<p>Now, if you'd like to automate the connection process, go to Step 4.</p>

<h2 id="step-3-windows-—-closing-the-tunnel">Step 3 (Windows) — Closing the Tunnel</h2>

<p>Closing the tunnel will stop Firefox's ability to browse over the proxy.</p>

<p>Close the PuTTY window you used to create the tunnel. That's it!</p>

<p>In Windows there isn't an easy way to automate the connection process, but both PuTTY and Firefox can save the settings you've previously entered, so just open the connections again to use the tunnel again.</p>

<h2 id="step-4-mac-os-x-linux-—-creating-shortcuts-for-repeated-use">Step 4 (Mac OS X/Linux) — Creating Shortcuts for Repeated Use</h2>

<p>For OS X or Linux systems, we can make an alias or create a script to quickly create the tunnel for us. The following are two ways to automate the tunnel process.</p>

<p>Note: These shortcut methods both require passwordless/passphraseless SSH key authentication to the server!</p>

<h3 id="clickable-bash-script">Clickable BASH Script</h3>

<p>If you want an icon to double click and the tunnel just starts, we can create a simple BASH script to do the job.</p>

<p>We make the script set up the tunnel and launch Firefox, although you’ll still need to add the proxy settings manually in Firefox the first time.</p>

<p><strong>On OS X</strong>, the Firefox binary that we can launch from the command line is inside <code>Firefox.app</code>. Assuming the app is in the Applications folder, the binary will be found at <code>/Applications/Firefox.app/Contents/MacOS/firefox</code>.</p>

<p><strong>On Linux systems</strong>, if you installed Firefox via a repo or it's pre-installed, then its location should be <code>/usr/bin/firefox</code>. You can always use the <code>which firefox</code> command to find out where it is on your system.</p>

<p>In the script below replace the path to Firefox with the one that is appropriate for your system.</p>

<p>Using a text editor like <code>nano</code> create a new file:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">nano ~/socks5.sh
</li></ul></code></pre>
<p>Add the following lines:</p>
<div class="code-label " title="socks5.sh">socks5.sh</div><pre class="code-pre "><code langs="">#!/bin/bash
ssh -D <span class="highlight">8123</span> -f -C -q -N <span class="highlight">sammy@example.com</span>
<span class="highlight">/Applications/Firefox.app/Contents/MacOS/firefox</span> &
</code></pre>
<ul>
<li>Replace <code><span class="highlight">8123</span></code> with your desired port number (it should match what you put in Firefox)</li>
<li>Replace <code><span class="highlight">sammy@example.com</span></code> with your SSH user and hostname or IP</li>
<li>Replace <code><span class="highlight">/Applications/Firefox.app/Contents/MacOS/firefox</span></code> with the path to Firefox's binary</li>
</ul>

<p>Save your script. For nano, type <code>CONTROL + o</code>, and then to quit, type <code>CONTROL + x</code>.</p>

<p>Make the script executable, so that when you double click on it, it will execute. From the command line, enter this command to add execute permissions, using your own script path:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">chmod +x <span class="highlight">/path/to/socks5.sh</span>
</li></ul></code></pre>
<p>On OS X, you may have to perform an additional step to tell Mac OS X that a <code>.sh</code> file should be executed like a program and not be opened in an editor.</p>

<p>To do this, right click on your <code>socks5.sh</code> file and select <strong>Get Info</strong>.</p>

<p>Locate the section <strong>Open with:</strong> and if the disclosure triangle isn’t pointing down, click on it so you can see the dropdown menu. Xcode might be set as the default app. <img src="https://assets.digitalocean.com/articles/socks5/8TJ7dvX.png" alt="Get Info" /></p>

<p>Change it to <strong>Terminal.app</strong>. If Terminal.app isn’t listed, choose <strong>Other</strong>, and then navigate to <strong>Applications > Utilities > Terminal.app</strong>.</p>

<p>To open your SOCKS proxy now, just double click on the <code>socks.sh</code> file.</p>

<p>(After executing, the script won’t prompt for a password, and so it will silently fail if you previously set up your SSH key to require a passphrase.)</p>

<p>The script will open a terminal window, start the SSH connection, and launch Firefox. Feel free to close the terminal window. As long as you kept the proxy settings in Firefox, you can start browsing over your secure connection.</p>

<h3 id="command-line-alias">Command-Line Alias</h3>

<p>If you find yourself on the command line frequently and want to bring up the tunnel, you can create a BASH alias to do the job for you.</p>

<p>The hardest part of creating an alias is figuring out where to save the alias command. </p>

<p>Different Linux distributions and OS X releases save aliases in different places. The best bet is to look for one of the following files and search for <code>alias</code> to see where other aliases are currently being saved. Possibilities include</p>

<ul>
<li><code>~/.bashrc</code></li>
<li><code>~/.bash_aliases</code></li>
<li><code>~/.bash_profile</code></li>
<li><code>~/.profile</code></li>
</ul>

<p>Once you've located the correct file, add this alias below any you already have, or just at the end of the file.</p>
<div class="code-label " title=".bashrc">.bashrc</div><pre class="code-pre "><code langs="">alias socks5='ssh -D <span class="highlight">8123</span> -f -C -q -N <span class="highlight">sammy@example.com</span> && <span class="highlight">/Applications/Firefox.app/Contents/MacOS/firefox</span> &'
</code></pre>
<ul>
<li>Replace <code><span class="highlight">8123</span></code> with your desired port number (it should match what you put in Firefox)</li>
<li>Replace <code><span class="highlight">sammy@example.com</span></code> with your SSH user and hostname or IP</li>
<li>Replace <code><span class="highlight">/Applications/Firefox.app/Contents/MacOS/firefox</span></code> with the path to Firefox's binary</li>
</ul>

<p>Your aliases are only loaded when you start a new shell, so close your terminal session and start a new one.</p>

<p>Now when you type:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">socks5
</li></ul></code></pre>
<p>This alias sets up your tunnel, then launches Firefox for you and returns you to the command prompt.</p>

<p>Make sure Firefox is still set to use the proxy. You can now browse securely!</p>

<h2 id="step-5-optional-—-troubleshooting-getting-through-firewalls">Step 5 (Optional) — Troubleshooting: Getting Through Firewalls</h2>

<p>If your connection is working, you are good to go and can stop reading.</p>

<p>However, if you've discovered that you can't make an SSH connection out due to a restrictive firewall, then it's likely that port 22, which is required to create the tunnel, is being blocked.</p>

<p>If you can control the proxy server's SSH settings (with superuser access to a IndiaReads Droplet, you will be able to do this), you can set SSH to listen on a port other than 22.</p>

<p>Which port can you use that isn't being blocked?</p>

<p>Aside from the questionable plan of running port scans with a tool like <a href="https://www.grc.com/">ShieldsUP!</a> (questionable since your local network may view this as an attack), it's best to try ports that are commonly left open.</p>

<p>Ports that are often open include <strong>80</strong> (general web traffic) and <strong>443</strong> (SSL web traffic).</p>

<p>If your SSH server isn't serving web content, we can tell SSH to use one of these web ports to communicate over instead of the default port 22. <strong>443</strong> is the best choice since it's expected to have encrypted traffic on this port, and our SSH traffic will be encrypted.</p>

<p>From a non-firewalled location, SSH in to the IndiaReads Droplet you are using for the proxy. (Or, use the built in <a href="https://indiareads/community/tutorials/how-to-use-the-digitalocean-console-to-access-your-droplet">console</a> from the control panel, but you may not want to do this if you're afraid your web traffic is being watched.)</p>

<p>Edit the server's SSH settings:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo nano /etc/ssh/sshd_config
</li></ul></code></pre>
<p>Look for the line <code>Port 22</code>. </p>

<p>We can either replace <code>22</code> entirely (which is a good SSH hardening technique anyway), or add a second port for SSH to listen on.</p>

<p>We'll choose to have SSH listen on multiple ports, so we'll add a new line under <code>Port 22</code> that reads <code>Port 443</code>. Here is an example:</p>
<div class="code-label " title="sshd_config">sshd_config</div><pre class="code-pre "><code langs="">. . .

Port 22
<span class="highlight">Port 443</span>

. . .
</code></pre>
<p>Restart SSH so it will reload the SSH configuration you just edited.</p>

<p>Depending on your distribution, the name of the SSH server daemon may be different, but it's likely to be <code>ssh</code> or <code>sshd</code>. If one doesn't work try the other.</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo service ssh restart
</li></ul></code></pre>
<p>To verify that your new SSH port works, open a new shell (don't close the current one yet, just in case you accidentally locked yourself out) and SSH in using the new port.</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">ssh <span class="highlight">sammy@example.com</span> -p 443
</li></ul></code></pre>
<p>If you are successful, you can now log out of both shells and open your SSH tunnel using the new port.</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">ssh -D <span class="highlight">8123</span> -f -C -q -N <span class="highlight">sammy@example.com</span> -p 443
</li></ul></code></pre>
<p>That's it! The Firefox settings will be exactly the same since they don't depend on the SSH port, just the tunnel port (<span class="highlight">8123</span> above).</p>

<h2 id="conclusion">Conclusion</h2>

<p>Open a SOCKS 5 tunnel to browse through a secure SSH tunnel whenever you need a lightweight way to access the web safe from prying eyes.</p>

    