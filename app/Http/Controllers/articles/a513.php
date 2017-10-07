<link rel='stylesheet' href='digital_ocean.css'><br> <br> 
      <h3 id="introduction">Introduction</h3>

<p>FreeBSD is a powerful operating system capable of functioning in a variety of roles.  Part of what makes this operating system an ideal choice in many scenarios is its reputation for flexibility.  A large contribution to this reputation comes from FreeBSD's supported method for installing software from source, known as the <strong>ports system</strong>.</p>

<p>In this guide, we will discuss some of the benefits of the ports system and will demonstrate how to use it to acquire and manage additional software.  We will cover how to install using the <code>make</code> command, how to customize your applications, and how to leverage some common tools to make ports maintenance easier.</p>

<h2 id="prerequisites">Prerequisites</h2>

<p>In order to follow along with this guide, you should have access to a FreeBSD 10.1 server and a non-root user account with <code>sudo</code> privileges.  <a href="https://indiareads/community/tutorials/how-to-get-started-with-freebsd-10-1">This guide</a> can assist you in logging into your FreeBSD server and setting up a reasonable working environment.</p>

<h2 id="managing-different-types-of-freebsd-software">Managing Different Types of FreeBSD Software</h2>

<p>The FreeBSD team maintains the base FreeBSD operating system as a coherent unit.  Its components are updated using a tool called <code>freebsd-update</code> and their behavior is controlled primarily through configuration files located within the <code>/etc</code> directory.  While you can install and use alternatives to the bundled software, you cannot easily or safely remove the ones that are included in the base system as these are considered functionally essential parts of the operating system.</p>

<p>In contrast, optional software is managed using different processes, tools, and locations within the filesystem.  Unlike software from the base system, optional software is configured within the <code>/usr/local/etc</code> directory.  FreeBSD provides two sanctioned methods for downloading and installing additional software onto the system.</p>

<p>The ports system, which we will be describing in this guide, is managed through a filesystem hierarchy located at <code>/usr/ports</code> that categorizes each available piece of software that FreeBSD knows how to build.  Within this directory, the first level subdirectory categorizes software primarily according to function or language.  Within these directories, folders exist for each individual piece of software.  Software can be downloaded, configured, compiled, and installed either through simple <code>make</code> commands or through available helper utilities.  The software in the ports collection includes all patches necessary to build and run the application on a FreeBSD system.</p>

<p>The other type of installation supported by the system is <strong>packages</strong>, which are software binaries compiled from the ports collection using reasonable defaults.  This is a good method of quickly acquiring software, but it forfeits the level of customization provided by the ports system.  You can learn more about how to manage software packages in <a href="https://indiareads/community/tutorials/how-to-manage-packages-on-freebsd-10-1-with-pkg">this guide</a>.</p>

<h2 id="prepping-the-ports-tree">Prepping the Ports Tree</h2>

<p>The ports tree is the name of the hierarchy that exists beneath the <code>/usr/ports</code> directory.  This hierarchy contains directories that correspond to port categories, within which are other directories that correspond to individual ports.</p>

<p>Before we begin manipulating any ports, we should ensure that this hierarchy is up-to-date.  Forgetting to refresh the ports hierarchy can result in build failures as the ports try to fetch and build files that may not be valid anymore.</p>

<p>We can update the ports tree using a utility called <code>portsnap</code>.  This tool queries the FreeBSD ports servers for changes.</p>

<h3 id="making-note-of-our-last-update">Making Note of Our Last Update</h3>

<p>Before we execute the actual update command, we need to take note of the timestamp on a specific file within our ports tree called <code>/usr/ports/UPDATING</code>.  We can use the <code>stat</code> tool to see the various timestamps associated with the file:</p>
<pre class="code-pre "><code langs="">stat -x /usr/ports/UPDATING
</code></pre>
<p>You should see output that looks like this:</p>
<pre class="code-pre "><code langs="">  File: "UPDATING"
  Size: 375337       FileType: Regular File
  Mode: (0644/-rw-r--r--)         Uid: (    0/    root)  Gid: (    0/   wheel)
Device: 0,81   Inode: 2011338    Links: 1
Access: Thu Dec 11 22:24:59 2014
Modify: <span class="highlight">Thu Dec 11 15:40:12 2014</span>
Change: <span class="highlight">Thu Dec 11 22:24:59 2014</span>
</code></pre>
<p>There is a chance that you will instead receive an error like this:</p>
<pre class="code-pre "><code langs="">stat: /usr/ports/UPDATING: stat: No such file or directory
</code></pre>
<p>If you see this, it means that you do not have a ports tree initialized on your system.  If this is the case, continue onto the next section to learn how to extract an initial ports tree onto your system using <code>portsnap</code>.</p>

<p>The values we want to pay attention to are the "Modify" and "Change" times, which are highlighted in the output above.  In this instance, the "Modify" timestamp will be the most recent time that a ports maintainer modified the file with important information.  The "Change" timestamp will be the last time that the file was synced to your server.</p>

<p>We need to remember the timestamp so that we know which updating notes we need to pay attention to after we refresh our ports tree.  We can save these to a file in our home directory by typing:</p>
<pre class="code-pre "><code langs="">stat -x /usr/ports/UPDATING > ~/last_update
</code></pre>
<p>Now that we have this information recorded, we can go ahead and update our ports tree.</p>

<h3 id="updating-the-ports-tree-with-portsnap">Updating the Ports Tree with Portsnap</h3>

<p>Once you have a good idea of when the ports tree was last updated, you can sync your ports tree with the most recent information from the FreeBSD project's site.  To do this, we will use a tool called <code>portsnap</code>.</p>

<p>If you do not have any information in the <code>/usr/ports</code> directory (if you encountered the error we mentioned in the last section), you can download and extract the entire ports tree to that directory with <code>portsnap</code>.  This process can take quite a long time, but it is only necessary if your <code>/usr/ports</code> directory is empty, a situation that should only happen once.  If your FreeBSD server is on IndiaReads, your ports tree should already be initialized:</p>
<pre class="code-pre "><code langs="">sudo portsnap fetch extract
</code></pre>
<p>This will download and extract the entire ports tree to the <code>/usr/ports</code> directory.</p>

<p>If you already have a ports tree built in the <code>/usr/ports</code> directory (if you were able to record the timestamps in the last section), you can update the files to their most recent versions with this command:</p>
<pre class="code-pre "><code langs="">sudo portsnap fetch update
</code></pre>
<p>This command will only extract those files which differ from the ones within the <code>/usr/ports</code> structure, so it will take significantly less time than the <code>extract</code> variant of the command.  This is the format that should be used in day-to-day updates of the ports tree.</p>

<p>Once your ports tree is built or updated, you can begin to manage and work with ports on your system.</p>

<h2 id="searching-the-ports-tree-for-applications">Searching the Ports Tree for Applications</h2>

<p>Now that you have an updated ports tree hierarchy on your system, you can begin looking at the software available to you.  There are several ways of doing this, each of which has its advantages.</p>

<h3 id="searching-with-whereis">Searching with <code>whereis</code></h3>

<p>The easiest way of searching for an application is by name using the <code>whereis</code> command.  This will search for the command on your system and within the ports tree.  If it finds a match, it will return the relevant path info for the application on your system.</p>

<p>Typically, if the application is not installed but the search was for a valid port, it will return the path to the port within the ports tree.  If the application <em>is</em> installed, it will usually return the path to the executable, the port, and often the <code>man</code> page:</p>

<p>For example, we can search for the <code>wget</code> utility by typing this:</p>
<pre class="code-pre "><code langs="">whereis wget
</code></pre>
<p>If the port is not installed, we would see something like this:</p>
<pre class="code-pre "><code langs="">wget: /usr/ports/ftp/wget
</code></pre>
<p>Since the path begins with <code>/usr/ports</code>, we know that this is an installable port.  We can use the path returned if we wish to install this port.</p>

<p>If the <code>wget</code> command is already installed, we may see output that looks like this:</p>
<pre class="code-pre "><code langs="">wget: /usr/local/bin/wget /usr/local/man/man1/wget.1.gz /usr/ports/ftp/wget
</code></pre>
<p>This includes the path to the actual installed executable, the <code>man</code> page file for the application, and the location of the port within the ports tree.</p>

<h3 id="searching-using-the-echo-command-on-the-filesystem-hierarchy">Searching Using the <code>echo</code> Command on the Filesystem Hierarchy</h3>

<p>In the <a href="https://www.freebsd.org/doc/en_US.ISO8859-1/books/handbook/ports-finding-applications.html">FreeBSD Handbook</a>, the authors also suggest a rather novel way of searching using only the <code>echo</code> command and the built-in structure of the ports tree.</p>

<p>The ports tree is set up with all of the relevant files and directories under the <code>/usr/ports</code> directory.  In the filesystem, each port is represented by a distinct directory that contains all of the information necessary to build and install the software on the FreeBSD system. </p>

<p>To assist in organization, these ports are grouped by function within category directories within <code>/usr/ports</code>.  So in the <code>wget</code> example above, we see that the <code>wget</code> command has been categorized within the <code>ftp</code> group.  So the <code>/usr/ports</code> directory contains category directories which, in turn, contain directories for ports.</p>

<p>We can exploit this consistent structure through the use of the <code>echo</code> command and wildcards.  Since we probably do not know the category the port will exist in, we will replace that directory level with an asterisk.  We can also put these before and after our search term if we want to be more flexible in our matching.  So we can search for <code>wget</code> related programs by typing:</p>
<pre class="code-pre "><code langs="">echo /usr/ports/*/*wget*
</code></pre>
<p>This will return something similar to this:</p>
<pre class="code-pre "><code langs="">/usr/ports/ftp/gwget /usr/ports/ftp/wget /usr/ports/www/ruby-wgettsv /usr/ports/www/wgetpaste
</code></pre>
<p>This can be a bit more flexible than the <code>whereis</code> command because it does not require an exact match.</p>

<h3 id="searching-using-the-available-make-targets">Searching Using the Available <code>make</code> Targets</h3>

<p>The most powerful way to search for ports is to use the <code>make</code> command.</p>

<p>This is also the command that is used to build and install ports onto the system, but is more generally a flexible tool that can be used to easily execute complex tasks that have been defined in a config file.  The FreeBSD developers have created <code>make</code> "targets" (task definitions) that will perform a search of the ports tree for different criteria.</p>

<p>To use this functionality, you must first move to the base of the ports tree.  This is where the <code>make</code> targets are defined:</p>
<pre class="code-pre "><code langs="">cd /usr/ports
</code></pre>
<p>The general syntax for executing a search is:</p>
<pre class="code-pre "><code langs="">make [search|quicksearch] [searchtype]=[searchquery] [modifiers]
</code></pre>
<p>The two <code>make</code> targets designed to search the ports tree are <code>search</code> and <code>quicksearch</code>.  These have exactly the same functionality, differing only in their default display.</p>

<p>The <code>search</code> target will return information about the name of the port, path in the port tree, a general description and then details about the build including the maintainer email, build dependencies, run dependencies, and the upstream URL.  The <code>quicksearch</code> target only returns the port name, path, and description.</p>

<p>The search types can be any of the following:</p>

<ul>
<li><strong>name</strong>: Search only within the name field of the port.</li>
<li><strong>key</strong>: Search within the name, comment, and dependencies fields of the port.</li>
<li><strong>path</strong>: Search a specific path within the ports hierarchy.</li>
<li><strong>info</strong>: Search within the info (description) field of the port.</li>
<li><strong>maint</strong>: Searches by the maintainer email address.</li>
<li><strong>cat</strong>: Searches based on the category of the port.</li>
<li><strong>bdeps</strong>: Searches the build-time dependencies of each port.</li>
<li><strong>rdeps</strong>: Searches the run-time dependencies of each port.</li>
<li><strong>www</strong>: Searches the ports website.</li>
</ul>

<p>You can also prepend an "x" before any of the above categories to remove results that satisfy a match.  For instance, if your search includes <code>xname=apache</code>, any port that has the string "apache" in its name field will not be returned.</p>

<p>Let's go over some quick examples.  Below, you can see the difference in the output of the <code>search</code> and <code>quicksearch</code> targets.  The <code>search</code> target includes full information about the matches:</p>
<pre class="code-pre "><code langs="">make search name=htop
</code></pre><pre class="code-pre "><code langs="">Port:   htop-1.0.3
Path:   /usr/ports/sysutils/htop
Info:   Better top(1) - interactive process viewer
Maint:  gaod@hychen.org
B-deps: autoconf-2.69 autoconf-wrapper-20131203 automake-1.14_1 automake-wrapper-20131203 gettext-runtime-0.19.3 indexinfo-0.2.2 libexecinfo-1.1_3 libffi-3.0.13_3 libiconv-1.14_6 m4-1.4.17_1,1 ncurses-5.9.20141213 perl5-5.18.4_11 python2-2_3 python27-2.7.9 readline-6.3.8
R-deps: libexecinfo-1.1_3 lsof-4.89.b,8 ncurses-5.9.20141213
WWW:    http://htop.sourceforge.net/
</code></pre>
<p>On the other hand, the <code>quicksearch</code> target only displays the essential information about the matches it finds:</p>
<pre class="code-pre "><code langs="">make quicksearch name=htop
</code></pre><pre class="code-pre "><code langs="">Port:   htop-1.0.3
Path:   /usr/ports/sysutils/htop
Info:   Better top(1) - interactive process viewer
</code></pre>
<p>It is possible to combine different search types to narrow down the results. For example, if we were to search for the <code>ntop</code> network monitor, we might see results that look like this:</p>
<pre class="code-pre "><code langs="">make quicksearch name=ntop
</code></pre><pre class="code-pre "><code langs="">Port:   ntopng-zmq-3.2.3_1
Path:   /usr/ports/devel/ntopng-zmq
Info:   NTOPNG specific ZMQ library

Port:   diveintopython-5.4_1
Path:   /usr/ports/lang/diveintopython
Info:   Free Python tutorial book that is "not For Dummies(tm)"

Port:   ntop-5.0.1_8
Path:   /usr/ports/net/ntop
Info:   Network monitoring tool with command line and web interfaces

Port:   ntopng-1.2.1_1
Path:   /usr/ports/net/ntopng
Info:   Network monitoring tool with command line and web interfaces

Port:   sntop-1.4.3_1
Path:   /usr/ports/net/sntop
Info:   Monitor status of network nodes using fping
</code></pre>
<p>Here, we can see that most of the results are related to <code>ntop</code>, but we also have a book about learning Python.  We can further filter by adding a path specification:</p>
<pre class="code-pre "><code langs="">make quicksearch name=ntop path=/net
</code></pre><pre class="code-pre "><code langs="">Port:   ntop-5.0.1_8
Path:   /usr/ports/net/ntop
Info:   Network monitoring tool with command line and web interfaces

Port:   ntopng-1.2.1_1
Path:   /usr/ports/net/ntopng
Info:   Network monitoring tool with command line and web interfaces

Port:   sntop-1.4.3_1
Path:   /usr/ports/net/sntop
Info:   Monitor status of network nodes using fping
</code></pre>
<p>We can also modify the behavior of the search in a few different ways.  Some valid modifiers are:</p>

<ul>
<li><strong>icase</strong>: Set this to "1" to turn on case-insensitivity.  This is the default.  To make searches case-sensitive, set this to "0".</li>
<li><strong>display</strong>: This contains a list of fields, separated by commas, to display in the output.</li>
<li><strong>keylim</strong>: Limit the searching (using the "key" search type) only to those fields being displayed.  Turn this on by setting it to "1".</li>
</ul>

<p>For instance, we could search for descriptions or paths that contain the capitalized string "Paste" by typing:</p>
<pre class="code-pre "><code langs="">make search key=Paste display=path,info keylim=1 icase=0
</code></pre><pre class="code-pre "><code langs="">Path:   /usr/ports/devel/pear-SebastianBergmann_PHPCPD
Info:   Copy/Paste Detector (CPD) for PHP code

Path:   /usr/ports/devel/py-zope.copypastemove
Info:   Copy, Paste, and Move support for content components

Path:   /usr/ports/german/bsdpaste
Info:   Pastebin web application to upload and read text on a webserver

Path:   /usr/ports/www/p5-WWW-Pastebin-PastebinCom-Create
Info:   Paste to http://pastebin.com from Perl

Path:   /usr/ports/www/p5-WebService-NoPaste
Info:   Pastebin web application to upload snippets of text

Path:   /usr/ports/www/py-django-dpaste
Info:   Pastebin Django application that powers dpaste.de

Path:   /usr/ports/www/wgetpaste
Info:   Paste to several pastebin services via bash script
</code></pre>
<p>One further situation that you may come across in your searches is a port that has been moved or deleted.  These results look like this:</p>
<pre class="code-pre "><code langs="">make quicksearch name=wget
</code></pre><pre class="code-pre "><code langs="">. . .

Port:   ftp/emacs-wget
Moved:
Date:   2011-05-02
Reason: Has expired: Upstream disappeared and distfile is no longer available

Port:   ftp/wgetpro
Moved:
Date:   2011-10-14
Reason: Vulnerable since 2004-12-14

Port:   www/wget4web
Moved:
Date:   2012-01-01
Reason: Has expired: Depends on expired www/apache13
</code></pre>
<p>If a port has been moved to a new location, the "Moved" field will contain the new place where the port can be found.  If this field is present, but empty, the port has been deleted.</p>

<p>Even though these are deleted, they will still show up in your search results.  If you wish to prevent moved or deleted ports from showing up, you can set the <code>PORTSEARCH_MOVED</code> environmental variable to "0".</p>

<p>For example, to set this variable to "0" for only the command that follows, using the default <code>tcsh</code>, we can type:</p>
<pre class="code-pre "><code langs="">env PORTSEARCH_MOVED=0 make quicksearch name=wget
</code></pre><pre class="code-pre "><code langs="">Port:   gwget-1.0.4_9
Path:   /usr/ports/ftp/gwget
Info:   GNOME wget front-end

Port:   wget-1.16
Path:   /usr/ports/ftp/wget
Info:   Retrieve files from the Net via HTTP(S) and FTP

Port:   ruby20-ruby-wgettsv-0.95
Path:   /usr/ports/www/ruby-wgettsv
Info:   Collect WWW resources and generate TSV data

Port:   wgetpaste-2.25
Path:   /usr/ports/www/wgetpaste
Info:   Paste to several pastebin services via bash script
</code></pre>
<p>As you can see, all of the entries that had been moved or deleted are now filtered out of our results.  If you wish to make this the default behavior, you can set <code>PORTSEARCH_MOVED=0</code> in your <code>make.conf</code> file:</p>
<pre class="code-pre "><code langs="">sudo sh -c 'echo "PORTSEARCH_MOVED=0" >> /etc/make.conf'
</code></pre>
<h2 id="installing-ports-using-make">Installing Ports Using Make</h2>

<p>Once you have found a port that you wish to install, you can easily download the required files, build the binary, and install it using the <code>make</code> command.</p>

<p>To install a port, change to the directory of the port within the port tree.  You can find this location through any of the search methods given above.  To demonstrate this, we will be installing a port called <code>portmaster</code>, which we will need later in this guide.</p>

<p>First, change to the port location.  The <code>portmaster</code> port is kept in the <code>ports-mgmt</code> category:</p>
<pre class="code-pre "><code langs="">cd /usr/ports/ports-mgmt/portmaster
</code></pre>
<p>Now, we can easily download, configure, compile, and install the port using <code>make</code> targets.  Since these operations affect our system, we will need to use <code>sudo</code>.  The long way to do this is through individual calls to <code>make</code>, like this.  Do not type these commands yet, we will show you a much shorter version momentarily:</p>
<pre class="code-pre "><code langs="">sudo make config
sudo make fetch
sudo make checksum
sudo make depends
sudo make extract
sudo make patch
sudo make configure
sudo make build
sudo make install
</code></pre>
<p>We <em>could</em> shorten this a bit by listing each target after a single <code>make</code> command like this:</p>
<pre class="code-pre "><code langs="">sudo make config fetch checksum depends extract patch configure build install
</code></pre>
<p>However, this is almost always unnecessary.  Each of the targets listed above will call any preceding targets necessary in order to complete the task.  So the above could simply be condensed into:</p>
<pre class="code-pre "><code langs="">sudo make install
</code></pre>
<p>Typically, we would want to expand this chain of commands slightly to make sure we configured everything correctly.  We usually want to specify <code>config-recursive</code>, an option not in the above pipeline, before the <code>install</code> target in order to take care of configuration for this port and any dependencies at the beginning of the installation.  Otherwise the build process may halt and wait for user input part way through building the necessary dependencies.</p>

<p>We also usually want to clean up a bit after the installation to reclaim disk space and keep a clean system.  We can do this with the <code>clean</code> or <code>distclean</code> targets.  The <code>clean</code> target deletes the extracted source code used to build this port and any dependency ports.  The <code>distclean</code> target does this as well, but also deletes the compressed source archive for this package from the <code>/usr/ports/distfiles</code> directory.</p>

<p>So a typical installation command may look like this:</p>
<pre class="code-pre "><code langs="">sudo make config-recursive install distclean
</code></pre>
<p>This will prompt you to configure the port and any dependencies at the beginning of the process.  Afterwards it will download and verify the integrity of the source archive.  It will then change contexts to fulfill any missing dependencies.  When that process is complete, it will return to the port in question, extract the archive, apply any necessary patches, and configure it according to the options you selected.  It will then compile the application and install it on your system.  Afterwards, it will remove the expanded source code for this port and any dependencies.  It will then delete the source archive for this port.</p>

<p>Execute the command above within the <code>/usr/ports/ports-mgmt/portmaster</code> directory:</p>
<pre class="code-pre "><code langs="">sudo make config-recursive install distclean
</code></pre>
<p>You will be presented with a single dialog box for the application.  If you are using one of the listed shells, you can choose to configure shell completion for the tool here:</p>

<p><img src="https://assets.digitalocean.com/articles/freebsd_ports_intro/dialog.png" alt="FreeBSD port config" /></p>

<p>The <code>portmaster</code> port does not have any dependencies, but if there were any, configuration options for dependencies would be presented directly after the target port's configuration above.  The port will be downloaded, configured, and installed.</p>

<p>If you are using the default <code>tcsh</code>, you will want to rescan your PATH after every installation so that your shell environment is aware of all of the installed applications:</p>
<pre class="code-pre "><code langs="">rehash
</code></pre>
<p>If the above process was successful, you have successfully installed your first port.</p>

<p>While the main operating system and configuration is done in the conventional locations, optional software installed through the ports system is installed within the <code>/usr/local</code> hierarchy.</p>

<p>This means that to configure optional software, you will have to look in the <code>/usr/local/etc</code> directory.  The executables themselves are kept primarily in the <code>/usr/local/bin</code> and <code>/usr/local/sbin</code> directories.  Keep this in mind when you are configuring or starting applications.</p>

<h3 id="notes-regarding-applications-that-run-as-services">Notes Regarding Applications that Run as Services</h3>

<p>One thing to keep in mind is that if you are installing a port that will be run as a service, the installation procedure will not start the service automatically.  In fact, there are a few steps that you must take in order to start the services within FreeBSD.</p>

<p>If you wish to start a service a single time, you can do so by typing:</p>
<pre class="code-pre "><code langs="">sudo service <span class="highlight">servicename</span> onestart
</code></pre>
<p>For instance, to start MySQL, you could type:</p>
<pre class="code-pre "><code langs="">sudo service mysql-server onestart
</code></pre>
<p>Assuming that any necessary configuration has been complete, this will start the service a single time.  If you want to stop the service as a later time, you can type:</p>
<pre class="code-pre "><code langs="">sudo service mysql-server onestop
</code></pre>
<p>While this works for quick tests, it is not the ideal way to manage services in FreeBSD.  To configure your service to start at each boot, you must enable it.  To do so, you have to add a line to the <code>/etc/rc.conf</code> file.</p>

<p>The init files that specify how optional services are started are kept in the <code>/usr/local/etc/rc.d</code> directory.  In each of these init files, a variable called <code>rcvar</code> tells the init system which variable within the <code>/etc/rc.conf</code> file to look for to determine whether to start the service.  For each optional service, you can find the appropriate line to add to the <code>/etc/rc.conf</code> file by typing:</p>
<pre class="code-pre "><code langs="">grep rcvar /usr/local/etc/rc.d/*
</code></pre>
<p>You will receive a list that looks something like this:</p>
<pre class="code-pre "><code langs="">/usr/local/etc/rc.d/avahi-daemon:<span class="highlight">rcvar=avahi_daemon_enable</span>
/usr/local/etc/rc.d/avahi-dnsconfd:<span class="highlight">rcvar=avahi_dnsconfd_enable</span>
/usr/local/etc/rc.d/dbus:<span class="highlight">rcvar=dbus_enable</span>
/usr/local/etc/rc.d/rsyncd:<span class="highlight">rcvar=rsyncd_enable</span>
</code></pre>
<p>The highlighted portion of the output shows the variable we need to set to "YES" to enable each of these services.</p>

<p>For example, to enable the <code>rsync</code> daemon service, we can add this line to <code>/etc/rc.conf</code>:</p>
<pre class="code-pre "><code langs="">rsyncd_enable="YES"
</code></pre>
<p>The appropriate line must be in the <code>/etc/rc.conf</code> file before using the normal service management commands.  For instance, you can add the above line to the bottom of the <code>/etc/rc.conf</code> file either with your text editor, or by typing:</p>
<pre class="code-pre "><code langs="">sudo sh -c "echo 'rsyncd_enable="YES"' >> /etc/rc.conf"
</code></pre>
<p>This will cause the rsync daemon to be started every boot.  You can now control the service using the <code>service</code> command without the "one" prefix.  For instance, you can start the service by typing:</p>
<pre class="code-pre "><code langs="">sudo service rsyncd start
</code></pre>
<p>You can stop the service again by typing:</p>
<pre class="code-pre "><code langs="">sudo service rsyncd stop
</code></pre>
<h2 id="removing-an-installed-port">Removing an Installed Port</h2>

<p>If you have installed a port that you no longer need, you can remove the application from your system using a similar but more straight-forward process.</p>

<p>We can use the <code>deinstall</code> target to remove an application from our system.  Again, change to the directory within the ports tree associated with the application you wish to remove:</p>
<pre class="code-pre "><code langs="">cd /usr/ports/ports-mgmt/portmaster
</code></pre>
<p>You can remove the application from your system by typing:</p>
<pre class="code-pre "><code langs="">sudo make deinstall
</code></pre>
<p>If you would also like to delete the options you configured for this port, you can do so by typing:</p>
<pre class="code-pre "><code langs="">sudo make rmconfig
</code></pre>
<p>To delete the configuration options for this port and all of its dependencies, type:</p>
<pre class="code-pre "><code langs="">sudo make rmconfig-recursive
</code></pre>
<p>If you removed <code>portmaster</code> using the above commands, reinstall it by typing:</p>
<pre class="code-pre "><code langs="">sudo make reinstall distclean
</code></pre>
<h2 id="updating-applications">Updating Applications</h2>

<p>Now that you know how to install or remove programs, we should demonstrate how to keep your applications up-to-date.</p>

<h3 id="checking-updating-file-for-important-update-notes">Checking UPDATING File for Important Update Notes</h3>

<p>At the beginning of this guide, we saved the timestamps for the <code>/usr/ports/UPDATING</code> file before we used <code>portsnap</code> to refresh our ports tree. </p>

<p>The <code>/usr/ports/UPDATING</code> file contains important notes from the ports maintainers about updates and changes that may require additional manual steps by the administrator.  Failure to read this file and apply its advice prior to updating applications can leave your system in an unusable state or affect the functionality of your applications.</p>

<p>First, check the timestamp that we saved to the file in our home directory:</p>
<pre class="code-pre "><code langs="">cat ~/last_update
</code></pre><pre class="code-pre "><code langs="">  File: "/usr/ports/UPDATING"
  Size: 375337       FileType: Regular File
  Mode: (0644/-rw-r--r--)         Uid: (    0/    root)  Gid: (    0/   wheel)
Device: 0,81   Inode: 2011338    Links: 1
Access: Thu Dec 11 22:24:59 2014
Modify: <span class="highlight">Thu Dec 11 15:40:12 2014</span>
Change: <span class="highlight">Thu Dec 11 22:24:59 2014</span>
</code></pre>
<p>Remember, the "Modify" timestamp indicates the last time that the UPDATING file on our system was modified by a port maintainer, and the "Change" timestamp indicates the time of our last sync.  The information above are the old timestamps.  We can tell from this information that we need to pay attention to any entries from December 11th until the current date.</p>

<p>Open the refreshed UPDATING file now:</p>
<pre class="code-pre "><code langs="">less /usr/ports/UPDATING
</code></pre>
<p>The file will look somewhat similar to this:</p>
<pre class="code-pre "><code langs="">This file documents some of the problems you may encounter when upgrading
your ports.  We try our best to minimize these disruptions, but sometimes
they are unavoidable.

You should get into the habit of checking this file for changes each time
you update your ports collection, before attempting any port upgrades.

20150101:
  AFFECTS: users of net/unison and net/unison-nox11
  AUTHOR: madpilot@FreeBSD.org

  Unison has been upgraded to version 2.48, which uses a different wire
  protocol than 2.40 did.  In order to support synchronization with
  other computers where Unison is still at version 2.40, a new port
  net/unison240 has been created.  It provides unison240 and if that is
  GTK2-enabled, also unison240-text.  This unison240 port can be
  installed in parallel with the existing net/unison port.

20141230:
  AFFECTS: users of deskutils/xpad
  AUTHOR: jgh@FreeBSD.org

  deskutils/xpad has been moved to deskutils/xpad3, since 4.x has been around
  for a while.

  Should you wish to stick with legacy branch at this time;

  # portmaster -o deskutils/xpad deskutils/xpad3

. . .
</code></pre>
<p>This file contains every potentially breaking change for every available port going all of the way back to 2008.  You only need to pay attention to the notices that:</p>

<ul>
<li>Have been added since the last time you updated your ports</li>
<li>Involve the ports you have installed on your system</li>
</ul>

<p>So, for this example, we would only need to pay attention to notices that have been added since December 11th involving our installed ports.  If you do not know which ports are installed on your system, you can use <code>portmaster</code> to create a complete list:</p>
<pre class="code-pre "><code langs="">portmaster -l
</code></pre><pre class="code-pre "><code langs="">===>>> Root ports (No dependencies, not depended on)
===>>> dialog4ports-0.1.5_2
===>>> pkg-1.4.0
===>>> pkgconf-0.9.7
===>>> portmaster-3.17.7
===>>> rsync-3.1.1_3
===>>> 5 root ports

===>>> Trunk ports (No dependencies, are depended on)
===>>> ca_root_nss-3.17.3_1
===>>> expat-2.1.0_2

. . .
</code></pre>
<p>The output will be divided into sections according to their dependency relationships.  Use this information to check against the UPDATING notes.</p>

<p>If any manual steps are required, complete those before continuing on with the rest of the update.</p>

<h3 id="checking-for-known-vulnerabilities">Checking for Known Vulnerabilities</h3>

<p>One other consideration to keep in mind when updating is whether the software installed on your system has any known security vulnerabilities.</p>

<p>FreeBSD maintains a vulnerability database that you can check to see whether any of your ports or packages have security problems.  This functionality is included in the <code>pkg</code> tool.  Run a security audit by typing:</p>
<pre class="code-pre "><code langs="">sudo pkg audit -F
</code></pre>
<p>This will download the latest version of the vulnerability database from the FreeBSD project's servers.  It will then check the versions of all of your installed ports or packages and compare them against the entries in the security database.</p>

<p>If any ports or packages installed on your system have known vulnerabilities in the database, you will be alerted.  Typically, these will have at least an up-to-date port available that patches the issue.</p>

<p>Below, we will discuss how to update all of the ports on your system or just a subset.  Regardless of your update strategy, it is essential that you at least update the ports that have known security vulnerabilities.</p>

<h3 id="updating-installed-ports">Updating Installed Ports</h3>

<p>After you have taken care of any manual steps outlined in the UPDATING files, you can update your software.</p>

<p>To see which ports have updates available, you can use the <code>portmaster</code> command with the <code>-L</code> flag:</p>
<pre class="code-pre "><code langs="">portmaster -L
</code></pre><pre class="code-pre "><code langs="">===>>> Root ports (No dependencies, not depended on)
===>>> dialog4ports-0.1.5_2
===>>> pkg-1.4.0
        ===>>> New version available: pkg-1.4.3
===>>> pkgconf-0.9.7
===>>> portmaster-3.17.7
===>>> rsync-3.1.1_3
===>>> 5 root ports

===>>> Trunk ports (No dependencies, are depended on)
===>>> ca_root_nss-3.17.3_1
===>>> expat-2.1.0_2

. . .

===>>> 44 total installed ports
        ===>>> 4 have new versions available
</code></pre>
<p>This provides a similar output to the lowercase variant of the option that we used earlier, but it checks for available updates as well.  Here, we can see that the <code>pkg</code> port has a new version available.  We can see that there are a total of 4 ports that have new versions available.</p>

<p>To upgrade a single port, you can go to the port's directory location within the port tree and reinstall the new version of the software:</p>
<pre class="code-pre "><code langs="">sudo make deinstall reinstall
</code></pre>
<p>You can also accomplish the same thing with the <code>portmaster</code> command.  You must provide the category and port name.  For instance, to upgrade the <code>wget</code> command, we could type:</p>
<pre class="code-pre "><code langs="">sudo portmaster ftp/wget
</code></pre>
<p>The above command can be used to install ports as well.  Many users coming from other backgrounds find <code>portmaster</code> to be a more familiar software management experience than using the <code>make</code> targets we outlined above.</p>

<p>Although it is possible to upgrade ports independently, it is usually best to update all of the software at once.  You can do this with <code>portmaster</code> using the <code>-a</code> flag:</p>
<pre class="code-pre "><code langs="">sudo portmaster -a
</code></pre>
<p>This will update all of the ports on the system to their newest version.  Any new configuration options will be presented to you at the beginning of the process.  If you have any packages installed with <code>pkg</code> with newer versions available through the ports system, these will be updated and transitioned over to ports as well.</p>

<h2 id="conclusion">Conclusion</h2>

<p>By now, you should have a fairly good grasp on how to work with ports on a FreeBSD system.  Ports are extremely flexible, allowing you to easily customize the majority of the applications on your server with little effort. </p>

<p>Many administrators welcome the trade off between compilation time and increased control, but your needs may vary.  However, learning about the ports system is a good investment regardless of your software strategy on FreeBSD.  There are times when critical updates might not have a package available yet, and there are certain pieces of software that cannot be distributed in a packaged format due to licensing restrictions.  These cases necessitate the use of ports regardless of your preferences.</p>

    