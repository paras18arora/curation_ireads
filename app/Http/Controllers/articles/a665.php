<link rel='stylesheet' href='digital_ocean.css'><br> <br> 
      <h3 id="introduction">Introduction</h3>

<p>Version control has become an indispensable tool in modern software development. Version control systems allow you to keep track of your software at the source level. You can track changes, revert to previous stages, and branch off from the base code to create alternative versions of files and directories.</p>

<p>One of the most popular version control systems is <code>git</code>. Many projects maintain their files in a Git repository, and sites like GitHub and Bitbucket have made sharing and contributing to code with Git easier than ever.</p>

<p>In this guide, we will demonstrate how to install Git on a CentOS 7 server. We will cover how to install the software in a couple of different ways, each with their own benefits, along with how to set up Git so that you can begin collaborating right away.</p>

<h2 id="prerequisites">Prerequisites</h2>

<p>Before you begin with this guide, there are a few steps that need to be completed first.</p>

<p>You will need a CentOS 7 server installed and configured with a non-root user that has <code>sudo</code> privileges. If you haven't done this yet, you can run through steps 1-4 in the <a href="https://indiareads/community/tutorials/initial-server-setup-with-centos-7">CentOS 7 initial server setup guide</a> to create this account.</p>

<p>Once you have your non-root user, you can use it to SSH into your CentOS server and continue with the installation of Git.</p>

<h2 id="install-git">Install Git</h2>

<p>The two most common ways to install Git will be described in this section. Each option has their own advantages and disadvantages, and the choice you make will depend on your own needs. For example, users who want to maintain updates to the Git software will likely want to use <code>yum</code> to install Git, while users who need features presented by a specific version of Git will want to build that version from source.</p>

<h3 id="option-one-—-install-git-with-yum">Option One — Install Git with Yum</h3>

<p>The easiest way to install Git and have it ready to use is to use CentOS's default repositories. This is the fastest method, but the Git version that is installed this way may be older than the newest version available. If you need the latest release, consider compiling <code>git</code> from source (the steps for this method can be found further down this tutorial).</p>

<p>Use <code>yum</code>, CentOS's native package manager, to search for and install the latest <code>git</code> package available in CentOS's repositories:</p>
<pre class="code-pre "><code langs="">sudo yum install git
</code></pre>
<p>If the command completes without error, you will have <code>git</code> downloaded and installed. To double-check that it is working correctly, try running Git's built-in version check:</p>
<pre class="code-pre "><code langs="">git --version
</code></pre>
<p>If that check produced a Git version number, then you can now move on to <strong>Setting up Git</strong>, found further down this article.</p>

<h3 id="option-two-—-install-git-from-source">Option Two — Install Git from Source</h3>

<p>If you want to download the latest release of Git available, or simply want more flexibility in the installation process, the best method for you is to compile the software from source. This takes longer, and will not be updated and maintained through the <code>yum</code> package manager, but it will allow you to download a newer version than what is available through the CentOS repositories, and will give you some control over the options that you can include.</p>

<p>Before you begin, you'll need to install the software that <code>git</code> depends on. These dependencies are all available in the default CentOS repositories, along with the tools that we need to build a binary from source:</p>
<pre class="code-pre "><code langs="">sudo yum groupinstall "Development Tools"
sudo yum install gettext-devel openssl-devel perl-CPAN perl-devel zlib-devel
</code></pre>
<p>After you have installed the necessary dependencies, you can go ahead and look up the version of Git that you want by visiting the project's <a href="https://github.com/git/git/releases">releases page</a> on GitHub.</p>

<p><img src="https://assets.digitalocean.com/articles/git_centos7/git_releases.png" alt="Git Releases on GitHub" /></p>

<p>The version at the top of the list is the most recent release. If it does not have <code>-rc</code> (short for "Release Candidate") in the name, that means that it is a stable release and is safe for use. Click on the version you want to download to be taken to that version's release page. Then right-click on the <strong>Source code (tar.gz)</strong> button and copy the link to your clipboard.</p>

<p><img src="https://assets.digitalocean.com/articles/git_centos7/git_download.png" alt="Copy Source Code Link" /></p>

<p>Now we are going to use the <code>wget</code> command in our CentOS server to download the source archive from the link that we copied, renaming it to <code>git.tar.gz</code> in the process so that it is easier to work with.</p>

<p><strong>Note:</strong> the URL that you copied may be different from mine, since the release that you download may be different.</p>
<pre class="code-pre "><code langs="">wget https://github.com/git/git/archive/v2.1.2.tar.gz -O git.tar.gz
</code></pre>
<p>Once the download is complete, we can unpack the source archive using <code>tar</code>. We'll need a few extra flags to make sure that the unpacking is done correctly: <code>z</code> decompresses the archive (since all .gz files are compressed), <code>x</code> extracts the individual files and folders from the archive, and <code>f</code> tells <code>tar</code> that we are declaring a filename to work with.</p>
<pre class="code-pre "><code langs="">tar -zxf git.tar.gz
</code></pre>
<p>This will unpack the compressed source to a folder named after the version of Git that we downloaded (in this example, the version is 2.1.2, so the folder is named <code>git-2.1.2</code>). We'll need to move to that folder to begin configuring our build. Instead of bothering with the full version name in the folder, we can use a wildcard (<code>*</code>) to save us some trouble in moving to that folder.</p>
<pre class="code-pre "><code langs="">cd git-*
</code></pre>
<p>Once we are in the source folder, we can begin the source build process. This starts with some pre-build checks for things like software dependencies and hardware configurations. We can check for everything that we need with the <code>configure</code> script that is generated by <code>make configure</code>. This script will also use a <code>--prefix</code> to declare <code>/usr/local</code> (the default program folder for Linux platforms) as the appropriate destination for the new binary, and will create a <code>Makefile</code> to be used in the following step.</p>
<pre class="code-pre "><code langs="">make configure
./configure --prefix=/usr/local
</code></pre>
<p>Makefiles are scriptable configuration files that are processed by the <code>make</code> utility. Our Makefile will tell <code>make</code> how to compile a program and link it to our CentOS installation so that we can execute the program properly. With a Makefile in place, we can now execute <code>make install</code> (with <code>sudo</code> privileges) to compile the source code into a working program and install it to our server:</p>
<pre class="code-pre "><code langs="">sudo make install
</code></pre>
<p>Git should now be built and installed on your CentOS 7 server. To double-check that it is working correctly, try running Git's built-in version check:</p>
<pre class="code-pre "><code langs="">git --version
</code></pre>
<p>If that check produced a Git version number, then you can now move on to <strong>Setting up Git</strong> below.</p>

<h2 id="set-up-git">Set Up Git</h2>

<p>Now that you have <code>git</code> installed, you will need to submit some information about yourself so that commit messages will be generated with the correct information attached. To do this, use the <code>git config</code> command to provide the name and email address that you would like to have embedded into your commits:</p>
<pre class="code-pre "><code langs="">git config --global user.name "<span class="highlight">Your Name</span>"
git config --global user.email "<span class="highlight">you@example.com</span>"
</code></pre>
<p>To confirm that these configurations were added successfully, we can see all of the configuration items that have been set by typing:</p>
<pre class="code-pre "><code langs="">git config --list
</code></pre><pre class="code-pre "><code langs="">user.name=<span class="highlight">Your Name</span>
user.email=<span class="highlight">you@example.com</span>
</code></pre>
<p>This configuration will save you the trouble of seeing an error message and having to revise commits after you submit them.</p>

<h2 id="conclusion">Conclusion</h2>

<p>You should now have <code>git</code> installed and ready to use on your system. To learn more about how to use Git, check out these more in-depth articles:</p>

<ul>
<li><a href="https://indiareads/community/articles/how-to-use-git-effectively">How To Use Git Effectively</a></li>
<li><a href="https://indiareads/community/articles/how-to-use-git-branches">How To Use Git Branches</a></li>
</ul>

    