<link rel='stylesheet' href='digital_ocean.css'><br><img src=https://community-cdn-digitalocean-com.global.ssl.fastly.net/assets/tutorials/images/large/02072014Undo_twitter.png?1426699640/> <br> 
      <h3 id="introduction">Introduction</h3>

<hr />

<p>When learning the Linux command line, a popular warning to come across is that there is no "undo" command.  This is especially relevant for things like deleting files using the <code>rm</code> command, but has implications in all kinds of situations.</p>

<p>In this guide, we will discuss some ways to "undo" some changes made on the command line.  There is no single strategy, so the programs and techniques involved vary depending on what exactly you are trying to guard against.  We will start with some obvious ideas and move forward.</p>

<p>We will be implementing these on an Ubuntu 12.04 system, but most Linux distributions and versions should be capable of implementing the suggestions with little adjustment.</p>

<h2 id="undoing-file-changes">Undoing File Changes</h2>

<hr />

<p>One of the only ways to restore a file that has been changed inadvertently (or deleted), is to have an extra copy of that file on hand.  We will discuss a few ways of ensuring that you have that option.</p>

<h3 id="backups">Backups</h3>

<hr />

<p>Of course, the easiest and safest way to be able to revert changes made on your server is to run regular routine backups on your important files.</p>

<p>There are a large number of backup programs that are available on a Linux system.  Some guides on how to <a href="https://indiareads/community/community_tags/backups">install and configure backups</a> can be found here.  It is important to study the differences between the tools to find out which one best suits your needs.  Equally important is regularly validating your backups to make sure they are doing what you want them to do.</p>

<p>Backups provide a very complete way to restore damage to your server.  They can handle total data corruption or deletion as long as the copied data is kept in a remote location.</p>

<p>Different levels of backup include full backups (back up all data completely), differential backups (back up every file that has changed since the last full backup), and incremental backups (back up data changes within files since the last full or differential backup).</p>

<p>A combination of these levels are often employed in tandem with each other to completely back up files without the overhead of running full backups every time.</p>

<p>Often, individual files can be restored without having to restore the entire filesystem.  This is especially useful if you accidentally delete or modify a file.</p>

<p>IndiaReads offers a backup plan that will automatically back up your entire server regularly.  You can enable this when creating your droplet by checking the box at the bottom of the page.</p>

<h3 id="version-control">Version Control</h3>

<hr />

<p>A strategy that is somewhat similar to backing up is version control.  While not an ideal solution for backing up an entire computer, if you are trying to simply revert files to a previous state, version control may be exactly what you are looking for.</p>

<p>Version control systems, like <code>git</code> and <code>mercurial</code>, allow you to track changes to files.  This means, if you put your configuration directory, like <code>/etc</code>, under version control, you can easily revert your changes if you made a change that broke something.</p>

<p>We have quite a few articles covering <a href="https://indiareads/community/community_tags/git">how to use git</a> on your server.</p>

<p>Briefly, you can install git on Ubuntu with the following commands:</p>
<pre class="code-pre "><code langs="">sudo apt-get update
sudo apt-get install git
</code></pre>
<p>Once the installation is complete, you need to set up a few configuration options by typing:</p>

<pre>
git config --global user.name "<span class="highlight">your_name</span>"
git config --global user.email "<span class="highlight">your_email</span>"
</pre>

<p>After this is done, change to a directory that you would like to track changes.  We will use the <code>/etc</code> directory in this example.  Another good place to put under version control is your home directory.  We can initialize a git repository by typing:</p>
<pre class="code-pre "><code langs="">cd /etc
sudo git init
</code></pre>
<p>You can then add all of the files in this directory (and subdirectories) by typing:</p>
<pre class="code-pre "><code langs="">sudo git add .
</code></pre>
<p>Commit the changes by typing something like:</p>
<pre class="code-pre "><code langs="">git commit -m "Initial commit"
</code></pre>
<p>Your files will now be under version control.  As you make changes in files in this directory, you will want to re-run those last two commands (with a different message instead of "Initial commit").</p>

<p>You can then revert a file to a previous state by finding the commit hash through the log:</p>
<pre class="code-pre "><code langs="">git log
</code></pre>
<hr />

<pre>
commit 7aca1cf3b5b19c6d37b4ddc6860945e8c644cd4f
Author: root <root>
Date:   Thu Jan 23 13:28:25 2014 -0500

    again

commit <span class="highlight">4be26a199fd9691dc567412a470d446507885966</span>
Author: root <root>
Date:   Thu Jan 23 13:20:38 2014 -0500

    initial commit
</root></root></pre>

<p>Then revert the file by tyipng something like:</p>

<pre>
git checkout <span class="highlight">commit_hash</span> -- <span class="highlight">file_to_revert</span>
</pre>

<p>This is an easy way to revert changes that you may have made.</p>

<p>Keep in mind that this only works well if you are ready to regularly commit to git as you make modifications.  One idea would be to set up a <a href="https://indiareads/community/articles/how-to-use-cron-to-automate-tasks-on-a-vps">cron job</a> to run this regularly.</p>

<h2 id="reverting-changes-with-your-package-manager">Reverting Changes with your Package Manager</h2>

<hr />

<p>Sometimes, you may make some changes using the <code>apt</code> package manager that you would like to revert.  Other times, the package manager can help you restore a package to default settings.  We will discuss these situations below.</p>

<h3 id="uninstalling-packages-with-apt">Uninstalling Packages with Apt</h3>

<hr />

<p>Sometimes, you install a package only to discover that it is not something that you want to keep.  You can remove a package in apt by typing:</p>

<pre>
sudo apt-get remove <span class="highlight">package</span>
</pre>

<p>However, this will leave the configuration files intact.  This is sometimes what you want, but if you are trying to completely remove the package from your system, you can use the <code>purge</code> command instead, like this:</p>

<pre>
sudo apt-get purge <span class="highlight">package</span>
</pre>

<p>This operates in almost entirely the same way, but also removes any configuration files associated with the package.  This is useful if you are sure that you don't need the package anymore or if you haven't made any modifications and can rely on the default configuration file.</p>

<p>You can uninstall any automatically installed dependencies that are no longer needed by using the autoremove apt command:</p>
<pre class="code-pre "><code langs="">sudo apt-get autoremove --purge
</code></pre>
<p>Another issue that happens when installing packages with apt is that "meta-packages" can be difficult to remove correctly.</p>

<p>Meta-packages are packages that are simply a list of dependencies.  They don't install anything themselves, but are a list of other packages to pull in.  They are also notoriously difficult to remove completely in an automatic way.</p>

<p>One tool that can help is the <code>deborphan</code> package.  Install it like this:</p>
<pre class="code-pre "><code langs="">sudo apt-get install deborphan
</code></pre>
<p>After you remove a meta-package, you can run the <code>orphaner</code> command to find orphans that have been left by the package uninstall.  This will help you find packages that aren't removed through regular methods.</p>

<p>Another way of finding stray files is through the <code>mlocate</code> package.  You can install it like this:</p>
<pre class="code-pre "><code langs="">sudo apt-get install mlocate
</code></pre>
<p>Afterwards, you can update the index of files by issuing this command:</p>
<pre class="code-pre "><code langs="">sudo updatedb
</code></pre>
<p>You can then search for the package name to see if there are additional places on the filesystem (outside of the apt indexes) where that package is referenced.</p>

<pre>
locate <span class="highlight">package_name</span>
</pre>

<p>You can also see the files the packages installed by a meta-package by either checking the apt logs:</p>
<pre class="code-pre "><code langs="">sudo nano /var/lob/apt/history.log
</code></pre>
<p>You can use the information about the packages that were installed and manually remove them if you do not need them anymore.</p>

<h3 id="restoring-default-files">Restoring Default Files</h3>

<hr />

<p>Sometimes, during configuration, you change configuration files and want to revert back to the default files as packaged by the distribution.</p>

<p>If you want to keep the current configuration file as a backup, you can copy it out of the way by typing:</p>
<pre class="code-pre "><code langs="">sudo mv file file.bak
</code></pre>
<p>The sudo in the command above is necessary if you do not have write permissions to the directory in question.  Otherwise, you can omit that command.</p>

<p>After you have removed the file or moved it out of the way, you can reinstall the package, telling apt to check if any configuration files are missing:</p>

<pre>
sudo apt-get -o Dpkg::Options="--force-confmiss" install --reinstall <span class="highlight">package_name</span>
</pre>

<p>If you do not know which package is responsible for the configuration file you need to restore, you can use the <code>dpkg</code> utility to tell you:</p>

<pre>
dpkg -S <span class="highlight">file_name</span>
</pre>

<p>If you simply want to run through the initial package configuration steps that happen during some installations to change some values, you can issue this command:</p>

<pre>
dpkg-reconfigure <span class="highlight">package_name</span>
</pre>

<p>This will relaunch the configuration prompts that happened when you initially installed the program.</p>

<h3 id="finding-the-default-permissions-of-files">Finding the Default Permissions of Files</h3>

<hr />

<p>Another common situation happens when you modify file permissions.  Sometimes, you change the permissions of a file for testing purposes or because you've been following some advice only to discover later that it was a bad idea.</p>

<p>It is possible to find out the default permissions of the file as packaged by your distribution by finding out which package owns a file.  You can do that by issuing this command:</p>

<pre>
dpkg -S <span class="highlight">filename</span>
</pre>

<p>This will tell you the package associated with that file.  For instance, if we want to find out the package owner of the <code>/etc/deluser.conf</code> file, we could type:</p>
<pre class="code-pre "><code langs="">dpkg -S /etc/deluser.conf
</code></pre>
<hr />
<pre class="code-pre "><code langs="">adduser: /etc/deluser.conf
</code></pre>
<p>As you can see, it tells us that the <code>adduser</code> package is responsible for that file.  We can then check the <code>.deb</code> file for that package by changing into the apt archive:</p>
<pre class="code-pre "><code langs="">cd /var/cache/apt/archive
</code></pre>
<p>In this directory, you will find the <code>.deb</code> files for many of the packages installed on your system.  If you cannot find a file that matches the package you are using, you may have to re-download it from the repositories using this command:</p>

<pre>
sudo apt-get download <span class="highlight">package</span>
</pre>

<p>For instance, if there's no <code>.deb</code> for our <code>adduser</code> package, we can acquire one by typing:</p>
<pre class="code-pre "><code langs="">sudo apt-get download adduser
</code></pre>
<p>Once the file is in that directory, we can query the default attributes of the files it installs by typing:</p>

<pre>
dpkg -c <span class="highlight">file.deb</span>
</pre>

<p>For the <code>adduser</code> program, this might look something like this:</p>
<pre class="code-pre "><code langs="">dpkg -c adduser_3.113ubuntu2_all.deb
</code></pre>
<hr />

<pre>
drwxr-xr-x root/root         0 2011-10-19 18:01 ./
drwxr-xr-x root/root         0 2011-10-19 18:01 ./etc/
<span class="highlight">-rw-r--r-- root/root       604 2011-10-19 18:01 ./etc/deluser.conf</span>
drwxr-xr-x root/root         0 2011-10-19 18:01 ./usr/
drwxr-xr-x root/root         0 2011-10-19 18:01 ./usr/sbin/
-rwxr-xr-x root/root     35120 2011-10-19 18:01 ./usr/sbin/adduser
-rwxr-xr-x root/root     16511 2011-10-19 18:01 ./usr/sbin/deluser
. . .
</pre>

<p>As you can see, we can verify that the default package sets the permissions to read/write access for the owner (root), and read access for all other users.</p>

<h2 id="conclusion">Conclusion</h2>

<hr />

<p>You should now have some strategies for reversing mistakes that you have made and have ideas for how to plan ahead to give yourself a contingency plan.  Almost all of the concepts mentioned above make use of some sort of record of the previous state of things, whether created by you or available through your distribution's repositories.</p>

<p>This should reinforce the importance of maintaining copies of files that are important to you.  Whether they be data files or configuration parameters, keeping track of what your system looks like when it is in good working order will assist you in repairing things when something goes wrong.</p>

<p>In the comments below, post any other suggestions you have for reverting changes.</p>

<div class="author">By Justin Ellingwood</div>

    