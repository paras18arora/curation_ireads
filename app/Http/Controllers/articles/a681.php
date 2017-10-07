<link rel='stylesheet' href='digital_ocean.css'><br> <br> 
      <p><strong><span class="highlight">Note: The Dokku project has changed significantly since this guide was written.  The instructions below may not reflect the current state of the Dokku project.</span></strong></p>

<h3 id="introduction">Introduction</h3>

<hr />

<p>A significant hurdle in developing an application is providing a sane and easy way to deploy your finished product.  <strong>Dokku</strong> is a Platform as a Service solution that enables you to quickly deploy and configure an application to a production environment on a separate server. </p>

<p>Dokku is similar to Heroku in that you can deploy to a remote server.  The difference is that Dokku is built to deploy to a single, personal server and is extremely lightweight.  Dokku uses Docker, a Linux container system, to easily manage its deployments.</p>

<p>In this guide, we will cover how to deploy a Python/Flask app with Dokku using the IndiaReads Dokku one-click installation image.</p>

<h2 id="step-one-––-create-the-dokku-droplet">Step One –– Create the Dokku Droplet</h2>

<hr />

<p>The first thing we need to do is create the VPS instance that contains our Dokku installation.  This is simple to set up using the IndiaReads Dokku application.</p>

<p>Click on the "Create" button to create a new droplet:</p>

<p><img src="https://assets.digitalocean.com/articles/dokku_flask/create_drop.png" alt="IndiaReads create droplet" /></p>

<p>Name your droplet, and select the size and region that you would like to use:</p>

<p><img src="https://assets.digitalocean.com/articles/dokku_flask/config1.png" alt="IndiaReads configure droplet 1" /><br />
Scroll down and click on the "Applications" tab.  Select the Dokku application image:</p>

<p><img src="https://assets.digitalocean.com/articles/dokku/dokku_image.png" alt="IndiaReads Dokku image" /></p>

<p>Select your SSH keys if you have them available.  If you do not already have them configured, now is a great time to <a href="https://indiareads/community/articles/how-to-use-ssh-keys-with-digitalocean-droplets">create SSH keys to use with your IndiaReads droplets</a>.  This step will help you later.</p>

<p>Click "Create Droplet".  Your Dokku VPS instance will be created.</p>

<p><img src="https://assets.digitalocean.com/articles/dokku_flask/final_create.png" alt="IndiaReads final create" /></p>

<p>Once your droplet is created, you should set up your domain name to point to your new Dokku droplet.  You can learn how to <a href="https://indiareads/community/articles/how-to-set-up-a-host-name-with-digitalocean">configure domain names with IndiaReads</a> here.</p>

<h2 id="step-two-––-access-the-droplet-to-complete-configuration">Step Two –– Access the Droplet To Complete Configuration</h2>

<hr />

<p>You can complete your Dokku configuration by accessing your VPS from a web browser.</p>

<p>If you configured a domain name to point to your Dokku installation, you should visit your domain name with your favorite web browser.  If you do not have a domain name configured, you can use your droplet's IP address.</p>

<p>You will be given a simple configuration page.  There are a few parts that you need to configure here.</p>

<p><img src="https://assets.digitalocean.com/articles/dokku_flask/ssh_keys.png" alt="IndiaReads Dokku ssh keys" /></p>

<p>First, check that the Public Key matches the computer that you will be deploying <em>from</em>.  This means that if your project is on your home computer, you should use the public key that corresponds to that set up.</p>

<p>If you selected multiple SSH keys to embed during droplet creation, only the first will be available here.  Modify it as necessary.</p>

<p><img src="https://assets.digitalocean.com/articles/dokku_flask/hostname_config.png" alt="IndiaReads Dokku hostname configuration" /></p>

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

<h2 id="step-three-––-deploy-a-sample-flask-application-to-your-dokku-droplet">Step Three –– Deploy a Sample Flask Application to your Dokku Droplet</h2>

<hr />

<p>At this point, your Dokku droplet is configured and ready to start receiving git repositories, which it will deploy automatically.</p>

<p>This process is as simple as pushing a local git repository to your Dokku droplet.</p>

<h3 id="what-requirements-do-dokku-apps-have">What Requirements do Dokku Apps Have?</h3>

<hr />

<p>Besides functioning in its own right, for your flask application to be deployed successfully with Dokku (or Heroku, for that matter), it needs to have two components to know how to deploy the app.</p>

<ul>
<li><strong>Procfile</strong>: A file called "Procfile" is required to be in the root application directory.  The purpose of this file is to specify how the application should be run once deployed.</li>
</ul>

<p>Basically, it specifies the commands necessary to start the web process for your project.</p>

<p>For many Flask projects, this can be as simple as:</p>

<pre>
web: gunicorn <span class="highlight">app_name</span>:app
</pre>

<ul>
<li><strong>Dependencies File</strong>: You need to include a file that will tell Dokku which libraries and applications are needed to run your app.  This can either be a <strong>requirements.txt</strong> file or a <strong>setup.py</strong> file.</li>
</ul>

<p>If you do not already have a requirements.txt file and you've been using Python's <code>pip</code> installer to get your dependencies, you can try to auto-generate a requirements.txt file by issuing this command:</p>

<pre>
pip freeze > requirements.txt
</pre>

<p>This works best if you have built your app using <code>virtualenv</code> to isolate your application environment.</p>

<h3 id="deploy-a-flask-app-database">Deploy a Flask App Database</h3>

<hr />

<p>If the application you are deploying does not use a database, you can skip to the next section.  The sample app that we will deploy will use a database.</p>

<p>Dokku and Heroku both adhere to a philosophy that application resources, such as databases, should be handled as separate entities so that they can easily be swapped out without changes to the application code.</p>

<p>Because of this, databases must be deployed separately and then associated with the application.  This makes it easy to change a backend if there are problems.</p>

<p>Dokku handles databases through a plugin system.  If your application relies on a database, you can install the related Dokku plugin, which will handle the database deployment.</p>

<p>We will deploy a PostgreSQL database for our application.  Log into your Dokku droplet as root.</p>

<p>We will install the PostgreSQL plugin from GitHub.  You can see a <a href="https://github.com/progrium/dokku/wiki/Plugins#community-plugins">list of available Dokku plugins</a> here.</p>
<pre class="code-pre "><code langs="">dokku plugin:install https://github.com/dokku/dokku-postgres.git
</code></pre>
<p>You now have the Dokku PostgreSQL installed.  We will deploy a database instance to associate with our application.  We can see that with our plugin installation, the available commands for Dokku have been modified:</p>
<pre class="code-pre "><code langs="">dokku help
</code></pre>
<hr />
<pre class="code-pre "><code langs="">config <app>                                    display the config vars for an app
config:get <app> KEY                            display a config value for an app
config:set <app> KEY1=VALUE1 [KEY2=VALUE2 ...]  set one or more config vars
config:unset <app> KEY1 [KEY2 ...]              unset one or more config vars
delete <app>                                    Delete an application
help            Print the list of commands
logs <app>
plugins-install Install active plugins
plugins         Print active plugins
postgres:create <app>     Create a PostgreSQL container
postgres:delete <app>     Delete specified PostgreSQL container
postgres:info <app>       Display database informations
postgres:link <app> <db>  Link an app to a PostgreSQL database
postgres:logs <app>       Display last logs from PostgreSQL container
run <app> <cmd>                                 Run a command in the environment of an application
url <app>
</code></pre>
<p>We have some additional PostgreSQL commands available.  We will have to decide right now what we want to call our application.  This will have implications on the URL we will access our app from if we are using virtualhost naming.</p>

<p>When you know what you'd like to call your application, you can create the associated database by typing:</p>

<pre>
dokku postgres:create <span class="highlight">app_name</span>
</pre>

<p>Your database will be created and you can now deploy your application easily.</p>

<h3 id="deploy-your-flask-application">Deploy your Flask Application</h3>

<hr />

<p>To avoid having to build an entire application complex enough to use a database, we will use a sample found on GitHub.  We will clone the <a href="https://github.com/mattupstate/flask-social-example">Flask-Social example application</a>.</p>

<p>On the computer that has the RSA key that matched the one you deployed with Dokku, we will clone the repository.  You will need to install git on the computer.  The installation procedures will differ based on the operating system, but you can <a href="http://git-scm.com/downloads">get help here</a>.</p>

<p>Change into the directory where you want the project directory.  Type this command:</p>
<pre class="code-pre "><code langs="">git clone https://github.com/mattupstate/flask-social-example.git
</code></pre>
<p>Change into the directory that was created:</p>
<pre class="code-pre "><code langs="">cd flask-social-example
</code></pre>
<p>Now, we need to add a remote that references our Dokku server and the application name we chose earlier:</p>

<pre>
git remote add <span class="highlight">name_for_remote</span> dokku@<span class="highlight">your_domain.com</span>:<span class="highlight">app_name</span>
</pre>

<p>A few things to note here.  The <code>name_for_remote</code> can be anything you want.  It is simply how you're referencing your Dokku server.  In many cases, it would be sensible to name it <code>dokku</code>.</p>

<p>Another thing to be sure to get right is the <code>app_name</code>.  This must match the app_name you chose during the database creation.  It is the only way that Dokku will know to tie these two pieces together.</p>

<p>Now, all you need to do to put your application on the Dokku server and automatically deploy it is push the repository:</p>

<pre>
git push <span class="highlight">name_for_remote</span> master
</pre>

<p>Your application will be deployed to Dokku.  When the deployment is complete, you will a message similar to this:</p>

<pre>
-----> app_name linked to postgres/app_name database
-----> Deploying app_name ...
-----> Cleaning up ...
=====> Application deployed:
       http://<span class="highlight">app_name</span>.<span class="highlight">your_domain.com</span>

To dokku@your_domain.com:app_name
 * [new branch]      master -> master
</pre>

<p>If you go to the URL that you were given, you should see the sample app:</p>

<pre>
http://<span class="highlight">app_name</span>.<span class="highlight">your_domain.com</span>
</pre>

<p><img src="https://assets.digitalocean.com/articles/dokku_flask/flask_sample.png" alt="IndiaReads Dokku Flask sample" /></p>

<h2 id="conclusion">Conclusion</h2>

<hr />

<p>By now, you should have successfully deployed your first Python/Flask application on Dokku.  Further application deployments should be easy on the same droplet.</p>

<p>Python deployments on Dokku are easy if you keep the following points in mind:</p>

<ul>
<li><p>Build your application dependencies using <code>pip</code>.</p></li>
<li><p>Create a requirements.txt file or setup.py file so that Dokku can build your application dependencies</p></li>
<li><p>Include a Procfile so that Dokku knows how to start your application</p></li>
<li><p>Deploy your database backend separately using the Dokku plugin system.</p></li>
</ul>

<p>Once you get a handle on these few points, deploying applications to your own VPS server can happen rapidly and automatically.  Dokku can help make your life easier by taking the headache out of deployment.</p>

<div class="author">By Justin Ellingwood</div>

    