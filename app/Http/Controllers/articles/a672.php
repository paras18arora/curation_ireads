<link rel='stylesheet' href='digital_ocean.css'><br> <br> 
      <h3 id="introduction">Introduction</h3>

<hr />

<p>As you work with Linux machines, you will likely begin to customize your environment quite heavily.  Some commonly used applications like text editors are so configurable, that unmodified versions can seem like entirely different programs.</p>

<p>It can take quite a bit of work to tweak your configurations, and often you will make minor edits as your usage changes over time.  This can be a lot to handle on just a single computer, but when  you want to synchronize your settings across multiple machines, things can quickly get complicated and unwieldy.</p>

<p>In this article, we will discuss one approach to dealing with complex configuration files that you wish to share across machines.  We will use git version control to manage our important files.  We can then upload these to a remote repository and then pull those files down to other computers.</p>

<p>We will be demonstrating these ideas on an Ubuntu 12.04 machine, but due to the nature of git and the configuration files we will be using, any reasonably up-to-date Linux distribution should work in a similar way.</p>

<h2 id="the-basic-idea">The Basic Idea</h2>

<hr />

<p>Before we jump into the actual steps that will implement this idea, let's talk about what we're actually going to set up.</p>

<p>We will assume that you have one computer with some heavy configuration already in progress.  This is the system that we will use to build our git repository.  We will add the appropriate files to the repo, and then push it to our remote git repository.</p>

<p>The remote git repository will be a place where we can store our configuration data.  It should be accessible to all of the other machines that we might want to place our configuration files on.  Some people feel comfortable using a public space like GitHub to host their files, but this comes with the risk of accidentally pushing sensitive data to a world-readable location.</p>

<p>In our guide, we will instead use GitLab, a self-hosting git repository solution that you can install and use on your own machines.  IndiaReads provides a one-click GitLab installation image for you to create a preconfigured GitLab VPS.  We will also cover how to install GitLab by yourself if you wish to go that route.</p>

<p>After our configuration files are in our remote git repository, we can then pull the files onto new systems to implement our settings.</p>

<h2 id="what-type-of-files-should-we-commit">What Type of Files Should We Commit?</h2>

<hr />

<p>While there are many different levels of customization that you do with new systems, some types of files and customizations are more appropriate for this type of a solution than others.</p>

<p>Files that have values that are heavily system-dependant probably should be handled either manually or using some other method, like a configuration management system like Chef, Puppet, or Ansible.</p>

<p>If the customizations you are doing are less user preferences and closer to system operating details, then using git probably won't help you much.  You'll have to change most of the values to match the client system anyways.</p>

<p>Configuration for tools and user environment files work the best for this type of a solution.  Things like customizations for vim, emacs, screen, tmux, bash, etc. are great candidates for this type of configuration.</p>

<p>Generally, a good rule of thumb is that any "dotfile" (hidden configuration file located within your home directory) that does not contain sensitive or heavily machine-dependent settings can be used.  If you are using a self-hosted alternative to GitHub, like GitLab, you can include files with sensitive information at your own risk.</p>

<h2 id="setting-up-your-seed-machine">Setting Up your Seed Machine</h2>

<hr />

<p>We will refer to the computer that already has modified configuration files that you'd like to use as the "seed" machine.  This is going to be the place where our configuration files originate.</p>

<p>We need to make sure that we have git installed, so that we can actually go about implementing our configuration syncing.  Install git from the repositories provided by your distribution.  For Ubuntu, these commands should work:</p>
<pre class="code-pre "><code langs="">sudo apt-get update
sudo apt-get install git
</code></pre>
<p>After we have installed git, we should set some configuration details that will keep our commit messages clean.  Substitute your own name and email address in the following commands:</p>

<pre>
git config --global user.name "<span class="highlight">your name</span>"
git config --global user.email "<span class="highlight">email@domain.com</span>"
</pre>

<p>At this point, we have a number of divergent approaches that we can take.  Let me explain each one in turn, as this choice will affect how we proceed later on.</p>

<h3 id="using-your-entire-home-directory-as-a-git-repo">Using your Entire Home Directory as a Git Repo</h3>

<hr />

<p>Perhaps the simplest approach that we have is to simply initialize a git repository within our home directory.  This approach has the advantage of being very simple and straight forward, but can get messy as things go on.</p>

<p>We can begin this method by typing:</p>
<pre class="code-pre "><code langs="">cd ~
git init
</code></pre>
<p>A git repository will be created within our home directory.  If we see the state of our files, we can see that there are a lot of files marked as "untracked":</p>
<pre class="code-pre "><code langs="">git status
</code></pre>
<hr />
<pre class="code-pre "><code langs=""># On branch master
#
# Initial commit
#
# Untracked files:
#   (use "git add <file>..." to include in what will be committed)
#
#   .bash_history
#   .bash_logout
#   .bashrc
#   .gitconfig
#   .profile
#   .screenrc
#   .ssh/
#   .viminfo
#   .vimrc
nothing added to commit but untracked files present (use "git add" to track)
</code></pre>
<p>It is good that git sees all of our files, but it will be quite a pain as we use this computer more if the majority of the files are considered "untracked" and create a long list like this every time we look at this.</p>

<p>If that doesn't bother you, you can simply add the files that you would like to the git repo, like this:</p>
<pre class="code-pre "><code langs="">git add .bashrc
git add .vimrc
. . .
</code></pre>
<p>If you'd rather keep your git status clean, so that it provides only information that you might consider useful, you can instead tell git to ignore <em>all</em> files by default, and we can specifically create exceptions for the files we want to check into version control.</p>

<p>We can do this by creating a <code>.gitignore</code> file in our directory that has a wildcard that will match everything:</p>
<pre class="code-pre "><code langs="">echo "*" > .gitignore
</code></pre>
<p>If we do this and check the status of our repo, you will see that nothing is there to track:</p>
<pre class="code-pre "><code langs="">git status
</code></pre>
<hr />
<pre class="code-pre "><code langs=""># On branch master
#
# Initial commit
#
nothing to commit (create/copy files and use "git add" to track)
</code></pre>
<p>In this scenario, you will then have to force the files that you want to add by using the <code>-f</code> flag to add:</p>
<pre class="code-pre "><code langs="">git add -f .bashrc
git add -f .vimrc
. . .
</code></pre>
<p>Either way, you need to commit your changes when you are finished:</p>
<pre class="code-pre "><code langs="">git commit -m "Initial configuration commit"
</code></pre>
<h3 id="creating-a-configuration-directory-to-store-files">Creating a Configuration Directory to Store Files</h3>

<hr />

<p>An alternative approach to creating a git repository that encompasses your entire home directory is to create a separate directory specifically for tracking these files.</p>

<p>For the purposes of this tutorial, we will call this directory <code>configs</code>:</p>
<pre class="code-pre "><code langs="">cd ~
mkdir configs
</code></pre>
<p>We can enter this directory and then initialize a git repository in here instead of the home directory:</p>
<pre class="code-pre "><code langs="">cd configs
git init
</code></pre>
<p>Now, we have a git repository, but there are not any files inside.  We want to put our files into this directory so that they can be committed with our version control system, but we also want our files available in the home directory so that programs can find them correctly.</p>

<p>The solution that accomplishes both of these objectives is to copy the files into this directory, and then create system links back into the main directory.</p>

<p>For each file, this will look like this:</p>
<pre class="code-pre "><code langs="">mv ~/.vimrc .
ln -s .vimrc ~/
mv ~/.bashrc .
ls -s .vimrc ~/
. . .
</code></pre>
<p>We now have all of our actual configuration files in our <code>~/configs</code> directory, and symbolic links to these files in our home directory.</p>

<p>This may sound more complicated, but it simplifies the git side of things quite a bit.  To add all of the files, we can simply type:</p>
<pre class="code-pre "><code langs="">git add .
</code></pre>
<p>And then we can commit them like this:</p>
<pre class="code-pre "><code langs="">git commit -m "Initial configuration commit"
</code></pre>
<p>This approach simplifies the git side, while perhaps making the actual management of your files a bit more complex.</p>

<h3 id="separating-the-git-directory-from-the-working-tree">Separating the Git Directory from the Working Tree</h3>

<hr />

<p>A third approach attempts to address some of the problems inherent with trying to version the home directory itself.  It separates the actual git repository from the place where it pulls the files.</p>

<p>This can be useful if you like the idea of versioning your home directory directly, but you find that it complicates things with having other git repositories.</p>

<p>Basically, when you initialize a git repo, by default, the current directory is considered the working directory where checkouts will place and modify files.  A git repo called <code>.git</code> will be created in this directory.</p>

<p>However, we can force git to use a separate working directory.</p>

<p>We can start much like we did in the last alternative by creating a separate directory for our configuration:</p>
<pre class="code-pre "><code langs="">cd ~
mkdir configs
</code></pre>
<p>Again, move inside the directory and then initialize the git repository:</p>
<pre class="code-pre "><code langs="">cd configs
git init
</code></pre>
<p>To show you how things will change, let's see what git status says right now:</p>
<pre class="code-pre "><code langs="">git status
</code></pre>
<hr />
<pre class="code-pre "><code langs=""># On branch master
#
# Initial commit
#
nothing to commit (create/copy files and use "git add" to track)
</code></pre>
<p>Currently, the working directory is the <code>~/configs</code> directory where the repo is located.  There are no files here, so it shows up as empty with nothing to commit.</p>

<p>Now, we do things a bit differently.  We will start by specifying a different working directory using the <code>core.worktree</code> git configuration option:</p>
<pre class="code-pre "><code langs="">git config core.worktree "../../"
</code></pre>
<p>What this does is establish the working directory relative to the path of the <code>.git</code> directory.  The first <code>../</code> refers to the <code>~/configs</code> directory, and the second one points us one step beyond that to our home directory.</p>

<p>Basically, we've told git "keep the repository here, but the files you are managing are two levels above the repo".</p>

<p>To see what has changed, we can check the status again:</p>
<pre class="code-pre "><code langs="">git status
</code></pre>
<hr />
<pre class="code-pre "><code langs=""># On branch master
#
# Initial commit
#
# Untracked files:
#   (use "git add <file>..." to include in what will be committed)
#
#       ../.bash_history
#       ../.bash_logout
#       ../.bashrc
#       ../.gitconfig
#       ../.lesshst
#       ../.profile
#       ../.screenrc
#       ../.ssh/
#       ../.viminfo
#       ../.vimrc
nothing added to commit but untracked files present (use "git add" to track)
</code></pre>
<p>You can see that git is now referencing the files in the home directory.</p>

<p>We can now add files by referring to them in relationship to the home directory:</p>
<pre class="code-pre "><code langs="">git add ~/.vimrc
git add ~/.screenrc
git add ~/.bashrc
. . .
</code></pre>
<p>We can then commit like this:</p>
<pre class="code-pre "><code langs="">git commit -m "Initial configuration commit"
</code></pre>
<p>In the directory, in order for git to put all of the pieces back together if you have to pull information later, you'll have to put a <code>/.git</code> file:</p>
<pre class="code-pre "><code langs="">cd ~
nano .git
</code></pre>
<p>Inside, all you need is a line that directs git to the repo file:</p>

<pre>
gitdir: /home/<span class="highlight">your_user</span>/configs/.git
</pre>

<p>This will allow everything to work smoothly.</p>

<h2 id="setting-up-the-remote-git-server">Setting up the Remote Git Server</h2>

<hr />

<p>Depending on your needs, there are a variety of options for setting up a remote repository to house your files.</p>

<p>An obvious choice would be to push your configuration repo up to GitHub.  This might be a great approach for some people, but keep in mind that it does present the possibility of accidentally exposing sensitive data.</p>

<p>If you want to avoid this by pushing to your own private git repository, GitLab is a great choice.</p>

<p>On IndiaReads, you can use the <a href="https://indiareads/community/articles/how-to-use-the-gitlab-one-click-install-image-to-manage-git-repositories">one-click GitLab image</a> to hit the ground running.</p>

<p>Another option is to install GitLab by yourself.  This guide will walk you through <a href="https://indiareads/community/articles/how-to-set-up-gitlab-as-your-very-own-private-github-clone">how to install and configure GitLab</a> on your own server.</p>

<p>Regardless of how you get it set up, you'll have to create a new empty repository to act as your remote target.</p>

<p>Once you create an empty repository, GitHub or GitLab will give you a page with commands on how to get your configuration files into the repository.  You will most likely want to click the button to switch to HTTP commands instead of SSH unless you have already added SSH keys.</p>

<p>It will look something like this:</p>

<p><img src="https://assets.digitalocean.com/articles/git_configs/existing_repo.png" alt="GitLab existing repo" /></p>

<p>Use the command suggestions to get your configuration into the remote repository.  Most likely, it will be something along the lines of:</p>

<pre>
cd configs      # <span class="highlight">or "cd ~" if you are using your home directory</span>
git remote add origin http://<span class="highlight">git_lab_ip</span>/<span class="highlight">your_gitlab_user</span>/<span class="highlight">repo_name</span>.git
git push -u origin master
</pre>

<p>This will push your configuration into the GitLab repo.</p>

<h2 id="pulling-configuration-from-the-remote-repo">Pulling Configuration from the Remote Repo</h2>

<hr />

<p>Now that we have our configuration files in our remote repo, we can pull them down from other machines.  We will refer to the server we want to add our configuration files to as our "target" machine.</p>

<p>On the target machine, make sure that you have git installed.  On Ubuntu, this will again be accomplished like this:</p>
<pre class="code-pre "><code langs="">sudo apt-get update
sudo apt-get install git
</code></pre>
<p>Once you have this installed, you should set your basic config variables again:</p>

<pre>
git config --global user.name "<span class="highlight">your name</span>"
git config --global user.email "<span class="highlight">email@domain.com</span>"
</pre>

<p>The next step depends again on how you want this machine to interact with the git repo files.</p>

<h3 id="put-home-directory-under-version-control">Put Home Directory Under Version Control</h3>

<hr />

<p>If you want to have the entire home directory under git version control, you should start an empty git repo there:</p>
<pre class="code-pre "><code langs="">cd ~
git init
</code></pre>
<p>From here, we can add the GitHub or GitLab repo as the origin of this repo:</p>

<pre>
git remote add origin http://<span class="highlight">git_lab_ip</span>/<span class="highlight">your_gitlab_user</span>/<span class="highlight">repo_name</span>.git
</pre>

<p>After this point, we will need to do something a bit different if we already have files on this machine that may come into conflict with what is in our repo.  For instance, a <code>~/.bashrc</code> file is usually included by default for each user.</p>

<p>One option would be to delete or move each file that conflicts with our repo files.  However, we can also just tell git to overwrite all conflicting files with the remote versions.</p>

<p>We can do this by first fetching the remote files, and then telling git to reset to the most recent commit, which should be our remote versions:</p>
<pre class="code-pre "><code langs="">git fetch --all
git reset --hard origin/master
</code></pre>
<p>This should bring all of the remote files into our new machine.</p>

<p>You can easily modify files on this machine and push them back to the remote repo as well:</p>
<pre class="code-pre "><code langs="">echo "# a comment" >> .bashrc
git add .bashrc
git commit -m "test"
git push origin master
</code></pre>
<h3 id="use-a-separate-configuration-directory">Use a Separate Configuration Directory</h3>

<hr />

<p>If you prefer to use a separate directory to hold the actual configuration files and you just want to link those files into your home directory, you can clone the repository instead.</p>

<p>We just need the URL that refers to our remote repository again.  This time, we will just use clone instead of initializing a new git repository:</p>

<pre>
git clone http://<span class="highlight">git_lab_ip</span>/<span class="highlight">your_gitlab_user</span>/<span class="highlight">configs</span>.git
</pre>

<p>Now, we can move into our newly cloned directory:</p>
<pre class="code-pre "><code langs="">cd configs
</code></pre>
<p>This will have all of our configuration files.  We can then link them individually into our home directory:</p>
<pre class="code-pre "><code langs="">ln -s .vimrc ~/
ln -s .screenrc ~/
</code></pre>
<p>Again, you might need to move or delete files that conflict with the new files:</p>
<pre class="code-pre "><code langs="">rm ~/.bashrc
ln -s .bashrc ~/
</code></pre>
<p>This is a more manual process, but you can have more fine-grained control over what files you want to be active on this computer.</p>

<h3 id="implement-separate-git-repo-and-directory">Implement Separate Git Repo and Directory</h3>

<hr />

<p>To implement the separated working directory and repo setup that we talked about for the seed machine, we can again clone the repo.</p>

<p>The difference with this method is that since we want the repository to unload its files directly into our home directory, we won't checkout any files at the time of the clone:</p>

<pre>
git clone --no-checkout http://<span class="highlight">git_lab_ip</span>/<span class="highlight">your_gitlab_user</span>/<span class="highlight">configs</span>.git
</pre>

<p>Now, we need to tell git that we want to unpack our files into the directory located two tiers above the <code>~/configs/.git</code> directory (the home directory):</p>
<pre class="code-pre "><code langs="">cd configs
git config core.worktree "../../"
</code></pre>
<p>Now, we can explicitly checkout the files.  Again, we need to force git to overwrite our current files.  We can do that by "resetting" back to the state that the files were in from our remote repo:</p>
<pre class="code-pre "><code langs="">git reset --hard origin/master
</code></pre>
<p>Your configuration files should now be available on your new computer.</p>

<h2 id="conclusion">Conclusion</h2>

<hr />

<p>You should now have a few options for how to keep your personal configuration files in version control.  By continually committing changes to your configuration files to your remote repository, you should be able to recreate the most important parts of your environment on remote machines quickly and effortlessly.</p>

<div class="author">By Justin Ellingwood</div>

    