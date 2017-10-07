<link rel='stylesheet' href='digital_ocean.css'><br><img src=https://community-cdn-digitalocean-com.global.ssl.fastly.net/assets/tutorials/images/large/DockerCompose-twitter.png?1452614793/> <br> 
      <h3 id="introduction">Introduction</h3>

<p><a href="https://indiareads/community/tutorials/how-to-install-and-use-docker-compose-on-ubuntu-14-04">Docker Compose</a> makes dealing with the orchestration processes of Docker containers (such as starting up, shutting down, and setting up intra-container linking and volumes) really easy. </p>

<p>This article provides a real-world example of using Docker Compose to install an application, in this case WordPress with PHPMyAdmin as an extra.  WordPress normally runs on a LAMP stack, which means Linux, Apache, MySQL/MariaDB, and PHP. The official WordPress Docker image includes Apache and PHP for us, so the only part we have to worry about is MariaDB.</p>

<h2 id="prerequisites">Prerequisites</h2>

<p>To follow this article, you will need the following:</p>

<ul>
<li>Ubuntu 14.04 Droplet</li>
<li>A non-root user with sudo privileges (<a href="https://indiareads/community/tutorials/initial-server-setup-with-ubuntu-14-04">Initial Server Setup with Ubuntu 14.04</a> explains how to set this up.)</li>
<li>Docker and Docker Compose installed from the instructions in <a href="https://indiareads/community/tutorials/how-to-install-and-use-docker-compose-on-ubuntu-14-04">How To Install and Use Docker Compose on Ubuntu 14.04</a></li>
</ul>

<h2 id="step-1-—-installing-wordpress">Step 1 — Installing WordPress</h2>

<p>We'll be using the official <a href="https://hub.docker.com/_/wordpress/">WordPress</a> and <a href="https://hub.docker.com/_/mariadb/">MariaDB</a> Docker images. If you're curious, there's lots more info about these images and their configuration options on their respective GitHub and Docker Hub pages.</p>

<p>Let's start by making a folder where our data will live and creating a minimal <code>docker-compose.yml</code> file to run our WordPress container:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">mkdir ~/wordpress && cd $_
</li></ul></code></pre>
<p>Then create a  <code>~/wordpress/docker-compose.yml</code> with your favorite text editor (nano is easy if you don't have a preference):</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">nano ~/wordpress/docker-compose.yml
</li></ul></code></pre>
<p>and paste in the following:</p>
<div class="code-label " title="~/wordpress/docker-compose.yml">~/wordpress/docker-compose.yml</div><pre class="code-pre "><code langs="">wordpress:
  image: wordpress
</code></pre>
<p>This just tells Docker Compose to start a new container called <code>wordpress</code> and  download the <code>wordpress</code> image from the Docker Hub. </p>

<p>We can bring the image up like so:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">docker-compose up
</li></ul></code></pre>
<p>You'll see Docker download and extract the WordPress image from the Docker Hub, and after some time you'll get some error messages similar to the below:</p>
<pre class="code-pre "><code langs=""><div class="secondary-code-label " title="Output">Output</div>wordpress_1 | error: missing WORDPRESS_DB_HOST and MYSQL_PORT_3306_TCP environment variables
wordpress_1 |   Did you forget to --link some_mysql_container:mysql or set an external db
wordpress_1 |   with -e WORDPRESS_DB_HOST=hostname:port?
dockercompose_wordpress_1 exited with code 1
</code></pre>
<p>This is WordPress complaining that it can't find a database. Let's add a MariaDB image to the mix and link it up to fix that.</p>

<h2 id="step-2-—-installing-mariadb">Step 2 — Installing MariaDB</h2>

<p>To add the MariaDB image to the group, re-open <code>docker-compose.yml</code> with your text editor:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">nano ~/wordpress/docker-compose.yml
</li></ul></code></pre>
<p>Change <code>docker-compose.yml</code> to match the below (be careful with the indentation, YAML files are white-space sensitive)</p>
<div class="code-label " title="docker-compose.yml">docker-compose.yml</div><pre class="code-pre "><code langs="">wordpress:
  image: wordpress
  <span class="highlight">links:</span>
    <span class="highlight">- wordpress_db:mysql</span>
<span class="highlight">wordpress_db:</span>
  <span class="highlight">image: mariadb</span>
</code></pre>
<p>What we've done here is define a new container called <code>wordpress_db</code> and told it to use the <code>mariadb</code> image from the Docker Hub. We also told the our <code>wordpress</code> container to  link our <code>wordpress_db</code> container into the <code>wordpress</code> container and call it <code>mysql</code> (inside the <code>wordpress</code> container the hostname <code>mysql</code> will be forwarded to our <code>wordpress_db</code> container). </p>

<p>If you run <code>docker-compose up</code> again, you will see it download the MariaDB image, and you'll also see that we're not quite there yet though:</p>
<pre class="code-pre "><code langs=""><div class="secondary-code-label " title="Output">Output</div>wordpress_db_1 | error: database is uninitialized and MYSQL_ROOT_PASSWORD not set
wordpress_db_1 |   Did you forget to add -e MYSQL_ROOT_PASSWORD=... ?
wordpress_1    | error: missing required WORDPRESS_DB_PASSWORD environment variable
wordpress_1    |   Did you forget to -e WORDPRESS_DB_PASSWORD=... ?
wordpress_1    | 
wordpress_1    |   (Also of interest might be WORDPRESS_DB_USER and WORDPRESS_DB_NAME.)
wordpress_wordpress_db_1 exited with code 1
wordpress_wordpress_1 exited with code 1
Gracefully stopping... (press Ctrl+C again to force)
</code></pre>
<p>WordPress is still complaining about being unable to find a database, and now we have a new complaint from MariaDB saying that no root password is set. </p>

<p>It appears that just linking the two containers isn't quite enough. Let's go ahead and set the <code>MYSQL_ROOT_PASSWORD</code> variable so that we can actually fire this thing up. </p>

<p>Edit the Docker Compose file yet again:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">nano ~/wordpress/docker-compose.yml
</li></ul></code></pre>
<p>Add these two lines to the <em>end</em> of the <code>wordpress_db</code> section, but <strong>make sure to change <span class="highlight"><code>examplepass</code></span> to a more secure password!</strong></p>
<div class="code-label " title="docker-compose.yml">docker-compose.yml</div><pre class="code-pre "><code langs="">wordpress_db:
...
  <span class="highlight">environment:</span>
    <span class="highlight">MYSQL_ROOT_PASSWORD: examplepass</span>
...
</code></pre>
<p>This will set an environment variable inside the <code>wordpress_db</code> container called <code>MYSQL_ROOT_PASSWORD</code> with your desired password. The MariaDB Docker image is configured to check for this environment variable when it starts up and will take care of setting up the DB with a root account with the password defined as <code>MYSQL_ROOT_PASSWORD</code>.</p>

<p>While we're at it, let's also set up a port forward so that we can connect to our WordPress  install once it actually loads up. Under the <code>wordpress</code> section add these two lines:</p>
<div class="code-label " title="docker-compose.yml">docker-compose.yml</div><pre class="code-pre "><code langs="">wordpress:
...
  <span class="highlight">ports:</span>
    <span class="highlight">- 8080:80</span>
...
</code></pre>
<p>The first port number is the port number on the host, and the second port number is the port inside the container. So, this configuration forwards requests on port 8080 of the host to the default web server port 80 inside the container.</p>

<p><span class="note"><strong>Note:</strong> If you would like Wordpress to run on the default web server port 80 on the host, change the previous line to <code>80:80</code> so that requests to port 80 on the host are forwarded to port 80 inside the Wordpress container.<br /></span></p>

<p>Your complete <code>docker-compose.yml</code> file should now look like this:</p>
<div class="code-label " title="docker-compose.yml">docker-compose.yml</div><pre class="code-pre "><code langs="">wordpress:
  image: wordpress
  links:
    - wordpress_db:mysql
  ports:
    - 8080:80
wordpress_db:
  image: mariadb
  environment:
    MYSQL_ROOT_PASSWORD: <span class="highlight">examplepass</span>
</code></pre>
<p>With this configuration we can actually go ahead and fire up WordPress. This time, let's run it with the <code>-d</code> option, which will tell <code>docker-compose</code> to run the containers in the background so that you can keep using your terminal:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">docker-compose up -d
</li></ul></code></pre>
<p>You'll see a whole bunch of text fly by your screen. Once it's calmed down,  open up a web browser and browse to the IP<br />
of your IndiaReads box on port 8080 (for example, if the IP address of your server is <span class="highlight">123.456.789.123</span> you should type <span class="highlight">http://123.456.789.123:8080</span> into your browser.)</p>

<p>You should see a fresh WordPress installation page and be able to complete the install and blog as usual. </p>

<p>Because these are both official Docker images and are following all of Docker's best practices, each of these images have pre-defined, persistent volumes for you — meaning that if you restart the container, your blog posts will still be there. You can learn more about working with Docker volumes in the <a href="https://indiareads/community/tutorials/how-to-work-with-docker-data-volumes-on-ubuntu-14-04">Docker data volumes tutorial</a>.</p>

<h2 id="step-3-—-adding-a-phpmyadmin-container">Step 3 — Adding a PhpMyAdmin Container</h2>

<p>Great, that was relatively painless. Let's try getting a little fancy. </p>

<p>So far we've only been using official images, which the Docker team takes great pains to ensure are accurate. You may have noticed that we didn't have to  give the WordPress container any environment variables to configure it. As soon as we linked it up to a properly configured MariaDB container everything just worked. </p>

<p>This is because there's a script inside the WordPress Docker container that actually grabs the <code>MYSQL_ROOT_PASSWORD</code> variable from our <code>wordpress_db</code> container and uses that to connect to WordPress. </p>

<p>Let's venture out of the official image area a little bit and use a <a href="https://hub.docker.com/r/corbinu/docker-phpmyadmin/">community contributed PhpMyAdmin image</a>. Go ahead and edit <code>docker-compose.yml</code> one more time:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">nano docker-compose.yml
</li></ul></code></pre>
<p>Paste the following at the end of the file:</p>
<div class="code-label " title="docker-compose.yml">docker-compose.yml</div><pre class="code-pre "><code langs="">phpmyadmin:
  image: corbinu/docker-phpmyadmin
  links:
    - wordpress_db:mysql
  ports:
    - 8181:80
  environment:
    MYSQL_USERNAME: root
    MYSQL_ROOT_PASSWORD: <span class="highlight">examplepass</span>
</code></pre>
<p>Be sure to replace <span class="highlight">examplepass</span> with the exact same root password from the <code>wordpress_db</code> container you setup earlier.</p>

<p>This grabs <code>docker-phpmyadmin</code> by community member <code>corbinu</code>, links it to our <code>wordpress_db</code> container with the name <code>mysql</code> (meaning from inside the <code>phpmyadmin</code> container references to the hostname <code>mysql</code> will be forwarded to our <code>wordpress_db</code> container), exposes its port 80 on port 8181 of the host system, and finally sets a couple of environment variables with our MariaDB username and password. This image does not automatically grab the <code>MYSQL_ROOT_PASSWORD</code> environment variable from the <code>wordpress_db</code> container's environment the way the <code>wordpress</code> image does. We actually have to copy the <code>MYSQL_ROOT_PASSWORD: <span class="highlight">examplepass</span></code> line from the <code>wordpress_db</code> container, and set the username to <code>root</code>.</p>

<p>The complete <code>docker-compose.yml</code> file should now look like this:</p>
<div class="code-label " title="docker-compose.yml">docker-compose.yml</div><pre class="code-pre "><code langs="">wordpress:
  image: wordpress
  links:
    - wordpress_db:mysql
  ports:
    - 8080:80
wordpress_db:
  image: mariadb
  environment:
    MYSQL_ROOT_PASSWORD: examplepass
<span class="highlight">phpmyadmin:</span>
  <span class="highlight">image: corbinu/docker-phpmyadmin</span>
  <span class="highlight">links:</span>
    <span class="highlight">- wordpress_db:mysql</span>
  <span class="highlight">ports:</span>
    <span class="highlight">- 8181:80</span>
  <span class="highlight">environment:</span>
    <span class="highlight">MYSQL_USERNAME: root</span>
    <span class="highlight">MYSQL_ROOT_PASSWORD: examplepass</span>
</code></pre>
<p>Now start up the application group again:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">docker-compose up -d
</li></ul></code></pre>
<p>You will see PhpMyAdmin being installed. Once it is finished, visit your server's IP address again (this time using port 8181, e.g. <span class="highlight">http://123.456.789.123:8181</span>). You'll be greeted by the PhpMyAdmin login screen.</p>

<p>Go ahead and login using username <code>root</code> and password you set in the YAML file, and you'll be able to browse your database. You'll notice that the server includes a <code>wordpress</code> database, which contains all the data from your WordPress install. </p>

<p>You can add as many containers as you like this way and link them all up in any way you please.  As you can see, the approach is quite powerful —instead of dealing with the configuration and prerequisites for each individual components and setting them all up on the same server, you get to plug the pieces together like Lego blocks and add components piecemeal. Using tools like <a href="https://docs.docker.com/swarm/install-w-machine/">Docker Swarm</a> you can even transparently run these containers over multiple servers! That's a bitoutside the scope of this tutorial though. Docker provides some [documentation]((https://docs.docker.com/swarm/install-w-machine/)) on it if you are interested. </p>

<h2 id="step-4-—-creating-the-wordpress-site">Step 4 — Creating the WordPress Site</h2>

<p>Since all the files for your new WordPress site are stored inside your Docker container, what happens to your files when you stop the container and start it again?</p>

<p>By default, the document root for the WordPress container is persistent. This is because the WordPress image from the Docker Hub is configured this way. If you make a change to your WordPress site, stop the application group, and start it again, your website will still have the changes you made.</p>

<p>Let's try it.</p>

<p>Go to your WordPress from a web browser (e.g. <span class="highlight">http://123.456.789.123:8080</span>). Edit the <strong>Hello World!</strong> post that already exists. Then, stop all the Docker containers with the following command:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">docker-compose stop
</li></ul></code></pre>
<p>Try loading the WordPress site again. You will see that the website is down. Start the Docker containers again:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">docker-compose up -d
</li></ul></code></pre>
<p>Again, load the WordPress site. You should see your blog site and the change you made earlier. This shows that the changes you make are saved even when the containers are stopped.</p>

<h2 id="step-5-—-storing-the-document-root-on-the-host-filesystem-optional">Step 5 — Storing the Document Root on the Host Filesystem (Optional)</h2>

<p>It is possible to store the document root for WordPress on the host filesystem using a Docker data volume to share files between the host and the container. </p>

<p><span class="note"><strong>Note:</strong> For more details on working with Docker data volumes, take a look at the <a href="https://indiareads/community/tutorials/how-to-work-with-docker-data-volumes-on-ubuntu-14-04">Docker data volumes tutorial</a>. <br /></span></p>

<p>Let's give it a try. Open up your <code>docker-compose.yml</code> file one more time:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">nano ~/wordpress/docker-compose.yml
</li></ul></code></pre>
<p>in the <code>wordpress:</code> section add the following lines:</p>
<div class="code-label " title="~/wordpress/docker-compose.yml">~/wordpress/docker-compose.yml</div><pre class="code-pre "><code langs="">wordpress:
...
  <span class="highlight">volumes:</span>
    <span class="highlight">- ~/wordpress/wp_html:/var/www/html</span>
    ...
</code></pre>
<p>Stop your currently running <code>docker-compose</code> session:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">docker-compose stop
</li></ul></code></pre>
<p>Remove the existing container so we can map the volume to the host filesystem:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">docker-compose rm wordpress
</li></ul></code></pre>
<p>Start WordPress again:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">docker-compose -d
</li></ul></code></pre>
<p>Once the prompt returns, WordPress should be up and running again — this time using the host filesystem to store the document root.</p>

<p>If you look in your <code>~/wordpress</code> directory, you'll see that there is now a <code>wp_html</code> directory in it:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">ls ~/wordpress
</li></ul></code></pre>
<p>All of the WordPress source files are inside it. Changes you make will be picked up by the WordPress container in real time. </p>

<p>This experience was a little smoother than it normally would be — the WordPress Docker container is configured to check if <code>/var/www/html</code> is empty or not when it starts and copies files there appropriately.  Usually you will have to do this step yourself.</p>

<h2 id="conclusion">Conclusion</h2>

<p>You should have a full WordPress deploy up and running. You should be able to use the same method to deploy quite a wide variety of systems using the images available on the Docker Hub. Be sure to figure out which volumes are persistent and which are not for each container you<br />
create.</p>

<p>Happy Dockering!</p>

    