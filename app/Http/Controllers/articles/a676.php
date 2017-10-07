<link rel='stylesheet' href='digital_ocean.css'><br> <br> 
      <p><strong><span class="highlight">Note: The Dokku project has changed significantly since this guide was written.  The instructions below may not reflect the current state of the Dokku project.</span></strong></p>

<h3 id="introduction">Introduction</h3>

<hr />

<p>A significant hurdle in developing an application is providing a sane and easy way to deploy your finished product.  <strong>Dokku</strong> is a Platform as a Service solution that enables you to quickly deploy and configure an application to a production environment on a separate server. </p>

<p>Dokku is similar to Heroku in that you can deploy to a remote server.  The difference is that Dokku is built to deploy to a single, personal server and is extremely lightweight.  Dokku uses Docker, a Linux container system, to easily manage its deployments.</p>

<p>In this guide, we will cover how to deploy a Go app with Dokku using the IndiaReads Dokku one-click installation image.</p>

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
<pre class="code-pre "><code langs="">http://<span class="highlight">your_domain.com</span>:<span class="highlight">app_specific_port_number</span>
</code></pre>
<p>If you select the "Use virtualhost naming for apps" check box, your apps will be accessible using a virtualhost instead:</p>
<pre class="code-pre "><code langs="">http://<span class="highlight">app_name</span>.<span class="highlight">your_domain.com</span>
</code></pre>
<p>Click the "Finish Setup" button to complete the configuration.</p>

<h2 id="step-three-––-deploy-your-go-web-app-using-dokku">Step Three –– Deploy your Go Web App using Dokku</h2>

<hr />

<p>Now that you have your Dokku droplet configured, we can start thinking about how to deploy our Go application.</p>

<p>To demonstrate some more complex functionality, we will have our Go application interface with a PostgreSQL database and print some rows from that.</p>

<h3 id="install-the-postgresql-plugin-and-initialize-an-application-database">Install the PostgreSQL Plugin and Initialize an Application Database</h3>

<hr />

<p>Dokku adheres to a philosophy that application resources should be handled as separate, interchangeable pieces.  This means that databases are not incorporated snugly into the application code, but instead are handled as external resources.</p>

<p>This allows you to easily change either the code or the database without affecting the other part.  They are separate, but can be linked together easily and effortlessly.</p>

<p>Dokku implements this kind of functionality through a plugin system.  Dokku has a <a href="http://progrium.viewdocs.io/dokku/plugins/">full list of plugins</a> that you can use to extend the functionality of the core program.  This means that you don't have to incorporate extraneous code on your production server if your projects do not require the functionality they provide.</p>

<p>To install the PostgreSQL plugin directly from GitHub, run:</p>
<pre class="code-pre "><code langs="">dokku plugin:install https://github.com/dokku/dokku-postgres.git
</code></pre>
<p>If you issue the <code>dokku help</code> command, you can see that Dokku has already incorporated PostgreSQL functionality within our environment:</p>
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
<p>We have the PostgreSQL commands available, so let's make a database for our application:</p>
<pre class="code-pre "><code langs="">dokku postgres:create <span class="highlight">app_name</span>
</code></pre>
<p>The <code>app_name</code> in this command is pretty important.  It must match the name that you will choose for your project, which in turn, decides the URL you will use to access your application if you set up virtual hosts during Dokku configuration:</p>
<pre class="code-pre "><code langs="">http://<span class="highlight">app_name</span>.your_domain.com
</code></pre>
<p>This command will output some connection information for the database.  This is accessible at any time by typing:</p>

<p>dokku postgres:info <span class="highlight">app_name</span></p>

<p>In most cases, you will not have to use this information directly, because once your application is pushed, it will create an environmental variable called <code>DATABASE_URL</code> which you can use to connect to the database within your code.</p>

<h3 id="getting-a-go-application-to-deploy">Getting a Go Application to Deploy</h3>

<hr />

<p>On your development machine (the machine with the SSH key that matches the one you selected when setting up Dokku), we will be configuring our Go application.  This machine needs to have git installed in order to properly connect to our Dokku server.</p>

<p>You can find instructions on how to install git on various platforms on the <a href="https://help.github.com/articles/set-up-git">GitHub software</a>.  If you are on Ubuntu or Debian, you can simply type:</p>
<pre class="code-pre "><code langs="">sudo apt-get install git
</code></pre>
<p>For our Go application to correctly hook into our Dokku environment, we're going to require the use of a few libraries.  We need to "os" library to have access to the <code>DATABASE_URL</code> environmental variable, the "net/http" library to serve our web requests, and the "database/sql" and "lib/pq" libraries to handle the database interactions.</p>

<p>Rather than trying to write a program from scratch, let's clone a sample application from GitHub.  Go to your home directory and clone it:</p>
<pre class="code-pre "><code langs="">cd ~
git clone https://github.com/imchairmanm/go_test.git
</code></pre>
<p>Change into the project's directory:</p>
<pre class="code-pre "><code langs="">cd go_test
</code></pre>
<p>Here, we can see a few things that are necessary for your Go program to function correctly with Dokku.</p>

<ul>
<li><p><strong>Procfile</strong>: This file defines the command needed to start the Go web service.  In this case, this file points to the <code>web.go</code> file with the line <code>web: web.go</code>, which is our main program.</p></li>
<li><p><strong>.godir</strong>: This file contains the directory that we are currently in.  It is used to establish import paths and such.</p></li>
</ul>

<p>In our project, our program is contained entirely within the <code>web.go</code> program.  Open this with a text editor to take a look inside:</p>
<pre class="code-pre "><code langs="">nano web.go
</code></pre>
<p>In the top of the program, you'll see the import statements for the libraries that I mentioned above:</p>

<pre>
package main

import (
    <span class="highlight">"os"</span>
    "fmt"
    <span class="highlight">"net/http"</span>
    <span class="highlight">_ "github.com/lib/pq"</span>
    <span class="highlight">"database/sql"</span>
)
</pre>

<p>We hook into our database with these lines.  See how the <code>os.Getenv("DATABASE_URL")</code> line assigns an internal variable to the environmental variable.  This is how you will connect to the database with your own applications.</p>
<pre class="code-pre "><code langs="">dokku_db := os.Getenv("DATABASE_URL")

db, err = sql.Open("postgres", dokku_db)
if err != nil {
        fmt.Printf("sql.Open error: %v\n", err)
        return
}
defer db.Close()
</code></pre>
<p>The rest of the file involves creating a table and writing some initial data to it.  When you are finished, close the file.</p>

<h3 id="deploying-your-go-application-to-dokku">Deploying your Go Application to Dokku</h3>

<hr />

<p>Since we obtained this project from GitHub, it is already under version control.  We need to set up our Dokku droplet as a git remote for our project so that git can push it effectively.</p>

<p>This is done through a command like this:</p>

<pre>
git remote add <span class="highlight">remote_name</span> dokku@your_domain.com:<span class="highlight">app_name</span>
</pre>

<p>In this command, the <code>remote_name</code> can be anything you choose.  It is simply a label to reference the remote Dokku machine.  In most cases, "dokku" or "production" would be appropriate names.</p>

<p>The <code>app_name</code> parameter must match the name you selected for your database.  If your application did not use a backend database, this would be the place where you would be selecting the name you'd be using to access your application within the URL.</p>

<p>Now that our application is ready and knows about our Dokku server, we can deploy simply by pushing it to the remote server, where the Dokku program will take over and configure the application environment:</p>
<pre class="code-pre "><code langs="">git push <span class="highlight">remote_name</span> master
</code></pre>
<p>You should see some output that looks something like this:</p>

<pre>
-----> app_name linked to postgresql/app_name database
-----> Deploying here ...
-----> Cleaning up ...
=====> Application deployed:
       <span class="highlight">http://app_name.your_domain.com</span>

To dokku@your_domain.com:app_name
 * [new branch]      master -> master
 </pre>

<p>If you visit that URL in your web browser, you should see the output of the program:</p>

<p><img src="https://assets.digitalocean.com/articles/dokku_go/output.png" alt="Dokku Go application example" /></p>

<h2 id="conclusion">Conclusion</h2>

<hr />

<p>You should now have a sample Go application launched by your Dokku droplet.  While the example program is quite messy, it demonstrates some of the steps required to get your Go application to play nicely with a web deployment application like Dokku.  The deployment itself is very easy and happens quickly.</p>

<div class="author">By Justin Ellingwood</div>

    