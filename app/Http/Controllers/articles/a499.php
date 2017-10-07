<link rel='stylesheet' href='digital_ocean.css'><br> <br> 
      <h3 id="introduction">Introduction</h3>

<p><em>scponly</em> is a secure alternative to anonymous FTP.  It gives the administrator the ability to setup a secure user account with restricted remote file access and without access to an interactive shell.</p>

<p>Why Use scponly Instead of Normal SSH? With scponly you are giving the user remote access to download and upload specific files.  They will not have an interactive shell, meaning they can't execute commands.  The user can only access the server via <code>scp</code>, <code>sftp</code>, or clients that support these protocols.  From a security perspective, this lowers your attack surface by limiting unneeded access to an interactive shell on a server.</p>

<h2 id="prerequisites">Prerequisites</h2>

<p>For this tutorial, you will need a fresh CentOS 6 or 7 Droplet. </p>

<p>All the commands in this tutorial should be run as a non-root user. If root access is required for the command, it will be preceded by <code>sudo</code>. If you don't already have that set up, follow this tutorial: <a href="https://indiareads/community/tutorials/initial-server-setup-with-centos-6">Initial Server Setup on CentOS 6</a> or <a href="https://indiareads/community/tutorials/initial-server-setup-with-centos-7">Initial Server Setup for CentOS 7</a>.</p>

<h2 id="step-1-—-install-packages">Step 1 —  Install Packages</h2>

<p>scponly is available in some third party repositories, but these builds of scponly are outdated and are missing some of the features we will be adding when we build scponly from source.</p>

<p>To build scponly from source you will need to install the following 5 packages:</p>

<ul>
<li>wget (To download files via the command line)</li>
<li>gcc (To compile scponly from source)</li>
<li>man (To read man pages)</li>
<li>rsync (To provide advanced file copying)</li>
<li>openssh-client-tools (To provide various ssh tools)</li>
</ul>

<p>We will use yum to install the prerequisite packages needed to build scponly.  During the yum install we will pass the required package names as well as <code>-y</code> which automatically answers yes to any prompts.</p>

<p>Install <code>wget</code>, <code>gcc</code>, <code>man</code>, <code>rsync</code>, and <code>openssh-clients</code> using the <code>yum install</code> command:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo yum install wget gcc man rsync openssh-clients -y
</li></ul></code></pre>
<h2 id="step-2-—-download-and-extract-scponly">Step 2 — Download and Extract scponly</h2>

<p>In this section we will be downloading the latest build of scponly from sourceforge using <code>wget</code> and extracting the files using <code>tar</code>.</p>

<p>Before downloading scponly, change to the <code>/opt</code> directory. This directory is usually designated for <em>optional</em> software.</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">cd /opt
</li></ul></code></pre>
<p>As of this article the latest snapshot of scponly is <strong>2011.05.26</strong>.  You can check the <a href="http://sourceforge.net/projects/scponly/files/scponly-snapshots/">Sourceforge page</a> for a later release and adjust the <code>wget</code> command accordingly.</p>

<p>Download the scponly source using <code>wget</code>:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo wget http://sourceforge.net/projects/scponly/files/scponly-snapshots/scponly-20110526.tgz
</li></ul></code></pre>
<p>Extract the scponly source code:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo tar -zxvf scponly-20110526.tgz
</li></ul></code></pre>
<h2 id="step-3-—-build-and-install-scponly">Step 3 — Build and Install scponly</h2>

<p>In this section we will use 3 main commands to build scponly: <code>configure</code>, <code>make</code>, and <code>make install</code>.  These are the 3 commands most often used when you are downloading and installing software from source code.</p>

<p>Change to the directory that contains the scponly source code you just uncompressed:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">cd /opt/scponly-20110526
</li></ul></code></pre>
<p>First, run the <code>configure</code> command to build a makefile with all the features you want enabled or disabled when building from source:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo ./configure --enable-chrooted-binary --enable-winscp-compat --enable-rsync-compat --enable-scp-compat --with-sftp-server=/usr/libexec/openssh/sftp-server 
</li></ul></code></pre>
<p>The following options were used:</p>

<ul>
<li><code>--enable-chrooted-binary:</code> Installs chrooted binary <code>scponlyc</code></li>
<li><code>--enable-winscp-compat:</code> Enables compatibility with WinSCP, a Windows scp/sftp client</li>
<li><code>--enable-rsync-compat:</code> Enable compatibility with rsync, a very versatile file copying utility</li>
<li><code>--enable-scp-compat:</code> Enables compatibility with the UNIX style scp commands</li>
</ul>

<p>Next we will build scponly with the <code>make</code> command.  The <code>make</code> command take all your options that you passed using the <code>configure</code> command and builds it into the binaries that will be installed and run on the OS.</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo make
</li></ul></code></pre>
<p>Next we will install the binaries with <code>make install</code>:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo make install
</li></ul></code></pre>
<p>Finally add the scponly shells to the <code>/etc/shells</code> file:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo /bin/su -c "echo "/usr/local/bin/scponly" >> /etc/shells"
</li></ul></code></pre>
<p>The <code>/etc/shells</code> file tells the operating system which shells are available to the users.  So we are telling the operating system that we added a new shell to the system called <code>scponly</code> and that the binary is located at <code>/usr/local/bin/scponly</code>.</p>

<h2 id="step-4-—-create-scponly-group">Step 4 — Create scponly Group</h2>

<p>Now we will create a group called scponly so we can easily manage all the users who will be accessing the server with scponly.  </p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo groupadd scponly
</li></ul></code></pre>
<h2 id="step-5-—-create-an-upload-directory-and-set-proper-permissions">Step 5 — Create an Upload Directory and Set Proper Permissions</h2>

<p>In this section we will create a centralized upload directory for the scponly group.  This allows you control over where and how much data can be uploaded to the server.  </p>

<p>Create a directory named <code>/pub/upload</code> this will be a directory dedicated to uploads:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo mkdir -p /pub/upload
</li></ul></code></pre>
<p>Change the group ownership of the <code>/pub/upload</code> directory to <code>scponly</code>:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo chown root:scponly /pub/upload
</li></ul></code></pre>
<p>The next step is setting up permissions on the <code>/pub/upload</code> directory.  By setting the permissions on this directory to 770 we are giving access to only the root users and members of the scponly group.</p>

<p>Change permissions on the <code>/pub/upload</code> directory to read, write, and execute for the owner and group and remove all permissions for others:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo chmod 770 /pub/upload
</li></ul></code></pre>
<h2 id="step-6-—-create-a-user-account-with-scponly-shell">Step 6 — Create a User Account with scponly Shell</h2>

<p>Now we are going to setup a test user account to verify our scponly configuration.</p>

<p>Create a user named <strong>testuser1</strong> and specify <strong>scponly</strong> as an alternative group and <code>/usr/local/bin/scponly</code> as the shell:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo useradd -m -d /home/testuser1 -s "/usr/local/bin/scponly" -c "testuser1" -G scponly testuser1
</li></ul></code></pre>
<p><span class="note"><strong>Note:</strong>  Next is a very important step.  The user's home directory should not be writable because they could modify certain SSH parameters  and possibly subvert the scponly shell.<br /></span></p>

<p>Change permissions on the <strong>testuser1</strong> home directory to read and execute only for the owner:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo chmod 500 /home/testuser1
</li></ul></code></pre>
<p>Finally, set a password for the <strong>testuser1</strong> user:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo passwd testuser1
</li></ul></code></pre>
<h2 id="step-7-—-verify-user-does-not-have-access-to-interactive-shell">Step 7 — Verify User Does Not Have Access to Interactive Shell</h2>

<p>Now we will test the scponly shell access and verify that it works as expected. </p>

<p>Let's verify that the <strong>testuser1</strong> account does not have access to a terminal.</p>

<p>Try to log into the server as testuser1: </p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">su - testuser1
</li></ul></code></pre>
<p>Your terminal will hang since you do not have access to an interactive shell.  Press <code>CTRL+C</code> to exit the scponly shell.</p>

<p>You can also test access from your local machine:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">ssh testuser1@<span class="highlight">your_server_ip</span>
</li></ul></code></pre>
<p>Again, your terminal will hang because testuser1 is not allowed shell access. Press <code>CTRL+C</code> to exit the scponly shell.</p>

<h2 id="step-8-—-test-users-ability-to-download-files">Step 8 — Test Users Ability to Download Files</h2>

<p>In this section we will be connecting via <code>sftp</code> from your local machine to your IndiaReads Droplet to verify that the <code>testuser1</code> account can download files. </p>

<p>First create a 100 Megabyte file using <code>fallocate</code>:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo fallocate -l 100m /home/testuser1/testfile.img
</li></ul></code></pre>
<p>Change ownership of the <code>testfile.img</code> file to testuser1: </p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo chown testuser1:testuser1 /home/testuser1/testfile.img
</li></ul></code></pre>
<p>On your local system change directory to <code>/tmp</code>:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">cd /tmp
</li></ul></code></pre>
<p>Next <code>sftp</code> to your IndiaReads server:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sftp testuser1@<span class="highlight">your_server_ip</span>
</li></ul></code></pre>
<p>You may be prompted to save the ssh key as you enter the password.</p>

<p>Once logged in issue <code>ls -l</code> at the <code>sftp></code> prompt:</p>
<pre class="code-pre custom_prefix"><code langs=""><ul class="prefixed"><li class="line" prefix="sftp>">ls -l
</li></ul></code></pre>
<p>Download the file using the <code>get</code> command:</p>
<pre class="code-pre custom_prefix"><code langs=""><ul class="prefixed"><li class="line" prefix="sftp>">get testfile.img
</li></ul></code></pre>
<p>Once the file is finished downloading type <code>quit</code> to exit:</p>
<pre class="code-pre custom_prefix"><code langs=""><ul class="prefixed"><li class="line" prefix="sftp>">quit
</li></ul></code></pre>
<p>Back on your local machine, verify that the file was downloaded successfully:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">ls -l testfile.img
</li></ul></code></pre>
<h2 id="step-9-—-test-users-ability-to-upload-files">Step 9 — Test Users Ability to Upload Files</h2>

<p>In this section we will be testing the ability of the <code>testuser1</code> account to upload files to the server using <code>sftp</code>.</p>

<p><span class="note"><strong>Note:</strong>  In this section we will be restricting access to the <code>/pub/upload</code> directory.  This is not required but is an added security benefit for multiple reasons such as managing quotas or disk usage and easily monitoring all uploads in a central location.<br /><br /></span></p>

<p>On your local system create an 100 megabyte file called <code>uploadfile.img</code> using <code>fallocate</code>:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">fallocate -l 100m /home/testuser1/uploadfile.img
</li></ul></code></pre>
<p>From your local system connect to your IndiaReads Droplet.</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sftp testuser1@<span class="highlight">your_server_ip</span>
</li></ul></code></pre>
<p>Next upload the <code>uploadfile.img</code> to <code>/pub/upload</code> from the <code>sftp</code> prompt:</p>
<pre class="code-pre custom_prefix"><code langs=""><ul class="prefixed"><li class="line" prefix="sftp>">put uploadfile.img /pub/upload/
</li></ul></code></pre>
<p>Verify the file was successfully uploaded by issuing the following command at the <code>sftp</code> prompt:</p>
<pre class="code-pre custom_prefix"><code langs=""><ul class="prefixed"><li class="line" prefix="sftp>">ls -ltr /pub/upload
</li></ul></code></pre>
<p>The results should similar to:</p>
<pre class="code-pre "><code langs="">-rw-r--r--    1 testuser1 testuser1 104857600 Jun  5 07:46 uploadfile.img
</code></pre>
<p>Finally type <code>quit</code> at the <code>sftp</code> prompt:</p>
<pre class="code-pre custom_prefix"><code langs=""><ul class="prefixed"><li class="line" prefix="sftp>">quit
</li></ul></code></pre>
<h2 id="conclusion">Conclusion</h2>

<p>scponly should be in every admin's toolbox.  It can be used as a secure alternative to anonymous FTP or as a way of giving authenticated users the ability to download and upload files without having an interactive shell. The logging of scponly occurs in the standard ssh log file <code>/var/log/secure</code>. As always read the man pages and keep your system updated.</p>

<p>For more information about scponly, go to the <a href="https://github.com/scponly/scponly/wiki">scponly GitHub page</a>.</p>

    