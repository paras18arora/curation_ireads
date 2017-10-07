<link rel='stylesheet' href='digital_ocean.css'><br> <br> 
      <h3 id="introduction">Introduction</h3>

<p>An indispensable tool in modern software development is some kind of version control system.  Version control systems allow you to keep track of your software at the source level.  You can track changes, revert to previous stages, and branch to create alternate versions of files and directories.</p>

<p>One of the most popular version control systems is <code>git</code>, a distributed version control system.  Many projects maintain their files in a git repository, and sites like GitHub and Bitbucket have made sharing and contributing to code simple and valuable.</p>

<p>In this guide, we will demonstrate how to install git on an Ubuntu 14.04 VPS instance.  We will cover how to install the software in two different ways, each of which have benefits.</p>

<p>This tutorial assumes you are signed in as a <a href="https://indiareads/community/articles/initial-server-setup-with-ubuntu-14-04">non-root user</a> which you can learn how to create here.</p>

<h2 id="how-to-install-git-with-apt">How To Install Git with Apt</h2>

<p>By far the easiest way of getting <code>git</code> installed and ready to use is by using Ubuntu's default repositories.  This is the fastest method, but the version may be older than the newest version.  If you need the latest release, consider following the steps to compile <code>git</code> from source.</p>

<p>You can use the <code>apt</code> package management tools to update your local package index.  Afterwards, you can download and install the program:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo apt-get update
</li><li class="line" prefix="$">sudo apt-get install git
</li></ul></code></pre>
<p>This will download and install <code>git</code> to your system.  You will still have to complete the configuration steps that we cover in the "setup" section, so feel free to skip to <a href="https://indiareads/community/tutorials/how-to-install-git-on-ubuntu-14-04#how-to-set-up-git">that section</a> now.</p>

<h2 id="how-to-install-git-from-source">How To Install Git from Source</h2>

<p>A more flexible method of installing <code>git</code> is to compile the software from source.  This takes longer and will not be maintained through your package manager, but it will allow you to download the latest release and will give you some control over the options you include if you wish to customize.</p>

<p>Before you begin, you need to install the software that <code>git</code> depends on.  This is all available in the default repositories, so we can update our local package index and then install the packages:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo apt-get update
</li><li class="line" prefix="$">sudo apt-get install build-essential libssl-dev libcurl4-gnutls-dev libexpat1-dev gettext unzip
</li></ul></code></pre>
<p>After you have installed the necessary dependencies, you can go ahead and get the version of git you want by visiting the <a href="https://github.com/git/git">git project's page on GitHub</a>.</p>

<p>The version you see when you arrive at the project's page is the branch that is actively being committed to.  If you want the latest stable release, you should go change the branch to the latest non-"rc" tag using this button along the left side of the project header:</p>

<p><img src="https://assets.digitalocean.com/articles/git_install_1404/change_branch.png" alt="git change branch" /></p>

<p>Next, on the right side of the page, right-click the "Download ZIP" button and select the option similar to "Copy Link Address":</p>

<p><img src="https://assets.digitalocean.com/articles/git_install_1404/download_zip.png" alt="git download zip" /></p>

<p>Back on your Ubuntu 14.04 server, you can type <code>wget</code> and follow it by pasting the address you copied.  The URL that you copied may be different from mine:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">wget <span class="highlight">https://github.com/git/git/archive/v1.9.2.zip</span> -O git.zip
</li></ul></code></pre>
<p>Unzip the file that you downloaded and move into the resulting directory by typing:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">unzip git.zip
</li><li class="line" prefix="$">cd git-*
</li></ul></code></pre>
<p>Now, you can make the package and install it by typing these two commands:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">make prefix=/usr/local all
</li><li class="line" prefix="$">sudo make prefix=/usr/local install
</li></ul></code></pre>
<p>Now that you have <code>git</code> installed, if you want to upgrade to a later version, you can simply clone the repository and then build and install:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">git clone <span class="highlight">https://github.com/git/git.git</span>
</li></ul></code></pre>
<p>To find the URL to use for the clone operation, navigate to the branch or tag that you want on the <a href="https://github.com/git/git">project's GitHub page</a> and then copy the clone URL on the right side:</p>

<p><img src="https://assets.digitalocean.com/articles/git_install_1404/clone_url.png" alt="git clone URL" /></p>

<p>This will create a new directory within your current directory where you can rebuild the package and reinstall the newer version, just like you did above.  This will overwrite your older version with the new version:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">make prefix=/usr/local all
</li><li class="line" prefix="$">sudo make prefix=/usr/local install
</li></ul></code></pre>
<h2 id="how-to-set-up-git">How To Set Up Git</h2>

<p>Now that you have <code>git</code> installed, you need to do a few things so that the commit messages that will be generated for you will contain your correct information.</p>

<p>The easiest way of doing this is through the <code>git config</code> command.  Specifically, we need to provide our name and email address because <code>git</code> embeds this information into each commit we do.  We can go ahead and add this information by typing:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">git config --global user.name "<span class="highlight">Your Name</span>"
</li><li class="line" prefix="$">git config --global user.email "<span class="highlight">youremail@domain.com</span>"
</li></ul></code></pre>
<p>We can see all of the configuration items that have been set by typing:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">git config --list
</li></ul></code></pre><div class="code-label " title="git configuration">git configuration</div><pre class="code-pre "><code langs="">user.name=<span class="highlight">Your Name</span>
user.email=<span class="highlight">youremail@domain.com</span>
</code></pre>
<p>As you can see, this has a slightly different format.  The information is stored in the configuration file, which you can optionally edit by hand with your text editor like this:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">nano ~/.gitconfig
</li></ul></code></pre><div class="code-label " title="~/.gitconfig contents">~/.gitconfig contents</div><pre class="code-pre "><code langs="">[user]
    name = <span class="highlight">Your Name</span>
    email = <span class="highlight">youremail@domain.com</span>
</code></pre>
<p>There are many other options that you can set, but these are the two essential ones needed.  If you skip this step, you'll likely see warnings when you commit to <code>git</code> that are similar to this:</p>
<div class="code-label " title="Output when git username and email not set">Output when git username and email not set</div><pre class="code-pre "><code langs="">[master 0d9d21d] initial project version
 Committer: root 
Your name and email address were configured automatically based
on your username and hostname. Please check that they are accurate.
You can suppress this message by setting them explicitly:

    git config --global user.name "Your Name"
    git config --global user.email you@example.com

After doing this, you may fix the identity used for this commit with:

    git commit --amend --reset-author
</code></pre>
<p>This makes more work for you because you will then have to revise the commits you have done with the corrected information.</p>

<h2 id="conclusion">Conclusion</h2>

<p>You should now have <code>git</code> installed and ready to use on your system.  To learn more about how to use Git, check out these articles:</p>

<ul>
<li><a href="https://indiareads/community/articles/how-to-use-git-effectively">How To Use Git Effectively</a></li>
<li><a href="https://indiareads/community/articles/how-to-use-git-branches">How To Use Git Branches</a></li>
</ul>

<div class="author">By Justin Ellingwood</div>

    