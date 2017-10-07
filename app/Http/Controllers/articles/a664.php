<link rel='stylesheet' href='digital_ocean.css'><br> <br> 
      <h2 id="introduction">Introduction</h2>

<p>There are many ways to install the WordPress content management system. This tutorial introduces two methods for installing WordPress from a public repository: SVN or Git.</p>

<p>While you can install WordPress in a few different ways, e.g. using a one-click image, downloading a zip file, or using the built-in FTP service – using a repository has some unique benefits.</p>

<ul>
<li>Quick upgrades and downgrades to different versions of WordPress</li>
<li>More secure protocols for transferring the files</li>
<li>Faster updates since only the changed files are transferred</li>
</ul>

<p>What happens if you update WordPress to the latest version and your site goes down? With SVN or Git, you can easily roll back the file changes with one command. This is impossible with the FTP updater.</p>

<h3 id="svn-or-git">SVN or Git?</h3>

<p><strong>SVN</strong> stands for Apache Subversion. The official WordPress repository uses SVN:</p>

<p><a href="http://core.svn.wordpress.org/">http://core.svn.wordpress.org/</a></p>

<p>The benefit of using SVN is that you're getting the files directly from WordPress.</p>

<p>Git is a somewhat more modern repository protocol. The GitHub WordPress repository is maintained by a third party, and currently gets its files from WordPress's SVN repository:</p>

<p><a href="https://github.com/WordPress/WordPress">https://github.com/WordPress/WordPress</a></p>

<p>The benefit of using Git is its more sophisticated version control. <strong>However, keep in mind that this is run by a third-party repository maintainer.</strong></p>

<p>You are free to choose which system works best in your situation.</p>

<h3 id="prerequisites">Prerequisites</h3>

<p>Are you ready to get started? Good!</p>

<p>Let's make sure you've got the necessary items:</p>

<ul>
<li>A <strong>1 GB</strong> Droplet running <strong>CentOS 7</strong> (you can adapt this guide for Debian-based distros fairly easily)</li>
<li>root SSH access to your server; you could also use sudo</li>
</ul>

<h2 id="svn-instructions">SVN Instructions</h2>

<p>Follow these instructions for SVN. Skip to the Git instructions instead if you'd rather use Git.</p>

<h3 id="svn-step-one-—-install-lamp">SVN Step One — Install LAMP</h3>

<p>Follow this tutorial to install Apache, MySQL, and PHP on your server:</p>

<p><a href="https://indiareads/community/tutorials/how-to-install-linux-apache-mysql-php-lamp-stack-on-centos-7">How To Install Linux, Apache, MySQL, PHP (LAMP) stack On CentOS 7</a></p>

<p>You can stop after <strong>Step Three — Install PHP</strong>.</p>

<h3 id="svn-step-two-—-install-svn">SVN Step Two — Install SVN</h3>

<p>Install SVN with the following command:</p>
<pre class="code-pre "><code langs="">yum install svn
</code></pre>
<p>You'll need to answer yes to the installation and let the process complete.</p>

<p>Now let's test it. Enter the following command:</p>
<pre class="code-pre "><code langs="">svn
</code></pre>
<p>You should see the following message:</p>
<pre class="code-pre "><code langs="">Type 'svn help' for usage.
</code></pre>
<h3 id="svn-step-three-—-check-out-wordpress">SVN Step Three — Check out WordPress</h3>

<p>When setting up a new WordPress installation, you should note the latest stable version. The best place for this is to visit the <a href="http://www.wordpress.org">official WordPress website</a>.</p>

<p>At the time of writing, this is WordPress 4.0, so that's what we'll use in the examples.</p>

<p>Decide where you want to install WordPress. In this example we'll use the default Apache document root, <code>/var/www/html</code>. You may want to set up a <a href="https://indiareads/community/tutorials/how-to-set-up-apache-virtual-hosts-on-centos-7">virtual host</a> instead.</p>

<p>Check out WordPress 4.0, or the latest version, right from WordPress's repository:</p>
<pre class="code-pre "><code langs="">svn co http://core.svn.wordpress.org/tags/<span class="highlight">4.0</span>/ /var/www/html/
</code></pre>
<p>The general form of the command is as follows:</p>
<pre class="code-pre "><code langs="">svn co http://core.svn.wordpress.org/tags/<span class="highlight">[VERSION]</span>/ <span class="highlight">[INSTALL IN THIS DIRECTORY]</span>/
</code></pre>
<p>You'll see a bunch of file names flash by as your server talks to WordPress's SVN server and grabs the files while noting the version numbers. The process should end with the message <code>Checked out revision <span class="highlight">[some number]</span>.</code></p>

<p>Example:</p>
<pre class="code-pre "><code langs="">Checked out revision 29726.
</code></pre>
<p>Congratulations! You've just installed WordPress using SVN. Now we need to set up the database and configure WordPress.</p>

<h3 id="svn-step-four-—-configure-wordpress">SVN Step Four — Configure WordPress</h3>

<p>Follow the instructions in this <a href="https://indiareads/community/tutorials/how-to-install-wordpress-on-centos-7">WordPress installation tutorial</a> <em>except</em> for the <span class="highlight">wget</span>, <span class="highlight">tar</span>, and <span class="highlight">rsync</span> commands.</p>

<p>You <strong>should</strong> set up the database, change the <span class="highlight">wp-config.php</span> details, and run the <span class="highlight">chown</span> command:</p>
<pre class="code-pre "><code langs="">chown -R apache:apache /var/www/html/*
</code></pre>
<p>At this point WordPress is ready to use! Visit your IP address or domain in your browser, and set your website and login details as prompted. Set it up to your liking, including any themes and plugins.</p>

<h3 id="svn-step-five-—-secure-the-svn-directory">SVN Step Five — Secure the .svn Directory</h3>

<p>SVN uses a special directory called <span class="highlight">.svn</span> that contain important information. In the name of security, it is best to block access to this data so it can't be viewed by the outside world using your web server.</p>

<p>If you want to see what it looks like now, visit http://<span class="highlight">example.com</span>/.svn/ in your browser, using your own domain name. It shows all the administrative files for the repository - not good! Now we'll fix this.</p>

<p>First, open your Apache configuration file for editing:</p>
<pre class="code-pre "><code langs="">nano /etc/httpd/conf/httpd.conf
</code></pre>
<p>Locate the <span class="highlight">AllowOverride</span> line in the <span class="highlight"><Directory "/var/www/html"></span> section. It should be the third <span class="highlight">AllowOverride</span> line in the default configuration file. Update the setting from <strong>None</strong> to <strong>ALL</strong>. This will allow your <span class="highlight">.htaccess</span> file to become active.</p>
<pre class="code-pre "><code langs="">...
<Directory "/var/www/html">

...

    Options Indexes FollowSymLinks

...

    AllowOverride <span class="highlight">ALL</span>

    #
    # Controls who can get stuff from this server.
    #
    Require all granted
</Directory>
...
</code></pre>
<p>Now create a new <span class="highlight">.htaccess</span> file in the  <span class="highlight">/var/www/html/.svn/.htaccess</span> directory:</p>
<pre class="code-pre "><code langs="">nano /var/www/html/.svn/.htaccess
</code></pre>
<p>Add the following contents to the file:</p>
<pre class="code-pre "><code langs="">order deny, allow
deny from all
</code></pre>
<p>Restart Apache:</p>
<pre class="code-pre "><code langs="">service httpd restart
</code></pre>
<p>Now you, or anyone trying to snoop on your server, will get an Internal Server Error if they visit http://<span class="highlight">example.com</span>/.svn/.</p>

<h2 id="svn-step-six-—-upgrade-or-roll-back">SVN Step Six — Upgrade or Roll Back</h2>

<p>New versions of WordPress will be released and you'll want to quickly and easily update your installation to address security patches, fix bugs, and add new features. So let's discuss how this is quickly and easily accomplished using SVN.</p>

<p>It's always a good idea to <a href="https://indiareads/community/tutorials/how-to-choose-an-effective-backup-strategy-for-your-vps">make a backup</a>.</p>

<p>Connect to your server with SSH, and move to your WordPress installation directory:</p>
<pre class="code-pre "><code langs="">cd /var/www/html/
</code></pre>
<p>Execute this command to switch to a new version:</p>
<pre class="code-pre "><code langs="">svn sw http://core.svn.wordpress.org/tags/<span class="highlight">[VERSION]</span>/ .
</code></pre>
<p><strong>[VERSION]</strong> is a placeholder for the actual number of the release.</p>

<p>The period (.) tells SVN where to check and install the files. Since we have changed to the directory containing the WordPress files, we simply used the period to tell SVN to look in the current directory. You could specify the path if you weren't in the directory.</p>

<p>If the new version to be installed was 4.0.1, the command would be:</p>
<pre class="code-pre "><code langs="">svn sw http://core.svn.wordpress.org/tags/4.0.1/ .
</code></pre>
<p>This is also the method for downgrading, too. So let's say you want to return to version 3.9.2; you would do that with this command:</p>
<pre class="code-pre "><code langs="">svn sw http://core.svn.wordpress.org/tags/3.9.2/ .
</code></pre>
<p>To see all the available options, check the <a href="http://core.svn.wordpress.org/tags/">WordPress SVN tags</a> page.</p>

<p>That is how easy it is to upgrade and downgrade the core WordPress files using the SVN system. Your custom settings, like your <span class="highlight">wp-config.php</span> file and your themes and plugins, should all stay in place. However, if you've modified any of the core files, you may run into problems. (That's why you should have made a backup.)</p>

<p>Once you have the files, you need to let WordPress make the changes it needs in the database.</p>

<p>Visit <span class="highlight">http://</span>example.com<span class="highlight">/wp-admin/</span></p>

<p>Click the <strong>Update WordPress Database</strong> button.</p>

<p>That's it! You should now be on your desired version of WordPress. If your site isn't working after the change, simply check out the version you had before. </p>

<h2 id="git-instructions">Git Instructions</h2>

<p>Follow these instructions for Git. Scroll back up to the SVN instructions if you'd rather use SVN.</p>

<h3 id="git-step-one-—-install-lamp">Git Step One — Install LAMP</h3>

<p>Follow this tutorial to install Apache, MySQL, and PHP on your server:</p>

<p><a href="https://indiareads/community/tutorials/how-to-install-linux-apache-mysql-php-lamp-stack-on-centos-7">How To Install Linux, Apache, MySQL, PHP (LAMP) stack On CentOS 7</a></p>

<p>You can stop after <strong>Step Three — Install PHP</strong>.</p>

<h3 id="git-step-two-—-install-git">Git Step Two — Install Git</h3>

<p>Install Git with the following command:</p>
<pre class="code-pre "><code langs="">yum install git
</code></pre>
<p>You'll need to answer yes to accept the download. Now let's test it.  Enter the following command:</p>
<pre class="code-pre "><code langs="">git
</code></pre>
<p>You should see the following message:</p>
<pre class="code-pre "><code langs="">usage: git ...
</code></pre>
<h3 id="git-step-three-—-clone-wordpress">Git Step Three — Clone WordPress</h3>

<p>First, figure out which version of WordPress you want to install. The best place for this is to visit the <a href="http://www.wordpress.org">official WordPress website</a>.</p>

<p>At the time of writing, this is WordPress 4.0, so that's what we'll use in the examples.</p>

<p>Decide where you want to install WordPress. In this example we'll use the default Apache document root, <span class="highlight">/var/www/html</span>. If you want to set up a <a href="https://indiareads/community/tutorials/how-to-set-up-apache-virtual-hosts-on-centos-7">virtual host</a>, you can do that instead.</p>

<p>Clone the latest version of WordPress from the GitHub repository:</p>
<pre class="code-pre "><code langs="">git clone git://github.com/WordPress/WordPress /var/www/html/
</code></pre>
<p>The general form of the command is as follows:</p>
<pre class="code-pre "><code langs="">git clone git://github.com/WordPress/WordPress <span class="highlight">[INSTALL IN THIS DIRECTORY]</span>/
</code></pre>
<p>You'll see some messages such as <span class="highlight">Cloning in...</span> along with, but not limited to, <span class="highlight">Receiving objects:</span> and <span class="highlight">Receiving deltas:</span> with some information. You now have a complete working development copy of WordPress, including past production runs.</p>

<p>However, we want the latest production (stable) version. First move to the WordPress directory on your server:</p>
<pre class="code-pre "><code langs="">cd /var/www/html/
</code></pre>
<p>Check out WordPress 4.0, or the latest stable version, with the following command:</p>
<pre class="code-pre "><code langs="">git checkout <span class="highlight">4.0</span>
</code></pre>
<p>The general form of the command is as follows:</p>
<pre class="code-pre "><code langs="">git checkout <span class="highlight">[VERSION]</span>
</code></pre>
<p>Git will display some information along with something like <code>HEAD is now at 8422210... Tag 4.0,</code> which indicates the file versions were successfully changed; in this case to 4.0.</p>

<p>Congratulations! You've just installed WordPress using Git. </p>

<p>Now we need to set up the database and configure WordPress.</p>

<h3 id="git-step-four-—-configure-wordpress">Git Step Four — Configure WordPress</h3>

<p>Follow the instructions in this <a href="https://indiareads/community/tutorials/how-to-install-wordpress-on-centos-7">WordPress installation tutorial</a>, but <strong>without</strong> the <span class="highlight">wget</span>, <span class="highlight">tar</span>, and <span class="highlight">rsync</span> commands.</p>

<p>You do need to set up the database, change the <span class="highlight">wp-config.php</span> details, and run the <span class="highlight">chown</span> command:</p>
<pre class="code-pre "><code langs="">chown -R apache:apache /var/www/html/*
</code></pre>
<p>At this point WordPress is ready to use! Visit your IP address or domain in your browser, and set your website and login details as prompted. You can add themes, plugins, and content as you like.</p>

<h3 id="git-step-five-—-secure-the-git-directory">Git Step Five — Secure the .git Directory</h3>

<p>Git uses a special directory called <code>.git</code> that contain important information. You should block web access to this directory for security's sake.</p>

<p>If you want to see what it looks like now, visit http://<span class="highlight">example.com</span>/.git/ in your browser, using your own domain name. It should list the files in the directory, which is a security issue.</p>

<p>First, open your Apache configuration file for editing:</p>
<pre class="code-pre "><code langs="">nano /etc/httpd/conf/httpd.conf
</code></pre>
<p>Locate the <span class="highlight">AllowOverride</span> line in the <span class="highlight"><Directory "/var/www/html"></span> section. It should be the third <span class="highlight">AllowOverride</span> line in the default configuration file. Update the setting from <strong>None</strong> to <strong>ALL</strong>. This will allow your <span class="highlight">.htaccess</span> file to become active.</p>
<pre class="code-pre "><code langs="">...
<Directory "/var/www/html">

...

    Options Indexes FollowSymLinks

...

    AllowOverride <span class="highlight">ALL</span>

    #
    # Controls who can get stuff from this server.
    #
    Require all granted
</Directory>
...
</code></pre>
<p>Now create a new <span class="highlight">.htaccess</span> file in the <span class="highlight">/var/www/html/.git/.htaccess</span> directory:</p>
<pre class="code-pre "><code langs="">nano /var/www/html/.git/.htaccess
</code></pre>
<p>Add the following contents to the file:</p>
<pre class="code-pre "><code langs="">order deny, allow
deny from all
</code></pre>
<p>Restart Apache:</p>
<pre class="code-pre "><code langs="">service httpd restart
</code></pre>
<p>Now you, or anyone trying to snoop on your server, will get an Internal Server Error if they visit http://<span class="highlight">example.com</span>/.git/.</p>

<h3 id="git-step-six-—-upgrade-or-roll-back">Git Step Six — Upgrade or Roll Back</h3>

<p>Now it's time to upgrade WordPress. You'll want to keep up with security patches, bug fixes, and new features. So let's discuss how to upgrade with Git.</p>

<p>It's always a good idea to <a href="https://indiareads/community/tutorials/how-to-choose-an-effective-backup-strategy-for-your-vps">make a backup</a>.</p>

<p>Connect to your server with SSH, and move to your WordPress installation directory:</p>
<pre class="code-pre "><code langs="">cd /var/www/html/
</code></pre>
<p>Fetch the latest files from the third-party WordPress repository:</p>
<pre class="code-pre "><code langs="">git fetch -p git://github.com/WordPress/WordPress
</code></pre>
<p>The <span class="highlight">-p</span> switch tells Git to remove any old versions that are no longer in the repository. This helps keep your files in sync with the remote server.</p>

<p>Execute this command to check out a new version:</p>
<pre class="code-pre "><code langs="">git checkout <span class="highlight">[VERSION]</span>
</code></pre>
<p><strong>[VERSION]</strong> is a placeholder for the actual number of the release. If the new version to be installed was 4.0.1, the command would be:</p>
<pre class="code-pre "><code langs="">git checkout 4.0.1
</code></pre>
<p>This is also the method for downgrading, too. If you want to return to version 3.9.2; you would do that with this command:</p>
<pre class="code-pre "><code langs="">git checkout 3.9.2
</code></pre>
<p>To see all the available options, check the <strong>branch</strong> dropdown and the <strong>Tags</strong> tab on the <a href="https://github.com/WordPress/WordPress">repository page</a>.</p>

<p>That's it! With Git, your custom settings, like your <span class="highlight">wp-config.php</span> file and your themes and plugins, should stay the same. However, if you've modified any of the core files, you may run into problems; hence the need for a backup.</p>

<p>Once you have the files, you need to let WordPress make the changes it needs in the database.</p>

<p>Visit http://<span class="highlight">example.com</span>/wp-admin/.</p>

<p>Click the <strong>Update WordPress Database</strong> button.</p>

<p>That's it! You should now be on your desired version of WordPress. If your site isn't working after the change, simply check out the version you had before.</p>

<h2 id="conclusion">Conclusion</h2>

<p>If you made it to the end of this tutorial you should have a basic understanding of setting up WordPress using the SVN and/or Git system(s). It is important to note that this method will back up the core WordPress system, but your custom themes and plugins will require a different approach.</p>

<p>Now that you have learned how to manage WordPress with version control, you'll probably never want to go back. This is so much faster, easier, and safer. You don't need to store any FTP information in your WordPress installation. Also, you can easily and quickly revert back to previous versions if the need arises, something that the FTP method makes more difficult.</p>

<p>This guide is not a replacement for a good <a href="https://indiareads/community/tutorials/how-to-choose-an-effective-backup-strategy-for-your-vps">backup system</a>, so make sure you have good backups, too.</p>

    