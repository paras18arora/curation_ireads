<link rel='stylesheet' href='digital_ocean.css'><br> <br> 
      <div><h2>Install MongoDB on Ubuntu 12.04</h2>

<p>MongoDB is a document database used commonly in modern web applications. This tutorial should help you setup a virtual private server to use as a dedicated MongoDB server for a production application environment.</p>

<h3>Step 1 -- Create a Droplet</h3>

<p>This one's easy. Once you're done, go ahead and `ssh` in.</p>

<p>N.B. :: It is recommended that you configure `ssh` and `sudo` like <a href="https://indiareads/community/articles/initial-server-setup-with-ubuntu-12-04" target="_blank">this</a></p>

<h3>Step 2 -- Create the Install Script</h3>

<p>The MongoDB install process is simple enough to be completed with a Bash script. Copy the following into a new file named `mongo_install.bash` in your home directory:</p>

<pre>
apt-key adv --keyserver keyserver.ubuntu.com --recv 7F0CEB10
echo "deb http://downloads-distro.mongodb.org/repo/ubuntu-upstart dist 10gen" | tee -a /etc/apt/sources.list.d/10gen.list
apt-get -y update
apt-get -y install mongodb-10gen
</pre>

<p>Here's an explanation of each line in the script:</p>

<ul>
<li>The `apt-key` call registers the public key of the custom 10gen MongoDB aptitude repository</li>
<li>A custom 10gen repository list file is created containing the location of the MongoDB binaries</li>
<li>Aptitude is updated so that new packages can be registered locally on the Droplet</li>
<li>Aptitude is told to install MongoDB</li>
</ul>

<p><strong>TIP:</strong> At any time, to change to your home directory, simply execute `cd`</p>

<h3>Step 3 -- Run the Install Script</h3>

<p>Execute the following from your home directory:</p>

<pre>
$ sudo bash ./mongo_install.bash
</pre>

<p>If everything is successful, you should see the output contain a PID of the newly started MongoDB process:</p>

<pre>
mongodb start/running, process 2368
</pre>

<h3>Step 4 -- Check It Out</h3>

<p>By default with this install method, MongoDB should start automatically when your Droplet is booted. This means that if you need to reboot your Droplet, MongoDB will start right back up.</p>

<p>To start learning about the running `mongod` process, run the following command:</p>

<pre>
$ ps aux | grep mongo
</pre>

<p>One line of the output should look like the following:</p>

<pre>
mongodb    569  0.4  6.4 627676 15936 ?        Ssl  22:54   0:02 /usr/bin/mongod --config /etc/mongodb.conf
</pre>

We can see the...

<ul>
<li>User: `mongodb`</li>
<li>PID: `569`</li>
<li>Command: `/usr/bin/mongod --config /etc/mongodb.conf`</li>
<li>Config File: `/etc/mongodb.conf`</li>
</ul>

<h3>Resources</h3>

<ul>
<li><a href="http://docs.mongodb.org/manual/tutorial/install-mongodb-on-debian-or-ubuntu-linux/" target="_blank">http://docs.mongodb.org/manual/tutorial/install-mongodb-on-debian-or-ubuntu-linux/</a></li>
<li><a href="https://indiareads/community/articles/introduction-to-ssh-on-ubuntu" target="_blank">https://indiareads/community/articles/introduction-to-ssh-on-ubuntu</a></li>
</ul>

<div class="author">By Etel Sverdlov</div></div>
    