<link rel='stylesheet' href='digital_ocean.css'><br> <br> 
      <h3 id="introduction">Introduction</h3>

<p>phpMyAdmin is a free, web-facing control panel that can access and edit MySQL databases hosted on your server. It integrates with all existing and future databases on your Ajenti server automatically.</p>

<p><img src="https://assets.digitalocean.com/articles/ajenti_ajenti_v_ubuntu1404/ajenti-phpMyAdmin.png" alt="phpMyAdmin main screen" /></p>

<p>This tutorial will show you integrate phpMyAdmin into the Ajenti control panel.</p>

<h3 id="prerequisites">Prerequisites</h3>

<ul>
<li>Ajenti with Ajenti V installed (read <a href="https://indiareads/community/tutorials/how-to-install-the-ajenti-control-panel-and-ajenti-v-on-ubuntu-14-04">How To Install The Ajenti Control Panel and Ajenti V on Ubuntu 14.04</a>)</li>
<li>A registered domain name that points to your Droplet (<span class="highlight">example.com</span> is used throughout this tutorial)</li>
<li>A subdomain (<code>phpmyadmin.<span class="highlight">example.com</span></code>) that resolves to your Droplet (follow the directions for setting up an A record in <a href="https://indiareads/community/tutorials/how-to-set-up-a-host-name-with-digitalocean">How To Set Up a Host Name with IndiaReads</a>)</li>
</ul>

<h2 id="step-1-—-creating-the-website-in-ajenti-v">Step 1 — Creating the Website in Ajenti V</h2>

<p>Before installing phpMyAdmin, you need to create the website for it in Ajenti.</p>

<p>In your browser, browse to your Ajenti control panel at <code>https://panel.<span class="highlight">example.com</span>:8000</code> (replacing <span class="highlight">example.com</span> with your domain name), and log in. In the sidebar to the right, under the <strong>Web</strong> section, click <strong>Websites</strong>. </p>

<p>Under the <strong>New Website</strong> section there is a <strong>Name</strong> text field, type <code>phpMyAdmin</code> and click the <strong>Create</strong> button. Under the <strong>Websites</strong> section on that same page, click <strong>Manage</strong> next to the new <code>phpMyAdmin</code> line. On the page that appears, uncheck the box next to <strong>Maintenance mode</strong>. In the <strong>Website Files</strong> section below that, change <strong>Path</strong> from <code>/srv/new-website</code> to <code>/srv/phpMyAdmin</code>. Press the <strong>Set</strong> button next to that text field. Click <strong>Apply Changes</strong> at the bottom of the screen.</p>

<p>At the top of that page, click the <strong>Domains</strong> tab. Click <strong>Add</strong> and replace <code>example.com</code> with <code>phpmyadmin.<span class="highlight">your_domain_name</span></code>. Click <strong>Apply Changes</strong> at the bottom of the screen.</p>

<p>Now click the <strong>Content</strong> tab. Change the dropdown box to <code>PHP FastCGI</code> and click <strong>Create</strong>. This basically tells Ajenti to enable PHP for this website.</p>

<p>Click <strong>Apply Changes</strong> at the bottom of the screen. Configuration should now be complete. You should now be able to install phpMyAdmin.</p>

<h2 id="step-2-—-installing-phpmyadmin">Step 2 — Installing phpMyAdmin</h2>

<p>To install phpMyAdmin, first browse to <a href="https://www.phpmyadmin.net/downloads/">www.phpmyadmin.net/downloads</a> and download the latest version of phpMyAdmin in a <code>.zip</code> file format, shown highlighted in blue in the screenshot below.</p>

<p><img src="https://assets.digitalocean.com/articles/ajenti_ajenti_v_ubuntu1404/ajenti-phpMyAdmin-zipfile.png" alt="Download the latest .zip version of phpMyAdmin" /></p>

<p>Back in the Ajenti control panel, browse to <strong>File Manager</strong> in the sidebar. Use the File Manager to browse to <code>/srv</code>. At the bottom of the page there is an <strong>Upload</strong> section. Click the <strong>Choose File</strong> button, and select the phpMyAdmin zip file you downloaded. After it finishes uploading, click the newly added file, named something like <code>phpMyAdmin-<span class="highlight">x.x.xx</span>-all-languages.zip</code>. In the modal box that appears, click the <strong>Unpack</strong> button (highlighted in blue in the screenshot below).</p>

<p><img src="https://assets.digitalocean.com/articles/ajenti_ajenti_v_ubuntu1404/ajenti-phpMyAdmin-directory.png" alt="Click the Unpack button" /></p>

<p>When it finishes, click the <strong>X</strong> button next to <strong>Terminal 0</strong> at the top of the screen. You should be taken back to the File Manager, and there should be a folder named something similar to <code>phpMyAdmin-<span class="highlight">x.x.xx</span>-all-languages</code>. Click the menu button to the right of that folder.</p>

<p><img src="https://assets.digitalocean.com/articles/ajenti_ajenti_v_ubuntu1404/ajenti-phpMyAdmin-menu.png" alt="Click the folder's menu" /></p>

<p>In the <strong>Name</strong> text box, rename the folder to <code>phpMyAdmin</code>, and click <strong>Save</strong>.</p>

<h2 id="step-3-—-setting-up-a-database">Step 3 — Setting up a Database</h2>

<p>We're going to cover setting up a database in Ajenti, so you can login to phpMyAdmin. If you already have a database and login, feel free to skip ahead to step 4.</p>

<p>In the Ajenti control panel, in the sidebar there is a section called <strong>Software</strong>. Directly below that is a menu option named <strong>MySQL</strong>. Click that to access the MySQL control panel in Ajenti.</p>

<p><img src="https://assets.digitalocean.com/articles/ajenti_ajenti_v_ubuntu1404/ajenti-phpMyAdmin-mysql.png" alt="MySQL database controls" /></p>

<p>Under the <strong>Databases</strong> section, click the <strong>Create</strong> button. There you will be able to enter a name for your database. This can be anything you'd like. It won't be seen by anybody but you.</p>

<p>The rest of the sections may automatically populate with default databases and users. This is fine, and you don't need to touch them. Under the <strong>Users</strong> section, click the <strong>Create</strong> button. The <strong>Username</strong> and <strong>Password</strong> fields can be whatever you want, this is what you will login to phpMyAdmin with, so be sure to remember it or write it down. In the <strong>Hostname</strong> field, enter <code>localhost</code>.</p>

<p>That should be it, now we can start using phpMyAdmin.</p>

<h2 id="step-4-—-logging-in">Step 4 — Logging in</h2>

<p>To access the phpMyAdmin web interface, browse to <code>phpmyadmin.<span class="highlight">your_domain_name</span></code> in your web browser. For the <strong>Username</strong> and <strong>Password</strong> fields, enter the credentials of your MySQL user, and press <strong>Go</strong>.</p>

<h2 id="conclusion">Conclusion</h2>

<p>Congratulations! You now have phpMyAdmin installed on your server.</p>

    