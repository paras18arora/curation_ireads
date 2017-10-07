<link rel='stylesheet' href='digital_ocean.css'><br> <br> 
      <div><h3>Introduction</h3>

<p><a href="http://ajenti.org" target="_blank">Ajenti</a> is an open source web based control panel for Linux servers. It offers a graphical user interface to perform most of the actions required to configure and keep your server up to date. If you are familiar with <a href="https://indiareads/community/tutorials/how-to-install-webmin-on-an-ubuntu-cloud-server" target="_blank">Webmin</a>, Ajenti is designed for the same purpose, but is simpler and easier to use. Then again, due to the fact it's simpler, it does not offer as many features. If you prefer to have a simple yet solid control panel with some eye candy, Ajenti is definitely worth checking out.</p>

<img alt="Ajenti Control Panel - Dashboard" src="https://assets.digitalocean.com/articles/Ajenti_Ubuntu/img1.jpg" />

<h2>1. Connecting To Your VPS</h2>

<p>Establish a SSH connection to your virtual server and authenticate as root. If you prefer not to use root, you may also use "sudo su" to create a root shell</p>

<pre>ssh root@your-ip</pre>

Download the latest package files:

<pre>apt-get update</pre>

<h2>2. Import Keys/Add Ajenti Repository</h2>

<h3>2.1. Importing the repository key</h3>

<p>The repository key is used to validate that the package originates from the legitimate source, thus preventing the installation of possibly infected packages.</p>

<p>The following command downloads the key and automatically adds it to your system.</p>

<pre>wget <a href="http://repo.ajenti.org/debian/key" target="_blank">http://repo.ajenti.org/debian/<WBR />key</a> -O- | apt-key add -</pre>

<h3>2.2. Adding the APT repository</h3>

<p>The following command can be used to add the repository to your sources.list</p>

<pre>echo "deb http://repo.ajenti.org/ng/debian main main ubuntu" >> /etc/apt/sources.list</pre>

<p><strong>OR</strong>, if you prefer, you can also open /etc/apt/sources.list with your favourite text editor, and paste the repository url there.</p>

<pre>nano /etc/apt/sources.list</pre>

<p>Navigate to the end of the file and paste the following line</p>

<pre>deb <a href="http://repo.ajenti.org/debian" target="_blank">http://repo.ajenti.org/debian</a> main main debian</pre>

<p>Save the changes (ctrl+o) and exit (ctrl+x).</p>

<h2>3. Installing Ajenti</h2>

<p>Update the package sources and install ajenti package. </p>

<pre>apt-get update && apt-get install ajenti -y</pre>

<p>Start Ajenti by executing the following command</p>

<pre>service ajenti restart</pre>

<p>If you are using a firewall, please open port 8000 to enable access to the control panel.</p>

<h2>4. Login to Ajenti</h2>

<p>Open your web browser and navigate to <strong><a href="https://yourdomain.com:8000" target="_blank">https://yourdomain.com:8000</a></strong> or <strong><a href="https://your-ip:8000" target="_blank">https://your-ip:8000</a></strong></p>

<p>You will most likely receive a warning indicating that there is an issue with the server's certificate. This is not dangerous, it just means that the certificate used was not issued by a reliable party, as it was self-generated. If you already have a trusted certificate, you may use it instead. That is, however, outside the scope of this tutorial. You are also offered an option to disable SSL, but it is recommended to keep it enabled, as with SSL your traffic will be encrypted and login credentials are not submitted in plan text.</p>

<p><strong>The default login credentials:</strong></p>
<p>Username: root</p>
<p>Password: admin</p>

<h2>5. Configuring Ajenti</h2>

<p>Once you have logged in, the first step is to change the root user's password. To do this, navigate to the "Configure" menu.</p>

<p>Click "Change password" on root account and write a new password. </p>

<img src="https://assets.digitalocean.com/articles/Ajenti_Ubuntu/img2.jpg" alt="Change password" />

<p>You may also create a new user and select which features you would like to grant the user access to. To do this, first click "Create", then set the name by clicking "unnamed" and writing the new name, and lasty click the icon on the left from the name to set the permissions.</p>

<img src="https://assets.digitalocean.com/articles/Ajenti_Ubuntu/img3.jpg" alt="Create user" />

<p>Once done, click "SAVE" on the top of the page.</p>

<p>If you altered any other settings, also remember to apply the changes by restarting the control panel.</p>

<h2>Customising Ajenti</h2>

<p>Dashboard can be used to display a lot of useful information at once. By default, only a welcome widget is shown. You may remove widgets by grabbing from the dotted area and dragging them down.</p>

<p>You can add new widgets by clicking "Add widget", and drag them to the position you would like them to be.</p>

<img alt="Ajenti Control Panel - Dashboard" src="https://assets.digitalocean.com/articles/Ajenti_Ubuntu/img4.jpg" />

<h2>Plugins</h2>

<p>Most of Ajenti's functionality is offered by the plugins. There are already many plugins available, and the number of plugins available increases as time passes.</p>

<p>You can view and install more plugins on the "Plugins" page. Some of the plugins are disabled by default, most likely because the application they are used to control is not installed. If you later install an application which Ajenti has a plugin for, restart the Ajenti control panel and it should be enabled.</p>

<h2>Setting Up a Website</h2>

<p>Ajenti control panel can be used to install and configure your web server, and finally upload the website.</p>

<p>LAMP (Linux Apachhe MySQL PHP) is the most common web server setup for Linux at the moment. There are other alternatives, such as Nginx and Lighttpd, but only Apache will be covered in this tutorial.</p>

<h3>Installing the web server</h3>

<ol>
	<li>Click "Packages" in the menu and then select the "Search" tab.</li>
	<li>Type "apache2" to the text field and click "Search". Find "apache2" on the list and click the tick icon to select the package. You should choose apache2:amd64 if your server is 64-bit, otherwise apache:i386.</li>
	<li>Type "php5" to the text field and click "Search". Find "php5:all" on the list and click the tick icon.</li>
	<li>Type "mysql-server" to the text field and click "Search". Find "mysql-server:all" on the list and click the tick icon.</li>
	<li>Install the packages by clicking the "Apply" button. This will open a new tab in the control panel, and you should write y and press enter when asked in the new tab.</li>
	<li>You will be asked to set the password for the root (admin) user of MySQL. Write a password and click enter, and do the same when you are asked to confirmed the password.</li>
	<li>Once the installation is complete, the terminal tab will be automatically closed.</li>
	<li>Your website is now live and may be accessed by using your IP or domain on a web browser.</li>
</ol>

<h3>Restart Ajenti</h3>

<p>The web server is now installed, but not shown by Ajenti, as the plugins are updated upon restart.</p>

<ol>
	<li>Click "Configure" on the menu</li>
	<li>Click "Restart" to restart Ajenti control panel and enable the plugin</li>
</ol>

<p>You will have to log in again, and then Apache will appear on the menu, where you can start, stop, reload, restart and configure Apache.</p>

<h3>Upload your website</h3>

<p>Ajenti has a file browser, but it is much more effective and easier to use SFTP to upload your files. FileZilla is the most commonly used client, but you may use any SFTP client you like.</p>

<ol>
	<li>Connect to sftp://your-ip, and enter the username and password of the root user when asked.</li>
	<li>Navigate to folder /var/www</li>
	<li>Delete index.html from the directory</li>
	<li>Drag the files from your local file browser (Explorer) to the remote folder on Filezilla, and wait for all upload to finish.</li>
</ol>

<p>In this tutorial, we will upload a test file called <strong>info.php</strong> to check that both Apache and PHP are installed and working.</p>

<p>Contents of index.php:</p>
<pre>
&lt?php
phpinfo();
?>
</pre>

<p>You may use any text editor on your local computer, save it as info.php and upload it to the web root (/var/www), or alternatively you can use the command line like shown below.</p>

<pre>nano /var/www/index.php</pre>

<p>Once the file is created and the editor opens up, paste the php code shown above, press <strong>ctrl + x</strong> and when you are asked whether to save the file or not, press <strong>y</strong> to confirm.</p>

<p>Now that the file has been saved, navigate to <a href="http://your-ip" target="_blank">http://your-ip</a>, and you should see a white page with text "Yay, the web server works!". If this is the case, you have succeeded installing the web server. If the page does not respond, or the text is not shown, make sure you followed all the steps, and that the server firewall allows connections on port 80 and 443.</p>

<p>You may update the website by uploading new files or editing the existing ones at any times using SFTP or SSH client.</p>

<h2>Screenshots</h2>

<h3>Filesystems</h3>

<img src="https://assets.digitalocean.com/articles/Ajenti_Ubuntu/img5.jpg" alt="Filesystems" />

<h3>Cron jobs</h3>

<img src="https://assets.digitalocean.com/articles/Ajenti_Ubuntu/img6.jpg" alt="Cron jobs" />

<h3>Package management</h3>

<img src="https://assets.digitalocean.com/articles/Ajenti_Ubuntu/img7.jpg" alt="Package management" />

<h3>Services</h3>

<img src="https://assets.digitalocean.com/articles/Ajenti_Ubuntu/img8.jpg" alt="Services" />

<h3>Processes</h3>

<img src="https://assets.digitalocean.com/articles/Ajenti_Ubuntu/img9.jpg" alt="Processes" />

 <div class="author">Submitted by: <a href="http://ruonavaara.fi">Lassi</a></div></div>
    