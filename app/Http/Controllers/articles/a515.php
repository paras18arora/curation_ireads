<link rel='stylesheet' href='digital_ocean.css'><br> <br> 
      <h3 id="introduction">Introduction</h3>

<p>FreeBSD's binary package manager, <strong>pkg</strong>, can be used to easily manage the installation of pre-compiled applications, the FreeBSD equivalent Debian and RPM packages. When compared with the other prevalent method of software installation on FreeBSD, compiling <strong>ports</strong> with the Ports Collection, using packages provides a simpler and faster alternative that works in many situations. Packages, however, are not as flexible as ports because package installations cannot be customized—if you have the need to customize the compilation options of your software installations, use <a href="https://indiareads/community/tutorials/how-to-install-and-manage-ports-on-freebsd-10-1">ports</a> instead of packages.</p>

<p>In this tutorial, we will show you how to manage packages on FreeBSD 10.1. This includes installing and deleting packages, among other related tasks.</p>

<h2 id="prerequisites">Prerequisites</h2>

<p>To use the commands in this tutorial, you must have <strong>root</strong> access to a FreeBSD server. That is, you must be able to log in to the server as root or another user that has superuser privileges via the sudo command. If you are planning on using root, you may omit the <code>sudo</code> portion of the example commands.</p>

<h2 id="how-to-install-new-packages-with-pkg">How To Install New Packages with Pkg</h2>

<p>If you know the name of the package that you want to install, you can install it by using the <code>pkg</code> command like this:</p>
<pre class="code-pre "><code langs="">sudo pkg install <span class="highlight">package_name</span>
</code></pre>
<p>You may also specify multiple packages to install, separated by spaces, like this:</p>
<pre class="code-pre "><code langs="">sudo pkg install <span class="highlight">package1 package2 ...</span>
</code></pre>
<p>As an example, let's install Nginx, a popular web server, with <code>pkg</code>:</p>
<pre class="code-pre "><code langs="">sudo pkg install nginx
</code></pre>
<p>Running this command will initiate the installation of the package you specified. First, your system will check for package repository catalog updates. If it is already fully updated, then search for the specified package. If the package is found, the package and the packages it depends on will be listed. A confirmation prompt will then appear.</p>

<p>In this case, only the <code>nginx</code> package will be installed. Respond to the prompt with <code>y</code> to confirm:</p>
<pre class="code-pre "><code langs="">New packages to be INSTALLED:
    nginx: 1.6.2_1,2

The process will require 654 KB more space.
244 KB to be downloaded.

Proceed with this action? [y/N]: <span class="highlight">y</span>
</code></pre>
<p>After confirming the package installation, the listed package(s) will be downloaded and installed on the system. Some packages will display important post-installation information or instructions regarding the use of the application, after the installation—be sure to follow any post-installation notes.</p>

<p>If you are using the default shell, <code>tcsh</code>, or <code>csh</code>, you should rebuild the list of binaries in your <code>PATH</code> with this command:</p>
<pre class="code-pre "><code langs="">rehash
</code></pre>
<p>It is also important to note that applications that are <em>services</em> do not automatically start, nor are they enabled as a service, after being installed. Let's look at how to run services now.</p>

<h2 id="how-to-run-services">How To Run Services</h2>

<p>On FreeBSD, services that are installed with packages provide a service initialization script in <code>/usr/local/etc/rc.d</code>. In the example case of Nginx, which runs as a service, the startup script is called <code>nginx</code>. Note that you should substitute the appropriate service script name, instead of the highlighted "nginx", when running the commands.</p>

<p>To demonstrate what happens if you attempt to start a service that is not enabled, try using the <code>service</code> command to start your software immediately after installing it:</p>
<pre class="code-pre "><code langs="">sudo service <span class="highlight">nginx</span> start
</code></pre>
<p>The service will not start and you will encounter a message that looks like the following:</p>
<pre class="code-pre "><code langs="">Cannot 'start' <span class="highlight">nginx</span>. Set <span class="highlight">nginx</span>_enable to YES in /etc/rc.conf or use 'onestart' instead of 'start'.
</code></pre>
<p>To enable the service, follow the directions in the message and add the following line to <code>/etc/rc.conf</code>:</p>
<pre class="code-pre "><code langs=""><span class="highlight">nginx</span>_enable="YES"
</code></pre>
<p>You may either open <code>/etc/rc.conf</code> in an editor and add the line, or use the <code>sysrc</code> utility to update the file like this:</p>
<pre class="code-pre "><code langs="">sudo sysrc <span class="highlight">nginx</span>_enable=yes
</code></pre>
<p>Now the service is enabled. It will start when your system boots, and you may use the <code>start</code> subcommand that was attempted earlier:</p>
<pre class="code-pre "><code langs="">sudo service <span class="highlight">nginx</span> start
</code></pre>
<p>If you want to run the service once, without enabling it, you may use the <code>onestart</code> subcommand. Starting a service in this fashion will run the startup script immediately, but it will not be started upon system boot. Try it now:</p>
<pre class="code-pre "><code langs="">sudo service <span class="highlight">nginx</span> onestart
</code></pre>
<p>Using the <code>onestart</code> subcommand is useful if you want to test the configuration of your services before enabling them.</p>

<h2 id="how-to-view-package-information-with-pkg">How To View Package Information with Pkg</h2>

<p>To view information about <strong>installed</strong> packages, you may use the <code>pkg info</code> command, like this:</p>
<pre class="code-pre "><code langs="">pkg info <span class="highlight">package_name</span>
</code></pre>
<p>This will print various information about the specified package including a description of the software, the options it was compiled with, and a list of the libraries that it depends on.</p>

<h2 id="how-to-upgrade-installed-packages-with-pkg">How To Upgrade Installed Packages with Pkg</h2>

<p>You may install the latest available versions of your system's installed packages with this command:</p>
<pre class="code-pre "><code langs="">sudo pkg upgrade
</code></pre>
<p>Running this command will compare your installed packages with the versions in the repository catalog, and print a list of the packages that can be updated to a newer version:</p>
<pre class="code-pre "><code langs="">Updating FreeBSD repository catalogue...
FreeBSD repository is up-to-date.
All repositories are up-to-date.
Checking for upgrades (2 candidates): 100%
Processing candidates (2 candidates): 100%
The following 2 packages will be affected (of 0 checked):

Installed packages to be UPGRADED:
    python27: 2.7.8_6 -> 2.7.9
    perl5: 5.18.4_10 -> 5.18.4_11

The process will require 2 MB more space.
23 MB to be downloaded.

Proceed with this action? [y/N]: <span class="highlight">y</span>
</code></pre>
<p>Respond with a <code>y</code> to the prompt to proceed to upgrade the listed packages.</p>

<h2 id="how-to-delete-packages-with-pkg">How To Delete Packages with Pkg</h2>

<p>If you know the name of the package that you want to delete, you can delete it by using the <code>pkg</code> command like this:</p>
<pre class="code-pre "><code langs="">sudo pkg delete <span class="highlight">package_name</span>
</code></pre>
<p>You may also specify multiple packages to delete, separated by spaces, like this:</p>
<pre class="code-pre "><code langs="">sudo pkg delete <span class="highlight">package1 package2 ...</span>
</code></pre>
<p>Let's delete Nginx package that we installed earlier:</p>
<pre class="code-pre "><code langs="">sudo pkg delete <span class="highlight">nginx</span>
</code></pre>
<p>You will see a message like the following, with a confirmation prompt:</p>
<pre class="code-pre "><code langs="">Checking integrity... done (0 conflicting)
Deinstallation has been requested for the following 1 packages (of 0 packages in the universe):

Installed packages to be REMOVED:
    nginx-1.6.2_1,2

The operation will free 654 KB.

Proceed with deinstalling packages? [y/N]: <span class="highlight">y</span>
</code></pre>
<p>Respond to the prompt with <code>y</code> to confirm the package delete action.</p>

<h2 id="how-to-remove-unused-dependencies">How To Remove Unused Dependencies</h2>

<p>If you delete a package that installed dependencies, the dependencies will still be installed. To remove the packages that are no longer required by any installed packages, run this command:</p>
<pre class="code-pre "><code langs="">sudo pkg autoremove
</code></pre>
<p>The list of packages that will be removed will be printed followed by a prompt. Respond <code>y</code> to the confirmation prompt if you want to delete the listed packages.</p>

<h2 id="how-to-find-packages-with-pkg">How To Find Packages with Pkg</h2>

<p>To find binary packages that are available in the repository, use the <code>pkg search</code> command.</p>

<h3 id="by-package-name">By Package Name</h3>

<p>The most basic way to search is by package name. If you want to search on package name, use the command like this:</p>
<pre class="code-pre "><code langs="">pkg search <span class="highlight">package_name</span>
</code></pre>
<p>For example, to search for packages with "nginx" in the name, use this command:</p>
<pre class="code-pre "><code langs="">pkg search nginx
</code></pre>
<p>This will print a list of the packages, including version numbers, with "nginx" in the name:</p>
<pre class="code-pre "><code langs="">nginx-1.6.2_1,2
nginx-devel-1.7.8
p5-Nginx-ReadBody-0.07_1
p5-Nginx-Simple-0.07_1
p5-Test-Nginx-0.24
</code></pre>
<p>If you want to read the detailed package information about the listed packages, use the <code>-f</code> option like this:</p>
<pre class="code-pre "><code langs="">pkg search -f <span class="highlight">package_name</span>
</code></pre>
<p>This will print the package information about each package that matches the specified package name.</p>

<h3 id="by-description">By Description</h3>

<p>If you're not sure of the name of the package you want to install, you may also search the descriptions of packages that are available in the repository by specifying the <code>-D</code> option. By default, the pattern match is not case-sensitive:</p>
<pre class="code-pre "><code langs="">pkg search -D <span class="highlight">pattern</span>
</code></pre>
<p>For example, to search for all packages with "java" in the description, use the command like this:</p>
<pre class="code-pre "><code langs="">pkg search -D java
</code></pre>
<p>This will print the names of all of available packages with the specified pattern in the description field, along with the description.</p>

<h2 id="how-to-learn-more-about-using-pkg">How To Learn More About Using Pkg</h2>

<p>Pkg is a very flexible utility that can be used in many ways that are not covered in this tutorial. Luckily, it provides an easy way to look up which options and subcommands are available, and what they do.</p>

<p>To print the available options and subcommands, use this command:</p>
<pre class="code-pre "><code langs="">pkg help
</code></pre>
<p>To read the man pages for the various subcommands, use <code>pkg help</code> and specify the command you want to learn about, like this:</p>
<pre class="code-pre "><code langs="">pkg help <span class="highlight">subcommand</span>
</code></pre>
<p>For example, if you want to learn more about using <code>pkg search</code>, enter this command:</p>
<pre class="code-pre "><code langs="">pkg help search
</code></pre>
<p>This will pull up a man page that details how to use <code>pkg search</code>.</p>

<h2 id="conclusion">Conclusion</h2>

<p>You should now know enough about using <code>pkg</code> to manage binary packages on your FreeBSD server.</p>

<p>If you want to learn more about managing software on your FreeBSD server, be sure to read up on <strong>ports</strong> with this tutorial: <a href="https://indiareads/community/tutorials/how-to-install-and-manage-ports-on-freebsd-10-1">How To Install and Manage Ports on FreeBSD 10.1</a>.</p>

    