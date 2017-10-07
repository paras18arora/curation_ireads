<link rel='stylesheet' href='digital_ocean.css'><br> <br> 
      <h3 id="introduction">Introduction</h3>

<hr />

<p>Sinatra is a concise framework that does not like to show off. It gets the job done, without forcing anything on the developer. When it comes to taking your application, developed using this wonderful little tool, the same logic applies and you are welcomed with multiple choices. Each working pretty much the same way: getting the job done  <em>without unnecessary complexities</em>.</p>

<p>In this IndiaReads article, following our first <a href="https://indiareads/community/articles/how-to-install-ruby-2-1-0-and-sinatra-on-ubuntu-13-with-rvm">Ruby 2.1 & Sinatra On Ubuntu 13</a> tutorial, we will learn a couple of different ways to take your Sinatra application out of its hidden box and share it with the world [on Ubuntu] using different (and interesting) technologies and methods.</p>

<h2 id="glossary">Glossary</h2>

<hr />

<h3 id="1-application-deployment">1. Application Deployment</h3>

<hr />

<h3 id="2-application-servers">2. Application Servers</h3>

<hr />

<ol>
<li>Rack Middleware</li>
<li>Phusion Passenger Application Server</li>
<li>Unicorn Application Server</li>
</ol>

<h3 id="3-http-www-servers">3. HTTP / WWW Servers</h3>

<hr />

<ol>
<li>Apache HTTP Server</li>
<li>Nginx HTTP Server Running As A Front-End Reverse-Proxy</li>
</ol>

<h3 id="4-installations">4. Installations</h3>

<hr />

<ol>
<li>Apache And Passenger Combination</li>
<li>Nginx And Unicorn Combination</li>
</ol>

<p><strong>Note:</strong> This article's examples build on our first Sinatra & Ruby 2.1.0 on Ubuntu 13 article by convention. If you would like to learn more about getting started with the framework or how to prepare the operating system with Ruby 2.1.0 and Sinatra, consider checking it out before continuing with this piece.</p>

<h2 id="application-deployment">Application Deployment</h2>

<hr />

<p>Deploying an application (regardless of it being a web site, an API or a server) usually means setting up a system from scratch (or from a snapshot taken in time), preparing it by updating everything, downloading dependencies, setting up the file structure and permissions followed by finally uploading your codebase or downloading it using a source control manager (SCM) such as <em>Git</em>.</p>

<p>Following the design incentives of Sinatra, we are going to try to keep things as simple and possible and go with tried, tested, and easy to use methods for our deployment examples here. We will be working with reputable and trusted tools to handle the job and learn about their differences.</p>

<h2 id="application-servers">Application Servers</h2>

<hr />

<p>The term "application server" applies to applications (i.e. servers), which (usually) contains another application (e.g. your web-application) and following certain specifications and interfaces (i.e. a common language), allows the contained application to communicate with the outside world.</p>

<p>These tools are, again, usually geared towards handling the <em>business logic</em> (performing procedures) alone and not for other HTTP / WWW operations such as sending or receiving static file assets, dealing with multiple clients, or contending with long standing connections -- although, thanks to many readily available libraries, there are such application servers available as well.</p>

<p>In most set-ups, one or more application servers (e.g. Passenger, Unicorn, Puma, Thin etc.) are placed behind a proper HTTP / WWW server (e.g. Nginx, Apache etc.), tasked with handling and dealing with all incoming connections first, before passing it to the next level. This allows serving of assets (e.g. javascript files, images etc.) to be extremely efficient, meanwhile leveraging the capabilities of both applications to their maximum and keeping clients on-the-line (i.e. not dropping connections) and processing requests within the application layer.</p>

<p><strong>Note:</strong> To learn about different Ruby web-application servers and understand what <em>Rack</em> is, check out our article <a href="https://indiareads/community/articles/a-comparison-of-rack-web-servers-for-ruby-web-applications">A Comparison of (Rack) Web Servers for Ruby Web Applications</a>.</p>

<h3 id="rack-middleware">Rack Middleware</h3>

<hr />

<p>Rack <em><em>middleware</em></em>, implementing the <em><em>Rack specification</em></em>, works by dividing incoming HTTP requests into different pipelined stages and handles them in pieces until it sends back a response coming from your web application (controller). It has two distinct components, a <em>Handler</em> and an <em>Adapter</em>, used for communicating with web servers and applications (frameworks) respectively.</p>

<p>In regards to Ruby based frameworks, web-application servers do their job by implementing the Rack specification / interface and connecting to your application through <code>config.ru</code> file, calling (and importing) your application as an object.</p>

<h3 id="phusion-passenger-application-server">Phusion Passenger Application Server</h3>

<hr />

<p>Passenger is a mature, feature rich product which aims to cover necessary needs and areas of application deployment whilst greatly simplifying the set-up and getting-started procedures. It eliminates the traditional <em>middleman</em> architecture by directly integrating with the Nginx (and Apache) reverse-proxy.</p>

<p>This highly popular tool can be used widely in many production scenarios. The open-source version of Passenger (which is what we will be using) has a multi-process single-threaded operation mode. Its Enterprise version can be configured to work either single-threaded or multi-threaded, depending on your needs.</p>

<p>To learn more about Passenger, you can visit its official website located at <a href="https://www.phusionpassenger.com/">https://www.phusionpassenger.com/</a>.</p>

<h3 id="unicorn-application-server">Unicorn Application Server</h3>

<hr />

<p>Unicorn is a very mature web application server for Ruby/Rack based web applications. It is fully-featured; however, it denies by design trying to do everything: Unicorn's principal is doing what needs to be done by a web application server and delegating the rest of the responsibilities (e.g. the operating system).</p>

<p>Unicorn's master process spawns workers, as per your requirements, to serve the requests. This process also monitors the workers in order to prevent memory and process related staggering issues. What this means for system administrators is that it will kill a process if (for example) it takes too much time to complete a task or memory issues occur.</p>

<p>As mentioned above, one of the areas in which Unicorn delegates tasks is using the operating system for load balancing. This allows the requests <em>not</em> to pile up against busy workers spawned.</p>

<h2 id="http-www-servers">HTTP / WWW Servers</h2>

<hr />

<h3 id="apache-http-server">Apache HTTP Server</h3>

<hr />

<p>Apache is an HTTP server that does not really need any introduction at this point. It has been world's most popular HTTP server and it is an extremely mature, feature rich and highly configurable product that runs and works extremely well. In this article, one of the front-facing servers we will work with and use is Apache and we will see how it integrates with the Phusion Passenger application server.</p>

<h3 id="nginx-http-server-running-as-a-front-end-reverse-proxy">Nginx HTTP Server Running As A Front-End Reverse-Proxy</h3>

<hr />

<p>Nginx is designed from ground up to act as a multi-purpose HTTP server. It is capable of serving static files (e.g. images, text files etc.) extremely well, balance connections and deal with certain exploits attempts. It acts as the first entry point of all requests, and passes them to Passenger for the web application to process and return a response.</p>

<p>It is a very high performant <em>web server</em> / <em>(reverse)-proxy</em> that is relatively easy to work with and easy to extend (with add-ons and plug-ins). Thanks to its architecture, Nginx is capable of handling <em>a lot</em> of requests (virtually unlimited), which - depending on your application or website load - could be really hard to tackle using some other, older alternatives.</p>

<p><strong>Remember:</strong> "Handling" connections technically means not dropping them and being able to serve them with something. You still need your application [server] and database functioning well in order to have Nginx serve clients' responses that are not error messages.</p>

<p>To learn more about Nginx, you can visit its official website located at <a href="http://nginx.com">nginx.com</a>.</p>

<h2 id="installations">Installations</h2>

<hr />

<p>After deciding on which server combination you would like to work with, the next step consists of actually getting them installed and ready on the droplet you want your application to run.</p>

<h3 id="apache-and-passenger-combination">Apache And Passenger Combination</h3>

<hr />

<p><strong>Note:</strong> Before installing Apache and Passenger, make sure to disable (or remove) Nginx if you have it installed; or configure it to not to block Apache's way (i.e. port / socket collisions).</p>

<p><strong>Note:</strong> If your droplet has less than 1 GB of RAM, you will need to perform the below simple procedure to prepare a SWAP disk space to be used as a temporary data holder (i.e. RAM substitute) <strong>for Passenger</strong>. Since IndiaReads virtual servers come with fast SSD disks, this does not really constitute an issue whilst performing the server application installation tasks.</p>
<pre class="code-pre "><code langs=""># Create a 1024 MB SWAP space
# The process should complete within less than a minute

sudo dd if=/dev/zero of=/swap bs=1M count=1024
sudo mkswap /swap
sudo swapon /swap
</code></pre>
<p>We will begin getting the Apache and Passenger combination with first preparing the system with the tools that Passenger requires.</p>

<p>These tools consist of the following:</p>

<ol>
<li><p>Curl development headers with SSL support</p></li>
<li><p>Apache 2</p></li>
<li><p>Apache 2 development headers</p></li>
<li><p>Apache Portable Runtime (APR) development headers</p></li>
<li><p>Apache Portable Runtime Utility (APU) development headers</p></li>
</ol>

<p>Let's get them one by one, following the official Passenger documentations' suggested ways:</p>
<pre class="code-pre "><code langs=""># Install cURL development headers with SSL support:
apt-get install libcurl4-openssl-dev

# Apache 2:
apt-get install apache2-mpm-worker

# Apache 2 development headers:
apt-get install apache2-threaded-dev

# And finally, Apache PRU (APU) development headers:
apt-get install libapr1-dev
</code></pre>
<p>Next, let's download and install Passenger using RubyGems' <code>gem</code>:</p>
<pre class="code-pre "><code langs="">gem install passenger

# This method of installing Passenger will use
# the available and activated Ruby interpreter.
# However, the installation will be available for
# all Ruby versions' use.
</code></pre>
<p>Once we have all the necessary elements ready, we can get the glue layer (i.e. module) that will allow Apache and Passenger to couple and work together.</p>

<p>Begin the installation procedure by running the following command:</p>
<pre class="code-pre "><code langs="">passenger-install-apache2-module

# Here's what you can expect from the installation process:

# 1. The Apache 2 module will be installed for you.
# 2. You'll learn how to configure Apache.
# 3. You'll learn how to deploy a Ruby on Rails application.

# ..
</code></pre>
<p>Press enter to continue. </p>

<p>Now the installer will ask you to choose which programming languages you will be working with. Scroll down with your arrow keys and use the space bar to choose what's appropriate for your use. Since we are aiming to deploy Sinatra, our selection will cover Ruby alone.</p>
<pre class="code-pre "><code langs="">Which languages are you interested in?

Use <space> to select.
If the menu doesn't display correctly, ensure that your terminal supports UTF-8.

 ‣ ⬢  Ruby
   ⬡  Python
   ⬡  Node.js
   ⬡  Meteor
</code></pre>
<p>Once you have made your choice, press enter to advance to the next step.</p>

<p>Now the installer will start compiling the Apache module.</p>

<p><strong>Note:</strong> This bit might take a short while -- usually a couple of minutes.</p>

<h3 id="nginx-and-unicorn-combination">Nginx And Unicorn Combination</h3>

<hr />

<p><strong>Note:</strong> Before installing Nginx with Unicorn, make sure to disable (or remove) Apache, or configure it to not to block Nginx (i.e. port / socket collisions).</p>

<p><strong>Note:</strong> If you are constrained with system resources (e.g. amount of RAM you have available), you might want to choose this combination to deploy your application.</p>

<p>Nginx and Unicorn together make a great combination for web-application deployments. Unicorn, as we have discussed previously, is an amazing server that works in smart ways and make user of all available system tools to handle the job -- <em>and it does it well!</em></p>

<p>We will start the installation process by getting Nginx first. The default system package manager <code>aptitude</code> (or <code>apt-get</code>) will be able to download and install Nginx for us.</p>

<p>Run the following to download and install Nginx using <code>aptitude</code>:</p>
<pre class="code-pre "><code langs="">aptitude install nginx
</code></pre>
<p>Once we have Nginx ready, the next step consists of getting Unicorn web-application server, which is made easy by RubyGems package manager.</p>

<p>Run the following to download and install Unicorn using <code>gem</code>:</p>
<pre class="code-pre "><code langs="">gem install unicorn 
</code></pre>
<h2 id="configuration">Configuration</h2>

<hr />

<p>In this section, we are going to see how to configure <strong>both</strong> server combinations to get our applications online. You should remember that despite the marketing efforts, you will be able to deploy any number of web-applications as long as you configure them properly and have enough system resources to support them.</p>

<p><strong>Note:</strong> Remember that you should choose one-or-the-other from above web-application / HTTP server combinations before continuing with (and applying) the below settings. </p>

<h3 id="apache-amp-passenger-combination">Apache & Passenger Combination</h3>

<hr />

<p>After finishing installing Apache, Passenger and Passenger's Apache module, you will be shown a message titled Almost there!</p>

<blockquote>
<p>Please edit your Apache configuration file and add these lines:</p>

<p>LoadModule passenger<em>module /usr/local/rvm/gems/ruby-2.1.0/gems/passenger-4.0.37/buildout/apache2/mod</em>passenger.so<br />
   <ifmodule mod_passenger><br />
     PassengerRoot /usr/local/rvm/gems/ruby-2.1.0/gems/passenger-4.0.37<br />
     PassengerDefaultRuby /usr/local/rvm/gems/ruby-2.1.0/wrappers/ruby<br />
   </ifmodule></p>

<p>After you restart Apache, you are ready to deploy any number of web<br />
applications on Apache, with a minimum amount of configuration!</p>
</blockquote>

<p>So, as suggested and advised, let's add the configuration block to our Apache configuration file.</p>

<p>Run the following to edit the Apache configuration file using nano text editor:</p>
<pre class="code-pre "><code langs="">nano /etc/apache2/apache2.conf
</code></pre>
<p>Add the below block of text to where you see fit, without affecting other configurations:</p>
<pre class="code-pre "><code langs="">LoadModule passenger_module /usr/local/rvm/gems/ruby-2.1.0/gems/passenger-4.0.37/buildout/apache2/mod_passenger.so
<IfModule mod_passenger.c>
    PassengerRoot /usr/local/rvm/gems/ruby-2.1.0/gems/passenger-4.0.37
    PassengerDefaultRuby /usr/local/rvm/gems/ruby-2.1.0/wrappers/ruby
</IfModule>
</code></pre>
<p>Save and exit by pressing CTRL+X and confirming with Y.</p>

<p><strong>Note:</strong> The above configuration must be kept as is. Its parameters are defined in a way to use the relevant Ruby version (<code>2.1.0</code> installed during the first Sinatra tutorial) at the relevant location (<code>/usr/local/rvm/gems/ruby-2.1.0/..</code>).</p>

<p>Next, we need to define <em>an application</em> as suggested by the installer:</p>

<blockquote>
<p>Deploying a web application: an example</p>

<p>Suppose you have a web application in /somewhere. Add a virtual host to your<br />
Apache configuration file and set its DocumentRoot to /somewhere/public:</p>

<p><virtualhost><br />
      ServerName www.yourhost.com<br />
      # !!! Be sure to point DocumentRoot to 'public'!<br />
      DocumentRoot /somewhere/public<br /><br />
      <directory><br />
         # This relaxes Apache security settings.<br />
         AllowOverride all<br />
         # MultiViews must be turned off.<br />
         Options -MultiViews<br />
      </directory><br />
   </virtualhost></p>
</blockquote>

<p>You can deploy yours anywhere you like; however, what can be considered a good example is using either:</p>

<ul>
<li><p>A sub-directory of a user, set for deployments, or;</p></li>
<li><p>Using the general <code>/var/www</code> location, as we have done in the past.</p></li>
</ul>

<p><strong>Note:</strong> For this next step of configurations, we will use our sample application from the previous Sinatra/Ruby 2.1.0 tutorial.</p>

<p>To let Apache know about our application and get Passenger to run it, we need to define a virtual host. For this purpose, we will create a file inside the <code>/etc/apache2/sites-available</code> directory.</p>

<p>Let's create an empty configuration file called <code>my_app</code> using nano:</p>

<p><strong>Note:</strong> You can name this file as you see fit to match your application settings.</p>
<pre class="code-pre "><code langs="">nano /etc/apache2/sites-available/my_app.conf
</code></pre>
<p>Place the contents below, modifying them to suit your application deployment directory:</p>
<pre class="code-pre "><code langs=""><VirtualHost *:80>
    # ServerName [!! Your domain OR droplet's IP]
    ServerName 162.243.74.190 

    # !!! Be sure to point DocumentRoot to 'public'!
    # DocumentRoot [root to your app./public]
    DocumentRoot /var/www/my_app/public

    # Directory [root to your app./public]
    <Directory /var/www/my_app/public>
        # This relaxes Apache security settings.
        AllowOverride all
        # MultiViews must be turned off.
        Options -MultiViews
    </Directory>

</VirtualHost>
</code></pre>
<p>Save and exit by pressing CTRL+X and confirming with Y.</p>

<p>Now we can add our new site configuration to Apache:</p>
<pre class="code-pre "><code langs=""># Usage sudo a2ensite [configuration name without .conf extension]
sudo a2ensite my_app
</code></pre>
<p>And reload:</p>
<pre class="code-pre "><code langs="">service apache2 reload
service apache2 restart
</code></pre>
<p>Your web-application should now be alive and on-line. Visit <strong>your droplet's IP</strong> address or the domain you have redirected and defined under the <code>ServerName</code> configuration:</p>
<pre class="code-pre "><code langs="">http://162.243.74.190/

# Hello world!
</code></pre>
<p><strong>Note:</strong> If you have opted for working with a domain name, you should add it to your <code>/etc/hosts</code> file as well with the following command:</p>
<pre class="code-pre "><code langs="">nano /etc/hosts
</code></pre>
<p>And append your domain to the list:</p>
<pre class="code-pre "><code langs=""># 127.0.0.1 [domain.tld]
# 127.0.0.1 [www.domain.tld]

127.0.0.1 example.com
127.0.0.1 www.example.com
</code></pre>
<h3 id="nginx-and-unicorn-combination">Nginx And Unicorn Combination</h3>

<hr />

<p>Unicorn can be configured a number of ways. For this tutorial, focusing on the key elements, we will create a file from scratch which is going to be used by Unicorn when starting the application server daemon.</p>

<p>Open up a blank <code>unicorn.rb</code> document, which will be saved inside the application directory (<code>/var/www/my_app</code>) directory:</p>
<pre class="code-pre "><code langs="">nano unicorn.rb
</code></pre>
<p>Place the below block of code, modifying it as necessary:</p>
<pre class="code-pre "><code langs=""># Set the working application directory
# working_directory "/path/to/your/app"
working_directory "/var/www/my_app"

# Unicorn PID file location
# pid "/path/to/pids/unicorn.pid"
pid "/var/www/my_app/pids/unicorn.pid"

# Path to logs
# stderr_path "/path/to/logs/unicorn.log"
# stdout_path "/path/to/logs/unicorn.log"
stderr_path "/var/www/my_app/logs/unicorn.log"
stdout_path "/var/www/my_app/logs/unicorn.log"

# Unicorn socket
# listen "/tmp/unicorn.[app name].sock"
listen "/tmp/unicorn.myapp.sock"

# Number of processes
# worker_processes 4
worker_processes 2

# Time-out
timeout 30
</code></pre>
<p>Save and exit by pressing CTRL+X and confirming with Y.</p>

<p><strong>Note:</strong> To simply test your application with Unicorn, you can run <code>unicorn</code> inside the application directory.</p>

<p>Next, we need to tell Nginx how to talk to Unicorn. For this purpose, it is sufficient at this level to edit the default configuration file: <code>default.conf</code> and leave <code>nginx.conf</code> as provided -- which is already set to include the default configurations.</p>
<pre class="code-pre "><code langs=""># Remove the default configuration file
rm -v /etc/nginx/sites-available/default

# Create a new, blank configuration
nano /etc/nginx/conf.d/default.conf
</code></pre>
<p>Replace the files contents with the ones from below, again amending the necessary bits to suit your needs:</p>
<pre class="code-pre "><code langs="">upstream app {
    # Path to Unicorn SOCK file, as defined previously
    server unix:/tmp/unicorn.myapp.sock fail_timeout=0;
}

server {


    listen 80;

    # Set the server name, similar to Apache's settings
    server_name localhost;

    # Application root, as defined previously
    root /var/www/my_app/public;

    try_files $uri/index.html $uri @app;

    location @app {
        proxy_set_header X-Forwarded-For $proxy_add_x_forwarded_for;
        proxy_set_header Host $http_host;
        proxy_redirect off;
        proxy_pass http://app;
    }

    error_page 500 502 503 504 /500.html;
    client_max_body_size 4G;
    keepalive_timeout 10;

}  
</code></pre>
<p>Save and exit by pressing CTRL+X and confirming with Y.</p>

<p><strong>Note:</strong> To learn more about Nginx, you can refer to <a href="https://indiareads/community/articles/how-to-configure-the-nginx-web-server-on-a-virtual-private-server">How to Configure Nginx Web Server on a VPS</a>.</p>

<p>Let's start the Unicorn and run it as a daemon using the configuration file:</p>
<pre class="code-pre "><code langs=""># Make sure that you are inside the application directory
# i.e. /my_app
unicorn -c unicorn.rb -D
</code></pre>
<p>Next, we are ready to reload and restart Nginx:</p>
<pre class="code-pre "><code langs="">service nginx restart
</code></pre>
<p>And that's it! You can now check out your deployment by going to your droplet's IP address (or the domain name associated to it).</p>
<pre class="code-pre "><code langs="">http://162.243.74.190/

# Hello world!
</code></pre>
<h2 id="further-reading">Further Reading</h2>

<hr />

<h3 id="firewall">Firewall:</h3>

<hr />

<p><a href="https://indiareads/community/articles/how-to-set-up-a-firewall-using-ip-tables-on-ubuntu-12-04">Setting up a firewall using IP Tables</a></p>

<h3 id="securing-ssh">Securing SSH:</h3>

<hr />

<p><a href="https://indiareads/community/articles/how-to-protect-ssh-with-fail2ban-on-ubuntu-12-04">How To Protect SSH with fail2ban on Ubuntu</a><br />
<a href="https://indiareads/community/articles/how-to-protect-ssh-with-fail2ban-on-centos-6">How To Protect SSH with fail2ban on CentOS 6</a></p>

<h3 id="creating-alerts">Creating Alerts:</h3>

<hr />

<p><a href="https://indiareads/community/articles/how-to-send-e-mail-alerts-on-a-centos-vps-for-system-monitoring">How To Send E-Mail Alerts on a CentOS VPS for System Monitoring</a></p>

<h3 id="monitor-and-watch-server-access-logs-daily">Monitor and Watch Server Access Logs Daily:</h3>

<hr />

<p><a href="https://indiareads/community/articles/how-to-install-and-use-logwatch-log-analyzer-and-reporter-on-a-vps">How To Install and Use Logwatch Log Analyser and Reporter</a></p>

<h3 id="optimising-unicorn-workers">Optimising Unicorn Workers:</h3>

<hr />

<p><a href="https://indiareads/community/articles/how-to-optimize-unicorn-workers-in-a-ruby-on-rails-app">How to Optimize Unicorn Workers in a Ruby on Rails App</a></p>

<div class="author">Submitted by: <a href="https://twitter.com/ostezer">O.S. Tezer</a></div>

    