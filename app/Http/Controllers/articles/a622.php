<link rel='stylesheet' href='digital_ocean.css'><br><img src=https://community-cdn-digitalocean-com.global.ssl.fastly.net/assets/tutorials/images/large/java_FreeBSD10-1.png?1441228873/> <br> 
      <h3 id="introduction">Introduction</h3>

<p>Java is a popular software platform that allows you to run Java applications and applets.</p>

<p>This tutorial covers how to install the following Java releases on FreeBSD 10.1, using packages and ports:</p>

<ul>
<li>OpenJDK 7 JDK <em>(default)</em></li>
<li>OpenJDK 8 JRE / JDK</li>
<li>OpenJDK 6 JRE / JDK</li>
</ul>

<p>This guide does not cover the installation of Oracle Java because only the 32-bit version is supported on FreeBSD, through the Linux Binary Compatibility feature. Additionally, OpenJDK satisfies the Java needs of most users.</p>

<h2 id="prerequisites">Prerequisites</h2>

<p>Before you begin this guide, you should have a FreeBSD 10.1 server. Also, you must connect to your FreeBSD server as a user with superuser privileges (i.e. is allowed to use <code>sudo</code> or change to the root user).</p>

<h2 id="variations-of-java">Variations of Java</h2>

<p>There are two different Java packages that can be installed: the Java Runtime Environment (JRE) and the Java Development Kit (JDK). JRE is an implementation of the Java Virtual Machine (JVM), which allows you to run compiled Java applications and applets. The JDK includes the JRE and other software that is required for writing, developing, and compiling Java applications and applets.</p>

<p>You may install various versions and releases of Java on a single system, but most people only need one installation. With that in mind, try to only install the version of Java that you need to run or develop your application(s).</p>

<h2 id="install-openjdk-via-packages">Install OpenJDK via Packages</h2>

<p>Using packages is an easy way to install the various releases of OpenJDK on your FreeBSD system.</p>

<h3 id="list-available-openjdk-packages">List Available OpenJDK Packages</h3>

<p>To see the list of OpenJDK releases available via packages, use this command:</p>
<pre class="code-pre "><code langs="">pkg search ^openjdk
</code></pre>
<p>You should see output that looks like this (possibly with different version numbers):</p>
<pre class="code-pre "><code langs=""><span class="highlight">openjdk</span>-7.71.14_1,1
<span class="highlight">openjdk6</span>-b33,1
<span class="highlight">openjdk6-jre</span>-b33,1
<span class="highlight">openjdk8</span>-8.25.17_3
<span class="highlight">openjdk8-jre</span>-8.25.17_3
</code></pre>
<p>The package names are highlighted in red, and are followed by their versions. As you can see the following packages are available:</p>

<ul>
<li><code>openjdk</code>: The default OpenJDK package, which happens to be OpenJDK 7 JDK</li>
<li><code>openjdk6</code>: The OpenJDK 6 JDK</li>
<li><code>openjdk6-jre</code>: The OpenJDK 6 JRE</li>
<li><code>openjdk8</code>: The OpenJDK 8 JDK</li>
<li><code>openjdk8-jre</code>: The OpenJDK 8 JRE</li>
</ul>

<h3 id="how-to-install-an-openjdk-package">How To Install an OpenJDK Package</h3>

<p>After you decide which release of OpenJDK you want, let's install it.</p>

<p>To install an OpenJDK package, use the <code>pkg install</code> command followed by the package you want to install. For example, to install OpenJDK 7 JDK, <code>openjdk</code>, run this command (substitute the highlighted package name with the one that you want to install):</p>
<pre class="code-pre "><code langs="">sudo pkg install <span class="highlight">openjdk</span>
</code></pre>
<p>Enter <code>y</code> at the confirmation prompt.</p>

<p>This installs OpenJDK and the packages it depends on.</p>

<p>This OpenJDK implementation requires a few file systems to be mounted for full functionality. Run these commands to perform the required mounts immediately:</p>
<pre class="code-pre "><code langs="">sudo mount -t fdescfs fdesc /dev/fd
sudo mount -t procfs proc /proc
</code></pre>
<p>To make this change permanent, we must add these mount points to the <code>/etc/fstab</code> file. Open the file to edit now:</p>
<pre class="code-pre "><code langs="">sudo vi /etc/fstab
</code></pre>
<p>Insert the following mount information into the file:</p>
<pre class="code-pre "><code langs="">fdesc   /dev/fd     fdescfs     rw  0   0
proc    /proc       procfs      rw  0   0
</code></pre>
<p>Save and exit.</p>

<p>Lastly, you will want to rehash to be sure that you can use your new Java binaries immediately:</p>
<pre class="code-pre "><code langs="">rehash
</code></pre>
<p>The OpenJDK package that you selected is now installed and ready to be used!</p>

<h2 id="install-openjdk-via-ports">Install OpenJDK via Ports</h2>

<p>Using ports is a flexible way to build and install the various releases of OpenJDK on your FreeBSD system. Installing Java this way allows you to customize your software build but it takes much longer than installing via packages.</p>

<h3 id="list-available-openjdk-ports">List Available OpenJDK Ports</h3>

<p>To see the list of OpenJDK releases available via ports, use this command:</p>
<pre class="code-pre "><code langs="">cd /usr/ports/java && ls -d openjdk*
</code></pre>
<p>You should see output that looks like this:</p>
<pre class="code-pre "><code langs="">openjdk6    openjdk6-jre    openjdk7    openjdk8    openjdk8-jre
</code></pre>
<p>The package names correspond with the release of Java that they provide. Note that the <code>-jre</code> suffix marks the JRE ports, while the lack of the suffix indicates the JDK ports.</p>

<h3 id="how-to-install-an-openjdk-port">How To Install an OpenJDK Port</h3>

<p>After you decide which release of OpenJDK you want, let's install it.</p>

<p>To build and install an OpenJDK port, use the <code>portmaster java/</code> command followed by the port you want to install. For example, to install OpenJDK 7 JDK, <code>openjdk7</code>, run this command (substitute the highlighted port name with the one that you want to install):</p>
<pre class="code-pre "><code langs="">sudo portmaster java/<span class="highlight">openjdk7</span>
</code></pre>
<p>You will see a series of prompts asking for the options and libraries that you wish to build your Java port and its dependencies with. You may accept the defaults or customize it to your needs.</p>

<p>After you answer all of the prompts, the OpenJDK port and its dependencies will be built and installed.</p>

<p>This OpenJDK implementation requires a few file systems to be mounted for full functionality. Run these commands to perform the required mounts immediately:</p>
<pre class="code-pre "><code langs="">sudo mount -t fdescfs fdesc /dev/fd
sudo mount -t procfs proc /proc
</code></pre>
<p>To make this change permanent, we must add these mount points to the <code>/etc/fstab</code> file. Open the file to edit now:</p>
<pre class="code-pre "><code langs="">sudo vi /etc/fstab
</code></pre>
<p>Insert the following mount information into the file:</p>
<pre class="code-pre "><code langs="">fdesc   /dev/fd     fdescfs     rw  0   0
proc    /proc       procfs      rw  0   0
</code></pre>
<p>Save and exit.</p>

<p>Lastly, you will want to rehash to be sure that you can use your new Java binaries immediately:</p>
<pre class="code-pre "><code langs="">rehash
</code></pre>
<p>The OpenJDK port that you selected is now installed and ready to be used!</p>

<h2 id="conclusion">Conclusion</h2>

<p>Congratulations! You are now able to run and develop your Java applications.</p>

<p>If you're interested in learning more about installing additional software on your FreeBSD servers, check out these tutorials about Packages and Ports:</p>

<ul>
<li><a href="https://indiareads/community/tutorials/how-to-manage-packages-on-freebsd-10-1-with-pkg">How To Manage Packages on FreeBSD 10.1 with Pkg</a></li>
<li><a href="https://indiareads/community/tutorials/how-to-install-and-manage-ports-on-freebsd-10-1">How To Install and Manage Ports on FreeBSD 10.1</a></li>
</ul>

    