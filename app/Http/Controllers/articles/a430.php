<link rel='stylesheet' href='digital_ocean.css'><br> <br> 
      <h3 id="introduction">Introduction</h3>

<p>Ubuntu has two types of releases, standard and Long Term Support (or "LTS"). Standard updates are released every six months and receive security updates from Ubuntu for at least nine months, while LTS updates are released every two years and are supported for at least five years.</p>

<p>If you are currently using Ubuntu 12.04, you will have security updates until at least October 2017. If you want to extend that support time, and get access to new features and updates, you can upgrade your server to the newest LTS release. In this guide, we will go over how to safely upgrade an Ubuntu 12.04 server to 14.04, taking care to preserve our existing configurations.</p>

<h2 id="step-one-—-backing-up-existing-data">Step One — Backing Up Existing Data</h2>

<p>Since you are likely using your 12.04 server to handle sensitive tasks or data, it's very important that you back up the current state of your server configuration and files. While the process that we will be going over is the recommended way to perform a release upgrade, there is no way to guarantee that a release upgrade won't cause issues with software or configurations. Backing up your data beforehand will make it much easier to recover in case of a problem resulting from the upgrade.</p>

<p>In this step, we will cover multiple backup methods. If you are using IndiaReads, it's advised that you make a snapshot through the control panel in addition to syncing the files to your local computer. This way, you have the ability to either restore individual files or restore the entire snapshot, depending on the nature of the scenario that necessitates a restore.</p>

<h3 id="sync-files-to-local-computer">Sync Files to Local Computer</h3>

<p>There are several effective ways to back up files on an Ubuntu server. In this example, we are going to use <code>rsync</code> to copy our server's files to a backup folder on our local computer. We won't be able to back up every folder in the file system, since some of them are temporary storage for running processes. Fortunately, we can exclude these folders from our backup.</p>

<p>You can use the following command to copy the server's file system, taking care to change the server credentials marked in <span class="highlight">red</span>.  If you use SSH key-based authentication on your server, your root user won't have a password set by default, so you'll need to point <code>rsync</code> to your private key file. This file is usually found at <code>/home/<span class="highlight">username</span>/.ssh/id_rsa</code>. Since we are downloading server files locally, the command must be run from our local computer, not on the server that we are backing up.</p>
<pre class="code-pre "><code langs="">sudo rsync -aAXv --exclude={"/dev/*","/proc/*","/sys/*","/tmp/*","/run/*","/mnt/*","/media/*","/lost+found"} -e 'ssh -i <span class="highlight">/path/to/private_key</span>' root@<span class="highlight">SERVER_IP_ADDRESS</span>:/* <span class="highlight">~/backup/</span>
</code></pre>
<p>The <code>aAX</code> flags tell <code>rsync</code> to preserve important file attributes like permissions, ownerships, and modification times. If you are using Docker or another virtualization tool, you should add the <code>S</code> flag so that <code>rsync</code> properly handles sparse files, like virtual storage.</p>

<p><strong>Note:</strong> <code>rsync</code> is only available for Unix-based operating systems like Linux and OS X. If your local computer is running Windows, you can copy your server's files using an SFTP client like Filezilla: <a href="https://indiareads/community/tutorials/how-to-use-filezilla-to-transfer-and-manage-files-securely-on-your-vps">How To Use Filezilla to Transfer and Manage Files Securely on your VPS</a></p>

<p>If you need to restore parts of your server files later on, you can use <code>rsync</code> again with the source and destination parameters reversed, like so: <code>sudo rsync -aAXv -e 'ssh -i <span class="highlight">/path/to/private_key</span>' <span class="highlight">~/backup/</span> root@<span class="highlight">SERVER_IP_ADDRESS</span>:/*</code></p>

<h3 id="make-digitalocean-droplet-snapshot">Make IndiaReads Droplet Snapshot</h3>

<p>If you are using IndiaReads, you can create a snapshot of the Droplet that you can easily restore if anything goes wrong. If this option is available to you, it's a good idea to use both backup methods for the sake of redundancy, rather than choosing one method over the other.</p>

<p>First, power off your server so that its current state is preserved for the snapshot:</p>
<pre class="code-pre "><code langs="">sudo poweroff
</code></pre>
<p>Next, log in to your IndiaReads control panel and select the Droplet that you're going to be upgrading. Go to the <strong>Snapshots</strong> panel, fill in a name for your new snapshot, and select <strong>Take Snapshot</strong>.</p>

<p><img src="https://assets.digitalocean.com/articles/upgrade_1204_to_1404/snapshot_take_snapshot.png" alt="Take Snapshot" /></p>

<p>Once the snapshot process is finished, your server will automatically be rebooted, so you can reconnect to it via SSH to continue with the upgrade.</p>

<p>If you need to restore your server to this snapshot later on, you can rebuild from that image in the <strong>Destroy</strong> section of your Droplet's control panel.</p>

<h2 id="step-two-—-preparing-for-the-upgrade">Step Two — Preparing for the Upgrade</h2>

<p>Before we begin with the release upgrade, we need to make sure that the software already installed is up-to-date. Updating our installed software now makes the release upgrade less of a leap forward for many packages, which will reduce the likelihood of errors.</p>

<p>We'll use <code>apt</code> to update our local package index, then upgrade the software that is currently installed:</p>
<pre class="code-pre "><code langs="">sudo apt-get update
sudo apt-get upgrade
</code></pre>
<p>We're going to use <code>update-manager-core</code> to manage the release upgrade. This package is installed by default in most Ubuntu 12.04 installations, but we can verify whether or not it is installed on the server by using <code>apt-cache</code>:</p>
<pre class="code-pre "><code langs="">apt-cache policy update-manager-core
</code></pre>
<p>If the command does not return an installed version number (i.e. if it shows <code>Installed: (none)</code>), then use <code>apt</code> to download the update manager from Ubuntu's software repositories:</p>
<pre class="code-pre "><code langs="">sudo apt-get install update-manager-core
</code></pre>
<p>Once we have confirmed that the update manager is installed, we are ready to begin the upgrade process.</p>

<h2 id="step-three-—-upgrading-to-14-04">Step Three — Upgrading to 14.04</h2>

<p>We can now run the <code>do-release-upgrade</code> command with root privileges. This is an automated script that will pull the newest release software from Ubuntu's repositories and apply the various upgrades to your server. This upgrade can take several minutes and will be prompting you from time to time, so keep an eye on your SSH session while the upgrade is in progress.</p>
<pre class="code-pre "><code langs="">sudo do-release-upgrade
</code></pre>
<p>While the update manager will handle most of the details involved in moving to the next LTS release, we will still need to make a few decisions as prompts come up. Most of these prompts will ask about overwriting existing configuration files. The default action is to keep the configuration that already exists, and that's generally the best option to ensure stability. However, be sure to read each prompt carefully before choosing an option, and don't be afraid to look up the package in question to be sure that you make the appropriate choice.</p>

<p>Near the end of the upgrade process, you will be prompted to reboot your server. Confirm with "y" to initiate the reboot.</p>

<p>Your SSH session will be disconnected, so you'll need to reconnect to confirm that the upgrade went through as expected. Once you've reconnected, use <code>lsb_release</code> to verify your new Ubuntu version number:</p>
<pre class="code-pre "><code langs="">lsb_release -a
</code></pre>
<p>You should see an output similar to the following:</p>
<pre class="code-pre "><code langs="">No LSB modules are available.
Distributor ID: Ubuntu
Description:    Ubuntu 14.04.1 LTS
Release:    14.04
Codename:   trusty
</code></pre>
<p>If the <code>Release:</code> shows 14.04, then you have successfully upgraded your Ubuntu LTS server!</p>

<h2 id="step-four-—-upgrading-the-kernel">Step Four — Upgrading the Kernel</h2>

<p>Even though you've downloaded a new kernel to go with your updated release, the kernel might not be activated for use by the software used to host your server. If you are using IndiaReads, the hosting software (called KVM) maintains the kernel outside of the server image, and will need to be updated separately.</p>

<p>You can see which kernel version your server is currently using with <code>uname</code>:</p>
<pre class="code-pre "><code langs="">uname -ri
</code></pre>
<p>Your output will look something like this:</p>
<pre class="code-pre "><code langs="">3.2.0-24-virtual i686
</code></pre>
<p>If the kernel version is lower than 3.13, that means that your server is not yet using Ubuntu 14.04's kernel. While it's unlikely that an older kernel will present issues with software, you might see improved performance or helpful new features with a newer kernel.</p>

<p>During the upgrade process, your server downloaded a new kernel to be loaded for use on Ubuntu 14.04. You can see which kernel version was downloaded by checking the contents of the <code>/lib/modules</code> directory:</p>
<pre class="code-pre "><code langs="">ls /lib/modules
</code></pre>
<p>You will see a list that looks something like the following:</p>
<pre class="code-pre "><code langs="">3.13.0-39-generic  3.2.0-24-virtual
</code></pre>
<p>In order to use a newly installed kernel, you must update the kernel selection in your droplet's control panel, then power off and boot your droplet.</p>

<p>First, log in to your IndiaReads control panel and select the server that you're going to be upgrading. In the <strong>Settings</strong> panel, select the <strong>Kernel</strong> tab.</p>

<p>Here you will see a drop-down list of available kernels. Select the kernel that matches the distribution, release, and version number of the one that you downloaded (<code>3.13.0-39-generic</code> in the above example):</p>

<p><img src="https://assets.digitalocean.com/articles/upgrade_1204_to_1404/change_kernel.png" alt="Change Kernel" /></p>

<p>Once the kernel is selected, click <strong>Change</strong> to load that kernel onto your server. To begin using the new kernel, you'll need to power down the server. While you can do this in the control panel, that is similar to unplugging the power from a computer, so it's recommended that you power down through the terminal instead:</p>
<pre class="code-pre "><code langs="">sudo poweroff
</code></pre>
<p>Once the server is completely shut down, you can boot it back up in the control panel. In the <strong>Power</strong> panel, select <strong>Boot</strong>.</p>

<p><img src="https://assets.digitalocean.com/articles/upgrade_1204_to_1404/change_kernel_boot.png" alt="Power Cycle" /></p>

<p>Now you can reconnect to your server via SSH and use <code>uname</code> to confirm that the new kernel is in use:</p>
<pre class="code-pre "><code langs="">uname -ri
</code></pre>
<p>You should see an output similar to the following:</p>
<pre class="code-pre "><code langs="">3.13.0-39-generic i686
</code></pre>
<p>If the kernel version matches the kernel that you loaded on the control panel, then you have successfully updated your kernel.</p>

<h2 id="conclusion">Conclusion</h2>

<p>You have now upgraded your Ubuntu server to the latest LTS release, giving you access to the latest software updates as well as security updates until at least 2019. If you run into compatibility issues with a program after the upgrade, check that program's documentation to see if there were any significant changes that require changes to its configuration.</p>

    