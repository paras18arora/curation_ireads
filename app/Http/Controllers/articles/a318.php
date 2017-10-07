<link rel='stylesheet' href='digital_ocean.css'><br> <br> 
      <h3 id="introduction">Introduction</h3>

<p>Parse is a Mobile Backend as a Service platform, owned by Facebook since 2013.  In January of 2016, Parse announced that its hosted services would shut down completely on January 28, 2017.</p>

<p>Fortunately, Parse has also released <a href="https://github.com/ParsePlatform/parse-server">an open source API server</a>, compatible with the hosted service's API, called <strong>Parse Server</strong>. Parse Server is under active development, and seems likely to attract a large developer community.  It can be be deployed to a range of environments running Node.js and MongoDB.</p>

<p>This guide focuses on migrating a pre-existing Parse application to a standalone instance of Parse Server running on Ubuntu 14.04.  It uses TLS/SSL encryption for all connections, using a certificate provided by Let's Encrypt, a new Certificate Authority which offers free certificates.  It includes a few details specific to IndiaReads and Ubuntu 14.04, but should be broadly applicable to systems running recent Debian-derived GNU/Linux distributions.</p>

<p><span class="warning"><strong>Warning:</strong> It is strongly recommended that this procedure first be tested with a development or test version of the app before attempting it with a user-facing production app.  It is also strongly recommended that you read this guide in conjunction with the <a href="https://parse.com/docs/server/guide#migrating">official migration documentation</a>.<br /></span></p>

<h2 id="prerequisites">Prerequisites</h2>

<p>This guide builds on <a href="https://indiareads/community/tutorials/how-to-run-parse-server-on-ubuntu-14-04">How To Run Parse Server on Ubuntu 14.04</a>.  It requires the following:</p>

<ul>
<li>An Ubuntu 14.04 server, configured with a non-root <code>sudo</code> user</li>
<li>Node.js 5.6.x</li>
<li>MongoDB 3.0.x</li>
<li>A domain name pointing at the server</li>
<li>A Parse App to be migrated</li>
</ul>

<p>The target server should have enough storage to handle all of your app's data.  Since Parse compresses data on their end, they officially recommend that you provision at least 10 times as much storage space as used by your hosted app.</p>

<h2 id="step-1-–-install-let-39-s-encrypt-and-retrieve-a-certificate">Step 1 – Install Let's Encrypt and Retrieve a Certificate</h2>

<p>Let's Encrypt is a new Certificate Authority that provides an easy way to obtain free TLS/SSL certificates.  Because a certificate is necessary to secure both the migration of data to MongoDB and your Parse Server API endpoint, we'll begin by retrieving one with the <code>letsencrypt</code> client.</p>

<h3 id="install-let-39-s-encrypt-and-dependencies">Install Let's Encrypt and Dependencies</h3>

<p>You must own or control the registered domain name that you wish to use the certificate with. If you do not already have a registered domain name, you may register one with one of the many domain name registrars out there (e.g. Namecheap, GoDaddy, etc.).</p>

<p>If you haven't already, be sure to create an <strong>A Record</strong> that points your domain to the public IP address of your server. This is required because of how Let's Encrypt validates that you own the domain it is issuing a certificate for. For example, if you want to obtain a certificate for <code>example.com</code>, that domain must resolve to your server for the validation process to work.</p>

<p>For more detail on this process, see <a href="https://indiareads/community/tutorials/how-to-set-up-a-host-name-with-digitalocean">How To Set Up a Host Name with IndiaReads</a> and <a href="https://indiareads/community/tutorials/how-to-point-to-digitalocean-nameservers-from-common-domain-registrars">How To Point to IndiaReads Nameservers from Common Domain Registrars</a>.</p>

<p>Begin by making sure that the <code>git</code> and <code>bc</code> packages are installed:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo apt-get -y install git bc
</li></ul></code></pre>
<p>Next, clone the <code>letsencrypt</code> repository from GitHub to <code>/opt/letsencrypt</code>.  The <code>/opt/</code> directory is a standard location for software that's not installed from the distribution's official package repositories:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo git clone https://github.com/letsencrypt/letsencrypt /opt/letsencrypt
</li></ul></code></pre>
<p>Change to the <code>letsencrypt</code> directory:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">cd /opt/letsencrypt
</li></ul></code></pre>
<h3 id="retrieve-initial-certificate">Retrieve Initial Certificate</h3>

<p>Run <code>letsencrypt</code> with the Standalone plugin:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">./letsencrypt-auto certonly --standalone
</li></ul></code></pre>
<p>You'll be prompted to answer several questions, including your email address, agreement to a Terms of Service, and the domain name(s) for the certificate.  Once finished, you'll receive notes much like the following:</p>
<pre class="code-pre "><code langs="">IMPORTANT NOTES:
 - Congratulations! Your certificate and chain have been saved at
   /etc/letsencrypt/live/<span class="highlight">your_domain_name</span>/fullchain.pem. Your cert will expire
   on <span class="highlight">2016-05-16</span>. To obtain a new version of the certificate in the
   future, simply run Let's Encrypt again.
 - If you like Let's Encrypt, please consider supporting our work by:

   Donating to ISRG / Let's Encrypt:   https://letsencrypt.org/donate
   Donating to EFF:                    https://eff.org/donate-le
</code></pre>
<p>Note the path and expiration date of your certificate, highlighted in the example output.  Your certificate files should now be available in <code>/etc/letsencrypt/<span class="highlight">your_domain_name</span>/</code>.</p>

<h3 id="set-up-let-39-s-encrypt-auto-renewal">Set Up Let's Encrypt Auto Renewal</h3>

<p><span class="warning"><strong>Warning:</strong> You can safely complete this guide without worrying about certificate renewal, but you <strong>will</strong> need to address it for any long-lived production environment.<br /></span></p>

<p>You may have noticed that your Let's Encrypt certificate is due to expire in 90 days.  This is a deliberate feature of the Let's Encrypt approach, intended to minimize the amount of time that a compromised certificate can exist in the wild if something goes wrong.</p>

<p>Let's Encrypt is still in beta.  Better auto-renewal features are planned, but in the meanwhile you will either have to repeat the certificate retrieval process by hand, or use a scheduled script to handle it for you.  The details of automating this process are covered in <a href="https://indiareads/community/tutorials/how-to-secure-nginx-with-let-s-encrypt-on-ubuntu-14-04">How To Secure Nginx with Let's Encrypt on Ubuntu 14.04</a>, particularly the section on <a href="https://indiareads/community/tutorials/how-to-secure-nginx-with-let-s-encrypt-on-ubuntu-14-04#step-4-%E2%80%94-set-up-auto-renewal">setting up auto renewal</a>.</p>

<h2 id="step-2-–-configure-mongodb-for-migration">Step 2 – Configure MongoDB for Migration</h2>

<p>Parse provides a migration tool for existing applications.  In order to make use of it, we need to open MongoDB to external connections and secure it with a copy of the TLS/SSL certificate from Let's Encrypt.  Start by combining <code>fullchain1.pem</code> and <code>privkey1.pem</code> into a new file in <code>/etc/ssl</code>:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo cat /etc/letsencrypt/archive/<span class="highlight">domain_name</span>/{fullchain1.pem,privkey1.pem} | sudo tee /etc/ssl/mongo.pem
</li></ul></code></pre>
<p><span class="note">You will have to repeat the above command after renewing your Let's Encrypt certificate.  If you configure auto-renewal of the Let's Encrypt certificate, remember to include this operation.<br /></span></p>

<p>Make sure <code>mongo.pem</code> is owned by the <strong>mongodb</strong> user, and readable only by its owner:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo chown mongodb:mongodb /etc/ssl/mongo.pem
</li><li class="line" prefix="$">sudo chmod 600 /etc/ssl/mongo.pem
</li></ul></code></pre>
<p>Now, open <code>/etc/mongod.conf</code> in <code>nano</code> (or your text editor of choice):</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo nano /etc/mongod.conf
</li></ul></code></pre>
<p>Here, we'll make several important changes.</p>

<p>First, look for the <code>bindIp</code> line in the <code>net:</code> section, and tell MongoDB to listen on all addresses by changing <code>127.0.0.1</code> to <code>0.0.0.0</code>.  Below this, add SSL configuration to the same section:</p>
<div class="code-label " title="/etc/mongod.conf">/etc/mongod.conf</div><pre class="code-pre "><code langs=""># network interfaces
net:
  port: 27017
  bindIp: <span class="highlight">0.0.0.0</span>
  <span class="highlight">ssl:</span>
    <span class="highlight">mode: requireSSL</span>
    <span class="highlight">PEMKeyFile: /etc/ssl/mongo.pem</span>
</code></pre>
<p>Next, under <code># security</code>, enable client authorization:</p>
<div class="code-label " title="/etc/mongod.conf">/etc/mongod.conf</div><pre class="code-pre "><code langs=""># security
security:
  authorization: enabled
</code></pre>
<p>Finally, the migration tool requires us to set the <code>failIndexKeyTooLong</code> parameter to <code>false</code>:</p>
<div class="code-label " title="/etc/mongod.conf">/etc/mongod.conf</div><pre class="code-pre "><code langs="">
setParameter:
  failIndexKeyTooLong: false
</code></pre>
<p><span class="note"><strong>Note:</strong> Whitespace is significant in MongoDB configuration files, which are based on <a href="http://yaml.org/">YAML</a>.  When copying configuration values, make sure that you preserve indentation.<br /></span></p>

<p>Exit and save the file.</p>

<p>Before restarting the <code>mongod</code> service, we need to add a user with the <code>admin</code> role.  Connect to the running MongoDB instance:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">mongo --port 27017
</li></ul></code></pre>
<p>Create an admin user and exit.  Be sure to replace <span class="highlight">sammy</span> with your desired username and <span class="highlight">password</span> with a strong password.</p>
<pre class="code-pre "><code langs="">use admin
db.createUser({
  user: "<span class="highlight">sammy</span>",
  pwd: "<span class="highlight">password</span>",
  roles: [ { role: "userAdminAnyDatabase", db: "admin" } ]
})
exit
</code></pre>
<p>Restart the <code>mongod</code> service:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo service mongod restart
</li></ul></code></pre>
<h2 id="step-2-–-migrate-application-data-from-parse">Step 2 – Migrate Application Data from Parse</h2>

<p>Now that you have a remotely-accessible MongoDB instance, you can use the Parse migration tool to transfer your app's data to your server.</p>

<h3 id="configure-mongodb-credentials-for-migration-tool">Configure MongoDB Credentials for Migration Tool</h3>

<p>We'll begin by connecting locally with our new admin user:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">mongo --port 27017 --ssl --sslAllowInvalidCertificates --authenticationDatabase admin --username <span class="highlight">sammy</span> --password
</li></ul></code></pre>
<p>You should be prompted to enter the password you set earlier.</p>

<p>Once connected, choose a name for the database to store your app's data.  For example, if you're migrating an app called Todo, you might use <code>todo</code>.  You'll also need to pick another strong password for a user called <strong>parse</strong>.</p>

<p>From the <code>mongo</code> shell, give this user access to <code><span class="highlight">database_name</span></code>:</p>
<pre class="code-pre custom_prefix"><code langs=""><ul class="prefixed"><li class="line" prefix=">">use <span class="highlight">database_name</span>
</li><li class="line" prefix=">">db.createUser({ user: "parse", pwd: "<span class="highlight">password</span>", roles: [ "readWrite", "dbAdmin" ] })
</li></ul></code></pre>
<h3 id="initiate-data-migration-process">Initiate Data Migration Process</h3>

<p>In a browser window, log in to Parse, and open the settings for your app.  Under <strong>General</strong>, locate the <strong>Migrate</strong> button and click it:</p>

<p><img src="https://assets.digitalocean.com/articles/parse_migration/small-000.png" alt="Parse App Settings: General: Migrate" /></p>

<p>You will be prompted for a MongoDB connection string.  Use the following format:</p>
<pre class="code-pre "><code langs="">mongodb://parse:<span class="highlight">password</span>@<span class="highlight">your_domain_name</span>:27017/<span class="highlight">database_name</span>?ssl=true
</code></pre>
<p>For example, if you are using the domain <code>example.com</code>, with the user <code>parse</code>, the password <code>foo</code>, and a database called <code>todo</code>, your connection string would look like this:</p>
<pre class="code-pre "><code langs="">mongodb://parse:foo@example.com:27017/todo?ssl=true
</code></pre>
<p>Don't forget <code>?ssl=true</code> at the end, or the connection will fail.  Enter the connection string into the dialog like so:</p>

<p><img src="https://assets.digitalocean.com/articles/parse_migration/small-001.png" alt="Parse App: Migration Dialog" /></p>

<p>Click <strong>Begin the migration</strong>.  You should see progress dialogs for copying a snapshot of your Parse hosted database to your server, and then for syncing new data since the snapshot was taken.  The duration of this process will depend on the amount of data to be transferred, and may be substantial.</p>

<p><img src="https://assets.digitalocean.com/articles/parse_migration/small-002.png" alt="Parse App: Migration Progress" /></p>

<p><img src="https://assets.digitalocean.com/articles/parse_migration/small-003.png" alt="Parse App: Migration Process" /></p>

<h3 id="verify-data-migration">Verify Data Migration</h3>

<p>Once finished, the migration process will enter a verification step.  Don't finalize the migration yet.  You'll first want to make sure the data has actually transferred, and test a local instance of Parse Server.</p>

<p><img src="https://assets.digitalocean.com/articles/parse_migration/small-004.png" alt="Parse App: Finished Migration, Waiting for Finalization" /></p>

<p>Return to your <code>mongo</code> shell, and examine your local database.  Begin by accessing <span class="highlight">database_name</span> and examining the collections it contains:</p>
<pre class="code-pre custom_prefix"><code langs=""><ul class="prefixed"><li class="line" prefix=">">use <span class="highlight">database_name</span>
</li></ul></code></pre><pre class="code-pre custom_prefix"><code langs=""><ul class="prefixed"><li class="line" prefix=">">show collections
</li></ul></code></pre><pre class="code-pre "><code langs=""><div class="secondary-code-label " title="Sample Output for Todo App">Sample Output for Todo App</div>Todo
_Index
_SCHEMA
_Session
_User
_dummy
system.indexes
</code></pre>
<p>You can examine the contents of a specific collection with the <code>.find()</code> method:</p>
<pre class="code-pre custom_prefix"><code langs=""><ul class="prefixed"><li class="line" prefix=">">db.<span class="highlight">ApplicationName</span>.find()
</li></ul></code></pre><pre class="code-pre "><code langs=""><div class="secondary-code-label " title="Sample Output for Todo App">Sample Output for Todo App</div>> <span class="highlight">db.Todo.find()</span>
{ "_id" : "hhbrhmBrs0", "order" : NumberLong(1), "_p_user" : "_User$dceklyR50A", "done" : false, "_acl" : { "dceklyR50A" : { "r" : true, "w" : true } }, "_rperm" : [ "dceklyR50A" ], "content" : "Migrate this app to my own server.", "_updated_at" : ISODate("2016-02-08T20:44:26.157Z"), "_wperm" : [ "dceklyR50A" ], "_created_at" : ISODate("2016-02-08T20:44:26.157Z") }
</code></pre>
<p>Your specific output will be different, but you should see data for your app.  Once satisfied, exit <code>mongo</code> and return to the shell:</p>
<pre class="code-pre custom_prefix"><code langs=""><ul class="prefixed"><li class="line" prefix=">">exit
</li></ul></code></pre>
<h2 id="step-3-–-install-and-configure-parse-server-and-pm2">Step 3 – Install and Configure Parse Server and PM2</h2>

<p>With your app data in MongoDB, we can move on to installing Parse Server itself, and integrating with the rest of the system.  We'll give Parse Server a dedicated user, and use a utility called <strong>PM2</strong> to configure it and ensure that it's always running.</p>

<h3 id="install-parse-server-and-pm2-globally">Install Parse Server and PM2 Globally</h3>

<p>Use <code>npm</code> to install the <code>parse-server</code> utility, the <code>pm2</code> process manager, and their dependencies, globally:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo npm install -g parse-server pm2
</li></ul></code></pre>
<h3 id="create-a-dedicated-parse-user-and-home-directory">Create a Dedicated Parse User and Home Directory</h3>

<p>Instead of running <code>parse-server</code> as <strong>root</strong> or your <code>sudo</code> user, we'll create a system user called <strong>parse</strong>:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo useradd --create-home --system parse
</li></ul></code></pre>
<p>Now set a password for <strong>parse</strong>:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo passwd parse
</li></ul></code></pre>
<p>You'll be prompted to enter a password twice.</p>

<p>Now, use the <code>su</code> command to become the <strong>parse</strong> user:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo su parse
</li></ul></code></pre>
<p>Change to <strong>parse</strong>'s home directory:</p>
<pre class="code-pre custom_prefix"><code langs=""><ul class="prefixed"><li class="line" prefix="parse $">cd ~
</li></ul></code></pre>
<h3 id="write-or-migrate-a-cloud-code-file">Write or Migrate a Cloud Code File</h3>

<p>Create a cloud code directory:</p>
<pre class="code-pre custom_prefix"><code langs=""><ul class="prefixed"><li class="line" prefix="parse $">mkdir -p ~/cloud
</li></ul></code></pre>
<p>Edit <code>/home/parse/cloud/main.js</code>:</p>
<pre class="code-pre custom_prefix"><code langs=""><ul class="prefixed"><li class="line" prefix="parse $">nano ~/cloud/main.js
</li></ul></code></pre>
<p>For testing purposes, you can paste the following:</p>
<div class="code-label " title="/home/parse/cloud/main.js">/home/parse/cloud/main.js</div><pre class="code-pre "><code langs="">Parse.Cloud.define('hello', function(req, res) {
  res.success('Hi');
});
</code></pre>
<p>Alternatively, you can migrate any cloud code defined for your application by copying it from the <strong>Cloud Code</strong> section of your app's settings on the Parse Dashboard.</p>

<p>Exit and save.</p>

<h3 id="retrieve-keys-and-write-home-parse-ecosystem-json">Retrieve Keys and Write /home/parse/ecosystem.json</h3>

<p><a href="http://pm2.keymetrics.io/">PM2</a> is a feature-rich process manager, popular with Node.js developers.  We'll use the <code>pm2</code> utility to configure our <code>parse-server</code> instance and keep it running over the long term.</p>

<p>You'll need to retrieve some of the keys for your app.  In the Parse dashboard, click on <strong>App Settings</strong> followed by <strong>Security & Keys</strong>:</p>

<p><img src="https://assets.digitalocean.com/articles/parse_migration/small-007.png" alt="Parse Dashboard: App Settings: Security & Keys" /></p>

<p>Of these, only the <strong>Application ID</strong> and <strong>Master Key</strong> are required.  Others (client, JavaScript, .NET, and REST API keys) <em>may</em> be necessary to support older client builds, but, if set, will be required in all requests.  Unless you have reason to believe otherwise, you should begin by using just the Application ID and Master Key.</p>

<p>With these keys ready to hand, edit a new file called <code>/home/parse/ecosystem.json</code>:</p>
<pre class="code-pre custom_prefix"><code langs=""><ul class="prefixed"><li class="line" prefix="parse $">nano ecosystem.json
</li></ul></code></pre>
<p>Paste the following, changing configuration values to reflect your MongoDB connection string, Application ID, and Master Key:</p>
<pre class="code-pre "><code langs="">{
  "apps" : [{
    "name"        : "parse-wrapper",
    "script"      : "/usr/bin/parse-server",
    "watch"       : true,
    "merge_logs"  : true,
    "cwd"         : "/home/parse",
    "env": {
      "PARSE_SERVER_CLOUD_CODE_MAIN": "/home/parse/cloud/main.js",
      "PARSE_SERVER_DATABASE_URI": "mongodb://<span class="highlight">parse</span>:<span class="highlight">password</span>@<span class="highlight">your_domain_name</span>:27017/<span class="highlight">database_name</span>?ssl=true",
      "PARSE_SERVER_APPLICATION_ID": "<span class="highlight">your_application_id</span>",
      "PARSE_SERVER_MASTER_KEY": "<span class="highlight">your_master_key</span>",
    }
  }]
}
</code></pre>
<p>The <code>env</code> object is used to set environment variables.  If you need to configure additional keys, <code>parse-server</code> also recognizes the following variables:</p>

<ul>
<li><code>PARSE_SERVER_COLLECTION_PREFIX</code></li>
<li><code>PARSE_SERVER_CLIENT_KEY</code></li>
<li><code>PARSE_SERVER_REST_API_KEY</code></li>
<li><code>PARSE_SERVER_DOTNET_KEY</code></li>
<li><code>PARSE_SERVER_JAVASCRIPT_KEY</code></li>
<li><code>PARSE_SERVER_DOTNET_KEY</code></li>
<li><code>PARSE_SERVER_FILE_KEY</code></li>
<li><code>PARSE_SERVER_FACEBOOK_APP_IDS</code></li>
</ul>

<p>Exit and save <code>ecosystem.json</code>.</p>

<p>Now, run the script with <code>pm2</code>:</p>
<pre class="code-pre custom_prefix"><code langs=""><ul class="prefixed"><li class="line" prefix="parse $">pm2 start ecosystem.json
</li></ul></code></pre><pre class="code-pre "><code langs=""><div class="secondary-code-label " title="Sample Output">Sample Output</div>...
[PM2] Spawning PM2 daemon
[PM2] PM2 Successfully daemonized
[PM2] Process launched
┌───────────────┬────┬──────┬──────┬────────┬─────────┬────────┬─────────────┬──────────┐
│ App name      │ id │ mode │ pid  │ status │ restart │ uptime │ memory      │ watching │
├───────────────┼────┼──────┼──────┼────────┼─────────┼────────┼─────────────┼──────────┤
│ parse-wrapper │ 0  │ fork │ 3499 │ online │ 0       │ 0s     │ 13.680 MB   │  enabled │
└───────────────┴────┴──────┴──────┴────────┴─────────┴────────┴─────────────┴──────────┘
 Use `pm2 show <id|name>` to get more details about an app
</code></pre>
<p>Now tell <code>pm2</code> to save this process list:</p>
<pre class="code-pre custom_prefix"><code langs=""><ul class="prefixed"><li class="line" prefix="parse $">pm2 save
</li></ul></code></pre><pre class="code-pre "><code langs=""><div class="secondary-code-label " title="Sample Output">Sample Output</div>[PM2] Dumping processes
</code></pre>
<p>The list of processes <code>pm2</code> is running for the <strong>parse</strong> user should now be stored in <code>/home/parse/.pm2</code>.</p>

<p>Now we need to make sure the <code>parse-wrapper</code> process we defined earlier in <code>ecosystem.json</code> is restored each time the server is restarted.  Fortunately, <code>pm2</code> can generate and install a script on its own.</p>

<p>Exit to your regular <code>sudo</code> user:</p>
<pre class="code-pre custom_prefix"><code langs=""><ul class="prefixed"><li class="line" prefix="parse $">exit
</li></ul></code></pre>
<p>Tell <code>pm2</code> to install initialization scripts for Ubuntu, to be run as the <strong>parse</strong> user, using <code>/home/parse</code> as its home directory:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo pm2 startup ubuntu -u parse --hp /home/parse/
</li></ul></code></pre><div class="code-label " title="Output">Output</div><pre class="code-pre "><code langs="">[PM2] Spawning PM2 daemon
[PM2] PM2 Successfully daemonized
[PM2] Generating system init script in /etc/init.d/pm2-init.sh
[PM2] Making script booting at startup...
[PM2] -ubuntu- Using the command:
      su -c "chmod +x /etc/init.d/pm2-init.sh && update-rc.d pm2-init.sh defaults"
 System start/stop links for /etc/init.d/pm2-init.sh already exist.
[PM2] Done.
</code></pre>
<h2 id="step-4-–-install-and-configure-nginx">Step 4 – Install and Configure Nginx</h2>

<p>We'll use the Nginx web server to provide a <strong>reverse proxy</strong> to <code>parse-server</code>, so that we can serve the Parse API securely over TLS/SSL.</p>

<p>Install the <code>nginx</code> package:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo apt-get install -y nginx
</li></ul></code></pre>
<p>Open <code>/etc/nginx/sites-enabled/default</code> in <code>nano</code> (or your editor of choice):</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo nano /etc/nginx/sites-enabled/default
</li></ul></code></pre>
<p>Replace its contents with the following:</p>
<div class="code-label " title="/etc/nginx/sites-enabled/default">/etc/nginx/sites-enabled/default</div><pre class="code-pre "><code langs=""># HTTP - redirect all requests to HTTPS
server {
    listen 80;
    listen [::]:80 default_server ipv6only=on;
    return 301 https://$host$request_uri;
}

# HTTPS - serve HTML from /usr/share/nginx/html, proxy requests to /parse/
# through to Parse Server
server {
        listen 443;
        server_name <span class="highlight">your_domain_name</span>;

        root /usr/share/nginx/html;
        index index.html index.htm;

        ssl on;
        # Use certificate and key provided by Let's Encrypt:
        ssl_certificate /etc/letsencrypt/live/<span class="highlight">your_domain_name</span>/fullchain.pem;
        ssl_certificate_key /etc/letsencrypt/live/<span class="highlight">your_domain_name</span>/privkey.pem;
        ssl_session_timeout 5m;
        ssl_protocols TLSv1 TLSv1.1 TLSv1.2;
        ssl_prefer_server_ciphers on;
        ssl_ciphers 'EECDH+AESGCM:EDH+AESGCM:AES256+EECDH:AES256+EDH';

        # Pass requests for /parse/ to Parse Server instance at localhost:1337
        location /parse/ {
                proxy_set_header X-Real-IP $remote_addr;
                proxy_set_header X-Forwarded-For $proxy_add_x_forwarded_for;
                proxy_set_header X-NginX-Proxy true;
                proxy_pass http://localhost:1337/;
                proxy_ssl_session_reuse off;
                proxy_set_header Host $http_host;
                proxy_redirect off;
        }

        location / {
                try_files $uri $uri/ =404;
        }
}
</code></pre>
<p>Exit the editor and save the file.  Restart Nginx so that changes take effect:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo service nginx restart
</li></ul></code></pre><pre class="code-pre "><code langs=""><div class="secondary-code-label " title="Output">Output</div> * Restarting nginx nginx
   ...done.
</code></pre>
<h2 id="step-5-–-test-parse-server">Step 5 – Test Parse Server</h2>

<p>At this stage, you should have the following:</p>

<ul>
<li>A TLS/SSL certificate, provided by Let's Encrypt</li>
<li>MongoDB, secured with the Let's Encrypt certificate</li>
<li><code>parse-server</code> running under the <strong>parse</strong> user on port 1337, configured with the keys expected by your app</li>
<li><code>pm2</code> managing the <code>parse-server</code> process under the <strong>parse</strong> user, and a startup script to restart <code>pm2</code> on boot</li>
<li><code>nginx</code>, secured with the Let's Encrypt certificate, and configured to proxy connections to <code>https://<span class="highlight">your_domain_name</span>/parse</code> to the <code>parse-server</code> instance</li>
</ul>

<p>It should now be possible to test reads, writes, and cloud code execution using <code>curl</code>.</p>

<p><span class="note"><strong>Note:</strong> The <code>curl</code> commands in this section should be harmless when used with a test or development app.  Be cautious when writing data to a production app.<br /></span></p>

<h3 id="writing-data-with-a-post">Writing Data with a POST</h3>

<p>You'll need to give <code>curl</code> several important options:</p>

<table class="pure-table"><thead>
<tr>
<th>Option</th>
<th>Description</th>
</tr>
</thead><tbody>
<tr>
<td><code>-X POST</code></td>
<td>Sets the request type, which would otherwise default to <code>GET</code></td>
</tr>
<tr>
<td><code>-H "X-Parse-Application-Id: <span class="highlight">your_application_id</span>"</code></td>
<td>Sends a header which identifies your application to <code>parse-server</code></td>
</tr>
<tr>
<td><code>-H "Content-Type: application/json"</code></td>
<td>Sends a header which lets <code>parse-server</code> know to expect JSON-formatted data</td>
</tr>
<tr>
<td><code>-d '{<span class="highlight">json_data</span>}</code></td>
<td>Sends the data itself</td>
</tr>
</tbody></table>

<p>Putting these all together, we get:</p>
<pre class="code-pre "><code langs="">curl -X POST \
  -H "X-Parse-Application-Id: <span class="highlight">your_application_id</span>" \
  -H "Content-Type: application/json" \
  -d '{"score":1337,"playerName":"Sammy","cheatMode":false}' \
  https://<span class="highlight">your_domain_name</span>/parse/classes/GameScore
</code></pre><div class="code-label " title="Sample Output">Sample Output</div><pre class="code-pre "><code langs="">{"objectId":"YpxFdzox3u","createdAt":"2016-02-18T18:03:43.188Z"}
</code></pre>
<h3 id="reading-data-with-a-get">Reading Data with a GET</h3>

<p>Since <code>curl</code> sends GET requests by default, and we're not supplying any data, you should only need to send the Application ID in order to read some sample data back:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">curl -H "X-Parse-Application-Id: <span class="highlight">your_application_id</span>" https://<span class="highlight">your_domain_name</span>/parse/classes/GameScore
</li></ul></code></pre><div class="code-label " title="Sample Output">Sample Output</div><pre class="code-pre "><code langs="">{"results":[{"objectId":"BNGLzgF6KB","score":1337,"playerName":"Sammy","cheatMode":false,"updatedAt":"2016-02-17T20:53:59.947Z","createdAt":"2016-02-17T20:53:59.947Z"},{"objectId":"0l1yE3ivB6","score":1337,"playerName":"Sean Plott","cheatMode":false,"updatedAt":"2016-02-18T03:57:00.932Z","createdAt":"2016-02-18T03:57:00.932Z"},{"objectId":"aKgvFqDkXh","score":1337,"playerName":"Sean Plott","cheatMode":false,"updatedAt":"2016-02-18T04:44:01.275Z","createdAt":"2016-02-18T04:44:01.275Z"},{"objectId":"zCKTgKzCRH","score":1337,"playerName":"Sean Plott","cheatMode":false,"updatedAt":"2016-02-18T16:56:51.245Z","createdAt":"2016-02-18T16:56:51.245Z"},{"objectId":"YpxFdzox3u","score":1337,"playerName":"Sean Plott","cheatMode":false,"updatedAt":"2016-02-18T18:03:43.188Z","createdAt":"2016-02-18T18:03:43.188Z"}]}
</code></pre>
<h3 id="executing-example-cloud-code">Executing Example Cloud Code</h3>

<p>A simple POST with no real data to <code>https://<span class="highlight">your_domain_name</span>/parse/functions/hello</code> will run the <code>hello()</code> function defined in <code>/home/parse/cloud/main.js</code>:</p>
<pre class="code-pre "><code langs="">curl -X POST \
  -H "X-Parse-Application-Id: <span class="highlight">your_application_id</span>" \
  -H "Content-Type: application/json" \
  -d '{}' \
  https://<span class="highlight">your_domain_name</span>/parse/functions/hello
</code></pre><div class="code-label " title="Sample Output">Sample Output</div><pre class="code-pre "><code langs="">{"result":"Hi"}
</code></pre>
<p>If you have instead migrated your own custom cloud code, you can test with a known function from <code>main.js</code>.</p>

<h2 id="step-6-–-configure-your-app-for-parse-server-and-finalize-migration">Step 6 – Configure Your App for Parse Server and Finalize Migration</h2>

<p>Your next step will be to change your client application itself to use the Parse Server API endpoint.  Consult the <a href="https://github.com/ParsePlatform/parse-server/wiki/Parse-Server-Guide#using-parse-sdks-with-parse-server">official documentation</a> on using Parse SDKs with Parse Server.  You will need the latest version of the SDK for your platform.  As with the <code>curl</code>-based tests above, use this string for the server URL:</p>
<pre class="code-pre "><code langs="">https://<span class="highlight">your_domain_name</span>/parse
</code></pre>
<p>Return to the Parse dashboard in your browser and the <strong>Migration</strong> tab:</p>

<p><img src="https://assets.digitalocean.com/articles/parse_migration/small-004.png" alt="Parse App: Migration Process" /></p>

<p>Click the <strong>Finalize</strong> button:</p>

<p><img src="https://assets.digitalocean.com/articles/parse_migration/small-005.png" alt="Parse Migration Finalization Dialog" /></p>

<p>Your app should now be migrated.</p>

<h2 id="conclusion-and-next-steps">Conclusion and Next Steps</h2>

<p>This guide offers a functional starting point for migrating a Parse-hosted app to a Parse Server install on a single Ubuntu system, such as a IndiaReads droplet.  The configuration we've described should be adequate for a low-traffic app with a modest userbase.  Hosting for a larger app may require multiple systems to provide redundant data storage and load balancing between API endpoints.  Even small projects are likely to involve infrastructure considerations that we haven't directly addressed.</p>

<p>In addition to reading the official Parse Server documentation and tracking the <a href="https://github.com/ParsePlatform/parse-server/issues">GitHub issues for the project</a> when troubleshooting, you may wish to explore the following topics:</p>

<ul>
<li><a href="https://indiareads/community/tutorial_series/new-ubuntu-14-04-server-checklist">The IndiaReads New Ubuntu 14.04 Server Checklist</a></li>
<li><a href="https://indiareads/community/tutorials/how-to-secure-nginx-with-let-s-encrypt-on-ubuntu-14-04">Securing Nginx with Let's Encrypt on Ubuntu 14.04</a></li>
<li><a href="https://indiareads/community/tutorials/how-to-install-node-js-on-an-ubuntu-14-04-server">Installing Node.js on Ubuntu 14.04</a></li>
<li><a href="https://indiareads/community/tutorials/understanding-digitalocean-droplet-backups">IndiaReads Droplet Backups</a></li>
</ul>

    