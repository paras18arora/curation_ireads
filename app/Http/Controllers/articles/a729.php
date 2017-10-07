<link rel='stylesheet' href='digital_ocean.css'><br> <br> 
      <h3 id="prerequisites">Prerequisites</h3>

<p>This tutorial will illustrate the steps required to install and configure Slim Framework on a Digital Ocean VPS. By the end of this tutorial, you will have a well organized, working instance of Slim Framework, complete with a folder structure that you can base your project in.</p>

<p>This tutorial assumes that you have a LAMP (or your preferred) stack installed on Ubuntu. If you don't, you can refer to this article that helps you <a href="https://indiareads/community/articles/how-to-install-linux-apache-mysql-php-lamp-stack-on-ubuntu">install a LAMP stack on Ubuntu</a>.</p>

<p>If your application won't be using MySQL, you can skip its installation. The minimum you will need installed is an Apache web server (<a href="https://indiareads/community/articles/how-to-set-up-mod_rewrite">with Mod_Rewrite</a>) and PHP (minimum 5.3 version).</p>

<h2 id="quick-setup-for-prerequisites">Quick Setup for Prerequisites</h2>

<p><strong>1. Install Apache</strong></p>
<pre class="code-pre "><code langs="">apt-get update  
apt-get install apache2
</code></pre>
<p><strong>2. Install PHP</strong></p>
<pre class="code-pre "><code langs="">apt-get install php5 libapache2-mod-php5 php5-mcrypt
</code></pre>
<p><strong>3. Enable <code>mod_rewrite</code></strong></p>
<pre class="code-pre "><code langs="">a2enmod rewrite
</code></pre>
<p><strong>4. Modify the Apache configuration file</strong></p>

<p>Modify the Apache configuration file and change <strong>AllowOverride None</strong> to <strong>AllowOverride All</strong> for the document root. Depending on your server setup, this configuration file could be any one of the following:</p>

<ul>
<li><code>/etc/apache2/apache2.conf</code></li>
<li><code>/etc/apache2/sites-enabled/000-default</code></li>
<li><code>/etc/apache2/sites-available/default</code></li>
</ul>

<p>In the configuration file, locate the section that looks like the following:</p>
<pre class="code-pre "><code langs=""><Directory /var/www/>
        Options Indexes FollowSymLinks
        AllowOverride None
        Require all granted
</Directory>
</code></pre>
<p>Change this to the following and save the file:</p>
<pre class="code-pre "><code langs=""><Directory /var/www/>
        Options Indexes FollowSymLinks
        AllowOverride All
        Require all granted
</Directory>
</code></pre>
<p><strong>5. Restart Apache</strong></p>
<pre class="code-pre "><code langs="">service apache2 restart
</code></pre>
<p>This tutorial also assumes familiarity with <a href="https://indiareads/community/articles/an-introduction-to-linux-basics">Linux basics</a>.</p>

<h2 id="what-is-slim-framework">What is Slim Framework?</h2>

<p>Slim is one of the most popular, open source microframeworks available for PHP in the market. It is extremely efficient, fast, and easy to use. While it is ideal for developing small to medium sized web applications, it can also be used quite effectively to build large scalable PHP applications.</p>

<p>Slim is packed with the most common utilities that you would expect in a framework:</p>

<ul>
<li>Easy to use, powerful, and flexible router</li>
<li>Custom view to render templates</li>
<li>Secure cookies</li>
<li>HTTP caching</li>
<li>Easy to use error handling and debugging</li>
<li>Simple configuration</li>
</ul>

<h2 id="installation">Installation</h2>

<p>Installing Slim Framework consists of three steps</p>

<ol>
<li>Downloading Slim Framework</li>
<li>Extracting from Zip File</li>
<li>Copying Slim Framework to a Common Location</li>
</ol>

<h3 id="1-download-slim-framework">1. Download Slim Framework</h3>

<p>You can download the Slim Framework using the following command:</p>
<pre class="code-pre "><code langs="">wget https://github.com/codeguy/Slim/zipball/master
</code></pre>
<p>This will fetch the framework as a <code>zip</code> file and store it in the current directory with the name <code>master</code>.</p>

<h3 id="2-extract-from-the-zip-file">2. Extract from the Zip File</h3>

<p>The contents of the zip file can be extracted using the following command:</p>
<pre class="code-pre "><code langs="">unzip master -d ./
</code></pre>
<p><strong>Note:</strong> <em>If you get an error that unzip isn't installed, you can install it by using the command <code>apt-get install unzip</code> and then execute the above command to extract all the files.</em></p>

<p>The above command will extract the files in a folder named something like <code>codeguy-Slim-3a2ac72</code>. This folder contains a folder named Slim which is the framework folder.</p>

<h3 id="3-copy-slim-framework-to-a-common-location">3. Copy Slim Framework to a Common Location</h3>

<p>We will now copy the <code>codeguy-Slim-3a2ac72/Slim</code> folder to a common location like <code>/usr/local/Slim</code> from where it'll be accessible to all projects on this server that use Slim. This will avoid duplication and prevent any maintenance issues that could arise from duplicate installations.</p>

<p>Let's copy the folder using the following command:</p>
<pre class="code-pre "><code langs="">cp -r ./codeguy-Slim-3a2ac72/Slim /usr/local/Slim
</code></pre>
<p><strong>Note:</strong> <em>The name of the extracted folder (<code>codeguy-Slim-3a2ac72</code> in this case) might be slightly different if you download a different version of Slim. Make sure to modify the name of the folder in the above command accordingly</em></p>

<p>Once this is done, any of your projects that use Slim Framework can reference it from this location.</p>

<p><strong>Important Note:</strong> <em>A lot of tutorials install frameworks in the public folder/document root (like <code>/var/www/Slim</code>). Installing framework files outside the public folder/document root (as done above) makes the application relatively more secure as the framework files won't be accessible in a browser.</em></p>

<h2 id="organizing-your-slim-based-project">Organizing Your Slim Based Project</h2>

<p>A Slim project is typically spread over three main directories:</p>

<p><strong>1. Slim framework directory</strong><br /><br />
This directory contains the framework files and is the directory that was copied in the previous step (/usr/local/Slim)  </p>

<p><strong>2. Project directory</strong><br /><br />
This directory contains your project files like routers, views, models, etc. Being a microframework, Slim doesn't enforce any particular project structure. This means that you are free to structure your project files in any manner you deem fit. This is particularly helpful in cases when developers are used to a particular folder structure. </p>

<p>This directory can reside anywhere on the server, but ideally it <em>should not</em> be in a web accessible location. You can place it in the <code>/usr/local</code> or in your home folder. For example, if you create in the project in a folder named <code>HelloSlim</code>, it could be located at <code>/usr/local/HelloSlim</code> or <code>~/HelloSlim</code> or any other location you prefer. </p>

<p>Here's one way how files in this folder could be arranged:</p>
<pre class="code-pre "><code langs="">HelloSlim
|- Routes
|  |- route1.php
|  |- route2.php
|- Models
|  |- model1.php
|  |- model2.php
|- Views
|  |- footer.php
|  |- header.php
|  |- sidebar.php
|  |- view1.php
|  |- view2.php
|- Class
|  |- class1.php
|  |- class2.php
|- routes.php       //contains 'include' statements for all routes in the 'Routes' folder
|- includes.php     //contains 'include' statements for all models/classes in the 'Models/Class' folders
</code></pre>
<p>You can create this folder structure by executing the following commands:</p>
<pre class="code-pre "><code langs="">mkdir /usr/local/HelloSlim
mkdir /usr/local/HelloSlim/Routes
mkdir /usr/local/HelloSlim/Models
mkdir /usr/local/HelloSlim/Views
mkdir /usr/local/HelloSlim/Class
</code></pre>
<p><strong>Note:</strong> <em>You can use this folder structure or change it completely to suit your preferences.</em></p>

<p><strong>3. Document root/Public folder</strong><br /><br />
This is the web accessible folder (typically located at <code>/var/www</code>). This folder contains only two Slim related files:</p>

<ul>
<li>index.php</li>
<li>.htaccess</li>
</ul>

<p>This folder will also contain all the projects script, style and image files. To keep things organized, you can divide those into the <code>scripts</code>, <code>styles</code> and <code>images</code> folders respectively. </p>

<p>Here's a sample structure of the document root folder:</p>
<pre class="code-pre "><code langs="">Document Root (eg. /var/www/) 
|- scripts
|  |- jquery.min.js
|  |- custom.js
|- styles
|  |- style.css
|  |- bootstrap.min.css
|- images
|  |- logo.png
|  |- banner.jpg
|- .htaccess
|- index.php
</code></pre>
<h2 id="file-contents">File Contents</h2>

<p>Assuming that your project has the structure defined above, you'll need to fill the <code>.htaccess</code> and <code>index.php</code> files (in the document root) with the following contents respectively:</p>

<p><strong>.htaccess</strong></p>
<pre class="code-pre "><code langs="">RewriteEngine On  
RewriteCond %{REQUEST_FILENAME} !-f  
RewriteRule ^ index.php [QSA,L]  
</code></pre>
<p><strong>index.php</strong></p>
<pre class="code-pre "><code langs=""><?php

require '/usr/local/Slim/Slim.php';     //include the framework in the project
\Slim\Slim::registerAutoloader();       //register the autoloader

$projectDir = '/usr/local/HelloSlim';   //define the directory containing the project files

require "$projectDir/includes.php";     //include the file which contains all the project related includes

$app = new \Slim\Slim(array(
    'templates.path' => '/usr/local/HelloSlim/Views'
));      //instantiate a new Framework Object and define the path to the folder that holds the views for this project

require "$projectDir/routes.php";       //include the file which contains all the routes/route inclusions

$app->run();                            //load the application
</code></pre>
<p>To complete this tutorial assuming that the project has been arranged as per the folder structure defined in the previous section, the <code>routes.php</code> and <code>includes.php</code> files (in the project directory) should have the following contents:</p>

<p><strong>routes.php</strong></p>
<pre class="code-pre "><code langs=""><?php

require '/usr/local/HelloSlim/Routes/route1.php';
require '/usr/local/HelloSlim/Routes/route2.php';
</code></pre>
<p><strong>Note:</strong> <em>You could create the routes directly in this file instead of including other files containing routes. However, defining routes in different, logically grouped files will make your project more maintainable</em></p>

<p><strong>includes.php</strong></p>
<pre class="code-pre "><code langs=""><?php

require "/usr/local/HelloSlim/Class/class1.php";
require "/usr/local/HelloSlim/Class/class2.php";

require "/usr/local/HelloSlim/Models/model1.php";
require "/usr/local/HelloSlim/Models/model2.php";
</code></pre>
<h2 id="sample-slim-application">Sample Slim Application</h2>

<p>Now that you know how to set up a Slim application, let's create a simple application which does the following:</p>

<ul>
<li>Handles static Routes (GET & POST)</li>
<li>Handles dynamic Routes</li>
<li>Uses views</li>
</ul>

<p><strong>Note:</strong> <em>This sample application will assume that Slim has been deployed as described above.</em></p>

<p>Let's map out the requirements for this sample application:</p>

<table width="100%">
    <tr>
        <th>Route</th>
        <th>Type</th>
        <th>Action</th>
    </tr>
    <tr>
        <td>/hello</td>
        <td>GET (static)</td>
        <td>Displays a static View</td>
    </tr>
    <tr>
        <td>/hello/NAME</td>
        <td>GET (dynamic)</td>
        <td>Displays a dynamic View</td>
    </tr>
    <tr>
        <td>/greet</td>
        <td>POST</td>
        <td>Displays a View after a POST request</td>
    </tr>
</table>

<p>This project will require the following files to be created in the Application folder (<code>/usr/local/HelloSlim/</code>):</p>
<pre class="code-pre "><code langs="">HelloSlim
|- Routes
|  |- getRoutes.php
|  |- postRoutes.php
|- Views
|  |- footer.php
|  |- header.php
|  |- hello.php
|  |- greet.php
|- routes.php       
</code></pre>
<p>The public folder/document root will look something like the following:</p>

<p>Here's a sample structure of the document root folder:</p>
<pre class="code-pre "><code langs="">Document Root (eg. /var/www/) 
|- .htaccess
|- index.php
</code></pre>
<p>Now populate these files as follows:</p>

<p><strong>1. /var/www/.htaccess</strong></p>
<pre class="code-pre "><code langs="">RewriteEngine On  
RewriteCond %{REQUEST_FILENAME} !-f  
RewriteRule ^ index.php [QSA,L] 
</code></pre>
<p><strong>2. /var/www/index.php</strong></p>
<pre class="code-pre "><code langs=""><?php

require '/usr/local/Slim/Slim.php';     //include the framework in the project
\Slim\Slim::registerAutoloader();       //register the autoloader

$projectDir = '/usr/local/HelloSlim';   //define the directory containing the project files

$app = new \Slim\Slim(array(
    'templates.path' => '/usr/local/HelloSlim/Views'
));      //instantiate a new Framework Object and define the path to the folder that holds the views for this project

require "$projectDir/routes.php";       //include the file which contains all the routes/route inclusions

$app->run();                            //load the application
</code></pre>
<p><strong>3. /usr/local/HelloSlim/Routes/getRoutes.php</strong></p>
<pre class="code-pre "><code langs=""><?php

$app->get('/', function(){
    echo 'This is a simple starting page';
});

//The following handles any request to the /hello route

$app->get('/hello', function() use ($app){
    // the following statement invokes and displays the hello.php View
    $app->render('hello.php');
});


//The following handles any dynamic requests to the /hello/NAME routes (like /hello/world)

$app->get('/hello/:name', function($name) use ($app){
    // the following statement invokes and displays the hello.php View. It also passes the $name variable in an array so that the view can use it.
    $app->render('hello.php', array('name' => $name));
});
</code></pre>
<p><strong>4. /usr/local/HelloSlim/Routes/postRoutes.php</strong></p>
<pre class="code-pre "><code langs=""><?php

 //The following handles the POST requests sent to the /greet route

$app->post('/greet', function() use ($app){
    //The following statement checks if 'name' has been POSTed. If it has, it assigns the value to the $name variable. If it hasn't been set, it assigns a blank string.
    $name = (null !== $app->request->post('name'))?$app->request->post('name'):'';

    //The following statement checks if 'greeting' has been POSTed. If it has, it assigns the value to the $greeting variable. If it hasn't been set, it assigns a blank string.
    $greeting = (null !== $app->request->post('greeting'))?$app->request->post('greeting'):'';

    // the following statement invokes and displays the 'greet.php' View. It also passes the $name & $greeting variables in an array so that the view can use them.
    $app->render('greet.php', array(
        'name' => $name,
        'greeting' => $greeting
    ));
});
</code></pre>
<p><strong>5. /usr/local/HelloSlim/Views/footer.php</strong></p>
<pre class="code-pre "><code langs="">        <small>Copyright notice...</small>
    </body>
</html>
</code></pre>
<p><strong>6. /usr/local/HelloSlim/Views/header.php</strong></p>

<pre>
<!DOCTYPE html>
      <html>
          <head>
               <title>Sample Slim Application</title>
          </head<
          <body>
</pre>

<p><strong>7. /usr/local/HelloSlim/Views/hello.php</strong></p>
<pre class="code-pre "><code langs="">
***
<?php include('header.php'); ?>

***
<h1>Hello <?php echo isset($name)?$name:''; ?></h1>
<!-- The above line handles both the dynamic and the static GET routes that we implemented in the getRoutes.php file.

***

<h2>Send a greeting</h2>
<form method='POST' action='/greet'>
    <label>Name</label><br>
    <input name='name' placeholder='Who do you want to greet?'><br>
    <label>Greeting</label><br>
    <input name='greeting' placeholder='Your greeting message'><br>
    <input type='submit' value='Greet!'>
</form>

***
<?php include('footer.php'); ?>
</code></pre>
<p><strong>8. /usr/local/HelloSlim/Views/greet.php</strong></p>
<pre class="code-pre "><code langs="">    <?php 

    include('header.php'); 

    echo "<p>$greeting, $name</p><p><a href='/hello'>First Page</a></p>";

    include('footer.php'); 
</code></pre>
<p><strong>9. /usr/local/HelloSlim/routes.php</strong></p>
<pre class="code-pre "><code langs="">    <?php

    include 'Routes/getRoutes.php';
    include 'Routes/postRoutes.php';
</code></pre>
<h2 id="sample-application-screenshots">Sample Application Screenshots</h2>

<p>If you visit your newly created sample application at <code>http://yourdomain.com/</code>, you'll see something like the following:</p>

<p><img src="https://assets.digitalocean.com/tutorial_images/I8tss2w.png" alt="Starting Page" title="Starting Page" /></p>

<p><strong>Note:</strong> <em>If you are not using a domain name with your Digital Ocean droplet, use the IP address of the droplet instead.</em></p>

<p>If you visit <code>http://yourdomain.com/hello</code>, you'll get the following:</p>

<p><img src="https://assets.digitalocean.com/tutorial_images/p5UQLb7.png" alt="Hello Static" title="Hello Static" /></p>

<p>If you visit <code>http://yourdomain.com/hello/World</code>, you'll get the following:</p>

<p><img src="https://assets.digitalocean.com/tutorial_images/jSaT39B.png" alt="Hello Dynamic" title="Hello Dynamic" /></p>

<p><strong>Note:</strong> <em>If you replace the 'World' in the URL with another word, the content of the page will change accordingly.</em></p>

<p>To test the POST route, enter a name and greeting in the available fields and hit the 'Greet!' button as follows:</p>

<p><img src="https://assets.digitalocean.com/tutorial_images/nAwG6Dk.png" alt="Greeting Entry" title="Greeting Entry" /></p>

<p>After hitting the 'Greet!' button, you should get something like the following:</p>

<p><img src="https://assets.digitalocean.com/tutorial_images/ZXFjXSM.png" alt="Greeting Result" title="Greeting Result" /></p>

<h2 id="final-word">Final Word</h2>

<p>Now that you have a well organized working instance of the Slim framework installed, you are ready to start working on your project. If you need additional help with Slim, you can always refer to the <a href="http://docs.slimframework.com/">comprehensive official documentation</a>.</p>

<div class="author">Submitted by: <a href="http://www.php.buzz">Jay</a></div>

    