<link rel='stylesheet' href='digital_ocean.css'><br> <br> 
      <h3 id="introduction">Introduction</h3>

<p>IndiaReads's Horizon One-Click Application provides a convenient way to get started with Horizon and get your RethinkDB-powered projects up and running. With <a href="https://horizon.io/">Horizon</a>, <a href="https://rethinkdb.com/">RethinkDB</a>, and Node.js all pre-installed, the One-Click is a great base for building your application. This tutorial will give you all the details you need to get your project off the ground.</p>

<p>Horizon is an open-source developer platform for building realtime applications. It includes a middleware server that connects to RethinkDB and exposes an API as well as a JavaScript client library which allows front-end developers to store documents and subscribe to streaming data. Employing RethinkDB and websockets, Horizon seeks to make it simple for developers to stand up modern, realtime applications without the need for writing backend code. For a full rundown, see their <a href="https://horizon.io/docs">documentation at horizon.io</a>.</p>

<p>In addition to what comes on a standard Ubuntu 14.04 Droplet, the Horizon One-Click Application includes the following components:</p>

<ul>
<li><strong>Horizon</strong></li>
<li><strong>RethinkDB</strong></li>
<li><strong>Node.js</strong></li>
<li><strong>Nginx</strong></li>
</ul>

<p>For convenience, the <code>git</code> package is installed on the image.</p>

<h2 id="creating-your-horizon-droplet">Creating Your Horizon Droplet</h2>

<p>You can launch a new Horizon instance by selecting <strong>Horizon with RethinkDB on 14.04</strong> from the Applications menu during Droplet creation:</p>

<p><img src="https://assets.digitalocean.com/articles/horizon-one-click/horizon_select_cp.png" alt="Control panel" /></p>

<p>Once you have created the Droplet, connect to it via the web-based console in the IndiaReads control panel or SSH:</p>
<pre class="code-pre "><code langs="">ssh root@<span class="highlight">your.ip.address</span>
</code></pre>
<h2 id="accessing-rethinkdb">Accessing RethinkDB</h2>

<p>The Horizon One-Click Application comes with RethinkDB preconfigured. It's web-based admin panel sits behind an Nginx reverse proxy. This allows it to be password protected. The password is randomly-generated on first boot, and can be found in the message of the day (MOTD) that will appear whenever you log into the server via SSH. The MOTD should look something like this:</p>
<div class="code-label " title="MOTD">MOTD</div><pre class="code-pre "><code langs="">----------------------------------------------------------------------
Thank you for using IndiaReads's Horizon/RethinkDB Application.

Your RethinkDB dashboard can be accessed at http://111.111.11.111:8080/

Your RethinkDB login credentials are:
Username: admin
Password: <span class="highlight">51PwBG3saQ</span>

----------------------------------------------------------------------
</code></pre>
<p>To remove this information from the MOTD, run:</p>
<pre class="code-pre [commands]"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo rm -f /etc/motd.tail
</li></ul></code></pre>
<p>You can change the password for the <code>admin</code> user by running:</p>
<pre class="code-pre [commands]"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo htpasswd -cb /etc/rethinkdb/.htpasswd admin <span class="highlight">my-new-password</span>
</li></ul></code></pre>
<h2 id="getting-started-with-horizon">Getting Started with Horizon</h2>

<p>Horizon currently includes three main components:</p>

<ul>
<li>A middleware server that connects to RethinkDB and serves the client API and WebSocket endpoint</li>
<li>A client library for use in your applications</li>
<li>A command line tool, <code>hz</code></li>
</ul>

<h3 id="creating-your-project">Creating Your Project</h3>

<p>The <code>hz</code> can be used to generate a project template and serve your Horizon app. To produce the scaffolding for your first app, run:</p>
<pre class="code-pre [commands]"><code langs=""><ul class="prefixed"><li class="line" prefix="$">hz init <span class="highlight">myapp</span>
</li></ul></code></pre>
<p>This produces a sample configuration file and project structure in the <code><span class="highlight">myapp</span></code> directory. Taking a closer look, you'll find:</p>
<pre class="code-pre "><code langs="">cd <span class="highlight">myapp</span>
tree -aF
.
├── dist/
│   └── index.html
├── .hz/
│   └── config.toml
└── src/

3 directories, 2 files
</code></pre>
<p>The <code>.hz/config.toml</code> is your project's configuration file. For more details on it's content, see <a href="https://horizon.io/docs/configuration">Horizon's documentation</a>.</p>

<p><code>dist/index.html</code> contains a sample app that simply connects to the Horizon server to verify that it is working.</p>
<pre class="code-pre "><code class="code-highlight language-html"><!doctype html>
<html>
  <head>
    <meta charset="UTF-8">
    <script src="/horizon/horizon.js"></script>
    <script>
      var horizon = Horizon();
      horizon.onReady(function() {
        document.querySelector('h1').innerHTML = 'myapp works!'
      });
      horizon.connect();
    </script>
  </head>
  <body>
   <marquee><h1></h1></marquee>
  </body>
</html>
</code></pre>
<h3 id="serving-your-project">Serving Your Project</h3>

<p>The quickest way to serve your Horizon project is by using the command <code>hz serve --dev --bind all</code>. Now, when you browse to <code>http://<span class="highlight">droplet.ip.add</span>:8181</code> you should see a message indicating that the app successfully connected to the Horizon server:</p>

<p><img src="https://assets.digitalocean.com/articles/horizon-one-click/it-works.png" alt="It works!" /></p>

<p>To better understand what this command does, let's break it down. By default, <code>hz serve</code> will only serve the application on localhost. In order to make it publicly available, we use the <code>--bind all</code> flag. The <code>--dev</code> flag combines a number of separate settings into one flag in order to provide a convenient way to serve your application while developing. It is the equivalent to using the following flags:</p>

<table class="pure-table"><thead>
<tr>
<th>Flag</th>
<th>Purpose</th>
</tr>
</thead><tbody>
<tr>
<td>--secure no</td>
<td>Not served with encryption, no need to provide SSL cert</td>
</tr>
<tr>
<td>--start-rethinkdb yes</td>
<td>Starts a separate RethinkDB instance with a datastore in <code>rethinkdb_data/</code></td>
</tr>
<tr>
<td>--auto-create-tables yes</td>
<td>Creates tables in RethinkDB automatically</td>
</tr>
<tr>
<td>--auto-create-indexes yes</td>
<td>Creates indexes in RethinkDB automatically</td>
</tr>
<tr>
<td>--serve-static ./dist</td>
<td>Serves static content from <code>dist/</code></td>
</tr>
</tbody></table>

<p>These defaults get you up and running quickly, but they should not be used in a production environment.</p>

<p>To connect to the already running RethinkDB instance rather than the development instance, you can use:</p>
<pre class="code-pre [commands]"><code langs=""><ul class="prefixed"><li class="line" prefix="$">hz serve --dev --bind all --start-rethinkdb no --connect localhost
</li></ul></code></pre>
<h2 id="keeping-up-to-date">Keeping Up-To-Date</h2>

<p>Horizon is installed system-wide via Node Package Manager, <code>npm</code>. In order to update it to the most recent version, just run:</p>
<pre class="code-pre [commands]"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo npm update -g horizon
</li></ul></code></pre>
<p>Node.js is installed using the NodeSource Apt repository for the 6.x "Current" series, while RethinkDB is installed from the project's own Apt repository. So both components will receive updates as you update the rest of your system as you normally would:</p>
<pre class="code-pre [commands]"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo apt-get update
</li><li class="line" prefix="$">sudo apt-get upgrade
</li></ul></code></pre>
<h2 id="next-steps">Next Steps</h2>

<ul>
<li><p>Learn more about Horizon by following their <a href="https://horizon.io/docs/getting-started">getting started guide</a> on GitHub, join the community in the <a href="http://slack.rethinkdb.com/">#horizon channel on RethinkDB's Slack</a>, or take part of the discussion on <a href="https://discuss.horizon.io/">the Horizon Discuss forum</a>.</p></li>
<li><p>Follow our <a href="https://indiareads/community/articles/initial-server-setup-with-ubuntu-14-04">Initial Server Setup guide</a> to give <code>sudo</code> privileges to your user, lock down root login, and take other steps to make your server ready for production.</p></li>
<li><p>Secure your Nginx reverse-proxy with SSL using <a href="https://indiareads/community/tutorials/how-to-secure-nginx-with-let-s-encrypt-on-ubuntu-14-04">Let's Encrypt</a>.</p></li>
<li><p>When you're ready to scale up, check out our tutorial on <a href="https://indiareads/community/tutorials/how-to-create-a-sharded-rethinkdb-cluster-on-ubuntu-14-04">creating a sharded RethinkDB cluster on Ubuntu 14.04</a>.</p></li>
</ul>

    