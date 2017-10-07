<link rel='stylesheet' href='digital_ocean.css'><br><img src=https://community-cdn-digitalocean-com.global.ssl.fastly.net/assets/tutorials/images/large/ubuntu16.04_Blog.png?1461274596/> <br> 
      <h3 id="introduction">Introduction</h3>

<p><span class="warning"><strong>Warning:</strong> An earlier version of this guide included mention of Ubuntu 14.04 systems.  While an upgrade from 14.04 <em>may</em> successfully complete, upgrades between LTS releases are not enabled by default until the first point release, and it is recommended to wait until the 16.04.1 point release to upgrade.  On IndiaReads systems, an upgraded Ubuntu 14.04 system will be left with an older kernel which may not be upgradeable for some time.<br /></span></p>

<p>The Ubuntu operating system's next Long Term Support release, version 16.04 (Xenial Xerus), is due to be released on April 21, 2016.</p>

<p>Although it hasn't yet been released at the time of this writing, it's already possible to upgrade a 15.10 system to the development version of 16.04.  This may be useful for testing both the upgrade process and the features of 16.04 itself in advance of the official release date.</p>

<p>This guide will explain the process for systems including (but not limited to) IndiaReads Droplets running Ubuntu 15.10.</p>

<p><span class="warning"><strong>Warning:</strong> As with almost any upgrade between major releases of an operating system, this process carries an inherent risk of failure, data loss, or broken software configuration.  Comprehensive backups and extensive testing are strongly advised.<br /></span></p>

<h2 id="prerequisites">Prerequisites</h2>

<p>This guide assumes that you have a system running Ubuntu 15.10, configured with a non-root user with <code>sudo</code> privileges for administrative tasks.</p>

<h2 id="potential-pitfalls">Potential Pitfalls</h2>

<p>Although many systems can be upgraded in place without incident, it is often safer and more predictable to migrate to a major new release by installing the distribution from scratch, configuring services with careful testing along the way, and migrating application or user data as a separate step.</p>

<p>You should never upgrade a production system without first testing all of your deployed software and services against the upgrade in a staging environment.  Keep in mind that libraries, languages, and system services may have changed substantially.  In Ubuntu 16.04, important changes since the preceding LTS release include a transition to the systemd init system in place of Upstart, an emphasis on Python 3 support, and PHP 7 in place of PHP 5.</p>

<p>Before upgrading, consider reading the <a href="https://wiki.ubuntu.com/XenialXerus/ReleaseNotes">Xenial Xerus Release Notes</a>.</p>

<h2 id="step-1-–-back-up-your-system">Step 1 – Back Up Your System</h2>

<p>Before attempting a major upgrade on any system, you should make sure you won't lose data if the upgrade goes awry.  The best way to accomplish this is to make a backup of your entire filesystem.  Failing that, ensure that you have copies of user home directories, any custom configuration files, and data stored by services such as relational databases.</p>

<p>On a IndiaReads Droplet, the easiest approach is to power down the system and take a snapshot.  See <a href="https://indiareads/community/tutorials/how-to-use-digitalocean-snapshots-to-automatically-backup-your-droplets">How To Use IndiaReads Snapshots to Automatically Backup your Droplets</a> for more details on the snapshot process.</p>

<p>For backup methods which will work on most Ubuntu systems, see <a href="https://indiareads/community/tutorials/how-to-choose-an-effective-backup-strategy-for-your-vps">How To Choose an Effective Backup Strategy for your VPS</a>.</p>

<h2 id="step-2-–-upgrade-currently-installed-packages">Step 2 – Upgrade Currently Installed Packages</h2>

<p>Before beginning the release upgrade, it's safest to install the latest versions of all packages <em>for the current release</em>.  Begin by updating the package list:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo apt-get update
</li></ul></code></pre>
<p>Next, upgrade installed packages to their latest available versions:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo apt-get upgrade
</li></ul></code></pre>
<p>You will be shown a list of upgrades, and prompted to continue.  Answer <strong>y</strong> for yes and press <strong>Enter</strong>.</p>

<p>This process may take some time.  Once it finishes, use the <code>dist-upgrade</code> command, which will perform upgrades involving changing dependencies, adding or removing new packages as necessary.  This will handle a set of upgrades which may have been held back by <code>apt-get upgrade</code>:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo apt-get dist-upgrade
</li></ul></code></pre>
<p>Again, answer <strong>y</strong> when prompted to continue, and wait for upgrades to finish.</p>

<p>Now that you have an up-to-date installation of Ubuntu 15.10, you can use <code>do-release-upgrade</code> to upgrade to the 16.04 release.</p>

<h2 id="step-3-–-use-ubuntu-39-s-do-release-upgrade-tool-to-perform-upgrade">Step 3 – Use Ubuntu's do-release-upgrade Tool to Perform Upgrade</h2>

<p>First, make sure you have the <code>update-manager-core</code> package installed:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo apt-get install update-manager-core
</li></ul></code></pre>
<p>Traditionally, Debian releases have been upgradeable by changing Apt's <code>/etc/apt/sources.list</code>, which specifies package repositories, and using <code>apt-get dist-upgrade</code> to perform the upgrade itself.  Ubuntu is still a Debian-derived distribution, so this process would likely still work.  Instead, however, we'll use <code>do-release-upgrade</code>, a tool provided by the Ubuntu project, which handles checking for a new release, updating <code>sources.list</code>, and a range of other tasks.  This is the officially recommended upgrade path for server upgrades which must be performed over a remote connection.</p>

<p>Start by running <code>do-release-upgrade</code> with no options:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo do-release-upgrade
</li></ul></code></pre>
<p>If Ubuntu 16.04 has not been released yet, you should see the following:</p>
<div class="code-label " title="Sample Output">Sample Output</div><pre class="code-pre "><code langs="">Checking for a new Ubuntu release
No new release found
</code></pre>
<p>In order to upgrade to 16.04 before its official release, specify the <code>-d</code> option in order to use the <em>development</em> release:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo do-release-upgrade -d
</li></ul></code></pre>
<p>If you're connected to your system over SSH, as is likely with a IndiaReads Droplet, you'll be asked whether you wish to continue.</p>

<p>On a Droplet, it's safe to upgrade over SSH.  Although <code>do-upgrade-release</code> has not informed us of this, you can use the console available from the IndiaReads Control Panel to connect to your Droplet without running SSH.</p>

<p>For virtual machines or managed servers hosted by other providers, you should keep in mind that losing SSH connectivity is a risk, particularly if you don't have another means of remotely connecting to the system's console.  For other systems under your control, remember that it's safest to perform major operating system upgrades only when you have direct physical access to the machine.</p>

<p>At the prompt, type <strong>y</strong> and press <strong>Enter</strong> to continue:</p>
<pre class="code-pre "><code langs="">Reading cache

Checking package manager

Continue running under SSH?

This session appears to be running under ssh. It is not recommended
to perform a upgrade over ssh currently because in case of failure it
is harder to recover.

If you continue, an additional ssh daemon will be started at port
'1022'.
Do you want to continue?

Continue [yN] <span class="highlight">y</span>
</code></pre>
<p>Next, you'll be informed that <code>do-release-upgrade</code> is starting a new instance of <code>sshd</code> on port 1022:</p>
<pre class="code-pre "><code langs="">Starting additional sshd 

To make recovery in case of failure easier, an additional sshd will 
be started on port '1022'. If anything goes wrong with the running 
ssh you can still connect to the additional one. 
If you run a firewall, you may need to temporarily open this port. As 
this is potentially dangerous it's not done automatically. You can 
open the port with e.g.: 
'iptables -I INPUT -p tcp --dport 1022 -j ACCEPT' 

To continue please press [ENTER]
</code></pre>
<p>Press <strong>Enter</strong>.  Next, you may be warned that a mirror entry was not found.  On IndiaReads systems, it is safe to ignore this warning and proceed with the upgrade, since a local mirror for 16.04 is in fact available.  Enter <strong>y</strong>:</p>
<pre class="code-pre "><code langs="">Updating repository information

No valid mirror found 

While scanning your repository information no mirror entry for the 
upgrade was found. This can happen if you run an internal mirror or 
if the mirror information is out of date. 

Do you want to rewrite your 'sources.list' file anyway? If you choose 
'Yes' here it will update all 'trusty' to 'xenial' entries. 
If you select 'No' the upgrade will cancel. 

Continue [yN] <span class="highlight">y</span>
</code></pre>
<p>Once new package lists have been downloaded and changes calculated, you'll be asked if you want to start the upgrade.  Again, enter <strong>y</strong> to continue:</p>
<pre class="code-pre "><code langs="">Do you want to start the upgrade?


6 installed packages are no longer supported by Canonical. You can
still get support from the community.

9 packages are going to be removed. 104 new packages are going to be
installed. 399 packages are going to be upgraded.

You have to download a total of 232 M. This download will take about
46 seconds with your connection.

Installing the upgrade can take several hours. Once the download has
finished, the process cannot be canceled.

 Continue [yN]  Details [d]<span class="highlight">y</span>
</code></pre>
<p>New packages will now be retrieved, then unpacked and installed.  Even if your system is on a fast connection, this will take a while.</p>

<p>During the installation, you may be presented with interactive dialogs for various questions.  For example, you may be asked if you want to automatically restart services when required:</p>

<p><img src="http://assets.digitalocean.com/articles/how-to-upgrade-to-ubuntu-1604/0.png" alt="Service Restart Dialog" /></p>

<p>In this case, it is safe to answer "Yes".  In other cases, you may be asked if you wish to replace a configuration file that you have modified with the default version from the package that is being installed.  This is often a judgment call, and is likely to require knowledge about specific software that is outside the scope of this tutorial.</p>

<p>Once new packages have finished installing, you'll be asked whether you're ready to remove obsolete packages.  On a stock system with no custom configuration, it should be safe to enter <strong>y</strong> here.  On a system you have modified heavily, you may wish to enter <strong>d</strong> and inspect the list of packages to be removed, in case it includes anything you'll need to reinstall later.</p>
<pre class="code-pre "><code langs="">Remove obsolete packages? 


53 packages are going to be removed. 

 Continue [yN]  Details [d]<span class="highlight">y</span>
</code></pre>
<p>Finally, assuming all has gone well, you'll be informed that the upgrade is complete and a restart is required.  Enter <strong>y</strong> to continue:</p>
<pre class="code-pre "><code langs="">System upgrade is complete.

Restart required 

To finish the upgrade, a restart is required. 
If you select 'y' the system will be restarted. 

Continue [yN] <span class="highlight">y</span>
</code></pre>
<p>On an SSH session, you'll likely see something like the following:</p>
<pre class="code-pre "><code langs="">=== Command detached from window (Thu Apr  7 13:13:33 2016) ===
=== Command terminated normally (Thu Apr  7 13:13:43 2016) ===
</code></pre>
<p>You may need to press a key here to exit to your local prompt, since your SSH session will have terminated on the server end.  Wait a moment for your system to reboot, and reconnect.  On login, you should be greeted by a message confirming that you're now on Xenial Xerus:</p>
<pre class="code-pre "><code langs="">Welcome to Ubuntu Xenial Xerus (development branch) (GNU/Linux 4.4.0-17-generic x86_64)
</code></pre>
<h2 id="conclusion">Conclusion</h2>

<p>You should now have a working Ubuntu 16.04 installation.  From here, you likely need to investigate necessary configuration changes to services and deployed applications.  In the coming weeks, we'll begin posting IndiaReads guides specific to Ubuntu 16.04 on a wide range of topics.</p>

    