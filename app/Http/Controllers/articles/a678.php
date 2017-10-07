<link rel='stylesheet' href='digital_ocean.css'><br> <br> 
      <p><strong><span class="highlight">Note: The Dokku project has changed significantly since this guide was written.  The instructions below may not reflect the current state of the Dokku project.</span></strong></p>

<h3 id="introduction">Introduction</h3>

<hr />

<p>A significant hurdle in developing an application is providing a sane and easy way to deploy your finished product.  <strong>Dokku</strong> is a Platform as a Service solution that enables you to quickly deploy and configure an application to a production environment on a separate server. </p>

<p>Dokku is similar to Heroku in that you can deploy to a remote server.  The difference is that Dokku is built to deploy to a single, personal server and is extremely lightweight.  Dokku uses Docker, a Linux container system, to easily manage its deployments.</p>

<p>In this guide, we will cover how to deploy a Node.js app with Dokku using the IndiaReads Dokku one-click installation image.</p>

<h2 id="step-one-––-create-the-dokku-droplet">Step One –– Create the Dokku Droplet</h2>

<hr />

<p>The first thing we need to do is create the VPS instance that contains our Dokku installation.  This is simple to set up using the IndiaReads Dokku application.</p>

<p>Click on the "Create" button to create a new droplet:</p>

<p><img src="https://assets.digitalocean.com/articles/dokku_nodejs/create_drop.png" alt="IndiaReads create droplet" /></p>

<p>Name your droplet, and select the size and region that you would like to use:</p>

<p><img src="https://assets.digitalocean.com/articles/dokku_nodejs/config1.png" alt="IndiaReads configure droplet 1" /><br />
Scroll down and click on the "Applications" tab.  Select the Dokku application image:</p>

<p><img src="https://assets.digitalocean.com/articles/dokku/dokku_image.png" alt="IndiaReads Dokku image" /></p>

<p>Select your SSH keys if you have them available.  If you do not already have them configured, now is a great time to <a href="https://indiareads/community/articles/how-to-use-ssh-keys-with-digitalocean-droplets">create SSH keys to use with your IndiaReads droplets</a>.  This step will help you later.</p>

<p>Click "Create Droplet".  Your Dokku VPS instance will be created.</p>

<p><img src="https://assets.digitalocean.com/articles/dokku_nodejs/final_create.png" alt="IndiaReads final create" /></p>

<p>Once your droplet is created, you should set up your domain name to point to your new Dokku droplet.  You can learn how to <a href="https://indiareads/community/articles/how-to-set-up-a-host-name-with-digitalocean">configure domain names with IndiaReads</a> here.</p>

<h2 id="step-two-––-access-the-droplet-to-complete-configuration">Step Two –– Access the Droplet To Complete Configuration</h2>

<hr />

<p>You can complete your Dokku configuration by accessing your VPS from a web browser.</p>

<p>If you configured a domain name to point to your Dokku installation, you should visit your domain name with your favorite web browser.  If you do not have a domain name configured, you can use your droplet's IP address.</p>

<p>You will be given a simple configuration page.  There are a few parts that you need to configure here.</p>

<p><img src="https://assets.digitalocean.com/articles/dokku_nodejs/ssh_keys.png" alt="IndiaReads Dokku ssh keys" /></p>

<p>First, check that the Public Key matches the computer that you will be deploying <em>from</em>.  This means that if your project is on your home computer, you should use the public key that corresponds to that set up.</p>

<p>If you selected multiple SSH keys to embed during droplet creation, only the first will be available here.  Modify it as necessary.</p>

<p><img src="https://assets.digitalocean.com/articles/dokku_nodejs/hostname_config.png" alt="IndiaReads Dokku hostname configuration" /></p>

<p>Next, modify the <strong>Hostname</strong> field to match your domain name.  Leave this as your IP address if you do not have a domain name configured.</p>

<p>Choose the way that you want your applications to be referenced.  By default, the application will be served like this:</p>

<pre>
http://<span class="highlight">your_domain.com</span>:<span class="highlight">app_specific_port_number</span>
</pre>

<p>If you select the "Use virtualhost naming for apps" check box, your apps will be accessible using a virtualhost instead:</p>

<pre>
http://<span class="highlight">app_name</span>.<span class="highlight">your_domain.com</span>
</pre>

<p>Click the "Finish Setup" button to complete the configuration.</p>

<h2 id="step-three-––-deploy-a-sample-node-js-application-to-your-dokku-droplet">Step Three –– Deploy a Sample Node.js Application to your Dokku Droplet</h2>

<hr />

<p>At this point, your Dokku droplet is configured and ready to start receiving git repositories, which it will deploy automatically.</p>

<p>This process is as simple as pushing a local git repository to your Dokku droplet.</p>

<h3 id="what-requirements-do-dokku-apps-have">What Requirements do Dokku Apps Have?</h3>

<hr />

<p>In order for Dokku to recognize a repository as housing a Node.js application, a file called <strong>package.json</strong> must be present at the document root.  This describes the application details and supplies the dependency and versioning information that will help Dokku deploy.</p>

<p>In order for the application to be correctly deployed on Dokku, a file called <strong>Procfile</strong> must be present as well.  This file contains the actual command that must be run to start the web process to serve your app.</p>

<ul>
<li><strong>Procfile</strong>: A file called "Procfile" is required to be in the root application directory.  The purpose of this file is to specify how the application should be run once deployed.</li>
</ul>

<p>Basically, it specifies the commands necessary to start the web process for your project.</p>

<p>For many Node.js projects, this can be as simple as:</p>

<pre>
web: node <span class="highlight">app_name</span>.js
</pre>

<ul>
<li><strong>package.json</strong>: In order for Dokku to recognize a repository as housing a Node.js application, this file must be present at the document root.  This describes the application details and supplies the dependency and versioning information that will help Dokku deploy.</li>
</ul>

<p>If you do not already have a package.json file, you can generate one by issuing this command in your application root directory:</p>
<pre class="code-pre "><code langs="">npm init
</code></pre>
<p>This will ask you quite a few questions and create a package.json file for you.  You will have to go back and manually add some dependency information for your application and an engine that describes the engines you're using.  This could look like this:</p>
<pre class="code-pre "><code langs="">. . .
"version": "0.2.3",
"dependencies": {
    "express": "3.1.x"
},
"engines": {
    "node": "0.10.x",
    "npm": "1.2.x"
},
"description": "a sample application for Dokku"
. . .
</code></pre>
<p>These are the two segments that tell Dokku what it needs to install and configure for your application environment.</p>

<h3 id="deploy-a-database-for-your-node-js-app">Deploy a Database for Your Node.js App</h3>

<hr />

<p>Dokku and Heroku handle databases and other resources as entities that should remain separate from the core application.  This allows you to easily switch the backend if another database will suit your needs better.</p>

<p>Dokku handles this through a plugin system.  We will install and configure this plugin system here.  If your application does not rely on a database, you can skip this section.</p>

<p>Our application will use a PostgreSQL database for its storage.  You can learn about the <a href="http://progrium.viewdocs.io/dokku/plugins/">available Dokku plugins</a> here, if your requirements are different.</p>

<p>To install the plugin, log into your Dokku droplet, and run:</p>
<pre class="code-pre "><code langs="">dokku plugin:install https://github.com/dokku/dokku-postgres.git
</code></pre>
<p>Now, we have the PostgreSQL libraries for Dokku installed.  Let's create a database for our application.  Dokku links an application with an associated database at deployment by looking for a database with a matching name.</p>

<p>We will have to decide what to name our application (which will have implications on the URL we will access it from if using virtual hosts), here.  Deploy a database with the following command:</p>

<pre>
dokku postgres:create <span class="highlight">app_name</span>
</pre>

<p>You will receive a message of confirmation that looks like this:</p>
<pre class="code-pre "><code langs="">-----> Starting container
       Waiting for container to be ready
       Creating container database
=====> Postgres container created: app_name
       DSN: postgres://postgres:1f61e96c987fc06cc5e71e70baeee776@172.17.0.9:5432/app_name
</code></pre>
<p>This tells you the connection details that are needed to use this database.  We can get this information at any time by typing:</p>

<pre>
dokku postgres:info <span class="highlight">app_name</span>
</pre>

<p>This information is used to create an environmental variable called <code>DATABASE_URL</code> that is assigned upon deployment.</p>

<h3 id="deploy-a-sample-node-js-app">Deploy a Sample Node.js App</h3>

<hr />

<p>Now, we can deploy a sample Node.js app to our Dokku server.  We will choose a simple <a href="https://github.com/tkalfigo/pigimon">Node.js Monitoring library</a> that will help us demonstrate some concepts.</p>

<p>On your development computer (the computer with the SSH key that matches the one you inputted in the Dokku setup), clone the repository with this command:</p>
<pre class="code-pre "><code langs="">git clone https://github.com/tkalfigo/pigimon.git
</code></pre>
<p>Enter the directory to begin modifying its content:</p>
<pre class="code-pre "><code langs="">cd pigimon
</code></pre>
<p>This project is in alpha stage, and is not built for deployment on Heroku or Dokku, so there are a few things that we need to modify.  First of all, there is no "master" branch, only a "develop" branch.  Dokku requires a master branch to deploy.  Let's make one and switch to it:</p>
<pre class="code-pre "><code langs="">git checkout -b master
</code></pre>
<p>We can see that there is already a "package.json" file that was mentioned above.  This is a good start.  However, the author forgot one dependency that will cause our build to fail currently.  Let's add it:</p>
<pre class="code-pre "><code langs="">nano package.json
</code></pre>
<p>In the "dependencies" section of the config file, we need to add a line to tell Dokku that any version of the "jsdom" module is also required.  You can insert it anywhere in the dependencies section, as long as you pay attention to the commas:</p>

<pre>
. . .
  "optimist": "~0.5.0",
  "pg": "~1.1.2",
  <span class="highlight">"jsdom": "*",</span>
  "underscore": "~1.4.4"
},
. . .
</pre>

<p>Save and close the file.</p>

<p>The next thing we need to do is add the "Procfile" that we mentioned as a requirement.  Our Procfile will be very simple, and will just tell Dokku to start at the <code>app.js</code> file to run the application:</p>
<pre class="code-pre "><code langs="">nano Procfile
</code></pre>
<hr />
<pre class="code-pre "><code langs="">web: node app.js
</code></pre>
<p>Save and close the file.</p>

<p>Finally, we will need to make a slight modification to how the application connects to the database.  As we mentioned, the app currently is not configured for Heroku or Dokku deployments, so it doesn't use the standard variables that these two pieces of software have created.  We need to have the app connect using the <code>DATABASE_URL</code> our PostgreSQL database will create.</p>

<p>Open the <code>app.js</code> file.</p>
<pre class="code-pre "><code langs="">nano app.js
</code></pre>
<p>Search for the PostgreSQL connection command that specifies how to connect to the database.  It should look like this:</p>
<pre class="code-pre "><code langs="">pg.connect(CONFIG.CONNECTION_STRING, function(err, client, done) {
</code></pre>
<p>Currently, the app is connecting using a configuration value in the <code>config.json</code> file.  This is represented by the <code>CONFIG.CONNECTION_STRING</code> value that is being passed to the <code>pg.connect</code> command.</p>

<p>Modify this value to use the <code>DATABASE_URL</code> variable.  Since this is an environmental variable, we refer to it as <code>process.env.DATABASE_URL</code>:</p>
<pre class="code-pre "><code langs="">pg.connect(process.env.DATABASE_URL, function(err, client, done) {
</code></pre>
<p>Save and close the file.</p>

<p>Now, our application is modified and should have all of the values needed to run successfully.  We need to commit our changes to git so that it is up-to-date:</p>
<pre class="code-pre "><code langs="">git add Procfile
git add .
git commit -m "Configure for Dokku"
</code></pre>
<p>Now, our application is created and we can push it to the Dokku server.  We just need to add the Dokku server as a remote on this project.  You can name the "remote<em>name" anything you would like, but "dokku" is a good choice.  The "app</em>name" must match the name you chose for your database:</p>

<pre>
git remote add <span class="highlight">remote_name</span> dokku@<span class="highlight">dokku_server_domain</span>:<span class="highlight">app_name</span>
</pre>

<p>Now, to deploy, we simply push our code to the Dokku server:</p>

<pre>
git push <span class="highlight">remote_name</span> master
</pre>

<p>You should see quite a bit of output as Dokku builds the dependencies and environment and then deploys your app.  At the end, you should see see something like this:</p>

<pre>
-----> Releasing app_name ...

-----> app_name linked to postgres/app_name database
-----> Deploying app_name ...
-----> Cleaning up ...
=====> Application deployed:
       <span class="highlight">http://app_name.domain_name.com</span>

To dokku@domain_name.com:app_name
</pre>

<p>If you go to the URL that is given, you should be able to see the sample application that we deployed:</p>

<p><img src="https://assets.digitalocean.com/articles/dokku_nodejs/sample_app.png" alt="Dokku Node.js sample app" /></p>

<p>If you ever need to remove an application that you have deployed, you can do so by issuing this command on the Dokku server:</p>

<pre>
dokku delete <span class="highlight">app_name</span>
</pre>

<p>To delete an associated PostgreSQL database, you can type:</p>

<pre>
dokku postgres:delete <span class="highlight">app_name</span>
</pre>

<h2 id="conclusion">Conclusion</h2>

<hr />

<p>At this point, you should have your first Node.js application up and running on your Dokku server.  Dokku is under heavy development, and undergoes changes from one release to the next.  We recommend that you regularly check the <a href="https://github.com/progrium/dokku">Dokku page on GitHub</a> if you plan on deploying production application.</p>

<div class="author">By Justin Ellingwood</div>

    