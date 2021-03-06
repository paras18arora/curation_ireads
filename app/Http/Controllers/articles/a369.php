<link rel='stylesheet' href='digital_ocean.css'><br><img src=https://community-cdn-digitalocean-com.global.ssl.fastly.net/assets/tutorials/images/large/ParseServer-twitter.png?1455041781/> <br> 
      <h3 id="introduction">Introduction</h3>

<p>Parse is a Mobile Backend as a Service platform, owned by Facebook since 2013.  In January of 2016, Parse <a href="http://blog.parse.com/announcements/moving-on/">announced</a> that its hosted services would shut down in January of 2017.</p>

<p>In order to help its users transition away from the service, Parse has released an open source version of its backend, called <strong>Parse Server</strong>, which can be deployed to environments running Node.js and MongoDB.</p>

<p>This guide supplements the official documentation with detailed instructions for installing Parse Server on an Ubuntu 14.04 system, such as a IndiaReads Droplet.  It is intended first and foremost as a starting point for Parse developers who are considering migrating their applications, and should be read in conjunction with the official <a href="https://parse.com/docs/server/guide">Parse Server Guide</a>.</p>

<h2 id="prerequisites">Prerequisites</h2>

<p>This guide assumes that you have a clean Ubuntu 14.04 system, configured with a non-root user with <code>sudo</code> privileges for administrative tasks.  You may wish to review the guides in the <a href="https://indiareads/community/tutorial_series/new-ubuntu-14-04-server-checklist">New Ubuntu 14.04 Server Checklist</a> series.</p>

<p>Additionally, your system will need a running instance of MongoDB.  You can start by working through <a href="https://indiareads/community/tutorials/how-to-install-mongodb-on-ubuntu-14-04">How to Install MongoDB on Ubuntu 14.04</a>.  MongoDB can also be installed automatically on a new Droplet by adding <a href="http://do.co/1C60X0a">this script</a> to its User Data when creating it. Check out <a href="https://indiareads/community/tutorials/an-introduction-to-droplet-metadata">this tutorial</a> to learn more about Droplet User Data.</p>

<p>Once your system is configured with a <code>sudo</code> user and MongoDB, return to this guide and continue.</p>

<h2 id="step-1-—-install-node-js-and-development-tools">Step 1 — Install Node.js and Development Tools</h2>

<p>Begin by changing the current working path to your <code>sudo</code> user's home directory:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">cd ~
</li></ul></code></pre>
<p><a href="https://github.com/nodesource/distributions">NodeSource</a> offers an Apt repository for Debian and Ubuntu Node.js packages.  We'll use it to install Node.js.  NodeSource offers an installation script for the the latest stable release (v5.5.0 at the time of this writing), which can be found in the <a href="https://github.com/nodesource/distributions#installation-instructions">installation instructions</a>.  Download the script with <code>curl</code>:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">curl -sL https://deb.nodesource.com/<span class="highlight">setup_5.x</span> -o nodesource_setup.sh
</li></ul></code></pre>
<p>You can review the contents of this script by opening it with <code>nano</code>, or your text editor of choice:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">nano ./nodesource_setup.sh
</li></ul></code></pre>
<p>Next, run <code>nodesource_setup.sh</code>.  The <code>-E</code> option to <code>sudo</code> tells it to preserve the user's environment variables so that they can be accessed by the script:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo -E bash ./nodesource_setup.sh
</li></ul></code></pre>
<p>Once the script has finished, NodeSource repositories should be available on the system.  We can use <code>apt-get</code> to install the <code>nodejs</code> package.  We'll also install the <code>build-essential</code> metapackage, which provides a range of development tools that may be useful later, and the Git version control system for retrieving projects from GitHub:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo apt-get install -y nodejs build-essential git
</li></ul></code></pre>
<h2 id="step-2-—-install-an-example-parse-server-app">Step 2 — Install an Example Parse Server App</h2>

<p>Parse Server is designed to be used in conjunction with <strong>Express</strong>, a popular web application framework for Node.js which allows middleware components conforming to a defined API to be mounted on a given path.  The <a href="https://github.com/ParsePlatform/parse-server-example.git">parse-server-example</a> repository contains a stubbed-out example implementation of this pattern.</p>

<p>Retrieve the repository with <code>git</code>:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">git clone https://github.com/ParsePlatform/parse-server-example.git
</li></ul></code></pre>
<p>Enter the <code>parse-server-example</code> directory you just cloned:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">cd ~/parse-server-example
</li></ul></code></pre>
<p>Use <code>npm</code> to install dependencies, including <code>parse-server</code>, in the current directory:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">npm install
</li></ul></code></pre>
<p><code>npm</code> will fetch all of the modules required by <code>parse-server</code> and store them in <code>~/parse-server-example/node_modules</code>.</p>

<h2 id="step-3-—-test-the-sample-application">Step 3 — Test the Sample Application</h2>

<p>Use <code>npm</code> to start the service.  This will run a command defined in the <code>start</code> property of <code>package.json</code>.  In this case, it runs <code>node index.js</code>:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">npm start
</li></ul></code></pre><pre class="code-pre "><code langs=""><div class="secondary-code-label " title="Output">Output</div>> parse-server-example@1.0.0 start /home/<span class="highlight">sammy</span>/parse-server-example
> node index.js

DATABASE_URI not specified, falling back to localhost.
parse-server-example running on port 1337.
</code></pre>
<p>You can terminate the running application at any time by pressing <strong>Ctrl-C</strong>.</p>

<p>The Express app defined in <code>index.js</code> will pass HTTP requests on to the <code>parse-server</code> module, which in turn communicates with your MongoDB instance and invokes functions defined in <code>~/parse-server-example/cloud/main.js</code>.</p>

<p>In this case, the endpoint for Parse Server API calls defaults to:</p>

<p><code>http://<span class="highlight">your_server_IP</span>/parse</code></p>

<p>In another terminal, you can use <code>curl</code> to test this endpoint.  Make sure you're logged into your server first, since these commands reference <code>localhost</code> instead of a specific IP address.</p>

<p>Create a record by sending a <code>POST</code> request with an <code>X-Parse-Application-Id</code> header to identify the application, along with some data formatted as JSON:</p>
<pre class="code-pre "><code langs="">curl -X POST \
  -H "X-Parse-Application-Id: myAppId" \
  -H "Content-Type: application/json" \
  -d '{"score":1337,"playerName":"Sammy","cheatMode":false}' \
  http://localhost:1337/parse/classes/GameScore
</code></pre><pre class="code-pre "><code langs=""><div class="secondary-code-label " title="Output">Output</div>{"objectId":"<span class="highlight">fu7t4oWLuW</span>","createdAt":"<span class="highlight">2016-02-02T18:43:00.659Z</span>"}
</code></pre>
<p>The data you sent is stored in MongoDB, and can be retrieved by using <code>curl</code> to send a <code>GET</code> request:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">curl -H "X-Parse-Application-Id: myAppId" http://localhost:1337/parse/classes/GameScore
</li></ul></code></pre><pre class="code-pre "><code langs=""><div class="secondary-code-label " title="Output">Output</div>{"results":[{"objectId":"GWuEydYCcd","score":1337,"playerName":"Sammy","cheatMode":false,"updatedAt":"2016-02-02T04:04:29.497Z","createdAt":"2016-02-02T04:04:29.497Z"}]}
</code></pre>
<p>Run a function defined in <code>~/parse-server-example/cloud/main.js</code>:</p>
<pre class="code-pre "><code langs="">curl -X POST \
  -H "X-Parse-Application-Id: myAppId" \
  -H "Content-Type: application/json" \
  -d '{}' \
  http://localhost:1337/parse/functions/hello
</code></pre><pre class="code-pre "><code langs=""><div class="secondary-code-label " title="Output">Output</div>{"result":"Hi"}
</code></pre>
<h2 id="step-4-—-configure-sample-application">Step 4 — Configure Sample Application</h2>

<p>In your original terminal, press <strong>Ctrl-C</strong> to stop the running version of the Parse Server application.</p>

<p>As written, the sample script can be configured by the use of six <a href="https://indiareads/community/tutorials/how-to-read-and-set-environmental-and-shell-variables-on-a-linux-vps">environment variables</a>:</p>

<table class="pure-table"><thead>
<tr>
<th>Variable</th>
<th>Description</th>
</tr>
</thead><tbody>
<tr>
<td><code>DATABASE_URI</code></td>
<td>A MongoDB connection URI, like <code>mongodb://localhost:27017/dev</code></td>
</tr>
<tr>
<td><code>CLOUD_CODE_MAIN</code></td>
<td>A path to a file containing <a href="https://parse.com/docs/cloudcode/guide">Parse Cloud Code functions</a>, like <code>cloud/main.js</code></td>
</tr>
<tr>
<td><code>APP_ID</code></td>
<td>A string identifier for your app, like <code>myAppId</code></td>
</tr>
<tr>
<td><code>MASTER_KEY</code></td>
<td>A secret master key which allows you to bypass all of the app's security mechanisms</td>
</tr>
<tr>
<td><code>PARSE_MOUNT</code></td>
<td>The path where the Parse Server API should be served, like <code>/parse</code></td>
</tr>
<tr>
<td><code>PORT</code></td>
<td>The port the app should listen on, like <code>1337</code></td>
</tr>
</tbody></table>

<p>You can set any of these values before running the script with the <code>export</code> command.  For example:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">export APP_ID=<span class="highlight">fooApp</span>
</li></ul></code></pre>
<p>It's worth reading through the contents of <code>index.js</code>, but in order to get a clearer picture of what's going on, you can also write your own shorter version of the example .  Open a new script in your editor:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">nano my_app.js
</li></ul></code></pre>
<p>And paste the following, changing the highlighted values where desired:</p>
<div class="code-label " title="~/parse-server-example/my_app.js">~/parse-server-example/my_app.js</div><pre class="code-pre "><code langs="">var express = require('express');
var ParseServer = require('parse-server').ParseServer;

// Configure the Parse API
var api = new ParseServer({
  databaseURI: '<span class="highlight">mongodb://localhost:27017/dev</span>',
  cloud: __dirname + '<span class="highlight">/cloud/main.js</span>',
  appId: '<span class="highlight">myOtherAppId</span>',
  masterKey: '<span class="highlight">myMasterKey</span>'
});

var app = express();

// Serve the Parse API on the <span class="highlight">/parse</span> URL prefix
app.use('<span class="highlight">/myparseapp</span>', api);

// Listen for connections on port 1337
var port = <span class="highlight">9999</span>;
app.listen(port, function() {
    console.log('parse-server-example running on port ' + port + '.');
});
</code></pre>
<p>Exit and save the file, then run it with Node.js:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">node my_app.js
</li></ul></code></pre><pre class="code-pre "><code langs=""><div class="secondary-code-label " title="Output">Output</div>parse-server-example running on port 9999.
</code></pre>
<p>Again, you can press <strong>Ctrl-C</strong> at any time to stop <code>my_app.js</code>.  As written above, the sample <code>my_app.js</code> will behave nearly identically to the provided <code>index.js</code>, except that it will listen on port 9999, with Parse Server mounted at <code>/myparseapp</code>, so that the endpoint URL looks like so:</p>

<p>http://<span class="highlight">your<em>server</em>IP</span>:9999/myparseapp</p>

<p>And it can be tested with <code>curl</code> like so:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">curl -H "X-Parse-Application-Id: <span class="highlight">myOtherAppId</span>" http://localhost:<span class="highlight">9999</span>/<span class="highlight">myparseapp</span>/classes/GameScore`
</li></ul></code></pre>
<h2 id="conclusion">Conclusion</h2>

<p>You should now know the basics of running a Node.js application like Parse Server in an Ubuntu environment.  Fully migrating an app from Parse is likely to be a more involved undertaking, requiring code changes and careful planning of infrastructure.  </p>

<p>For much greater detail on this process, see the second guide in this series, <a href="https://indiareads/community/tutorials/how-to-migrate-a-parse-app-to-parse-server-on-ubuntu-14-04">How To Migrate a Parse App to Parse Server on Ubuntu 14.04</a>.  You should also reference the official <a href="https://parse.com/docs/server/guide">Parse Server Guide</a>, particularly the section on <a href="https://parse.com/docs/server/guide#migrating">migrating an existing Parse app</a>.</p>

    