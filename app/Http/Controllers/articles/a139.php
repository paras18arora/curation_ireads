<link rel='stylesheet' href='digital_ocean.css'><br> <br> 
      <h3 id="introduction">Introduction</h3>

<hr />

<p>In April 2013, <a href="http://john.onolan.org/">John O'Nolan</a>, no newcomer to the field of blog-making, launched a Kickstarter for a new kind of blog called <a href="http://en.ghost.org/">Ghost</a>, which could radically simplify writing and maintaining a blog.  Here, we'll walk through all of the steps to get Ghost set up and running on a IndiaReads VPS.</p>

<h2 id="prerequisites">Prerequisites</h2>

<hr />

<p>Before you get started, there are a few things that you should pull together</p>

<ol>
<li><p>Obtain a copy of Ghost</p>

<ul>
<li>This tutorial will assume you already have a copy of Ghost on your local computer.  Since it's only available to Kickstarter backers right now, you should have been sent a link to the site where you can download it.
<br /></li>
</ul></li>
<li><p>Set up a VPS</p>

<ul>
<li>This tutorial will assume that you've already set up a VPS.  We'll be using Ubuntu 12.04, but you should be fine with whatever you'd like.  If you need help with this part, <a href="https://indiareads/community/articles/how-to-create-your-first-digitalocean-droplet-virtual-server">this tutorial</a> will get you started.
<br /></li>
</ul></li>
<li><p>Point a domain at your VPS</p>

<ul>
<li>This tutorial will assume that you've already pointed a domain at your VPS.  <a href="https://indiareads/community/articles/how-to-set-up-a-host-name-with-digitalocean">This tutorial</a> should help you out with that part, if you're unsure of how to do that.</li>
</ul></li>
</ol>

<h2 id="step-1-install-npm">Step 1: Install npm</h2>

<hr />

<p>Before we get started, I highly recommend making sure your system is up-to-date.  Start by SSHing into your VPS by running:</p>
<pre class="code-pre "><code langs="">ssh root@*your-server-ip*
</code></pre>
<p>on your local machine, and running the following on your VPS:</p>
<pre class="code-pre "><code langs="">apt-get update
apt-get upgrade
</code></pre>
<p>Once that is complete, we need to get <code>npm</code> installed.  Running the following commands will install some dependancies for Node, add its repository to <code>apt-get</code>, and then install <code>nodejs</code>.</p>
<pre class="code-pre "><code langs="">apt-get install python-software-properties python g++ make
add-apt-repository ppa:chris-lea/node.js
apt-get update
apt-get install nodejs
</code></pre>
<p><strong>Note:</strong> You shouldn't need to run the commands with <code>sudo</code> because you're probably logged in as root, but if you're deviating from this tutorial and are logged in as another user, remember that you'll probably need <code>sudo</code>.</p>

<p>Now, if you run <code>npm</code> at the command line, you should see some help information printed out.  If that's all good, you're ready to install Ghost!</p>

<h2 id="step-2-install-ghost">Step 2: Install Ghost</h2>

<hr />

<p>The next thing to do will be getting your copy of Ghost onto the remote server.  Please note that this step is only necessary for now, while Ghost is in beta.  Once it is available to the public, it will be installable through <code>npm</code> (and this tutorial will likely be updated to reflect that).</p>

<p>You're welcome to download the file directly to your VPS or transfer it via FTP.  I will show you how to use SCP to copy the folder from your host to the server.  The following commands are to be run in your local terminal:</p>
<pre class="code-pre "><code langs="">cd /path/to/unzipped/ghost/folder
scp -r ghost-0.3 root@*your-server-ip*:~/
</code></pre>
<p>This will copy all of the contents of the <code>ghost-0.3</code> folder to the home folder of the root user on the server.</p>

<p>Now, back on the remote server, move into the Ghost folder that you just uploaded and use <code>npm</code> to install Ghost.  The commands will look something like this:</p>
<pre class="code-pre "><code langs="">cd ~/ghost-0.3
npm install --production
</code></pre>
<p>Once this finishes, run the following to make sure that the install worked properly:</p>
<pre class="code-pre "><code langs="">npm start
</code></pre>
<p>Your output should look something like the following:</p>
<pre class="code-pre "><code langs="">> ghost@0.3.0 start /root/ghost-0.3
> node index

Ghost is running...
Listening on 127.0.0.1:2368
Url configured as: http://my-ghost-blog.com
</code></pre>
<p>If that is the case, congratulations! Ghost is up and running on your server.  Stop the process with Control-C and move onto the next steps to complete the configuration.</p>

<h2 id="step-3-install-and-configure-nginx">Step 3: Install and Configure nginx</h2>

<hr />

<p>The next step is to install and configure <code>nginx</code>.  Nginx (pronounced "engine-x") is "a free, open-source, high-performance HTTP server and reverse proxy".  Basically, it will allow connections from the outside on port 80 to connect through to the port that Ghost is running on, so that people can see your blog.</p>

<p>Intallation is simple:</p>
<pre class="code-pre "><code langs="">apt-get install nginx
</code></pre>
<p>Configuration is only a little more challenging.  Start off by <code>cd</code>ing to nginx's configuration files and removing the default file:</p>
<pre class="code-pre "><code langs="">cd /etc/nginx/
rm sites-enabled/default
</code></pre>
<p>Now, make a new configuration file:</p>
<pre class="code-pre "><code langs="">cd sites-available
touch ghost
</code></pre>
<p>And paste in the following code, modifying it to adapt to your own configuration (the only thing you should need to change is the domain name):</p>
<pre class="code-pre "><code langs="">server {
    listen 0.0.0.0:80;
    server_name *your-domain-name*;
    access_log /var/log/nginx/*your-domain-name*.log;

    location / {
        proxy_set_header X-Real-IP $remote_addr;
        proxy_set_header HOST $http_host;
        proxy_set_header X-NginX-Proxy true;

        proxy_pass http://127.0.0.1:2368;
        proxy_redirect off;
    }
}
</code></pre>
<p>Finally, create a symlink from the file in <code>sites-available</code> to <code>sites-enabled</code>:</p>
<pre class="code-pre "><code langs="">cd ..
ln -s sites-available/ghost sites-enabled/ghost
</code></pre>
<p>This will listen for traffic incoming on port 80 and pass the requests along to Ghost, provided they are connecting to the domain that you provide.</p>

<p>Start up the server again (use the code from the end of Step 2) and visit your domain.  If you see Ghost, you're good to go!</p>

<p><img src="https://assets.digitalocean.com/articles/ghost/g824kEY.png" alt="ghost" /></p>

<h2 id="step-4-configure-upstart">Step 4: Configure Upstart</h2>

<hr />

<p>The last step is to make an Upstart task that will handle Ghost and make sure that, should your server get turned off for some reason, Ghost will get kicked back on.  Start by making a new Upstart configuration file by doing the following:</p>
<pre class="code-pre "><code langs="">cd /etc/init
nano ghost.conf
</code></pre>
<p>And paste in the following configuration:</p>
<pre class="code-pre "><code langs=""># ghost

# description "An Upstart task to make sure that my Ghost server is always running"
# author "Your Name Here"

start on startup

script
    cd /root/ghost
    npm start
end script
</code></pre>
<p>This should ensure that Ghost gets started whenever your server does, and allow you to easily control Ghost using <code>service ghost start</code>, <code>service ghost stop</code>, and <code>service ghost restart</code>.</p>

    