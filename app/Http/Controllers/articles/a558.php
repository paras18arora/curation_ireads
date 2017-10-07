<link rel='stylesheet' href='digital_ocean.css'><br><img src=https://community-cdn-digitalocean-com.global.ssl.fastly.net/assets/tutorials/images/large/scalable_wordpress_site_tw.jpg?1430403384/> <br> 
      <h3 id="introduction">Introduction</h3>

<p>In this guide, we will create and deploy a scalable WordPress instance consisting of a MySQL database server, a GlusterFS distributed filesystem, Nginx web servers, and an Nginx load balancer.  By using <a href="https://indiareads/company/blog/automating-application-deployments-with-user-data/">user-data</a> and <a href="https://indiareads/community/tutorials/an-introduction-to-droplet-metadata">Droplet meta-data</a>, we will automate the deployment of our site.  Finally, we will provide a Ruby script which will automate this entire process and ease the creation of scalable Wordpress sites.  Through this tutorial, you will learn about the power and flexibility of user-data and Droplet meta-data in deploying services on IndiaReads.</p>

<h2 id="step-one-—-planning-our-deployment">Step One — Planning our Deployment</h2>

<p>The deployment we create in this tutorial will consist of a single MySQL database server, multiple GlusterFS servers in a cluster, multiple Nginx web servers, and a single Nginx load balancer.</p>

<p><img src="https://assets.digitalocean.com/articles/automate_wp_cluster/wp-cluster.png" alt="WordPress Deployment" /></p>

<p>Before we begin we should know:</p>

<ul>
<li>What size Droplet we will use for our MySQL server</li>
<li>How many GlusterFS nodes we will create</li>
<li>What size our GlusterFS nodes will be</li>
<li>How many web server nodes we will need</li>
<li>What size Droplets we will use for our web servers</li>
<li>What size Droplet we will use for our load balancer</li>
<li>The domain name we will use for our new site</li>
</ul>

<p>We can add additional nodes or scale up the nodes we created if we need to later.  Once we have decided on these details, we can begin deploying our site.</p>

<h2 id="step-two-—-deploying-mysql">Step Two — Deploying MySQL</h2>

<p>We will begin by deploying our MySQL server.  To do this, we will create a default Ubuntu 14.04 x64 Droplet using the following user-data.</p>
<pre class="code-pre "><code langs="">#!/bin/bash
export DEBIAN_FRONTEND=noninteractive;
export PUBLIC_IP=$(curl -s http://169.254.169.254/metadata/v1/interfaces/public/0/ipv4/address)
export PRIVATE_IP=$(curl -s http://169.254.169.254/metadata/v1/interfaces/private/0/ipv4/address)
apt-get update;
apt-get -y install mysql-server;
mysqladmin -u root create wordpress;
mysqladmin -u root password "<span class="highlight">mysql_password</span>";
sed -i.bak "s/127.0.0.1/$PRIVATE_IP/g" /etc/mysql/my.cnf;
service mysql restart;
mysql -uroot -p<span class="highlight">mysql_password</span> -e "CREATE USER 'wordpress'@'%' IDENTIFIED BY '<span class="highlight">mysql_password</span>'";
mysql -uroot -p<span class="highlight">mysql_password</span> -e "GRANT ALL PRIVILEGES ON wordpress.* TO 'wordpress'@'%'";
</code></pre>
<p>This user-data script will perform the following functions on our new Droplet:</p>

<p>First, we export a variable which tells <code>apt-get</code> that we are running in non-interactive mode to prevent it from prompting for any input when it installs packages.</p>
<pre class="code-pre "><code langs="">export DEBIAN_FRONTEND=noninteractive;
</code></pre>
<p>Next, we use Droplet meta-data to get the Droplet's public and private IP addresses and assign them to variables:</p>
<pre class="code-pre "><code langs="">export PUBLIC_IP=$(curl -s http://169.254.169.254/metadata/v1/interfaces/public/0/ipv4/address)
export PRIVATE_IP=$(curl -s http://169.254.169.254/metadata/v1/interfaces/private/0/ipv4/address)
</code></pre><pre class="code-pre note"><code langs="">Note: Droplet Meta-Data is not available in NYC1, NYC2, and AMS1 at this time.
</code></pre>
<p>We then use <code>apt</code> to install the MySQL server.</p>
<pre class="code-pre "><code langs="">apt-get update;
apt-get -y install mysql-server;
</code></pre>
<p>Now we need to create a new database called <strong>wordpress</strong>.</p>
<pre class="code-pre "><code langs="">mysqladmin -u root create wordpress;
</code></pre>
<p>Then we set a password for our MySQL root user.</p>
<pre class="code-pre "><code langs="">mysqladmin -u root password "<span class="highlight">mysql_password</span>";
</code></pre>
<p>Because our MySQL server will be accepting queries from our web servers, we need to have it listen on the private IP address rather than only on <code>localhost</code>. To do this, we will use <code>sed</code> to update the MySQL configuration file by doing a find and replace and then restart the service.</p>
<pre class="code-pre "><code langs="">sed -i.bak "s/127.0.0.1/$PRIVATE_IP/g" /etc/mysql/my.cnf;
service mysql restart;
</code></pre>
<p>Finally, we will create a new MySQL user called <strong>wordpress</strong> and give it permission to access the <em>wordpress</em> database.</p>
<pre class="code-pre "><code langs="">mysql -uroot -p<span class="highlight">mysql_password</span> -e "CREATE USER 'wordpress'@'%' IDENTIFIED BY '<span class="highlight">mysql_password</span>'";
mysql -uroot -p<span class="highlight">mysql_password</span> -e "GRANT ALL PRIVILEGES ON wordpress.* TO 'wordpress'@'%'";
</code></pre>
<p>By deploying our new Droplet with this user-data script, we will have a configured MySQL server listening on its private IP address and with our configured database and user without ever logging in via SSH or the console.</p>

<h2 id="step-three-—-deploying-glusterfs">Step Three — Deploying GlusterFS</h2>

<p>Before deploying our GlusterFS cluster, we need to decide how many nodes we will deploy.  There are two variables that will go into this decision.  First, we need to decide how much space we need and then we need to decide on a replica setting to use.  The replica setting tells GlusterFS how many copies of any file to store.  For example, a replica setting of 2 will mean that every file is duplicated on at least 2 servers.  This will cut our available storage in half since we are keeping two copies of each file but will provide improved redundancy.  The number of GlusterFS nodes we create must be a multiple of our replica setting.  For a cluster with a replica setting of 2, we will need to create our nodes in a multiple of 2 (so 2, 4, 6, or 8 nodes would be acceptable).</p>

<p>For this example, we will deploy a 4 node GlusterFS cluster using a replica setting of 2.</p>

<p>For our first 3 nodes, we will use the following user-data script:</p>
<pre class="code-pre "><code langs="">#!/bin/bash
export DEBIAN_FRONTEND=noninteractive;
apt-get update;
apt-get install -y python-software-properties;
add-apt-repository -y ppa:gluster/glusterfs-3.5;
apt-get update;
apt-get install -y glusterfs-server;
</code></pre>
<p>Again, we first set the <code>DEBIAN_FRONTEND</code> variable so <code>apt</code> knows that we are running in non-interactive mode:</p>
<pre class="code-pre "><code langs="">export DEBIAN_FRONTEND=noninteractive;
</code></pre>
<p>We then update our <code>apt</code> database and install <code>python-software-properties</code>, which is needed to add the <a href="https://help.launchpad.net/Packaging/PPA">PPA</a> for GlusterFS.</p>
<pre class="code-pre "><code langs="">apt-get update;
apt-get install -y python-software-properties;
</code></pre>
<p>Next we will add the GlusterFS PPA so we can grab our deb packages.</p>
<pre class="code-pre "><code langs="">add-apt-repository -y ppa:gluster/glusterfs-3.5;
</code></pre>
<p>Then we will update our <code>apt</code> database again and install glusterfs-server.</p>
<pre class="code-pre "><code langs="">apt-get install -y glusterfs-server;
</code></pre>
<p>For our first three nodes, this is all we need to do.  Make a note of the private IP addresses assigned to each of these new Droplets as we will need them when creating our final GlusterFS node and creating our volume.</p>

<p>For our final node, we will use the following user-data script:</p>
<pre class="code-pre "><code langs="">#!/bin/bash
export DEBIAN_FRONTEND=noninteractive;
export PRIVATE_IP=$(curl -s http://169.254.169.254/metadata/v1/interfaces/private/0/ipv4/address)
apt-get update;
apt-get install -y python-software-properties;
add-apt-repository -y ppa:gluster/glusterfs-3.5;
apt-get update;
apt-get install -y glusterfs-server;
sleep 30;
gluster peer probe <span class="highlight">node1_private_ip</span>;
gluster peer probe <span class="highlight">node2_private_ip</span>;
gluster peer probe <span class="highlight">node3_private_ip</span>;
gluster volume create file_store <span class="highlight">replica 2</span> transport tcp <span class="highlight">node1_private_ip:/gluster</span> <span class="highlight">node2_private_ip:/gluster</span> <span class="highlight">node3_private_ip:/gluster</span> $PRIVATE_IP:/gluster force;
gluster volume start file_store;
</code></pre><pre class="code-pre note"><code langs="">Note: If you do not want to enable replication you should not include the "replica" setting in your "volume create" command.
</code></pre>
<p>The first section of this user-data script is pretty similar to the one we used on the other GlusterFS nodes, though we are assigning our new Droplet's private IP to the $PRIVATE_IP variable.  Once <code>glusterfs-server</code> is installed, though, we do some additional work. </p>

<p>First, our script will wait 30 seconds for the new glusterfs-server to start up and be available.</p>
<pre class="code-pre "><code langs="">sleep 30
</code></pre>
<p>Then we probe the three GlusterFS Droplets we created earlier in order to add all four to a cluster.</p>
<pre class="code-pre "><code langs="">gluster peer probe <span class="highlight">node1_private_ip</span>;
gluster peer probe <span class="highlight">node2_private_ip</span>;
gluster peer probe <span class="highlight">node3_private_ip</span>;
</code></pre>
<p>Next we will create our GlusterFS volume named "file<em>store" with a replica setting of 2 and including all four of our nodes.  Since we wont know the IP address of our newest node yet we will use the $PRIVATE</em>IP variable for it.</p>
<pre class="code-pre "><code langs="">gluster volume create file_store replica 2 transport tcp <span class="highlight">node1_private_ip:/gluster</span> <span class="highlight">node2_private_ip:/gluster</span> <span class="highlight">node3_private_ip:/gluster</span> $PRIVATE_IP:/gluster force;
</code></pre>
<p>Finally, we will start the new volume to make it accessible from our clients:</p>
<pre class="code-pre "><code langs="">gluster volume start file_store;
</code></pre>
<p>We now have a distributed filesystem where we can keep our WordPress files that will be accessible to all our web server nodes.</p>

<h2 id="step-four-—-deploying-nginx-web-servers">Step Four — Deploying Nginx Web Servers</h2>

<p>Now that we have a database server and a distributed filesystem all set, we can deploy our web servers.   We will use the following user-data script to deploy our first Nginx web server node and configure our WordPress installation inside our GlusterFS volume.</p>
<pre class="code-pre "><code langs="">#!/bin/bash
export DEBIAN_FRONTEND=noninteractive;
export PRIVATE_IP=$(curl -s http://169.254.169.254/metadata/v1/interfaces/private/0/ipv4/address)
apt-get update;
apt-get -y install nginx glusterfs-client php5-fpm php5-mysql;
sed -i s/\;cgi\.fix_pathinfo\=1/cgi\.fix_pathinfo\=0/g /etc/php5/fpm/php.ini;
mkdir /gluster;
mount -t glusterfs <span class="highlight">gluter_node_private_ip:/file_store</span> /gluster;
echo "<span class="highlight">gluster_node_private_ip:/file_store</span> /gluster glusterfs defaults,_netdev 0 0" >> /etc/fstab;
mkdir /gluster/www;
wget https://raw.githubusercontent.com/ryanpq/do-wpc/master/default -O /etc/nginx/sites-enabled/default;
service nginx restart;
# Get Wordpress Files
wget https://wordpress.org/latest.tar.gz -O /root/wp.tar.gz;
tar -zxf /root/wp.tar.gz -C /root/;
cp -Rf /root/wordpress/* /gluster/www/.;
cp /gluster/www/wp-config-sample.php /gluster/www/wp-config.php;
sed -i "s/'DB_NAME', 'database_name_here'/'DB_NAME', 'wordpress'/g" /gluster/www/wp-config.php;
sed -i "s/'DB_USER', 'username_here'/'DB_USER', 'wordpress'/g" /gluster/www/wp-config.php;
sed -i "s/'DB_PASSWORD', 'password_here'/'DB_PASSWORD', '<span class="highlight">mysql_password</span>'/g" /gluster/www/wp-config.php;
sed -i "s/'DB_HOST', 'localhost'/'DB_HOST', '<span class="highlight">mysql_private_ip</span>'/g" /gluster/www/wp-config.php;
chown -Rf www-data:www-data /gluster/www;
</code></pre>
<p>This script is a bit more complicated than our previous ones, so let's break it down step by step.</p>

<p>First, we will again set the <code>DEBIAN_FRONTEND</code> variable as we have in our previous scripts and populate our <code>$PRIVATE_IP</code> variable.</p>
<pre class="code-pre "><code langs="">export DEBIAN_FRONTEND=noninteractive;
export PRIVATE_IP=$(curl -s http://169.254.169.254/metadata/v1/interfaces/private/0/ipv4/address)
</code></pre>
<p>Next, we will update our <code>apt</code> database and install Nginx, the glusterfs client, and the php libraries we will need.</p>
<pre class="code-pre "><code langs="">apt-get update;
apt-get -y install nginx glusterfs-client php5-fpm php5-mysql;
</code></pre>
<p>Then we will use <code>sed</code>'s find and replace functionality to update our <code>php.ini</code> file and set the cgi.fixpathinfo variable to 0.</p>
<pre class="code-pre "><code langs="">sed -i s/\;cgi\.fix_pathinfo\=1/cgi\.fix_pathinfo\=0/g /etc/php5/fpm/php.ini;
</code></pre>
<p>Now we'll create a folder called <code>/gluster</code> in the root of our disk image and mount our GlusterFS volume there.  Then we will create an fstab entry so our GlusterFS volume is automatically mounted when the Droplet boots.</p>
<pre class="code-pre "><code langs="">mkdir /gluster;
mount -t glusterfs <span class="highlight">gluter_node_private_ip:/file_store</span> /gluster;
echo "<span class="highlight">gluster_node_private_ip:/file_store</span> /gluster glusterfs defaults,_netdev 0 0" >> /etc/fstab;
</code></pre>
<p>Then we will create a folder called <code>www</code> in our GlusterFS volume.  This folder will act as our web root.</p>
<pre class="code-pre "><code langs="">mkdir /gluster/www;
</code></pre>
<p>Next we will pull a new Nginx configuration file from a remote server.  This file will set our web root to <code>/gluster/www</code> and ensure Nginx is configured to use PHP.  You can view this configuration file <a href="https://raw.githubusercontent.com/ryanpq/do-wpc/master/default">here</a>. Once we have replaced our Nginx configuration file we will restart the service for this change to take effect.</p>
<pre class="code-pre "><code langs="">wget https://raw.githubusercontent.com/ryanpq/do-wpc/master/default -O /etc/nginx/sites-enabled/default;
service nginx restart;
</code></pre>
<p>Now we will grab a copy of the latest version of WordPress, extract it and copy its contents to our new web root.</p>
<pre class="code-pre "><code langs="">wget https://wordpress.org/latest.tar.gz -O /root/wp.tar.gz;
tar -zxf /root/wp.tar.gz -C /root/;
cp -Rf /root/wordpress/* /gluster/www/.;
</code></pre>
<p>Next, we will copy the sample WordPress configuration file to <code>wp-config.php</code>.</p>
<pre class="code-pre "><code langs="">cp /gluster/www/wp-config-sample.php /gluster/www/wp-config.php;
</code></pre>
<p>And update its variables to match our new environment, again using <code>sed</code>'s find and replace function.</p>
<pre class="code-pre "><code langs="">sed -i "s/'DB_NAME', 'database_name_here'/'DB_NAME', 'wordpress'/g" /gluster/www/wp-config.php;
sed -i "s/'DB_USER', 'username_here'/'DB_USER', 'wordpress'/g" /gluster/www/wp-config.php;
sed -i "s/'DB_PASSWORD', 'password_here'/'DB_PASSWORD', '<span class="highlight">mysql_password</span>'/g" /gluster/www/wp-config.php;
sed -i "s/'DB_HOST', 'localhost'/'DB_HOST', '<span class="highlight">mysql_private_ip</span>'/g" /gluster/www/wp-config.php;
</code></pre>
<p>And finally, we will make sure that the files in our web root are owned by the user <strong>www-data</strong> which our Nginx process will be running as.</p>
<pre class="code-pre "><code langs="">chown -Rf www-data:www-data /gluster/www;
</code></pre>
<p>We now have our first web server node all set up and ready to receive requests.</p>

<p>Since each of our web server nodes shares the same GlusterFS volume for storage, there are fewer steps for each additional node we create.  For additional nodes we will use the following user-data script:</p>
<pre class="code-pre "><code langs="">#!/bin/bash
export DEBIAN_FRONTEND=noninteractive;
export PRIVATE_IP=$(curl -s http://169.254.169.254/metadata/v1/interfaces/private/0/ipv4/address)
apt-get update;
apt-get -y install nginx glusterfs-client php5-fpm php5-mysql;
sed -i s/\;cgi\.fix_pathinfo\=1/cgi\.fix_pathinfo\=0/g /etc/php5/fpm/php.ini;
mkdir /gluster;
mount -t glusterfs <span class="highlight">gluster_node_private_ip:/file_store</span> /gluster;
echo "<span class="highlight">gluster_node_private_ip:/file_store</span> /gluster glusterfs defaults,_netdev 0 0" >> /etc/fstab;
mkdir /gluster/www;
wget https://raw.githubusercontent.com/ryanpq/do-wpc/master/default -O /etc/nginx/sites-enabled/default;
service nginx restart;
</code></pre>
<p>For our additional web nodes we will still be installing the same packages, mounting our GlusterFS volume and replacing our Nginx configuration file but we will not need to do any setup of our WordPress instance since we did this when creating our first node.</p>

<h2 id="step-five-—-deploying-our-load-balancer">Step Five — Deploying Our Load Balancer</h2>

<p>The final step in this deployment is to create our load balancer. We will be using another Nginx server for this purpose. To set up this node we will use the following user-data script:</p>
<pre class="code-pre "><code langs="">#!/bin/bash
export DEBIAN_FRONTEND=noninteractive;
apt-get update;
apt-get -y install nginx;
lbconf="
server {
    listen 80 default_server;
    listen [::]:80 default_server ipv6only=on;

    root /usr/share/nginx/html;
    index index.php index.html index.htm;

    location / {
      proxy_pass  http://backend;
      include proxy_params;
    }

}

upstream backend  {
    ip_hash;
    server <span class="highlight">web_node_1_private_ip</span>
    server <span class="highlight">web_node_2_private_ip</span>
}
"
echo $lbconf > /etc/nginx/sites-enabled/default;
service nginx restart;
</code></pre>
<p>For our load balancer's user-data script, we will be building our Nginx configuration directly in the script.  We start off much as we did with our other Droplets by ensuring that <code>apt</code> knows we are running in non-interactive mode.</p>
<pre class="code-pre "><code langs="">export DEBIAN_FRONTEND=noninteractive;
</code></pre>
<p>Then we will install Nginx:</p>
<pre class="code-pre "><code langs="">apt-get update;
apt-get -y install nginx;
</code></pre>
<p>Next we will create our new Nginx configuration in a variable called <code>lbconf</code>. Adding an entry for each of our web servers in the <em>upstream backend</em> section.</p>
<pre class="code-pre "><code langs="">lbconf="
server {
    listen 80 default_server;
    listen [::]:80 default_server ipv6only=on;

    root /usr/share/nginx/html;
    index index.php index.html index.htm;

    location / {
      proxy_pass  http://backend;
      include proxy_params;
    }

}

upstream backend  {
    ip_hash;
    server <span class="highlight">web_node_1_private_ip</span>
    server <span class="highlight">web_node_2_private_ip</span>
}
"
</code></pre>
<p>We will then write the <code>lbconf</code> variable to our Nginx configuration file, replacing its current content.</p>
<pre class="code-pre "><code langs="">echo $lbconf > /etc/nginx/sites-enabled/default;
</code></pre>
<p>And finally, we'll restart Nginx for this configuration to take effect.</p>
<pre class="code-pre "><code langs="">service nginx restart;
</code></pre>
<h2 id="step-six-—-setting-up-dns">Step Six — Setting Up DNS</h2>

<p>Now, before we access our new WordPress site via the browser, we should set up a DNS entry for it.  We will do this via the control panel.</p>

<p>In the IndiaReads control panel, click on <strong>DNS</strong>. In the <strong>Add Domain</strong> form, enter your domain name and select your load balancer Droplet from the drop-down menu, then click <strong>Create Domain</strong>.</p>

<p>In order to use the <strong>www</strong> subdomain for your site you will need to create another record in this new domain.</p>

<p>Click <strong>Add Record</strong> and choose the <strong>CNAME</strong> record type. In the name field enter <strong>www</strong> and in the hostname field enter <strong>@</strong>.  This will direct requests for the www subdomain to the same location as your main domain (your load balancer Droplet).</p>

<h2 id="step-seven-—-configuring-wordpress">Step Seven — Configuring WordPress</h2>

<p>Now that we have launched all our Droplets and configured our domain, we can access our new WordPress site by visiting our newly configured domain in a web browser.</p>

<p><img src="https://assets.digitalocean.com/articles/automate_wp_cluster/wp-setup.png" alt="Configure Wordpress" /></p>

<p>We will be prompted here to create a user account and give our new site a name.  Once we have done this our deployment is complete and we can begin using the new site.</p>

<h2 id="step-eight-—-automating-the-process">Step Eight — Automating the Process</h2>

<p>Now that we can create our WordPress deployment without ever needing to ssh into a Droplet, we can take things a step further and automate this process using the <a href="https://developers.digitalocean.com/documentation/v2/">IndiaReads API</a>.  </p>

<p>A sample Ruby script has been created based on this tutorial which will prompt the user to provide the relevant details and then automatically deploy a new scablable WordPress instance. You can find this script <a href="https://github.com/ryanpq/do-wpc">on GitHub</a>.</p>

<h2 id="conclusion">Conclusion</h2>

<p>We now have a scalable WordPress deployment but there are additional steps we can take to ensure our new site is secure and stable.</p>

<ul>
<li><a href="https://indiareads/community/tutorials/how-to-set-up-a-firewall-using-iptables-on-ubuntu-14-04">Set up a firewall on your droplets to prevent unauthorized access</a>.</li>
<li><a href="https://indiareads/community/tutorials/initial-server-setup-with-ubuntu-14-04">Change your ssh port and restrict ssh access</a>.</li>
<li><a href="https://indiareads/community/tutorials/how-to-create-a-redundant-storage-pool-using-glusterfs-on-ubuntu-servers">Restrict access to your GlusterFS volume</a>.</li>
</ul>

    