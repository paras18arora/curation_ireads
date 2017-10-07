<link rel='stylesheet' href='digital_ocean.css'><br><img src=https://community-cdn-digitalocean-com.global.ssl.fastly.net/assets/tutorials/images/large/MongoDB_Install_twitter_mostov.png?1462917690/> <br> 
      <h3 id="introduction">Introduction</h3>

<p>MongoDB is a free and open-source NoSQL document database used commonly in modern web applications. This tutorial will help you set up MongoDB on your server for a production application environment.</p>

<p>Because the official Ubuntu 16.04 MongoDB packages have not yet been published by the MongoDB maintainers, this tutorial will use the Ubuntu 14.04 packages. This involves following an additional step to configure MongoDB as a <code>systemd</code> service that will automatically start on boot; the outdated packages don't do this automatically because <a href="https://indiareads/community/tutorials/what-s-new-in-ubuntu-16-04#the-systemd-init-system">Ubuntu 14.04 uses a different init system than 16.04</a>.</p>

<p><span class="note"><strong>Note:</strong> Upgrading to the 16.04 packages when they are released may require removing the newly created unit file (from step 3) to avoid conflict with the one supplied by the packages. Apart from that, there should be no problems with upgrading to official packages further on. If you are unsure, we recommend waiting for the official packages instead. This text will be updated after they are released.<br /></span></p>

<h2 id="prerequisites">Prerequisites</h2>

<p>To follow this tutorial, you will need:</p>

<ul>
<li>One Ubuntu 16.04 Droplet set up by following this <a href="https://indiareads/community/tutorials/initial-server-setup-with-ubuntu-16-04">initial server setup tutorial</a>, including a sudo non-root user</li>
</ul>

<h2 id="step-1-—-adding-the-mongodb-repository">Step 1 — Adding the MongoDB Repository</h2>

<p>MongoDB is already included in Ubuntu package repositories, but the official MongoDB repository provides most up-to-date version and is the recommended way of installing the software. In this step, we will add this official repository to our server.</p>

<p>Ubuntu ensures the authenticity of software packages by verifying that they are signed with GPG keys, so we first have to import they key for the official MongoDB repository.</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo apt-key adv --keyserver hkp://keyserver.ubuntu.com:80 --recv EA312927
</li></ul></code></pre>
<p>After successfully importing the key, you will see:</p>
<div class="code-label " title="Output">Output</div><pre class="code-pre "><code langs="">gpg: Total number processed: 1
gpg:               imported: 1  (RSA: 1)
</code></pre>
<p>Next, we have to add the MongoDB repository details so <code>apt</code> will know where to download the packages from.</p>

<p>Issue the following command to create a list file for MongoDB.</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo echo "deb http://repo.mongodb.org/apt/ubuntu <span class="highlight">trusty</span>/mongodb-org/3.2 multiverse" | sudo tee /etc/apt/sources.list.d/mongodb-org-3.2.list
</li></ul></code></pre>
<p>After adding the repository details, we need to update the packages list.</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo apt-get update
</li></ul></code></pre>
<h2 id="step-2-—-installing-and-verifying-mongodb">Step 2 — Installing and Verifying MongoDB</h2>

<p>Now we can install the MongoDB package itself.</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo apt-get install -y --allow-unauthenticated mongodb-org
</li></ul></code></pre>
<p><span class="note"><strong>Note:</strong> MongoDB packages we are using do not meet signature strength standards that Ubuntu 16.04 expects and must be installed with additional <code>--allow-unauthenticated</code> switch. <br /></span></p>

<p>This command will install several packages containing latest stable version of MongoDB along with helpful management tools for the MongoDB server. </p>

<p>In order to properly launch MongoDB as a service on Ubuntu 16.04, we additionally need to create a unit file describing the service. A <em>unit file</em> tells <code>systemd</code> how to manage a resource. The most common unit type is a <em>service</em>, which determines how to start or stop the service, when should it be automatically started at boot, and whether it is dependent on other software to run.</p>

<p>We'll create a unit file to manage the MongoDB service. Create a configuration file named <code>mongodb.service</code> in the <code>/etc/systemd/system</code> directory using <code>nano</code> or your favorite text editor.</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo nano /etc/systemd/system/mongodb.service
</li></ul></code></pre>
<p>Paste in the following contents, then save and close the file.</p>
<div class="code-label " title="/etc/systemd/system/mongodb.service">/etc/systemd/system/mongodb.service</div><pre class="code-pre "><code langs="">[Unit]
Description=High-performance, schema-free document-oriented database
After=network.target

[Service]
User=mongodb
ExecStart=/usr/bin/mongod --quiet --config /etc/mongod.conf

[Install]
WantedBy=multi-user.target
</code></pre>
<p>This file has a simple structure:</p>

<ul>
<li><p>The <strong>Unit</strong> section contains the overview (e.g. a human-readable description for MongoDB service) as well as dependencies that must be satisfied before the service is started. In our case, MongoDB depends on networking already being available, hence <code>network.target</code> here.</p></li>
<li><p>The <strong>Service</strong> section how the service should be started. The <code>User</code> directive specifies that the server will be run under the <code>mongodb</code> user, and the <code>ExecStart</code> directive defines the startup command for MongoDB server.</p></li>
<li><p>The last section, <strong>Install</strong>, tells <code>systemd</code> when the service should be automatically started. The <code>multi-user.target</code> is a standard system startup sequence, which means the server will be automatically started during boot.   </p></li>
</ul>

<p>Next, start the newly created service with <code>systemctl</code>.</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo systemctl start mongodb
</li></ul></code></pre>
<p>While there is no output to this command, you can also use <code>systemctl</code> to check that the service has started properly.</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo systemctl status mongodb
</li></ul></code></pre><div class="code-label " title="Output">Output</div><pre class="code-pre "><code langs=""><span class="highlight">● mongodb.service</span> - High-performance, schema-free document-oriented database
   Loaded: loaded (/etc/systemd/system/mongodb.service; enabled; vendor preset: enabled)
   Active: <span class="highlight">active</span> (running) since Mon 2016-04-25 14:57:20 EDT; 1min 30s ago
 Main PID: 4093 (mongod)
    Tasks: 16 (limit: 512)
   Memory: 47.1M
      CPU: 1.224s
   CGroup: /system.slice/mongodb.service
           └─4093 /usr/bin/mongod --quiet --config /etc/mongod.conf
</code></pre>
<p>The last step is to enable automatically starting MongoDB when the system starts.</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo systemctl enable mongodb
</li></ul></code></pre>
<p>The MongoDB server now configured and running, and you can manage the MongoDB service using the <code>systemctl</code> command (e.g. <code>sudo systemctl mongodb stop</code>, <code>sudo systemctl mongodb start</code>).</p>

<h2 id="step-3-—-adjusting-the-firewall-optional">Step 3 — Adjusting the Firewall (Optional)</h2>

<p>Assuming you have followed the <a href="https://indiareads/community/tutorials/initial-server-setup-with-ubuntu-16-04">initial server setup tutorial</a> instructions to enable the firewall on your server, MongoDB server will be inaccessible from the internet. </p>

<p>If you intend to use the MongoDB server only locally with applications running on the same server, it is a recommended and secure setting. However, if you would like to be able to connect to your MongoDB server from the internet, we have to allow the incoming connections in <code>ufw</code>.</p>

<p>To allow access to MongoDB on its default port <code>27017</code> from everywhere, you could use <code>sudo ufw allow <span class="highlight">27017</span></code>. However, enabling internet access to MongoDB server on a default installation gives unrestricted access to the whole database server.</p>

<p>in most cases, MongoDB should be accessed only from certain trusted locations, such as another server hosting an application. To accomplish this task, you can allow access on MongoDB's default port while specifying the IP address of another server that will be explicitly allowed to connect. </p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo ufw allow from <span class="highlight">your_other_server_ip</span>/32 to any port <span class="highlight">27017</span>  
</li></ul></code></pre>
<p>You can verify the change in firewall settings with <code>ufw</code>.</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo ufw status
</li></ul></code></pre>
<p>You should see traffic to <code>27017</code> port allowed in the output.If you have decided to allow only a certain IP address to connect to MongoDB server, the IP address of the allowed location will be listed instead of <em>Anywhere</em> in the output.</p>
<div class="code-label " title="Output">Output</div><pre class="code-pre "><code langs="">Status: active

To                         Action      From
--                         ------      ----
<span class="highlight">27017                      ALLOW       Anywhere</span>
OpenSSH                    ALLOW       Anywhere
<span class="highlight">27017 (v6)                 ALLOW       Anywhere (v6)</span>
OpenSSH (v6)               ALLOW       Anywhere (v6)
</code></pre>
<p>More advanced firewall settings for restricting access to services are described in <a href="https://indiareads/community/tutorials/ufw-essentials-common-firewall-rules-and-commands">UFW Essentials: Common Firewall Rules and Commands</a>.</p>

<h2 id="conclusion">Conclusion</h2>

<p>You can find more in-depth instructions regarding MongoDB installation and configuration in <a href="https://indiareads/community/search?q=mongodb">these IndiaReads community articles</a>. </p>

    