<link rel='stylesheet' href='digital_ocean.css'><br><img src=https://community-cdn-digitalocean-com.global.ssl.fastly.net/assets/tutorials/images/large/01272014Phalcon_twitter.png?1426699635/> <br> 
      <div><h3>About Phalcon</h3><hr />

<a href="http://phalconphp.com/en/">Phalcon</a> is a PHP framework that promotes the Model-View-Controller architecture and has many framework-like features you'd expect in a piece of software like this - ORM, templating engine, routing, caching, etc. 
<br /><br />
One cool thing about it is that performance-wise, it is arguably much faster than other frameworks out there. The reason is that it is not your ordinary PHP framework whose files you just copy onto your server and you are good to go. It is in fact a PHP extension written in C.
<br /><br />
In this article we will look at how to get started with Phalcon on your VPS running Ubuntu 12.04. If you are following along, I assume you already have your server set up with the LAMP stack (Apache, MySQL and PHP). There is a good <a href="https://indiareads/community/articles/how-to-install-linux-apache-mysql-php-lamp-stack-on-ubuntu">tutorial on IndiaReads</a> to help you get set up if needed. 

<h2>Installation</h2><hr />

The first thing you need to do is install the requirements for Phalcon. Run the following three commands to do that:

<pre>sudo apt-get update
sudo apt-get install git-core gcc autoconf make
sudo apt-get install php5-dev php5-mysql</pre>
<br />
You can remove packages from the commands if you already have them installed (for instance <em>git-core</em> if you already have Git installed). Next up, you need to clone the framework repo onto your system:

<pre>git clone git://github.com/phalcon/cphalcon.git</pre>
<br />
After that is done, navigate in the following folder with this command:

<pre>cd cphalcon/build</pre>
<br />
And run the install file to install the extension:

<pre>sudo ./install</pre>
<br />
What you need to do next is edit your <strong>php.ini</strong> file:

<pre>nano /etc/php5/apache2/php.ini</pre>
<br />
And add the following line to the end of it:

<pre>extension=phalcon.so</pre>
<br />
Then restart your server for the changes to take effect:

<pre>sudo service apache2 restart</pre>
<br />
And this should do it. To check if Phalcon has been successfully installed, you'll need to check the output of <strong>phpinfo()</strong> statement. If you don't know how to proceed, create a file called <strong>info.php</strong> somewhere where you can access it from the browser and paste in the following line:

<pre><?php phpinfo(); ?></pre>
<br />
Save the file and point your browser to it. In the PHP information displayed on that page, you should see that the Phalcon framework is enabled and also verify its version. 

<h2>Your first Phalcon project structure</h2><hr />

If you've used other PHP frameworks, you would be expecting some framework related files somewhere in your project's folder structure. With Phalcon, all these files are readily available in memory, so all you need to do to get started is create an empty folder structure somewhere in Apache's document root (defaults to <em>/var/www</em>). The recommended way to go is the following:

<pre>project_name/
 app/
   controllers/
   models/
   views/
 public/
   css/
   img/
   js/</pre>
<br />
So what you have here is a project folder which has 2 main folders: <em>app</em> and <em>public</em>. The first will house the logic of your application (mostly PHP) whereas the second one is where your browser will point and be redirected to the resources in the app folder on the one hand, and have access to all the frontend assets, on the other.

<h2>Bootstrapping</h2><hr />

The first and most important file you need to create is your <strong>index.php</strong> file the application will use to bootstrap. Create this file in the public/ folder of your application:

<pre>nano /var/www/project_name/public/index.php</pre>
<br />
And paste in the following code:

<pre><?php

try {

   //Register an autoloader
   $loader = new \Phalcon\Loader();
   $loader->registerDirs(array(
       '../app/controllers/',
       '../app/models/'
   ))->register();

   //Create a DI
   $di = new Phalcon\DI\FactoryDefault();

   //Setup the view component
   $di->set('view', function(){
       $view = new \Phalcon\Mvc\View();
       $view->setViewsDir('../app/views/');
       return $view;
   });

   //Setup a base URI so that all generated URIs include the "tutorial" folder
   $di->set('url', function(){
       $url = new \Phalcon\Mvc\Url();
       $url->setBaseUri('/project_name/');
       return $url;
   });

   //Handle the request
   $application = new \Phalcon\Mvc\Application($di);

   echo $application->handle()->getContent();

} catch(\Phalcon\Exception $e) {
    echo "PhalconException: ", $e->getMessage();
}</pre>
<br />
For more information about what this file contains, you can check the official Phalcon website. But please note that you need to replace this line:

<pre>$url->setBaseUri('/project_name/');</pre>
<br />
With one that is appropriate for your case, i.e. containing the name of your project folder.

<h2>URL Rewriting</h2><hr />

Phalcon will need to make use of <em>.htaccess</em> files to make some important rerouting and secure the application's folder structure from prying eyes. For this, the <strong>mod_rewrite</strong> module from Apache needs to be enabled and <em>.htaccess</em> files need to be allowed to make modifications to the Apache instructions. 
<br /><br />
So, if this is not the case for you, edit the Apache virtual host file under which the Phalcon application is (defaults to <em>/var/www</em> if you do not have some particular virtual host for this application), and make sure that <strong>Allow Overrides</strong> is set to <strong>All</strong> under the <em>/var/www</em> directory (again, if your application is in the default Apache document root). You can edit the default virtual host file with the following command:

<pre>nano /etc/apache2/sites-available/default</pre>
<br />
And where you see this block, make the changes to correspond to the following.

<pre><Directory /var/www/>
   Options Indexes FollowSymLinks MultiViews
   AllowOverride All
   Order allow,deny
   allow from all
</Directory></pre>
<br />
Finally, make sure that <strong>mod_rewrite</strong> is enabled in your Apache. To check if it is already enabled, use the following command:

<pre>apache2ctl -M</pre>
<br />
If you see "rewrite_module" in the list, you are fine. If not, use the following command to enable the module:

<pre>a2enmod rewrite</pre>
<br />
After all these steps, or after any individual one you had to perform, give Apache a restart so they take effect:

<pre>sudo service apache2 restart</pre>
<br />
Now that this is taken care of, create an <em>.htaccess</em> file in the main project folder:

<pre>nano /var/www/project_name/.htaccess</pre>
<br />
And paste in the following directives:

<pre><IfModule mod_rewrite.c>
   RewriteEngine on
   RewriteRule  ^$ public/    [L]
   RewriteRule  (.*) public/$1 [L]
</IfModule></pre>
<br />
This will reroute all requests from the project main folder to the public/ folder. Now create another <em>.htaccess</em> file but this time in the public/ folder:

<pre>nano /var/www/project_name/public/.htaccess</pre>
<br />
And paste in the following directives:

<pre><IfModule mod_rewrite.c>
   RewriteEngine On
   RewriteCond %{REQUEST_FILENAME} !-d
   RewriteCond %{REQUEST_FILENAME} !-f
   RewriteRule ^(.*)$ index.php?_url=/$1 [QSA,L]
</IfModule></pre>
<br />
This will reroute all the requests coming to this folder to the index.php file (in case an actual file by the name requested does not exist in that folder or below - you know, for the frontend assets to still be accessible).

<h2>Your first controller</h2><hr />

If you now point your browser to the project folder, you'll get an error that the Index controller class could not be loaded. That's because by default, the application needs one of these if there is not controller passed in with the request. So let's create one to display <em>Hello World</em> onto the page. 
<br /><br />
Create the following <em>IndexController.php</em> file in the controllers folder:

<pre>nano /var/www/project_name/app/controllers/IndexController.php</pre>
<br /><br />
Inside, paste the following:

<pre><?php

class IndexController extends \Phalcon\Mvc\Controller {

   public function indexAction()    {
       echo "<h1>Hello World!</h1>";
   }

}</pre>
<br />
You'll notice that we extend the default Phalcon Controller class and name it <strong>IndexController</strong> (all controller names need to end with the word "Controller". Inside this class, we define a method (all methods, also called Actions, need to end with the word "Action") called <strong>indexAction</strong>. Since it is called index, it is also the first Action that gets called by this controller if no particular action is specified in the request. 
<br /><br />
If you now access the project folder from the browser, you should see the string being echoed. 

<h3>Conclusion</h3><hr />

In this tutorial we've seen how to install Phalcon and get started with your project. In the next one we will take it a bit further and see how we can leverage the Phalcon MVC framework and how to access information stored in the database.

<div style="text-align: right; font-size:smaller;">Article Submitted by: <a href="http://www.webomelette.com/">Danny</a></div></div>
    