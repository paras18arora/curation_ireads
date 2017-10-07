<link rel='stylesheet' href='digital_ocean.css'><br> <br> 
      <div><h3>About Virtualmin</h3>

<p>Virtualmin is a Webmin module which allows for extensive management of (multiple) virtual private servers. You will be able to manage Apache, Nginx, PHP, DNS, MySQL, PostgreSQL, mailboxes, FTP, SSH, SSL, Subversion/Git repositories and many more.</p>

<p>In this tutorial, we will be installing the GPL (free) edition of Virtualmin on a freshly created VPS (droplet).</p>

<h2>Prerequisites</h2>

<ul><li>Virtualmin highly recommends using a freshly installed server to prevent conflicts, assuming you just created a new VPS, this should be all good.</li>

<li>Confirm that your VPS has a fully qualified domain name set as hostname. An example of a fully qualified domain name is "<a href="http://myserver.example.com" target="_blank">myserver.example.com</a>" or "<a href="http://example.com" target="_blank">example.com</a>".

Make sure that the domain name points to your server's IP address.

Use the following command to check your current hostname.

<pre>hostname -f</pre>

And use the following command to change your hostname if necessary.

<pre>hostname <a href="http://myserver.example.com" target="_blank">myserver.example.com</a></pre></li></ul>


		

<h2>Login as Root</h2>

<p>Grab the IP address of your droplet from the IndiaReads control panel and use SSH to login as root.</p>

<pre>ssh <a href="mailto:root@123.45.67.89" target="_blank">root@123.45.67.89</a></pre>

<h2>Downloading the Install Script</h2>

<p>Virtualmin provides an install script which allows for an easy installation. Use the following command to download the script to your root directory.</p>

<pre>wget <a href="http://software.virtualmin.com/gpl/scripts/install.sh" target="_blank">http://software.virtualmin.<WBR />com/gpl/scripts/install.sh</a> -O /root/virtualmin-install.sh</pre>

<p>You should expect to see something like this when it's finished:</p>

<pre>2013-07-06 11:03:57 (129 KB/s) - `/root/virtualmin-install.sh' saved [45392/45392]</pre>		<h2>Running the Install Script</h2>
		Now it's time to run the script we just downloaded.<pre>sh /root/virtualmin-install.sh</pre>

<p>This will start the installation wizard. It will start with a short disclaimer, after accepting it the installation will begin.</p>


<h2>Accessing Virtualmin</h2>
		
<p>When the install script has finished installing, you can reach Virtualmin with the following URL:</p>

<p><strong><a href="https://myserver.example.com:10000/" target="_blank">https://myserver.example.com:<WBR />10000/</a></strong></p>
		
<p>There you can login with your root username and password. Once you are logged in the "Post-Installation Wizard", it will begin to configure your Virtualmin installation.</p>

<h2>Post-Installation Wizard</h2>

<p>This wizard is pretty self-explanatory, we'll cover some of the steps with some additional information.</p>

<p><strong>Memory use</strong></p>

<ul><li>Preload Virtualmin libraries?
This will make your Virtualmin UI faster, use this when you are going to use the UI extensively, the UI is very usable without it.</li>

<li>Run email domain lookup server?
If fast e-mail is important to you and you have the spare RAM then it's recommended to enable this.</li>
</ul>

<p><strong>Virus scanning</strong></p>

		<ul>
			<li>Run ClamAV server scanner? This is explained pretty well on the page, if your server receives a lot of e-mails then it's beneficial to enable it.</li>
		</ul>
		
<p><strong>Note:</strong>
If you are installing Virtualmin on a 512MB VPS and you have just enabled ClamAV server scanner in the step above, then it is very likely that you run accros this error:</p>

<pre>
A problem occurred testing the ClamAV server scanner :
ERROR: Can't connect to clamd: No such file or directory

----------- SCAN SUMMARY -----------
Infected files: 0
Time: 0.000 sec (0 m 0 s)
		</pre>
		
<p>The reason why you get this error is because your VPS is running out of RAM... you can choose to upgrade your RAM or add swap space to handle the increased memory usage.</p>

For more information about swap space and how to enable it, please follow this tutorial: <a href="https://indiareads/community/articles/how-to-add-swap-on-ubuntu-12-04" target="_blank">https://indiareads/<WBR />community/articles/how-to-add-<WBR />swap-on-ubuntu-12-04</a>.

<p><strong>Spam filtering</strong></p>

		<ul>
			<li>
				Run SpamAssassin server filter?<br /><br />
				Again this is explained pretty well on the page, if your server receives a lot of e-mails then it's beneficial to enable it.<br />
			</li>
		</ul>
		
<p><strong>Database servers</strong></p>

<p>This step should be pretty clear assuming you know what MySQL or PostgreSQL is. Enable whichever one you need.</p>
		
<p>If you picked MySQL, the next step will ask you to enter a root password for your MySQL server. The step after that asks what type of configuration MySQL should use.</p>

<p>It's recommended to pick the one that matches your RAM (I believe it selects the right one by default).</p>
		
<p><strong>DNS zones</strong></p>
		
<p>If you plan on managing your DNS zones with Virtualmin then enter your primary and secondary nameservers here.</p>

<p><strong>Passwords</strong></p>
		
<p>Virtualmin gives you two choices on how it should save passwords. It is highly recommended to select "Only store hashed passwords".</p>

<p>This way if any uninvited people get into your server they won't be able to retrieve any personal passwords.</p>
		
<p>All right, you've completed the post-installation wizard! You might see a big yellow bar on the top of the page with a button that says "Re-check and refresh configuration".</p>

<p>It's recommended to press that button just to make sure everything is well.</p>

<p>If you run into an error during that check, follow the instructions to resolve it and re-check your configuration until all errors are gone.</p>
		
<h2>Some Useful Knowledge</h2>

<p>Here's some information which will help you get around Virtualmin:</p>
		
<p><strong>Virtual Private Server</strong></p>

<p>A virtual private server (usually) represents a website, typically every website has it's own virtual private server.</p>

<p><strong>Sub-server</strong></p>

<p>A sub-server sounds confusing but it's basically a subdomain.</p>
		
<p><strong>Virtualmin vs Webmin</strong></p>

<p>As you can see on the top left, you have Virtualmin and Webmin. These are different control panels, Virtualmin is where you manage all the VPS and anything related to that. Webmin is where you manage the server itself.</p>
		
<p><strong>Documentation</strong></p>

<p>Virtualmin is very well documented, this means that every page has it's own help page and every option's label (the label in front of the input field) is linked to an explanation of that option.</p>
		
<p>Here's a screenshot explaining the menu structure of Virtualmin.</p>
	
<img src="https://assets.digitalocean.com/tutorial_images/IyndTvb.jpg" />

<h2>Setting Up a Virtual Private Server</h2>

<p>Now that we've gone through the installation and wizard, we can start setting up our virtual private server(s). Click "Create Virtual Server" in the navigation on the left side.</p>

<p>Enter the domain name you want to setup a server for, in this tutorial we will use: <a href="http://example.com" target="_blank">example.com</a>.</p>

<p>Enter an administration password which will become the main password to manage the virtual private server. If you are managing the virtual private server by yourself then you don't really need to know this password. In that case, I suggest using a long generated password for extra security.</p>

<p>Virtualmin allows you to manage server configuration templates and account plans, these can be modified under "System Settings" and then "Server Templates" and "Account Plans".</p>

<p>You can specify an administration username, leaving it on automatic would make "example" the username.</p>
	
<p>Have a look at the options hidden underneath the other tabs and enable/disable/change anything you'd like to configure your virtual private server.</p>

<p>Now click "Create Server", Virtualmin will execute the steps needed to setup your virtual private server, if any errors occur, it will display them there.</p>

<h2>Setting Up a Subdomain</h2>

<p>Now that we've setup our virtual private server, it's time to add a subdomain, click on "Create Virtual Server" again.</p>
		
<p>Notice how different options are now on the top of the page: "Top-level server" (Virtual private server), "Sub-server" (Subdomain), "Alias of <a href="http://example.com" target="_blank">example.com</a>" and "Alias of <a href="http://example.com" target="_blank">example.com</a>, with own e-mail".</p>

<p>Click on "Sub-server" to create a subdomain of "<a href="http://example.com" target="_blank">example.com</a>".</p>
		
<p>Fill in the full domain name (<a href="http://test.example.com" target="_blank">test.example.com</a>) and go through the options below it, once you are ready click "Create Server".</p>

<p>Watch Virtualmin do what it needs to do and after it's all done, you should see "<a href="http://test.example.com" target="_blank">test.example.com</a>" as the currently selected virtual private server.</p>

<h2>Setting Up Users</h2>

<p>First of all, let's make sure we are on the top-level server "<a href="http://example.com" target="_blank">example.com</a>" and then click on "Edit Users". On the top, you see you have three options of creating users: "Add a user to this server.", "Batch create users." and "Add a website FTP access user."</p>

<p>If you are only looking to setup a user that has FTP access then click that link, we will go with "Add a user to this server.". The first step is to enter the user's email address, real name and password. Then, carefully look at the other options available to get your ideal setup, when you're done press "Create".</p>

<p>You will now see your user being added to the list, the main user is bold. It also tells you what the user's login is (by default this is something like test.example).</p>

<p>For further setup of e-mail addresses see the "Edit Mail Aliases" link in the menu.</p>

<h2>Setting Up Your Databases</h2>

<p>Click the "Edit Databases" link in the menu, remember to set your virtual private server correctly. Depending on your settings, every virtual private server has its own database (or multiple).</p>

<p>Every database has a "Manage..." link which gives you a very simple view of the database and allows you to execute queries. Now go back to the "Edit Databases" page and click "Passwords", here is your database's password which was automatically generated by Virtualmin.</p>

<p>Moving on to the "Import Database" tab you can assign an existing database (a database created outside of Virtualmin) to the current virtual private server, useful for when you created databases using a MySQL client of some form.</p>

<p>Last but not least, the "Remote hosts" tab allows you to provide multiple hosts to connect to your server, it's recommended to leave it as is (localhost) and use an SSH tunnel to login to your database server.</p>

<h2>Directory Structure</h2>

<p>Virtualmin has a very nicely organised directory structure. See the following scheme.</p>

<pre>
`-- /home/example
    |-- /home/example/awstats
    |-- /home/example/cgi-bin
    |-- /home/example/domains
    |   `-- /home/example/domains/<a href="http://test.example.com" target="_blank">test.<WBR />example.com</a>
    |       |-- /home/example/domains/<a href="http://test.example.com/awstats" target="_blank">test.<WBR />example.com/awstats</a>
    |       |-- /home/example/domains/<a href="http://test.example.com/cgi-bin" target="_blank">test.<WBR />example.com/cgi-bin</a>
    |       |-- /home/example/domains/<a href="http://test.example.com/homes" target="_blank">test.<WBR />example.com/homes</a>
    |       |-- /home/example/domains/<a href="http://test.example.com/logs" target="_blank">test.<WBR />example.com/logs</a>
    |       `-- /home/example/domains/<a href="http://test.example.com/public_html" target="_blank">test.<WBR />example.com/public_html</a>
    |           `-- /home/example/domains/<a href="http://test.example.com/public_html/stats" target="_blank">test.<WBR />example.com/public_html/stats</a>
    |-- /home/example/etc
    |   `-- /home/example/etc/php5
    |-- /home/example/fcgi-bin
    |-- /home/example/homes
    |   `-- /home/example/homes/test
    |       `-- /home/example/homes/test/<WBR />Maildir
    |           |-- /home/example/homes/test/<WBR />Maildir/cur
    |           |-- /home/example/homes/test/<WBR />Maildir/new
    |           `-- /home/example/homes/test/<WBR />Maildir/tmp
    |-- /home/example/logs
    |-- /home/example/public_html
    |   `-- /home/example/public_html/<WBR />stats
    `-- /home/example/tmp	
		</pre>

<p>As you can see, everything is put in <strong>/home/example</strong> and our subdomain can be found in <strong>/home/example/domains/<a href="http://test.example.com/" target="_blank">test.<WBR />example.com/</a></strong>. Every domain has its own logs directory and Virtualmin comes with awstats by default and is accessible through "<a href="http://www.example.com/stats" target="_blank">http://www.example.com/stats</a>"<WBR />, unless you disabled this during the creation of the virtual private server.</p>

<h2>Where Do I Go from Here?</h2>

<p>Take some time to go through Virtualmin's settings. There are many things you can change to make your experience better. Don't forget to also explore the Webmin side of this control panel.</p>

<p>This tutorial only touches the surface of Virtualmin and there's a lot more which can be done with it or added to it through modules. There are even modules for setting up svn/git repositories.</p></div>
    