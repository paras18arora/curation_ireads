<link rel='stylesheet' href='digital_ocean.css'><br> <br> 
      <p></p><div class="code-label notes-and-warnings warning" title="Warning">Warning</div><span class="warning">
This guide is targeted at Chef 11.  The Chef 12 platform introduces some significant configuration differences.  You can find a guide on how to set up a Chef 12 server, workstation, and node <a href="https://indiareads/community/tutorials/how-to-set-up-a-chef-12-configuration-management-system-on-ubuntu-14-04-servers">here</a>.<br /></span>

<h3 id="introduction">Introduction</h3>

<hr />

<p>As your organizational structure grows and the separate components necessary to manage your environment expand, administering each server and service can become unmanageable.</p>

<p>Configuration management solutions are designed to simplify the management of systems and infrastructure.  The goal of configuration management tools are to allow you to manage your infrastructure as a code base.  <strong>Chef</strong> is a configuration management solution that allows you to manage large numbers of servers easily.</p>

<p>In a <a href="https://indiareads/community/articles/how-to-understand-the-chef-configuration-environment-on-a-vps">previous guide</a>, we discussed the general structure of the Chef components and the way the system operates on a conceptual level.  We went over some key terminology and the relationship between many different components.</p>

<p>In this guide, we will work to install a small Chef 11 setup.  This will be one Chef server used to store configuration data and administer access rights.  This will serve as a hub for our other machines.</p>

<p>We will also install a workstation that will allow us to interact with our server and build our configuration policies.  This is where we will do the work to manage our infrastructure environment.</p>

<p>Finally, we will bootstrap a node, which will represent one of the servers in our organization that will be managed through Chef.  We will do this using the server and workstation that we configured.</p>

<p>All three of these machines will be using Ubuntu 12.04 x86_64 VPS instances for simplicity's sake.  We will be targeting the Chef 11 release as it is stable and well tested.</p>

<h2 id="server-installation">Server Installation</h2>

<hr />

<p>The first component that we need to get online is the Chef server.  Because this is central to the communication of our other components, it needs to be available for our other machines to complete their setup.</p>

<p>Before doing this, it is important to set up a domain name for your Chef server to resolve requests correctly.  You can see our guide on getting a <a href="https://digitalocean.com/community/articles/how-to-set-up-a-host-name-with-digitalocean">domain name set up with IndiaReads</a> here.</p>

<p>If you do not have a domain name, you will need to edit the <code>/etc/hosts</code> file on each of the VPS instances that you will be using, so that they can all resolve the Chef server by name.  If you <em>do</em> have a domain name, this should only be necessary on the VPS you will be using as the Chef server.  You can do this by typing this on the VPS you will use as the Chef server:</p>

<pre>
sudo nano /etc/hosts
</pre>

<p>Inside, add the IP address of this computer and then the name you would like to use to connect to the server.  You can then add a short name after that.  Something like this:</p>

<pre>
<span class="highlight">111.222.333.444     chef.domain.com   chef</span>
</pre>

<p>Change the <code>111.222.333.444</code> to your Chef server's IP address and change the other two values to whatever you'd like to use to refer to your server as.  Add this line to point to your Chef server to this file on each of the machines you plan to use if you are not using a domain name.</p>

<p>You can check that this is setup correctly by typing:</p>

<pre>
hostname -f
</pre>

<p>This should give you the name that is used to reach this server.</p>

<p>You can get the chef server package by visiting <a href="http://www.getchef.com/chef/install/">this page</a> in your web browser.</p>

<p>Click on the "Chef Server" tab and then select the menus that match your operating system:</p>

<p><img src="https://assets.digitalocean.com/articles/chef_install/server_operating.png" alt="Chef server select operating system" /></p>

<p>Select the most recent version of the Chef 11 server available to you on the right-hand side:</p>

<p><img src="https://assets.digitalocean.com/articles/chef_install/server_newest.png" alt="Chef server newest" /></p>

<p>You will be presented with a link to a deb file.  Right-click on this and select the option that is similar to "copy link location".</p>

<p>In the VPS instance that you will be using as the server, change to your user's home directory and use the <code>wget</code> utility to download the deb.  At the time of this writing, the most recent link is this:</p>
<pre class="code-pre "><code langs="">cd ~
wget https://opscode-omnibus-packages.s3.amazonaws.com/ubuntu/12.04/x86_64/chef-server_11.0.10-1.ubuntu.12.04_amd64.deb
</code></pre>
<p>This will download the installation package that you can then install like this:</p>
<pre class="code-pre "><code langs="">sudo dpkg -i chef-server*
</code></pre>
<p>This will install the server component on this machine.</p>

<p>It prints to the screen afterwards that you should run this next command to actually configure the service around your specific machine.  This will configure everything automatically:</p>
<pre class="code-pre "><code langs="">sudo chef-server-ctl reconfigure
</code></pre>
<p>Once this step is complete, the server should be up and running.  You can access the web interface immediately by typing <code>https://</code> followed by your server's domain name or IP address.</p>

<pre>
https://<span class="highlight">server_domain_or_IP</span>
</pre>

<p>Because the SSL certificates were signed by an authority that your browser does not recognize by default, you will see a warning message appear:</p>

<p><img src="https://assets.digitalocean.com/articles/chef_install/ssl_warning.png" alt="Chef SSL warning" /></p>

<p>Click the "Proceed anyway" button to bypass this screen and access the login screen.  It will look something like this:</p>

<p><img src="https://assets.digitalocean.com/articles/chef_install/login.png" alt="Chef server login screen" /></p>

<p>The default login credentials are as follows:</p>

<pre>
Default Username: <span class="highlight">admin</span>
Default Password: <span class="highlight">p@ssw0rd1</span>
</pre>

<p>When you log in for the first time, you will be immediately prompted to change your password.  Select a new password and then click on the "Save User" button on the bottom:</p>

<p><img src="https://assets.digitalocean.com/articles/chef_install/change_pw.png" alt="Chef server change pw" /></p>

<p>You have now configured the server to a point where we can leave it and begin our workstation configuration.</p>

<h2 id="workstation-installation">Workstation Installation</h2>

<hr />

<p>Our workstation computer is the VPS that we will use to create and edit the actual policies that dictate our infrastructure environments.  This machine has a copy of the Chef repo that describes our machines and services and it uploads those to the Chef server for implementation.</p>

<p>We will start by simply installing <code>git</code> for version control:</p>
<pre class="code-pre "><code langs="">sudo apt-get update
sudo apt-get install git
</code></pre>
<p>This actually has two purposes.  The obvious use is that we will be keeping our configuration under version control to track changes.  The second purpose is to temporarily cache our password with sudo so that the following command works.</p>

<p>We will now download and run the client installation script from the Chef website.  Type this command to complete all of these steps:</p>
<pre class="code-pre "><code langs="">curl -L https://www.opscode.com/chef/install.sh | sudo bash
</code></pre>
<p>Our Chef workstation component is now installed.  However it is very far from being configured.</p>

<p>The next step is to acquire the "chef-repo" directory structure for a properly formatted Chef repository from GitHub.  We can clone the structure into our home directory by typing:</p>
<pre class="code-pre "><code langs="">cd ~
git clone https://github.com/opscode/chef-repo.git
</code></pre>
<p>This will create a directory called <code>chef-repo</code> in your home directory.  This is where the entire configuration for your setup will be contained.</p>

<p>We will create a configuration directory for the Chef tools themselves within this directory:</p>
<pre class="code-pre "><code langs="">mkdir -p ~/chef-repo/.chef
</code></pre>
<p>Within this directory, we will need to put some of the authentication files from our Chef server.  Specifically, we need two private keys.</p>

<h3 id="generating-and-copying-keys-from-the-server">Generating and Copying Keys from the Server</h3>

<hr />

<p>Go back to your Chef server in your web browser:</p>

<pre>
https://<span class="highlight">server_domain_or_IP</span>
</pre>

<p>Log in using the <code>admin</code> user's credentials that you changed before.</p>

<p>Click on the "Clients" tab in the top navigation bar.  You will see two two clients called chef-validator and chef-webui:</p>

<p><img src="https://assets.digitalocean.com/articles/chef_install/client_list.png" alt="Chef server clients" /></p>

<p>Click on the "Edit" button associated with the <code>chef-validator</code> client.  Regenerate the private key by selecting that box and clicking "Save Client":</p>

<p><img src="https://assets.digitalocean.com/articles/chef_install/val_regen.png" alt="Chef regenerate key" /></p>

<p>You will be taken a screen with the newly generated values for the key file.</p>

<p><img src="https://assets.digitalocean.com/articles/chef_install/val_new_key.png" alt="Chef val new key" /></p>

<p><strong>Note:</strong> This key will only be available once, so don't click out of this page!  If you do, you will need to regenerate the key again.</p>

<p>Copy the value of the private key field (the one at the bottom).</p>

<p>On your workstation machine, change to the Chef configuration directory we created in the repo:</p>
<pre class="code-pre "><code langs="">cd ~/chef-repo/.chef
</code></pre>
<p>Open a new file for the validator key we just created:</p>
<pre class="code-pre "><code langs="">nano chef-validator.pem
</code></pre>
<p>In this file, paste the contents of the key you copied from the server's web interface (some lines have been removed for brevity here):</p>
<pre class="code-pre "><code langs="">-----BEGIN RSA PRIVATE KEY-----
MIIEowIBAAKCAQEA6Np8f3J3M4NkA4J+r144P4z27B7O0htfXmPOjvQa2avkzWwx
oP28SjUkU/pZD5jTWxsIlRjXgDNdtLwtHYABT+9Q5xiTQ37s+eeJgykQIifED23C
aDi1cFXOp/ysBXaGwjvl5ZBCZkQGRG4NIuL7taPMsVTqM41MRgbAcLCdl5g7Vkri
. . .
. . .
xGjoTVH1vBAJ7BG1RHJZlx+T9QnrK+fQu5R9mikkLHayxi13mD0C
-----END RSA PRIVATE KEY-----
</code></pre>
<p>Ensure that there are not extra blank lines above or below the key.  Save and close the file.</p>

<p>We will follow the same procedure to regenerate and save the admin user's key file.  This time, the key is for a user, so click on the "Users" tab on the top.</p>

<p>Again, click on the "Edit" button associated with the admin user, check the "Regenerate Private Key" box and click the "Save User" button:</p>

<p><img src="https://assets.digitalocean.com/articles/chef_install/admin_regen.png" alt="Chef admin user regen" /></p>

<p>Copy the Private key value on the next screen.  Once again, <strong>this will not be shown again</strong>, so copy it correctly the first time.</p>

<p>Back on your workstation computer, you will need to create another file for the admin user in the same directory:</p>
<pre class="code-pre "><code langs="">nano admin.pem
</code></pre>
<p>Paste the contents of the key you copied from the server's interface (again, this is shortened):</p>
<pre class="code-pre "><code langs="">-----BEGIN RSA PRIVATE KEY-----
MIIEpAIBAAKCAQEA/apu0+F5bkVtX6qGYcfoA6sIW/aLFUEc3Bw7ltb50GoZnUPj
0Ms1N1Rv/pdVZXeBa8KsqICAhAzvwSr0H9j+AoURidbkLv4urVC9VS4dZyIRfwvq
PGvAKop9bbY2WJMs23SiEkurEDyfKaqXKW687taJ9AKbH2yVx0ArPI2RwS3Sze3g
. . .
. . .
VTkNpg3lLRSGbQkvRUP6Kt20erS2bfETTtH6ok/zW4db8B/vnBlcZg==
-----END RSA PRIVATE KEY-----
</code></pre>
<p>Verify that there are no extra lines above or below the pasted key lines.  Save and close the file.</p>

<h3 id="configure-the-knife-command">Configure the Knife Command</h3>

<hr />

<p>We now have to configure the <code>knife</code> command.  This command is the central way of communicating with our server and the nodes that we will be configuring.  We need to tell it how to authenticate and then generate a user to access the Chef server.</p>

<p>Luckily, we've been laying the groundwork for this step by acquiring the appropriate credential files.  We can start the configuration by typing:</p>
<pre class="code-pre "><code langs="">knife configure --initial
</code></pre>
<p>This will ask you a series of questions. We will go through them one by one:</p>

<pre>
WARNING: No knife configuration file found
Where should I put the config file? [/home/<span class="highlight">your_user</span>/.chef/knife.rb]
</pre>

<p>The values in the brackets ([]) are the default values that knife will use if we do not select a value.</p>

<p>We want to place our knife configuration file in the hidden directory we have been using:</p>

<pre>
/home/<span class="highlight">your_user</span>/chef-repo/.chef/knife.rb
</pre>

<p>In the next question, type in the domain name or IP address you use to access the Chef server.  This should begin with <code>https://</code> and end with <code>:443</code>:</p>

<pre>
https://<span class="highlight">server_domain_or_IP</span>:443
</pre>

<p>You will be asked for a name for the new user you will be creating.  Choose something descriptive:</p>

<pre>
Please enter a name for the new user: [root] <span class="highlight">station1</span>
</pre>

<p>It will then ask you for the admin name.  This you can just press enter on to accept the default value (we didn't change the admin name).</p>

<p>It will then ask you for the location of the existing administrators key.  This should be:</p>

<pre>
/home/<span class="highlight">your_user</span>/chef-repo/.chef/admin.pem
</pre>

<p>It will ask a similar set of questions about the validator.  We haven't changed the validator's name either, so we can keep that as <code>chef-validator</code>.  Press enter to accept this value.</p>

<p>It will then ask you for the location of the validation key.  It should be something like this:</p>

<pre>
/home/<span class="highlight">your_user</span>/chef-repo/.chef/chef-validator.pem
</pre>

<p>Next, it will ask for the path to the repository.  This is the <code>chef-repo</code> folder we have been operating in:</p>

<pre>
/home/<span class="highlight">your_user</span>/chef-repo
</pre>

<p>Finally, it will ask you to select a password for your new user.  Select anything you would like.</p>

<p>This should complete our knife configuration.  If we look in our <code>chef-repo/.chef</code> directory, we should see a knife configuration file and the credentials of our new user:</p>
<pre class="code-pre "><code langs="">ls ~/chef-repo/.chef
</code></pre>
<hr />
<pre class="code-pre "><code langs="">admin.pem  chef-validator.pem  knife.rb  station1.pem
</code></pre>
<h3 id="cleaning-up-and-testing-the-workstation">Cleaning up and Testing the Workstation</h3>

<hr />

<p>Our configuration for our workstation is almost complete.  We need to do a few things to clean up and verify that our connections work.</p>

<p>First, we should get our Chef repository under version control.  Because Chef configuration operates as source code, we can handle it in the same way as we would with the files for any program.</p>

<p>First, we need to initialize our git name and email.  Type:</p>

<pre>
git config --global user.email "<span class="highlight">your_email@domain.com</span>"
git config --global user.name "<span class="highlight">Your Name</span>"
</pre>

<p>Since our "chef-repo" directory structure was pulled straight from GitHub, it is under git version control already.</p>

<p>However, we do not want to include the "chef-repo/.chef" directory in this version control.  This contains our private keys and the knife configuration file.  They do not have anything to do with our infrastructure we want to design.</p>

<p>Add this directory to the ignore list by opening the <code>.gitignore</code> file:</p>
<pre class="code-pre "><code langs="">nano ~/chef-repo/.gitignore
</code></pre>
<p>At the bottom of the file, type <code>.chef</code> to include the entire directory:</p>

<pre>
.rake_test_cache

###
# Ignore Chef key files and secrets
###
.chef/*.pem
.chef/encrypted_data_bag_secret
<span class="highlight">.chef</span>
</pre>

<p>Save and close the file.</p>

<p>Now, we can commit our current state (which probably won't have any changes beside the <code>.gitignore</code> file we just modified) by typing:</p>
<pre class="code-pre "><code langs="">git add .
git commit -m 'Finish configuring station1'
</code></pre>
<p>We also want to make sure that our user uses the version of Ruby packaged with our Chef installation.  Otherwise, calls made by Chef could be interpreted by the system's Ruby installation, which may be incompatible with the rest of our tools.</p>

<p>We can just modify our path by adding a line to the bottom of our <code>.bash_profile</code> file.</p>

<p>Type this in to add the line:</p>
<pre class="code-pre "><code langs="">echo 'export PATH="/opt/chef/embedded/bin:$PATH"' >> ~/.bash_profile
</code></pre>
<p>Now, we can implement these changes into our current environment by typing:</p>
<pre class="code-pre "><code langs="">source ~/.bash_profile
</code></pre>
<p>We can test whether we can connect successfully with the Chef server by requesting some information from the server using the knife command.</p>

<p>This will return a list of all of our users:</p>
<pre class="code-pre "><code langs="">knife user list
</code></pre>
<hr />
<pre class="code-pre "><code langs="">admin
station1
</code></pre>
<p>If this is successful, then our workstation can successfully communicate with our server.</p>

<h2 id="bootstrapping-a-client-node">Bootstrapping a Client Node</h2>

<hr />

<p>Now that we have the Chef server and a workstation online, we can try to bootstrap a Chef client on a sample node.  We will use another Ubuntu instance.</p>

<p>The bootstrapping process involves setting up Chef client on a node.  Chef client is a piece of software that communicates with the server in order to receive directions for its own configuration.  The client then brings the node it is installed on in-line with the policy given to it by the server.</p>

<p>This process will simply configure our new VPS instance to be under the umbrella of our Chef management system.  We can then configure it however we would like by creating policies on our workstation and uploading them to our server.</p>

<p>To complete this process, we only need to know three pieces of information about the VPS we want to install the client software on:</p>

<ul>
<li>IP address or domain name</li>
<li>Username (accessible through SSH and with sudo privileges)</li>
<li>Password</li>
</ul>

<p>With these pieces of information, we can install the appropriate packages by using our knife tool on our workstation.</p>

<p>You want to type a command that looks like this:</p>

<pre>
knife bootstrap <span class="highlight">node_domain_or_IP</span> -x <span class="highlight">username</span> -P <span class="highlight">password</span> -N <span class="highlight">name_for_node</span> --sudo
</pre>

<p>Let's break this down a bit.  The domain name/IP address tells knife which server to connect to.  The username and password provide the login credentials.</p>

<p>If the user you are using is not root, then the <code>--sudo</code> option is necessary in order for the bootstrapping process to successfully install software on the remote computer.  It will prompt you for the password once you log in to use the sudo command.</p>

<p>The name for the node is a name that you select that is used internally by Chef.  This is how you will refer to this machine when crafting policies and using knife.</p>

<p>After the command is run, the client software will be installed on the remote node.  It will be configured to communicate with the Chef server to receive instructions.</p>

<p>We can query our list of clients by typing:</p>
<pre class="code-pre "><code langs="">knife client list
</code></pre>
<hr />
<pre class="code-pre "><code langs="">chef-validator
chef-webui
client1
</code></pre>
<p>We can see the two clients that are configured by default during the Chef server installation (chef-validator and chef-webui), as well as the client we just created.</p>

<p>You can just as easily set up other nodes to bring them under configuration control of your Chef system.</p>

<h2 id="conclusion">Conclusion</h2>

<hr />

<p>You should now have a Chef server, a separate workstation to create your configurations, and an example node.</p>

<p>We have not done any actual configuration of the node through Chef at this point, but we are set up to begin this process.  In future tutorials, we will discuss how to implement policies and create recipes and cookbooks to manage your nodes.</p>

<div class="author">By Justin Ellingwood</div>

    