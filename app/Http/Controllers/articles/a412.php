<link rel='stylesheet' href='digital_ocean.css'><br><img src=https://community-cdn-digitalocean-com.global.ssl.fastly.net/assets/tutorials/images/large/ubuntu16.04_twitter.png?1461253629/> <br> 
      <h3 id="introduction">Introduction</h3>

<p>The Ubuntu operating system's most recent Long Term Support version, version 16.04 (Xenial Xerus), was released on April 21, 2016.</p>

<p>This guide is intended as a brief overview of new features and significant changes to the system as a whole, since 14.04 LTS, from the perspective of server system administration.  It draws on <a href="https://wiki.ubuntu.com/XenialXerus/ReleaseNotes">the official Xenial Xerus release notes</a>, along with a variety of other sources.</p>

<h2 id="what-is-a-long-term-support-release">What is a Long Term Support Release?</h2>

<p>While new Ubuntu Desktop and Server releases appear every six months, LTS versions are released every two years, and are guaranteed support from Canonical for five years after release.  This means that they constitute a stable platform for deploying production systems, and receive security updates and critical bugfixes for a substantial window of time.  16.04 will continue to be updated until April of 2021.</p>

<p>You can read a <a href="https://wiki.ubuntu.com/LTS">detailed breakdown of the Ubuntu LTS release cycle</a> on the Ubuntu Wiki.</p>

<h2 id="the-systemd-init-system">The systemd Init System</h2>

<p>Users of Ubuntu 15.10 or Debian Jessie may already be familiar with systemd, which is now the default init system for the majority of mainstream GNU/Linux distributions.  On Ubuntu, systemd supplants Canonical's Upstart.</p>

<p>If you make use of custom init scripts, or routinely configure long-running services, you will need to know the basics of systemd.  For an overview, read <a href="https://indiareads/community/tutorials/systemd-essentials-working-with-services-units-and-the-journal">Systemd Essentials: Working with Services, Units, and the Journal</a>.</p>

<h2 id="the-kernel">The Kernel</h2>

<p>Ubuntu 16.04 is built on <a href="http://kernelnewbies.org/Linux_4.4">the 4.4 series of Linux Kernels</a>, released in January of 2016.</p>

<p>On IndiaReads, new 16.04 Droplets and Droplets upgraded from 15.10 will be able to manage and upgrade their own kernels.  This is not the case for Droplets upgraded from Ubuntu 14.04 LTS.</p>

<h2 id="ssh">SSH</h2>

<p>Ubuntu 16.04 defaults to OpenSSH 7.2p2, which disables the SSH version 1 protocol, and disallows the use of DSA (ssh-dss) keys.  If you are using an older key or are required to communicate with a legacy SSH server from your system, you should read the <a href="https://wiki.ubuntu.com/XenialXerus/ReleaseNotes#OpenSSH_7.2p2">release notes on SSH</a>.  Although relatively few DSA keys are still in use, there is some possibility that you may need to generate new keys before performing an upgrade or disabling password-based SSH authentication on a new Ubuntu 16.04 server.</p>

<p>For an overview of generating and using new SSH keys, see <a href="https://indiareads/community/tutorials/how-to-configure-ssh-key-based-authentication-on-a-linux-server">How To Configure SSH Key-Based Authentication on a Linux Server</a>.</p>

<h2 id="packaging-software-distribution-and-containers">Packaging, Software Distribution, and Containers</h2>

<h3 id="apt">Apt</h3>

<p>At its core, Ubuntu is still built on the Debian project, and by extension on <code>.deb</code> package files managed by Apt, the Advanced Package Tool.</p>

<p>The Apt tools have not changed a great deal, although Ubuntu 16.04 upgrades to Apt 1.2, which includes some security improvements.  Users migrating from older releases may also wish to consider use of the <code>apt</code> command in place of the traditional <code>apt-get</code> and <code>apt-cache</code> for many package management operations.  More detail on the <code>apt</code> command can be found in <a href="https://indiareads/community/tutorials/package-management-basics-apt-yum-dnf-pkg">Package Management Basics: apt, yum, dnf, pkg</a>.</p>

<h3 id="snap-packages">Snap Packages</h3>

<p>Although most users of Ubuntu in server environments will continue to rely on Apt for package management, 16.04 <a href="https://insights.ubuntu.com/2016/04/13/snaps-for-classic-ubuntu/">includes access</a> to a new kind of package called a <strong>snap</strong>, emerging from Ubuntu's mobile and Internet of Things development efforts.  While snaps are unlikely to be a major factor for server deployments early in 16.04's lifecycle, Canonical have repeatedly indicated that snaps represent the future of packaging for Ubuntu, so they're likely to be a development worth following.</p>

<h3 id="lxd">LXD</h3>

<p>LXD is a "container hypervisor", built around LXC, which in turn is an interface to Linux kernel containment features.  You can read <a href="https://linuxcontainers.org/lxc/introduction/">an introduction to LXC</a> and a <a href="https://linuxcontainers.org/lxd/getting-started-cli/">getting-started guide to LXD</a> on linuxcontainers.org.</p>

<h2 id="zfs">ZFS</h2>

<p>Ubuntu 16.04 includes a native kernel module for ZFS, an advanced filesystem originating in the 2000s at Sun Microsystems and currently developed for Open Source systems under the umbrella of the <a href="http://open-zfs.org/wiki/Main_Page">OpenZFS project</a>.  ZFS combines the traditional roles of a filesystem and volume manager, and offers many compelling features.</p>

<p>The decision to distribute ZFS has not been without controversy, drawing <a href="https://sfconservancy.org/blog/2016/feb/25/zfs-and-linux/">criticism over licensing issues</a> from the Software Conservancy and the Free Software Foundation.  Nevertheless, ZFS is a promising technology with a long development history—an especially significant consideration for filesystems, which usually require years of work before they are considered mature enough for widespread production use.  Systems administrators will likely want to track its adoption in the Linux ecosystem, both from a technical and a legal perspective.</p>

<p>You can read <a href="https://wiki.ubuntu.com/Kernel/Reference/ZFS">more about ZFS on Ubuntu</a> on the Ubuntu Wiki.</p>

<h2 id="language-runtimes-and-development-tools">Language Runtimes and Development Tools</h2>

<h3 id="go-1-6">Go 1.6</h3>

<p>Go 1.6 was <a href="https://blog.golang.org/go1.6">released</a> earlier this year, and is packaged for Ubuntu 16.04.</p>

<h3 id="php-7">PHP 7</h3>

<p>Ubuntu 16.04's PHP packages now default to v7.0.  PHP 7 offers major performance improvements over its predecessors, along with new features such as scalar type declarations for function parameters and return values.  It also deprecates some legacy features and removes a number of extensions.  If you are developing or deploying PHP 5 software, code changes or upgrades to newer releases may be necessary before you migrate your application.</p>

<p>See <a href="https://indiareads/company/blog/getting-ready-for-php-7/">Getting Ready for PHP 7</a> and the <a href="http://php.net/manual/en/migration70.php">official PHP migration guide</a> for a detailed list of changes.</p>

<h3 id="python-3-5">Python 3.5</h3>

<p>Ubuntu 16.04 comes by default with Python 3.5.1 installed as the <code>python3</code> binary.  Python 2 is still installable using the <code>python</code> package:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo apt-get install python
</li></ul></code></pre>
<p>This may be necessary to support existing code which hasn't yet been ported.</p>

<p>Users of the Vim editor should note that the default builds of Vim now use Python 3, which may break plugins that rely on Python 2.</p>

<h2 id="conclusion">Conclusion</h2>

<p>While this guide is not exhaustive, you should now have a general idea of the major changes and new features in Ubuntu 16.04.</p>

<p>The safest course of action in migrating to a major new release is usually to install the distribution from scratch, configure services with careful testing along the way, and migrate application or user data as a separate step.  For some common configurations, you may want to read one or more of:</p>

<ul>
<li><a href="https://indiareads/community/tutorials/initial-server-setup-with-ubuntu-16-04">Initial Server Setup with Ubuntu 16.04</a></li>
<li><a href="https://indiareads/community/tutorials/how-to-add-and-delete-users-on-ubuntu-16-04">How to Add and Delete Users on Ubuntu 16.04</a></li>
<li><a href="https://indiareads/community/tutorials/how-to-install-linux-apache-mysql-php-lamp-stack-on-ubuntu-16-04">How To Install Linux, Apache, MySQL, PHP (LAMP) stack on Ubuntu 16.04</a></li>
<li><a href="https://indiareads/community/tutorials/how-to-install-nginx-on-ubuntu-16-04">How To Install Nginx on Ubuntu 16.04</a></li>
<li><a href="https://indiareads/community/tutorials/how-to-install-linux-nginx-mysql-php-lemp-stack-in-ubuntu-16-04">How To Install Linux, Nginx, MySQL, PHP (LEMP stack) in Ubuntu 16.04</a></li>
</ul>

<p>You can also read <a href="https://indiareads/community/tutorials/how-to-upgrade-to-ubuntu-16-04-lts">How To Upgrade to Ubuntu 16.04 LTS</a> for details on the process of upgrading an existing system in place.</p>

    