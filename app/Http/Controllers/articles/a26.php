<link rel='stylesheet' href='digital_ocean.css'><br> <br> 
      <h3 id="introduction">Introduction</h3>

<p>Node.js is an open source JavaScript runtime environment for easily building server-side and networking applications. The platform runs on Linux, OS X, FreeBSD, and Windows. Node.js applications can be run at the command line, but we'll focus on running them as a service, so that they will automatically restart on reboot or failure, and can safely be used in a production environment.</p>

<p>In this tutorial, we will cover setting up a production-ready Node.js environment on a single Ubuntu 16.04 server.  This server will run a Node.js application managed by PM2, and provide users with secure access to the application through an Nginx reverse proxy.  The Nginx server will offer HTTPS, using a free certificate provided by Let's Encrypt.</p>

<h2 id="prerequisites">Prerequisites</h2>

<p>This guide assumes that you have an Ubuntu 16.04 server, configured with a non-root user with <code>sudo</code> privileges, as described in the <a href="https://digitalocean.com/community/articles/initial-server-setup-with-ubuntu-16-04">initial server setup guide for Ubuntu 16.04</a>.</p>

<p>It also assumes that you have a domain name, pointing at the server's public IP address.</p>

<p>Let's get started by installing the Node.js runtime on your server.</p>

<h2 id="install-node-js">Install Node.js</h2>

<p>We will install the latest current release of Node.js, using the <a href="https://github.com/nodesource/distributions">NodeSource</a> package archives.</p>

<p>First, you need to install the NodeSource PPA in order to get access to its contents.  Make sure you're in your home directory, and use <code>curl</code> to retrieve the installation script for the Node.js 6.x archives:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">cd ~
</li><li class="line" prefix="$">curl -sL https://deb.nodesource.com/setup_6.x -o nodesource_setup.sh
</li></ul></code></pre>
<p>You can inspect the contents of this script with <code>nano</code> (or your preferred text editor):</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">nano nodesource_setup.sh
</li></ul></code></pre>
<p>And run the script under <code>sudo</code>:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo bash nodesource_setup.sh
</li></ul></code></pre>
<p>The PPA will be added to your configuration and your local package cache will be updated automatically. After running the setup script from nodesource, you can install the Node.js package in the same way that you did above:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo apt-get install nodejs
</li></ul></code></pre>
<p>The <code>nodejs</code> package contains the <code>nodejs</code> binary as well as <code>npm</code>, so you don't need to install <code>npm</code> separately. However, in order for some <code>npm</code> packages to work (such as those that require compiling code from source), you will need to install the <code>build-essential</code> package:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo apt-get install build-essential
</li></ul></code></pre>
<p>The Node.js runtime is now installed, and ready to run an application! Let's write a Node.js application.</p>

<p><span class="note"><strong>Note:</strong> When installing from the NodeSource PPA, the Node.js executable is called <code>nodejs</code>, rather than <code>node</code>.<br /></span></p>

<h2 id="create-node-js-application">Create Node.js Application</h2>

<p>We will write a <em>Hello World</em> application that simply returns "Hello World" to any HTTP requests. This is a sample application that will help you get your Node.js set up, which you can replace with your own application--just make sure that you modify your application to listen on the appropriate IP addresses and ports.</p>

<h3 id="hello-world-code">Hello World Code</h3>

<p>First, create and open your Node.js application for editing. For this tutorial, we will use <code>nano</code> to edit a sample application called <code>hello.js</code>:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">cd ~
</li><li class="line" prefix="$">nano hello.js
</li></ul></code></pre>
<p>Insert the following code into the file.  If you want to, you may replace the highlighted port, <code><span class="highlight">8080</span></code>, in both locations (be sure to use a non-admin port, i.e. 1024 or greater):</p>
<div class="code-label " title="hello.js">hello.js</div><pre class="code-pre "><code langs="">#!/usr/bin/env nodejs
var http = require('http');
http.createServer(function (req, res) {
  res.writeHead(200, {'Content-Type': 'text/plain'});
  res.end('Hello World\n');
}).listen(<span class="highlight">8080</span>, 'localhost');
console.log('Server running at http://localhost:<span class="highlight">8080</span>/');
</code></pre>
<p>Now save and exit.</p>

<p>This Node.js application simply listens on the specified address (<code>localhost</code>) and port (<code><span class="highlight">8080</span></code>), and returns "Hello World" with a <code>200</code> HTTP success code. Since we're listening on <strong>localhost</strong>, remote clients won't be able to connect to our application.</p>

<h3 id="test-application">Test Application</h3>

<p>In order to test your application, mark <code>hello.js</code> executable:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">chmod +x ./hello.js
</li></ul></code></pre>
<p>And run it like so:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">./hello.js
</li></ul></code></pre><pre class="code-pre "><code langs=""><div class="secondary-code-label " title="Output">Output</div>Server running at http://localhost:<span class="highlight">8080</span>/
</code></pre>
<p><span class="note"><strong>Note:</strong> Running a Node.js application in this manner will block additional commands until the application is killed by pressing <strong>Ctrl-C</strong>.<br /></span></p>

<p>In order to test the application, open another terminal session on your server, and connect to <strong>localhost</strong> with <code>curl</code>:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">curl http://localhost:<span class="highlight">8080</span>
</li></ul></code></pre>
<p>If you see the following output, the application is working properly and listening on the proper address and port:</p>
<pre class="code-pre "><code langs=""><div class="secondary-code-label " title="Output">Output</div>Hello World
</code></pre>
<p>If you do not see the proper output, make sure that your Node.js application is running, and configured to listen on the proper address and port.</p>

<p>Once you're sure it's working, kill the application (if you haven't already) by pressing <strong>Ctrl+C</strong>.</p>

<h2 id="install-pm2">Install PM2</h2>

<p>Now we will install PM2, which is a process manager for Node.js applications. PM2 provides an easy way to manage and daemonize applications (run them in the background as a service).</p>

<p>We will use <code>npm</code>, a package manager for Node modules that installs with Node.js, to install PM2 on our server. Use this command to install PM2:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo npm install -g pm2
</li></ul></code></pre>
<p>The <code>-g</code> option tells <code>npm</code> to install the module <em>globally</em>, so that it's available system-wide.</p>

<h2 id="manage-application-with-pm2">Manage Application with PM2</h2>

<p>PM2 is simple and easy to use. We will cover a few basic uses of PM2.</p>

<h3 id="start-application">Start Application</h3>

<p>The first thing you will want to do is use the <code>pm2 start</code> command to run your application, <code>hello.js</code>, in the background:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">pm2 start <span class="highlight">hello.js</span>
</li></ul></code></pre>
<p>This also adds your application to PM2's process list, which is outputted every time you start an application:</p>
<pre class="code-pre "><code langs=""><div class="secondary-code-label " title="Output">Output</div>[PM2] Spawning PM2 daemon
[PM2] PM2 Successfully daemonized
[PM2] Starting <span class="highlight">hello.js</span> in fork_mode (1 instance)
[PM2] Done.
┌──────────┬────┬──────┬──────┬────────┬─────────┬────────┬─────────────┬──────────┐
│ App name │ id │ mode │ pid  │ status │ restart │ uptime │ memory      │ watching │
├──────────┼────┼──────┼──────┼────────┼─────────┼────────┼─────────────┼──────────┤
│ <span class="highlight">hello</span>    │ 0  │ fork │ 3524 │ online │ 0       │ 0s     │ 21.566 MB   │ disabled │
└──────────┴────┴──────┴──────┴────────┴─────────┴────────┴─────────────┴──────────┘
 Use `pm2 show <id|name>` to get more details about an app
</code></pre>
<p>As you can see, PM2 automatically assigns an <strong>App name</strong> (based on the filename, without the <code>.js</code> extension) and a PM2 <strong>id</strong>. PM2 also maintains other information, such as the <strong>PID</strong> of the process, its current status, and memory usage.</p>

<p>Applications that are running under PM2 will be restarted automatically if the application crashes or is killed, but an additional step needs to be taken to get the application to launch on system startup (boot or reboot). Luckily, PM2 provides an easy way to do this, the <code>startup</code> subcommand.</p>

<p>The <code>startup</code> subcommand generates and configures a startup script to launch PM2 and its managed processes on server boots. You must also specify the platform you are running on, which is <code>ubuntu</code>, in our case:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">pm2 startup systemd
</li></ul></code></pre>
<p>The last line of the resulting output will include a command that you must run with superuser privileges:</p>
<pre class="code-pre "><code langs=""><div class="secondary-code-label " title="Output">Output</div>[PM2] You have to run this command as root. Execute the following command:
      <span class="highlight">sudo su -c "env PATH=$PATH:/usr/bin pm2 startup systemd -u sammy --hp /home/sammy"</span>
</code></pre>
<p>Run the command that was generated (similar to the highlighted output above, but with your username instead of <code><span class="highlight">sammy</span></code>) to set PM2 up to start on boot (use the command from your own output):</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo su -c "env PATH=$PATH:/usr/bin pm2 startup systemd -u <span class="highlight">sammy</span> --hp /home/<span class="highlight">sammy</span>"
</li></ul></code></pre>
<p>This will create a systemd <strong>unit</strong> which runs <code>pm2</code> for your user on boot.  This <code>pm2</code> instance, in turn, runs <code>hello.js</code>.  You can check the status of the systemd unit with <code>systemctl</code>:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">systemctl status pm2
</li></ul></code></pre>
<p>For a detailed overview of systemd, see <a href="https://indiareads/community/tutorials/systemd-essentials-working-with-services-units-and-the-journal">Systemd Essentials: Working with Services, Units, and the Journal</a>.</p>

<h3 id="other-pm2-usage-optional">Other PM2 Usage (Optional)</h3>

<p>PM2 provides many subcommands that allow you to manage or look up information about your applications. Note that running <code>pm2</code> without any arguments will display a help page, including example usage, that covers PM2 usage in more detail than this section of the tutorial.</p>

<p>Stop an application with this command (specify the PM2 <code>App name</code> or <code>id</code>):</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">pm2 stop <span class="highlight">app_name_or_id</span>
</li></ul></code></pre>
<p>Restart an application with this command (specify the PM2 <code>App name</code> or <code>id</code>):</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">pm2 restart <span class="highlight">app_name_or_id</span>
</li></ul></code></pre>
<p>The list of applications currently managed by PM2 can also be looked up with the <code>list</code> subcommand:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">pm2 list
</li></ul></code></pre>
<p>More information about a specific application can be found by using the <code>info</code> subcommand (specify the PM2 <em>App name</em> or <em>id</em>):</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">pm2 info <span class="highlight">example</span>
</li></ul></code></pre>
<p>The PM2 process monitor can be pulled up with the <code>monit</code> subcommand. This displays the application status, CPU, and memory usage:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">pm2 monit
</li></ul></code></pre>
<p>Now that your Node.js application is running, and managed by PM2, let's set up the reverse proxy.</p>

<h2 id="set-up-nginx-as-a-reverse-proxy-server">Set Up Nginx as a Reverse Proxy Server</h2>

<p>Now that your application is running, and listening on <strong>localhost</strong>, you need to set up a way for your users to access it. We will set up an Nginx web server as a reverse proxy for this purpose. This tutorial will set up an Nginx server from scratch. If you already have an Nginx server setup, you can just copy the <code>location</code> block into the server block of your choice (make sure the location does not conflict with any of your web server's existing content).</p>

<p>First, install Nginx using apt-get:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo apt-get install nginx
</li></ul></code></pre>
<p>Now open the default server block configuration file for editing:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo nano /etc/nginx/sites-available/default
</li></ul></code></pre>
<p>Delete everything in the file and insert the following configuration. Be sure to substitute your own domain name for the <code>server_name</code> directive. Additionally, change the port (<code><span class="highlight">8080</span></code>) if your application is set to listen on a different port:</p>
<div class="code-label " title="/etc/nginx/sites-available/default">/etc/nginx/sites-available/default</div><pre class="code-pre "><code langs="">server {
    listen 80;

    server_name <span class="highlight">example.com</span>;

    location / {
        proxy_pass http://localhost:<span class="highlight">8080</span>;
        proxy_http_version 1.1;
        proxy_set_header Upgrade $http_upgrade;
        proxy_set_header Connection 'upgrade';
        proxy_set_header Host $host;
        proxy_cache_bypass $http_upgrade;
    }
}
</code></pre>
<p>This configures the server to respond to requests at its root. Assuming our server is available at <code>example.com</code>, accessing <code>http://example.com/</code> via a web browser would send the request to <code>hello.js</code>, listening on port <code>8080</code> at <strong>localhost</strong>.</p>

<p>You can add additional <code>location</code> blocks to the same server block to provide access to other applications on the same server. For example, if you were also running another Node.js application on port <code>8081</code>, you could add this location block to allow access to it via <code>http://example.com/app2</code>:</p>
<div class="code-label " title="Nginx Configuration — Additional Locations">Nginx Configuration — Additional Locations</div><pre class="code-pre "><code langs="">    location /app2 {
        proxy_pass http://localhost:<span class="highlight">8081</span>;
        proxy_http_version 1.1;
        proxy_set_header Upgrade $http_upgrade;
        proxy_set_header Connection 'upgrade';
        proxy_set_header Host $host;
        proxy_cache_bypass $http_upgrade;
    }
</code></pre>
<p>Once you are done adding the location blocks for your applications, save and exit.</p>

<p>Next, restart Nginx:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo systemctl restart nginx
</li></ul></code></pre>
<p>Assuming that your Node.js application is running, and your application and Nginx configurations are correct, you should now be able to access your application via the Nginx reverse proxy.  Try it out by accessing your server's URL (its public IP address or domain name).</p>

<p>Now that you can access your application, we'll secure the connection to your app with HTTPS using Let's Encrypt.</p>

<h3 id="install-let-39-s-encrypt-and-dependencies">Install Let's Encrypt and Dependencies</h3>

<p>Let's Encrypt is a new Certificate Authority that provides an easy way to obtain free TLS/SSL certificates.</p>

<p>You must own or control the registered domain name that you wish to use the certificate with. If you do not already have a registered domain name, you may register one with one of the many domain name registrars out there (e.g. Namecheap, GoDaddy, etc.).</p>

<p>If you haven't already, be sure to create an <strong>A Record</strong> that points your domain to the public IP address of your server. This is required because of how Let's Encrypt validates that you own the domain it is issuing a certificate for. For example, if you want to obtain a certificate for <code>example.com</code>, that domain must resolve to your server for the validation process to work.</p>

<p>For more detail on this process, see <a href="https://indiareads/community/tutorials/how-to-set-up-a-host-name-with-digitalocean">How To Set Up a Host Name with IndiaReads</a> and <a href="https://indiareads/community/tutorials/how-to-point-to-digitalocean-nameservers-from-common-domain-registrars">How To Point to IndiaReads Nameservers from Common Domain Registrars</a>.</p>

<p>Before installing Let's Encrypt, make sure that the <code>git</code> and <code>bc</code> packages are installed:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo apt-get -y install git bc
</li></ul></code></pre>
<p>Next, clone the <code>letsencrypt</code> repository from GitHub to <code>/opt/letsencrypt</code>.  The <code>/opt/</code> directory is a standard location for software that's not installed from the distribution's official package repositories:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo git clone https://github.com/letsencrypt/letsencrypt /opt/letsencrypt
</li></ul></code></pre>
<p>Change to the <code>letsencrypt</code> directory:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">cd /opt/letsencrypt
</li></ul></code></pre>
<h3 id="retrieve-initial-certificate">Retrieve Initial Certificate</h3>

<p>Since <code>nginx</code> is already running on port 80, and the Let's Encrypt client needs this port in order to verify ownership of your domain, stop <code>nginx</code> temporarily:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo systemctl stop nginx
</li></ul></code></pre>
<p>Run <code>letsencrypt</code> with the Standalone plugin:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">./letsencrypt-auto certonly --standalone
</li></ul></code></pre>
<p>You'll be prompted to answer several questions, including your email address, agreement to a Terms of Service, and the domain name(s) for the certificate.  Once finished, you'll receive notes much like the following:</p>
<pre class="code-pre "><code langs="">IMPORTANT NOTES:
 - Congratulations! Your certificate and chain have been saved at
   /etc/letsencrypt/live/<span class="highlight">your_domain_name</span>/fullchain.pem. Your cert will expire
   on <span class="highlight">2016-08-10</span>. To obtain a new version of the certificate in the
   future, simply run Let's Encrypt again.
 - If you like Let's Encrypt, please consider supporting our work by:

   Donating to ISRG / Let's Encrypt:   https://letsencrypt.org/donate
   Donating to EFF:                    https://eff.org/donate-le
</code></pre>
<p>Note the path and expiration date of your certificate, highlighted in the example output.  Your certificate files should now be available in <code>/etc/letsencrypt/<span class="highlight">your_domain_name</span>/</code>.</p>

<h3 id="configure-nginx-for-https">Configure Nginx for HTTPS</h3>

<p>You'll need to add some details to your Nginx configuration.  Open <code>/etc/nginx/sites-enabled/default</code> in <code>nano</code> (or your editor of choice):</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo nano /etc/nginx/sites-enabled/default
</li></ul></code></pre>
<p>Replace its contents with the following:</p>
<div class="code-label " title="/etc/nginx/sites-enabled/default">/etc/nginx/sites-enabled/default</div><pre class="code-pre "><code langs=""># HTTP - redirect all requests to HTTPS:
server {
        listen 80;
        listen [::]:80 default_server ipv6only=on;
        return 301 https://$host$request_uri;
}

# HTTPS - proxy requests on to local Node.js app:
server {
        listen 443;
        server_name <span class="highlight">your_domain_name</span>;

        ssl on;
        # Use certificate and key provided by Let's Encrypt:
        ssl_certificate /etc/letsencrypt/live/<span class="highlight">your_domain_name</span>/fullchain.pem;
        ssl_certificate_key /etc/letsencrypt/live/<span class="highlight">your_domain_name</span>/privkey.pem;
        ssl_session_timeout 5m;
        ssl_protocols TLSv1 TLSv1.1 TLSv1.2;
        ssl_prefer_server_ciphers on;
        ssl_ciphers 'EECDH+AESGCM:EDH+AESGCM:AES256+EECDH:AES256+EDH';

        # Pass requests for / to localhost:8080:
        location / {
                proxy_set_header X-Real-IP $remote_addr;
                proxy_set_header X-Forwarded-For $proxy_add_x_forwarded_for;
                proxy_set_header X-NginX-Proxy true;
                proxy_pass http://localhost:8080/;
                proxy_ssl_session_reuse off;
                proxy_set_header Host $http_host;
                proxy_cache_bypass $http_upgrade;
                proxy_redirect off;
        }
}
</code></pre>
<p>Exit the editor and save the file.  Start Nginx again:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo systemctl start nginx
</li></ul></code></pre>
<p>You can test your new certificate and Nginx configuration by visiting <code>http://<span class="highlight">your_domain_name</span>/</code> in your browser.  You should be redirected to <code>https://<span class="highlight">your_domain_name</span>/</code>, without any security errors, and see the "Hello World" printed by your Node.js app.</p>

<h3 id="set-up-let-39-s-encrypt-auto-renewal">Set Up Let's Encrypt Auto Renewal</h3>

<p><span class="warning"><strong>Warning:</strong> You can safely complete this guide without worrying about certificate renewal, but you <strong>will</strong> need to address it for any long-lived production environment.<br /></span></p>

<p>You may have noticed that your Let's Encrypt certificate is due to expire in 90 days.  This is a deliberate feature of the Let's Encrypt approach, intended to minimize the amount of time that a compromised certificate can exist in the wild if something goes wrong.</p>

<p>The Let's Encrypt client can automatically renew your certificate, but in the meanwhile you will either have to repeat the certificate retrieval process by hand, or use a scheduled script to handle it for you.  The details of automating this process are covered in <a href="https://indiareads/community/tutorials/how-to-secure-nginx-with-let-s-encrypt-on-ubuntu-16-04">How To Secure Nginx with Let's Encrypt on Ubuntu 16.04</a>, particularly the section on <a href="https://indiareads/community/tutorials/how-to-secure-nginx-with-let-s-encrypt-on-ubuntu-16-04#step-6-set-up-auto-renewal">setting up auto renewal</a>.</p>

<h2 id="conclusion">Conclusion</h2>

<p>Congratulations! You now have your Node.js application running behind an Nginx reverse proxy on an Ubuntu 16.04 server. This reverse proxy setup is flexible enough to provide your users access to other applications or static web content that you want to share. Good luck with your Node.js development!</p>

    