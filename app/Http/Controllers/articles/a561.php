<link rel='stylesheet' href='digital_ocean.css'><br><img src=https://community-cdn-digitalocean-com.global.ssl.fastly.net/assets/tutorials/images/large/do_automaticscaling_twitter_01.jpg?1426699821/> <br> 
      <h3 id="introduction">Introduction</h3>

<p>In this tutorial, we will demonstrate how to use IndiaReads API to horizontally scale your server setup. To do this, we will use <a href="https://github.com/thisismitch/doproxy">DOProxy</a>, a relatively simple Ruby script that, once configured, provides a command line interface to scale your HTTP application server tier up or down.</p>

<p>DOProxy, which was written specifically for this tutorial, provides a simple way for creating and deleting application server droplets, using the IndiaReads API, and automatically managing them behind an HAProxy load balancer. This basic scaling model allows your users to access your application through the HAProxy server, which will forward them to the load-balanced, backend application servers.</p>

<p>DOProxy performs three primary functions:</p>

<ul>
<li>Create a droplet, and add it to the load balancer</li>
<li>Delete a droplet, and remove it from the load balancer</li>
<li>Maintain an inventory of the droplets that it created, until they are deleted</li>
</ul>

<p><img src="https://assets.digitalocean.com/articles/apiv2/doproxy/doproxy_create.png" alt="DOProxy create" /></p>

<p><strong>Note:</strong> The primary purpose of this tutorial is to teach the minimally required concepts necessary to programmatically scale your IndiaReads server architecture through the API. You should not run DOProxy, in its current form, in a production environment; it was not designed with resiliency in mind, and it performs just enough error checking to get by. With that said, if you are curious about learning about horizontal scaling through the API, it's a great way to get started.</p>

<h2 id="prerequisites">Prerequisites</h2>

<p>This tutorial touches on a variety of technologies that you might want to read up on before proceeding, including:</p>

<ul>
<li><a href="https://indiareads/community/tutorials/an-introduction-to-haproxy-and-load-balancing-concepts">Reverse proxy load balancers, such as HAProxy</a></li>
<li><a href="https://indiareads/community/tutorials/how-to-use-the-digitalocean-api-v2">IndiaReads API v2</a></li>
<li><a href="https://indiareads/community/tutorials/an-introduction-to-cloud-config-scripting">Cloudinit and User-data</a></li>
<li><a href="https://indiareads/community/tutorials/an-introduction-to-droplet-metadata">IndiaReads Droplet Metadata</a></li>
</ul>

<p>Because DOProxy is written in Ruby, knowledge of Ruby is a plus but not necessary; we will provide pseudocode to explain the gist of the DOProxy code. Also, we will use the official IndiaReads Ruby wrapper, <a href="https://github.com/digitalocean/droplet_kit">DropletKit</a>, which enables us to easily make API calls in our Ruby code.</p>

<p>Before we get into the details of DOProxy works, we will install and use it on a server. Let's install DOProxy on an Ubuntu 14.04 droplet now.</p>

<h2 id="install-doproxy">Install DOProxy</h2>

<p>First, create an Ubuntu 14.04 droplet in the NYC3 region (you may use any region that supports <strong>private networking</strong> and <strong>userdata</strong> if you configure the <code>region</code> variable in the <code>doproxy.yml</code> file after installing DOProxy). This droplet will run the HAProxy load balancer, and the DOProxy scaling script, so choose a size that you think will be adequate for your desired scale potential. Because this tutorial is a basic demonstration of scaling, which won't receive any real traffic, the 1GB size is probably adequate.</p>

<p>We will refer to this droplet as the <em>DOProxy server</em>.</p>

<p>Next, log in and follow the <strong>Installation</strong> and <strong>Configuration</strong> (including <strong>doproxy config</strong> and <strong>Userdata</strong>) sections in the <a href="https://github.com/thisismitch/doproxy">DOProxy GitHub repository</a> to install DOProxy on this server. Use the sample <code>doproxy.yml</code> and <code>user-data.yml</code> files by copying them, as noted in the directions. Be sure to replace the <code>token</code> and <code>ssh_key_ids</code> values in the DOproxy configuration file, or the script will not work.</p>

<p>Now that you have DOProxy and HAProxy installed on your server, let's try and scale our environment.</p>

<h2 id="run-doproxy">Run DOProxy</h2>

<p>Log in to your DOProxy server as <strong>root</strong>, and change to the directory where you cloned DOProxy, if you haven't done so already.</p>

<p>Now run DOProxy without any arguments:</p>
<pre class="code-pre "><code langs="">ruby doproxy.rb
</code></pre>
<p>This should print out the available commands like so:</p>
<pre class="code-pre "><code langs="">Commands:
doproxy.rb print                   # Print backend droplets in inventory file
doproxy.rb create                  # Create a new backend droplet and reload
doproxy.rb delete <LINE_NUMBER>    # Delete a droplet and reload
doproxy.rb reload                  # Generate HAProxy config and reload HAProxy
doproxy.rb generate                # Generate HAProxy config based on inventory
</code></pre>
<p>Currently, DOProxy hasn't created any droplets. Let's create some to get our HTTP service online, and scale up.</p>

<h3 id="scale-up-create">Scale Up (Create)</h3>

<p>Run the create command to create the first droplet that is managed by DOProxy:</p>
<pre class="code-pre "><code langs="">ruby doproxy.rb create
</code></pre>
<p>This will take some time before returning to the prompt (because the script creates a new droplet via the API and waits for it to boot up). We'll talk about how the API call is made, later, when we go through the DOProxy code.</p>

<p>When it is complete, you should see a success message that contains the droplet ID, like so:</p>
<pre class="code-pre "><code langs="">Success: 4202645 created and added to backend.
</code></pre>
<p>If you visit your DOProxy server's public IP address in a web browser. You should see a page that lists your new droplet's <em>hostname</em>, <em>id</em>, and <em>public IP address</em>.</p>

<p>We'll use DOProxy to create two more droplets, for a total of three. Feel free to create more if you want: </p>
<pre class="code-pre "><code langs="">ruby doproxy.rb create
ruby doproxy.rb create
</code></pre>
<p>Now visit your DOProxy server's public IP address in a web browser again. If you refresh the page, you will notice that the information on the page will change—it will cycle through the droplets that you created. This is because they are all being load balanced by HAProxy—each droplet is added to the load balancer configuration when it is created.</p>

<p>If you happen to look in the IndiaReads Control Panel, you will notice that these new droplets will be listed there (along with the rest of your droplets):</p>

<p><img src="https://assets.digitalocean.com/articles/apiv2/doproxy/doproxy_control_panel.png" alt="Droplets in Control Panel" /></p>

<p>Let's take a closer look at the droplets that were created by looking at DOProxy's inventory.</p>

<h3 id="print-inventory">Print Inventory</h3>

<p>DOProxy provides a <em>print</em> command, that will print out all of the droplets that are part of its inventory:</p>
<pre class="code-pre "><code langs="">ruby doproxy.rb print
</code></pre>
<p>You should see output that looks something like this:</p>
<pre class="code-pre "><code langs="">0) auto-nginx-0  (pvt ip: 10.132.224.168, status: active, id: 4202645)
1) auto-nginx-1  (pvt ip: 10.132.228.224, status: active, id: 4205587)
2) auto-nginx-2  (pvt ip: 10.132.252.42, status: active, id: 4205675)
</code></pre>
<p>In the example output, we see information about the three droplets that we created, such as their hostnames, status, and droplet IDs. The hostnames and IDs should match what you saw when you accessed the HAProxy load balancer (via DOProxy's public IP address).</p>

<p>As you may have noticed, DOProxy only printed information about droplets that it created. This is because it maintains an inventory of the droplets it creates.</p>

<p>Check out the contents of the <code>inventory</code> file now:</p>
<pre class="code-pre "><code langs="">cat inventory
</code></pre>
<p>You should see the ID of each droplet, one per line. Each time a droplet is created, its ID is stored in this inventory file.</p>

<p>As you may have guessed, DOProxy's <code>print</code> command iterates through the droplet IDs in the inventory file and performs an API call to retrieve droplet information about each one.</p>

<p>It should be noted that storing your server inventory in a single file is not the best solution—it can easily be corrupted or deleted—but it demonstrates a simple implementation that works. A distributed key value store, such as <strong>etcd</strong>, would be a better solution. You would also want to save more than just the droplet ID in the inventory (so you don't have to make API calls every time you want to look at certain droplet information).</p>

<h3 id="scale-down-delete">Scale Down (Delete)</h3>

<p>DOProxy also has a delete command that lets you delete droplets in your inventory. The delete command requires that you provide the line number of the droplet to delete (as displayed by the <code>print</code> command).</p>

<p>Before running this command you will probably want to print your inventory:</p>
<pre class="code-pre "><code langs="">ruby doproxy.rb print
</code></pre>
<p>So, for example, if you want to delete the third droplet, you would supply <code>2</code> as the line number:</p>
<pre class="code-pre "><code langs="">ruby doprorxy.rb delete 2
</code></pre>
<p>After a moment, you'll see the confirmation message:</p>
<pre class="code-pre "><code langs="">Success: 4205675 deleted and removed from backend.
</code></pre>
<p>The delete command deletes the droplet via the API, removes it from the HAProxy configuration, and deletes it from the inventory. Feel free to verify that the droplet was deleted by using the DOProxy print command or by checking the IndiaReads control panel. You will also notice that it is no longer part of the load balancer.</p>

<h2 id="haproxy-configuration">HAProxy Configuration</h2>

<p>The last piece of DOProxy that we haven't discussed is how HAProxy is configured.</p>

<p>When you run the <code>create</code> or <code>delete</code> DOProxy command, the information of each droplet in the inventory is retrieved, and some of the information is used to create an HAProxy configuration file. In particular, the droplet ID and private IP address is used to add each droplet as a backend server.</p>

<p>Look at the last few lines of the generated <code>haproxy.cfg</code> file like this:</p>
<pre class="code-pre "><code langs="">tail haproxy.cfg
</code></pre>
<p>You should see something like this:</p>
<pre class="code-pre "><code langs="">    frontend www-http
       bind 104.236.236.43:80
       reqadd X-Forwarded-Proto:\ http
       default_backend www-backend

    backend www-backend

       server www-4202645 10.132.224.168:80 check # id:4202645, hostname:auto-nginx-0
       server www-4205587 10.132.228.224:80 check # id:4205587, hostname:auto-nginx-1
</code></pre>
<p>The <code>frontend</code> section should contain the public IP address of your DOProxy server, and the <code>backend</code> section should contain lines that refer to each of the droplets that were created.</p>

<p><strong>Note:</strong> At this point, you may want to delete the rest of the droplets that were created with DOProxy (<code>ruby doproxy.rb delete 0</code> until all of the servers are gone).</p>

<p>Now that you've seen DOProxy's scaling in action, let's take a closer look at the code.</p>

<h2 id="doproxy-code">DOProxy Code</h2>

<p>In this section, we will look at the pertinent files and lines of code that make DOProxy work. Seeing how DOProxy was implemented should give you some ideas of how you can use the API to manage and automate your own server infrastructure.</p>

<p>Since you cloned the repository to your server, you can look at the files there, or you can look at the files at the DOProxy repository <a href="https://github.com/thisismitch/doproxy">(https://github.com/thisismitch/doproxy)</a>.</p>

<p>Important files:</p>

<ul>
<li><strong>doproxy.rb</strong>: DOProxy Ruby script. Provides the command line interface and brains behind DOProxy</li>
<li><strong>doproxy.yml</strong>: DOProxy configuration file. Contains API token and specifies droplet create options</li>
<li><strong>haproxy.cfg.erb</strong>: HAProxy configuration template. Used to generate load balancer configuration with proper backend server information</li>
<li><strong>inventory</strong>: Droplet inventory file. Stores IDs of created droplets</li>
<li><strong>user-data.yml</strong>: Userdata file. A cloud-config file that will run on a new droplet when it is created</li>
</ul>

<p>Let's dive into the configuration files first.</p>

<h3 id="doproxy-yml">doproxy.yml</h3>

<p>The important lines in the DOProxy configuration file, <code>doproxy.yml</code>, are the following:</p>
<pre class="code-pre "><code langs="">token: <span class="highlight">878a490235d53e34b44369b8e78</span>
ssh_key_ids:           # IndiaReads ID for your SSH Key
  - <span class="highlight">163420</span>
...
droplet_options:
  hostname_prefix: auto-nginx
  region: nyc3
  size: 1gb
  image: ubuntu-14-04-x64
</code></pre>
<p>The <code>token</code> is where you can configure your <em>read and write</em> API token.</p>

<p>The other lines specify the options that will be used when DOProxy creates a new droplet. For example, it will install the specified SSH key (by ID or fingerprint), and it will prefix the hostnames with "auto-nginx".</p>

<p>More information about valid droplet options, check out the <a href="https://developers.digitalocean.com/v2/#create-a-new-droplet">IndiaReads API documentation</a>.</p>

<h3 id="user-data-yml">user-data.yml</h3>

<p>The userdata file, <code>user-data.yml</code>, is a file that will be executed by cloud-init on each new droplet, when it is created. This means that you can supply a cloud-config file or a script to install your application software on each new droplet.</p>

<p>The sample userdata file contains a simple bash script that installs Nginx on an Ubuntu server, and replaces its default configuration file with the droplet hostname, ID, and public IP address:</p>
<pre class="code-pre "><code langs="">#!/bin/bash

apt-get -y update
apt-get -y install nginx
export DROPLET_ID=$(curl http://169.254.169.254/metadata/v1/id)
export HOSTNAME=$(curl -s http://169.254.169.254/metadata/v1/hostname)
export PUBLIC_IPV4=$(curl -s http://169.254.169.254/metadata/v1/interfaces/public/0/ipv4/address)
echo Droplet: $HOSTNAME, ID: $DROPLET_ID, IP Address: $PUBLIC_IPV4 > /usr/share/nginx/html/index.html
</code></pre>
<p>The droplet information (hostname, ID, and IP address) are retrieved through the IndiaReads Metadata service—that's what those <code>curl</code> commands are doing.</p>

<p>Obviously, you would want to do something more useful than this, like install and configure your application. You can use this to automate the integration of your droplets into your overall infrastructure, by doing things like automatically installing SSH keys and connecting to your configuration management or monitoring tools.</p>

<p>To read more about userdata, cloud-config, and metadata, check out these links:</p>

<ul>
<li><a href="https://indiareads/community/tutorials/an-introduction-to-cloud-config-scripting">An Introduction to Cloud-Config Scripting</a></li>
<li><a href="https://indiareads/community/tutorials/an-introduction-to-droplet-metadata">An Introduction to Droplet Metadata</a></li>
</ul>

<h3 id="haproxy-cfg-erb">haproxy.cfg.erb</h3>

<p>The HAProxy configuration template, <code>haproxy.cfg.erb</code>, contains most of the load balancer configuration, with some Ruby code that will be replaced with backend droplet information. </p>

<p>We'll just look at the Ruby section that generates the backend configuration:</p>
<pre class="code-pre "><code langs="">backend www-backend
   <% @droplets.each_with_index do |droplet, index| %>
   server www-<%= droplet.id %> <%= droplet.private_ip %>:80 check # id:<%= droplet.id %>, hostname:<%= droplet.name -%>
   <% end %>
</code></pre>
<p>This code iterates through each of the droplets in the inventory, and adds a new HAProxy backend for each one (based on the private IP address).</p>

<p>For example, a line like this will be produced for each droplet:</p>
<pre class="code-pre "><code langs="">server www-4202645 10.132.224.168:80 check # id:4202645, hostname:auto-nginx-0
</code></pre>
<p>Whenever a droplet is created or deleted, DOProxy generates a new HAProxy configuration file—the <code>haproxy.cfg</code> file that you looked at earlier.</p>

<h3 id="doproxy-rb">doproxy.rb</h3>

<p>The DOProxy Ruby script, <code>doproxy.rb</code>, consists mainly of a DOProxy class that contains the methods that perform the droplet creation and deletion, inventory management, and HAProxy configuration generation.</p>

<p>If you understand Ruby, check out the file on GitHub: <a href="https://github.com/thisismitch/doproxy/blob/master/doproxy.rb">https://github.com/thisismitch/doproxy/blob/master/doproxy.rb</a>.</p>

<p>If you don't understand Ruby, here is some simplified pseudocode that explains each method. It may be useful to reference this against the actual Ruby code, to help you understand what is happening.</p>

<h4 id="def-initialize">def initialize</h4>

<p>Executed every time DOProxy runs, unless no arguments are specified.</p>

<ol>
<li>Read <code>doproxy.yml</code> configuration file (get API token, and droplet options). 2ified.</li>
</ol>

<h4 id="def-get_inventory">def get_inventory</h4>

<p>Retrieves information for each droplet in the inventory file. It must be executed before any of the following methods are executed.</p>

<ol>
<li>Read inventory file (which contains droplet IDs)</li>
<li>For each droplet ID, use the API to retrieve droplet information</li>
</ol>

<h4 id="def-print_inventory">def print_inventory</h4>

<p>When the "doproxy.rb print" command is used, prints droplet information to the screen. It relies on <code>get_inventory</code>.</p>

<ol>
<li>For each droplet in the inventory, print the hostname, private IP address, status, and ID (which was retrieved by <code>get_inventory</code>)</li>
</ol>

<h4 id="def-create_server">def create_server</h4>

<p>When the "doproxy.rb create" command is used, creates a new droplet and adds it to the inventory file, then calls <code>reload_haproxy</code> to generate HAProxy configuration and reload the load balancer.</p>

<ol>
<li>Read the userdata file</li>
<li>Use API to create a droplet based on supplied userdata and options</li>
<li>Wait for droplet status to become "active"—use API to retrieve droplet information every 15 seconds until status changes</li>
<li>When status is "active", add the droplet ID to the inventory file</li>
<li>Call <code>reload_haproxy</code> to generate HAProxy configuration and reload the load balancer</li>
</ol>

<h4 id="def-delete_server-line_number">def delete_server(line_number)</h4>

<p>When the "doproxy.rb delete" command is used, deletes the specified droplet and deletes its ID from the inventory file, then calls <code>reload_haproxy</code> to generate HAProxy configuration and reload the load balancer.</p>

<ol>
<li>Remove the specified line from the inventory file (delete droplet ID)</li>
<li>Use API to delete droplet by its ID</li>
<li>Call <code>reload_haproxy</code> to generate HAProxy configuration and reload the load balancer</li>
</ol>

<h4 id="def-generate_haproxy_cfg">def generate_haproxy_cfg</h4>

<p>This is a supporting method that creates new HAProxy configuration files based on the droplets in the inventory.</p>

<ol>
<li>Open the HAProxy configuration template, <code>haproxy.cfg.erb</code></li>
<li>For each droplet in inventory, add a corresponding backend server</li>
<li>Write resulting <code>haproxy.cfg</code> file to disk</li>
</ol>

<h4 id="def-reload_haproxy">def reload_haproxy</h4>

<p>This is a supporting method that copies the HAProxy configuration file into the proper location, and reloads HAProxy. This relies on <code>generate_haproxy_cfg</code>.</p>

<ol>
<li>Copy HAProxy configuration file <code>haproxy.cfg</code> to the location where HAProxy will read on reload</li>
<li>Reload HAProxy</li>
</ol>

<p>That's all of the important code that makes DOProxy work. The last thing we will discuss is DropletKit, the API wrapper that we used in DOProxy.</p>

<h2 id="dropletkit-gem">DropletKit Gem</h2>

<p>DOProxy uses the <a href="https://github.com/digitalocean/droplet_kit">DropletKit gem</a>, the official IndiaReads API v2 Ruby wrapper, to make IndiaReads API calls. DropletKit allows us to easily write Ruby programs that do things like:</p>

<ul>
<li>Create new droplets</li>
<li>Delete existing droplets</li>
<li>Get information about existing droplets, such as status, IP address, droplet ID, region, etc</li>
</ul>

<p>This tutorial focused on these particular API endpoints, but keep in mind that there are many other endpoints that can help facilitate programmatic management your IndiaReads server infrastructure.</p>

<h2 id="conclusion">Conclusion</h2>

<p>Now that you've seen how a simple script can help scale a server environment, by leveraging the IndiaReads API, cloud-config, and metadata, hopefully you can apply these concepts to scale your own server setup. Although DOProxy isn't production ready, it should give you some ideas for implementing your own scaling solution.</p>

<p>Remember that the scaling setup described here, with DOProxy, is great but it could be greatly improved by using it in conjunction with a monitoring system. This would allow you to automatically scale your application server tier up and down, depending on certain conditions, such as server resource utilization.</p>

<p>Have any questions or comments? Feel free to post them below!</p>

    