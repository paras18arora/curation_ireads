<link rel='stylesheet' href='digital_ocean.css'><br> <br> 
      <h3 id="about-lithium">About Lithium</h3>

<hr />

<p>Lithium is a full stack PHP framework for developing web applications. Based on the Model-View-Controller (MVC) architecture, it is built for PHP 5.3+ and integrates with the latest storage technologies like MongoDB or CouchDB.</p>

<p>It is designed to offer both  great project organization as well as the possibility to code out of the framework as you develop your own unique web application. Additionally, it features a robust plugin system that allows you to use your favorite components from outside the framework (such as Twig for templating or Doctrine2 for ORM).</p>

<p>In this tutorial we will look at how we can install Lithium on our VPS, as well as get started with a simple web application. For that I assume you already have your server set up and are running the LAMP stack (Apache, MySQL and PHP). If you don't already, there's a <a href="https://indiareads/community/articles/how-to-install-linux-apache-mysql-php-lamp-stack-on-ubuntu">great tutorial</a> on IndiaReads that can get you set up.</p>

<h2 id="apache-setup">Apache setup</h2>

<hr />

<p>Since we are using Apache as a webserver and Lithium makes heavy use of the .htaccess file for URL rewriting, we'll need to also make sure that Apache will in fact let it do that. If you haven't already done the following steps, you'll need to do them now. </p>

<p>Edit the virtual host file that is responsible for the folder where you will have the application (in our case, let's say the default Apache document root: /var/www):</p>
<pre class="code-pre "><code langs="">sudo nano /etc/apache2/sites-available/default
</code></pre>
<p>Inside the block marked with this beginning:</p>
<pre class="code-pre "><code langs=""><Directory /var/www/>
</code></pre>
<p>Make sure that instead of <code>AllowOverride None</code> you have <code>AllowOverride All</code>. </p>

<p>Next thing we need to do is enable <code>mod_rewrite</code> (again if you don't already have it enabled). To check if it's already enabled, use the following command:</p>
<pre class="code-pre "><code langs="">apache2ctl -M
</code></pre>
<p>If you see "rewrite_module" in the list, you are fine. If not, use the following command to enable the module:</p>
<pre class="code-pre "><code langs="">a2enmod rewrite 
</code></pre>
<p>After making any changes to either the virtual host file or enabling an Apache module, you have to restart Apache:</p>
<pre class="code-pre "><code langs="">sudo service apache2 restart
</code></pre>
<h2 id="installation">Installation</h2>

<hr />

<p>Before we begin installing Lithium, let's install Git so we can use it to fetch the framework from GitHub. You can do this with the following 2 commands:</p>
<pre class="code-pre "><code langs="">sudo apt-get update
sudo apt-get install git-core
</code></pre>
<p>Next, we can clone the Lithium git repository onto our server (while being in our web server's document root: /var/www for Apache):</p>
<pre class="code-pre "><code langs="">git clone git://github.com/UnionOfRAD/framework.git site
</code></pre>
<p>This will clone the framework repository and place it in a folder called <code>site</code>. Now we can install Lithium as a submodule:</p>
<pre class="code-pre "><code langs="">cd site
git submodule init
git submodule update
</code></pre>
<p>This will now clone the <code>lithium</code> library as well onto our server in the libraries/lithium/ folder. This will be needed for bootstrapping the application. </p>

<h2 id="command-line">Command line</h2>

<hr />

<p>Lithium comes with a command line utility (<code>li3</code>) that helps with code generation, documentation, etc. But to make it usable from anywhere, we'll need to add the console library to the shell path. Open the <code>.bash_profile</code> file located in your home folder (if you don't already have one you can create it):</p>
<pre class="code-pre "><code langs="">nano ~/.bash_profile 
</code></pre>
<p>And paste the following in it:</p>
<pre class="code-pre "><code langs="">PATH=$PATH:/path/to/docroot/lithium/libraries/lithium/console
</code></pre>
<p>Make sure you replace the path with the correct path that leads to the console in your case. So in our case it would be:</p>
<pre class="code-pre "><code langs="">PATH=$PATH:/var/www/site/libraries/lithium/console
</code></pre>
<p>After any such move, you should run the following command to make sure the bash command will take effect:</p>
<pre class="code-pre "><code langs="">source ~/.bash_profile
</code></pre>
<p>And now test the command to make sure it is working by running it without any options to get its help information:</p>
<pre class="code-pre "><code langs="">li3
</code></pre>
<h2 id="database-connection">Database connection</h2>

<hr />

<p>Most web applications need a database to rely on for storage. With Lithium, you can use a wide range of database engines like MySQL, MariaDB, MongoDB, CouchDB etc. For the purpose of setting up our test appplication we will use MySQL, but you are free to experiment with whatever you feel more comfortable. There is more information <a href="http://li3.me/docs/manual/quickstart">here</a> about setting it up with MongoDB. </p>

<p>The first thing we need is a database so make sure you have one. If you don't know how to work with MySQL and create your db, read <a href="https://indiareads/community/articles/a-basic-mysql-tutorial">this great tutorial</a> on using MySQL. </p>

<p>To set up a database connection, first edit the <code>bootstrap.php</code> file located in the app/config folder of your application (site/):</p>
<pre class="code-pre "><code langs="">nano /var/www/site/app/config/bootstrap.php
</code></pre>
<p>Inside this file, if commented, uncomment the following line:</p>
<pre class="code-pre "><code langs="">require __DIR__ . '/bootstrap/connections.php';
</code></pre>
<p>Then edit the following file:</p>
<pre class="code-pre "><code langs="">nano /var/www/site/app/config/bootstrap/connections.php
</code></pre>
<p>And uncomment the database configuration found under the following block:</p>
<pre class="code-pre "><code langs="">/**
* Uncomment this configuration to use MySQL as your default database.
*/
</code></pre>
<p>You'll notice multiple blocks like this for different database engines. Additionally, set your MySQL connection information where appropriate. </p>

<h2 id="your-application">Your application</h2>

<hr />

<p>It's time to visit the browser and see what we have so far. You can do so by navigating to your ip/site. There you should see your Lithium application up and running with some information about its status and the server configuration that needs to be made for it to work. </p>

<p>If you see the following message:</p>
<pre class="code-pre "><code langs="">Magic quotes are enabled in your PHP configuration
</code></pre>
<p>You need to edit the php.ini file on your server:</p>
<pre class="code-pre "><code langs="">sudo nano /etc/php5/apache2/php.ini
</code></pre>
<p>And paste the following line in it:</p>
<pre class="code-pre "><code langs="">magic_quotes_gpc = Off
</code></pre>
<p>Then save the file and restart Apache:</p>
<pre class="code-pre "><code langs="">sudo service apache2 restart
</code></pre>
<h2 id="model-view-controller">Model-View-Controller</h2>

<hr />

<p>Since Lithium is a MVC framework, you'll see in the folder structure 3 important folders for that: controllers/, models/ and views/. Let's quickly create our first controller and print <code>Hello world!</code> onto the page with it. </p>

<p>Create a new file in the controllers/ folder called <code>HelloController.php</code> with the following content:</p>
<pre class="code-pre "><code langs=""><?php

namespace app\controllers;

class HelloController extends \lithium\action\Controller {
 public function index() {
   echo "Hello World!";
 }
}

?>
</code></pre>
<p>You can save the file. What we did here was create a new controller class located in a carefully named file (based on the class name) and that extends the Lithium controller class. Inside, we created an index method that will get called if no parameters are passed when calling this controller. Inside this method we just print out the message.</p>

<p>To access this in the browser, you now have to navigate to your-ip/site/hello and you should see <code>Hello World</code> printed on the page. </p>

<h3 id="conclusion">Conclusion</h3>

<hr />

<p>In this tutorial, we've seen how to install Lithium PHP and make the necessary server configuration to make it work. We've seen how to connect it to a database (we have not used yet) and created our first controller that simply prints a message onto the page. </p>

<p>In the next tutorial, we will go a bit further and see how the MVC architecture works with Lithium. We'll use Views as well as Models (to illustrate the interaction with our MySQL storage engine). </p>

<div class="author">Article Submitted by: <a href="http://www.webomelette.com/">Danny</a></div>

    