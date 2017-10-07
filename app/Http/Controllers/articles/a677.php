<link rel='stylesheet' href='digital_ocean.css'><br> <br> 
      <p><strong><span class="highlight">Note: The Dokku project has changed significantly since this guide was written.  The instructions below may not reflect the current state of the Dokku project.</span></strong></p>

<h3 id="introduction">Introduction</h3>

<hr />

<p>A significant hurdle in developing an application is providing a sane and easy way to deploy your finished product.  <strong>Dokku</strong> is a Platform as a Service solution that enables you to quickly deploy and configure an application to a production environment on a separate server. </p>

<p>Dokku is similar to Heroku in that you can deploy to a remote server.  The difference is that Dokku is built to deploy to a single, personal server and is extremely lightweight.  Dokku uses Docker, a Linux container system, to easily manage its deployments.</p>

<p>In this guide, we will cover how to deploy a PHP app with Dokku using the IndiaReads Dokku one-click installation image.</p>

<h2 id="step-one-––-create-the-dokku-droplet">Step One –– Create the Dokku Droplet</h2>

<hr />

<p>The first thing we need to do is create the VPS instance that contains our Dokku installation.  This is simple to set up using the IndiaReads Dokku application.</p>

<p>Click on the "Create" button to create a new droplet:</p>

<p><img src="https://assets.digitalocean.com/articles/dokku_rails/create_drop.png" alt="IndiaReads create droplet" /></p>

<p>Name your droplet, and select the size and region that you would like to use:</p>

<p><img src="https://assets.digitalocean.com/articles/dokku_rails/config1.png" alt="IndiaReads configure droplet 1" /></p>

<p>Scroll down and click on the "Applications" tab.  Select the Dokku application image:</p>

<p><img src="https://assets.digitalocean.com/articles/dokku/dokku_image.png" alt="IndiaReads Dokku image" /></p>

<p>Select your SSH keys if you have them available.  If you do not already have them configured, now is a great time to <a href="https://indiareads/community/articles/how-to-use-ssh-keys-with-digitalocean-droplets">create SSH keys to use with your IndiaReads droplets</a>.  This step will help you later.</p>

<p>Click "Create Droplet".  Your Dokku VPS instance will be created.</p>

<p><img src="https://assets.digitalocean.com/articles/dokku_rails/final_create.png" alt="IndiaReads final create" /></p>

<p>Once your droplet is created, you should set up your domain name to point to your new Dokku droplet.  You can learn how to <a href="https://indiareads/community/articles/how-to-set-up-a-host-name-with-digitalocean">configure domain names with IndiaReads</a> here.</p>

<h2 id="step-two-––-access-the-droplet-to-complete-configuration">Step Two –– Access the Droplet To Complete Configuration</h2>

<hr />

<p>You can complete your Dokku configuration by accessing your VPS from a web browser.</p>

<p>If you configured a domain name to point to your Dokku installation, you should visit your domain name with your favorite web browser.  If you do not have a domain name configured, you can use your droplet's IP address.</p>

<p>You will be given a simple configuration page.  There are a few parts that you need to configure here.</p>

<p><img src="https://assets.digitalocean.com/articles/dokku_rails/ssh_keys.png" alt="IndiaReads Dokku ssh keys" /></p>

<p>First, check that the Public Key matches the computer that you will be deploying <em>from</em>.  This means that if your project is on your home computer, you should use the public key that corresponds to that set up.</p>

<p>If you selected multiple SSH keys to embed during droplet creation, only the first will be available here.  Modify it as necessary.</p>

<p><img src="https://assets.digitalocean.com/articles/dokku_rails/hostname_config.png" alt="IndiaReads Dokku hostname configuration" /></p>

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

<h2 id="step-three-––-deploy-a-simple-php-application-to-your-dokku-droplet">Step Three –– Deploy a Simple PHP Application to your Dokku Droplet</h2>

<hr />

<p>Now that we have Dokku configured correctly, we are ready to begin preparing things for our deployment.  Dokku handles PHP applications smoothly, so you should not have to tinker with your code very much to successfully launch your application.</p>

<h3 id="create-an-application-database-with-dokku">Create an Application Database with Dokku</h3>

<hr />

<p>One thing you may be familiar with if you have deployed apps using other services is the idea of the database being separate from your application environment.</p>

<p>Configuring things in this way allows you to easily divorce your data from the application if either the database backend or the code itself must be updated.  It also allows you to switch databases on a whim with little or no fuss.</p>

<p>In order to stay lean, Dokku handles database resources through a plugin system.  We can install the database plugin we need without the overhead of having the code for each separate database packaged into our deployment environment.  Dokku has a page in their wiki listing <a href="https://github.com/progrium/dokku/wiki/Plugins#community-plugins">available plugins</a>.  Each has their own installation instructions.</p>

<p>Our application will take advantage of a PostgreSQL database, so we will install the associated plugin.  To install the PostgreSQL plugin directly from GitHub, run:</p>
<pre class="code-pre "><code langs="">dokku plugin:install https://github.com/dokku/dokku-postgres.git
</code></pre>
<p>This will download and configure all of the necessary packages for Dokku to integrate PostgreSQL functionality within apps.  If we look at the Dokku help command, we can see that the new database commands are already available to us:</p>
<pre class="code-pre "><code langs="">dokku help
</code></pre>
<hr />
<pre class="code-pre "><code langs="">. . .
plugins         Print active plugins
postgres:create <app>     Create a PostgreSQL container
postgres:delete <app>     Delete specified PostgreSQL container
postgres:info <app>       Display database informations
postgres:link <app> <db>  Link an app to a PostgreSQL database
postgres:logs <app>       Display last logs from PostgreSQL container
run <app> <cmd>                                 Run a command in the environment of an application
. . .
</code></pre>
<p>We will create a database now that our application can then hook into once it's deployed.  This is the point where we will need to decide the name of our application.  If you set up virtual hosts during the Dokku configuration, the app's name will be present in the URL to reach it:</p>

<pre>
dokku postgres:create <span class="highlight">php_app</span>
</pre>

<p>It will give you the connection information for accessing this database.  For the most part, you will not need to connect to the database using this information because when your app is deployed, this information is accessible in a formatted URL within an environmental variable called <code>DATABASE_URL</code>.</p>

<h3 id="fill-in-application-data">Fill in Application Data</h3>

<hr />

<p>Our PHP application will be very simple and straight forward.  In fact, it will be so simple that it will not include methods of inserting data within our database, only querying it.</p>

<p>To work around this, we're going to prime our database with a few pieces of data so that we can query later on.  This would normally not be necessary at all.  It is just for the sake of demonstration.</p>

<p>First, on the Dokku droplet, install the PostgreSQL utilities so that we can connect to the database:</p>
<pre class="code-pre "><code langs="">apt-get install postgresql
</code></pre>
<p>Now, we need to get those connection parameters that were given as output during the database configuration.  We can recall them by typing:</p>

<pre>
dokku postgres:info <span class="highlight">php_app</span>
</pre>

<pre>
    DSN: postgres://postgres:d257ed7158ae9a01339615aef4a3f871@172.17.0.43:5432/php_app
</pre>

<p>We can then connect to the database using the <code>psql</code> command, substituting the data here:</p>

<pre>
psql -h <span class="highlight">postgres://postgres:d257ed7158ae9a01339615aef4a3f871@172.17.0.43:5432/php_app</span>
</pre>

<p>You will be given a PostgreSQL prompt.</p>

<p>We will create a simple table that we can use to query later:</p>
<pre class="code-pre "><code langs="">CREATE TABLE picnic (
    item_id SERIAL,
    item_name VARCHAR(30),
    item_quantity INTEGER
);
</code></pre>
<p>Now, insert a few values into the table:</p>
<pre class="code-pre "><code langs="">INSERT INTO picnic (item_name, item_quantity) VALUES
    ('sandwich', 6),
    ('root_beer', 4),
    ('water', 2),
    ('salad', 6),
    ('potato_chips', 3),
    ('grapes', 50);
</code></pre>
<p>Exit out of PostgreSQL so that we can continue:</p>
<pre class="code-pre "><code langs="">\q
</code></pre>
<h3 id="create-or-configure-your-php-application">Create or Configure your PHP Application</h3>

<hr />

<p>On our development machine (whichever computer has the SSH key that matches the one you entered during the Dokku setup), we will be developing our PHP application.</p>

<p>For the purpose of this tutorial, we will assume that you are developing on an Ubuntu 12.04 VPS, but it could really be any machine as long as you can install git onto it.  If your development computer is running Ubuntu and you don't have git installed yet, install it now:</p>
<pre class="code-pre "><code langs="">sudo apt-get install git
</code></pre>
<p>We will be getting a simple PHP application from GitHub because it does a great job of demonstrating a strategy for connecting to a PostgreSQL database which stores its credentials in a <code>DATABASE_URL</code> environmental variable.</p>

<p>Clone the repository within your user's home directory and then enter the application directory like this:</p>
<pre class="code-pre "><code langs="">cd ~
git clone https://github.com/kch/heroku-php-pg.git php_app
cd php_app
</code></pre>
<p>Now, we will examine the <code>index.php</code> file and modify it a little to use the table we just created:</p>
<pre class="code-pre "><code langs="">nano index.php
</code></pre>
<p>Before we make any changes, I want to point out the method the author uses to connect to the database:</p>
<pre class="code-pre "><code langs="">function pg_connection_string_from_database_url() {
    extract(parse_url($_ENV["DATABASE_URL"]));
    return "user=$user password=$pass host=$host dbname=" . substr($path, 1);
}

$pg_conn = pg_connect(pg_connection_string_from_database_url());
</code></pre>
<p>The <code>pg_connection_string_from_database_url</code> function is used to parse the <code>DATABASE_URL</code> environmental variable and assign the relevant pieces to variables that coincide with the way that PHP connects to PostgreSQL databases.</p>

<p>Let me break it down further:</p>

<ul>
<li><code>$_ENV["DATABASE_URL"]</code>: provides access to the DATABASE_URL environmental variable</li>
<li><code>parse_url</code>: Creates an associative array containing the components of the URL</li>
<li><code>extract</code>: This imports an array of variables into the symbol table</li>
</ul>

<p>The result is that we associate the parameters necessary to establish a database connection with variables.  We then pass these out of the function as variable names that the PHP PostgreSQL library recognizes as connection information.</p>

<p>These variables are passed to the <code>pg_connect</code> function, which returns an object that represents the open database connection.</p>

<p>Now that you have an understanding of how PHP establishes a connection to the database, we will modify the querying string contained in this line:</p>
<pre class="code-pre "><code langs="">$result = pg_query($pg_conn, "SELECT relname FROM pg_stat_user_tables WHERE schemaname='public'");
</code></pre>
<p>Comment this line out and then write a new query string below it that will use the table we just created:</p>
<pre class="code-pre "><code langs="">$result = pg_query($pg_conn, "SELECT item_name,item_quantity from picnic;");
</code></pre>
<p>We will also change some of the lines that print to better reflect our content.  Find the section that currently prints the table names:</p>
<pre class="code-pre "><code langs="">print "Tables in your database:\n";
while ($row = pg_fetch_row($result)) { print("- $row[0]\n"); }
</code></pre>
<p>We want to modify the first line to correctly label our content as picnic items.  We also want to change the second line to not only print the first item in the row (<code>$row[0]</code>), but also the second item, so that we can see the associated quantity of each food item for our picnic:</p>
<pre class="code-pre "><code langs="">// print "Tables in your database:\n";
// while ($row = pg_fetch_row($result)) { print("- $row[0]\n"); }
print "Items in your picnic:\n";
while ($row = pg_fetch_row($result)) { print("- $row[0] -> $row[1]\n"); }
</code></pre>
<p>Save and close the file.</p>

<p>Since we cloned this project from GitHub, it is already under version control, but we need to add our new changes to the repository:</p>
<pre class="code-pre "><code langs="">git add .
git commit -m 'Modify database connection and printing'
</code></pre>
<p>Our application is complete and our git repository is complete.</p>

<h3 id="deploy-your-php-application-to-dokku">Deploy your PHP Application to Dokku</h3>

<hr />

<p>The app is now to the point where it is ready for production.  We have created a PostgreSQL database on our Dokku droplet that will serve this application.  All that's left to do is actually deploy the app.</p>

<p>To deploy our app to Dokku, all we need to do is git push our files to the Dokku droplet.  Dokku will configure and launch our app automatically.  The first step is to add the Dokku droplet as a git remote for our application.</p>

<pre>
git remote add <span class="highlight">remote_name</span> dokku@your_domain.com:<span class="highlight">php_app</span>
</pre>

<p>In this scenario, the <code>remote_name</code> can be anything you'd like.  It's just a label that is used on the local computer to refer to your Dokku droplet.  In most cases, names like "dokku" or "production" would be appropriate and descriptive.</p>

<p>However, the <code>php_app</code> label is important because it has to match the name you chose for your database.  This is also the piece that decides how your app will be accessed through virtual hosts.</p>

<p>Now, deploy your application by pushing it to Dokku:</p>

<pre>
git push <span class="highlight">remote_name</span> master
</pre>

<p>You should see the Dokku output on your local machine.  At the end, it will give you the URL you can use to access your application:</p>

<pre>
       Default process types for PHP (classic) -> web
-----> Releasing php_app ...
-----> Deploying php_app ...
-----> Cleaning up ...
=====> Application deployed:
       <span class="highlight">http://php_app.your_domain.com</span>

To dokku@your_domain.com:php_app
   e2b2547..5dfaed7  master -> master
</pre>

<p>If you visit this page in your web browser, you should be able to see the results of our PHP application:</p>

<pre>
http://<span class="highlight">php_app</span>.your_domain.com
</pre>

<p><img src="https://assets.digitalocean.com/articles/dokku_php/php_app.png" alt="Dokku PHP app deployment" /></p>

<h2 id="conclusion">Conclusion</h2>

<hr />

<p>You have now deployed a PHP application to your Dokku droplet.  Although our example application was rather simple, you can see how it is easy to tie in a database to your application through Dokku's plugin system.  You can apply the same steps we used here in more complex examples.  Dokku will take care of the rest of the configuration and deployment.</p>

<div class="author">By Justin Ellingwood</div>

    