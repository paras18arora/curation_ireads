<link rel='stylesheet' href='digital_ocean.css'><br> <br> 
      <h2 id="getting-started">Getting Started</h2>

<hr />

<h3 id="introduction">Introduction</h3>

<hr />

<p>Xibo is a digital signage platform that is released under an open-source license. If you are reading this and wondering what digital signage is – think about the information screen at your local Doctor's office or advertising in various shops.</p>

<p>The Xibo system is made up from two components: First of all there is the server, which is used to create and schedule the layouts; and then there is the client, which downloads all of the relevant information about the currently scheduled layout and displays this on the screen.</p>

<p><strong>Considerations that should be made are mentioned below:</strong></p>

<ol>
<li><p>Long term support - In a production environment, having long-term support available is really important. For this, I have chosen to use <strong>Ubuntu Server 12.04</strong>, which is supported until 2017.</p></li>
<li><p>PHP settings - During the installation process, I will discuss some settings that need to be tweaked for the system to work as well as possible.</p></li>
<li><p>File storage location - When set, the media files path should <strong>not</strong> be within the web server root folder structure.</p></li>
</ol>

<h2 id="installation-instructions">Installation instructions</h2>

<hr />

<h3 id="prerequisites">Prerequisites</h3>

<hr />

<ol>
<li><p>An Ubuntu 12.04 droplet</p></li>
<li><p>Apache 2 web server</p></li>
<li><p>PHP5</p></li>
<li><p>MySQL server</p></li>
<li><p>PHP5 GD plugin</p></li>
<li><p>PHP5 MySQL plugin</p></li>
<li><p>PHP5 crypt plugin</p></li>
</ol>

<h2 id="updating-the-vps">Updating the VPS</h2>

<hr />

<p>When using Ubuntu, <code>apt-get</code> can be used to update the package repositories and also install any updates:</p>
<pre class="code-pre "><code langs="">sudo apt-get update
sudo apt-get upgrade
</code></pre>
<p>When asked, have a look at the changes that <code>apt-get</code> is suggesting and accept them.</p>

<h3 id="install-the-packages">Install the packages</h3>

<hr />

<p>On the command line run the following commands. When asked, accept any changes that need to be made. Take care when setting the mysql root user, which will happen as part of the installation process.</p>
<pre class="code-pre "><code langs="">sudo apt-get install apache2 mysql-server php5 php5-gd php5-mysql php5-mcrypt
</code></pre>
<h3 id="download-and-unpack-the-server-files">Download and unpack the server files</h3>

<hr />

<p>The latest stable version of Xibo is 1.4.2. Running the following commands will download the server, unpack the files, move them to the relevant location and then change the ownership of the folders so that the web server has the access it requires.</p>
<pre class="code-pre "><code langs="">cd ~
wget https://launchpad.net/xibo/1.4/1.4.2/+download/xibo-server-142.2.tar.gz
tar xvzf xibo-server-142.2.tar.gz
sudo mv xibo-server-142 /var/www/xibo-server
sudo chown www-data:www-data -R /var/www/xibo-server
</code></pre>
<p>Now, we need to provide a location for the media files to be stored in:</p>
<pre class="code-pre "><code langs="">sudo mkdir /media/xibo-library
sudo chown www-data:www-data -R /media/xibo-library
</code></pre>
<h3 id="update-the-php-configuration-file">Update the PHP configuration file</h3>

<hr />

<p>The default configuration of PHP is quite conservative when it comes to the time scripts can execute for and the size of files that can be uploaded. To make the needed changes, we will use the nano text editor:</p>
<pre class="code-pre "><code langs="">sudo nano /etc/php5/apache2/php.ini
</code></pre>
<p>Once the file has been opened in nano, you can use the ctrl-w key combination to search for the following items:</p>

<ol>
<li><p><code>upload_max_filesize</code></p></li>
<li><p><code>post_max_size</code></p></li>
<li><p><code>max_execution_time</code></p></li>
</ol>

<p>If you wish to be able to upload files of size up to 30MB and wish to be able to upload for a maximum of ten minutes, then make sure the lines look like the following:</p>
<pre class="code-pre "><code langs="">upload_max_filesize = 30MB
post_max_size = 30MB
max_execution_time = 600
</code></pre>
<p>To save the file, press Ctrl-O and press Enter. Then exit by pressing Ctrl-X. Now restart the webserver so that these changes can be loaded:</p>
<pre class="code-pre "><code langs="">sudo /etc/init.d/apache2 restart
</code></pre>
<h2 id="complete-the-installation-using-the-web-interface">Complete the installation using the web interface</h2>

<hr />

<p>The command line work has now been completed and all that remains is to visit the web-based installer to completely set up the xibo server. Open up a web browser and visit the following address:</p>
<pre class="code-pre "><code langs="">http://IP_ADDRESS_OR_HOSTNAME_OF_YOUR_DROPLET/xibo-server/
</code></pre>
<p>This will then open up the install.php page:</p>

<p><img src="https://assets.digitalocean.com/articles/Xibo/image1.jpg" /></p>

<p>The second step of the installer will verify that all of the dependencies have been installed successfully. Assuming success select <strong>Next</strong>.</p>

<p>The third step of the installer asks if you would like to use an existing database or create a new one. For new installations, I would always select to create a new database.</p>

<p><img src="https://assets.digitalocean.com/articles/Xibo/image3.jpg" /></p>

<p>The fourth step is for you to input the settings of your mysql server. The root password is the one that you set earlier in this process and the xibo username and password are accounts that the installer will create for xibo to access this database.</p>

<p><img src="https://assets.digitalocean.com/articles/Xibo/image4.jpg" /></p>

<p>Step five should complete quickly and shows that the database schema has been imported.</p>

<p><img src="https://assets.digitalocean.com/articles/Xibo/image5.jpg" /></p>

<p>The sixth step asks for a password to be set for the <code>xibo_admin</code> administrative user. When entered, the installer at the next screen will verify that the password was entered and was satisfactory.</p>

<p><img src="https://assets.digitalocean.com/articles/Xibo/image6.jpg" /></p>

<p>The seventh (and final) step is asking for us to provide the <em>library location</em> for the media files and a <em>server key</em>. This server key is important and is used so that the client can authenticate with the server. Without the correct server key, the client will not be able to download data from the server. Finally, decide whether anonymous statistics should be sent to the project maintainers<br />
or not.</p>

<p><img src="https://assets.digitalocean.com/articles/Xibo/image7.jpg" /></p>

<p>Now, we will see two screens in a row that confirm that settings are correct and that the system has been successfully installed. When looking at this second confirmation, go ahead and open up the login page.</p>

<p><img src="https://assets.digitalocean.com/articles/Xibo/image8.jpg" /></p>

<h2 id="client-and-usage">Client and usage</h2>

<hr />

<p>The easiest way to test the server is to login and create some layouts. The usage of the server is beyond the scope of this tutorial and further information can be found <a href="http://xibo.org.uk/docs/">here</a></p>

<h3 id="staying-on-the-cutting-edge">Staying on the cutting edge</h3>

<hr />

<p>Being open-source, development of Xibo is done in the public eye. If you wish to help out with the development of the project, whether via programming new features, fixing bugs, providing translations or by testing new code, information can be found <a href="http://www.xibo.org.uk">here</a>.</p>

<p>Using the steps below, you will be able to install any of the public <code>bzr</code> branches, which are available on <a href="https://code.launchpad.net/xibo">launchpad</a></p>

<h2 id="considerations">Considerations</h2>

<hr />

<ol>
<li><p>Like all pre-release software, the chance of bugs are higher than in released software. Without thorough testing, software will never be able to improve. That said, please do not use these releases in production!</p></li>
<li><p>With some of the <code>bzr</code> branches, some functionality could be broken. Use a <code>bzr</code> branch of the code to test specific things.</p></li>
<li><p>Even numbered releases (1.0, 1.2, 1.4) are considered stable and odd numbered releases (1.1,1.3,1.5) are development releases. These<br />
releases tend to be more stable than using a <code>bzr</code> branch and are available from the <a href="https://launchpad.net/xibo/+download">download</a> page and can be installed in the same way as shown above.</p></li>
</ol>

<h3 id="installing-from-a-bzr-branch">Installing from a <code>bzr</code> branch</h3>

<hr />

<p>Firstly, we will need to install the <code>bzr</code> tools. At the command line enter:</p>
<pre class="code-pre "><code langs="">sudo apt-get install bzr
</code></pre>
<p>Secondly, at the <a href="https://code.launchpad.net/xibo">code</a> page, select a branch that you are interested in and click on the <em>lp:</em> link. Clicking on the link for 1.6, a page opens with the following <code>bzr</code>-branch link. To take a copy, go back to the command line and enter:</p>
<pre class="code-pre "><code langs="">cd ~
bzr branch lp:xibo
</code></pre>
<p>After a minute or two, a new folder will be available in your home folder and this can then be copied into the web-server root:</p>
<pre class="code-pre "><code langs="">sudo cp xibo/server /var/www/xibo-server-bzr -R
sudo chown www-data:www-data -R /var/www/xibo-server-bzr
</code></pre>
<p>Now, we need to provide a location for the media files to be stored in:</p>
<pre class="code-pre "><code langs="">sudo mkdir /media/xibo-library-bzr
sudo chown www-data:www-data -R /media/xibo-library-bzr
</code></pre>
<p>Once these steps have been completed, you can simply visit the new web-address like above, but replace <em>xibo-server</em> with <em>xibo-server-bzr</em>.<br />
When asked about a database, create a new database with a different name to the stable install described earlier.</p>

<h3 id="final-thoughts">Final thoughts</h3>

<hr />

<p>Hopefully this tutorial has helped with installing the xibo server. If you wish to allow larger files to be uploaded in the future, simply follow the <em>php.ini</em> instructions that are listed earlier in this guide.</p>

<div class="author">Article Submitted by: <a href="http://www.mattmole.co.uk/wordpress/?p=10">Matt Holder</a></div>

    