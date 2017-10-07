<link rel='stylesheet' href='digital_ocean.css'><br><img src=https://community-cdn-digitalocean-com.global.ssl.fastly.net/assets/tutorials/images/large/gogs_tw.jpg?1431982401/> <br> 
      <h3 id="an-article-from-gogs">An Article from <a href="http://gogs.io">Gogs</a></h3>

<h2 id="introduction">Introduction</h2>

<p><a href="http://gogs.io">Gogs</a> is a self-hosted Git service written in Go which is very easy to get running and has low system usage as well. It aspires to be the easiest, fastest, and most painless way to set up a self-hosted Git service.</p>

<p>By the end of this tutorial, you will have a running instance of Gogs, which includes a web interface, an admin dashboard, and access to operations like Git pull and push.</p>

<h2 id="prerequisites">Prerequisites</h2>

<p>To follow this tutorial, you will need:</p>

<ul>
<li>An Ubuntu 14.04 Droplet of any size.</li>
<li>A <a href="https://indiareads/community/tutorials/how-to-add-and-delete-users-on-an-ubuntu-14-04-vps">sudo non-root user</a>. In this tutorial, we'll use a separate sudo non-root user only for Gogs for security concerns. This tutorial assumes this dedicated user is named <strong>git</strong>, following the convention of the Git service; this tutorial should be followed as the <strong>git</strong> user.</li>
</ul>

<h2 id="step-1-—-install-the-database">Step 1 — Install the Database</h2>

<p>In this step, we will create the back end Gogs database.</p>

<p>After you log in, make sure your system packages are up to date.</p>
<pre class="code-pre "><code langs="">sudo apt-get update
</code></pre>
<p>We're going to use MySQL as our back end server, so next, install it. The <code>-y</code> flag here assumes yes to all prompts.</p>
<pre class="code-pre "><code langs="">sudo apt-get -y install mysql-server
</code></pre>
<p>During the installation, you will be asked to enter the password of the database <strong>root</strong> user. Make sure you use a secure one, and remember it, because you'll need it later in this tutorial.</p>

<p>Now create and open a file named <code>gogs.sql</code>. Here, we're using <code>nano</code>, but you can use your favorite text editor.</p>
<pre class="code-pre "><code langs="">nano gogs.sql
</code></pre>
<p>Paste the following contents into the file, and save and close it.</p>
<pre class="code-pre "><code langs="">DROP DATABASE IF EXISTS gogs;
CREATE DATABASE IF NOT EXISTS gogs CHARACTER SET utf8 COLLATE utf8_general_ci;
</code></pre>
<p>Finally, execute <code>gogs.sql</code> with MySQL to create the Gogs database. Replace <code><span class="highlight">your_password</span></code> with the root password you chose earlier in this step.</p>

<p><strong>Note</strong>: there is no space between the <code>-p</code> flag and your password in this command.</p>
<pre class="code-pre "><code langs="">mysql -u root -p<span class="highlight">your_password</span> < gogs.sql
</code></pre>
<blockquote>
</blockquote>

<p>To install Gogs from source, version control tools like Git and Mercurial are needed, so install them next.</p>
<pre class="code-pre "><code langs="">sudo apt-get -y install mercurial git
</code></pre>
<p>If you plan to clone a repository via SSH, a functioning SSH server is required, but fortunately, Ubuntu 14.04 comes with one out of the box.</p>

<h2 id="step-2-—-install-go">Step 2 — Install Go</h2>

<p>Because Gogs is written in Go, we need to install it before compiling Gogs.</p>

<p>First, there are some environment variables we need to set for Go. To do that, open the file <code>~/.bashrc</code> for editing.</p>
<pre class="code-pre "><code langs="">nano ~/.bashrc
</code></pre>
<p>Add the following lines to the end of the file, then close and save it.</p>
<pre class="code-pre "><code langs="">export GOPATH=/home/git/go
export GOROOT=/usr/local/src/go
export PATH=${PATH}:$GOROOT/bin
</code></pre>
<p>Next, apply your changes.</p>
<pre class="code-pre "><code langs="">source ~/.bashrc
</code></pre>
<p>Then use <code>wget</code> to download latest complied version of Go from its <a href="http://golang.org/dl/">website</a>. At the time of writing, the most recent file is <code>go1.4.2.linux-amd64.tar.gz</code>.</p>
<pre class="code-pre "><code langs="">wget https://storage.googleapis.com/golang/go1.4.2.linux-amd64.tar.gz
</code></pre>
<p>Use <code>tar</code> to unarchive it.</p>
<pre class="code-pre "><code langs="">tar zxf go1.4.2.linux-amd64.tar.gz
</code></pre>
<p>Change directories to the <code>$GOROOT</code> we defined in <code>~/.bashrc</code>.</p>
<pre class="code-pre "><code langs="">sudo mv go $GOROOT
</code></pre>
<p>Now, if you type <code>go</code> in your terminal:</p>
<pre class="code-pre "><code langs="">go
</code></pre>
<p>You should see something like this:</p>
<pre class="code-pre "><code langs="">Go is a tool for managing Go source code.

Usage:

    go command [arguments]

...

Use "go help [topic]" for more information about that topic.
</code></pre>
<h2 id="step-3-—-install-and-start-gogs-as-a-service">Step 3 — Install and Start Gogs as a Service</h2>

<p>Go has a built in command, <code>get</code>, for easily downloading the source code of a Go project along with all of its dependencies, which we'll use to download Gogs.</p>
<pre class="code-pre "><code langs="">go get -d github.com/gogits/gogs
</code></pre>
<p>The source code of Gogs will now be in <code>$GOPATH/src/github.com/gogits/gogs</code>, so move there.</p>
<pre class="code-pre "><code langs="">cd $GOPATH/src/github.com/gogits/gogs
</code></pre>
<p>Next, build and generate the binary. This command may take a moment to run.</p>
<pre class="code-pre "><code langs="">go build
</code></pre>
<p>We're going to use <a href="http://supervisord.org/">Supervisor</a> to manage the Gogs service. </p>

<p>First, let's install it.</p>
<pre class="code-pre "><code langs="">sudo apt-get -y install supervisor
</code></pre>
<p>Let's make a Gogs daemon by creating a Supervisor configuration section. First, create a directory for the log files to live in.</p>
<pre class="code-pre "><code langs="">sudo mkdir -p /var/log/gogs
</code></pre>
<p>Next, we'll open the Supervisor configuration file for editing.</p>
<pre class="code-pre "><code langs="">sudo nano /etc/supervisor/supervisord.conf
</code></pre>
<p>Append the following contents to the file to create the Gogs section.</p>
<pre class="code-pre "><code langs="">[program:gogs]
directory=/home/git/go/src/github.com/gogits/gogs/
command=/home/git/go/src/github.com/gogits/gogs/gogs web
autostart=true
autorestart=true
startsecs=10
stdout_logfile=/var/log/gogs/stdout.log
stdout_logfile_maxbytes=1MB
stdout_logfile_backups=10
stdout_capture_maxbytes=1MB
stderr_logfile=/var/log/gogs/stderr.log
stderr_logfile_maxbytes=1MB
stderr_logfile_backups=10
stderr_capture_maxbytes=1MB
environment = HOME="/home/git", USER="git"
user = git
</code></pre>
<p>This section defines the command we want to execute to start Gogs, automatically starts it with Supervisor, and specifies the locations of log files and corresponding environment variables. For more about Supervisor configuration, read this <a href="https://indiareads/community/tutorials/how-to-install-and-manage-supervisor-on-ubuntu-and-debian-vps">tutorial</a>.</p>

<p>Now restart Supervisor.</p>
<pre class="code-pre "><code langs="">sudo service supervisor restart
</code></pre>
<p>We can check that Gogs is running with the following command.</p>
<pre class="code-pre "><code langs="">ps -ef | grep gogs
</code></pre>
<p>You should see something like this as the output.</p>
<pre class="code-pre "><code langs="">root      1344  1343  0 08:55 ?        00:00:00 /home/git/go/src/github.com/gogits/gogs/gogs web
</code></pre>
<p>You can verify that the server is running by taking a look at the <code>stdout.log</code> file, too.</p>
<pre class="code-pre "><code langs="">tail /var/log/gogs/stdout.log
</code></pre>
<p>You should see a line like this:</p>
<pre class="code-pre "><code langs="">2015/03/09 14:24:42 [I] Gogs: Go Git Service 0.5.16.0301 Beta
</code></pre>
<p>You should also be able visit the web page with URL <code>http://<span class="highlight">your_server_ip</span>:3000/</code>. This will redirect to the installation page, but don't fill that out just yet.</p>

<h2 id="step-4-—-set-up-nginx-as-a-reverse-proxy">Step 4 — Set Up Nginx as a Reverse Proxy</h2>

<p>Let's move on to configuring <strong>Nginx</strong> as a reverse proxy so you can easily bind a domain name to Gogs.</p>

<p>First, install Nginx.</p>
<pre class="code-pre "><code langs="">sudo apt-get -y install nginx
</code></pre>
<p>Next, create an Nginx configuration file for gogs.</p>
<pre class="code-pre "><code langs="">sudo nano /etc/nginx/sites-available/gogs
</code></pre>
<p>Add the following content, replacing <code><span class="highlight">your_server_ip</span></code> with your Droplet's IP address. If you're using a domain name for your Droplet, you can use your domain name here instead.</p>
<pre class="code-pre "><code langs="">server {
    listen 80;
    server_name <span class="highlight">your_server_ip</span>;

    proxy_set_header X-Real-IP  $remote_addr; # pass on real client IP

    location / {
        proxy_pass http://localhost:3000;
    }
}
</code></pre>
<p>And symlink it so that Nginx can use it.</p>
<pre class="code-pre "><code langs="">sudo ln -s /etc/nginx/sites-available/gogs /etc/nginx/sites-enabled/gogs
</code></pre>
<p>For more about Nginx virtual host configuration files, see this <a href="https://indiareads/community/tutorials/how-to-set-up-nginx-server-blocks-virtual-hosts-on-ubuntu-14-04-lts">tutorial</a>.</p>

<p>Finally, restart Nginx to activate the virtual host configuration.</p>
<pre class="code-pre "><code langs="">sudo service nginx restart
</code></pre>
<p>You should now be able visit the web page with the URL <code>http://<span class="highlight">your_server_ip</span>/</code>, without specifying the port.</p>

<h2 id="step-5-—-initialize-gogs">Step 5 — Initialize Gogs</h2>

<p>There is one more simple step left to initialize Gogs for its first run.</p>

<p>Visit <code>http://<span class="highlight">your_server_ip</span>/install</code> and fill in the following options. Many of them will be filled out for you already, but make sure to replace the variables in red with the values for your server.</p>

<p>In the first section, <strong>Gogs requires MySQL, PostgreSQL or SQLite3</strong>, fill out:</p>

<ul>
<li>Database Type: <code>MySQL</code></li>
<li>Host: <code>127.0.0.1:3306</code></li>
<li>User: <code>root</code></li>
<li>Password: <code><span class="highlight">your_database_password</span></code></li>
<li>Database Name: <code>gogs</code></li>
</ul>

<p>In the second section, <strong>General Settings of Gogs</strong>, fill out:</p>

<ul>
<li>Repository Root Path: <code>/home/git/gogs-repositories</code></li>
<li>Run User: <code>git</code></li>
<li>Domain: <code><span class="highlight">your_server_ip</span></code></li>
<li>HTTP Port: <code>3000</code></li>
<li>Application URL: <code>http://<span class="highlight">your_server_ip</span>/</code></li>
</ul>

<p>Skip the optional e-mail and notification settings, then under <strong>Admin Account Settings</strong>, choose an admin username and password, and include your email address. We'll refer to the admin username as <code><span class="highlight">your_admin_username</span></code> in the next step.</p>

<p>Finally, click <strong>Install Gogs</strong>, and then log in.</p>

<h2 id="step-6-—-test-gogs">Step 6 — Test Gogs</h2>

<p>You're all done! Let's do a simple pull/push test just to make sure Gogs is functioning correctly.</p>

<p>First, go to <code>http://<span class="highlight">your_server_ip</span>/repo/create</code> and create a repository with the name <strong>my-test-repo</strong>, and you click on the option <strong>Initialize this repository with a README.md</strong>.</p>

<p>Now you should be able to clone it. First, move to your home directory.</p>
<pre class="code-pre "><code langs="">cd
</code></pre>
<p>Next, clone the repository.</p>
<pre class="code-pre "><code langs="">git clone http://<span class="highlight">your_server_ip</span>/<span class="highlight">your_admin_username</span>/my-test-repo.git
</code></pre>
<p>Change to the repository directory.</p>
<pre class="code-pre "><code langs="">cd my-test-repo
</code></pre>
<p>Update the <code>README.md</code>.</p>
<pre class="code-pre "><code langs="">echo 'I love Gogs!' >> README.md
</code></pre>
<p>Commit your changes and push them. This command will ask you for your Gogs username and password.</p>
<pre class="code-pre "><code langs="">git add --all && git commit -m "init commit" && git push origin master
</code></pre>
<h3 id="conclusion">Conclusion</h3>

<p>Now, if you go back to <code>http://<span class="highlight">your_server_ip</span>/<span class="highlight">your_admin_username</span>/my-test-repo</code>, you'll see the "I love Gogs!" line appended to the README. It's that easy!</p>

    