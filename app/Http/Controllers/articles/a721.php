<link rel='stylesheet' href='digital_ocean.css'><br><img src=https://community-cdn-digitalocean-com.global.ssl.fastly.net/assets/tutorials/images/large/symfony2_image.png?1445955194/> <br> 
      <h3 id="introduction">Introduction</h3>

<p>Symfony is a full-stack, open source PHP framework. It’s well known for its independent components which can be easily integrated into any other PHP project. The Symfony framework is suitable for building PHP applications of any size, including console applications meant to run only on the command line.</p>

<p>In this tutorial, we will see how to install, configure, and get started with a Symfony 2 application on Ubuntu 14.04.</p>

<p><span class="note">This is a <strong>development</strong> setup, intended to make you familiar with Symfony and get you started writing your first Symfony project. We'll walk you through the setup and installation of a brand new Symfony project.<br /></span></p>

<h2 id="prerequisites">Prerequisites</h2>

<p>For this tutorial, you will need:</p>

<ul>
<li>One Ubuntu 14.04 server</li>
<li>A sudo non-root user, which you can set up by following the <a href="https://indiareads/community/tutorials/initial-server-setup-with-ubuntu-14-04">Initial Server Setup tutorial</a></li>
</ul>

<h2 id="step-1-—-installing-php">Step 1 — Installing PHP</h2>

<p>Before we get started, we'll need to install PHP for the command line environment. There's no need to install a full-featured web server such as Apache or Nginx because Symfony comes with a console command that makes it trivial to run and manage PHP's built-in web server while you are developing your application. It´s a simple and efficient way to run the application while it's in development mode.</p>

<p>First, let's update the package manager cache:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo apt-get update
</li></ul></code></pre>
<p>Now, in order to use and execute PHP scripts via the command line, install the <code>php5-cli</code> package.</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo apt-get install php5-cli
</li></ul></code></pre>
<p>You should now have PHP installed on your server. To check if it was successfully installed, run:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">php -v
</li></ul></code></pre>
<p>And you should get output similar to this:</p>
<pre class="code-pre "><code langs=""><div class="secondary-code-label " title="Output">Output</div>PHP 5.5.9-1ubuntu4.11 (cli) (built: Jul 2 2015 15:23:08)
Copyright (c) 1997-2014 The PHP Group
Zend Engine v2.5.0, Copyright (c) 1998-2014 Zend Technologies
with Zend OPcache v7.0.3, Copyright (c) 1999-2014, by Zend Technologies
</code></pre>
<h2 id="step-2-—-configuring-date-timezone-in-php-ini">Step 2 — Configuring date.timezone in php.ini</h2>

<p>Symfony requires that the option <code>date.timezone</code> is set in your <code>php.ini</code> file(s). If you are testing this tutorial on a fresh server, this option is not defined yet. If that's the case, your Symfony application won't run.</p>

<p>We'll need to edit the server's <code>php.ini</code> file to make sure we have this option defined. This file should be located at <code>/etc/php5/cli/php.ini</code>.</p>

<p>Open the <code>php.ini</code> file using nano or your favorite command line editor:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo nano /etc/php5/cli/php.ini
</li></ul></code></pre>
<p>Search for the line containing <code>date.timezone</code>. Uncomment the directive by removing the <code>;</code> sign in the beginning of the line, and add the appropriate timezone for your application. In this example we'll use <code>Europe/Amsterdam</code>, but you can choose any <a href="http://php.net/manual/en/timezones.php">supported timezone</a>.</p>
<div class="code-label " title="Modified php.ini">Modified php.ini</div><pre class="code-pre "><code langs="">[Date]
; Defines the default timezone used by the date functions
; http://php.net/date.timezone
date.timezone = <span class="highlight">Europe/Amsterdam</span>
</code></pre>
<p>Save the file and exit.</p>

<h2 id="step-3-—-getting-the-symfony-installer">Step 3 — Getting the Symfony Installer</h2>

<p>The easiest way to create a new Symfony project is by using the official Symfony Installer. It's a simple script created to facilitate the bootstrap of new Symfony applications.</p>

<p>The following command will download the Symfony Installer and place it on your <code>/usr/local/bin</code> path:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo curl -LsS http://symfony.com/installer -o /usr/local/bin/symfony
</li></ul></code></pre>
<p>Now, you'll need to make the script executable with the next command:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo chmod a+x /usr/local/bin/symfony
</li></ul></code></pre>
<p>To test the Symfony Installer, run:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">symfony
</li></ul></code></pre>
<p>The output should look like this:</p>
<pre class="code-pre "><code langs=""><div class="secondary-code-label " title="Output">Output</div>
 Symfony Installer (1.1.7)
 =========================

 This is the official installer to start new projects based on the
 Symfony full-stack framework.

 To create a new project called blog in the current directory using
 the latest stable version of Symfony, execute the following command:

   symfony new blog

. . .
</code></pre>
<h2 id="step-4-—-creating-a-new-symfony-project">Step 4 — Creating a new Symfony Project</h2>

<p>Now that we have the Symfony Installer in place, we can proceed and create a new Symfony project. Because this is a development setup and we will be using PHP's built-in web server, you can go ahead an create the project inside your home directory. For this example, we'll create a project named "myproject", but you can use your own project name in the next command.</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">cd ~
</li><li class="line" prefix="$">symfony new <span class="highlight">myproject</span>
</li></ul></code></pre>
<p>This will create a new folder <code>myproject</code> inside your home directory, containing a brand new Symfony application. The command will produce output similar to this:</p>
<pre class="code-pre "><code langs=""><div class="secondary-code-label " title="Output">Output</div>
 Downloading Symfony...

 Preparing project...

 ✔  Symfony 2.7.3 was successfully installed. Now you can:

    * Change your current directory to /home/sammy/myproject

    * Configure your application in app/config/parameters.yml file.

    * Run your application:
        1. Execute the php app/console server:run command.
        2. Browse to the http://localhost:8000 URL.

    * Read the documentation at http://symfony.com/doc

</code></pre>
<h2 id="step-5-—-running-the-application-with-the-symfony-console">Step 5 — Running the Application with the Symfony Console</h2>

<p>The built-in web server that comes with PHP (since PHP 5.4) is suited for running PHP applications while in development, for testing, or for demonstrations. It enables a more frictionless experience because you won't need to bother configuring a full-featured web server like Apache or Nginx.</p>

<p>Symfony comes with a console command that facilitates the process of starting / stopping PHP's built-in web server, also allowing (since Symfony 2.6) you to run the web server in the background. </p>

<p>The Symfony console is a CLI script that has several commands to help you build and test your application. You can even <a href="http://symfony.com/doc/current/cookbook/console/console_command.html">include your own commands</a> written in PHP!</p>

<p>The web server can be initiated anytime with the console command <code>server:run</code>. However, the default settings used by the Symfony command will only accept connections to <code>localhost</code> on port <code>8000</code>. If you are following this tutorial on an external development / testing server or a local virtual machine, you'll need to provide an extra parameter to the command, telling the web server to listen to a different IP address.</p>

<p>To allow connections coming from both internal and external networks, run this command from inside the project directory:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">php app/console server:run 0.0.0.0:8000
</li></ul></code></pre><pre class="code-pre "><code langs=""><div class="secondary-code-label " title="Output">Output</div>Server running on http://0.0.0.0:8000

Quit the server with CONTROL-C.
</code></pre>
<p>This will start PHP's built-in web server, listening to all network interfaces on port <code>8000</code>. </p>

<p>Now you should be able to access the application if you point your browser to <code>http://<span class="highlight">your_server_ip</span>:8000</code>. You should see a page like this:</p>

<p><img src="https://assets.digitalocean.com/articles/symfony_1404/preview.png" alt="Symfony App Preview" /></p>

<p>The command will keep running in the active terminal until you terminate its execution with <code>CTRL+C</code>. To make the web server run in the background, you should use the <code>server:start</code> command instead:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">php app/console server:start 0.0.0.0:8000
</li></ul></code></pre><pre class="code-pre "><code langs=""><div class="secondary-code-label " title="Output">Output</div>Web server listening on http://0.0.0.0:8000
</code></pre>
<p>This will make the web server run in the background, leaving your terminal session free for executing other commands. To stop the server, you should use:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">php app/console server:stop 0.0.0.0:8000
</li></ul></code></pre><pre class="code-pre "><code langs=""><div class="secondary-code-label " title="Output">Output</div>Stopped the web server listening on http://0.0.0.0:8000
</code></pre>
<p>You can also check the status of the web server with:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">php app/console server:status 0.0.0.0:8000
</li></ul></code></pre>
<p>When the server is not running, this is the output you should get:</p>
<pre class="code-pre "><code langs=""><div class="secondary-code-label " title="Output">Output</div>No web server is listening on http://0.0.0.0:8000
</code></pre>
<p>When there's an active server running in the specified IP and port, you should get output like this:</p>
<pre class="code-pre "><code langs=""><div class="secondary-code-label " title="Output">Output</div>Web server still listening on http://0.0.0.0:8000
</code></pre>
<p>Just remember that the <code>server:stop</code> and <code>server:status</code> commands should include the same <code>IPADDRESS:PORT</code> portion you used when initiating the server with <code>server:start</code>.</p>

<h2 id="conclusion">Conclusion</h2>

<p>Symfony is a full-stack PHP framework suited for building applications of any size. In this tutorial, we saw how to install and get started with Symfony 2 on a fresh Ubuntu 14.04 server using PHP's built-in web server to run the application. For more information on how to build PHP applications using Symfony, have a look at their <a href="http://symfony.com/doc/2.7/book/page_creation.html">official documentation</a>.</p>

    