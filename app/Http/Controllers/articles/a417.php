<link rel='stylesheet' href='digital_ocean.css'><br><img src=https://community-cdn-digitalocean-com.global.ssl.fastly.net/assets/tutorials/images/large/PackageManagementBasics-twitter.png?1452813692/> <br> 
      <h3 id="introduction">Introduction</h3>

<p>Most modern Unix-like operating systems offer a centralized mechanism for finding and installing software.  Software is usually distributed in the form of <strong>packages</strong>, kept in <strong>repositories</strong>.  Working with packages is known as <strong>package management</strong>.  Packages provide the basic components of an operating system, along with shared libraries, applications, services, and documentation.</p>

<p>A package management system does much more than one-time installation of software.  It also provides tools for upgrading already-installed packages.  Package repositories help to ensure that code has been vetted for use on your system, and that the installed versions of software have been approved by developers and package maintainers.</p>

<p>When configuring servers or development environments, it's often necessary look beyond official repositories.  Packages in the stable release of a distribution may be out of date, especially where new or rapidly-changing software is concerned.  Nevertheless, package management is a vital skill for system administrators and developers, and the wealth of packaged software for major distributions is a tremendous resource.</p>

<p>This guide is intended as a quick reference for the fundamentals of finding, installing, and upgrading packages on a variety of distributions, and should help you translate that knowledge between systems.</p>

<h2 id="package-management-systems-a-brief-overview">Package Management Systems:  A Brief Overview</h2>

<p>Most package systems are built around collections of package files.  A package file is usually an archive which contains compiled binaries and other resources making up the software, along with installation scripts.  Packages also contain valuable metadata, including their <strong>dependencies</strong>, a list of other packages required to install and run them.</p>

<p>While their functionality and benefits are broadly similar, packaging formats and tools vary by platform:</p>

<table class="pure-table"><thead>
<tr>
<th>Operating System</th>
<th>Format</th>
<th>Tool(s)</th>
</tr>
</thead><tbody>
<tr>
<td>Debian</td>
<td><code>.deb</code></td>
<td><code>apt</code>, <code>apt-cache</code>, <code>apt-get</code>, <code>dpkg</code></td>
</tr>
<tr>
<td>Ubuntu</td>
<td><code>.deb</code></td>
<td><code>apt</code>, <code>apt-cache</code>, <code>apt-get</code>, <code>dpkg</code></td>
</tr>
<tr>
<td>CentOS</td>
<td><code>.rpm</code></td>
<td><code>yum</code></td>
</tr>
<tr>
<td>Fedora</td>
<td><code>.rpm</code></td>
<td><code>dnf</code></td>
</tr>
<tr>
<td>FreeBSD</td>
<td>Ports, <code>.txz</code></td>
<td><code>make</code>, <code>pkg</code></td>
</tr>
</tbody></table>

<p>In Debian and systems based on it, like Ubuntu, Linux Mint, and Raspbian, the package format is the <code>.deb</code> file.  APT, the Advanced Packaging Tool, provides commands used for most common operations: Searching repositories, installing collections of packages and their dependencies, and managing upgrades.  APT commands operate as a front-end to the lower-level <code>dpkg</code> utility, which handles the installation of individual <code>.deb</code> files on the local system, and is sometimes invoked directly.</p>

<p>Recent releases of most Debian-derived distributions include the <code>apt</code> command, which offers a concise and unified interface to common operations that have traditionally been handled by the more-specific <code>apt-get</code> and <code>apt-cache</code>.  Its use is optional, but may simplify some tasks.</p>

<p>CentOS, Fedora, and other members of the Red Hat family use RPM files.  In CentOS, <code>yum</code> is used to interact with both individual package files and repositories.</p>

<p>In recent versions of Fedora, <code>yum</code> has been supplanted by <code>dnf</code>, a modernized fork which retains most of <code>yum</code>'s interface.</p>

<p>FreeBSD's binary package system is administered with the <code>pkg</code> command.  FreeBSD also offers the Ports Collection, a local directory structure and tools which allow the user to fetch, compile, and install packages directly from source using Makefiles.  It's usually much more convenient to use <code>pkg</code>, but occasionally a pre-compiled package is unavailable, or you may need to change compile-time options.</p>

<h2 id="update-package-lists">Update Package Lists</h2>

<p>Most systems keep a local database of the packages available from remote repositories.  It's best to update this database before installing or upgrading packages.  As a partial exception to this pattern, <code>yum</code> and <code>dnf</code> will check for updates before performing some operations, but you can ask them at any time whether updates are available.</p>

<table class="pure-table"><thead>
<tr>
<th>System</th>
<th>Command</th>
</tr>
</thead><tbody>
<tr>
<td>Debian / Ubuntu</td>
<td><code>sudo apt-get update</code></td>
</tr>
<tr>
<td></td>
<td><code>sudo apt update</code></td>
</tr>
<tr>
<td>CentOS</td>
<td><code>yum check-update</code></td>
</tr>
<tr>
<td>Fedora</td>
<td><code>dnf check-update</code></td>
</tr>
<tr>
<td>FreeBSD Packages</td>
<td><code>sudo pkg update</code></td>
</tr>
<tr>
<td>FreeBSD Ports</td>
<td><code>sudo portsnap fetch update</code></td>
</tr>
</tbody></table>

<h2 id="upgrade-installed-packages">Upgrade Installed Packages</h2>

<p>Making sure that all of the installed software on a machine stays up to date would be an enormous undertaking without a package system.  You would have to track upstream changes and security alerts for hundreds of different packages.  While a package manager doesn't solve every problem you'll encounter when upgrading software, it does enable you to maintain most system components with a few commands.</p>

<p>On FreeBSD, upgrading installed ports can introduce breaking changes or require manual configuration steps.  It's best to read <code>/usr/ports/UPDATING</code> before upgrading with <code>portmaster</code>.</p>

<table class="pure-table"><thead>
<tr>
<th>System</th>
<th>Command</th>
<th>Notes</th>
</tr>
</thead><tbody>
<tr>
<td>Debian / Ubuntu</td>
<td><code>sudo apt-get upgrade</code></td>
<td>Only upgrades installed packages, where possible.</td>
</tr>
<tr>
<td></td>
<td><code>sudo apt-get dist-upgrade</code></td>
<td>May add or remove packages to satisfy new dependencies.</td>
</tr>
<tr>
<td></td>
<td><code>sudo apt upgrade</code></td>
<td>Like <code>apt-get upgrade</code>.</td>
</tr>
<tr>
<td></td>
<td><code>sudo apt full-upgrade</code></td>
<td>Like <code>apt-get dist-upgrade</code>.</td>
</tr>
<tr>
<td>CentOS</td>
<td><code>sudo yum update</code></td>
<td></td>
</tr>
<tr>
<td>Fedora</td>
<td><code>sudo dnf upgrade</code></td>
<td></td>
</tr>
<tr>
<td>FreeBSD Packages</td>
<td><code>sudo pkg upgrade</code></td>
<td></td>
</tr>
<tr>
<td>FreeBSD Ports</td>
<td><code>less /usr/ports/UPDATING</code></td>
<td>Uses <code>less</code> to view update notes for ports (use arrow keys to scroll, press <strong>q</strong> to quit).</td>
</tr>
<tr>
<td></td>
<td><code>cd /usr/ports/ports-mgmt/portmaster && sudo make install && sudo portmaster -a</code></td>
<td>Installs <code>portmaster</code> and uses it to update installed ports.</td>
</tr>
</tbody></table>

<h2 id="find-a-package">Find a Package</h2>

<p>Most distributions offer a graphical or menu-driven front end to package collections.  These can be a good way to browse by category and discover new software.  Often, however, the quickest and most effective way to locate a package is to search with command-line tools.</p>

<table class="pure-table"><thead>
<tr>
<th>System</th>
<th>Command</th>
<th>Notes</th>
</tr>
</thead><tbody>
<tr>
<td>Debian / Ubuntu</td>
<td><code>apt-cache search <span class="highlight">search_string</span></code></td>
<td></td>
</tr>
<tr>
<td></td>
<td><code>apt search <span class="highlight">search_string</span></code></td>
<td></td>
</tr>
<tr>
<td>CentOS</td>
<td><code>yum search <span class="highlight">search_string</span></code></td>
<td></td>
</tr>
<tr>
<td></td>
<td><code>yum search all <span class="highlight">search_string</span></code></td>
<td>Searches all fields, including description.</td>
</tr>
<tr>
<td>Fedora</td>
<td><code>dnf search <span class="highlight">search_string</span></code></td>
<td></td>
</tr>
<tr>
<td></td>
<td><code>dnf search all <span class="highlight">search_string</span></code></td>
<td>Searches all fields, including description.</td>
</tr>
<tr>
<td>FreeBSD Packages</td>
<td><code>pkg search <span class="highlight">search_string</span></code></td>
<td>Searches by name.</td>
</tr>
<tr>
<td></td>
<td><code>pkg search -f <span class="highlight">search_string</span></code></td>
<td>Searches by name, returning full descriptions.</td>
</tr>
<tr>
<td></td>
<td><code>pkg search -D <span class="highlight">search_string</span></code></td>
<td>Searches description.</td>
</tr>
<tr>
<td>FreeBSD Ports</td>
<td><code>cd /usr/ports && make search name=<span class="highlight">package</span></code></td>
<td>Searches by name.</td>
</tr>
<tr>
<td></td>
<td><code>cd /usr/ports && make search key=<span class="highlight">search_string</span></code></td>
<td>Searches comments, descriptions, and dependencies.</td>
</tr>
</tbody></table>

<h2 id="view-info-about-a-specific-package">View Info About a Specific Package</h2>

<p>When deciding what to install, it's often helpful to read detailed descriptions of packages.  Along with human-readable text, these often include metadata like version numbers and a list of the package's dependencies.</p>

<table class="pure-table"><thead>
<tr>
<th>System</th>
<th>Command</th>
<th>Notes</th>
</tr>
</thead><tbody>
<tr>
<td>Debian / Ubuntu</td>
<td><code>apt-cache show <span class="highlight">package</span></code></td>
<td>Shows locally-cached info about a package.</td>
</tr>
<tr>
<td></td>
<td><code>apt show <span class="highlight">package</span></code></td>
<td></td>
</tr>
<tr>
<td></td>
<td><code>dpkg -s <span class="highlight">package</span></code></td>
<td>Shows the current installed status of a package.</td>
</tr>
<tr>
<td>CentOS</td>
<td><code>yum info <span class="highlight">package</span></code></td>
<td></td>
</tr>
<tr>
<td></td>
<td><code>yum deplist <span class="highlight">package</span></code></td>
<td>Lists dependencies for a package.</td>
</tr>
<tr>
<td>Fedora</td>
<td><code>dnf info <span class="highlight">package</span></code></td>
<td></td>
</tr>
<tr>
<td></td>
<td><code>dnf repoquery --requires <span class="highlight">package</span></code></td>
<td>Lists dependencies for a package.</td>
</tr>
<tr>
<td>FreeBSD Packages</td>
<td><code>pkg info <span class="highlight">package</span></code></td>
<td>Shows info for an installed package.</td>
</tr>
<tr>
<td>FreeBSD Ports</td>
<td><code>cd /usr/ports/<span class="highlight">category</span>/<span class="highlight">port</span> && cat pkg-descr</code></td>
<td></td>
</tr>
</tbody></table>

<h2 id="install-a-package-from-repositories">Install a Package from Repositories</h2>

<p>Once you know the name of a package, you can usually install it and its dependencies with a single command.  In general, you can supply multiple packages to install simply by listing them all.</p>

<table class="pure-table"><thead>
<tr>
<th>System</th>
<th>Command</th>
<th>Notes</th>
</tr>
</thead><tbody>
<tr>
<td>Debian / Ubuntu</td>
<td><code>sudo apt-get install <span class="highlight">package</span></code></td>
<td></td>
</tr>
<tr>
<td></td>
<td><code>sudo apt-get install <span class="highlight">package1 package2 ...</span></code></td>
<td>Installs all listed packages.</td>
</tr>
<tr>
<td></td>
<td><code>sudo apt-get install -y <span class="highlight">package</span></code></td>
<td>Assumes "yes" where <code>apt</code> would usually prompt to continue.</td>
</tr>
<tr>
<td></td>
<td><code>sudo apt install <span class="highlight">package</span></code></td>
<td>Displays a colored progress bar.</td>
</tr>
<tr>
<td>CentOS</td>
<td><code>sudo yum install <span class="highlight">package</span></code></td>
<td></td>
</tr>
<tr>
<td></td>
<td><code>sudo yum install <span class="highlight">package1 package2 ...</span></code></td>
<td>Installs all listed packages.</td>
</tr>
<tr>
<td></td>
<td><code>sudo yum install -y <span class="highlight">package</span></code></td>
<td>Assumes "yes" where <code>yum</code> would usually prompt to continue.</td>
</tr>
<tr>
<td>Fedora</td>
<td><code>sudo dnf install <span class="highlight">package</span></code></td>
<td></td>
</tr>
<tr>
<td></td>
<td><code>sudo dnf install <span class="highlight">package1 package2 ...</span></code></td>
<td>Installs all listed packages.</td>
</tr>
<tr>
<td></td>
<td><code>sudo dnf install -y <span class="highlight">package</span></code></td>
<td>Assumes "yes" where <code>dnf</code> would usually prompt to continue.</td>
</tr>
<tr>
<td>FreeBSD Packages</td>
<td><code>sudo pkg install <span class="highlight">package</span></code></td>
<td></td>
</tr>
<tr>
<td></td>
<td><code>sudo pkg install <span class="highlight">package1 package2 ...</span></code></td>
<td>Installs all listed packages.</td>
</tr>
<tr>
<td>FreeBSD Ports</td>
<td><code>cd /usr/ports/<span class="highlight">category</span>/<span class="highlight">port</span> && sudo make install</code></td>
<td>Builds and installs a port from source.</td>
</tr>
</tbody></table>

<h2 id="install-a-package-from-the-local-filesystem">Install a Package from the Local Filesystem</h2>

<p>Sometimes, even though software isn't officially packaged for a given operating system, a developer or vendor will offer package files for download.  You can usually retrieve these with your web browser, or via <code>curl</code> on the command line.  Once a package is on the target system, it can often be installed with a single command.</p>

<p>On Debian-derived systems, <code>dpkg</code> handles individual package files.  If a package has unmet dependencies, <code>gdebi</code> can often be used to retrieve them from official repositories.</p>

<p>On CentOS and Fedora systems, <code>yum</code> and <code>dnf</code> are used to install individual files, and will also handle needed dependencies.</p>

<table class="pure-table"><thead>
<tr>
<th>System</th>
<th>Command</th>
<th>Notes</th>
</tr>
</thead><tbody>
<tr>
<td>Debian / Ubuntu</td>
<td><code>sudo dpkg -i <span class="highlight">package.deb</span></code></td>
<td></td>
</tr>
<tr>
<td></td>
<td><code>sudo apt-get install -y <span class="highlight">gdebi</span> && sudo gdebi <span class="highlight">package.deb</span></code></td>
<td>Installs and uses <code>gdebi</code> to install <code><span class="highlight">package.deb</span></code> and retrieve any missing dependencies.</td>
</tr>
<tr>
<td>CentOS</td>
<td><code>sudo yum install <span class="highlight">package.rpm</span></code></td>
<td></td>
</tr>
<tr>
<td>Fedora</td>
<td><code>sudo dnf install <span class="highlight">package.rpm</span></code></td>
<td></td>
</tr>
<tr>
<td>FreeBSD Packages</td>
<td><code>sudo pkg add <span class="highlight">package.txz</span></code></td>
<td></td>
</tr>
<tr>
<td></td>
<td><code>sudo pkg add -f <span class="highlight">package.txz</span></code></td>
<td>Installs package even if already installed.</td>
</tr>
</tbody></table>

<h2 id="remove-one-or-more-installed-packages">Remove One or More Installed Packages</h2>

<p>Since a package manager knows what files are provided by a given package, it can usually remove them cleanly from a system if the software is no longer needed.</p>

<table class="pure-table"><thead>
<tr>
<th>System</th>
<th>Command</th>
<th>Notes</th>
</tr>
</thead><tbody>
<tr>
<td>Debian / Ubuntu</td>
<td><code>sudo apt-get remove <span class="highlight">package</span></code></td>
<td></td>
</tr>
<tr>
<td></td>
<td><code>sudo apt remove <span class="highlight">package</span></code></td>
<td></td>
</tr>
<tr>
<td></td>
<td><code>sudo apt-get autoremove</code></td>
<td>Removes unneeded packages.</td>
</tr>
<tr>
<td>CentOS</td>
<td><code>sudo yum remove <span class="highlight">package</span></code></td>
<td></td>
</tr>
<tr>
<td>Fedora</td>
<td><code>sudo dnf erase <span class="highlight">package</span></code></td>
<td></td>
</tr>
<tr>
<td>FreeBSD Packages</td>
<td><code>sudo pkg delete <span class="highlight">package</span></code></td>
<td></td>
</tr>
<tr>
<td></td>
<td><code>sudo pkg autoremove</code></td>
<td>Removes unneeded packages.</td>
</tr>
<tr>
<td>FreeBSD Ports</td>
<td><code>sudo pkg delete <span class="highlight">package</span></code></td>
<td></td>
</tr>
<tr>
<td></td>
<td><code>cd /usr/ports/<span class="highlight">path_to_port</span> && make deinstall</code></td>
<td>De-installs an installed port.</td>
</tr>
</tbody></table>

<h2 id="the-apt-command">The <code>apt</code> Command</h2>

<p>Administrators of Debian-family distributions are generally familiar with <code>apt-get</code> and <code>apt-cache</code>.  Less widely known is the simplified <code>apt</code> interface, designed specifically for interactive use.</p>

<table class="pure-table"><thead>
<tr>
<th>Traditional Command</th>
<th><code>apt</code> Equivalent</th>
</tr>
</thead><tbody>
<tr>
<td><code>apt-get update</code></td>
<td><code>apt update</code></td>
</tr>
<tr>
<td><code>apt-get dist-upgrade</code></td>
<td><code>apt full-upgrade</code></td>
</tr>
<tr>
<td><code>apt-cache search <span class="highlight">string</span></code></td>
<td><code>apt search <span class="highlight">string</span></code></td>
</tr>
<tr>
<td><code>apt-get install <span class="highlight">package</span></code></td>
<td><code>apt install <span class="highlight">package</span></code></td>
</tr>
<tr>
<td><code>apt-get remove <span class="highlight">package</span></code></td>
<td><code>apt remove <span class="highlight">package</span></code></td>
</tr>
<tr>
<td><code>apt-get purge <span class="highlight">package</span></code></td>
<td><code>apt purge <span class="highlight">package</span></code></td>
</tr>
</tbody></table>

<p>While <code>apt</code> is often a quicker shorthand for a given operation, it's not intended as a complete replacement for the traditional tools, and its interface may change between versions to improve usability.  If you are using package management commands inside a script or a shell pipeline, it's a good idea to stick with <code>apt-get</code> and <code>apt-cache</code>.</p>

<h2 id="get-help">Get Help</h2>

<p>In addition to web-based documentation, keep in mind that Unix manual pages (usually referred to as <strong>man pages</strong>) are available for most commands from the shell.  To read a page, use <code>man</code>:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">man <span class="highlight">page</span>
</li></ul></code></pre>
<p>In <code>man</code>, you can navigate with the arrow keys.  Press <strong>/</strong> to search for text within the page, and <strong>q</strong> to quit.</p>

<table class="pure-table"><thead>
<tr>
<th>System</th>
<th>Command</th>
<th>Notes</th>
</tr>
</thead><tbody>
<tr>
<td>Debian / Ubuntu</td>
<td><code>man apt-get</code></td>
<td>Updating the local package database and working with packages.</td>
</tr>
<tr>
<td></td>
<td><code>man apt-cache</code></td>
<td>Querying the local package database.</td>
</tr>
<tr>
<td></td>
<td><code>man dpkg</code></td>
<td>Working with individual package files and querying installed packages.</td>
</tr>
<tr>
<td></td>
<td><code>man apt</code></td>
<td>Working with a more concise, user-friendly interface to most basic operations.</td>
</tr>
<tr>
<td>CentOS</td>
<td><code>man yum</code></td>
<td></td>
</tr>
<tr>
<td>Fedora</td>
<td><code>man dnf</code></td>
<td></td>
</tr>
<tr>
<td>FreeBSD Packages</td>
<td><code>man pkg</code></td>
<td>Working with pre-compiled binary packages.</td>
</tr>
<tr>
<td>FreeBSD Ports</td>
<td><code>man ports</code></td>
<td>Working with the Ports Collection.</td>
</tr>
</tbody></table>

<h2 id="conclusion-and-further-reading">Conclusion and Further Reading</h2>

<p>This guide provides an overview of basic operations that can be cross-referenced between systems, but only scratches the surface of a complex topic.  For greater detail on a given system, you can consult the following resources:</p>

<ul>
<li><a href="https://indiareads/community/tutorials/ubuntu-and-debian-package-management-essentials">This guide</a> covers Ubuntu and Debian package management in detail.</li>
<li>There's an <a href="https://www.centos.org/docs/5/html/yum/">official CentOS guide to managing software with <code>yum</code></a>.</li>
<li>There's a <a href="https://fedoraproject.org/wiki/Dnf">Fedora wiki page about <code>dnf</code></a>, and an <a href="https://dnf.readthedocs.org/en/latest/index.html">official manual for <code>dnf</code> itself</a>.</li>
<li><a href="https://indiareads/community/tutorials/how-to-manage-packages-on-freebsd-10-1-with-pkg">This guide</a> covers FreeBSD package management using <code>pkg</code>.</li>
<li>The <a href="https://www.freebsd.org/doc/handbook/">FreeBSD Handbook</a> contains a <a href="https://www.freebsd.org/doc/handbook/ports-using.html">section on using the Ports Collection</a>.</li>
</ul>

    