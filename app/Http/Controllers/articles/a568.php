<link rel='stylesheet' href='digital_ocean.css'><br><img src=https://community-cdn-digitalocean-com.global.ssl.fastly.net/assets/tutorials/images/large/salt_cloud_tw.png?1426699753/> <br> 
      <h3 id="introduction">Introduction</h3>

<p>You have your app written, and now you need to deploy it. You could make a production environment and set your app up on a VM, but how do you scale it when it gets popular? How do you roll out new versions? What about load balancing? And, most importantly, how can you be certain the configuration is correct? We can automate all of this to save ourselves a lot of time.</p>

<p>In this tutorial, we'll show you how to define your application in a Salt Cloud map file, including the use of custom Salt grains to assign roles to your servers and dynamically configure a reverse proxy.</p>

<p>At the end of this tutorial, you will have two basic app servers, an Nginx reverse proxy with a dynamically-built configuration, and the ability to scale your application in minutes.</p>

<h2 id="prerequisites">Prerequisites</h2>

<p>To follow this tutorial, you will need:</p>

<ul>
<li><p>One 1 GB CentOS 7 Droplet. All commands in this tutorial will be exeucted as root, so you don't need to create a sudo non-root user.</p></li>
<li><p>An SSH key on the Droplet for the <strong>root</strong> user (i.e. a new keypair created on the Droplet). Add this SSH key in the IndiaReads control panel so you can use it to log into other IndiaReads Droplets from the master Droplet. You can use the  <a href="https://indiareads/community/tutorials/how-to-use-ssh-keys-with-digitalocean-droplets">How To Use SSH Keys with IndiaReads Droplets</a> tutorial for instructions.</p>

<p>Make a note of the name you assign to the key in the IndiaReads control panel. In this tutorial, we're using the name <span class="highlight">salt-master-root-key</span>. You should also make a note of the private key location; by default, it's <code>/root/.ssh/id_rsa</code>.</p></li>
<li><p>A personal access token, which you can create by following the instructions in this step of <a href="https://indiareads/community/tutorials/how-to-use-the-digitalocean-api-v2#how-to-generate-a-personal-access-token">How To Use the IndiaReads APIv2</a>. Be sure to set the scope to read and write.</p></li>
</ul>

<h2 id="step-1-—-installing-salt-and-salt-cloud">Step 1 — Installing Salt and Salt Cloud</h2>

<p>To start, you'll need to have Salt Cloud installed and configured on your server. For this tutorial, we'll just use the Salt bootstrap script.</p>

<p>First, etch the Salt bootstrap script to install Salt.</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">wget -O install_salt.sh https://bootstrap.saltstack.com
</li></ul></code></pre>
<p>Run the Salt bootstrap script. We use the <code>-M</code> flag to also install <code>salt-master</code>.</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sh install_salt.sh -M
</li></ul></code></pre>
<p>While the Salt Cloud codebase has been merged into the core Salt project, it's still packaged separately for CentOS. Fortunately, the <code>install_salt</code> script configured the repos for us, so we can just install <code>salt-cloud</code> with yum:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">yum install salt-cloud
</li></ul></code></pre>
<p>Now we can check Salt Cloud's version to confirm a successful installation:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">salt-cloud --version
</li></ul></code></pre>
<p>You should see output like this:</p>
<div class="code-label " title="salt-cloud --version output">salt-cloud --version output</div><pre class="code-pre "><code langs="">salt-cloud 2015.5.3 (Lithium)
</code></pre>
<p>Note that Salt is on a rolling release cycle, so your version may differ slightly from above.</p>

<h2 id="step-2-—-configuring-salt-cloud">Step 2 — Configuring Salt Cloud</h2>

<p>In this section, we'll configure Salt Cloud to connect to IndiaReads and define some profiles for our Droplets.</p>

<h3 id="configuring-the-digitalocean-provider-file">Configuring the IndiaReads Provider File</h3>

<p>In Salt Cloud, <em>provider files</em> are where you define where the new VMs will be created. Providers are defined in the <code>/etc/salt/cloud.providers.d</code> directory</p>

<p>Create and open a IndiaReads provider file using <code>nano</code> or your favorite text editor.</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">nano /etc/salt/cloud.providers.d/digital_ocean.conf
</li></ul></code></pre>
<p>Insert the below text, replacing the variables in <span class="highlight">red</span> with yours — namely the server IP and access token, and also the SSH key name and file if you customized them.</p>
<div class="code-label " title="/etc/salt/cloud.providers.d/digital_ocean.conf">/etc/salt/cloud.providers.d/digital_ocean.conf</div><pre class="code-pre "><code langs="">### /etc/salt/cloud.providers.d/digital_ocean.conf ###
######################################################
do:
  provider: digital_ocean
  minion:                      
    master: <span class="highlight">your_server_ip</span>

  # IndiaReads Access Token
  personal_access_token: <span class="highlight">your_access_token</span>

  # This is the name of your SSH key in your Digital Ocean account
  # as it appears in the control panel.          
  ssh_key_name: <span class="highlight">salt-master-root-key</span> 

  # This is the path on disk to the private key for your Digital Ocean account                                                                    
  ssh_key_file: <span class="highlight">/root/.ssh/id_rsa</span>
</code></pre>
<p>You need to lock down the permissions on your SSH key file. Otherwise, SSH will refuse to use it. Assuming yours is at the default location, <code>/root/.ssh/id_rsa</code>, you can do this with:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">chmod 600 /root/.ssh/id_rsa
</li></ul></code></pre>
<h3 id="configuring-the-profiles-for-deployable-servers">Configuring the Profiles for Deployable Servers</h3>

<p>In Salt Cloud, <em>profiles</em> are individual VM descriptions that are tied to a provider (e.g. "A 512 MB Ubuntu VM on IndiaReads"). These are defined in the <code>/etc/salt/cloud.profiles.d</code> directory.</p>

<p>Create and open a profile file.</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">nano /etc/salt/cloud.profiles.d/digital_ocean.conf
</li></ul></code></pre>
<p>Paste the following into the file. No modification is necessary:</p>
<div class="code-label " title="/etc/salt/cloud.profiles.d/digital_ocean.conf">/etc/salt/cloud.profiles.d/digital_ocean.conf</div><pre class="code-pre "><code langs="">### /etc/salt/cloud.profiles.d/digital_ocean.conf ###
#####################################################

ubuntu_512MB_ny3:
  provider: do
  image: ubuntu-14-04-x64
  size: 512MB
  location: nyc3
  private_networking: True

ubuntu_1GB_ny3:
  provider: do
  image: ubuntu-14-04-x64
  size: 1GB
  location: nyc3
  private_networking: True
</code></pre>
<p>Save and close the file. This file defines two profiles:</p>

<ul>
<li>A Ubuntu 14.04 VM with 512 MB of memory, deployed in the New York 3 region.</li>
<li>A Ubuntu 14.04 VM with 1 GB of memory, deployed in the New York 3 region.</li>
</ul>

<p>If you want to use an image other than Ubuntu 14.04, you can use Salt Cloud to list all of the available image names on IndiaReads with the following command:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">salt-cloud --list-images do
</li></ul></code></pre>
<p>This will show all of the standard IndiaReads images, as well as custom images that you have saved on your account with the snapshot tool. You can replace the image name or region that we used in the provider file with a different image name from this list. If you do, make sure to use the <span class="highlight">slug</span> field from this output in the <span class="highlight">image</span> setting in the profile file.</p>

<p>Test your configuration with a quick query.</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">salt-cloud -Q
</li></ul></code></pre>
<p>You should see something like the following.</p>
<div class="code-label " title="Example salt-cloud -Q output">Example salt-cloud -Q output</div><pre class="code-pre "><code langs="">[INFO    ] salt-cloud starting
do:
    ----------
    digital_ocean:
        ----------
        centos-salt:
            ----------
            id:
                2806501
            image_id:
                6372108
            public_ips:
                192.241.247.229
            size_id:
                63
            state:
                active
</code></pre>
<p>This means Salt Cloud is talking to your IndiaReads account, and you have two basic profiles configured.</p>

<h2 id="step-three-—-writing-a-simple-map-file">Step Three — Writing a Simple Map File</h2>

<p>A map file is a YAML file that lists the profiles and number of servers you want to create. We'll start with a simple map file, then build on it in the next section.</p>

<p>Using the above profiles, let's say you want two 1 GB app servers fronted by a single 512 MB reverse proxy. We'll make a mapfile named <code>/etc/salt/cloud.maps.d/do-app-with-rproxy.map</code> and define the app.</p>

<p>First, create the file:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">nano /etc/salt/cloud.maps.d/do-app-with-rproxy.map
</li></ul></code></pre>
<p>Insert the following text. No modification is necessary:</p>
<div class="code-label " title="/etc/salt/cloud.maps.d/do-app-with-rproxy.map">/etc/salt/cloud.maps.d/do-app-with-rproxy.map</div><pre class="code-pre "><code langs="">### /etc/salt/cloud.maps.d/do-app-with-rproxy.map ####
######################################################
ubuntu_512MB_ny3:
  - nginx-rproxy

ubuntu_1GB_ny3:
  - appserver-01
  - appserver-02
</code></pre>
<p>That's it! That's about as simple as a Map File gets. Go ahead and deploy those servers with:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">salt-cloud -m /etc/salt/cloud.maps.d/do-app-with-rproxy.map
</li></ul></code></pre>
<p>Once the command finishes, confirm success with a quick ping:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">salt '*' test.ping
</li></ul></code></pre>
<p>You should see the following:</p>
<pre class="code-pre "><code langs="">[label salt '*' test.ping
appserver-01:
    True
appserver-02:
    True
nginx-rproxy:
    True
</code></pre>
<p>Once you've successfully created the VMs in your map file, deleting them is just as easy:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">salt-cloud -d -m /etc/salt/cloud.maps.d/do-app-with-rproxy.map
</li></ul></code></pre>
<p>Be sure to use that command with caution, though! It will delete <em>all</em> the VMs specified in that map file.</p>

<h2 id="step-four-—-writing-a-more-realistic-map-file">Step Four — Writing A More Realistic Map File</h2>

<p>That map file worked fine, but even a shell script could spin up a set of VMs. What we need is to define the footprint of our application. Let's go back to our map file and add a few more things; open the map file again.</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">nano /etc/salt/cloud.maps.d/do-app-with-rproxy.map
</li></ul></code></pre>
<p>Delete the previous contents of the file and place the following into it. No modification is needed:</p>
<div class="code-label " title="/etc/salt/cloud.maps.d/do-app-with-rproxy.map">/etc/salt/cloud.maps.d/do-app-with-rproxy.map</div><pre class="code-pre "><code langs="">### /etc/salt/cloud.maps.d/do-app-with-rproxy.map ###
#####################################################
ubuntu_512MB_ny3:
  - nginx-rproxy:
      minion:
        mine_functions:
          network.ip_addrs:
            interface: eth0
        grains:
          roles: rproxy
ubuntu_1GB_ny3:
  - appserver-01:
      minion:
        mine_functions:
          network.ip_addrs:
            interface: eth0
        grains:
          roles: appserver
  - appserver-02:
      minion:
        mine_functions:
          network.ip_addrs:
            interface: eth0
        grains:
          roles: appserver
</code></pre>
<p>Now we're getting somewhere! It looks like a lot but we've only added two things. Let's go over the two additions: the <code>mine_functions</code> section and the <code>grains</code> section.</p>

<p>We've told Salt Cloud to modify the Salt Minion config for these VMs and add some custom <a href="http://docs.saltstack.com/en/latest/topics/targeting/grains.html">grains</a>. Specifically, the grains give the reverse proxy VM the <code>rproxy</code> role and give the app servers the <code>appserver</code> role. This will come in handy when we need to dynamically configure the reverse proxy.</p>

<p>The <code>mine_functions</code> will also be added to the Salt Minion config. It instructs the Minion to send the IP address found on <strong>eth0</strong> back to the Salt Master to be stored in the <a href="http://docs.saltstack.com/en/latest/topics/mine/">Salt mine</a>. This means the Salt Master will automatically know the IP of the newly-created Droplet without us having to configure it. We'll be using this in the next part.</p>

<h2 id="step-five-—-defining-the-reverse-proxy">Step Five — Defining the Reverse Proxy</h2>

<p>We have a common task in front of us now: install the reverse proxy web server and configure it. For this tutorial, we'll be using Nginx as the reverse proxy. </p>

<h3 id="writing-the-nginx-salt-state">Writing the Nginx Salt State</h3>

<p>It's time to get our hands dirty and write a few Salt states. First, make the default Salt state tree location:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">mkdir /srv/salt
</li></ul></code></pre>
<p>Navigate into that directory and make one more directory just for nginx:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">cd /srv/salt
</li><li class="line" prefix="$">mkdir /srv/salt/nginx
</li></ul></code></pre>
<p>Go into that directory and, using your favorite editor, create a new file called <code>rproxy.sls</code>:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">cd /srv/salt/nginx
</li><li class="line" prefix="$">nano /srv/salt/nginx/rproxy.sls
</li></ul></code></pre>
<p>Place the following into that file. No modification is needed:</p>
<div class="code-label " title="/srv/salt/nginx/rproxy.sls">/srv/salt/nginx/rproxy.sls</div><pre class="code-pre "><code langs="">### /srv/salt/nginx/rproxy.sls ###
##################################

### Install Nginx and configure it as a reverse proxy, pulling the IPs of
### the app servers from the Salt Mine.

nginx-rproxy:
  # Install Nginx
  pkg:
    - installed
    - name: nginx    
  # Place a customized Nginx config file
  file:
    - managed
    - source: salt://nginx/files/awesome-app.conf.jin
    - name: /etc/nginx/conf.d/awesome-app.conf
    - template: jinja
    - require:
      - pkg: nginx-rproxy
  # Ensure Nginx is always running.
  # Restart Nginx if the config file changes.
  service:
    - running
    - enable: True
    - name: nginx
    - require:
      - pkg: nginx-rproxy
    - watch:
      - file: nginx-rproxy
  # Restart Nginx for the initial installation.
  cmd:
    - run
    - name: service nginx restart
    - require:
      - file: nginx-rproxy
</code></pre>
<p>This state does the following:</p>

<ul>
<li>Installs Nginx.</li>
<li>Places our custom config file into <code>/etc/nginx/conf.d/awesome-app.conf</code>.</li>
<li>Ensures Nginx is running.</li>
</ul>

<p>Our Salt state simply installs Nginx and drops a config file; the really interesting content is in the config.</p>

<h3 id="writing-the-nginx-reverse-proxy-config-file">Writing the Nginx Reverse Proxy Config File</h3>

<p>Let's make one more directory for our config file:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">mkdir /srv/salt/nginx/files
</li><li class="line" prefix="$">cd /srv/salt/nginx/files
</li></ul></code></pre>
<p>And open the config file:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">nano /srv/salt/nginx/files/awesome-app.conf.jin
</li></ul></code></pre>
<p>Put the following in the config file. No modification is necessary, unless you are <strong>not</strong> using private networking; in that case, change the <code>1</code> to <code>0</code> as noted in-line:</p>
<div class="code-label " title="/srv/salt/nginx/files/awesome-app.conf.jin">/srv/salt/nginx/files/awesome-app.conf.jin</div><pre class="code-pre "><code langs="">### /srv/salt/nginx/files/awesome-app.conf.jin ###
##################################################

### Configuration file for Nginx to act as a 
### reverse proxy for an app farm.

# Define the app servers that we're in front of.
upstream awesome-app {
    {% for server, addrs in salt['mine.get']('roles:appserver', 'network.ip_addrs', expr_form='grain').items() %}
    server {{ addrs[0] }}:1337;
    {% endfor %}
}

# Forward all port 80 http traffic to our app farm, defined above as 'awesome-app'.
server {
    listen       80;
    server_name  {{ salt['network.ip_addrs']()[<span class="highlight">1</span>] }};  # <span class="highlight"><-- change the '1' to '0' if you're not using </span>
                                                       #     IndiaReads's private networking.

    access_log  /var/log/nginx/awesome-app.access.log;
    error_log  /var/log/nginx/awesome-app.error.log;

    ## forward request to awesome-app ##
    location / {
     proxy_pass  http://awesome-app;
     proxy_set_header        Host            $host;
     proxy_set_header        X-Real-IP       $remote_addr;
     proxy_set_header        X-Forwarded-For $proxy_add_x_forwarded_for;
   }
}
</code></pre>
<p>We use the <code>.jin</code> extension to tell ourselves that the file contains <a href="http://docs.saltstack.com/en/latest/ref/renderers/all/salt.renderers.jinja.html">Jinja templating</a>. Jinja templating allows us to put a small amount of logic into our text files so we can dynamically generate config details.</p>

<p>This config file instructs Nginx to take all port 80 HTTP traffic and forward it on to our app farm. It has two parts: an upstream (our app farm) and the configuration to act as a proxy between the user and our app farm.</p>

<p>Let's talk about the upstream. A normal, non-templated upstream specifies a collection of IPs. However, we don't know what the IP addresses of our minions will be until they exist, and we don't edit config files manually. (Otherwise, there'd be no reason to use Salt!)</p>

<p>Remember the <code>mine_function</code> lines in our map file? The minions are giving their IPs to the Salt Master to store them for just such an occassion. Let's look at that Jinja line a little closer:</p>
<div class="code-label " title="Jinja excerpt">Jinja excerpt</div><pre class="code-pre "><code langs="">{% for server, addrs in salt['mine.get']('roles:appserver', 'network.ip_addrs', expr_form='grain').items() %}
</code></pre>
<p>This is a for-loop in Jinja, running an arbitrary Salt function. In this case, it's running <a href="http://docs.saltstack.com/en/latest/ref/modules/all/salt.modules.mine.html#salt.modules.mine.get"><code>mine.get</code></a>. The parameters are:</p>

<ul>
<li><code>roles:appserver</code> - This says to only get the details from the minions who have the "appserver" role.</li>
<li><code>network.ip_addrs</code> - This is the data we want to get out of the mine. We specified this in our map file as well.</li>
<li><code>expr_form='grain'</code> - This tells Salt that we're targeting our minions based on their grains. More on matching by grain at <a href="http://docs.saltstack.com/en/latest/topics/targeting/grains.html">the Saltstack targeting doc</a>.</li>
</ul>

<p>Following this loop, the variable <code>{{addrs}}</code> contains a list of IP addresses (even if it's only one address). Because it's a list, we have to grab the first element with <code>[0]</code>.</p>

<p>That's the upstream. As for the server name:</p>
<pre class="code-pre "><code langs="">server_name  {{ salt['network.ip_addrs']()[0] }};
</code></pre>
<p>This is the same trick as the Salt mine call (call a Salt function in Jinja). It's just simpler. It's calling <a href="http://docs.saltstack.com/en/latest/ref/modules/all/salt.modules.network.html#salt.modules.network.ip_addrs"><code>network.ip_addrs</code></a> and taking the first element of the returned list. This also lets us avoid having to manually edit our file.</p>

<h2 id="step-six-—-defining-the-app-farm">Step Six — Defining the App Farm</h2>

<p>A reverse proxy doesn't mean much if it doesn't have an app behind it. Let's make a small Node.js application that just reports the IP of the server it's on (so we can confirm we're reaching both machines).</p>

<p>Make a new directory called <code>awesome-app</code> and move there.</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">mkdir -p /srv/salt/awesome-app
</li><li class="line" prefix="$">cd /srv/salt/awesome-app
</li></ul></code></pre>
<p>Create a new app state file called <code>app.sls</code>.</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">nano /srv/salt/awesome-app/app.sls
</li></ul></code></pre>
<p>Place the following into the file. No modification is necessary:</p>
<div class="code-label " title="/srv/salt/awesome-app/app.sls">/srv/salt/awesome-app/app.sls</div><pre class="code-pre "><code langs="">### /srv/salt/awesome-app/app.sls ###
#####################################

### Install Nodejs and start a simple
### web application that reports the server IP.

install-app:
  # Install prerequisites
  pkg:
    - installed
    - names: 
      - node
      - npm
      - nodejs-legacy  # workaround for Debian systems
  # Place our Node code
  file: 
    - managed
    - source: salt://awesome-app/files/app.js
    - name: /root/app.js
  # Install the package called 'forever'
  cmd:
    - run
    - name: npm install forever -g
    - require:
      - pkg: install-app

run-app:
  # Use 'forever' to start the server
  cmd:
    - run
    - name: forever start app.js
    - cwd: /root
</code></pre>
<p>This state file does the following:</p>

<ul>
<li>Installs the <code>nodejs</code>, <code>npm</code>, and <code>nodejs-legacy</code> packages.</li>
<li>Adds the JavaScript file that will be our simple app.</li>
<li>Uses NPM to install <a href="https://www.npmjs.org/package/forever"><code>Forever</code></a>.</li>
<li>Runs the app.</li>
</ul>

<p>Now create the (small) app code:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">mkdir /srv/salt/awesome-app/files
</li><li class="line" prefix="$">cd /srv/salt/awesome-app/files
</li></ul></code></pre>
<p>Create the file:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">nano /srv/salt/awesome-app/files/app.js
</li></ul></code></pre>
<p>Place the following into it. No modification is needed:</p>
<div class="code-label " title="/srv/salt/awesome-app/files/app.js">/srv/salt/awesome-app/files/app.js</div><pre class="code-pre "><code langs="">/* /srv/salt/awesome-app/files/app.js

   A simple Node.js web application that
   reports the server's IP.
   Shamefully stolen from StackOverflow:
   http://stackoverflow.com/questions/10750303/how-can-i-get-the-local-ip-address-in-node-js
*/

var os = require('os');
var http = require('http');

http.createServer(function (req, res) {
  var interfaces = os.networkInterfaces();
  var addresses = [];
  for (k in interfaces) {
      for (k2 in interfaces[k]) {
          var address = interfaces[k][k2];
          if (address.family == 'IPv4' && !address.internal) {
              addresses.push(address.address)
          }
      }
  }

  res.writeHead(200, {'Content-Type': 'text/plain'});
  res.end(JSON.stringify(addresses));
}).listen(1337, '0.0.0.0');
console.log('Server listening on port 1337');

</code></pre>
<p>This is a simple Node.js server that does one thing: accepts HTTP requests on port 1337 and respond with the server's IPs.</p>

<p>At this point, you should have a file structure that looks like the following:</p>
<div class="code-label " title="File structure">File structure</div><pre class="code-pre "><code langs="">/srv/salt
         ├── awesome-app
         │   ├── app.sls
         │   └── files
         │       └── app.js
         └── nginx
             ├── rproxy.sls
             └── files
                 └── awesome-app.conf.jin
</code></pre>
<h2 id="step-seven-—-deploying-the-application">Step Seven — Deploying the Application</h2>

<p>All that's left is to deploy the application.</p>

<h3 id="deploy-the-servers-with-salt-cloud">Deploy the Servers With Salt Cloud</h3>

<p>Run the same deployment command from earlier, which will now use all the configurations we've been making.</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">salt-cloud -m /etc/salt/cloud.maps.d/do-app-with-rproxy.map
</li></ul></code></pre>
<p>Wait for Salt Cloud to complete; this will take a while. Once it returns, confirm successful deployment by pinging the app servers:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">salt -G 'roles:appserver' test.ping
</li></ul></code></pre>
<p>You should see:</p>
<div class="code-label " title="App server ping output">App server ping output</div><pre class="code-pre "><code langs="">appserver-02:
    True
appserver-01:
    True
</code></pre>
<p>Ping the reverse proxy:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">salt -G 'roles:rproxy' test.ping
</li></ul></code></pre>
<p>You should see:</p>
<div class="code-label " title="Reverse proxy ping output">Reverse proxy ping output</div><pre class="code-pre "><code langs="">nginx-rproxy:
    True
</code></pre>
<p>Once you have your VMs, it's time to give them work.      </p>

<h3 id="build-the-application">Build the Application</h3>

<p>Next, issue the Salt commands to automatically build the app farm and the reverse proxy.</p>

<p>Build the app farm:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">salt -G 'roles:appserver' state.sls awesome-app.app
</li></ul></code></pre>
<p>There will be a fair amount of output, but it should end with the following:</p>
<pre class="code-pre "><code langs="">Summary
------------
Succeeded: 6 (changed=6)
Failed:    0
------------
Total states run:     6

</code></pre>
<p>Build the reverse proxy:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">salt -G 'roles:rproxy' state.sls nginx.rproxy
</li></ul></code></pre>
<p>Again, there will be a fair amount of output, ending with the following:</p>
<pre class="code-pre "><code langs="">Summary
------------
Succeeded: 4 (changed=4)
Failed:    0
------------
Total states run:     4

</code></pre>
<p>So what just happened here? </p>

<p>The first command (the one with the app servers) took the Salt state that we wrote earlier and executed it on the two app servers. This resulted in two machines with identical configurations running identical versions of code.</p>

<p>The second command (the reverse proxy) executed the Salt state we wrote for Nginx. It installed Nginx and  the configuration file, dynamically filling in the IPs of our app farm in the config file.</p>

<p>Once those Salt runs complete, you can test to confirm successful deployment. Find the IP of your reverse proxy:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">salt -G 'roles:rproxy' network.ip_addrs
</li></ul></code></pre>
<p>You may get back two IPs if you're using private networking on your Droplet.</p>

<p>Plug the public IP into your browser and visit the web page! Hit refresh a few times to confirm that Nginx is actually proxying among the two app servers you built. You should see the IPs changing, confirming that you are, indeed, connecting to more than one app server.</p>

<p>If you get the same IP despite refreshing, it's likely due to browser caching. You can try using <code>curl</code> instead to show that Nginx is proxying among your app servers. Run this command several times and observe the output:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">curl http://<span class="highlight">ip-of-nginx-rproxy</span>
</li></ul></code></pre>
<p>We can take this a few steps further and <em>completely</em> automate the application deployment via <a href="http://docs.saltstack.com/en/latest/topics/tutorials/states_pt5.html#states-overstate">OverState</a>. This would let us build a single command to tell Salt to build, say, the app servers first before moving on to build the reverse proxy, guaranteeing the order of our build process.</p>

<h2 id="step-eight-—-scaling-up-optional">Step Eight — Scaling Up (Optional)</h2>

<p>The point of using Salt is to automate your build process; the point of using Salt Cloud and map files is to easily scale your deployment. If you wanted to add more app servers (say, two more) to your deployment, you would update your map file to look like this:</p>
<div class="code-label " title="/etc/salt/cloud.maps.d/do-app-with-rproxy.map">/etc/salt/cloud.maps.d/do-app-with-rproxy.map</div><pre class="code-pre "><code langs="">### /etc/salt/cloud.maps.d/do-app-with-rproxy.map ###
#####################################################
ubuntu_512MB_ny3:
  - nginx-rproxy:
      minion:
        mine_functions:
          network.ip_addrs:
            interface: eth0
        grains:
          roles: rproxy
ubuntu_1GB_ny3:
- appserver-01:
    minion:
      mine_functions:
        network.ip_addrs:
            interface: eth0
      grains:
        roles: appserver
- appserver-02:
    minion:
      mine_functions:
        network.ip_addrs:
            interface: eth0
      grains:
        roles: appserver
- appserver-03:
    minion:
      mine_functions:
        network.ip_addrs:
            interface: eth0
      grains:
        roles: appserver
- appserver-04:
    minion:
      mine_functions:
        network.ip_addrs:
            interface: eth0
      grains:
        roles: appserver        
</code></pre>
<p>After making that update, you would re-run the <span class="highlight">salt-cloud</span> command and the two <span class="highlight">salt</span> commands from Step 6:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">salt-cloud -m /etc/salt/cloud.maps.d/do-app-with-rproxy.map
</li><li class="line" prefix="$">salt -G 'roles:appserver' state.sls awesome-app.app
</li><li class="line" prefix="$">salt -G 'roles:rproxy' state.sls nginx.rproxy
</li></ul></code></pre>
<p>The existing servers wouldn't be impacted by the repeat Salt run, the new servers would be built to spec, and the Nginx config would update to begin routing traffic to the new app servers.</p>

<h2 id="conclusion">Conclusion</h2>

<p>Deploying an app that just reports the server's IP isn't very useful. Fortunately, this approach is not limited to Node.js applications. Salt doesn't care what language your app is written in.</p>

<p>If you wanted to take this framework to deploy your own app, you would just need to automate the task of installing your app on a server (either via a script or with Salt states) and replace our <span class="highlight">awesome-app</span> example with your own automation.</p>

<p>Just as Salt automates processes on your Droplets, Salt Cloud automates processes on your cloud. Enjoy!</p>

    