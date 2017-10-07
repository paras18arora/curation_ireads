<link rel='stylesheet' href='digital_ocean.css'><br> <br> 
      <h3 id="introduction">Introduction</h3>

<p>MongoDB is a free and open-source NoSQL database. It is one of the most popular databases used in web applications today because it offers high performance, scalability, and lots of flexibility in database schema design. In this tutorial, you will learn how to install and run MongoDB on FreeBSD 10.1.</p>

<h2 id="prerequisites">Prerequisites</h2>

<p>To follow this tutorial, you need to have:</p>

<ul>
<li>A FreeBSD 10.1 server which is accessible over SSH</li>
<li>A user with <strong>root</strong> privileges; the default <strong>freebsd</strong> user on IndiaReads is fine</li>
<li>SSH key</li>
</ul>

<p>A FreeBSD Droplet requires an SSH Key for remote access. The <strong>freebsd</strong> user is automatically created, and your SSH key is added to this user account. A root password will not be emailed out for FreeBSD. For help on setting up an SSH Key, read <a href="https://indiareads/community/tutorials/how-to-configure-ssh-key-based-authentication-on-a-freebsd-server">How To Configure SSH Key-Based Authentication on a FreeBSD Server</a>.</p>

<p><span class="note"><strong>Note:</strong> Check out the <a href="https://indiareads/community/tutorial_series/getting-started-with-freebsd">Getting Started with FreeBSD</a> Tutorial Series for help on installing and using FreeBSD 10.1.<br /></span></p>

<h2 id="step-1-—-installing-the-package-management-tool">Step 1 — Installing the Package Management Tool</h2>

<p>Log into your FreeBSD 10.1 Droplet using the command:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">ssh freebsd@<span class="highlight">your_server_ip</span>
</li></ul></code></pre>
<p>FreeBSD uses a tool called <code>pkg</code> to manage binary packages. Update the repository catalogue by typing:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo pkg update -f
</li></ul></code></pre>
<h2 id="step-2-—-installing-mongodb">Step 2 — Installing MongoDB</h2>

<p>Now that <code>pkg</code> is ready to be used, install MongoDB and all its dependencies by running the following command:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo pkg install mongodb
</li></ul></code></pre>
<p>You might be prompted to update <code>pkg</code> first before installing <code>mongodb</code>. If prompted, press Y. The installation of MongoDB will automatically start after <code>pkg</code> is updated.</p>

<p>You will be shown a list of packages that are going to be installed and asked to confirm if you want to proceed. Press Y to begin the installation.</p>

<h2 id="step-3-—-allowing-mongodb-to-start-automatically-at-boot-time">Step 3 — Allowing MongoDB to Start Automatically At Boot Time</h2>

<p>To start MongoDB automatically at boot time, you need to edit the <code>/etc/rc.conf</code> file. You will need to use <code>sudo</code> because root privileges are required. If you want to use <code>nano</code>, you will need to install it with the following command:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo pkg install nano
</li></ul></code></pre>
<p>You might have to log out and log back in to get <code>nano</code> added to your default path.</p>

<p>Otherwise, you can use <code>vi</code>:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo vi /etc/rc.conf
</li></ul></code></pre>
<p>Add the following line at the end of the file to allow MongoDB's primary daemon to start automatically when your FreeBSD server is booting up:</p>
<pre class="code-pre "><code langs="">mongod_enable="YES"
</code></pre>
<h2 id="step-4-—-starting-mongodb">Step 4 — Starting MongoDB</h2>

<p>You can now reboot your server to start MongoDB automatically. If you don't want to do that, you can start MongoDB manually using the <code>service</code> command.</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo service mongod start
</li></ul></code></pre>
<p>MongoDB is up and running. </p>

<h2 id="step-5-—-configuring-mongodb">Step 5 — Configuring MongoDB</h2>

<p>Optionally, you can add configuration details to <code>/usr/local/etc/mongodb.conf</code> to customize MongoDB.</p>

<p>For example, to run on port <strong>9000</strong> instead of port <strong>27017</strong> (the default port), add the following to <code>mongodb.conf</code>:</p>
<div class="code-label " title="/usr/local/etc/mongodb.conf">/usr/local/etc/mongodb.conf</div><pre class="code-pre "><code langs="">net:
    port: <span class="highlight">9000</span>
</code></pre>
<p>Every time you modify <code>mongodb.conf</code>, you must restart MongoDB to enable the changes:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo service mongod restart
</li></ul></code></pre>
<p>Refer to <a href="http://docs.mongodb.org/v2.6/reference/configuration-options/">MongoDB Reference: Configuration File Options</a> for a complete list of options.</p>

<h2 id="step-6-—-verifying-the-installation">Step 6 — Verifying the Installation</h2>

<p>Connect to the database using the <code>mongo</code> shell:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo mongo
</li></ul></code></pre>
<p>If you changed the configuration to run MongoDB on a different port, run the following instead:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo mongo <span class="highlight">--port <your-port-number></span>
</li></ul></code></pre>
<p>If everything went well, you will see the following output:</p>
<pre class="code-pre "><code langs="">MongoDB shell version: 2.6.7
connecting to: test
Welcome to the MongoDB shell.
For interactive help, type "help".
For more comprehensive documentation, see
    http://docs.mongodb.org/
Questions? Try the support group
    http://groups.google.com/group/mongodb-user
> 
</code></pre>
<p>On a 32-bit FreeBSD server, you will also see the following warnings:</p>
<pre class="code-pre "><code langs="">Server has startup warnings: 
2015-05-13T19:01:49.548+0100 [initandlisten] 
2015-05-13T19:01:49.548+0100 [initandlisten] ** NOTE: This is a 32 bit MongoDB binary.
2015-05-13T19:01:49.548+0100 [initandlisten] **       32 bit builds are limited to less than 2GB of data (or less with --journal).
2015-05-13T19:01:49.548+0100 [initandlisten] **       Note that journaling defaults to off for 32 bit and is currently off.
2015-05-13T19:01:49.548+0100 [initandlisten] **       See http://dochub.mongodb.org/core/32bit
2015-05-13T19:01:49.548+0100 [initandlisten]
</code></pre>
<p>Though these warnings can be ignored in a development or test environment, it is recommended that you run production instances of MongoDB only on 64-bit servers.</p>

<h2 id="conclusion">Conclusion</h2>

<p>In this short tutorial, you learned how to use the package management tool to install MongoDB on your FreeBSD 10.1 server. To know more about what you can do with your instance of MongoDB, refer to the <a href="http://docs.mongodb.org/v2.6/">MongoDB 2.6 Manual</a>.</p>

    