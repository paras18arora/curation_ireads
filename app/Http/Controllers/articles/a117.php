<link rel='stylesheet' href='digital_ocean.css'><br> <br> 
      <div><h3>About Webmin</h3>

<p>Webmin is a web-based interface for system administration for Unix.  Using any modern web browser, you can setup user accounts, Apache, DNS, file sharing, and much more. Webmin removes the need to manually edit Unix configuration files, like /etc/passwd, and lets you manage a system from the console or remotely.</p>

<h2>Step One—Root Login</h2>

<p>Once you know your IP address and root password, login as the main user, root.</p>

<pre>ssh root@123.45.67.890</pre>

<h2>Step Two—Add Webmin to APT repository</h2>

<p>Edit the /etc/apt/sources.list file on your system and add the following lines.</p>

<p>Open the file with nano:</p>

<pre>nano /etc/apt/sources.list</pre>

<p>Press [Page Down] button on the keyboard to reach to the end of file. (or press Left ALt+/)</p>

<p>Then paste these two lines below:</p>

<pre>
deb http://download.webmin.com/download/repository sarge contrib
deb http://webmin.mirror.somersettechsolutions.co.uk/repository sarge contrib
</pre>

<p>Save changes with CTRL+O and exit with CTRL+X.</p>

<h2>Step Three—Install Webmin’s GPG key</h2>

<p>You should also fetch and install Webmin’s GPG key in which the repository is signed and with the commands:</p>

<pre>
cd /root
wget http://www.webmin.com/jcameron-key.asc
apt-key add jcameron-key.asc
</pre>

<h2>Step Four—Install Webmin</h2>

<p>You will now be able to install with the commands:</p>

<pre>
apt-get update
apt-get install webmin
</pre>

<h2>Step Five—Login to Webmin</h2>

<p>Open your browser and go to http://your_droplet_ip:10000/</p>

<img src="https://assets.digitalocean.com/tutorial_images/OwJL0Bj.png?1" alt="login" />

<p>You can now login as root with your root password, or as any user who can use sudo to run commands as root.</p>

<img src="https://assets.digitalocean.com/tutorial_images/7xJr8Gr.png?1" alt="account" /></div>
    