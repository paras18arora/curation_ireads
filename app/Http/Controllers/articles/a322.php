<link rel='stylesheet' href='digital_ocean.css'><br><img src=https://community-cdn-digitalocean-com.global.ssl.fastly.net/assets/tutorials/images/large/MongoDB_Install_twitter_mostov.png?1462917741/> <br> 
      <h3 id="introduction">Introduction</h3>

<p>MongoDB is a free and open-source NoSQL document database used commonly in modern web applications. This tutorial will help you set up MongoDB on your server for a production application environment.</p>

<p><span class="note"><strong>Note:</strong> MongoDB can be installed automatically on your Droplet by adding <a href="http://do.co/1C60X0a">this script</a> to its User Data when launching it. Check out <a href="https://indiareads/community/tutorials/an-introduction-to-droplet-metadata">this tutorial</a> to learn more about Droplet User Data.<br /></span></p>

<h2 id="prerequisites">Prerequisites</h2>

<p>To follow this tutorial, you will need:</p>

<ul>
<li><p>One Ubuntu 14.04 Droplet.</p></li>
<li><p>A sudo non-root user, which you can set up by following this <a href="https://indiareads/community/tutorials/initial-server-setup-with-ubuntu-14-04">initial server setup tutorial</a>.</p></li>
</ul>

<h2 id="step-1-—-importing-the-public-key">Step 1 — Importing the Public Key</h2>

<p>In this step, we will import the MongoDB GPG public key.</p>

<p>MongoDB is already included in Ubuntu package repositories, but the official MongoDB repository provides most up-to-date version and is the recommended way of installing the software. Ubuntu ensures the authenticity of software packages by verifying that they are signed with GPG keys, so we first have to import they key for the official MongoDB repository.</p>

<p>To do so, execute:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo apt-key adv --keyserver hkp://keyserver.ubuntu.com:80 --recv 7F0CEB10
</li></ul></code></pre>
<p>After successfully importing the key you will see:</p>
<div class="code-label " title="Output">Output</div><pre class="code-pre "><code langs="">gpg: Total number processed: 1
gpg:               imported: 1  (RSA: 1)
</code></pre>
<h2 id="step-2-—-creating-a-list-file">Step 2 — Creating a List File</h2>

<p>Next, we have to add the MongoDB repository details so APT will know where to download the packages from.</p>

<p>Issue the following command to create a list file for MongoDB.</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">echo "deb http://repo.mongodb.org/apt/ubuntu "$(lsb_release -sc)"/mongodb-org/3.0 multiverse" | sudo tee /etc/apt/sources.list.d/mongodb-org-3.0.list
</li></ul></code></pre>
<p>After adding the repository details, we need to update the packages list.</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo apt-get update
</li></ul></code></pre>
<h2 id="step-3-—-installing-and-verifying-mongodb">Step 3 — Installing and Verifying MongoDB</h2>

<p>Now we can install the MongoDB package itself.</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo apt-get install -y mongodb-org
</li></ul></code></pre>
<p>This command will install several packages containing latest stable version of MongoDB along with helpful management tools for the MongoDB server. </p>

<p>After package installation MongoDB will be automatically started. You can check this by running the following command.</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">service mongod status
</li></ul></code></pre>
<p>If MongoDB is running, you'll see an output like this (with a different process ID).</p>
<div class="code-label " title="Output">Output</div><pre class="code-pre "><code langs="">mongod start/running, process 1611
</code></pre>
<p>You can also stop, start, and restart MongoDB using the <code>service</code> command (e.g. <code>service mongod stop</code>, <code>service mongod start</code>).</p>

<h2 id="conclusion">Conclusion</h2>

<p>You can find more in-depth instructions regarding MongoDB installation and configuration in <a href="https://indiareads/community/search?q=mongodb">these IndiaReads community articles</a>.</p>

    